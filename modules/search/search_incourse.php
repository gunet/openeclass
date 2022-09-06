<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


/**
 * @file search_incourse.php
 * @brief search inside a course
 */


$require_current_course = TRUE;
$guest_allowed = true;
require_once '../../include/baseTheme.php';
require_once 'indexer.class.php';
require_once 'announcementindexer.class.php';
require_once 'agendaindexer.class.php';
require_once 'linkindexer.class.php';
require_once 'videoindexer.class.php';
require_once 'videolinkindexer.class.php';
require_once 'exerciseindexer.class.php';
require_once 'forumindexer.class.php';
require_once 'forumtopicindexer.class.php';
require_once 'forumpostindexer.class.php';
require_once 'documentindexer.class.php';
require_once 'unitindexer.class.php';
require_once 'unitresourceindexer.class.php';

$pageName = $langSearch;

if (!get_config('enable_search')) {
    $tool_content .= "<div class='alert alert-info'>$langSearchDisabled</div>";
    draw($tool_content, 2);
    exit;
}

$found = false;
register_posted_variables(array('announcements' => true,
    'agenda' => true,
    'course_units' => true,
    'documents' => true,
    'exercises' => true,
    'forums' => true,
    'links' => true,
    'video' => true), 'all');

if (isset($_GET['all'])) {
    $all = intval($_GET['all']);
    $announcements = $agenda = $course_units = $documents = $exercises = $forums = $links = $video = 1;
}

if (isset($_REQUEST['search_terms'])) {
    $search_terms = trim($_REQUEST['search_terms']);
}

if (empty($search_terms)) {

    // display form
    $langSearchCriteria;
    $tool_content .= "
    <div class='form-wrapper'>
        <form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>
        <fieldset>
        <div class='form-group'>
            <label for='search_terms' class='col-sm-2 control-label'>$langOR:</label>
            <div class='col-sm-10'>
                <input name='search_terms' type='text' class='form-control'>
            </div>
        </div>
        <div class='form-group'>
            <label for='search_terms' class='col-sm-2 control-label'>$langSearchIn:</label>
            <div class='col-sm-10'>
                <div class='row'>
                    <div class='col-xs-6 col-sm-4'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='announcements' checked>
                            $langAnnouncements
                          </label>
                        </div>
                    </div>
                    <div class='col-xs-6 col-sm-4'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='agenda' checked>
                            $langAgenda
                          </label>
                        </div>
                    </div>
                    <div class='col-xs-6 col-sm-4'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='course_units' checked>
                            $langCourseUnits
                          </label>
                        </div>
                    </div>
                    <div class='col-xs-6 col-sm-4'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='documents' checked>
                            $langDoc
                          </label>
                        </div>
                    </div>
                    <div class='col-xs-6 col-sm-4'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='forums' checked>
                            $langForums
                          </label>
                        </div>
                    </div>
                    <div class='col-xs-6 col-sm-4'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='exercises' checked>
                            $langExercices
                          </label>
                        </div>
                    </div>
                    <div class='col-xs-6 col-sm-4'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='video' checked>
                            $langVideo
                          </label>
                        </div>
                    </div>
                    <div class='col-xs-6 col-sm-4'>
                        <div class='checkbox'>
                          <label>
                            <input type='checkbox' name='links' checked>
                            $langLinks
                          </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='submit' value='$langDoSearch'>
            </div>
        </div>
       </fieldset>
       </form>
    </div>";
} else {
    // prepare data in POST for feeding Indexer
    $_POST['course_id'] = $course_id;
    $_POST['search_terms'] = $search_terms;

    $idx = new Indexer();
    if (!$idx->getIndex()) {
        draw($tool_content, 2);
        exit;
    }
    $tool_content .= action_bar(array(
                    array('title' => $langAdvancedSearch,
                          'url' => $_SERVER['SCRIPT_NAME'],
                          'icon' => 'fa-search',
                          'level' => 'primary-label',
                          'button-class' => 'btn-success',)));

    $announceHits = array();
    $agendaHits = array();
    $documentHits = array();
    $exerciseHits = array();
    $forumHits = array();
    $forumTopicHits = array();
    $forumPostHits = array();
    $linkHits = array();
    $videoHits = array();
    $vlinkHits = array();
    $unitHits = array();
    $uresHits = array();

    $idxQ = Indexer::buildQuery($_POST);
    $allHits = $idx->searchRaw($idxQ);
    foreach ($allHits as $hit) {
        switch ($hit->doctype) {
            case Indexer::DOCTYPE_AGENDA:
                if ($agenda && $hit->visible) {
                    $agendaHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_ANNOUNCEMENT:
                if ($announcements && $hit->visible) {
                    $announceHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_DOCUMENT:
                if ($documents && $hit->visible) {
                    $documentHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_EXERCISE:
                if ($exercises && $hit->visible) {
                    $exerciseHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_FORUM:
                if ($forums) {
                    $forumHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_FORUMPOST:
                if ($forums) {
                    $forumPostHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_FORUMTOPIC:
                if ($forums) {
                    $forumTopicHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_LINK:
                if ($links) {
                    $linkHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_UNIT:
                if ($course_units && $hit->visible) {
                    $unitHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_UNITRESOURCE:
                if ($course_units && $hit->visible) {
                    $uresHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_VIDEO:
                if ($video) {
                    $videoHits[] = $hit;
                }
                break;
            case Indexer::DOCTYPE_VIDEOLINK:
                if ($video) {
                    $vlinkHits[] = $hit;
                }
                break;
            default:
                break;
        }
    }

    $search_results = '';
    $results_count = 0;
    // search in announcements
    if ($announcements) {
        $announceHitsCount = count($announceHits);
        if ($announceHitsCount > 0) {
            $results_count += $announceHitsCount;
            $search_results .= "
              <table class='table-default'>
              <tr>
                <th colspan='2'>$langAnnouncements:</th>
              </tr>";
            $announces = Database::get()->queryArray("SELECT id, title, content, date FROM announcement WHERE id in " . inIdsFromHits($announceHits));
            $announcesUrls = urlsFromHits($announceHits);

            $numLine = 0;
            foreach ($announces as $announce) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                                  <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                                  <td><a href='" . $announcesUrls[$announce->id] . "'>" . q($announce->title) . "</a>&nbsp;&nbsp;
                                  <small>(" . format_locale_date(strtotime($announce->date)) . ")
                                  </small><br />" . $announce->content . "</td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in agenda
    if ($agenda) {
        $agendaHitsCount = count($agendaHits);
        if ($agendaHitsCount > 0) {
            $results_count += $agendaHitsCount;
            $search_results .= "
                  <table class='table-default'>
                  <tr>
                    <th colspan='2' class='left'>$langAgenda:</th>
                  </tr>";
            $agendas = Database::get()->queryArray("SELECT title, content, start, duration FROM agenda WHERE id in " . inIdsFromHits($agendaHits));

            $numLine = 0;
            foreach ($agendas as $agenda) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "
                  <tr class='$class'>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
                $message = $langUnknown;
                if ($agenda->duration != "") {
                    if ($agenda->duration == 1) {
                        $message = $langHour;
                    } else {
                        $message = $langHours;
                    }
                }
                $search_results .= "<span class='day'>" .
                        ucfirst(format_locale_date(strtotime($agenda->start))) .
                        "</span> ($langHour: " . ucfirst(date("H:i", strtotime($agenda->start))) . ")<br />"
                        . q($agenda->title) . " (" . $langDuration . ": " . q($agenda->duration) . " $message) " . $agenda->content . "
                    </td>
                  </tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in documents
    if ($documents) {
        $documentHitsCount = count($documentHits);
        if ($documentHitsCount > 0) {
            $results_count += $documentHitsCount;
            $search_results .= "
                  <table class='table-default'>
                  <tr>
                    <th colspan='2' class='left'>$langDoc:</th>
                  </tr>";
            $documents = Database::get()->queryArray("SELECT id, filename, path, comment FROM document WHERE id in " . inIdsFromHits($documentHits));
            $docsUrls = urlsFromHits($documentHits);

            $numLine = 0;
            foreach ($documents as $document) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $add_comment = (empty($document->comment)) ? "" : "<br /><span class='smaller'> (" . q($document->comment) . ")</span>";
                $search_results .= "<a href='" . $docsUrls[$document->id] . "'>" . q($document->filename) . "</a> $add_comment </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in exercises
    if ($exercises) {
        $exerciseHitsCount = count($exerciseHits);
        if ($exerciseHitsCount > 0) {
            $results_count += $exerciseHitsCount;
            $search_results .= "
                <table class='table-default'>
                    <tr>
                      <th colspan='2' class='left'>$langExercices:</th>
                    </tr>";
            $exercises = Database::get()->queryArray("SELECT id, title, description FROM exercise WHERE id in " . inIdsFromHits($exerciseHits));
            $exerciseUrls = urlsFromHits($exerciseHits);

            $numLine = 0;
            foreach ($exercises as $exercise) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $desc_text = (empty($exercise->description)) ? "" : "<br /> <span class='smaller'>" . $exercise->description . "</span>";
                $search_results .= "<a href='" . $exerciseUrls[$exercise->id] . "'>" . q($exercise->title) . "</a>$desc_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in forums
    if ($forums) {
        $forumHitsCount = count($forumHits);
        if ($forumHitsCount > 0) {
            $results_count += $forumHitsCount;
            $search_results .= "
                        <table class='table-default'>
                        <tr>
                          <th colspan='2' class='left'>$langForum ($langCategories):</th>
                        </tr>";
            $forums = Database::get()->queryArray("SELECT id, name, `desc` FROM forum WHERE id in " . inIdsFromHits($forumHits));
            $forumUrls = urlsFromHits($forumHits);

            $numLine = 0;
            foreach ($forums as $forum) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $desc_text = (empty($forum->desc)) ? "" : "<br /><span class='smaller'>(" . q($forum->desc) . ")</span>";
                $search_results .= "<a href='" . $forumUrls[$forum->id] . "'>" . q($forum->name) . "</a> " . $desc_text . " </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }

        if (count($forumTopicHits) > 0) {
            $search_results .= "
                <table class='table-default'>
                <tr>
                  <th colspan='2' class='left'>$langForum ($langTopics - $langMessages):</th>
                        </tr>";
            $ftopics = Database::get()->queryArray("SELECT id, title FROM forum_topic WHERE id in " . inIdsFromHits($forumTopicHits));
            $ftopicUrls = urlsFromHits($forumTopicHits);

            $numLine = 0;
            foreach ($ftopics as $ftopic) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
                $search_results .= "<strong>$langSubject</strong>: <a href='" . $ftopicUrls[$ftopic->id] . "'>" . q($ftopic->title) . "</a>";
                if (count($forumPostHits) > 0) {
                    $fposts = Database::get()->queryArray("SELECT id, post_text FROM forum_post WHERE id in " . inIdsFromHits($forumPostHits));
                    $fpostUrls = urlsFromHits($forumPostHits);
                    foreach ($fposts as $fpost) {
                        $search_results .= "<br /><strong>$langMessage</strong> <a href='" . $fpostUrls[$fpost->id] . "'>" . $fpost->post_text . "</a>";
                    }
                }
                $search_results .= "</td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in links
    if ($links) {
        $linkHitsCount = count($linkHits);
        if ($linkHitsCount > 0) {
            $results_count += $linkHitsCount;
            $search_results .= "
                <table class='table-default'>
                <tr>
                  <th colspan='2' class='left'>$langLinks:</th>
                </tr>";
            $links = Database::get()->queryArray("SELECT id, title, description FROM link WHERE id in " . inIdsFromHits($linkHits));
            $linkUrls = urlsFromHits($linkHits);

            $numLine = 0;
            foreach ($links as $link) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
                $desc_text = (empty($link->description)) ? "" : "<span class='smaller'>" . $link->description . "</span>";
                $search_results .= "<a href='" . $linkUrls[$link->id] . "' target='_blank'> " . q($link->title) . "</a> $desc_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in video and videolinks
    if ($video) {
        $videoHitsCount = count($videoHits);
        if ($videoHitsCount > 0) {
            $results_count += $videoHitsCount;
            $search_results .= "
                <table class='table-default'>
                <tr>
                  <th colspan='2' class='left'>$langVideo:</th>
                </tr>";
            $videos = Database::get()->queryArray("SELECT id, title, description FROM video WHERE id in " . inIdsFromHits($videoHits));
            $videoUrls = urlsFromHits($videoHits);

            $numLine = 0;
            foreach ($videos as $video) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
                $desc_text = (empty($video->description)) ? "" : "<span class='smaller'>(" . q($video->description) . ")</span>";
                $search_results .= "<a href='" . $videoUrls[$video->id] . "' target=_blank>" . q($video->title) . "</a> $desc_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }

        $vlinkHitsCount = count($vlinkHits);
        if ($vlinkHitsCount > 0) {
            $results_count += $vlinkHitsCount;
            $search_results .= "
                        <table class='table-default'>
                        <tr>
                        <th colspan='2'>$langLinks:</th>
                        </tr>";
            $vlinks = Database::get()->queryArray("SELECT id, title, description FROM videolink WHERE id in " . inIdsFromHits($vlinkHits));
            $vlinkUrls = urlsFromHits($vlinkHits);

            $numLine = 0;
            foreach ($vlinks as $vlink) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $desc_text = (empty($vlink->description)) ? "" : "<span class='smaller'>(" . q($vlink->description) . ")</span>";
                $search_results .= "<a href='" . $vlinkUrls[$vlink->id] . "' target=_blank>" . q($vlink->title) . "</a><br /> $desc_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in cours_units and unit_resources
    if ($course_units) {
        $unitHitsCount = count($unitHits);
        if ($unitHitsCount > 0) {
            $results_count += $unitHitsCount;
            $search_results .= "
                <table class='table-default'>
                <tr>
                  <th colspan='2' class='left'>$langCourseUnits:</th>
                </tr>";
            $units = Database::get()->queryArray("SELECT id, title, comments FROM course_units WHERE id in " . inIdsFromHits($unitHits));
            $unitUrls = urlsFromHits($unitHits);

            $numLine = 0;
            foreach ($units as $unit) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $comments_text = (empty($unit->comments)) ? "" : " " . $unit->comments;
                $search_results .= "<a href='" . $unitUrls[$unit->id] . "'>" . q($unit->title) . "</a> $comments_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }

        $uresHitsCount = count($uresHits);
        if ($uresHitsCount > 0) {
            $results_count += $uresHitsCount;
            $search_results .= "
                <table class='table-default' align='left'>
                <tr>
                  <th colspan='2' class='left'>$langCourseUnits:</th>
                </tr>";
            $ureses = Database::get()->queryArray("SELECT id, title, comments FROM unit_resources WHERE id in " . inIdsFromHits($uresHits));
            $uresUrls = urlsFromHits($uresHits);

            $numLine = 0;
            foreach ($ureses as $ures) {
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $comments_text = (empty($ures->comments)) ? "" : "<span class='smaller'>" . $ures->comments . "</span>";
                $search_results .= q($ures->title) . " <a href='" . $uresUrls[$ures->id] . "'> $comments_text </a></td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }
    $tool_content .= "
        <div class='alert alert-info'>$langDoSearch:&nbsp;<label> '" . q($search_terms) . "'</label><br><small>$results_count $langResults2</small></div>
    ";
    $tool_content .= $search_results;
    // else ... no results found
    if ($found == false) {
        $tool_content .= "<div class='alert alert-warning'>$langNoResult</div>";
    }
} // end of search
draw($tool_content, 2);

function inIdsFromHits($hits) {
    $hitIds = array();
    foreach ($hits as $hit) {
        $hitIds[] = intval($hit->pkid);
    }
    return "(" . implode(",", $hitIds) . ")";
}

function urlsFromHits($hits) {
    $hitUrls = array();
    foreach ($hits as $hit) {
        $hitUrls[$hit->pkid] = $hit->url;
    }
    return $hitUrls;
}

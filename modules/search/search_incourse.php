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
require_once 'include/lib/textLib.inc.php';
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
    $search_terms = addslashes($_REQUEST['search_terms']);
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
    // ResourceIndexers require course_id inside the input data array (POST, but we do not want to pass it through the form)
    $_POST['course_id'] = $course_id;
    // Search Terms might come from GET, but we want to pass it alltogether with POST in ResourceIndexers
    $_POST['search_terms'] = $search_terms;
    $idx = new Indexer();
$tool_content .= action_bar(array(
                    array('title' => $langAdvancedSearch,
                          'url' => $_SERVER['SCRIPT_NAME'],
                          'icon' => 'fa-search',
                          'level' => 'primary-label',
                          'button-class' => 'btn-success',)));

    $search_results = '';
    $results_count = 0;
    // search in announcements
    if ($announcements) {
        $announceHits = $idx->searchRaw(AnnouncementIndexer::buildQuery($_POST));
        $announceHitsCount = count($announceHits);
        if ($announceHitsCount > 0) {
            $results_count += $announceHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
              <table class='table-default'>
              <tr>
                <th colspan='2'>$langAnnouncements:</th>
              </tr>";

            $numLine = 0;
            foreach ($announceHits as $annHit) {
                $announce = Database::get()->querySingle("SELECT title, content, date FROM announcement WHERE id = ?d", $annHit->pkid);

                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                                  <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                                  <td><a href='" . $annHit->url . "'>" . q($announce->title) . "</a>&nbsp;&nbsp;
                                  <small>(" . nice_format(claro_format_locale_date($dateFormatLong, strtotime($announce->date))) . ")
                                  </small><br />" . $announce->content . "</td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in agenda
    if ($agenda) {
        $agendaHits = $idx->searchRaw(AgendaIndexer::buildQuery($_POST));
        $agendaHitsCount = count($agendaHits);
        if ($agendaHitsCount > 0) {
        $results_count += $agendaHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                  <table class='table-default'>
          <tr>
            <th colspan='2' class='left'>$langAgenda:</th>
                  </tr>";

            $numLine = 0;
            foreach ($agendaHits as $agHit) {
                $agenda = Database::get()->querySingle("SELECT title, content, start, duration FROM agenda WHERE id = ?d", $agHit->pkid);                

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
                        ucfirst(claro_format_locale_date($dateFormatLong, strtotime($agenda->start))) .
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
        $documentHits = $idx->searchRaw(DocumentIndexer::buildQuery($_POST));
        $documentHitsCount = count($documentHits);
        if ($documentHitsCount > 0) {
            $results_count += $documentHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                  <table class='table-default'>
                  <tr>
                    <th colspan='2' class='left'>$langDoc:</th>
                  </tr>";

            $numLine = 0;
            foreach ($documentHits as $docHit) {
                $document = Database::get()->querySingle("SELECT filename, path, comment FROM document WHERE id = ?d", $docHit->pkid);

                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $add_comment = (empty($document->comment)) ? "" : "<br /><span class='smaller'> (" . q($document->comment) . ")</span>";
                $search_results .= "<a href='" . $docHit->url . "'>" . q($document->filename) . "</a> $add_comment </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in exercises
    if ($exercises) {
        $exerciseHits = $idx->searchRaw(ExerciseIndexer::buildQuery($_POST));
        $exerciseHitsCount = count($exerciseHits);
        if ($exerciseHitsCount > 0) {
            $results_count += $exerciseHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                <table class='table-default'>
                    <tr>
                      <th colspan='2' class='left'>$langExercices:</th>
                    </tr>";

            $numLine = 0;
            foreach ($exerciseHits as $exerciseHit) {
                $exercise = Database::get()->querySingle("SELECT title, description FROM exercise WHERE id = ?d",$exerciseHit->pkid);

                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $desc_text = (empty($exercise->description)) ? "" : "<br /> <span class='smaller'>" . $exercise->description . "</span>";
                $search_results .= "<a href='" . $exerciseHit->url . "'>" . q($exercise->title) . "</a>$desc_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in forums
    if ($forums) {
        $forumHits = $idx->searchRaw(ForumIndexer::buildQuery($_POST));
        $forumTopicHits = $idx->searchRaw(ForumTopicIndexer::buildQuery($_POST));
        $forumPostHits = $idx->searchRaw(ForumPostIndexer::buildQuery($_POST));
        $forumHitsCount = count($forumHits);
        if ($forumHitsCount > 0) {
            $results_count += $forumHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                        <table width='99%' class='sortable' id='t5' align='left'>
                        <tr>
                        <th colspan='2' class='left'>$langForum ($langCategories):</th>
                        </tr>";

            $numLine = 0;
            foreach ($forumHits as $forumHit) {
                $forum = Database::get()->querySingle("SELECT name, `desc` FROM forum WHERE id = ?d",$forumHit->pkid);                

                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $desc_text = (empty($forum->desc)) ? "" : "<br /><span class='smaller'>(" . q($forum->desc) . ")</span>";
                $search_results .= "<a href='" . $forumHit->url . "'>" . q($forum->name) . "</a> " . $desc_text . " </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }

        if (count($forumTopicHits) > 0) {
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                <table class='table-default'>
        <tr>
          <th colspan='2' class='left'>$langForum ($langSubjects - $langMessages):</th>
                </tr>";

            $numLine = 0;
            foreach ($forumTopicHits as $forumTopicHit) {
                $ftopic = Database::get()->querySingle("SELECT title FROM forum_topic WHERE id = ?d", $forumTopicHit->pkid);                
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
                $search_results .= "<strong>$langSubject</strong>: <a href='" . $forumTopicHit->url . "'>" . q($ftopic->title) . "</a>";
                if (count($forumPostHits) > 0) {
                    foreach ($forumPostHits as $forumPostHit) {
                        $fpost = Database::get()->querySingle("SELECT post_text FROM forum_post WHERE id = ?d", $forumPostHit->pkid);                        
                        $search_results .= "<br /><strong>$langMessage</strong> <a href='" . $forumPostHit->url . "'>" . $fpost->post_text . "</a>";
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
        $linkHits = $idx->searchRaw(LinkIndexer::buildQuery($_POST));
        $linkHitsCount = count($linkHits);
        if ($linkHitsCount > 0) {
            $results_count += $linkHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                <table class='table-default'>
                <tr>
                  <th colspan='2' class='left'>$langLinks:</th>
                </tr>";

            $numLine = 0;
            foreach ($linkHits as $linkHit) {
                $link = Database::get()->querySingle("SELECT title, description FROM link WHERE id = ?d", $linkHit->pkid);                

                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
                $desc_text = (empty($link->description)) ? "" : "<span class='smaller'>" . $link->description . "</span>";
                $search_results .= "<a href='" . $linkHit->url . "' target='_blank'> " . q($link->title) . "</a> $desc_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in video and videolinks
    if ($video) {
        $videoHits = $idx->searchRaw(VideoIndexer::buildQuery($_POST));
        $vlinkHits = $idx->searchRaw(VideolinkIndexer::buildQuery($_POST));
        $videoHitsCount = count($videoHits);
        if ($videoHitsCount > 0) {
            $results_count += $videoHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                <table class='table-default'>
        <tr>
                  <th colspan='2' class='left'>$langVideo:</th>
                </tr>";
            $numLine = 0;
            foreach ($videoHits as $videoHit) {
                $video = Database::get()->querySingle("SELECT title, description FROM video WHERE id = ?d", $videoHit->pkid);                

                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                    <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                    <td>";
                $desc_text = (empty($video->description)) ? "" : "<span class='smaller'>(" . q($video->description) . ")</span>";
                $search_results .= "<a href='" . $videoHit->url . "' target=_blank>" . q($video->title) . "</a> $desc_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
        $vlinkHitsCount = count($vlinkHits);
        if ($vlinkHitsCount > 0) {
            $results_count += $vlinkHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                        <table width='99%' class='sortable' id='t9' align='left'>
                        <tr>
                        <th colspan='2' class='left'>$langLinks:</th>
                        </tr>";

            $numLine = 0;
            foreach ($vlinkHits as $vlinkHit) {
                $vlink = Database::get()->querySingle("SELECT title, description FROM videolink WHERE id = ?d", $vlinkHit->pkid);

                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $desc_text = (empty($vlink->description)) ? "" : "<span class='smaller'>(" . q($vlink->description) . ")</span>";
                $search_results .= "<a href='" . $vlinkHit->url . "' target=_blank>" . q($vlink->title) . "</a><br /> $desc_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }

    // search in cours_units and unit_resources
    if ($course_units) {
        $unitHits = $idx->searchRaw(UnitIndexer::buildQuery($_POST));
        $uresHits = $idx->searchRaw(UnitResourceIndexer::buildQuery($_POST));
        $unitHitsCount = count($unitHits);
        if ($unitHitsCount > 0) {
            $results_count += $unitHitsCount;
            $search_results .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                <table class='table-default'>
                <tr>
                  <th colspan='2' class='left'>$langCourseUnits:</th>
                </tr>";

            $numLine = 0;
            foreach ($unitHits as $unitHit) {
                $unit = Database::get()->querySingle("SELECT title, comments FROM course_units WHERE id = ?d", $unitHit->pkid);
                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $comments_text = (empty($unit->comments)) ? "" : " " . $unit->comments;
                $search_results .= "<a href='" . $unitHit->url . "'>" . q($unit->title) . "</a> $comments_text </td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
        $uresHitsCount = count($uresHits);
        if ($uresHitsCount > 0) {
            $results_count += $uresHitsCount;
            $search_results .= "
                <script type='text/javascript' src='../auth/sorttable.js'></script>
                <table class='table-default sortable' align='left'>
                <tr>
                  <th colspan='2' class='left'>$langCourseUnits:</th>
                </tr>";

            $numLine = 0;
            foreach ($uresHits as $uresHit) {
                $ures = Database::get()->querySingle("SELECT title, comments FROM unit_resources WHERE id = ?d", $uresHit->pkid);

                $class = ($numLine % 2) ? 'odd' : 'even';
                $search_results .= "<tr class='$class'>
                        <td width='1' valign='top'><img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td>";
                $comments_text = (empty($ures->comments)) ? "" : "<span class='smaller'>" . $ures->comments . "</span>";
                $search_results .= q($ures->title) . " <a href='" . $uresHit->url . "'> $comments_text </a></td></tr>";
                $numLine++;
            }
            $search_results .= "</table>";
            $found = true;
        }
    }
    $tool_content .= "
        <div class='alert alert-info'>$langDoSearch:&nbsp;<label> '$search_terms'</label><br><small>$results_count $langResults2</small></div>
    ";
    $tool_content .= $search_results;
    // else ... no results found
    if ($found == false) {
        $tool_content .= "<div class='alert alert-warning'>$langNoResult</div>";
    }
} // end of search
draw($tool_content, 2);

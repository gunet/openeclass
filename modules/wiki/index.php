<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
  @brief main page of wiki subsystem
  @author: Frederic Minne <zefredz@gmail.com>
           Open eClass Team <eclass@gunet.gr>
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'wiki';
require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

load_js('tools.js');

$style = '';

$_gid = isset($_REQUEST['gid']) ? $_REQUEST['gid'] : null;

//check if groups are enabled for this course
//if not user will be shown the course wiki page
$result = Database::get()->querySingle("SELECT `wiki` FROM `group_properties` WHERE course_id = ?d", $course_id);
if (is_object($result) && $result->wiki == 1) {
    $is_groupAllowed = true;
} else {
    $is_groupAllowed = false;
}

$toolName = $langWiki;

// display mode
// check and set user access level for the tool
// set admin mode and groupId

if ($_gid && $_gid != 0 && $is_groupAllowed) {
    // group context
    $groupId = intval($_gid);

    $sql = "SELECT `name` FROM `group` WHERE `id` = ?d";
    $result = Database::get()->querySingle($sql, $groupId);
    if (is_object($result)) {
        $group_name = $result->name;
        $navigation[] = array('url' => '../group/index.php?course=' . $course_code, 'name' => $langGroups);
        $navigation[] = array('url' => '../group/group_space.php?course=' . $course_code, 'name' => $group_name);
    } else {
        $groupId = 0;
    }
} else {
    // course context
    $groupId = 0;
}

// require wiki files
require_once 'lib/class.wiki.php';
require_once 'lib/class.wikistore.php';
require_once 'lib/class.wikipage.php';
require_once 'lib/lib.requestfilter.php';
require_once 'lib/lib.wikidisplay.php';

// filter request variables
// filter allowed actions using user status
if ($is_editor) {
    $valid_actions = array('list', 'rqEdit', 'exEdit', 'exDelete', 'exExport', 'visible', 'invisible');
} else {
    $valid_actions = array('list');
}

$_CLEAN = filter_by_key('action', $valid_actions, 'R', false);
$action = ( isset($_CLEAN['action']) ) ? $_CLEAN['action'] : 'list';

$wikiId = (isset($_REQUEST['wikiId'])) ? intval($_REQUEST['wikiId']) : 0;

$creatorId = $uid;

// Objects instantiation

$wikiStore = new WikiStore();
$wikiList = array();

// --------- Start of command processing ----------------

switch ($action) {
    case 'visible':
            $w = new wiki();
            $w->load($wikiId);
            $w->setVisibility(1);
            $w->save();
            redirect_to_home_page("modules/wiki/index.php?course=$course_code");
        break;
    case 'invisible':
            $w = new wiki();
            $w->load($wikiId);
            $w->setVisibility(0);
            $w->save();
            redirect_to_home_page("modules/wiki/index.php?course=$course_code");
        break;
    case 'exExport':
    {
        require_once "lib/class.wiki2xhtmlexport.php";

        if (!$wikiStore->wikiIdExists($wikiId)) {
            $message = $langWikiInvalidWikiId;
            $action = "error";
            $style = "caution";
        } else {
            $wiki = $wikiStore->loadWiki($wikiId);
            $wikiTitle = $wiki->getTitle();
            $renderer = new WikiToSingleHTMLExporter($wiki);

            $contents = $renderer->export();

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=WikiExport.html');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            ob_clean();
            flush();
            echo $contents;
            exit;
        }
        break;
    }
    // execute delete
    case "exDelete":
            if ($wikiStore->wikiIdExists($wikiId)) {
                $wiki = $wikiStore->deleteWiki($wikiId);
                Session::Messages($langWikiDeletionSucceed, 'alert-success');
            } else {
                Session::Messages($langWikiInvalidWikiId);
            }

            if ($groupId === 0) {
                redirect_to_home_page("modules/wiki/index.php?course=$course_code");
            } else {
                redirect_to_home_page("modules/wiki/index.php?course=$course_code&gid=$groupId");
            }
    // request edit
    case "rqEdit": {
            if ($wikiId == 0) {
                $wikiTitle = Session::has('title') ? Session::get('title') : '';
                $wikiDesc = Session::has('desc') ? Session::get('desc') : '';
                $wikiACL = null;
            } elseif ($wikiStore->wikiIdExists($wikiId)) {
                $wiki = $wikiStore->loadWiki($wikiId);
                $wikiTitle = Session::has('title') ? Session::get('title') : $wiki->getTitle();
                $wikiDesc = Session::has('desc') ? Session::get('desc') : $wiki->getDescription();
                $wikiACL = $wiki->getACL();
                $groupId = $wiki->getGroupId();
            } else {
                $message = $langWikiInvalidWikiId;
                $action = "error";
                $style = "caution";
            }
            break;
        }
    // execute edit
    case "exEdit": {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));
        $v->labels(array(
            'title' => "$langTheField $langTitle"
        ));
        if($v->validate()) {
            $wikiTitle = (isset($_POST['title'])) ? strip_tags($_POST['title']) : '';
            $wikiDesc = (isset($_POST['desc'])) ? strip_tags($_POST['desc']) : '';

            $acl = (isset($_POST['acl'])) ? $_POST['acl'] : null;
            // initialise access control list
            $wikiACL = WikiAccessControl::emptyWikiACL();
            if (is_array($acl)) {
                foreach ($acl as $key => $value) {
                    if ($value == 'on') {
                        $wikiACL[$key] = true;
                    }
                }
            }

            // force Wiki ACL coherence

            if ($wikiACL['course_read'] == false and $wikiACL['course_edit'] == true) {
                $wikiACL['course_edit'] = false;
            }
            if ($wikiACL['group_read'] == false and $wikiACL['group_edit'] == true) {
                $wikiACL['group_edit'] = false;
            }
            if ($wikiACL['other_read'] == false and $wikiACL['other_edit'] == true) {
                $wikiACL['other_edit'] = false;
            }

            if ($wikiACL['course_edit'] == false and $wikiACL['course_create'] == true) {
                $wikiACL['course_create'] = false;
            }
            if ($wikiACL['group_edit'] == false and $wikiACL['group_create'] == true) {
                $wikiACL['group_create'] = false;
            }
            if ($wikiACL['other_edit'] == false and $wikiACL['other_create'] == true) {
                $wikiACL['other_create'] = false;
            }
            if ($wikiId == 0) {
                $wiki = new Wiki();
                $wiki->setTitle($wikiTitle);
                $wiki->setDescription($wikiDesc);
                $wiki->setACL($wikiACL);
                $wiki->setGroupId($groupId);
                $wikiId = $wiki->save();

                $mainPageContent = $langWikiMainPageContent;

                $wikiPage = new WikiPage($wikiId);
                $wikiPage->create($creatorId, '__MainPage__', $mainPageContent, '', date("Y-m-d H:i:s"), true);

                $message = $langWikiCreationSucceed;
                $style = "success";
            } elseif ($wikiStore->wikiIdExists($wikiId)) {
                $wiki = $wikiStore->loadWiki($wikiId);
                $wiki->setTitle($wikiTitle);
                $wiki->setDescription($wikiDesc);
                $wiki->setACL($wikiACL);
                $wiki->setGroupId($groupId);
                $wikiId = $wiki->save();

                $message = $langWikiEditionSucceed;
                $style = "success";
            } else {
                $message = $langWikiInvalidWikiId;
                $action = "error";
                $style = "caution";
            }

            $action = 'list';
        } else {
            $new_or_edit = $wikiId ? "&wikiId=$wikiId" : "";
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/wiki/index.php?course=$course_code&gid=$groupId&action=rqEdit$new_or_edit");
        }
        // no break
    }
    // list wiki
    case "list": {
            if ($groupId == 0) {
                $wikiList = $wikiStore->getCourseWikiList();
            } else {
                $wikiList = $wikiStore->getWikiListByGroup($groupId);
            }
            break;
        }
}

// ------------ End of command processing ---------------

if ($action == 'rqEdit') {
    $jspath = $urlAppend . 'modules/wiki/lib/javascript';
    $htmlHeadXtra[] = "<script type='text/javascript' src='$jspath/wiki_acl.js'></script>";
    $claroBodyOnload[] = 'initBoxes();';
}

// Breadcrumps
switch ($action) {
    case "rqEdit": {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gid=$groupId", 'name' => $langWiki);
        $pageName = $langWikiCreateWiki;
        break;
    }
    case "list":
    default: {
        $pageName = $langWiki;
    }
}


// --------- Start of display ----------------

$toolTitle = array();

if ($groupId != 0) {
    $toolTitle['supraTitle'] = $group_name;
}

switch ($action) {
    // edit form
    case "rqEdit": {
            if ($wikiId == 0) {
                $toolTitle['mainTitle'] = $langWikiTitleNew;
            } else {
                $toolTitle['mainTitle'] = $langWikiTitleEdit;
                $toolTitle['subTitle'] = $wikiTitle;
            }
            break;
        }
    // list wiki
    case "list": {
            $toolTitle['mainTitle'] = sprintf($langWikiTitlePattern, $langWikiList);
            break;
        }
}

switch ($action) {
    // an error occurs
    case "error": {
            break;
    }
    // edit form
    case "rqEdit": {
            $tool_content .= claro_disp_wiki_properties_form($wikiId, $wikiTitle, $wikiDesc, $groupId, $wikiACL);
            break;
        }
    // list wiki
    case "list": {
            if (!empty($message)) {
                $tool_content .= "<div class='alert alert-success'>$message</div>";
            }
            if ($is_editor) {
                $tool_content .= action_bar(array(
                        array('title' => $langBack,
                              'url' => "$_SERVER[SCRIPT_NAME]'?course=$course_code",
                              'icon' => 'fa-reply',
                              'level' => 'primary-label',
                              'show' => isset($_GET['action'])),
                        array('title' => $langWikiCreateWiki,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gid=$groupId&amp;action=rqEdit",
                              'icon' => 'fa-plus-circle',
                              'level' => 'primary-label',
                              'button-class' => 'btn-success',
                              'show' => !isset($_GET['action']))
                        ),false);
            }

            // wiki list not empty
            if (is_array($wikiList) && count($wikiList) > 0) {

                $tool_content .= "<div class='row'>
                                  <div class='col-md-12'>
                                  <div class='table-responsive'>
                                  <table class='table-default'>";
                $tool_content .= "<tr class='list-header'>
                                    <th class='text-left' style='width: 30%;'>$langTitle</th>
                                    <th class='text-center'>$langDescription</th>
                                    <th class='text-center'>$langPages</th>";
                if ($is_editor) {
                    $tool_content .= "<th class='text-center'>" .icon('fa-gears'). "</th>";
                } else {
                    $tool_content .= "<th class='text-center'>$langWikiLastModification</th>";
                }
                $tool_content .= "</tr>";

                foreach ($wikiList as $entry) {
                    if ($entry->visible == 1) {
                        $vis_class = '';
                        $vis_message = $langViewHide;
                        $vis_icon = 'fa-eye-slash';
                        $vis_action = 'invisible';
                    } else {
                        $vis_class = 'not_visible';
                        $vis_message = $langViewShow;
                        $vis_icon = 'fa-eye';
                        $vis_action = 'visible';
                    }
                    // display title for all users
                    $tool_content .= "<tr class = '$vis_class'><td>";
                    // display direct link to main page
                    $tool_content .= '<a class="item" href="page.php?course=' . $course_code . '&amp;wikiId='
                            . $entry->id . '&amp;action=show'
                            . '">'
                            . $entry->title . '</a>';
                    $tool_content .= '</td>';

                    $tool_content .= '<td class="text-center">';
                    if (!empty($entry->description)) {
                        $tool_content .= $entry->description;
                    } else {
                        $tool_content .= "<span class='not_visible'>$langWikiNoDescription</span>";
                    }
                    $tool_content .= "  </td>
                                        <td class='text-center'>
                                            <a href='page.php?course=$course_code&amp;wikiId=$entry->id&amp;action=all'>
                                                " . $wikiStore->getNumberOfPagesInWiki($entry->id) . "
                                            </a>
                                        </td>";
                    if ($is_editor) {
                        $tool_content.= "<td class='option-btn-cell'>";
                        $tool_content .=
                                action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gid=$groupId&amp;wikiId=$entry->id&amp;action=rqEdit",
                                          'icon' => 'fa-edit'),
                                    array('title' => $vis_message,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gid=$groupId&amp;wikiId=$entry->id&amp;action=$vis_action",
                                          'icon' => $vis_icon),
                                    array('title' => $langWikiRecentChanges,
                                          'url' => "page.php?course=$course_code&amp;wikiId=$entry->id&amp;action=recent",
                                          'icon' => "fa-clock-o"),
                                    array('title' => $langWikiExport,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gid=$groupId&amp;wikiId=$entry->id&amp;action=exExport",
                                          'icon' => "fa-download"),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gid=$groupId&amp;wikiId=$entry->id&amp;action=exDelete",
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => $langWikiDeleteWiki)
                                ));
                        $tool_content.= "</td>";
                    } else { // student view
                        $last_modification = current($wikiStore->loadWiki($entry->id)->recentChanges());
                        if ($last_modification){
                            $tool_content .= "<td class='text-center'>
                                            " . q(user_get_data($last_modification->editor_id)->givenname) . "<br/>"
                                              . format_locale_date(strtotime($last_modification->last_mtime), 'short')."
                                                </td>";
                        } else {
                            $tool_content .= "<td class='text-center not_visible'>$langWikiNoModifications</td>";
                        }
                    }
                    $tool_content .= '</tr>';
                }
                $tool_content .= '</table></div></div></div>';
            }
            // wiki list empty
            else {
                $tool_content .= '<div class="alert alert-warning">' . $langWikiNoWiki . '</div>';
            }
            break;
        }
    default:
        break;
}

// ------------ End of display ---------------
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);

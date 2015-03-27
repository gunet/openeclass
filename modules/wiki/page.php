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


/* ===========================================================================
  page.php
  @last update: 15-05-2007 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7.9 licensed under GPL
  copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)

  original file: page Revision: 1.61.2.4

  Claroline authors: Frederic Minne <zefredz@gmail.com>
  ==============================================================================
  @Description:

  @Comments:

  @todo:
  ==============================================================================
 */
$tlabelReq = 'CLWIKI__';
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Wiki';
require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/textLib.inc.php';
$style = '';

//check if groups are enabled for this course
//if not user will be shown the course wiki page
$sql = "SELECT `wiki` FROM `group_properties` WHERE course_id = ?d";
$result = Database::get()->querySingle($sql, $course_id);
if (is_object($result) && $result->wiki == 1) {
    $is_groupAllowed = true;
} else {
    $is_groupAllowed = false;
}

$sql = "SELECT COUNT(`user_id`) as c FROM `course_user` WHERE `course_id` = ?d AND `user_id` = ?d";
$result = Database::get()->querySingle($sql, $course_id, $uid);
if ($result->c > 0) {
    $is_courseMember = true;
} else {
    $is_courseMember = false;
}
$toolName = $langWiki;

// check and set user access level for the tool
if (!isset($_REQUEST['wikiId'])) {
    header("Location: index.php?course=$course_code");
    exit();
}

// Wiki specific classes and libraries
require_once 'modules/wiki/lib/class.wiki2xhtmlrenderer.php';
require_once 'modules/wiki/lib/class.wikipage.php';
require_once 'modules/wiki/lib/class.wikistore.php';
require_once 'modules/wiki/lib/class.wiki.php';
require_once "modules/wiki/lib/class.wikisearchengine.php";
require_once 'modules/wiki/lib/class.lockmanager.php';
require_once 'modules/wiki/lib/lib.requestfilter.php';
require_once 'modules/wiki/lib/lib.wikidisplay.php';
require_once 'modules/wiki/lib/lib.javascript.php';

// set request variables
$wikiId = (isset($_REQUEST['wikiId'])) ? intval($_REQUEST['wikiId']) : 0;

// security fix : disable access to other groups wiki
// and to wikis from non-existent groups or disabled group wikis
$sql = "SELECT `group_id` "
        . "FROM `wiki_properties` "
        . "WHERE `id` = ?d"
        . " AND `course_id` = ?d"
;

$result = Database::get()->querySingle($sql, $wikiId, $course_id);

if (is_object($result)) {
    $groupId = $result->group_id;
    if ($groupId != 0) {
        
        if ($is_groupAllowed) {
            //check if user is group member
            $sql = "SELECT `user_id` FROM `group_members`  WHERE user_id = ?d and group_id = ?d";
            $result = Database::get()->querySingle($sql, $uid, $groupId);
            if (is_object($result)) {
            	$is_groupMember = true;
            } else {
            	$is_groupMember = false;
            }
            
            $sql = "SELECT `name` FROM `group` WHERE `id` = ?d";
            $result = Database::get()->querySingle($sql, $groupId);
            if (is_object($result)) {
            	$group_name = $result->name;
            	$navigation[] = array('url' => '../group/index.php?course=' . $course_code, 'name' => $langGroups);
            	$navigation[] = array('url' => '../group/group_space.php?course=' . $course_code, 'name' => $group_name);
            }    
        } else {//redirect user to course wiki
            header("Location: index.php?course=$course_code");
            exit();
        }
    }
} else {
    $groupId = 0;
}

// Objects instantiation
$wikiStore = new WikiStore();

if (!$wikiStore->wikiIdExists($wikiId)) {
    die($langWikiInvalidWikiId);
    draw($tool_content, 2, null, $head_content);
}

$wiki = $wikiStore->loadWiki($wikiId);
$wikiPage = new WikiPage($wikiId);
$wikiRenderer = new Wiki2xhtmlRenderer($wiki);

$accessControlList = $wiki->getACL();

// --------------- Start of access rights management --------------
// Wiki access levels

$is_allowedToEdit = false;
$is_allowedToRead = false;
$is_allowedToCreate = false;

// set user access rights using user status and wiki access control list

if ($groupId != 0 && $is_groupAllowed) {
    // group_context
    if (is_array($accessControlList)) {
        $is_allowedToRead = $is_editor || ( $is_groupMember && WikiAccessControl::isAllowedToReadPage($accessControlList, 'group') ) || ( $is_courseMember && WikiAccessControl::isAllowedToReadPage($accessControlList, 'course') ) || WikiAccessControl::isAllowedToReadPage($accessControlList, 'other');
        $is_allowedToEdit = $is_editor || ( $is_groupMember && WikiAccessControl::isAllowedToEditPage($accessControlList, 'group') ) || ( $is_courseMember && WikiAccessControl::isAllowedToEditPage($accessControlList, 'course') ) || WikiAccessControl::isAllowedToEditPage($accessControlList, 'other');
        $is_allowedToCreate = $is_editor || ( $is_groupMember && WikiAccessControl::isAllowedToCreatePage($accessControlList, 'group') ) || ( $is_courseMember && WikiAccessControl::isAllowedToCreatePage($accessControlList, 'course') ) || WikiAccessControl::isAllowedToCreatePage($accessControlList, 'other');
    }
} else {
    // course context
    if (is_array($accessControlList)) {
        // course member
        if ($is_courseMember) {
            $is_allowedToRead = $is_editor || WikiAccessControl::isAllowedToReadPage($accessControlList, 'course');
            $is_allowedToEdit = $is_editor || WikiAccessControl::isAllowedToEditPage($accessControlList, 'course');
            $is_allowedToCreate = $is_editor || WikiAccessControl::isAllowedToCreatePage($accessControlList, 'course');
        }
        // not a course member
        else {
            $is_allowedToRead = $is_editor || WikiAccessControl::isAllowedToReadPage($accessControlList, 'other');
            $is_allowedToEdit = $is_editor || WikiAccessControl::isAllowedToEditPage($accessControlList, 'other');
            $is_allowedToCreate = $is_editor || WikiAccessControl::isAllowedToCreatePage($accessControlList, 'other');
        }
    }
}

// --------------- End of  access rights management ----------------
// filter action

if ($is_allowedToEdit || $is_allowedToCreate) {
    $valid_actions = array("edit", "preview", "save"
        , "delete", "show", "recent", "diff", "all", "history"
        , "rqSearch", "exSearch"
    );
} else {
    $valid_actions = array("show", "recent", "diff", "all"
        , "history", "rqSearch", "exSearch"
    );
}

$_CLEAN = filter_by_key('action', $valid_actions, "R", false);

$action = ( isset($_CLEAN['action']) ) ? $_CLEAN['action'] : 'show';

// get request variables

$creatorId = $uid;

$versionId = ( isset($_REQUEST['versionId']) ) ? intval($_REQUEST['versionId']) : 0;

$wiki_title = ( isset($_REQUEST['title']) ) ? strip_tags(rawurldecode($_REQUEST['title'])) : '';

$changelog = ( isset($_POST['changelog']) ) ? strip_tags($_POST['changelog']) : '';

if ($action == "diff") {
    $old = ( isset($_REQUEST['old']) ) ? intval($_REQUEST['old']) : 0;
    $new = ( isset($_REQUEST['new']) ) ? intval($_REQUEST['new']) : 0;
}

// get content

if ($action == "edit") {
    if (isset($_REQUEST['wiki_content'])) {
        if (!get_magic_quotes_gpc()) {
            $content = ( $_REQUEST['wiki_content'] == '' ) ? "__CONTENT__EMPTY__" : $_REQUEST['wiki_content'];
        } else {
            $content = ( $_REQUEST['wiki_content'] == '' ) ? "__CONTENT__EMPTY__" : $_REQUEST['wiki_content'];
        }
    } else {
        $content = '';
    }
} else {
    if (!get_magic_quotes_gpc()) {
        $content = ( isset($_REQUEST['wiki_content']) ) ? $_REQUEST['wiki_content'] : '';
    } else {
        $content = ( isset($_REQUEST['wiki_content']) ) ? $_REQUEST['wiki_content'] : '';
    }
}

// use __MainPage__ if empty title

if ($wiki_title === '') {
    // create wiki main page in a localisation compatible way
    $wiki_title = '__MainPage__';

    if ($wikiStore->pageExists($wikiId, $wiki_title)) {
        // do nothing
    } else {
        // something weird's happened
        die("$langWrongWikiPageTitle");
        draw($tool_content, 2, null, $head_content);
    }
}

// --------- Start of wiki command processing ----------
// init message
$message = '';

switch ($action) {
    case 'rqSearch': {
        break;
    }
    case 'exSearch': {
        $pattern = isset($_REQUEST['searchPattern']) ? trim($_REQUEST['searchPattern']) : null;
        
        if (!empty($pattern)) {
            $searchEngine = new WikiSearchEngine();
            $searchResult = $searchEngine->searchInWiki($pattern, $wikiId, CLWIKI_SEARCH_ANY);

            if (is_null( $searchResult )) {
                $searchResult = array();
            }
        
            $wikiList = $searchResult;
        }
        else {
            $message = $langWikiSearchMissingKeywords;
            $style = 'caution';
            $action = 'rqSearch';
        }
        break;
    }
    // show differences
    case 'diff': {
        include 'modules/wiki/lib/lib.diff.php';

        if ($wikiStore->pageExists($wikiId, $wiki_title)) {
            // older version
            $wikiPage->loadPageVersion($old);
            $old = $wikiPage->getContent();
            $oldTime = $wikiPage->getCurrentVersionMtime();
            $oldEditor = $wikiPage->getEditorId();

            // newer version
            $wikiPage->loadPageVersion($new);
            $new = $wikiPage->getContent();
            $newTime = $wikiPage->getCurrentVersionMtime();
            $newEditor = $wikiPage->getEditorId();

            // get differences
            $diff = '<table style="border: 0;">' . diff($old, $new, true, 'format_table_line') . '</table>';
        }
        break;
    }
    // page history
    //case 'history':
    // recent changes
    case 'recent': {
        $recentChanges = $wiki->recentChanges();
        break;
    }
    // all pages
    case 'all': {
        $allPages = $wiki->allPages();
        break;
    }
    // edit page content
    case 'edit': {
        
        $lock_manager = new LockManager();
        
        //require a lock for this page
        $gotLock = $lock_manager->getLock($wiki_title, $wikiId, $uid);
        
        if ($gotLock) {//succesfully locked page
            
            if ($wikiStore->pageExists($wikiId, $wiki_title)) {
            	if ($versionId == 0) {
            		$wikiPage->loadPage($wiki_title);
            	} else {
            		$wikiPage->loadPageVersion($versionId);
            	}
            	if ($content == '') {
            		$content = $wikiPage->getContent();
            	}
            	if ($content == "__CONTENT__EMPTY__") {
            		$content = '';
            	}
            
            	$wiki_title = $wikiPage->getTitle();
            } else {
            	if ($content == '') {
            		$message = $langWikiNoContent;
            		$style = 'alert-warning';
            	}
            }
        } else {//already locked by another user
            $action = 'conflict';
        }
        
        break;
    }
    // view page
    case 'show': {
        if ($wikiStore->pageExists($wikiId, $wiki_title)) {
            if ($versionId == 0) {
                $wikiPage->loadPage($wiki_title);
            } else {
                $wikiPage->loadPageVersion($versionId);
            }

            $content = $wikiPage->getContent();

            $wiki_title = $wikiPage->getTitle();
        } else {
            $message = $langWikiPageNotFound;
            $style = 'alert-warning';
        }
        break;
    }
    // save page
    case 'save': {
        $lock_manager = new LockManager();
        
        //require a lock for this page
        $gotLock = $lock_manager->getLock($wiki_title, $wikiId, $uid);
        
        if ($gotLock) {//a lock was acquired, so we can proceed in saving
            if(isset($_REQUEST['current']) AND $_REQUEST['current']=='yes') {
            	$wikiPage->loadPageVersion($versionId);
            	$content = $wikiPage->getContent();
            	$changelog = $langWikiPageRevertedVersion;
            	$versionId = 0;
            }
            
            if (isset($content)) {
            	$time = date('Y-m-d H:i:s');
            
            	if ($wikiPage->pageExists($wiki_title)) {
            		$wikiPage->loadPage($wiki_title);
            		if ($content == $wikiPage->getContent()) {
            
            			$message = $langWikiIdenticalContent;
            			$style = 'caution';
            			$action = 'show';
            		} else {
            			$wikiPage->edit($creatorId, $content, $changelog, $time, true);
            			if ($wikiPage->hasError()) {
            				$message = "Database error : " . $wikiPage->getError();
            				$style = "caution";
            			} else {
            				$message = $langWikiPageSaved;
            				$style = "success";
            			}
            			$action = 'show';
            		}
            	} else {
            		$wikiPage->create($creatorId, $wiki_title, $content, $changelog, $time, true);
            		if ($wikiPage->hasError()) {
            			$message = 'Database error : ' . $wikiPage->getError();
            			$style = 'caution';
            		} else {
            			$message = $langWikiPageSaved;
            			$style = 'success';
            		}
            		$action = 'show';
            	}
            }
            //release the lock after finishing saving
            $lock_manager->releaseLock($wiki_title, $wikiId);
        } else {//failed to lock, unable to save
            $action = 'conflict';
        }
        
        break;
    }
    // page history
    case 'history': {
        $wikiPage->loadPage($wiki_title);
        $wiki_title = $wikiPage->getTitle();
        $history = $wikiPage->history(0, 0, 'DESC');
        break;
    }
}

// change to use empty page content

if (!isset($content)) {
    $content = '';
}

// --------- End of wiki command processing -----------
// --------- Start of wiki display --------------------
// set xtra head

$jspath = document_web_path() . '/lib/javascript';

// set image repository
$head_content .= "<script type=\"text/javascript\">"
        . "\nvar sImgPath = '$themeimg'"
        . "\n</script>\n"
;

$head_content .= '<link rel="stylesheet" type="text/css" href="'.$urlServer.'modules/wiki/style.css">';

//navigation bar
if (!add_units_navigation()) {
    $navigation[] = array('url' => 'index.php?course=' . $course_code . '&amp;gid=' . $groupId, 'name' => $langWiki);
    $navigation[] = array('url' => 'page.php?course=' . $course_code . '&amp;wikiId=' . $wikiId . '&amp;action=show', 'name' => $wiki->getTitle());
}

switch ($action) {
    case "edit": {
            $dispTitle = ( $wiki_title == "__MainPage__" ) ? $langWikiMainPage : $wiki_title;
            $navigation[] = array('url' => 'page.php?course=' . $course_code . '&amp;action=show&amp;wikiId='
                . $wikiId . '&amp;title=' . $wiki_title
                , 'name' => $dispTitle);

            $pageName = $langEdit;
            break;
        }
    case "all": {
            $pageName = $langWikiAllPages;
            break;
        }
    case "recent": {
            $pageName = $langWikiRecentChanges;
            break;
        }
    case "rqSearch": {
        	$pageName = $langSearch;
        	break;
        }
    case "exSearch": {
        	$pageName = $langSearch;
        	break;
        }   
    case "history": {
            $dispTitle = ( $wiki_title == "__MainPage__" ) ? $langWikiMainPage : $wiki_title;
            $navigation[] = array('url' => 'page.php?course=' . $course_code . '&amp;action=show&amp;wikiId='
                . $wikiId . '&amp;title=' . $wiki_title
                , 'name' => $dispTitle);
            $pageName = $langWikiPageHistory;
            break;
        }
    default: {
            $pageName = ( $wiki_title == "__MainPage__" ) ? $langWikiMainPage : $wiki_title;
        }
}

// tool title

$toolTitle = array();
$toolTitle['mainTitle'] = sprintf($langWikiTitlePattern, $wiki->getTitle());

if ($groupId != 0) {
    $toolTitle['supraTitle'] = $group_name;
}

switch ($action) {
    case "all": {
        $toolTitle['subTitle'] = $langWikiAllPages;
        break;
    }
    case "recent": {
        $toolTitle['subTitle'] = $langWikiRecentChanges;
        break;
    }
    case "history": {
        $toolTitle['subTitle'] = $langWikiPageHistory;
        break;
    }
    case 'rqSearch':
    case 'exSearch': {
        $toolTitle['subTitle'] = $langWikiSearchInPages;
    }
    default: {
        $subTitle = ( $wiki_title == "__MainPage__" ) ? $langWikiMainPage : $wiki_title;
        break;
    }
}

if (!empty($message)) {
    $tool_content .= "<div class='alert $style'>$message</div>";
}

// user is not allowed to read this page

if (!$is_allowedToRead) {
    $tool_content .= $langWikiNotAllowedToRead;
    draw($tool_content, 2, null, $head_content);
    die;
}


if ($action != "edit" && $action != "history" && $action != "diff") {
// Wiki navigation bar
$tool_content .= action_bar(array(
    array(
        'title' => $langWikiMainPage,
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=". $wiki->getWikiId() ."&amp;action=show&amp;title=__MainPage__",
        'icon' => 'fa-wikipedia',
        'level' => 'primary-label'
    ),        
    array(
        'title' => $langWikiRecentChanges,
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=". $wiki->getWikiId() ."&amp;action=recent",
        'icon' => 'fa-history',
        'level' => 'primary'
    ),
    array(
        'title' => $langWikiAllPages,
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=". $wiki->getWikiId() ."&amp;action=all",
        'icon' => 'fa-files-o',
        'level' => 'primary'
    ),   
    array(
        'title' => $langSearch,
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=" . $wiki->getWikiId() . "&amp;action=rqSearch",
        'icon' => 'fa-search',
        'level' => 'primary'
    ),
    array(
        'title' => $langWikiList,
        'url' => "index.php?course=$course_code&amp;gid=$groupId",
        'icon' => 'fa-list',
        'level' => 'primary'
    )    
));    
}


if ($action == "edit" || $action == "diff" || $action == "history" || $action == "conflict") {
    $tool_content .= action_bar(array(
        array(
            'title' => $langWikiBackToPage,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=" . $wiki->getWikiId() . "&amp;action=show&amp;title=". rawurlencode($wiki_title)
        )
    ));

}


switch ($action) {
    case "diff": {
            $oldTime = nice_format($oldTime, true);

            $userInfo = user_get_data($oldEditor);
            $oldEditorStr = q($userInfo->givenname) . "&nbsp;" . q($userInfo->surname);

            $newTime = nice_format($newTime, TRUE);

            $userInfo = user_get_data($newEditor);
            $newEditorStr = q($userInfo->givenname) . "&nbsp;" . q($userInfo->surname);

            $tool_content .= "<div class='alert alert-info'>
                             ". sprintf($langWikiDifferencePattern, $oldTime, $oldEditorStr, $newTime, $newEditorStr) ."
                             </div>
                             <strong>$langWikiDifferenceKeys</strong>";

            $tool_content .= '<div class="diff">' . "\n";
            $tool_content .= '= <span class="diffEqual" >' . $langWikiDiffUnchangedLine . '</span><br />';
            $tool_content .= '+ <span class="diffAdded" >' . $langWikiDiffAddedLine . '</span><br />';
            $tool_content .= '- <span class="diffDeleted" >' . $langWikiDiffDeletedLine . '</span><br />';
            $tool_content .= 'M <span class="diffMoved" >' . $langWikiDiffMovedLine . '</span><br />';
            $tool_content .= '</div>' . "\n";
            $tool_content .= '<strong>' . $langWikiDifferenceTitle . '</strong>';
            $tool_content .= '<div class="diff">' . "\n";
            $tool_content .= $diff;
            $tool_content .= '</div>' . "\n";

            break;
        }
    case "recent": {
            if (is_array($recentChanges)) {
                $tool_content .= '<div class="list-group">' . "\n";

                foreach ($recentChanges as $recentChange) {
                    $pgtitle = ( $recentChange->title == "__MainPage__" ) ? $langWikiMainPage : $recentChange->title
                    ;

                    $entry = '<a class="list-group-item" href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;wikiId='
                            . $wikiId . '&amp;title=' . rawurlencode($recentChange->title)
                            . '&amp;action=show"'
                            . '><strong>' . $pgtitle . '</strong><small>'
                    ;

                    $time = nice_format($recentChange->last_mtime,TRUE);

                    $userInfo = user_get_data($recentChange->editor_id);

                    $userStr = q($userInfo->givenname) . "&nbsp;" . q($userInfo->surname);
                    $userUrl = $userStr;
                    $tool_content .=
                            sprintf($langWikiRecentChangesPattern, $entry, $time, $userUrl)
                            . '</small></a>'
                            . "\n"
                    ;
                }

                $tool_content .= '</div>' . "\n";
            }
            break;
        }
    case "all": {
            // handle main page

            $tool_content .= '<div class="list-group"><a class="list-group-item" href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code
                    . '&amp;wikiId=' . $wikiId
                    . '&amp;title=' . rawurlencode("__MainPage__")
                    . '&amp;action=show">'
                    . $langWikiMainPage
                    . '</a>'
            ;

            // other pages

            if (is_array($allPages)) {

                foreach ($allPages as $page) {
                    if ($page->title == "__MainPage__") {
                        // skip main page
                        continue;
                    }

                    $pgtitle = rawurlencode($page->title);

                    $link = '<a class="list-group-item" href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;wikiId='
                            . $wikiId . '&amp;title=' . $pgtitle . '&amp;action=show"'
                            . '>' . $page->title . '</a>'
                    ;

                    $tool_content .= $link . "\n";
                }
                $tool_content .= '</div>' . "\n";
            }
            break;
        }
    case "conflict": {
   
    	$tool_content .= '<div class="wikiTitle">' . "\n";
    	$tool_content .= '<h2>' . $langWikiEditLock
    	. '</h2>'
        . "\n"
    	;
    	
		$tool_content .= '</div>' . "\n";
		$message = $langWikiLockInfo;
		$tool_content .= "<div class='alert alert-danger'>$message</div></br />";


		if (isset($content) && $content != '') {
		    //proceed to edit for, in order to save the content later
			$action = 'edit';
			$pre_action = 'conflict';
		} else {
		    break;
		}
    }    
    // edit page
    case "edit": {
            if (!$wiki->pageExists($wiki_title) && !$is_allowedToCreate) {
                $tool_content .= $langWikiNotAllowedToCreate;
            } elseif ($wiki->pageExists($wiki_title) && !$is_allowedToEdit) {
                $tool_content .= $langWikiNotAllowedToEdit;
            } else {
                $script = $_SERVER['SCRIPT_NAME'] . "?course=$course_code";
                
                //Do not show progress bar if a lock conflict was detected
                if (!isset($pre_action) || $pre_action != 'conflict') {
                    //add lock time progress bar
                    
                    $head_content .= "<script type='text/javascript'>
                        function secondsToHms(d) {
                        d = Number(d);
                        var h = Math.floor(d / 3600);
                        var m = Math.floor(d % 3600 / 60);
                        var s = Math.floor(d % 3600 % 60);
                        return ((h > 0 ? h + \":\" : \"\") + (m > 0 ? (h > 0 && m < 10 ? \"0\" : \"\") + m + \":\" : \"0:\") + (s < 10 ? \"0\" : \"\") + s); }
                    </script>\n\n";
                    
                    $head_content .= "<script type='text/javascript'>
                                        	function countdown(callback) {
                                        	    var bar = document.getElementById('progress'),
                                        	    timer = document.getElementById('progresstime'),
                                        	    time = max = ".($lock_manager->lock_duration-5).",
                                        	            
                                        	    url = 'lib/confirmlock.php'
                                        	    data = { uid : ".$uid.", page_title : \"".rawurlencode($wiki_title)."\", wiki_id : ".$wikiId." }
                                        	            
                                        	    int = setInterval(function() {    
                                        	    	timer.innerHTML = secondsToHms(time);
                                        	        bar.style.width = Math.floor(100 * time-- / max) + '%';
                                        	        if (time + 1 == 0) {
                                        	            clearInterval(int);
                                        	            // 600ms - width animation time
                                        	            callback && setTimeout(callback, 600);
                                        	        }
                                        	        if ((max - time) % 25 == 0) {//ajax polling to keep lock alive   
                                        	            $.post(url, data);    
                                        	        }
                                        	    }, 1000);
                                        	}
                                        	            
                                        	$(document).ready(function(){
                                        	    countdown(function() {
                                        	        bootbox.alert('".$langWikiLockTimeEnd."');
                                        	    });
                                            })
                                    </script>\n";
                    
                    $tool_content .= "  <div>
                                            $langWikiLockTimeRemaining
                                            <span id='progresstime'>".intval(gmdate('i', $lock_manager->lock_duration-5)).":".gmdate('s', $lock_manager->lock_duration-5)."</span>
                                        </div>
                                        <div class='progress'>
                                            <div class='progress-bar progress-bar-striped active' id='progress'></div>
                                        </div>
                                        <noscript>
                                            <div>
                                                <img src='lib/nojslock.php?uid=$uid&amp;page_title=".urlencode($wiki_title)."&amp;wiki_id=$wikiId'>
                                            </div>
                                       </noscript>";
                }
                
                $tool_content .= claro_disp_wiki_editor($wikiId, $wiki_title, $versionId, $content, $changelog, $script
                        , true, false)
                ;
            }

            break;
        }
        //delete page
        case "delete": {
             if($wiki_title != "__MainPage__" and $is_editor) { //only a teacher can delete a page
                 if ($wikiStore->pageExists($wikiId, $wiki_title)) {
                     $wikiPage->loadPage($wiki_title);
                     if ($wikiPage->delete()) {
                         Session::Messages($langWikiPageDeleted, 'alert-success');
                         redirect_to_home_page("modules/wiki/page.php?course=$course_code&wikiId=$wikiId&action=show");
                     } else {
                         Session::Messages($langWikiDeletePageError, 'alert-danger');
                         redirect_to_home_page("modules/wiki/page.php?course=$course_code&action=show&title=".rawurlencode($wiki_title)."&wikiId=$wikiId");                         
                     }
                 } else {
                    Session::Messages($langWikiPageNotFound);
                    redirect_to_home_page("modules/wiki/page.php?course=$course_code&wikiId=$wikiId&action=show");                     
                 }
             }
             break;
        }
    // page preview
    case "preview": {
            if (!isset($content)) {
                $content = '';
            }

            $tool_content .= claro_disp_wiki_preview($wikiRenderer, $wiki_title, $content);
            $tool_content .= claro_disp_wiki_preview_buttons($wikiId, $wiki_title, $content, $changelog);
            break;
        }
    // view page
    case "show": {
            if ($wikiPage->hasError()) {
                $tool_content .= $wikiPage->getError();
            } else {
                
                //unlock after edit cancellation
                //only if current user is the lock owner (to avoid unlocking with GET)
                $lock_manager = new LockManager();
                if ($lock_manager->getLockOwner($wiki_title, $wikiId) == $uid) {
                    $lock_manager->releaseLock($wiki_title, $wikiId);
                } 

                // get localized value for wiki main page title
                if ($wiki_title === '__MainPage__') {
                    //$displaytitle = $langWikiMainPage;
                    $displaytitle = '';
                } else {
                    //$displaytitle = $wiki_title;
                    $displaytitle = '';
                }


                if ($versionId != 0) {
                    $editorInfo = user_get_data($wikiPage->getEditorId());

                    $editorStr = q($editorInfo->givenname) . "&nbsp;" . q($editorInfo->username);

                    $editorUrl = '&nbsp;-&nbsp;' . $editorStr;

                    $mtime = nice_format($wikiPage->getCurrentVersionMtime(), true);
                    ;

                    $versionInfo = sprintf($langWikiVersionInfoPattern, $mtime, $editorUrl);

                    $versionInfo = '&nbsp;<span style="font-size: 10px; font-weight: normal; color: red;">'
                            . $versionInfo . '</span>'
                    ;
                } else {
                    $versionInfo = '';
                }
                
                if(isset($_GET['printable']) and $_GET['printable']=="yes") {
                    if ($versionId == 0) {
                	    $wikiPage->loadPage($wiki_title);
                	}
                    else {
                        $wikiPage->loadPageVersion($versionId);
                    }
                    
                    $htmltitle = ($wiki_title=='__MainPage__') ? $langWikiMainPage : $wiki_title;
                    
                    $style = '<style type="text/css">
                            table { border: black solid 1px; }
                            td { border: black solid 1px; }
                              </style>';
                    $printable_content = '<html><head><meta charset="utf-8"><title>'.
                                          $htmltitle.'</title>'.
                                          $style.'</head><body>';
                    
                    $printable_content .= '<h1>'.$htmltitle. '</h1>'."\n";
                    
                    $printable_content .= '<h3>'.$toolTitle['mainTitle'].'</h3><hr/>'."\n";
                    //remove the toc script (if it exists) with preg_replace
                    $printable_content .= preg_replace('#<script(.*?)>(.*?)</script>#is', '', $wikiRenderer->render($wikiPage->getContent()))."\n";
                    $printable_content .= '<hr>';
                		
                    $editorInfo = user_get_data($wikiPage->getEditorId());
                    $editorStr = $editorInfo->givenname . "&nbsp;" . $editorInfo->surname;
                    $editorUrl = '&nbsp;-&nbsp;' . $editorStr;
                
                    $cur_ver_time = $wikiPage->getCurrentVersionMtime();
                    $time = explode(" ", $cur_ver_time);
                    
                    $mtime = nice_format($wikiPage->getCurrentVersionMtime(), TRUE);
                
                	$versionInfo = sprintf($langWikiVersionInfoPattern, $mtime, $editorUrl);
                	$printable_content .= $versionInfo;
                	$printable_content .= '</body></html>';
                }
                else {           
                    $wiki_content = "<div id='mainContent' class='wiki2xhtml'>
                                    ". $wikiRenderer->render($content) ."
                                    </div>";
                }
            }

            break;
        }
    case "history": {
        if ($wiki_title === '__MainPage__') {
            //$displaytitle = $langWikiMainPage;
            $displaytitle = '';
        } else {
            //$displaytitle = $wiki_title;
            $displaytitle = '';
        }

        $tool_content .= '<div class="wikiTitle">' . "\n";
        $tool_content .= '<h2>' . $displaytitle . '</h2>' . "\n";
        $tool_content .= '</div>' . "\n";

        $tool_content .= '<form id="differences" method="GET" action="'
                . $_SERVER['SCRIPT_NAME']
                . '">'
                . "\n"
        ;

        $tool_content .= '<div>' . "\n"
                . '<input type="hidden" name="course" value="' . $course_code . '" />' . "\n"
                . '<input type="hidden" name="wikiId" value="' . $wikiId . '" />' . "\n"
                . '<input type="hidden" name="title" value="' . $wiki_title . '" />' . "\n"
                . '</div>' . "\n"
        ;

        $tool_content .= '<table class="table-default">' . "\n";

        if (is_array($history)) {
            $firstPass = true;

            foreach ($history as $version) {
                $tool_content .= '<tr>' . "\n";

                if ($firstPass == true) {
                    $checked = ' checked="checked"';
                    $makecurrent = '';
                    $firstPass = false;
                } else {
                    $checked = '';
                    if ($is_allowedToEdit || $is_allowedToCreate) {
                        $makecurrent = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?course='.$course_code.'&amp;wikiId='
                            . $wikiId . '&amp;title=' . rawurlencode($wiki_title)
                            . '&amp;action=save&amp;current=yes&amp;versionId=' . $version->id
                            . '" onClick="return confirm(\''.$langSureToMakeWikiPageCurrent.'\');">'.$langWikiPageMakeCurrent.'</a>';
                    }
                }

                $tool_content .= '<td>'
                        . '<input type="radio" name="old" value="' . $version->id . '"' . $checked . ' />' . "\n"
                        . '</td>'
                        . "\n"
                ;

                $tool_content .= '<td>'
                        . '<input type="radio" name="new" value="' . $version->id . '"' . $checked . ' />' . "\n"
                        . '</td>'
                        . "\n"
                ;

                $userInfo = user_get_data($version->editor_id);                

                $userStr = q($userInfo->givenname) . "&nbsp;" . q($userInfo->surname);

                $userUrl = $userStr;

                $versionUrl = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;wikiId='
                        . $wikiId . '&amp;title=' . rawurlencode($wiki_title)
                        . '&amp;action=show&amp;versionId=' . $version->id
                        . '">'
                        . nice_format($version->mtime,TRUE)
                        . '</a>'
                ;

                $tool_content .= '<td>'
                        . sprintf($langWikiVersionPattern, $versionUrl, $userUrl)
                        . '</td><td>'.$makecurrent.'</td><td colspan="3"></td>'
                        . "\n"
                ;
                
                if ($version->changelog != '') {
                	$tool_content .='<td colspan="4">(<i>'.$version->changelog
                	. '</i>)</td>'
                	. "\n";
                }
                
                $tool_content .= '</tr>' . "\n";
            }
        }

        $tool_content .= '</table>' . "\n";
        $tool_content .= '<input class="btn btn-primary" type="submit" name="action[diff]" value="' . $langWikiShowDifferences . '" /></form>';
        break;
    }
    case 'exSearch':
    {
        $tool_content .= '<h3>'.$langWikiSearchResults.'</h3>' . "\n";

        if (!empty($searchResult)) {
            $tool_content .= '<ul>' . "\n";
    
            foreach ($searchResult as $page) {
                if ('__MainPage__' == $page->title) {
                    $title = $langWikiMainPage;
                }
                else {
                    $title = $page->title;
                }
    
                $urltitle = rawurlencode($page->title);
    
                $link = '<a href="'
                        . htmlspecialchars($_SERVER['SCRIPT_NAME'] . '?wikiId='
                        . $wikiId . '&title=' . $urltitle
                        . '&action=show') . '">' . $title . '</a>';
    
                $tool_content .= '<li>' . $link. '</li>' . "\n";
            }
            $tool_content .= '</ul>' . "\n";
        } else {
            $tool_content .= $langNoResult;
        }
        
        break;
    }
    case 'rqSearch':
    {
        $tool_content .= "
            <div class='form-wrapper'>
                <form class='form-inline' role='form' method='post' action='". htmlspecialchars($_SERVER['SCRIPT_NAME'].'?wikiId='.$wikiId.'&course='.$course_code)."'>
                    <input type='hidden' name='action' value='exSearch'>
                    <div class='form-group'>
                        <label for='searchPattern'>$langSearch:</label>
                            <div class='input-group'>
                                <input class='form-control' type='text' id='searchPattern' name='searchPattern' placeholder='$langSearch'>
                                <div class='input-group-btn'>
                                    <input class='btn btn-primary' type='submit' value='". $langSubmit ."'>
                                        <a class='btn btn-default' href='".htmlspecialchars($_SERVER['SCRIPT_NAME'].'?wikiId='.$wikiId.'&course='.$course_code)."'>$langCancel</a>
                                </div>
                            </div>
                    </div>
                </form>
            </div>";
        break;
    }
    default: {
        trigger_error("Invalid action supplied to " . $_SERVER['SCRIPT_NAME'], E_USER_ERROR);
    }
}
$print_button = icon('fa-print', $langWikiPagePrintable, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=". $wiki->getWikiId(). "&amp;action=show&amp;printable=yes&amp;versionId=$versionId&amp;title=". rawurlencode($wiki_title));
if ($action == 'show' && (!isset($_GET['printable']) || $_GET['printable']!="yes")) {
        $tool_content .= "<div class='panel panel-action-btn-default'>
                                <div class='panel-heading'>
                                    <div class='pull-right'>
                                    ".action_button(array(
                                      array(
                                          'title' => $langWikiEditPage,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=".$wiki->getWikiId()."&amp;action=edit&amp;title=". rawurlencode($wiki_title). "&amp;versionId=$versionId",
                                          'icon' => 'fa-edit',
                                          'show' => ($is_allowedToEdit || $is_allowedToCreate)
                                      ),
                                      array(
                                          'title' => $langWikiDeletePage,
                                          'class' => 'delete',
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=".$wiki->getWikiId()."&amp;action=delete&amp;title=".rawurlencode($wiki_title),
                                          'icon' => 'fa-times',
                                          'confirm' => $langWikiDeletePageWarning,
                                          'show' => ($is_allowedToEdit || $is_allowedToCreate) && $wiki_title != "__MainPage__" && $is_editor
                                      ),
                                      array(
                                          'title' => $langWikiPageHistory,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=". $wiki->getWikiId(). "&amp;action=history&amp;title=". rawurlencode($wiki_title),
                                          'icon' => 'fa-history'
                                      ),
                                      array(
                                          'title' => $langWikiPagePrintable,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;wikiId=". $wiki->getWikiId(). "&amp;action=show&amp;printable=yes&amp;versionId=$versionId&amp;title=". rawurlencode($wiki_title),
                                          'icon' => 'fa-print'
                                      )                                        
                                    ))."</div>
                                    <h3 class='panel-title'>
                                        ". ( $wiki_title != "__MainPage__" ? $wiki_title : $langWikiMainPage) ." 
                                     </h3>
                                </div>
                                <div class='panel-body'>
                                    ". (isset($wiki_content) ? $wiki_content : "") ."
                                </div>
                          </div>";
}
add_units_navigation(TRUE);
if (isset($_GET['printable']) and $_GET['printable']=="yes") {
    print $printable_content;
}
else {
    draw($tool_content, 2, null, $head_content);
}


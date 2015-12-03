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
 * @file index.php
 * @brief Units display module
 */

define('HIDE_TOOL_TITLE', 1);
$require_current_course = true;
$guest_allowed = true;
$require_help = TRUE;
$helpTopic = 'AddCourseUnitscontent';

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/action.php';
require_once 'functions.php';
require_once 'modules/document/doc_init.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
}

$pageName = '';
$lang_editor = $language;
load_js('tools.js');
ModalBoxHelper::loadModalBox(true);

if (isset($_REQUEST['edit_submit'])) {
    units_set_maxorder();
    $tool_content .= handle_unit_info_edit();
}

$form = process_actions();

// check if we are trying to access a protected resource directly
$access = Database::get()->querySingle("SELECT public FROM course_units WHERE id = ?d", $id);
if ($access) {
    if (!resource_access(1, $access->public)) {
        $tool_content .= "<div class='alert alert-danger'>$langForbidden</div>";
        draw($tool_content, 2, null, $head_content);
        exit;    
    }
}

if ($is_editor) {
    $base_url = $urlAppend . "modules/units/insert.php?course=$course_code&amp;id=$id&amp;type=";
    $tool_content .= "
    <div class='row'>
        <div class='col-md-12'>" .
        action_bar(array(
            array('title' => $langEditUnitSection,
                  'url' => "info.php?course=$course_code&amp;edit=$id&amp;next=1",
                  'icon' => 'fa fa-edit',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success'),
            array('title' => $langAdd.' '.$langInsertExercise,
                  'url' => $base_url . 'exercise',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertDoc,
                  'url' => $base_url . 'doc',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertText,
                  'url' => $base_url . 'text',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertLink,
                  'url' => $base_url . 'link',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langLearningPath1,
                  'url' => $base_url . 'lp',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertVideo,
                  'url' => $base_url . 'video',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertForum,
                  'url' => $base_url . 'forum',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertEBook,
                  'url' => $base_url . 'ebook',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertWork,
                  'url' => $base_url . 'work',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertPoll,
                  'url' => $base_url . 'poll',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            array('title' => $langAdd.' '.$langInsertWiki,
                  'url' => $base_url . 'wiki',
                  'icon' => 'fa fa-paste',
                  'level' => 'secondary'),
            )) .
    "
    </div>
  </div>";
}

if ($is_editor) {
    $visibility_check = '';
} else {
    $visibility_check = "AND visible=1";
}
if (isset($id) and $id !== false) {
    $info = Database::get()->querySingle("SELECT * FROM course_units WHERE id = ?d AND course_id = ?d $visibility_check", $id, $course_id);
    if ($info) {
        $pageName = $info->title;
        $comments = trim($info->comments);
    } else {
        Session::Messages($langUnknownResType);
        redirect_to_home_page("courses/$course_code/");        
    }
}

// Links for next/previous unit
foreach (array('previous', 'next') as $i) {
    if ($i == 'previous') {
        $op = '<=';
        $dir = 'DESC';        
        $arrow1 = "<i class='fa fa-arrow-left space-after-icon'></i>";
        $arrow2 = '';
        $page_btn = 'pull-left';
    } else {
        $op = '>=';
        $dir = '';      
        $arrow1 = '';
        $arrow2 = "<i class='fa fa-arrow-right space-before-icon'></i>";
        $page_btn = 'pull-right';
    }
    
    if (isset($_SESSION['uid']) and isset($_SESSION['status'][$course_code]) and $_SESSION['status'][$course_code]) {
            $access_check = "";
    } else {
        $access_check = "AND public = 1";
    }
        
    $q = Database::get()->querySingle("SELECT id, title, public FROM course_units
                       WHERE course_id = ?d
                             AND id <> ?d
                             AND `order` $op $info->order
                             AND `order` >= 0
                             $visibility_check
                             $access_check
                       ORDER BY `order` $dir
                       LIMIT 1", $course_id, $id);
    if ($q) {
        $q_id = $q->id;
        $q_title = htmlspecialchars($q->title);         
        $link[$i] = "<a class='btn btn-default $page_btn' title='$q_title'  href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$q_id'>$arrow1". ellipsize($q_title, 30) ."$arrow2</a>";
    } else {
        $link[$i] = '&nbsp;';
    }
}

if ($link['previous'] != '&nbsp;' or $link['next'] != '&nbsp;') {
    $tool_content .= "
        <div class='row'>
            <div class='col-md-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        $link[previous]
                        $link[next]
                    </div>
                </div>
            </div>
        </div>";
}
$moduleTag = new ModuleElement($id);
$tags_list = $moduleTag->showTags();
$tool_content .= "<div class='row margin-bottom'>
      <div class='col-md-12'>
        
      </div>
    </div>

    <div class='row'>
      <div class='col-md-12'>
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <div class='panel-title h3'>".q($pageName)."</div>
            </div>
            <div class='panel-body'>$comments";
if (!empty($tags_list)) {
    $tool_content .= "
                    <div>
                        $langTags: $tags_list
                    </div>
                    ";
}
    $tool_content .= "    
            </div>          
        </div>
      </div>
    </div>
    <div class='row'>
  <div class='col-md-12'>
    <div class='panel padding'>";
show_resources($id);
$tool_content .= "
    </div>
  </div>
</div>";
$q = Database::get()->queryArray("SELECT id, title FROM course_units
             WHERE course_id = ?d AND `order` > 0
                   $visibility_check
             ORDER BY `order`", $course_id);
$course_units_options ='';
foreach ($q as $info) {
    $selected = ($info->id == $id) ? ' selected ' : '';
    $course_units_options .= "<option value='$info->id'$selected>" .
            htmlspecialchars(ellipsize($info->title, 50)) .
                '</option>';
}
$tool_content .= "
    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <form class='form-horizontal' name='unitselect' action='" . $urlServer . "modules/units/' method='get'>
                        <div class='form-group'>
                            <label class='col-sm-8 control-label'>$langCourseUnits</label>
                            <div class='col-sm-4'>
                                <select name='id' class='form-control' onChange='document.unitselect.submit();'>
                                    $course_units_options
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>";

draw($tool_content, 2, null, $head_content);

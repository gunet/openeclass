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
$require_help = TRUE;
$helpTopic = 'AddCourseUnitscontent';

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/action.php';
require_once 'functions.php';
require_once 'modules/document/doc_init.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
}

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
        $tool_content .= "<p class='caution'>$langForbidden</p>";
        draw($tool_content, 2, null, $head_content);
        exit;    
    }
}

if ($is_editor) {

    $comment_edit_link = "

    <a class='btn-default-eclass place-at-toolbox color-green' title='...' href='info.php?course=$course_code&amp;edit=$id&amp;next=1' rel='tooltip' data-toggle='tooltip' data-placement='top'>
      <i class='fa fa-edit'></i>
    </a>
    ";
    

    $tool_content .= "

<div class='row'>
  <div class='col-md-12'>
    
    <div class='toolbox pull-right margin-top-thin margin-bottom-thin'>
      

      <a class='btn-default-eclass place-at-toolbox' title='$langAdd $langInsertExercise' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...'>
        <i class='fa fa-paste space-after-icon'></i>$langAdd
      </a>
      <a class='btn-default-eclass place-at-toolbox' title='$langAdd $langInsertDoc' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...'>
        <i class='fa fa-file-o space-after-icon'></i>$langAdd
      </a>
      
      $comment_edit_link

    </div>


    <div class='toolbox pull-right margin-top-thin margin-bottom-thin margin-right'>
      <ul class='toolbox-submenu'>
        <li>
          $langAdd 
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langInsertText' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-file-text-o'></i>
          </a>
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langInsertLink' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-link'></i>
          </a>
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langLearningPath1' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-random'></i>
          </a>
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langInsertVideo' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-video-camera'></i>
          </a>
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langInsertForum' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-comment'></i>
          </a>
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langInsertEBook' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-book'></i>
          </a>
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langInsertWork' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-paste'></i>
          </a>
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langInsertPoll' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-paste'></i>
          </a>
        </li>
        <li>
          <a class='btn-default-eclass place-at-toolbox submenu-button' title='$langAdd langInsertWiki' rel='tooltip' data-toggle='tooltip' data-placement='top' href='...''>
            <i class='fa fa-paste'></i>
          </a>
        </li>
      </ul>
      <button class='btn-default-eclass place-at-toolbox' title=''>
        <i class='fa fa-th-large'></i>
      </button>
    </div>

  </div>
</div>


<div class='row' style='display:none;'>
  <div class='col-md-12'>

    <div id='operations_container'>
		<form name='resinsert' action='{$urlServer}modules/units/insert.php' method='get'>
		<select name='type' onChange='document.resinsert.submit();'>
			<option>-- $langAdd --</option>
			<option value='doc'>$langInsertDoc</option>
			<option value='exercise'>$langInsertExercise</option>
			<option value='text'>$langInsertText</option>
			<option value='link'>$langInsertLink</option>
			<option value='lp'>$langLearningPath1</option>
			<option value='video'>$langInsertVideo</option>
			<option value='forum'>$langInsertForum</option>
			<option value='ebook'>$langInsertEBook</option>
			<option value='work'>$langInsertWork</option>
      <option value='poll'>$langInsertPoll</option>
			<option value='wiki'>$langInsertWiki</option>                            
		</select>
		<input type='hidden' name='id' value='$id'>
		<input type='hidden' name='course' value='$course_code'>
		</form>
		</div>

  </div>
</div>" .
            $form;
}

if ($is_editor) {
    $visibility_check = '';
} else {
    $visibility_check = "AND visible=1";
}
if (isset($id) and $id !== false) {
    $info = Database::get()->querySingle("SELECT * FROM course_units WHERE id = ?d AND course_id = ?d $visibility_check", $id, $course_id);
}
if (!$info) {
    $nameTools = $langUnitUnknown;
    $tool_content .= "<p class='caution'>$langUnknownResType</p>";
    draw($tool_content, 2, null, $head_content);
    exit;
} else {
    $nameTools = htmlspecialchars($info->title);
    $comments = trim($info->comments);
}

// Links for next/previous unit
foreach (array('previous', 'next') as $i) {
    if ($i == 'previous') {
        $op = '<=';
        $dir = 'DESC';
        $arrow1 = '« ';
        $arrow2 = '';
    } else {
        $op = '>=';
        $dir = '';
        $arrow1 = '';
        $arrow2 = ' »';
    }
    
    if (isset($_SESSION['uid']) and (isset($_SESSION['status'][$currentCourse]) and $_SESSION['status'][$currentCourse])) {
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
        $link[$i] = "$arrow1<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$q_id'>$q_title</a>$arrow2";
    } else {
        $link[$i] = '&nbsp;';
    }
}



$tool_content .= "
<div class='row margin-bottom'>
  <div class='col-md-12'>
    <h3 class='page-title'>$nameTools</h3>
  </div>
</div>
";





if (!empty($comments)) {
    $tool_content .= "

<div class='row'>
  <div class='col-md-12'>
    <div class='panel padding'>

          $comments

    </div>
  </div>
</div>";
}



$tool_content .= "
<div class='row'>
  <div class='col-md-12'>
    <div class='panel padding'>";

show_resources($id);

$tool_content .= "

    </div>
  </div>
</div>";



$tool_content .= "
<div class='row'>
  <div class='col-md-12'>
    
    <div class='toolbox whole-row margin-top-thin margin-bottom-thin'>

    <a class='btn-default-eclass place-at-toolbox' title='Previous Chapter*' rel='tooltip' data-toggle='tooltip' data-placement='top' href=''>
      <i class='fa fa-arrow-left space-after-icon'></i>Previous Chapter*
    </a>    

    <a class='btn-default-eclass place-at-toolbox' title='Select Chapter' rel='tooltip' data-toggle='tooltip' data-placement='top' href=''>
      <i class='fa fa-angle-down space-after-icon'></i>Select Chapter*
    </a>

    

    <a class='btn-default-eclass place-at-toolbox' title='Next Chapter*' rel='tooltip' data-toggle='tooltip' data-placement='top' href=''>
      Next Chapter*<i class='fa fa-arrow-right space-before-icon'></i>
    </a>

    </div>
  </div>
</div>


<div class='row'>
  <div class='col-md-12'>
    
    <div class='toolbox whole-row margin-top-thin margin-bottom-thin'>
      

  
";
if ($link['previous'] != '&nbsp;' or $link['next'] != '&nbsp;') {
    $tool_content .= "


    

    ". $link['previous'] ."
    ". $link['next'] ."


    <form name='unitselect' action='" . $urlServer . "modules/units/' method='get'>
          <table width='99%' class='tbl'>
            <tr class='odd'>
              <td class='right'>" . $langCourseUnits . ":&nbsp;</td>
              <td width='50' class='right'>" .
              "<select name='id' onChange='document.unitselect.submit();'>";

$q = Database::get()->queryArray("SELECT id, title FROM course_units
               WHERE course_id = ?d AND `order` > 0
                     $visibility_check
               ORDER BY `order`", $course_id);
foreach ($q as $info) {
    $selected = ($info->id == $id) ? ' selected ' : '';
    $tool_content .= "<option value='$info->id'$selected>" .
            htmlspecialchars(ellipsize($info->title, 40)) .
            '</option>';
}
$tool_content .= "</select>
            </td>
          </tr>
        </table>
      </form>



    ";
}
$tool_content .= "

    </div>
  </div>
</div>";

draw($tool_content, 2, null, $head_content);
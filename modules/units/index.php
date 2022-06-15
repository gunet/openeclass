<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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

$require_current_course = true;
$guest_allowed = true;
$require_help = TRUE;
$helpTopic = 'course_units';
$helpSubTopic = 'units_actions';

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/action.php';
require_once 'functions.php';
require_once 'modules/document/doc_init.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/tc/functions.php';

doc_init();

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
}

$action = new action();
$action->record(MODULE_ID_UNITS);

$pageName = ''; // delete $pageName set in doc_init.php
$toolName = $langCourseUnits;
$lang_editor = $language;
load_js('tools.js');
load_js('sortable/Sortable.min.js');
ModalBoxHelper::loadModalBox(true);

$q = Database::get()->querySingle("SELECT flipped_flag FROM course WHERE code = ?s", $course_code);

if($q->flipped_flag =="2"){
     // Handle unit resource reordering
     if ($is_editor and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if (isset($_POST['toReorder'])) {
            reorder_table('unit_resources', 'unit_id', $id, $_POST['toReorder'],
                isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
            exit;
        }
    }
}else{
    // Handle unit resource reordering
    if ($is_editor and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if (isset($_POST['toReorder'])) {
            reorder_table('unit_resources', 'unit_id', $id, $_POST['toReorder'],
                isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
            exit;
        }
    }

}

if (isset($_REQUEST['edit_submit'])) {
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



if($q->flipped_flag ==2){
    if ($is_editor) {
        $tool_content .= "
        <div class='row'>
            <div class='col-md-12'>" .
            action_bar(array(
                array('title' => $langEdit,
                    'url' => "info.php?course=$course_code&amp;edit=$id&amp;next=1",
                    'icon' => 'fa fa-edit',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'))) .
                    "
                    </div>
                </div>";
    }
}else{
    if ($is_editor) {
        $base_url = $urlAppend . "modules/units/insert.php?course=$course_code&amp;id=$id&amp;type=";
        $tool_content .= "
        <div class='row'>
            <div class='col-md-12'>" .
            action_bar(array(
                array('title' => $langEdit,
                    'url' => "info.php?course=$course_code&amp;edit=$id&amp;next=1",
                    'icon' => 'fa fa-edit',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'),
                array('title' => $langAdd.' '.$langInsertExercise,
                    'url' => $base_url . 'exercise',
                    'icon' => 'fa fa-pencil-square-o',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_EXERCISE)),
                array('title' => $langAdd.' '.$langInsertDoc,
                    'url' => $base_url . 'doc',
                    'icon' => 'fa fa-folder-open-o',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_DOCS)),
                array('title' => $langAdd.' '.$langInsertText,
                    'url' => $base_url . 'text',
                    'icon' => 'fa fa-file-text-o',
                    'level' => 'secondary'),
                array('title' => $langAdd.' '.$langInsertLink,
                    'url' => $base_url . 'link',
                    'icon' => 'fa fa-link',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_LINKS)),
                array('title' => $langAdd.' '.$langLearningPath1,
                    'url' => $base_url . 'lp',
                    'icon' => 'fa fa-ellipsis-h',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_LP)),
                array('title' => $langAdd.' '.$langInsertVideo,
                    'url' => $base_url . 'video',
                    'icon' => 'fa fa-film',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_VIDEO)),
                array('title' => $langAdd.' '.$langInsertForum,
                    'url' => $base_url . 'forum',
                    'icon' => 'fa fa-comments',
                    'level' => 'secondary'),
                array('title' => $langAdd.' '.$langInsertEBook,
                    'url' => $base_url . 'ebook',
                    'icon' => 'fa fa-book',
                    'level' => 'secondary',
                    'show' =>  !is_module_disable(MODULE_ID_EBOOK)),
                array('title' => $langAdd.' '.$langInsertWork,
                    'url' => $base_url . 'work',
                    'icon' => 'fa fa-flask',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_ASSIGN)),
                array('title' => $langAdd.' '.$langInsertPoll,
                    'url' => $base_url . 'poll',
                    'icon' => 'fa fa-question-circle',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_QUESTIONNAIRE)),
                array('title' => $langAdd.' '.$langInsertWiki,
                    'url' => $base_url . 'wiki',
                    'icon' => 'fa fa-wikipedia-w',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_WIKI)),
                array('title' => $langAdd.' '.$langInsertChat,
                    'url' => $base_url . 'chat',
                    'icon' => 'fa fa-exchange',
                    'level' => 'secondary',
                    'show' => !is_module_disable(MODULE_ID_CHAT)),
                array('title' => $langAdd.' '.$langInsertTcMeeting,
                    'url' => $base_url . 'tc',
                    'icon' => 'fa fa-exchange',
                    'level' => 'secondary',
                    'show' => (!is_module_disable(MODULE_ID_TC) && is_configured_tc_server()))
                )) .
        "
        </div>
    </div>";
    }

}


if ($is_editor) {
    $visibility_check = $check_start_week = '';
    $query = "SELECT id, title, comments, start_week, finish_week, visible, public FROM course_units "
        . "WHERE course_id = ?d ";
} else {
    $visibility_check = "AND visible=1";
    $check_start_week = " AND (start_week <= CURRENT_DATE() OR start_week IS NULL)";
    $query = "SELECT id, title, comments, start_week, finish_week, visible, public FROM course_units "
        . "WHERE course_id = ?d "
        . "AND visible = 1 ";
}
if (isset($id) and $id !== false) {
    $info = Database::get()->querySingle("SELECT * FROM course_units WHERE id = ?d AND course_id = ?d $visibility_check $check_start_week", $id, $course_id);
    if ($info) {
        $pageName = $info->title;
        $comments = standard_text_escape(trim($info->comments));
        $course_start_week = $course_finish_week = '';
        if (!(is_null($info->start_week))) {
            $course_start_week = " $langFrom2 " . nice_format($info->start_week);
        }
        if (!(is_null($info->finish_week))) {
            $course_finish_week = " $langTill " . nice_format($info->finish_week);
        }
    }
}

if (!isset($info) or !$info) {
    Session::Messages($langUnknownResType);
    redirect_to_home_page("courses/$course_code/");
}

$all_units = Database::get()->queryArray($query, $course_id);

if (!$is_editor) {
    $user_units = findUserVisibleUnits($uid, $all_units);
} else {
    $user_units = $all_units;
}

foreach ($user_units as $user_unit) {
    $userUnitsIds[] = $user_unit->id;
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
    $q = Database::get()->querySingle("SELECT id, title, start_week, finish_week, public FROM course_units
                       WHERE course_id = ?d
                             AND id <> ?d
                             AND `order` $op $info->order
                             AND `order` >= 0
                             $visibility_check
                             $access_check
                             $check_start_week
                       ORDER BY `order` $dir
                       LIMIT 1", $course_id, $id);

    // security check
    if (!in_array($id, $userUnitsIds)) {
        redirect_to_home_page("courses/$course_code/");
    }

    if ($q and in_array($q->id, $userUnitsIds)) {
        $q_id = $q->id;
        $q_title = htmlspecialchars($q->title);
        $link[$i] = "<a class='$page_btn' title='$q_title'  href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$q_id'>$arrow1". ellipsize($q_title, 30) ."$arrow2</a>";
    } else {
        $link[$i] = '&nbsp;';
    }
}

if ($link['previous'] != '&nbsp;' or $link['next'] != '&nbsp;') {
    $tool_content .= "
        <div class='row'>
            <div class='col-md-12'>
              <div class='form-wrapper course_units_pager clearfix'>
                $link[previous]
                $link[next]
              </div>
            </div>
        </div>";
}

$tool_content .= "
  <div class='row'>
    <div class='col-md-12'>
      <div class='panel panel-default'>
          <div class='panel-heading'>
                <div class='panel-title h3'>
                    " . q($pageName) . "
                    <h6 class='text-muted'>
                        $course_start_week
                        $course_finish_week
                    </h6>
                </div>
          </div>
        <div class='panel-body'>
         <div>
            $comments
         </div>";

    $moduleTag = new ModuleElement($id);
    $tags_list = $moduleTag->showTags();

    if (!empty($tags_list)) {
        $tool_content .= "
          <div class='unit-tags'>
              <small><span class='text-muted'>$langTags:</span> $tags_list</small>
          </div>";
      }

      $tool_content .= "<div class='unit-resources'>";
      show_resources($id);
      $tool_content .= "</div>";

    $tool_content .= "
        </div>
      </div>
    </div>
  </div>";
$q = Database::get()->queryArray("SELECT id, title, start_week FROM course_units
             WHERE course_id = ?d AND `order` > 0
                   $visibility_check $check_start_week
             ORDER BY `order`", $course_id);
$course_units_options ='';
foreach ($q as $info) {
    $selected = ($info->id == $id) ? ' selected ' : '';
    $course_units_options .= "<option value='$info->id'$selected>" .
            htmlspecialchars(ellipsize($info->title, 50)) .
                '</option>';
}

if($is_editor){
    $q_in_class = Database::get()->queryArray("SELECT ID, activity_id, visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d",
                   $course_code, $id, 0);

    $q_in_home = Database::get()->queryArray("SELECT ID,activity_id,visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d",
                    $course_code, $id, 1);

    $q_after_class = Database::get()->queryArray("SELECT ID,activity_id,visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d",
                    $course_code, $id, 2);
}else{
    $q_in_class = Database::get()->queryArray("SELECT ID, activity_id, visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d AND visible=?d",
                   $course_code, $id, 0,1);

    $q_in_home = Database::get()->queryArray("SELECT ID,activity_id,visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d AND visible=?d",
                    $course_code, $id, 1,1);

    $q_after_class = Database::get()->queryArray("SELECT ID,activity_id,visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d AND visible=?d",
                    $course_code, $id, 2,1);
}
$cu_indirect = getIndirectReference($id);
$q = Database::get()->querySingle("SELECT flipped_flag FROM course WHERE code = ?s", $course_code);
if($q->flipped_flag == 2) {
    $tool_content .= "

        <div class='row'>
            <div class='col-md-12'>
            <div class='panel panel-default'>
            <div class='panel-heading'>
                <div class='paenel-title h4'>
                    $langActivities";
                    if ($is_editor) {
                        $tool_content .= "&nbsp;&nbsp;" . icon('fa-pencil', $langEdit, $urlAppend."modules/create_course/course_units_activities.php?course=$course_code&amp;edit_act=$id");
                    }
                $tool_content .= "
                </div>
            </div>
            

            <div class='panel-body'>
                <table class='table table-bordered'>
                    <tbody>";

        if($q_in_home)
            $tool_content .="</tr><tr><th scope='row'><label class='col-2 control-label'>$langActInHome</label></th>";
            $base_url = $urlAppend . "modules/units/insert.php?course=$course_code&amp;id=$id&amp;type=";

            foreach($q_in_home as $in_home){
                $act_title = q($activities[$in_home->activity_id]['title']);


                $vis = $in_home->visible;
                $class_vis = $vis == 0  ? 'not_visible' : '';
                $act_indirect = getIndirectReference($in_home->ID);

                $tool_content .= "<td><span class='col-sm-20 $class_vis control-label'>$act_title</span></td>";
                if($is_editor){
                    $tool_content .= "<td>". action_button(array(
                        array('title' => $langAdd.' '.$langInsertExercise,
                            'url' => $base_url . 'exercise&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-pencil-square-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_EXERCISE,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertDoc,
                            'url' => $base_url . 'doc&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-folder-open-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_DOCS,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertLink,
                            'url' => $base_url . 'link&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-link',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LINKS,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langLearningPath1,
                            'url' => $base_url . 'lp&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-ellipsis-h',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertVideo,
                            'url' => $base_url . 'video&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-film',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertForum,
                            'url' => $base_url . 'forum&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertEBook,
                            'url' => $base_url . 'ebook&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-book',
                            'level' => 'secondary',
                            'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertWork,
                            'url' => $base_url . 'work&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-flask',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertPoll,
                            'url' => $base_url . 'poll&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-question-circle',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertWiki,
                            'url' => $base_url . 'wiki&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-wikipedia-w',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WIKI,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertChat,
                            'url' => $base_url . 'chat&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_CHAT,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langInsertTcMeeting,
                            'url' => $base_url . 'tc&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_TC,$course_code,$id,$in_home->activity_id) && is_configured_tc_server()),
                        array('title' => $langAdd.' '.$langGlossary,
                            'url' => $base_url . 'glossary&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langWall,
                            'url' => $base_url . 'wall&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WALL,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langMindmap,
                            'url' => $base_url . 'mindmap&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-sitemap',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_MINDMAP,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langBlog,
                            'url' => $base_url . 'blog&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-columns',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langComments,
                            'url' => $base_url . 'comments&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langGradebook,
                            'url' => $base_url . 'gradebook&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-sort-numeric-desc',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$in_home->activity_id)),
                        array('title' => $langAdd.' '.$langProgress,
                            'url' => $base_url . 'progress&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-trophy',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$in_home->activity_id))

                    ),'',true) . "</td><td>";


                $tool_content .= action_button(array(
                        array('title' => $vis == 1? $langViewHide : $langViewShow,
                        'url' => "$_SERVER[REQUEST_URI]&vis_act=$in_home->ID",
                        'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $langDelete,
                            'url' => "$_SERVER[REQUEST_URI]&del_act=$in_home->ID&actid=".$in_home->activity_id,
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langUnitActivityDeleteConfirm))) ;




                }
                $tool_content .= "</td></tr><tr><td></td>";
            }
            if($q_in_class){
                $tool_content .= "<tr><th scope='row'><label class='col-2 control-label'>$langActInClass</label></th>";


            foreach($q_in_class as $in_class){
                $act_title = q($activities[$in_class->activity_id]['title']);

                $vis = $in_class->visible;
                $class_vis = $vis == 0  ? 'not_visible' : '';
                $act_indirect = getIndirectReference($in_class->activity_id);

                $tool_content .= "<td><span class='col-sm-20  $class_vis control-label'>$act_title</span></td>";
                if($is_editor){
                    $tool_content .= "<td>". action_button(array(
                        array('title' => $langAdd.' '.$langInsertExercise,
                            'url' => $base_url . 'exercise&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-pencil-square-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_EXERCISE,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertDoc,
                            'url' => $base_url . 'doc&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-folder-open-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_DOCS,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertLink,
                            'url' => $base_url . 'link&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-link',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LINKS,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langLearningPath1,
                            'url' => $base_url . 'lp&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-ellipsis-h',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertVideo,
                            'url' => $base_url . 'video&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-film',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertForum,
                            'url' => $base_url . 'forum&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertEBook,
                            'url' => $base_url . 'ebook&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-book',
                            'level' => 'secondary',
                            'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertWork,
                            'url' => $base_url . 'work&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-flask',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertPoll,
                            'url' => $base_url . 'poll&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-question-circle',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertWiki,
                            'url' => $base_url . 'wiki&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-wikipedia-w',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WIKI,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertChat,
                            'url' => $base_url . 'chat&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_CHAT,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertTcMeeting,
                            'url' => $base_url . 'tc&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_TC,$course_code,$id,$in_class->activity_id) && is_configured_tc_server()),
                        array('title' => $langAdd.' '.$langGlossary,
                            'url' => $base_url . 'glossary&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langWall,
                            'url' => $base_url . 'wall&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WALL,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langMindmap,
                            'url' => $base_url . 'mindmap&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-sitemap',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_MINDMAP,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langGroups,
                            'url' => $base_url . 'group&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-users',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GROUPS,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langBlog,
                            'url' => $base_url . 'blog&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-columns',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langComments,
                            'url' => $base_url . 'comments&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langGradebook,
                            'url' => $base_url . 'gradebook&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-sort-numeric-desc',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$in_class->activity_id)),
                        array('title' => $langAdd.' '.$langProgress,
                            'url' => $base_url . 'progress&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-trophy',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$in_class->activity_id))

                    ),'',true). "</td><td>";

                    $tool_content .=
                    action_button(array(
                        array('title' => $vis == 1? $langViewHide : $langViewShow,
                        'url' => "$_SERVER[REQUEST_URI]&vis_act=$in_class->ID",
                        'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $langDelete,
                            'url' => "$_SERVER[REQUEST_URI]&del_act=$in_class->ID&actid=".$in_class->activity_id,
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langUnitActivityDeleteConfirm))) ;


                }
                $tool_content .= "</td></tr><tr><td></td>";

            }
        }

        if($q_after_class){
            $tool_content .= "<tr><th scope='row'><label class='col-md-auto control-label'>$langActAfterClass</label></th>";

            foreach($q_after_class as $after_class){
                $act_title = q($activities[$after_class->activity_id]['title']);


                $vis = $after_class->visible;
                $class_vis = $vis == 0  ? 'not_visible' : '';
                $act_indirect = getIndirectReference($after_class->ID);


                $tool_content .= "<td><span class='col-sm-20 $class_vis control-label'>$act_title</span></td>";
                if($is_editor){
                    $tool_content .= "<td>".action_button(array(
                        array('title' => $langAdd.' '.$langInsertExercise,
                            'url' => $base_url . 'exercise&fc_type=2&act_name='. $act_title,
                            'icon' => 'fa fa-pencil-square-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_EXERCISE,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertDoc,
                            'url' => $base_url . 'doc&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-folder-open-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_DOCS,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertLink,
                            'url' => $base_url . 'link&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-link',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LINKS,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langLearningPath1,
                            'url' => $base_url . 'lp&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-ellipsis-h',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertVideo,
                            'url' => $base_url . 'video&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-film',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertForum,
                            'url' => $base_url . 'forum&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertEBook,
                            'url' => $base_url . 'ebook&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-book',
                            'level' => 'secondary',
                            'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertWork,
                            'url' => $base_url . 'work&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-flask',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertPoll,
                            'url' => $base_url . 'poll&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-question-circle',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertWiki,
                            'url' => $base_url . 'wiki&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-wikipedia-w',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WIKI,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertChat,
                            'url' => $base_url . 'chat&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_CHAT,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langInsertTcMeeting,
                            'url' => $base_url . 'tc&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_TC,$course_code,$id,$after_class->activity_id) && is_configured_tc_server()),
                        array('title' => $langAdd.' '.$langGlossary,
                            'url' => $base_url . 'glossary&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langWall,
                            'url' => $base_url . 'wall&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WALL,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langMindmap,
                            'url' => $base_url . 'mindmap&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-sitemap',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_MINDMAP,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langGroups,
                            'url' => $base_url . 'group&fc_type=2&act_name=='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-users',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GROUPS,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langBlog,
                            'url' => $base_url . 'blog&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-columns',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langComments,
                            'url' => $base_url . 'comments&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langGradebook,
                            'url' => $base_url . 'gradebook&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-sort-numeric-desc',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$after_class->activity_id)),
                        array('title' => $langAdd.' '.$langProgress,
                            'url' => $base_url . 'progress&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                            'icon' => 'fa fa-trophy',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$after_class->activity_id))

                    ),'',true) . "</td><td>";


                    $tool_content .= action_button(array(
                        array('title' => $vis == 1? $langViewHide : $langViewShow,
                        'url' => "$_SERVER[REQUEST_URI]&vis_act=$after_class->ID",
                        'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $langDelete,
                            'url' => "$_SERVER[REQUEST_URI]&del_act=$after_class->ID&actid=".$after_class->activity_id,
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langUnitActivityDeleteConfirm))) ;





                }
                $tool_content .= "</td></tr><tr><td></td>";
            }
            $tool_content .="
                </tr>";
        }

        $tool_content .="</tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>";
}
$tool_content .="

    <div class='row'>
        <div class='col-md-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' name='unitselect' action='" . $urlServer . "modules/units/' method='get'>
                    <input type='hidden' name='course' value='$course_code'>
                    <div class='form-group'>
                        <label class='col-sm-8 control-label'>$langCourseUnits</label>
                        <div class='col-sm-4'>
                            <label class='hidden' for='id'>$langCourseUnits</label>
                            <select name='id' id='id' class='form-control' onChange='document.unitselect.submit();'>
                                $course_units_options
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>";

draw($tool_content, 2, null, $head_content);

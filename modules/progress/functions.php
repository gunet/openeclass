<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

        
/**
 * @brief display all certificates -- initial screen
 * @global type $course_id
 * @global type $tool_content
 * @global type $course_code
 * @global type $is_editor
 * @global type $langDelete
 * @global type $langConfirmDelete
 * @global type $langCreateDuplicate
 * @global type $langNoCertificates
 * @global type $langEditChange
 * @global type $langAvailCert
 * @global type $langViewHide
 * @global type $langViewShow
 * @global type $langEditChange
 * @global type $langSee
 */
function display_certificates() {

    global $course_id, $tool_content, $course_code, $is_editor,
           $langDelete, $langConfirmDelete, $langCreateDuplicate,
           $langNoCertificates, $langEditChange, $langAvailCert,
           $langViewHide, $langViewShow, $langEditChange, $langSee;

    if ($is_editor) {
        $sql_cer = Database::get()->queryArray("SELECT id, title, description, active FROM certificate WHERE course_id = ?d", $course_id);
    } else {
        $sql_cer = Database::get()->queryArray("SELECT id, title, description, active FROM certificate WHERE course_id = ?d AND active = 1", $course_id);
    }
    
    if (count($sql_cer) == 0) { // no certificates
        $tool_content .= "<div class='alert alert-info'>$langNoCertificates</div>";
    } else {
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'>
                            <th>$langAvailCert</th>";
        if( $is_editor) {
            $tool_content .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
        }
        $tool_content .= "</tr>";
        foreach ($sql_cer as $data) {            
            $row_class = !$data->active ? "class='not_visible'" : "";
            $tool_content .= "
                    <tr $row_class>
                        <td>
                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id'>".q($data->title)."</a>
                        </td>";
            if($is_editor) {
                $tool_content .= "<td class='option-btn-cell'>";
                $tool_content .= action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;edit=1",
                                          'icon' => 'fa-cogs'),
                                    array('title' => $data->active ? $langViewHide : $langViewShow,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;vis=" .
                                                  ($data->active ? '0' : '1'),
                                          'icon' => $data->active ? 'fa-eye-slash' : 'fa-eye'),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del_cert=$data->id",
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => $langConfirmDelete),
                                    array('title' => $langSee,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;preview=1",
                                          'icon' => 'fa-search')
                                        ));
                $tool_content .= "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div></div></div>";
    }
}


/**
 * * @brief display all badges -- initial screen
 * @global type $course_id
 * @global type $tool_content
 * @global type $course_code
 * @global type $is_editor
 * @global type $langDelete
 * @global type $langConfirmDelete
 * @global type $langCreateDuplicate
 * @global type $langNoBadges
 * @global type $langEditChange
 * @global type $langAvailBadge
 * @global type $langViewHide
 * @global type $langViewShow
 * @global type $langEditChange
 * @global type $langSee
 */
function display_badges() {

    global $course_id, $tool_content, $course_code, $is_editor,
           $langDelete, $langConfirmDelete, $langCreateDuplicate,
           $langNoBadges, $langEditChange, $langAvailBadge,
           $langViewHide, $langViewShow, $langEditChange, $langSee;

    if ($is_editor) {
        $sql_cer = Database::get()->queryArray("SELECT id, title, description, active FROM badge WHERE course_id = ?d", $course_id);
    } else {
        $sql_cer = Database::get()->queryArray("SELECT id, title, description, active FROM badge WHERE course_id = ?d AND active = 1", $course_id);
    }
    
    if (count($sql_cer) == 0) { // no badges
        $tool_content .= "<div class='alert alert-info'>$langNoBadges</div>";
    } else {
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'>
                            <th>$langAvailBadge</th>";
        if( $is_editor) {
            $tool_content .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
        }
        $tool_content .= "</tr>";
        foreach ($sql_cer as $data) {            
            $row_class = !$data->active ? "class='not_visible'" : "";
            $tool_content .= "
                    <tr $row_class>
                        <td>
                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id'>".q($data->title)."</a>
                        </td>";
            if($is_editor) {
                $tool_content .= "<td class='option-btn-cell'>";
                $tool_content .= action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id&amp;edit=1",
                                          'icon' => 'fa-cogs'),
                                    array('title' => $data->active ? $langViewHide : $langViewShow,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id&amp;vis=" .
                                                  ($data->active ? '0' : '1'),
                                          'icon' => $data->active ? 'fa-eye-slash' : 'fa-eye'),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del_badge=$data->id",
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => $langConfirmDelete),
                                    array('title' => $langSee,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id&amp;preview=1",
                                          'icon' => 'fa-search')
                                        ));
                $tool_content .= "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div></div></div>";
    }
}

/**
 * @brief display all certificate activities
 * @global type $tool_content
 * @global type $course_code
 * @global type $langNoActivCert
 * @global type $langAttendanceActList
 * @global type $langTitle
 * @global type $langType
 * @global type $langOfAssignment
 * @global type $langExerciseAsModuleLabel
 * @global type $langOfBlog
 * @global type $langDocumentAsModuleLabel
 * @global type $langMediaAsModuleLabel
 * @global type $langOfEBook
 * @global type $langOfPoll
 * @global type $langWiki
 * @global type $langOfForums
 * @global type $langOfBlogComments
 * @global type $langOfCourseComments
 * @global type $langOfLikesForum
 * @global type $langOfLikesSocial
 * @global type $langOfLearningPath
 * @global type $langDelete
 * @global type $langEditChange
 * @global type $langConfirmDelete 
 * @global type $langInsertWorkCap
 * @global type $langAdd
 * @global type $langExport
 * @global type $langBack
 * @global type $langInsertWorkCap
 * @global type $langUsers
 * @global type $langValue
 * @global type $langPersoValue
 * @param type $element
 * @param type $certificate_id
 */
function display_activities($element, $id) {

    global $tool_content, $course_code,
           $langNoActivCert, $langAttendanceActList, $langTitle, $langType,
           $langOfAssignment, $langExerciseAsModuleLabel, $langOfBlog,
           $langMediaAsModuleLabel, $langOfEBook, $langOfPoll, $langWiki,
           $langOfForums, $langOfBlogComments, $langOfCourseComments, $langOfLikesForum,
           $langOfLearningPath, $langDelete, $langEditChange, $langConfirmDelete,           
           $langInsertWorkCap, $langDocumentAsModuleLabel,
           $langAdd, $langExport, $langBack, $langInsertWorkCap, $langUsers,
           $langValue, $langPersoValue, $langOfLikesSocial;

    if ($element == 'certificate') {
        $link_id = "course=$course_code&amp;certificate_id=$id";
    } else {
        $link_id = "course=$course_code&amp;badge_id=$id";
    }
    
    $tool_content .= action_bar(
            array(
                array('title' => $langAdd,
                      'level' => 'primary-label',
                      'options' => array(
                          array('title' => "$langOfAssignment",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=assignment",
                                'icon' => 'fa fa-flask space-after-icon',
                                'class' => ''),
                          array('title' => "$langExerciseAsModuleLabel",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=exercise",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfBlog",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=blog",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfBlogComments",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=blogcomments",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          /*array('title' => "$langOfCourseComments",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=coursecomments",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),*/
                          array('title' => "$langOfForums",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=forum",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfLearningPath",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=lp",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          /*array('title' => "$langOfLikesSocial",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=likesocial",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfLikesForum",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=likeforum",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),*/
                          array('title' => "$langDocumentAsModuleLabel",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=document",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langMediaAsModuleLabel",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=multimedia",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),                          
                          array('title' => "$langOfEBook",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=ebook",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langOfPoll",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=poll",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => ''),
                          array('title' => "$langWiki",
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=wiki",
                                'icon' => 'fa fa-edit space-after-icon',
                                'class' => '')),
                     'icon' => 'fa-plus'),
                array('title' => $langUsers,
                      'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;progressall=true",
                      'icon' => 'fa-users',
                      'level' => 'primary-label'),
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label'),                
                array('title' => "$langExport",
                      'url' => "dumpcertificatebook.php?$link_id&amp;enc=1253",
                      'icon' => 'fa-file-excel-o'),                
            ),
            true
        );
     
    // certificate details
    $tool_content .= display_certificate_settings($element, $id);
        
    //get available activities
    $result = Database::get()->queryArray("SELECT * FROM ${element}_criterion WHERE $element = ?d ORDER BY `id` DESC", $id);    

    if (count($result) > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>
                        <table class='table-default'>
                        <tr class='list-header'><th class='text-center' colspan='5'>$langAttendanceActList</th></tr>
                        <tr class='list-header'>
                            <th>$langTitle</th>
                            <th>$langType</th>
                            <th class='text-center'>$langValue</th>
                            <th class='text-center'><i class='fa fa-cogs'></i></th>
                        </tr>";
        foreach ($result as $details) {
            $resource_data = get_resource_details($element, $details->id);            
            $tool_content .= "<tr><td>";            
            $tool_content .= $resource_data['title'];
            $tool_content .= "</td><td>" . $resource_data['type'] ."</td>";
            
            // display operators and thresholds            
            $tool_content .= "<td class='text-center'>";
            if (!empty($details->operator)) {
                $op = get_operators();
                $tool_content .= $op[$details->operator];
            } else {
                $tool_content .= "&mdash;";
            }
            $tool_content .= "&nbsp;$details->threshold</td>";
            
            $tool_content .= "<td class='text-center option-btn-cell'>".
                    action_button(array(
                        array('title' => $langEditChange,
                            'icon' => 'fa-edit',
                            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;act_mod=$details->id",
                            'show' => in_array($details->activity_type, criteria_with_operators())
                            ),
                        array('title' => $langDelete,
                            'icon' => 'fa-times',
                            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;del_cert_res=$details->id",
                            'confirm' => $langConfirmDelete,
                            'class' => 'delete'))).
                    "</td></tr>";
        } // end of while
        $tool_content .= "</table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoActivCert</div>";
    }
}


/**
 * @brief choose activity for inserting in certificate / badge
 * @param type $element_id
 * @param type $element
 * @param type $activity
 */
function insert_activity($element, $element_id, $activity) {
                                
    switch ($activity) {
        case 'assignment':            
            display_available_assignments($element, $element_id);
            break;
        case 'exercise':
            display_available_exercises($element, $element_id);
            break;
        case 'blog';
            display_available_blogs($element, $element_id);
            break;
        case 'blogcomments':
            display_available_blogcomments($element, $element_id);
            break;
        case 'coursecomments':
            display_available_coursecomments($element, $element_id);
            break;
        case 'forum':
            display_available_forums($element, $element_id);
            break;
        case 'lp':
            display_available_lps($element, $element_id);
            break;
        case 'likesocial';
            break;
        case 'likeforum';
            break;
        case 'document':
            display_available_documents($element, $element_id);
            break;
        case 'multimedia':
            display_available_multimedia($element, $element_id);
            break;
        case 'ebook':
            display_available_ebooks($element, $element_id);
            break;
        case 'poll':
            display_available_polls($element, $element_id);
            break;
        case 'wiki':
            display_available_wiki($element, $element_id);
            break;
        default: break;
        }        
}


/**
 * @brief display editing form about resource
 * @global type $tool_content
 * @global type $course_code
 * @global type $langModify
 * @global type $langOperator
 * @global type $langUsedCertRes
 * @param type $element_id
 * @param type $element
 * @param type $activity_id
 */
function display_modification_activity($element, $element_id, $activity_id) {
    
    global $tool_content, $course_code, $langModify, $langOperator, $langUsedCertRes;
    
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    if (resource_usage($activity_id)) { // check if resource has been used by user
        Session::Messages("$langUsedCertRes", "alert-warning");
        redirect_to_home_page("modules/progress/index.php?course=$course_code&amp;${element}_id=$element_id");
    } else { // otherwise editing is not allowed
        $data = Database::get()->querySingle("SELECT threshold, operator FROM ${element}_criterion 
                                            WHERE id = ?d AND $element = ?d", $activity_id, $element_id);
        $operators = get_operators();

        $tool_content .= "<form action='index.php?course=$course_code' method='post'>";
        $tool_content .= "<input type='hidden' name='$element_name' value='$element_id'>";
        $tool_content .= "<input type='hidden' name='activity_id' value='$activity_id'>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='name' class='col-sm-1 control-label'>$langOperator:</label>";    
        $tool_content .= "<span class='col-sm-1'>" . selection($operators, 'cert_operator', $operators[$data->operator]) . "</span>";
        $tool_content .= "<span class='col-sm-2'><input class='form-control' type='text' name='cert_threshold' value='$data->threshold'></span>";    
        $tool_content .= "</div>";
        $tool_content .= "<div class='col-sm-5 col-sm-offset-5'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='mod_cert_activity' value='$langModify'>";
        $tool_content .= "</div>";
        $tool_content .= "</form>";
    }
}

/**
 * @brief assignments display form
 * @global type $course_id
 * @global type $tool_content 
 * @global type $langNoAssign
 * @global type $course_code
 * @global type $langTitle
 * @global type $langGroupWorkDeadline_of_Submission
 * @global type $langChoice
 * @global type $langActive
 * @global type $langInactive
 * @global type $langAddModulesButton
 * @global type $langOperator
 * @global type $langValue
 * @param type $element
 * @param type $element_id
 */
function display_available_assignments($element, $element_id) {

    global $course_id, $tool_content, $langNoAssign, $course_code,
           $langTitle, $langGroupWorkDeadline_of_Submission, $langChoice, 
           $langActive, $langInactive, $langAddModulesButton,
           $langOperator, $langValue;
     
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT * FROM assignment WHERE course_id = ?d AND active = 1 AND id NOT IN 
                                (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                    AND resource != ''
                                    AND activity_type = 'assignment' 
                                    AND module = " . MODULE_ID_ASSIGN . ") 
                                ORDER BY title", $course_id, $element_id);    
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoAssign</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name = '$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langTitle</th>" .
                "<th style='width:160px;'>$langGroupWorkDeadline_of_Submission</th>" .
                "<th style='width:5px;'>$langOperator</th>" .
                "<th style='width:50px;'>$langValue</th>" . 
                "<th style='width:10px;' class='text-center'>$langChoice</th>" .
                "</tr>";        
        foreach ($result as $row) {            
            if ($row->active) {
                $visible = icon('fa-eye', $langActive);
            } else {
                $visible = icon('fa-eye-slash', $langInactive);
            }
            $assignment_id = $row->id;
            $description = empty($row->description) ? '' :
                    "<div>$row->description</div>";            
            $tool_content .= "<tr>" .
                    "<td> " . q($row->title) . "<br><br><div class='text-muted'>$description</div></td>" .
                    "<td class='text-center'>".nice_format($row->submission_date, true)."</td>
                    <td>". selection(get_operators(), "operator[$assignment_id]") . "</td>".
                    "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$assignment_id]' value=''></td>" .
                    "<td class='text-center'><input name='assignment[]' value='$assignment_id' type='checkbox'></td>" .
                    "</tr>";            
        }
        $tool_content .= "</table>" .
                "<div align='right'><input class='btn btn-primary' type='submit' name='add_assignment' value='$langAddModulesButton'></div></th></form>";
    }        
}


/**
 * @brief exercises display form
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $urlServer
 * @global type $langExercices
 * @global type $langNoExercises
 * @global type $langDescription
 * @global type $langChoice
 * @global type $langOperator
 * @global type $langValue
 * @global type $langAddModulesButton
 * @param type $element
 * @param type $element_id
 */
function display_available_exercises($element, $element_id) {

    global $course_id, $course_code, $tool_content, $urlServer, $langExercices,
            $langNoExercises, $langDescription, $langChoice, $langAddModulesButton,
            $langOperator, $langValue;
    
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                    AND exercise.active = 1 AND exercise.id NOT IN 
                                    (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                            AND resource != '' 
                                            AND activity_type = 'exercise' 
                                            AND module = " . MODULE_ID_EXERCISE . ") ORDER BY title", $course_id, $element_id);
    $quizinfo = array();
    foreach ($result as $row) {
        $quizinfo[] = array(
            'id' => $row->id,
            'name' => $row->title,
            'comment' => $row->description,
            'visibility' => $row->active);
    }
    if (count($quizinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoExercises</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" . 
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th width='50%' class='text-left'>$langExercices</th>" .
                "<th class='text-left'>$langDescription</th>" .
                "<th style='width:5px;'>$langOperator</th>" .
                "<th style='width:50px;'>$langValue</th>" . 
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";        
        foreach ($quizinfo as $entry) {
            if ($entry['visibility'] == '0') {
                $vis = 'not_visible';
            } else {
                $vis = '';
            }
            $exercise_id = $entry['id'];
            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td class='text-left'><a href='${urlServer}modules/exercise/exercise_submit.php?course=$course_code&amp;exerciseId=$exercise_id'>" . q($entry['name']) . "</a></td>";
            $tool_content .= "<td class='text-left'>" . $entry['comment'] . "</td>";            
            $tool_content .= "<td>". selection(get_operators(), "operator[$exercise_id]") . "</td>";
            $tool_content .= "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$exercise_id]' value=''></td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='exercise[]' value='$exercise_id'></td>";
            $tool_content .= "</tr>";            
        }
        $tool_content .= "</table><div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_exercise' value='$langAddModulesButton'></div>
        </form>";
    }        
}

/**
 * @brief document display form 
 * @global type $webDir
 * @global type $course_code
 * @global type $tool_content
 * @global type $langDirectory
 * @global type $langUp
 * @global type $langName
 * @global type $langSize
 * @global type $langDate
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoDocuments
 * @global type $course_code
 * @global type $group_sql
 * @param type $element
 * @param type $element_id
 */
function display_available_documents($element, $element_id) {
    
    global $webDir, $course_code, $tool_content, 
            $langDirectory, $langUp, $langName, $langSize,
            $langDate, $langAddModulesButton, $langChoice,
            $langNoDocuments, $course_code, $group_sql;
    
    require_once 'modules/document/doc_init.php';
    require_once 'include/lib/mediaresource.factory.php';
    require_once 'include/lib/fileManageLib.inc.php';
    require_once 'include/lib/fileDisplayLib.inc.php';    
    require_once 'include/lib/multimediahelper.class.php';

    doc_init();
    
    $common_docs = false;
    $basedir = $webDir . '/courses/' . $course_code . '/document';
    $path = get_dir_path('path');
    $dir_param = get_dir_path('dir');
    $dir_setter = $dir_param ? ('&amp;dir=' . $dir_param) : '';
    $dir_html = $dir_param ? "<input type='hidden' name='dir' value='$dir_param'>" : '';
    
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT id, course_id, path, filename, format, title, extra_path, date_modified, visible, copyrighted, comment, IF(title = '', filename, title) AS sort_key FROM document
                                     WHERE $group_sql AND visible = 1 AND
                                          path LIKE ?s AND
                                          path NOT LIKE ?s AND id NOT IN 
                                        (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                            AND resource!='' AND activity_type = 'document' AND module = " . MODULE_ID_DOCS . ")
                                ORDER BY sort_key COLLATE utf8_unicode_ci",
                                "$path/%", "$path/%/%", $element_id);

    $fileinfo = array();
    $urlbase = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;$element_name=$element_id&amp;add=true&amp;act=document$dir_setter&amp;type=doc&amp;path=";

    foreach ($result as $row) {
        $fullpath = $basedir . $row->path;
        if ($row->extra_path) {
            $size = 0;
        } else {
            $size = file_exists($fullpath)? filesize($fullpath): 0;
        }
        $fileinfo[] = array(
            'id' => $row->id,
            'is_dir' => is_dir($fullpath),
            'size' => $size,
            'title' => $row->title,
            'name' => htmlspecialchars($row->filename),
            'format' => $row->format,
            'path' => $row->path,
            'visible' => $row->visible,
            'comment' => $row->comment,
            'copyrighted' => $row->copyrighted,
            'date' => $row->date_modified,
            'object' => MediaResourceFactory::initFromDocument($row));
    }
    if (count($fileinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoDocuments</div>";
    } else {
        if (!empty($path)) {
            $dirname = Database::get()->querySingle("SELECT filename FROM document WHERE $group_sql AND path = ?s", $path);
            $parentpath = dirname($path);
            $dirname =  htmlspecialchars($dirname->filename);
            $parentlink = $urlbase . $parentpath;
            $parenthtml = "<span class='pull-right'><a href='$parentlink'>$langUp " .
                    icon('fa-level-up') . "</a></span>";
            $colspan = 4;
        }
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .                
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>";
        if( !empty($path)) {
        $tool_content .=
                "<tr>" .
                "<th colspan='$colspan'><div class='text-left'>$langDirectory: $dirname$parenthtml</div></th>" .
                "</tr>" ;
        }
        $tool_content .=
                "<tr class='list-header'>" .
                "<th class='text-left'>$langName</th>" .
                "<th class='text-center'>$langSize</th>" .
                "<th class='text-center'>$langDate</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        $counter = 0;
        foreach (array(true, false) as $is_dir) {
            foreach ($fileinfo as $entry) {
                if ($entry['is_dir'] != $is_dir) {
                    continue;
                }
                $dir = $entry['path'];
                if ($is_dir) {
                    $image = 'fa-folder-o';
                    $file_url = $urlbase . $dir;
                    $link_text = $entry['name'];

                    $link_href = "<a href='$file_url'>$link_text</a>";
                } else {
                    $image = choose_image('.' . $entry['format']);
                    $file_url = file_url($entry['path'], $entry['name'], $common_docs ? 'common' : $course_code);

                    $dObj = $entry['object'];
                    $dObj->setAccessURL($file_url);
                    $dObj->setPlayURL(file_playurl($entry['path'], $entry['name'], $common_docs ? 'common' : $course_code));

                    $link_href = MultimediaHelper::chooseMediaAhref($dObj);
                }
                if ($entry['visible'] == 'i') {
                    $vis = 'invisible';
                } else {
                    $vis = '';                    
                }
                $tool_content .= "<tr class='$vis'>";
                $tool_content .= "<td>" . icon($image, '')."&nbsp;&nbsp;&nbsp;$link_href";

                /* * * comments ** */
                if (!empty($entry['comment'])) {
                    $tool_content .= "<br /><div class='comment'>" .
                            standard_text_escape($entry['comment']) .
                            "</div>";
                }
                $tool_content .= "</td>";
                if ($is_dir) {
                    // skip display of date and time for directories
                    $tool_content .= "<td>&nbsp;</td><td>&nbsp;</td>";
                } else {
                    $size = format_file_size($entry['size']);
                    $date = nice_format($entry['date'], true, true);
                    $tool_content .= "<td class='text-right'>$size</td><td class='text-center'>$date</td>";
                }
                $tool_content .= "<td class='text-center'><input type='checkbox' name='document[]' value='$entry[id]' /></td>";
                $tool_content .= "</tr>";
                $counter++;
            }
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_document' value='$langAddModulesButton' /></div>$dir_html</form>";        
    }
}


/**
 * @brief blog display form
 * @global type $tool_content
 * @global type $langAddModulesButton 
 * @global type $course_code
 * @global type $langTitle
 * @global type $langValue
 * @global type $langChoice
 * @global type $langOperator
 * @global type $langNumOfBlogs
 * @global type $langResourceAlreadyAdded
 * @param type $element
 * @param type $element_id
 */
function display_available_blogs($element, $element_id) {
    
    global $tool_content, $langAddModulesButton, $langNumOfBlogs,
           $course_code, $langTitle, $langValue, $langResourceAlreadyAdded,
           $langChoice, $langOperator;
            
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $res = Database::get()->queryArray("SELECT resource FROM ${element}_criterion WHERE $element = ?d                                     
                                            AND resource IS NULL 
                                            AND activity_type = 'blog' 
                                            AND module = " . MODULE_ID_BLOG . "", $element_id);
    if (count($res) > 0) {
        $tool_content .= "<div class='alert alert-warning'>$langResourceAlreadyAdded</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:70%;'>&nbsp;$langTitle</th>" .
                "<th style='width:5px;'>&nbsp;$langOperator</th>" .
                "<th style='width:30px;'>$langValue</th>" . 
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
                
            $tool_content .= "<tr>" .
                    "<td>$langNumOfBlogs</td>" .
                    "<td>". selection(get_operators(), "operator") . "</td>".
                    "<td class='text-center'><input style='width:30px;' type='text' name='threshold' value=''></td>" .
                    "<td class='text-center'><input name='blog' value='1' type='checkbox'></td>" .
                    "</tr>";            
        
        $tool_content .= "</table>" .
                "<div align='right'><input class='btn btn-primary' type='submit' name='add_blog' value='$langAddModulesButton'></div></th></form>";
    }
}

/**
 * @brief blog comment display form
 * @global type $tool_content
 * @global type $langAddModulesButton
 * @global type $langBlogEmpty
 * @global type $urlServer
 * @global type $course_code
 * @global type $langTitle
 * @global type $langValue
 * @global type $langChoice
 * @global type $langDate
 * @global type $course_id
 * @global type $langOperator
 * @param type $element
 * @param type $element_id
 */
function display_available_blogcomments($element, $element_id) {
    
    global $tool_content, $langAddModulesButton, $langBlogEmpty, 
           $urlServer, $course_code, $langTitle, $langValue,
           $langChoice, $langDate, $course_id, $langOperator;
    
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT * FROM blog_post WHERE course_id = ?d AND id NOT IN 
                                (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                    AND resource != ''
                                    AND activity_type = 'blogcomment' 
                                    AND module = " . MODULE_ID_BLOG . ") 
                                ORDER BY title", $course_id, $element_id);    
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langBlogEmpty</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:50%;'>&nbsp;$langTitle</th>" .
                "<th class='text-left'>&nbsp;$langDate</th>" .
                "<th style='width:5px;'>&nbsp;$langOperator</th>" .
                "<th style='width:50px;'>$langValue</th>" . 
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";        
        foreach ($result as $row) {                        
            $blog_id = $row->id;            
            $tool_content .= "<tr>" .
                    "<td><a href='${urlServer}modules/blog/index.php?course=$course_code&amp;action=showPost&amp;pId=$blog_id#comments-title'>" . q($row->title) . "</a></td>" .
                    "<td class='text-center'>" . nice_format($row->time, true) . "</td>
                    <td>". selection(get_operators(), "operator[$blog_id]") . "</td>".
                    "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$blog_id]' value=''></td>" .
                    "<td class='text-center'><input name='blogcomment[]' value='$blog_id' type='checkbox'></td>" .
                    "</tr>";            
        }
        $tool_content .= "</table>" .
                "<div align='right'><input class='btn btn-primary' type='submit' name='add_blogcomment' value='$langAddModulesButton'></div></th></form>";
    }
}


function display_available_coursecomments($element, $element_id) {
    
    global $tool_content;
    
    $tool_content .= "<div class='alert alert-warning'>....Προς υλοποίηση....</div>";
    
    return $tool_content;    
}

/**
 * @brief forum display form
 * @global type $tool_content
 * @global type $urlServer
 * @global type $course_id
 * @global type $langComments
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoForums
 * @global type $langForums
 * @global type $course_code
 * @global type $langOperator
 * @global type $langValue
 * @param type $element
 * @param type $element_id
 */
function display_available_forums($element, $element_id) {
      
    global $tool_content, $urlServer, $course_id,
           $langComments, $langAddModulesButton, $langChoice, $langNoForums, 
           $langForums, $course_code, $langOperator, $langValue;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT * FROM forum WHERE course_id = ?d 
                                        AND forum.id NOT IN 
                                        (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                            AND resource != '' 
                                            AND activity_type = 'forum' 
                                            AND module = " . MODULE_ID_FORUM . ")", $course_id, $element_id);
    $foruminfo = array();
    foreach ($result as $row) {
        $foruminfo[] = array(
            'id' => $row->id,
            'name' => $row->name,
            'comment' => $row->desc,
            'topics' => $row->num_topics);
    }
    if (count($foruminfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoForums</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th>$langForums</th>" .
                "<th>$langComments</th>" .
                "<th style='width:5px;'>$langOperator</th>" .
                "<th style='width:50px;'>$langValue</th>" . 
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";

        foreach ($foruminfo as $entry) {
            $forum_id = $entry['id'];
            $tool_content .= "<tr>";
            $tool_content .= "<td><a href='${urlServer}modules/forum/viewforum.php?course=$course_code&amp;forum=$forum_id'>" . q($entry['name']). "</a></td>";
            $tool_content .= "<td>" . q($entry['comment']) . "</td>";            
            $tool_content .= "<td>". selection(get_operators(), "operator[$forum_id]") . "</td>";
            $tool_content .= "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$forum_id]' value=''></td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='forum[]' value='$forum_id'></td>";
            $tool_content .= "</tr>";
            $r = Database::get()->queryArray("SELECT * FROM forum_topic WHERE forum_id = ?d 
                                                AND forum_topic.id NOT IN 
                                            (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                                AND resource != '' 
                                                AND activity_type = 'forumtopic' 
                                                AND module = " . MODULE_ID_FORUM . ")", $forum_id, $element_id);
            if (count($r) > 0) { // if forum topics found
                $topicinfo = array();
                foreach ($r as $topicrow) {
                    $topicinfo[] = array(
                        'topic_id' => $topicrow->id,
                        'topic_title' => $topicrow->title,
                        'topic_time' => $topicrow->topic_time);
                }
                foreach ($topicinfo as $topicentry) {
                    $topic_id = $topicentry['topic_id'];
                    $tool_content .= "<tr>";
                    $tool_content .= "<td>&nbsp;".icon('fa-comments')."&nbsp;&nbsp;<a href='${urlServer}/modules/forum/viewtopic.php?course=$course_code&amp;topic=$topic_id&amp;forum=$entry[id]'>" . q($topicentry['topic_title']) . "</a></td>";
                    $tool_content .= "<td>&nbsp;</td>";
                    $tool_content .= "<td>". selection(get_operators(), "operator[$topic_id]") . "</td>";
                    $tool_content .= "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$topic_id]' value=''></td>";
                    $tool_content .= "<td class='text-center'><input type='checkbox' name='forumtopic[]' value='$topic_id'></td>";
                    $tool_content .= "</tr>";
                }
            }
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>
                            <input class='btn btn-primary' type='submit' name='add_forum' value='$langAddModulesButton'>
                        </div></form>";        
    }
}

/**
 * @brief learning paths display form
 * @global type $course_id
 * @global type $course_code
 * @global type $urlServer
 * @global type $langNoLearningPath
 * @global type $langLearningPaths
 * @global type $langComments
 * @global type $langChoice
 * @global type $langAddModulesButton
 * @global type $langValue
 * @global type $langOperator
 * @param type $element
 * @param type $element_id
 */
function display_available_lps($element, $element_id) {

    global $course_id, $course_code, $urlServer, $tool_content, 
           $langNoLearningPath, $langLearningPaths, $langComments, $langChoice, 
           $langAddModulesButton, $langOperator, $langValue;
    
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE lp_learnPath.course_id = ?d
                                            AND lp_learnPath.visible = 1
                                            AND lp_learnPath.learnPath_id NOT IN 
                                        (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                                    AND resource!='' 
                                                    AND activity_type = 'learning path' 
                                                    AND module = " . MODULE_ID_LP . ")", $course_id, $element_id);
    $lpinfo = array(); 
    foreach ($result as $row) {
        $lpinfo[] = array(
            'id' => $row->learnPath_id,
            'name' => $row->name,
            'comment' => $row->comment,
            'visible' => $row->visible,
            'rank' => $row->rank);
    }
    if (count($lpinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoLearningPath</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th width='50%'>$langLearningPaths</th>" .
                "<th class='text-left'>$langComments</th>" .
                "<th style='width:5px;'>$langOperator</th>" .
                "<th style='width:50px;'>$langValue</th>" . 
                "<th style='width:10px;' class='text-center'>$langChoice</th>" .                                
                "</tr>";        
        foreach ($lpinfo as $entry) {
            if ($entry['visible'] == 0) {
                $vis = 'not_visible';
                $disabled = 'disabled';
            } else {
                $vis = '';
                $disabled = '';
            }            
            $m_id = Database::get()->querySingle("SELECT module_id FROM lp_rel_learnPath_module WHERE learnPath_id = ?d 
                                                    AND rank = (SELECT MIN(rank) FROM lp_rel_learnPath_module WHERE learnPath_id = ?d)", 
                                                $entry['id'], $entry['id']);
            if (($m_id) and $m_id->module_id > 0) {
                $lp_id = $entry['id'];
                $tool_content .= "<tr class='$vis'>";
                $tool_content .= "<td>&nbsp;".icon('fa-ellipsis-h')."&nbsp;&nbsp;<a href='${urlServer}modules/learnPath/viewer.php?course=$course_code&amp;path_id=$lp_id&amp;module_id=$m_id->module_id'>" . q($entry['name']) . "</a></td>";
                $tool_content .= "<td>" . $entry['comment'] . "</td>";
                $tool_content .= "<td>". selection(get_operators(), "operator[$lp_id]") . "</td>";
                $tool_content .= "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$lp_id]' value=''></td>";
                $tool_content .= "<td class='text-center'><input type='checkbox' name='lp[]' value='$lp_id' $disabled></td>";
                $tool_content .= "</tr>";            
            }
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_lp' value='$langAddModulesButton'></div></form>";
        
    }      
}

function display_available_ratings($element, $element_id) {
    $tool_content .= '..Still working on this...';

    return $tool_content;
}
  

/**
 * @brief multimedia display form
 * @global type $tool_content
 * @global type $themeimg
 * @global type $course_id
 * @global type $langTitle
 * @global type $langDescription
 * @global type $langDate
 * @global type $langChoice
 * @global type $langAddModulesButton
 * @global type $langNoVideo
 * @global type $course_code
 * @param type $element
 * @param type $element_id
 */
function display_available_multimedia($element, $element_id) {
          
    require_once 'include/lib/mediaresource.factory.php';
    require_once 'include/lib/multimediahelper.class.php';

    global $tool_content, $themeimg, $course_id,
                $langTitle, $langDescription, $langDate, $langChoice,
                $langAddModulesButton, $langNoVideo, $course_code;
               
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $count = 0;
    $video_found = FALSE;
    $cnt1 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM video WHERE course_id = ?d", $course_id)->cnt;
    $cnt2 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM videolink WHERE course_id = ?d", $course_id)->cnt;
    $count = $cnt1 + $cnt2;    
    if ($count > 0) {
        $video_found = TRUE;
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" . 
                         "<input type='hidden' name='$element_name' value='$element_id'>" .
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'>" .
                         "<th width='200' class='text-left'>&nbsp;$langTitle</th>" .
                         "<th class='text-left'>$langDescription</th>" .
                         "<th width='100'>$langDate</th>" .
                         "<th width='80'>$langChoice</th>" .
                         "</tr>";
        foreach (array('video', 'videolink') as $table) {
            $result = Database::get()->queryArray("SELECT * FROM $table WHERE (category IS NULL OR category = 0) 
                                                        AND course_id = ?d
                                                        AND visible = 1
                                                        AND id NOT IN 
                                                (SELECT resource FROM ${element}_criterion WHERE $element = ?d                                                     
                                                    AND resource!=''
                                                    AND activity_type IN ('video','videolink') AND module = ". MODULE_ID_VIDEO . ")", $course_id, $element_id);
            foreach ($result as $row) {
                $row->course_id = $course_id;
                if ($table == 'video') {
                    $vObj = MediaResourceFactory::initFromVideo($row);
                    $videolink = MultimediaHelper::chooseMediaAhref($vObj);
                } else {
                    $vObj = MediaResourceFactory::initFromVideoLink($row);
                    $videolink = MultimediaHelper::chooseMedialinkAhref($vObj);
                }                
                $tool_content .= "<td>&nbsp;".icon('fa-film')."&nbsp;&nbsp;" . $videolink . "</td>".
                                 "<td>" . q($row->description) . "</td>".
                                 "<td class='text-center'>" . nice_format($row->date, true, true) . "</td>" .
                                 "<td class='text-center'><input type='checkbox' name='video[]' value='$table:$row->id'></td>" .
                                 "</tr>";                
            }
        }
        $sql = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY name", $course_id);
        if ($sql) {
            foreach ($sql as $videocat) {
                $tool_content .= "<tr>";
                $tool_content .= "<td>".icon('fa-folder-o')."&nbsp;&nbsp;" .
                                 q($videocat->name) . "</td>";
                $tool_content .= "<td colspan='2'>" . standard_text_escape($videocat->description) . "</td>";
                $tool_content .= "<td align='center'><input type='checkbox' name='videocatlink[]' value='$videocat->id' /></td>";
                $tool_content .= "</tr>";
                foreach (array('video', 'videolink') as $table) {
                    $sql2 = Database::get()->queryArray("SELECT * FROM $table WHERE category = ?d
                                                        AND visible = 1
                                                        AND id NOT IN 
                                                    (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                                        AND resource!=''
                                                        AND activity_type IN ('video','videolink') AND module = " . MODULE_ID_VIDEO . ")", $videocat->id, $element_id);
                    foreach ($sql2 as $linkvideocat) {
                            $tool_content .= "<tr>";
                            $tool_content .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='$themeimg/links_on.png' />&nbsp;&nbsp;<a href='" . q($linkvideocat->url) . "' target='_blank'>" .
                                    q(($linkvideocat->title == '')? $linkvideocat->url: $linkvideocat->title) . "</a></td>";
                            $tool_content .= "<td>" . standard_text_escape($linkvideocat->description) . "</td>";
                            $tool_content .= "<td class='text-center'>" . nice_format($linkvideocat->date, true, true) . "</td>";
                            $tool_content .= "<td class='text-center'><input type='checkbox' name='video[]' value='$table:$linkvideocat->id' /></td>";
                            $tool_content .= "</tr>";	
                    }
                }
            }
        }
        $tool_content .= "</table><div class='text-right'><input class='btn btn-primary' type='submit' name='add_multimedia' value='".q($langAddModulesButton)."'>&nbsp;&nbsp;</div></form>";
    }
    if (!$video_found) {
        $tool_content .= "<div class='alert alert-warning'>$langNoVideo</div>";
    }   
}


/**
 * @brief ebook display form
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $urlServer
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoEBook
 * @global type $langEBook
 * @global type $course_code
 * @param type $element
 * @param type $element_id
 */
function display_available_ebooks($element, $element_id) {
  
  global $course_id, $course_code, $tool_content, $urlServer,
    $langAddModulesButton, $langChoice, $langNoEBook,
    $langEBook, $course_code;
    
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT * FROM ebook WHERE ebook.course_id = ?d
                                                AND ebook.visible = 1
                                                AND ebook.id NOT IN 
                                        (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                        AND resource!='' AND activity_type = 'ebook' AND module = " . MODULE_ID_EBOOK . ")", $course_id, $element_id);
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoEBook</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langEBook</th>" .                
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        $unit_parameter = 'unit=' . $id;
        foreach ($result as $catrow) {        
            $tool_content .= "<tr>";
            $tool_content .= "<td class='bold'>".icon('fa-book')."&nbsp;&nbsp;" .
                    q($catrow->title) . "</td>";
            $tool_content .= "<td class='text-center'>
                            <input type='checkbox' name='ebook[]' value='$catrow->id' />
                            <input type='hidden' name='ebook_title[$catrow->id]'
                               value='" . q($catrow->title) . "'></td>";
            $tool_content .= "</tr>";
            $q = Database::get()->queryArray("SELECT ebook_section.id AS sid,
                                    ebook_section.public_id AS psid,
                                    ebook_section.title AS section_title,
                                    ebook_subsection.id AS ssid,
                                    ebook_subsection.public_id AS pssid,
                                    ebook_subsection.title AS subsection_title,
                                    document.path,
                                    document.filename
                                    FROM ebook, ebook_section, ebook_subsection, document
                                    WHERE ebook.id = ?d AND
                                        ebook.course_id = ?d AND
                                        ebook_section.ebook_id = ebook.id AND
                                        ebook_section.id = ebook_subsection.section_id AND
                                        document.id = ebook_subsection.file_id AND
                                        document.course_id = ?d AND
                                        document.subsystem = " . EBOOK . "
                                        ORDER BY CONVERT(psid, UNSIGNED), psid,
                                                 CONVERT(pssid, UNSIGNED), pssid", $catrow->id, $course_id, $course_id);

            $ebook_url_base = "{$urlServer}modules/ebook/show.php/$course_code/$catrow->id/";
            $old_sid = false;            
            foreach ($q as $row) {                
                $sid = $row->sid;
                $ssid = $row->ssid;
                $display_id = $sid . ',' . $ssid;
                $surl = $ebook_url_base . $display_id . '/' . $unit_parameter;
                if ($old_sid != $sid) {
                    $tool_content .= "<tr>
                                    <td class='section'>".icon('fa-link')."&nbsp;&nbsp;
                                        " . q($row->section_title) . "</td>
                                    <td align='center'><input type='checkbox' name='section[]' value='$sid' />
                                        <input type='hidden' name='section_title[$sid]'
                                               value='" . q($row->section_title) . "'></td></tr>";
                }
                $tool_content .= "<tr>
                                <td class='subsection'>".icon('fa-link')."&nbsp;&nbsp;
                                <a href='" . q($surl) . "' target='_blank'>" . q($row->subsection_title) . "</a></td>
                                <td align='center'><input type='checkbox' name='subsection[]' value='$ssid' />
                                   <input type='hidden' name='subsection_title[$ssid]'
                                          value='" . q($row->subsection_title) . "'></td>
                            </tr>";
                $old_sid = $sid;
            }
        }
        $tool_content .= 
                "</table>
                <div class='text-right'>
                <input class='btn btn-primary' type='submit' name='add_ebook' value='$langAddModulesButton' /></div></form>";
    }  
}


/**
 * @brief poll display form
 * @global type $course_id
 * @global type $course_code
 * @global type $urlServer
 * @global type $tool_content 
 * @global type $langPollNone
 * @global type $langQuestionnaire
 * @global type $langChoice
 * @global type $langAddModulesButton 
 * @param type $element
 * @param type $element_id
 */
function display_available_polls($element, $element_id) {
    
    global $course_id, $course_code, $urlServer, $tool_content,
            $langPollNone, $langQuestionnaire, $langChoice, $langAddModulesButton;            
      
    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT * FROM poll WHERE poll.course_id = ?d
                                    AND poll.active = 1
                                    AND poll.pid NOT IN 
                                (SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                    AND resource != '' AND activity_type = 'questionnaire' AND module = " . MODULE_ID_QUESTIONNAIRE . ")", 
                        $course_id, $element_id);
    
    $pollinfo = array();
    foreach ($result as $row) {
        $pollinfo[] = array(
            'id' => $row->pid,
            'title' => $row->name,
            'active' => $row->active);
    }
    if (count($pollinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langPollNone</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langQuestionnaire</th>" .                
                "<th style='width:80px;' class='text-center'>$langChoice</th>" .
                "</tr>";        
        foreach ($pollinfo as $entry) {            
            $tool_content .= "<tr>";
            $tool_content .= "<td>&nbsp;".icon('fa-question')."&nbsp;&nbsp;<a href='${urlServer}modules/questionnaire/pollresults.php?course=$course_code&amp;pid=$entry[id]'>" . q($entry['title']) . "</a></td>";            
            $tool_content .= "<td class='text-center'><input type='checkbox' name='poll[]' value='$entry[id]'></td>";            
            $tool_content .= "</tr>";            
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_poll' value='$langAddModulesButton'></div></form>";
    }      
}

/**
 * @brief wiki display form 
 * @global type $tool_content 
 * @global type $langAddModulesButton
 * @global type $langChoice  
 * @global type $course_code
 * @global type $langValue
 * @global type $langTitle
 * @global type $langWikiPages
 * @global type $langOperator
 * @global type $langResourceAlreadyAdded
 * @param type $element
 * @param type $element_id
 */
function display_available_wiki($element, $element_id) {
    
    global $tool_content, $langResourceAlreadyAdded,
    $langAddModulesButton, $langChoice, $langTitle, $langWikiPages,
    $course_code, $langOperator, $langValue;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $result = Database::get()->queryArray("SELECT resource FROM ${element}_criterion WHERE $element = ?d 
                                            AND resource IS NULL
                                            AND activity_type = 'wiki' 
                                            AND module = " . MODULE_ID_WIKI . "", $element_id);    
    if (count($result) > 0) {
        $tool_content .= "<div class='alert alert-warning'>$langResourceAlreadyAdded</div>";
    } else {
        $tool_content .= "<form action='index.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:70%;'>&nbsp;$langTitle</th>" .
                "<th style='width:5px;'>&nbsp;$langOperator</th>" .
                "<th style='width:30px;'>$langValue</th>" . 
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
                
        $tool_content .= "<tr>
                            <td>$langWikiPages</td>
                            <td>". selection(get_operators(), "operator") . "</td>
                            <td class='text-center'><input style='width:50px;' type='text' name='threshold' value=''></td>
                            <td align='center'><input type='checkbox' name='wiki' value='1'></td>
                        </tr>";
        
        $tool_content .= "
                    </table>
                <div class='text-right'>
                    <input class='btn btn-primary' type='submit' name='add_wiki' value='$langAddModulesButton'>
                </div></form>";
    }
}


/**
 * @brief display badge / certificate settings
 * @global type $tool_content
 * @global type $course_id
 * @global type $course_code
 * @global type $langDescription
 * @global type $langConfig
 * @global type $langMessage
 * @global type $langpublisher
 * @global type $langCertificateDetails
 * @global type $langBadgeDetails
 * @param type $element
 * @param type $element_id
 */
function display_certificate_settings($element, $element_id) {
    
    global $tool_content, $course_id, $course_code, $urlServer,
           $langDescription, $langConfig, $langMessage, 
           $langpublisher, $langCertificateDetails, $langBadgeDetails;

    $field = ($element == 'certificate')? 'template' : 'icon';
    $header = ($element == 'certificate')? "$langCertificateDetails" : "$langBadgeDetails";
    $data = Database::get()->querySingle("SELECT issuer, $field, title, description, message, active, bundle 
                            FROM $element WHERE id = ?d AND course_id = ?d", $element_id, $course_id);
    $issuer = $data->issuer;
    $template = $data->$field;
    $title = $data->title;
    $description = $data->description;
    $message = $data->message;
    $icon = '';
    if ($element == 'badge') {
        $badge_filename = Database::get()->querySingle("SELECT filename FROM badge_icon WHERE id = ?d", $element_id)->filename;        
        $icon_link = $urlServer . BADGE_TEMPLATE_PATH . "$badge_filename";
        $icon = "<div><img src='$icon_link' width='60' height='60'></div>";
    }
           
    $tool_content .= "<div class='panel panel-action-btn-primary'>";
    $tool_content .= "<div class='panel-heading'>
            <h3 class='panel-title'>
                $header &nbsp;
                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;${element}_id=$element_id&amp;edit=1'>
                    <span class='fa fa-edit' title='' data-toggle='tooltip' data-original-title='$langConfig'></span>
                </a>
            </h3>
        </div>";            
        
    $tool_content .= "<div class='panel-body'>";
    $tool_content .= $icon;
    $tool_content .= "<div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langDescription:</strong>
                </div>
                <div class='col-sm-9'>
                    " . $description . "
                </div>
            </div>";
    $tool_content .= "<div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langMessage:</strong>
                </div>
                <div class='col-sm-9'>
                    " . $message . "
                </div>
            </div>";
    $tool_content .= "<div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langpublisher:</strong>
                </div>
                <div class='col-sm-9'>
                    " . $issuer . "
                </div>
            </div>";
    $tool_content .= "</div></div>";        
    
}



/**
 * @brief add / edit certificate / badge settings
 * @global string $tool_content
 * @global type $course_code
 * @global type $course_id
 * @global type $langTitle
 * @global type $langSave
 * @global type $langInsert
 * @global type $langActivate
 * @global type $langDescription
 * @global type $langpublisher
 * @global type $langMessage
 * @global type $langTemplate
 * @param type $element
 * @param type $element_id
 */
function certificate_settings($element, $element_id = 0) {

    global $tool_content, $course_code, $langTemplate, $course_id, 
           $langTitle, $langSave, $langInsert, $langMessage,
           $langActivate, $langDescription, $langpublisher, $langIcon;
               
    if ($element_id > 0) {      // edit
        $field = ($element == 'certificate')? 'template' : 'icon';
        $data = Database::get()->querySingle("SELECT issuer, $field, title, description, message, active, bundle 
                                FROM $element WHERE id = ?d AND course_id = ?d", $element_id, $course_id);
        $issuer = $data->issuer;
        $template = $data->$field;
        $title = $data->title;
        $description = $data->description;
        $message = $data->message;        
        $cert_id = ($element == 'certificate')? "<input type='hidden' name='certificate_id' value='$element_id'>" : "<input type='hidden' name='badge_id' value='$element_id'>";
        $name = 'edit_element';
    } else {        // add
        $issuer = q(get_config('institution'));
        $template = '';        
        $title = '';
        $description = '';
        $message = '';        
        $cert_id = '';
        $name = ($element == 'certificate')? 'newCertificate' : 'newBadge';
    }
    
    $tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>$langTitle</label>            
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' placeholder='$langTitle' name='title' value='$title'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='description' class='col-sm-2 control-label'>$langDescription: </label>
                        <div class='col-sm-10'>
                            " . rich_text_editor('description', 2, 60, $description) . "
                        </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>";
                    $tool_content .= ($element == 'certificate') ? $langTemplate : $langIcon;
                    $tool_content .= "</label>
                        <div class='col-sm-10'>";
                            $tool_content .= ($element == 'certificate') ? selection(get_certificate_templates(), 'template', $template) : selection(get_badge_icons(), 'template', $template);
                        $tool_content .= "</div>
                </div>
                <div class='form-group'>
                    <label for='message' class='col-sm-2 control-label'>$langMessage</label>
                    <div class='col-sm-10'>                        
                        " . rich_text_editor('message', 4, 60, $message) . "                        
                    </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>$langpublisher</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='issuer' value='$issuer'>
                    </div>
                </div>                
                $cert_id";                                 
                $tool_content .= "<div class='form-group'>
                    <div class='col-xs-12'>".form_buttons(array(
                        array(
                                'text' => $langSave,
                                'name' => $name,
                                'value'=> $langInsert
                            ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                            )
                        ))."</div>
                </div>
            </form>
        </div>";
}


/**
 * @brief student view certificates / badges
 * @global type $uid
 * @global type $course_id
 * @global type $urlServer
 */
function student_view_progress() {
    
    global $uid, $course_id, $urlServer;
    
    require_once 'Game.php';

    // check for completeness in order to refresh user data
    Game::checkCompleteness($uid, $course_id);

    $data = array();
    $iter = array('certificate', 'badge');

    // initialize data vars for template
    foreach ($iter as $key) {
        $data['game_' . $key] = array();
    }

    // populate with data
    foreach ($iter as $key) {
        $gameQ = "select a.*, b.title,"
                . " b.description, b.active, b.created, b.id"
                . " from user_{$key} a "
                . " join {$key} b on (a.{$key} = b.id) "
                . " where a.user = ?d and b.course_id = ?d and b.active = 1";
        Database::get()->queryFunc($gameQ, function($game) use ($key, &$data) {            
            if ($key == 'badge') { // get badge icon                
                $game->filename = Database::get()->querySingle("SELECT filename FROM badge_icon WHERE id = 
                                                                    (SELECT icon FROM badge WHERE id = ?d)", $game->id)->filename;           
            }
            $data['game_' . $key][] = $game;
        }, $uid, $course_id);
    }    
    $data['badge_template_path'] = $urlServer . BADGE_TEMPLATE_PATH;
    view('modules.progress.progress', $data);        
}


/**
 * @brief display users progress (teacher view)
 * @global type $tool_content 
 * @global type $course_code
 * @global type $course_id
 * @global type $langNoCertificateUsers
 * @global type $langName
 * @global type $langSurname
 * @global type $langAmShort
 * @global type $langID
 * @global type $langProgress
 * @global type $langUsersCertResults
 * @global type $langUsersS
 * @param type $element
 * @param type $element_id
 */
function display_users_progress($element, $element_id) {
    
    global $tool_content, $course_code, $course_id, $langNoCertificateUsers, $langName, $langUsersS,
           $langSurname, $langAmShort, $langID, $langProgress, $langDetails, $langUsersCertResults;
        
    if ($element == 'certificate') {
        $sql = Database::get()->queryArray("SELECT user, completed, completed_criteria, total_criteria FROM user_certificate 
                                            WHERE certificate = ?d", $element_id);
        $certified_users = Database::get()->querySingle("SELECT COUNT(*) AS t FROM user_certificate WHERE 
                                                    completed = 1 AND certificate = ?d", $element_id)->t;
        $param_name = 'certificate_id';
    } else {
        $sql = Database::get()->queryArray("SELECT user, completed, completed_criteria, total_criteria FROM user_badge 
                                            WHERE badge = ?d", $element_id);
        $certified_users = Database::get()->querySingle("SELECT COUNT(*) AS t FROM user_badge WHERE 
                                                    completed = 1 AND badge = ?d", $element_id)->t;
        $param_name = 'badge_id';
    }
    $all_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user 
                                        WHERE `user`.`id` = `course_user`.`user_id`
                                        AND `course_user`.`course_id` = ?d", $course_id)->total;
    
    if (count($sql) > 0) {
        $tool_content .= "<div class='alert alert-info'>$langUsersCertResults $certified_users / $all_users $langUsersS.</div>";
        /*              <th class='text-center'>".icon('fa-cogs')."</th> */
            //$tool_content .= "<table id='users_table{$course_id}' class='table-default custom_list_order'>";            
        $tool_content .= "<table class='table-default custom_list_order'>";
            $tool_content .= "<thead>
                        <tr>
                          <th style='width:5%'>$langID</th>
                          <th>$langName $langSurname</th>
                          <th style='width:10%;'>$langProgress</th>
                        </tr>
                    </thead>
                    <tbody>";
        $cnt = 1;
        foreach ($sql as $user_data) {
            if ($user_data->completed == 1) {
                $icon = icon('fa-check-circle');
            } else {
                $icon = icon('fa-hourglass-2');
            }
            $tool_content .= "<tr>
                    <td>". $cnt++ . "</td>
                    <td>" . display_user($user_data->user). "<br>" .
                    "($langAmShort: ". uid_to_am($user_data->user) . ")</td>
                    <td>" . round($user_data->completed_criteria / $user_data->total_criteria * 100, 0) . "%&nbsp;&nbsp;$icon"
                          . "<small><a href='index.php?course=$course_code&amp;$param_name=$element_id&amp;u=$user_data->user'>$langDetails</a></small>
                    </td>
                    </tr>";            
        }
        $tool_content .= "</tbody></table>";
    } else {
        $tool_content .= "<div class='alert alert-info'>$langNoCertificateUsers</div>";
    }        
}


/**
 * @brief detailed view of user progress in various subsystems
 * @global type $tool_content
 * @global type $langNoUserActivity
 * @global type $langAttendanceActivity
 * @global type $langInstallEnd
 * @global type $langTotalPercentCompleteness
 * @param type $element
 * @param type $element_id
 * @param type $user_id
 */
function display_user_progress_details($element, $element_id, $user_id) {
    
    global $tool_content, $langNoUserActivity, $langAttendanceActivity, 
           $langInstallEnd, $langTotalPercentCompleteness;
    
    $resource_data = array();
    
    $tool_content .= "<h5>" .  uid_to_name($user_id) . "</h5>";
    // certificate
    if ($element == 'certificate') { // completed user resources
        $sql = Database::get()->queryArray("SELECT certificate_criterion FROM user_certificate_criterion JOIN certificate_criterion 
                                                            ON user_certificate_criterion.certificate_criterion = certificate_criterion.id 
                                                                AND certificate_criterion.certificate = ?d 
                                                                AND user = ?d", $element_id, $user_id);
        // incomplete user resources
        $sql2 = Database::get()->queryArray("SELECT id FROM certificate_criterion WHERE certificate = ?d 
                                                    AND id NOT IN 
                                            (SELECT certificate_criterion FROM user_certificate_criterion JOIN certificate_criterion 
                                                ON user_certificate_criterion.certificate_criterion = certificate_criterion.id 
                                                AND certificate_criterion.certificate = ?d AND user = ?d)", $element_id, $element_id, $user_id);
        $sql3 = "SELECT completed, completed_criteria, total_criteria FROM user_certificate WHERE certificate = ?d AND user = ?d";
    } else { // badge
        $sql = Database::get()->queryArray("SELECT badge_criterion FROM user_badge_criterion JOIN badge_criterion 
                                                            ON user_badge_criterion.badge_criterion = badge_criterion.id 
                                                                AND badge_criterion.badge = ?d 
                                                                AND user = ?d", $element_id, $user_id);
        // incomplete user resources
        $sql2 = Database::get()->queryArray("SELECT id FROM badge_criterion WHERE badge = ?d 
                                                    AND id NOT IN 
                                            (SELECT badge_criterion FROM user_badge_criterion JOIN badge_criterion 
                                                ON user_badge_criterion.badge_criterion = badge_criterion.id 
                                                AND badge_criterion.badge = ?d AND user = ?d)", $element_id, $element_id, $user_id);
        $sql3 = "SELECT completed, completed_criteria, total_criteria FROM user_badge WHERE badge = ?d AND user = ?d";
    }
    if (count($sql) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoUserActivity</div>";
    }
    $tool_content .= "<table class='table-default custom_list_order'>";
    $tool_content .= "<thead>
                <tr>
                  <th>$langAttendanceActivity</th>
                  <th style='width:10px;'>$langInstallEnd</th>
                </tr>
            </thead>
            <tbody>";            
        foreach ($sql as $user_criterion) {
            $resource_data = get_resource_details($element, $user_criterion);                
            $activity = $resource_data['title'] . "&nbsp;<small>(" .$resource_data['type'] . ")</small>";
            $tool_content .= "<tr>
                    <td>" . $activity . "</td>
                    <td class='text-center'>" . icon('fa-check-circle') . "</td>
                    </tr>";
        }
        foreach ($sql2 as $user_criterion) {
            $resource_data = get_resource_details($element, $user_criterion);                
            $activity = $resource_data['title'] . "&nbsp;<small>(" .$resource_data['type'] . ")</small>";
            $tool_content .= "<tr class='not_visible'>
                    <td>" . $activity . "</td>
                    <td class='text-center'>" . icon('fa-hourglass-2') . "</td>
                    </tr>";
        }
        $user_data = Database::get()->querySingle($sql3, $element_id, $user_id);            
        $tool_content .= "<tr>
                <td><strong>$langTotalPercentCompleteness</strong></td>
                <td class='text-center'><em>" . round($user_data->completed_criteria / $user_data->total_criteria * 100, 0) . "%</em></td>
                </tr>";
    $tool_content .= "</tbody></table>";    
}


/**
 * @brief return an array of operators
 * @return type
 */
function get_operators() {

    return array('gt' => '>',
                 'get' => '>=',
                 'lt' => '<',
                 'let' => '<=',
                 'eq' => '=',
                 'neq' => '!=');
}


/**
 * @brief return array with criteria having operators
 * @return type
 */
function criteria_with_operators() {
    
    return array('assignment', 
                 'exercise', 
                 'learning path',
                 'wiki',
                 'forum',
                 'forumtopic',
                 'blog',
                 'blogcomment',
                 'forum',
                 'forum_post');
}
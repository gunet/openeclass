<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**
 *  @file info.php
 *  @brief edit or create course unit
 */

$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'AddCourseUnits';
require_once '../../include/baseTheme.php';
require_once 'modules/tags/moduleElement.class.php';

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['assign_type'])) {
        if ($_POST['assign_type'] == 2) {
            $data = Database::get()->queryArray("SELECT name,id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
        } elseif ($_POST['assign_type'] == 1) {
            $data = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                    FROM user, course_user
                                    WHERE user.id = course_user.user_id
                                    AND course_user.course_id = ?d AND course_user.status = " . USER_STUDENT . "
                                    AND user.id ORDER BY surname", $course_id);
        }
        echo json_encode($data);
        exit;
    }
}

load_js('tools.js');
load_js('select2');
load_js('bootstrap-datepicker');

$data['start_week'] = $data['finish_week'] = '';
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $pageName = $langEditUnit;
    $cu = Database::get()->querySingle("SELECT id, title, comments, start_week, finish_week, assign_to_specific 
                                            FROM course_units 
                                            WHERE id = ?d 
                                            AND course_id = ?d",
                                        $id, $course_id);
    $navigation[] = [ 'name' => $cu->title, 'url' => "index.php?course=$course_code&id=$id"];
    if (!$cu) {
        Session::flash('message',$langUnknownResType);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("courses/$course_code/");
    }
    $unitDescr = $cu->comments;
    if (!(is_null($cu->start_week))) {
        $data['start_week'] = DateTime::createFromFormat('Y-m-d', $cu->start_week)->format('d-m-Y');
    }
    if (!(is_null($cu->finish_week))) {
        $data['finish_week'] = DateTime::createFromFormat('Y-m-d', $cu->finish_week)->format('d-m-Y');
    }
    $data['unitTitle'] = $cu->title;
    $data['unitId'] = $cu->id;

    $unassigned_options = $assignee_options = '';
    if ($cu->assign_to_specific) {
        //preparing options in select boxes for assigning to specific users/groups
        $assignee_options='';
        $unassigned_options='';
        if ($cu->assign_to_specific == 2) {
            $assignees = Database::get()->queryArray("SELECT `group`.id AS id, `group`.name
                FROM course_units_to_specific, `group`
                WHERE `group`.id = course_units_to_specific.group_id                    
                    AND course_units_to_specific.unit_id = ?d", $id);
            $all_groups = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d AND visible = 1", $course_id);
            foreach ($assignees as $assignee_row) {
                $assignee_options .= "<option value='".$assignee_row->id."'>".$assignee_row->name."</option>";
            }
            $unassigned = array_udiff($all_groups, $assignees,
                function ($obj_a, $obj_b) {
                    return $obj_a->id - $obj_b->id;
                }
            );
            foreach ($unassigned as $unassigned_row) {
                $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->name</option>";
            }
        } else {
            $assignees = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                FROM course_units_to_specific, user
                WHERE user.id = course_units_to_specific.user_id AND course_units_to_specific.unit_id = ?d", $id);
            $all_users = Database::get()->queryArray("SELECT user.id AS id, user.givenname, user.surname
                FROM user, course_user
                WHERE user.id = course_user.user_id
                AND course_user.course_id = ?d
                AND course_user.status = " . USER_STUDENT . "
                AND user.id", $course_id);
            foreach ($assignees as $assignee_row) {
                $assignee_options .= "<option value='$assignee_row->id'>$assignee_row->surname $assignee_row->givenname</option>";
            }
            $unassigned = array_udiff($all_users, $assignees,
                function ($obj_a, $obj_b) {
                    return $obj_a->id - $obj_b->id;
                }
            );
            foreach ($unassigned as $unassigned_row) {
                $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->surname $unassigned_row->givenname</option>";
            }
        }
    }

    $data['unitAssignToSpecific'] = $cu->assign_to_specific;
    $data['unassigned_options'] = $unassigned_options;
    $data['assignee_options'] = $assignee_options;


} else {
    $data['unitAssignToSpecific'] = $unitAssignToSpecific = 0;
    $data['unassigned_options'] = $unassigned_options = '';
    $data['assignee_options'] = $assignee_options = '';
    $pageName = $langAddUnit;
    $unitDescr = '';
    $data['unitTitle'] = $data['unitId'] = null;
}

$data['postUrl'] = "index.php?course=$course_code" .
    ($data['unitId'] ? "&id=$data[unitId]": '');
$data['descriptionEditor'] = rich_text_editor('unitdescr', 10, 20, $unitDescr);
$data['tagInput'] = $data['unitId']? eClassTag::tagInput($data['unitId']): eClassTag::tagInput();

view('modules.units.info', $data);

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

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'prequesities';

require_once '../../include/baseTheme.php';
load_js('select2');

$prereqs_url = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langCoursePrerequisites);
$toolName = $langCoursePrerequisites;

if (isset($_GET['add'])) {
    $pageName = $langNewCoursePrerequisite;
    $navigation[] = $prereqs_url;

    view('modules.course_prerequisites.new');
} else {
    if (isset($_POST['addcommit'])) {
        $prereqId = intval($_POST['prerequisite_course']);
        add_prereq($prereqId);
    }
    if (isset($_GET['del'])) {
        $prereqId = intval($_GET['del']);
        del_prereq($prereqId);
    }
    $data['result'] = $result = Database::get()->queryArray("SELECT c.*
                                 FROM course_prerequisite cp 
                                 JOIN course c on (c.id = cp.prerequisite_course) 
                                 WHERE cp.course_id = ?d 
                                 ORDER BY c.title", $course_id);

    $action_bar = action_bar(array(
        array('title' => $langNewCoursePrerequisite,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=1",
            'button-class' => 'btn-success',
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label')
    ));

    $data['action_bar'] = $action_bar;

    view('modules.course_prerequisites.index', $data);
}

function add_prereq($prereqId) {
    global $course_id, $langNewCoursePrerequisiteSuccess, $langNewCoursePrerequisiteFailInvalid,
           $langNewCoursePrerequisiteFailSelf, $langNewCoursePrerequisiteFailAlreadyIn,
           $langNewCoursePrerequisiteFailBadgeMissing;

    // check invalid
    if ($prereqId <= 0) {
        Session::flash('message',$langNewCoursePrerequisiteFailInvalid);
        Session::flash('alert-class', 'alert-danger');
        return;
    }

    // check the prereq same as current course
    if ($prereqId == $course_id) {
        Session::flash('message',$langNewCoursePrerequisiteFailSelf);
        Session::flash('alert-class', 'alert-danger');
        return;
    }

    // check already exists
    $result = Database::get()->queryArray("SELECT cp.id
                                 FROM course_prerequisite cp 
                                 WHERE cp.course_id = ?d
                                 AND cp.prerequisite_course = ?d", $course_id, $prereqId);
    if (count($result) > 0) {
        Session::flash('message',$langNewCoursePrerequisiteFailAlreadyIn);
        Session::flash('alert-class', 'alert-danger');
        return;
    }

    // check if badge for course completion exists
    $result = Database::get()->queryArray("SELECT id
                                 FROM badge  
                                 WHERE course_id = ?d
                                 AND bundle = -1 AND active = 1", $prereqId);
    if (!$result) {
        Session::flash('message',$langNewCoursePrerequisiteFailBadgeMissing);
        Session::flash('alert-class', 'alert-danger');
        return;
    }

    Session::flash('message',$langNewCoursePrerequisiteSuccess);
    Session::flash('alert-class', 'alert-success');
    Database::get()->query("INSERT INTO course_prerequisite (course_id, prerequisite_course) VALUES (?d, ?d)", $course_id, $prereqId);
}

function del_prereq($prereqId) {
    global $course_id, $langDelCoursePrerequisiteSuccess;

    if ($prereqId <= 0) {
        return;
    }
    Session::flash('message',$langDelCoursePrerequisiteSuccess);
    Session::flash('alert-class', 'alert-success');
    Database::get()->query("DELETE FROM course_prerequisite WHERE course_id = ?d AND prerequisite_course = ?d", $course_id, $prereqId);
}

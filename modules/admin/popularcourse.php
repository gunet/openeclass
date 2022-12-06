<?php

/* ========================================================================
 * Open eClass 4.0
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
 * @file popularcours.php
 * @brief search on courses by title, code, type and faculty
 */


require_once '../../include/baseTheme.php';

if(isset($_POST['submit'])){
    if(!empty($_POST['set_popular_course'])){
        $ids_popular_courses = $_POST['set_popular_course'];
        $ids_popular_courses = implode(', ', $ids_popular_courses);
        $sql = ('UPDATE course SET popular_course = 1 WHERE id IN ('.$ids_popular_courses.')' );
        Database::get()->query($sql);
        $sql = ('UPDATE course SET popular_course = 0 WHERE id NOT IN ('.$ids_popular_courses.')' );
        Database::get()->query($sql);
    }else{
        $sql = ('UPDATE course SET popular_course = 0');
        Database::get()->query($sql);
    }
    Session::flash('message',$langFaqEditSuccess); 
    Session::flash('alert-class', 'alert-success');
}


$toolName = $langPopularCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

$data['courses'] = Database::get()->queryArray("SELECT *FROM course WHERE visible != ?d", 3);


view('admin.courses.popularcours', $data);
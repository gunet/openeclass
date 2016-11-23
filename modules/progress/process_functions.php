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
 * @brief add assignment db entries in certificate criterion 
 * @param type $certificate_id
 * @return type
 */
function add_assignment_to_certificate($certificate_id) {
            
    if (isset($_POST['assignment'])) {
        foreach ($_POST['assignment'] as $data) {
            Database::get()->query("INSERT INTO certificate_criterion
                    SET certificate = ?d, module=5, resource = ?d, activity_type = 'assignment'",
                $certificate_id, $data);
        }
    }
    return;    
}


/**
 * @brief add exercise db entries in certificate criterion 
 * @param type $certificate_id
 * @return type
 */
function add_exercise_to_certificate($certificate_id) {
    
    if (isset($_POST['exercise'])) {
        foreach ($_POST['exercise'] as $data) {    
            Database::get()->query("INSERT INTO certificate_criterion
                    SET certificate = ?d, module = 10, resource = ?d, activity_type = 'exercise'",
                $certificate_id, $data);
        }
    }
    return;
}



function add_lp_to_certificate($certificate_id) {
    
    if (isset($_POST['lp'])) {
        foreach ($_POST['lp'] as $data) {
            Database::get()->query("INSERT INTO certificate_criterion
                    SET certificate = ?d, module = 23, resource = ?d ,activity_type = 'learning path'",
                $certificate_id, $data);
        }
    }
}


function delete_certificate_activity($certificate_id, $activity_id) {

    global $langAttendanceDel, $langAttendanceDelFailure;

    $delAct = Database::get()->query("DELETE FROM certificate_criterion WHERE id = ?d AND certificate = ?d", $activity_id, $certificate_id)->affectedRows;
    if($delAct) {
        Session::Messages("$langAttendanceDel", "alert-success");
    } else {
        Session::Messages("$langAttendanceDelFailure", "alert-danger");
    }
}



function get_certificate_title($certificate_id) {

    $at_title = Database::get()->querySingle("SELECT title FROM certificate WHERE id = ?d", $certificate_id)->title;

    return $at_title;
}



/**
 * @brief add certificate in DB
 * @global type $course_id
 * @param type $title
 * @param type $description
 * @param type $message
 * @param type $template
 * @param type $issuer
 * @param type $active
 * @return type
 */
function add_certificate($title, $description, $message, $template, $issuer, $active) {
    
    global $course_id;
    
    $new_cert_id = Database::get()->query("INSERT INTO certificate 
                                SET course_id = ?d,
                                title = ?s,
                                description = ?s,
                                message = ?s,
                                template = ?d,
                                issuer = ?s,
                                active = ?d", $course_id, $title, $description, $message, $template, $issuer, $active)->lastInsertID;
    return $new_cert_id;
    
}

/**
 * @brief modify certificate settings in DB
 * @global type $course_id
 * @param type $certificate_id
 * @param type $title
 * @param type $description
 * @param type $message
 * @param type $template
 * @param type $issuer
 * @param type $active
 */
function modify_certificate($certificate_id, $title, $description, $message, $template, $issuer, $active) {    
    
    global $course_id;
    
    Database::get()->query("UPDATE certificate SET title = ?s, 
                                                   description = ?s,
                                                   message = ?s,
                                                   template = ?d,
                                                   issuer = ?s,
                                                   active = ?d
                                                WHERE id = ?d AND course_id = ?d",
                                    $title, $description, $message, $template, $issuer, $active, $certificate_id, $course_id);
    
}


/**
 * @brief modify certificate visibility in DB
 * @global type $course_id
 * @param type $certificate_id
 * @param type $visibility
 */
function modify_certificate_visility($certificate_id, $visibility) {
    
    global $course_id;
    
    Database::get()->query("UPDATE certificate SET active = ?d WHERE id = ?d AND course_id = ?d", $visibility, $certificate_id, $course_id);
    
}

/**
 * @brief delete certificate in DB
 * @global type $course_id
 * @param type $certificate_id
 */
function delete_certificate($certificate_id) {

    global $course_id;

 /**   $r = Database::get()->queryArray("SELECT id FROM certificate_criterion WHERE certificate_id = ?d", $certificate_id);
    foreach ($r as $act) {
        delete_certificate_activity($certificate_id, $act->id);
    } */
    
    Database::get()->query("DELETE FROM certificate WHERE id = ?d AND course_id = ?d", $certificate_id, $course_id);

}
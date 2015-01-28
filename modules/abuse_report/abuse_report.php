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
 * Inject code for report flag icon
 * @param string $rtype
 * @param int $rid
 * @param int $course_id
 * @return string html flag icon
 */
function abuse_report_icon_flag ($rtype, $rid, $course_id) {
    global $head_content;
    
    $head_content .= '';
    
    $out = "";
    

    return $out;
}

/**
 * Inject code for report flag option in action button
 * @param string $rtype
 * @param int $rid
 * @param int $course_id
 * @return string html flag option for action button
 */
function abuse_report_action_button_flag ($rtype, $rid, $course_id) {

}

/**
 * Check if flag should be shown or not
 * @param string $rtype
 * @param int $rid
 * @param int $course_id
 * @param int $uid
 * @param boolean $is_editor
 * @return boolean
 */
function abuse_report_show_flag ($rtype, $rid, $course_id, $is_editor) {
    
    if (setting_get(SETTING_COURSE_ABUSE_REPORT_ENABLE, $course_id) != 1) {
        return false;
    } elseif ($is_editor) { //do not show for editor
        return false;
    } else {
        //check if there is already a report for this resource from this user
        $result = Database::get()->querySingle("SELECT COUNT(`id`) AS c FROM `abuse_report` WHERE `rtype` = ?s 
                AND `rid` = ?d AND `user_id` = ?d AND `course_id` = ?d", $rtype, $rid, $_SESSION['uid'], $course_id);
        if ($result->c != 0) {
            return false;
        }
        //check for each resource type if user is author
        if ($rtype == 'comment') {
            $result = Database::get()->querySingle("SELECT COUNT(`id`) AS c FROM `comments` WHERE `id` = ?d AND `user_id` = ?d", $rid, $_SESSION['uid']);
            if ($result->c != 0) {
                return false;
            }
        }
    }
    
    return true;

}
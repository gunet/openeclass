<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

$require_login = true;
$require_valid_uid = TRUE;
include '../../include/baseTheme.php';

$nameTools = $langEmailUnsubscribe;
$navigation[]= array ("url"=>"profile.php", "name"=> $langModifyProfile);

check_uid();

if (isset($_GET['submit'])) {        
        if (isset($_GET['cid'])) {  // change email subscription for one course
                $cid = intval($_GET['cid']);
                if (isset($_GET['c_unsub'])) {
                        db_query("UPDATE cours_user SET receive_mail = 1
                                WHERE user_id = $uid AND cours_id = $cid");        
                } else {
                        db_query("UPDATE cours_user SET receive_mail = 0
                                WHERE user_id = $uid AND cours_id = $cid");        
                }                
        $course_title = course_id_to_title($cid);        
        $tool_content .= "<div class='success'>".sprintf($course_title, $langEmailUnsubSuccess)."</div>";
        } else { // change email subscription for all courses
                foreach ($_SESSION['status'] as $course_code => $c_value) {
                        if (array_key_exists($course_code, $_GET['c_unsub'])) {                        
                                db_query("UPDATE cours_user SET receive_mail = 1
                                WHERE user_id = $uid AND cours_id = ". course_code_to_id($course_code));
                        } else {                        
                                 db_query("UPDATE cours_user SET receive_mail = 0
                                WHERE user_id = $uid AND cours_id = ". course_code_to_id($course_code));
                        }
                }
                $tool_content .= "<div class='success'>$langWikiEditionSucceed</div>";
        }        
        
} else {
        $tool_content .= "<form action='$_SERVER[PHP_SELF]'>";
        $tool_content .= "<div class='info'>$langInfoUnsubscribe</div>";
        if (isset($_GET['cid'])) { // one course only                
                $cid = intval($_GET['cid']);
                $course_title = course_id_to_title($cid);        
                $selected = get_user_email_notification($uid, $cid) ? 'checked': '';        
                $tool_content .= "<input type='checkbox' name='c_unsub' value='1' $selected>&nbsp;$course_title <br />";
                $tool_content .= "<input type='hidden' name='cid' value='$cid'>";
        } else { // displays all courses
                foreach ($_SESSION['status'] as $course_code => $status) {
                        $course_title = course_code_to_title($course_code);
                        $cid = course_code_to_id($course_code);        
                        $selected = get_user_email_notification($uid, $cid) ? 'checked': '';        
                        $tool_content .= "<input type='checkbox' name='c_unsub[$course_code]' value='1' $selected>&nbsp;$course_title <br />";
                }       
        }
        $tool_content .= "<br /><input type='submit' name='submit' value='$langSubmit'>";
        $tool_content .= "</form>";
}

draw($tool_content, 1);
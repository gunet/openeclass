<?php
/*
 * Open eClass 2.4 - E-learning and Course Management System
 * ========================================================================
 * Copyright(c) 2010  Greek Universities Network - GUnet
 *
 * User Profile
 *
 */

$require_login = true;
include '../../include/baseTheme.php';
$require_valid_uid = TRUE;

$nameTools = $langUserProfile;

$userdata = array();

if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
} else {
        $navigation[] = array("url" => "profile.php", "name" => $langModifProfile);
        $id = $uid;
}        
        $sql = "SELECT nom, prenom, email, am, department, has_icon FROM user WHERE user_id = $id";
        $userdata = db_query_get_single_row($sql);
        $tool_content .= q("$userdata[prenom] $userdata[nom]");
        if (!empty($userdata['email'])) {
                $tool_content .= "&nbsp;($userdata[email])";
        }
        $tool_content .= "<br /><br />$langAm: $userdata[am]";
        $tool_content .= "<br /><br />$langFaculty: ".find_faculty_by_id($userdata['department']);
        $tool_content .= "<br /><br />" . profile_image($id, IMAGESIZE_LARGE, !$userdata['has_icon']);

draw($tool_content, 1);
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
        $tool_content .= "<table width='100%' class='tbl_1'> <tr>";
        $tool_content .= "<th rowspan='3' width='256'>" . profile_image($id, IMAGESIZE_LARGE, !$userdata['has_icon']);
        $tool_content .= "</th>";        
        $tool_content .= "<td>".q("$userdata[prenom] $userdata[nom]");
        $tool_content .= "</td></tr><tr><td>";
        if (!empty($userdata['email'])) {
                $tool_content .= "&nbsp;($userdata[email])";
        }
        if (!empty($userdata['am'])) {
                $tool_content .= "<br /><br />$langAm: " . q($userdata['am']);
        }
        $tool_content .= "</td></tr><tr>";
        $tool_content .= "<td>$langFaculty: ".find_faculty_by_id($userdata['department']);
        $tool_content .= "</td></tr></table>";

draw($tool_content, 1);
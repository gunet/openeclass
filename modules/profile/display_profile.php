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
        $navigation[] = array('url' => 'profile.php', 'name' => $langModifProfile);
        $id = $uid;
}
$sql = "SELECT nom, prenom, email, am, department, has_icon, description FROM user WHERE user_id = $id";
$userdata = db_query_get_single_row($sql);
$tool_content .= "<table class='tbl'>
        <tr>
            <td>" . profile_image($id, IMAGESIZE_LARGE, !$userdata['has_icon']) . "</td>
            <td><b>" . q("$userdata[prenom] $userdata[nom]") . "</b><br>";
if (!empty($userdata['email'])) {
        $tool_content .= "<b>$langEmail:</b> " . mailto($userdata['email']) . "<br>";
}
if (!empty($userdata['am'])) {
        $tool_content .= "<b>$langAm:</b> " . q($userdata['am']) . "<br>";
}
$tool_content .= "<b>$langFaculty:</b> " . find_faculty_by_id($userdata['department']) . "<br>";
if (!empty($userdata['description'])) {
        $tool_content .= standard_text_escape($userdata['description']);
}
$tool_content .= "
       </td>
     </tr>
     </table>";

draw($tool_content, 1);

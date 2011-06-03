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
include '../../include/baseTheme.php';
$require_valid_uid = TRUE;

$nameTools = $langUserProfile;

$userdata = array();

if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
} else {
        $navigation[] = array('url' => 'profile.php', 'name' => $langModifyProfile);
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

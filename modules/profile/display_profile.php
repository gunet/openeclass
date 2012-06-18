<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';

$tree = new hierarchy();
$user = new user();

$nameTools = $langUserProfile;

$userdata = array();

if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
} else {
        $navigation[] = array('url' => 'profile.php', 'name' => $langModifyProfile);
        $id = $uid;
}
$userdata = db_query_get_single_row("SELECT user.nom, user.prenom, user.email, user.phone, user.am, 
                                            user.has_icon, user.description, 
                                            user.email_public, user.phone_public, user.am_public 
                                        FROM user 
                                        WHERE user.user_id = $id ");
$tool_content .= "<table class='tbl'>
        <tr>
            <td>" . profile_image($id, IMAGESIZE_LARGE, !$userdata['has_icon']) . "</td>
            <td><b>" . q("$userdata[prenom] $userdata[nom]") . "</b><br>";
if (!empty($userdata['email']) and allow_access($userdata['email_public'])) {
        $tool_content .= "<b>$langEmail:</b> " . mailto($userdata['email']) . "<br>";
}
if (!empty($userdata['am']) and allow_access($userdata['am_public'])) {
        $tool_content .= "<b>$langAm:</b> " . q($userdata['am']) . "<br>";
}
if (!empty($userdata['phone']) and allow_access($userdata['phone_public'])) {
        $tool_content .= "<b>$langPhone:</b> " . q($userdata['phone']) . "<br>";
}
$tool_content .= "<b>$langFaculty:</b> "; 

$departments = $user->getDepartmentIds($id);
$i = 1;
foreach ($departments as $dep) {
    $br = ($i < count($departments)) ? '<br/>' : '';
    $tool_content .= $tree->getFullPath($dep) . $br;
    $i++;
}

$tool_content .= "<br>";
if (!empty($userdata['description'])) {
        $tool_content .= standard_text_escape($userdata['description']);
}
$tool_content .= "</td></tr></table>";

draw($tool_content, 1);

function allow_access($level)
{
        global $uid, $statut;

        if ($level == ACCESS_USERS and $uid > 0) {
                return true;
        } elseif ($level == ACCESS_PROFS and $statut = 1) {
                return true;
        } else {
                return false;
        }
}

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

/*===========================================================================
 * @version $Id$

 @authors list: Karatzidis Stratos <kstratos@uom.gr>
 Vagelis Pitsioygas <vagpits@uom.gr>
 ==============================================================================
 @Description: Display all the available auth methods for user registration
 ==============================================================================
 */

include '../../include/baseTheme.php';
include 'auth.inc.php';

$nameTools = $langNewUser;
$tool_content .= "
<table class='tbl_1' width='100%'>
<tr>
<th width='160'>$langOfStudent</th>
<td>";

$auth = get_auth_active_methods();

// check for close user registration
if (get_config('close_user_registration')) {
    $newuser = "formuser.php";
    $user_reg_type = $langUserAccountInfo1;
} else {
    $newuser = "newuser.php";
    $user_reg_type = $langUserAccountInfo2;
}

$disable_eclass_stud_reg = get_config('disable_eclass_stud_reg');
if ($disable_eclass_stud_reg) {
	$tool_content .= "$user_reg_type";
}
else {
	$tool_content .= "<img src='$themeimg/arrow.png' title='bullet' alt='bullet'><a href='$newuser'>$user_reg_type</a>";
}

if (count($auth) > 1) {
   $tool_content .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;$langUserAccountInfo4:";
}

if(!empty($auth)) {
        foreach($auth as $k => $v) {
                if ($v != 1) {  // bypass the eclass auth method, as it has already been displayed
                        $tool_content .= "<br />&nbsp;&nbsp;&nbsp;<img src='$themeimg/arrow.png' title='bullet' alt='bullet' />&nbsp;<a href='altnewuser.php?auth=".$v."'>".get_auth_info($v)."</a>";
                }
        }
}

$tool_content .= "</td></tr><tr><th>$langOfTeacher</th><td>";

$disable_eclass_prof_reg = get_config('disable_eclass_prof_reg');
if ($disable_eclass_prof_reg) {
	$tool_content .= "$langUserAccountInfo1";
}
else {
	$tool_content .= "<img src='$themeimg/arrow.png' title='bullet'  alt='bullet' /><a href='formuser.php?p=1'>$langUserAccountInfo1</a>";
}

if (count($auth) > 1) {
   $tool_content .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;$langUserAccountInfo4:";
}

if(!empty($auth)) {
        foreach($auth as $k=>$v) {
                if ($v == 1) {  // bypass the eclass auth method, as it has already been displayed
                        continue;
                } else {
                                $tool_content .= "<br />&nbsp;&nbsp;&nbsp;<img src='$themeimg/arrow.png' title='bullet' alt='bullet' />&nbsp;<a href='altnewuser.php?p=1&amp;auth=".$v."'>".get_auth_info($v)."</a>";
                }
        }
} else {
        $tool_content .= "<p>$langCannotUseAuthMethods </p>";
}

$tool_content .= "</td></tr></table>";
draw($tool_content, 0);

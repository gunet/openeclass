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

include '../../include/baseTheme.php';
include 'auth.inc.php';

$user_registration = get_config('user_registration');
$eclass_prof_reg = get_config('eclass_prof_reg');
$alt_auth_prof_reg = get_config('alt_auth_prof_reg');
$eclass_stud_reg = get_config('eclass_stud_reg'); // student registration via eclass
$alt_auth_stud_reg = get_config('alt_auth_stud_reg'); //user registration via alternative auth methods

$toolName = $langRegistration;
$auth = get_auth_active_methods();

if ($user_registration) {
    $tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);

    if (isset($_GET['provider'])) {
        $provider = $_GET['provider'];
    }

    $registration_info = get_config('registration_info');
    if ($registration_info) {
        $tool_content .= "<div class='alert alert-info'>$registration_info</div>";
    } else {
        // student registration
        if ($eclass_stud_reg != FALSE or $alt_auth_stud_reg != FALSE) {
            $tool_content .= "<table class='table-default table-responsive'>";
            $tool_content .= "<tr class='list-header'><th>$langOfUser</th></tr>";
            if ($eclass_stud_reg == 2) { // allow student registration via eclass
                $tool_content .= "<tr><td><a href='newuser.php'>$langUserAccountInfo2</a></td></tr>";
            } elseif ($eclass_stud_reg == 1) { // allow student registration via request
                $tool_content .= "<tr><td><a href='formuser.php'>$langUserAccountInfo1</a></td></tr>";
            }

            if (count($auth) > 1 and $alt_auth_stud_reg != FALSE) { // allow user registration via alt auth methods
                if ($alt_auth_stud_reg == 2) { // registration
                    $tool_content .= "<tr><td>$langUserAccountInfo4:";
                } else { // request
                    $tool_content .= "<tr><td>$langUserAccountInfo1:";
                }
                foreach ($auth as $k => $v) {
                    if ($v != 1) {  // bypass the eclass auth method
                        if ($v < 8) {
                            $tool_content .= "<br><a href='altnewuser.php?auth=" . $v . "'>" . q(get_auth_info($v)) . "</a>";
                        } else {
                            if (($eclass_stud_reg == 1) and isset($provider)) {
                                $tool_content .= "<br><a href='formuser.php?auth=" . $v . "'>" . q(get_auth_info($v)) . "</a>";
                            } else if (isset($provider)) { //hybridauth registration
                                $tool_content .= "<br><a href='newuser.php?auth=" . $v . "'>" . q(get_auth_info($v)) . "</a>";
                            }
                        }
                    }
                }
                $tool_content .= "</td></tr>";
            }
            $tool_content .= "</table>";
        } else {
            $tool_content .= "<div class='alert alert-info'>$langStudentCannotRegister</div>";
        }

        // teacher registration
        if ($eclass_prof_reg or $alt_auth_prof_reg) { // allow teacher registration
            $tool_content .= "<table class='table-default'>";
            $tool_content .= "<tr class='list-header'><th>$langUserWithRights</th></tr>";
            if (count($auth) > 1 and $alt_auth_prof_reg) {
                $tool_content .= "<td>$langUserAccountInfo1 $langwith:";
                foreach ($auth as $k => $v) {
                    if ($v != 1) {  // bypass the eclass auth method
                        if ($v < 8) {
                            if ($alt_auth_prof_reg) {
                                $tool_content .= "<br><a href='altnewuser.php?auth=" . $v . "&p=1'>" . q(get_auth_info($v)) . "</a>";
                            } else {
                                $tool_content .= "<br><a href='altnewuser.php?auth=" . $v . "'>" . q(get_auth_info($v)) . "</a>";
                            }
                        } else if ($alt_auth_prof_reg and isset($provider)) {
                            $tool_content .= "<br /><a href='formuser.php?auth=" . $v . "&p=1'>" . q(get_auth_info($v)) . "</a>";
                        }
                    }
                }
                $tool_content .= "</td>";
            }
            if ($eclass_prof_reg) {
                $tool_content .= "<tr><td><a href='formuser.php?p=1'>$langUserAccountInfo1</a></td></tr>";
            }
            $tool_content .= "</table>";
        } else {
            $tool_content .= "<div class='alert alert-info'>$langTeacherCannotRegister</div>";
        }
    }
} else { // disable registration
    $tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
    $tool_content .= "<div class='alert alert-info'>$langCannotRegister</div>";
}

draw($tool_content, 0);

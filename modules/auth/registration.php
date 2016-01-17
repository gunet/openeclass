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

$toolName = $langNewUser;
$auth = get_auth_active_methods();

$provider = '';
$provider_user_data = '';

if ($user_registration) {
    $tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);

    //HybridAuth checks, authentication and user profile info.
    $user_data = '';
    if(!empty($_GET['provider'])) {
        //check if there are any available alternative providers for authentication
        require_once 'modules/auth/methods/hybridauth/config.php';
        require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
        $config = get_hybridauth_config();

        $hybridauth = new Hybrid_Auth( $config );
        $allProviders = $hybridauth->getProviders();
        $tool_content_providers = "";

        if(count($allProviders) && array_key_exists($_GET['provider'], $allProviders)) $provider = '?provider=' . $_GET['provider'];

        if(!empty($provider)) { //if(!empty($provider), it means the provider is existent and valid - it's checked above
            try {
                // create an instance for Hybridauth with the configuration file path as parameter
                $hybridauth = new Hybrid_Auth($config);

                // try to authenticate the selected $provider
                $adapter = $hybridauth->authenticate( @ trim( strip_tags($_GET["provider"])) );

                // grab the user profile
                $user_data = $adapter->getUserProfile();

                //user profile data
                if($user_data->firstName) $provider_user_data .= '&givenname_form=' . q($user_data->firstName);
                if($user_data->lastName) $provider_user_data .= '&surname_form=' . q($user_data->lastName);
                if($user_data->displayName) $provider_user_data .= '&username=' . q(strtolower(preg_replace('/\s+/', '', $user_data->displayName))) . '&uname=' . q(strtolower(preg_replace('/\s+/', '', $user_data->displayName)));
                if($user_data->email) $provider_user_data .= '&usermail=' . q($user_data->email) . '&email=' . q($user_data->email);
                if($user_data->phone) $provider_user_data .= '&userphone=' . q($user_data->phone) . '&phone=' . q($user_data->phone);
                if($user_data->identifier) $provider_user_data .= '&provider_id=' . q($user_data->identifier); //provider user identifier
                //echo $user_data->photoURL;

            } catch(Exception $e) {
                // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to
                // let hybridauth forget all about the user so we can try to authenticate again.

                // Display the recived error,
                // to know more please refer to Exceptions handling section on the userguide
                switch($e->getCode()) {
                    case 0 : $warning = "<p class='alert1'>Unspecified error.</p>"; break;
                    case 1 : $warning = "<p class='alert1'>HybridAuth configuration error.</p>"; break;
                    case 2 : $warning = "<p class='alert1'>Provider not properly configured.</p>"; break;
                    case 3 : $warning = "<p class='alert1'>Unknown or disabled provider.</p>"; break;
                    case 4 : $warning = "<p class='alert1'>Missing provider application credentials.</p>"; break;
                    case 5 : $warning = "<p class='alert1'>Authentication failed. The user has canceled the authentication or the provider refused the connection.</p>"; break;
                    case 6 : $warning = "<p class='alert1'>User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.</p>"; $adapter->logout();
                    break;
                    case 7 : $warning = "<p class='alert1'>User not connected to the provider.</p>"; $adapter->logout(); break;
                }

                // debug messages for hybridauth errors
                //$warning .= "<br /><br /><b>Original error message:</b> " . $e->getMessage();
                //$warning .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>";

                return false;
            }
        } //endif( isset( $_GET["provider"] ) && $_GET["provider"] )
    } //endif(!empty($_GET['provider']))

    $registration_info = get_config('registration_info');
    if ($registration_info) {
        $tool_content .= "<div class='alert alert-info'>$registration_info</div>";
    } else {
        // student registration
        if ($eclass_stud_reg != FALSE or $alt_auth_stud_reg != FALSE) {
            $tool_content .= "<table class='table-default table-responsive'>";
            $tool_content .= "<tr class='list-header'><th>$langOfStudent</th></tr>";
            if ($eclass_stud_reg == 2) { // allow student registration via eclass
                $tool_content .= "<tr><td><a href='newuser.php$provider$provider_user_data'>$langUserAccountInfo2</a></td></tr>";
            } elseif ($eclass_stud_reg == 1) { // allow student registration via request
                $tool_content .= "<tr><td><a href='formuser.php$provider$provider_user_data'>$langUserAccountInfo1</a></td></tr>";
            }

            if (count($auth) > 1 and $alt_auth_stud_reg != FALSE) { // allow user registration via alt auth methods
                if ($alt_auth_stud_reg == 2) { // registration
                    $tool_content .= "<tr><td>$langUserAccountInfo4:";
                } else { // request
                    $tool_content .= "<tr><td>$langUserAccountInfo1:";
                }
                foreach ($auth as $k => $v) {
                    if ($v != 1) {  // bypass the eclass auth method
                        //hybridauth registration is performed in newuser.php of formuser.php rather than altnewuser.php
                        if ($v < 8) {
                            $tool_content .= "<br /><a href='altnewuser.php?auth=" . $v . "'>" . get_auth_info($v) . "</a>";
                        } else {
                            if($eclass_stud_reg == 1) $tool_content .= "<br /><a href='formuser.php?auth=" . $v . "'>" . get_auth_info($v) . "</a>";
                                else $tool_content .= "<br /><a href='newuser.php?auth=" . $v . "'>" . get_auth_info($v) . "</a>";
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
            $tool_content .= "<tr class='list-header'><th>$langOfTeacher</th></tr>";
            if ($eclass_prof_reg) {
                if(empty($provider)) $tool_content .= "<tr><td><a href='formuser.php?p=1'>$langUserAccountInfo1</a></td></tr>";
                    else $tool_content .= "<tr><td><a href='formuser.php$provider$provider_user_data&p=1'>$langUserAccountInfo1</a></td></tr>";
            }
            if (count($auth) > 1 and $alt_auth_prof_reg) {
                $tool_content .= "<td>$langUserAccountInfo1 $langWith:";
                foreach ($auth as $k => $v) {
                    if ($v != 1) {  // bypass the eclass auth method
                        //hybridauth registration is performed in newuser.php rather than altnewuser
                        if ($v < 8) {
                            if ($alt_auth_prof_reg) $tool_content .= "<br /><a href='altnewuser.php?auth=" . $v . "&p=1'>" . get_auth_info($v) . "</a>";
                            else $tool_content .= "<br /><a href='altnewuser.php?auth=" . $v . "'>" . get_auth_info($v) . "</a>";
                        } else {
                            if ($alt_auth_prof_reg) $tool_content .= "<br /><a href='formuser.php?auth=" . $v . "&p=1'>" . get_auth_info($v) . "</a>";
                                else $tool_content .= "<br /><a href='newuser.php?auth=" . $v . "&p=1'>" . get_auth_info($v) . "</a>";
                        }
                    }
                }
                $tool_content .= "</td>";
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

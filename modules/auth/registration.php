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

$data['user_registration'] = get_config('user_registration');
$data['eclass_prof_reg'] = get_config('eclass_prof_reg');
$data['alt_auth_prof_reg'] = get_config('alt_auth_prof_reg');
$data['eclass_stud_reg'] = get_config('eclass_stud_reg'); // student registration via eclass
$data['alt_auth_stud_reg']= get_config('alt_auth_stud_reg'); //user registration via alternative auth methods
$data['registration_info'] = get_config('registration_info');

$toolName = $langRegistration;
$data['auth'] = get_auth_active_methods();

$data['provider'] = $provider = '';
$data['provider_user_data'] = $provider_user_data = '';

$data['action_bar'] = action_bar(
                                [[
                                    'title' => $langBack,
                                    'url' => $urlServer,
                                    'icon' => 'fa-reply',
                                    'level' => 'primary',
                                    'button-class' => 'btn-default'
                                ]], false);

    //HybridAuth checks, authentication and user profile info.
    $user_data = '';
    if(!empty($_GET['provider'])) {
        $data['provider'] = $provider = $_GET['provider'];
    }

$data['menuTypeID'] = 0;
view('modules.auth.registration', $data);

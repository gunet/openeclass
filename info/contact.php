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


$mail_ver_excluded = true;
$force_password_excluded = true;
require_once '../include/baseTheme.php';
$toolName = $contactpoint;

if (get_config('dont_display_contact_menu')) {
    redirect_to_home_page();
}

$data['postaddress'] = nl2br(q(get_config('postaddress')));
$data['phone'] = q(get_config('phone'));
$data['emailhelpdesk'] = $emailhelpdesk = get_config('email_helpdesk');
if(!empty($data['emailhelpdesk'])){
    $data['emailhelpdesk'] = "<a href='mailto:$emailhelpdesk'>".str_replace('@', ' &lt;at> ', $emailhelpdesk)."</a>";
}

$data['action_bar'] = action_bar(
                                    [
                                        [
                                            'title' => $langBack,
                                            'url' => $urlServer,
                                            'icon' => 'fa-reply',
                                            'level' => 'primary',
                                            'button-class' => 'btn-secondary'
                                        ]
                                    ], false);

view('info.contact', $data);

<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */


$mail_ver_excluded = true;
$force_password_excluded = true;
require_once '../include/baseTheme.php';
$toolName = $contactpoint;

if (get_config('dont_display_contact_menu')) {
    redirect_to_home_page();
}

$tenant = getCurrentTenant();

if ($tenant) {
    $tenant_options = unserialize($tenant->options);
    $postaddress = getTenantOption($tenant_options, 'contact_address');
    $phone = getTenantOption($tenant_options, 'contact_phone');
    $emailhelpdesk = getTenantOption($tenant_options, 'contact_email');
} else {
    $postaddress = nl2br(q(get_config('postaddress')));
    $phone = get_config('phone');
    $emailhelpdesk = get_config('email_helpdesk');
}

$data['postaddress'] = $postaddress;
$data['phone'] = $phone;
$data['emailhelpdesk'] = $emailhelpdesk;
if(!empty($data['emailhelpdesk'])){
    $data['emailhelpdesk'] = "<a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>";
}

view('info.contact', $data);

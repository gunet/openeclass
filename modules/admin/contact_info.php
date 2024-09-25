<?php

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langUpgContact;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_POST['submit'])) {
    set_config('phone', $_POST['formtelephone']);
    set_config('email_helpdesk', $_POST['formemailhelpdesk']);
    set_config('postaddress', $_POST['formpostaddress']);

    $contact_info = 0;
    if (isset($_POST['enable_form_contact']) and $_POST['enable_form_contact'] == 'on') {
        $contact_info = 1;
    }
    set_config('contact_form_activation', $contact_info);

    Session::flash('message', $langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/index.php');
}

view('admin.other.contact_info');

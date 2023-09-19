<?php

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langUpgContact;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_POST['submit'])) {
    set_config('phone', $_POST['formtelephone']);
    set_config('email_helpdesk', $_POST['formemailhelpdesk']);
    set_config('postaddress', $_POST['formpostaddress']);

    Session::flash('message', $langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/index.php');
}
$data['action_bar'] = action_bar([
    [
        'title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary'
    ]
]);

$data['menuTypeID'] = 3;

view('admin.other.contact_info', $data);

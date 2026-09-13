<?php

$require_admin = true;

require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/cobyapp.php';

$app = ExtAppManager::getApp('coby');
$toolName = $langConfig . ' ' . $app->getDisplayName();
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('slimselect');

if (isset($_POST['submit'])) {
    //print_a($_POST);die;
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $result = $app->storeParams();
    if (isset($_POST['enabled'])) {
        $users_list = Database::get()->queryArray("SELECT id, username, givenname, surname FROM user WHERE status = " . USER_TEACHER . " ORDER BY surname");
        foreach ($users_list as $c) {
            delete_user_option($c->id, 'coby_enable');
        }
        if ($_POST['coby_users'][0] > 0) { // copy is enabled for specific users
            foreach ($_POST['coby_users'] as $user_id) {
                set_user_option($user_id, 'coby_enable', 1);
            }
            set_config('ext_coby_enabled_all_users', 0);
        } else {
            set_config('ext_coby_enabled_all_users', 1);
        }
    }
    Session::flash('message', $langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/extapp.php');
} else {
    $users_content = '';
    $selections = array();
    $select_all = 'selected';
    $users_list = Database::get()->queryArray("SELECT id, username, givenname, surname FROM user WHERE status = " . USER_TEACHER . " ORDER BY surname");
    foreach ($users_list as $c) {
        $selected = '';
        if (get_user_option($c->id, 'coby_enable')) {
            $selected = "selected";
            $select_all = '';
        }
        $users_content .= "<option value='$c->id' $selected>" . q("$c->surname $c->givenname") . "(" . q($c->username) . ")</option>";
    }
    $all_users_content = "<option value='0' $select_all><h2>$langToAllUsers</h2></option>";
    $data['users_content'] = $all_users_content . $users_content;
}

$data['appParams'] = $app->getParams();

view('admin.other.extapps.cobyconf', $data);
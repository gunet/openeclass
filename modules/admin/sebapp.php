<?php

$require_admin = true;

require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/sebapp.php';

$app = ExtAppManager::getApp('seb');
$toolName = $langConfig . ' ' . $app->getDisplayName();
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('slimselect');

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $result = $app->storeParams();
    if (isset($_POST['enabled'])) {
        Database::get()->query("DELETE FROM seb_courses"); // seb is enabled to all courses
        if ($_POST['seb_courses'][0] > 0) { // seb is enabled to specific courses
            foreach ($_POST['seb_courses'] as $course_id) {
                Database::get()->query("INSERT INTO seb_courses (course_id) VALUES (?d)", $course_id);
            }
        }
    }
    Session::flash('message', $langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/extapp.php');
} else {
    $selections = array();
    $select_all = 'selected';
    $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE visible != " . COURSE_INACTIVE . " ORDER BY title");
    $seb_courses_list = Database::get()->queryArray("SELECT course_id FROM seb_courses");
    if (count($seb_courses_list) > 0) {
        $select_all = '';
        foreach ($seb_courses_list as $c) {
            $selections[] = $c->course_id;
        }
    }
    $data['seb_courses'] = $selections;
    $courses_content = "<option value='0' $select_all><h2>$langToAllCourses</h2></option>";
    foreach ($courses_list as $c) {
        $selected = '';
        if (in_array($c->id, $selections)) {
            $selected = "selected";
        }
        $courses_content .= "<option value='$c->id' $selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
    }
    $data['courses_content'] = $courses_content;
}
$data['appParams'] = $app->getParams();

view('admin.other.extapps.sebconf', $data);
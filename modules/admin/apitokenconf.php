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

$require_admin = true;
$require_help = true;
$helpTopic = 'external_tools';
$helpSubTopic = 'api_token';

require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/apitokenapp.php';

load_js('bootstrap-datetimepicker');
load_js('select2');

$head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('#token_expires_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                minuteStep: 10,
                autoclose: true
            });
        });
    </script>";

$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
$app = ExtAppManager::getApp('apitoken');
$duration_time = 365*24*60*60; // one year (in seconds)

$toolName = $langCreateAPIToken;
$action_bar = action_bar(array(
    array('title' => $langAdd,
        'url' => 'apitokenconf.php?add',
        'icon' => 'fa-plus',
        'level' => 'primary-label',
        'button-class' => 'btn-success')
    ));

$tool_content .= $action_bar;
$tool_content .= "<div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" . $app->getLongDescription() . "</span></div>";

if (isset($_GET['delete'])) {
    Database::get()->query("DELETE FROM api_token WHERE id = ?d", $_GET['delete']);
    Session::Messages($langApiTokenDeleted, 'alert-success');
    redirect_to_home_page($app->getConfigUrl());
}


if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_POST['token_expires_at'])) {
        if (empty($_POST['token_expires_at']) || "" == trim($_POST['token_expires_at']) ) {
            $token_expires_at = $duration_time;
        }
        $expires_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['token_expires_at']);
        $token_expires_at = $expires_at->format("Y-m-d H:i");
    } else {
        $token_expires_at = $duration_time;
    }
    if (isset($_POST['enabled'])) {
        $enabled = 1;
    } else {
        $enabled = 0;
    }
    $all_courses = (isset($_POST['api_all_courses']) && $_POST['api_all_courses'] == 'true')? 1: 0;
    $token = null;
    if (isset($_GET['edit'])) {
        if ($_POST['submit'] == 'create_token') { // generate api token
            $token = "eclass_".bin2hex(random_bytes(32));
            Database::get()->query("UPDATE api_token
                SET token = ?s, updated = " . DBHelper::timeAfter() . ", expired = ?s
                WHERE id = ?d",
                $token, $token_expires_at, $_GET['edit']);
        }
        $result_update = Database::get()->query("UPDATE api_token SET
                            name = ?s,
                            comments = ?s,
                            ip = ?s,
                            enabled = ?d,
                            expired = ?s,
                            all_courses = ?d
                        WHERE id = ?d", $_POST['name'], $_POST['comments'], $_POST['remote_url'], $enabled, $token_expires_at, $all_courses, $_GET['edit']);
        $token_id = $_GET['edit'];
    } else {
        $token = "eclass_".bin2hex(random_bytes(32));
        $result_insert = Database::get()->query("INSERT INTO api_token SET
                                token = ?s,
                                name = ?s,
                                comments = ?s,
                                ip = ?s,
                                enabled = 1,
                                created = " . DBHelper::timeAfter() . ",
                                expired = ?s",
            $token, $_POST['name'], $_POST['comments'], $_POST['remote_url'], $token_expires_at);
        $token_id = $result_insert->lastInsertID;
    }
    Database::get()->query('DELETE FROM api_token_course WHERE token_id = ?d', $token_id);
    if (!$all_courses) {
        $course_update_sql = implode(',', array_fill(0, count($_POST['api_courses']), '(?d, ?d)'));
        $course_update_values = array_map(function ($course_id) use ($token_id) {
            return [$course_id, $token_id];
        }, $_POST['api_courses']);
        Database::get()->query('INSERT INTO api_token_course (course_id, token_id) VALUES ' . $course_update_sql,
            $course_update_values);
    }

    if ($token) {
        Session::Messages("<div>$langAPITokenCreated <div class='mt-2'><strong>$token</strong></div></div>", 'alert-success');
    } else {
        Session::Messages($langFaqEditSuccess, 'alert-success');
    }
    redirect_to_home_page($app->getConfigUrl());
}

$q = Database::get()->queryArray("SELECT id, token, name, comments, ip, expired, enabled FROM api_token");

if (count($q) > 0) {
    $tool_content .= "<div class='table-responsive mt-4 mb-4'>";
    $tool_content .= "<table class='table-default'>
                <thead>
                    <tr class='list-header'>
                        <th>$langExtAppName</th>
                        <th>Remote IP</th>
                        <th class='text-end' aria-label='$langSettingSelect'>" . icon('fa-gears') . "</th>
                    </tr>
                </thead>";

    foreach ($q as $data) {
        if ($data->enabled == 1) {
            $expired_message = '';
            $icon = "<span class='fa fa-check' title='$langCEnabled'></span>";
            $class = '';
        } else if ($data->expired < date('Y-m-d H:i:s')) {
            $expired_message = "<span class='text-danger'>($langHasExpiredS)</span>";
            $icon = "<span class='fa-solid fa-xmark' title='$langTypeInactive'></span>";
            $class = 'not_visible';
        } else {
            $expired_message = '';
            $icon = "<span class='fa-solid fa-xmark' title='$langTypeInactive'></span>";
            $class = 'not_visible';
        }
        $tool_content .= "<tr class='$class'>";
        $tool_content .= "<td><a href='$_SERVER[SCRIPT_NAME]?edit=$data->id'>$data->name</a> $icon $expired_message<div class='help-block'>$data->comments</div></td>";
        $tool_content .= "<td>$data->ip</td>";
        $tool_content .= "<td class='option-btn-cell text-end'>" .
            action_button(array(
                array('title' => $langEditChange,
                    'url' => "$_SERVER[SCRIPT_NAME]?edit=$data->id",
                    'icon' => 'fa-edit'),
                array('title' => $langDelete,
                    'url' => "$_SERVER[SCRIPT_NAME]?delete=$data->id",
                    'icon' => 'fa-xmark',
                    'class' => 'delete',
                    'confirm' => $langConfirmDelete))) . "</td>";
        $tool_content .= "</tr>";
    }
    $tool_content .= "</table></div>";
} else {
    $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoApiToken</span></div>";
}

if (isset($_GET['edit'])) {
    $data = Database::get()->querySingle('SELECT * FROM api_token WHERE id = ?d', $_GET['edit']);
    $courses_list = Database::get()->queryArray("SELECT course.id AS id, code, title, token_id
        FROM course LEFT JOIN api_token_course
            ON course.id = api_token_course.course_id AND token_id = ?d
        WHERE visible <> " . COURSE_INACTIVE . " ORDER BY title", $data->id);
    $listcourses = implode("\n", array_map(function ($course) {
        $selected = $course->token_id? 'selected': '';
          return "<option value='{$course->id}' $selected>" . q("{$course->title} ({$course->code})") . "</option>";
    }, $courses_list));
    $exp_date = DateTime::createFromFormat('Y-m-d H:i:s', $data->expired);
    $enable_checked = $data->enabled == 1 ? 'checked' : '';
    if ($data->all_courses) {
        $all_courses_checked = 'checked';
        $some_courses_checked = '';
        $course_select_display = 'd-none';
    } else {
        $all_courses_checked = '';
        $some_courses_checked = 'checked';
        $course_select_display = '';
    }
    $tool_content .= "
        <div class='row extapp'><div class='col-lg-6 col-12'>
            <div class='form-wrapper form-edit border-0 px-0'>
                <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?edit=$_GET[edit]' method='post'>
                    <div class='form-group'>
                        <label for='$langExtAppName' class='col-12 control-label-notes'>$langExtAppName</label>
                        <div class='col-12'>
                            <input id='$langExtAppName' class='form-control' type='text' name='name' value='" . q($data->name) . "'>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='$langIpAddress' class='col-12 control-label-notes'>$langIpAddress</label>
                        <div class='col-12'>
                            <input id='$langIpAddress' class='form-control' type='text' name='remote_url' value='" . q($data->ip) . "'>
                            <div class='form-text'>$langAPITokenIP</div>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='$langComments' class='col-12 control-label-notes'>$langComments</label>
                        <div class='col-12'>
                            <textarea id='$langComments' class='form-control' rows='3' cols='40' name='comments'>" . q($data->comments) . "</textarea>
                        </div>
                    </div>
                    <div class='input-append date form-group mt-4'>
                        <label for='token_expires_at' class='col-12 control-label-notes'>$langExpirationDate:</label>
                        <div class='col-12'>
                            <div class='input-group'>
                                <input class='form-control mt-0 border-end-0' id='token_expires_at' name='token_expires_at' type='text' value='" . $exp_date->format("d-m-Y H:i") . "'>
                                <span class='input-group-text input-group-addon h-40px bg-input-default border-start-0 input-border-color'><i class='fa-regular fa-calendar'></i></span>
                            </div>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <div class='col-sm-12 control-label-notes'>$langAPITokenAccess</div>
                            <div class='col-sm-12 d-inline-flex'>
                                <div class='radio'>
                                    <label>
                                        <input type='radio' id='api_all_courses' name='api_all_courses' value='true' $all_courses_checked>
                                        $langToAllCourses
                                    </label>
                                </div>
                                <div class='radio ms-4'>
                                    <label>
                                        <input type='radio' id='api_some_courses' name='api_all_courses' value='false' $some_courses_checked>
                                        $langToSomeCourses
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id='courses-select-field' class='form-group $course_select_display'>
                    <div class='col-sm-12'>
                        <select class='form-select' name='api_courses[]' multiple class='form-control' id='select-courses'>
                            $listcourses
                        </select>
                        <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                    </div>
                </div>
                    <div class='form-group mt-4'>
                        <div class='col-sm-offset-2 col-sm-10'>
                            <div class='checkbox'>
                                <label class='label-container' aria-label='$langSettingSelect'>
                                <input type='checkbox' name='enabled' value='1' $enable_checked><span class='checkmark'></span>$langCEnabled</label>
                           </div>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <div class='col-12 d-flex justify-content-end'>
                            <button class='btn submitAdminBtn' type='submit' name='submit'>$langSubmit</button>
                            <button class='btn submitAdminBtn ms-2' type='submit' name='submit' value='create_token'>$langCreateAPIToken</button>
                            <a href='$_SERVER[SCRIPT_NAME]' class='btn cancelAdminBtn ms-2'>$langCancel</a>
                        </div>
                    </div>" .
                    generate_csrf_token_form_field() . "
                </form>
            </div>
        </div>

        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>

        </div>";
} elseif (isset($_GET['add'])) {
        $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course
            WHERE visible <> " . COURSE_INACTIVE . " ORDER BY title");
        $listcourses = implode("\n", array_map(function ($course) {
              return "<option value='{$course->id}'>" . q("{$course->title} ({$course->code})") . "</option>";
        }, $courses_list));
        $expirationDate = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now") + $duration_time));
        $tool_content .= "
        <div class='row extapp'><div class='col-lg-6 col-12'>
          <div class='form-wrapper form-edit border-0 px-0'>
            <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
                <div class='form-group'>
                    <label for='$langExtAppName' class='col-12 control-label-notes'>$langExtAppName</label>
                    <div class='col-12'>
                        <input id='$langExtAppName' class='form-control' type='text' name='name'>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='$langIpAddress' class='col-12 control-label-notes'>$langIpAddress</label>
                    <div class='col-12'>
                        <input id='$langIpAddress' class='form-control' type='text' name='remote_url'>
                        <div class='form-text'>$langAPITokenIP</div>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='$langComments' class='col-12 control-label-notes'>$langComments</label>
                    <div class='col-12'>
                        <textarea id='$langComments' class='form-control' rows='3' cols='40' name='comments'></textarea>
                    </div>
                </div>
                <div class='input-append date form-group mt-4'>
                    <div class='col-12 control-label-notes'>$langExpirationDate:</div>
                    <div class='col-12'>
                        <div class='input-group'>
                            <input class='form-control mt-0 border-end-0' id='token_expires_at' name='token_expires_at' type='text' value='" . $expirationDate->format("d-m-Y H:i") . "'>
                            <span class='input-group-text input-group-addon h-40px bg-input-default input-border-color'><i class='fa-regular fa-calendar'></i></span>
                        </div>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <div class='col-sm-12 control-label-notes'>$langAPITokenAccess</div>
                        <div class='col-sm-12 d-inline-flex'>
                            <div class='radio'>
                                <label>
                                    <input type='radio' id='api_all_courses' name='api_all_courses' value='true' checked>
                                    $langToAllCourses
                                </label>
                            </div>
                            <div class='radio ms-4'>
                                <label>
                                    <input type='radio' id='api_some_courses' name='api_all_courses' value='false'>
                                    $langToSomeCourses
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div id='courses-select-field' class='form-group d-none'>
                    <div class='col-sm-12'>
                        <select class='form-select' name='api_courses[]' multiple class='form-control' id='select-courses'>
                            $listcourses
                        </select>
                        <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <div class='col-sm-offset-2 col-sm-10'>
                        <div class='checkbox'>
                            <label class='label-container' aria-label='$langSettingSelect'>
                            <input type='checkbox' name='enabled' value='1' checked><span class='checkmark'></span>$langCEnabled</label>
                       </div>
                    </div>
                </div>
                <div class='form-group mt-4'>
                  <div class='col-12 d-flex justify-content-end'>
                    <button class='btn submitAdminBtn' type='submit' name='submit'>$langSubmit</button>
                    &nbsp;
                    <a href='$_SERVER[SCRIPT_NAME]' class='btn cancelAdminBtn ms-2'>$langCancel</a>
                  </div>
                </div>" .
                generate_csrf_token_form_field() . "
            </form>
          </div>
        </div>

        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>

        </div>";
}

$head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('input[name=api_all_courses]').change(function () {
                if ($('#api_some_courses').is(':checked')) {
                    $('#courses-select-field').removeClass('d-none').hide().slideDown(400);
                } else {
                    $('#courses-select-field').slideUp(400);
                }
            });
            $('#select-courses').select2();
            $('#selectAll').click(function(e) {
                e.preventDefault();
                var stringVal = [];
                $('#select-courses').find('option').each(function(){
                    stringVal.push($(this).val());
                });
                $('#select-courses').val(stringVal).trigger('change');
            });
            $('#removeAll').click(function(e) {
                e.preventDefault();
                var stringVal = [];
                $('#select-courses').val(stringVal).trigger('change');
            });
            $('#allCourses').click(function(e) {
                var sc = $('#select-courses');
                e.preventDefault();
                $('#select-courses').val(['0']).trigger('change');
            });
        });
    </script>";

draw($tool_content, 3, null, $head_content);

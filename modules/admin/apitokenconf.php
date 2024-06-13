<?php

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/apitokenapp.php';

load_js('bootstrap-datetimepicker');

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
$tool_content .= action_bar(array(
    array('title' => $langAdd,
        'url' => 'apitokenconf.php?add',
        'icon' => 'fa-plus',
        'level' => 'primary-label',
        'button-class' => 'btn-success')
    ));

$tool_content .= "<div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" . $app->getLongDescription() . "</span></div>";

if (isset($_GET['delete'])) {
    Database::get()->query("DELETE FROM api_token WHERE id = ?d", $_GET['delete']);
    Session::Messages($langApiTokenDeleted, 'alert-success');
    redirect_to_home_page($app->getConfigUrl());
}


if (isset($_POST['submit'])) {
    if (isset($_POST['token_expires_at'])) {
        if (empty($_POST['token_expires_at']) || "" == trim($_POST['token_expires_at']) ) {
            $token_expires_at = $duration_time;
        }
        $expires_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['token_expires_at']);
        $token_expires_at = $expires_at->format("Y-m-d H:i");
    } else {
        $token_expires_at = $duration_time;
    }

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_GET['edit'])) {
        if ($_POST['submit'] == 'create_token') { // generate api token
            $token = "eclass_".bin2hex(random_bytes(32));
            $result_update_new = Database::get()->query("UPDATE api_token SET token = ?s,
                                                        updated = " . DBHelper::timeAfter() . ", 
                                                        expired = ?s 
                                                    WHERE id = ?d",
                $token, $token_expires_at, $_GET['edit']);
        } else {
            if (isset($_POST['enabled'])) {
                $enabled = 1;
            } else {
                $enabled = 0;
            }
            $result_update = Database::get()->query("UPDATE api_token SET 
                                name = ?s, 
                                comments = ?s, 
                                ip = ?s,
                                enabled = ?d,
                                expired = ?s
                            WHERE id = ?d", $_POST['name'], $_POST['comments'], $_POST['remote_url'], $enabled, $token_expires_at, $_GET['edit']);
        }
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
    }
    if (isset($result_insert) or isset($result_update_new)) {
        Session::Messages("$langAPITokenCreated <div style='margin-top: 15px;'><strong>$token</strong></div>", 'alert-success');
    } else if (isset($result_update)) {
        Session::Messages("$langFaqEditSuccess", 'alert-success');
    } else {
        Session::Messages($result, 'alert-danger');
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
                        <th class='text-end'>" . icon('fa-gears') . "</th>
                    </tr>
                </thead>";

    foreach ($q as $data) {
        if ($data->enabled == 1) {
            $expired_message = '';
            $icon = "<span class='fa fa-check' title='$langCΕnabled'></span>";
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

if (isset($_GET['add']) or isset($_GET['edit'])) {
    if (isset($_GET['edit'])) {
        $data = Database::get()->querySingle("SELECT * FROM api_token WHERE id = ?d", $_GET['edit']);
        $exp_date = DateTime::createFromFormat("Y-m-d H:i:s", $data->expired);
        $tool_content .= "
        <div class='row extapp'><div class='col-lg-6 col-12'>
          <div class='form-wrapper form-edit border-0 px-0'>
            <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?edit=$_GET[edit]' method='post'>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='$langExtAppName' class='col-12 control-label-notes'>$langExtAppName</label>";
        $tool_content .= "<div class='col-12'><input class='form-control' type='text' name='name' value='$data->name'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='$langIpAddress' class='col-12 control-label-notes'>$langIpAddress</label>";
        $tool_content .= "<div class='col-12'><input class='form-control' type='text' name='remote_url' value='$data->ip'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='$langComments' class='col-12 control-label-notes'>$langComments</label>";
        $tool_content .= "<div class='col-12'><textarea class='form-control' rows='3' cols='40' name='comments'>$data->comments</textarea></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='input-append date form-group mt-4'>
                                    <label class='col-12 control-label-notes'>$langExpirationDate:</label>
                                    <div class='col-12'>
                                        <div class='input-group'>
                                            <input class='form-control mt-0 border-end-0' id='token_expires_at' name='token_expires_at' type='text' value='" . $exp_date->format("d-m-Y H:i") . "'>
                                            <span class='input-group-text input-group-addon h-40px bg-input-default border-start-0 input-border-color'><i class='fa-regular fa-calendar'></i></span>
                                        </div>
                                    </div>
                                </div>";
        $checked = $data->enabled == 1 ? "checked" : "";
        $tool_content .= "<div class='form-group mt-4'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
        $tool_content .= "<label class='label-container'><input type='checkbox' name='enabled' value='1' $checked><span class='checkmark'></span>$langCΕnabled</label>";
        $tool_content .= "</div></div></div>";
        $tool_content .= "
                    <div class='form-group mt-4'>
                      <div class='col-12 d-flex justify-content-end'>
                        <button class='btn submitAdminBtn' type='submit' name='submit'>$langSubmit</button>";
        $tool_content .= "<button class='btn submitAdminBtn ms-2' type='submit' name='submit' value='create_token'>$langCreateAPIToken</button>";
        $tool_content .= "
                    <a href='$_SERVER[SCRIPT_NAME]' class='btn cancelAdminBtn ms-2'>$langCancel</a>
                  </div>
                </div>" .
            generate_csrf_token_form_field() . "
            </form>
            </div>
        </div>
        
        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
        
        </div>";
    } else {
        $expirationDate = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now") + $duration_time));
        $boolean_field = "";
        $tool_content .= "
        <div class='row extapp'><div class='col-lg-6 col-12'>
          <div class='form-wrapper form-edit border-0 px-0'>
            <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>";

        foreach ($app->getParams() as $param) {
            if ($param->getType() == ExtParam::TYPE_BOOLEAN) {
                $boolean_field .= "<div class='form-group mt-4'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
                $boolean_field .= "<label class='label-container'><input type='checkbox' name='" . $param->name() . "' value='1' checked><span class='checkmark'></span>" . $param->display() . "</label>";
                $boolean_field .= "</div></div></div>";
            } elseif ($param->getType() == ExtParam::TYPE_STRING) {
                $tool_content .= "<div class='form-group mt-4'>";
                $tool_content .= "<label for='" . $param->name() . "' class='col-12 control-label-notes'>" . $param->display() . "</label>";
                $tool_content .= "<div class='col-12'><input class='form-control' type='text' name='" . $param->name() . "' value='" . q($param->value()) . "'></div>";
                $tool_content .= "</div>";
            } elseif ($param->getType() == ExtParam::TYPE_MULTILINE) {
                $tool_content .= "<div class='form-group mt-4'>";
                $tool_content .= "<label for='" . $param->name() . "' class='col-12 control-label-notes'>" . $param->display() . "</label>";
                $tool_content .= "<div class='col-12'><textarea class='form-control' rows='3' cols='40' name='" . $param->name() . "'>" .
                    q($param->value()) . "</textarea></div>";
                $tool_content .= "</div>";
            }
        }

        $tool_content .= "<div class='input-append date form-group mt-4'>
                    <label class='col-12 control-label-notes'>$langExpirationDate:</label>
                    <div class='col-12'>
                        <div class='input-group'>
                            <input class='form-control mt-0 border-end-0' id='token_expires_at' name='token_expires_at' type='text' value='" . $expirationDate->format("d-m-Y H:i") . "'>
                            <span class='input-group-text input-group-addon h-40px bg-input-default input-border-color'><i class='fa-regular fa-calendar'></i></span>
                        </div>
                    </div>
                </div>";

        $tool_content .= $boolean_field;

        $tool_content .= "
                <div class='form-group mt-4'>
                  <div class='col-12 d-flex justify-content-end'>
                    <button class='btn submitAdminBtn' type='submit' name='submit'>$langSubmit</button>";
        $tool_content .= "&nbsp;
                    <a href='$_SERVER[SCRIPT_NAME]' class='btn cancelAdminBtn ms-2'>$langCancel</a>
                  </div>
                </div>" .
            generate_csrf_token_form_field() . "
            </form>
            </div>
        </div>
        
        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
        
        </div>";
    }
}

draw($tool_content, 3, null, $head_content);

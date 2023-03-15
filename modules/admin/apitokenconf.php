<?php

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/apitokenapp.php';

$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
$app = ExtAppManager::getApp('apitoken');

$toolName = $app->getShortDescription();
$tool_content .= action_bar(array(
        array('title' => $langAdd,
          'url' => 'apitokenconf.php?add',
          'icon' => 'fa-plus',
          'level' => 'primary-label',
          'button-class' => 'btn-success'),
        array('title' => $langBack,
            'url' => 'apitokenconf.php',
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

if (isset($_GET['delete'])) {
    Database::get()->query("DELETE FROM api_token WHERE id = ?d", $_GET['delete']);
    Session::Messages($langApiTokenDeleted, 'alert-success');
    redirect_to_home_page($app->getConfigUrl());
}


if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_GET['edit'])) {
        $duration_time = 365*24*60*60; // one year (in seconds)
        if ($_POST['submit'] == 'create_token') { // generate api token
            $token = "eclass_".bin2hex(random_bytes(32));
            $result_update_new = Database::get()->query("UPDATE api_token SET token = ?s,
                                                        updated = " . DBHelper::timeAfter() . ", 
                                                        expired = " . DBHelper::timeAfter($duration_time) . " 
                                                    WHERE id = ?d",
                                            $token, $_GET['edit']);
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
                                enabled = ?d                                 
                            WHERE id = ?d", $_POST['name'], $_POST['comments'], $_POST['remote_url'], $enabled, $_GET['edit']);
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
                                expired = " . DBHelper::timeAfter(31536000) . "",
                            $token, $_POST['name'], $_POST['comments'], $_POST['remote_url']);
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
    $tool_content .= "<div class='table-responsive'>";
    $tool_content .= "<table class='table-default'>
                <thead>
                    <tr>
                        <th class='text-left'>$langExtAppName</th>
                        <th class='text-center'>Remote IP</th>
                        <th class='text-center'>" . icon('fa-gears') . "</th>
                    </tr>
                </thead>";

    foreach ($q as $data) {
        $class = '';
        if ($data->enabled == 0) {
            $class = 'not_visible';
        }
        if ($data->expired < date('Y-m-d H:i:s')) {
            $expired_message = "<span class='text-danger'>($langHasExpiredS)</span>";
        } else {
            $expired_message = '';
        }
        $tool_content .= "<tr class='$class'>";
        $tool_content .= "<td>$data->name $expired_message<div class='help-block'>$data->comments</div></td>";
        $tool_content .= "<td>$data->ip</td>";
        $tool_content .= "<td class='option-btn-cell'>" .
                action_button(array(
                    array('title' => $langEditChange,
                        'url' => "$_SERVER[SCRIPT_NAME]?edit=$data->id",
                        'icon' => 'fa-edit'),
                    array('title' => $langDelete,
                        'url' => "$_SERVER[SCRIPT_NAME]?delete=$data->id",
                        'icon' => 'fa-times',
                        'class' => 'delete',
                        'confirm' => $langConfirmDelete))) . "</td>";
        $tool_content .= "</tr>";
    }
    $tool_content .= "</table></div>";
} else {
    $tool_content .= "<div class='alert alert-warning text-center'>$langNoApiToken</div>";
}


if (isset($_GET['add']) or isset($_GET['edit'])) {
    if (isset($_GET['edit'])) {
        $data = Database::get()->querySingle("SELECT * FROM api_token WHERE id = ?d", $_GET['edit']);
        $tool_content .= "
        <div class='row extapp'><div class='col-xs-12'>
          <div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?edit=$_GET[edit]' method='post'>";
                $tool_content .= "<div class='form-group'>";
                $tool_content .= "<label for='$langExtAppName' class='col-sm-2 control-label'>$langExtAppName</label>";
                $tool_content .= "<div class='col-sm-10'><input class='form-control' type='text' name='name' value='$data->name'></div>";
                $tool_content .= "</div>";
                $tool_content .= "<div class='form-group'>";
                $tool_content .= "<label for='$langIpAddress' class='col-sm-2 control-label'>$langIpAddress</label>";
                $tool_content .= "<div class='col-sm-10'><input class='form-control' type='text' name='remote_url' value='$data->ip'></div>";
                $tool_content .= "</div>";
                $tool_content .= "<div class='form-group'>";
                $tool_content .= "<label for='$langComments' class='col-sm-2 control-label'>$langComments</label>";
                $tool_content .= "<div class='col-sm-10'><textarea class='form-control' rows='3' cols='40' name='comments'>$data->comments</textarea></div>";
                $tool_content .= "</div>";
                $checked = $data->enabled == 1 ? "checked" : "";
                $tool_content .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
                $tool_content .= "<label><input type='checkbox' name='enabled' value='1' $checked>$langCΕnabled</label>";
                $tool_content .= "</div></div></div>";
                $tool_content .= "
                    <div class='form-group'>
                      <div class='col-sm-offset-2 col-sm-10'>
                        <button class='btn btn-primary' type='submit' name='submit'>$langSubmit</button>";
                $tool_content .= "&nbsp;<button class='btn btn-success' type='submit' name='submit' value='create_token'>$langCreateAPIToken</button>";
                $tool_content .= "&nbsp;
                    <a href='$_SERVER[SCRIPT_NAME]' class='btn btn-default'>$langCancel</a>
                  </div>
                </div>" .
                generate_csrf_token_form_field() . "
            </form>
            </div>
        </div></div>";
    } else {
        $boolean_field = "";
        $tool_content .= "
        <div class='row extapp'><div class='col-xs-12'>
          <div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>";

            foreach ($app->getParams() as $param) {
                if ($param->getType() == ExtParam::TYPE_BOOLEAN) {
                    $boolean_field .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
                    $boolean_field .= "<label><input type='checkbox' name='" . $param->name() . "' value='1' checked>" . $param->display() . "</label>";
                    $boolean_field .= "</div></div></div>";
                } elseif ($param->getType() == ExtParam::TYPE_STRING) {
                    $tool_content .= "<div class='form-group'>";
                    $tool_content .= "<label for='" . $param->name() . "' class='col-sm-2 control-label'>" . $param->display() . "</label>";
                    $tool_content .= "<div class='col-sm-10'><input class='form-control' type='text' name='" . $param->name() . "' value='" . q($param->value()) . "'></div>";
                    $tool_content .= "</div>";
                } elseif ($param->getType() == ExtParam::TYPE_MULTILINE) {
                    $tool_content .= "<div class='form-group'>";
                    $tool_content .= "<label for='" . $param->name() . "' class='col-sm-2 control-label'>" . $param->display() . "</label>";
                    $tool_content .= "<div class='col-sm-10'><textarea class='form-control' rows='3' cols='40' name='" . $param->name() . "'>" .
                        q($param->value()) . "</textarea></div>";
                    $tool_content .= "</div>";
                }
            }

            $tool_content .= $boolean_field;
            $tool_content .= "
                <div class='form-group'>
                  <div class='col-sm-offset-2 col-sm-10'>
                    <button class='btn btn-primary' type='submit' name='submit'>$langSubmit</button>";
            $tool_content .= "&nbsp;
                    <a href='$_SERVER[SCRIPT_NAME]' class='btn btn-default'>$langCancel</a>
                  </div>
                </div>" .
                generate_csrf_token_form_field() . "
            </form>
            </div>
        </div></div>";
    }
}

draw($tool_content, 3, null);

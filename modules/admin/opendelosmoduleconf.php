<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/opendelosapp.php';
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$available_themes = active_subdirs("$webDir/template", 'theme.html');

// code from extapp.php

$app = ExtAppManager::getApp('opendelos');

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if ($_POST['submit'] == 'clear') {
        foreach ($app->getParams() as $param) {
            $param->setValue('');
            $param->persistValue();
        }
        Session::Messages($langFileUpdatedSuccess, 'alert-info');
    } else {
        $result = $app->storeParams();
        if ($result) {
            Session::Messages($result, 'alert-danger');
        } else {
            Session::Messages($langFileUpdatedSuccess, 'alert-success');
        }
    }

    // http vs https check
    $server_https = isset($_SERVER['HTTPS']);
    $delos_https = preg_match('/^https/i', $app->getParam(OpenDelosApp::URL)->value());

    if (!$server_https && $delos_https) {
        Session::Messages($langOpenDelosHttpsError, 'alert-danger');
        $app->setEnabled(0);
    }

    redirect_to_home_page($app->getConfigUrl());
}

$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
$pageName = $langModify . ' ' . $app->getDisplayName();
$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => 'extapp.php',
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

$boolean_field = "";

$tool_content .= "<div class='row extapp'><div class='col-xs-12'>";
$tool_content .= "<div class='form-wrapper'>";
$tool_content .= "<form class='form-horizontal' role='form' action='" . $_SERVER['SCRIPT_NAME'] . "' method='post'>";
$tool_content .= "<fieldset>";

foreach ($app->getParams() as $param) {

    if ($param->getType() == ExtParam::TYPE_BOOLEAN) {
        $checked = $param->value() == 1 ? "value='0' checked" : "value='1'";
        $boolean_field .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
        $boolean_field .= "<label><input type='checkbox' name='" . $param->name() . "' $checked>" . $param->display() . "</label>";
        $boolean_field .= "</div></div></div>";
    } elseif ($param->getType() == ExtParam::TYPE_MULTILINE) {
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='" . $param->name() . "' class='col-sm-2 control-label'>" . $param->display() . "</label>";
        $tool_content .= "<div class='col-sm-10'><textarea class='form-control' rows='3' cols='40' name='" . $param->name() . "'>" .
            q($param->value()) . "</textarea></div>";
        $tool_content .= "</div>";
    } else {
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='" . $param->name() . "' class='col-sm-2 control-label'>" . $param->display() . "</label>";
        $tool_content .= "<div class='col-sm-10'><input class='form-control' type='text' name='" . $param->name() . "' value='" . q($param->value()) . "'></div>";
        $tool_content .= "</div>";
    }
}

$tool_content .= $boolean_field;
$tool_content .= "<div class='form-group'>";
$tool_content .= "<div class='col-sm-offset-2 col-sm-10'>";
$tool_content .= "<button class='btn btn-primary' type='submit' name='submit' value='$langModify'>$langModify</button> <button class='btn btn-danger' type='submit' name='submit' value='clear'>$langClearSettings</button>";
$tool_content .= "</div>";
$tool_content .= "</div>";
$tool_content .= "</fieldset>". generate_csrf_token_form_field() ."";
$tool_content .= "</form></div></div></div>";

draw($tool_content, 3, null);

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

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'extconfig/externals.php';

$toolName = $langExtAppConfig;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
load_js('tools.js');
load_js('validation.js');
$available_themes = active_subdirs("$webDir/template", 'theme.html');

$appName = isset($_GET['edit'])? $_GET['edit']: null;

// Code to be executed with Ajax call when clicking the activate/deactivate button from External App list page
if (isset($_POST['state'])) {
    $appName = $_POST['appName'];
    $newState = $_POST['state'] == 'fa-toggle-on' ? 0 : 1;
    $appNameAjax = $appName;

    ExtAppManager::getApp($appNameAjax)->setEnabled($newState);
    echo $newState;
    exit;
}

if ($appName) {
    $app = ExtAppManager::getApp($appName);

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
    $tool_content .= "<form class='form-horizontal' role='form' action='extapp.php?edit=" . $appName . "' method='post'>";
    $tool_content .= "<fieldset>";
    foreach ($app->getParams() as $param) {
        if ($param->getType() == ExtParam::TYPE_BOOLEAN) {
            $checked = $param->value() == 1 ? "checked" : "";
            $boolean_field .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
            $boolean_field .= "<label><input type='checkbox' name='" . $param->name() . "' value='1' $checked>" . $param->display() . "</label>";
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
} else {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    $tool_content .= "<div class='row extapp'><div class='col-xs-12'>";
    $tool_content .="<table class='table-default dataTable no-footer extapp-table'>";
    $tool_content .="<thead class='list-header'><td>$langExtAppName</td><td>$langDescription</td></thead>";
    foreach (ExtAppManager::getApps() as $app) {
        $tool_content .="<tr>";
        // WARNING!!!! LEAVE THE SIZE OF THE IMAGE TO BE DOUBLE THE SIZE OF THE ACTUAL PNG FILE, TO SUPPORT HDPI DISPLAYS!!!!
        $tool_content .= "<td style='width:90px; padding:0px;'>";
        $tool_content .= "<div class='text-center' style='padding:10px;'><a href='$urlAppend" . $app->getConfigUrl() . "''>";
        if ($app->getAppIcon() !== null) {
            $tool_content .= "<img height='50' width='89' src='" . $app->getAppIcon() . "'/>";
        }
        if ($app->isConfigured()) {
            if ($app->getName() == 'h5p') { // h5p is enabled
                $app_active = "<button type='button' class='btn btn-success' data-app='" . $app->getName() . "'> <i class='fa fa-toggle-on'></i> </button>";
            } else {
                $app_active = $app->isEnabled() ? "<button type='button' class='btn btn-success extapp-status' data-app='" . $app->getName() . "'> <i class='fa fa-toggle-on'></i> </button>" : "<button type='button' class='btn btn-danger extapp-status' data-app='" . $app->getName() . "'> <i class='fa fa-toggle-off'></i></button>";
            }
        } else {
            $app_active = "<button type='button' class='btn btn-default' data-app='" . $app->getName() . "'  data-toggle='modal' data-target='#noSettings'> <i class='fa fa-warning'></i> </button>";
        }
        $tool_content .= $app->getDisplayName() . "</a>";
        $tool_content .= "</div></td>";
        $tool_content .= "<td class='text-muted clearfix'><div class='extapp-dscr-wrapper'>" . $app->getShortDescription() . "</div><div class='extapp-controls'><div class='btn-group btn-group-sm'>" . $app_active . "<a href='$urlAppend" . $app->getConfigUrl() . "' class='btn btn-primary'> <i class='fa fa-sliders fw'></i> </a></div></div></td>";
        $tool_content .="</tr>";
    }

    $tool_content .="</table>";
    $tool_content .= "</div></div>";

    // Modal message when trying to enable tool without applying settings
    $tool_content .= "<div class='modal fade' id='noSettings' tabindex='-1' role='dialog' aria-labelledby='myModalLabel'>
                          <div class='modal-dialog' role='document'>
                            <div class='modal-content'>
                              <div class='modal-header'>
                                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                                <h4 class='modal-title' id='myModalLabel'>$langNotConfigured</h4>
                              </div>
                              <div class='modal-body'>
                                $langEnableAfterConfig
                              </div>
                            </div>
                          </div>
                        </div>";
}

draw($tool_content, 3, null, $head_content);

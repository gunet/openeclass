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
require_once 'extconfig/externals.php';

$toolName = $langExtAppConfig;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
load_js('tools.js');
load_js('validation.js');
$available_themes = active_subdirs("$webDir/template", 'theme.html');

$shouldEdit = isset($_GET['edit']);
$shouldUpdate = isset($_GET['update']);
$appName = $shouldEdit ? $_GET['edit'] : ($shouldUpdate ? $_GET['update'] : null);

if ($appName) {
    $app = ExtAppManager::getApp($appName);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "extapp.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    $tool_content .= "<h4>" . $langModify . " " . $app->getDisplayName() . "</h4>\n";

    if ($shouldUpdate) {
        $result = $app->storeParams();
        if ($result) {
            $tool_content .= "<div class='alert alert-danger'>$result</div>";
            $shouldEdit = true;
        } else {
            $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";
        }
    }
    if ($shouldEdit) {
        $tool_content .= "\n<div class='form-wrapper'>\n";
        $tool_content .= "  <form class='form-horizontal' role='form' action='extapp.php?update=" . $appName . "' method='post'>\n";
        $tool_content .= "    <fieldset>\n";

        foreach ($app->getParams() as $param) {
            $tool_content .= "      <div class='form-group'>\n";
            $tool_content .= "        <label for='" . $param->name() . "' class='col-sm-2 control-label'>" . $param->display() . "</label>\n";
            $tool_content .= "        <div class='col-sm-10'><input class='FormData_InputText form-control' type='text' name='" . $param->name() . "' value='" . $param->value() . "'></div>";
            $tool_content .= "      </div>\n";
        }

        $tool_content .= "      <div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit' value='$langModify'></div>\n";
        $tool_content .= "    </fieldset>\n";
        $tool_content .= "  </form>\n</div>\n";
    }
} else {
    foreach (ExtAppManager::getApps() as $app) {
        $tool_content .= "    <p><form method='post' action='extapp.php?edit=" . $app->getName() . "'>
        <input class='btn btn-info' type='submit' value='" . $app->getDisplayName() . "'>
    </form></p>\n";
    }
    $tool_content .= "    <p><form method='post' action='bbbmoduleconf.php'>
        <input class='btn btn-info' type='submit' value='BigBlueButton'>
    </form><p>\n";
}


draw($tool_content, 3, null, $head_content);

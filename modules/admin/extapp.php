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

if (isset($_POST['state'])) {
    $newState = $_POST['state'] == 'fa-toggle-on' ? 0 : 1;

    $appNameAjax = $_POST['appName'];
    ExtAppManager::getApp($appNameAjax)->setEnabled($newState);

    echo $newState;
    exit;
}

if (!$shouldEdit) {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
}

if ($appName) {
    $navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
    $app = ExtAppManager::getApp($appName);
    $pageName = $langModify . ' ' . $app->getDisplayName();
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => 'extapp.php',
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    if ($shouldUpdate) {
        $result = $app->storeParams();
        if ($result) {
            Session::Messages($result, 'alert-danger');
        } else {
            Session::Messages($langFileUpdatedSuccess, 'alert-success');
        }
        redirect_to_home_page('modules/admin/extapp.php?edit=' . $appName);
    }

    $boolean_field = "";

    if ($shouldEdit) {
        $tool_content .= "\n<div class='row extapp'>\n<div class='col-xs-12'>\n";
        $tool_content .= "  <div class='form-wrapper'>\n";
        $tool_content .= "    <form class='form-horizontal' role='form' action='extapp.php?update=" . $appName . "' method='post'>\n";
        $tool_content .= "      <fieldset>\n";

        foreach ($app->getParams() as $param) {

            if ($param->getType() == ExtParam::TYPE_BOOLEAN) {
                $checked = $param->value() == 1 ? "value='0' checked" : "value='1'";
                $boolean_field .= "        <div class='form-group'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>\n";
                $boolean_field .= "          <label><input type='checkbox' name='" . $param->name() . "' $checked>" . $param->display() . "</label>";
                $boolean_field .= "        </div></div></div>\n";
            } else {
                $tool_content .= "        <div class='form-group'>\n";
                $tool_content .= "          <label for='" . $param->name() . "' class='col-sm-2 control-label'>" . $param->display() . "</label>\n";
                $tool_content .= "          <div class='col-sm-10'><input class='form-control' type='text' name='" . $param->name() . "' value='" . $param->value() . "'></div>";
                $tool_content .= "        </div>\n";
            }
        }

        $tool_content .= $boolean_field;
        $tool_content .= "          <div class='form-group'>\n";
        $tool_content .= "              <div class='col-sm-offset-2 col-sm-10'>";
        $tool_content .= "                  <button class='btn btn-primary' type='submit' name='submit' value='$langModify'>$langModify</button> <button class='btn btn-danger' type='submit' name='submit' value='$langClearSettings'>$langClearSettings</button>";
        $tool_content .= "              </div>\n";
        $tool_content .= "          </div>\n";
        $tool_content .= "      </fieldset>\n";
        $tool_content .= "    </form>\n</div>\n</div>\n</div>\n";
        //$tool_content .= "<p>".$app->getLongDescription()."</p>";
    }
} else {
    $tool_content .= "<div class=\"row extapp\">\n<div class='col-xs-12'>\n";
    $tool_content .="<table class=\"table-default dataTable no-footer extapp-table\">\n";
    $tool_content.="<thead class='list-header'><td>$langExtAppName</td><td>$langExtAppDescription</td></thead>\n";
    $tool_content.="\n";
    foreach (ExtAppManager::getApps() as $app) {
        $tool_content .="<tr>\n";
        // WARNING!!!! LEAVE THE SIZE OF THE IMAGE TO BE DOUBLE THE SIZE OF THE ACTUAL PNG FILE, TO SUPPORT HDPI DISPLAYS!!!!
        //$tool_content .= "<td style=\"width:90px;\"><a href=\"extapp.php?edit=" . $app->getName() . "\"'><img height=\"50\" width=\"89\" src=\"" . $app->getAppIcon() . "\"/></a></td>\n";
        //$tool_content .= "<td style=\"vertical-align:middle; text-align:center; width:1px;\"><a href=\"extapp.php?edit=" . $app->getName() . "\"'>" . $app->getDisplayName() . "</a></td>\n";
        $tool_content .= "<td style=\"width:90px; padding:0px;\">";
        $tool_content .= "<div class=\"text-center\" style=\"padding:10px;\"><a href=\"extapp.php?edit=" . $app->getName() . "\"'>";
        if ($app->getAppIcon() !== null) {
            $tool_content .= "<img height=\"50\" width=\"89\" src=\"" . $app->getAppIcon() . "\"/>\n";
        }
        $app_active = $app->isEnabled() ? "<button type=\"button\" class=\"btn btn-success extapp-status\" data-app=\"" . $app->getName() . "\"> <i class=\"fa fa-toggle-on\"></i> </button>" : "<button type=\"button\" class=\"btn btn-danger extapp-status\" data-app=\"" . $app->getName() . "\"> <i class=\"fa fa-toggle-off\"></i> </button>";
        $tool_content .= $app->getDisplayName() . "</a></div></td>\n";

        $tool_content .= "<td class=\"text-muted\"><div class=\"extapp-dscr-wrapper\">" . $app->getShortDescription() . "<div class=\"extapp-controls\"><div class=\"btn-group btn-group-xs\">" . $app_active . "<a href=\"extapp.php?edit=" . $app->getName() . "\" class=\"btn btn-default\"> <i class=\"fa fa-sliders fw\"></i> </a></div></div></div></td>\n";
        $tool_content .="</tr>\n";
    }
    $tool_content .="<tr>\n";
    $tool_content .= "<td style=\"width:90px; padding:0px;\"><div class=\"text-center\" style=\"padding:10px;\"><a href=\"bbbmoduleconf.php\"><img height=\"50\" width=\"89\"  class=\"img-responsive\" src=\"../../template/icons/bigbluebutton.png\"/>BigBlueButton</a></div></td>\n";
    $tool_content .= "<td class=\"text-muted\"><div class=\"extapp-dscr-wrapper\">$langBBBDescription<div class=\"extapp-controls\"><div class=\"btn-group btn-group-xs\"><span class=\"btn btn-success\"><i class=\"fa fa-toggle-on fw\"></i></span> <a href=\"bbbmoduleconf.php\" class=\"btn btn-default\"> <i class=\"fa fa-sliders fw\"></i> </a></div></div></div></td>\n";
    $tool_content .="</tr>\n";

    $tool_content.="</table>\n";
    $tool_content .= "</div>\n</div>\n";
}

draw($tool_content, 3, null, $head_content);

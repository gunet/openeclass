<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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
 * ======================================================================== */


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/autojudgeapp.php';
$nameTools = $langAutoJudge;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
$pageName = $langBasicCfgSetting;

$available_themes = active_subdirs("$webDir/template", 'theme.html');

// Scan the connectors directory and locate the appropriate classes
$connectorClasses = AutojudgeApp::getAutoJudgeServices();

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "extapp.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')),false);

// Save new auto_judge.php
if (isset($_POST['submit'])) {
    set_config('autojudge_connector', $_POST['formconnector']);
    foreach($connectorClasses as $curConnectorClass) {
        $connector = new $curConnectorClass();
        foreach($connector->getConfigFields() as $curField => $curLabel) {
            set_config($curField, $_POST['form'.$curField]);
        }
    }

    // Display result message
    Session::Messages($langAutoJudgeUpdated, 'alert-success');
    redirect_to_home_page('modules/admin/extapp.php');
} // end of if($submit)
// Display auto_judge.php edit form
else {
    $connectorOptions = array_map(function($connectorClass) {
        $connector = new $connectorClass();
        $selected = q(get_config('autojudge_connector')) == $connectorClass ? " selected='selected'" : '';
        return "<option value='$connectorClass'$selected>".$connector->getName()."</option>";
    }, $connectorClasses);
    $tool_content .= "<div class='form-wrapper'><form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]' method='post'>
	 <div class='form-group'>
            <label class='col-sm-3 control-label'>$langAutoJudgeConnector:</label>
            <div class='col-sm-8'><select class='form-control' name='formconnector'>".implode('', $connectorOptions)."</select></div>
         </div>";
    foreach($connectorClasses as $curConnectorClass) {
        $connector = new $curConnectorClass();
        $tool_content .= "
        <div class='form-group connector-config connector-$curConnectorClass' style='display: none;'>
            <label class='col-sm-3 control-label'>$langAutoJudgeSupportedLanguages:</label>
            <div class='col-sm-8'>".implode(', ', array_keys($connector->getSupportedLanguages()))."</div>
        </div>
        <div class='form-group connector-config connector-$curConnectorClass' style='display: none;'>
            <label class='col-sm-3 control-label'>$langAutoJudgeSupportsInput:</label>
            <div class='col-sm-8'>".($connector->supportsInput() ? $langCMeta['true'] : $langCMeta['false'])."</div>
        </div>";
        foreach($connector->getConfigFields() as $curField => $curLabel) {
            $tool_content .= "
              <div class='form-group connector-config connector-$curConnectorClass' style='display: none;'>
                <label class='col-sm-3 control-label'>$curLabel:</label>
                <div class='col-sm-8'><input class='FormData_InputText' type='text' name='form$curField' size='40' value='" . q(get_config($curField)) . "'></div>
              </div>";
        }
    }
    $tool_content .= "<div class='form-group'><div class='col-sm-offset-3'>";
    $tool_content .= form_buttons(array(
        array(
            'text' => $langModify,
            'name' => 'submit',
            'value'=> $langModify
        ),
        array(
            'href' => "extapp.php"
        )
    ));
    $tool_content .= "</div></div>";
    $tool_content .= "</form>";

    $tool_content .= "</div>";
    $head_content .= "
        <script type='text/javascript'>
        function update_connector_config_visibility() {
            $('div.connector-config').hide();
            $('div.connector-config input').removeAttr('required');
            $('div.connector-'+$('select[name=\"formconnector\"]').val()).show();
            $('div.connector-'+$('select[name=\"formconnector\"]').val()+' input').attr('required', 'required');
        }
        $(document).ready(function() {
            $('select[name=\"formconnector\"]').change(function() {
                update_connector_config_visibility();
            });
            update_connector_config_visibility();
        });
        </script>";
}

draw($tool_content, 3, null, $head_content);


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
require_once 'modules/admin/extconfig/wafapp.php';
$nameTools = $langWaf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

$available_themes = active_subdirs("$webDir/template", 'theme.html');

// Scan the connectors directory and locate the appropriate classes
$connectorClasses = WafApp::getWafServices();

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    set_config('waf_connector', $_POST['formconnector']);
    foreach($connectorClasses as $curConnectorClass) {
        $connector = new $curConnectorClass();
        foreach($connector->getConfigFields() as $curField => $curLabel) {
            set_config($curField, preg_replace('/[^0-9]/', '', $_POST[$curField]));
        }
        $connector->updateRules();
    }

    // Display result message
    $tool_content .= "<div class='alert alert-success'>$langWafUpdated</div>";
} // end of if($submit)
else {
    $connectorOptions = array_map(function($connectorClass) {
        $connector = new $connectorClass();
        $selected = q(get_config('awaf_connector')) == $connectorClass ? " selected='selected'" : '';
        return "<option value='$connectorClass'$selected>".$connector->getName()."</option>";
    }, $connectorClasses);
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>
                <fieldset><legend>$langBasicCfgSetting</legend>
	 <table class='table table-bordered' width='100%'>
         <tr>
            <th width='200' class='left'><b>$langWafConnector</b></th>
            <td><select name='formconnector'>".implode('', $connectorOptions)."</select></td>
         </tr>";
    foreach($connectorClasses as $curConnectorClass) {
        $connector = new $curConnectorClass();

        $rules = $connector->getRules();
        foreach($connector->getConfigFields() as $curField => $curLabel) {
            $enabled = get_config($curField);
            if(!$enabled || $enabled == 0){
                $checked = "";
                $notchecked = " checked='true' ";
            }else{
                $checked = " checked='true' ";
                $notchecked = "";
            }
            $tool_content .= "
              <tr class='connector-config connector-$curConnectorClass' style='display: none;'>
                <th width='200' class='left'><b>Rule ".q($curLabel)."</b><br><br><var>Impact: ".q($rules[$curField]['impact'])."</var></th>
                <td><input class='FormData_InputText' type='text' name='form$curField' disabled='disabled' size='80'  value='" . q($rules[$curField]['rule']). "'>
                <input class='FormData_InputText' type='text' name='form$curField' disabled='disabled' size='80' value='" . q($rules[$curField]['description']). "'></td>
                <td>  
                <label class='col-sm-3 control-label'>$langActivate:</label>
                    <br>
                    <div class='col-sm-9 radio'><label><input  type='radio' id='$curField' name='$curField' " . $notchecked ." value='0'>$langNo</label></div>
                    <div class='col-sm-offset-3 col-sm-9 radio'><label><input  type='radio' id='$curField' name='$curField' " . $checked ." value='1'>$langYes</label></div>
                </td>
              </tr>";
        }
    }
    $tool_content .= "</table></fieldset>";
    $tool_content .= "<input class='btn btn-primary' type='submit' name='submit' value='$langModify'>". generate_csrf_token_form_field() ."</form>";

    $head_content .= "
        <script type='text/javascript'>
        function update_connector_config_visibility() {
            $('tr.connector-config').hide();
            $('tr.connector-config input').removeAttr('required');
            $('tr.connector-'+$('select[name=\"formconnector\"]').val()).show();
            $('tr.connector-'+$('select[name=\"formconnector\"]').val()+' input').attr('required', 'required');
        }
        $(document).ready(function() {
            $('select[name=\"formconnector\"]').change(function() {
                update_connector_config_visibility();
            });
            update_connector_config_visibility();
        });
        </script>";
}

// Display link to index.php
$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "extapp.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));
draw($tool_content, 3, null, $head_content);


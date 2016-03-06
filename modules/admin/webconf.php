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

$toolName = $langWebConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('validation.js');

$available_themes = active_subdirs("$webDir/template", 'theme.html');

$wc_server = isset($_GET['edit_server']) ?  $_GET['edit_server'] : '';

if (isset($_GET['add_server'])) {
    $pageName = $$langAddWebConfServer;
    $toolName = $langWebConf;
    $navigation[] = array('url' => 'webconf.php', 'name' => $langWebConf);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "webconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='host' class='col-sm-3 control-label'>$langWebConfServer:</label>
                    <div class='col-sm-9'><input class='form-control' id='host' type='text' name='hostname_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langActivate:</label>
            <div class='col-sm-9 radio'><label><input  type='radio' id='enabled_false' name='enabled' checked='false' value='false'>$langNo</label></div>
            <div class='col-sm-offset-3 col-sm-9 radio'><label><input  type='radio' id='enabled_true' name='enabled' checked='true' value='true'>$langYes</label></div>
        </div>";
    $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'><input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'></div></div>";
    $tool_content .= "</fieldset></form></div>";

    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("serverForm");
            chkValidator.addValidation("hostname_form","req","' . $langWebConfServerAlertHostname . '");
        //]]></script>';

} else if (isset($_GET['delete_server'])) {
    $id = $_GET['delete_server'];
    Database::get()->querySingle("DELETE FROM wc_servers WHERE id=?d", $id);
    // Display result message
    $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "webconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
}
// Save new config.php
else if (isset($_POST['submit'])) {
    $hostname = $_POST['hostname_form'];
    $enabled = $_POST['enabled'];
    
    if (isset($_POST['id_form'])) {
        $id = $_POST['id_form'];
        Database::get()->querySingle("UPDATE wc_servers SET hostname = ?s,
                enabled=?s
                WHERE id =?d", $hostname, $enabled, $id);
    } else {
        Database::get()->querySingle("INSERT INTO wc_servers (hostname,enabled) VALUES
        (?s,?s)", $hostname, $enabled);
    }    
    // Display result message
    $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";
    // Display link to go back to index.php
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "webconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
} // end of if($submit)
// Display config.php edit form
else {    
    if (isset($_GET['edit_server'])) {
        $pageName = $langEdit;
        $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "webconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
        
        $server = Database::get()->querySingle("SELECT * FROM wc_servers WHERE id = ?d", $wc_server);
        
        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<fieldset>";        
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='host' class='col-sm-3 control-label'>$langWebConfServer:</label>
                        <div class='col-sm-9'><input class='form-control' id='host' type='text' name='hostname_form' value='$server->hostname'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<label class='col-sm-3 control-label'>$langActivate:</label>";
        if ($server->enabled == "false") {
            $checkedfalse2 = " checked='false' ";
        } else $checkedfalse2 = '';
        
        $tool_content .= "<div class='col-sm-9 radio'><label><input  type='radio' id='enabled_false' name='enabled' $checkedfalse2 value='false'>$langNo</label></div>";
        
        if ($server->enabled == "true") {
            $checkedtrue2 = " checked='false' ";
        } else $checkedtrue2 = '';
        
        $tool_content .= "<div class='col-sm-offset-3 col-sm-9 radio'><label><input  type='radio' id='enabled_true' name='enabled' $checkedtrue2 value='true'>$langYes</label></div>";      $tool_content .= "<input class='form-control' type = 'hidden' name = 'id_form' value='$wc_server'>";
        $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'><input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'></div></div>";
        $tool_content .= "</fieldset></form></div>";

        $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("serverForm");
            chkValidator.addValidation("hostname_form","req","' . $langWebConfServerAlertHostname . '");
        //]]></script>';
        
    } else {
        //display available WebConf servers
        $tool_content .= action_bar(array(
            array('title' => $langAddWebConfServer,
                'url' => "webconf.php?add_server",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langBack,
                'url' => "extapp.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));

        $q = Database::get()->queryArray("SELECT * FROM wc_servers");
        if (count($q)>0) {
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                <tr><th class = 'text-center'>$langWebConfServer</th>
                    <th class = 'text-center'>".icon('fa-gears')."</th></tr>
                </thead>";
            foreach ($q as $srv) {
                $enabled_bbb_server = ($srv->enabled)? $langYes : $langNo;

                $tool_content .= "<tr>";
                $tool_content .= "<td>$srv->hostname</td>";
                $tool_content .= "<td class='option-btn-cell'>".action_button(array(
                                                    array('title' => $langEditChange,
                                                          'url' => "$_SERVER[SCRIPT_NAME]?edit_server=$srv->id",
                                                          'icon' => 'fa-edit'),
                                                    array('title' => $langDelete,
                                                          'url' => "$_SERVER[SCRIPT_NAME]?delete_server=$srv->id",
                                                          'icon' => 'fa-times',
                                                          'class' => 'delete',
                                                          'confirm' => $langConfirmDelete)
                                                    ))."</td>";
                $tool_content .= "</tr>";
            }            	
            $tool_content .= "</table></div>";
        } else {
             $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν διαθέσιμοι εξυπηρετητές.</div>";
        }
    }
}

draw($tool_content, 3, null, $head_content);

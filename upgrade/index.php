<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

define('UPGRADE', true);

require_once '../include/baseTheme.php';

if ($urlAppend[strlen($urlAppend) - 1] != '/') {
    $urlAppend .= '/';
}

$nameTools = $langUpgrade;

if ($language == 'el') {
    $upgrade_info_file = 'http://wiki.openeclass.org/doku.php?id=el:upgrade_doc';
    $link_changes_file = 'http://wiki.openeclass.org/el:changes';
} else {
    $upgrade_info_file = 'http://wiki.openeclass.org/doku.php?id=en:upgrade_doc';
    $link_changes_file = 'http://wiki.openeclass.org/en:changes';
}
// Main body
$tool_content .= "
<div class='alert alert-warning'><b>$langWarnUpgrade</b><p>$langExplUpgrade</p>
<p> &nbsp; </p>
<p class='red'>$langExpl2Upgrade</p>";
set_time_limit(0);
$tool_content .= "<p>&nbsp;</p><p>$langUpgToSee <a href='$link_changes_file' target=_blank>$langHere</a>. $langUpgRead <a href='$upgrade_info_file' target=_blank>$langUpgMan</a>
   $langUpgLastStep</p>
<p>$langUpgradeCont</p></div>";
$tool_content .= "<div class = 'form-wrapper'>
    <form class='form-horizontal' role='form' method='post' action='upgrade.php'>
    <fieldset>
    <div class='form-group'><label for='Details' class='col-sm-2 control-label'>$langUpgDetails</label></div>
    <div class='form-group'>
        <label for='username' class='col-sm-2 control-label'>$langUsername</label>
        <div class='col-sm-10'>
            <input class='auth_input_admin' style='width:200px; heigth:20px;' type='text' name='login' size='20' autocomplete='off' >
        </div>
    </div>
    <div class='form-group'>
        <label for='pass' class='col-sm-2 control-label'>$langPass:</label>
        <div class='col-sm-10'>
            <input class='auth_input_admin' type='password' style='width:200px; heigth:20px;' name='password' size='20' autocomplete='off' >
        </div>
    </div>
    <p class='pull-right'>
      <input class='btn btn-primary' type='submit' name='submit_upgrade2' value='$langUpgrade' />
     </p>";
 
$tool_content .= "</fieldset></form></div>";

draw($tool_content, 0);

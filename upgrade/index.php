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

$pageName = $langUpgrade;

if ($language == 'el') {
    $upgrade_info_file = 'http://wiki.openeclass.org/doku.php?id=el:upgrade_doc';
    $link_changes_file = 'http://wiki.openeclass.org/el:changes';
} else {
    $upgrade_info_file = 'http://wiki.openeclass.org/doku.php?id=en:upgrade_doc';
    $link_changes_file = 'http://wiki.openeclass.org/en:changes';
}

// Main body
$tool_content .= "
  <div class='alert alert-warning'>
    <h4>$langWarnUpgrade</h4>
    <p class='margin-bottom-thin'>$langExplUpgrade</p>
    <p class='margin-top-thin margin-bottom-thin text-danger'>$langExpl2Upgrade</p>
    <p class='margin-top-thin margin-bottom-thin'>$langUpgToSee <a href='$link_changes_file' target=_blank>$langHere</a>.
      $langUpgRead <a href='$upgrade_info_file' target='_blank'>$langUpgMan</a> $langUpgLastStep</p>
    <p>$langUpgradeCont</p>
  </div>
  
  <div class='panel panel-default login-page'>
    <div class='panel-heading'><span>$langUpgDetails</span></div>
    <div class='panel-body login-page-option'>
      <form class='form-horizontal' role='form' action='upgrade.php' method='post'>
        <div class='form-group'>
          <div class='col-xs-12'>
            <input class='form-control' name='login' placeholder='$langUsername'>
          </div>
        </div>
        <div class='form-group'>
          <div class='col-xs-12'>
            <input class='form-control' name='password' placeholder='$langPass' type='password'>
          </div>
        </div>
        <div class='form-group'>
          <div class='col-xs-12'>
            <button class='btn btn-primary margin-bottom-fat' type='submit' name='submit_upgrade2' value='$langUpgrade'>$langUpgrade</button>
          </div>
        </div>
      </form>
    </div>
  </div>";

draw($tool_content, 0);

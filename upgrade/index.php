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
    $upgrade_info_file = 'https://docs.openeclass.org/el/upgrade';
    $link_changes_file = 'https://docs.openeclass.org/el/current';
} else {
    $upgrade_info_file = 'https://docs.openeclass.org/en/upgrade';
    $link_changes_file = 'https://docs.openeclass.org/el/current';
}

$tool_content .= "
        <div class='col-12'>            
            <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langExpl2Upgrade</span></div>
            <div class='alert alert-info'><i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                <p><span>$langUpgToSee <a href='$link_changes_file' target=_blank>$langHere</a>.
                    $langUpgRead <a href='$upgrade_info_file' target='_blank' aria-label='(opens in a new tab)'>$langUpgMan</a> $langUpgLastStep
                </p></span>
            </div>            
        </div>";


  $tool_content .= "<div class='card panelCard px-lg-4 py-lg-3 login-page m-auto' style='max-width:400px;'>
      <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
        <h3>$langUpgDetails</h3>
      </div>
      <div class='card-body login-page-option rounded-0'>

        <form role='form' action='upgrade.php' method='post'>

          <div class='form-group mt-3'>
            <div class='col-12 ms-auto me-auto'>
              <input class='form-control' name='login' placeholder='$langUsername' type='text'>
            </div>
          </div>

          <div class='form-group mt-3'>
            <div class='col-12 ms-auto me-auto'>
              <input class='form-control' name='password' placeholder='$langPass' type='password'>
            </div>
          </div>

          <div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-center'>
              <button class='btn submitAdminBtn margin-bottom-fat' type='submit' name='submit_upgrade2' value='$langUpgrade'>$langUpgrade</button>
            </div>
          </div>

        </form>

      </div>
  </div>
</div>";

draw($tool_content, 0);

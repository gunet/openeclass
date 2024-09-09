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
    <div class='row row-cols-lg-2 row-cols-1 g-4 mt-0'>
      <div class='col'>
        <div class='alert alert-warning mt-0'>
          <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
          <span>$langExpl2Upgrade</span>
        </div>
        <div class='alert alert-info'>
          <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
          <span>$langUpgToSee <a href='$link_changes_file' target=_blank>$langHere</a>.
                $langUpgRead <a href='$upgrade_info_file' target='_blank' aria-label='$langOpenNewTab'>$langUpgMan</a> $langUpgLastStep
          </span>
        </div> 
        <div class='card panelCard px-lg-4 py-lg-3'>
          <div class='card-header border-0 d-flex justify-content-between align-items-center'>
            <h2>$langUpgDetails</h2>
          </div>
          <div class='card-body'>
            <form role='form' action='upgrade.php' method='post'>
              <div class='form-group'>
                <label for='admin_username' class='control-label-notes'>$langUsername</label>
                <input id='admin_username' class='form-control' name='login' placeholder='$langUsername' type='text'>
              </div>
              <div class='form-group mt-4'>
                <label for='admin_password' class='control-label-notes'>$langPass</label>
                <input id='admin_password' class='form-control' name='password' placeholder='$langPass' type='password'>
              </div>
              <div class='form-group mt-5'>
                <button class='btn submitAdminBtn w-100' type='submit' name='submit_upgrade2' value='$langUpgrade'>$langUpgrade</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class='col d-none d-lg-block text-end'>
        <img class='form-image-modules' src='" . get_form_image() . "' alt='form-image'>
      </div>
    </div>";

draw($tool_content, 0);

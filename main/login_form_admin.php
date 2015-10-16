<?php

/* ========================================================================
 * Open eClass 3.2
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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

$pageName = $langAdminLoginPage;

$tool_content .= "    
  <div class='panel panel-default login-page'>
    <div class='panel-heading'><span>$langUpgDetails</span></div>
    <div class='panel-body login-page-option'>
      <form class='form-horizontal' role='form' action='$urlServer' method='post'>
        <div class='form-group'>
          <div class='col-xs-12'>
            <input class='form-control' name='uname' placeholder='$langUsername'>
          </div>
        </div>
        <div class='form-group'>
          <div class='col-xs-12'>
            <input class='form-control' name='pass' placeholder='$langPass' type='password'>
          </div>
        </div>
        <div class='form-group'>
          <div class='col-xs-12'>
            <button class='btn btn-primary margin-bottom-fat' type='submit' name='submit' value='submit'>$langAdminLoginPage</button>
          </div>
        </div>
      </form>
    </div>
  </div>";

draw($tool_content, 0);

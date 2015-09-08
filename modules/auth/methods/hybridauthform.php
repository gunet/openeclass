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
 /* ===========================================================================
  hybridauthform.php
  @authors list: Sakis Agorastos <th_agorastos@hotmail.com>
  ==============================================================================
  @Description: Generic form for HybridAuth providers
  ==============================================================================
 */

function hybridAuthForm($auth) {
    global $tool_content, $langAuthenticateVia, $langInstructionsAuth;

    $r = Database::get()->querySingle("SELECT auth_settings, auth_instructions, auth_name FROM auth WHERE auth_id = ?d", $auth);
    if (!empty($r->auth_settings)) {
        $auth_settings = unserialize($r->auth_settings);
    } else {
        $auth_settings['id'] = $auth_settings['secret'] = '';
    }
    $auth_instructions = $r->auth_instructions;
    $authName = q(ucfirst($r->auth_name));

    $tool_content .= "<div class='alert alert-info'>" . ucfirst($langAuthenticateVia) . " $authName</div>
      <div class='form-group'>
          <label for='hybridauth_id_key' class='col-sm-2 control-label'>$authName Id/Key:</label>
          <div class='col-sm-10'>
              <input class='form-control' name='hybridauth_id_key' id='hybridauth_id_key' type='text' value='" . q($auth_settings['id']) . "'>
          </div>
      </div> 
      <div class='form-group'>
          <label for='hybridauth_secret' class='col-sm-2 control-label'>" . ucfirst($r->auth_name) . " Secret:</label>
          <div class='col-sm-10'>
              <input class='form-control' name='hybridauth_secret' id='hybridauth_secret' type='text' value='" . q($auth_settings['secret']) . "'>
          </div>
      </div> 
      <div class='form-group'>
          <label for='auth_instructions' class='col-sm-2 control-label'>$langInstructionsAuth:</label>
          <div class='col-sm-10'>
              <textarea class='form-control' name='hybridauth_instructions' id='hybridauth_instructions' rows='10'>" . q($auth_instructions) . "</textarea>
          </div>
      </div>";
}

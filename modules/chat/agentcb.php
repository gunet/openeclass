<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

  ============================================================================
  @Description: Main script for the work tool
  ============================================================================
 */

$require_current_course = TRUE;
$require_login = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/chat/functions.php';

$actionBar .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label'
    )
));

$tool_content = $actionBar . "<div class='alert alert-danger'>" . $langColmoocCreateAgentFailed . "</div>";

if (isset($_GET['id']) && $is_editor) {

    $conf = Database::get()->querySingle("SELECT * FROM conference WHERE conf_id = ?d", $_GET['id']);
    if ($conf && $conf->conf_id && $conf->agent_id) {
        $success = colmooc_update_activity($conf->conf_id, $conf->conf_title, $conf->agent_id);
        if ($success) {
            Database::get()->query("UPDATE conference SET agent_created = true WHERE conf_id = ?d", $conf->conf_id);
            $tool_content = $actionBar . "<div class='alert alert-info'>" . $langColmoocCreateAgentSuccess . "</div>";
        }
    }
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
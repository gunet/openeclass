<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_current_course = TRUE;
$require_login = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/chat/functions.php';

$actionBar .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary'
    )
));

$tool_content = $actionBar . "<div class='col-sm-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>" . $langColmoocCreateAgentFailed . "</span></div></div>";

if (isset($_GET['id']) && $is_editor) {

    $conf = Database::get()->querySingle("SELECT * FROM conference WHERE conf_id = ?d", $_GET['id']);
    if ($conf && $conf->conf_id && $conf->agent_id) {
        $success = colmooc_update_activity($conf->conf_id, $conf->conf_title, $conf->agent_id);
        if ($success) {
            Database::get()->query("UPDATE conference SET agent_created = true WHERE conf_id = ?d", $conf->conf_id);
            $tool_content = $actionBar . "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" . $langColmoocCreateAgentSuccess . "</span></div></div>";
        }
    }
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);

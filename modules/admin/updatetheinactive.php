<?php

/* ========================================================================
 * Open eClass 3.0
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


$require_admin = TRUE;
require_once '../../include/baseTheme.php';
$toolName = $langAddTime;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array('url' => 'search_user.php', 'name' => $langSearchUser);

// Main body
$activate = isset($_GET['activate']) ? $_GET['activate'] : ''; //variable of declaring the activation update
// update process for all the inactive records/users
if ((!empty($activate)) && ($activate == 1)) {
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    
    // update        
    $countinactive = Database::get()->query("UPDATE user SET expires_at = ".DBHelper::timeAfter(15552000) . " WHERE expires_at<= CURRENT_DATE()")->affectedRows;
    if ($countinactive > 0) {
        $tool_content .= " " . $langRealised . " " . $countinactive . " " . $langChanges . " <br><br>";
    } else {
        $tool_content .= $langNoChanges;
    }
    
}
draw($tool_content, 3);

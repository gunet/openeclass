<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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



/* * ===========================================================================
  serachuser.php
  @last update: 15-10-2006 by Karatzidis Stratos
  @authors list: Karatzidis Stratos <kstratos@uom.gr>
  Pitsiougas Vagelis <vagpits@uom.gr>
  ==============================================================================
  @Description: Activate the inactive accounts


  ==============================================================================
 */


$require_admin = TRUE;
require_once '../../include/baseTheme.php';
$nameTools = $langAddTime;  // Define $nameTools
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

// Main body
$activate = isset($_GET['activate']) ? $_GET['activate'] : ''; //variable of declaring the activation update
// update process for all the inactive records/users
if ((!empty($activate)) && ($activate == 1)) {
    // do the update
    $newtime = time() + 15552000;
    $countinactive = Database::get()->query("UPDATE user SET expires_at=" . $newtime . " WHERE expires_at<=" . time())->affectedRows;
    if ($countinactive > 0) {
        $tool_content .= " " . $langRealised . " " . $countinactive . " " . $langChanges . " <br><br>";
    } else {
        $tool_content .= $langNoChanges;
    }
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
}
draw($tool_content, 3);

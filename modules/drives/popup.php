<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

//die(0);

session_start();
require_once 'clouddrive.php';

$drive = CloudDriveManager::getSessionDrive();

$callback_auth = $drive->getCallbackToken();
if (!$callback_auth) {
    header('Location: ' . $drive->getAuthURL());
    die();
} else if ($callback_auth) {
    if ($drive->authorize($callback_auth)) {
        echo "<script type='text/javascript'>";
        echo "window.close();";
        echo "</script>";
    } else {
        echo "Unable to authorize user";
    }
    die();
}


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

//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

session_start();
require_once 'clouddrive.php';
$drive = CloudDriveManager::getSessionDrive();
$parentDir = substr(urldecode($_POST['dir']), 0, -1);

echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
foreach ($drive->getFiles($parentDir) as $file) {
    if ($file->isFolder()) {
        echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . q($file->id()) . "/\">" . q($file->name()) . "</a></li>";
    } else {
        echo "<li class=\"file\"><a href=\"#\" rel=\"" . q($file->toJSON()) . "\">" . q($file->name()) . "</a></li>";
    }
}
//ext_$ext
echo "</ul>";

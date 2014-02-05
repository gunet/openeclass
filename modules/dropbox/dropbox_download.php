<?php
/* ========================================================================
 * Open eClass 2.8
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

/**
 * handles downloads of files. Direct downloading is prevented because of an .htaccess file in the
 * dropbox directory. So everything goes through this script.
 * 
 * 1. Initialising vars
 * 2. Authorisation 
 * 3. Sanity check of get data & file
 * 4. Send headers
 * 5. Send file
 * 
 */
$require_login = TRUE;
$require_current_course = TRUE;

require_once("functions.php");
require_once("dropbox_class.inc.php");
include '../../include/lib/forcedownload.php';

if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
} else {
        header("Location: $urlServer");
}

$work = new Dropbox_work($id);

$path = $dropbox_cnf["sysPath"] . "/" . $work -> filename; //path to file as stored on server
$file = $work->real_filename;

send_file_to_client($path, $file, null, true);
exit;
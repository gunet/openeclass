<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
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
 
require_once("dropbox_init1.inc.php");
require_once("dropbox_class.inc.php");
include '../../include/lib/forcedownload.php';

/**
 * ========================================
 * AUTHORISATION SECTION
 * ========================================
 */
if (!isset($uid))
{
    exit();
}

if (isset($_GET['mailing'])) 
{
	checkUserOwnsThisMailing($_GET['mailing'], $uid);
}


/**
 * ========================================
 * SANITY CHECKS OF GET DATA & FILE
 * ========================================
 */
if (!isset( $_GET['id']) || ! is_numeric( $_GET['id'])) die($dropbox_lang["generalError"]);

$work = new Dropbox_work($_GET['id']);

$path = $dropbox_cnf["sysPath"] . "/" . $work -> filename; //path to file as stored on server
$file = $work->title;

send_file_to_client($path, $file, null, true);
exit;

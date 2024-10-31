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

require_once '../clouddrive.php';
$drive = new Dropbox();
if (strpbrk($_GET['code'], "\r\n"))
	die();
if (strpbrk($_GET['state'], "\r\n"))
	die();
header('Location: ../popup.php?' . $drive->getDriveDefaultParameter() . "&code=" . urlencode($_GET['code']) . "&state=" . urlencode($_GET['state']));
die();

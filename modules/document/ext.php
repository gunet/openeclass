<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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

/**
  @file ext.php
  @brief redirect to external documents after recording hit via file.php
 */

$course = $_GET['course'] ?? '';
$path = $_GET['path'] ?? '';

if (empty($course) or empty($path)) {
    http_response_code(400); // Bad request
    exit;
}

define('EXTERNAL_FILE', true);
require_once 'file.php';

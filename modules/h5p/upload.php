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

$require_current_course = true;
require_once '../../include/baseTheme.php';

$backUrl = $urlAppend . 'modules/h5p/index.php?course=' . $course_code;

$can_upload = true;
$upload_target_url = 'courses/temp/h5p';

$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => $langH5p];

$data = [];

view('modules.h5p.upload', $data);

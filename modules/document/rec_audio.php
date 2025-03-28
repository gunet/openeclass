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

$require_login = true;
$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'documents';
$helpSubTopic = 'rec_audio';
require_once '../../include/baseTheme.php';

if (!get_config('allow_rec_audio')) {
    redirect_to_home_page();
}

$toolName = $langUploadRecAudio;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langDoc);

view('modules.document.rec_audio');

<?php
/*
 * ========================================================================
 * Open eClass 3.11 - E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
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
 *
 * For a full list of contributors, see "credits.txt".
 */

set_time_limit(0);

$require_login = true;
$require_current_course = true;
require_once '../../include/baseTheme.php';
require_once 'classes/H5PHubUpdater.php';

$backUrl = $urlAppend . 'modules/h5p/index.php?course=' . $course_code;

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => $backUrl,
        'icon' => 'fa-reply',
        'level' => 'primary')
), false);

$toolName = $langMaj;
$navigation[] = ['url' => $backUrl, 'name' => "H5P"];

$hubUpdater = new H5PHubUpdater();
$hubUpdater->fetchLatestContentTypes();

$tool_content .= $langH5pUpdateComplete;

draw($tool_content, 2);
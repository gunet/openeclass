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

$require_current_course = true;
require_once '../../include/baseTheme.php';

$backUrl = $urlAppend . 'modules/h5p/?course=' . $course_code;

$tool_content .= action_bar([[
    'title' => $langBack,
    'url' => $backUrl,
    'icon' => 'fa-reply',
    'level' => 'primary-label'
]], false);

$can_upload = TRUE;
$upload_target_url = 'courses/temp/h5p';

$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => $langH5p];

$tool_content .= "<div class='alert alert-info'>$langImportH5P</div>";

$tool_content .= "<div class='row'>
    <div class='col-md-12'>
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='save.php' method='post' enctype='multipart/form-data'>
                <div class='form-group'>
                    <label for='userFile' class='col-sm-2 control-label'>$langPathUploadFile:</label>
                    <div class='col-sm-10'>
                        <input type='file' id='userFile' name='userFile'>
                        <span class='help-block' style='margin-bottom: 0px;'><small>$langMaxFileSize " . ini_get('upload_max_filesize') . "</small></span>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-offset-2 col-sm-10'>
                        <button class='btn btn-primary' type='submit'>$langUpload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>";

draw($tool_content, 2);

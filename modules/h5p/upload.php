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

$backUrl = $urlAppend . 'modules/h5p/index.php?course=' . $course_code;

$tool_content .= action_bar([[
    'title' => $langBack,
    'url' => $backUrl,
    'icon' => 'fa-reply',
    'level' => 'primary'
]], false);

$can_upload = TRUE;
$upload_target_url = 'courses/temp/h5p';

$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => $langH5p];

$tool_content .= "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langImportH5P</span></div></div>";

$tool_content .= "
    <div class='col-sm-12'>
        <div class='form-wrapper form-edit rounded'>
            <form class='form-horizontal' role='form' action='save.php' method='post' enctype='multipart/form-data'>
                <div class='form-group'>
                    <label for='userFile' class='col-sm-6 control-label-notes'>$langPathUploadFile</label>
                    <div class='col-sm-12'>
                        <input type='file' id='userFile' name='userFile'>
                        <div class='infotext col-12 margin-bottom-fat TextSemiBold Neutral-900-cl mt-4'>
                            $langMaxFileSize " . ini_get('upload_max_filesize') . "
                        </div>
                    </div>
                </div>
                <div class='form-group mt-5'>
                    <div class='col-12 d-flex justify-content-center align-items-center'>
                        <button class='btn submitAdminBtn' type='submit'>$langUpload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>";

draw($tool_content, 2);

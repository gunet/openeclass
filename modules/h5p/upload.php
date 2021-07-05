<?php

$require_current_course = true;

require_once '../../include/baseTheme.php';

$backUrl = $urlAppend . 'modules/h5p/?course=' . $course_code;

$tool_content .= action_bar([
            [ 'title' => $langBack,
              'url' => $backUrl,
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);

$can_upload = TRUE;
$upload_target_url = 'courses/temp/h5p';

$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => "H5P"];

$tool_content .= "<div class='row'>
    <div class='col-md-12'>
        <div class='form-wrapper'>
        	<form class='form-horizontal' role='form' action='save.php' method='post' enctype='multipart/form-data'>
        		<label for='userFile' class='col-sm-2 control-label'>$langPathUploadFile : </label>
                                <div class='col-sm-10'>
                                    <input type='file' id='userFile' name='userFile'>
                                </div>
        		<button class='btn btn-primary' type='submit'>$langUpload</button>
        	</form>
        </div>
    </div>
</div>";

draw($tool_content, 2);

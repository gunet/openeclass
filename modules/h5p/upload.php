<?php

$require_current_course = true;

require_once '../../include/baseTheme.php';

$data = [];
$backUrl = $urlAppend . 'modules/h5p/?course=' . $course_code;

$data['action_bar'] = action_bar([[
    'title' => $langBack,
    'url' => $backUrl,
    'icon' => 'fa-reply',
    'level' => 'primary-label'
]], false);
$data['can_upload'] = TRUE;
$data['upload_target_url'] = 'courses/temp/h5p';
$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => $langH5P];

view('modules.h5p.upload', $data);
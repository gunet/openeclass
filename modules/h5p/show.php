<?php

$require_current_course = true;

require_once '../../include/baseTheme.php';

$data = [];
$backUrl = $urlAppend . 'modules/h5p/?course=' . $course_code;

$data['action_bar'] = action_bar([
            [ 'title' => $langBack,
              'url' => $backUrl,
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);
		
$toolName = $langImport;
$navigation[] = ['url' => $backUrl, 'name' => $langH5P];
$data['workspaceUrl'] = $urlAppend . 'courses/' . $course_code . '/h5p/workspace1';
view('modules.h5p.show', $data);
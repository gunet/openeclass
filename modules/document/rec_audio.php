<?php

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

$data['backButton'] = action_bar(array(
    array('title' => $langBack,
        'url' => "index.php?course=$course_code",
        'icon' => 'fa-reply',
        'level' => 'primary-label'
    )));

view('modules.document.rec_audio', $data);

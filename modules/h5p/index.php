<?php

$require_current_course = true;

require_once '../../include/baseTheme.php';

$data['content'] = Database::get()->queryArray("SELECT * FROM h5p_content WHERE course_id = ?d ORDER BY id ASC", $course_id);

$data['action_bar'] = action_bar(array(
    array('title' => $langMaj,
        'url' => "update.php?course=" . $course_code,
        'icon' => 'fa-refresh',
        'level' => 'primary-label',
        'button-class' => 'btn-success'),
    array('title' => $langImport,
        'url' => "upload.php?course=" . $course_code,
        'icon' => 'fa-upload',
        'level' => 'primary-label',
        'button-class' => 'btn-success')
), false);

$toolName = $langH5P;
view('modules.h5p.index', $data);
<?php

$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'classes/H5PFactory.php';

$toolName = $langH5P;
$factory = new H5PFactory();
$editorAjax = $factory->getH5PEditorAjax();

$data['content'] = Database::get()->queryArray("SELECT * FROM h5p_content WHERE course_id = ?d ORDER BY id ASC", $course_id);
$data['h5pcontenttypes'] = $editorAjax->getLatestLibraryVersions();
$data['webDir'] = $webDir;

view('modules.h5p.index', $data);
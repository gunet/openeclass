<?php

$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'classes/H5PFactory.php';

$toolName = $langH5P;
$factory = new H5PFactory();
$editorAjax = $factory->getH5PEditorAjax();

// Control Flags
if (isset($_GET['choice']) && isset($_GET['id'])) {
    switch($_GET['choice']) {
        case 'do_disable':
            Database::get()->querySingle("UPDATE h5p_content set enabled = 0 WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
            Session::Messages($langH5pSaveSuccess, 'alert-success');
            redirect_to_home_page("modules/h5p/index.php?course=$course_code");
            break;
        case 'do_enable':
            Database::get()->querySingle("UPDATE h5p_content set enabled = 1 WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
            Session::Messages($langH5pSaveSuccess, 'alert-success');
            redirect_to_home_page("modules/h5p/index.php?course=$course_code");
            break;
        case 'do_reuse_disable':
            Database::get()->querySingle("UPDATE h5p_content set reuse_enabled = 0 WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
            Session::Messages($langH5pSaveSuccess, 'alert-success');
            redirect_to_home_page("modules/h5p/index.php?course=$course_code");
            break;
        case 'do_reuse_enable':
            Database::get()->querySingle("UPDATE h5p_content set reuse_enabled = 1 WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
            Session::Messages($langH5pSaveSuccess, 'alert-success');
            redirect_to_home_page("modules/h5p/index.php?course=$course_code");
            break;
    }
}

$onlyEnabledWhere = ($is_editor) ? '' : " AND enabled = 1 ";
$data['content'] = Database::get()->queryArray("SELECT * FROM h5p_content WHERE course_id = ?d $onlyEnabledWhere ORDER BY id ASC", $course_id);
$data['h5pcontenttypes'] = $editorAjax->getLatestLibraryVersions();
$data['webDir'] = $webDir;

view('modules.h5p.index', $data);

<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
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
 * ========================================================================
 */

// Allow unlimited time for creating the archive
set_time_limit(0);
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/textLib.inc.php'; // textLib has functions required by templates
require_once 'backport_functions.php'; // backported functions from eclass default branch
require_once 'override_functions.php'; // overridden functions
require_once 'offline_functions.php';

$viewsDir = $webDir . '/modules/offline/views';
$cacheDir = $webDir . '/courses/'. $course_code . '/temp/' . safe_filename();
$downloadDir = $webDir . '/courses/'. $course_code . '/temp/' . safe_filename();
mkdir($cacheDir);
mkdir($downloadDir);
$dload_filename = $webDir . '/courses/' . $course_code . '/temp/' . safe_filename('zip');
$real_filename = remove_filename_unsafe_chars($public_code . '-offline.zip');

/////////////////////////////
// generic and course data //
/////////////////////////////
$data = [
    'urlAppend' => './',
    'template_base' => './template/default',
    'themeimg' => './template/default/img',
    'logo_img' => './template/default/img/eclass-new-logo.png',
    'logo_img_small' => './template/default/img/logo_eclass_small.png',
    'is_mobile' => false,
    'eclass_version' => ECLASS_VERSION,
    'course_info_popover' => null,
    'lessonStatus' => null,
    'departments' => null,
    'alter_layout' => null,
    'course_units' => null,
    'course_home_main_area_widgets' => null,
    'cunits_sidebar_columns' => null,
    'cunits_sidebar_subcolumns' => null,
    'user_personal_calendar' => null,
    'course_home_sidebar_widgets' => null,
    'course_descriptions_modals' => null,
    'pageTitle' => null,
    'container' => null,
    'uname' => null,
    'menuTypeID' => null,
    'require_help' => null,
    'show_toggle_student_view' => null,
    'messages' => null,
    'search_action' => null,
    'current_module_dir' => null,
    'sidebar_courses' => null,
    'actionBar' => null,
    'dialogBox' => null,
    'curDirName' => null,
    'curDirPath' => null,
    'downloadPath' => null,
    'is_in_tinymce' => false,
    'can_upload' => false,
];

$data['course_info'] = Database::get()->querySingle("SELECT keywords, visible, prof_names, public_code, course_license, finish_date,
                                               view_type, start_date, finish_date, description, home_layout, course_image, password
                                          FROM course WHERE id = ?d", $course_id);
$data['numUsers'] = Database::get()->querySingle("SELECT COUNT(user_id) AS numUsers
                FROM course_user
                WHERE course_id = ?d", $course_id)->numUsers;
$section_title = $currentCourseName;
$toolArr = lessonToolsMenu_offline(true, $data['urlAppend']);
$global_data = compact('is_editor', 'course_code', 'course_id', 'language',
    'pageTitle', 'urlAppend', 'urlServer', 'eclass_version', 'template_base', 'toolName',
    'container', 'uid', 'uname', 'is_embedonce', 'session', 'nextParam',
    'require_help', 'helpTopic', 'helpSubTopic', 'head_content', 'toolArr', 'module_id',
    'module_visibility', 'professor', 'pageName', 'menuTypeID', 'section_title',
    'messages', 'logo_img', 'logo_img_small', 'styles_str', 'breadcrumbs',
    'is_mobile', 'current_module_dir','search_action', 'require_current_course',
    'saved_is_editor', 'require_course_admin', 'is_course_admin', 'require_editor', 'sidebar_courses',
    'show_toggle_student_view', 'themeimg', 'currentCourseName');
$bladeData = array_merge($global_data, $data);


use Philo\Blade\Blade;
$blade = new Blade($viewsDir, $cacheDir);

/////////////////
// course home //
/////////////////
$homeout = $blade->view()->make('modules.course.home.index', $bladeData)->render();
$fp = fopen($downloadDir . '/index.html', 'w');
fwrite($fp, $homeout);
fclose($fp);

/////////////
// modules //
/////////////
mkdir($downloadDir . '/modules');
$bladeData['urlAppend'] = '../../';
$bladeData['template_base'] = $bladeData['urlAppend'] . 'template/default';
$bladeData['themeimg'] = $bladeData['urlAppend'] . 'template/default/img';
$bladeData['logo_img'] = $bladeData['themeimg'] . '/eclass-new-logo.png';
$bladeData['logo_img_small'] = $bladeData['themeimg'] . '/logo_eclass_small.png';
$bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);

///////////////
// documents //
///////////////
offline_documents('', '', $bladeData);

/////////////
// statics //
/////////////
copyDirTo($webDir . "/template", $downloadDir);
copyDirTo($webDir . "/js", $downloadDir);

/////////
// zip //
/////////
zip_offline_directory($dload_filename, $downloadDir);
claro_delete_file($cacheDir);
claro_delete_file($downloadDir);
send_file_to_client($dload_filename, $real_filename, null, true, true);
exit();

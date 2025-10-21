<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

// Allow unlimited time for creating the archive
set_time_limit(0);
ini_set('display_errors', '1');
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'modules/exercise/exercise.class.php';
require_once 'modules/exercise/question.class.php';
require_once 'modules/exercise/answer.class.php';
require_once 'modules/exercise/exercise.lib.php';
require_once 'include/course_settings.php';
require_once 'override_functions.php'; // overridden functions
require_once 'offline_functions.php';
require_once 'offline_imscp.php';

// security check
$offline_course = get_config('offline_course') && (setting_get(SETTING_OFFLINE_COURSE, $course_id));
if (!$offline_course) {
    Session::flash('message',$langForbidden);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page('');
}

$tree = new Hierarchy();
$course = new Course();



/************************************ */
/* ViewDir for modern offline course */
/*********************************** */
$viewsDir = $webDir . '/resources/views/offline';
/************************************* */
/************************************* */

$cacheDir = $webDir . '/courses/'. $course_code . '/temp/' . safe_filename();
$downloadDir = $webDir . '/courses/'. $course_code . '/temp/' . safe_filename();
mkdir($cacheDir);
mkdir($downloadDir);
$dload_filename = $webDir . '/courses/' . $course_code . '/temp/' . safe_filename('zip');
$real_filename = remove_filename_unsafe_chars($public_code . '-offline.zip');

/////////////////////////////
// generic and course data //
/////////////////////////////
$logo_img = "./resources/img/eclass-new-logo.svg";
$logo_img_small = "./resources/img/eclass-new-logo.svg";

$theme_data = get_theme_options();

$styles_str = $theme_data['styles'];
if (!empty($theme_data['logo_img'])) {
    $logo_img = $theme_data['logo_img'];
}
if (!empty($theme_data['logo_img_small'])) {
    $logo_img_small = $theme_data['logo_img_small'];
}

$data = [
    'breadcrumbs' => '',
    'urlAppend' => './',
    'is_mobile' => false,
    'eclass_version' => ECLASS_VERSION,
    'jquery_version' => JQUERY_VERSION,
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
    'full_description' => '',
    'truncated_text' => '',
    'default_open_group' => 0
];

$data['course_info'] = $course_info = Database::get()->querySingle("SELECT title, keywords, visible, prof_names, public_code, course_license,
                                               view_type, start_date, end_date, description, home_layout, course_image, password, is_collaborative
                                          FROM course WHERE id = ?d", $course_id);
if ($course_info->description) {
    $description = preg_replace('|src=(["\']).*/modules/document/file.php?'.$course_code.'/(.*)\1|','src=\1modules/document/\2\1', standard_text_escape($course_info->description));
    // Text button for read more & read less
    $postfix_truncate_more = "<a href='#' class='more_less_btn'>$langReadMore &nbsp;<span class='fa fa-arrow-down'></span></a>";
    $postfix_truncate_less = "<a href='#' class='more_less_btn'>$langReadLess &nbsp;<span class='fa fa-arrow-up'></span></a>";

    // Create full description text & truncated text
    $data['full_description'] = $description.$postfix_truncate_less;
    $data['truncated_text'] = ellipsize_html($description, 1000, $postfix_truncate_more);
}

$departments = $course->getDepartmentIds($course_id);
$i = 1;
foreach ($departments as $dep) {
    $br = ($i < count($departments)) ? '<br>' : '';
    $data['departments'] .= $tree->getFullPath($dep) . $br;
    $i++;
}

$section_title = $currentCourseName;
$toolArr = lessonToolsMenu_offline(true, $data['urlAppend']);
$global_data = compact('is_editor', 'course_code', 'course_id', 'language',
    'urlServer', 'toolName',
    'uid', 'session', 'head_content', 'toolArr', 'module_id',
    'pageName', 'section_title', 'logo_img', 'logo_img_small', 'styles_str',
    'require_current_course', 'is_course_admin',
    'currentCourseName','collaboration_platform', 'collaboration_value', 'is_collaborative_course');
$bladeData = array_merge($global_data, $data);
$bladeData['pageTitle'] = $course_info->title;
$bladeData['professor'] = $course_info->prof_names;
$bladeData['course_date'] = format_locale_date(time());

use Jenssegers\Blade\Blade;
use Jenssegers\Blade\Container;

$blade = new Blade($viewsDir, $cacheDir, Container::getInstance());

// course units
$bladeData['course_units'] = $course_units = offline_course_units();
/////////////////
// course home //
/////////////////
$homeout = $blade->make('modules.course.home.index', $bladeData)->render();
$fp = fopen($downloadDir . '/index.html', 'w');
fwrite($fp, $homeout);
fclose($fp);

/////////////
// modules //
/////////////
mkdir($downloadDir . '/modules');
$bladeData['lessonStatus'] = '../../';
$bladeData['urlAppend'] = '../';
$bladeData['template_base'] = $bladeData['urlAppend'] . 'template/modern';
$bladeData['themeimg'] = $bladeData['urlAppend'] . 'resources/img';

if (!empty($theme_data['logo_img'])) {
    $bladeData['logo_img'] = $bladeData['urlAppend'] . $theme_data['logo_img'];
} else {
    $bladeData['logo_img'] = $bladeData['themeimg'] . '/eclass-new-logo.svg';
}
if (!empty($theme_data['logo_img_small'])) {
    $bladeData['logo_img_small'] = $bladeData['urlAppend'] . $theme_data['logo_img_small'];
} else {
    $bladeData['logo_img_small'] = $bladeData['themeimg'] . '/eclass-new-logo.svg';
}

$bladeData['toolArr'] = lessonToolsMenu_offline(true, $bladeData['urlAppend']);

////////////////
// unit resources
////////////////
offline_unit_resources($bladeData, $downloadDir);
///////////////
// documents //
///////////////
offline_documents('', 'document', 'document', $bladeData);
////////////////
// announcements
////////////////
offline_announcements($bladeData);
////////////////
// video
////////////////
offline_videos($bladeData);
///////////////
// glossary
///////////////
if((isset($is_collaborative_course) and !$is_collaborative_course) or is_null($is_collaborative_course)){
    offline_glossary($bladeData, $downloadDir);
}
//////////////
// links
//////////////
offline_links($bladeData, $downloadDir);
/////////////
// description
/////////////
offline_description($bladeData, $downloadDir);

///////////////////
// not implemented yet
if((isset($is_collaborative_course) and !$is_collaborative_course) or is_null($is_collaborative_course)){
    offline_exercises($bladeData);
    offline_ebook($bladeData);
}
offline_agenda($bladeData);
if((isset($is_collaborative_course) and !$is_collaborative_course) or is_null($is_collaborative_course)){
    offline_blog($bladeData);
    offline_wiki($bladeData);
}
//////////////////////////

/////////////
// statics //
/////////////
copyDirTo($webDir . "/template", $downloadDir);
copyDirTo($webDir . "/courses/theme_data", $downloadDir);
copyDirTo($webDir . "/js", $downloadDir);
copy($webDir . '/modules/learnPath/export/imscp_v1p2.xsd', $downloadDir . '/imscp_v1p2.xsd');
offline_create_manifest($downloadDir);

/////////
// zip //
/////////
zip_offline_directory($dload_filename, $downloadDir);
claro_delete_file($cacheDir);
claro_delete_file($downloadDir);
send_file_to_client($dload_filename, $real_filename, null, true, true);
exit();

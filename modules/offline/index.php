<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
//ini_set('display_errors', '1');
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/textLib.inc.php'; // textLib has functions required by templates
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
    Session::Messages($langForbidden, 'aleρτ-warning');
    redirect_to_home_page('');
}

$tree = new Hierarchy();
$course = new Course();

$viewsDir = $webDir . '/resources/views/offline';
$cacheDir = $webDir . '/courses/'. $course_code . '/temp/' . safe_filename();
$downloadDir = $webDir . '/courses/'. $course_code . '/temp/' . safe_filename();
mkdir($cacheDir);
mkdir($downloadDir);
$dload_filename = $webDir . '/courses/' . $course_code . '/temp/' . safe_filename('zip');
$real_filename = remove_filename_unsafe_chars($public_code . '-offline.zip');

/////////////////////////////
// generic and course data //
/////////////////////////////
$logo_img = "./template/default/img/eclass-new-logo.png";
$logo_img_small = "./template/default/img/logo_eclass_small.png";

$theme_data = get_theme_options();

$styles_str = $theme_data['styles'];
if (!empty($theme_data['logo_img'])) {
    $logo_img = $theme_data['logo_img'];
}
if (!empty($theme_data['logo_img_small'])) {
    $logo_img_small = $theme_data['logo_img_small'];
}

$data = [
    'urlAppend' => './',
    'template_base' => './template/default',
    'themeimg' => './template/default/img',
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
    'full_description' => '',
    'truncated_text' => ''
];

$data['course_info'] = $course_info = Database::get()->querySingle("SELECT title, keywords, visible, prof_names, public_code, course_license, finish_date,
                                               view_type, start_date, finish_date, description, home_layout, course_image, password
                                          FROM course WHERE id = ?d", $course_id);
if ($course_info->description) {
    $description = preg_replace('|src=(["\']).*/modules/document/file.php/'.$course_code.'/(.*)\1|','src=\1modules/document/\2\1', standard_text_escape($course_info->description));
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
$global_data = @compact('is_editor', 'course_code', 'course_id', 'language',
    'pageTitle', 'urlAppend', 'urlServer', 'eclass_version', 'template_base', 'toolName',
    'container', 'uid', 'uname', 'is_embedonce', 'session', 'nextParam',
    'require_help', 'helpTopic', 'helpSubTopic', 'head_content', 'toolArr', 'module_id',
    'module_visibility', 'professor', 'pageName', 'menuTypeID', 'section_title',
    'messages', 'breadcrumbs', 'logo_img', 'logo_img_small', 'styles_str',
    'is_mobile', 'current_module_dir','search_action', 'require_current_course',
    'saved_is_editor', 'require_course_admin', 'is_course_admin', 'require_editor', 'sidebar_courses',
    'show_toggle_student_view', 'themeimg', 'currentCourseName');
$bladeData = array_merge($global_data, $data);
$bladeData['pageTitle'] = $course_info->title;
$bladeData['professor'] = $course_info->prof_names;
$bladeData['course_date'] = claro_format_locale_date($dateTimeFormatLong, strtotime('now'));

use Jenssegers\Blade\Blade;
$blade = new Blade($viewsDir, $cacheDir);

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
$bladeData['template_base'] = $bladeData['urlAppend'] . 'template/default';
$bladeData['themeimg'] = $bladeData['urlAppend'] . 'template/default/img';

if (!empty($theme_data['logo_img'])) {
    $bladeData['logo_img'] = $bladeData['urlAppend'] . $theme_data['logo_img'];
} else {
    $bladeData['logo_img'] = $bladeData['themeimg'] . '/eclass-new-logo.png';
}
if (!empty($theme_data['logo_img_small'])) {
    $bladeData['logo_img_small'] = $bladeData['urlAppend'] . $theme_data['logo_img_small'];
} else {
    $bladeData['logo_img_small'] = $bladeData['themeimg'] . '/logo_eclass_small.png';
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
offline_glossary($bladeData, $downloadDir);
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
offline_exercises($bladeData);
offline_ebook($bladeData);
offline_agenda($bladeData);
offline_blog($bladeData);
offline_wiki($bladeData);
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

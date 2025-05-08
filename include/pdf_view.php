<?php
/* ========================================================================
 * Open eClass 4.1
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2025  Greek Universities Network - GUnet
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
 * ======================================================================== */


/**
 * @brief Outputs a PDF file based on an HTML view using Mpdf
 * @param $view_file
 * @param array $view_data
 * @param string $filename
 * @param boolean $download If true, file is downloaded, if false, shown inline
 */
function pdf_view($view_file, $view_data = [], $filename = 'output.pdf', $download = true) {
    global $webDir, $course_code, $course_id, $head_content, $language, $siteName,
        $urlAppend, $uid, $urlServer, $theme, $pageName, $currentCourseName,
        $session, $professor, $toolName, $themeimg, $is_enabled_collaboration,
        $collaboration_platform, $collaboration_value, $courseLicense,
        $eclass_banner_value, $is_admin, $is_editor, $is_collaborative_course,
        $is_consultant, $is_coordinator, $is_course_admin, $is_course_reviewer,
        $is_departmentmanage_user, $is_power_user, $is_lti_enrol_user,
        $is_simple_user, $is_usermanage_user, $leftsideImg, $logo_img,
        $logo_img_small, $image_footer, $favicon_img, $theme_css, $theme_id;

    if (!isset($course_id) or !$course_id or $course_id < 1) {
        $course_id = $course_code = null;
    }

    $pageTitle = $siteName;

    $template_base = $urlAppend . 'template/' . $theme;
    if (isset($_SESSION['uname'])) {
        $uname = $_SESSION['uname'];
    }

    if (!$toolName and $pageName) {
        $toolName = $pageName;
    } elseif (!$pageName and $toolName) {
        $pageName = $toolName;
    }

    //Get the Current Module ID
    if ($is_editor and isset($course_code)) {
        $module_id = current_module_id();
        if (display_activation_link($module_id)) {
            $module_visibility = visible_module($module_id);
        } else {
            $module_visibility = false;
        }
    }

    if (!isset($uname)) {
        $uname = null;
    }

    $logo_url_path = $urlAppend;
    if ($is_lti_enrol_user) {
        $uname = q($_SESSION['givenname'] . " " . $_SESSION['surname']);
    }

    $views = $webDir . '/resources/views/';
    $cacheDir = $webDir . '/storage/views/';

    $blade = new Blade($views, $cacheDir);

    $global_data = compact('is_editor', 'is_course_reviewer', 'course_code',
        'course_id', 'language', 'uid', 'uname', 'session', 'head_content',
        'pageTitle', 'webDir', 'urlAppend', 'urlServer', 'template_base',
        'toolName', 'professor', 'pageName', 'logo_img', 'logo_img_small',
        'is_course_admin', 'themeimg', 'currentCourseName', 'is_admin',
        'is_power_user', 'is_usermanage_user', 'is_departmentmanage_user',
        'is_lti_enrol_user', 'logo_url_path', 'leftsideImg',
        'eclass_banner_value', 'courseLicense', 'image_footer', 'favicon_img',
        'collaboration_platform', 'collaboration_value',
        'is_enabled_collaboration', 'is_collaborative_course', 'is_consultant',
        'is_coordinator', 'is_simple_user', 'theme_css', 'theme_id');
    $data = array_merge($global_data, $view_data);

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'tempDir' => _MPDF_TEMP_PATH,
        'fontDir' => array_merge($fontDirs, [ $webDir . '/template/modern/fonts' ]),
        'fontdata' => $fontData + [
            'opensans' => [
                'R' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-regular.ttf',
                'B' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700.ttf',
                'I' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-italic.ttf',
                'BI' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700italic.ttf'
            ],
            'roboto' => [
                'R' => 'roboto-v15-latin_greek_cyrillic_greek-ext-regular.ttf',
                'I' => 'roboto-v15-latin_greek_cyrillic_greek-ext-italic.ttf',
            ]
        ]
    ]);

    $mpdf->setFooter('{DATE j-n-Y} || {PAGENO} / {nb}');
    $creator = $course_id? course_id_to_prof($course_id): $siteName;
    $mpdf->SetCreator($creator);
    $mpdf->SetAuthor($creator);
    $mpdf->WriteHTML($blade->make($view_file, $data)->render());
    $mpdf->Output($filename, $download? 'D': 'I'); // Download / Inline display
    exit;
}

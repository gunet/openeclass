<?php
/* ========================================================================
 * Open eClass 4.0
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
 * ======================================================================== */

/*
 * Base Theme Component, eClass Core
 *
 * @abstract This component is the core of eclass. Each and every file that
 * needs to output to the user's browser must include this file and use
 * the view() or draw() calls to output the UI to the user's browser.
 *
 *
 */
use Jenssegers\Blade\Blade;
$navigation = array();
$action_bar = '';
$sectionName = '';
$pageName = '';
$toolName = '';
require_once 'init.php';

if (isset($toolContent_ErrorExists)) {
    Session::flash('message',$toolContent_ErrorExists);
    Session::flash('alert-class', 'alert-warning');
    if (!$uid) {
        $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI'] ?? '');
        header("Location:" . $urlServer . "main/login_form.php?next=" . urlencode($next));
    } elseif ($_SESSION['status'] == USER_GUEST) {
        redirect_to_home_page();
    } else {
        redirect_to_home_page("main/portfolio.php");
    }
    exit();
}

require_once 'tools.php';

/**
 * @brief draws html content
 * @param $view_file
 * @param array $view_data
 */
function view($view_file, $view_data = array()) {
    global $webDir, $is_editor, $is_course_reviewer, $course_code, $course_id, $language, $siteName,
            $urlAppend, $urlServer, $theme, $pageName, $currentCourseName, $uid, $session,
            $require_help, $professor, $helpTopic, $helpSubTopic, $head_content, $toolName, $themeimg, $navigation,
            $require_current_course, $saved_is_editor, $require_course_admin, $is_course_admin,
            $require_editor, $langHomePage, $is_in_tinymce, $action_bar,
            $is_admin, $is_power_user, $is_departmentmanage_user, $is_usermanage_user, $leftsideImg,
            $courseLicense, $loginIMG, $authCase, $authNameEnabled, $pinned_announce_id, $pinned_announce_title, $pinned_announce_body,
            $collaboration_platform, $collaboration_value, $is_enabled_collaboration, $is_collaborative_course,
            $is_consultant, $require_consultant, $is_coordinator, $is_simple_user,
            $container, $logo_img, $logo_img_small, $eclass_banner_value, $PositionFormLogin,  $image_footer,
            $favicon_img, $theme_css, $theme_id, $langClose, $VideoUploadedInJumbotron;

    if (!isset($course_id) or !$course_id or $course_id < 1) {
        $course_id = $course_code = null;
    }

    if (!isset($_SESSION['provider'])) {
        $_SESSION['provider'] = null;
    }
    $pageTitle = $siteName;
    $is_mobile = (isset($_SESSION['mobile']) && $_SESSION['mobile'] == true);

    $toolArr = [];
    if (isset($course_id) and !$is_mobile) {
        $toolArr = lessonToolsMenu();
    }
    if ($is_in_tinymce) {
        $toolArr = pickerMenu();
    }

    $is_embedonce = (isset($_SESSION['embedonce']) && $_SESSION['embedonce'] == true);
    unset($_SESSION['embedonce']);

    $current_module_dir = module_path($_SERVER['REQUEST_URI']);
    $default_open_group = 0; // Open first tool group by default

    foreach ($toolArr as $tool_group_id => $tool_group) {
        if (in_array($current_module_dir, array_map('module_path', $tool_group[2]))) {
            $default_open_group = $tool_group_id;
        }
    }


    $eclass_version = ECLASS_VERSION;
    $template_base = $urlAppend . 'template/' . $theme;
    if (isset($_SESSION['uname'])) {
        $uname = $_SESSION['uname'];
    }

    if (!$toolName and $pageName) {
        $toolName = $pageName;
    } elseif (!$pageName and $toolName) {
        $pageName = $toolName;
    }

    // breadcrumb and page title
    $breadcrumbs = array();
    if (!$is_embedonce and !$is_mobile and $current_module_dir != '/') {
        // Breadcrumb landing page link
        $homepageSet = get_config('homepage');
        $showStart = true;
        if ($homepageSet == 'external' and ($landingUrl = get_config('landing_url'))) {
            $landingPageName = get_config('landing_name');
            if (!$landingPageName) {
                $landingPageName = trans('langHomePage');
            }
            $item['bread_text'] = $landingPageName;
            $item['bread_href'] = $landingUrl;
            $breadcrumbs[] = $item;
            unset($item);
        } elseif ($homepageSet == 'toolbox') {
            $item['bread_text'] = get_config('toolbox_name', $langHomePage);
            $item['bread_href'] = $urlAppend . 'main/toolbox.php';
            $breadcrumbs[] = $item;
            unset($item);
        }

        // Breadcrumb first entry (home / portfolio)
        if ($session->status != USER_GUEST && $_SESSION['provider'] !== 'lti_publish') {
            if (isset($_SESSION['uid'])) {
                $item['bread_text'] = trans('langPortfolio');
                if (isset($require_current_course) or $pageName) {
                    $item['bread_href'] = $urlAppend . 'main/portfolio.php';
                }
            } else {
                $homebreadcrumb = get_config('homepage_name');
                if (isset($homebreadcrumb)) {
                    $item['bread_text'] = $homebreadcrumb;
                } else {
                    $item['bread_text'] = trans('langHomePage');
                }
                $showStart = true;
            }

            if ($showStart) {
                if (isset($require_current_course) or $pageName) {
                    $item['bread_href'] = $urlAppend;
                }
            }
            $breadcrumbs[] = $item;
            unset($item);
        }

        // Breadcrumb course home entry
        if (isset($course_code)) {
            $item['bread_text'] = ellipsize($currentCourseName, 48);
            if ($pageName) {
                $item['bread_href'] = $urlAppend . 'courses/' . $course_code . '/';
            }
            $pageTitle .= " | " . ellipsize($currentCourseName, 32);
            $breadcrumbs[] = $item;
            unset($item);
        }
        foreach ($navigation as $step) {
            $item['bread_text'] = $step['name'];
            if (isset($step['url'])) {
                $item['bread_href'] = $step['url'];
            }
            $breadcrumbs[] = $item;
            unset($item);
        }
        if ($pageName) {
            $item['bread_text'] = $pageName;
            $pageTitle .= " | " . $pageName;
            $breadcrumbs[] = $item;
        }
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

    //Construct the after login redirect url
    $nextParam = '';
    if (!$uid) {
        if (!get_config('hide_login_link')) {
            $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
            if (preg_match('@(?:^/(?:modules|courses)|listfaculte|opencourses|openfaculties)@', $next)) {
                $nextParam = '?next=' . urlencode($next);
            }
        }
    }

    // if $require_help is true (set by each tool) display the help link
    if ($require_help) {
        $head_content .= "
        <script>
            $(function() {
                $('#help-btn').click(function(e) {
                    e.preventDefault();
                    $.get($(this).attr(\"href\"), function(data) {
                        bootbox.alert({
                            size: 'large',
                            backdrop: true,
                            message: data,
                            buttons: {
                                ok: {
                                    label: '". js_escape($langClose). "',
                                    className: 'submitAdminBtnDefault'
                                }
                            }
                        });
                    });
                });
            });
        </script>
        ";
    }

    //Check if auth refers to cas or shibboleth and others available auth_ids are disabled.
    $authCase = 0;
    $authNameEnabled = '';
    $aC = database::get()->queryArray("SELECT * FROM auth WHERE auth_default > ?d",0);
    if(count($aC) == 1){
        foreach($aC as $a){
            if($a->auth_name == 'shibboleth' or $a->auth_name == 'cas'){
                $authCase = 1;
                $authNameEnabled = $a->auth_name;
            }
        }
    }

    // Get important admin announcement
    if (!defined('UPGRADE')) {
        $pinned_announce_id = 0;
        $pinned_announce_title = '';
        $pinned_announce_body = '';
        $important_announce = Database::get()->queryArray("SELECT * FROM admin_announcement WHERE important = ?d AND visible = ?d",1,1);
        if (count($important_announce) > 0) {
            foreach ($important_announce as $an) {
                $pinned_announce_id = $an->id;
                $pinned_announce_title = $an->title;
                $pinned_announce_body = $an->body;
            }
        }
    }

    $sidebar_courses = Database::get()->queryArray("SELECT id, code, title, prof_names, public_code
        FROM course, course_user
        WHERE course.id = course_id AND course.visible != " . COURSE_INACTIVE . " AND user_id = ?d
        ORDER BY reg_date DESC", $uid);

    $show_toggle_student_view = isset($require_current_course) &&
                                ($is_editor || isset($saved_is_editor) && $saved_is_editor) &&
                                !(isset($require_course_admin) && $require_course_admin) &&
                                !(isset($require_editor) && $require_editor);

    if (!isset($module_id)) {
        $module_id = null;
        $module_visibility = false;
    }

    if (!isset($uname)) {
        $uname = null;
    }

    $logo_url_path = $urlAppend;
    $is_lti_enrol_user = '';
    if ($uid) {
        // customization for LTI enrolled users
        $is_lti_enrol_user = substr($_SESSION['uname'], 0, strlen("enrol_lti_")) === "enrol_lti_";
        if ($is_lti_enrol_user) {
            $uname = q($_SESSION['givenname'] . " " . $_SESSION['surname']);
            $logo_url_path = "#";
        }
    }

    $views = $webDir . '/resources/views/';
    $cacheDir = $webDir . '/storage/views/';

    if (!is_dir($cacheDir)) {
        $tempDir = $cacheDir;
        $cacheDir = null;
        if (mkdir($tempDir, 0755, true)) {
            $cacheDir = $tempDir;
        }
    }
    if (!is_writable($cacheDir) or !$cacheDir) {
        $cacheDir = sys_get_temp_dir() . '/storage';
        if (!(is_dir($cacheDir) or mkdir($cacheDir, 0755, true))) {
            die("Error: Unable to find a writable storage directory - tried '$tempDir', '$cacheDir'.");
        }
    }

    $blade = new Blade($views, $cacheDir);

    $cache_suffix = CACHE_SUFFIX;

    $global_data = compact('is_editor', 'is_course_reviewer', 'course_code', 'course_id', 'language', 'cache_suffix',
            'pageTitle', 'urlAppend', 'urlServer', 'eclass_version', 'template_base', 'toolName',
            'container', 'uid', 'uname', 'is_embedonce', 'session', 'nextParam', 'action_bar',
            'require_help', 'helpTopic', 'helpSubTopic', 'head_content', 'toolArr', 'module_id',
            'module_visibility', 'professor', 'pageName',
            'logo_img', 'logo_img_small', 'breadcrumbs', 'is_mobile', 'current_module_dir', 'require_current_course',
            'saved_is_editor', 'require_course_admin', 'is_course_admin', 'require_editor', 'sidebar_courses',
            'show_toggle_student_view', 'themeimg', 'currentCourseName', 'default_open_group',
            'is_admin', 'is_power_user', 'is_usermanage_user', 'is_departmentmanage_user', 'is_lti_enrol_user',
            'logo_url_path','leftsideImg','eclass_banner_value', 'is_in_tinymce', 'PositionFormLogin',
            'courseLicense', 'loginIMG', 'image_footer', 'authCase', 'authNameEnabled', 'pinned_announce_id',
            'pinned_announce_title', 'pinned_announce_body','favicon_img','collaboration_platform', 'collaboration_value',
            'is_enabled_collaboration', 'is_collaborative_course', 'is_consultant', 'require_consultant', 'is_coordinator',
            'is_simple_user', 'theme_css', 'theme_id', 'VideoUploadedInJumbotron');
    $data = array_merge($global_data, $view_data);

    echo $blade->make($view_file, $data)->render();
}

/**
 * @param $view_file
 * @param array $view_data
 * @return mixed
 */
function widget_view($view_file, $view_data = array()) {
    global $webDir;

    $views = $webDir . "/$view_data[widget_folder]/views/";
    $cache = $webDir . '/storage/views/';
    $blade = new Blade($views, $cache);

    $global_data = [];
    $data = array_merge($global_data, $view_data);
    return $blade->make($view_file, $data)->render();
}


/**
 * @brief Old views. user for compatibility issue.
 * @param mixed $toolContent html code
 * @param string $tool_css (optional) catalog name where a "tool.css" file exists
 * @param string $head_content (optional) code to be added to the HEAD of the UI
 */
function draw($tool_content, $menuTypeID, $tool_css = null, $head_content = null) {
    view('legacy.index', compact('tool_content'));
}

// Simplified draw for pop-ups
function draw_popup($tool_content = '', $head_content = '') {

    $data['tool_content'] = $tool_content;
    $data['head_content'] = $head_content;

    view('legacy.popup', $data);
}

/**
 * Function dumpArray
 *
 * Used for debugging purposes. Dumps array to browser
 * window.
 *
 * @param array $arr
 */
function dumpArray($arr) {
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

/**
 * Function print_a
 *
 * Used for debugging purposes. Dumps array to browser
 * window. Better organisation of arrays than dumpArray
 *
 * @param array $arr
 */
function print_a($TheArray) {

    echo "<table border=1>";
    if (is_object($TheArray)) {
        $TheArray = (array)($TheArray);
    }
    $Keys = array_keys($TheArray);
    foreach ($Keys as $OneKey) {
        echo "<tr>";
        echo "<td bgcolor='yellow'>";
        echo "<b>" . $OneKey . "</b>";
        echo "</td>";
        echo "<td bgcolor='#C4C2A6'>";
        if (is_array($TheArray [$OneKey])) {
            print_a($TheArray [$OneKey]);
        } elseif (is_object($TheArray [$OneKey])) {
            print_a((array)$TheArray [$OneKey]);
        } else {
            echo $TheArray [$OneKey];
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

/**
 * Function array2html
 *
 * Used for debugging purposes. Returns an html string with array keys and values
 * handling multidimensional arrays as well.
 *
 * @param array $arr
 *
 * Returns string $str
 */
function array2html($TheArray) {
    $str = '';
    foreach ($TheArray as $key => $value) {
        if (is_array($value)) {
            $str .= '<li>' . $key . ':<ol>';
            foreach ($value as $item)
                $str .= '<li>' . $item . '</li>';
            $str .= '</ol></li>';
        } else {
            $str .= '<li>' . $key . ': ' . $value . '</li>';
        }
    }
    return $str;
}

/**
 * @brief displays lang selection box
 * @return string|void
 */
function lang_selections_Desktop($idLanguage) {

    global $session, $native_language_names_init, $langSelectedLang;

    if (isset($_SESSION['uid'])) { //ignore language selection for logged-in users
        return;
    }
    if (count($session->active_ui_languages) < 2) {
        return ('&nbsp;');
    }
    $Selected_Language = '';
    foreach ($session->active_ui_languages as $code) {
        if($code == $session->language){
           $Selected_Language = q($native_language_names_init[$code]);
        }
    }
    $lang_select = '<div class="dropdown d-flex justify-content-center align-items-end">
                        <a class="d-flex justify-content-end align-items-center link-selection-language gap-2" href="#" id="'.$idLanguage.'" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="'.$langSelectedLang.'">
                            <span class="d-none d-lg-block">'. $Selected_Language .'</span>
                            <span class="fa-solid fa-earth-europe d-block d-lg-none"></span>
                            <i class="fa-solid fa-chevron-down" role="presentation"></i>
                        </a>
                        <div class="m-0 dropdown-menu dropdown-menu-end contextual-menu p-3" role="menu" aria-labelledby="'.$idLanguage.'">
                            <ul class="list-group list-group-flush">';
                            foreach ($session->active_ui_languages as $code) {
                                $class = ($code == $session->language)? ' class="active"': '';
                                $lang_select .=
                                    "<li role='presentation'$class>
                                        <a class='list-group-item py-3' role='menuitem' tabindex='-1' href='$_SERVER[SCRIPT_NAME]?localize=$code'>
                                            " .q($native_language_names_init[$code]) . "
                                        </a>
                                    </li>";
                            }
            $lang_select .= "</ul>
                        </div>
                    </div>";
    return $lang_select;
}

/*
 * Function lang_select_option
 *
 * Returns the HTML code for the <select> element of the language selection tool
 *
 */

function lang_select_options($name, $onchange_js = '', $default_langcode = false) {
    global $session;

    if ($default_langcode === false) {
        $default_langcode = $session->language;
    }

    return selection($session->native_language_names, $name, $default_langcode, $onchange_js);
}

function is_external_link($link) {
    global $urlServer;
    static $host, $phpMyAdminURL, $phpSysInfoURL;

    if (!isset($host)) {
        $host = parse_url($urlServer);
        $host = $host['host'];
        $phpMyAdminURL = get_config('phpMyAdminURL');
        $phpSysInfoURL = get_config('phpSysInfoURL');
    }

    $info = parse_url($link);
    return (isset($info['host']) and $info['host'] != $host) or
        $link == $phpMyAdminURL or
        $link == $phpSysInfoURL;
}

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
 * requires output to the user's browser must include this file and use
 * the view() or draw() calls to output the UI to the user's browser.
 *
 */
use Philo\Blade\Blade;
$navigation = array();
$sectionName = '';
$pageName = '';
$toolName = '';
require_once 'init.php';

if (isset($toolContent_ErrorExists)) {
    Session::Messages($toolContent_ErrorExists);
    if (!$uid) {
        $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
        header("Location:" . $urlServer . "main/login_form.php?next=" . urlencode($next));
    } else {
        header("Location:" . $urlServer . "index.php");
    }
    exit();
}

require_once 'template/template.inc.php';
require_once 'tools.php';

function view($view_file, $view_data = array()) {
    global $webDir, $is_editor, $course_code, $course_id, $language, $siteName,
    $urlAppend, $urlServer, $theme, $pageName, $currentCourseName, $uid, $session, $toolName,
    $require_help, $professor, $helpTopic, $helpSubTopic, $head_content, $toolName, $themeimg, $navigation,
    $require_current_course, $saved_is_editor, $require_course_admin, $is_course_admin,
    $require_editor, $langHomePage;

    // negative course_id might be set in common documents
    if ($course_id < 1) {
        unset($course_id);
        unset($course_code);
    }

    $pageTitle = $siteName;
    $is_mobile = (isset($_SESSION['mobile']) && $_SESSION['mobile'] == true);

    // Setting $menuTypeID and Getting Side Menu
    $menuTypeID = isset($view_data['menuTypeID']) ? $view_data['menuTypeID'] : 2;

    $toolArr = $is_mobile ? array() : getSideMenu($menuTypeID);

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
    if (isset($GLOBALS['leftNavExtras'])) {
        $eclass_leftnav_extras = $GLOBALS['leftNavExtras'];
    }

    // Check if there are any messages to display
    $messages = Session::getMessages();

    if (!$toolName and $pageName) {
        $toolName = $pageName;
    } elseif (!$pageName and $toolName) {
        $pageName = $toolName;
    }

    // set the text and icon on the third bar (header)
    if ($menuTypeID == 2) {
        $section_title = $currentCourseName;
    } elseif ($menuTypeID == 3) {
        $section_title = trans('langAdmin');
    } elseif ($menuTypeID > 0 and $menuTypeID < 3) {
        $section_title = trans('langUserPortfolio');
    } else {
        $homepagetitle = get_config('homepage_title');
        if (isset($homepagetitle)) {
            $section_title = $homepagetitle;
        } else {
            $section_title = $siteName;
        }
    }

    //set the appropriate search action for the searchBox form
    if ($menuTypeID == 2) {
        $search_action = "search_incourse.php?all=true";
    } else {
        $search_action = "search.php";
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
            array_push($breadcrumbs, $item);
            unset($item);
        } elseif ($homepageSet == 'toolbox') {
            $item['bread_text'] = get_config('toolbox_name', $langHomePage);
            $item['bread_href'] = $urlAppend . 'main/toolbox.php';
            array_push($breadcrumbs, $item);
            unset($item);
        }


        // Breadcrumb first entry (home / portfolio)
        if ($session->status != USER_GUEST) {
            if (isset($_SESSION['uid'])) {
                $item['bread_text'] = trans('langPortfolio');
                if (isset($require_current_course) or $pageName) {
                    $item['bread_href'] = $urlAppend . 'main/portfolio.php';
                }
            } else {
                $hideStart = true;
                $homebreadcrumb = get_config('homepage_name');
                if (isset($homebreadcrumb)) {
                    $item['bread_text'] = trans('homebreadcrumb');
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
            if (!isset($hideStart)) {
                array_push($breadcrumbs, $item);
                unset($item);
            }
        }

        // Breadcrumb course home entry
        if (isset($course_code)) {
            $item['bread_text'] = ellipsize($currentCourseName, 48);
            if ($pageName) {
                $item['bread_href'] = $urlAppend . 'courses/' . $course_code . '/';
            }
            $pageTitle .= " | " . ellipsize($currentCourseName, 32);
            array_push($breadcrumbs, $item);
            unset($item);
        }
        foreach ($navigation as $step) {
            $item['bread_text'] = $step['name'];
            if (isset($step['url'])) {
                $item['bread_href'] = $step['url'];
            }
            array_push($breadcrumbs, $item);
            unset($item);
        }
        if ($pageName) {
            $item['bread_text'] = $pageName;
            $pageTitle .= " | " . $pageName;
            array_push($breadcrumbs, $item);
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
    if ($require_help == true) {
        $head_content .= "
        <script>
        $(function() {
            $('#help-btn').click(function(e) {
                e.preventDefault();
                $.get($(this).attr(\"href\"), function(data) {bootbox.alert(data);});
            });
        });
        </script>
        ";
    }

    // Add Theme Options styles
    $logo_img = $themeimg.'/eclass-new-logo.png';
    $logo_img_small = $themeimg.'/logo_eclass_small.png';
    $container = 'container';
    $theme_id = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : get_config('theme_options_id');
    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);

        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
        $styles_str = '';
        if (!empty($theme_options_styles['bgColor']) || !empty($theme_options_styles['bgImage'])) {
            $background_type = "";
            if (isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'stretch') {
                $background_type .= "background-size: 100% 100%;";
            } elseif(isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'fix') {
                $background_type .= "background-size: 100% 100%;background-attachment: fixed;";
            }
            $bg_image = isset($theme_options_styles['bgImage']) ? " url('$urlThemeData/$theme_options_styles[bgImage]')" : "";
            $bg_color = isset($theme_options_styles['bgColor']) ? $theme_options_styles['bgColor'] : "";
            $styles_str .= "body{background: $bg_color$bg_image;$background_type}";
        }
        $gradient_str = 'radial-gradient(closest-corner at 30% 60%, #009BCF, #025694)';
        if (!empty($theme_options_styles['loginJumbotronBgColor']) && !empty($theme_options_styles['loginJumbotronRadialBgColor'])) $gradient_str = "radial-gradient(closest-corner at 30% 60%, $theme_options_styles[loginJumbotronRadialBgColor], $theme_options_styles[loginJumbotronBgColor])";
        if (isset($theme_options_styles['loginImg'])) $styles_str .= ".jumbotron.jumbotron-login { background-image: url('$urlThemeData/$theme_options_styles[loginImg]'), $gradient_str }";
        if (isset($theme_options_styles['loginImgPlacement']) && $theme_options_styles['loginImgPlacement']=='full-width') {
            $styles_str .= ".jumbotron.jumbotron-login {  background-size: cover, cover; background-position: 0% 0%;}";
        }
        //$styles_str .= ".jumbotron.jumbotron-login {  background-size: 353px, cover; background-position: 10% 60%;}";
        if (isset($theme_options_styles['fluidContainerWidth'])){
            $container = 'container-fluid';
            $styles_str .= ".container-fluid {max-width:$theme_options_styles[fluidContainerWidth]px}";
        }
        if (isset($theme_options_styles['openeclassBanner'])){
             $styles_str .= "#openeclass-banner {display: none;}";
        }
        if (!empty($theme_options_styles['leftNavBgColor'])) {
            $rgba_no_alpha = explode(',', $theme_options_styles['leftNavBgColor']);
            $rgba_no_alpha[3] = "1)";
            $rgba_no_alpha = implode(',', $rgba_no_alpha);

            $styles_str .= "#background-cheat-leftnav, #bgr-cheat-header, #bgr-cheat-footer{background:$theme_options_styles[leftNavBgColor];} @media(max-width: 992px){#leftnav{background:$rgba_no_alpha;}}";
        }
        if (!empty($theme_options_styles['linkColor'])) $styles_str .= "a {color: $theme_options_styles[linkColor];}";
        if (!empty($theme_options_styles['linkHoverColor'])) $styles_str .= "a:hover, a:focus {color: $theme_options_styles[linkHoverColor];}";
        if (!empty($theme_options_styles['leftSubMenuFontColor'])) $styles_str .= "#leftnav .panel a {color: $theme_options_styles[leftSubMenuFontColor];}";
        if (!empty($theme_options_styles['leftSubMenuHoverBgColor'])) $styles_str .= "#leftnav .panel a.list-group-item:hover{background: $theme_options_styles[leftSubMenuHoverBgColor];}";
        if (!empty($theme_options_styles['leftSubMenuHoverFontColor'])) $styles_str .= "#leftnav .panel a.list-group-item:hover{color: $theme_options_styles[leftSubMenuHoverFontColor];}";
        if (!empty($theme_options_styles['leftMenuFontColor'])) $styles_str .= "#leftnav .panel a.parent-menu{color: $theme_options_styles[leftMenuFontColor];}";
        if (!empty($theme_options_styles['leftMenuBgColor'])) $styles_str .= "#leftnav .panel a.parent-menu{background: $theme_options_styles[leftMenuBgColor];}";
        if (!empty($theme_options_styles['leftMenuHoverFontColor'])) $styles_str .= "#leftnav .panel .panel-heading:hover {color: $theme_options_styles[leftMenuHoverFontColor];}";
        if (!empty($theme_options_styles['leftMenuSelectedFontColor'])) $styles_str .= "#leftnav .panel a.parent-menu:not(.collapsed){color: $theme_options_styles[leftMenuSelectedFontColor];}";
        if (isset($theme_options_styles['imageUpload'])) $logo_img =  "$urlThemeData/$theme_options_styles[imageUpload]";
        if (isset($theme_options_styles['imageUploadSmall'])) $logo_img_small = "$urlThemeData/$theme_options_styles[imageUploadSmall]";
    }

    $sidebar_courses = Database::get()->queryArray("SELECT id, code, title, prof_names, public_code
        FROM course, course_user
        WHERE course.id = course_id AND course.visible != " . COURSE_INACTIVE . " AND user_id = ?d
        ORDER BY reg_date DESC", $uid);

    $show_toggle_student_view = isset($require_current_course) &&
                                ($is_editor || isset($saved_is_editor) && $saved_is_editor) &&
                                !(isset($require_course_admin) && $require_course_admin) &&
                                !(isset($require_editor) && $require_editor);

    $views = $webDir.'/resources/views';
    $cache = $webDir . '/storage/views';
    $blade = new Blade($views, $cache);

    $global_data = compact('is_editor', 'course_code', 'course_id', 'language',
            'pageTitle', 'urlAppend', 'urlServer', 'eclass_version', 'template_base', 'toolName',
            'container', 'uid', 'uname', 'is_embedonce', 'session', 'nextParam',
            'require_help', 'helpTopic', 'helpSubTopic', 'head_content', 'toolArr', 'module_id',
            'module_visibility', 'professor', 'pageName', 'menuTypeID', 'section_title',
            'messages', 'logo_img', 'logo_img_small', 'styles_str', 'breadcrumbs',
            'is_mobile', 'current_module_dir','search_action', 'require_current_course',
            'saved_is_editor', 'require_course_admin', 'is_course_admin', 'require_editor', 'sidebar_courses',
            'show_toggle_student_view', 'themeimg', 'currentCourseName', 'default_open_group');
    $data = array_merge($global_data, $view_data);
    echo $blade->view()->make($view_file, $data)->render();
}
function widget_view($view_file, $view_data = array()) {
    global $webDir, $is_editor, $course_code, $course_id, $language, $siteName,
    $urlAppend, $theme, $pageName, $currentCourseName, $uid, $session, $toolName,
    $require_help, $professor, $helpTopic, $head_content, $toolName, $themeimg, $navigation,
    $require_current_course, $saved_is_editor, $require_course_admin, $require_editor;

    $views = $webDir."/$view_data[widget_folder]/views";
    $cache = $webDir . '/storage/views';
    $blade = new Blade($views, $cache);

    $global_data = [];
    $data = array_merge($global_data, $view_data);
    return $blade->view()->make($view_file, $data)->render();
}
/**
 * Function draw
 *
 * This method processes all data to render the display. It is executed by
 * each tool. Is in charge of generating the interface and parse it to the user's browser.
 *
 * @param mixed $toolContent html code
 * @param int $menuTypeID
 * @param string $tool_css (optional) catalog name where a "tool.css" file exists
 * @param string $head_content (optional) code to be added to the HEAD of the UI
 * @param string $body_action (optional) code to be added to the BODY tag
 */
function draw($tool_content, $menuTypeID, $tool_css = null, $head_content = null, $body_action = null, $hideLeftNav = null, $perso_tool_content = null) {
    global $session, $course_code, $course_id, $helpTopic,
        $is_editor, $langActivate, $langNote,
        $langAdmin, $langAdvancedSearch, $langAnonUser, $langChangeLang,
        $langChooseLang, $langDeactivate, $langProfileMenu,
        $langHelp, $langUsageTerms,
        $langHomePage, $langLogin, $langLogout, $langMyPersoAgenda, $langMyAgenda,
        $langMyPersoAnnouncements, $langMyPersoDeadlines,
        $langMyPersoDocs, $langMyPersoForum, $langMyCourses,
        $langPortfolio, $langSearch, $langUser,
        $langUserPortfolio, $langUserHeader, $language,
        $navigation, $pageName, $toolName, $sectionName, $currentCourseName,
        $require_current_course, $require_course_admin, $require_help, $siteName,
        $switchLangURL, $theme, $themeimg, $is_course_admin,
        $toolContent_ErrorExists, $urlAppend, $urlServer,
        $language, $saved_is_editor, $langProfileImage,
        $langStudentViewEnable, $langStudentViewDisable, $langTitle, $langEnterNote, $langFieldsRequ;

    $is_embedonce = (isset($_SESSION['embedonce']) && $_SESSION['embedonce'] == true);
    if ($is_embedonce) {
        unset($_SESSION['embedonce']);
        view('legacy.embed', compact('tool_content', 'menuTypeID', 'perso_tool_content'));
    } else {
        view('legacy.index', compact('tool_content', 'menuTypeID', 'perso_tool_content'));
    }
}

// Simplified draw for pop-ups
function draw_popup() {
    global $theme, $language, $urlAppend, $theme, $pageName, $head_content, $tool_content;

    $t = new Template('template/' . $theme);
    $t->set_file('fh', 'popup.html');
    $t->set_var('LANG', $language);
    $t->set_var('ECLASS_VERSION', ECLASS_VERSION);
    $t->set_var('template_base', $urlAppend . 'template/' . $theme);
    $t->set_var('PAGE_TITLE', $pageName);
    $t->set_var('HEAD_EXTRAS', $head_content);
    $t->set_var('TOOL_CONTENT', $tool_content);
    $t->pparse('Output', 'fh');
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

/*
 * Function lang_selections
 *
 * Returns the HTML code for a language selection tool form
 *
 */

function lang_selections() {
    global $session, $native_language_names_init;
    if (count($session->active_ui_languages) < 2) {
        return ('&nbsp;');
    }
    $lang_select = "<li class='dropdown'>
      <a href='#' class='dropdown-toggle' role='button' id='dropdownMenuLang' data-toggle='dropdown'>
          <span class='fa fa-globe'></span><span class='sr-only'>" . trans('langChooseLang') . "</span>
      </a>
      <ul class='dropdown-menu' role='menu' aria-labelledby='dropdownMenuLang'>";
    foreach ($session->active_ui_languages as $code) {
        $class = ($code == $session->language)? ' class="active"': '';
        $lang_select .=
            "<li role='presentation'$class>
                <a role='menuitem' tabindex='-1' href='$_SERVER[SCRIPT_NAME]?localize=$code'>" .
                    q($native_language_names_init[$code]) . "</a></li>";
    }
    $lang_select .= "</ul></li>";
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

/*
 * Function module_path
 *
 * Returns a canonicalized form of the current request path to use in matching
 * the current module
 *
 */
function module_path($path) {
    global $urlAppend, $urlServer, $is_admin;

    if (strpos($path, 'modules/units/insert.php') !== false) {
        if (strpos($path, '&dir=') !== false) {
            return 'document';
        }
    }
    if (strpos($path, 'listreq.php') !== false or
        strpos($path, 'newuseradmin.php') !== false or
        strpos($path, 'ldapnewprofadmin.php') !== false) {
        if (strpos($path, '?type=user') !== false) {
            return 'listreq-user';
        } else {
            return 'listreq';
        }
    }

    if (strpos($path, '/usage/') !== false && strpos($path, 't=u') !== false && strpos($path, 'u=') !== false && $is_admin) {
        return '/admin/search_user.php';
    }

    $path = preg_replace('/\?[a-zA-Z0-9=&;]+$/', '', $path);
    $path = str_replace(array($urlServer, $urlAppend, 'index.php'),
                        array('/', '/', ''), $path);
    if (strpos($path, '/course_info/restore_course.php') !== false) {
        return 'course_info/restore_course.php';
    } elseif (strpos($path, '/info/') !== false) {
        return preg_replace('|^.*(info/.*\.php)|', '\1', $path);
    } elseif (strpos($path, '/admin/') !== false) {
        $new_path = preg_replace('|^.*(/admin/.*)|', '\1', $path);
        if ($new_path == '/admin/auth_process.php') {
            return '/admin/auth.php';
        } elseif ($new_path == '/admin/listusers.php' or $new_path == '/admin/edituser.php') {
            return '/admin/search_user.php';
        }
        return $new_path;
    } elseif (strpos($path, '/main/unreguser.php') !== false or
              (strpos($path, '/main/profile') !== false and
               strpos($path, 'personal_stats') === false)) {
        return 'main/profile';
    } elseif (strpos($path, '/main/') !== false) {
        return preg_replace('|^.*(main/.*\.php)|', '\1', $path);
    } elseif (preg_match('+/auth/(opencourses|listfaculte)\.php+', $path)) {
        return '/auth/opencourses.php';
    } elseif (preg_match('+/auth/(registration|newuser|altnewuser|formuser|altsearch)\.php+', $path)) {
        return '/auth/registration.php';
    } elseif (isset($GLOBALS['course_code']) and
              strpos($path, '/courses/' . $GLOBALS['course_code']) !== false) {
        return 'course_home';
    }
    return preg_replace('|^.*modules/([^/]+)/.*$|', '\1', $path);
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

<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component is the core of eclass. Each and every file that
 * requires output to the user's browser must include this file and use
 * the draw method to output the UI to the user's browser.
 *
 * An exception of this scenario is when the user uses the personalised
 * interface. In that case function drawPerso needs to be called.
 *
 */

$navigation = array();
$sectionName = '';
$pageName = '';
$toolName = '';
require_once 'init.php';

if (isset($toolContent_ErrorExists)) {
    Session::Messages($toolContent_ErrorExists);
    if (!$uid) {
        $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
        redirect_to_home_page("main/login_form.php?next=" . urlencode($next));
    } elseif ($_SESSION['status'] == USER_GUEST) {
        redirect_to_home_page();
    } else {
        redirect_to_home_page("main/portfolio.php");
    }
}


require_once 'template/template.inc.php';
require_once 'tools.php';

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
function draw($toolContent, $menuTypeID, $tool_css = null, $head_content = null, $body_action = null, $hideLeftNav = null, $perso_tool_content = null) {
    global $session, $course_code, $course_id, $helpTopic, $helpSubTopic, $uid,
        $is_editor, $is_admin, $is_power_user, $is_departmentmanage_user, $is_usermanage_user,
        $langActivate, $langNote,$langPleaseWait,
        $langAdmin, $langAdminTool, $langAdvancedSearch,
        $langChooseLang, $langDeactivate, $langProfileMenu,
        $langEclass, $langHelp, $langUsageTerms, $langPrivacyPolicy,
        $langHomePage, $langLogin, $langLogout, $langMyAgenda,
        $langMyPersoAnnouncements, $langMyCourses, $langGoToHomePage,
        $langPortfolio, $langSearch,
        $langUserPortfolio, $langUserHeader,
        $navigation, $pageName, $toolName, $sectionName, $currentCourseName,
        $require_current_course, $require_course_admin, $require_help, $siteName,
        $theme, $themeimg, $langMyPortfolio,
        $urlAppend, $urlServer, $public_code,
        $theme_settings, $language, $saved_is_editor,
        $langStudentViewEnable, $langStudentViewDisable, $langTitle, $langEnterNote, $langFieldsRequ;

    // negative course_id might be set in common documents
    if ($course_id < 1) {
        unset($course_id);
        unset($course_code);
    }

    // get blocks content from $toolContent array
    if ($perso_tool_content) {
        $lesson_content = $perso_tool_content ['lessons_content'];
        $personal_calendar_content = $perso_tool_content ['personal_calendar_content'];
    }

    function get_theme_class($class) {
        global $theme_settings;

        if (isset($theme_settings['classes'][$class])) {
            return $theme_settings['classes'][$class];
        } else {
            return $class;
        }
    }

    if (!$toolName and $pageName) {
        $toolName = $pageName;
    } elseif (!$pageName and $toolName) {
        $pageName = $toolName;
    }
    $pageTitle = $siteName;
    $is_mobile = (isset($_SESSION['mobile']) && $_SESSION['mobile'] == true);
    $is_embedonce = (isset($_SESSION['embedonce']) && $_SESSION['embedonce'] == true);
    unset($_SESSION['embedonce']);

    //get the left side menu from tools.php
    $toolArr = ($is_mobile) ? array() : getSideMenu($menuTypeID);
    $numOfToolGroups = count($toolArr);

    $GLOBALS['head_content'] = '';
    $head_content .= $GLOBALS['head_content'];
    $t = new Template('template/' . $theme);

    if ($is_embedonce) {
        $template_file = 'embed.html';
    } else {
        $template_file = 'theme.html';
    }

    $t->set_file('fh', $template_file);
    $t->set_block('fh', 'mainBlock', 'main');

    // template_callback() can be defined in theme settings.php
    if (function_exists('template_callback')) {
        template_callback($t, $menuTypeID, $is_embedonce);
    }

    $t->set_var('LANG', $language);
    $t->set_var('ECLASS_VERSION', CACHE_SUFFIX);

    if (!$is_embedonce) {
        // Remove search if not enabled
        if (!get_config('enable_search')) {
            $t->set_block('mainBlock', 'searchBlock', 'delete');
        }
        $t->set_var('leftNavClass', 'no-embed');
    }


    //  BEGIN constructing of left navigation
    //  ----------------------------------------------------------------------
    $t->set_block('mainBlock', 'leftNavBlock', 'leftNav');
    $t->set_block('leftNavBlock', 'leftNavCategoryBlock', 'leftNavCategory');
    $t->set_block('leftNavCategoryBlock', 'leftNavCategoryTitleBlock', 'leftNavCategoryTitle');

    $t->set_block('leftNavCategoryBlock', 'leftNavLinkBlock', 'leftNavLink');

    $t->set_var('template_base', $urlAppend . 'template/' . $theme);
    $t->set_var('img_base', $themeimg);

    $current_module_dir = module_path($_SERVER['REQUEST_URI']);
    if (!$is_mobile and !$hideLeftNav) {
        if (is_array($toolArr)) {
            $group_opened = false;
            for ($i = 0; $i < $numOfToolGroups; $i ++) {
                if (!$is_embedonce) {
                    $t->set_var('NAV_BLOCK_CLASS', $toolArr[$i][0]['class']);
                    $t->set_var('TOOL_GROUP_ID', $i);
                    if ($toolArr [$i] [0] ['type'] == 'none') {
                        $t->set_var('ACTIVE_TOOLS', '&nbsp;');
                        $t->set_var('NAV_CSS_CAT_CLASS', 'spacer');
                    } elseif ($toolArr [$i] [0] ['type'] == 'split') {
                        $t->set_var('ACTIVE_TOOLS', '&nbsp;');
                        $t->set_var('NAV_CSS_CAT_CLASS', 'split');
                    } elseif ($toolArr [$i] [0] ['type'] == 'text') {
                        $t->set_var('ACTIVE_TOOLS', $toolArr [$i] [0] ['text']);
                        $t->set_var('NAV_CSS_CAT_CLASS', 'category');
                    }
                    $t->parse('leftNavCategoryTitle', 'leftNavCategoryTitleBlock', false);
                }
                $t->set_var('GROUP_CLASS', '');
                $numOfTools = count($toolArr[$i][1]);
                for ($j = 0; $j < $numOfTools; $j++) {
                    $t->set_var('TOOL_LINK', $toolArr[$i][2][$j]);
                    $t->set_var('TOOL_TEXT', $toolArr[$i][1][$j]);
                    if (is_external_link($toolArr[$i][2][$j]) or $toolArr[$i][3][$j] == 'fa-external-link') {
                        $t->set_var('TOOL_EXTRA', ' target="_blank"');
                    } else {
                        $t->set_var('TOOL_EXTRA', '');
                    }

                    $t->set_var('IMG_FILE', $toolArr[$i][3][$j]);
                    $img_class = basename($toolArr[$i][3][$j], ".png");
                    $img_class = preg_replace('/_(on|off)$/', '', $img_class);
                    if (isset($theme_settings['icon_map'][$img_class])) {
                        $img_class = $theme_settings['icon_map'][$img_class];
                    }
                    $t->set_var('IMG_CLASS', $img_class);
                    $module_dir = module_path($toolArr[$i][2][$j]);
                    if ($module_dir == $current_module_dir) {
                        $t->set_var('TOOL_CLASS', get_theme_class('tool_active'));
                        $t->set_var('GROUP_CLASS', get_theme_class('group_active'));
                        $group_opened = true;
                    } else {
                        $t->set_var('TOOL_CLASS', '');
                    }
                    $t->parse('leftNavLink', 'leftNavLinkBlock', true);
                }
                if (!$group_opened and
                    ($is_embedonce or
                     $current_module_dir == '/' or
                     $current_module_dir == 'course_home' or
                     $current_module_dir == 'units' or
                     $current_module_dir == 'weeks' or
                     $current_module_dir == 'main/portfolio.php' or
                     ($current_module_dir == 'usage' and !$GLOBALS['is_course_admin']))) {
                    $t->set_var('GROUP_CLASS', get_theme_class('group_active'));
                    $group_opened = true;
                }
                $t->parse('leftNavCategory', 'leftNavCategoryBlock', true);
                $t->clear_var('leftNavLink'); //clear inner block
            }
            $t->parse('leftNav', 'leftNavBlock', true);
        }
    }

    $t->set_var('URL_PATH', $urlAppend);
    $t->set_var('LOGO_URL_PATH', $urlAppend);
    $t->set_var('SITE_NAME', $siteName);
    $t->set_var('FAVICON_PATH', $urlAppend . 'template/favicon/favicon.ico');
    $t->set_var('ICON_PATH', $urlAppend . 'template/favicon/openeclass_128x128.png');
    $t->set_var('LANG_PLEASE_WAIT', $langPleaseWait);

    //If there is a message to display, show it (ex. Session timeout)
    if ($messages = Session::getMessages()) {
        $t->set_var('EXTRA_MSG', "<div class='row'><div class='col-xs-12'>".$messages."</div></div>");
    }

    $t->set_var('TOOL_CONTENT', $toolContent);

    if (isset($GLOBALS['leftNavExtras'])) {
        $t->set_var('ECLASS_LEFTNAV_EXTRAS', $GLOBALS['leftNavExtras']);
    }

    // if user is logged in display the logout option
    if ($uid) {
        $t->set_var('LANG_USER', q($langUserHeader));
        $t->set_var('USER_NAME', q($_SESSION['givenname']));
        $t->set_var('USER_SURNAME', q($_SESSION['surname']));
        $t->set_var('LANG_USER_ICON', $langProfileMenu);
        $t->set_var('USER_ICON', user_icon($uid));
        $t->set_var('PROFILE_MENU_DATA_TOGGLE', "data-toggle='dropdown'");
        $t->set_var('USERNAME', q($_SESSION['uname']));
        $t->set_var('LANG_PROFILE', q($GLOBALS['langMyProfile']));
        $t->set_var('PROFILE_LINK', $urlAppend . 'main/profile/display_profile.php');
        $t->set_var('LANG_MESSAGES', q($GLOBALS['langMyDropBox']));
        $t->set_var('MESSAGES_LINK', $urlAppend . 'modules/message/index.php');
        $t->set_var('LANG_COURSES', q($GLOBALS['langMyCourses']));
        $t->set_var('COURSES_LINK', $urlAppend . 'main/my_courses.php');
        $t->set_var('LANG_AGENDA', q($langMyAgenda));
        $t->set_var('AGENDA_LINK', $urlAppend . 'main/personal_calendar/index.php');
        $t->set_var('LANG_NOTES', q($GLOBALS['langNotes']));
        $t->set_var('NOTES_LINK', $urlAppend . 'main/notes/index.php');
        $t->set_var('LANG_STATS', q($GLOBALS['langMyStats']));
        $t->set_var('STATS_LINK', $urlAppend . 'modules/usage/?t=u');
        $t->set_var('LANG_LOGOUT', q($langLogout));
        $t->set_var('LOGOUT_LINK', $urlAppend . 'modules/auth/logout.php');
        $t->set_var('CSRF_TOKEN', $_SESSION['csrf_token']);
        $t->set_var('MY_COURSES', q($GLOBALS['langMyCoursesSide']));
        $t->set_var('MY_MESSAGES', q($GLOBALS['langNewMyMessagesSide']));
        $t->set_var('LANG_ANNOUNCEMENTS', q($GLOBALS['langMyAnnouncements']));
        $t->set_var('ANNOUNCEMENTS_LINK', $urlAppend . 'modules/announcements/myannouncements.php');
        if (!$is_embedonce) {
            if ($session->status == USER_GUEST) {
                $t->set_block('mainBlock', 'UserMenuBlock', 'delete');
            } else {
                if ((isset($is_admin) and $is_admin) or
                    (isset($is_power_user) and $is_power_user) or
                    (isset($is_usermanage_user) and ($is_usermanage_user)) or
                    (isset($is_departmentmanage_user) and $is_departmentmanage_user)) {
                        $t->set_var('LANG_ADMIN', q($langAdminTool));
                        $t->set_var('ADMIN_LINK', $urlAppend . 'modules/admin/');
                        $t->set_var('topMenuDivider', 'style="border-top: 1px solid #ddd"');
                        $t->set_var('ADMIN_ICON',"<li><a href='". $urlAppend . "modules/admin/index.php'><span class='fa fa-wrench'></span></a></li>");
                } else {
                        $t->set_block('mainBlock', 'AdminToolsBlock', 'delete');
                }
                if (get_config('personal_blog')) {
                    $t->set_var('LANG_MYBLOG', q($GLOBALS['langMyBlog']));
                    $t->set_var('MYBLOG_LINK', $urlAppend . 'modules/blog/index.php');
                } elseif ($menuTypeID > 0) {
                    $t->set_block('mainBlock', 'PersoBlogBlock', 'delete');
                }
                if (get_config('eportfolio_enable')) {
                    $t->set_var('LANG_MYEPORTFOLIO', q($GLOBALS['langMyePortfolio']));
                    $t->set_var('MYEPORTFOLIO_LINK', $urlAppend . 'main/eportfolio/index.php?id='.$GLOBALS['uid'].'&token='.token_generate('eportfolio' . $GLOBALS['uid']));
                } elseif ($menuTypeID > 0) {
                    $t->set_block('mainBlock', 'ePortfolioBlock', 'delete');
                }
                if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or
                    ($session->status == USER_STUDENT and get_config('mydocs_student_enable'))) {
                    $t->set_var('LANG_MYDOCS', q($GLOBALS['langMyDocs']));
                    $t->set_var('MYDOCS_LINK', $urlAppend . 'main/mydocs/index.php');
                } elseif ($menuTypeID > 0) {
                    $t->set_block('mainBlock', 'MyDocsBlock', 'delete');
                }
            }
        }
        $t->set_var('QUICK_NOTES', q($GLOBALS['langQuickNotesSide']));
        $t->set_var('langSave', q($GLOBALS['langSave']));
        $t->set_var('langAllNotes', q($GLOBALS['langAllNotes']));
        $t->set_var('langAllMessages', q($GLOBALS['langAllMessages']));
        $t->set_var('langNoteTitle', q($langTitle));
        $t->set_var('langEnterNoteLabel', $langNote);
        $t->set_var('langEnterNote', q($langEnterNote));
        $t->set_var('langFieldsRequ', q($langFieldsRequ));

        $t->set_var('LOGGED_IN', 'true');

        // customization for LTI enrolled users
        $is_lti_enrol_user = substr($_SESSION['uname'], 0, strlen("enrol_lti_") ) === "enrol_lti_";
        if ($is_lti_enrol_user) {
            $t->set_var('USERNAME', q($_SESSION['givenname'] . " " . $_SESSION['surname']));
            $t->set_var('HOMEBUTTON_STYLE', "style='display: none;'");
            $t->set_var('BREADCRUMB_STYLE', "style='display: none;'");
            $t->set_var('LOGO_URL_PATH', "#");
            $t->set_var('PROFILE_MENU_DATA_TOGGLE', "");
        }
    } else {
        if (get_config('hide_login_link')) {
            $t->set_block('mainBlock', 'LoginIconBlock', 'delete');
        } else {
            $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
            if (preg_match('@(?:^/(?:modules|courses)|listfaculte|opencourses|openfaculties)@', $next)) {
                $nextParam = '?next=' . urlencode($next);
            } else {
                $nextParam = '';
            }
            $t->set_var('LANG_LOGOUT', $langLogin);
            $t->set_var('LOGOUT_LINK', $urlServer . 'main/login_form.php' . $nextParam);
        }
        $t->set_var('LOGGED_IN', 'false');
    }
    if (isset($require_current_course) and !isset($sectionName)) {
        $sectionName = $currentCourseName;
    }
    // set the text and icon on the third bar (header)
    if ($menuTypeID == 2) {
        if (!$pageName) {
            $t->set_var('SECTION_TITLE', q($currentCourseName));
        } else {
            $t->set_var('SECTION_TITLE', "<a href='{$urlServer}courses/$course_code/'>" . q($currentCourseName) . '</a>');
        }
        if (!empty($public_code) and ($public_code !== $course_code)) {
            $t->set_var('COURSE_PUBLIC_CODE', "($public_code)");
        }
    } elseif ($menuTypeID == 3) {
        $t->set_var('SECTION_TITLE', $langAdmin);
        $sectionName = $langAdmin;
    } elseif ($menuTypeID > 0 and $menuTypeID < 3) {
        $t->set_var('SECTION_TITLE', $langUserPortfolio);
        $sectionName = $langUserPortfolio;
    } else {
        $homepagetitle = get_config('homepage_title');
        if (isset($homepagetitle)) {
            $t->set_var('SECTION_TITLE', $homepagetitle);
            $sectionName = $homepagetitle;
        } else {
            $t->set_var('SECTION_TITLE', get_config('defaultHomepageTitle', $langEclass));
            $sectionName = get_config('defaultHomepageTitle', $langEclass);
        }
    }

    //set the appropriate search action for the searchBox form
    if ($menuTypeID == 2) {
        $searchAction = "search_incourse.php?all=true";
        $searchAdvancedURL = $searchAction;
    } elseif ($menuTypeID == 1 || $menuTypeID == 3) {
        $searchAction = "search.php";
        $searchAdvancedURL = $searchAction;
    } else { //$menuType == 0
        $searchAction = "search.php";
        $searchAdvancedURL = $searchAction;
    }
    $mod_activation = '';
    if ($is_editor and isset($course_code)) {
        // link for activating / deactivating module
        $module_id = current_module_id();
        if (display_activation_link($module_id)) {
            if (visible_module($module_id)) {
                $modIconClass = 'fa-minus-square tiny-icon-red';
                $modIconTitle = q($langDeactivate);
                $modState = 0;
            } else {
                $modIconClass = 'fa-check-square tiny-icon-green';
                $modIconTitle = q($langActivate);
                $modState = 1;
            }
            $mod_activation = "<a href='{$urlAppend}main/module_toggle.php?course=$course_code&amp;module_id=$module_id' id='module_toggle' data-state='$modState' data-toggle='tooltip' data-placement='top' title='$modIconTitle'><span class='fa tiny-icon $modIconClass'></span></a>";
        }
    }

    $t->set_var('SEARCH_ACTION', $searchAction);
    $t->set_var('SEARCH_ADVANCED_URL', $searchAdvancedURL);
    $t->set_var('SEARCH_TITLE', $langSearch);
    $t->set_var('SEARCH_ADVANCED', $langAdvancedSearch);
    $t->set_var('LANG_PORTFOLIO', $langPortfolio);
    $t->set_var('LANG_MYPORTFOLIO', $langMyPortfolio);

    $t->set_var('TOOL_NAME', q($toolName));

    if ($is_editor) {
        $t->set_var('ACTIVATE_MODULE', $mod_activation);
    }

    if (!$t->get_var('LANG_SELECT')) {
        if ($menuTypeID != 2) {
            $t->set_var('LANG_SELECT', lang_selections());
            $t->set_var('LANG_SELECT_TITLE', "title='$langChooseLang'");
        } else {
            $t->set_var('LANG_SELECT', '');
        }
    }

    // breadcrumb and page title
    if (!$is_embedonce and !$is_mobile and $current_module_dir != '/') {

        $t->set_block('mainBlock', 'breadCrumbLinkBlock', 'breadCrumbLink');
        $t->set_block('mainBlock', 'breadCrumbEntryBlock', 'breadCrumbEntry');

        // Breadcrumb landing page link
        $homepageSet = get_config('homepage');
        $showStart = true;
        if ($homepageSet == 'external' and ($landingUrl = get_config('landing_url'))) {
            $landingPageName = get_config('landing_name');
            if (!$landingPageName) {
                $landingPageName = $langHomePage;
            }
            $t->set_var('BREAD_TEXT', $landingPageName);
            $t->set_var('BREAD_HREF', $landingUrl);
            $t->parse('breadCrumbEntry', 'breadCrumbLinkBlock', true);
        } elseif ($homepageSet == 'toolbox') {
            $t->set_var('BREAD_TEXT', get_config('toolbox_name', $langHomePage));
            $t->set_var('BREAD_HREF', $urlAppend . 'main/toolbox.php');
            $t->parse('breadCrumbEntry', 'breadCrumbLinkBlock', true);
        }

        // Breadcrumb first entry (home / portfolio)
        if ($session->status != USER_GUEST) {
            if (isset($_SESSION['uid'])) {
                $t->set_var('BREAD_TEXT', '<span class="fa fa-home"></span> ' . $langPortfolio);
                $t->set_var('BREAD_HREF', $urlAppend . 'main/portfolio.php');
            } else {
                $homebreadcrumb = getSerializedMessage(get_config('homepage_name'));
                if ($homebreadcrumb) {
                    $t->set_var('BREAD_TEXT', $homebreadcrumb);
                } else {
                    $t->set_var('BREAD_TEXT', get_config('defaultHomepageBcrmp', $langHomePage));
                }
                $t->set_var('BREAD_HREF', $urlAppend);
                $showStart = true;
            }

            if ($showStart) {
                if (isset($require_current_course) or $pageName) {
                    $t->parse('breadCrumbEntry', 'breadCrumbLinkBlock', true);
                } else {
                    $t->parse('breadCrumbEntry', 'breadCrumbEntryBlock', true);
                }
            }
        }

        // Breadcrumb course home entry
        if (isset($course_code) and $menuTypeID != 3) {
            $t->set_var('BREAD_TEXT', q(ellipsize($currentCourseName, 48)));
            if ($pageName) {
                $t->set_var('BREAD_HREF', $urlAppend . 'courses/' . $course_code . '/');
                $t->parse('breadCrumbEntry', 'breadCrumbLinkBlock', true);
            } else {
                $t->parse('breadCrumbEntry', 'breadCrumbEntryBlock', true);
            }
            $pageTitle .= " | " . ellipsize($currentCourseName, 32);
        }

        foreach ($navigation as $step) {
            $t->set_var('BREAD_TEXT', q($step['name']));
            if (isset($step['url'])) {
                $t->set_var('BREAD_HREF', $step['url']);
                $t->parse('breadCrumbEntry', 'breadCrumbLinkBlock', true);
            } else {
                $t->parse('breadCrumbEntry', 'breadCrumbEntryBlock', true);
            }
        }

        if ($pageName) {
            $t->set_var('BREAD_TEXT', q($pageName));
            $t->parse('breadCrumbEntry', 'breadCrumbEntryBlock', true);
        }

        if ($pageName) {
            $pageTitle .= " | " . $pageName;
        }

    } else {
        if (!$is_embedonce) {
            $t->set_block('mainBlock', 'breadCrumbs', 'delete');
        }
    }

    //END breadcrumb --------------------------------

    $t->set_var('PAGE_TITLE', q($pageTitle));

    if (isset($course_code)) {
        $t->set_var('COURSE_CODE', $course_code);
        $t->set_var('COURSE_ID', $course_id);
    }

    if (!$is_embedonce) {
        if ($is_mobile) {
            $t->set_block('mainBlock', 'normalViewOpenDiv', 'delete');
            $t->set_block('mainBlock', 'headerBlock', 'delete');
        } else {
            $t->set_block('mainBlock', 'mobileViewOpenDiv', 'delete');
        }
    }

    // Add Theme Options styles
    $t->set_var('logo_img', $themeimg.'/eclass-new-logo.png');
    $t->set_var('logo_img_small', $themeimg.'/logo_eclass_small.png');
    $t->set_var('container', 'container');
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
            $styles_str .= ".jumbotron.jumbotron-login {background-size: cover, cover; background-position: 0% 0%;}";
        }
        //$styles_str .= ".jumbotron.jumbotron-login {  background-size: 353px, cover; background-position: 10% 60%;}";
        if (isset($theme_options_styles['fluidContainerWidth'])){
            $t->set_var('container', 'container-fluid');
            $styles_str .= ".container-fluid {max-width:$theme_options_styles[fluidContainerWidth]px}";
        }
        if (isset($theme_options_styles['openeclassBanner'])){
             $styles_str .= "#openeclass-banner {display: none;}";
        }
        if (!empty($theme_options_styles['leftNavBgColor'])) {
            $rgba_no_alpha = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $rgba_no_alpha[3] = '1';
            $rgba_no_alpha = 'rgba(' . implode(',', $rgba_no_alpha) . ')';
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
        if (isset($theme_options_styles['imageUpload'])) $t->set_var('logo_img', "$urlThemeData/$theme_options_styles[imageUpload]");
        if (isset($theme_options_styles['imageUploadSmall'])) $t->set_var('logo_img_small', "$urlThemeData/$theme_options_styles[imageUploadSmall]");

        $t->set_var('EXTRA_CSS', "<style>$styles_str</style>");
    }

    $t->set_var('TOOL_PATH', $urlAppend);

    if (isset($body_action)) {
        $t->set_var('BODY_ACTION', $body_action);
    }

    $t->set_var('LANG_SEARCH', $langSearch);

    // display role switch button if needed
    if (isset($require_current_course) and
        ($is_editor or (isset($saved_is_editor) and $saved_is_editor)) and
        !(isset($require_course_admin) and $require_course_admin) and
        !(isset($require_editor) and $require_editor)) {
        if ($is_editor) {
            $t->set_var('STUDENT_VIEW_TITLE', $langStudentViewEnable);
        } else {
            $t->set_var('STUDENT_VIEW_TITLE', $langStudentViewDisable);
            $t->set_var('STUDENT_VIEW_CLASS', 'btn-toggle-on');
        }
        $t->set_var('STUDENT_VIEW_URL', $urlAppend . 'main/student_view.php?course=' . $course_code);
    } else {
        if (!$is_embedonce) {
            $t->set_block('mainBlock', 'statusSwitchBlock', 'delete');
        }
    }

    // if $require_help is true (set by each tool) display the help link
    if ($require_help == true) {
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
                                label: '". js_escape($GLOBALS['langClose']). "'
                            }
                        }
                    });
                });
            });
        });
        </script>
        ";

        $help_link_icon = "
        <a id='help-btn' href=\"" . $urlAppend . "modules/help/help.php?language=$language&amp;topic=$helpTopic&amp;subtopic=$helpSubTopic\">
            <span class='pull-right text-info fa fa-question-circle fa-2x' data-toggle='tooltip' data-placement='top' title='$langHelp'>
            </span>
        </a>";

        $t->set_var('HELP_LINK_ICON', $help_link_icon);
        $t->set_var('LANG_HELP', $langHelp);
    } else {
        $t->set_var('HELP_LINK_ICON', '');
        $t->set_var('LANG_HELP', '');
    }

    if (isset($head_content)) {
        global $webDir; // required by indexer
        require_once 'modules/search/indexer.class.php';
        if (isset($_SESSION[Indexer::SESSION_PROCESS_AT_NEXT_DRAW]) && $_SESSION[Indexer::SESSION_PROCESS_AT_NEXT_DRAW] === true) {
            $head_content .= Indexer::queueAsyncJSCode();
            $_SESSION[Indexer::SESSION_PROCESS_AT_NEXT_DRAW] = false;
        }
        $t->set_var('HEAD_EXTRAS', $head_content);
    }

    if (defined('RSS')) {
        $rss_icon = defined('RSS_ICON')? RSS_ICON: 'fa-rss-square';
        $rss_title = q(defined('RSS_TITLE')? RSS_TITLE: $GLOBALS['langRSSFeed']);
        $t->set_var('RSS_LINK_ICON', "
            <a href='$urlAppend" . RSS . "'>
                <span class='fa $rss_icon tiny-icon tiny-icon-rss' data-toggle='tooltip' data-placement='top' title='$rss_title'></span>
            </a>");
    }

    if ($perso_tool_content) {
        $t->set_var('LANG_MY_PERSO_LESSONS', $langMyCourses);
        $t->set_var('LANG_MY_PERSO_ANNOUNCEMENTS', $langMyPersoAnnouncements);
        $t->set_var('LANG_MY_PERSONAL_CALENDAR', $langMyAgenda);
        $t->set_var('LESSON_CONTENT', $lesson_content);
        $t->set_var('URL_PATH', $urlAppend);
        $t->set_var('TOOL_PATH', $urlAppend);
        $t->set_var('PERSONAL_CALENDAR_CONTENT', $personal_calendar_content);
    }

    $t->set_var('GO_TO_HOME', q($langGoToHomePage));
    $t->set_var('COPYRIGHT', 'Open eClass © 2003-' . date('Y'));
    $t->set_var('TERMS_URL', $urlAppend .'info/terms.php');
    $t->set_var('LANG_TERMS', $langUsageTerms);
    if (get_config('activate_privacy_policy_text')) {
        $t->set_var('LANG_PRIVACY_POLICY', $langPrivacyPolicy);
        $t->set_var('PRIVACY_POLICY_URL', $urlAppend .'info/privacy_policy.php');
    } elseif (!$is_embedonce) {
        $t->set_block('mainBlock', 'privacyPolicyBlock', 'delete');
    }

    // Remove tool title block from selected pages
    if (defined('HIDE_TOOL_TITLE')) {
        $t->set_block('mainBlock', 'toolTitleBlock', 'toolTitleBlockVar');
        $t->set_var('toolTitleBlockVar', '');
    }

    $t->set_var('EXTRA_FOOTER_CONTENT', get_config('extra_footer_content'));

    // Hack to leave HTML body unclosed
    if (defined('TEMPLATE_REMOVE_CLOSING_TAGS')) {
        $t->set_block('mainBlock', 'closingTagsBlock', 'delete');
    }

    if (get_config('ext_analytics_enabled') and $html_footer = get_config('ext_analytics_code')) {
        $t->set_var('HTML_FOOTER', $html_footer);
    }

    if (get_config('ext_userway_enabled') and $html_footer = get_config('ext_userway_code')) {
        $t->set_var('HTML_FOOTER', $html_footer);
    }

    if (file_exists('node_modules/mathjax/es5/tex-chtml.js')) {
        $t->set_var('HTML_FOOTER', '<script type="text/javascript" id="MathJax-script" async src="'.
            $urlAppend . 'node_modules/mathjax/es5/tex-chtml.js"></script>', true);
    }

    //  At this point all variables are set and we are ready to send the final output
    //  back to the browser
    $t->parse('main', 'mainBlock', false);

    $t->pparse('Output', 'fh');
}

// Simplified draw for pop-ups
function draw_popup() {
    global $language, $urlAppend, $theme, $pageName, $head_content, $tool_content;

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
    global $session;
    if (count($session->active_ui_languages) < 2) {
        return ('&nbsp;');
    }
    $html = '<form name="langform" action="' . $_SERVER ['SCRIPT_NAME'] . '" method="get" >';
    $html .= lang_select_options('localize', 'onChange="document.langform.submit();"');
    $html .= '</form>';
    return $html;
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
    global $urlAppend, $urlServer;

    if (strpos($path, 'modules/units/insert.php') !== false) {
        if (strpos($path, '&dir=') !== false) {
            return 'document';
        }
    }
    if (strpos($path, 'listreq.php') !== false or
        strpos($path, 'ldapnewprofadmin.php') !== false) {
        if (strpos($path, '?type=user') !== false) {
            return 'listreq-user';
        } else {
            return 'listreq';
        }
    }

    $original_path = $path;
    $path = preg_replace('/\?[a-zA-Z0-9=&;_-]+$/', '', $path);
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
    } elseif (preg_match('|/course_home/page.php.*id=(\d+)|', $original_path, $matches)) {
        return 'page:id=' . $matches[1];
    } elseif (isset($GLOBALS['course_code']) and
              strpos($path, '/courses/' . $GLOBALS['course_code']) !== false) {
        return 'course_home';
    } elseif (strpos($path, '/lti_consumer/launch.php') !== false or
              strpos($path, '/lti_consumer/load.php') !== false) {
        $lti_path = str_replace(array($urlServer, $urlAppend, '&amp;'), array('/', '/', '&'), $original_path);
        return $lti_path;
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

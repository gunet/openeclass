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

require_once 'init.php';

if ($is_editor and isset($course_code) and isset($_GET['hide'])) {
    $eclass_module_id = intval($_GET['eclass_module_id']);
    $cid = course_code_to_id($course_code);
    $visible = ($_GET['hide'] == 0) ? 0 : 1;
    Database::get()->query("UPDATE course_module SET visible = ?d
                        WHERE module_id = ?d AND
                        course_id = ?d", $visible, $eclass_module_id, $cid);
}

if (isset($toolContent_ErrorExists)) {
    Session::set_flashdata($toolContent_ErrorExists, 'alert1');
    session_write_close();
    if (!$uid) {
        $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
        header("Location:" . $urlSecure . "main/login_form.php?next=" . urlencode($next));
    } else {
        header("Location:" . $urlServer . "index.php");
    }
    exit();
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
    global $courseHome, $course_code, $helpTopic,
    $homePage, $title, $is_editor, $langActivate,
    $langAdmin, $langAdvancedSearch, $langAnonUser, $langChangeLang,
    $langChooseLang, $langCopyrightFooter, $langDeactivate,
    $langEclass, $langExtrasLeft, $langExtrasRight, $langHelp,
    $langHomePage, $langLogin, $langLogout, $langMyPersoAgenda, $langMyAgenda,
    $langMyPersoAnnouncements, $langMyPersoDeadlines,
    $langMyPersoDocs, $langMyPersoForum, $langMyPersoLessons,
    $langPersonalisedBriefcase, $langSearch, $langUser,
    $langUserBriefcase, $langUserHeader, $language, $nameTools,
    $navigation, $page_name, $page_navi,
    $require_current_course, $require_help, $siteName, $siteName,
    $status, $switchLangURL, $theme, $themeimg,
    $toolContent_ErrorExists, $urlAppend, $urlSecure, $urlServer,
    $theme_settings;

    //get blocks content from $toolContent array
    if ($perso_tool_content) {
        $lesson_content = $perso_tool_content ['lessons_content'];
        $assigns_content = $perso_tool_content ['assigns_content'];
        $announce_content = $perso_tool_content ['announce_content'];
        $docs_content = $perso_tool_content ['docs_content'];
        $agenda_content = $perso_tool_content ['agenda_content'];
        $forum_content = $perso_tool_content ['forum_content'];
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

    $is_mobile = (isset($_SESSION['mobile']) && $_SESSION['mobile'] == true) ? true : false;
    $is_embedonce = (isset($_SESSION['embedonce']) && $_SESSION['embedonce'] == true) ? true : false;
    unset($_SESSION['embedonce']);

    //get the left side menu from tools.php
    $toolArr = ($is_mobile) ? array() : getSideMenu($menuTypeID);
    $numOfToolGroups = count($toolArr);

    $GLOBALS['head_content'] = '';
    $head_content .= $GLOBALS['head_content'];
    $t = new Template('template/' . $theme);

    if ($is_mobile)
        $t->set_file('fh', 'mtheme.html');
    elseif ($is_embedonce)
        $t->set_file('fh', 'dtheme.html');
    else
        $t->set_file('fh', 'theme.html');

    $t->set_block('fh', 'mainBlock', 'main');

    // template_callback() can be defined in theme settings.php
    if (function_exists('template_callback')) {
        template_callback($t, $menuTypeID);
    }

    //	BEGIN constructing of left navigation
    //	----------------------------------------------------------------------
    $t->set_block('mainBlock', 'leftNavBlock', 'leftNav');
    $t->set_block('leftNavBlock', 'leftNavCategoryBlock', 'leftNavCategory');
    $t->set_block('leftNavCategoryBlock', 'leftNavCategoryTitleBlock', 'leftNavCategoryTitle');

    $t->set_block('leftNavCategoryBlock', 'leftNavLinkBlock', 'leftNavLink');

    $t->set_var('template_base', $urlAppend . 'template/' . $theme);
    $t->set_var('img_base', $themeimg);

    $current_module_dir = module_path($_SERVER['REQUEST_URI']);
    if (is_array($toolArr)) {
        for ($i = 0; $i < $numOfToolGroups; $i ++) {
            $t->set_var ('NAV_BLOCK_CLASS', $toolArr[$i][0]['class']);
            $t->set_var('TOOL_GROUP_ID', $i);
            if ($toolArr [$i] [0] ['type'] == 'none') {
                $t->set_var('ACTIVE_TOOLS', '&nbsp;');
                $t->set_var('NAV_CSS_CAT_CLASS', 'spacer');
                $t->parse('leftNavCategoryTitle', 'leftNavCategoryTitleBlock', false);
            } elseif ($toolArr [$i] [0] ['type'] == 'split') {
                $t->set_var('ACTIVE_TOOLS', '&nbsp;');
                $t->set_var('NAV_CSS_CAT_CLASS', 'split');
                $t->parse('leftNavCategoryTitle', 'leftNavCategoryTitleBlock', false);
            } elseif ($toolArr [$i] [0] ['type'] == 'text') {
                $t->set_var('ACTIVE_TOOLS', $toolArr [$i] [0] ['text']);
                $t->set_var('NAV_CSS_CAT_CLASS', 'category');
                $t->parse('leftNavCategoryTitle', 'leftNavCategoryTitleBlock', false);
            }

            $t->set_var('GROUP_CLASS', '');
            $numOfTools = count($toolArr[$i][1]);
            for ($j = 0; $j < $numOfTools; $j++) {
                $t->set_var('TOOL_LINK', $toolArr[$i][2][$j]);
                $t->set_var('TOOL_TEXT', $toolArr[$i][1][$j]);
                if (in_array($toolArr[$i][2][$j], array(get_config('phpMyAdminURL'), get_config('phpSysInfoURL'))) or
                        strpos($toolArr[$i][3][$j], 'external_link') === 0) {
                    $t->set_var('TOOL_ATTR', ' target="_blank"');
                } else {
                    $t->set_var('TOOL_ATTR', '');
                }

                $t->set_var('IMG_FILE', $toolArr [$i] [3] [$j]);
                $img_class = basename($toolArr [$i] [3] [$j], ".png");
                $img_class = preg_replace('/_(on|off)$/', '', $img_class);
                if (isset($theme_settings['icon_map'][$img_class])) {
                    $img_class = $theme_settings['icon_map'][$img_class];
                }
                $t->set_var('IMG_CLASS', $img_class);
                $module_dir = module_path($toolArr[$i][2][$j]);
                if ($module_dir == $current_module_dir) {
                    $t->set_var('TOOL_CLASS', get_theme_class('tool_active'));
                    $t->set_var('GROUP_CLASS', get_theme_class('group_active'));
                } else {
                    $t->set_var('TOOL_CLASS', '');
                }
                $t->parse('leftNavLink', 'leftNavLinkBlock', true);
            }

            $t->parse('leftNavCategory', 'leftNavCategoryBlock', true);
            $t->clear_var('leftNavLink'); //clear inner block
        }
        $t->parse('leftNav', 'leftNavBlock', true);

        if (isset($hideLeftNav)) {
            $t->clear_var('leftNav');
            $t->set_var('CONTENT_MAIN_CSS', 'content_main_no_nav');
        } elseif ($homePage && !isset($_SESSION['uid'])) {
            $t->set_var('CONTENT_MAIN_CSS', 'content_main_first');
        } else {
            $t->set_var('CONTENT_MAIN_CSS', 'content_main');
        }

        $t->set_var('URL_PATH', $urlAppend);
        $t->set_var('SITE_NAME', $siteName);


        //If there is a message to display, show it (ex. Session timeout)
        if ($messages = Session::render_flashdata()) {
            $t->set_var('EXTRA_MSG', $messages);
        }

        $t->set_var('TOOL_CONTENT', $toolContent);

        // If we are on the login page we can define two optional variables
        // in common.inc.php (to allow internationalizing messages)
        // for extra content on the left and right bar.

        if ($homePage && !isset($_SESSION['uid'])) {
            $t->set_var('ECLASS_HOME_EXTRAS_LEFT', $langExtrasLeft);
            $t->set_var('ECLASS_HOME_EXTRAS_RIGHT', $langExtrasRight);
        }

        if (isset($GLOBALS['leftNavExtras']))
            $t->set_var('ECLASS_LEFTNAV_EXTRAS', $GLOBALS['leftNavExtras']);

        //if user is logged in display the logout option
        if (isset($_SESSION['uid'])) {
            $t->set_var('LANG_USER', $langUserHeader);
            $t->set_var('USER_NAME', q($_SESSION['givenname']));
            $t->set_var('USER_SURNAME', q($_SESSION['surname']) . ", ");
            $t->set_var('LANG_LOGOUT', $langLogout);
            $t->set_var('LOGOUT_LINK', $urlServer . 'index.php?logout=yes');
	    $t->set_var('LOGGED_IN', 'true');
        } else {
            if (!get_config('dont_display_login_form')) {
                $t->set_var('LANG_LOGOUT', $langLogin);
                $t->set_var('LOGOUT_LINK', $urlSecure . 'main/login_form.php');
            } else {
                $t->set_var('LOGOUT_LINK', '#');
            }
	    $t->set_var('LOGGED_IN', 'false');
        }
        // set the text and icon on the third bar (header)
        if ($menuTypeID == 2) {
            $t->set_var('THIRD_BAR_TEXT', "<a href='${urlServer}courses/$course_code/'>" . q($title) . '</a>');
            $t->set_var('THIRDBAR_LEFT_ICON', 'lesson_icon');
        } elseif ($menuTypeID == 3) {
            $t->set_var('THIRD_BAR_TEXT', $langAdmin);
            $t->set_var('THIRDBAR_LEFT_ICON', 'admin_bar_icon');
        } elseif ($menuTypeID > 0 and $menuTypeID < 3) {
            $t->set_var('THIRD_BAR_TEXT', $langUserBriefcase);
            $t->set_var('THIRDBAR_LEFT_ICON', 'briefcase_icon');
        } elseif ($menuTypeID > 0) {
            $t->set_var('THIRD_BAR_TEXT', $langPersonalisedBriefcase);
            $t->set_var('THIRDBAR_LEFT_ICON', 'briefcase_icon');
        } else {
            $t->set_var('THIRD_BAR_TEXT', $langEclass);
            $t->set_var('THIRDBAR_LEFT_ICON', 'logo_icon');
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
                    $message = $langDeactivate;
                    $mod_activation = "<a class='deactivate_module' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;eclass_module_id=$module_id&amp;hide=0'>($langDeactivate)</a>";
                } else {
                    $message = $langActivate;
                    $mod_activation = "<a class='activate_module' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;eclass_module_id=$module_id&amp;hide=1'>($langActivate)</a>";
                }
            }
        }

        $t->set_var('SEARCH_ACTION', $searchAction);
        $t->set_var('SEARCH_ADVANCED_URL', $searchAdvancedURL);
        $t->set_var('SEARCH_TITLE', $langSearch);
        $t->set_var('SEARCH_ADVANCED', $langAdvancedSearch);

        $t->set_var('TOOL_NAME', $nameTools);

        if ($is_editor) {
            $t->set_var('ACTIVATE_MODULE', $mod_activation);
        }

        if ($menuTypeID != 2) {
            $t->set_var('LANG_SELECT', lang_selections());
            $t->set_var('LANG_SELECT_TITLE', "title='$langChooseLang'");
        } else {
            $t->set_var('LANG_SELECT', '');
        }

        //START breadcrumb AND page title

        if (!$page_navi)
            $page_navi = $navigation;
        if (!$page_name)
            $page_name = $nameTools;

        $t->set_block('mainBlock', 'breadCrumbHomeBlock', 'breadCrumbHome');

        if ($status != USER_GUEST) {
            if (!isset($_SESSION['uid'])) {
                $t->set_var('BREAD_TEXT', $langHomePage);
            } else {
                $t->set_var('BREAD_TEXT', $langPersonalisedBriefcase);
            }

            if (!$homePage) {
                $t->set_var('BREAD_HREF_FRONT', '<a href="{%BREAD_START_LINK%}">');
                $t->set_var('BREAD_START_LINK', $urlServer);
                $t->set_var('BREAD_HREF_END', '</a>');
            }

            $t->parse('breadCrumbHome', 'breadCrumbHomeBlock', false);
        }

        $pageTitle = $siteName;

        $breadIterator = 1;
        $t->set_block('mainBlock', 'breadCrumbStartBlock', 'breadCrumbStart');

        if (isset($course_code) && !$courseHome) {
            $t->set_var('BREAD_HREF_FRONT', '<a href="{%BREAD_LINK%}">');
            $t->set_var('BREAD_LINK', $urlServer . 'courses/' . $course_code . '/index.php');
            $t->set_var('BREAD_TEXT', q(ellipsize($title, 64)));
            if ($status == USER_GUEST)
                $t->set_var('BREAD_ARROW', '');
            $t->set_var('BREAD_HREF_END', '</a>');
            $t->parse('breadCrumbStart', 'breadCrumbStartBlock', true);
            $breadIterator ++;
            if (isset($pageTitle)) {
                $pageTitle .= " | " . q($title);
            } else {
                $pageTitle = q($title);
            }
        } elseif (isset($course_code) && $courseHome) {
            $t->set_var('BREAD_HREF_FRONT', '');
            $t->set_var('BREAD_LINK', '');
            $t->set_var('BREAD_TEXT', q(ellipsize($title, 64)));
            $t->set_var('BREAD_ARROW', '&#187;');
            $t->set_var('BREAD_HREF_END', '');
            $t->parse('breadCrumbStart', 'breadCrumbStartBlock', true);
            $breadIterator ++;
            $pageTitle .= " | " . q($title);
        }

        if (isset($page_navi) && is_array($page_navi) && !$homePage) {
            foreach ($page_navi as $step) {

                $t->set_var('BREAD_HREF_FRONT', '<a href="{%BREAD_LINK%}">');
                $t->set_var('BREAD_LINK', $step['url']);
                $t->set_var('BREAD_TEXT', ellipsize($step['name'], 64));
                $t->set_var('BREAD_ARROW', '&#187;');
                $t->set_var('BREAD_HREF_END', '</a>');
                $t->parse('breadCrumbStart', 'breadCrumbStartBlock', true);

                $breadIterator ++;

                $pageTitle .= " | " . $step ["name"];
            }
        }

        if (isset($page_name) && !$homePage) {

            $t->set_var('BREAD_HREF_FRONT', '');
            $t->set_var('BREAD_TEXT', $page_name);
            $t->set_var('BREAD_ARROW', '&#187;');
            $t->set_var('BREAD_HREF_END', '');

            $t->parse('breadCrumbStart', 'breadCrumbStartBlock', true);
            $breadIterator ++;
            $pageTitle .= " | " . $page_name;
        }

        $t->set_block('mainBlock', 'breadCrumbEndBlock', 'breadCrumbEnd');

        for ($breadIterator2 = 0; $breadIterator2 < $breadIterator; $breadIterator2 ++) {

            $t->parse('breadCrumbEnd', 'breadCrumbEndBlock', true);
        }

        //END breadcrumb --------------------------------


        $t->set_var('PAGE_TITLE', q($pageTitle));

        // Add the optional mobile-specific css if necessarry
        if ($is_mobile) {
            $t->set_var('EXTRA_CSS', "<link href=\"{$urlAppend}template/${theme}${tool_css}/theme_mobile.css\" rel=\"stylesheet\" type=\"text/css\" >");
        }

        // Add the optional embed-specific css if necessarry
        if ($is_embedonce) {
            $t->set_var('EXTRA_CSS', "<link href=\"{$urlAppend}template/${theme}${tool_css}/theme_embed.css\" rel=\"stylesheet\" type=\"text/css\" >");
        }

        // Add the optional tool-specific css of the tool, if it's set
        if (isset($tool_css)) {
            $t->set_var('TOOL_CSS', "<link href=\"{%TOOL_PATH%}modules/$tool_css/tool.css\" rel=\"stylesheet\" type=\"text/css\" >");
        }

        $t->set_var('TOOL_PATH', $urlAppend);

        if (isset($head_content)) {
            $t->set_var('HEAD_EXTRAS', $head_content);
        }

        if (isset($body_action)) {
            $t->set_var('BODY_ACTION', $body_action);
        }

        $t->set_var('LANG_SEARCH', $langSearch);

        //if $require_help is true (set by each tool) display the help link
        if ($require_help == true) {
            if (isset($require_current_course) and !$is_editor) {
                $helpTopic .= '_student';
            }
            $help_link_icon = " <a href=\"" . $urlAppend . "modules/help/help.php?topic=$helpTopic&amp;language=$language\"
        onClick=\"window.open('" . $urlAppend . "modules/help/help.php?topic=$helpTopic&amp;language=$language','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10');
        return false;\"><img class='HelpIcon' src='$themeimg/help.png' alt='$langHelp' title='$langHelp' /></a>";

            $t->set_var('HELP_LINK_ICON', $help_link_icon);
            $t->set_var('LANG_HELP', $langHelp);
        } else {
            $t->set_var('HELP_LINK_ICON', '');
            $t->set_var('LANG_HELP', '');
        }
        if (defined('RSS')) {
            $t->set_var('RSS_LINK_ICON', "&nbsp;<span class='feed'><a href='$urlAppend" . RSS . "'><img src='$themeimg/feed.png' alt='RSS Feed' title='RSS Feed' /></a></span>");
        }

        if ($perso_tool_content) {
            $t->set_var('LANG_MY_PERSO_LESSONS', $langMyPersoLessons);
            $t->set_var('LANG_MY_PERSO_DEADLINES', $langMyPersoDeadlines);
            $t->set_var('LANG_MY_PERSO_ANNOUNCEMENTS', $langMyPersoAnnouncements);
            $t->set_var('LANG_MY_PERSO_DOCS', $langMyPersoDocs);
            $t->set_var('LANG_MY_PERSO_AGENDA', $langMyPersoAgenda);
            $t->set_var('LANG_PERSO_FORUM', $langMyPersoForum);
            $t->set_var('LANG_MY_PERSONAL_CALENDAR', $langMyAgenda);
            
            $t->set_var('LESSON_CONTENT', $lesson_content);
            $t->set_var('ASSIGN_CONTENT', $assigns_content);
            $t->set_var('ANNOUNCE_CONTENT', $announce_content);
            $t->set_var('DOCS_CONTENT', $docs_content);
            $t->set_var('AGENDA_CONTENT', $agenda_content);
            $t->set_var('FORUM_CONTENT', $forum_content);
            $t->set_var('URL_PATH', $urlAppend);
            $t->set_var('TOOL_PATH', $urlAppend);
            $t->set_var('PERSONAL_CALENDAR_CONTENT', $personal_calendar_content);
        }

        $t->set_var('LANG_COPYRIGHT_NOTICE', $langCopyrightFooter);

        // Remove tool title block from selected pages
        if (defined('HIDE_TOOL_TITLE')) {
            $t->set_block('mainBlock', 'toolTitleBlock', 'toolTitleBlockVar');
            $t->set_var('toolTitleBlockVar', '');
        }

        //	At this point all variables are set and we are ready to send the final output
        //	back to the browser
        $t->parse('main', 'mainBlock', false);
        $t->pparse('Output', 'fh');
    }
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
    echo "<table border=1>\n";

    $Keys = array_keys($TheArray);
    foreach ($Keys as $OneKey) {
        echo "<tr>\n";
        echo "<td bgcolor='yellow'>";
        echo "<b>" . $OneKey . "</b>";
        echo "</td>\n";
        echo "<td bgcolor='#C4C2A6'>";
        if (is_array($TheArray [$OneKey]))
            print_a($TheArray [$OneKey]);
        else
            echo $TheArray [$OneKey];
        echo "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
}

/**
 * Function print_html_r
 *
 * Used for debugging purposes. Dumps array to browser
 *
 * @param array $arr
 */
function print_html_r($TheArray) {
    echo nl2br(eregi_replace(" ", " ", print_r($TheArray, TRUE)));
    echo "<br /><br />";
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
    if (strpos($path, '/course_info/restore_course.php') !== false) {
        return 'course_info/restore_course.php';
    } elseif (strpos($path, '/info/') !== false) {
        return preg_replace('|^.*(info/.*\.php)|', '\1', $path);
    } elseif (strpos($path, '/admin/') !== false) {
        return preg_replace('|^.*(/admin/.*)|', '\1', $path);
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
    }
    return preg_replace('|^.*modules/([^/]+)/.*$|', '\1', $path);
}


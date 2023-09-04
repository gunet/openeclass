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
use Jenssegers\Blade\Blade;
$navigation = array();
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

require_once 'template/template.inc.php';
require_once 'tools.php';

/**
 * @brief draws html content
 * @param $view_file
 * @param array $view_data
 */
function view($view_file, $view_data = array()) {
    global $webDir, $is_editor, $course_code, $course_id, $language, $siteName,
            $urlAppend, $urlServer, $theme, $pageName, $currentCourseName, $uid, $session,
            $require_help, $professor, $helpTopic, $helpSubTopic, $head_content, $toolName, $themeimg, $navigation,
            $require_current_course, $saved_is_editor, $require_course_admin, $is_course_admin,
            $require_editor, $langHomePage,
            $is_admin, $is_power_user, $is_departmentmanage_user, $is_usermanage_user, $leftsideImg;

    if (!isset($course_id) or !$course_id) {
        $course_id = $course_code = null;
    } else if ($course_id < 1) { // negative course_id might be set in common documents
        unset($course_id);
        unset($course_code);
    }

    $pageTitle = $siteName;
    $is_mobile = (isset($_SESSION['mobile']) && $_SESSION['mobile'] == true);
    $is_in_tinymce = false;

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
            // if (!isset($hideStart)) {
                array_push($breadcrumbs, $item);
                unset($item);
            //}
        }

        // Breadcrumb course home entry
        if (isset($course_code) and $menuTypeID != 3) {
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
    }

    // Add Theme Options styles
    $styles_str = '';
    $leftsideImg = '';
    $PositionFormLogin = 0;
    $eclass_banner_value = 1;
    
    $container = 'container';
    $theme_id = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : get_config('theme_options_id');

    $logo_img = $themeimg.'/eclass-new-logo.svg';
    $logo_img_small = $themeimg.'/eclass-new-logo.svg';

    //////////////////////////////////////////  Theme creation  ///////////////////////////////////////////////

    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);

        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
        //$styles_str .= ".menu-popover .delete.confirmAction{background: red;}";
        $styles_str .= " 
            body{
                background: #ffffff !important;
            }

            #btn-search{
                height:33px;
                width:30px;
                background-color:#ffffff;
                border-right: solid 1px #E8EDF8 !important;
            }

            .menu-item.active,
            .menu-item.active2{
                color: #2B3944;
            }

            .jumbotron-image-default{
                display: none;
            }

            .inputSearch{ height: 33px !important; margin-top: 0px !important;}

        "
        ;
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
        if (!empty($theme_options_styles['loginJumbotronBgColor']) && !empty($theme_options_styles['loginJumbotronRadialBgColor'])) {
            $gradient_str = "radial-gradient(closest-corner at 30% 60%, $theme_options_styles[loginJumbotronRadialBgColor], $theme_options_styles[loginJumbotronBgColor])";
            $styles_str .= "
                .jumbotron.jumbotron-login {
                     background: $gradient_str; 
                }
            ";
        }

        
        if (!empty($theme_options_styles['loginTextColor'])){
            $styles_str .= "
                .eclass-title, .eclassInfo{
                    color: $theme_options_styles[loginTextColor];
                }
            ";
        }
        
        if (isset($theme_options_styles['loginImg'])){
            $styles_str .= "
                .jumbotron.jumbotron-login { 
                    background-image: url('$urlThemeData/$theme_options_styles[loginImg]'), $gradient_str; 
                    border:0px; no-repeat center center fixed; 
                    -webkit-background-size: cover; 
                    -moz-background-size: cover; 
                    -o-background-size: cover; 
                    background-size: cover;
                }
            ";
        }
        if (isset($theme_options_styles['FormLoginPlacement']) && $theme_options_styles['FormLoginPlacement']=='center-position') {
            $PositionFormLogin = 1;
        }

        if (isset($theme_options_styles['fluidContainerWidth'])){
            $container = 'container-fluid';
            $styles_str .= ".container-fluid {max-width:$theme_options_styles[fluidContainerWidth]px}";
        }
        if (isset($theme_options_styles['openeclassBanner'])){
             $styles_str .= "#openeclass-banner {display: none;}";
             $eclass_banner_value = 0;
        }

       
        if (!empty($theme_options_styles['bgColorHeader'])) {
            $styles_str .= "
                #bgr-cheat-header {
                    background: $theme_options_styles[bgColorHeader];
                }

                .panel-admin>.panel-heading, .descCoursePanel, #cal-header{
                    background:$theme_options_styles[bgColorHeader];
                }

                div[role='progressbar'] {
                    --fg: $theme_options_styles[bgColorHeader];
                    --bg: #e8e8e8;
                }

                // .bgTheme{
                //     background-color: $theme_options_styles[bgColorHeader] !important;
                // }

                .nav-link-adminTools.normalColorBlueText.active{
                    background-color: $theme_options_styles[bgColorHeader];
                    color: #ffffff !important;
                }

                .input-StatusCourse:checked{
                    background-color: $theme_options_styles[bgColorHeader];
                }

                .circle-img-contant { 
                    background-color: $theme_options_styles[bgColorHeader];
                }

            ";
        }

        if (!empty($theme_options_styles['bgColorFooter'])) {
            $styles_str .= "
                .site-footer, .div_social{
                    background: $theme_options_styles[bgColorFooter];
                }
            ";
        }

        if (!empty($theme_options_styles['linkColorHeaderFooter'])){
            $styles_str .= "

                #bgr-cheat-header .form-value, #bgr-cheat-header .fa-bars{
                    color:$theme_options_styles[linkColorHeaderFooter];
                }

                .user-menu-btn .user-name,
                .user-menu-btn .fa-chevron-down,
                .loginText{
                    color: $theme_options_styles[linkColorHeaderFooter];
                }

                .menu-item{
                    color: $theme_options_styles[linkColorHeaderFooter];
                }

                .copyright, 
                .social-icon-tool, 
                .a_tools_site_footer {
                    color:$theme_options_styles[linkColorHeaderFooter];
                }

            ";
        }

        if (!empty($theme_options_styles['linkHoverColorHeaderFooter'])){
            $styles_str .= "

                .menu-item:hover,
                .menu-item:focus{
                    color: $theme_options_styles[linkHoverColorHeaderFooter];
                }

                .user-menu-btn:hover,
                .user-menu-btn:focus{
                    border-top: solid 4px $theme_options_styles[linkHoverColorHeaderFooter];
                }

                .user-menu-btn:hover .user-name,
                .user-menu-btn:focus .user-name{
                    color: $theme_options_styles[linkHoverColorHeaderFooter];
                }

                .user-menu-btn:hover .fa-chevron-down,
                .user-menu-btn:focus .fa-chevron-down{
                    color: $theme_options_styles[linkHoverColorHeaderFooter];
                }

                .copyright:hover, .copyright:focus,
                .social-icon-tool:hover, .social-icon-tool:focus,
                .a_tools_site_footer:hover, .a_tools_site_footer:focus {
                    color: $theme_options_styles[linkHoverColorHeaderFooter];
                }
                  
            ";
        }

        if (!empty($theme_options_styles['buttonBgWhiteColor'])) {
            $styles_str .= "
                // .submitAdminBtn, .opencourses_btn {
                //     border-color: $theme_options_styles[buttonBgWhiteColor];
                //     color: $theme_options_styles[buttonBgWhiteColor];
                // }

            ";
        }
  
        if (!empty($theme_options_styles['whiteButtonTextColor'])) {
            $styles_str .= "
                // .submitAdminBtn, .opencourses_btn, .searchGroupBtn {
                //     border-color: $theme_options_styles[whiteButtonTextColor];
                //     color: $theme_options_styles[whiteButtonTextColor];
                //     background-color: #ffffff;
                // }
                

                // .menu-popover .list-group-item{
                //     border-color: $theme_options_styles[whiteButtonTextColor];
                //     color: $theme_options_styles[whiteButtonTextColor];
                //     background-color: #ffffff;
                // }


            ";
        }
        
        if (!empty($theme_options_styles['whiteButtonHoveredTextColor'])) {
            $styles_str .= "
                // .submitAdminBtn:hover, .opencourses_btn:hover, .searchGroupBtn:hover,
                // .submitAdminBtn:focus, .opencourses_btn:focus, .searchGroupBtn:focus {
                //     border-color: $theme_options_styles[whiteButtonHoveredTextColor];
                //     color: $theme_options_styles[whiteButtonHoveredTextColor];
                // }

                // .menu-popover:focus{
                //     color: $theme_options_styles[whiteButtonHoveredTextColor];
                // }

                // .menu-popover .list-group-item:hover{
                //     border-color: $theme_options_styles[whiteButtonHoveredTextColor];
                //     color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                // }


            ";
        }
        
        if (!empty($theme_options_styles['whiteButtonHoveredBgColor'])) {
            $styles_str .= "
                // .submitAdminBtn:hover, .opencourses_btn:hover, .searchGroupBtn:hover,
                // .submitAdminBtn:focus, .opencourses_btn:focus, .searchGroupBtn:focus {
                //     background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                // }

                // .menu-popover:focus{
                //     background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                // }

                // .menu-popover .list-group-item:hover{
                //     background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                // }

            ";
        }
        
        
      
        if (!empty($theme_options_styles['buttonBgColor'])) {
            $styles_str .= "
                // .submitAdminBtn.active, submitAdminBtn.active:hover{
                //     background-color: $theme_options_styles[buttonBgColor];
                //     z-index: 0 !important;
                // }

                // .submitAdminBtnDefault, .submitAdminBtnDefault:hover{
                //     border-color: $theme_options_styles[buttonBgColor];
                //     background-color: $theme_options_styles[buttonBgColor];
                // }

                // .login-form-submit, .login-form-submit:hover {
                //     border-color: $theme_options_styles[buttonBgColor];
                //     background-color: $theme_options_styles[buttonBgColor];
                // }

            ";
        }

        if (!empty($theme_options_styles['buttonTextColor'])) {
            $styles_str .= "
                // .submitAdminBtn.active, .submitAdminBtn.active:hover{
                //     color: $theme_options_styles[buttonTextColor];
                // }

                // .submitAdminBtnDefault, .submitAdminBtnDefault:hover{
                //     color: $theme_options_styles[buttonTextColor];
                // }

                // .login-form-submit, .login-form-submit:hover{
                //     color: $theme_options_styles[buttonTextColor];
                // }

            ";
        }
        

        if (!empty($theme_options_styles['leftNavBgColor'])) {

            $aboutLeftForm = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $aboutLeftForm[3] = '0.1';
            $aboutLeftForm = 'rgba(' . implode(',', $aboutLeftForm) . ')';
            

            $rgba_no_alpha = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $rgba_no_alpha[3] = '1';
            $rgba_no_alpha = 'rgba(' . implode(',', $rgba_no_alpha) . ')';

            $styles_str .= " 

                // .left-form{ 
                //     background: linear-gradient(170deg, $aboutLeftForm 20%, rgba(255,255,255,1) 35%, rgba(232,232,232,1) 100%);
                // }

                .pagination-glossary .page-item.active .page-link{
                    background-color: $theme_options_styles[leftNavBgColor];
                    border-color: $theme_options_styles[leftNavBgColor];
                    color:#ffffff !important;
                }

                

            ";
        }
        
        if (!empty($theme_options_styles['linkColor'])){
           $styles_str .= "

                a, .toolAdminText, .announce-link-homepage{
                    color: $theme_options_styles[linkColor];
                }

                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaWeek-button.fc-state-active,
                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaDay-button.fc-state-active,
                .personal-calendar-header .btn-group .btn.active{
                    background:$theme_options_styles[linkColor] !important;
                }

                .lightBlueText { 
                    color:$theme_options_styles[linkColor];
                }
                  
                .bgLightBlue { 
                    background-color: $theme_options_styles[linkColor];
                }

                .Help-text-panel-heading, .normalColorBlueText {
                    color:  $theme_options_styles[linkColor] !important;
                }

                .list-group-default .list-group-btn {
                    color: $theme_options_styles[linkColor];
                }

                .nav-tabs .nav-item:hover .nav-link{
                    color: $theme_options_styles[linkColor];
                }

                .nav-tabs .nav-item .nav-link.active {
                    font-weight: 700;
                    font-style: normal;
                    letter-spacing: 0px;
                    line-height: 24px;
                    color: $theme_options_styles[linkColor];
                    font-size: 16px;
                    border: 0px;
                    background-color: transparent;
                    border-bottom: solid 2px $theme_options_styles[linkColor];
                }

                .contextual-sidebar .list-group-item.active {
                    color: $theme_options_styles[linkColor];
                }
                
            ";
        }

        if (!empty($theme_options_styles['linkHoverColor'])){
            $styles_str .= "
                a:hover, a:focus, 
                #bgr-cheat-header .form-value:hover, #bgr-cheat-header .form-value:focus{
                    color: $theme_options_styles[linkHoverColor];
                } 

                #btn-search:hover, #btn-search:focus{
                    color: $theme_options_styles[linkHoverColor];
                }

                .group-section .list-group-item .accordion-btn[aria-expanded='true'], 
                .group-section .list-group-item .accordion-btn.showAll {
                    color: $theme_options_styles[linkHoverColor];
                }

                .group-section .list-group-item .accordion-btn:hover {
                    text-decoration: none;
                    color: $theme_options_styles[linkHoverColor];
                }

                .searchGroupBtn:hover span{
                    color:$theme_options_styles[linkHoverColor];
                }

                .contextual-sidebar .list-group-item:hover {
                    color: $theme_options_styles[linkHoverColor];
                }

            ";
        }

        if (!empty($theme_options_styles['leftSubMenuFontColor'])){
            $styles_str .= "
                .toolSidebarTxt{
                    color: $theme_options_styles[leftSubMenuFontColor];
                }
            ";
        }


        if (!empty($theme_options_styles['leftSubMenuHoverBgColor'])){
            $styles_str .= "
                // #leftnav .panel a.list-group-item:hover{
                //     background: $theme_options_styles[leftSubMenuHoverBgColor];
                // } 
                
                // #leftnav .panel a.list-group-item.active:hover{
                //     background:#ccc;
                // }

            ";
        }

        if (!empty($theme_options_styles['leftSubMenuHoverFontColor'])){
            $styles_str .= "
                // #leftnav .panel a.list-group-item:hover{
                //     color: $theme_options_styles[leftSubMenuHoverFontColor];
                // }
                // #leftnav .panel .leftMenuToolCourse:hover .toolSidebarTxt{
                //     color: $theme_options_styles[leftSubMenuHoverFontColor];
                // }

            ";
        }

        if (!empty($theme_options_styles['leftMenuFontColor'])){
            $styles_str .= "
                // #leftnav .panel a.parent-menu{
                //     color: $theme_options_styles[leftMenuFontColor];
                // }

                // #leftnav .panel a.parent-menu span{
                //     color: $theme_options_styles[leftMenuFontColor];
                // }
                
            ";
        }

        if (!empty($theme_options_styles['leftMenuBgColor'])){
            $styles_str .= "
                // #leftnav .panel a.parent-menu{
                //     background: $theme_options_styles[leftMenuBgColor];
                // }

                // #leftnav .panel a.parent-menu .panel-title{
                //     background: $theme_options_styles[leftMenuBgColor];
                // }
                
            ";
        }

        if (!empty($theme_options_styles['leftMenuHoverFontColor'])){
            $styles_str .= "
                // #leftnav .panel .panel-sidebar-heading:hover{
                //     color: $theme_options_styles[leftMenuHoverFontColor];
                // }

                // #leftnav .panel .panel-sidebar-heading:hover span{
                //     color: $theme_options_styles[leftMenuHoverFontColor];
                // }
            ";
        }

        if (!empty($theme_options_styles['leftMenuSelectedFontColor'])){
            $styles_str .= "
                // #leftnav .panel a.parent-menu:not(.collapsed){
                //     color: $theme_options_styles[leftMenuSelectedFontColor];
                // }

                // #leftnav .panel:has(.show.Borders.Collapse0) #Tool0 span{
                //     color: $theme_options_styles[leftMenuSelectedFontColor];
                // }

                // #leftnav .panel:has(.show.Borders.Collapse1) #Tool1 span{
                //     color: $theme_options_styles[leftMenuSelectedFontColor];
                // }

                
            ";
        }

        if (isset($theme_options_styles['imageUpload'])){
            $logo_img =  "$urlThemeData/$theme_options_styles[imageUpload]";
        }

        if (isset($theme_options_styles['imageUploadSmall'])){
            $logo_img_small = "$urlThemeData/$theme_options_styles[imageUploadSmall]";
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

    $views = $webDir . '/resources/views/' . get_config('theme');
    $cache = $webDir . '/storage/views/' . get_config('theme');
    $blade = new Blade($views, $cache);

    $global_data = compact('is_editor', 'course_code', 'course_id', 'language',
            'pageTitle', 'urlAppend', 'urlServer', 'eclass_version', 'template_base', 'toolName',
            'container', 'uid', 'uname', 'is_embedonce', 'session', 'nextParam',
            'require_help', 'helpTopic', 'helpSubTopic', 'head_content', 'toolArr', 'module_id',
            'module_visibility', 'professor', 'pageName', 'menuTypeID', 'section_title',
            'messages', 'logo_img', 'logo_img_small', 'styles_str', 'breadcrumbs',
            'is_mobile', 'current_module_dir','search_action', 'require_current_course',
            'saved_is_editor', 'require_course_admin', 'is_course_admin', 'require_editor', 'sidebar_courses',
            'show_toggle_student_view', 'themeimg', 'currentCourseName', 'default_open_group',
            'is_admin', 'is_power_user', 'is_usermanage_user', 'is_departmentmanage_user', 'is_lti_enrol_user',
            'logo_url_path','leftsideImg','eclass_banner_value', 'is_in_tinymce', 'PositionFormLogin');
    $data = array_merge($global_data, $view_data);
    //echo '  '.get_config('theme').'  -  '.$view_file;
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
    $cache = $webDir . '/storage/views/' . get_config('theme');
    $blade = new Blade($views, $cache);

    $global_data = [];
    $data = array_merge($global_data, $view_data);
    return $blade->make($view_file, $data)->render();
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

function lang_selections_Mobile() {
    global $session, $native_language_names_init, $langDropdown;
    if (count($session->active_ui_languages) < 2) {
        return ('&nbsp;');
    }

    $lang_select = "
      <a class='btn btn-transparent d-flex justify-content-center align-items-center me-2' type='button' aria-expanded='false' href='#dropdownMenuLang' data-bs-toggle='dropdown'>
          <i class='fa-solid fa-earth-europe'></i>
      </a>
      <div class='m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border' aria-labelledby='dropdownMenuLang'>
      <ul class='list-group list-group-flush'>";
    foreach ($session->active_ui_languages as $code) {
        $class = ($code == $session->language)? ' class="active"': '';
        $lang_select .=
            "<li role='presentation'$class>
                <a class='list-group-item d-flex justify-content-start align-items-start py-3' role='menuitem' tabindex='-1' href='$_SERVER[SCRIPT_NAME]?localize=$code'>
                    <span class='fa-solid fa-earth-europe pe-2' aria-hidden='true'></span>
                    " .q($native_language_names_init[$code]) . "
                </a>
            </li>";
    }
    $lang_select .= "</ul></div>";
    return $lang_select;
}

/**
 * @brief displays lang selection box
 * @return string|void
 */
function lang_selections_Desktop() {

    global $session, $native_language_names_init;

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
                        <a class="form-value d-flex justify-content-end align-items-center" href="#" id="Dropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            '. $Selected_Language .'
                            <i class="fa-solid fa-chevron-down ps-2"></i> 
                        </a>
                        <div class="m-0 dropdown-menu dropdown-menu-end contextual-menu p-3 me-lg-0 me-md-5 me-0" role="menu" aria-labelledby="dropdownMenuLang">
                            <ul class="list-group list-group-flush">';
                            foreach ($session->active_ui_languages as $code) {
                                $class = ($code == $session->language)? ' class="active"': '';
                                $lang_select .=
                                    "<li role='presentation'$class>
                                        <a class='list-group-item py-3' role='menuitem' tabindex='-1' href='$_SERVER[SCRIPT_NAME]?localize=$code'>
                                            <i class='fa-solid fa-earth-europe'></i>
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
        strpos($path, 'newuseradmin.php') !== false or
        strpos($path, 'ldapnewprofadmin.php') !== false) {
        if (strpos($path, '?type=user') !== false) {
            return 'listreq-user';
        } else {
            return 'listreq';
        }
    }

    $original_path = $path;
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

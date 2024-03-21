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
    global $webDir, $is_editor, $is_course_reviewer, $course_code, $course_id, $language, $siteName,
            $urlAppend, $urlServer, $theme, $pageName, $currentCourseName, $uid, $session,
            $require_help, $professor, $helpTopic, $helpSubTopic, $head_content, $toolName, $themeimg, $navigation,
            $require_current_course, $saved_is_editor, $require_course_admin, $is_course_admin,
            $require_editor, $langHomePage,
            $is_admin, $is_power_user, $is_departmentmanage_user, $is_usermanage_user, $leftsideImg, 
            $tmp_pageName, $courseLicense, $loginIMG, $authCase, $authNameEnabled, $pinned_announce_id, $pinned_announce_title, $pinned_announce_body;

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
                                label: '". js_escape($GLOBALS['langClose']). "',
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

    //Get important admin announcement
    $pinned_announce_id = 0;
    $pinned_announce_title = '';
    $pinned_announce_body = '';
    $important_announce = Database::get()->queryArray("SELECT * FROM admin_announcement WHERE important = ?d AND visible = ?d",1,1);
    if(count($important_announce) > 0){
        foreach($important_announce as $an){
            $pinned_announce_id = $an->id;
            $pinned_announce_title = $an->title;
            $pinned_announce_body = $an->body;
        }
    }

    // Add Theme Options styles
    $styles_str = '';
    $leftsideImg = '';
    $image_footer = '';
    $PositionFormLogin = 0;
    $eclass_banner_value = 1;

    $container = 'container';
    $forms_image = 'form-image-modules';
    $theme_id = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : get_config('theme_options_id');

    $logo_img = $themeimg.'/eclass-new-logo.svg';
    $logo_img_small = $themeimg.'/eclass-new-logo.svg';
    $loginIMG = $themeimg.'/loginIMG.png';

    //////////////////////////////////////////  Theme creation  ///////////////////////////////////////////////

    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);

        $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;

        $styles_str .= " 

            #submitSearch{
                gap: 8px;
            }
            #search_terms{ 
                border-color: transparent;
                background-color: transparent;
            }
            .inputSearch::placeholder{
                background-color: transparent;
            }

            .form-wrapper.form-edit.form-create-theme{
                border: 0px;
            }

            .diffEqual {
                background-color: transparent !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover{
                color: #C44601;
            }

            .calendarViewDatesTutorGroup .fc-list-table .fc-list-heading .fc-widget-header {
                background: transparent;
            }
        
        ";

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BACKGROUND COLOR OF BRIEF PROFILE IN PORTOFOLIO /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BriefProfilePortfolioBgColor'])){
            $styles_str .= "
                .portfolio-profile-container{
                    background-color: $theme_options_styles[BriefProfilePortfolioBgColor];
                  }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// TEXT COLOR OF BRIEF PROFILE IN PORTOFOLIO ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BriefProfilePortfolioTextColor'])){
            $styles_str .= "
                .portofolio-text-intro{
                    color: $theme_options_styles[BriefProfilePortfolioTextColor] !important;
                  }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR OR BACKGROUND IMAGE OF BODY ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColor']) || !empty($theme_options_styles['bgImage'])) {
            $background_type = "";
            if (isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'stretch') {
                $background_type .= "background-size: 100% 100%;";
            } elseif(isset($theme_options_styles['bgType']) && $theme_options_styles['bgType'] == 'fix') {
                $background_type .= "background-size: 100% 100%;background-attachment: fixed;";
            }
            $bg_image = isset($theme_options_styles['bgImage']) ? " url('$urlThemeData/$theme_options_styles[bgImage]')" : "";
            $bg_color = isset($theme_options_styles['bgColor']) ? $theme_options_styles['bgColor'] : "";
            $styles_str .= "
                                body{
                                    background: $bg_color$bg_image;$background_type
                                }
                                .main-container,
                                .module-container{
                                    background-color: $bg_color;
                                }
                            ";
        }

        $gradient_str = 'radial-gradient(closest-corner at 30% 60%, #009BCF, #025694)';

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// BACKGROUND COLOR OF JUMBOTRON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['loginJumbotronBgColor']) && !empty($theme_options_styles['loginJumbotronRadialBgColor'])) {
            $gradient_str = "radial-gradient(closest-corner at 30% 60%, $theme_options_styles[loginJumbotronRadialBgColor], $theme_options_styles[loginJumbotronBgColor])";
            $styles_str .= "
                .jumbotron.jumbotron-login{
                    background: $gradient_str;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BACKGROUND IMAGE OF JUMBOTRON /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['loginImg'])){
                $styles_str .= "
                    .jumbotron.jumbotron-login{
                        background-image: url('$urlThemeData/$theme_options_styles[loginImg]'), $gradient_str; 
                        border:0px; 
                        background-position: no-repeat center center fixed; 
                        -webkit-background-size: cover; 
                        -moz-background-size: cover; 
                        -o-background-size: cover; 
                        background-size: cover;
                    }
                ";

        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// BACKGROUND IMAGE OF LOGIN FORM ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['loginImgL'])){
            $loginIMG =  "$urlThemeData/$theme_options_styles[loginImgL]";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// TEXT COLOR OF HOMEPAGE_INTRO ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['loginTextColor'])){
            $styles_str .= "
                .jumbotron-intro-text *{
                    color: $theme_options_styles[loginTextColor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR OF HOMEPAGE_INTRO ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['loginTextBgColor'])){
            $styles_str .= "
                .jumbotron-intro-text{
                    border-radius:8px;
                    padding: 5px 15px 15px 15px;
                    background-color: $theme_options_styles[loginTextBgColor];
                }
            ";
            // If jumbotron-intro-text has rgba which contains zero at the end (a) then change padding(left-right) to zero 
            preg_match_all('!\d+!', $theme_options_styles['loginTextBgColor'], $matches); 
            if(count($matches) > 0){
                $counterRgb = 0;
                foreach($matches as $match){
                    foreach($match as $value){
                        if(count($match) == 4 && $counterRgb == 3 && $value == 0){
                            $styles_str .= "
                                .jumbotron-intro-text{
                                    padding: 5px 0px 15px 0px;
                                }
                            ";
                        }
                        $counterRgb++;
                    }
                }
            }
             
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// POSITION OF LOGIN-FORM ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['FormLoginPlacement']) && $theme_options_styles['FormLoginPlacement']=='center-position') {
            $PositionFormLogin = 1;
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// FLUID OR BOXED SIZE OF PLATFORM ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['fluidContainerWidth'])){
            $container = 'container-fluid';
            $styles_str .= ".container-fluid {max-width:$theme_options_styles[fluidContainerWidth]px}";

            $styles_str .= "
            
                @media(min-width:1400px) and (max-width:1500px){
                    .main-section:has(.course-wrapper) .form-image-modules{
                        max-width: 450px;
                        float:right;
                    }
                }
                @media(min-width:1501px) and (max-width:1600px){
                    .main-section:has(.course-wrapper) .form-image-modules{
                        max-width: 470px;
                        float:right;
                    }
                }
                @media(min-width:1601px) and (max-width:1700px){
                    .main-section:has(.course-wrapper) .form-image-modules{
                        max-width: 490px;
                        float:right;
                    }
                }
                @media(min-width:1701px) and (max-width:1800px){
                    .main-section:has(.course-wrapper) .form-image-modules{
                        max-width: 510px;
                        float:right;
                    }
                }
                @media(min-width:1801px){
                    .main-section:has(.course-wrapper) .form-image-modules{
                        max-width: 530px;
                        float:right;
                    }
                }
                @media(min-width:1901px){
                    .main-section:has(.course-wrapper) .form-image-modules{
                        max-width: 550px;
                        float:right;
                    }
                }
                
                .main-section:not(:has(.course-wrapper)) .form-image-modules{
                    width:auto;
                } 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// SHOW - HIDE ECLASS_BANNER //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['openeclassBanner'])){
            $styles_str .= "#openeclass-banner {display: none;}";
            $eclass_banner_value = 0;
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////// TYPOGRAPHY ///////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ColorHyperTexts'])){
            $styles_str .= "
                body,
                h1,h2,h3,h4,h5,h6,
                p,strong,.li-indented,li,small,
                .Neutral-900-cl,
                .agenda-comment,
                .form-label, 
                .default-value,
                label,
                th,
                td,
                .panel-body,
                .card-body,
                div,
                .visibleFile,
                .list-group-item,
                .help-block,
                .control-label-notes,
                .title-default,
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    color:$theme_options_styles[ColorHyperTexts];
                }


                .dataTables_wrapper .dataTables_length, 
                .dataTables_wrapper .dataTables_filter, 
                .dataTables_wrapper .dataTables_info, 
                .dataTables_wrapper .dataTables_processing, 
                .dataTables_wrapper .dataTables_paginate {
                    color:$theme_options_styles[ColorHyperTexts] !important;
                }

                .circle-img-contant{
                    border: solid 1px $theme_options_styles[ColorHyperTexts];
                }

                .text-muted,
                .input-group-text{
                    color:$theme_options_styles[ColorHyperTexts] !important;
                }

                .c3-tooltip-container *{
                    background-color: #ffffff;
                    color: #2B3944;
                }

                .panel-default .panel-heading .panel-title, 
                .panel-action-btn-default .panel-heading .panel-title {
                    color:$theme_options_styles[ColorHyperTexts] ;
                }

                .panel-default .panel-heading, 
                .panel-action-btn-default .panel-heading {
                    color:$theme_options_styles[ColorHyperTexts] ;
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BACKGROUND-COLOR HEADER'S WRAPPER //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['BgColorWrapperHeader'])) {
            $styles_str .= "

                #bgr-cheat-header{ 
                    background-color: $theme_options_styles[BgColorWrapperHeader];
                }

                .offCanvas-Tools{
                    background: $theme_options_styles[BgColorWrapperHeader];
                }

                .navbar-learningPath,
                .header-container-learningPath{
                    background: $theme_options_styles[BgColorWrapperHeader];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR FOOTER'S WRAPPER /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColorWrapperFooter'])) {
            $styles_str .= "

                #bgr-cheat-footer,
                .div_social{
                    background-color: $theme_options_styles[bgColorWrapperFooter];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////// LINKS COLOR OF HEADER ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColorHeader'])){
            $styles_str .= "


                .link-selection-language,
                .link-bars-options,
                .user-menu-btn .user-name,
                .user-menu-btn .fa-chevron-down{
                    color: $theme_options_styles[linkColorHeader];
                }

                .container-items .menu-item{
                    color: $theme_options_styles[linkColorHeader];
                }

                #search_terms,
                #search_terms::placeholder{
                    color:$theme_options_styles[linkColorHeader];
                }

                #bgr-cheat-header .fa-magnifying-glass{
                    color:$theme_options_styles[linkColorHeader];
                }

                .header-login-text{
                    color:$theme_options_styles[linkColorHeader];
                }

                .header-mobile-link{
                    color:$theme_options_styles[linkColorHeader];
                }

                .split-left{
                    border-left: solid 1px $theme_options_styles[linkColorHeader];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF ACTIVE LINK HEADER //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['linkActiveBgColorHeader'])){
            $styles_str .= "
                .container-items .menu-item.active,
                .container-items .menu-item.active2 {
                    background-color: $theme_options_styles[linkActiveBgColorHeader];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// COLOR OF ACTIVE LINK HEADER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['linkActiveColorHeader'])){
            $styles_str .= "
                .container-items .menu-item.active,
                .container-items .menu-item.active2 {
                    color: $theme_options_styles[linkActiveColorHeader];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// COLOR OF HOVER LINK IN HEADER ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColorHeader'])){
            $styles_str .= "
                .link-selection-language:hover,
                .link-selection-language:focus,
                .link-bars-options:hover,
                .link-bars-options:focus,
                .container-items .menu-item:hover,
                .container-items .menu-item:focus{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                #bgr-cheat-header:not(:has(.fixed)) .user-menu-btn:hover,
                #bgr-cheat-header:not(:has(.fixed)) .user-menu-btn:focus{
                    border-top: solid 4px $theme_options_styles[linkHoverColorHeader];
                }

                .user-menu-btn:hover .user-name,
                .user-menu-btn:focus .user-name{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .user-menu-btn:hover .fa-chevron-down,
                .user-menu-btn:focus .fa-chevron-down{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .copyright:hover, .copyright:focus,
                .social-icon-tool:hover, .social-icon-tool:focus,
                .a_tools_site_footer:hover, .a_tools_site_footer:focus{
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                #bgr-cheat-header .fa-magnifying-glass:hover,
                #bgr-cheat-header .fa-magnifying-glass:focus {
                    color: $theme_options_styles[linkHoverColorHeader];
                }

                .header-login-text:hover,
                .header-login-text:focus{
                    color:$theme_options_styles[linkHoverColorHeader];
                }

                .header-mobile-link:hover,
                .header-mobile-link:focus{
                    color:$theme_options_styles[linkHoverColorHeader];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED COLOR TO ACTIVE LINK IN HEADER ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['HoveredActiveLinkColorHeader'])){
            $styles_str .= "

                .container-items .menu-item.active:hover,
                .container-items .menu-item.active:focus,
                .container-items .menu-item.active2:hover,
                .container-items .menu-item.active2:focus{
                    color: $theme_options_styles[HoveredActiveLinkColorHeader];
                } 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// SHADOW TO THE BOTTOM SIDE INTO HEADER /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['shadowHeader'])){
            $styles_str .= " 
                #bgr-cheat-header{ box-shadow: none; }
            ";
        }else{
            $styles_str .= " 
                #bgr-cheat-header{ box-shadow: 1px 2px 6px rgba(43,57,68,0.04); }
            "; 
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////// LINKS COLOR OF FOOTER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColorFooter'])){
            $styles_str .= "

                .container-items-footer .menu-item {
                    color: $theme_options_styles[linkColorFooter];
                }

                .copyright, 
                .social-icon-tool, 
                .a_tools_site_footer {
                    color:$theme_options_styles[linkColorFooter];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// COLOR OF HOVER LINK IN FOOTER ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColorFooter'])){
            $styles_str .= "

                .container-items-footer .menu-item:hover,
                .container-items-footer .menu-item:focus{
                    color: $theme_options_styles[linkHoverColorFooter];
                }

                .copyright:hover, .copyright:focus,
                .social-icon-tool:hover, .social-icon-tool:focus,
                .a_tools_site_footer:hover, .a_tools_site_footer:focus {
                    color: $theme_options_styles[linkHoverColorFooter];
                }

                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////// TEXT COLOR OF TABS /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link{
                    color: $theme_options_styles[clTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// HOVERED TEXT COLOR OF TABS /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link:hover{
                    color: $theme_options_styles[clHoveredTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// COLOR TEXT OF ACTIVE TABS //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clActiveTabs'])){
            $styles_str .= "
                .nav-tabs .nav-item .nav-link.active{
                    color: $theme_options_styles[clActiveTabs];
                    border-bottom: solid 2px $theme_options_styles[clActiveTabs];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// COLOR TEXT OF ACCORDIONS  //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn{
                    color: $theme_options_styles[clAccordions];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER BOTTOM COLOR TEXT OF ACCORDIONS  ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clBorderBottomAccordions'])){
            $styles_str .= "
                .group-section .list-group-item{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomAccordions];
                }

                .border-bottom-default{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// HOVERED COLOR TEXT OF ACCORDIONS  /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn:hover{
                    color: $theme_options_styles[clHoveredAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// COLOR TEXT OF ACTIVE ACCORDIONS  //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clActiveAccordions'])){
            $styles_str .= "
                .group-section .list-group-item .accordion-btn[aria-expanded='true'], 
                .group-section .list-group-item .accordion-btn.showAll{
                    color: $theme_options_styles[clActiveAccordions];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF LIST GROUP //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgLists'])){
            $styles_str .= "
                .list-group-item.list-group-item-action{
                    background-color: $theme_options_styles[bgLists];
                }
                .list-group-item.list-group-item-action:hover{
                    background-color: $theme_options_styles[bgLists];
                }

                .list-group-item.element{
                    background-color: $theme_options_styles[bgLists];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER BOTTOM OF LIST GROUP /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBottomLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomLists];
                }

                .profile-pers-info-row{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomLists];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// COLOR LINK OF LIST GROUP /////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action a,
                .list-group-item.element a{
                    color: $theme_options_styles[clLists];
                }

                .list-group-item.list-group-action a span,
                .list-group-item.element a span{
                    color: $theme_options_styles[clLists];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// HOVERED COLOR LINK OF LIST GROUP ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredLists'])){
            $styles_str .= "

                .list-group-item.list-group-item-action a:hover,
                .list-group-item.element a:hover{
                    color: $theme_options_styles[clHoveredLists];
                }

                .list-group-item.list-group-item-action a span:hover,
                .list-group-item.element a span:hover{
                    color: $theme_options_styles[clHoveredLists];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// ADD PADDING TO THE LIST GROUP /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['AddPaddingListGroup'])){
            $styles_str .= " 
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    padding-left: 15px;
                    padding-right: 15px;
                }

                .homepage-annnouncements-container .list-group-item.element{
                    padding-left: 0px;
                    padding-right: 0px;
                }
            ";
        }else{
            $styles_str .= " 
                .list-group-item.list-group-item-action,
                .list-group-item.element{
                    padding-left: 0px;
                    padding-right: 0px;
                }
            "; 
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR OF SECONDARY BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgWhiteButtonColor'])) {
            $styles_str .= "
                .submitAdminBtn, 
                .cancelAdminBtn,
                .opencourses_btn {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    background-color: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .btn-outline-primary {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .quickLink{
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .menu-popover{
                    background: $theme_options_styles[bgWhiteButtonColor];
                }

                .bs-placeholder.submitAdminBtn{
                    background: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .showSettings{
                    background: $theme_options_styles[bgWhiteButtonColor] !important;
                }

                .btn.btn-default {
                    background-color: $theme_options_styles[bgWhiteButtonColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button{
                    background-color:  $theme_options_styles[bgWhiteButtonColor];
                }

                .showCoursesBars,
                .showCoursesBars:hover,
                .showCoursesBars:focus,
                .showCoursesPics,
                .showCoursesPics:hover,
                .showCoursesPics:focus{
                    background-color:  $theme_options_styles[bgWhiteButtonColor];
                }

                .pagination-glossary .page-item .page-link{
                    background-color:  $theme_options_styles[bgWhiteButtonColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR OF SECONDARY BUTTON /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonTextColor'])) {
            $styles_str .= "
                .submitAdminBtn, 
                .cancelAdminBtn,
                .opencourses_btn {
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .btn-outline-primary {
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .submitAdminBtn .fa-solid::before, 
                .submitAdminBtn .fa-regular::before,
                .submitAdminBtn .fa-brands::before,
                .submitAdminBtn span.fa::before{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .quickLink{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .menu-popover{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .bs-placeholder .filter-option .filter-option-inner-inner {
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .showSettings{
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

                .btn.btn-default {
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button .fc-icon::after,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button .fc-icon::after{
                    color: $theme_options_styles[whiteButtonTextColor];
                }

                .pagination-glossary .page-item .page-link{
                    color: $theme_options_styles[whiteButtonTextColor] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR OF SECONDARY BUTTON ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonBorderTextColor'])) {
            $styles_str .= "
                .submitAdminBtn, 
                .cancelAdminBtn,
                .opencourses_btn {
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    border-color: $theme_options_styles[whiteButtonBorderTextColor] !important;
                }

                .btn-outline-primary {
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .quickLink{
                    border: solid 1px $theme_options_styles[whiteButtonBorderTextColor];
                }

                .menu-popover{
                    border: solid 1px $theme_options_styles[whiteButtonBorderTextColor];
                }

                .btn.btn-default {
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button{
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

                .pagination-glossary .page-item .page-link{
                    border-color: $theme_options_styles[whiteButtonBorderTextColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// HOVERED TEXT COLOR OF SECONDARY BUTTON ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonHoveredTextColor'])) {
            $styles_str .= "
                .submitAdminBtn:hover, 
                .cancelAdminBtn:hover,
                .opencourses_btn:hover,
                .submitAdminBtn:focus, 
                .cancelAdminBtn:focus,
                .opencourses_btn:focus {
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover {
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .btn-outline-primary:hover,
                .btn-outline-primary:focus{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .submitAdminBtn .fa-solid::before:hover, 
                .submitAdminBtn .fa-regular::before:hover,
                .submitAdminBtn .fa-brands::before:hover,
                .submitAdminBtn span.fa::before:hover{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .quickLink:hover,
                .quickLink:hover .fa-solid{
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .menu-popover:hover,
                .menu-popover:focus{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .bs-placeholder:hover .filter-option .filter-option-inner-inner {
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .showSettings:hover{
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }

                .btn.btn-default:hover,
                .btn.btn-default:focus {
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .showCoursesBars,
                .showCoursesBars:hover,
                .showCoursesBars:focus,
                .showCoursesPics,
                .showCoursesPics:hover,
                .showCoursesPics:focus{
                    color: $theme_options_styles[whiteButtonHoveredTextColor];
                }

                .pagination-glossary .page-item:hover .page-link{
                    color: $theme_options_styles[whiteButtonHoveredTextColor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// HOVERED BORDER COLOR OF SECONDARY BUTTON ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonHoveredBorderTextColor'])) {
            $styles_str .= "
                .submitAdminBtn:hover, 
                .cancelAdminBtn:hover,
                .opencourses_btn:hover,
                .submitAdminBtn:focus, 
                .cancelAdminBtn:focus,
                .opencourses_btn:focus {
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover {
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor] !important;
                }

                .btn-outline-primary:hover,
                .btn-outline-primary:focus{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .quickLink:hover,
                .quickLink:hover .fa-solid{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .menu-popover:hover,
                .menu-popover:focus{
                    border: solid 1px $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .showSettings:hover{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .btn.btn-default:hover,
                .btn.btn-default:focus {
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button:hover .fc-icon::after,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button:hover .fc-icon::after{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

                .pagination-glossary .page-item:hover .page-link{
                    border-color: $theme_options_styles[whiteButtonHoveredBorderTextColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// HOVERED BACKGROUND COLOR OF SECONDARY BUTTON //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['whiteButtonHoveredBgColor'])) {
            $styles_str .= "
                .submitAdminBtn:hover, 
                .cancelAdminBtn:hover,
                .opencourses_btn:hover,
                .submitAdminBtn:focus, 
                .cancelAdminBtn:focus,
                .opencourses_btn:focus {
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .btn-outline-primary:hover,
                .btn-outline-primary:focus{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .quickLink:hover,
                .quickLink:hover .fa-solid{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .menu-popover:hover,
                .menu-popover:focus{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .bs-placeholder.submitAdminBtn:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .showSettings:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor] !important;
                }

                .btn.btn-default:hover,
                .btn.btn-default:focus {
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-prev-button:hover,
                .calendarViewDatesTutorGroup .fc-header-toolbar .fc-button-group .fc-next-button:hover,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-prev-button:hover,
                .calendarAddDaysCl .fc-header-toolbar .fc-button-group .fc-next-button:hover,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-prev-button:hover,
                .bookingCalendarByUser .fc-header-toolbar .fc-button-group .fc-next-button:hover,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-prev-button:hover,
                .myCalendarEvents .fc-header-toolbar .fc-button-group .fc-next-button:hover{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }

                .pagination-glossary .page-item:hover .page-link{
                    background-color: $theme_options_styles[whiteButtonHoveredBgColor];
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR OF PRIMARY BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonBgColor'])) {
            $styles_str .= "
                .submitAdminBtn.active{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .login-form-submit{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .submitAdminBtnDefault, 
                input[type=submit], 
                button[type=submit]{
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .submitAdminBtnClassic.active {
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }

                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn {
                    border-color: $theme_options_styles[buttonBgColor] ;
                    background-color: $theme_options_styles[buttonBgColor] ;
                }
               

                .carousel-indicators>button.active {
                    border-color: tranparent;
                    background-color: $theme_options_styles[buttonBgColor];
                }


                .pagination-glossary .page-item.active .page-link {
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }
               
                .bootbox.show .modal-footer .submitAdminBtn, 
                .modal.show .modal-footer .submitAdminBtn {
                    border-color: $theme_options_styles[buttonBgColor];
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .btn.btn-primary{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    background-color: $theme_options_styles[buttonBgColor];
                }

                
                .searchGroupBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .myProfileBtn{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

                .showCoursesBars.active,
                .showCoursesPics.active{
                    background-color: $theme_options_styles[buttonBgColor];
                }

                .pagination-glossary .page-item.active .page-link{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }
                  

            ";

            $colorChevronLeftRight = "$theme_options_styles[buttonBgColor]";

            $FirstLeftSVG = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512'%3E%3Cpath fill='$colorChevronLeftRight' d='M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z'/%3E%3C/svg";
            $SecondLeftSVG = 'url("data:image/svg+xml,%3C' . $FirstLeftSVG .'%3E")';

            $FirstRightSVG = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512'%3E%3Cpath fill='$colorChevronLeftRight' d='M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z'/%3E%3C/svg";
            $SecondRightSVG = 'url("data:image/svg+xml,%3C' . $FirstRightSVG .'%3E")';

            $styles_str .= "
                .testimonials .slick-prev.slick-arrow {
                    background: $SecondLeftSVG no-repeat center;
                    background-size: contain;
                    height: 24px;
                    width: 24px;
                    border-radius: 50%;
                    z-index: 1;
                }

                .testimonials .slick-next.slick-arrow {
                    background: $SecondRightSVG no-repeat center;
                    background-size: contain;
                    height: 24px;
                    width: 24px;
                    border-radius: 50%;
                    z-index: 1;
                }

                .mce-btn{
                    background-color: $theme_options_styles[buttonBgColor] !important;
                }

                .personal-calendar-header .btn-group .btn.active{
                    background-color: $theme_options_styles[buttonBgColor] !important;
                }
            ";

            
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED BACKCKGROUND COLOR OF PRIMARY BUTTON /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonHoverBgColor'])) {
            $styles_str .= "
                
                submitAdminBtn.active:hover{
                border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }
                
                .login-form-submit:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }
                
                .submitAdminBtnDefault:hover,
                input[type=submit]:hover,
                button[type=submit]:hover{
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }
                
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn:hover, 
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor] ;
                    background-color: $theme_options_styles[buttonHoverBgColor] ;
                }
                
                .pagination-glossary .page-item.active .page-link:hover {
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }
            
                
                
                .bootbox.show .modal-footer .submitAdminBtn:hover, 
                .modal.show .modal-footer .submitAdminBtn:hover {
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .btn.btn-primary:hover{
                    border-color: $theme_options_styles[buttonHoverBgColor];
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .searchGroupBtn:hover{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }


                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn:hover{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

                .myProfileBtn:hover,
                .myProfileBtn:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                    border-color: $theme_options_styles[buttonHoverBgColor];
                }

                .showCoursesBars.active:hover,
                .showCoursesBars.active:focus,
                .showCoursesPics.active:hover,
                .showCoursesPics.active:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor];
                }

                .mce-btn:hover,
                .mce-btn:focus{
                    background-color: $theme_options_styles[buttonHoverBgColor] !important;
                }

                .personal-calendar-header .btn-group .btn.active:hover{
                    background-color: $theme_options_styles[buttonHoverBgColor] !important;
                }

                .pagination-glossary .page-item.active:hover .page-link{
                    background-color: $theme_options_styles[buttonBgColor];
                    border-color: $theme_options_styles[buttonBgColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR OF COLORFUL BUTTON //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['buttonTextColor'])) {
            $styles_str .= "
                .submitAdminBtn.active, 
                .submitAdminBtn.active:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .submitAdminBtnDefault, 
                .submitAdminBtnDefault:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .login-form-submit, 
                .login-form-submit:hover{
                    color: $theme_options_styles[buttonTextColor];
                }
                
                input[type=submit], 
                input[type=submit]:hover,
                button[type=submit],
                button[type=submit]:hover{
                    color: $theme_options_styles[buttonTextColor];
                }

                .submitAdminBtnClassic.active {
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn:hover, 
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn:hover {
                    color: $theme_options_styles[buttonTextColor];
                }
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-solid::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-solid::before,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-regular::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-regular::before,
                .form-wrapper:has(.submitAdminBtn) .submitAdminBtn .fa-brands::before,
                .form-horizontal:has(.submitAdminBtn) .submitAdminBtn .fa-brands::before{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .pagination-glossary .page-item.active .page-link,
                .pagination-glossary .page-item.active .page-link:hover {
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .bootbox.show .modal-footer .submitAdminBtn, 
                .bootbox.show .modal-footer .submitAdminBtn:hover, 
                .modal.show .modal-footer .submitAdminBtn,
                .modal.show .modal-footer .submitAdminBtn:hover {
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .btn.btn-primary,
                .btn.btn-primary:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .nav-link-adminTools.Neutral-900-cl.active{
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .submitAdminBtnDefault span,
                .submitAdminBtnDefault span:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .submitAdminBtnDefault .fa-solid::before, 
                .submitAdminBtnDefault .fa-solid::before:hover,
                .submitAdminBtnDefault .fa-regular::before,
                .submitAdminBtnDefault .fa-regular::before:hover,
                .submitAdminBtnDefault .fa-brands::before,
                .submitAdminBtnDefault .fa-brands::before:hover{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .searchGroupBtn span{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .wallWrapper:has(.submitAdminBtn) .submitAdminBtn{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .myProfileBtn,
                .myProfileBtn:hover,
                .myProfileBtn:focus{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                
                .showCoursesBars.active,
                .showCoursesBars.active:hover,
                .showCoursesBars.active:focus,
                .showCoursesPics.active,
                .showCoursesPics.active:hover,
                .showCoursesPics.active:focus{
                    color: $theme_options_styles[buttonTextColor] ;
                }

                .mce-btn,
                .mce-btn i{
                    color: $theme_options_styles[buttonTextColor] !important;
                }
                
                .personal-calendar-header .btn-group .btn.active{
                    color: $theme_options_styles[buttonTextColor] !important;
                }

                .pagination-glossary .page-item.active .page-link{
                    color: $theme_options_styles[buttonTextColor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND COLOR TO THE DELETION BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgDeleteButtonColor'])) {
            $styles_str .= "
                .deleteAdminBtn, 
                button[type=submit].deleteAdminBtn,
                input[type=submit].deleteAdminBtn {
                    border-color: $theme_options_styles[bgDeleteButtonColor];
                    background-color: $theme_options_styles[bgDeleteButtonColor];
                }

                .btn.btn-danger,
                .delete.confirmAction, 
                .delete.delete_btn{
                    border-color: $theme_options_styles[bgDeleteButtonColor];
                    background-color: $theme_options_styles[bgDeleteButtonColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO THE DELETION BUTTON /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clDeleteButtonColor'])) {
            $styles_str .= "
                .deleteAdminBtn, 
                button[type=submit].deleteAdminBtn,
                input[type=submit].deleteAdminBtn {
                    color: $theme_options_styles[clDeleteButtonColor];
                }

                .btn.btn-danger,
                .delete.confirmAction, 
                .delete.delete_btn{
                    color: $theme_options_styles[clDeleteButtonColor];
                }

                .deleteAdminBtn .fa-solid::before, 
                .deleteAdminBtn .fa-regular::before, 
                .deleteAdminBtn .fa-brands::before, 
                .deleteAdminBtn .fa::before{
                    color: $theme_options_styles[clDeleteButtonColor] !important;
                }
                  
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////// BACKGROUND HOVERED COLOR TO THE DELETION BUTTON ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredDeleteButtonColor'])) {
            $styles_str .= "
                .deleteAdminBtn:hover,
                button[type=submit].deleteAdminBtn:hover,
                input[type=submit].deleteAdminBtn:hover {
                    border-color: $theme_options_styles[bgHoveredDeleteButtonColor];
                    background-color: $theme_options_styles[bgHoveredDeleteButtonColor];
                }

                .btn.btn-danger:hover,
                .delete.confirmAction:hover, 
                .delete.delete_btn:hover{
                    border-color: $theme_options_styles[bgHoveredDeleteButtonColor];
                    background-color: $theme_options_styles[bgHoveredDeleteButtonColor];
                }
                  
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// TEXT HOVERED COLOR TO THE DELETION BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredDeleteButtonColor'])) {
            $styles_str .= "
                .deleteAdminBtn:hover,
                button[type=submit].deleteAdminBtn:hover,
                input[type=submit].deleteAdminBtn:hover {
                    color: $theme_options_styles[clHoveredDeleteButtonColor];
                }

                .btn.btn-danger:hover,
                .delete.confirmAction:hover, 
                .delete.delete_btn:hover{
                    color: $theme_options_styles[clHoveredDeleteButtonColor];
                }

                .deleteAdminBtn:hover .fa-solid::before, 
                .deleteAdminBtn:hover .fa-regular::before, 
                .deleteAdminBtn:hover .fa-brands::before, 
                .deleteAdminBtn:hover .fa::before{
                    color: $theme_options_styles[clHoveredDeleteButtonColor] !important;
                }
                  
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE SUCCESS BUTTON //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgSuccessButtonColor'])) {
            $styles_str .= "
                .successAdminBtn, 
                button[type=submit].successAdminBtn,
                input[type=submit].successAdminBtn {
                    border-color: $theme_options_styles[bgSuccessButtonColor];
                    background-color: $theme_options_styles[bgSuccessButtonColor];
                }

                .btn.btn-success{
                    border-color: $theme_options_styles[bgSuccessButtonColor];
                    background-color: $theme_options_styles[bgSuccessButtonColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO THE SUCCESS BUTTON //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clSuccessButtonColor'])) {
            $styles_str .= "
                .successAdminBtn, 
                button[type=submit].successAdminBtn,
                input[type=submit].successAdminBtn {
                    color: $theme_options_styles[clSuccessButtonColor];
                }

                .btn.btn-success{
                    color: $theme_options_styles[clSuccessButtonColor];
                }

                .successAdminBtn .fa-solid::before, 
                .successAdminBtn .fa-regular::before, 
                .successAdminBtn .fa-brands::before, 
                .successAdminBtn .fa::before{
                    color: $theme_options_styles[clSuccessButtonColor] !important;
                }
                  
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////// BACKGROUND HOVERED COLOR TO THE SUCCESS BUTTON ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredSuccessButtonColor'])) {
            $styles_str .= "
                .successAdminBtn:hover,
                button[type=submit].successAdminBtn:hover,
                input[type=submit].successAdminBtn:hover {
                    border-color: $theme_options_styles[bgHoveredSuccessButtonColor];
                    background-color: $theme_options_styles[bgHoveredSuccessButtonColor];
                }

                .btn.btn-danger:hover{
                    border-color: $theme_options_styles[bgHoveredSuccessButtonColor];
                    background-color: $theme_options_styles[bgHoveredSuccessButtonColor];
                }
                  
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// TEXT HOVERED COLOR TO THE SUCCESS BUTTON ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredSuccessButtonColor'])) {
            $styles_str .= "
                .successAdminBtn:hover,
                button[type=submit].successAdminBtn:hover,
                input[type=submit].successAdminBtn:hover {
                    color: $theme_options_styles[clHoveredSuccessButtonColor];
                }

                .btn.btn-danger:hover{
                    color: $theme_options_styles[clHoveredSuccessButtonColor];
                }

                .successAdminBtn:hover .fa-solid::before, 
                .successAdminBtn:hover .fa-regular::before, 
                .successAdminBtn:hover .fa-brands::before, 
                .successAdminBtn:hover .fa::before{
                    color: $theme_options_styles[clHoveredSuccessButtonColor] !important;
                }
                  
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO THE HELP BUTTON ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHelpButtonColor'])) {
            $styles_str .= "
                .helpAdminBtn {
                    border-color: $theme_options_styles[bgHelpButtonColor];
                    background-color: $theme_options_styles[bgHelpButtonColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// TEXT COLOR TO THE HELP BUTTON ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHelpButtonColor'])) {
            $styles_str .= "
                .helpAdminBtn {
                    color: $theme_options_styles[clHelpButtonColor];
                }

                .helpAdminBtn .fa-solid::before, 
                .helpAdminBtn .fa-regular::before, 
                .helpAdminBtn .fa-brands::before, 
                .helpAdminBtn .fa::before{
                    color: $theme_options_styles[clHelpButtonColor] !important;
                }  
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BACKGROUND HOVERED COLOR TO THE HELP BUTTON /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredHelpButtonColor'])) {
            $styles_str .= "
                .helpAdminBtn:hover {
                    border-color: $theme_options_styles[bgHoveredHelpButtonColor];
                    background-color: $theme_options_styles[bgHoveredHelpButtonColor];
                } 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// TEXT HOVERED COLOR TO THE HELP BUTTON ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredHelpButtonColor'])) {
            $styles_str .= "
                .helpAdminBtn:hover {
                    color: $theme_options_styles[clHoveredHelpButtonColor];
                }

                .helpAdminBtn:hover .fa-solid::before, 
                .helpAdminBtn:hover .fa-regular::before, 
                .helpAdminBtn:hover .fa-brands::before, 
                .helpAdminBtn:hover .fa::before{
                    color: $theme_options_styles[clHoveredHelpButtonColor] !important;
                }
                  
            ";
        }

        // Override button with white background if needed
        if (empty($theme_options_styles['whiteButtonTextColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic {
                    background-color:#ffffff;
                    border-color: #0073E6;
                    color: #0073E6;
                }
            ";
        }
        if (empty($theme_options_styles['whiteButtonHoveredTextColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover, 
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover {
                    background-color:#ffffff;
                    border-color: #0073E6;
                    color: #0073E6;
                }
            ";
        }
        if (empty($theme_options_styles['whiteButtonHoveredBgColor'])) {
            $styles_str .= "
                .form-wrapper:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover,
                .form-horizontal:has(.submitAdminBtnClassic) .submitAdminBtnClassic:hover{
                    border-color: #0073E6;
                    background-color: #ffffff;
                    color: #0073E6;
                }
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR CONTEXTUAL MENU ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgContextualMenu'])) {
            $styles_str .= "
                .contextual-menu{
                    background-color: $theme_options_styles[bgContextualMenu];
                }

                .contextual-menu-user::-webkit-scrollbar-track {
                    background-color: $theme_options_styles[bgContextualMenu];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// BORDER COLOR CONTEXTUAL MENU /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgBorderContextualMenu'])) {
            $styles_str .= "
                .contextual-menu{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }

                .contextual-menu-user{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }

                .contextual-border{
                    border: solid 1px $theme_options_styles[bgBorderContextualMenu];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// BACKGROUND COLOR TOOL CONTEXTUAL MENU /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgColorListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    background-color: $theme_options_styles[bgColorListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER BOTTOM COLOR TOOL CONTEXTUAL MENU /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clBorderBottomListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////// COLOR TOOL CONTEXTUAL MENU //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item,
                .contextual-menu button[type='submit'],
                .contextual-menu input[type='submit']{
                    color: $theme_options_styles[clListMenu];
                }

                .contextual-menu .list-group-item .settings-icons::before{
                    color: $theme_options_styles[clListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TOOL CONTEXTUAL MENU ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['bgHoveredListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item:hover,
                .contextual-menu button[type='submit']:hover
                .contextual-menu input[type='submit']:hover{
                    background-color: $theme_options_styles[bgHoveredListMenu];
                }
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// HOVERED COLOR TOOL CONTEXTUAL MENU ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clHoveredListMenu'])) {
            $styles_str .= "
                .contextual-menu .list-group-item:hover,
                .contextual-menu button[type='submit']:hover
                .contextual-menu input[type='submit']:hover{
                    color: $theme_options_styles[clHoveredListMenu];
                }
                .contextual-menu .list-group-item:hover .settings-icons::before{
                    color: $theme_options_styles[clHoveredListMenu];
                }
 
            ";
        }
  
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////// USERNAME COLOR CONTEXTUAL MENU /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenuUsername'])) {
            $styles_str .= "
                .contextual-menu-user .username-text,
                .contextual-menu-user .username-paragraph{
                    color:$theme_options_styles[clListMenuUsername];
                } 
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// LOGOUT COLOR CONTEXTUAL MENU //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenuLogout'])) {
            $styles_str .= "
                .contextual-menu-user .logout-list-item *{
                    color:$theme_options_styles[clListMenuLogout];
                } 
 
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// DELETE OPTION COLOR CONTEXTUAL MENU ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['clListMenuDeletion'])) {
            $styles_str .= "
                .contextual-menu .list-group-item:has(.fa-xmark),
                .contextual-menu .list-group-item:has(.fa-trash),
                .contextual-menu .list-group-item:has(.fa-eraser),
                .contextual-menu .list-group-item:has(.fa-times),
                .contextual-menu .list-group-item:has(.fa-xmark) .fa::before,
                .contextual-menu .list-group-item:has(.fa-trash) .fa::before,
                .contextual-menu .list-group-item:has(.fa-eraser) .fa::before,
                .contextual-menu .list-group-item:has(.fa-times) .fa::before{
                    color: $theme_options_styles[clListMenuDeletion] !important;
                }
 
            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgRadios'])){
            $styles_str .= "
                input[type='radio']{
                    background-color: $theme_options_styles[BgRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// BORDER COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderRadios'])){
            $styles_str .= "
                input[type='radio']{
                    border: solid 1px $theme_options_styles[BgBorderRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// TEXT COLOR TO RADIO COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClRadios'])){
            $styles_str .= "
                .radio label{ 
                    color: $theme_options_styles[ClRadios];
                } 

                input[type='radio']{
                    color:  $theme_options_styles[ClRadios];
                }

                .radio:not(:has(input[type='radio']:checked)) .help-block{
                    color: $theme_options_styles[ClRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND AND TEXT COLOR TO ACTIVE RADIO COMPONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgClRadios'])){
            $styles_str .= "
                input[type='radio']:checked { 
                    border: solid 6px $theme_options_styles[BgClRadios];
                }
                .input-StatusCourse:checked{
                    box-shadow: inset 0 0 0 0px #e8e8e8;
                    border: 0px solid #e8e8e8;
                    background-color: $theme_options_styles[BgClRadios];
                }
                .form-wrapper.form-edit label:has(input[type='radio']:checked){
                    color: $theme_options_styles[BgClRadios];
                }
                
                .radio label:has(input[type='radio']:checked),
                .radio:has(input[type='radio']:checked) .help-block{
                    color: $theme_options_styles[BgClRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// ICON COLOR TO ACTIVE RADIO COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClIconRadios'])){
            $styles_str .= "
                .radio:has(.input-StatusCourse:checked) .fa{
                    color: $theme_options_styles[ClIconRadios];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO INACTIVE RADIO COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClInactiveRadios'])){
            $styles_str .= "
                label:has(input[type='radio']:disabled){
                    color: $theme_options_styles[ClInactiveRadios];
                }
            ";
        }

        
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO CHECKBOX COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox'] {
                    background-color: $theme_options_styles[BgCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO CHECKBOX COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox'] {
                    border: 1px solid $theme_options_styles[BgBorderCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO CHECKBOX COMPOENENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClCheckboxes'])){
            $styles_str .= "
                .label-container {
                    color: $theme_options_styles[ClCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND COLOR TO ACTIVE CHECKBOX COMPONENT ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgActiveCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox']:checked {
                    border: 1px solid $theme_options_styles[BgActiveCheckboxes];
                    background-color: $theme_options_styles[BgActiveCheckboxes];
                }
                .label-container > input[type='checkbox']:active {
                    border: 1px solid $theme_options_styles[BgActiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// TEXT COLOR TO ACTIVE CHECKBOX COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClActiveCheckboxes'])){
            $styles_str .= "
                .label-container:has(input[type='checkbox']:checked),
                .label-container:has(input[type='checkbox']:checked) .fa{
                    color: $theme_options_styles[ClActiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// ICON COLOR TO ACTIVE CHECKBOX COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClIconCheckboxes'])){
            $styles_str .= "
                .label-container > input[type='checkbox']:checked + .checkmark::before {
                    color: $theme_options_styles[ClIconCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// TEXT COLOR TO INACTIVE CHECKBOX COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClInactiveCheckboxes'])){
            $styles_str .= "
                .label-container:has(input[type='checkbox']:disabled){
                    color: $theme_options_styles[ClInactiveCheckboxes];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO INPUT COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgInput'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .login-input,
                .login-input::placeholder,
                input[type='text'], 
                input[type='password'], 
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    background-color: $theme_options_styles[BgInput];
                }
                
                textarea,
                textarea.form-control{
                    background-color: $theme_options_styles[BgInput];
                }
                
                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {   
                    background-color: $theme_options_styles[BgInput];
                }

                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    background-color: $theme_options_styles[BgInput] !important;
                }
                
                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {   
                    background-color: $theme_options_styles[BgInput] !important;
                }

                .add-on,
                .add-on1,
                .add-on2{
                    background-color: $theme_options_styles[BgInput] !important;
                }

                .input-group-text.bg-input-default{
                    background-color: $theme_options_styles[BgInput];
                }

                .form-control:disabled {
                    background-color: $theme_options_styles[BgInput];
                }

                input:-webkit-autofill, 
                input:-webkit-autofill:hover, 
                input:-webkit-autofill:focus, 
                textarea:-webkit-autofill, 
                textarea:-webkit-autofill:hover, 
                textarea:-webkit-autofill:focus {
                    background-color: $theme_options_styles[BgInput];
                    -webkit-box-shadow: 0 0 0 30px $theme_options_styles[BgInput] inset !important;
                }
                

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BORDER COLOR TO INPUT COMPONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderInput'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .login-input,
                .login-input::placeholder,
                input[type='text'], 
                input[type='password'], 
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    border-color: $theme_options_styles[clBorderInput];
                }
                
                textarea,
                textarea.form-control{
                    border-color: $theme_options_styles[clBorderInput];
                }
                
                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {   
                    border-color: $theme_options_styles[clBorderInput];
                }

                input:-webkit-autofill, 
                input:-webkit-autofill:hover, 
                input:-webkit-autofill:focus, 
                textarea:-webkit-autofill, 
                textarea:-webkit-autofill:hover, 
                textarea:-webkit-autofill:focus {
                    border: 1px solid $theme_options_styles[clBorderInput];
                }


                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    border-color: $theme_options_styles[clBorderInput] !important;
                }

                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {   
                    border-color: $theme_options_styles[clBorderInput] !important;
                }

                .input-border-color {
                    border-color: $theme_options_styles[clBorderInput] ;
                }

                .form-control:disabled {
                    border-color: $theme_options_styles[clBorderInput] ;
                }
                

                .wallWrapper textarea:focus{
                    border-color: $theme_options_styles[clBorderInput] ;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO INPUT COMPONENT /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clInputText'])){
            $styles_str .= "
                input::placeholder,
                .form-control,
                .form-control::placeholder,
                .login-input::placeholder,
                .login-input,
                input[type='text'], 
                input[type='password'], 
                input[type='number'],
                input[type='search'],
                input[type='url'],
                input[type='email']{
                    color: $theme_options_styles[clInputText];
                }
                
                textarea,
                textarea::placeholder,
                textarea.form-control{
                    color: $theme_options_styles[clInputText];
                }
                
                input[type='text']:focus,
                input[type='datetime']:focus,
                input[type='datetime-local']:focus,
                input[type='date']:focus,
                input[type='month']:focus,
                input[type='time']:focus,
                input[type='week']:focus,
                input[type='number']:focus,
                input[type='email']:focus,
                input[type='url']:focus,
                input[type='search']:focus,
                input[type='tel']:focus,
                input[type='color']:focus,
                .form-control:focus,
                .uneditable-input:focus,
                textarea:focus,
                .login-input:focus {   
                    color: $theme_options_styles[clInputText];
                }

                input:-webkit-autofill, 
                input:-webkit-autofill:hover, 
                input:-webkit-autofill:focus, 
                textarea:-webkit-autofill, 
                textarea:-webkit-autofill:hover, 
                textarea:-webkit-autofill:focus {
                    -webkit-text-fill-color: $theme_options_styles[clInputText];
                }



                .dataTables_wrapper input::placeholder{
                    color: $theme_options_styles[clInputText] !important;
                }
                  
                .dataTables_wrapper input[type='text'],
                .dataTables_wrapper input[type='password'],
                .dataTables_wrapper input[type='email'],
                .dataTables_wrapper input[type='number'],
                .dataTables_wrapper input[type='url'],
                .dataTables_wrapper input[type='search']{
                    color: $theme_options_styles[clInputText] !important;
                }
                
                .dataTables_wrapper input[type='text']:focus,
                .dataTables_wrapper input[type='number']:focus,
                .dataTables_wrapper input[type='email']:focus,
                .dataTables_wrapper input[type='url']:focus,
                .dataTables_wrapper input[type='search']:focus,
                .dataTables_wrapper .form-control:focus,
                .dataTables_wrapper .uneditable-input:focus {   
                    color: $theme_options_styles[clInputText] !important;
                }

                .input-group-text .fa-calendar{
                    color: $theme_options_styles[clInputText];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO SELECT COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgSelect'])){
            $styles_str .= "

                select.form-select {
                    background-color: $theme_options_styles[BgSelect];
                }
              
                select.form-select:focus {
                    background-color: $theme_options_styles[BgSelect];
                }

                .dataTables_wrapper select {
                    background-color: $theme_options_styles[BgSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    background-color: $theme_options_styles[BgSelect] !important;;
                }


                .select2-selection.select2-selection--multiple{ 
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-dropdown--below {
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    background-color: $theme_options_styles[BgSelect] !important;
                }

                .select2-container--default .select2-results__option[aria-selected=false]{
                    background-color: $theme_options_styles[BgSelect] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO SELECT COMBONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderSelect'])){
            $styles_str .= "

                select.form-select {
                    border-color: $theme_options_styles[clBorderSelect];
                }
              
                select.form-select:focus {
                    border-color: $theme_options_styles[clBorderSelect];
                }

                .dataTables_wrapper select {
                    border-color: $theme_options_styles[clBorderSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    border-color: $theme_options_styles[clBorderSelect] !important;;
                }

                .select2-selection.select2-selection--multiple{ 
                    border-color: $theme_options_styles[clBorderSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    border: 1px solid $theme_options_styles[clBorderSelect] !important;
                }

                select:-webkit-autofill:hover, 
                select:-webkit-autofill:focus {
                    border: 1px solid $theme_options_styles[clBorderSelect];
                }

                .mce-floatpanel {
                    border: 1px solid $theme_options_styles[clBorderSelect] !important;;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// TEXT COLOR TO OPTION OF SELECT COMBONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clOptionSelect'])){
            $colorChevronDown = "$theme_options_styles[clOptionSelect]";
            $mySVG = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='$colorChevronDown' d='M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z'/%3E%3C/svg";
            $mysvg2 = 'url("data:image/svg+xml,%3C' . $mySVG .'%3E")';
            $styles_str .= "

                select.form-select {
                    color: $theme_options_styles[clOptionSelect];
                    background-image: $mysvg2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
              
                select.form-select:focus {
                    color: $theme_options_styles[clOptionSelect];
                }

                select.form-select option:not(:checked) {
                    color: $theme_options_styles[clOptionSelect];
                }

                .dataTables_wrapper select {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .dataTables_wrapper select:focus {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .dataTables_wrapper select option:not(:checked) {
                    color: $theme_options_styles[clOptionSelect] !important;;
                }

                .select2-selection.select2-selection--multiple{ 
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                .select2-selection--multiple:before {
                    border-top: 5px solid $theme_options_styles[clOptionSelect] !important;
                }

                .select2-container--default .select2-selection--multiple .select2-selection__choice {
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                select:-webkit-autofill:hover, 
                select:-webkit-autofill:focus {
                    -webkit-text-fill-color: $theme_options_styles[clOptionSelect];
                }

                .select2-container--default .select2-results__option[aria-selected=false]{
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                .mce-menu-item{
                    color: $theme_options_styles[clOptionSelect] !important;
                }

                .mce-menu-item .mce-text {
                    color: $theme_options_styles[clOptionSelect] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// HOVERED BACKGROUND COLOR TO OPTION OF SELECT COMBONENT /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgHoveredSelectOption'])){
            $styles_str .= "

                select.form-select option:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption];
                }

                .dataTables_wrapper select option:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;;
                }

                .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;
                }

                .select2-container--default .select2-results__option[aria-selected=false]:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;
                }

                .mce-menu-item:hover{
                    background-color: $theme_options_styles[bgHoveredSelectOption] !important;
                }

                

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// HOVERED TEXT COLOR TO OPTION OF SELECT COMBONENT ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredSelectOption'])){
            $styles_str .= "

                select.form-select option:hover{
                    color: $theme_options_styles[clHoveredSelectOption];
                }

                .dataTables_wrapper select option:hover{
                    color: $theme_options_styles[clHoveredSelectOption] !important;;
                }

                .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

                .mce-menu-item:hover{
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

                .mce-menu-item-normal.mce-active:hover .mce-text {
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

                .mce-menu-item:hover .mce-text {
                    color: $theme_options_styles[clHoveredSelectOption] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////// BACKGROUND COLOR TO ACTIVE OPTION OF SELECT COMBONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgOptionSelected'])){
            $styles_str .= "

                select.form-select option:checked{
                    background-color: $theme_options_styles[bgOptionSelected];
                }

                .dataTables_wrapper select option:checked{
                    background-color: $theme_options_styles[bgOptionSelected] !important;;
                }


                .select2-container--default .select2-results__option[aria-selected=true] {
                    background-color: $theme_options_styles[bgOptionSelected] !important;
                }

                .mce-menu-item-normal.mce-active {
                    background-color: $theme_options_styles[bgOptionSelected] !important;
                }

                .mce-menu-item:hover, 
                .mce-menu-item.mce-selected, 
                .mce-menu-item:focus {
                    background-color: $theme_options_styles[bgOptionSelected] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// TEXT COLOR TO ACTIVE OPTION OF SELECT COMBONENT ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clOptionSelected'])){
            $styles_str .= "

                select.form-select option:checked{
                    color: $theme_options_styles[clOptionSelected];
                }

                .dataTables_wrapper select option:checked{
                    color: $theme_options_styles[clOptionSelected] !important;;
                }


                .select2-container--default .select2-results__option[aria-selected=true] {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .mce-menu-item-normal.mce-active {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .mce-menu-item:hover, 
                .mce-menu-item.mce-selected, 
                .mce-menu-item:focus {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .mce-menu-item-normal.mce-active .mce-text {
                    color: $theme_options_styles[clOptionSelected] !important;
                }

                .mce-menu-item:hover .mce-text, 
                .mce-menu-item.mce-selected .mce-text {
                    color: $theme_options_styles[clOptionSelected] !important;
                }


            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO FORM COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgForms'])){
            $styles_str .= "
                .form-wrapper.form-edit { 
                    background-color: $theme_options_styles[BgForms];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// LABEL COLOR IN FORM COMPONENT /////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['clLabelForms'])){
            $styles_str .= "
                form label,
                form .form-label{
                    color:$theme_options_styles[clLabelForms];
                }

                .form-wrapper.form-edit .control-label-notes,
                .form-group .control-label-notes{ 
                    color:$theme_options_styles[clLabelForms];
                }
            ";
        }
        
            
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO MODAL COMPONONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['BgModal'])){
            $styles_str .= "
                .bootbox.show .bootbox-close-button{
                    background-color:$theme_options_styles[BgModal];
                }
                .modal.show .close{
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content {
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content-opencourses{
                    background:$theme_options_styles[BgModal];
                }
                .course-content::-webkit-scrollbar-track {
                    background-color: $theme_options_styles[BgModal];
                }
                .modal-content-opencourses .close{
                    background-color: $theme_options_styles[BgModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO MODAL COMPONONENT ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['clBorderModal'])){
            $styles_str .= "
                .modal-content {
                    border: 1px solid $theme_options_styles[clBorderModal];
                }
                .modal-content-opencourses{
                    border: solid 1px $theme_options_styles[clBorderModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR TO MODAL COMPONONENT ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['clTextModal'])){
            $styles_str .= "
                .bootbox.show .modal-header .modal-title, 
                .modal.show .modal-header .modal-title {
                    color:  $theme_options_styles[clTextModal];
                }
                .modal-content h1,
                .modal-content h2,
                .modal-content h3,
                .modal-content h4,
                .modal-content h5,
                .modal-content h6,
                .modal-content div,
                .modal-content small,
                .modal-content span,
                .modal-content p,
                .modal-content b,
                .modal-content strong,
                .modal-content li,
                .modal-content label,
                .modal-content{
                    color:  $theme_options_styles[clTextModal];
                }

                .bootbox.show .bootbox-body, 
                .modal.show .modal-body{
                    color: $theme_options_styles[clTextModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// ICON COLOR TO MODAL DELETION COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['clDeleteIconModal'])){
            $styles_str .= "
                .icon-modal-default .fa-trash-can.Accent-200-cl::before{
                    color: $theme_options_styles[clDeleteIconModal];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// ICON COLOR TO CLOSED MODAL COMPONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['clXmarkModal'])){
            $SVGmodalClose = "svg xmlns='http://www.w3.org/2000/svg' height='20' width='15' viewBox='0 0 384 512' fill='%23000'%3e%3cpath fill='$theme_options_styles[clXmarkModal]' d='M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z'/%3e%3c/svg";
            $SVGmodalClose2 = 'transparent url("data:image/svg+xml,%3C' . $SVGmodalClose .'%3E") center / 1em auto no-repeat';

            $styles_str .= "
                .bootbox.show .bootbox-close-button,
                .modal.show .close,
                .modal-display .close{
                    background: $SVGmodalClose2;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO AGENDA COMPONONENT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['bgAgenda'])){
            $styles_str .= "
                .panel-admin-calendar,
                .panel-admin-calendar>.panel-body-calendar {
                    background-color: $theme_options_styles[bgAgenda];
                }
                .myPersonalCalendar {
                    background-color: $theme_options_styles[bgAgenda];
                }

                

                .myPersonalCalendar .cal-row-fluid.cal-row-head {
                    background: $theme_options_styles[bgAgenda];
                }
                #cal-day-box .cal-day-hour:nth-child(odd) {
                    background-color: $theme_options_styles[bgAgenda] !important;
                }


                .datepicker-centuries .table-condensed,
                .datepicker-centuries .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-decades .table-condensed,
                .datepicker-decades .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-years .table-condensed,
                .datepicker-years .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-months .table-condensed,
                .datepicker-months .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datepicker-days .table-condensed,
                .datepicker-days .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                




                .datetimepicker-years .table-condensed,
                .datetimepicker-years .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-months .table-condensed,
                .datetimepicker-months .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-days .table-condensed,
                .datetimepicker-days .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-hours .table-condensed,
                .datetimepicker-hours .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                .datetimepicker-minutes .table-condensed,
                .datetimepicker-minutes .table-condensed .dow{ 
                    background-color: $theme_options_styles[bgAgenda];
                }
                

                
                .cal-day-today {
                    background-color: $theme_options_styles[bgAgenda] !important;
                }

                .datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top {
                    background-color: $theme_options_styles[bgAgenda];
                }

                .datetimepicker.datetimepicker-dropdown-bottom-right.dropdown-menu {
                    background-color: $theme_options_styles[bgAgenda];
                }
                 


                #cal-slide-content {
                    background: $theme_options_styles[bgAgenda] !important;
                    box-shadow: none !important;
                }


                .datetimepicker.dropdown-menu,
                .datepicker.dropdown-menu{
                    background: $theme_options_styles[bgAgenda] !important;
                }

                #cal-week-box{
                    background-color: $theme_options_styles[bgAgenda] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO OF AGENDA'S HEADER //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['BgColorHeaderAgenda'])){
            $styles_str .= "
                .panel-admin-calendar .panel-heading, 
                #cal-header {
                    background: $theme_options_styles[BgColorHeaderAgenda];
                }
                #calendar-header {
                    background: $theme_options_styles[BgColorHeaderAgenda];
                }




                .datepicker-centuries .table-condensed thead .prev,
                .datepicker-centuries .table-condensed thead .next,
                .datepicker-centuries .table-condensed thead .datepicker-switch,
                .datepicker-centuries .table-condensed thead .prev:hover,
                .datepicker-centuries .table-condensed thead .next:hover,
                .datepicker-centuries .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-decades .table-condensed thead .prev,
                .datepicker-decades .table-condensed thead .next,
                .datepicker-decades .table-condensed thead .datepicker-switch,
                .datepicker-decades .table-condensed thead .prev:hover,
                .datepicker-decades .table-condensed thead .next:hover,
                .datepicker-decades .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-years .table-condensed thead .prev,
                .datepicker-years .table-condensed thead .next,
                .datepicker-years .table-condensed thead .datepicker-switch,
                .datepicker-years .table-condensed thead .prev:hover,
                .datepicker-years .table-condensed thead .next:hover,
                .datepicker-years .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-months .table-condensed thead .prev,
                .datepicker-months .table-condensed thead .next,
                .datepicker-months .table-condensed thead .datepicker-switch,
                .datepicker-months .table-condensed thead .prev:hover,
                .datepicker-months .table-condensed thead .next:hover,
                .datepicker-months .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datepicker-days .table-condensed thead .prev,
                .datepicker-days .table-condensed thead .next,
                .datepicker-days .table-condensed thead .datepicker-switch,
                .datepicker-days .table-condensed thead .prev:hover,
                .datepicker-days .table-condensed thead .next:hover,
                .datepicker-days .table-condensed thead .datepicker-switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                



                .datetimepicker-years .table-condensed thead .prev,
                .datetimepicker-years .table-condensed thead .next,
                .datetimepicker-years .table-condensed thead .switch,
                .datetimepicker-years .table-condensed thead .prev:hover,
                .datetimepicker-years .table-condensed thead .next:hover,
                .datetimepicker-years .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-months .table-condensed thead .prev,
                .datetimepicker-months .table-condensed thead .next,
                .datetimepicker-months .table-condensed thead .switch,
                .datetimepicker-months .table-condensed thead .prev:hover,
                .datetimepicker-months .table-condensed thead .next:hover,
                .datetimepicker-months .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-days .table-condensed thead .prev,
                .datetimepicker-days .table-condensed thead .next,
                .datetimepicker-days .table-condensed thead .switch,
                .datetimepicker-days .table-condensed thead .prev:hover,
                .datetimepicker-days .table-condensed thead .next:hover,
                .datetimepicker-days .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-hours .table-condensed thead .prev,
                .datetimepicker-hours .table-condensed thead .next,
                .datetimepicker-hours .table-condensed thead .switch,
                .datetimepicker-hours .table-condensed thead .prev:hover,
                .datetimepicker-hours .table-condensed thead .next:hover,
                .datetimepicker-hours .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }
                .datetimepicker-minutes .table-condensed thead .prev,
                .datetimepicker-minutes .table-condensed thead .next,
                .datetimepicker-minutes .table-condensed thead .switch,
                .datetimepicker-minutes .table-condensed thead .prev:hover,
                .datetimepicker-minutes .table-condensed thead .next:hover,
                .datetimepicker-minutes .table-condensed thead .switch:hover{ 
                    background-color: $theme_options_styles[BgColorHeaderAgenda] !important;
                }

                .datepicker table tr td span.focused {
                    background: $theme_options_styles[BgColorHeaderAgenda] !important;
                }


                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR OF AGENDA'S HEADER //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['clColorHeaderAgenda'])){
            $styles_str .= "
                .panel-admin-calendar .panel-heading, #cal-header {
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

                #current-month,
                #cal-header .fa-chevron-left,
                #cal-header .fa-chevron-right {
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

                .text-agenda-title,
                .text-agenda-title:hover{
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

     

            



                .datepicker-centuries .table-condensed thead tr th.next::after,
                .datepicker-decades .table-condensed thead tr th.next::after,
                .datepicker-years .table-condensed thead tr th.next::after,
                .datepicker-months .table-condensed thead tr th.next::after,
                .datepicker-days .table-condensed thead tr th.next::after{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datepicker-centuries .table-condensed thead tr th.datepicker-switch,
                .datepicker-decades .table-condensed thead tr th.datepicker-switch,
                .datepicker-years .table-condensed thead tr th.datepicker-switch,
                .datepicker-months .table-condensed thead tr th.datepicker-switch,
                .datepicker-days .table-condensed thead tr th.datepicker-switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datepicker-centuries .table-condensed thead tr th.prev::before,
                .datepicker-decades .table-condensed thead tr th.prev::before,
                .datepicker-years .table-condensed thead tr th.prev::before,
                .datepicker-months .table-condensed thead tr th.prev::before,
                .datepicker-days .table-condensed thead tr th.prev::before{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }




                .datetimepicker-years .table-condensed thead .prev::before,
                .datetimepicker-years .table-condensed thead .next::after,
                .datetimepicker-years .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-months .table-condensed thead .prev::before,
                .datetimepicker-months .table-condensed thead .next::after,
                .datetimepicker-months .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-days .table-condensed thead .prev::before,
                .datetimepicker-days .table-condensed thead .next::after,
                .datetimepicker-days .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-hours .table-condensed thead .prev::before,
                .datetimepicker-hours .table-condensed thead .next::after,
                .datetimepicker-hours .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }
                .datetimepicker-minutes .table-condensed thead .prev::before,
                .datetimepicker-minutes .table-condensed thead .next::after,
                .datetimepicker-minutes .table-condensed thead .switch{ 
                    color: $theme_options_styles[clColorHeaderAgenda] !important;
                }

     
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////// TEXT COLOR OF AGENDA'S BODY ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['clColorBodyAgenda'])){
            $styles_str .= "
            
                .cal-row-fluid.cal-row-head .cal-cell1,
                .number-day-cal {
                    color: $theme_options_styles[clColorBodyAgenda];
                }

                .myPersonalCalendar .cal-row-fluid.cal-row-head .cal-cell1,
                .myPersonalCalendar .number-day-cal,
                .myPersonalCalendar .cal-day-hour div,
                #cal-day-box div,
                .cal-year-box div,
                .cal-month-box div,
                .cal-week-box div {
                    color: $theme_options_styles[clColorBodyAgenda];
                }

        


                .datepicker-centuries .table-condensed thead tr th.dow,
                .datepicker-decades .table-condensed thead tr th.dow,
                .datepicker-years .table-condensed thead tr th.dow,
                .datepicker-months .table-condensed thead tr th.dow,
                .datepicker-days .table-condensed thead tr th.dow{ 
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                .datepicker-centuries .table-condensed tbody tr td,
                .datepicker-decades .table-condensed tbody tr td,
                .datepicker-years .table-condensed tbody tr td,
                .datepicker-months .table-condensed tbody tr td,
                .datepicker-days .table-condensed tbody tr td{ 
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                




                .datetimepicker-years .table-condensed thead tr th.dow,
                .datetimepicker-months .table-condensed thead tr th.dow,
                .datetimepicker-days .table-condensed thead tr th.dow,
                .datetimepicker-hours .table-condensed thead tr th.dow,
                .datetimepicker-minutes .table-condensed thead tr th.dow{ 
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }
                .datetimepicker-years .table-condensed tbody tr td,
                .datetimepicker-months .table-condensed tbody tr td,
                .datetimepicker-days .table-condensed tbody tr td,
                .datetimepicker-hours .table-condensed tbody tr td,
                .datetimepicker-minutes .table-condensed tbody tr td{ 
                    color: $theme_options_styles[clColorBodyAgenda] !important;
                }


            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER COLOR TO AGENDA COMPONENT ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['BgBorderColorAgenda'])){
            $styles_str .= "
                .panel-admin-calendar,
                .panel-admin-calendar>.panel-body-calendar {
                    border-bottom: solid 1px $theme_options_styles[BgBorderColorAgenda];
                    border-left: solid 1px $theme_options_styles[BgBorderColorAgenda];
                    border-right: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }
                .panel-body-calendar {
                    margin-top: -0.7px;
                }
                .panel-admin-calendar .panel-heading{
                    border-top: solid 1px $theme_options_styles[BgBorderColorAgenda];
                    border-left: solid 1px $theme_options_styles[BgBorderColorAgenda];
                    border-right: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }
                #calendar_wrapper{
                    border: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }
                

                .fc-unthemed .fc-content, 
                .fc-unthemed .fc-divider, 
                .fc-unthemed .fc-list-heading td, 
                .fc-unthemed .fc-list-view, 
                .fc-unthemed .fc-popover, 
                .fc-unthemed .fc-row, 
                .fc-unthemed tbody, 
                .fc-unthemed td, 
                .fc-unthemed th, 
                .fc-unthemed thead {
                    border-color: $theme_options_styles[BgBorderColorAgenda];
                }

                .calendarViewDatesTutorGroup table,
                .calendarAddDaysCl table,
                .bookingCalendarByUser table,
                .myCalendarEvents table {
                    border-color:  $theme_options_styles[BgBorderColorAgenda];
                }

                .calendarViewDatesTutorGroup .fc-widget-header,
                .calendarAddDaysCl .fc-widget-header,
                .bookingCalendarByUser .fc-widget-header,
                .myCalendarEvents .fc-widget-header,
                .calendarViewDatesTutorGroup table .fc-head table thead tr th,
                .calendarAddDaysCl table .fc-head table thead tr th,
                .bookingCalendarByUser table .fc-head table thead tr th,
                .myCalendarEvents table .fc-head table thead tr th{
                    border-color:  $theme_options_styles[BgBorderColorAgenda];
                }
                .calendarViewDatesTutorGroup table .fc-head,
                .calendarAddDaysCl table .fc-head,
                .bookingCalendarByUser table .fc-head,
                .myCalendarEvents table .fc-head{
                    border-color:  $theme_options_styles[BgBorderColorAgenda];
                }

                .calendarViewDatesTutorGroup table .fc-body .fc-widget-content,
                .calendarAddDaysCl table .fc-body .fc-widget-content,
                .bookingCalendarByUser table .fc-body .fc-widget-content,
                .myCalendarEvents table .fc-body .fc-widget-content{
                    border-color:  $theme_options_styles[BgBorderColorAgenda];
                }

                .calendarViewDatesTutorGroup table .fc-body tbody tr td,
                .calendarAddDaysCl table .fc-body tbody tr td,
                .bookingCalendarByUser table .fc-body tbody tr td,
                .myCalendarEvents table .fc-body tbody tr td{
                    border-color: $theme_options_styles[BgBorderColorAgenda] ;
                }

                .calendarViewDatesTutorGroup table .fc-body tbody tr,
                .calendarAddDaysCl table .fc-body tbody tr,
                .bookingCalendarByUser table .fc-body tbody tr,
                .myCalendarEvents table .fc-body tbody tr{
                    border-color: $theme_options_styles[BgBorderColorAgenda] ;
                }

                .calendarViewDatesTutorGroup .fc-list-table  tbody tr {
                    border-bottom: solid 1px $theme_options_styles[BgBorderColorAgenda];
                }

                #cal-week-box{
                    border: 1px solid $theme_options_styles[BgBorderColorAgenda] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BORDER COLOR SLOTS TO AGENDA EVENTS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['BgBorderColorAgendaEvent'])){
            $styles_str .= "
                .calendarAddDaysCl .fc-body table tbody tr td.fc-axis,
                .calendarAddDaysCl .fc-body table tbody tr td{
                    border:solid 1px $theme_options_styles[BgBorderColorAgendaEvent] !important;
                }
                
                .myCalendarEvents .fc-body table tbody tr td.fc-axis,
                .myCalendarEvents .fc-body table tbody tr td{
                    border:solid 1px $theme_options_styles[BgBorderColorAgendaEvent] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TO AGENDA COMPONENT /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['bgColorHoveredBodyAgenda'])){
            $styles_str .= "
                .datetimepicker-years .table-condensed thead tr th:hover,
                .datetimepicker-years .table-condensed tbody tr td .year:hover,
                .datetimepicker-months .table-condensed thead tr th:hover,
                .datetimepicker-months .table-condensed tbody tr td .month:hover,
                .datetimepicker-days .table-condensed thead tr th:hover,
                .datetimepicker-days .table-condensed tbody tr td:hover,
                .datetimepicker-hours .table-condensed thead tr th:hover,
                .datetimepicker-hours .table-condensed tbody tr td .hour:hover,
                .datetimepicker-minutes .table-condensed thead tr th:hover,
                .datetimepicker-minutes .table-condensed tbody tr td .minute:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }



                .datepicker-centuries .table-condensed thead tr th:hover,
                .datepicker-decades .table-condensed thead tr th:hover,
                .datepicker-years .table-condensed thead tr th:hover,
                .datepicker-months .table-condensed thead tr th:hover,
                .datepicker-days .table-condensed thead tr th:hover{ 
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }
                .datepicker-centuries .table-condensed tbody tr td .century:hover,
                .datepicker-decades .table-condensed tbody tr td .decade:hover,
                .datepicker-years .table-condensed tbody tr td .year:hover,
                .datepicker-months .table-condensed tbody tr td .month:hover,
                .datepicker-days .table-condensed tbody tr td:hover{ 
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }


                .panel-body-calendar .cal-row-head:hover{
                    background-color: transparent !important;
                }
                .panel-body-calendar .cal-row-head .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }


                .panel-body-calendar .cal-row-fluid:hover{
                    background-color: transparent !important;
                }
                .panel-body-calendar .cal-row-fluid .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

                .myPersonalCalendar .cal-month-box .cal-row-fluid:hover{
                    background-color: transparent !important;
                }
                .myPersonalCalendar .cal-month-box .cal-row-fluid .cal-cell1:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

                .myPersonalCalendar .cal-year-box .row-fluid:hover,
                .myPersonalCalendar .cal-week-box .row-fluid:hover,
                #cal-day-box .row-fluid:hover{
                    background-color: transparent !important;
                }
                .myPersonalCalendar .cal-year-box .row-fluid div:hover,
                .myPersonalCalendar .cal-week-box .row-fluid div:hover,
                #cal-day-box .row-fluid div:hover{
                    background-color: $theme_options_styles[bgColorHoveredBodyAgenda] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT HOVERED COLOR TO AGENDA COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['clColorHoveredBodyAgenda'])){
            $styles_str .= "
                .datetimepicker-years .table-condensed thead tr th:hover,
                .datetimepicker-years .table-condensed tbody tr td .year:hover,
                .datetimepicker-months .table-condensed thead tr th:hover,
                .datetimepicker-months .table-condensed tbody tr td .month:hover,
                .datetimepicker-days .table-condensed thead tr th:hover,
                .datetimepicker-days .table-condensed tbody tr td:hover,
                .datetimepicker-hours .table-condensed thead tr th:hover,
                .datetimepicker-hours .table-condensed tbody tr td .hour:hover,
                .datetimepicker-minutes .table-condensed thead tr th:hover,
                .datetimepicker-minutes .table-condensed tbody tr td .minute:hover{
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }



                .datepicker-centuries .table-condensed thead tr th:hover,
                .datepicker-decades .table-condensed thead tr th:hover,
                .datepicker-years .table-condensed thead tr th:hover,
                .datepicker-months .table-condensed thead tr th:hover,
                .datepicker-days .table-condensed thead tr th:hover{ 
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }
                .datepicker-centuries .table-condensed tbody tr td .century:hover,
                .datepicker-decades .table-condensed tbody tr td .decade:hover,
                .datepicker-years .table-condensed tbody tr td .year:hover,
                .datepicker-months .table-condensed tbody tr td .month:hover,
                .datepicker-days .table-condensed tbody tr td:hover{ 
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }


                .panel-body-calendar .cal-row-head .cal-cell1:hover,
                .panel-body-calendar .cal-month-box .cal-cell1:hover div{
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }  


                .myPersonalCalendar .cal-cell1:hover div,
                .myPersonalCalendar .cal-cell:hover span{
                    color: $theme_options_styles[clColorHoveredBodyAgenda] !important;
                }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO ACTIVE DATETIME SLOT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['bgColorActiveDateTime'])){
            $styles_str .= "
                .datetimepicker table tr td span.active:active, 
                .datetimepicker table tr td span.active:hover:active, 
                .datetimepicker table tr td span.active.disabled:active, 
                .datetimepicker table tr td span.active.disabled:hover:active, 
                .datetimepicker table tr td span.active.active, 
                .datetimepicker table tr td span.active:hover.active, 
                .datetimepicker table tr td span.active.disabled.active, 
                .datetimepicker table tr td span.active.disabled:hover.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datepicker table tr td.active:active, 
                .datepicker table tr td.active.highlighted:active, 
                .datepicker table tr td.active.active, 
                .datepicker table tr td.active.highlighted.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datetimepicker table tr td.active:active, 
                .datetimepicker table tr td.active:hover:active, 
                .datetimepicker table tr td.active.disabled:active, 
                .datetimepicker table tr td.active.disabled:hover:active, 
                .datetimepicker table tr td.active.active, 
                .datetimepicker table tr td.active:hover.active, 
                .datetimepicker table tr td.active.disabled.active, 
                .datetimepicker table tr td.active.disabled:hover.active{
                    background-image: none !important;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

                .datepicker table tr td span.active:active, 
                .datepicker table tr td span.active:hover:active, 
                .datepicker table tr td span.active.disabled:active, 
                .datepicker table tr td span.active.disabled:hover:active, 
                .datepicker table tr td span.active.active, 
                .datepicker table tr td span.active:hover.active, 
                .datepicker table tr td span.active.disabled.active, 
                .datepicker table tr td span.active.disabled:hover.active {
                    color: #fff;
                    background-color: $theme_options_styles[bgColorActiveDateTime] !important;
                    border-color: $theme_options_styles[bgColorActiveDateTime] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO ACTIVE DATETIME SLOT /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        if(!empty($theme_options_styles['TextColorActiveDateTime'])){
            $styles_str .= "
                .datetimepicker table tr td span.active:active, 
                .datetimepicker table tr td span.active:hover:active, 
                .datetimepicker table tr td span.active.disabled:active, 
                .datetimepicker table tr td span.active.disabled:hover:active, 
                .datetimepicker table tr td span.active.active, 
                .datetimepicker table tr td span.active:hover.active, 
                .datetimepicker table tr td span.active.disabled.active, 
                .datetimepicker table tr td span.active.disabled:hover.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datepicker table tr td.active:active, 
                .datepicker table tr td.active.highlighted:active, 
                .datepicker table tr td.active.active, 
                .datepicker table tr td.active.highlighted.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datetimepicker table tr td.active:active, 
                .datetimepicker table tr td.active:hover:active, 
                .datetimepicker table tr td.active.disabled:active, 
                .datetimepicker table tr td.active.disabled:hover:active, 
                .datetimepicker table tr td.active.active, 
                .datetimepicker table tr td.active:hover.active, 
                .datetimepicker table tr td.active.disabled.active, 
                .datetimepicker table tr td.active.disabled:hover.active{
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datepicker table tr td span.active:active, 
                .datepicker table tr td span.active:hover:active, 
                .datepicker table tr td span.active.disabled:active, 
                .datepicker table tr td span.active.disabled:hover:active, 
                .datepicker table tr td span.active.active, 
                .datepicker table tr td span.active:hover.active, 
                .datepicker table tr td span.active.disabled.active, 
                .datepicker table tr td span.active.disabled:hover.active {
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

                .datepicker table tr td span.focused:active, 
                .datepicker table tr td span.focused:hover:active, 
                .datepicker table tr td span.focused.disabled:active, 
                .datepicker table tr td span.focused.disabled:hover:active, 
                .datepicker table tr td span.focused {
                    color: $theme_options_styles[TextColorActiveDateTime] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR OF COURSE LEFT MENU /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['leftNavBgColor'])) {

            $aboutLeftForm = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $aboutLeftForm[3] = '0.1';
            $aboutLeftForm = 'rgba(' . implode(',', $aboutLeftForm) . ')';


            $rgba_no_alpha = explode(',', preg_replace(['/^.*\(/', '/\).*$/'], '', $theme_options_styles['leftNavBgColor']));
            $rgba_no_alpha[3] = '1';
            $rgba_no_alpha = 'rgba(' . implode(',', $rgba_no_alpha) . ')';

            $styles_str .= " 

                .ContentLeftNav, #collapseTools{
                    background: $theme_options_styles[leftNavBgColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BACKGROUND COLOR TO TABLE COMPONENT //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgTables'])){
            $styles_str .= "

                #portfolio_lessons tbody tr{
                    background-color: $theme_options_styles[BgTables];
                }

                .table-default tbody tr td, 
                .announcements_table tbody tr td, 
                table.dataTable tbody tr td, 
                .table-default tbody tr th, 
                .announcements_table tbody tr th, 
                table.dataTable tbody tr th {
                    background-color: $theme_options_styles[BgTables];
                }

                thead,
                .title1 {
                    background-color: $theme_options_styles[BgTables];
                }

                .row-course:hover td:first-child, .row-course:hover td:last-child{
                    background-color: $theme_options_styles[BgTables];
                }

                table.dataTable.display tbody tr.odd, 
                table.dataTable.display tbody tr.odd > .sorting_1, 
                table.dataTable.order-column.stripe tbody tr.odd > .sorting_1, 
                table.dataTable.display tbody tr.even > .sorting_1, 
                table.dataTable.order-column.stripe tbody tr.even > .sorting_1 {
                    background-color: $theme_options_styles[BgTables] !important;
                }

                table.dataTable tbody tr {
                    background-color: $theme_options_styles[BgTables] !important;
                }

                .table-exercise-secondary {
                    background-color: $theme_options_styles[BgTables] ;
                }
                .table-exercise td, .table-exercise th {
                    background-color: transparent;
                }

                .user-details-exec{
                    background-color: $theme_options_styles[BgTables];
                }

                .border-bottom-table-head{
                    background-color: $theme_options_styles[BgTables] !important;
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BORDER BOTTOM COLOR TO TABLE'S ROWS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderBottomRowTables'])){
            $styles_str .= "
                .table-default tbody tr{
                    border-bottom: solid 1px $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable tbody td{
                    border-bottom: solid 1px $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable.no-footer {
                    border-bottom: 1px solid $theme_options_styles[BgBorderBottomRowTables] !important;
                }  
                .dataTables_wrapper.no-footer .dataTables_scrollBody {
                    border-bottom: 1px solid $theme_options_styles[BgBorderBottomRowTables] !important;
                }
                table.dataTable tfoot th, table.dataTable tfoot td {
                    border-top: 1px solid  $theme_options_styles[BgBorderBottomRowTables] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER BOTTOM COLOR TO TABLE'S THEAD /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderBottomHeadTables'])){
            $styles_str .= "
                thead, 
                tbody .list-header,
                tbody tr.header-pollAnswers,
                .border-bottom-table-head,
                thead tr.list-header td,
                tbody tr.list-header td,
                tbody tr.list-header th {
                    border-bottom: solid 2px $theme_options_styles[BgBorderBottomHeadTables] !important;
                }
                table.dataTable thead th, 
                table.dataTable thead td {
                    border-bottom: 1px solid $theme_options_styles[BgBorderBottomHeadTables] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO MENU-POPOVER COMPONENT /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgMenuPopover'])){
            $styles_str .= "
                .menu-popover.fade.show{ 
                    background: $theme_options_styles[BgMenuPopover];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER COLOR TO MENU-POPOVER COMPONENT ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderMenuPopover'])){
            $styles_str .= "
                .menu-popover.fade.show{ 
                    border: solid 1px $theme_options_styles[BgBorderMenuPopover];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO MENU-POPOVER OPTIONS ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{ 
                    background-color: $theme_options_styles[BgMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO MENU-POPOVER OPTIONS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{ 
                    color: $theme_options_styles[clMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER BOTTOM COLOR TO MENU-POPOVER OPTIONS ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBottomMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item{ 
                    border-bottom: solid 1px $theme_options_styles[clBorderBottomMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND HOVERED COLOR TO MENU-POPOVER OPTIONS /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgHoveredMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item:hover{ 
                    background-color: $theme_options_styles[BgHoveredMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT HOVERED COLOR TO MENU-POPOVER OPTIONS ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item:hover{ 
                    color: $theme_options_styles[clHoveredMenuPopoverOption];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// TEXT COLOR TO MENU-POPOVER DELETE OPTION //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clDeleteMenuPopoverOption'])){
            $styles_str .= "
                .menu-popover .list-group-item:has(.fa-xmark),
                .menu-popover .list-group-item:has(.fa-trash),
                .menu-popover .list-group-item:has(.fa-eraser),
                .menu-popover .list-group-item:has(.fa-times),
                .menu-popover .list-group-item:has(.fa-xmark) .fa::before,
                .menu-popover .list-group-item:has(.fa-trash) .fa::before,
                .menu-popover .list-group-item:has(.fa-eraser) .fa::before,
                .menu-popover .list-group-item:has(.fa-times) .fa::before,
                .menu-popover .list-group-item.warning-delete{
                    color: $theme_options_styles[clDeleteMenuPopoverOption] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// BACKGROUND COLOR TO THE TEXT EDITOR /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgTextEditor'])){
            $styles_str .= "
                .mce-container, 
                .mce-widget, 
                .mce-widget *, 
                .mce-reset {
                    background: $theme_options_styles[BgTextEditor] !important;
                }
                .mce-window .mce-container-body {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-tab.mce-active {
                    background: $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-tab {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  .mce-textbox {
                    background:  $theme_options_styles[BgTextEditor] !important;
                  }
                  i.mce-i-checkbox {
                    background-image: -webkit-linear-gradient(top,#fff,$theme_options_styles[BgTextEditor]) !important;
                  }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////// BORDER COLOR TO THE TEXT EDITOR //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgBorderTextEditor'])){
            $styles_str .= "
                .mce-panel {
                    border: solid 1px $theme_options_styles[BgBorderTextEditor] !important;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// TEXT COLOR TO THE TEXT EDITOR ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['ClTextEditor'])){
            $togglePNG = $urlServer . "js/tinymce/skins/light/img/toggle.png";
            $styles_str .= "

                .mce-toolbar .mce-btn i {
                    color: $theme_options_styles[ClTextEditor] !important;
                }
                
                .mce-menubtn span {
                    color: $theme_options_styles[ClTextEditor] !important;
                }
                .mce-btn i {
                    text-shadow: 0px 0px $theme_options_styles[ClTextEditor] !important;
                }

                .mce-container, .mce-container *, .mce-widget, .mce-widget *, .mce-reset {
                    color: $theme_options_styles[ClTextEditor] !important;
                }

                .mce-caret {
                    border-top: 4px solid $theme_options_styles[ClTextEditor] !important;
                }

                .mce-toolbar .mce-btn i.mce-i-none{
                    background-image: url('$togglePNG') !important;
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// BACKGROUND CONTAINER OF SCROLLBAR //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgScrollBar'])){
            $styles_str .= "
              .container-items::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .contextual-menu::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .course-content::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .panel-body::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }
              
              .table-responsive::-webkit-scrollbar-track,
              .dataTables_wrapper::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .chat-iframe::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }
              
              .bodyChat::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }

              .calendarViewDatesTutorGroup table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-track,
              .calendarAddDaysCl table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-track,
              .bookingCalendarByUser table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-track,
              .myCalendarEvents table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-track {
                background-color: $theme_options_styles[BgScrollBar];
              }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BACKGROUND COLOR TO THE SCROLLBAR COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorScrollBar'])){
            $styles_str .= "

              .container-items::-webkit-scrollbar-thumb{
                background: $theme_options_styles[BgColorScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-thumb{
                background: $theme_options_styles[BgColorScrollBar];
              }
            
              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
              
              .contextual-menu::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .course-content::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
 
              .panel-body::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
             
              .table-responsive::-webkit-scrollbar-thumb,
              .dataTables_wrapper::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }
              
              .chat-iframe::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .bodyChat::-webkit-scrollbar-thumb {
                background-color: $theme_options_styles[BgColorScrollBar];
              }

              .calendarViewDatesTutorGroup table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb,
              .calendarAddDaysCl table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb,
              .bookingCalendarByUser table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb,
              .myCalendarEvents table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb {
                 background-color: $theme_options_styles[BgColorScrollBar];
              }
            
              
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND HOVERED COLOR TO THE SCROLLBAR COMPONENT //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgHoveredColorScrollBar'])){
            $styles_str .= "

              .container-items::-webkit-scrollbar-thumb:hover{
                background: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .container-items-footer::-webkit-scrollbar-thumb:hover{
                background: $theme_options_styles[BgHoveredColorScrollBar];
              }
            
              .testimonial.slick-slide.slick-current.slick-active.slick-center .testimonial-body::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
              
              .contextual-menu::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .course-content::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
 
              .panel-body::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
             
              .table-responsive::-webkit-scrollbar-thumb:hover,
              .dataTables_wrapper::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
              
              .chat-iframe::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .jsmind-inner::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

              .bodyChat::-webkit-scrollbar-thumb:hover {
                background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }
              

              .calendarViewDatesTutorGroup table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb:hover,
              .calendarAddDaysCl table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb:hover,
              .bookingCalendarByUser table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb:hover,
              .myCalendarEvents table .fc-body .fc-widget-content .fc-scroller::-webkit-scrollbar-thumb:hover{
                    background-color: $theme_options_styles[BgHoveredColorScrollBar];
              }

            ";
        }


        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////// PROGRESSBAR COMPONENT ////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BackProgressBar']) && !empty($theme_options_styles['BgProgressBar']) && 
                            !empty($theme_options_styles['BgColorProgressBarAndText'])){

            $styles_str .= "
                .progress-circle-bar{
                    --size: 9rem;
                    --fg: $theme_options_styles[BgColorProgressBarAndText];
                    --bg: $theme_options_styles[BgProgressBar];
                    --pgPercentage: var(--value);
                    animation: growProgressBar 3s 1 forwards;
                    width: var(--size);
                    height: var(--size);
                    border-radius: 50%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    background: 
                        radial-gradient(closest-side, $theme_options_styles[BackProgressBar] 80%, transparent 0 99.9%, $theme_options_styles[BackProgressBar] 0),
                        conic-gradient(var(--fg) calc(var(--pgPercentage) * 1%), var(--bg) 0)
                        ;
                    font-weight: 700; font-style: normal;
                    font-size: calc(var(--size) / 5);
                    color: var(--fg);

                }

                .progress-bar {
                    background-color: $theme_options_styles[BgColorProgressBarAndText];
                }

                .progress-line{
                    background-color: $theme_options_styles[BgProgressBar];
                }
                .progress-line-bar{
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    overflow: hidden;
                    color: $theme_options_styles[BgProgressBar];
                    text-align: center;
                    white-space: nowrap;
                    background-color: $theme_options_styles[BgColorProgressBarAndText];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE TOOLTIP COMPONENT //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgColorTooltip'])){

            $styles_str .= "
                .tooltip.fade.show *{
                    background-color: $theme_options_styles[bgColorTooltip];

                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////// TEXT COLOR TO THE TOOLTIP COMPONENT ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorTooltip'])){

            $styles_str .= "
                .tooltip.fade.show *{
                    color: $theme_options_styles[TextColorTooltip];

                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR AND TEXT COLOR TO ALERT COMPONENT /////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgAlertInfo'])){
            $styles_str .= "
                .alert-info { 
                    background-color:$theme_options_styles[bgAlertInfo]; 
                }
            ";
        }
        if(!empty($theme_options_styles['clAlertInfo'])){
            $SVGbtnClose = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath fill='$theme_options_styles[clAlertInfo]' d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg";
            $SVGbtnClose2 = 'url("data:image/svg+xml,%3C' . $SVGbtnClose .'%3E")';
            $styles_str .= "
                .alert-info,
                .alert-info h1,
                .alert-info h2,
                .alert-info h3,
                .alert-info h4,
                .alert-info h5,
                .alert-info h6,
                .alert-info div,
                .alert-info small,
                .alert-info span,
                .alert-info p,
                .alert-info b,
                .alert-info strong,
                .alert-info li,
                .alert-info label{
                    color: $theme_options_styles[clAlertInfo] !important; 
                }

                .alert-info .btn-close{
                    background-image: $SVGbtnClose2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
            ";
        }

        if(!empty($theme_options_styles['bgAlertWarning'])){
            $styles_str .= "
                .alert-warning { 
                    background-color:$theme_options_styles[bgAlertWarning]; 
                }
            ";
        }
        if(!empty($theme_options_styles['clAlertWarning'])){
            $SVGbtnClose = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath fill='$theme_options_styles[clAlertWarning]' d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg";
            $SVGbtnClose2 = 'url("data:image/svg+xml,%3C' . $SVGbtnClose .'%3E")';
            $styles_str .= "
                .alert-warning,
                .alert-warning h1,
                .alert-warning h2,
                .alert-warning h3,
                .alert-warning h4,
                .alert-warning h5,
                .alert-warning h6,
                .alert-warning div,
                .alert-warning small,
                .alert-warning span,
                .alert-warning p,
                .alert-warning b,
                .alert-warning strong,
                .alert-warning li,
                .alert-warning label{
                    color: $theme_options_styles[clAlertWarning] !important; 
                }

                .alert-warning .btn-close{
                    background-image: $SVGbtnClose2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
            ";
        }

        if(!empty($theme_options_styles['bgAlertSuccess'])){
            $styles_str .= "
                .alert-success { 
                    background-color:$theme_options_styles[bgAlertSuccess]; 
                }
            ";
        }
        if(!empty($theme_options_styles['clAlertSuccess'])){
            $SVGbtnClose = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath fill='$theme_options_styles[clAlertSuccess]' d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg";
            $SVGbtnClose2 = 'url("data:image/svg+xml,%3C' . $SVGbtnClose .'%3E")';
            $styles_str .= "
                .alert-success,
                .alert-success h1,
                .alert-success h2,
                .alert-success h3,
                .alert-success h4,
                .alert-success h5,
                .alert-success h6,
                .alert-success div,
                .alert-success small,
                .alert-success span,
                .alert-success p,
                .alert-success b,
                .alert-success strong,
                .alert-success li,
                .alert-success label{
                    color: $theme_options_styles[clAlertSuccess] !important; 
                }

                .alert-success .btn-close{
                    background-image: $SVGbtnClose2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
            ";
        }

        if(!empty($theme_options_styles['bgAlertDanger'])){
            $styles_str .= "
                .alert-danger { 
                    background-color:$theme_options_styles[bgAlertDanger]; 
                }
            ";
        }
        if(!empty($theme_options_styles['clAlertDanger'])){
            $SVGbtnClose = "svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath fill='$theme_options_styles[clAlertDanger]' d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg";
            $SVGbtnClose2 = 'url("data:image/svg+xml,%3C' . $SVGbtnClose .'%3E")';
            $styles_str .= "
                .alert-danger,
                .alert-danger h1,
                .alert-danger h2,
                .alert-danger h3,
                .alert-danger h4,
                .alert-danger h5,
                .alert-danger h6,
                .alert-danger div,
                .alert-danger small,
                .alert-danger span,
                .alert-danger p,
                .alert-danger b,
                .alert-danger strong,
                .alert-danger li,
                .alert-danger label{
                    color: $theme_options_styles[clAlertDanger] !important; 
                }

                .alert-danger .btn-close{
                    background-image: $SVGbtnClose2;
                    background-repeat: no-repeat;
                    background-position: right 0.75rem center;
                    background-size: 16px 12px;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// LINKS COLOR OF PLATFORM ///////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkColor'])){
            $styles_str .= "

                a, .toolAdminText{
                    color: $theme_options_styles[linkColor];
                }


                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaWeek-button.fc-state-active,
                .myCalendarEvents .fc-header-toolbar .fc-right .fc-agendaDay-button.fc-state-active{
                    background:$theme_options_styles[linkColor] !important;
                }

                .Primary-600-cl,
                .Primary-500-cl {
                    color: $theme_options_styles[linkColor];
                }

                .Primary-500-bg {
                    background-color:  $theme_options_styles[linkColor];
                }

                .menu-item.active,
                .menu-item.active2{
                    color:  $theme_options_styles[linkColor];
                }
                  
                .portfolio-tools a{
                    color: $theme_options_styles[linkColor];
                }

                .nav-link-adminTools{
                    color: $theme_options_styles[linkColor];
                }

                #cal-slide-content a.event-item{
                    color: $theme_options_styles[linkColor] !important;
                }
                
                .dataTables_paginate.paging_simple_numbers span .paginate_button, 
                .dataTables_paginate.paging_full_numbers span .paginate_button{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                    color: $theme_options_styles[linkColor] !important;
                    background: transparent !important; 
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                    color: $theme_options_styles[linkColor] !important;
                    background: transparent !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button:active {
                    background: transparent !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.next:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.last:hover{
                    color: $theme_options_styles[linkColor] !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.next.disabled:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.last.disabled:hover{
                    color: $theme_options_styles[linkColor] !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.first:hover{
                    color: $theme_options_styles[linkColor] !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.previous.disabled:hover, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.first.disabled:hover{
                    color: $theme_options_styles[linkColor] !important;
                }
                  
                .dataTables_wrapper .dataTables_paginate .paginate_button.previous, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.first {
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.next, 
                .dataTables_wrapper .dataTables_paginate .paginate_button.last{
                    color: $theme_options_styles[linkColor] !important;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
                    color: $theme_options_styles[linkColor] !important;
                }

                .mycourses-pagination .page-item{
                    background-color: transparent !important;
                }
                
                .mycourses-pagination .page-item .page-link{
                    color: $theme_options_styles[linkColor] !important;
                    background-color: transparent !important;
                }
                
                .mycourses-pagination .page-item .page-link:hover{
                    background-color: transparent !important;
                    color: $theme_options_styles[linkColor] !important;
                }
                .mycourses-pagination .page-item .page-link.active:hover{
                    background-color: transparent !important;
                    color: $theme_options_styles[linkColor] !important;
                }
                .mycourses-pagination .page-item .page-link.active{
                    background-color: transparent !important;
                    color: $theme_options_styles[linkColor] !important;
                }
                
                .mycourses-pagination .page-item-pages{
                    background-color: transparent !important;
                }
                .mycourses-pagination .page-item-previous{
                    background-color: transparent !important;
                }
                .mycourses-pagination .page-item-next{
                    background-color: transparent !important;
                }

                .mycourses-pagination .page-item-next .page-link:hover,
                .mycourses-pagination .page-item-previous .page-link:hover{
                    background-color: transparent !important;
                }
                .mycourses-pagination .page-item-previous .page-link:hover,
                .mycourses-pagination .page-item-previous .page-link:hover{
                    background-color: transparent !important;
                }


                .commentPress:hover{
                    color: $theme_options_styles[linkColor];
                }


                #cal-slide-content a.event-item {
                    color: $theme_options_styles[linkColor];
                }


                .tree-units summary::before {
                    background: $theme_options_styles[linkColor] url(../../template/modern/img/units-expand-collapse.svg) 0 0;
                }

                .prev-next-learningPath{
                    color: $theme_options_styles[linkColor];
                }

                #leftTOCtoggler{
                    color: $theme_options_styles[linkColor];
                }

                .more-enabled-login-methods div{
                    color: $theme_options_styles[linkColor];
                }

                .ClickCourse,
                .ClickCourse:hover{
                    color: $theme_options_styles[linkColor];
                }

                .carousel-prev-btn,
                .carousel-prev-btn:hover,
                .carousel-next-btn,
                .carousel-next-btn:hover{
                    color: $theme_options_styles[linkColor];
                }

                .link-color{
                    color: $theme_options_styles[linkColor];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// HOVERED COLOR TO THE PLATFORM'S LINKS ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkHoverColor'])){
            $styles_str .= "
                a:hover, a:focus{
                    color: $theme_options_styles[linkHoverColor];
                } 

                #btn-search:hover, #btn-search:focus{
                    color: $theme_options_styles[linkHoverColor];
                }

                .portfolio-tools a:hover{
                    color: $theme_options_styles[linkHoverColor];
                }

                .nav-link-adminTools:hover{
                    color: $theme_options_styles[linkHoverColor];
                }

                .link-color:hover,
                .link-color:focus{
                    color: $theme_options_styles[linkHoverColor];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////// DELETE PLATFORM LINK COLOR ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['linkDeleteColor'])){
            $styles_str .= "
                .link-delete,
                .link-delete:hover,
                .link-delete:focus{
                    color: $theme_options_styles[linkDeleteColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// SETTINGS TO THE LEFT MENU OF COURSE /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (!empty($theme_options_styles['leftSubMenuFontColor'])){
            $styles_str .= "
                .toolSidebarTxt{
                    color: $theme_options_styles[leftSubMenuFontColor];
                }
            ";
        }

        if (!empty($theme_options_styles['leftMenuFontColor'])){
            $styles_str .= "
                #leftnav .panel a.parent-menu{
                    color: $theme_options_styles[leftMenuFontColor];
                }

                #leftnav .panel a.parent-menu span{
                    color: $theme_options_styles[leftMenuFontColor];
                }

                #leftnav .panel a.parent-menu .Tools-active-deactive{
                    color: $theme_options_styles[leftMenuFontColor];
                }
                
                #collapse-left-menu-icon path{
                    fill: $theme_options_styles[leftMenuFontColor] !important;
                }
                
            ";
        }

        if (!empty($theme_options_styles['leftMenuHoverFontColor'])){
            $styles_str .= "
                #leftnav .panel .panel-sidebar-heading:hover{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }

                #leftnav .panel .panel-sidebar-heading:hover span{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }

                #leftnav .panel .panel-sidebar-heading:hover .Tools-active-deactive{
                    color: $theme_options_styles[leftMenuHoverFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuFontColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item, .menu_btn_button .fa-bars{
                    color: $theme_options_styles[leftSubMenuFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuHoverFontColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item:hover{
                    color:$theme_options_styles[leftSubMenuHoverFontColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftSubMenuHoverBgColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item:hover{
                    background-color:$theme_options_styles[leftSubMenuHoverBgColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftMenuSelectedBgColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item.active {
                    background-color: $theme_options_styles[leftMenuSelectedBgColor];
                }
            ";
        }

        if(!empty($theme_options_styles['leftMenuSelectedLinkColor'])){
            $styles_str .= "
                .contextual-sidebar .list-group-item.active {
                    color: $theme_options_styles[leftMenuSelectedLinkColor];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////// UPLOAD LOGO ////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if (isset($theme_options_styles['imageUpload'])){
            $logo_img =  "$urlThemeData/$theme_options_styles[imageUpload]";
        }

        if (isset($theme_options_styles['imageUploadSmall'])){
            $logo_img_small = "$urlThemeData/$theme_options_styles[imageUploadSmall]";
        }

        if (isset($theme_options_styles['imageUploadFooter'])){
            $image_footer = "$urlThemeData/$theme_options_styles[imageUploadFooter]";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR OF HOMEPAGE ANNOUNCEMENTS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorAnnouncementHomepage'])){
            $styles_str .= "
                .homepage-annnouncements-container{
                    background-color: $theme_options_styles[BgColorAnnouncementHomepage];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// TEXT COLOR OF HOMEPAGE ANNOUNCEMENTS ////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['TextColorAnnouncementHomepage'])){
            $styles_str .= "
                .homepage-annnouncements-container .card h3,
                .homepage-annnouncements-container .card .Neutral-900-cl{
                    color: $theme_options_styles[TextColorAnnouncementHomepage];
                }
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND COLOR OF PORTFOLIO - COURSES CONTAINER ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgColorWrapperPortfolioCourses'])){
            $styles_str .= "
                .portfolio-courses-container {
                    background-color:$theme_options_styles[BgColorWrapperPortfolioCourses];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BACKGROUND COLOR OF COURSE CONTAINER (RIGHT COL) ////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['RightColumnCourseBgColor'])){
            $styles_str .= "
                .col_maincontent_active {
                    background-color:$theme_options_styles[RightColumnCourseBgColor];
                }

                @media(max-width:991px){
                    .module-container:has(.course-wrapper){
                        background-color:$theme_options_styles[RightColumnCourseBgColor];
                    }
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////// BORDER COLOR TO THE LEFT SIDE OF COURSE CONTAINER  //////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BorderLeftToRightColumnCourseBgColor'])){
            $styles_str .= "
                @media(min-width:992px){
                    .col_maincontent_active {
                        border-left: solid 1px $theme_options_styles[BorderLeftToRightColumnCourseBgColor];
                    }
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BACKGROUND COLOR TO THE PANEL'S BODY //////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgPanels'])){
            $styles_str .= "

                .panel-action-btn-default,
                .panel-primary,
                .panel-success,
                .panel-default,
                .panel-info,
                .panel-danger,
                .panel-admin,
                .card,
                .user-info-card,
                .panelCard,
                .cardLogin,
                .statistics-card,
                .bodyChat{
                    background-color:$theme_options_styles[BgPanels] ;
                }

                .wallWrapper{
                    background-color:$theme_options_styles[BgPanels] !important;
                }

                .testimonials .testimonial {
                    background: $theme_options_styles[BgPanels] ;
                }

                /* active testimonial */
                .testimonial.slick-slide.slick-current.slick-active.slick-center{
                    background-color: $theme_options_styles[BgPanels] ;
                }

                #lti_label{
                    background-color: $theme_options_styles[BgPanels] ;
                }

                #jsmind_container {
                    background: $theme_options_styles[BgPanels] !important;
                }

                .card-transparent,
                .card-transparent .card-header,
                .card-transparent .card-body,
                .card-transparent .card-footer,
                .card-transparent .panel-heading,
                .card-transparent .panel-body,
                .card-transparent .panel-footer{
                    background-color: transparent ;
                }

                .panel-default .panel-heading, 
                .panel-action-btn-default .panel-heading {
                    background: $theme_options_styles[BgPanels];
                }

                .admin-nav{
                    background: $theme_options_styles[BgPanels] !important;
                }
               
                
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////// BORDER COLOR TO THE PANELS //////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderPanels'])){
            $styles_str .= "

                .user-info-card,
                .form-homepage-login,
                .panelCard,
                .cardLogin,
                .border-card,
                .statistics-card,
                .panel-success,
                .panel-admin,
                .panel-default,
                .panel-danger,
                .panel-primary,
                .panel-info,
                .panel-action-btn-default{
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .panelCard.border-card-left-default {
                    border-left: solid 7px $theme_options_styles[clBorderPanels];
                }

                .border-top-default{
                    border-top: solid 1px $theme_options_styles[clBorderPanels];
                    border-left: none;
                    border-right: none;
                    border-bottom: none;
                }

                .BorderSolidDes{
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .wallWrapper{
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                .testimonials .testimonial {
                    border: solid 1px $theme_options_styles[clBorderPanels] ;
                }

                /* active testimonial */
                .testimonial.slick-slide.slick-current.slick-active.slick-center{
                    border: solid 1px $theme_options_styles[clBorderPanels] ;
                }

                #lti_label{
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                #jsmind_container {
                    border: solid 1px $theme_options_styles[clBorderPanels] !important;
                }

                .panel-default .panel-heading, 
                .panel-action-btn-default .panel-heading {
                    border: none;
                }

                .panel-default:has(.panel-heading) {
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

                .panel-default:not(:has(.panel-heading)){
                    border: solid 1px $theme_options_styles[clBorderPanels];
                }

            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE COMMENTS PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgCommentsPanels'])){
            $styles_str .= "
                .panelCard-comments{
                    background-color: $theme_options_styles[BgCommentsPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////// BORDER COLOR TO THE COMMENTS PANELS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderBgCommentsPanels'])){
            $styles_str .= "
                .panelCard-comments{
                    border: solid 1px $theme_options_styles[clBorderBgCommentsPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE QUESTIONNAIRE PANELS ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgQuestionnairePanels'])){
            $styles_str .= "
                .panelCard-questionnaire{
                    background-color: $theme_options_styles[BgQuestionnairePanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE QUESTIONNAIRE PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderQuestionnairePanels'])){
            $styles_str .= "
                .panelCard-questionnaire{
                    border: solid 1px $theme_options_styles[clBorderQuestionnairePanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////// BACKGROUND COLOR TO THE EXERCISE PANELS /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['BgExercisesPanels'])){
            $styles_str .= "
                .panelCard-exercise{
                    background-color: $theme_options_styles[BgExercisesPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BORDER COLOR TO THE EXERCISES PANELS ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clBorderExercisesPanels'])){
            $styles_str .= "
                .panelCard-exercise{
                    border: solid 1px $theme_options_styles[clBorderExercisesPanels];
                }
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////////// BACKGROUND COLOR TO THE CHAT CONTAINER /////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutChatContainer'])){
            $styles_str .= "
                .bodyChat{
                    background-color: $theme_options_styles[AboutChatContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////////// BORDER COLOR TO THE CHAT CONTAINER ///////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderChatContainer'])){
            $styles_str .= "
                .embed-responsive-item{
                    border: solid 1px $theme_options_styles[AboutBorderChatContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE COURSE INFO CONTAINER //////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutCourseInfoContainer'])){
            $styles_str .= "
                .card-course-info{
                    background-color: $theme_options_styles[AboutCourseInfoContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE COURSE INFO CONTAINER ////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderCourseInfoContainer'])){
            $styles_str .= "
                .card-course-info{
                    border: solid 1px $theme_options_styles[AboutBorderCourseInfoContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        //////////////// BACKGROUND COLOR TO THE COURSE UNITS CONTAINER /////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutUnitsContainer'])){
            $styles_str .= "
                .card-units{
                    background-color: $theme_options_styles[AboutUnitsContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////////// BORDER COLOR TO THE COURSE UNITS CONTAINER ///////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['AboutBorderUnitsContainer'])){
            $styles_str .= "
                .card-units{
                    border: solid 1px $theme_options_styles[AboutBorderUnitsContainer];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ////////////// BACKGROUND COLOR OF CONTAINER IMPORTANT ANNCOUNCEMENT ////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['bgContainerImportantAnnouncement'])){
            $styles_str .= "
                .notification-top-bar{
                    background: $theme_options_styles[bgContainerImportantAnnouncement];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// TEXT COLOR OF CONTAINER IMPORTANT ANNCOUNCEMENT ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clContainerImportantAnnouncement'])){
            $styles_str .= "
                .notification-top-bar *{
                    color: $theme_options_styles[clContainerImportantAnnouncement];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////////// LINK COLOR OF CONTAINER IMPORTANT ANNCOUNCEMENT ///////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clLinkImportantAnnouncement'])){
            $styles_str .= "
                .notification-top-bar a,
                .hide-notification-bar .fa-xmark{
                    color: $theme_options_styles[clLinkImportantAnnouncement];
                }
            
            ";
        }

        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        ///////////// HOVERED LINK COLOR OF CONTAINER IMPORTANT ANNCOUNCEMENT ///////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////

        if(!empty($theme_options_styles['clHoveredLinkImportantAnnouncement'])){
            $styles_str .= "
                .notification-top-bar a:hover,
                .hide-notification-bar:hover .fa-xmark{
                    color: $theme_options_styles[clHoveredLinkImportantAnnouncement];
                }
            
            ";
        }


        
        // Create .css file for the ($theme_id) in order to override the default.css file when it is necessary.
        if (isset($styles_str) && $styles_str){
            $fileStyleStr = $webDir . "/courses/theme_data/$theme_id/style_str.css";
            if(!file_exists($fileStyleStr)){
                file_put_contents($fileStyleStr,"");
            }else{
                file_put_contents($fileStyleStr,$styles_str);
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

    $views = $webDir . '/resources/views/' . get_config('theme');
    $cache = $webDir . '/storage/views/' . get_config('theme');
    $blade = new Blade($views, $cache);

    $global_data = compact('is_editor', 'is_course_reviewer', 'course_code', 'course_id', 'language',
            'pageTitle', 'urlAppend', 'urlServer', 'eclass_version', 'template_base', 'toolName',
            'container', 'uid', 'uname', 'is_embedonce', 'session', 'nextParam',
            'require_help', 'helpTopic', 'helpSubTopic', 'head_content', 'toolArr', 'module_id',
            'module_visibility', 'professor', 'pageName', 'menuTypeID', 'section_title',
            'messages', 'logo_img', 'logo_img_small', 'styles_str', 'breadcrumbs',
            'is_mobile', 'current_module_dir','search_action', 'require_current_course',
            'saved_is_editor', 'require_course_admin', 'is_course_admin', 'require_editor', 'sidebar_courses',
            'show_toggle_student_view', 'themeimg', 'currentCourseName', 'default_open_group',
            'is_admin', 'is_power_user', 'is_usermanage_user', 'is_departmentmanage_user', 'is_lti_enrol_user',
            'logo_url_path','leftsideImg','eclass_banner_value', 'is_in_tinymce', 'PositionFormLogin', 'tmp_pageName', 
            'courseLicense', 'loginIMG', 'image_footer', 'authCase', 'authNameEnabled', 'pinned_announce_id', 
            'pinned_announce_title', 'pinned_announce_body');
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
 * @brief
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

/**
 * @brief displays lang selection box
 * @return string|void
 */
function lang_selections_Desktop($idLanguage) {

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
                        <a class="d-flex justify-content-end align-items-center link-selection-language gap-2" href="#" id="'.$idLanguage.'" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Link to language">
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

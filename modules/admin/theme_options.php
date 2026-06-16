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

$require_departmentmanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
//Default Styles
$defaults = array(
                'rgba(255, 255, 255, 1)' => array('leftNavBgColor','bgColorContainerPortfolioInfo', 'leftNavBgColorSmallScreen','bgColor','buttonTextColor','textColorPortfolioButtons', 'bgColorContentPlatform', 'clAlertInfo', 'clAlertWarning', 'clAlertSuccess', 'clAlertDanger',
                                                    'whiteButtonHoveredBgColor','BgColorWrapperHeader', 'BgColorContainerLogo', 'bgColorWrapperFooter', 'clLinkAlertInfo', 'clLinkAlertWarning', 'clLinkAlertSuccess', 'clLinkAlertDanger',
                                                    'BgColorWrapperPortfolioCourses', 'RightColumnCourseBgColor', 'BgPanels', 'BgCommentsPanels', 'BgQuestionnairePanels', 'BgReportsPanels', 'BgProgressActivitiesPanels', 'BgExercisesPanels', 'BgForms', 'BgTables', 'bgLists' ,
                                                    'bgContextualMenu', 'bgColorListMenu', 'bgWhiteButtonColor', 'BgRadios', 'ClIconRadios', 'BgCheckboxes', 'ClIconCheckboxes',
                                                    'BgInput', 'BgSelect' ,'clHoveredSelectOption' ,'clOptionSelected', 'BgModal', 'bgAgenda', 'clColorHeaderAgenda',
                                                    'BgMenuPopover', 'BgMenuPopoverOption', 'BgTextEditor', 'BgScrollBar' ,'BackProgressBar', 'TextColorActiveDateTime', 'TextColorTooltip', 'clDeleteButtonColor',
                                                    'clHoveredDeleteButtonColor', 'clSuccessButtonColor', 'clHoveredSuccessButtonColor', 'clHelpButtonColor', 'clHoveredHelpButtonColor', 'BgBorderForms',
                                                    'BgColorAnnouncementHomepageLink','clBadgeSuccess','clBadgeWarning','clBadgeNeutral','clBadgePrimary','clBadgeAccent', 'BoxShadowPanels', 'AboutChatContainerBoxShadow', 'AboutCourseInfoContainerBoxShadow', 'AboutUnitsContainerBoxShadow', 'FormsBoxShadow',
                                                    'BoxShadowRowTables', 'bgPanelEvents', 'bgColorContainerPortfolioButtons', 'bgBorderHoveredPanels', 'BgColorStatisticsHomepage', 'BgColorPopularCoursesHomepage', 'BgColorTextsHomepage', 'BgColorStatisticsHomepage_gr', 'BgColorPopularCoursesHomepage_gr', 'BgColorTextsHomepage_gr', 'bgCardAnnouncementDate', 'bgColorBreadcrumb', 'BorderColorBreadcrumb', 'boxShadowInputSelect', 'BgColorCardLogin'),
                'rgba(247, 249, 254, 1)' => array('BriefProfilePortfolioBgColor', 'bgColorSectionPortfolioBtns', 'BriefProfilePortfolioBgColor_gr', 'loginJumbotronRadialBgColor','loginJumbotronBgColor','bgRadialWrapperJumbotron','BgColorAnnouncementHomepage', 'BgColorAnnouncementHomepage_gr', 'AboutUnitsContainer', 'AboutCourseInfoContainer'),
                'rgb(0, 115, 230, 1)' => array('leftMenuFontColor','buttonBgColor', 'bgColorPortfolioButtons', 'whiteButtonTextColor','whiteButtonBorderTextColor', 'whiteButtonHoveredTextColor', 'whiteButtonHoveredBorderTextColor', 'BgClRadios', 'BgActiveCheckboxes', 'clHoveredMenuPopoverOption', 'clLinkImportantAnnouncement'),
                'rgba(43, 57, 68, 1)' => array('linkColorHeader','linkColorFooter','loginTextColor', 'leftSubMenuFontColor','ColorHyperTexts', 'clLabelForms', 'clListMenuUsername',
                                                'clListMenu', 'BriefProfilePortfolioTextColor', 'ClRadios', 'ClCheckboxes', 'ClActiveCheckboxes', 'clTextModal',
                                                'BgColorHeaderAgenda', 'clMenuPopoverOption', 'bgColorTooltip', 'TextColorAnnouncementHomepage','BgBadgeNeutral', 'clHoveredTextPanels', 'TextColorStatisticsHomepage', 'TextColorPopularCoursesHomepage', 'TextColorTextsHomepage', 'TextColorCardAnnouncementDate', 'textColorCardLogin'),
                'rgba(0, 115, 230, 1)' => array('linkColor','linkHoverColorHeader','linkHoverColorFooter','linkCopyrightColorFooter', 'linkCopyrightHoverColorFooter', 'leftSubMenuHoverFontColor','linkActiveColorHeader',
                                                'clHoveredTabs', 'clActiveTabs', 'clHoveredAccordions', 'clActiveAccordions', 'clLists', 'clHoveredLists', 'bgHoveredSelectOption',
                                                'bgOptionSelected', 'BgBorderBottomHeadTables', 'HoveredActiveLinkColorHeader', 'BgColorProgressBarAndText', 'clLinkImportantAnnouncement',
                                                'clColorAnnouncementHomepageLinkElement','clHoveredColorAnnouncementHomepageLinkElement', 'ColorBlueText', 'ColorFocus', 'linkColorCardLogin'),
                'rgba(0, 115, 230, 0.7)' => array('buttonHoverBgColor','bgHoverColorPortfolioButtons', 'clHoveredLinkImportantAnnouncement', 'linkHoverColorCardLogin'),
                "rgba(77,161,228,1)" => array('leftMenuSelectedFontColor', 'leftMenuHoverFontColor'),
                "rgba(239, 246, 255, 1)" => array('leftSubMenuHoverBgColor','leftMenuSelectedBgColor','linkActiveBgColorHeader', 'clBorderPanels', 'clBorderBgCommentsPanels', 'clBorderQuestionnairePanels', 'clBorderReportsPanels', 'clBorderProgressActivitiesPanels', 'clBorderExercisesPanels', 'clBorderBottomListMenu',
                                                    'clHoveredListMenu', 'bgHoveredListMenu', 'BgBorderColorAgenda', 'BgBorderBottomRowTables', 'BgBorderColorAgendaEvent',
                                                    'clBorderBottomMenuPopoverOption', 'BgHoveredMenuPopoverOption', 'AboutBorderChatContainer', 'AboutChatContainer', 'AboutBorderCourseInfoContainer', 'AboutBorderUnitsContainer'),
                "rgba(35,82,124,1)" => array('linkHoverColor','clLinkHoveredAlertInfo','clLinkHoveredAlertWarning','clLinkHoveredAlertSuccess','clLinkHoveredAlertDanger'),
                "rgba(0,0,0,0.2)" => array('leftMenuBgColor'),
                "rgba(0,0,0,0)" => array('loginTextBgColor','loginTextBgColorSmallScreen','BgColorLinkBanner'),
                "rgba(180, 190, 209, 1)" => array('BgColorScrollBar', 'BgHoveredColorScrollBar'),
                "rgba(79, 104, 147, 1)" => array('clContainerImportantAnnouncement'),
                "rgba(104, 125, 163, 1)" => array('ClInactiveRadios', 'ClInactiveCheckboxes', 'clBorderInput', 'clBorderSelect', 'clColorHoveredBodyAgenda', 'BgBorderTextEditor'),
                "rgba(232, 237, 248, 1)" => array('clBorderBottomAccordions', 'clBorderModal', 'BgBorderMenuPopover', 'BorderLeftToRightColumnCourseBgColor'),
                "rgba(239, 242, 251, 1)" => array('clBorderBottomLists','BgBorderColorAnnouncementHomepageLink', 'BgBorderColorCardLogin'),
                "rgba(205, 212, 224, 1)" => array('bgBorderContextualMenu'),
                "rgba(155, 169, 193, 1)" => array('ColorMutedTexts', 'BgBorderRadios', 'BgBorderCheckboxes', 'bgHelpButtonColor'),
                "rgba(0, 51, 153, 1)" => array('bgColorActiveDateTime'),
                "rgba(232, 232, 232, 1)" => array('BgProgressBar'),
                "rgba(196, 70, 1, 1)" => array('bgDeleteButtonColor', 'clListMenuLogout', 'clListMenuDeletion', 'linkDeleteColor', 'clDeleteMenuPopoverOption', 'clDeleteIconModal', 'clXmarkModal','BgBadgeAccent', 'bgAlertDanger', 'clRequiredFieldForm', 'ColorRedText'),
                "rgba(183, 10, 10, 1)" => array('bgHoveredDeleteButtonColor', 'borderClAlertDanger'),
                "rgba(225, 225, 225, 1)" => array('bgColorHoveredBodyAgenda'),
                "rgba(30, 126, 14, 1)" => array('bgSuccessButtonColor','BgBadgeSuccess', 'bgAlertSuccess', 'ColorGreenText', 'borderClAlertSuccess'),
                "rgba(245, 118, 0, 1)" => array('BgBadgeWarning', 'bgAlertWarning', 'ColorOrangeText', 'borderClAlertWarning'),
                "rgba(37, 70, 240, 1)" => array('BgBadgePrimary', 'bgAlertInfo', 'borderClAlertInfo'),
                "rgba(30, 126, 14, 0.81)" => array('bgHoveredSuccessButtonColor'),
                "rgba(155, 169, 193, 0.82)" => array('bgHoveredHelpButtonColor'),
                "rgba(255, 255, 255, 0)" => array('bgHoveredBoxShadowPanels', 'borderColorContentPlatformLeftRight', 'bgColorSectionContainers'),
                "rgba(232, 242, 231, 1)" => array('bgContainerImportantAnnouncement'),
                "rgba(62, 73, 101, 1)" => array('clOptionSelect', 'ClTextEditor', 'clInputText', 'clTabs', 'clAccordions', 'clColorBodyAgenda'),
                "rgba(0, 74, 148, 1)" => array('leftMenuSelectedLinkColor'),
                "rgba(250, 251, 252,1)" => array('bgColorDeactiveDateTime'),
                "repeat" => array('bgType'),
                "boxed" => array('containerType'),
                "fluid" => array('view_platform'),
                "small-right" => array("loginImgPlacement"),
                "" => array('fluidContainerWidth','maxHeightJumbotron','maxWidthTextJumbotron', 'sliderWidthImgForm')
            );

$tenant = getCurrentTenant();
$tenant_themes = getTenantThemes();
$tenant_theme_ids = [];

if ($tenant_themes) {
    $tenant_theme_ids = array_map(fn($t) => intval($t->id), $tenant_themes);
}

if ($tenant && $tenant->theme_id) {
    $active_theme = $tenant->theme_id;    
} else {
    $active_theme = get_config('theme_options_id');
}

$preview_theme = $_SESSION['theme_options_id'] ?? NULL;
$theme_id = $preview_theme ?? $active_theme;
if (isset($_GET['reset_theme_options'])) {
    unset($_SESSION['theme_options_id']);
    redirect_to_home_page('modules/admin/theme_options.php');
}
if (isset($_GET['delete_image'])) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles, [
            'allowed_classes' => ['stdClass'],
            'max_depth' => 0,
        ]);
        $logo_type = $_GET['delete_image'];
        unlink("$webDir/courses/theme_data/$theme_id/{$theme_options_styles[$logo_type]}");
        unset($theme_options_styles[$logo_type]);
        $serialized_data = serialize($theme_options_styles);
        Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, $theme_id);
        redirect_to_home_page('modules/admin/theme_options.php');
}
if (isset($_GET['export'])) {
        if (!$theme_id) redirect_to_home_page('modules/admin/theme_options.php'); // if default theme
        require_once 'include/lib/fileUploadLib.inc.php';
        if (!is_dir("courses/theme_data")) make_dir('courses/theme_data');
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_name = $theme_options->name;

        $styles = unserialize($theme_options->styles);
        $export_data = base64_encode(serialize($theme_options));
        $export_data_file = 'courses/theme_data/theme_options.txt';
        file_put_contents('courses/theme_data/theme_options.txt', $export_data);
        $filename = "courses/theme_data/".replace_dangerous_char(greek_to_latin($theme_name)).".zip";
        $file_list = array("courses/theme_data/theme_options.txt");
        if (isset($styles['bgImage'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[bgImage]";
        }
        if (isset($styles['imageUpload'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[imageUpload]";
        }
        if (isset($styles['imageUploadSmall'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[imageUploadSmall]";
        }
        if (isset($styles['imageUploadFooter'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[imageUploadFooter]";
        }
        if (isset($styles['imageUploadForm'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[imageUploadForm]";
        }
        if (isset($styles['imageUploadRegistration'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[imageUploadRegistration]";
        }
        if (isset($styles['imageUploadFaq'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[imageUploadFaq]";
        }
        if (isset($styles['loginImg'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[loginImg]";
        }
        if (isset($styles['loginImgL'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[loginImgL]";
        }
        if(isset($styles['RightColumnCourseBgImage'])){
            $file_list[] = "courses/theme_data/$theme_id/$styles[RightColumnCourseBgImage]";
        }
        if (isset($styles['faviconUpload'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[faviconUpload]";
        }
        if (isset($styles['contactUpload'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[contactUpload]";
        }
        if (isset($styles['imageUploadBriefProfilePortfolio'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[imageUploadBriefProfilePortfolio]";
        }
        if (isset($styles['loginBgImage'])) {
            $file_list[] = "courses/theme_data/$theme_id/$styles[loginBgImage]";
        }

        $zipFile = new ZipArchive();
        $zipFile->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($file_list as $file_to_add) {
            $zipFile->addFile($webDir . "/" . $file_to_add, str_replace("courses/theme_data/", '', $file_to_add));
        }
        $zipFile->close();
        header("Content-Type: application/x-zip");
        set_content_disposition('attachment', $filename);
        stop_output_buffering();
        @readfile($filename);
        @unlink($filename);
        @unlink($export_data_file);
        exit;
}
if (isset($_POST['import'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }
    validateUploadedFile($_FILES['themeFile']['name'], 2);
    if (get_file_extension($_FILES['themeFile']['name']) == 'zip') {
        $file_name = $_FILES['themeFile']['name'];
        if (!is_dir('courses/theme_data')) {
            make_dir('courses/theme_data');
        }
        if (move_uploaded_file($_FILES['themeFile']['tmp_name'], "courses/theme_data/$file_name")) {
            require_once 'modules/admin/extconfig/externals.php';
            $connector = AntivirusApp::getAntivirus();
            if ($connector->isEnabled()) {
                $output=$connector->check("courses/theme_data/$file_name");
                if($output->status==$output::STATUS_INFECTED){
                    AntivirusApp::block($output->output);
                }
            }
            $archive = new ZipArchive();
            if ($archive->open("courses/theme_data/$file_name")) {
                // Allowed theme payload: metadata text plus the asset types themes already use (css/js overrides, images, icons, web fonts).
                // Current values of $allowedExtensions and $allowedMimeTypes are not definite and should be reviewed by project maintainers.
                $allowedExtensions = array('txt','css','js','png','jpg','jpeg','gif','svg','webp','ico','woff','woff2','ttf','eot');
                $allowedMimeTypes = array(
                    'text/plain', 'text/css', 'application/javascript', 'text/javascript',
                    'image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon',
                    'font/woff', 'font/woff2', 'application/font-woff', 'application/x-font-ttf', 'font/ttf',
                    'application/x-font-eot', 'application/vnd.ms-fontobject'
                );
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $invalidArchive = false;
                for ($i = 0; $i < $archive->numFiles; $i++) {
                    $entry = $archive->statIndex($i)['name'];
                    $normalizedEntry = ltrim($entry, '/');
                    if (strpos($normalizedEntry, "\0") !== false || preg_match('#(^|/)\\.\\.(/|$)#', $normalizedEntry)) {
                        $invalidArchive = true;
                        break;
                    }
                    if (substr($normalizedEntry, -1) === '/') {
                        continue; // directory
                    }
                    $extension = strtolower(pathinfo($normalizedEntry, PATHINFO_EXTENSION));
                    if ($extension === '' || !in_array($extension, $allowedExtensions)) {
                        $invalidArchive = true;
                        break;
                    }
                    $stream = $archive->getStream($entry);
                    if ($stream === false) {
                        $invalidArchive = true;
                        break;
                    }
                    $buffer = stream_get_contents($stream, 256 * 1024);
                    fclose($stream);
                    $mimeType = $finfo->buffer($buffer) ?: 'application/octet-stream';
                    if (!in_array($mimeType, $allowedMimeTypes)) {
                        $invalidArchive = true;
                        break;
                    }
                }
                if ($invalidArchive) {
                    $archive->close();
                    @unlink("courses/theme_data/$file_name");
                    Session::Messages($langUnwantedFiletype, 'alert-danger');
                    redirect_to_home_page('modules/admin/theme_options.php');
                }
                $archive->extractTo('courses/theme_data/temp');
                unlink("courses/theme_data/$file_name");
                $theme_options_file = "$webDir/courses/theme_data/temp/theme_options.txt";
                if (!file_exists($theme_options_file)) {
                    removeDir("$webDir/courses/theme_data/temp");
                    Session::Messages($langUnwantedFiletype, 'alert-danger');
                    redirect_to_home_page('modules/admin/theme_options.php');
                }
                $base64_str = file_get_contents($theme_options_file);
                unlink($theme_options_file);
                $theme_options = unserialize(base64_decode($base64_str), [
                    'allowed_classes' => ['stdClass'],
                    'max_depth' => 0,
                ]);
                if (!$theme_options || !isset($theme_options->name) || !isset($theme_options->styles) || !isset($theme_options->id)) {
                    unset($theme_options);
                    removeDir("$webDir/courses/theme_data/temp");
                    Session::Messages($langUnwantedFiletype, 'alert-danger');
                    redirect_to_home_page('modules/admin/theme_options.php');
                }
                $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles, version)
                    VALUES (?s, ?s, 4)", $theme_options->name, $theme_options->styles)->lastInsertID;
                rename("$webDir/courses/theme_data/temp/".intval($theme_options->id), "$webDir/courses/theme_data/temp/$new_theme_id");
                recurse_copy("$webDir/courses/theme_data/temp","$webDir/courses/theme_data");
                removeDir("$webDir/courses/theme_data/temp");
                Session::Messages($langThemeInstalled, 'alert-success');
            } else {
                die("Error while unzipping file !");
            }
            $archive->close();
        }
    } else {
        Session::Messages($langUnwantedFiletype, 'alert-danger');
    }
    redirect_to_home_page('modules/admin/theme_options.php');
}
if (isset($_POST['optionsSave'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $active_theme);

    // Abort when user is not an admin and theme doesn't belong to the current tenant.
    if (!$is_admin && $tenant && $theme_options->tenant_id !== $tenant->id) {
        Session::flash('message', $langThemeEditNotAllowed);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/admin/theme_options.php');
    }

    upload_images();

    //jumbotron image
    if(!empty($_POST['choose_from_jumbotronlist'])) {
        $imageName = $_POST['choose_from_jumbotronlist'];
        $imagePath = "$webDir/template/modern/images/jumbotron_images/$imageName";
        $newPath = "$webDir/courses/theme_data/$theme_id/";
        $name = pathinfo($imageName, PATHINFO_FILENAME);
        $ext =  get_file_extension($imageName);
        $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
        $newName  = $newPath.$image_without_ext.".".$ext;
        $copied = copy($imagePath , $newName);
        if ((!$copied)) {
            echo "Error : Not Copied";
        }
        else{
            //serialize $_post login img jumbotron
            $_POST['loginImg'] = $image_without_ext.".".$ext;
        }
    }

    //login image
    if(!empty($_POST['choose_from_loginlist'])) {
        $imageName = $_POST['choose_from_loginlist'];
        $imagePath = "$webDir/template/modern/images/login_images/$imageName";
        $newPath = "$webDir/courses/theme_data/$theme_id/";
        $name = pathinfo($imageName, PATHINFO_FILENAME);
        $ext =  get_file_extension($imageName);
        $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
        $newName  = $newPath.$image_without_ext.".".$ext;
        $copied = copy($imagePath , $newName);
        if ((!$copied)) {
            echo "Error : Not Copied";
        }
        else{
            //serialize $_post login img jumbotron
            $_POST['loginImgL'] = $image_without_ext.".".$ext;
        }
    }

    //form image
    if(!empty($_POST['choose_from_formlist'])) {
        $imageName = $_POST['choose_from_formlist'];
        $imagePath = "$webDir/template/modern/images/form_images/$imageName";
        $newPath = "$webDir/courses/theme_data/$theme_id/";
        $name = pathinfo($imageName, PATHINFO_FILENAME);
        $ext =  get_file_extension($imageName);
        $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
        $newName  = $newPath.$image_without_ext.".".$ext;
        $copied = copy($imagePath , $newName);
        if ((!$copied)) {
            echo "Error : Not Copied";
        }
        else{
            //serialize $_post login img jumbotron
            $_POST['imageUploadForm'] = $image_without_ext.".".$ext;
        }
    }

    //registration image
    if(!empty($_POST['choose_from_registrationlist'])) {
        $imageName = $_POST['choose_from_registrationlist'];
        $imagePath = "$webDir/template/modern/images/registration_images/$imageName";
        $newPath = "$webDir/courses/theme_data/$theme_id/";
        $name = pathinfo($imageName, PATHINFO_FILENAME);
        $ext =  get_file_extension($imageName);
        $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
        $newName  = $newPath.$image_without_ext.".".$ext;
        $copied = copy($imagePath , $newName);
        if ((!$copied)) {
            echo "Error : Not Copied";
        }
        else{
            //serialize $_post login img jumbotron
            $_POST['imageUploadRegistration'] = $image_without_ext.".".$ext;
        }
    }

    //FAQ image
    if(!empty($_POST['choose_from_faqlist'])) {
        $imageName = $_POST['choose_from_faqlist'];
        $imagePath = "$webDir/template/modern/images/faq_images/$imageName";
        $newPath = "$webDir/courses/theme_data/$theme_id/";
        $name = pathinfo($imageName, PATHINFO_FILENAME);
        $ext =  get_file_extension($imageName);
        $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
        $newName  = $newPath.$image_without_ext.".".$ext;
        $copied = copy($imagePath , $newName);
        if ((!$copied)) {
            echo "Error : Not Copied";
        }
        else{
            //serialize $_post login img jumbotron
            $_POST['imageUploadFaq'] = $image_without_ext.".".$ext;
        }
    }

    // Save user theme customization setting
    if (isset($_POST['enable_user_theme_customization'])) {
        set_config('enable_user_theme_customization', 1);

        // Save selected themes for users
        $selected_themes = array();
        if (isset($_POST['user_selectable_themes']) && is_array($_POST['user_selectable_themes'])) {
            $selected_themes = array_map('intval', $_POST['user_selectable_themes']);
            $selected_themes = array_filter($selected_themes); // Remove empty values
        }
        set_config('user_selectable_themes', implode(',', $selected_themes));
    } else {
        set_config('enable_user_theme_customization', 0);
        set_config('user_selectable_themes', ''); // Clear selection when disabled
    }

    clear_default_settings();
    $serialized_data = serialize($_POST);
    Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, $theme_id);
    $_SESSION['theme_changed'] = true;
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_GET['delThemeId'])) {
    $theme_id = intval($_GET['delThemeId']);
    $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);

    // Abort when user is not an admin and theme doesn't belong to the current tenant.
    if (!$is_admin && $tenant && $theme_options->tenant_id !== $tenant->id) {
        Session::flash('message', $langThemeEditNotAllowed);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/admin/theme_options.php');
    }

    $theme_options_styles = unserialize($theme_options->styles);
    @removeDir("$webDir/courses/theme_data/$theme_id");
    Database::get()->query("DELETE FROM theme_options WHERE id = ?d", $theme_id);
    if($_GET['delThemeId'] == $active_theme) {
        Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", 0, 'theme_options_id');
    } else {
        unset($_SESSION['theme_options_id']);
    }
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_POST['themeOptionsName'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $theme_options_name = $_POST['themeOptionsName'];
    $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles, version, tenant_id) VALUES(?s, '', 4, ?d)", $theme_options_name, $tenant ? $tenant->id : null)->lastInsertID;
    clear_default_settings();

    clone_images($new_theme_id); //clone images
    upload_images($new_theme_id); //upload new images
    $serialized_data = serialize($_POST);
    Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, $new_theme_id);
    $_SESSION['theme_options_id'] = $new_theme_id;
    $_SESSION['theme_changed'] = true;
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_POST['active_theme_options'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_POST['preview'])){
        if ($_POST['active_theme_options'] == $active_theme) {
            unset($_SESSION['theme_options_id']);
        } else {
            $_SESSION['theme_options_id'] = $_POST['active_theme_options'];
        }
    } else {
        if ($is_admin) {
            set_config('theme_options_id', $_POST['active_theme_options']);
        } elseif ($is_departmentmanage_user) {
            Database::get()->query("UPDATE tenant SET theme_id = ?d WHERE id = ?d", $_POST['active_theme_options'], $tenant->id);
            $_SESSION['current_user_tenant']->theme_id = $_POST['active_theme_options'];
        }
        unset($_SESSION['theme_options_id']);
    }
    $_SESSION['theme_changed'] = true;
    redirect_to_home_page('modules/admin/theme_options.php');
} else {
    $toolName = $langAdmin;
    $pageName = $langThemeSettings;
    $navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
    load_js('spectrum');
    load_js('bootstrap-slider');

    $tenant_theme_ids_js_array = json_encode($tenant_theme_ids);

    $is_tenant_js = $tenant ? 1 : 0;

    $head_content .= "
    <script>
        $(function(){
            $('#fluidContainerWidth').slider({
                tooltip: 'hide',
                formatter: function(value) {
                    $('#pixelCounter').text(value + 'px');
                }
            });
            $('#sliderWidthImgForm').slider({
                tooltip: 'hide',
                formatter: function(value) {
                    $('#sliderWidthImgFormCounter').text(value + '%');
                }
            });


            if($('#widthOfFormId').is(':checked')){
                $('.sliderWidthImgFormClass').css('display','block');
            }else{
                $('.sliderWidthImgFormClass').css('display','none');
            }
            $('#widthOfFormId').change(function() {
                if(this.checked) {
                    $('.sliderWidthImgFormClass').css('display','block');
                }else{
                    $('.sliderWidthImgFormClass').css('display','none');
                }
            });

            if($('#strechedImgOfFormId').is(':checked')){
                $('.streched_repeaded_img_form_class').css('display','block');
            }else{
                $('.streched_repeaded_img_form_class').css('display','none');
            }
            $('#strechedImgOfFormId').change(function() {
                if(this.checked) {
                    $('.streched_repeaded_img_form_class').css('display','block');
                }else{
                    $('.streched_repeaded_img_form_class').css('display','none');
                }
            });


            $('#maxHeightJumbotron').slider({
                tooltip: 'hide',
                formatter: function(value) {
                    $('#pixelCounterHeightJumbotron').text(value + 'px');
                }
            });
            $('#maxWidthTextJumbotron').slider({
                tooltip: 'hide',
                formatter: function(value) {
                    $('#pixelCounterWidthTextJumbotron').text(value + 'px');
                }
            });
            $('input[name=\'containerType\']').change(function(){
                if($(this).val()=='fluid') {
                    $('#fluidContainerWidth').slider('enable');
                    $('#fluidContainerWidth').prop('disabled', false);
                    $('#fluidContainerWidth').closest('.form-group').removeClass('hidden');
                } else {
                    $('#fluidContainerWidth').slider('disable');
                    $('#fluidContainerWidth').prop('disabled', true);
                    $('#fluidContainerWidth').closest('.form-group').addClass('hidden');
                }
            });
            $('.uploadTheme').click(function (e)
            {
                e.preventDefault();
                bootbox.dialog({
                    title: '$langImport',
                    message: '<div class=\"row\">'+
                                '<div class=\"col-sm-12\">'+
                                    '<form id=\"uploadThemeForm\" class=\"form-horizontal\" role=\"form\" enctype=\"multipart/form-data\" method=\"post\">'+
                                        '<div class=\"form-group\">'+
                                        '<div class=\"col-sm-12\">'+
                                            '<input id=\"themeFile\" name=\"themeFile\" type=\"file\">'+
                                            '<input name=\"import\" type=\"hidden\">'+
                                        '</div>'+
                                        '</div>". addslashes(generate_csrf_token_form_field()) ."'+
                                    '</form>'+
                                '</div>'+
                            '</div>',
                    buttons: {
                        success: {
                            label: '$langUpload',
                            className: 'submitAdminBtn',
                            callback: function (d) {
                                var themeFile = $('#themeFile').val();
                                if(themeFile != '') {
                                    $('#uploadThemeForm').submit();
                                } else {
                                    $('#themeFile').closest('.form-group').addClass('has-error');
                                    $('#themeFile').after('<span class=\"help-block\">$langTheFieldIsRequired</span>');
                                    return false;
                                }
                            }
                        },
                        cancel: {
                            label: '$langCancel',
                            className: 'cancelAdminBtn'
                        }
                    }
                });
            });
            var optionsSaveCallback = function (d) {
                var themeOptionsName = $('#themeOptionsName').val();
                if (themeOptionsName) {
                    var input = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'themeOptionsName').val(themeOptionsName);
                    $('#theme_options_form').append($(input)).submit();
                } else {
                    $('#themeOptionsName').closest('.form-group').addClass('has-error');
                    $('#themeOptionsName').after('<span class=\"help-block\">$langTheFieldIsRequired</span>');
                    return false;
                }
            };
            $('#optionsSaveAs').click(function (e)
            {
                e.preventDefault();
                bootbox.dialog({
                    title: '$langSaveAs',
                    message: '<div class=\"row\">'+
                                '<div class=\"col-sm-12\">'+
                                    '<form class=\"form-horizontal\" role=\"form\">'+
                                        '<div class=\"form-group\">'+
                                        '<div class=\"col-sm-12\">'+
                                            '<input id=\"themeOptionsName\" name=\"themeOptionsName\" type=\"text\" placeholder=\"$langThemeOptionsName\" class=\"form-control\">'+
                                        '</div>'+
                                        '</div>". addslashes(generate_csrf_token_form_field()) ."'+
                                    '</form>'+
                                '</div>'+
                            '</div>',
                    buttons: {
                        success: {
                            label: '$langSave',
                            className: 'submitAdminBtn',
                            callback: optionsSaveCallback,
                        },
                        cancel: {
                            label: '$langCancel',
                            className: 'cancelAdminBtn'
                        }
                    }
                });
                $('#themeOptionsName').keypress(function (e) {
                    if (e.which == 13) {
                        e.preventDefault();
                        optionsSaveCallback();
                    }
                });
            });

            var tenant_theme_ids = $tenant_theme_ids_js_array;

            $('select#theme_selection').change(function ()
            {
                var cur_val = $(this).val();

                if (cur_val == '$active_theme') {
                    $('a#theme_enable').addClass('d-none');
                    $('a#theme_preview').addClass('d-none');
                } else {
                    $('a#theme_enable').removeClass('d-none');
                    if (cur_val != '$preview_theme') {
                        $('a#theme_preview').removeClass('d-none');
                    }
                }

                if (cur_val == '$preview_theme') $('a#theme_preview').addClass('d-none');
                if (cur_val == 0 || ($is_tenant_js && !tenant_theme_ids.includes(+cur_val))) {
                    $('a#theme_delete').addClass('d-none');
                } else {
                    $('a#theme_delete').removeClass('d-none');
                    var formAction = $('a#theme_delete').closest('form').attr('action');
                    var newValue = $('select#theme_selection').val();
                    var newAction = formAction.replace(/(delThemeId=).*/, '$1'+newValue);
                    $('a#theme_delete').closest('form').attr('action', newAction);
                }
            });
            $('a.theme_enable').click(function (e)
            {
                e.preventDefault();
                $('#theme_selection').submit();
            });
            $('a#theme_preview').click(function (e)
            {
                e.preventDefault();
                $('#theme_selection').append('<input type=\"hidden\" name=\"preview\">');
                $('#theme_selection').submit();
            });
            $('.colorpicker').spectrum({
            preferredFormat: 'rgb',
                showAlpha: true,
                showInitial: true,
                showInput: true,
                 cancelText: '$langCancel',
                chooseText: '$langSubmit'

            });
            $('#btnEnterAColor').click(function() {
                $(this).closest('.colorpicker').spectrum('set', $('#enterAColor').val());
            });


            //jumbotron images upload
            $('.chooseJumbotronImage').on('click',function(){
                var id_img = this.id;
                alert('Selected image: '+id_img);
                document.getElementById('choose_from_jumbotronlist').value = id_img;
                $('#JumbotronImagesModal').modal('hide');
                document.getElementById('selectedImage').value = '$langSelect:'+id_img;

            });

            //login images upload
            $('.chooseLoginImage').on('click',function(){
                var id_img = this.id;
                alert('Selected image: '+id_img);
                document.getElementById('choose_from_loginlist').value = id_img;
                $('#LoginImagesModal').modal('hide');
                document.getElementById('selectedImageLogin').value = '$langSelect:'+id_img;

            });

            //form images upload
            $('.chooseFormImage').on('click',function(){
                var id_img = this.id;
                alert('Selected image: '+id_img);
                document.getElementById('choose_from_formlist').value = id_img;
                $('#FormImagesModal').modal('hide');
                document.getElementById('selectedImageForm').value = '$langSelect:'+id_img;

            });

            //registration images upload
            $('.chooseRegistrationImage').on('click',function(){
                var id_img = this.id;
                alert('Selected image: '+id_img);
                document.getElementById('choose_from_registrationlist').value = id_img;
                $('#RegistrationImagesModal').modal('hide');
                document.getElementById('selectedImageRegistration').value = '$langSelect:'+id_img;

            });

            //faq images upload
            $('.chooseFaqImage').on('click',function(){
                var id_img = this.id;
                alert('Selected image: '+id_img);
                document.getElementById('choose_from_faqlist').value = id_img;
                $('#FaqImagesModal').modal('hide');
                document.getElementById('selectedImageFaq').value = '$langSelect:'+id_img;

            });



            $('#view_platform_boxed').change(function() {
                if($('#view_platform_fluid').is(':checked')){
                    $('#view_platform_boxed').prop('checked',true);
                    $('#view_platform_fluid').prop('checked',false);
                }
            });

            $('#view_platform_fluid').change(function() {
                if($('#view_platform_boxed').is(':checked')){
                    $('#view_platform_fluid').prop('checked',true);
                    $('#view_platform_boxed').prop('checked',false);
                }
            });

            if($('#enableBoxLogoId').is(':checked')){
                $('.logo-container').removeClass('d-none').addClass('d-block');
            }
            $('#enableBoxLogoId').change(function() {
                if($('#enableBoxLogoId').is(':checked')){
                    $('.logo-container').removeClass('d-none').addClass('d-block');
                } else {
                    $('.logo-container').removeClass('d-block').addClass('d-none');
                }
            });

            // Show/hide user selectable themes section based on checkbox
            $('#enable_user_theme_customization').change(function() {
                if($(this).is(':checked')){
                    $('#user_selectable_themes_section').removeClass('d-none');
                } else {
                    $('#user_selectable_themes_section').addClass('d-none');
                }
            });

            $('.nav-link-1').on('click', function () {
                $('.nav-link-2.active').removeClass('active');
            });
            $('.nav-link-2').on('click', function () {
                $('.nav-link-1.active').removeClass('active');
            });
        });
    </script>";
    $all_themes = Database::get()->queryArray("SELECT * FROM theme_options WHERE version = 4 ORDER BY name, id");
    $themes_arr[0] = "---- $langDefaultThemeSettings ----";
    foreach ($all_themes as $row) {
        $themes_arr[$row->id] = $row->name;
    }

    if ($theme_id) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
    }
    initialize_settings();

    // Get user theme customization setting
    $enable_user_theme_customization = get_config('enable_user_theme_customization', 0);

    // Get selected themes for users
    $user_selectable_themes_str = get_config('user_selectable_themes', '');
    $user_selectable_themes = array();
    if (!empty($user_selectable_themes_str)) {
        $user_selectable_themes = array_map('intval', explode(',', $user_selectable_themes_str));
        $user_selectable_themes = array_filter($user_selectable_themes);
    }

    // Build theme checkboxes HTML
    $theme_checkboxes_html = "";
    if (!empty($all_themes)) {
        foreach ($all_themes as $theme_item) {
            $theme_item_id = intval($theme_item->id);
            $theme_item_name = isset($theme_item->name) ? htmlspecialchars($theme_item->name, ENT_QUOTES, 'UTF-8') : '';
            $is_checked = in_array($theme_item_id, $user_selectable_themes) ? 'checked' : '';

            if (!empty($theme_item_name)) {
                $theme_checkboxes_html .= "
                                    <div class='col-md-6 col-lg-4 mb-3'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label='".htmlspecialchars($theme_item_name, ENT_QUOTES, 'UTF-8')."'>
                                                <input type='checkbox' name='user_selectable_themes[]' value='".intval($theme_item_id)."' ".$is_checked.">
                                                <span class='checkmark'></span>
                                                ".htmlspecialchars($theme_item_name, ENT_QUOTES, 'UTF-8')."
                                            </label>
                                        </div>
                                    </div>";
            }
        }
    }




    $activate_class = isset($preview_theme) ? '' : ' hidden';
    $activate_btn = "<a href='#' class='theme_enable btn submitAdminBtn $activate_class' id='theme_enable'>$langActivate</a>";
    $preview_class = ' hidden';
    $preview_btn = "<a href='#' class='btn submitAdminBtn $preview_class' id='theme_preview'>$langSee</a>";

    if ($theme_id == 0) {
        $del_class = $options_save_class = " d-none";
    } else if ($tenant) {
        if (in_array($theme_id, $tenant_theme_ids)) {
            $del_class = $options_save_class = "";
        } else {
            $del_class = $options_save_class = " d-none";
        }
    } else {
        $del_class = $options_save_class = "";
    }

    $delete_btn = "
                    <form class='form-inline mt-0' style='display:inline;' method='post' action='$_SERVER[SCRIPT_NAME]?delThemeId=$theme_id'>
                        <a class='confirmAction mt-md-0 btn deleteAdminBtn $del_class delThemeBtn' id='theme_delete' data-title='$langConfirmDelete' data-message='$langThemeSettingsDelete' data-cancel-txt='$langCancel' data-action-txt='$langDelete' data-action-class='deleteAdminBtn'>$langDelete</a>
                    </form>";
    $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;

    if (isset($theme_options_styles['imageUpload'])) {
        $logo_field = "<img src='$urlThemeData/$theme_options_styles[imageUpload]' style='max-height:100px;max-width:150px;' alt='Image upload for large screen'>";
            if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
                $logo_field .= "&nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUpload'>$langDelete</a>";
            }
        $logo_field .= "<input type='hidden' name='imageUpload' value='$theme_options_styles[imageUpload]'>";
    } else {
       $logo_field = "<label for='imageUpload' aria-label='$langLogo'></label><input type='file' name='imageUpload' id='imageUpload'>";
    }
    if (isset($theme_options_styles['imageUploadSmall'])) {
        $small_logo_field = "<img src='$urlThemeData/$theme_options_styles[imageUploadSmall]' style='max-height:100px;max-width:150px;' alt='Image upload for small screen'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $small_logo_field .= "&nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadSmall'>$langDelete</a>";
        }
        $small_logo_field .= "<input type='hidden' name='imageUploadSmall' value='$theme_options_styles[imageUploadSmall]'>";
    } else {
       $small_logo_field = "<label for='imageUploadSmall' aria-label='$langLogoSmall'></label><input type='file' name='imageUploadSmall' id='imageUploadSmall'>";
    }
    if (isset($theme_options_styles['imageUploadFooter'])) {
        $image_footer_field = "<img src='$urlThemeData/$theme_options_styles[imageUploadFooter]' style='max-height:100px;max-width:150px;' alt='Image upload for footer'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $image_footer_field .= "&nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadFooter'>$langDelete</a>";
        }
        $image_footer_field .= "<input type='hidden' name='imageUploadFooter' value='$theme_options_styles[imageUploadFooter]'>";
    } else {
       $image_footer_field = "<label for='imageUploadFooter' aria-label='$langFooterUploadImage'></label><input type='file' name='imageUploadFooter' id='imageUploadFooter'>";
    }
    if (isset($theme_options_styles['bgImage'])) {
        $bg_field = "<div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[bgImage]' style='max-height:100px;max-width:150px;' alt='Image upload for background'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $bg_field .= "<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=bgImage'>$langDelete</a>";
        }
        $bg_field .= "</div><input type='hidden' name='bgImage' value='$theme_options_styles[bgImage]'>";
    } else {
       $bg_field = "<input aria-label='$langBgImg' type='file' name='bgImage' id='bgImage'>";
    }
    if (isset($theme_options_styles['loginImg'])) {
        $login_image_field = "
            <div class='col-sm-12 mb-2'>$langBgImg</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
            <img src='$urlThemeData/$theme_options_styles[loginImg]' style='max-height:100px;max-width:150px;' alt='Image upload for login form'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $login_image_field .= "<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=loginImg'>$langDelete</a>";
        }
        $login_image_field .= "</div><input type='hidden' name='loginImg' value='$theme_options_styles[loginImg]'>";
    } else {
       $login_image_field = "
            <label for='loginImg' class='col-sm-12 mb-2'>$langBgImg</label>
            <ul class='nav nav-tabs' id='nav-tab' role='tablist'>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link active' id='tabs-upload-tab' data-bs-toggle='tab' data-bs-target='#tabs-upload' type='button' role='tab' aria-controls='tabs-upload' aria-selected='true'>$langUpload</button>
                </li>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link' id='tabs-selectImage-tab' data-bs-toggle='tab' data-bs-target='#tabs-selectImage' type='button' role='tab' aria-controls='tabs-selectImage' aria-selected='false'>$langAddPicture</button>
                </li>
            </ul>
            <div class='tab-content mt-3' id='tabs-tabContent'>
                <div class='tab-pane fade show active' id='tabs-upload' role='tabpanel' aria-labelledby='tabs-upload-tab'>
                     <input type='file' name='loginImg' id='loginImg'>
                </div>
                <div class='tab-pane fade' id='tabs-selectImage' role='tabpanel' aria-labelledby='tabs-selectImage-tab'>
                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#JumbotronImagesModal'>
                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;$langSelect
                    </button>
                    <input type='hidden' id='choose_from_jumbotronlist' name='choose_from_jumbotronlist'>
                    <label for='selectedImage'>$langImageSelected:</label>
                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImage'>
                </div>
            </div>


        ";
    }

    if (isset($theme_options_styles['loginImgL'])) {
        $login_image_fieldL = "
            <div class='col-sm-12 mb-2'>$langLoginImg:</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[loginImgL]' style='max-height:100px;max-width:150px;' alt='Image upload'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $login_image_fieldL .= "<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=loginImgL'>$langDelete</a>";
        }
        $login_image_fieldL .= "</div><input type='hidden' name='loginImgL' value='$theme_options_styles[loginImgL]'>";
    } else {
       $login_image_fieldL = "
            <label for='loginImgL' class='col-sm-12 mb-2'>$langLoginImg:</label>
            <ul class='nav nav-tabs' id='nav-tab2' role='tablist'>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link active' id='tabs-upload-tab2' data-bs-toggle='tab' data-bs-target='#tabs-upload2' type='button' role='tab' aria-controls='tabs-upload2' aria-selected='true'>$langUpload</button>
                </li>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link' id='tabs-selectImage-tab2' data-bs-toggle='tab' data-bs-target='#tabs-selectImage2' type='button' role='tab' aria-controls='tabs-selectImage2' aria-selected='false'>$langAddPicture</button>
                </li>
            </ul>
            <div class='tab-content mt-3' id='tabs-tabContent2'>
                <div class='tab-pane fade show active' id='tabs-upload2' role='tabpanel' aria-labelledby='tabs-upload-tab2'>
                    <input aria-label='$langBgImg' type='file' name='loginImgL' id='loginImgL'>
                </div>
                <div class='tab-pane fade' id='tabs-selectImage2' role='tabpanel' aria-labelledby='tabs-selectImage-tab2'>
                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#LoginImagesModal'>
                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;$langSelect
                    </button>
                    <input type='hidden' id='choose_from_loginlist' name='choose_from_loginlist'>
                    <label for='selectedImageLogin'>$langImageSelected:</label>
                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImageLogin'>
                </div>
            </div>
       ";
    }

    if (isset($theme_options_styles['loginBgImage'])) {
        $login_image_fieldL_2 = "<div><label class='mb-2' for='loginBgImage' aria-label='$langLoginBgImage'>$langLoginBgImage</label></div><img src='$urlThemeData/$theme_options_styles[loginBgImage]' style='max-height:100px;max-width:150px;' alt='Contact upload'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $login_image_fieldL_2 .= "&nbsp;&nbsp;<a class='btn deleteAdminBtn d-inline-flex' href='$_SERVER[SCRIPT_NAME]?delete_image=loginBgImage'>$langDelete</a>";
        }
        $login_image_fieldL_2 .= "<input type='hidden' name='loginBgImage' value='$theme_options_styles[loginBgImage]'>";
    } else {
       $login_image_fieldL_2 = "<div><label for='loginBgImage' aria-label='$langLoginBgImage'>$langLoginBgImage</label></div><input type='file' name='loginBgImage' id='loginBgImage'>";
    }

    if (isset($theme_options_styles['imageUploadForm'])) {
        $form_image_fieldL = "<div class='col-12'>$langFormUploadImage</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[imageUploadForm]' style='max-height:100px;max-width:150px;' alt='$langDownloadFile'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $form_image_fieldL .= "<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadForm'>$langDelete</a>";
        }
        $form_image_fieldL .= "</div><input type='hidden' name='imageUploadForm' value='$theme_options_styles[imageUploadForm]'>";
    } else {
       $form_image_fieldL = "
            <label for='imageUploadForm' class='col-12'>$langFormUploadImage</label>
            <ul class='nav nav-tabs' id='nav-tab3' role='tablist'>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link active' id='tabs-upload-tab3' data-bs-toggle='tab' data-bs-target='#tabs-upload3' type='button' role='tab' aria-controls='tabs-upload3' aria-selected='true'>$langUpload</button>
                </li>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link' id='tabs-selectImage-tab3' data-bs-toggle='tab' data-bs-target='#tabs-selectImage3' type='button' role='tab' aria-controls='tabs-selectImage3' aria-selected='false'>$langAddPicture</button>
                </li>
            </ul>
            <div class='tab-content mt-3' id='tabs-tabContent3'>
                <div class='tab-pane fade show active' id='tabs-upload3' role='tabpanel' aria-labelledby='tabs-upload-tab3'>
                    <input type='file' name='imageUploadForm' id='imageUploadForm'>
                </div>
                <div class='tab-pane fade' id='tabs-selectImage3' role='tabpanel' aria-labelledby='tabs-selectImage-tab3'>
                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#FormImagesModal'>
                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;$langSelect
                    </button>
                    <input type='hidden' id='choose_from_formlist' name='choose_from_formlist'>
                    <label for='selectedImageForm'>$langImageSelected:</label>
                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImageForm'>
                </div>
            </div>
       ";
    }

    if (isset($theme_options_styles['imageUploadRegistration'])) {
        $registration_image_fieldL = "<div class='col-sm-12 mb-2'>$langRegistrationUploadImage:</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[imageUploadRegistration]' style='max-height:100px;max-width:150px;' alt='Image upload'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $registration_image_fieldL .= "<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadRegistration'>$langDelete</a>";
        }
        $registration_image_fieldL .= "</div><input type='hidden' name='imageUploadRegistration' value='$theme_options_styles[imageUploadRegistration]'>";
    } else {
       $registration_image_fieldL = "
            <label for='imageUploadRegistration' class='col-sm-12 mb-2'>$langRegistrationUploadImage:</label>
            <ul class='nav nav-tabs' id='nav-tab4' role='tablist'>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link active' id='tabs-upload-tab4' data-bs-toggle='tab' data-bs-target='#tabs-upload4' type='button' role='tab' aria-controls='tabs-upload4' aria-selected='true'>$langUpload</button>
                </li>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link' id='tabs-selectImage-tab4' data-bs-toggle='tab' data-bs-target='#tabs-selectImage4' type='button' role='tab' aria-controls='tabs-selectImage4' aria-selected='false'>$langAddPicture</button>
                </li>
            </ul>
            <div class='tab-content mt-3' id='tabs-tabContent4'>
                <div class='tab-pane fade show active' id='tabs-upload4' role='tabpanel' aria-labelledby='tabs-upload-tab4'>
                    <input type='file' name='imageUploadRegistration' id='imageUploadRegistration'>
                </div>
                <div class='tab-pane fade' id='tabs-selectImage4' role='tabpanel' aria-labelledby='tabs-selectImage-tab4'>
                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#RegistrationImagesModal'>
                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;$langSelect
                    </button>
                    <input type='hidden' id='choose_from_registrationlist' name='choose_from_registrationlist'>
                    <label for='selectedImageRegistration'>$langImageSelected:</label>
                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImageRegistration'>
                </div>
            </div>



       ";
    }

    if (isset($theme_options_styles['imageUploadFaq'])) {
        $faq_image_fieldL = "<div class='col-sm-12 mb-2'>$langFaqUploadImage:</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
            <img src='$urlThemeData/$theme_options_styles[imageUploadFaq]' style='max-height:100px;max-width:150px;' alt='Image upload'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $faq_image_fieldL .= "<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadFaq'>$langDelete</a>";
        }
        $faq_image_fieldL .= "</div><input type='hidden' name='imageUploadFaq' value='$theme_options_styles[imageUploadFaq]'>";
    } else {
       $faq_image_fieldL = "
            <label for='imageUploadFaq' class='col-sm-12 mb-2'>$langFaqUploadImage:</label>
            <ul class='nav nav-tabs' id='nav-tab5' role='tablist'>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link active' id='tabs-upload-tab5' data-bs-toggle='tab' data-bs-target='#tabs-upload5' type='button' role='tab' aria-controls='tabs-upload5' aria-selected='true'>$langUpload</button>
                </li>
                <li class='nav-item' role='presentation'>
                    <button class='nav-link' id='tabs-selectImage-tab5' data-bs-toggle='tab' data-bs-target='#tabs-selectImage5' type='button' role='tab' aria-controls='tabs-selectImage5' aria-selected='false'>$langAddPicture</button>
                </li>
            </ul>
            <div class='tab-content mt-3' id='tabs-tabContent5'>
                <div class='tab-pane fade show active' id='tabs-upload5' role='tabpanel' aria-labelledby='tabs-upload-tab5'>
                    <input type='file' name='imageUploadFaq' id='imageUploadFaq'>
                </div>
                <div class='tab-pane fade' id='tabs-selectImage5' role='tabpanel' aria-labelledby='tabs-selectImage-tab5'>
                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#FaqImagesModal'>
                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;$langSelect
                    </button>
                    <input type='hidden' id='choose_from_faqlist' name='choose_from_faqlist'>
                    <label for='selectedImageFaq'>$langImageSelected:</label>
                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImageFaq'>
                </div>
            </div>
       ";
    }

    if (isset($theme_options_styles['RightColumnCourseBgImage'])) {
        $RightColumnCourseBgImage = "<img src='$urlThemeData/$theme_options_styles[RightColumnCourseBgImage]' style='max-height:100px;max-width:150px;' alt='Image upload for course content'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $RightColumnCourseBgImage .= "&nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=RightColumnCourseBgImage'>$langDelete</a>";
        }
        $RightColumnCourseBgImage .= "<input type='hidden' name='RightColumnCourseBgImage' value='$theme_options_styles[RightColumnCourseBgImage]' id='RightColumnCourseBgImage'>";
    } else {
       $RightColumnCourseBgImage = "<input type='file' name='RightColumnCourseBgImage' id='RightColumnCourseBgImage'>";
    }

    if (isset($theme_options_styles['faviconUpload'])) {
        $faviconUpload = "<img src='$urlThemeData/$theme_options_styles[faviconUpload]' style='max-height:100px;max-width:150px;' alt='Favicon upload'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $faviconUpload .= "&nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=faviconUpload'>$langDelete</a>";
        }
        $faviconUpload .= "<input type='hidden' name='faviconUpload' value='$theme_options_styles[faviconUpload]'>";
    } else {
       $faviconUpload = "<label for='faviconUpload' aria-label='$langFavicon'></label><input type='file' name='faviconUpload' id='faviconUpload'>";
    }

    if (isset($theme_options_styles['contactUpload'])) {
        $contactUpload = "<img src='$urlThemeData/$theme_options_styles[contactUpload]' style='max-height:100px;max-width:150px;' alt='Contact upload'>";
        if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
            $contactUpload .= "&nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=contactUpload'>$langDelete</a>";
        }
        $contactUpload .= "<input type='hidden' name='contactUpload' value='$theme_options_styles[contactUpload]'>";
    } else {
       $contactUpload = "<label for='contactUpload' aria-label='$langContact'></label><input type='file' name='contactUpload' id='contactUpload'>";
    }

    if (isset($theme_options_styles['imageUploadBriefProfilePortfolio'])) {
        $logo_imageUploadBriefProfilePortfolio = "<img src='$urlThemeData/$theme_options_styles[imageUploadBriefProfilePortfolio]' style='max-height:100px;max-width:150px;' alt='Image upload for large screen'>";
            if (($tenant && in_array($theme_id, $tenant_theme_ids)) || $is_admin) {
                $logo_imageUploadBriefProfilePortfolio .= "&nbsp;&nbsp;<a class='btn deleteAdminBtn d-inline-flex' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadBriefProfilePortfolio'>$langDelete</a>";
            }
        $logo_imageUploadBriefProfilePortfolio .= "<input type='hidden' name='imageUploadBriefProfilePortfolio' value='$theme_options_styles[imageUploadBriefProfilePortfolio]'>";
    } else {
       $logo_imageUploadBriefProfilePortfolio = "<label class='control-label-notes mb-2 me-2' for='imageUploadBriefProfilePortfolio' aria-label='$langLogo'>$langBgImageBasicUserInfo</label><input type='file' name='imageUploadBriefProfilePortfolio' id='imageUploadBriefProfilePortfolio'>";
    }

    $action_bar .= action_bar(array(
        array('title' => $langImport,
            'url' => "#",
            'icon' => 'fa-upload',
            'modal-class' => 'uploadTheme',
            'button-class' => 'btn-success',
            'level' => 'primary-label')
        ),false);

    $tool_content .= $action_bar;

    if (isset($preview_theme)) {
        $tool_content .= "
                <div class='alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2'>

                        <div class='d-flex gap-2'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            <span>$langPreviewState &nbsp;".$themes_arr[$preview_theme].".</span>
                        </div>
                        <div class='d-lg-flex'>
                            <a href='#' class='theme_enable TextBold'>$langActivate</a>
                            <a href='theme_options.php?reset_theme_options=true' class='TextBold ms-lg-2 mt-lg-0 mt-2'>$langLogout</a>
                        </div>

                </div>
                ";
    }




    // Get all images from dir jumbotron_images
    $dirname = getcwd();
    $dirname = $dirname . '/template/modern/images/jumbotron_images';
    $dir_jumbotron_images = scandir($dirname);

    // Get all images from dir login_images
    $dirname2 = getcwd();
    $dirname2 = $dirname2 . '/template/modern/images/login_images';
    $dir_login_images = scandir($dirname2);

    // Get all images from dir form_images
    $dirname3 = getcwd();
    $dirname3= $dirname3 . '/template/modern/images/form_images';
    $dir_form_images = scandir($dirname3);

     // Get all images from dir form_images
     $dirname4 = getcwd();
     $dirname4 = $dirname4 . '/template/modern/images/registration_images';
     $dir_registration_images = scandir($dirname4);

     // Get all images from dir faq_images
     $dirname5 = getcwd();
     $dirname5 = $dirname5 . '/template/modern/images/faq_images';
     $dir_faq_images = scandir($dirname5);


    @$tool_content .= "
    <div class='row m-auto mb-4'>
        <div class='col-lg-6 col-12 ms-auto me-auto'>
            <div class='form-wrapper form-edit theme-option-wrapper'>
                <div class='d-flex justify-content-start align-items-center gap-2'>
                    <h2 class='text-heading-h3 mb-1'>$langActiveTheme:</h2>
                    ".$themes_arr[$active_theme]."
                </div>
                <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' id='theme_selection'>
                    <div class='form-group mt-4'>
                        <div class='col-12'>
                            <label for='theme_selection' class='col-12 control-label-notes'>$langAvailableThemes:</label>
                            ".  selection($themes_arr, 'active_theme_options', $theme_id, 'class="form-control form-submit" id="theme_selection"')."
                        </div>
                    </div>
                    ". generate_csrf_token_form_field() ."
                </form>
                <div class='form-group mt-4 margin-bottom-fat'>
                    <div class='col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                        $activate_btn
                        $preview_btn
                        $delete_btn
                    </div>
                </div>
            </div>
        </div>
    </div>";

$tool_content .= "
<div class='col-12'>
    <div class='card panelCard theme_creation_panel h-100'>
        <div class='card-body'>
            <form id='theme_options_form' class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' enctype='multipart/form-data' method='post'>

                <div class='d-flex align-items-start'>
                    <div class='nav flex-column nav-pills me-3' id='v-pills-tab-1' role='tablist' aria-orientation='vertical'>
                        <h4 class='mb-3 ms-auto me-auto text-nowrap'>$langGenSettings</h4>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 active text-nowrap' data-bs-target='#generalsetting' aria-controls='generalsetting' role='tab' data-bs-toggle='pill' aria-selected='true'>$langPlatformView</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 text-nowrap' data-bs-target='#navsettingsBody' aria-controls='navsettingsBody' role='tab' data-bs-toggle='pill' aria-selected='false'>$langNavBody</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 text-nowrap' data-bs-target='#navsettingsHeader' aria-controls='navsettingsHeader' role='tab' data-bs-toggle='pill' aria-selected='false'>$langNavSettingsHeader</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 text-nowrap' data-bs-target='#navsettingsMainSection' aria-controls='navsettingsMainSection' role='tab' data-bs-toggle='pill' aria-selected='false'>$langNavSettingsnavsettingsMainSection</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 text-nowrap' data-bs-target='#navsettingsFooter' aria-controls='navsettingsFooter' role='tab' data-bs-toggle='pill' aria-selected='false'>$langNavSettingsFooter</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 text-nowrap' data-bs-target='#navsettingsLoginHomepage' aria-controls='navsettingsLoginHomepage' role='tab' data-bs-toggle='pill' aria-selected='false'>$langHomePage</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 text-nowrap' data-bs-target='#navcontainer' aria-controls='navcontainer' role='tab' data-bs-toggle='pill' aria-selected='false'>$langPortfolio</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 text-nowrap' data-bs-target='#navsettings' aria-controls='navsettings' role='tab' data-bs-toggle='pill' aria-selected='false'>$langCoursePage</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navsettingsAgenda' aria-controls='navsettingsAgenda' role='tab' data-bs-toggle='pill' aria-selected='false'>$langAgenda</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-1 text-nowrap' data-bs-target='#navMoreOptions' aria-controls='navMoreOptions' role='tab' data-bs-toggle='pill' aria-selected='false'>$langNavMoreOptions</button>                         
                    </div>
                    <div class='tab-content w-100' id='v-pills-tabContent'>
                        <!-- GENERAL SETTINGS -->
                        " . build_general_settings() . "

                        <!-- BODY SETTINGS -->
                        " . build_body() . "

                        <!-- HEADER SETTINGS -->
                        " . build_header() . "

                        <!-- MAIN SETTINGS -->
                        " . build_main() . "

                        <!-- FOOTER SETTINGS -->
                        " . build_footer() . "

                        <!-- LINKS SETTINGS -->
                        " . build_links() . "

                        <!-- BUTTON SETTINGS -->
                        " . build_buttons() . "

                        <!-- TEXT SETTINGS -->
                        " . build_typography() . "

                        <!-- CARD SETTINGS -->
                        " . build_cards() . "

                        <!-- FORM SETTINGS -->
                        " . build_forms() . "

                        <!-- RADIO SETTINGS -->
                        " . build_radios() . "

                        <!-- CHECKBOX SETTINGS -->
                        " . build_checkboxes() . "

                        <!-- INPUT SETTINGS -->
                        " . build_inputs() . "

                        <!-- TEXT EDITOR SETTINGS -->
                        " . build_text_editor() . "

                        <!-- SELECT SETTINGS -->
                        " . build_select() . "

                        <!-- MODAL SETTINGS -->
                        " . build_modal() . "

                        <!-- TABLE SETTINGS -->
                        " . build_tables() . "

                        <!-- TAB SETTINGS -->
                        " . build_tabs() . "

                        <!-- ACCORDION SETTINGS -->
                        " . build_accordion() . "

                        <!-- LIST SETTINGS -->
                        " . build_list_group() . "

                        <!-- CONTEXTUAL MENU SETTINGS -->
                        " . build_contextual_menu() . "

                        <!-- MENU POPOVER SETTINGS -->
                        " . build_menu_popover() . "

                        <!-- AGENDA SETTINGS -->
                        " . build_agenda() . "

                        <!-- SCROLLBAR SETTINGS -->
                        " . build_scrollbar() . "

                        <!-- BADGES SETTINGS -->
                        " . build_badges() . "

                        <!-- PROGRESSBAR SETTINGS -->
                        " . build_progress_bar() . "

                        <!-- TOOLTIP SETTINGS -->
                        " . build_tooltips() . "

                        <!-- ALERT SETTINGS -->
                        " . build_alerts() . "

                        <!-- HOMEPAGE SETTINGS -->
                        " . build_homepage() . "

                        <!-- PORTFOLIO SETTINGS -->
                        " . build_portfolio() . "

                        <!-- COURSEHOME SETTINGS -->
                        " . build_coursehome() . "

                        <!-- MORE OPTIONS SETTINGS -->
                        " . build_more_options() . "
                    
                        <div class='form-group mt-5'>
                            <div class='col-12 d-flex justify-content-center align-items-center gap-2 flex-wrap'>
                                ".($theme_id ? "<input class='btn successAdminBtn $options_save_class' name='optionsSave' type='submit' value='$langSave'>" : "")."
                                <input class='btn successAdminBtn' name='optionsSaveAs' id='optionsSaveAs' type='submit' value='$langSaveAs'>
                                ".($theme_id ? "<a class='btn btn-default' href='theme_options.php?export=true'>$langExport</a>" : "")."
                            </div>
                        </div>
                    </div>
                    <div class='nav flex-column nav-pills ms-3' id='v-pills-tab-2' role='tablist' aria-orientation='vertical'>
                        <h4 class='mb-3 ms-auto me-auto text-nowrap'>$langComponents</h4>
                                                <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navHyperTexts' aria-controls='navHyperTexts' role='tab' data-bs-toggle='pill' aria-selected='false'>Text</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navLinks' aria-controls='navLinks' role='tab' data-bs-toggle='pill' aria-selected='false'>Link</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navButtons' aria-controls='navButtons' role='tab' data-bs-toggle='pill' aria-selected='false'>Button</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navPanels' aria-controls='navPanels' role='tab' data-bs-toggle='pill' aria-selected='false'>Card</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navForms' aria-controls='navForms' role='tab' data-bs-toggle='pill' aria-selected='false'>Form</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navRadios' aria-controls='navRadios' role='tab' data-bs-toggle='pill' aria-selected='false'>Radio</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navCheckboxes' aria-controls='navCheckboxes' role='tab' data-bs-toggle='pill' aria-selected='false'>Checkbox</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navInputText' aria-controls='navInputText' role='tab' data-bs-toggle='pill' aria-selected='false'>Input</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navTextEditor' aria-controls='navTextEditor' role='tab' data-bs-toggle='pill' aria-selected='false'>Editor</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navSelect' aria-controls='navSelect' role='tab' data-bs-toggle='pill' aria-selected='false'>Select</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navModal' aria-controls='navModal' role='tab' data-bs-toggle='pill' aria-selected='false'>Modal</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navTables' aria-controls='navTables' role='tab' data-bs-toggle='pill' aria-selected='false'>Table</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navTabs' aria-controls='navTabs' role='tab' data-bs-toggle='pill' aria-selected='false'>Tab</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navAccordions' aria-controls='navAccordions' role='tab' data-bs-toggle='pill' aria-selected='false'>Accordion</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navLists' aria-controls='navLists' role='tab' data-bs-toggle='tab' aria-selected='false'>List group</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navContextualMenu' aria-controls='navContextualMenu' role='tab' data-bs-toggle='pill' aria-selected='false'>Contextual Menu</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navMenuPopover' aria-controls='navMenuPopover' role='tab' data-bs-toggle='pill' aria-selected='false'>Menu popover</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navsettingsScrollBar' aria-controls='navsettingsScrollBar' role='tab' data-bs-toggle='pill' aria-selected='false'>Scrollbar</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navsettingsBadge' aria-controls='navsettingsBadge' role='tab' data-bs-toggle='pill' aria-selected='false'>Badge</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navsettingsProgressBar' aria-controls='navsettingsProgressBar' role='tab' data-bs-toggle='pill' aria-selected='false'>ProgressBar</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navsettingsTooltip' aria-controls='navsettingsTooltip' role='tab' data-bs-toggle='pill' aria-selected='false'>Tooltip</button>
                        <button type='button' class='nav-link nav-link-adminTools Neutral-900-cl nav-link-2 text-nowrap' data-bs-target='#navsettingsAlerts' aria-controls='navsettingsAlerts' role='tab' data-bs-toggle='tab' aria-selected='false'>Alert</button>
                    </div>
                </div>


                 ". generate_csrf_token_form_field() ."
            </form>
        </div>
    </div>
</div>


<div class='modal fade' id='JumbotronImagesModal' tabindex='-1' aria-labelledby='JumbotronImagesModalLabel' aria-hidden='true'>
    <div class='modal-dialog modal-lg'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='JumbotronImagesModalLabel'>$langLoginImg (jumbotron)</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='$langClose'></button>
            </div>
            <div class='modal-body'>
                <div class='row row-cols-1 row-cols-md-2 g-4'>";
                        foreach($dir_jumbotron_images as $image) {
                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                            $imgExtArr = ['jpg', 'jpeg', 'png'];
                            if(in_array($extension, $imgExtArr)){
                                $tool_content .= "
                                    <div class='col'>
                                        <div class='card panelCard card-default h-100'>
                                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/jumbotron_images/$image' alt='image jumbotron'/>
                                            <div class='card-body'>
                                                <p>$image</p>

                                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseJumbotronImage mt-3' value='$langSelect'>
                                            </div>
                                        </div>
                                    </div>
                                ";
                            }

                        }

$tool_content .= "
                </div>
            </div>
        </div>
    </div>
</div>



<div class='modal fade' id='LoginImagesModal' tabindex='-1' aria-labelledby='LoginImagesModalLabel' aria-hidden='true'>
    <div class='modal-dialog modal-lg'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='LoginImagesModalLabel'>$langLoginImg</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='$langClose'></button>
            </div>
            <div class='modal-body'>
                <div class='row row-cols-1 row-cols-md-2 g-4'>";
                        foreach($dir_login_images as $image) {
                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                            $imgExtArr = ['jpg', 'jpeg', 'png'];
                            if(in_array($extension, $imgExtArr)){
                                $tool_content .= "
                                    <div class='col'>
                                        <div class='card panelCard card-default h-100'>
                                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/login_images/$image' alt='image login'/>
                                            <div class='card-body'>
                                                <p>$image</p>

                                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseLoginImage mt-3' value='$langSelect'>
                                            </div>
                                        </div>
                                    </div>
                                ";
                            }

                        }

$tool_content .= "
                </div>
            </div>
        </div>
    </div>
</div>


<div class='modal fade' id='FormImagesModal' tabindex='-1' aria-labelledby='FormImagesModalLabel' aria-hidden='true'>
    <div class='modal-dialog modal-lg'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='FormImagesModalLabel'>$langFormImg</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='$langClose'></button>
            </div>
            <div class='modal-body'>
                <div class='row row-cols-1 row-cols-md-2 g-4'>";
                        foreach($dir_form_images as $image) {
                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                            $imgExtArr = ['jpg', 'jpeg', 'png', 'svg'];
                            if(in_array($extension, $imgExtArr)){
                                $tool_content .= "
                                    <div class='col'>
                                        <div class='card panelCard card-default h-100'>
                                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/form_images/$image' alt='$langFormImg'/>
                                            <div class='card-body'>
                                                <p>$image</p>

                                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseFormImage mt-3' value='$langSelect'>
                                            </div>
                                        </div>
                                    </div>
                                ";
                            }

                        }

$tool_content .= "
                </div>
            </div>
        </div>
    </div>
</div>



<div class='modal fade' id='RegistrationImagesModal' tabindex='-1' aria-labelledby='RegistrationImagesModalLabel' aria-hidden='true'>
    <div class='modal-dialog modal-lg'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='RegistrationImagesModalLabel'>$langFormRegistrationImg</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='$langClose'></button>
            </div>
            <div class='modal-body'>
                <div class='row row-cols-1 row-cols-md-2 g-4'>";
                        foreach($dir_registration_images as $image) {
                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                            $imgExtArr = ['jpg', 'jpeg', 'png', 'svg'];
                            if(in_array($extension, $imgExtArr)){
                                $tool_content .= "
                                    <div class='col'>
                                        <div class='card panelCard card-default h-100'>
                                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/registration_images/$image' alt='$langRegistration'/>
                                            <div class='card-body'>
                                                <p>$image</p>

                                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseRegistrationImage mt-3' value='$langSelect'>
                                            </div>
                                        </div>
                                    </div>
                                ";
                            }

                        }

$tool_content .= "
                </div>
            </div>
        </div>
    </div>
</div>

<div class='modal fade' id='FaqImagesModal' tabindex='-1' aria-labelledby='FaqImagesModalLabel' aria-hidden='true'>
    <div class='modal-dialog modal-lg'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='FaqImagesModalLabel'>$langfaqImg</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='$langClose'></button>
            </div>
            <div class='modal-body'>
                <div class='row row-cols-1 row-cols-md-2 g-4'>";
                        foreach($dir_faq_images as $image) {
                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                            $imgExtArr = ['jpg', 'jpeg', 'png', 'svg'];
                            if(in_array($extension, $imgExtArr)){
                                $tool_content .= "
                                    <div class='col'>
                                        <div class='card panelCard card-default h-100'>
                                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/faq_images/$image' alt='$langFaq'/>
                                            <div class='card-body'>
                                                <p>$image</p>

                                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseFaqImage mt-3' value='$langSelect'>
                                            </div>
                                        </div>
                                    </div>
                                ";
                            }

                        }

$tool_content .= "
                </div>
            </div>
        </div>
    </div>
</div>


";
}

function clear_default_settings() {
    global $defaults;
    foreach ($defaults as $setting => $option_array) {
        foreach ($option_array as $option){
            if(isset($_POST[$option]) && $_POST[$option] == $setting) unset($_POST[$option]);
        }
    }
    if(isset($_POST['themeOptionsName'])) unset($_POST['themeOptionsName']);
    if(isset($_POST['optionsSave'])) unset($_POST['optionsSave']); //unnecessary submit button value
}
function initialize_settings() {
    global $theme_options_styles, $defaults;

    foreach ($defaults as $setting => $option_array) {
        foreach ($option_array as $option){
            if(!isset($theme_options_styles[$option])) $theme_options_styles[$option] = $setting;
        }
    }
}
function clone_images($new_theme_id = null) {
    global $webDir, $theme, $theme_id;
    if (!is_dir("$webDir/courses/theme_data/$new_theme_id")) {
        make_dir("$webDir/courses/theme_data/$new_theme_id");
    }
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg','loginImgL','imageUploadFooter','imageUploadForm', 'imageUploadRegistration', 'imageUploadFaq', 'RightColumnCourseBgImage','faviconUpload','contactUpload', 'imageUploadBriefProfilePortfolio', 'loginBgImage');
    foreach($images as $image) {
        if (isset($_POST[$image])) {
            $image_name = $_POST[$image];
            if(copy("$webDir/courses/theme_data/".intval($theme_id)."/$image_name", "$webDir/courses/theme_data/$new_theme_id/$image_name")){
                $_POST[$image] = $image_name;
            }
        }
    }
}
function upload_images($new_theme_id = null) {
    global $webDir, $theme, $theme_id;
    if (isset($new_theme_id)) $theme_id = $new_theme_id;
    if (!is_dir("$webDir/courses/theme_data/$theme_id")) {
        make_dir("$webDir/courses/theme_data/$theme_id", 0755);
    }
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg','loginImgL','imageUploadFooter','imageUploadForm', 'imageUploadRegistration', 'imageUploadFaq', 'RightColumnCourseBgImage','faviconUpload','contactUpload', 'imageUploadBriefProfilePortfolio', 'loginBgImage');
    foreach($images as $image) {
        if (isset($_FILES[$image]) && is_uploaded_file($_FILES[$image]['tmp_name'])) {
            $file_name = $_FILES[$image]['name'];
            validateUploadedFile($file_name, 2);
            $i=0;
            while (is_file("$webDir/courses/theme_data/$theme_id/$file_name")) {
                $i++;
                $name = pathinfo($file_name, PATHINFO_FILENAME);
                $ext =  get_file_extension($file_name);
                $file_name = "$name-$i.$ext";
            }
            move_uploaded_file($_FILES[$image]['tmp_name'], "$webDir/courses/theme_data/$theme_id/$file_name");
            require_once 'modules/admin/extconfig/externals.php';
            $connector = AntivirusApp::getAntivirus();
            if($connector->isEnabled() == true ){
                $output=$connector->check("$webDir/courses/theme_data/$theme_id/$file_name");
                if($output->status==$output::STATUS_INFECTED){
                    AntivirusApp::block($output->output);
                }
            }
            $_POST[$image] = $file_name;
        }
    }
}



// General settings
function build_general_settings() {
    global $langForm, $langViewPlatform, $langSettingSelect, $theme_options_styles, $langViewBoxedType,
           $langHelpBoxedWidthInfo, $langViewFluidType, $langHelpFluidWidthInfo, $langLayoutConfig, $langLayout,
           $langBoxed, $langFluid, $langFluidContainerWidth, $langLogoConfig, $langLogo, $langLogoNormal, $logo_field,
           $langLogoSmall, $small_logo_field, $langFavicon, $faviconUpload, $urlServer, $langDisplayOptionsImg, 
           $langDisplayPlatformAsCardLayout, $langDisplayPlatformAsCardLayoutNoBorderRadius, $head_content;

    $head_content .= "
    <script>
        $(function() {
            if($('#enable_aside_main_cards').is(':checked')){
                $('.enable_main_card_checkbox').removeClass('d-none').addClass('d-block');
                $('.bgColorSectionContainersPalette').removeClass('d-none').addClass('d-block');
            }
            $('#enable_aside_main_cards').on('click', function () {
                if($('#enable_aside_main_cards').is(':checked')){
                    $('.enable_main_card_checkbox').removeClass('d-none').addClass('d-block');
                    $('.bgColorSectionContainersPalette').removeClass('d-none').addClass('d-block');
                } else {
                    $('.enable_main_card_checkbox').removeClass('d-block').addClass('d-none');
                $('.bgColorSectionContainersPalette').removeClass('d-block').addClass('d-none');
                }
            });
        });
    </script>
    ";

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade show active' id='generalsetting' role='tab'>
                <div class='form-wrapper form-edit form-create-theme rounded'>
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                            <div>
                                <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langViewPlatform</h2>
                                <div class='form-group'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='view_platform' id='view_platform_boxed' value='boxed' ".(($theme_options_styles['view_platform'] == 'boxed')? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langHelpBoxedWidthInfo
                                        </label>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='view_platform' id='view_platform_fluid' value='fluid' ".(($theme_options_styles['view_platform'] == 'fluid')? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langHelpFluidWidthInfo
                                        </label>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='enable_aside_main_cards' id='enable_aside_main_cards' value='1' ".((isset($theme_options_styles['enable_aside_main_cards']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langDisplayPlatformAsCardLayout
                                        </label>
                                    </div>
                                </div>
                                <div class='form-group mt-4 enable_main_card_checkbox d-none'>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='enable_aside_main_cards_no_border_radius' id='enable_aside_main_cards_no_border_radius' value='1' ".((isset($theme_options_styles['enable_aside_main_cards_no_border_radius']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langDisplayPlatformAsCardLayoutNoBorderRadius
                                        </label>
                                    </div>
                                </div>
                                <div class='form-group mt-4 d-none bgColorSectionContainersPalette'>
                                    <label for='bgColorSectionContainers' class='mb-2 me-2'>Χρώμα φόντου στην μορφή των καρτών (cards):</label>
                                    <input name='bgColorSectionContainers' type='text' class='form-control colorpicker' id='bgColorSectionContainers' value='$theme_options_styles[bgColorSectionContainers]'>
                                </div>
                                <hr>
                                <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langLayoutConfig</h2>
                                <div class='form-group'>
                                    <div class='col-sm-12 mb-2'>$langLayout:</div>
                                    <div class='form-inline col-sm-12'>
                                        <div class='row'>
                                            <div class='col-sm-3'>
                                                <div class='radio'>
                                                    <label>
                                                    <input type='radio' name='containerType' value='boxed' ".(($theme_options_styles['containerType'] == 'boxed')? 'checked' : '').">
                                                        $langBoxed &nbsp;
                                                    </label>
                                                </div>
                                            </div>
                                            <div class='col-sm-9'>
                                                <div class='radio'>
                                                    <label>
                                                    <input type='radio' name='containerType' value='fluid' ".(($theme_options_styles['containerType'] == 'fluid')? 'checked' : '').">
                                                        $langFluid &nbsp;
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group".(($theme_options_styles['containerType'] == 'boxed')? ' hidden' : '')." mt-4'>
                                    <label for='fluidContainerWidth' class='col-sm-6 mb-2'>$langFluidContainerWidth:</label>
                                    <div class='col-sm-12'>
                                        <input id='fluidContainerWidth' name='fluidContainerWidth' data-slider-id='ex1Slider' type='text' data-slider-min='1140' data-slider-max='1920' data-slider-step='10' data-slider-value='$theme_options_styles[fluidContainerWidth]' ".(($theme_options_styles['containerType'] == 'boxed')? ' disabled' : '').">
                                        <span style='margin-left:10px;' id='pixelCounter'></span>
                                    </div>
                                </div>
                                <hr>
                                <h2 class='theme_options_legend text-decoration-underline mt-2 text-heading-h3'>$langLogoConfig</h2>
                                <div class='form-group'>
                                    <div class='col-sm-12 mb-2'>$langLogo <small>$langLogoNormal</small>:</div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        $logo_field
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 mb-2'>$langLogo <small>$langLogoSmall</small>:</div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        $small_logo_field
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 mb-2'>$langFavicon </div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        $faviconUpload
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </fieldset>
                </div>
            </div>
    ";

    return $html;


}

// Body
function build_body() {
    global $langForm, $langForm, $langBgColor, $theme_options_styles, $langBgImg, $bg_field, $langConfig,
            $langRepeatedImg, $langFixedImg, $langStretchedImg, $langSettingSelect, $langAddOpacityImage,
            $urlServer, $head_content;
            
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsBody' role='tab'>
                <div class='form-wrapper form-edit form-create-theme rounded'>
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                            <div>
                                <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langConfig (Body)</h2>
                                <div class='form-group mt-4'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='bgOpacityImage' value='1' ".((isset($theme_options_styles['bgOpacityImage']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langAddOpacityImage
                                        </label>
                                    </div>
                                </div>
                                <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                    <label for='bgColor' class='mb-2 me-2'>$langBgColor:</label>
                                    <input name='bgColor' type='text' class='form-control colorpicker' id='bgColor' value='$theme_options_styles[bgColor]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 mb-2'>$langBgImg:</div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        $bg_field
                                    </div>
                                    <div class='form-inline col-sm-9 col-sm-offset-3 mt-2'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='bgType' value='repeat' ".(($theme_options_styles['bgType'] == 'repeat')? 'checked' : '').">
                                                $langRepeatedImg &nbsp;
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='bgType' value='fix' ".(($theme_options_styles['bgType'] == 'fix')? 'checked' : '').">
                                                $langFixedImg &nbsp;
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='bgType' value='stretch' ".(($theme_options_styles['bgType'] == 'stretch')? 'checked' : '').">
                                                $langStretchedImg &nbsp;
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>";


    return $html;


}


// header
function build_header() {
    global $langConfig, $langBgColor, $theme_options_styles, $langLinkColor, $langLinkHoverColor,
           $langActiveLinkBgColorHeader, $langActiveLinkColorHeader, $langHoveredActiveLinkColorHeader,
           $langShadowHeader, $langSettingSelect, $langEnableBoxLogo, $langDeactivate, $langActivate;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsHeader'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langConfig (Header)</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorWrapperHeader' class='mb-2 me-2'>$langBgColor:</label>
                                <input name='BgColorWrapperHeader' type='text' class='form-control colorpicker' id='BgColorWrapperHeader' value='$theme_options_styles[BgColorWrapperHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkColorHeader' class=' mb-2 me-2'>$langLinkColor:</label>
                                <input name='linkColorHeader' type='text' class='form-control colorpicker' id='linkColorHeader' value='$theme_options_styles[linkColorHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkHoverColorHeader' class='mb-2 me-2'>$langLinkHoverColor:</label>
                                <input name='linkHoverColorHeader' type='text' class='form-control colorpicker' id='linkHoverColorHeader' value='$theme_options_styles[linkHoverColorHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkActiveBgColorHeader' class='mb-2 me-2'>$langActiveLinkBgColorHeader:</label>
                                <input name='linkActiveBgColorHeader' type='text' class='form-control colorpicker' id='linkActiveBgColorHeader' value='$theme_options_styles[linkActiveBgColorHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkActiveColorHeader' class='mb-2 me-2'>$langActiveLinkColorHeader:</label>
                                <input name='linkActiveColorHeader' type='text' class='form-control colorpicker' id='linkActiveColorHeader' value='$theme_options_styles[linkActiveColorHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='HoveredActiveLinkColorHeader' class='mb-2 me-2'>$langHoveredActiveLinkColorHeader:</label>
                                <input name='HoveredActiveLinkColorHeader' type='text' class='form-control colorpicker' id='HoveredActiveLinkColorHeader' value='$theme_options_styles[HoveredActiveLinkColorHeader]'>
                            </div>
                            <div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                        <input type='checkbox' name='shadowHeader' value='1' ".((isset($theme_options_styles['shadowHeader']))? 'checked' : '').">
                                        <span class='checkmark'></span>
                                        $langShadowHeader
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='col-sm-12 mt-4'>
                                <div class='checkbox'>
                                    <label class='label-container' aria-label='$langSettingSelect'>
                                        <input id='enableBoxLogoId' type='checkbox' name='enableBoxLogo' value='1' ".((isset($theme_options_styles['enableBoxLogo']))? 'checked' : '').">
                                        <span class='checkmark'></span>
                                        $langEnableBoxLogo
                                    </label>
                                </div>
                            </div>
                            <div class='col-sm-12 logo-container d-none mt-4'>
                                <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                    <label for='BgColorContainerLogo' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                    <input name='BgColorContainerLogo' type='text' class='form-control colorpicker' id='BgColorContainerLogo' value='$theme_options_styles[BgColorContainerLogo]'>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

//Main
function build_main() {
    global $langConfig, $langBgColor, $theme_options_styles, $langBorderColorLeftRight,
           $langSettingSelect, $langActivate, $head_content, $langActivateBorder;

    $head_content .= "
    <script>
        $(function() {
            if($('#EnableBorderSidesOfMain').is(':checked')){
                $('.enable_border_main').removeClass('d-none').addClass('d-block');
            }
            $('#EnableBorderSidesOfMain').on('click', function () {
                if($('#EnableBorderSidesOfMain').is(':checked')){
                    $('.enable_border_main').removeClass('d-none').addClass('d-block');
                } else {
                    $('.enable_border_main').removeClass('d-block').addClass('d-none');
                }
            });
        });
    </script>
    ";

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsMainSection'>
                <div class='form-wrapper form-edit rounded'>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langConfig (Main)</h2>
                    <div class='form-group mt-4'>
                        <label for='bgColorContentPlatform' class='mb-2 me-2'>$langBgColor:</label>
                        <input name='bgColorContentPlatform' type='text' class='form-control colorpicker' id='bgColorContentPlatform' value='$theme_options_styles[bgColorContentPlatform]'>
                    </div>
                    <div class='form-group mt-4'>
                        <div class='checkbox'>
                            <label class='label-container' aria-label='$langSettingSelect'>
                            <input id='EnableBorderSidesOfMain' type='checkbox' name='EnableBorderSidesOfMain' value='1' ".((isset($theme_options_styles['EnableBorderSidesOfMain']))? 'checked' : '').">
                            <span class='checkmark'></span>
                                $langActivateBorder
                            </label>
                        </div>
                    </div>
                    <div class='form-group mt-4 d-none enable_border_main'>
                        <label for='borderColorContentPlatformLeftRight' class='mb-2 me-2'>$langBorderColorLeftRight:</label>
                        <input name='borderColorContentPlatformLeftRight' type='text' class='form-control colorpicker' id='borderColorContentPlatformLeftRight' value='$theme_options_styles[borderColorContentPlatformLeftRight]'>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// footer
function build_footer() {
    global $langConfig, $langBgColor, $theme_options_styles, $langLinkColor,
           $langHoverLinkColorFooter, $langCopyright, $langFooterUploadImage, 
           $image_footer_field, $langLinkColorCopyrights, $langHoverLinkColorCopyrights;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsFooter'>
                <div class='form-wrapper form-edit rounded'>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langConfig (Footer)</h2>
                    <div class='form-group mt-4'>
                        <label for='bgColorWrapperFooter' class='mb-2 me-2'>$langBgColor:</label>
                        <input name='bgColorWrapperFooter' type='text' class='form-control colorpicker' id='bgColorWrapperFooter' value='$theme_options_styles[bgColorWrapperFooter]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='linkColorFooter' class='mb-2 me-2'>$langLinkColor:</label>
                        <input name='linkColorFooter' type='text' class='form-control colorpicker' id='linkColorFooter' value='$theme_options_styles[linkColorFooter]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='linkHoverColorFooter' class='mb-2 me-2'>$langHoverLinkColorFooter:</label>
                        <input name='linkHoverColorFooter' type='text' class='form-control colorpicker' id='linkHoverColorFooter' value='$theme_options_styles[linkHoverColorFooter]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='linkCopyrightColorFooter' class='mb-2 me-2'>$langLinkColorCopyrights:</label>
                        <input name='linkCopyrightColorFooter' type='text' class='form-control colorpicker' id='linkCopyrightColorFooter' value='$theme_options_styles[linkCopyrightColorFooter]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='linkCopyrightHoverColorFooter' class='mb-2 me-2'>$langHoverLinkColorCopyrights:</label>
                        <input name='linkCopyrightHoverColorFooter' type='text' class='form-control colorpicker' id='linkCopyrightHoverColorFooter' value='$theme_options_styles[linkCopyrightHoverColorFooter]'>
                    </div>
                    <div class='form-group mt-4'>
                        <div class='col-sm-12 mb-2'>$langFooterUploadImage:</div>
                        <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                            $image_footer_field
                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// links
function build_links() {
    global $langLinksCongiguration, $langLinkColor, $theme_options_styles,
           $langLinkHoverColor, $langDeleteLinkColor;
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navLinks'>
                <div class='form-wrapper form-edit rounded'>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langLinksCongiguration</h2>
                    <div class='form-group mt-4'>
                        <label for='linkColor' class=' mb-2 me-2'>$langLinkColor:</label>
                        <input name='linkColor' type='text' class='form-control colorpicker' id='linkColor' value='$theme_options_styles[linkColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='linkHoverColor' class='mb-2 me-2'>$langLinkHoverColor:</label>
                        <input name='linkHoverColor' type='text' class='form-control colorpicker' id='linkHoverColor' value='$theme_options_styles[linkHoverColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='linkDeleteColor' class='mb-2 me-2'>$langDeleteLinkColor:</label>
                        <input name='linkDeleteColor' type='text' class='form-control colorpicker' id='linkDeleteColor' value='$theme_options_styles[linkDeleteColor]'>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// buttons
function build_buttons() {
    global $langButtonsColorCongiguration, $langBgColor, $theme_options_styles, $langTextColor,
           $langHoverWhiteColorButton, $langButtonsColorWhiteCongiguration, $langButtonColorWhiteCongiguration,
           $langBorderTextColor, $langHoverTextColor, $langHoverBorderTextColor, $langHoverWhiteColorButton,
           $langButtonsColorDel, $langbgDeleteButtonColor, $langclDeleteButtonColor, $langbgHoveredDeleteButtonColor,
           $langclHoveredDeleteButtonColor, $langButtonsColorSuccess, $langbgSuccessButtonColor, $langclSuccessButtonColor,
           $langbgHoveredSuccessButtonColor, $langclHoveredSuccessButtonColor, $langButtonsColorHelp, $langbgHelpButtonColor,
           $langclHelpButtonColor, $langbgHoveredHelpButtonColor, $langclHoveredHelpButtonColor;
    $html = ''; 
    $html .= "
    <div role='tabpanel' class='tab-pane fade' id='navButtons'>
                <div class='form-wrapper form-edit rounded'>
                
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langButtonsColorCongiguration</h2>
                    <div class='form-group mt-4'>
                        <label for='buttonBgColor' class='mb-2 me-2'>$langBgColor:</label>
                        <input name='buttonBgColor' type='text' class='form-control colorpicker' id='buttonBgColor' value='$theme_options_styles[buttonBgColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='buttonTextColor' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='buttonTextColor' type='text' class='form-control colorpicker' id='buttonTextColor' value='$theme_options_styles[buttonTextColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='buttonHoverBgColor' class='mb-2 me-2'>$langHoverWhiteColorButton:</label>
                        <input name='buttonHoverBgColor' type='text' class='form-control colorpicker' id='buttonHoverBgColor' value='$theme_options_styles[buttonHoverBgColor]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline mt-2 text-heading-h3'>$langButtonsColorWhiteCongiguration</h2>
                    <div class='form-group mt-4'>
                        <label for='bgWhiteButtonColor' class='mb-2 me-2'>$langButtonColorWhiteCongiguration:</label>
                        <input name='bgWhiteButtonColor' type='text' class='form-control colorpicker' id='bgWhiteButtonColor' value='$theme_options_styles[bgWhiteButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='whiteButtonTextColor' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='whiteButtonTextColor' type='text' class='form-control colorpicker' id='whiteButtonTextColor' value='$theme_options_styles[whiteButtonTextColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='whiteButtonBorderTextColor' class='mb-2 me-2'>$langBorderTextColor:</label>
                        <input name='whiteButtonBorderTextColor' type='text' class='form-control colorpicker' id='whiteButtonBorderTextColor' value='$theme_options_styles[whiteButtonBorderTextColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='whiteButtonHoveredTextColor' class='cmb-2 me-2'>$langHoverTextColor:</label>
                        <input name='whiteButtonHoveredTextColor' type='text' class='form-control colorpicker' id='whiteButtonHoveredTextColor' value='$theme_options_styles[whiteButtonHoveredTextColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='whiteButtonHoveredBorderTextColor' class='mb-2 me-2'>$langHoverBorderTextColor:</label>
                        <input name='whiteButtonHoveredBorderTextColor' type='text' class='form-control colorpicker' id='whiteButtonHoveredBorderTextColor' value='$theme_options_styles[whiteButtonHoveredBorderTextColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='whiteButtonHoveredBgColor' class='mb-2 me-2'>$langHoverWhiteColorButton:</label>
                        <input name='whiteButtonHoveredBgColor' type='text' class='form-control colorpicker' id='whiteButtonHoveredBgColor' value='$theme_options_styles[whiteButtonHoveredBgColor]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline mt-2 text-heading-h3'>$langButtonsColorDel</h2>
                    <div class='form-group mt-4'>
                        <label for='bgDeleteButtonColor' class='mb-2 me-2'>$langbgDeleteButtonColor:</label>
                        <input name='bgDeleteButtonColor' type='text' class='form-control colorpicker' id='bgDeleteButtonColor' value='$theme_options_styles[bgDeleteButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clDeleteButtonColor' class='mb-2 me-2'>$langclDeleteButtonColor:</label>
                        <input name='clDeleteButtonColor' type='text' class='form-control colorpicker' id='clDeleteButtonColor' value='$theme_options_styles[clDeleteButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgHoveredDeleteButtonColor' class='mb-2 me-2'>$langbgHoveredDeleteButtonColor:</label>
                        <input name='bgHoveredDeleteButtonColor' type='text' class='form-control colorpicker' id='bgHoveredDeleteButtonColor' value='$theme_options_styles[bgHoveredDeleteButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clHoveredDeleteButtonColor' class='mb-2 me-2'>$langclHoveredDeleteButtonColor:</label>
                        <input name='clHoveredDeleteButtonColor' type='text' class='form-control colorpicker' id='clHoveredDeleteButtonColor' value='$theme_options_styles[clHoveredDeleteButtonColor]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline mt-2 text-heading-h3'>$langButtonsColorSuccess</h2>
                    <div class='form-group mt-4'>
                        <label for='bgSuccessButtonColor' class='mb-2 me-2'>$langbgSuccessButtonColor:</label>
                        <input name='bgSuccessButtonColor' type='text' class='form-control colorpicker' id='bgSuccessButtonColor' value='$theme_options_styles[bgSuccessButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clSuccessButtonColor' class='mb-2 me-2'>$langclSuccessButtonColor:</label>
                        <input name='clSuccessButtonColor' type='text' class='form-control colorpicker' id='clSuccessButtonColor' value='$theme_options_styles[clSuccessButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgHoveredSuccessButtonColor' class='cmb-2 me-2'>$langbgHoveredSuccessButtonColor:</label>
                        <input name='bgHoveredSuccessButtonColor' type='text' class='form-control colorpicker' id='bgHoveredSuccessButtonColor' value='$theme_options_styles[bgHoveredSuccessButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clHoveredSuccessButtonColor' class='mb-2 me-2'>$langclHoveredSuccessButtonColor:</label>
                        <input name='clHoveredSuccessButtonColor' type='text' class='form-control colorpicker' id='clHoveredSuccessButtonColor' value='$theme_options_styles[clHoveredSuccessButtonColor]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline mt-2 text-heading-h3'>$langButtonsColorHelp</h2>
                    <div class='form-group mt-4'>
                        <label for='bgHelpButtonColor' class='mb-2 me-2'>$langbgHelpButtonColor:</label>
                        <input name='bgHelpButtonColor' type='text' class='form-control colorpicker' id='bgHelpButtonColor' value='$theme_options_styles[bgHelpButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clHelpButtonColor' class='mb-2 me-2'>$langclHelpButtonColor:</label>
                        <input name='clHelpButtonColor' type='text' class='form-control colorpicker' id='clHelpButtonColor' value='$theme_options_styles[clHelpButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgHoveredHelpButtonColor' class='mb-2 me-2'>$langbgHoveredHelpButtonColor:</label>
                        <input name='bgHoveredHelpButtonColor' type='text' class='form-control colorpicker' id='bgHoveredHelpButtonColor' value='$theme_options_styles[bgHoveredHelpButtonColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clHoveredHelpButtonColor' class='mb-2 me-2'>$langclHoveredHelpButtonColor:</label>
                        <input name='clHoveredHelpButtonColor' type='text' class='form-control colorpicker' id='clHoveredHelpButtonColor' value='$theme_options_styles[clHoveredHelpButtonColor]'>
                    </div>
                        
                </div>
            </div>
    ";

    return $html;
}

// typography
function build_typography() {
    global $langPHyperTextColor, $langPHyperTextColor, $theme_options_styles,
           $langMytedTextColor, $langRedText, $langGreenText, $langBlueText, $langOrangeText;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navHyperTexts'>
                <div class='form-wrapper form-edit rounded'>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langPHyperTextColor</h2>
                    <div class='form-group mt-4'>
                        <label for='ColorHyperTexts' class='mb-2 me-2'>$langPHyperTextColor:</label>
                        <input name='ColorHyperTexts' type='text' class='form-control colorpicker' id='ColorHyperTexts' value='$theme_options_styles[ColorHyperTexts]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ColorMutedTexts' class='mb-2 me-2'>$langMytedTextColor:</label>
                        <input name='ColorMutedTexts' type='text' class='form-control colorpicker' id='ColorMutedTexts' value='$theme_options_styles[ColorMutedTexts]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ColorRedText' class='mb-2 me-2'>$langRedText:</label>
                        <input name='ColorRedText' type='text' class='form-control colorpicker' id='ColorRedText' value='$theme_options_styles[ColorRedText]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ColorGreenText' class='mb-2 me-2'>$langGreenText:</label>
                        <input name='ColorGreenText' type='text' class='form-control colorpicker' id='ColorGreenText' value='$theme_options_styles[ColorGreenText]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ColorBlueText' class='mb-2 me-2'>$langBlueText:</label>
                        <input name='ColorBlueText' type='text' class='form-control colorpicker' id='ColorBlueText' value='$theme_options_styles[ColorBlueText]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ColorOrangeText' class='mb-2 me-2'>$langOrangeText:</label>
                        <input name='ColorOrangeText' type='text' class='form-control colorpicker' id='ColorOrangeText' value='$theme_options_styles[ColorOrangeText]'>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// cards

function build_cards() {
    global $langConcerngingPanels, $langBgPanels, $theme_options_styles, $langclBorderPanels,
           $langBgHoveredPanels, $langHoverTextColor, $langHoveredBoxShadowPanels, $langConcerngingCommentsPanels,
           $langConcerngingQuestionnairePanels, $langConcerngingExercisePanels, $langConcerngingReportsPanels,
           $langConcerngingProgressActivitiesPanels, $langBoxShadowPanels;
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navPanels'>
                <div class='form-wrapper form-edit rounded'>

                    <div>
                        <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langConcerngingPanels</h2>
                        <div class='form-group mt-4'>
                            <label for='BgPanels' class='mb-2 me-2'>$langBgPanels:</label>
                            <input name='BgPanels' type='text' class='form-control colorpicker' id='BgPanels' value='$theme_options_styles[BgPanels]'>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='clBorderPanels' class='mb-2 me-2'>$langclBorderPanels:</label>
                            <input name='clBorderPanels' type='text' class='form-control colorpicker' id='clBorderPanels' value='$theme_options_styles[clBorderPanels]'>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='bgBorderHoveredPanels' class=' mb-2 me-2'>$langBgHoveredPanels:</label>
                            <input name='bgBorderHoveredPanels' type='text' class='form-control colorpicker' id='bgBorderHoveredPanels' value='$theme_options_styles[bgBorderHoveredPanels]'>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='clHoveredTextPanels' class='mb-2 me-2'>$langHoverTextColor:</label>
                            <input name='clHoveredTextPanels' type='text' class='form-control colorpicker' id='clHoveredTextPanels' value='$theme_options_styles[clHoveredTextPanels]'>
                        </div>
                            <div class='form-group mt-4'>
                            <label for='bgHoveredBoxShadowPanels' class='mb-2 me-2'>$langHoveredBoxShadowPanels:</label>
                            <input name='bgHoveredBoxShadowPanels' type='text' class='form-control colorpicker' id='bgHoveredBoxShadowPanels' value='$theme_options_styles[bgHoveredBoxShadowPanels]'>
                        </div>
                    </div>

                    <hr>

                    <div>
                        <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langConcerngingCommentsPanels</h2>
                        <div class='form-group mt-4'>
                            <label for='BgCommentsPanels' class=' mb-2 me-2'>$langBgPanels:</label>
                            <input name='BgCommentsPanels' type='text' class='form-control colorpicker' id='BgCommentsPanels' value='$theme_options_styles[BgCommentsPanels]'>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='clBorderBgCommentsPanels' class='mb-2 me-2'>$langclBorderPanels:</label>
                            <input name='clBorderBgCommentsPanels' type='text' class='form-control colorpicker' id='clBorderBgCommentsPanels' value='$theme_options_styles[clBorderBgCommentsPanels]'>
                        </div>
                    </div>

                    <hr>
                    
                    <div>
                        <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langConcerngingQuestionnairePanels</h2>
                        <div class='form-group mt-4'>
                            <label for='BgQuestionnairePanels' class='mb-2 me-2'>$langBgPanels:</label>
                            <input name='BgQuestionnairePanels' type='text' class='form-control colorpicker' id='BgQuestionnairePanels' value='$theme_options_styles[BgQuestionnairePanels]'>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='clBorderQuestionnairePanels' class='mb-2 me-2'>$langclBorderPanels:</label>
                            <input name='clBorderQuestionnairePanels' type='text' class='form-control colorpicker' id='clBorderQuestionnairePanels' value='$theme_options_styles[clBorderQuestionnairePanels]'>
                        </div>
                    </div>
                   
                    <hr>
                    
                    <div>
                        <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langConcerngingExercisePanels</h2>
                        <div class='form-group mt-4'>
                            <label for='BgExercisesPanels' class='mb-2 me-2'>$langBgPanels:</label>
                            <input name='BgExercisesPanels' type='text' class='form-control colorpicker' id='BgExercisesPanels' value='$theme_options_styles[BgExercisesPanels]'>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='clBorderExercisesPanels' class='mb-2 me-2'>$langclBorderPanels:</label>
                            <input name='clBorderExercisesPanels' type='text' class='form-control colorpicker' id='clBorderExercisesPanels' value='$theme_options_styles[clBorderExercisesPanels]'>
                        </div>
                    </div>
                   
                    <hr>
                    
                    <div>
                        <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langConcerngingReportsPanels</h2>
                        <div class='form-group mt-4'>
                            <label for='BgReportsPanels' class='mb-2 me-2'>$langBgPanels:</label>
                            <input name='BgReportsPanels' type='text' class='form-control colorpicker' id='BgReportsPanels' value='$theme_options_styles[BgReportsPanels]'>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='clBorderReportsPanels' class='mb-2 me-2'>$langclBorderPanels:</label>
                            <input name='clBorderReportsPanels' type='text' class='form-control colorpicker' id='clBorderReportsPanels' value='$theme_options_styles[clBorderReportsPanels]'>
                        </div>
                    </div>
                        
                    <hr>

                    
                    <div>
                        <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langConcerngingProgressActivitiesPanels</h2>
                        <div class='form-group mt-4'>
                            <label for='BgProgressActivitiesPanels' class='cmb-2 me-2'>$langBgPanels:</label>
                            <input name='BgProgressActivitiesPanels' type='text' class='form-control colorpicker' id='BgProgressActivitiesPanels' value='$theme_options_styles[BgProgressActivitiesPanels]'>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='clBorderProgressActivitiesPanels' class='mb-2 me-2'>$langclBorderPanels:</label>
                            <input name='clBorderProgressActivitiesPanels' type='text' class='form-control colorpicker' id='clBorderProgressActivitiesPanels' value='$theme_options_styles[clBorderProgressActivitiesPanels]'>
                        </div>
                    </div>
                

                    <hr>


                    
                    <div>
                        <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langBoxShadowPanels</h2>
                        <div class='form-group d-flex mt-4'>
                            <label for='BoxShadowPanels' class='mb-2 me-2'>$langBoxShadowPanels:</label>
                            <input name='BoxShadowPanels' type='text' class='form-control colorpicker' id='BoxShadowPanels' value='$theme_options_styles[BoxShadowPanels]'>
                        </div>
                    </div>
                    
                </div>
            </div>
    ";

    return $html;
}

// forms
function build_forms() {
    global $langForms, $langBgForms, $theme_options_styles, $langclBorderPanels,
           $langBoxShadowPanels, $langAddPadding, $langSettingSelect, $langActivate,
           $langColorLabel, $langColorRequiredField, $form_image_fieldL, $langwidthOfForm,
           $langStrechedImgOfForm, $langFixedImg, $langRepeatedImg, $langStretchedImg,
           $langAboutRegistrationImageUpload, $registration_image_fieldL;
    $html = '';
    $html .= "
    <div role='tabpanel' class='tab-pane fade' id='navForms'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langForms</h2>
                            <div class='form-group mt-4'>
                                <label for='BgForms' class='mb-2 me-2'>$langBgForms:</label>
                                <input name='BgForms' type='text' class='form-control colorpicker' id='BgForms' value='$theme_options_styles[BgForms]'>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='clLabelForms' class='mb-2 me-2'>$langColorLabel:</label>
                                <input name='clLabelForms' type='text' class='form-control colorpicker' id='clLabelForms' value='$theme_options_styles[clLabelForms]'>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='clRequiredFieldForm' class='mb-2 me-2'>$langColorRequiredField:</label>
                                <input name='clRequiredFieldForm' type='text' class='form-control colorpicker' id='clRequiredFieldForm' value='$theme_options_styles[clRequiredFieldForm]'>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='BgBorderForms' class='mb-2 me-2'>$langclBorderPanels:</label>
                                <input name='BgBorderForms' type='text' class='form-control colorpicker' id='BgBorderForms' value='$theme_options_styles[BgBorderForms]'>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='FormsBoxShadow' class='mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='FormsBoxShadow' type='text' class='form-control colorpicker' id='FormsBoxShadow' value='$theme_options_styles[FormsBoxShadow]'>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='checkbox'>
                                    <label class='label-container' aria-label='$langSettingSelect'>
                                        <input type='checkbox' name='AddPaddingFormWrapper' value='1' ".((isset($theme_options_styles['AddPaddingFormWrapper']))? 'checked' : '').">
                                        <span class='checkmark'></span>
                                        $langAddPadding
                                    </label>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input id='widthOfFormId' type='checkbox' name='widthOfForm' value='1' ".((isset($theme_options_styles['widthOfForm']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langwidthOfForm
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group sliderWidthImgFormClass mt-4' style='display:none;'>
                                <div class='col-sm-12'>
                                    <input id='sliderWidthImgForm' name='sliderWidthImgForm' data-slider-id='exImgSlider' type='text' data-slider-min='50' data-slider-max='85' data-slider-step='1' data-slider-value='$theme_options_styles[sliderWidthImgForm]'>
                                    <span style='margin-left:10px;' id='sliderWidthImgFormCounter'></span>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input id='strechedImgOfFormId' type='checkbox' name='strechedImgOfForm' value='1' ".((isset($theme_options_styles['strechedImgOfForm']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langStrechedImgOfForm
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group streched_repeaded_img_form_class mt-4' style='display:none;'>
                                <div class='radio mb-2'>
                                    <label>
                                        <input type='radio' name='TypeImageForm' value='fixed' ".((isset($theme_options_styles['TypeImageForm']) && $theme_options_styles['TypeImageForm'] == 'fixed')? 'checked' : '').">
                                        $langFixedImg
                                    </label>
                                </div>
                                <div class='radio mb-2'>
                                    <label>
                                        <input type='radio' name='TypeImageForm' value='repeated' ".((isset($theme_options_styles['TypeImageForm']) && $theme_options_styles['TypeImageForm'] == 'repeated')? 'checked' : '').">
                                        $langRepeatedImg
                                    </label>
                                </div>
                                <div class='radio mb-2'>
                                    <label>
                                        <input type='radio' name='TypeImageForm' value='streched' ".((isset($theme_options_styles['TypeImageForm']) && $theme_options_styles['TypeImageForm'] == 'streched')? 'checked' : '').">
                                        $langStretchedImg
                                    </label>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    $form_image_fieldL
                                </div>
                            </div>

                        </div>
                    </div>

                    <hr>
                    
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langAboutRegistrationImageUpload</h2>
                            <div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    $registration_image_fieldL
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// radios
function build_radios() {
    global $langRadios, $langBgRadios, $theme_options_styles, $langBgBorderRadios,
           $langClRadios, $langBgClRadios, $langClIconRadios, $langClInactiveRadios;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navRadios'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langRadios</h2>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgRadios' class='mb-2 me-2'>$langBgRadios:</label>
                        <input name='BgRadios' type='text' class='form-control colorpicker' id='BgRadios' value='$theme_options_styles[BgRadios]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgBorderRadios' class='mb-2 me-2'>$langBgBorderRadios:</label>
                        <input name='BgBorderRadios' type='text' class='form-control colorpicker' id='BgBorderRadios' value='$theme_options_styles[BgBorderRadios]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='ClRadios' class='mb-2 me-2'>$langClRadios:</label>
                        <input name='ClRadios' type='text' class='form-control colorpicker' id='ClRadios' value='$theme_options_styles[ClRadios]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgClRadios' class='comb-2 me-2'>$langBgClRadios:</label>
                        <input name='BgClRadios' type='text' class='form-control colorpicker' id='BgClRadios' value='$theme_options_styles[BgClRadios]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='ClIconRadios' class='mb-2 me-2'>$langClIconRadios:</label>
                        <input name='ClIconRadios' type='text' class='form-control colorpicker' id='ClIconRadios' value='$theme_options_styles[ClIconRadios]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='ClInactiveRadios' class= mb-2 me-2'>$langClInactiveRadios:</label>
                        <input name='ClInactiveRadios' type='text' class='form-control colorpicker' id='ClInactiveRadios' value='$theme_options_styles[ClInactiveRadios]'>
                    </div>
                    
                </div>
            </div>
    ";

    return $html;
}

// checkboxes
function build_checkboxes() {
    global $langCheckboxes, $langBgCheckboxes, $theme_options_styles, $langBgBorderCheckboxes,
           $langClCheckboxes, $langBgActiveCheckboxes, $langClActiveCheckboxes, $langClIconCheckboxes,
           $langClIconCheckboxes, $langClInactiveCheckboxes;
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navCheckboxes'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langCheckboxes</h2>
                    <div class='form-group mt-4'>
                        <label for='BgCheckboxes' class='mb-2 me-2'>$langBgCheckboxes:</label>
                        <input name='BgCheckboxes' type='text' class='form-control colorpicker' id='BgCheckboxes' value='$theme_options_styles[BgCheckboxes]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BgBorderCheckboxes' class='mb-2 me-2'>$langBgBorderCheckboxes:</label>
                        <input name='BgBorderCheckboxes' type='text' class='form-control colorpicker' id='BgBorderCheckboxes' value='$theme_options_styles[BgBorderCheckboxes]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ClCheckboxes' class='mb-2 me-2'>$langClCheckboxes:</label>
                        <input name='ClCheckboxes' type='text' class='form-control colorpicker' id='ClCheckboxes' value='$theme_options_styles[ClCheckboxes]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BgActiveCheckboxes' class='mb-2 me-2'>$langBgActiveCheckboxes:</label>
                        <input name='BgActiveCheckboxes' type='text' class='form-control colorpicker' id='BgActiveCheckboxes' value='$theme_options_styles[BgActiveCheckboxes]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ClActiveCheckboxes' class='mb-2 me-2'>$langClActiveCheckboxes:</label>
                        <input name='ClActiveCheckboxes' type='text' class='form-control colorpicker' id='ClActiveCheckboxes' value='$theme_options_styles[ClActiveCheckboxes]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ClIconCheckboxes' class='mb-2 me-2'>$langClIconCheckboxes:</label>
                        <input name='ClIconCheckboxes' type='text' class='form-control colorpicker' id='ClIconCheckboxes' value='$theme_options_styles[ClIconCheckboxes]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ClInactiveCheckboxes' class='mb-2 me-2'>$langClInactiveCheckboxes:</label>
                        <input name='ClInactiveCheckboxes' type='text' class='form-control colorpicker' id='ClInactiveCheckboxes' value='$theme_options_styles[ClInactiveCheckboxes]'>
                    </div>
                        
                </div>
            </div>
    ";

    return $html;
}

// input
function build_inputs() {
    global $langInputText, $langBgInput, $theme_options_styles, $langclBorderInput, $langclInputText;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navInputText'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langInputText</h2>
                    <div class='form-group mt-4'>
                        <label for='BgInput' class='mb-2 me-2'>$langBgInput:</label>
                        <input name='BgInput' type='text' class='form-control colorpicker' id='BgInput' value='$theme_options_styles[BgInput]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBorderInput' class='mb-2 me-2'>$langclBorderInput:</label>
                        <input name='clBorderInput' type='text' class='form-control colorpicker' id='clBorderInput' value='$theme_options_styles[clBorderInput]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clInputText' class='mb-2 me-2'>$langclInputText:</label>
                        <input name='clInputText' type='text' class='form-control colorpicker' id='clInputText' value='$theme_options_styles[clInputText]'>
                    </div>
                    
                </div>
            </div>
    ";

    return $html;
}

// text editor
function build_text_editor() {
    global $langInputTextEditor, $langBgTextEditor, $theme_options_styles,
           $langBgBorderTextEditor, $langClTextEditor;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navTextEditor'>
                <div class='form-wrapper form-edit rounded'>
                        
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langInputTextEditor</h2>
                    <div class='form-group mt-4'>
                        <label for='BgTextEditor' class='mb-2 me-2'>$langBgTextEditor:</label>
                        <input name='BgTextEditor' type='text' class='form-control colorpicker' id='BgTextEditor' value='$theme_options_styles[BgTextEditor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BgBorderTextEditor' class='mb-2 me-2'>$langBgBorderTextEditor:</label>
                        <input name='BgBorderTextEditor' type='text' class='form-control colorpicker' id='BgBorderTextEditor' value='$theme_options_styles[BgBorderTextEditor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='ClTextEditor' class='mb-2 me-2'>$langClTextEditor:</label>
                        <input name='ClTextEditor' type='text' class='form-control colorpicker' id='ClTextEditor' value='$theme_options_styles[ClTextEditor]'>
                    </div>
                    
                </div>
            </div>
    ";

    return $html;
}

// select
function build_select() {
    global $langSettingSelect, $langBgSelect, $theme_options_styles,
           $langclBorderSelect, $langclOptionSelect, $langbgHoveredSelectOption,
           $langclHoveredSelectOption, $langbgOptionSelected, $langclOptionSelected;
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navSelect'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langSettingSelect</h2>
                    <div class='form-group mt-4'>
                        <label for='BgSelect' class='mb-2 me-2'>$langBgSelect:</label>
                        <input name='BgSelect' type='text' class='form-control colorpicker' id='BgSelect' value='$theme_options_styles[BgSelect]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBorderSelect' class='mb-2 me-2'>$langclBorderSelect:</label>
                        <input name='clBorderSelect' type='text' class='form-control colorpicker' id='clBorderSelect' value='$theme_options_styles[clBorderSelect]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clOptionSelect' class='mb-2 me-2'>$langclOptionSelect:</label>
                        <input name='clOptionSelect' type='text' class='form-control colorpicker' id='clOptionSelect' value='$theme_options_styles[clOptionSelect]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgHoveredSelectOption' class='mb-2 me-2'>$langbgHoveredSelectOption:</label>
                        <input name='bgHoveredSelectOption' type='text' class='form-control colorpicker' id='bgHoveredSelectOption' value='$theme_options_styles[bgHoveredSelectOption]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clHoveredSelectOption' class='mb-2 me-2'>$langclHoveredSelectOption:</label>
                        <input name='clHoveredSelectOption' type='text' class='form-control colorpicker' id='clHoveredSelectOption' value='$theme_options_styles[clHoveredSelectOption]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgOptionSelected' class='mb-2 me-2'>$langbgOptionSelected:</label>
                        <input name='bgOptionSelected' type='text' class='form-control colorpicker' id='bgOptionSelected' value='$theme_options_styles[bgOptionSelected]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clOptionSelected' class='mb-2 me-2'>$langclOptionSelected:</label>
                        <input name='clOptionSelected' type='text' class='form-control colorpicker' id='clOptionSelected' value='$theme_options_styles[clOptionSelected]'>
                    </div>
                    
                </div>
            </div>
    ";

    return $html;
}

//modal
function build_modal() {
    global $langSettingModals, $langBgModal, $theme_options_styles, $langclBorderModal,
           $langclTextModal, $langclDeleteIconModal, $langclDeleteIconModal, $langclXmarkModal;
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navModal'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langSettingModals</h2>
                    <div class='form-group mt-4'>
                        <label for='BgModal' class='mb-2 me-2'>$langBgModal:</label>
                        <input name='BgModal' type='text' class='form-control colorpicker' id='BgModal' value='$theme_options_styles[BgModal]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBorderModal' class='mb-2 me-2'>$langclBorderModal:</label>
                        <input name='clBorderModal' type='text' class='form-control colorpicker' id='clBorderModal' value='$theme_options_styles[clBorderModal]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clTextModal' class='mb-2 me-2'>$langclTextModal:</label>
                        <input name='clTextModal' type='text' class='form-control colorpicker' id='clTextModal' value='$theme_options_styles[clTextModal]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clDeleteIconModal' class='mb-2 me-2'>$langclDeleteIconModal:</label>
                        <input name='clDeleteIconModal' type='text' class='form-control colorpicker' id='clDeleteIconModal' value='$theme_options_styles[clDeleteIconModal]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clXmarkModal' class='mb-2 me-2'>$langclXmarkModal:</label>
                        <input name='clXmarkModal' type='text' class='form-control colorpicker' id='clXmarkModal' value='$theme_options_styles[clXmarkModal]'>
                    </div>
                    
                </div>
            </div>
    ";

    return $html;
}

// tables
function build_tables() {
    global $langTables, $langBgTables, $theme_options_styles, $langBgBorderBottomHeadTables,
           $langBgBorderBottomRowTables, $langBoxShadowRowTables;
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navTables'>
                <div class='form-wrapper form-edit rounded'>
                   
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langTables</h2>
                    <div class='form-group mt-4'>
                        <label for='BgTables' class='mb-2 me-2'>$langBgTables:</label>
                        <input name='BgTables' type='text' class='form-control colorpicker' id='BgTables' value='$theme_options_styles[BgTables]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BgBorderBottomHeadTables' class='mb-2 me-2'>$langBgBorderBottomHeadTables:</label>
                        <input name='BgBorderBottomHeadTables' type='text' class='form-control colorpicker' id='BgBorderBottomHeadTables' value='$theme_options_styles[BgBorderBottomHeadTables]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BgBorderBottomRowTables' class='mb-2 me-2'>$langBgBorderBottomRowTables:</label>
                        <input name='BgBorderBottomRowTables' type='text' class='form-control colorpicker' id='BgBorderBottomRowTables' value='$theme_options_styles[BgBorderBottomRowTables]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BoxShadowRowTables' class='mb-2 me-2'>$langBoxShadowRowTables:</label>
                        <input name='BoxShadowRowTables' type='text' class='form-control colorpicker' id='BoxShadowRowTables' value='$theme_options_styles[BoxShadowRowTables]'>
                    </div>
                 
                </div>
            </div>
    ";

    return $html;
}

// tabs
function build_tabs() {
    global $langTabs, $langTextColor, $theme_options_styles, 
           $langHoverTextColor, $langActiveTextColor;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navTabs'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langTabs</h2>
                    <div class='form-group mt-4'>
                        <label for='clTabs' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='clTabs' type='text' class='form-control colorpicker' id='clTabs' value='$theme_options_styles[clTabs]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clHoveredTabs' class='mb-2 me-2'>$langHoverTextColor:</label>
                        <input name='clHoveredTabs' type='text' class='form-control colorpicker' id='clHoveredTabs' value='$theme_options_styles[clHoveredTabs]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clActiveTabs' class='mb-2 me-2'>$langActiveTextColor:</label>
                        <input name='clActiveTabs' type='text' class='form-control colorpicker' id='clActiveTabs' value='$theme_options_styles[clActiveTabs]'>
                    </div>

                </div>
            </div>
    ";

    return $html;
}

// accordio
function build_accordion() {
    global $langAccordions, $langTextColor, $theme_options_styles,
           $langAccordionsBorderBottom, $langHoverTextColor, $langActiveTextColor;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navAccordions'>
                <div class='form-wrapper form-edit rounded'>
                        
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langAccordions</h2>
                    <div class='form-group mt-4'>
                        <label for='clAccordions' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='clAccordions' type='text' class='form-control colorpicker' id='clAccordions' value='$theme_options_styles[clAccordions]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBorderBottomAccordions' class='mb-2 me-2'>$langAccordionsBorderBottom:</label>
                        <input name='clBorderBottomAccordions' type='text' class='form-control colorpicker' id='clBorderBottomAccordions' value='$theme_options_styles[clBorderBottomAccordions]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clHoveredAccordions' class='mb-2 me-2'>$langHoverTextColor:</label>
                        <input name='clHoveredAccordions' type='text' class='form-control colorpicker' id='clHoveredAccordions' value='$theme_options_styles[clHoveredAccordions]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clActiveAccordions' class='mb-2 me-2'>$langActiveTextColor:</label>
                        <input name='clActiveAccordions' type='text' class='form-control colorpicker' id='clActiveAccordions' value='$theme_options_styles[clActiveAccordions]'>
                    </div>
                    
                </div>
            </div>
    ";

    return $html;
}

// list group
function build_list_group() {
    global $langLists, $langBgColorList, $theme_options_styles, $langclBorderBottomLists,
           $langclLists, $langclHoveredLists, $langAddPaddingListGroup, $langSettingSelect;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navLists'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langLists</h2>
                    <div class='form-group mt-4'>
                        <label for='bgLists' class='mb-2 me-2'>$langBgColorList:</label>
                        <input name='bgLists' type='text' class='form-control colorpicker' id='bgLists' value='$theme_options_styles[bgLists]'>
                    </div>
                    
                    <div class='form-group mt-4'>
                        <label for='clBorderBottomLists' class='mb-2 me-2'>$langclBorderBottomLists:</label>
                        <input name='clBorderBottomLists' type='text' class='form-control colorpicker' id='clBorderBottomLists' value='$theme_options_styles[clBorderBottomLists]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='clLists' class='mb-2 me-2'>$langclLists:</label>
                        <input name='clLists' type='text' class='form-control colorpicker' id='clLists' value='$theme_options_styles[clLists]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='clHoveredLists' class='mb-2 me-2'>$langclHoveredLists:</label>
                        <input name='clHoveredLists' type='text' class='form-control colorpicker' id='clHoveredLists' value='$theme_options_styles[clHoveredLists]'>
                    </div>

                    <div class='form-group mt-2'>
                        <div class='col-sm-12'>
                            <div class='checkbox'>
                                <label class='label-container' aria-label='$langSettingSelect'>
                                    <input type='checkbox' name='AddPaddingListGroup' value='1' ".((isset($theme_options_styles['AddPaddingListGroup']))? 'checked' : '').">
                                    <span class='checkmark'></span>
                                    $langAddPaddingListGroup
                                </label>
                            </div>
                        </div>
                    </div>    
                    
                </div>
            </div>
    ";

    return $html;
    
}

// contextual menu
function build_contextual_menu() {
    global $langContextualMenuInfo, $langBgColorMenuCont, $theme_options_styles, 
           $langbgBorderContextualMenu, $langBgColorListMenu, $langbgHoveredListMenu,
           $langclBorderBottomListMenu, $langclListMenu, $langclHoveredclHoveredListMenu,
           $langclListMenuUsername, $langclListMenuLogout, $langclListMenuDeletion;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navContextualMenu'>
                <div class='form-wrapper form-edit rounded'>
                        
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langContextualMenuInfo</h2>
                    <div class='form-group mt-4'>
                        <label for='bgContextualMenu' class='mb-2 me-2'>$langBgColorMenuCont:</label>
                        <input name='bgContextualMenu' type='text' class='form-control colorpicker' id='bgContextualMenu' value='$theme_options_styles[bgContextualMenu]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgBorderContextualMenu' class='mb-2 me-2'>$langbgBorderContextualMenu:</label>
                        <input name='bgBorderContextualMenu' type='text' class='form-control colorpicker' id='bgBorderContextualMenu' value='$theme_options_styles[bgBorderContextualMenu]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='bgColorListMenu' class='mb-2 me-2'>$langBgColorListMenu:</label>
                        <input name='bgColorListMenu' type='text' class='form-control colorpicker' id='bgColorListMenu' value='$theme_options_styles[bgColorListMenu]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='bgHoveredListMenu' class='mb-2 me-2'>$langbgHoveredListMenu:</label>
                        <input name='bgHoveredListMenu' type='text' class='form-control colorpicker' id='bgHoveredListMenu' value='$theme_options_styles[bgHoveredListMenu]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='clBorderBottomListMenu' class='mb-2 me-2'>$langclBorderBottomListMenu:</label>
                        <input name='clBorderBottomListMenu' type='text' class='form-control colorpicker' id='clBorderBottomListMenu' value='$theme_options_styles[clBorderBottomListMenu]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='clListMenu' class='mb-2 me-2'>$langclListMenu:</label>
                        <input name='clListMenu' type='text' class='form-control colorpicker' id='clListMenu' value='$theme_options_styles[clListMenu]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='clHoveredListMenu' class='mb-2 me-2'>$langclHoveredclHoveredListMenu:</label>
                        <input name='clHoveredListMenu' type='text' class='form-control colorpicker' id='clHoveredListMenu' value='$theme_options_styles[clHoveredListMenu]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='clListMenuUsername' class='mb-2 me-2'>$langclListMenuUsername:</label>
                        <input name='clListMenuUsername' type='text' class='form-control colorpicker' id='clListMenuUsername' value='$theme_options_styles[clListMenuUsername]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='clListMenuLogout' class='mb-2 me-2'>$langclListMenuLogout:</label>
                        <input name='clListMenuLogout' type='text' class='form-control colorpicker' id='clListMenuLogout' value='$theme_options_styles[clListMenuLogout]'>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='clListMenuDeletion' class='mb-2 me-2'>$langclListMenuDeletion:</label>
                        <input name='clListMenuDeletion' type='text' class='form-control colorpicker' id='clListMenuDeletion' value='$theme_options_styles[clListMenuDeletion]'>
                    </div>
                    
                </div>
            </div>
    ";

    return $html;
}

// menu popover
function build_menu_popover() {
    global $langMenuPopover, $langBgMenuPopover, $theme_options_styles,
           $langBgBorderMenuPopover, $langBgMenuPopoverOption, $langclMenuPopoverOption,
           $langclBorderBottomMenuPopoverOption, $langBgHoveredMenuPopoverOption, 
           $langclHoveredMenuPopoverOption, $langclDeleteMenuPopoverOption;
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navMenuPopover'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langMenuPopover</h2>
                            <div class='form-group mt-4'>
                                <label for='BgMenuPopover' class='mb-2 me-2'>$langBgMenuPopover:</label>
                                <input name='BgMenuPopover' type='text' class='form-control colorpicker' id='BgMenuPopover' value='$theme_options_styles[BgMenuPopover]'>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='BgBorderMenuPopover' class='mb-2 me-2'>$langBgBorderMenuPopover:</label>
                                <input name='BgBorderMenuPopover' type='text' class='form-control colorpicker' id='BgBorderMenuPopover' value='$theme_options_styles[BgBorderMenuPopover]'>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='BgMenuPopoverOption' class='mb-2 me-2'>$langBgMenuPopoverOption:</label>
                                <input name='BgMenuPopoverOption' type='text' class='form-control colorpicker' id='BgMenuPopoverOption' value='$theme_options_styles[BgMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='clMenuPopoverOption' class='mb-2 me-2'>$langclMenuPopoverOption:</label>
                                <input name='clMenuPopoverOption' type='text' class='form-control colorpicker' id='clMenuPopoverOption' value='$theme_options_styles[clMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='clBorderBottomMenuPopoverOption' class='mb-2 me-2'>$langclBorderBottomMenuPopoverOption:</label>
                                <input name='clBorderBottomMenuPopoverOption' type='text' class='form-control colorpicker' id='clBorderBottomMenuPopoverOption' value='$theme_options_styles[clBorderBottomMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4 r'>
                                <label for='BgHoveredMenuPopoverOption' class='mb-2 me-2'>$langBgHoveredMenuPopoverOption:</label>
                                <input name='BgHoveredMenuPopoverOption' type='text' class='form-control colorpicker' id='BgHoveredMenuPopoverOption' value='$theme_options_styles[BgHoveredMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='clHoveredMenuPopoverOption' class=' mb-2 me-2'>$langclHoveredMenuPopoverOption:</label>
                                <input name='clHoveredMenuPopoverOption' type='text' class='form-control colorpicker' id='clHoveredMenuPopoverOption' value='$theme_options_styles[clHoveredMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='clDeleteMenuPopoverOption' class='mb-2 me-2'>$langclDeleteMenuPopoverOption:</label>
                                <input name='clDeleteMenuPopoverOption' type='text' class='form-control colorpicker' id='clDeleteMenuPopoverOption' value='$theme_options_styles[clDeleteMenuPopoverOption]'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// agenda
function build_agenda() {
    global $langAgendaSettings, $langBgColorAgenda, $theme_options_styles, $langBgBorderColorAgenda,
           $langBgBorderColorAgendaEvent, $langBgColorHeaderAgenda, $langclColorHeaderAgenda, $langclColorBodyAgenda,
           $langbgColorHoveredBodyAgenda, $langclColorHoveredBodyAgenda, $langbgColorActiveDateTime, $langbgColorDeactiveDateTime,
           $langtextColorActiveDateTime, $langbgPanelEvents;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsAgenda'>
                <div class='form-wrapper form-edit rounded'>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langAgendaSettings</h2>
                    <div class='form-group mt-4'>
                        <label for='bgAgenda' class='mb-2 me-2'>$langBgColorAgenda:</label>
                        <input name='bgAgenda' type='text' class='form-control colorpicker' id='bgAgenda' value='$theme_options_styles[bgAgenda]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BgBorderColorAgenda' class='mb-2 me-2'>$langBgBorderColorAgenda:</label>
                        <input name='BgBorderColorAgenda' type='text' class='form-control colorpicker' id='BgBorderColorAgenda' value='$theme_options_styles[BgBorderColorAgenda]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BgBorderColorAgendaEvent' class='mb-2 me-2'>$langBgBorderColorAgendaEvent:</label>
                        <input name='BgBorderColorAgendaEvent' type='text' class='form-control colorpicker' id='BgBorderColorAgendaEvent' value='$theme_options_styles[BgBorderColorAgendaEvent]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='BgColorHeaderAgenda' class='mb-2 me-2'>$langBgColorHeaderAgenda:</label>
                        <input name='BgColorHeaderAgenda' type='text' class='form-control colorpicker' id='BgColorHeaderAgenda' value='$theme_options_styles[BgColorHeaderAgenda]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clColorHeaderAgenda' class='mb-2 me-2'>$langclColorHeaderAgenda:</label>
                        <input name='clColorHeaderAgenda' type='text' class='form-control colorpicker' id='clColorHeaderAgenda' value='$theme_options_styles[clColorHeaderAgenda]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clColorBodyAgenda' class='mb-2 me-2'>$langclColorBodyAgenda:</label>
                        <input name='clColorBodyAgenda' type='text' class='form-control colorpicker' id='clColorBodyAgenda' value='$theme_options_styles[clColorBodyAgenda]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgColorHoveredBodyAgenda' class='mb-2 me-2'>$langbgColorHoveredBodyAgenda:</label>
                        <input name='bgColorHoveredBodyAgenda' type='text' class='form-control colorpicker' id='bgColorHoveredBodyAgenda' value='$theme_options_styles[bgColorHoveredBodyAgenda]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clColorHoveredBodyAgenda' class='mb-2 me-2'>$langclColorHoveredBodyAgenda:</label>
                        <input name='clColorHoveredBodyAgenda' type='text' class='form-control colorpicker' id='clColorHoveredBodyAgenda' value='$theme_options_styles[clColorHoveredBodyAgenda]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgColorActiveDateTime' class='mb-2 me-2'>$langbgColorActiveDateTime:</label>
                        <input name='bgColorActiveDateTime' type='text' class='form-control colorpicker' id='bgColorActiveDateTime' value='$theme_options_styles[bgColorActiveDateTime]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgColorDeactiveDateTime' class='mb-2 me-2'>$langbgColorDeactiveDateTime:</label>
                        <input name='bgColorDeactiveDateTime' type='text' class='form-control colorpicker' id='bgColorDeactiveDateTime' value='$theme_options_styles[bgColorDeactiveDateTime]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='TextColorActiveDateTime' class='mb-2 me-2'>$langtextColorActiveDateTime:</label>
                        <input name='TextColorActiveDateTime' type='text' class='form-control colorpicker' id='TextColorActiveDateTime' value='$theme_options_styles[TextColorActiveDateTime]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='bgPanelEvents' class='mb-2 me-2'>$langbgPanelEvents:</label>
                        <input name='bgPanelEvents' type='text' class='form-control colorpicker' id='bgPanelEvents' value='$theme_options_styles[bgPanelEvents]'>
                    </div>
                        
                </div>
            </div>
    ";

    return $html;
}

//scrollbar
function build_scrollbar() {
    global $langSettingsScrollBar, $BgScrollBar, $langBgColorScrollBar,
           $langBgHoveredColorScrollBar, $theme_options_styles;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsScrollBar'>
                <div class='form-wrapper form-edit rounded'>
                  
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langSettingsScrollBar</h2>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgScrollBar' class='mb-2 me-2'>$BgScrollBar:</label>
                        <input name='BgScrollBar' type='text' class='form-control colorpicker' id='BgScrollBar' value='$theme_options_styles[BgScrollBar]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgHoveredColorScrollBar' class='mb-2 me-2'>$langBgHoveredColorScrollBar:</label>
                        <input name='BgHoveredColorScrollBar' type='text' class='form-control colorpicker' id='BgHoveredColorScrollBar' value='$theme_options_styles[BgHoveredColorScrollBar]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgColorScrollBar' class='mb-2 me-2'>$langBgColorScrollBar:</label>
                        <input name='BgColorScrollBar' type='text' class='form-control colorpicker' id='BgColorScrollBar' value='$theme_options_styles[BgColorScrollBar]'>
                    </div>
                    
                        
                </div>
            </div>
    ";

    return $html;
}

// badges
function build_badges() {
    global $langBgColor, $theme_options_styles, $langTextColor;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsBadge'>
                <div class='form-wrapper form-edit rounded'>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>Badge Success</h2>
                    <div class='form-group mt-4'>
                        <label for='BgBadgeSuccess' class=' mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgeSuccess' type='text' class='form-control colorpicker' id='BgBadgeSuccess' value='$theme_options_styles[BgBadgeSuccess]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBadgeSuccess' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgeSuccess' type='text' class='form-control colorpicker' id='clBadgeSuccess' value='$theme_options_styles[clBadgeSuccess]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>Badge Warning</h2>
                    <div class='form-group mt-4'>
                        <label for='BgBadgeWarning' class='mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgeWarning' type='text' class='form-control colorpicker' id='BgBadgeWarning' value='$theme_options_styles[BgBadgeWarning]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBadgeWarning' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgeWarning' type='text' class='form-control colorpicker' id='clBadgeWarning' value='$theme_options_styles[clBadgeWarning]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>Badge Neutral</h2>
                    <div class='form-group mt-4'>
                        <label for='BgBadgeNeutral' class='mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgeNeutral' type='text' class='form-control colorpicker' id='BgBadgeNeutral' value='$theme_options_styles[BgBadgeNeutral]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBadgeNeutral' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgeNeutral' type='text' class='form-control colorpicker' id='clBadgeNeutral' value='$theme_options_styles[clBadgeNeutral]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>Badge Primary</h2>
                    <div class='form-group mt-4'>
                        <label for='BgBadgePrimary' class='mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgePrimary' type='text' class='form-control colorpicker' id='BgBadgePrimary' value='$theme_options_styles[BgBadgePrimary]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBadgePrimary' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgePrimary' type='text' class='form-control colorpicker' id='clBadgePrimary' value='$theme_options_styles[clBadgePrimary]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>Badge Danger</h2>
                    <div class='form-group mt-4'>
                        <label for='BgBadgeAccent' class=' mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgeAccent' type='text' class='form-control colorpicker' id='BgBadgeAccent' value='$theme_options_styles[BgBadgeAccent]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='clBadgeAccent' class='mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgeAccent' type='text' class='form-control colorpicker' id='clBadgeAccent' value='$theme_options_styles[clBadgeAccent]'>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// progress bar
function build_progress_bar() {
    global $langSettingsProgressBar, $langInfoProgressBar, $langBackProgressBar, 
           $theme_options_styles, $langBgProgressBar, $langBgColorProgressBarAndText;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsProgressBar'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langSettingsProgressBar</h2>
                            <p>$langInfoProgressBar</p>
                            <div class='form-group mt-4'>
                                <label for='BackProgressBar' class='mb-2 me-2'>$langBackProgressBar:</label>
                                <input name='BackProgressBar' type='text' class='form-control colorpicker' id='BackProgressBar' value='$theme_options_styles[BackProgressBar]'>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='BgProgressBar' class=' mb-2 me-2'>$langBgProgressBar:</label>
                                <input name='BgProgressBar' type='text' class='form-control colorpicker' id='BgProgressBar' value='$theme_options_styles[BgProgressBar]'>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='BgColorProgressBarAndText' class='mb-2 me-2'>$langBgColorProgressBarAndText:</label>
                                <input name='BgColorProgressBarAndText' type='text' class='form-control colorpicker' id='BgColorProgressBarAndText' value='$theme_options_styles[BgColorProgressBarAndText]'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// tooltips
function build_tooltips() {
    global $langSettingsTooltip, $langbgColorTooltip, $theme_options_styles, $langTextColorTooltip;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsTooltip'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langSettingsTooltip</h2>
                    <div class='form-group mt-4'>
                        <label for='bgColorTooltip' class='mb-2 me-2'>$langbgColorTooltip:</label>
                        <input name='bgColorTooltip' type='text' class='form-control colorpicker' id='bgColorTooltip' value='$theme_options_styles[bgColorTooltip]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='TextColorTooltip' class='mb-2 me-2'>$langTextColorTooltip:</label>
                        <input name='TextColorTooltip' type='text' class='form-control colorpicker' id='TextColorTooltip' value='$theme_options_styles[TextColorTooltip]'>
                    </div>

                </div>
            </div>
    ";

    return $html;
}

// alerts
function build_alerts() {
    global $langSettingsAlertInfo, $langBgColorAlertInfo, $theme_options_styles, $langBorderTextColorAlertInfo, 
           $langTextColorAlertInfo, $langLinkColorAlertInfo, $langLinkHoverColorAlertInfo, $langSettingsAlertWarning,
           $langBgColorAlertWarning, $langBorderTextColorAlertWarning, $langTextColorAlertWarning, $langLinkColorAlertWarning,
           $langLinkHoverColorAlertWarning, $langSettingsAlertSuccess, $langBgColorAlertSuccess, $langBorderTextColorAlertSuccess,
           $langTextColorAlertSuccess, $langLinkColorAlertSuccess, $langLinkHoverColorAlertSuccess, $langSettingsAlertDanger,
           $langBgColorAlertDanger, $langBorderTextColorAlertDanger, $langTextColorAlertDange, $langLinkColorAlertDanger, 
           $langLinkHoverColorAlertDanger, $langTextColorAlertDanger;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsAlerts'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langSettingsAlertInfo</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAlertInfo' class='mb-2 me-2'>$langBgColorAlertInfo:</label>
                                <input name='bgAlertInfo' type='text' class='form-control colorpicker' id='bgAlertInfo' value='$theme_options_styles[bgAlertInfo]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='borderClAlertInfo' class=' mb-2 me-2'>$langBorderTextColorAlertInfo:</label>
                                <input name='borderClAlertInfo' type='text' class='form-control colorpicker' id='borderClAlertInfo' value='$theme_options_styles[borderClAlertInfo]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAlertInfo' class=' mb-2 me-2'>$langTextColorAlertInfo:</label>
                                <input name='clAlertInfo' type='text' class='form-control colorpicker' id='clAlertInfo' value='$theme_options_styles[clAlertInfo]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkAlertInfo' class=' mb-2 me-2'>$langLinkColorAlertInfo:</label>
                                <input name='clLinkAlertInfo' type='text' class='form-control colorpicker' id='clLinkAlertInfo' value='$theme_options_styles[clLinkAlertInfo]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkHoveredAlertInfo' class=' mb-2 me-2'>$langLinkHoverColorAlertInfo:</label>
                                <input name='clLinkHoveredAlertInfo' type='text' class='form-control colorpicker' id='clLinkHoveredAlertInfo' value='$theme_options_styles[clLinkHoveredAlertInfo]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-3 text-heading-h3'>$langSettingsAlertWarning</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAlertWarning' class=' mb-2 me-2'>$langBgColorAlertWarning:</label>
                                <input name='bgAlertWarning' type='text' class='form-control colorpicker' id='bgAlertWarning' value='$theme_options_styles[bgAlertWarning]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='borderClAlertWarning' class=' mb-2 me-2'>$langBorderTextColorAlertWarning:</label>
                                <input name='borderClAlertWarning' type='text' class='form-control colorpicker' id='borderClAlertWarning' value='$theme_options_styles[borderClAlertWarning]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAlertWarning' class=' mb-2 me-2'>$langTextColorAlertWarning:</label>
                                <input name='clAlertWarning' type='text' class='form-control colorpicker' id='clAlertWarning' value='$theme_options_styles[clAlertWarning]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkAlertWarning' class=' mb-2 me-2'>$langLinkColorAlertWarning:</label>
                                <input name='clLinkAlertWarning' type='text' class='form-control colorpicker' id='clLinkAlertWarning' value='$theme_options_styles[clLinkAlertWarning]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkHoveredAlertWarning' class=' mb-2 me-2'>$langLinkHoverColorAlertWarning:</label>
                                <input name='clLinkHoveredAlertWarning' type='text' class='form-control colorpicker' id='clLinkHoveredAlertWarning' value='$theme_options_styles[clLinkHoveredAlertWarning]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-3 text-heading-h3'>$langSettingsAlertSuccess</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAlertSuccess' class=' mb-2 me-2'>$langBgColorAlertSuccess:</label>
                                <input name='bgAlertSuccess' type='text' class='form-control colorpicker' id='bgAlertSuccess' value='$theme_options_styles[bgAlertSuccess]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='borderClAlertSuccess' class=' mb-2 me-2'>$langBorderTextColorAlertSuccess:</label>
                                <input name='borderClAlertSuccess' type='text' class='form-control colorpicker' id='borderClAlertSuccess' value='$theme_options_styles[borderClAlertSuccess]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAlertSuccess' class=' mb-2 me-2'>$langTextColorAlertSuccess:</label>
                                <input name='clAlertSuccess' type='text' class='form-control colorpicker' id='clAlertSuccess' value='$theme_options_styles[clAlertSuccess]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkAlertSuccess' class=' mb-2 me-2'>$langLinkColorAlertSuccess:</label>
                                <input name='clLinkAlertSuccess' type='text' class='form-control colorpicker' id='clLinkAlertSuccess' value='$theme_options_styles[clLinkAlertSuccess]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkHoveredAlertSuccess' class=' mb-2 me-2'>$langLinkHoverColorAlertSuccess:</label>
                                <input name='clLinkHoveredAlertSuccess' type='text' class='form-control colorpicker' id='clLinkHoveredAlertSuccess' value='$theme_options_styles[clLinkHoveredAlertSuccess]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-3 text-heading-h3'>$langSettingsAlertDanger</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAlertDanger' class=' mb-2 me-2'>$langBgColorAlertDanger:</label>
                                <input name='bgAlertDanger' type='text' class='form-control colorpicker' id='bgAlertDanger' value='$theme_options_styles[bgAlertDanger]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='borderClAlertDanger' class=' mb-2 me-2'>$langBorderTextColorAlertDanger:</label>
                                <input name='borderClAlertDanger' type='text' class='form-control colorpicker' id='borderClAlertDanger' value='$theme_options_styles[borderClAlertDanger]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAlertDanger' class=' mb-2 me-2'>$langTextColorAlertDanger:</label>
                                <input name='clAlertDanger' type='text' class='form-control colorpicker' id='clAlertDanger' value='$theme_options_styles[clAlertDanger]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkAlertDanger' class=' mb-2 me-2'>$langLinkColorAlertDanger:</label>
                                <input name='clLinkAlertDanger' type='text' class='form-control colorpicker' id='clLinkAlertDanger' value='$theme_options_styles[clLinkAlertDanger]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkHoveredAlertDanger' class=' mb-2 me-2'>$langLinkHoverColorAlertDanger:</label>
                                <input name='clLinkHoveredAlertDanger' type='text' class='form-control colorpicker' id='clLinkHoveredAlertDanger' value='$theme_options_styles[clLinkHoveredAlertDanger]'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// homepage
function build_homepage() {
    global $langForm, $langBasicOptions, $langLoginBgGradient, $theme_options_styles, $langBgColor,
           $login_image_field, $langSettingSelect, $langJumbotronWithVideo, $langMaxHeight, $langMaxHeightMaxScreenJumbotron,
           $langHelpJumbotronInfoText, $langMaxHeightHalfMaxScreenJumbotron, $langHelpJumbotronInfoText, $langTextColor,
           $langMaxWidthTextJumbotron, $langBgColor, $langText, $langBgColor, $langLogoSmall, $langPositionJumbotronText,
           $langTopPositionJumbotronText, $langCenterPositionJumbotronText, $langBottomPositionJumbotronText, $langIntroTextCenterPos,
           $login_image_fieldL_2, $login_image_fieldL, $langFormLoginPlacementCenter, $langFormLoginPlacementLeft, $langBgColorCardLogin, 
           $langBgBorderColorCardLogin, $langTextColorCardLogin, $langLinkColorCardLogin, $langLinkHoverColorCardLogin, $langLoginBanner,
           $langDeactivate, $langBgColorLinkBanner, $langAnnouncements, $langbgCardAnnouncementDate, $langTextColorCardAnnouncementDate,
           $langBgColorListItem, $langBgBorderColorListItem, $langclLists, $langclHoveredLists, $langAddPaddingListGroup, $langActivate,
           $langVisitsStats, $langPopularCourse, $langHomepageTexts, $langTextIntroColor, $langBgColorTextIntro, $lang_login_form;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettingsLoginHomepage'>
                <div class='form-wrapper form-edit rounded'>
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                            <div>
                                <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>Jumbotron</h2>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12'>
                                    $login_image_field
                                    </div>
                                    <div class='col-sm-12 mt-4'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label='$langSettingSelect'>
                                                <input type='checkbox' name='JumbotronWithVideo' value='1' ".((isset($theme_options_styles['JumbotronWithVideo']))? 'checked' : '').">
                                                <span class='checkmark'></span>
                                                $langJumbotronWithVideo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                    <label for='loginJumbotronBgColor' class='mb-2 me-2'>$langLoginBgGradient</label>
                                    <input name='loginJumbotronBgColor' type='text' class='form-control colorpicker' id='loginJumbotronBgColor' value='$theme_options_styles[loginJumbotronBgColor]'>
                                    <i class='fa fa-arrow-right ms-3 me-3'></i>
                                    <input aria-label='$langBgColor' name='loginJumbotronRadialBgColor' type='text' class='form-control colorpicker' id='loginJumbotronRadialBgColor' value='$theme_options_styles[loginJumbotronRadialBgColor]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='maxHeightJumbotron' class='col-sm-6 mb-2'>$langMaxHeight</label>
                                    <div class='col-sm-12'>
                                        <input id='maxHeightJumbotron' name='maxHeightJumbotron' data-slider-id='ex2Slider' type='text' data-slider-min='270' data-slider-max='1080' data-slider-step='10' data-slider-value='$theme_options_styles[maxHeightJumbotron]'>
                                        <span style='margin-left:10px;' id='pixelCounterHeightJumbotron'></span>
                                    </div>
                                    <div class='col-sm-12 mt-4'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label='$langSettingSelect'>
                                                <input type='checkbox' name='MaxHeightMaxScreenJumbotron' value='1' ".((isset($theme_options_styles['MaxHeightMaxScreenJumbotron']))? 'checked' : '').">
                                                <span class='checkmark'></span>
                                                $langMaxHeightMaxScreenJumbotron
                                            </label>
                                        </div>
                                        <small>$langHelpJumbotronInfoText</small>
                                    </div>
                                    <div class='col-sm-12 mt-4'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label='$langSettingSelect'>
                                                <input type='checkbox' name='MaxHeightHalfMaxScreenJumbotron' value='1' ".((isset($theme_options_styles['MaxHeightHalfMaxScreenJumbotron']))? 'checked' : '').">
                                                <span class='checkmark'></span>
                                                $langMaxHeightHalfMaxScreenJumbotron
                                            </label>
                                        </div>
                                        <small>$langHelpJumbotronInfoText</small>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='maxWidthTextJumbotron' class='mb-2'>$langMaxWidthTextJumbotron</label>
                                    <div class='col-sm-12'>
                                        <input id='maxWidthTextJumbotron' name='maxWidthTextJumbotron' data-slider-id='ex3Slider' type='text' data-slider-min='260' data-slider-max='1920' data-slider-step='10' data-slider-value='$theme_options_styles[maxWidthTextJumbotron]'>
                                        <span style='margin-left:10px;' id='pixelCounterWidthTextJumbotron'></span>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='loginTextColor' class='mb-2 me-2'>$langTextIntroColor</label>
                                    <input name='loginTextColor' type='text' class='form-control colorpicker' id='loginTextColor' value='$theme_options_styles[loginTextColor]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='loginTextBgColor' class='mb-2 me-2'>$langBgColorTextIntro</label>
                                    <input name='loginTextBgColor' type='text' class='form-control colorpicker' id='loginTextBgColor' value='$theme_options_styles[loginTextBgColor]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='loginTextBgColorSmallScreen' class='mb-2 me-2'>$langBgColorTextIntro $langLogoSmall:</label>
                                    <input name='loginTextBgColorSmallScreen' type='text' class='form-control colorpicker' id='loginTextBgColorSmallScreen' value='$theme_options_styles[loginTextBgColorSmallScreen]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 mb-2'>$langPositionJumbotronText</div>
                                    <div class='radio mb-2'>
                                        <label>
                                            <input type='radio' name='PositionJumbotronText' value='0' ".((isset($theme_options_styles['PositionJumbotronText']) and $theme_options_styles['PositionJumbotronText'] == '0')? 'checked' : '').">
                                            $langTopPositionJumbotronText
                                        </label>
                                    </div>

                                    <div class='radio mb-2'>
                                        <label>
                                            <input type='radio' name='PositionJumbotronText' value='1' ".((isset($theme_options_styles['PositionJumbotronText']) and $theme_options_styles['PositionJumbotronText'] == '1')? 'checked' : '').">
                                            $langCenterPositionJumbotronText
                                        </label>
                                    </div>

                                    <div class='radio'>
                                        <label>
                                            <input type='radio' name='PositionJumbotronText' value='2' ".((isset($theme_options_styles['PositionJumbotronText']) and $theme_options_styles['PositionJumbotronText'] == '2')? 'checked' : '').">
                                            $langBottomPositionJumbotronText
                                        </label>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='introTextCenterPos' value='1' ".((isset($theme_options_styles['introTextCenterPos']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langIntroTextCenterPos
                                        </label>
                                    </div>
                                </div>



                                <hr>



                                <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$lang_login_form</h2>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12'>
                                    $login_image_fieldL_2
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12'>
                                    $login_image_fieldL
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='form-inline col-sm-9 col-sm-offset-3'>
                                        <div class='radio'>
                                            <label>
                                            <input type='radio' name='FormLoginPlacement' value='center-position' " . ((isset($theme_options_styles['FormLoginPlacement']) && $theme_options_styles['FormLoginPlacement'] == 'center-position') ? 'checked' : '') . ">
                                            $langFormLoginPlacementCenter
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                            <input type='radio' name='FormLoginPlacement' value='right-position' " . ((isset($theme_options_styles['FormLoginPlacement']) && $theme_options_styles['FormLoginPlacement'] == 'right-position') ? 'checked' : '') . ">
                                            $langFormLoginPlacementLeft &nbsp;
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='BgColorCardLogin' class='mb-2 me-2'>$langBgColorCardLogin</label>
                                    <input name='BgColorCardLogin' type='text' class='form-control colorpicker' id='BgColorCardLogin' value='$theme_options_styles[BgColorCardLogin]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='BgBorderColorCardLogin' class='mb-2 me-2'>$langBgBorderColorCardLogin</label>
                                    <input name='BgBorderColorCardLogin' type='text' class='form-control colorpicker' id='BgBorderColorCardLogin' value='$theme_options_styles[BgBorderColorCardLogin]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='textColorCardLogin' class='mb-2 me-2'>$langTextColorCardLogin</label>
                                    <input name='textColorCardLogin' type='text' class='form-control colorpicker' id='textColorCardLogin' value='$theme_options_styles[textColorCardLogin]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='linkColorCardLogin' class='mb-2 me-2'>$langLinkColorCardLogin</label>
                                    <input name='linkColorCardLogin' type='text' class='form-control colorpicker' id='linkColorCardLogin' value='$theme_options_styles[linkColorCardLogin]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='linkHoverColorCardLogin' class='mb-2 me-2'>$langLinkHoverColorCardLogin</label>
                                    <input name='linkHoverColorCardLogin' type='text' class='form-control colorpicker' id='linkHoverColorCardLogin' value='$theme_options_styles[linkHoverColorCardLogin]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 mb-2'>$langLoginBanner:</div>
                                    <div class='col-sm-12'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='openeclassBanner' value='1' ".((isset($theme_options_styles['openeclassBanner']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langDeactivate
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12'>
                                        <label for='BgColorLinkBanner' class='mb-2 me-2'>$langBgColorLinkBanner:</label>
                                        <input name='BgColorLinkBanner' type='text' class='form-control colorpicker' id='BgColorLinkBanner' value='$theme_options_styles[BgColorLinkBanner]'>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>


                    <hr>



                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langAnnouncements</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorAnnouncementHomepage' class='mb-2 me-2'>$langBgColor</label>
                                <input name='BgColorAnnouncementHomepage' type='text' class='form-control colorpicker' id='BgColorAnnouncementHomepage' value='$theme_options_styles[BgColorAnnouncementHomepage]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BgColorAnnouncementHomepage_gr' type='text' class='form-control colorpicker' id='BgColorAnnouncementHomepage_gr' value='$theme_options_styles[BgColorAnnouncementHomepage_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorAnnouncementHomepage' class='s mb-2 me-2'>$langTextColor</label>
                                <input name='TextColorAnnouncementHomepage' type='text' class='form-control colorpicker' id='TextColorAnnouncementHomepage' value='$theme_options_styles[TextColorAnnouncementHomepage]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgCardAnnouncementDate' class='mb-2 me-2'>$langbgCardAnnouncementDate:</label>
                                <input name='bgCardAnnouncementDate' type='text' class='form-control colorpicker' id='bgCardAnnouncementDate' value='$theme_options_styles[bgCardAnnouncementDate]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorCardAnnouncementDate' class='mb-2 me-2'>$langTextColorCardAnnouncementDate:</label>
                                <input name='TextColorCardAnnouncementDate' type='text' class='form-control colorpicker' id='TextColorCardAnnouncementDate' value='$theme_options_styles[TextColorCardAnnouncementDate]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorAnnouncementHomepageLink' class=' mb-2 me-2'>$langBgColorListItem:</label>
                                <input name='BgColorAnnouncementHomepageLink' type='text' class='form-control colorpicker' id='BgColorAnnouncementHomepageLink' value='$theme_options_styles[BgColorAnnouncementHomepageLink]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderColorAnnouncementHomepageLink' class=' mb-2 me-2'>$langBgBorderColorListItem:</label>
                                <input name='BgBorderColorAnnouncementHomepageLink' type='text' class='form-control colorpicker' id='BgBorderColorAnnouncementHomepageLink' value='$theme_options_styles[BgBorderColorAnnouncementHomepageLink]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clColorAnnouncementHomepageLinkElement' class=' mb-2 me-2'>$langclLists:</label>
                                <input name='clColorAnnouncementHomepageLinkElement' type='text' class='form-control colorpicker' id='clColorAnnouncementHomepageLinkElement' value='$theme_options_styles[clColorAnnouncementHomepageLinkElement]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredColorAnnouncementHomepageLinkElement' class='mb-2 me-2'>$langclHoveredLists:</label>
                                <input name='clHoveredColorAnnouncementHomepageLinkElement' type='text' class='form-control colorpicker' id='clHoveredColorAnnouncementHomepageLinkElement' value='$theme_options_styles[clHoveredColorAnnouncementHomepageLinkElement]'>
                            </div>
                            <div class='col-sm-12 mt-4'>
                                <div class='checkbox'>
                                    <label class='label-container' aria-label='$langSettingSelect'>
                                        <input type='checkbox' name='AddPaddingAnnouncementsListGroup' value='1' ".((isset($theme_options_styles['AddPaddingAnnouncementsListGroup']))? 'checked' : '').">
                                        <span class='checkmark'></span>
                                        $langAddPaddingListGroup
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>



                    <hr>



                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langVisitsStats</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorStatisticsHomepage' class=' mb-2 me-2'>$langBgColor</label>
                                <input name='BgColorStatisticsHomepage' type='text' class='form-control colorpicker' id='BgColorStatisticsHomepage' value='$theme_options_styles[BgColorStatisticsHomepage]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BgColorStatisticsHomepage_gr' type='text' class='form-control colorpicker' id='BgColorStatisticsHomepage_gr' value='$theme_options_styles[BgColorStatisticsHomepage_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorStatisticsHomepage' class='mb-2 me-2'>$langTextColor</label>
                                <input name='TextColorStatisticsHomepage' type='text' class='form-control colorpicker' id='TextColorStatisticsHomepage' value='$theme_options_styles[TextColorStatisticsHomepage]'>
                            </div>
                        </div>
                    </div>



                    <hr>



                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langPopularCourse</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorPopularCoursesHomepage' class=' mb-2 me-2'>$langBgColor</label>
                                <input name='BgColorPopularCoursesHomepage' type='text' class='form-control colorpicker' id='BgColorPopularCoursesHomepage' value='$theme_options_styles[BgColorPopularCoursesHomepage]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BgColorPopularCoursesHomepage_gr' type='text' class='form-control colorpicker' id='BgColorPopularCoursesHomepage_gr' value='$theme_options_styles[BgColorPopularCoursesHomepage_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorPopularCoursesHomepage' class='mb-2 me-2'>$langTextColor</label>
                                <input name='TextColorPopularCoursesHomepage' type='text' class='form-control colorpicker' id='TextColorPopularCoursesHomepage' value='$theme_options_styles[TextColorPopularCoursesHomepage]'>
                            </div>
                        </div>
                    </div>



                    <hr>



                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langHomepageTexts</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorTextsHomepage' class=' mb-2 me-2'>$langBgColor</label>
                                <input name='BgColorTextsHomepage' type='text' class='form-control colorpicker' id='BgColorTextsHomepage' value='$theme_options_styles[BgColorTextsHomepage]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BgColorTextsHomepage_gr' type='text' class='form-control colorpicker' id='BgColorTextsHomepage_gr' value='$theme_options_styles[BgColorTextsHomepage_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorTextsHomepage' class=' mb-2 me-2'>$langTextColor</label>
                                <input name='TextColorTextsHomepage' type='text' class='form-control colorpicker' id='TextColorTextsHomepage' value='$theme_options_styles[TextColorTextsHomepage]'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// portfolio
function build_portfolio() {
    global $langPortFolioProfileContainer, $langBgColorBasicUserInfo, $theme_options_styles, $logo_imageUploadBriefProfilePortfolio,
           $langBgGradientBgImageProfileContainerInfo, $langBgColor, $langBriefProfilePortfolioTextColor, $langButtonInBriefProfile,
           $langBgColorSectionBasicUserBtns, $langBgColorContainerOfBriefBtns, $langBgColorButton, $langTextColorButton, $langHoverBgColorButton,
           $langPortfolioCoursesContainer;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navcontainer'>
                <div class='form-wrapper form-edit rounded'>
                    
                    <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langPortFolioProfileContainer</h2>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center mb-3'>
                        <label for='bgColorContainerPortfolioInfo' class='mb-2 me-2'>$langBgColorBasicUserInfo</label>
                        <input name='bgColorContainerPortfolioInfo' type='text' class='form-control colorpicker' id='bgColorContainerPortfolioInfo' value='$theme_options_styles[bgColorContainerPortfolioInfo]'>
                    </div>
                    $logo_imageUploadBriefProfilePortfolio
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BriefProfilePortfolioBgColor' class='mb-2 me-2'>$langBgGradientBgImageProfileContainerInfo</label>
                        <input name='BriefProfilePortfolioBgColor' type='text' class='form-control colorpicker' id='BriefProfilePortfolioBgColor' value='$theme_options_styles[BriefProfilePortfolioBgColor]'>
                        <i class='fa fa-arrow-right ms-3 me-3'></i>
                        <input aria-label='$langBgColor' name='BriefProfilePortfolioBgColor_gr' type='text' class='form-control colorpicker' id='BriefProfilePortfolioBgColor_gr' value='$theme_options_styles[BriefProfilePortfolioBgColor_gr]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BriefProfilePortfolioTextColor' class=' mb-2 me-2'>$langBriefProfilePortfolioTextColor:</label>
                        <input name='BriefProfilePortfolioTextColor' type='text' class='form-control colorpicker' id='BriefProfilePortfolioTextColor' value='$theme_options_styles[BriefProfilePortfolioTextColor]'>
                    </div>

                    <hr>

                    <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langButtonInBriefProfile</h2>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center mb-3'>
                        <label for='bgColorSectionPortfolioBtns' class=' mb-2 me-2'>$langBgColorSectionBasicUserBtns</label>
                        <input name='bgColorSectionPortfolioBtns' type='text' class='form-control colorpicker' id='bgColorSectionPortfolioBtns' value='$theme_options_styles[bgColorSectionPortfolioBtns]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgColorContainerPortfolioButtons' class=' mb-2 me-2'>$langBgColorContainerOfBriefBtns</label>
                        <input name='bgColorContainerPortfolioButtons' type='text' class='form-control colorpicker' id='bgColorContainerPortfolioButtons' value='$theme_options_styles[bgColorContainerPortfolioButtons]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgColorPortfolioButtons' class=' mb-2 me-2'>$langBgColorButton:</label>
                        <input name='bgColorPortfolioButtons' type='text' class='form-control colorpicker' id='bgColorPortfolioButtons' value='$theme_options_styles[bgColorPortfolioButtons]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='textColorPortfolioButtons' class=' mb-2 me-2'>$langTextColorButton:</label>
                        <input name='textColorPortfolioButtons' type='text' class='form-control colorpicker' id='textColorPortfolioButtons' value='$theme_options_styles[textColorPortfolioButtons]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgHoverColorPortfolioButtons' class=' mb-2 me-2'>$langHoverBgColorButton:</label>
                        <input name='bgHoverColorPortfolioButtons' type='text' class='form-control colorpicker' id='bgHoverColorPortfolioButtons' value='$theme_options_styles[bgHoverColorPortfolioButtons]'>
                    </div>

                    <hr>

                    <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langPortfolioCoursesContainer</h2>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgColorWrapperPortfolioCourses' class=' mb-2 me-2'>$langBgColor:</label>
                        <input name='BgColorWrapperPortfolioCourses' type='text' class='form-control colorpicker' id='BgColorWrapperPortfolioCourses' value='$theme_options_styles[BgColorWrapperPortfolioCourses]'>
                    </div>
                       
                    
                </div>
            </div>
    ";

    return $html;
}

// coursehome
function build_coursehome() {
    global $langBgColorConfigRightColumn, $langBgColor, $theme_options_styles, $langBgImg, $RightColumnCourseBgImage,
           $langBgBorderLeftColor, $langBgColorConfig, $langHelpCourseUI, $langLogoSmall, $langMainMenuConfiguration,
           $langMainMenuLinkColor, $langMainMenuLinkHoverColor, $langSubMenuConfig, $langSubMenuLinkColor, 
           $langSubMenuLinkHoverColor, $langSubMenuLinkBgHoverColor, $langSubMenuLinkBgActive, $langSubMenuLinkColorActive;

    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navsettings'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langBgColorConfigRightColumn</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='RightColumnCourseBgColor' class='me-2 mb-2'>$langBgColor:</label>
                                <input name='RightColumnCourseBgColor' type='text' class='form-control colorpicker' id='RightColumnCourseBgColor' value='$theme_options_styles[RightColumnCourseBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='RightColumnCourseBgImage' class='me-2 mb-2'>$langBgImg:</label>
                                $RightColumnCourseBgImage
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BorderLeftToRightColumnCourseBgColor' class='me-2 mb-2'>$langBgBorderLeftColor:</label>
                                <input name='BorderLeftToRightColumnCourseBgColor' type='text' class='form-control colorpicker' id='BorderLeftToRightColumnCourseBgColor' value='$theme_options_styles[BorderLeftToRightColumnCourseBgColor]'>
                            </div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langBgColorConfig $langHelpCourseUI</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftNavBgColor' class='me-2 mb-2'>$langBgColor:</label>
                                <input name='leftNavBgColor' type='text' class='form-control colorpicker' id='leftNavBgColor' value='$theme_options_styles[leftNavBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftNavBgColorSmallScreen' class='me-2 mb-2'>$langBgColor <small>$langLogoSmall</small>:</label>
                                <input name='leftNavBgColorSmallScreen' type='text' class='form-control colorpicker' id='leftNavBgColorSmallScreen' value='$theme_options_styles[leftNavBgColorSmallScreen]'>
                            </div>
                            <hr>
                            <h2 class='theme_options_legend text-decoration-underline mt-2 text-heading-h3'>$langMainMenuConfiguration $langHelpCourseUI</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftMenuFontColor' class=' mb-2 me-2'>$langMainMenuLinkColor:</label>
                                <input name='leftMenuFontColor' type='text' class='form-control colorpicker' id='leftMenuFontColor' value='$theme_options_styles[leftMenuFontColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftMenuHoverFontColor' class=' mb-2 me-2'>$langMainMenuLinkHoverColor:</label>
                                <input name='leftMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftMenuHoverFontColor' value='$theme_options_styles[leftMenuHoverFontColor]'>
                            </div>
                            <hr>
                            <h2 class='theme_options_legend text-decoration-underline mt-2 text-heading-h3'>$langSubMenuConfig $langHelpCourseUI</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftSubMenuFontColor' class=' mb-2 me-2'>$langSubMenuLinkColor:</label>
                                <input name='leftSubMenuFontColor' type='text' class='form-control colorpicker' id='leftSubMenuFontColor' value='$theme_options_styles[leftSubMenuFontColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftSubMenuHoverFontColor' class=' mb-2 me-2'>$langSubMenuLinkHoverColor:</label>
                                <input name='leftSubMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverFontColor' value='$theme_options_styles[leftSubMenuHoverFontColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftSubMenuHoverBgColor' class=' mb-2 me-2'>$langSubMenuLinkBgHoverColor:</label>
                                <input name='leftSubMenuHoverBgColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverBgColor' value='$theme_options_styles[leftSubMenuHoverBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftMenuSelectedBgColor' class=' mb-2 me-2'>$langSubMenuLinkBgActive:</label>
                                <input name='leftMenuSelectedBgColor' type='text' class='form-control colorpicker' id='leftMenuSelectedBgColor' value='$theme_options_styles[leftMenuSelectedBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftMenuSelectedLinkColor' class=' mb-2 me-2'>$langSubMenuLinkColorActive:</label>
                                <input name='leftMenuSelectedLinkColor' type='text' class='form-control colorpicker' id='leftMenuSelectedLinkColor' value='$theme_options_styles[leftMenuSelectedLinkColor]'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

// more options
function build_more_options() {
    global $langAboutImportantAnnouncement, $langbgContainerImportantAnnouncement, $theme_options_styles, $langclContainerImportantAnnouncement,
           $langclLinkImportantAnnouncement, $langclHoveredLinkImportantAnnouncement, $langAboutFaqImageUpload, $faq_image_fieldL,
           $langContact, $langChooseContactImg, $contactUpload, $langAboutChatContainer, $langContainerBgColor, $langBorderContainerBgColor,
           $langBoxShadowPanels, $langAboutCourseInfoContainer, $langAboutUnitsContainer, $langColorFocus, $langBoxShadowInputSelect,
           $langBgColor, $langBorderTextColor, $langUserThemeCustomization, $langEnableUserThemeCustomization, $langEnableUserThemeCustomizationHelp,
           $enable_user_theme_customization, $langSelectThemesForUsers, $langSelectThemesForUsersHelp, $theme_checkboxes_html;
    $html = '';
    $html .= "
            <div role='tabpanel' class='tab-pane fade' id='navMoreOptions'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline text-heading-h3'>$langAboutImportantAnnouncement</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgContainerImportantAnnouncement' class='mb-2 me-2'>$langbgContainerImportantAnnouncement:</label>
                                <input name='bgContainerImportantAnnouncement' type='text' class='form-control colorpicker' id='bgContainerImportantAnnouncement' value='$theme_options_styles[bgContainerImportantAnnouncement]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clContainerImportantAnnouncement' class=' mb-2 me-2'>$langclContainerImportantAnnouncement:</label>
                                <input name='clContainerImportantAnnouncement' type='text' class='form-control colorpicker' id='clContainerImportantAnnouncement' value='$theme_options_styles[clContainerImportantAnnouncement]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkImportantAnnouncement' class=' mb-2 me-2'>$langclLinkImportantAnnouncement:</label>
                                <input name='clLinkImportantAnnouncement' type='text' class='form-control colorpicker' id='clLinkImportantAnnouncement' value='$theme_options_styles[clLinkImportantAnnouncement]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredLinkImportantAnnouncement' class='mb-2 me-2'>$langclHoveredLinkImportantAnnouncement:</label>
                                <input name='clHoveredLinkImportantAnnouncement' type='text' class='form-control colorpicker' id='clHoveredLinkImportantAnnouncement' value='$theme_options_styles[clHoveredLinkImportantAnnouncement]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langAboutFaqImageUpload</h2>
                            <div class='form-group mt-4'>

                                <div class='col-sm-12'>
                                    $faq_image_fieldL
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langContact </h2>
                        <div class='col-sm-12 mb-2'>$langChooseContactImg:</div>
                        <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                            $contactUpload
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langAboutChatContainer</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutChatContainer' class='mb-2 me-2'>$langContainerBgColor:</label>
                                <input name='AboutChatContainer' type='text' class='form-control colorpicker' id='AboutChatContainer' value='$theme_options_styles[AboutChatContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutBorderChatContainer' class=' mb-2 me-2'>$langBorderContainerBgColor:</label>
                                <input name='AboutBorderChatContainer' type='text' class='form-control colorpicker' id='AboutBorderChatContainer' value='$theme_options_styles[AboutBorderChatContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutChatContainerBoxShadow' class=' mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='AboutChatContainerBoxShadow' type='text' class='form-control colorpicker' id='AboutChatContainerBoxShadow' value='$theme_options_styles[AboutChatContainerBoxShadow]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langAboutCourseInfoContainer</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutCourseInfoContainer' class=' mb-2 me-2'>$langContainerBgColor:</label>
                                <input name='AboutCourseInfoContainer' type='text' class='form-control colorpicker' id='AboutCourseInfoContainer' value='$theme_options_styles[AboutCourseInfoContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutBorderCourseInfoContainer' class=' mb-2 me-2'>$langBorderContainerBgColor:</label>
                                <input name='AboutBorderCourseInfoContainer' type='text' class='form-control colorpicker' id='AboutBorderCourseInfoContainer' value='$theme_options_styles[AboutBorderCourseInfoContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutCourseInfoContainerBoxShadow' class=' mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='AboutCourseInfoContainerBoxShadow' type='text' class='form-control colorpicker' id='AboutCourseInfoContainerBoxShadow' value='$theme_options_styles[AboutCourseInfoContainerBoxShadow]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langAboutUnitsContainer</h2>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutUnitsContainer' class=' mb-2 me-2'>$langContainerBgColor:</label>
                                <input name='AboutUnitsContainer' type='text' class='form-control colorpicker' id='AboutUnitsContainer' value='$theme_options_styles[AboutUnitsContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutBorderUnitsContainer' class=' mb-2 me-2'>$langBorderContainerBgColor:</label>
                                <input name='AboutBorderUnitsContainer' type='text' class='form-control colorpicker' id='AboutBorderUnitsContainer' value='$theme_options_styles[AboutBorderUnitsContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutUnitsContainerBoxShadow' class='mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='AboutUnitsContainerBoxShadow' type='text' class='form-control colorpicker' id='AboutUnitsContainerBoxShadow' value='$theme_options_styles[AboutUnitsContainerBoxShadow]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='ColorFocus' class=' mb-2 me-2'>$langColorFocus:</label>
                        <input name='ColorFocus' type='text' class='form-control colorpicker' id='ColorFocus' value='$theme_options_styles[ColorFocus]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='boxShadowInputSelect' class=' mb-2 me-2'>$langBoxShadowInputSelect:</label>
                        <input name='boxShadowInputSelect' type='text' class='form-control colorpicker' id='boxShadowInputSelect' value='$theme_options_styles[boxShadowInputSelect]'>
                    </div>
                    <hr>
                    <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>Breadcrumbs</h2>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgColorBreadcrumb' class=' mb-2 me-2'>$langBgColor:</label>
                        <input name='bgColorBreadcrumb' type='text' class='form-control colorpicker' id='bgColorBreadcrumb' value='$theme_options_styles[bgColorBreadcrumb]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BorderColorBreadcrumb' class=' mb-2 me-2'>$langBorderTextColor:</label>
                        <input name='BorderColorBreadcrumb' type='text' class='form-control colorpicker' id='BorderColorBreadcrumb' value='$theme_options_styles[BorderColorBreadcrumb]'>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div class='w-100'>
                            <h2 class='theme_options_legend text-decoration-underline mt-4 text-heading-h3'>$langUserThemeCustomization</h2>
                            <div class='form-group mt-4'>
                                <div class='checkbox'>
                                    <label class='label-container' aria-label='$langEnableUserThemeCustomization'>
                                        <input type='checkbox' name='enable_user_theme_customization' id='enable_user_theme_customization' value='1' ".($enable_user_theme_customization ? 'checked' : '').">
                                        <span class='checkmark'></span>
                                        $langEnableUserThemeCustomization
                                    </label>
                                    <small class='ms-5 d-block mt-2'>$langEnableUserThemeCustomizationHelp</small>
                                </div>
                            </div>

                            <!-- Theme Selection Section (shown when checkbox is enabled) -->
                            <div id='user_selectable_themes_section' class='form-group mt-4 ".($enable_user_theme_customization ? '' : 'd-none')."'>
                                <h4 class='theme_options_legend text-decoration-underline mt-3 mb-3'>$langSelectThemesForUsers</h4>
                                <p class='mb-3'>$langSelectThemesForUsersHelp</p>
                                <div class='row'>".
                                    $theme_checkboxes_html ."
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    ";

    return $html;
}

draw($tool_content, 3, null, $head_content);

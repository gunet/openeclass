<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
//Default Styles
$defaults = array(
                'rgba(255, 255, 255, 1)' => array('leftNavBgColor','leftNavBgColorSmallScreen','bgColor','buttonTextColor', 'bgColorContentPlatform', 'clAlertInfo', 'clAlertWarning', 'clAlertSuccess', 'clAlertDanger',
                                                    'whiteButtonHoveredBgColor','BgColorWrapperHeader', 'bgColorWrapperFooter', 'clLinkAlertInfo', 'clLinkAlertWarning', 'clLinkAlertSuccess', 'clLinkAlertDanger',
                                                    'BgColorWrapperPortfolioCourses', 'RightColumnCourseBgColor', 'BgPanels', 'BgCommentsPanels', 'BgQuestionnairePanels', 'BgReportsPanels', 'BgExercisesPanels', 'BgForms', 'BgTables', 'bgLists' ,
                                                    'bgContextualMenu', 'bgColorListMenu', 'bgWhiteButtonColor', 'BgRadios', 'ClIconRadios', 'BgCheckboxes', 'ClIconCheckboxes',
                                                    'BgInput', 'BgSelect' ,'clHoveredSelectOption' ,'clOptionSelected', 'BgModal', 'bgAgenda', 'clColorHeaderAgenda',
                                                    'BgMenuPopover', 'BgMenuPopoverOption', 'BgTextEditor', 'BgScrollBar' ,'BackProgressBar', 'TextColorActiveDateTime', 'TextColorTooltip', 'clDeleteButtonColor',
                                                    'clHoveredDeleteButtonColor', 'clSuccessButtonColor', 'clHoveredSuccessButtonColor', 'clHelpButtonColor', 'clHoveredHelpButtonColor', 'BgBorderForms',
                                                    'BgColorAnnouncementHomepageLink','clBadgeSuccess','clBadgeWarning','clBadgeNeutral','clBadgePrimary','clBadgeAccent', 'BoxShadowPanels', 'AboutChatContainerBoxShadow', 'AboutCourseInfoContainerBoxShadow', 'AboutUnitsContainerBoxShadow', 'FormsBoxShadow', 
                                                    'BoxShadowRowTables', 'bgPanelEvents', 'bgBorderHoveredPanels', 'BgColorStatisticsHomepage', 'BgColorPopularCoursesHomepage', 'BgColorTextsHomepage', 'BgColorStatisticsHomepage_gr', 'BgColorPopularCoursesHomepage_gr', 'BgColorTextsHomepage_gr'),
                'rgba(247, 249, 254, 1)' => array('BriefProfilePortfolioBgColor', 'BriefProfilePortfolioBgColor_gr', 'loginJumbotronRadialBgColor','loginJumbotronBgColor','bgRadialWrapperJumbotron','BgColorAnnouncementHomepage', 'BgColorAnnouncementHomepage_gr', 'AboutUnitsContainer', 'AboutCourseInfoContainer'),
                'rgb(0, 115, 230, 1)' => array('leftMenuFontColor','buttonBgColor', 'whiteButtonTextColor','whiteButtonBorderTextColor', 'whiteButtonHoveredTextColor', 'whiteButtonHoveredBorderTextColor', 'BgClRadios', 'BgActiveCheckboxes', 'clHoveredMenuPopoverOption', 'clLinkImportantAnnouncement'),
                'rgba(43, 57, 68, 1)' => array('linkColorHeader','linkColorFooter','loginTextColor', 'leftSubMenuFontColor','ColorHyperTexts', 'clLabelForms', 'clListMenuUsername',
                                                'clListMenu', 'BriefProfilePortfolioTextColor', 'ClRadios', 'ClCheckboxes', 'ClActiveCheckboxes', 'clTextModal',
                                                'BgColorHeaderAgenda', 'clMenuPopoverOption', 'bgColorTooltip', 'TextColorAnnouncementHomepage','BgBadgeNeutral', 'clHoveredTextPanels', 'TextColorStatisticsHomepage', 'TextColorPopularCoursesHomepage', 'TextColorTextsHomepage'),
                'rgba(0, 115, 230, 1)' => array('linkColor','linkHoverColorHeader','linkHoverColorFooter','leftSubMenuHoverFontColor','linkActiveColorHeader',
                                                'clHoveredTabs', 'clActiveTabs', 'clHoveredAccordions', 'clActiveAccordions', 'clLists', 'clHoveredLists', 'bgHoveredSelectOption',
                                                'bgOptionSelected', 'BgBorderBottomHeadTables', 'HoveredActiveLinkColorHeader', 'BgColorProgressBarAndText', 'clLinkImportantAnnouncement',
                                                'clColorAnnouncementHomepageLinkElement','clHoveredColorAnnouncementHomepageLinkElement', 'ColorBlueText', 'ColorFocus'),
                'rgba(0, 115, 230, 0.7)' => array('buttonHoverBgColor', 'clHoveredLinkImportantAnnouncement'),
                "rgba(77,161,228,1)" => array('leftMenuSelectedFontColor', 'leftMenuHoverFontColor'),
                "rgba(239, 246, 255, 1)" => array('leftSubMenuHoverBgColor','leftMenuSelectedBgColor','linkActiveBgColorHeader', 'clBorderPanels', 'clBorderBgCommentsPanels', 'clBorderQuestionnairePanels', 'clBorderReportsPanels', 'clBorderExercisesPanels', 'clBorderBottomListMenu',
                                                    'clHoveredListMenu', 'bgHoveredListMenu', 'BgBorderColorAgenda', 'BgBorderBottomRowTables', 'BgBorderColorAgendaEvent',
                                                    'clBorderBottomMenuPopoverOption', 'BgHoveredMenuPopoverOption', 'AboutBorderChatContainer', 'AboutChatContainer', 'AboutBorderCourseInfoContainer', 'AboutBorderUnitsContainer'),
                "rgba(35,82,124,1)" => array('linkHoverColor','clLinkHoveredAlertInfo','clLinkHoveredAlertWarning','clLinkHoveredAlertSuccess','clLinkHoveredAlertDanger'),
                "rgba(0,0,0,0.2)" => array('leftMenuBgColor'),
                "rgba(0,0,0,0)" => array('loginTextBgColor'),
                "rgba(180, 190, 209, 1)" => array('BgColorScrollBar', 'BgHoveredColorScrollBar'),
                "rgba(79, 104, 147, 1)" => array('clContainerImportantAnnouncement'),
                "rgba(104, 125, 163, 1)" => array('ClInactiveRadios', 'ClInactiveCheckboxes', 'clBorderInput', 'clBorderSelect', 'clColorHoveredBodyAgenda', 'BgBorderTextEditor'),
                "rgba(232, 237, 248, 1)" => array('clBorderBottomAccordions', 'clBorderModal', 'BgBorderMenuPopover', 'BorderLeftToRightColumnCourseBgColor'),
                "rgba(239, 242, 251, 1)" => array('clBorderBottomLists','BgBorderColorAnnouncementHomepageLink'),
                "rgba(205, 212, 224, 1)" => array('bgBorderContextualMenu'),
                "rgba(155, 169, 193, 1)" => array('BgBorderRadios', 'BgBorderCheckboxes', 'bgHelpButtonColor'),
                "rgba(0, 51, 153, 1)" => array('bgColorActiveDateTime'),
                "rgba(232, 232, 232, 1)" => array('BgProgressBar'),
                "rgba(196, 70, 1, 1)" => array('bgDeleteButtonColor', 'clListMenuLogout', 'clListMenuDeletion', 'linkDeleteColor', 'clDeleteMenuPopoverOption', 'clDeleteIconModal', 'clXmarkModal','BgBadgeAccent', 'bgAlertDanger', 'clRequiredFieldForm', 'ColorRedText'),
                "rgba(183, 10, 10, 1)" => array('bgHoveredDeleteButtonColor'),
                "rgba(225, 225, 225, 1)" => array('bgColorHoveredBodyAgenda'),
                "rgba(30, 126, 14, 1)" => array('bgSuccessButtonColor','BgBadgeSuccess', 'bgAlertSuccess', 'ColorGreenText'),
                "rgba(245, 118, 0, 1)" => array('BgBadgeWarning', 'bgAlertWarning', 'ColorOrangeText'),
                "rgba(37, 70, 240, 1)" => array('BgBadgePrimary', 'bgAlertInfo'),
                "rgba(30, 126, 14, 0.81)" => array('bgHoveredSuccessButtonColor'),
                "rgba(155, 169, 193, 0.82)" => array('bgHoveredHelpButtonColor'),
                "rgba(255, 255, 255, 0)" => array('bgHoveredBoxShadowPanels'),
                "rgba(232, 242, 231, 1)" => array('bgContainerImportantAnnouncement'),
                "rgba(62, 73, 101, 1)" => array('clOptionSelect', 'ClTextEditor', 'clInputText', 'clTabs', 'clAccordions', 'clColorBodyAgenda'),
                "rgba(0, 74, 148, 1)" => array('leftMenuSelectedLinkColor'),
                "rgba(250, 251, 252,1)" => array('bgColorDeactiveDateTime'),
                "repeat" => array('bgType'),
                "boxed" => array('containerType'),
                "fluid" => array('view_platform'),
                "small-right" => array("loginImgPlacement"),
                "" => array('fluidContainerWidth')
            );
$active_theme = get_config('theme_options_id');
$preview_theme = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : NULL;
$theme_id = isset($preview_theme) ? $preview_theme : $active_theme;
if (isset($_GET['reset_theme_options'])) {
    unset($_SESSION['theme_options_id']);
    redirect_to_home_page('modules/admin/theme_options.php');
}
if (isset($_GET['delete_image'])) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
        $theme_options_styles = unserialize($theme_options->styles);
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
            array_push($file_list, "courses/theme_data/$theme_id/$styles[bgImage]");
        }
        if (isset($styles['imageUpload'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[imageUpload]");
        }
        if (isset($styles['imageUploadSmall'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[imageUploadSmall]");
        }
        if (isset($styles['imageUploadFooter'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[imageUploadFooter]");
        }
        if (isset($styles['imageUploadForm'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[imageUploadForm]");
        }
        if (isset($styles['imageUploadRegistration'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[imageUploadRegistration]");
        }
        if (isset($styles['imageUploadFaq'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[imageUploadFaq]");
        }
        if (isset($styles['loginImg'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[loginImg]");
        }
        if (isset($styles['loginImgL'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[loginImgL]");
        }
        if(isset($styles['RightColumnCourseBgImage'])){
            array_push($file_list, "courses/theme_data/$theme_id/$styles[RightColumnCourseBgImage]");
        }
        if (isset($styles['faviconUpload'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[faviconUpload]");
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
            if($connector->isEnabled() == true ){
                $output=$connector->check("courses/theme_data/$file_name");
                if($output->status==$output::STATUS_INFECTED){
                    AntivirusApp::block($output->output);
                }
            }
            $archive = new ZipArchive();
            if ($archive->open("courses/theme_data/$file_name")) {
                $archive->extractTo('courses/theme_data/temp');
                unlink("$webDir/courses/theme_data/$file_name");
                $base64_str = file_get_contents("$webDir/courses/theme_data/temp/theme_options.txt");
                unlink("$webDir/courses/theme_data/temp/theme_options.txt");
                $theme_options = unserialize(base64_decode($base64_str));
                $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles, version) VALUES(?s, ?s, 4)", $theme_options->name, $theme_options->styles)->lastInsertID;
                rename("$webDir/courses/theme_data/temp/".intval($theme_options->id), "$webDir/courses/theme_data/temp/$new_theme_id");
                recurse_copy("$webDir/courses/theme_data/temp","$webDir/courses/theme_data");
                removeDir("$webDir/courses/theme_data/temp");
                Session::flash('message',$langThemeInstalled);
                Session::flash('alert-class', 'alert-success');
            } else {
                die("Error while unzipping file !");
            }
            $archive->close();
        }
    } else {
        Session::flash('message',$langUnwantedFiletype);
        Session::flash('alert-class', 'alert-danger');
    }
    redirect_to_home_page('modules/admin/theme_options.php');
}
if (isset($_POST['optionsSave'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
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

    clear_default_settings();
    $serialized_data = serialize($_POST);
    Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, $theme_id);
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_GET['delThemeId'])) {
    $theme_id = intval($_GET['delThemeId']);
    $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
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
    $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles, version) VALUES(?s, '', 4)", $theme_options_name)->lastInsertID;
    clear_default_settings();

    clone_images($new_theme_id); //clone images
    upload_images($new_theme_id); //upload new images
    $serialized_data = serialize($_POST);
    Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, $new_theme_id);
    $_SESSION['theme_options_id'] = $new_theme_id;
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
        set_config('theme_options_id', $_POST['active_theme_options']);
        unset($_SESSION['theme_options_id']);
    }
    redirect_to_home_page('modules/admin/theme_options.php');
} else {
    $pageName = $langThemeSettings;
    $navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
    load_js('spectrum');
    load_js('bootstrap-slider');
    $head_content .= "
    <script>
        $(function(){
            $('#fluidContainerWidth').slider({
                tooltip: 'hide',
                formatter: function(value) {
                    $('#pixelCounter').text(value + 'px');
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
            $('select#theme_selection').change(function ()
            {
                var cur_val = $(this).val();
                if (cur_val == '$active_theme') {
                    $('a#theme_enable').addClass('hidden');
                    $('a#theme_preview').addClass('hidden');
                } else {
                    $('a#theme_enable').removeClass('hidden');
                    if (cur_val != '$preview_theme') {
                        $('a#theme_preview').removeClass('hidden');
                    }
                }
                if (cur_val == '$preview_theme') $('a#theme_preview').addClass('hidden');
                if (cur_val == 0) {
                    $('a#theme_delete').addClass('hidden');
                } else {
                    $('a#theme_delete').removeClass('hidden');
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




    $activate_class = isset($preview_theme) ? '' : ' hidden';
    $activate_btn = "<a href='#' class='theme_enable btn submitAdminBtn $activate_class' id='theme_enable'>$langActivate</a>";
    $preview_class = ' hidden';
    $preview_btn = "<a href='#' class='btn submitAdminBtn $preview_class' id='theme_preview'>$langSee</a>";
    $del_class = ($theme_id != 0) ? "" : " hidden";
    $delete_btn = "
                    <form class='form-inline mt-0' style='display:inline;' method='post' action='$_SERVER[SCRIPT_NAME]?delThemeId=$theme_id'>
                        <a class='confirmAction mt-md-0 btn deleteAdminBtn $del_class delThemeBtn' id='theme_delete' data-title='$langConfirmDelete' data-message='$langThemeSettingsDelete' data-cancel-txt='$langCancel' data-action-txt='$langDelete' data-action-class='deleteAdminBtn'>$langDelete</a>
                    </form>";
    $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
    if (isset($theme_options_styles['imageUpload'])) {
        $logo_field = "
            <img src='$urlThemeData/$theme_options_styles[imageUpload]' style='max-height:100px;max-width:150px;' alt='Image upload for large screen'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUpload'>$langDelete</a>
            <input type='hidden' name='imageUpload' value='$theme_options_styles[imageUpload]'>
        ";
    } else {
       $logo_field = "<label for='imageUpload' aria-label='$langLogo'></label><input type='file' name='imageUpload' id='imageUpload'>";
    }
    if (isset($theme_options_styles['imageUploadSmall'])) {
        $small_logo_field = "
            <img src='$urlThemeData/$theme_options_styles[imageUploadSmall]' style='max-height:100px;max-width:150px;' alt='Image upload for small screen'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadSmall'>$langDelete</a>
            <input type='hidden' name='imageUploadSmall' value='$theme_options_styles[imageUploadSmall]'>
        ";
    } else {
       $small_logo_field = "<label for='imageUploadSmall' aria-label='$langLogoSmall'></label><input type='file' name='imageUploadSmall' id='imageUploadSmall'>";
    }
    if (isset($theme_options_styles['imageUploadFooter'])) {
        $image_footer_field = "
            <img src='$urlThemeData/$theme_options_styles[imageUploadFooter]' style='max-height:100px;max-width:150px;' alt='Image upload for footer'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadFooter'>$langDelete</a>
            <input type='hidden' name='imageUploadFooter' value='$theme_options_styles[imageUploadFooter]'>
        ";
    } else {
       $image_footer_field = "<label for='imageUploadFooter' aria-label='$langFooterUploadImage'></label><input type='file' name='imageUploadFooter' id='imageUploadFooter'>";
    }
    if (isset($theme_options_styles['bgImage'])) {
        $bg_field = "
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[bgImage]' style='max-height:100px;max-width:150px;' alt='Image upload for background'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=bgImage'>$langDelete</a>
            </div>
            <input type='hidden' name='bgImage' value='$theme_options_styles[bgImage]'>
        ";
    } else {
       $bg_field = "<input aria-label='$langBgImg' type='file' name='bgImage' id='bgImage'>";
    }
    if (isset($theme_options_styles['loginImg'])) {
        $login_image_field = "
            <div class='col-sm-12 control-label-notes mb-2'>$langBgImg (jumbotron):</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[loginImg]' style='max-height:100px;max-width:150px;' alt='Image upload for login form'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=loginImg'>$langDelete</a>
            </div>
            <input type='hidden' name='loginImg' value='$theme_options_styles[loginImg]'>
        ";
    } else {
       $login_image_field = "
            <label for='loginImg' class='col-sm-12 control-label-notes mb-2'>$langBgImg (jumbotron):</label>
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
            <div class='col-sm-12 control-label-notes mb-2'>$langLoginImg:</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[loginImgL]' style='max-height:100px;max-width:150px;' alt='Image upload'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=loginImgL'>$langDelete</a>
            </div>
            <input type='hidden' name='loginImgL' value='$theme_options_styles[loginImgL]'>
        ";
    } else {
       $login_image_fieldL = "
            <label for='loginImgL' class='col-sm-12 control-label-notes mb-2'>$langLoginImg:</label>
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

    if (isset($theme_options_styles['imageUploadForm'])) {
        $form_image_fieldL = "
            <div class='col-12 control-label-notes'>$langFormUploadImage</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[imageUploadForm]' style='max-height:100px;max-width:150px;' alt='$langDownloadFile'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadForm'>$langDelete</a>
            </div>
            <input type='hidden' name='imageUploadForm' value='$theme_options_styles[imageUploadForm]'>
        ";
    } else {
       $form_image_fieldL = "
            <label for='imageUploadForm' class='col-12 control-label-notes'>$langFormUploadImage</label>
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
        $registration_image_fieldL = "
            <div class='col-sm-12 control-label-notes mb-2'>$langRegistrationUploadImage:</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[imageUploadRegistration]' style='max-height:100px;max-width:150px;' alt='Image upload'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadRegistration'>$langDelete</a>
            </div>
            <input type='hidden' name='imageUploadRegistration' value='$theme_options_styles[imageUploadRegistration]'>
        ";
    } else {
       $registration_image_fieldL = "
            <label for='imageUploadRegistration' class='col-sm-12 control-label-notes mb-2'>$langRegistrationUploadImage:</label>
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
        $faq_image_fieldL = "
            <div class='col-sm-12 control-label-notes mb-2'>$langFaqUploadImage:</div>
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[imageUploadFaq]' style='max-height:100px;max-width:150px;' alt='Image upload'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadFaq'>$langDelete</a>
            </div>
            <input type='hidden' name='imageUploadFaq' value='$theme_options_styles[imageUploadFaq]'>
        ";
    } else {
       $faq_image_fieldL = "
            <label for='imageUploadFaq' class='col-sm-12 control-label-notes mb-2'>$langFaqUploadImage:</label>
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
        $RightColumnCourseBgImage = "
            <img src='$urlThemeData/$theme_options_styles[RightColumnCourseBgImage]' style='max-height:100px;max-width:150px;' alt='Image upload for course content'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=RightColumnCourseBgImage'>$langDelete</a>
            <input type='hidden' name='RightColumnCourseBgImage' value='$theme_options_styles[RightColumnCourseBgImage]' id='RightColumnCourseBgImage'>
        ";
    } else {
       $RightColumnCourseBgImage = "<input type='file' name='RightColumnCourseBgImage' id='RightColumnCourseBgImage'>";
    }


    if (isset($theme_options_styles['faviconUpload'])) {
        $faviconUpload = "
            <img src='$urlThemeData/$theme_options_styles[faviconUpload]' style='max-height:100px;max-width:150px;' alt='Favicon upload'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=faviconUpload'>$langDelete</a>
            <input type='hidden' name='faviconUpload' value='$theme_options_styles[faviconUpload]'>
        ";
    } else {
       $faviconUpload = "<label for='faviconUpload' aria-label='$langFavicon'></label><input type='file' name='faviconUpload' id='faviconUpload'>";
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
                    <h3 class='mb-1'>$langActiveTheme:</h3>
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
<div class='card panelCard px-lg-4 py-lg-3 h-100'>
<div class='card-body'>
<div role='tabpanel mt-4'>

  <!-- Nav tabs -->
  <ul class='nav nav-tabs' role='tablist'>
    <li role='presentation' class='nav-item'><a class='nav-link active' href='#generalsetting' aria-controls='generalsetting' role='tab' data-bs-toggle='tab'>$langGeneralSettings</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsBody' aria-controls='navsettingsBody' role='tab' data-bs-toggle='tab'>$langNavBody</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsHeader' aria-controls='navsettingsHeader' role='tab' data-bs-toggle='tab'>$langNavSettingsHeader</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsMainSection' aria-controls='navsettingsMainSection' role='tab' data-bs-toggle='tab'>$langNavSettingsnavsettingsMainSection</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsFooter' aria-controls='navsettingsFooter' role='tab' data-bs-toggle='tab'>$langNavSettingsFooter</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navLinks' aria-controls='navLinks' role='tab' data-bs-toggle='tab'>$langNavLinks</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navButtons' aria-controls='navButtons' role='tab' data-bs-toggle='tab'>$langNavButtons</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navHyperTexts' aria-controls='navHyperTexts' role='tab' data-bs-toggle='tab'>$langNavHyperTexts</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navPanels' aria-controls='navPanels' role='tab' data-bs-toggle='tab'>$langPanels</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navForms' aria-controls='navForms' role='tab' data-bs-toggle='tab'>$langForms</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navRadios' aria-controls='navRadios' role='tab' data-bs-toggle='tab'>$langRadio</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navCheckboxes' aria-controls='navCheckboxes' role='tab' data-bs-toggle='tab'>$langCheckbox</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navInputText' aria-controls='navInputText' role='tab' data-bs-toggle='tab'>$langInputText</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navTextEditor' aria-controls='navTextEditor' role='tab' data-bs-toggle='tab'>$langInputTextEditor</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navSelect' aria-controls='navSelect' role='tab' data-bs-toggle='tab'>$langSelectOption</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navModal' aria-controls='navModal' role='tab' data-bs-toggle='tab'>$langModals</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navTables' aria-controls='navTables' role='tab' data-bs-toggle='tab'>$langTables</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navTabs' aria-controls='navTabs' role='tab' data-bs-toggle='tab'>$langTabs</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navAccordions' aria-controls='navAccordions' role='tab' data-bs-toggle='tab'>$langAccordions</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navLists' aria-controls='navLists' role='tab' data-bs-toggle='tab'>$langLists</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navContextualMenu' aria-controls='navContextualMenu' role='tab' data-bs-toggle='tab'>$langContextualMenu</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navMenuPopover' aria-controls='navMenuPopover' role='tab' data-bs-toggle='tab'>$langMPopover</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsAgenda' aria-controls='navsettingsAgenda' role='tab' data-bs-toggle='tab'>$langNavSettingsAgenda</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsScrollBar' aria-controls='navsettingsScrollBar' role='tab' data-bs-toggle='tab'>$langNavSettingsScrollBar</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsBadge' aria-controls='navsettingsBadge' role='tab' data-bs-toggle='tab'>$langNavSettingsBadges</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsProgressBar' aria-controls='navsettingsProgressBar' role='tab' data-bs-toggle='tab'>$langNavSettingsProgressBar</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsTooltip' aria-controls='navsettingsTooltip' role='tab' data-bs-toggle='tab'>$langNavSettingsTooltip</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsAlerts' aria-controls='navsettingsAlerts' role='tab' data-bs-toggle='tab'>$langNavSettingsAlert</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsLoginHomepage' aria-controls='navsettingsLoginHomepage' role='tab' data-bs-toggle='tab'>$langHomePage</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navcontainer' aria-controls='navcontainer' role='tab' data-bs-toggle='tab'>$langPortfolio</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettings' aria-controls='navsettings' role='tab' data-bs-toggle='tab'>$langNavSettings</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navMoreOptions' aria-controls='navMoreOptions' role='tab' data-bs-toggle='tab'>$langNavMoreOptions</a></li>
  </ul>

  <!-- Tab panes -->
  <div class='col-12 mt-4'>
    <form id='theme_options_form' class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' enctype='multipart/form-data' method='post'>
        <div class='tab-content'>

            <!-- GENERAL SETTINGS -->
            <div role='tabpanel' class='tab-pane fade show active' id='generalsetting'>
                <div class='form-wrapper form-edit form-create-theme rounded'>
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                            <div>
                                <h3 class='theme_options_legend text-decoration-underline'>$langViewPlatform</h3>
                                <div class='form-group'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='view_platform' id='view_platform_boxed' value='boxed' ".(($theme_options_styles['view_platform'] == 'boxed')? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langViewBoxedType
                                        </label>
                                        <small class='ms-5'>$langHelpBoxedWidthInfo</small>
                                    </div>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='view_platform' id='view_platform_fluid' value='fluid' ".(($theme_options_styles['view_platform'] == 'fluid')? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langViewFluidType
                                        </label>
                                        <small class='ms-5'>$langHelpFluidWidthInfo</small>
                                    </div>
                                </div>
                                <hr>
                                <h3 class='theme_options_legend text-decoration-underline'>$langLayoutConfig</h3>
                                <div class='form-group'>
                                    <div class='col-sm-12 control-label-notes mb-2'>$langLayout:</div>
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
                                    <label for='fluidContainerWidth' class='col-sm-6 control-label-notes mb-2'>$langFluidContainerWidth:</label>
                                    <div class='col-sm-12'>
                                        <input id='fluidContainerWidth' name='fluidContainerWidth' data-slider-id='ex1Slider' type='text' data-slider-min='1140' data-slider-max='1920' data-slider-step='10' data-slider-value='$theme_options_styles[fluidContainerWidth]' ".(($theme_options_styles['containerType'] == 'boxed')? ' disabled' : '').">
                                        <span style='margin-left:10px;' id='pixelCounter'></span>
                                    </div>
                                </div>
                                <hr>
                                <h3 class='theme_options_legend text-decoration-underline mt-2'>$langLogoConfig</h3>
                                <div class='form-group'>
                                    <div class='col-sm-12 control-label-notes mb-2'>$langLogo <small>$langLogoNormal</small>:</div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        $logo_field
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>$langLogo <small>$langLogoSmall</small>:</div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        $small_logo_field
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>$langFavicon </div>
                                    <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                        $faviconUpload
                                    </div>
                                </div>
                            </div>
                            <div class='d-flex justify-content-center align-items-start flex-wrap gap-4'>
                                <figure class='figure'>
                                    <img src='$urlServer/template/modern/images/theme_settings/general_1.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                    <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                                </figure>
                                <figure class='figure'>
                                    <img src='$urlServer/template/modern/images/theme_settings/general_2.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                    <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                                </figure>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            <!-- BODY SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsBody'>
                <div class='form-wrapper form-edit form-create-theme rounded'>
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                            <div>
                                <h3 class='theme_options_legend text-decoration-underline'>$langConfig (Body)</h3>
                                <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                    <label for='bgColor' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                    <input name='bgColor' type='text' class='form-control colorpicker' id='bgColor' value='$theme_options_styles[bgColor]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>$langBgImg:</div>
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
                                <div class='form-group mt-4'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='bgOpacityImage' value='1' ".((isset($theme_options_styles['bgOpacityImage']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langAddOpacityImage
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='d-flex justify-content-center align-items-start flex-wrap gap-4'>
                                <figure class='figure'>
                                    <img src='$urlServer/template/modern/images/theme_settings/body_1.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                    <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                                </figure>
                                <figure class='figure'>
                                    <img src='$urlServer/template/modern/images/theme_settings/body_2.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                    <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                                </figure>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            <!-- HEADER SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsHeader'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langConfig (Header)</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorWrapperHeader' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                <input name='BgColorWrapperHeader' type='text' class='form-control colorpicker' id='BgColorWrapperHeader' value='$theme_options_styles[BgColorWrapperHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkColorHeader' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                                <input name='linkColorHeader' type='text' class='form-control colorpicker' id='linkColorHeader' value='$theme_options_styles[linkColorHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkHoverColorHeader' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
                                <input name='linkHoverColorHeader' type='text' class='form-control colorpicker' id='linkHoverColorHeader' value='$theme_options_styles[linkHoverColorHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkActiveBgColorHeader' class='control-label-notes mb-2 me-2'>$langActiveLinkBgColorHeader:</label>
                                <input name='linkActiveBgColorHeader' type='text' class='form-control colorpicker' id='linkActiveBgColorHeader' value='$theme_options_styles[linkActiveBgColorHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkActiveColorHeader' class='control-label-notes mb-2 me-2'>$langActiveLinkColorHeader:</label>
                                <input name='linkActiveColorHeader' type='text' class='form-control colorpicker' id='linkActiveColorHeader' value='$theme_options_styles[linkActiveColorHeader]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='HoveredActiveLinkColorHeader' class='control-label-notes mb-2 me-2'>$langHoveredActiveLinkColorHeader:</label>
                                <input name='HoveredActiveLinkColorHeader' type='text' class='form-control colorpicker' id='HoveredActiveLinkColorHeader' value='$theme_options_styles[HoveredActiveLinkColorHeader]'>
                            </div>
                            <hr>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langShadowHeader</h3>
                            <div class='form-group mt-2'>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                        <input type='checkbox' name='shadowHeader' value='1' ".((isset($theme_options_styles['shadowHeader']))? 'checked' : '').">
                                        <span class='checkmark'></span>
                                        $langDeactivate
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/header.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAIN SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsMainSection'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langConfig (Main)</h3>
                            <div class='form-group'>
                                <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                    <label for='bgColorContentPlatform' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                    <input name='bgColorContentPlatform' type='text' class='form-control colorpicker' id='bgColorContentPlatform' value='$theme_options_styles[bgColorContentPlatform]'>
                                </div>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/main.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsFooter'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langConfig (Footer)</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgColorWrapperFooter' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                <input name='bgColorWrapperFooter' type='text' class='form-control colorpicker' id='bgColorWrapperFooter' value='$theme_options_styles[bgColorWrapperFooter]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkColorFooter' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                                <input name='linkColorFooter' type='text' class='form-control colorpicker' id='linkColorFooter' value='$theme_options_styles[linkColorFooter]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkHoverColorFooter' class='control-label-notes mb-2 me-2'>$langHoverLinkColorFooter:</label>
                                <input name='linkHoverColorFooter' type='text' class='form-control colorpicker' id='linkHoverColorFooter' value='$theme_options_styles[linkHoverColorFooter]'>
                            </div>
                            <div class='form-group mt-4'>
                                <div class='col-sm-12 control-label-notes mb-2'>$langFooterUploadImage:</div>
                                <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                                    $image_footer_field
                                </div>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/footer.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BADGES SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsBadge'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline'>Badge Success</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgBadgeSuccess' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgeSuccess' type='text' class='form-control colorpicker' id='BgBadgeSuccess' value='$theme_options_styles[BgBadgeSuccess]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='clBadgeSuccess' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgeSuccess' type='text' class='form-control colorpicker' id='clBadgeSuccess' value='$theme_options_styles[clBadgeSuccess]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline'>Badge Warning</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgBadgeWarning' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgeWarning' type='text' class='form-control colorpicker' id='BgBadgeWarning' value='$theme_options_styles[BgBadgeWarning]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='clBadgeWarning' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgeWarning' type='text' class='form-control colorpicker' id='clBadgeWarning' value='$theme_options_styles[clBadgeWarning]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline'>Badge Neutral</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgBadgeNeutral' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgeNeutral' type='text' class='form-control colorpicker' id='BgBadgeNeutral' value='$theme_options_styles[BgBadgeNeutral]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='clBadgeNeutral' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgeNeutral' type='text' class='form-control colorpicker' id='clBadgeNeutral' value='$theme_options_styles[clBadgeNeutral]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline'>Badge Primary</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgBadgePrimary' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgePrimary' type='text' class='form-control colorpicker' id='BgBadgePrimary' value='$theme_options_styles[BgBadgePrimary]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='clBadgePrimary' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgePrimary' type='text' class='form-control colorpicker' id='clBadgePrimary' value='$theme_options_styles[clBadgePrimary]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline'>Badge Danger</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgBadgeAccent' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                        <input name='BgBadgeAccent' type='text' class='form-control colorpicker' id='BgBadgeAccent' value='$theme_options_styles[BgBadgeAccent]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='clBadgeAccent' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                        <input name='clBadgeAccent' type='text' class='form-control colorpicker' id='clBadgeAccent' value='$theme_options_styles[clBadgeAccent]'>
                    </div>
                </div>
            </div>

            <!-- SCROLLBAR SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsScrollBar'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langSettingsScrollBar</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgScrollBar' class='control-label-notes mb-2 me-2'>$BgScrollBar:</label>
                                <input name='BgScrollBar' type='text' class='form-control colorpicker' id='BgScrollBar' value='$theme_options_styles[BgScrollBar]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorScrollBar' class='control-label-notes mb-2 me-2'>$langBgColorScrollBar:</label>
                                <input name='BgColorScrollBar' type='text' class='form-control colorpicker' id='BgColorScrollBar' value='$theme_options_styles[BgColorScrollBar]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgHoveredColorScrollBar' class='control-label-notes mb-2 me-2'>$langBgHoveredColorScrollBar:</label>
                                <input name='BgHoveredColorScrollBar' type='text' class='form-control colorpicker' id='BgHoveredColorScrollBar' value='$theme_options_styles[BgHoveredColorScrollBar]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/scrollbar.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROGRESSBAR SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsProgressBar'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langSettingsProgressBar</h3>
                            <p>($langInfoProgressBar)</p>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BackProgressBar' class='control-label-notes mb-2 me-2'>$langBackProgressBar:</label>
                                <input name='BackProgressBar' type='text' class='form-control colorpicker' id='BackProgressBar' value='$theme_options_styles[BackProgressBar]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgProgressBar' class='control-label-notes mb-2 me-2'>$langBgProgressBar:</label>
                                <input name='BgProgressBar' type='text' class='form-control colorpicker' id='BgProgressBar' value='$theme_options_styles[BgProgressBar]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorProgressBarAndText' class='control-label-notes mb-2 me-2'>$langBgColorProgressBarAndText:</label>
                                <input name='BgColorProgressBarAndText' type='text' class='form-control colorpicker' id='BgColorProgressBarAndText' value='$theme_options_styles[BgColorProgressBarAndText]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/progressbar.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOOLTIP SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsTooltip'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langSettingsTooltip</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgColorTooltip' class='control-label-notes mb-2 me-2'>$langbgColorTooltip:</label>
                                <input name='bgColorTooltip' type='text' class='form-control colorpicker' id='bgColorTooltip' value='$theme_options_styles[bgColorTooltip]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorTooltip' class='control-label-notes mb-2 me-2'>$langTextColorTooltip:</label>
                                <input name='TextColorTooltip' type='text' class='form-control colorpicker' id='TextColorTooltip' value='$theme_options_styles[TextColorTooltip]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/tooltip.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ALERT SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsAlerts'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langSettingsAlertInfo</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAlertInfo' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                <input name='bgAlertInfo' type='text' class='form-control colorpicker' id='bgAlertInfo' value='$theme_options_styles[bgAlertInfo]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAlertInfo' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                                <input name='clAlertInfo' type='text' class='form-control colorpicker' id='clAlertInfo' value='$theme_options_styles[clAlertInfo]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkAlertInfo' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                                <input name='clLinkAlertInfo' type='text' class='form-control colorpicker' id='clLinkAlertInfo' value='$theme_options_styles[clLinkAlertInfo]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkHoveredAlertInfo' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
                                <input name='clLinkHoveredAlertInfo' type='text' class='form-control colorpicker' id='clLinkHoveredAlertInfo' value='$theme_options_styles[clLinkHoveredAlertInfo]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/alert_4.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-3'>$langSettingsAlertWarning</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAlertWarning' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                <input name='bgAlertWarning' type='text' class='form-control colorpicker' id='bgAlertWarning' value='$theme_options_styles[bgAlertWarning]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAlertWarning' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                                <input name='clAlertWarning' type='text' class='form-control colorpicker' id='clAlertWarning' value='$theme_options_styles[clAlertWarning]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkAlertWarning' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                                <input name='clLinkAlertWarning' type='text' class='form-control colorpicker' id='clLinkAlertWarning' value='$theme_options_styles[clLinkAlertWarning]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkHoveredAlertWarning' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
                                <input name='clLinkHoveredAlertWarning' type='text' class='form-control colorpicker' id='clLinkHoveredAlertWarning' value='$theme_options_styles[clLinkHoveredAlertWarning]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/alert_2.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-3'>$langSettingsAlertSuccess</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAlertSuccess' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                <input name='bgAlertSuccess' type='text' class='form-control colorpicker' id='bgAlertSuccess' value='$theme_options_styles[bgAlertSuccess]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAlertSuccess' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                                <input name='clAlertSuccess' type='text' class='form-control colorpicker' id='clAlertSuccess' value='$theme_options_styles[clAlertSuccess]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkAlertSuccess' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                                <input name='clLinkAlertSuccess' type='text' class='form-control colorpicker' id='clLinkAlertSuccess' value='$theme_options_styles[clLinkAlertSuccess]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkHoveredAlertSuccess' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
                                <input name='clLinkHoveredAlertSuccess' type='text' class='form-control colorpicker' id='clLinkHoveredAlertSuccess' value='$theme_options_styles[clLinkHoveredAlertSuccess]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/alert_1.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-3'>$langSettingsAlertDanger</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAlertDanger' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                <input name='bgAlertDanger' type='text' class='form-control colorpicker' id='bgAlertDanger' value='$theme_options_styles[bgAlertDanger]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAlertDanger' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                                <input name='clAlertDanger' type='text' class='form-control colorpicker' id='clAlertDanger' value='$theme_options_styles[clAlertDanger]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkAlertDanger' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                                <input name='clLinkAlertDanger' type='text' class='form-control colorpicker' id='clLinkAlertDanger' value='$theme_options_styles[clLinkAlertDanger]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkHoveredAlertDanger' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
                                <input name='clLinkHoveredAlertDanger' type='text' class='form-control colorpicker' id='clLinkHoveredAlertDanger' value='$theme_options_styles[clLinkHoveredAlertDanger]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/alert_5.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HOMEPAGE SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsLoginHomepage'>
                <div class='form-wrapper form-edit rounded'>
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                            <div>
                                <h3 class='theme_options_legend text-decoration-underline'>$langBasicOptions</h3>
                                <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                    <label for='loginJumbotronBgColor' class='control-label-notes mb-2 me-2'>$langLoginBgGradient (jumbotron):</label>
                                    <input name='loginJumbotronBgColor' type='text' class='form-control colorpicker' id='loginJumbotronBgColor' value='$theme_options_styles[loginJumbotronBgColor]'>
                                    <i class='fa fa-arrow-right ms-3 me-3'></i>
                                    <input aria-label='$langBgColor' name='loginJumbotronRadialBgColor' type='text' class='form-control colorpicker' id='loginJumbotronRadialBgColor' value='$theme_options_styles[loginJumbotronRadialBgColor]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12'>
                                    $login_image_field
                                    </div>
                                </div>



                                <div class='form-group mt-4'>
                                    <label for='maxHeightJumbotron' class='col-sm-6 control-label-notes mb-2'>$langMaxHeight (jumbotron):</label>
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
                                </div>



                                <div class='form-group mt-4'>
                                    <label for='loginTextColor' class='control-label-notes mb-2 me-2'>$langTextColor (jumbotron):</label>
                                    <input name='loginTextColor' type='text' class='form-control colorpicker' id='loginTextColor' value='$theme_options_styles[loginTextColor]'>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='maxWidthTextJumbotron' class='control-label-notes mb-2'>$langMaxWidthTextJumbotron (jumbotron):</label>
                                    <div class='col-sm-12'>
                                        <input id='maxWidthTextJumbotron' name='maxWidthTextJumbotron' data-slider-id='ex3Slider' type='text' data-slider-min='260' data-slider-max='1920' data-slider-step='10' data-slider-value='$theme_options_styles[maxWidthTextJumbotron]'>
                                        <span style='margin-left:10px;' id='pixelCounterWidthTextJumbotron'></span>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='loginTextBgColor' class='control-label-notes mb-2 me-2'>$langBgColor $langText (jumbotron):</label>
                                    <input name='loginTextBgColor' type='text' class='form-control colorpicker' id='loginTextBgColor' value='$theme_options_styles[loginTextBgColor]'>
                                </div>



                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>$langPositionJumbotronText (jumbotron):</div>
                                    <div class='radio mb-2'>
                                        <label>
                                            <input type='radio' name='PositionJumbotronText' value='0' ".(($theme_options_styles['PositionJumbotronText'] == '0')? 'checked' : '').">
                                            $langTopPositionJumbotronText 
                                        </label>
                                    </div>

                                    <div class='radio mb-2'>
                                        <label>
                                            <input type='radio' name='PositionJumbotronText' value='1' ".(($theme_options_styles['PositionJumbotronText'] == '1')? 'checked' : '').">
                                            $langCenterPositionJumbotronText 
                                        </label>
                                    </div>

                                    <div class='radio'>
                                        <label>
                                            <input type='radio' name='PositionJumbotronText' value='2' ".(($theme_options_styles['PositionJumbotronText'] == '2')? 'checked' : '').">
                                            $langBottomPositionJumbotronText
                                        </label>
                                    </div>
                                </div>





                                <div class='form-group mt-4'>
                                    <div class='col-sm-12'>
                                    $login_image_fieldL
                                    </div>
                                </div>";
                                $tool_content .= "<div class='form-group mt-4'>
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
                                </div>";
                                $tool_content .= "<div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-2'>$langLoginBanner:</div>
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
                            </div>
                            <div class='d-flex justify-content-center align-items-start'>
                                <figure class='figure'>
                                    <img src='$urlServer/template/modern/images/theme_settings/homepage_1.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                    <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                                </figure>
                            </div>
                        </div> 
                    </fieldset>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAnnouncements</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorAnnouncementHomepage' class='control-label-notes mb-2 me-2'>$langBgColor(Container) - linear gradient:</label>
                                <input name='BgColorAnnouncementHomepage' type='text' class='form-control colorpicker' id='BgColorAnnouncementHomepage' value='$theme_options_styles[BgColorAnnouncementHomepage]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BgColorAnnouncementHomepage_gr' type='text' class='form-control colorpicker' id='BgColorAnnouncementHomepage_gr' value='$theme_options_styles[BgColorAnnouncementHomepage_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorAnnouncementHomepage' class='control-label-notes mb-2 me-2'>$langTextColor(Container):</label>
                                <input name='TextColorAnnouncementHomepage' type='text' class='form-control colorpicker' id='TextColorAnnouncementHomepage' value='$theme_options_styles[TextColorAnnouncementHomepage]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorAnnouncementHomepageLink' class='control-label-notes mb-2 me-2'>$langBgColorListItem:</label>
                                <input name='BgColorAnnouncementHomepageLink' type='text' class='form-control colorpicker' id='BgColorAnnouncementHomepageLink' value='$theme_options_styles[BgColorAnnouncementHomepageLink]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderColorAnnouncementHomepageLink' class='control-label-notes mb-2 me-2'>$langBgBorderColorListItem:</label>
                                <input name='BgBorderColorAnnouncementHomepageLink' type='text' class='form-control colorpicker' id='BgBorderColorAnnouncementHomepageLink' value='$theme_options_styles[BgBorderColorAnnouncementHomepageLink]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clColorAnnouncementHomepageLinkElement' class='control-label-notes mb-2 me-2'>$langclLists:</label>
                                <input name='clColorAnnouncementHomepageLinkElement' type='text' class='form-control colorpicker' id='clColorAnnouncementHomepageLinkElement' value='$theme_options_styles[clColorAnnouncementHomepageLinkElement]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredColorAnnouncementHomepageLinkElement' class='control-label-notes mb-2 me-2'>$langclHoveredLists:</label>
                                <input name='clHoveredColorAnnouncementHomepageLinkElement' type='text' class='form-control colorpicker' id='clHoveredColorAnnouncementHomepageLinkElement' value='$theme_options_styles[clHoveredColorAnnouncementHomepageLinkElement]'>
                            </div>
                            <p class='control-label-notes mt-4'>$langAddPaddingListGroup:</p>
                            <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label class='label-container' aria-label='$langSettingSelect'>
                                        <input type='checkbox' name='AddPaddingAnnouncementsListGroup' value='1' ".((isset($theme_options_styles['AddPaddingAnnouncementsListGroup']))? 'checked' : '').">
                                        <span class='checkmark'></span>
                                        $langActivate
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/homepage_2.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langVisitsStats</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorStatisticsHomepage' class='control-label-notes mb-2 me-2'>$langBgColor(Container) - linear gradient:</label>
                                <input name='BgColorStatisticsHomepage' type='text' class='form-control colorpicker' id='BgColorStatisticsHomepage' value='$theme_options_styles[BgColorStatisticsHomepage]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BgColorStatisticsHomepage_gr' type='text' class='form-control colorpicker' id='BgColorStatisticsHomepage_gr' value='$theme_options_styles[BgColorStatisticsHomepage_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorStatisticsHomepage' class='control-label-notes mb-2 me-2'>$langTextColor(Container):</label>
                                <input name='TextColorStatisticsHomepage' type='text' class='form-control colorpicker' id='TextColorStatisticsHomepage' value='$theme_options_styles[TextColorStatisticsHomepage]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langPopularCourse</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorPopularCoursesHomepage' class='control-label-notes mb-2 me-2'>$langBgColor(Container) - linear gradient:</label>
                                <input name='BgColorPopularCoursesHomepage' type='text' class='form-control colorpicker' id='BgColorPopularCoursesHomepage' value='$theme_options_styles[BgColorPopularCoursesHomepage]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BgColorPopularCoursesHomepage_gr' type='text' class='form-control colorpicker' id='BgColorPopularCoursesHomepage_gr' value='$theme_options_styles[BgColorPopularCoursesHomepage_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorPopularCoursesHomepage' class='control-label-notes mb-2 me-2'>$langTextColor(Container):</label>
                                <input name='TextColorPopularCoursesHomepage' type='text' class='form-control colorpicker' id='TextColorPopularCoursesHomepage' value='$theme_options_styles[TextColorPopularCoursesHomepage]'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langHomepageTexts</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorTextsHomepage' class='control-label-notes mb-2 me-2'>$langBgColor(Container) - linear gradient:</label>
                                <input name='BgColorTextsHomepage' type='text' class='form-control colorpicker' id='BgColorTextsHomepage' value='$theme_options_styles[BgColorTextsHomepage]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BgColorTextsHomepage_gr' type='text' class='form-control colorpicker' id='BgColorTextsHomepage_gr' value='$theme_options_styles[BgColorTextsHomepage_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorTextsHomepage' class='control-label-notes mb-2 me-2'>$langTextColor(Container):</label>
                                <input name='TextColorTextsHomepage' type='text' class='form-control colorpicker' id='TextColorTextsHomepage' value='$theme_options_styles[TextColorTextsHomepage]'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COURSEHOME SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettings'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langBgColorConfigRightColumn</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='RightColumnCourseBgColor' class='control-label-notes me-2 mb-2'>$langBgColor:</label>
                                <input name='RightColumnCourseBgColor' type='text' class='form-control colorpicker' id='RightColumnCourseBgColor' value='$theme_options_styles[RightColumnCourseBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='RightColumnCourseBgImage' class='control-label-notes me-2 mb-2'>$langBgImg:</label>
                                $RightColumnCourseBgImage
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BorderLeftToRightColumnCourseBgColor' class='control-label-notes me-2 mb-2'>$langBgBorderLeftColor:</label>
                                <input name='BorderLeftToRightColumnCourseBgColor' type='text' class='form-control colorpicker' id='BorderLeftToRightColumnCourseBgColor' value='$theme_options_styles[BorderLeftToRightColumnCourseBgColor]'>
                            </div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langBgColorConfig $langHelpCourseUI</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftNavBgColor' class='control-label-notes me-2 mb-2'>$langBgColor:</label>
                                <input name='leftNavBgColor' type='text' class='form-control colorpicker' id='leftNavBgColor' value='$theme_options_styles[leftNavBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftNavBgColorSmallScreen' class='control-label-notes me-2 mb-2'>$langBgColor <small>$langLogoSmall</small>:</label>
                                <input name='leftNavBgColorSmallScreen' type='text' class='form-control colorpicker' id='leftNavBgColorSmallScreen' value='$theme_options_styles[leftNavBgColorSmallScreen]'>
                            </div>
                            <hr>
                            <h3 class='theme_options_legend text-decoration-underline mt-2'>$langMainMenuConfiguration $langHelpCourseUI</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftMenuFontColor' class='control-label-notes mb-2 me-2'>$langMainMenuLinkColor:</label>
                                <input name='leftMenuFontColor' type='text' class='form-control colorpicker' id='leftMenuFontColor' value='$theme_options_styles[leftMenuFontColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftMenuHoverFontColor' class='control-label-notes mb-2 me-2'>$langMainMenuLinkHoverColor:</label>
                                <input name='leftMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftMenuHoverFontColor' value='$theme_options_styles[leftMenuHoverFontColor]'>
                            </div>
                            <hr>
                            <h3 class='theme_options_legend text-decoration-underline mt-2'>$langSubMenuConfig $langHelpCourseUI</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftSubMenuFontColor' class='control-label-notes mb-2 me-2'>$langSubMenuLinkColor:</label>
                                <input name='leftSubMenuFontColor' type='text' class='form-control colorpicker' id='leftSubMenuFontColor' value='$theme_options_styles[leftSubMenuFontColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftSubMenuHoverFontColor' class='control-label-notes mb-2 me-2'>$langSubMenuLinkHoverColor:</label>
                                <input name='leftSubMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverFontColor' value='$theme_options_styles[leftSubMenuHoverFontColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftSubMenuHoverBgColor' class='control-label-notes mb-2 me-2'>$langSubMenuLinkBgHoverColor:</label>
                                <input name='leftSubMenuHoverBgColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverBgColor' value='$theme_options_styles[leftSubMenuHoverBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftMenuSelectedBgColor' class='control-label-notes mb-2 me-2'>$langSubMenuLinkBgActive:</label>
                                <input name='leftMenuSelectedBgColor' type='text' class='form-control colorpicker' id='leftMenuSelectedBgColor' value='$theme_options_styles[leftMenuSelectedBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='leftMenuSelectedLinkColor' class='control-label-notes mb-2 me-2'>$langSubMenuLinkColorActive:</label>
                                <input name='leftMenuSelectedLinkColor' type='text' class='form-control colorpicker' id='leftMenuSelectedLinkColor' value='$theme_options_styles[leftMenuSelectedLinkColor]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/coursehome.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BUTTON SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navButtons'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langButtonsColorCongiguration</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='buttonBgColor' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                <input name='buttonBgColor' type='text' class='form-control colorpicker' id='buttonBgColor' value='$theme_options_styles[buttonBgColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='buttonTextColor' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                                <input name='buttonTextColor' type='text' class='form-control colorpicker' id='buttonTextColor' value='$theme_options_styles[buttonTextColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='buttonHoverBgColor' class='control-label-notes mb-2 me-2'>$langHoverWhiteColorButton:</label>
                                <input name='buttonHoverBgColor' type='text' class='form-control colorpicker' id='buttonHoverBgColor' value='$theme_options_styles[buttonHoverBgColor]'>
                            </div>
                            <hr>
                            <h3 class='theme_options_legend text-decoration-underline mt-2'>$langButtonsColorWhiteCongiguration</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgWhiteButtonColor' class='control-label-notes mb-2 me-2'>$langButtonColorWhiteCongiguration:</label>
                                <input name='bgWhiteButtonColor' type='text' class='form-control colorpicker' id='bgWhiteButtonColor' value='$theme_options_styles[bgWhiteButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='whiteButtonTextColor' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                                <input name='whiteButtonTextColor' type='text' class='form-control colorpicker' id='whiteButtonTextColor' value='$theme_options_styles[whiteButtonTextColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='whiteButtonBorderTextColor' class='control-label-notes mb-2 me-2'>$langBorderTextColor:</label>
                                <input name='whiteButtonBorderTextColor' type='text' class='form-control colorpicker' id='whiteButtonBorderTextColor' value='$theme_options_styles[whiteButtonBorderTextColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='whiteButtonHoveredTextColor' class='control-label-notes mb-2 me-2'>$langHoverTextColor:</label>
                                <input name='whiteButtonHoveredTextColor' type='text' class='form-control colorpicker' id='whiteButtonHoveredTextColor' value='$theme_options_styles[whiteButtonHoveredTextColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='whiteButtonHoveredBorderTextColor' class='control-label-notes mb-2 me-2'>$langHoverBorderTextColor:</label>
                                <input name='whiteButtonHoveredBorderTextColor' type='text' class='form-control colorpicker' id='whiteButtonHoveredBorderTextColor' value='$theme_options_styles[whiteButtonHoveredBorderTextColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='whiteButtonHoveredBgColor' class='control-label-notes mb-2 me-2'>$langHoverWhiteColorButton:</label>
                                <input name='whiteButtonHoveredBgColor' type='text' class='form-control colorpicker' id='whiteButtonHoveredBgColor' value='$theme_options_styles[whiteButtonHoveredBgColor]'>
                            </div>
                            <hr>
                            <h3 class='theme_options_legend text-decoration-underline mt-2'>$langButtonsColorDel</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgDeleteButtonColor' class='control-label-notes mb-2 me-2'>$langbgDeleteButtonColor:</label>
                                <input name='bgDeleteButtonColor' type='text' class='form-control colorpicker' id='bgDeleteButtonColor' value='$theme_options_styles[bgDeleteButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clDeleteButtonColor' class='control-label-notes mb-2 me-2'>$langclDeleteButtonColor:</label>
                                <input name='clDeleteButtonColor' type='text' class='form-control colorpicker' id='clDeleteButtonColor' value='$theme_options_styles[clDeleteButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgHoveredDeleteButtonColor' class='control-label-notes mb-2 me-2'>$langbgHoveredDeleteButtonColor:</label>
                                <input name='bgHoveredDeleteButtonColor' type='text' class='form-control colorpicker' id='bgHoveredDeleteButtonColor' value='$theme_options_styles[bgHoveredDeleteButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredDeleteButtonColor' class='control-label-notes mb-2 me-2'>$langclHoveredDeleteButtonColor:</label>
                                <input name='clHoveredDeleteButtonColor' type='text' class='form-control colorpicker' id='clHoveredDeleteButtonColor' value='$theme_options_styles[clHoveredDeleteButtonColor]'>
                            </div>
                            <hr>
                            <h3 class='theme_options_legend text-decoration-underline mt-2'>$langButtonsColorSuccess</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgSuccessButtonColor' class='control-label-notes mb-2 me-2'>$langbgSuccessButtonColor:</label>
                                <input name='bgSuccessButtonColor' type='text' class='form-control colorpicker' id='bgSuccessButtonColor' value='$theme_options_styles[bgSuccessButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clSuccessButtonColor' class='control-label-notes mb-2 me-2'>$langclSuccessButtonColor:</label>
                                <input name='clSuccessButtonColor' type='text' class='form-control colorpicker' id='clSuccessButtonColor' value='$theme_options_styles[clSuccessButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgHoveredSuccessButtonColor' class='control-label-notes mb-2 me-2'>$langbgHoveredSuccessButtonColor:</label>
                                <input name='bgHoveredSuccessButtonColor' type='text' class='form-control colorpicker' id='bgHoveredSuccessButtonColor' value='$theme_options_styles[bgHoveredSuccessButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredSuccessButtonColor' class='control-label-notes mb-2 me-2'>$langclHoveredSuccessButtonColor:</label>
                                <input name='clHoveredSuccessButtonColor' type='text' class='form-control colorpicker' id='clHoveredSuccessButtonColor' value='$theme_options_styles[clHoveredSuccessButtonColor]'>
                            </div>
                            <hr>
                            <h3 class='theme_options_legend text-decoration-underline mt-2'>$langButtonsColorHelp</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgHelpButtonColor' class='control-label-notes mb-2 me-2'>$langbgHelpButtonColor:</label>
                                <input name='bgHelpButtonColor' type='text' class='form-control colorpicker' id='bgHelpButtonColor' value='$theme_options_styles[bgHelpButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHelpButtonColor' class='control-label-notes mb-2 me-2'>$langclHelpButtonColor:</label>
                                <input name='clHelpButtonColor' type='text' class='form-control colorpicker' id='clHelpButtonColor' value='$theme_options_styles[clHelpButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgHoveredHelpButtonColor' class='control-label-notes mb-2 me-2'>$langbgHoveredHelpButtonColor:</label>
                                <input name='bgHoveredHelpButtonColor' type='text' class='form-control colorpicker' id='bgHoveredHelpButtonColor' value='$theme_options_styles[bgHoveredHelpButtonColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredHelpButtonColor' class='control-label-notes mb-2 me-2'>$langclHoveredHelpButtonColor:</label>
                                <input name='clHoveredHelpButtonColor' type='text' class='form-control colorpicker' id='clHoveredHelpButtonColor' value='$theme_options_styles[clHoveredHelpButtonColor]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/button.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LINKS SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navLinks'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langLinksCongiguration</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkColor' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                                <input name='linkColor' type='text' class='form-control colorpicker' id='linkColor' value='$theme_options_styles[linkColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkHoverColor' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
                                <input name='linkHoverColor' type='text' class='form-control colorpicker' id='linkHoverColor' value='$theme_options_styles[linkHoverColor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='linkDeleteColor' class='control-label-notes mb-2 me-2'>$langDeleteLinkColor:</label>
                                <input name='linkDeleteColor' type='text' class='form-control colorpicker' id='linkDeleteColor' value='$theme_options_styles[linkDeleteColor]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/link.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PORTFOLIO SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navcontainer'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langPortFolioProfileContainer</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BriefProfilePortfolioBgColor' class='control-label-notes mb-2 me-2'>$langPortFolioProfileContainer - radial gradient:</label>
                                <input name='BriefProfilePortfolioBgColor' type='text' class='form-control colorpicker' id='BriefProfilePortfolioBgColor' value='$theme_options_styles[BriefProfilePortfolioBgColor]'>
                                <i class='fa fa-arrow-right ms-3 me-3'></i>
                                <input aria-label='$langBgColor' name='BriefProfilePortfolioBgColor_gr' type='text' class='form-control colorpicker' id='BriefProfilePortfolioBgColor_gr' value='$theme_options_styles[BriefProfilePortfolioBgColor_gr]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BriefProfilePortfolioTextColor' class='control-label-notes mb-2 me-2'>$langBriefProfilePortfolioTextColor:</label>
                                <input name='BriefProfilePortfolioTextColor' type='text' class='form-control colorpicker' id='BriefProfilePortfolioTextColor' value='$theme_options_styles[BriefProfilePortfolioTextColor]'>
                            </div>
                            <hr>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langPortfolioCoursesContainer</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorWrapperPortfolioCourses' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                                <input name='BgColorWrapperPortfolioCourses' type='text' class='form-control colorpicker' id='BgColorWrapperPortfolioCourses' value='$theme_options_styles[BgColorWrapperPortfolioCourses]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/portfolio.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TEXT SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navHyperTexts'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langPHyperTextColor</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ColorHyperTexts' class='control-label-notes mb-2 me-2'>$langPHyperTextColor:</label>
                                <input name='ColorHyperTexts' type='text' class='form-control colorpicker' id='ColorHyperTexts' value='$theme_options_styles[ColorHyperTexts]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ColorRedText' class='control-label-notes mb-2 me-2'>$langRedText:</label>
                                <input name='ColorRedText' type='text' class='form-control colorpicker' id='ColorRedText' value='$theme_options_styles[ColorRedText]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ColorGreenText' class='control-label-notes mb-2 me-2'>$langGreenText:</label>
                                <input name='ColorGreenText' type='text' class='form-control colorpicker' id='ColorGreenText' value='$theme_options_styles[ColorGreenText]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ColorBlueText' class='control-label-notes mb-2 me-2'>$langBlueText:</label>
                                <input name='ColorBlueText' type='text' class='form-control colorpicker' id='ColorBlueText' value='$theme_options_styles[ColorBlueText]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ColorOrangeText' class='control-label-notes mb-2 me-2'>$langOrangeText:</label>
                                <input name='ColorOrangeText' type='text' class='form-control colorpicker' id='ColorOrangeText' value='$theme_options_styles[ColorOrangeText]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/text.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navPanels'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langConcerngingPanels</h3>
                            <div class='form-group d-flex justify-content-start align-items-center'>
                                <label for='BgPanels' class='control-label-notes mb-2 me-2'>$langBgPanels:</label>
                                <input name='BgPanels' type='text' class='form-control colorpicker' id='BgPanels' value='$theme_options_styles[BgPanels]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderPanels' class='control-label-notes mb-2 me-2'>$langclBorderPanels:</label>
                                <input name='clBorderPanels' type='text' class='form-control colorpicker' id='clBorderPanels' value='$theme_options_styles[clBorderPanels]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgBorderHoveredPanels' class='control-label-notes mb-2 me-2'>$langBgHoveredPanels:</label>
                                <input name='bgBorderHoveredPanels' type='text' class='form-control colorpicker' id='bgBorderHoveredPanels' value='$theme_options_styles[bgBorderHoveredPanels]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredTextPanels' class='control-label-notes mb-2 me-2'>$langHoverTextColor:</label>
                                <input name='clHoveredTextPanels' type='text' class='form-control colorpicker' id='clHoveredTextPanels' value='$theme_options_styles[clHoveredTextPanels]'>
                            </div>
                             <div class='form-group d-flex justify-content-start align-items-center mt-4'>
                                <label for='bgHoveredBoxShadowPanels' class='control-label-notes mb-2 me-2'>$langHoveredBoxShadowPanels:</label>
                                <input name='bgHoveredBoxShadowPanels' type='text' class='form-control colorpicker' id='bgHoveredBoxShadowPanels' value='$theme_options_styles[bgHoveredBoxShadowPanels]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/card_1.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langConcerngingCommentsPanels</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgCommentsPanels' class='control-label-notes mb-2 me-2'>$langBgPanels:</label>
                                <input name='BgCommentsPanels' type='text' class='form-control colorpicker' id='BgCommentsPanels' value='$theme_options_styles[BgCommentsPanels]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderBgCommentsPanels' class='control-label-notes mb-2 me-2'>$langclBorderPanels:</label>
                                <input name='clBorderBgCommentsPanels' type='text' class='form-control colorpicker' id='clBorderBgCommentsPanels' value='$theme_options_styles[clBorderBgCommentsPanels]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/card_3.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langConcerngingQuestionnairePanels</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgQuestionnairePanels' class='control-label-notes mb-2 me-2'>$langBgPanels:</label>
                                <input name='BgQuestionnairePanels' type='text' class='form-control colorpicker' id='BgQuestionnairePanels' value='$theme_options_styles[BgQuestionnairePanels]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderQuestionnairePanels' class='control-label-notes mb-2 me-2'>$langclBorderPanels:</label>
                                <input name='clBorderQuestionnairePanels' type='text' class='form-control colorpicker' id='clBorderQuestionnairePanels' value='$theme_options_styles[clBorderQuestionnairePanels]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/card_2.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langConcerngingExercisePanels</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgExercisesPanels' class='control-label-notes mb-2 me-2'>$langBgPanels:</label>
                                <input name='BgExercisesPanels' type='text' class='form-control colorpicker' id='BgExercisesPanels' value='$theme_options_styles[BgExercisesPanels]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderExercisesPanels' class='control-label-notes mb-2 me-2'>$langclBorderPanels:</label>
                                <input name='clBorderExercisesPanels' type='text' class='form-control colorpicker' id='clBorderExercisesPanels' value='$theme_options_styles[clBorderExercisesPanels]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/card_4.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langConcerngingReportsPanels</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgReportsPanels' class='control-label-notes mb-2 me-2'>$langBgPanels:</label>
                                <input name='BgReportsPanels' type='text' class='form-control colorpicker' id='BgReportsPanels' value='$theme_options_styles[BgReportsPanels]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderReportsPanels' class='control-label-notes mb-2 me-2'>$langclBorderPanels:</label>
                                <input name='clBorderReportsPanels' type='text' class='form-control colorpicker' id='clBorderReportsPanels' value='$theme_options_styles[clBorderReportsPanels]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/card_5.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langBoxShadowPanels</h3>
                            <div class='form-group d-flex justify-content-start align-items-center mt-4'>
                                <label for='BoxShadowPanels' class='control-label-notes mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='BoxShadowPanels' type='text' class='form-control colorpicker' id='BoxShadowPanels' value='$theme_options_styles[BoxShadowPanels]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start gap-3 flex-wrap'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/card_6.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RADIO SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navRadios'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langRadios</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgRadios' class='control-label-notes mb-2 me-2'>$langBgRadios:</label>
                                <input name='BgRadios' type='text' class='form-control colorpicker' id='BgRadios' value='$theme_options_styles[BgRadios]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderRadios' class='control-label-notes mb-2 me-2'>$langBgBorderRadios:</label>
                                <input name='BgBorderRadios' type='text' class='form-control colorpicker' id='BgBorderRadios' value='$theme_options_styles[BgBorderRadios]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ClRadios' class='control-label-notes mb-2 me-2'>$langClRadios:</label>
                                <input name='ClRadios' type='text' class='form-control colorpicker' id='ClRadios' value='$theme_options_styles[ClRadios]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgClRadios' class='control-label-notes mb-2 me-2'>$langBgClRadios:</label>
                                <input name='BgClRadios' type='text' class='form-control colorpicker' id='BgClRadios' value='$theme_options_styles[BgClRadios]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ClIconRadios' class='control-label-notes mb-2 me-2'>$langClIconRadios:</label>
                                <input name='ClIconRadios' type='text' class='form-control colorpicker' id='ClIconRadios' value='$theme_options_styles[ClIconRadios]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ClInactiveRadios' class='control-label-notes mb-2 me-2'>$langClInactiveRadios:</label>
                                <input name='ClInactiveRadios' type='text' class='form-control colorpicker' id='ClInactiveRadios' value='$theme_options_styles[ClInactiveRadios]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/radio.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CHECKBOX SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navCheckboxes'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langCheckboxes</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgCheckboxes' class='control-label-notes mb-2 me-2'>$langBgCheckboxes:</label>
                                <input name='BgCheckboxes' type='text' class='form-control colorpicker' id='BgCheckboxes' value='$theme_options_styles[BgCheckboxes]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderCheckboxes' class='control-label-notes mb-2 me-2'>$langBgBorderCheckboxes:</label>
                                <input name='BgBorderCheckboxes' type='text' class='form-control colorpicker' id='BgBorderCheckboxes' value='$theme_options_styles[BgBorderCheckboxes]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ClCheckboxes' class='control-label-notes mb-2 me-2'>$langClCheckboxes:</label>
                                <input name='ClCheckboxes' type='text' class='form-control colorpicker' id='ClCheckboxes' value='$theme_options_styles[ClCheckboxes]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgActiveCheckboxes' class='control-label-notes mb-2 me-2'>$langBgActiveCheckboxes:</label>
                                <input name='BgActiveCheckboxes' type='text' class='form-control colorpicker' id='BgActiveCheckboxes' value='$theme_options_styles[BgActiveCheckboxes]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ClActiveCheckboxes' class='control-label-notes mb-2 me-2'>$langClActiveCheckboxes:</label>
                                <input name='ClActiveCheckboxes' type='text' class='form-control colorpicker' id='ClActiveCheckboxes' value='$theme_options_styles[ClActiveCheckboxes]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ClIconCheckboxes' class='control-label-notes mb-2 me-2'>$langClIconCheckboxes:</label>
                                <input name='ClIconCheckboxes' type='text' class='form-control colorpicker' id='ClIconCheckboxes' value='$theme_options_styles[ClIconCheckboxes]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ClInactiveCheckboxes' class='control-label-notes mb-2 me-2'>$langClInactiveCheckboxes:</label>
                                <input name='ClInactiveCheckboxes' type='text' class='form-control colorpicker' id='ClInactiveCheckboxes' value='$theme_options_styles[ClInactiveCheckboxes]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/checkbox.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TEXT EDITOR SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navTextEditor'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langInputTextEditor</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgTextEditor' class='control-label-notes mb-2 me-2'>$langBgTextEditor:</label>
                                <input name='BgTextEditor' type='text' class='form-control colorpicker' id='BgTextEditor' value='$theme_options_styles[BgTextEditor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderTextEditor' class='control-label-notes mb-2 me-2'>$langBgBorderTextEditor:</label>
                                <input name='BgBorderTextEditor' type='text' class='form-control colorpicker' id='BgBorderTextEditor' value='$theme_options_styles[BgBorderTextEditor]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='ClTextEditor' class='control-label-notes mb-2 me-2'>$langClTextEditor:</label>
                                <input name='ClTextEditor' type='text' class='form-control colorpicker' id='ClTextEditor' value='$theme_options_styles[ClTextEditor]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/text_editor.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AGENDA SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navsettingsAgenda'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langAgendaSettings</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgAgenda' class='control-label-notes mb-2 me-2'>$langBgColorAgenda:</label>
                                <input name='bgAgenda' type='text' class='form-control colorpicker' id='bgAgenda' value='$theme_options_styles[bgAgenda]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderColorAgenda' class='control-label-notes mb-2 me-2'>$langBgBorderColorAgenda:</label>
                                <input name='BgBorderColorAgenda' type='text' class='form-control colorpicker' id='BgBorderColorAgenda' value='$theme_options_styles[BgBorderColorAgenda]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderColorAgendaEvent' class='control-label-notes mb-2 me-2'>$langBgBorderColorAgendaEvent:</label>
                                <input name='BgBorderColorAgendaEvent' type='text' class='form-control colorpicker' id='BgBorderColorAgendaEvent' value='$theme_options_styles[BgBorderColorAgendaEvent]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgColorHeaderAgenda' class='control-label-notes mb-2 me-2'>$langBgColorHeaderAgenda:</label>
                                <input name='BgColorHeaderAgenda' type='text' class='form-control colorpicker' id='BgColorHeaderAgenda' value='$theme_options_styles[BgColorHeaderAgenda]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clColorHeaderAgenda' class='control-label-notes mb-2 me-2'>$langclColorHeaderAgenda:</label>
                                <input name='clColorHeaderAgenda' type='text' class='form-control colorpicker' id='clColorHeaderAgenda' value='$theme_options_styles[clColorHeaderAgenda]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clColorBodyAgenda' class='control-label-notes mb-2 me-2'>$langclColorBodyAgenda:</label>
                                <input name='clColorBodyAgenda' type='text' class='form-control colorpicker' id='clColorBodyAgenda' value='$theme_options_styles[clColorBodyAgenda]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgColorHoveredBodyAgenda' class='control-label-notes mb-2 me-2'>$langbgColorHoveredBodyAgenda:</label>
                                <input name='bgColorHoveredBodyAgenda' type='text' class='form-control colorpicker' id='bgColorHoveredBodyAgenda' value='$theme_options_styles[bgColorHoveredBodyAgenda]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clColorHoveredBodyAgenda' class='control-label-notes mb-2 me-2'>$langclColorHoveredBodyAgenda:</label>
                                <input name='clColorHoveredBodyAgenda' type='text' class='form-control colorpicker' id='clColorHoveredBodyAgenda' value='$theme_options_styles[clColorHoveredBodyAgenda]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgColorActiveDateTime' class='control-label-notes mb-2 me-2'>$langbgColorActiveDateTime:</label>
                                <input name='bgColorActiveDateTime' type='text' class='form-control colorpicker' id='bgColorActiveDateTime' value='$theme_options_styles[bgColorActiveDateTime]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgColorDeactiveDateTime' class='control-label-notes mb-2 me-2'>$langbgColorDeactiveDateTime:</label>
                                <input name='bgColorDeactiveDateTime' type='text' class='form-control colorpicker' id='bgColorDeactiveDateTime' value='$theme_options_styles[bgColorDeactiveDateTime]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='TextColorActiveDateTime' class='control-label-notes mb-2 me-2'>$langtextColorActiveDateTime:</label>
                                <input name='TextColorActiveDateTime' type='text' class='form-control colorpicker' id='TextColorActiveDateTime' value='$theme_options_styles[TextColorActiveDateTime]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgPanelEvents' class='control-label-notes mb-2 me-2'>$langbgPanelEvents:</label>
                                <input name='bgPanelEvents' type='text' class='form-control colorpicker' id='bgPanelEvents' value='$theme_options_styles[bgPanelEvents]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/agenda.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MENU POPOVER SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navMenuPopover'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langMenuPopover</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgMenuPopover' class='control-label-notes mb-2 me-2'>$langBgMenuPopover:</label>
                                <input name='BgMenuPopover' type='text' class='form-control colorpicker' id='BgMenuPopover' value='$theme_options_styles[BgMenuPopover]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderMenuPopover' class='control-label-notes mb-2 me-2'>$langBgBorderMenuPopover:</label>
                                <input name='BgBorderMenuPopover' type='text' class='form-control colorpicker' id='BgBorderMenuPopover' value='$theme_options_styles[BgBorderMenuPopover]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgMenuPopoverOption' class='control-label-notes mb-2 me-2'>$langBgMenuPopoverOption:</label>
                                <input name='BgMenuPopoverOption' type='text' class='form-control colorpicker' id='BgMenuPopoverOption' value='$theme_options_styles[BgMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clMenuPopoverOption' class='control-label-notes mb-2 me-2'>$langclMenuPopoverOption:</label>
                                <input name='clMenuPopoverOption' type='text' class='form-control colorpicker' id='clMenuPopoverOption' value='$theme_options_styles[clMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderBottomMenuPopoverOption' class='control-label-notes mb-2 me-2'>$langclBorderBottomMenuPopoverOption:</label>
                                <input name='clBorderBottomMenuPopoverOption' type='text' class='form-control colorpicker' id='clBorderBottomMenuPopoverOption' value='$theme_options_styles[clBorderBottomMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgHoveredMenuPopoverOption' class='control-label-notes mb-2 me-2'>$langBgHoveredMenuPopoverOption:</label>
                                <input name='BgHoveredMenuPopoverOption' type='text' class='form-control colorpicker' id='BgHoveredMenuPopoverOption' value='$theme_options_styles[BgHoveredMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredMenuPopoverOption' class='control-label-notes mb-2 me-2'>$langclHoveredMenuPopoverOption:</label>
                                <input name='clHoveredMenuPopoverOption' type='text' class='form-control colorpicker' id='clHoveredMenuPopoverOption' value='$theme_options_styles[clHoveredMenuPopoverOption]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clDeleteMenuPopoverOption' class='control-label-notes mb-2 me-2'>$langclDeleteMenuPopoverOption:</label>
                                <input name='clDeleteMenuPopoverOption' type='text' class='form-control colorpicker' id='clDeleteMenuPopoverOption' value='$theme_options_styles[clDeleteMenuPopoverOption]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/menu_popover.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navForms'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langForms</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgForms' class='control-label-notes mb-2 me-2'>$langBgForms:</label>
                                <input name='BgForms' type='text' class='form-control colorpicker' id='BgForms' value='$theme_options_styles[BgForms]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderForms' class='control-label-notes mb-2 me-2'>$langclBorderPanels:</label>
                                <input name='BgBorderForms' type='text' class='form-control colorpicker' id='BgBorderForms' value='$theme_options_styles[BgBorderForms]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='FormsBoxShadow' class='control-label-notes mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='FormsBoxShadow' type='text' class='form-control colorpicker' id='FormsBoxShadow' value='$theme_options_styles[FormsBoxShadow]'>
                            </div>
                            <p class='control-label-notes mt-4'>$langAddPadding:</p>
                            <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label class='label-container' aria-label='$langSettingSelect'>
                                        <input type='checkbox' name='AddPaddingFormWrapper' value='1' ".((isset($theme_options_styles['AddPaddingFormWrapper']))? 'checked' : '').">
                                        <span class='checkmark'></span>
                                        $langActivate
                                    </label>
                                </div>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLabelForms' class='control-label-notes mb-2 me-2'>$langColorLabel:</label>
                                <input name='clLabelForms' type='text' class='form-control colorpicker' id='clLabelForms' value='$theme_options_styles[clLabelForms]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clRequiredFieldForm' class='control-label-notes mb-2 me-2'>$langColorRequiredField:</label>
                                <input name='clRequiredFieldForm' type='text' class='form-control colorpicker' id='clRequiredFieldForm' value='$theme_options_styles[clRequiredFieldForm]'>
                            </div>
                            <div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    $form_image_fieldL
                                </div>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/form_1.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAboutRegistrationImageUpload</h3>
                            <div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    $registration_image_fieldL
                                </div>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/form_2.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INPUT SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navInputText'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langInputText</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgInput' class='control-label-notes mb-2 me-2'>$langBgInput:</label>
                                <input name='BgInput' type='text' class='form-control colorpicker' id='BgInput' value='$theme_options_styles[BgInput]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderInput' class='control-label-notes mb-2 me-2'>$langclBorderInput:</label>
                                <input name='clBorderInput' type='text' class='form-control colorpicker' id='clBorderInput' value='$theme_options_styles[clBorderInput]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clInputText' class='control-label-notes mb-2 me-2'>$langclInputText:</label>
                                <input name='clInputText' type='text' class='form-control colorpicker' id='clInputText' value='$theme_options_styles[clInputText]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/input.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SELECT SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navSelect'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langSettingSelect</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgSelect' class='control-label-notes mb-2 me-2'>$langBgSelect:</label>
                                <input name='BgSelect' type='text' class='form-control colorpicker' id='BgSelect' value='$theme_options_styles[BgSelect]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderSelect' class='control-label-notes mb-2 me-2'>$langclBorderSelect:</label>
                                <input name='clBorderSelect' type='text' class='form-control colorpicker' id='clBorderSelect' value='$theme_options_styles[clBorderSelect]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clOptionSelect' class='control-label-notes mb-2 me-2'>$langclOptionSelect:</label>
                                <input name='clOptionSelect' type='text' class='form-control colorpicker' id='clOptionSelect' value='$theme_options_styles[clOptionSelect]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgHoveredSelectOption' class='control-label-notes mb-2 me-2'>$langbgHoveredSelectOption:</label>
                                <input name='bgHoveredSelectOption' type='text' class='form-control colorpicker' id='bgHoveredSelectOption' value='$theme_options_styles[bgHoveredSelectOption]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredSelectOption' class='control-label-notes mb-2 me-2'>$langclHoveredSelectOption:</label>
                                <input name='clHoveredSelectOption' type='text' class='form-control colorpicker' id='clHoveredSelectOption' value='$theme_options_styles[clHoveredSelectOption]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgOptionSelected' class='control-label-notes mb-2 me-2'>$langbgOptionSelected:</label>
                                <input name='bgOptionSelected' type='text' class='form-control colorpicker' id='bgOptionSelected' value='$theme_options_styles[bgOptionSelected]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clOptionSelected' class='control-label-notes mb-2 me-2'>$langclOptionSelected:</label>
                                <input name='clOptionSelected' type='text' class='form-control colorpicker' id='clOptionSelected' value='$theme_options_styles[clOptionSelected]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/select.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navModal'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langSettingModals</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgModal' class='control-label-notes mb-2 me-2'>$langBgModal:</label>
                                <input name='BgModal' type='text' class='form-control colorpicker' id='BgModal' value='$theme_options_styles[BgModal]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderModal' class='control-label-notes mb-2 me-2'>$langclBorderModal:</label>
                                <input name='clBorderModal' type='text' class='form-control colorpicker' id='clBorderModal' value='$theme_options_styles[clBorderModal]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clTextModal' class='control-label-notes mb-2 me-2'>$langclTextModal:</label>
                                <input name='clTextModal' type='text' class='form-control colorpicker' id='clTextModal' value='$theme_options_styles[clTextModal]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clDeleteIconModal' class='control-label-notes mb-2 me-2'>$langclDeleteIconModal:</label>
                                <input name='clDeleteIconModal' type='text' class='form-control colorpicker' id='clDeleteIconModal' value='$theme_options_styles[clDeleteIconModal]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clXmarkModal' class='control-label-notes mb-2 me-2'>$langclXmarkModal:</label>
                                <input name='clXmarkModal' type='text' class='form-control colorpicker' id='clXmarkModal' value='$theme_options_styles[clXmarkModal]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/modal.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navTables'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langTables</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgTables' class='control-label-notes mb-2 me-2'>$langBgTables:</label>
                                <input name='BgTables' type='text' class='form-control colorpicker' id='BgTables' value='$theme_options_styles[BgTables]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderBottomHeadTables' class='control-label-notes mb-2 me-2'>$langBgBorderBottomHeadTables:</label>
                                <input name='BgBorderBottomHeadTables' type='text' class='form-control colorpicker' id='BgBorderBottomHeadTables' value='$theme_options_styles[BgBorderBottomHeadTables]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BgBorderBottomRowTables' class='control-label-notes mb-2 me-2'>$langBgBorderBottomRowTables:</label>
                                <input name='BgBorderBottomRowTables' type='text' class='form-control colorpicker' id='BgBorderBottomRowTables' value='$theme_options_styles[BgBorderBottomRowTables]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='BoxShadowRowTables' class='control-label-notes mb-2 me-2'>$langBoxShadowRowTables:</label>
                                <input name='BoxShadowRowTables' type='text' class='form-control colorpicker' id='BoxShadowRowTables' value='$theme_options_styles[BoxShadowRowTables]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/table.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navTabs'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langTabs</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clTabs' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                                <input name='clTabs' type='text' class='form-control colorpicker' id='clTabs' value='$theme_options_styles[clTabs]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredTabs' class='control-label-notes mb-2 me-2'>$langHoverTextColor:</label>
                                <input name='clHoveredTabs' type='text' class='form-control colorpicker' id='clHoveredTabs' value='$theme_options_styles[clHoveredTabs]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clActiveTabs' class='control-label-notes mb-2 me-2'>$langActiveTextColor:</label>
                                <input name='clActiveTabs' type='text' class='form-control colorpicker' id='clActiveTabs' value='$theme_options_styles[clActiveTabs]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/tab.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACCORDION SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navAccordions'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langAccordions</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clAccordions' class='control-label-notes mb-2 me-2'>$langTextColor:</label>
                                <input name='clAccordions' type='text' class='form-control colorpicker' id='clAccordions' value='$theme_options_styles[clAccordions]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderBottomAccordions' class='control-label-notes mb-2 me-2'>$langAccordionsBorderBottom:</label>
                                <input name='clBorderBottomAccordions' type='text' class='form-control colorpicker' id='clBorderBottomAccordions' value='$theme_options_styles[clBorderBottomAccordions]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredAccordions' class='control-label-notes mb-2 me-2'>$langHoverTextColor:</label>
                                <input name='clHoveredAccordions' type='text' class='form-control colorpicker' id='clHoveredAccordions' value='$theme_options_styles[clHoveredAccordions]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clActiveAccordions' class='control-label-notes mb-2 me-2'>$langActiveTextColor:</label>
                                <input name='clActiveAccordions' type='text' class='form-control colorpicker' id='clActiveAccordions' value='$theme_options_styles[clActiveAccordions]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/accordion.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LIST SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navLists'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langLists</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgLists' class='control-label-notes mb-2 me-2'>$langBgColorList:</label>
                                <input name='bgLists' type='text' class='form-control colorpicker' id='bgLists' value='$theme_options_styles[bgLists]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderBottomLists' class='control-label-notes mb-2 me-2'>$langclBorderBottomLists:</label>
                                <input name='clBorderBottomLists' type='text' class='form-control colorpicker' id='clBorderBottomLists' value='$theme_options_styles[clBorderBottomLists]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLists' class='control-label-notes mb-2 me-2'>$langclLists:</label>
                                <input name='clLists' type='text' class='form-control colorpicker' id='clLists' value='$theme_options_styles[clLists]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredLists' class='control-label-notes mb-2 me-2'>$langclHoveredLists:</label>
                                <input name='clHoveredLists' type='text' class='form-control colorpicker' id='clHoveredLists' value='$theme_options_styles[clHoveredLists]'>
                            </div>
                            <hr>
                            <div class='form-group mt-2'>
                                <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAddPaddingListGroup</h3>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label='$langSettingSelect'>
                                            <input type='checkbox' name='AddPaddingListGroup' value='1' ".((isset($theme_options_styles['AddPaddingListGroup']))? 'checked' : '').">
                                            <span class='checkmark'></span>
                                            $langActivate
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/list_group.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTEXTUAL MENU SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navContextualMenu'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langContextualMenuInfo</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgContextualMenu' class='control-label-notes mb-2 me-2'>$langBgColorMenuCont:</label>
                                <input name='bgContextualMenu' type='text' class='form-control colorpicker' id='bgContextualMenu' value='$theme_options_styles[bgContextualMenu]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgBorderContextualMenu' class='control-label-notes mb-2 me-2'>$langbgBorderContextualMenu:</label>
                                <input name='bgBorderContextualMenu' type='text' class='form-control colorpicker' id='bgBorderContextualMenu' value='$theme_options_styles[bgBorderContextualMenu]'>
                            </div>

                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgColorListMenu' class='control-label-notes mb-2 me-2'>$langBgColorListMenu:</label>
                                <input name='bgColorListMenu' type='text' class='form-control colorpicker' id='bgColorListMenu' value='$theme_options_styles[bgColorListMenu]'>
                            </div>

                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgHoveredListMenu' class='control-label-notes mb-2 me-2'>$langbgHoveredListMenu:</label>
                                <input name='bgHoveredListMenu' type='text' class='form-control colorpicker' id='bgHoveredListMenu' value='$theme_options_styles[bgHoveredListMenu]'>
                            </div>
                            
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clBorderBottomListMenu' class='control-label-notes mb-2 me-2'>$langclBorderBottomListMenu:</label>
                                <input name='clBorderBottomListMenu' type='text' class='form-control colorpicker' id='clBorderBottomListMenu' value='$theme_options_styles[clBorderBottomListMenu]'>
                            </div>
                            
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clListMenu' class='control-label-notes mb-2 me-2'>$langclListMenu:</label>
                                <input name='clListMenu' type='text' class='form-control colorpicker' id='clListMenu' value='$theme_options_styles[clListMenu]'>
                            </div>

                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredListMenu' class='control-label-notes mb-2 me-2'>$langclHoveredclHoveredListMenu:</label>
                                <input name='clHoveredListMenu' type='text' class='form-control colorpicker' id='clHoveredListMenu' value='$theme_options_styles[clHoveredListMenu]'>
                            </div>

                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clListMenuUsername' class='control-label-notes mb-2 me-2'>$langclListMenuUsername:</label>
                                <input name='clListMenuUsername' type='text' class='form-control colorpicker' id='clListMenuUsername' value='$theme_options_styles[clListMenuUsername]'>
                            </div>

                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clListMenuLogout' class='control-label-notes mb-2 me-2'>$langclListMenuLogout:</label>
                                <input name='clListMenuLogout' type='text' class='form-control colorpicker' id='clListMenuLogout' value='$theme_options_styles[clListMenuLogout]'>
                            </div>

                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clListMenuDeletion' class='control-label-notes mb-2 me-2'>$langclListMenuDeletion:</label>
                                <input name='clListMenuDeletion' type='text' class='form-control colorpicker' id='clListMenuDeletion' value='$theme_options_styles[clListMenuDeletion]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/contextual_menu.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MORE OPTIONS SETTINGS -->
            <div role='tabpanel' class='tab-pane fade' id='navMoreOptions'>
                <div class='form-wrapper form-edit rounded'>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline'>$langAboutImportantAnnouncement</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='bgContainerImportantAnnouncement' class='control-label-notes mb-2 me-2'>$langbgContainerImportantAnnouncement:</label>
                                <input name='bgContainerImportantAnnouncement' type='text' class='form-control colorpicker' id='bgContainerImportantAnnouncement' value='$theme_options_styles[bgContainerImportantAnnouncement]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clContainerImportantAnnouncement' class='control-label-notes mb-2 me-2'>$langclContainerImportantAnnouncement:</label>
                                <input name='clContainerImportantAnnouncement' type='text' class='form-control colorpicker' id='clContainerImportantAnnouncement' value='$theme_options_styles[clContainerImportantAnnouncement]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clLinkImportantAnnouncement' class='control-label-notes mb-2 me-2'>$langclLinkImportantAnnouncement:</label>
                                <input name='clLinkImportantAnnouncement' type='text' class='form-control colorpicker' id='clLinkImportantAnnouncement' value='$theme_options_styles[clLinkImportantAnnouncement]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='clHoveredLinkImportantAnnouncement' class='control-label-notes mb-2 me-2'>$langclHoveredLinkImportantAnnouncement:</label>
                                <input name='clHoveredLinkImportantAnnouncement' type='text' class='form-control colorpicker' id='clHoveredLinkImportantAnnouncement' value='$theme_options_styles[clHoveredLinkImportantAnnouncement]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/more_1.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAboutFaqImageUpload</h3>
                            <div class='form-group mt-4'>
                                
                                <div class='col-sm-12'>
                                    $faq_image_fieldL
                                </div>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/more_2.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAboutChatContainer</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutChatContainer' class='control-label-notes mb-2 me-2'>$langContainerBgColor:</label>
                                <input name='AboutChatContainer' type='text' class='form-control colorpicker' id='AboutChatContainer' value='$theme_options_styles[AboutChatContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutBorderChatContainer' class='control-label-notes mb-2 me-2'>$langBorderContainerBgColor:</label>
                                <input name='AboutBorderChatContainer' type='text' class='form-control colorpicker' id='AboutBorderChatContainer' value='$theme_options_styles[AboutBorderChatContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutChatContainerBoxShadow' class='control-label-notes mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='AboutChatContainerBoxShadow' type='text' class='form-control colorpicker' id='AboutChatContainerBoxShadow' value='$theme_options_styles[AboutChatContainerBoxShadow]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/more_3.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAboutCourseInfoContainer</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutCourseInfoContainer' class='control-label-notes mb-2 me-2'>$langContainerBgColor:</label>
                                <input name='AboutCourseInfoContainer' type='text' class='form-control colorpicker' id='AboutCourseInfoContainer' value='$theme_options_styles[AboutCourseInfoContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutBorderCourseInfoContainer' class='control-label-notes mb-2 me-2'>$langBorderContainerBgColor:</label>
                                <input name='AboutBorderCourseInfoContainer' type='text' class='form-control colorpicker' id='AboutBorderCourseInfoContainer' value='$theme_options_styles[AboutBorderCourseInfoContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutCourseInfoContainerBoxShadow' class='control-label-notes mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='AboutCourseInfoContainerBoxShadow' type='text' class='form-control colorpicker' id='AboutCourseInfoContainerBoxShadow' value='$theme_options_styles[AboutCourseInfoContainerBoxShadow]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/more_4.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='d-flex justify-content-between align-items-start flex-wrap gap-3'>
                        <div>
                            <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAboutUnitsContainer</h3>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutUnitsContainer' class='control-label-notes mb-2 me-2'>$langContainerBgColor:</label>
                                <input name='AboutUnitsContainer' type='text' class='form-control colorpicker' id='AboutUnitsContainer' value='$theme_options_styles[AboutUnitsContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutBorderUnitsContainer' class='control-label-notes mb-2 me-2'>$langBorderContainerBgColor:</label>
                                <input name='AboutBorderUnitsContainer' type='text' class='form-control colorpicker' id='AboutBorderUnitsContainer' value='$theme_options_styles[AboutBorderUnitsContainer]'>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                                <label for='AboutUnitsContainerBoxShadow' class='control-label-notes mb-2 me-2'>$langBoxShadowPanels:</label>
                                <input name='AboutUnitsContainerBoxShadow' type='text' class='form-control colorpicker' id='AboutUnitsContainerBoxShadow' value='$theme_options_styles[AboutUnitsContainerBoxShadow]'>
                            </div>
                        </div>
                        <div class='d-flex justify-content-center align-items-start'>
                            <figure class='figure'>
                                <img src='$urlServer/template/modern/images/theme_settings/more_5.png' class='figure-img img-fluid rounded theme-img-settings' alt='...'>
                                <figcaption class='figure-caption'>$langDisplayOptionsImg</figcaption>
                            </figure>
                        </div>
                    </div>
                    <hr>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='ColorFocus' class='control-label-notes mb-2 me-2'>$langColorFocus:</label>
                        <input name='ColorFocus' type='text' class='form-control colorpicker' id='ColorFocus' value='$theme_options_styles[ColorFocus]'>
                    </div>
                </div>
            </div>

            <div role='tabpanel' class='tab-pane' id='messages'>...</div>
            <div role='tabpanel' class='tab-pane' id='settings'>...</div>
        </div>
            <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-center align-items-center gap-2 flex-wrap'>
                    ".($theme_id ? "<input class='btn successAdminBtn' name='optionsSave' type='submit' value='$langSave'>" : "")."
                    <input class='btn successAdminBtn' name='optionsSaveAs' id='optionsSaveAs' type='submit' value='$langSaveAs'>
                    ".($theme_id ? "<a class='btn btn-default' href='theme_options.php?export=true'>$langExport</a>" : "")."
                </div>
            </div>
            ". generate_csrf_token_form_field() ."
        </form>
    </div>
</div>
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
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg','loginImgL','imageUploadFooter','imageUploadForm', 'imageUploadRegistration', 'imageUploadFaq', 'RightColumnCourseBgImage','faviconUpload');
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
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg','loginImgL','imageUploadFooter','imageUploadForm', 'imageUploadRegistration', 'imageUploadFaq', 'RightColumnCourseBgImage','faviconUpload');
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
draw($tool_content, 3, null, $head_content);

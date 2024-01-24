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
                'rgba(255, 255, 255, 1)' => array('leftNavBgColor','bgColor','bgColorHeader','buttonTextColor','bgColorFooter',
                                                    'whiteButtonHoveredBgColor','BgColorWrapperHeader', 'bgColorWrapperFooter', 'BgColorWrapperHomepage', 
                                                    'BgColorWrapperPortfolioCourses', 'RightColumnCourseBgColor', 'BgPanels', 'BgForms', 'BgTables', 'bgLists' ,
                                                    'bgContextualMenu', 'bgColorListMenu', 'bgWhiteButtonColor', 'BgRadios', 'ClIconRadios', 'BgCheckboxes', 'ClIconCheckboxes', 'BgInput', 'BgSelect' ,'clHoveredSelectOption' ,'clOptionSelected', 'BgModal', 'bgAgenda'),
                'rgba(247, 249, 254, 1)' => array('BriefProfilePortfolioBgColor','loginJumbotronRadialBgColor','loginJumbotronBgColor','bgColorWrapperJumbotron','bgRadialWrapperJumbotron', 'BgColorWrapperBriefProfilePortfolio'),
                'rgb(0, 115, 230, 1)' => array('leftMenuFontColor','buttonBgColor', 'whiteButtonTextColor', 'whiteButtonHoveredTextColor', 'BgClRadios', 'BgActiveCheckboxes'),
                'rgba(43, 57, 68, 1)' => array('linkColorHeader','linkColorFooter','loginTextColor', 'leftSubMenuFontColor','ColorHyperTexts', 'clLabelForms', 'clListMenuUsername', 'clListMenu', 'BriefProfilePortfolioTextColor', 'ClRadios', 'ClCheckboxes', 'ClActiveCheckboxes', 'clTextModal', 'BgColorHeaderAgenda'),
                'rgba(0, 115, 230, 1)' => array('linkColor','linkHoverColorHeader','linkHoverColorFooter','leftSubMenuHoverFontColor','leftMenuSelectedLinkColor','linkActiveColorHeader', 'clHoveredTabs', 'clActiveTabs', 'clHoveredAccordions', 'clActiveAccordions', 'clLists', 'clHoveredLists', 'bgHoveredSelectOption', 'bgOptionSelected'),
                'rgba(0, 115, 230, 0.6)' => array('buttonHoverBgColor'),
                "rgba(77,161,228,1)" => array('leftMenuSelectedFontColor', 'leftMenuHoverFontColor'),
                "rgba(239, 246, 255, 1)" => array('leftSubMenuHoverBgColor','leftMenuSelectedBgColor','linkActiveBgColorHeader', 'clBorderPanels', 'clBorderBottomListMenu', 'clHoveredListMenu', 'bgHoveredListMenu'),
                "rgba(35,82,124,1)" => array('linkHoverColor'),
                "rgba(0,0,0,0.2)" => array('leftMenuBgColor'),
                "rgba(0,0,0,0)" => array('loginTextBgColor'),
                "rgba(79, 104, 147, 1)" => array('clTabs'),
                "rgba(104, 125, 163, 1)" => array('clAccordions','ClInactiveRadios', 'ClInactiveCheckboxes', 'clBorderInput', 'clInputText', 'clBorderSelect', 'clOptionSelect'),
                "rgba(232, 237, 248, 1)" => array('clBorderBottomAccordions', 'clBorderModal'),
                "rgba(239, 242, 251, 1)" => array('clBorderBottomLists'),
                "rgba(205, 212, 224, 1)" => array('bgBorderContextualMenu'),
                "rgba(155, 169, 193, 1)" => array('BgBorderRadios', 'BgBorderCheckboxes'),
                "repeat" => array('bgType'),
                "boxed" => array('containerType'),
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
        if (isset($styles['loginImg'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[loginImg]");
        }
        if (isset($styles['loginImgL'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[loginImgL]");
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
            if ($archive->open("courses/theme_data/$file_name") == TRUE) {
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
    if(isset($_POST['choose_from_jumbotronlist']) && !empty($_POST['choose_from_jumbotronlist'])){
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
    if(isset($_POST['choose_from_loginlist']) && !empty($_POST['choose_from_loginlist'])){
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
    $activate_btn = "<a href='#' class='theme_enable btn submitAdminBtn $activate_class me-2 mb-2' id='theme_enable'>$langActivate</a>";
    $preview_class = ' hidden';
    $preview_btn = "<a href='#' class='btn submitAdminBtn submitAdminBtnClassic $preview_class me-2 mb-2' id='theme_preview'>$langSee</a>";
    $del_class = ($theme_id != 0) ? "" : " hidden";
    $delete_btn = "
                    <form class='form-inline mt-0' style='display:inline;' method='post' action='$_SERVER[SCRIPT_NAME]?delThemeId=$theme_id'>
                        <a class='confirmAction mt-md-0 btn deleteAdminBtn $del_class delThemeBtn' id='theme_delete' data-title='$langConfirmDelete' data-message='$langThemeSettingsDelete' data-cancel-txt='$langCancel' data-action-txt='$langDelete' data-action-class='deleteAdminBtn'>$langDelete</a>
                    </form>";
    $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
    if (isset($theme_options_styles['imageUpload'])) {
        $logo_field = "
            <img src='$urlThemeData/$theme_options_styles[imageUpload]' style='max-height:100px;max-width:150px;'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUpload'>$langDelete</a>
            <input type='hidden' name='imageUpload' value='$theme_options_styles[imageUpload]'>
        ";
    } else {
       $logo_field = "<input type='file' name='imageUpload' id='imageUpload'>";
    }
    if (isset($theme_options_styles['imageUploadSmall'])) {
        $small_logo_field = "
            <img src='$urlThemeData/$theme_options_styles[imageUploadSmall]' style='max-height:100px;max-width:150px;'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadSmall'>$langDelete</a>
            <input type='hidden' name='imageUploadSmall' value='$theme_options_styles[imageUploadSmall]'>
        ";
    } else {
       $small_logo_field = "<input type='file' name='imageUploadSmall' id='imageUploadSmall'>";
    }
    if (isset($theme_options_styles['imageUploadFooter'])) {
        $image_footer_field = "
            <img src='$urlThemeData/$theme_options_styles[imageUploadFooter]' style='max-height:100px;max-width:150px;'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadFooter'>$langDelete</a>
            <input type='hidden' name='imageUploadFooter' value='$theme_options_styles[imageUploadFooter]'>
        ";
    } else {
       $image_footer_field = "<input type='file' name='imageUploadFooter' id='imageUploadFooter'>";
    }
    if (isset($theme_options_styles['bgImage'])) {
        $bg_field = "
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[bgImage]' style='max-height:100px;max-width:150px;'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=bgImage'>$langDelete</a>
            </div>
            <input type='hidden' name='bgImage' value='$theme_options_styles[bgImage]'>
        ";
    } else {
       $bg_field = "<input type='file' name='bgImage' id='bgImage'>";
    }
    if (isset($theme_options_styles['loginImg'])) {
        $login_image_field = "
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[loginImg]' style='max-height:100px;max-width:150px;'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=loginImg'>$langDelete</a>
            </div>
            <input type='hidden' name='loginImg' value='$theme_options_styles[loginImg]'>
        ";
    } else {
       $login_image_field = "

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
                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImage'>
                </div>
            </div>


        ";
    }

    if (isset($theme_options_styles['loginImgL'])) {
        $login_image_fieldL = "
            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                <img src='$urlThemeData/$theme_options_styles[loginImgL]' style='max-height:100px;max-width:150px;'>
                <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=loginImgL'>$langDelete</a>
            </div>
            <input type='hidden' name='loginImgL' value='$theme_options_styles[loginImgL]'>
        ";
    } else {
       $login_image_fieldL = "

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
                    <input type='file' name='loginImgL' id='loginImgL'>
                </div>
                <div class='tab-pane fade' id='tabs-selectImage2' role='tabpanel' aria-labelledby='tabs-selectImage-tab2'>
                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#LoginImagesModal'>
                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;$langSelect
                    </button>
                    <input type='hidden' id='choose_from_loginlist' name='choose_from_loginlist'>
                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImageLogin'>
                </div>
            </div>



       ";
    }


    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "{$urlAppend}modules/admin/index.php",
            'icon' => 'fa-reply',
            'level' => 'primary'),
        array('title' => $langImport,
            'url' => "#",
            'icon' => 'fa-upload',
            'class' => 'uploadTheme',
            'level' => 'primary-label')

        ),false);
    if (isset($preview_theme)) {
        $tool_content .= "
                <div class='alert alert-warning d-flex justify-content-between align-items-center'>

                        <div>
                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langPreviewState &nbsp;".$themes_arr[$preview_theme].".</span>
                        </div>
                        <div class='d-lg-flex'>
                            <a href='#' class='theme_enable btn submitAdminBtn'>$langActivate</a>
                            <a href='theme_options.php?reset_theme_options=true' class='btn cancelAdminBtn ms-lg-2 mt-lg-0 mt-2'>$langLogout</a>
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



    @$tool_content .= "
    <div class='col-sm-12 mb-4'>
    <div class='form-wrapper form-edit theme-option-wrapper Borders'>
        <div class='d-flex justify-content-start align-items-center gap-2'>
            <strong class='control-label-notes mb-0'>$langActiveTheme:</strong>
            ".$themes_arr[$active_theme]."
        </div>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' id='theme_selection'>
            <div class='form-group mt-4'>
                <label for='bgColor' class='col-sm-12 control-label-notes'>$langAvailableThemes:</label>
                <div class='col-sm-12'>
                    ".  selection($themes_arr, 'active_theme_options', $theme_id, 'class="form-control form-submit" id="theme_selection"')."
                </div>
            </div>
            ". generate_csrf_token_form_field() ."
        </form>
        <div class='form-group mt-4 margin-bottom-fat'>
            <div class='col-12 d-flex flex-wrap'>
                $activate_btn
                $preview_btn
                $delete_btn
            </div>
        </div>
    </div></div>";

$tool_content .= "
<div class='col-12 border-card p-3'>
<div role='tabpanel mt-4'>

  <!-- Nav tabs -->
  <ul class='nav nav-tabs' role='tablist'>
    <li role='presentation' class='nav-item'><a class='nav-link active' href='#generalsetting' aria-controls='generalsetting' role='tab' data-bs-toggle='tab'>$langGeneralSettings</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsBody' aria-controls='navsettingsBody' role='tab' data-bs-toggle='tab'>$langNavBody</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsHeader' aria-controls='navsettingsHeader' role='tab' data-bs-toggle='tab'>$langNavSettingsHeader</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsFooter' aria-controls='navsettingsFooter' role='tab' data-bs-toggle='tab'>$langNavSettingsFooter</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navlinksButtons' aria-controls='navlinksButtons' role='tab' data-bs-toggle='tab'>$langNavLinksButtons</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navHyperTexts' aria-controls='navHyperTexts' role='tab' data-bs-toggle='tab'>$langNavHyperTexts</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navPanels' aria-controls='navPanels' role='tab' data-bs-toggle='tab'>$langPanels</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navForms' aria-controls='navForms' role='tab' data-bs-toggle='tab'>$langForms</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navRadios' aria-controls='navRadios' role='tab' data-bs-toggle='tab'>$langRadio</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navCheckboxes' aria-controls='navCheckboxes' role='tab' data-bs-toggle='tab'>$langCheckbox</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navInputText' aria-controls='navInputText' role='tab' data-bs-toggle='tab'>$langInputText</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navSelect' aria-controls='navSelect' role='tab' data-bs-toggle='tab'>$langSelectOption</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navModal' aria-controls='navModal' role='tab' data-bs-toggle='tab'>$langModals</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navTables' aria-controls='navTables' role='tab' data-bs-toggle='tab'>$langTables</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navTabs' aria-controls='navTabs' role='tab' data-bs-toggle='tab'>$langTabs</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navAccordions' aria-controls='navAccordions' role='tab' data-bs-toggle='tab'>$langAccordions</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navLists' aria-controls='navLists' role='tab' data-bs-toggle='tab'>$langLists</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navContextualMenu' aria-controls='navContextualMenu' role='tab' data-bs-toggle='tab'>$langContextualMenu</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsAgenda' aria-controls='navsettingsAgenda' role='tab' data-bs-toggle='tab'>$langNavSettingsAgenda</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettingsLoginHomepage' aria-controls='navsettingsLoginHomepage' role='tab' data-bs-toggle='tab'>$langHomePage</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navcontainer' aria-controls='navcontainer' role='tab' data-bs-toggle='tab'>$langPortfolio</a></li>
    <li role='presentation' class='nav-item'><a class='nav-link' href='#navsettings' aria-controls='navsettings' role='tab' data-bs-toggle='tab'>$langNavSettings</a></li>
    
  </ul>

  <!-- Tab panes -->
  <div class='form-wrapper form-edit theme-option-wrapper Borders'>
    <form id='theme_options_form' class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' enctype='multipart/form-data' method='post'>
        <div class='tab-content'>

        
            <div role='tabpanel' class='tab-pane fade show active' id='generalsetting'>
                <div class='form-wrapper form-edit form-create-theme rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langLayoutConfig</h3>
                    <div class='form-group'>
                        <label class='col-sm-6 control-label-notes mb-2'>$langLayout:</label>
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
                            <input id='fluidContainerWidth' name='fluidContainerWidth' data-slider-id='ex1Slider' type='text' data-slider-min='1340' data-slider-max='1920' data-slider-step='10' data-slider-value='$theme_options_styles[fluidContainerWidth]' ".(($theme_options_styles['containerType'] == 'boxed')? ' disabled' : '').">
                            <span style='margin-left:10px;' id='pixelCounter'></span>
                        </div>
                    </div>

                    <hr>

                    <h3 class='theme_options_legend text-decoration-underline mt-2'>$langLogoConfig</h3>
                    <div class='form-group'>
                        <label for='imageUpload' class='col-sm-6 control-label-notes mb-2'>$langLogo <small>$langLogoNormal</small>:</label>
                        <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                            $logo_field
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='imageUploadSmall' class='col-sm-6 control-label-notes mb-2'>$langLogo <small>$langLogoSmall</small>:</label>
                        <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                            $small_logo_field
                        </div>
                    </div>

                   
                </div>
            </div>











            <div role='tabpanel' class='tab-pane fade' id='navsettingsBody'>
                <div class='form-wrapper form-edit form-create-theme rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-2'>$langBgColorConfig</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgColor' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                        <input name='bgColor' type='text' class='form-control colorpicker' id='bgColor' value='$theme_options_styles[bgColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='imageBg' class='col-sm-6 control-label-notes mb-2'>$langBgImg:</label>
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

















            <div role='tabpanel' class='tab-pane fade' id='navsettingsHeader'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend mt-2 text-decoration-underline'>$langBgHeaderCongiguration</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgColorWrapperHeader' class='control-label-notes mb-2 me-2'>$langBgColorWrapperHeader:</label>
                        <input name='BgColorWrapperHeader' type='text' class='form-control colorpicker' id='BgColorWrapperHeader' value='$theme_options_styles[BgColorWrapperHeader]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgColorHeader' class='control-label-notes mb-2 me-2'>$langBgColor Header:</label>
                        <input name='bgColorHeader' type='text' class='form-control colorpicker' id='bgColorHeader' value='$theme_options_styles[bgColorHeader]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langLinkColorHeader</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='linkColorHeader' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                        <input name='linkColorHeader' type='text' class='form-control colorpicker' id='linkColorHeader' value='$theme_options_styles[linkColorHeader]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langHoverLinkColorHeader</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='linkHoverColorHeader' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
                        <input name='linkHoverColorHeader' type='text' class='form-control colorpicker' id='linkHoverColorHeader' value='$theme_options_styles[linkHoverColorHeader]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langActiveLinkBgColorHeader</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='linkActiveBgColorHeader' class='control-label-notes mb-2 me-2'>$langActiveLinkBgColorHeader:</label>
                        <input name='linkActiveBgColorHeader' type='text' class='form-control colorpicker' id='linkActiveBgColorHeader' value='$theme_options_styles[linkActiveBgColorHeader]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langActiveLinkColorHeader</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='linkActiveColorHeader' class='control-label-notes mb-2 me-2'>$langActiveLinkColorHeader:</label>
                        <input name='linkActiveColorHeader' type='text' class='form-control colorpicker' id='linkActiveColorHeader' value='$theme_options_styles[linkActiveColorHeader]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langShadowHeader</h3>
                    <div class='form-group mt-2'>
                        <div class='col-sm-12'>
                            <div class='checkbox'>
                                <label class='label-container'>
                                <input type='checkbox' name='shadowHeader' value='1' ".((isset($theme_options_styles['shadowHeader']))? 'checked' : '').">
                                <span class='checkmark'></span>
                                $langDeactivate
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>






























            <div role='tabpanel' class='tab-pane fade' id='navsettingsFooter'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-2'>$langBgFooterCongiguration</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgColorWrapperFooter' class='control-label-notes mb-2 me-2'>$langBgColorWrapperFooter:</label>
                        <input name='bgColorWrapperFooter' type='text' class='form-control colorpicker' id='bgColorWrapperFooter' value='$theme_options_styles[bgColorWrapperFooter]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgColorFooter' class='control-label-notes mb-2 me-2'>$langBgColor Footer:</label>
                        <input name='bgColorFooter' type='text' class='form-control colorpicker' id='bgColorFooter' value='$theme_options_styles[bgColorFooter]'>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langLinkColorFooter</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='linkColorFooter' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                        <input name='linkColorFooter' type='text' class='form-control colorpicker' id='linkColorFooter' value='$theme_options_styles[linkColorFooter]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='linkHoverColorFooter' class='control-label-notes mb-2 me-2'>$langHoverLinkColorFooter:</label>
                        <input name='linkHoverColorFooter' type='text' class='form-control colorpicker' id='linkHoverColorFooter' value='$theme_options_styles[linkHoverColorFooter]'>
                    </div>
                    <hr>
                    <div class='form-group mt-4'>
                        <label for='imageUploadFooter' class='col-sm-6 control-label-notes mb-2'>$langFooterUploadImage:</label>
                        <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                            $image_footer_field
                        </div>
                    </div>
                </div>
            </div>





















            <div role='tabpanel' class='tab-pane fade' id='navsettingsLoginHomepage'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-2'>$langLoginConfiguration</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgColorWrapperJumbotron' class='control-label-notes mb-2 me-2'>$langBgColorWrapperJumbotron (jumbotron):</label>
                        <input name='bgColorWrapperJumbotron' type='text' class='form-control colorpicker' id='bgColorWrapperJumbotron' value='$theme_options_styles[bgColorWrapperJumbotron]'>
                        <i class='fa fa-arrow-right ms-3 me-3'></i>
                        <input name='bgRadialWrapperJumbotron' type='text' class='form-control colorpicker' id='bgRadialWrapperJumbotron' value='$theme_options_styles[bgRadialWrapperJumbotron]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='loginJumbotronBgColor' class='control-label-notes mb-2 me-2'>$langLoginBgGradient (jumbotron):</label>
                        <input name='loginJumbotronBgColor' type='text' class='form-control colorpicker' id='loginJumbotronBgColor' value='$theme_options_styles[loginJumbotronBgColor]'>
                        <i class='fa fa-arrow-right ms-3 me-3'></i>
                        <input name='loginJumbotronRadialBgColor' type='text' class='form-control colorpicker' id='loginJumbotronRadialBgColor' value='$theme_options_styles[loginJumbotronRadialBgColor]'>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='loginImg' class='col-sm-12 control-label-notes mb-2'>$langLoginImg (jumbotron):</label>
                        <div class='col-sm-12'>
                        $login_image_field
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='loginTextColor' class='control-label-notes mb-2 me-2'>$langTextColor (jumbotron):</label>
                        <input name='loginTextColor' type='text' class='form-control colorpicker' id='loginTextColor' value='$theme_options_styles[loginTextColor]'>

                    </div>
                    <div class='form-group mt-4'>
                        <label for='loginTextBgColor' class='control-label-notes mb-2 me-2'>$langBgColor $langText (jumbotron):</label>
                        <input name='loginTextBgColor' type='text' class='form-control colorpicker' id='loginTextBgColor' value='$theme_options_styles[loginTextBgColor]'>

                    </div>


                
                    <div class='form-group mt-4'>
                        <label for='loginImgL' class='col-sm-6 control-label-notes mb-2'>$langLoginImg:</label>
                        <div class='col-sm-12'>
                        $login_image_fieldL
                        </div>
                    </div>


                    <div class='form-group mt-4'>
                        <div class='form-inline col-sm-9 col-sm-offset-3'>
                            <div class='radio'>
                                <label>
                                <input type='radio' name='FormLoginPlacement' value='center-position' ".(($theme_options_styles['FormLoginPlacement'] == 'center-position')? 'checked' : '').">
                                $langFormLoginPlacementCenter
                                </label>
                            </div>
                            <div class='radio'>
                                <label>
                                <input type='radio' name='FormLoginPlacement' value='right-position' ".(($theme_options_styles['FormLoginPlacement'] == 'right-position')? 'checked' : '').">
                                $langFormLoginPlacementLeft &nbsp;
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='loginImg' class='col-sm-12 control-label-notes mb-2'>$langLoginBanner:</label>
                        <div class='col-sm-12'>
                            <div class='checkbox'>
                                <label class='label-container'>
                                <input type='checkbox' name='openeclassBanner' value='1' ".((isset($theme_options_styles['openeclassBanner']))? 'checked' : '').">
                                <span class='checkmark'></span>
                                $langDeactivate
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langHomepageContainer</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgColorWrapperHomepage' class='control-label-notes mb-2 me-2'>$langBgColor:</label>
                        <input name='BgColorWrapperHomepage' type='text' class='form-control colorpicker' id='BgColorWrapperHomepage' value='$theme_options_styles[BgColorWrapperHomepage]'>
                    </div>
                </div>
            </div>




















            <div role='tabpanel' class='tab-pane fade' id='navsettings'>
                <div class='form-wrapper form-edit rounded'>

                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langBgColorConfigRightColumn</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='RightColumnCourseBgColor' class='control-label-notes me-2 mb-2'>$langBgColor:</label>
                        <input name='RightColumnCourseBgColor' type='text' class='form-control colorpicker' id='RightColumnCourseBgColor' value='$theme_options_styles[RightColumnCourseBgColor]'>
                    </div>

                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langBgColorConfig $langHelpCourseUI</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                    <label for='leftNavBgColor' class='control-label-notes me-2 mb-2'>$langBgColor:</label>
                    <input name='leftNavBgColor' type='text' class='form-control colorpicker' id='leftNavBgColor' value='$theme_options_styles[leftNavBgColor]'>
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
            </div>


















            <div role='tabpanel' class='tab-pane fade' id='navlinksButtons'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-2'>$langButtonsColorCongiguration</h3>
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
                        <label for='whiteButtonHoveredTextColor' class='control-label-notes mb-2 me-2'>$langHoverTextColor:</label>
                        <input name='whiteButtonHoveredTextColor' type='text' class='form-control colorpicker' id='whiteButtonHoveredTextColor' value='$theme_options_styles[whiteButtonHoveredTextColor]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='whiteButtonHoveredBgColor' class='control-label-notes mb-2 me-2'>$langHoverWhiteColorButton:</label>
                        <input name='whiteButtonHoveredBgColor' type='text' class='form-control colorpicker' id='whiteButtonHoveredBgColor' value='$theme_options_styles[whiteButtonHoveredBgColor]'>
                    </div>

                    <hr>

                    <h3 class='theme_options_legend text-decoration-underline mt-2'>$langLinksCongiguration ($langEclass)</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='linkColor' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
                        <input name='linkColor' type='text' class='form-control colorpicker' id='linkColor' value='$theme_options_styles[linkColor]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='linkHoverColor' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
                        <input name='linkHoverColor' type='text' class='form-control colorpicker' id='linkHoverColor' value='$theme_options_styles[linkHoverColor]'>
                    </div>
                </div>
            </div>



            <div role='tabpanel' class='tab-pane fade' id='navcontainer'>
                <div class='form-wrapper form-edit rounded'>

                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langPortFolioProfileContainer</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgColorWrapperBriefProfilePortfolio' class='control-label-notes mb-2 me-2'>$langBgColorWrapperBriefProfilePortfolio:</label>
                        <input name='BgColorWrapperBriefProfilePortfolio' type='text' class='form-control colorpicker' id='BgColorWrapperBriefProfilePortfolio' value='$theme_options_styles[BgColorWrapperBriefProfilePortfolio]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BriefProfilePortfolioBgColor' class='control-label-notes mb-2 me-2'>$langPortFolioProfileContainer:</label>
                        <input name='BriefProfilePortfolioBgColor' type='text' class='form-control colorpicker' id='BriefProfilePortfolioBgColor' value='$theme_options_styles[BriefProfilePortfolioBgColor]'>
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
            </div>


            <div role='tabpanel' class='tab-pane fade' id='navHyperTexts'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langPHyperTextColor</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='ColorHyperTexts' class='control-label-notes mb-2 me-2'>$langPHyperTextColor:</label>
                        <input name='ColorHyperTexts' type='text' class='form-control colorpicker' id='ColorHyperTexts' value='$theme_options_styles[ColorHyperTexts]'>
                    </div>
                </div>
            </div>


            <div role='tabpanel' class='tab-pane fade' id='navPanels'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langBgPanels</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgPanels' class='control-label-notes mb-2 me-2'>$langBgPanels:</label>
                        <input name='BgPanels' type='text' class='form-control colorpicker' id='BgPanels' value='$theme_options_styles[BgPanels]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='clBorderPanels' class='control-label-notes mb-2 me-2'>$langclBorderPanels:</label>
                        <input name='clBorderPanels' type='text' class='form-control colorpicker' id='clBorderPanels' value='$theme_options_styles[clBorderPanels]'>
                    </div>
                </div>
            </div>


            <div role='tabpanel' class='tab-pane fade' id='navRadios'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langRadios</h3>
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
            </div>










































            <div role='tabpanel' class='tab-pane fade' id='navCheckboxes'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langCheckboxes</h3>
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
            </div>





























































            <div role='tabpanel' class='tab-pane fade' id='navsettingsAgenda'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAgendaSettings</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='bgAgenda' class='control-label-notes mb-2 me-2'>$langBgColorAgenda:</label>
                        <input name='bgAgenda' type='text' class='form-control colorpicker' id='bgAgenda' value='$theme_options_styles[bgAgenda]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgColorHeaderAgenda' class='control-label-notes mb-2 me-2'>$langBgColorHeaderAgenda:</label>
                        <input name='BgColorHeaderAgenda' type='text' class='form-control colorpicker' id='BgColorHeaderAgenda' value='$theme_options_styles[BgColorHeaderAgenda]'>
                    </div>
                </div>
            </div>






















































            <div role='tabpanel' class='tab-pane fade' id='navForms'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langForms</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgForms' class='control-label-notes mb-2 me-2'>$langBgForms:</label>
                        <input name='BgForms' type='text' class='form-control colorpicker' id='BgForms' value='$theme_options_styles[BgForms]'>
                    </div>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='clLabelForms' class='control-label-notes mb-2 me-2'>$langColorLabel:</label>
                        <input name='clLabelForms' type='text' class='form-control colorpicker' id='clLabelForms' value='$theme_options_styles[clLabelForms]'>
                    </div>
                </div>
            </div>











            <div role='tabpanel' class='tab-pane fade' id='navInputText'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langInputText</h3>
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
            </div>


















            <div role='tabpanel' class='tab-pane fade' id='navSelect'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langSettingSelect</h3>
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
            </div>


































            <div role='tabpanel' class='tab-pane fade' id='navModal'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langSettingModals</h3>
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
                </div>
            </div>








            <div role='tabpanel' class='tab-pane fade' id='navTables'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langTables</h3>
                    <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                        <label for='BgTables' class='control-label-notes mb-2 me-2'>$langBgTables:</label>
                        <input name='BgTables' type='text' class='form-control colorpicker' id='BgTables' value='$theme_options_styles[BgTables]'>
                    </div>
                </div>
            </div>













            <div role='tabpanel' class='tab-pane fade' id='navTabs'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langTabs</h3>
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
            </div>












            <div role='tabpanel' class='tab-pane fade' id='navAccordions'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langAccordions</h3>
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
            </div>














            <div role='tabpanel' class='tab-pane fade' id='navLists'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langLists</h3>
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
                                <label class='label-container'>
                                    <input type='checkbox' name='AddPaddingListGroup' value='1' ".((isset($theme_options_styles['AddPaddingListGroup']))? 'checked' : '').">
                                    <span class='checkmark'></span>
                                    $langActivate
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>





















            <div role='tabpanel' class='tab-pane fade' id='navContextualMenu'>
                <div class='form-wrapper form-edit rounded'>
                    <h3 class='theme_options_legend text-decoration-underline mt-4'>$langContextualMenuInfo</h3>
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
                </div>
            </div>







            <div role='tabpanel' class='tab-pane' id='messages'>...</div>
            <div role='tabpanel' class='tab-pane' id='settings'>...</div>
        </div>
            <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-center align-items-center gap-2 flex-wrap'>
                    ".($theme_id ? "<input class='btn submitAdminBtn' name='optionsSave' type='submit' value='$langSave'>" : "")."
                    <input class='btn submitAdminBtn' name='optionsSaveAs' id='optionsSaveAs' type='submit' value='$langSaveAs'>
                    ".($theme_id ? "<a class='btn submitAdminBtn' href='theme_options.php?export=true'>$langExport</a>" : "")."
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
                <h5 class='modal-title' id='JumbotronImagesModalLabel'>$langLoginImg (jumbotron)</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>
            <div class='modal-body'>
                <div class='row row-cols-1 row-cols-md-2 g-4'>";
                        foreach($dir_jumbotron_images as $image) {
                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                            $imgExtArr = ['jpg', 'jpeg', 'png'];
                            if(in_array($extension, $imgExtArr)){
                                $tool_content .= "
                                    <div class='col'>
                                        <div class='card h-100'>
                                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/jumbotron_images/$image' alt='image jumbotron'/>
                                            <div class='card-body'>
                                                <p class='form-value'>$image</p>

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
                <h5 class='modal-title' id='LoginImagesModalLabel'>$langLoginImg</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>
            <div class='modal-body'>
                <div class='row row-cols-1 row-cols-md-2 g-4'>";
                        foreach($dir_login_images as $image) {
                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                            $imgExtArr = ['jpg', 'jpeg', 'png'];
                            if(in_array($extension, $imgExtArr)){
                                $tool_content .= "
                                    <div class='col'>
                                        <div class='card h-100'>
                                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/login_images/$image' alt='image login'/>
                                            <div class='card-body'>
                                                <p class='form-value'>$image</p>

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
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg','loginImgL','imageUploadFooter');
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
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg','loginImgL','imageUploadFooter');
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

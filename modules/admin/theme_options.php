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
                'rgba(35,44,58,1)' => array('leftNavBgColor','bgColor'),
                'rgba(173,173,173,1)' => array('leftMenuFontColor', 'leftSubMenuFontColor'),
                "rgba(77,161,228,1)" => array('linkColor', 'leftSubMenuHoverBgColor', 'leftMenuSelectedFontColor', 'leftMenuHoverFontColor'),
                "rgba(35,82,124,1)" => array('linkHoverColor'),
                "rgba(238,238,238,1)" => array('leftSubMenuHoverFontColor'),
                "rgba(0,0,0,0.2)" => array('leftMenuBgColor'),
                "repeat" => array('bgType'),
                "boxed" => array('containerType'),
                "rgba(0,155,207,1)" => array('loginJumbotronRadialBgColor'),
                "rgba(2,86,148,1)" => array('loginJumbotronBgColor'),
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
        if (isset($styles['loginImg'])) {
            array_push($file_list, "courses/theme_data/$theme_id/$styles[loginImg]");
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
                $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles) VALUES(?s, ?s)", $theme_options->name, $theme_options->styles)->lastInsertID;
                rename("$webDir/courses/theme_data/temp/".intval($theme_options->id), "$webDir/courses/theme_data/temp/$new_theme_id");
                recurse_copy("$webDir/courses/theme_data/temp","$webDir/courses/theme_data");
                removeDir("$webDir/courses/theme_data/temp");
                //Session::Messages($langThemeInstalled);
                Session::flash('message',$langThemeInstalled); 
                Session::flash('alert-class', 'alert-success');
            } else {
                die("Error while unzipping file !");
            }
            $archive->close();
        }
    } else {
        //Session::Messages($langUnwantedFiletype);
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
    $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles) VALUES(?s, '')", $theme_options_name)->lastInsertID;
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
        });
    </script>";
    $all_themes = Database::get()->queryArray("SELECT * FROM theme_options ORDER BY name, id");
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
    $preview_btn = "<a href='#' class='btn submitAdminBtn $preview_class me-2 mb-2' id='theme_preview'>$langSee</a>";
    $del_class = ($theme_id != 0) ? "" : " hidden";
    $delete_btn = "
                    <form class='form-inline mt-0' style='display:inline;' method='post' action='$_SERVER[SCRIPT_NAME]?delThemeId=$theme_id'>
                        <a class='confirmAction mt-md-0 btn deleteAdminBtn $del_class' id='theme_delete' data-title='$langConfirmDelete' data-message='$langThemeSettingsDelete' data-cancel-txt='$langCancel' data-action-txt='$langDelete' data-action-class='deleteAdminBtn'>$langDelete</a>
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
    if (isset($theme_options_styles['bgImage'])) {
        $bg_field = "
            <img src='$urlThemeData/$theme_options_styles[bgImage]' style='max-height:100px;max-width:150px;'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=bgImage'>$langDelete</a>
            <input type='hidden' name='bgImage' value='$theme_options_styles[bgImage]'>
        ";
    } else {
       $bg_field = "<input type='file' name='bgImage' id='bgImage'>";
    }
    if (isset($theme_options_styles['loginImg'])) {
        $login_image_field = "
            <img src='$urlThemeData/$theme_options_styles[loginImg]' style='max-height:100px;max-width:150px;'> &nbsp;&nbsp;<a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?delete_image=loginImg'>$langDelete</a>
            <input type='hidden' name='loginImg' value='$theme_options_styles[loginImg]'>
        ";
    } else {
       $login_image_field = "<input type='file' name='loginImg' id='loginImg'>";
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
    @$tool_content .= "
    <div class='col-sm-12 mb-4'>
    <div class='form-wrapper form-edit Borders p-lg-5 p-3'>
        <div class='d-flex justify-content-start'>
            <strong class='control-label-notes'>$langActiveTheme:</strong>
            ".$themes_arr[$active_theme]."
        </div>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' id='theme_selection'>
            <div class='form-group mt-4'>
                <label for='bgColor' class='col-sm-6 control-label-notes ps-2'>$langAvailableThemes:</label>
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
<div role='tabpanel mt-4'>

  <!-- Nav tabs -->
  <ul class='nav nav-tabs' role='tablist'>
    <li role='presentation' class='nav-item' style='border:solid 1px #ffffff;'><a class='nav-link active normalColorBlueText TextSemiBold' href='#generalsetting' aria-controls='generalsetting' role='tab' data-bs-toggle='tab'>$langGeneralSettings</a></li>
    <li role='presentation' class='nav-item' style='border:solid 1px #ffffff;'><a class='nav-link normalColorBlueText TextSemiBold' href='#navsettings' aria-controls='navsettings' role='tab' data-bs-toggle='tab'>$langNavSettings</a></li>
  </ul>

  <!-- Tab panes -->
  <form id='theme_options_form' class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' enctype='multipart/form-data' method='post'>
  <div class='tab-content'>
    <div role='tabpanel' class='tab-pane fade show active' id='generalsetting'>
        <div class='form-wrapper form-edit rounded-2 p-lg-5 p-3'>
            <legend class='theme_options_legend normalColorBlueText TextBold text-uppercase fs-5 mt-4'>$langLayoutConfig</legend>
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

            <legend class='theme_options_legend normalColorBlueText TextBold text-uppercase fs-5 mt-2'>$langLogoConfig</legend>
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

            <hr>

            <legend class='theme_options_legend normalColorBlueText TextBold text-uppercase fs-5 mt-2'>$langBgColorConfig</legend>
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

            <hr>

            <legend class='theme_options_legend normalColorBlueText TextBold text-uppercase fs-5 mt-2'>$langLinksCongiguration</legend>
            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
              <label for='linkColor' class='control-label-notes mb-2 me-2'>$langLinkColor:</label>
              <input name='linkColor' type='text' class='form-control colorpicker' id='linkColor' value='$theme_options_styles[linkColor]'>
              
            </div>
            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
              <label for='linkHoverColor' class='control-label-notes mb-2 me-2'>$langLinkHoverColor:</label>
              <input name='linkHoverColor' type='text' class='form-control colorpicker' id='linkHoverColor' value='$theme_options_styles[linkHoverColor]'>
            </div>

            <hr>


            <legend class='theme_options_legend normalColorBlueText TextBold text-uppercase fs-5 mt-2'>$langLoginConfiguration</legend>
            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
                    <label for='loginJumbotronBgColor' class='control-label-notes mb-2 me-2'>$langLoginBgGradient:</label>
                    <input name='loginJumbotronBgColor' type='text' class='form-control colorpicker' id='loginJumbotronBgColor' value='$theme_options_styles[loginJumbotronBgColor]'>
                    <i class='fa fa-arrow-right ms-3 me-3'></i>
                    <input name='loginJumbotronRadialBgColor' type='text' class='form-control colorpicker' id='loginJumbotronRadialBgColor' value='$theme_options_styles[loginJumbotronRadialBgColor]'>
            </div>
            <div class='form-group mt-4'>
                <label for='loginImg' class='col-sm-6 control-label-notes mb-2'>$langLoginImg:</label>
                <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
                   $login_image_field
                </div>
            </div>
            <div class='form-group mt-4'>
                <div class='form-inline col-sm-9 col-sm-offset-3'>
                      <div class='radio'>
                        <label>
                          <input type='radio' name='FormLoginPlacement' value='center-position' ".(($theme_options_styles['FormLoginPlacement'] == 'center-position')? 'checked' : '').">
                          $langFormLoginPlacementCenter &nbsp;
                        </label>
                      </div>
                      <div class='radio'>
                        <label>
                          <input type='radio' name='FormLoginPlacement' value='right-position' ".(($theme_options_styles['FormLoginPlacement'] == 'right-position')? 'checked' : '').">
                          $langFormLoginPlacementRight &nbsp;
                        </label>
                      </div>
                </div>
            </div>
            <div class='form-group mt-4'>
                <label for='loginImg' class='col-sm-12 control-label-notes mb-2'>$langLoginBanner:</label>
                <div class='col-sm-12'>
                      <div class='checkbox'>
                        <label>
                          <input type='checkbox' name='openeclassBanner' value='1' ".((isset($theme_options_styles['openeclassBanner']))? 'checked' : '').">
                          $langDeactivate
                        </label>
                      </div>
                </div>
            </div>
        </div>
    </div>
    <div role='tabpanel' class='tab-pane fade' id='navsettings'>
        <div class='form-wrapper form-edit rounded'>
            <legend class='theme_options_legend normalColorBlueText TextBold text-uppercase fs-5 mt-4'>$langBgColorConfig</legend>
            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
              <label for='leftNavBgColor' class='control-label-notes me-2 mb-2'>$langBgColor:</label>
              <input name='leftNavBgColor' type='text' class='form-control colorpicker' id='leftNavBgColor' value='$theme_options_styles[leftNavBgColor]'>
             
            </div>

            <hr>

            <legend class='theme_options_legend normalColorBlueText TextBold text-uppercase fs-5 mt-2'>$langMainMenuConfiguration</legend>
            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
              <label for='leftMenuBgColor' class='control-label-notes me-2 mb-2'>$langMainMenuBgColor:</label>
              <input name='leftMenuBgColor' type='text' class='form-control colorpicker' id='leftMenuBgColor' value='$theme_options_styles[leftMenuBgColor]'>
            </div>
            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
              <label for='leftMenuFontColor' class='control-label-notes mb-2 me-2'>$langMainMenuLinkColor:</label>
              <input name='leftMenuFontColor' type='text' class='form-control colorpicker' id='leftMenuFontColor' value='$theme_options_styles[leftMenuFontColor]'>
            </div>
            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
              <label for='leftMenuHoverFontColor' class='control-label-notes mb-2 me-2'>$langMainMenuLinkHoverColor:</label>
              <input name='leftMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftMenuHoverFontColor' value='$theme_options_styles[leftMenuHoverFontColor]'>
            </div>
            <div class='form-group mt-4 d-flex justify-content-start align-items-center'>
              <label for='leftMenuSelectedFontColor' class='control-label-notes mb-2 me-2'>$langMainMenuActiveLinkColor:</label>
              <input name='leftMenuSelectedFontColor' type='text' class='form-control colorpicker' id='leftMenuSelectedFontColor' value='$theme_options_styles[leftMenuSelectedFontColor]'>
            </div>

            <hr>


            <legend class='theme_options_legend normalColorBlueText TextBold text-uppercase fs-5 mt-2'>$langSubMenuConfig</legend>
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
        </div>
    </div>
    <div role='tabpanel' class='tab-pane' id='messages'>...</div>
    <div role='tabpanel' class='tab-pane' id='settings'>...</div>
  </div>
    <div class='form-group mt-5'>
        <div class='col-12 d-flex justify-content-center align-items-center'>
            ".($theme_id ? "<input class='btn submitAdminBtn me-1' name='optionsSave' type='submit' value='$langSave'>" : "")."
            <input class='btn submitAdminBtn mb-md-0 mb-2 me-1' name='optionsSaveAs' id='optionsSaveAs' type='submit' value='$langSaveAs'>
            ".($theme_id ? "<a class='btn submitAdminBtn' href='theme_options.php?export=true'>$langExport</a>" : "")."
        </div>
    </div>
    ". generate_csrf_token_form_field() ."
</form>
</div>";
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
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg');
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
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg');
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

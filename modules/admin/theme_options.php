<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
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
                "rgba(0,155,207,1)" => array('loginJumbotronRadialBgColor'),
                "rgba(2,86,148,1)" => array('loginJumbotronBgColor'),
                "small-right" => array("loginImgPlacement")        
            );
    
if (isset($_GET['reset_theme_options'])) {
    $theme_options_sets = Database::get()->queryArray("SELECT * FROM theme_options");
    foreach ($theme_options_sets as $theme_options) {
        $theme_options_styles = unserialize($theme_options->styles);
        if (isset($theme_options_styles['custom_logo'])) {
           $theme_options_styles['imageUpload'] = $theme_options_styles['custom_logo'];
           unset($theme_options_styles['custom_logo']);           
        }
        if (isset($theme_options_styles['custom_logo_small'])) {
           $theme_options_styles['imageUploadSmall'] = $theme_options_styles['custom_logo_small'];
           unset($theme_options_styles['custom_logo_small']);              
        }
        unset($theme_options_styles['optionsSave']);
        $serialized_data = serialize($theme_options_styles);
        Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, $theme_options->id);
    }
    $theme_options_styles = unserialize($theme_options->styles);    
}
if (isset($_GET['delete_image'])) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", get_config('theme_options_id'));
        $theme_options_styles = unserialize($theme_options->styles);
        $logo_type = $_GET['delete_image'];
        unlink("$webDir/template/$theme/img/$theme_options_styles[$logo_type]");
        unset($theme_options_styles[$logo_type]);
        $serialized_data = serialize($theme_options_styles);
        Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, get_config('theme_options_id'));
        redirect_to_home_page('modules/admin/theme_options.php');
}
if (isset($_POST['optionsSave'])) {
    upload_images();
    clear_default_settings();
    $serialized_data = serialize($_POST);
    Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, get_config('theme_options_id'));
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_GET['delThemeId'])) {
    $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", get_config('theme_options_id'));
    $theme_options_styles = unserialize($theme_options->styles);
    @unlink("$webDir/template/$theme/img/$theme_options_styles[imageUpload]");
    @unlink("$webDir/template/$theme/img/$theme_options_styles[imageUploadSmall]");
    @unlink("$webDir/template/$theme/img/$theme_options_styles[bgImage]");
    Database::get()->query("DELETE FROM theme_options WHERE id = ?d", get_config('theme_options_id'));
    Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", 0, 'theme_options_id');
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_POST['themeOptionsName'])) {
    $theme_options_name = $_POST['themeOptionsName'];
    clear_default_settings();
    rename_images(); //clone and rename already used images
    upload_images(); //upload new images
    $serialized_data = serialize($_POST);
    $theme_options_id = Database::get()->query("INSERT INTO theme_options (name, styles) VALUES(?s, ?s)", $theme_options_name, $serialized_data)->lastInsertID;
    Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", $theme_options_id, 'theme_options_id');
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_POST['active_theme_options'])) {
    Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", $_POST['active_theme_options'], 'theme_options_id');
    redirect_to_home_page('modules/admin/theme_options.php');     
} else {
    $pageName = $langThemeSettings;
    $navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
    load_js('spectrum');
    $head_content .= "
    <script>
        $(function(){
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
                                        '</div>'+
                                    '</form>'+
                                '</div>'+
                            '</div>',                          
                    buttons: {
                        success: {
                            label: '$langSave',
                            className: 'btn-success',
                            callback: function (d) {
                                var themeOptionsName = $('#themeOptionsName').val();
                                if(themeOptionsName) {
                                    var input = $('<input>')
                                                   .attr('type', 'hidden')
                                                   .attr('name', 'themeOptionsName').val(themeOptionsName);
                                    $('#theme_options_form').append($(input)).submit();
                                } else {
                                    $('#themeOptionsName').closest('.form-group').addClass('has-error');
                                    $('#themeOptionsName').after('<span class=\"help-block\">$langTheFieldIsRequired</span>');
                                    return false;
                                }
                            }
                        },
                        cancel: {
                            label: '$langCancel',
                            className: 'btn-default'
                        }                        
                    }
                });
            })
            $('select.form-submit').change(function ()
            {
                $(this).closest('form').submit();
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
    $all_themes = Database::get()->queryArray("SELECT * FROM theme_options");
    $themes_arr[0] = "---- $langDefaultThemeSettings ----";
    foreach ($all_themes as $row) {
        $themes_arr[$row->id] = $row->name;
    }
    
    if (get_config('theme_options_id')) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", get_config('theme_options_id'));
        $theme_options_styles = unserialize($theme_options->styles);
    }
    initialize_settings();
    
    $delete_btn = (get_config('theme_options_id')) 
            ? 
            "<form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]?delThemeId=".get_config('theme_options_id')."'>
                <div class='form-group'>
                    <div class='col-sm-9 col-sm-offset-3'>
                        <a class='confirmAction btn btn-danger btn-xs' data-title='$langConfirmDelete' data-message='$langThemeSettingsDelete' data-cancel-txt='$langCancel' data-action-txt='$langDelete' data-action-class='btn-danger'>$langDelete</a>
                    </div>
                </div>
            </form>"
            : "";
    if (isset($theme_options_styles['imageUpload'])) {
        $logo_field = "
            <img src='$themeimg/$theme_options_styles[imageUpload]' style='max-height:100px;max-width:150px;'> &nbsp&nbsp<a class='btn btn-xs btn-danger' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUpload'>$langDelete</a>
            <input type='hidden' name='imageUpload' value='$theme_options_styles[imageUpload]'>
        ";
    } else {
       $logo_field = "<input type='file' name='imageUpload' id='imageUpload'>"; 
    }
    if (isset($theme_options_styles['imageUploadSmall'])) {
        $small_logo_field = "
            <img src='$themeimg/$theme_options_styles[imageUploadSmall]' style='max-height:100px;max-width:150px;'> &nbsp&nbsp<a class='btn btn-xs btn-danger' href='$_SERVER[SCRIPT_NAME]?delete_image=imageUploadSmall'>$langDelete</a>
            <input type='hidden' name='imageUploadSmall' value='$theme_options_styles[imageUploadSmall]'>
        ";
    } else {
       $small_logo_field = "<input type='file' name='imageUploadSmall' id='imageUploadSmall'>"; 
    }
    if (isset($theme_options_styles['bgImage'])) {
        $bg_field = "
            <img src='$themeimg/$theme_options_styles[bgImage]' style='max-height:100px;max-width:150px;'> &nbsp&nbsp<a class='btn btn-xs btn-danger' href='$_SERVER[SCRIPT_NAME]?delete_image=bgImage'>$langDelete</a>
            <input type='hidden' name='bgImage' value='$theme_options_styles[bgImage]'>
        ";
    } else {
       $bg_field = "<input type='file' name='bgImage' id='bgImage'>"; 
    }
    if (isset($theme_options_styles['loginImg'])) {
        $login_image_field = "
            <img src='$themeimg/$theme_options_styles[loginImg]' style='max-height:100px;max-width:150px;'> &nbsp&nbsp<a class='btn btn-xs btn-danger' href='$_SERVER[SCRIPT_NAME]?delete_image=loginImg'>$langDelete</a>
            <input type='hidden' name='loginImg' value='$theme_options_styles[loginImg]'>
        ";
    } else {
       $login_image_field = "<input type='file' name='loginImg' id='loginImg'>"; 
    }    
    @$tool_content .= "
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
            <div class='form-group'>
                <label for='bgColor' class='col-sm-3 control-label'>$langActiveThemeSettings:</label>
                <div class='col-sm-9'>
                    ".  selection($themes_arr, 'active_theme_options', get_config('theme_options_id'), 'class="form-control form-submit"')."
                </div>
            </div>
        </form>
        $delete_btn
    </div>
    <div class='form-wrapper'>
        <form id='theme_options_form' class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' enctype='multipart/form-data' method='post'>
            <h3>$langGeneralSettings</h3>
            <div class='form-group'>
                <label for='imageUpload' class='col-sm-3 control-label'>$langLogo <small>$langLogoNormal</small>:</label>
                <div class='col-sm-9'>
                   $logo_field
                </div>
            </div>
            <div class='form-group'>
                <label for='imageUploadSmall' class='col-sm-3 control-label'>$langLogo <small>$langLogoSmall</small>:</label>
                <div class='col-sm-9'>
                   $small_logo_field
                </div>
            </div>              
            <div class='form-group'>
              <label for='bgColor' class='col-sm-3 control-label'>$langBgColor:</label>
              <div class='col-sm-9'>
                <input name='bgColor' type='text' class='form-control colorpicker' id='bgColor' value='$theme_options_styles[bgColor]'>
              </div>
            </div>
            <div class='form-group'>
                <label for='imageBg' class='col-sm-3 control-label'>$langBgImg:</label>
                <div class='col-sm-9'>
                   $bg_field
                </div>
                <div class='form-inline col-sm-9 col-sm-offset-3'>
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
            <div class='form-group'>
              <label for='linkColor' class='col-sm-3 control-label'>$langLinkColor:</label>
              <div class='col-sm-9'>
                <input name='linkColor' type='text' class='form-control colorpicker' id='linkColor' value='$theme_options_styles[linkColor]'>
              </div>
            </div> 
            <div class='form-group'>
              <label for='linkHoverColor' class='col-sm-3 control-label'>$langLinkHoverColor:</label>
              <div class='col-sm-9'>
                <input name='linkHoverColor' type='text' class='form-control colorpicker' id='linkHoverColor' value='$theme_options_styles[linkHoverColor]'>
              </div>
            </div>                 
            <div class='form-group'>
              <label for='loginJumbotronBgColor' class='col-sm-3 control-label'>$langLoginBgGradient:</label>
              <div class='col-xs-4 col-sm-1'>
                <input name='loginJumbotronBgColor' type='text' class='form-control colorpicker' id='loginJumbotronBgColor' value='$theme_options_styles[loginJumbotronBgColor]'>
              </div>
              <div class='col-xs-1 text-center' style='padding-top: 7px;'>
                <i class='fa fa-arrow-right'></i>
              </div>
              <div class='col-xs-4 col-sm-1'>
                <input name='loginJumbotronRadialBgColor' type='text' class='form-control colorpicker' id='loginJumbotronRadialBgColor' value='$theme_options_styles[loginJumbotronRadialBgColor]'>
              </div>              
            </div>
            <div class='form-group'>
                <label for='loginImg' class='col-sm-3 control-label'>$langLoginImg:</label>
                <div class='col-sm-9'>
                   $login_image_field
                </div>
            </div>
            <div class='form-group'>
                <div class='form-inline col-sm-9 col-sm-offset-3'>
                      <div class='radio'>
                        <label>
                          <input type='radio' name='loginImgPlacement' value='small-right' ".(($theme_options_styles['loginImgPlacement'] == 'small-right')? 'checked' : '').">
                          $langLoginImgPlacementSmall &nbsp; 
                        </label>
                      </div>
                      <div class='radio'>
                        <label>
                          <input type='radio' name='loginImgPlacement' value='full-width' ".(($theme_options_styles['loginImgPlacement'] == 'full-width')? 'checked' : '').">
                          $langLoginImgPlacementFull &nbsp;
                        </label>
                      </div>                                    
                </div> 
            </div>
            <div class='form-group'>
                <label for='loginImg' class='col-sm-3 control-label'>Banner Open eClass Οθόνης Σύνδεσης:</label>
                <div class='col-sm-9'>
                      <div class='checkbox'>
                        <label>
                          <input type='checkbox' name='openeclassBanner' value='1' ".((isset($theme_options_styles['openeclassBanner']))? 'checked' : '').">
                          $langDeactivate
                        </label>
                      </div>                   
                </div>
            </div>            
            <hr>
            <h3>$langNavSettings</h3>            
            <div class='form-group'>
              <label for='leftNavBgColor' class='col-sm-3 control-label'>$langBgColor:</label>
              <div class='col-sm-9'>
                <input name='leftNavBgColor' type='text' class='form-control colorpicker' id='leftNavBgColor' value='$theme_options_styles[leftNavBgColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftMenuBgColor' class='col-sm-3 control-label'>$langMainMenuBgColor:</label>
              <div class='col-sm-9'>
                <input name='leftMenuBgColor' type='text' class='form-control colorpicker' id='leftMenuBgColor' value='$theme_options_styles[leftMenuBgColor]'>
              </div>
            </div>             
            <div class='form-group'>
              <label for='leftMenuFontColor' class='col-sm-3 control-label'>$langMainMenuLinkColor:</label>
              <div class='col-sm-9'>
                <input name='leftMenuFontColor' type='text' class='form-control colorpicker' id='leftMenuFontColor' value='$theme_options_styles[leftMenuFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftMenuHoverFontColor' class='col-sm-3 control-label'>$langMainMenuLinkHoverColor:</label>
              <div class='col-sm-9'>
                <input name='leftMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftMenuHoverFontColor' value='$theme_options_styles[leftMenuHoverFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftMenuSelectedFontColor' class='col-sm-3 control-label'>$langMainMenuActiveLinkColor:</label>
              <div class='col-sm-9'>
                <input name='leftMenuSelectedFontColor' type='text' class='form-control colorpicker' id='leftMenuSelectedFontColor' value='$theme_options_styles[leftMenuSelectedFontColor]'>
              </div>
            </div>               
            <div class='form-group'>
              <label for='leftSubMenuFontColor' class='col-sm-3 control-label'>$langSubMenuLinkColor:</label>
              <div class='col-sm-9'>
                <input name='leftSubMenuFontColor' type='text' class='form-control colorpicker' id='leftSubMenuFontColor' value='$theme_options_styles[leftSubMenuFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftSubMenuHoverFontColor' class='col-sm-3 control-label'>$langSubMenuLinkHoverColor:</label>
              <div class='col-sm-9'>
                <input name='leftSubMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverFontColor' value='$theme_options_styles[leftSubMenuHoverFontColor]'>
              </div>
            </div>                
            <div class='form-group'>
              <label for='leftSubMenuHoverBgColor' class='col-sm-3 control-label'>$langSubMenuLinkBgHoverColor:</label>
              <div class='col-sm-9'>
                <input name='leftSubMenuHoverBgColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverBgColor' value='$theme_options_styles[leftSubMenuHoverBgColor]'>
              </div>
            </div>                                  
            <div class='form-group'>
                <div class='col-sm-9 col-sm-offset-3'>
                    ".(get_config('theme_options_id') ? "<input class='btn btn-primary' name='optionsSave' type='submit' value='$langSave'>" : "")."
                    <input class='btn btn-success' name='optionsSaveAs' id='optionsSaveAs' type='submit' value='Αποθήκευση ως ...'>    
                </div>
            </div>        
        </form>
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
function rename_images() {
    global $webDir, $theme;
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg');
    foreach($images as $image) {
        if (isset($_POST[$image])) {
            $file_name = $old_image = $_POST[$image];
            $i=0;
            while (is_file("$webDir/template/$theme/img/$file_name")) {
                $i++;
                $name = pathinfo($file_name, PATHINFO_FILENAME);
                $ext =  get_file_extension($file_name);
                $file_name = "$name-$i.$ext";
            }
            if ($old_image != $file_name) {
                if(copy("$webDir/template/$theme/img/$old_image", "$webDir/template/$theme/img/$file_name")){
                    $_POST[$image] = $file_name;
                }                
            }
        }
    }
}
function upload_images() {
    global $webDir, $theme;
    $images = array('bgImage','imageUpload','imageUploadSmall','loginImg');
    foreach($images as $image) {
        if (isset($_FILES[$image]) && is_uploaded_file($_FILES[$image]['tmp_name'])) {
            $file_name = $_FILES[$image]['name'];
            validateUploadedFile($file_name, 2);
            $i=0;
            while (is_file("$webDir/template/$theme/img/$file_name")) {
                $i++;
                $name = pathinfo($file_name, PATHINFO_FILENAME);
                $ext =  get_file_extension($file_name);
                $file_name = "$name-$i.$ext";
            }
            move_uploaded_file($_FILES[$image]['tmp_name'], "$webDir/template/$theme/img/$file_name");
            $_POST[$image] = $file_name;
        }
    }
}
draw($tool_content, 3, null, $head_content);
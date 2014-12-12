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
    if (isset($_FILES['imageUpload']) && is_uploaded_file($_FILES['imageUpload']['tmp_name'])) {
        $file_name = $_FILES['imageUpload']['name'];
        validateUploadedFile($file_name, 2);
        if (!is_file("$webDir/template/$theme/img/$file_name")) {
            move_uploaded_file($_FILES['imageUpload']['tmp_name'], "$webDir/template/$theme/img/$file_name");
        }
        $_POST['custom_logo'] = $file_name;
    }
    if (isset($_FILES['imageUploadSmall']) && is_uploaded_file($_FILES['imageUploadSmall']['tmp_name'])) {
        $file_name = $_FILES['imageUploadSmall']['name'];
        validateUploadedFile($file_name, 2);
        if (!is_file("$webDir/template/$theme/img/$file_name")) {
            move_uploaded_file($_FILES['imageUploadSmall']['tmp_name'], "$webDir/template/$theme/img/$file_name");
        }
        $_POST['custom_logo_small'] = $file_name;
    }
    if (isset($_FILES['bgImage']) && is_uploaded_file($_FILES['bgImage']['tmp_name'])) {
        $file_name = $_FILES['bgImage']['name'];
        validateUploadedFile($file_name, 2);
        if (!is_file("$webDir/template/$theme/img/$file_name")) {
            move_uploaded_file($_FILES['bgImage']['tmp_name'], "$webDir/template/$theme/img/$file_name");
        }
        $_POST['bgImage'] = $file_name;
    }          
    $serialized_data = serialize($_POST);
    Database::get()->query("UPDATE theme_options SET styles = ?s WHERE id = ?d", $serialized_data, get_config('theme_options_id'));
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_POST['delThemeId'])) {
    Database::get()->query("DELETE FROM theme_options WHERE id = ?d", get_config('theme_options_id'));
    Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", 0, 'theme_options_id');
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_POST['themeOptionsName'])) {
    $theme_options_name = $_POST['themeOptionsName'];
    unset($_POST['themeOptionsName']);
    if (isset($_FILES['imageUpload']) && is_uploaded_file($_FILES['imageUpload']['tmp_name'])) {
        $file_name = $_FILES['imageUpload']['name'];
        validateUploadedFile($file_name, 2);
        if (!is_file("$webDir/template/$theme/img/$file_name")) {
            move_uploaded_file($_FILES['imageUpload']['tmp_name'], "$webDir/template/$theme/img/$file_name");
        }
        $_POST['custom_logo'] = $file_name;
    }
    if (isset($_FILES['imageUploadSmall']) && is_uploaded_file($_FILES['imageUploadSmall']['tmp_name'])) {
        $file_name = $_FILES['imageUploadSmall']['name'];
        validateUploadedFile($file_name, 2);
        if (!is_file("$webDir/template/$theme/img/$file_name")) {
            move_uploaded_file($_FILES['imageUploadSmall']['tmp_name'], "$webDir/template/$theme/img/$file_name");
        }
        $_POST['custom_logo_small'] = $file_name;
    }
    if (isset($_FILES['bgImage']) && is_uploaded_file($_FILES['bgImage']['tmp_name'])) {
        $file_name = $_FILES['bgImage']['name'];
        validateUploadedFile($file_name, 2);
        if (!is_file("$webDir/template/$theme/img/$file_name")) {
            move_uploaded_file($_FILES['bgImage']['tmp_name'], "$webDir/template/$theme/img/$file_name");
        }
        $_POST['bgImage'] = $file_name;
    }    
    $serialized_data = serialize($_POST);
    $theme_options_id = Database::get()->query("INSERT INTO theme_options (name, styles) VALUES(?s, ?s)", $theme_options_name, $serialized_data)->lastInsertID;
    Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", $theme_options_id, 'theme_options_id');
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_POST['active_theme_options'])) {
    Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", $_POST['active_theme_options'], 'theme_options_id');
    redirect_to_home_page('modules/admin/theme_options.php');     
} else {
    $nameTools = $langThemeSettings;
    $navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
    load_js('bootstrap-colorpicker');
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
                                            '<input id=\"themeOptionsName\" name=\"themeOptionsName\" type=\"text\" placeholder=\"Theme Options Name\" class=\"form-control\">'+
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
            $('.colorpicker').colorpicker({
                format: 'rgba'
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
    } else {
        $theme_options_styles['leftNavBgColor'] = $theme_options_styles['bgColor'] = '#232C3A';
        $theme_options_styles['leftMenuFontColor'] = $theme_options_styles['leftSubMenuFontColor'] = '#ADADAD';
        $theme_options_styles['leftSubMenuHoverBgColor'] = $theme_options_styles['leftMenuSelectedFontColor'] = $theme_options_styles['leftMenuHoverFontColor'] = "#4da1e4";
        $theme_options_styles['leftSubMenuHoverFontColor'] = "#eee";
        $theme_options_styles['leftMenuBgColor'] = "rgba(0, 0, 0, 0.2)";
        $theme_options_styles['bgType'] = 'repeat';
    }
    $delete_btn = (get_config('theme_options_id')) 
            ? 
            "<form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>
                <div class='form-group'>
                    <div class='col-sm-9 col-sm-offset-3'>
                        <input type='hidden' name='delThemeId' value='".get_config('theme_options_id')."'>
                        <a class='confirmAction btn btn-danger btn-xs' data-title='$langConfirmDelete' data-message='$langThemeSettingsDelete' data-cancel-txt='$langCancel' data-action-txt='$langDelete' data-action-class='btn-danger'>$langDelete</a>
                    </div>
                </div>
            </form>"
            : "";
    if (isset($theme_options_styles['custom_logo'])) {
        $logo_field = "
            <img src='$themeimg/$theme_options_styles[custom_logo]' style='max-height:100px;max-width:150px;'> &nbsp&nbsp<a class='btn btn-xs btn-danger' href='$_SERVER[SCRIPT_NAME]?delete_image=custom_logo'>$langDelete</a>
            <input type='hidden' name='custom_logo' value='$theme_options_styles[custom_logo]'>
        ";
    } else {
       $logo_field = "<input type='file' name='imageUpload' id='imageUpload'>"; 
    }
    if (isset($theme_options_styles['custom_logo_small'])) {
        $small_logo_field = "
            <img src='$themeimg/$theme_options_styles[custom_logo_small]' style='max-height:100px;max-width:150px;'> &nbsp&nbsp<a class='btn btn-xs btn-danger' href='$_SERVER[SCRIPT_NAME]?delete_image=custom_logo_small'>$langDelete</a>
            <input type='hidden' name='custom_logo_small' value='$theme_options_styles[custom_logo_small]'>
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
                <label for='imageUpload' class='col-sm-3 control-label'>Λογότυπο <small>(Κανονικό)</small>:</label>
                <div class='col-sm-9'>
                   $logo_field
                </div>
            </div>
            <div class='form-group'>
                <label for='imageUploadSmall' class='col-sm-3 control-label'>Λογότυπο <small>(Για μικρές οθόνες)</small>:</label>
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
                <label for='imageBg' class='col-sm-3 control-label'>Εικόνα Φόντου:</small>:</label>
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
                          <input type='radio' name='bgType' value='stretch' ".(($theme_options_styles['bgType'] == 'stretch')? 'checked' : '').">
                          $langStretchedImg &nbsp;
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
              <label for='leftSubMenuFontColor' class='col-sm-3 control-label'>Submenu Font Color:</label>
              <div class='col-sm-9'>
                <input name='leftSubMenuFontColor' type='text' class='form-control colorpicker' id='leftSubMenuFontColor' value='$theme_options_styles[leftSubMenuFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftSubMenuHoverBgColor' class='col-sm-3 control-label'>Submenu Hover Background Color:</label>
              <div class='col-sm-9'>
                <input name='leftSubMenuHoverBgColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverBgColor' value='$theme_options_styles[leftSubMenuHoverBgColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftSubMenuHoverFontColor' class='col-sm-3 control-label'>Submenu Font Hover Color:</label>
              <div class='col-sm-9'>
                <input name='leftSubMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftSubMenuHoverFontColor' value='$theme_options_styles[leftSubMenuHoverFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftMenuFontColor' class='col-sm-3 control-label'>Menu Font Color:</label>
              <div class='col-sm-9'>
                <input name='leftMenuFontColor' type='text' class='form-control colorpicker' id='leftMenuFontColor' value='$theme_options_styles[leftMenuFontColor]'>
              </div>
            </div>                     
            <div class='form-group'>
              <label for='leftMenuBgColor' class='col-sm-3 control-label'>Menu Background Color:</label>
              <div class='col-sm-9'>
                <input name='leftMenuBgColor' type='text' class='form-control colorpicker' id='leftMenuBgColor' value='$theme_options_styles[leftMenuBgColor]'>
              </div>
            </div>            
            <div class='form-group'>
              <label for='leftMenuHoverFontColor' class='col-sm-3 control-label'>Menu Font Hover Color:</label>
              <div class='col-sm-9'>
                <input name='leftMenuHoverFontColor' type='text' class='form-control colorpicker' id='leftMenuHoverFontColor' value='$theme_options_styles[leftMenuHoverFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftMenuSelectedFontColor' class='col-sm-3 control-label'>Menu Font Selected Color:</label>
              <div class='col-sm-9'>
                <input name='leftMenuSelectedFontColor' type='text' class='form-control colorpicker' id='leftMenuSelectedFontColor' value='$theme_options_styles[leftMenuSelectedFontColor]'>
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
draw($tool_content, 3, null, $head_content);
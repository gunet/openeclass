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
require_once 'modalconfirmation.php';

if (isset($_POST['optionsSave'])) {
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
    $serialized_data = serialize($_POST);
    $theme_options_id = Database::get()->query("INSERT INTO theme_options (name, styles) VALUES(?s, ?s)", $theme_options_name, $serialized_data)->lastInsertID;
    Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", $theme_options_id, 'theme_options_id');
    redirect_to_home_page('modules/admin/theme_options.php');
} elseif (isset($_POST['active_theme_options'])) {
    Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", $_POST['active_theme_options'], 'theme_options_id');
    redirect_to_home_page('modules/admin/theme_options.php');     
} else {
    $nameTools = 'Theme Options';
    $navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
    load_js('bootstrap-colorpicker');
    $head_content .= "
    <script>
        $(function(){
            $('#optionsSaveAs').click(function (e)
            {
                e.preventDefault();
                bootbox.dialog({
                    title: 'Αποθήκευση ως ...',
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
                                    $('#themeOptionsName').after('<span class=\"help-block\">Το πεδίο είναι απαραίτητο</span>');
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
    $themes_arr[0] = "---- Default Theme Options ----";
    foreach ($all_themes as $row) {
        $themes_arr[$row->id] = $row->name;
    }
    
    if (get_config('theme_options_id')) {
        $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", get_config('theme_options_id'));
        $theme_options_styles = unserialize($theme_options->styles);
    } else {
        $theme_options_styles['leftNavBgColor'] = $theme_options_styles['bgColor'] = '#232C3A';
        $theme_options_styles['leftNavFontColor'] = '#ADADAD';
        $theme_options_styles['leftNavHoverBgColor'] = $theme_options_styles['leftNavMainCatSelectedFontColor'] = $theme_options_styles['leftNavMainCatHoverFontColor'] = "#4da1e4";
        $theme_options_styles['leftNavHoverFontColor'] = "#eee";
        $theme_options_styles['leftNavHoverBgColor'] = "rgba(0, 0, 0, 0.2)";
    }
    $delete_btn = (get_config('theme_options_id')) 
            ? 
            "<form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>
                <div class='form-group'>
                    <div class='col-sm-9 col-sm-offset-3'>
                        <input type='hidden' name='delThemeId' value='".get_config('theme_options_id')."'>
                        <a class='confirmAction btn btn-danger btn-xs' data-title='Επιβεβαίωση διαγραφής' data-message='Επιβεβαίωση διαγραφής θεματικών επιλογών' data-cancel-txt='Ακύρωση' data-action-txt='Διαγραφή' data-action-class='btn-danger'>$langDelete</a>
                    </div>
                </div>
            </form>"
            : "";
    @$tool_content .= "
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
            <div class='form-group'>
                <label for='bgColor' class='col-sm-3 control-label'>Active Theme Options:</label>
                <div class='col-sm-9'>
                    ".  selection($themes_arr, 'active_theme_options', get_config('theme_options_id'), 'class="form-control form-submit"')."
                </div>
            </div>
        </form>
        $delete_btn
    </div>
    <div class='form-wrapper'>
        <form id='theme_options_form' class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
            <h3>General Options</h3>
            <div class='form-group'>
              <label for='bgColor' class='col-sm-3 control-label'>Background Color :</label>
              <div class='col-sm-9'>
                <input name='bgColor' type='text' class='form-control colorpicker' id='bgColor' value='$theme_options_styles[bgColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='bgImage' class='col-sm-3 control-label'>Background Image URL:</label>
              <div class='col-sm-9'>
                <input name='bgImage' type='text' class='form-control' id='bgImage' value='$theme_options_styles[bgImage]'>
              </div>
            </div>
            <hr>
            <h3>Left Navigation Options</h3>            
            <div class='form-group'>
              <label for='leftNavBgColor' class='col-sm-3 control-label'>Background Color:</label>
              <div class='col-sm-9'>
                <input name='leftNavBgColor' type='text' class='form-control colorpicker' id='leftNavBgColor' value='$theme_options_styles[leftNavBgColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftNavFontColor' class='col-sm-3 control-label'>Font Color:</label>
              <div class='col-sm-9'>
                <input name='leftNavFontColor' type='text' class='form-control colorpicker' id='leftNavFontColor' value='$theme_options_styles[leftNavFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftNavHoverBgColor' class='col-sm-3 control-label'>Hover Background Color:</label>
              <div class='col-sm-9'>
                <input name='leftNavHoverBgColor' type='text' class='form-control colorpicker' id='leftNavHoverBgColor' value='$theme_options_styles[leftNavHoverBgColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftNavHoverFontColor' class='col-sm-3 control-label'>Font Hover Color:</label>
              <div class='col-sm-9'>
                <input name='leftNavHoverFontColor' type='text' class='form-control colorpicker' id='leftNavHoverFontColor' value='$theme_options_styles[leftNavHoverFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftNavMainCatBgColor' class='col-sm-3 control-label'>Main Category Background Color:</label>
              <div class='col-sm-9'>
                <input name='leftNavMainCatBgColor' type='text' class='form-control colorpicker' id='leftNavMainCatBgColor' value='$theme_options_styles[leftNavMainCatBgColor]'>
              </div>
            </div>            
            <div class='form-group'>
              <label for='leftNavMainCatHoverFontColor' class='col-sm-3 control-label'>Main Category Font Hover Color:</label>
              <div class='col-sm-9'>
                <input name='leftNavMainCatHoverFontColor' type='text' class='form-control colorpicker' id='leftNavMainCatHoverFontColor' value='$theme_options_styles[leftNavMainCatHoverFontColor]'>
              </div>
            </div>
            <div class='form-group'>
              <label for='leftNavMainCatSelectedFontColor' class='col-sm-3 control-label'>Main Category Font Selected Color:</label>
              <div class='col-sm-9'>
                <input name='leftNavMainCatSelectedFontColor' type='text' class='form-control colorpicker' id='leftNavMainCatSelectedFontColor' value='$theme_options_styles[leftNavMainCatSelectedFontColor]'>
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
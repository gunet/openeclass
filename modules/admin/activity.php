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

$require_admin = true;
$require_help = true;
require_once '../../include/baseTheme.php';

$helpTopic = 'users_administration';

$toolName = $langAdmin;
$pageName = $langActivityCourse;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_POST['toReorder'])) {
    reorder_table('activity_heading', null, null, getDirectReference($_POST['toReorder']),
        isset($_POST['prevReorder'])? getDirectReference($_POST['prevReorder']): null);
    exit;
} elseif (isset($_POST['action']) and $_POST['action'] == 'delete') {
    if (isset($_GET['delete'])) {
        $id = getDirectReference($_GET['delete']);
        if ($id) {
            Database::get()->query('DELETE FROM activity_heading WHERE id = ?d', $id);
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
        }
    }
    redirect_to_home_page('modules/admin/activity.php');
} elseif (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_POST['id'])) {
        $id = getDirectReference($_POST['id']);
    }
    $headings = array();
    foreach ($session->active_ui_languages as $key => $langcode) {
        $headings[$langcode] = isset($_POST['heading'][$langcode])?
            canonicalize_whitespace($_POST['heading'][$langcode]): '';
    }
    if (isset($id)) {
        Database::get()->query('UPDATE activity_heading SET required = ?d, heading = ?s WHERE id = ?d',
            intval(!!$_POST['required']), serialize($headings), $id);
    } else {
        $maxOrder = Database::get()->querySingle('SELECT MAX(`order`) AS maxOrder FROM activity_heading')->maxOrder;
        Database::get()->query('INSERT INTO activity_heading SET required = ?d, heading = ?s, `order` = ?d',
            intval(!!$_POST['required']), serialize($headings), $maxOrder + 1);
    }
    Session::flash('message',$langFaqEditSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/activity.php');
} elseif (isset($_GET['add']) or isset($_GET['edit'])) {
    $idInput = '';
    $headings = array();
    $checked = '';
    if (isset($_GET['edit'])) {
        $id = getDirectReference($_GET['edit']);
        $idInput = "<input type='hidden' name='id' value='$_GET[edit]'>";
        $item = Database::get()->querySingle('SELECT * FROM activity_heading WHERE id = ?d', $id);
        if (!$item) {
            Session::flash('message',$langGeneralError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/activity.php');
        }
        $headings = unserialize($item->heading);
        $checked = $item->required? ' checked': '';
    }
    $navigation[] = array('url' => 'activity.php', 'name' => $langActivityCourse);
    $toolName = $langActivityCourseAdd;
    $tool_content .= "<div class='row'>
       <div class='col-lg-6 col-12 mt-3'>
        <div class='form-wrapper form-edit rounded border-0 px-0'>
        <form role='form' class='form-horizontal' method='post' action='activity.php'>
          $idInput
          <fieldset><legend class='mb-0' aria-label='$langForm'></legend>";

    foreach ($session->active_ui_languages as $key => $langcode) {
        if (isset($headings[$langcode])) {
            $value = q($headings[$langcode]);
        } else {
            $value = '';
        }
        $tool_content .= "
            <div class='form-group mb-4'>
              <label for='heading-$langcode' class='col-sm-12 control-label-notes' for='heading-$langcode'>$langTitle (" . $langNameOfLang[langcode_to_name($langcode)] . ")</label>
              <div class='col-sm-12'>
                <input class='form-control' type='text' name='heading[$langcode]' id='heading-$langcode' value='$value' placeholder='$langTitle...'>
              </div>
            </div>";
    }

    $tool_content .= "
  
            <div class='form-group mt-4'>
              <div class='col-sm-9 col-sm-offset-3 checkbox'>
                <label class='label-container' aria-label='$langSelect'>
                  <input type='checkbox' name='required' id='required' value='1' $checked>
                  <span class='checkmark'></span>
                  $langCMeta[compulsory]
                </label>
              </div>
            </div>

            

            <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2'>
                <input class='btn submitAdminBtn' type='submit' name='submit' value='" . q($langSubmit) . "'>
                <a href='activity.php' class='btn cancelAdminBtn'>$langCancel</a>
            </div>
          </fieldset>
          ". generate_csrf_token_form_field() ."
        </form>
      </div></div>
      <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
      <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
            </div></div>";
} else {
    load_js('sortable/Sortable.min.js');
    $head_content .= "
<script>
$(function() {
    $('.confirm-delete').on('click', function (e) {
        var href = '\'' + $(this).attr('href') + '\'';
        e.preventDefault();
        bootbox.dialog({
            message: '" . js_escape($langConfirmDelete) . "',
            title: '" . js_escape($langDelete) . "',
            buttons: {
                cancel_btn: {
                    label: '" . js_escape($langCancel) . "',
                    className: 'cancelAdminBtn'
                },
                action_btn: {
                    label: '" . js_escape($langDelete) . "',
                    className: 'deleteAdminBtn',
                    callback: function () {
                        $('<form method=post action=' + href + '><input type=hidden name=action value=delete></form>').appendTo('body').submit();
                    }
                }
            }
        });
    });

    Sortable.create(document.getElementById('headings'), {
        handle: '.fa-arrows',
        animation: 150,
        onEnd: function (evt) {
            var itemEl = $(evt.item);
            var idReorder = itemEl.attr('data-id');
            var prevIdReorder = itemEl.prev().attr('data-id');
            $.ajax({
                type: 'post',
                dataType: 'text',
                data: {
                    toReorder: idReorder,
                    prevReorder: prevIdReorder,
                },
                success: function(data) {
                    $('.indexing').each(function (i){
                        $(this).html(i+1);
                    });
                },
            })
        }
    });
});
</script>";

    $action_bar = action_bar(array(
        array('title' => $langAdd,
              'url' => 'activity.php?add=true',
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success')
        ));

    $tool_content .= $action_bar;
    $headings = Database::get()->queryArray('SELECT * FROM activity_heading ORDER BY `order`');
    if (count($headings)) {
        $tool_content .= "<div class='panel-group' id='headings'>";
        foreach ($headings as $item) {
            $type = $item->required? $langCMeta['compulsory']: $langOptional;
            $headings = unserialize($item->heading);
            if ($headings[$language]) {
                $heading = $headings[$language];
            } elseif ($headings['en']) {
                $heading = $headings['en'];
            } elseif ($headings['el']) {
                $heading = $headings['el'];
            } else {
                $heading = $headings[array_keys($headings)[0]];
            }
            $heading = q($heading);
            $indirectId = getIndirectReference($item->id);
            $tool_content .= "
              <div class='card panelCard card-default px-lg-4 py-lg-3 mt-3' data-id='$indirectId'>
                <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                             
                    <h3>$heading</h3>
                                
                    <div class='d-flex justify-content-end align-items-center'>$type " .
                        icon('fa-edit ps-2 pe-2', $langEdit, 'activity.php?edit=' . $indirectId) . "
                        <a class='confirm-delete pe-2' href='activity.php?delete=$indirectId' aria-label='$langDelete' title='$langDelete' data-bs-toggle='tooltip'>
                            <span class='fa-solid fa-xmark delete_btn text-danger'></span>
                            <span class='sr-only'>$langDelete</span></a>
                            <span class='fa fa-arrows' data-bs-toggle='tooltip' data-bs-placement='top' title='$langReorder'></span>
                    </div>                             
                </div>
                <div class='card-body'>";
                    foreach ($headings as $lang => $msg) {
                        $tool_content .= "
                          <div class='row'>
                            <div class='col-md-2 col-4 text-end'>
                              <strong>" . $langNameOfLang[langcode_to_name($lang)] . ":</strong>
                            </div>
                            <div class='col-md-10 col-8'>" . q($msg) . "</div>
                          </div>";
                    }
                    $tool_content .= "
                </div>
              </div>";
            }
        $tool_content .= "</div>";
    } else {
        $tool_content .= "
                        <div class='col-12'>
                            <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoActivityHeadings</span>
                            </div>
                        </div>";
    }
}

draw($tool_content, null, null, $head_content);

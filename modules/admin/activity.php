<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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

$toolName = $langActivityCourse;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_POST['toReorder'])) {
    reorder_table('activity_title', null, null, getDirectReference($_POST['toReorder']),
        isset($_POST['prevReorder'])? getDirectReference($_POST['prevReorder']): null);
    exit;
} elseif (isset($_POST['action']) and $_POST['action'] == 'delete') {
    if (isset($_GET['delete'])) {
        $id = getDirectReference($_GET['delete']);
        if ($id) {
            Database::get()->query('DELETE FROM activity_title WHERE id = ?d', $id);
            Session::Messages($langGlossaryDeleted, 'alert-success');
        }
    }
    redirect_to_home_page('modules/admin/activity.php');
} elseif (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_POST['id'])) {
        $id = getDirectReference($_POST['id']);
    }
    $titles = array();
    foreach ($session->active_ui_languages as $key => $langcode) {
        $titles[$langcode] = isset($_POST['title'][$langcode])?
            canonicalize_whitespace($_POST['title'][$langcode]): '';
    }
    if (isset($id)) {
        Database::get()->query('UPDATE activity_title SET required = ?d, title = ?s WHERE id = ?d',
            intval(!!$_POST['required']), serialize($titles), $id);
    } else {
        $maxOrder = Database::get()->querySingle('SELECT MAX(`order`) AS maxOrder FROM activity_title')->maxOrder;
        Database::get()->query('INSERT INTO activity_title SET required = ?d, title = ?s, `order` = ?d',
            intval(!!$_POST['required']), serialize($titles), $maxOrder + 1);
    }
    Session::Messages($langFaqEditSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/activity.php');
} elseif (isset($_GET['add']) or isset($_GET['edit'])) {
    $idInput = '';
    $titles = array();
    $checked = '';
    if (isset($_GET['edit'])) {
        $id = getDirectReference($_GET['edit']);
        $idInput = "<input type='hidden' name='id' value='$_GET[edit]'>";
        $item = Database::get()->querySingle('SELECT * FROM activity_title WHERE id = ?d', $id);
        if (!$item) {
            Session::Messages($langGeneralError, 'alert-danger');
            redirect_to_home_page('modules/admin/activity.php');
        }
        $titles = unserialize($item->title);
        $checked = $item->required? ' checked': '';
    }
    $navigation[] = array('url' => 'activity.php', 'name' => $langActivityCourse);
    $toolName = $langActivityCourseAdd;
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => 'activity.php',
              'icon' => 'fa-reply',
              'level' => 'primary-label'))) . "
      <div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='activity.php'>
          $idInput
          <fieldset>";

    foreach ($session->active_ui_languages as $key => $langcode) {
        if (isset($titles[$langcode])) {
            $value = q($titles[$langcode]);
        } else {
            $value = '';
        }
        $tool_content .= "
            <div class='form-group'>
              <label class='col-sm-3 control-label' for='title-$langcode'>$langTitle (" . $langNameOfLang[langcode_to_name($langcode)] . "):</label>
              <div class='col-sm-9'>
                <input class='form-control' type='text' name='title[$langcode]' id='name-$langcode' value='$value'>
              </div>
            </div>";
    }

    $tool_content .= "
            <div class='form-group'>
              <div class='col-sm-9 col-sm-offset-3 checkbox'>
                <label>
                  <input type='checkbox' name='required' id='required' value='1'$checked>
                  $langCMeta[compulsory]
                </label>
              </div>
            </div>
            <div class='form-group'>
               <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='submit' value='" . q($langSubmit) . "'>
                <a href='activity.php' class='btn btn-default'>$langCancel</a>
              </div>
            </div>
          </fieldset>
          ". generate_csrf_token_form_field() ."
        </form>
      </div>";
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
                    className: 'btn-default'
                },
                action_btn: {
                    label: '" . js_escape($langDelete) . "',
                    className: 'btn-danger',
                    callback: function () {
                        $('<form method=post action=' + href + '><input type=hidden name=action value=delete></form>').appendTo('body').submit();
                    }
                }
            }
        });
    });

    Sortable.create(document.getElementById('titles'), {
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

    $tool_content .= action_bar(array(
        array('title' => $langAdd,
              'url' => 'activity.php?add=true',
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        array('title' => $langBack,
              'url' => 'index.php',
              'icon' => 'fa-reply',
              'level' => 'primary-label')));

    $titles = Database::get()->queryArray('SELECT * FROM activity_title ORDER BY `order`');
    if (count($titles)) {
        $tool_content .= "<div class='panel-group' id='titles'>";
        foreach ($titles as $item) {
            $type = $item->required? $langCMeta['compulsory']: $langOptional;
            $titles = unserialize($item->title);
            if ($titles[$language]) {
                $title = $titles[$language];
            } elseif ($titles['en']) {
                $title = $titles['en'];
            } elseif ($titles['el']) {
                $title = $titles['el'];
            } else {
                $title = $titles[array_keys($titles)[0]];
            }
            $title = q($title);
            $indirectId = getIndirectReference($item->id);
            $tool_content .= "
      <div class='panel panel-default' data-id='$indirectId'>
        <div class='panel-heading'>
          $title
          <div class='pull-right'>$type " .
            icon('fa-edit', $langEdit, 'activity.php?edit=' . $indirectId) . "
            <a class='confirm-delete' href='activity.php?delete=$indirectId' title='$langDelete' data-toggle='tooltip'>
                <span class='fa fa-times delete_btn text-danger'></span><span class='sr-only'>$langDelete</span></a>
            <span class='fa fa-arrows' data-toggle='tooltip' data-placement='top' title='$langReorder'></span>
          </div>
        </div>
        <div class='panel-body'>";
            foreach ($titles as $lang => $msg) {
                $tool_content .= "
          <div class='row'>
            <div class='col-xs-2 text-right'>
              <strong>" . $langNameOfLang[langcode_to_name($lang)] . ":</strong>
            </div>
            <div class='col-xs-10'>" . q($msg) . "</div>
          </div>";
            }
            $tool_content .= "
        </div>
      </div>";
        }
        $tool_content .= "</div>";

    } else {
        $tool_content .= "<div class='alert alert-warning text-center'>$langNoActivityHeadings</div>";
    }
}

draw($tool_content, 3, null, $head_content);

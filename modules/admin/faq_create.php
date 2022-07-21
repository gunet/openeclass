<?php

/* ========================================================================
 * Open eClass 3.5
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


$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toDelete'])) {
        Database::get()->query("UPDATE `faq` SET `order` = `order` - 1 WHERE `order` > ?d", $_POST['oldOrder']);
        Database::get()->query("DELETE FROM faq WHERE `id` = ?d", $_POST['toDelete']);
    } elseif (isset($_POST['toReorder'])) {
        reorder_table('faq', null, null, $_POST['toReorder'],
            isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
    }
    exit;
}

if (isset($_POST['submitFaq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    $top = Database::get()->querySingle("SELECT MAX(`order`) as max FROM `faq`")->max;

    Database::get()->query("INSERT INTO faq (title, body, `order`) VALUES (?s, ?s, ?d)", $question, $answer, $top + 1);

    //Session::Messages("$langFaqAddSuccess", 'alert-success');
    Session::flash('message',"$langFaqAddSuccess"); 
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/faq_create.php");
}

if (isset($_POST['modifyFaq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    $record = $_POST['id'];

    Database::get()->query("UPDATE faq SET `title`=?s, `body`=?s WHERE `id`=?d", $question, $answer, $record);

    //Session::Messages("$langFaqEditSuccess", 'alert-success');
    Session::flash('message',"$langFaqEditSuccess"); 
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/faq_create.php");
}

if (isset($_GET['faq']) && $_GET['faq'] == 'delete') {
    $record = $_GET['id'];

    Database::get()->query("DELETE FROM faq WHERE `id`=?d", $record);

    //Session::Messages("$langFaqDeleteSuccess", 'alert-success');
    Session::flash('message',"$langFaqDeleteSuccess"); 
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/faq_create.php");
}

load_js('sortable/Sortable.min.js');

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminCreateFaq;
$pageName = $langAdminCreateFaq;

$head_content .= "
    <script type='text/javascript'>
    $(document).ready(function() {

      $(document).on('click', '.expand:not(.revert)', function(e) {
        e.preventDefault();
        $('.faq-section .panel-collapse:not(.in)').collapse('show');
        $(this).toggleClass('revert');
        $(this).children().eq(0).toggleClass('fa-plus-circle').toggleClass('fa-minus-circle');
        $(this).children().eq(1).html('$langFaqCloseAll');
      });

      $(document).on('click', '.expand.revert', function(e) {
        e.preventDefault();
        $('.faq-section .panel-collapse.in').collapse('hide');
        $(this).toggleClass('revert');
        $(this).children().eq(0).toggleClass('fa-minus-circle').toggleClass('fa-plus-circle');
        $(this).children().eq(1).html('$langFaqExpandAll');
      });

      $(document).on('click', '.forDelete', function(e) {
        e.preventDefault();
        idDelete = $(this).data('id');
        idOrder = $(this).data('order');
        elem_rem = $(this).parents('.list-group-item');
        var ids = [];
        $('.faq-section .list-group-item').each(function () {
          ids.push($(this).data('id'));
        });
        bootbox.confirm('".js_escape($langSureToDelAnnounce)."', function(result) {
          if (result) {

            $.ajax({
              type: 'post',
              data: { 
                      toDelete: idDelete,
                      oldOrder: idOrder
                    },
              success: function() {

                elem_rem.remove();
                
                $('.indexing').each(function (i){
                  $(this).html(i+1);
                });

                $('.tooltip').remove();

                moreDeletes = $('.alert-success').length;

                if (moreDeletes > 0){
                  $('.alert-success').html('$langFaqDeleteSuccess');
                } else {
                  $('.row.action_bar').before('<div class=\'alert alert-success\'>$langFaqDeleteSuccess</div>');
                }

              }
            });
          }
        });
      });

      Sortable.create(accordion, {
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
              }
            })
          }
          
        });
    });
  </script>
";


$data['action_bar'] = action_bar(
    [
        [
            'title' => $langFaqAdd,
            'url' => $_SERVER['SCRIPT_NAME'].'?faq=new',
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ],
        [
            'title' => $langFaqExpandAll,
            'url' => "#",
            'class' => 'expand',
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label'
        ],
        [
            'title' => $langBack,
            'url' => $_SERVER['SCRIPT_NAME'],
            'icon' => 'fa-reply',
            'level' => 'primary-label'
        ]
    ],false);

$data['faqs'] = Database::get()->queryArray("SELECT * FROM faq ORDER BY `order` ASC");

$data['new'] = isset($_GET['faq']) && $_GET['faq'] == 'new';
$data['modify'] = isset($_GET['faq']) && $_GET['faq'] == 'modify';


if ($data['modify']) {
    $data['id'] = $_GET['id'];
    $data['faq_mod'] = Database::get()->querySingle("SELECT * FROM `faq` WHERE `id`=?d", $_GET['id']);
    $data['editor'] = rich_text_editor('answer', 5, 40, $data['faq_mod']->body );
}

if ($data['new']) {
    $data['editor'] = rich_text_editor('answer', 5, 40, '' );
}

$data['menuTypeID'] = isset($uid) && $uid ? 1 : 0;

view('admin.other.faq_create', $data);

<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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



if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

  if (isset($_POST['toDelete'])){
    Database::get()->query("UPDATE `faq` SET `order`=`order` - 1 WHERE `order`>?d", $_POST['oldOrder']);
    Database::get()->query("DELETE FROM faq WHERE `id`=?d", $_POST['toDelete']);
  }

  if (isset($_POST['toReorder'])){

    if ($_POST['newIndex'] < $_POST['oldIndex']){
      Database::get()->query("UPDATE `faq` SET `order`=`order` + 1 WHERE `order`>=?d AND `order`<?d", $_POST['newIndex'] + 1, $_POST['oldIndex'] + 1);
    }elseif ($_POST['newIndex'] > $_POST['oldIndex']) {
      Database::get()->query("UPDATE `faq` SET `order`=`order` - 1 WHERE `order`<=?d AND `order`>?d", $_POST['newIndex'] + 1, $_POST['oldIndex'] + 1);
    }

    Database::get()->query("UPDATE `faq` SET `order`=?d WHERE `id`=?d ", $_POST['newIndex'] + 1, $_POST['toReorder']);
    
  }

  exit();
}

if (isset($_POST['submitFaq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    $top = Database::get()->querySingle("SELECT MAX(`order`) as max FROM `faq`")->max;

    Database::get()->query("INSERT INTO faq (title, body, `order`) VALUES (?s, ?s, ?d)", $question, $answer, $top + 1);

    Session::Messages("$langFaqAddSuccess", 'alert-success');
    redirect_to_home_page("modules/admin/faq_create.php");
}

if (isset($_POST['modifyFaq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    $record = $_POST['id'];

    Database::get()->query("UPDATE faq SET `title`=?s, `body`=?s WHERE `id`=?d", $question, $answer, $record);

    Session::Messages("$langFaqEditSuccess", 'alert-success');
    redirect_to_home_page("modules/admin/faq_create.php");
}

if (isset($_GET['faq']) && $_GET['faq'] == 'delete') {
    $record = $_GET['id'];

    Database::get()->query("DELETE FROM faq WHERE `id`=?d", $record);

    Session::Messages("$langFaqDeleteSuccess", 'alert-success');
    redirect_to_home_page("modules/admin/faq_create.php");
}

load_js('sortable/Sortable.min.js');

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminCreateFaq;
$pageName = $langAdminCreateFaq;

$tool_content = action_bar(array(
                            array('title' => $langFaqAdd,
                                  'url' => $_SERVER['SCRIPT_NAME'].'?faq=new',
                                  'icon' => 'fa-plus-circle',
                                  'level' => 'primary-label',
                                  'button-class' => 'btn-success'),
                            array('title' => 'Expand All',
                                  'url' => "#",
                                  'class' => 'expand',
                                  'icon' => 'fa-plus-circle',
                                  'level' => 'primary-label'),
                            array('title' => $langBack,
                                  'url' => $_SERVER['SCRIPT_NAME'],
                                  'icon' => 'fa-reply',
                                  'level' => 'primary-label')),false);

if (isset($_GET['faq']) && $_GET['faq'] != 'delete') {

  $submitBtn = 'submitFaq';
  $submitBtnValue = $langSubmit;
  $id = "";
  $title = "";
  $body = "";
  
  if ($_GET['faq'] == 'modify') {

    $submitBtn = 'modifyFaq';
    $submitBtnValue = $langSave;
    $id = "<input type='hidden' name='id' value='$_GET[id]'>";

    $faq = Database::get()->querySingle("SELECT * FROM `faq` WHERE `id`=?d", $_GET['id']);
    $title = $faq->title;
    $body = $faq->body;

  }

  $tool_content .= "
    <div class='row'>
        <div class='col-xs-12'>
        <div class='form-wrapper'>
          <form role='form' class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>
              $id
              <div class='form-group'>
                  <label for='question' class='col-sm-2 control-label'>$langFaqQuestion <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                  <div class='col-sm-10'>
                      <input class='form-control' type='text' name='question' value='$title' />
                  </div>
              </div>
              <div class='form-group'>
                  <label for='answer' class='col-sm-2 control-label'>$langFaqAnswer <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                  <div class='col-sm-10'>" . rich_text_editor('answer', 5, 40, $body) . "</div>
              </div>
              <div class='form-group'>
                  <div class='col-sm-offset-2 col-sm-10'>
                      <sup><small>(<span class='text-danger'>*</span>)</small></sup> <small class='text-muted'>$langCPFFieldRequired</small>
                  </div>
              </div>
              <div class='form-group'>
                <div class='col-sm-offset-2 col-sm-10'>".form_buttons(array(
                  array(
                      'text' => $submitBtnValue,
                      'name' => $submitBtn,
                      'value'=> $submitBtnValue
                  ),
                  array(
                      'href' => "$_SERVER[SCRIPT_NAME]",
                  )
              ))."</div>
              </div>
          </form>
        </div>
      </div>
      </div>
  ";
  draw($tool_content, 3, null, $head_content);
  exit();

}

$faqs = Database::get()->queryArray("SELECT * FROM faq ORDER BY `order` ASC");

$faqCounter = 0;

$tool_content .= "
  <div class='row'>
      <div class='col-xs-12'>
        <div class='panel'>
          <div class='panel-group faq-section  list-group' id='accordion' role='tablist' aria-multiselectable='true'>";

          if (count($faqs) == 0) {
            $tool_content .= "

              <div class='panel list-group-item'>
                <div class='text-center text-muted'><em>$langFaqNoEntries</em> <br><br> <em>$langFaqAddNew</em></div>
              </div>
              ";
          } else {

            foreach ($faqs as $faq) {
              $faqCounter++;
              $tool_content .= "

              <div class='panel list-group-item' data-id='$faq->id'>
                <div class='panel-heading' role='tab' id='heading-$faq->id'>
                  <h4 class='panel-title'>
                    <a role='button' data-toggle='collapse' data-parent='#accordion' href='#faq-$faq->id' aria-expanded='true' aria-controls='#$faq->id'>
                        <span class='indexing'>$faqCounter.</span>$faq->title <span class='caret'></span>
                    </a>
                    <a class='forDelete' href='javascript:void(0);' data-id='$faq->id' data-order='$faq->order'><span class='fa fa-times text-danger pull-right' data-toggle='tooltip' data-placement='top' title='$langDelete'></span></a>
                    <a href='javascript:void(0);'><span class='fa fa-arrows pull-right'></span></a>
                    <a href='$_SERVER[SCRIPT_NAME]?faq=modify&id=$faq->id'><span class='fa fa-pencil-square pull-right' data-toggle='tooltip' data-placement='top' title='$langEdit'></span></a>
                  </h4>
                </div>
                <div id='faq-$faq->id' class='panel-collapse collapse' role='tabpanel' aria-labelledby='heading-$faq->id'>
                  <div class='panel-body'>
                    <p><strong><u>$langFaqAnswer:</u></strong></p>
                    $faq->body
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
        $(this).parents('.list-group-item').remove();
        var ids = [];
        $('.faq-section .list-group-item').each(function () {
          ids.push($(this).data('id'));
        });
        $.ajax({
          type: 'post',
          data: { 
                  toDelete: idDelete,
                  oldOrder: idOrder
                },
          success: function() {
            
            $('.indexing').each(function (i){
              $(this).html(i+1);
            });

            $('tooltip').remove();
            moreDeletes = $('.alert-success').length;
            if (moreDeletes > 0){
              $('.alert-success').html('$langFaqDeleteSuccess');
            } else {
              $('.row.action_bar').before('<div class=\'alert alert-success\'>$langFaqDeleteSuccess</div>');
            }
          }
        });
      });

      Sortable.create(accordion, {
          handle: '.fa-arrows',
          animation: 150,
          onEnd: function (evt) {

            var itemEl = $(evt.item);
            var idReorder = itemEl.attr('data-id');

            $.ajax({
              type: 'post',
              dataType: 'text',
              data: { 
                      toReorder: idReorder,
                      oldIndex: evt.oldIndex,
                      newIndex: evt.newIndex
                    },
              success: function(data) {
                $('.indexing').each(function (i){
                  $(this).html(i+1);
                });

             /* moreDeletes = $('.alert-success').length;
                if (moreDeletes > 0){
                  $('.alert-success').html('$langFaqReorderSuccess');
                } else {
                  $('.row.action_bar').before('<div class=\'alert alert-success\'>$langFaqReorderSuccess</div>');
                } */
              }
            })

          }
        });
    });
  </script>
";

draw($tool_content, 3, null, $head_content);
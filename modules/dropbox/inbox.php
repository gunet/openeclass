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

$require_login = TRUE;
if(isset($_GET['course'])) {//course messages
    $require_current_course = TRUE;
} else {//personal messages
    $require_current_course = FALSE;
}
$guest_allowed = FALSE;

include '../../include/baseTheme.php';
include 'include/lib/fileDisplayLib.inc.php';
require_once("class.msg.php");

if (!isset($course_id)) {
    $course_id = 0;
}

if (isset($_GET['mid'])) {
    $personal_msgs_allowed = get_config('dropbox_allow_personal_messages');

    $mid = intval($_GET['mid']);
    $msg = new Msg($mid, $uid, 'msg_view');
    if (!$msg->error) {

        $urlstr = '';
        if ($course_id != 0) {
            $urlstr = "?course=".$course_code;
        }
        $out = action_bar(array(
                            array('title' => $langReply,
                                  'url' => "javascript:void(0)",
                                  'icon' => 'fa-edit',
                                  'button-class' => 'btn-reply btn-default',
                                  'level' => 'primary-label'),
                            array('title' => $langBack,
                                  'url' => "inbox.php".$urlstr,
                                  'icon' => 'fa-reply',
                                  'button-class' => 'back_index btn-default',
                                  'level' => 'primary-label'),
                            array('title' => $langDelete,
                                    'url' => 'javascript:void(0)',
                                    'icon' => 'fa-times',
                                    'button-class' => 'delete_in_inner',
                                    'link-attrs' => "data-id='$msg->id'")
                        ));
        $recipients = '';
        foreach ($msg->recipients as $r) {
            if ($r != $msg->author_id) {
                $recipients .= display_user($r, false, false, "outtabs").'<br/>';
            }
        }
        $out .= "<div id='del_msg'></div>
                <div id='msg_area'>
                    <div class='panel panel-primary'>
                        <div class='panel-body'>
                            <div class='row  margin-bottom-thin'>
                                <div class='col-sm-2'>
                                    <strong>$langSubject:</strong>
                                </div>
                                <div class='col-sm-10'>
                                    ".q($msg->subject)."
                                </div>
                            </div>";
        if ($msg->course_id != 0 && $course_id == 0) {
            $out .= "       <div class='row  margin-bottom-thin'>
                                <div class='col-sm-2'>
                                    <strong>$langCourse:</strong>
                                </div>
                                <div class='col-sm-10'>
                                    <a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($msg->course_id)."\">".course_id_to_title($msg->course_id)."</a>
                                </div>
                            </div>";
        }
        $out .= "
                            <div class='row  margin-bottom-thin'>
                                <div class='col-sm-2'>
                                    <strong>$langDate:</strong>
                                </div>
                                <div class='col-sm-10'>
                                    ".nice_format(date('Y-m-d H:i:s',$msg->timestamp), true)."
                                </div>
                            </div>
                            <div class='row  margin-bottom-thin'>
                                <div class='col-sm-2'>
                                    <strong>$langSender:</strong>
                                </div>
                                <div class='col-sm-10'>
                                    ".display_user($msg->author_id, false, false, "outtabs")."
                                </div>
                            </div>
                            <div class='row  margin-bottom-thin'>
                                <div class='col-sm-2'>
                                    <strong>$langRecipients:</strong>
                                </div>
                                <div class='col-sm-10'>
                                    $recipients
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='panel panel-default'>
                        <div class='panel-heading'>$langMessage</div>
                        <div class='panel-body'>
                            <div class='row  margin-bottom-thin'>
                                <div class='col-xs-12'>
                                    ".standard_text_escape($msg->body)."
                                </div>
                            </div>";
                if ($msg->filename != '' && $msg->filesize != 0) {
                   $out .= "<hr>
                            <div class='row  margin-top-thin'>
                                <div class='col-sm-2'>
                                    $langAttachedFile
                                </div>
                                <div class='col-sm-10'>
                                 <a href=\"dropbox_download.php?course=".course_id_to_code($msg->course_id)."&amp;id=$msg->id\" class=\"outtabs\" target=\"_blank\">$msg->real_filename
                    &nbsp<i class='fa fa-save'></i></a>&nbsp;&nbsp;(".format_file_size($msg->filesize).")
                                </div>
                            </div>";
               }
               $out .= "</div>
                    </div>";

        /*****Reply Form****/
        if ($msg->course_id == 0 && !$personal_msgs_allowed) {
            //do not show reply form when personal messages are not allowed
        } else {
            $out .= "<div class='form-wrapper' id='replyBox' style='display:none;'>";
            if ($course_id == 0) {
                $out .= "<form method='post' class='form-horizontal' role='form' action='dropbox_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
                if ($msg->course_id != 0) {//thread belonging to a course viewed from the central ui
                    $out .= "<input type='hidden' name='course' value='".course_id_to_code($msg->course_id)."' />";
                }
            } else {
                $out .= "<form method='post' class='form-horizontal' role='form' action='dropbox_submit.php?course=$course_code' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
            }
            //hidden variables needed in case of a reply
            foreach ($msg->recipients as $rec) {
                if ($rec != $uid) {
                    $out .= "<input type='hidden' name='recipients[]' value='$rec' />";
                }
            }
            $out .= generate_csrf_token_form_field() . "
                <fieldset>
                <legend>$langReply</legend>
                    <div class='form-group'>
                        <label for='senderName' class='col-sm-2 control-label'>$langSender:</label>
                        <div class='col-sm-10'>
                            <input name='senderName' type='text' class='form-control' id='senderName' value='" . q(uid_to_name($uid)) . "' disabled>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='message_title' class='col-sm-2 control-label'>$langSubject:</label>
                        <div class='col-sm-10'>
                            <input name='message_title' type='text' class='form-control' id='message_title' value='" .
                                q($langMsgRe . ' ' . $msg->subject) . "'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='body' class='col-sm-2 control-label'>$langMessage:</label>
                        <div class='col-sm-10'>
                            ".rich_text_editor('body', 4, 20, '')."                            
                        </div>
                    </div>";

            if ($course_id != 0) {
                enableCheckFileSize();
                $out .= "<div class='form-group'>
                            <label for='body' class='col-sm-2 control-label'>$langFileName:</label>
                            <div class='col-sm-10'>" .
                                fileSizeHidenInput() . "
                                <input type='file' name='file' size='35'>
                            </div>
                        </div>";
            }
$out .=         "
                    <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='mailing' value='1' checked>
                                        " . q($langMailToUsers) . "
                                    </label>
                                </div>

                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>".
                            form_buttons(array(
                                array(
                                    'text'  => $langSend,
                                    'name'  => 'submit',
                                    'value' => $langAddModify
                                ),
                                array(
                                    'href' => "javascript:void(0)",
                                    'id'   => "cancelReply"
                                )
                            ))
                            ."
                        </div>
                    </div>
                </fieldset>";

            $out .= "
                 <div class='pull-right'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>
               </form></div>";

             $out .= "<script type='text/javascript' src='{$urlAppend}js/select2-3.5.1/select2.min.js'></script>\n
                 <script type='text/javascript'>
                        $(document).ready(function () {

                            $('.row.title-row').next('.row').hide();
                            $('#dropboxTabs .nav.nav-tabs').hide();
                            $('.btn-reply').on('click', function(){
                                $('#replyBox').show();
                                $('html, body').animate({
                                    scrollTop: $('#replyBox').offset().top
                                }, 2000);
                            });
                            $('#cancelReply').on('click', function(){
                                $('#replyBox').hide();
                                $('html, body').animate({
                                    scrollTop: $('#header_section').offset().top
                                }, 2000);
                            });
                            $('.back_index').on('click', function(){
                                $('.row.title-row').next('.row').show();
                                $('#dropboxTabs .nav.nav-tabs').show();
                            });

                            $('#select-recipients').select2();
                            $('#selectAll').click(function(e) {
                                e.preventDefault();
                                var stringVal = [];
                                $('#select-recipients').find('option').each(function(){
                                    stringVal.push($(this).val());
                                });
                                $('#select-recipients').val(stringVal).trigger('change');
                            });
                            $('#removeAll').click(function(e) {
                                e.preventDefault();
                                var stringVal = [];
                                $('#select-recipients').val(stringVal).trigger('change');
                            });
                        });

                        </script>";
        }
        /******End of Reply Form ********/

        $out .= "</div>";

        $out .= '<script>
                  $(function() {
                    $("#in_msg_body").find("a").addClass("outtabs");

                    $(document).off("click", ".delete_in");

                    $(document).on("click", ".delete_in_inner", function (e) {
                         e.preventDefault();
                         var id = $(this).data("id");
                         var string = "mid="+id;
                         bootbox.confirm("'.js_escape($langConfirmDelete).'", function(result) {
                         if(result) {
                             $.ajax({
                              type: "POST",
                              url: "ajax_handler.php",
                              datatype: "json",
                              data: string,
                              success: function(){
                                 $("#del_msg").html("<p class=\"alert alert-success\">'.js_escape($langMessageDeleteSuccess).'</p>");
                                 $(".alert-success").delay(3000).fadeOut(1500);
                                 $("#msg_area").remove();
                              }});
                         }
                        });
                      });


                    $(".delete").click(function() {
                      if (confirm("' . js_escape($langConfirmDelete) . '")) {
                        var rowContainer = $(this).parent().parent();
                        var id = rowContainer.attr("id");
                        var string = \'mid=\'+ id ;

                        $.ajax({
                          type: "POST",
                          url: "ajax_handler.php",
                          data: string,
                          cache: false,
                          success: function(){
                            $("#msg_area").slideUp(\'fast\', function() {
                              $(this).remove();
                              $("#del_msg").html("<p class=\'success\'>'.q($langMessageDeleteSuccess).'</p>");
                            });
                          }
                       });
                       return false;
                     }
                   });
                 });
                 </script>';
        //head content has the scripts necessary for tinymce as a result of calling rich_text_editor
        $out .= $head_content;
    }
} else {

    $out = "<div id='del_msg'></div><div id='inbox'>";

    $out .= "<table id='inbox_table' class='table-default'>
                  <thead>
                    <tr class='list-header'>
                      <th>$langSubject</th>";
    if ($course_id == 0) {
        $out .= "    <th>$langCourse</th>";
    }
    $out .= "         <th>$langSender</th>
                      <th>$langDate</th>
                      <th class='text-center option-btn-cell'><i class='fa fa-cogs'></i></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
              </table></div>";

    $out .= "<script type='text/javascript'>
               $(document).ready(function() {

                 var oTable = $('#inbox_table').dataTable({
                   'aoColumnDefs':[{'sClass':'option-btn-cell text-center', 'aTargets':[-1]}],
                   'bStateSave' : true,
                   'bProcessing': true,
                   'sDom': '<\"top\"fl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                   'bServerSide': true,
                   'searchDelay': 1000,
                   'sAjaxSource': 'ajax_handler.php?mbox_type=inbox&course_id=$course_id',
                   'aLengthMenu': [
                       [10, 15, 20 , -1],
                       [10, 15, 20, '".js_escape($langAllOfThem)."'] // change per page values here
                    ],
                   'sPaginationType': 'full_numbers',
                   'bSort': false,
                   'bAutoWidth' : false,
                   'fnDrawCallback': function( oSettings ) {
                        $('#inbox_table_filter label input').attr({
                          class : 'form-control input-sm',
                          placeholder : '".js_escape($langSearch)."...'
                        });
                    },
                   'oLanguage': {
                        'sLengthMenu':   '".js_escape("$langDisplay _MENU_ $langResults2")."',
                        'sZeroRecords':  '".js_escape($langNoResult)."',
                        'sInfo':         '".js_escape("$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults")."',
                        'sInfoEmpty':    '".js_escape("$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2")."',
                        'sInfoFiltered': '',
                        'sInfoPostFix':  '',
                        'sSearch':       '',
                        'sUrl':          '',
                        'oPaginate': {
                             'sFirst':    '&laquo;',
                             'sPrevious': '&lsaquo;',
                             'sNext':     '&rsaquo;',
                             'sLast':     '&raquo;'
                        }
                    }
                 });
                 $(document).off( 'click','.delete_in_inner');
                 $(document).on( 'click','.delete_in', function (e) {
                     e.preventDefault();
                     var id = $(this).data('id');
                     var string = 'mid='+id;
                     bootbox.confirm('".js_escape($langConfirmDelete)."', function(result) {
                     if(result) {
                         $.ajax({
                          type: 'POST',
                          url: 'ajax_handler.php',
                          datatype: 'json',
                          data: string,
                          success: function(data){
                             var num_page_records = oTable.fnGetData().length;
                             var per_page = $('#inbox_table').DataTable().page.info().length;
                             var page_number = $('#inbox_table').DataTable().page.info().page;
                             if(num_page_records==1){
                                 if(page_number!=0) {
                                     page_number--;
                                 }
                             }
                             $('#del_msg').html('<p class=\'alert alert-success\'>".js_escape($langMessageDeleteSuccess)."</p>');
                             $('.alert-success').delay(3000).fadeOut(1500);
                             $('#msg_area').remove();
                             oTable.fnPageChange(page_number);
                          },
                          error: function(xhr, textStatus, error){
                              console.log(xhr.statusText);
                              console.log(textStatus);
                              console.log(error);
                          }
                        });
                    }
                    });
                  });

                 $('.delete_all_in').click(function() {
                     bootbox.confirm('".js_escape($langConfirmDeleteAllMsgs)."', function(result) {
                         if(result) {
                             var string = 'all_inbox=1';
                             $.ajax({
                                 type: 'POST',
                                 url: 'ajax_handler.php?course_id=$course_id',
                                 data: string,
                                 cache: false,
                                 success: function(){
                                     var num_page_records = oTable.fnGetData().length;
                                     var per_page = $('#inbox_table').DataTable().page.info().length;
                                     var page_number = $('#inbox_table').DataTable().page.info().page;
                                     if(num_page_records==1){
                                         if(page_number!=0) {
                                             page_number--;
                                         }
                                     }
                                     $('#del_msg').html('<p class=\'alert alert-success\'>".js_escape($langMessageDeleteAllSuccess)."</p>');
                                     $('.alert-success').delay(3000).fadeOut(1500);
                                     oTable.fnPageChange(page_number);
                                 }
                             });
                         }
                     })
                 });

               });
             </script>";
}
echo $out;


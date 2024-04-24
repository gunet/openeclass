<?php
/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'class.msg.php';

$personal_msgs_allowed = get_config('dropbox_allow_personal_messages');
$student_to_student_allow = get_config('dropbox_allow_student_to_student');

if (!isset($course_id)) {
    $ajax_url = "ajax_handler.php";
    $ajax_url_inbox = "$ajax_url?mbox_type=inbox";
    $course_id = 0;
} else {
    $ajax_url = "ajax_handler.php?course=$course_code";
    $ajax_url_inbox = "$ajax_url&mbox_type=inbox";
}

if (isset($_GET['mid'])) {

    $mid = intval($_GET['mid']);
    $msg = new Msg($mid, $uid, 'msg_view');
    if ($msg->course_id != 0) {
        $course_id = $msg->course_id;
        $course_code = course_id_to_code($course_id);
    } else {
        $course_id = 0;
    }
    if (!$msg->error) {
        $urlstr = '';
        if ($course_id != 0) {
            $urlstr = "?course=".$course_code;
        }
        $out = action_bar(array(
                            array('title' => $langReply,
                                  'icon' => 'fa-edit',
                                  'button-class' => 'btn-reply btn-default',
                                  'level' => 'primary-label'),
                            array('title' => $langForward,
                                  'icon' => 'fa-forward',
                                  'button-class' => 'btn-forward btn-default',
                                  'level' => 'primary-label'),
                            array('title' => $langBack,
                                  'url' => "inbox.php".$urlstr,
                                  'icon' => 'fa-reply',
                                  'button-class' => 'back_index btn-default',
                                  'level' => 'primary-label'),
                            array('title' => $langDelete,
                                  'url' => 'javascript:void(0)',
                                  'icon' => 'fa-times',
                                  'class' => 'delete_in_inner',
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
            $out .= "<div class='row  margin-bottom-thin'>
                        <div class='col-sm-2'>
                            <strong>$langCourse:</strong>
                        </div>
                        <div class='col-sm-10'>
                            <a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($msg->course_id)."\">".q(course_id_to_title($msg->course_id))."</a>
                        </div>
                    </div>";
        }
        $out .= "
                        <div class='row  margin-bottom-thin'>
                            <div class='col-sm-2'>
                                <strong>$langDate:</strong>
                            </div>
                            <div class='col-sm-10'>
                                ". format_locale_date($msg->timestamp, 'short') ."
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
                                  <a href='message_download.php?course=" .
                                     course_id_to_code($msg->course_id) . "&amp;id={$msg->id}' class='outtabs' target='_blank'>" .
                                     q($msg->real_filename) . "</a>&nbsp<i class='fa fa-save'></i></a>&nbsp;&nbsp;(" .
                                     format_file_size($msg->filesize). ")
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
                $out .= "<form method='post' class='form-horizontal' role='form' action='message_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
                if ($msg->course_id != 0) {//thread belonging to a course viewed from the central ui
                    $out .= "<input type='hidden' name='course' value='".course_id_to_code($msg->course_id)."' />";
                }
            } else {
                $out .= "<form method='post' class='form-horizontal' role='form' action='message_submit.php?course=$course_code' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
            }
            $out .= generate_csrf_token_form_field() . "
                <fieldset>
                <h4>$langReply</h4>
                    <div class='form-group'>
                        <label for='senderName' class='col-sm-2 control-label'>$langSender:</label>
                        <div class='col-sm-10'>
                            <input name='senderName' type='text' class='form-control' id='senderName' value='" . q(uid_to_name($uid)) . "' disabled>
                        </div>
                    </div>";

            $out .= "
            <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langSendTo:</label>
            <div class='col-sm-10'>
                <select name='recipients[]' multiple='multiple' class='form-control' id='select-recipients'>";

            // mail sender
            $out .= "<option value='$msg->author_id' selected>". q(uid_to_name($msg->author_id)) . "</option>";

            addRecipientOptions();

            $out .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
            </div>
        </div>";
        $out .= "<div class='form-group'>
                    <label for='message_title' class='col-sm-2 control-label'>$langSubject:</label>
                    <div class='col-sm-10'>
                        <input name='message_title' type='text' class='form-control' id='message_title' value='" .
                            q($langMsgRe . ' ' . $msg->subject) . "'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='body' class='col-sm-2 control-label'>$langMessage:</label>
                    <div class='col-sm-10'>
                        ".rich_text_editor('body', 4, 20, $msg->body . "<hr align='left' width='70%'><br><br>")."
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

            $out .= "
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

            if ($course_id != 0) {
                $out .= "<div class='pull-right'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>";
            }
           $out .= "</form></div>";

            // forward form
            $out .= "<div class='form-wrapper' id='forwardBox' style='display:none;'>";
            if ($course_id == 0) {
                $out .= "<form method='post' class='form-horizontal' role='form' action='message_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
                if ($msg->course_id != 0) { // thread belonging to a course viewed from the central ui
                    $out .= "<input type='hidden' name='course' value='".course_id_to_code($msg->course_id)."' />";
                }
            } else {
                $out .= "<form method='post' class='form-horizontal' role='form' action='message_submit.php?course=$course_code' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
            }
            $out .= generate_csrf_token_form_field() . "
                <fieldset>
                <h4>$langForward</h4>
                    <div class='form-group'>
                        <label for='senderName' class='col-sm-2 control-label'>$langSender:</label>
                        <div class='col-sm-10'>
                            <input name='senderName' type='text' class='form-control' id='senderName' value='" . q(uid_to_name($uid)) . "' disabled>
                        </div>
                    </div>
                <div class='form-group'>
                <label for='title' class='col-sm-2 control-label'>$langSendTo:</label>
                <div class='col-sm-10'>
                    <select name='recipients[]' multiple='multiple' class='form-control' id='select-recipients-forward'>";

            addRecipientOptions();

            $out .= "</select><a href='#' id='removeAllForward'>$langJQUncheckAll</a>
                </div>
            </div>

            <div class='form-group'>
                <label for='message_title' class='col-sm-2 control-label'>$langSubject:</label>
                <div class='col-sm-10'>
                    <input name='message_title' type='text' class='form-control' id='message_title' value='" .
                        q($langMsgFw . ' ' . $msg->subject) . "'>
                </div>
            </div>
            <div class='form-group'>
                <label for='body' class='col-sm-2 control-label'>$langMessage:</label>
                <div class='col-sm-10'>
                    ".rich_text_editor('body', 4, 20, $msg->body . "<hr align='left' width='70%'><br><br>")."
                </div>
            </div>";

            if ($msg->filename and $msg->filesize != 0) {
                $out .= "
                    <div class='form-group attachment-section'>
                        <label class='col-sm-2 control-label'>$langAttachedFile:</label>
                        <div class='col-sm-8'>
                            <p class='form-control-static'>
                                <input type='hidden' name='keepAttachment' value='{$msg->id}'>
                                <a href='message_download.php?course=" .
                                course_id_to_code($msg->course_id) . "&amp;id={$msg->id}' class='outtabs' target='_blank'>" .
                                q($msg->real_filename) . "</a>&nbsp<i class='fa fa-save'></i></a>&nbsp;&nbsp;(" .
                                format_file_size($msg->filesize) . ")
                            </p>
                        </div>
                        <div class='col-sm-2'>
                            <button class='pull-right btn btn-default attachment-delete-button'><span class='fa fa-times space-after-icon'></span>$langLessElements</button>
                        </div>
                    </div>";
            } elseif ($course_id != 0) {
                enableCheckFileSize();
                $out .= "<div class='form-group'>
                            <label for='body' class='col-sm-2 control-label'>$langFileName:</label>
                            <div class='col-sm-10'>" .
                                fileSizeHidenInput() . "
                                <input type='file' name='file' size='35'>
                            </div>
                        </div>";
            }
            $out .= "
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

            $out .= "<div class='pull-right'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>
               </form></div>";

            // ************* End of forward form ******************

            $out .=
                "<script type='text/javascript'>
                    $(document).ready(function () {
                        $('.row.title-row').next('.row').hide();
                        $('#dropboxTabs .nav.nav-tabs').hide();
                        $('.btn-reply').on('click', function(e) {
                            e.preventDefault();
                            $('#forwardBox').hide();
                            $('#replyBox').show();
                            $('html, body').animate({
                                scrollTop: $('#replyBox').offset().top
                            }, 500);
                            $('#select-recipients').select2({
                                placeholder: '".js_escape($langSearch)."',
                                multiple: true,
                                cache: true
                            });
                            return false;
                        });
                        $('.btn-forward').on('click', function(e) {
                            e.preventDefault();
                            $('#replyBox').hide();
                            $('#forwardBox').show();
                            $('html, body').animate({
                                scrollTop: $('#forwardBox').offset().top
                            }, 500);
                            $('#select-recipients-forward').select2({
                                placeholder: '".js_escape($langSearch)."',
                                multiple: true,
                                cache: true
                            });
                            return false;
                        });
                        $('#cancelReply').on('click', function(e){
                            e.preventDefault();
                            $('#replyBox').hide();
                            $('html, body').animate({
                                scrollTop: $('#header_section').offset().top
                            }, 500);
                            return false;
                        });
                        $('.back_index').on('click', function(){
                            $('.row.title-row').next('.row').show();
                            $('#dropboxTabs .nav.nav-tabs').show();
                        });
                        $('#selectAll').click(function(e) {
                            e.preventDefault();
                            var stringVal = [];
                            $('#select-recipients').find('option').each(function(){
                                stringVal.push($(this).val());
                            });
                            $('#select-recipients').val(stringVal).trigger('change');
                            return false;
                        });
                        $('#removeAll').click(function(e) {
                            e.preventDefault();
                            $('#select-recipients').val([]).trigger('change');
                            return false;
                        });
                        $('#removeAllForward').click(function(e) {
                            e.preventDefault();
                            $('#select-recipients-forward').val([]).trigger('change');
                            return false;
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
                         var id = $(this).children("a").data("id");
                         var string = "mid="+id+"&'. generate_csrf_token_link_parameter() .'";
                         bootbox.confirm("'.js_escape($langConfirmDelete).'", function(result) {
                         if(result) {
                             $.ajax({
                                  type: "POST",
                                  url: "'.$ajax_url.'",
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
                        var string = "mid="+id+"&'. generate_csrf_token_link_parameter() .'";
                        $.ajax({
                          type: "POST",
                          url: "'.$ajax_url.'",
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
                   'sAjaxSource': '$ajax_url_inbox',
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
                     var string = 'mid='+id+'&". generate_csrf_token_link_parameter() ."';
                     bootbox.confirm('".js_escape($langConfirmDelete)."', function(result) {
                     if (result) {
                         $.ajax({
                          type: 'POST',
                          url: '$ajax_url',
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
                             var string = 'all_inbox=1&". generate_csrf_token_link_parameter() . "';
                             $.ajax({
                                 type: 'POST',
                                 url: '$ajax_url',
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


/**
 * @brief add recipients (in 'reply' and 'forward' message)
 */
function addRecipientOptions() {
    global $course_id, $is_editor, $student_to_student_allow, $out, $uid;

    if ($course_id != 0) { // course messages
        if ($is_editor || $student_to_student_allow == 1) {
            // select all users from this course except yourself
            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                        FROM user u, course_user cu
                        WHERE cu.course_id = ?d
                            AND cu.user_id = u.id
                            AND cu.status != ?d
                            AND u.id != ?d
                        ORDER BY name";

            $res = Database::get()->queryArray($sql, $course_id, USER_GUEST, $uid);

            // find course groups (if any)
            $sql_g = "SELECT id, name FROM `group` WHERE course_id = ?d ORDER BY name";
            $result_g = Database::get()->queryArray($sql_g, $course_id);
            foreach ($result_g as $res_g) {
                if (isset($_GET['group_id']) and $_GET['group_id'] == $res_g->id) {
                    $selected_group = ' selected';
                } else {
                    $selected_group = '';
                }
                $out .= "<option value = '_$res_g->id' $selected_group>".q($res_g->name)."</option>";
            }
        } else {
            // if user is student and student-student messages not allowed for course messages show teachers
            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                        FROM user u, course_user cu
                        WHERE cu.course_id = ?d
                            AND cu.user_id = u.id
                            AND (cu.status = ?d OR cu.editor = ?d)
                            AND u.id != ?d
                        ORDER BY name";

            $res = Database::get()->queryArray($sql, $course_id, USER_TEACHER, 1, $uid);

            // check if user is group tutor
            $sql_g = "SELECT g.id, g.name FROM `group` as g, group_members as gm
                WHERE g.id = gm.group_id AND g.course_id = ?d AND gm.user_id = ?d AND gm.is_tutor = ?d";

            $result_g = Database::get()->queryArray($sql_g, $course_id, $uid, 1);
            foreach ($result_g as $res_g) {
                $out .= "<option value = '_$res_g->id'>".q($res_g->name)."</option>";
            }

            // find user's group and their tutors
            $tutors = array();
            $sql_g = "SELECT `group`.id FROM `group`, group_members
                          WHERE `group`.course_id = ?d
                              AND `group`.id = group_members.group_id
                              AND `group_members`.user_id = ?d";
            $result_g = Database::get()->queryArray($sql_g, $course_id, $uid);
            foreach ($result_g as $res_g) {
                $sql_gt = "SELECT u.id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                              FROM user u, group_members g
                              WHERE g.group_id = ?d
                                  AND g.is_tutor = ?d
                                  AND g.user_id = u.id
                                  AND u.id != ?d";
                $res_gt = Database::get()->queryArray($sql_gt, $res_g->id, 1, $uid);
                foreach ($res_gt as $t) {
                    $tutors[$t->id] = q($t->name)." (".q($t->username).")";
                }
            }
        }

        foreach ($res as $r) {
            if (isset($tutors) && !empty($tutors)) {
                if (isset($tutors[$r->user_id])) {
                    unset($tutors[$r->user_id]);
                }
            }
            $out .= "<option value='{$r->user_id}'>" . q($r->name) . " (".q($r->username).")</option>";
        }
        if (isset($tutors)) {
            foreach ($tutors as $key => $value) {
                $out .= "<option value=" . $key . ">" . q($value) . "</option>";
            }
        }
    }
}

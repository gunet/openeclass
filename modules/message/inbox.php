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
            array('title' => $langBack,
                  'url' => "inbox.php".$urlstr,
                  'icon' => 'fa-reply',
                  'button-class' => 'back_index btn-secondary',
                  'temporary-button-class' => '',
                  'level' => 'primary'),
            array('title' => $langReply,
                  'icon' => 'fa-reply-all',
                  'button-class' => 'btn-reply btn-secondary',
                  'temporary-button-class' => 'btn-reply',
                  'level' => 'primary-label'),
            array('title' => $langForward,
                  'icon' => 'fa-forward',
                  'button-class' => 'btn-forward btn-secondary',
                  'temporary-button-class' => 'btn-forward',
                  'level' => 'primary-label'),
            array('title' => $langDelete,
                  'url' => 'javascript:void(0)',
                  'icon' => 'fa-xmark',
                  'class' => 'delete_in_inner',
                  'link-attrs' => "data-id='$msg->id'")
        ));

        $recipients = '';
        foreach ($msg->recipients as $r) {
            if ($r != $msg->author_id) {
                $recipients .= display_user($r, false, false, "outtabs").'<br/>';
            }
        }
        $out .= "
                <div id='del_msg'></div>
                <div id='msg_area'>
                        <div class='row row-cols-1 g-4'>
                            <div class='col'>
                                <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                        <h3>$langMessageInfo</h3>
                                    </div>
                                    <div class='card-body'>
                                        <ul class='list-group list-group-flush'>
                                            <li class='list-group-item element'>
                                                <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                    <div class='col-md-3 col-12'>
                                                        <div class='title-default'>$langSubject</div>
                                                    </div>
                                                    <div class='col-md-9 col-12 title-default-line-height'>
                                                        ".q($msg->subject)."
                                                    </div>
                                                </div>
                                            </li>";
                                        if ($msg->course_id != 0 && $course_id == 0) {
                                            $out .= "
                                            <li class='list-group-item element'>
                                                <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                    <div class='col-md-3 col-12'>
                                                        <div class='title-default'>$langCourse</div>
                                                    </div>
                                                    <div class='col-md-9 col-12 title-default-line-height'>
                                                        <a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($msg->course_id)."\">".course_id_to_title($msg->course_id)."</a>
                                                    </div>
                                                </div>
                                            </li>";
                                        }
                                        $out .= "
                                            <li class='list-group-item element'>
                                                <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                    <div class='col-md-3 col-12'>
                                                        <div class='title-default'>$langDate</div>
                                                    </div>
                                                    <div class='col-md-9 col-12 title-default-line-height'>
                                                        ". format_locale_date($msg->timestamp, 'short') ."
                                                    </div>
                                                </div>
                                            </li>
                                            <li class='list-group-item element'>
                                                <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                    <div class='col-md-3 col-12'>
                                                        <div class='title-default'>$langSender</div>
                                                    </div>
                                                    <div class='col-md-9 col-12 title-default-line-height'>
                                                        ".display_user($msg->author_id, false, false, "outtabs")."
                                                    </div>
                                                </div>
                                            </li>
                                            <li class='list-group-item element'>
                                                <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                    <div class='col-md-3 col-12'>
                                                        <div class='title-default'>$langRecipients</div>
                                                    </div>
                                                    <div class='col-md-9 col-12 title-default-line-height'>
                                                        $recipients
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class='col'>
                                <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                        <h3>$langMessageContent</h3>
                                    </div>
                                    <div class='card-body'>
                                        
                                            <div class='d-flex justify-content-start align-items-center flex-wrap gap-3 mb-4'>
                                                <div>
                                                    ".standard_text_escape($msg->body)."
                                                </div>
                                            </div>";
                                            if ($msg->filename != '' && $msg->filesize != 0) {
                                                $out .= "
                                                        <div class='d-flex justify-content-start align-items-center flex-wrap gap-3'>
                                                            <div>
                                                                $langAttachedFile
                                                            </div>
                                                            <div>
                                                                <a href='message_download.php?course=" .
                                                                    course_id_to_code($msg->course_id) . "&amp;id={$msg->id}' class='outtabs' target='_blank' aria-label='(opens in a new tab)'>" .
                                                                    q($msg->real_filename) . "
                                                                </a>
                                                                &nbsp;<i class='fa fa-save'></i></a>&nbsp;&nbsp;(" .
                                                                    format_file_size($msg->filesize). ")
                                                                </div>
                                                        </div>";
                                            }
                            $out .= "   
                                    </div>
                                </div>
                            </div>


                        </div>";








                        
        /*****Reply Form****/
        if ($msg->course_id == 0 && !$personal_msgs_allowed) {
            //do not show reply form when personal messages are not allowed
        } else {
            if(!isset($_GET['course'])){
            $out .= "<div class='row mt-4'>
                        <div class='col-lg-6 col-12'>";
            }else{
            $out .= "
                <div class='d-lg-flex gap-4 mt-4'>
                    <div class='flex-grow-1'>
                        <div class='col-12 mt-4'>";
            }

                    $out .= "<div class='form-wrapper form-edit rounded' id='replyBox' style='display:none;'>";
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
                                            <h3 class='TextBold text-center'>$langReply</h3>

                                            <div class='form-group mt-4'>
                                                <label for='senderName' class='col-sm-12 control-label-notes'>$langSender</label>
                                                <div class='col-sm-12'>
                                                    <input name='senderName' type='text' class='form-control' id='senderName' value='" . q(uid_to_name($uid)) . "' disabled>
                                                </div>
                                            </div>";

                                    $out .= "

                                            <div class='form-group mt-4'>
                                                <label for='title' class='col-sm-12 control-label-notes'>$langSendTo</label>
                                                <div class='col-sm-12'>
                                                    <select name='recipients[]' multiple='multiple' class='form-select' id='select-recipients'>";

                                                // mail sender
                                                $out .= "<option value='$msg->author_id' selected>". q(uid_to_name($msg->author_id)) . "</option>";

                                                addRecipientOptions();

                                                $out .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                                                </div>
                                            </div>";

                                    $out .= "

                                            <div class='form-group mt-4'>
                                                <label for='message_title' class='col-sm-12 control-label-notes'>$langSubject</label>
                                                <div class='col-sm-12'>
                                                    <input name='message_title' type='text' class='form-control' id='message_title' value='" .
                                                        q($langMsgRe . ' ' . $msg->subject) . "'>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <label for='body' class='col-sm-12 control-label-notes'>$langMessage</label>
                                                <div class='col-sm-12'>
                                                    ".rich_text_editor('body', 4, 20, $msg->body . "<hr align='left' width='70%'><br><br>")."
                                                </div>
                                            </div>";

                                        if ($course_id != 0) {
                                            enableCheckFileSize();
                                            $out .= "

                                            <div class='form-group mt-4'>
                                                <label for='body' class='col-sm-12 control-label-notes'>$langFileName</label>
                                                <div class='col-sm-12'>" .
                                                    fileSizeHidenInput() . "
                                                    <input type='file' name='file' size='35'>
                                                </div>
                                            </div>";
                                        }

                                        $out .= "

                                            <div class='form-group mt-4'>
                                                <div class='col-sm-10 col-sm-offset-2'>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='mailing' value='1' checked>
                                                                <span class='checkmark'></span>
                                                                " . q($langMailToUsers) . "
                                                            </label>
                                                        </div>

                                                </div>
                                            </div>

                                            <div class='form-group mt-5'>
                                                <div class='col-12 d-flex justify-content-end align-items-center'>
                                                        ".
                                                        form_buttons(array(
                                                            array(
                                                                'class' => 'submitAdminBtn',
                                                                'text'  => $langSend,
                                                                'name'  => 'submit',
                                                                'value' => $langAddModify
                                                            ),
                                                            array(
                                                                'class' => 'cancelAdminBtn ms-1',
                                                                'href'  => "$_SERVER[SCRIPT_NAME]".(($course_id != 0)? "?course=$course_code" : ""),
                                                                'id'   => "cancelReply"
                                                            )
                                                        ))
                                                        ."
                                                </div>
                                            </div>
                                    </fieldset>";

                                    if ($course_id != 0) {
                                        $out .= "<div class='text-end mt-3'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>";
                                    }

                        $out .= "</form>
                            </div> <!-- end-form-wrapper --> ";

            if(isset($_GET['course'])){
                $out .= "</div> <!-- end col-12 -->
                    </div> <!-- end flex-grow -->
                    <div class='form-content-modules d-none message-reply'>
                        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
                    </div>
                </div>"; // end d-lg-flex
            }

            if (!isset($_GET['course'])) {
                $out .= "
                    </div> <!-- end col-lg-6 col-12 -->
                    <div class='col-lg-6 col-12 d-none message-reply'>
                        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
                    </div>
                ";
        $out .= "</div>"; // end row
            }














            // forward form
            if(!isset($_GET['course'])){
                $out .= "<div class='row'>
                            <div class='col-lg-6 col-12'>";
            }else{
            $out .= "<div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='col-12 mt-4'>";
            }

                        $out .= "<div class='form-wrapper form-edit rounded' id='forwardBox' style='display:none;'>";
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
                                                <h3 class='TextBold text-center'>$langForward</h3>

                                                <div class='form-group mt-4'>
                                                    <label for='senderName' class='col-sm-12 control-label-notes'>$langSender</label>
                                                    <div class='col-sm-12'>
                                                        <input name='senderName' type='text' class='form-control' id='senderName' value='" . q(uid_to_name($uid)) . "' disabled>
                                                    </div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='title' class='col-sm-12 control-label-notes'>$langSendTo</label>
                                                    <div class='col-sm-12'>
                                                        <select name='recipients[]' multiple='multiple' class='form-select' id='select-recipients-forward'>";

                                                addRecipientOptions();

                                                $out .= "</select><a href='#' id='removeAllForward'>$langJQUncheckAll</a>
                                                    </div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='message_title' class='col-sm-12 control-label-notes'>$langSubject</label>
                                                    <div class='col-sm-12'>
                                                        <input name='message_title' type='text' class='form-control' id='message_title' value='" .
                                                            q($langMsgFw . ' ' . $msg->subject) . "'>
                                                    </div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='body' class='col-sm-12 control-label-notes'>$langMessage</label>
                                                    <div class='col-sm-12'>
                                                        ".rich_text_editor('body', 4, 20, $msg->body . "<hr align='left' width='70%'><br><br>")."
                                                    </div>
                                                </div>";

                                        if ($msg->filename and $msg->filesize != 0) {
                                            $out .= "

                                                <div class='form-group attachment-section mt-4'>
                                                    <label class='col-sm-12 control-label-notes'>$langAttachedFile</label>
                                                    <div class='col-sm-8'>
                                                        <p class='form-control-static'>
                                                            <input type='hidden' name='keepAttachment' value='{$msg->id}'>
                                                            <a href='message_download.php?course=" .
                                                            course_id_to_code($msg->course_id) . "&amp;id={$msg->id}' class='outtabs' target='_blank' aria-label='(opens in a new tab)'>" .
                                                            q($msg->real_filename) . "</a>&nbsp;<i class='fa fa-save'></i></a>&nbsp;&nbsp;(" .
                                                            format_file_size($msg->filesize) . ")
                                                        </p>
                                                    </div>
                                                    <div class='col-sm-2'>
                                                        <button class='float-end btn cancelAdminBtn attachment-delete-button'><span class='fa-solid fa-xmark space-after-icon'></span>$langLessElements</button>
                                                    </div>
                                                </div>";
                                        } elseif ($course_id != 0) {
                                            enableCheckFileSize();

                                        $out .= "<div class='form-group mt-4'>
                                                    <label for='body' class='col-sm-12 control-label-notes'>$langFileName</label>
                                                    <div class='col-sm-12'>" .
                                                        fileSizeHidenInput() . "
                                                        <input type='file' name='file' size='35'>
                                                    </div>
                                                </div>";
                                        }
                                        $out .= "

                                                <div class='form-group mt-4'>
                                                    <div class='col-sm-10 col-sm-offset-2'>
                                                            <div class='checkbox'>
                                                                <label class='label-container'>
                                                                    <input type='checkbox' name='mailing' value='1' checked>
                                                                    <span class='checkmark'></span>
                                                                    " . q($langMailToUsers) . "
                                                                </label>
                                                            </div>
                                                    </div>
                                                </div>

                                                <div class='form-group mt-5'>
                                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                                            ".
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'submitAdminBtn',
                                                                    'text'  => $langSend,
                                                                    'name'  => 'submit',
                                                                    'value' => $langAddModify
                                                                ),
                                                                array(
                                                                    'class' => 'cancelAdminBtn ms-1',
                                                                    'href'  => "$_SERVER[SCRIPT_NAME]".(($course_id != 0)? "?course=$course_code" : ""),
                                                                    'id'   => "cancelReply"
                                                                )
                                                            ))
                                                            ."
                                                    </div>
                                                </div>

                                            </fieldset>";

                                        $out .= "<div class='text-end mt-3'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>

                                        </form>
                                    </div> <!-- end form-wrapper --> ";
                if(isset($_GET['course'])){
                    $out .= "</div> <!--  end col-12 -->
                        </div> <!--  end flex-grow -->
                        <div class='form-content-modules d-none message-forward'>
                            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
                        </div>
                    </div>"; // end d-lg-flex
                }
                             

                if (!isset($_GET['course'])) {
                    $out .= "
                        </div> <!-- end col-lg-6 col-12 -->
                        <div class='col-lg-6 col-12 d-none message-forward'>
                            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
                        </div>
                    </div>
                    ";
                }




            // ************* End of forward form ******************

            $out .=
                "<script type='text/javascript'>
                    $(document).ready(function () {
                        $('.row.title-row').next('.row').hide();
                        $('#dropboxTabs .nav.nav-tabs').hide();
                        $('.btn-reply').on('click', function(e) {
                            e.preventDefault();
                            $('.message-reply').addClass('d-lg-block');
                            $('.message-forward').removeClass('d-lg-block');
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
                            $('.message-reply').removeClass('d-lg-block');
                            $('.message-forward').addClass('d-lg-block');
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

                        // bootbox.confirm("'.js_escape($langConfirmDelete).'", function(result) {
                        //     if(result) {
                        //         $.ajax({
                        //             type: "POST",
                        //             url: "'.$ajax_url.'",
                        //             datatype: "json",
                        //             data: string,
                        //             success: function(){
                        //                 $("#del_msg").html("<p class=\"alert alert-success\"><i class=\"fa-solid fa-circle-check fa-lg\"></i><span>'.js_escape($langMessageDeleteSuccess).'</span></p>");
                        //                 $(".alert-success").delay(3000).fadeOut(1500);
                        //                 $("#msg_area").remove();
                        //         }});
                        //     }
                        // });

                        bootbox.confirm({
                            closeButton: false,
                            title: "<div class=\"icon-modal-default\"><i class=\"fa-regular fa-trash-can fa-xl Accent-200-cl\"></i></div><h3 class=\"modal-title-default text-center mb-0\">'.js_escape($langConfirmDelete).'</h3>",
                            message: "<p class=\"text-center\">'.js_escape($langConfirmDelete).'</p>",
                            buttons: {
                                cancel: {
                                    label: "'.js_escape($langCancel).'",
                                    className: "cancelAdminBtn position-center"
                                },
                                confirm: {
                                    label: "'.js_escape($langDelete).'",
                                    className: "deleteAdminBtn position-center",
                                }
                            },
                            callback: function (result) {
                                if(result) {
                                    $.ajax({
                                        type: "POST",
                                        url: "'.$ajax_url.'",
                                        datatype: "json",
                                        data: string,
                                        success: function(){
                                            $("#del_msg").html("<p class=\"alert alert-success\"><i class=\"fa-solid fa-circle-check fa-lg\"></i><span>'.js_escape($langMessageDeleteSuccess).'</span></p>");
                                            $(".alert-success").delay(3000).fadeOut(1500);
                                            $("#msg_area").remove();
                                    }});
                                }
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

    $out = "<div id='del_msg'></div><div id='inbox' class='table-responsive'>";

    $out .= "<table id='inbox_table' class='table-default'>
                  <thead>
                    <tr class='list-header'>
                      <th>$langSubject</th>";
    if ($course_id == 0) {
        $out .= "    <th>$langCourse</th>";
    }
    $out .= "         <th>$langSender</th>
                      <th style='width:15%;'>$langDate</th>
                      <th style='width:10%;' class='option-btn-cell'><i class='fa fa-cogs'></i></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
              </table></div>";
    $out .= "

    <script type='text/javascript'>
               $(document).ready(function() {

                 var oTable = $('#inbox_table').dataTable({
                   'aoColumnDefs':[{'sClass':'option-btn-cell text-end', 'aTargets':[-1]}],
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
                          class : 'form-control input-sm ms-0 mb-3',
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

                    // bootbox.confirm('".js_escape($langConfirmDelete)."', function(result) {
                    //     if (result) {
                    //         $.ajax({
                    //             type: 'POST',
                    //             url: '$ajax_url',
                    //             datatype: 'json',
                    //             data: string,
                    //             success: function(data){
                    //                 var num_page_records = oTable.fnGetData().length;
                    //                 var per_page = $('#inbox_table').DataTable().page.info().length;
                    //                 var page_number = $('#inbox_table').DataTable().page.info().page;
                    //                 if(num_page_records==1){
                    //                     if(page_number!=0) {
                    //                         page_number--;
                    //                     }
                    //                 }
                    //                 $('#del_msg').html('<p class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>".js_escape($langMessageDeleteSuccess)."</span></p>');
                    //                 $('.alert-success').delay(3000).fadeOut(1500);
                    //                 $('#msg_area').remove();
                    //                 oTable.fnPageChange(page_number);
                    //             },
                    //             error: function(xhr, textStatus, error){
                    //                 console.log(xhr.statusText);
                    //                 console.log(textStatus);
                    //                 console.log(error);
                    //             }
                    //         });
                    //     }
                    // });

                    bootbox.confirm({
                        closeButton: false,
                        title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><h3 class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</h3>',
                        message: '<p class=\'text-center\'>".js_escape($langConfirmDelete)."</p>',
                        buttons: {
                            cancel: {
                                label: '".js_escape($langCancel)."',
                                className: 'cancelAdminBtn position-center'
                            },
                            confirm: {
                                label: '".js_escape($langDelete)."',
                                className: 'deleteAdminBtn position-center',
                            }
                        },
                        callback: function (result) {
                            if(result) {
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
                                        $('#del_msg').html('<p class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>".js_escape($langMessageDeleteSuccess)."</span></p>');
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
                        }
                    });
                });

                $('.delete_all_in').click(function() {
                    // bootbox.confirm('".js_escape($langConfirmDeleteAllMsgs)."', function(result) {
                    //     if(result) {
                    //         var string = 'all_inbox=1&". generate_csrf_token_link_parameter() . "';
                    //         $.ajax({
                    //             type: 'POST',
                    //             url: '$ajax_url',
                    //             data: string,
                    //             cache: false,
                    //             success: function(){
                    //                 var num_page_records = oTable.fnGetData().length;
                    //                 var per_page = $('#inbox_table').DataTable().page.info().length;
                    //                 var page_number = $('#inbox_table').DataTable().page.info().page;
                    //                 if(num_page_records==1){
                    //                     if(page_number!=0) {
                    //                         page_number--;
                    //                     }
                    //                 }
                    //                 $('#del_msg').html('<p class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>".js_escape($langMessageDeleteAllSuccess)."</span></p>');
                    //                 $('.alert-success').delay(3000).fadeOut(1500);
                    //                 oTable.fnPageChange(page_number);
                    //             }
                    //         });
                    //     }
                    // })

                    bootbox.confirm({
                        closeButton: false,
                        title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><h3 class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</h3>',
                        message: '<p class=\'text-center\'>".js_escape($langConfirmDeleteAllMsgs)."</p>',
                        buttons: {
                            cancel: {
                                label: '".js_escape($langCancel)."',
                                className: 'cancelAdminBtn position-center'
                            },
                            confirm: {
                                label: '".js_escape($langDelete)."',
                                className: 'deleteAdminBtn position-center',
                            }
                        },
                        callback: function (result) {
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
                                        $('#del_msg').html('<p class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>".js_escape($langMessageDeleteAllSuccess)."</span></p>');
                                        $('.alert-success').delay(3000).fadeOut(1500);
                                        oTable.fnPageChange(page_number);
                                    }
                                });
                            }
                        }
                    });

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

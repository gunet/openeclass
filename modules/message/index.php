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

$require_login = true;
if (isset($_GET['course'])) { //course messages
    $require_current_course = true;
    $require_user_registration = true;
} else { //personal messages
    $require_current_course = false;
    $require_user_registration = false;
}
$guest_allowed = false;
$require_help = true;
$helpTopic = 'message';

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

load_js('select2');

if ($is_admin and $require_current_course) {
    $require_course_admin = true; // hide role switcher
}
$toolName = $langPortfolio;
$pageName = $langDropBox;
$personal_msgs_allowed = get_config('dropbox_allow_personal_messages');

if (!isset($course_id)) {
    $course_id = 0;
}

if ($course_id != 0) {
    $message_dir = $webDir . "/courses/" . $course_code . "/dropbox";
    if (!is_dir($message_dir)) {
        make_dir($message_dir);
    }

    // get dropbox quotas from database
    $d = Database::get()->querySingle("SELECT dropbox_quota FROM course WHERE code = ?s", $course_code);
    $diskQuotaDropbox = $d->dropbox_quota;
    $diskUsed = dir_total_space($message_dir);
}

// javascript functions
$head_content .= '<script type="text/javascript">
                    function checkForm (frm) {
                        if (frm.elements["recipients[]"].selectedIndex < 0) {
                                alert("' . $langNoUserSelected . '");
                                return false;
                        } else {
                                return true;
                        }
                    }
                </script>';

if ($course_id != 0) {
    if ($status != USER_GUEST and !get_user_email_notification($uid, $course_id)) {
        $tool_content .= "<div class='col-12 mt-3'>
        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoUserEmailNotification
            (<a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langModify</a>)</span></div></div>";
    }
} else {
    if (!get_mail_ver_status($uid)) {
        $tool_content .= "<div class='col-12 mt-3'>
        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoUserEmailNotification
            (<a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langModify</a>)</span></div></div>";
    }
}

$courseParam = ($course_id === 0) ? '' : '?course=' . $course_code;
if (isset($_GET['mid'])) {
    if ($courseParam != '') {
        $msg_id_param = '&amp;mid='.intval($_GET['mid']);
    } else {
        $msg_id_param = '?mid='.intval($_GET['mid']);
    }
} else {
    $msg_id_param = '';
}
// action bar
if (!isset($_GET['showQuota'])) {
    if (isset($_GET['upload'])) {
        $navigation[] = array('url' => "index.php", 'name' => $langDropBox);
        if (isset($_GET['type'])) {
            $pageName = $langNewCourseMessage;
        } else {
            $pageName = $langNewPersoMessage;
        }
    } else {
        if ($course_id != 0) {
            $action_bar = action_bar(array(
                                array('title'   => $langNewCourseMessage,
                                      'url'     => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;upload=1&amp;type=cm",
                                      'icon'    => 'fa-file-pen',
                                      'level'   => 'primary-label',
                                      'button-class' => 'btn-success'),
                                array('title'   => $langQuotaBar,
                                      'url'     => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;showQuota=TRUE",
                                      'icon'    => 'fa-pie-chart',
                                      'show'    => $is_editor),
                                array('title'   => $langDropboxMassDelete,
                                      'url'     => 'javascript:void(0)',
                                      'class'   => 'delete_all_in',
                                      'icon'    => 'fa-xmark')
                            ));
        } else {
            $action_bar = action_bar(array(
                                array('title'   => $langNewCourseMessage,
                                      'url'     => "$_SERVER[SCRIPT_NAME]?upload=1&amp;type=cm",
                                      'icon'    => 'fa-file-pen',
                                      'level'   => 'primary-label',
                                      'button-class' => 'btn-success'),
                                array('title'   => $langNewPersoMessage,
                                      'url' => "$_SERVER[SCRIPT_NAME]?upload=1",
                                      'icon' => 'fa-user-pen',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-success',
                                      'show' => $personal_msgs_allowed),
                                array('title' => $langDropboxMassDelete,
                                      'url' => 'javascript:void(0)',
                                      'icon' => 'fa-xmark',
                                      'class' => 'delete_all_in')
                            ));
        }
        $tool_content .= $action_bar;
    }
}

if (isset($_GET['course']) and isset($_GET['showQuota']) and $_GET['showQuota']) {
    $pageName = $langQuotaBar;
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langDropBox);
    $space_released = 0;

    if ($is_editor && ($diskUsed/$diskQuotaDropbox >= 0.8)) { // 80% of quota
        $space_to_free = format_file_size($diskQuotaDropbox*0.8);
        if (isset($_GET['free']) && $_GET['free']) { // free some space
            $sql = "SELECT da.filename, da.id, da.filesize FROM dropbox_attachment as da, dropbox_msg as dm
                    WHERE da.msg_id = dm.id
                    AND dm.course_id = ?d
                    ORDER BY dm.timestamp ASC";
            $result = Database::get()->queryArray($sql, $course_id);
            foreach ($result as $file) {
                unlink($message_dir . "/" . $file->filename);
                $space_released += $file->filesize;
                Database::get()->query("DELETE FROM dropbox_attachment WHERE id = ?d", $file->id);
                if ($space_released >= $diskQuotaDropbox/20) { // we want to keep 20% of quota
                    break;
                }
            }
            $message = sprintf($langDropboxFreeSpaceSuccess, format_file_size($space_released));
            Session::flash('message', $message);
            Session::flash('alert-class', 'alert-success');
        } else { //provide option to free some space
            $message = "<a onclick=\"return confirm('".sprintf($langDropboxFreeSpaceConfirm, $space_to_free)."');\" href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;showQuota=TRUE&amp;free=TRUE'>".sprintf($langDropboxFreeSpace, $space_to_free)."</a>";
            Session::flash('message', $message);
            Session::flash('alert-class', 'alert-danger');
        }
    }
    $backPath = "$_SERVER[SCRIPT_NAME]" . (($course_id != 0)? "?course=$course_code" : "");
    showquota($diskQuotaDropbox, $diskUsed-$space_released);
    exit;
}

if (isset($_REQUEST['upload']) && $_REQUEST['upload'] == 1) { //new message form
    if ($course_id == 0) {
        if (isset($_GET['type']) && $_GET['type'] == 'cm') {
            $type = 'cm';
        } else {
            $type = 'pm';
        }
    }

    if ($course_id == 0 && $type == 'pm') {
        if (!$personal_msgs_allowed) {
            $tool_content .= "
                <div class='col-12'>
                    <div class='alert alert-warning'>
                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                        <span>$langGeneralError</span>
                    </div>
                </div>";
            draw($tool_content, 1, null, $head_content);
            exit;
        }
        $tool_content .= "
                <div class='row'>
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form class='form-horizontal' role='form' id='newmsg' method='post' action='message_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
    } elseif ($course_id == 0 && $type == 'cm') {
        $tool_content .= "
                <div class='row'>
                    <div class='col-lg-6 col-12'>
        ";
     $tool_content .= "<div class='form-wrapper form-edit border-0 px-0'>
                            <form class='form-horizontal' role='form' method='post' action='message_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
    } else {
        $type = 'cm'; //only course messages are allowed in the context of a course
$tool_content .= "<div class='col-12'>
                     <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit border-0 px-0'>
                                <form class='form-horizontal' role='form' method='post' action='message_submit.php?course=$course_code' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
    }
                    $tool_content .= "
                        <fieldset>
                                <legend class='mb-0' aria-label='$langForm'></legend>
                                <div class='form-group'>
                                    <label for='titleid' class='col-sm-12 control-label-notes'>$langSender</label>
                                    <div class='col-sm-12'>
                                    <input id='titleid' type='text' class='form-control' value='" . q(uid_to_name($uid)) . "' disabled>
                                    </div>
                                </div>";
                                if ($type == 'cm' && $course_id == 0) {//course message from central interface
                                    //find user's courses with message module (aka dropbox) activated
                                    $sql = "SELECT course.code code, course.title title
                                            FROM course, course_user, course_module
                                            WHERE course.id = course_user.course_id
                                            AND course.id = course_module.course_id
                                            AND course_module.module_id = ?d
                                            AND course_module.visible = ?d
                                            AND course_user.user_id = ?d
                                            ORDER BY title";
                                    $res = Database::get()->queryArray($sql, MODULE_ID_MESSAGE, 1, $uid);

                                    $head_content .= "<script type='text/javascript'>
                                                        $(document).on('change','#courseselect',function(){
                                                        $.ajax({
                                                            type: 'POST',
                                                            dataType: 'json',
                                                            url: 'load_recipients.php',
                                                            data: {'course' : $('#courseselect').val() }
                                                        }).done(function(data) {
                                                            $('#select-recipients').empty();
                                                            if(!($.isEmptyObject(data))) {
                                                            $('#select-recipients').empty();
                                                            $.each(data, function(key,value){
                                                                if (key.charAt(0) == '_') {
                                                                $('#select-recipients').prepend('<option value=\'' + key + '\'>' + value + '</option>');
                                                                } else {
                                                                $('#select-recipients').append('<option value=\'' + key + '\'>' + value + '</option>');
                                                                }
                                                            });
                                                            }
                                                            $('#select-recipients').select2('destroy');
                                                            $('#select-recipients').select2();
                                                        });
                                                        });
                                                    </script>";

                                    $tool_content .= "
                                        <div class='form-group mt-4'>
                                            <label for='courseselect' class='col-sm-12 control-label-notes'>$langCourse</label>
                                            <div class='col-sm-12'>
                                                <select id='courseselect' class='form-select' name='course'>
                                                    <option value='-1'>&nbsp;</option>";
                                    foreach ($res as $course) {
                                        $tool_content .="<option value='".$course->code."'>".q($course->title)."</option>";
                                    }
                                    $tool_content .="    </select>
                                                    </div>
                                                    </div>";
                                }

                                if ($course_id != 0 || ($type == 'cm' && $course_id == 0)) {
                                    $tool_content .= "
                                    <div class='form-group mt-4'>
                                        <label for='select-recipients' class='col-sm-12 control-label-notes'>$langSendTo <span class='asterisk Accent-200-cl'>(*)</span></label>
                                        <div class='col-sm-12'>
                                            <select name='recipients[]' multiple='multiple' class='form-select' id='select-recipients'>";

                                    if ($course_id != 0) {//course messages
                                        $student_to_student_allow = get_config('dropbox_allow_student_to_student');

                                        if ($is_editor || $student_to_student_allow == 1) {
                                            //select all users from this course except yourself
                                            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                                                        FROM user u, course_user cu
                                                        WHERE cu.course_id = ?d
                                                        AND cu.user_id = u.id
                                                        AND cu.status != ?d
                                                        AND u.id != ?d
                                                        ORDER BY name";

                                            $res = Database::get()->queryArray($sql, $course_id, USER_GUEST, $uid);

                                            // find course groups (if any)
                                            $sql_g = "SELECT `group`.id, name, COUNT(user_id) AS members_present
                                                FROM `group` LEFT JOIN group_members ON group_id = `group`.id
                                                WHERE course_id = ?d
                                                GROUP BY `group`.id, `group`.name
                                                ORDER BY name";
                                            $result_g = Database::get()->queryArray($sql_g, $course_id);
                                            foreach ($result_g as $res_g) {
                                                if (isset($_GET['group_id']) and $_GET['group_id'] == $res_g->id) {
                                                    $selected_group = " selected";
                                                } else {
                                                    $selected_group = "";
                                                }
                                                $disabled_group = $res_g->members_present? '': 'disabled';
                                                $tool_content .= "<option value = '_$res_g->id' $disabled_group $selected_group>".q($res_g->name)."</option>";
                                            }
                                        } else {
                                            //if user is student and student-student messages not allowed for course messages show teachers
                                            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                                                        FROM user u, course_user cu
                                                        WHERE cu.course_id = ?d
                                                        AND cu.user_id = u.id
                                                        AND (cu.status = ?d OR cu.editor = ?d)
                                                        AND u.id != ?d
                                                        ORDER BY name";

                                            $res = Database::get()->queryArray($sql, $course_id, USER_TEACHER, 1, $uid);

                                            //check if user is group tutor
                                            $sql_g = "SELECT `g`.id, `g`.name FROM `group` as `g`, `group_members` as `gm`
                                            WHERE `g`.id = `gm`.group_id AND `g`.course_id = ?d AND `gm`.user_id = ?d AND `gm`.is_tutor = ?d";

                                            $result_g = Database::get()->queryArray($sql_g, $course_id, $uid, 1);
                                            foreach ($result_g as $res_g) {
                                                $tool_content .= "<option value = '_$res_g->id'>".q($res_g->name)."</option>";
                                            }

                                            //find user's group and their tutors
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
                                            if (isset($_GET['user_id']) and $_GET['user_id'] == $r->user_id) {
                                                $selected_user = " selected";
                                            } else {
                                                $selected_user = "";
                                            }
                                            $tool_content .= "<option value=" . $r->user_id . " $selected_user>" . q($r->name) . " (".q($r->username).")" . "</option>";
                                        }
                                        if (isset($tutors)) {
                                            foreach ($tutors as $key => $value) {
                                                $tool_content .= "<option value=" . $key . ">" . q($value) . "</option>";
                                            }
                                        }
                                    }

                                    $tool_content .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                                        </div>
                                    </div>";
                                } elseif ($type == 'pm' && $course_id == 0) {//personal messages
                                    $head_content .= "<script type='text/javascript'>
                                                        $(document).ready(function () {
                                                            $('#recipients').select2({
                                                                placeholder:'$langSearch',
                                                                multiple: true,
                                                                minimumInputLength: 3,
                                                                ajax: {
                                                                    url: 'load_recipients.php?autocomplete=1',
                                                                    dataType: 'json',
                                                                    quietMillis: 250,
                                                                    processResults: function (data) { // parse the results into the format expected by Select2.
                                                                        // since we are using custom formatting functions we do not need to alter the remote JSON data
                                                                        return {
                                                                            results: data.items
                                                                        };
                                                                    },
                                                                    cache: true
                                                                },
                                                            });
                                                        });
                                                    </script>";

                                    if (isset($_GET['id'])) {
                                        $q = Database::get()->querySingle("SELECT id, CONCAT_WS(' ',surname,givenname) AS name FROM user WHERE id = ?d", $_GET['id']);
                                        if ($q) {
                                            $u_name = $q->name;
                                        }
                                        $tool_content .= "<input type='hidden' name='recipients' value='$_GET[id]'>
                                                        <div class='form-group mt-4'>
                                                            <div class='col-sm-12 control-label-notes'>$langSendTo</div>
                                                            <div class='col-sm-12'>
                                                                <div>$u_name</div>
                                                            </div>
                                                        </div>";
                                    } else {
                                        $tool_content .= "
                                                        <div class='form-group mt-4'>
                                                            <label for='recipients' class='col-sm-12 control-label-notes'>$langSendTo</label>
                                                            <div class='col-sm-12'>
                                                                <select name='recipients[]' class='form-select' id='recipients'></select><span class='help-block'>$langSearchSurname</span>
                                                            </div>
                                                        </div>";
                                    }
                                }

                                $tool_content .= "
                                    <div class='form-group mt-4'>
                                        <label for='message_title_id' class='col-sm-12 control-label-notes'>$langSubject <span class='asterisk Accent-200-cl'>(*)</span></label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='message_title' id='message_title_id'>
                                        </div>
                                    </div>";

                                $tool_content .= "
                                    <div class='form-group mt-4'>
                                        <label for='body' class='col-sm-12 control-label-notes'>$langMessage</label>
                                        <div class='col-sm-12'>
                                            ".rich_text_editor('body', 4, 20, '')."
                                        </div>
                                    </div>";
                                if ($course_id != 0 || ($type == 'cm' && $course_id == 0)) {
                                    enableCheckFileSize();
                                    $tool_content .= "
                                    <div class='form-group mt-4'>
                                        <label for='file_ID' class='col-sm-12 control-label-notes mb-2'>$langAttachedFile</label>
                                        <div class='col-sm-12'>" .
                                            fileSizeHidenInput() . "
                                            <input type='file' name='file' id='file_ID'>
                                            <div class='help-block' style='margin-bottom: 0px;'><small>$langMaxFileSize " . ini_get('upload_max_filesize') . "</small></div>
                                        </div>
                                    </div>";
                                }

                                $tool_content .= "
                                    <div class='form-group mt-4'>
                                        <div class='col-xs-10 col-xs-offset-2'>
                                            <div class='checkbox'>
                                            <label class='label-container' aria-label='$langSelect'>
                                                <input type='checkbox' name='mailing' value='1' checked />
                                                <span class='checkmark'></span>
                                                $langMailToUsers
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
                                                            'value' => $langSend
                                                        ),
                                                        array(
                                                            'class' => 'cancelAdminBtn ms-1',
                                                            'href'  => "$_SERVER[SCRIPT_NAME]".(($course_id != 0)? "?course=$course_code" : "")
                                                        )
                                                ))
                                                ."
                                        
                                            
                                        
                                        </div>
                                    </div>
                        </fieldset>
                        ". generate_csrf_token_form_field() ."
        </form></div></div>"; // end col-lg-6 col-12

    if($course_id > 0 && isset($_GET['upload']) && isset($_GET['type']) && $_GET['type'] == 'cm'){
        $tool_content .= "
                <div class='form-content-modules d-none d-lg-block'>
                    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                </div>
            </div>
        </div> 
        "; // end col-12
    }

    if (($course_id == 0 && $type == 'cm') or ($course_id == 0 && $type == 'pm')){
        $tool_content .= "
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                </div>
            </div>"; // end row
    }
    if ($course_id != 0 || ($type == 'cm' && $course_id == 0)) {
        $head_content .= "<script type='text/javascript'>
            $(document).ready(function () {

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

            </script>
        ";
    }
} else { //mailbox
    load_js('datatables');
    load_js('trunk8');
    $head_content .= "<script type='text/javascript'>
                        $(document).ready(function() {

                            // bootstrap tabs load external content via AJAX
                            $('a[data-bs-toggle=\"tab\"]').on('show.bs.tab', function (e) {
                                var contentID = $(e.target).attr('data-bs-target');
                                var contentURL = $(e.target).attr('href');
                                $(contentID).load(contentURL);

                                if(contentID == '#inbox') {
                                    $('.delete_all_out').unbind('click');
                                    $('.delete_all_out').addClass('delete_all_in').removeClass('delete_all_out');
                                } else if (contentID == '#outbox') {
                                    $('.delete_all_in').unbind('click');
                                    $('.delete_all_in').addClass('delete_all_out').removeClass('delete_all_in');
                                }
                            });

                            // trap links to open inside tabs
                            $('.tab-content').on('click', 'a', function(e) {
                                var in_content = $(e.currentTarget).parents('.panel-body').length;
                                if (!in_content && e.currentTarget.className != 'outtabs' &&
                                    e.currentTarget.className.indexOf('paginate_button') == -1) {
                                    e.preventDefault();
                                    $(this).closest('.tab-pane').load(this.href);
                                } else if (in_content) {
                                    // open links in message bodies in new window
                                    e.currentTarget.target = '_blank';
                                }
                            });

                            // show 1st tab
                            $('#dropboxTabs a:first').tab('show');

                            $('body').on('click', '.attachment-delete-button', function (e) {
                                e.preventDefault();
                                $('.attachment-section').fadeOut(300, function() {
                                    $(this).remove();
                                });
                                return false;
                            });
                        });

                    </script>";

    $tool_content .= "
                      <div id='dropboxTabs' class='col-12 mt-3'>
                        <ul class='nav nav-tabs' role='tablist'>
                            <li class='nav-item' role='presentation'><a class='nav-link' id='inboxA' data-bs-target='#inbox' role='tab' data-bs-toggle='tab' href= 'inbox.php" . $courseParam . $msg_id_param . "'><i class='fa-solid fa-inbox pe-1'></i>$langDropBoxInbox</a></li>
                            <li class='nav-item' role='presentation'><a class='nav-link' id='outboxA' data-bs-target='#outbox' role='tab' data-bs-toggle='tab' href='outbox.php" . $courseParam . "'><i class='fa-solid fa-paper-plane pe-1'></i>$langDropBoxOutbox</a></li>
                        </ul>
                        <div class='tab-content'>
                            <div role='tabpanel' class='tab-pane fade show active' id='inbox' aria-labelledby='inboxA'></div>
                            <div role='tabpanel' class='tab-pane fade' id='outbox' aria-labelledby='outboxA'></div>
                        </div>
                      </div>";
}

if ($course_id == 0) {
    draw($tool_content, 1, null, $head_content);
} else {
    draw($tool_content, 2, null, $head_content);
}

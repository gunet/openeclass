<?php

/* ========================================================================
 * Open eClass 3.15
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

/**
 * @file invite_one.php
 * @brief Invite many external user to course
 */
$require_current_course = true;
$require_course_admin = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/user/invite_functions.php';

load_js('bootstrap-datetimepicker');

if (!get_config('course_invitation')) {
    redirect_to_home_page('modules/user/?course=' . $course_code);
}

if (isset($_POST['emailNewBodyInput']) and $_POST['emailNewBodyInput']) {
    $email_body = purify($_POST['emailNewBodyEditor']);
    $email_subject = purify($_POST['email_subject']);
} else {
    $email_body = $default_email_body;
    $email_subject = $default_email_subject;
}

if (isset($_POST['submit']) and isset($_FILES['userfile'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    if (isset($_POST['expires_at'])) {
        $expires_at = $_POST['expires_at'];
    } else {
        $expires_at = null;
    }

    $file = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['userfile']['tmp_name']);
    $sheet = $file->getActiveSheet();
    $errorLines = $existingLines = [];
    $count = 0;
    foreach ($sheet->getRowIterator() as $row) {
        $data = [];
        $cellIterator = $row->getCellIterator();
        foreach ($cellIterator as $cell) {
            $value = trim($cell->getValue());
            if ($value !== '') {
                $data[] = $value;
            }
        }

        if (count($data) > 3 or !valid_email($data[0])) {
            $errorLines[] = $data;
            continue;
        }

        $email = canonicalize_whitespace($data[0]);
        $surname = $data[1]? canonicalize_whitespace($data[1]): '';
        $givenname = $data[2]? canonicalize_whitespace($data[2]): '';

        $user = Database::get()->querySingle('SELECT * FROM user WHERE email = ?s', $email);
        if ($user) {
            Database::get()->query('INSERT IGNORE INTO course_user
                SET user_id = ?d, course_id = ?d, status = ?d, reg_date = NOW(), document_timestamp = NOW()',
                $user->id, $course_id, USER_STUDENT);
            $existingLines[] = $data;
            continue;
        }

        $token = bin2hex(random_bytes(8));
        Database::get()->query('DELETE FROM course_invitation
            WHERE course_id = ?d AND email = ?s',
            $course_id, $email);
        Database::get()->query('INSERT INTO course_invitation
            SET email = ?s, surname = ?s, givenname = ?s, created_at = NOW(),
                course_id = ?d, identifier = ?s, expires_at = ?s',
            $email, $surname, $givenname,
            $course_id, $token, $expires_at);
        send_invitation($email, $token, $email_subject, $email_body);
        $count++;
    }
    //Session::Messages("$langCourseInvitationsSent $count $langUsersS!", 'alert-success');
    Session::flash('message',"$langCourseInvitationsSent $count $langUsersS!");
    Session::flash('alert-class', 'alert-success');
    if ($errorLines) {
        $errorList = '<ul>' . implode('', array_map(function ($item) {
            return '<li>' . q(implode(' ', $item)) . '</li>';
        }, $errorLines)) . '</ul>';
        //Session::Messages("$langErrorInserting" .$errorList, 'alert-danger');
        Session::flash('message',"$langErrorInserting" .$errorList);
        Session::flash('alert-class', 'alert-danger');
    }
    if ($existingLines) {
        $existingList = '<ul>' . implode('', array_map(function ($item) {
            return '<li>' . q(implode(' ', $item)) . '</li>';
        }, $existingLines)) . '</ul>';
        //Session::Messages("$langAlreadyRegisteredUsers:" .$existingList, 'alert-success');
        Session::flash('message',"$langAlreadyRegisteredUsers:" .$existingList);
        Session::flash('alert-class', 'alert-success');
    }
    redirect_to_home_page('modules/user/invite.php?course=' . $course_code);
}

$toolName = $langCourseInviteMany;
$navigation[] = ['url' => "{$urlAppend}modules/user/index.php?course=$course_code", 'name' => $langUsers];
$navigation[] = ['url' => "invite.php?course=$course_code", 'name' => $langCourseUsersInvitation];

$tool_content .= action_bar([
    [ 'title' => $langBack,
      'url' => "invite.php?course=$course_code",
      'icon' => 'fa-reply',
      'level' => 'primary-label' ],
    ]);

enableCheckFileSize();
$tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' enctype='multipart/form-data' method='post' action='invite_many.php?course=$course_code'>" .
                    generate_csrf_token_form_field() . "
                    <fieldset>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>$langCourseInvitationUsersExcelInfo</p>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='userfile' class='col-sm-12 control-label-notes'>$langWorkFile:</label>
                            <div class='col-sm-12'>" . fileSizeHidenInput() . "
                                <input type='file' id='userfile' name='userfile'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>$langExpirationDate:</label>
                            <div class='col-sm-12'>
                                <div class='input-group'>
                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                    <input class='form-control mt-0 border-start-0' id='user_date_expires_at' name='expires_at' type='text' value='22-09-2027 16:59'>
                                    
                                </div>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <div class='col-sm-10 col-sm-offset-2'>
                                <div class='checkbox'>
                                    <label class='label-container'>
                                        <input name='customEmailBody' id='customEmailBody' type='checkbox'> 
                                        <span class='checkmark'></span>
                                        $langCustomEmailBody
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class='form-group emailsubject hidden mt-4'>
                            <label for='email_subject' class='col-sm-12 control-label-notes'>$langTopic</label>
                            <div class='col-sm-12'>
                                    <input class='form-control' id='email_subject' name='email_subject' type='text' value='" . q($default_email_subject) . "'>
                            </div>
                        </div>

                        <div class='form-group emailbody hidden mt-4'>
                            <label for='email_body' class='col-sm-12 control-label-notes'>$langBodyMessage</label>
                            <div class='col-sm-12'>" .
                                rich_text_editor('emailNewBodyEditor', 4, 20, $default_email_body) . "
                            </div>
                            <input type='hidden' class='emailNewBodyInput' name='emailNewBodyInput' value=0>
                        </div>

                        <div class='form-group customMailHelp hidden mt-4'>
                            <label for='email_body' class='col-sm-12 control-label-notes'></label>
                            <div class='col-sm-12'>
                                <div class='alert alert-info'>
                                    $langInvitationCustomEmail
                                </div>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <div class='col-sm-offset-2 col-sm-10 d-flex gap-1'>" .
                                form_buttons([[ 'class' => 'btn-primary',
                                                'name' => 'submit',
                                                'value' => $langUpload,
                                                'javascript' => '' ],
                                              [ 'class' => 'cancelAdminBtn',
                                                'href' => "invite.php?course=$course_code" ]]) . "
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
    </div>

    <script>
        $(function() {
            $('#customEmailBody').change(function() {
                if ($(this).is(':checked')) {
                  $('.emailbody, .emailsubject, .customMailHelp').removeClass('hidden');
                  $('.emailNewBodyInput').val(1);
                } else {
                  $('.emailbody, .emailsubject, .customMailHelp').addClass('hidden');
                  $('.emailNewBodyInput').val(0);
                }
            });

            $('#user_date_expires_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                minuteStep: 5,
                autoclose: true
            });
        });
    </script>";

draw($tool_content, 2, null, $head_content);

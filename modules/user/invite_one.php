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
 * @brief Invite one external user to course
 */
$require_current_course = true;
$require_course_admin = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/user/invite_functions.php';

if (!get_config('course_invitation')) {
    redirect_to_home_page('modules/user/?course=' . $course_code);
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('email_form'));
    $v->rule('email', 'email_form');
    $invite = null;
    if ($v->validate()) {
        if (isset($_POST['id'])) {
            $invite = Database::get()->querySingle('SELECT * FROM course_invitation
                WHERE course_id = ?d AND id = ?d', $course_id, getDirectReference($_POST['id']));
        }
        $user = Database::get()->querySingle('SELECT * FROM user WHERE email = ?s', $_POST['email_form']);
        if ($user) {
            $course_user = Database::get()->querySingle('SELECT * FROM course_user
                WHERE user_id = ?d AND course_id = ?d', $user->id, $course_id);
            if ($course_user) {
                Session::Messages(sprintf("$langUserWithEmail <b>%s</b> $langAlreadyRegistered", q($_POST['email_form'])), 'alert-info');
            } else {
                Database::get()->query('INSERT INTO course_user
                    SET user_id = ?d, course_id = ?d, status = ?d, reg_date = NOW(), document_timestamp = NOW()',
                    $user->id, $course_id, USER_STUDENT);
                Session::Messages(sprintf("$langUserWithEmail <b>%s</b> (%s) $langAlreadyAccount",
                    q($_POST['email_form']), q("{$user->surname} {$user->givenname}")), 'alert-info');
            }
            if ($invite) {
                Database::get()->query('UPDATE course_invitation
                    SET registered_at = NOW() WHERE id = ?d',
                    $invite->id);
            }
            redirect_to_home_page('modules/user/invite_one.php?course=' . $course_code);
        }
        if (isset($_POST['expires_at'])) {
            $expires_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['expires_at'])->format("Y-m-d H:i");
        } else {
            $expires_at = null;
        }
        if ($invite) {
            $token = $invite->identifier;
        } else {
            $token = bin2hex(random_bytes(8));
        }
        $email = canonicalize_whitespace($_POST['email_form']);
        $surname = canonicalize_whitespace($_POST['surname_form']);
        $givenname = canonicalize_whitespace($_POST['givenname_form']);
        if ($invite) {
            Database::get()->query('DELETE FROM course_invitation
                WHERE course_id = ?d AND email = ?s AND id <> ?d',
                $course_id, $email, $invite->id);
            Database::get()->query('UPDATE course_invitation
                SET email = ?s, surname = ?s, givenname = ?s, expires_at = ?s
                WHERE id = ?d',
                $email, $surname, $givenname,
                $expires_at, $invite->id);
        } else {
            Database::get()->query('DELETE FROM course_invitation
                WHERE course_id = ?d AND email = ?s',
                $course_id, $email);
            Database::get()->query('INSERT INTO course_invitation
                SET email = ?s, surname = ?s, givenname = ?s, created_at = NOW(),
                    course_id = ?d, identifier = ?s, expires_at = ?s',
                $email, $surname, $givenname,
                $course_id, $token, $expires_at);
        }
        if (isset($_POST['emailNewBodyInput']) and $_POST['emailNewBodyInput']) {
            $email_body = purify($_POST['emailNewBodyEditor']);
            $email_subject = purify($_POST['email_subject']);
        } else {
            $email_body = $default_email_body;
            $email_subject = $default_email_subject;
        }
        send_invitation($email, $token, $email_subject, $email_body);
        Session::Messages($langCourseInvitationSent, 'alert-success');
        redirect_to_home_page('modules/user/invite_one.php?course=' . $course_code);
    }
}

$id = null;
$value_givenname = $value_surname = $value_email = '';
if (isset($_GET['id'])) {
    $id = getDirectReference($_GET['id']);
    $id_indirect = getIndirectReference($id);
    $invite = Database::get()->querySingle('SELECT * FROM course_invitation
        WHERE course_id = ?d AND id = ?d', $course_id, $id);
    if (!$invite) {
        $id = null;
    } else {
        if ($invite->expires_at) {
            $exp_date = DateTime::createFromFormat("Y-m-d H:i:s", $invite->expires_at);
        }
        $value_givenname = " value='" . q($invite->givenname) . "'";
        $value_surname = " value='" . q($invite->surname) . "'";
        $value_email = " value='" . q($invite->email) . "'";
    }
}
if (!isset($exp_date)) {
    $exp_date = (new DateTime())->add(DateInterval::createFromDateString('+1 Year'));
}
$value_date_expires = " value='" . $exp_date->format("d-m-Y H:i") . "'";

$toolName = $langCourseInviteOne;
$navigation[] = ['url' => "{$urlAppend}modules/user/?course=$course_code", 'name' => $langUsers];
$navigation[] = ['url' => "invite.php?course=$course_code", 'name' => $langCourseUsersInvitation];

$tool_content .= action_bar([
    [ 'title' => $langBack,
      'url' => "invite.php?course=$course_code",
      'icon' => 'fa-reply',
      'level' => 'primary-label' ],
    ]);

$tool_content .= "
    <div class='form-wrapper'>
      <form class='form-horizontal' role='form' action='invite_one.php' method='post'>" .
        ($id? "<input type='hidden' name='id' value='$id_indirect'>": '') .
        generate_csrf_token_form_field() . "
        <fieldset>
          <div class='form-group'>
            <label for='givenname_form' class='col-sm-2 control-label'>$langName:</label>
            <div class='col-sm-10'><input class='form-control' id='givenname_form' type='text' name='givenname_form' placeholder='$langName' $value_givenname></div>
          </div>
          <div class='form-group'>
            <label for='surname_form' class='col-sm-2 control-label'>$langSurname:</label>
            <div class='col-sm-10'><input class='form-control' id='surname_form' type='text' name='surname_form' placeholder='$langSurname' $value_surname></div>
          </div>
          <div class='form-group'>
            <label for='email_form' class='col-sm-2 control-label'>e-mail:</label>
            <div class='col-sm-10'><input class='form-control' id='email_form' type='text' name='email_form' placeholder='user@example.com' $value_email></div>
          </div>
          <div class='form-group'>
            <label class='col-sm-2 control-label'>$langExpirationDate:</label>
            <div class='col-sm-10'>
              <div class='input-group'>
                <input class='form-control' id='user_date_expires_at' name='expires_at' type='text' $value_date_expires>
                <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
              </div>
            </div>
          </div>

          <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
              <div class='checkbox'>
                <label>
                  <input name='customEmailBody' id='customEmailBody' type='checkbox'> $langCustomEmailBody
                </label>
              </div>
            </div>
          </div>
          <div class='form-group emailsubject hidden'>
            <label for='email_subject' class='col-sm-2 control-label'>$langTopic</label>
            <div class='col-sm-10'>
                <input class='form-control' id='email_subject' name='email_subject' type='text' value='" . q($default_email_subject) . "'>
            </div>
          </div>
          <div class='form-group emailbody hidden'>
            <label for='email_body' class='col-sm-2 control-label'>$langBodyMessage</label>
            <div class='col-sm-10'>" .
              rich_text_editor('emailNewBodyEditor', 4, 20, $default_email_body) . "
            </div>
            <input type='hidden' class='emailNewBodyInput' name='emailNewBodyInput' value=0>
          </div>
          <div class='form-group customMailHelp hidden'>
            <label for='email_body' class='col-sm-2 control-label'></label>
            <div class='col-sm-10'>
              <div class='alert alert-info'>
                $langInvitationCustomEmail
              </div>
            </div>
          </div>
          <div class='form-group'>
       <div class='col-sm-offset-2 col-sm-10'>
        <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
       </div>
      </fieldset>
     </form>
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
          });
      </script>";

draw($tool_content, 2, null, $head_content);

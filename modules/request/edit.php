<?php
/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'modules/request/functions.php';
require_once 'include/sendMail.inc.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $request = Database::get()->querySingle('SELECT * FROM request
        WHERE id = ?d AND course_id = ?d',
        $id, $course_id);
    if (!$request) {
        redirect_to_home_page("courses/$course_code");
    }

    if ($request->type_id) {
        $data['type'] = Database::get()->querySingle('SELECT * FROM request_type
            WHERE id = ?d', $request->type_id);
        $data['field_data'] = [];
        Database::get()->queryFunc('SELECT request_field.id AS field_id,
                name, data, datatype, `values`, request_field.description
            FROM request
                JOIN request_field ON request.type_id = request_field.type_id
                LEFT JOIN request_field_data ON request.id = request_field_data.request_id AND
                          request_field.id = request_field_data.field_id
            WHERE request.id = ?d ORDER BY sortorder', function ($field) use (&$data) {
                if ($field->datatype == REQUEST_FIELD_DATE and $field->data) {
                    $tmpDate = DateTime::createFromFormat('Y-m-d', $field->data);
                    if ($tmpDate) {
                        $field->data = $tmpDate->format('d-m-Y');
                    } else {
                        $field->data = null;
                    }
                }
                $data['field_data'][$field->field_id] = $field;
                $data['field_data'][$field->field_id]->name = getSerializedMessage($field->name);
                if ($field->values) {
                    $data['field_data'][$field->field_id]->values = array_map('getSerializedMessage',
                        unserialize($field->values));
                    if ($field->data and
                        !in_array($field->data, $data['field_data'][$field->field_id]->values)) {
                            $data['field_data'][$field->field_id]->values[] = $field->data;
                    }
                }
            }, $request->id);
    } else {
        $data['field_data'] = null;
    }
    $data['request'] = $request;
    $data['backUrl'] = $urlAppend . "modules/request/index.php?course=$course_code&id=$id";
    $data['targetUrl'] = $urlAppend . "modules/request/edit.php?course=$course_code&id=$id";
    $can_modify = $is_editor || $request->creator_id == $uid ||
        in_array($uid, $data['assigned']);
    if (!$can_modify) {
        redirect_to_home_page("modules/request/index.php?course=$course_code&id=$id");
    }

    $navigation[] = array('url' => $urlAppend . "modules/request/index.php?course=$course_code", 'name' => trans('langRequests'));
    $navigation[] = array('url' => $data['backUrl'], 'name' => $request->title);
    $toolName = trans('langEditRequest');

    if (isset($_POST['token'])) {
        if (!validate_csrf_token($_POST['token'])) {
            csrf_token_error();
        }
        $args = [];
        $query = [];
        $comment = '';
        if (isset($_POST['requestTitle']) and $_POST['requestTitle'] != $request->title) {
            $query[] = 'title = ?s';
            $newTitle = canonicalize_whitespace($_POST['requestTitle']);
            $args[] = $newTitle;
            $comment .= '<p>' . sprintf(trans('langRequestFieldChange'),
                '<b>' . trans('langTitle') . '</b>',
                '<b>' . q($newTitle) . '</b>') . '<br>' . $langFrom . ': ' .
                q($request->title) . '</p>';
        }
        if (isset($_POST['requestDescription']) and $_POST['requestDescription'] != $request->description) {
            $query[] = 'description = ?s';
            $newDescription = purify($_POST['requestDescription']);
            $args[] = $newDescription;
            $comment .= '<p>' . sprintf(trans('langRequestFieldChange'),
                '<b>' . trans('langDescription') . '</b>',
                '<b>' . $newDescription . '</b>') . '<br>' . $langFrom . ': ' .
                $request->description . '</p>';
        }
        if ($args) {
            Database::get()->query('UPDATE request
                SET ' . implode(', ', $query) . 'WHERE id = ?d',
                $args, $id);
        }
        if ($data['field_data']) {
            foreach ($data['field_data'] as $field) {
                $name = 'field_' . $request->type_id . '_' . $field->field_id;
                if (isset($_POST[$name]) and $field->datatype == REQUEST_FIELD_DATE) {
                    $tmpDate = DateTime::createFromFormat('d-m-Y', $_POST[$name]);
                    if ($tmpDate) {
                        $_POST[$name] = $tmpDate->format('Y-m-d');
                    } else {
                        $_POST[$name] = null;
                    }
                }
                if (isset($_POST[$name]) and $_POST[$name] != $field->data) {
                    $newData = $_POST[$name];
                    Database::get()->query('INSERT INTO request_field_data
                        (request_id, field_id, data) VALUES (?d, ?d, ?s)
                        ON DUPLICATE KEY UPDATE data = ?s',
                        $request->id, $field->field_id, $newData, $newData);
                    $comment .= '<p>' . sprintf(trans('langRequestFieldChange'),
                        '<b>' . q($field->name) . '</b>',
                        '<b>' . q($newData) . '</b>') .
                        ($field->data === ''? '':
                            ('<br>' . $langFrom . ': ' . q($field->data))) . '</p>';
                }
            }
        }
        if ($comment) {
            Database::get()->query('INSERT INTO request_action
                SET request_id = ?d, user_id = ?d, ts = NOW(),
                    old_state = ?d, new_state = ?d, comment = ?s',
                $request->id, $uid, $request->state, $request->state,
                $comment);

            Session::flash('message',trans('langFaqEditSuccess'));
            Session::flash('alert-class', 'alert-success');

            if (isset($_POST['send_mail'])) {
                $recipients = [];
                if (get_user_email_notification($request->creator_id, $course_id)) {
                    $email = uid_to_email($request->creator_id);
                    if ($email) {
                        $recipients[] = $email;
                    }
                }
                $watchers = Database::get()->queryArray('SELECT user_id
                    FROM request_watcher WHERE request_id = ?d',
                    $request->id);
                foreach ($watchers as $watcher) {
                    if (get_user_email_notification($watcher->user_id, $course_id)) {
                        $email = uid_to_email($watcher->user_id);
                        if ($email) {
                            $recipients[] = $email;
                        }
                    }
                }
                $recipients = array_unique($recipients);

                $datetime = format_locale_date(time(), 'short');
                $emailSubject = $langEditRequest . ': ' . $request->title;
                $emailContent = "
                <!-- Header Section -->
                  <div id='mail-header'>
                      <br>
                      <div>
                          <div id='header-title'>" . q($langEditRequest) . ": <a href='{$urlServer}modules/request/index.php?course=$course_code&amp;id=$rid'>" . q($request->title) . "</a>.</div>
                          <ul id='forum-category'> <li><span><b>$langSender:</b></span> <span class='left-space'>" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "</span></li>
                              <li><span><b>$langdate:</b></span> <span class='left-space'>$datetime</span></li>
                          </ul>
                      </div>
                  </div>
                  <!-- Body Section -->
                  <div id='mail-body'>
                      <br>
                      <div><b>$langChangeDescription</b></div>
                      <div id='mail-body-inner'>
                          $comment
                      </div>
                  </div>
                  <!-- Footer Section -->
                  <div id='mail-footer'>
                      <br>
                      <div>
                          <small>" . sprintf($langLinkUnsubscribe, q($currentCourseName)) ." <a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a></small>
                      </div>
                  </div>";
                $emailBody = html2text($emailContent);
                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'],
                    '', $recipients, $emailSubject, $emailBody, $emailContent);
            }

            redirect_to_home_page("modules/request/index.php?course=$course_code&id=" . $request->id);
        }
    }

    $data['action_bar'] = action_bar([
        [ 'title' => $langBack,
          'url' => $data['backUrl'],
          'icon' => 'fa-reply',
          'level' => 'primary' ]], false);

    $data['descriptionEditor'] = rich_text_editor('requestDescription', 4, 20, $request->description);

    load_js('bootstrap-datepicker');
    load_js('bootstrap-combobox');

    view('modules.request.edit', $data);

} else {
    redirect_to_home_page("courses/$course_code");
}

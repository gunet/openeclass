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
require_once 'include/lib/textLib.inc.php';
require_once 'include/sendMail.inc.php';

$toolName = $langNewRequest;

$requestDescription = '';
if (isset($_POST['requestTitle'])) {
    $title = canonicalize_whitespace($_POST['requestTitle']);
    $requestDescription = purify($_POST['requestDescription']);
    if ($title) {
        if (isset($_POST['requestType']) and $_POST['requestType'] != '0') {
            $type_info = Database::get()->querySingle('SELECT id, name
                FROM request_type WHERE id = ?d', $_POST['requestType']);
            $type_id = $type_info->id;
            $type_name = $type_info->name;
        } else {
            $type_id = null;
            $type_name = $langRequestBasicType;
        }
        $state = isset($_POST['assignTo'])? REQUEST_STATE_ASSIGNED: REQUEST_STATE_NEW;
        $result = Database::get()->query('INSERT INTO request
            SET course_id = ?d, title = ?s, description = ?s,
                creator_id = ?d, state = ?d, type_id = ?d,
                open_date = NOW(), change_date = NOW(), close_date = NULL',
            $course_id, $title, $requestDescription,
            $uid, $state, $type_id);
        if ($result) {
            $rid = $result->lastInsertID;
            $watchers = [];
            $watchersSeen = [];
            $recipients = [];
            if (get_user_email_notification($uid, $course_id)) {
                $recipients[] = uid_to_email($uid);
            }

            $assignedNames = [];
            if (isset($_POST['assignTo'])) {
                foreach ($_POST['assignTo'] as $user_id) {
                    $watchers[] = [$rid, $user_id, REQUEST_ASSIGNED, 1];
                    $watchersSeen[$user_id] = true;
                    $assignedNames[] = uid_to_name($user_id);
                    if (get_user_email_notification($user_id, $course_id)) {
                        $recipients[] = uid_to_email($user_id);
                    }
                }
            }
            $assignedNames = implode(', ', $assignedNames);

            $watcherNames = [];
            if (isset($_POST['requestWatchers'])) {
                foreach ($_POST['requestWatchers'] as $user_id) {
                    $watcherNames[] = uid_to_name($user_id);
                    if (!isset($watchersSeen[$user_id])) {
                        $watchers[] = [$rid, $user_id, REQUEST_WATCHER, 1];
                    }
                    if (get_user_email_notification($user_id, $course_id)) {
                        $recipients[] = uid_to_email($user_id);
                    }
                }
            }
            if (count($watchers)) {
                $placeholders = implode(', ', array_fill(0, count($watchers), '(?d, ?d, ?d, ?d)'));
                Database::get()->query("INSERT INTO request_watcher
                    (request_id, user_id, type, notification) VALUES $placeholders",
                    $watchers);
            }
            $watcherNames = implode(', ', $watcherNames);

            $stateName = $stateLabels[$state];

            $fields_text = '';
            if ($type_id) {
                $field_data = [];
                Database::get()->queryFunc('SELECT * FROM request_field
                    WHERE type_id = ?d', function ($field) use ($type_id, $rid, &$field_data, &$fields_text) {
                        $field_name = 'field_' . $type_id . '_' . $field->id;
                        if (isset($_POST[$field_name])) {
                            if ($field->datatype == REQUEST_FIELD_DATE) {
                                $tmpDate = DateTime::createFromFormat('d-m-Y', $_POST[$field_name]);
                                if ($tmpDate) {
                                    $_POST[$field_name] = $tmpDate->format('Y-m-d');
                                } else {
                                    $_POST[$field_name] = '';
                                }
                            } elseif ($field->datatype == REQUEST_FIELD_TEXTAREA) {
                                $_POST[$field_name] = purify($_POST[$field_name]);
                            }
                            $field_data[] = [$rid, $field->id, $_POST[$field_name]];
                        } else {
                            $_POST[$field_name] = '-';
                        }

                        if ($field->datatype == REQUEST_FIELD_DATE and $tmpDate) {
                            $_POST[$field_name] = claro_format_locale_date(
                                trans('dateFormatLong') . ' ' . trans('timeNoSecFormat'),
                                $tmpDate);
                        }
                        if ($field->datatype == REQUEST_FIELD_TEXTAREA) {
                            $field_data_display = $_POST[$field_name];
                        } elseif ($field->datatype == REQUEST_FIELD_TEXTBOX) {
                            $field_data_display = '<pre>' . q($_POST[$field_name]) . '</pre>';
                        } else {
                            $field_data_display = " <span class='left-space'>" .
                                q($_POST[$field_name]) . "</span>";
                        }
                        $fields_text .= "<div><b>" . q($field->name) .
                            ":</b>" . $field_data_display . "<br>";
                    }, $type_id);
                if (count($field_data)) {
                    $placeholders = implode(', ', array_fill(0, count($field_data), '(?d, ?d, ?s)'));
                    Database::get()->query('INSERT INTO request_field_data
                        (request_id, field_id, data) VALUES ' . $placeholders,
                        $field_data);
                }
            }
            if ($fields_text) {
                $fields_text .= '<hr>';
            }

            if (isset($_POST['send_mail'])) {
                $datetime = claro_format_locale_date($dateTimeFormatShort);
                $emailSubject = $langNewRequest . ': ' . $title;
                $emailContent = "
                    <!-- Header Section -->
                      <div id='mail-header'>
                          <br>
                          <div>
                              <div id='header-title'>" . q($langNewRequest) . ": <a href='{$urlServer}modules/request/?course=$course_code&amp;id=$rid'>" . q($title) . "</a>.</div>
                              <ul id='forum-category'> <li><span><b>$langSender:</b></span> <span class='left-space'>" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "</span></li>
                                  <li><span><b>$langdate:</b></span> <span class='left-space'>$datetime</span></li>
                              </ul>
                          </div>
                      </div>
                      <!-- Body Section -->
                      <div id='mail-body'>
                          <br>
                          <div><b>$langType:</b> <span class='left-space'>".q($type_name)."</span></div><br>
                          <div><b>$m[WorkAssignTo]:</b> <span class='left-space'>".q($assignedNames)."</span></div><br>
                          <div><b>$langWatchers:</b> <span class='left-space'>".q($watcherNames)."</span></div><br>
                          <div><b>$langNewBBBSessionStatus:</b> <span class='left-space'>".q($stateName)."</span></div><br>
                          <hr>
                          <div><b>$langDescription</b></div>
                          <div id='mail-body-inner'>
                              $requestDescription
                          </div>
                          $fields_text
                      </div>
                      <!-- Footer Section -->
                      <div id='mail-footer'>
                          <br>
                          <div>
                              <small>" . sprintf($langLinkUnsubscribe, q($currentCourseName)) ." <a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a></small>
                          </div>
                      </div>";
                $emailBody = html2text($emailContent);
                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'],
                    '', $recipients, $emailSubject, $emailBody, $emailContent);
            }

            Session::Messages(trans('langRequestCreated'), 'alert-success');
            redirect_to_home_page("modules/request/?course=$course_code&id=$rid");
        }
    } else {
        Session::Messages(trans('langFieldsRequ'), 'alert-warning');
    }
}

$backUrl = $urlAppend . 'modules/request/?course=' . $course_code;
$navigation[] = array('url' => $backUrl, 'name' => $langRequests);

$data['action_bar'] = action_bar(
        [
            [ 'title' => $langBack,
              'url' => $backUrl,
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);

$data['descriptionEditor'] = rich_text_editor('requestDescription', 4, 20, $requestDescription);
$data['creatorName'] = $_SESSION['givenname'] . ' ' . $_SESSION['surname'];
$data['backUrl'] = $backUrl;
$data['targetUrl'] = $urlAppend . 'modules/request/new.php?course=' . $course_code;;
$data['course_users'] = Database::get()->queryArray("SELECT user_id,
        CONCAT(surname, ' ', givenname) name, email
    FROM course_user JOIN user ON user_id = user.id
    WHERE course_id = ?d AND user_id <> ?d
    ORDER BY surname, givenname", $course_id, $uid);
$data['request_types'] = Database::get()->queryArray('SELECT * FROM request_type ORDER BY id');
if ($data['request_types']) {
    foreach ($data['request_types'] as $type) {
        $fields = Database::get()->queryArray(
            'SELECT * FROM request_field WHERE type_id = ?d ORDER BY sortorder',
            $type->id);
        foreach ($fields as $field) {
            if ($field->values) {
                $field->values = array_map('getSerializedMessage', unserialize($field->values));
            }
            $field->name = getSerializedMessage($field->name);
            $field->data = null;
            $data['request_fields'][$type->id][$field->id] = $field;
        }
    }
}

load_js('select2');
load_js('bootstrap-datepicker');
load_js('bootstrap-combobox');

view('modules.request.new', $data);

<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
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

require_once '../../include/baseTheme.php';
require_once 'modules/lti/lib.php';

// request parameters
$scope = (isset($_REQUEST['scope'])) ? $_REQUEST['scope'] : '';
$responsetype = (isset($_REQUEST['response_type'])) ? $_REQUEST['response_type'] : '';
$clientid = (isset($_REQUEST['client_id'])) ? $_REQUEST['client_id'] : '';
$redirecturi = (isset($_REQUEST['redirect_uri'])) ? $_REQUEST['redirect_uri'] : '';
$loginhint = (isset($_REQUEST['login_hint'])) ? $_REQUEST['login_hint'] : '';
$ltimessagehintenc = (isset($_REQUEST['lti_message_hint'])) ? $_REQUEST['lti_message_hint'] : '';
$state = (isset($_REQUEST['state'])) ? $_REQUEST['state'] : '';
$responsemode = (isset($_REQUEST['response_mode'])) ? $_REQUEST['response_mode'] : '';
$nonce = (isset($_REQUEST['nonce'])) ? $_REQUEST['nonce'] : '';
$prompt = (isset($_REQUEST['prompt'])) ? $_REQUEST['prompt'] : '';

$ok = !empty($scope) && !empty($responsetype) && !empty($clientid) &&
    !empty($redirecturi) && !empty($loginhint) &&
    !empty($nonce);

if (!$ok) {
    $error = 'invalid_request';
}
$ltimessagehint = json_decode($ltimessagehintenc);
$ok = $ok && isset($ltimessagehint->launchid);
if (!$ok) {
    $error = 'invalid_request';
    $desc = 'No launch id in LTI hint';
}
if ($ok && ($scope !== 'openid')) {
    $ok = false;
    $error = 'invalid_scope';
}
if ($ok && ($responsetype !== 'id_token')) {
    $ok = false;
    $error = 'unsupported_response_type';
}
if ($ok) {
    $launchid = $ltimessagehint->launchid;
    if (isset($_SESSION[$launchid])) {
        list($courseid, $ltiAppId, $messagetype, $resourceType, $resourceId, $titleb64, $textb64) = explode(',', $_SESSION[$launchid], 7);
        unset($_SESSION[$launchid]);
        $ltiApp = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $ltiAppId);
        $ok = ($clientid === $ltiApp->client_id);
    } else {
        $ok = false;
    }
    if (!$ok) {
        $error = 'unauthorized_client';
    }
}
if ($ok && (intval($loginhint) !== intval($uid))) {
    $ok = false;
    $error = 'access_denied';
}

// If we're unable to load up config; we cannot trust the redirect uri for POSTing to.
if (empty($ltiApp)) {
    throw new Exception('LTI Error: invalid request.');
} else {
    $uris = array_map("trim", explode("\n", $ltiApp->lti_provider_redirection_uri));
    if (!in_array($redirecturi, $uris)) {
        throw new Exception('LTI Error: invalid request.');
    }
}
if ($ok) {
    if (isset($responsemode)) {
        $ok = ($responsemode === 'form_post');
        if (!$ok) {
            $error = 'invalid_request';
            $desc = 'Invalid response_mode';
        }
    } else {
        $ok = false;
        $error = 'invalid_request';
        $desc = 'Missing response_mode';
    }
}
if ($ok && !empty($prompt) && ($prompt !== 'none')) {
    $ok = false;
    $error = 'invalid_request';
    $desc = 'Invalid prompt';
}

if ($ok) {
    // require course
    $course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d ", $courseid);
    if (empty($course) || empty($course->id) || empty($course->code)) {
        throw new Exception('LTI Error: course not found during auth request.');
    }
    $_SESSION['dbname'] = $course->code;
    $stat = Database::get()->querySingle("SELECT status, tutor, editor, course_reviewer FROM course_user WHERE user_id = ?d AND course_id = ?d", $uid, $courseid);
    if (!$is_admin && (empty($stat) || (empty($stat->status) && empty($stat->editor) && empty($stat->tutor)))) {
        throw new Exception('LTI Error: course_user not found during auth request.');
    }
    if ($is_admin && (empty($stat) || (empty($stat->status) && empty($stat->editor) && empty($stat->tutor)))) {
        $stat = new stdClass();
        $stat->status = USER_TEACHER;
    }

    if ($resourceId) {
        list($endpoint, $params) = ltiGetLaunchData($ltiApp, $course, $stat, $resourceType, $resourceId, $messagetype, $nonce);
    } else {
        if (!$is_admin && $stat->status != USER_TEACHER && !$stat->editor && !$stat->tutor) {
            throw new Exception('LTI Error: action requires course editor access.');
        }
        // Set the return URL.
        $returnurlparams = [
            'course' => $courseid,
            'id' => $ltiAppId,
            'sesskey' => randomkeys(10)
        ];
        $returnurl = $urlServer . "modules/lti/contentitem_return.php?" . getQueryString($returnurlparams);
        // Prepare the request.
        $title = base64_decode($titleb64);
        $text = base64_decode($textb64);
        $request = ltiBuildContentItemSelectionRequest($ltiApp, $course, $stat, $returnurl, $title, $text, $nonce);
        $endpoint = $request->url;
        $params = $request->params;
    }
} else {
    $params['error'] = $error;
    if (!empty($desc)) {
        $params['error_description'] = $desc;
    }
}
if (isset($state)) {
    $params['state'] = $state;
}
if (isset($_SESSION['lti_message_hint'])) {
    unset($_SESSION['lti_message_hint']);
}
$r = '<form action="' . $redirecturi . "\" name=\"ltiAuthForm\" id=\"ltiAuthForm\" " .
    "method=\"post\" enctype=\"application/x-www-form-urlencoded\">\n";
if (!empty($params)) {
    foreach ($params as $key => $value) {
        $key = htmlspecialchars($key, ENT_COMPAT);
        $value = htmlspecialchars($value, ENT_COMPAT);
        $r .= "  <input type=\"hidden\" name=\"{$key}\" value=\"{$value}\"/>\n";
    }
}
$r .= "</form>\n";
$r .= "<script type=\"text/javascript\">\n" .
    "//<![CDATA[\n" .
    "document.ltiAuthForm.submit();\n" .
    "//]]>\n" .
    "</script>\n";
echo $r;
exit();

function getQueryString(array $params): string {
    $arr = array();
    foreach ($params as $key => $val) {
        if (isset($val) && $val !== '') {
            $arr[] = rawurlencode($key)."=".rawurlencode($val);
        } else {
            $arr[] = rawurlencode($key);
        }
    }
    return implode('&', $arr);
}

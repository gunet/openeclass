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

require_once '../../include/baseTheme.php';
require_once 'modules/lti/lib.php';

// Handle the return from the Tool Provider after selecting a content item.

$id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
$courseid = (isset($_REQUEST['course'])) ? $_REQUEST['course'] : '';
$jwt = (isset($_REQUEST['JWT'])) ? $_REQUEST['JWT'] : '';

if (empty($id) || empty($courseid) || empty($jwt)) {
    throw new Exception('LTI Error: invalid contentitem_return request.');
}

$params = ltiConvertFromJwt($id, $jwt);
$consumerkey = $params['oauth_consumer_key'] ?? '';
$messagetype = $params['lti_message_type'] ?? '';
$version = $params['lti_version'] ?? '';
$items = $params['content_items'] ?? '';
$errormsg = $params['lti_errormsg'] ?? '';
$msg = $params['lti_msg'] ?? '';

$course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $courseid);
if (empty($course) || empty($course->code)) {
    throw new Exception('LTI Error: course not found during contentitem_return request.');
}
$_SESSION['dbname'] = $course->code;

$returndata = null;
if (empty($errormsg) && !empty($items)) {
    try {
        $returndata = ltiToolConfigurationFromContentItem($id, $messagetype, $version, $consumerkey, $items);
    } catch (Exception $e) {
        $errormsg = $e->getMessage();
    }
}

$retjs = json_encode($returndata);
$js = <<<EOT
<script type="text/javascript" src="${urlAppend}js/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
let returndata = $retjs;

$(document).ready(function() {
    $(window).ready(function() {
        if (window != top) {
            // Send return data to be processed by the parent window.
            parent.processContentItemReturnData(returndata);
        } else {
            window.processContentItemReturnData(returndata);
        }
    });
});
</script>
EOT;

$html = <<< EOT
<html>
<head>
<title></title>
$js
</head>
<body>
<div>LTI Content Selection Success. You may now close this window and save the assignment or refresh the assignment to apply these changes.</div>
</body>
</html>
EOT;
echo $html;

// Render notification messages.
if ($errormsg) {
    // Content item selection has encountered an error.
    Session::flash('message', $errormsg);
    Session::flash('alert-class', 'alert-danger');
} else if (!empty($returndata)) {
    // Means success.
    if (!$msg) {
        $msg = "LTI: Successfully Fetched Tool Configuration from Content";
    }
    Session::flash('message', $msg);
    Session::flash('alert-class', 'alert-success');
}
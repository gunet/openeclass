<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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


$mail_ver_excluded = true;
require_once '../include/baseTheme.php';
$pageName = $contactpoint;

$postaddress = nl2br(q(get_config('postaddress')));
$Institution = q(get_config('institution'));
$phone = q(get_config('phone'));
$fax = q(get_config('fax'));
$phonemessage = empty($phone) ? '' : "<label>$langPhone:&nbsp;</label>$phone<br>";
$faxmessage = empty($fax) ? '' : "<label>$langFax</label>$fax<br>";
$emailhelpdesk = get_config('email_helpdesk');

$tool_content .= "<div class='alert alert-info col-sm-10 page-header'>
<label>$langPostMail&nbsp;</label>$Institution<br> $postaddress 

$phonemessage
$faxmessage
<label>$langEmail:&nbsp;</label>" . mailto($emailhelpdesk, str_replace('@', ' &lt;at> ', $emailhelpdesk)) . "
</div>";

if (isset($uid) and $uid) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}

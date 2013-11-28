<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
$nameTools = $contactpoint;

$postaddress = nl2br(q(get_config('postaddress')));
$Institution = q(get_config('institution'));
$phone = q(get_config('phone'));
$phonemessage = empty($phone) ? '' : "<tr><th>$langPhone</th><td>$phone</td></tr>";
$fax = q(get_config('fax'));
$faxmessage = empty($fax) ? '' : "<tr><th>$langFax</th><td>$fax</td></tr>";
$emailhelpdesk = get_config('email_helpdesk');

$tool_content .= "
<table class='tbl_1' width='100%'>
<tr><th width='155'>$langPostMail</th><td> $Institution<br> $postaddress </td>
</tr>
$phonemessage
$faxmessage
<tr><th><b>$langEmail:</b></th><td>" . mailto($emailhelpdesk, str_replace('@', ' &lt;at> ', $emailhelpdesk)) . "</td>
</table>";

if (isset($uid) and $uid) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}

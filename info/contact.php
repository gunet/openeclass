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
$phonemessage = empty($phone) ? "<label>$langPhone:</label> <span class='not_visible'> - $langProfileNotAvailable - </span><br>" : "<label>$langPhone:&nbsp;</label>$phone<br>";
$faxmessage = empty($fax) ? "<label>$langFax</label> <span class='not_visible'> - $langProfileNotAvailable - </span><br>" : "<label>$langFax&nbsp;</label>$fax<br>";
$emailhelpdesk = get_config('email_helpdesk');
$emailhelpdesk = empty($emailhelpdesk) ? "<label>$langEmail:</label> <span class='not_visible'> - $langProfileNotAvailable - </span><br>" : "<label>$langEmail: </label>&nbsp;<a href='mailto:$emailhelpdesk'>".str_replace('@', ' &lt;at> ', $emailhelpdesk)."</a>";       

$tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
$tool_content .= "<div class='row'>
                    <div class='col-xs-12'>
                        <div class='panel'>
                            <div class='panel-body'>
                                <label>$langPostMail&nbsp;</label>$Institution<br> $postaddress<br> $phonemessage $faxmessage $emailhelpdesk                                
                            </div>
                        </div>
                    </div>
                </div>";

if (isset($uid) and $uid) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}

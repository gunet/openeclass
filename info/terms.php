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

require_once '../include/baseTheme.php';

$toolName = $langUsageTerms;

$email = get_config('email_helpdesk');

foreach (array($language, 'en', 'el') as $l) {
    $terms_file = "lang/$l/terms.html";
    if (file_exists($terms_file)) {
        break;
    }
}
$tool_content = action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
$tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= str_replace(
    array('{%INSTITUTION%}', '{%EMAIL_HELPDESK%}'),
    array(q(get_config('institution')), "<a href='mailto:$email'>$email</a>"),
    file_get_contents($terms_file));
$tool_content .= "</div></div></div></div>";

if (isset($_SESSION['uid'])) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}

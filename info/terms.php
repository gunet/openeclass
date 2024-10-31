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


require_once '../include/baseTheme.php';

$toolName = $langUsageTerms;

$email = get_config('email_helpdesk');

foreach (array($language, 'en', 'el') as $l) {
    $terms_file = "lang/$l/terms.html";
    if (file_exists($terms_file)) {
        break;
    }
}

$data['terms'] = str_replace(
    array('{%INSTITUTION%}', '{%EMAIL_HELPDESK%}'),
    array(q(get_config('institution')), "<a href='mailto:$email'>$email</a>"),
    file_get_contents($terms_file));

view('info.terms', $data);

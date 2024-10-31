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

if (!get_config('activate_privacy_policy_text')) {
    redirect_to_home_page();
}

$toolName = $langPrivacyPolicy;

$policy = get_config('privacy_policy_text_' . $language);
if (!$policy) {
    $policyFile = "lang/$language/privacy.html";
    if (file_exists($policyFile)) {
        $policy = file_get_contents($policyFile);
    } else {
        $policy = get_config('privacy_policy_text_en');
        if (!$policy) {
            $policy = file_get_contents('lang/en/privacy.html');
        }
    }
}

$data['policy'] = $policy;

view('info.privacy_policy', $data);

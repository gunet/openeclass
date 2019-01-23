<?php

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
            $policyFile = "lang/en/privacy.html";
        }
    }
}

$tool_content = action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ), false);

$tool_content .= "<div class='row'>"
                    . "<div class='col-xs-12'>"
                    . "<div class='panel'>"
                    . "<div class='panel-body'>"
                    . $policy
                    . "</div>"
                    . "</div>"
                    . "</div>"
                    . "</div>";

if (isset($_SESSION['uid'])) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}


<?php

require_once '../include/baseTheme.php';

$toolName = $langPrivacyPolicy;

$tool_content = action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);

$tool_content .= "<div class='row'>"
                    . "<div class='col-xs-12'>"
                    . "<div class='panel'>"
                    . "<div class='panel-body'>";

if ($language == 'el') {
    $tool_content .= get_config('privacy_policy_text');
} else {
    $tool_content .= get_config('privacy_policy_text_en');
}

$tool_content .= "</div>"
            . "</div>"
            . "</div>"
            . "</div>";

if (isset($_SESSION['uid'])) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}


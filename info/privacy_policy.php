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
            $policy = file_get_contents('lang/en/privacy.html');
        }
    }
}
$data['menuTypeID'] = $uid? 1: 0;
$data['policy'] = $policy;
$data['action_bar'] = action_bar([
    [ 'title' => trans('langBack'),
      'url' => $urlAppend,
      'icon' => 'fa-reply',
      'level' => 'primary-label',
      'button-class' => 'btn-secondary' ]
  ], false);

view('info.privacy_policy', $data);

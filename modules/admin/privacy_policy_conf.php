<?php

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langPrivacyPolicy;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_POST['updatePrivacyPolicy'])) {
    set_config('privacy_policy_timestamp', date('Y-m-d H:i:s'));
    redirect_to_home_page("modules/admin/index.php");
}

if (isset($_POST['submit'])) {
    if (isset($_POST['activate_privacy_policy_text'])) {
        set_config('activate_privacy_policy_text', 1);
    } else {
        set_config('activate_privacy_policy_text', 0);
    }
    if (isset($_POST['activate_privacy_policy_consent'])) {
        set_config('activate_privacy_policy_text', 1);
        set_config('activate_privacy_policy_consent', 1);
    } else {
        set_config('activate_privacy_policy_consent', 0);
    }

    $activate_privacy_policy_consent = get_config('activate_privacy_policy_consent');
    $privacyPolicyChanged = false;

    foreach ($session->active_ui_languages as $langCode) {
        $langVar = 'privacy_policy_text_' . $langCode;
        if (isset($_POST[$langVar])) {
            $oldText = get_config($langVar);
            $newText = purify(trim($_POST[$langVar]));
            if ($oldText != $newText) {
                set_config($langVar, purify(trim($_POST[$langVar])));
                $privacyPolicyChanged = true;
            }
        }
    }

    if ($privacyPolicyChanged and $activate_privacy_policy_consent) {
        Session::flash('message', trans('langPrivacyPolicyConsentAskAgain') .
            "<form method='post' action='$_SERVER[SCRIPT_NAME]'>
                <button type='submit' class='btn btn-default' name='updatePrivacyPolicy' value='true'>" .
            trans('langPrivacyPolicyConsentRedisplay') . "
                </button>
            </form>");
        Session::flash('alert-class', 'alert-info');
        redirect_to_home_page("modules/admin/index.php");
    }

    Session::flash('message', $langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/index.php");
}

$data['cbox_activate_privacy_policy_text'] = get_config('activate_privacy_policy_text') ? 'checked' : '';
$data['cbox_activate_privacy_policy_consent'] = get_config('activate_privacy_policy_consent') ? 'checked' : '';

$data['sel'] = [];
$data['selectable_langs'] = [];
$langdirs = active_subdirs($webDir . '/lang', 'messages.inc.php');
$active_ui_languages = explode(' ', get_config('active_ui_languages'));

foreach ($language_codes as $langcode => $langname) {
    if (in_array($langcode, $langdirs)) {
        $loclangname = $langNameOfLang[$langname];
        if (in_array($langcode, $active_ui_languages)) {
            $data['selectable_langs'][$langcode] = $loclangname;
        }
        $checked = in_array($langcode, $active_ui_languages) ? ' checked' : '';
        $data['sel'][] = "
                        <div class='checkbox'>
                            <label class='label-container'>
                                <input type='checkbox' name='av_lang[]' value='$langcode' $checked>
                                <span class='checkmark'></span>
                                $loclangname
                            </label>
                        </div>";

    }
}

foreach ($session->active_ui_languages as $langCode) {
    $policy = get_config('privacy_policy_text_' . $langCode);
    if (!$policy) {
        $policyFile = "lang/$langCode/privacy.html";
        if (file_exists($policyFile)) {
            $policy = file_get_contents($policyFile);
        } else {
            $policy = get_config('privacy_policy_text_en');
            if (!$policy) {
                $policy = file_get_contents('lang/en/privacy.html');
            }
        }
    }
    $data['policyText'][$langCode] = $policy;
}

rich_text_editor(null, null, null, null);
view('admin.other.privacy_policy_conf', $data);

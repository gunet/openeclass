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

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langAdmin;
$pageName = $langAccessibility;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_POST['submit'])) {
    if (isset($_POST['activate_accessibility_text'])) {
        set_config('activate_accessibility_text', 1);
    } else {
        set_config('activate_accessibility_text', 0);
    }

    foreach ($session->active_ui_languages as $langCode) {
        $langVar = 'accessibility_text_' . $langCode;
        if (isset($_POST[$langVar])) {
            $oldText = get_config($langVar);
            $newText = purify(trim($_POST[$langVar]));
            if ($oldText != $newText) {
                set_config($langVar, purify(trim($_POST[$langVar])));
            }
        }
    }

    Session::flash('message', $langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/index.php");
}

$data['cbox_activate_accessibility_text'] = get_config('activate_accessibility_text') ? 'checked' : '';

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
                            <label class='label-container' aria-label='$langSelect'>
                                <input type='checkbox' name='av_lang[]' value='$langcode' $checked>
                                <span class='checkmark'></span>
                                $loclangname
                            </label>
                        </div>";

    }
}

foreach ($session->active_ui_languages as $langCode) {
    $policy = get_config('accessibility_text_' . $langCode);
    if (!$policy) {
        $policyFile = "lang/$langCode/accessibility.html";
        if (file_exists($policyFile)) {
            $policy = file_get_contents($policyFile);
        } else {
            $policy = get_config('accessibility_text_en');
            if (!$policy) {
                if (file_exists('lang/en/accessibility.html')) {
                    $policy = file_get_contents('lang/en/accessibility.html');
                } else {
                    $policy = '';
                }
            }
        }
    }
    $data['policyText'][$langCode] = $policy;
}

rich_text_editor(null, null, null, null);
view('admin.other.accessibility_conf', $data);

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

require_once '../../include/baseTheme.php';

$require_admin = true;
$require_help = true;
$helpTopic = 'external_tools';
$helpSubTopic = 'open_badges';

$toolName = $langBackpackExternalProvider;
$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];
$navigation[] = ['url' => 'extapp.php', 'name' => $langExtAppConfig];

load_js('tools.js');
load_js('validation.js');

$head_content .= <<<HTML
<script type='text/javascript'>
    $(document).ready(function () {                
        $('#select-courses').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-courses').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-courses').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            $('#select-courses').val([]).trigger('change');
        });
    });
</script>
HTML;

function flash_and_redirect($message, $class, $redirect) {
    Session::flash('message', $message);
    Session::flash('alert-class', $class);
    redirect_to_home_page($redirect);
    exit;
}

function draw_backpack_provider_form($provider = null, $submit_name = 'do_add_backpack_provider', $submit_label = null) {
    global $tool_content, $langAdd, $langTitle, $langBackpackProvider, $langBackpackProviderUrl, $langForm;
    $name = $provider && isset($provider->name) ? q($provider->name) : '';
    $api_url = $provider && isset($provider->api_url) ? q($provider->api_url) : '';
    $version = $provider && isset($provider->ob_version) ? $provider->ob_version : '';
    $id_field = $provider && isset($provider->id) ? "<input type='hidden' name='provider_id' value='".q($provider->id)."'>" : '';
    $submit_label = $submit_label ?: $langAdd;

    $tool_content .= "
        <div class='d-lg-flex gap-4 mt-4'>
            <div class='flex-grow-1'>
                <div class='form-wrapper form-edit border-0 px-0'>
                    <form class='form-horizontal' role='form' name='backpackProviderForm' action='$_SERVER[SCRIPT_NAME]' method='post'>
                        $id_field
                        <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='form-group'>
                            <label for='provider_name' class='col-sm-12 control-label-notes'>$langBackpackProvider <span class='asterisk Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='provider_name' id='provider_name' value='$name' required>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='api_url' class='col-sm-12 control-label-notes'>$langBackpackProviderUrl <span class='asterisk Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='url' name='api_url' id='api_url' value='$api_url' required>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='version' class='col-sm-12 control-label-notes'>OpenBadge version <span class='asterisk Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'>
                                <select class='form-select' name='version' id='version' required>
                                    <option value='OpenBadge v2.0'".($version == '2.1' ? ' selected' : '').">OpenBadge v2.0</option>
                                    <option value='OpenBadge v2.1'".($version == '2.0' ? ' selected' : '').">OpenBadge v2.1</option>
                                    <option value='OpenBadge v3'".($version == '3.0' ? ' selected' : '').">OpenBadge v3</option>
                                </select>
                            </div>
                        </div>
                        <div class='form-group mt-5'>
                            <div class='col-12 d-flex justify-content-end align-items-center'>
                                <input class='btn submitAdminBtn' type='submit' name='$submit_name' value='$submit_label'>
                            </div>
                        </div>
                        </fieldset>
                        ". generate_csrf_token_form_field() ."
                    </form>
                </div>
            </div>
        </div>";

    $tool_content .='<script type="text/javascript">\n//<![CDATA[\n    let chkValidator  = new Validator("backpackProviderForm");\n    chkValidator.addValidation("provider_name", "req", "Please enter the provider name.");\n    chkValidator.addValidation("api_url", "req", "Please enter the API URL.");\n//]]>\n</script>';
}

function handle_add_backpack_provider() {
    global $langNewBackpackProvider, $navigation;
    $pageName = $langNewBackpackProvider;
    $navigation[] = ['url' => 'openbadgeconf.php', 'name' => $langBackpackExternalProvider];
    draw_backpack_provider_form();
}

function handle_delete_backpack_provider($indirectId) {
    $directId = getDirectReference($indirectId);
    Database::get()->query("DELETE FROM backpack_provider WHERE id=?d", $directId);
    flash_and_redirect($GLOBALS['langBBBDeleteSuccessful'], 'alert-success', "modules/admin/openbadgeconf.php");
}

function handle_create() {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    $name = isset($_POST['provider_name']) ? trim($_POST['provider_name']) : '';
    $api_url = isset($_POST['api_url']) ? trim($_POST['api_url']) : '';
    $version = isset($_POST['version']) ? trim($_POST['version']) : '';

    $errors = [];
    if ($name === '') {
        $errors[] = 'Provider name is required.';
    }
    if ($api_url === '' || !filter_var($api_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'A valid API URL is required.';
    }
    if ($version === '') {
        $errors[] = 'Version is required.';
    }

    if (!empty($errors)) {
        flash_and_redirect(implode('<br>', $errors), 'alert-danger', $_SERVER['SCRIPT_NAME'] . '?add_backpack_provider');
    }

    // Insert into backpack_provider
    $result = Database::get()->query(
        "INSERT INTO backpack_provider (name, api_url, active, created_at, updated_at) VALUES (?s, ?s, 1, NOW(), NOW())",
        $name, $api_url
    );

    if ($result) {
        flash_and_redirect('Backpack provider added successfully.', 'alert-success', $_SERVER['SCRIPT_NAME']);
    } else {
        $db_error = Database::get()->errorInfo();
        error_log('Failed to add backpack provider: ' . print_r($db_error, true));
        $error_message = isset($db_error[2]) ? $db_error[2] : 'Unknown error';
        flash_and_redirect('Failed to add backpack provider. DB Error: ' . htmlspecialchars($error_message), 'alert-danger', $_SERVER['SCRIPT_NAME'] . '?add_backpack_provider');
    }
}

function handle_edit_backpack_provider($id) {
    global $langEdit, $navigation, $tool_content;
    $pageName = $langEdit;
    $navigation[] = ['url' => 'openbadgeconf.php', 'name' => $GLOBALS['langOpenBadgeConf']];
    $tool_content .= action_bar([
        ['title' => $GLOBALS['langBack'], 'url' => "openbadgeconf.php", 'icon' => 'fa-reply', 'level' => 'primary']
    ]);

    $directId = getDirectReference($id);
    $provider = Database::get()->querySingle("SELECT * FROM backpack_provider WHERE id = ?d", $directId);
    if (!$provider) {
        flash_and_redirect('Provider not found.', 'alert-danger', $_SERVER['SCRIPT_NAME']);
    }
    draw_backpack_provider_form($provider, 'do_update_backpack_provider', $GLOBALS['langEdit']);
}

function handle_list_templates() {
    global $tool_content, $langNewBackpackProvider, $langSettingSelect, $langYes, $langNo, $langEditChange, $langDelete, $langConfirmDelete, $langNoAvailableBackpackProvider;
    $tool_content .= action_bar([
        ['title' => $langNewBackpackProvider, 'url' => "openbadgeconf.php?add_backpack_provider", 'icon' => 'fa-plus-circle', 'level' => 'primary-label', 'button-class' => 'btn-success']
    ]);
    $backpack_external_providers = Database::get()->queryArray("SELECT id, name, active, api_url, updated_at FROM backpack_provider ORDER BY name ASC");
    if (count($backpack_external_providers) > 0) {
        $tool_content .= "<div class='table-responsive'><table class='table-default'>
            <thead>
            <tr class='list-header'>
                <th>Όνομα παρόχου</th>
                <th>URL παρόχου</th>
                <th>Ενεργοποιημένος</th>
                <th class='text-end' aria-label='$langSettingSelect'>".icon('fa-gears')."</th></tr>
            </thead>";
        foreach ($backpack_external_providers as $backpack_external_provider) {
            $enabled_lti_template = ($backpack_external_provider->active == 1) ? $langYes : $langNo;
            $tool_content .= "<tr>
                <td class='p-4'>{$backpack_external_provider->name}</td>
                <td class='p-4'>{$backpack_external_provider->api_url}</td>
                <td class='p-4'>$enabled_lti_template</td>
                <td class='option-btn-cell text-end p-20'>" .
                action_button([
                    ['title' => $langEditChange, 'url' => "{$_SERVER['SCRIPT_NAME']}?edit_backpack_provider=" . getIndirectReference($backpack_external_provider->id), 'icon' => 'fa-edit'],
                    ['title' => $langDelete, 'url' => "{$_SERVER['SCRIPT_NAME']}?delete_backpack_provider=" . getIndirectReference($backpack_external_provider->id), 'icon' => 'fa-xmark', 'class' => 'delete', 'confirm' => $langConfirmDelete]
                ]) . "</td></tr>";
        }
        $tool_content .= "</table></div>";
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAvailableBackpackProvider</span></div></div>";
    }
}

function handle_update_backpack_provider() {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    $id = isset($_POST['provider_id']) ? trim($_POST['provider_id']) : '';
    $name = isset($_POST['provider_name']) ? trim($_POST['provider_name']) : '';
    $api_url = isset($_POST['api_url']) ? trim($_POST['api_url']) : '';
    $version = isset($_POST['version']) ? trim($_POST['version']) : '';

    $errors = [];
    if ($id === '' || !is_numeric($id)) {
        $errors[] = 'Invalid provider ID.';
    }
    if ($name === '') {
        $errors[] = 'Provider name is required.';
    }
    if ($api_url === '' || !filter_var($api_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'A valid API URL is required.';
    }
    if ($version === '') {
        $errors[] = 'Version is required.';
    }

    if (!empty($errors)) {
        flash_and_redirect(implode('<br>', $errors), 'alert-danger', $_SERVER['SCRIPT_NAME'] . '?edit_backpack_provider=' . urlencode($id));
    }

    $result = Database::get()->query(
        "UPDATE backpack_provider SET name = ?s, api_url = ?s, updated_at = NOW() WHERE id = ?d",
        $name, $api_url, $id
    );

    if ($result) {
        flash_and_redirect('Backpack provider updated successfully.', 'alert-success', $_SERVER['SCRIPT_NAME']);
    } else {
        flash_and_redirect('Failed to update backpack provider, please try again.', 'alert-danger', $_SERVER['SCRIPT_NAME'] . '?edit_backpack_provider=' . urlencode($id));
    }
}

if (isset($_GET['add_backpack_provider'])) {
    handle_add_backpack_provider();
} elseif (isset($_GET['delete_backpack_provider'])) {
    handle_delete_backpack_provider($_GET['delete_backpack_provider']);
} elseif (isset($_POST['do_add_backpack_provider'])) {
    handle_create();
} elseif (isset($_GET['edit_backpack_provider'])) {
    handle_edit_backpack_provider($_GET['edit_backpack_provider']);
} elseif (isset($_POST['do_update_backpack_provider'])) {
    handle_update_backpack_provider();
} else {
    handle_list_templates();
}

draw($tool_content, 3, null, $head_content);

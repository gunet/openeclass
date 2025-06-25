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
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/aiapp.php';
require_once 'include/lib/ai/AIProviderFactory.php';
require_once 'include/lib/ai/services/AIService.php';

$toolName = $langAI;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
$navigation[] = array('url' => 'aimoduleconf.php', 'name' => $langAI);

load_js('select2');
const AI_KEY_DURATION_TIME = 365*24*60*60; // one year (in seconds)

if (isset($_GET['edit_provider'])) {
    $data['existingConfig'] = $existingConfig = Database::get()->querySingle("SELECT * FROM ai_providers WHERE id = ?d", $_GET['edit_provider']);
    $currentModelName = '';
    if ($existingConfig && isset($existingConfig->model_name)) {
        $currentModelName = htmlspecialchars($existingConfig->model_name, ENT_QUOTES, 'UTF-8');
    }
    $providerDisplayNames = AIProviderFactory::getProviderDisplayNames();

    $data['dropdownOptions'] = array_map(function ($key, $value) {
        return ['value' => $key, 'label' => $value];
    }, array_keys($providerDisplayNames), $providerDisplayNames);
    $data['dropdownOptions'] = array_merge(
        $data['dropdownOptions'],
        [['value' => 'other', 'label' => 'Other']]
    );

} else if (isset($_GET['delete_provider'])) {
    Database::get()->query("DELETE FROM ai_providers WHERE id = ?d", $_GET['delete_provider']);
    Session::flash('message', $langAITokenDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/aimoduleconf.php');
} else if (isset($_GET['delete_service'])) {
    Database::get()->query("DELETE FROM ai_modules WHERE id = ?d", $_GET['delete_service']);
    Session::flash('message', $langAIModuleDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/aimoduleconf.php');
} else if (isset($_POST['submit_provider'])) {
    $provider_type = $_POST['provider'] ?? '';
    $api_key = trim($_POST['api_key']) ?? '';
    $model = $_POST['model'] ?? '';
    $endpoint_url = !empty($_POST['endpoint_url']) ? $_POST['endpoint_url'] : null;
    $model_name = $_POST['model_name'] ?? '';
    $api_type = $_POST['api_type'] ?? 'openai_chat';
    $ai_enabled = isset($_POST['ai_enabled']) ? 1 : 0;
    $expirationDate = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now") + AI_KEY_DURATION_TIME))->format("Y-m-d H:i:s");

    if ($provider_type && $api_key) {
        try {
            // For "another" provider type, use custom values
            if ($provider_type === 'other') {
                $provider_type = 'custom';
                $model = $model_name;
            }
            // Check if the provider already exists
            $existing = Database::get()->querySingle("SELECT id FROM ai_providers WHERE provider_type = ?s", $provider_type);

            if ($existing) {
                // Update existing provider
                Database::get()->query("UPDATE ai_providers SET 
                                        api_key = ?s, 
                                        model_name = ?s, 
                                        endpoint_url = ?s, 
                                        enabled = ?d 
                                    WHERE provider_type = ?s",
                                    $api_key, $model, $endpoint_url, $ai_enabled, $provider_type);
            } else {
                // Insert new provider
                Database::get()->query("INSERT INTO ai_providers (name, provider_type, api_key, model_name, endpoint_url, enabled, created, updated, expired) 
                                            VALUES (?s, ?s, ?s, ?s, ?s, ?s, " . DBHelper::timeAfter() . "," . DBHelper::timeAfter() . ", ?t)",
                                        ucfirst($provider_type) . ' Provider',
                                        $provider_type,
                                        $api_key,
                                        $model,
                                        $endpoint_url,
                                        $ai_enabled,
                                        $expirationDate);
            }

            Session::Messages($langAIConfigSaved, 'alert-success');
            redirect_to_home_page('modules/admin/aimoduleconf.php');
        } catch (Exception $e) {
            Session::Messages($langGeneralError . ': ' . $e->getMessage(), 'alert-danger');
        }
    } else {
        Session::Messages($langFieldsMissing, 'alert-warning');
    }
} else if (isset($_POST['submit_service'])) {
    print_a($_POST);
    Database::get()->query("INSERT INTO ai_modules (ai_module_id, ai_provider_id, all_courses) 
                                VALUES (?d, ?d, 1)",
                            $_POST['module'], $_POST['provider_model']);
    Session::Messages($langAIConfigSaved, 'alert-success');
    redirect_to_home_page('modules/admin/aimoduleconf.php');
} else if (isset($_GET['add_provider'])) {
// Get provider display names
    $providerDisplayNames = AIProviderFactory::getProviderDisplayNames();

    $data['dropdownOptions'] = array_map(function ($key, $value) {
        return ['value' => $key, 'label' => $value];
    }, array_keys($providerDisplayNames), $providerDisplayNames);
    $data['dropdownOptions'] = array_merge(
        $data['dropdownOptions'],
        [['value' => 'other', 'label' => 'Other']]
    );
    $currentModelName = '';
} else if (isset($_GET['add_service'])) {

    $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course
                                                    WHERE visible != " . COURSE_INACTIVE . "
                                                 ORDER BY title");
    $selections = array();
    $courses_content = "<option value='0' selected><h2>$langToAllCourses</h2></option>";
    foreach ($courses_list as $c) {
        $selected = in_array($c->id, $selections) ? "selected" : "";
        $courses_content .= "<option value='$c->id' $selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
    }
    $data['courses_content'] = $courses_content;

    $provider_model_data = [];
    $data['ai_services'] = AIService::getAIServices();
    $providers_data = Database::get()->queryArray("SELECT id, name, model_name FROM ai_providers WHERE enabled = 1");
    foreach ($providers_data as $provider_data) {
        $provider_model_data[$provider_data->id] = $provider_data->name . ' (' . $provider_data->model_name . ')';
    }
    $data['provider_model_data'] = $provider_model_data;
} else { // list
    $providerDisplayNames = AIProviderFactory::getProviderDisplayNames();
    $data['q'] = $q = Database::get()->queryArray("SELECT * FROM ai_providers");
    // Load existing configuration

    $data['dropdownOptions'] = array_map(function ($key, $value) {
        return ['value' => $key, 'label' => $value];
    }, array_keys($providerDisplayNames), $providerDisplayNames);
    $data['dropdownOptions'] = array_merge(
        $data['dropdownOptions'],
        [['value' => 'other', 'label' => 'Other']]
    );

// Get the current model name for JavaScript (escaped for security)
    $currentModelName = '';
    if ($q && isset($q->model_name)) {
        $currentModelName = htmlspecialchars($q->model_name, ENT_QUOTES, 'UTF-8');
    }

    $ai_services = AIService::getAIServices();

    $q = Database::get()->queryArray("SELECT ai_modules.id, ai_module_id, name, model_name, all_courses 
                FROM ai_modules 
                    JOIN ai_providers 
                ON ai_modules.ai_provider_id = ai_providers.id");
    foreach ($q as $modules_data) {
        $ai_module_data[] = [
            $modules_data->id,
            $ai_services[$modules_data->ai_module_id],
            $modules_data->name,
            $modules_data->model_name,
            $modules_data->all_courses
        ];
    }

    $data['ai_module_data'] = $ai_module_data;
}
view('admin.other.extapps.aimoduleconf', $data);

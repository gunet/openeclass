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

$nameTools = $langAI;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
$pageName = $langAI;
const AI_KEY_DURATION_TIME = 365*24*60*60; // one year (in seconds)

if (isset($_GET['edit'])) {
    $data['q'] = $q = Database::get()->queryArray("SELECT * FROM ai_providers WHERE id = ?d", $_GET['edit']);
} else if (isset($_GET['delete'])) {
    Database::get()->query("DELETE FROM ai_providers WHERE id = ?d", $_GET['delete']);
    Session::flash('message', $langAITokenDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/aimoduleconf.php');
} else if (isset($_POST['submit'])) {
    print_a($_POST);
    //die;

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
                Database::get()->query("INSERT INTO ai_providers (name, provider_type, api_key, model_name, endpoint_url, enabled, created, updated, expired, options) 
                                            VALUES (?s, ?s, ?s, ?s, ?s, ?s, " . DBHelper::timeAfter() . "," . DBHelper::timeAfter() . ", ?t, '')",
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
} else if (isset($_GET['add'])) {
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
}


$head_content .= "
    <script type='text/javascript'>
    $(document).ready(function() {
        // Function to load models for a given provider
        function loadModels(provider) {
            if (provider) {
                $('#modelDropdown').empty().append('<option value=\"\">Loading...</option>');

                $.ajax({
                    url: 'aigetmodels.php',
                    method: 'POST',
                    data: { provider: provider },
                    success: function (response) {
                        console.log('Server Response:', response); // Debugging
                        
                        if (response && response.success && typeof response.models === 'object') {
                            $('#modelDropdown').empty().append('<option value=\"\">" . js_escape($langSelectLanguageModel) . "</option>');

                            Object.entries(response.models).forEach(function ([key, label]) {
                                var selected = '';
                                if (key === '" . $currentModelName . "') {
                                    selected = ' selected';
                                }
                                $('#modelDropdown').append('<option value=\"' + key + '\"' + selected + '>' + label + '</option>');
                            });
                        } else if (response && response.error) {
                            $('#modelDropdown').empty().append('<option value=\"\">' + response.error + '</option>');
                        } else {
                            $('#modelDropdown').empty().append('<option value=\"\">" . js_escape($langNoLangModels) . "</option>');
                        }
                    },
                    error: function () {
                        $('#modelDropdown').empty().append('<option value=\"\">Error loading models</option>');
                    }
                });
            } else {
                $('#modelDropdown').empty().append('<option value=\"\">Select a model</option>');
            }
        }

        // Load models for existing provider on page load
        var selectedProvider = $('#dropdownprovider').val();
        if (selectedProvider && selectedProvider !== 'other') {
            loadModels(selectedProvider);
        }

        // Handle provider dropdown change
        $('#dropdownprovider').on('change', function () {
            const provider = $(this).val();

            if (provider === 'other') {
                $('#modelDropdownContainer').addClass('d-none');
                $('#otherFields').removeClass('d-none');
            } else {
                $('#modelDropdownContainer').removeClass('d-none');
                $('#otherFields').addClass('d-none');
            }

            if (provider && provider !== 'other') {
                loadModels(provider);
            }
        });

        // Handle test connection button
        $('#testConnectionBtn').on('click', function() {
            const btn = $(this);
            const originalText = btn.text();
            const apiKey = $('#api_key').val();
            const provider = $('#dropdownprovider').val();
            const model = provider === 'other' ? $('#modelName').val() : $('#modelDropdown').val();
            const endpointUrl = $('#endpointUrl').val();

            if (!apiKey) {
                $('#connectionStatus').html('<div class=\"alert alert-warning\">Please enter an API key first</div>');
                return;
            }

            if (!provider) {
                $('#connectionStatus').html('<div class=\"alert alert-warning\">Please select a provider first</div>');
                return;
            }

            // Show loading state
            btn.prop('disabled', true).text('Testing...');
            $('#connectionStatus').html('<div class=\"alert alert-info\">Testing connection...</div>');

            $.ajax({
                url: 'aitestconnection.php',
                method: 'POST',
                data: {
                    provider_type: provider,
                    api_key: apiKey,
                    model_name: model,
                    endpoint_url: endpointUrl
                },
                success: function(response) {
                    if (response.success) {
                        $('#connectionStatus').html('<div class=\"alert alert-success\"><i class=\"fa fa-check\"></i> ' + response.message + '</div>');
                    } else {
                        $('#connectionStatus').html('<div class=\"alert alert-danger\"><i class=\"fa fa-times\"></i> ' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#connectionStatus').html('<div class=\"alert alert-danger\"><i class=\"fa fa-times\"></i> Connection test failed</div>');
                },
                complete: function() {
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });
    });
    </script>";

view('admin.other.extapps.aimoduleconf', $data);

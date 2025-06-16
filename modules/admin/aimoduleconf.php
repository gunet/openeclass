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


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/aiapp.php';
require_once 'include/lib/ai/AIProviderFactory.php';

$nameTools = $langAI;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
$pageName = $langAI;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $provider_type = $_POST['dropdown'] ?? '';
    $api_key = $_POST['api_key'] ?? '';
    $model = $_POST['model'] ?? '';
    $endpoint_url = !empty($_POST['endpoint_url']) ? $_POST['endpoint_url'] : null;
    $model_name = $_POST['model_name'] ?? '';
    $api_type = $_POST['api_type'] ?? 'openai_chat';
    $ai_enabled = isset($_POST['ai_enabled']) ? 'true' : 'false';
    
    if ($provider_type && $api_key) {
        try {
            // For "other" provider type, use custom values
            if ($provider_type === 'other') {
                $provider_type = 'custom';
                $model = $model_name;
            }
            
            // Check if provider already exists
            $existing = Database::get()->querySingle("SELECT id FROM ai_providers WHERE provider_type = ?", [$provider_type]);
            
            if ($existing) {
                // Update existing provider
                Database::get()->query("UPDATE ai_providers SET 
                    api_key = ?, 
                    model_name = ?, 
                    endpoint_url = ?, 
                    enabled = ? 
                    WHERE provider_type = ?", 
                    [$api_key, $model, $endpoint_url, $ai_enabled, $provider_type]);
            } else {
                // Insert new provider
                Database::get()->query("INSERT INTO ai_providers 
                    (name, provider_type, api_key, model_name, endpoint_url, enabled) 
                    VALUES (?, ?, ?, ?, ?, ?)", 
                    [ucfirst($provider_type) . ' Provider', $provider_type, $api_key, $model, $endpoint_url, $ai_enabled]);
            }
            
            Session::Messages($langAIConfigSaved, 'alert-success');
            redirect_to_home_page('modules/admin/aimoduleconf.php');
        } catch (Exception $e) {
            Session::Messages($langGeneralError . ': ' . $e->getMessage(), 'alert-danger');
        }
    } else {
        Session::Messages($langFieldsMissing, 'alert-warning');
    }
}

// Get provider display names
$providerDisplayNames = AIProviderFactory::getProviderDisplayNames();

// Load existing configuration
$existingConfig = null;
try {
    $existingConfig = Database::get()->querySingle("SELECT * FROM ai_providers WHERE enabled = 'true' LIMIT 1");
} catch (Exception $e) {
    // Ignore errors, will use defaults
}

$data['dropdownOptions'] = array_map(function ($key, $value) {
    return ['value' => $key, 'label' => $value];
}, array_keys($providerDisplayNames), $providerDisplayNames);
$data['dropdownOptions'] = array_merge(
    $data['dropdownOptions'],
    [['value' => 'other', 'label' => 'Other']]
);

// Pass existing config to view
$data['existingConfig'] = $existingConfig;

// Get current model name for JavaScript (escaped for security)
$currentModelName = '';
if ($existingConfig && isset($existingConfig->model_name)) {
    $currentModelName = htmlspecialchars($existingConfig->model_name, ENT_QUOTES, 'UTF-8');
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
                            $('#modelDropdown').empty().append('<option value=\"\">Select a model</option>');
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
                            $('#modelDropdown').empty().append('<option value=\"\">No models available</option>');
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

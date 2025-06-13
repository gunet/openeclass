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

// Get provider display names
$providerDisplayNames = AIProviderFactory::getProviderDisplayNames();

$data['dropdownOptions'] = array_map(function ($key, $value) {
    return ['value' => $key, 'label' => $value];
}, array_keys($providerDisplayNames), $providerDisplayNames);
$data['dropdownOptions'] = array_merge(
    $data['dropdownOptions'],
    [['value' => 'other', 'label' => 'Other']]
);


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
                        if (response && typeof response.models === 'object') {
                            $('#modelDropdown').empty().append('<option value=\"\">Select a model</option>');
                            Object.entries(response.models).forEach(function ([key, label]) {
                                $('#modelDropdown').append('<option value=\"' + key + '\">' + label + '</option>');
                            });
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

        // Load OpenAI models on page load
        loadModels('openai');

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
    });
    </script>";

view('admin.other.extapps.aimoduleconf', $data);

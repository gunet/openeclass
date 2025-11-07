@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @include('layouts.partials.show_alert')

                @if (isset($_GET['add_provider']) || isset($_GET['edit_provider']))
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                                <div class='form-group'>
                                    <label for="dropdown" class="form-label">{{ trans('langSelectAIProvider') }}</label>
                                    <select id='dropdownprovider' name='provider' class='form-select'>
                                        @foreach ($dropdownOptions as $option)
                                            <option value='{{ $option['value'] }}' @if (isset($existingConfig->provider_type) && $existingConfig->provider_type == $option['value']) selected @endif> {{ ($option['label']) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for="api_key" class="form-label">API Key</label>
                                    <input type="text" id="api_key" name="api_key" class="form-control" placeholder="Enter API key" value="@if (isset($existingConfig->api_key)) {{ $existingConfig->api_key }} @endif">
                                </div>

                                <div id='modelDropdownContainer' class='form-group mt-4'>
                                    <label for="modelDropdown" class="form-label">{{ trans('langLanguageModel') }}</label>
                                    <select id="modelDropdown" name="model" class="form-select">
                                        <option value="{{ $currentModelName }}">{{ $currentModelName }}</option>
                                    </select>
                                </div>

                                <div class='form-group mt-4'>
                                    <button type="button" id="testConnectionBtn" class="btn submitAdminBtn">
                                        <i class="fa fa-plug"></i> {{ trans('langTestConnection') }}
                                    </button>
                                </div>

                                <div id="connectionStatus" class="mt-2"></div>

                                <div id="otherFields" class="mt-4 d-none">
                                    <div class='form-group'>
                                        <label for="apiType" class="form-label">API Type</label>
                                        <select id="apiType" name="api_type" class="form-control">
                                            <option value="openai_chat">OpenAI Chat Completions Compatible</option>
                                        </select>
                                    </div>

                                    <div class='form-group mt-3'>
                                        <label for="endpointUrl" class="form-label">Endpoint URL</label>
                                        <input type="text" id="endpointUrl" name="endpoint_url" class="form-control" placeholder="Enter custom API URL">
                                    </div>

                                    <div class='form-group mt-3'>
                                        <label for="modelName" class="form-label">{{ trans('langLanguageModelName') }}</label>
                                        <input type="text" id="modelName" name="model_name" class="form-control" placeholder="{{ trans('langLanguageModelName') }}">
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <div class='col-sm-offset-2 col-sm-10'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label='{{ trans('langSettingSelect') }}'>
                                                <input type='checkbox' name='ai_enabled' @if (isset($existingConfig->enabled) and $existingConfig->enabled == '1') value='1' checked @else value='0' @endif>
                                                <span class='checkmark'></span>{{ trans('langCEnabled') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center'>

                                        {!! form_buttons(array(
                                            array(
                                                'class' => 'submitAdminBtn',
                                                'text' => trans('langSubmit'),
                                                'name' => 'submit_provider',
                                                'value'=> trans('langSubmit')
                                            )
                                        )) !!}

                                        {!! form_buttons(array(
                                            array(
                                                'class' => 'cancelAdminBtn ms-1',
                                                'href' => "aimoduleconf.php"
                                            )
                                        )) !!}

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>
                @elseif (isset($_GET['add_service']) || isset($_GET['edit_service']))
                    <div class='col-lg-9 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                                @if (isset($_GET['edit_service']))
                                    <input type="hidden" name="ai_service_id" value="{{ $_GET['edit_service'] }}">
                                @endif
                                <div class='form-group'>
                                    <label for="dropdown" class="form-label">{{ trans('langAIService') }}</label>
                                    <select name='module' class='form-select'>
                                        @foreach ($ai_services as $value => $label)
                                            <option value='{{ $value }}' @if (isset($ai_service) and $ai_service == $value) selected @endif> {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for="modelDropdown" class="form-label">{{ trans('langProvider') }} - {{ trans('langLanguageModel') }}</label>
                                    <select name="provider_model" class="form-select">
                                        @foreach ($provider_model_data as $id => $name)
                                            <option value='{{ $id }}' @if (isset($model_data) and $model_data == $name) selected @endif> {{ $name }}</option>"
                                         @endforeach
                                    </select>
                                </div>

                                <div class='form-group mt-4' id='courses-list'>
                                    <label for='select-courses' class='col-12 control-label-notes'>{{ trans('langUseOfService') }}&nbsp;&nbsp;
                                    <span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='right' title='{{ trans('langUseOfServiceInfo') }}'></span></label>
                                    <div class='col-12'>
                                        <select id='select-courses' class='form-select' name='ai_courses[]' multiple>
                                            {!! $courses_content !!}
                                        </select>
                                        <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
                                    </div>
                                </div>

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                        {!! form_buttons(array(
                                            array(
                                                'class' => 'submitAdminBtn',
                                                'text' => trans('langSubmit'),
                                                'name' => 'submit_service',
                                                'value'=> trans('langSubmit')
                                            )
                                        )) !!}
                                        {!! form_buttons(array(
                                            array(
                                                'class' => 'cancelAdminBtn ms-1',
                                                'href' => "aimoduleconf.php"
                                            )
                                        )) !!}

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class='col-lg-3 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>

                @else
                {{-- list of AI providers --}}
                    <h3>
                        {{ trans('langProviders') }}
                        <a href="{{ $_SERVER['SCRIPT_NAME'] }}?add_provider">
                            <span class="fa-solid fa-circle-plus fa-lg" title="{{ trans('langAddProvider') }}" data-bs-original-title="{{ trans('langAddProvider') }}" data-bs-toggle="tooltip" data-bs-placement="top"></span>
                        </a>
                    </h3>
                    <div class='table-responsive'>
                        <table class='table-default'>
                            <thead>
                                <tr class='list-header'>
                                    <th>{{ trans('langProvider') }}</th>
                                    <th>{{ trans('langLanguageModel') }}</th>
                                    <th>{{ trans('langCEnabled') }}</th>
                                    <th class='text-end' aria-label='{{ trans('langSettingSelect') }}'><i class='fa-solid fa-gears'></i></th>
                                </tr>
                            </thead>

                            @foreach ($q as $row)
                                <tr @if (!$row->enabled) class='not_visible' @endif>
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->model_name }}</td>
                                    <td>{{ ($row->enabled)? trans('langYes') : trans('langNo') }}</td>
                                    <td class='option-btn-cell text-end'>
                                        {!!
                                            action_button(array(
                                                array('title' => trans('langEditChange'),
                                                      'url' => "$_SERVER[SCRIPT_NAME]?edit_provider=$row->id",
                                                      'icon' => 'fa-edit'),
                                                array('title' => trans('langDelete'),
                                                      'url' => "$_SERVER[SCRIPT_NAME]?delete_provider=$row->id",
                                                      'icon' => 'fa-times',
                                                      'class' => 'delete',
                                                      'confirm' => trans('langConfirmDelete'))))
                                        !!}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>




                    {{-- list of AI modules --}}
                    <h3 class='mt-4'>
                        {{ trans('langModules') }}
                        <a href="{{ $_SERVER['SCRIPT_NAME'] }}?add_service">
                            <span class="fa-solid fa-circle-plus fa-lg" title="{{ trans('langAssignAIToModule') }}" data-bs-original-title="{{ trans('langAssignAIToModule') }}" data-bs-toggle="tooltip" data-bs-placement="top"></span>
                        </a>
                    </h3>
                    <div class='table-responsive'>
                        <table class='table-default'>
                            <thead>
                            <tr class='list-header'>
                                <th>{{ trans('langAIService') }}</th>
                                <th>{{ trans('langProvider') }}</th>
                                <th>{{ trans('langLanguageModel') }}</th>
                                <th>{{ trans('langGradebookActivityWeightLeft') }}</th>
                                <th class='text-end' aria-label='{{ trans('langSettingSelect') }}'><i class='fa-solid fa-gears'></i></th>
                            </tr>
                            </thead>

                            @foreach ($ai_module_data as $ai_module)
                                <tr @if ($ai_module['enabled'] == 0) class="not_visible" @endif>
                                    <td>{{ $ai_module['module_id'] }}</td>
                                    <td>{{ $ai_module['name'] }}</td>
                                    <td>{{ $ai_module['model_name'] }}</td>
                                    <td>
                                        @if ($ai_module['all_courses'] == 1)
                                            {{ trans('langToAllCourses') }}
                                        @else
                                            {{ $ai_course_title }}
                                        @endif
                                    </td>
                                    <td class='option-btn-cell text-end'>
                                        {!!
                                            action_button(array(
                                                array('title' => trans('langEdit'),
                                                      'url' => "$_SERVER[SCRIPT_NAME]?edit_service=$ai_module[id]",
                                                      'icon' => 'fa-edit'
                                                      ),
                                                array('title' => trans('langDelete'),
                                                      'url' => "$_SERVER[SCRIPT_NAME]?delete_service=$ai_module[id]",
                                                      'icon' => 'fa-times',
                                                      'class' => 'delete',
                                                      'confirm' => trans('langConfirmDelete'))
                                              ))
                                        !!}
                                    </td>
                                </tr>
                            @endforeach
                        </table>

                    </div>
                @endif

            </div>
        </div>
    </div>

    <script type='text/javascript'>
        function doSelectedCourses() {
            let selectedVals = $('#select-courses').val();
            let i = 0;
            let csvSelection = '';

            // comma separate selection and update field
            while (i < selectedVals.length) {
                if (csvSelection.length > 0) {
                    csvSelection = csvSelection.concat(',', selectedVals[i]);
                } else {
                    csvSelection = csvSelection.concat(selectedVals[i]);
                }
                i++;
            }
            $('#enabled-courses').val(csvSelection);

            // remove 'all courses' selection when selected other courses
            if (selectedVals.length > 1) {
                let index = selectedVals.indexOf('0');
                if (index > -1) {
                    selectedVals.splice(index, 1);
                    $('#select-courses').val(selectedVals).trigger('change');
                }
            }
            // restore all courses selections when deselected other courses
            if (selectedVals.length <= 0) {
                selectedVals.push(0);
                $('#select-courses').val(selectedVals).trigger('change');
            }
        }

        // Function to load models for a given provider
        function loadModels(provider) {
            if (provider) {
                $('#modelDropdown').empty().append('<option value=\"\">Loading...</option>');

                $.ajax({
                    url: 'aigetmodels.php',
                    method: 'POST',
                    data: { provider: provider },
                    success: function (response) {
                        //console.log('Server Response:', response);
                        if (response && response.success && typeof response.models === 'object') {
                            $('#modelDropdown').empty().append('<option value=\"\">{{ trans('langSelectLanguageModel') }}</option>');

                            Object.entries(response.models).forEach(function ([key, label]) {
                                var selected = '';
                                if (key === '{{ $currentModelName }}') {
                                    selected = ' selected';
                                }
                                $('#modelDropdown').append('<option value=\"' + key + '\"' + selected + '>' + label + '</option>');
                            });
                        } else if (response && response.error) {
                            $('#modelDropdown').empty().append('<option value=\"\">' + response.error + '</option>');
                        } else {
                            $('#modelDropdown').empty().append('<option value=\"\">{{ trans('langNoLangModels') }}</option>');
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

        $(document).ready(function () {

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

            $('#select-courses').select2();
            $('#selectAll').click(function(e) {
                e.preventDefault();
                let stringVal = [];
                $('#select-courses').find('option').each(function(){
                    if ($(this).val() != 0) {
                        stringVal.push($(this).val());
                    }
                });
                $('#select-courses').val(stringVal).trigger('change');
            });
            $('#removeAll').click(function(e) {
                e.preventDefault();
                let stringVal = [];
                stringVal.push(0);
                $('#select-courses').val(stringVal).trigger('change');
            });
            $('#select-courses').change(function(e) {
                doSelectedCourses();
            });
            doSelectedCourses();
        });

    </script>

@endsection

@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @if(isset($action_bar))
                    {!! $action_bar !!}
                @else
                    <div class='mt-4'></div>
                @endif

                @include('layouts.partials.show_alert')

                @if (isset($_GET['add']) || isset($_GET['edit']))
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                                <div class='form-group'>
                                    <label for="dropdown" class="form-label">{{ trans('langSelectAIProvider') }}</label>
                                    <select id='dropdownprovider' name='provider' class='form-control'>
                                        @foreach ($dropdownOptions as $option)
                                            <option value='{{ $option['value'] }}' @if (isset($existingConfig->provider_type) && $existingConfig->provider_type == $option['value']) selected @endif> {{ ($option['label']) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class='form-group mt-3'>
                                    <label for="api_key" class="form-label">API Key</label>
                                    <input type="text" id="api_key" name="api_key" class="form-control" placeholder="Enter API key" value="@if (isset($existingConfig->api_key)) {{ $existingConfig->api_key }} @endif">
                                </div>

                                <div id='modelDropdownContainer' class='form-group mt-3'>
                                    <label for="modelDropdown" class="form-label">{{ trans('langLanguageModel') }}</label>
                                    <select id="modelDropdown" name="model" class="form-control">
                                        <option value="">{{ trans('langSelectLanguageModel') }}</option>
                                    </select>
                                </div>

                                <div class='form-group mt-3'>
                                    <button type="button" id="testConnectionBtn" class="btn btn-outline-primary">
                                        <i class="fa fa-plug"></i> {{ trans('langTestConnection') }}
                                    </button>
                                </div>

                                <div id="connectionStatus" class="mt-2"></div>

                                <div id="otherFields" class="mt-3 d-none">
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
                                                'text' => trans('langModify'),
                                                'name' => 'submit',
                                                'value'=> trans('langModify')
                                            )
                                        )) !!}


                                        {!! form_buttons(array(
                                            array(
                                                'class' => 'cancelAdminBtn ms-1',
                                                'href' => "extapp.php"
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

                @else
                    {!! action_bar(array(
                        array('title' => trans('langAdd'),
                            'url' => "$_SERVER[SCRIPT_NAME]?add",
                            'icon' => 'fa-plus',
                            'level' => 'primary-label',
                            'button-class' => 'btn-success')
                        ));
                    !!}

                {{-- list of AI providers --}}
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
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->model_name }}</td>
                                    <td>{{ ($row->enabled)? trans('langYes') : trans('langNo') }}</td>
                                    <td class='option-btn-cell text-end'>
                                        {!!
                                            action_button(array(
                                                array('title' => trans('langEditChange'),
                                                      'url' => "$_SERVER[SCRIPT_NAME]?edit=$row->id",
                                                      'icon' => 'fa-edit'),
                                                array('title' => trans('langDelete'),
                                                      'url' => "$_SERVER[SCRIPT_NAME]?delete=$row->id",
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
                    <div class='table-responsive'>
                        <table class='table-default'>
                            <thead>
                            <tr class='list-header'>
                                <th>{{ trans('langAIService') }}</th>
                                <th>{{ trans('langProvider') }}</th>
                                <th>{{ trans('langLanguageModel') }}</th>
                                <th class='text-end' aria-label='{{ trans('langSettingSelect') }}'><i class='fa-solid fa-gears'></i></th>
                            </tr>
                            </thead>

                            @foreach ($ai_service_data as $ai_service)
                                <tr>
                                    <td>{{ $ai_service['ai_service_name'] }}</td>
                                    <td>{{ $ai_service['ai_provider_id'] }}</td>
                                    <td>{{ $ai_service['ai_module_name'] }}</td>
                                    <td class='option-btn-cell text-end'>
                                        {!!
                                            action_button(array(
                                                array('title' => trans('langEditChange'),
                                                      'url' => "$_SERVER[SCRIPT_NAME]?edit=$row->id",
                                                      'icon' => 'fa-edit'),
                                                array('title' => trans('langDelete'),
                                                      'url' => "$_SERVER[SCRIPT_NAME]?delete=$row->id",
                                                      'icon' => 'fa-times',
                                                      'class' => 'delete',
                                                      'confirm' => trans('langConfirmDelete'))))
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

@endsection

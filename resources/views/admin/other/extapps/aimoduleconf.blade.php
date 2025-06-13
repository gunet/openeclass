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
                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>

                        <form class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                            <div class='form-group'>
                                <label for="dropdown" class="form-label">Select Provider</label>
                                <select id='dropdownprovider' name="dropdown" class="form-control">
                                    <?php foreach ($dropdownOptions as $option): ?>
                                    <option value="<?= q($option['value']) ?>"><?= q($option['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class='form-group mt-3'>
                                <label for="apiKeyInput" class="form-label">API Key</label>
                                <input type="text" id="apiKeyInput" name="api_key" class="form-control" placeholder="Enter API key">
                            </div>

                            <div id='modelDropdownContainer' class='form-group mt-3'>
                                <label for="modelDropdown" class="form-label">Model</label>
                                <select id="modelDropdown" name="model" class="form-control">
                                    <option value="">Select a model</option>
                                </select>
                            </div>

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
                                    <label for="modelName" class="form-label">Model Name</label>
                                    <input type="text" id="modelName" name="model_name" class="form-control" placeholder="Enter model name">
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


            </div>
        </div>
    </div>
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
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langAutoJudgeConnector') }}</label>
                                    <div class='col-sm-12'>
                                        <select class='form-select' name='formconnector'>{!! implode('', $connectorOptions) !!}</select>
                                    </div>
                                </div>
                                @foreach($connectorClasses as $curConnectorClass)
                                    <div class='form-group connector-config connector-{{ $curConnectorClass }} mt-4' style='display: none;'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langAutoJudgeSupportedLanguages') }}</label>
                                        <div class='col-sm-12'>
                                            {!! implode(', ', array_keys((new $curConnectorClass)->getSupportedLanguages())) !!}</div>
                                    </div>
                                    <div class='form-group connector-config connector-{{ $curConnectorClass }} mt-4' style='display: none;'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langAutoJudgeSupportsInput') }}</label>
                                        <div class='col-sm-12'>
                                            {{ (new $curConnectorClass)->supportsInput() ? trans("langCMeta['true']") : trans("langCMeta['false']") }}
                                        </div>
                                    </div>
                                    @foreach((new $curConnectorClass())->getConfigFields() as $curField => $curLabel)
                                        <div class='form-group connector-config connector-{{ $curConnectorClass }} mt-4' style='display: none;'>
                                            <label class='col-sm-12 control-label-notes'>{{ $curLabel }}:</label>
                                            <div class='col-sm-12'><input class='FormData_InputText' type='text' name='form$curField' size='40' value='{{ get_config($curField) }}'></div>
                                        </div>
                                    @endforeach
                                @endforeach
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
@endsection
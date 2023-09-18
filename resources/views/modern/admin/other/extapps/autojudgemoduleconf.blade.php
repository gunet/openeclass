@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    

                   

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                            
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
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        
                                          
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
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                      <div class='col-12 h-100 left-form'></div>
                    </div>
                
        </div>
</div>
</div>
@endsection
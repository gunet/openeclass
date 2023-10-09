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
                        <div class='form-wrapper form-edit border-0 px-0'>
                            
                            <form class='form-horizontal' role='form' name='serverForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            <fieldset>
                                <div class='form-group'>
                                    <label for='api_url_form' class='col-sm-12 control-label-notes'>API URL</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' placeholder="api url..." type='text' id='api_url_form' name='api_url_form' value='{{ isset($server) ? $server->api_url : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='key_form' class='col-sm-12 control-label-notes'>{{ trans('langPresharedKey') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' placeholder="{{ trans('langPresharedKey') }}..." type='text' name='key_form' value='{{ isset($server) ? $server->server_key : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='max_rooms_form' class='col-sm-12 control-label-notes'>{{ trans('langMaxRooms') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' placeholder="{{ trans('langMaxRooms') }}..." id='max_rooms_for' name='max_rooms_form' value='{{ isset($server) ? $server->max_rooms : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='max_rooms_form' class='col-sm-12 control-label-notes'>{{ trans('langMaxUsers') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' placeholder="{{ trans('langMaxUsers') }}..." id='max_users_form' name='max_users_form' value='{{ isset($server) ? $server->max_users : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langBBBEnableRecordings') }}</label>
                                    <div class="col-sm-12 d-inline-flex">
                                        <div class='radio'>
                                            <label>
                                                <input  type='radio' id='recordings_on' name='enable_recordings' value='true'{{ $enabled_recordings ? ' checked' : '' }}>
                                                {{ trans('langYes') }}
                                            </label>
                                        </div>                
                                        <div class='radio ms-4'>
                                            <label>
                                                <input  type='radio' id='recordings_off' name='enable_recordings' value='false'{{ $enabled_recordings ? '' : ' checked' }}>
                                                {{ trans('langNo') }}
                                            </label>
                                        </div>                    
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langActivate') }}</label>
                                    <div class="col-sm-12 d-inline-flex">
                                        <div class='radio'>
                                            <label>
                                                <input  type='radio' id='enabled_true' name='enabled' value='true'{{ $enabled ? ' checked' : '' }}>
                                                {{ trans('langYes') }}
                                            </label>
                                        </div>                
                                        <div class='radio ms-4'>
                                            <label>
                                                <input  type='radio' id='enabled_false' name='enabled' value='false'{{ $enabled ? '' : ' checked' }}>
                                                {{ trans('langNo') }}
                                            </label>
                                        </div>                      
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langBBBServerOrder') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' placeholder="{{ trans('langBBBServerOrder') }}..." name='weight' value='{{ isset($server) ? $server->weight : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langUseOfTc') }}</label>
                                    <div class="col-sm-12">
                                        <select class='form-select' name='tc_courses[]' multiple class='form-control' id='select-courses'>                        
                                            {!! $listcourses !!}
                                        </select>            
                                        <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
                                    </div>
                                </div>
                                @if (isset($server))
                                    <input class='form-control' type = 'hidden' name = 'id_form' value='{{ getIndirectReference($bbb_server) }}'>
                                @endif
                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langAddModify') }}'>
                                    </div>
                                </div>
                            </fieldset>
                            </form>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{{$urlAppend}}template/modern/img/form-image.png' alt='form-image'>
                    </div>
                
        </div>
</div>
</div>


<script language="javaScript" type="text/javascript">
    var chkValidator  = new Validator("serverForm");
    chkValidator.addValidation("key_form","req","{{ trans('langBBBServerAlertKey') }}");
    chkValidator.addValidation("api_url_form","req","{{ trans('langBBBServerAlertAPIUrl') }}");
    chkValidator.addValidation("max_rooms_form","req","{{ trans('langBBBServerAlertMaxRooms') }}");
    chkValidator.addValidation("max_rooms_form","numeric","{{ trans('langBBBServerAlertMaxRooms') }}");
    chkValidator.addValidation("max_users_form","req","{{ trans('langBBBServerAlertMaxUsers') }}");
    chkValidator.addValidation("max_users_form","numeric","{{ trans('langBBBServerAlertMaxUsers') }}");
    chkValidator.addValidation("weight","req","{{ trans('langBBBServerAlertOrder') }}");
    chkValidator.addValidation("weight","numeric","{{ trans('langBBBServerAlertOrder') }}");
</script>  
@endsection
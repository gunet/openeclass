@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    
                        {!! isset($action_bar) ?  $action_bar : '' !!}

                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>
                        <div class='col-lg-6 col-12'>
                           <div class='form-wrapper form-edit rounded'>
                            
                            <form class='form-horizontal' role='form' name='serverForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                                <fieldset>        
                                    <div class='form-group'>
                                    <label for='host' class='col-sm-12 control-label-notes'>{{ trans('langOpenMeetingsServer') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' id='host' type='text' placeholder="{{ trans('langOpenMeetingsServer') }}..." name='hostname_form' value='{{ isset($server) ? $server->hostname : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='ip_form' class='col-sm-12 control-label-notes'>{{ trans('langPort') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' placeholder="{{ trans('langPort') }}..." id='ip_form' name='port_form' value='{{ isset($server) ? $server->port : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='key_form' class='col-sm-12 control-label-notes'>{{ trans('langOpenMeetingsAdminUser') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' placeholder="{{ trans('langOpenMeetingsAdminUser') }}..." name='username_form' value='{{ isset($server) ? $server->username : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='api_url_form' class='col-sm-12 control-label-notes'>{{ trans('langOpenMeetingsAdminPass') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' placeholder="{{ trans('langOpenMeetingsAdminPass') }}..." name='password_form' value='{{ isset($server) ? $server->password : "" }}'>
                                    </div>
                                </div>            
                                <div class='form-group mt-4'>
                                    <label for='webapp_form' class='col-sm-12 control-label-notes'>{{ trans('langOpenMeetingsWebApp') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' placeholder="{{ trans('langOpenMeetingsWebApp') }}..." name='webapp_form' value='{{ isset($server) ? $server->webapp : "" }}'>
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
                                    <input class='form-control' type='hidden' name='id_form' value='{{ getIndirectReference($om_server) }}'>
                                @endif            
                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langAddModify') }}'>
                                    </div>
                                </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script language="javaScript" type="text/javascript">
        var chkValidator  = new Validator("serverForm");
        chkValidator.addValidation("hostname_form","req","{{ trans('langBBBServerAlertHostname') }}");
        chkValidator.addValidation("key_form","req","{{ trans('langBBBServerAlertKey') }}");            
        chkValidator.addValidation("max_rooms_form","req","{{ trans('langBBBServerAlertMaxRooms') }}");
</script>    
@endsection
@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                            
                            <form class='form-horizontal' role='form' name='serverForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            <fieldset>
                                <div class='form-group mt-3'>
                                    <label for='api_url_form' class='col-sm-6 control-label-notes'>API URL:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' id='api_url_form' name='api_url_form' value='{{ isset($server) ? $server->api_url : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label for='key_form' class='col-sm-6 control-label-notes'>{{ trans('langPresharedKey') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='key_form' value='{{ isset($server) ? $server->server_key : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label for='max_rooms_form' class='col-sm-6 control-label-notes'>{{ trans('langMaxRooms') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' id='max_rooms_for' name='max_rooms_form' value='{{ isset($server) ? $server->max_rooms : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label for='max_rooms_form' class='col-sm-6 control-label-notes'>{{ trans('langMaxUsers') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' id='max_users_form' name='max_users_form' value='{{ isset($server) ? $server->max_users : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langBBBEnableRecordings') }}:</label>
                                    <div class="col-sm-12">
                                        <div class='radio'>
                                            <label>
                                                <input  type='radio' id='recordings_on' name='enable_recordings' value='true'{{ $enabled_recordings ? ' checked' : '' }}>
                                                {{ trans('langYes') }}
                                            </label>
                                        </div>                
                                        <div class='radio'>
                                            <label>
                                                <input  type='radio' id='recordings_off' name='enable_recordings' value='false'{{ $enabled_recordings ? '' : ' checked' }}>
                                                {{ trans('langNo') }}
                                            </label>
                                        </div>                    
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langActivate') }}:</label>
                                    <div class="col-sm-12">
                                        <div class='radio'>
                                            <label>
                                                <input  type='radio' id='enabled_true' name='enabled' value='true'{{ $enabled ? ' checked' : '' }}>
                                                {{ trans('langYes') }}
                                            </label>
                                        </div>                
                                        <div class='radio'>
                                            <label>
                                                <input  type='radio' id='enabled_false' name='enabled' value='false'{{ $enabled ? '' : ' checked' }}>
                                                {{ trans('langNo') }}
                                            </label>
                                        </div>                      
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langBBBServerOrder') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='weight' value='{{ isset($server) ? $server->weight : "" }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langUseOfTc') }}:</label>
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
                                <div class='form-group mt-3'>
                                    <div class='col-sm-offset-3 col-sm-9'>
                                        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langAddModify') }}'>
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
@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='serverForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
        <fieldset>        
            <div class='form-group'>
            <label for='host' class='col-sm-3 control-label'>{{ trans('langOpenMeetingsServer') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' id='host' type='text' name='hostname_form' value='{{ isset($server) ? $server->hostname : "" }}'>
            </div>
        </div>
        <div class='form-group'>
            <label for='ip_form' class='col-sm-3 control-label'>{{ trans('langPort') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' id='ip_form' name='port_form' value='{{ isset($server) ? $server->port : "" }}'>
            </div>
        </div>
        <div class='form-group'>
            <label for='key_form' class='col-sm-3 control-label'>{{ trans('langOpenMeetingsAdminUser') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='username_form' value='{{ isset($server) ? $server->username : "" }}'>
            </div>
        </div>
        <div class='form-group'>
            <label for='api_url_form' class='col-sm-3 control-label'>{{ trans('langOpenMeetingsAdminPass') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='password_form' value='{{ isset($server) ? $server->password : "" }}'>
            </div>
        </div>            
        <div class='form-group'>
            <label for='webapp_form' class='col-sm-3 control-label'>{{ trans('langOpenMeetingsWebApp') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='webapp_form' value='{{ isset($server) ? $server->webapp : "" }}'>
            </div>
        </div>
        <div class='form-group'>
            <label for='max_rooms_form' class='col-sm-3 control-label'>{{ trans('langMaxRooms') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' id='max_rooms_for' name='max_rooms_form' value='{{ isset($server) ? $server->max_rooms : "" }}'>
            </div>
        </div>
        <div class='form-group'>
            <label for='max_rooms_form' class='col-sm-3 control-label'>{{ trans('langMaxUsers') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' id='max_users_form' name='max_users_form' value='{{ isset($server) ? $server->max_users : "" }}'>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langBBBEnableRecordings') }}:</label>
            <div class="col-sm-9">
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
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langActivate') }}:</label>
            <div class="col-sm-9">
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
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langBBBServerOrder') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='weight' value='{{ isset($server) ? $server->weight : "" }}'>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langUseOfTc') }}:</label>
            <div class="col-sm-9">
                <select class='form-control' name='tc_courses[]' multiple class='form-control' id='select-courses'>                        
                    {!! $listcourses !!}
                </select>            
                <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
            </div>
        </div>
        @if (isset($server))
            <input class='form-control' type='hidden' name='id_form' value='{{ getIndirectReference($om_server) }}'>
        @endif            
        <div class='form-group'>
            <div class='col-sm-offset-3 col-sm-9'>
                <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langAddModify') }}'>
            </div>
        </div>
        </fieldset>
    </form>
    </div>
   <script language="javaScript" type="text/javascript">
            var chkValidator  = new Validator("serverForm");
            chkValidator.addValidation("hostname_form","req","{{ trans('langBBBServerAlertHostname') }}");
            chkValidator.addValidation("key_form","req","{{ trans('langBBBServerAlertKey') }}");            
            chkValidator.addValidation("max_rooms_form","req","{{ trans('langBBBServerAlertMaxRooms') }}");
    </script>    
@endsection
@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='serverForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
        <fieldset>
            <div class='form-group'>
                <label for='host' class='col-sm-3 control-label'>{{ trans('langWebConfServer') }}:</label>
                <div class='col-sm-9'>
                    <input class='form-control' id='host' type='text' name='hostname_form' value="{{ isset($server) ? $server->hostname : ''}}">
                </div>
            </div>
            <div class='form-group'>
                <label for='rtpm' class='col-sm-3 control-label'>{{ trans('langWebConfScreenshareServer') }}:</label>
                <div class='col-sm-9'>
                    <input class='form-control' id='screenshare' type='text' name='screenshare_form' value="{{ isset($server) ? $server->screenshare : ''}}">
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langActivate') }}:</label>
                <div class='col-sm-9'>
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
                <label class='col-sm-3 control-label'>{{ trans('langUseOfTc') }}:</label>
                <div class="col-sm-9">
                    <div class='radio'>
                        <label>
                            <input  type='radio' id='enabled_true' name='allcourses' value='1'{{ $enabled_all_courses ? ' checked' : '' }}>                                
                            {{ trans('langYes') }}
                            <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title = '{{ trans('langToAllCoursesInfo') }}'></span>
                        </label>
                    </div>                
                    <div class='radio'>
                        <label>
                            <input  type='radio' id='enabled_false' name='allcourses' value='o'{{ $enabled_all_courses ? '' : ' checked' }}>
                            {{ trans('langNo') }}
                            <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title = '{{ trans('langToSomeCoursesInfo') }}'></span>
                        </label>
                    </div>                      
                </div>
            </div>
            @if (isset($server))
                <input class='form-control' type = 'hidden' name = 'id_form' value='{{ $wc_server }}'>
            @endif
            <div class='form-group'>
                <div class='col-sm-offset-3 col-sm-9'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{!! trans('langAddModify') !!}'>
                </div>
            </div>
        </fieldset>
        </form>
    </div>
    <script language="javaScript" type="text/javascript">
        var chkValidator  = new Validator("serverForm");
        chkValidator.addValidation("hostname_form","req", "{{ trans('langWebConfServerAlertHostname') }}");
        chkValidator.addValidation("rtpm_form","req", "{{ trans('langWebConfScreenshareServerAlertHostname') }}");
    </script>    
@endsection
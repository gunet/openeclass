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
                    <select class='form-control' name='tc_courses[]' multiple class='form-control' id='select-courses'>
                        {!! $listcourses !!}
                    </select>            
                    <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>                
                </div>
            </div>
            @if (isset($server))
                <input class='form-control' type = 'hidden' name = 'id_form' value='{{ $wc_server }}'>
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
        chkValidator.addValidation("hostname_form","req", "{{ trans('langWebConfServerAlertHostname') }}");        
    </script>    
@endsection
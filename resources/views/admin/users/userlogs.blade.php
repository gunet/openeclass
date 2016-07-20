@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    {!! $users_login_data !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='get' action='{{ $_SERVER['SCRIPT_NAME'] }}'>  
            <input type="hidden" name="u" value="{{ $u }}">
            <div class='form-group' data-date='{{ $user_date_start }}' data-date-format='dd-mm-yyyy'>
                <label class='col-sm-2 control-label'>{{ trans('langStartDate') }}:</label>
                <div class='col-sm-10'>               
                    <input class='form-control' name='user_date_start' id='user_date_start' type='text' value = '{{ $user_date_start }}'>
                </div>
            </div>
            <div class='form-group' data-date= '{{ $user_date_end }}' data-date-format='dd-mm-yyyy'>
                <label class='col-sm-2 control-label'>{{ trans('langEndDate') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='user_date_end' id='user_date_start' type='text' value= '{{ $user_date_end }}'>
                </div>
            </div>
            <div class='form-group'>  
                <label class='col-sm-2 control-label'>{{ trans('langLogTypes') }}:</label>
                <div class='col-sm-10'>{!! selection($log_types, 'logtype', $logtype, "class='form-control'") !!}</div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ trans('langCourse') }}:</label>
                <div class="col-sm-10">{!! selection($cours_opts, 'u_course_id', $u_course_id, "class='form-control'") !!}</div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">{{ trans('langLogModules') }}:</label>
                <div class="col-sm-10">{!! selection($module_names, 'u_module_id', '', "class='form-control'") !!}</div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">    
                    <input class="btn btn-primary" type="submit" name="submit" value="{{ trans('langSubmit') }}">
                </div>
            </div>
        </form>
    </div>               
@endsection
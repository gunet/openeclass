@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='alert alert-info'>{{ trans('langAskManyUsersToCourses') }}</div>
    <div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
            <fieldset>
               <h4>{{ trans('langUsersData') }}</h4>
                <div class='form-group'>
                    <div class='radio'>
                    <label>
                        <input type='radio' name='type' value='uname' checked>{{ trans('langUsername') }}
                    </label>
                    </div>
                <div class='col-sm-7'>{!! text_area('user_info', 10, 30, '') !!}</div>
            </div>
            </fieldset>
            <fieldset>
               <h4>{{ trans('langCourseCodes') }}</h4>
               <div class='form-group'>
                    <div class='col-sm-7'>{!! text_area('courses_codes', 10, 30, '') !!}</div>
                </div>
                {!! showSecondFactorChallenge() !!}
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                </div>
            </fieldset>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection
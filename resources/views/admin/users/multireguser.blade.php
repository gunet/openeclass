@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='alert alert-info'>{!! trans('langMultiRegUserInfo') !!}</div>
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit='return validateNodePickerForm();' >
        <fieldset>        
        <div class='form-group'>
            <label for='fields' class='col-sm-3 control-label'>{{ trans('langMultiRegFields') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' id='fields' type='text' name='fields' value='first last id email phone'>
            </div>
        </div>
        <div class='form-group'>
            <label for='user_info' class='col-sm-3 control-label'>{{ trans('langUsersData') }}:</label>
            <div class='col-sm-9'>
                <textarea class='auth_input form-control' name='user_info' id='user_info' rows='10'></textarea>
            </div>
        </div>
        <div class='form-group'>
            <label for='type' class='col-sm-3 control-label'>{{ trans('langMultiRegType') }}:</label>
            <div class='col-sm-9'>
                <select class='form-control' name='type' id='type'>
                    <option value='stud'>{{ trans('langsOfStudents') }}</option>
                    <option value='prof'>{{ trans('langOfTeachers') }}</option>
                </select>
            </div>
        </div>
        @if (!$eclass_method_unique)
            <div class='form-group'>
                <label for='passsword' class='col-sm-3 control-label'>{{ trans('langMethods') }}</label>
                <div class='col-sm-9'>
                    {!! selection($auth_m, "auth_methods_form", '', "class='form-control'") !!}
                </div>
            </div>
        @endif
        <div class='form-group'>
            <label for='prefix' class='col-sm-3 control-label'>{{ trans('langMultiRegPrefix') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='prefix' id='prefix' value='user'>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langFaculty') }}:</label>
            <div class='col-sm-9'>
                {!! $html !!}
            </div>
        </div>
        <div class='form-group'>
            <label for='am' class='col-sm-3 control-label'>{{ trans('langAm') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='am' id='am'>
            </div>
        </div>
        <div class='form-group'>
            <label for='lang' class='col-sm-3 control-label'>{{ trans('langLanguage') }}:</label>
            <div class='col-sm-9'>{!! lang_select_options('lang', 'class="form-control"') !!}</div>
        </div>
        <div class='form-group'>
        <label for='email_public' class='col-sm-3 control-label'>{{ trans('langEmail') }}</label>
            <div class='col-sm-9'>{!! selection($access_options, 'email_public', ACCESS_PRIVATE, 'class="form-control"') !!}</div>
        </div>
        <div class='form-group'>
        <label for='am_public' class='col-sm-3 control-label'>{{ trans('langAm') }}</label>
            <div class='col-sm-9'>{!! selection($access_options, 'am_public', ACCESS_PRIVATE, 'class="form-control"') !!}</div>
        </div>
        <div class='form-group'>
        <label for='phone_public' class='col-sm-3 control-label'>{{ trans('langPhone') }}</label>
            <div class='col-sm-9'>{!! selection($access_options, 'phone_public', ACCESS_PRIVATE, 'class="form-control"') !!}</div>
        </div>
        <div class='form-group'>
        <label for='send_mail' class='col-sm-3 control-label'>{{ trans('langInfoMail') }}</label>
            <div class='col-sm-9'>
                <div class='checkbox'>
                    <label>
                        <input name='send_mail' id='send_mail' type='checkbox'> {{ trans('langMultiRegSendMail') }}
                    </label>
                </div>            
            </div>
        </div>
        <div class='form-group'>
            {!! showSecondFactorChallenge() !!}
            <div class='col-sm-9 col-sm-offset-3'>
                <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                <a class='btn btn-default' href='index.php'>{{ trans('langCancel') }}</a>
            </div>
        </div>       
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>                
@endsection
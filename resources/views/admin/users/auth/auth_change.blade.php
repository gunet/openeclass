@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (isset($auth_methods_active) == 0)
        <div class='alert alert-warning'>{{ trans('langAuthChangeno') }}</div>
    @else
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' name='authchange' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>   
            <fieldset>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>{{ trans('langAuthChangeto') }}:</label>
                    <div class='col-sm-10'>
                        {!! selection($auth_methods_active, 'auth_change', '', "class='form-control'") !!}
                    </div>
                </div>
                <input type='hidden' name='auth' value='{{ getIndirectReference(intval($auth)) }}'>  
                <div class='col-sm-offset-2 col-sm-10'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                </div>
            </fieldset>
            {!! generate_csrf_token_form_field() !!}    
            </form>
        </div>
    @endif         
@endsection
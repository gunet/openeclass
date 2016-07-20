@extends('layouts.default')

@section('content')

{!! $action_bar !!}

@if ($user_registration) 
    @if (!in_array($auth, $authmethods)) 
        <div class='alert alert-danger'>{{ trans('langCannotRegister') }}</div>
    @elseif (!$_SESSION['u_prof'] and !$alt_auth_stud_reg)
        <div class='alert alert-danger'>{{ trans('langCannotRegister') }}</div>
    @elseif ($_SESSION['u_prof'] and !$alt_auth_prof_reg)
        <div class='alert alert-danger'>{{ trans('langCannotRegister') }}</div>
    @else
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='altsearch.php'>
                <fieldset> {{ $auth_instructions }}
                        <div class='form-group'>
                            <label for='UserName' class='col-sm-2 control-label'>{{ trans('langUsername') }}</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' size='30' maxlength='30' name='uname' autocomplete='off' {{ $set_uname }} placeholder='{{ trans('langUserNotice') }}'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='Pass' class='col-sm-2 control-label'>{{ trans('langPass') }}</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='password' size='30' maxlength='30' name='passwd' autocomplete='off' placeholder='{{ trans('langPass') }}'>
                            </div>
                        </div>                    
                    <input type='hidden' name='auth' value='{{ $auth }}'>
                    <div class='form-group'>
                        <div class='col-sm-offset-2 col-sm-10'>
                            {!! $form_buttons !!}
                            @if (isset($_SESSION['prof']) and $_SESSION['prof']) 
                                <input type='hidden' name='p' value='1'>
                            @endif
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>    
    @endif
@else
    <div class='alert alert-info'>{{ trans('langCannotRegister') }}</div>
@endif
@endsection
    
    

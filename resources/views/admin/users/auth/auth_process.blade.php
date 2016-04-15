@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' name='authmenu' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
        <fieldset>	
            <input type='hidden' name='auth' value='{{ getIndirectReference($auth) }}'>
            @if ($auth == 1)
                @include('admin.users.auth.methods.eclass')
            @elseif ($auth == 2)
                @include('admin.users.auth.methods.pop3')
                @include('admin.users.auth.methods.eclass')
            @elseif ($auth == 3)
                @include('admin.users.auth.methods.imap')
                @include('admin.users.auth.methods.eclass') 
            @elseif ($auth == 4)
                @include('admin.users.auth.methods.ldap')
                @include('admin.users.auth.methods.eclass')   
            @elseif ($auth == 5)
                @include('admin.users.auth.methods.db')
                @include('admin.users.auth.methods.eclass')
            @elseif ($auth == 6)
                @include('admin.users.auth.methods.shib')
                @include('admin.users.auth.methods.eclass')
            @elseif ($auth == 7)
                @include('admin.users.auth.methods.cas')
                @include('admin.users.auth.methods.eclass')
            @else
                @include('admin.users.auth.methods.hybrid')
            @endif
            {!! showSecondFactorChallenge() !!}
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                    <a class='btn btn-default' href='auth.php'>{{ trans('langCancel') }}</a>
                </div>
            </div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>            
@endsection
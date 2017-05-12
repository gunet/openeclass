@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (get_admin_rights($user) > 0)
        <div class='alert alert-warning'>
            {{ trans('langCantDeleteAdmin', ["<em>$u_realname ($u_account)</em>"]) }}
            {{ trans('langIfDeleteAdmin') }}
        </div>
    @else
        <div class='alert alert-warning'>{{ trans('langConfirmDeleteQuestion1') }} <em>{{ $u_realname }} ({{ $u_account }})</em><br>
            {{ trans('langConfirmDeleteQuestion3') }}
        </div>
        <form method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $user }}'>
            {!! showSecondFactorChallenge() !!}
            <input class='btn btn-danger' type='submit' name='doit' value='{{ trans('langDelete') }}'>
            {!! generate_csrf_token_form_field() !!}
        </form>
    @endif
@endsection
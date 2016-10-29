@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
        <div class='alert alert-warning'>
            {{ $user_request->status == 5 ? trans('langWarnReject') : trans('langGoingRejectRequest') }}
        </div>
        <table class='table-default'>
            <tr>
                <th>{{ trans('langName') }}</th>
                <td>{{ $user_request->givenname }}</td>
            </tr>
            <tr>
                <th>{{ trans('langSurname') }}</th>
                <td>{{ $user_request->surname }}</td>
            </tr>
            <tr>
                <th>{{ trans('langEmail') }}</th>
                <td>{{ $user_request->email }}</td>
            </tr>
            <tr>
                <th class='left'>{{ trans('langComments') }}</th>
                <td>
                    <input type='hidden' name='id' value='{{ $id }}'>
                    <input type='hidden' name='close' value='2'>
                    <input type='hidden' name='prof_givenname' value='{{ $user_request->givenname }}'>
                    <input type='hidden' name='prof_surname' value='{{ $user_request->surname }}'>
                    <textarea class='auth_input' name='comment' rows='5' cols='60'>{{ $user_request->comment }}</textarea>
                </td>
            </tr>
            <tr>
                <th class='left'>{{ trans('langRequestSendMessage') }}</th>
                <td>
                    &nbsp;<input type='text' class='auth_input' name='prof_email' value='{{ $user_request->email }}'>
                    <input type='checkbox' name='sendmail' value='1' checked='yes'> 
                    <small>({{ trans('langGroupValidate') }})</small>
                </td>
            </tr>
            <tr>
                <th class='left'>&nbsp;</th>
                <td>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRejectRequest') }}'>
                    &nbsp;&nbsp;
                    <small>({{ trans('langRequestDisplayMessage') }})</small>
                </td>
            </tr>
        </table>
        {!! generate_csrf_token_form_field() !!}
    </form>
@endsection
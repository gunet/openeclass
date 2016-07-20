@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <form name='mail_verification' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
        <div class='table-responsive'>
	<table class='table-default'>
		<tr>
                    <td class='text-left' colspan='3'>
                        <b>{{ trans('langMailVerificationSettings') }}</b>
                    </td>
                </tr>
		<tr>
                    <td class='text-left' colspan='2'>{{ trans('lang_email_required') }}:</td>
                    <td class='text-center'>{{ $mr }}</td>
                </tr>
		<tr>
                    <td class='text-left' colspan='2'>{{ trans('lang_email_verification_required') }}:</td>
                    <td class='text-center'>{{ $mv }}</td>
                </tr>
		<tr>
                    <td class='text-left' colspan='2'>{{ trans('lang_dont_mail_unverified_mails') }}:</td>
                    <td class='text-center'>{{ $mm }}</td>
                </tr>
		<tr>
                    <td colspan='3'>&nbsp;</td>
                </tr>
		<tr>
                    <td>
                        <a href='listusers.php?search=yes&verified_mail=1'>{{ trans('langMailVerificationYes') }}</a>
                    </td>
                    <td class='text-center'>
                        <b>{{ $verified_email_cnt }}</b>
                    </td>
                    <td class='text-right'><input class='btn btn-primary' type='submit' name='submit1' value='{{ trans("m['edit']") }}'></td>
                </tr>
		<tr>
                    <td>
                        <a href='listusers.php?search=yes&verified_mail=2'>{{ trans('langMailVerificationNo') }}</a></td>
			<td class='text-center'>
                            <b>{{ $unverified_email_cnt }}</b>
                        </td>
                        <td class='text-right'>
                            <input class='btn btn-primary' type='submit' name='submit2' value='{{ trans("m['edit']}") }}'>
                        </td>
                </tr>
		<tr>
                    <td>
                        <a href='listusers.php?search=yes&verified_mail=0'>{{ trans('langMailVerificationPending') }}</a></td>
			<td class='text-center'>
                            <b>{{ $verification_required_email_cnt }}</b>
                        </td>
                        <td class='text-right'>
                            <input class='btn btn-primary' type='submit' name='submit0' value='{{ trans("m['edit']") }}'>
                        </td>
                </tr>
                @if (!get_config('email_required'))
                    <tr>
                        <td>
                            <a href='listusers.php?search=yes&verified_mail=0'>{{ trans('langUsersWithNoMail') }}</a>
                        </td>
                        <td class='text-center'>
                            <b>{{ $empty_email_user_cnt }}</b>
                        </td>
                        <td class='text-right'>&nbsp;</td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <a href='listusers.php?search=yes'>{{ trans('langTotal') }} {{ trans('langUsersOf') }}</a>
                    </td>
                    <td class='text-center'>
                        <b>{{ $user_cnt }}</b>
                    </td>
                    <td class='text-right'>&nbsp;</td>
                </tr>
	</table>
        </div> 
        {!! generate_csrf_token_form_field() !!}
    </form>
    @include('admin.users.mail_ver_settings.messages')
@endsection
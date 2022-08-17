@extends('layouts.default')

@section('content')


<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <form name='mail_verification' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                        <div class='table-responsive'>
                    <table class='announcements_table'>
                        <tr class='notes_thead'>
                                    <td class='text-white text-left' colspan='3'>
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
                                    <td class='text-end'><input class='btn btn-primary' type='submit' name='submit1' value='{{ trans("m['edit']") }}'></td>
                                </tr>
                        <tr>
                                    <td>
                                        <a href='listusers.php?search=yes&verified_mail=2'>{{ trans('langMailVerificationNo') }}</a></td>
                            <td class='text-center'>
                                            <b>{{ $unverified_email_cnt }}</b>
                                        </td>
                                        <td class='text-end'>
                                            <input class='btn btn-primary' type='submit' name='submit2' value='{{ trans("m['edit']}") }}'>
                                        </td>
                                </tr>
                        <tr>
                                    <td>
                                        <a href='listusers.php?search=yes&verified_mail=0'>{{ trans('langMailVerificationPending') }}</a></td>
                            <td class='text-center'>
                                            <b>{{ $verification_required_email_cnt }}</b>
                                        </td>
                                        <td class='text-end'>
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
                                        <td class='text-end'>&nbsp;</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>
                                        <a href='listusers.php?search=yes'>{{ trans('langTotal') }} {{ trans('langUsersOf') }}</a>
                                    </td>
                                    <td class='text-center'>
                                        <b>{{ $user_cnt }}</b>
                                    </td>
                                    <td class='text-end'>&nbsp;</td>
                                </tr>
                    </table>
                        </div> 
                        {!! generate_csrf_token_form_field() !!}
                    </form>
                    @include('admin.users.mail_ver_settings.messages')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
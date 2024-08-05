@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/pwstrength.js'></script>
    @if(isset($mail_notification))
        <script type="text/javascript">$(control_deactivate);</script>
    @endif
@endpush

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        @if(isset($mailNotVerified))
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                {{ trans('langMailNotVerified') }}
                                <a href = '{{ $urlAppend }}modules/auth/mail_verify_change.php?from_profile=true'>{{ trans('langHere') }}</a>
                            </span>
                            </div>
                        @endif
                        @if(isset($mail_notification))
                            <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langEmailUnsubscribeWarning') }}</span></div>
                            <label class='label-container'>
                                <input type='checkbox' id='unsub' name='unsub' value='1'>
                                <span class='checkmark'></span>
                                {{ trans('langEmailFromCourses') }}
                            </label>
                        @endif
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{!! trans('langInfoUnsubscribe') !!}</span></div>

                        <div class='col-12 mt-5'>
                            <div class='row'>



                                <div class='col-lg-6 col-12'>
                                    <div class='form-wrapper form-edit border-0 px-0'>
                                        <div id='unsubscontrols'>
                                        @if(isset($_REQUEST['cid']))
                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                            <input type='checkbox' name='c_unsub' value='1' {{ $selected }}>
                                            @php $cid = $_GET['cid']; $course_title = course_id_to_title($cid) @endphp
                                            <span class='checkmark'></span>
                                            {{ $course_title }}
                                            <input type='hidden' name='cid' value='{{ $cid }}'>
                                        </label>
                                        @else
                                            @foreach($_SESSION['courses'] as $code => $status)
                                                @if (course_status(course_code_to_id($code)) != COURSE_INACTIVE)
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='c_unsub[{{ $code }}]' value='1' {{ get_user_email_notification($uid, course_code_to_id($code)) ? 'checked' : '' }}>
                                                    <span class='checkmark'></span>
                                                    {{ course_code_to_title($code) }}
                                                </label>
                                                @endif
                                            @endforeach
                                        @endif
                                        </div>
                                        <br>
                                        <div class='col-12 d-flex justify-content-end align-items-center mt-5 gap-2'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                            <a class='btn cancelAdminBtn' href='display_profile.php'>{{ trans('langCancel') }}</a>
                                        </div>


                                    </div>
                                </div>
                                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                                </div>
                            </div>
                        </div>
                        {!! generate_csrf_token_form_field() !!}
                    </form>

        </div>

</div>
</div>

@endsection

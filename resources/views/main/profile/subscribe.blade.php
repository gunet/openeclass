@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/pwstrength.js'></script>
    @if(isset($mail_notification))
        <script type="text/javascript">$(control_deactivate);</script>
    @endif
@endpush

@section('content')

    {!! $action_bar !!}

    <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
        @if(isset($mailNotVerified))
            <div class='alert alert-warning'>
                {{ trans('langMailNotVerified') }}
                <a href = '{{ $urlAppend }}modules/auth/mail_verify_change.php?from_profile=true'>{{ trans('langHere') }}</a>
            </div>
        @endif
        @if(isset($mail_notification))
            <div class='alert alert-info'>{{ trans('langEmailUnsubscribeWarning') }}</div>
            <input type='checkbox' id='unsub' name='unsub' value='1'>&nbsp;{{ trans('langEmailFromCourses') }}
        @endif
        <div class='alert alert-info'>{!! trans('langInfoUnsubscribe') !!}</div>
            <div id='unsubscontrols'>
            @if(isset($_REQUEST['cid']))
                <input type='checkbox' name='c_unsub' value='1' {{ $selected }}>&nbsp;{{ $course_title }}<br />
                <input type='hidden' name='cid' value='{{ getIndirectReference($cid) }}'>
            @else
                @foreach($_SESSION['courses'] as $code => $status)
                    <input type='checkbox' name='c_unsub[{{ $code }}]' value='1' {{ $selected }}>&nbsp;{{ $title }}<br />
                @endforeach
            @endif
            </div>
            <br>
            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
            <a class='btn btn-default' href='display_profile.php'>{{ trans('langCancel') }}</a>
        {!! generate_csrf_token_form_field() !!}
    </form>

@endsection
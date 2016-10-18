@extends('layouts.default')

@push('head_scripts')
<script type="text/javascript" src="{{ $urlAppend }}js/pwstrength.js"></script>
<script type="text/javascript">

    var lang = {
        pwStrengthTooShort: '{!! js_escape(trans('langPwStrengthTooShort')) !!}',
        pwStrengthWeak: '{!! js_escape(trans('langPwStrengthWeak')) !!}',
        pwStrengthGood: '{!! js_escape(trans('langPwStrengthGood')) !!}',
        pwStrengthStrong: '{!! js_escape(trans('langPwStrengthStrong')) !!}',
    };


    $(document).ready(function() {
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });
    });

</script>
@endpush

@section('content')

{!! $action_bar !!}

@if(isset($_REQUEST['u']) and isset($_REQUEST['h']))
@elseif(isset($_POST['send_link']))
    @if($res_first_attempt)
        @if(!password_is_editable($res_first_attempt->password))
            <div class="row">
                <div class="col-xs-12">
                    <div class='alert alert-danger'>
                        <p><strong>{!! trans('langPassCannotChange1') !!}</strong></p>
                        <p>{!! trans('langPassCannotChange2') !!} {!! get_auth_info($auth) !!}
                            {!! trans('langPassCannotChange3') !!} <a href='mailto:{{ $emailhelpdesk }}'>{{ $emailhelpdesk }}</a> {!! trans('langPassCannotChange4') !!}</p>
                        $homelink
                    </div>
                </div>
            </div>
        @endif
        @if($found_editable_password)
            @if(!$mail_sent)
                <div class="row">
                    <div class="col-xs-12">
                        <div class='alert alert-danger'>
                            <p><strong>{!! trans('langAccountEmailError1') !!}</strong></p>
                            <p>{!! trans('langAccountEmailError2') !!} {{ $email }}.</p>
                            <p>{!! trans('langAccountEmailError3') !!} <a href='mailto:{{ $emailhelpdesk }}'>{{ $emailhelpdesk }}'</a>.</p></div>
                        {{ $homelink }}
                    </div>
                </div>
            @elseif(!isset($auth))
                <div class="row">
                    <div class="col-xs-12">
                        <div class='alert alert-success'>{!! trans('lang_pass_email_ok') !!} <strong>{!! q($email) !!}</strong>
                        </div>{{ $homelink }}
                    </div>
                </div>
            @endif
        @endif
    @else
        @if(isset($res_second_attempt) && $res_second_attempt)
            <div class="row">
                <div class="col-xs-12">
                    <div class='alert alert-danger'>
                        <p>{!! trans('langLostPassPending') !!}</p>
                    </div>
                    {{ $homelink }}
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-xs-12">
                    <div class='alert alert-danger'>
                        <p><strong>{!! trans('langAccountNotFound1') !!} ({!! q("$userName / $email") !!}")</strong></p>
                        <p>{!! trans('langAccountNotFound2') !!} <a href='mailto:{{ $emailhelpdesk }}'>{{ $emailhelpdesk }}</a>, {!! trans('langAccountNotFound3') !!}</p></div>
                    {{ $homelink }}
                </div>
            </div>
        @endif
    @endif
@else
    <div class="row">
        <div class="col-xs-12">
            <div class='alert alert-info'>{!! trans('lang_pass_intro') !!}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='{!! $_SERVER['SCRIPT_NAME'] !!}'>
                    <div class='row'><div class='col-sm-8'><legend>{!! trans('langUserData') !!}</legend></div></div>
                    <div class='form-group'>
                        <div class='col-sm-8'>
                            <input class='form-control' type='text' name='userName' id='userName' autocomplete='off' placeholder='{!! trans('lang_username') !!}'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-8'>
                            <input class='form-control' type='text' name='email' id='email' autocomplete='off' placeholder='{!! trans('lang_email') !!}'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-8'>
                            <button class='btn btn-primary' type='submit' name='send_link' value='$lang_pass_submit'>{!! trans('lang_pass_submit') !!}</button>
                            <button class='btn btn-default' href='{{ $urlServer }}'>{!! trans('langCancel') !!}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
</div>
@endif

@endsection
    
    

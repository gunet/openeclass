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
    
    

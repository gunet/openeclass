
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

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12 mb-4'>
                <h1>{{ trans('lang_remind_pass') }}</h1>
            </div>

            @include('layouts.partials.show_alert') 

            @if(isset($_REQUEST['u']) and isset($_REQUEST['h']))
                @if(isset($is_valid))
                    @if(!$change_ok)
                        <div class='col-12'>
                            <div class='col-lg-6 col-12 ms-auto me-auto mt-3'>
                                <div class="card panelCard px-lg-4 py-lg-3 h-100">
                                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                        <h1>{{ trans('langNewPass1') }} </h1>
                                    </div>
                                    <div class='card-body'>

                                            <form role="form" method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                                <input type='hidden' name='u' value='{{ $userUID }}'>
                                                <input type='hidden' name='h' value='{{ q($_REQUEST['h']) }}'>
                                                <div class="form-group">
                                                    <label  for='password' class='col-sm-12 control-label-notes'>{!! trans('langNewPass1') !!} <span class='Accent-200-cl'>(*)</span></label>
                                                    <div class="col-sm-12">
                                                        <input type='password' placeholder="{!! trans('langNewPass1') !!}" class='form-control' size='40' name='newpass' value='' id='password' autocomplete='off'>&nbsp;<span id='result'></span>
                                                    </div>
                                                </div>
                                                <div class="form-group mt-3">
                                                    <label for='new_pass_word' class="col-sm-12 control-label-notes">{!! trans('langNewPass2') !!} <span class='Accent-200-cl'>(*)</span></label>
                                                    <div class="col-sm-12">
                                                        <input type='password' placeholder="{!! trans('langNewPass2') !!}" class='form-control' size='40' name='newpass1' value='' id='new_pass_word' autocomplete='off'>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-4'>
                                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                                        <input class='btn submitAdminBtn' type='submit' name='submit' value="{{ trans('langModify') }}">
                                                    </div>
                                                </div>
                                            </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @else
                <div class='col-12'>
                    <div class='col-12 mb-5' style='text-align: justify;'>
                        {!! trans('lang_pass_intro') !!}
                    </div>
                    <div class='col-lg-6 col-12 ms-auto me-auto mt-3'>
                        <div class="card panelCard px-lg-4 py-lg-3 h-100">
                            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                <h1>{{ trans('langUserData') }} </h1>
                            </div>
                            <div class='card-body'>

                                    <form role='form' method='post' action='{!! $_SERVER['SCRIPT_NAME'] !!}'>
                                        <div class='form-group'>
                                            <div class='col-12'>
                                                <label for='userName' class='form-label'>{{ trans('lang_username') }} <span class='Accent-200-cl'>(*)</span></label>
                                            </div>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='userName' id='userName' autocomplete='off' placeholder='{{ trans('lang_username') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <div class='col-12'>
                                                <label for='email' class='form-label'>{{ trans('lang_email') }} <span class='Accent-200-cl'>(*)</span></label>
                                            </div>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='email' id='email' autocomplete='off' placeholder='{{ trans('lang_email') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                                <input class='btn submitAdminBtn' type='submit' name='send_link' value='{{ trans('langSend') }}'>
                                                <a class='btn cancelAdminBtn' href='{{ $urlServer }}'>{{ trans('langCancel') }}</a>
                                            </div>
                                        </div>
                                    </form>

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection


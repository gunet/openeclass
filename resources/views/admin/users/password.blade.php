@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript" src="{{ $urlAppend }}js/pwstrength.js"></script>
    <script type='text/javascript'>
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

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            <div class='mt-4'></div>

            @include('layouts.partials.show_alert')

            <div class='col-lg-6 col-12'>
              <div class='form-wrapper form-edit border-0 px-0'>

                  <form class='form-horizontal' role='form' method='post' action='{{ $urlServer }}modules/admin/password.php'>
                    <fieldset>
                      <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                      <input type='hidden' name='userid' value='{{ $_GET['userid'] }}'>
                      <div class='form-group'>
                        <label for='password' class='col-sm-12 control-label-notes'>{{ trans('langNewPass1') }}</label>
                        <div class='col-sm-12'>
                            <input class='form-control' placeholder="{{ trans('langNewPass1') }}" type='password' size='40' name='password_form' value='' id='password' autocomplete='off'>&nbsp;
                            <span id='result'></span>
                        </div>
                      </div>
                      <div class='form-group mt-4'>
                        <label id='password_formid' class='col-sm-12 control-label-notes'>{{ trans('langNewPass2') }}</label>
                        <div class='col-sm-12'>
                            <input id='password_formid' class='form-control' placeholder="{{ trans('langNewPass2') }}" type='password' size='40' name='password_form1' value='' autocomplete='off'>
                        </div>
                      </div>
                      <div class='col-12 mt-5 d-flex justify-content-end align-items-center gap-2'>
                        {!! showSecondFactorChallenge() !!}
                        <input class='btn submitAdminBtn' type='submit' name='changePass' value='{{ trans('langSubmit') }}'>
                        <a class='btn cancelAdminBtn' href='{{ $urlServer }}modules/admin/edituser.php?u={{ urlencode(getDirectReference($_REQUEST['userid'])) }}'>{{ trans('langCancel') }}</a>
                      </div>
                    </fieldset>
                    {!! generate_csrf_token_form_field() !!}
                  </form>
              </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
            </div>

        </div>
    </div>
</div>
@endsection

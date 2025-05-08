@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {
            $("#privacyPolicyConsent").click(function () {
                if ($(this).is(':checked')) {
                    $('#privacyPolicyLink')
                        .prop('checked', true)
                        .prop('disabled', true);
                } else {
                    $('#privacyPolicyLink')
                        .prop('disabled', false);
                }
            });
            $('#privacyPolicyLink')
                .prop('disabled', $("#privacyPolicyConsent").is(':checked'));
        });
    </script>
@endpush

@extends('layouts.default')

@section('content')
    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">
                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                @include('layouts.partials.legend_view')

                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit'>
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                                <div class='landing-default'>
                                    @foreach ($selectable_langs as $langCode => $langName)
                                        <div class='form-group mb-4'>
                                            <label for='privacy_policy_text_{{ $langCode }}' class='col-sm-12 control-label-notes'>{{ trans('langText') }}: <span>({{ $langName }})</span></label>
                                            <div class='col-sm-12'>
                                                {!! rich_text_editor('privacy_policy_text_'.$langCode, 5, 20, $policyText[$langCode]) !!}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes mb-1'>{{ trans('langViewShow') }}: </div>
                                    <div class='col-sm-12'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                                <input id='privacyPolicyLink' type='checkbox' name='activate_privacy_policy_text' {{ $cbox_activate_privacy_policy_text }} >
                                                <span class='checkmark'></span>
                                                {{ trans('langDisplayPrivacyPolicyLink') }}
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                                <input id='privacyPolicyConsent' type='checkbox' name='activate_privacy_policy_consent' {{ $cbox_activate_privacy_policy_consent }} >
                                                <span class='checkmark'></span>
                                                {{ trans('langAskPrivacyPolicyConsent') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                        <a class='btn cancelAdminBtn' href='index.php'>{{ trans('langCancel') }}</a>
                                    </div>
                                </div>

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

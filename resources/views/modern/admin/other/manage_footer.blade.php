@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @include('layouts.partials.show_alert') 

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>


                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langText') }}</label>
                                    <div class='col-sm-12'>
                                        {!! $footer_intro !!}
                                    </div>
                                </div>

                                <div class='col-sm-12 mt-4'>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='dont_display_contact_menu' value='1' {!! get_config('dont_display_contact_menu') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{trans('lang_dont_display_contact_menu')}}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='dont_display_about_menu' value='1' {!! get_config('dont_display_about_menu') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{trans('lang_dont_display_about_menu')}}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='dont_display_manual_menu' value='1' {!! get_config('dont_display_manual_menu') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{trans('lang_dont_display_manual_menu')}}
                                        </label>
                                    </div>

                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input id='privacyPolicyLink' type='checkbox' name='activate_privacy_policy_text' value='1' {!! get_config('activate_privacy_policy_text') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{ trans('langDisplayPrivacyPolicyLink') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input id='privacyPolicyConsent' type='checkbox' name='activate_privacy_policy_consent' value='1' {!! get_config('activate_privacy_policy_consent') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{ trans('langAskPrivacyPolicyConsent') }}
                                        </label>
                                    </div>

                                </div>


                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                        <button type="submit" class="btn submitAdminBtn" name="submit">{{ trans('langSave') }}</button>
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                    </div>

        </div>
    </div>

</div>

@endsection

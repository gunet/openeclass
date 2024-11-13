@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">
            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
            @include('layouts.partials.legend_view')

            <div class='col-lg-6 col-12 mt-3'>
                <div class='form-wrapper form-edit'>
                    <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                                <div class='form-group'>
                                    <label for='formpostaddress' class='col-sm-12 control-label-notes'>{{ trans('langPostMail') }}</label>
                                    <div class='col-sm-12'>
                                        <textarea class='form-control form-control-admin' name='formpostaddress' id='formpostaddress'>{{ get_config('postaddress') }}</textarea>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='formtelephone' class='col-sm-12 control-label-notes'>{{ trans('langPhone') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control form-control-admin' type='text' name='formtelephone' id='formtelephone' value='{{ get_config('phone') }}'>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='formemailhelpdesk' class='col-sm-12 control-label-notes'>{{ trans('langHelpDeskEmail') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control form-control-admin' type='text' name='formemailhelpdesk' id='formemailhelpdesk' value='{{ get_config('email_helpdesk') }}'>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                            <input type='checkbox' name='enable_form_contact' {!! get_config('contact_form_activation') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{ trans('langEnableContactInfo') }}
                                        </label>
                                    </div>
                                </div>

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
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

@extends('layouts.default')

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

                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                            <div class='form-group'>
                                <label for='email_title' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class='col-sm-12'>
                                    <input id='email_title' class='form-control' placeholder="{{ trans('langTitle') }}" type='text' name='email_title' value=''>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                            <label for='body_mail' class='col-sm-12 control-label-notes'>{{ trans('typeyourmessage') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class='col-sm-12'>
                                {!! $body_mail_rich_text !!}
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class='col-sm-12'>
                                    {!! $buildusernode !!}
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='col-sm-12 control-label-notes mb-1'>{{ trans('langSendMessageTo') }} <span class='asterisk Accent-200-cl'>(*)</span></div>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                            <input type='checkbox' name='send_to_prof' value='1'>
                                            <span class='checkmark'></span> {{ trans('langProfOnly') }}
                                        </label>
                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                            <input type='checkbox' name='send_to_users' value='1'>
                                            <span class='checkmark'></span>{{ trans('langStudentsOnly') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='mt-3'></div>
                            {!! showSecondFactorChallenge() !!}
                            <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSend') }}'>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>

        </div>
</div>
</div>
@endsection

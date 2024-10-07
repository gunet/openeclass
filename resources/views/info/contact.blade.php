@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12 mb-4'>
                <h1>{{ $toolName }}</h1>
            </div>

            @include('layouts.partials.show_alert')

            @if(!empty($postaddress) or !empty($phone) or !empty($emailhelpdesk))
                <div class='col-12'>
                    <div class='row row-cols-1 row-cols-md-3 g-4'>
                        @if(!empty($postaddress))
                            <div class='col'>
                                <div class='col-12 d-flex justify-content-center mb-2'>
                                    <div class='circle-img-contant'>
                                        <i class="fa-solid fa-address-card fa-lg"></i>
                                    </div>
                                </div>
                                <div class='col-12 d-flex justify-content-center text-center mb-0'>
                                    <strong>{!! trans('langInstitutePostAddress') !!}</strong>
                                </div>
                                <div class='col-12 d-flex justify-content-center text-center'>
                                    {!! $postaddress !!}
                                </div>
                            </div>
                        @endif
                        @if(!empty($phone))
                            <div class='col'>
                                <div class='col-12 d-flex justify-content-center mb-2'>
                                    <div class='circle-img-contant'>
                                        <i class="fa-solid fa-phone fa-lg"></i>
                                    </div>
                                </div>
                                <div class='col-12 d-flex justify-content-center text-center mb-0'>
                                    <strong>{!! trans('langPhone') !!}</strong>
                                </div>
                                <div class='col-12 d-flex justify-content-center text-center'>
                                    {{ $phone }}
                                </div>
                            </div>
                        @endif
                        @if(!empty($emailhelpdesk))
                            <div class='col'>
                                <div class='col-12 d-flex justify-content-center mb-2'>
                                    <div class='circle-img-contant'>
                                        <i class="fa-solid fa-square-envelope fa-lg"></i>
                                    </div>
                                </div>
                                <div class='col-12 d-flex justify-content-center text-center mb-0'>
                                    <strong>{!! trans('langEmail') !!}</strong>
                                </div>
                                <div class='col-12 d-flex justify-content-center text-center'>
                                    {!! $emailhelpdesk !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if(get_config('contact_form_activation'))
                <div class='col-12 mt-4'>
                    <div class='row row-cols-1 row-cols-lg-2 g-4'>
                        <div class='col'>
                            <div class='form-wrapper form-edit rounded-2'>
                                <form method='post' action="{{ $urlAppend }}modules/admin/contact_form.php">
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        @if(empty($emailhelpdesk))
                                            <div class='row row-cols-1 mb-4'>
                                                <div class='col'>
                                                    <div class='alert alert-warning'>
                                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                                        <span>{{ trans('langHelpDeskEmailDoesNotExist') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class='row row-cols-1 row-cols-lg-2 g-4 mb-4'>
                                            <div class='col mt-0'>
                                                <label for="NameID" class="col-sm-12 control-label-notes">{{ trans('langName') }} <span class="asterisk Accent-200-cl">(*)</span></label>
                                                <input id='NameID' type='text' class='form-control' name='contact_name'>
                                                @if(Session::getError('contact_name'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('contact_name') !!}</span>
                                                @endif
                                            </div>
                                            <div class='col mt-md-4 mt-lg-0'>
                                                <label for="SurNameID" class="col-sm-12 control-label-notes">{{ trans('langSurname') }} <span class="asterisk Accent-200-cl">(*)</span></label>
                                                <input id="SurNameID" type='text' class='form-control' name='contact_surname'>
                                                @if(Session::getError('contact_surname'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('contact_surname') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='row row-cols-1 g-4 mb-4'>
                                            <div class='col'>
                                                <label for="emailID" class="col-sm-12 control-label-notes">E-mail - {{ trans('langOfUserS') }}<span class="asterisk Accent-200-cl">(*)</span></label>
                                                <input id="emailID" type='email' class='form-control' name='contact_email'>
                                                @if(Session::getError('contact_email'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('contact_email') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='row row-cols-1 g-4 mb-4'>
                                            <div class='col'>
                                                <label for="subjectID" class="col-sm-12 control-label-notes">{{ trans('langSubject') }} <span class="asterisk Accent-200-cl">(*)</span></label>
                                                <input id="subjectID" type='text' class='form-control' name='contact_subject'>
                                                @if(Session::getError('contact_subject'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('contact_subject') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='row row-cols-1 g-4 mb-4'>
                                            <div class='col'>
                                                <label for="messageID" class="col-sm-12 control-label-notes">{{ trans('langMessage') }} <span class="asterisk Accent-200-cl">(*)</span></label>
                                                <textarea id="messageID" class='form-control' placeholder="{{ trans('typeyourmessage') }}" name='contact_message'></textarea>
                                                @if(Session::getError('contact_message'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('contact_message') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='row row-cols-1 justify-content-center g-4 mb-3'>
                                            <div class='col'>
                                                <button class="btn submitAdminBtn d-inline-flex @if(empty($emailhelpdesk)) pe-none opacity-help @endif" name='send_message'>{{ trans('langSend') }}</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <div class='col d-none d-lg-block text-end'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

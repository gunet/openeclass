@push('head_scripts')
    <script type='text/javascript'>
        $(function() {
            $('#send_mail').change(function() {
                if ($(this).is(':checked')) {
                    $('.customEmailBodyDiv').removeClass('hidden');
                } else {
                    $('.customEmailBodyDiv, .emailbody, .customMailHelp').addClass('hidden');
                    $('.emailNewBodyInput').val(0);
                    $('#customEmailBody').prop('checked', false);
                }
            });

            $('#customEmailBody').change(function() {
                if ($(this).is(':checked')) {
                    $('.emailbody, .customMailHelp').removeClass('hidden');
                    $('.emailNewBodyInput').val(1);
                } else {
                    $('.emailbody, .customMailHelp').addClass('hidden');
                    $('.emailNewBodyInput').val(0);
                }
            });
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

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert') 

            <div class='col-12'>
                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{!! trans('langMultiRegUserInfo') !!}</span></div>
            </div>

             <div class='col-lg-6 col-12'>
                <div class='form-wrapper form-edit border-0 px-0'>
                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' enctype='multipart/form-data' onsubmit='return validateNodePickerForm();' >
                <fieldset>
                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                <div class='form-group mt-4'>
                    <label for='userfile' class='col-sm-12 control-label-notes mb-2'>{{ trans('langFileUserData') }} <span class='Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>
                        <input type='file' id='userfile' name='userfile'>
                    </div>
                </div>

                <div class='form-group mt-4'>
                    <label for='type' class='col-sm-12 control-label-notes'>{{ trans('langMultiRegType') }} <span class='Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>
                        <select class='form-select' name='type' id='type'>
                            <option value='stud'>
                                {{ trans('langsOfStudents') }}
                            </option>
                            <option value='prof'>
                                {{ trans('langOfTeachers') }}
                            </option>
                        </select>
                    </div>
                </div>
                @if (!$eclass_method_unique)
                    <div class='form-group mt-4'>
                        <label for='passsword' class='col-sm-12 control-label-notes'>{{ trans('langMethods') }} <span class='Accent-200-cl'>(*)</span></label>
                        <div class='col-sm-12'>
                            {!! selection($auth_m, "auth_methods_form", '', "class='form-control' id='passsword'") !!}
                        </div>
                    </div>
                @endif

                <div class='form-group mt-4'>
                    <label for='prefix' class='col-sm-12 control-label-notes'>{{ trans('langMultiRegPrefix') }} <span class='Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>
                        <input class='form-control' type='text' name='prefix' id='prefix' value='user'>
                    </div>
                </div>

                <div class='form-group mt-4'>
                    <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }} <span class='Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>
                        {!! $html !!}
                    </div>
                </div>

                <div class='form-group mt-4'>
                    <label for='lang' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }} <span class='Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>{!! lang_select_options('lang', 'class="form-control" id="lang"') !!}</div>
                </div>

                <div class='form-group mt-4'>
                <label for='email_public' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }} <span class='Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>{!! selection($access_options, 'email_public', ACCESS_PROFS, 'class="form-control" id="email_public"') !!}</div>
                </div>


                <div class='form-group mt-4'>
                <label for='am_public' class='col-sm-12 control-label-notes'>{{ trans('langAm') }} <span class='Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>{!! selection($access_options, 'am_public', ACCESS_PROFS, 'class="form-control" id="am_public"') !!}</div>
                </div>


                <div class='form-group mt-4'>
                <label for='phone_public' class='col-sm-12 control-label-notes'>{{ trans('langPhone') }} <span class='Accent-200-cl'>(*)</span></label>
                    <div class='col-sm-12'>{!! selection($access_options, 'phone_public', ACCESS_PROFS, 'class="form-control" id="phone_public"') !!}</div>
                </div>

                <div class='form-group mt-4'>
                <label for='send_mail' class='col-sm-12 control-label-notes mb-1'>{{ trans('langInfoMail') }}</label>
                    <div class='col-sm-12'>
                        <div class='checkbox'>
                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                <input name='send_mail' id='send_mail' type='checkbox'><span class='checkmark'></span> {{ trans('langMultiRegSendMail') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class='form-group mt-4 customEmailBodyDiv hidden'>
                    <label for='customEmailBody' class='col-sm-12 control-label-notes mb-1'>{{ trans('langCustomEmailBody') }}</label>
                    <div class='col-sm-12'>
                        <div class='checkbox'>
                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                <input name='customEmailBody' id='customEmailBody' type='checkbox'>
                                <span class='checkmark'></span>{{ trans('langYes') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class='form-group mt-4 emailbody hidden'>
                    <div class='form-group mt-4'>
                        <label for='email_body' class='col-sm-3 control-label-notes mb-1'>{{ trans('langSubject') }}:</label>
                        <div class='col-sm-12'>
                            <input class='form-control' type='text' name='emailNewSubject' id='email_body'>
                        </div>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='emailNewBodyEditor' class='col-sm-12 control-label-notes mb-1'>{{ trans('langEmail') }}:</label>
                        <div class='col-sm-12'>
                            {!! $rich_text_editor !!}
                        </div>
                    </div>
                    <input type='hidden' class='emailNewBodyInput' name='emailNewBodyInput' value=0>
                </div>

                <div class='form-group mt-4 customMailHelp hidden'>
                    <div class='col-sm-12'>
                        <div class='alert alert-info'>{!! trans('langCustomMailHelp') !!}</div>
                    </div>
                </div>

                <div class='form-group mt-5'>
                    {!! showSecondFactorChallenge() !!}
                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                        <a class='btn cancelAdminBtn' href='index.php'>{{ trans('langCancel') }}</a>
                    </div>
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

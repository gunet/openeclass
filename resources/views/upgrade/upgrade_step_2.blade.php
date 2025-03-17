@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>

            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @if ($error_message)
                    <div class='alert alert-danger'>
                        <i class='fa-solid fa-circle-xmark fa-lg'></i>
                        <span>{{ $error_message }}</span>
                    </div>
                @endif

                @if (!in_array(get_config('email_transport'), array('smtp', 'sendmail')) and !get_config('email_announce'))
                    <div class='alert alert-info'>
                        {{ trans('langEmailSendWarn') }}
                    </div>
                @endif

                <div class='row row-cols-lg-2 row-cols-1 g-4 mt-4 mb-3'>
                    <div class='col-md-7 col-lg-8'>
                        <div class='form-wrapper'>
                            <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                                <fieldset>
                                    <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                    {!! $mail_settings_form !!}

                                    <div class='card panelCard card-default px-lg-4 py-lg-3'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                            <h3>
                                                {{ trans('langThemeSettings') }}
                                            </h3>
                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                                <div class='alert alert-info'>
                                                    {{ trans('langOldThemeInfo') }}
                                                </div>

                                                <div class='alert alert-info'>
                                                    {{ trans('langOldThemeInfoLocation') }}
                                                    <br><strong>{{ $webDir }}/courses/theme_data/</strong>
                                                </div>

                                                <div class='form-group'>
                                                    <div class='col-sm-12'>
                                                        <a class='link-color TextBold' type='button' href='#view_themes_screens' data-bs-toggle='modal'>{{ trans('langViewScreensThemes') }}</a></br></br>
                                                        <p class='mb-3'><span class='control-label-notes'>{{ trans('langActiveTheme') }}:&nbsp;</span>{{ $active_theme }}</p>
                                                        <label for='themeSelection' class='control-label-notes'>{{ trans('langAvailableThemes') }}:</label>
                                                        {!! $theme_selection !!}
                                                    </div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label class='col-sm-12 control-label-notes' for='homepage_intro'>{{ trans('langHomePageIntroText') }}:</label>
                                                    <div class='col-sm-12'>
                                                        {!! $homepage_intro !!}
                                                    </div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <div class='col-12 d-flex justify-content-end'>
                                                        <input aria-label="{{ trans('langContinue') }}" class='btn btn-primary' name='do_upgrade' value='{{ trans('langContinue') }} &raquo;' type='submit'>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>

                    @include('upgrade.upgrade_menu', [ 'upgrade_menu' => upgrade_menu(false) ] )

                </div>

                <div class='modal fade' id='view_themes_screens' tabindex='-1' aria-labelledby='view_themes_screensLabel' aria-hidden='true'>
                    <div class='modal-dialog modal-fullscreen' style='margin-top:0px;'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <div class='modal-title' id='view_themes_screensLabel'>{{ trans('langAvailableThemes') }}</div>
                                <button type='button' class='close' data-bs-dismiss='modal' aria-label='{{ trans('langClose') }}'></button>
                            </div>
                            <div class='modal-body'>
                                <div class='row row-cols-1 g-4'>
                                    {!! $theme_images !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(function () {
            $('#themeSelection').on('click',function(e){
                e.preventDefault();
                var selectedThemeId = $(this).val();
                $.ajax({
                    url: '{{ $_SERVER['SCRIPT_NAME'] }}',
                    data: 'action=preview_theme&selected_theme_id='+selectedThemeId+'&token={{ $_SESSION['csrf_token'] }}',
                    type: 'POST',
                    success: function(response) {
                        if (response == 1) {
                            //window.location.href = '{{ $_SERVER['SCRIPT_NAME'] }}';
                        }
                    },
                    error:function(error){
                        console.log(error)
                    },
                });
            })
            $('#formEmailTransport').change(function() {
                var type = $(this).val();
                if (type == 1) {
                    $('.SMTP-settings').show();
                    $('.Sendmail-settings').hide();
                } else if (type == 2) {
                    $('.SMTP-settings').hide();
                    $('.Sendmail-settings').show();
                } else {
                    $('.SMTP-settings, .Sendmail-settings').hide();
                }
                if (type == 0 && $('#formEmailAnnounce').val() == '') {
                    $('#emailSendWarn').show();
                    $('#formEmailAnnounceGroup').addClass('has-error');
                } else {
                    $('#emailSendWarn').hide();
                    $('#formEmailAnnounceGroup').removeClass('has-error');
                }
            }).change();
            $('#revealPass').mousedown(function () {
                $('#formSMTPPassword').attr('type', 'text');
            }).mouseup(function () {
                $('#formSMTPPassword').attr('type', 'password');
            });
        });
    </script>

@endsection

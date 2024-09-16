@extends('layouts.default')

@push('head_scripts')
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

            $('#auth_selection').change(function () {
                var state = $(this).find(':selected').attr('value') != '1';
                if (state == true) {
                    $('#password').prop({
                        'disabled': true,
                        'class': 'form-control bg-light text-muted'}
                        );
                } else {
                    $('#password').prop({
                        'disabled': false,
                        'class': 'form-control'
                    });
                }
            }).change();

            $('#user_date_expires_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '{{ js_escape($language) }}',
                minuteStep: 10,
                autoclose: true
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

                @if(isset($action_bar))
                    {!! $action_bar !!}
                @else
                    <div class='mt-4'></div>
                @endif

                @include('layouts.partials.show_alert') 

                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>

                     @if ($existing_user)
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] . $params }}' method='post' onsubmit='return validateNodePickerForm();'>
                            <div class='col-12 mb-4'>
                                <div class='card panelCard border-card-left-default px-3 py-2 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                        <h3>{{ getValue('givenname_form', $pn) }} {{ getValue('surname_form', $ps) }}</h3>
                                    </div>
                                    <div class='card-body'>
                                        <div class='row row-cols-1 g-3'>
                                            <div class='col-sm-12'>
                                                <h5>{{ trans('langUsername') }}:</h5> {{ getValue('uname_form', $pu) }}
                                            </div>
                                            <div class='col-sm-12'>
                                                <h5>{{ trans('langEmail') }}:</h5> {{ getValue('email_form', $pe) }}
                                            </div>
                                            <div class='col-sm-12'>
                                                <h5>{{ trans('langPhone') }}:</h5> {{ getValue('phone_form', $pphone) }}
                                            </div>
                                            <div class='col-sm-12'>
                                                <h5>{{ trans('langComments') }}:</h5> {{ $pcom }}
                                            </div>
                                            <div class='col-sm-12'>
                                                <h5>{{ trans('langDate') }}:</h5> {{ $pdate }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group{{ Session::hasError('faculty') ? ' has-error' : '' }} mt-4">
                                        <label for="dialog-set-value" class="col-sm-12 control-label-notes">{{ trans('langFaculty') }}</label>
                                        <div class="col-sm-12">
                                            {!! $tree_html !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="u" value="{{ $u }}">
                            <input type='hidden' name='rid' value='{{ $id }}'>
                            <input type="hidden" name="type" value="{{ $type }}">
                            <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                            </div>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    @else
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] . $params }}' method='post' onsubmit='return validateNodePickerForm();'>
                            <div class="form-group{{ Session::hasError('givenname_form') ? ' has-error' : '' }}">
                                <label for="givenname_form" class="col-sm-12 control-label-notes">{{ trans('langName') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    <input class='form-control' placeholder="{{ trans('langName') }}..." id='givenname_form' type='text' name='givenname_form' value='{{ getValue('givenname_form', $pn) }}'>
                                    @if (Session::hasError('givenname_form'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('givenname_form') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ Session::hasError('surname_form') ? ' has-error' : '' }} mt-4">
                                <label for="surname_form" class="col-sm-12 control-label-notes">{{ trans('langSurname') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='surname_form' type='text' name='surname_form' value='{{ getValue('surname_form', $ps) }}' placeholder="{{ trans('langSurname') }}...">
                                    @if (Session::hasError('surname_form'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('surname_form') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ Session::hasError('uname_form') ? ' has-error' : '' }} mt-4">
                                <label for="uname_form" class="col-sm-12 control-label-notes">{{ trans('langUsername') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='uname_form' type='text' name='uname_form' value='{{ getValue('uname_form', $pu) }}' placeholder="{{ trans('langUsername') }}...">
                                    @if (Session::hasError('uname_form'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('uname_form') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if ($eclass_method_unique)
                                <input type='hidden' name='auth_form' value='1'>
                            @else
                                <div class="form-group{{ Session::hasError('auth_selection') ? ' has-error' : '' }} mt-4">
                                    <label for="auth_selection" class="col-sm-12 control-label-notes">{{ trans('langEditAuthMethod') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                    <div class="col-sm-12">
                                    {!! selection($auth_m, 'auth_form', $auth, "id='auth_selection' class='form-control'") !!}
                                        @if (Session::hasError('auth_selection'))
                                            <span class="help-block Accent-200-cl">{{ Session::getError('auth_selection') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="form-group{{ Session::hasError('password') ? ' has-error' : '' }} mt-4">
                                <label for="password" class="col-sm-12 control-label-notes">{{ trans('langPass') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='password' type='text' name='password' value='{{ getValue('password', choose_password_strength()) }}' autocomplete='off' placeholder="{{ trans('langPass') }}...">
                                    @if (Session::hasError('password'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('password') }}</span>
                                    @endif
                                    <span id='result'></span >
                                </div>
                            </div>
                            <div class="form-group{{ Session::hasError('email_form') ? ' has-error' : '' }} mt-4">
                                <label for="email_form" class="col-sm-12 control-label-notes">{{ trans('langEmail') }}</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='email_form' type='text' name='email_form' value='{{ getValue('email_form', $pe) }}' autocomplete='off' placeholder="{{ trans('langEmail') }} {{ get_config('email_required') ? trans('langCompulsory') : trans('langOptional') }}">
                                    @if (Session::hasError('email_form'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('email_form') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ Session::hasError('verified_mail_form') ? ' has-error' : '' }} mt-4">
                                <label for="verified_mail_form" class="col-sm-12 control-label-notes">{{ trans('langEmailVerified') }}</label>
                                <div class="col-sm-12">
                                    {!! selection($verified_mail_data, "verified_mail_form", $pv, "class='form-control' id='verified_mail_form'") !!}
                                    @if (Session::hasError('verified_mail_form'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('verified_mail_form') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ Session::hasError('phone_form') ? ' has-error' : '' }} mt-4">
                                <label for="phone_form" class="col-sm-12 control-label-notes">{{ trans('langPhone') }}</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='phone_form' type='text' name='phone_form' value='{{ getValue('phone_form', $pphone) }}' placeholder="{{ trans('langPhone') }}...">
                                    @if (Session::hasError('phone_form'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('phone_form') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ Session::hasError('faculty') ? ' has-error' : '' }} mt-4">
                                <label for="dialog-set-value" class="col-sm-12 control-label-notes">{{ trans('langFaculty') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    {!! $tree_html !!}
                                    @if (Session::hasError('faculty'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('faculty') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <div class='col-sm-12 control-label-notes mb-2'> {{ trans('langUserPermissions') }}</div>
                                <div class="radio mb-2 d-flex justify-content-start align-items-center">
                                    <label>
                                        <input type='radio' name='pstatus' value='5' id='norights-option' {!! $user_selected !!}>
                                        {{ trans('langWithNoCourseCreationRights') }}
                                    </label>
                                </div>
                                <div class="radio mb-2 d-flex justify-content-start align-items-center">
                                    <label>
                                        <input type="radio" name="pstatus" value="1" id="rights-option" {!! $prof_selected !!}>
                                        {{ trans('langWithCourseCreationRights') }}
                                    </label>
                                </div>
                                <div class='checkbox mb-2'>
                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                        <input type='checkbox' name='enable_course_registration' value='1' checked>
                                        <span class='checkmark'></span>{{ trans('langInfoEnableCourseRegistration') }}
                                    </label>
                                </div>
                            </div>

                            @if ($pstatus == USER_STUDENT)
                                <!--only for students-->
                                <div class="form-group{{ Session::hasError('am_form') ? ' has-error' : '' }} mt-4">
                                    <label for="am_form" class="col-sm-12 control-label-notes">{{ trans('langAm') }}</label>
                                    <div class="col-sm-12">
                                        <input class='form-control' id='am_form' type='text' name='am_form' value='{{ getValue('am_form', $pam) }}' placeholder="{{ get_config('am_required') ? trans('langCompulsory') : trans('langOptional') }}">
                                        @if (Session::hasError('am_form'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('am_form') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if (get_config('block_duration_account'))
                                <div class='input-append date form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes'>{{ trans('langExpirationDate') }}</div>
                                    <div class='col-sm-12'>
                                        <span class='help-block'>{{ trans('lang_message_block_duration_account') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class='input-append date form-group mt-4'>
                                    <label for='user_date_expires_at' class='col-sm-12 control-label-notes'>{{ trans('langExpirationDate') }}</label>
                                    <div class='col-sm-12'>
                                        <div class='input-group'>
                                            <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                            <input class='form-control mt-0 border-start-0' id='user_date_expires_at' name='user_date_expires_at' type='text' value='{{ $expirationDatevalue }}'>

                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group{{ Session::hasError('language_form') ? ' has-error' : '' }} mt-4">
                                <label for="language_form" class="col-sm-12 control-label-notes">{{ trans('langLanguage') }}</label>
                                <div class="col-sm-12">
                                    {!! lang_select_options('language_form', "class='form-control' id='language_form'", Session::has('language_form') ? Session::get('language_form'): $language) !!}
                                    @if (Session::hasError('language_form'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('language_form') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if ($ext_uid)
                                <div class="form-group mt-4">
                                    <label for="provider" class="col-sm-12 control-label-notes">{{ trans('langProviderConnectWith') }}</label>
                                    <div class="col-sm-12">
                                        <p class='form-control-static'>
                                            <img src='{{ $themeimg }}/{{ $auth_ids[$ext_uid->auth_id] . '.png' }}'>&nbsp;
                                            {{ $authFullName[$ext_uid->auth_id] }}
                                            <br>
                                            <small>{{ trans('langProviderConnectWithTooltip') }}</small>
                                        </p>
                                    </div>
                                </div>
                            @endif
                            @if (isset($_GET['id']))

                                <div class="form-group mt-4">
                                    <div class="col-sm-12 control-label-notes">{{ trans('langComments') }}</div>
                                    <div class="col-sm-12">
                                        <p class='form-control-static'>
                                            {{ $pcom }}
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <div class="col-sm-12 control-label-notes">{{ trans('langDate') }}</div>
                                    <div class="col-sm-12">
                                        <p class='form-control-static'>
                                            {{ $pdate }}
                                        </p>
                                    </div>
                                </div>
                                <input type='hidden' name='rid' value='{{ $id }}'>
                            @endif

                            <div class='mt-3'></div>
                            <div class='row'>{!! render_profile_fields_form($cpf_context, true) !!}</div>
                            {!! showSecondFactorChallenge() !!}

                            <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                            </div>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                 @endif
                </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
            </div>
            </div>
        </div>
    </div>
@endsection

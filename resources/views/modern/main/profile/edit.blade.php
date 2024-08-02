@extends('layouts.default')

@push('head_scripts')
<script type='text/javascript'>
    var urlAppend = '{{ js_escape($urlAppend) }}',
        lang = {
            addPicture: '{{ js_escape(trans('langAddPicture')) }}',
            confirmDelete: '{{ js_escape(trans('langConfirmDelete')) }}'
        };
    $(profile_init);
</script>
@endpush

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            {!! $action_bar !!}

            @include('layouts.partials.show_alert') 

            <div class='col-lg-6 col-12'>
            <div class='form-wrapper form-edit border-0 px-0'>
                <form class='form-horizontal' role='form' method='post' enctype='multipart/form-data' action='{{ $sec }}' onsubmit='return validateNodePickerForm();'>
                    
                        <div class='form-group'>
                            <label for='givenname_form' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                            <div class='col-sm-12'>
                            @if ($allow_name_change)
                                <input type='text' class='form-control' name='givenname_form' id='givenname_form' value='{{ $givenname_form }}'>
                            @else {
                                <p class='form-control-static'>{{$givenname_form}}</p>
                                <input type='hidden' name='givenname_form' value='{{ $givenname_form }}' id='givenname_form'>
                            @endif
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='surname_form' class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}</label>
                            <div class='col-sm-12'>
                            @if ($allow_name_change)
                                <input type='text' class='form-control' name='surname_form' id='surname_form' value='{{ $surname_form }}'>
                            @else
                                <p class='form-control-static'>{{ $surname_form }}</p>
                                <input type='hidden' name='surname_form' value='{{ $surname_form }}' id='surname_form'>
                            @endif
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='username_form' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                            <div class='col-sm-12'>
                            @if ($allow_username_change)
                                <input class='form-control' class='form-control' type='text' name='username_form' id='username_form' value='{{ $username_form }}'>
                            @else
                                [{{ $auth_text }}]
                                <p class='form-control-static'>{{ $username_form }}</p>
                            @endif
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='email_form' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }}</label>
                            <div class='row'>
                                <div class='col-sm-12'>
                                    @if ($allow_email_change)
                                        <input class='form-control' type='text' name='email_form' id='email_form' value='{{ $email_form }}'>
                                    @else
                                        <p class='form-control-static'>{{ $email_form }}</p>
                                        <input type='hidden' name='am_form' value='{{ $email_form }}' id='email_form'>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='am_form' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                            <div class='row'>
                                <div class='col-sm-12'>
                                    @if ($allow_am_change)
                                        <input type='text' class='form-control' name='am_form' id='am_form' value='{{ $am_form }}'>
                                    @else
                                        <p class='form-control-static'>{{ $am_form }}</p>
                                        <input type='hidden' name='am_form' value='{{ $am_form }}' id='am_form'>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='phone_form' class='col-sm-12 control-label-notes'>{{ trans('langPhone') }}</label>
                            <div class='row'>
                                <div class='col-sm-12'>
                                    <input type='text' class='form-control' name='phone_form' id='phone_form' value='{{$phone_form }}'>
                                </div>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langEmailFromCourses') }}</div>
                            <div class='col-sm-12'>
                                <div class='radio'>
                                    <label>
                                        <input type='radio' name='subscribe' value='yes' {{ $selectedyes }}> {{ trans('langYes') }}
                                    </label>
                                </div>
                                <div class='radio'>
                                    <label>
                                        <input type='radio' name='subscribe' value='no' {{ $selectedno }}> {{ trans('langNo') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langViewShow') }}</div>
                            <div class='help-block mb-2'>(*){{ trans('langShowSettingsInfo') }}</div>
                            <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label class='label-container'>
                                        <input type='checkbox' name='email_public' value='1' {!! $email_public_selected  !!}> 
                                        <span class='checkmark'></span>
                                        {{ trans('langEmail') }}
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label class='label-container'>
                                        <input type='checkbox' name='am_public' value='1' {!! $am_public_selected !!}> 
                                        <span class='checkmark'></span>
                                        {{ trans('langAm') }}
                                    </label class='label-container'>
                                </div>
                                <div class='checkbox'>
                                    <label class='label-container'>
                                        <input type='checkbox' name='phone_public' value='1' {!! $phone_public_selected !!}> 
                                        <span class='checkmark'></span>
                                        {{ trans('langPhone') }}
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label class='label-container'>
                                        <input type='checkbox' name='pic_public' value='1' {!! $pic_public_selected !!}> 
                                        <span class='checkmark'></span>
                                        {{ trans('langProfileImage') }}
                                    </label>
                                </div>
                            </div>
                            
                        </div>

                        @if (get_config('email_verification_required'))
                            <div class='form-group {{ $messageClass }} mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langVerifiedMail') }}</label>
                                <div class='col-sm-12 form-control-static'>{!! $message !!}</div>
                            </div>
                        @endif

                        @if (!get_config('restrict_owndep'))
                            <div class='form-group mt-4'>
                                <label for='dialog-set-value' class='col-sm-12 control-label-notes mb-2'>{{ trans('langFaculty') }}</label>
                                <div class='col-sm-12 form-control-static'>
                                        {!! $html !!}
                                </div>
                            </div>
                        @endif

                        <div class='form-group mt-4'>
                            <label for='selected_lang' class='col-sm-12 control-label-notes mb-2'>{{ trans('langLanguage') }}</label>
                            <div class='col-sm-12'>{!! lang_select_options('userLanguage', "class='form-control' id='selected_lang'") !!}</div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='user_image_selected' class='col-sm-12 control-label-notes mb-2'>{{ $message_pic }}</label>
                            <div class='col-sm-12'>
                                <span>
                                    {!! $picture !!} {!! $delete !!}
                                </span>
                                {!! fileSizeHidenInput() !!}
                                <input type='file' name='userimage' size='30' id='user_image_selected'>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='desc_form' class='col-sm-12 control-label-notes mb-2'>{{ trans('langProfileAboutMe') }}</label>
                            <div class='col-sm-12'>{!! $info_text_area !!}</div>
                        </div>

                        {!! render_profile_fields_form(array('origin' => 'edit_profile')) !!}

                        @if (count($allProviders) > 0)

                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langProviderConnectWith') }}</label>
                                <div class='col-sm-12'>
                                    <div class='row'>
                                    @foreach ($allProviders as $provider)
                                        <div class='col-2 text-center'>
                                            <img src='$themeimg/{{ strtolower($provider) }}.png' alt="{{ trans('langLoginVia') }}"><br>{{ $provider }}<br>
                                    @if ($userProviders[strtolower($provider)])
                                        <img src='{{ $themeimg }}/tick.png' alt='{{ trans('langProviderConnectWith') }} {{ $provider }}'>
                                        <a href='{{ $sec }}?action=delete&provider={{ $provider }}'>{{ trans('langProviderDeleteConnection') }}</a>
                                    @else
                                        <a href='{{ $sec }}?action=connect&provider={{ $provider }}'>{{ trans('langProviderConnect') }}</a>
                                    @endif
                                        </div>
                                    @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4"></div>
                        {{ $SecFactorProfile }}
                        <div class="mt-3"></div>
                        {{ $SecFactorChallenge }}
                        <div class='col-12 mt-5 d-flex justify-content-end align-items-center gap-2'>
                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                            <a href='display_profile.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                        </div>

                
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

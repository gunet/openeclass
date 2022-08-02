@extends('layouts.default')

@push('head_scripts')
<script type='text/javascript'>
    var lang =
        {
            addPicture: '" . js_escape({{ trans('langAddPicture') }}) . "',
            confirmDelete: '" . js_escape({{ trans('langConfirmDelete') }}) . "'
        };
    $(profile_init);
</script>
@endpush

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                        {!! Session::get('message') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    <div class='col-12'>
                    <div class='form-wrapper shadow-sm p-3 rounded'>
                        <form class='form-horizontal' role='form' method='post' enctype='multipart/form-data' action='{{ $sec }}' onsubmit='return validateNodePickerForm();'>
                            <fieldset>
                                <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='givenname_form' class='col-sm-6 control-label-notes'>{{ trans('langName') }}:</label>
                                    <div class='col-sm-12'>
                                    @if ($allow_name_change)
                                        <input type='text' class='form-control' name='givenname_form' id='givenname_form' value='{{ $givenname_form }}'>
                                    @else {
                                        <p class='form-control-static'>{{$givenname_form}}</p>
                                        <input type='hidden' name='givenname_form' value='{{ $givenname_form }}'>
                                    @endif
                                    </div>
                                </div>
                                <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='surname_form' class='col-sm-6 control-label-notes'>{{ trans('langSurname') }}:</label>
                                    <div class='col-sm-12'>
                                    @if ($allow_name_change)
                                        <input type='text' class='form-control' name='surname_form' id='surname_form' value='{{ $surname_form }}'>
                                    @else
                                        <p class='form-control-static'>{{ $surname_form }}</p>
                                        <input type='hidden' name='surname_form' value='{{ $surname_form }}'>
                                    @endif
                                    </div>
                                </div>
                                <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='username_form' class='col-sm-6 control-label-notes'>{{ trans('langUsername') }}:</label>
                                    <div class='col-sm-12'>
                                    @if ($allow_username_change)
                                        <input class='form-control' class='form-control' type='text' name='username_form' id='username_form' value='{{ $username_form }}'>
                                    @else
                                        [{{ $auth_text }}]
                                        <p class='form-control-static'>{{ $username_form }}</p>
                                    @endif
                                    </div>
                                </div>
                                <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='email_form' class='col-sm-6 control-label-notes'>{{ trans('langEmail') }}:</label>
                                    <div class='col-sm-5'>
                                        <input class='form-control' type='text' name='email_form' id='email_form' value='{{ $email_form }}'>
                                    </div>
                                    <div class='col-sm-5'>
                                        {!! selection($access_options, 'email_public', $email_public, "class='form-control'") !!}
                                    </div>
                                </div>
                                <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='am_form' class='col-sm-6 control-label-notes'>{{ trans('langAm') }}:</label>
                                    <div class='col-sm-5'>
                                        <input type='text' class='form-control' name='am_form' id='am_form' value='{{ $am_form }}'>
                                    </div>
                                    <div class='col-sm-5'>
                                        {!! selection($access_options, 'am_public', $am_public, "class='form-control'") !!}
                                    </div>
                                </div>
                                <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='phone_form' class='col-sm-6 control-label-notes'>{{ trans('langPhone') }}</label>
                                    <div class='col-sm-5'>
                                        <input type='text' class='form-control' name='phone_form' id='phone_form' value='{{$phone_form }}'>
                                    </div>
                                    <div class='col-sm-5'>
                                        {!! selection($access_options, 'phone_public', $phone_public, "class='form-control'") !!}
                                    </div>
                                </div>
                                <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='emailfromcourses' class='col-sm-6 control-label-notes'>{{ trans('langEmailFromCourses') }}:</label>
                                    <div class='col-sm-10'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='subscribe' value='yes' {{ $selectedyes }}>{{ trans('langYes') }}
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='subscribe' value='no' {{ $selectedno }}>{{ trans('langNo') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @if (get_config('email_verification_required'))
                            <div class="row p-2"></div>
                                <div class='form-group {{ $messageClass }}'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langVerifiedMail') }}</label>
                                    <div class='col-sm-12 form-control-static'>{!! $message !!}</div>
                                </div>
                            @endif
                            @if (!get_config('restrict_owndep'))
                            <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='faculty' class='col-sm-6 control-label-notes'>{{ trans('langFaculty') }}:</label>
                                    <div class='col-sm-12 form-control-static'>
                                            {!! $html !!}
                                    </div>
                                </div>
                            @endif

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='language' class='col-sm-6 control-label-notes'>{{ trans('langLanguage') }}:</label>
                                <div class='col-sm-12'>{!! lang_select_options('userLanguage', "class='form-control'") !!}</div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='picture' class='col-sm-6 control-label-notes'>{{ $message_pic }}</label>
                                <div class='col-sm-12'>
                                    <span>
                                        {!! $picture !!} {!! $delete !!}
                                    </span>
                                    {!! fileSizeHidenInput() !!}
                                    <input type='file' name='userimage' size='30'>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='desription' class='col-sm-6 control-label-notes'>{{ trans('langProfileAboutMe') }}:</label>
                                <div class='col-sm-12'>{!! $info_text_area !!}</div>
                            </div>
                            {!! render_profile_fields_form(array('origin' => 'edit_profile')) !!}
                            @if (count($allProviders) > 0)
                                <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langProviderConnectWith') }}:</label>
                                    <div class='col-sm-12'>
                                        <div class='row'>";
                                        @foreach ($allProviders as $provider => $settings)
                                            $lcProvider = strtolower($provider);
                                            <div class='col-xs-2 text-center'>
                                                <img src='$themeimg/{{ strtolower($provider) }}.png' alt="{{ trans('langLoginVia') }}"><br>{{ $provider }}<br>";
                                        @if ($userProviders[strtolower($provider)])
                                            <img src='{{ $themeimg }}/tick.png' alt='{{ trans('langProviderConnectWith') }} {{ $provider }}'>
                                            <a href='{{ $data[sec] }}?action=delete&provider={{ $provider }}'>{{ trans('langProviderDeleteConnection') }}</a>";
                                        @else
                                            <a href='{{ $data[sec] }}?action=connect&provider={{ $provider }}'>{{ trans('langProviderConnect') }}</a>
                                        @endif
                                            </div>
                                        @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row p-2"></div>
                                {{ $SecFactorProfile }}
                                <div class="row p-2"></div>
                                {{ $SecFactorChallenge }}
                                <div class='col-sm-offset-2 col-sm-10'>
                                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                    <a href='display_profile.php' class='btn btn-secondary'>{{ trans('langCancel') }}</a>
                                </div>
                        </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

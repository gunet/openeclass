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

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit rounded'>
                        <form class='form-horizontal' role='form' method='post' enctype='multipart/form-data' action='{{ $sec }}' onsubmit='return validateNodePickerForm();'>
                            <fieldset>
                                <div class='form-group'>
                                    <label for='givenname_form' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                    <div class='col-sm-12'>
                                    @if ($allow_name_change)
                                        <input type='text' class='form-control' name='givenname_form' id='givenname_form' value='{{ $givenname_form }}'>
                                    @else {
                                        <p class='form-control-static'>{{$givenname_form}}</p>
                                        <input type='hidden' name='givenname_form' value='{{ $givenname_form }}'>
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
                                        <input type='hidden' name='surname_form' value='{{ $surname_form }}'>
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
                                        <div class='col-sm-6'>
                                            <input class='form-control' type='text' name='email_form' id='email_form' value='{{ $email_form }}'>
                                        </div>
                                        <div class='col-sm-6 mt-md-0 mt-2'>
                                            {!! selection($access_options, 'email_public', $email_public, "class='form-control'") !!}
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='am_form' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                                    <div class='row'>
                                        <div class='col-sm-6'>
                                            <input type='text' class='form-control' name='am_form' id='am_form' value='{{ $am_form }}'>
                                        </div>
                                        <div class='col-sm-6 mt-md-0 mt-2'>
                                            {!! selection($access_options, 'am_public', $am_public, "class='form-control'") !!}
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='phone_form' class='col-sm-12 control-label-notes'>{{ trans('langPhone') }}</label>
                                    <div class='row'>
                                        <div class='col-sm-6'>
                                            <input type='text' class='form-control' name='phone_form' id='phone_form' value='{{$phone_form }}'>
                                        </div>
                                        <div class='col-sm-6 mt-md-0 mt-2'>
                                            {!! selection($access_options, 'phone_public', $phone_public, "class='form-control'") !!}
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label for='emailfromcourses' class='col-sm-12 control-label-notes mb-1'>{{ trans('langEmailFromCourses') }}</label>
                                    <div class='col-sm-12 d-inline-flex'>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' name='subscribe' value='yes' {{ $selectedyes }}> {{ trans('langYes') }}
                                            </label>
                                        </div>
                                        <div class='radio ms-4'>
                                            <label>
                                                <input type='radio' name='subscribe' value='no' {{ $selectedno }}> {{ trans('langNo') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @if (get_config('email_verification_required'))

                                <div class='form-group {{ $messageClass }} mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langVerifiedMail') }}</label>
                                    <div class='col-sm-12 form-control-static'>{!! $message !!}</div>
                                </div>
                            @endif
                            @if (!get_config('restrict_owndep'))

                                <div class='form-group mt-4'>
                                    <label for='faculty' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                    <div class='col-sm-12 form-control-static'>
                                            {!! $html !!}
                                    </div>
                                </div>
                            @endif



                            <div class='form-group mt-4'>
                                <label for='language' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                <div class='col-sm-12'>{!! lang_select_options('userLanguage', "class='form-control'") !!}</div>
                            </div>



                            <div class='form-group mt-4'>
                                <label for='picture' class='col-sm-12 control-label-notes'>{{ $message_pic }}</label>
                                <div class='col-sm-12'>
                                    <span>
                                        {!! $picture !!} {!! $delete !!}
                                    </span>
                                    {!! fileSizeHidenInput() !!}
                                    <input type='file' name='userimage' size='30'>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='desription' class='col-sm-12 control-label-notes'>{{ trans('langProfileAboutMe') }}</label>
                                <div class='col-sm-12'>{!! $info_text_area !!}</div>
                            </div>
                            {!! render_profile_fields_form(array('origin' => 'edit_profile')) !!}
                            @if (count($allProviders) > 0)

                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langProviderConnectWith') }}</label>
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
                                <div class='col-12 mt-5 d-flex justify-content-center align-items-center'>
                                   
                                     
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                       
                                        
                                            <a href='display_profile.php' class='btn cancelAdminBtn ms-1'>{{ trans('langCancel') }}</a>
                                        

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

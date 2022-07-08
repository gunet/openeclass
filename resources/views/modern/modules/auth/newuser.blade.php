@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @if($course_code)
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    @else
                        @include('layouts.partials.sidebarAdmin')
                    @endif 
                </div>
            </div>


            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @if($course_code)
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            @else
                                @include('layouts.partials.sidebarAdmin')
                            @endif
                        </div>
                    </div>

                    {!! $action_bar !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    @if (!$user_registration or $eclass_stud_reg != 2)
                        <div class='alert alert-info'>
                            {{ trans('langStudentCannotRegister') }}
                        </div>
                    @else
                        @if (isset($_POST['submit']))
                            @if ($vmail)
                                <div class='alert alert-info'> {{ trans('langMailVerificationSuccess') }} {{ trans('langMailVerificationSuccess2') }} <br><br><small> {{ trans('langMailVerificationNote') }} </small> <br><br>{{ trans('langClick') }} <a href='{{ $urlServer }}' class='mainpage'>{{ trans('langHere') }}</a> {{ trans('langBackPage') }}</div>
                            @else
                                <div class='alert alert-success'>
                                    <p>{{ $user_msg }}</p>
                                    <p>{{ trans('langClick') }} <a href='../../'>{{ trans('langHere') }}</a> {{ trans('langPersonalSettingsMore') }}
                                    <ul>
                                        <li>{{ trans('langPersonalSettingsMore1') }}</li>
                                        <li>{{ trans('langPersonalSettingsMore2') }}</li>
                                    </ul>
                                    </p>
                                </div>
                            @endif
                        @else
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='form-wrapper shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <form class='form-horizontal' role='form' action='newuser.php' method='post' onsubmit='return validateNodePickerForm();'>
                            <fieldset>
                            <div class='form-group'>
                                <label for='Name' class='col-sm-6 control-label-notes'>{{ trans('langName') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='givenname_form' size='30' maxlength='100' value = '{{ $user_data_firstname }}'  placeholder='{{ trans('langName') }}'>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='SurName' class='col-sm-6 control-label-notes'>{{ trans('langSurname') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='surname_form' size='30' maxlength='100' value = '{{ $user_data_lastname }}' placeholder='{{ trans('langSurname') }}'>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='UserName' class='col-sm-6 control-label-notes'>{{ trans('langUsername') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='uname' value = '{{ $user_data_displayName }}' accept="" size='30' maxlength='100' autocomplete='off' placeholder='{{ trans('langUserNotice') }}'>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='UserPass' class='col-sm-6 control-label-notes'>{{ trans('langPass') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='password' name='password1' size='30' maxlength='30' autocomplete='off' id='password' placeholder='{{ trans('langUserNotice') }}'><span id='result'></span>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                            <label for='UserPass2' class='col-sm-6 control-label-notes'>{{ trans('langConfirmation') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='password' name='password' size='30' maxlength='30' autocomplete='off'/>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='UserEmail' class='col-sm-6 control-label-notes'>{{ trans('langEmail') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='email' size='30' maxlength='100' value = '{{ $user_data_email }}' placeholder='{{ trans('email_message') }}'>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='UserAm' class='col-sm-6 control-label-notes'>{{ trans('langAm') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='am' size='20' maxlength='20' value = '{{ $user_data_am }}' placeholder='{{trans ('am_message') }}'>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='UserPhone' class='col-sm-6 control-label-notes'>{{ trans('langPhone') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='phone' size='20' maxlength='20' value = '{{ $user_data_phone }}' placeholder='{{ trans('langOptional') }}'>
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                            <label for='UserFac' class='col-sm-6 control-label-notes'>{{ trans('langFaculty') }}:</label>
                                <div class='col-sm-12'>
                                    {!! $buildusernode !!}
                                </div>
                            </div>

                            <div class="row p-2"></div>

                            <div class='form-group'>
                            <label for='UserLang' class='col-sm-6 control-label-notes'>{{ trans('langLanguage') }}:</label>
                                <div class='col-sm-12'>
                                    {!! $lang_select_options !!}
                                </div>
                            </div>
                            @if ($display_captcha)
                            <div class="row p-2"></div>
                                <div class='form-group'>
                                    <label for='captcha_code' class='col-sm-6 control-label-notes'>{{ trans('langCaptcha') }}:</label>
                                    <div class='col-sm-12'>{!! $captcha !!}</div>
                                </div>
                            @endif
                            <!-- add custom profile fields -->
                            <div class="row p-2"></div>
                            {!! $render_profile_fields_form !!}

                            <!-- check if provider_id from an authenticated user and a valid provider name are set so as to show the relevant form -->
                            @if(!empty($provider_name) && !empty($provider_id))
                            <div class="row p-2"></div>
                                <div class='form-group'>
                                <label for='UserLang' class='col-sm-6 control-label-notes'>{{ trans('langProviderConnectWith') }}:</label>
                                <div class='col-sm-12'><p class='form-control-static'>
                                    <img src='$themeimg/" . q($provider_name) . ".png' alt='" . q($provider_name) . "'>&nbsp;" . q(ucfirst($provider_name)) . "<br /><small>{{ trans('langProviderConnectWithTooltip') }}</small></p>
                                </div>

                                <div class="row p-2"></div>

                                <div class='col-sm-offset-2 col-sm-10'>
                                    <input type='hidden' name='provider' value= ' {{ $provider_name }}'>
                                    <input type='hidden' name='provider_id' value=' {{ $provider_id }}'>
                                </div>
                                </div>
                            @endif

                            <div class="row p-2"></div>
                            
                            <div class='form-group'>
                                <div class='col-sm-offset-2 col-sm-10'>
                                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                                </div>
                            </div>
                        </fieldset>
                        </form>
                        </div></div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

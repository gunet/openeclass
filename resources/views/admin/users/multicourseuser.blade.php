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
                       <div class='alert alert-info'>
                            <i class='fa-solid fa-circle-info fa-lg'></i><span>
                            {{ trans('langAskManyUsersToCourses') }}</span>
                        </div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                        
                        <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                            <fieldset>
                                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                <label for='user_Id' class='control-label-notes'>{{ trans('langUsersData') }}</label>
                                <div class='form-group'>
                                    <div class='radio'>
                                        <label>
                                            <input type='radio' name='type' value='uname' checked>{{ trans('langUsername') }}
                                        </label>
                                    </div>
                                    <div class='col-sm-12'>{!! text_area('user_info', 10, 30, '', 'id="user_Id"') !!}</div>
                                </div>
                            </fieldset>
                            <div class='mt-4'></div>
                            <fieldset>
                                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                <label for='courses_Id' class='control-label-notes'>{{ trans('langCourseCodes') }}</label>
                                <div class='form-group'>
                                    <div class='col-sm-12'>{!! text_area('courses_codes', 10, 30, '', 'id="courses_Id"') !!}</div>
                                </div>
                                {!! showSecondFactorChallenge() !!}
                                <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
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
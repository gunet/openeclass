@extends('layouts.default')

@section('content')
    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')

                        @if (!get_config('enable_search'))
                            <div class='alert alert-info'>
                                <i class='fa-solid fa-circle-info fa-lg'></i>
                                <span>{{ trans('langSearchDisabled') }}</span>
                            </div>
                        @else
                            <div class='d-lg-flex gap-4 mt-4'>
                                <div class='flex-grow-1'>
                                    <div class='form-wrapper form-edit rounded'>
                                        <form class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                            <fieldset>
                                                <legend class='mb-0' aria-label='$langForm'></legend>
                                                <div class='row form-group'>
                                                    <label for='search_terms_id' class='col-12 control-label-notes'>{{ trans('langOR') }}</label>
                                                    <div class='col-12'>
                                                        <input name='search_terms' type='text' class='form-control' id='search_terms_id'>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-4'>
                                                    <div class='col-sm-6 control-label-notes mb-2'>{{ trans('langSearchIn') }}</div>
                                                    <div class='col-12'>
                                                        <div class='row'>
                                                            <div class='col-6 col-sm-4'>
                                                                <div class='checkbox'>
                                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                        <input type='checkbox' name='announcements' checked>
                                                                        <span class='checkmark'></span>
                                                                        {{ trans('langAnnouncements') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class='col-6 col-sm-4'>
                                                                <div class='checkbox'>
                                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                        <input type='checkbox' name='agenda' checked>
                                                                        <span class='checkmark'></span>
                                                                        {{ trans('langAgenda') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class='col-6 col-sm-4'>
                                                                <div class='checkbox'>
                                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                        <input type='checkbox' name='course_units' checked>
                                                                        <span class='checkmark'></span>
                                                                        {{ trans('langCourseUnits') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class='col-6 col-sm-4'>
                                                                <div class='checkbox'>
                                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                        <input type='checkbox' name='documents' checked>
                                                                        <span class='checkmark'></span>
                                                                        {{ trans('langDoc') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class='col-6 col-sm-4'>
                                                                <div class='checkbox'>
                                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                        <input type='checkbox' name='forums' checked>
                                                                        <span class='checkmark'></span>
                                                                        {{ trans('langForums') }}
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            @if(!$is_collaborative_course)
                                                                <div class='col-6 col-sm-4'>
                                                                    <div class='checkbox'>
                                                                        <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                            <input type='checkbox' name='exercises' checked>
                                                                            <span class='checkmark'></span>
                                                                            {{ trans('langExercices') }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <div class='col-6 col-sm-4'>
                                                                <div class='checkbox'>
                                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                        <input type='checkbox' name='video' checked>
                                                                        <span class='checkmark'></span>
                                                                        {{ trans('langVideo') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class='col-6 col-sm-4'>
                                                                <div class='checkbox'>
                                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                        <input type='checkbox' name='links' checked>
                                                                        <span class='checkmark'></span>
                                                                        {{ trans('langLinks') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-5'>
                                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langDoSearch') }}'>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </form>
                                    </div></div><div class='d-none d-lg-block'>
                                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt='{{ trans('langImgFormsDes') }}'>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection



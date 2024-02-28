@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">
                <div class="row">
                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langAskUser') }}</span></div>
                            <div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}'>
                                    <div class='form-group'>
                                        <label for='surname' class='col-sm-6 control-label-notes'>{{ trans('langSurname') }}</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' id='surname' type='text' name='search_surname' value='{!! q($search_surname) !!}' placeholder='{{ trans('langSurname') }}'>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='name' class='col-sm-6 control-label-notes'>{{ trans('langName') }}</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' id='name' type='text' name='search_givenname' value='{!! q($search_givenname) !!}' placeholder='{{ trans('langName') }}'>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='username' class='col-sm-6 control-label-notes'>{{ trans('langUsername') }}</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' id='username' type='text' name='search_username' value='{!! q($search_username)  !!}' placeholder='{{ trans('langUsername') }}'>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='am' class='col-sm-6 control-label-notes'>{{ trans('langAm') }}</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' id='am' type='text' name='search_am' value='{!! q($search_am) !!} ' placeholder='{{ trans('langAm') }}'></div>
                                    </div>
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='search' value='{{ trans('langSearch') }}'>
                                            <a class='btn cancelAdminBtn ms-1' href='index.php?course={{ $course_code }}'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>
                    </div>
                </div>

                {!! $results !!}

            </div>
        </div>
    </div>
</div>
@endsection

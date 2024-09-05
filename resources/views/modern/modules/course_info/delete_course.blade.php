@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    @include('layouts.partials.show_alert')

                    <div class="col-12">
                        <div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>
                            {{ trans('langByDel_A') }}
                                <strong> {{ $currentCourseName }} {{ ($course_code) }} ;</strong>
                            </span>
                        </div>
                    </div>

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class="flex-grow-1">
                            <div class='form-wrapper form-edit border-0 px-0'>

                                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}'>
                                    <div class="col-12">
                                        <div class='alert alert-warning'><i class='fa-solid fa-circle-xmark fa-lg'></i>
                                            {{ trans('langByDel') }}
                                        </div>
                                    </div>

                                    {{ showSecondFactorChallenge() }}

                                    <div class='form-group mt-4'>
                                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                            <input aria-label="{{ trans('langDelete') }}" class='btn deleteAdminBtn' type='submit' name='delete' value='{{ trans('langDelete') }}'>
                                            <a aria-label="{{ trans('langCancel') }}" href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#courses-select').select2({
                minimumInputLength: 2,
                tags: true,
                ajax: {
                    url: '{{ $urlServer }}modules/course_prerequisites/coursefeed.php',
                    dataType: 'json'
                }
            });
        });
    </script>
@endpush


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

                        <div class='d-lg-flex gap-4 mt-5'>
                            <div class='flex-grow-1'>
                                <div class='form-wrapper form-edit rounded'>
                                    <div class='alert alert-info'>
                                        <i class='fa-solid fa-circle-info fa-lg'></i>
                                        <span>{{ trans('langNewCoursePrerequisiteHelp2') }}</span>
                                    </div>
                                    <form role='form' class='form-horizontal' method='post' action='index.php?course={{ $course_code }}'>
                                        <input type='hidden' name='addcommit' value='1'>
                                        <fieldset>
                                            <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                            <div class='form-group'>
                                                <label for='courses-select' class='col-sm-12 control-label-notes'>{{ trans('langCourse') }}:</label>
                                                <div class='col-sm-12'>
                                                    <select id='courses-select' class='form-select' name='prerequisite_course'></select>
                                                </div>
                                            </div>
                                            <div class='form-group mt-4'>
                                                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                                    <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                                </div>
                                            </div>
                                        </fieldset>
                                    {!! generate_csrf_token_form_field() !!}
                                    </form>
                                </div>
                            </div>
                            <div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{{ get_form_image() }}' alt='{{ trans('langImgFormsDes') }}'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection




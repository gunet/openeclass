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
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar', ['is_editor' => $is_editor])
                            </div>
                        </div>

                        @include('layouts.partials.show_alert')

                        @include('layouts.partials.legend_view')

                        <div class='d-lg-flex gap-4 mt-4'>
                            <div class='flex-grow-1'>
                                <div class='col-12'>
                                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langImportH5P') }}</span></div>
                                </div>

                                <div class='form-wrapper form-edit border-0 px-0'>
                                    <form class='form-horizontal' role='form' action='save.php?course={{ $course_code }}' method='post' enctype='multipart/form-data'>
                                        <div class='form-group'>
                                            <label for='userFile' class='col-sm-6 control-label-notes'>{{ trans('langPathUploadFile') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='file' id='userFile' name='userFile'>
                                                <div class='infotext col-12 margin-bottom-fat TextBold Neutral-900-cl mt-4'>
                                                    {{ trans('langMaxFileSize') }} {{ ini_get('upload_max_filesize') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <div class='col-12 d-flex justify-content-start align-items-start'>
                                                <button class='btn submitAdminBtn' type='submit'>{{ trans('langUpload') }}</button>
                                            </div>
                                        </div>
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

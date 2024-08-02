
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

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                            <form class='form-horizontal' role='form' method='post' action='editdesc.php?course={{$course_code}}' enctype='multipart/form-data'>
                                <div class='row form-group'>
                                    <label for='description' class='col-12 control-label-notes'>{{ trans('langCourseLayout') }}</label>
                                    <div class='col-12'>
                                        {!! $selection !!}
                                    </div>
                                </div>
                                @if($layout == 1)
                                    <div id='image_field' class='form-group mt-4'>
                                @else
                                    <div id='image_field' class='form-group hidden'>
                                @endif

                                    <label for='course_image' class='col-12 control-label-notes'>{{ trans('langCourseImage') }}</label>
                                    <div class='col-sm-12'>
                                        @if (isset($course_image))
                                            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap gap-2'>
                                                <img style="max-height:100px;max-width:150px;" src='{{ $urlAppend }}courses/{{ $course_code }}/image/{{ $course_image }}' alt="{{ trans('langCourseImage') }}"> &nbsp;&nbsp;
                                                <a class='btn deleteAdminBtn' href='{{$_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&delete_image=true&{!! generate_csrf_token_link_parameter() !!}'>
                                                    {{ trans('langDelete') }}
                                                </a>
                                            </div>
                                            <input type='hidden' name='course_image' value='{{ $course_image }}'>
                                        @else
                                            {!! $enableCheckFileSize !!}
                                            {!! $fileSizeHidenInput !!}
                                            <ul class='nav nav-tabs' id='nav-tab' role='tablist'>
                                                <li class='nav-item' role='presentation'>
                                                    <button class='nav-link active' id='tabs-upload-tab' data-bs-toggle='tab' data-bs-target='#tabs-upload' type='button' role='tab' aria-controls='tabs-upload' aria-selected='true'>{{ trans('langUpload') }}</button>
                                                </li>
                                                <li class='nav-item' role='presentation'>
                                                    <button class='nav-link' id='tabs-selectImage-tab' data-bs-toggle='tab' data-bs-target='#tabs-selectImage' type='button' role='tab' aria-controls='tabs-selectImage' aria-selected='false'>{{ trans('langAddPicture') }}</button>
                                                </li>
                                            </ul>
                                            <div class='tab-content mt-3' id='tabs-tabContent'>
                                                <div class='tab-pane fade show active' id='tabs-upload' role='tabpanel' aria-labelledby='tabs-upload-tab'>
                                                    <input type='file' name='course_image' id='course_image'>
                                                </div>
                                                <div class='tab-pane fade' id='tabs-selectImage' role='tabpanel' aria-labelledby='tabs-selectImage-tab'>
                                                    <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#CoursesImagesModal'>
                                                        <i class='fa-solid fa-image settings-icons'></i>&nbsp;{{ trans('langSelect') }}
                                                    </button>
                                                    <input type='hidden' id='choose_from_list' name='choose_from_list'>
                                                    <label for='selectedImage'>{{ trans('langImageSelected') }}:</label>
                                                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImage'>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class='row form-group mt-4'>
                                    <label for='description' class='col-12 control-label-notes'>{{ trans('langDescription') }}</label>
                                    <div class='col-12'>
                                        {!! $rich_text_editor !!}
                                    </div>
                                </div>
                                <div class='row form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                        <a href='{{$urlServer}}courses/{{$course_code}}/index.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                    </div>
                                </div>

                                <div class='modal fade' id='CoursesImagesModal' tabindex='-1' aria-labelledby='CoursesImagesModalLabel' aria-hidden='true'>
                                    <div class='modal-dialog modal-lg'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <div class='modal-title' id='CoursesImagesModalLabel'>{{ trans('langCourseImage') }}</div>
                                                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                                            </div>
                                            <div class='modal-body'>
                                                <div class='row row-cols-1 row-cols-md-2 g-4'>
                                                    {!! $image_content !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                    <div class='d-none d-lg-block'>
                        <img class='form-image-modules' src='{{ get_form_image() }}' alt="{{ trans('langImgFormsDes') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('select[name=layout]').change(function ()
        {
            if($(this).val() == 1) {
                $('#image_field').removeClass('hidden');
            } else {
                $('#image_field').addClass('hidden');
            }
        });
        $('.chooseCourseImage').on('click',function(){
            var id_img = this.id;
            alert('{{ js_escape(trans('langImageSelected')) }}!');
            document.getElementById('choose_from_list').value = id_img;
            $('#CoursesImagesModal').modal('hide');
            document.getElementById('selectedImage').value = '{{ trans('langSelect') }}:'+id_img;
        });
    });
</script>

@endsection

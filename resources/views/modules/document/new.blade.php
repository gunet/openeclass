@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} @if($course_code) module-container py-lg-0 @else main-container @endif'>
        <div class="@if($course_code) course-wrapper d-lg-flex align-items-lg-strech w-100 @else row m-auto @endif">

            @if($course_code)
                @include('layouts.partials.left_menu')
            @endif

            @if($course_code)
                <div class="col_maincontent_active">
            @else
                <div class="col-12">
            @endif
                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @if($course_code)
                            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                                <div class="offcanvas-header">
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                                </div>
                                <div class="offcanvas-body">
                                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                </div>
                            </div>
                        @endif

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')

                        @if ($can_upload == 1)

                            <div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4'>
                                <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>

                                    <div class='form-wrapper form-edit mt-2 border-0 px-0'>

                                        <form class='form-horizontal' role='form' action='{{ $upload_target_url }}' method='post'>
                                            <input type='hidden' name='{{ $pathName }}' value='{{ $pathValue }}'>
                                            {!! $group_hidden_input !!}
                                            @if ($back)
                                                <input type='hidden' name='back' value='{{ $back }}'>
                                            @endif
                                            @if ($sections)
                                                <div class='form-group mb-4'>
                                                    <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langSection') }}</div>
                                                    <div class='col-sm-12'>
                                                        {!! selection($sections, 'section_id', $section_id) !!}
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($filename)
                                                <div class='form-group mb-4'>
                                                    <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langFileName') }}</div>
                                                    <div class='col-sm-12'>
                                                        <p class='form-control-static'>{{ $filename }}</p>
                                                    </div>
                                                </div>
                                            @endif


                                            <div class="form-group{{ Session::getError('file_title') ? ' has-error' : '' }}">
                                                <label for='file_title' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' placeholder="{{ trans('langTitle') }}..." id='file_title' name='file_title' value='{{ $title }}'>
                                                    <span class='help-block Accent-200-cl'>{{ Session::getError('file_title') }}</span>
                                                </div>
                                            </div>


                                            <div class='form-group mt-4'>
                                                <label for='file_content' class='col-sm-12 control-label-notes'>{{ trans('langContent') }}</label>
                                                <div class='col-sm-12'>
                                                    {!! $rich_text_editor !!}
                                                </div>
                                            </div>

                                            <div class='form-group mt-5'>

                                                <div class='col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                                                    <button class='btn submitAdminBtn' type='submit'>{{ trans('langSave') }}</button>
                                                    <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                                    {!! generate_csrf_token_form_field() !!}
                                                </div>

                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
                                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                                </div>
                            </div>
                        @else
                            <div class='col-12'>
                                <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNotAllowed') }}</span></div>
                            </div>
                        @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

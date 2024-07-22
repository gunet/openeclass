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
                                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                    </div>
                                    <div class="offcanvas-body">
                                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                    </div>
                                </div>
                            @endif

                            @include('layouts.partials.legend_view')

                            @include('layouts.partials.show_alert')

                            <div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4'>
                                <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
                                    <div class='form-wrapper form-edit rounded'>
                                        <form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
                                            <input type='hidden' name='commentPath' value='{{ $file->path }}'>

                                            <div class='form-group'>
                                                <label class='col-12 control-label-notes'>
                                                    @if ($is_dir)
                                                        {{ trans('langDirectory') }}
                                                    @else
                                                        {{ trans('langFile') }}
                                                    @endif
                                                </label>
                                                <div class='col-sm-12'>
                                                    <p class='form-control-static'>{{ $file->filename }}</p>
                                                </div>
                                            </div>

                                            @unless ($is_dir)
                                                <div class='form-group mt-4'>
                                                    <label for='inputFileTitle' class='col-12 control-label-notes'>{{ trans('langTitle') }}:</label>
                                                    <div class='col-12'>
                                                        <input type='text' class='form-control' id='inputFileTitle' placeholder='{{ trans('langTitle') }}' name='file_title' value='{{ $file->title }}'>
                                                    </div>
                                                </div>
                                            @endunless

                                            <div class='form-group mt-4'>
                                                <label for='inputFileComment' class='col-12 control-label-notes'>{{ trans('langComment') }}:</label>
                                                <div class='col-12'>
                                                    <input type='text' class='form-control' id='inputFileComment' placeholder='{{ trans('langComment') }}' name='file_comment' value='{{ $file->comment }}'>
                                                </div>
                                            </div>

                                            @unless ($is_dir)
                                                <div class='form-group mt-4'>
                                                    <label for='inputFileCopyright' class='col-sm-6 control-label-notes'>{{ trans('langCopyrighted') }}:</label>
                                                    <div class='col-12'>
                                                        {!! selection($license_title, 'file_copyrighted', $selected_license_title) !!}
                                                    </div>
                                                </div>
                                                <div class='row'>
                                                    <div class='help-block'>
                                                        {{ trans('langNotRequired') }}
                                                    </div>
                                                </div>
                                            @endunless

                                            <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                                                <button class='btn submitAdminBtn' type='submit'>{{ trans('langSubmit') }}</button>
                                                <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                            </div>
                                            {!! generate_csrf_token_form_field() !!}
                                        </form>
                                    </div>
                                </div>
                                <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
                                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection

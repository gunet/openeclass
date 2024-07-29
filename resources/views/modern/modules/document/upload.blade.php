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

                    @if ($can_upload)
                        <div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4'>
                            <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
                                <div class='form-wrapper form-edit rounded'>
                                    <form class='form-horizontal' role='form' action='{{ $upload_target_url }}' method='post' enctype='multipart/form-data'>
                                        <input type='hidden' name='uploadPath' value='{{ $uploadPath }}'>
                                        {!! $group_hidden_input !!}
                                        @if (isset($_GET['ext']))
                                            <input type='hidden' name='ext' value='true'>
                                        @endif
                                        <div class='form-group'>
                                            @if ($pendingCloudUpload)
                                                <label for='fileCloudName' class='col-12 control-label-notes'>{{ trans('langCloudFile') }}</label>
                                                <div class='col-12'>
                                                    <input type='hidden' class='form-control' id='fileCloudInfo' name='fileCloudInfo' value='{{ $pendingCloudUpload }}'>
                                                    <input type='text' class='form-control' name='fileCloudName' value='{{ CloudFile::fromJSON($pendingCloudUpload)->name() }}' readonly>
                                                </div>
                                            @elseif (isset($_GET['ext']))
                                                <label for='fileURL' class='col-12 control-label-notes'>{{ trans('langExternalFileInfo') }}</label>
                                                <div class='col-12'>
                                                    <input type='text' class='form-control' id='fileURL' name='fileURL'>
                                                </div>
                                            @else
                                                <label for='userFile' class='control-label-notes me-2 mt-1'>{{ trans('langPathUploadFile') }}</label>
                                                <div class='col-12'>
                                                    {!! fileSizeHidenInput() !!}
                                                    {!! CloudDriveManager::renderAsButtons() !!}
                                                    <input type='file' id='userFile' name='userFile'>
                                                </div>
                                            @endif
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='inputFileTitle' class='col-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                            <div class='col-12'>
                                                <input type='text' class='form-control' id='inputFileTitle' placeholder='{{ trans('langTitle') }}' name='file_title'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='inputFileComment' class='col-12 control-label-notes'>{{ trans('langComment') }}</label>
                                            <div class='col-12'>
                                                <input type='text' class='form-control' id='inputFileComment' placeholder='{{ trans('langComment') }}' name='file_comment'>
                                            </div>
                                        </div>
                                        <input type='hidden' name='file_creator' value='{{ $_SESSION['givenname'] . ' ' . $_SESSION['surname'] }}' size='40'>

                                        <div class='form-group mt-4'>
                                            <label for='inputFileCopyright' class='col-sm-6 control-label-notes'>{{ trans('langCopyrighted') }}</label>
                                            <div class='col-12'>
                                                {!! selection($license_title, 'file_copyrighted'); !!}
                                            </div>
                                        </div>

                                        @unless (isset($_GET['ext']))
                                            <div class='form-group mt-4'>
                                                <div class='col-sm-offset-2 col-sm-10'>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label="{{ trans('langUncompress')}}">
                                                            <input type='checkbox' name='uncompress' value='1'>
                                                            <span class='checkmark'></span>
                                                            {{ trans('langUncompress') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endunless

                                        @if ($can_upload_replacement)
                                            <div class='form-group mt-3'>
                                                <div class='col-sm-offset-2 col-sm-12'>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label="{{ trans('langReplaceSameName')}}">
                                                            <input type='checkbox' name='replace' value='1'>
                                                            <span class='checkmark'></span>
                                                            {{ trans('langReplaceSameName') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class='form-group mt-4'>
                                            <div class='help-block'>
                                                {{ trans('langNotRequired') }}
                                            </div>
                                            <div class='help-block'>{{ trans('langMaxFileSize') }}
                                                {{ ini_get('upload_max_filesize') }}
                                            </div>
                                        </div>

                                        <div class='form-group mt-5 d-flex justify-content-end align-items-center flex-wrap gap-2'>
                                            <button class='btn submitAdminBtn' type='submit'>{{ trans('langUpload') }}</button>
                                            <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </form>
                                    {!! generate_csrf_token_form_field() !!}
                                </div>
                            </div>
                            <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                            </div>
                        </div>
                    @else
                        <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNotAllowed') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


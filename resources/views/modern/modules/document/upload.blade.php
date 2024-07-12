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

                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>

                        @include('layouts.partials.legend_view')

                        {!! $backButton !!}

                        @include('layouts.partials.show_alert') 

                        @if ($can_upload)

                                <div class='col-12'>
                                    <div class='form-wrapper form-edit p-3 mt-2 rounded'>

                                        <form class='form-horizontal' role='form' action='{{ $upload_target_url }}' method='post' enctype='multipart/form-data'>
                                            <input type='hidden' name='uploadPath' value='{{ $uploadPath }}'>
                                            @if ($externalFile)
                                                <input type='hidden' name='ext' value='true'>
                                            @endif
                                            <div class='form-group'>
                                                @if ($pendingCloudUpload)
                                                    <label for='fileCloudName' class='col-sm-6 control-label-notes'>{{ trans('langCloudFile') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <input type='hidden' class='form-control' id='fileCloudInfo' name='fileCloudInfo' value='{{ $pendingCloudUpload }}'>
                                                        <input type='text' class='form-control' name='fileCloudName' value='{{ CloudFile::fromJSON($pendingCloudUpload)->name() }}' readonly>
                                                    </div>
                                                @elseif ($externalFile)
                                                    <label for='fileURL' class='col-sm-6 control-label-notes'>{{ trans('langExternalFileInfo') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <input type='text' class='form-control' id='fileURL' name='fileURL'>
                                                    </div>
                                                @else
                                                    <label for='userFile' class='col-sm-6 control-label-notes'>{{ trans('langPathUploadFile') }}:</label>
                                                    <div class='col-sm-12'>
                                                        {!! fileSizeHidenInput() !!}
                                                        {!! CloudDriveManager::renderAsButtons() !!}
                                                        <input type='file' id='userFile' name='userFile'>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <label for='inputFileTitle' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' id='inputFileTitle' name='file_title'>
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <label for='inputFileComment' class='col-sm-6 control-label-notes'>{{ trans('langComment') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' id='inputFileComment' name='file_comment'>
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <label for='inputFileCategory' class='col-sm-6 control-label-notes'>{{ trans('langCategory') }}:</label>
                                                <div class='col-sm-12'>
                                                <select class='form-select' name='file_category'>
                                                    <option selected value='0'>{{ trans('langCategoryOther') }}</option>
                                                    <option value='1'>{{ trans('langExercise') }}</option>
                                                    <option value='2'>{{ trans('langCategoryLecture') }}</option>
                                                    <option value='3'>{{ trans('langCategoryEssay') }}</option>
                                                    <option value='4'>{{ trans('langDescription') }}</option>
                                                    <option value='5'>{{ trans('langCategoryExample') }}</option>
                                                    <option value='6'>{{ trans('langCategoryTheory') }}</option>
                                                </select>
                                                </div>

                                                <input type='hidden' name='file_creator' value='{{ $_SESSION['givenname'] . ' ' . $_SESSION['surname'] }}' size='40'>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <label for='inputFileSubject' class='col-sm-6 control-label-notes'>{{ trans('langSubject') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' id='inputFileSubject' name='file_subject'>
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <label for='inputFileDescription' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' id='inputFileDescription' name='file_description'>
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <label for='inputFileAuthor' class='col-sm-6 control-label-notes'>{{ trans('langAuthor') }}:</label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' id='inputFileAuthor' name='file_author'>
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <input type='hidden' name='file_date' value='' size='40'>
                                                <input type='hidden' name='file_format' value='' size='40'>

                                                <label for='inputFileLanguage' class='col-sm-6 control-label-notes'>{{ trans('langLanguage') }}:</label>
                                                <div class='col-sm-12'>
                                                    {!! selection($languages, 'file_language', $language) !!}
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <label for='inputFileCopyright' class='col-sm-6 control-label-notes'>{{ trans('langCopyrighted') }}:</label>
                                                <div class='col-sm-12'>
                                                    {!! selection($copyrightTitles, 'file_copyrighted') !!}
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            @unless ($externalFile)
                                                <div class='form-group'>
                                                    <div class='col-sm-offset-2 col-sm-10'>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='uncompress' value='1'>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langUncompress') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endunless

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <div class='col-sm-offset-2 col-sm-12'>
                                                    <div class='checkbox'>
                                                         <label class='label-container'>
                                                            <input type='checkbox' name='replace' value='1'>
                                                            <span class='checkmark'></span>
                                                            {{ trans('langReplaceSameName') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='row'>
                                                <div class='infotext col-sm-offset-2 col-sm-12 margin-bottom-fat'>{{ trans('langNotRequired') }}
                                                    {{ trans('langMaxFileSize') }}
                                                    {{ ini_get('upload_max_filesize') }}
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <div class='col-xs-offset-2 col-xs-12'>
                                                    <button class='btn btn-primary' type='submit'>{{ trans('langUpload') }}</button>
                                                    <a class='btn btn-secondary' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>

                        @else
                        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNotAllowed') }}</span></div>
                        @endif
                    </div>
                </div>
            </div>

    </div>
    </div>
@endsection


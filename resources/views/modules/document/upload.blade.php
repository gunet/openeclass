@extends('layouts.default')

@section('content')
        {!! $backButton !!}
        @if ($can_upload)
            <div class='row'>
            <div class='col-md-12'>
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' action='{{ $upload_target_url }}' method='post' enctype='multipart/form-data'>
                        <input type='hidden' name='uploadPath' value='{{ $uploadPath }}'>
                        @if ($externalFile)
                            <input type='hidden' name='ext' value='true'>
                        @endif
                        <div class='form-group'>
                            @if ($pendingCloudUpload)
                                <label for='fileCloudName' class='col-sm-2 control-label'>{{ trans('langCloudFile') }}:</label>
                                <div class='col-sm-10'>
                                    <input type='hidden' class='form-control' id='fileCloudInfo' name='fileCloudInfo' value='{{ $pendingCloudUpload }}'>
                                    <input type='text' class='form-control' name='fileCloudName' value='{{ CloudFile::fromJSON($pendingCloudUpload)->name() }}' readonly>
                                </div>
                            @elseif ($externalFile)
                                <label for='fileURL' class='col-sm-2 control-label'>{{ trans('langExternalFileInfo') }}:</label>
                                <div class='col-sm-10'>
                                    <input type='text' class='form-control' id='fileURL' name='fileURL'>
                                </div>
                            @else
                                <label for='userFile' class='col-sm-2 control-label'>{{ trans('langPathUploadFile') }}:</label>
                                <div class='col-sm-10'>
                                    {!! fileSizeHidenInput() !!}
                                    {!! CloudDriveManager::renderAsButtons() !!}
                                    <input type='file' id='userFile' name='userFile'>
                                </div>
                            @endif
                        </div>
                        <div class='form-group'>
                            <label for='inputFileTitle' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' id='inputFileTitle' name='file_title'>
                            </div>
                        </div>

                        <div class='form-group'>
                            <label for='inputFileComment' class='col-sm-2 control-label'>{{ trans('langComment') }}:</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' id='inputFileComment' name='file_comment'>
                            </div>
                        </div>

                        <div class='form-group'>
                            <label for='inputFileCategory' class='col-sm-2 control-label'>{{ trans('langCategory') }}:</label>
                            <div class='col-sm-10'>
                              <select class='form-control' name='file_category'>
                                <option selected value='0'>{{ trans('langCategoryOther') }}</option>
                                <option value='1'>{{ trans('langCategoryExcercise') }}</option>
                                <option value='2'>{{ trans('langCategoryLecture') }}</option>
                                <option value='3'>{{ trans('langCategoryEssay') }}</option>
                                <option value='4'>{{ trans('langCategoryDescription') }}</option>
                                <option value='5'>{{ trans('langCategoryExample') }}</option>
                                <option value='6'>{{ trans('langCategoryTheory') }}</option>
                              </select>
                            </div>

                            <input type='hidden' name='file_creator' value='{{ $_SESSION['givenname'] . ' ' . $_SESSION['surname'] }}' size='40'>
                        </div>

                        <div class='form-group'>
                            <label for='inputFileSubject' class='col-sm-2 control-label'>{{ trans('langSubject') }}:</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' id='inputFileSubject' name='file_subject'>
                            </div>
                        </div>

                        <div class='form-group'>
                            <label for='inputFileDescription' class='col-sm-2 control-label'>{{ trans('langDescription') }}:</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' id='inputFileDescription' name='file_description'>
                            </div>
                        </div>

                        <div class='form-group'>
                            <label for='inputFileAuthor' class='col-sm-2 control-label'>{{ trans('langAuthor') }}:</label>
                            <div class='col-sm-10'>
                                <input type='text' class='form-control' id='inputFileAuthor' name='file_author'>
                            </div>
                        </div>

                        <div class='form-group'>
                            <input type='hidden' name='file_date' value='' size='40'>
                            <input type='hidden' name='file_format' value='' size='40'>

                            <label for='inputFileLanguage' class='col-sm-2 control-label'>{{ trans('langLanguage') }}:</label>
                            <div class='col-sm-10'>
                                {!! selection($languages, 'file_language', $language) !!}
                            </div>
                        </div>

                        <div class='form-group'>
                            <label for='inputFileCopyright' class='col-sm-2 control-label'>{{ trans('langCopyrighted') }}:</label>
                            <div class='col-sm-10'>
                                {!! selection($copyrightTitles, 'file_copyrighted') !!}
                            </div>
                        </div>

                        @unless ($externalFile)
                            <div class='form-group'>
                                <div class='col-sm-offset-2 col-sm-10'>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='uncompress' value='1'>
                                            <strong>{{ trans('langUncompress') }}</strong>
                                        </label>
                                    </div>
                                  </div>
                            </div>
                        @endunless 

                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='replace' value='1'>
                                        <strong>{{ trans('langReplaceSameName') }}</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='infotext col-sm-offset-2 col-sm-10 margin-bottom-fat'>{{ trans('langNotRequired') }}
                                {{ trans('langMaxFileSize') }}
                                {{ ini_get('upload_max_filesize') }}
                            </div>
                        </div>

                        <div class='form-group'>
                            <div class='col-xs-offset-2 col-xs-10'>
                                <button class='btn btn-primary' type='submit'>{{ trans('langUpload') }}</button>
                                <a class='btn btn-default' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                            </div>
                        </div>
                        {!! generate_csrf_token_form_field() !!}
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class='alert alert-warning'>{{ trans('langNotAllowed') }}</div>
    @endif
@endsection


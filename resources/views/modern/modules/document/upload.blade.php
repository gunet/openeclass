@extends('layouts.default')

@section('content')

    <div class="pb-3 pt-3">   

        <div class="container-fluid main-container">

            <div class="row">

                @if($course_code)
                <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>
                @endif

                @if($course_code)
                <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                @else
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                @endif
                    <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">
                        @if($course_code)
                        <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                            <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                                <i class="fas fa-align-left"></i>
                                <span></span>
                            </button>
                            
                        
                            <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                                <i class="fas fa-tools"></i>
                            </a>
                        </nav>
                        @else
                        <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                            <a type="button" id="getTopicButton" class="d-none d-sm-block d-md-none d-lg-block ms-2 btn btn-primary btn btn-primary" href="{{$urlAppend}}modules/help/help.php?language={{$language}}&topic={{$helpTopic}}&subtopic={{$helpSubTopic}}" style='margin-top:-10px'>
                                <i class="fas fa-question"></i>
                            </a>
                        </nav>
                        @endif

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                        <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>
                        

                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])


                        <div class="row p-2"></div><div class="row p-2"></div>
                        @if($course_code)
                        <span class="control-label-notes ms-1">{{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small></span>
                        <div class="row p-2"></div><div class="row p-2"></div>
                        @endif
                        

                        {!! $backButton !!}

                        @if(Session::has('message'))
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                {{ Session::get('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </p>
                        </div>
                        @endif
                        
                        <div class="row p-2"></div><div class="row p-2"></div>

                        @if ($can_upload == 1)
                            
                                <div class='col-md-12'>
                                    <div class='form-wrapper'>
                                        <form class='form-horizontal' role='form' action='/modules/document/index.php' method='post' enctype='multipart/form-data'>
                                            <input type="hidden" name="courseCodeAfterUpload" value={{$course_code}}>
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
                                                            <label>
                                                                <input type='checkbox' name='uncompress' value='1'>
                                                                <strong>{{ trans('langUncompress') }}</strong>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endunless 

                                            <div class="row p-2"></div>     

                                            <div class='form-group'>
                                                <div class='col-sm-offset-2 col-sm-12'>
                                                    <div class='checkbox'>
                                                        <label>
                                                            <input type='checkbox' name='replace' value='1'>
                                                            <strong>{{ trans('langReplaceSameName') }}</strong>
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
                        <div class='alert alert-warning'>{{ trans('langNotAllowed') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


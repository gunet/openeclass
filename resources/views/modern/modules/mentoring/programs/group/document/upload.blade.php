@extends('layouts.default')

@section('content')

<?php load_js('tinymce.popup.urlgrabber.min.js');?>

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

             
                    @if($is_group_doc == 1)
                        @if($isCommonGroup == 1)
                            <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?group_id={!! getInDirectReference($group_id) !!}">{{ trans('langDoc') }}&nbsp({!! show_mentoring_program_group_name($group_id) !!})</a></li>
                                    <li class="breadcrumb-item active TextMedium" aria-current="page">{{ trans('langDownloadFile') }}</li>
                                </ol>
                            </nav>
                        @else
                            <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/index.php">{{ trans('langGroupMentorsMentees') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?group_id={!! getInDirectReference($group_id) !!}">{{ trans('langDoc')}}(&nbsp{!! show_mentoring_program_group_name($group_id) !!})</a></li>
                                    <li class="breadcrumb-item active TextMedium" aria-current="page">{{ trans('langDownloadFile') }}</li>
                                </ol>
                            </nav>
                        @endif

                    @else
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                @if(isset($_GET['common_program']))
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?common_docs=true">{{ trans('langCommonDocs') }}</a></li>
                                @else
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?mydocs=true">{{ trans('langMyDocs') }}</a></li>
                                @endif
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ trans('langDownloadFile') }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif  
                    
                    

                    {!! $backButton !!}
    
                        @if ($can_upload_mentoring or $can_upload_mentoring == $uid)
                            
                            
                            
                            <div class='col-lg-6 col-12'>
                                <div class='form-wrapper form-edit rounded-2 p-3 solidPanel'>
                                    <form class='form-horizontal' role='form' action='{{ $upload_target_url }}' method='post' enctype='multipart/form-data'>

                                        <input type='hidden' name='uploadPath' value='{{ $uploadPath }}'>
                                        
                                        @if ($externalFile)
                                            <input type='hidden' name='ext' value='true'>
                                        @endif

                                        <div class='form-group'>
                                            @if ($pendingCloudUpload)
                                                <label for='fileCloudName' class='col-12 control-label-notes'>{{ trans('langCloudFile') }}:</label>
                                                <div class='col-12'>
                                                    <input type='hidden' class='form-control' id='fileCloudInfo' name='fileCloudInfo' value='{{ $pendingCloudUpload }}'>
                                                    <input type='text' class='form-control' name='fileCloudName' value='{{ CloudFile::fromJSON($pendingCloudUpload)->name() }}' readonly>
                                                </div>
                                            @elseif ($externalFile)
                                                <label for='fileURL' class='col-12 control-label-notes'>{{ trans('langExternalFileInfo') }}:</label>
                                                <div class='col-12'>
                                                    <input type='text' class='form-control' id='fileURL' name='fileURL'>
                                                </div>
                                            @else
                                                <label for='userFile' class='col-12 control-label-notes'>{{ trans('langPathUploadFile') }}:</label>
                                                <div class='col-12'>
                                                    {!! fileSizeHidenInput() !!}
                                                    {!! CloudDriveManager::renderAsButtons() !!}
                                                    <input type='file' id='userFile' name='userFile'>
                                                </div>
                                            @endif
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='inputFileTitle' class='col-12 control-label-notes'>{{ trans('langTitle') }}:</label>
                                            <div class='col-12'>
                                                <input type='text' class='form-control' id='inputFileTitle' name='file_title'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='inputFileComment' class='col-12 control-label-notes'>{{ trans('langComment') }}:</label>
                                            <div class='col-12'>
                                                <input type='text' class='form-control' id='inputFileComment' name='file_comment'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-12 control-label-notes'>{{ trans('langCategory') }}:</label>
                                            <div class='col-12'>
                                            <select class='form-select' name='file_category'>
                                                <option selected value='0'>{{ trans('langCategoryOther') }}</option>
                                                <option value='1'>{{ trans('langCategoryExcercise') }}</option>
                                                <option value='2'>{{ trans('langCategoryLecture') }}</option>
                                                <option value='3'>{{ trans('langCategoryEssay') }}</option>
                                                <option value='4'>{{ trans('langDescription') }}</option>
                                                <option value='5'>{{ trans('langCategoryExample') }}</option>
                                                <option value='6'>{{ trans('langCategoryTheory') }}</option>
                                            </select>
                                            </div>

                                            <input type='hidden' name='file_creator' value='{{ $_SESSION['givenname'] . ' ' . $_SESSION['surname'] }}' size='40'>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='inputFileSubject' class='col-12 control-label-notes'>{{ trans('langSubject') }}:</label>
                                            <div class='col-12'>
                                                <input type='text' class='form-control' id='inputFileSubject' name='file_subject'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='inputFileDescription' class='col-12 control-label-notes'>{{ trans('langDescription') }}:</label>
                                            <div class='col-12'>
                                                <input type='text' class='form-control' id='inputFileDescription' name='file_description'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='inputFileAuthor' class='col-12 control-label-notes'>{{ trans('langAuthor') }}:</label>
                                            <div class='col-12'>
                                                <input type='text' class='form-control' id='inputFileAuthor' name='file_author'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <input type='hidden' name='file_date' value='' size='40'>
                                            <input type='hidden' name='file_format' value='' size='40'>

                                            <label class='col-12 control-label-notes'>{{ trans('langLanguage') }}:</label>
                                            <div class='col-12'>
                                                {!! selection($languages, 'file_language', $language) !!}
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-12 control-label-notes'>{{ trans('langCopyrighted') }}:</label>
                                            <div class='col-12'>
                                                {!! selection($copyrightTitles, 'file_copyrighted') !!}
                                            </div>
                                        </div>

                                        @unless ($externalFile)
                                            <div class='form-group mt-4'>
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

                                        <div class='form-group mt-4'>
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
                                            <div class='infotext col-sm-offset-2 col-sm-10 margin-bottom-fat mt-4'>{{ trans('langNotRequired') }}
                                                {{ trans('langMaxFileSize') }}
                                                {{ ini_get('upload_max_filesize') }}
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class='col-12 d-flex justify-content-center'>
                                                <button class='btn submitAdminBtn' type='submit'>{{ trans('langUpload') }}</button>
                                                <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                            </div>
                                        </div>
                                        {!! generate_csrf_token_form_field() !!}
                                    </form>
                                </div>
                            </div>
                            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                                <div class='col-12 h-100 left-form'></div>
                            </div>
                              
                           
                        @else
                            <div class='alert alert-warning'>{{ trans('langNotAllowed') }}</div>
                        @endif




                  

                   
                
        </div>
    </div>
</div>

<script>

    $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

</script>

@endsection

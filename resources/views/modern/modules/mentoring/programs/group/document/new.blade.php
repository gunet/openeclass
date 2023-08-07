@extends('layouts.default')

@section('content')

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
                                        <li class="breadcrumb-item active TextMedium" aria-current="page">{{ trans('langCreateDoc') }}</li>
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
                                        <li class="breadcrumb-item active TextMedium" aria-current="page">{{ trans('langCreateDoc') }}</li>
                                    </ol>
                                </nav>
                            @endif
                        @else
                            <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                    @if(isset($_GET['editPathMydoc']) or isset($_GET['program']))
                                        <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?mydocs=true">{{ trans('langMyDocs') }}</a></li>
                                    @else
                                        <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?common_docs=true">{{ trans('langCommonDocs') }}</a></li>
                                    @endif
                                    <li class="breadcrumb-item active TextMedium" aria-current="page">{{ trans('langCreateDoc') }}</li>
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
                        
                        @if ($can_upload_mentoring or $uploading_as_user)
                            
                            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                                <div class='col-12 h-100 left-form'></div>
                            </div>
                            <div class='col-lg-6 col-12'>
                                <div class='form-wrapper form-edit rounded-2 p-3 solidPanel'>
                                
                                    <form class='form-horizontal' role='form' action='{{ $upload_target_url }}' method='post'>
                                        <input type='hidden' name='{{ $pathName }}' value='{{ $pathValue }}'>
                                        {!! $group_hidden_input !!}
                                        @if ($back)
                                            <input type='hidden' name='back' value='{{ $back }}'>
                                        @endif
                                        @if ($sections)
                                            <div class='form-group mb-4'>
                                                <label for='section' class='col-sm-12 control-label-notes'>{{ trans('langSection') }}</label>
                                                <div class='col-sm-12'>
                                                    {!! selection($sections, 'section_id', $section_id) !!}
                                                </div>
                                            </div>
                                        @endif

                                        

                                        @if ($filename)
                                            <div class='form-group mb-4'>
                                                <label for='file_name' class='col-sm-12 control-label-notes'>{{ trans('langFileName') }}</label>
                                                <div class='col-sm-12'>
                                                    <p class='form-control-static'>{{ $filename }}</p>
                                                </div>
                                            </div>
                                        @endif

                                    

                                        <div class="form-group{{ Session::getError('file_title') ? ' has-error' : '' }}">
                                            <label for='file_title' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' placeholder="{{ trans('langTitle') }}..." id='file_title' name='file_title' value='{{ $title }}'>
                                                <span class='help-block'>{{ Session::getError('file_title') }}</span>
                                            </div>
                                        </div>

                                        

                                        <div class='form-group mt-4'>
                                            <label for='file_title' class='col-sm-12 control-label-notes'>{{ trans('langContent') }}</label>
                                            <div class='col-sm-12'>
                                                {!! $rich_text_editor !!}
                                            </div>
                                        </div>

                                    

                                        <div class='form-group mt-5'>
                                           
                                            <div class='col-12 d-flex justify-content-center align-items-center'>
                                                <button class='btn submitAdminBtn' type='submit'>{{ trans('langSave') }}</button>
                                                <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                                {!! generate_csrf_token_form_field() !!}
                                            </div>
                                            
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                        <div class='col-12'>
                            <div class='alert alert-warning'>{{ trans('langNotAllowed') }}</div>
                        </div>
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

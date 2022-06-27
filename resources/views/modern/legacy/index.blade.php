<?php
$i=0;
if(count($toolArr) == 3){
foreach($toolArr as $tool){

       if($i==0){
          $US = $tool[0]['text'];
       }
       if($i==1){
        $CA = $tool[0]['text'];
       }
       if($i==2){
        $AT = $tool[0]['text'];
       }
    $i++;
}}
// print_r($US);
// print_r($CA);
// print_r($AT);

?>
@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row" style="">

            @if($course_code)
            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>
            @endif

            <!-- is admin tool sidebar -->
            @if(count($toolArr)==3 && ($US == 'Διαχείριση χρηστών' or $US == 'Users Management')
            && ($CA == 'Διαχείριση μαθημάτων' or $CA == 'Course Administration')
            && ($AT == 'Διαχείριση πλατφόρμας' or $AT == 'Admin Tool') && !($course_code))
                <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                        @include('layouts.partials.sidebarAdmin')
                    </div>
                </div>
            @endif

            @if($course_code or (count($toolArr)==3
            && ($US == 'Διαχείριση χρηστών' or $US == 'Users Management')
            && ($CA == 'Διαχείριση μαθημάτων' or $CA == 'Course Administration')
            && ($AT == 'Διαχείριση πλατφόρμας' or $AT == 'Admin Tool')))
            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
            @else
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_all">
            @endif

                <div class="row p-5">

                    @if($course_code or (count($toolArr)==3
                    && ($US == 'Διαχείριση χρηστών' or $US == 'Users Management')
                    && ($CA == 'Διαχείριση μαθημάτων' or $CA == 'Course Administration')
                    && ($AT == 'Διαχείριση πλατφόρμας' or $AT == 'Admin Tool')))
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



                    <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <?php $size_breadcrumb = count($breadcrumbs); $count=0; ?>
                            <?php for($i=0; $i<$size_breadcrumb; $i++){ ?>
                                <li class="breadcrumb-item"><a href="{!! $breadcrumbs[$i]['bread_href'] !!}">{!! $breadcrumbs[$i]['bread_text'] !!}</a></li>
                            <?php } ?>
                        </ol>
                    </nav>



                    @if($course_code or (count($toolArr)==3
                    && ($US == 'Διαχείριση χρηστών' or $US == 'Users Management')
                    && ($CA == 'Διαχείριση μαθημάτων' or $CA == 'Course Administration')
                    && ($AT == 'Διαχείριση πλατφόρμας' or $AT == 'Admin Tool')))
                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @if($course_code)
                               @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            @else
                               @include('layouts.partials.sidebarAdmin')
                            @endif
                        </div>
                    </div>
                    @endif


                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="row p-2"></div><div class="row p-2"></div>
                        <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-folder-open" aria-hidden="true"></i> {{$toolName}} @if($course_code) {{trans('langsOfCourse')}} <<strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong>></span>@endif
                            <div class="manage-course-tools"style="float:right">
                                @if($is_editor == 1)
                                    @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                                @endif
                            </div>
                        </legend>
                    </div>



                    @if($course_code)
                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5">
                            <span class="control-label-notes">
                                {{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small>
                            </span>
                        </div>
                    @endif

                    @if(!$course_code)
                        <div class="row p-2"></div><div class="row p-2"></div>
                    @endif

                    {!! $tool_content !!}

                </div>


            </div>


        </div>
    </div>



</div>

@endsection



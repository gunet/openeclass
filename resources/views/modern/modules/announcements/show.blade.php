
@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                   
                <div class="row p-5">

                        <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                            <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                                <i class="fas fa-align-left"></i>
                                <span></span>
                            </button>
                            
                        
                            <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                                <i class="fas fa-tools"></i>
                            </a>
                        </nav>

                        <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/portfolio.php">{{trans('langPortfolio')}}</a></li>
                                <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/my_courses.php">{{trans('mycourses')}}</a></li>
                                <li class="breadcrumb-item"><a href="{{$urlServer}}courses/{{$course_code}}/index.php">{{$currentCourseName}}</a></li>
                                <li class="breadcrumb-item"><a href="{{ $urlAppend }}modules/announcements/index.php?course={{$course_code}}">{{trans('langAnnouncements')}}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{trans('langAnnouncements')}}</li>
                            </ol>
                        </nav>

                        <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>

                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-10 col-sm-6">
                            <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-bell"></i> {{$toolName}} {{trans('langsOfCourse')}} <<strong >{{$currentCourseName}} <small>({{$course_code}})</small></strong>></span>
                                <div class="manage-course-tools"style="float:right">
                                    @if($is_editor == 1)
                                        @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                                    @endif
                                </div>
                            </legend>
                        </div>

                        <div class="row p-2"></div>
                        <span class="control-label-notes">{{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small></span>
                        <div class="row p-2"></div>

                        <!-- {!! $action_bar !!} -->

                        <div class='row'>
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='panel'>
                                    <div class='panel-body'>
                                        <div class='single_announcement'>
                                            <label class="control-label-notes">{{trans('langHomePageIntroTitle')}}</label>
                                            <div class='announcement-title'>
                                                {!! $title !!}
                                            </div>

                                            <div class="row p-2"></div>


                                            <label class="control-label-notes">{{trans('langDate')}}</label>
                                            <div class='announcement-date'>
                                                {!! $date !!}
                                            </div>

                                            <div class="row p-2"></div>


                                            <label class="control-label-notes">{{trans('langContent')}}</label>
                                            <div class='announcement-main'>
                                                {!! $content !!}
                                            </div>
                                        </div>

                                        <div class="row p-2"></div>


                                        @if ($tags_list)
                                            <hr>
                                            <div>{{ trans('langTags') }}: {!! $tags_list !!}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

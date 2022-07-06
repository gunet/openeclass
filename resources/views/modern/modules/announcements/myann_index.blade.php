
@extends('layouts.default')

@section('content')

    <div class="pb-3 pt-3">

        <div class="container-fluid main-container">

            <div class="row">

                @if(!$allCoursesAnnouncements)
                        <!-- <script type="text/javascript" src="{{ $urlAppend }}template/modern/js/my_courses_color_header.js"></script> -->

                        <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                            <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>

                        <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                            <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">
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

                                @if(Session::has('message'))
                                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                                    <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                        {{ Session::get('message') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </p>
                                </div>
                                @endif


                                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                    <div class="row p-2"></div><div class="row p-2"></div>
                                    <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-bell"></i> {{trans('langAnnouncements')}} {{trans('langsOfCourse')}} <<strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong>></span>
                                        <div class="manage-course-tools"style="float:right">
                                            @if($is_editor == 1)
                                                    @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                                            @endif
                                        </div>
                                    </legend>
                                </div>

                                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5">
                                    <span class="control-label-notes">
                                        {{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small>
                                    </span>
                                </div>

                                @if($is_editor)

                                    <div class="col-xl-12 col-md-12 col-sm-12 col-xs-12">
                                        <button class="float-end btn btn-success add_btn_announcements"><i class="fa fa-plus" aria-hidden="true"></i><a class="addAnnouncement_a" href="{{ $urlAppend }}modules/announcements/new.php?course={{$course_code}}">{{trans('langAddAnn')}}</a></button>
                                    </div>

                                    <div class="row p-2"></div>
                                @endif

                                @if(Session::has('message'))
                                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                                    <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                        {{ Session::get('message') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </p>
                                </div>
                                @endif
                                @include('layouts.partials.announcements',['announcements' => $aaData , 'title_course' => $currentCourseName ,'is_editor' => $is_editor,'announcements_ids' => $announcements_ids, 'course_code' => $course_code])
                            </div>
                        </div>
                @else



                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center courses-details">
                            <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                                <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                                    <button type="button" class="ms-2 btn btn-primary btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style='margin-top:-10px'>
                                        <i class="fas fa-question"></i>
                                    </button>
                                </nav>

                                <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/portfolio.php">{{trans('langPortfolio')}}</a></li>
                                        <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/my_courses.php">{{trans('mycourses')}}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{$toolName}}</li>
                                    </ol>
                                </nav>


                                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                    <div class="row p-2"></div>
                                    <legend class="float-none w-auto py-2 px-4 notes-legend"><span style="margin-left:-20px;"><i class="fas fa-bell"></i>{{trans('langMyPersoAnnouncements')}}</span></legend>
                                </div>

                                <div class='row p-2'></div><div class='row p-2'></div>

                                <div class='table-responsive'>
                                    <table id='ann_table_my_ann' class='announcements_table'>
                                        <thead>
                                        <tr class='notes_thead'>
                                            <th class="text-white">{{ trans('langAnnouncement') }}</th>
                                            <th class="text-white">{{ trans('langDate') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">{{trans('langHelp')}}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <iframe frameborder="0" width="100%" height="500px" src="https://docs.openeclass.org/el/teacher/portfolio/?do=export_xhtml"></iframe>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{trans('langClose')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>



                @endif
            </div>
        </div>
    </div>


@endsection

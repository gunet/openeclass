<?php
    // $course_code = $_GET['course'];
    // $course_id = course_code_to_id($course_code);
    // $title_course = course_id_to_title($course_id);
    // $course_code_title = course_id_to_code($course_id);
    // $course_Teacher = course_id_to_prof($course_id);
    // $is_editor = check_editor($user_id,$cid);
?>

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

                        @if(Session::has('message'))
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                {{ Session::get('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </p>
                        </div>
                        @endif

                        <div class='row'>
                            <div class='col-sm-12'>
                                <div class='form-wrapper'>
                                    <form class='form-horizontal' role='form'>
                                        <div class='form-group'>
                                            <label class='col-sm-4 control-label-notes'>{{ trans('langQuotaUsed') }}:</label>
                                            <div class='col-sm-8'>
                                                <p class='form-control-static'>{{ $used }}</p>
                                            </div>
                                        </div>
                                        <div class="row p-2"></div>
                                        <div class='form-group'>
                                            <label class='col-sm-3 control-label-notes'>{{ trans('langQuotaPercentage') }}:</label>
                                            <div class='col-sm-9'>
                                                <!-- <div class='progress'>
                                                    <p class='progress-bar from-control-static' role='progressbar' aria-valuenow='{{$diskUsedPercentage}}' aria-valuemin='0' aria-valuemax='100' style='min-width: 2em; width: {{$diskUsedPercentage}}%;'>
                                                        <span style="color:black">{{$diskUsedPercentage}}%</span>
                                                    </p>
                                                </div> -->
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="min-width: 2em; width: {{$diskUsedPercentage}}%;" aria-valuenow="{{$diskUsedPercentage}}" aria-valuemin="0" aria-valuemax="100">{{$diskUsedPercentage}}%</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row p-2"></div>
                                        <div class="row p-2"></div>
                                        <div class='form-group'>
                                            <label class='col-sm-4 control-label-notes'>{{ trans('langQuotaTotal') }}:</label>
                                            <div class='col-sm-8'>
                                                <p class='form-control-static'>{{ $quota }}</p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


<?php 
    $lesson = Database::get()->querySingle("SELECT * FROM `course` WHERE `course`.`title`='{$title_course}' ");
    $course_code_title = $lesson->code;  
    $course_Teacher = $lesson->prof_names;
?>
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

                                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                                        <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                                            <div class="offcanvas-header">
                                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">
                                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                            </div>
                                        </div>
                                        
                                            
                                        <form class='form-horizontal' role='form' action='{{ $cat_url }}' method='post'>

                                            <!-- <input type="hidden" name="checkStudentView" value={{$is_editor}}> -->

                                            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                <div class="row p-2"></div><div class="row p-2"></div>
                                                <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-list" aria-hidden="true"></i> {{$toolName}} {{trans('langsOfCourse')}} <<strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong>></span>
                                                    <div class="manage-course-tools"style="float:right">
                                                        @if($is_editor)
                                                            
                                                            @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                                                            
                                                        @endif
                                                    </div>
                                                </legend>
                                            </div>
                                            
                                            <div class="row p-2"></div><div class="row p-2"></div>
                                            <span class="control-label-notes ms-1">{{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small></span>
                                            <div class="row p-2"></div><div class="row p-2"></div>

                                            @if(isset($glossary_cat))
                                                <input type='hidden' name='category_id' value='{{ getIndirectReference($glossary_cat->id) }}'>
                                            @endif

                                            <div class='form-group{{ Session::getError('name') ? " has-error" : "" }}'>
                                                <label for='name' class='col-sm-4 control-label-notes'>{{ trans('langCategoryName') }}: </label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' id='term' name='name' placeholder='{{ trans('langCategoryName') }}' value='{{ $name }}'>
                                                    <span class='help-block'>{{ Session::getError('name') }}</span>    
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>
                                                <label for='description' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}</label>
                                                <div class='col-sm-12'>
                                                    {!! $description_rich !!}
                                                </div>
                                            </div>

                                            <div class="row p-2"></div>

                                            <div class='form-group'>    
                                                <div class='col-sm-12 col-sm-offset-2'>
                                                    {!! $form_buttons !!}
                                                </div>
                                            </div>
                                            {!! generate_csrf_token_form_field() !!}   
                                                           
                                        </form>
                                            
                                        
                                
                            </div>
                       
                    </div>
                </div>
            </div>
        </div>                  
@endsection


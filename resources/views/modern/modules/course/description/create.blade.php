@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container pb-3">

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


                    <div class="col-xxl-12 col-lx-12 col-lg-12 col-md-10 col-sm-6">
                        <div class="row p-2"></div><div class="row p-2"></div>
                        <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-list" aria-hidden="true"></i> {{$toolName}} του μαθήματος <strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong></span>
                            <div class="manage-course-tools"style="float:right">
                                @if($is_editor == 1)
                                    
                                        @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                                    
                                @endif
                            </div>
                        </legend>
                    </div>

                    <div class="row p-2"></div><div class="row p-2"></div>
                    <span class="control-label-notes ms-1">{{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small></span>
                    <div class="row p-2"></div><div class="row p-2"></div>

                    {!! $action_bar !!}
                    <div class='form-wrapper'>
                        <form class='form-horizontal' role='form' action='{{$urlServer}}modules/course_description/index.php?course={{ $course_code }}' method='post'>
                        <input type='hidden' name='course' value='{{ $course_code }}'>
                            @if ($editId)
                            <input type='hidden' name='editId' value='{{ getIndirectReference($editId) }}'>
                            @endif            

                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label for='editType' class='col-sm-6 control-label-notes'>{{ trans('langType') }}: </label>
                                <div class='col-sm-12'>
                                    {!! selection($types, 'editType', $defaultType, 'class="form-control" id="typSel"') !!}
                                </div>
                            </div>

                            <div class="row p-2"></div>


                            <div class='form-group{{ $titleError ? " form-error" : ""}}'>
                                <label for='titleSel' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</label>
                                <div class='col-sm-12'>
                                    <input type='text' name='editTitle' class='form-control' value='{{ $cdtitle }}' size='40' id='titleSel'>
                                    {!! Session::getError('editTitle', "<span class='help-block'>:message</span>") !!}                                    
                                </div>
                            </div>      
                            
                            <div class="row p-2"></div>


                            <div class='form-group'>
                                <label for='editComments' class='col-sm-6 control-label-notes'>{{ trans('langContent') }}:</label>
                                <div class='col-sm-12'>
                                {!! $text_area_comments !!}
                                </div>
                            </div>

                            <div class="row p-2"></div>

                        <div class='form-group'>    
                            <div class='col-sm-10 col-sm-offset-2'>
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


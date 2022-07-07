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

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>
                                
                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
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


                        {!! isset($action_bar) ?  $action_bar : '' !!}

                        @if(Session::has('message'))
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                {{ Session::get('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </p>
                        </div>
                        @endif
                        <div class="row p-2"></div>

                        @if ($course_descs)
                            @foreach ($course_descs as $key => $course_desc)    
                                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>      
                                    <div class='panel panel-action-btn-default mydescriptionPanel ps-3 pt-3 pb-3 pe-3'>
                                        <div class='panel-heading'>
                                            @if ($is_editor) 
                                                <div class='pull-right'>
                                                {!! action_button(
                                                        array(
                                                            array(
                                                                'title' => trans('langEditChange'),
                                                                'url' => "edit.php?course=$course_code&amp;id=" . getIndirectReference($course_desc->id),
                                                                'icon' => 'fa-edit'
                                                            ),
                                                            array('title' => $course_desc->visible ? trans('langRemoveFromCourseHome') : trans('langAddToCourseHome'),
                                                                'url' => "index.php?course=$course_code&amp;vis=" . getIndirectReference($course_desc->id),
                                                                'icon' => $course_desc->visible ? 'fa-eye-slash' : 'fa-eye'
                                                            ),
                                                            array('title' => trans('langUp'),
                                                                'level' => 'primary',
                                                                'icon' => 'fa-arrow-up',
                                                                'url' => "index.php?course=$course_code&amp;up=" . getIndirectReference($course_desc->id),
                                                                'disabled' => $key <= 0),
                                                            array('title' => trans('langDown'),
                                                                'level' => 'primary',
                                                                'icon' => 'fa-arrow-down',
                                                                'url' => "index.php?course=$course_code&amp;down=" . getIndirectReference($course_desc->id),
                                                                'disabled' => $key + 1 >= count($course_descs)),
                                                            array('title' => trans('langDelete'),
                                                                'url' => "index.php?course=$course_code&amp;del=" . getIndirectReference($course_desc->id),
                                                                'icon' => 'fa-times',
                                                                'class' => 'delete',
                                                                'confirm' => trans('langConfirmDelete'))                            
                                                        )
                                                ) !!}

                                                <!-- <div class="btn-group" role="group" aria-label="Basic example">
                                                    <a type="button" class="btn btn-secondary" href="edit.php?course={{$course_code}}&amp;id={{getIndirectReference($course_desc->id)}}"><i class="fas fa-edit" style="color:white"></i> {{trans('langEditChange')}}</a>
                                                    <a type="button" class="btn btn-secondary" href="index.php?course={{$course_code}}&amp;vis={{getIndirectReference($course_desc->id)}}">@if($course_desc->visible == 0)<i class="fas fa-eye"></i>{{trans('langAddToCourseHome')}}@else<i class="fas fa-eye-slash"></i>{{trans('langRemoveFromCourseHome')}}@endif</a>
                                                    <a type="button" class="btn btn-danger" href="index.php?course={{$course_code}}&amp;del={{getIndirectReference($course_desc->id)}}"><i class="fas fa-trash" style="color:white"></i> {{trans('langDelete')}}</a>
                                                    <a type="button" class="btn btn-secondary" href="index.php?course={{$course_code}}&amp;up={{getIndirectReference($course_desc->id)}}"><i class="fas fa-arrow-up"></i> {{trans('langUp')}}</a>
                                                    <a type="button" class="btn btn-secondary" href="index.php?course={{$course_code}}&amp;down={{getIndirectReference($course_desc->id)}}"><i class="fas fa-arrow-down"></i> {{trans('langDown')}}</a>
                                                </div> -->


                                                </div>
                                            @endif
                                            <h3 class='panel-title'>
                                                <span class='control-label-notes'>{{ $course_desc->title}}</span>
                                                @if ($course_desc->visible && $is_editor)
                                                    &nbsp;&nbsp;
                                                    <span data-original-title='{{ trans('langSeenToCourseHome') }}' data-toggle='tooltip' data-placement='bottom' class='label label-primary'>
                                                        <i class='fa fa-eye'></i>
                                                    </span>
                                                @endif
                                            </h3>      
                                        </div>
                                        <div class='panel-body'>
                                            {!! handleType($course_desc->type) !!} 
                                            <br>
                                            <br>
                                            {!! standard_text_escape($course_desc->comments) !!} 
                                        </div>            
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'><div class='alert alert-warning'>{{ trans('langThisCourseDescriptionIsEmpty') }}</div></div>
                        @endif

                    </div>
                </div>


        </div>
    </div>
</div>
@endsection
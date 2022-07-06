<?php 

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

                            <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/portfolio.php">{{trans('langPortfolio')}}</a></li>
                                    <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/my_courses.php">{{trans('mycourses')}}</a></li>
                                    <li class="breadcrumb-item"><a href="{{$urlServer}}courses/{{$course_code}}/index.php">{{$currentCourseName}}</a></li>
                                    <li class="breadcrumb-item"><a href="{{ $urlAppend }}modules/glossary/index.php?course={{$course_code}}">{{trans('langGlossary')}}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{trans('langFaculties')}}</li>
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
                                    
                            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="row p-2"></div><div class="row p-2"></div>
                                <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-list" aria-hidden="true"></i> {{$toolName}} {{trans('langsOfCourse')}} <<strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong>></span>
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

                            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 bg-white">{!! isset($action_bar) ?  $action_bar : '' !!}</div>
                            <div class="row p-2"></div>

                            @if(Session::has('message'))
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                                <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                    {{ Session::get('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </p>
                            </div>
                            @endif

                            @if (count($categories))
                                <div class='table-responsive glossary-categories' style="">    
                                    <table class='table' id="glossary_table" style="overflow: inherit">
                                        <thead class="notes_thead text-light">
                                            
                                            <tr>
                                                <th scope="col"><span class="notes_th_comment">#</span></th>
                                                <th scope="col"><span class="notes_th_comment">{{ trans('langName') }}</span></th>
                                                <th scope="col"><span class="notes_th_comment">Περιγραφή</span></th>
                                                @if($is_editor)
                                                
                                                        <th scope="col"><span class="notes_th_comment"><i class='fas fa-cogs'></i></span></th>
                                                    
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i=0; ?>
                                            
                                            @foreach ($categories as $category)
                                                <?php $i++; ?>
                                                <?php $tmp_edit_id = Database::get()->querySingle("SELECT * FROM `glossary_category` WHERE `glossary_category`.`name`='{$category->name}' ");?>
                                                <?php $edit_id = $tmp_edit_id->id; ?>

                                            <tr>
                                                
                                                <th scope="row">{{$i}}</th>
                                                <td>
                                                    <a href='{{ $base_url }}&amp;cat={{ getIndirectReference($category->id) }}'>
                                                        <strong> {{ $category->name }}</strong>
                                                    </a>
                                                </td>
                                                <td>
                                                    {!! $category->description !!}
                                                </td>
                                                <td>
                                                    @if($is_editor)
                                                      
                                                    {!! action_button(array(
                                                        array('title' => trans('langCategoryMod'),
                                                            'url' => "$cat_url&amp;edit=" . getIndirectReference($category->id),
                                                            'icon' => 'fa-edit'),
                                                        array('title' => trans('langCategoryDel'),
                                                            'url' => "$cat_url&amp;delete=" . getIndirectReference($category->id),
                                                            'icon' => 'fa-times',
                                                            'class' => 'delete',
                                                            'confirm' => trans('langConfirmDelete')
                                                            )
                                                        )
                                                    ) !!}    
                                                      
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else 
                                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'><div class='alert alert-warning'>{{trans('langAnalyticsNotAvailable')}} {{trans('langGlossary')}}.</div></div>
                            @endif
                                   
                            
                        </div>
                   
                </div>
            </div>
        </div>
    </div>
@endsection


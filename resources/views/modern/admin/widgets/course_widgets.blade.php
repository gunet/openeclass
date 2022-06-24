@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3 mobile_width">

    <div class="container-fluid main-container my_course_info_container">


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
                                    <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/portfolio.php">Χαρτοφυλάκιο</a></li>
                                    <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/my_courses.php">Τα μαθήματά μου</a></li>
                                    <li class="breadcrumb-item"><a href="{{$urlServer}}courses/{{$course_code}}/index.php">{{$currentCourseName}}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{$toolName}}</li>
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
                                <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-folder-open" aria-hidden="true"></i> {{$toolName}} <strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong></span>
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
                            
                            <div class='row'>
                                <div class="col-4">
                                    <h3 class="control-label-notes">{{ trans('langInstalledWidgets') }}</h3>
                                    <hr>
                                    @if (count($installed_widgets))
                                    <div id="widgets">
                                        @foreach ($installed_widgets as $key => $installed_widget)
                                                <div class="panel panel-success widget mt-3" data-widget-id="{{ $installed_widget->id }}">
                                                    <div class="panel-heading notes_thead pt-2 ps-3 pb-3 pe-3 text-center">                   
                                                        <a data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" 
                                                        href="#widget_desc_{{ $key }}" class="text-white widget_title">
                                                            {{ $installed_widget->getName() }} <span></span> <span class="pull-right"></span>
                                                        </a>                     
                                                    </div>
                                                    <div id="widget_desc_{{ $key }}" class="panel-collapse collapse">
                                                        <div class="panel-body panel-body-admin ps-3 pb-3 pt-3 pe-3 text-muted">
                                                            {{ $installed_widget->getDesc() }}
                                                        </div>
                                                        <div class="panel-footer clearfix">
                                                            <div class="pull-right">
                                                                <form action='{{ $_SERVER['SCRIPT_NAME'] }}' id="uninstallForm{{ $key }}" method="post">
                                                                    <input type="hidden" name='widgetClassName' value='{{ get_class($installed_widget) }}'>
                                                                    <input type="hidden" name='widgetAction' value='uninstall'>
                                                                </form>
                                                                <a class='text-danger' href="#" onclick="$('#uninstallForm{{ $key }}').submit();">
                                                                    <small>{{ trans('langWidgetUninstall') }}</small>
                                                                </a>                               
                                                            </div>                      
                                                        </div>                        
                                                    </div>
                                                    <div class="panel-collapse collapse in hidden">
                                                        <div class="panel-body">

                                                        </div>
                                                        <div class="panel-footer clearfix">
                                                            <a href="#" class="remove">
                                                                <small>{{ trans('langDelete') }}</small>
                                                            </a>
                                                            <div class="pull-right">
                                                                <a href="#" class="btn btn-xs btn-primary submitOptions">
                                                                    {{ trans('langSubmit') }}
                                                                </a>                                
                                                            </div>                    
                                                        </div>                        
                                                    </div>                    
                                                </div>
                                    @endforeach
                                    </div>   
                                    @else
                                    <div class='text-warning margin-bottom-fat'>
                                        {{ trans('langNoInstalledWidgets') }}
                                    </div>       
                                    @endif      
                                </div>    
                                <div class="col-8">
                                    @include('admin.widgets.common.courseHomeMain')
                                    @include('admin.widgets.common.courseHomeSide')                   
                                </div>
                            </div>    
                            
                </div>
            </div>
        </div>
    </div>
</div>
                            

@endsection


@extends('layouts.default')

@section('content')
  
<div class="col-12 main-section">
<div class="{{ $container }} @if($course_code and $currentCourseName and !isset($_GET['fromFlipped'])) module-container py-lg-0 @else main-container @endif">
    <div class="@if($course_code and $currentCourseName and !isset($_GET['fromFlipped'])) course-wrapper d-lg-flex align-items-lg-strech w-100 @else row m-auto @endif">

        @if($course_code and $currentCourseName and !isset($_GET['fromFlipped']))
            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>
        @endif

        @if($course_code and $currentCourseName and !isset($_GET['fromFlipped']))
            <div class="col_maincontent_active">
        @else
            <div class="col-12">
        @endif
                <div class="row">
                
                    @if(isset($_SESSION['uid']))
                        
                             @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                        
                    @else
                        
                            @if($course_code and !$is_in_tinymce and $currentCourseName and !isset($_GET['fromFlipped']))
                                <nav class="me-lg-0 me-2">
                                    <a class="btn d-lg-none p-0" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools">
                                        <img src='{{ $urlAppend }}template/modern/img/Icons_menu-collapse.svg' alt="Open Course Tools"/>
                                    </a>
                                </nav>
                            @endif
                        
                    @endif

                    @if($course_code and $currentCourseName)
                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>
                    @endif
                    
                    @include('layouts.partials.legend_view')

                    @include('layouts.partials.show_alert') 
                    
                    <div class='col-12'>{!! $tool_content !!}</div>
                </div>    
        </div>
    </div>
</div>
</div>

@endsection




@extends('layouts.default')

@section('content')

    <div class="pb-3 pt-3">

        <div class="container-fluid main-container">

            <div class="row">

                @if(!$allCoursesAnnouncements)

                        <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                            <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>

                        <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
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


                                @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                                

                                @if($is_editor)
                    
                                    <div class="col-xl-12 col-md-12 col-sm-12 col-xs-12">
                                        <a class="float-end btn btn-success" href="{{ $urlAppend }}modules/announcements/new.php?course={{$course_code}}"><i class="fa fa-plus" aria-hidden="true"></i> {{trans('langAddAnn')}}</a>
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

                        <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                            <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                            @if($course_code)
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            @else
                                @include('layouts.partials.sidebarAdmin')
                            @endif 
                            </div>
                        </div>

                        <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
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


                                @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

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

                @endif
            </div>
        </div>
    </div>
    

@endsection

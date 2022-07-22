@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @if($course_code)
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    @else
                        @include('layouts.partials.sidebarAdmin')
                    @endif 
                </div>
            </div>

            @if($course_code)
            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
            @else
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
            @endif

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

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
                    @endif

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if($course_code)
                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>
                    @endif

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    <div class='row ps-5 pe-4 pt-5 pb-4'>
                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                            <h3 class="control-label-notes">{{ trans('langInstalledWidgets') }}</h3>
                            <hr>
                            @if (count($installed_widgets))
                            <div id="widgets">
                                @foreach ($installed_widgets as $key => $installed_widget)
                                        <div class="panel panel-success widget mt-3" data-widget-id="{{ $installed_widget->id }}">
                                            <div class="panel-heading">                   
                                                <a data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" 
                                                href="#widget_desc_{{ $key }}" class="text-white ps-2">
                                                    {{ $installed_widget->getName() }} <span class='fa fa-arrow-down fs-6 ps-2'></span>
                                                </a>                     
                                            </div>
                                            <div id="widget_desc_{{ $key }}" class="panel-collapse collapse">
                                                <div class="panel-body ps-3 pt-3 pb-3 pe-3 text-muted">
                                                    {{ $installed_widget->getDesc() }}
                                                </div>
                                                <div class="panel-footer ps-3 pt-3 pb-3 pe-3 clearfix">
                                                    <div class="pull-right">
                                                        <form action='{{ $_SERVER['SCRIPT_NAME'] }}' id="uninstallForm{{ $key }}" method="post">
                                                            <input type="hidden" name='widgetClassName' value='{{ get_class($installed_widget) }}'>
                                                            <input type="hidden" name='widgetAction' value='uninstall'>
                                                        </form>
                                                        <a href="#" onclick="$('#uninstallForm{{ $key }}').submit();">
                                                            <small>{{ trans('langWidgetUninstall') }}</small>
                                                        </a>                               
                                                    </div>                      
                                                </div>                        
                                            </div>
                                            <div class="panel-collapse collapse in hidden">
                                                <div class="panel-body ps-3 pt-3 pb-3 pe-3">

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
                        <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                            @include('admin.widgets.common.portfolioMain')
                            @include('admin.widgets.common.portfolioSide')                   
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    

                            

@endsection


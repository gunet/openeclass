@extends('layouts.default')

@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

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
                        @include('layouts.partials.sidebarAdmin')
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


                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            <h3 class="control-label-notes">{{ trans('langInstalledWidgets') }}</h3>
                            <hr>
                            @if (count($installed_widgets))
                            <div id="widgets">
                                @foreach ($installed_widgets as $key => $installed_widget)
                                        <div class="panel panel-success widget mt-2" data-widget-id="{{ $installed_widget->id }}">
                                            <div class="panel-heading notes_thead pt-2 pb-2 ps-3 pe-3">
                                                <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}"
                                                href="#widget_desc_{{ $key }}" class="widget_title">
                                                    {{ $installed_widget->getName() }} <span></span> <span class="pull-right"></span>
                                                    <span class='fa fa-arrow-down ps-2 fs-6'></span>
                                                </a>
                                            </div>
                                            <div id="widget_desc_{{ $key }}" class="panel-collapse collapse">
                                                <div class="panel-body text-muted panel-body-admin p-3">
                                                    {{ $installed_widget->getDesc() }}
                                                </div>
                                                <div class="panel-footer clearfix">
                                                    <div class="pull-right">
                                                        <form action='{{ $_SERVER['SCRIPT_NAME'] }}' id="uninstallForm{{ $key }}" method="post">
                                                            <input type="hidden" name='widgetClassName' value='{{ get_class($installed_widget) }}'>
                                                            <input type="hidden" name='widgetAction' value='uninstall'>
                                                        </form>
                                                        <a class='text-danger fs-6' href="#" onclick="$('#uninstallForm{{ $key }}').submit();">
                                                            <small>{{ trans('langWidgetUninstall') }}</small>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="panel-collapse collapse in hidden">
                                                <div class="panel-body">

                                                </div>
                                                <div class="panel-footer clearfix">
                                                    <a href="#" class="remove text-danger">
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

                            <h3 class="control-label-notes mt-5">{{ trans('langAvailableWidgets') }}</h3>
                            <hr>
                            @if (count($uninstalled_widgets))
                            <div>
                                @foreach ($uninstalled_widgets as $key => $uninstalled_widget)
                                    <div class="panel panel-default panel-default-admin mt-3">
                                        <div class="panel-heading notes_thead ps-3 pb-3 pt-2 pe-3">
                                            <span class="text-white">
                                                {{ $uninstalled_widget->getName() }}
                                            </span>

                                        </div>
                                        <div class="panel-body panel-body-admin">
                                            <span class="text-white">
                                            {{ $uninstalled_widget->getDesc() }}
                                            </span>
                                        </div>
                                        <div class="panel-footer clearfix">
                                            <div class="pull-right">
                                                <form action='{{ $_SERVER['SCRIPT_NAME'] }}' id="installForm{{ $key }}" method="post">
                                                    <input type="hidden" name='widgetClassName' value='{{ get_class($uninstalled_widget) }}'>
                                                    <input type="hidden" name='widgetAction' value='install'>
                                                </form>
                                                <a class='text-success' href="#" onclick="$('#installForm{{ $key }}').submit();">
                                                    <small>{{ trans('langWidgetInstall') }}</small>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @else
                            <div class='text-warning margin-bottom-fat'>
                                {{ trans('langNoAvailableWidgets') }}
                            </div>
                            @endif
                        </div>
                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5">

                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active nav-item"><a class='nav-link nav-link-adminTools' href="#home" aria-controls="home" role="tab" data-bs-toggle="tab">Αρχική</a></li>
                                    <li role="presentation" class='nav-item'><a class='nav-link nav-link-adminTools' href="#portfolio" aria-controls="portfolio" role="tab" data-bs-toggle="tab">Χαρτοφυλάκιο</a></li>
                                    <li role="presentation" class='nav-item'><a class='nav-link nav-link-adminTools' href="#course_home" aria-controls="course_home" role="tab" data-bs-toggle="tab">Αρχική Μαθήματος</a></li>
                                </ul>

                            <!-- Tab panes -->
                                <div class="tab-content mt-2">
                                    <div role="tabpanel" class="tab-pane active" id="home">
                                        @include('admin.widgets.common.homePageMain')
                                        @include('admin.widgets.common.homePageSide')
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="portfolio">
                                        @include('admin.widgets.common.portfolioMain')
                                        @include('admin.widgets.common.portfolioSide')
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="course_home">
                                        @include('admin.widgets.common.courseHomeMain')
                                        @include('admin.widgets.common.courseHomeSide')
                                    </div>
                                </div>
                        </div>


                </div>
            </div>
        </div>
    </div>
</div>


@endsection


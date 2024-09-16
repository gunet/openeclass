@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                <nav id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                    <div class="d-none d-lg-block ContentLeftNav">
                        @php $dont_display_array_in_sidebar = ''; @endphp

                        @if($count_home_sidebar_widgets >= 0)
                            @php $dont_display_array_in_sidebar = 1; @endphp
                        @endif
                          
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor, 'dont_display_array_in_sidebar' => $dont_display_array_in_sidebar])
                    </div>
                </nav>

                <div class="col_maincontent_active">

                        <div class="row">

                            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                                <div class="offcanvas-header">
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                                </div>
                                <div class="offcanvas-body">
                                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                </div>
                            </div>

                            @include('layouts.partials.legend_view')

                            @include('layouts.partials.show_alert') 

                            <div class="col-md-4 col-12">
                                <h3 class="">{{ trans('langInstalledWidgets') }}</h3>
                                <hr>
                                @if (count($installed_widgets) > 0)
                                <div id="widgets">
                                    @foreach ($installed_widgets as $key => $installed_widget)
                                            <div class="panel panel-success widget mt-3" data-widget-id="{{ $installed_widget->id }}">
                                                <div class="panel-heading text-center Borders">
                                                    <a data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" href="#widget_desc_{{ $key }}" class="text-white TextMedium widget_title">
                                                        {{ $installed_widget->getName() }}
                                                        <span class="fa fa-arrow-down fs-6"></span>
                                                    </a>
                                                </div>
                                                <div id="widget_desc_{{ $key }}" class="panel-collapse collapse">
                                                    <div class="panel-body text-muted">
                                                        {{ $installed_widget->getDesc() }}
                                                    </div>
                                                    <div class="panel-footer clearfix">
                                                        <div class="float-end">
                                                            <form class='mb-0 mt-0' action='{{ $_SERVER['SCRIPT_NAME'] }}' id="uninstallForm{{ $key }}" method="post">
                                                                <input type="hidden" name='widgetClassName' value='{{ get_class($installed_widget) }}'>
                                                                <input type="hidden" name='widgetAction' value='uninstall'>
                                                            </form>
                                                            <a class='btn deleteAdminBtn' href="#" onclick="$('#uninstallForm{{ $key }}').submit();">
                                                                {{ trans('langWidgetUninstall') }}
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
                                                        <div class="float-end">
                                                            <a href="#" class="btn submitAdminBtn submitOptions">
                                                                {{ trans('langSubmit') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                @endforeach
                                </div>
                                @else
                                <div class="alert alert-warning">
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoInstalledWidgets') }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-8 col-12 mt-md-0 mt-4">
                                @include('admin.widgets.common.courseHomeMain',['final_data_courseHomePageMain_widget' => $final_data_courseHomePageMain_widget])
                                @include('admin.widgets.common.courseHomeSide',['final_data_courseHomeSide_widget' => $final_data_courseHomeSide_widget])
                            </div>


                </div>
            </div>
        </div>
    
</div>
</div>

@endsection


@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp

                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif


                        <div class="col-12">
                            <h3 class="">{{ trans('langInstalledWidgets') }}</h3>
                            <hr>
                            @if (count($installed_widgets))
                            <div id="widgets">
                                @foreach ($installed_widgets as $key => $installed_widget)
                                        <div class="panel panel-success widget mt-3" data-widget-id="{{ $installed_widget->id }}">
                                            <div class="panel-heading">
                                                <a class='text-white TextMedium collapsed' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}"
                                                href="#widget_desc_{{ $key }}" class="widget_title">
                                                    {{ $installed_widget->getName() }} <span></span> <span class="float-end"></span>
                                                    <span class='fa fa-arrow-down ps-2 fs-6'></span>
                                                </a>
                                            </div>
                                            <div id="widget_desc_{{ $key }}" class="panel-collapse collapse">
                                                <div class="panel-body NoBorders">
                                                    {{ $installed_widget->getDesc() }}
                                                </div>
                                                <div class="panel-footer clearfix BordersBottom">
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
                                                    <a href="#" class="remove text-danger">
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

                            <h3 class=" mt-5">{{ trans('langAvailableWidgets') }}</h3>
                            <hr>
                            @if (count($uninstalled_widgets))
                            <div>
                                @foreach ($uninstalled_widgets as $key => $uninstalled_widget)
                                    <div class="panel panel-default panel-default-admin mt-3">
                                        <div class="panel-heading">
                                            <h3 class='mb-0'>
                                                {{ $uninstalled_widget->getName() }}
                                            </h3>

                                        </div>
                                        <div class="panel-body">
                                            <span class="Neutral-900-cl">
                                            {{ $uninstalled_widget->getDesc() }}
                                            </span>
                                        </div>
                                        <div class="panel-footer clearfix">
                                            <div class="float-end">
                                                <form class='mb-0 mt-0' action='{{ $_SERVER['SCRIPT_NAME'] }}' id="installForm{{ $key }}" method="post">
                                                    <input type="hidden" name='widgetClassName' value='{{ get_class($uninstalled_widget) }}'>
                                                    <input type="hidden" name='widgetAction' value='install'>
                                                </form>
                                                <a class='btn submitAdminBtn' href="#" onclick="$('#installForm{{ $key }}').submit();">
                                                    {{ trans('langWidgetInstall') }}
                                                </a>
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
                        <div class="col-12 mt-5">

                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="nav-item"><a class='nav-link active' href="#home" aria-controls="home" role="tab" data-bs-toggle="tab">{{ trans('langHome')}} </a></li>
                                    <li role="presentation" class='nav-item'><a class='nav-link' href="#portfolio" aria-controls="portfolio" role="tab" data-bs-toggle="tab">{{ trans('langPortfolio')}}</a></li>
                                    <li role="presentation" class='nav-item'><a class='nav-link' href="#course_home" aria-controls="course_home" role="tab" data-bs-toggle="tab">{{ trans('langHome')}}&nbsp;{{ trans('langOfCourse')}}</a></li>
                                </ul>

                            <!-- Tab panes -->
                                <div class="tab-content mt-2">
                                    <div role="tabpanel" class="tab-pane active" id="home">
                                        @include('admin.widgets.common.homePageMain',['final_data_homepagePageMain_widget' => $final_data_homepagePageMain_widget])
                                        @include('admin.widgets.common.homePageSide',['final_data_homepageSide_widget' => $final_data_homepageSide_widget])
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="portfolio">
                                        @include('admin.widgets.common.portfolioMain',['final_data_portfolioPageMain_widget' => $final_data_portfolioPageMain_widget])
                                        @include('admin.widgets.common.portfolioSide',['final_data_portfolioSide_widget' => $final_data_portfolioSide_widget])
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="course_home">
                                        @include('admin.widgets.common.courseHomeMain',['final_data_courseHomePageMain_widget' => $final_data_courseHomePageMain_widget])
                                        @include('admin.widgets.common.courseHomeSide',['final_data_courseHomeSide_widget' => $final_data_courseHomeSide_widget])
                                    </div>
                                </div>
                        </div>



        </div>

</div>
</div>

@endsection

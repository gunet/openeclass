@extends('layouts.default')

@section('content')


<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
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
                                            <div class="panel-heading">                   
                                                <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" 
                                                href="#widget_desc_{{ $key }}" class="widget_title">
                                                    {{ $installed_widget->getName() }} <span></span> <span class="float-end"></span>
                                                    <span class='fa fa-arrow-down ps-2 fs-6'></span>
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
                                                    <div class="float-end">
                                                        <a href="#" class="btn btn-sm btn-primary submitOptions">
                                                            {{ trans('langSubmit') }}
                                                        </a>                                
                                                    </div>                    
                                                </div>                        
                                            </div>                    
                                        </div>
                            @endforeach
                            </div>   
                            @else
                            <div class='orangeText margin-bottom-fat'>
                                {{ trans('langNoInstalledWidgets') }}
                            </div>       
                            @endif
                            
                            <h3 class="control-label-notes mt-5">{{ trans('langAvailableWidgets') }}</h3>
                            <hr>
                            @if (count($uninstalled_widgets))
                            <div>           
                                @foreach ($uninstalled_widgets as $key => $uninstalled_widget)
                                    <div class="panel panel-default panel-default-admin mt-3">
                                        <div class="panel-heading">                   
                                            <span class="text-secondary">
                                                {{ $uninstalled_widget->getName() }}
                                            </span>

                                        </div>
                                        <div class="panel-body">
                                            <span class="text-secondary">
                                            {{ $uninstalled_widget->getDesc() }}
                                            </span>
                                        </div>
                                        <div class="panel-footer clearfix">
                                            <div class="float-end">
                                                <form class='mb-0 mt-0' action='{{ $_SERVER['SCRIPT_NAME'] }}' id="installForm{{ $key }}" method="post">
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
                            <div class='orangeText margin-bottom-fat'>
                                {{ trans('langNoAvailableWidgets') }}
                            </div>       
                            @endif        
                        </div>    
                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5">

                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="nav-item"><a class='nav-link active nav-link-adminTools' href="#home" aria-controls="home" role="tab" data-bs-toggle="tab">Αρχική</a></li>
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


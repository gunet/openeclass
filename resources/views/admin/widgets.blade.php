@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-4">
        <h3 class="content-title">{{ trans('langInstalledWidgets') }}</h3>
        <hr>
        @if (count($installed_widgets))
        <div id="widgets">
            @foreach ($installed_widgets as $key => $installed_widget)
                    <div class="panel panel-success widget" data-widget-id="{{ $installed_widget->id }}">
                        <div class="panel-heading">                   
                            <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                               href="#widget_desc_{{ $key }}" class="widget_title">
                              {{ $installed_widget->getName() }}
                            </a>                     
                        </div>
                        <div id="widget_desc_{{ $key }}" class="panel-collapse collapse">
                            <div class="panel-body text-muted">
                                {{ $installed_widget->getDesc() }}
                            </div>
                            <div class="panel-footer clearfix">
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
                        <div id="widget_form" class="panel-collapse collapse in hidden">
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
            Δεν υπάρχουν εγκατεστημένα widgets
        </div>       
        @endif
           


        <h3 class="content-title">{{ trans('langAvailableWidgets') }}</h3>
        <hr>
        @if (count($uninstalled_widgets))
        <div>           
            @foreach ($uninstalled_widgets as $key => $uninstalled_widget)
                <div class="panel panel-default">
                    <div class="panel-heading">                   
                        <span class="text-muted">
                            {{ $uninstalled_widget->getName() }}
                        </span>

                    </div>
                    <div class="panel-body">
                        <span class="text-muted">
                        {{ $uninstalled_widget->getDesc() }}
                        </span>
                    </div>
                    <div class="panel-footer clearfix">
                        <div class="pull-right">
                            <form action='{{ $_SERVER['SCRIPT_NAME'] }}' id="installForm{{ $key }}" method="post">
                                <input type="hidden" name='widgetClassName' value='{{ get_class($uninstalled_widget) }}'>
                                <input type="hidden" name='widgetAction' value='install'>
                            </form>
                            <a href="#" onclick="$('#installForm{{ $key }}').submit();">
                                <small>{{ trans('langWidgetInstall') }}</small>
                            </a>                               
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class='text-warning margin-bottom-fat'>
            Δεν υπάρχουν διαθέσιμα προς εγκατάσταση widgets
        </div>       
        @endif        
    </div>    
    <div class="col-xs-8">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
              <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Αρχική</a></li>
              <li role="presentation"><a href="#portfolio" aria-controls="portfolio" role="tab" data-toggle="tab">Χαρτοφυλάκιο</a></li>
              <li role="presentation"><a href="#course_home" aria-controls="course_home" role="tab" data-toggle="tab">Αρχική Μαθήματος</a></li>
            </ul>

          <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="home">
                <div class="panel panel-primary margin-top-fat">
                    <div class="panel-heading">
                        <h4 class="panel-title">                
                            {{ trans('langHomePageMainContent') }}
                        </h4>
                    </div>
                    <div class="panel-body" id="home_widget_main" data-widget-area-id="1">
                        @foreach ($home_main_area_widgets as $key => $home_main_area_widget)
                        <div class="panel panel-success widget" data-widget-id="{{ $home_main_area_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                            <div class="panel-heading">                   
                                <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                                   href="#widget_desc_{{ $key }}" class="widget_title">
                                    {{ $home_main_area_widget->getName() }} <span></span>
                                </a>                     
                            </div>
                            <div id="widget_form" class="panel-collapse collapse in">
                                <div class="panel-body">
                                    {!! $home_main_area_widget->getOptionsForm($key) !!}
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
                </div>
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h4 class="panel-title">                
                            {{ trans('langHomePageSidebarContent') }}
                        </h4>
                    </div>
                    <div class="panel-body" id="home_widget_sidebar" data-widget-area-id="2">
                        @foreach ($home_sidebar_widgets as $key => $home_sidebar_widget)
                        <div class="panel panel-success widget" data-widget-id="{{ $home_sidebar_widget->widget_id }}" data-widget-widget-area-id="{{ $key }}">
                            <div class="panel-heading">                   
                                <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                                   href="#widget_desc_{{ $key }}" class="widget_title">
                                  {{ $home_sidebar_widget->getName() }}
                                </a>                     
                            </div>
                            <div id="widget_form" class="panel-collapse collapse in">
                                <div class="panel-body">
                                    {!! $home_sidebar_widget->getOptionsForm($key) !!}
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
                </div>                  
              </div>
                <div role="tabpanel" class="tab-pane" id="portfolio">
                    <div class="panel panel-primary margin-top-fat">
                        <div class="panel-heading">
                            <h4 class="panel-title">                
                                {{ trans('langPortfolioMainContent') }}
                            </h4>
                        </div>                  
                        <div class="panel-body" id="portfolio_widget_main" data-widget-area-id="3">
                            @foreach ($portfolio_main_area_widgets as $key => $portfolio_main_area_widget)
                            <div class="panel panel-success widget" data-widget-id="{{ $portfolio_main_area_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                                <div class="panel-heading">                   
                                    <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                                       href="#widget_desc_{{ $key }}" class="widget_title">
                                        {{ $portfolio_main_area_widget->getName() }} <span></span>
                                    </a>                     
                                </div>
                                <div id="widget_form" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        {!! $portfolio_main_area_widget->getOptionsForm($key) !!}
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
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4 class="panel-title">                
                                {{ trans('langPortfolioSidebarContent') }}
                            </h4>
                        </div>                  
                        <div class="panel-body" id="portfolio_widget_sidebar" data-widget-area-id="4">
                            @foreach ($portfolio_sidebar_widgets as $key => $portfolio_sidebar_widget)
                            <div class="panel panel-success widget" data-widget-id="{{ $portfolio_sidebar_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                                <div class="panel-heading">                   
                                    <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                                       href="#widget_desc_{{ $key }}" class="widget_title">
                                        {{ $portfolio_sidebar_widget->getName() }} <span></span>
                                    </a>                     
                                </div>
                                <div id="widget_form" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        {!! $portfolio_sidebar_widget->getOptionsForm($key) !!}
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
                    </div>                    
                </div>                  
                <div role="tabpanel" class="tab-pane" id="course_home">
                    <div class="panel panel-primary margin-top-fat">
                        <div class="panel-heading">
                            <h4 class="panel-title">                
                                {{ trans('langCourseHomeMainContent') }}
                            </h4>
                        </div>                  
                        <div class="panel-body" id="course_home_widget_main" data-widget-area-id="5">
                            @foreach ($course_home_main_area_widgets as $key => $course_home_main_area_widget)
                            <div class="panel panel-success widget" data-widget-id="{{ $course_home_main_area_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                                <div class="panel-heading">                   
                                    <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                                       href="#widget_desc_{{ $key }}" class="widget_title">
                                        {{ $course_home_main_area_widget->getName() }} <span></span>
                                    </a>                     
                                </div>
                                <div id="widget_form" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        {!! $course_home_main_area_widget->getOptionsForm($key) !!}
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
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4 class="panel-title">                
                                {{ trans('langCourseHomeSidebarContent') }}
                            </h4>
                        </div>                  
                        <div class="panel-body" id="course_home_widget_sidebar" data-widget-area-id="6">
                            @foreach ($course_home_sidebar_widgets as $key => $course_home_sidebar_widget)
                            <div class="panel panel-success widget" data-widget-id="{{ $course_home_sidebar_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                                <div class="panel-heading">                   
                                    <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                                       href="#widget_desc_{{ $key }}" class="widget_title">
                                        {{ $course_home_sidebar_widget->getName() }} <span></span>
                                    </a>                     
                                </div>
                                <div id="widget_form" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        {!! $course_home_sidebar_widget->getOptionsForm($key) !!}
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
                    </div>                    
                </div>
            </div>
    
        
    </div>    
</div>
                            

@endsection


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
                                {{ $installed_widget->getName() }} <span></span>
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
            {{ trans('langNoInstalledWidgets') }}
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
            {{ trans('langNoAvailableWidgets') }}
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
                            

@endsection


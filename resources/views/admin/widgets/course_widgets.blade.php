@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-4">
        <h3 class="content-title">{{ trans('langInstalledWidgets') }}</h3>
        <hr>
        @if (count($installed_widgets))
        <div id="widgets">
            @foreach ($installed_widgets as $key => $installed_widget)
                    <div class="panel panel-success widget" data-widget-id="{{ $installed_widget->id }}">
                        <div class="panel-heading">                   
                            <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                               href="#widget_desc_{{ $key }}" class="widget_title">
                                {{ $installed_widget->getName() }} <span></span> <span class="float-end"></span>
                            </a>                     
                        </div>
                        <div id="widget_desc_{{ $key }}" class="panel-collapse collapse">
                            <div class="panel-body text-muted">
                                {{ $installed_widget->getDesc() }}
                            </div>
                            <div class="panel-footer clearfix">
                                <div class="float-end">
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
                            <div class="panel-body">

                            </div>
                            <div class="panel-footer clearfix">
                                <a href="#" class="remove">
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
    </div>    
    <div class="col-8">
        @include('admin.widgets.common.courseHomeMain')
        @include('admin.widgets.common.courseHomeSide')                   
    </div>    
</div>
                            

@endsection


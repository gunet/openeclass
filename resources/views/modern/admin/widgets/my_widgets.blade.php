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
                                                <div class="panel-body text-muted">
                                                    {{ $installed_widget->getDesc() }}
                                                </div>
                                                <div class="panel-footer">
                                                    <div class="text-end">
                                                        <form class='mt-0 mb-0' action='{{ $_SERVER['SCRIPT_NAME'] }}' id="uninstallForm{{ $key }}" method="post">
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


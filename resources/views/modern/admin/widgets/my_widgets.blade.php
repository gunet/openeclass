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


                        <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12">
                            <h3 class="">{{ trans('langInstalledWidgets') }}</h3>
                            <hr>
                            @if (count($installed_widgets))
                            <div id="widgets">
                                @foreach ($installed_widgets as $key => $installed_widget)
                                        <div class="panel panel-success widget mt-3" data-widget-id="{{ $installed_widget->id }}">
                                            <div class="panel-heading Borders">
                                                <a class='text-white TextMedium' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}"
                                                href="#widget_desc_{{ $key }}" class="ps-2">
                                                    {{ $installed_widget->getName() }} <span class='fa fa-arrow-down fs-6 ps-2'></span>
                                                </a>
                                            </div>
                                            <div id="widget_desc_{{ $key }}" class="panel-collapse collapse">
                                                <div class="panel-body NoBorders">
                                                    {{ $installed_widget->getDesc() }}
                                                </div>
                                                <div class="panel-footer BordersBottom">
                                                    <div class="d-flex justify-content-end">
                                                        <form class='mt-0 mb-0' action='{{ $_SERVER['SCRIPT_NAME'] }}' id="uninstallForm{{ $key }}" method="post">
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
                        <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">

                                @include('admin.widgets.common.portfolioMain',['final_data_portfolioPageMain_widget' => $final_data_portfolioPageMain_widget])
                                @include('admin.widgets.common.portfolioSide',['final_data_portfolioSide_widget' => $final_data_portfolioSide_widget])
                        </div>


        </div>

</div>
</div>



@endsection


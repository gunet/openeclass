                <div class="panel panel-primary margin-top-fat mt-3">
                    <div class="panel-heading">
                        <h4 class="panel-title">                
                            {{ trans('langHomePageMainContent') }}
                        </h4>
                    </div>
                    <div class="panel-body" id="home_widget_main" data-widget-area-id="1">
                        @foreach ($home_main_area_widgets as $key => $home_main_area_widget)
                        <div class="panel panel-success widget" data-widget-id="{{ $home_main_area_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                            <div class="panel-heading">                   
                                <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" 
                                   href="#widget_desc_{{ $key }}" class="widget_title">
                                    {{ $home_main_area_widget->getName() }} <span></span>
                                </a>                     
                            </div>
                            <div class="panel-collapse collapse in">
                                <div class="panel-body">
                                    {!! $home_main_area_widget->getOptionsForm($key) !!}
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
                </div>
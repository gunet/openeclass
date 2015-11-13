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
                                    {{ $home_sidebar_widget->getName() }} <span></span>
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


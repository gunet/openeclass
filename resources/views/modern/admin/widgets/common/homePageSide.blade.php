                <div class="panel panel-primary mt-3">
                    <div class="panel-heading">
                        <h4 class="panel-title">                
                            {{ trans('langHomePageSidebarContent') }}
                        </h4>
                    </div>
                    <div class="panel-body Borders" id="home_widget_sidebar" data-widget-area-id="2">
                        @foreach ($home_sidebar_widgets as $key => $home_sidebar_widget)
                        <div class="panel panel-success widget mt-3" data-widget-id="{{ $home_sidebar_widget->widget_id }}" data-widget-widget-area-id="{{ $key }}">
                            <div class="panel-heading">                   
                                <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" 
                                   href="#widget_desc_{{ $key }}" class="widget_title">
                                    {{ $home_sidebar_widget->getName() }} <span></span>
                                </a>                     
                            </div>
                            <div id="widget_desc_{{ $key }}" class="panel-collapse collapse in collapsed">
                                <div class="panel-body">
                                    {!! $home_sidebar_widget->getOptionsForm($key) !!}
                                </div>
                                <div class="panel-footer clearfix">
                                    <a href="#" class="remove btn deleteAdminBtn">
                                        {{ trans('langDelete') }}
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


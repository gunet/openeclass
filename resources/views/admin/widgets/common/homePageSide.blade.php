                <div class="panel panelCard card-default px-lg-4 py-lg-3 mt-3">
                    <div class="panel-heading border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <h3 class='mb-0'>
                            {{ trans('langHomePageSidebarContent') }}
                        </h3>
                    </div>
                    <div class="panel-body" id="home_widget_sidebar" data-widget-area-id="2">
                        @php $countWidgets = 0; @endphp
                        @foreach ($home_sidebar_widgets as $key => $home_sidebar_widget)
                        <div class="panel panel-success widget mb-3" data-widget-id="{{ $home_sidebar_widget->widget_id }}" data-widget-widget-area-id="{{ $key }}">
                            <div class="panel-heading">                   
                                <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" 
                                   href="#widget_desc_{{ $key }}" class="widget_title">
                                    {{ $home_sidebar_widget->getName() }} 
                                    <span class='fa fa-arrow-down ms-1'></span>
                                </a>                     
                            </div>
                            <div id="widget_desc_{{ $key }}" class="panel-collapse collapse in collapsed">
                                <div class="panel-body">
                                    {!! $home_sidebar_widget->getOptionsForm($key,$final_data_homepageSide_widget[$countWidgets]) !!}
                                </div>
                                <div class="panel-footer clearfix d-flex justify-content-start align-items-center gap-2 flex-wrap">
                                    <a href="#" class="remove btn deleteAdminBtn">
                                        {{ trans('langDelete') }}
                                    </a>
                                  
                                    <a href="#" class="btn submitAdminBtn submitOptions ms-1">
                                        {{ trans('langSubmit') }}
                                    </a>                                
                                                      
                                </div>                        
                            </div>                    
                        </div>        
                        @php $countWidgets++; @endphp        
                        @endforeach        
                    </div>
                </div>


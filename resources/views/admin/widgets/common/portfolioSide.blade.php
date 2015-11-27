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
                                <div class="panel-collapse collapse in">
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


                    <div class="panel panel-primary mt-3">
                        <div class="panel-heading notes_thead">
                            <h4 class="panel-title text-white">                
                                {{ trans('langCourseHomeSidebarContent') }}
                            </h4>
                        </div>                  
                        <div class="panel-body panel-body-admin ps-3 pt-3 pb-3 pe-3" id="course_home_widget_sidebar" data-widget-area-id="6">
                            @foreach ($course_home_sidebar_widgets as $key => $course_home_sidebar_widget)
                            <div class="panel panel-success widget" data-widget-id="{{ $course_home_sidebar_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                                <div class="panel-heading">                   
                                    <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" 
                                       href="#widget_desc_{{ $key }}" class="widget_title">
                                        {{ $course_home_sidebar_widget->getName() }} <span></span>
                                    </a>                     
                                </div>
                                <div class="panel-collapse collapse in">
                                    <div class="panel-body panel-body-admin ps-3 pt-3 pb-3 pe-3">
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


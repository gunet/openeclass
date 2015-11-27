                    <div class="panel panel-primary margin-top-fat">
                        <div class="panel-heading">
                            <h4 class="panel-title">                
                                {{ trans('langCourseHomeMainContent') }}
                            </h4>
                        </div>                  
                        <div class="panel-body" id="course_home_widget_main" data-widget-area-id="5">
                            @foreach ($course_home_main_area_widgets as $key => $course_home_main_area_widget)
                            <div class="panel{{!isset($courseWidgets) || isset($courseWidgets) && $course_home_main_area_widget->is_course_admin_widget ? ' panel-success widget' : ' panel-default'}}" data-widget-id="{{ $course_home_main_area_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                                <div class="panel-heading">                   
                                    <a style="text-decoration: none; display: block; color: #777;" data-toggle="collapse" data-target="#widget_desc_{{ $key }}" 
                                       href="#widget_desc_{{ $key }}" class="widget_title">
                                        {{ $course_home_main_area_widget->getName() }} <span></span> <small class="pull-right">{{ $course_home_main_area_widget->is_course_admin_widget ? trans('langWidgetCourse') : trans('langWidgetAdmin') }}</small>
                                    </a>                     
                                </div>
                                @if (!isset($courseWidgets) || isset($courseWidgets) && $course_home_main_area_widget->is_course_admin_widget)
                                <div class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        {!! $course_home_main_area_widget->getOptionsForm($key) !!}
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
                                @endif
                            </div>                
                            @endforeach
                        </div>
                    </div>


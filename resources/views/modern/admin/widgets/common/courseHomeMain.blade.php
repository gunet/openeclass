                    <div class="panel panel-primary margin-top-fat mt-3">
                        <div class="panel-heading">
                            <h4 class="panel-title text-white">                
                                {{ trans('langCourseHomeMainContent') }}
                            </h4>
                        </div>                  
                        <div class="panel-body panel-body-admin ps-3 pt-3 pb-3 pe-3" id="course_home_widget_main" data-widget-area-id="5">
                            @foreach ($course_home_main_area_widgets as $key => $course_home_main_area_widget)
                            <div class="panel{{!isset($courseWidgets) || isset($courseWidgets) && $course_home_main_area_widget->is_course_admin_widget ? ' panel-success widget' : ' panel-default'}}" data-widget-id="{{ $course_home_main_area_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                                <div class="panel-heading">                   
                                    <a class='text-white' data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" 
                                       href="#widget_desc_{{ $key }}" class="widget_title">
                                        {{ $course_home_main_area_widget->getName() }} <span></span> <small class="float-end">{{ $course_home_main_area_widget->is_course_admin_widget ? trans('langWidgetCourse') : trans('langWidgetAdmin') }}</small>
                                    </a>                     
                                </div>
                                @if (!isset($courseWidgets) || isset($courseWidgets) && $course_home_main_area_widget->is_course_admin_widget)
                                <div class="panel-collapse collapse in">
                                    <div class="panel-body panel-body-admin ps-3 pt-3 pb-3 pe-3">
                                        {!! $course_home_main_area_widget->getOptionsForm($key) !!}
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
                                @endif
                            </div>                
                            @endforeach
                        </div>
                    </div>


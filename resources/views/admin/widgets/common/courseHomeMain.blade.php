<div class="panel panelCard px-lg-4 py-lg-3 mt-3">
    <div class="panel-heading border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <h3 class='mb-0'>
            {{ trans('langCourseHomeMainContent') }}
        </h3>
    </div>
    <div class="panel-body" id="course_home_widget_main" data-widget-area-id="5">
        @php $countWidgets = 0; @endphp
        @foreach ($course_home_main_area_widgets as $key => $course_home_main_area_widget)
            <div class="panel{{!isset($courseWidgets) || isset($courseWidgets) && $course_home_main_area_widget->is_course_admin_widget ? ' panel-success widget' : ' panel-default'}} mb-3" data-widget-id="{{ $course_home_main_area_widget->id }}" data-widget-widget-area-id="{{ $key }}">
                <div class="panel-heading {{!isset($courseWidgets) || isset($courseWidgets) && $course_home_main_area_widget->is_course_admin_widget ? ' ' : ' rounded-2'}}">
                    <a data-bs-toggle="collapse" data-bs-target="#widget_desc_{{ $key }}" href="#widget_desc_{{ $key }}" class="text-white widget_title">
                        {{ $course_home_main_area_widget->getName() }} 
                        <span class='fa fa-arrow-down ms-1'></span>
                        <span class="float-end">{{ $course_home_main_area_widget->is_course_admin_widget ? trans('langWidgetCourse') : trans('langWidgetAdmin') }}</span>
                    </a>
                </div>
                @if (!isset($courseWidgets) || isset($courseWidgets) && $course_home_main_area_widget->is_course_admin_widget)
                    <div id="widget_desc_{{ $key }}" class="panel-collapse collapse in collapsed">
                        <div class="panel-body">
                            {!! $course_home_main_area_widget->getOptionsForm($key,$final_data_courseHomePageMain_widget[$countWidgets]) !!}
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
                @endif
            </div>
            @php $countWidgets++; @endphp
        @endforeach
    </div>
</div>


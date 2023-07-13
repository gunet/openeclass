
<div id="leftnav" class="col-12 sidebar float-menu pt-3">


    @php $is_course_teacher = check_editor($uid,$course_id); @endphp

    @if(($is_editor or $is_power_user or $is_departmentmanage_user or $is_usermanage_user or $is_course_teacher) && $course_code)
        <p class="text-center form-value mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>

         <!-- THIS IS SECOND CHOICE OF VIEW-STUDENT-TEACHER TOOGLE-BUTTON -->
        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form" class='d-flex justify-content-center mb-5'>
            <label class="switch-sidebar">
                <input class="form-check-input slider-btn-on btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}" type="checkbox" id="flexSwitchCheckChecked" {{ !$is_editor ? "checked" : "" }}>
                <div class="slider-round">
                    <span class="on">{{ trans('langCStudent2') }}</span>
                    <span class="off">{{ trans('langCTeacher') }}</span>
                </div>
            </label>
        </form>
    @endif

    <div class="panel-group accordion mt-4 mb-4" id="sidebar-accordion">
        <div class="panel">
            @foreach ($toolArr as $key => $tool_group)
                <a id="Tool{{$key}}" class="collapsed parent-menu mt-5" data-bs-toggle="collapse" href="#collapse{{ $key }}">
                    <div class="panel-sidebar-heading ps-lg-3 pe-lg-3">
                        <div class="panel-title h3 bg-transparent">
                            <div class='d-flex justify-content-between align-items-end'>
                                {{ $tool_group[0]['text'] }}
                                <span class="fa fa-chevron-up"></span>
                            </div>
                        </div>
                        <div class='lineSidebar'></div>
                    </div>
                </a>
                <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' show': '' }} rounded-0 Collapse{{ $key }}" aria-labelledby="Tool{{$key}}" data-bs-parent="#sidebar-accordion">
                    <div class="m-0 p-0 contextual-sidebar w-auto border-0">
                        <ul class="list-group list-group-flush">
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <li>
                                    <a href="{!! $tool_group[2][$key2] !!}" class='list-group-item d-flex justify-content-start align-items-start py-2 border-0 {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                                        <i class="{{ $tool_group[3][$key2] }} pe-2"></i>
                                        {!! $tool !!}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class='p-3'></div>
            @endforeach
        </div>
        {{ isset($eclass_leftnav_extras) ? $eclass_leftnav_extras : "" }}
    </div>
</div>

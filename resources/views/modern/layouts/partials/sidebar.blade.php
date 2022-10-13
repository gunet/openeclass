<div id="leftnav" class="sidebar float-menu">
    @php $is_course_teacher = check_editor($uid,$course_id); @endphp
    @if(($is_editor or $is_power_user or $is_departmentmanage_user or $is_usermanage_user or $is_course_teacher) && $course_code)
        <p class="text-center text-light mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>
        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form" class='d-flex justify-content-center'>
            <button class='btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }} w-100' data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
                <span class="on">{{ trans('langCStudent2') }}</span>
                <span class="off">{{ trans('langCTeacher') }}</span>
                <p class="on2">{{ trans('langCStudent2') }}</p>
                <p class="off2">{{ trans('langCTeacher') }}</p>
            </button>
        </form>
    @endif
    <div class="panel-group accordion mt-5" id="sidebar-accordion">
        <div class="panel">
            @foreach ($toolArr as $key => $tool_group)
                <a id="Tool{{$key}}" class="collapsed parent-menu mt-5" data-bs-toggle="collapse" href="#collapse{{ $key }}">
                    <div class="panel-sidebar-heading">
                        <div class="panel-title h3">
                            <div class='d-inline-flex align-items-top'>
                                <span class="fa fa-chevron-right tool-sidebar"></span>
                                <span class='text-wrap tool-sidebar-text ps-2'>{{ $tool_group[0]['text'] }}</span>
                            </div>
                        </div><hr class='text-white'>
                    </div>
                </a>
                <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' show': '' }}" aria-labelledby="Tool{{$key}}" data-bs-parent="#sidebar-accordion">
                    @foreach ($tool_group[1] as $key2 => $tool)
                        <a href="{!! $tool_group[2][$key2] !!}" class='leftMenuToolCourse list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                            <div class='d-inline-flex align-items-top'>
                                <span class="fa {{ $tool_group[3][$key2] }} fa-fw posTool tool-sidebar toolSidebarTxt pe-2"></span>
                                <span class='toolSidebarTxt'>{!! $tool !!}</span>
                            </div>

                        </a>
                    @endforeach
                </div>
                <div class='p-3'></div>
            @endforeach
        </div>
        {{ isset($eclass_leftnav_extras) ? $eclass_leftnav_extras : "" }}
    </div>
</div>

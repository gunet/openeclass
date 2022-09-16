<div id="leftnav" class="sidebar float-menu">
    @php $is_course_teacher = check_editor($uid,$course_id); @endphp 
    @if(($is_editor or $is_power_user or $is_departmentmanage_user or $is_usermanage_user or $is_course_teacher) && $course_code)
        <p class="text-center text-light mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>
        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form" class='d-flex justify-content-center'>
            <button class='btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}' data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
                <span class="on">{{ trans('langCStudent2') }}</span>
                <span class="off">{{ trans('langCTeacher') }}</span>
                <p class="on2">{{ trans('langCStudent2') }}</p>
                <p class="off2">{{ trans('langCTeacher') }}</p>
            </button>
        </form>

    @else
        <p class="text-center text-light mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>
        <div class='d-flex justify-content-center'>
            <a class='w-100 btn btn-primary pe-none text-white text-center'>
                @php $is_course_teacher = check_editor($uid,$course_id); @endphp 
                @if (isset($_SESSION['uid']))
                    @if(($session->status == USER_TEACHER) and $is_course_teacher)
                    <span class='text-uppercase'><span class='fa fa-user text-warning pe-2'></span>{{trans('langCTeacher')}}</span>
                    @elseif($session->status == USER_STUDENT or ($session->status == USER_TEACHER and !$is_course_teacher))
                    <span class='text-uppercase'><span class='fa fa-user text-warning pe-2'></span>{{ trans('langCStudent2') }}</span>
                    @elseif($session->status == USERMANAGE_USER)
                    <span class='text-uppercase'><span class='fa fa-user text-warning pe-2'></span>{{ trans('langManageUser') }}</span>
                    @elseif($session->status == USER_DEPARTMENTMANAGER)
                    <span class='text-uppercase'><span class='fa fa-user text-warning pe-2'></span>{{ trans('langManageDepartment') }}</span>
                    @elseif($session->status == USER_GUEST)
                    <span class='text-uppercase'><span class='fa fa-user text-warning pe-2'></span>{{ trans('langVisitor') }}</span>
                    @else
                    <span class='text-uppercase'><span class='fa fa-user text-warning pe-2'></span>{{ trans('langAdministrator') }}</span>
                    @endif
                @else
                <span class='text-uppercase'><span class='fa fa-user text-warning pe-2'></span>{{ trans('langCStudent2') }}</span>
                @endif
            </a>
        </div>
    @endif

    <div class="panel-group accordion mt-5" id="sidebar-accordion">
        <div class="panel">
            @foreach ($toolArr as $key => $tool_group)
                <a id="Tool{{$key}}" class="collapsed parent-menu mt-5" data-bs-toggle="collapse" href="#collapse{{ $key }}">
                    <div class="panel-sidebar-heading">
                        <div class="panel-title h3">
                            <div class='d-inline-flex align-items-center'>
                                <span class="fa fa-chevron-right ms-1 tool-sidebar"></span>
                                <span class='text-wrap tool-sidebar-text mt-1 ps-2'>{{ $tool_group[0]['text'] }}</span>
                            </div>
                        </div><hr class='text-white'>
                    </div>
                </a>
                <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' show': '' }}" aria-labelledby="Tool{{$key}}" data-bs-parent="#sidebar-accordion">
                    @foreach ($tool_group[1] as $key2 => $tool)
                        <a href="{!! $tool_group[2][$key2] !!}" class='leftMenuToolCourse list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                            <div class='d-inline-flex align-items-center'>
                                <span class="fa {{ $tool_group[3][$key2] }} fa-fw tool-sidebar toolSidebarTxt pe-2"></span>
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

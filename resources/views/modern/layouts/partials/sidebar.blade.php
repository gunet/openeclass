<div id="leftnav" class="sidebar float-menu">
    @if(($is_editor || $is_power_user || $is_departmentmanage_user || $is_usermanage_user) && $course_code)
        <p class="text-center text-light fs-6 mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>
        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form" class='d-flex justify-content-center'>
            <button class='btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}' data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
                <span class="on">{{ trans('langCStudent2') }}</span>
                <span class="off">{{ trans('langCTeacher') }}</span>
                <p class="text-right on2">{{ trans('langCStudent2') }}</p>
                <p class="text-left off2">{{ trans('langCTeacher') }}</p>
            </button>
        </form>

    @else
        <p class="text-center text-light fs-6 mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>
        <div class='d-flex justify-content-center'>
            <a class='w-75 btn btn-primary pe-none text-white text-center'>
                @if(($session->status == USER_TEACHER))
                <span class='text-uppercase'>{{trans('langCTeacher')}}</span>
                @elseif($session->status == USER_STUDENT)
                <span class='text-uppercase'>{{ trans('langCStudent2') }}</span>
                @elseif($session->status == USERMANAGE_USER)
                <span class='text-uppercase'>{{ trans('langManageUser') }}</span>
                @elseif($session->status == USER_DEPARTMENTMANAGER)
                <span class='text-uppercase'>{{ trans('langManageDepartment') }}</span>
                @elseif($session->status == ADMIN_USER)
                <span class='text-uppercase'>{{ trans('langAdministrator') }}</span>
                @else
                <span class='text-uppercase'>{{ trans('langVisitor') }}</span>
                @endif
            </a>
        </div>
    @endif
    <div class="panel-group accordion mt-5" id="sidebar-accordion">
        <div class="panel bg-transparent">
            @foreach ($toolArr as $key => $tool_group)
                @if($tool_group[0]['text'] != 'Διαχείριση μαθήματος')
                    <a id="Tool{{$key}}" class="collapsed parent-menu mt-5" data-bs-toggle="collapse" href="#collapse{{ $key }}">
                        <div class="panel-heading">
                            <div class="panel-title h3">
                                <div class='row'>
                                    <div class='col-1'>
                                        <span class="fa fa-chevron-right ms-1 fs-6 text-warning" style='font-size:12px;'></span>
                                    </div>
                                    <div class='col-10'>
                                        <span class='text-wrap text-white fs-6 mt-1'>{{ $tool_group[0]['text'] }}</span>
                                    </div>
                                </div>


                            </div><hr class='text-white'>
                        </div>
                    </a>
                    <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' show': '' }}" aria-labelledby="Tool{{$key}}" data-bs-parent="#sidebar-accordion">
                        @foreach ($tool_group[1] as $key2 => $tool)
                        <a href="{{ $tool_group[2][$key2] }}" class='list-group-item bg-transparent{{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                            <div class='row'>
                                <div class='col-xxl-1 col-xl-1 col-2'>
                                    <span class="fa {{ $tool_group[3][$key2] }} fa-fw mt-1 text-warning toolSidebarTxt"></span>
                                </div>
                                <div class='col-xxl-10 col-xl-10 col-9'>
                                    <span class='text-white toolSidebarTxt'>{!! $tool !!}</span>
                                </div>

                            </div>
                        </a>
                        @endforeach
                    </div>
                @endif
                <div class='p-3'></div>
            @endforeach
        </div>
        {{ isset($eclass_leftnav_extras) ? $eclass_leftnav_extras : "" }}
    </div>
</div>

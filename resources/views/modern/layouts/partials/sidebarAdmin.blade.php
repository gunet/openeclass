<?php //print_a($toolArr)?>


<nav class="navbar navbar-expand d-flex flex-column align-item-start navbar_sidebar" id="background-cheat-leftnav" style="margin-left:-11px;">
    <!-- @if($is_editor || $is_power_user || $is_departmentmanage_user || $is_usermanage_user)
        <div class="row p-2"></div>
        <p class="text-left text-light">{{ trans('langViewAs') }}:</p>

        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form">
            <button class='btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}' data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
                <span class="on">{{trans('langCStudent2')}}</span>
                <span class="off">{{trans('langCTeacher')}}</span>
                <p class="text-right on2">{{trans('langCStudent2')}}</p>
                <p class="text-left off2">{{trans('langCTeacher')}}</p>
            </button>
        </form>
    @endif -->

    <!-- @foreach ($toolArr as $key => $tool_group)
        <a class="collapsed parent-menu" data-bs-toggle="collapse" data-bs-parent="#sidebar-accordion" href="#collapse{{ $key }}">
            <div class="panel-heading">
                <div class="panel-title h3">
                    <span class="fa fa-chevron-right"></span>
                    <span>{{ $tool_group[0]['text'] }}</span>
                </div>
            </div>
        </a>
        <div id="collapse{{ $key }}" class="panel-collapse list-group collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' in': '' }}">
            @foreach ($tool_group[1] as $key2 => $tool)
                <a href="{{ $tool_group[2][$key2] }}" class="list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}" {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                    <span class="fa {{ $tool_group[3][$key2] }} fa-fw"></span>
                    <span>{!! $tool !!}</span>
                </a>
            @endforeach
        </div>
    @endforeach -->

    <ul class="navbar-nav d-flex flex-column mt-5 w-100 ps-2 pe-2">
        <li class="nav-item w-100">
            @foreach ($toolArr as $key => $tool_group)
                @if ($key == 0)
                    <?php
                        $dropdown_id = "DropdownAdmin2";
                        $class_id = "classAdminTwo";
                        $classname = "active_tools_dropdownAdmin2";
                    ?>
                @elseif ($key == 1)
                    <?php
                        $dropdown_id = "DropdownAdmin3";
                        $class_id = "classAdminThree";
                        $classname = "active_tools_dropdownAdmin3";
                    ?>
                @elseif ($key == 2)
                    <?php
                        $dropdown_id = "DropdownAdmin1";
                        $class_id = "classAdminOne";
                        $classname = "active_tools_dropdownAdmin1";
                    ?>
                @endif
                <!-- Desktop collapse -->
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    <a role="button" class="{{ $classname }} nav-link text-light pl-4">
                        {{ $tool_group[0]['text'] }}<i class="{{ $class_id }} float-end pt-1 fas fa-angle-right"></i>
                    </a>
                    <div class="col-lg-12 collapse" id="{{ $dropdown_id }}">
                           <?php $i=0; ?>
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <a id="{{$dropdown_id}}{{$i}}" class="dropdown-item sidebarTextsAdmin pt-2 pb-2 ms-0 me-0 text-white" href="{{ $tool_group[2][$key2] }}">
                                    <div class='row'>
                                        <div class='col-lg-1 sidebarColToolPhoto'>    
                                            <span class="fas {{ $tool_group[3][$key2] }} pt-1" ></span>
                                        </div>
                                        <div class='col-lg-8 sidebarColToolText'>
                                            <span class="fs-6 text-wrap">{{ $tool_group[1][$key2] }}</span>
                                        </div>
                                    </div>
                                </a>

                                <?php $i++; ?>
                            @endforeach
                    
                    </div>
                </div>
                 <!-- Mobile collapse -->
                <div class="d-lg-none mr-auto">
                    <a class="nav-link text-light pl-4" data-bs-toggle="collapse" href="#{{ $dropdown_id }}" role="button" aria-expanded="false" aria-controls="{{ $dropdown_id }}">
                        {{ $tool_group[0]['text'] }} <span class="float-end pe-2 fa fa-angle-down mt-1 "></span>
                    </a>
                    <div class="col-lg-12 collapse" id="{{ $dropdown_id }}">
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <a class="dropdown-item sidebarTexts pt-2 pb-2 ms-0 me-0 text-white text-wrap" href="{{ $tool_group[2][$key2] }}"><span class="fas {{ $tool_group[3][$key2] }}" ></span>&nbsp;&nbsp;{{ $tool_group[1][$key2] }}</a>
                            @endforeach
                    </div>
                </div>

                <div class="row p-2"></div>
            @endforeach
        </li>
    </ul>
</nav>



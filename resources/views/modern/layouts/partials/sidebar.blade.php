
<nav class="navbar navbar-expand d-flex flex-column align-item-start navbar_sidebar" id="background-cheat-leftnav" style="margin-left:-11px;">
    @if(($is_editor || $is_power_user || $is_departmentmanage_user || $is_usermanage_user) && $course_code)
        <div class="row p-2"></div>
        <p class="text-left text-light fs-6 viewPageAs">{{ trans('langViewAs') }}:</p>

        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form">
            <button class='btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}' data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
                <span class="on">{{ trans('langCStudent') }}</span>
                <span class="off">{{ trans('langCTeacher') }}</span>
                <p class="text-right on2">{{ trans('langCStudent') }}</p>
                <p class="text-left off2">{{ trans('langCTeacher') }}</p>
            </button>
        </form>
    @else
        <div class="row p-2"></div>
        <p class="text-left text-light fs-6 viewPageAs">{{ trans('langViewAs') }}:</p>
        <a class='w-75 btn btn-primary pe-none text-white text-center'>{{trans('langCTeacher')}}</a>

    @endif

    <ul class="navbar-nav d-flex flex-column mt-4 w-100 p-2">
        <li class="nav-item w-100">
            @foreach ($toolArr as $key => $tool_group)
                @if ($key == 0)
                    @php
                        $dropdown_id = "Dropdown2";
                        $class_id = "classTwo";
                        $classname = "active_tools_dropdown2";
                    @endphp
                @elseif ($key == 1)
                    @php
                        $dropdown_id = "Dropdown3";
                        $class_id = "classThree";
                        $classname = "active_tools_dropdown3";
                    @endphp
                @elseif ($key == 2)
                    @continue  <!-- not displaying admin menu -->
                @endif
                <!-- Desktop collapse -->
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    <a role="button" class="{{ $classname }} nav-link text-white ps-1">
                        {{ $tool_group[0]['text'] }} <i class="{{ $class_id }} text-white pt-1 float-end fa fa-angle-down"></i>
                    </a>
                    <div class="col-lg-12 show" id="{{ $dropdown_id }}">
                            @php
                                $i=0
                            @endphp
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <a id="{{$dropdown_id}}{{$i}}" class="dropdown-item sidebarTexts pt-2 pb-2 ms-0 me-0 text-white" href="{{ $tool_group[2][$key2] }}">
                                    <div class='row'>
                                        <div class='col-lg-1 sidebarColToolPhoto'>
                                            <span class="fas {!! $tool_group[3][$key2] !!} pt-1"></span>
                                        </div>
                                        &nbsp;&nbsp;<div class='col-lg-8 sidebarColToolText'>
                                            <div class="fs-6 text-wrap">{!! $tool_group[1][$key2] !!}</div>
                                        </div>
                                    </div>
                                </a>
                                @php
                                    $i++;
                                @endphp
                            @endforeach
                    </div>
                </div>
                <!-- Mobile collapse -->
                <div class="d-lg-none mr-auto">
                    <a role="button" class="nav-link text-white" data-bs-toggle="collapse" href="#{{ $dropdown_id }}" aria-expanded="false" aria-controls="{{ $dropdown_id }}">
                        {{ $tool_group[0]['text'] }} <span class="text-white ps-5 ms-4 pe-0 fa fa-angle-down"></span>
                    </a>
                    <div class="col-lg-12 show" id="{{ $dropdown_id }}">

                            @foreach ($tool_group[1] as $key2 => $tool)
                                <a class="dropdown-item sidebarTexts pt-2 pb-2 ms-0 me-0 text-white text-wrap" href="{{ $tool_group[2][$key2] }}"><i class="fas {{ $tool_group[3][$key2] }}" ></i>&nbsp;&nbsp;{{ $tool_group[1][$key2] }}</a>
                            @endforeach

                    </div>
                </div>
                <div class="row p-2"></div>
            @endforeach
        </li>
    </ul>

</nav>


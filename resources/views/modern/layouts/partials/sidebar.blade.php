
<!-- <nav class="navbar navbar-expand d-flex flex-column align-item-start navbar_sidebar" id="background-cheat-leftnav" style="margin-left:-11px;">
    @if(($is_editor || $is_power_user || $is_departmentmanage_user || $is_usermanage_user) && $course_code)
        <div class="row p-2"></div>
        <p class="text-left text-light fs-6 viewPageAs">{{ trans('langViewAs') }}:</p>

        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form">
            <button class='btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}' data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
                <span class="on">{{ trans('langCStudent2') }}</span>
                <span class="off">{{ trans('langCTeacher') }}</span>
                <p class="text-right on2">{{ trans('langCStudent2') }}</p>
                <p class="text-left off2">{{ trans('langCTeacher') }}</p>
            </button>
        </form>
    @else
        <div class="row p-2"></div>
        <p class="text-left text-light fs-6 viewPageAs">{{ trans('langViewAs') }}:</p>
        <a class='w-75 btn btn-primary pe-none text-white text-center'>{{trans('langCStudent2')}}</a>
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
                    @continue  
                @endif
                
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

</nav> -->



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
        <div class='d-flex justify-content-center'><a class='w-75 btn btn-primary pe-none text-white text-center'>{{trans('langCStudent2')}}</a></div>
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
                        <a href="{{ $tool_group[2][$key2] }}" class='list-group-item bg-transparent{{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}} ps-4' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                            <div class='row'>  
                                <div class='col-xl-1 col-1 icon_tool'>
                                    <span class="fa {{ $tool_group[3][$key2] }} fa-fw mt-1 text-warning iconTool"></span>
                                </div>
                                <div class='col-xl-9 col-9 text_tool'>
                                    <span class='text-wrap text-white mt-1 textTool'>{!! $tool !!}</span>
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


            
            
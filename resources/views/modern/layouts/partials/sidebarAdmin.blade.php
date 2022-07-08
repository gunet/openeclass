<!-- 
<nav class="navbar navbar-expand d-flex flex-column align-item-start navbar_sidebar" id="background-cheat-leftnav" style="margin-left:-11px;">

    <ul class="navbar-nav d-flex flex-column mt-5 w-100 ps-2 pe-2">
        <li class="nav-item w-100">
            @foreach ($toolArr as $key => $tool_group)
                @if ($key == 0)
                    <?php
                        // $dropdown_id = "DropdownAdmin2";
                        // $class_id = "classAdminTwo";
                        // $classname = "active_tools_dropdownAdmin2";
                    ?>
                @elseif ($key == 1)
                    <?php
                        // $dropdown_id = "DropdownAdmin3";
                        // $class_id = "classAdminThree";
                        // $classname = "active_tools_dropdownAdmin3";
                    ?>
                @elseif ($key == 2)
                    <?php
                        // $dropdown_id = "DropdownAdmin1";
                        // $class_id = "classAdminOne";
                        // $classname = "active_tools_dropdownAdmin1";
                    ?>
                @endif
                
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    <a role="button" class="{{ $classname }} nav-link text-light pl-4">
                        {{ $tool_group[0]['text'] }}<i class="{{ $class_id }} float-end pt-1 fas fa-angle-right"></i>
                    </a>
                    <div class="col-lg-12 collapse" id="{{ $dropdown_id }}">
                           <?php //$i=0; ?>
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <a id="{{$dropdown_id}}{{$i}}" class="dropdown-item sidebarTextsAdmin pt-2 pb-2 ms-0 me-0 text-white" href="{{ $tool_group[2][$key2] }}">
                                    <div class='row'>
                                        <div class='col-lg-1 sidebarColToolPhoto'>    
                                            <span class="fas {!! $tool_group[3][$key2] !!} pt-1" ></span>
                                        </div>
                                        <div class='col-lg-8 sidebarColToolText'>
                                            <span class="fs-6 text-wrap">{!! $tool_group[1][$key2] !!}</span>
                                        </div>
                                    </div>
                                </a>

                                <?php //$i++; ?>
                            @endforeach
                    
                    </div>
                </div>
                 
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
</nav> -->



<div id="leftnav" class="sidebar float-menu">
    @if(($is_editor || $is_power_user || $is_departmentmanage_user || $is_usermanage_user))
        <p class="text-center text-light fs-6 mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>

        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form">
            <button class='btn-toggle{{ $is_editor ? " btn-toggle-on" : "" }}' data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
                <span class="on">{{ trans('langCStudent2') }}</span>
                <span class="off">{{ trans('langCTeacher') }}</span>
                <p class="text-right on2">{{ trans('langCStudent2') }}</p>
                <p class="text-left off2">{{ trans('langCTeacher') }}</p>
            </button>
        </form>
    @else
        <p class="text-center text-light fs-6 mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>
        <a class='w-75 btn btn-primary pe-none text-white text-center'>{{trans('langCStudent2')}}</a>
    @endif
    <div class="panel-group accordion mt-5" id="sidebar-accordion">
        <div class="panel bg-transparent">
            @foreach ($toolArr as $key => $tool_group)
                <a id="Tool{{$key}}" class="collapsed parent-menu mt-5" data-bs-toggle="collapse" href="#collapse{{ $key }}">
                    <div class="panel-heading">
                        <div class="panel-title h3">
                            <div class='row'>
                                <div class='col-1'>
                                    <span class="fa fa-chevron-right text-warning ms-0 mt-1" style='font-size:12px;'></span>
                                </div>
                                <div class='col-10'>
                                    <span class='text-wrap text-white fs-6'>{{ $tool_group[0]['text'] }}</span>
                                </div>
                            </div>
                            
                            
                        </div><hr class='text-white'>
                    </div>
                </a>
                <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' show': '' }}" aria-labelledby="Tool{{$key}}" data-bs-parent="#sidebar-accordion">
                    @foreach ($tool_group[1] as $key2 => $tool)
                    <a href="{{ $tool_group[2][$key2] }}" class='list-group-item bg-transparent{{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}} ps-4' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                        <div class='row'>  
                            <div class='col-xl-1 col-1 icon_Admintool'>
                                <span class="fa {{ $tool_group[3][$key2] }} fa-fw mt-1 text-warning iconTool"></span>
                            </div>
                            <div class='col-xl-9 col-9 text_Admintool'>
                                <span class='text-wrap text-white textTool'>{!! $tool !!}</span>
                            </div>
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



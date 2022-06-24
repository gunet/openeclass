<?php //print_a($toolArr)?>
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> -->
<!-- <script type="text/javascript" src="{{ $urlAppend }}template/modern/js/sidebar_slider_action.js"></script> -->

<nav class="navbar navbar-expand d-flex flex-column align-item-start navbar_sidebar" id="background-cheat-leftnav" style="margin-left:-11px;">
    @if($is_editor || $is_power_user || $is_departmentmanage_user || $is_usermanage_user)
        <div class="row p-2"></div>
        <p class="text-left text-light">{{ trans('langViewAs') }}:</p>

        <!-- <form onclick="VIEW()" method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form">
            <input type="checkbox" value="{{$is_editor}}" class='toggles btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}' id="rounded" data-bs-placement='top' title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
            <label for="rounded" class="rounded" data-checked="{{ trans('langCTeacher') }}" data-unchecked="{{ trans('langCStudent') }}"></label>
            <input type="hidden" name="studentOrteacher" id="student_view" value="1">
        </form> -->
        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form">
            <button class='btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}' data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}">
                <span class="on">{{trans('langCStudent2')}}</span>
                <span class="off">{{trans('langCTeacher')}}</span>
                <p class="text-right on2">{{trans('langCStudent2')}}</p>
                <p class="text-left off2">{{trans('langCTeacher')}}</p>
            </button>
        </form>
    @endif

    <!-- @foreach ($toolArr as $key => $tool_group)
        <a class="collapsed parent-menu" data-toggle="collapse" data-parent="#sidebar-accordion" href="#collapse{{ $key }}">
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

    <ul class="navbar-nav d-flex flex-column mt-4 w-100 p-2">
        <li class="nav-item w-100">
            @foreach ($toolArr as $key => $tool_group)
                @if ($key == 0)
                    <?php
                        $dropdown_id = "Dropdown2";
                        $class_id = "classTwo";
                        $classname = "active_tools_dropdown2";
                    ?>
                @elseif ($key == 1)
                    <?php
                        $dropdown_id = "Dropdown3";
                        $class_id = "classThree";
                        $classname = "active_tools_dropdown3";
                    ?>
                @elseif ($key == 2)
                    @continue  <!-- not displaying admin menu -->
                @endif
                <!-- Desktop collapse -->
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    <a role="button" class="{{ $classname }} nav-link text-white"> 
                        {{ $tool_group[0]['text'] }} <i class="{{ $class_id }} text-white pt-1 float-end fa fa-angle-down"></i>
                    </a>
                    <div class="col-lg-12 show" id="{{ $dropdown_id }}">
                            <?php $i=0; ?>
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <a id="{{$dropdown_id}}{{$i}}" class="dropdown-item sidebarTexts pt-2 pb-2 ms-0 me-0 text-white" href="{{ $tool_group[2][$key2] }}">
                                    <div class='row'>
                                        <div class='col-lg-1 sidebarColToolPhoto'>
                                            <span class="fas {{ $tool_group[3][$key2] }} pt-1"></span>
                                        </div>
                                        &nbsp;&nbsp;<div class='col-lg-8 sidebarColToolText'>
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


    <!-- <ul class="navbar-nav d-flex flex-column mt-4 w-100 p-3 navclass_trigger">
        <li class="nav-item w-100" style="margin-top:-20px;">
            <a class="active_tools_dropdown2 nav-link text-light pl-4" data-bs-toggle="collapse" href="#Dropdown2" data-bs-target="#Dropdown2" aria-expanded="true" aria-controls="Dropdown2">
                {{ trans('langCActiveTools') }} <i class="classTwo fas fa-angle-down" style="float:right"></i>
                <i class="classTwo fas fa-angle-up" style="float:right"></i>
            </a>
            <div class="col-lg-12 collapse show col_collapse_items" id="Dropdown2">
                <ul>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="{{ $urlAppend }}modules/announcements/index.php?course={{$course_code}}"><i class="fas fa-bullhorn"></i> Ανακοινώσεις</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="{{ $urlAppend }}modules/exercise/index.php?course={{$course_code}}"><i class="fas fa-pencil-alt"></i> Ασκήσεις</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="#"><i class="fas fa-sort-numeric-down"></i></i> Βαθμολόγιο</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="{{ $urlAppend }}modules/glossary/index.php?course={{$course_code}}"><i class="fas fa-list"></i> Γλωσσάριο</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="#" ><i class="fas fa-ellipsis-h"></i> Γραμμή Μάθησης</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="{{$urlAppend}}modules/document/index.php?course={{$course_code}}"><i class="fas fa-folder"></i> Έγγραφα</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="#" ><i class="fas fa-flask"></i> Εργασίες</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="#" ><i class="fas fa-question"></i> Ερωτηματολόγια</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="#" ><i class="fas fa-check"></i> Παρουσιολόγιο</a></li>
                    <li><a style="font-size:15px;" class="dropdown-item text-light pl-4 p-2" href="#"><i class="fas fa-link"></i> Σύνδεσμοι</a></li>
                </ul>
            </div>
        </li>
        <div class="row p-2"></div>
        @if($is_editor || $is_power_user || $is_departmentmanage_user || $is_usermanage_user)
            <li class="nav-item w-100">
                <a href="#Dropdown3" class="active_tools_dropdown3 nav-link text-light pl-4" data-bs-toggle="collapse" data-bs-target="#Dropdown3" aria-expanded="false" aria-controls="Dropdown3">
                    {{ trans('langCInactiveTools') }} <i class="classThree fas fa-angle-down" style="float:right"></i>
                    <i class="classThree fas fa-angle-up" style="float:right"></i></a>
                <div class="col-lg-12 collapse col_collapse_items" id="Dropdown3">
                    <ul>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Αιτήματα</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Διαδραστικό περιεχόμενο</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Ενοιολογικός χάρτης</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Ηλεκτρονικό βιβλίο</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Ιστολόγιο</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Κουβεντούλα</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Ομάδες χρηστών</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Πολυμέσα</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Πρόοδος</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Συζητήσεις</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Σύστημα wiki</a></li>
                        <li><a href="#" class="dropdown-item text-light pl-4 p-2">Τοίχος</a></li>
                    </ul>
                </div>
            </li>
            <div class="row p-2"></div>
            <div class="row p-2"></div>
        @endif
        <div class="row p-2"></div>
    </ul> -->
</nav>

<script>

    // function VIEW(){
    //     var Myelement = document.getElementById("student_view");
    //     Myelement.value = localStorage.input;
    //     document.getElementById("student-view-form").submit();
    // }
</script>

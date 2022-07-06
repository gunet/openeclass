
<nav class="navbar navbar-expand d-flex flex-column align-item-start navbar_sidebar" id="background-cheat-leftnav" style="margin-left:-11px;">

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
                                            <span class="fas {!! $tool_group[3][$key2] !!} pt-1" ></span>
                                        </div>
                                        <div class='col-lg-8 sidebarColToolText'>
                                            <span class="fs-6 text-wrap">{!! $tool_group[1][$key2] !!}</span>
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



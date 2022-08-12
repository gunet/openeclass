<div id="leftnav" class="sidebar float-menu">

    <p class="text-center text-light fs-6 mt-3 viewPageAs">{{ trans('langViewAs') }}:</p>
    <div class='d-flex justify-content-center'>
        <a class='w-75 btn btn-primary pe-none text-white text-center'>
            <span class='text-uppercase'><span class='fa fa-user text-warning pe-2'></span>{{ trans('langCStudent2') }}</span>
        </a>
    </div>
    
    <div class="panel-group accordion mt-5" id="sidebar-accordion">
        <div class="panel bg-transparent">
            @foreach ($toolArr as $key => $tool_group)
                <a id="Tool{{$key}}" class="collapsed parent-menu mt-5" data-bs-toggle="collapse" href="#collapse{{ $key }}">
                    <div class="panel-sidebar-heading">
                        <div class="panel-title h3">
                            <div class='d-inline-flex align-items-center'>
                                <span class="fa fa-chevron-right ms-1 fs-6 text-warning" style='font-size:12px;'></span>
                                <span class='text-wrap text-white fs-6 mt-1 ps-2'>{{ $tool_group[0]['text'] }}</span>
                            </div>
                        </div><hr class='text-white'>
                    </div>
                </a>
                <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' show': '' }}" aria-labelledby="Tool{{$key}}" data-bs-parent="#sidebar-accordion">
                    @foreach ($tool_group[1] as $key2 => $tool)
                        <a href="{{ $tool_group[2][$key2] }}" class='leftMenuToolCourse list-group-item bg-transparent{{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                            <div class='d-inline-flex align-items-center'>
                                <span class="fa {{ $tool_group[3][$key2] }} fa-fw mt-1 text-warning toolSidebarTxt pe-2"></span>
                                <span class='text-white toolSidebarTxt pt-1'>{!! $tool !!}</span>
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

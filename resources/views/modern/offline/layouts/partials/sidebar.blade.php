<div id="leftnav" class="sidebar float-menu">

    <div class='col-12 text-end d-none d-lg-block'>
        <button type="button" id="menu-btn" class="btn menu_btn_button">
            <img class='settings-icons' src='{{ $urlAppend }}template/modern/img/Icons_menu-collapse.svg' />
        </button>
    </div>
    
    <div class="panel-group accordion mt-4 mb-4" id="sidebar-accordion">
        <div class="panel">
            @foreach ($toolArr as $key => $tool_group)
                <a id="Tool{{$key}}" class="collapsed parent-menu mt-5 menu-header" data-bs-toggle="collapse" href="#collapse{{ $key }}">
                    <div class="panel-sidebar-heading bg-transparent border-bottom-default px-lg-0">
                        <div class="panel-title h3 bg-transparent">
                            <div class='d-flex justify-content-start align-items-start gap-1'>
                                <span class="fa fa-chevron-up" style='transition: transform .3s ease-in-out;'></span>
                                {{ $tool_group[0]['text'] }}
                                
                            </div>
                        </div>
                    </div>
                </a>
                <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' show': '' }} rounded-0 Collapse{{ $key }} mt-3" aria-labelledby="Tool{{$key}}" data-bs-parent="#sidebar-accordion">
                    <div class="m-0 p-0 contextual-sidebar w-auto border-0">
                        <ul class="list-group list-group-flush">
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <li>
                                    <a href="{!! $tool_group[2][$key2] !!}" class='list-group-item d-flex justify-content-start align-items-start gap-2 py-1 border-0 {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                                        <i class="{{ $tool_group[3][$key2] }} mt-1 settings-icons"></i>
                                        <span class='menu-items TextBold'>{!! $tool !!}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class='p-3'></div>
            @endforeach
        </div>
        {{ isset($eclass_leftnav_extras) ? $eclass_leftnav_extras : "" }}
    </div>
</div>


<div id="leftnav" class="col-12 sidebar float-menu pt-3">

    <div class='col-12 text-end d-none d-lg-block'>
        <button type="button" id="menu-btn" class="btn menu_btn_button" data-bs-toggle="tooltip" data-bs-placement="right">
            @if(get_config('theme_options_id') == 0)
                <img class='settings-icons' src='{{ $urlAppend }}template/modern/img/Icons_menu-collapse.svg' />
            @else 
                <i class="fa-solid fa-bars settings-icons"></i>
            @endif
        </button>
    </div>


    @php $is_course_teacher = check_editor($uid,$course_id); @endphp

    @if(($is_editor or $is_power_user or $is_departmentmanage_user or $is_usermanage_user or $is_course_teacher) && $course_code)
       
         <!-- THIS IS SECOND CHOICE OF VIEW-STUDENT-TEACHER TOOGLE-BUTTON -->
    <div class='col-12 mt-4'>
        <form method="post" action="{{ $urlAppend }}main/student_view.php?course={{ $course_code }}" id="student-view-form" class='d-flex justify-content-center mb-5'>
            <label class="switch-sidebar">
                <input class="form-check-input slider-btn-on btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}" type="checkbox" id="flexSwitchCheckChecked" {{ !$is_editor ? "checked" : "" }}>
                <div class="slider-round">
                    <span class="on">{{ trans('langCStudent2') }}</span>
                    <span class="off">{{ trans('langCTeacher') }}</span>
                </div>
            </label>
        </form>
    </div>
    @endif

    <div class='col-12 my-4 px-1'>
        <div class="panel-group accordion" id="sidebar-accordion">
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
                                        <a href="{!! $tool_group[2][$key2] !!}" 
                                            class='list-group-item d-flex justify-content-start align-items-start gap-2 py-1 border-0 {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}
                                            data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right" title="{!! $tool !!}">
                                            <i class="{{ $tool_group[3][$key2] }} mt-1 settings-icons"></i>
                                            <span class='menu-items TextBold w-100'>{!! $tool !!}</span>
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
    
</div>



<script type="text/javascript">
    $(document).ready( function () {
        if($( "#background-cheat-leftnav" ).hasClass( "active-nav" )){
            $('#menu-btn').attr('data-bs-original-title','{{ js_escape(trans('langOpenOptions')) }}');
            $('.contextual-sidebar .list-group-item').tooltip('enable');
        }else{
            $('#menu-btn').attr('data-bs-original-title','{{ js_escape(trans('langCloseOptions')) }}');
            $('.contextual-sidebar .list-group-item').tooltip('disable');
        }
        
        $('#menu-btn').on('click',function(){
            $('#menu-btn').tooltip('hide');
            if($( "#background-cheat-leftnav" ).hasClass( "active-nav" )){
                $('#menu-btn').attr('data-bs-original-title','{{ js_escape(trans('langOpenOptions')) }}');
                $('.contextual-sidebar .list-group-item').tooltip('enable');
            }else{
                $('#menu-btn').attr('data-bs-original-title','{{ js_escape(trans('langCloseOptions')) }}');
                $('#menu-btn').tooltip('enable');
                $('.contextual-sidebar .list-group-item').tooltip('disable');
            }
        });
    } );
</script>




<div id="leftnav" class="col-12 sidebar float-menu pt-3">

    <div class='col-12 text-end d-none d-lg-block'>
        <button type="button" id="menu-btn" class="btn menu_btn_button" data-bs-toggle="tooltip" data-bs-placement="right" onclick="ToogleButton()" aria-label="Course menu">
        <svg id='collapse-left-menu-icon' width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" role="presentation">
            <path d="M5 5C4.44772 5 4 5.44772 4 6V6.5C4 7.05228 4.44772 7.5 5 7.5H19.25C19.9404 7.5 20.5 6.94036 20.5 6.25C20.5 5.55964 19.9404 5 19.25 5H5Z" fill="#2B3944"/>
            <path d="M5 10.5C4.44772 10.5 4 10.9477 4 11.5V12C4 12.5523 4.44772 13 5 13H14.75C15.4404 13 16 12.4404 16 11.75C16 11.0596 15.4404 10.5 14.75 10.5H5Z" fill="#2B3944"/>
            <path d="M5 16C4.44772 16 4 16.4477 4 17V17.5C4 18.0523 4.44772 18.5 5 18.5H10.75C11.4404 18.5 12 17.9404 12 17.25C12 16.5596 11.4404 16 10.75 16H5Z" fill="#2B3944"/>
        </svg>
        </button>
    </div>


    @php $is_course_teacher = check_editor($uid,$course_id); @endphp

    @if(($is_editor or $is_power_user or $is_departmentmanage_user or $is_usermanage_user or $is_course_teacher) && $course_code)
       
         <!-- THIS IS SECOND CHOICE OF VIEW-STUDENT-TEACHER TOOGLE-BUTTON -->
    <div class='col-12 mt-lg-4 mt-3'>
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
                            <div class="panel-title pb-2 bg-transparent">
                                <div class='d-flex justify-content-start align-items-start gap-1 Tools-active-deactive'>
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
                                            class='list-group-item d-flex justify-content-start align-items-start module-tool px-3 py-1 border-0 {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}' {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}
                                            data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right" title="{!! $tool !!}" style='gap:1rem;'>
                                            <i class="{{ $tool_group[3][$key2] }} mt-1"></i>
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



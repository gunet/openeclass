@php

    $go_back_url = $_SERVER['REQUEST_URI'];

    if(!$module_visibility){
        $visible_module = 0;
    }else{
        $visible_module = 1;
    }

@endphp


@if($course_code and !isset($_GET['fromFlipped']))
    <div class='d-block mt-4'>
        <div class='d-flex gap-lg-5 gap-4' style='min-height:100px;'>
            @if($is_editor)

                    <div class='flex-grow-1'>
                        @if($toolName)
                            <div class='col-12 mb-2'>
                                <h2 class='mb-0'>{{$currentCourseName}}</h2>
                                <p>{{course_id_to_public_code($course_id)}}&nbsp - &nbsp{{course_id_to_prof($course_id)}}</p>
                            </div>
                            <div class='col-12 d-inline-flex'>
                                <!-- toolName -->
                                <h4>{{$toolName}}</h4>
                                
                                
                            </div>
                        @else
                            <div class='col-12'>
                                
                                <h2 class='mb-0'>{{$currentCourseName}}</h2>
                                
                                <div class='d-flex justify-content-start align-items-center gap-2'>
                                    <p>{{course_id_to_public_code($course_id)}}&nbsp - &nbsp{{course_id_to_prof($course_id)}}</p> 
                                    @if($courseLicense > 0)
                                        <div class='copyright-img'>{!! copyright_info($course_id) !!}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class='d-flex flex-column'>
                            @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])

                            <div class='d-flex justify-content-end align-items-end gap-2 mt-auto'>
                                <!-- active - inactive module_id -->
                                @if($module_id != MODULE_ID_COURSEINFO and $module_id != MODULE_ID_USERS
                                    and $module_id != MODULE_ID_USAGE and $module_id != MODULE_ID_TOOLADMIN
                                    and $module_id != MODULE_ID_ABUSE_REPORT and $module_id != MODULE_ID_COURSE_WIDGETS
                                    and $module_id != MODULE_ID_UNITS and !empty($module_id))
                                        <form id="form_id" action="{{$urlAppend}}main/module_toggle.php?course={{$course_code}}&module_id={{$module_id}}" method="post">
                                            <input type="hidden" name="hide" value="{{$visible_module}}">
                                            <input type="hidden" name="Active_Deactive_Btn">
                                            <input type="hidden" name="prev_url" value="{{$go_back_url}}">
                                            @if (display_activation_link($module_id))
                                                @if($visible_module == 0)
                                                    <a class='btn submitAdminBtn' href="javascript:$('#form_id').submit();"
                                                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langActivate') }}">
                                                        <i class="fa-regular fa-eye-slash"></i>
                                                    </a>
                                                @else
                                                    <a class='btn submitAdminBtn' href="javascript:$('#form_id').submit();"
                                                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langDeactivate') }}">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        </form>
                                @endif
                                <!-- rss for announcements - blog -->
                                @if($module_id == MODULE_ID_ANNOUNCE or $module_id == MODULE_ID_BLOG)
                                    @php $getToken = generate_csrf_token_link_parameter(); @endphp
                                    @if($module_id == MODULE_ID_ANNOUNCE)
                                        <a class='btn submitAdminBtn' href="{{$urlAppend}}modules/announcements/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{trans('langRSSFeed')}}">
                                            <i class="fa-solid fa-rss"></i>
                                        </a>
                                    @else
                                        <a class='btn submitAdminBtn' href="{{$urlAppend}}modules/blog/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-original-title="{{trans('langRSSFeed')}}">
                                            <i class="fa-solid fa-rss"></i>
                                        </a>
                                    @endif
                                @endif

                                <a id='help-btn' href='{{ $urlServer }}modules/help/help.php?language={{ $language }}&topic={{ $helpTopic }}' class='btn submitAdminBtn' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langHelp') }}">
                                    <i class="fa-solid fa-circle-question"></i>
                                </a>
                            </div>






                    </div>
                
            @else

                    <div class='flex-grow-1'>
                        @if($toolName)
                            <div class='col-12 mb-2'>
                                <h2 class='mb-0'>{{$currentCourseName}}</h2>
                                <p>{{course_id_to_public_code($course_id)}}&nbsp - &nbsp{{course_id_to_prof($course_id)}}</p>
                            </div>
                            <div class='col-12 d-inline-flex'>
                                <h4>{{$toolName}}</h4>
                                
                            </div>
                        @else
                            <div class='col-12'>
                                <h2 class='mb-0'>{{$currentCourseName}}</h2>
                                <div class='d-flex justify-content-start align-items-center gap-2'>
                                    <p>{{course_id_to_public_code($course_id)}}&nbsp - &nbsp{{course_id_to_prof($course_id)}}</p>
                                    @if($courseLicense > 0)
                                        <div class='copyright-img'>{!! copyright_info($course_id) !!}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class='d-flex gap-2'>
                        <!-- rss for announcements - blog -->
                        @if($toolName == trans('langAnnouncements') or $toolName == trans('langBlog'))
                            @php $getToken = generate_csrf_token_link_parameter(); @endphp
                            @if($toolName == trans('langAnnouncements'))
                                <a class='btn submitAdminBtn' href="{{$urlAppend}}modules/announcements/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}" data-bs-toggle="tooltip" 
                                    data-bs-placement="bottom" data-bs-original-title="{{trans('langRSSFeed')}}">
                                    <i class="fa-solid fa-rss"></i>
                                </a>
                            @else
                                <a class='btn submitAdminBtn' href="{{$urlAppend}}modules/blog/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}" data-bs-toggle="tooltip" 
                                    data-bs-placement="bottom" data-original-title="{{trans('langRSSFeed')}}">
                                    <i class="fa-solid fa-rss"></i>
                                </a>
                            @endif
                        @endif

                        <a id='help-btn' href='{{ $urlServer }}modules/help/help.php?language={{ $language }}&topic={{ $helpTopic }}' class='btn submitAdminBtn' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langHelp') }}">
                            <span class='fa-solid fa-circle-question'></span>
                        </a>
                    </div>
                
            @endif
        </div></br>
    </div>
@endif


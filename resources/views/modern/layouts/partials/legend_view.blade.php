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
        <div class='d-flex gap-lg-5 gap-4' style='margin-bottom: 15px;'>
            <div class='flex-grow-1'>
                @if($toolName)
                    <div class='col-12 mb-2'>
                        <h2 class='mb-0'>{{ $currentCourseName }}</h2>
                        <p>{{ course_id_to_public_code($course_id) }}&nbsp; - &nbsp;{{ course_id_to_prof($course_id) }}</p>
                    </div>
                    <div class='col-12 d-inline-flex'>
                        <!-- toolName -->
                        <h3>{{ $toolName }}</h3>
                    </div>
                @else
                    <div class='col-12 mb-2'>
                        <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                            <h2 class='mb-0'>{{ $currentCourseName }}</h2>
                            {!! course_access_icon($course_info->visible) !!}
                            @if($courseLicense > 0)
                                {!! copyright_info($course_id) !!}
                            @endif
                        </div>
                        <div class='d-flex justify-content-start align-items-center gap-2 mt-2'>
                            <p>{{ course_id_to_public_code($course_id) }}&nbsp; - &nbsp;{{ course_id_to_prof($course_id) }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class='d-flex flex-column'>
                <!-- course admin menu -->
                @if ($is_editor)
                    @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                @endif

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
                                        <a class='btn successAdminBtn gap-2 text-decoration-none' href="javascript:$('#form_id').submit();"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langActivate') }}" aria-label="{{ trans('langActivate') }}">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </a>
                                    @else
                                        <a class='btn deleteAdminBtn gap-2 text-decoration-none' href="javascript:$('#form_id').submit();"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langDeactivate') }}" aria-label="{{ trans('langDeactivate') }}">
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
                            <a class='btn warningAdminBtn gap-2 text-decoration-none' href="{{$urlAppend}}modules/announcements/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{trans('langRSSFeed')}}" aria-label="{{trans('langRSSFeed')}}">
                                <i class="fa-solid fa-rss"></i>
                            </a>
                        @else
                            <a class='btn warningAdminBtn gap-2 text-decoration-none' href="{{$urlAppend}}modules/blog/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-original-title="{{trans('langRSSFeed')}}" aria-label="{{trans('langRSSFeed')}}">
                                <i class="fa-solid fa-rss"></i>
                            </a>
                        @endif
                    @endif
                    @if($toolName)
                        <a id='help-btn' href='{{ $urlServer }}modules/help/help.php?language={{ $language }}&topic={{ $helpTopic }}' class='btn helpAdminBtn text-decoration-none gap-2' 
                            data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langHelp') }}" aria-label="{{ trans('langHelp') }}">
                            <i class="fa-solid fa-circle-info"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

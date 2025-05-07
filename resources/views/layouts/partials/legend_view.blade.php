@php
    $go_back_url = $_SERVER['REQUEST_URI'];
    if (!$module_visibility) {
        $visible_module = 0;
    } else {
        $visible_module = 1;
    }
@endphp

@if (!isset($_GET['fromFlipped']))
    <h1 class='sr-only'>
        @if($course_code)
            {{ trans('langCourse') }} : {{ $currentCourseName }}
        @elseif($pageTitle)
            {{ $pageTitle }}
        @endif
    </h1>
    <h2 class='sr-only'>
        @if($course_code)
            {{ trans('langCode') }} : {{ $course_code }}
        @elseif($pageName)
            {{ trans('langThePageIs') }} {{ $pageName }}
        @elseif($toolName) {{ trans('langThePageIs') }} {{ $toolName }}
        @endif
    </h2>
    @if ($course_code or $require_help or $breadcrumbs)
        <div class='col-12 mt-4 @if (!isset($action_bar) or empty($action_bar)) mb-3 @endif'>
    @else
        <div class='col-12 @if (!isset($action_bar) or empty($action_bar)) mb-3 @endif'>
    @endif
        <div class='d-flex gap-lg-5 gap-4'>
            <div class='flex-grow-1'>
                @if ($course_code) {{-- course --}}
                    <div class='col-12 mb-2'>
                        <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                            @if (isset($course_code))
                                <a href="{{$urlAppend}}courses/{{$course_code}}"><h2 class='mb-0'>{{ $currentCourseName }}</h2></a>
                            @else
                                <h2 class='mb-0'>{{ $currentCourseName }}</h2>
                            @endif
                        </div>
                        <div class='d-flex justify-content-start align-items-center gap-2 mt-2 flex-wrap'>
                            <p>{{ course_id_to_public_code($course_id) }}&nbsp; - &nbsp;{{ course_id_to_prof($course_id) }}</p>
                            <div class='course-title-icons d-flex justify-content-start align-items-center gap-2'>
                                {!! course_access_icon(course_status($course_id)) !!}
                                @if($courseLicense > 0)
                                    {!! copyright_info($course_id) !!}
                                @endif
                            </div>
                        </div>
                        @if (!isset($action_bar) or empty($action_bar))
                            <div class='col-12 d-inline-flex'>
                                <p>
                                    {{ $toolName }}
                                    @if ($pageName and ($pageName != $toolName))
                                        - {{ $pageName }}
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    @if($toolName)
                        <div class='col-12 d-inline-flex'>
                            <h2>
                                {{ $toolName }}
                            </h2>
                        </div>
                        @if (!isset($action_bar) or empty($action_bar))
                            <div class='col-12 d-inline-flex mt-2'>
                                @if ($pageName and ($pageName != $toolName))
                                    <h3>
                                        {{ $pageName }}
                                    </h3>
                                @endif
                            </div>
                        @endif
                    @endif
                @endif
            </div>

            <div class='d-flex flex-column'>
                <!-- course admin menu -->
                @if ($is_editor)
                    @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                @endif
                @if ($course_code) {{-- course --}}
                    <div class='d-flex justify-content-end align-items-end gap-2 mt-3'>
                @else
                    <div class='d-flex justify-content-end align-items-end gap-2'>
                @endif
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
                                        <a class='btn deleteAdminBtn text-decoration-none' href="javascript:$('#form_id').submit();"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langActivate') }}" aria-label="{{ trans('langActivate') }}">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </a>
                                    @else
                                        <a class='btn successAdminBtn text-decoration-none' href="javascript:$('#form_id').submit();"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langDeactivate') }}" aria-label="{{ trans('langDeactivate') }}">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                    @endif
                                @endif
                            </form>
                    @endif

                    @if (defined('RSS')) {{-- rss link --}}
                        <a class='btn btn-default text-decoration-none tiny-icon-rss' href="{{RSS}}"
                           data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ defined('RSS_TITLE')? RSS_TITLE: trans('langRSSFeed') }}"
                           aria-label="{{ defined('RSS_TITLE')? RSS_TITLE: trans('langRSSFeed') }}">
                           <span class="fa-solid {{ defined('RSS_ICON')? RSS_ICON: 'fa-rss' }}"></span>
                        </a>
                    @endif
                    @if ($require_help) {{-- help icon / link --}}
                        <a id='help-btn' href='{{ $urlServer }}modules/help/help.php?language={{ $language }}&topic={{ $helpTopic }}&subtopic={{ $helpSubTopic }}'
                            class='btn helpAdminBtn text-decoration-none' data-bs-toggle='tooltip' data-bs-placement='bottom'
                            title data-bs-original-title="{{ trans('langHelp') }}" aria-label="{{ trans('langHelp') }}" tabindex="-1" role="button">
                            <i class="fas fa-question-circle"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

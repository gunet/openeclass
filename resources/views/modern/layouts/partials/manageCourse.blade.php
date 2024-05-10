
<div class="dropdown h-40px" style='z-index:2;'>
    <button class="btn submitAdminBtnDefault manageCourseBtn float-end d-flex justify-content-center align-items-center gap-2" type="button" id="dropdownManageCourse" data-bs-toggle="dropdown" data-bs-display='static' aria-expanded="false" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-gear"></i>
            <span class='hidden-lg hidden-md hidden-xs TextBold'>{{trans('langModifyInfo')}}</span>
            <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border contextual-menu-manage-course" aria-labelledby="dropdownManageCourse">
        <ul class="list-group list-group-flush">
            @if ($is_course_admin)
                <li>
                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/course_info/index.php?course={{$coursePrivateCode}}">
                        <i class="fa-solid fa-gears settings-icons"></i>
                        {{trans('langCourseInfo')}}
                    </a>
                </li>
            @endif
            @if ($is_course_admin)
                <li>
                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/course_home/editdesc.php?course={{$coursePrivateCode}}">
                        <i class="fa-solid fa-pen-to-square settings-icons"></i>
                        {{trans('langDescription')}}
                    </a>
                </li>
            @endif
            @if ($is_course_admin)
                <li>
                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/user/index.php?course={{$coursePrivateCode}}">
                        <i class="fa-solid fa-user settings-icons"></i>
                        {{trans('langUsers')}}
                    </a>
                </li>
            @endif

            <li>
                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/usage/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-chart-simple settings-icons"></i>
                    {{trans('langUsage')}}
                </a>
            </li>

            @if ($is_course_admin)
                <li>
                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/course_tools/index.php?course={{$coursePrivateCode}}">
                        <i class="fa-solid fa-screwdriver-wrench settings-icons"></i>
                        {{trans('langTools')}}
                    </a>
                </li>
            @endif
            
            @if ($is_course_admin)
                <li>
                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/abuse_report/index.php?course={{$coursePrivateCode}}">
                        <i class="fa-solid fa-flag settings-icons"></i>
                        {{trans('langAbuseReports')}}
                    </a>
                </li>
            @endif
            
            @if(isset($is_collaborative_course) and !$is_collaborative_course)
                @if ($is_course_admin)
                    <li>
                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/course_prerequisites/index.php?course={{$coursePrivateCode}}">
                            <i class="fa-solid fa-building-columns settings-icons"></i>
                            {{trans('langCoursePrerequisites')}}     
                        </a>
                    </li>
                @endif

                @if ($is_course_admin)
                    <li>
                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/course_widgets/index.php?course={{$coursePrivateCode}}">
                            <i class="fa-solid fa-wand-magic-sparkles settings-icons"></i>
                            {{trans('langWidgets')}}
                        </a>
                    </li>
                @endif

            
                @if ($is_course_admin)
                    <li>
                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/lti_consumer/index.php?course={{$coursePrivateCode}}">
                            <i class="fa-solid fa-link settings-icons"></i>
                            {{trans('langLtiConsumer')}}
                        </a>
                    </li>
                @endif

                @if ($is_course_admin)
                    <li>
                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/analytics/index.php?course={{$coursePrivateCode}}">
                            <i class="fa-solid fa-chart-line settings-icons"></i>
                            {{trans('langLearningAnalytics')}}
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
</div>




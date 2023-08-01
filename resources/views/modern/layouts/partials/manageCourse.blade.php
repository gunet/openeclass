
<div class="dropdown">
    <button class="btn submitAdminBtn submitAdminBtnDefault manageCourseBtn float-end d-flex justify-content-center align-items-center gap-2" type="button" id="dropdownManageCourse" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-gear"></i>
            <span class='hidden-lg hidden-md hidden-xs TextBold'>{{trans('langModifyInfo')}}</span>
            <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border" aria-labelledby="dropdownManageCourse">
        <ul class="list-group list-group-flush">
            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/course_info/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-gears pe-2"></i>
                    {{trans('langCourseInfo')}}
                </a>
            </li>
            @if ($is_course_admin)
                <li>
                    <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/course_home/editdesc.php?course={{$coursePrivateCode}}">
                        <i class="fa-solid fa-pen-to-square pe-2"></i>
                        {{trans('langDescription')}}
                    </a>
                </li>
            @endif
            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/user/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-user pe-2"></i>
                    {{trans('langUsers')}}
                </a>
            </li>

            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/usage/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-chart-simple pe-2"></i>
                    {{trans('langUsage')}}
                </a>
            </li>

            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/course_tools/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-screwdriver-wrench pe-2"></i>
                    {{trans('langTools')}}
                </a>
            </li>
            
            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/abuse_report/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-flag pe-2"></i>
                    {{trans('langAbuseReports')}}
                </a>
            </li>
            
            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/course_prerequisites/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-building-columns pe-2"></i>
                    {{trans('langCoursePrerequisites')}}     
                </a>
            </li>

            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/course_widgets/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-wand-magic-sparkles pe-2"></i>
                    {{trans('langWidgets')}}
                </a>
            </li>

            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/lti_consumer/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-link pe-2"></i>
                    {{trans('langLtiConsumer')}}
                </a>
            </li>

            <li>
                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/analytics/index.php?course={{$coursePrivateCode}}">
                    <i class="fa-solid fa-chart-line pe-2"></i>
                    {{trans('langLearningAnalytics')}}
                </a>
            </li>
        </ul>
    </div>
</div>





<div class="dropdown dropstart">
    <button class="btn btn-primary rounded dropdown-toggle float-end @if($toolName) mt-4 @else mt-2 @endif" type="button" id="dropdownManageCourse" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-expanded="false" data-bs-toggle-second="tooltip" data-bs-placement="left" title="{{trans('langModifyInfo')}}">
        <i class="fas fa-tasks"></i>
    </button>
    <ul class="dropdown-menu manage-course-ul" aria-labelledby="dropdownManageCourse">
        <li class='bg-primary active-manage-course-li'><span class="manage-course-title ms-2 me-2 fs-5"><i class="fas fa-bank"></i> {{ trans('langAdm') }}</span></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_info/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-cogs"></i>&nbsp;&nbsp;{{trans('langCourseInfo')}}</a></li>
        @if ($is_course_admin)
            <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_home/editdesc.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-edit"></i>&nbsp;&nbsp;{{trans('langDescription')}}</a></li>
        @endif
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/user/index.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-user"></i>&nbsp;&nbsp;{{trans('langUsers')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/usage/index.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-chart-bar"></i>&nbsp;&nbsp;{{trans('langUsage')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_tools/index.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-wrench"></i>&nbsp;&nbsp;{{trans('langTools')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/abuse_report/index.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-flag"></i>&nbsp;&nbsp;{{trans('langAbuseReports')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_prerequisites/index.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-university"></i>&nbsp;&nbsp;{{trans('langCoursePrerequisites')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_widgets/index.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-magic"></i>&nbsp;&nbsp;{{trans('langWidgets')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/lti_consumer/index.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-link"></i>&nbsp;&nbsp;{{trans('langLtiConsumer')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/analytics/index.php?course={{$coursePrivateCode}}" class="manage-course-item ps-3 pe-2"><i class="fas fa-chart-line"></i>&nbsp;&nbsp;{{trans('langLearningAnalytics')}}</a></li>
    </ul>
</div>




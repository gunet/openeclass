
<div class="dropdown dropstart">
    <button class="btn btn-primary dropdown-toggle mb-3" type="button" id="dropdownManageCourse" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-expanded="false" data-bs-toggle-second="tooltip" data-bs-placement="left" title="{{ trans('langModifyInfo') }}">
        <i class="fas fa-tasks"></i>
    </button>
    <ul class="dropdown-menu manage-course-ul" aria-labelledby="dropdownManageCourse">
        <li style="background-color:#0d6efd; border:solid 2px #0d6efd; height:28px; margin-top:-10px;"><span class="manage-course-title ms-3 me-3">{{ trans('langModifyInfo') }}</span></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_info/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-cogs"></i>&nbsp;&nbsp;{{ trans('langCourseInfo') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_home/editdesc.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-edit"></i>&nbsp;&nbsp; {{ trans('langCourseEdit') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/user/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-user"></i>&nbsp;&nbsp; {{ trans('langUsers') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/usage/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-chart-bar"></i>&nbsp;&nbsp; {{ trans('langUsage') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_tools/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-wrench"></i>&nbsp;&nbsp; {{ trans('langTools') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/abuse_report/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-flag"></i>&nbsp;&nbsp; {{ trans('langAbuseReports') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_prerequisites/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-university"></i>&nbsp;&nbsp; {{ trans('langCoursePrerequisites') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_widgets/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-magic"></i>&nbsp;&nbsp; {{ trans('langWidgets') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/lti_consumer/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-link"></i>&nbsp;&nbsp; {{ trans('langLtiConsumer') }}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/analytics/index.php?course={{ $course_code }}" class="manage-course-item"><i class="fas fa-chart-line"></i>&nbsp;&nbsp; {{ trans('langLearningAnalytics') }}</a></li>
    </ul>
</div>


<div class="dropdown dropstart">
    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownManageCourse" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-expanded="false" data-bs-toggle-second="tooltip" data-bs-placement="left" title="{{trans('langModifyInfo')}}">
        <i class="fas fa-tasks"></i>
    </button>
    <ul class="dropdown-menu manage-course-ul" aria-labelledby="dropdownManageCourse">
        <li style="background-color:#0d6efd; border:solid 2px #0d6efd; height:28px; margin-top:-10px;"><span class="manage-course-title ms-3 me-3">{{trans('langModifyInfo')}}</span></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_info/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-cogs"></i> {{trans('langCourseInfo')}}</a></li>
        @if ($is_course_admin)                              
            <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_home/editdesc.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-edit"></i> {{trans('langCourseEdit')}}</a></li>
        @endif
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/user/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-user"></i> {{trans('langUsers')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/usage/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-chart-bar"></i> {{trans('langUsage')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_tools/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-wrench"></i> {{trans('langTools')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/abuse_report/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-flag"></i> {{trans('langAbuseReports')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_prerequisites/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-university"></i> {{trans('langCoursePrerequisites')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/course_widgets/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-magic"></i> {{trans('langWidgets')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/lti_consumer/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-link"></i> {{trans('langLtiConsumer')}}</a></li>
        <li class="manage-course-li"><a href="{{ $urlAppend }}modules/analytics/index.php?course={{$coursePrivateCode}}" class="manage-course-item"><i class="fas fa-chart-line"></i> {{trans('langLearningAnalytics')}}</a></li>
    </ul>
</div>




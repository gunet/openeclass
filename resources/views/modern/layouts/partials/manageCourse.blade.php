
<div class="dropdown dropstart">
    <button class="btn btn-sm btn-primary rounded dropdown-toggle float-end" type="button" id="dropdownManageCourse" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-expanded="false">
        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{trans('langModifyInfo')}}"><i class="fas fa-tasks"></i></span>
    </button>
    <ul class="p-0 m-0 dropdown-menu manage-course-ul shadow-lg border border-secondary" aria-labelledby="dropdownManageCourse">
        <li class='manage-course-li-active border-bottom border-secondary text-center p-2 bgNormalBlueText'>
            <span class="d-inline fas fa-bank text-white"></span>
            <span class="d-inline text-white"> {{trans('langModifyInfo')}}</span>
        </li>
        <li class="manage-course-li border-0">
            <a href="{{ $urlAppend }}modules/course_info/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                    <span class='blackBlueText'>{{trans('langCourseInfo')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-cogs blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>
        @if ($is_course_admin)
            <li class="manage-course-li border-0">
                <a href="{{ $urlAppend }}modules/course_home/editdesc.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                    <div class='row'>
                        <div class='col-10 text-start'>
                            <span class='blackBlueText'>{{trans('langDescription')}}</span>
                        </div>
                        <div class='col-2 text-end'>
                            <span class="pt-1 fas fa-edit blackBlueText"></span>
                        </div>
                    </div>
                </a>
            </li>
        @endif
        <li class="manage-course-li border-0">
            <a href="{{ $urlAppend }}modules/user/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                        <span class="blackBlueText">{{trans('langUsers')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-user blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>

        <li class="manage-course-li border-0">
            <a href="{{ $urlAppend }}modules/usage/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                        <span class="blackBlueText">{{trans('langUsage')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-chart-bar blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>

        <li class="manage-course-li border-0">
            <a href="{{ $urlAppend }}modules/course_tools/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                        <span class="blackBlueText">{{trans('langTools')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-wrench blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>
        
        <li class="manage-course-li border-0">
            <a href="{{ $urlAppend }}modules/abuse_report/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                        <span class="blackBlueText">{{trans('langAbuseReports')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-flag blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>
        
        <li class="manage-course-li border-0">
            <a href="{{ $urlAppend }}modules/course_prerequisites/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                        <span class="blackBlueText">{{trans('langCoursePrerequisites')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-university blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>

        <li class="manage-course-li border-0">
            <a href="{{ $urlAppend }}modules/course_widgets/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                        <span class="blackBlueText">{{trans('langWidgets')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-magic blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>

        <li class="manage-course-li border-0">
            <a href="{{ $urlAppend }}modules/lti_consumer/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                        <span class="blackBlueText">{{trans('langLtiConsumer')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-link blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>

        <li class="manage-course-li border border-0">
            <a href="{{ $urlAppend }}modules/analytics/index.php?course={{$coursePrivateCode}}" class="list-group-item border border-top-0 border-bottom-secondary text-dark TextMedium">
                <div class='row'>
                    <div class='col-10 text-start'>
                        <span class="blackBlueText">{{trans('langLearningAnalytics')}}</span>
                    </div>
                    <div class='col-2 text-end'>
                        <span class="pt-1 fas fa-chart-line blackBlueText"></span>
                    </div>
                </div>
            </a>
        </li>
    </ul>
</div>




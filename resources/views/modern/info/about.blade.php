@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                <div class="row p-5">
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                            <a type="button" id="getTopicButton" class="d-none d-sm-block d-md-none d-lg-block ms-2 btn btn-primary btn btn-primary" href="{{$urlAppend}}modules/help/help.php?language={{$language}}&topic={{$helpTopic}}&subtopic={{$helpSubTopic}}" style='margin-top:-10px'>
                                <i class="fas fa-question"></i>
                            </a>
                        </nav>
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    </div>

                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3">
                        <legend class="float-none w-auto py-2 px-4 notes-legend"><span style="margin-left:-20px;"><i class="fa fa-credit-card fa-fw"></i> {{trans('langPlatformIdentity')}}</span></legend>
                        <div class="row p-2"></div>
                    </div>
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='row'>
                            <div class='text-start text-secondary'>{{trans('langEclass')}} - {{trans('langInfo')}}</div>
                            {!! $action_bar !!}
                        </div>
                    </div>


                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='panel shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <div class='panel-body'>
                                <div><strong class='control-label-notes'>{{ trans('langInstituteShortName') }}:</strong> <a href='{{ $institution_url }}' target='_blank' class='mainpage'>{{ $institution }}</a></div>
                                <div><strong class='control-label-notes'>{{ trans('langCampusName') }}:</strong> {{ $siteName }}</div>
                                <div><strong class='control-label-notes'>{{ trans('langVersion') }}:</strong> <a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank'>{{ $eclass_version }}</a></div>
                                <div><strong class='control-label-notes'>{{ trans('langSupportUser') }}:</strong> {{ $admin_name }}</div>
                            </div>
                        </div>
                    </div>


                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='row'>
                            <div class='col-sm-6'>
                                <ul class='list-group'>
                                    <li class='list-group-item'><strong>{{ trans('langCourses') }}</strong><span class='badge text-secondary float-end'>{{ $course_inactive }}</span></li>
                                    <li class='list-group-item'>{{ trans('langOpenCoursesShort') }}<span class='badge text-secondary float-end'>{{ $course_open }}</span></li>
                                    <li class='list-group-item'>{{ trans('langOpenCourseWithRegistration') }}<span class='badge text-secondary float-end'>{{ $course_registration }}</span></li>
                                    <li class='list-group-item'>{{ trans('langClosedCourses') }}<span class='badge text-secondary float-end'>{{ $course_closed }}</span></li>
                                </ul>
                            </div>
                            <div class='col-sm-6'>
                                <ul class='list-group'>
                                    <li class='list-group-item'><label>{{ trans('langUsers') }}</label><span class='badge text-secondary float-end'>{{ $count_total }}</span></li>
                                    <li class='list-group-item'>{{ trans('langTeachers') }}<span class='badge text-secondary float-end'>{{ $count_status[USER_TEACHER] }}</span></li>
                                    <li class='list-group-item'>{{ trans('langStudents') }}<span class='badge text-secondary float-end'>{{ $count_status[USER_STUDENT] }}</span></li>
                                    <li class='list-group-item'>{{ trans('langGuest') }}<span class='badge text-secondary float-end'>{{ $count_status[USER_GUEST] }}</span> </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

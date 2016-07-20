@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    <div class='row'>
        <div class='col-sm-12'>
            <div class='panel'>
                <div class='panel-body'>
                    <div><strong>{{ trans('langInstituteShortName') }}:</strong> <a href='{{ $institution_url }}' target='_blank' class='mainpage'>{{ $institution }}</a></div>
                    <div><strong>{{ trans('langCampusName') }}:</strong> {{ $siteName }}</div>
                    <div><strong>{{ trans('langVersion') }}:</strong> <a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank'>{{ $eclass_version }}</a></div>
                    <div><strong>{{ trans('langSupportUser') }}:</strong> {{ $admin_name }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class='row'>
        <div class='col-sm-6'>
            <ul class='list-group'>
                <li class='list-group-item'><strong>{{ trans('langCourses') }}</strong><span class='badge'>{{ $course_inactive }}</span></li>
                <li class='list-group-item'>{{ trans('langOpenCoursesShort') }}<span class='badge'>{{ $course_open }}</span></li>
                <li class='list-group-item'>{{ trans('langOpenCourseWithRegistration') }}<span class='badge'>{{ $course_registration }}</span></li>
                <li class='list-group-item'>{{ trans('langClosedCourses') }}<span class='badge'>{{ $course_closed }}</span></li>
            </ul>
        </div>
        <div class='col-sm-6'>
            <ul class='list-group'>
                <li class='list-group-item'><label>{{ trans('langUsers') }}</label><span class='badge'>{{ $count_total }}</span></li>
                <li class='list-group-item'>{{ trans('langTeachers') }}<span class='badge'>{{ $count_status[USER_TEACHER] }}</span></li>
                <li class='list-group-item'>{{ trans('langStudents') }}<span class='badge'>{{ $count_status[USER_STUDENT] }}</span></li>
                <li class='list-group-item'>{{ trans('langGuest') }}<span class='badge'>{{ $count_status[USER_GUEST] }}</span> </li>
            </ul>
        </div>
    </div>

@endsection

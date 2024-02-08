@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    <div class='col-12'>
                        <h1>{{ trans('langPlatformIdentity') }}</h1>
                    </div>

                    
                    <div class='col-lg-4 col-12 d-flex justify-content-center align-items-center mb-lg-0 mb-5'>
                        <img class='contactImage' src="{{ $urlAppend }}template/modern/img/indexlogo.png" alt="Some information">
                    </div>
                    <div class='col-lg-8 col-12'>
                        <div class='row'>
                            <div class='col-md-6 col-12'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class="fa-solid fa-address-card fa-lg"></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong class='form-label'>{{ trans('langInstituteShortName') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'><a href='{{ $institution_url }}' target='_blank' class='mainpage' aria-label='{{ $institution }}'>{{ $institution }}</a></div>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-5'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class="fa-solid fa-address-card fa-lg"></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong class='form-label'>{{ trans('langCampusName') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'>{{ $siteName }}</div>
                            </div>
                        </div>
                        <div class='row mt-5'>
                            <div class='col-md-6 col-12 mt-md-0 mt-2'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class="fa-solid fa-address-card fa-lg"></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong class='form-label'>{{ trans('langVersion') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'><a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank' aria-label='{{ $eclass_version }}'>{{ $eclass_version }}</a></div>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-5'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class="fa-solid fa-address-card fa-lg"></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong class='form-label'>{{ trans('langSupportUser') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'>{{ $admin_name }}</div>
                            </div>
                        </div>
                    </div>
                    

                    
                    <div class='col-12 mt-5'>
                        <div class='row row-cols-1 row-cols-md-2 g-3 g-md-4'>
                            <div class='col'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-5 flex-wrap'>
                                        <div>{{ trans('langCourses') }}</div>
                                        <div>{{ $course_inactive }}</div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langOpenCoursesShort') }}</div>
                                        <div>{{ $course_open }}</div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langOpenCourseWithRegistration') }}</div>
                                        <div>{{ $course_registration }}</div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langClosedCourses') }}</div>
                                        <div>{{ $course_closed }}</div>
                                    </li>
                                </ul>
                            </div>
                            <div class='col'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-5 flex-wrap'>
                                        <div>{{ trans('langUsers') }}</div>
                                        <div>{{ $count_total }}</div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langTeachers') }}</div>
                                        <div>{{ $count_status[USER_TEACHER] }}</div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langStudents') }}</div>
                                        <div>{{ $count_status[USER_STUDENT] }}</div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langGuest') }}</div>
                                        <div>{{ $count_status[USER_GUEST] }}</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                
        </div>
    </div>
</div>

@endsection

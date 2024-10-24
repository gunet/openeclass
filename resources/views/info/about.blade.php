@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    <div class='col-12 mb-4'>
                        <h1>{{ $toolName }}</h1>
                    </div>

                    <div class='col-12'>
                        <div class='row row-cols-lg-4 row-cols-md-2 row-cols-1 g-4'>
                            <div class='col'>
                                <div class='card panelCard card-default px-lg-4 py-lg-3 w-100 h-100'>
                                    <div class='card-body'>
                                        <div class='col-12 d-flex justify-content-center mb-2 text-center'>
                                            <div class='circle-img-contant'><i class="fa-solid fa-address-card fa-lg"></i></div>
                                        </div>
                                        <div class='col-12 d-flex justify-content-center mb-0 text-center'>
                                            <strong class='form-label'>{{ trans('langInstituteShortNameSecondary') }}</strong>
                                        </div>
                                        <div class='col-12 d-flex justify-content-center text-center'>
                                            <a href='{{ $institution_url }}' target='_blank' class='mainpage' aria-label='{{ $institution }}'>{{ $institution }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col'>
                                <div class='card panelCard card-default px-lg-4 py-lg-3 w-100 h-100'>
                                    <div class='card-body'>
                                        <div class='col-12 d-flex justify-content-center mb-2 text-center'>
                                            <div class='circle-img-contant'><i class="fa-solid fa-address-card fa-lg"></i></div>
                                        </div>
                                        <div class='col-12 d-flex justify-content-center mb-0 text-center'>
                                            <strong class='form-label'>{{ trans('langCampusName') }}</strong>
                                        </div>
                                        <div class='col-12 d-flex justify-content-center text-center'><p class='form-label' style="font-weight:400;">{{ $siteName }}</p></div>
                                    </div>
                                </div>
                            </div>
                            <div class='col'>
                                <div class='card panelCard card-default px-lg-4 py-lg-3 w-100 h-100'>
                                    <div class='card-body'>
                                        <div class='col-12 d-flex justify-content-center mb-2 text-center'>
                                            <div class='circle-img-contant'><i class="fa-solid fa-address-card fa-lg"></i></div>
                                        </div>
                                        <div class='col-12 d-flex justify-content-center mb-0 text-center'>
                                            <strong class='form-label'>{{ trans('langVersion') }}</strong>
                                        </div>
                                        <div class='col-12 d-flex justify-content-center text-center'>
                                            <a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank' aria-label='{{ $eclass_version }}'>{{ $eclass_version }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col'>
                                <div class='card panelCard card-default px-lg-4 py-lg-3 w-100 h-100'>
                                    <div class='card-body'>
                                        <div class='col-12 d-flex justify-content-center mb-2 text-center'>
                                            <div class='circle-img-contant'><i class="fa-solid fa-address-card fa-lg"></i></div>
                                        </div>
                                        <div class='col-12 d-flex justify-content-center mb-0 text-center'>
                                            <strong class='form-label'>{{ trans('langSupportUser') }}</strong>
                                        </div>
                                        <div class='col-12 d-flex justify-content-center text-center'><p class='form-label' style="font-weight:400;">{{ $admin_name }}</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class='col-12 mt-5'>
                        <div class='row row-cols-1 row-cols-md-2 g-3 g-md-4'>
                            <div class='col'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-5 flex-wrap'>
                                        <div class='d-flex justify-content-start align-items-start gap-2'><i class="fa-solid fa-book-open mt-1" role="presentation"></i>{{ trans('langCourses') }}</div>
                                        <div><span class='badge Primary-600-bg'>{{ $course_inactive }}</span></div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langOpenCoursesShort') }}</div>
                                        <div><span class='badge Success-200-bg'>{{ $course_open }}</span></div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langOpenCourseWithRegistration') }}</div>
                                        <div><span class='badge Warning-200-bg'>{{ $course_registration }}</span></div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langClosedCourses') }}</div>
                                        <div><span class='badge Accent-200-bg'>{{ $course_closed }}</span></div>
                                    </li>
                                </ul>
                            </div>
                            <div class='col'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-5 flex-wrap'>
                                        <div class='d-flex justify-content-start align-items-start gap-2'><i class="fa-solid fa-user mt-1" role="presentation"></i>{{ trans('langUsers') }}</div>
                                        <div><span class='badge Primary-600-bg'>{{ $count_total }}</span></div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langTeachers') }}</div>
                                        <div><span class='badge Success-200-bg'>{{ $count_status[USER_TEACHER] }}</span></div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langStudents') }}</div>
                                        <div><span class='badge Warning-200-bg'>{{ $count_status[USER_STUDENT] }}</span></div>
                                    </li>
                                    <li class='list-group-item element d-flex justify-content-between align-items-centergap-5 flex-wrap'>
                                        <div>{{ trans('langGuest') }}</div>
                                        <div><span class='badge Accent-200-bg'>{{ $count_status[USER_GUEST] }}</span></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>


        </div>
    </div>
</div>

@endsection

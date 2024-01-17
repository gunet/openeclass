@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    <div class='col-12'>
                        <h1>{{ trans('langPlatformIdentity') }}</h1>
                    </div>

                    
                    <div class='col-lg-4 col-12 d-flex justify-content-center align-items-center mb-lg-0 mb-5'>
                        <img class='contactImage' src="{{ $urlAppend }}template/modern/img/indexlogo.png">
                    </div>
                    <div class='col-lg-8 col-12'>
                        <div class='row'>
                            <div class='col-md-6 col-12'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong class='form-label'>{{ trans('langInstituteShortName') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'><a href='{{ $institution_url }}' target='_blank' class='mainpage'>{{ $institution }}</a></div>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-5'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong class='form-label'>{{ trans('langCampusName') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center form-value'>{{ $siteName }}</div>
                            </div>
                        </div>
                        <div class='row mt-5'>
                            <div class='col-md-6 col-12 mt-md-0 mt-2'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong class='form-label'>{{ trans('langVersion') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'><a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank'>{{ $eclass_version }}</a></div>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-5'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong class='form-label'>{{ trans('langSupportUser') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center form-value'>{{ $admin_name }}</div>
                            </div>
                        </div>
                                {{--<div class='p-1'><strong class='control-label-notes'>{{ trans('langInstituteShortName') }}:</strong> <a href='{{ $institution_url }}' target='_blank' class='mainpage'>{{ $institution }}</a></div>
                                <div class='p-1'><strong class='control-label-notes'>{{ trans('langCampusName') }}:</strong> {{ $siteName }}</div>
                                <div class='p-1'><strong class='control-label-notes'>{{ trans('langVersion') }}:</strong> <a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank'>{{ $eclass_version }}</a></div>
                                <div class='p-1'><strong class='control-label-notes'>{{ trans('langSupportUser') }}:</strong> {{ $admin_name }}</div>--}}
                    </div>
                    

                    
                        <div class='col-12 mt-5'>
                            <div class='row'>
                                <div class='col-md-6 col-12'>
                                    <ul class='list-group list-group-flush'>
                                        <li class='list-group-item'><strong class='Primary-500-cl text-uppercase'>{{ trans('langCourses') }}</strong><span class='badge Primary-500-bg text-white float-end'>{{ $course_inactive }}</span></li>
                                        <li class='list-group-item list-about'>{{ trans('langOpenCoursesShort') }}<span class='badge bg-secondary text-white float-end'>{{ $course_open }}</span></li>
                                        <li class='list-group-item list-about'>{{ trans('langOpenCourseWithRegistration') }}<span class='badge bg-secondary text-white float-end'>{{ $course_registration }}</span></li>
                                        <li class='list-group-item list-about'>{{ trans('langClosedCourses') }}<span class='badge bg-secondary text-white float-end'>{{ $course_closed }}</span></li>
                                    </ul>
                                </div>
                                <div class='col-md-6 col-12 mt-md-0 mt-3'>
                                    <ul class='list-group list-group-flush'>
                                        <li class='list-group-item'><strong class='Primary-500-cl text-uppercase'>{{ trans('langUsers') }}</strong><span class='badge Primary-500-bg text-white float-end'>{{ $count_total }}</span></li>
                                        <li class='list-group-item list-about'>{{ trans('langTeachers') }}<span class='badge bg-secondary text-white float-end'>{{ $count_status[USER_TEACHER] }}</span></li>
                                        <li class='list-group-item list-about'>{{ trans('langStudents') }}<span class='badge bg-secondary text-white float-end'>{{ $count_status[USER_STUDENT] }}</span></li>
                                        <li class='list-group-item list-about'>{{ trans('langGuest') }}<span class='badge bg-secondary text-white float-end'>{{ $count_status[USER_GUEST] }}</span> </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                
        </div>
    </div>
</div>

@endsection

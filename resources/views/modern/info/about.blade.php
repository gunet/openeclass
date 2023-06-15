@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class='col-12 mt-3 mb-3'>
                        <div class='text-md-start text-center ms-md-2 text-secondary'>{{trans('langEclass')}} - {{trans('langInfo')}}</div>
                    </div>

                    
                    <div class='col-lg-4 col-12 d-flex justify-content-center align-items-center mb-lg-0 mb-5'>
                        <img class='contactImage' src="{{ $urlAppend }}template/modern/img/indexlogo.png">
                    </div>
                    <div class='col-lg-8 col-12'>
                        <div class='row'>
                            <div class='col-md-6 col-12'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong>{{ trans('langInstituteShortName') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'><a href='{{ $institution_url }}' target='_blank' class='mainpage'>{{ $institution }}</a></div>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-5'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong>{{ trans('langCampusName') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'>{{ $siteName }}</div>
                            </div>
                        </div>
                        <div class='row mt-5'>
                            <div class='col-md-6 col-12 mt-md-0 mt-2'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong>{{ trans('langVersion') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'><a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank'>{{ $eclass_version }}</a></div>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-5'>
                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                <div class='col-12 d-flex justify-content-center mb-0'><strong>{{ trans('langSupportUser') }}:</strong></div>
                                <div class='col-12 d-flex justify-content-center'>{{ $admin_name }}</div>
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
                                    <li class='list-group-item'><strong class='lightBlueText text-uppercase'>{{ trans('langCourses') }}</strong><span class='badge bgTheme text-white float-end'>{{ $course_inactive }}</span></li>
                                    <li class='list-group-item'>{{ trans('langOpenCoursesShort') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $course_open }}</span></li>
                                    <li class='list-group-item'>{{ trans('langOpenCourseWithRegistration') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $course_registration }}</span></li>
                                    <li class='list-group-item'>{{ trans('langClosedCourses') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $course_closed }}</span></li>
                                </ul>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-3'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item'><strong class='lightBlueText text-uppercase'>{{ trans('langUsers') }}</strong><span class='badge bgTheme text-white float-end'>{{ $count_total }}</span></li>
                                    <li class='list-group-item'>{{ trans('langTeachers') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $count_status[USER_TEACHER] }}</span></li>
                                    <li class='list-group-item'>{{ trans('langStudents') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $count_status[USER_STUDENT] }}</span></li>
                                    <li class='list-group-item'>{{ trans('langGuest') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $count_status[USER_GUEST] }}</span> </li>
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

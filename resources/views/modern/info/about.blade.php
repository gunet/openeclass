@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}
                    
                    <div class='col-12'>
                        
                        <div class="card panelCard px-lg-4 py-lg-3">
                            <div class='card-body'>
                                <div class='row'>
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
                                </div>
                            </div>
                        </div>
                       

                        <div class='row mt-4'>
                            <div class='col-md-6 col-12'>
                                <div class="card panelCard px-lg-4 py-lg-3">
                                    <div class='card-body'>
                                        <ul class='list-group list-group-flush'>
                                            <li class='list-group-item px-0'><strong class='lightBlueText text-uppercase'>{{ trans('langCourses') }}</strong><span class='badge bgTheme text-white float-end'>{{ $course_inactive }}</span></li>
                                            <li class='list-group-item px-0'>{{ trans('langOpenCoursesShort') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $course_open }}</span></li>
                                            <li class='list-group-item px-0'>{{ trans('langOpenCourseWithRegistration') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $course_registration }}</span></li>
                                            <li class='list-group-item px-0'>{{ trans('langClosedCourses') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $course_closed }}</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-4'>
                                <div class="card panelCard px-lg-4 py-lg-3">
                                    <div class='card-body'>
                                        <ul class='list-group list-group-flush'>
                                            <li class='list-group-item px-0'><strong class='lightBlueText text-uppercase'>{{ trans('langUsers') }}</strong><span class='badge bgTheme text-white float-end'>{{ $count_total }}</span></li>
                                            <li class='list-group-item px-0'>{{ trans('langTeachers') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $count_status[USER_TEACHER] }}</span></li>
                                            <li class='list-group-item px-0'>{{ trans('langStudents') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $count_status[USER_STUDENT] }}</span></li>
                                            <li class='list-group-item px-0'>{{ trans('langGuest') }}<span class='badge bgEclass normalColorBlueText float-end'>{{ $count_status[USER_GUEST] }}</span> </li>
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

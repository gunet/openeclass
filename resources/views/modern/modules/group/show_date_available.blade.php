@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    

                    @if(Session::has('message'))
                        <div class='col-12 all-alerts'>
                            <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                @php
                                    $alert_type = '';
                                    if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                        $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                    }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                        $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                    }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                        $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                    }else{
                                        $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                    }
                                @endphp

                                @if(is_array(Session::get('message')))
                                    @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                    {!! $alert_type !!}<span>
                                    @foreach($messageArray as $message)
                                        {!! $message !!}
                                    @endforeach</span>
                                @else
                                    {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                                @endif

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif


                    @if($is_course_admin or $is_tutor)
                        <div class='col-12'>
                            @if($is_course_admin)
                                @if(count($group_tutors) > 0)
                                    <div class='row row-cols-1 g-4'>
                                        @foreach($group_tutors as $tutor)
                                            <div class='col'>
                                                <div class="card panelCard px-lg-4 py-lg-3 h-100 mb-3">
                                                    <div class='card-body'>
                                                        <div class='col-12'>
                                                            <div class="row m-auto g-4">
                                                                <div class="col-md-4">
                                                                    <div class="text-center">
                                                                        @php $image_tutor = profile_image($tutor->user_id, IMAGESIZE_LARGE, 'img-responsive img-circle img-profile img-public-profile'); @endphp
                                                                        {!! $image_tutor !!}
                                                                        <h4 class='mt-2'>{{ $tutor->givenname }}&nbsp;{{ $tutor->surname }}</h4>
                                                                        <p class="badge Success-200-bg text-white vsmall-text TextBold rounded-pill px-2 py-1">{{ trans('langGroupTutor')}}</p></br>
                                                                        @if(count($nextAvDate) > 0)
                                                                            @foreach($nextAvDate as $d)
                                                                                @foreach(array_keys($d) as $key)
                                                                                    @if($key == $tutor->user_id)
                                                                                        <h5 class='mt-2 mb-0 text-decoration-underline'>{{ trans('langNextAvailableDate')}}</h5>
                                                                                        <h5>{{ format_locale_date(strtotime($d[$key]['start']), 'short') }} </h5>
                                                                                    @endif
                                                                                @endforeach
                                                                            @endforeach
                                                                        @endif

                                                                        <a class='btn submitAdminBtn d-inline-flex mt-3' href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;bookings_of_tutor={{ $tutor->user_id }}">
                                                                            {{ trans('langAvailableTutorBookings')}}
                                                                        </a></br>

                                                                        <a class='btn submitAdminBtn d-inline-flex mt-3' href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;history_booking={{ $tutor->user_id }}">
                                                                            {{ trans('langAvailableHistoryBookings')}}
                                                                        </a></br>
                                                                        
                                                                        <a class='btn submitAdminBtnDefault d-inline-flex mt-3' href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;add_for_tutor={{ $tutor->user_id }}">
                                                                            {{ trans('langAddAvailability')}}
                                                                        </a>

                                                                        
                                                                        
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    @include('modules.group.tutor_calendar',['editorId' => $tutor->user_id, 'CourseID' => $course_id])
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class='row row-cols-1 g-4'>
                                    <div class='col'>
                                        <div class="card panelCard px-lg-4 py-lg-3 mb-3">
                                            <div class='card-body'>
                                                <div class='col-12'>
                                                    <div class="row m-auto g-4">
                                                        <div class="col-md-4">
                                                            <div class="text-center">
                                                                @php $image_tutor = profile_image($uid, IMAGESIZE_LARGE, 'img-responsive img-circle img-profile img-public-profile'); @endphp
                                                                {!! $image_tutor !!}
                                                                <h4 class='mt-2'>{{ $tutor_name }}&nbsp;{{ $surname_name }}</h4>
                                                                <p class="badge Success-200-bg text-white vsmall-text TextBold rounded-pill px-2 py-1">{{ trans('langGroupTutor')}}</p></br>
                                                                @if(count($nextAvDate) > 0)
                                                                    @foreach($nextAvDate as $d)
                                                                        @foreach(array_keys($d) as $key)
                                                                            @if($key == $uid)
                                                                                <h5 class='mt-2 mb-0 text-decoration-underline'>{{ trans('langNextAvailableDate')}}</h5>
                                                                                <h5>{{ format_locale_date(strtotime($d[$key]['start']), 'short') }} </h5>
                                                                            @endif
                                                                        @endforeach
                                                                    @endforeach
                                                                @endif
                                                                
                                                                <a class='btn submitAdminBtn d-inline-flex mt-3' href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;bookings_of_tutor={{ $uid }}">
                                                                    {{ trans('langMyAvailableBookings')}}
                                                                </a></br>

                                                                <a class='btn submitAdminBtn d-inline-flex mt-3' href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;history_booking={{ $uid }}">
                                                                    {{ trans('langAvailableHistoryBookings')}}
                                                                </a></br>

                                                                <a class='btn submitAdminBtnDefault d-inline-flex mt-3' href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;add_for_tutor={{ $uid }}">
                                                                    {{ trans('langAddAvailability')}}
                                                                </a>

                                                                
                                                                
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8">
                                                            @include('modules.group.tutor_calendar',['editorId' => $uid, 'CourseID' => $course_id])
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif



                </div>
            </div>
        </div>
    </div>
</div>




@endsection
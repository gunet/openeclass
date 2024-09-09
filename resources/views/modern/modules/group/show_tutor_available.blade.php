@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    @if($is_editor)
                        {!! isset($action_bar) ?  $action_bar : '' !!}
                    @endif

                    @include('layouts.partials.show_alert') 
                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if($is_member && !$is_tutor)
                        @if(count($group_tutors) > 0)
                            <div class='col-12'>
                                <div class='row row-cols-1 g-4'>
                                    @foreach($group_tutors as $tutor)
                                        
                                        <div class='col'>
                                            <div class="card panelCard px-lg-4 py-lg-3 mb-3">
                                                <div class='card-body'>
                                                    <div class='col-12'>
                                                        <div class="row m-auto g-4">
                                                            <div class="col-md-4">
                                                                <div class="text-center">
                                                                    @php $image_tutor = profile_image($tutor->user_id, IMAGESIZE_LARGE, 'img-responsive img-circle img-profile img-public-profile'); @endphp
                                                                    {!! $image_tutor !!}
                                                                    <h4 class='mt-2'>{{ $tutor->givenname }}&nbsp;{{ $tutor->surname }}</h4>
                                                                    <p class="badge Success-200-bg vsmall-text TextBold rounded-pill px-2 py-1 mb-3">{{ trans('langGroupTutor')}}</p></br>
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


                                                                    </br><a class='btn submitAdminBtnDefault d-inline-flex' href="{{ $urlAppend }}modules/group/booking.php?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;tutor_id={{ $tutor->user_id }}">
                                                                        {{ trans('langDoBooking')}}
                                                                    </a></br>

                                                                    
                                                                    <a class='btn submitAdminBtn d-inline-flex mt-3' href="{{ $urlAppend }}modules/group/date_available.php?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;booking_with_tutor={{ $tutor->user_id }}">
                                                                        {{ trans('langMYBookings')}}
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
                            </div>
                        @endif
                    @endif



                </div>
            </div>
        </div>
    </div>
</div>




@endsection
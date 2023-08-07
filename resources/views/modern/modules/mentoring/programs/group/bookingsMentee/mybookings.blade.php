
@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if($isCommonGroup == 1)
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @else
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/index.php">{{ trans('langGroupMentorsMentees') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4 ps-3 pe-3'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoGroupBookingsText')!!}</p>
                        </div>
                    </div>
                    
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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                   
                    <div class='col-6'>
                        {!! $action_bar !!}
                    </div>
                    <div class='col-6 text-end'>
                        @if($is_tutor_of_mentoring_program or $is_admin)
                            @if(count($booking_history) > 0)
                                <button class='btn viewOptionToolBtn small-text'
                                        data-bs-toggle='modal' data-bs-target='#BookingHistory'><span class='fa fa-trash'></span><span class='hidden-xs-mentoring'>&nbsp{!! trans('langBookingHistory') !!}</span>
                                </button>
                                <div class="modal fade" id="BookingHistory" tabindex="-1" aria-labelledby="BookingDeleteLabel" aria-hidden="true">
                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?space_group_id={!! getInDirectReference($group_id) !!}">
                                        <div class="modal-dialog modal-lg modal-danger">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="BookingDeleteLabel">{!! trans('langBookingHistory') !!}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <button type='submit' class="btn btn-outline-danger btn-sm small-text rounded-2 mb-3" name="delete_history_booking_id" value='delAllBooking'>
                                                        <span class='fa fa-trash'></span>&nbsp{{ trans('langDeleteAllBookings') }}
                                                    </button>
                                                    <table class='table table-default rounded-2' id='historyBookingTable'>
                                                        <thead>
                                                            <tr class='list-header'> 
                                                                <th class='text-center'>{{ trans('langName') }}</th>
                                                                <th class='text-center'>{{ trans('langFrom') }}</th>
                                                                <th class='text-center'>{{ trans('langUntil') }}</th>
                                                                <th class='text-center'>{{ trans('langDelete') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($booking_history as $h)
                                                                <tr>
                                                                    <td class='text-center'>{{ $h->title }}</td>
                                                                    <td class='text-center'>{!! format_locale_date(strtotime($h->start)) !!}</td>
                                                                    <td class='text-center'>{!! format_locale_date(strtotime($h->end)) !!}</td>
                                                                    <td class='text-center'>
                                                                        <input type='hidden' name='all_booking_ids[]' value='{{ $h->mentoring_booking_id }}'>
                                                                        <input type='hidden' name='booking_id' value='{{ $h->mentoring_booking_id }}'>
                                                                        <button type='submit' class="btn btn-danger btn-sm small-text rounded-2" name="delete_history_booking_id" value='delOneBooking'>
                                                                            <span class='fa fa-trash'></span>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @endif
                    </div>
                   
                   
                    @if(count($mybookings) > 0)
                        @foreach($mybookings as $b)
                            <div class='col-md-6 col-12 d-flex align-items-strech'>
                                <div class="panel panel-admin rounded-2 border-1 BorderSolid bg-white mb-lg-4 mb-4 py-md-4 px-md-4 py-3 px-3 shadow-none">
                                    @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                                        @php 
                                            $profile_img = profile_image($b->mentee_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile rounded-2 mb-1'); 
                                            $UserName = $b->title;
                                        @endphp
                                    @else
                                        @php 
                                            $profile_img = profile_image($b->mentor_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile rounded-2 mb-1'); 
                                            $getName = Database::get()->queryArray("SELECT givenname,surname FROM user WHERE id = ?d",$b->mentor_id);
                                            foreach($getName as $n){
                                                $UserName = $n->givenname.' '.$n->surname;
                                            }
                                        @endphp
                                    @endif
                                    <div class='panel-heading bg-body p-0'>
                                        <div class='col-12 Help-panel-heading'>
                                            <span class='panel-title text-uppercase Help-text-panel-heading'>{!! $profile_img !!}&nbsp<span class='textBold blackBlueText'>{{ $UserName }}</span></span>
                                        </div>
                                    </div>
                                    
                                    <div class="panel-body p-3 rounded-2">
                                        @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                                            <h6 class="card-title text-center small-text">
                                                @if($is_editor_current_group)
                                                    {{ trans('langTheUser')}}&nbsp<strong class='text-info'>{{ $b->title }}</strong>&nbsp{{ trans('langBookingHasBooking') }}
                                                @else
                                                    @php 
                                                        $details_mentors = Database::get()->queryArray("SELECT givenname,surname FROM user WHERE id = ?d",$b->mentor_id);
                                                        $profile_img_small = profile_image($b->mentor_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile m-auto d-block mt-2');
                                                    @endphp
                                                    
                                                    @foreach($details_mentors as $d)
                                                        {{ trans('langTheUser')}}&nbsp{{ $b->title }}&nbsp{{ trans('langHasBookingAdminTutor') }}&nbsp{!! $profile_img_small !!}&nbsp{{ $d->givenname }}&nbsp{{ $d->surname }}
                                                    @endforeach
                                                @endif
                                            </h6>
                                        @else
                                            @php $details_mentor = Database::get()->queryArray("SELECT givenname,surname FROM user WHERE id = ?d",$b->mentor_id); @endphp
                                            @foreach($details_mentor as $d)
                                                <h6 class="card-title text-center small-text">{{ trans('langHasDoingBookingWith') }}&nbsp<strong class='text-info'>{{ $d->givenname }}&nbsp{{ $d->surname }}</strong>&nbsp{{ trans('langForContanct') }}</h6>
                                            @endforeach
                                        @endif
                                        <p class='text-center'>
                                            <p class="card-text text-center"><span class='text-capitalize TextBold'>{{ trans('langFrom')}}</span>:&nbsp{!! format_locale_date(strtotime($b->start)) !!}</p>
                                            <p class="card-text text-center"><span class='text-capitalize TextBold'>{{ trans('langUntil') }}</span>:&nbsp{!! format_locale_date(strtotime($b->end)) !!}</p>
                                        </p>
                                    </div> 
                                    <div class='panel-footer text-center rounded-2'>
                                        @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                                            @if($b->accepted == 0)
                                                <button class='btn btn-outline-success btn-sm small-text me-3 mb-3'
                                                        data-bs-toggle='modal' data-bs-target='#BookingAccept{{ $b->id }}'><span class='fa fa-check'></span>&nbsp{!! trans('langAcceptBooking') !!}
                                                </button>
                                            @else
                                                <span class='fa fa-check text-success fs-6 TextBold me-3'>&nbsp{{ trans('langHasAcceptedBooking') }}</span> 
                                            @endif
                                            <button class='btn btn-outline-danger btn-sm small-text mb-3'
                                                    data-bs-toggle='modal' data-bs-target='#BookingDelete{{ $b->id }}'><span class='fa fa-trash'></span>&nbsp{!! trans('langCancelBooking') !!}
                                            </button>
                                        @else
                                            @if($b->accepted == 1)
                                                <span class='fa fa-check text-success fs-6 TextBold me-3'>&nbsp{{ trans('langHasAcceptedBooking') }}</span>
                                            @else
                                                <span class='fa-solid fa-info fs-6 TextRegular me-3'>&nbsp{!! trans('langHasNoAcceptedBookingYet') !!}</span>
                                            @endif
                                            <a class='btn btn-outline-danger btn-sm small-text mb-3' href='{{ $urlAppend }}modules/mentoring/programs/group/bookingsMentee/booking_space.php?group_id={!! getInDirectReference($b->group_id) !!}&mentor_id={!! getInDirectReference($b->mentor_id) !!}'>
                                                <span class='fa fa-calendar'></span>&nbsp{{ trans('langCancelBooking') }}
                                            </a>
                                            
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="BookingAccept{{ $b->id }}" tabindex="-1" aria-labelledby="BookingAcceptLabel{{ $b->id }}" aria-hidden="true">
                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?space_group_id={!! getInDirectReference($b->group_id) !!}">
                                    <div class="modal-dialog modal-md modal-success">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="BookingAcceptLabel{{ $b->id }}">{!! trans('langAcceptBooking') !!}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                {{ trans('langContinueToBooking') }}
                                                <input type='hidden' name='accept_booking_id' value="{{ $b->mentoring_booking_id }}">

                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                <button type='submit' class="btn btn-success small-text rounded-2" name="accept_booking">
                                                    {{ trans('langAcceptBooking') }}
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="modal fade" id="BookingDelete{{ $b->id }}" tabindex="-1" aria-labelledby="BookingDeleteLabel{{ $b->id }}" aria-hidden="true">
                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?space_group_id={!! getInDirectReference($b->group_id) !!}">
                                    <div class="modal-dialog modal-md modal-danger">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="BookingDeleteLabel{{ $b->id }}">{!! trans('langCancelBooking') !!}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                {{ trans('langContinueToBooking') }}
                                                <input type='hidden' name='booking_id' value="{{ $b->mentoring_booking_id }}">

                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                <button type='submit' class="btn btn-danger small-text rounded-2" name="delete_booking">
                                                    {{ trans('langDelete') }}
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        @endforeach
                    @else
                       <div class='col-12'>
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'>
                                <div class='alert alert-warning rounded-2'>{{ trans('langNoExistBookings') }}</div>
                            </div>
                       </div>
                    @endif

                    <!--------------------------------------------------------------------------------------------------------------------------->

                    

                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
     
       $('#historyBookingTable').DataTable();

    } );
</script>
@endsection

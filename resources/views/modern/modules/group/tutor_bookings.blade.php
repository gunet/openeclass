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
                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    
                    @include('layouts.partials.show_alert') 

                    @if($is_course_admin or $is_tutor)
                        @if(count($bookings) > 0)
                            <div class='col-12'>
                                <div class='table-responsive'>
                                    <table class='table-default'>
                                        <thead>
                                            <tr>
                                                <th style='width:40%;'>{{ trans('langUser') }}</th>
                                                <th style='width:20%;'>{{ trans('langFrom') }}</th>
                                                <th style='width:20%;'>{{ trans('langUntil') }}</th>
                                                <th style='width:15%;'>{{ trans('langAccept') }}</th>
                                                <th style='width:5%;'></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bookings as $b)
                                                <tr>
                                                    <td>{{ $b->title }}</td>
                                                    <td>{{ format_locale_date(strtotime($b->start), 'short') }}</td>
                                                    <td>{{ format_locale_date(strtotime($b->end), 'short') }}</td>
                                                    <td>
                                                        @if($b->accepted == 1)
                                                            <span class='badge Success-200-bg'>{{ trans('langYes')}}</span>
                                                        @else
                                                            <span class='badge Accent-200-bg'>{{ trans('langNo')}}</span>
                                                        @endif
                                                    </td>
                                                    <td class='text-end'>
                                                        {!! action_button(array(
                                                            array('title' => trans('langAcceptBooking'),
                                                                    'url' => "#",
                                                                    'icon-extra' => "data-bs-toggle='modal' data-bs-target='#BookingAccept{$b->id}'",
                                                                    'icon' => 'fa-edit',
                                                                    'show' => ($b->accepted == 0)),
                                                            array('title' => trans('langCancel'),
                                                                    'url' => "#",
                                                                    'icon-extra' => "data-bs-toggle='modal' data-bs-target='#BookingDelete{$b->id}'",
                                                                    'icon' => 'fa-xmark')
                                                            )
                                                        ) !!}
                                                    </td>
                                                </tr>

                                                <div class="modal fade" id="BookingAccept{{ $b->id }}" tabindex="-1" aria-labelledby="BookingAcceptLabel{{ $b->id }}" aria-hidden="true">
                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $b->group_id }}&amp;bookings_of_tutor={{ $b->tutor_id }}">
                                                        <div class="modal-dialog modal-md modal-success">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <div class='modal-title'>
                                                                        <div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div>
                                                                        <div class='modal-title-default text-center mb-0 mt-2' id="BookingAcceptLabel{{ $b->id }}">{!! trans('langAcceptBooking') !!}</div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    {{ trans('langContinueToBooking') }}
                                                                    <input type='hidden' name='accept_booking_id' value="{{ $b->booking_id }}">
                                                                </div>
                                                                <div class="modal-footer d-flex justify-content-center align-items-center">
                                                                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn submitAdminBtnDefault" name="accept_booking">
                                                                        {{ trans('langAcceptBooking') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                    
                                                <div class='modal fade' id='BookingDelete{{ $b->id }}' tabindex='-1' aria-labelledby='BookingDeleteLabel{{ $b->id }}' aria-hidden='true'>
                                                    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $b->group_id }}&amp;bookings_of_tutor={{ $b->tutor_id }}">
                                                        <div class='modal-dialog modal-md'>
                                                            <div class='modal-content'>
                                                                <div class='modal-header'>
                                                                    <div class='modal-title' id='BookingDeleteLabel{{ $b->id }}'>
                                                                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                                                                        <div class="modal-title-default text-center mb-0 mt-2" id="BookingDeleteLabel{{ $b->id }}">{!! trans('langCancelBooking') !!}</div>
                                                                    </div>
                                                                </div>
                                                                <div class='modal-body text-center'>
                                                                    {{ trans('langContinueToBooking') }}
                                                                    <input type='hidden' name='booking_id' value="{{ $b->booking_id }}">
                                                                </div>
                                                                <div class='modal-footer d-flex justify-content-center align-items-center'>
                                                                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn deleteAdminBtn" name="delete_booking">
                                                                        {{ trans('langDelete') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class='col-12'>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoInfoAvailable') }}</span>
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
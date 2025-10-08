
@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#all_bookings,#all_history_bookings').DataTable({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'aoColumns': [
                    {'bSortable' : false, 'sWidth': '5%' },
                    {'bSortable' : false },
                    {'bSortable' : false },
                    {'bSortable' : false },
                    {'bSortable' : false },
                ],
                'order' : [],
                'oLanguage': {
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'zeroRecords': '{{ trans('langNoResult') }}',
                    'sInfo': '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                    'sInfoEmpty': '{{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}',
                    'sInfoFiltered': '',
                    'sInfoPostFix': '',
                    'sSearch': '',
                    'sUrl': '',
                    'oPaginate': {
                        'sFirst': '&laquo;',
                        'sPrevious': '&lsaquo;',
                        'sNext': '&rsaquo;',
                        'sLast': '&raquo;'
                    }
                }
            });
            $('.dt-search input').attr({
                'class': 'form-control input-sm ms-0 mb-3',
                'placeholder': '{{ trans('langSearch') }}...'
            });
            $('.dt-search label').attr('aria-label', '{{ trans('langSearch') }}');
        });

    </script>
@endpush

@push('head_scripts')
    <script>
        $(function() {
            $(document).on('click', '.edit-book', function(e){
                var bookID = $(this).attr('data-id');
                document.getElementById("acceptBook").value = bookID;
            });
            $(document).on('click', '.delete-book', function(e){
                var bookID = $(this).attr('data-id');
                document.getElementById("deleteBook").value = bookID;
            });
            $(document).on('click', '.delete-historybook', function(e){
                var bookID = $(this).attr('data-id');
                document.getElementById("deletehistoryBook").value = bookID;
            });
        });
    </script>
@endpush

@section('content')

<div class="col-12 main-section" >

    <div class="{{ $container }} main-container">

        <div class="row m-auto">


            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar) and $action_bar)
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert')

            @if(count($bookings) > 0)
                <div class='col-12 mb-5'>
                    <table class='table-default' id='all_bookings'>
                        <thead>
                            <tr>
                                @if($is_user_teacher)
                                    <th style='width:40%;'>{{ trans('langFrom') }} {{ trans('langUser') }}</th>
                                    <th style='width:20%;'>{{ trans('langFrom') }}</th>
                                    <th style='width:20%;'>{{ trans('langUntil') }}</th>
                                    <th style='width:15%;'>{{ trans('langAccept') }}</th>
                                    <th style='width:5%;' aria-label="{{ trans('langSettingSelect') }}"></th>
                                @else
                                    <th style='width:30%;'>{{ trans('langUser') }}</th>
                                    <th style='width:30%;'>{{ trans('langWithTutor') }}</th>
                                    <th style='width:15%;'>{{ trans('langFrom') }}</th>
                                    <th style='width:15%;'>{{ trans('langUntil') }}</th>
                                    <th style='width:10%;'>{{ trans('langAccept') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $b)
                                <tr>
                                    <td @if($is_user_teacher) style='width:40%;' @else style='width:30%;' @endif>{{ $b->title }}</td>
                                    @if(!$is_user_teacher)
                                        <td style='width:30%;'>{!! display_user($b->teacher_id) !!}</td>
                                    @endif
                                    <td @if($is_user_teacher) style='width:20%;' @else style='width:15%;' @endif>{{ format_locale_date(strtotime($b->start), 'short') }}</td>
                                    <td @if($is_user_teacher) style='width:20%;' @else style='width:15%;' @endif>{{ format_locale_date(strtotime($b->end), 'short') }}</td>
                                    <td @if($is_user_teacher) style='width:20%;' @else style='width:15%;' @endif>
                                        @if($b->accepted == 1)
                                            <span class='badge Success-200-bg'>{{ trans('langYes')}}</span>
                                        @else
                                            <span class='badge Accent-200-bg'>{{ trans('langNo')}}</span>
                                        @endif
                                    </td>
                                    @if($is_user_teacher)
                                        <td class='text-end' style='width:5%;'>
                                            {!! action_button(array(
                                                array('title' => trans('langAcceptBooking'),
                                                        'url' => "#",
                                                        'icon-class' => "edit-book",
                                                        'icon-extra' => "data-id='{$b->booking_id}' data-bs-toggle='modal' data-bs-target='#BookingAccept'",
                                                        'icon' => 'fa-edit',
                                                        'show' => ($b->accepted == 0)),
                                                array('title' => trans('langCancel'),
                                                        'url' => "#",
                                                        'icon-class' => "delete-book",
                                                        'icon-extra' => "data-id='{$b->booking_id}' data-bs-toggle='modal' data-bs-target='#BookingDelete'",
                                                        'icon' => 'fa-xmark')
                                                )
                                            ) !!}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class='col-12 mb-5'>
                    <div class='alert alert-warning'>
                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoInfoAvailable') }}</span>
                    </div>
                </div>
            @endif



            @if($is_user_teacher)
                <div class='col-12'>
                    <h2 class='pb-3'>{{ trans('langHistoyBooking') }}</h2>
                    @if(count($booking_history) > 0)
                        <table class='table-default' id='all_history_bookings'>
                            <thead>
                                <tr>
                                    <th style='width:40%;'>{{ trans('langFrom') }} {{ trans('langUser') }}</th>
                                    <th style='width:20%;'>{{ trans('langFrom') }}</th>
                                    <th style='width:20%;'>{{ trans('langUntil') }}</th>
                                    <th style='width:15%;'>{{ trans('langAccept') }}</th>
                                    <th style='width:5%;' aria-label="{{ trans('langSettingSelect') }}"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($booking_history as $b)
                                    <tr>
                                        <td style='width:40%;'>{{ $b->title }}</td>
                                        <td style='width:20%;'>{{ format_locale_date(strtotime($b->start), 'short') }}</td>
                                        <td style='width:20%;'>{{ format_locale_date(strtotime($b->end), 'short') }}</td>
                                        <td style='width:15%;'>
                                            @if($b->accepted == 1)
                                                <span class='badge Success-200-bg'>{{ trans('langYes')}}</span>
                                            @else
                                                <span class='badge Accent-200-bg'>{{ trans('langNo')}}</span>
                                            @endif
                                        </td>
                                        <td class='text-end' style='width:5%;'>
                                            {!! action_button(array(
                                                array('title' => trans('langCancel'),
                                                        'url' => "#",
                                                        'icon-class' => "delete-historybook",
                                                        'icon-extra' => "data-id='{$b->booking_id}' data-bs-toggle='modal' data-bs-target='#BookingHistoryDelete'",
                                                        'icon' => 'fa-xmark')
                                                )
                                            ) !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoInfoAvailable') }}</span>
                        </div>
                    @endif
                </div>
            @endif


        </div>

    </div>
</div>

<div class="modal fade" id="BookingAccept" tabindex="-1" aria-labelledby="BookingAcceptLabel" aria-hidden="true">
    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?user_id={{ $uid }}">
        <div class="modal-dialog modal-md modal-success">
            <div class="modal-content">
                <div class="modal-header">
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div>
                        <div class='modal-title-default text-center mb-0 mt-2' id="BookingAcceptLabel">{!! trans('langAcceptBooking') !!}</div>
                    </div>
                </div>
                <div class="modal-body text-center">
                    {{ trans('langContinueToBooking') }}
                    <input id="acceptBook" type='hidden' name='accept_booking_id'>
                </div>
                <div class="modal-footer d-flex justify-content-center align-items-center">
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn submitAdminBtnDefault" name="accept_book">
                        {{ trans('langAcceptBooking') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class='modal fade' id='BookingDelete' tabindex='-1' aria-labelledby='BookingDeleteLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?user_id={{ $uid }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <div class="modal-title-default text-center mb-0 mt-2" id="BookingDeleteLabel">{!! trans('langCancelBooking') !!}</div>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    {{ trans('langContinueToBooking') }}
                    <input id="deleteBook" type='hidden' name='booking_id'>
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn deleteAdminBtn" name="delete_book">
                        {{ trans('langDelete') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class='modal fade' id='BookingHistoryDelete' tabindex='-1' aria-labelledby='BookingHistoryDeleteLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?user_id={{ $uid }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <div class="modal-title-default text-center mb-0 mt-2" id="BookingHistoryDeleteLabel">{!! trans('langDelete') !!}</div>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    {{ trans('langContinueToBooking') }}
                    <input id="deletehistoryBook" type='hidden' name='booking_history_id'>
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn deleteAdminBtn" name="delete_history_book">
                        {{ trans('langDelete') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

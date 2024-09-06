
@extends('layouts.default')


@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#all_users_booking').DataTable({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'aoColumns': [
                    {'bSortable' : false, 'sWidth': '5%' },
                    {'bSortable' : false },
                    {'bSortable' : false },
                    {'bSortable' : false },
                ],
                'order' : [],
                'oLanguage': {
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sZeroRecords': '{{ trans('langNoResult') }}',
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
            $('.dataTables_filter input').attr({
                'class': 'form-control input-sm ms-0 mb-3',
                'placeholder': '{{ trans('langSearch') }}...'
            });
            $('.dataTables_filter label').attr('aria-label', '{{ trans('langSearch') }}');
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

            @if($showUsers)
                @if(count($user_teachers) > 0)
                    <div class='col-12'>

                            <table id="all_users_booking" class='table-default'>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('langUser') }}</th>
                                        <th>{{ trans('langEmail') }}</th>
                                        <th aria-label="{{ trans('langSettingSelect') }}"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach($user_teachers as $u)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ $u->givenname }}&nbsp;{{ $u->surname }}</td>
                                            <td>
                                                @if(!empty($u->email))
                                                    {{ $u->email }}
                                                @else
                                                    {{ trans('langNoInfoAvailable') }}
                                                @endif
                                            </td>
                                            <td class='text-end'>
                                                <a class='btn submitAdminBtn d-inline-flex text-decoration-none'
                                                    href="{{ $_SERVER['SCRIPT_NAME'] }}?uBook={{ $u->id }}&amp;bookWith=1&amp;do_booking=1">
                                                        {{ trans('langDoBooking')}}
                                                </a>
                                            </td>
                                        </tr>
                                        @php $i++; @endphp
                                    @endforeach
                                </tbody>
                            </table>

                    </div>
                @else
                    <div class='col-12'>
                        <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoInfoAvailable') }}</span>
                        </div>
                    </div>
                @endif
            @else

                <div id="loaderBooking" class="modal fade in" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body bg-transparent d-flex justify-content-center align-items-center">
                                <img src='{{ $urlAppend }}resources/img/ajax-loader.gif' alt='Loading'>
                                <span>{{ trans('langPlsWait') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='col-12'>
                    <a class='btn submitAdminBtn d-inline-flex' href='#' data-bs-toggle='modal' data-bs-target='#infoEvents'>{{ trans('langInfoColourEvent') }}</a>
                    <div class='modal fade' id='infoEvents' tabindex='-1' role='dialog' aria-labelledby='infoEventsLabel' aria-hidden='true'>
                        <div class='modal-dialog'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <div class='modal-title' id='infoEventsLabel'>{{ trans('langInfoColourEvent') }}</div>
                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                    </button>
                                </div>
                                <div class='modal-body'>
                                    <div class='col-12'>
                                        <ul>
                                            <li class='mb-2'><p>{!! trans('langBlueInfoBookingTutor') !!}</p></li>
                                            <li class='mb-2'><p>{!! trans('langSuccessInfoBookingTutor') !!}</p></li>
                                            <li class='mb-2'><p>{!! trans('langPinkInfoBookingTutor') !!}</p></li>
                                            <li><p>{!! trans('langWarningInfoBookingTutor') !!}</p></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3 bookings-content">
                    <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                        <div class='card-body'>
                            <div id='calendarBooking' class='bookingCalendarByUser'></div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="titleSimpleUser" value="{{ $booking_by_username }} {{ $booking_by_surname }}">
                <input type="hidden" id="startTime">
                <input type="hidden" id="endTime">
                <input type="hidden" id="tutor_Id" value="{{ $tutor_id }}">

            @endif




        </div>

    </div>
</div>



@if(!$showUsers)
    <script type='text/javascript'>
        $(document).ready(function () {

            var calendar = $('#calendarBooking').fullCalendar({
                header:{
                    left: 'prev,next ',
                    center: 'title',
                    right: ''
                },
                defaultView: 'agendaWeek',
                slotDuration: '00:30' ,
                editable: false,
                minTime: '08:00:00',
                maxTime: '23:00:00',
                contentHeight:"auto",
                selectable: true,
                allDaySlot: false,
                displayEventTime: true,
                events: "{{ $urlAppend }}main/profile/book_create_delete.php?view=1&show_tutor={{ $tutor_id }}",
                eventRender: function( event, element, view ) {
                    var title = element.find( '.fc-title' );
                    title.html( title.text() );


                    var timee = element.find( '.fc-time span' );

                    element.popover({
                        title: timee[0].innerText+event.title,
                        trigger: 'hover',
                        placement: 'top',
                        container: 'body',
                        html: true,
                        sanitize: false
                    });

                },
                eventClick:  function(event) {
                    start = moment(event.start).format('YYYY-MM-DD HH:mm');
                    end = moment(event.end).format('YYYY-MM-DD HH:mm');

                    if(event.className == 'bookingAdd'){

                        if(confirm("{{ js_escape(trans('langdobookingwithtutor')) }}")){
                            var startTime = start;
                            var endTime = end;
                            var title = $('#titleSimpleUser').val();
                            var user_id = $('#tutor_Id').val();


                            $('#loaderBooking').modal('toggle');

                            $.ajax({
                                url: '{{ $urlAppend }}main/profile/book_create_delete.php',
                                data: 'action=add&title='+title+'&start='+startTime+'&end='+endTime+'&tutor_Id='+user_id,
                                type: "POST",
                                success: function(json) {
                                    $('#loaderBooking').modal('hide');
                                    if(json == 1){
                                        alert("{{ js_escape(trans('langAddBookingSuccess')) }}");
                                        window.location.reload();
                                    }else if(json == 0){
                                        alert("{{ js_escape(trans('langAddBookingNoSuccess')) }}");
                                        window.location.reload();
                                    }else if(json == 2){
                                        alert("{{ js_escape(trans('langTutorHasRemovedTheDate')) }}");
                                        window.location.reload();
                                    }else if(json == 3){
                                        alert("{{ js_escape(trans('ThereIsABookingWithTheSameSlot')) }}");
                                        window.location.reload();
                                    }

                                },
                                error:function(error){
                                    console.log(error)
                                },
                            });

                        }

                    }

                    if(event.className == 'bookingDelete'){

                        var id = event.id;
                        if(confirm("{{ js_escape(trans('langdelbookingwithtutor')) }}")){
                            $('#loaderBooking').modal('toggle');
                            $.ajax({
                                url: '{{ $urlAppend }}main/profile/book_create_delete.php',
                                data: 'action=delete&id='+id,
                                type: "POST",
                                success: function(json) {
                                    $('#loaderBooking').modal('hide');
                                    if(json == 1){
                                        alert("{{ js_escape(trans('langDeleteBookingSuccess')) }}");
                                        window.location.reload();
                                    }
                                },
                                error:function(error){
                                    console.log(error)
                                },
                            });

                        }
                    }
                }
            });

        });

    </script>
@endif


@endsection

@extends('layouts.default')
@push('head_scripts')
<script>
    $(function() {
        $(document).on('click', '.booking_info', function(e){
            var bookID = $(this).attr('data-id');
            document.getElementById("book_for_del").value = bookID;
        });
    });
</script>
@endpush
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

                    @include('layouts.partials.show_alert') 

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if(count($booking_history) > 0)
                        <div class='col-12'>
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <thead>
                                        <tr>
                                            <th>{{ trans('langTutor') }}</th>
                                            <th>{{ trans('langUser') }}</th>
                                            <th>{{ trans('langStart') }}</th>
                                            <th>{{ trans('langEnd') }}</th>
                                            <th>{{ trans('langAccept')}}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    @foreach($booking_history as $h)
                                        <tr>
                                            <td>{{ $h->givenname }}&nbsp;{{ $h->surname }}</td>
                                            <td>{{ $h->title }}</td>
                                            <td>{{ format_locale_date(strtotime($h->start), 'short') }}</td>
                                            <td>{{ format_locale_date(strtotime($h->end), 'short') }}</td>
                                            <td>
                                                @if($h->accepted == 0)
                                                    {{ trans('langNo') }}
                                                @else
                                                    {{ trans('langYes') }}
                                                @endif
                                            </td>
                                            <td class='text-end'>
                                                {!! action_button(array(
                                                    array('title' => trans('langDelete'),
                                                            'url' => "#",
                                                            'icon-class' => "booking_info",
                                                            'icon-extra' => "data-id='{$h->id}' data-bs-toggle='modal' data-bs-target='#BookingHistoryDelete'  data-bs-remote='false'",
                                                            'icon' => 'fa-xmark')
                                                    )
                                                ) !!}
                                            </td>
                                        </tr>
                                    @endforeach
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



                </div>
            </div>
        </div>
    </div>
</div>


<div class='modal fade' id='BookingHistoryDelete' tabindex='-1' aria-labelledby='BookingHistoryDelete' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;group_id={{ $group_id }}&amp;history_booking={{ $tutor }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title' id='BookingHistoryDelete'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <h3 class="modal-title-default text-center mb-0 mt-2">{!! trans('langDelete') !!}</h3>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    {{ trans('langContinueToBooking') }}
                    <input id="book_for_del" type='hidden' name='del_booking_id'>
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button class="btn deleteAdminBtn" name="delete_history_booking_id">
                        {{ trans('langDelete') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>


@endsection
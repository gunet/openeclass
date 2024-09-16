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

                    @if($is_member && !$is_tutor)
                        @if(count($bookings_user) > 0)
                            <div class='col-12'>
                                    <a class='btn submitAdminBtn d-inline-flex' href="{{ $urlAppend }}modules/group/booking.php?course={{ $course_code}}&amp;group_id={{ $group_id}}&amp;tutor_id={{ $tutor_id }}">
                                        {{ trans('langBookingAgenda') }}:&nbsp;({{ $TutorGivenname }}&nbsp;{{ $TutorSurname }})
                                    </a>
                                    <div class='table-responsive'>
                                        <table class='table-default'>
                                            <thead>
                                                <th>{{ trans('langTutor') }}</th>
                                                <th>{{ trans('langStart') }}</th>
                                                <th>{{ trans('langEnd') }}</th>
                                                <th>{{ trans('langAccept') }}</th>
                                            </thead>
                                            <tbody>
                                                @foreach($bookings_user as $b)
                                                    <tr>
                                                        <td>{{ $TutorGivenname }}&nbsp;{{ $TutorSurname }}</td>
                                                        <td>{{ format_locale_date(strtotime($b->start), 'short') }}</td>
                                                        <td>{{ format_locale_date(strtotime($b->end), 'short') }}</td>
                                                        <td>
                                                            @if($b->accepted == 0)
                                                                {{ trans('langNo')}}
                                                            @else
                                                                {{ trans('langYes')}}
                                                            @endif
                                                        </td>
                                                    </tr>

                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
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
@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#all_sessions_scheduled').DataTable({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'aoColumns': [
                    {'bSortable' : false, 'sWidth': '30%' },
                    {'bSortable' : false },
                    {'bSortable' : false },
                    {'bSortable' : false },
                    {'bSortable' : false },
                    {'bSortable' : false },
                ],
                'order' : [],
                'oLanguage': {
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sZeroRecords': '{{ trans('langNoResult') }}',
                    'sInfo': '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                    'sInfoEmpty': '',
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
            $('.dt-container.dt-bootstrap5 .dt-search input').attr({
                class: 'form-control input-sm ms-0 mb-3',
                placeholder: '{{ trans('langSearch') }}...'
            });
            $('.dt-container.dt-bootstrap5 .dt-search label').attr('aria-label', '{{ trans('langSearch') }}');
        });
    </script>
@endpush

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

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert')

                    <div class='col-12'>
                        @if(count($sessions) > 0)
                            <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                    <h3 class='mb-0'>{{ trans('langSessionsTable')}}</h3>
                                </div>
                                <div class='card-body'>
                                    <table class='table-default' id='all_sessions_scheduled'>
                                        <thead>
                                            <tr>
                                                <th>{{ trans('langSession')}}</th>
                                                <th>{{ trans('langConsultant')}}</th>
                                                <th>{{ trans('langDate') }}</th>
                                                <th>{{ trans('langStart') }}</th>
                                                <th>{{ trans('langFinish') }}</th>
                                                <th>{{ trans('langStatement') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sessions as $s)
                                                <tr>
                                                    <td>
                                                        <a class='link-color @if($is_simple_user) {{ $s->display }} @endif' href='{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&session={{ $s->id}}'>
                                                            {{ $s->title }}
                                                        <a>
                                                    </td>
                                                    <td>{!! participant_name($s->creator) !!}</td>
                                                    <td>{!! format_locale_date(strtotime($s->start), 'short', false) !!}</td>
                                                    <td>{!! date("H:i", strtotime($s->start)) !!}</td>
                                                    <td>{!! date("H:i", strtotime($s->finish)) !!}</td>
                                                    @if($is_simple_user)
                                                        <td>
                                                            <ul>
                                                                @if($s->start > $current_time)
                                                                    <li class='py-1'>{{ trans('langSessionNotStarted') }}</li>
                                                                @endif
                                                                @if($s->has_prereq)
                                                                    <li class='py-1'>{{ trans('langExistsInCompletedPrerequisite') }}</li>
                                                                @endif
                                                                @if($s->finish < $current_time)
                                                                    <li class='py-1'><span class='badge Accent-200-bg'>{{ trans('langSessionHasExpired') }}</span></li>
                                                                @endif
                                                            </ul>
                                                        </td>
                                                    @else
                                                        <td>
                                                            @if($s->finish < $current_time)
                                                                <span class='badge Accent-200-bg'>{{ trans('langSessionHasExpired') }}</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langNoSessionsExist') }}</span>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

@endsection

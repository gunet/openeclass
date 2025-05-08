@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        var lang = {
            assignmentPasswordModalTitle: '{{ trans('langAssignmentPasswordModalTitle') }}',
            theFieldIsRequired: '{{ trans('langTheFieldIsRequired') }}',
            cancel: '{{ trans('langCancel') }}',
            submit: '{{ trans('langSubmit') }}',
        };
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

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        @if (count($result) > 0)
                            <div class='col-sm-12'>
                                <div class='table-responsive'>
                                    <table id='assignment_table_{{ $course_code }}' class='table-default'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th style='width:45%;'>{{ trans('langTitle') }}</th>
                                                <th style='width:25%;'>{{ trans('langGroupWorkDeadline_of_Submission') }}</th>
                                                <th class='text-center'>{{ trans('langSubmitted') }}</th>
                                                <th class='text-center'>{{ trans('langGradebookGrade') }}</th>
                                                @if (get_config('eportfolio_enable'))
                                                    <th style='width:10%;' class='text-center' aria-label='{{ trans('langSettingSelect') }}'><i class="fa-solid fa-gear"></th>
                                                @else
                                                    <th aria-label='{{ trans('langSettingSelect') }}'></th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($result as $key => $row)
                                            @if (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date)) {{-- assignment not starting yet --}}
                                                <tr class='not_visible'>
                                            @else
                                                <tr>
                                            @endif
                                                <td>
                                                    @if (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date)) {{-- assignment not starting yet --}}
                                                        {{ $row->title }}
                                                    @else
                                                        <a href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $row->id }}' @if ($row->password_lock) class='password_protected' @endif>{{ $row->title }}</a>

                                                        @if ($row->assignment_type == ASSIGNMENT_TYPE_TURNITIN)
                                                            &nbsp;&nbsp;<span class='badge' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='{{ trans('langAssignmentTypeTurnitinInfo') }}'>
                                                                <small>
                                                                    {{ trans('langAssignmentTypeTurnitin') }}
                                                                </small>
                                                            </span>
                                                        @endif

                                                        @if (!isset($_REQUEST['unit']))
                                                            @if ($row->password_lock or $row->ip_lock)
                                                                &nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                                                                                  data-bs-original-title='<ul>@if ($row->password_lock) <li>{{ trans('langPassCode') }}</li> @endif @if ($row->ip_lock) <li>{{ trans('langIPUnlock') }} @endif</li></ul>'>
                                                                </span>
                                                            @endif
                                                        @endif

                                                        <div>
                                                            <small class='text-muted'>
                                                                @if ($row->group_submissions)
                                                                    {{ trans('langGroupAssignment') }}
                                                                @else
                                                                    {{ trans('langUserAssignment') }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td data-sort='{{ $loop->iteration }}'>
                                                    @if (isset($row->deadline))
                                                        {!! format_locale_date(strtotime($row->deadline)) !!}
                                                    @else
                                                        {{ trans('langNoDeadline') }}
                                                    @endif
                                                    @if (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date)) {{-- assignment not starting yet --}}
                                                        <div class='Warning-200-cl'>
                                                            <small>
                                                                {{ trans('langWillStartAt') }}: {!! format_locale_date(strtotime($row->submission_date)) !!}
                                                            </small>
                                                        </div>
                                                    @elseif ($row->time > 0)
                                                        <div>
                                                            <small class='label label-warning'>{{ trans('langDaysLeft') }} {!! format_time_duration($row->time) !!}</small>
                                                        </div>
                                                    @elseif($row->deadline)
                                                        <div>
                                                            <small class='label label-danger'>
                                                                {{ trans('langHasExpiredS') }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class='text-center'>
                                                    @if ($submission = find_submissions(is_group_assignment($row->id), $uid, $row->id, $gids))
                                                        <i class='fa-solid fa-check'></i><br>
                                                        @foreach ($submission as $sub)
                                                            @if (isset($sub->group_id)) {{-- if is a group assignment --}}
                                                                <div>
                                                                    <small>
                                                                        {{ trans('langGroupSubmit') }} {{ trans('langOfGroup') }} <em> {{ gid_to_name($sub->group_id) }}</em>
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <i class='fa-regular fa-hourglass-half'></i><br>
                                                    @endif
                                                </td>
                                                <td class='text-center'>
                                                    @if ($submission = find_submissions(is_group_assignment($row->id), $uid, $row->id, $gids))
                                                        @foreach ($submission as $sub)
                                                            <div>
                                                                @if (submission_grade($sub->id))
                                                                    {{ submission_grade($sub->id) }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </td>

                                                @if (get_config('eportfolio_enable'))
                                                    <td class='text-center' style='width:10%;'>
                                                        {!! action_button(array(
                                                            array(
                                                                'title' => trans('langAddResePortfolio'),
                                                                'url' => $urlAppend . "main/eportfolio/resources.php?token=" .token_generate('eportfolio' . $uid) ."&amp;action=add&amp;type=work_submission&amp;rid=$row->id",
                                                                'icon' => 'fa-star'
                                                                )
                                                            ));
                                                        !!}
                                                     </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class='col-sm-12'>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoAssign') }}</span>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type='text/javascript'>
        $(document).ready(function() {
            $('#assignment_table_{{ $course_code }}').DataTable ({
                'stateSave': true,
                'columns': [ {{ $columns }} ],
                'fnDrawCallback': function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
                'aLengthMenu': [
                    [10, 20, 30 , -1],
                    [10, 20, 30, '{{ trans('langAllOfThem') }}']
                ],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [ [1, 'asc'] ],
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

            $(document).on('click', '.password_protected', unit_password_bootbox);
        });
    </script>

@endsection


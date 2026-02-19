@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @if (count($result) > 0)
                            <div class='col-sm-12'>
                                
                                    <table id='assignment_table_{{ $course_code }}' class='table-default'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th style='width:45%;'>{{ trans('langTitle') }}</th>
                                                <th class='text-center'>{{ trans('langSubmShort') }}</th>
                                                <th class='text-center'>{{ trans('langNogrShort') }}</th>
                                                <th style='width:20%;'>{{ trans('langGroupWorkDeadline_of_Submission') }}</th>
                                                <th class='text-center' aria-label='{{ trans('langSettingSelect') }}'><i class="fa-solid fa-gear"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($result as $key => $row)

                                                @if (!$row->active or (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date))) {{-- assignment not starting yet or inactive --}}
                                                    <tr class='not_visible'>
                                                @else
                                                    <tr>
                                                @endif

                                                    <td style='width:40%;'>
                                                        <a href='{{ $urlAppend }}modules/work/index.php?course={{ $course_code }}&id={{ $row->id }}'>{{ $row->title }}</a>
                                                        @if ($row->password_lock or $row->ip_lock)
                                                            &nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                                                                                    data-bs-original-title='<ul>@if ($row->password_lock) <li>{{ trans('langPassCode') }}</li> @endif @if ($row->ip_lock) <li>{{ trans('langIPUnlock') }} @endif</li></ul>'>
                                                            </span>
                                                        @endif

                                                        @if ($row->assignment_type == ASSIGNMENT_TYPE_TURNITIN)
                                                            &nbsp;&nbsp;<span class='badge' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='{{ trans('langAssignmentTypeTurnitinInfo') }}'>
                                                                <small>
                                                                    {{ trans('langAssignmentTypeTurnitin') }}
                                                                </small>
                                                            </span>
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

                                                        @if ($row->assign_to_specific == 1)
                                                            <a class='assigned_to' data-ass_id='{{ $row->id }}'><small class='help-block link-color'>{{ trans('langWorkAssignTo') }}: {{ trans('langWorkToUser') }}</small></a>
                                                        @elseif ($row->assign_to_specific == 2)
                                                            <a class='assigned_to' data-ass_id='{{ $row->id }}'><small class='help-block link-color'>{{ trans('langWorkAssignTo') }}: {{ trans('langWorkToGroup') }}</small></a>
                                                        @endif

                                                        @if ($row->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $row->start_date_review && $row->due_date_review)
                                                            <p class="TextBold mt-2 mb-0 text-decoration-underline small-text">{{ trans('langGradeReviews')}}</p>
                                                            <p class="TextBold mb-0 small-text">{{ trans('langStartDate') }}:&nbsp;<span>{{ format_locale_date(strtotime($row->start_date_review), 'short') }}</span></p>
                                                            <p class="TextBold mb-0 small-text">{{ trans('langEndDate') }}:&nbsp;<span>{{ format_locale_date(strtotime($row->due_date_review), 'short') }}</span></p>
                                                            @if ( strtotime(date("d-m-Y H:i:s")) < strtotime($row->start_date_review) )
                                                                <p class="text-warning TextBold small-text mt-2" style="line-height:14px;">{{ trans('langGradeReviewHasNotStarted') }}</p>
                                                            @elseif ( strtotime(date("d-m-Y H:i:s")) > strtotime($row->start_date_review) && strtotime(date("d-m-Y H:i:s")) < strtotime($row->due_date_review))
                                                                <p class="text-success TextBold small-text mt-2" style="line-height:14px;">{{ trans('langGradeReviewInProgress') }}</p>
                                                                <div class="mt-2">
                                                                    <div class='spinner-grow text-success spinner-grow-sm' role='status'>
                                                                        <span class='visually-hidden'></span>
                                                                    </div>
                                                                    <div class='spinner-grow text-danger spinner-grow-sm' role='status'>
                                                                        <span class='visually-hidden'></span>
                                                                    </div>
                                                                    <div class='spinner-grow text-warning spinner-grow-sm' role='status'>
                                                                        <span class='visually-hidden'></span>
                                                                    </div>
                                                                    <div class='spinner-grow text-info spinner-grow-sm' role='status'>
                                                                        <span class='visually-hidden'></span>
                                                                    </div>
                                                                </div>
                                                            @elseif (strtotime(date("d-m-Y H:i:s")) > strtotime($row->due_date_review))
                                                                <p class="text-danger TextBold small-text mt-2" style="line-height:14px;">{{ trans('langGradeReviewHasExpired') }}</p>
                                                            @endif
                                                        @endif

                                                    </td>

                                                    <td class='text-center'>
                                                        {{ countSubmissions($row->id) }}
                                                    </td>
                                                    <td class='text-center'>
                                                        {{ countUngradedSubmissions($row->id) }}
                                                    </td>

                                                    <td data-sort='{{ $loop->iteration }}' style='width:20%;'>
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
                                                                <small class='label label-warning'>
                                                                    {{ trans('langDaysLeft') }} {!! format_time_duration($row->time) !!}
                                                                </small>
                                                            </div>
                                                        @elseif (intval($row->deadline))
                                                            <div>
                                                                <small class='label label-danger'>
                                                                    {{ trans('langHasExpiredS') }}
                                                                </small>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td style='width:10%;' class='text-center'>
                                                        @if ($is_editor)
                                                            {!!
                                                                action_button(array(
                                                                    array('title' => trans('langEditChange'),
                                                                          'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=edit",
                                                                          'icon' => 'fa-edit'),
                                                                    array('title' => trans('langWorkUserGroupNoSubmission'),
                                                                          'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;disp_non_submitted=true",
                                                                          'icon' => 'fa-minus-square'),
                                                                    array('title' => $row->active == 1 ? trans('langDeactivate') : trans('langActivate'),
                                                                            'url' => $row->active == 1 ? "{$urlAppend}modules/work/index.php?course=$course_code&amp;choice=disable&amp;id=$row->id" : "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=enable&amp;id=$row->id",
                                                                            'icon' => $row->active == 1 ? 'fa-eye-slash' : 'fa-eye'),
                                                                    array('title' => trans('langWorkSubsDelete'),
                                                                            'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=do_purge",
                                                                            'icon' => 'fa-eraser',
                                                                            'confirm' => trans('langWarnForSubmissions'),
                                                                            'show' => (countSubmissions($row->id) > 0)),
                                                                    array('title' => trans('langDelete'),
                                                                            'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=do_delete",
                                                                            'icon' => 'fa-xmark',
                                                                            'class' => 'delete',
                                                                            'confirm' => trans('langWorksDelConfirm'))))
                                                            !!}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                
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
                'columns': [ null, null, null, null, { orderable: false } ],
                'fnDrawCallback': function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
                'lengthMenu': [10, 20, 30 , -1],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'responsive': true,
                'order' : [ [3, 'asc'] ],
                'oLanguage': {
                    'lengthLabels': {
                        '-1': '{{ trans('langAllOfThem') }}'
                     },
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sZeroRecords': '{{ trans('langNoResult') }}',
                    'sEmptyTable': '{{ trans('langNoResult') }}',
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
            $('.dt-search input').attr({
                'class': 'form-control input-sm ms-0 mb-3',
                'placeholder': '{{ trans('langSearch') }}...'
            });
            $('.dt-search label').attr('aria-label', '{{ trans('langSearch') }}');

            $(document).on('click', '.assigned_to', function(e) {
                e.preventDefault();
                var ass_id = $(this).data('ass_id');
                let url = '{{ $urlAppend }}' + 'modules/work/index.php?ass_info_assigned_to=true&ass_id='+ass_id;
                $.ajax({
                    url: url,
                    success: function(data) {
                        var dialog = bootbox.dialog({
                            message: data,
                            title : '{{ trans('langWorkAssignTo') }}',
                            onEscape: true,
                            backdrop: true,
                            buttons: {
                                success: {
                                    label: '{{ trans('langClose') }}',
                                    className: 'cancelAdminBtn',
                                }
                            }
                        });
                        dialog.init(function() {
                            typeof MathJax !== 'undefined' && MathJax.typeset();
                        });
                    }
                });
            });

        });
    </script>

@endsection

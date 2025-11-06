
@extends('layouts.default')

@push('head_styles')
    <style>
        .table-responsive td { word-break: break-word; }
    </style>
@endpush

@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {

            $.fn.dataTable.ext.order['dom-text-numeric'] = function(settings, col) {
                return this.api()
                    .column(col, { order: 'index' })
                    .nodes()
                    .map(function(td) {
                        let val = parseFloat($('input', td).val().trim()); // Ensure numeric parsing
                        return isNaN(val) ? -Infinity : val; // Use -Infinity for empty values
                    })
                    .toArray();
            };

            var table = $('#submissions_table_{{ $course_code }}').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": 'tools-col' },
                    { "type": "num", "orderDataType": "dom-text-numeric", "targets": 'grade-col' } // FIXED: No dot (.)
                ],
                'lengthMenu': [10, 20, 50, -1],
                'searchDelay': 1000,
                'oLanguage': {
                    'lengthLabels': {
                        '-1': '{{ trans('langAllOfThem') }}'
                     },
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sEmptyTable': '{{ trans('langNoSubmissions') }}',
                    'sZeroRecords': '{{ trans('langNoSubmissions') }}',
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

            $('.work-form').on('submit', function(e) {
                var form = this;

                table.rows().every(function() {
                    var row = $(this.node());

                    row.find('input').each(function() {
                        var input = $(this);
                        $('<input>').attr({
                            type: 'hidden',
                            name: input.attr('name'),
                            value: input.val()
                        }).appendTo(form);
                    });
                });
            });

            $('.table-default').on('click', '.onlineText', function (e) {
                e.preventDefault();
                var sid = $(this) . data('id');
                var assignment_title = $('#assignment_title') . text();
                $.ajax({
                    type: 'POST',
                    url: '',
                    datatype: 'json',
                    data: {
                        sid: sid
                    },
                    success: function (data){
                        data = $.parseJSON(data);
                        bootbox.alert({
                            title: assignment_title,
                            size: 'large',
                            message: data.submission_text ? data.submission_text : '',
                            buttons:
                                {
                                    ok: {
                                        label: '{{ trans('langClose') }}',
                                        className: 'submitAdminBtn position-center'
                                    }
                                },
                        });
                    },
                    error: function (xhr, textStatus, error) {
                        console . log(xhr . statusText);
                        console . log(textStatus);
                        console . log(error);
                    }
                });
            });

            $('.table-default').on('click', '.linkdelete', function(e) {
                var link = $(this).attr('href');
                e.preventDefault();
                bootbox.confirm({
                    closeButton: false,
                    title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><div class=\'modal-title-default text-center mb-0\'> {{ trans('langConfirmDelete') }}</div>',
                    message: '<p class=\'text-center\'> {{ trans('langDelWarnUserAssignment') }}</p>',
                    buttons: {
                        cancel: {
                            label: '{{ trans('langCancel') }}',
                            className: 'cancelAdminBtn position-center'
                        },
                        confirm: {
                            label: '{{ trans('langDelete') }}',
                            className: 'deleteAdminBtn position-center',
                        }
                    },
                    callback: function (result) {
                        if(result) {
                            document.location.href = link;
                        }
                    }
                });
            });

            $('button#transfer_grades').click(function(e) {
                e.preventDefault();
                $('input[name=grade_review]').each(function() {
                    if (this.value) {
                        var input_grade_value_name = 'grades[' + this.id + '][grade]';
                        var input_grade = $('input[name=\"' + input_grade_value_name + '\"]');
                        if (!input_grade.val()) {
                            input_grade.val(this.value);
                        }
                    }
                });
            })
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

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        @include('modules.work.assignment_details')

                        <div class="col-12 d-flex justify-content-center my-4">
                            <div class="bg-transparent">
                                <i class="fa-solid fa-circle-chevron-down fs-1 Neutral-900-cl"></i>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card panelCard px-lg-4 py-lg-3" @if($assign->deadline && $cdate > $assign->deadline) style="opacity: 0.65;" @endif>
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0" style="line-height: 14px;">
                                        <em>{{ trans('langAssignmentsSubmission')}}</em>
                                    </h3>
                                    <small><em>{{ trans('langPhase') }} (1)</em></small>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0">
                                            <p class="form-label mb-0">{{ trans('langStartDate') }}</p>
                                            <p><span>{!! format_locale_date(strtotime($assign->submission_date)) !!}</span></p>
                                        </li>
                                        @if ($assign->deadline)
                                        <li class="list-group-item px-0">
                                            <p class="form-label mb-0">{{ trans('langEndDate') }}</p>
                                            <p><span>{!! format_locale_date(strtotime($assign->deadline)) !!}</span></p>
                                        </li>
                                        @endif
                                        <li class="list-group-item px-0">
                                            @if ($cdate < $assign->submission_date)
                                                <p class="text-warning TextBold small-text" style="line-height:14px;">{{ trans('langAssignmentsSubmissionHasNoStarted') }}</p>
                                            @elseif ($cdate >= $assign->submission_date && $cdate <= $assign->deadline)
                                                <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                    <p class="text-success TextBold small-text" style="line-height:14px;">{{ trans('langAssignmentsSubmissionInProgress') }}</p>
                                                    <div>
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
                                                </div>
                                            @elseif ($assign->deadline && $cdate > $assign->deadline)
                                                <p class="text-danger TextBold small-text" style="line-height:14px;">{{ trans('langAssignmentsSubmissionHasExpired') }}</p>
                                            @endif
                                        </li>
                                        <li class="list-group-item px-0">
                                            @if ($count_of_assignments > 0)
                                                <div class='col-12'>
                                                    <p class="TextBold Success-200-cl small-text"style="line-height:14px;">
                                                        {{ trans('langSubmissions') }}:&nbsp;
                                                        <span class="badge Success-200-bg" style="border-radius: 50%;">
                                                            {{ $count_of_assignments }}
                                                        </span>
                                                    </p>
                                                </div>
                                            @else
                                                <div class='col-12'>
                                                    <span class="badge Warning-200-bg">{{ trans('langNoSubmissions') }}</span>
                                                </div>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-center my-4 @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) d-block @else d-none @endif">
                                <div class="bg-transparent">
                                    <i class="fa-solid fa-circle-chevron-down fs-1 Neutral-900-cl"></i>
                                </div>
                            </div>
                            <div class="card panelCard px-lg-4 py-lg-3 @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) d-block @else d-none @endif" @if($assign->due_date_review && ($cdate > $assign->due_date_review or $cdate < $assign->start_date_review)) style="opacity: 0.65;" @endif>
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0" style="line-height: 14px;">
                                        <em>{{ trans('langGradeReviews') }}</em>
                                    </h3>
                                    <small><em>{{ trans('langPhase') }} (2)</em></small>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0">
                                            <p class="form-label mb-0">{{ trans('langStartDate') }}</p>
                                            <p><span>{!! format_locale_date(strtotime($assign->start_date_review)) !!}</span></p>
                                        </li>
                                        <li class="list-group-item px-0">
                                            <p class="form-label mb-0">{{ trans('langEndDate') }}</p>
                                            <p><span>{!! format_locale_date(strtotime($assign->due_date_review)) !!}</span></p>
                                        </li>
                                        @if ($cdate > $assign->deadline)
                                            <li class="list-group-item px-0">
                                                @if ($assign->reviews_per_assignment < $count_of_assignments)
                                                    @if ($cdate < $assign->start_date_review)
                                                        <p class="text-warning TextBold small-text" style="line-height:14px;">{{ trans('langGradeReviewHasNotStarted') }}</p>
                                                    @elseif ($cdate >= $assign->start_date_review && $cdate <= $assign->due_date_review)
                                                        <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                            <p class="text-success TextBold small-text" style="line-height:14px;">{{ trans('langGradeReviewInProgress') }}</p>
                                                            <div>
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
                                                        </div>
                                                    @elseif ($cdate > $assign->due_date_review)
                                                        <p class="text-danger TextBold small-text" style="line-height:14px;">{{ trans('langGradeReviewHasExpired') }}</p>
                                                    @endif
                                                    <form class='form-horizontal mt-4' role='form' method='post' action='index.php?course={{ $course_code }}' enctype='multipart/form-data'>
                                                        <input type='hidden' name='assign' value='{{ $id }}'>
                                                        <div class='form-group'>
                                                            <div class='mt-3 text-center'>
                                                                <input style='white-space: normal; height: auto; opacity: 1 !important;' class='btn submitAdminBtn' type='submit' name='ass_review' value='{{ trans('langAssignmentDistribution') }}'>
                                                            </div>
                                                            <div class="mt-2">
                                                                <div class="help-block">(*) {{ trans('OldAssignmentsWillBeRemoved') }}</div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <div class="mt-4">

                                                        <h4 class="">
                                                            <i class="fa-solid fa-circle-info"></i>
                                                            &nbsp;{{ trans('langPreviewGradesAssessments') }}
                                                        </h4>

                                                        @if (count($grades_info) > 0)
                                                            <table class="table-default">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ trans('langUser') }}</th>
                                                                        <th>{{ trans('langReceivedFrom') }}</th>
                                                                        <th class="text-center">{{ trans('langTotal') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                @foreach ($grades_info as $index => $g)
                                                                    <tr>
                                                                        <td>{{ uid_to_name($index) }}</td>
                                                                        <td>{!! $g['grade_received'] !!}</td>
                                                                        <td>
                                                                            <div class="d-flex justify-content-center">
                                                                                <span class='TextBold Success-200-cl fs-6'>{!! $g['grade_total'] !!}</span>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @else
                                                            <div class="alert alert-warning">
                                                                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                                                                <span>{{ trans('langNoInfoAvailable') }}</span>
                                                            </div>
                                                        @endif

                                                    </div>
                                                @else
                                                    <div class='col-12'>
                                                        <div class='alert alert-warning'>
                                                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langPeerReviewImpossible') }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-center my-4">
                                <div class="bg-transparent">
                                    <i class="fa-solid fa-circle-chevron-down fs-1 Neutral-900-cl"></i>
                                </div>
                            </div>
                            <div class="card panelCard px-lg-4 py-lg-3" @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $cdate <= $assign->due_date_review) style="opacity: 0.65;" @endif>
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0" style="line-height: 14px;">
                                        <em>{{ trans('langFinalGrade') }}</em>
                                    </h3>
                                    <small>
                                        @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE)
                                            <em>{{ trans('langPhase') }} (3)</em>
                                        @else
                                            <em>{{ trans('langPhase') }} (2)</em>
                                        @endif
                                    </small>
                                </div>
                                <div class="card-body">
                                    @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE)
                                        @if ($cdate > $assign->due_date_review)
                                            <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                <p class="text-success TextBold small-text" style="line-height:14px;">{{ trans('langFinalGradeProcessInProgress') }}</p>
                                                <div>
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
                                            </div>
                                            <div class='mt-4'>
                                                <button class='btn submitAdminBtn' href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}' id='transfer_grades'>{{ trans('langTransferGrades') }}</button>
                                            </div>
                                        @else
                                            <p class="text-warning TextBold small-text" style="line-height:14px;">{{ trans('langAddFinalGradeHasNotStartedYet') }}</p>
                                        @endif
                                    @endif

                                    <form action='{{ $urlAppend }}modules/work/index.php?course={{ $course_code }}' method='post' class='form-inline work-form'>
                                        <input type='hidden' name='grades_id' value='{{ $id }}'>
                                        <div class='table-responsive'>
                                            <table id ='submissions_table_{{ $course_code }}' class='table table-default'>
                                                <thead>
                                                    <tr class='list-header'>
                                                        <th class="user-col">
                                                            {{ trans('langSurnameName') }}
                                                        </th>
                                                        @if ($assign->submission_type == 1)
                                                            <th>{{ trans('langWorkOnlineText') }}</th>
                                                        @elseif ($assign->submission_type == 2)
                                                            <th>{{ trans('langOpenCoursesFiles') }}</th>
                                                        @else
                                                            <th>{{ trans('langFileName') }}</th>
                                                        @endif
                                                        @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {{-- neo pedio vathmos aksiologhshs mono gia peer review --}}
                                                        <th class="grade-col">
                                                            {{ trans('langPeerReviewGrade') }}
                                                        </th>
                                                        @endif
                                                        <th class="grade-col" style="width: 10%;">{{ trans('langGradebookGrade') }}</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach ($result as $row)
                                                        @if (isset($seen[$row->uid])) {{-- used for submission with multiple files --}}
                                                            @continue
                                                        @endif
                                                    {{-- student data --}}
                                                        <tr>
                                                            <td class='user-col' style='width: 40%'>
                                                                @if (empty($row->group_id))
                                                                    {!! display_user($row->uid) !!}
                                                                @else
                                                                    {!! display_group($row->group_id) !!}
                                                                @endif
                                                                @if (!is_null(uid_to_am($row->uid)))
                                                                    <div class='text-heading-h6 my-3'>
                                                                        {{ trans('langAmShort') }}: {{ uid_to_am($row->uid) }}
                                                                    </div>
                                                                @endif

                                                                {{-- peer review status message --}}
                                                                @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE)
                                                                    @if ($count_of_assignments > $assign->reviews_per_assignment && $rows_assignment_grading_review)
                                                                        {!! get_review_status_message($start_date_review, $id, $row->uid) !!}
                                                                    @endif
                                                                @endif

                                                                    {{-- student comments --}}
                                                                @if (trim($row->comments != ''))
                                                                    <div class='my-3'>
                                                                        <small> {{ $row->comments }}</small>
                                                                    </div>
                                                                @endif

                                                                <div class='my-3'>
                                                                    @if ($row->grade_comments or $row->grade_comments_filename) {{-- teacher comments --}}
                                                                        <strong>
                                                                            {{ trans('langGradeComments') }}
                                                                        </strong>
                                                                        @if (preg_match('/[\n\r] +\S/', trim(q_math($row->grade_comments))))
                                                                            <div style='white-space: pre-wrap'>{!! trim(q_math($row->grade_comments)) !!}</div>
                                                                        @else
                                                                            &nbsp;<span>{!! nl2br(trim(q_math($row->grade_comments))) !!}</span>
                                                                        @endif
                                                                        <div>
                                                                            {!! MultimediaHelper::chooseMediaAhrefRaw("$_SERVER[SCRIPT_NAME]?course=$course_code&amp;getcomment=$row->id", "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;getcomment=$row->id", $row->grade_comments_filename, $row->grade_comments_filename); !!}
                                                                        </div>
                                                                    @endif
                                                                    @if ($row->grade != '') {{-- grade submission date --}}
                                                                        <div class='text-heading-h6 mt-2'>
                                                                            {{ trans('langGradedAt') }} {!! format_locale_date(strtotime($row->grade_submission_date), 'short', false) !!}
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                {{-- auto judge results --}}
                                                                @if($autojudge->isEnabled() and $assign->auto_judge)
                                                                    <a href='{{ $urlAppend }}modules/work/work_result_rpt.php?course={{ $course_code }}&assignment={{ $id }}&submission={{ $row->id }}'>
                                                                        <strong>{{ trans('langAutoJudgeShowWorkResultRpt') }}</strong>
                                                                    </a>
                                                                @endif
                                                            </td>

                                                            {{-- submission files --}}
                                                            <td class='filename-col text-nowrap'>
                                                                @if ($assign->submission_type == 1)
                                                                    <button class='onlineText btn btn-xs btn-default submitAdminBtn' data-id='{{ $row->id }}'>
                                                                        {{ trans('langQuestionView') }}
                                                                    </button>
                                                                @elseif (!empty($row->file_name))
                                                                    {!! get_user_file_submissions($assign, $result, $row) !!}
                                                                    <br> {!! get_unplag_plagiarism_results($row->id) !!}
                                                                @endif

                                                                <div class="col-12 mt-3">
                                                                    {!! format_locale_date(strtotime($row->submission_date)) !!}
                                                                    @if ($row->deadline && $row->submission_date > $row->deadline)
                                                                        <div class='Accent-200-cl'>
                                                                            <small class='Accent-200-cl'>{{ trans('langLateSubmission') }}</small>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </td>

                                                            {{-- Peer Review Grade results --}}
                                                            @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE)
                                                                @if ($count_of_assignments > $reviews_per_assignment && $rows_assignment_grading_review)
                                                                    <td class='col-md-1 text-center'>
                                                                        <div class='form-group'>
                                                                            {!! get_grade_review_field($due_date_review, $row->id, $reviews_per_assignment) !!}
                                                                        </div>
                                                                    </td>
                                                                @endif
                                                            @endif

                                                            {{-- grade input text --}}
                                                            <td>
                                                                <div class='form-group {!! Session::getError("grade.$row->id") ? "has-error" : "" !!}'>
                                                                    @if($row->grading_scale_id && $row->grading_type == ASSIGNMENT_RUBRIC_GRADE && empty($row->grade) && $is_editor)
                                                                        <a class='link' href='{{ $urlAppend }}modules/work/grade_edit.php?course={{ $course_code }}&assignment={{ $id }}&submission={{ $row->id }}' aria-label='{{ trans('langSGradebookBook') }}'>
                                                                            <span class='fa fa-fw fa-plus' data-bs-original-title='{{ trans('langSGradebookBook') }}' title='' data-bs-toggle='tooltip'></span></a>
                                                                    @elseif ($row->grading_scale_id && $row->grading_type == ASSIGNMENT_SCALING_GRADE && $is_editor)
                                                                        <select name='grades[{{ $row->id }}][grade]' class='form-control' id='scales'>
                                                                            {!! get_scale_options($row->grading_scale_id, $row->grade) !!}
                                                                        </select>
                                                                    @else
                                                                        <input aria-label='{{ trans('langGradebookGrade') }}' class='form-control' type='text' value='{{ $row->grade }}' name='grades[{{ $row->id }}][grade]' maxlength='4' size='3' {!! $disabled !!}>
                                                                    @endif
                                                                    <span class='help-block Accent-200-cl'>{!! Session::getError("grade.$row->id") !!}</span>
                                                                </div>

                                                                <div class='col-12 mt-3 d-flex justify-content-center gap-2'>
                                                                    @if (isset($_GET['unit']))
                                                                        <a class='link' href='../work/grade_edit.php?course={{ $course_code }}&assignment={{ $id }}&submission={{ $row->id }}' aria-label='{{ trans('langEdit') }}'>
                                                                    @else
                                                                        <a class='link' href='grade_edit.php?course={{ $course_code }}&assignment={{ $id }}&submission={{ $row->id }}' aria-label='{{ trans('langEdit') }}'>
                                                                    @endif
                                                                        <span class='fa-solid fa-edit' data-bs-original-title='{{ trans('langEdit') }}' title='' data-bs-toggle='tooltip'></span>
                                                                    </a>
                                                                    <a class='linkdelete' href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $id }}&as_id={{ $row->id }}' aria-label='{{ trans('langDeleteSubmission') }}'>
                                                                        <span class='fa-solid fa-xmark text-danger' data-bs-original-title='{{ trans('langDeleteSubmission') }}' title='' data-bs-toggle='tooltip'></span>
                                                                    </a>
                                                                </div>

                                                            </td>
                                                        </tr>
                                                        @php
                                                            /* used for submissions with multiple files */
                                                            $seen[$row->uid] = true;
                                                        @endphp
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if ($is_editor)
                                            <div class='form-group mt-4'>
                                                <div class='col-12'>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                            <input type='checkbox' value='1' name='send_email'><span class='checkmark'></span> {{ trans('langMailToUsers') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='mt-4 d-flex justify-content-end'>
                                                <button class='btn submitAdminBtn' type='submit' name='submit_grades' {!! $disabled !!}>{{ trans('langGradeOk') }}</button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>


                        {{-- Turnitin Results --}}
                        @if ($assign->assignment_type == ASSIGNMENT_TYPE_TURNITIN)
                            {!! show_turnitin_integration($id) !!}
                        @endif


                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


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
                'aLengthMenu': [
                    [10, 20, 50 , -1],
                    [10, 20, 50, '{{ trans('langAllOfThem') }}']
                ],
                'searchDelay': 1000,
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

            $('.linkdelete') .click(function(e) {
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

                        {{--  Peer Review assignment distribution --}}
                        @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $cdate > $assign->deadline)
                            @if ($assign->reviews_per_assignment < $count_of_assignments)
                                <form class='form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}' enctype='multipart/form-data'>
                                    <input type='hidden' name='assign' value='{{ $id }}'>
                                    <div class='form-group'>
                                        <div class='mt-3 text-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='ass_review' value='{{ trans('langAssignmentDistribution') }}'>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class='col-12'>
                                    <div class='alert alert-warning'>
                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langPeerReviewImpossible') }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- Turnitin Results --}}
                        @if ($assign->assignment_type == ASSIGNMENT_TYPE_TURNITIN)
                            {!! show_turnitin_integration($id) !!}
                        @endif

                        {{-- list of submissions --}}
                        @if ($count_of_assignments > 0)
                            <form action='{{ $urlAppend }}modules/work/index.php?course={{ $course_code }}' method='post' class='form-inline work-form'>
                                <input type='hidden' name='grades_id' value='{{ $id }}'>
                                <br>
                                <div class='alert alert-success'>
                                    <strong>{{ trans('langSubmissions') }}:</strong>&nbsp;{{ $count_of_assignments }}
                                </div>
                                {{-- button for transferring student peer review grades to teacher grades --}}
                                @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $cdate > $assign->deadline)
                                    <div class='mt-4'>
                                        <button class='btn submitAdminBtn' href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}' id='transfer_grades'>{{ trans('langTransferGrades') }}</button>
                                    </div>
                                @endif

                                <div class='table-responsive mt-3'>
                                    <table id ='submissions_table_{{ $course_code }}' class='table table-default'>
                                        <thead>
                                            <tr class='list-header'>
                                                {!! sort_link(trans('langSurnameName'), 'username', 'class="user-col"') !!}
                                                @if ($assign->submission_type == 1)
                                                    <th>{{ trans('langWorkOnlineText') }}</th>
                                                @elseif ($assign->submission_type == 2)
                                                    <th>{{ trans('langOpenCoursesFiles') }}</th>
                                                @else
                                                    <th>{{ trans('langFileName') }}</th>
                                                @endif

                                                {!! sort_link(trans('langSubDate'), 'date', 'class="date-col"') !!}

                                                @if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $cdate > $assign->deadline) {{-- neo pedio vathmos aksiologhshs mono gia peer review --}}
                                                    {!! sort_link(trans('langPeerReviewGrade'), '') !!}
                                                @endif
                                                {!! sort_link(trans('langGradebookGrade'), 'grade', 'style="width: 10%;" class="grade-col"') !!}
                                                @if ($is_editor)
                                                    <th class='tools-col' style='width:10%;' aria-label='{{ trans('langSettingSelect') }}'></th>
                                                @endif
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($result as $row)
                                            {{-- student data --}}
                                                <tr>
                                                    <td class='user-col' style='width: 45%'>
                                                        @if (empty($row->group_id))
                                                            {!! display_user($row->uid) !!}
                                                        @else
                                                            {!! display_group($row->group_id) !!}
                                                        @endif
                                                        @if (!is_null(uid_to_am($row->uid)))
                                                            <div class='text-heading-h6'>
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
                                                            <div class='mt-1'>
                                                                <small> {{ $row->comments }}</small>
                                                            </div>
                                                        @endif

                                                        <div class='mt-2'>
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
                                                                <div class='text-heading-h6 mt-1'>
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
                                                    <td class='filename-col col-md-2'>
                                                        @if ($assign->submission_type == 1)
                                                            <button class='onlineText btn btn-xs btn-default' data-id='{{ $row->id }}'>
                                                                {{ trans('langQuestionView') }}
                                                            </button>
                                                        @elseif (!empty($row->file_name))
                                                            {!! get_user_file_submissions($assign, $result, $row) !!}
                                                            <br> {!! get_unplag_plagiarism_results($row->id) !!}
                                                        @endif
                                                    </td>

                                                    {{-- submission date --}}
                                                    <td class='col-md-1'>
                                                        <small>
                                                            {!! format_locale_date(strtotime($row->submission_date)) !!}
                                                            @if ($row->deadline && $row->submission_date > $row->deadline)
                                                                <div class='Accent-200-cl'>
                                                                    <small style='color: red;'>{{ trans('langLateSubmission') }}</small>
                                                                </div>
                                                            @endif
                                                        </small>
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
                                                    </td>

                                                    {{-- edit / delete buttons --}}
                                                    @if ($is_editor)
                                                        <td class='text-end'>
                                                            @if (isset($_GET['unit']))
                                                                <a class='link' href='../work/grade_edit.php?course={{ $course_code }}&assignment={{ $id }}&submission={{ $row->id }}' aria-label='{{ trans('langEdit') }}'>
                                                            @else
                                                                <a class='link' href='grade_edit.php?course={{ $course_code }}&assignment={{ $id }}&submission={{ $row->id }}' aria-label='{{ trans('langEdit') }}'>
                                                            @endif
                                                                <span class='fa fa-fw fa-edit' data-bs-original-title='{{ trans('langEdit') }}' title='' data-bs-toggle='tooltip'></span>
                                                            </a>&nbsp;
                                                            <a class='linkdelete ps-2' href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $id }}&as_id={{ $row->id }}' aria-label='{{ trans('langDeleteSubmission') }}'>
                                                                <span class='fa fa-fw fa-xmark text-danger' data-bs-original-title='{{ trans('langDeleteSubmission') }}' title='' data-bs-toggle='tooltip'></span>
                                                            </a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($is_editor)
                                    <div class='form-group'>
                                        <div class='col-12'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                    <input type='checkbox' value='1' name='send_email'><span class='checkmark'></span> {{ trans('langMailToUsers') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='mt-4'>
                                        <button class='btn submitAdminBtn' type='submit' name='submit_grades' {!! $disabled !!}>{{ trans('langGradeOk') }}</button>
                                    </div>
                                @endif
                            </form>
                        @else
                            <div class='col-12 mt-3 bg-transparent'>
                                <p class='sub_title1 text-center TextBold mb-0 pt-2'>{{ trans('langSubmissions') }}:</p>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoSubmissions') }}</span>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

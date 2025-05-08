<div class='col-12'>
    <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
            <h3 class='mb-0'>
                {{ trans('langWorkInfo') }}
            </h3>
            @if ($is_editor)
                <a href='{{ $urlAppend }}modules/work/index.php?course={{ $course_code }}&amp;id={{ $row->id }}&amp;choice=edit' aria-label='{{ trans('langEditChange') }}'>
                    <span class='fa-solid fa-edit fa-lg' title='{{ trans('langEditChange') }}' data-bs-toggle='tooltip'></span>
                </a>
            @endif
        </div>

        <div class='card-body'>
            <ul class='list-group list-group-flush'>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>{{ trans('langTitle') }}</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height' id='assignment_title'>
                            {{ $row->title }}
                        </div>
                    </div>
                </li>
                @if (!empty($row->description))
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langDescription') }}</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                {!! mathfilter($row->description, 12 , "../../courses/mathimg/") !!}
                            </div>
                        </div>
                    </li>
                @endif
                @if (!empty($row->comments))
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langComments') }}</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height' style='white-space: pre-wrap'>
                                {{ $row->comments }}
                            </div>
                        </div>
                    </li>
                @endif

                @if (!empty($row->file_name))
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langWorkFile') }}</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                {!! MultimediaHelper::chooseMediaAhrefRaw($fileUrl, $fileUrl, $row->file_name, $row->file_name) !!}
                            </div>
                        </div>
                    </li>
                @endif

                @if ($grade_type != ASSIGNMENT_SCALING_GRADE)
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langMaxGrade') }}</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                {{ $row->max_grade }}
                            </div>
                        </div>
                    </li>
                @endif

                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default mb-1'>{{ trans('langGradeType') }}</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            @if ($preview_rubric == 1)
                                <a class='' role='button' data-bs-toggle='collapse' href='#collapseRubric' aria-expanded='false' aria-controls='collapseRubric'>
                                    @switch ($grade_type)
                                        @case (ASSIGNMENT_STANDARD_GRADE)
                                            {{ trans('langGradeNumber') }}
                                            @break
                                        @case(ASSIGNMENT_SCALING_GRADE)
                                            {{ trans('langGradeScale') }}
                                            @break
                                        @case(ASSIGNMENT_RUBRIC_GRADE)
                                            {{ trans('langGradeRubric') }}
                                            @break
                                        @case(ASSIGNMENT_PEER_REVIEW_GRADE)
                                            {{ trans('langGradeReviews') }}
                                            @break
                                    @endswitch
                                </a>
                                </div>
                            </div>
                            <div class='table-responsive collapse' id='collapseRubric'>
                                <table class='table-default'>
                                    <thead class='list-header'>
                                        <th>{{ trans('langDetail') }}</th>
                                        <th>{{ trans('langCriteria') }}</th>
                                    </thead>
                                    <tr>
                                        <td>
                                            <div class='text-heading-h5'>{{ $rubric_name }}</div>
                                            <div class='text-heading-h6'>{!! $rubric_desc !!}</div>
                                        </td>
                                        <td>
                                            <ul class='list-unstyled'>
                                                {!! $criteria_list !!}
                                            </ul>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            @else
                                @switch ($row->grading_type)
                                    @case (ASSIGNMENT_STANDARD_GRADE)
                                        {{ trans('langGradeNumber') }}
                                    @break
                                    @case(ASSIGNMENT_SCALING_GRADE)
                                        {{ trans('langGradeScale') }}
                                    @break
                                    @case(ASSIGNMENT_RUBRIC_GRADE)
                                        {{ trans('langGradeRubric') }}
                                    @break
                                    @case(ASSIGNMENT_PEER_REVIEW_GRADE)
                                        {{ trans('langGradeReviews') }}
                                    @break
                                @endswitch
                            </div>
                        </div>
                    @endif
                </li>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>{{ trans('langStartDate') }}</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            {!! format_locale_date(strtotime($row->submission_date)) !!}
                        </div>
                    </div>
                </li>

                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>
                                {{ trans('langGroupWorkDeadline_of_Submission') }}
                            </div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            @if (isset($row->deadline))
                                {!! format_locale_date(strtotime($row->deadline)) !!}
                            @else
                                {{ trans('langNoDeadLine') }}
                            @endif

                            @if ($row->time > 0)
                                <div>
                                    <small class='label label-warning'>
                                        {{ trans('langDaysLeft') }} {!! format_time_duration($row->time) !!}
                                    </small>
                                </div>
                            @elseif (intval($row->deadline))
                                <div>
                                    <small class='label label-danger'>
                                        {{ trans('langEndDeadLine') }}
                                    </small>
                                </div>
                            @endif

                        </div>
                    </div>
                </li>

                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>
                                {{ trans('langAssignmentType') }}
                            </div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            @if ($row->group_submissions == '0')
                                {{ trans('langUserAssignment') }}
                            @else
                                {{ trans('langGroupAssignment') }}
                            @endif
                        </div>
                    </div>
                </li>

                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>{{ trans('langWorkAssignTo') }}</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                        @if ($row->assign_to_specific == 1)
                                {{ trans('langWorkToUser') }}
                        @elseif ($row->assign_to_specific == 2)
                            {{ trans('langWorkToGroup') }}
                        @else
                            {{ trans('langWorkToAllUsers') }}
                        @endif
                        </div>
                    </div>
                </li>

                @if ($tags_list)
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langTags') }}</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                {!! $tags_list !!}
                            </div>
                        </div>
                    </li>
                @endif

                @if ($grade_type == ASSIGNMENT_PEER_REVIEW_GRADE && !$x)
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>
                                    {{ trans('langReviewStart') }}
                                </div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                {!! format_locale_date(strtotime($row->start_date_review)) !!}
                                @if ($row->time_start > 0)
                                    <div>
                                        <small class='label label-warning'>
                                            {{ trans('langDaysLeft') }} {!! format_time_duration($row->time_start) !!}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>

                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langReviewEnd') }}:</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                {!! format_locale_date(strtotime($row->due_date_review)) !!}
                                @if ($row->time_due > 0)
                                    <div>
                                        <small class='label label-warning'>
                                            {{ trans('langDaysLeft') }} {!! format_time_duration($row->time_due) !!}
                                        </small>
                                    </div>
                                @elseif (intval($row->due_date_review))
                                    <div>
                                        <small class="label label-danger">
                                            {{ trans('langEndDeadLine') }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>

@if (isset($row->deadline) && $row->deadline < date('Y-m-d H:i:s') && $row->late_submission && !$is_editor)
    <div class='col-sm-12'>
        <div class='alert alert-warning'>
            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
            <span>{{ trans('langWarnAboutDeadLine') }}</span>
        </div>
    </div>
@endif

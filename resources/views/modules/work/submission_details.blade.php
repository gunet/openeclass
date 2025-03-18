<div class='col-12 mt-4'>
    <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
            <h3>
                {{ trans('langSubmissionWorkInfo') }}
            </h3>
        </div>
        <div class='card-body'>
            <ul class='list-group list-group-flush'>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>
                                {{ trans('langSubmissionStatusWorkInfo') }}
                            </div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            {!! $notice !!}
                        </div>
                    </div>
                </li>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>
                                {{ trans('langGradebookGrade') }}
                            </div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            @if ($preview_rubric == 1 and $points_to_graded == 1)
                                <a role='button' data-bs-toggle='collapse' href='#collapseGrade' aria-expanded='false' aria-controls='collapseGrade'>
                                     {{ $grade }}
                                </a>
                                <div class='table-responsive collapse' id='collapseGrade'>
                                    <table class='table-default'>
                                        <thead class='list-header'>
                                            <th>{{ trans('langCriteria') }}</th>
                                        </thead>
                                        <tr>
                                            <td>
                                                <ul class='list-unstyled'>
                                                    {!! $criteria_list !!}
                                                </ul>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @else
                            {{ $grade }}
                        @endif
                    </div>
                </li>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>
                                {{ trans('langGradeComments') }}
                            </div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height' style='white-space: pre-wrap'>
                            {!! $grade_comments !!} {{ $file_comments_link }}
                        </div>
                    </div>
                </li>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>
                                {{ trans('langSubDate') }}
                            </div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>{!! $submission_date !!}</div>
                    </div>
                </li>

                @if ($submission_type == ASSIGNMENT_RUBRIC_GRADE)
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langOpenCoursesFiles') }}</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                {!! $links !!}
                            </div>
                        </div>
                    </li>
                @elseif ($submission_type == ASSIGNMENT_STANDARD_GRADE)
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langFileName') }}</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                {!! $filelink !!}
                            </div>
                        </div>
                    </li>
                @else {{-- online text --}}
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans(('langWorkOnlineText')) }}</div>
                            </div>
                            <div class='col-sm-9 col-12 title-default-line-height'>
                                <a href='#' class='onlineText btn submitAdminBtn d-inline-flex' data-id='{{ $submission_id }}'>{{ trans('langQuestionView') }}</a>
                            </div>
                        </div>
                    </li>
                @endif

                @if ($assignment_auto_judge)
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>{{ trans('langAutoJudgeEnable') }}</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                <a href='{{ $urlAppend }}modules/work/work_result_rpt.php?course={{ $course_code }}&amp;assignment={{ $sub->assignment_id }}&amp;submission={{ $submission_id }}'> {{ trans('langAutoJudgeShowWorkResultRpt') }}</a>
                            </div>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>

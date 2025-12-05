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

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        <div class='d-lg-flex gap-4 mt-4'>
                            <div class='flex-grow-1'>
                                <div class='form-wrapper form-edit rounded'>
                                    <form class='form-horizontal' enctype='multipart/form-data' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}'>
                                        @if (isset($assignment_id))
                                            <input type='hidden' name='id' value='{{ $assignment_id }}'>
                                            <input type='hidden' name='choice' value='do_edit'>
                                        @endif
                                        <fieldset>
                                            <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                            <div class='row form-group @if($title_error) has-error @endif'>
                                                <label for='title' class='col-12 control-label-notes mb-1'>{{ trans('langTitle') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                                <div class='col-12'>
                                                    <input name='title' type='text' class='form-control' id='title' placeholder='{{ trans('langTitle') }}' value='{{ $title }}'>
                                                    <span class='help-block Accent-200-cl'>{{ $title_error }}</span>
                                                </div>
                                            </div>
                                            <div class='row form-group mt-4'>
                                                <label for='desc' class='col-12 control-label-notes mb-1'>{{ trans('langDescription') }}</label>
                                                <div class='col-12'>
                                                    {!! rich_text_editor('desc', 4, 20, $desc) !!}
                                                </div>
                                            </div>
                                            <div class='row form-group mt-4'>
                                                <label for='userfile' class='col-12 control-label-notes mb-1'>{{ trans('langWorkFile') }}</label>
                                                <div class='col-12'>
                                                    @if ($assignment_filename)
                                                        <a href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;get={{ $assignment_id }}&amp;file_type=1'>{{ $assignment_filename }}</a>
                                                        <a href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;id={{ $assignment_id }}&amp;choice=do_delete_file' onClick='return confirmation("{{ trans('langWorkDeleteAssignmentFileConfirm') }}");'>
                                                        <span class='fa-solid fa-xmark fa-lg Accent-200-cl' title='{{ trans('langWorkDeleteAssignmentFileConfirm') }}'></span></a>
                                                    @endif
                                                    {!! fileSizeHidenInput() !!}
                                                    <input type='file' id='userfile' name='userfile'>
                                                </div>
                                            </div>

                                            @include('modules.work.lti_app_form')

                                            <div class='row form-group mt-4'>
                                                <div class='col-12 control-label-notes mb-1'>
                                                    {{ trans('langGradeType') }}
                                                </div>
                                                <div class='col-12'>
                                                    <div class='radio'>
                                                        <label>
                                                            <input type='radio' id='numbers_button' name='grading_type' value='0' @if ($grading_type == ASSIGNMENT_STANDARD_GRADE) checked @endif>
                                                            {{ trans('langGradeNumbers') }}
                                                        </label>
                                                    </div>

                                                    <div class='radio @if (!$grading_scales_exist) not_visible @endif'>
                                                        <label @if (!$grading_scales_exist) <label data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langNoGradeScales') }}' @endif>
                                                            <input type='radio' id='scales_button' name='grading_type' value='1' @if ($grading_type == ASSIGNMENT_SCALING_GRADE) checked @endif @if (!$grading_scales_exist) disabled @endif>
                                                            {{ trans('langGradeScales') }}
                                                        </label>
                                                    </div>

                                                    <div class='radio @if (!$rubrics_exist) not_visible @endif'>
                                                        <label @if (!$rubrics_exist) data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langNoGradeRubrics') }}' @endif>
                                                            <input type='radio' id='rubrics_button' name='grading_type' value='2' @if ($grading_type == ASSIGNMENT_RUBRIC_GRADE) checked @endif @if (!$rubrics_exist) disabled @endif>
                                                            {{ trans('langGradeRubrics') }}
                                                        </label>
                                                    </div>

                                                    <div class='radio @if (!$rubrics_exist) not_visible @endif'>
                                                        <label @if (!$rubrics_exist) data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langNoGradeRubrics') }}' @endif>
                                                            <input type='radio' id='reviews_button' name='grading_type' value='3' @if ($grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) checked @endif @if (!$rubrics_exist) disabled @endif>
                                                            {{ trans('langGradeReviews') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='row form-group @if ($max_grade_error) has-error @endif @if ($grading_type == ASSIGNMENT_STANDARD_GRADE) mt-4 @else hidden mt-4 @endif'>
                                                <label for='max_grade' class='col-12 control-label-notes mb-1'>{{ trans('langMaxGrade') }}</label>
                                                <div class='col-12'>
                                                    <input name='max_grade' type='text' class='form-control' id='max_grade' placeholder='{{ trans('langMaxGrade') }}' value='{{ $max_grade }}'>
                                                    <span class='help-block'>{{ $max_grade_error }}</span>
                                                </div>
                                            </div>

                                            <div class='row form-group @if ($scale_error) has-error @endif @if ($grading_type == ASSIGNMENT_SCALING_GRADE) mt-4 @else hidden mt-4 @endif'>
                                                <label for='scales' class='col-12 control-label-notes mb-1'>{{ trans('langGradeScales') }}</label>
                                                <div class='col-12'>
                                                    <select name='scale' class='form-select' id='scales' @if (!$grading_type) disabled @endif>
                                                        {!! $scale_options !!}
                                                    </select>
                                                    <span class='help-block'>{{ $scale_error }}</span>
                                                </div>
                                            </div>

                                            <div class='row form-group @if ($rubric_error) has-error @endif @if ($grading_type == ASSIGNMENT_RUBRIC_GRADE) mt-4 @else hidden mt-4 @endif'>
                                                <label for='rubrics' class='col-12 control-label-notes mb-1'>{{ trans('langGradeRubrics') }}</label>
                                                <div class='col-12'>
                                                    <select name='rubric' class='form-select' id='rubrics' @if (!$grading_type) disabled @endif>
                                                        {!! $rubric_options !!}
                                                    </select>
                                                    <span class='help-block'>{{ $rubric_error }}</span>
                                                </div>
                                            </div>

                                            <div class='row form-group @if ($review_error_user) has-error @endif @if ($grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) mt-4 @else hidden mt-4 @endif'>
                                                <label for='reviews_per_user' class='col-12 control-label-notes mb-1'>{{ trans('langReviewsPerUser') }}</label>
                                                <div class='col-12'>
                                                    <input name='reviews_per_user' type='text' class='form-control' id='reviews_per_user' value='{{ $reviews_per_user }}' @if (!$grading_type) disabled @endif>
                                                    <span class='help-block'>{{ trans('langAllowableReviewValues') }} {{ $review_error_user }}</span>
                                                </div>
                                            </div>

                                            <div class='row form-group @if ($review_error_rubric) has-error @endif @if ($grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) mt-4 @else hidden mt-4 @endif'>
                                                <label for='reviews' class='col-12 control-label-notes mb-1'>{{ trans('langGradeRubrics') }}</label>
                                                <div class='col-12'>
                                                    <select name='rubric_review' class='form-select' id='reviews' @if (!$grading_type) disabled @endif>
                                                        @if (isset($assignment_id))
                                                            {!! $rubric_options_review !!}
                                                        @else
                                                            {!! $rubric_options !!}
                                                        @endif
                                                    </select>
                                                    <span class='help-block'>&nbsp;{{ $review_error_rubric }}</span>
                                                </div>

                                                <div class='row input-append date @if (Session::getError('WorkStart_review')) has-error @endif mt-4' id='startdatepicker' data-date='{{ $WorkStart_review }}' data-date-format='dd-mm-yyyy'>
                                                    <label for='WorkStart_review' class='col-12 control-label-notes mb-1'>{{ trans('langReviewStart') }}</label>
                                                    <div class='col-12'>
                                                        <div class='input-group'>
                                                           <span class='input-group-addon'>
                                                                <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                     <input class='mt-0' type='checkbox' id='enableWorkStart_review' name='enableWorkStart_review' value='1' @if ($enableWorkStart_review) checked @endif>
                                                                     <span class='checkmark'></span>
                                                                </label>
                                                           </span>
                                                            <span class='add-on1 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                            <input class='form-control mt-0 border-start-0' name='WorkStart_review' id='WorkStart_review' type='text' value='{{ $WorkStart_review }}' @if (!$enableWorkStart_review) disabled @endif>
                                                        </div>
                                                        <span class='help-block'>
                                                            @if (Session::hasError('WorkStart_review'))
                                                                {{ Session::getError('WorkStart_review') }}
                                                            @else
                                                                <i class='fa fa-share fa-rotate-270'></i> {{ trans('langReviewDateHelpBlock') }})
                                                            @endif
                                                        </span>
                                                        &nbsp
                                                    </div>
                                                </div>

                                                <div class='row input-append date @if (Session::getError('WorkEnd_review')) has-error @endif mt-4' id='enddatepicker' data-date='{{ $WorkEnd_review }}' data-date-format='dd-mm-yyyy'>
                                                    <label for='WorkEnd_review' class='col-12 control-label-notes mb-1'>{{ trans('langReviewEnd') }}</label>
                                                    <div class='col-12'>
                                                        <div class='input-group'>
                                                           <span class='input-group-addon'>
                                                           <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                             <input class='mt-0' type='checkbox' id='enableWorkEnd_review' name='enableWorkEnd_review' value='1' @if ($enableWorkEnd_review) checked @endif>
                                                             <span class='checkmark'></span></label>
                                                             </span>
                                                            <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                            <input class='form-control mt-0 border-start-0' name='WorkEnd_review' id='WorkEnd_review' type='text' value='{{ $WorkEnd_review }}' @if (!$enableWorkEnd_review) disabled @endif>
                                                        </div>
                                                        <span class='help-block'>
                                                            @if (Session::hasError('WorkEnd_review'))
                                                                {{ Session::getError('WorkEnd_review') }}
                                                            @else
                                                                <i class='fa fa-share fa-rotate-270'></i> {{ trans('langAssignmentEndHelpBlock') }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='row form-group mt-4'>
                                                <div class='col-12 control-label-notes mb-1'>
                                                    {{ trans('langWorkSubType') }}
                                                </div>
                                                <div class='col-12'>
                                                    <div class='radio'>
                                                        <label>
                                                            <input aria-label='{{ trans('langWorkFile') }}' type='radio' id='file_button' name='submission_type' value='0' @if ($submission_type == 0) checked @endif >
                                                            {{ trans('langWorkFile') }}
                                                        </label>
                                                    </div>
                                                    <div class='radio'>
                                                        <label class='radio'>
                                                            <input aria-label='{{ trans('langWorkMultipleFiles') }}' type='radio' id='file_button' name='submission_type' value='2' @if ($submission_type == 2) checked @endif>
                                                            <div class='me-2'>
                                                                {{ trans('langWorkMultipleFiles') }}
                                                            </div>
                                                            <div>
                                                                {!! selection(fileCountOptions(), 'fileCount', $fileCount) !!}
                                                            </div>
                                                        </label>
                                                    </div>
                                                    <div class='radio'>
                                                        <label>
                                                            <input aria-label='{{ trans('langWorkOnlineText') }}' type='radio' id='online_button' name='submission_type' value='1' @if ($submission_type == 1) checked @endif>
                                                            {{ trans('langWorkOnlineText') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='row input-append date form-group @if (Session::getError('WorkStart')) has-error @endif mt-4' id='startdatepicker' data-date='{{ $WorkStart }}' data-date-format='dd-mm-yyyy'>
                                                <label for='WorkStart' class='col-12 control-label-notes mb-1'>{{ trans('langStartDate') }}</label>
                                                <div class='col-12'>
                                                    <div class='input-group'>
                                                       <span class='input-group-addon'>
                                                           <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                             <input class='mt-0' type='checkbox' id='enableWorkStart' name='enableWorkStart' value='1' @if ($enableWorkStart) checked @endif>
                                                             <span class='checkmark'></span></label>
                                                       </span>
                                                        <span class='add-on1 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                        <input class='form-control mt-0 border-start-0' name='WorkStart' id='WorkStart' type='text' value='{{ $WorkStart }}' @if (!$enableWorkStart) disabled @endif>
                                                    </div>
                                                    <span class='help-block'>
                                                        @if (Session::hasError('WorkStart'))
                                                            {{ Session::getError('WorkStart') }}
                                                        @else
                                                            &nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> {{ trans('langAssignmentStartHelpBlock') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            <div class='row input-append date form-group @if (Session::getError('WorkEnd')) has-error @endif mt-4' id='enddatepicker' data-date='{{ $WorkEnd }}' data-date-format='dd-mm-yyyy'>
                                            <label for='WorkEnd' class='col-12 control-label-notes mb-1'>{{ trans('langGroupWorkDeadline_of_Submission') }}</label>
                                                <div class='col-12'>
                                                    <div class='input-group'>
                                                       <span class='input-group-addon'>
                                                            <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                <input class='mt-0' type='checkbox' id='enableWorkEnd' name='enableWorkEnd' value='1' @if ($enableWorkEnd) checked @endif>
                                                            <span class='checkmark'></span></label>
                                                        </span>
                                                        <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                        <input class='form-control mt-0 border-start-0' name='WorkEnd' id='WorkEnd' type='text' value='{{ $WorkEnd }}' @if (!$enableWorkEnd) disabled @endif>
                                                    </div>
                                                    <span class='help-block'>
                                                        @if (Session::hasError('WorkEnd'))
                                                            {{ Session::getError('WorkEnd') }}
                                                        @else
                                                            &nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> {{ trans('langAssignmentEndHelpBlock') }}
                                                       @endif
                                                    </span>
                                                </div>
                                            </div>

                                            @include('modules.work.lti_app_feedback_release_date')

                                            <div class='mt-4 form-group @if (!$WorkEnd) hide @endif mt-4' id='late_sub_row'>
                                                <div class='col-12'>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                            <input type='checkbox' id='late_submission' name='late_submission' value='1' @if ($late_submission) checked @endif @if ($grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) disabled @endif>
                                                            <span class='checkmark'></span>
                                                            {{ trans('langLateSubmissionEnable') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <div class='col-12'>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                            <input type='checkbox' name='notify_submission' value='1' @if ($notification) checked @endif>
                                                            <span class='checkmark'></span>
                                                            {{ trans('langNotifyAssignmentSubmission') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='row form-group mt-4'>
                                                <div class='col-12 control-label-notes mb-1'>
                                                    {{ trans('langAssignmentType') }}
                                                </div>
                                                <div class='col-12'>
                                                    <div class='radio'>
                                                        <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                            <input type='radio' id='user_button' name='group_submissions' value='0' @if ($group_submissions == 0 or !isset($assignment_id)) checked @endif>
                                                            {{ trans('langUserAssignment') }}
                                                        </label>
                                                    </div>
                                                    <div class='radio'>
                                                        <label>
                                                            <input type='radio' id='group_button' name='group_submissions' value='1' @if ($group_submissions == 1) checked @endif @if ($assignment_type == ASSIGNMENT_TYPE_TURNITIN) disabled @endif>
                                                            {{ trans('langGroupAssignment') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            @if ($group_submissions)
                                                <div class='row form-group mt-4'>
                                                    <div class='col-12 control-label-notes mb-1'>
                                                        {{ trans('langWorkAssignTo') }}
                                                    </div>
                                                    <div class='col-12'>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' id='assign_button_all' name='assign_to_specific' value='0' @if ($assign_to_specific == 0) checked @endif>
                                                                <span id='assign_button_all_text'>{{ trans('langWorkToAllGroups') }}</span>
                                                            </label>
                                                        </div>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' id='assign_button_some' name='assign_to_specific' value='2' @if ($assign_to_specific == 2) checked @endif>
                                                                <span id='assign_button_some_text'>{{ trans('langWorkToGroup') }}</span>
                                                            </label>
                                                        </div>
                                                        <div class='radio d-none' id='assign_group_div'>
                                                            <label>
                                                                <input type='radio' id='assign_button_group' name='assign_to_specific' value='2'>
                                                                <span id='assign_button_group_text'>{{ trans('langWorkToGroup') }}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class='row form-group mt-4'>
                                                    <div class='col-12 control-label-notes mb-1'>
                                                        {{ trans('langWorkAssignTo') }}
                                                    </div>
                                                    <div class='col-12'>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' id='assign_button_all' name='assign_to_specific' value='0' @if ($assign_to_specific == 0 or !isset($assignment_id)) checked @endif>
                                                                <span id='assign_button_all_text'>{{ trans('langWorkToAllUsers') }}</span>
                                                            </label>
                                                        </div>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' id='assign_button_some' name='assign_to_specific' value='1' @if ($assign_to_specific == 1) checked @endif>
                                                                <span id='assign_button_some_text'>{{ trans('langWorkToUser') }}</span>
                                                            </label>
                                                        </div>
                                                        <div class='radio' id='assign_group_div'>
                                                            <label>
                                                                <input type='radio' id='assign_button_group' name='assign_to_specific' value='2' @if ($assign_to_specific == 2) checked @endif>
                                                                <span id='assign_button_group_text'>{{ trans('langWorkToGroup') }}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class='form-group mt-4'>
                                                <div class='col-12 md-3'>
                                                    <div class='table-responsive'>
                                                        <table id='assignees_tbl' class='table-default @if ($assign_to_specific == 0 or !isset($assignment_id)) hide @endif'>
                                                            <thead>
                                                                <tr class='title1 list-header'>
                                                                    <td id='assignees' class='form-label'>{{ trans('langStudents') }}</td>
                                                                    <td class='text-center form-label'>{{ trans('langMove') }}</td>
                                                                    <td class='form-label'>{{ trans('langWorkAssignTo') }}</td>
                                                                </tr>
                                                            </thead>
                                                            <tr>
                                                                <td>
                                                                    <select aria-label='{{ trans('langStudents') }}' class='form-select h-100 rounded-0' id='assign_box' size='10' multiple>
                                                                        {!! $unassigned_options !!}
                                                                    </select>
                                                                </td>
                                                                <td class='text-center'>
                                                                    <input class='btn btn-outline-primary btn-sm rounded-2 h-40px'type='button' onClick="move('assign_box','assignee_box')" value='   &gt;&gt;   ' />
                                                                    <br />
                                                                    <input class='mt-2 btn btn-outline-primary btn-sm h-40px rounded-2' type='button' onClick="move('assignee_box','assign_box')" value='   &lt;&lt;   ' />
                                                                </td>
                                                                <td style='width: 40%;'>
                                                                    <select aria-label='{{ trans('langWorkAssignTo') }}' class='form-select h-100 rounded-0' id='assignee_box' name='ingroup[]' size='10' multiple>
                                                                        {!! $assignee_options !!}
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Auto Judge Options --}}
                                            @if ($autojudge_enabled)
                                                <div class='row form-group mt-4'>
                                                    <div class='col-12 control-label-notes -1'>
                                                        {{ trans('langAutoJudgeEnable') }}
                                                    </div>
                                                    <div class='col-12'>
                                                        <div class='radio'>
                                                            <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                <input type='checkbox' id='auto_judge' name='auto_judge' value='1' @if ($auto_judge) checked @endif>
                                                                <span class='checkmark'></span>
                                                            </label>
                                                        </div>
                                                        <div class='table-responsive'>
                                                            <table style='display: none;'>
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ trans('langAutoJudgeInput') }}</th>
                                                                        <th>{{ trans('langOperator') }}</th>
                                                                        <th>{{ trans('langAutoJudgeExpectedOutput') }}</th>
                                                                        <th>{{ trans('langAutoJudgeWeight') }}</th>
                                                                        <th>&nbsp;</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @if ($auto_judge_scenarios != null)
                                                                        @foreach ($auto_judge_scenarios as $aajudge)
                                                                            <tr>
                                                                                <td>
                                                                                    <input type='text' value='{{ $aajudge['input'] }}' name='auto_judge_scenarios[{{ $loop->index }}][input]' @if (!$autojudge->supportsInput()) readonly placeholder='{{ trans('langAutoJudgeInputNotSupported') }}' @endif>
                                                                                </td>
                                                                                <td>
                                                                                    <select name='auto_judge_scenarios[$rows][assertion]' class='auto_judge_assertion' aria-label='$langSelect'>
                                                                                        <option value='eq' @if ($aajudge['assertion'] === 'eq') selected @endif {{ trans('langAutoJudgeAssertions["eq"]') }} </option>
                                                                                        <option value='same' @if ($aajudge['assertion'] === 'same') selected @endif {{ trans('langAutoJudgeAssertions["same"]') }}</option>
                                                                                        <option value='notEq' @if ($aajudge['assertion'] === 'notEq') selected @endif {{ trans('langAutoJudgeAssertions["notEq"]') }}</option>
                                                                                        <option value='notSame' @if ($aajudge['assertion'] === 'notSame') selected @endif {{ trans('langAutoJudgeAssertions["notSame"]') }}</option>
                                                                                        <option value='integer' @if ($aajudge['assertion'] === 'integer') selected @endif {{ trans('langAutoJudgeAssertions["integer"]') }}</option>
                                                                                        <option value='float' @if ($aajudge['assertion'] === 'float') selected @endif {{ trans('langAutoJudgeAssertions["float"]') }}</option>
                                                                                        <option value='digit' @if ($aajudge['assertion'] === 'digit') selected @endif {{ trans('langAutoJudgeAssertions["digit"]') }}</option>
                                                                                        <option value='boolean' @if ($aajudge['assertion'] === 'boolean') selected @endif {{ trans('langAutoJudgeAssertions["boolean"]') }}</option>
                                                                                        <option value='notEmpty' @if ($aajudge['assertion'] === 'notEmpty') selected @endif {{ trans('langAutoJudgeAssertions["notEmpty"]') }}</option>
                                                                                        <option value='notNull' @if ($aajudge['assertion'] === 'notNull') selected @endif {{ trans('langAutoJudgeAssertions["notNull"]') }}</option>
                                                                                        <option value='string' @if ($aajudge['assertion'] === 'string') selected @endif {{ trans('langAutoJudgeAssertions["string"]') }}</option>
                                                                                        <option value='startsWith' @if ($aajudge['assertion'] === 'startsWith') selected @endif {{ trans('langAutoJudgeAssertions["startsWith"]') }}</option>
                                                                                        <option value='endsWith' @if ($aajudge['assertion'] === 'endsWith') selected @endif {{ trans('langAutoJudgeAssertions["endsWith"]') }}</option>
                                                                                        <option value='contains' @if ($aajudge['assertion'] === 'contains') selected @endif {{ trans('langAutoJudgeAssertions["contains"]') }}</option>
                                                                                        <option value='numeric' @if ($aajudge['assertion'] === 'numeric') selected @endif {{ trans('langAutoJudgeAssertions["numeric"]') }}</option>
                                                                                        <option value='isArray' @if ($aajudge['assertion'] === 'isArray') selected @endif {{ trans('langAutoJudgeAssertions["isArray"]') }}</option>
                                                                                        <option value='true' @if ($aajudge['assertion'] === 'true') selected @endif {{ trans('langAutoJudgeAssertions["true"]') }}</option>
                                                                                        <option value='false' @if ($aajudge['assertion'] === 'false') selected @endif {{ trans('langAutoJudgeAssertions["false"]') }}</option>
                                                                                        <option value='isJsonString' @if ($aajudge['assertion'] === 'isJsonString') selected @endif {{ trans('langAutoJudgeAssertions["isJsonString"]') }}</option>
                                                                                        <option value='isObject' @if ($aajudge['assertion'] === 'isObject') selected @endif {{ trans('langAutoJudgeAssertions["isObject"]') }}</option>
                                                                                    </select>
                                                                                </td>

                                                                                @if (isset($aajudge['output']))
                                                                                    <td>
                                                                                        <input type='text' value='{{ $aajudge['output'] }}' name='auto_judge_scenarios[{{ $loop->index }}][output]' class='auto_judge_output'>
                                                                                    </td>
                                                                                @else
                                                                                    <td>
                                                                                        <input type='text' value='' name='auto_judge_scenarios[{{ $loop->index }}][output]' disabled='disabled' class='auto_judge_output'>
                                                                                    </td>
                                                                                @endif

                                                                                <td>
                                                                                    <input type='text' value='{{ $aajudge['weight'] }}' name='auto_judge_scenarios[{{ $loop->index }}][weight]' class='auto_judge_weight'>
                                                                                </td>
                                                                                <td>
                                                                                    <a href='#' aria-label='{{ trans('langDelete') }}' class='autojudge_remove_scenario'
                                                                                        @if ($loop->index <= 0)
                                                                                            "style='display: none;'"
                                                                                        @else
                                                                                            "style='display: visible;'"
                                                                                        @endif>
                                                                                        <span class='fa fa-fw fa-xmark text-danger' data-bs-title='{{ trans('langDelete') }}' data-bs-toggle='tooltip'></span>
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @else
                                                                        <tr>
                                                                            <td><input type='text' name='auto_judge_scenarios[0][input]' @if(!$autojudge->supportsInput()) readonly='readonly' placeholder='{{ trans('langAutoJudgeInputNotSupported') }}' @endif></td>
                                                                            <td>
                                                                                <select name='auto_judge_scenarios[0][assertion]' class='auto_judge_assertion' aria-label='{{ trans('langSelect') }}'>
                                                                                    <option value='eq' selected='selected'> {{ trans('langAutoJudgeAssertions["eq"]') }} </option>
                                                                                    <option value='same'> {{ trans('langAutoJudgeAssertions["same"]') }}</option>
                                                                                    <option value='notEq'>{{ trans('langAutoJudgeAssertions["notEq"]') }}</option>
                                                                                    <option value='notSame'>{{ trans('langAutoJudgeAssertions["notSame"]') }}</option>
                                                                                    <option value='integer'>{{ trans('langAutoJudgeAssertions["integer"]') }}</option>
                                                                                    <option value='float'>{{ trans('langAutoJudgeAssertions["float"]') }}</option>
                                                                                    <option value='digit'>{{ trans('langAutoJudgeAssertions["digit"]') }}</option>
                                                                                    <option value='boolean'>{{ trans('langAutoJudgeAssertions["boolean"]') }}</option>
                                                                                    <option value='notEmpty'>{{ trans('langAutoJudgeAssertions["notEmpty"]') }}</option>
                                                                                    <option value='notNull'>{{ trans('langAutoJudgeAssertions["notNull"]') }}</option>
                                                                                    <option value='string'>{{ trans('langAutoJudgeAssertions["string"]') }}</option>
                                                                                    <option value='startsWith'>{{ trans('langAutoJudgeAssertions["startsWith"]') }}</option>
                                                                                    <option value='endsWith'>{{ trans('langAutoJudgeAssertions["endsWith"]') }}</option>
                                                                                    <option value='contains'>{{ trans('langAutoJudgeAssertions["contains"]') }}</option>
                                                                                    <option value='numeric'>{{ trans('langAutoJudgeAssertions["numeric"]') }}</option>
                                                                                    <option value='isArray'>{{ trans('langAutoJudgeAssertions["isArray"]') }}</option>
                                                                                    <option value='true'>{{ trans('langAutoJudgeAssertions["true"]') }}</option>
                                                                                    <option value='false'>{{ trans('langAutoJudgeAssertions["false"]') }}</option>
                                                                                    <option value='isJsonString'>{{ trans('langAutoJudgeAssertions["isJsonString"]') }}</option>
                                                                                    <option value='isObject'>{{ trans('langAutoJudgeAssertions["isObject"]') }}</option>
                                                                                </select>
                                                                            </td>
                                                                            <td><input type='text' name='auto_judge_scenarios[0][output]' class='auto_judge_output'></td>
                                                                            <td><input type='text' name='auto_judge_scenarios[0][weight]' class='auto_judge_weight'></td>
                                                                            <td>
                                                                                <a href='#' class='autojudge_remove_scenario' style='display: none;' aria-label='{{ trans('langDelete') }}'>
                                                                                    <span class='fa fa-fw fa-xmark text-danger' data-bs-title='{{ trans('langDelete') }}' data-bs-toggle='tooltip'></span>
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan='5' style='text-align: right;'> {{ trans('langAutoJudgeSum') }}: <span id='weights-sum'>0</span></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan='5' style='text-align: left;'><input type='submit' value='{{ trans('langAutoJudgeNewScenario') }}' id='autojudge_new_scenario' /></td>
                                                                        </tr>
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='row form-group mt-4'>
                                                    <div class='col-12 control-label-notes mb-1'>
                                                        {{ trans('langAutoJudgeProgrammingLanguage') }}
                                                    </div>
                                                    <div class='col-12'>
                                                        {!! $autojudge_supported_languages !!}
                                                    </div>
                                                </div>
                                            @endif

                                            <div class='row form-group mt-4'>
                                                <label for='assignmentPasswordLock' class='col-12 control-label-notes mb-1'>{{ trans('langPassCode') }}</label>
                                                <div class='col-12'>
                                                    <input name='assignmentPasswordLock' type='text' class='form-control' id='assignmentPasswordLock' value='{{ $assignmentPasswordLock }}'>
                                                </div>
                                            </div>

                                            <div class='row form-group  @if (Session::getError('assignmentIPLock')) has-error @endif mt-4'>
                                                <label for='assignmentIPLock' class='col-12 control-label-notes mb-1'>{{ trans('langIPUnlock') }}</label>
                                                <div class='col-12'>
                                                    <select name='assignmentIPLock[]' class='form-select' id='assignmentIPLock' multiple>
                                                        {!! $assignmentIPLockOptions !!}
                                                    </select>
                                                </div>
                                            </div>
                                            {!! $tagsAssignment !!}

                                            <div class='form-group mt-5'>
                                                <div class='col-12 d-flex justify-content-end align-items-center'>
                                                    {!!
                                                    form_buttons(array(
                                                                    array(
                                                                        'class'         => 'submitAdminBtn',
                                                                        'name'          => "$submit_name",
                                                                        'value'         => trans('langSubmit'),
                                                                        'javascript'    => "selectAll('assignee_box',true)"
                                                                    ),
                                                                    array(
                                                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                                                                        'class' => 'cancelAdminBtn ms-1'
                                                                    )
                                                                ))
                                                     !!}
                                                </div>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>

                            <div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='{{ trans('langImgFormsDes') }}'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('modules.work.lti_app_js_functions')
    <script type='text/javascript'>
        $(function() {
            $('#scales').select2({ width: '100%' });
            $('#rubrics').select2({ width: '100%' });
            $('#reviews').select2({ width: '100%' });
            $('input[name=grading_type]').on('change', function(e){
                let choice = $(this).val();
                if (choice == 0) {
                    $('#max_grade')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                } else if (choice == 1) {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                } else if (choice == 2) {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#rubrics')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#reviews')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                }
                else  {
                    $('#max_grade')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#scales')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#rubrics')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#reviews')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#reviews_per_user')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                }
            });
            $('input[name=assignment_type]').on('change', function(e) {
                let choice = $(this).val();
                if (choice == 0) {
                    hideLtiAllFields();
                    hideLti13Fields();

                    // user groups
                    $('#group_button')
                        .prop('disabled', false);

                    // grading type
                    $('#scales_button')
                        .prop('disabled', false);
                    $('#rubrics_button')
                        .prop('disabled', false);
                    $('#reviews_button')
                        .prop('disabled', false);

                    // submission type
                    $('#file_button')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#online_button')
                        .prop('disabled', false);

                } else if (choice == 1) {
                    showLtiAllFields();

                    // user groups
                    $('#user_button')
                        .prop('checked', true)
                        .trigger('click')
                        .trigger('change');
                    $('#group_button')
                        .prop('disabled', true);

                    // grading type
                    $('#numbers_button')
                        .prop('checked', true)
                        .trigger('click')
                        .trigger('change');
                    $('#scales_button')
                        .prop('disabled', true);
                    $('#rubrics_button')
                        .prop('disabled', true);
                    $('#reviews_button')
                        .prop('disabled',true);

                    // submission type
                    $('#file_button')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#online_button')
                        .prop('disabled', true);

                    // dates
                    $('#enableWorkStart').trigger('click');
                    $('#enableWorkEnd').trigger('click');
                    $('#enableWorkFeedbackRelease').trigger('click');
                    $('#WorkEnd').val('{{ $tii_fwddate }}');
                    $('#tii_feedbackreleasedate').val('{{ $tii_fwddate }}');
                    $('#enableWorkStart_review').trigger('click');
                    $('#enableWorkEnd_review').trigger('click');

                    // check for select content
                    checkLtiSelectContentRequired();
                }
            });
            $('#WorkEnd, #WorkStart,#WorkStart_review, #WorkEnd_review,#tii_feedbackreleasedate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                autoclose: true
            });
            $('#enableWorkEnd, #enableWorkStart').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#'+dateType).prop('disabled', false);
                    $('#late_sub_row').removeClass('hide');

                } else {
                    $('input#'+dateType).prop('disabled', true);
                    $('#late_sub_row').addClass('hide');
                }
            });
            $('#enableWorkFeedbackRelease').change(function() {
                if($(this).prop('checked')) {
                    $('input#tii_feedbackreleasedate').prop('disabled', false);
                    $('#late_sub_row').removeClass('hide');
                } else {
                    $('input#tii_feedbackreleasedate').prop('disabled', true);
                }
            });
            $('#enableWorkEnd_review, #enableWorkStart_review').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#'+dateType).prop('disabled', false);
                } else {
                    $('input#'+dateType).prop('disabled', true);
                }
            });

            $('input[name=grading_type]').on('change', function(e){
                var choice = $(this).val();
                if (choice == 3 ) {
                    $('#late_submission').prop('disabled', true)
                } else {
                    $('#late_submission').prop('disabled', false)
                }
            });

            $('#tii_use_small_exclusion').change(function() {
                if($(this).prop('checked')) {
                    $('#tii_exclude_type_words')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_exclude_type_percentage')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                    $('#tii_exclude_value')
                        .prop('disabled', false)
                        .closest('div.form-group')
                        .removeClass('hidden');
                } else {
                    $('#tii_exclude_type_words')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_exclude_type_percentage')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                    $('#tii_exclude_value')
                        .prop('disabled', true)
                        .closest('div.form-group')
                        .addClass('hidden');
                }
            });
            $('#assignmentIPLock').select2({
                minimumResultsForSearch: Infinity,
                tags: true,
                tokenSeparators: [',', ' '],
                width: '100%'
            });

            $('input[name=group_submissions]').click(changeAssignLabel);
            $('input[id=assign_button_some]').click(ajaxAssignees);
            $('input[id=assign_button_group]').click(ajaxAssignees);
            $('input[id=assign_button_all]').click(hideAssignees);

            let selectContentModalEl = document.getElementById('SelectContentModal')
            selectContentModalEl.addEventListener('show.bs.modal', function (event) {
                setSelectContentFrameHtml('{{ trans('langPleaseWait') }}');
            });
            selectContentModalEl.addEventListener('shown.bs.modal', function (event) {
                let selectedTemplate = Number($('#lti_templates').find(':selected').val());
                $.ajax({
                    method: 'POST',
                    url: '{{ $urlAppend }}modules/lti/contentitem.php',
                    data: { id: selectedTemplate, course: {!! $course_id !!}, resourcetype: '{!! RESOURCE_LINK_TYPE_ASSIGNMENT !!}'  }
                })
                .done(function(data) {
                    setSelectContentFrameHtml(data);
                });
            });
            //selectContentModalEl.addEventListener('hidden.bs.modal', function (event) {
            //
            //});
            $('#lti_templates').on('change', function(e) {
                checkLtiSelectContentRequired();
            });
            @if (isset($assignment_id))
            checkLtiSelectContentRequired();
            @endif

        });

        function hideAssignees()
        {
            $('#assignees_tbl').addClass('hide');
            $('#assignee_box').find('option').remove();
        }
        function changeAssignLabel()
        {
            var assign_to_specific = $('input:radio[name=assign_to_specific]:checked').val();
            if ((assign_to_specific===1) || (assign_to_specific===2)) {
                ajaxAssignees();
            }
            if (this.id=='group_button') {
                $('#assign_button_all_text').text('{{ trans('langWorkToAllGroups') }}');
                $('#assign_button_some').val('2');
                $('#assign_button_some_text').text('{{ trans('langWorkToGroup') }}');
                $('#assignees').text('{{ trans('langGroups') }}');
                $('#assign_group_div').hide();
            } else {
                $('#assign_button_all_text').text('{{ trans('langWorkToAllUsers') }}');
                $('#assign_button_some').val('1');
                $('#assign_button_some_text').text('{{ trans('langWorkToUser') }}');
                $('#assign_button_group_text').text('{{ trans('langWorkToGroup') }}');
                $('#assignees').text('{{ trans('langStudents') }}');
                $('#assign_group_div').removeClass('d-none').show();
            }
        }

        function ajaxAssignees()
        {
            $('#assignees_tbl').removeClass('hide');
            var type = $('input:radio[name=group_submissions]:checked').val();
            var g_type = $('input:radio[name=assign_to_specific]:checked').val();
            $.post('{{ $urlAppend }}modules/work/index.php?course={{ $course_code }}',
                {
                    assign_type: type,
                    assign_g_type: g_type,
                },
                function(data,status) {
                    var index;
                    var parsed_data = JSON.parse(data);
                    var select_content = '';
                    if (type == 0) {
                        if (g_type == 1) {
                            for (index = 0; index < parsed_data.length; ++index) {
                                select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + q(parsed_data[index]['surname'] + ' ' + parsed_data[index]['givenname']) + '<\/option>';
                            }
                        } else if (g_type == 2) {
                            for (index = 0; index < parsed_data.length; ++index) {
                                select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + q(parsed_data[index]['name']) + '<\/option>';
                            }
                        }
                    } else {
                        for (index = 0; index < parsed_data.length; ++index) {
                            select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + q(parsed_data[index]['name']) + '<\/option>';
                        }
                    }
                    $('#assignee_box').find('option').remove();
                    $('#assign_box').find('option').remove().end().append(select_content);
                });
        }
    </script>

    @if ($autojudge->isEnabled())
        <script type='text/javascript'>

            $(document).ready(function() {
                updateWeightsSum();
                $('.auto_judge_weight').change(updateWeightsSum);
                $('#max_grade').change(updateWeightsSum);
                changeAutojudgeScenariosVisibility.apply($('input[name=auto_judge]'));
            });

            function check_weights() {
                /* function to check weight validity */
                if($('#hidden-opt').is(':visible') && $('#auto_judge').is(':checked')) {
                    var weights = document.getElementsByClassName('auto_judge_weight');
                    var weight_sum = 0;
                    var max_grade = parseFloat(document.getElementById('max_grade').value);
                    max_grade = Math.round(max_grade * 1000) / 1000;

                    for (i = 0; i < weights.length; i++) {
                        // match ints or floats
                        w = weights[i].value.match(/^\d+\.\d+$|^\d+$/);
                        if(w != null) {
                            w = parseFloat(w);
                            if(w >= 0  && w <= max_grade)  // 0->max_grade allowed
                            {
                                /* allow 3 decimal digits */
                                weight_sum += w;
                                continue;
                            }
                            else{
                                alert('Weights must be between 1 and max_grade!');
                                return false;
                            }
                        }
                        else {
                            alert('Only numbers as weights!');
                            return false;
                        }
                    }
                    diff = Math.round((max_grade - weight_sum) * 1000) / 1000;
                    if (diff >= 0 && diff <= 0.001) {
                        return true;
                    }
                    else {
                        alert('Weights do not sum up to ' + max_grade +
                            '!\\n(Remember, 3 decimal digits precision)');
                        return false;
                    }
                }
                else
                    return true;
            }
            function updateWeightsSum() {
                var weights = document.getElementsByClassName('auto_judge_weight');
                var weight_sum = 0;
                var max_grade = parseFloat(document.getElementById('max_grade').value);
                max_grade = Math.round(max_grade * 1000) / 1000;

                for (i = 0; i < weights.length; i++) {
                    // match ints or floats
                    w = weights[i].value.match(/^\d+\.\d+$|^\d+$/);
                    if(w != null) {
                        w = parseFloat(w);
                        if(w >= 0  && w <= max_grade)  // 0->max_grade allowed
                        {
                            /* allow 3 decimal digits */
                            weight_sum += w;
                            continue;
                        }
                        else{
                            $('#weights-sum').html('-');
                            $('#weights-sum').css('color', 'red');
                            return;
                        }
                    }
                    else {
                        $('#weights-sum').html('-');
                        $('#weights-sum').css('color', 'red');
                        return;
                    }
                }
                $('#weights-sum').html(weight_sum);
                diff = Math.round((max_grade - weight_sum) * 1000) / 1000;
                if (diff >= 0 && diff <= 0.001) {
                    $('#weights-sum').css('color', 'green');
                } else {
                    $('#weights-sum').css('color', 'red');
                }
            }

            $('input[name=auto_judge]').click(changeAutojudgeScenariosVisibility);

            function changeAutojudgeScenariosVisibility() {
                if($(this).is(':checked')) {
                    $(this).parent().parent().find('table').show();
                    $('#lang').parent().parent().show();
                } else {
                    $(this).parent().parent().find('table').hide();
                    $('#lang').parent().parent().hide();
                }
            }
            $('#autojudge_new_scenario').click(function(e) {
                var rows = $(this).parent().parent().parent().find('tr').size()-1;
                // Clone the first line
                var newLine = $(this).parent().parent().parent().find('tr:first').clone();
                // Replace 0 wth the line number
                newLine.html(newLine.html().replace(/auto_judge_scenarios\[0\]/g, 'auto_judge_scenarios['+rows+']'));
                // Initialize the remove event and show the button
                newLine.find('.autojudge_remove_scenario').show();
                newLine.find('.autojudge_remove_scenario').click(removeRow);
                // Clear out any potential content
                newLine.find('input').val('');
                // Insert it just before the final line
                newLine.insertBefore($(this).parent().parent().parent().find('tr:last'));
                // Add the event handler
                newLine.find('.auto_judge_weight').change(updateWeightsSum);
                e.preventDefault();
                return false;
            });
            // Remove row
            function removeRow(e) {
                $(this).parent().parent().remove();
                e.preventDefault();
                return false;
            }
            $('.autojudge_remove_scenario').click(removeRow);

            $(document).on('change', 'select.auto_judge_assertion', function(e) {
                e.preventDefault();
                var value = $(this).val();

                // Change selected attr.
                $(this).find('option').each(function() {
                    if ($(this).attr('selected') == 'selected') {
                        $(this).removeAttr('selected');
                    } else if ($(this).attr('value') == value) {
                        $(this).attr('selected', true);
                    }
                });
                var row       = $(this).parent().parent();
                var tableBody = $(this).parent().parent().parent();
                var indexNum  = row.index() + 1;

                if (value === 'eq' ||
                    value === 'same' ||
                    value === 'notEq' ||
                    value === 'notSame' ||
                    value === 'startsWith' ||
                    value === 'endsWith' ||
                    value === 'contains'
                ) {
                    tableBody.find('tr:nth-child('+indexNum+')').find('input.auto_judge_output').removeAttr('disabled');
                } else {
                    tableBody.find('tr:nth-child('+indexNum+')').find('input.auto_judge_output').val('');
                    tableBody.find('tr:nth-child('+indexNum+')').find('input.auto_judge_output').attr('disabled', 'disabled');
                }
                return false;
            });
        </script>
    @endif






@endsection

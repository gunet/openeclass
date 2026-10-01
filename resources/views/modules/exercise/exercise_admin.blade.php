@extends('layouts.default')

@section('content')

    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            <aside class='aside-sidebar'>@include('layouts.partials.left_menu')</aside>
            <main id="main" class="col-12 main-maincontent col_maincontent_active">
                <div class="row">
                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @include('layouts.partials.show_alert')

                    <div id='operations_container'>
                        {!! $action_bar !!}
                    </div>

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded' style="padding: 0px !important; border: 0px !important; background-color: transparent !important;">
                                <form id="exerciseForm" class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}{{ $form_string }}'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>

                                            <div class="accordion exercise-accordion" id="exerciseReviewAccordion">

                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#reviewBasic"
                                                                aria-expanded="true"
                                                                aria-controls="reviewBasic">
                                                            <span class="accordion-icon">
                                                                <i class="fa-solid fa-file-lines"></i>
                                                            </span>
                                                            <span class="accordion-title">
                                                                <span class="accordion-title-text">
                                                                    {{ trans('langBasicItems') }}
                                                                </span>
                                                                <small class="accordion-subtitle">
                                                                    {{ trans('langBasicItemsInfo') }}
                                                                </small>
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="reviewBasic" class="accordion-collapse collapse show" data-bs-parent="#exerciseReviewAccordion">
                                                        <div class="accordion-body">
                                                            <div class='form-group @if (Session::getError('exerciseTitle')) ? has-error @endif '>
                                                                <label for='exerciseTitle' class='col-12 control-label-notes mb-1'>
                                                                    {{ trans('langExerciseName') }}
                                                                    <span class='asterisk Accent-200-cl'>(*)</span>
                                                                </label>
                                                                <input name='exerciseTitle' type='text' class='form-control' id='exerciseTitle' value='{{ $exerciseTitle }}' placeholder='{{ trans('langExerciseName') }}'>
                                                                <span class='help-block Accent-200-cl'>{{ Session::getError('exerciseTitle') }}</span>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <label for='exerciseDescription' class='col-12 control-label-notes mb-2'>{{ trans('langDescription') }}</label>
                                                                <div class='col-12'>
                                                                    {!! rich_text_editor('exerciseDescription', 4, 30, $exerciseDescription, options: array('id' => 'exerciseDescription')) !!}
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <label class='col-12 control-label-notes mb-1'>{{ trans('langViewShowQuestions') }}</label>
                                                                <div class='col-12'>
                                                                    <select name='exerciseType' class='form-select'>
                                                                        <option value='{{ SINGLE_PAGE_TYPE }}'
                                                                                @if ($exerciseType == SINGLE_PAGE_TYPE) selected @endif>{{ trans('langSimpleExercise') }}</option>
                                                                        <option value='{{ MULTIPLE_PAGE_TYPE }}'
                                                                                @if ($exerciseType == MULTIPLE_PAGE_TYPE) selected @endif>{{ trans('langSequentialExercise') }}</option>
                                                                        <option value='{{ ONE_WAY_TYPE }}'
                                                                                @if ($exerciseType == ONE_WAY_TYPE)?
                                                                                selected @endif> {{ trans('langOneWayExercise') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <label class='col-12 control-label-notes mb-1'>{{ trans('langViewShowAnswers') }}</label>
                                                                <div class='col-12'>
                                                                    <select name='dispresults' class='form-select'>
                                                                        <option value='1'
                                                                                @if ($displayResults == 1) selected @endif >{{ trans('langAnswersDisp') }}</option>
                                                                        <option value='0'
                                                                                @if ($displayResults == 0) selected @endif >{{ trans('langAnswersNotDisp') }}</option>
                                                                        <option value='3'
                                                                                @if ($displayResults == 3) selected @endif>{{ trans('langAnswersDispLastAttempt') }}</option>
                                                                        <option value='4'
                                                                                @if ($displayResults == 4) selected @endif>{{ trans('langAnswersDispEndDate') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#reviewTime"
                                                                aria-expanded="false"
                                                                aria-controls="reviewTime">
                                                            <span class="accordion-icon">
                                                                <i class="fa-regular fa-clock"></i>
                                                            </span>
                                                            <span class="accordion-title">
                                                                <span class="accordion-title-text">
                                                                    {{ trans('langTimeAndConstraint') }}
                                                                </span>
                                                                <small class="accordion-subtitle">
                                                                    {{ trans('langTimeAndConstraintInfo') }}
                                                                </small>
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="reviewTime" class="accordion-collapse collapse" data-bs-parent="#exerciseReviewAccordion">
                                                        <div class="accordion-body">
                                                            <div class='input-append date form-group @if (Session::getError('exerciseStartDate')) has-error @endif'
                                                                id='startdatepicker' data-date='{{ $exerciseStartDate }}'
                                                                data-date-format='dd-mm-yyyy'>
                                                                <label for='exerciseStartDate' class='col-12 control-label-notes mb-1'>{{ trans('langStart') }}</label>
                                                                <div class='col-12'>
                                                                    <div class='input-group'>
                                                                        <span class='input-group-addon'>
                                                                            <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                                <input class='mt-0' type='checkbox' id='enableStartDate'
                                                                                    name='enableStartDate' value='1'
                                                                                    @if ($enableStartDate) checked @endif>
                                                                                <span class='checkmark'></span>
                                                                            </label>
                                                                        </span>
                                                                        <span class='add-on1'>
                                                                            <i class='fa-regular fa-calendar Neutral-600-cl'></i>
                                                                        </span>
                                                                        <input class='form-control mt-0'
                                                                            name='exerciseStartDate' id='exerciseStartDate' type='text'
                                                                            value='{{ $exerciseStartDate }}'
                                                                            @if (!$enableStartDate) disabled @endif>
                                                                    </div>
                                                                    <span class='help-block'>
                                                                        @if (Session::hasError('exerciseStartDate'))
                                                                            {{ Session::getError('exerciseStartDate') }}
                                                                        @else
                                                                            &nbsp;&nbsp;&nbsp;
                                                                        @endif
                                                                        <i class='fa fa-share fa-rotate-270'></i>{{ trans('langExerciseStartHelpBlock') }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class='input-append date form-group @if (Session::getError('exerciseEndDate')) has-error @endif mt-4'
                                                                id='enddatepicker' data-date='{{ $exerciseEndDate }}'
                                                                data-date-format='dd-mm-yyyy'>
                                                                <label for='exerciseEndDate' class='col-12 control-label-notes mb-1'>
                                                                    {{ trans('langFinish') }}
                                                                </label>
                                                                <div class='col-12'>
                                                                    <div class='input-group'>
                                                                        <span class='input-group-addon'>
                                                                            <label class='label-container'
                                                                                aria-label='{{ trans('langSelect') }}'>
                                                                                <input class='mt-0' type='checkbox' id='enableEndDate'
                                                                                        name='enableEndDate' value='1'
                                                                                        @if ($enableEndDate) checked @endif>
                                                                                <span class='checkmark'></span>
                                                                            </label>
                                                                        </span>
                                                                        <span class='add-on2'>
                                                                            <i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                                        <input class='form-control mt-0'
                                                                            name='exerciseEndDate' id='exerciseEndDate' type='text'
                                                                            value='{{ $exerciseEndDate }}'
                                                                            @if (!$enableEndDate) disabled @endif>
                                                                    </div>
                                                                    <span class='help-block'>
                                                                        @if (Session::hasError('exerciseEndDate'))
                                                                            {{ Session::getError('exerciseEndDate') }}
                                                                        @else
                                                                            &nbsp;&nbsp;&nbsp;
                                                                        @endif
                                                                        <i class='fa fa-share fa-rotate-270'></i>{{ trans('langExerciseEndHelpBlock') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class='row form-group @if (Session::getError('exerciseTimeConstraint')) has-error @endif mt-4'>
                                                                <div class='col-12'>
                                                                    <label for='exerciseTimeConstraint'
                                                                        class='col-12 control-label-notes mb-0'>
                                                                        {{ trans('langExerciseConstrain') }}
                                                                        <span class='fa-solid fa-circle-info ps-1'
                                                                            data-bs-toggle='tooltip' data-bs-placement='top'
                                                                            title='{{ trans('langExerciseConstrainExplanation') }}'
                                                                            style='margin-bottom: 10px;'></span>
                                                                    </label>
                                                                    <input type='text' class='form-control'
                                                                        name='exerciseTimeConstraint' id='exerciseTimeConstraint'
                                                                        value='{{ $exerciseTimeConstraint }}'
                                                                        placeholder='{{ trans('langExerciseConstrain') }}'>
                                                                    <span class='help-block'>
                                                                        @if (Session::getError('exerciseTimeConstraint'))
                                                                            {{ Session::getError('exerciseTimeConstraint') }}
                                                                        @else
                                                                            {{ trans('langExerciseConstrainUnit') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#reviewAssessment"
                                                                aria-expanded="false"
                                                                aria-controls="reviewAssessment">
                                                            <span class="accordion-icon">
                                                                <i class="fa-solid fa-chart-column"></i>
                                                            </span>
                                                            <span class="accordion-title">
                                                                <span class="accordion-title-text">
                                                                     {{ trans('langGradingMethos') }}
                                                                </span>

                                                                <small class="accordion-subtitle">
                                                                    {{ trans('langGradingMethodsInfo') }}
                                                                </small>
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="reviewAssessment" class="accordion-collapse collapse" data-bs-parent="#exerciseReviewAccordion">
                                                        <div class="accordion-body">
                                                            <div class='form-group'>
                                                                <label for='exerciseRangeId'
                                                                    class='col-12 control-label-notes mb-1'>{{ trans('langExerciseScaleGrade') }}</label>
                                                                <div class='col-12'>
                                                                    <select name='exerciseRange' class='form-select' id='exerciseRangeId'>
                                                                        <option value='' @if ($exerciseRange == 0) selected @endif>
                                                                            -- {{ trans('langExerciseNoScaleGrade') }} --
                                                                        </option>
                                                                        <option value='10' @if ($exerciseRange == 10) selected @endif>0-10
                                                                        </option>
                                                                        <option value='20' @if ($exerciseRange == 20) selected @endif>0-20
                                                                        </option>
                                                                        <option value='5' @if ($exerciseRange == 5) selected @endif>0-5
                                                                        </option>
                                                                        <option value='100' @if ($exerciseRange == 100) selected @endif>
                                                                            0-100
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <label for='exerciseRangeId' class='col-12 control-label-notes mb-1'>
                                                                    {{ trans('langExerciseCBCalcGradeMethod') }}
                                                                    <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip'
                                                                        data-bs-placement='top'
                                                                        title='{{ trans('langExerciseCBCalcGradeMethodLegend') }}'
                                                                        style='margin-bottom: 10px;'></span>
                                                                </label>
                                                                <div class='col-12'>
                                                                    <div class='checkbox'>
                                                                        <label class='label-container'
                                                                            aria-label='{{ trans('langSelect') }}'>
                                                                            <input type="checkbox" name="exerciseCalcGradeMethod"
                                                                                @if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_CERTAINTY_BASED) checked @endif>
                                                                            <span class='checkmark'></span>
                                                                            {{ trans('langActivate') }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <div class='col-12'>
                                                                    <label for='exerciseTimeConstraint'
                                                                        class='col-12 control-label-notes mb-1'>
                                                                        <strong id='legend_grade_pass'>
                                                                            @if ($exerciseRange == 0)
                                                                                {{ trans('langSuccessPercentage') }}
                                                                            @else
                                                                                {{ trans('langExerciseGradePass') }}
                                                                            @endif
                                                                        </strong>
                                                                        <span class='fa-solid fa-circle-info ps-1'
                                                                            data-bs-toggle='tooltip' data-bs-placement='top'
                                                                            title='{{ trans('langExerciseGradePassLegend') }}'
                                                                            style='margin-bottom: 10px;'></span>
                                                                    </label>
                                                                    <input type='text' class='form-control' name='exerciseGradePass'
                                                                        id='exerciseGradePass' value='{{ $exerciseGradePass }}'
                                                                        size='4' maxlength='4'>
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <label class='col-12 control-label-notes mb-1'>{{ trans('langGradeVisible') }}</label>
                                                                <div class='col-12'>
                                                                    <select name='dispscore' class='form-select'>
                                                                        <option value='1'
                                                                                @if ($displayScore == 1) selected @endif>{{ trans('langScoreDisp') }}</option>
                                                                        <option value='0'
                                                                                @if ($displayScore == 0) selected @endif>{{ trans('langScoreNotDisp') }}</option>
                                                                        <option value='3'
                                                                                @if ($displayScore == 3) selected @endif>{{ trans('langScoreDispLastAttempt') }}</option>
                                                                        <option value='4'
                                                                                @if ($displayScore == 4) selected @endif>{{ trans('langScoreDispEndDate') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>    
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#reviewComments"
                                                                aria-expanded="false"
                                                                aria-controls="reviewComments">
                                                            <span class="accordion-icon">
                                                                <i class="fa-solid fa-comment exercise-card-icon"></i>
                                                            </span>
                                                            <span class="accordion-title">
                                                                <span class="accordion-title-text">
                                                                    {{ trans('langReviewFeedback') }}
                                                                </span>
                                                                <small class="accordion-subtitle">
                                                                    {{ trans('langReviewFeedbackInfo') }}
                                                                </small>
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="reviewComments" class="accordion-collapse collapse" data-bs-parent="#exerciseReviewAccordion">
                                                        <div class="accordion-body">
                                                            <div class='form-group mb-4'>
                                                                <label for='exerciseEndMessage' class='col-12 control-label-notes mb-1'>{{ trans('langEndMessage') }}
                                                                    <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langEndMessageInfo') }}' style='margin-bottom: 10px;'></span>
                                                                </label>
                                                                <div class='col-12'>
                                                                    {!! rich_text_editor('exerciseEndMessage', 4, 30, $exerciseEndMessage, options: array('id' => 'exerciseEndMessage')) !!}
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="feedback-intro alert alert-info d-flex align-items-start gap-3 mb-4" role="note">
                                                                <span class="feedback-intro-icon">
                                                                    <i class="fa-solid fa-chart-line"></i>
                                                                </span>
                                                                <div>
                                                                    <strong>{{ trans('langFeedbackScale') }}</strong>
                                                                    <p class="mb-0">{{ trans('langFeedbackScaleInfo') }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="card panelCard feedback-scale-card border-0 shadow-sm mb-4">
                                                                <div class="card-body">
                                                                    <div id="feedback-scale" class="feedback-scale" aria-live="polite" aria-label="{{ trans('langFeedbackScale') }}"></div>
                                                                </div>
                                                            </div>
                                                            <div id='feedback-container' class='col-12 mt-3'>
                                                                @if (count($exerciseFeedback) > 0)
                                                                    @foreach ($exerciseFeedback as $counter => $feedback)
                                                                        <div class='feedback-row'>
                                                                            <div class="feedback-range">
                                                                                <span class="feedback-range-caption">{{ trans('langDisplayForGrade')}}</span>
                                                                                <strong class="badge Primary-600-bg feedback-range-value">—</strong>
                                                                            </div>
                                                                            <div class='feedback-fields'>
                                                                                <div class='feedback-grade-field'>
                                                                                    <label for='grade_{{ $counter }}' class='form-label'>{{ trans('langFeedbackFromGrade') }}</label>
                                                                                    <input id='grade_{{ $counter }}' class='form-control feedback-grade' type='number' name='feedback_grade[{{ $counter }}]' min='0' step='any' required value="{{ $feedback['grade'] }}">
                                                                                </div>
                                                                                <div class='feedback-text-field'>
                                                                                    <label for='text_{{ $counter }}' class='form-label'>{{ trans('langReviewFeedbackText') }}</label>
                                                                                    <input id='text_{{ $counter }}' class='form-control feedback-text' type='text' name='feedback_text[{{ $counter }}]' maxlength='200' required value="{{ $feedback['feedback_text'] }}">
                                                                                </div>
                                                                            </div>
                                                                            <button type="button" class='btn deleteAdminBtn delete-feedback-btn' aria-label='{{ trans('langDelete') }}'><i class='fa-solid fa-trash-can'></i></button>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                            <div class="feedback-actions">
                                                                <button type='button' class='btn submitAdminBtn' id='add-feedback-btn'>
                                                                    <i class="fa-solid fa-plus"></i> 
                                                                    {{ trans('langAddRange') }}
                                                                </button>
                                                                <span id="feedback-empty" @if (count($exerciseFeedback) > 0) hidden @endif>
                                                                    {{ trans('langNoFeedbackMessagesDefined') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#reviewAccess"
                                                                aria-expanded="false"
                                                                aria-controls="reviewAccess">
                                                            <span class="accordion-icon">
                                                                <i class="fa-solid fa-unlock-keyhole exercise-card-icon"></i>
                                                            </span>
                                                            <span class="accordion-title">
                                                                <span class="accordion-title-text">
                                                                    {{ trans('langReviewAccess') }}
                                                                </span>
                                                                <small class="accordion-subtitle">
                                                                    {{ trans('langReviewAccessInfo') }}
                                                                </small>
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="reviewAccess" class="accordion-collapse collapse" data-bs-parent="#exerciseReviewAccordion">
                                                        <div class="accordion-body">
                                                            <div class='form-group'>
                                                                <div class='control-label-notes mb-1'>{{ trans('langWorkAssignTo') }}</div>
                                                                <div class='col-12'>
                                                                    <div class='radio'>
                                                                        <label>
                                                                            <input type='radio' id='assign_button_all'
                                                                                name='assign_to_specific' value='0'
                                                                                @if ($exerciseAssignToSpecific == 0) checked @endif>
                                                                            {{ trans('langWorkToAllUsers') }}
                                                                        </label>
                                                                    </div>
                                                                    <div class='radio'>
                                                                        <label>
                                                                            <input type='radio' id='assign_button_user'
                                                                                name='assign_to_specific' value='1'
                                                                                @if ($exerciseAssignToSpecific == 1) checked @endif>
                                                                            {{ trans('langWorkToUser') }}
                                                                        </label>
                                                                    </div>
                                                                    <div class='radio'>
                                                                        <label>
                                                                            <input type='radio' id='assign_button_group'
                                                                                name='assign_to_specific' value='2'
                                                                                @if ($exerciseAssignToSpecific == 2) checked @endif>
                                                                            {{ trans('langWorkToGroup') }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <div class='col-12'>
                                                                    <div class='table-responsive mt-0'>
                                                                        <table id='assignees_tbl'
                                                                            class='table-default @unless (in_array($exerciseAssignToSpecific, [1, 2])) hide @endunless'>
                                                                            <thead>
                                                                            <tr class='title1 list-header'>
                                                                                <td class='form-label'
                                                                                    id='assignees'>{{ trans('langStudents') }}</td>
                                                                                <td class='form-label text-center'>{{ trans('langMove') }}</td>
                                                                                <td class='form-label'>{{ trans('langWorkAssignTo') }}</td>
                                                                            </tr>
                                                                            </thead>
                                                                            <tr>
                                                                                <td>
                                                                                    <select aria-label='{{ trans('langStudent') }}'
                                                                                            class='form-select h-100' id='assign_box'
                                                                                            size='10' multiple>
                                                                                        @if (isset($unassigned_options))
                                                                                            {!! $unassigned_options !!}
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <div class='d-flex align-items-center flex-column gap-2'>
                                                                                        <input aria-label='{{ trans('langMove') }}'
                                                                                            class='btn submitAdminBtn submitAdminBtnClassic'
                                                                                            type='button'
                                                                                            onClick="move('assign_box','assignee_box')"
                                                                                            value='   &gt;&gt;   '/>
                                                                                        <input aria-label='{{ trans('langMove') }}'
                                                                                            class='btn submitAdminBtn submitAdminBtnClassic'
                                                                                            type='button'
                                                                                            onClick="move('assignee_box','assign_box')"
                                                                                            value='   &lt;&lt;   '/>
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <select aria-label='{{ trans('langWorkAssignTo') }}'
                                                                                            class='form-select h-100' id='assignee_box'
                                                                                            name='ingroup[]' size='10' multiple>
                                                                                        @if (isset($assignee_options))
                                                                                            {!! $assignee_options !!}
                                                                                        @endif
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mt-4">
                                                                <div class='form-group  @if (Session::getError('exercisePasswordLock')) has-error @endif'>
                                                                    <label for='exercisePasswordLock'
                                                                        class='col-12 control-label-notes mb-1'>{{ trans('langPassCode') }}</label>
                                                                    <div class='col-12'>
                                                                        <input name='exercisePasswordLock' type='text'
                                                                            class='form-control'
                                                                            id='exercisePasswordLock'
                                                                            value='{{ $exercisePasswordLock }}'
                                                                            placeholder=''>
                                                                        <span class='help-block Accent-200-cl'> {{ Session::getError('exercisePasswordLock') }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class='form-group @if (Session::getError('exerciseIPLock')) has-error @endif mt-4'>
                                                                    <label for='exerciseIPLock'
                                                                        class='col-12 control-label-notes mb-1'>{{ trans('langIPUnlock') }}</label>
                                                                    <div class='help-block'>
                                                                        {{ trans('langIPUnlockLegend') }}
                                                                    </div>
                                                                    <div class='col-12'>
                                                                        <select name='exerciseIPLock[]' class='form-control'
                                                                                id='exerciseIPLock' multiple>
                                                                            {!! $exerciseIPLockOptions !!}
                                                                        </select>
                                                                        <span class='help-block Accent-200-cl'>{{ Session::getError('exerciseIPLock') }}</span>
                                                                    </div>
                                                                </div>
                                                                {!! $tags_list !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#reviewAttempt"
                                                                aria-expanded="false"
                                                                aria-controls="reviewAttempt">
                                                            <span class="accordion-icon">
                                                                <i class="fa-solid fa-recycle"></i>
                                                            </span>
                                                            <span class="accordion-title">
                                                                <span class="accordion-title-text">
                                                                    {{ trans('langReviewUserAttemps') }}
                                                                </span>
                                                                <small class="accordion-subtitle">
                                                                    {{ trans('langReviewUserAttempsInfo') }}
                                                                </small>
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="reviewAttempt" class="accordion-collapse collapse" data-bs-parent="#exerciseReviewAccordion">
                                                        <div class="accordion-body">
                                                            <div class='form-group' id='exerciseTempSaveDiv'>
                                                                <div class='col-12 control-label-notes mb-2'>
                                                                    {{ trans('langTemporarySave') }}
                                                                </div>
                                                                <div class='col-12'>
                                                                    <div class='row'>
                                                                        <div class='col-12 radio'>
                                                                            <label>
                                                                                <input type='radio' name='exerciseTempSave' value='0'
                                                                                    @if ($exerciseTempSave == 0) checked @endif>
                                                                                {{ trans('langDeactivate') }}
                                                                            </label>
                                                                        </div>
                                                                        <div class='col-12 radio'>
                                                                            <label>
                                                                                <input type='radio' name='exerciseTempSave' value='1'
                                                                                    @if ($exerciseTempSave == 1) checked @endif>
                                                                                {{ trans('langActivate') }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class='form-group @if (Session::getError('exerciseAttemptsAllowed')) has-error @endif mt-4'>
                                                                <div class='col-12'>
                                                                    <label for='exerciseAttemptsAllowed'
                                                                        class='col-12 control-label-notes mb-1'>
                                                                        {{ trans('langExerciseAttemptsAllowed') }}
                                                                        <span class='fa-solid fa-circle-info ps-1'
                                                                            data-bs-toggle='tooltip' data-bs-placement='top'
                                                                            title='{{ trans('langExerciseAttemptsAllowedExplanation') }}'
                                                                            style='margin-bottom: 10px;'></span>
                                                                    </label>
                                                                    <input type='text' class='form-control'
                                                                        name='exerciseAttemptsAllowed'
                                                                        id='exerciseAttemptsAllowed'
                                                                        value='{{ $exerciseAttemptsAllowed  }}'
                                                                        placeholder='{{ trans('langExerciseConstrain') }}'>
                                                                    <span class='help-block'>
                                                                        @if (Session::getError('exerciseAttemptsAllowed'))
                                                                            {{ Session::getError('exerciseAttemptsAllowed') }}
                                                                        @else
                                                                            {{ trans('langExerciseAttemptsAllowedUnit') }}
                                                                        @endif
                                                                    </span> 
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <div class='col-12 control-label-notes mb-1'>
                                                                    {{ trans('langContinueAttempt') }}
                                                                </div>
                                                                <div class='col-12'>
                                                                    <div class='checkbox'>
                                                                        <label class='label-container'
                                                                            aria-label='{{ trans('langSelect') }}'>
                                                                            <input id='continueAttempt' name='continueAttempt'
                                                                                type='checkbox' @if ($continueTimeLimit) checked @endif>
                                                                            <span class='checkmark'></span>
                                                                            {{ trans('langContinueAttemptExplanation') }}
                                                                        </label>
                                                                    </div>
                                                                    <div id='continueTimeField' class='form-inline'
                                                                        style='margin-top: 15px; @unless ($continueTimeLimit) display: none @endunless'>
                                                                        {!! $continueTimeField !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#reviewSettings"
                                                                aria-expanded="false"
                                                                aria-controls="reviewSettings">
                                                            <span class="accordion-icon">
                                                                <i class="fa-solid fa-gear"></i>
                                                            </span>
                                                            <span class="accordion-title">
                                                                <span class="accordion-title-text">
                                                                   {{ trans('langReviewAdvancedSettings') }}
                                                                </span>
                                                                <small class="accordion-subtitle">
                                                                    {{ trans('langReviewAdvancedSettingsInfo') }}
                                                                </small>
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="reviewSettings" class="accordion-collapse collapse" data-bs-parent="#exerciseReviewAccordion">
                                                        <div class="accordion-body">    
                                                            <div class='form-group'>
                                                                <p class="form-label">{{ trans('langActivateExamMode') }}</p>
                                                                <div class='col-12'>
                                                                    <div class='checkbox'>
                                                                        <label class='label-container'
                                                                            aria-label='{{ trans('langSelect') }}'>
                                                                            <input id='isExam_' name='isExam' type='checkbox'
                                                                                @if ($isExam) checked @endif>
                                                                            <span class='checkmark'></span>
                                                                            {{ trans('langActivateExamMode') }}
                                                                        </label>
                                                                        <div class='help-block'>
                                                                            {{ trans('langRequireCourseUserLogin') }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4 d-none' id='stricter_exam'>
                                                                <div class='col-12'>
                                                                    <div class='checkbox'>
                                                                        <label class='label-container'
                                                                            aria-label='{{ trans('langSelect') }}'>
                                                                            <input name='stricterExamRestriction' type='checkbox'
                                                                                @if($exerciseStricterExamRestriction) checked @endif>
                                                                            <span class='checkmark'></span>
                                                                            {{ trans('langExerciseWillBeCanceledInStrictMode') }}
                                                                        </label>
                                                                        <div class='help-block'>
                                                                            {{ trans('langStrictModeExceptForQtypes') }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if (CourseHasSafeExamBrowserEnabled())
                                                                    <div class='col-12 mt-3'>
                                                                        <div class='checkbox'>
                                                                            <label class='label-container'
                                                                                aria-label='{{ trans('langSelect') }}'>
                                                                                <input name='useSafeExamBrowser' type='checkbox'
                                                                                    id='useSafeExamBrowser'
                                                                                    @if($exerciseUseSafeExamBrowser) checked @endif>
                                                                                <span class='checkmark'></span>
                                                                                {{ trans('langSafeExamBrowserInfo') }}
                                                                                <span class='fa-solid fa-circle-info ps-1'
                                                                                    data-bs-toggle='tooltip' data-bs-placement='right'
                                                                                    title='{{ trans('langSafeExamBrowserLegend') }}'
                                                                                    style='margin-top: 5px;'></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <div class='col-sm-12 control-label-notes mb-1'>{{ trans('langExercisePreventCopy') }}</div>
                                                                <div class='col-12'>
                                                                    <div class='checkbox'>
                                                                        <label class='label-container'
                                                                            aria-label='{{ trans('langSelect') }}'>
                                                                            <input id='jsPreventCopy' name='jsPreventCopy' type='checkbox'
                                                                                @if ($exercisePreventCopy) checked @endif>
                                                                            <span class='checkmark'></span>
                                                                            {{ trans('langExercisePreventCopyExplanation') }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class='form-group mt-4'>
                                                                <label for='exerciseRangeId' class='col-12 control-label-notes mb-1'>
                                                                    {{ trans('langMultipleChoiceQuestions') }}
                                                                    <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langShuffleAnswersLegend') }}' style='margin-bottom: 10px;'></span>
                                                                </label>
                                                                <div class='col-12'>
                                                                    <div class='checkbox'>
                                                                        <label class='label-container'
                                                                            aria-label='{{ trans('langSelect') }}'>
                                                                            <input name='shuffle_answers' type='checkbox'
                                                                                @if ($hasShuffleAnswers) checked @endif>
                                                                            <span class='checkmark'></span>
                                                                            {{ trans('langShuffleAnswers') }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                             
                                            <div class='col-12 d-flex justify-content-end align-items-center mt-4'>
                                                {!! $form_buttons !!}
                                            </div>
                                    </fieldset>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{{ get_form_image() }}'
                                 alt='{{ trans('langImgFormsDes') }}'>
                        </div>
                    </div>
            </main>
        </div>
    </div>


    <script type='text/javascript'>
        $(function () {

            $('#exerciseRangeId').change(function () {
                var selectedValue = $(this).val();
                if (selectedValue !== '') {
                    $('#legend_grade_pass').text('{{ trans('langExerciseGradePass') }}');
                } else {
                    $('#legend_grade_pass').text('{{ trans('langSuccessPercentage') }}');
                }
            });

            $('#exerciseStartDate, #exerciseEndDate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                autoclose: true
            }).on('changeDate', function (ev) {
                if ($(this).attr('id') === 'exerciseEndDate') {
                    $('#answersDispEndDate, #scoreDispEndDate').removeClass('hidden');
                }
            }).on('blur', function (ev) {
                if ($(this).attr('id') === 'exerciseEndDate') {
                    var end_date = $(this).val();
                    if (end_date === '') {
                        if ($('input[name=\"dispresults\"]:checked').val() == 4) {
                            $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                        }
                        $('#answersDispEndDate, #scoreDispEndDate').addClass('hidden');
                    }
                }
            });
            $('#enableEndDate, #enableStartDate').change(function () {
                var dateType = $(this).prop('id').replace('enable', '');
                if ($(this).prop('checked')) {
                    $('input#exercise' + dateType).prop('disabled', false);
                    if (dateType === 'EndDate' && $('input#exerciseEndDate').val() !== '') {
                        $('#answersDispEndDate, #scoreDispEndDate').removeClass('hidden');
                    }
                } else {
                    $('input#exercise' + dateType).prop('disabled', true);
                    if ($('input[name=\"dispresults\"]:checked').val() == 4) {
                        $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                    }
                    $('#answersDispEndDate, #scoreDispEndDate').addClass('hidden');
                }
            });
            $('#exerciseAttemptsAllowed').blur(function () {
                var attempts = $(this).val();
                if (attempts == 0) {
                    $('#answersDispLastAttempt, #scoreDispLastAttempt').addClass('hidden');
                    if ($('input[name=\"dispresults\"]:checked').val() == 3) {
                        $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                    }
                } else {
                    $('#answersDispLastAttempt, #scoreDispLastAttempt').removeClass('hidden');
                }
            });

            slimSelectFun(
                '#exerciseIPLock',
                '{{ js_escape(trans('langSearch')) }}',
                '{{ js_escape(trans('langWelcomeSelect')) }}',
                '{{ js_escape(trans('langSelectAll')) }}',
                '{{ js_escape(trans('langListChoices')) }}',
                {
                    tags: true,
                    tokenSeparators: [',', ' '],
                }
            );

            $('#assign_button_all').click(hideAssignees);
            $('#assign_button_user, #assign_button_group').click(ajaxAssignees);
            $('#continueAttempt').change(function () {
                if ($(this).prop('checked')) {
                    $('#continueTimeField').show('fast');
                } else {
                    $('#continueTimeField').hide('fast');
                }
            }).change();

            if ($('#isExam_').is(':checked')) {
                $('#stricter_exam').removeClass('d-none').addClass('d-block');
            } else {
                $('#stricter_exam').removeClass('d-block').addClass('d-none');
            }
            $('#isExam_').on('click', function () {
                if ($(this).is(':checked')) {
                    $('#stricter_exam').removeClass('d-none').addClass('d-block');
                } else {
                    $('#stricter_exam').removeClass('d-block').addClass('d-none');
                }
            });

            var count = $('#feedback-container .feedback-row').length;
            $('#feedback-container').on('input change', '.feedback-grade, .feedback-text', updateFeedbackRanges);
            updateFeedbackRanges();
            $('#add-feedback-btn').click(function (e) {
                e.preventDefault(); 
                count++;
                $('#feedback-container').append(`<div class='feedback-row'>
                                                    <div class="feedback-range">
                                                        <span class="feedback-range-caption">{{ js_escape(trans('langCountsForGrade')) }}</span>
                                                        <strong class="badge Primary-600-bg feedback-range-value">{{ js_escape(trans('langNewLimit')) }}</strong>
                                                    </div>
                                                    <div class='feedback-fields'>
                                                        <div class='feedback-grade-field'>
                                                            <label for='grade_${count}' class='form-label'>{{ js_escape(trans('langFeedbackFromGrade')) }}</label>
                                                            <input id='grade_${count}' class='form-control feedback-grade' type='number' name='feedback_grade[${count}]' min='0' step='any' required>
                                                        </div>
                                                        <div class='feedback-text-field'>
                                                            <label for='text_${count}' class='form-label'>{{ js_escape(trans('langReviewFeedbackText')) }}</label>
                                                            <input id='text_${count}' class='form-control feedback-text' type='text' name='feedback_text[${count}]' maxlength='200' required>
                                                        </div>
                                                    </div>
                                                    <button type="button" class='btn deleteAdminBtn delete-feedback-btn' aria-label='{{ js_escape(trans('langDelete')) }}'><i class='fa-solid fa-trash-can'></i></button>
                                                </div>`);
                updateFeedbackRanges();
            });
            $('#feedback-container').on('click', '.delete-feedback-btn', function () {
                $(this).closest('.feedback-row').remove();
                $('#feedback-container .feedback-row').each(function (index) {
                    var newIndex = index + 1;
                    $(this).find('input[name^="feedback_text"]').attr('name', 'feedback_text[' + newIndex + ']');
                    $(this).find('input[name^="feedback_grade"]').attr('name', 'feedback_grade[' + newIndex + ']');
                });
                updateFeedbackRanges();
            });

            $('#useSafeExamBrowser').change(function () {
                if ($(this).is(':checked')) {
                    $('#exerciseTempSaveDiv').hide();
                } else {
                    $('#exerciseTempSaveDiv').show();
                }
            }).trigger('change');

        });

        function ajaxAssignees() {
            $('#assignees_tbl').removeClass('hide');
            var type = $(this).val();
            $.post('',
                {
                    assign_type: type
                },
                function (data, status) {
                    var index;
                    var parsed_data = JSON.parse(data);
                    var select_content = '';
                    if (type == 1) {
                        for (index = 0; index < parsed_data.length; ++index) {
                            select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + q(parsed_data[index]['surname'] + ' ' + parsed_data[index]['givenname']) + '<\/option>';
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

        function hideAssignees() {
            $('#assignees_tbl').addClass('hide');
            $('#assignee_box').find('option').remove();
        }

        function updateFeedbackRanges() {
            var rows = $('#feedback-container .feedback-row').get().map(function (row) {
                return { 
                    row: row, 
                    grade: parseFloat($(row).find('.feedback-grade').val()), 
                    message: $(row).find('.feedback-text').val() 
                };
            }).filter(function (item) { 
                return !isNaN(item.grade) && item.grade >= 0; 
            }).sort(function (a, b) { 
                return a.grade - b.grade; 
            });

            var $scale = $('#feedback-scale').empty();
            var rangeBelowText = '{{ js_escape(trans('langFeedbackRangeBelow')) }}';
            var rangeAndAboveText = '{{ js_escape(trans('langFeedbackRangeAndAbove')) }}';
            var feedbackMessageText = '{{ js_escape(trans('langFeedbackMessage')) }}';

            rows.forEach(function (item, index) {
                if (index > 0) {
                    var previous = rows[index - 1];
                    $scale.append(segment(previous.message || feedbackMessageText + ' ' + index));
                }
                $scale.append(marker(item.grade, false));
                var next = rows[index + 1];
                var label = next ? item.grade + ' ' + rangeBelowText + ' ' + next.grade : item.grade + ' ' + rangeAndAboveText + ' ';
                $(item.row).find('.feedback-range-value').text(label);
            });
            if (rows.length > 0) {
                var last = rows[rows.length - 1];
                $scale.append(segment(last.message || feedbackMessageText + ' ' + rows.length));
            }
            $('#feedback-empty').prop('hidden', $('#feedback-container .feedback-row').length > 0);
            $('#feedback-scale').toggleClass('feedback-scale-empty', rows.length === 0);
        }

        function marker(value, isStart) {
            var $marker = $('<div>').addClass('feedback-scale-point' + (isStart ? ' feedback-scale-start' : ''));
            $marker.append($('<span>').addClass('feedback-scale-dot'));
            $marker.append($('<strong>').text(value));
            return $marker;
        }

        function segment(message) {
            var $segment = $('<span>').addClass('feedback-scale-line');
            $segment.append($('<span>').addClass('feedback-scale-track'));
            if (message) { 
                $segment.append($('<small>').addClass('feedback-scale-message').text(message));
            }
            return $segment;
        }

    </script>

@endsection

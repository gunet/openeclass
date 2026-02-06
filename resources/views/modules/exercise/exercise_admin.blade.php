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

                        <div class='d-lg-flex gap-4 mt-4'>
                            <div class='flex-grow-1'>
                                <div class='form-wrapper form-edit rounded'>
                                    <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}{{ $form_string }}'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                        <div class='row form-group @if (Session::getError('exerciseTitle')) ? has-error @endif '>
                                                <label for='exerciseTitle' class='col-12 control-label-notes mb-1'>
                                                        {{ trans('langExerciseName') }}
                                                    <span class='asterisk Accent-200-cl'>(*)</span>
                                                </label>
                                                <div class='col-12'>
                                                    <input name='exerciseTitle' type='text' class='form-control' id='exerciseTitle' value='{{  $exerciseTitle }}' placeholder='{{ trans('langExerciseName') }}'>
                                                    <span class='help-block Accent-200-cl'>{{ Session::getError('exerciseTitle') }}</span>
                                                </div>
                                        </div>
                                        <div class='row form-group mt-4'>
                                            <label for='exerciseDescription' class='col-12 control-label-notes mb-1'>{{ trans('langDescription') }}</label>
                                            <div class='col-12'>
                                                {!! rich_text_editor('exerciseDescription', 4, 30, $exerciseDescription) !!}
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <label for='exerciseEndMessage' class='col-12 control-label-notes mb-1'>{{ trans('langEndMessage') }}
                                                <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langEndMessageInfo') }}' style='margin-bottom: 10px;'></span>
                                            </label>
                                            <div class='col-12'>
                                                {!! rich_text_editor('exerciseEndMessage', 4, 30, $exerciseEndMessage) !!}
                                            </div>
                                        </div>

                                        <div class='col-12 d-flex justify-content-start align-items-center gap-3 flex-wrap my-4'>
                                            <button class='btn submitAdminBtn' id='add-feedback-btn'>{{ trans('langAddFeedback') }}</button>
                                        </div>
                                        <div id='feedback-container'>
                                            @if (count($exerciseFeedback) > 0)
                                                @foreach ($exerciseFeedback as $counter => $feedback)
                                                    <div class='feedback-row d-flex align-items-center justify-content-between border-bottom mb-1'>
                                                        <input type='text' name='feedback_text[{{ $counter }}]' size='60' maxlength='200' value="{{ $feedback['feedback_text'] }}">
                                                        <input type='text' name='feedback_grade[{{ $counter }}]' size='4' maxlength='4' value="{{ $feedback['grade'] }}">
                                                        <a class='delete-feedback-btn'><span class='fa-solid fa-xmark' style='color:red;'></span></a>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <label class='col-12 control-label-notes mb-1'>{{ trans('langViewShow') }}</label>
                                            <div class='col-12'>
                                                <select name='exerciseType' class='form-select'>
                                                    <option value='{{ SINGLE_PAGE_TYPE }}' @if ($exerciseType == SINGLE_PAGE_TYPE) selected  @endif>{{ trans('langSimpleExercise') }}</option>
                                                    <option value='{{ MULTIPLE_PAGE_TYPE }}' @if ($exerciseType == MULTIPLE_PAGE_TYPE) selected @endif>{{ trans('langSequentialExercise') }}</option>
                                                    <option value='{{ ONE_WAY_TYPE }}' @if ($exerciseType == ONE_WAY_TYPE)? selected @endif> {{ trans('langOneWayExercise') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <label for='exerciseRangeId' class='col-12 control-label-notes mb-1'>
                                                {{ trans('langAnswers') }}
                                                <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langShuffleAnswersLegend') }}' style='margin-bottom: 10px;'></span>
                                            </label>
                                            <div class='col-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input name='shuffle_answers' type='checkbox' @if ($hasShuffleAnswers) checked @endif>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langShuffleAnswers') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <label for='exerciseRangeId' class='col-12 control-label-notes mb-1'>{{ trans('langExerciseScaleGrade') }}</label>
                                            <div class='col-12'>
                                                <select name='exerciseRange' class='form-select' id='exerciseRangeId'>
                                                    <option value='' @if ($exerciseRange == 0) selected @endif>-- {{ trans('langExerciseNoScaleGrade') }} --</option>
                                                    <option value='10'  @if ($exerciseRange == 10) selected @endif>0-10</option>
                                                    <option value='20' @if ($exerciseRange == 20) selected @endif>0-20</option>
                                                    <option value='5' @if ($exerciseRange == 5) selected @endif>0-5</option>
                                                    <option value='100' @if ($exerciseRange == 100) selected @endif>0-100</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <label for='exerciseCalcGradeMethod' class='col-12 control-label-notes mb-1'>{{ trans('langExerciseCalcGradeMethod') }}</label>
                                            <div class='col-12'>
                                                <select name='exerciseCalcGradeMethod' class='form-select'>
                                                    <option value='1' @if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_STANDARD) selected @endif>{{ trans('langExerciseNoCalcGradeMethod') }}</option>
                                                    <option value='2'  @if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_CERTAINTY_BASED) selected @endif>{{ trans('langExerciseCBCalcGradeMethod') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <div class='col-12'>
                                                <div class='row'>
                                                    <div class='col-md-3'>
                                                        <label for='exerciseTimeConstraint' class='col-12 control-label-notes mb-1'>
                                                            <strong id='legend_grade_pass'>
                                                                @if ($exerciseRange == 0)
                                                                    {{ trans('langSuccessPercentage') }}
                                                                @else
                                                                    {{ trans('langExerciseGradePass') }}
                                                                @endif
                                                            </strong>
                                                            <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langExerciseGradePassLegend') }}' style='margin-bottom: 10px;'></span>
                                                        </label>
                                                        <input type='text' class='form-control' name='exerciseGradePass' id='exerciseGradePass' value='{{ $exerciseGradePass }}' size='4' maxlength='4'>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row input-append date form-group @if (Session::getError('exerciseStartDate')) has-error @endif mt-4' id='startdatepicker' data-date='{{ $exerciseStartDate }}' data-date-format='dd-mm-yyyy'>
                                            <label for='exerciseStartDate' class='col-12 control-label-notes mb-1'>{{ trans('langStart') }}</label>
                                            <div class='col-12'>
                                                <div class='input-group'>
                                            <span class='input-group-addon'>
                                                <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                    <input class='mt-0' type='checkbox' id='enableStartDate' name='enableStartDate' value='1' @if ($enableStartDate) checked @endif>
                                                    <span class='checkmark'></span>
                                                </label>
                                            </span>
                                                    <span class='add-on1 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                    <input class='form-control mt-0 border-start-0' name='exerciseStartDate' id='exerciseStartDate' type='text' value='{{ $exerciseStartDate }}' @if (!$enableStartDate) disabled @endif>
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

                                        <div class='row input-append date form-group @if (Session::getError('exerciseEndDate')) has-error @endif mt-4' id='enddatepicker' data-date='{{ $exerciseEndDate }}' data-date-format='dd-mm-yyyy'>
                                            <label for='exerciseEndDate' class='col-12 control-label-notes mb-1'>
                                                {{ trans('langFinish') }}
                                            </label>
                                            <div class='col-12'>
                                                <div class='input-group'>
                                                <span class='input-group-addon'>
                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                         <input class='mt-0' type='checkbox' id='enableEndDate' name='enableEndDate' value='1' @if ($enableEndDate) checked @endif>
                                                         <span class='checkmark'></span>
                                                    </label>
                                                </span>
                                                    <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                    <input class='form-control mt-0 border-start-0' name='exerciseEndDate' id='exerciseEndDate' type='text' value='{{ $exerciseEndDate }}' @if (!$enableEndDate) disabled @endif>
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

                                        <div class='row form-group mt-4'>
                                            <div class='col-12 control-label-notes mb-1'>
                                                {{ trans('langTemporarySave') }}
                                            </div>
                                            <div class='col-12'>
                                                <div class='row'>
                                                    <div class='col-md-6 col-12 radio'>
                                                        <label>
                                                            <input type='radio' name='exerciseTempSave' value='0' @if ($exerciseTempSave == 0) checked @endif>
                                                            {{ trans('langDeactivate') }}
                                                        </label>
                                                    </div>
                                                    <div class='col-md-6 col-12 radio'>
                                                        <label>
                                                            <input type='radio' name='exerciseTempSave' value='1' @if ($exerciseTempSave == 1) checked @endif>
                                                            {{ trans('langActivate') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row form-group @if (Session::getError('exerciseTimeConstraint') or Session::getError('exerciseAttemptsAllowed')) has-error @endif mt-4'>
                                            <div class='col-12'>
                                                <div class='row'>
                                                    <div class='col-md-6'>
                                                        <label for='exerciseTimeConstraint' class='col-12 control-label-notes mb-1'>
                                                            {{ trans('langExerciseConstrain') }}
                                                            <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langExerciseConstrainExplanation') }}' style='margin-bottom: 10px;'></span>
                                                        </label>
                                                        <input type='text' class='form-control' name='exerciseTimeConstraint' id='exerciseTimeConstraint' value='{{ $exerciseTimeConstraint }}' placeholder='{{ trans('langExerciseConstrain') }}'>
                                                        <span class='help-block'>
                                                            @if (Session::getError('exerciseTimeConstraint'))
                                                                {{ Session::getError('exerciseTimeConstraint') }}
                                                            @else
                                                                {{ trans('langExerciseConstrainUnit') }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class='col-md-6'>
                                                        <label for='exerciseAttemptsAllowed' class='col-12 control-label-notes mb-1'>
                                                            {{ trans('langExerciseAttemptsAllowed') }}
                                                            <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langExerciseAttemptsAllowedExplanation') }}' style='margin-bottom: 10px;'></span>
                                                        </label>
                                                        <input type='text' class='form-control' name='exerciseAttemptsAllowed' id='exerciseAttemptsAllowed' value='{{ $exerciseAttemptsAllowed  }}' placeholder='{{ trans('langExerciseConstrain') }}'>
                                                        <span class='help-block'>
                                                            @if (Session::getError('exerciseAttemptsAllowed'))
                                                                {{ Session::getError('exerciseAttemptsAllowed') }}
                                                            @else
                                                                {{ trans('langExerciseAttemptsAllowedUnit') }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <label class='col-12 control-label-notes mb-1'>{{ trans('langAnswers') }}</label>
                                            <div class='col-12'>
                                                <select name='dispresults' class='form-select'>
                                                    <option value='1' @if ($displayResults == 1) selected @endif >{{ trans('langAnswersDisp') }}</option>
                                                    <option value='0' @if ($displayResults == 0) selected @endif >{{ trans('langAnswersNotDisp') }}</option>
                                                    <option value='3' @if ($displayResults == 3) selected @endif>{{ trans('langAnswersDispLastAttempt') }}</option>
                                                    <option value='4' @if ($displayResults == 4) selected @endif>{{ trans('langAnswersDispEndDate') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <label class='col-12 control-label-notes mb-1'>{{ trans('langScore') }}</label>
                                            <div class='col-12'>
                                                <select name='dispscore' class='form-select'>
                                                    <option value='1' @if ($displayScore == 1) selected @endif>{{ trans('langScoreDisp') }}</option>
                                                    <option value='0' @if ($displayScore == 0) selected @endif>{{ trans('langScoreNotDisp') }}</option>
                                                    <option value='3' @if ($displayScore == 3) selected @endif>{{ trans('langScoreDispLastAttempt') }}</option>
                                                    <option value='4' @if ($displayScore == 4) selected @endif>{{ trans('langScoreDispEndDate') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <div class='control-label-notes mb-1'>{{ trans('langWorkAssignTo') }}</div>
                                            <div class='col-12'>
                                                <div class='radio'>
                                                    <label>
                                                        <input type='radio' id='assign_button_all' name='assign_to_specific' value='0' @if ($exerciseAssignToSpecific == 0) checked @endif>
                                                        {{ trans('langWorkToAllUsers') }}
                                                    </label>
                                                </div>
                                                <div class='radio'>
                                                    <label>
                                                        <input type='radio' id='assign_button_user' name='assign_to_specific' value='1' @if ($exerciseAssignToSpecific == 1) checked @endif>
                                                        {{ trans('langWorkToUser') }}
                                                    </label>
                                                </div>
                                                <div class='radio'>
                                                    <label>
                                                        <input type='radio' id='assign_button_group' name='assign_to_specific' value='2' @if ($exerciseAssignToSpecific == 2) checked @endif>
                                                        {{ trans('langWorkToGroup') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <div class='col-12'>
                                                <div class='table-responsive mt-0'>
                                                    <table id='assignees_tbl' class='table-default @unless (in_array($exerciseAssignToSpecific, [1, 2])) hide @endunless'>
                                                        <thead>
                                                            <tr class='title1 list-header'>
                                                                <td class='form-label' id='assignees'>{{ trans('langStudents') }}</td>
                                                                <td class='form-label text-center'>{{ trans('langMove') }}</td>
                                                                <td class='form-label'>{{ trans('langWorkAssignTo') }}</td>
                                                            </tr>
                                                        </thead>
                                                        <tr>
                                                            <td>
                                                                <select aria-label='{{ trans('langStudent') }}' class='form-select h-100' id='assign_box' size='10' multiple>
                                                                    @if (isset($unassigned_options))
                                                                        {!! $unassigned_options !!}
                                                                    @endif
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <div class='d-flex align-items-center flex-column gap-2'>
                                                                    <input aria-label='{{ trans('langMove') }}' class='btn submitAdminBtn submitAdminBtnClassic' type='button' onClick="move('assign_box','assignee_box')" value='   &gt;&gt;   ' />
                                                                    <input aria-label='{{ trans('langMove') }}' class='btn submitAdminBtn submitAdminBtnClassic' type='button' onClick="move('assignee_box','assign_box')" value='   &lt;&lt;   ' />
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <select aria-label='{{ trans('langWorkAssignTo') }}' class='form-select h-100' id='assignee_box' name='ingroup[]' size='10' multiple>
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

                                        <div class='row form-group mt-4'>
                                            <div class='col-12 control-label-notes mb-1'>{{ trans('langExerciseType') }}</div>
                                            <div class='col-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input id='isExam_' name='isExam' type='checkbox' @if ($isExam) checked @endif>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langExam') }}
                                                    </label>
                                                    <div class='help-block'>
                                                        {{ trans('langRequireCourseUserLogin') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4 d-none' id='stricter_exam'>
                                            <div class='col-sm-12 control-label-notes mb-1'>{{ trans('langStricterExamRestriction') }}:</div>
                                            <div class='col-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input name='stricterExamRestriction' type='checkbox' @if($exerciseStricterExamRestriction) checked @endif>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langExerciseWillBeCanceledInStrictMode') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <div class='col-12 control-label-notes mb-1'>
                                                {{ trans('langContinueAttempt') }}
                                            </div>
                                            <div class='col-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input id='continueAttempt' name='continueAttempt' type='checkbox' @if ($continueTimeLimit) checked @endif>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langContinueAttemptExplanation') }}
                                                    </label>
                                                </div>
                                                <div id='continueTimeField' class='form-inline' style='margin-top: 15px; @unless ($continueTimeLimit) display: none @endunless'>
                                                    {!! $continueTimeField !!}
                                                </div>
                                            </div>
                                        </div>

                                        <div class='row form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-1'>{{ trans('langExercisePreventCopy') }}:</div>
                                            <div class='col-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input id='jsPreventCopy' name='jsPreventCopy' type='checkbox' @if ($exercisePreventCopy) checked @endif>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langExercisePreventCopyExplanation') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='panel-group group-section mt-4' id='accordionEx' role='tablist' aria-multiselectable='true'>
                                            <ul class='list-group list-group-flush'>
                                                <li class='list-group-item px-0 mb-4 bg-transparent'>
                                                    <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#CheckAccess' aria-expanded='false' aria-controls='CheckAccess'>
                                                        <span class='fa-solid fa-chevron-down'></span>
                                                        {{ trans('langCheckAccess') }}
                                                    </a>
                                                    <div id='CheckAccess' class='panel-collapse accordion-collapse collapse border-0 rounded-0' role='tabpanel' data-bs-parent='#accordionEx'>
                                                        <div class='panel-body bg-transparent Neutral-900-cl p-0'>
                                                            <div class='form-group  @if (Session::getError('exercisePasswordLock')) has-error @endif mt-4'>
                                                            <label for='exercisePasswordLock' class='col-12 control-label-notes mb-1'>{{ trans('langPassCode') }}</label>
                                                            <div class='col-12'>
                                                                <input name='exercisePasswordLock' type='text' class='form-control' id='exercisePasswordLock' value='{{ $exercisePasswordLock }}' placeholder=''>
                                                                <span class='help-block Accent-200-cl'> {{ Session::getError('exercisePasswordLock') }}</span>
                                                            </div>
                                                        </div>
                                                        <div class='form-group @if (Session::getError('exerciseIPLock')) has-error @endif mt-4'>
                                                            <label for='exerciseIPLock' class='col-12 control-label-notes mb-1'>{{ trans('langIPUnlock') }}</label>
                                                            <div class='help-block'>
                                                                {{ trans('langIPUnlockLegend') }}
                                                            </div>
                                                            <div class='col-12'>
                                                                <select name='exerciseIPLock[]' class='form-select' id='exerciseIPLock' multiple>
                                                                    {!! $exerciseIPLockOptions !!}
                                                                </select>
                                                                <span class='help-block Accent-200-cl'>{{ Session::getError('exerciseIPLock') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </li>
                                            </ul>
                                        </div>

                                        {!! $tags_list !!}

                                        <div class='row form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-end align-items-center'>
                                                {!! $form_buttons !!}
                                            </div>
                                        </div>

                                </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>
                </div>
                <div class='d-none d-lg-block'>
                    <img class='form-image-modules' src='{{ get_form_image() }}' alt='{{ trans('langImgFormsDes') }}'>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type='text/javascript'>
    $(function() {

        $('#exerciseRangeId').change(function() {
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
        }).on('changeDate', function(ev){
            if($(this).attr('id') === 'exerciseEndDate') {
                $('#answersDispEndDate, #scoreDispEndDate').removeClass('hidden');
            }
        }).on('blur', function(ev){
            if($(this).attr('id') === 'exerciseEndDate') {
                var end_date = $(this).val();
                if (end_date === '') {
                    if ($('input[name=\"dispresults\"]:checked').val() == 4) {
                        $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                    }
                    $('#answersDispEndDate, #scoreDispEndDate').addClass('hidden');
                }
            }
        });
        $('#enableEndDate, #enableStartDate').change(function() {
            var dateType = $(this).prop('id').replace('enable', '');
            if($(this).prop('checked')) {
                $('input#exercise'+dateType).prop('disabled', false);
                if (dateType === 'EndDate' && $('input#exerciseEndDate').val() !== '') {
                    $('#answersDispEndDate, #scoreDispEndDate').removeClass('hidden');
                }
            } else {
                $('input#exercise'+dateType).prop('disabled', true);
                if ($('input[name=\"dispresults\"]:checked').val() == 4) {
                    $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                }
                $('#answersDispEndDate, #scoreDispEndDate').addClass('hidden');
            }
        });
        $('#exerciseAttemptsAllowed').blur(function(){
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

        $('#exerciseIPLock').select2({
            minimumResultsForSearch: Infinity,
            tags: true,
            tokenSeparators: [',', ' ']
        });

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
        $('#isExam_').on('click', function() {
            if ($(this).is(':checked')) {
                $('#stricter_exam').removeClass('d-none').addClass('d-block');
            } else {
                $('#stricter_exam').removeClass('d-block').addClass('d-none');
            }
        });

        var count = 0;
        @if (count($exerciseFeedback) > 0)
            count = {{ count($exerciseFeedback) }};
        @endif

        $('#add-feedback-btn').click(function(e) {
            e.preventDefault();
            count++;
            var feedbackRow = `
                  <div class='feedback-row d-flex align-items-center justify-content-between border-bottom mb-1'>
                        <input type='text' name='feedback_text[${count}]' size='60' maxlength='200' placeholder='{{ trans('langText') }}'>
                        <input type='text' name='feedback_grade[${count}]' size='4' maxlength='4' placeholder='{{ trans('langGradebookGrade') }}'>
                        <a class='delete-feedback-btn'><span class='fa-solid fa-xmark' style='color:red;'></span></a>
                  </div>`;
            $('#feedback-container').append(feedbackRow);
        });

        $('#feedback-container').on('click', '.delete-feedback-btn', function() {
            $(this).closest('.feedback-row').remove();
            $('#feedback-container .feedback-row').each(function(index) {
                var newIndex = index + 1;
                $(this).find('input[name^="feedback_text"]').attr('name', 'feedback_text[' + newIndex + ']');
                $(this).find('input[name^="feedback_grade"]').attr('name', 'feedback_grade[' + newIndex + ']');
            })
        });
    });

    function ajaxAssignees()
    {
        $('#assignees_tbl').removeClass('hide');
        var type = $(this).val();
        $.post('',
            {
                assign_type: type
            },
            function(data,status){
                var index;
                var parsed_data = JSON.parse(data);
                var select_content = '';
                if(type==1){
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

    function hideAssignees()
    {
        $('#assignees_tbl').addClass('hide');
        $('#assignee_box').find('option').remove();
    }
</script>

@endsection

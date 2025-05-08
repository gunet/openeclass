@if (is_active_external_lti_app($turnitinapp, TURNITIN_LTI_TYPE, $course_id))
    <div class='row input-append date form-group {{ $work_feedback_release_hidden }} {{ $work_feedback_release_haserror }} mt-4' id='feedbackreleasedatepicker' data-date='{{ $WorkFeedbackRelease }}' data-date-format='dd-mm-yyyy'>
        <label for='tii_feedbackreleasedate' class='col-12 control-label-notes mb-1'>{{ trans('langTiiFeedbackReleaseDate') }}</label>
        <div class='col-12'>
            <div class='input-group'>
                    <span class='input-group-addon'>
                        <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                            <input class='mt-0' type='checkbox' id='enableWorkFeedbackRelease' name='enableWorkFeedbackRelease' value='1' {{ $work_feedback_release_checked }}>
                            <span class='checkmark'></span>
                        </label>
                    </span>
                <span class='add-on2 input-group-text h-40px input-border-color border-end-0'>
                    <i class='fa-regular fa-calendar Neutral-600-cl'></i>
                </span>
                <input class='form-control mt-0 border-start-0' name='tii_feedbackreleasedate' id='tii_feedbackreleasedate' type='text' value='{{ $WorkFeedbackRelease }}' {{ $work_feedback_release_disabled }}>
            </div>
            <span class='help-block'>
                @if (Session::hasError('WorkFeedbackRelease'))
                    {{ Session::getError('WorkFeedbackRelease') }}
                @else
                    &nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> {{ trans('langAssignmentFeedbackReleaseHelpBlock') }}
                @endif
            </span>
        </div>
    </div>
@endif

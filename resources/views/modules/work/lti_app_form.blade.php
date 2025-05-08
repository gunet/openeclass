@if (is_active_external_lti_app($turnitinapp, TURNITIN_LTI_TYPE, $course_id))
    {{-- {!! $divs_for_lti_templates !!} --}}

    <div class='row form-group mt-4'>
        <div class='col-12 control-label-notes mb-1'>{{ trans('langAssignmentType') }}</div>
        <div class='{{ $assignment_type_radios_class }}'>
            <div class='{{ $assignment_type_eclass_div_class }}'>
                <label>
                    <input type='radio' name='assignment_type' value='0' @if ($assignment_type == ASSIGNMENT_TYPE_ECLASS) checked @endif>
                    {{ trans('langAssignmentTypeEclass') }}
                </label>
            </div>
            <div class='{{ $assignment_type_turnitin_div_class }}'>
                <label>
                    <input type='radio' name='assignment_type' value='1' @if ($assignment_type == ASSIGNMENT_TYPE_TURNITIN) checked @endif>
                    {{ trans('langAssignmentTypeTurnitin') }}
                </label>
            </div>
        </div>
        <div class='{{ $help_block_div_class }}'>
            <span class='help-block'>{{ trans('langTurnitinNewAssignNotice') }}</span>
        </div>
    </div>

    <div class='col-12 form-group {{ $lti_hidden }} mt-4 mb-4 p-3' id='lti_label' style='box-shadow: 0 0 10px 0 rgba(0,0,0,0.1); padding-top:10px; padding-bottom:10px;'>
        <div class='{{ $ltiopts_div_class  }}'>{{ trans('langLTIOptions') }}</div>
        <div class='form-group {{ $lti_hidden }} mt-4'>
            <label for='lti_templates' class='{{ $tiiapp_label_class }}'>{{ trans('langTiiApp') }}</label>
            <div class='{{ $lti_template_div_class }}'>
                <select name='lti_template' class='form-select' id='lti_templates' {{ $lti_disabled }}>
                    {!! $lti_template_options !!}
                </select>
            </div>
        </div>
        <div class='form-group mt-4' id='SelectContentModalDiv'>
            <div class='col-sm-12'>
                <button type='button' class='btn submitAdminBtn' style='display: inline; margin-bottom: 10px; margin-right: 10px;' data-bs-toggle='modal' data-bs-target='#SelectContentModal'>{{ trans('langTiiSelectContent') }}</button>
                <span id='tii_selected_content_span'>{{ $tii_selected_content }}</span>
            </div>
            <div class='col-sm-12'>{{ trans('langTiiSelectContentDesc') }}</div>
        </div>
        <div class='{{ $lti_launchcontainer_div_class }}'>
            <label for='lti_launchcontainer' class='{{ $lti_launchcontainer_label_class }}'>{{ trans('langLTILaunchContainer') }}</label>
            <div class='col-sm-12'>{!! selection(lti_get_containers_selection(), 'lti_launchcontainer', $lti_launchcontainer, 'id="lti_launchcontainer"' . $lti_disabled)  !!}</div>
        </div>
        <!-- <div class='{{ $tii_submit_papers_to_div_class }}'>
            <label for='tii_submit_papers_to' class='{{ $tii_submit_papers_to_label_class }}'>{{ trans('langTiiSubmissionSettings') }}:</label>
            <div class='col-sm-12'>
              <select name='tii_submit_papers_to' class='form-select' id='tii_submit_papers_to' {{ $lti_disabled }}>
                    <option value='0' @if ($tii_submit_papers_to == 0) selected @endif>{{ trans('langTiiSubmissionNoStore') }}</option>
                    <option value='1' @if ($tii_submit_papers_to == 1) selected @endif>{{ trans('langTiiSubmissionStandard') }}</option>
                    <option value='2' @if ($tii_submit_papers_to == 2) selected @endif>{{ trans('langTiiSubmissionInstitutional') }}</option>
              </select>
            </div>
        </div> -->
        <div class='form-group {{ $lti_hidden }} mt-4'>
            <div class='{{ $tii_compare_against_div_class }}'>{{ trans('langTiiCompareAgainst') }}</div>
            <div class='col-sm-12'>
                <div class='checkbox'>
                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                        <input type='checkbox' name='tii_studentpapercheck' id='tii_studentpapercheck' value='1' {{ $tii_studentpapercheck_checked }} {{ $lti_disabled }}>
                        <span class='checkmark'></span>
                        {{ trans('langTiiStudentPaperCheck') }}
                    </label>
                </div>
                <div class='checkbox'>
                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                        <input type='checkbox' name='tii_internetcheck' id='tii_internetcheck' value='1' {{ $tii_internetcheck_checked }} {{ $lti_disabled }}>
                        <span class='checkmark'></span>
                        {{ trans('langTiiInternetCheck') }}
                    </label>
                </div>
                <div class='checkbox'>
                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                        <input type='checkbox' name='tii_journalcheck' id='tii_journalcheck' value='1' {{ $tii_journalcheck_checked }} {{ $lti_disabled }}>
                        <span class='checkmark'></span>
                        {{ trans('langTiiJournalCheck') }}
                    </label>
                </div>
                <!--<div class='checkbox'>
                <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                    <input type='checkbox' name='tii_institutioncheck' id='tii_institutioncheck' value='1' {{ $tii_institutioncheck_checked }} {{ $lti_disabled }}>
                    <span class='checkmark'></span>
                    {{ trans('langTiiInstitutionCheck') }}
                  </label>
                </div>-->
            </div>
        </div>
        <div class='form-group {{ $lti_hidden }} mt-4'>
            <label for='tii_report_gen_speed' class='{{ $tii_report_gen_speed_label_class }}'>{{ trans('langTiiSimilarityReport') }}</label>
            <div class='col-sm-12'>
                <select name='tii_report_gen_speed' class='form-select' id='tii_report_gen_speed' {{ $lti_disabled }}>
                    <option value='0' @if ($tii_report_gen_speed == 0) selected @endif>{{ trans('langTiiReportGenImmediatelyNoResubmit') }}</option>
                    <option value='1' @if ($tii_report_gen_speed == 1) selected @endif>{{ trans('langTiiReportGenImmediatelyWithResubmit') }}</option>
                    <option value='2' @if ($tii_report_gen_speed == 2) selected @endif>{{ trans('langTiiReportGenOnDue') }}</option>
                </select>
            </div>
            <div class='{{ $tii_s_view_reports_div_class }}'>
                <div class='checkbox'>
                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                        <input type='checkbox' name='tii_s_view_reports' id='tii_s_view_reports' value='1' {{ $tii_s_view_reports_checked }} {{ $lti_disabled }}>
                        <span class='checkmark'></span>
                        {{ trans('langTiiSViewReports') }}
                    </label>
                </div>
                <div class='checkbox'>
                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                        <input type='checkbox' name='tii_use_biblio_exclusion' id='tii_use_biblio_exclusion' value='1' {{ $tii_use_biblio_exclusion_checked }} {{ $lti_disabled }}>
                        <span class='checkmark'></span>
                        {{ trans('langTiiExcludeBiblio') }}
                    </label>
                </div>
                <div class='checkbox'>
                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                        <input type='checkbox' name='tii_use_quoted_exclusion' id='tii_use_quoted_exclusion' value='1' {{ $tii_use_quoted_exclusion }} {{ $lti_disabled }}>
                        <span class='checkmark'></span>
                        {{ trans('langTiiExcludeQuoted') }}
                    </label>
                </div>
                <div class='checkbox'>
                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                        <input type='checkbox' name='tii_use_small_exclusion' id='tii_use_small_exclusion' value='1' {{ $tii_use_small_exclusion }} {{ $lti_disabled }}>
                        <span class='checkmark'></span>
                        {{ trans('langTiiExcludeSmall') }}
                    </label>
                </div>
            </div>
        </div>
        <div class='{{ $tii_exclude_type_group_div_class }}'>
            <div class='{{ $tii_exclude_type_div_label_class }}'>{{ trans('langTiiExcludeType') }}</div>
            <div class='{{ $tii_exclude_type_div_inner_class }}'>
                <div class='radio'>
                    <label>
                        <input type='radio' name='tii_exclude_type' id='tii_exclude_type_words' value='words' {{ $tii_exclude_type_words_checked }} {{ $lti_disabled }}>
                        {{ trans('langTiiExcludeTypeWords') }}
                    </label>
                </div>
                <div class='radio'>
                    <label>
                        <input type='radio' name='tii_exclude_type' id='tii_exclude_type_percentage' value='percentage' {{ $tii_exclude_type_percentage_checked }} {{ $lti_disabled }}>
                        {{ trans('langPercentage') }}
                    </label>
                </div>
            </div>
        </div>
        <div class='{{ $tii_exclude_value_group_div_class }}'>
            <label for='tii_exclude_value' class='{{ $tii_exclude_value_label_div_class }}'>{{ trans('langTiiExcludeValue') }}:</label>
            <div class='{{ $tii_exclude_value_div_class }}'>
                <input name='tii_exclude_value' type='text' class='form-control' id='tii_exclude_value' value='{{ $tii_exclude_value }}' {{ $lti_disabled }}>
            </div>
        </div>
        <div class='{{ $tii_instructorcustomparameters_group_div_class }}'>
            <label for='tii_instructorcustomparameters' class='{{ $tii_instructorcustomparameters_label_div_class }}'>{{ trans('langTiiInstructorCustomParameters') }}:</label>
            <div class='{{ $tii_instructorcustomparameters_div_class }}'>
                <textarea class='form-control' name='tii_instructorcustomparameters' id='tii_instructorcustomparameters' rows='3' {{ $lti_disabled }}>{{ $tii_instructorcustomparameters }}</textarea>
            </div>
        </div>
    </div>

    <div class='modal fade' id='SelectContentModal' tabindex='-1' aria-labelledby='SelectContentModalLabel' aria-hidden='true'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title' id='SelectContentModalLabel'>{{ trans('langTiiSelectContent') }}</div>
                    <button type='button' class='close' data-bs-dismiss='modal' aria-label='{{ trans('langClose') }}'></button>
                </div>
                <div class='modal-body' id='SelectContentModalBody'>
                    <iframe id='SelectContentModalBodyContentFrame'
                            src='about:blank'
                            allowfullscreen=''
                            width='100%'
                            height='800px'
                            style='border: 1px solid #ddd; border-radius: 4px;'>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
@else
    <input type='hidden' name='assignment_type' value='0' />
@endif
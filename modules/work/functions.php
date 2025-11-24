<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


/**
 * @brief new / edit assignment
 */
function new_edit_assignment($assignment_id = null) {
    global $course_id, $language, $autojudge;

    load_js('bootstrap-datetimepicker');
    load_js('select2');

    $assignee_options = '';
    $unassigned_options = '';
    $lti_templates = resolve_lti_templates();

    if ($assignment_id != null) { // edit assignment
        $row = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $assignment_id);
        $grading_type = ($row->grading_type ? $row->grading_type : ASSIGNMENT_STANDARD_GRADE);
        $assignment_type = ($row->assignment_type ? $row->assignment_type : ASSIGNMENT_TYPE_ECLASS);

        $scales = Database::get()->queryArray('SELECT * FROM grading_scale WHERE course_id = ?d', $course_id);
        $scale_options = '';
        foreach ($scales as $scale) {
            $scale_options .= "<option value='$scale->id'" . (($row->grading_scale_id == $scale->id && $grading_type == ASSIGNMENT_SCALING_GRADE) ? " selected" : "") . ">$scale->title</option>";
        }
        $rubrics = Database::get()->queryArray('SELECT * FROM rubric WHERE course_id = ?d', $course_id);
        $rubric_options = '';
        foreach ($rubrics as $rubric) {
            $rubric_options .= "<option value='$rubric->id'" . (($row->grading_scale_id == $rubric->id && $grading_type == ASSIGNMENT_RUBRIC_GRADE) ? " selected" : "") . ">$rubric->name</option>";
        }
        $rubric_option_review = '';
        foreach ($rubrics as $rub) {
            $rubric_option_review .= "<option value='$rub->id'" . (($row->grading_scale_id == $rub->id && $grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) ? " selected" : "") . ">$rub->name</option>";
        }

        if ($row->assign_to_specific) {
            //preparing options in select boxes for assigning to specific users/groups
            if (($row->group_submissions) or ($row->assign_to_specific == 2)) {
                $assignees = Database::get()->queryArray("SELECT `group`.id AS id, `group`.name
                                   FROM assignment_to_specific, `group`
                                    WHERE course_id = ?d
                                    AND `group`.id = assignment_to_specific.group_id
                                    AND assignment_to_specific.assignment_id = ?d", $course_id, $assignment_id);
                $all_groups = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d AND visible = 1", $course_id);
                foreach ($assignees as $assignee_row) {
                    $assignee_options .= "<option value='" . $assignee_row->id . "'>" . $assignee_row->name . "</option>";
                }
                $unassigned = array_udiff($all_groups, $assignees,
                    function ($obj_a, $obj_b) {
                        return $obj_a->id - $obj_b->id;
                    }
                );
                foreach ($unassigned as $unassigned_row) {
                    $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->name</option>";
                }
            } else {
                $assignees = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                   FROM assignment_to_specific, user
                                   WHERE user.id = assignment_to_specific.user_id AND assignment_to_specific.assignment_id = ?d
                                   ORDER BY surname, givenname, am", $assignment_id);
                $all_users = Database::get()->queryArray("SELECT user.id AS id, user.givenname, user.surname
                                    FROM user, course_user
                                    WHERE user.id = course_user.user_id
                                      AND course_user.course_id = ?d AND course_user.status = " . USER_STUDENT . "
                                      AND user.id
                                    ORDER BY user.surname, user.givenname, user.am", $course_id);
                foreach ($assignees as $assignee_row) {
                    $assignee_options .= "<option value='$assignee_row->id'>$assignee_row->surname $assignee_row->givenname</option>";
                }
                $unassigned = array_udiff($all_users, $assignees,
                    function ($obj_a, $obj_b) {
                        return $obj_a->id - $obj_b->id;
                    }
                );
                foreach ($unassigned as $unassigned_row) {
                    $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->surname $unassigned_row->givenname</option>";
                }
            }
        }

        $data['title'] = Session::has('title') ? Session::get('title') : $row->title;
        $data['desc'] = Session::has('desc') ? Session::get('desc') : $row->description;
        $data['launch_container'] = $row->launchcontainer;
        $data['lti_disabled'] = ($assignment_type != ASSIGNMENT_TYPE_TURNITIN) ? 'disabled' : '';
        $data['student_papercheck'] = $row->tii_studentpapercheck;
        $data['internetcheck'] = $row->tii_internetcheck;
        $data['journalcheck'] = $row->tii_journalcheck;
        $data['report_gen_speed'] = $row->tii_report_gen_speed;
        $data['institutioncheck'] = $row->tii_institutioncheck;
        $data['tii_fwddate'] = '';
        $data['tii_s_view_reports'] = $row->tii_s_view_reports;
        $data['tii_use_biblio_exclusion'] = $row->tii_use_biblio_exclusion;
        $data['tii_use_quoted_exclusion'] = $row->tii_use_quoted_exclusion;
        $data['tii_exclude_type'] = $row->tii_exclude_type;
        $data['tii_exclude_value'] = $row->tii_exclude_value;
        $data['rubric_options'] = $rubric_options;
        $data['rubric_options_review'] = $rubric_option_review;
        $data['scale_options'] = $scale_options;
        $data['submission_type'] = $row->submission_type;
        $data['assignment_type'] = $assignment_type;
        $data['grading_type'] = $grading_type;
        $data['late_submission'] = $row->late_submission;
        $data['assign_to_specific'] = $row->assign_to_specific;
        $data['WorkStart'] = $WorkStart = $row->submission_date ? DateTime::createFromFormat('Y-m-d H:i:s', $row->submission_date)->format('d-m-Y H:i') : NULL;
        $data['WorkEnd'] = $WorkEnd = $row->deadline ? DateTime::createFromFormat('Y-m-d H:i:s', $row->deadline)->format('d-m-Y H:i') : NULL;
        $data['WorkStart_review'] = $WorkStart_review = $row->start_date_review ? DateTime::createFromFormat('Y-m-d H:i:s', $row->start_date_review)->format('d-m-Y H:i') : NULL;
        $data['WorkEnd_review'] = $WorkEnd_review = $row->due_date_review ? DateTime::createFromFormat('Y-m-d H:i:s', $row->due_date_review)->format('d-m-Y H:i') : NULL;
        $data['WorkFeedbackRelease'] = $row->tii_feedbackreleasedate ? DateTime::createFromFormat('Y-m-d H:i:s', $row->tii_feedbackreleasedate)->format('d-m-Y H:i') : NULL;
        $data['work_feedback_release_hidden'] = ($row->assignment_type == 1) ? '' : 'hidden';
        $data['work_feedback_release_disabled'] = is_null($row->tii_feedbackreleasedate) ? 'disabled' : '';
        $data['max_grade'] = Session::has('max_grade') ? Session::get('max_grade') : ($row->max_grade ? $row->max_grade : 10);
        $data['reviews_per_user'] = Session::has('reviews_per_user') ? Session::get('reviews_per_user') : ($row->reviews_per_assignment ? $row->reviews_per_assignment : 5);
        $data['enableWorkStart'] = Session::has('enableWorkStart') ? Session::get('enableWorkStart') : ($WorkStart ? 1 : 0);
        $data['enableWorkEnd'] = Session::has('enableWorkEnd') ? Session::get('enableWorkEnd') : ($WorkEnd ? 1 : 0);
        $data['enableWorkStart_review'] = Session::has('enableWorkStart_review') ? Session::get('enableWorkStart_review') : ($WorkStart_review ? 1 : 0);
        $data['enableWorkEnd_review'] = Session::has('enableWorkEnd_review') ? Session::get('enableWorkEnd_review') : ($WorkEnd_review ? 1 : 0);
        $data['assignmentPasswordLock'] = Session::has('assignmentPasswordLock') ? Session::get('assignmentPasswordLock') : $row->password_lock;
        $data['fileCount'] = $row->max_submissions;
        $assignmentIPLock = Session::has('assignmentIPLock') ?
            Session::get('assignmentIPLock') :
            explode(',', $row->ip_lock);
        $data['assignmentIPLockOptions'] = implode('', array_map(
            function ($item) {
                $item = trim($item);
                return $item ? ('<option selected>' . q($item) . '</option>') : '';
            }, $assignmentIPLock));
        $data['notification'] = $row->notification;
        $data['group_submissions'] = $row->group_submissions;
        $data['assignment_id'] = $assignment_id;
        $data['assignment_filename'] = $row->file_name;

        $data['auto_judge'] = $row->auto_judge;
        if ($row->auto_judge == true) {
            $auto_judge_scenarios = unserialize($row->auto_judge_scenarios);
        } else {
            $auto_judge_scenarios = null;
        }
        $data['auto_judge_scenarios'] = $auto_judge_scenarios;
        $data['submit_name'] = 'do_edit';

        $data['lti_template_options'] = resolve_lti_template_options($lti_templates, $row);
        $data['lti_template_options_selected_lti_template'] = $row->lti_template;
        $lti_hidden = ($assignment_type == ASSIGNMENT_TYPE_TURNITIN) ? '' : 'hidden';
        $data['lti_hidden'] = $lti_hidden;
        $data['lti_disabled'] = ($assignment_type == ASSIGNMENT_TYPE_TURNITIN) ? '' : 'disabled';
        $data['lti_launchcontainer'] = $row->launchcontainer;
        $data['tii_submit_papers_to'] = $row->tii_submit_papers_to;
        $data['assignment_type_radios_class'] = 'col-12 d-inline-flex';
        $data['assignment_type_eclass_div_class'] = 'radio';
        $data['assignment_type_turnitin_div_class'] = 'radio ms-3';
        $data['help_block_div_class'] = 'col-12 mt-1 mb-1';
        $data['ltiopts_div_class'] = 'TextBold large-text col-sm-offset-1';
        $data['tiiapp_label_class'] = 'col-sm-6 control-label-notes';
        $data['lti_template_div_class'] = 'col-sm-12';
        $data['tii_selected_content'] = (!empty($row->tii_instructorcustomparameters)) ? get_selected_content_indicator() : '';
        $data['lti_launchcontainer_div_class'] = "form-group $lti_hidden mt-4";
        $data['lti_launchcontainer_label_class'] = 'col-sm-6 control-label-notes';
        $data['tii_submit_papers_to_div_class'] = "form-group $lti_hidden mt-3";
        $data['tii_submit_papers_to_label_class'] = 'col-sm-6 control-label-notes';
        $data['tii_compare_against_div_class'] = 'col-sm-12 control-label-notes';
        $data['tii_studentpapercheck_checked'] = ((($row->tii_studentpapercheck == 1) or ($assignment_type == ASSIGNMENT_TYPE_ECLASS)) ? 'checked' : '');
        $data['tii_internetcheck_checked'] = ((($row->tii_internetcheck == 1)  or ($assignment_type == ASSIGNMENT_TYPE_ECLASS)) ? 'checked' : '');
        $data['tii_journalcheck_checked'] = ((($row->tii_journalcheck == 1) or ($assignment_type == ASSIGNMENT_TYPE_ECLASS)) ? 'checked' : '');
        $data['tii_institutioncheck_checked'] = (($row->tii_institutioncheck == 1) ? 'checked' : '');
        $data['tii_report_gen_speed_label_class'] = 'col-sm-12 control-label-notes';
        $data['tii_report_gen_speed'] = $row->tii_report_gen_speed;
        $data['tii_s_view_reports_div_class'] = 'col-sm-12 mt-3';
        $data['tii_s_view_reports_checked'] = (($row->tii_s_view_reports == 1) ? 'checked' : '');
        $data['tii_use_biblio_exclusion_checked'] = (($row->tii_use_biblio_exclusion == 1) ? 'checked' : '');
        $data['tii_use_quoted_exclusion'] = (($row->tii_use_quoted_exclusion == 1) ? 'checked' : '');
        $data['tii_use_small_exclusion'] = (($row->tii_exclude_type != 'none') ? 'checked' : '');
        $data['tii_exclude_type_group_div_class'] = 'row form-group ' . (($row->tii_exclude_type == 'none') ? 'hidden' : '') . ' mt-4';
        $data['tii_exclude_type_div_label_class'] = 'col-12 control-label-notes';
        $data['tii_exclude_type_div_inner_class'] = 'col-12';
        $data['tii_exclude_type_words_checked'] = (($row->tii_exclude_type == 'words' || $row->tii_exclude_type == 'none') ? 'checked' : '');
        $data['tii_exclude_type_percentage_checked'] = (($row->tii_exclude_type == 'percentage') ? 'checked' : '');
        $data['tii_exclude_value_group_div_class'] = 'row form-group ' . (($row->tii_exclude_type == 'none') ? 'hidden' : '') . ' mt-4';
        $data['tii_exclude_value_label_div_class'] = 'col-12 control-label-notes';
        $data['tii_exclude_value_div_class'] = 'col-12';
        $data['tii_exclude_value'] = intval($row->tii_exclude_value);
        $data['tii_instructorcustomparameters_group_div_class'] = "form-group $lti_hidden mt-4";
        $data['tii_instructorcustomparameters_label_div_class'] = 'col-12 control-label-notes';
        $data['tii_instructorcustomparameters_div_class'] = 'col-12';
        $data['tii_instructorcustomparameters'] = $row->tii_instructorcustomparameters;
    } else { // new assignment

        $scales = Database::get()->queryArray('SELECT * FROM grading_scale WHERE course_id = ?d', $course_id);
        $scale_options = "";
        foreach ($scales as $scale) {
            $scale_options .= "<option value='$scale->id'>$scale->title</option>";
        }
        $rubrics = Database::get()->queryArray('SELECT * FROM rubric WHERE course_id = ?d', $course_id);
        $rubric_options = "";
        foreach ($rubrics as $rubric) {
            $rubric_options .= "<option value='$rubric->id'>$rubric->name</option>";
        }

        $interval = new DateInterval('P1M');
        $data['tii_fwddate'] = (new DateTime('NOW'))->add($interval)->format('d-m-Y H:i');

        $data['title'] = Session::has('title') ? Session::get('title') : '';
        $data['desc'] = Session::has('desc') ? Session::get('desc') : '';
        $data['tii_exclude_value'] = 0;
        $data['student_papercheck'] = $data['internetcheck'] = $data['journalcheck'] = $data['lti_disabled'] = '';
        $data['report_gen_speed'] = $data['institutioncheck'] = $data['tii_s_view_reports'] = $data['tii_use_biblio_exclusion'] = $data['tii_use_quoted_exclusion'] = $data['tii_exclude_type'] = 'none';
        $data['notification'] = $data['group_submissions'] = $data['assign_to_specific'] = $data['late_submission'] = '';
        $data['rubric_options'] = $rubric_options;
        $data['scale_options'] = $scale_options;
        $data['reviews_per_user'] = Session::has('reviews_per_user') ? Session::get('reviews_per_user') : 3;
        $data['max_grade'] = Session::has('max_grade') ? Session::get('max_grade') : 10;
        $data['scale'] = Session::getError('scale');
        $data['rubric'] = Session::getError('rubric');
        $data['rubric_review'] = Session::getError('rubric_review');
        $data['submission_type'] = Session::has('submission_type') ? Session::get('submission_type') : 0;
        $data['assignment_type'] = Session::has('assignment_type') ? Session::get('assignment_type') : 0;
        $data['grading_type'] = Session::has('grading_type') ? Session::get('grading_type') : 0;
        $data['WorkStart'] = Session::has('WorkStart') ? Session::get('WorkStart') : (new DateTime('NOW'))->format('d-m-Y H:i');
        $data['WorkEnd'] = $WorkEnd = Session::has('WorkEnd') ? Session::get('WorkEnd') : "";
        $data['WorkStart_review'] = Session::has('WorkStart_review') ? Session::get('WorkStart_review') : (new DateTime('NOW'))->format('d-m-Y H:i'); //hmeromhnia enarkshs ths aksiologhshs apo omotimous
        $data['WorkEnd_review'] = $WorkEnd_review = Session::has('WorkEnd_review') ? Session::get('WorkEnd_review') : null; //deadline aksiologhshs apo omotimous
        $data['enableWorkStart'] = Session::has('enableWorkStart') ? Session::get('enableWorkStart') : null;
        $data['enableWorkEnd'] = Session::has('enableWorkEnd') ? Session::get('enableWorkEnd') : ($WorkEnd ? 1 : 0);
        $data['enableWorkStart_review'] = Session::has('enableWorkStart_review') ? Session::get('enableWorkStart_review') : null;
        $data['enableWorkEnd_review'] = Session::has('enableWorkEnd_review') ? Session::get('enableWorkEnd_review') : ($WorkEnd_review ? 1 : 0);
        $data['WorkFeedbackRelease'] = Session::has('WorkFeedbackRelease') ? Session::get('WorkFeedbackRelease') : null;
        $data['work_feedback_release_hidden'] = 'hidden';
        $data['work_feedback_release_disabled'] = Session::has('WorkFeedbackRelease') ? '' : 'disabled';
        $data['assignmentPasswordLock'] = Session::has('assignmentPasswordLock') ? Session::get('assignmentPasswordLock') : '';
        $assignmentIPLock = Session::has('assignmentIPLock') ? Session::get('assignmentIPLock') : array();
        $data['assignmentIPLockOptions'] = implode('', array_map(
            function ($item) {
                $item = trim($item);
                return $item ? ('<option selected>' . q($item) . '</option>') : '';
            }, $assignmentIPLock));

        //enableCheckFileSize();

        $data['fileCount'] = Session::has('fileCount') ? Session::get('fileCount') : 2;
        $data['language'] = $language;
        $data['assignment_filename'] = '';
        $data['auto_judge_scenarios'] = null;
        $data['auto_judge'] = false;
        $data['submit_name'] = 'new_assign';

        $data['lti_template_options'] = resolve_lti_template_options($lti_templates, null);
        $data['assignment_type'] = Session::has('assignment_type') ? Session::get('assignment_type') : 0;
        $data['lti_hidden'] = 'hidden';
        $data['lti_disabled'] = 'disabled';
        $data['lti_launchcontainer'] = LTI_LAUNCHCONTAINER_EMBED;
        $data['tii_submit_papers_to'] = TII_SUBMIT_PAPERS_STANDARD;
        $data['assignment_type_radios_class'] = 'col-12';
        $data['assignment_type_eclass_div_class'] = 'radio';
        $data['assignment_type_turnitin_div_class'] = 'radio';
        $data['help_block_div_class'] = 'col-12';
        $data['ltiopts_div_class'] = 'TextBold large-text';
        $data['tiiapp_label_class'] = 'col-sm-12 control-label-notes';
        $data['lti_template_div_class'] = 'col-12';
        $data['tii_selected_content'] = '';
        $data['lti_launchcontainer_div_class'] = "form-group hidden mt-3";
        $data['lti_launchcontainer_label_class'] = 'col-sm-12 control-label-notes';
        $data['tii_submit_papers_to_div_class'] = "form-group hidden mt-4";
        $data['tii_submit_papers_to_label_class'] = 'col-sm-12 control-label-notes mb-1';
        $data['tii_compare_against_div_class'] = 'col-sm-12 control-label-notes mb-1';
        $data['tii_studentpapercheck_checked'] = 'checked';
        $data['tii_internetcheck_checked'] = 'checked';
        $data['tii_journalcheck_checked'] = 'checked';
        $data['tii_institutioncheck_checked'] = 'checked';
        $data['tii_report_gen_speed_label_class'] = 'col-sm-12 control-label-notes mb-1';
        $data['tii_report_gen_speed'] = TII_REPORT_GEN_IMMEDIATELY_NO_RESUBMIT;
        $data['tii_s_view_reports_div_class'] = 'col-sm-12 mt-4';
        $data['tii_s_view_reports_checked'] = '';
        $data['tii_use_biblio_exclusion_checked'] = '';
        $data['tii_use_quoted_exclusion'] = '';
        $data['tii_use_small_exclusion'] = '';
        $data['tii_exclude_type_group_div_class'] = "form-group hidden mt-4";
        $data['tii_exclude_type_div_label_class'] = 'col-sm-12 control-label-notes mb-1';
        $data['tii_exclude_type_div_inner_class'] = 'col-sm-12';
        $data['tii_exclude_type_words_checked'] = 'checked';
        $data['tii_exclude_type_percentage_checked'] = '';
        $data['tii_exclude_value_group_div_class'] = "form-group hidden mt-4";
        $data['tii_exclude_value_label_div_class'] = 'col-sm-6 control-label-notes';
        $data['tii_exclude_value_div_class'] = 'col-sm-12';
        $data['tii_exclude_value'] = 0;
        $data['tii_instructorcustomparameters_group_div_class'] = "form-group hidden mt-4";
        $data['tii_instructorcustomparameters_label_div_class'] = 'col-sm-6 control-label-notes';
        $data['tii_instructorcustomparameters_div_class'] = 'col-sm-12';
        $data['tii_instructorcustomparameters'] = '';
    }

    $data['autojudge_enabled'] = false;
    if ($autojudge->isEnabled()) {
        $supported_languages = $autojudge->getSupportedLanguages();
        if (!isset($supported_languages['error'])) {
            $supported_languages = "<select id='lang' name='lang'>" .
                implode(array_map(function ($lang) {
                    $lang = q($lang);
                    return "<option value='$lang'>$lang</option>\n";
                }, array_keys($supported_languages))) .
                "</select>";
            $data['autojudge_supported_languages'] = $supported_languages;
            $data['autojudge_enabled'] = true;
        }
    }
    $data['autojudge'] = $autojudge;
    $data['grading_scales_exist'] = grading_scales_exist();
    $data['rubrics_exist'] = rubrics_exist();
    $data['assignee_options'] = $assignee_options;
    $data['unassigned_options'] = $unassigned_options;
    $data['turnitinapp'] = ExtAppManager::getApp(strtolower(TurnitinApp::NAME));
    //Get possible validation errors
    $data['title_error'] = Session::getError('title');
    $data['max_grade_error'] = Session::getError('max_grade');
    $data['scale_error'] = Session::getError('scale');
    $data['rubric_error'] = Session::getError('rubric');
    $data['review_error_user'] = Session::getError('reviews_per_user');
    $data['review_error_rubric'] = Session::getError('rubric_review');
    $data['work_feedback_release_haserror'] = Session::getError('WorkFeedbackRelease') ? 'has-error' : '';
    $data['work_feedback_release_checked'] = (Session::has('enableWorkFeedbackRelease') ? Session::get('enableWorkFeedbackRelease') : ($data['WorkFeedbackRelease'] ? 1 : 0)) ? 'checked' : '';
    $data['lti_templates'] = $lti_templates;

    rich_text_editor(null, null, null, null);

    $data['tagsAssignment'] = eClassTag::tagInput($assignment_id);

    view('modules.work.new_edit_assignment', $data);
}


/**
 * @brief display user assignment
 * @param type $id
 */
function display_student_assignment($id, $on_behalf_of = false) {

    global $uid, $urlAppend, $langNoneWorkGroupNoSubmission,
           $course_id, $course_code, $unit, $langAssignmentWillBeActive, $langOnBehalfOf,
           $langWrongPassword, $langIPHasNoAccess, $langNoPeerReview,
           $langNoneWorkUserNoSubmission, $langGroupSpaceLink, $langPendingPeerSubmissions,
           $is_editor, $langGroupAssignmentPublish, $langGroupAssignmentNoGroups,
           $langThisIsGroupAssignment;

    $_SESSION['has_unlocked'] = array();

    $group_select_form = $form_link = $back_link = $group_select_hidden_input = $grade_field = '';
    $submissions_exist = false;
    $submit_ok = false;
    $submission_details_data = $assignment_review_data = [];
    $cdate = date('Y-m-d H:i:s');
    $is_group_assignment = is_group_assignment($id);
    $user_group_info = user_group_info($uid, $course_id);
    if (!empty($user_group_info)) {
        $gids_sql_ready = implode(',',array_keys($user_group_info));
    } else {
        $gids_sql_ready = "''";
    }

    if ($on_behalf_of && $is_editor) {
        $row = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                                     FROM assignment
                                                     WHERE course_id = ?d
                                                        AND id = ?d",
        $course_id, $id);
    } else {
        $row = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                                                         CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
                                                         CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due
                                                     FROM assignment
                                                     WHERE course_id = ?d
                                                        AND id = ?d
                                                        AND active = 1
                                                        AND (assign_to_specific = 0 OR
                                                             id IN
                                                               (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                                                UNION
                                                                SELECT assignment_id FROM assignment_to_specific
                                                                   WHERE group_id != 0 AND group_id IN ($gids_sql_ready)))",
            $course_id, $id, $uid);
    }
    $data['count_of_assign'] = $count_of_assign = countSubmissions($id);
    $_SESSION['has_unlocked'][$id] = true;

    if ($row) {
        $grading_type = $row->grading_type;
        $data['reviews_per_assignment'] = $reviews_per_assignment = $row->reviews_per_assignment;
        if ($row->password_lock !== '' and (!isset($_POST['password']) or $_POST['password'] !== $row->password_lock) and !$on_behalf_of) {
            $_SESSION['has_unlocked'][$id] = false;
            Session::flash('message',$langWrongPassword);
            Session::flash('alert-class', 'alert-warning');
            if (isset($unit)) {
                redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit");
            } else {
                redirect_to_home_page("modules/work/index.php?course=" . $course_code);
            }
        }

        if ($row->ip_lock) {
            $user_ip = Log::get_client_ip();
            if (!match_ip_to_ip_or_cidr($user_ip, explode(',', $row->ip_lock))) {
                Session::flash('message', $langIPHasNoAccess);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page('modules/work/index.php?course=' . $course_code);
            }
        }

        $WorkStart = new DateTime($row->submission_date);
        $current_date = new DateTime('NOW');
        if ($WorkStart > $current_date) {
            Session::flash('message',$langAssignmentWillBeActive . ' ' . $WorkStart->format('d-m-Y H:i'));
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/work/index.php?course=$course_code");
        }


        $user = Database::get()->querySingle("SELECT * FROM assignment_submit
            WHERE assignment_id = ?d AND uid = ?d
            ORDER BY id LIMIT 1", $id, $uid);
        if ($user) {
            $data = display_assignment_details($row, false); // emfanizodai hmeromhnies start, due otan uparxei peer review
        } else {
            $data = display_assignment_details($row, true); // den emfanizontai oi hmeromhnies start, due otan o foithths den exei upovalei parolo pou uparxei peer review
        }

        $submit_ok = ($row->time > 0 || !(int) $row->deadline || $row->time <= 0 && $row->late_submission) || $on_behalf_of;

        foreach (find_submissions($row->group_submissions, $uid, $id, $user_group_info) as $sub) {
            if ($row->submission_type == 2) {
                $submissions_exist = submission_count($sub->id);
            } else {
                $submissions_exist = true;
            }
            if ($sub->grade != '' && $row->assignment_type != ASSIGNMENT_TYPE_TURNITIN) {
                $submit_ok = false;
            }
            $submission_details_data = display_submission_details($sub->id);
        }

        if ($submit_ok) {
            if (!$_SESSION['courses'][$course_code]) {
                return;
            }
            if ($is_group_assignment) {
                if (!$on_behalf_of) {
                    if (count($user_group_info) == 1) {
                        $gids = array_keys($user_group_info);
                        $group_select_hidden_input = "<input type='hidden' name='group_id' value='$gids[0]' />";
                    } elseif ($user_group_info) {
                        $group_select_form = "
                        <div class='form-group mt-4'>
                            <label for='group_id' class='col-sm-12 control-label-notes'>$langGroupSpaceLink:</label>
                            <div class='col-sm-12'>
                              " . selection($user_group_info, 'group_id') . "
                            </div>
                        </div>";
                    } else {
                        $group_link = $urlAppend . 'modules/group/';
                        $GroupWorkWarning = "<span>$langThisIsGroupAssignment<br>" .
                            sprintf(count($user_group_info) ?
                                $langGroupAssignmentPublish :
                                $langGroupAssignmentNoGroups, $group_link) .
                            "</span>";
                        Session::flash('message', $GroupWorkWarning);
                        Session::flash('alert-class', 'alert-warning');
                    }
                } else {
                    $groups_with_no_submissions = groups_with_no_submissions($id);
                    if (count($groups_with_no_submissions)>0) {
                        $group_select_form = "
                        <div class='form-group mt-4'>
                            <label for='group_id' class='col-sm-12 control-label-notes'>$langGroupSpaceLink:</label>
                            <div class='col-sm-12'>
                              " . selection($groups_with_no_submissions, 'group_id') . "
                            </div>
                        </div>";
                    } else {
                        Session::flash('message', $langNoneWorkGroupNoSubmission);
                        Session::flash('alert-class', 'alert-danger');
                        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
                    }
                }
            } elseif ($on_behalf_of) {
                $users_with_no_submissions = users_with_no_submissions($id);
                if (count($users_with_no_submissions) > 0) {
                    $group_select_form = "
                    <div class='form-group my-4'>
                        <label for='user_id' class='col-sm-12 control-label-notes'>$langOnBehalfOf:</label>
                        <div class='col-sm-12'>
                          " .selection($users_with_no_submissions, 'user_id', '', "class='form-control'") . "
                        </div>
                    </div>";
                    if ($grading_type == ASSIGNMENT_SCALING_GRADE) {
                        $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $row->grading_scale_id, $course_id)->scales;
                        $scales = unserialize($serialized_scale_data);
                        $scale_options = "<option value> - </option>";
                        foreach ($scales as $scale) {
                            $scale_options .= "<option value='$scale[scale_item_value]'>$scale[scale_item_name]</option>";
                        }
                        $grade_field = "<select name='grade' class='form-select' id='scales'>$scale_options</select>";
                    } elseif ($grading_type == ASSIGNMENT_RUBRIC_GRADE) {
                        $valuegrade = (isset($grade)) ? $grade : '';
                        $grade_field = "<input class='form-control' type='text' value='$valuegrade' name='grade' maxlength='4' size='3' readonly>";
                    } else {
                        $grade_field = "<input class='form-control' type='text' name='grade' maxlength='4' size='3'>";
                    }
                } else {
                    Session::flash('message', $langNoneWorkUserNoSubmission);
                    Session::flash('alert-class', 'alert-danger');
                    redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
                }
            }
        }

        if ($is_editor) {
            $back_link = $form_link = "index.php?course=$course_code&id=$id";
        } else {
            if (isset($unit)) {
                $back_link = "../units/index.php?course=$course_code&id=$unit";
                $form_link = "../units/view.php?course=$course_code";
            } else {
                $back_link = $form_link = "{$urlAppend}modules/work/index.php?course=$course_code";
            }
        }

        $assignment_details_data = display_assignment_details($row);

        // h sunarthhsh theloume na kaleitai an einai peer review kai an exei
        // upovalei ergasia o foithths dhladh an einai true h $submissions_exist
        $data['ass'] = $ass = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE assignment_id = ?d AND uid = ?d ", $id, $uid);
        $rows = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE assignment_id = ?d ", $id);
        $data['start_date_review'] = $row->start_date_review;
        if ($grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $submissions_exist && $ass) {
            $assignment_review_data = display_assignment_review($id);
        }
    } else {
        redirect_to_home_page("modules/work/index.php?course=$course_code");
    }

    if (!is_null($assignment_review_data)) {
        $data = $submission_details_data+$assignment_details_data+$assignment_review_data;
    } else {
        $data = $submission_details_data+$assignment_details_data;
    }

    $data['row'] = $row;
    $data['cdate'] = $cdate;
    $data['assignment_type'] = $row->assignment_type;
    $data['grading_type'] = $grading_type;
    $data['max_grade'] = $row->max_grade;
    $data['submission_type'] = $row->submission_type;
    $data['max_submissions'] = $row->max_submissions;
    $data['count_user_group_info'] = count($user_group_info);
    $data['is_group_assignment'] = $is_group_assignment;
    $data['group_select_hidden_input'] = $group_select_hidden_input;
    $data['group_select_form'] = $group_select_form;
    $data['grade_field'] = $grade_field;
    $data['rich_text_editor'] = rich_text_editor('submission_text', 10, 20, '');
    $data['id'] = $id;
    $data['on_behalf_of'] = $on_behalf_of;
    $data['submissions_exist'] = $submissions_exist;
    $data['form_link'] = $form_link;
    $data['back_link'] = $back_link;
    $data['submit_ok'] = $submit_ok;
    $data['assignment_link'] = isset($unit) ?
        "{$urlAppend}modules/units/index.php?course=$course_code&id=$unit" :
        "{$urlAppend}modules/work/index.php?course=$course_code&id=$id";

    view('modules.work.submit_assignment', $data);
}

/**
 *sunarthsh foithth
 * @param type $id
 * @param type $display_graph_results
 */
function display_assignment_review($id) {
    global $course_id, $course_code, $uid, $langQuestionView,
           $langSGradebookBook, $langEdit, $urlAppend;

    $assign = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                                FROM assignment
                                                WHERE course_id = ?d
                                                AND id = ?d",
                                            $course_id, $id);
    $i = 1;
    $html_content = '';
    $result = Database::get()->queryArray("SELECT * from assignment_grading_review WHERE assignment_id = ?d && users_id = ?d",$id, $uid);;
    //auta einai ta onomata panw sto pedio tou pinaka bathmos hmeromhnia...
    foreach ($result as $row) {
        $html_content .= "<input type='hidden' name='assignment' value='$row->id'>";
        if ($assign->submission_type) {
            $filelink = "<a href='#' class='onlineText btn btn-sm btn-default' data-id='$row->id'>$langQuestionView</a>";
        } else {
            if (empty($row->file_name)) {
                $filelink = '&nbsp;';
            } else {
                if (isset($_GET['unit'])) {
                    $unit = intval($_GET['unit']);
                    $fileUrl = "{$urlAppend}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;id=$unit&amp;get=$row->user_submit_id";
                } else {
                    $fileUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$row->user_submit_id";
                }
                $namelen = mb_strlen($row->file_name);
                if ($namelen > 30) {
                    $extlen = mb_strlen(get_file_extension($row->file_name));
                    $basename = mb_substr($row->file_name, 0, $namelen - $extlen - 3);
                    $ext = mb_substr($row->file_name, $namelen - $extlen - 3);
                    $filename = ellipsize($basename, 27, '...' . $ext);
                } else {
                    $filename = $row->file_name;
                }
                $filelink = MultimediaHelper::chooseMediaAhrefRaw($fileUrl, $fileUrl, $filename, $row->file_name);
            }
        }
        if (isset($_GET['unit'])) {
            $edit_grade_link = "../units/view.php?course=$course_code&amp;res_type=assignment_grading&amp;unit=$unit&amp;assignment=$id&amp;submission=$row->id";
        } else {
            $edit_grade_link = "grade_edit_review.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
        }
        $icon_field = "<a class='link' href='$edit_grade_link' aria-label='$langEdit'><span class='fa fa-fw fa-edit' data-bs-title='$langEdit' title='' data-bs-toggle='tooltip'></span></a>";

        $grade = Database::get()->querySingle("SELECT grade FROM assignment_grading_review WHERE id = ?d ", $row->id)->grade;
        if (!empty($grade)) {
            $grade_field = "<input class='form-control' type='text' value='$grade' name='grade' maxlength='4' size='3' disabled>";
        } else {
            $icon_field = '';
            if (isset($_GET['unit'])) {
                $grade_link = "../units/view.php?course=$course_code&amp;res_type=assignment_grading&amp;unit=$unit&amp;assignment=$id&amp;submission=$row->id";
            } else {
                $grade_link = "grade_edit_review.php?course=$course_code&amp;assignment=$id&amp;submission=$row->id";
            }
            $grade_field = "<a class='link' href='$grade_link' aria-label='$langSGradebookBook'><span class='fa fa-fw fa-plus' data-bs-title='$langSGradebookBook' title='' data-bs-toggle='tooltip'></span></a>";
        }
        $html_content .= "<tr>
            <td class='text-end'>$i.</td>
            <td>$filelink</td>
            <td class='col-1'>
              <div class='form-group ".(Session::getError("grade.$row->id") ? "has-error" : "")."'>
                $grade_field
                <span class='help-block Accent-200-cl'>".Session::getError("grade.$row->id")."</span>
              </div>
            </td>
            <td>$icon_field</td>
        </tr>";
        $i++;
    } //end foreach

    $data['ass'] = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE assignment_id = ?d AND uid = ?d ", $id, $uid);
    $data['reviews_per_assignment'] = Database::get()->querySingle("SELECT reviews_per_assignment FROM assignment WHERE id = ?d", $id)->reviews_per_assignment;
    $data['cdate'] = date('Y-m-d H:i:s');
    $data['count_of_assign'] = countSubmissions($id);
    $data['submission_type'] = $assign->submission_type;
    $data['grading_type'] = $assign->grading_type;
    $data['start_date_review'] = $assign->start_date_review;
    $data['result'] = $result;
    $data['html_content'] = $html_content;

    return $data;
}

/**
 * @brief display assignment submissions - Teacher view
 * @param type $id
 */
function display_assignment_submissions($id) {

    global $course_id, $autojudge, $langgrade;

    $grade_review_field = $condition = $review_message = '';
    $assign = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                                                        CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
                                                        CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due,
                                                        auto_judge
                                                    FROM assignment
                                                      WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $data = display_assignment_details($assign);
    $count_of_assignments = countSubmissions($id);
    $data['result'] = [];
    if ($count_of_assignments > 0) {
        $data['result'] = Database::get()->queryArray("SELECT assign.id id, assign.file_name file_name,
                                                assign.uid uid, assign.group_id group_id,
                                                assign.submission_date submission_date,
                                                assign.grade_submission_date grade_submission_date,
                                                assign.grade grade, assign.comments comments,
                                                assign.grade_comments grade_comments,
                                                assign.grade_comments_filename grade_comments_filename,
                                                assign.grade_comments_filepath grade_comments_filepath,
                                                assignment.grading_scale_id grading_scale_id,
                                                assignment.deadline deadline,
                                                assignment.grading_type
                                               FROM assignment_submit AS assign, user, assignment
                                               WHERE assign.assignment_id = ?d AND assign.assignment_id = assignment.id AND user.id = assign.uid
                                               ORDER BY surname", $id);

        $data['rows_assignment_grading_review'] = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE assignment_id = ?d ", $id);
    }
    $data['seen'] = [];
    $data['start_date_review'] = $assign->start_date_review;
    $data['due_date_review'] = $assign->due_date_review;
    $data['reviews_per_assignment'] = $assign->reviews_per_assignment;
    // disabled grades submit if turnitin
    $data['disabled'] = ($assign->assignment_type == ASSIGNMENT_TYPE_TURNITIN) ? ' disabled': '';
    $data['id'] = $id;
    $data['autojudge'] = $autojudge;
    $data['grade_review_field'] = $grade_review_field;
    $data['condition'] = $condition;
    $data['review_message'] = $review_message;
    $data['count_of_assignments'] = $count_of_assignments;
    $data['row'] = $assign;
    $data['assign'] = $assign;
    $data['cdate'] = date('Y-m-d H:i:s');
    $data['grades_info'] = $grades_info = [];

    if ($assign->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
        $users_submissions = Database::get()->queryArray("SELECT user_id FROM assignment_grading_review WHERE assignment_id = ?d", $id);
        $users_grades = Database::get()->queryArray("SELECT id,assignment_id,user_id,file_name,users_id,grade FROM assignment_grading_review WHERE assignment_id = ?d", $id);
        if (count($users_submissions) > 0 && count($users_grades) > 0) {
            foreach ($users_submissions as $u) {
                $arr = [];
                $f_g_grade = 0;
                $g_grade = 0;
                $grade_counter = 0;
                foreach ($users_grades as $g) {
                    if ($u->user_id == $g->user_id) {
                        if ($g->grade) {
                            $grade_counter++;
                            $g_grade = $g_grade + $g->grade;
                            $f_g_grade = floor(($g_grade / $grade_counter) * 100) / 100; // truncate to 2 decimal places
                            $arr[] = "<strong>" . uid_to_name($g->users_id) . "</strong> $langgrade -> " . "<span class='TextBold fs-6 Success-200-cl'>" . $g->grade . "</span><br>";
                        }
                        $str_arr = (count($arr) > 0) ? implode('', $arr) : '-';
                        $grades_info[$u->user_id] = [
                            'grade_received' => $str_arr,
                            'grade_total' => $f_g_grade
                        ];
                    }
                }
            }
        }
        $data['grades_info'] = $grades_info;
    }

    view('modules.work.assignment_submissions', $data);
}


/**
 * @brief display assignment details, $row holds assignment data
 * @param type $row
 * @return array
 */
function display_assignment_details($row, $x=false): array
{
    global $head_content, $course_code, $course_id, $urlAppend, $is_editor,
           $langDelAssign, $langBack, $langZipDownload, $langExportGrades,
           $langAddGrade, $langImportGrades, $langGraphResults, $langWorksDelConfirm,
           $langWorkUserGroupNoSubmission;

    load_js('screenfull/screenfull.min.js');
    $head_content .= "<script>$(function () {
            initialize_filemodal({
                download: '$GLOBALS[langDownload]',
                print: '$GLOBALS[langPrint]',
                fullScreen: '$GLOBALS[langFullScreen]',
                newTab: '$GLOBALS[langNewTab]',
                cancel: '$GLOBALS[langCancel]'
            });
        });</script>";


    if ($is_editor) {
        if (isset($_GET['disp_results']) or isset($_GET['disp_non_submitted'])) {
            $action_bar = action_bar(array(
                array(
                    'title' => $langBack,
                    'icon' => 'fa-reply',
                    'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id",
                    'level' => 'primary-label'
                )
            ));
        } else {
            $action_bar = action_bar(array(
                array(
                    'title' => $langZipDownload,
                    'icon' => 'fa-file-zipper',
                    'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;download=$row->id",
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'
                ),
                array(
                    'title' => $langExportGrades,
                    'icon' => 'fa-file-excel',
                    'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=export",
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'
                ),
                array(
                    'title' => $langAddGrade,
                    'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=add",
                    'icon' => 'fa-plus-circle'
                ),
                array(
                    'title' => $langImportGrades,
                    'icon' => 'fa-upload',
                    'url' => "import.php?course=$course_code&amp;id=$row->id",
                    'show' => ($row->grading_type == 0)
                ),
                array(
                    'title' => $langGraphResults,
                    'icon' => 'fa-bar-chart',
                    'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;disp_results=true"
                ),
                array(
                    'title' => $langWorkUserGroupNoSubmission,
                    'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;disp_non_submitted=true",
                    'icon' => 'fa-minus-square'
                ),
                array(
                    'title' => $langDelAssign,
                    'icon' => 'fa-xmark',
                    'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$row->id&amp;choice=do_delete",
                    'text-class' => 'text-danger',
                    'button-class' => "deleteAdminBtn",
                    'confirm' => "$langWorksDelConfirm"
                )
            ));
        }
    } else {
        if (isset($_GET['unit'])) {
            $back_url = "../units/index.php?course=$course_code&amp;id=$_GET[unit]";
        } else {
            $back_url = "$_SERVER[SCRIPT_NAME]?course=$course_code";
        }

        $action_bar = action_bar(array(
            array(
                'title' => $langBack,
                'icon' => 'fa-reply',
                'url' => "$back_url",
                'level' => 'primary-label'
            )
        ));
    }

    $criteria_list = '';
    $data['rubric_name'] = $data['rubric_desc'] = $data['criteria_list'] = '';
    $data['preview_rubric'] = 0;
    $data['grade_type'] = $grade_type = $row->grading_type;
    if ($grade_type == ASSIGNMENT_RUBRIC_GRADE) {
        $rubric_id = $row ->grading_scale_id;
        $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $rubric_id);
        if ($rubric) {
            $data['rubric_name'] =  $rubric->name;
            $data['rubric_desc'] = $rubric -> description;
            $data['preview_rubric'] = $preview_rubric = $rubric -> preview_rubric;
            $points_to_graded = $rubric -> points_to_graded;
            $criteria = unserialize($rubric->scales);
            foreach ($criteria as $ci => $criterio) {
                $criteria_list .= "<li><b>$criterio[title_name] ($criterio[crit_weight]%)</b></li>";
                if(is_array($criterio['crit_scales'])) {
                    $criteria_list .= "<li><ul>";
                    foreach ($criterio['crit_scales'] as $si=>$scale) {
                        if ($preview_rubric ==1 AND $points_to_graded == 1) {
                            $criteria_list .= "<li>$scale[scale_item_name] ( $scale[scale_item_value] )</li>";
                        } elseif ($preview_rubric ==1 AND $points_to_graded == 0) {
                            $criteria_list .= "<li>$scale[scale_item_name]</li>";
                        } else {
                            $criteria_list .= "";
                        }
                    }
                    $criteria_list .= "</ul></li>";
                }
            }
        }
    } elseif ($grade_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
        $rubric_id = $row ->grading_scale_id;
        $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $rubric_id);
        if ($rubric) {
            $data['rubric_name'] = $rubric->name;
            $data['rubric_desc'] = $rubric -> description;
            $data['preview_rubric'] = $preview_rubric = $rubric -> preview_rubric;
            $points_to_graded = $rubric -> points_to_graded;
            $criteria = unserialize($rubric->scales);
            foreach ($criteria as $ci => $criterio) {
                $criteria_list .= "<li><b>$criterio[title_name] ($criterio[crit_weight]%)</b></li>";
                if(is_array($criterio['crit_scales'])) {
                    $criteria_list .= "<li><ul>";
                    foreach ($criterio['crit_scales'] as $si=>$scale) {
                        if ($preview_rubric ==1 AND $points_to_graded == 1) {
                            $criteria_list .= "<li>$scale[scale_item_name] ( $scale[scale_item_value] )</li>";
                        } elseif ($preview_rubric ==1 AND $points_to_graded == 0) {
                            $criteria_list .= "<li>$scale[scale_item_name]</li>";
                        } else {
                            $criteria_list .= "";
                        }
                    }
                    $criteria_list .= "</ul></li>";
                }
            }
        }
    }

    $moduleTag = new ModuleElement($row->id);

    if (isset($_GET['unit'])) {
        $unit = intval($_GET['unit']);
        $fileUrl = "{$urlAppend}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;get=$row->id&amp;file_type=1&amp;id=$unit";
    } else {
        $fileUrl = "{$urlAppend}modules/work/index.php?course=$course_code&amp;get=$row->id&amp;file_type=1";
    }
    $data['fileUrl'] = $fileUrl;
    $data['criteria_list'] = $criteria_list;
    $data['tags_list'] = $moduleTag->showTags();
    $data['x'] = $x;
    $data['action_bar'] = $action_bar;

    return $data;
}


/**
 * @brief display all assignments (main index.php)
 */
function display_assignments($editor = true) {

    global $langNewAssign, $course_code, $course_id, $uid,
           $langGradeScales, $langGradeRubrics, $urlAppend;

    // ordering assignments by deadline, without deadline, expired.
    // query uses pseudo limit in ordering results
    // (see https://dev.mysql.com/doc/refman/5.7/en/union.html)
    if ($editor) { // editor query
        $data['result'] = $result = Database::get()->queryArray("
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment
                WHERE course_id = ?d
                    AND UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) > 0
                ORDER BY time
                DESC
                LIMIT 10000
            )
            UNION
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment
                WHERE course_id = ?d
                    AND deadline IS NULL
                ORDER BY title
                ASC
                LIMIT 10000
            )
            UNION
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment
                WHERE course_id = ?d
                    AND UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) < 0
                ORDER BY time
                DESC
                LIMIT 10000
            )", $course_id, $course_id, $course_id);

        $data['action_bar'] = action_bar(array(
            array('title' => $langNewAssign,
                'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;add=1",
                'button-class' => 'btn-success',
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label'),
            array('title' => $langGradeScales,
                'url' => "grading_scales.php?course=$course_code",
                'icon' => 'fa-sort-alpha-asc'),
            array('title' => $langGradeRubrics,
                'url' => "rubrics.php?course=$course_code",
                'icon' => 'fa-brands fa-readme'),
        ),false);

        view('modules.work.index', $data);

    } else { // student query
        if (get_config('eportfolio_enable')) {
            $data['columns'] = 'null, null, null, null, { orderable: false }';
        } else {
            $data['columns'] = 'null, null, null, null';
        }

        $data['gids'] = $gids = user_group_info($uid, $course_id);
        if (!empty($gids)) {
            $gids_sql_ready = implode(',',array_keys($gids));
        } else {
            $gids_sql_ready = "''";
        }

        $data['result'] = Database::get()->queryArray("
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment WHERE course_id = ?d
                        AND UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) > 0
                        AND active = '1' AND
                        (assign_to_specific = 0 OR id IN
                            (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                UNION
                            SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                        )
                    ORDER BY time
                    DESC
                    LIMIT 1000
            )
            UNION
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment WHERE course_id = ?d
                        AND deadline IS NULL
                        AND active = '1' AND
                        (assign_to_specific = 0 OR id IN
                            (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                UNION
                            SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                        )
                    ORDER BY title
                    ASC
                    LIMIT 1000
            )
            UNION
            (
                SELECT *, UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS time
                    FROM assignment WHERE course_id = ?d
                        AND UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) < 0
                        AND active = '1' AND
                        (assign_to_specific = 0 OR id IN
                            (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                UNION
                            SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                        )
                    ORDER BY time
                    DESC
                    LIMIT 1000
            )
            ", $course_id, $uid, $course_id, $uid, $course_id, $uid);

        view('modules.work.index_st', $data);
    }
}


/**
 * @brief Show details of a submission
 * @param type $id
 */
function display_submission_details($id) {

    global $uid, $course_id, $langSubmittedAndGraded, $course_code,
           $urlAppend, $langOfGroup, $langGroupSubmit, $langYourGroup,
           $head_content, $langSubmitted,$langSubmittedByOtherMember, $langClose,
           $langDownload, $langPrint, $langFullScreen, $langNewTab, $langCancel;

    load_js('tools.js');
    load_js('screenfull/screenfull.min.js');

    $head_content .= "<script type='text/javascript'>
    $(function() {
        $('.onlineText').click(function(e){
            e.preventDefault();
            var sid = $(this).data('id');
            var assignment_title = $('#assignment_title').text();
            $.ajax({
              type: 'POST',
              url: '',
              datatype: 'json',
              data: {
                 sid: sid
              },
              success: function(data){
                data = $.parseJSON(data);
                bootbox.alert({
                    title: assignment_title,
                    size: 'large',
                    message: data.submission_text? data.submission_text: '',
                    buttons: {
                                ok: {
                                    label: '". js_escape($langClose). "',
                                    className: 'submitAdminBtnDefault'
                                }
                            }
                });
              },
              error: function(xhr, textStatus, error){
                  console.log(xhr.statusText);
                  console.log(textStatus);
                  console.log(error);
              }
            });
        });
        initialize_filemodal({
            download: '$langDownload',
            print: '$langPrint',
            fullScreen: '$langFullScreen',
            newTab: '$langNewTab',
            cancel: '$langCancel'});
        });
        </script>";

    $sub = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $id);
    if (!$sub) {
        die("Error: submission $id doesn't exist.");
    }
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $sub->assignment_id);

    $notice = $langSubmitted;
    $grade = $grade_comments = '';
    $data['submission_comments'] = $sub->comments;
    if (!empty($sub->grade_comments)) {
        $grade_comments = q($sub->grade_comments);
    }
    if (!empty($sub->grade)) {
        $notice = $langSubmittedAndGraded;
        if ($assignment->grading_type == ASSIGNMENT_SCALING_GRADE) {
            $serialized_scale_data = Database::get()->querySingle("SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d", $assignment->grading_scale_id, $course_id)->scales;
            $scales = unserialize($serialized_scale_data);
            foreach ($scales as $scale) {
                if ($sub->grade == $scale['scale_item_value']) {
                    $grade = $scale['scale_item_name'];
                    break;
                }
            }
        } else {
            $grade = $sub->grade;
        }
    }

    // Κατάσταση υποβολής εργασίας από ομάδες
    if ($sub->uid != $uid) {
        $notice .= "<br><br>$langSubmittedByOtherMember " .
            "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>" .
            "$langYourGroup " . gid_to_name($sub->group_id) . "</a> (" . uid_to_name($sub->uid) . ")";
    } elseif ($sub->group_id) {
        $notice .= "<br>$langGroupSubmit $langOfGroup <em>" . gid_to_name($sub->group_id) . "</em>.";
    }
    if (!empty($sub->grade_rubric)) {
        $sel_criteria = unserialize($sub->grade_rubric);
    } else {
        $sel_criteria = [];
    }

    $rubric_id = $assignment -> grading_scale_id;
    $preview_rubric = $points_to_graded = $criteria_list = '';
    $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $rubric_id);
    if ($rubric) {
        $rubric_name =  $rubric->name;
        $rubric_desc = $rubric->description;
        $criteria = unserialize($rubric->scales);
        foreach ($criteria as $ci => $criterio) {
            $criteria_list .= "<li><b>$criterio[title_name] ($criterio[crit_weight]%)</b></li>";
            if(is_array($criterio['crit_scales'])){
                $criteria_list .= "<li><ul>";
                foreach ($criterio['crit_scales'] as $si=>$scale) {
                    if (!isset($sel_criteria[$ci])) {
                        $sel_criteria[$ci] = '';
                    }
                    if ($sel_criteria[$ci]==$si) {
                        $criteria_list .= "<li><strong>$scale[scale_item_name] ( $scale[scale_item_value] )</strong></li>";
                    } else {
                        $criteria_list .= "<li>$scale[scale_item_name] ( $scale[scale_item_value] )</li>";
                    }
                }
                $criteria_list .= "</ul></li>";
            }
        }
        $preview_rubric = $rubric->preview_rubric;
        $points_to_graded = $rubric->points_to_graded;
    }

    if ($sub->grade_comments_filename) {
        if (isset($_GET['unit'])) {
            $unit = intval($_GET['unit']);
            $file_comments_url = "{$urlAppend}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;getcomment=$sub->id&amp;id=$unit";
        } else {
            $file_comments_url = "{$urlAppend}modules/work/index.php?course=$course_code&amp;getcomment=$sub->id";
        }
        $file_comments_link = '<div class="mt-2">' . MultimediaHelper::chooseMediaAhrefRaw($file_comments_url, $file_comments_url, $sub->grade_comments_filename, $sub->grade_comments_filename) . "</div>";
    } else {
        $file_comments_link = '';
    }

    $data['file_comments_link'] = $file_comments_link;

    if ($assignment->submission_type == ASSIGNMENT_RUBRIC_GRADE) {    // multiple files
        $links = implode('<br>',
            array_map(function ($item) {
                global $course_code, $urlAppend;
                if (isset($_GET['unit'])) {
                    $url = $urlAppend . "modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;get=$item->id";
                } else {
                    $url = $urlAppend . "modules/work/index.php?course=$course_code&amp;get=$item->id";
                }
                return MultimediaHelper::chooseMediaAhrefRaw($url, $url, $item->file_name, $item->file_name);
            }, Database::get()->queryArray('SELECT id, file_name FROM assignment_submit
                    WHERE assignment_id = ?d AND uid = ?d AND group_id = ?d ORDER BY id',
                $sub->assignment_id, $sub->uid, $sub->group_id)));
        $data['links'] = $links;
    } elseif ($assignment->submission_type == ASSIGNMENT_STANDARD_GRADE) {
        // single file
        if (isset($_GET['unit'])) {
            $url = "{$urlAppend}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;get=$sub->id";
        } else {
            $url = "{$urlAppend}modules/work/index.php?course=$course_code&amp;get=$sub->id";
        }
        $filelink = MultimediaHelper::chooseMediaAhrefRaw($url, $url, $sub->file_name, $sub->file_name);
        $data['filelink'] = $filelink;
    }

    $data['preview_rubric'] = $preview_rubric;
    $data['points_to_graded'] = $points_to_graded;
    $data['criteria_list'] = $criteria_list;
    $data['notice'] = $notice;
    $data['grade'] = $grade;
    $data['grade_comments'] = $grade_comments;
    $data['submission_date'] = format_locale_date(strtotime($sub->submission_date));
    $data['submission_id'] = $sub->id;
    $data['submission_type'] = $assignment->submission_type;
    $data['assignment_auto_judge'] = $assignment->auto_judge;

    return $data;
}


/**
 * @brief display assignment submissions results in graph
 * @param $id
 * @return void
 */
function display_assignment_submissions_graph_results($id)
{

    global $course_id, $langBack, $urlAppend, $course_code;

    $action_bar = action_bar(array(
        array(
            'title' => $langBack,
            'icon' => 'fa-reply',
            'url' => "{$urlAppend}modules/work/index.php?course=$course_code&amp;id=$id",
            'level' => 'primary-label'
        )
    ));

    $assign = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                                                        CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
                                                        CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due,
                                                        auto_judge
                                                    FROM assignment
                                                      WHERE course_id = ?d AND id = ?d", $course_id, $id);

    $data = display_assignment_details($assign);

    $result1 = Database::get()->queryArray("SELECT grade FROM assignment_submit WHERE assignment_id = ?d ORDER BY grade ASC", $id);
    $gradeOccurances = array(); // Named array to hold grade occurrences/stats
    $gradesExists = 0;
    $data['json_encoded_chart_data'] = '';
    foreach ($result1 as $row) {
        $theGrade = $row->grade;
        if ($theGrade) {
            $gradesExists = 1;
            if (!isset($gradeOccurances[$theGrade])) {
                $gradeOccurances[$theGrade] = 1;
            } else {
                ++$gradeOccurances[$theGrade];
            }
        }
    }
    // display pie chart with grades results
    if ($gradesExists) {
        // Used to display grades distribution chart
        $graded_submissions_count = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit AS assign
                                                                 WHERE assign.assignment_id = ?d AND
                                                                 assign.grade <> ''", $id)->count;

        if ($assign->grading_scale_id and $assign->grading_type == 1) {
            $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assign->grading_scale_id, $course_id)->scales;
            $scales = unserialize($serialized_scale_data);
            $scale_values = array_value_recursive('scale_item_value', $scales);
        }
        foreach ($gradeOccurances as $gradeValue => $gradeOccurance) {
            $percentage = round((100.0 * $gradeOccurance / $graded_submissions_count),2);
            if ($assign->grading_scale_id and $assign->grading_type == 1) {
                $key = closest($gradeValue, $scale_values, true)['key'];
                $gradeValue = $scales[$key]['scale_item_name'];
            }
            $this_chart_data['grade'][] = "$gradeValue";
            $this_chart_data['percentage'][] = $percentage;
        }
        $data['json_encoded_chart_data'] = json_encode($this_chart_data);
    }

    $data['gradesExists'] = $gradesExists;
    $data['row'] = $assign;
    $data['assignment_id'] = $assign->id;
    $data['action_bar'] = $action_bar;

    view('modules.work.plot_results', $data);
}

/**
 * @brief display form about turnit it
 * @param $id
 * @return string
 */
function show_turnitin_integration($id) {
    global $course_code, $langTurnitinIntegration, $urlAppend;

    $html_content = '';

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
    $lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d", $assignment->lti_template);

    if ($assignment->launchcontainer == LTI_LAUNCHCONTAINER_EMBED) {
        $html_content .= '<div class="col-sm-12 mt-3"><iframe id="contentframe"
            src="' . $urlAppend . "modules/work/post_launch.php?course=" . $course_code . "&amp;id=" . $id . '"
            webkitallowfullscreen=""
            mozallowfullscreen=""
            allowfullscreen=""
            width="100%"
            height="800px"
            style="border: 1px solid #ddd; border-radius: 4px;"></iframe>';
    } else {
        $joinLink = create_join_button_for_assignment($lti, $assignment, $langTurnitinIntegration . ":&nbsp;&nbsp;");
        $html_content .= "<div class='form-wrapper'>" . $joinLink . "</div>";
    }
    return $html_content;
}


// Is this a group assignment?
function is_group_assignment($id) {
    global $course_id;

    $res = Database::get()->querySingle("SELECT group_submissions FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
    if ($res) {
        if ($res->group_submissions == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    } else {
        die("Error: assignment $id doesn't exist");
    }
}

/**
 * @brief send email to user groups
 * @param $gid
 * @param $subject
 * @param $plainBody
 * @param $body
 */
function send_mail_to_group_id($gid, $subject, $plainBody, $body) {

    $res = Database::get()->queryArray("SELECT surname, givenname, email
                                 FROM user, group_members AS members
                                 WHERE members.group_id = ?d
                                 AND user.id = members.user_id", $gid);
    foreach ($res as $info) {
        send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], "$info->givenname $info->surname", $info->email, $subject, $plainBody, $body);
    }
}

/**
 * @brief send mail to users
 * @param $uid
 * @param $subject
 * @param $plainBody
 * @param $body
 */
function send_mail_to_user_id($uid, $subject, $plainBody, $body) {

    $user = Database::get()->querySingle("SELECT surname, givenname, email FROM user WHERE id = ?d", $uid);
    send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'],"$user->givenname $user->surname", $user->email, $subject, $plainBody, $body);
}


/**
 * @brief insert the assignment into the database
 * @return type
 */
function add_assignment() {
    global $workPath, $course_id, $uid, $langTheField, $m, $langTitle,
           $langErrorCreatingDirectory, $langGeneralError, $langPeerReviewPerUserCompulsory,
           $course_code, $langFormErrors, $langNewAssignSuccess, $langIPInvalid,
           $langPeerReviewStartDateCompulsory, $langPeerReviewEndDateCompulsory,
           $langPeerReviewDeadlineCompulsory, $langPeerReviewStartDateError,
           $langPeerReviewStartDateError2;

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->rule('integer', array('group_submissions', 'assign_to_specific'));
    $v->addRule('ipORcidr', 'ipORcidr', $langIPInvalid);
    $v->rule('ipORcidr', array('assignmentIPLock'));
    if (isset($_POST['max_grade'])) {
        $v->rule('required', array('max_grade'));
        $v->rule('numeric', array('max_grade'));
        $v->labels(array('max_grade' => "$langTheField $m[max_grade]"));
    }
    //upoxrewtika pedia sthn epilogh aksiologhsh apo omotimous
    elseif (isset($_POST['reviews_per_user'])){
        $v->rule('required', array('reviews_per_user'));
        $v->rule('numeric', array('reviews_per_user'));
        $v->rule('min', array('reviews_per_user'), 3);
        $v->rule('max', array('reviews_per_user'), 5);
        $v->labels(array('reviews_per_user' => "$langPeerReviewPerUserCompulsory"));

        $v->rule('required', array('WorkStart_review'));
        $v->labels(array('WorkStart_review' => "$langPeerReviewStartDateCompulsory"));

        $v->rule('required', array('WorkEnd_review'));
        $v->labels(array('WorkEnd_review' => "$langPeerReviewEndDateCompulsory"));

        $v->rule('required', array('WorkEnd'));
        $v->labels(array('WorkEnd' => "$langPeerReviewDeadlineCompulsory"));

        if ( isset($_POST['WorkStart_review'] ) < isset($_POST['WorkEnd']) )  {
            /*$v->addRule('error', 'error', $langrevnvalid);
            $v->rule('error', array('WorkStart_review'));*/
            $v->rule('min',array('WorkStart_review'), "$langPeerReviewStartDateError2");
            $v->labels(array('WorkStart_review' => "$langPeerReviewStartDateError"));
        }
    }
    $v->labels(array('title' => "$langTheField $langTitle"));
    if ($v->validate()) {
        $title = $_POST['title'];
        $desc =$_POST['desc'];
        $submission_date = datetimeCreateAndFormat($_POST['WorkStart'] ?? null, (new DateTime('NOW'))->format('Y-m-d H:i:s'));
        $deadline = datetimeCreateAndFormat($_POST['WorkEnd'] ?? null, NULL);
        //aksiologhseis ana xrhsth
        $reviews_per_user = isset($_POST['reviews_per_user']) && !empty($_POST['reviews_per_user']) ? $_POST['reviews_per_user']: NULL;
        //hmeromhnia enarkshs ths aksiologhshs apo omotimous
        $submission_date_review = datetimeCreateAndFormat($_POST['WorkStart_review'] ?? null, NULL);
        //deadline aksiologhshs apo omotimous
        $deadline_review = datetimeCreateAndFormat($_POST['WorkEnd_review'] ?? null, NULL);
        $submission_type = isset($_POST['submission_type']) ? intval($_POST['submission_type']) : 0;
        $late_submission = isset($_POST['late_submission']) ? 1 : 0;
        $group_submissions = $_POST['group_submissions'];
        $notify_submission = isset($_POST['notify_submission']) ? 1 : 0;

        if (isset($_POST['grading_type'])) {
            $grade_type = $_POST['grading_type'];
        } else {
            $grade_type = ASSIGNMENT_STANDARD_GRADE;
        }

        if (isset($_POST['scale'])) {
            $max_grade = max_grade_from_scale($_POST['scale']);
            $grading_scale_id = $_POST['scale'];
        } elseif (isset($_POST['rubric'])) {
            $max_grade = max_grade_from_rubric($_POST['rubric']);
            $grading_scale_id = $_POST['rubric'];
        } elseif (isset($_POST['max_grade'])) {
            $max_grade = $_POST['max_grade'];
            $grading_scale_id = 0;
        } elseif (isset($_POST['reviews_per_user'])) { // peer review
            $max_grade = max_grade_from_rubric($_POST['rubric_review']);
            $grading_scale_id = $_POST['rubric_review'];
        }

        if (!isset($max_grade)) {
            $max_grade = isset($_POST['max_grade'])? $_POST['max_grade']: 0;
        }
        $assign_to_specific = $_POST['assign_to_specific'];
        $assigned_to = filter_input(INPUT_POST, 'ingroup', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
        $auto_judge           = isset($_POST['auto_judge']) ? filter_input(INPUT_POST, 'auto_judge', FILTER_VALIDATE_INT) : 0;
        $auto_judge_scenarios = isset($_POST['auto_judge_scenarios']) ? serialize($_POST['auto_judge_scenarios']) : "";
        $lang                 = isset($_POST['lang']) ? filter_input(INPUT_POST, 'lang') : '';
        $secret = uniqid('');
        $password_lock = $_POST['assignmentPasswordLock'];
        if (isset($_POST['assignmentIPLock'])) {
            $ip_lock = implode(',', $_POST['assignmentIPLock']);
        } else {
            $ip_lock = '';
        }

        if ($assign_to_specific == 1 && empty($assigned_to)) {
            $assign_to_specific = 0;
        }
        $assignment_type = intval($_POST['assignment_type']);

        $lti_template = $_POST['lti_template'] ?? NULL;
        $launchcontainer = $_POST['lti_launchcontainer'] ?? NULL;
        $tii_feedbackreleasedate = datetimeCreateAndFormat($_POST['tii_feedbackreleasedate'] ?? null, NULL);
        $tii_internetcheck = isset($_POST['tii_internetcheck']) ? 1 : 0;
        $tii_institutioncheck = isset($_POST['tii_institutioncheck']) ? 1 : 0;
        $tii_journalcheck = isset($_POST['tii_journalcheck']) ? 1 : 0;
        $tii_s_view_reports = isset($_POST['tii_s_view_reports']) ? 1 : 0;
        $tii_studentpapercheck = isset($_POST['tii_studentpapercheck']) ? 1 : 0;
        $tii_use_biblio_exclusion = isset($_POST['tii_use_biblio_exclusion']) ? 1 : 0;
        $tii_use_quoted_exclusion = isset($_POST['tii_use_quoted_exclusion']) ? 1 : 0;
        $tii_report_gen_speed = 0;
        if (isset($_POST['tii_report_gen_speed']) && intval($_POST['tii_report_gen_speed']) == 1) {
            $tii_report_gen_speed = 1;
        } else if (isset($_POST['tii_report_gen_speed']) && intval($_POST['tii_report_gen_speed']) == 2) {
            $tii_report_gen_speed = 2;
        }
        $tii_submit_papers_to = 1;
        if (isset($_POST['tii_submit_papers_to']) && intval($_POST['tii_submit_papers_to']) == 0) {
            $tii_submit_papers_to = 0;
        } else if (isset($_POST['tii_submit_papers_to']) && intval($_POST['tii_submit_papers_to']) == 2) {
            $tii_submit_papers_to = 2;
        }
        $tii_exclude_type = "none";
        $tii_exclude_value = 0;
        if (isset($_POST['tii_use_small_exclusion'])) {
            $tii_exclude_type = $_POST['tii_exclude_type'];
            $tii_exclude_value = intval($_POST['tii_exclude_value']);
            if ($tii_exclude_type == "percentage" && $tii_exclude_value > 100) {
                $tii_exclude_value = 100;
            }
        }
        $tii_instructorcustomparameters = $_POST['tii_instructorcustomparameters'] ?? NULL;

        $fileCount = isset($_POST['fileCount'])? $_POST['fileCount']: 0;

        if (make_dir("$workPath/$secret") and make_dir("$workPath/admin_files/$secret")) {
            $id = Database::get()->query("INSERT INTO assignment
                    (course_id, title, description, deadline, late_submission,
                    comments, submission_type, submission_date, active, secret_directory,
                    group_submissions, grading_type, max_grade, grading_scale_id,
                    assign_to_specific, auto_judge, auto_judge_scenarios, lang,
                    notification, password_lock, ip_lock, assignment_type, lti_template,
                    launchcontainer, tii_feedbackreleasedate, tii_internetcheck, tii_institutioncheck,
                    tii_journalcheck, tii_report_gen_speed, tii_s_view_reports, tii_studentpapercheck,
                    tii_submit_papers_to, tii_use_biblio_exclusion, tii_use_quoted_exclusion,
                    tii_exclude_type, tii_exclude_value, tii_instructorcustomparameters, reviews_per_assignment,
                    start_date_review, due_date_review, max_submissions)
                VALUES (?d, ?s, ?s, ?t, ?d, ?s, ?d, ?t, 1, ?s, ?d, ?d, ?f, ?d, ?d, ?d, ?s, ?s, ?d, ?s, ?s, ?d, ?d, ?d, ?t,
                ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s, ?d, ?s, ?d, ?t, ?t, ?d)",
                $course_id, $title, $desc, $deadline, $late_submission, '',
                $submission_type, $submission_date, $secret, $group_submissions, $grade_type,
                $max_grade, $grading_scale_id, $assign_to_specific, $auto_judge,
                $auto_judge_scenarios, $lang, $notify_submission, $password_lock,
                $ip_lock, $assignment_type, $lti_template, $launchcontainer, $tii_feedbackreleasedate,
                $tii_internetcheck, $tii_institutioncheck, $tii_journalcheck, $tii_report_gen_speed,
                $tii_s_view_reports, $tii_studentpapercheck, $tii_submit_papers_to, $tii_use_biblio_exclusion,
                $tii_use_quoted_exclusion, $tii_exclude_type, $tii_exclude_value, $tii_instructorcustomparameters, $reviews_per_user,
                $submission_date_review, $deadline_review, $fileCount)->lastInsertID;

            if ($id) {
                // tags
                $moduleTag = new ModuleElement($id);
                if (isset($_POST['tags'])) {
                    $moduleTag->syncTags($_POST['tags']);
                } else {
                    $moduleTag->syncTags(array());
                }

                $secret = work_secret($id);

                $student_name = canonicalize_whitespace(uid_to_name($uid));
                $local_name = !empty($student_name)? $student_name : uid_to_name($uid, 'username');
                $am = Database::get()->querySingle("SELECT am FROM user WHERE id = ?d", $uid)->am;
                if (!empty($am)) {
                    $local_name .= $am;
                }
                $local_name = greek_to_latin($local_name);
                $local_name = replace_dangerous_char($local_name);
                if (!isset($_FILES) || !$_FILES['userfile']['size']) {
                    $_FILES['userfile']['name'] = '';
                    $_FILES['userfile']['tmp_name'] = '';
                } else {
                    validateUploadedFile($_FILES['userfile']['name'], 2);
                    $ext = get_file_extension($_FILES['userfile']['name']);
                    $filename = "$secret/$local_name" . (empty($ext) ? '' : '.' . $ext);
                    if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/admin_files/$filename")) {
                        @chmod("$workPath/admin_files/$filename", 0644);
                        $file_name = $_FILES['userfile']['name'];
                        Database::get()->query("UPDATE assignment SET file_path = ?s, file_name = ?s WHERE id = ?d", $filename, $file_name, $id);
                    }
                }
                if ($assign_to_specific && !empty($assigned_to)) {
                    if (($group_submissions == 1) or ($assign_to_specific == 2)) {
                        $column = 'group_id';
                        $other_column = 'user_id';
                    } else {
                        $column = 'user_id';
                        $other_column = 'group_id';
                    }
                    foreach ($assigned_to as $assignee_id) {
                        Database::get()->query("INSERT INTO assignment_to_specific ({$column}, {$other_column}, assignment_id) VALUES (?d, ?d, ?d)", $assignee_id, 0, $id);
                    }
                }
                Log::record($course_id, MODULE_ID_ASSIGN, LOG_INSERT, array('id' => $id,
                    'title' => $title,
                    'description' => $desc,
                    'deadline' => $deadline,
                    'secret' => $secret,
                    'group' => $group_submissions));
                Session::flash('message',$langNewAssignSuccess);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
            } else {
                @rmdir("$workPath/$secret");
                Session::flash('message',$langGeneralError);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
            }
        } else {
            Session::flash('message',$langErrorCreatingDirectory);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/index.php?course=$course_code&add=1");
    }
}


/**
 * @brief edit assignment
 * @param type $id
 * @return type
 */
function edit_assignment($id) {
    global $langEditSuccess, $m, $langTheField, $course_code,
           $course_id, $uid, $workPath, $langFormErrors, $langTitle,
           $langIPInvalid, $langPeerReviewPerUserCompulsory,
           $langPeerReviewStartDateCompulsory, $langPeerReviewEndDateCompulsory,
           $langPeerReviewDeadlineCompulsory, $langPeerReviewStartDateError2,
           $langPeerReviewStartDateError;

    $row = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->rule('integer', array('group_submissions', 'assign_to_specific'));
    $v->addRule('ipORcidr', 'ipORcidr', $langIPInvalid);
    $v->rule('ipORcidr', array('assignmentIPLock'));

    if (isset($_POST['max_grade'])) {
        $v->rule('required', array('max_grade'));
        $v->rule('numeric', array('max_grade'));
        $v->labels(array('max_grade' => "$langTheField $m[max_grade]"));
    }
    //upoxrewtika pedia sthn epilogh aksiologhsh apo omotimous
    if ($row->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE and isset($_POST['reviews_per_user']) and !empty($_POST['reviews_per_user'])) {
        $v->rule('required', array('reviews_per_user'));
        $v->rule('numeric', array('reviews_per_user'));
        $v->rule('min', array('reviews_per_user'), 3);
        $v->rule('max', array('reviews_per_user'), 5);
        $v->labels(array('reviews_per_user' => "$langPeerReviewPerUserCompulsory"));

        $v->rule('required', array('WorkStart_review'));
        $v->labels(array('WorkStart_review' => "$langPeerReviewStartDateCompulsory"));
        $v->rule('required', array('WorkEnd_review'));
        $v->labels(array('WorkEnd_review' => "$langPeerReviewEndDateCompulsory"));

        $v->rule('required', array('WorkEnd'));
        $v->labels(array('WorkEnd' => "$langPeerReviewDeadlineCompulsory"));

        if ($_POST['WorkStart_review'] < $_POST['WorkEnd']) {
            $v->rule('min',array('WorkStart_review'), "$langPeerReviewStartDateError2");
            $v->labels(array('WorkStart_review' => "$langPeerReviewStartDateError"));
        }
    }

    $v->labels(array('title' => "$langTheField $langTitle"));
    if ($v->validate()) {
        $title = $_POST['title'];
        $desc = purify($_POST['desc']);
        $reviews_per_user = null;
        if (isset($_POST['reviews_per_user'])) {
            $reviews_per_user = $_POST['reviews_per_user'];
        }
        $submission_type = isset($_POST['submission_type']) ? intval($_POST['submission_type']) : 0;
        $submission_date = datetimeCreateAndFormat($_POST['WorkStart'] ?? null, (new DateTime('NOW'))->format('Y-m-d H:i:s'));
        $deadline = datetimeCreateAndFormat($_POST['WorkEnd'] ?? null, NULL);
        //hmeromhnia enarkshs ths aksiologhshs apo omotimous
        $submission_date_review = datetimeCreateAndFormat($_POST['WorkStart_review'] ?? null, NULL);
        //deadline aksiologhshs apo omotimous
        $deadline_review = datetimeCreateAndFormat($_POST['WorkEnd_review'] ?? null, NULL);
        $late_submission = isset($_POST['late_submission']) ? 1 : 0;
        $group_submissions = $_POST['group_submissions'];
        $grade_type = $_POST['grading_type'];

        if (isset($_POST['rubric_review']) && isset($_POST['reviews_per_user']) && ($grade_type == ASSIGNMENT_PEER_REVIEW_GRADE)) {
            $max_grade = max_grade_from_rubric($_POST['rubric_review']);
            $grading_scale_id = $_POST['rubric_review'];
        } elseif (isset($_POST['scale']) && ($grade_type == ASSIGNMENT_SCALING_GRADE)) {
            $max_grade = max_grade_from_scale($_POST['scale']);
            $grading_scale_id = $_POST['scale'];
        } elseif (isset($_POST['rubric']) && ($grade_type == ASSIGNMENT_RUBRIC_GRADE)) {
            $max_grade = max_grade_from_rubric($_POST['rubric']);
            $grading_scale_id = $_POST['rubric'];
        } elseif (isset($_POST['max_grade']) && ($grade_type == ASSIGNMENT_STANDARD_GRADE)) {
            $max_grade = $_POST['max_grade'];
            $grading_scale_id = 0;
        }

        $assign_to_specific = filter_input(INPUT_POST, 'assign_to_specific', FILTER_VALIDATE_INT);
        $assigned_to = filter_input(INPUT_POST, 'ingroup', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
        $auto_judge           = isset($_POST['auto_judge']) ? filter_input(INPUT_POST, 'auto_judge', FILTER_VALIDATE_INT) : 0;
        $auto_judge_scenarios = isset($_POST['auto_judge_scenarios']) ? serialize($_POST['auto_judge_scenarios']) : "";
        $lang                 = isset($_POST['lang']) ? filter_input(INPUT_POST, 'lang') : '';

        $fileCount = $_POST['fileCount'] ?? 0;

        if ($assign_to_specific == 1 && empty($assigned_to)) {
            $assign_to_specific = 0;
        }

        if (!isset($_POST['comments'])) {
            $comments = '';
        } else {
            $comments = purify($_POST['comments']);
        }

        if (!isset($_FILES) || !$_FILES['userfile']['size']) {
            $_FILES['userfile']['name'] = '';
            $_FILES['userfile']['tmp_name'] = '';
            $filename = $row->file_path;
            $file_name = $row->file_name;
        } else {
            validateUploadedFile($_FILES['userfile']['name'], 2);
            $student_name = trim(uid_to_name($uid));
            $local_name = !empty($student_name)? $student_name : uid_to_name($uid, 'username');
            $am = Database::get()->querySingle("SELECT am FROM user WHERE id = ?d", $uid)->am;
            if (!empty($am)) {
                $local_name .= $am;
            }
            $local_name = greek_to_latin($local_name);
            $local_name = replace_dangerous_char($local_name);
            $secret = $row->secret_directory;
            $ext = get_file_extension($_FILES['userfile']['name']);
            $filename = "$secret/$local_name" . (empty($ext) ? '' : '.' . $ext);
            make_dir("$workPath/admin_files/$secret");
            if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/admin_files/$filename")) {
                @chmod("$workPath/admin_files/$filename", 0644);
                $file_name = $_FILES['userfile']['name'];
            }
        }
        $notify_submission = isset($_POST['notify_submission']) ? 1 : 0;
        $assignment_type = intval($_POST['assignment_type']);
        $lti_template = isset($_POST['lti_template']) ? $_POST['lti_template'] : NULL;
        $launchcontainer = isset($_POST['lti_launchcontainer']) ? $_POST['lti_launchcontainer'] : NULL;
        $tii_feedbackreleasedate = datetimeCreateAndFormat($_POST['tii_feedbackreleasedate'] ?? null, NULL);
        $tii_internetcheck = isset($_POST['tii_internetcheck']) ? 1 : 0;
        $tii_institutioncheck = isset($_POST['tii_institutioncheck']) ? 1 : 0;
        $tii_journalcheck = isset($_POST['tii_journalcheck']) ? 1 : 0;
        $tii_s_view_reports = isset($_POST['tii_s_view_reports']) ? 1 : 0;
        $tii_studentpapercheck = isset($_POST['tii_studentpapercheck']) ? 1 : 0;
        $tii_use_biblio_exclusion = isset($_POST['tii_use_biblio_exclusion']) ? 1 : 0;
        $tii_use_quoted_exclusion = isset($_POST['tii_use_quoted_exclusion']) ? 1 : 0;
        $tii_report_gen_speed = 0;
        if (isset($_POST['tii_report_gen_speed']) && intval($_POST['tii_report_gen_speed']) == 1) {
            $tii_report_gen_speed = 1;
        } else if (isset($_POST['tii_report_gen_speed']) && intval($_POST['tii_report_gen_speed']) == 2) {
            $tii_report_gen_speed = 2;
        }
        $tii_submit_papers_to = 1;
        if (isset($_POST['tii_submit_papers_to']) && intval($_POST['tii_submit_papers_to']) == 0) {
            $tii_submit_papers_to = 0;
        } else if (isset($_POST['tii_submit_papers_to']) && intval($_POST['tii_submit_papers_to']) == 2) {
            $tii_submit_papers_to = 2;
        }
        $tii_exclude_type = "none";
        $tii_exclude_value = 0;
        if (isset($_POST['tii_use_small_exclusion'])) {
            $tii_exclude_type = $_POST['tii_exclude_type'];
            $tii_exclude_value = intval($_POST['tii_exclude_value']);
            if ($tii_exclude_type == "percentage" && $tii_exclude_value > 100) {
                $tii_exclude_value = 100;
            }
        }
        $tii_instructorcustomparameters = $_POST['tii_instructorcustomparameters'] ?? NULL;

        Database::get()->query("UPDATE assignment SET title = ?s, description = ?s,
                group_submissions = ?d, comments = ?s, submission_type = ?d,
                deadline = ?t, late_submission = ?d, submission_date = ?t, grading_type = ?d, max_grade = ?f,
                grading_scale_id = ?d, assign_to_specific = ?d, file_path = ?s, file_name = ?s,
                auto_judge = ?d, auto_judge_scenarios = ?s, lang = ?s, notification = ?d,
                password_lock = ?s, ip_lock = ?s, assignment_type = ?d, lti_template = ?d, launchcontainer = ?d,
                tii_feedbackreleasedate = ?t, tii_internetcheck = ?d, tii_institutioncheck = ?d,
                tii_journalcheck = ?d, tii_report_gen_speed = ?d, tii_s_view_reports = ?d, tii_studentpapercheck = ?d,
                tii_submit_papers_to = ?d, tii_use_biblio_exclusion = ?d, tii_use_quoted_exclusion = ?d,
                tii_exclude_type = ?s, tii_exclude_value = ?d, tii_instructorcustomparameters = ?s, reviews_per_assignment = ?d,
                start_date_review = ?t, due_date_review = ?t,
                max_submissions = ?d
            WHERE course_id = ?d AND id = ?d",
            $title, $desc, $group_submissions, $comments, $submission_type,
            $deadline, $late_submission, $submission_date, $grade_type, $max_grade,
            $grading_scale_id, $assign_to_specific, $filename, $file_name,
            $auto_judge, $auto_judge_scenarios, $lang, $notify_submission,
            $_POST['assignmentPasswordLock'],
            isset($_POST['assignmentIPLock'])? implode(',', $_POST['assignmentIPLock']): '',
            $assignment_type, $lti_template, $launchcontainer, $tii_feedbackreleasedate,
            $tii_internetcheck, $tii_institutioncheck, $tii_journalcheck, $tii_report_gen_speed,
            $tii_s_view_reports, $tii_studentpapercheck, $tii_submit_papers_to, $tii_use_biblio_exclusion,
            $tii_use_quoted_exclusion, $tii_exclude_type, $tii_exclude_value, $tii_instructorcustomparameters, $reviews_per_user,
            $submission_date_review, $deadline_review, $fileCount, $course_id, $id);

        // purge old entries (if any)
        Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);
        // tags
        $moduleTag = new ModuleElement($id);
        if (isset($_POST['tags'])) {
            $moduleTag->syncTags($_POST['tags']);
        } else {
            $moduleTag->syncTags(array());
        }
        if ($assign_to_specific && !empty($assigned_to)) {
            if (($group_submissions == 1) or ($assign_to_specific == 2)) {
                $column = 'group_id';
                $other_column = 'user_id';
            } else {
                $column = 'user_id';
                $other_column = 'group_id';
            }
            foreach ($assigned_to as $assignee_id) {
                Database::get()->query("INSERT INTO assignment_to_specific ({$column}, {$other_column}, assignment_id) VALUES (?d, ?d, ?d)", $assignee_id, 0, $id);
            }
        }
        Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY,
            array('id' => $id,
                'title' => $title,
                'description' => $desc,
                'deadline' => $deadline,
                'group' => $group_submissions));

        Session::flash('message',$langEditSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id&choice=edit");
    }
}


/**
 * @brief submit assignment
 * @param type $id
 * @param type $on_behalf_of
 */
function submit_work($id, $on_behalf_of = null) {

    global $course_id, $uid, $unit, $langOnBehalfOfGroupComment,
           $works_url, $langOnBehalfOfUserComment, $workPath,
           $langUploadSuccess, $langUploadError, $course_code,
           $langAutoJudgeInvalidFileType, $langExerciseNotPermit, $langNoFileUploaded,
           $langAutoJudgeScenariosPassed, $autojudge, $langEmptyFaculte;

    $row = Database::get()->querySingle("SELECT id, title, group_submissions, submission_type, submission_date,
                            deadline, late_submission, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                            auto_judge, auto_judge_scenarios, lang, max_grade, notification, max_submissions
                            FROM assignment
                            WHERE course_id = ?d AND id = ?d",
        $course_id, $id);

    $notification = $row->notification;
    $auto_judge = $row->auto_judge;
    $auto_judge_scenarios = $auto_judge ? unserialize($row->auto_judge_scenarios) : null;
    $lang = $row->lang;
    $max_grade = $row->max_grade;

    if ($autojudge->isEnabled() && $auto_judge) {
        $langExt = $autojudge->getSupportedLanguages();
    }

    $nav[] = $works_url;
    $nav[] = array('url' => "$_SERVER[SCRIPT_NAME]?id=$id", 'name' => q($row->title));

    $submit_ok = FALSE; // Default do not allow submission
    if (isset($uid) && $uid) { // check if logged-in
        if ($GLOBALS['status'] == USER_GUEST) { // user is guest
            $submit_ok = FALSE;
        } else { // user NOT guest
            if (isset($_SESSION['courses'][$_SESSION['dbname']])) { // user is registered to this lesson
                $WorkStart = new DateTime($row->submission_date);
                $current_date = new DateTime('NOW');
                $interval = $WorkStart->diff($current_date);
                if ($WorkStart > $current_date) {
                    $submit_ok = FALSE; // before assignment
                } else if (($row->time < 0 && intval($row->deadline) && !$row->late_submission) and !$on_behalf_of) {
                    $submit_ok = FALSE; // after assignment deadline
                } else {
                    $submit_ok = TRUE; // before deadline
                }
            } else {
                //user NOT registered to this lesson
                $submit_ok = FALSE;
            }
        }
    } //checks for submission validity end here

    if ($submit_ok) {
        $success_msgs = array();
        //Preparing variables
        $user_id = isset($on_behalf_of) ? $on_behalf_of : $uid;
        if ($row->group_submissions) {
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : -1;
            $gids = user_group_info($on_behalf_of ? null : $user_id, $course_id);
        } else {
            $group_id = 0;
        }
        // If submission type is Online Text
        if ($row->submission_type == 1) {
            $filename = '';
            $file_name = '';
            $files_to_keep = [];
            if (isset($_POST['submission_text']) and !empty($_POST['submission_text'])) {
                $submission_text = purify($_POST['submission_text']);
                $success_msgs[] = $langUploadSuccess;
            } else {
                Session::flash('message',$langEmptyFaculte);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
            }
        } else { // If submission type is one or multiple files
            if ($row->group_submissions) {
                $local_name = isset($gids[$group_id]) ? greek_to_latin($gids[$group_id]) : '';
            } else {
                $student_name = trim(uid_to_name($user_id));
                $local_name = !empty($student_name)? $student_name : uid_to_name($user_id, 'username');
                $am = uid_to_am($user_id);
                if (!empty($am)) {
                    $local_name .= ' ' . $am;
                }
                $local_name = greek_to_latin($local_name);
            }
            $local_name .= ' (' . uid_to_name($user_id, 'username') . ')';
            $local_name = replace_dangerous_char($local_name);
            $local_name = work_secret($row->id) . '/' . $local_name;

            $files_to_keep = [];
            $file_name = $filename = $submission_text = '';
            $no_files = isset($on_behalf_of) && !isset($_FILES);
            $has_uploaded_files = isset($_FILES['userfile']) && !empty($_FILES['userfile']['name'][0]);

            if (!$no_files && $has_uploaded_files) {

                $files_list = [];

                if (isset($_FILES['userfile']) && is_array($_FILES['userfile']['name'])) {
                    foreach ($_FILES['userfile']['name'] as $i => $name) {
                        // Αν δεν υπάρχει αρχείο σε αυτή τη θέση, το προσπερνάμε
                        if ($_FILES['userfile']['error'][$i] == UPLOAD_ERR_NO_FILE) continue;

                        $files_list[] = [
                            'name' => $_FILES['userfile']['name'][$i],
                            'tmp_name' => $_FILES['userfile']['tmp_name'][$i],
                            'error' => $_FILES['userfile']['error'][$i]
                        ];
                    }
                }

                $maxFiles = $row->max_submissions;
                $totalFiles = count($files_list);
                $fileInfo = [];

                foreach ($files_list as $file) {
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        Session::flash('message', $langUploadError . ' (Code: ' . $file['error'] . ')');
                        Session::flash('alert-class', 'alert-danger');
                        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                        exit();
                    }
                }

                if ($totalFiles > $maxFiles) {
                    Session::flash('message',$GLOBALS['langWorkFilesCountExceeded']);
                    Session::flash('alert-class', 'alert-danger');
                    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                    exit();
                }

                if ($totalFiles == 1) {
                    $format = '';
                } else {
                    $destDir = $workPath . '/' . $local_name;
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $format = '/%0' . strlen($totalFiles) . 'd';
                }

                $j = 1;
                $files_to_keep = [];

                $all_files_moved = true;

                foreach ($files_list as $file) {
                    $file_name = $file['name'];

                    validateUploadedFile($file_name, 2);

                    $ext = get_file_extension($file_name);
                    $filename = $local_name . sprintf($format, $j) . (empty($ext) ? '' : '.' . $ext);
                    $destination = $workPath . '/' . $filename;

                    $moved = move_uploaded_file($file['tmp_name'], $destination);

                    if (!$moved) {
                        $all_files_moved = false;
                        Session::flash('message', "Error moving file: " . $file_name);
                        Session::flash('alert-class', 'alert-danger');
                        break; // Σταματάμε το loop
                    }

                    $fileInfo[] = [$filename, $file_name];
                    $files_to_keep[] = $filename;
                    $j++;
                }

                if (!empty($fileInfo)) {
                    list($filename, $file_name) = $fileInfo[0];
                }

                if (!$all_files_moved) {
                    // Το μήνυμα έχει μπει στο session flash μέσα στο loop
                    Session::flash('message', $langUploadError);
                    Session::flash('alert-class', 'alert-danger');
                    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                    exit();
                }
            }
            $success_msgs[] = $langUploadSuccess;
        }

        $submit_ip = Log::get_client_ip();

        $grade_comments = $grade_ip = '';
        $grade = null;
        if (isset($on_behalf_of)) {
            if ($row->group_submissions) {
                $stud_comments = sprintf($langOnBehalfOfGroupComment, uid_to_name($uid), $gids[$group_id]);
            } else {
                $stud_comments = sprintf($langOnBehalfOfUserComment, uid_to_name($uid), uid_to_name($user_id));
            }
            $grade_comments = $_POST['stud_comments'];
            $grade_valid = filter_input(INPUT_POST, 'grade', FILTER_VALIDATE_FLOAT);
            (isset($_POST['grade']) && $grade_valid!== false) ? $grade = $grade_valid : $grade = NULL;
            $grade_ip = $submit_ip;
        } else {
            if ($row->group_submissions) {
                if (array_key_exists($group_id, $gids)) {
                    $del_submission_msg = delete_submissions_by_uid(-1, $group_id, $row->id, $files_to_keep);
                    if (!empty($del_submission_msg)) {
                        $success_msgs[] = $del_submission_msg;
                    }
                }
            } else {
                $del_submission_msg = delete_submissions_by_uid($user_id, -1, $row->id, $files_to_keep);
                if (!empty($del_submission_msg)) {
                    $success_msgs[] = $del_submission_msg;
                }
            }
            $stud_comments = $_POST['stud_comments'];
        }
        if (isset($_POST['grade_rubric'])){
            $grade_rubric = serialize($_POST['grade_rubric']);
        } else {
            $grade_rubric = '';
        }

        if (!$row->group_submissions || array_key_exists($group_id, $gids)) {
            $data = array(
                $user_id,
                $row->id,
                $submit_ip,
                $filename,
                $file_name,
                $submission_text,
                $stud_comments,
                $grade,
                $grade_rubric,
                $grade_comments,
                $grade_ip,
                $group_id
            );
            $sid = Database::get()->query("INSERT INTO assignment_submit
                                    (uid, assignment_id, submission_date, submission_ip, file_path,
                                     file_name, submission_text, comments, grade, grade_rubric, grade_comments, grade_submission_ip,
                                     grade_submission_date, group_id)
                                     VALUES (?d, ?d, ". DBHelper::timeAfter() . ", ?s, ?s, ?s, ?s, ?s, ?f, ?s, ?s, ?s, " . DBHelper::timeAfter() . ", ?d)", $data)->lastInsertID;

            // for multifile submissions, add more records for files 2-n
            if ($row->submission_type == 2 && $totalFiles > 1) {
                array_shift($fileInfo); // first file has been inserted, so discard it
                foreach ($fileInfo as $file) {
                    $data = [$user_id, $row->id, $submit_ip, $file[0], $file[1], '', '', 0, '', '', '', $group_id];
                    Database::get()->query("INSERT INTO assignment_submit
                        (uid, assignment_id, submission_date, submission_ip, file_path,
                         file_name, submission_text, comments, grade, grade_rubric, grade_comments, grade_submission_ip,
                         grade_submission_date, group_id)
                         VALUES (?d, ?d, ". DBHelper::timeAfter() . ", ?s, ?s, ?s, ?s, ?s, ?f, ?s, ?s, ?s, " .
                        DBHelper::timeAfter() . ", ?d)", $data)->lastInsertID;
                }
            }

            triggerGame($course_id, $user_id, $row->id);
            triggerAssignmentSubmit($course_id, $user_id, $row->id);
            triggerAssignmentAnalytics($course_id, $user_id, $row->id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
            triggerAssignmentAnalytics($course_id, $user_id, $row->id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
            Log::record($course_id, MODULE_ID_ASSIGN, LOG_INSERT, array('id' => $sid,
                'title' => $row->title,
                'assignment_id' => $row->id,
                'filepath' => $filename,
                'filename' => $file_name,
                'comments' => $stud_comments,
                'group_id' => $group_id));

            // notify course admin (if requested)
            if ($notification) {
                notify_for_assignment_submission($row->title);
            }

            if ($row->group_submissions) {
                $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE id = ?d", $sid)->group_id;
                $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                foreach ($user_ids as $user_id) {
                    update_attendance_book($user_id->user_id, $row->id, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                    update_gradebook_book($user_id->user_id, $row->id, $grade/$row->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                }
            } else {
                $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                // update attendance book as well
                update_attendance_book($quserid, $row->id, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                //update gradebook if needed
                $book_grade = is_null($grade)? null: $grade / $row->max_grade;
                update_gradebook_book($quserid, $id, $book_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
            }
            if ($on_behalf_of and isset($_POST['send_email'])) {
                $email_grade = $_POST['grade'];
                $email_comments = $_POST['stud_comments'];
                grade_email_notify($row->id, $sid, $email_grade, $email_comments);
            }
        }

        // Send file to AutoJudge service
        if($autojudge->isEnabled()) {
            if ($auto_judge && $ext === $langExt[$lang]) {
                $content = file_get_contents("$workPath/$filename");
                // Run each scenario and count how many passed
                $auto_judge_scenarios_output = array(
                    array(
                        'student_output'=> '',
                        'passed'=> 0,
                    )
                );

                $passed = 0;
                $i = 0;
                $partial = 0;
                $errorsComment = '';
                $weight_sum = 0;
                foreach($auto_judge_scenarios as $curScenario) {
                    $input = new AutoJudgeConnectorInput();
                    $input->input = $curScenario['input'];
                    $input->code = $content;
                    $input->lang = $lang;
                    $result = $autojudge->compile($input);
                    // Check if we have compilation errors.
                    if ($result->compileStatus !== $result::COMPILE_STATUS_OK) {
                        // Write down the error message.
                        $num = $i+1;
                        $errorsComment = $result->compileStatus." ".$result->output."<br />";
                        $auto_judge_scenarios_output[$i]['passed'] = 0;
                    } else {
                        // Get all needed values to run the assertion.
                        $auto_judge_scenarios_output[$i]['student_output'] = $result->output;
                        $scenarioOutputExpectation = trim($curScenario['output']);
                        $scenarionAssertion        = $curScenario['assertion'];
                        // Do it now.
                        $assertionResult = doScenarioAssertion(
                            $scenarionAssertion,
                            $auto_judge_scenarios_output[$i]['student_output'],
                            $scenarioOutputExpectation
                        );
                        // Check if assertion passed.
                        if ($assertionResult) {
                            $passed++;
                            $auto_judge_scenarios_output[$i]['passed'] = 1;
                            $partial += $curScenario['weight'];
                        } else {
                            $num = $i+1;
                            $auto_judge_scenarios_output[$i]['passed'] = 0;
                        }
                    }

                    $weight_sum += $curScenario['weight'];
                    $i++;
                }

                // 3 decimal digits precision
                $grade = round($partial / $weight_sum * $max_grade, 3);
                // allow an error of 0.001
                if($max_grade - $grade <= 0.001)
                    $grade = $max_grade;
                // Add the output as a comment
                $comment = $langAutoJudgeScenariosPassed.': '.$passed.'/'.count($auto_judge_scenarios);
                rtrim($errorsComment, '<br />');
                if ($errorsComment !== '') {
                    $comment .= '<br /><br />'.$errorsComment;
                }
                submit_grade_comments([
                    'assignment' => $id,
                    'submission' => $sid,
                    'grade' => $grade,
                    'comments' => $comment,
                    'send_email' => false,
                    'auto_judge_scenarios_output' => $auto_judge_scenarios_output,
                    'preventUiAlterations' => true,
                ]);

            } else if ($auto_judge && $ext !== $langExt[$lang]) {
                if($lang == null) { die('Auto Judge is enabled but no language is selected'); }
                if($langExt[$lang] == null) { die('An unsupported language was selected. Perhaps platform-wide auto judge settings have been changed?'); }
                submit_grade_comments([
                    'assignment' => $id,
                    'submission' => $sid,
                    'grade' => 0,
                    'comments' => sprintf($langAutoJudgeInvalidFileType, $langExt[$lang], $ext),
                    'send_email' => false,
                    'auto_judge_scenarios_output' => null,
                    'preventUiAlterations' => true,
                ]);
            }
        }
        // End Auto-judge
        Session::flash('message', $success_msgs);
        Session::flash('alert-class', 'alert-success');
        if (isset($unit)) {
            redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit");
        } else {
            redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
        }

    } else { // not submit_ok
        Session::flash('message',$langExerciseNotPermit);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/work/index.php?course=$course_code");
    }
}


/**
 * @brief delete assignment
 * @param type $id
 */
function delete_assignment($id) {

    global $workPath, $course_code, $webDir, $course_id;

    $secret = work_secret($id);
    $row = Database::get()->querySingle("SELECT title, assign_to_specific FROM assignment WHERE course_id = ?d
                                        AND id = ?d", $course_id, $id);
    if ($row != null) {
        $uids = Database::get()->queryArray("SELECT uid FROM assignment_submit WHERE assignment_id = ?d", $id);
        foreach ($uids as $user_id) {
            triggerGame($course_id, $user_id->uid, $id);
            triggerAssignmentSubmit($course_id, $user_id->uid, $id);
            triggerAssignmentAnalytics($course_id, $user_id->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
            triggerAssignmentAnalytics($course_id, $user_id->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
        }
        if (Database::get()->query("DELETE FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id)->affectedRows > 0){
            Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $id);
            Database::get()->query("DELETE FROM assignment_grading_review WHERE assignment_id = ?d", $id);

            if ($row->assign_to_specific) {
                Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);
            }

            $admin_files_directory = $webDir . "/courses/" . $course_code . "/work/admin_files/" . $secret;
            removeDir($admin_files_directory);

            move_dir("$workPath/$secret", "$webDir/courses/garbage/{$course_code}_work_{$id}_$secret");

            Log::record($course_id, MODULE_ID_ASSIGN, LOG_DELETE, array('id' => $id,
                'title' => $row->title));
            return true;
        }
        return false;
    }
    return false;
}
/**
 * @brief delete assignment's submissions
 * @param type $id
 */
function purge_assignment_subs($id) {

    global $workPath, $webDir, $course_code, $course_id;

    $secret = work_secret($id);
    $row = Database::get()->querySingle("SELECT title, assign_to_specific FROM assignment WHERE course_id = ?d
                                    AND id = ?d", $course_id, $id);
    $uids = Database::get()->queryArray("SELECT uid FROM assignment_submit WHERE assignment_id = ?d", $id);

    foreach ($uids as $user_id) {
        triggerGame($course_id, $user_id->uid, $id);
        triggerAssignmentSubmit($course_id, $user_id->uid, $id);
        triggerAssignmentAnalytics($course_id, $user_id->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
        triggerAssignmentAnalytics($course_id, $user_id->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
    }
    if (Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $id)->affectedRows > 0) {
        if ($row->assign_to_specific) {
            Database::get()->query("DELETE FROM assignment_to_specific WHERE assignment_id = ?d", $id);
        }
        move_dir("$workPath/$secret", "$webDir/courses/garbage/{$course_code}_work_{$id}_$secret");
        return true;
    }
    return false;
}

/**
 * @brief delete user assignment
 * @param type $id
 */
function delete_user_assignment($id) {
    global $course_code, $webDir, $course_id;

    $return = true;
    $info = Database::get()->querySingle('SELECT uid, group_id, assignment_id
        FROM assignment_submit WHERE id = ?d', $id);
    if (is_null($info->group_id)) {
        $records = Database::get()->queryArray('SELECT id, file_path FROM assignment_submit
            WHERE assignment_id = ?d AND uid = ?d AND group_id IS NULL',
            $info->assignment_id, $info->uid);
    } else {
        $records = Database::get()->queryArray('SELECT id, file_path FROM assignment_submit
            WHERE assignment_id = ?d AND uid = ?d AND group_id = ?d',
            $info->assignment_id, $info->uid, $info->group_id);
    }
    foreach ($records as $record) {
        if (Database::get()->query("DELETE FROM assignment_submit WHERE id = ?d", $record->id)->affectedRows > 0) {
            if ($record->file_path) {
                $file = $webDir . "/courses/" . $course_code . "/work/" . $record->file_path;
                if (!my_delete($file)) {
                    $return = false;
                }
            }
        }
    }
    if ($return) {
        if (count($records) > 1) {
            $userdir = preg_replace('|/[^/]+$|', '', $file);
            rmdir($userdir);
        }
        triggerGame($course_id, $info->uid, $id);
        triggerAssignmentSubmit($course_id, $info->uid, $id);
        triggerAssignmentAnalytics($course_id, $info->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
        triggerAssignmentAnalytics($course_id, $info->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
    }
    return $return;
}

/**
 * @brief Delete submissions to assignment $id if submitted by user $uid or group $gid
 * Doesn't delete files if they are one of the $files_to_keep
 * @param $uid
 * @param $gid
 * @param $id
 * @param $files_to_keep
 * @return string
 */
function delete_submissions_by_uid($uid, $gid, $id, $files_to_keep = []) {
    global $workPath, $langDeletedWorkByGroup, $langDeletedWorkByUser;

    $return = '';
    $res = Database::get()->queryArray("SELECT id, file_path, file_name, uid, group_id
        FROM assignment_submit
        WHERE assignment_id = ?d AND (uid = ?d OR group_id = ?d)", $id, $uid, $gid);
    foreach ($res as $row) {
        if (!in_array($row->file_path, $files_to_keep)) {
            @unlink("$workPath/$row->file_path");
            // try to remove parent dir if this was part of a multifile submission
            $parts = explode('/', $row->file_path);
            if (count($parts) == 3) {
                $parent = "$workPath/$parts[0]/$parts[1]";
                if (is_dir($parent)) {
                    @rmdir($parent);
                }
            }
        }
        $ass_cid = Database::get()->querySingle("SELECT course_id FROM assignment WHERE id = ?d", $id)->course_id;
        Database::get()->query("DELETE FROM assignment_submit WHERE id = ?d", $row->id);
        triggerGame($ass_cid, $row->uid, $id);
        triggerAssignmentAnalytics($ass_cid, $row->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
        triggerAssignmentAnalytics($ass_cid, $row->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
        if ($GLOBALS['uid'] == $row->uid) {
            $return .= $langDeletedWorkByUser;
        } else {
            $return .= $langDeletedWorkByGroup;
        }
        $return .= ' "<i>' . q($row->file_name) . '</i>". ';
    }
    return $return;
}

/**
 * @param $id
 * @brief function delete gia pinaka assignment_grade_review, to delete ginetai apo ton kathhghth
 * @return string
 */
function delete_submissions($id) {

    global $langPeerReviewAssignmentInfo1, $langPeerReviewAssignmentInfo2;

    $res = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE assignment_id = ?d ", $id);
    if ($res) {
        $msg = $langPeerReviewAssignmentInfo1;
    } else {
        $msg = $langPeerReviewAssignmentInfo2;
    }
    foreach ($res as $row) {
        Database::get()->query("DELETE FROM assignment_grading_review WHERE assignment_id = ?d ", $id);
    }
    return $msg;
}

/**
 * @brief delete teacher assignment file
 * @param type $id
 */
function delete_teacher_assignment_file($id) {
    global $course_code, $webDir;

    $filename = Database::get()->querySingle("SELECT file_path FROM assignment WHERE id = ?d", $id);
    $file = $webDir . "/courses/" . $course_code . "/work/admin_files/" . $filename->file_path;
    if (Database::get()->query("UPDATE assignment SET file_path='', file_name='' WHERE id = ?d", $id)->affectedRows > 0) {
        if (my_delete($file)) {
            return true;
        }
        return false;
    }
}

/**
 * @brief Find submissions by a user (or the user's groups)
 * @param $is_group_assignment
 * @param $uid
 * @param $id
 * @param $gids
 * @return array|DBResult|null
 */

function find_submissions($is_group_assignment, $uid, $id, $gids) {

    if ($is_group_assignment AND count($gids)) {
        $groups_sql = join(', ', array_keys($gids));
        $res = Database::get()->queryArray("SELECT id, uid, group_id, submission_date,
                file_path, file_name, comments, grade, grade_comments, grade_submission_date
            FROM assignment_submit
            WHERE assignment_id = ?d AND
                  group_id IN ($groups_sql)", $id);
        if (!$res) {
            return [];
        } else {
            return array_filter($res, function ($item) {
                static $seen = [];

                $return = !isset($seen[$item->group_id]);
                $seen[$item->group_id] = true;
                return $return;
            });
        }

    } else {
        $res = Database::get()->querySingle("SELECT id, grade
            FROM assignment_submit
            WHERE assignment_id = ?d AND uid = ?d
            ORDER BY id LIMIT 1", $id, $uid);
        if (!$res) {
            return [];
        } else {
            return [$res];
        }
    }
}


/**
* @brief Return a list of groups with no submissions for assignment $id
*/
function groups_with_no_submissions($id) {
    global $course_id;

    $q = Database::get()->queryArray('SELECT group_id FROM assignment_submit WHERE assignment_id = ?d', $id);
    $groups = user_group_info(null, $course_id, $id);
    if (count($q)>0) {
        foreach ($q as $row) {
            unset($groups[$row->group_id]);
        }
    }
    return $groups;
}

/**
 * @brief get user assignment's file submissions
 * @param $assignment
 * @param $result
 * @param $row
 * @return string
 */
function get_user_file_submissions($assignment, $result, $row): string
{
    if ($assignment->submission_type == 2) {
        // Get all files by the same user and group
        $allFiles = array_filter($result, function ($item) use ($row) {
            return $item->uid == $row->uid && $item->group_id == $row->group_id;
        });
    } else {
        $allFiles = [$row];
    }
    $filelink = implode('<div class="mb-2"></div>', array_map(function ($item) {
        global $urlAppend, $course_code;
        $url = "{$urlAppend}modules/work/index.php?course=$course_code&amp;get=$item->id";
        $namelen = mb_strlen($item->file_name);
        if ($namelen > 30) {
            $extlen = mb_strlen(get_file_extension($item->file_name));
            $basename = mb_substr($item->file_name, 0, $namelen - $extlen - 3);
            $ext = mb_substr($item->file_name, $namelen - $extlen - 3);
            $filename = ellipsize($basename, 27, '...' . $ext);
        } else {
            $filename = $item->file_name;
        }
        return MultimediaHelper::chooseMediaAhrefRaw($url, $url, $filename, $item->file_name);
    }, $allFiles));

    return $filelink;
}



// Returns grade, if submission has been graded, or "Yes" (translated) if
// there is a comment by the professor but no grade, or FALSE if neither
// grade nor professor comment is set
function submission_grade($subid) {
    global $langYes, $course_id;

    $res = Database::get()->querySingle("SELECT grade, grade_comments, assignment_id
                                                FROM assignment_submit
                                            WHERE id = ?d", $subid);
    if ($res) {
        $assignment_grading_data = Database::get()->querySingle("SELECT grading_type, grading_scale_id FROM assignment WHERE id = ?d", $res->assignment_id);
        if ($assignment_grading_data->grading_type == ASSIGNMENT_SCALING_GRADE) {
            $serialized_scale_data = Database::get()->querySingle("SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d", $assignment_grading_data->grading_scale_id, $course_id)->scales;
            $scales = unserialize($serialized_scale_data);
            foreach ($scales as $scale) {
                if ($res->grade == $scale['scale_item_value']) {
                    $grade = $scale['scale_item_name'];
                    break;
                }
            }
        } else {
            $grade = $res->grade;
        }

        if (!empty($grade)) {
            return trim($grade);
        } elseif (!empty($res->grade_comments)) {
            return $langYes;
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

/**
 * @brief Notify students by email about grade/comment submission
 * Send to single user for individual submissions or group members for group submissions
 * @param $assignment_id
 * @param $submission_id
 * @param $grade
 * @param $comments
 * @return void
 */
function grade_email_notify($assignment_id, $submission_id, $grade, $comments): void
{

    global $currentCourseName, $urlServer, $course_code, $langLinkFollows,
           $langWorkEmailSubject, $langGradebookGrade, $langComments, $langWorkEmailMessage;
    static $title, $group;

    if (!isset($title)) {
        $res = Database::get()->querySingle("SELECT title, group_submissions FROM assignment WHERE id = ?d", $assignment_id);
        $title = $res->title;
        $group = $res->group_submissions;
    }
    $info = Database::get()->querySingle("SELECT uid, group_id FROM assignment_submit WHERE id= ?d", $submission_id);

    $subject = sprintf($langWorkEmailSubject, $title);
    $grade_comments = '';
    if ($comments) {
        $grade_comments = "<div><strong>$langComments: </strong>$comments<br><br></div>\n";
    }

    $header_html_topic_notify = "<!-- Header Section -->
    <div id='mail-header'>
        <br>
        <div>
            <div id='header-title'>" . sprintf($langWorkEmailMessage, $title, $currentCourseName) . "</a></div>
        </div>
    </div>";

    $body_html_topic_notify = "<!-- Body Section -->
    <div id='mail-body'>
        <br>
        <div><b>$langGradebookGrade: </b> <span class='left-space'>$grade</span></div><br>
        $grade_comments
        $langLinkFollows <a href='{$urlServer}modules/work/index.php?course=$course_code&id=$assignment_id'>{$urlServer}modules/work/index.php?course=$course_code&id=$assignment_id</a>
    </div>";

    $body = $header_html_topic_notify.$body_html_topic_notify;

    $plainBody = html2text($body);
    if (!$group or !$info->group_id) {
        send_mail_to_user_id($info->uid, $subject, $plainBody, $body);
    } else {
        send_mail_to_group_id($info->group_id, $subject, $plainBody, $body);
    }
}


function triggerGame($courseId, $uid, $assignId) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = AssignmentEvent::ACTIVITY;
    $eventData->module = MODULE_ID_ASSIGN;
    $eventData->resource = intval($assignId);
    AssignmentEvent::trigger(AssignmentEvent::UPGRADE, $eventData);
}

function triggerAssignmentSubmit($courseId, $uid, $assignId) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = AssignmentSubmitEvent::ACTIVITY;
    $eventData->module = MODULE_ID_ASSIGN;
    $eventData->resource = intval($assignId);
    AssignmentSubmitEvent::trigger(AssignmentSubmitEvent::UPDATE, $eventData);
}

function triggerAssignmentAnalytics($courseId, $uid, $assignmentId, $eventname) {
    $data = new stdClass();
    $data->course_id = $courseId;
    $data->uid = $uid;
    $data->resource = $assignmentId;

    if ($eventname == AssignmentAnalyticsEvent::ASSIGNMENTGRADE) {
        $data->element_type = 40;
    } else if ($eventname == AssignmentAnalyticsEvent::ASSIGNMENTDL) {
        $data->element_type = 41;
    }
    AssignmentAnalyticsEvent::trigger($eventname, $data, true);
}

/**
 * @brief Export assignment's grades to CSV file
 * @param type $id
 */
function export_grades_to_csv($id) {

    global $course_code, $course_id, $langNoDeadline, $langNotice2,
           $langSurname, $langName, $langAm, $langGroup, $langScore,
           $langUsername, $langEmail, $langStartDate, $langGradebookGrade,
           $langGroupWorkDeadline_of_Submission;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($langScore);
    $sheet->getDefaultColumnDimension()->setWidth(30);

    $filename = $course_code . "_" . $id . "_grades_list.xlsx";

    // additional security
    $q = Database::get()->querySingle("SELECT id, title, submission_date, deadline FROM assignment
                            WHERE id = ?d AND course_id = ?d", $id, $course_id);
    if ($q) {
        $assignment_id = $q->id;
        if (!is_null($q->deadline)) {
            $deadline_message = "$langGroupWorkDeadline_of_Submission: " . format_locale_date(strtotime($q->deadline), 'full');
        } else {
            $deadline_message = $langNoDeadline;
        }
        $message = $q->title . " (" . $langStartDate .": ". format_locale_date(strtotime($q->submission_date), 'full') . "  ". $deadline_message .")";
        $data[] = [ $message ];
        $data[] = [];
        $data[] = [ $langSurname, $langName, $langNotice2, $langAm, $langGroup, $langUsername, $langEmail, $langGradebookGrade ];
        $data[] = [];
        $sql = Database::get()->queryFunc("SELECT MAX(uid) AS uid,
                                            CAST(MAX(grade) AS DECIMAL(10,2)) AS grade,
                                            MAX(submission_date) AS submission_date,
                                            MAX(surname) AS surname,
                                            MAX(givenname) AS givenname,
                                            username,
                                            MAX(am) AS am,
                                            MAX(email) AS email
                                           FROM assignment_submit JOIN user
                                               ON uid = user.id
                                           WHERE assignment_id = ?d
                                            GROUP BY username
                                            ORDER BY surname, givenname",
                function ($item)  use (&$data, $course_id) {
                    $ug = user_groups($course_id, $item->uid, 'txt');
                    $sub_date = format_locale_date(strtotime($item->submission_date));
                    $data[] = [ $item->surname, $item->givenname, $sub_date, $item->am, $ug, $item->username, $item->email, $item->grade ];
                }, $assignment_id);
    }
    $sheet->mergeCells("A1:H1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    for ($i = 1; $i <= 8; $i++) {
        $cells = [$i, 3];
        $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
    }
    // create spreadsheet
    $sheet->fromArray($data, NULL);
    // file output
    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    set_content_disposition('attachment', $filename);
    $writer->save("php://output");
    exit;
}

/**
 * @brief notify (via email) course admin about assignment submission
 * @param type $title
 */
function notify_for_assignment_submission($title) {

    global $logo, $langAssignmentPublished, $langTo, $course_id,
           $langsCourse, $langHasAssignmentPublished,
           $urlServer, $course_code, $langSender, $langAssignment;

    $emailSubject = "$logo - $langAssignmentPublished";
    $emailHeaderContent = "
        <!-- Header Section -->
        <div id='mail-header'>
            <br>
            <div>
                <div id='header-title'>$langHasAssignmentPublished $langTo $langsCourse <a href='{$urlServer}courses/$course_code/'>" . q(course_id_to_title($course_id)) . "</a>.</div>
                <ul id='forum-category'>
                    <li><span><b>$langSender:</b></span> <span class='left-space'>" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "</span></li>
                </ul>
            </div>
        </div>";

    $emailBodyContent = "
        <!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div><b>$langAssignment:</b> <span class='left-space'>".q($title)."</span></div><br>
        </div>";

    $emailContent = $emailHeaderContent . $emailBodyContent;
    $emailBody = html2text($emailContent);

    $profs = Database::get()->queryArray("SELECT user.id AS prof_uid, user.email AS email,
                              user.surname, user.givenname
                           FROM course_user JOIN user ON user.id = course_user.user_id
                           WHERE course_id = ?d AND course_user.status = " . USER_TEACHER . "", $course_id);

    foreach ($profs as $prof) {
        if (!get_user_email_notification_from_courses($prof->prof_uid) or (!get_user_email_notification($prof->prof_uid, $course_id))) {
            continue;
        } else {
            $to_name = $prof->givenname . " " . $prof->surname;
            if (!send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $to_name, $prof->email, $emailSubject, $emailBody, $emailContent)) {
                continue;
            }
        }
    }
}


/**
 * @brief download function
 * @param type $id
 * @param type $file_type
 * @return boolean
 */
function send_file($id, $file_type) {
    global $uid, $is_editor, $is_course_reviewer;

    $files_to_download = [];
    if (!$is_editor and is_module_disable(MODULE_ID_ASSIGN)) {
        return false;
    }

    if (isset($_GET['download']) and $_GET['download']) {
        $disposition = null;
    } else {
        $disposition = 'inline';
    }

    if (isset($file_type)) {
        if ($file_type == 1) {
            $info = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
            if (!$info) { // invalid (not found) assignment
                return false;
            }
            if (!$is_editor) { // don't show file to users if not active and before submission date
                if ((!$info->active) or (date("Y-m-d H:i:s") < $info->submission_date)) {
                    return false;
                }
                // make sure that user entered password and has been accepted
                if ($info->password_lock and (!isset($_SESSION['has_unlocked'][$id]) or !$_SESSION['has_unlocked'][$id])) {
                    return false;
                }
            }
            send_file_to_client("$GLOBALS[workPath]/admin_files/$info->file_path", $info->file_name, $disposition, true);
        } elseif ($file_type == 2) { // download comments file
            $info = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $id);
            if (!$info) {
                return false;
            }
            send_file_to_client("$GLOBALS[workPath]/admin_files/$info->grade_comments_filepath", $info->grade_comments_filename, $disposition, true);
        }
    } else {

        $info = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $id);
        if (!$info) {
            return false;
        }

        $a = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $info->assignment_id);

        if ($a->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
            $result = Database:: get()->queryArray("SELECT * FROM assignment_grading_review
                                        WHERE assignment_id = ?d
                                        AND users_id = ?d", $a->id, $uid);

            foreach ($result as $data) {
                $files_to_download[] = $data->file_path;
            }
            if (in_array($info->file_path, $files_to_download)) {
                send_file_to_client("$GLOBALS[workPath]/$info->file_path", $info->file_name, $disposition, true);
            }
        }

        if ($info->group_id) {
            initialize_group_info($info->group_id);
        }
        if (!($is_course_reviewer or $info->uid == $uid or $GLOBALS['is_member'])) {
            return false;
        }
        send_file_to_client("$GLOBALS[workPath]/$info->file_path", $info->file_name, $disposition, true);

    }
    exit;
}

/**
 * @brief send file for plagiarism check
 * @param type $assign_id
 * @param type $file_id
 * @param type $true_file_path
 * @param type $true_file_name
 * @global type $course_code
 */
function send_file_for_plagiarism($assign_id, $file_id, $true_file_path, $true_file_name) {

    global $course_code, $langPlagiarismAlreadyCheck, $langPlagiarismFileSent;

    if (!Plagiarism::get()->isFileSubmitted($file_id)) {
        Plagiarism::get()->submitFile($file_id, $true_file_path, $true_file_name);
        Session::flash('message',$langPlagiarismFileSent);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message',$langPlagiarismAlreadyCheck);
        Session::flash('alert-class', 'alert-warning');
    }
    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$assign_id");
}

/**
 * @brief Zip submissions to assignment $id and send it to user
 * @param type $id
 * @return boolean
 */
function download_assignments($id) {
    global $workPath, $course_code, $webDir;

    $sub_type = Database::get()->querySingle("SELECT submission_type FROM assignment WHERE id = ?d", $id)->submission_type;
    $counter = Database::get()->querySingle("SELECT COUNT(*) AS `count` FROM assignment_submit WHERE assignment_id = ?d", $id)->count;
    if ($counter) {
        ignore_user_abort(true); // needed to ensure zip file is deleted
        $secret = work_secret($id);
        $filename = "{$course_code}_work_$id.zip";
        $filepath = "$webDir/courses/temp/$filename";
        $temp_online_text_path = "$webDir/courses/temp/{$course_code}_work_$id";
        $zip = new ZipArchive();
        $zip->open($filepath, ZipArchive::CREATE);
        chdir($workPath);
        create_zip_index("$secret/index.html", $id);
        if ($sub_type == 1) { // free text assignment
            if (!is_dir($temp_online_text_path)) {
                mkdir($temp_online_text_path);
            }
            chdir($temp_online_text_path);
            $sql = Database::get()->queryArray("SELECT uid, submission_text FROM assignment_submit WHERE assignment_id = ?d", $id);
            foreach ($sql as $data) {
                $onlinetext = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => _MPDF_TEMP_PATH,
                ]);
                $onlinetext->WriteHTML($data->submission_text);
                $pdfname = strtr(greek_to_latin(uid_to_name($data->uid)), '\\/:', '___') . ".pdf";
                $onlinetext->Output($pdfname, 'F');
                unset($onlinetext);
            }
            foreach (glob('*.pdf') as $pdfname) {
                $zip->addFile($pdfname);
            }
            $zip->addFile("$workPath/$secret/index.html", "index.html");
        } else { // 'normal' assignment
            foreach (glob("$secret/*") as $file) {
                if (is_dir($file)) {
                    foreach (glob("$file/*") as $subfile) {
                        $zip->addFile($subfile, "work_$id/".substr($subfile, strlen($secret)+1));
                    }
                } elseif (file_exists($file) and is_readable($file)) {
                    $zip->addFile($file, "work_$id/".substr($file, strlen($secret)+1));
                }
            }
        }
        if ($zip->close()) {
            header("Content-Type: application/zip");
            set_content_disposition('attachment', $filename);
            header("Content-Length: " . filesize($filepath));
            stop_output_buffering();
            readfile($filepath);
        }
        if (file_exists($temp_online_text_path)) {
            removeDir($temp_online_text_path);
        }
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        exit;
    } else {
        return false;
    }
}


/**
 * @brief Create an index.html file for assignment $id listing user submissions
Set $online to TRUE to get an online view (on the web) - else the index.html works for the zip file
 * @param $path
 * @param $id
 * @param bool $online
 *
 */
function create_zip_index($path, $id) {
    global $charset, $course_id, $langGradebookGrade, $langSubDate,
           $langAssignment, $langAm, $langSurnameName, $langLateSubmission,
           $langGradeComments, $langComments, $langGroupSubmit, $langOfGroup;

    $fp = fopen($path, "w");
    if (!$fp) {
        die("Unable to create assignment index file - aborting");
    }
    fputs($fp, '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">
                <style type="text/css">
                .sep td, th { border: 1px solid; }
                td { border: none; padding: .1em .5em; }
                table { border-collapse: collapse; border: 2px solid; }
                .sep { border-top: 2px solid black; }
                </style>
    </head>
    <body>
        <table class="table-default">
            <tr>
                <th>' . $langSurnameName . '</th>
                <th>' . $langAm .  '</th>
                <th>' . $langAssignment . '</th>
                <th>' . $langSubDate . '</th>
                <th>' . $langGradebookGrade . '</th>
            </tr>');

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
    $assign_type = $assignment->submission_type;
    if ($assignment->grading_type == ASSIGNMENT_SCALING_GRADE) {
        $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assignment->grading_scale_id, $course_id)->scales;
        $scales = unserialize($serialized_scale_data);
        $scale_values = array_value_recursive('scale_item_value', $scales);
    }

    $submissions = Database::get()->queryArray("SELECT a.id, a.uid, a.file_path, a.file_name,
                a.submission_text, a.submission_date, a.grade, a.comments,
                a.grade_comments, a.group_id, b.deadline
            FROM assignment_submit a, assignment b
            WHERE a.assignment_id = ?d AND a.assignment_id = b.id
            ORDER BY a.id", $id);
    $seen = [];
    foreach ($submissions as $row) {
        if (in_array($row->id, $seen)) {
            continue;
        }
        if ($assign_type == 1) {
            $filename = greek_to_latin(uid_to_name($row->uid)) . ".pdf";
        } else {
            $filename = preg_replace('|^[^/]+/|', '', $row->file_path);
        }
        $filelink = empty($filename) ? '&nbsp;' :
            ("<a href='$filename'>" . q($row->file_name) . '</a>');

        // If further files exist for this submission
        if ($assign_type == 2 and strpos($filename, '/') !== false) {
            $otherFiles = Database::get()->queryArray('SELECT id, file_name, file_path
                FROM assignment_submit
                WHERE assignment_id = ?d AND uid = ?d AND group_id = ?d AND id <> ?d
                ORDER BY id', $id, $row->uid, $row->group_id, $row->id);
            foreach ($otherFiles as $file) {
                $seen[] = $file->id;
                $filename = preg_replace('|^[^/]+/|', '', $file->file_path);
                $filelink .= "<br><a href='$filename'>" . q($file->file_name) . '</a>';
            }
        }

        $late_sub_text = ((int) $row->deadline && $row->submission_date > $row->deadline) ?  "<div class='Accent-200-cl'>$langLateSubmission</div>" : '';
        if ($assignment->grading_type == ASSIGNMENT_SCALING_GRADE) {
            if ($assignment->grading_scale_id and !is_null($row->grade)) {
                $key = closest($row->grade, $scale_values)['key'];
                $row->grade = $scales[$key]['scale_item_name'];
            }
        }

        fputs($fp, '
            <tr class="sep">
                <td>' . q(uid_to_name($row->uid)) . '</td>
                <td>' . q(uid_to_am($row->uid)) . '</td>
                <td align="center">' . $filelink . '</td>
                <td align="center">' . $row->submission_date .$late_sub_text. '</td>
                <td align="center">' . $row->grade . '</td>
            </tr>');
        if (trim($row->comments != '')) {
            fputs($fp, "
            <tr><td colspan='6'><b>$langComments: " .
                "</b>$row->comments</td></tr>");
        }
        if (trim($row->grade_comments != '')) {
            fputs($fp, "
            <tr><td colspan='6'><b>$langGradeComments: " .
                "</b>$row->grade_comments</td></tr>");
        }
        if (!empty($row->group_id)) {
            fputs($fp, "<tr><td colspan='6'>$langGroupSubmit " .
                "$langOfGroup $row->group_id</td></tr>\n");
        }
    }
    fputs($fp, ' </table></body></html>');
    fclose($fp);
}


/*
 * @brief Show a simple html page with grades and submissions
 */
function show_plain_view($id) {
    global $workPath, $charset;

    $secret = work_secret($id);
    create_zip_index("$secret/index.html", $id);
    header("Content-Type: text/html; charset=$charset");
    readfile("$workPath/$secret/index.html");
    exit;
}


/**
 * @brief display users / groups with no assignment submissions
 * @param type $id
 */
function display_not_submitted($id) {
    global $course_id;

    $row = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
								CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
								CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due
                                FROM assignment
                                WHERE course_id = ?d AND id = ?d", $course_id, $id);

    $data = display_assignment_details($row);

    $data['group_submissions'] = false;
    if ($row->group_submissions) {
        $data['group_submissions'] = true;
        $groups = groups_with_no_submissions($id);
        $num_results = count($groups);
        $data['num_results'] = $num_results;
        $data['groups'] = $groups;
    } else {
        $users = users_with_no_submissions($id);
        $num_results = count($users);
        $data['num_results'] = $num_results;
        $data['users'] = $users;
    }
    $data['row'] = $row;

    view('modules.work.not_submitted', $data);
}


/*
 * @brief Return a list of users with no submissions for assignment $id
 */
function users_with_no_submissions($id) {
    global $course_id;
    if (Database::get()->querySingle("SELECT assign_to_specific FROM assignment WHERE id = ?d", $id)->assign_to_specific) {
        $q = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                FROM user, course_user
                                WHERE user.id = course_user.user_id
                                AND course_user.course_id = ?d
                                AND course_user.status = " . USER_STUDENT . "
                                AND user.id NOT IN (SELECT uid FROM assignment_submit WHERE assignment_id = ?d)
                                AND user.id IN (
                                    SELECT user_id FROM assignment_to_specific WHERE assignment_id = ?d
                                    UNION
                                    SELECT group_members.user_id FROM assignment_to_specific, group_members
                                        WHERE assignment_to_specific.group_id = group_members.group_id AND assignment_id = ?d)
                                ORDER BY surname, givenname", $course_id, $id, $id, $id);
    } else {
        $q = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                FROM user, course_user
                                WHERE user.id = course_user.user_id
                                AND course_user.course_id = ?d
                                AND course_user.status = " . USER_STUDENT . "
                                AND user.id NOT IN (SELECT uid FROM assignment_submit
                                                    WHERE assignment_id = ?d) ORDER BY surname, givenname", $course_id, $id);
    }
    $users = array();
    foreach ($q as $row) {
        $users[$row->id] = "$row->surname $row->givenname";
    }
    return $users;
}

/**
 * @brief submit grades to students
 * @param type $grades_id
 * @param type $grades
 * @param type $email
 */
function submit_grades($grades_id, $grades, $email = false) {
    global $langGrades, $course_id, $course_code, $langFormErrors,
           $langTheField, $langGradebookGrade;

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $grades_id);
    $errors = [];

    foreach ($grades['grades'] as $key => $grade) {
        $v = new Valitron\Validator($grade);
        $v->addRule('emptyOrNumeric', function($field, $value, array $params) {
            if(is_numeric($value) || empty($value)) return true;
        });
        $v->rule('emptyOrNumeric', array('grade'));
        $v->rule('min', array('grade'), 0);
        $v->rule('max', array('grade'), $assignment->max_grade);
        $v->labels(array(
            'grade' => "$langTheField $langGradebookGrade"
        ));
        if(!$v->validate()) {
            $valitron_errors = $v->errors();
            $errors["grade.$key"] = $valitron_errors['grade'];
        }
    }

    if(empty($errors)) {
        if(is_array($grades['grades'])) {
            foreach ($grades['grades'] as $sid => $grade) {
                $sid = intval($sid);
                $val = Database::get()->querySingle("SELECT grade from assignment_submit WHERE id = ?d", $sid)->grade;

                $grade = is_numeric($grade['grade']) ? $grade['grade'] : null;

                if ($val !== $grade) {
                    Database::get()->query("UPDATE assignment_submit
                                                SET grade = ?f, grade_submission_date = NOW(), grade_submission_ip = ?s
                                                WHERE id = ?d", $grade, Log::get_client_ip(), $sid);
                    $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                    triggerGame($course_id, $quserid, $assignment->id);
                    triggerAssignmentAnalytics($course_id, $quserid, $assignment->id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
                    triggerAssignmentAnalytics($course_id, $quserid, $assignment->id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
                    Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $sid,
                        'title' => $assignment->title,
                        'grade' => $grade));

                    //update gradebook if needed
                    if ($assignment->group_submissions) {
                        $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE id = ?d", $sid)->group_id;
                        $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                        foreach ($user_ids as $user_id) {
                            update_gradebook_book($user_id->user_id, $assignment->id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                        }
                    } else {
                        $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                        update_gradebook_book($quserid, $assignment->id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                    }
                    if ($email) {
                        grade_email_notify($grades_id, $sid, $grade, '');
                    }
                    Session::flash('message',$langGrades);
                    Session::flash('alert-class', 'alert-success');
                }
            }
        }

        Session::flash('message',$langGrades);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($errors);
    }
    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$grades_id");
}


/**
 * @brief submit grade and comment for student submission
 * @param type $args
 */
function submit_grade_reviews($args) {
    global $langGrades, $course_id, $course_code, $unit, $langFormErrors;

    $id = $args['assignment'];//assignment=id_ergasias exei topotheththei ws pedio hidden sto grade_edit_review
    $rubric = Database::get()->querySingle("SELECT * FROM rubric as a JOIN assignment as b WHERE b.course_id = ?d AND a.id = b.grading_scale_id AND b.id = ?d", $course_id, $id);

    $sid = $args['submission'];//asubimision=id_submision exei topotheththei ws pedio hidden sto grade_edit_review

    $v = new Valitron\Validator($args);
    $v->rule('numeric', array('assignment', 'submission'));
    $v->rule('required', array('grade_rubric'));

    if($v->validate()) {
        $grade_rubric = serialize($args['grade_rubric']);
        $criteria = unserialize($rubric->scales);
        $r_grade = 0;
        foreach ($criteria as $ci => $criterio) {
            if(is_array($criterio['crit_scales']) and isset($args['grade_rubric'][$ci])) {
                $r_grade += $criterio['crit_scales'][$args['grade_rubric'][$ci]]['scale_item_value'] * $criterio['crit_weight'];
            }
        }

        $grade = $r_grade/100;
        $grade = is_numeric($grade) ? $grade : null;
        $comment = $args['comments'];
        Database::get()->query("UPDATE assignment_grading_review
                                    SET grade = ?f, comments =?s, date_submit = " . DBHelper::timeAfter() . ", rubric_scales = ?s
                                    WHERE id = ?d",
                            $grade, $comment, $grade_rubric, $sid);

        Session::flash('message', $langGrades);
        Session::flash('alert-class', 'alert-success');

        if ($unit) {
            redirect_to_home_page("modules/units/index.php?course=$course_code&id=$unit");
        } else {
            redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/grade_edit_review.php?course=$course_code&assignment=$id&submission=$sid");
    }

}


/**
 * @brief submit grade and comment for student submission
 * @param type $args
 */
function submit_grade_comments($args): void
{

    global $langGrades, $course_id, $langTheField, $course_code,
           $langFormErrors, $workPath, $langGradebookGrade;

    if (isset($args['grade'])) {
        $args['grade'] = trim($args['grade']);
        $args['grade'] = $args['grade'] === '' ? null : fix_float($args['grade']);
    }

    $id = $args['assignment']; // assignment=id_ergasias hidden pedio sto grade_edit arxeio
    $sid = $args['submission'];
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $id);
    $grading_type = $assignment->grading_type;

    $v = new Valitron\Validator($args);
    $v->addRule('emptyOrNumeric', function($field, $value, array $params) {
        if(is_numeric($value) || empty($value)) return true;
    });
    $v->rule('numeric', array('assignment', 'submission'));
    $v->rule('emptyOrNumeric', array('grade'));
    $v->rule('min', array('grade'), 0);
    $v->rule('max', array('grade'), $assignment->max_grade);
    $v->labels(array(
        'grade' => "$langTheField $langGradebookGrade"
    ));

    if($v->validate()) {
        $grade_rubric = '';
        if ($grading_type == ASSIGNMENT_SCALING_GRADE) {
            $grade = $args['grade'];
        } else if ($grading_type == ASSIGNMENT_RUBRIC_GRADE) {
            $rubric = Database::get()->querySingle("SELECT * FROM rubric AS a  JOIN assignment AS b
                                                            WHERE b.course_id = ?d
                                                                AND a.id = b.grading_scale_id
                                                                AND b.id = ?d", $course_id, $id);
            $grade_rubric = serialize($args['grade_rubric']);
            $criteria = unserialize($rubric->scales);
            $r_grade = 0;
            foreach ($criteria as $ci => $criterio) {
                if (is_array($criterio['crit_scales'])) {
                    $r_grade += $criterio['crit_scales'][$args['grade_rubric'][$ci]]['scale_item_value'] * $criterio['crit_weight'];
                }
            }
            $grade = $r_grade/100;
        } else if ($grading_type == ASSIGNMENT_PEER_REVIEW_GRADE) {
            // edw tha kahoristei o telikos bathmos pou tha valei o kathghths
            $sum = 0;
            $count = 0;
            $users= Database::get()->queryArray("SELECT grade FROM assignment_grading_review WHERE user_submit_id = ?d", $sid);
            foreach ($users as $row){
                if ($row->grade){
                    $count = $count + 1;
                }
                $sum = $sum + $row->grade;
            }
            $grad = $sum / $count;
            $grade = number_format($grad,1);
        } else {
            $grade = $args['grade'];
        }

        $comment = (isset($args['comments']))? $args['comments'] : '';

        if (isset($_FILES['comments_file']) and is_uploaded_file($_FILES['comments_file']['tmp_name'])) { // upload comments file
            $comments_filename = $_FILES['comments_file']['name'];
            validateUploadedFile($comments_filename); // check file type
            $comments_filename = add_ext_on_mime($comments_filename);
            // File name used in file system and path field
            $safe_comments_filename = safe_filename(get_file_extension($comments_filename));
            if (move_uploaded_file($_FILES['comments_file']['tmp_name'], "$workPath/admin_files/$safe_comments_filename")) {
                @chmod("$workPath/admin_files/$safe_comments_filename", 0644);
                $comments_real_filename = $_FILES['comments_file']['name'];
                $comments_filepath = $safe_comments_filename;
            }
        } else {
            $comments_filepath = $comments_real_filename = '';
        }

        $grade = is_numeric($grade) ? $grade : null;
        if(isset($args['auto_judge_scenarios_output'])){
            Database::get()->query("UPDATE assignment_submit SET auto_judge_scenarios_output = ?s
                                    WHERE id = ?d",serialize($args['auto_judge_scenarios_output']), $sid);
        }
        if (Database::get()->query("UPDATE assignment_submit
                                    SET grade = ?f, grade_rubric = ?s, grade_comments = ?s,
                                    grade_comments_filepath = ?s,
                                    grade_comments_filename = ?s,
                                    grade_submission_date = NOW(), grade_submission_ip = ?s
                                    WHERE id = ?d", $grade, $grade_rubric, $comment, $comments_filepath,
                $comments_real_filename, Log::get_client_ip(), $sid)->affectedRows>0) {
            $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
            triggerGame($course_id, $quserid, $id);
            triggerAssignmentAnalytics($course_id, $quserid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
            triggerAssignmentAnalytics($course_id, $quserid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
            Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $sid,
                'title' => $assignment->title,
                'grade' => $grade,
                'comments' => $comment));
            if ($assignment->group_submissions) {
                $group_id = Database::get()->querySingle("SELECT group_id FROM assignment_submit WHERE id = ?d", $sid)->group_id;
                $user_ids = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
                foreach ($user_ids as $user_id) {
                    update_gradebook_book($user_id, $id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                }
            } else {
                //update gradebook if needed
                $quserid = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE id = ?d", $sid)->uid;
                update_gradebook_book($quserid, $id, $grade/$assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
            }
        }
        if (isset($args['email'])) {
            grade_email_notify($id, $sid, $grade, $comment);
        }
        Session::flash('message', $langGrades);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/work/grade_edit.php?course=$course_code&assignment=$id&submission=$sid");
    }
}

/**
 * @brief submit reviews per assignment. Used in peer assignments
 */
function submit_review_per_ass($id) {
    global $course_code, $langNoPeerReviewMultipleFiles;

    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d ",$id);
    $assign = Database::get()->queryArray("SELECT * FROM assignment_submit WHERE assignment_id = ?d ",$id);

    $del_submission_msg = delete_submissions($id);
    $success_msgs[] = $del_submission_msg;
    $value = 1;
    $value1 = 0;
    foreach ($assign as $row1) {
        $ass = Database::get()->queryArray("SELECT * FROM assignment_submit WHERE assignment_id = ?d LIMIT $assignment->reviews_per_assignment OFFSET $value", $id);

        $rowcount = count($ass);

        $count = $assignment->reviews_per_assignment - $rowcount;//oi ergasies pou leipoun
        foreach($ass as $row2) {
            if ($assignment->submission_type == 1) { // online text
                Database::get()->query("INSERT INTO assignment_grading_review ( assignment_id, user_submit_id, user_id, submission_text, submission_date, gid, users_id)
				VALUES (?d, ?d, ?d, ?s, ?t, ?d, ?d)", $id, $row1->id, $row1->uid, $row1->submission_text, $row1->submission_date, $row1->group_id, $row2->uid)->lastInsertID;
            } else if ($assignment->submission_type == 0) { // single file submission
                Database::get()->query("INSERT INTO assignment_grading_review ( assignment_id, user_submit_id, user_id, file_path, file_name, submission_date, gid, users_id)
				VALUES (?d, ?d, ?d, ?s, ?s, ?t, ?d, ?d)", $id, $row1->id, $row1->uid, $row1->file_path, $row1->file_name, $row1->submission_date, $row1->group_id, $row2->uid)->lastInsertID;
            } else if ($assignment->submission_type == 2) { // multiple file submission
                Session::flash('message', $langNoPeerReviewMultipleFiles);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
            }
        }
        if ($count != 0)
        {
            $assign1 = Database::get()->queryArray("SELECT * FROM assignment_submit WHERE assignment_id = ?d LIMIT $count OFFSET $value1", $id);
            foreach ($assign1 as $row3)
            {
                if ($assignment->submission_type == 1) { // online text
                    Database::get()->query("INSERT INTO assignment_grading_review ( assignment_id, user_submit_id, user_id, submission_text, submission_date, gid, users_id)
					VALUES (?d, ?d, ?d, ?s, ?t, ?d, ?d)", $id, $row1->id, $row1->uid, $row1->submission_text, $row1->submission_date, $row1->group_id, $row3->uid)->lastInsertID;
                } else if ($assignment->submission_type == 0) { // single file submission
                    Database::get()->query("INSERT INTO assignment_grading_review ( assignment_id, user_submit_id, user_id, file_path, file_name, submission_date, gid, users_id)
					VALUES (?d, ?d, ?d, ?s, ?s, ?t, ?d, ?d)", $id, $row1->id, $row1->uid, $row1->file_path, $row1->file_name, $row1->submission_date, $row1->group_id, $row3->uid)->lastInsertID;
                } else if ($assignment->submission_type == 2) { // multiple file submission
                    Session::flash('message', $langNoPeerReviewMultipleFiles);
                    Session::flash('alert-class', 'alert-warning');
                    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
                }
            }
        }
        $value++;
    }
    Session::flash('message', $success_msgs);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
}

/**
 * @brief Teacher view: for peer review submissions get student review status message
 * @param $start_date_review
 * @param $assignment_id
 * @param $user_id
 * @return string
 */
function get_review_status_message ($start_date_review, $assignment_id, $user_id): string
{
    global $langPeerReviewCompletedByStudent, $langPeerReviewPendingByStudent,
           $langPeerReviewMissingByStudent, $langQuestionCorrectionTitle2, $langFrom2;

    $cdate = date('Y-m-d H:i:s');
    $review_message = '';

    if ($cdate > $start_date_review) { //status aksiologhshs kathe foithth
        $assigns = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE assignment_id = ?d AND users_id = ?d", $assignment_id, $user_id);
        $r_count = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_grading_review WHERE assignment_id = ?d AND users_id = ?d", $assignment_id, $user_id)->count;
        $counter = 0;
        foreach ($assigns as $ass) {
            if (empty($ass->grade)) {
                $counter++;
            }
        }
        if ($counter == 0) {
            $review_message = "<div class='text-heading-h6' style='color: green;'>$langPeerReviewCompletedByStudent</div>&nbsp;";
        } elseif ($counter < $r_count) {
            $review_message = "<div class='text-heading-h6' style='color: darkorange;'>$langPeerReviewPendingByStudent<br>($langQuestionCorrectionTitle2 $counter $langFrom2 $r_count)</div>";
        } else {
            $review_message = "<div class='text-heading-h6' style='color: red;'>$langPeerReviewMissingByStudent</div>";
        }
    }
    return $review_message;
}


/**
 * @brief Teacher view: for peer review submissions get grade review field
 * @param $due_date_review
 * @param $user_submit_id
 * @param $reviews_per_assignment
 * @return string
 */
function get_grade_review_field($due_date_review, $user_submit_id, $reviews_per_assignment): string
{

    $cdate = date('Y-m-d H:i:s');
    $condition = '';
    $grade_review_field = "<input class='form-control' type='text' value='' name='grade_review' maxlength='4' size='3' disabled>";
    if ($cdate > $due_date_review) {
        //select tous vathmous ths kathe upovolhs kai vres ton mo kai topothethse ton sto pedio
        $grades= Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE user_submit_id = ?d", $user_submit_id);
        $count_grade = 0;
        $sum = 0;
        $grade_review = '';
        foreach ($grades as $as) {
            if ($as->grade){
                $count_grade++;
            }
            if ($count_grade == $reviews_per_assignment) {
                $condition = "<span class='fa fa-fw fa-check text-success' data-bs-toggle='tooltip' data-bs-placement='top' title='$count_grade/$reviews_per_assignment'></span>";
            } else {
                $condition = "<span class='fa fa-fw fa-xmark text-danger' data-bs-toggle='tooltip' data-bs-placement='top' title='$count_grade/$reviews_per_assignment'></span>";
            }
            $sum = $sum + $as->grade;
        }
        if ($sum != 0) {
            $grade = $sum / $count_grade;
            if (is_float($grade)) {
                $grade_review = number_format($grade,1);
            } else {
                $grade_review = $grade;
            }
        }
        $grade_review_field = "<input class='form-control' id='$user_submit_id' type='text' value='$grade_review' name='grade_review' maxlength='4' size='3' disabled>";
    }
    return $grade_review_field.$condition;
}

function resolve_lti_templates(): array {
    return Database::get()->queryArray('SELECT * FROM lti_apps WHERE enabled = true AND is_template = true AND type = ?s', TURNITIN_LTI_TYPE);
}

function resolve_lti_template_options(array $lti_templates, ?stdClass $assignment): string {
    $lti_template_options = "";
    $assignment_type = Session::has('assignment_type') ? Session::get('assignment_type') : 0;
    if (!is_null($assignment)) {
        $assignment_type = $assignment->assignment_type;
    }
    foreach ($lti_templates as $lti) {
        if (!is_null($assignment)) {
            $lti_template_options .= "<option value='$lti->id'" . (($assignment->lti_template == $lti->id && $assignment_type == ASSIGNMENT_TYPE_TURNITIN) ? " selected" : "") . ">$lti->title</option>";
        } else {
            $lti_template_options .= "<option value='$lti->id'>$lti->title</option>";
        }
    }
    return $lti_template_options;
}

function resolve_lti_template_1P3_ids_js(array $lti_templates): string {
    $lti_template_1P3_ids = [];
    foreach ($lti_templates as $lti) {
        if ($lti->lti_version === LTI_VERSION_1_3) {
            $lti_template_1P3_ids[] = $lti->id;
        }
    }
    return implode(",", $lti_template_1P3_ids);
}

function get_selected_content_indicator(): string {
    global $langTiiSelectedContent;
    return "<span><i class='icon fa fa-check text-success fa-fw me-1' title='$langTiiSelectedContent' role='img' aria-label='$langTiiSelectedContent'></i>$langTiiSelectedContent</span>";
}

function datetimeCreateAndFormat(?string $data, ?string $default): ?string {
    $ret = $default;
    if (!empty($data)) {
        $dt = DateTime::createFromFormat('d-m-Y H:i', $data);
        if ($dt !== false) {
            $ret = $dt->format('Y-m-d H:i:s');
        }
    }
    return $ret;
}

function check_turnitin_13_not_released(stdClass $assign): bool {
    $ret = false;
    if ($assign->assignment_type == ASSIGNMENT_TYPE_TURNITIN) {
        $lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d", $assign->lti_template);
        $ltiversion = $lti->lti_version;
        $cdate = date('Y-m-d H:i:s');
        if ($ltiversion === LTI_VERSION_1_3 && $cdate < $assign->tii_feedbackreleasedate) {
            $ret = true;
        }
    }
    return $ret;
}

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

$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'utilities.php';
require_once 'modules/group/group_functions.php';

$toolName = $langScore;
$unit = isset($unit)? $unit: null;

if (isset($_REQUEST['assignment']) && isset($_REQUEST['submission'])) {
    $as_id = intval($_REQUEST['assignment']);
    $sub_id = intval($_REQUEST['submission']);
    $assign = get_assignment_details($as_id);
    $cdate = date('Y-m-d H:i:s');

    if ($unit) {
        $navigation[] = array("url" => "index.php?course=$course_code&id=$unit", "name" => $langWorks);
    } else {
        $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langWorks);
        $navigation[] = array("url" => "index.php?course=$course_code&amp;id=$as_id", "name" => q($assign->title));
    }
    show_form($as_id, $sub_id, $assign);
} else {
    redirect_to_home_page('modules/work/index.php?course='.$course_code);
}

draw($tool_content, 2);

/**
 * @param type $id
 * @param type $sid
 * @param type $assign (contains an array with the assignment's details)
 */
function show_form($id, $sid, $assign) {
    global $unit, $langGradeOk, $tool_content, $course_code, $langCancel, $langGradebookGrade,
           $langBack, $assign, $langWorkOnlineText, $course_id, $pageName, $langEndPeerReview,
           $langReviewStart, $langReviewEnd, $langImgFormsDes, $langGradeComments,
           $langFileName, $langSGradebookBook, $langWorkNoSubmission;

    $pageName = $langSGradebookBook;

	$cdate = date('Y-m-d H:i:s');
	$sub = Database::get()->querySingle("SELECT * FROM assignment_grading_review WHERE id = ?d", $sid);
	if ($sub) {
		$comments = Session::has('comments') ? Session::get('comments') : q($sub->comments);
		//ean uparxei to submission_type grapse ws etiketa online keimeno me to keimeno tou dipla alliws grapse ws etiketa onoma arxeioy kai to arxeio dipla ws download link
		if($assign->submission_type) {
			$submission = "<div class='form-group mt-3'>
							<label class='col-sm-3 control-label-notes'>$langWorkOnlineText:</label>
							<div class='col-sm-9'>
								$sub->submission_text
							</div>
						</div>";
		} else {
		    if ($unit) {
		        $get_link = "view.php?course=$course_code&amp;res_type=assignment&amp;id=$_REQUEST[unit]&amp;get=$sub->user_submit_id";
            } else {
                $get_link = "index.php?course=$course_code&amp;get=$sub->user_submit_id";
            }
			$submission = "<div class='form-group mt-3'>
							<label class='col-sm-3 control-label-notes'>$langFileName:</label>
							<div class='col-sm-9'>
							<a href='$get_link'>".q($sub->file_name)."</a>
							</div>
						</div>";
		}
		//dialekse thn roubrika opou o kwdikos mathhmatos isoutai me ton kwdiko tou mathhmatos pou eimai twra kai to id=grading_scale_id pou exei topotheththei sthn vash apo prin
		$rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d ", $course_id, $assign->grading_scale_id);
		$criteria = unserialize($rubric->scales);
		$submitted_grade = Database::get()->querySingle("SELECT * FROM assignment_grading_review WHERE id = ?d", $sub->id);
        if (!is_null($submitted_grade->rubric_scales)) {
            $sel_criteria = unserialize($submitted_grade->rubric_scales);
        } else {
            $sel_criteria = [];
        }

		$criteria_list = "";
		foreach ($criteria as $ci => $criterio ) {
			$criteria_list .= "<li class='list-group-item'>$criterio[title_name] <b>($criterio[crit_weight]%)</b></li>";
			if(is_array($criterio['crit_scales'])){
				$criteria_list .= "<li><ul class='list-unstyled'>";
				foreach ($criterio['crit_scales'] as $si=>$scale) {
					$selectedrb = ((count($sel_criteria) > 0) && (isset($sel_criteria[$ci]) && $sel_criteria[$ci]==$si))?"checked=\"checked\"":"";
					$criteria_list .= "<li class='list-group-item'>
                                        <input type='radio' name='grade_rubric[$ci]' value='$si' $selectedrb> $scale[scale_item_name] ( $scale[scale_item_value] )
                                    </li>";
				}
				$criteria_list .= "</ul></li>";

			}
		}
		$grade_field = "<div class='col-sm-12' id='myModalLabel'>
            <div class='text-heading-h5'>$rubric->name</div>
            <div class='table-responsive'>
            <table class='table-default'>
                <tr>
                    <td>
                        <ul class='list-unstyled'>
                            $criteria_list
                        </ul>
                    </td>
                </tr>
            </table>
            </div>
		</div>";
        if ($unit) {
            $form_link = "view.php?res_type=assignment&course=$course_code&unit=$unit";
            $back_link = "index.php?course=$course_code&id=$unit";
            $cancel_link = "index.php?course=$course_code&id=$unit";
        } else {
            $form_link = "index.php?course=$course_code&id=$sub->assignment_id";
            $back_link = "index.php?course=$course_code&id=$sub->assignment_id";
            $cancel_link = "index.php?course=$course_code&id=$sub->assignment_id";
        }
		$tool_content .= action_bar(array(
			array(
					'title' => $langBack,
					'url' => "$back_link",
					'icon' => "fa-reply",
					'level' => 'primary'
					)
			));

		$tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
			<form class='form-horizontal' role='form' method='post' action='$form_link'>
                <input type='hidden' name='assignment' value='$id'>
                <input type='hidden' name='submission' value='$sid'>                    
                    <div class='form-group mt-4'>
                        <div class='col-sm-12 control-label-notes'>$langReviewStart</div>
                        <div class='col-sm-12'>
                            <span>" . format_locale_date(strtotime($assign->start_date_review)) . "</span>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <div class='col-sm-12 control-label-notes'>$langReviewEnd</div>
                        <div class='col-sm-12'>
                            <span>" . format_locale_date(strtotime($assign->due_date_review)) . "</span>
                        </div>
                    </div>
                    $submission
                    <div class='form-group".(Session::getError('grade') ? " has-error" : "")." mt-4'>
                        <div class='col-sm-6 control-label-notes'>$langGradebookGrade <span class='asterisk Accent-200-cl'>(*)</span></div>                        
                            $grade_field
                            <span class='help-block Accent-200-cl'>".(Session::hasError('grade') ? Session::getError('grade') : "")."</span>                        
                    </div>
                    <div class='form-group mt-4'>
                        <label for='comments' class='col-sm-12 control-label-notes'>$langGradeComments</label>
                        <div class='col-sm-12'>
                            <textarea class='form-control' rows='3' name='comments'  id='comments'>$comments</textarea>
                        </div>
                    </div>";
                    if ($assign->due_date_review > $cdate) {
                        $tool_content .="                        
                        <div class='form-group mt-5'>
                            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                <input class='btn submitAdminBtn' type='submit' name='grade_comments_review' value='$langGradeOk'>
                                <a class='btn cancelAdminBtn' href='$cancel_link'>$langCancel</a>
                            </div>
                        </div>";
                    } else {
                        Session::flash('message', $langEndPeerReview);
                        Session::flash('alert-class', 'alert-danger');
                    }
			$tool_content .= "
			</form>
		</div></div>
		<div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>
    </div>";
	} else {
		//an den uparxoun ergasies pou eginan submit
        Session::flash('message', $langWorkNoSubmission);
        Session::flash('alert-class', 'alert-danger');
		redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
	}
}

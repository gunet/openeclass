<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

$require_current_course = true;
$require_help = true;
$helpTopic = 'assignments';
$helpSubTopic = 'grades';

require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'modules/group/group_functions.php';

$toolName = $langScore;

load_js('tools.js');
// delete confirmation student review
$head_content .= "<script type='text/javascript'>";
$head_content .= '
        $(function () {
            $(document).on("click", ".linkdelete", function(e) {
                var link = $(this).attr("href");
                e.preventDefault();               
                bootbox.confirm({
                    closeButton: false,
                    title: "<div class=\"icon-modal-default\"><i class=\"fa-regular fa-trash-can fa-xl Accent-200-cl\"></i></div><div class=\"modal-title-default text-center mb-0\">'.js_escape($langConfirmDelete).'</div>",
                    message: "<p class=\"text-center\">'.js_escape($langConfirmDeleteStudentReview).'</p>",
                    buttons: {
                        cancel: {
                            label: "'.js_escape($langCancel).'",
                            className: "cancelAdminBtn position-center"
                        },
                        confirm: {
                            label: "'.js_escape($langDelete).'",
                            className: "deleteAdminBtn position-center",
                        }
                    },
                    callback: function (result) {
                        if(result) {
                            document.location.href = link;
                        }
                    }
                });


            });
        });
    ';
$head_content .= "</script>";

if (isset($_GET['ass_id']) ) { // delete student review
    $ass_id = intval($_GET['ass_id']);
    $id = intval($_GET['id']);
    $a_id = intval($_GET['a_id']);
    if (delete_review($ass_id)) {
        Session::flash('message',$langStudentReviewDeleted);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message',$langDelError);
        Session::flash('alert-class', 'alert-danger');
    }
    redirect_to_home_page('modules/work/grade_edit.php?course='.$course_code.'&assignment='.$id.'&submission='.$a_id);
}
if ($is_editor && isset($_GET['assignment']) && isset($_GET['submission'])) {
    $as_id = intval($_GET['assignment']);
    $sub_id = intval($_GET['submission']);
    $assign = get_assignment_details($as_id);

    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langWorks);
    $navigation[] = array("url" => "index.php?course=$course_code&amp;id=$as_id", "name" => q($assign->title));
    show_edit_form($as_id, $sub_id, $assign);
    draw($tool_content, 2, null, $head_content);
} else {
    redirect_to_home_page('modules/work/index.php?course='.$course_code);
}
/**
 * @brief Returns an array of the details of assignment $id
 * @global type $course_id
 * @param type $id
 * @return type
 */
function get_assignment_details($id) {
    global $course_id;
    return Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
}


/**
 * @brief delete user assignment review
 * @param type $id
 */
function delete_review($id) {
    if (Database::get()->query("DELETE FROM assignment_grading_review WHERE id = ?d", $id)->affectedRows > 0) {
        return true;
    }
    return false;
}



/**
 * @brief Show to professor details of a student's submission and allow editing of fields
 * @param type $id
 * @param type $sid
 * @param type $assign (contains an array with the assignment's details)
 */
function show_edit_form($id, $sid, $assign) {

    global $m, $langGradeOk, $tool_content, $course_code, $langCancel,
           $langBack, $assign, $langWorkOnlineText, $course_id, $langCommentsFile, $pageName,
           $langPeerReviewNoAssignments, $langNotGraded, $langDeletePeerReview,
           $langGradebookGrade, $langGradeRubric, $langNoAssignmentsForReview,
           $langOpenCoursesFiles, $urlAppend, $langImgFormsDes, $langSelect, $langForm;

    $grading_type = Database::get()->querySingle("SELECT grading_type FROM assignment WHERE id = ?d",$id)->grading_type;
    $sub = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $sid);
    $count_of_ass = Database::get()->querySingle("SELECT COUNT(*) FROM assignment_submit WHERE id = ?d", $id);

    ///$reviews_per_ass = Database::get()->querySingle("SELECT reviews_per_assignment FROM assignment WHERE id = ?d",$id)->reviews_per_assignment;
    $reviews_per_ass = Database::get()->querySingle('SELECT reviews_per_assignment FROM assignment WHERE id = ?d ', $id)->reviews_per_assignment;
    if ($sub) {
        if ($grading_type == 3 ) {
            $cdate = date('Y-m-d H:i:s');
            if($cdate < $assign->start_date_review){
                $tool_content .= "
                    <p class='sub_title1'></p>
                    <div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langPeerReviewNoAssignments</span></div></div>";
			}
			/*if ($cdate > $ass->deadline && $cdate > $ass->start_date_review){
				$tool_content .= "<div class='form-group'>
						<div class='col-sm-9 col-sm-offset-3'>
							<input class='btn btn-primary' type='submit' name='ass_review' value='Ανάθεση'>

						</div>
					</div>";
			}*/
            if ($cdate > $assign->start_date_review){
                //tha emfanistoun oi ergasies
                //$tool_content .= "<input type='' name='assign' value='$id'>";
                //$tool_content .= "<input type='' name='assign' value='$sid'>";
                $rows = Database::get()->queryArray("SELECT * FROM assignment_grading_review
                    WHERE assignment_id = ?d ", $id);
                if ($reviews_per_ass < $count_of_ass && $rows) {
                    $tool_content .= action_bar(array(
                        array(
                            'title' => $langBack,
                            'url' => "index.php?course=$course_code&id=$sub->assignment_id",
                            'icon' => "fa-reply",
                            'level' => 'primary'
                        )));
                    $ass = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE user_submit_id =?d ", $sid);
                    //$tool_content .= "<input type='' name='gra' value='$sub->id' />";
                    foreach($ass AS $row){
                        $uid_2_name = display_user($row->users_id);
                        $grade = Session::has('grade') ? Session::get('grade') : $row->grade;
                        $comments = Session::has('comments') ? Session::get('comments') : q($row->comments);
                        $pageName = $m['addgradecomments'];
                        //roubrika
                        $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d ", $course_id, $assign->grading_scale_id);
                        $criteria = unserialize($rubric->scales);
                        //$submitted_grade = Database::get()->querySingle("SELECT * FROM assignment_submit as a JOIN assignment as b WHERE course_id = ?d AND a.assignment_id = b.id AND b.id = ?d AND a.id = ?d", $course_id, $id, $sid);
                        $submitted_grade = Database::get()->querySingle("SELECT * FROM assignment_grading_review WHERE id = ?d ", $row->id);
                        $sel_criteria = unserialize($submitted_grade->rubric_scales);
                        $criteria_list = "";
                        foreach ($criteria as $ci => $criterio ) {
                            $criteria_list .= "<li class='list-group-item'>$criterio[title_name] <b>($criterio[crit_weight]%)</b></li>";
                            if(is_array($criterio['crit_scales'])){
                                $criteria_list .= "<li><ul class='list-unstyled'>";
                                foreach ($criterio['crit_scales'] as $si=>$scale) {
                                    $selectedrb = ($sel_criteria[$ci]==$si?"checked=\"checked\"":"");
                                    $criteria_list .= "<li class='list-group-item'>
                                        <input type='radio' name='grade_rubric[$ci]' value='$si' $selectedrb>
                                        $scale[scale_item_name] ( $scale[scale_item_value] )
                                    </li>";
                                }
                                $criteria_list .= "</ul></li>";

                            }
                        }
                        $grade_field = "<div class='col-12' id='myModalLabel'><h5>$rubric->name</h5>
                            <table class='table-default'>
                              <tr>
                                <td>
                                  <ul class='list-unstyled'>
                                    $criteria_list
                                  </ul>
                                </td>
                              </tr>
                            </table>
                          </div>";

                        if ($cdate > $assign->due_date_review and empty($row->grade)) {
                            $message = "<span style='color:#ff0000;text-align:center;'>$langNotGraded</span>";
                        } else {
                            $message = '';
                        }

                        $tool_content .= "
                        <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' role='form' method='post' enctype='multipart/form-data'>
                                    <input type='hidden' name='assignment' value='$id' />
                                    <input type='hidden' name='submission' value='$row->id' />
                                    <div class='btn-group float-end'>
                                        <a aria-label='$langDeletePeerReview' class='linkdelete btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;a_id=$sub->id&amp;ass_id=$row->id' data-bs-placement='bottom' data-bs-toggle='tooltip' title='$langDeletePeerReview' data-bs-original-title=''>
                                            <span class='fa-solid fa-xmark'></span>
                                        </a>
                                    </div>
                                    <div class='row form-group mt-4'>
                                        <div class='col-12 control-label-notes'>$m[username]</div>
                                        $uid_2_name &nbsp; $message
                                    </div>

                                    <div class='row form-group mt-4'>
                                        <div class='col-12 control-label-notes'>$m[sub_date]</div>
                                        <div class='col-12'>
                                            <span>".q($row->date_submit)."</span>
                                        </div>
                                    </div>
                                    <div class='row form-group".(Session::getError('grade') ? " has-error" : "")." mt-4'>
                                        <div class='col-12 control-label-notes'>$langGradeRubric <span class='asterisk Accent-200-cl'>(*)</span></div>
                                        $grade_field
                                        <span class='help-block Accent-200-cl'>".(Session::hasError('grade') ? Session::getError('grade') : "")."</span>
                                    </div>
                                    <div class='row form-group mt-4'>
                                        <div class='col-12 control-label-notes'>$langGradebookGrade</div>
                                        <div class='col-12'>
                                            <span>".q($row->grade)."</span>
                                        </div>
                                    </div>
                                    <div class='row form-group mt-4'>
                                        <div class='col-12 control-label-notes'>$m[gradecomments]</div>
                                        <div class='col-12'>
                                            <span>".q($row->comments)."</span>
                                        </div>
                                    </div>
                                </form>
                            </div></div>";
                    }
                    $tool_content.= "
                            <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code' enctype='multipart/form-data'>
                                <input type='hidden' name='assignment' value='$id' />
                                <input type='hidden' name='submission' value='$sid' />";

                                if (get_user_email_notification($sub->uid, $course_id)) {
                                    $tool_content .= "<div class='form-group'>
                                        <div class='col-sm-9 col-sm-offset-3'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label='$langSelect'>
                                                    <input type='checkbox' value='1' id='email_button' name='email' checked>
                                                    <span class='checkmark'></span>
                                                    $m[email_users]
                                                </label>
                                            </div>
                                        </div>
                                    </div>";
                                }
                                $tool_content .= "<div class='form-group'>
                                    <div class='col-12 d-inline-flex justify-content-end gap-2'>
                                        <input class='btn submitAdminBtn' type='submit' name='grade_comments' value='$langGradeOk'>
                                        <a class='btn cancelAdminBtn' href='index.php?course=$course_code&id=$sub->assignment_id'>$langCancel</a>
                                    </div>
                                </div>
                            </form>";
                } else {
                    $tool_content .= "
                            <p class='sub_title1'></p>
                            <div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAssignmentsForReview</span></div></div>";
                }
            }
        }

		//an den exoyme peer_review
		else {
			$uid_2_name = display_user($sub->uid);
			if (!empty($sub->group_id)) {
				$group_submission = "($m[groupsubmit] $m[ofgroup] " .
						"<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>"
						 . gid_to_name($sub->group_id) . "</a>)";
			} else {
				$group_submission = '';
			}
			$grade = Session::has('grade') ? Session::get('grade') : $sub->grade;
			$comments = Session::has('comments') ? Session::get('comments') : q($sub->grade_comments);
			$pageName = $m['addgradecomments'];
			if ($assign->submission_type == 1) {
                // online text
                $submission = "
                        <div class='row form-group mt-4'>
							<div class='col-12 control-label-notes'>$langWorkOnlineText</div>
                            <div class='col-12'>
                                <p class='form-control-static'>$sub->submission_text</p>
							</div>
						</div>";
            } elseif ($assign->submission_type == 2 and preg_match('|/.*/|', $sub->file_path)) {
                // multiple files
                $files = Database::get()->queryArray('SELECT id, file_name
                    FROM assignment_submit
                    WHERE assignment_id = ?d AND uid = ?d AND group_id = ?d
                    ORDER BY id', $sub->assignment_id, $sub->uid, $sub->group_id);
                $links = implode('<br>', array_map(function ($file) {
                    global $urlAppend, $course_code;
                    return "<a href='{$urlAppend}modules/work/index.php?course=$course_code&amp;get=$file->id'>" .
                        q($file->file_name) . "</a>";
                }, $files));
                $submission = "
                        <div class='row form-group mt-4'>
							<div class='col-12 control-label-notes'>$langOpenCoursesFiles</div>
							<div class='col-12'><p class='form-control-static'>$links</p></div>
						</div>";
            } else {
                // single file
                $submission = "
                        <div class='row form-group mt-4'>
							<div class='col-12 control-label-notes'>$m[filename]</div>
							<div class='col-12'>
                                <p class='form-control-static'>
                                    <a href='index.php?course=$course_code&amp;get=$sub->id'>".q($sub->file_name)."</a>
                                </p>
							</div>
						</div>";
			}
			if ($assign->grading_scale_id) {
				if ($grading_type == ASSIGNMENT_SCALING_GRADE) {
					$serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assign->grading_scale_id, $course_id)->scales;
					$scales = unserialize($serialized_scale_data);
					$scale_options = "<option value> - </option>";
					$scale_values = array_value_recursive('scale_item_value', $scales);
					if (!in_array($sub->grade, $scale_values) && !is_null($sub->grade)) {
						$sub->grade = closest($sub->grade, $scale_values)['value'];
					}
					foreach ($scales as $scale) {
						$scale_options .= "<option value='$scale[scale_item_value]'".($sub->grade == $scale['scale_item_value'] ? " selected" : "").">$scale[scale_item_name]</option>";
					}
					$grade_field = "<div class='col-12'><select aria-label='$scale_options' name='grade' class='form-select' id='scales'>$scale_options</select></div>";
				} elseif ($grading_type == ASSIGNMENT_RUBRIC_GRADE) {
					$rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d ", $course_id, $assign->grading_scale_id);
					$criteria = unserialize($rubric->scales);
					$submitted_grade = Database::get()->querySingle("SELECT * FROM assignment_submit as a
                                                                                JOIN assignment as b
                                                                              WHERE course_id = ?d
                                                                              AND a.assignment_id = b.id
                                                                              AND b.id = ?d
                                                                              AND a.id = ?d", $course_id, $id, $sid);
					if (!empty($submitted_grade->grade_rubric)) {
                        $sel_criteria = unserialize($submitted_grade->grade_rubric);
                    } else {
                        $sel_criteria = array();
                    }

					$criteria_list = "";
					foreach ($criteria as $ci => $criterio ) {
						$criteria_list .= "<li class='list-group-item'>$criterio[title_name] <b>($criterio[crit_weight]%)</b></li>";
						if(is_array($criterio['crit_scales'])) {
							$criteria_list .= "<li><ul class='list-unstyled'>";
							foreach ($criterio['crit_scales'] as $si=>$scale) {
							    if (!isset($sel_criteria[$ci])) {
                                    $sel_criteria[$ci] = '';
                                }
								$selectedrb = ($sel_criteria[$ci]==$si?"checked=\"checked\"":"");
								$criteria_list .= "<li class='list-group-item'>
                                    <input type='radio' name='grade_rubric[$ci]' value='$si' $selectedrb>
                                    $scale[scale_item_name] ( $scale[scale_item_value] )
                                    </li>";
							}
							$criteria_list .= "</ul></li>";
						}
					}
					$grade_field = "<div class='col-12' id='myModalLabel'><div class='TextBold large-text'>$rubric->name</div>
								<table class='table-default'>
								<tr>
									<td>
										<ul class='list-unstyled'>
											$criteria_list
										</ul>
									</td>
								</tr>
								</table>
								</div>";
				}
			} else {
				$grade_field = "<div class='col-12'>"
							  . "<input aria-label='$m[max_grade]' class='form-control' type='text' name='grade' maxlength='4' size='3' value='$sub->grade'> ($m[max_grade]: $assign->max_grade)"
							  . "</div>";

			}
			$tool_content .= "
                <div class='d-lg-flex gap-4 mt-4'>
                <div class='flex-grow-1'>
			<div class='form-wrapper form-edit rounded'>
				<form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code' enctype='multipart/form-data'>
				<input type='hidden' name='assignment' value='$id'>
				<input type='hidden' name='submission' value='$sid'>
				<fieldset>
                    <legend class='mb-0' aria-label='$langForm'></legend>
					<div class='row form-group'>
						<div class='col-12 control-label-notes'>$m[username]</div>
						<div class='col-12'>
						$uid_2_name $group_submission
						</div>
					</div>

					<div class='row form-group mt-4'>
						<div class='col-12 control-label-notes'>$m[sub_date]</div>
						<div class='col-12'>
							".format_locale_date(strtotime($sub->submission_date))."
						</div>
					</div>

					$submission

					<div class='row form-group".(Session::getError('grade') ? " has-error" : "")." mt-4'>
						<div class='col-12 control-label-notes'>$langGradebookGrade <span class='asterisk Accent-200-cl'>(*)</span></div>
							$grade_field
							<span class='help-block Accent-200-cl'>".(Session::hasError('grade') ? Session::getError('grade') : "")."</span>
					</div>

					<div class='row form-group mt-4'>
						<label for='comments' class='col-12 control-label-notes'>$m[gradecomments]</label>
						<div class='col-12'>
							<textarea class='form-control' rows='3' name='comments'  id='comments'>$comments</textarea>
						</div>
					</div>

					<div class='row form-group mt-4'>
						<label for='comments_file' class='col-12 control-label-notes'>$langCommentsFile</label>
						<div class='col-12'>
							<input type='file' name='comments_file' id='comments_file' size='35'>
							" . fileSizeHidenInput() . "
						</div>
					</div>";

                   if (get_user_email_notification($sub->uid, $course_id)) {
                       $tool_content .= "<div class='form-group mt-4'>
						<div class='col-12'>
							<div class='checkbox'>
                            <label class='label-container' aria-label='$langSelect'>
									<input type='checkbox' value='1' id='email_button' name='email' checked>
                                    <span class='checkmark'></span>
									$m[email_users]
								</label>
							</div>
						</div>
					</div>";
                   }

                    $tool_content .= "
					<div class='form-group mt-5'>
						<div class='col-12 d-inline-flex justify-content-end gap-2'>
							<input class='btn submitAdminBtn' type='submit' name='grade_comments' value='$langGradeOk'>
							<a class='btn cancelAdminBtn' href='index.php?course=$course_code&id=$sub->assignment_id'>$langCancel</a>

						</div>
					</div>

				</fieldset>
				</form>
			</div></div>
			<div class='d-none d-lg-block'>
                <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
            </div>
        </div>";
		}
    } else {
        Session::flash('message',$m['WorkNoSubmission']);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
    }
}

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
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';

$toolName = $langScore;

load_js('tools.js');
load_js('screenfull/screenfull.min.js');

// delete confirmation student review
$head_content .= "
<script type='text/javascript'>
    $(function () {
        $(document).on('click', '.linkdelete', function(e) {
            var link = $(this).attr('href');
            e.preventDefault();
            bootbox.confirm('" . js_escape($langConfirmDeleteStudentReview) . "', function(result) {
                if (result) {
                    document.location.href = link;
                }
            });
        });

        initialize_filemodal({
            download: '" . js_escape($langDownload) . "',
            print: '" . js_escape($langPrint) . "',
            fullScreen: '" . js_escape($langFullScreen) . "',
            newTab: '" . js_escape($langNewTab) . "',
            cancel: '" . js_escape($langCancel) . "'
        });

    });
</script>";
ModalBoxHelper::loadModalBox();

if (isset($_GET['ass_id']) ) { // delete student review
    $ass_id = intval($_GET['ass_id']);
    $id = intval($_GET['id']);
    $a_id = intval($_GET['a_id']);
    if (delete_review($ass_id)) {
        Session::Messages($langStudentReviewDeleted, 'alert-success');
    } else {
        Session::Messages($langDelError, 'alert-danger');
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
 * @global type $langNoAssignmentsForReview
 * @global type $langGradeOk
 * @global string $tool_content
 * @global type $course_code
 * @global type $langCancel
 * @global type $langBack
 * @global type $assign
 * @global type $langWorkOnlineText
 * @global type $course_id
 * @global type $langCommentsFile
 * @global type $langGradebookGrade
 * @global type $langDeleteSubmission
 * @global type $pageName
 * @param type $id
 * @param type $sid
 * @param type $assign (contains an array with the assignment's details)
 */
function show_edit_form($id, $sid, $assign) {

    global $m, $langGradeOk, $tool_content, $course_code, $langCancel,
           $langBack, $assign, $langWorkOnlineText, $course_id, $langCommentsFile, $pageName,
           $langPeerReviewNoAssignments, $langNotGraded, $langDeletePeerReview,
           $langGradebookGrade, $langGradeRubric, $langNoAssignmentsForReview,
           $langOpenCoursesFiles;

    $grading_type = Database::get()->querySingle("SELECT grading_type FROM assignment WHERE id = ?d",$id)->grading_type;
    $sub = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $sid);
    $count_of_ass = Database::get()->querySingle("SELECT COUNT(*) FROM assignment_submit WHERE id = ?d", $id);

    ///$reviews_per_ass = Database::get()->querySingle("SELECT reviews_per_assignment FROM assignment WHERE id = ?d",$id)->reviews_per_assignment;
    $reviews_per_ass = Database::get()->querySingle('SELECT reviews_per_assignment FROM assignment WHERE id = ?d ', $id)->reviews_per_assignment;
    if ($sub) {
        if ($grading_type == 3) {
            $cdate = date('Y-m-d H:i:s');
            if ($cdate < $assign->start_date_review) {
                $tool_content .= "
                    <p class='sub_title1'></p>
                    <div class='alert alert-warning'>$langPeerReviewNoAssignments</div>";
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
                            'level' => 'primary-label'
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
                        $grade_field = "<div class='col-sm-9' id='myModalLabel'><h5>$rubric->name</h5>
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
						    <div class='form-wrapper'>
                                <form class='form-horizontal' role='form' method='post' enctype='multipart/form-data'>
                                    <input type='hidden' name='assignment' value='$id' />
                                    <input type='hidden' name='submission' value='$row->id' />
                                    <div class='btn-group pull-right'>
                                        <a class='linkdelete btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;a_id=$sub->id&amp;ass_id=$row->id' data-placement='bottom' data-toggle='tooltip' title='$langDeletePeerReview' data-original-title=''>
                                            <span class='fa fa-times'></span>
                                        </a>
                                    </div>
                                    <div class='form-group'>
                                        <label class='col-sm-3 control-label'>$m[username]:</label>
                                        $uid_2_name &nbsp $message
                                    </div>

                                    <div class='form-group'>
                                        <label class='col-sm-3 control-label'>$m[sub_date]:</label>
                                        <div class='col-sm-9'>
                                            <span>".q($row->date_submit)."</span>
                                        </div>
                                    </div>
                                    <div class='form-group".(Session::getError('grade') ? " has-error" : "")."'>
                                        <label for='grade' class='col-sm-3 control-label'>$langGradeRubric:</label>
                                        $grade_field
                                        <span class='help-block'>".(Session::hasError('grade') ? Session::getError('grade') : "")."</span>
                                    </div>
                                    <div class='form-group'>
                                        <label class='col-sm-3 control-label'>$langGradebookGrade:</label>
                                        <div class='col-sm-9'>
                                            <span>".q($row->grade)."</span>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <label class='col-sm-3 control-label'>$m[gradecomments]:</label>
                                        <div class='col-sm-9'>
                                            <span>".q($row->comments)."</span>
                                        </div>
                                    </div>
                                </form>
                            </div>";
                    }
                    $tool_content.= "
                            <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code' enctype='multipart/form-data'>
                                <input type='hidden' name='assignment' value='$id' />
                                <input type='hidden' name='submission' value='$sid' />";

                                if (get_user_email_notification($sub->uid, $course_id)) {
                                    $tool_content .= "<div class='form-group'>
                                            <div class='col-sm-9 col-sm-offset-3'>
                                                <div class='checkbox'>
                                                    <label>
                                                        <input type='checkbox' value='1' id='email_button' name='email' checked>
                                                        $m[email_users]
                                                    </label>
                                                </div>
                                            </div>
                                        </div>";
                                }

                                $tool_content .= "
                                 <div class='form-group'>
                                    <div class='col-sm-9 col-sm-offset-3'>
                                        <input class='btn btn-primary' type='submit' name='grade_comments' value='$langGradeOk'>
                                        <a class='btn btn-default' href='index.php?course=$course_code&id=$sub->assignment_id'>$langCancel</a>
                                    </div>
                                </div>
                            </form>";
                } else {
                    $tool_content .= "
                            <p class='sub_title1'></p>
                            <div class='alert alert-warning'>$langNoAssignmentsForReview</div>";
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
                        <div class='form-group'>
							<label class='col-sm-3 control-label'>$langWorkOnlineText:</label>
                            <div class='col-sm-9'>
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
                    $url = "{$urlAppend}modules/work/index.php?course=$course_code&amp;get=$file->id";
                    return MultimediaHelper::chooseMediaAhrefRaw($url, $url, $file->file_name, $file->file_name);
                }, $files));
                $submission = "
                        <div class='form-group'>
							<label class='col-sm-3 control-label'>$langOpenCoursesFiles:</label>
							<div class='col-sm-9'><p class='form-control-static'>$links</p></div>
						</div>";
            } else {
                // single file
                $url = "index.php?course=$course_code&amp;get=$sub->id";
                $submission = "
                        <div class='form-group'>
							<label class='col-sm-3 control-label'>$m[filename]:</label>
							<div class='col-sm-9'>
                                <p class='form-control-static'>" .
                                    MultimediaHelper::chooseMediaAhrefRaw($url, $url, $sub->file_name, $sub->file_name) . "
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
					$grade_field = "<div class='col-sm-3'><select name='grade' class='form-control' id='scales'>$scale_options</select></div>";
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
					$grade_field = "<div class='col-sm-9' id='myModalLabel'><h5>$rubric->name</h5>
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
				$grade_field = "<div class='col-sm-3'>"
							  . "<input class='form-control' type='text' name='grade' maxlength='4' size='3' value='$sub->grade'> ($m[max_grade]: $assign->max_grade)"
							  . "</div>";

			}
            if ($sub->grade_comments_filepath) {
                $comments_url = "$GLOBALS[urlAppend]modules/work/?course=$course_code&amp;getcomment=$sub->id";
                $comments_file = "<p class='form-control-static'>" .
                    MultimediaHelper::chooseMediaAhrefRaw($comments_url, $comments_url, $sub->grade_comments_filename, $sub->grade_comments_filename) .
                    "</p>";
            } else {
                $comments_file = '';
            }
			$tool_content .= action_bar(array(
					array(
						'title' => $langBack,
						'url' => "index.php?course=$course_code&id=$sub->assignment_id",
						'icon' => "fa-reply",
						'level' => 'primary-label'
					)
				))."
			<div class='form-wrapper'>
				<form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code' enctype='multipart/form-data'>
				<input type='hidden' name='assignment' value='$id'>
				<input type='hidden' name='submission' value='$sid'>
				<fieldset>
					<div class='form-group'>
						<label class='col-sm-3 control-label'>$m[username]:</label>
						<div class='col-sm-9'>
						$uid_2_name $group_submission
						</div>
					</div>
					<div class='form-group'>
						<label class='col-sm-3 control-label'>$m[sub_date]:</label>
						<div class='col-sm-9'>
							".format_locale_date(strtotime($sub->submission_date))."
						</div>
					</div>
					$submission
					<div class='form-group".(Session::getError('grade') ? " has-error" : "")."'>
						<label for='grade' class='col-sm-3 control-label'>$langGradebookGrade:</label>
							$grade_field
							<span class='help-block'>".(Session::hasError('grade') ? Session::getError('grade') : "")."</span>
					</div>
					<div class='form-group'>
						<label for='comments' class='col-sm-3 control-label'>$m[gradecomments]:</label>
						<div class='col-sm-9'>
							<textarea class='form-control' rows='3' name='comments'  id='comments'>$comments</textarea>
						</div>
					</div>
					<div class='form-group'>
						<label for='comments_file' class='col-sm-3 control-label'>$langCommentsFile:</label>
                        <div class='col-sm-9'>
                            $comments_file
							<input type='file' name='comments_file' id='comments_file' size='35'>
							" . fileSizeHidenInput() . "
						</div>
					</div>";

                    if (get_user_email_notification($sub->uid, $course_id)) {
                        $tool_content .= "<div class='form-group'>
                            <div class='col-sm-9 col-sm-offset-3'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' value='1' id='email_button' name='email' checked>
                                        $m[email_users]
                                    </label>
                                </div>
                            </div>
                        </div>";
					}

					$tool_content .= "
					<div class='form-group'>
						<div class='col-sm-9 col-sm-offset-3'>
							<input class='btn btn-primary' type='submit' name='grade_comments' value='$langGradeOk'>
							<a class='btn btn-default' href='index.php?course=$course_code&id=$sub->assignment_id'>$langCancel</a>
						</div>
					</div>
				</fieldset>
				</form>
			</div>";
		}
    } else {
        Session::Messages($m['WorkNoSubmission'], 'alert-danger');
        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
    }
}

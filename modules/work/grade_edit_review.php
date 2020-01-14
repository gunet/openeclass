<?php
$require_current_course = true;
//This component is the core of eclass. Each and every file that requires output to the user's browser must include this file and use the view() or draw() calls to output the UI to the user's browser.
require_once '../../include/baseTheme.php';//
require_once 'functions.php';
require_once 'modules/group/group_functions.php';

$toolName = $langScore;
if ( isset($_GET['assignment']) && isset($_GET['submission'])) {
    $as_id = intval($_GET['assignment']);
    $sub_id = intval($_GET['submission']);
    $assign = get_assignment_details($as_id);
    $cdate = date('Y-m-d H:i:s');

    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langWorks);
    $navigation[] = array("url" => "index.php?course=$course_code&amp;id=$as_id", "name" => q($assign->title));

    //if ($assign->due_date_review > $cdate ) {
    show_form($as_id, $sub_id, $assign);
    draw($tool_content, 2);
    //}else{
     //   redirect_to_home_page("modules/work/index.php?course=$course_code&amp;id=$as_id");
    //}

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
 *            
 * @global type $m
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
 * @global type $pageName
 * @param type $id
 * @param type $sid
 * @param type $assign (contains an array with the assignment's details)
 */
function show_form($id, $sid, $assign) {
    global $m, $langGradeOk, $tool_content, $course_code, $langCancel, $langGradebookGrade,
           $langBack, $assign, $langWorkOnlineText, $course_id, $pageName, $langEndPeerReview,
           $langReviewStart, $langReviewEnd;

	$cdate = date('Y-m-d H:i:s');
	$sub = Database::get()->querySingle("SELECT * FROM assignment_grading_review WHERE id = ?d", $sid);
	if ($sub) {
		$uid_2_name = display_user(false);//anwnymo
		/*if (!empty($sub->gid)) {
			//$group_submission = "($m[groupsubmit] $m[ofgroup] " .
					  //  "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>"
					   //  . gid_to_name($sub->group_id) . "</a>)";
			$group_submission = "(Ομαδική εργασία)";
		} else {
			$group_submission = '';
		}*/
		$comments = Session::has('comments') ? Session::get('comments') : q($sub->comments);
		$pageName = $m['addgradecomments'];//addgradecomments=Προσθήκη σχολίων βαθμολογητή
		//ean uparxei to to submission_type grapse ws etiketa online keimeno me to keimeno tou dipla alliws grapse ws etiketa onoma arxeioy kai to arxeio dipla ws download link
		if($assign->submission_type) {
			$submission = "<div class='form-group'>
							<label class='col-sm-3 control-label'>$langWorkOnlineText:</label>
							<div class='col-sm-9'>
								$sub->submission_text
							</div>
						</div>";
		} else {
			$submission = "<div class='form-group'>
							<label class='col-sm-3 control-label'>$m[filename]:</label>
							<div class='col-sm-9'>
							<a href='index.php?course=$course_code&amp;get=$sub->user_submit_id'>".q($sub->file_name)."</a>
							</div>
						</div>";
		}
		//dialekse thn roubrika opou o kwdikos mathhmatos isoutai me ton kwdiko tou mathhmatos pou eimai twra kai to id=grading_scale_id pou exei topotheththei sthn vash apo prin
		$rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d ", $course_id, $assign->grading_scale_id);
		$criteria = unserialize($rubric->scales);
		//$submitted_grade = Database::get()->querySingle("SELECT * FROM assignment_submit as a JOIN assignment as b WHERE course_id = ?d AND a.assignment_id = b.id AND b.id = ?d AND a.id = ?d", $course_id, $id, $sid);
		$submitted_grade = Database::get()->querySingle("SELECT * FROM assignment_grading_review WHERE id = ?d ", $sub->id);
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
		$tool_content .= action_bar(array(
			array(
					'title' => $langBack,
					'url' => "index.php?course=$course_code&id=$sub->assignment_id",
					'icon' => "fa-reply",
					'level' => 'primary-label'
					)
			))."
		<div class='form-wrapper'>
			<form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code&id=$sub->assignment_id' enctype='multipart/form-data'>
			<input type='hidden' name='assignment' value='$id'>
			<input type='hidden' name='submission' value='$sid'>
			<fieldset>
				<div class='form-group'>
					<label class='col-sm-3 control-label'>$m[username]:</label>
					<div class='col-sm-9'>
					$uid_2_name 
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 control-label'>$langReviewStart:</label>
					<div class='col-sm-9'>
						<span>".q($assign->start_date_review)."</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 control-label'>$langReviewEnd:</label>
					<div class='col-sm-9'>
						<span>".q($assign->due_date_review)."</span>
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
				</div>";
				if ($assign->due_date_review > $cdate) {
					$tool_content .="
					<div class='form-group'>
						<div class='col-sm-9 col-sm-offset-3'>
							<div class='checkbox'>
								<label>
									<input type='checkbox' value='1' id='email_button' name='email' checked>
									$m[email_users]
								</label>
							</div>
						</div>
					</div>
					<div class='form-group'>
						<div class='col-sm-9 col-sm-offset-3'>
							<input class='btn btn-primary' type='submit' name='grade_comments_review' value='$langGradeOk'>
							<a class='btn btn-default' href='index.php?course=$course_code&id=$sub->assignment_id'>$langCancel</a>
						</div>
					</div>";
				} else {
					Session::Messages("$langEndPeerReview", 'alert-danger');
				}
			$tool_content .="
			</fieldset>
			</form>
		</div>";
	} else {
		//an den uparxoun ergasies pou eginan submit
		Session::Messages($m['WorkNoSubmission'], 'alert-danger');
		redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
	}
}
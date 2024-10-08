<?php

$require_current_course = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'modules/work/functions.php';
require_once 'modules/gradebook/functions.php';
require_once 'modules/progress/AssignmentEvent.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';

$id = $_GET['id'];
$assignment = Database::get()->querySingle('SELECT * FROM assignment WHERE id = ?d', $id);

if (isset($_FILES['userfile'])) {
    $file = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['userfile']['tmp_name']);
    $sheet = $file->getActiveSheet();
    $gradeComments = $userGrades = $errorLines = $invalidUsers = $extraUsers = [];
    foreach ($sheet->getRowIterator() as $row) {
        $data = [];
        $cellIterator = $row->getCellIterator();
        foreach ($cellIterator as $cell) {
            $value = trim($cell->getValue() ?? '');
            if ($value !== '') {
                $data[] = $value;
            }
        }

        if (!in_array(count($data), [2, 3]) or !is_numeric($data[1]) or $data[1] < 0 or $data[1] > $assignment->max_grade) {
            $errorLines[] = $data;
        }

        if (preg_match('/\(([^)]+)\)/', $data[0], $matches)) {
            $username = $matches[1];
        } else {
            $username = $data[0];
        }
        $uname_where = (get_config('case_insensitive_usernames')) ? "COLLATE utf8mb4_general_ci = " : "COLLATE utf8mb4_bin = ";
        $user = Database::get()->querySingle("SELECT * FROM user WHERE username $uname_where ?s", $username);

        if (!$user) {
            $invalidUsers[] = $username;
        } else {
            $submission = Database::get()->querySingle('SELECT id FROM assignment_submit
                WHERE uid = ?d AND assignment_id = ?d',
                $user->id, $id);
            if (!$submission) {
                $extraUsers[] = $username;
            } else {
                $userGrades[$user->id] = $data[1];
                if (isset($data[2])) {
                    $gradeComments[$user->id] = $data[2];
                }
            }
        }
    }
    if (!($errorLines or $invalidUsers or $extraUsers)) {
        foreach ($userGrades as $user_id => $grade) {
            if (isset($gradeComments[$user_id])) {
                Database::get()->query('UPDATE assignment_submit
                    SET grade = ?f, grade_comments = ?s,
                        grade_submission_date = NOW(), grade_submission_ip = ?s
                    WHERE uid = ?d AND assignment_id = ?d',
                    $grade, $gradeComments[$user_id], Log::get_client_ip(), $user_id, $id);
            } else {
                Database::get()->query('UPDATE assignment_submit
                    SET grade = ?f, grade_submission_date = NOW(), grade_submission_ip = ?s
                    WHERE uid = ?d AND assignment_id = ?d',
                    $grade, Log::get_client_ip(), $user_id, $id);
            }
            triggerGame($course_id, $user_id, $id);
            update_gradebook_book($user_id, $id, $grade / $assignment->max_grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
        }
        Session::Messages($langGradesImported, 'alert-success');
        redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id");
    } else {
        $message = $langImportGradesError;
        if ($invalidUsers) {
            $errorText = implode('', array_map(function ($username) {
                return '<li>' . q($username) . '</li>';
            }, $invalidUsers));
            $message .= "<p>$langImportInvalidUsers<ul>$errorText</ul></p>";
        }
        if ($extraUsers) {
            $errorText = implode('', array_map(function ($username) {
                return '<li>' . q($username) . '</li>';
            }, $extraUsers));
            $message .= "<p>$langImportExtraUsers<ul>$errorText</ul></p>";
        }
        if ($errorLines) {
            $errorText = implode('', array_map(function ($line) {
                $line = array_map('q', $line);
                return '<tr class="danger"><td>' . implode('</td><td>', $line) . '</td></tr>';
            }, $errorLines));
            $message .= "<p>$langImportErrorLines
                    <table class='table table-condensed table-bordered table-striped'>
                        <tbody>$errorText</tbody>
                    </table></p>";
        }
        Session::Messages($message, 'alert-danger');
        redirect_to_home_page("modules/work/import.php?course=$course_code&id=$id");
    }
}

$pageName = $langImportGrades;

$navigation[] = ['url' => "index.php?course=$course_code", 'name' => $langWorks];
$navigation[] = ['url' => "index.php?course=$course_code&amp;id=$id", 'name' => q($assignment->title)];

$tool_content .= action_bar([
    [ 'title' => $langBack,
      'level' => 'primary-label',
      'url' => "index.php?course=$course_code&amp;id=$id",
      'icon' => 'fa-reply']]);

enableCheckFileSize();
$tool_content .= "
    <div class='row'>
        <div class='col-sm-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' enctype='multipart/form-data' method='post' action='import.php?course=$course_code&amp;id=$id'>
                    <fieldset>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>$langImportGradesHelp</p>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='userfile' class='col-sm-2 control-label'>$langWorkFile:</label>
                            <div class='col-sm-10'>" . fileSizeHidenInput() . "
                                <input type='file' id='userfile' name='userfile'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>" .
                                form_buttons([[ 'class' => 'btn-primary',
                                                'name' => 'new_assign',
                                                'value' => $langUpload,
                                                'javascript' => '' ],
                                              [ 'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code" ]]) . "
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>";

draw($tool_content, 2, null, $head_content);

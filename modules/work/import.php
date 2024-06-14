<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

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
    $file = IOFactory::load($_FILES['userfile']['tmp_name']);
    $sheet = $file->getActiveSheet();
    $gradeComments = $userGrades = $errorLines = $invalidUsers = $extraUsers = [];
    foreach ($sheet->getRowIterator() as $row) {
        $data = [];
        $cellIterator = $row->getCellIterator();
        foreach ($cellIterator as $cell) {
            $value = trim($cell->getValue());
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

        Session::flash('message',$langGradesImported);
        Session::flash('alert-class', 'alert-success');
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

        Session::flash('message',$message);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/work/import.php?course=$course_code&id=$id");
    }
}

$pageName = $langImportGrades;

$navigation[] = ['url' => "index.php?course=$course_code", 'name' => $langWorks];
$navigation[] = ['url' => "index.php?course=$course_code&amp;id=$id", 'name' => q($assignment->title)];

enableCheckFileSize();
$tool_content .= "

<div class='d-lg-flex gap-4 mt-4'>
<div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' enctype='multipart/form-data' method='post' action='import.php?course=$course_code&amp;id=$id'>
                    <fieldset>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>$langImportGradesHelp</p>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='userfile' class='col-sm-12 control-label-notes'>$langWorkFile:</label>
                            <div class='col-sm-12'>" . fileSizeHidenInput() . "
                                <input type='file' id='userfile' name='userfile'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <div class='col-12 d-flex justify-content-end'>" .
                                form_buttons([[ 'class' => 'submitAdminBtn',
                                                'name' => 'new_assign',
                                                'value' => $langUpload,
                                                'javascript' => '' ],
                                              [ 'class' => 'cancelAdminBtn',
                                                  'href' => "index.php?course=$course_code&id=$id" ]
                                            ]) . "
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
    </div>";

draw($tool_content, 2, null, $head_content);

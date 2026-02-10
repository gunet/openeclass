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

/**
 * @file index.php
 * @brief main script for the questionnaire tool
 */

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$require_current_course = TRUE;
$require_user_registration = true;
$require_help = TRUE;
$helpTopic = 'questionnaire';
require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_QUESTIONNAIRE);
/* * *********************************** */

$toolName = $langQuestionnaire;

load_js('tools.js');
load_js('datatables');

if ($is_editor) {
    // disable ordering for action button column
    $columns = 'null, null, null, { orderable: false }';
} else {
    $columns = 'null, null, null';
}

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#polls').DataTable ({
                'columns': [ null, null, null, { orderable: false } ],
                'fnDrawCallback': function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
                'lengthMenu': [10, 20, 30 , -1],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[1, 'desc']],
                'oLanguage': {
                   'lengthLabels': {
                        '-1': '$langAllOfThem'
                    },
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });

            $('.dt-search input').attr({
                 'class' : 'form-control input-sm ms-0 mb-3',
                 'placeholder' : '$langSearch...'
            });
            $('.dt-search label').attr('aria-label', '$langSearch');

            $(document).on('click', '.assigned_to', function(e) {
                  e.preventDefault();
                  var pid = $(this).data('pid');
                  url = '$urlAppend' + 'modules/questionnaire/index.php?poll_info_assigned_to=true&pid=' + pid;
                  $.ajax({
                    url: url,
                    success: function(data) {
                        var dialog = bootbox.dialog({
                            message: data,
                            title : '$langWorkAssignTo',
                            onEscape: true,
                            backdrop: true,
                            buttons: {
                                success: {
                                    label: '$langClose',
                                    className: 'btn-success',
                                }
                            }
                        });
                        dialog.init(function() {
                            typeof MathJax !== 'undefined' && MathJax.typeset();
                        });
                    }
                  });
            });
        });
        </script>";

if (isset($_GET['cancelPoll'])) {
    unset($_SESSION['current_page']);
    unset($_SESSION['data_answers']);
    unset($_SESSION['data_file_answer']);
    unset($_SESSION['question_ids']);
    unset($_SESSION['q_row_columns']);
    unset($_SESSION['loop_init_answers']);
    unset($_SESSION['emptyQuestions']);
    unset($_SESSION['user_removed_file']);
}

if (isset($_GET['verification_code'])) {
    $afftected_rows = Database::get()->query("UPDATE poll_user_record SET email_verification = 1, verification_code = NULL WHERE verification_code = ?s", $_GET['verification_code'])->affectedRows;
    if ($afftected_rows > 0) {
        Session::flash('message',$langPollParticipationValid);
        Session::flash('alert-class', 'alert-success');
    }
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
if ($is_editor) {

    if (isset($_GET['download_qrcode'])) {
        $pID = intval($_GET['download_qrcode']);
        $fileNamePoll = $pID . '_QRcode.svg';
        $dload_filename = "$webDir/courses/$course_code/pollQrCode/$pID/$fileNamePoll";
        //send_file_to_client($dload_filename, $fileNamePoll, null, true, false);

        $filePath = $dload_filename; // Update this with the actual path to your SVG file
        // Check if the file exists
        if (file_exists($filePath)) {
            // Set the appropriate headers to trigger a download
            header('Content-Description: File Transfer');
            header('Content-Type: image/svg+xml');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));

            // Clear output buffer
            ob_clean();
            flush();

            // Read the file and send it to the output buffer
            readfile($filePath);
            exit;
        } else {
            // Handle the error if the file does not exist
            echo "File not found.";
        }
    }

    // QR code generation for specific poll
    if (isset($_GET['gen_qrcode'])) {

        $pollID = intval($_GET['pollId']);
        $fileNamePoll = $pollID . '_QRcode.svg';

        // Initialize URL and parameters
        $string = $urlServer . "/modules/questionnaire/pollparticipate.php?course=" . $course_code . "&UseCase=1&pid=" . $pollID;

        // Create QR Code
        $renderer = new ImageRenderer(new RendererStyle(256), new SvgImageBackEnd());
        $writer = new Writer($renderer);

        // Generate the QR image
        $qr_image = $writer->writeString($string);

        // Base64 encode the SVG string
        $qr_image_base64 = base64_encode($qr_image);

        // Prepare the data URL for the SVG
        $qr_image_data_url = "data:image/svg+xml;base64," . $qr_image_base64;

        // Specify the path where you want to save the image
        $qrCode_dir = "$webDir/courses/$course_code/pollQrCode/$pollID";
        if (!file_exists($qrCode_dir)) {
            mkdir("$webDir/courses/$course_code/pollQrCode/$pollID", 0755, true);
        }
        if (file_exists($qrCode_dir . '/' . $fileNamePoll)) {
            unlink($qrCode_dir . '/' . $fileNamePoll);
        }
        file_put_contents($qrCode_dir . '/' . $fileNamePoll, $qr_image);

        $downloadURL = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&download_qrcode=$pollID";

        // Prepare JavaScript content for modal
        $head_content .= "
            <script type='text/javascript'>
                $(document).ready(function() {
                    var downloadURL = '$downloadURL';
                    var bts = {
                        download: {
                            label: '" . js_escape($langDownload) . "',
                            className: 'submitAdminBtn gap-1',
                            callback: function (d) {
                                var anchor = document.createElement('a');
                                anchor.href = downloadURL;
                                anchor.target = '_blank';
                                anchor.download = '$fileNamePoll';
                                anchor.click();
                            }
                        }
                    };

                    if (screenfull.enabled) {
                        bts.fullscreen = {
                            label: '" . js_escape($langFullScreen) . "',
                            className: 'submitAdminBtn gap-1',
                            callback: function() {
                                screenfull.request(document.getElementById('fileFrame'));
                                return false;
                            }
                        };
                    }
                    bts.cancel = {
                        label: '" . js_escape($langCancel) . "',
                        className: 'cancelAdminBtn'
                    };

                    bootbox.dialog({
                        size: 'large',
                        title: '" . js_escape($langGenQrCode) . "',
                        onEscape: function() {},
                        backdrop: true,
                        message: '<div class=\"row\">' +
                                    '<div class=\"col-sm-12\">' +
                                        '<div class=\"iframe-container\" style=\"height:300px;\">' +
                                            '<iframe title=\"{$langGenQrCode}\" id=\"fileFrame\" src=\"{$qr_image_data_url}\" style=\"width:260px; height:260px; margin:auto; display:block;\"></iframe>' +
                                        '</div>' +
                                    '</div>' +
                                 '</div>',
                        buttons: bts
                    });
                });
            </script>
        ";
    }

    // info about polls assigned to users and groups
    if (isset($_GET['poll_info_assigned_to'])) {
        echo "<ul>";
        $q = Database::get()->queryArray("SELECT user_id, group_id FROM poll_to_specific WHERE poll_id = ?d", $_GET['pid']);
        foreach ($q as $user_data) {
            if (is_null($user_data->user_id)) { // assigned to group
                $group_name = Database::get()->querySingle("SELECT name FROM `group` WHERE id = ?d", $user_data->group_id)->name;
                echo "<li>$group_name</li>";
            } else { // assigned to user
                echo "<li>" . q(uid_to_name($user_data->user_id)) . "</li>";
            }
        }
        echo "</ul>";
        exit;
    }

    if (isset($_GET['pid'])) {
        $pid = $_GET['pid'];
        $p = Database::get()->querySingle("SELECT pid FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
        if(!$p){
            redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
        }
        // activate / deactivate polls
        if (isset($_GET['visibility'])) {
            switch ($_GET['visibility']) {
                case 'activate':
                    Database::get()->query("UPDATE poll SET active = 1 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                    Session::flash('message',$langPollActivated);
                    Session::flash('alert-class', 'alert-success');
                    break;
                case 'deactivate':
                    if (!resource_belongs_to_progress_data(MODULE_ID_QUESTIONNAIRE, $pid)) {
                        Database::get()->query("UPDATE poll SET active = 0 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                        Session::flash('message',$langPollDeactivated);
                        Session::flash('alert-class', 'alert-success');
                    } else {
                        Session::flash('message',$langResourceBelongsToCert);
                        Session::flash('alert-class', 'alert-warning');
                    }
                    break;
            }
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }
        if (isset($_GET['access'])) {
            switch ($_GET['access']) {
                case 'public':
                    Database::get()->query("UPDATE poll SET public = 1 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                    Session::flash('message',$langPollUnlocked);
                    Session::flash('alert-class', 'alert-success');
                    break;
                case 'limited':
                    Database::get()->query("UPDATE poll SET public = 0 WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                    Session::flash('message',$langPollLocked);
                    Session::flash('alert-class', 'alert-success');
                    break;
            }
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }
        // delete polls
        if (isset($_GET['delete']) and $_GET['delete'] == 'yes') {
            if (!resource_belongs_to_progress_data(MODULE_ID_QUESTIONNAIRE, $pid)) {
                $poll_title = Database::get()->querySingle("SELECT name FROM poll WHERE course_id = ?d", $course_id)->name;
                Database::get()->query("DELETE FROM poll_question_answer WHERE pqid IN
                            (SELECT pqid FROM poll_question WHERE pid = ?d)", $pid);
                Database::get()->query("DELETE FROM `poll_to_specific` WHERE poll_id = ?d", $pid);
                $deleted_rows = Database::get()->query("DELETE FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
                Database::get()->query("DELETE FROM poll_question WHERE pid = ?d", $pid);
                Database::get()->query("DELETE FROM poll_user_record WHERE pid = ?d", $pid);

                if ($deleted_rows > 0) {
                    Log::record($course_id, MODULE_ID_QUESTIONNAIRE, LOG_DELETE, array('title' => $poll_title));
                }
                Session::flash('message',$langPollDeleted);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
            } else {
                Session::flash('message',$langResourceBelongsToCert);
                Session::flash('alert-class', 'alert-warning');
            }
        // delete poll results
        } elseif (isset($_GET['delete_results']) && $_GET['delete_results'] == 'yes') {
            unset($_SESSION['current_page']);
            unset($_SESSION['data_answers']);
            unset($_SESSION['data_file_answer']);
            unset($_SESSION['question_ids']);
            unset($_SESSION['q_row_columns']);
            unset($_SESSION['loop_init_answers']);
            unset($_SESSION['user_removed_file']);
            $folder = "$webDir/courses/$course_code/poll_$pid";
            deleteFolderContents($folder);
            Database::get()->query("DELETE FROM poll_user_record WHERE pid = ?d", $pid);
            $poll_title = Database::get()->querySingle("SELECT name FROM poll WHERE course_id = ?d", $course_id)->name;
            Log::record($course_id, MODULE_ID_QUESTIONNAIRE, LOG_DELETE, array('title' => $poll_title, 'legend' => 'purge_results'));
            Session::flash('message',$langPollResultsDeleted);
            Session::flash('alert-class', 'alert-success');

            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        //clone poll
        } elseif (isset($_POST['clone_to_course_id'])) {
            $clone_course_id = $_POST['clone_to_course_id'];
            if ($is_admin) {
                $ok = true;
            } else {
                $ok = Database::get()->querySingle("SELECT course_id FROM course_user
                    WHERE user_id = ?d AND course_id = ?d AND
                          (status = ?d OR editor = 1)",
                    $uid, $clone_course_id, USER_TEACHER);
            }
            if ($ok) {
                $poll = Database::get()->querySingle("SELECT * FROM poll WHERE pid = ?d", $pid);
                $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);

                if ($clone_course_id == $course_id) {
                    $poll->name .= " ($langCopy2)";
                } else {
                    $clone_course = Database::get()->querySingle('SELECT title, code FROM course WHERE id = ?d', $clone_course_id);
                }
                $poll_data = array(
                    $poll->creator_id,
                    $clone_course_id,
                    $poll->name,
                    $poll->creation_date,
                    $poll->start_date,
                    $poll->end_date,
                    purify($poll->description),
                    purify($poll->end_message),
                    $poll->anonymized,
                    $poll->assign_to_specific,
                    $poll->show_results,
                    $poll->multiple_submissions,
                    $poll->default_answer,
                    $poll->pagination,
                    $poll->require_answer,
                    $poll->options
                );
                $new_pid = Database::get()->query("INSERT INTO poll
                                    SET creator_id = ?d,
                                        course_id = ?d,
                                        name = ?s,
                                        creation_date = ?t,
                                        start_date = ?t,
                                        end_date = ?t,
                                        description = ?s,
                                        end_message = ?s,
                                        anonymized = ?d,
                                        assign_to_specific = ?d,
                                        show_results = ?d,
                                        multiple_submissions = ?d,
                                        default_answer = ?d,
                                        active = 1,
                                        pagination = ?d,
                                        require_answer = ?d,
                                        options = ?s", $poll_data)->lastInsertID;
                if ($poll->assign_to_specific) {
                    Database::get()->query("INSERT INTO `poll_to_specific` (user_id, group_id, poll_id)
                                            SELECT user_id, group_id, ?d FROM `poll_to_specific`
                                            WHERE poll_id = ?d", $new_pid, $pid)->lastInsertID;
                }
                foreach ($questions as $question) {
                    $q_description = !empty($question->description) ? $question->description : '';
                    $answer_scales = !empty($question->answer_scale) ? $question->answer_scale : '';
                    $q_row = $question->q_row;
                    $q_column = $question->q_column;
                    $total_weight = !is_null($question->total_weight) ? $question->total_weight : 0;
                    $new_pqid = Database::get()->query("INSERT INTO poll_question
                                               SET pid = ?d,
                                                   question_text = ?s,
                                                   qtype = ?d,
                                                   q_position = ?d,
                                                   q_scale = ?d,
                                                   `description` = ?s,
                                                   answer_scale = ?s,
                                                   q_row = ?d,
                                                   q_column = ?d,
                                                   `page` = ?d,
                                                   require_response = ?d,
                                                   total_weight = ?d,
                                                   has_sub_question = ?d", 
                                                   $new_pid, $question->question_text, $question->qtype, 
                                                   $question->q_position, $question->q_scale, $q_description, 
                                                   $answer_scales, $q_row, $q_column,
                                                   $question->page, $question->require_response, $total_weight, $question->has_sub_question)->lastInsertID;
                    $answers = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d ORDER BY pqaid", $question->pqid);
                    foreach ($answers as $answer) {
                        Database::get()->query("INSERT INTO poll_question_answer
                                                SET pqid = ?d,
                                                    answer_text = ?s,
                                                    sub_question = ?d,
                                                    `weight` = ?d,
                                                    sub_qid = ?d", $new_pqid, $answer->answer_text, $answer->sub_question, $answer->weight, $answer->sub_qid);
                    }
                }

                // Update the sub-question ids from poll_question_answer table.
                $newQidsWithSubQ = Database::get()->queryArray("SELECT pqid FROM poll_question WHERE pid = ?d AND has_sub_question = ?d", $new_pid, 1);
                $newSubQids = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d AND has_sub_question = ?d", $new_pid, -1);
                $assocArrQ = [];
                if (count($newSubQids) > 0) {
                    foreach ($newSubQids as $q) {
                        $oldQidWithSubQ = Database::get()->querySingle("SELECT pqid FROM poll_question 
                                                                 WHERE pid = ?d
                                                                 AND question_text = ?s
                                                                 AND qtype = ?d
                                                                 AND q_position = ?d
                                                                 AND `page` = ?d
                                                                 AND has_sub_question = ?d", $pid, $q->question_text, $q->qtype, $q->q_position, $q->page, $q->has_sub_question)->pqid;
                        $assocArrQ[$oldQidWithSubQ] = $q->pqid;
                    }
                }
                if (count($assocArrQ) > 0 && count($newQidsWithSubQ) > 0) {
                    foreach ($newQidsWithSubQ as $q) {
                        $new_answers = Database::get()->queryArray("SELECT pqaid,sub_qid FROM poll_question_answer WHERE pqid = ?d", $q->pqid);
                        if (count($new_answers) > 0) {
                            foreach ($new_answers as $an) {
                                if (isset($assocArrQ[$an->sub_qid]) && $an->sub_qid > 0) {
                                    Database::get()->query("UPDATE poll_question_answer SET sub_qid = ?d WHERE pqaid = ?d", $assocArrQ[$an->sub_qid], $an->pqaid);
                                }
                            }
                        }
                    }
                }

                $message = $langCopySuccess;
                if (isset($clone_course)) {
                    $clone_code = q($clone_course->code);
                    $message .= "<br><a href='{$urlAppend}modules/questionnaire/index.php?course=$clone_code'>$langViewShow</a>";
                } else {
                    $show_link = '';
                }
                Session::flash('message',$message);
                Session::flash('alert-class', 'alert-success');
            }
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }
    }
    $action_bar = action_bar(array(
            array('title' => $langCreatePoll,
                  'url' => "admin.php?course=$course_code&amp;newPoll=yes",
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success')
            ));
    $tool_content .= $action_bar;

}

printPolls();
add_units_navigation(TRUE);

draw($tool_content, 2, null, $head_content);


/**
 * @brief print polls
 */
function printPolls() {
    global $tool_content, $course_id, $course_code, $urlAppend,
        $langTitle, $langCancel, $langOpenParticipation,
        $langFrom, $langTill, $langPollNone, $is_editor, $is_course_reviewer, $langAnswers,
        $langEditChange, $langDelete, $langSurveyNotStarted, $langResourceAccessLock,
        $langDeactivate, $langHasExpired, $langActivate, $langResourceAccessUnlock,
        $langParticipate,  $langHasParticipated, $langSee,
        $langHasNotParticipated, $uid, $langConfirmDelete, $langResults,
        $langPurgeExercises, $langConfirmPurgeExercises, $langCreateDuplicate,
        $langCreateDuplicateIn, $langCurrentCourse, $langDate,
        $langUserDuration, $m, $langQuickSurvey, $langChoiceLesson, $langGenQrCode,
        $langWorkToUser, $langWorkAssignTo, $langWorkToGroup;

    $poll_check = 0;
    $query = "SELECT * FROM poll WHERE course_id = ?d";
    $query_params[] = $course_id;
    // Bring only those assigned to the student
    if (!$is_course_reviewer) {
        $gids = user_group_info($uid, $course_id);
        if (!empty($gids)) {
            $gids_sql_ready = implode(',',array_keys($gids));
        } else {
            $gids_sql_ready = "''";
        }
        $query .= " AND
                    (assign_to_specific = '0' OR assign_to_specific != '0' AND pid IN
                       (SELECT poll_id FROM poll_to_specific WHERE user_id = ?d UNION SELECT poll_id FROM poll_to_specific WHERE group_id IN ($gids_sql_ready))
                    )";
        $query_params[] = $uid;
    }
    $query .= " ORDER BY start_date DESC";

    $result = Database::get()->queryArray($query, $query_params);

    $num_rows = count($result);
    if ($num_rows > 0) {
        ++$poll_check;
    }
    if (!$poll_check) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>" . $langPollNone . "</span></div></div><br>";
    } else {
        // Print active polls
        $tool_content .= "<div class='col-sm-12'>
                    <div class='table-responsive'>
              <table id='polls' class='table-default'>
              <thead>
                <tr class='list-header'>
                    <th style='min-width: 55%;'><div align='left'>&nbsp;$langTitle</div></th>
                    <th>$langDate</th>";
        if ($is_course_reviewer) {
            $tool_content .= "<th width='16'>$langAnswers</th>";
        } else {
            $tool_content .= "<th>$langParticipate</th>";
        }

        $tool_content .= "<th>".icon('fa-cogs')."</th>";
        $tool_content .= "</tr></thead><tbody>";
        foreach ($result as $thepoll) {
            if (!$is_editor && !resource_access($thepoll->active, $thepoll->public)) {
                continue;
            }
            $visibility = $thepoll->active;

            if (($visibility) or ($is_editor)) {
                if ($visibility) {
                    $visibility_css = "";
                    $visibility_func = "deactivate";
                } else {
                    $visibility_css = " class=\"not_visible\"";
                    $visibility_func = "activate";
                }
                $tool_content .= "<tr $visibility_css>";

                $temp_CurrentDate = new DateTime('NOW');
                $temp_StartDate = new DateTime($thepoll->start_date);
                $temp_EndDate = new DateTime($thepoll->end_date);
                $pid = $thepoll->pid;
                $total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $pid)->total;
                // check if user has participated
                $has_participated = Database::get()->querySingle("SELECT COUNT(*) as counter FROM poll_user_record
                        WHERE uid = ?d AND pid = ?d", $uid, $pid)->counter;

                // check if poll has ended OR not started yet
                $poll_ended = 0;
                $poll_not_started = 0;
                if($temp_CurrentDate < $temp_StartDate) {
                    $poll_not_started = 1;
                } else if ($temp_CurrentDate >= $temp_EndDate) {
                    $poll_ended = 1;
                }

                $tool_content .= "<td><div class='table_td'><div class='table_td_header clearfix' style='word-wrap: break-word; word-break: break-word; line-height: 16px;'>";
                if ($is_editor) {
                    $lock_icon = "";
                    if (!$thepoll->public) {
                        $lock_icon = "&nbsp;&nbsp;&nbsp;<span class='fa fa-lock'></span>";
                    }
                    if ($thepoll->assign_to_specific == 1) {
                        $assign_to_users_message = "<a class='assigned_to' data-pid='$thepoll->pid'><small class='help-block'>$langWorkAssignTo: $langWorkToUser</small></a>";
                    } else if ($thepoll->assign_to_specific == 2) {
                        $assign_to_users_message = "<a class='assigned_to' data-pid='$thepoll->pid'><small class='help-block'>$langWorkAssignTo: $langWorkToGroup</small></a>";
                    } else {
                        $assign_to_users_message = '';
                    }
                    $tool_content .= "<a href='pollparticipate.php?course=$course_code&amp;UseCase=1&pid=$pid'>".q($thepoll->name)."</a>$lock_icon $assign_to_users_message";
                } elseif ($is_course_reviewer) {
                    $tool_content .= "<a href='pollparticipate.php?course=$course_code&amp;UseCase=1&pid=$pid'>" . q($thepoll->name) . "</a>";
                } else {
                    if  ($poll_ended == 1 || $poll_not_started == 1) { // poll out of date
                        $tool_content .= q($thepoll->name);
                    } else {
                        if ($uid == 0 || $has_participated == 0 || $thepoll->multiple_submissions) {
                            $tool_content .= "<a href='pollparticipate.php?course=$course_code&amp;UseCase=1&pid=$pid'>" . q($thepoll->name) . "</a>";
                        } else {
                            $tool_content .= q($thepoll->name);
                        }
                    }
                }

                if ($thepoll->type == POLL_QUICK) {
                    $tool_content .= "<div class='text-muted' style='font-size: 12px;'>$langQuickSurvey</div>";
                }

                $tool_content .= "</div>
                                    <div class='mt-2'>" . standard_text_escape($thepoll->description) . "</div>
                                    </div></td>";
                if (isset($thepoll->start_date)) {
                    $sort_date = date("Y-m-d H:i", strtotime($thepoll->start_date));
                } else {
                    $sort_date = '';
                }
                $tool_content .= "
                        <td data-sort='$sort_date'>
                            <div style='padding-top: 7px;'><span class='text-success'>$langFrom</span>: &nbsp;" . format_locale_date(strtotime($thepoll->start_date)) . "</div>
                            <div style='padding-top: 7px;'><span class='text-danger'>$langTill</span>: &nbsp;" . format_locale_date(strtotime($thepoll->end_date)) . "</div>
                        </td>";

                if ($is_editor) {
                    $tool_content .= "
                    <td class='text-center'>$total_participants</td>
                    <td class='text-end option-btn-cell'>" .
                        action_button([
                            ['title' => $langEditChange,
                                'icon' => 'fa-edit',
                                'url' => "admin.php?course=$course_code&amp;pid=$pid"],
                            ['title' => $visibility ? $langDeactivate : $langActivate,
                                'icon' => $visibility ? 'fa-toggle-off' : 'fa-toggle-on',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;visibility=$visibility_func&amp;pid={$pid}"],
                            ['title' => $langResults,
                                'level' => 'primary',
                                'icon' => 'fa-line-chart',
                                'url' => "pollresults.php?course=$course_code&amp;pid=$pid",
                                'disabled' => ($total_participants == 0 || $thepoll->type == POLL_LIMESURVEY)],
                            ['title' => $langSee,
                                'icon' => 'fa fa-play-circle',
                                'url' => "pollparticipate.php?course=$course_code&amp;UseCase=1&amp;pid=$pid"],
                            ['title' => $langUserDuration,
                                'icon' => 'fa-users',
                                'url' => "participation.php?course=$course_code&amp;pid=$pid",
                                'disabled' => $total_participants == 0,
                                'show' => !$thepoll->anonymized],
                            ['title' => $thepoll->public ? $langResourceAccessLock : $langResourceAccessUnlock,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;" . ($thepoll->public ? "access=limited" : "access=public") . "&amp;pid=$pid",
                                'icon' => $thepoll->public ? 'fa-lock' : 'fa-unlock',
                                'show' => course_status($course_id) == COURSE_OPEN],
                            ['title' => $langCreateDuplicate,
                                'icon' => 'fa-copy',
                                'icon-class' => 'warnLink',
                                'icon-extra' => "data-pid='$pid'",
                                'show' => $thepoll->type == POLL_NORMAL],
                            ['title' => $langGenQrCode,
                                'icon' => 'fa-solid fa-qrcode',
                                'icon-class' => 'QRcodeOpen',
                                'url' => "index.php?course=$course_code&amp;pollId=$pid&amp;gen_qrcode=true"],
                            ['title' => $langPurgeExercises,
                                'icon' => 'fa-eraser',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_results=yes&amp;pid=$pid",
                                'confirm' => $langConfirmPurgeExercises,
                                'show' => $total_participants > 0],
                            ['title' => $langDelete,
                                'icon' => 'fa-xmark',
                                'class' => 'delete',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=yes&amp;pid=$pid",
                                'confirm' => $langConfirmDelete],
                        ]) . "
                    </td></tr>";
                } else if ($is_course_reviewer) {
                        $total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $pid)->total;
                        $tool_content .= "<td style='text-align: center;'>$total_participants";
                        $tool_content .= "</td>";
                    $tool_content .= "<td class='text-end option-btn-cell'>";
                        if ($total_participants > 0 && $thepoll->type != POLL_LIMESURVEY) {
                            $tool_content .= "<div style='padding-top:7px;padding-bottom:7px;'>
                                            <a href='pollresults.php?course=$course_code&pid=$pid'><span class='submitAdminBtn fa fa-line-chart'></span></a>
                                        </div>";
                        }
                        $tool_content .= "</td></tr>";
                } else {
                    $tool_content .= "<td>";
                    if ($poll_not_started == 1) {
                        $tool_content .= $langSurveyNotStarted;
                    } elseif ($has_participated > 0) {
                        $tool_content .= $uid ? $langHasParticipated : $langOpenParticipation;
                    } else if ($poll_ended == 1) {
                        $tool_content .= $langHasExpired;
                    } else {
                        $tool_content .= $uid ? $langHasNotParticipated : $langOpenParticipation;
                    }
                    $tool_content .= "</td>";

                    $line_chart_link = ($has_participated && $thepoll->show_results && ($thepoll->type == POLL_NORMAL || $thepoll->type == POLL_QUICK))? "" . icon('fa-line-chart', $langResults, "pollresults.php?course=$course_code&pid=$pid") : "" ;
                    $tool_content .= "<td class='text-end option-btn-cell'>
                                        <div style='padding-top:7px;padding-bottom:7px;'>$line_chart_link</div>
                                      </td></tr>";
                }
            }
        }

        $tool_content .= "</tbody></table></div></div>

            <div class='modal fade' tabindex='-1' role='dialog' id='cloneModal'>
              <div class='modal-dialog' role='document'>
                <div class='modal-content'>
                  <form action='$_SERVER[SCRIPT_NAME]' method='POST' id='clone_form'>
                    <div class='modal-header'>
                        <div class='modal-title'>$langCreateDuplicateIn</div>
                        <button type='button' class='close' data-bs-dismiss='modal' aria-label='$langCancel'></button>

                    </div>
                    <div class='modal-body'>
                        <div class='form-group'>
                          <select class='form-select' id='course_id' name='clone_to_course_id' aria-label='$langChoiceLesson'>
                            <option value='$course_id' selected>--- $langCurrentCourse ---</option>
                          </select>
                        </div>
                    </div>
                    <div class='modal-footer'>
                      <button type='button' class='btn cancelAdminBtn' data-bs-dismiss='modal'>$langCancel</button>
                      <button type='submit' class='btn submitAdminBtn ms-1'>$langCreateDuplicate</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <script>
              $(function () {
                $(document).on('click', '.warnLink', function(e) {
                    var pid = $(this).data('pid');
                    $('#clone_form').attr('action', '" . js_escape($_SERVER['SCRIPT_NAME']) . "?course=$course_code&pid=' + pid);
                    $('#cloneModal').modal('show').on('hide.bs.modal', function () {
                      if ($('#course_id').hasClass('select2-hidden-accessible')) {
                        $('#course_id').select2('destroy');
                      }
                    });
                    $('#course_id').select2({
                      width: '100%',
                      selectOnClose: true,
                      dropdownParent: $('#cloneModal'),
                      ajax: {
                        url: '" . js_escape($urlAppend . 'main/coursefeed.php') .  "',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                          return {
                            term: params.term,
                            page: params.page || 1
                          };
                        }
                      }
                    });
                    e.preventDefault();
                });
              });
            </script>";
        load_js('select2');
    }
}

// Delete all uploaded files from the current poll
function deleteFolderContents($folder) {
    if (!is_dir($folder)) {
        return false;
    }

    $items = scandir($folder);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $itemPath = $folder . DIRECTORY_SEPARATOR . $item;

        if (is_dir($itemPath)) {
            // Recursively delete subdirectory contents
            deleteFolderContents($itemPath);
            // Remove the empty directory
            rmdir($itemPath);
        } else {
            // Delete file
            unlink($itemPath);
        }
    }
    return true;
}

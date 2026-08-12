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

use Mpdf\MpdfException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$require_current_course = true;
if (!isset($_GET['from_session_view'])) {
    $require_course_reviewer = true;
}
require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'modules/questionnaire/functions.php';
require_once 'modules/session/functions.php';

if (!isset($_GET['pid'])) {
    redirect_to_home_page();
} else {
    $pid = intval($_GET['pid']);
}

if (isset($_GET['from_session_view']) && $is_consultant) {
    $is_course_reviewer = true;
}

if (!$is_course_reviewer) {
    forbidden();
}

// Check if uid is the coordinator or the consultant of the current session.
if (isset($_GET['from_session_view']) && isset($_GET['session'])) {
    $check = Database::get()->querySingle("SELECT user_id FROM course_user 
        WHERE user_id = ?d AND course_id = ?d AND status = ?d AND tutor = ?d", $uid, $course_id, USER_STUDENT, 1);
    if ($check && !is_session_consultant($_GET['session'],$course_id)) {
        Session::flash('message', $langForbidden);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/session/index.php?course=$course_code");
    }
}

$full = isset($_GET['full']) && $_GET['full'];

$p = Database::get()->querySingle("SELECT pid, name, anonymized FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
if (!$p) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langResults);
$sheet->getDefaultColumnDimension()->setWidth(30);
$out_filename = $course_code . '_poll_results_' . ($full ? 'full_' : '') . $p->name;
$course_title = course_id_to_title($course_id);

$qlist = [];
$submit_date = [];
$anonymized = $p->anonymized;
$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total
    FROM poll_user_record WHERE pid = ?d AND
         (email_verification = 1 OR email_verification IS NULL)", $p->pid)->total;

$poll_title = $p->name . " (". $langPollTotalAnswers . ": " . $total_participants . ")";
$data[] = [ $poll_title ];
$data[] = [];

$sqlSession = '';
$args_s = [];
if (isset($_GET['dumppoll_session'])) {
    $sqlSession = "AND b.session_id = ?d";
    $args_s = [$_GET['session']];
}
$sqlUser = '';
$sql_user_args = [];
if (isset($_GET['forQuestionUserDump']) && $_GET['forQuestionUserDump'] > 0) {
    $sqlUser = "AND b.uid = ?d";
    $sql_user_args = [$_GET['forQuestionUserDump']];
}

$sqlPqid = '';
$sqlPqidArg = [];
if (isset($_GET['forQuestionDump']) && $_GET['forQuestionDump'] > 0) {
    $sqlPqid = "AND pqid = ?d";
    $sqlPqidArg = [$_GET['forQuestionDump']];
}

if ($full) { // user questions results
    if ($anonymized) {
        $heading = array($langName);
    } else {
        $heading = array($langSurname, $langName, $langAm, $langUsername, $langEmail);
    }
    $heading[] = $langDate;
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d AND qtype != ?d AND has_sub_question != ?d $sqlPqid ORDER BY q_position", $pid, 0, -1, $sqlPqidArg);
    $users = Database::get()->queryArray("SELECT uid AS user_identifier
                                            FROM poll_user_record
                                                WHERE pid = ?d
                                                AND uid != 0
                                            UNION
                                                SELECT email AS user_identifier
                                                FROM poll_user_record
                                                WHERE pid = ?d
                                                AND email_verification = 1",
                                        $pid, $pid);
    $q_counter = 0;
    foreach ($questions as $q) {
        if ($q->qtype == QTYPE_TABLE) {
            $sub_questions = Database::get()->queryArray("SELECT answer_text,sub_question FROM poll_question_answer WHERE pqid = ?d", $q->pqid);
            if (count($sub_questions) > 0) {
                foreach ($sub_questions as $sq) {
                    $sub_questions_text_arr[] = $sq->answer_text;
                    $sub_questions_text_str = implode(' ,', $sub_questions_text_arr);
                    $questions_tmp = (object) array("pqid" => $q->pqid,
                                                  "pid" => $q->pid,
                                                  "question_text" => $q->question_text .' ' . '[' . $sub_questions_text_str . ']',
                                                  "qtype" => $q->qtype,
                                                  "q_position" => $q->q_position,
                                                  "q_scale" => $q->q_scale,
                                                  "description" => $q->description,
                                                  "answer_scale" => $q->answer_scale,
                                                  "q_row" => $q->q_row,
                                                  "q_column" => $q->q_column);
                }
                $questions[] = $questions_tmp;
            }
            unset($questions[$q_counter]);
        }
        $q_counter++;
    }

    $headingQ = [];
    foreach ($questions as $q) {
        if ($q->qtype == QTYPE_LABEL) {
            $q->question_text = strip_tags($q->question_text);
        }
        $heading[] = $q->question_text;
        $headingQ[] = $q->pqid;
        if ($q->qtype == QTYPE_LABEL) {
            foreach ($users as $user) {
                $qlist[$user->user_identifier][$q->pqid] = '-';
            }
        } elseif ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {
            $answers = Database::get()->queryArray("SELECT c.answer_text, a.aid, b.uid, b.email, a.poll_user_record_id, a.submit_date
                                FROM poll_user_record b, poll_answer_record a
                                LEFT JOIN poll_question_answer c
                                ON a.aid = c.pqaid
                                WHERE a.poll_user_record_id = b.id
                                AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                AND a.qid = ?d
                                $sqlUser
                                $sqlSession
                                ORDER BY a.submit_date ASC", $q->pqid, $sql_user_args, $args_s);

            foreach ($answers as $a) {
                // Get user answers from sub-question
                if ($q->qtype == QTYPE_SINGLE && $q->has_sub_question == 1) {
                    $sub_question_id = 0;
                    $check_sub_q = Database::get()->querySingle("SELECT sub_qid FROM poll_question_answer WHERE pqaid = ?d", $a->aid)->sub_qid;
                    if ($check_sub_q > 0) {
                        $sub_question_id = $check_sub_q;
                        $sub_question_type = Database::get()->querySingle("SELECT qtype FROM poll_question WHERE pqid = ?d", $sub_question_id)->qtype;
                        if ($sub_question_type == QTYPE_SINGLE) {
                            $res = Database::get()->querySingle("SELECT poll_question_answer.answer_text FROM poll_question_answer
                                                                 LEFT JOIN poll_answer_record ON poll_question_answer.pqaid=poll_answer_record.aid
                                                                 WHERE poll_answer_record.poll_user_record_id = ?d
                                                                 AND poll_answer_record.qid = ?d", $a->poll_user_record_id, $sub_question_id)->answer_text;
                            $a->sub_q_answers = $res;
                        } elseif ($sub_question_type == QTYPE_MULTIPLE) {
                            $res = Database::get()->queryArray("SELECT poll_question_answer.answer_text FROM poll_question_answer
                                                                LEFT JOIN poll_answer_record ON poll_question_answer.pqaid=poll_answer_record.aid
                                                                WHERE poll_answer_record.poll_user_record_id = ?d
                                                                AND poll_answer_record.qid = ?d", $a->poll_user_record_id, $sub_question_id);
                            if (count($res) > 0) {
                                $resArr = [];
                                foreach ($res as $r) {
                                    $resArr[] = $r->answer_text;
                                }
                                $a->sub_q_answers = implode(',', $resArr);
                            }
                        } elseif ($sub_question_type == QTYPE_FILL) {
                            $res = Database::get()->querySingle("SELECT answer_text FROM poll_answer_record
                                                                 WHERE poll_user_record_id = ?d
                                                                 AND qid = ?d", $a->poll_user_record_id, $sub_question_id)->answer_text;
                            $a->sub_q_answers = $res;
                        }
                    }
                }

                $sub_answer_text = '';
                if (isset($a->sub_q_answers)) {
                    $sub_answer_text = "[" . $a->sub_q_answers . "]";
                }

                $answer_text = ($a->aid < 0)? $langPollUnknown: $a->answer_text . $sub_answer_text;
                $user_identifier = $a->uid ?: $a->email;
                if (isset($qlist[$user_identifier][$a->poll_user_record_id][$q->pqid])) {
                    $qlist[$user_identifier][$a->poll_user_record_id][$q->pqid] .= ', ' . $answer_text;
                } else {
                    $qlist[$user_identifier][$a->poll_user_record_id][$q->pqid] = $answer_text;
                }
                $submit_date[$user_identifier][$a->poll_user_record_id] = date('d-m-Y H:i:s', strtotime($a->submit_date));
            }
        } elseif ($q->qtype == QTYPE_TABLE) {
            $answers = Database::get()->queryArray("SELECT a.answer_text, a.sub_qid_row, a.sub_qid,  b.uid, b.email, a.submit_date, a.poll_user_record_id FROM poll_answer_record a
                                                    JOIN poll_user_record b ON b.id=a.poll_user_record_id
                                                    WHERE a.qid = ?d
                                                    $sqlUser
                                                    $sqlSession
                                                    ORDER BY a.submit_date, a.sub_qid_row, a.sub_qid ASC", $q->pqid, $sql_user_args, $args_s);

            foreach ($answers as $a) {
                $answer_text = $a->answer_text;
                $user_identifier = $a->uid ?: $a->email;
                if (isset($qlist[$user_identifier][$a->poll_user_record_id][$q->pqid])) {
                    $qlist[$user_identifier][$a->poll_user_record_id][$q->pqid] .= ', ' . $answer_text;
                } else {
                    $qlist[$user_identifier][$a->poll_user_record_id][$q->pqid] = $answer_text;
                }
                $submit_date[$user_identifier][$a->poll_user_record_id] = date('d-m-Y H:i:s', strtotime($a->submit_date));
            }
        } elseif ($q->qtype == QTYPE_SCALE) {
            $answerScale = Database::get()->querySingle("SELECT answer_scale FROM poll_question WHERE pqid = ?d", $q->pqid)->answer_scale;
            $arrAnsScale = explode('|', $answerScale);
            $arrAnsScale = array_combine(
                range(1, count($arrAnsScale)),
                $arrAnsScale
            );
            $answers = Database::get()->queryArray("SELECT b.uid, a.answer_text, b.email, a.submit_date, a.poll_user_record_id FROM poll_answer_record a
                                                    JOIN poll_user_record b ON b.id=a.poll_user_record_id
                                                    WHERE a.qid = ?d
                                                    $sqlUser
                                                    $sqlSession
                                                    ORDER BY a.submit_date ASC", $q->pqid, $sql_user_args, $args_s);
            foreach ($answers as $a) {
                $user_identifier = $a->uid ?: $a->email;
                $qlist[$user_identifier][$a->poll_user_record_id][$q->pqid] = $arrAnsScale[$a->answer_text] ?? '';
                $submit_date[$user_identifier][$a->poll_user_record_id] = date('d-m-Y H:i:s', strtotime($a->submit_date));
            }
        } elseif ($q->qtype == QTYPE_FILL || $q->qtype == QTYPE_DATETIME || $q->qtype == QTYPE_SHORT || $q->qtype == QTYPE_FILE || $q->qtype == QTYPE_DATE) { // other question types
            $answers = Database::get()->queryArray("SELECT a.answer_text, b.uid, b.email, a.submit_date, a.poll_user_record_id
                                FROM poll_answer_record a, poll_user_record b
                                WHERE qid = ?d
                                AND a.poll_user_record_id = b.id
                                AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                $sqlUser
                                $sqlSession
                                ORDER BY a.submit_date DESC", $q->pqid, $sql_user_args, $args_s);

            foreach ($answers as $a) {
                $user_identifier = $a->uid ?: $a->email;
                $u_answer_text = $a->answer_text;
                if ($q->qtype == QTYPE_FILE) {
                    $arrFile = unserialize($a->answer_text, ['allowed_classes' => false]);
                    if (is_array($arrFile) && isset($arrFile['filename']) && is_string($arrFile['filename'])) {
                        $u_answer_text = basename(trim($arrFile['filename']));
                    } else {
                        $u_answer_text = '';
                    }
                }
                $qlist[$user_identifier][$a->poll_user_record_id][$q->pqid] = $u_answer_text;
                $submit_date[$user_identifier][$a->poll_user_record_id] = date('d-m-Y H:i:s', strtotime($a->submit_date));
            }
        }
    }

    $k = 0;
    $data[] = $heading;
    $session_participants = [];
    if (isset($_GET['dumppoll_session'])) {
        $participants = Database::get()->queryArray("SELECT participants FROM mod_session_users
                                                     WHERE session_id = ?d 
                                                     AND is_accepted = 1
                                                     AND participants IN (SELECT uid FROM poll_user_record WHERE pid = ?d AND session_id = ?d)", $_GET['session'], $pid, $_GET['session']);
        
        if (count($participants) > 0) {
            foreach ($participants as $p) {
                $session_participants[] = $p->participants;
            }
        }
    }

    foreach ($qlist as $user_identifier => $q_answers) {
        foreach ($q_answers as $record_id => $answers) {
            // Session view
            if (isset($_GET['dumppoll_session']) && !in_array($user_identifier, $session_participants)) {
                continue;
            }
            $answers_keys = array_keys($answers);
            $result = array_diff($headingQ, $answers_keys);
            if (count($result) > 0) {
                foreach ($result as $key => $a) {
                    $value = array($a => '');
                    $answers = array_merge(array_slice($answers, 0, $key), $value, array_slice($answers, $key));
                }
            }

            $k++;
            if ($anonymized) {
                $user_info = [ "$langStudent $k"];
            } else {
                $user_info = get_user_info($user_identifier);
            }
            $user_info[] = $submit_date[$user_identifier][$record_id] ?? '';
            $data[] = array_merge($user_info, $answers);
        }
    }

    $sheet->mergeCells("A1:F1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    for ($i = 1; $i <= 6; $i++) {
        $cells = [$i, 3];
        $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
    }

}

// create spreadsheet
$sheet->fromArray($data, NULL);
// file output
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
set_content_disposition('attachment', $out_filename . ".xlsx");
$writer->save("php://output");
exit;



/**
 * @brief Get user info
 * @param $uid
 * @return array
 */
function get_user_info($uid) {
    global $langAnonymous;

    $info = Database::get()->querySingle('SELECT username, am, email, givenname, surname
        FROM user WHERE id = ?d', $uid);
    if ($info) {
        return array($info->surname, $info->givenname, $info->am,
            $info->username, $info->email);
    } else {
        return array($langAnonymous, '-', '-', '-', $uid);
    }
}

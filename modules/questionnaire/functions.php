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

define('QTYPE_SINGLE', 1);
define('QTYPE_FILL', 2);
define('QTYPE_MULTIPLE', 3);
define('QTYPE_LABEL', 4);
define('QTYPE_SCALE', 5);
define('QTYPE_TABLE', 6);
define('QTYPE_DATETIME', 7);
define('QTYPE_SHORT', 8);

function validate_qtype($qtype)
{
    $qtype = intval($qtype);
    if (in_array($qtype, array(QTYPE_SINGLE, QTYPE_MULTIPLE, QTYPE_FILL, QTYPE_LABEL, QTYPE_SCALE, QTYPE_TABLE, QTYPE_DATETIME, QTYPE_SHORT))) {
        return $qtype;
    } else {
        return QTYPE_LABEL;
    }
}

/**
 * @brief chech if poll has questions
 * @param $pid
 * @return bool
 */
function hasPollQuestions($pid)
{
    $pq = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d", $pid);
    if (!$pq) {
        return false;
    } else {
        return true;
    }
}


/**
 * @brief create colles questions
 * @param type $pid
 */
function createcolles($pid) {

    global $qcolles1, $qcolles2,$qcolles3,$qcolles4,$qcolles5,$qcolles6,
                    $qcolles7,$qcolles8,$qcolles9,$qcolles10,$qcolles11,$qcolles12,
                    $qcolles13,$qcolles14,$qcolles15,$qcolles16,$qcolles17,$qcolles18,
                    $qcolles19,$qcolles20,$qcolles21,$qcolles22, $qcolles23,$qcolles24,
                    $lcolles1, $lcolles2, $lcolles3, $lcolles4, $lcolles5, $lcolles6;

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles1, 4, 1, NULL);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles1, 5, 2, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles2, 5, 3, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles3, 5, 4, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles4, 5, 5, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles2, 4, 6, NULL);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles5, 5, 7, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles6, 5, 8, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles7, 5, 9, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles8, 5, 10, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles3, 4, 11, NULL);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles9, 5, 12, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles10, 5, 13, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles11, 5, 14, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles12, 5, 15, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles4, 4, 16, NULL);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles13, 5, 17, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles14, 5, 18, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles15, 5, 19, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles16, 5, 20, 5);

	Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles5, 4, 21, NULL);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles17, 5, 22, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles18, 5, 23, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles19, 5, 24, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles20, 5, 25, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles6, 4, 26, NULL);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles21, 5, 27, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles22, 5, 28, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles23, 5, 29, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles24, 5, 30, 5);
}

/**
 * @brief create attls questions
 * @param type $pid
 */
function createattls($pid) {
    global $question1, $question2, $question3, $question4, $question5,
                        $question6, $question7, $question8, $question9, $question10,
                        $question11, $question12, $question13, $question14, $question15,
                        $question16, $question17, $question18, $question19, $question20;

    Database::get()->query("INSERT INTO poll_question
            (pid, question_text, qtype, q_position, q_scale)
            VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question1, 5, 1, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question2, 5, 2, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question3, 5, 3, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question4, 5, 4, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question5, 5, 5, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question6, 5, 6, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question7, 5, 7, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question8, 5, 8, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question9, 5, 9, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question10, 5, 10, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question11, 5, 11, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question12, 5, 12, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question13, 5, 13, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question14, 5, 14, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question15, 5, 15, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question16, 5, 16, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question17, 5, 17, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question18, 5, 18, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question19, 5, 19, 5);

    Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question20, 5, 20, 5);
}

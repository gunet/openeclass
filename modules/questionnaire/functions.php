<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

define('QTYPE_SINGLE', 1);
define('QTYPE_FILL', 2);
define('QTYPE_MULTIPLE', 3);
define('QTYPE_LABEL', 4);
define('QTYPE_SCALE', 5);

function validate_qtype($qtype)
{
    $qtype = intval($qtype);
    if (in_array($qtype, array(QTYPE_SINGLE, QTYPE_MULTIPLE, QTYPE_FILL, QTYPE_LABEL, QTYPE_SCALE))) {
        return $qtype;
    } else {
        return QTYPE_LABEL;
    }
}

/**
 * @brief create colles questions
 * @global type $qcolles1
 * @global type $qcolles2
 * @global type $qcolles3
 * @global type $qcolles4
 * @global type $qcolles5
 * @global type $qcolles6
 * @global type $qcolles7
 * @global type $qcolles8
 * @global type $qcolles9
 * @global type $qcolles10
 * @global type $qcolles11
 * @global type $qcolles12
 * @global type $qcolles13
 * @global type $qcolles14
 * @global type $qcolles15
 * @global type $qcolles16
 * @global type $qcolles17
 * @global type $qcolles18
 * @global type $qcolles19
 * @global type $qcolles20
 * @global type $qcolles21
 * @global type $qcolles22
 * @global type $qcolles23
 * @global type $qcolles24
 * @global type $lcolles1
 * @global type $lcolles2
 * @global type $lcolles3
 * @global type $lcolles4
 * @global type $lcolles5
 * @global type $lcolles6
 * @param type $pid
 */
function createcolles($pid) {

    global $qcolles1, $qcolles2,$qcolles3,$qcolles4,$qcolles5,$qcolles6,
                    $qcolles7,$qcolles8,$qcolles9,$qcolles10,$qcolles11,$qcolles12,
                    $qcolles13,$qcolles14,$qcolles15,$qcolles16,$qcolles17,$qcolles18,
                    $qcolles19,$qcolles20,$qcolles21,$qcolles22, $qcolles23,$qcolles24,
                    $lcolles1, $lcolles2, $lcolles3, $lcolles4, $lcolles5, $lcolles6;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles1, 4, 1, NULL)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles1, 5, 2, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles2, 5, 3, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles3, 5, 4, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles4, 5, 5, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles2, 4, 6, NULL)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles5, 5, 7, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles6, 5, 8, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles7, 5, 9, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles8, 5, 10, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles3, 4, 11, NULL)->lastInsertID;		

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles9, 5, 12, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles10, 5, 13, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles11, 5, 14, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles12, 5, 15, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles4, 4, 16, NULL)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles13, 5, 17, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles14, 5, 18, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles15, 5, 19, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles16, 5, 20, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles21, 5, 21, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles22, 5, 22, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles23, 5, 23, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles19, 5, 24, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles20, 5, 25, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $lcolles6, 4, 26, NULL)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles21, 5, 27, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles22, 5, 28, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles23, 5, 29, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $qcolles24, 5, 30, 5)->lastInsertID;					
}

/**
 * @brief create attls questions
 * @global type $question1
 * @global type $question2
 * @global type $question3
 * @global type $question4
 * @global type $question5
 * @global type $question6
 * @global type $question7
 * @global type $question8
 * @global type $question9
 * @global type $question10
 * @global type $question11
 * @global type $question12
 * @global type $question13
 * @global type $question14
 * @global type $question15
 * @global type $question16
 * @global type $question17
 * @global type $question18
 * @global type $question19
 * @global type $question20
 * @param type $pid
 */
function createattls($pid) {
    global $question1, $question2, $question3, $question4, $question5, 
                        $question6, $question7, $question8, $question9, $question10, 
                        $question11, $question12, $question13, $question14, $question15, 
                        $question16, $question17, $question18, $question19, $question20; 

    $pqid = Database::get()->query("INSERT INTO poll_question
            (pid, question_text, qtype, q_position, q_scale)
            VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question1, 5, 1, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question2, 5, 2, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question3, 5, 3, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question4, 5, 4, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question5, 5, 5, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question6, 5, 6, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question7, 5, 7, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question8, 5, 8, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question9, 5, 9, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question10, 5, 10, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question11, 5, 11, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question12, 5, 12, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question13, 5, 13, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question14, 5, 14, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question15, 5, 15, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question16, 5, 16, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question17, 5, 17, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question18, 5, 18, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question19, 5, 19, 5)->lastInsertID;

    $pqid = Database::get()->query("INSERT INTO poll_question
        (pid, question_text, qtype, q_position, q_scale)
        VALUES (?d, ?s, ?d, ?d, ?d)", $pid, $question20, 5, 20, 5)->lastInsertID;
}
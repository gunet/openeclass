<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/*
 * Personalised Announcements Component, eClass Personalised
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * @package eClass Personalised
 *
 * @abstract This component populates the announcements block on the user's personalised
 * interface. It is based on the diploma thesis of Evelthon Prodromou.
 *
 */

/*
 * Function getUserAnnouncements
 *
 * Populates an array with data regarding the user's personalised announcements
 *
 * @param array $param
 * @param string $type (data, html)
 * @return array
 */

function getUserAnnouncements($param = null, $type) {

    global $mysqlMainDb, $uid;

    $uid = $param['uid'];
    $lesson_id = $param['lesson_id'];
    $lesson_code = $param['lesson_code'];
    $max_repeat_val = $param['max_repeat_val'];
    $lesson_title = $param['lesson_titles'];
    $lesson_code = $param['lesson_code'];
    $lesson_professor = $param['lesson_professor'];

    $last_month = strftime('%Y %m %d', strtotime('now -1 month'));

    $announce_query_new = createQueries(array(
        'lesson_id' => $lesson_id,
        'lesson_code' => $lesson_code,
        'max_repeat_val' => $max_repeat_val,
        'date' => $last_month));

    $announceSubGroup = array();
    for ($i = 0; $i < $max_repeat_val; $i++) { //each iteration refers to one lesson
        $mysql_query_result = db_query($announce_query_new[$i], $mysqlMainDb);
        if ($num_rows = mysql_num_rows($mysql_query_result) > 0) {
            $announceLessonData = array();
            $announceData = array();
            array_push($announceLessonData, $lesson_title[$i]);
            array_push($announceLessonData, $lesson_code[$i]);
        }
        while ($myAnnouncements = mysql_fetch_row($mysql_query_result)) {
            if ($myAnnouncements) {
                $myAnnouncements[0] = strip_tags($myAnnouncements[0], '<b><i><u><ol><ul><li><br>');
                array_push($announceData, $myAnnouncements);
            }
        }
        if ($num_rows > 0) {
            array_push($announceLessonData, $announceData);
            array_push($announceSubGroup, $announceLessonData);
        }
    }

    if ($type == "html") {
        return announceHtmlInterface($announceSubGroup);
    } elseif ($type == "data") {
        return $announceSubGroup;
    }
}

/**
 * Function announceHtmlInterface
 *
 * Generates html content for the announcements block of eClass personalised.
 *
 * @param array $data
 * @return string HTML content for the documents block
 * @see function getUserAnnouncements()
 */
function announceHtmlInterface($data) {
    global $langNoAnnouncementsExist, $langMore, $dateFormatLong, $urlServer;
    
    $announceExist = false;
    $assign_content = '<table width="100%">';

    $max_repeat_val = count($data);
    for ($i = 0; $i < $max_repeat_val; $i++) {
        $iterator = count($data[$i][2]);
        if ($iterator > 0) {
            $announceExist = true;
            $assign_content .= "<tr><td class='sub_title1'>" . q(ellipsize($data[$i][0], 70)) . "</td></tr>";
            $url = $urlServer . "modules/announcements/index.php?course=" . $data[$i][1] . "&amp;an_id=";
            for ($j = 0; $j < $iterator; $j++) {
                $an_id = $data[$i][2][$j][3];
                $assign_content .= "<tr><td><ul class='custom_list'><li><a href='$url$an_id'>" .
                        "<b>" . q($data[$i][2][$j][0]) .
                        "</b></a><span class='smaller'><b><br />" .
                        claro_format_locale_date($dateFormatLong, strtotime($data[$i][2][$j][2])) .
                        "</b></span><div class='smaller'>" .
                        standard_text_escape(ellipsize_html($data[$i][2][$j][1], 250, "<strong>&nbsp;...<a href='$url$an_id'>[$langMore]</a></strong>")) .
                        "</div></li></ul></td></tr>";
            }
        }
    }

    $assign_content .= "</table>";

    if (!$announceExist) {
        $assign_content = "<p class='alert1'>$langNoAnnouncementsExist</p>";
    }
    return $assign_content;
}

/**
 * Function createQueries
 *
 * Creates needed queries used by getUserAnnouncements
 *
 * @param array $queryParam
 * @return array sql query
 * @see function getUserAnnouncements()
 */
function createQueries($queryParam) {

    global $maxValue;

    $lesson_id = $queryParam['lesson_id'];
    $lesson_code = $queryParam['lesson_code'];
    $max_repeat_val = $queryParam['max_repeat_val'];
    $date = $queryParam['date'];

    for ($i = 0; $i < $max_repeat_val; $i++) {
        if (is_array($date)) {
            $dateVar = $date[$i];
        } else {
            $dateVar = $date;
        }

        $announce_query[$i] = "SELECT title, content, `date`, announcement.id
                        FROM announcement, course_module
                        WHERE announcement.course_id = $lesson_id[$i]
				AND announcement.visible = 1
                                AND DATE_FORMAT(`date`,'%Y %m %d') >='$dateVar'
                                AND course_module.module_id = " . MODULE_ID_ANNOUNCE . "
                                AND course_module.visible = 1
                                AND course_module.course_id = $lesson_id[$i]
                        ORDER BY announcement.`date` DESC";
    }
    return $announce_query;
}

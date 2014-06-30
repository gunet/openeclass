<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

/**
 * @file agenda.php
 * @brief get user course agenda events
 */

/**
 * @brief displays last 5 course user agenda items
 * @global type $langNoEventsExist
 * @global type $langUnknown
 * @global type $langDuration
 * @global type $langMore
 * @global type $langHours
 * @global type $langHour
 * @global type $langExerciseStart
 * @global type $urlServer
 * @global type $dateFormatLong
 * @param type $param
 * @param type $type
 * @return string
 */
function getUserAgenda($param) {
    
    global $langNoEventsExist, $langUnknown, $langDuration, $langMore, $langHours, $langHour;
    global $langExerciseStart, $urlServer, $dateFormatLong;
            
    $course_id = $param['lesson_id'];
    $found = false;
    $course_ids = array();
    // exclude courses with disabled agenda modules    
    foreach ($course_id as $cid) {
        $q = Database::get()->queryArray("SELECT visible FROM course_module WHERE
                                                      module_id = " . MODULE_ID_AGENDA . " AND
                                                      course_id = ?d", $cid);
        foreach ($q as $row) {
            if ($row->visible == 1) {
                array_push($course_ids, $cid);
            }
        }
    }    
    $course_ids = implode(",", $course_ids);
    if (empty($course_ids)) {// in case there aren't any enabled agenda modules
        return "<p class='alert1'>$langNoEventsExist</p>";
    }
               
    $mysql_query_result = Database::get()->queryArray("SELECT agenda.title, agenda.content, agenda.start,
                                                        agenda.duration, course.code, course.title AS course_title
                                                        FROM agenda, course WHERE agenda.course_id IN ($course_ids)
                                                        AND agenda.course_id = course.id
                                                        AND agenda.visible = 1
                                                        HAVING (TO_DAYS(start) - TO_DAYS(NOW())) >= '0'
                                                    ORDER BY start ASC
                                                    LIMIT 5");
          
    $agenda_content = "<table width='100%'>";
    if ($mysql_query_result > 0) {        
        foreach ($mysql_query_result as $data) {
            $agenda_content .= "<tr><td class='sub_title1'>" . claro_format_locale_date($dateFormatLong, strtotime($data->start)) . "</td></tr>";                        
            $url = $urlServer . "modules/agenda/index.php?course=" . $data->code;
            if (strlen($data->duration) == 0) {
                $data->duration = "$langUnknown";
            } elseif ($data->duration == 1) {
                $data->duration = $data->duration . " $langHour";
            } else {
                $data->duration = $data->duration . " $langHours";
            }                                
            $agenda_content .= "<tr><td><ul class='custom_list'>
                            <li><a href='$url'><b>" . q($data->title) . "</b></a><br /><b>" . q(ellipsize($data->course_title, 80)) . "</b>
                            <div class='smaller'>" . $langExerciseStart . ": <b>" . date('H:i', strtotime($data->start)) . "</b> | $langDuration: <b>" . $data->duration . "</b>
                            <br />" . standard_text_escape(ellipsize_html($data->content, 150, "... <a href='$url'>[$langMore]</a>")) . "</div></li></ul></td></tr>";
            $found = true;
        }
        
    }
    $agenda_content .= "</table>";
    if ($found) {
        return $agenda_content;
    } else {
        return "<p class='alert1'>$langNoEventsExist</p>";
    }
            
}

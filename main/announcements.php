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

/**
 * @file announcements.php
 * @brief displays user course announcements 
 */


/**
 * @brief get last month course announcements 
 * @global type $urlServer
 * @global type $langMore
 * @global type $dateFormatLong
 * @global type $langNoAnnouncementsExist
 * @param type $param
 * @return string
 */
function getUserAnnouncements($param) {

    global $urlServer, $langMore, $dateFormatLong, $langNoAnnouncementsExist;
    
    $lesson_id = $param['lesson_id'];
    $last_month = strftime('%Y %m %d', strtotime('now -1 month'));

    $found = false;
    $ann_content = '<table width="100%">';
    foreach ($lesson_id as $lid) {
        $q = Database::get()->queryArray("SELECT title, content, `date`, announcement.id
                        FROM announcement, course_module
                        WHERE announcement.course_id = ?d
                                AND announcement.visible = 1
                                AND DATE_FORMAT(`date`,'%Y %m %d') >= ?s
                                AND course_module.module_id = " . MODULE_ID_ANNOUNCE . "
                                AND course_module.visible = 1
                                AND course_module.course_id = ?d
                        ORDER BY announcement.`date` DESC", $lid, $last_month, $lid);
        if ($q) { // if course has announcements
            $found = true;
            $ann_content .= "<tr><td class='sub_title1'>" . q(ellipsize(course_id_to_title($lid), 70)) . "</td></tr>";
            foreach ($q as $data) {            
                    $url = $urlServer . "modules/announcements/index.php?course=" . course_id_to_code($lid) . "&amp;an_id=";
                    $ann_content .= "<tr><td><ul class='custom_list'><li><a href='$url$data->id'>" .
                            "<b>" . q($data->title) ."</b></a>
                            <span class='smaller'><b><br />".
                            claro_format_locale_date($dateFormatLong, strtotime($data->date)) .
                            "</b></span><div class='smaller'>" .
                            standard_text_escape(ellipsize_html($data->content, 250, "<strong>&nbsp;...<a href='$url$data->id'>[$langMore]</a></strong>")) .
                            "</div></li></ul></td></tr>";                
            }
        }
    }
    $ann_content .= "</table>";
    if ($found) {
        return $ann_content;
    } else {
        return "<p class='alert1'>$langNoAnnouncementsExist</p>";
    }
}

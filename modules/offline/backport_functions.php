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

function trans($var_name, $var_array = []) {
    if (preg_match("/\['.+'\]/", $var_name)) {
        preg_match_all("([^\['\]]+)", $var_name, $matches);
        global ${$matches[0][0]};

        if ($var_array) {
            return vsprintf(${$matches[0][0]}[$matches[0][1]], $var_array);
        } else {
            return ${$matches[0][0]}[$matches[0][1]];
        }
    } else {
        global ${$var_name};

        if ($var_array) {
            return vsprintf(${$var_name}, $var_array);
        } else {
            return ${$var_name};
        }
    }
}

/**
 * @brief fetch course announcements
 * @global type $course_id
 * @global type $course_code
 * @global type $langNoAnnounce
 * @global type $urlAppend
 * @global type $dateFormatLong
 * @return string
 */
function course_announcements() {
    global $course_id, $course_code, $langNoAnnounce, $urlAppend, $dateFormatLong;

    if (visible_module(MODULE_ID_ANNOUNCE)) {
        $q = Database::get()->queryArray("SELECT title, `date`, id
                            FROM announcement
                            WHERE course_id = ?d AND
                                  visible = 1
                            ORDER BY `date` DESC LIMIT 5", $course_id);
        if ($q) { // if announcements exist
            $ann_content = '';
            foreach ($q as $ann) {
                $ann_url = $urlAppend . "modules/announcements/?course=$course_code&amp;an_id=" . $ann->id;
                $ann_date = claro_format_locale_date($dateFormatLong, strtotime($ann->date));
                $ann_content .= "<li class='list-item'>
                                    <span class='item-wholeline'><div class='text-title'><a href='$ann_url'>" . q(ellipsize($ann->title, 60)) ."</a></div>$ann_date</span>
                                </li>";
            }
            return $ann_content;
        }
    }
    return "<li class='list-item'><span class='item-wholeline'><div class='text-title not_visible'> - $langNoAnnounce - </div></span></li>";
}
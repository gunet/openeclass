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

require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/multimediahelper.class.php';

/*
 * Personalised Documents Component, eClass Personalised
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * @package eClass Personalised
 *
 * @abstract This component populates the documents block on the user's personalised
 * interface. It is based on the diploma thesis of Evelthon Prodromou.
 *
 */

/*
 * Function getUserDocuments
 *
 * Populates an array with data regarding the user's personalised documents
 *
 * @param array $param
 * @param  string $type (data, html)
 * @return array
 */

function getUserDocuments($param) {

    $last_month = strftime('%Y-%m-%d', strtotime('now -1 month'));
    // get user documents newer than one month
    $new_docs = docsHtmlInterface($last_month);

    return $new_docs;
}

/**
 * Function docsHtmlInterface
 *
 * Generates html content for the documents block of eClass personalised.
 *
 * @param $date
 * @return string HTML content for the documents block
 * @see function getUserDocuments()
 */
function docsHtmlInterface($date) {
    global $langNoDocsExist, $uid;
    global $mysqlMainDb, $group_sql;

    $q = db_query("SELECT document.path, document.course_id, document.filename,
                          document.title, document.date_modified,
                          course.title as course_title, course.code, document.format, document.visible,
                          document.id
                   FROM document, course_user, course
                   WHERE document.course_id = course_user.course_id AND
                             course_user.user_id = $uid AND
                             course.id = course_user.course_id AND
			     subsystem = " . MAIN . " AND
			     document.visible = 1 AND
                             date_modified >= '$date' AND
			     format <> '.dir'
                       ORDER BY date_modified DESC", $mysqlMainDb);

    if ($q and mysql_num_rows($q) > 0) {
        $group_courses = array();

        $content = '<table width="100%">';
        while ($row = mysql_fetch_array($q)) {
            if (isset($group_courses[$row['course_id']])) {
                array_push($group_courses[$row['course_id']], $row);
            } else
                $group_courses[$row['course_id']] = array($row);
        }
        foreach ($group_courses as $group_courses_member) {
            $first_check = 0;
            foreach ($group_courses_member as $course_file) {
                if ($first_check == 0) {
                    $content.= "<tr><td class='sub_title1'>" . q($course_file['course_title']) . "</td></tr>";
                    $first_check = 1;
                }
                $group_sql = "course_id = " . $course_file['course_id'] . " AND subsystem = " . MAIN;
                $url = file_url($course_file['path'], null, $course_file['code']);

                $dObj = MediaResourceFactory::initFromDocument($course_file);
                $dObj->setAccessURL($url);
                $dObj->setPlayURL(file_playurl($course_file['path'], null, $course_file['code']));
                $href = MultimediaHelper::chooseMediaAhref($dObj);

                $content .= "<tr><td class='smaller'><ul class='custom_list'><li>" .
                        $href . ' - (' .
                        nice_format(date('Y-m-d', strtotime($course_file['date_modified']))) .
                        ")</li></ul></td></tr>";
            }
        }
        unset($group_courses);
        $content .= "</table>";
        return $content;
    } else {
        return "\n<p class='alert1'>$langNoDocsExist</p>\n";
    }
}

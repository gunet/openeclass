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
 * @file documents.php
 * @brief get user documents
 */

require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/multimediahelper.class.php';

/**
 * @brief get user documents newer than one month
 * @global type $langNoDocsExist
 * @param type $param
 * @return string
 */
function getUserDocuments($param) {
    
    global $langNoDocsExist, $group_sql;
      
    $last_month = strftime('%Y-%m-%d', strtotime('now -1 month'));    
    $lesson_id = $param['lesson_id'];
    
    $found = false;
    $doc_content = '<table width="100%">';
    foreach ($lesson_id as $lid) {
        $q = Database::get()->queryArray("SELECT document.path, document.course_id, document.filename,
                                            document.title, document.date_modified,
                                            document.format, document.visible,
                                            document.id
                                     FROM document, course_module
                                     WHERE document.course_id = ?d AND                             
                                            subsystem = " . MAIN . " AND
                                            document.visible = 1 AND
                                            date_modified >= '$last_month' AND
                                            format <> '.dir' AND
                                            course_module.module_id = " . MODULE_ID_DOCS . " AND
                                            course_module.visible = 1 AND
                                            course_module.course_id = ?d
                                    ORDER BY date_modified DESC", $lid, $lid);

            
        if ($q) {
            $found = true;        
            $doc_content .= "<tr><td class='sub_title1'>" . q(ellipsize(course_id_to_title($lid), 70)) . "</td></tr>";
            foreach ($q as $course_file) {
                    $group_sql = "course_id = " . $lid . " AND subsystem = " . MAIN;                    
                    $url = file_url($course_file->path, $course_file->filename, course_id_to_code($lid));
                    $dObj = MediaResourceFactory::initFromDocument($course_file, true);
                    $dObj->setAccessURL($url);
                    $dObj->setPlayURL(file_playurl($course_file->path, $course_file->filename, course_id_to_code($lid)));
                    $href = MultimediaHelper::chooseMediaAhref($dObj);
                    $doc_content .= "<tr><td class='smaller'><ul class='custom_list'><li>" .
                            $href . ' - (' .nice_format(date('Y-m-d', strtotime($course_file->date_modified))) .")</li></ul></td></tr>";
            }
        }
    }
    $doc_content .= "</table>";
    if ($found) {
        return $doc_content;
    } else {            
        return "<p class='alert1'>$langNoDocsExist</p>";
    }        
}
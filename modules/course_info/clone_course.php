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

$require_current_course = true;
$require_course_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'archive_functions.php';
require_once 'restore_functions.php';

$toolName = $langCloneCourse;
$treeObj = new Hierarchy();
$_POST['restoreThis'] = null; // satisfy course_details_form()

if (isset($_POST['create_restored_course'])) {
    $tool_content = "posted";
    $currentCourseCode = $course_code;
    $success = doArchive($course_id, $course_code);
    
    if ($success !== 0) {
        $retArr = unpack_zip_inner($webDir . "/courses/archive/$course_code/$course_code-" . date('Ymd') . ".zip");
        $restoreEntry = null;
        
        foreach ($retArr as $entry) {
            if ($entry['course'] === $course_code) {
                $restoreEntry = $entry;
            }
        }

        if ($restoreEntry !== null) {
            $_POST['restoreThis'] = $restoreEntry['path']; // assign the real value to the variable, but no real essence here
            register_posted_variables(array('restoreThis' => true,
                'course_code' => true,
                'course_lang' => true,
                'course_title' => true,
                'course_desc' => true,
                'course_vis' => true,
                'course_prof' => true), 'all', 'autounquote');
            create_restored_course($tool_content, $restoreThis, $course_code, $course_lang, $course_title, $course_vis, $course_prof);
            $tool_content .= "</p><br /><center><p><a href='index.php?course=$currentCourseCode'>$langBack</a></p></center>";
            $course_code = $currentCourseCode; // revert course code to the correct value
        }
    }
} else {
    $tool_content = course_details_form($public_code, $currentCourseName, $titulaires, $currentCourseLanguage, null, $visible, '', null);
}

load_js('jstree');
list($js, $html) = $treeObj->buildCourseNodePicker();
$head_content .= $js;
draw($tool_content, 2, null, $head_content);
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
 * @brief create course
 * @param  type  $public_code
 * @param  type  $lang
 * @param  type  $title
 * @param string $description
 * @param  array $departments
 * @param  type  $vis
 * @param  type  $prof
 * @param  type  $password
 * @return boolean
 */
function create_course($public_code, $lang, $title, $description, $departments, $vis, $prof, $password = '') {

    $code = strtoupper(new_code($departments[0]));
    if (!create_course_dirs($code)) {
        return false;
    }
    if (!$public_code) {
        $public_code = $code;
    }
    $q = Database::get()->query("INSERT INTO course
                         SET code = ?s,
                             lang = ?s,
                             title = ?s,
                             keywords = '',
                             description = ?s,
                             visible = ?d,
                             prof_names = ?s,
                             public_code = ?s,
                             created = " . DBHelper::timeAfter() . ",
                             password = ?s,
                             glossary_expand = 0,
                             glossary_index = 1", $code, $lang, $title, $description, $vis, $prof, $public_code, $password);
    if ($q) {
        $course_id = $q->lastInsertID;
    } else {
        return false;
    }       

    require_once 'include/lib/course.class.php';
    $course = new Course();
    $course->refresh($course_id, $departments);

    return array($code, $course_id);
}

/**
 * @brief create main course index.php
 * @global type $webDir
 * @param type $code
 * @return boolean
 */
function course_index($code) {
    global $webDir;

    $fd = fopen($webDir . "/courses/$code/index.php", "w");
    chmod($webDir . "/courses/$code/index.php", 0644);
    if (!$fd) {
        return false;
    }
    fwrite($fd, "<?php\nsession_start();\n" .
            "\$_SESSION['dbname']='$code';\n" .
            "include '../../modules/course_home/course_home.php';\n");
    fclose($fd);
    return true;
}

/**
 * @brief create course directories 
 * @param type $code
 * @return boolean
 */
function create_course_dirs($code) {
    global $langDirectoryCreateError;

    $base = "courses/$code";
    umask(0);
    foreach (array($base, "$base/image", "$base/document", "$base/dropbox",
                   "$base/page", "$base/work", "$base/group", "$base/temp",
                   "$base/scormPackages", "video/$code") as $dir) {
       if (!make_dir($dir)) {
            Session::Messages(sprintf($langDirectoryCreateError, $dir));
            return false;
       } 
    }
    return true;
}

/**
 * @brief create modules entries
 * @param type $cid
 */
function create_modules($cid) {
    $vis_module_ids = array(MODULE_ID_AGENDA, MODULE_ID_LINKS, MODULE_ID_DOCS,
        MODULE_ID_ANNOUNCE, MODULE_ID_DESCRIPTION, MODULE_ID_MESSAGE,);

    $invis_module_ids = array(MODULE_ID_VIDEO, MODULE_ID_ASSIGN,
        MODULE_ID_FORUM, MODULE_ID_EXERCISE,
        MODULE_ID_GRADEBOOK, MODULE_ID_ATTENDANCE, MODULE_ID_GROUPS,
        MODULE_ID_GLOSSARY, MODULE_ID_EBOOK,
        MODULE_ID_CHAT, MODULE_ID_QUESTIONNAIRE,
        MODULE_ID_LP, MODULE_ID_WIKI, MODULE_ID_BLOG, MODULE_ID_TC);

    $vis_placeholders = array();
    $vis_args = array();
    foreach ($vis_module_ids as $mid) {
        $vis_placeholders[] = "(?d, 1, ?d)";
        $vis_args[] = intval($mid);
        $vis_args[] = intval($cid);
    }
    $invis_placeholders = array();
    $invis_args = array();
    foreach ($invis_module_ids as $mid) {
        $invis_placeholders[] = "(?d, 0, ?d)";
        $invis_args[] = intval($mid);
        $invis_args[] = intval($cid);
    }

    Database::get()->query("INSERT INTO course_module (module_id, visible, course_id) VALUES " . implode(', ', $vis_placeholders), $vis_args);
    Database::get()->query("INSERT INTO course_module (module_id, visible, course_id) VALUES " . implode(', ', $invis_placeholders), $invis_args);
}

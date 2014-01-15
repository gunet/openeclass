<?php

/* ========================================================================
 * Open eClass 2.8
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
 * @global type $mysqlMainDb
 * @param type $fake_code
 * @param type $lang
 * @param type $title
 * @param type $fac
 * @param type $vis
 * @param type $prof
 * @param type $password
 * @return boolean
 */
function create_course($public_code, $lang, $title, $fac, $vis, $prof, $password = '') {    

    $code = strtoupper(new_code($fac[0]));
    if (!create_course_dirs($code)) {
        return false;
    }
    if (!$public_code) {
        $public_code = $code;
    }
    if (!db_query("INSERT INTO course
                         SET code = '$code',
                             lang = '$lang',
                             title = " . quote($title) . ",
                             keywords = '',
                             visible = $vis,
                             prof_names = " . quote($prof) . ",
                             public_code = " . quote($public_code) . ",
                             created = NOW(),
                             password = " . quote($password) . ",
                             glossary_expand = 0,
                             glossary_index = 1")) {
        return false;
    }
    $course_id = mysql_insert_id();
    foreach ($fac as $facid) {
        if (!isset($set_fac)) {
            $set_fac = "INSERT INTO course_department (course, department) VALUES ";
        } else {
            $set_fac .= ' ,';
        }
        $set_fac .= "($course_id, $facid)";
    }
    db_query($set_fac);
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
 * @global type $webDir
 * @param type $code
 * @return boolean
 */
function create_course_dirs($code) {
    global $webDir;

    $base = $webDir . "/courses/$code";
    umask(0);
    if (!(mkpath("$base") and
            mkpath("$base/image") and
            mkpath("$base/document") and
            mkpath("$base/dropbox") and
            mkpath("$base/page") and
            mkpath("$base/work") and
            mkpath("$base/group") and
            mkpath("$base/temp") and
            mkpath("$base/scormPackages") and
            mkpath($webDir . "/video/$code"))) {
        return false;
    }
    return true;
}


/**
 * @brief create modules entries
 * @param type $cid
 */
function create_modules($cid) {
    $vis_module_ids = array(MODULE_ID_AGENDA, MODULE_ID_LINKS, MODULE_ID_DOCS,
                            MODULE_ID_ANNOUNCE, MODULE_ID_DESCRIPTION);
    
    $invis_module_ids = array(MODULE_ID_VIDEO, MODULE_ID_ASSIGN,
                            MODULE_ID_FORUM, MODULE_ID_EXERCISE, MODULE_ID_GROUPS,
                            MODULE_ID_DROPBOX, MODULE_ID_GLOSSARY, MODULE_ID_EBOOK,
                            MODULE_ID_CHAT, MODULE_ID_QUESTIONNAIRE,
                            MODULE_ID_LP, MODULE_ID_WIKI);

    $values = array();
    foreach ($vis_module_ids as $mid) {
        $vis_values[] = "($mid, 1, $cid)";
    }
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES " .
            implode(', ', $vis_values));
    
    foreach ($invis_module_ids as $mid) {
        $invis_values[] = "($mid, 0, $cid)";
    }    
    db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES " .
            implode(', ', $invis_values));
}

<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
    $dirs = [$base, "$base/image", "$base/document", "$base/dropbox",
        "$base/page", "$base/work", "$base/group", "$base/temp",
        "$base/scormPackages", "video/$code"];
    foreach ($dirs as $dir) {
        if (!make_dir($dir)) {
            Session::Messages(sprintf($langDirectoryCreateError, $dir));
            return false;
        }
        if ($dir != $base) {
            touch("$dir/index.html");
        }
    }
    return true;
}

/**
 * @brief create modules entries
 * @param type $cid
 */
function create_modules($cid) {
    global $modules;

    $module_ids[1] = default_modules();
    $module_ids[0] = array_diff(array_keys($modules), $module_ids[1]);

    $args = $placeholders = array();
    foreach (array(0, 1) as $vis) {
        foreach ($module_ids[$vis] as $mid) {
            $placeholders[] = '(?d, ?d, ?d)';
            $args[] = array($mid, $vis, $cid);
        }
    }
    Database::get()->query("INSERT IGNORE INTO course_module
        (module_id, visible, course_id) VALUES " .
        implode(', ', $placeholders), $args);
}

/**
 * @brief default modules enabled in new courses
 */
function default_modules() {
    // Modules enabled by default in new courses
    $default_module_defaults = array(MODULE_ID_AGENDA, MODULE_ID_LINKS,
        MODULE_ID_DOCS, MODULE_ID_ANNOUNCE,
        MODULE_ID_MESSAGE);

    if ($def = get_config('default_modules')) {
        return unserialize($def);
    } else {
        return $default_module_defaults;
    }
}

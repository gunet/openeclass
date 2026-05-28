<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

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
                             view_type = 'units',
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
            Session::flash('message',sprintf($langDirectoryCreateError, $dir));
            Session::flash('alert-class', 'alert-warning');
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

    $isCollabCourse = Database::get()->querySingle("SELECT is_collaborative FROM course WHERE id = ?d",$cid);
    if($isCollabCourse->is_collaborative){
        $module_ids[1] = default_modules_collaboration();
    }else{
        $module_ids[1] = default_modules();
    }

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

/**
 * @brief default modules enabled in new collaborations
 */
function default_modules_collaboration() {

    // Modules enabled by default in new collaborations
    $default_module_defaults_collab = array(MODULE_ID_SESSION, MODULE_ID_AGENDA, MODULE_ID_LINKS,
        MODULE_ID_DOCS, MODULE_ID_ANNOUNCE, MODULE_ID_MESSAGE);

    if ($def_collab = get_config('default_modules_collaboration')) {
        return unserialize($def_collab);
    } else {
        return $default_module_defaults_collab;
    }

}

/**
 * @brief Import CADMOS file (.cdm) into course
 * @param string $code course code
 * @param string $filename CADMOS file path
 * @return boolean
 */
function import_cadmos_file($course_id, $course_code, $path) {
    global $webDir;

    $target = $webDir . "/courses/$course_code/cadmos";
    mkdir($target, 0755);
    $zip = new ZipArchive;
    if ($zip->open($path)) {
        $zip->extractTo($target);
        $zip->close();
        $cadmos = json_decode(file_get_contents("$target/source.json"));

        $activities = [];
        $FlowSub = $cadmos->data->Flow->FlowSub;
        $FlowBase = $cadmos->data->Flow->FlowBase;
        uasort($FlowSub, function ($a, $b) { return $a->top - $b->top; });

        foreach ($FlowBase as $item) {
            foreach ($item->Activities as $activity) {
                $activity->ActorName = $item->ActorName;
                $activities[] = $activity;
            }
        }
        uasort($activities, function ($a, $b) { return $b->top - $a->top; });

        for ($i = count($FlowSub) - 1; $i >= 0; $i--) {
            $FlowSub[$i]->Activities = [];
            for ($j = 0; $j < count($activities); $j++) {
                if ($activities[$j] and $activities[$j]->top > $FlowSub[$i]->top) {
                    $FlowSub[$i]->Activities[] = $activities[$j];
                    $activities[$j] = null;
                }
            }
        }

        $widgets = [];
        foreach ($cadmos->data->Conceptual->ConceptualBase as $item) {
            $widgets[$item->id] = $item;
        }

        $order = 0;
        foreach ($FlowSub as $item) {
            $unit_id = Database::get()->query('INSERT INTO course_units
                SET title = ?s, visible = 1, public = 1, `order` = ?d, course_id = ?d, comments = ?s',
                q($item->text), $order++, $course_id,
                "<p><span class='badge bg-primary'>{$item->phaseTime} Minutes</span></p>")->lastInsertID;
            $act_order = 0;
            foreach ($item->Activities as $activity) {
                $widget = $widgets[$activity->id] ?? null;
                if ($widget) {
                    if (count($widget->ModalData->LearningGoal) == 1) {
                        $learningGoal = q($widget->ModalData->LearningGoal[0]);
                    } else {
                        $learningGoal = '<ul>' . implode('',
                            array_map(function ($item) { return '<li>' . q($item) . '</li>'; },
                            $widget->ModalData->LearningGoal)) . '</ul>';
                    }
                    $desc = "
                        <div>
                            <span class='badge bg-success'>{$widget->ModalData->Type}</span>
                            <span class='badge bg-info'>{$widget->ModalData->Actor}</span>
                            <span class='badge bg-warning'>{$widget->ModalData->TimeLimit} m.</span></div>
                            <h4>" . q($widget->ModalData->Title) . "</h4>
                            <p>" . q($widget->ModalData->Description) . "</p>
                            <hr>
                            <p><strong>Learning Goal:</strong> $learningGoal</p>
                        </div>";
                    Database::get()->query('INSERT INTO unit_resources
                        SET unit_id = ?d, title = ?s, comments = ?s, type = ?s,
                            res_id = 0, visible = 1, `date` = NOW(), `order` = ?d',
                        $unit_id, q($activity->title), $desc, 'text', $act_order++);
                }
            }
        }
    }
    return true;
}

function applyMapping($value, $mapping) {
    return isset($mapping[$value]) ? $mapping[$value] : $value;
}

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
/**
 * @brief Import CADMOS JSON data into course
 * @param int $course_id course ID
 * @param string $course_code course code
 * @param mixed $cadmos CADMOS object or JSON string
 * @return boolean
 */
function import_cadmos_data($course_id, $course_code, $cadmos) {
    global $webDir;

    $target = $webDir . "/courses/$course_code/cadmos";
    if (!is_dir($target)) {
        @mkdir($target, 0755, true);
    }

    if (is_string($cadmos)) {
        $cadmos_json_str = $cadmos;
        $cadmos = json_decode($cadmos);
    } else {
        $cadmos_json_str = json_encode($cadmos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    if (!file_exists("$target/source.json") && !empty($cadmos_json_str)) {
        @file_put_contents("$target/source.json", $cadmos_json_str);
    }

    if (!is_object($cadmos) || !isset($cadmos->data)) {
        return false;
    }

    // Save learning objectives from LessonInfo->Goals to course_description
    $goals = $cadmos->data->LessonInfo->Goals ?? [];
    if (!empty($goals) && is_array($goals)) {
        $cleaned_goals = [];
        foreach ($goals as $goal) {
            $g = trim(preg_replace('/^[•\-\*\s\t]+/u', '', $goal));
            if (!empty($g)) {
                $cleaned_goals[] = '<li>' . q($g) . '</li>';
            }
        }
        if (!empty($cleaned_goals)) {
            $goal_html = '<ul>' . implode('', $cleaned_goals) . '</ul>';
            $type_info = Database::get()->querySingle("SELECT title FROM course_description_type WHERE id = 2");
            $section_title = 'Objectives';
            if ($type_info) {
                $titles = unserialize($type_info->title);
                $course_lang = Database::get()->querySingle("SELECT lang FROM course WHERE id = ?d", $course_id)->lang ?? 'el';
                $section_title = $titles[$course_lang] ?? $titles['en'] ?? $titles['el'] ?? 'Objectives';
            }
            $exists = Database::get()->querySingle("SELECT id FROM course_description WHERE course_id = ?d AND type = 2", $course_id);
            if (!$exists) {
                Database::get()->query("INSERT INTO course_description SET
                    course_id = ?d,
                    title = ?s,
                    comments = ?s,
                    type = 2,
                    visible = 1,
                    `order` = 1,
                    update_dt = " . DBHelper::timeAfter(),
                    $course_id, $section_title, $goal_html);
            }
        }
    }

    $activities = [];
    $FlowSub = $cadmos->data->Flow->FlowSub ?? [];
    $FlowBase = $cadmos->data->Flow->FlowBase ?? [];
    if (!is_array($FlowSub)) {
        $FlowSub = (array)$FlowSub;
    }
    if (!is_array($FlowBase)) {
        $FlowBase = (array)$FlowBase;
    }
    uasort($FlowSub, function ($a, $b) { return ($a->top ?? 0) - ($b->top ?? 0); });

    foreach ($FlowBase as $item) {
        if (!empty($item->Activities) && is_array($item->Activities)) {
            foreach ($item->Activities as $activity) {
                $activity->ActorName = $item->ActorName ?? '';
                $activities[] = $activity;
            }
        }
    }
    uasort($activities, function ($a, $b) { return ($b->top ?? 0) - ($a->top ?? 0); });

    for ($i = count($FlowSub) - 1; $i >= 0; $i--) {
        $FlowSub[$i]->Activities = [];
        for ($j = 0; $j < count($activities); $j++) {
            if ($activities[$j] and ($activities[$j]->top ?? 0) > ($FlowSub[$i]->top ?? 0)) {
                $FlowSub[$i]->Activities[] = $activities[$j];
                $activities[$j] = null;
            }
        }
    }

    $widgets = [];
    $ConceptualBase = $cadmos->data->Conceptual->ConceptualBase ?? [];
    if (!is_array($ConceptualBase)) {
        $ConceptualBase = (array)$ConceptualBase;
    }
    foreach ($ConceptualBase as $item) {
        if (isset($item->id)) {
            $widgets[$item->id] = $item;
        }
    }

    $order = 0;
    foreach ($FlowSub as $item) {
        $phaseTime = intval($item->phaseTime ?? 0);
        $time_comment = $phaseTime > 0 ? "<p><span class='badge bg-primary'>{$phaseTime} Minutes</span></p>" : '';
        $unit_id = Database::get()->query('INSERT INTO course_units
            SET title = ?s, visible = 1, public = 1, `order` = ?d, course_id = ?d, comments = ?s',
            q($item->text ?? ''), $order++, $course_id, $time_comment)->lastInsertID;
        $act_order = 0;
        if (!empty($item->Activities)) {
            foreach ($item->Activities as $activity) {
                $widget = $widgets[$activity->id] ?? null;
                if ($widget && isset($widget->ModalData)) {
                    $m = $widget->ModalData;
                    if (isset($m->LearningGoal) && is_array($m->LearningGoal) && count($m->LearningGoal) > 0) {
                        if (count($m->LearningGoal) == 1) {
                            $learningGoal = q(trim(preg_replace('/^[•\-\*\s\t]+/u', '', $m->LearningGoal[0])));
                        } else {
                            $learningGoal = '<ul>' . implode('',
                                array_map(function ($g) { return '<li>' . q(trim(preg_replace('/^[•\-\*\s\t]+/u', '', $g))) . '</li>'; },
                                $m->LearningGoal)) . '</ul>';
                        }
                    } else {
                        $learningGoal = '';
                    }

                    $badgeType = !empty($m->Type) ? "<span class='badge bg-success me-1'>" . q($m->Type) . "</span>" : "";
                    $actor = !empty($m->Actor) ? $m->Actor : (!empty($activity->ActorName) ? $activity->ActorName : "");
                    $badgeActor = !empty($actor) ? "<span class='badge bg-info me-1'>" . q($actor) . "</span>" : "";
                    $badgeTime = !empty($m->TimeLimit) ? "<span class='badge bg-warning me-1'>" . q($m->TimeLimit) . " m.</span>" : "";

                    $resource_html = '';
                    if (!empty($widget->children) && is_array($widget->children)) {
                        $res_items = [];
                        foreach ($widget->children as $child) {
                            if (isset($child->ModalData)) {
                                $cm = $child->ModalData;
                                $cType = !empty($cm->Type) ? "<span class='badge bg-secondary me-1'>" . q($cm->Type) . "</span>" : "";
                                $cTitle = !empty($cm->Title) ? "<strong>" . q($cm->Title) . "</strong>" : "";
                                $cDesc = !empty($cm->Description) ? "<span class='text-muted'> - " . q($cm->Description) . "</span>" : "";
                                $cLoc = !empty($cm->ResourceLocation) ? " <a href='" . q($cm->ResourceLocation) . "' target='_blank' rel='noopener noreferrer'><i class='fa fa-external-link'></i></a>" : "";
                                $res_items[] = "<li>$cType $cTitle $cDesc $cLoc</li>";
                            }
                        }
                        if (!empty($res_items)) {
                            $resource_html = "<hr><p><strong>Resources:</strong></p><ul>" . implode('', $res_items) . "</ul>";
                        }
                    }

                    $goal_section = !empty($learningGoal) ? "<hr><p><strong>Learning Goal:</strong> $learningGoal</p>" : "";

                    $desc = "
                        <div>
                            <div>{$badgeType}{$badgeActor}{$badgeTime}</div>
                            <h4 class='mt-2'>" . q($m->Title ?? $activity->title ?? '') . "</h4>
                            <p>" . nl2br(q($m->Description ?? '')) . "</p>
                            {$goal_section}
                            {$resource_html}
                        </div>";
                    Database::get()->query('INSERT INTO unit_resources
                        SET unit_id = ?d, title = ?s, comments = ?s, type = ?s,
                            res_id = 0, visible = 1, `date` = NOW(), `order` = ?d',
                        $unit_id, q($activity->title ?? $m->Title ?? ''), $desc, 'text', $act_order++);
                }
            }
        }
    }
    return true;
}

function import_cadmos_file($course_id, $course_code, $path) {
    global $webDir;

    $target = $webDir . "/courses/$course_code/cadmos";
    if (!is_dir($target)) {
        mkdir($target, 0755, true);
    }
    $zip = new ZipArchive;
    if ($zip->open($path)) {
        $zip->extractTo($target);
        $zip->close();
        $cadmos = json_decode(file_get_contents("$target/source.json"));
        return import_cadmos_data($course_id, $course_code, $cadmos);
    }
    return false;
}

function applyMapping($value, $mapping) {
    return isset($mapping[$value]) ? $mapping[$value] : $value;
}

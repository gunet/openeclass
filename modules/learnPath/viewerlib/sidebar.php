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

require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/learnPath/viewerlib/render_helpers.php';

function sidebar_fragment(int $pathId, int $moduleId, int $attempt): string {
    global $course_code, $course_id, $uid, $is_editor, $langNoModule, $urlAppend;

    if ($uid) {
        $uidCheckString = 'AND UMP.`user_id` = ' . intval($uid);
    } else {
        $uidCheckString = 'AND UMP.`user_id` IS NULL ';
    }

    $sql = "SELECT M.*, LPM.*, A.`path`, UMP.`lesson_status`, UMP.`credit`
            FROM (`lp_module` AS M,
                 `lp_rel_learnPath_module` AS LPM)
            LEFT JOIN `lp_asset` AS A ON M.`startAsset_id` = A.`asset_id`
            LEFT JOIN `lp_user_module_progress` AS UMP
               ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
               AND UMP.`attempt` = ?d
               $uidCheckString
            WHERE M.`module_id` = LPM.`module_id`
              AND LPM.`learnPath_id` = ?d
              AND M.`course_id` = ?d
            ORDER BY LPM.`rank` ASC";
    $result = Database::get()->queryArray($sql, $attempt, $pathId, $course_id);

    if (count($result) == 0) {
        return "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoModule</span></div>";
    }

    $extendedList = [];
    foreach ($result as $list) {
        $modar = [];
        $modar['module_id'] = $list->module_id;
        $modar['course_id'] = $list->course_id;
        if (empty($list->name) and $list->contentType == 'LINK') {
            $modar['name'] = $list->path;
        } else {
            $modar['name'] = $list->name;
        }
        $modar['comment'] = $list->comment;
        $modar['accessibility'] = $list->accessibility;
        $modar['startAsset_id'] = $list->startAsset_id;
        $modar['contentType'] = $list->contentType;
        $modar['launch_data'] = $list->launch_data;
        $modar['learnPath_module_id'] = $list->learnPath_module_id;
        $modar['learnPath_id'] = $list->learnPath_id;
        $modar['lock'] = $list->lock;
        $modar['visible'] = $list->visible;
        $modar['specificComment'] = $list->specificComment;
        $modar['rank'] = $list->rank;
        $modar['parent'] = $list->parent;
        $modar['raw_to_pass'] = $list->raw_to_pass;
        $modar['path'] = $list->path;
        $modar['lesson_status'] = $list->lesson_status;
        $modar['credit'] = $list->credit;
        $extendedList[] = $modar;
    }

    $q = Database::get()->querySingle("SELECT name, comment FROM lp_learnPath
                                    WHERE learnpath_id = ?d AND course_id = ?d", $modar['learnPath_id'], $modar['course_id']);
    $lp_name = $q->name;

    $flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));
    $is_blocked = false;

    $unitParam = isset($_GET['unit']) ? "&amp;unit=$_GET[unit]" : '';

    // Pre-compute indent and is_blocked for each module
    $modules = [];
    foreach ($flatElementList as $module) {
        $indent = 0;
        for ($i = 0; $i < $module['children']; $i++) {
            $indent += 10;
        }
        $module['indent'] = $indent;
        $module['is_blocked'] = $is_blocked;
        $modules[] = $module;

        // Update blocking state for subsequent modules
        if ($module['contentType'] != CTLABEL_) {
            if (($module['lock'] == 'CLOSE') && ($module['credit'] != 'CREDIT' || ($module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED'))) {
                $is_blocked = true;
            }
        }
    }

    return render_lp_partial('modules.learnPath.partials.sidebar', [
        'lp_name'         => $lp_name,
        'modules'         => $modules,
        'is_editor'       => $is_editor,
        'course_code'     => $course_code,
        'unitParam'       => $unitParam,
        'currentModuleId' => $moduleId,
        'urlAppend'       => $urlAppend,
    ]);
}

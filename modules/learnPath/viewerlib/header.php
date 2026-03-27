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

require_once 'include/action.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/gradebook/functions.php';
require_once 'modules/learnPath/viewerlib/render_helpers.php';

function header_fragment(int $pathId, int $moduleId): string {
    global $course_code, $course_id, $uid, $is_editor, $urlAppend, $themeimg, $langClose;

    $action = new action();
    $action->record(MODULE_ID_LP);

    if (isset($_GET['unit'])) {
        $unitParam = "&amp;unit=" . intval($_GET['unit']);
        $returl = $urlAppend . "modules/units/index.php?course=$course_code&id=" . intval($_GET['unit']);
    } else {
        $unitParam = '';
        $returl = $urlAppend . "modules/learnPath/index.php?course=$course_code";
    }


    if ($uid) {
        $uidCheckString = "AND UMP.`user_id` = ?d";
        $uidParam = intval($uid);
    } else {
        $uidCheckString = "AND UMP.`user_id` IS NULL ";
        $uidParam = null;
    }

    $sql = "SELECT MIN(LPM.`learnPath_module_id`) AS learnPath_module_id,
                   MIN(LPM.`parent`) AS parent,
                   MIN(LPM.`lock`) AS `lock`,
                   MIN(M.`module_id`) AS module_id,
                   MIN(M.`contentType`) AS contentType,
                   MIN(M.`name`) AS name,
                   MIN(UMP.`lesson_status`) AS lesson_status,
                   MIN(UMP.`raw`) AS `raw`,
                   MIN(UMP.`scoreMax`) AS scoreMax,
                   MIN(UMP.`credit`) AS credit,
                   MIN(UMP.`progress_measure`) AS progress_measure,
                   MIN(A.`path`) AS path
           FROM (`lp_rel_learnPath_module` AS LPM,
                 `lp_module` AS M)
       LEFT JOIN `lp_user_module_progress` AS UMP
              ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
               $uidCheckString
       LEFT JOIN `lp_asset` AS A
              ON M.`startAsset_id` = A.`asset_id`
           WHERE LPM.`module_id` = M.`module_id`
             AND LPM.`learnPath_id` = ?d
             AND LPM.`visible` = 1
             AND LPM.`module_id` = M.`module_id`
             AND M.`course_id` = ?d
        GROUP BY LPM.`module_id`
        ORDER BY MIN(LPM.`rank`)";
    $queryParams = $uidParam !== null
        ? [$uidParam, $pathId, $course_id]
        : [$pathId, $course_id];
    $moduleList = Database::get()->queryArray($sql, ...$queryParams);

    $extendedList = [];
    foreach ($moduleList as $module) {
        $modar = [];
        $modar['name'] = $module->name;
        $modar['contentType'] = $module->contentType;
        $modar['learnPath_module_id'] = $module->learnPath_module_id;
        $modar['parent'] = $module->parent;
        $modar['path'] = $module->path;
        $modar['lock'] = $module->lock;
        $modar['module_id'] = $module->module_id;
        $modar['lesson_status'] = $module->lesson_status;
        $modar['raw'] = $module->raw;
        $modar['progress_measure'] = $module->progress_measure;
        $modar['scoreMax'] = $module->scoreMax;
        $modar['credit'] = $module->credit;
        $extendedList[] = $modar;
    }

    $flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

    $is_blocked = false;
    $moduleNb = 0;
    $previous = '';
    $previousModule = '';
    $nextModule = '';

    foreach ($flatElementList as $module) {
        if (!$is_blocked || $is_editor) {
            if ($module['contentType'] != CTLABEL_) {
                if ($moduleId == $module['module_id']) {
                    $previousModule = $previous;
                }
                if ($previous == $moduleId) {
                    $nextModule = $module['module_id'];
                }
            }

            if (($module['lock'] == 'CLOSE')
                    && ($module['credit'] != 'CREDIT'
                    || ($module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED'))) {
                $is_blocked = true;
            }
        }

        if ($module['contentType'] != CTLABEL_) {
            $moduleNb++;
        }

        if ($module['contentType'] != CTLABEL_) {
            $previous = $module['module_id'];
        }
    }

    $theme_id = $_SESSION['theme_options_id'] ?? get_config('theme_options_id');
    $theme_options = Database::get()->querySingle('SELECT * FROM theme_options WHERE id = ?d', $theme_id);
    $theme_options_styles = $theme_options ? unserialize($theme_options->styles, ['allowed_classes' => false]) : [];
    $urlThemeData = $urlAppend . 'courses/theme_data/' . $theme_id;
    $logoUrl = isset($theme_options_styles['imageUploadSmall']) ? $urlThemeData . '/' . $theme_options_styles['imageUploadSmall'] : $themeimg . '/eclass-new-logo.svg';

    $progressValue = null;
    $progressBarHtml = '';
    if ($uid) {
        // Gradebook: progress is a score-based calculation
        $lpProgress = get_learnPath_progress($pathId, $uid);
        update_gradebook_book($uid, $pathId, $lpProgress / 100, GRADEBOOK_ACTIVITY_LP);

        // Progress bar: use progress_measure from the current module's latest attempt
        $pm = Database::get()->querySingle(
            "SELECT progress_measure FROM lp_user_module_progress
              WHERE user_id = ?d AND learnPath_id = ?d
              ORDER BY attempt DESC, user_module_progress_id DESC LIMIT 1",
            $uid, $pathId
        );
        $progressDisplay = $pm && $pm->progress_measure !== null
            ? (int) round((float) $pm->progress_measure * 100)
            : 0;
        $progressValue = $progressDisplay;
        if ($progressDisplay > 0) {
            $progressBarHtml = disp_progress_bar($progressDisplay, 1);
        }
    }

    $prevModuleAttr = $previousModule !== '' ? (int) $previousModule : 0;
    $nextModuleAttr = $nextModule !== '' ? (int) $nextModule : 0;
    $progressAttr = $progressValue !== null ? $progressValue : 0;

    return render_lp_partial('modules.learnPath.partials.header', [
        'returl'         => $returl,
        'prevModuleAttr' => $prevModuleAttr,
        'nextModuleAttr' => $nextModuleAttr,
        'progressAttr'   => $progressAttr,
        'logoUrl'        => $logoUrl,
        'progressBarHtml'=> $progressBarHtml,
        'langClose'      => $langClose,
        'course_code'    => $course_code,
        'unitParam'      => $unitParam,
        'moduleNb'       => $moduleNb,
        'previousModule' => $previousModule,
        'nextModule'     => $nextModule,
        'path_id'        => $pathId,
        'urlAppend'      => $urlAppend,
    ]);
}

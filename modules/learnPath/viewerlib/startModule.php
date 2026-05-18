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

require_once 'modules/learnPath/viewerlib/response_helpers.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/gradebook/functions.php';
require_once 'modules/document/doc_init.php';

/**
 * Resolve the start module URL and metadata for the scorm player.
 *
 * @param int $pathId       Learning path ID
 * @param int $moduleId     Module ID
 * @param int $attempt      Attempt number
 * @return false|string     JSON string with module info or error envelope
 */
function startModule(int $pathId, int $moduleId, int $attempt): false|string {
    global $urlServer, $course_code, $course_id, $is_editor, $uid;

    $clarolineRepositoryWeb = $urlServer . 'courses/' . $course_code;
    doc_init();

    $lp_error = function (string $message, string $code): false|string {
        return resp_return_json([
            'ok' => false,
            'error' => $message,
            'code' => $code,
        ]);
    };

    // if credit was already set this query changes nothing else
    $directly_pass_lp_module = function (int $userid, int $lpmid) use ($course_id, $pathId, $attempt): void {
        $sql = "UPDATE `lp_user_module_progress`
                   SET `credit` = 1,
                       `raw` = 100,
                       `lesson_status` = 'COMPLETED',
                       `scoreMin` = 0,
                       `scoreMax` = 100,
                       `progress_measure` = 1,
                       `accessed` = " . DBHelper::timeAfter() . "
                 WHERE `user_id` = ?d
                   AND `learnPath_module_id` = ?d
                   AND `attempt` = ?d";
        Database::get()->query($sql, $userid, $lpmid, $attempt);
        triggerLPGame($course_id, $userid, $pathId, LearningPathEvent::UPDPROGRESS);
        triggerLPAnalytics($course_id, $userid, $pathId);
    };

    if (empty($pathId) || empty($moduleId)) {
        return $lp_error('Missing path or module id', 'LP_BAD_REQUEST');
    }

    check_LPM_validity($is_editor, $course_code, true, true);

    $learnPathModuleId = Database::get()->querySingle("SELECT `learnPath_module_id`
          FROM `lp_rel_learnPath_module`
         WHERE `learnPath_id` = ?d
           AND `module_id` = ?d", $pathId, $moduleId);
    if (!$learnPathModuleId || empty($learnPathModuleId->learnPath_module_id)) {
        return $lp_error('Module not found', 'LP_NOT_FOUND');
    }
    $learnPathModuleId = (int) $learnPathModuleId->learnPath_module_id;

    // SET USER_MODULE_PROGRESS IF NOT SET
    if ($uid) { // if not anonymous
        $num = Database::get()->querySingle("SELECT COUNT(LPM.`learnPath_module_id`) AS count
                FROM `lp_user_module_progress` AS UMP, `lp_rel_learnPath_module` AS LPM
               WHERE UMP.`user_id` = ?d
                 AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
                 AND LPM.`learnPath_id` = ?d
                 AND LPM.`module_id` = ?d
                 AND UMP.`attempt` = ?d", $uid, $pathId, $moduleId, $attempt)->count;

        // if never initialized: create an empty user_module_progress line
        if ($num == 0) {
            Database::get()->query("INSERT INTO `lp_user_module_progress`
                    ( `user_id` , `learnPath_id` , `learnPath_module_id`, `lesson_location`, `suspend_data`, `attempt`, `started`, `accessed`, `progress_measure` )
                    VALUES (?d , ?d, ?d, '', '', ?d, " . DBHelper::timeAfter() . ", " . DBHelper::timeAfter() . ", 0)", $uid, $pathId, $learnPathModuleId, $attempt);
            triggerLPGame($course_id, $uid, $pathId, LearningPathEvent::UPDPROGRESS);
            triggerLPAnalytics($course_id, $uid, $pathId);
        }
    } // else anonymous: record nothing

    // Get info about launched module
    $module = Database::get()->querySingle("SELECT `contentType`, `startAsset_id`, `name`
            FROM `lp_module`
           WHERE `module_id` = ?d
             AND `course_id` = ?d", $moduleId, $course_id);
    if (!$module) {
        return $lp_error('Module not found', 'LP_NOT_FOUND');
    }

    $asset = Database::get()->querySingle("SELECT `path` FROM `lp_asset` WHERE `asset_id` = ?d", $module->startAsset_id);
    $assetPath = $asset ? $asset->path : '';
    $moduleStartAssetPage = '';

    // Get path of file of the starting asset to launch
    switch ($module->contentType) {
        case CTDOCUMENT_ :
            if ($uid) { // Directly pass this module
                $directly_pass_lp_module((int) $uid, $learnPathModuleId);
            } // else anonymous: record nothing
            $file_url = file_url($assetPath);
            $play_url = file_playurl($assetPath);

            $furl = $file_url;
            if (MultimediaHelper::isSupportedMedia($module->name)) {
                $furl = $play_url;
            }

            $moduleStartAssetPage = $furl;
            $_SESSION['FILE_PHP__LP_MODE'] = true;
            break;

        case CTEXERCISE_ :
            // clean session vars of exercise
            unset($_SESSION['objExercise']);
            unset($_SESSION['objQuestion']);
            unset($_SESSION['objAnswer']);
            unset($_SESSION['questionList']);
            unset($_SESSION['exerciseResult']);
            unset($_SESSION['exeStartTime']);

            $moduleStartAssetPage = $urlServer . "modules/learnPath/navigation/showExercise.php?course=$course_code&amp;exerciseId=" . urlencode($assetPath);
            break;
        case CTSCORMASSET_ :
            if ($uid) { // Directly pass this module
                $directly_pass_lp_module((int) $uid, $learnPathModuleId);
            } // else anonymous: record nothing
            // Don't break, we need to also execute CTSCORM_
        case CTSCORM_ :
            // real scorm content method
            $startAssetPage = $assetPath;
            // Prevent path traversal in SCORM asset paths
            if (str_contains($startAssetPage, '..')) {
                return $lp_error('Invalid SCORM asset path', 'LP_BAD_REQUEST');
            }
            $modulePath = 'path_' . $pathId;
            $moduleStartAssetPage = $clarolineRepositoryWeb . '/scormPackages/' . $modulePath . $startAssetPage;
            break;
        case CTCLARODOC_ :
            $moduleStartAssetPage = '';
            break;
        case CTCOURSE_DESCRIPTION_ :
            if ($uid) { // Directly pass this module
                $directly_pass_lp_module((int) $uid, $learnPathModuleId);
            } // else anonymous: record nothing
            $moduleStartAssetPage = $urlServer . "modules/learnPath/navigation/showCourseDescription.php?course=$course_code";
            break;
        case CTLINK_ :
            if (!preg_match('#^https?://#i', $assetPath)) {
                return $lp_error('Invalid link URL', 'LP_BAD_REQUEST');
            }
            if ($uid) { // Directly pass this module
                $directly_pass_lp_module((int) $uid, $learnPathModuleId);
            } // else anonymous: record nothing
            $moduleStartAssetPage = $assetPath;
            break;
        case CTMEDIA_ :
            if ($uid) {
                $directly_pass_lp_module((int) $uid, $learnPathModuleId);
            }
            if (MultimediaHelper::isSupportedFile($assetPath)) {
                $moduleStartAssetPage = $urlServer . "modules/learnPath/navigation/showMedia.php?course=$course_code&id=" . urlencode($assetPath) . "&viewModule_id=" . $moduleId;
            } else {
                $moduleStartAssetPage = $urlServer
                        . "modules/video/index.php?course=$course_code&action=download&id=" . urlencode($assetPath);
            }
            break;
        case CTMEDIALINK_ :
            if ($uid) {
                $directly_pass_lp_module((int) $uid, $learnPathModuleId);
            }
            if (MultimediaHelper::isEmbeddableMedialink($assetPath)) {
                $moduleStartAssetPage = $urlServer . "modules/learnPath/navigation/showMediaLink.php?course=$course_code&id=" . urlencode($assetPath) . "&viewModule_id=" . $moduleId;
            } else {
                if (!preg_match('#^https?://#i', $assetPath)) {
                    return $lp_error('Invalid link URL', 'LP_BAD_REQUEST');
                }
                $moduleStartAssetPage = $assetPath;
            }
            break;
    } // end switch

    if ($moduleStartAssetPage === '') {
        return $lp_error('Module content not available', 'LP_NOT_FOUND');
    }

    return resp_return_json([
        'ok' => true,
        'moduleStartAssetPage' => $moduleStartAssetPage,
        'contentType' => $module->contentType,
        'learnPathModuleId' => $learnPathModuleId,
        'moduleId' => $moduleId,
        'pathId' => $pathId,
    ]);
}

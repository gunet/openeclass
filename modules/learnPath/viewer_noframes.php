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

$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'modules/learnPath/viewerlib/startModule.php';
require_once 'modules/learnPath/viewerlib/response_helpers.php';
require_once 'modules/learnPath/viewerlib/updateProgress.php';
require_once 'modules/learnPath/viewerlib/scormData.php';
require_once 'modules/learnPath/viewerlib/render_helpers.php';
require_once 'modules/learnPath/viewerlib/header.php';
require_once 'modules/learnPath/viewerlib/sidebar.php';

// override session vars if get args are present
if (!empty($_GET['path_id'])) {
    $_SESSION['path_id'] = intval($_GET['path_id']);
}
if (!empty($_GET['module_id'])) {
    $_SESSION['lp_module_id'] = intval($_GET['module_id']);
}
$_SESSION['lp_attempt_clean'] = false;
if (!empty($_GET['cleanattempt'])) {
    $_SESSION['lp_attempt_clean'] = true;
}

check_LPM_validity($is_editor, $course_code, true);

// central session reads
$pathId = (int) $_SESSION['path_id'];
$moduleId = (int) $_SESSION['lp_module_id'];
$attempt = (int) ($_SESSION['lp_attempt'] ?? 1);
$attemptClean = !empty($_SESSION['lp_attempt_clean']);

$unitParam = isset($_GET['unit']) ? "&amp;unit=" . intval($_GET['unit']) : '';
$unitParamPlain = isset($_GET['unit']) ? "&unit=" . intval($_GET['unit']) : '';

$fragment = $_GET['fragment'] ?? '';
if ($fragment === 'header') {
    resp_send_fragment(header_fragment($pathId, $moduleId), 200);
    exit;
}
if ($fragment === 'toc') {
    resp_send_fragment(sidebar_fragment($pathId, $moduleId, $attempt), 200);
    exit;
}

// prepareModule AJAX endpoint — validates module, sets session, returns JSON with module URL + SCORM data
if (isset($_GET['action']) && $_GET['action'] === 'prepareModule') {
    $requestedModuleId = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0;
    if (empty($requestedModuleId)) {
        resp_send_json(['ok' => false, 'error' => 'Missing module_id', 'code' => 'LP_BAD_REQUEST'], 400);
        exit;
    }

    // Use AJAX-safe validation (no redirects)
    $validity = check_LPM_validity_ajax($requestedModuleId, $is_editor);
    if (!$validity['ok']) {
        $statusCode = 403;
        if ($validity['code'] === 'LP_BAD_REQUEST') $statusCode = 400;
        elseif ($validity['code'] === 'LP_NOT_FOUND') $statusCode = 404;
        resp_send_json($validity, $statusCode);
        exit;
    }

    // Session is now updated by check_LPM_validity_ajax — re-read into locals
    $moduleId = (int) $_SESSION['lp_module_id'];
    $startModuleJson = startModule($pathId, $moduleId, $attempt);
    $startModuleResult = $startModuleJson ? json_decode($startModuleJson, true) : null;

    if (!$startModuleResult || empty($startModuleResult['ok'])) {
        resp_send_json($startModuleResult ?: ['ok' => false, 'error' => 'Module resolution failed', 'code' => 'LP_ERROR'], 500);
        exit;
    }

    // Build SCORM data if the target module is SCORM
    $targetContentType = $startModuleResult['contentType'] ?? '';
    $targetIsScorm = ($targetContentType == CTSCORM_ || $targetContentType == CTSCORMASSET_);

    $scormApiData = null;
    if ($targetIsScorm) {
        $scormApiData = buildScormApiData(
            $pathId, $moduleId, $attempt,
            $uid ? (int) $uid : null,
            (int) $course_id,
            $attemptClean
        );
    }

    $commitUrl = $urlAppend . "modules/learnPath/viewer_noframes.php?course=$course_code$unitParamPlain&action=updateProgress";

    resp_send_json([
        'ok' => true,
        'moduleStartAssetPage' => $startModuleResult['moduleStartAssetPage'],
        'contentType' => $targetContentType,
        'isScorm' => $targetIsScorm,
        'learnPathModuleId' => $startModuleResult['learnPathModuleId'],
        'moduleId' => $startModuleResult['moduleId'],
        'pathId' => $startModuleResult['pathId'],
        'scormData' => $scormApiData,
        'commitUrl' => $commitUrl,
    ], 200);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'updateProgress') {
    $payloadJson = updateProgress();
    if ($payloadJson === '') {
        resp_no_content(204);
        exit;
    }

    $payload = json_decode($payloadJson, true);
    if (!is_array($payload)) {
        resp_send_json(['ok' => false, 'error' => 'Internal error', 'code' => 'LP_ERROR'], 500);
        exit;
    }

    $status = 200;
    if (isset($payload['ok']) && $payload['ok'] === false) {
        $code = $payload['code'] ?? '';
        if ($code === 'LP_BAD_REQUEST') {
            $status = 400;
        } elseif ($code === 'LP_FORBIDDEN' || $code === 'LP_NOT_AUTH') {
            $status = 401;
        } elseif ($code === 'LP_NOT_FOUND') {
            $status = 404;
        } elseif ($code === 'LP_TOO_FAST') {
            $status = 429;
        } else {
            $status = 500;
        }
    }

    resp_send_json($payload, $status);
    exit;
}

// detect attempt — only runs on full page loads (AJAX endpoints exit above)
if ($uid) {
    $maxAttempt = intval(Database::get()->querySingle("SELECT MAX(attempt) AS maxatt
        FROM `lp_user_module_progress` AS UMP
       WHERE UMP.`user_id` = ?d
         AND UMP.`learnPath_id` = ?d", $uid, $pathId)->maxatt);
    $_SESSION['lp_attempt'] = $maxAttempt + 1;
} else {
    $_SESSION['lp_attempt'] = 1;
}
$attempt = $_SESSION['lp_attempt'];

$startModuleResponse = startModule($pathId, $moduleId, $attempt);
$startModuleData = $startModuleResponse ? json_decode($startModuleResponse, true) : null;
$moduleStartAssetPage = '';
$moduleContentType = null;

if ($startModuleData && !empty($startModuleData['ok'])) {
    $moduleStartAssetPage = $startModuleData['moduleStartAssetPage'] ?? '';
    $moduleContentType = $startModuleData['contentType'] ?? null;
}

$isScorm = ($moduleContentType == CTSCORM_ || $moduleContentType == CTSCORMASSET_);

// Capture SCORM API script output
$isAnonymous = !$uid;
$lp_update_url = $urlAppend . "modules/learnPath/viewer_noframes.php?course=$course_code$unitParamPlain&action=updateProgress";
// Provide locals for scormAPI_noframes.inc.php (no direct $_SESSION reads there)
$lp_pathId = $pathId;
$lp_moduleId = $moduleId;
$lp_attempt = $attempt;
$lp_attemptClean = $attemptClean;
ob_start();
require_once 'viewerlib/scormAPI_noframes.inc.php';
$scormApiScript = ob_get_clean();

// Build template data
$data = [
    'charset'              => $charset,
    'pageTitle'            => q($langPreview) . ' - ' . q($siteName),
    'urlAppend'            => $urlAppend,
    'theme_id'             => $theme_id,
    'cache_suffix'         => CACHE_SUFFIX,
    'jquery_version'       => JQUERY_VERSION,
    'course_code'          => $course_code,
    'unitParamPlain'       => $unitParamPlain,
    'moduleStartAssetPage' => $moduleStartAssetPage,
    'isScorm'              => $isScorm,
    'headerFragment'       => header_fragment($pathId, $moduleId),
    'sidebarFragment'      => sidebar_fragment($pathId, $moduleId, $attempt),
    'scormApiScript'       => $scormApiScript,
    'initialModuleId'      => $moduleId,
    'presenceUrl'          => $urlAppend . "modules/learnPath/record_action.php?course=$course_code",
];

view_lp_player('modules.learnPath.viewer_noframes', $data);

/**
 * Lightweight Blade renderer for the learning path player.
 * Bypasses the heavy view() function (breadcrumbs, sidebar courses, LTI, etc.)
 * since the player is a standalone fullscreen page.
 */
function view_lp_player(string $template, array $data): void {
    echo render_lp_partial($template, $data);
}

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
 * @file details.php
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @brief Displays student's progress for an LP
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
if ($is_course_reviewer) {
    $navigation[] = array("url" => "details.php?course=$course_code&path_id=" . $_REQUEST['path_id'], "name" => $langTracking);
} else {
    $navigation[] = array("url" => "learningPath.php?course=$course_code&path_id=" . $_REQUEST['path_id'], "name" => $langTracking);
}

if (empty($_REQUEST['uInfo']) || empty($_REQUEST['path_id'])) {
    header("Location: ./index.php?course=$course_code");
    exit();
}

if (!$is_course_reviewer && ($_REQUEST['uInfo'] != $_SESSION['uid'])) { // security check
    header("Location: ./index.php?course=$course_code");
    exit();
}

if (!isset($_GET['pdf']) && $is_course_reviewer) {
    $action_bar .= action_bar(array(
        array('title' => $langBack,
            'url' => "detailsUser.php?course=$course_code&amp;uInfo=$_REQUEST[uInfo]",
            'icon' => 'fa-reply',
            'level' => 'primary'),
        array('title' => $langDumpPDF,
            'url' => "detailsUserPath.php?course=$course_code&amp;uInfo=$_REQUEST[uInfo]&amp;path_id=$_REQUEST[path_id]&amp;pdf=true;",
            'icon' => 'fa-file-pdf',
            'level' => 'primary-label'),
        array('title' => $langDumpExcel,
            'url' => "detailsUserPath.php?course=$course_code&amp;uInfo=$_REQUEST[uInfo]&amp;path_id=$_REQUEST[path_id]&amp;xls=true;",
            'icon' => 'fa-file-excel',
            'level' => 'primary-label')

    ), false);

    $tool_content .= $action_bar;
}

$LPname = Database::get()->querySingle("SELECT `name`
        FROM `lp_learnPath`
        WHERE `learnPath_id` = ?d
        AND `course_id` = ?d", $_REQUEST['path_id'], $course_id)->name;

$sql = "SELECT LPM.`learnPath_module_id`, LPM.`parent`,
    LPM.`lock`, M.`module_id`,
    M.`contentType`, M.`name`,
    UMP.`lesson_status`, UMP.`raw`,
    UMP.`scoreMax`, UMP.`credit`,
    UMP.`session_time`, UMP.`total_time`, UMP.`attempt`,
    UMP.`started`, UMP.`accessed`, A.`path`
    FROM `lp_user_module_progress` AS UMP
    LEFT JOIN `lp_rel_learnPath_module` AS LPM
        ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
        AND UMP.`user_id` = ?d
    LEFT JOIN `lp_module` AS M
        ON LPM.`module_id` = M.`module_id`
    LEFT JOIN `lp_asset` AS A
        ON M.`startAsset_id` = A.`asset_id`
    WHERE LPM.`learnPath_id` = ?d
        AND LPM.`visible` = 1
        AND M.`course_id` = ?d
        ORDER BY UMP.`attempt`, LPM.`rank`";

$moduleList = Database::get()->queryArray($sql, $_REQUEST['uInfo'], $_REQUEST['path_id'], $course_id);

$maxAttempt = 1;
$elementList = [];
foreach ($moduleList as $module) {
    $moduleElement = [
        'identity' => $module->learnPath_module_id . '.' . $module->attempt, // because we need to group per attempt
        'learnPath_module_id' => $module->learnPath_module_id,
        'parent' => $module->parent,
        'lock' => $module->lock,
        'module_id' => $module->module_id,
        'contentType' => $module->contentType,
        'name' => $module->name,
        'lesson_status' => $module->lesson_status,
        'raw' => $module->raw,
        'scoreMax' => $module->scoreMax,
        'credit' => $module->credit,
        'session_time' => $module->session_time,
        'total_time' => $module->total_time,
        'attempt' => $module->attempt,
        'started' => $module->started,
        'accessed' => $module->accessed,
        'path' => $module->path];
    $elementList[] = $moduleElement;
    if ($module->attempt > $maxAttempt) {
        $maxAttempt = $module->attempt;
    }
}

$moduleNbT = 0;
$globalProg = $global_time = array();
for ($i = 1; $i <= $maxAttempt; $i++) {
    $globalProg[$i] = 0;
    $global_time[$i] = "0000:00:00";
}

$maxDeep = 1; // used to compute colspan of <td> cells - only single level depth of modules currently used

$toolName = $LPname;

$tool_content .= "<h3>" . q(uid_to_name($_REQUEST['uInfo'])) . "</h3>";

$tool_content .= "<div class='table-responsive'>
    <table class='table-default'>
    <thead>
        <tr class='list-header'>
            <th colspan=" . ($maxDeep + 1) . ">$langLearningObjects</th>
            <th>$langAttempt</th>
            <th>$langStart</th>
            <th>$langAttemptAccessed</th>            
            <th>$langTotalTimeSpent</th>
            <th>$langStatement</th>
            <th>$langProgress</th>
        </tr></thead>";

$data[] = [ uid_to_name($_REQUEST['uInfo']) . ": " . $LPname ];
$data[] = [];
$data[] = [ $langLearningObjects, $langAttempt, $langStart, $langAttemptAccessed, $langTotalTimeSpent, $langStatement, $langProgress ];


// ---------------- display list of lp modules ------------------------
$totalTime = "0000:00:00";
foreach ($elementList as $module) {
    if ($module['scoreMax'] > 0) {
        $progress = @round($module['raw'] / $module['scoreMax'] * 100);
    } else {
        $progress = 0;
    }

    if ($module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0) {
        if ($module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED') {
            $progress = 100;
        } else {
            $progress = 0;
        }
    }

    // display the current module name
    $spacingString = '';
    $colspan = $maxDeep + 1;

    $tool_content .= '<tr>' . $spacingString . '<td colspan="' . $colspan . '">';

    //-- if chapter head
    if ($module['contentType'] == CTLABEL_) {
        $tool_content .= '<strong>' . q($module['name']) . '</strong>';
    } else { //-- if user can access module
        if ($module['contentType'] == CTEXERCISE_) {
            $moduleImg = 'fa-square-pen';
        } elseif ($module['contentType'] == CTLINK_) {
            $moduleImg = 'fa-link';
        } elseif ($module['contentType'] == CTCOURSE_DESCRIPTION_) {
            $moduleImg = 'fa-info-circle';
        } elseif ($module['contentType'] == CTMEDIA_ or $module['contentType'] == CTMEDIALINK_) {
            $moduleImg = 'fa-film';
        } else {
            $moduleImg = choose_image(basename($module['path']));
        }
        $contentType_alt = selectAlt($module['contentType']);
        $tool_content .= icon($moduleImg, $contentType_alt) . "&nbsp;&nbsp;" . q($module['name']) . '</small>';
    }

    $tool_content .= "</td>";

    //-- attempt
    $tool_content .= "<td>" . q($module['attempt']) . "</td>";
    //-- started
    $tool_content .= "<td>" . format_locale_date(strtotime($module['started']), 'short') . "</td>";
    //-- accessed
    $tool_content .= "<td>" . format_locale_date(strtotime($module['accessed']), 'short') . "</td>";

    if ($module['contentType'] == CTSCORM_) {
        $session_time = $module['session_time']? preg_replace("/\.[0-9]{0,2}/", "", $module['session_time']): '';
        $total_time = $module['total_time']? preg_replace("/\.[0-9]{0,2}/", "", $module['total_time']): '';
        $global_time[$module['attempt']] = addScormTime($global_time[$module['attempt']], $total_time);
    } elseif ($module['contentType'] == CTLABEL_ || $module['contentType'] == CTEXERCISE_) {
        $session_time = $module['session_time'];
        $total_time = $module['total_time'];
        $global_time[$module['attempt']] = addScormTime($global_time[$module['attempt']], $total_time);
    } else {
        // if no progression has been recorded for this module,
        // leave
        if ($module['lesson_status'] == "") {
            $session_time = "&nbsp;";
            $total_time = "&nbsp;";
        } else { // columns are n/a
            $session_time = "-";
            $total_time = "-";
        }
    }
    $tool_content .= "<td>$total_time</td>";
    $tool_content .= "<td style='width:15%;'>" . disp_lesson_status($module['lesson_status']) . "</td>";
    //-- progression
    if ($module['contentType'] != CTLABEL_) {
        // display the progress value for current module
        $tool_content .= "<td>" . disp_progress_bar($progress, 1) . "</td>";
    } else { // label
        $tool_content .= "<td>&nbsp;</td>";
    }

    if ($progress > 0) {
        $globalProg[$module['attempt']] += $progress;
    }
    if ($module['contentType'] != CTLABEL_) {
        $moduleNbT++;
    }

    $tool_content .= "</tr>";
    $data[] = [ q($module['name']), q($module['attempt']), format_locale_date(strtotime($module['started']), 'short'),
                format_locale_date(strtotime($module['accessed']), 'short'), $session_time, $total_time,
                disp_lesson_status($module['lesson_status']), $progress . "%"
              ];
    $totalTime = addScormTime($totalTime, $global_time[$module['attempt']]);
}

if ($moduleNbT == 0) {
    $tool_content .= "<tr><td colspan='9'>$langNoModule</td></tr>";
} elseif ($moduleNbT > 0) {
    $bestAttempt = 1; // discover best attempt
    for ($i = 1; $i <= $maxAttempt; $i++) {
        if ($globalProg[$i] > $globalProg[$bestAttempt]) {
            $bestAttempt = $i;
        }
    }

    $nbrOfVisibleModules = calculate_number_of_visible_modules($_REQUEST['path_id']);
    $bestProgress = 0;
    if (is_numeric($nbrOfVisibleModules)) {
        $bestProgress = @round($globalProg[$bestAttempt] / $nbrOfVisibleModules);
    }
    $lpCombinedProgress = get_learnPath_combined_progress($_REQUEST['path_id'], $_REQUEST['uInfo']);

    // display global stats
    $tool_content .= "<tr>
                        <th class='ps-1' colspan='" . ($maxDeep + 4) . "'>" . ($totalTime != "0000:00:00" ? $langTotal : '&nbsp;') . "</></th>                                               
                        <th><small>" . ($totalTime != "0000:00:00" ? $totalTime : '&nbsp;') . "</small></th>
                        <th>&nbsp;</th>
                        <th class='ms-1 p-2'>" . disp_progress_bar($lpCombinedProgress, 1) . "</th>
                    </tr>";
    $data[] = [];
    if ($global_time[$bestAttempt] != "0000:00:00") {
        $data[] = [ $langTimeInLearnPath, $global_time[$bestAttempt] ];
    }
    $data[] = [ $langTotalPercentCompleteness, $lpCombinedProgress . "%" ];
}
$tool_content .= "</table></div>";

if ($is_course_reviewer) {
    if (isset($_GET['xls'])) {
        $course_title = course_code_to_title($_GET['course']);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($course_title);
        $sheet->getDefaultColumnDimension()->setWidth(30);
        $filename = $course_code . " learning_path_user_report.xlsx";

        $sheet->mergeCells("A1:J1");
        $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
        for ($i = 1; $i <= 10; $i++) {
            $cells = [$i, 3];
            $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
        }
        // create spreadsheet
        $sheet->fromArray($data, NULL);
        // file output
        $writer = new Xlsx($spreadsheet);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        set_content_disposition('attachment', $filename);
        $writer->save("php://output");
        exit;

    } else if (isset($_GET['pdf'])) {
        $pdf_content = "
            <!DOCTYPE html>
            <html lang='el'>
            <head>
              <meta charset='utf-8'>
              <title>" . q("$currentCourseName - $langTracking") . "</title>
              <style>
                * { font-family: 'opensans'; }
                body { font-family: 'opensans'; font-size: 10pt; }
                small, .small { font-size: 8pt; }
                h1, h2, h3, h4 { font-family: 'roboto'; margin: .8em 0 0; }
                h1 { font-size: 16pt; }
                h2 { font-size: 12pt; border-bottom: 1px solid black; }
                h3 { font-size: 10pt; color: #158; border-bottom: 1px solid #158; }
                th { text-align: left; border-bottom: 1px solid #999; }
                td { text-align: left; }
              </style>
            </head>
            <body>
            <h2>" . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
            <h2>" . q($LPname) . "</h2>";

        $pdf_content .= $tool_content;
        $pdf_content .= "</body></html>";

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf\Mpdf([
            'margin_top' => 53,     // approx 200px
            'margin_bottom' => 53,  // approx 200px
            'tempDir' => _MPDF_TEMP_PATH,
            'fontDir' => array_merge($fontDirs, [$webDir . '/template/modern/fonts']),
            'fontdata' => $fontData + [
                    'opensans' => [
                        'R' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-regular.ttf',
                        'B' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700.ttf',
                        'I' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-italic.ttf',
                        'BI' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700italic.ttf'
                    ],
                    'roboto' => [
                        'R' => 'roboto-v15-latin_greek_cyrillic_greek-ext-regular.ttf',
                        'I' => 'roboto-v15-latin_greek_cyrillic_greek-ext-italic.ttf',
                    ]
                ]
        ]);

        
        $mpdf->SetHTMLHeader(get_platform_logo());
        $footerHtml = '
        <div>
            <table width="100%" style="border: none;">
                <tr>
                    <td style="text-align: left;">{DATE j-n-Y}</td>
                    <td style="text-align: right;">{PAGENO} / {nb}</td>
                </tr>
            </table>
        </div>
        ' . get_platform_logo('','footer') . '';
        $mpdf->SetHTMLFooter($footerHtml);
        $mpdf->SetCreator(course_id_to_prof($course_id));
        $mpdf->SetAuthor(course_id_to_prof($course_id));
        $mpdf->WriteHTML($pdf_content);
        $mpdf->Output("$course_code learning_path_user_report.pdf", 'I'); // 'D' or 'I' for download / inline display
        exit;
    }
}

draw($tool_content, 2, null, $head_content);


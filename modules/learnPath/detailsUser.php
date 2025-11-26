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


/* ===========================================================================
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: tracking/userLog.php Revision: 1.37

  Claroline authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
  Hugues Peeters    <peeters@ipm.ucl.ac.be>
  Christophe Gesche <gesche@ipm.ucl.ac.be>
  Sebastien Piraux  <piraux_seb@hotmail.com>
  ==============================================================================
  @brief: This script presents the student's progress for all
  learning paths available in a course to the teacher.
  Only the Learning Path specific code was ported and
  modified from the original claroline file.
  ==============================================================================
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = TRUE;
$require_course_reviewer = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langLearningPaths);
$navigation[] = array('url' => "detailsAll.php?course=$course_code", 'name' => $langTrackAllPathExplanation);
// user info can not be empty, return to the list of details
if (empty($_REQUEST['uInfo'])) {
    header("Location: ./detailsAll.php?course=$course_code");
    exit();
}

// check if the user is in this course
$rescnt = Database::get()->querySingle("SELECT COUNT(*) AS count
            FROM `course_user` as `cu` , `user` as `u`
            WHERE `cu`.`user_id` = `u`.`id`
            AND `cu`.`course_id` = ?d
            AND `u`.`id` = ?d", $course_id, $_REQUEST['uInfo'])->count;

if ($rescnt == 0) {
    header("Location: ./detailsAll.php?course=$course_code");
    exit();
}

// get a list of learning paths of this course
// list available learning paths
$lpList = Database::get()->queryArray("SELECT name, learnPath_id
            FROM lp_learnPath
            WHERE course_id = ?d
            AND visible = 1
            ORDER BY `rank`", $course_id);


$pageName = $user_details = uid_to_name($_REQUEST['uInfo']);
$course_title = course_code_to_title($_GET['course']);

$data[] = [ $user_details . ' (' . $course_title . ')' ];
$data[] = [];
$data[] = [ $langLearnPath, $langAttempts, $langAttemptStarted, $langAttemptAccessed, $langTotalTimeSpent, $langLearningPathStatus, $langProgress, $langScore ];

if (!isset($_GET['pdf'])) {
    $action_bar = action_bar(array(
        array('title' => $langBack,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary'),
        array('title' => $langDumpPDF,
            'url' => "detailsUser.php?course=$course_code&amp;uInfo=$_GET[uInfo]&amp;pdf=true",
            'icon' => 'fa-file-pdf',
            'level' => 'primary-label'),
        array('title' => $langDumpExcel,
            'url' => "detailsUser.php?course=$course_code&amp;uInfo=$_GET[uInfo]&amp;xls=true",
            'icon' => 'fa-file-excel',
            'level' => 'primary-label')

    ));
}

$tool_content .= $action_bar;
// table header
$tool_content .= "<div class='table-responsive'><table class='table-default'>
                    <thead>
                    <tr class='list-header'>
                        <th>$langLearnPath</th>
                        <th>$langAttempts</th>
                        <th>$langAttemptStarted</th>
                        <th>$langAttemptAccessed</th>
                        <th>$langTotalTimeSpent</th>
                        <th>$langLearningPathStatus</th>
                        <th>$langProgress</th>
                        <th>$langScore</th>
                    </tr></thead>";

if (count($lpList) == 0) {
    $tool_content .= "<tr><td colspan='7'>$langNoLearningPath</td></tr>";
} else {
    $totalProgress = 0;
    $totalTimeSpent = "0000:00:00";
    // display each learning path with the corresponding progression of the user
    foreach ($lpList as $lpDetails) {
        list($lpProgress, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb, $lpScore) = get_learnPath_progress_details($lpDetails->learnPath_id, $_GET['uInfo']);
        $totalProgress += $lpProgress;
        if (!empty($lpTotalTime)) {
            $totalTimeSpent = addScormTime($totalTimeSpent, $lpTotalTime);
        }
        $lp_total_status = disp_lesson_status($lpTotalStatus);
        $lpDisplay = format_lp_progress_display($lpAttemptsNb, $lpTotalTime, $lpProgress, $lpScore);
        $data[] = [ $lpDetails->name, q($lpAttemptsNb), format_locale_date(strtotime($lpTotalStarted), 'short'),
                    format_locale_date(strtotime($lpTotalAccessed), 'short'), $lpDisplay['time'], $lp_total_status, $lpDisplay['progress_text'], $lpDisplay['score']
                  ];
        $tool_content .= "<tr>";
        if (!isset($_GET['pdf'])) {
            $tool_content .= "<td><a href='detailsUserPath.php?course=$course_code&amp;uInfo=" . $_GET['uInfo'] . "&amp;path_id=" . $lpDetails->learnPath_id . "'>" . q($lpDetails->name) . "</a></td>";
        } else {
            $tool_content .= "<td>" . q($lpDetails->name) . "</td>";
        }

        $tool_content .= "<td style='text-align: center;'>" . q($lpAttemptsNb) ."</td>
                          <td>" . format_locale_date(strtotime($lpTotalStarted), 'short') . "</td>
                          <td>" . format_locale_date(strtotime($lpTotalAccessed), 'short') . "</td>
                          <td>" . $lpDisplay['time'] . "</td>
                          <td>" . $lp_total_status . "</td>
                          <td>" . $lpDisplay['progress_html'] . "</td>
                          <td class='text-end'>" . $lpDisplay['score'] . "</td>
                     </tr>";
    }

    $total_progress = round($totalProgress/count($lpList));

    $tool_content .= "<tr>
                        <td colspan='4'><strong>$langTotal</strong></td>
                        <td>" . q($totalTimeSpent) . "</td>
                        <td></td>
                        <td>" . disp_progress_bar($total_progress, 1) . "</td>
                        <td></td>
                      </tr>";
    $data[] = [];
    $data[] = [ $langTotal, '', '', '', $totalTimeSpent, '', $total_progress . '%' ];
}
$tool_content .= "</table></div>";

if (isset($_GET['xls'])) {

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($langTracking);
    $sheet->getDefaultColumnDimension()->setWidth(30);
    $filename = $course_code . " - " . $user_details . "_user_stats.xlsx";

    $sheet->mergeCells("A1:F1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    for ($i = 1; $i <= 7; $i++) {
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
          <title>" . q("$currentCourseName - $langTrackUser") . "</title>
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
        <body><div style='height: 160px;'></div>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($langTrackUser) . "</h2>
        <h3> " . q(uid_to_name($_REQUEST['uInfo'])) . "</h3>";

    $pdf_content .= $tool_content;
    $pdf_content .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
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
    $mpdf->Output("$course_code learning_path_user_results.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
} else {
    draw($tool_content, 2, null, $head_content);
}

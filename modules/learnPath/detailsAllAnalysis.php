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
 * @file details.php
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @brief Displays course user progress in LPs
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = TRUE;
$require_course_reviewer = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
$pageName = $langTrackAllPathExplanation;

load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#lp_users_progress').DataTable ({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [],
                'columns': [ { orderable: false }, { orderable: false }, { orderable: false }, { orderable: false }, { orderable: false } ],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input').attr({
                'class' : 'form-control input-sm ms-0 mb-3',
                'placeholder' : '$langSearch...'
            });
            $('.dataTables_filter label').attr('aria-label', '$langSearch');  
        });
        </script>";

if (!isset($_GET['pdf'])) {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary'),
        array('title' => $langDumpPDF,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pdf=true",
            'icon' => 'fa-file-pdf',
            'level' => 'primary-label'),
        array('title' => $langDumpExcel,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;xls=true",
            'icon' => 'fa-file-excel',
            'level' => 'primary-label')
        ),
        false);
}

// check if there are learning paths available
$lcnt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM lp_learnPath WHERE course_id = ?d", $course_id)->count;
if ($lcnt == 0) {
    $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoLearningPath</span></div>";
    draw($tool_content, 2, null, $head_content);
    exit;
}

$tool_content .= "<div class='table-responsive'>
        <table id='lp_users_progress' class='table-default'>
        <thead>
            <tr class='list-header'>
                <th>$langStudent</th>
                <th>$langLearnPath</th>
                <th>$langAttempts</th>
                <th>$langTotalTimeSpent</th>
                <th>$langProgress</th>
            </tr>
        </thead>";

$course_title = course_code_to_title($_GET['course']);

$data[] = [ $course_title ];
$data[] = [];
$data[] = [ $langSurnameName, $langLearnPath, $langAttempts, $langTotalTimeSpent, $langProgress ];

$usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`, U.`email`
                FROM `user` AS U, `course_user` AS CU
                    WHERE U.`id`= CU.`user_id`
                    AND CU.`course_id` = ?d
                    ORDER BY U.`surname` ASC", $course_id);

$tool_content .= "<tbody>";
foreach ($usersList as $user) {
    // list available learning paths
    $learningPathList = Database::get()->queryArray("SELECT learnPath_id, name FROM lp_learnPath WHERE course_id = ?d", $course_id);
    $iterator = 1;
    $globalprog = 0;
    $globaltime = "00:00:00";
    $lpaths = array();
    $lp_content = "";

    foreach ($learningPathList as $learningPath) {
        // % progress
        list($prog, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb) = get_learnPath_progress_details($learningPath->learnPath_id, $user->id);
        if ($prog >= 0) {
            $globalprog += $prog;
        }
        if (!empty($lpTotalTime)) {
            $globaltime = addScormTime($globaltime, $lpTotalTime);
        }

        // ---- xls format ----
        $lpContent = array('', $learningPath->name, $lpAttemptsNb, $lpTotalTime, $prog);
        $lpaths[] = $lpContent;
        // --------------------
        $lp_content .= "<tr>";
        $lp_content .= "<td></td>";
        $lp_content .= "<td>" . q($learningPath->name) . "</td>";
        $lp_content .= "<td>" . q($lpAttemptsNb) . "</td>";
        $lp_content .= "<td>" . q($lpTotalTime) . "</td>";
        $lp_content .= "<td>" . disp_progress_bar($prog, 1) . "</td>";
        $lp_content .= "</tr>";
        $iterator++;
    }

    $total = round($globalprog / ($iterator - 1));
    if ($globaltime === "00:00:00") {
        $globaltime = "";
    }

    // ---- xls format ----
    $data[] = ["$user->surname $user->givenname ($user->email)", ' ', ' ', $globaltime, $total . '%'];
    foreach ($lpaths as $lpContent) {
        $data[] = [$lpContent[0], $lpContent[1], $lpContent[2], $lpContent[3], $lpContent[4] . '%'];
    }
    // --------------------

    $tool_content .= "<tr>";
    if (!isset($_GET['pdf'])) {
        $tool_content .= "<td><a href='detailsUser.php?course=$course_code&amp;uInfo=$user->id'>" . q(uid_to_name($user->id)) . " (" . q($user->email) .")</a></td>";
    } else {
        $tool_content .= "<td>" . q(uid_to_name($user->id)) . "</td>";
    }

    $tool_content .= "<td></td>
            <td></td>
            <td>" . q($globaltime) . "</td>
            <td>"
            . disp_progress_bar($total, 1) . "
            </td>";
    $tool_content .= "</tr>";
    $tool_content .= $lp_content;
}
$tool_content .= "</tbody></table></div>";

if (isset($_GET['xls'])) {

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($langTrackAllPathExplanation);
    $sheet->getDefaultColumnDimension()->setWidth(30);
    $filename = $course_code . "_learning_path_user_stats_analysis.xlsx";

    $sheet->mergeCells("A1:E1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    for ($i = 1; $i <= 5; $i++) {
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
          <title>" . q("$currentCourseName - $langTrackAllPathExplanation") . "</title>
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
        <body>" . get_platform_logo() .
        "<h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($langTrackAllPathExplanation) . "</h2>";

    $pdf_content .= $tool_content;
    $pdf_content .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'tempDir' => _MPDF_TEMP_PATH,
        'fontDir' => array_merge($fontDirs, [ $webDir . '/template/modern/fonts' ]),
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

    $mpdf->setFooter('{DATE j-n-Y} || {PAGENO} / {nb}');
    $mpdf->SetCreator(course_id_to_prof($course_id));
    $mpdf->SetAuthor(course_id_to_prof($course_id));
    $mpdf->WriteHTML($pdf_content);
    $mpdf->Output("$course_code learning_path_results.pdf", 'I'); // 'D' or 'I' for download / inline display
} else {
    draw($tool_content, 2, null, $head_content);
}

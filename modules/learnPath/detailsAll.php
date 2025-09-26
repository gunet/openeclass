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
 * @brief Displays course user progress in LPs
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = TRUE;
$require_course_reviewer = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
$toolName = $langTrackAllPathExplanation;

load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#lp_users_progress').DataTable ({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[2, 'desc']],
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
    $action_bar = action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary'),
        array('title' => $langDumpPDF,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&pdf=true",
            'icon' => 'fa-file-pdf',
            'level' => 'primary-label'),
        array('title' => $langDumpExcel,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&xls=true",
            'icon' => 'fa-file-excel',
            'level' => 'primary-label')
        ),
        false);
}

$tool_content .= $action_bar;

$course_title = course_code_to_title($_GET['course']);

$data[] = [ $course_title ];
$data[] = [];
$data[] = [ $langSurnameName, $langEmail, $langAm, $langGroup, $langTotalTimeSpent, $langProgress ];

// check if there are learning paths available
$lcnt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM lp_learnPath WHERE course_id = ?d", $course_id)->count;
if ($lcnt == 0) {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoLearningPath</span></div></div>";
    draw($tool_content, 2, null, $head_content);
    exit;
}

$tool_content .= "<div class='table-responsive'>
        <table id='lp_users_progress' class='table-default'>
        <thead>
            <tr class='list-header'>
                <th class='col-student'>$langStudent</th>
                <th>$langEmail</th>
                <th>$langAm</th>
                <th>$langGroup</th>
                <th>$langTotalTimeSpent</th>
                <th>$langProgress</th>
            </tr>
        </thead>";

$usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`, U.`email`,  U.`am`
                FROM `user` AS U, `course_user` AS CU
                    WHERE U.`id`= CU.`user_id`
                    AND CU.`course_id` = ?d
                    ORDER BY U.`surname` ASC, U.`givenname` ASC", $course_id);

$tool_content .= "<tbody>";
foreach ($usersList as $user) {
    // list available visible learning paths
    $learningPathList = Database::get()->queryArray("SELECT learnPath_id FROM lp_learnPath WHERE course_id = ?d AND visible = 1", $course_id);
    $iterator = 1;
    $globalprog = 0;
    $globaltime = "00:00:00";

    foreach ($learningPathList as $learningPath) {
        // % progress
        list($prog, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb) = get_learnPath_progress_details($learningPath->learnPath_id, $user->id);
        if ($prog >= 0) {
            $globalprog += $prog;
        }
        if (!empty($lpTotalTime)) {
            $globaltime = addScormTime($globaltime, $lpTotalTime);
        }
        $iterator++;
    }
    $total = round($globalprog / ($iterator - 1));
    // ---- xls format ----
    $ug = user_groups($course_id, $user->id, false);
    $data[] = [ "$user->surname $user->givenname", $user->email, $user->am, $ug, $globaltime, $total . '%' ];
    // --------------------

    if ($globaltime === "00:00:00") {
        $globaltime = "";
    }
    $tool_content .= "<tr>";
    if (!isset($_GET['pdf'])) {
        $tool_content .= "<td class='col-student'><a href='detailsUser.php?course=$course_code&amp;uInfo=$user->id'>" . q(uid_to_name($user->id)) . "</a></td>";
    } else {
        $tool_content .= "<td>" . q(uid_to_name($user->id)) . "</td>";
    }
    $tool_content .= "<td>" . q($user->email). "</td>
                      <td>" . q($user->am) . "</td>";
    if (!isset($_GET['pdf'])) {
        $tool_content .= "<td><div style='width:200px;'> " . user_groups($course_id, $user->id) . "</div></td>";
    } else {
        $tool_content .= "<td><div style='width:200px;'> " . user_groups($course_id, $user->id, false) . "</div></td>";
    }
    $tool_content .= "<td>" . q($globaltime) . "</td>
            <td>"
            . disp_progress_bar($total, 1) . "
            </td>";
    $tool_content .= "</tr>";
}
$tool_content .= "</tbody></table></div>";

if (isset($_GET['xls'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($langTrackAllPathExplanation);
    $sheet->getDefaultColumnDimension()->setWidth(30);
    $filename = $course_code . "_learning_path_user_stats.xlsx";

    $sheet->mergeCells("A1:F1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    for ($i = 1; $i <= 6; $i++) {
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
        <body><div style='height: 160px;'></div>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
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
    $mpdf->Output("$course_code learning_path_results.pdf", 'I'); // 'D' or 'I' for download / inline display
} else {
    draw($tool_content, 2, null, $head_content);
}

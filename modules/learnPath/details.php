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
 * @brief Displays course user progress in specific LP
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = TRUE;
$require_editor = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
$toolName = $langStatsOfLearnPath;

if (empty($_REQUEST['path_id'])) { // path id can not be empty
    header("Location: ./index.php?course=$course_code");
    exit();
} else {
    $path_id = intval($_REQUEST['path_id']);
}

load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#lpu_progress').DataTable ({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[0, 'asc']],
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

// get infos about the learningPath
$learnPathName = Database::get()->querySingle("SELECT `name` FROM `lp_learnPath` WHERE `learnPath_id` = ?d AND `course_id` = ?d", $path_id, $course_id);

if ($learnPathName) {
    $titleTab['subTitle'] = q($learnPathName->name);
    $pageName = $langLearnPath.": ".disp_tool_title($titleTab);

    if (isset($_GET['pdf'])) {
        $emailCol = "<th class='text-left'>$langEmail</th>";
    } else {
        $emailCol = '';
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "index.php",
                'icon' => 'fa-reply',
                'level' => 'primary'),
            array('title' => $langDumpPDF,
                'url' => "details.php?course=$course_code&path_id=$path_id&pdf=true",
                'icon' => 'fa-file-pdf',
                'level' => 'primary-label'),
            array('title' => $langDumpExcel,
                'url' => "details.php?course=$course_code&path_id=$path_id&xls=true",
                'icon' => 'fa-file-excel',
                'level' => 'primary-label')

        ));
    }

    $tool_content .= "<div class='table-responsive'>
                    <table id='lpu_progress' class='table-default table-lpu-progress'>
                    <thead>
                        <tr class='list-header'>
                            <th class='text-left'>$langStudent</th>
                            $emailCol
                            <th>$langAttempts</th>
                            <th>$langAttemptStarted</th>
                            <th>$langAttemptAccessed</th>
                            <th>$langTotalTimeSpent</th>
                            <th>$langLessonStatus</th>
                            <th>$langProgress</th>
                        </tr>
                    </thead>";


    $data[] = [ $currentCourseName ];
    $data[] = [ $learnPathName->name ];
    $data[] = [];
    $data[] = [ $langSurnameName, $langEmail, $langAm, $langGroup, $langAttempts, $langAttemptStarted, $langAttemptAccessed, $langTotalTimeSpent, $langLessonStatus, $langProgress ];

    $usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`, U.`email`, U.`am`
                        FROM `user` AS U,
                             `course_user` AS CU
                        WHERE U.`id` = CU.`user_id`
                        AND CU.`course_id` = ?d
                        ORDER BY U.`surname` ASC, 
                                 U.`givenname` ASC",
                    $course_id);

    $tool_content .= "<tbody>";
    foreach ($usersList as $user) {
        list($lpProgress, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb) = get_learnPath_progress_details($path_id, $user->id);
        $lpCombinedProgress = get_learnPath_combined_progress($path_id, $user->id);
        $tool_content .= "<tr>";
        if (!isset($_GET['pdf'])) {
            $tool_content .= "<td>
                                <a href='detailsUserPath.php?course=$course_code&amp;uInfo=$user->id&amp;path_id=$path_id'>" . q($user->surname) . " " . q($user->givenname) . "</a>
                              </td>";
        } else {
            $tool_content .= "<td>" . q($user->surname) . " " . q($user->givenname) . "</td>" .
                "<td class='text-left'>" . q($user->email) . "</td>";
        }

        $lp_total_status = disp_lesson_status($lpTotalStatus);
        $lp_total_started = format_locale_date(strtotime($lpTotalStarted), 'short');
        $lp_total_accessed = format_locale_date(strtotime($lpTotalAccessed), 'short');

        $tool_content .= "<td class='text-center'>" . q($lpAttemptsNb) ."</td>                            
                            <td>" . $lp_total_started . "</td>
                            <td>" . $lp_total_accessed . "</td>
                            <td>" . q($lpTotalTime) . "</td>
                            <td>" . $lp_total_status . "</td>
                            <td>" . disp_progress_bar($lpCombinedProgress, 1) . "</td>";

        $tool_content .= "</tr>";

        $ug = user_groups($course_id, $user->id, 'csv');
        $data[] = [ "$user->surname $user->givenname", $user->email, $user->am, $ug, $lpAttemptsNb, $lp_total_started, $lp_total_accessed, $lpTotalTime, $lp_total_status, $lpProgress . '%' ];
    }
    $tool_content .= "</tbody></table></div>";
}

if (isset($_GET['xls'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($langStatsOfLearnPath);
    $sheet->getDefaultColumnDimension()->setWidth(30);
    $filename = $course_code . " learning_path_results.xlsx";

    $sheet->mergeCells("A1:J1");
    $sheet->mergeCells("A2:J2");
    $sheet->getCell('A1')->getStyle()->getFont()->setBold(true);
    $sheet->getCell('A2')->getStyle()->getFont()->setItalic(true);
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
    $titleTab['subTitle'] = q($learnPathName->name);
    $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName - $langStatsOfLearnPath") . "</title>
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
        <h2> " . q($langStatsOfLearnPath) . "</h2>
        <h3>" . disp_tool_title($titleTab) . "</h3>";

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

    $mpdf->setFooter('{DATE j-n-Y} || {PAGENO} / {nb}');
    $mpdf->SetCreator(course_id_to_prof($course_id));
    $mpdf->SetAuthor(course_id_to_prof($course_id));
    $mpdf->WriteHTML($pdf_content);
    $mpdf->Output("$course_code learning_path_results.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
} else {
    draw($tool_content, 2, null, $head_content);
}

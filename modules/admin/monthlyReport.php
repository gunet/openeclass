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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';
require_once 'include/lib/hierarchy.class.php';

$toolName = $langAdmin;
$pageName = $langMonthlyReport;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array("url" => "../usage/index.php?t=a", "name" => $langUsage);

$tree = new Hierarchy();

list($roots, $rootSubtrees) = $tree->buildRootsWithSubTreesArray();

$fc = ($_GET['fc'] ?? $roots[0]->id);
$fc_name = $tree->getNodeName($fc);

if (count($tree->buildRootsArray()) > 1) { // multiple root tree
    $data['buildRoots'] = $tree->buildRootsSelectForm(intval($roots[0]->id));
} else {
    $data['buildRoots'] = '';
}

if (isset($_GET['d'])) {  // detailed statistics per faculty
    $monthly_data = get_monthly_archives($fc, true, $_GET['m']);
} else {
    $monthly_data = get_monthly_archives($fc);
}

if (isset($_GET['format'])) {
    if ($_GET['format'] == 'pdf') {
        export_monthly_data(get_monthly_archives($fc), 'pdf');
    } else if ($_GET['format'] == 'xls') {
        export_monthly_data($monthly_data, 'xls');
    }
}

$data['monthly_data'] = $monthly_data;
$data['fc'] = $fc;
$data['fc_name'] = $fc_name;
$data['action_bar'] = action_bar(array(
    array('title' => $langDumpPDF,
        'url' => "$_SERVER[SCRIPT_NAME]?fc=$fc&amp;format=pdf",
        'icon' => 'fa-file-pdf',
        'level' => 'primary-label',
        'show' => false),
    array('title' => $langDumpExcel,
        'url' => "$_SERVER[SCRIPT_NAME]?fc=$fc&amp;format=xls",
        'icon' => 'fa-file-excel',
        'level' => 'primary-label')
));

view('admin.other.stats.monthlyReport', $data);


function export_monthly_data($report_data, $format): void
{

    global $webDir, $uid, $fc_name, $langMonthlyReport, $langTeachers,
           $langFaculty, $langStudents, $langCourses, $langDoc,
           $langExercises, $langWorks, $langAnnouncements, $langMessages,
           $langForums, $langMonth;

    if ($format == 'pdf') {
        $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$langMonthlyReport") . "</title>
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
        <h2> " . get_config('site_name') . "</h2>";

        $pdf_content .= $report_data;
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
        $mpdf->SetCreator(uid_to_name($uid));
        $mpdf->SetAuthor(uid_to_name($uid));
        $mpdf->WriteHTML($pdf_content);
        $mpdf->Output("monthly_report.pdf", 'I'); // 'D' or 'I' for download / inline display
        exit;
    } else if ($format == 'xls') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($langMonthlyReport);
        $sheet->getDefaultColumnDimension()->setWidth(40);
        $filename = "monthly_report.xlsx";

        $data[] = [ "$langFaculty: $fc_name" ];
        $data[] = [ $langMonth, $langTeachers, $langStudents, $langCourses, $langDoc,$langExercises, $langWorks, $langAnnouncements, $langMessages, $langForums ];

        foreach ($report_data as $monthly_report_data) {
            $data[] = [
                date_format(date_create($monthly_report_data['month']), "n / Y"),
                $monthly_report_data['teachers'],
                $monthly_report_data['students'],
                $monthly_report_data['courses'],
                $monthly_report_data['documents'],
                $monthly_report_data['exercises'],
                $monthly_report_data['assignments'],
                $monthly_report_data['announcements'],
                $monthly_report_data['messages'],
                $monthly_report_data['forum_posts']
            ];
        }

        $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
        for ($i=1; $i<=10; $i++) {
            $cells = [$i, 2];
            $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
        }
        // create spreadsheet
        $sheet->fromArray($data);
        // file output
        $writer = new Xlsx($spreadsheet);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=$filename");
        $writer->save("php://output");
        exit;
    }
}

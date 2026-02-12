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
 * @file tcuserduration.php
 * @brief display various teleconference participation reports
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'tc';

require_once '../../include/baseTheme.php';

if (isset($_GET['per_user'])) {
    $toolName = $langUserDuration;
} else{
    $toolName = $langParticipate;
}

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langBBB);

if (isset($_GET['id'])) {
    $meetingid = get_tc_meeting_id($_GET['id']);
    if (!isset($_GET['pdf']) and !isset($_GET['xls'])) {
        $action_bar = action_bar(array(
            array('title' => $langDumpPDF,
                'url' => "tcuserduration.php?course=$course_code&amp;id=$_GET[id]&amp;pdf=true",
                'icon' => 'fa-file-pdf',
                'level' => 'primary-label',
                'show' => $is_course_reviewer),
            array('title' => $langDumpExcel,
                'url' => "tcuserduration.php?course=$course_code&amp;id=$_GET[id]&amp;xls=true",
                'icon' => 'fa-file-excel',
                'level' => 'primary-label',
                'show' => $is_course_reviewer)
        ));
        $tool_content .= $action_bar;
    }
} else {
    if (!isset($_GET['pdf']) and !isset($_GET['xls'])) {
        if (isset($_GET['per_user'])) {
            $url = "tcuserduration.php?course=$course_code&per_user=true&pdf=true";
            $xls_url = "tcuserduration.php?course=$course_code&per_user=true&xls=true";
            $back_url = "index.php?course=$course_code";
        } else if (isset($_GET['u'])) {
            $url = "tcuserduration.php?course=$course_code&u=$_GET[u]&pdf=true";
            $xls_url = "tcuserduration.php?course=$course_code&u=$_GET[u]&xls=true";
            $back_url = "tcuserduration.php?course=$course_code&per_user=true";
        } else {
            $url = "tcuserduration.php?course=$course_code&pdf=true";
            $xls_url = "tcuserduration.php?course=$course_code&xls=true";
            $back_url = "index.php?course=$course_code";
        }

        if (isset($_GET['u'])) {
            $back_link = "tcuserduration.php?course=$course_code&per_user=true";
            $navigation[] = array('url' => $back_link, 'name' => $langUserDuration);
        } else {
            $back_link = "index.php?course=$course_code";
        }
        $action_bar = action_bar(array(
            array('title' => $langBack,
                'url' => "$back_link",
                'icon' => 'fa-reply',
                'level' => 'primary'),
            array('title' => $langDumpPDF,
                'url' => "$url",
                'icon' => 'fa-file-pdf',
                'level' => 'primary-label',
                'show' => $is_course_reviewer),
            array('title' => $langDumpExcel,
                'url' => "$xls_url",
                'icon' => 'fa-file-excel',
                'level' => 'primary-label',
                'show' => $is_course_reviewer)
            ));
        $tool_content .= $action_bar;
    }
}

if (isset($_GET['per_user']) or isset($_GET['u'])) { // all-users participation in meetings
    if (!$is_course_reviewer and isset($_GET['per_user'])) { // security check
        redirect_to_home_page();
    }

    if (isset($_GET['u']) and $_GET['u']) { // participation for a specific user
        if (!$is_course_reviewer and $_GET['u'] != $_SESSION['uid']) { // security check
            redirect_to_home_page();
        }

        $u = $_GET['u'];
        $bbb_name = uid_to_name($u, 'username');


        if (isset($_GET['xls'])) {
            $data[] = [ get_config('site_name') . " - " . q($currentCourseName) ];
            $data[] = [ q($langStatsReport) ];
            $data[] = [];
        }

        $result = Database::get()->queryArray("SELECT title, start_date, meetingid, bbbuserid, totaltime, date FROM tc_attendance, tc_session 
                                            WHERE tc_attendance.meetingid = tc_session.meeting_id
                                            AND tc_session.course_id = ?d 
                                            AND tc_attendance.bbbuserid = ?s
                                            ORDER BY date DESC", $course_id, $bbb_name);

        $total_time = Database::get()->querySingle("SELECT SUM(totaltime) AS totaltime FROM tc_attendance, tc_session 
                                                    WHERE tc_attendance.meetingid = tc_session.meeting_id
                                                    AND tc_session.course_id = ?d 
                                                    AND tc_attendance.bbbuserid = ?s", $course_id, $bbb_name)->totaltime;
        if (count($result) > 0) {
            $ug_string = '';
            $ug = user_groups($course_id, $u, 'txt');
            if ($ug != '-') {
                $ug_string = " - $ug";
            }
            $tool_content .= "<div class='panel panel-default'>";
            $tool_content .= "<div class='panel-heading'><strong>" . q(uid_to_name($u, 'surname')) . " " . q(uid_to_name($u, 'givenname')) . " " . $ug_string . "</strong></div>";
            $tool_content .= "<div class='panel-body'><em>$langTotalDuration:</em> <strong>" . format_time_duration(0 + 60 * $total_time, 24, false) . "</strong></span></div><br>";
            $tool_content .= "</div>";
            if (isset($_GET['xls'])) {
                $data[] = [ uid_to_name($u, 'surname') . " " . uid_to_name($u, 'givenname') .  " " . $ug_string . " -- $langTotalDuration: " . format_time_duration(0 + 60 * $total_time, 24, false) ];
            }
            $data[] = [];

            $tool_content .= "<div class='table-responsive'><table class='table-default'>";
            $tool_content .= "<thead><tr class='list-header'>
                              <th>$langBBB</th>";
            if (isset($_GET['pdf'])) {
                $tool_content .= "<th>$langDate</th>";
            } else {
                $tool_content .= "<th>$langLogIn</th>";
            }
            $tool_content .= "<th>$langDuration</th>
                           </tr></thead>";
            if (isset($_GET['xls'])) {
                $data[] = [$langBBB, $langDate, $langDuration];
            }
            foreach ($result as $row) {
                $tool_content .= "<tr>";
                $tool_content .= "<td>$row->title</td>";
                if (isset($_GET['pdf'])) {
                    $tool_content .= "<td>" . format_locale_date(strtotime($row->date), 'full', false) . "</td>";
                } else {
                    $tool_content .= "<td>" . format_locale_date(strtotime($row->date), 'full') . "</td>";
                }
                $tool_content .= "<td>" . format_time_duration(0 + 60 * $row->totaltime, 24, false) . "</td>";
                $tool_content .= "</tr>";
                if (isset($_GET['xls'])) {
                    $data[] = [ $row->title, format_locale_date(strtotime($row->date), 'full', false), format_time_duration(0 + 60 * $row->totaltime, 24, false)];
                }
            }
            $tool_content .= "</table></div>";
        } else {
            $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langBBBNoParticipation</span></div>";
        }
    } else { // all users
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "
                    <thead><tr>
                      <th>$langSurnameName</th>
                      <th>$langAm</th>
                      <th>$langGroup</th>
                      <th>$langTotalDuration</th>
                      <th class='text-center' aria-label='$langSettingSelect'>" . icon('fa-gears') . "</th>
                    </tr></thead>";

        if (isset($_GET['xls'])) {
            $data[] = [ get_config('site_name') . " - " . q($currentCourseName) ];
            $data[] = [ q($langStatsReport) ];
            $data[] = [];
            $data[] = [ $langSurnameName , $langAm, $langGroup, $langTotalDuration ];
        }
        $users = Database::get()->queryArray("SELECT user.id, surname, givenname, am                           
                                FROM course_user, user
                                WHERE user.id = course_user.user_id
                                AND course_user.course_id = ?d 
                                ORDER BY surname, givenname",
                    $course_id);
        foreach ($users as $user) {
            $bbb_name = uid_to_name($user->id, 'username');
            $result = Database::get()->querySingle("SELECT SUM(totaltime) AS totaltime FROM tc_attendance, tc_session 
                                                    WHERE tc_attendance.meetingid = tc_session.meeting_id
                                                    AND tc_session.course_id = ?d 
                                                    AND tc_attendance.bbbuserid = ?s", $course_id, $bbb_name);
            if (isset($_GET['pdf']) or isset($_GET['xls'])) {
                $link_to_user = "$user->surname" . " " . "$user->givenname";
                $grp_name = user_groups($course_id, $user->id, false);
            } else {
                $link_to_user = "$_SERVER[SCRIPT_NAME]?course=$course_code&u=$user->id";
                $grp_name = user_groups($course_id, $user->id);
            }

            $tool_content .= "<tr>                            
                        <td>"  . q($user->surname) . " " .q($user->givenname) . "</td>
                        <td>$user->am</td>
                        <td>" . $grp_name . "</td>                            
                        <td>" . format_time_duration(0 + 60 * $result->totaltime, 24, false) . "</td>
                        <td>" . icon('fa-line-chart', $langDetails, $link_to_user) . "</td>
                    </tr>";

            if (isset($_GET['xls'])) {
                $data[] = [ "$user->surname  $user->givenname", $user->am, $grp_name, format_time_duration(0 + 60 * $result->totaltime, 24, false) ];
            }
        }
        $tool_content .= "</table></div>";
    }
} else {
    $result = [];
    if (isset($meetingid)) { // specific course meeting
        $result = Database::get()->queryArray("SELECT title, start_date, meetingid, bbbuserid, totaltime, date FROM tc_attendance, tc_session 
                                                    WHERE tc_attendance.meetingid = tc_session.meeting_id
                                                      AND tc_session.meeting_id = ?s
                                                    AND tc_session.course_id = ?d
                                                    ORDER BY date DESC",
            $meetingid, $course_id);
    } else { // all course meetings
        $result = Database::get()->queryArray("SELECT title, start_date, meetingid, bbbuserid, totaltime, date 
                                                    FROM tc_attendance, tc_session 
                                                    WHERE tc_attendance.meetingid = tc_session.meeting_id
                                                    AND tc_session.course_id = ?d
                                                    ORDER BY start_date DESC,
                                                    date DESC",
            $course_id);
    }

    // display results
    if (count($result) > 0) {

        if (isset($_GET['xls'])) {
            $data[] = [ get_config('site_name') . " - " . q($currentCourseName) ];
            $data[] = [ q($langStatsReport) ];
            $data[] = [];
            $data[] = [ $langSurnameName, $langBBB, $langDate, $langTotalDuration ];
        }
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $temp_date = null;
        $first = true;
        foreach ($result as $row) {
            if ($row->start_date != $temp_date) {
                if (!$first) {
                    $tool_content .= "<tr><td colspan='4'><hr></td></tr>"; // blank line
                }
                $tool_content .= "<tr><td colspan='4' class='list-header text-left'><strong>$row->title</strong></td></tr>";
                $tool_content .= "<thead><tr class='list-header'>
                                  <th class='ps-3'>$langSurnameName</th>
                                  <th>$langBBB</th>";
               if (isset($_GET['pdf'])) {
                   $tool_content .= "<th>$langDate</th>";
               } else {
                   $tool_content .= "<th>$langLogIn</th>";
               }
               $tool_content .= "<th>$langTotalDuration</th>
                               </tr></thead>";
                $temp_date = $row->start_date;
                $first = false;
            }
            $user_full_name = Database::get()->querySingle("SELECT fullName FROM tc_log
                            WHERE tc_log.bbbuserid = ?s ORDER BY id DESC LIMIT 1", $row->bbbuserid)->fullName;
            $tool_content .= "<tr><td>$user_full_name</td>                            
                            <td>$row->title</td>";
            if (isset($_GET['pdf'])) {
                $tool_content .= "<td>" . format_locale_date(strtotime($row->date), 'full', false) . "</td>";
            } else {
                $tool_content .= "<td>" . format_locale_date(strtotime($row->date), 'full') . "</td>";
            }
            $tool_content .= "<td>" . format_time_duration(0 + 60 * $row->totaltime, 24, false) . "</td>
                            </tr>";
            if (isset($_GET['xls'])) {
                $data[] = [ $user_full_name, $row->title, format_locale_date(strtotime($row->date), 'full', false), format_time_duration(0 + 60 * $row->totaltime, 24, false) ];
            }
        }
        $tool_content .= "</table></div>";
    } else {
        $tool_content .= "<div class='col-sm-12 alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langBBBNoParticipation</span></div>";
    }
}


// pdf output
if (isset($_GET['pdf']) and $is_course_reviewer) {
    $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName - $langStatsReport") . "</title>
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
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
         <h3>" . q($langStatsReport) . "</h3>
         <p></p>";

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
    $mpdf->Output("$course_code tc_report.pdf", 'I'); // 'D' or 'I' for download / inline display
} else if (isset($_GET['xls']) and $is_course_reviewer) { // xls output
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($langParticipate);
    $sheet->getDefaultColumnDimension()->setWidth(25);
    $filename = $course_code . "_tc_report.xlsx";
    $sheet->mergeCells("A1:B1");
    for ($i=1; $i<=4; $i++) {
        $cells = [$i, 4];
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
} else {
    draw($tool_content, 2, null, $head_content);
}

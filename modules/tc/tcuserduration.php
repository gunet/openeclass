<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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
 * @file tcuserduration.php
 * @brief display various teleconference participation reports
 */
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
    if (!isset($_GET['pdf'])) {
        $tool_content .= action_bar(array(
            array('title' => "$langExport",
                'url' => "dumptcuserduration.php?course=$course_code&amp;meeting_id=$meetingid",
                'icon' => 'fa-file-zipper',
                'level' => 'primary-label',
                'button-class' => 'btn-success',
                'show' => $is_course_reviewer),
            array('title' => $langDumpPDF,
                'url' => "tcuserduration.php?course=$course_code&amp;id=$_GET[id]&amp;pdf=true",
                'icon' => 'fa-file-pdf',
                'level' => 'primary-label',
                'button-class' => 'btn-success',
                'show' => $is_course_reviewer),
            array('title' => $langBack,
                'url' => "index.php?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label')
        ));
    }
} else {
    if (!isset($_GET['pdf'])) {
        if (isset($_GET['per_user'])) {
            $url = "tcuserduration.php?course=$course_code&per_user=true&pdf=true";
            $back_url = "index.php?course=$course_code";
        } else if (isset($_GET['u'])) {
            $url = "tcuserduration.php?course=$course_code&u=$_GET[u]&pdf=true";
            $back_url = "tcuserduration.php?course=$course_code&per_user=true";
        } else {
            $url = "tcuserduration.php?course=$course_code&pdf=true";
            $back_url = "index.php?course=$course_code";
        }
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$back_url",
                'icon' => 'fa-reply',
                'level' => 'primary'),
            array('title' => $langDumpPDF,
                'url' => "$url",
                'icon' => 'fa-file-pdf',
                'level' => 'primary-label',
                'button-class' => 'btn-success',
                'show' => $is_course_reviewer)
        ));
    }
}

if (isset($_GET['per_user']) or isset($_GET['u'])) { // all users participation in meetings
    if (!$is_course_reviewer and isset($_GET['per_user'])) { // security check
        redirect_to_home_page();
    }
    if (isset($_GET['u']) and $_GET['u']) { // participation for specific user
        if (!$is_course_reviewer and $_GET['u'] != $_SESSION['uid']) { // security check
            redirect_to_home_page();
        }
        $u = $_GET['u'];
        $bbb_name = uid_to_name($u, 'username');
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
            $tool_content .= "<div class='panel panel-default'>";
            $tool_content .= "<div class='panel-heading'><strong>" . q(uid_to_name($u, 'surname')) . " " . q(uid_to_name($u, 'givenname')) . "</strong></div>";
            $tool_content .= "<div class='panel-body'><em>$langTotalDuration:</em> <strong>" . format_time_duration(0 + 60 * $total_time, 24, false) . "</strong></span></div><br>";
            $tool_content .= "</div>";
            $tool_content .= "<div class='table-responsive'><table class='table-default'>";
            $tool_content .= "<thead><tr class='list-header'>                                  
                              <th>$langBBB</th>
                              <th>$langLogIn</th>
                              <th>$langDuration</th>
                           </tr></thead>";
            foreach ($result as $row) {
                $tool_content .= "<tr>                            
                        <td>$row->title</td>
                        <td>" . format_locale_date(strtotime($row->date), 'full') . "</td>
                        <td>" . format_time_duration(0 + 60 * $row->totaltime, 24, false) . "</td>
                    </tr>";
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
                      <th class='text-center'>" . icon('fa-gears') . "</th>
                    </tr></thead>";

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
            if (isset($_GET['pdf'])) {
                $link_to_user = "$user->surname" . " " . "$user->givenname";
                $grp_name = user_groups($course_id, $user->id, false);
            } else {
                $link_to_user = "$_SERVER[SCRIPT_NAME]?course=$course_code&u=$user->id";
                $grp_name = user_groups($course_id, $user->id);
            }

            $tool_content .= "<tr>                            
                        <td>$user->surname $user->givenname</td>
                        <td>$user->am</td>
                        <td>" . $grp_name . "</td>                            
                        <td>" . format_time_duration(0 + 60 * $result->totaltime, 24, false) . "</td>
                        <td>" . icon('fa-line-chart', $langDetails, $link_to_user) . "</td>
                    </tr>";
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
                                  <th>$langBBB</th>
                                  <th>$langLogIn</th>
                                  <th>$langTotalDuration</th>
                               </tr></thead>";
                $temp_date = $row->start_date;
                $first = false;
            }
            $user_full_name = Database::get()->querySingle("SELECT fullName FROM tc_log
                            WHERE tc_log.bbbuserid = ?s ORDER BY id DESC LIMIT 1", $row->bbbuserid)->fullName;
            $tool_content .= "<tr><td>$user_full_name</td>                            
                            <td>$row->title</td>
                            <td>" . format_locale_date(strtotime($row->date), 'full') . "</td>
                            <td>" . format_time_duration(0 + 60 * $row->totaltime, 24, false) . "</td>
                            </tr>";
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
          <title>" . q("$currentCourseName - $langParticipate") . "</title>
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
        "<h2> " . get_config('site_name') . " - " . q($currentCourseName) . " - " . q($langParticipate) . "</h2><p></p>";

    $pdf_content .= $tool_content;
    $pdf_content .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'tempDir' => _MPDF_TEMP_PATH,
        'fontDir' => array_merge($fontDirs, [ $webDir . '/template/default/fonts' ]),
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
    $mpdf->Output("$course_code tc_report.pdf", 'I'); // 'D' or 'I' for download / inline display
} else {
    draw($tool_content, 2, null, $head_content);
}

<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * @file userduration.php
 * @brief Shows logins made by a user or all users of a course, during a specific period.
 * Data from table 'logins'
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'course_stats';
$helpSubTopic = 'users_participation';

require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/usage/usage.lib.php';

if (isset($_GET['u'])) { //  stats per user
    if ($_SESSION['uid'] != $_GET['u'] and !$is_course_reviewer) { // security check
        Session::Messages($langCheckCourseAdmin, 'alert-danger');
        redirect_to_home_page("courses/$course_code/");
    }

    $am_legend = $xls_am_legend = $grp_legend = $xls_grp_legend = '';
    $am = uid_to_am($_GET['u']);
    if (!empty($am)) {
        $am_legend = " ($langAmShort: " . $am . ")"; // user am
        $xls_am_legend = "$langAmShort: $am";
    }
    $grp_name = user_groups($course_id, $_GET['u'], false);
    if ($grp_name != '-') {
        $grp_legend = "<span><small>$langGroup: " . $grp_name . "</small></span>"; // user group
        $xls_grp_legend = "$langGroup: $grp_name";
    }

    $user_actions = Database::get()->queryArray("SELECT
                            SUM(ABS(actions_daily.duration)) AS duration,
                              module_id
                            FROM actions_daily
                            WHERE course_id = ?d
                              AND user_id = ?d
                             AND module_id != " . MODULE_ID_TC . "
                             AND module_id != " . MODULE_ID_LP . "
                            GROUP BY module_id", $course_id, $_GET['u']);


    if (isset($_GET['format']) and $_GET['format'] == 'xls') { // xls output

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($langParticipate);
        $sheet->getDefaultColumnDimension()->setWidth(25);
        $filename = $course_code . "_user_duration.xlsx";

        $user_details = uid_to_name($_GET['u']) . " $xls_am_legend $xls_grp_legend";
        $data[] = [ $user_details ];

        $ud = user_duration_course($_GET['u']);
        $uhits = course_hits($course_id, $_GET['u']);
        $ureg = get_course_user_registration($course_id, $_GET['u']);
        $data[] = [ $langCourseRegistrationDate, $ureg];
        $data[] = [ $langHits, $uhits ];
        $data[] = [ $langTotalDuration, $ud];

        $data[] = [];

        $data[] = [ $langModule, $langDuration ];
        foreach ($user_actions as $ua) {
            $mod = which_module($ua->module_id);
            $dur = format_time_duration(0 + $ua->duration, 24, false);
            $data[] = [ $mod, $dur ];
        }
        // learning path duration
        $lp_time = timeToSeconds(get_lp_stats($course_id, $_GET['u']));
        if ($lp_time > 0) {
            $mod =  which_module(MODULE_ID_LP);
            $dur = format_time_duration($lp_time, 24, false);
            $data[] = [ $mod, $dur ];
        }

        $sheet->mergeCells("A1:B1");
        $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
        $sheet->getCell('A2')->getStyle()->getFont()->setBold(true);
        $sheet->getCell('A3')->getStyle()->getFont()->setBold(true);
        $sheet->getCell('A4')->getStyle()->getFont()->setBold(true);
        // create spreadsheet
        $sheet->fromArray($data, NULL);
        // file output
        $writer = new Xlsx($spreadsheet);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        set_content_disposition('attachment', $filename);
        $writer->save("php://output");
        exit;

    } else { // html + pdf output
        if ($is_course_reviewer) {
            $back_url = "$_SERVER[SCRIPT_NAME]?course=$course_code";
        } else {
            $back_url = "{$urlAppend}courses/$course_code/";
        }
        if (!isset($_GET['format'])) {
            $toolName = "$langParticipate $langOfUserS";
            if ($is_course_reviewer) {
                $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
                $navigation[] = array('url' => 'userduration.php?course=' . $course_code, 'name' => $langUserDuration);
            }
            $tool_content .= action_bar(array(
                array('title' => $langDumpPDF,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$_GET[u]&amp;format=pdf",
                    'icon' => 'fa-file-pdf-o',
                    'level' => 'primary-label'),
                array('title' => $langDumpUser,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$_GET[u]&amp;format=xls",
                    'icon' => 'fa-download',
                    'level' => 'primary-label'),
                array('title' => $langBack,
                    'url' => "$back_url",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
            ), false);
            $tool_content .= "<div class='panel panel-default'>";
            $tool_content .= "<div class='panel-heading'><strong>"  . uid_to_name($_GET['u']) . "</strong> $am_legend $grp_legend</div>";
            $tool_content .= "<div class='panel-body'>";
            $tool_content .= "<h5><strong>$langTotalDuration:</strong> ". user_duration_course($_GET['u']) . "</h5>";
            $tool_content .= "<h6><strong>$langCourseRegistrationDate:</strong> " . get_course_user_registration($course_id, $_GET['u']) . "</h6>";
            $tool_content .= "<h6><strong>$langHits:</strong> ".course_hits($course_id, $_GET['u']) . "</h6>";
            $tool_content .= "</div></div>";
        } else {
            $tool_content .= "<h3>"  . uid_to_name($_GET['u']) . " $am_legend $grp_legend</h3>";
            $tool_content .= "<h3><strong>$langTotalDuration:</strong> ". user_duration_course($_GET['u']) . "</h3>";
            $tool_content .= "<h6><strong>$langCourseRegistrationDate:</strong> " . get_course_user_registration($course_id, $_GET['u']) . "</h6>";
            $tool_content .= "<h6><strong>$langHits:</strong> ".course_hits($course_id, $_GET['u']) . "</h6>";
        }

        $tool_content .= "
            <table class='table-default'>
            <tr class='list-header'>
              <th>$langModule</th>
              <th>$langDuration</th>
            </tr>";
        foreach ($user_actions as $ua) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>" . which_module($ua->module_id) . "</td>";
            $tool_content .= "<td>" . format_time_duration(0 + $ua->duration, 24, false) . "</td>";
            $tool_content .= "</tr>";
        }

        // learning path duration
        $lp_time = timeToSeconds(get_lp_stats($course_id, $_GET['u']));
        if ($lp_time > 0) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>" . which_module(MODULE_ID_LP) . "</td>";
            $tool_content .= "<td>" . format_time_duration($lp_time, 24, false) . "</td>";
            $tool_content .= "</tr>";
        }

        $tool_content .= "</table>";

        // user last logins
        $user_logins = Database::get()->queryArray("SELECT last_update
                      FROM actions_daily
                            WHERE course_id = ?d
                              AND user_id = ?d
                    AND module_id = ". MODULE_ID_UNITS . "
                    ORDER BY last_update
                    DESC ", $course_id, $_GET['u']);

        if (count($user_logins) > 0) {
            $tool_content .= "<table class='table-default'>
            <tr class='list-header'>
                <th>$langLastUserVisits</th>
            </tr>";
            foreach ($user_logins as $ul) {
                $tool_content .= "<tr>";
                $tool_content .= "<td>" . format_locale_date(strtotime($ul->last_update)) . "</td>";
                $tool_content .= "</tr>";
            }
            $tool_content .= "</table>";
        }
    }

    if (isset($_GET['format']) and $_GET['format'] == 'pdf') { // pdf format
        pdf_output();
    } else {
        draw($tool_content, 2);
    }
} else if ($is_course_reviewer and isset($_GET['m']) and $_GET['m'] != -1) { // stats per module

    $module = $_GET['m'];
    $user_actions = Database::get()->queryArray("SELECT
                        SUM(actions_daily.duration) AS duration, user_id,
                          module_id
                        FROM actions_daily
                        WHERE course_id = ?d
                          AND module_id = ?d
                        GROUP BY user_id", $course_id, $module);



    if (isset($_GET['format']) and $_GET['format'] == 'xls') { // xls output
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($langParticipate);
        $sheet->getDefaultColumnDimension()->setWidth(40);
        $filename = $course_code . "_user_duration.xlsx";
        $mod = which_module($module);

        $data[] = [ "$langModule: $mod" ];
        $data[] = [ $langUser, $langGroup, $langAm, $langDuration ];

        foreach ($user_actions as $um) {
            $grp_name = user_groups($course_id, $um->user_id, false);
            $user_am = uid_to_am($um->user_id);
            $user_details = uid_to_name($um->user_id);
            if ($module == MODULE_ID_LP) {
                $lp_time = timeToSeconds(get_lp_stats($course_id, $um->user_id));
                $data[] = [$user_details, $grp_name, $user_am, format_time_duration(0 + $lp_time, 24, false)];
            } else {
                $data[] = [$user_details, $grp_name, $user_am, format_time_duration(0 + $um->duration, 24, false)];
            }
        }

        $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
        for ($i=1; $i<=4; $i++) {
            $cells = [$i, 2];
            $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
        }

        // create spreadsheet
        $sheet->fromArray($data, NULL);
        // file output
        $writer = new Xlsx($spreadsheet);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=$filename");
        $writer->save("php://output");
        exit;
    } else { // html output
        if (!isset($_GET['format'])) {
            $toolName = "$langParticipate $langOfUserS";
            $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
            $navigation[] = array('url' => '$_SERVER[SCRIPT_NAME]?course=' . $course_code, 'name' => $langUserDuration);

            $tool_content .= action_bar(array(
                array('title' => $langDumpPDF,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;m=$module&amp;format=pdf",
                    'icon' => 'fa-file-pdf-o',
                    'level' => 'primary-label'),
                array('title' => $langDumpUser,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;m=$module&amp;format=xls",
                    'icon' => 'fa-download',
                    'level' => 'primary-label'),
                array('title' => $langBack,
                    'url' => "index.php?course=$course_code",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
            ), false);
            $tool_content .= selection_course_modules();
            $tool_content .= "<div class='alert alert-info'>" . which_module($module) . "</div>";
        } else {
            $tool_content .= "<h3>" . which_module($module) . "</h3>";
        }

        $tool_content .= "
            <table class='table-default'>
            <tr>
                <th>$langUser</th>
                <th>$langGroup</th>
                <th>$langAm</th>
                <th>$langDuration</th>
            </tr>";
        foreach ($user_actions as $um) {
            if (!isset($_GET['format'])) {
                $grp_name = user_groups($course_id, $um->user_id);
            } else {
                $grp_name = user_groups($course_id, $um->user_id, false);
            }
            $user_am = uid_to_am($um->user_id);
            $tool_content .= "<tr>";
            if (!isset($_GET['format'])) {
                $tool_content .= "<td>" . display_user($um->user_id) . "</td>";
            } else {
                $tool_content .= "<td>" . uid_to_name($um->user_id) . "</td>";
            }
            $tool_content .= "<td>" . $grp_name . "</td>";
            $tool_content .= "<td>" . $user_am . "</td>";

            if ($module == MODULE_ID_LP) {
                $lp_time = timeToSeconds(get_lp_stats($course_id, $um->user_id));
                $tool_content .= "<td>" . format_time_duration(0 + $lp_time, 24, false) . "</td>";
            } else {
                $tool_content .= "<td>" . format_time_duration(0 + $um->duration, 24, false) . "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>";
        if (isset($_GET['format']) and $_GET['format'] == 'pdf') {
            pdf_output();
        } else {
            draw($tool_content, 2);
        }
    }
} else if ($is_course_reviewer) {
    if (isset($_GET['format']) and $_GET['format'] == 'xls') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($langParticipate);
        $sheet->getDefaultColumnDimension()->setWidth(20);
        $filename = $course_code . "_users_duration.xlsx";
        $data[] = [ $langSurname, $langName, $langAm, $langGroup, $langDuration ];

        for ($i=1; $i<=5; $i++) { // format first row
            $cells = [$i, 1];
            $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
        }

    } else {
        if (!isset($_GET['format'])) {
            $toolName = $langUserDuration;
            $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
            $tool_content .= action_bar(array(
                array('title' => $langLearningPaths,
                    'url' => "../learnPath/detailsAll.php?course=$course_code",
                    'icon' => 'fa-vcard-o',
                    'level' => 'primary-label'),
                array('title' => $langBBB,
                    'url' => "../tc/tcuserduration.php?course=$course_code&amp;per_user=true",
                    'icon' => 'fa-vcard-o',
                    'level' => 'primary-label'),
                array('title' => $langDumpPDF,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;format=pdf",
                    'icon' => 'fa-file-pdf-o',
                    'level' => 'primary-label'),
                array('title' => $langDumpUser,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;format=xls",
                    'icon' => 'fa-download',
                    'level' => 'primary-label'),
                array('title' => $langBack,
                    'url' => "index.php?course=$course_code",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
            ), false);

            $tool_content .= selection_course_modules();
        }
        $tool_content .= "
        <table class='table-default'>
        <tr>
          <th>$langSurnameName</th>
          <th>$langAm</th>
          <th>$langGroup</th>
          <th>$langDuration</th>
          <th class='text-center'>" . icon('fa-gears') . "</th>
        </tr>";
    }

    $result = user_duration_query($course_id);
    if (count($result) > 0) {
        foreach ($result as $row) {
            if (!isset($_GET['format'])) {
                $grp_name = user_groups($course_id, $row->id);
            } else {
                $grp_name = user_groups($course_id, $row->id, false);
            }
            if (isset($_GET['format']) and $_GET['format'] == 'xls') {
                $data[] = [ $row->surname, $row->givenname, $row->am, $grp_name, format_time_duration(0 + $row->duration, 24, false) ];
            } else {
                $tool_content .= "<tr>";
                if (!isset($_GET['format'])) {
                    $tool_content .= "<td>" . display_user($row->id) . "</td>";
                } else {
                    $tool_content .= "<td>" . uid_to_name($row->id) . "</td>";
                }
                $tool_content .= "<td class='center'>$row->am</td>
                                <td class='center'>$grp_name</td>
                                <td class='center'>" . format_time_duration(0 + $row->duration, 24, false) . "</td>";
                if (!isset($_GET['format'])) {
                    $tool_content .= "<td class='center'>" . icon('fa-line-chart', $langDetails, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$row->id") . "</td>";
                }
                $tool_content .= "</tr>";
            }
        }
        $tool_content .= "</table>";
    }
    if (isset($_GET['format']) and $_GET['format'] == 'xls') {
        // create spreadsheet
        $sheet->fromArray($data, NULL);
        // file output
        $writer = new Xlsx($spreadsheet);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=$filename");
        $writer->save("php://output");

    } elseif (isset($_GET['format']) and $_GET['format'] == 'pdf') { // pdf format
        pdf_output();
    } else {
        draw($tool_content, 2);
    }
}

/**
 * @brief Do the queries to calculate usage between timestamps $start and $end
 * @param type $course_id
 * @param type $start
 * @param type $end
 * @param type $group
 * @return returns a MySQL resource, where fetching rows results in duration, surname, givenname, user_id, am
 */
function user_duration_query($course_id, $start = false, $end = false, $group = false) {
    $terms = array();
    if ($start !== false AND $end !== false) {
        $date_where = 'AND actions_daily.day BETWEEN ?s AND ?s';
        $terms = array($start . ' 00:00:00',
                       $end . ' 23:59:59');
    } elseif ($start !== false) {
        $date_where = 'AND actions_daily.day > ?s';
        $terms[] = $start . ' 00:00:00';
    } elseif ($end !== false) {
        $date_where = 'AND actions_daily.day < ?s';
        $terms[] = $end . ' 23:59:59';
    } else {
        $date_where = '';
    }

    if ($group !== false) {
        $from = "`group_members` AS groups
                                LEFT JOIN user ON groups.user_id = user.id";
        $and = "AND groups.group_id = ?d";
        $terms[] = $group;
    } else {
        $from = " (SELECT id, surname, givenname, username, email, status, am
                      FROM user
                      UNION
                          (SELECT 0 AS id,
                            '' AS surname,
                            'Anonymous' AS givenname,
                            null AS username,
                            null AS email,
                            null AS status,
                            null AS am)
                       ) AS user ";
        $and = '';
    }

    return Database::get()->queryArray("SELECT SUM(ABS(actions_daily.duration)) AS duration,
                                       user.surname AS surname,
                                       user.givenname AS givenname,
                                       user.id AS id,
                                       user.am AS am
                                FROM $from
                                LEFT JOIN actions_daily ON user.id = actions_daily.user_id
                                WHERE (actions_daily.course_id = ?d
                                    AND actions_daily.module_id != " . MODULE_ID_TC . ")
                                $and
                                $date_where
                                GROUP BY user.id, surname, givenname, am
                                ORDER BY surname, givenname",  $course_id, $terms);
}


/**
 * @brief display selection box of course modules
 * @return string
 */
function selection_course_modules() {

    global $langAllModules, $langModule, $langInfoUserDuration, $langInfoUserDuration2, $course_id, $modules, $course_code, $module;

    $mod_opts = "<option value='-1'>$langAllModules</option>";
    $result = Database::get()->queryArray("SELECT module_id FROM course_module
                    WHERE course_id = ?d
                    AND module_id != " . MODULE_ID_TC , $course_id);
    foreach ($result as $row) {
        $mid = $row->module_id;
        $extra = '';
        if ($module == $mid) {
            $extra = 'selected';
        }
        $mod_opts .= "<option value=" . $mid . " $extra>" . $modules[$mid]['title'] . "</option>";
    }

    $content = "<div class='alert alert-info'>$langInfoUserDuration $langInfoUserDuration2</div>";
    $content .= "<div class='row'>
        <div class='col-md-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' name='module_select' action='$_SERVER[SCRIPT_NAME]' method='get'>
                <input type='hidden' name='course' value='$course_code'>
                    <div class='form-group'>
                        <label class='col-sm-8 control-label'>$langModule</label>
                        <div class='col-sm-4'>
                            <select name='m' id='m' class='form-control' onChange='document.module_select.submit();'>
                                $mod_opts
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>";

    return $content;
}

/**
 * @brief output to pdf file
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_output() {
    global $tool_content, $langUserDuration, $currentCourseName,
           $webDir, $course_id, $course_code;

    $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName - $langUserDuration") . "</title>
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
        <h2> " . q($langUserDuration) . "</h2>";

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
    $mpdf->Output("$course_code user_report.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}

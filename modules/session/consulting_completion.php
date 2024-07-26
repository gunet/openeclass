<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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
 * @file consulting_completion.php
 * @brief Display a detailed table about consulting completion for each user
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'functions.php';

check_activation_of_collaboration();

$pageName = $langTableCompletedConsulting;

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

$users_actions = [];
$sql_consultant = "";
if($is_consultant && !$is_coordinator){
    $sql_consultant = "AND creator = $uid";
}

$forUser = "";
$data['action_bar'] = "";
$user_pdf = "";
if(isset($_GET['user_rep'])){
    $sql_consultant = "";
    $user_pdf = "&amp;session=$_GET[session]&amp;user_rep=$_GET[user_rep]";
    $forUser = "AND user_id = " . $_GET['user_rep'];
    $data['action_bar'] = action_bar([
        [ 'title' => $langBack,
          'url' => 'user_report.php?course=' . $course_code . '&session=' . $_GET['session'],
          'icon' => 'fa-reply',
          'button-class' => 'btn-success',
          'level' => 'primary-label' ]
    ], false);
}

$res = Database::get()->queryFunc("SELECT user_id FROM course_user 
                                   WHERE course_id = ?d 
                                   AND status = ?d 
                                   AND tutor = ?d 
                                   AND editor = ?d 
                                   AND course_reviewer = ?d
                                   $forUser", function($result) use(&$course_id, &$users_actions, &$langSessionCondition, &$langUserHasCompletedCriteria, &$langUserHasNotCompletedCriteria, &$langPercentageSessionCompletion, &$sql_consultant, &$langAllCompletedResources)  {

                                        $userID = $result->user_id;
                                        if(isset($_GET['user_rep'])){
                                            $sql_consultant = "";
                                            $userID = $_GET['user_rep'];
                                        }

                                        $user_badge_sessions = Database::get()->queryArray("SELECT id,title,start,creator FROM mod_session 
                                                                                     WHERE course_id = ?d AND visible = ?d
                                                                                     AND id IN (SELECT session_id FROM mod_session_users
                                                                                                    WHERE participants = ?d 
                                                                                                    AND is_accepted = ?d)
                                                                                     AND id IN (SELECT session_id FROM badge WHERE course_id = ?d AND session_id > 0)
                                                                                     $sql_consultant", $course_id, 1, $userID, 1, $course_id);
                                        if(count($user_badge_sessions)){
                                            $users_actions[$result->user_id] = $user_badge_sessions;
                                            if(count($users_actions) > 0){
                                                foreach($users_actions as $key => $val){
                                                    $per = 0;
                                                    foreach($val as $v){
                                                        $badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $v->id);
                                                        if($badge){
                                                            $per = get_cert_percentage_completion_by_user('badge',$badge->id,$key);
                                                        }
                                                        if($per < 100){
                                                            $icon_badge = " 
                                                                            <strong>
                                                                                $langPercentageSessionCompletion
                                                                            </strong>
                                                                            <ul>
                                                                                <li class='criteria_not_completed Accent-200-cl' style='font-style:normal; font-weight:600;'>
                                                                                    $per%
                                                                                </li>
                                                                            </ul>";
                                                        }else{
                                                            $icon_badge = " 
                                                                            <strong>
                                                                                $langPercentageSessionCompletion
                                                                            </strong>
                                                                            <ul>
                                                                                <li class='criteria_completed Success-200-cl' style='font-style:normal; font-weight:600;'>
                                                                                    $per%
                                                                                </li>
                                                                            </ul>";
                                                        }
                                                        $v->completion = $icon_badge;
                                                    }
                                                }
                                            }
                                        }
                                 }, $course_id, USER_STUDENT, 0, 0, 0);

// Display users reports in a table
$tool_content .= "
    <div class='col-12'>";
        if(count($users_actions) > 0){
 $tool_content .= " <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                            <h3 class='mb-0'>$langUserReferences</h3>
                            <a class='btn submitAdminBtn export-pdf-btn' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;format=pdf$user_pdf'>$langDumpPDF</a>
                        </div>
                        <div class='card-body'>
                            <p style='margin-bottom:25px;'>$langShowOnlySessionWithCompletionEnable</p>";
                            foreach($users_actions as $key => $val){
                $tool_content .= "<div class='card cardReports' style='margin-bottom:25px;'>
                                        <div class='card-body'>  
                                            <h4 style=' display:flex; justify-content:end; align-items:center; gap:5px; margin-bottom:10px;'>
                                                <img class='user-icon-filename' src='".user_icon($key, IMAGESIZE_SMALL)."' alt='".participant_name($key)."'>
                                                " . participant_name($key) . "
                                            </h4>";
                $tool_content .= "  
                                            <table class='table-default'>
                                                <thead>
                                                    <tr>
                                                        <th style='width:30%;'>$langSSession</th>
                                                        <th style='width:30%;'>$langConsultant</th>
                                                        <th style='width:40%;'>$langCompletionResources</th>
                                                    </tr>
                                                </thead>";
                                                foreach($val as $v){
                                $tool_content .= "  <tr>
                                                        <td style='vertical-align:top;'>
                                                            <strong>{$v->title}</br>
                                                                " .format_locale_date(strtotime($v->start), 'short', false). "
                                                            </strong>
                                                        </td>
                                                        <td style='vertical-align:top;'>" . participant_name($v->creator) . "</td>
                                                        <td style='vertical-align:top;'>
                                                            " . session_completed_resources_by_user($v->id,$course_id,$key) ."
                                                            <div>{$v->completion}</div>
                                                        </td>
                                                    </tr>";
                                                }
                        $tool_content .= "  </table>";
                    $tool_content .= "    </div>
                                    </div>";
                            }
                  $tool_content .= "
                        </div>
                    </div>";
                }else{
  $tool_content .= "<div class='alert alert-warning'>
                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                        <span>$langNoInfoAvailable</span>
                    </div>";
                }
$tool_content .= " </div>
";


if (isset($_GET['format']) and $_GET['format'] == 'pdf') { // pdf format
    pdf_reports_output();
}else{
    $data['tool_content'] = $tool_content;
    view('modules.session.consulting_completion', $data);
}



/**
 * @brief output to pdf file
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_reports_output() {
    global $tool_content, $langUserDuration, $currentCourseName,
           $webDir, $course_id, $course_code, $langHasParticipatedInTool, $langHasNotParticipatedInTool;

    $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName") . "</title>
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
            .criteria_not_completed { color: #FF0000; }
            .criteria_completed { color: #008000;}
            .resources_list { list-style-type: none; padding: 0px; }
            .text-success { color: #228B22; }
            .text-danger { color: #D22B2B; }
            .cardReports { background: #FAFBFC; padding: 15px; border: solid 1px #989ea6; }
            .export-pdf-btn {display: none;}
          </style>
        </head>
        <body>" . get_platform_logo() .
        "<h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>";

    // Array containing icons 
    $searchVal = array('&#10004;', '&#x2718;');
    // Array containing replace icons with strings
    $replaceVal = array('<strong class="text-success">' . $langHasParticipatedInTool . '</strong>', '<strong class="text-danger">' . $langHasNotParticipatedInTool . '</strong>');
    $output = str_replace($searchVal, $replaceVal, $tool_content);
    $pdf_content .= $output;

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
    $mpdf->Output("$course_code users_reports.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}

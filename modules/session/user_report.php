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
 * @file user_report.php
 * @brief Shows reports made by a user or all users of a session.
 * Data from table 'session resources'
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'modules/progress/process_functions.php';
require_once 'functions.php';

check_activation_of_collaboration();

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}elseif(isset($_GET['id'])){
    $data['sessionID'] = $sessionID = $_GET['id'];
}

session_exists($sessionID);
check_user_belongs_in_session($sessionID);

$sessionTitle = title_session($course_id,$sessionID);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);
$pageName = $langUserReferences;

if (isset($_GET['u'])) { //  stats per user
    if ($is_simple_user) { // security check
        Session::flash('message',$langCheckCourseAdmin);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("courses/$course_code/");
    }

    $navigation[] = array('url' => 'user_report.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $langUserReferences);
    $pageName = participant_name($_GET['u']);

    $user_information = user_reference($sessionID, $course_id, $_GET['u']);

    $tool_content .= "
    <div class='col-12'>
        <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>                            
                    <h3>$langRefernce</h3>";
                    if(($is_consultant or $is_course_reviewer) && !isset($_GET['format'])){
                        $tool_content .= "<a class='btn submitAdminBtn' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;session=$sessionID&amp;u=$_GET[u]&amp;format=pdf'>$langDumpPDF</a>";
                    }
    $tool_content .= "
            </div>
            <div class='card-body'>
                <ul class='list-group list-group-flush'>";

                        $tool_content .= "<li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langName</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['username'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langTitle</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['title'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langType</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['type'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langDate</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['date'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langStart</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['start_date'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langFinish</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['end_date'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langTools</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['tools'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langUserHasCompleted</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['has_completed'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>$langPercentageSessionCompletion</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['percentage'] . "%
                                                </div>
                                            </div>
                                        </li>

                </ul>
            </div>
        </div>
    </div>";
    

    if (isset($_GET['format']) and $_GET['format'] == 'pdf') { // pdf format
        pdf_session_output($sessionID);
    } else {
        draw($tool_content, 2);
    }

} else if (!$is_simple_user) {

    $tool_content .= "
        <div class='col-sm-12'>
            <div class='table-responsive'>
                <table class='table-default'>
                    <thead>
                        <tr class='list-header'>
                            <th>$langSurnameName</th>
                            <th>$langPercentageSessionCompletion</th>
                            <th>" . icon('fa-gears') . "</th>
                        </tr>
                    </thead>";
    

    $result = users_session($sessionID);
    if (count($result) > 0) {
        foreach ($result as $row) {
            $tool_content .= "<tr>";
                $tool_content .= "<td>" . display_user($row->id) . "</td>";
                $tool_content .= "<td>$row->percentage</td>";
                $tool_content .= "<td class='text-end'>" . icon('fa-line-chart', $langDetails, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$row->id&amp;session=$sessionID") . "</td>";
            $tool_content .= "</tr>";
        }
 $tool_content .= "</table>
            </div>
        </div>";
    }

     draw($tool_content, 2);
    
}


/**
 * @brief Take user information for surrent session
 * @param int $sid
 */

 function users_session($sid){

    require_once 'modules/progress/process_functions.php';

    global $course_id;

    $result = array();

    $result = Database::get()->queryArray("SELECT user.id,user.givenname,user.surname FROM mod_session_users
                                           LEFT JOIN user ON mod_session_users.participants=user.id
                                           WHERE mod_session_users.is_accepted = ?d
                                           AND mod_session_users.session_id = ?d", 1, $sid);

    if(count($result) > 0){
        $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $sid);
        if ($sql_badge) {
            $badge_id = $sql_badge->id;
        }
        foreach($result as $r){
            if(!is_remote_session($course_id,$sid)){
                check_session_completion_by_meeting_completed($sid,$r->id);
            }elseif(is_remote_session($course_id,$sid)){
                check_session_completion_by_tc_completed($sid,$r->id);
            }
           
            check_session_progress($sid,$r->id);
            $per = get_cert_percentage_completion_by_user('badge',$badge_id,$r->id);
            $r->percentage = "
                <div class='progress' style='width:200px;'>
                    <div class='progress-bar' role='progressbar' style='width: $per%;' aria-valuenow='$per' aria-valuemin='0' aria-valuemax='100'>$per%</div>
                </div>
            ";
        }
    }else{
        $r->percentage = '';
    }

    return $result;
 }

/**
 * @brief Take user information for surrent session
 * @param int $cid
 * @param int $sid
 * @param int $u
 */
 function user_reference($sid,$cid,$u){

    global $langIndividualSession, $langGroupSession, $langNotRemote, $langRemote,
           $langDate, $langPercentageSessionCompletion, $langTools, $langRefernce, 
           $langUserHasCompleted, $langNotUploadedDeliverable, $langCommentsByConsultant,
           $langNoCommentsAvailable, $langCompletedSessionWithoutActivity;

    $session_info = Database::get()->querySingle("SELECT * FROM mod_session WHERE course_id = ?d AND id = ?d",$cid, $sid);
    $user_name = participant_name($u);
    $titleSession = title_session($cid,$sid);
    $typeSession = is_remote_session($cid,$sid) ? "$langRemote" : "$langNotRemote";
    $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $cid, $sid);
    $criteria = "";
    $tools_completed = [];
    $tools_completed_msg = "";
    $comments_by_consultant = [];
    if ($sql_badge) {
        $badge_id = $sql_badge->id;
        $badge_criteria = Database::get()->queryArray("SELECT id,activity_type,resource FROM badge_criterion WHERE badge = ?d",$badge_id);
        if(count($badge_criteria) > 0){
            $titleCr = "";
            $criteria .= "<ul>";
            foreach($badge_criteria as $c){
                if($c->activity_type == 'document-submit'){
                    $tDoc = Database::get()->querySingle("SELECT * FROM session_resources WHERE res_id = ?d AND session_id = ?d",$c->resource,$sid);
                    if($tDoc){
                        $titleDoc = $tDoc->title ?? $langTools." : ".$titleSession;
                        $titleCr = $titleDoc;
                        $completion = Database::get()->querySingle("SELECT is_completed,deliverable_comments FROM session_resources 
                                                                    WHERE doc_id = ?d AND from_user = ?d AND session_id = ?d",$c->resource, $u, $sid);
                        if($completion && $completion->is_completed){
                            $commentByConsultant = (!empty($completion->deliverable_comments)) ? $completion->deliverable_comments : "$langNoCommentsAvailable";
                            $tools_completed[] = $titleDoc . "<p><strong>" . $langCommentsByConsultant . "</strong></p><p>" . $commentByConsultant . "</p><hr></br>";
                        }
                    }
                }elseif($c->activity_type == 'tc-completed'){
                    $tc = Database::get()->querySingle("SELECT * FROM session_resources WHERE res_id = ?d",$c->resource);
                    if($tc){
                        $titleTc = $tc->title ?? $langTools." : ".$titleSession;
                        $titleCr = $titleTc;
                        $completion = Database::get()->querySingle("SELECT id FROM user_badge_criterion 
                                                                    WHERE user = ?d AND badge_criterion = ?d", $u, $c->id);
                        if($completion){
                            $tools_completed[] = $titleTc;
                        }
                    }
                }elseif($c->activity_type == 'noactivity'){
                    $titleCr = $langCompletedSessionWithoutActivity;
                    $tools_completed[] = $titleCr;
                }
                $criteria .= "<li>$titleCr</li>";
            }
            $criteria .= "</ul>";

        }
    }

    if(count($tools_completed) > 0){
        foreach($tools_completed as $t){
            $tools_completed_msg .= "<ul><li>$t</li></ul>";
        }
    }else{
        $tools_completed_msg = "$langNotUploadedDeliverable";
    }

    if(!is_remote_session($cid,$sid)){
        check_session_completion_by_meeting_completed($sid,$u);
    }elseif(is_remote_session($cid,$sid)){
        check_session_completion_by_tc_completed($sid,$u);
    }
    
    check_session_progress($sid,$u);
    $per = get_cert_percentage_completion_by_user('badge',$badge_id,$u);

    $userInfo = [
        'username' => $user_name,
        'title' => $titleSession,
        'type' => $typeSession,
        'date' => format_locale_date(strtotime($session_info->start), 'short', false),
        'start_date' => date("H:i", strtotime($session_info->start)),
        'end_date' => date("H:i", strtotime($session_info->finish)),
        'percentage' => $per,
        'tools' => $criteria,
        'has_completed' => $tools_completed_msg

    ];

    return $userInfo;
 }


/**
 * @brief output to pdf file
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_session_output($sid) {
    global $tool_content, $langUserDuration, $currentCourseName,
           $webDir, $course_id, $course_code;

    $sessionTitle = title_session($course_id,$sid);

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
          </style>
        </head>
        <body>" . get_platform_logo() .
        "<h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($sessionTitle) . "</h2>";

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
    $mpdf->Output("$course_code user_report.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}

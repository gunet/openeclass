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

if(isset($_POST['delete_material'])){
    $session_ids = Database::get()->queryArray("SELECT id FROM mod_session 
                                                WHERE course_id = ?d
                                                AND creator = ?d
                                                AND id IN (SELECT session_id FROM mod_session_users 
                                                            WHERE participants = ?d 
                                                            AND is_accepted = ?d)", $course_id, $_POST['aboutTutor'], $_POST['aboutU'], 1);
    foreach($session_ids as $s){
            Database::get()->query("DELETE FROM session_user_material 
                                    WHERE course_id = ?d
                                    AND session_id = ?d
                                    AND user_id = ?d", $course_id, $s->id, $_POST['aboutU']);
    }

    Session::flash('message',$langDelMaterialSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/user_report.php?course=$course_code&session=$sessionID");

}

if(isset($_GET['material_pdf'])){
    $m = Database::get()->querySingle("SELECT content FROM session_user_material 
                                        WHERE course_id = ?d
                                        AND session_id = ?d
                                        AND user_id = ?d", $course_id, $sessionID, $_GET['uMaterial']);

    pdf_user_material_output($sessionID, $m->content, $_GET['uMaterial']);
}

if(isset($_POST['add_material'])){
    $session_ids = Database::get()->queryArray("SELECT id FROM mod_session 
                                                WHERE course_id = ?d
                                                AND creator = ?d
                                                AND id IN (SELECT session_id FROM mod_session_users 
                                                            WHERE participants = ?d 
                                                            AND is_accepted = ?d)", $course_id, $_POST['aboutTutor'], $_POST['aboutU'], 1);

    foreach($session_ids as $s){
        $existsMaterial = Database::get()->querySingle("SELECT * FROM session_user_material
                                                        WHERE course_id = ?d
                                                        AND session_id = ?d
                                                        AND user_id = ?d", $course_id, $s->id, $_POST['aboutU']);

        if(!$existsMaterial){
            Database::get()->query("INSERT INTO session_user_material 
                                    SET content = ?s,
                                    course_id = ?d,
                                    session_id = ?d,
                                    user_id = ?d",$_POST['addContent'], $course_id, $s->id, $_POST['aboutU']);
        }else{
            Database::get()->query("UPDATE session_user_material SET content = ?s
                                    WHERE course_id = ?d
                                    AND session_id = ?d
                                    AND user_id = ?d",$_POST['addContent'], $course_id, $s->id, $_POST['aboutU']);
        }
    }

    Session::flash('message',$langRegDone);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/user_report.php?course=$course_code&session=$sessionID");
}

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
                        $tool_content .= "<a class='btn submitAdminBtn' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;session=$sessionID&amp;u=$_GET[u]&amp;format=pdf' target='_blank' aria-label='$langOpenNewTab'>$langDumpPDF</a>";
                    }
    $tool_content .= "
            </div>
            <div class='card-body'>
                <ul class='list-group list-group-flush'>";

                        $tool_content .= "<li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <strong class='title-default'>$langName</strong>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['username'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <strong class='title-default'>$langTitle</strong>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['title'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <strong class='title-default'>$langType</strong>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['type'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <strong class='title-default'>$langDate</strong>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['date'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <strong class='title-default'>$langStart</strong>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['start_date'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <strong class='title-default'>$langFinish</strong>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . $user_information['end_date'] . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <strong class='title-default'>$langCompletionResources</strong>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    " . session_completed_resources_by_user($sessionID, $course_id, $_GET['u']) . "
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <strong class='title-default'>$langPercentageSessionCompletion</strong>
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
                            <th aria-label='$langSettingSelect'>" . icon('fa-gears') . "</th>
                            <th aria-label='$langSettingSelect'></th>
                        </tr>
                    </thead>";


    $result = users_session($sessionID);
    if (count($result) > 0) {
        $linkReport = $urlServer . "modules/session/consulting_completion.php?course=$course_code&session=$sessionID";
        foreach ($result as $row) {
            $tool_content .= "<tr>";
                $tool_content .= "<td>
                                    <a class='link-color d-flex justify-content-start align-items-center gap-2' href='" . $linkReport . "&user_rep=$row->id" . "' aria-label='".participant_name($row->id)."'
                                        data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langShowReportUserTable'>
                                        <img class='user-icon-filename' src='".user_icon($row->id, IMAGESIZE_SMALL)."' alt='$langUser:".participant_name($row->id)."'>
                                        <span>" . participant_name($row->id) . "</span>
                                    </a>
                                 </td>";
                $tool_content .= "<td>$row->percentage</td>";
                $tool_content .= "<td class='text-center'>" . icon('fa-line-chart', $langShowReportUserCurrentSession, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$row->id&amp;session=$sessionID") . "</td>";
                $tool_content .= "<td class='text-center'>
                                    <a class='link-color' data-bs-toggle='modal' href='#materialForUser{$row->id}' aria-label='$langMaterialForUser'>
                                        <span class='fa-solid fa-newspaper' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langMaterialForUser'>
                                    </a>
                                  </td>";
            $tool_content .= "</tr>";


            $contentToModify = "";
            $materialToPdf = "";
            $materialToDelete = "";
            $tutorSession = 0;
            $action_msg = "$langAdd";

            if($is_consultant && !$is_coordinator){
                $tutorSession = $uid;
            }elseif($is_coordinator){
                $tutorSession = Database::get()->querySingle("SELECT creator FROM mod_session WHERE id = ?d", $sessionID)->creator;
            }

            $contentInfo = Database::get()->queryArray("SELECT content FROM session_user_material 
                                                        WHERE course_id = ?d
                                                        AND session_id IN (SELECT id FROM mod_session WHERE creator = ?d)
                                                        AND user_id = ?d", $course_id, $tutorSession, $row->id);

            if(count($contentInfo) > 0){
                foreach($contentInfo as $c){
                    if(!empty($c->content)){
                        $contentToModify = $c->content;
                    }
                }
            }



            if(!empty($contentToModify)){
                $action_msg = "$langModify";
                $materialToPdf = "<a class='btn successAdminBtn' href='$_SERVER[SCRIPT_NAME]?course={$course_code}&session={$sessionID}&material_pdf=true&uMaterial={$row->id}' target='_blank'>$langDumpPDF</a>";
                $materialToDelete = "<a class='btn deleteAdminBtn' href='#deleteMaterialForUser{$row->id}' data-bs-toggle='modal' target='_blank'>$langDelete</a>";
            }

            $not_display = "";
            if($is_course_reviewer && !$is_consultant){
                $materialToPdf = "";
                $materialToDelete = "";
                $not_display = "d-none";
            }

            $tool_content .= "
            <div class='modal fade' id='materialForUser{$row->id}' tabindex='-1' aria-labelledby='materialForUser{$row->id}Label' aria-hidden='true'>
                <form method='post' action='$_SERVER[SCRIPT_NAME]?course={$course_code}&session={$sessionID}'>
                    <div class='modal-dialog modal-lg modal-success'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <div class='modal-title'>
                                    <div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div>
                                    <div class='modal-title-default text-center mb-0 mt-2' id='materialForUser{$row->id}Label'>$langMaterialForUser</div>
                                </div>
                            </div>
                            <div class='modal-body text-center'>
                                <input type='hidden' name='aboutU' value='{$row->id}' />
                                <input type='hidden' name='aboutSession' value='{$sessionID}' />
                                <input type='hidden' name='aboutTutor' value='{$tutorSession}' />
                                " . rich_text_editor('addContent', 4, 20, $contentToModify) . "
                            </div>
                            <div class='modal-footer d-flex justify-content-center align-items-center'>
                                <a class='btn cancelAdminBtn' href='' data-bs-dismiss='modal'>$langCancel</a>
                                <button type='submit' class='btn submitAdminBtnDefault $not_display' name='add_material'>$action_msg</button>
                                $materialToPdf
                                $materialToDelete
                            </div>
                        </div>
                    </div>
                </form>
            </div>";

            $tool_content .= "
            <div class='modal fade' id='deleteMaterialForUser{$row->id}' tabindex='-1' aria-labelledby='deleteMaterialForUser{$row->id}Label' aria-hidden='true'>
                <form method='post' action='$_SERVER[SCRIPT_NAME]?course={$course_code}&session={$sessionID}'>
                    <div class='modal-dialog modal-md modal-danger'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <div class='modal-title'>
                                    <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                                    <div class='modal-title-default text-center mb-0' id='deleteMaterialForUser{$row->id}Label'>$langConfirmDelete</div>
                                </div>
                            </div>
                            <div class='modal-body text-center'>
                                <input type='hidden' name='aboutU' value='{$row->id}' />
                                <input type='hidden' name='aboutTutor' value='{$tutorSession}' />
                            </div>
                            <div class='modal-footer d-flex justify-content-center align-items-center'>
                                <a class='btn cancelAdminBtn' href='' data-bs-dismiss='modal'>$langCancel</a>
                                <button type='submit' class='btn deleteAdminBtn' name='delete_material'>$langDelete</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>";



        }

 $tool_content .= "</table>
            </div>
        </div>";
    }else{
        $tool_content .= "<tr>
                            <td>&#8722;</td>
                            <td>&#8722;</td>
                            <td>&#8722;</td>
                          </tr>
                        </table></div></div>";
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
            check_session_completion_without_activities($sid);
            if(isset($badge_id)){
                $per = get_cert_percentage_completion_by_user('badge',$badge_id,$r->id);
            }else{
                $per = 0;
            }

            $r->percentage = "
                <div class='progress' style='width:200px;'>
                    <div class='progress-bar' role='progressbar' style='width: $per%;' aria-valuenow='$per' aria-valuemin='0' aria-valuemax='100'>$per%</div>
                </div>
            ";
        }
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
           $langNoCommentsAvailable, $langCompletedSessionWithoutActivity, $langCompletedSessionMeeting;

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
                            $tools_completed[] = $titleDoc . "<div style='margin-top:5px;'><strong style='text-decoration: underline;'>" . $langCommentsByConsultant . "</strong></div><ul><li style='margin-bottom:20px;'>" . $commentByConsultant . "</li></ul>";
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
                }elseif($c->activity_type == 'meeting-completed'){
                    $titleCr = $langCompletedSessionMeeting;
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
    check_session_completion_without_activities($sid);
    if(isset($badge_id)){
        $per = get_cert_percentage_completion_by_user('badge',$badge_id,$u);
    }else{
        $per = 0;
    }


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
           $webDir, $course_id, $course_code, $language, $langHasParticipatedInTool,
           $langHasNotParticipatedInTool;

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
            .text-success { color: #228B22; }
            .text-danger { color: #D22B2B; }
          </style>
        </head>
        <body><div style='height: 160px;'></div>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($sessionTitle) . "</h2>";

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
    $mpdf->Output("$course_code user_report.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}


/**
 * @brief output to pdf file for materials
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_user_material_output($sid,$content_m,$user_n) {
    global $currentCourseName, $webDir, $course_id, $course_code, $language, $langMaterialForUser;

    $sessionTitle = title_session($course_id,$sid);
    $nameUser = participant_name($user_n);

    $pdf_mcontent = "
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
          </style>
        </head>
        <body><div style='height: 160px;'></div>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($sessionTitle) . "</h2>
        <h3>$langMaterialForUser:&nbsp;&nbsp;" . q($nameUser) . "<h3>";

    $pdf_mcontent .= $content_m;
    $pdf_mcontent .= "</body></html>";

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
    $mpdf->WriteHTML($pdf_mcontent);
    $mpdf->Output("$course_code user_material.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}

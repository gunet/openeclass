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
 * @file consulting_completion.php
 * @brief Display a detailed table about consulting completion for each user
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/progress/process_functions.php';
require_once 'functions.php';

check_activation_of_collaboration();

if ($is_simple_user) {
    Session::flash('message', $langForbidden);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/session/index.php?course=$course_code");
}

$pageName = $langTableCompletedConsulting;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

$head_content .= "
    <script>
        $(function() {
            $('.link-Update-Percentage').on('click', function () {
                $('.show-calculation-message').removeClass('d-none').addClass('d-block');
            });
        });
    </script>";

$users_actions = [];
$forUser = "";
$forUserArgs = "";
$forUserIn = 0;
$data['action_bar'] = "";
$user_pdf = "";
$user_selected = 0;
$statusComplete = isset($_GET['status']) ? '&status=complete' : '';

if(isset($_GET['user_rep'])) {
    $user_pdf = "&amp;session=$_GET[session]&amp;user_rep=$_GET[user_rep]";
    $forUser = "AND user_id = ?d";
    $forUserArgs = [$_GET['user_rep']];
    $user_selected = $_GET['user_rep'];

    $data['action_bar'] = action_bar([
        [ 'title' => $langBack,
          'url' => 'user_report.php?course=' . $course_code . '&session=' . $_GET['session'],
          'icon' => 'fa-reply',
          'button-class' => 'btn-success',
          'level' => 'primary-label' ]
    ], false);
}

// consultant mode
if($is_consultant && !$is_coordinator && isset($_POST['form_user_report']) && $_POST['form_user_report'] > 0) {
    $user_pdf = "&amp;user_rep=$_POST[form_user_report]";
    $forUser = "AND user_id = ?d";
    $forUserArgs = [$_POST['form_user_report']];
    $user_selected = $_POST['form_user_report'];
}

if(isset($_GET['user_docs'])) {
    $userid = $_GET['user_docs'];
    if($userid > 0){
        $user_pdf = "&amp;user_rep=$_GET[user_docs]";
        $forUser = "AND user_id = ?d";
        $forUserArgs = [$_GET['user_docs']];
        $user_selected = $_GET['user_docs'];
        $sessions_user = Database::get()->queryArray("
            SELECT DISTINCT s.id, s.title
            FROM mod_session s

            INNER JOIN mod_session_users su
                ON su.session_id = s.id
            AND su.participants = ?d
            AND su.is_accepted = ?d

            WHERE s.course_id = ?d
            AND s.creator = ?d
        ", $userid, 1, $course_id, $uid);

        if(count($sessions_user) > 0){
            $dload_filename = $webDir . '/courses/temp/' . safe_filename('zip');
            $theName = preg_replace('/\s+/', '_', participant_name($userid));
            $real_filename = $theName . '.zip';
            $subsystem = MYSESSIONS;
            $empty_dirs = 0;
            $zipFile = new ZipArchive();
            $zipFile->open($dload_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            foreach($sessions_user as $s){
                $subsystem_id = $s->id;
                $group_sql = "course_id = $course_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id AND lock_user_id = $userid";
                $basedir = $webDir . '/courses/' . $course_code . '/session/session_' . $s->id . '/' . $userid;
                $total_docs = Database::get()->queryArray("SELECT id FROM document WHERE $group_sql");
                if (count($total_docs) > 0) {
                    $empty_dirs++;
                }
                if (file_exists($basedir)) {
                    create_map_to_real_filename('/', false);
                    $topdir = $basedir;

                    // Create recursive directory iterator
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($topdir),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    $dir = preg_replace('/[\/]{2,}/', '/', $s->title ."/");
                    $zipFile->addEmptyDir($dir);
                    foreach ($files as $name => $file) {
                        // Get real and filename to be added for current file
                        $filePath = fix_directory_separator($file->getRealPath());
                        $relativePath = substr($filePath, strlen($basedir));

                        // Skip directories (they will be added automatically)
                        if (!$file->isDir()) {
                            // Add current file to archive
                            $zipFile->addFile($filePath, $s->title . '/' . substr($map_filenames[$relativePath], 1));
                        }
                    }
                }
            }

            if (!$zipFile->close()) {
                die("Error while creating ZIP file!");
            }

            if($empty_dirs > 0){
                send_file_to_client($dload_filename, $real_filename, null, true, true);
                exit;
            }else{
                Session::flash('message',$langNotExistFilesForUser . ' ' . '<strong>' . uid_to_name($userid) . '</strong>');
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page('modules/session/consulting_completion_consultant.php?course=' . $course_code . $statusComplete);
            }

        }
    }else{
        Session::flash('message',$langChooseUser);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/session/consulting_completion_consultant.php?course=' . $course_code . $statusComplete);
    }
}

$sql_users = "";
$consultant_as_tutor_group = Database::get()->queryArray("SELECT * FROM group_members WHERE user_id = ?d AND is_tutor = ?d", $uid, 1);
if(count($consultant_as_tutor_group) > 0) {
    $sql_users = "
        INNER JOIN group_members gm_user
            ON gm_user.user_id = cu.user_id

        INNER JOIN group_members gm_tutor
            ON gm_tutor.group_id = gm_user.group_id
        AND gm_tutor.user_id = ?d
        AND gm_tutor.is_tutor = 1
    ";
    $sql_users_args = [$uid];
}

if (!empty($sql_users)) {
    $query_vars = [$sql_users_args, $course_id, USER_STUDENT, 0 , 0, 0];
} else {
    $query_vars = [$course_id, USER_STUDENT, 0, 0, 0];
}

$course_users = Database::get()->queryArray("
    SELECT DISTINCT cu.user_id
    FROM course_user cu

    INNER JOIN mod_session_users msu
        ON msu.participants = cu.user_id
       AND msu.is_accepted = 1

    $sql_users

    WHERE cu.course_id = ?d
      AND cu.status = ?d
      AND cu.tutor = ?d
      AND cu.editor = ?d
      AND cu.course_reviewer = ?d
", $query_vars);

if (!empty($forUser) && $forUserIn != 1) {
    $q_vars = [$course_id, USER_STUDENT, 0, 0, 0, $forUserArgs];
} else {
    $q_vars = [$course_id, USER_STUDENT, 0, 0, 0];
}

$res = Database::get()->queryFunc("SELECT user_id FROM course_user 
                                   WHERE course_id = ?d 
                                   AND status = ?d 
                                   AND tutor = ?d 
                                   AND editor = ?d 
                                   AND course_reviewer = ?d
                                   $forUser", function($result) use(&$course_id, &$users_actions, &$langSessionCondition, &$langUserHasCompletedCriteria, &$langUserHasNotCompletedCriteria, &$langPercentageSessionCompletion, &$langAllCompletedResources, &$sqlSelectConsultantArgs, &$uid)  {

                                        $userID = $result->user_id;
                                        if(isset($_GET['user_rep']) && !isset($_GET['user_consultant_report'])){
                                            $userID = $_GET['user_rep'];
                                        }

                                        $user_badge_sessions = Database::get()->queryArray("
                                            SELECT DISTINCT
                                                s.id,
                                                s.title,
                                                s.start,
                                                s.finish,
                                                s.creator,
                                                su.percentage
                                            FROM mod_session s
                                            INNER JOIN mod_session_users su
                                                ON su.session_id = s.id
                                                AND su.participants = ?d
                                                AND su.is_accepted = ?d
                                            INNER JOIN badge b
                                                ON b.session_id = s.id
                                                AND b.course_id = ?d
                                                AND b.session_id > 0
                                            WHERE s.course_id = ?d
                                            AND s.visible = ?d
                                            AND s.creator = ?d
                                        ", $userID, 1, $course_id, $course_id, 1, $uid);

                                        if (count($user_badge_sessions) > 0) {
                                            $users_actions[$result->user_id] = $user_badge_sessions;
                                        }

                                 }, $q_vars);
 
// Display users reports in a table
$tool_content .= "
    <div class='col-12'>";
        if(count($users_actions) > 0){
 $tool_content .= " <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                            <h3 class='title_reports mb-0'>$langUserReferences</h3>
                        </div>
                        <div class='card-body'>
                            <div class='d-flex justify-content-start align-items-center gap-3 flex-wrap'>";
                            if (isset($_GET['status']) && $_GET['status'] == 'complete') {
            $tool_content .= "  
                                    <a class='btn submitAdminBtn export-pdf-btn gap-1 mb-3' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;format=pdf$user_pdf$statusComplete' target='_blank' aria-label='$langOpenNewTab'>
                                        <i class='fa-solid fa-file-pdf'></i>
                                        $langDumpPDF
                                    </a>
                                    <a class='btn submitAdminBtn docs-pdf-btn gap-1 mb-3' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;user_docs=$user_selected$statusComplete'>
                                        <i class='fa-solid fa-download'></i>
                                        $langDocsUser
                                    </a>
                                ";
                            } else {
            $tool_content .= "
                                    <div>
                                        <a class='btn submitAdminBtnDefault link-Update-Percentage gap-1 mb-3' href='{$urlAppend}modules/session/update_percentage.php?course=$course_code&amp;update_percentage=true'>
                                            <i class='fa-solid fa-arrow-rotate-right'></i>
                                            $langUpdatePercentage
                                        </a>

                                        <div class='d-flex align-items-start gap-2 show-calculation-message d-none mb-3'>
                                            <div class='spinner-border text-warning' role='status' style='width:20px; height:20px;'>
                                                <span class='visually-hidden'></span>
                                            </div>
                                            $langPlsWait
                                        </div>
                                    </div>";
                            }
         $tool_content .= " </div>
                            <p class='info_completion' style='margin-bottom:25px;'>$langShowOnlySessionWithCompletionEnable</p>";
                            if(count($course_users) > 0 && !isset($_GET['user_rep'])){
                              $tool_content .= "<div class='col-12 mb-4'>
                                                    <form class='form-user-report' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code$statusComplete'>
                                                        <div class='d-flex justify-content-start align-items-center gap-2'>";
                                         $tool_content .= " <div class='search-user-container'>
                                                                <label for='form_id_user_report' class='control-label-notes mb-1'>$langSearchUser</label>
                                                                <select class='form-select mt-0' name='form_user_report' aria-label='$langSelect' id='form_id_user_report'>
                                                                    <option value='0'>$langAllUsers</option>";
                                                foreach($course_users as $u){
                                                    $is_selected = ($user_selected == $u->user_id) ? 'selected' : '';
                                                    $tool_content .= "<option value='{$u->user_id}' $is_selected>" . participant_name($u->user_id) . "</option>";
                                                }
                                                $tool_content .= "</select>
                                                            </div>";

                                        $tool_content .= "  <button type='submit' class='btn searchGroupBtn mt-4' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langSearch' aria-label='$langSearch'>
                                                                <i class='fa-solid fa-search'></i>
                                                            </button>

                                                        </div>
                                                    </form>
                                                </div>";
                            }

                            foreach($users_actions as $key => $val){
                $tool_content .= "<div class='card cardReports' style='margin-bottom:25px;'>
                                        <div class='d-flex justify-content-start align-items-center gap-3'>
                                            <img class='card-img-top' style='width:40px; height:40xp; object-fit:cover;' src='".user_icon($key, IMAGESIZE_LARGE)."' alt='$langUser:".participant_name($key)."'>
                                            <h3 class='mb-0'>" . participant_name($key) . "</h3>
                                        </div>
                                        <div class='card-body'>";
                $tool_content .= "  
                                            <table class='table-default'>
                                                <thead style='background-color: transparent;'>
                                                    <tr>
                                                        <th style='width:30%;'>$langSSession</th>
                                                        <th style='width:30%;'>$langConsultant</th>
                                                        <th style='width:40%;'>$langCompletionResources</th>
                                                    </tr>
                                                </thead>";
                                                foreach($val as $v){
                                                    if($v->percentage < 100) {
                                                        $icon_badge = " 
                                                                        <strong>
                                                                            $langPercentageSessionCompletion
                                                                        </strong>
                                                                        <ul>
                                                                            <li class='criteria_not_completed Accent-200-cl' style='font-style:normal; font-weight:600;'>
                                                                                $v->percentage%
                                                                            </li>
                                                                        </ul>";
                                                    } else {
                                                        $icon_badge = " 
                                                                        <strong>
                                                                            $langPercentageSessionCompletion
                                                                        </strong>
                                                                        <ul>
                                                                            <li class='criteria_completed Success-200-cl' style='font-style:normal; font-weight:600;'>
                                                                                $v->percentage%
                                                                            </li>
                                                                        </ul>";
                                                    }

                                $tool_content .= "  <tr style='border:0px !important;'>
                                                        <td style='vertical-align:top; border:0px !important; background-color: transparent;'>
                                                            <strong>{$v->title}</br>
                                                                " . format_locale_date(strtotime($v->start), 'short', false) . "<br><small>$langStartSession&nbsp;" . date('H:i', strtotime($v->start)) . "<br>$langFinishSession&nbsp;" . date('H:i', strtotime($v->finish)) . "</small>
                                                            </strong>
                                                        </td>
                                                        <td style='vertical-align:top; border:0px !important; background-color: transparent;'>" . participant_name($v->creator) . "</td>
                                                        <td style='vertical-align:top; border:0px !important; background-color: transparent;'>
                                                            <div> " . session_completed_resources_by_user($v->id, $course_id, $key) . " </div>
                                                            <div>$icon_badge</div>
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
    if (isset($_GET['user_rep'])) {
        $uId = intval($_GET['user_rep']);
    } else {
        $uId = 0;
    }
    pdf_reports_output($uId);
}else{
    $data['tool_content'] = $tool_content;
    view('modules.session.consulting_completion', $data);
}



/**
 * @brief output to pdf file
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_reports_output($uId) {
    global $tool_content, $langUserDuration, $currentCourseName,
           $webDir, $course_id, $course_code, $langHasParticipatedInTool, $langHasNotParticipatedInTool;

    $pdfTitle = ($uId > 0) ? '(Summary sessions) ' . uid_to_name($uId) : '(Summary sessions) all_users_reports';

    $htmlHeader = "
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
            h2 { font-size: 14pt; }
            h3 { font-size: 14pt; color: #000000; margin-top: 0px; margin-bottom:20px; background-color: #EFF6FF; padding: 10px; }
            h4 { font-size: 11pt; }
            th { text-align: left; border-bottom: 1px solid #999; }
            td { text-align: left; }
            .criteria_not_completed { 
                color: #FF0000; 
            }
            .criteria_completed { 
                color: #008000;
            }
            .text-success { 
                color: #228B22; 
            }
            .text-danger { 
                color: #D22B2B; 
            }
            .cardReports { 
                background: #ffffff; 
                padding: 15px; 
                border: solid 1px #989ea6; 
                margin-top:20px; 
            }
            .export-pdf-btn,
            .form-user-report,
            .docs-pdf-btn,
            .user-icon-filename,
            .info_completion,
            .title_reports,
            .card-img-top,
            .link-Update-Percentage,
            .resource_item {
                margin-bottom: 25px;
            }
          </style>
        </head>
        <body>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>";

    // Array contains icons
    $searchVal = array('&#10004;', '&#x2718;');
    // Array replaces icons with strings
    $replaceVal = array('<strong class="text-success">' . $langHasParticipatedInTool . '</strong>', '<strong class="text-danger">' . $langHasNotParticipatedInTool . '</strong>');
    $output = str_replace($searchVal, $replaceVal, $tool_content);

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $image_height_header = setting_get(SETTING_COURSE_IMAGE_PRINT_HEADER_WIDTH, $course_id);
    $image_height_footer = setting_get(SETTING_COURSE_IMAGE_PRINT_FOOTER_WIDTH, $course_id);
    $mpdf = new Mpdf\Mpdf([
        'margin_top' => $image_height_header+15,     // mm
        'margin_bottom' => $image_height_footer+15,  // mm
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

    // Performance options
    $mpdf->simpleTables = true;
    $mpdf->packTableData = true;
    $mpdf->shrink_tables_to_fit = 0;

    // Write CSS/header ONCE
    $mpdf->WriteHTML($htmlHeader, \Mpdf\HTMLParserMode::HEADER_CSS);
    // Header/Footer
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

    // Parse HTML safely
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $html = mb_convert_encoding(
        $output,
        'HTML-ENTITIES',
        'UTF-8'
    );
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Find all cards
    $cards = $xpath->query("
        //div[contains(concat(' ', normalize-space(@class), ' '), ' cardReports ')]
    ");
    if ($cards->length === 0) {
        $mpdf->WriteHTML($output, \Mpdf\HTMLParserMode::HTML_BODY);
    } else {
        foreach ($cards as $card) {
            $cardHtml = $dom->saveHTML($card);
            $mpdf->WriteHTML(
                $cardHtml,
                \Mpdf\HTMLParserMode::HTML_BODY
            );
            $mpdf->WriteHTML('<pagebreak />');
        }
    }

    // Close HTML
    $mpdf->WriteHTML("</body></html>");

    // Output
    $mpdf->Output("$course_code $pdfTitle.pdf", 'I');
    exit;
}

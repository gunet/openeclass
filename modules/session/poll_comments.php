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
 * @file poll_comments.php
 * @brief Sessions display module
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'functions.php';

check_activation_of_collaboration();

is_session_type_course();

if (isset($_GET['session'])) {
    $data['sessionID'] = $sessionID = $_GET['session'];
}
if (isset($_GET['pid'])) {
    $data['pid'] = $pid = $_GET['pid'];
}

$pageName = $langCommentsByConsultant;
$sessionTitle = title_session($course_id,$sessionID);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));


// Delete comment
if (isset($_POST['delete_comment'])) {
    Database::get()->query("DELETE FROM session_poll_comments WHERE id = ?d", $_POST['delete_comment']);
    Session::flash('message',$langDelConsultantComments);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/poll_comments.php?course=$course_code&session=$sessionID&pid=$pid");
}

// Add or modify comment
if (isset($_POST['add_comments'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    $v = new Valitron\Validator($_POST);
    $v->rule('required', 'title');
    $v->rule('required', 'comments');
    $v->rule('required', 'participants_u');
    $v->rule('min', 'participants_u', 1);
    $v->labels(array(
        'title' => "$langTheField $langTitle",
        'comments' => "$langTheField $langComments",
        'participants_u' => "$langTheField $langReferencedObject",
    ));

    if ($v->validate()) {
        $notify_comments = 0;
        $title = (isset($_POST['title'])) ? q($_POST['title']) : '';
        $comments = (isset($_POST['comments'])) ? purify($_POST['comments']) : '';
        $u_participant = (isset($_POST['participants_u']) && $_POST['participants_u'] > 0) ? $_POST['participants_u'] : 0;
        if (isset($_POST['notify_comments']) and $_POST['notify_comments'] == 'on') {
            $notify_comments = 1;
        }

        $c = Database::get()->querySingle("SELECT id FROM session_poll_comments 
                                            WHERE course_id = ?d AND session_id = ?d AND poll_id = ?d AND user_id = ?d", $course_id, $sessionID, $pid, $u_participant);
        if (!$c) {
            Database::get()->query("INSERT INTO session_poll_comments SET
                                    course_id = ?d, 
                                    session_id = ?d,
                                    poll_id = ?d,
                                    user_id = ?d, 
                                    title = ?s, 
                                    comments = ?s,
                                    notify_comments = ?d", $course_id, $sessionID, $pid, $u_participant, $title, $comments, $notify_comments);
        } else {
            Database::get()->query("UPDATE session_poll_comments SET
                                    course_id = ?d, 
                                    session_id = ?d,
                                    poll_id = ?d,
                                    user_id = ?d, 
                                    title = ?s,
                                    comments = ?s,
                                    notify_comments = ?d
                                    WHERE id = ?d", $course_id, $sessionID, $pid, $u_participant, $title, $comments, $notify_comments, $c->id);
        }

        if ($notify_comments == 1) {
            $course_title = course_id_to_title($course_id);
            $session_title = title_session($course_id,$sessionID);
            $poll_title = Database::get()->querySingle("SELECT `name` FROM poll WHERE pid = ?d AND course_id = ?d", $pid, $course_id)->name;
            $emailHeader = "
            <!-- Header Section -->
                    <div id='mail-header'>
                        <br>
                        <div>
                            <div id='header-title'><span>$course_title</span></div>
                        </div>
                    </div>";
    
            $emailMain = "
            <!-- Body Section -->
                <div id='mail-body'>
                    <br>
                    <div><strong>$session_title:</strong>($langCommentsByConsultant - $poll_title)</div>
                    <div id='mail-body-inner'>
                        <ul id='forum-category'>
                            <li>
                              <span><b>$langTitle: </b></span> 
                              <span>$title</span>
                            </li>
                            <li>
                              <span><b>$langComments: </b></span> 
                              <span>$comments</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <br>
                        <p>$langProblem</p><br>" . get_config('admin_name') . "
                        <ul id='forum-category'>
                            <li>$langManager: $siteName</li>
                            <li>$langTel: -</li>
                            <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                        </ul>
                    </div>
                </div>";
    
            $emailsubject = $siteName;
            $emailbody = $emailHeader.$emailMain;
            $emailPlainBody = html2text($emailbody);
              
            if (get_user_email_notification($u_participant)) {
                $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d", $u_participant)->email;
                send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);
            }
        }

        Session::flash('message',$langAddConsultantComments);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/session/poll_comments.php?course=$course_code&session=$sessionID&pid=$pid");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        if (!isset($_POST['modify_comment'])) {
            redirect_to_home_page("modules/session/poll_comments.php?course=$course_code&session=$sessionID&pid=$pid&add=1");
        } else {
            redirect_to_home_page("modules/session/poll_comments.php?course=$course_code&session=$sessionID&pid=$pid&add=1&modify=1&comment=$_POST[modify_comment]");
        }
        
    }
}


$data['participants'] = $participants = [];
$data['title'] = '';
$data['comments'] = '';
$data['user_u'] = 0;
$data['user_n'] = '';
$data['comment_id'] = 0;
$data['notify_com'] = 0;
if (isset($_GET['add']) or isset($_GET['modify'])) {

    // add comment
    if (!isset($_GET['modify'])) {
        $data['rich_text_editor_comments'] = rich_text_editor('comments', 5, 40, '');
        $data['participants'] = $participants = Database::get()->queryArray("SELECT user.givenname,user.surname,mod_session_users.participants FROM mod_session_users
                                                                             LEFT JOIN user ON mod_session_users.participants=user.id
                                                                             WHERE mod_session_users.session_id = ?d
                                                                             AND mod_session_users.is_accepted = ?d",$sessionID,1);
    } else { // edit comment
        if (isset($_GET['comment'])) {
            $comment = Database::get()->querySingle("SELECT * FROM session_poll_comments WHERE id = ?d", $_GET['comment']);
            if ($comment) {
                $data['rich_text_editor_comments'] = rich_text_editor('comments', 5, 40, $comment->comments);
                $data['comment_id'] = $comment->id;
                $data['title'] = $comment->title;
                $data['user_u'] = $comment->user_id;
                $info_u = Database::get()->querySingle("SELECT givenname,surname FROM user WHERE id = ?d", $comment->user_id);
                $data['user_n'] = $info_u->givenname . '&nbsp;' . $info_u->surname;
                $data['notify_com'] = $comment->notify_comments;
            }
        }
    }
    
}

// View comment
$user_comments = 0;
$data['html_comment'] = $html_comment = '';
$viewComment = '';
if (isset($_GET['view_comment'])) {
    $viewComment = "&amp;view_comment=$_GET[view_comment]";
    $info_comment = Database::get()->querySingle("SELECT * FROM session_poll_comments WHERE id = ?d", $_GET['view_comment']);
    $pollName = Database::get()->querySingle("SELECT `name` FROM poll WHERE pid = ?d AND course_id = ?d", $info_comment->poll_id, $info_comment->course_id)->name;
    if ($info_comment) {
        $info_u = Database::get()->querySingle("SELECT givenname,surname FROM user WHERE id = ?d", $info_comment->user_id);
        $user_comments = $info_u->givenname . '&nbsp;' . $info_u->surname;
        $data['html_comment'] = $html_comment = "
            <div class='col-12'>
                <div class='card panelCard card-default px-lg-4 py-lg-3'>
                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                        <h3> " . q($info_comment->title) . "</h3>
                    </div>
                    <div class='card-body'>
                        <p>" . purify($info_comment->comments) . "</p>
                    </div>
                    <div class='card-footer small-text border-0'>
                        <strong class='text-decoration-underline'>$langReferencedObject:</strong>&nbsp;&nbsp;<strong>$user_comments</strong>&nbsp;|&nbsp;($pollName)
                    </div>
                </div>
            </div>     
        ";
    }
}

if (!isset($_GET['view_comment'])) {
    $data['all_comments'] = $all_comments = Database::get()->queryArray("SELECT user.givenname,user.surname,session_poll_comments.id,session_poll_comments.course_id,session_poll_comments.session_id,session_poll_comments.user_id,session_poll_comments.poll_id,session_poll_comments.title,poll.pid,poll.name 
                                                                         FROM session_poll_comments 
                                                                         INNER JOIN user ON session_poll_comments.user_id=user.id
                                                                         INNER JOIN poll ON session_poll_comments.poll_id=poll.pid
                                                                         WHERE session_poll_comments.course_id = ?d 
                                                                         AND session_poll_comments.session_id = ?d
                                                                         AND session_poll_comments.poll_id = ?d", $course_id, $sessionID, $pid);
}

$urlBack = "";
if (!isset($_GET['add']) && !isset($_GET['modify']) && !isset($_GET['view_comment']) && !isset($_POST['add_comments'])) {
    $urlBack = $urlAppend ."modules/session/session_space.php?course=" . $course_code . "&amp;session=" . $sessionID;
} else {
     $urlBack = $_SERVER['SCRIPT_NAME'] ."?course=" . $course_code . "&amp;session=" . $sessionID . "&amp;pid=" . $_GET['pid'];
}
$data['action_bar'] = action_bar([
    [ 'title' => $langBack,
      'url' => $urlBack,
      'icon' => 'fa-reply',
      'button-class' => 'btn-success',
      'level' => 'primary-label' ],
    [ 'title' => $langDumpPDF,
      'url' => $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code . "&amp;session=" . $sessionID . "&amp;pid=" . $_GET['pid'] . "&amp;format=pdf" . $viewComment,
      'icon' => 'fa-solid fa-file-pdf',
      'button-class' => 'btn-success',
      'level' => 'primary-label',
      'show' => isset($_GET['view_comment'])],
    [ 'title' => $langAdd,
      'url' => $_SERVER['SCRIPT_NAME'] .'?course=' . $course_code . '&amp;session=' . $sessionID . '&amp;pid=' . $_GET['pid'] . '&amp;add=1',
      'icon' => 'fa-circle-plus',
      'button-class' => 'btn-success',
      'level' => 'primary-label',
      'show' => (!isset($_GET['add']) && !isset($_GET['view_comment']) && $is_consultant)]
], false);


if (isset($_GET['format']) and $_GET['format'] == 'pdf') { // pdf format
    pdf_comment_output($sessionID,$html_comment);
} else {
    view('modules.session.poll_comments', $data);
}



/**
 * @brief output to pdf file for materials
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_comment_output($sid,$content_m) {
    global $currentCourseName, $webDir, $course_id, $course_code, $language;

    $sessionTitle = title_session($course_id,$sid);

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
            h3 { font-size: 10pt; color: #158; border-bottom: 1px solid #158; margin-top: 20px;}
          </style>
        </head>
        <body>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($sessionTitle) . "</h2>";

    $pdf_mcontent .= $content_m;

    $pdf_mcontent .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'margin_top' => 63,     // approx 200px
        'margin_bottom' => 63,  // approx 200px
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
    $mpdf->Output("$course_code user_comments.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}
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
 * @file messageList.php
 * @brief Functionality of chat module
 */

$require_current_course = TRUE;
$require_login = TRUE;
$require_user_registration = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/document/doc_init.php';

$coursePath = $webDir . '/courses/';
if (isset($_REQUEST['conference_id'])) {
    $conference_id = intval($_REQUEST['conference_id']);
}
if (!isset($conference_id) or !$conference_id) {
    forbidden();
}

$fileChatName = $coursePath . $course_code . '/' . $conference_id. '_chat.txt';
$tmpArchiveFile = $coursePath . $course_code . '/' . $conference_id. '_tmpChatArchive.txt';

$nick = uid_to_name($uid);

// How many lines to show on screen
define('MESSAGE_LINE_NB', 300);
// How many lines to keep in temporary archive
// (the rest are in the current chat file)
define('MAX_LINE_IN_FILE', 300);

if ($GLOBALS['language'] == 'el') {
    $timeNow = date("d-m-Y / H:i", time());
} else {
    $timeNow = date("Y-m-d / H:i", time());
}

if (!file_exists($fileChatName)) {
    $fp = fopen($fileChatName, 'w') or die('<center>$langChatError</center>');
    fclose($fp);
}

// chat commands
// reset command
if (isset($_GET['reset']) && $is_editor) {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    $fchat = fopen($fileChatName, 'w');

    if (flock($fchat, LOCK_EX)) {
        ftruncate($fchat, 0);
        fwrite($fchat, $timeNow . " ---- " . $langWashFrom . " ---- " . $nick . " -------- !@#$ systemMsgClear\n");
        fflush($fchat);
        flock($fchat, LOCK_UN);
    }
    fclose($fchat);
    @unlink($tmpArchiveFile);
    if (isset($_REQUEST['unit'])) {
        redirect_to_home_page("modules/units/view.php?course=$course_code&res_type=chat_actions&conference_id=$conference_id");
    } else {
        redirect_to_home_page("modules/chat/messageList.php?course=$course_code&conference_id=$conference_id");
    }
}

// store
if (isset($_GET['store']) && $is_editor) {
    doc_init();
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    $saveIn = "chat." . date("Y-m-j-his") . ".txt";
    $chat_filename = '/' . safe_filename('txt');

    //Concat temp & chat file removing system messages and html tags
    $exportFileChat = $coursePath . $course_code . '/'. $conference_id . '_chat_export.txt';
    $fp = fopen($exportFileChat, 'a+');
    $tmp_file = @file_get_contents($tmpArchiveFile);
    $chat_file = @file_get_contents($fileChatName);
    $con_file = preg_replace(array('/^(.*?)!@#\$ systemMsg.*\n/m','/!@#\$.*/'), '', strip_tags($tmp_file.$chat_file));

    fwrite($fp, $con_file);
    fclose($fp);

    if (copy($exportFileChat, $basedir . $chat_filename)) {
        Database::get()->query("INSERT INTO document SET
                            course_id = ?d,
                            subsystem = ?d,
                            path = ?s,
                            filename = ?s,
                            format='txt',
                            date = NOW(),
                            date_modified = NOW()", $course_id, $subsystem, $chat_filename, $saveIn);
        $fchat = fopen($fileChatName, 'a');
        fwrite($fchat, $timeNow." ---- ".$langSaveMessage . " ---- !@#$ systemMsgSave\n");
        fclose($fchat);
        @unlink($exportFileChat);
    }

    if (isset($_REQUEST['unit'])) {
        redirect_to_home_page("modules/units/view.php?course=$course_code&res_type=chat_actions&conference_id=$conference_id");
    } else {
        redirect_to_home_page("modules/chat/messageList.php?course=$course_code&conference_id=$conference_id");
    }
}

// add new line
    if (isset($_POST['chatLine']) and trim($_POST['chatLine']) != '') {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $chatLine = purify($_POST['chatLine']);
        $fchat = fopen($fileChatName, 'a');
        if ($is_editor) {
            $nick = "<b>".q($nick)."</b>";
        }
        fwrite($fchat, $timeNow . ' - ' . $nick . ' : ' . stripslashes($chatLine) . " !@#$ $uid       \n");
        fclose($fchat);
        if (isset($_REQUEST['unit'])) {
            redirect_to_home_page("modules/units/view.php?course=$course_code&res_type=chat_actions&conference_id=$conference_id");
        } else {
            redirect_to_home_page("modules/chat/messageList.php?course=$course_code&conference_id=$conference_id");
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <base target="_parent">
    <meta http-equiv="refresh" content="30; url=<?php
        echo "{$urlServer}modules/units/view.php?course=$course_code&res_type=chat_actions&conference_id=$conference_id";
    ?>" />
    <?php /*print_r($_REQUEST)*/?>
    <title><?php echo $langMessages ?></title>
    <!-- jQuery -->
    <script src="<?php echo $urlServer;?>js/jquery-<?php echo JQUERY_VERSION; ?>.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="<?php echo $urlServer;?>template/default/js/bootstrap.min.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="<?php echo $urlServer;?>template/default/CSS/bootstrap-custom.css">

    <style type="text/css">
        span { color: #727266; font-size: 11px; }
        div { font-size: 12px; }
        body { font-family: Verdana, Arial, Helvetica, sans-serif; }
    </style>
</head>
<body style='background: white;'>
<?php
    // display message list
    $fileContent = file($fileChatName);
    $FileNbLine = count($fileContent);
    $lineToRemove = $FileNbLine - MESSAGE_LINE_NB;
    if ($lineToRemove < 0) {
        $lineToRemove = 0;
    }
    $tmp = array_splice($fileContent, 0, $lineToRemove);

    $fileReverse = array_reverse($fileContent);
    foreach ($fileReverse as $thisLine) {
        $thisLine = preg_replace_callback('/\[m\].*?\[\/m\]/s', 'math_unescape', $thisLine);
        $newline = mathfilter($thisLine, 12, '../../courses/mathimg/');
        $str_1 = explode(' !@#$ ', $newline);
        //New message system (3.0 generated conferences)
        if (isset($str_1[1])) {
            if (trim($str_1[1]) == "systemMsgClear" || trim($str_1[1]) == "systemMsgSave") {
                if (trim($str_1[1]) == "systemMsgClear"){
                    $class = 'alert-success';
                } else {
                    $class = 'alert-info';
                }
                echo "<div class='row margin-right-thin margin-left-thin margin-top-thin'>
                            <div class='col-xs-12'>
                                <div class='alert $class text-center'>
                                    $str_1[0]
                                </div>
                            </div>
                      </div>\n";
            } else {
                $user_id = intval(trim($str_1[1]));
                $str_2 = explode(' - ', $str_1[0], 2);
                $datetime = $str_2[0];
                $str_3 = explode(' : ', $str_2[1]);
                $username = $str_3[0];
                if (isset($str_3[1])) {
                    $usertext = $str_3[1];
                } else {
                    $usertext = '';
                }
                $token = token_generate($user_id, true);
                echo "<div class='row margin-right-thin margin-left-thin margin-top-thin'>
                            <div class='col-xs-12'>
                                <div class='media'>
                                    <a class='media-left' href='{$urlServer}main/profile/display_profile.php?id=$user_id&amp;token=$token'>
                                        ". profile_image($user_id, IMAGESIZE_SMALL) ."
                                    </a>
                                    <div class='media-body bubble'>
                                        <div class='label label-success media-heading'>$datetime</div>
                                        <small>$langBlogPostUser ". display_user($user_id, false, false) ."</small>
                                        <div class='margin-top-thin'>
                                            " . $usertext . "
                                        </div>
                                    </div>
                                </div>
                            </div>
                      </div>\n";
            }
        } else { //prior to version 3.0 generated conferences
                echo "<div class='row margin-right-thin margin-left-thin margin-top-thin'>
                            <div class='col-xs-12'>
                                <div class='alert alert-default'>
                                    $str_1[0]
                                </div>
                            </div>
                      </div>\n";
        }
    }
    echo "</body></html>\n";


    /*
     * For performance reason, buffer the content
     * in a temporary archive file
     * once the chat file is too large
     */

    if ($FileNbLine > MAX_LINE_IN_FILE) {
        buffer(implode('', $tmp), $tmpArchiveFile);
        // clean the original file
        $fp = fopen($fileChatName, "w");
        fwrite($fp, implode('', $fileContent));
    }

    function buffer($content, $tmpFile) {
        $fp = fopen($tmpFile, "a");
        fwrite($fp, $content);
    }

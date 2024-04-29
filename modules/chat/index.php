<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

$require_current_course = TRUE;
$require_login = TRUE;
$require_user_registration = TRUE;
$require_help = true;
$helpTopic = 'chat';
require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'include/log.class.php';
$coursePath = $webDir . '/courses/';

$toolName = $langChat;
$display = TRUE;

load_js('tools.js');
load_js('validation.js');
load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#select-chatusers').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-chatusers').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-chatusers').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-chatusers').val(stringVal).trigger('change');
        });
    });
</script>";

$available_themes = active_subdirs("$webDir/template", 'theme.html');

if ($is_editor) {
    if (isset($_GET['visible'])) {
        if ($_GET['visible'] == 1) {
            $status = 'active';
        } else {
            $status = 'inactive';
        }
        $conf_id = $_GET['id'];
        $conf_title = Database::get()->querySingle("SELECT conf_title FROM conference WHERE conf_id = ?d", $conf_id)->conf_title;
        Database::get()->querySingle("UPDATE conference SET status = ?s WHERE conf_id =?d",  $status, $conf_id);
        Log::record($course_id, MODULE_ID_CHAT,LOG_MODIFY, array('id' => $conf_id,
                                                                                     'title' => $conf_title,
                                                                                     'status' => $status));
    }
    if (isset($_GET['add_conference'])) {
        $display = FALSE;
        $pageName = $langAdd;
        $textarea = rich_text_editor('description', 4, 20, '');

        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "index.php",
                'icon' => 'fa-reply',
                'level' => 'primary')));

        $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='confForm' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='title' class='col-sm-6 control-label-notes'>$langTitle</label>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "<input class='form-control' type='text' name='title' id='title' placeholder='$langTitle' size='50' />";
        $tool_content .= "</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='description' class='col-sm-6 control-label-notes'>$langDescription</label>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "$textarea";
        $tool_content .= "</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                <div class='checkbox'>
                    <label class='label-container'>
                        <input type='checkbox' name='status' checked>
                        <span class='checkmark'></span> 
                        $langViewShow
                    </label>
                </div>
            </div>
        </div>";
        if ($colmoocapp->isEnabled()) {
            $tool_content .= "<div class='form-group mt-4'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <div class='checkbox'>
                        <label class='label-container'>
                            <input type='checkbox' name='chat_activity' >
                            <span class='checkmark'></span> 
                            $langChatActivity
                        </label>
                    </div>
                </div>
            </div>";
        }

        $tool_content .= "<div class='form-group mt-4'><label for='Email' class='col-sm-offset-2 col-sm-12 control-panel control-labe-notes'>$langChatToSpecUsers</label></div>
            <div class='form-group mt-4'>
                <div class='col-sm-12'>
                    <select class='form-select' name='chat_users[]' multiple class='form-control' id='select-chatusers'>";
            $chat_users = Database::get()->queryArray("SELECT cu.user_id, CONCAT(u.surname, ' ', u.givenname) name, u.username
                                                        FROM course_user cu
                                                            JOIN user u ON cu.user_id=u.id
                                                        WHERE cu.course_id = ?d
                                                        ORDER BY u.surname, u.givenname", $course_id);

            $tool_content .= "<option value='0' selected><h2>$langAllUsers</h2></option>";
            foreach($chat_users as $cu) {
                $tool_content .= "<option value='" . q($cu->user_id) . "'>" . q($cu->name) . " (" . q($cu->username) . ")</option>";
            }
            $tool_content .= "</select>
                    <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                </div>
            </div>";

        $tool_content .= "<div class='col-12 mt-5 d-flex justify-content-end align-items-center'><input class='btn submitAdminBtn' type='submit' name='submit' value='$langAddModify'></div>";
        $tool_content .= "</form></div></div><div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
                            </div>
                        </div>";
        $tool_content .='<script type="text/javascript">
            //<![CDATA[
                var chkValidator  = new Validator("confForm");
                chkValidator.addValidation("title","req","'.$langChatTitleError.'");
            //]]></script>';

    } else if (isset($_GET['delete_conference'])) {
        $id = $_GET['delete_conference'];
        $conf_title = Database::get()->querySingle("SELECT conf_title FROM conference WHERE conf_id = ?d", $id)->conf_title;
        Database::get()->querySingle("DELETE FROM conference WHERE conf_id=?d", $id);
        Log::record($course_id, MODULE_ID_CHAT, LOG_DELETE, array('id' => $id,
                                                                                      'title' => $conf_title));
        $fileChatName = $coursePath . $course_code . '/'.$id.'_chat.txt';
        $tmpArchiveFile = $coursePath . $course_code . '/'.$id.'_tmpChatArchive.txt';

        if(file_exists($fileChatName))
           unlink($fileChatName);
        if(file_exists($tmpArchiveFile))
            unlink($tmpArchiveFile);

        Session::flash('message', $langChatDeleted);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/chat/index.php");

    } else if (isset($_POST['submit'])) {
        $chat_user_id = $chat_group_id = 0; // default value
        $title = $_POST['title'];
        $description = $_POST['description'];
        if (isset($_POST['chat_users']) and count($_POST['chat_users']) > 0) {
            $chat_user_id = '';
            foreach ($_POST['chat_users'] as $chatusers) {
                $chat_user_id .= "$chatusers" . ",";
            }
            $chat_user_id = mb_substr($chat_user_id, 0, -1);
        }
        if (isset($_POST['status'])) {
            $status = 'active';
        } else {
            $status = 'inactive';
        }
        if (isset($_POST['chat_activity'])) {
            $chat_activity = true;
        } else {
            $chat_activity = false;
        }
        $skipFlash = false;
        if (isset($_POST['conference_id'])) {
            $conf_id = $_POST['conference_id'];
            Database::get()->querySingle("UPDATE conference SET conf_title= ?s,conf_description = ?s, status = ?s, chat_activity = ?b, user_id = ?s, group_id = ?s
                                            WHERE conf_id =?d", $title, $description, $status, $chat_activity, $chat_user_id, $chat_group_id, $conf_id);

            Log::record($course_id, MODULE_ID_CHAT, LOG_MODIFY, array('id' => $id,
                                                                                          'title' => $title,
                                                                                          'description' => $description));
            // handle chat that was colmooc false and became true
            if ($chat_activity) {
                $colmooc_activity_id = Database::get()->querySingle("SELECT * FROM conference WHERE conf_id = ?d", $conf_id)->chat_activity_id;
                if ($colmooc_activity_id == null) {
                    $colmooc_activity_id = colmooc_create_activity($conf_id, $title);
                    if ($colmooc_activity_id) {
                        Database::get()->querySingle("UPDATE conference SET chat_activity_id = ?d WHERE conf_id = ?d", $colmooc_activity_id, $conf_id);
                        Session::flash('message', $langQuotaSuccess . ". " . $langColMoocAgentNeeded);
                        Session::flash('alert-class', 'alert-success');
                        $skipFlash = true;
                    } else {
                        Database::get()->querySingle("UPDATE conference SET chat_activity = false WHERE conf_id = ?d", $conf_id);
                    }
                }
            }
        } else {
            $newChatId = Database::get()->query("INSERT INTO conference (course_id, conf_title, conf_description, status, chat_activity, user_id, group_id)
                      VALUES (?d, ?s, ?s, ?s, ?b, ?s, ?s)", $course_id, $title, $description, $status, $chat_activity, $chat_user_id, $chat_group_id)->lastInsertID;

            Log::record($course_id, MODULE_ID_CHAT,LOG_INSERT, array('id' => $newChatId,
                                                                                         'title' => $title,
                                                                                         'description' => $description));
            if ($chat_activity) {
                $colmooc_activity_id = colmooc_create_activity($newChatId, $title);
                if ($colmooc_activity_id) {
                    Database::get()->querySingle("UPDATE conference SET chat_activity_id = ?d WHERE conf_id = ?d", $colmooc_activity_id, $newChatId);
                    Session::flash('message', $langQuotaSuccess . ". " . $langColMoocAgentNeeded);
                    Session::flash('alert-class', 'alert-success');
                    $skipFlash = true;
                } else {
                    Database::get()->querySingle("UPDATE conference SET chat_activity = false WHERE conf_id = ?d", $newChatId);
                }
            }
        }
        // Display result message
        if (!$skipFlash) {
            Session::flash('message', $langQuotaSuccess);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page("modules/chat/index.php");
} elseif (isset($_GET['edit_conference'])) {
        $display = FALSE;
        $pageName = $langEdit;
        $conf_id = $_GET['edit_conference'];
        $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary')));

        $conf = Database::get()->querySingle("SELECT * FROM conference WHERE conf_id = ?d", $conf_id);
        $textarea = rich_text_editor('description', 4, 20, $conf->conf_description);

        $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
                            <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='confForm' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>";
        $tool_content .= "<fieldset>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='title' class='col-sm-6 control-label-notes'>$langTitle</label>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "<input class='form-control' type='text' name='title' id='title' value='$conf->conf_title' size='50' />";
        $tool_content .= "</div>";
        $tool_content .= "</div>";

        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='desc' class='col-sm-6 control-label-notes'>$langDescription</label>";
        $tool_content .= "<div class='col-sm-12'>";
        $tool_content .= "$textarea";
        $tool_content .= "</div>";
        $tool_content .= "</div>";

        $tool_content .= "<div class='form-group mt-4'><label for='Email' class='col-sm-offset-2 col-sm-10 control-panel control-label-notes'>$langChatToSpecUsers</label></div>
        <div class='form-group mt-4'>
            <div class='col-sm-12'>
                <select class='form-select' name='chat_users[]' multiple class='form-control' id='select-chatusers'>";

        if ($conf->user_id > 0) { // existing chat users (if exist)
            $existing_chat_users = explode(',', $conf->user_id);
            foreach ($existing_chat_users as $ecu) {
                $chat_users = Database::get()->querySingle("SELECT id, CONCAT(surname, ' ', givenname) AS name, username
                                                        FROM user WHERE id = $ecu
                                                        ORDER BY surname, givenname");

                $tool_content .= "<option value='" . q($chat_users->id) . "' selected>" . q($chat_users->name) . " (" . q($chat_users->username) . ")</option>";
            }
            $other_users = '';
            foreach ($existing_chat_users as $ecu) {
                $other_users .= "'" . $ecu . "',";
            }
            $other_users = mb_substr($other_users, 0, -1);
            $extra_sql = "AND cu.user_id NOT IN ($other_users)";
            $tool_content .= "<option value='0'><h2>$langAllUsers</h2></option>";
        } else {
            $extra_sql = "";
            $tool_content .= "<option value='0' selected><h2>$langAllUsers</h2></option>";
        }
        // remaining chat users
        $other_chat_users = Database::get()->queryArray("SELECT cu.user_id, CONCAT(u.surname, ' ', u.givenname) AS name, u.username
                                                    FROM course_user cu
                                                        JOIN user u ON cu.user_id=u.id
                                                    WHERE cu.course_id = ?d $extra_sql
                                                    ORDER BY u.surname, u.givenname", $course_id);

        foreach($other_chat_users as $cu) {
            $tool_content .= "<option value='" . q($cu->user_id) . "'>" . q($cu->name) . " (" . q($cu->username) . ")</option>";
        }
        $tool_content .= "</select>
                <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
            </div>
        </div>";

        $checked_status = ($conf->status == "active") ? 'checked' : '';
        $tool_content .= "<div class='form-group mt-4'>
            <div class='col-sm-10 col-sm-offset-2'>
                <div class='checkbox'>
                    <label class='label-container'>
                        <input type='checkbox' name='status' $checked_status>
                        <span class='checkmark'></span>
                        $langViewShow
                    </label>
                </div>
            </div>
        </div>";

        if ($colmoocapp->isEnabled()) {
            $activity_status = ($conf->chat_activity == true) ? 'checked' : '';
            $tool_content .= "<div class='form-group mt-4'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <div class='checkbox'>
                        <label class='label-container'>
                            <input type='checkbox' name='chat_activity' $activity_status>
                            <span class='checkmark'></span>
                            $langChatActivity
                        </label>
                    </div>
                </div>
            </div>";
        }

        $tool_content .= "<input type = 'hidden' name = 'conference_id' value='$conf_id'>";
        $tool_content .= "<div class='col-12 mt-5 d-flex justify-content-end align-items-center'><input class='btn submitAdminBtn' type='submit' name='submit' value='$langSubmit'></div>";
        $tool_content .= "</fieldset></form></div></div><div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
                            </div>
                        </div>";
        $tool_content .='<script language="javaScript" type="text/javascript">
                //<![CDATA[
                    var chkValidator  = new Validator("confForm");
                    chkValidator.addValidation("title","req","'.$langChatTitleError.'");
                //]]></script>';
        }
}
if ($display) {
    $q = array();
    if ($is_editor) {
        $tool_content .= action_bar(array(
            array('title' => $langAdd,
                'url' => "index.php?add_conference&amp;course=$course_code",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success')));

        $q = Database::get()->queryArray("SELECT * FROM conference WHERE course_id=?d ORDER BY conf_id DESC",$course_id);
    } else {
        $q = Database::get()->queryArray("SELECT * FROM conference WHERE course_id=?d AND status = 'active' 
            AND (chat_activity = false OR (chat_activity = true AND agent_created = TRUE AND chat_activity_id IS NOT NULL AND agent_id IS NOT NULL)) 
            ORDER BY conf_id DESC", $course_id);
    }
    if (count($q)>0) {
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th>$langChat</th>
                    <th width='150'>$langNewBBBSessionStatus</th>
                    <th width='200'>$langStartDate</th>";

        if($is_editor){
            $tool_content .= "<th>".icon('fa-gears')."</th>";
        }
        $tool_content .="</tr></thead>";
        foreach ($q as $conf) {

            $conf_details = '';

            // colmooc details
            if ($conf->chat_activity && $conf->agent_created && $conf->chat_activity_id && $conf->agent_id) {
                if ($is_editor) {
                    $compl_cnt = Database::get()->querySingle("select count(cus.id) as cnt from colmooc_user_session cus 
                        join conference c on (c.chat_activity_id = cus.activity_id) 
                        where c.course_id = ?d and c.conf_id = ?d and cus.session_status = 1", $course_id, $conf->conf_id)->cnt;
                    $conf_details .= "<br/><small>($langColMoocCompletions: $compl_cnt)</small>";
                } else {
                    $colmoocUserSession = Database::get()->querySingle("SELECT * FROM colmooc_user_session WHERE user_id = ?d AND activity_id = ?d", $uid, $conf->chat_activity_id);
                    if ($colmoocUserSession && $colmoocUserSession->session_status == 1) {
                        $conf_details .= "<br/><small>($langColMoocSessionStatusFinished <img src='$themeimg/tick.png'/>)</small>";
                    }
                }
            }

            $enabled_conference = ($conf->status == 'active')? "<span class='text-success'><span class='fa fa-eye'></span> $langVisible</span>" : "<span class='text-danger'><span class='fa fa-eye-slash'></span> $langInvisible</span>";
            ($conf->status == 'active')? $tool_content .= "<tr>" : $tool_content .= "<tr class='not_visible'>";
            $tool_content .= "<td>";
            if (is_valid_chat_user($uid, $conf->conf_id, $conf->status)) { // chat access control
                $tool_content .= "<a href='./chat.php?conference_id=$conf->conf_id&course=$course_code'>" . q($conf->conf_title) . "</a>";
            } else {
                $tool_content .= q($conf->conf_title);
            }
            $tool_content .= "<div>$conf->conf_description</div>" . $conf_details;
            $tool_content .= "</td>";
            $tool_content .= "<td>$enabled_conference</td>";
            $tool_content .= "<td>".format_locale_date(strtotime($conf->start), 'short')."</td>";
            if($is_editor) {
                $tool_content .= "<td class='option-btn-cell text-end'>".
                    action_button(array(
                        array('title' => $langEdit,
                              'url' => "$_SERVER[SCRIPT_NAME]?edit_conference=$conf->conf_id&amp;course=$course_code",
                              'icon' => 'fa-edit'),
                        array('title' => ($conf->status=='active') ? $langViewHide : $langViewShow,
                              'url' => "?course=$course_code&amp;id=$conf->conf_id" . (($conf->status == 'active') ? "&amp;visible=0" : "&amp;visible=1"),
                              'icon' => ($conf->status == 'active') ? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $langDelete,
                              'url' => "$_SERVER[SCRIPT_NAME]?delete_conference=$conf->conf_id",
                              'icon' => 'fa-xmark',
                              'class' => 'delete',
                              'confirm' => $langConfirmDelete)
                        )
                    )."</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
    } else {
         $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoChatAvailable</span></div></div>";
    }
}

draw($tool_content, 2, null, $head_content);

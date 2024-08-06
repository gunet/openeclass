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

/**
 * display available users in a session
 */

 function user_participant_name($u){
    $name = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$u);
    $surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$u);
    return $name->givenname . ' ' . $surname->surname;
}

function list_session_attendance($sid,$cid) {

    global $course_id, $course_code, $tool_content, $langChoice, 
            $langNotExistAttendanceCriterion, $langUser, $langSubmit, 
            $langChooseParticipatedUser, $langAddParticipationUser, $langSelect;

    // Firstly check if exists criterio for attendance completion.
    $cr_exists = Database::get()->querySingle("SELECT id FROM badge_criterion 
                                                WHERE badge IN (SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d)
                                                AND activity_type = ?s",$cid,$sid,'consultant-completion');

    if($cr_exists){
        $participants = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d",$sid,1);
        $all_users_badge = [];
        $users_badge = Database::get()->queryArray("SELECT user FROM user_badge_criterion WHERE badge_criterion = ?d",$cr_exists->id);
        if(count($users_badge) > 0){
            foreach($users_badge as $u){
                $all_users_badge[] = $u->user;
            }
        }
        if(count($participants) > 0){
            $tool_content .= "<div class='col-12'>
                <div class='alert alert-info'>
                    <i class='fa-solid fa-circle-info fa-lg'></i>
                    <span>$langChooseParticipatedUser</span>
                </div>
                <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&session=$sid'>
                <div class='table-responsive'>
                    <table class='table-default'>
                        <thead>
                            <tr>
                            <th>$langAddParticipationUser</th>
                            </tr>
                        </thead>
                        <tbody>";
            foreach($participants as $p){
                $selected = "";
                if(in_array($p->participants,$all_users_badge)){
                    $selected = "checked";
                }
                $tool_content .= " <tr>
                                        <td>
                                            <div class='d-flex justify-content-start align-items-center gap-2'>
                                                <label class='label-container' aria-label='$langSelect'>
                                                    <input name='submit_attendance[]' value='{$p->participants}' type='checkbox' $selected>
                                                    <span class='checkmark'></span>
                                                </label>
                                                " .  user_participant_name($p->participants) . "
                                            </div>
                                        </td>
                                    </tr>";
            }
            $tool_content .= "  <tr colspan='3'>
                                    <td>
                                        <input type='hidden' name='badgeCrId' value='{$cr_exists->id}'>
                                        <button type='submit' class='btn submitAdminBtn' name='submit_attend'>$langSubmit</submit>
                                    </td>
                                </tr>";
            $tool_content .= "</tbody></table></div></form></div>";
        }
    }else{
        $tool_content .= "<div class='col-12'>
                            <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>$langNotExistAttendanceCriterion</span>
                            </div>
                          </div>";
    }

    return $tool_content;
}


/**
 * @brief insert users in database
 * @param integer $sid
 */
function insert_session_attendance($sid) {
    global $course_id, $course_code, $langCompleteAttendUser;
    if(isset($_POST['submit_attendance'])){
        $old_users = array();
        $old_users_badge = Database::get()->queryArray("SELECT user FROM user_badge_criterion WHERE badge_criterion = ?d",$_POST['badgeCrId']);
        if(count($old_users_badge) > 0){
            foreach($old_users_badge as $u){
                $old_users[] = $u->user;
            }
        }
        $new_users = array();
        foreach ($_POST['submit_attendance'] as $u) {
            $new_users[] = $u;
        }
        $result=array_diff($old_users,$new_users);
        if(count($result) > 0){
            foreach ($result as $r) {
                Database::get()->query("DELETE FROM user_badge_criterion WHERE badge_criterion = ?d AND user = ?d",$_POST['badgeCrId'],$r);
            }
        }
        foreach ($_POST['submit_attendance'] as $u) {
            Database::get()->query("INSERT INTO user_badge_criterion SET 
                                            user = ?d,
                                            `created` = " . DBHelper::timeAfter() . ",
                                            badge_criterion = ?d", $u, $_POST['badgeCrId']);
        }
    }else{
        Database::get()->query("DELETE FROM user_badge_criterion WHERE badge_criterion = ?d",$_POST['badgeCrId']);
    }
    Session::flash('message',$langCompleteAttendUser);
    Session::flash('alert-class', 'alert-success');
    header('Location: session_space.php?course=' . $course_code . '&session=' . $sid);
    exit;
}

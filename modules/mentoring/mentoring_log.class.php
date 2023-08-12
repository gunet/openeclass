<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
 * @file mentoring_log.class.php
 * @author Yannis Exidaridis <jexi@noc.uoa.gr>
 * @brief defines class Log for logging actions
 */
define('MENTORING_LOG_INSERT', 1);
define('MENTORING_LOG_MODIFY', 2);
define('MENTORING_LOG_DELETE', 3);
define('MENTORING_LOG_CREATE_PROGRAM', 4);
define('MENTORING_LOG_DELETE_PROGRAM', 5);
define('MENTORING_LOG_MODIFY_PROGRAM', 6);

class Mentoring_Log {

    /**
     * record users actions
     * @param type $mentoring_program_id
     * @param type $module_id
     * @param type $action_type
     * @param type $details
     * @return none;
     */
    public static function record($mentoring_program_id, $module_id, $action_type, $details) {

        // check `config` values for logging
        if (get_config('disable_log_actions')) {
            return;
        } else {
            if (get_config('disable_log_system_actions') and $module_id == 0) {
                return;
            } elseif (get_config('disable_log_course_actions')) {
                return;
            }
        }

        if (!isset($_SESSION['uid'])) { // it is used only when logging login failures
            $userid = 0;
        } else {
            $userid = $_SESSION['uid']; // in all other cases
        }
        Database::get()->query("INSERT INTO mentoring_log SET
                                user_id = ?d,
                                mentoring_program_id = ?d,
                                module_id = ?d,
                                details = ?s,
                                action_type = ?d,
                                ts = " . DBHelper::timeAfter() . ",
                                ip = ?s", $userid, $mentoring_program_id, $module_id, serialize($details), $action_type, Mentoring_Log::get_client_ip());
        return;
    }

    /**
     * @brief display users logging
     * Note: $module_id = $mentoring_program_id = 0 means other logging (e.g. modify user profile, course creation etc.)
     * @param int $mentoring_program_id (-1 means all programs)
     * @param type $user_id (-1 means all users)
     * @param int $module_id (-1 means all modules)
     * @param type $logtype (-1 means logtypes)
     * @param type $date_from
     * @param type $date_now
     * @param type script_page
     * @return none
     */
    public function display($program_id, $user_id, $module_id, $logtype, $date_from, $date_now) {

        global $tool_content, $modules, $langToolManagement,
            $langNoUsersLog, $langDate, $langUser, $langAction, $langDetail, $langConfig,
            $langPorgram, $langModule, $langAdminUsers, $langExternalLinks, $langProgram,
            $langModifyInfo, $langAbuseReport, $langIpAddress,$is_admin,$uid;

        $q1 = $q2 = $q3 = $q4 = '';

        if ($user_id != -1) {
            $q1 = "AND user_id = $user_id"; // display specific user
        }

        if ($logtype > 0) {
            $q3 = "AND action_type = $logtype"; // specific programs logging
            if ($logtype > 3) { // specific system logging
                $module_id = 0;
            }
        } elseif ($logtype == -2) { // display all system logging
            $q2 = "AND module_id = 0";
            $q4 = "AND mentoring_program_id = 0";
        }

        if ($module_id > 0) {
            $q2 = "AND module_id = $module_id"; // display specific module
        } elseif ($module_id == -1) { // display all programs module logging
            $q2 = "AND module_id > 0"; // but exclude system logging
        }

        if ($program_id > 0) {
            $q4 = "AND mentoring_program_id = $program_id"; // display specific programs
        } elseif ($program_id == -1) { // display all programs logging
            $q4 = "AND mentoring_program_id > 0"; // but exclude system logging
        }elseif($program_id == 0){
            $q4 = "AND mentoring_program_id = 0"; // this is for deleted program and show it only admin
        }
        // count logs
        $num_of_logs = Database::get()->querySingle("SELECT COUNT(*) AS count FROM mentoring_log WHERE ts BETWEEN '$date_from' AND '$date_now' $q1 $q2 $q3 $q4")->count;
        // fetch logs
        $sql = Database::get()->queryArray("SELECT user_id, mentoring_program_id, module_id, details, action_type, ts, ip FROM mentoring_log
                                WHERE ts BETWEEN '$date_from' AND '$date_now'
                                $q1 $q2 $q3 $q4
                                ORDER BY ts DESC");

         
        // print_r("SELECT user_id, mentoring_program_id, module_id, details, action_type, ts, ip FROM mentoring_log
        // WHERE ts BETWEEN '$date_from' AND '$date_now'
        // $q1 $q2 $q3 $q4
        // ORDER BY ts DESC");     
                         
        if ($num_of_logs > 0 and (($logtype != MENTORING_LOG_DELETE_PROGRAM) or ($is_admin and $logtype == MENTORING_LOG_DELETE_PROGRAM))) {
            $tool_content .= "<table id = 'mentoring_log_results_table' class='table-default rounded-2'>";
            $tool_content .= "<thead>";
            // log header
            $tool_content .= "<tr class='list-header'>
                            <th>$langDate</th>
                            <th>$langUser</th>
                            <th>$langIpAddress</th>
                            ";
            if ($program_id == -1) {
                $tool_content .= "<th>$langProgram</th>";
            }
            if ($module_id == -1) {
                $tool_content .= "<th>$langModule</th>";
            }
            $tool_content .= "<th>$langAction</th><th>$langDetail</th>";
            $tool_content .= "</tr>";
            $tool_content .= "</thead>";
            $tool_content .= "<tbody>";
            // display logs
            foreach ($sql as $r) {
                $tool_content .= "<tr>";
                $tool_content .= "<td><span style='display:none;'>$r->ts</span>" . format_locale_date(strtotime($r->ts), 'short') . "<s/</td>";
                if (($r->user_id == 0)) { // login failures or delete user
                    $tool_content .= "<td>&nbsp;&nbsp;&mdash;&mdash;&mdash;</td>";
                } else {
                    $tool_content .= "<td class='pe-none'>" . display_user($r->user_id, false, false) . "</td>";
                }
                $tool_content .= "<td>" . $r->ip ."</td>";
                if ($program_id == -1) { // all PROGRAMS
                    $tool_content .= "<td>" .  q(show_mentoring_program_title_by_id($r->mentoring_program_id)) . "</td>";
                }
                if ($module_id == -1) { // all modules
                    $mid = $r->module_id;
                    if ($mid == MENTORING_MODULE_ID_PROGRAM) {
                        $tool_content .= "<td>" . $langModifyInfo . "</td>";
                    } else {
                        $tool_content .= "<td>" . $modules[$mid]['title'] . "</td>";
                    }
                }
                $tool_content .= "<td>" . $this->mentoring_get_action_names($r->action_type) . "</td>";
                if ($program_id == 0 or $module_id == 0) { // system logging
                    $tool_content .= "<td>" . $this->mentoring_other_action_details($r->action_type, $r->details) . "</td>";
                } else { // program logging
                    $tool_content .= "<td>" . $this->program_action_details($r->module_id, $r->details, $r->action_type) . "</td>";
                }
                $tool_content .= "</tr>";
            }
            $tool_content .= "</tbody>";
            $tool_content .= "</table>";
        } else {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoUsersLog</span></div></div>";
        }
        return $tool_content;
    }

    /**
     *
     * @global type $langUnknownModule
     * @param type $module_id
     * @param type $details
     * @return type
     * drive to appropriate subsystem for displaying details
     */
    public function program_action_details($module_id, $details, $type=null) {

        global $langUnknownModule;

        switch ($module_id) {
            case MENTORING_MODULE_ID_DOCS: $content = $this->mentoring_document_action_details($details);
                break;
            case MENTORING_MODULE_ID_GROUP: $content = $this->mentoring_group_action_details($details);
                break;
            case MENTORING_MODULE_ID_MEETING: $content = $this->mentoring_tc_action_details($details);
                break;
            case MENTORING_MODULE_ID_FORUM: $content = $this->mentoring_forum_action_details($details);
                break;
            case MENTORING_MODULE_ID_PROGRAM: $content = $this->mentoring_modify_program_action_details($details);
                break;
            case MENTORING_MODULE_ID_REQUESTS: $content = $this->mentoring_modify_requests_action_details($details);
                break;
            default: $content = $langUnknownModule;
                break;
        }
        return $content;
    }

    /**
     * @brief drive to appropriate subsystems for displaying results
     * @param type $logtype
     * @param type $details
     * @return \type
     */
    private function mentoring_other_action_details($logtype, $details) {

        global $langUnknownAction,$is_admin;

        switch ($logtype) {
            case MENTORING_LOG_CREATE_PROGRAM: $content = $this->mentoring_create_program_action_details($details);
                break;
            case MENTORING_LOG_MODIFY_PROGRAM: $content = $this->mentoring_modify_program_action_details($details);
                break;
            case MENTORING_LOG_DELETE_PROGRAM && $is_admin: $content = $this->mentoring_delete_program_action_details($details);
                break;
            default: $content = $langUnknownAction;
                break;
        }
        return $content;
    }

    /**
     * display action details while creating course
     * @global type $langTitle
     * @param type $details
     * @return string
     */
    private function mentoring_create_program_action_details($details) {

        global $langTitle;

        $details = unserialize($details);

        $content = "$langTitle &laquo;" . q($details['title']) . "&raquo;";
        $content .= "&nbsp;(" . q($details['code']) . ")";

        return $content;
    }

    /**
     * display action details while deleting course
     * @global type $langTitle
     * @param type $details
     * @return string
     */
    private function mentoring_delete_program_action_details($details) {

        global $langTitle,$is_admin,$uid;

        $details = unserialize($details);

        if($details['type'] == 'delete_program'){
            $content = "$langTitle &laquo;" . q($details['title']) . "&raquo;";
            $content .= "&nbsp;(" . q($details['code']) . ")";

            return $content;
        }
    }

    /**
     * @brief display action details while modifying course info
     * @param type $details
     * @return string
     */
    private function mentoring_modify_program_action_details($details) {

        global $langTitle, $langCode, $langMentoringMentorss,$lang_username, $langName;;

        $details = unserialize($details);

        if($details['type'] == 'modify_program'){
            $content = "$langTitle:&nbsp".$details['title'].",$langCode:&nbsp".$details['public_code'].",$langMentoringMentorss&nbsp";
            foreach($details['mentors'] as $mentor_id){
                $content .= "<div class='pe-none'>".display_user($mentor_id,false,false)."</div>";
            }
        }
        return $content;
    }

    /**
     * @brief display user profile actions details
     * @param $details
     * @return string
     */
    private function mentoring_profile_action_details($details) {

        global $lang_username, $langAm, $langChangePass, $langUpdateImage,
               $langType, $langDelImage, $langPersoDetails, $langActivate,
               $langDeactivate, $langOfNotifications, $langsOfCourse,
               $langFrom2, $langIn;

        $details = unserialize(($details));
        $content = '';

        if (!empty($details['modifyprofile'])) {
            $content .= "$langPersoDetails<br />$lang_username: ";
            if (!empty($details['old_username'])) {
                $content .= "$langFrom2 " . q($details['old_username']). " $langIn ";
            }
            $content .= q($details['username']);
            $content .= " Email: ";
            if (!empty($details['old_email'])) {
                $content .= "$langFrom2 " . q($details['old_email']) . " $langIn ";
            }
            $content .= q($details['email']);
            $content .= " $langAm: ";
            if (!empty($details['old_am'])) {
                $content .= " $langFrom2 " . q($details['old_am']) . " $langIn ";
            }
            if (!empty($details['am'])) {
                $content .= q($details['am']);
            }
        }
        if (!empty($details['pass_change'])) {
            $content .= "$langChangePass";
        }
        if (!empty($details['addimage'])) {
            $content .= "$langUpdateImage&nbsp;($langType: " . q($details['imagetype']) . ")";
        }
        if (!empty($details['deleteimage'])) {
            $content .= "$langDelImage";
        }
        if (isset($details['email_notifications'])) {
            if ($details['email_notifications'] == 1) {
                $content .= "$langActivate $langOfNotifications  <br />&nbsp;&laquo;$langsOfCourse " . $details['course_title'] . "&raquo;";
            } else {
                $content .= "$langDeactivate $langOfNotifications  <br />&nbsp;&laquo;$langsOfCourse " . $details['course_title'] . "&raquo;";
            }
        }

        return $content;
    }

    /**
     * display login failures details
     * @param type $details
     * @return type
     */
    private function mentoring_login_failure_action_details($details) {

        global $lang_username;

        $details = unserialize($details);

        $content = "$lang_username&nbsp;&laquo;" . q($details['uname']) . "&raquo;";

        return $content;
    }

    private function mentoring_delete_user_action_details($details) {
        global $lang_username, $langName;

        $details = unserialize($details);

        $content = "$lang_username&nbsp;&laquo;" . q($details['username']) . "&raquo;&nbsp;$langName&nbsp;&laquo;" . q($details['name']) . "&raquo;";

        return $content;
    }

    private function mentoring_register_user_action_details($details){
        global $lang_username, $langName;

        $details = unserialize($details);

        $content = "$lang_username&nbsp;&laquo;" . q($details['username']) . "&raquo;&nbsp;$langName&nbsp;&laquo;" . q($details['name']) . "&raquo;";

        return $content;
    }

    private function mentoring_modify_requests_action_details($details){
        global $langRequest,$langFrom,$langDenyRequest,$langRequestReset,$langAcceptRequest,$langDeleteRequest,$langUser,$langDelete,$langMemberOfProgram;

        $details = unserialize($details);
        if($details['type_request'] == 2){
            $content = "$langRequest&nbsp$langFrom&nbsp" . q($details['name']) ."&nbsp->&nbsp<span clas='text-warning'>$langDenyRequest</span>";
        }elseif($details['type_request'] == -1){
            $content = "$langRequest&nbsp$langFrom&nbsp" . q($details['name']) ."&nbsp->&nbsp<span class='text-danger'>$langDeleteRequest</span>";
        }elseif($details['type_request'] == 0){
            $content = "$langRequest&nbsp$langFrom&nbsp" . q($details['name']) ."&nbsp->&nbsp<span class='text-primary'>$langRequestReset</span>";
        }elseif($details['type_request'] == 1){
            $content = "$langRequest&nbsp$langFrom&nbsp" . q($details['name']) ."&nbsp->&nbsp<span class='text-success'>$langAcceptRequest&nbsp->&nbsp$langMemberOfProgram</span>";
        }elseif($details['type_request'] == -3){
            $content = "$langUser&nbsp" . q($details['name']) ."&nbsp->&nbsp$langDelete";
        }

        return $content;
    }

    /**
     * display action details in documents
     * @param type $details
     * @return string
     */
    private function mentoring_document_action_details($details) {

        global $langFileName, $langComments, $langTitle, $langRename, $langMove, $langTo, $langIn;

        $details = unserialize($details);

        $content = "$langFileName &laquo" . q($details['filename']) . "&raquo";
        
        if (!empty($details['title'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langTitle &laquo" . q($details['title']) . "&raquo";
        }
        if (!empty($details['comment'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langComments &laquo" . q($details['comment']) . "&raquo";
        }
        if (!empty($details['newfilename'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langRename $langIn &laquo" . q($details['newfilename']) . "&raquo";
        }
        if (!empty($details['newpath'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langMove $langTo &laquo" . q($details['newpath']) . "&raquo";
        }
        return $content;
    }


    /**
     * display action details in groups
     * @param type $details
     * @return string
     */
    private function mentoring_group_action_details($details) {

        global $langGroup, $langRegistration, $langInGroup, $langDelete, $langUser,$langCreate,
               $langActivate,$langDoc,$langDeactivate,$langForum,$langMentoringMentor,$langMentees;

        $details = unserialize($details);

        if($details['type'] == 'delete_group'){
            $content = "$langGroup:&nbsp" .$details['group_title']. "&nbsp->&nbsp$langDelete";
        }elseif($details['type'] == 'delete_mentee_from_group'){
            $content = "$langGroup:&nbsp" .$details['group_title']. "&nbsp->$langUser&nbsp-><div class='pe-none'>".$details['uid']."</div>&nbsp->&nbsp$langDelete";
        }elseif($details['type'] == 'insert_group'){
            $content = "$langGroup:&nbsp" .$details['group_title']. "&nbsp->&nbsp$langCreate";
        }elseif($details['type'] == 'group_properties'){
            $content = "$langGroup:&nbsp" .$details['group_title'];
            if($details['has_forum'] == 1){
                $content .= ",&nbsp$langForum->&nbsp$langActivate";
            }else{
                $content .= ",&nbsp$langForum->&nbsp$langDeactivate";
            }
            if($details['has_doc'] == 1){
                $content .= ",&nbsp$langDoc->&nbsp$langActivate";
            }else{
                $content .= ",&nbsp$langDoc->&nbsp$langDeactivate";
            }
        }elseif($details['type'] == 'insert_tutor_group'){
            $content = "$langGroup:&nbsp" .$details['group_title']. "&nbsp->$langMentoringMentor&nbsp->&nbsp<div class='pe-none'>".$details['uid']."</div>";
        }elseif($details['type'] == 'insert_mentee_group'){
            $content = "$langGroup:&nbsp" .$details['group_title']. "&nbsp->$langMentees&nbsp->&nbsp<div class='pe-none'>".$details['uid']."</div>";
        }

        return $content;
    }

    /**
     * display action details in course description
     * @param type $details
     * @return string
     */
    private function mentoring_description_action_details($details) {

        global $langTitle, $langContent;

        $details = unserialize($details);

        $content = "$langTitle  &laquo" . q($details['title']) . "&raquo";
        $content .= "&nbsp;&mdash;&nbsp; $langContent &laquo" . ellipsize($details['content'], 100) . "&raquo";

        return $content;
    }


    /**
     * @display action details in tc module
     * @param type $details
     * @return string
     */
    private function mentoring_tc_action_details($details) {

        global $langTitle, $langDescription, $langType, $langFrom, $langUntil;

        $d = unserialize($details);
        $content = '';
        if (isset($d['tc_type'])) {
            $content .= "$langType " . "&laquo" . $d['tc_type']. "&raquo&nbsp;&mdash;&nbsp;";
            $content .= "$langTitle " . "&laquo" . $d['title'] . "&raquo";
            $content .= "$langFrom " . "&laquo" . $d['from'] . "&raquo";
            $content .= "$langUntil " . "&laquo" . $d['until'] . "&raquo";
        }
       
       

        return $content;

    }

    /**
     * @display action details in tc module
     * @param type $details
     * @return string
     */
    private function mentoring_forum_action_details($details){
        global $langSubject,$langRename;
        
        $d = unserialize($details);

        if(!empty($d['old_title'])){
            $content = "$langSubject " . "&laquo" . $d['old_title']. "&raquo&nbsp;&mdash;&nbsp;" .$langRename."&nbsp&laquo". $d['title']."&raquo";
        }else{
            $content = "$langSubject " . "&laquo" . $d['title'];

        }

        return $content;
    }

    
    /**
     * @param type $action_type
     * @return string (real action names)
     */
    public function mentoring_get_action_names($action_type) {

        global $langInsert, $langModify, $langDelete,
        $langFinalizeProgram, $langProgramDel, $langModifyProgram, $langUnregUsers, $langUnknownAction;

        switch ($action_type) {
            case MENTORING_LOG_INSERT: return $langInsert;
            case MENTORING_LOG_MODIFY: return $langModify;
            case MENTORING_LOG_DELETE: return $langDelete;
            case MENTORING_LOG_CREATE_PROGRAM: return $langFinalizeProgram;
            case MENTORING_LOG_DELETE_PROGRAM: return $langProgramDel;
            case MENTORING_LOG_MODIFY_PROGRAM: return $langModifyProgram;
            default: return $langUnknownAction;
        }
    }

    /**
     * Retrieve the best guess of the client's actual IP address.
     *
     * http://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
     *
     * @return string IP address
     */
    public static function get_client_ip() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return '0.0.0.0';
        }
    }

}

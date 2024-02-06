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
 * @file log.class.php
 * @author Yannis Exidaridis <jexi@noc.uoa.gr>
 * @brief defines class Log for logging actions
 */
define('LOG_INSERT', 1);
define('LOG_MODIFY', 2);
define('LOG_DELETE', 3);
define('LOG_PROFILE', 4);
define('LOG_CREATE_COURSE', 5);
define('LOG_DELETE_COURSE', 6);
define('LOG_MODIFY_COURSE', 7);
define('LOG_LOGIN_FAILURE', 8);
define('LOG_LOGIN_DOUBLE', 10);
define('LOG_DELETE_USER', 9);

class Log {

    /**
     * record users actions
     * @param type $course_id
     * @param type $module_id
     * @param type $action_type
     * @param type $details
     * @return void;
     */
    public static function record($course_id, $module_id, $action_type, $details) {

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
        Database::get()->query("INSERT INTO log SET
                                user_id = ?d,
                                course_id = ?d,
                                module_id = ?d,
                                details = ?s,
                                action_type = ?d,
                                ts = " . DBHelper::timeAfter() . ",
                                ip = ?s", $userid, $course_id, $module_id, serialize($details), $action_type, Log::get_client_ip());
        return;
    }

    /**
     * @brief display users logging
     * Note: $module_id = $course_id = 0 means other logging (e.g. modify user profile, course creation etc.)
     * @param int $course_id (-1 means all courses)
     * @param type $user_id (-1 means all users)
     * @param int $module_id (-1 means all modules)
     * @param type $logtype (-1 means logtypes)
     * @param type $date_from
     * @param type $date_now
     * @param type $script_page script_page
     * @return void
     */
    public function display($course_id, $user_id, $module_id, $logtype, $date_from, $date_now, $script_page) {

        global $tool_content, $modules, $langToolManagement,
            $langNoUsersLog, $langDate, $langUser, $langAction, $langDetail, $langConfig,
            $langCourse, $langModule, $langAdminUsers, $langExternalLinks, $langCourseInfo,
            $langModifyInfo, $langAbuseReport, $langIpAddress;

        $q1 = $q2 = $q3 = $q4 = '';

        if ($user_id != -1) {
            $q1 = "AND user_id = $user_id"; // display specific user
        }

        if ($logtype > 0) {
            $q3 = "AND action_type = $logtype"; // specific course logging
            if ($logtype > 3) { // specific system logging
                $module_id = $course_id = 0;
            }
        } elseif ($logtype == -2) { // display all system logging
            $q2 = "AND module_id = 0";
            $q4 = "AND course_id = 0";
        }

        if ($module_id > 0) {
            $q2 = "AND module_id = $module_id"; // display specific module
        } elseif ($module_id == -1) { // display all course module logging
            $q2 = "AND module_id > 0"; // but exclude system logging
        }

        if ($course_id > 0) {
            $q4 = "AND course_id = $course_id"; // display specific course
        } elseif ($course_id == -1) { // display all course logging
            $q4 = "AND course_id > 0"; // but exclude system logging
        }
        // count logs
        $num_of_logs = Database::get()->querySingle("SELECT COUNT(*) AS count FROM log WHERE ts BETWEEN '$date_from' AND '$date_now' $q1 $q2 $q3 $q4")->count;
        // fetch logs
        $sql = Database::get()->queryArray("SELECT user_id, course_id, module_id, details, action_type, ts, ip FROM log
                                WHERE ts BETWEEN '$date_from' AND '$date_now'
                                $q1 $q2 $q3 $q4
                                ORDER BY ts DESC");
        if ($num_of_logs > 0) {
            if ($course_id > 0) {
                $tool_content .= "<div class='alert alert-info'>$langCourse: " . q(course_id_to_title($course_id)) . "</div>";
            }
            if ($module_id > 0) {
                if ($module_id == MODULE_ID_USERS) {
                    $tool_content .= "<div class='alert alert-info'>$langModule: " . $langAdminUsers . "</div>";
                } elseif ($module_id == MODULE_ID_TOOLADMIN) {
                    $tool_content .= "<div class='alert alert-info'>$langModule: " . $langExternalLinks . "</div>";
                } elseif ($module_id == MODULE_ID_ABUSE_REPORT) {
                    $tool_content .= "<div class='alert alert-info'>$langModule: " . $langAbuseReport . "</div>";
                } elseif ($module_id == MODULE_ID_COURSEINFO) {
                    $tool_content .= "<div class='alert alert-info'>$langConfig</div>";
                } else {
                    $tool_content .= "<div class='alert alert-info'>$langModule: " . $modules[$module_id]['title'] . "</div>";
                }
            }
            $tool_content .= "<table id = 'log_results_table' class='table-default'>";
            $tool_content .= "<thead>";
            // log header
            $tool_content .= "<tr class='list-header'>
                            <th>$langDate</th>
                            <th>$langUser</th>
                            <th>$langIpAddress</th>
                            ";
            if ($course_id == -1) {
                $tool_content .= "<th>$langCourse</th>";
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
                if (($r->user_id == 0) or ($logtype == LOG_DELETE_USER)) { // login failures or delete user
                    $tool_content .= "<td>&nbsp;&nbsp;&mdash;&mdash;&mdash;</td>";
                } else {
                    $tool_content .= "<td>" . display_user($r->user_id, false, false) . "</td>";
                }
                $tool_content .= "<td>" . $r->ip ."</td>";
                if ($course_id == -1) { // all courses
                    $tool_content .= "<td>" .  q(course_id_to_title($r->course_id)) . "</td>";
                }
                if ($module_id == -1) { // all modules
                    $mid = $r->module_id;
                    if ($mid == MODULE_ID_USERS) {
                        $tool_content .= "<td>" . $langAdminUsers . "</td>";
                    } elseif ($mid == MODULE_ID_TOOLADMIN) {
                        $tool_content .= "<td>$langToolManagement / $langExternalLinks</td>";
                    } elseif ($mid == MODULE_ID_SETTINGS) {
                        $tool_content .= "<td>" . $langCourseInfo . "</td>";
                    } elseif ($mid == MODULE_ID_ABUSE_REPORT) {
                        $tool_content .= "<td>" . $langAbuseReport . "</td>";
                    } elseif ($mid == MODULE_ID_COURSEINFO) {
                        $tool_content .= "<td>" . $langModifyInfo . "</td>";
                    } else {
                        $tool_content .= "<td>" . $modules[$mid]['title'] . "</td>";
                    }
                }
                $tool_content .= "<td>" . $this->get_action_names($r->action_type) . "</td>";
                if ($course_id == 0 or $module_id == 0) { // system logging
                    $tool_content .= "<td>" . $this->other_action_details($r->action_type, $r->details) . "</td>";
                } else { // course logging
                    $tool_content .= "<td>" . $this->course_action_details($r->module_id, $r->details, $r->action_type) . "</td>";
                }
                $tool_content .= "</tr>";
            }
            $tool_content .= "</tbody>";
            $tool_content .= "</table>";
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langNoUsersLog</div>";
        }
        return;
    }

    /**
     * @brief move logs from table `log` to table `log_archive`
     * @return void
     */
    public static function rotate() {

        $date = get_config('log_expire_interval');
        // move records in table `log_archive`
        $sql = Database::get()->query("INSERT INTO log_archive (user_id, course_id, module_id, details, action_type, ts, ip)
                                SELECT user_id, course_id, module_id, details, action_type, ts, ip FROM log
                                WHERE DATE_SUB(CURDATE(),interval $date month) > ts");

        // delete previous records from `log`
        if ($sql) {
            Database::get()->query("DELETE FROM log WHERE date_sub(CURDATE(),interval $date month) > ts");
        }
    }

    /**
     * @brief purge logs from table `logs_archive`
     * @return void
     */
    public static function purge() {

        $date = get_config('log_purge_interval');
        $sql = Database::get()->query("DELETE FROM log_archive WHERE DATE_SUB(CURDATE(),interval $date month) > ts");

    }

    /**
     *
     * @global type $langUnknownModule
     * @param type $module_id
     * @param type $details
     * @return type
     * drive to appropriate subsystem for displaying details
     */
    public function course_action_details($module_id, $details, $type=null) {

        global $langUnknownModule;

        switch ($module_id) {
            case MODULE_ID_AGENDA: $content = $this->agenda_action_details($details);
                break;
            case MODULE_ID_LINKS: $content = $this->link_action_details($details);
                break;
            case MODULE_ID_DOCS: $content = $this->document_action_details($details);
                break;
            case MODULE_ID_ANNOUNCE: $content = $this->announcement_action_details($details);
                break;
            case MODULE_ID_ASSIGN: $content = $this->assignment_action_details($details);
                break;
            case MODULE_ID_VIDEO: $content = $this->video_action_details($details);
                break;
            case MODULE_ID_MESSAGE: $content = $this->dropbox_action_details($details);
                break;
            case MODULE_ID_GROUPS: $content = $this->group_action_details($details);
                break;
            case MODULE_ID_DESCRIPTION: $content = $this->description_action_details($details);
                break;
            case MODULE_ID_GLOSSARY: $content = $this->glossary_action_details($details);
                break;
            case MODULE_ID_LP: $content = $this->lp_action_details($details);
                break;
            case MODULE_ID_EXERCISE: $content = $this->exercise_action_details($details);
                break;
            case MODULE_ID_QUESTIONNAIRE: $content = $this->questionnaire_action_details($details);
                break;
            case MODULE_ID_WIKI: $content = $this->wiki_action_details($details);
                break;
            case MODULE_ID_USERS: $content = $this->course_user_action_details($details, $type);
                break;
            case MODULE_ID_TOOLADMIN: $content = $this->external_link_action_details($details);
                break;
            case MODULE_ID_ABUSE_REPORT: $content = $this->abuse_report_action_details($details);
                break;
            case MODULE_ID_WALL: $content = $this->wall_action_details($details);
                break;
            case MODULE_ID_COURSEINFO: $content = $this->modify_course_action_details($details);
                break;
            case MODULE_ID_SETTINGS: $content = $this->modify_course_action_details($details); // <-- for backward compatibility only !!!
                break;
            case MODULE_ID_GRADEBOOK: $content = $this->gradebook_action_details($details);
                break;
            case MODULE_ID_ATTENDANCE: $content = $this->attendance_action_details($details);
                break;
            case MODULE_ID_TC: $content = $this->tc_action_details($details);
                break;
            case MODULE_ID_CHAT: $content = $this->chat_action_details($details);
                break;
            /*case MODULE_ID_MINDMAP: $content = $this->mindmap_action_details($details);
                break; */
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
    private function other_action_details($logtype, $details) {

        global $langUnknownAction;

        switch ($logtype) {
            case LOG_CREATE_COURSE: $content = $this->create_course_action_details($details);
                break;
            case LOG_DELETE_COURSE: $content = $this->delete_course_action_details($details);
                break;
            case LOG_PROFILE: $content = $this->profile_action_details($details);
                break;
            case LOG_LOGIN_FAILURE: $content = $this->login_failure_action_details($details);
                break;
            case LOG_DELETE_USER: $content = $this->delete_user_action_details($details);
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
    private function create_course_action_details($details) {

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
    private function delete_course_action_details($details) {

        global $langTitle;

        $details = unserialize($details);

        $content = "$langTitle &laquo;" . q($details['title']) . "&raquo;";
        $content .= "&nbsp;(" . q($details['code']) . ")";

        return $content;
    }

    /**
     * @brief display action details while modifying course info
     * @param type $details
     * @return string
     */
    private function modify_course_action_details($details) {

        global $langCourseStatusChange, $langIn, $langClosedCourse,
               $langRegCourse, $langOpenCourse, $langInactiveCourse,
               $langActivate, $langDeactivate, $langBlogComment,
               $langBlogSharingLog, $langBlogRatingLog, $langsCourseSharing,
               $langCourseComment, $langsCourseAnonymousRating, $langsCourseRating,
               $langForumRating, $langCourseSocialBookmarks, $langCourseAbuseReport;

        $details = unserialize($details);

        if (isset($details['visible'])) {
            switch ($details['visible']) {
                case COURSE_CLOSED: $mes = q($langIn). "&nbsp;&laquo;". q($langClosedCourse) . "&raquo;";
                    break;
                case COURSE_REGISTRATION: $mes = q($langIn). "&nbsp;&laquo;". q($langRegCourse) . "&raquo;";
                    break;
                case COURSE_OPEN: $mes = q($langIn). "&nbsp;&laquo;". q($langOpenCourse) . "&raquo;";
                    break;
                case COURSE_INACTIVE: $mes = q($langIn). "&nbsp;&laquo;". q($langInactiveCourse) . "&raquo;";
                    break;
                default: $mes = '';
                    break;
            }
            $content = "$langCourseStatusChange $mes";
            $content .= "&nbsp;(" . q($details['public_code']) . ")";
        } else {
            $lm = '';
            switch ($details['id']) {
                case SETTING_BLOG_COMMENT_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langBlogComment";
                    break;
                case SETTING_BLOG_RATING_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langBlogRatingLog";
                    break;
                case SETTING_BLOG_SHARING_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langBlogSharingLog";
                    break;
                case SETTING_COURSE_SHARING_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langsCourseSharing";
                    break;
                case SETTING_COURSE_RATING_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langsCourseRating";
                    break;
                case SETTING_COURSE_COMMENT_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langCourseComment";
                    break;
                case SETTING_COURSE_ANONYMOUS_RATING_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langsCourseAnonymousRating";
                    break;
                case SETTING_FORUM_RATING_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langForumRating";
                    break;
                case SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langCourseSocialBookmarks";
                    break;
                case SETTING_COURSE_ABUSE_REPORT_ENABLE: $lm = ($details['value']) ?  "$langActivate" : "$langDeactivate";
                    $mes = "$lm $langCourseAbuseReport";
                    break;
                default: $mes = '';
                    break;
            }
            $content = "$mes";
        }
        return $content;
    }

    /**
     * @brief display user profile actions details
     * @param $details
     * @return string
     */
    private function profile_action_details($details) {

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
    private function login_failure_action_details($details) {

        global $lang_username;

        $details = unserialize($details);

        $content = "$lang_username&nbsp;&laquo;" . q($details['uname']) . "&raquo;";

        return $content;
    }

    private function delete_user_action_details($details) {
        global $lang_username, $langName;

        $details = unserialize($details);

        $content = "$lang_username&nbsp;&laquo;" . q($details['username']) . "&raquo;&nbsp;$langName&nbsp;&laquo;" . q($details['name']) . "&raquo;";

        return $content;
    }

    /**
     * display action details in video
     * @param type $details
     * @return string
     */
    private function video_action_details($details) {

        global $langTitle, $langDescription;

        $details = unserialize($details);
        $content = "$langTitle  &laquo" . q($details['title']) . "&raquo";
        if (!empty($details['description'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo" . q($details['description']) . "&raquo";
        }
        if (!empty($details['url'])) {
            $content .= "&nbsp;&mdash;&nbsp; URL &laquo" . q($details['url']) . "&raquo";
        }
        return $content;
    }

    /**
     * display action details in assignments
     * @param type $details
     * @return string
     */
    private function assignment_action_details($details) {

        global $langTitle, $langDescription, $m, $langGradebookGrade;

        $details = unserialize($details);
        $content = "$langTitle  &laquo" . q($details['title']) . "&raquo";
        if (!empty($details['description'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo" . $details['description'] . "&raquo";
        }
        if (!empty($details['filename'])) {
            $content .= "&nbsp;&mdash;&nbsp; " . q($m['filename']) . " &laquo" . q($details['filename']) . "&raquo";
        }
        if (!empty($details['comments'])) {
            $content .= "&nbsp;&mdash;&nbsp; " . q($m['comments']) . " &laquo" . q($details['comments']) . "&raquo";
        }
        if (!empty($details['grade'])) {
            $content .= "&nbsp;&mdash;&nbsp; " . q($langGradebookGrade) . " &laquo" . q($details['grade']) . "&raquo";
        }
        return $content;
    }

    /**
     * display action details in announcements
     * @param type $details
     * @return string
     */
    private function announcement_action_details($details) {

        global $langTitle, $langContent;

        $details = unserialize($details);
        $content = "$langTitle &laquo" . q($details['title']) .
                "&raquo&nbsp;&mdash;&nbsp; $langContent &laquo" . $details['content'] . "&raquo";
        return $content;
    }

    /**
     * display action details in agenda
     * @param type $details
     * @return string
     */
    private function agenda_action_details($details) {

        global $langTitle, $langContent, $langDuration, $langhours, $langDate;

        $details = unserialize($details);

        $content = "$langTitle &laquo" . q($details['title']) .
                "&raquo&nbsp;&mdash;&nbsp; $langContent &laquo" . $details['content'] . "";
        return $content;
    }

    /**
     * display action details in link
     * @param type $details
     * @return string
     */
    private function link_action_details($details) {

        global $langTitle, $langDescription, $langCategoryName;

        $details = unserialize($details);
        $content = '';
        if (!empty($details['url'])) {
            $content .= "URL: " . q($details['url']);
        }
        if (!empty($details['category'])) {
            $content .= " $langCategoryName &laquo" . q($details['category']) . "&raquo";
        }
        if (!empty($details['title'])) {
            $content .= " &mdash; $langTitle &laquo" . q($details['title']) . "&raquo";
        }
        if (!empty($details['description'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo" . $details['description'] . "&raquo";
        }
        return $content;
    }

    /**
     * display action details in documents
     * @param type $details
     * @return string
     */
    private function document_action_details($details) {

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
     * display action details in dropbox
     * @param type $details
     * @return string
     */
    private function dropbox_action_details($details) {

        global $langFileName, $langSubject, $langMessage;

        $details = unserialize($details);

        $content = "$langSubject &laquo" . q($details['subject']) . "&raquo";
        if (!empty($details['filename'])) {
            $content .= "&nbsp;&mdash;&nbsp;$langFileName &laquo" . q($details['filename']) . "&raquo";
        }
        if (!empty($details['body'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langMessage &laquo" . standard_text_escape($details['body']) . "&raquo";
        }
        return $content;
    }

    /**
     * display action details in groups
     * @param type $details
     * @return string
     */
    private function group_action_details($details) {

        global $langGroup, $langRegistration, $langInGroup;

        $details = unserialize($details);

        if (!empty($details['uid'])) {
            $content = "$langRegistration &laquo" . display_user($details['uid'], false, false) . "&raquo $langInGroup &laquo" . q($details['name']) . "&raquo";
        } else {
            $content = "$langGroup &laquo" . q($details['name']) . "&raquo";
        }

        return $content;
    }

    /**
     * display action details in course description
     * @param type $details
     * @return string
     */
    private function description_action_details($details) {

        global $langTitle, $langContent;

        $details = unserialize($details);

        $content = "$langTitle  &laquo" . q($details['title']) . "&raquo";
        $content .= "&nbsp;&mdash;&nbsp; $langContent &laquo" . ellipsize($details['content'], 100) . "&raquo";

        return $content;
    }

    /**
     * display action details in glossary
     * @param type $details
     * @return string
     */
    private function glossary_action_details($details) {

        global $langGlossaryTerm, $langGlossaryDefinition, $langGlossaryURL, $langCategoryNotes;

        $details = unserialize($details);

        $content = "$langGlossaryTerm &laquo" . q($details['term']) . "&raquo";
        if (!empty($details['definition'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langGlossaryDefinition &laquo" . q(ellipsize($details['definition'], 100)) . "&raquo";
        }
        if (!empty($details['url'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langGlossaryURL &laquo" . q($details['url']) . "&raquo";
        }
        if (!empty($details['notes'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langCategoryNotes &laquo" . $details['notes'] . "&raquo";
        }

        return $content;
    }

    /**
     * display action details in learning path
     * @global type $langLearnPath
     * @global type $langComments
     * @param type $details
     * @return string
     */
    private function lp_action_details($details) {

        global $langLearnPath, $langComments;

        $details = unserialize($details);

        $content = "$langLearnPath &laquo" . q($details['name']) . "&raquo";
        if (!empty($details['comment'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langComments &laquo" . q(ellipsize($details['comment'], 100)) . "&raquo";
        }
        return $content;
    }

    /**
     * display action details in exercises
     * @return string
     */
    private function exercise_action_details($details) {

        global $langTitle, $langDescription, $langAttempt, $urlAppend, $course_code,
               $langDelete, $langOfAttempt, $langOfUserS, $langModify, $langIn, $langPurgeExercises;

        $details = unserialize($details);
        if (is_object($details['title'])) {
            $details['title'] = $details['title']->title;
        }
        $content = "$langTitle &laquo" . q($details['title']) . "&raquo";
        if (!empty($details['description'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo" . q(ellipsize($details['description'], 100)) . "&raquo";
        }
        if (!empty($details['legend'])) {
            $content .= "&nbsp;&mdash;&nbsp;" . q($details['legend']) ;
        }
        if (isset($details['eurid']) and isset($course_code) and $course_code) {
            $content .= "<br><a href='{$urlAppend}modules/exercise/exercise_result.php?course=$course_code&amp;eurId=$details[eurid]'>$langAttempt</a>";
        }
        if (isset($details['del_eurid_uid'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDelete $langOfAttempt $langOfUserS &laquo;" . uid_to_name($details['del_eurid_uid']) . "&raquo;";
        }
        if (isset($details['mod_eurid_uid'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langModify $langOfAttempt $langOfUserS &laquo;" . uid_to_name($details['mod_eurid_uid']) . "&raquo;&nbsp;$langIn&nbsp;&laquo;" . get_exercise_attempt_status_legend($details['new_eurid_status']) . "&raquo";
        }
        if (isset($details['purge_results'])) {
            $content .= "&nbsp;&mdash;&nbsp;$langPurgeExercises";
        }

        return $content;
    }

    /**
     * @brief action details in questionnaire
     * @param $details
     * @return string
     */
    private function questionnaire_action_details($details) {

        global $langTitle, $langDescription, $langSubmit, $langPurgeExerciseResults;

        $details = unserialize($details);
        if (is_object($details['title'])) {
            $details['title'] = $details['title']->title;
        }
        $content = "$langTitle &laquo" . q($details['title']) . "&raquo";
        if (!empty($details['description'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo" . q(ellipsize($details['description'], 100)) . "&raquo";
        }
        if (!empty($details['legend'])) {
            switch ($details['legend']) {
                case 'submit_answers': $content .= "&nbsp;&mdash;&nbsp; $langSubmit";
                    break;
                case 'purge_results': $content .= "&nbsp;&mdash;&nbsp; $langPurgeExerciseResults";
                    break;
            }
        }
        return $content;
    }

    /**
     * display action details in wiki
     * @global type $langTitle
     * @global type $langDescription
     * @param type $details
     * @return string
     */
    private function wiki_action_details($details) {

        global $langTitle, $langDescription;

        $details = unserialize($details);

        $content = "$langTitle &laquo" . q($details['title']) . "&raquo";
        if (!empty($details['description'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo" . q(ellipsize($details['description'], 100)) . "&raquo";
        }
        return $content;
    }

    /**
     * display action details in course users administration
     * @param type $details
     * @return string
     */
    private function course_user_action_details($details, $type): string
    {

        global $langOfUserS, $langToUser,
        $langsOfTeacher, $langsOfEditor, $langRegistration, $langAddGUser,
        $langUnCourse, $langTheU, $langGiveRight,
        $langRemovedRight, $langsOfGroupTutor, $langsOfCourseReviewer,
        $langDelUsers, $langParams;

        $details = unserialize($details);

        if (isset($details['multiple'])) {
            if ($type == LOG_DELETE) {
                $content = "$langDelUsers &mdash; $langParams: " .
                    implode(', ', $details['params']) . '<br>' .
                    display_user($details['uid']);
            }
            return $content;
        }

        switch ($details['right']) {
            case '+5': $content = $langRegistration;
                       $content .= "&nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo";
                break;
            case '-5': $content = $langUnCourse;
                       $content .= "&nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo";
                break;
            case '0': $content = "$langUnCourse $langOfUserS";
                      $content .= "&nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo";
                break;
            case '+1': $content = "$langTheU &nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo;&nbsp;";
                       $content .= "$langGiveRight $langsOfTeacher $langToUser";
                       $content .= "&nbsp;&laquo" . display_user($details['dest_uid'], false, false) . "&raquo";
                break;
            case '+2': $content = "$langTheU &nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo&nbsp;";
                       $content .= "$langGiveRight $langsOfEditor $langToUser";
                       $content .= "&nbsp;&laquo" . display_user($details['dest_uid'], false, false) . "&raquo";
                break;
            case '+3': $content = "$langTheU &nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo&nbsp;";
                       $content .= "$langGiveRight $langsOfGroupTutor $langToUser";
                       $content .= "&nbsp;&laquo" . display_user($details['dest_uid'], false, false) . "&raquo";
                break;
            case '+4': $content = "$langTheU &nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo&nbsp;";
                       $content .= "$langGiveRight $langsOfCourseReviewer $langToUser";
                       $content .= "&nbsp;&laquo" . display_user($details['dest_uid'], false, false) . "&raquo";
                       break;
            case '-1': $content = "$langTheU &nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo&nbsp;";
                       $content .= "$langRemovedRight $langsOfTeacher $langToUser";
                       $content .= "&nbsp;&laquo" . display_user($details['dest_uid'], false, false) . "&raquo";
                break;
            case '-2': $content = "$langTheU &nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo&nbsp;";
                       $content .= "$langRemovedRight $langsOfEditor $langToUser";
                       $content .= "&nbsp;&laquo" . display_user($details['dest_uid'], false, false) . "&raquo";
                break;
            case '-3': $content = "$langTheU &nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo&nbsp;";
                       $content .= "$langRemovedRight $langsOfGroupTutor $langToUser";
                       $content .= "&nbsp;&laquo" . display_user($details['dest_uid'], false, false) . "&raquo";
                break;
            case '-4': $content = "$langTheU &nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo&nbsp;";
                       $content .= "$langRemovedRight $langsOfCourseReviewer $langToUser";
                       $content .= "&nbsp;&laquo" . display_user($details['dest_uid'], false, false) . "&raquo";
                       break;
            case '+10': $content = "$langAddGUser&nbsp;&laquo" . display_user($details['uid'], false, false) . "&raquo&nbsp;";
                break;
        }
        return $content;
    }

    /**
     * display action details in external links
     * @param type $details
     * @return string
     */
    private function external_link_action_details($details) {
        global $course_code, $urlAppend, $langLinkName, $langActivate, $langDeactivate, $langCoursePages;

        $details = unserialize($details);

        if (isset($details['page_path'])) {
            return "$langCoursePages: <a target='_blank' href='{$urlAppend}modules/course_home/page.php?course={$course_code}&amp;id=$details[id]'>" . q($details['title']) . '</a>';
        }
        $parts = [];
        if (isset($details['activate'])) {
            $parts[] = "$langActivate: " .
                implode(', ', array_map(function ($mid) {
                    return q($GLOBALS['modules'][$mid]['title']);
                }, $details['activate']));
        }
        if (isset($details['deactivate'])) {
            $parts[] = "$langDeactivate: " .
                implode(', ', array_map(function ($mid) {
                    return q($GLOBALS['modules'][$mid]['title']);
                }, $details['deactivate']));
        }
        if (isset($details['link']) and $details['link']) {
            $parts[] = "URL: " . q($details['link']);
        }
        if (isset($details['name_link']) and $details['name_link']) {
            $parts[] = "$langLinkName &laquo" . q($details['name_link']) . "&raquo";
        }
        return implode('<br>', $parts);
    }

    /**
     * display action details in abuse reports
     * @param type $details
     * @return string
     */
    private function abuse_report_action_details($details) {

        global $langCreator, $langAbuseReportCat, $langSpam, $langRudeness, $langOther, $langMessage,
               $langComment, $langForumPost, $langAbuseResourceType, $langContent, $langAbuseReportStatus,
               $langAbuseReportOpen, $langAbuseReportClosed, $langLinks, $langWallPost;

        $reports_cats = array('rudeness' => $langRudeness,
                              'spam' => $langSpam,
                              'other' => $langOther);

        $resource_types = array('comment' => $langComment,
                                'forum_post' => $langForumPost,
                                'link' => $langLinks,
                                'wallpost' => $langWallPost);

        $details = unserialize($details);

        $content = "$langCreator: ". display_user($details['user_id'], false, false)."<br/>";
        $content .= "$langAbuseReportCat: &laquo".$reports_cats[$details['reason']]."&raquo<br/>";
        $content .= "$langMessage: &laquo".q($details['message'])."&raquo<br/>";
        $content .= "$langAbuseResourceType: &laquo".$resource_types[$details['rtype']]."&raquo<br/>";
        if ($details['rtype'] == 'comment') {
            $content .= "$langContent: &laquo".q($details['rcontent'])."&raquo<br/>";
        } elseif ($details['rtype'] == 'forum_post') {
            $content .= "$langContent: &laquo".mathfilter($details['rcontent'], 12, "../courses/mathimg/")."&raquo<br/>";
        }
        if ($details['status'] == 1) {
            $content.= "$langAbuseReportStatus: &laquo".$langAbuseReportOpen."&raquo";
        } elseif ($details['status'] == 0) {
            $content.= "$langAbuseReportStatus: &laquo".$langAbuseReportClosed."&raquo";
        }

        return $content;
    }

    /**
     * display action details for social wall
     * @global type $langContent
     * @global type $langWallVideoLink
     * @param type $details
     * @return string
     */
    private function wall_action_details($details) {
        global $langContent, $langWallExtVideoLink;

        $details = unserialize($details);

        $content = '';

        if (!empty($details['content'])) {
            $content .= "$langContent: &laquo".q($details['content'])."&raquo<br/>";
        }
        if (!empty($details['extvideo'])) {
            $content .= "$langWallExtVideoLink: &laquo".q($details['extvideo'])."&raquo<br/>";
        }

        return $content;
    }

    /**
     *
     * @brief display action details in gradebooks
     * @param type $details
     * @return string
     */
    private function gradebook_action_details($details){
        global $langTitle, $langType, $langDate, $langStart, $langEnd, $langGradebookWeight, $langVisibility,
                $langGradebookRange, $langOfGradebookActivity,
                $langOfGradebookUser, $langOfGradebookUsers, $langAdd, $langDelete,
                $langGroups, $langUsers, $langUser, $langGradebookDateOutOf, $langGradebookDateIn,
                $langModify, $langOfGradebookVisibility, $langOfUsers, $langAction,
                $langGradebookDateRange, $langGradebookRegistrationDateRange,
                $langGradebookLabs, $langGradebookOral, $langGradebookProgress,
                $langGradebookOtherType, $langGradebookExams, $langVisibleVals, $langRefreshList;

        $langActivityType = array('', $langGradebookOral, $langGradebookLabs, $langGradebookProgress, $langGradebookExams, $langGradebookOtherType);

        $d = unserialize($details);
        $content = "";
        $separator = function() use(&$content) {return empty($content)? "":", ";};
        //Gradebook basic info
        if(isset($d['title'])){
            $content .= "$langTitle: {$d['title']}";
        }
        if(isset($d['gradebook_range'])){
            $content .= $separator()."$langGradebookRange: ".$d['gradebook_range'];
        }
        if(isset($d['start_date'])){
            $content .= $separator()."$langStart: ".$d['start_date'];
        }
        if(isset($d['end_date'])){
            $content .= $separator()."$langEnd: ".$d['end_date'];
        }

        if(isset($d['action'])){
            $content .= $separator()."$langAction: ";
            if($d['action'] == 'change gradebook visibility'){
                $content .= "$langModify $langOfGradebookVisibility";
                $content .= $separator()."$langVisibility: {$langVisibleVals[$d['visibility']]}";
            }
            //Gradebook activities
            elseif($d['action'] == 'add activity' or $d['action'] == 'modify activity'){
                $content .= ($d['action'] == 'add activity')? "$langAdd $langOfGradebookActivity":"$langModify $langOfGradebookActivity";
                if(isset($d['activity_type']) && isset($langActivityType[$d['activity_type']])){
                    $content .= $separator()."$langType: {$langActivityType[$d['activity_type']]}";
                }
                if(isset($d['activity_title'])){
                    $content .= $separator()."$langTitle:</label> {$d['activity_title']}";
                }
                if(isset($d['activity_date'])){
                    $content .= $separator()."$langDate: {$d['activity_date']}";
                }
                if(isset($d['weight'])){
                    $content .= $separator()."$langGradebookWeight: {$d['weight']}";
                }
                if(isset($d['visible'])){
                    $content .= $separator()."$langVisibility: {$langVisibleVals[$d['visible']]}";
                }
            }
            elseif($d['action'] == 'delete activity'){
                $content .= "$langDelete $langOfGradebookActivity";
                if(isset($d['activity_title'])){
                    $content .= $separator()."$langTitle: {$d['activity_title']}";
                }
            }
            //Gradebook users
            elseif($d['action'] == 'add users'){
                $content .= "$langAdd $langOfUsers";
                if(isset($d['user_count'])){
                    $content .= $separator()."$langUsers: {$d['user_count']}";
                }
            }
            elseif($d['action'] == 'delete users'){
                $content .= ($d['action'] == 'delete user')? "$langDelete $langOfGradebookUser":"$langDelete $langOfGradebookUsers";
                if(isset($d['user_name'])){
                    $content .= $separator()."$langUser: {$d['user_name']}";
                }
                elseif(isset($d['user_count'])){
                    $content .= $separator()."$langUsers: {$d['user_count']}";
                }

            }
            elseif($d['action'] == 'reset users'){
                $content .= "$langRefreshList";
                if(isset($d['group_count'])){
                    $content .= $separator()."$langGroups: {$d['group_count']}";
                }
                elseif(isset($d['user_count'])){
                    $content .= $separator()."$langUsers: {$d['user_count']}";
                }
            }
            elseif($d['action'] == 'add users in date range' || $d['action'] == 'delete users out of date range'){
                $content .= ($d['action'] == 'add users in date range')? "$langAdd $langOfUsers $langGradebookDateIn $langGradebookDateRange":"$langDelete $langOfUsers $langGradebookDateOutOf $langGradebookDateRange";
                if(isset($d['user_count'])){
                    $content .= $separator()."$langUsers: {$d['user_count']}";
                }
                if(isset($d['users_start']) && $d['users_end']){
                    $content .= $separator()."$langGradebookRegistrationDateRange: {$d['users_start']} - {$d['users_end']}";
                }

            }
        }

        return $content;
    }

   /**
    * @brief display action details in attendance module
    * @param type $details
    * @return string
    */
    private function attendance_action_details($details){
        global $langTitle, $langDate, $langStart, $langEnd, $langAttendanceLimit, $langVisibility, $langOfGradebookActivity,
                $langOfGradebookUser, $langOfGradebookUsers, $langAdd, $langDelete, $langGroups, $langUsers, $langUser, $langGradebookDateOutOf,
                $langGradebookDateIn, $langOfGradebookVisibility, $langAction, $langGradebookDateRange, $langGradebookRegistrationDateRange,
                $langModify, $langVisibleVals, $langRefreshList, $langOfUsers;

        $d = unserialize($details);
        $content = "";
        $separator = function() use(&$content) {return empty($content)? "":", ";};
        //Attendance basic info
        if(isset($d['title'])){
            $content .= "$langTitle: {$d['title']}";
        }
        if(isset($d['attendance_limit'])){
            $content .= $separator()."$langAttendanceLimit: ".$d['attendance_limit'];
        }
        if(isset($d['start_date'])){
            $content .= $separator()."$langStart: ".$d['start_date'];
        }
        if(isset($d['end_date'])){
            $content .= $separator()."$langEnd: ".$d['end_date'];
        }
        if(isset($d['action'])){
            $content .= $separator()."$langAction: ";
            if($d['action'] == 'change gradebook visibility'){
                $content .= "$langModify $langOfGradebookVisibility";
                $content .= $separator()."$langVisibility: {$langVisibleVals[$d['visibility']]}";

            }
            //Attendance activities
            elseif($d['action'] == 'add activity' or $d['action'] == 'modify activity'){
                $content .= ($d['action'] == 'add activity')? "$langAdd $langOfGradebookActivity":"$langModify $langOfGradebookActivity";
                if(isset($d['activity_title'])){
                    $content .= $separator()."$langTitle:</label> {$d['activity_title']}";
                }
                if(isset($d['activity_date'])){
                    $content .= $separator()."$langDate: {$d['activity_date']}";
                }
                if(isset($d['visible'])){
                    $content .= $separator()."$langVisibility: {$langVisibleVals[$d['visible']]}";
                }
            }
            elseif($d['action'] == 'delete activity'){
                $content .= "$langDelete $langOfGradebookActivity";
                if(isset($d['activity_title'])){
                    $content .= $separator()."$langTitle: {$d['activity_title']}";
                }
            }
            //Attendance users
            elseif($d['action'] == 'add users'){
                $content .= "$langAdd $langOfUsers";
                if(isset($d['user_count'])){
                    $content .= $separator()."$langUsers: {$d['user_count']}";
                }

            }
            elseif($d['action'] == 'delete user'){
                $content .= ($d['action'] == 'delete user')? "$langDelete $langOfGradebookUser":"$langDelete $langOfGradebookUsers";
                if(isset($d['user_name'])){
                    $content .= $separator()."$langUser: {$d['user_name']}";
                }
                elseif(isset($d['user_count'])){
                    $content .= $separator()."$langUsers: {$d['user_count']}";
                }

            }
            elseif($d['action'] == 'reset users'){
                $content .= "$langRefreshList";
                if(isset($d['group_count'])){
                    $content .= $separator()."$langGroups: {$d['group_count']}";
                }
                elseif(isset($d['user_count'])){
                    $content .= $separator()."$langUsers: {$d['user_count']}";
                }
            }
            elseif($d['action'] == 'add users in date range' || $d['action'] == 'delete users in date range'){
                $content .= ($d['action'] == 'add users in date range')? "$langAdd $langOfUsers $langGradebookDateIn $langGradebookDateRange":"$langDelete $langOfUsers $langGradebookDateOutOf $langGradebookDateRange";
                if(isset($d['user_count'])){
                    $content .= $separator()."$langUsers: {$d['user_count']}";
                }
                if(isset($d['users_start']) && $d['users_end']){
                    $content .= $separator()."$langGradebookRegistrationDateRange: {$d['users_start']} - {$d['users_end']}";
                }

            }
        }
        return $content;
    }

    /**
     * @display action details in tc module
     * @param type $details
     * @return string
     */
    private function tc_action_details($details) {

        global $langTitle, $langDescription, $langType;

        $d = unserialize($details);
        $content = '';
        if (isset($d['tc_type'])) {
            $content .= "$langType " . "&laquo" . $d['tc_type']. "&raquo&nbsp;&mdash;&nbsp;";
        }
        $content .= "$langTitle " . "&laquo" . $d['title'] . "&raquo";
        if (isset($d['desc'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDescription " . "&laquo" . $d['desc']. "&raquo";
        }

        return $content;

    }

    /**
     * @brief action details in chat module
     * @param $details
     * @return string
     */
    private function chat_action_details($details) {

        global $langTitle, $langDescription, $langModify, $langOfGradebookVisibility;

        $d = unserialize($details);

        $content = "$langTitle " . "&laquo" . $d['title'] . "&raquo";
        if (isset($d['desc'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langDescription " . "&laquo" . $d['desc']. "&raquo";
        }
        if (isset($d['status'])) {
            $content .= "&nbsp;&mdash;&nbsp; $langModify $langOfGradebookVisibility " . "&laquo" . $d['status']. "&raquo";
        }

        return $content;

    }
    /**
     * @param type $action_type
     * @return string (real action names)
     */
    public function get_action_names($action_type) {

        global $langInsert, $langModify, $langDelete, $langModProfile, $langLoginFailures,
        $langFinalize, $langCourseDel, $langModifyInfo, $langUnregUsers, $langUnknownAction;

        switch ($action_type) {
            case LOG_INSERT: return $langInsert;
            case LOG_MODIFY: return $langModify;
            case LOG_DELETE: return $langDelete;
            case LOG_PROFILE: return $langModProfile;
            case LOG_CREATE_COURSE: return $langFinalize;
            case LOG_DELETE_COURSE: return $langCourseDel;
            case LOG_MODIFY_COURSE: return $langModifyInfo;
            case LOG_LOGIN_FAILURE: return $langLoginFailures;
            case LOG_DELETE_USER: return $langUnregUsers;
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

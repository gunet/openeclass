<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

define('LOG_INSERT', 1);
define('LOG_MODIFY', 2);
define('LOG_DELETE', 3);

class Log {
        // log users actions
        public static function record($module_id, $action_type, $details) {
                
                global $course_id;
                                
                db_query("INSERT INTO log SET 
                                user_id = $_SESSION[uid],
                                course_id = $course_id,
                                module_id = $module_id,
                                details = ".quote(serialize($details)).",
                                action_type = $action_type,
                                ts = NOW(),
                                ip = '$_SERVER[SERVER_ADDR]'");
                return;
        }
        
        //-------------------------------------------------------------
        // display users actions 
        //
        // $module_id = -1 means all modules
        // $logtype = 0 means logtypes
        // $user_id = -1 means all users
        // $course_id = -1 means all courses
        //-------------------------------------------------------------
        public function display($course_id, $user_id, $module_id, $logtype, $date_from, $date_now) {
                 
                global $tool_content, $modules;
                global $langNoUsersLog, $langDate, $langUser, $langAction, $langDetail, 
                        $langCourse, $langModule, $langAllModules;
                
                $q1 = $q2 = $q3 = $q4 = '';
                if ($user_id != -1) {
                        $q1 = "AND user_id = $user_id";
                }                
                if ($module_id != -1) {
                        $q2 = "AND module_id = $module_id";
                }
                if ($logtype != 0) {
                        $q3 = "AND action_type = $logtype";
                }
                if ($course_id != -1) {
                        $q4 = "AND course_id = $course_id";
                }
                $sql = db_query("SELECT user_id, course_id, module_id, details, action_type, ts FROM log
                                WHERE ts BETWEEN '$date_from' AND '$date_now'
                                $q1 $q2 $q3 $q4
                                ORDER BY ts DESC");
                
                if (mysql_num_rows($sql) > 0) {
                                if ($course_id != -1) {
                                        $tool_content .= "<div class='info'>$langCourse: ".course_id_to_title($course_id)."</div>";
                                }                                
                                if ($module_id != -1) {
                                        $tool_content .= "<div class='info'>$langModule: ".$modules[$module_id]['title']."</div>";
                                }
                                $tool_content .= "<table class='tbl'>";                                                                
                                $tool_content .= "<tr><th>$langDate</th><th>$langUser</th>";
                                if ($course_id == -1) {
                                        $tool_content .= "<th>$langCourse</th>";
                                }
                                if ($module_id == -1) {
                                        $tool_content .= "<th>$langModule</th>";
                                }
                                $tool_content .= "<th>$langAction</th><th>$langDetail</th>";
                                $tool_content .= "</tr>";                
                                while ($r = mysql_fetch_array($sql)) {
                                              $tool_content .= "<tr>";
                                        $tool_content .= "<td>".nice_format($r['ts'], true)."</td>";
                                        $tool_content .= "<td>".display_user($r['user_id'], false, false)."</td>";
                                        if ($course_id == -1) { // all courses
                                                $tool_content .= "<td>".course_id_to_title($r['course_id'])."</td>";
                                        }
                                        if ($module_id == -1) { // all modules
                                               $mid = $r['module_id'];
                                               $tool_content .= "<td>".$modules[$mid]['title']."</td>";
                                        }                                        
                                        $tool_content .= "<td>".$this->get_action_names($r['action_type'])."</td>";
                                        $tool_content .= "<td>".$this->action_details($r['module_id'], $r['details'])."</td>";
                                        $tool_content .= "</tr>";
                                }                
                                $tool_content .= "</table>";
                } else {
                        $tool_content .= "<div class='alert1'>$langNoUsersLog</div>";
                }
                return;
        }
         
       
        private function action_details($module_id, $details) {
                                      
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
                        default: $content = $langUnknownModule;
                                break;
                        }                        
                return $content;
        }
        
        private function video_action_details($details) {
                
                global $langTitle, $langDescription;
                
                $details = unserialize($details);
                $content = "$langTitle  &laquo".$details['title']."&raquo";
                if (!empty($details['description'])) {
                        $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo".$details['description']."&raquo";
                }
                if (!empty($details['url'])) {
                        $content .= "&nbsp;&mdash;&nbsp; URL &laquo".$details['url']."&raquo";
                }                
                return $content;                
        }
        
        private function assignment_action_details($details) {
                
                global $langTitle, $langDescription, $m;
                
                $details = unserialize($details);
                $content = "$langTitle  &laquo".$details['title']."&raquo";
                if (!empty($details['description'])) {
                        $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo".$details['description']."&raquo";
                }
                if (!empty($details['filename'])) {
                        $content .= "&nbsp;&mdash;&nbsp; ".$m['filename']." &laquo".$details['filename']."&raquo";
                }
                if (!empty($details['comments'])) {
                        $content .= "&nbsp;&mdash;&nbsp; ".$m['comments']." &laquo".$details['comments']."&raquo";
                }
                if (!empty($details['grade'])) {
                        $content .= "&nbsp;&mdash;&nbsp; ".$m['grade']." &laquo".$details['grade']."&raquo";
                }                
                return $content;
        }
        
        private function announcement_action_details($details) {
                
                global $langTitle, $langContent;
                
                $details = unserialize($details);                
                $content = "$langTitle &laquo".$details['title'].
                            "&raquo&nbsp;&mdash;&nbsp; $langContent &laquo".$details['content']."&raquo";
                return $content;
        }
        
        private function agenda_action_details($details) {
                
                global $langTitle, $langContent, $langDuration, $langhours, $langDate;
                
                $details = unserialize($details);
                $date = $details['day']." ".$details['hour'];
                                                
                $content = "$langTitle &laquo".$details['title'].
                            "&raquo&nbsp;&mdash;&nbsp; $langContent &laquo".$details['content']."&raquo
                             &nbsp;&mdash;&nbsp;$langDate: ".nice_format($date, true)."
                             &nbsp;&mdash;&nbsp;$langDuration: ".$details['lasting']." $langhours";
                return $content;
                
        }
        
        private function link_action_details($details) {
                
                global $langTitle, $langDescription, $langCategoryName;
                                
                $details = unserialize($details);
                $content = '';
                if (!empty($details['url'])) {
                        $content .= "URL: ".$details['url'];
                }                
                if (!empty($details['category'])) {
                        $content .= " $langCategoryName &laquo".$details['category']."&raquo";
                }
                if (!empty($details['title'])) {
                        $content .= " &mdash; $langTitle &laquo".$details['title']."&raquo";
                }                
                if (!empty($details['description'])) {
                        $content .= "&nbsp;&mdash;&nbsp; $langDescription &laquo".$details['description']."&raquo";
                }                                        
                return $content;
        }
        
        private function document_action_details($details) {
                
                global $langFileName, $langComments, $langTitle, $langRename, $langMove, $langTo, $langIn;
                
                $details = unserialize($details);
                
                $content = "$langFileName &laquo".$details['filename']."&raquo";
                if (!empty($details['title'])) {
                        $content .= "&nbsp;&mdash;&nbsp; $langTitle &laquo".$details['title']."&raquo";
                }
                if (!empty($details['comment'])) {
                        $content .= "&nbsp;&mdash;&nbsp; $langComments &laquo".$details['comment']."&raquo";
                }
                if (!empty($details['newfilename'])) {
                        $content .= "&nbsp;&mdash;&nbsp; $langRename $langIn &laquo".$details['newfilename']."&raquo";
                }
                if (!empty($details['newpath'])) {
                        $content .= "&nbsp;&mdash;&nbsp; $langMove $langTo &laquo".$details['newpath']."&raquo";
                }                
                return $content;
        }                
        
        // return the real action names
        private function get_action_names($action_type) {
                
                global $langInsert, $langModify, $langDelete, $langUnknownAction;
                
                switch ($action_type) {
                        case LOG_INSERT: return $langInsert;
                        case LOG_MODIFY: return $langModify;
                        case LOG_DELETE: return $langDelete;
                        default: return $langUnknownAction;
                }
        }
}
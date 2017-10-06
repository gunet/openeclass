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
 * eClass personal and course events manipulation library
 *
 * @version 1.0
 * @absract
 * This class mainly contains static methods, so it could be defined simply
 * as a name space.
 * However, it is created as a class for a possible need of instantiation of
 * event objects in the future. Another scenario could be the creation
 * of a set of abstract methods to be implemented separately per module.
 *
 */

require_once 'include/log.class.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/references.class.php';

class Calendar_Events {

    /** @staticvar array of event groups to group and style events in calendar
    */
    private static $event_groups = array('deadline', 'course', 'personal', 'admin');

    /** @staticvar array of urls to form links from events in calendar
    */
    private static $event_type_url = array(
            'personal' => 'main/personal_calendar/?id=thisid',
            'admin' => 'main/personal_calendar/?admin=1&id=thisid',
            'assignment' => 'modules/work/index.php?id=thisid&course=thiscourse',
            'exercise' => 'modules/exercise/exercise_submit.php?course=thiscourse&exerciseId=thisid',
            'agenda' => 'modules/agenda/?id=thisid&course=thiscourse',
            'teleconference' => 'modules/tc/?course=thiscourse');

    /** @staticvar object with user calendar settings
    */
    private static $calsettings;

    public static function get_calendar_settings() {
        global $uid;

        Calendar_Events::$calsettings = new stdClass();
        $q = Database::get()->querySingle("SELECT
                            user_id, view_type,
                            personal_color, course_color, deadline_color, admin_color,
                            CAST(show_personal AS UNSIGNED INTEGER) AS show_personal,
                            CAST(show_course AS UNSIGNED INTEGER) AS show_course,
                            CAST(show_deadline AS UNSIGNED INTEGER) AS show_deadline,
                            CAST(show_admin AS UNSIGNED INTEGER) AS show_admin
                        FROM personal_calendar_settings WHERE user_id = ?d", $uid);
        if ($q) {
            Calendar_Events::$calsettings->user_id = $q->user_id;
            Calendar_Events::$calsettings->view_type = $q->view_type;
            Calendar_Events::$calsettings->personal_color = $q->personal_color;
            Calendar_Events::$calsettings->course_color = $q->course_color;
            Calendar_Events::$calsettings->deadline_color = $q->deadline_color;
            Calendar_Events::$calsettings->admin_color = $q->admin_color;
            Calendar_Events::$calsettings->show_personal = $q->show_personal;
            Calendar_Events::$calsettings->show_course = $q->show_course;
            Calendar_Events::$calsettings->show_deadline = $q->show_deadline;
            Calendar_Events::$calsettings->show_admin = $q->show_admin;
        } else {
            Calendar_Events::$calsettings->view_type = 'month';
            Calendar_Events::$calsettings->personal_color = '#5882fa';
            Calendar_Events::$calsettings->course_color = '#5882fa';
            Calendar_Events::$calsettings->deadline_color = '#fa5882';
            Calendar_Events::$calsettings->admin_color = '#eeeeee';
            Calendar_Events::$calsettings->show_personal = 1;
            Calendar_Events::$calsettings->show_course = 1;
            Calendar_Events::$calsettings->show_deadline = 1;
            Calendar_Events::$calsettings->show_admin = 1;
        }
    }

    public static function set_calendar_settings($show_personal, $show_course, $show_seadline, $show_admin) {
        global $uid;
        Database::get()->querySingle("UPDATE personal_calendar_settings SET show_personal = ?b, show_course = ?b, show_deadline = ?b, show_admin = ?b WHERE user_id = ?d", $show_personal, $show_course, $show_seadline, $show_admin, $uid);
    }

    private static function set_calendar_view_preference($view_type = 'month') {
        global $uid;
        Database::get()->querySingle("UPDATE personal_calendar_settings SET view_type = ?s WHERE user_id = ?d", $view_type, $uid);
    }

    /********  Basic set of functions to be called from inside **************
     *********** personal events module that manipulates event items ********************/

    /**
     * Get event details given the event id
     * @param int $eventid id in table personal_calendar
     * @return array event tuple
     */
    public static function get_event($eventid) {
        global $uid;
        return Database::get()->querySingle("SELECT * FROM personal_calendar WHERE id = ?d AND user_id = ?d", $eventid, $uid);
    }

    /**
     * Get admin event details given the event id
     * @param int $eventid id in table admin_calendar
     * @return array event tuple
     */
    public static function get_admin_event($eventid) {

        return Database::get()->querySingle("SELECT * FROM admin_calendar WHERE id = ?d", $eventid);

    }

    /**
     * Get personal events with details for a given user
     * @param string $startdate mysql friendly formatted string representing the start of the time frame for which events are seeked
     * @param string $enddate mysql friendly formatted string representing the end of the time frame for which events are seeked
     * @param int $user_id if empty the session user is assumed
     * @return array of user events with details
     */
    public static function get_user_events($scope = "month", $startdate = null, $enddate = null, $user_id = NULL) {
        global $uid;
        if (is_null($user_id)) {
            $user_id = $uid;
        }
        $dateconditions = array("month" => "date_format(?t".',"%Y-%m") = date_format(start,"%Y-%m")',
                                "week" => "YEARWEEK(?t,1) = YEARWEEK(start,1)",
                                 "day" => "date_format(?t".',"%Y-%m-%d") = date_format(start,"%Y-%m-%d")');

        $select_from_where_clause = "SELECT id, title, start, date_format(start, '%Y-%m-%d') startdate, duration, content, "
                . "'personal' event_type, 'personal' event_group FROM personal_calendar WHERE user_id = ?d";
        $order_by_clause =  " ORDER BY `start`";
        if (!is_null($startdate) && !is_null($enddate)) {
            $datecond = " AND start>=?t AND start<=?t";
            return Database::get()->queryArray($select_from_where_clause.$datecond.$order_by_clause, $user_id, $startdate, $enddate);
        } elseif (!is_null($startdate)) {
            $datecond = " AND ";
            $datecond .= (array_key_exists($scope, $dateconditions))? $dateconditions[$scope]: $dateconditions["month"];
            return Database::get()->queryArray($select_from_where_clause.$datecond.$order_by_clause, $user_id, $startdate);
        } else {
            return Database::get()->queryArray($select_from_where_clause.$order_by_clause, $user_id);
        }
    }

    /**
     * Get calendar events for a given user including personal and course events
     * @param string $scope month|week|day the calendar selected view
     * @param string $startdate mysql friendly formatted string representing the start of the time frame for which events are seeked
     * @param string $enddate mysql friendly formatted string representing the end of the time frame for which events are seeked
     * @param int $user_id if empty the session user is assumed
     * @return array of user events with details
     */
    public static function get_calendar_events($scope = "month", $startdate = null, $enddate = null, $user_id = NULL) {
        global $uid, $is_admin;

        if (is_null($user_id)) {
            $user_id = $uid;
        }

        //form date range condition
        $dateconditions = array("month" => "date_format(?t".',"%Y-%m") = date_format(start,"%Y-%m")',
                                "week" => "YEARWEEK(?t,1) = YEARWEEK(start,1)",
                                 "day" => "date_format(?t".',"%Y-%m-%d") = date_format(start,"%Y-%m-%d")');
        if (!is_null($startdate) && !is_null($enddate)) {
            $datecond = " AND start>=?t AND start<=?t";
        } elseif (!is_null($startdate)) {
            $datecond = " AND ";
            $datecond .= (array_key_exists($scope, $dateconditions))? $dateconditions[$scope]:$dateconditions["month"];
        } else {
            $datecond = "";
        }
        //retrieve events from various tables according to user preferences on what type of events to show
        $q = "";
        $q_args = array();
        $q_args_templ = array();
        $q_args_templ[] = $user_id;
        if (!is_null($startdate)) {
           $q_args_templ[] = $startdate;
        }
        if (!is_null($enddate)) {
           $q_args_templ[] = $enddate;
        }
        if (isset($uid)) {
            Calendar_Events::get_calendar_settings();
            if (Calendar_Events::$calsettings->show_personal == 1) {
                $dc = str_replace('start', 'pc.start', $datecond);
                $q .= "SELECT id, title, start, date_format(start,'%Y-%m-%d') startdate, duration, date_format(start + time(duration), '%Y-%m-%d %H:%i') `end`, content, 'personal' event_group, 'event-special' class, 'personal' event_type, null as course FROM personal_calendar pc "
                        . "WHERE user_id = ?d " . $dc;
                $q_args = array_merge($q_args, $q_args_templ);
            }
            $st = ($is_admin)? 0: $_SESSION['status'];
            if (Calendar_Events::$calsettings->show_admin == 1) {
                //admin
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'adm.start', $datecond);
                $q .= "SELECT id, title, start, date_format(start, '%Y-%m-%d') startdate, duration, date_format(start + time(duration), '%Y-%m-%d %H:%i') `end`, content, 'admin' event_group, 'event-success' class, 'admin' event_type, null as course FROM admin_calendar adm "
                        . "WHERE (visibility_level>=$st OR user_id = ?d) " . $dc;
                $q_args = array_merge($q_args, $q_args_templ);
            }
            if (Calendar_Events::$calsettings->show_course == 1) {
                // agenda
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'ag.start', $datecond);
                $q .= "SELECT ag.id, CONCAT(c.title,': ',ag.title), ag.start, date_format(ag.start,'%Y-%m-%d') startdate, ag.duration, date_format(ag.start + time(ag.duration), '%Y-%m-%d %H:%i') `end`, content, 'course' event_group, 'event-info' class, 'agenda' event_type,  c.code course "
                        . "FROM agenda ag JOIN course_user cu ON ag.course_id=cu.course_id JOIN course c ON cu.course_id=c.id "
                        . "WHERE cu.user_id =?d AND (ag.visible = 1 OR cu.status = 1) AND ag.visible = 1 "
                        . $dc;
                $q_args = array_merge($q_args, $q_args_templ);

                // BigBlueButton
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'tc.start_date', $datecond);
                $q .= "SELECT tc.id, CONCAT(c.title,': ',tc.title), tc.start_date start, date_format(tc.start_date,'%Y-%m-%d') startdate, '00:00' duration, date_format(tc.start_date, '%Y-%m-%d %H:%i') `end`, tc.description content, 'course' event_group, 'event-info' class, 'teleconference' event_type,  c.code course "
                        . "FROM tc_session tc JOIN course_user cu ON tc.course_id=cu.course_id JOIN course c ON cu.course_id=c.id "
                        . "WHERE cu.user_id =?d AND tc.active='1' "
                        . $dc;
                $q_args = array_merge($q_args, $q_args_templ);
            }
            if (Calendar_Events::$calsettings->show_deadline == 1) {
                // assignments
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'ass.deadline', $datecond);
                $q .= "SELECT ass.id, CONCAT(c.title,': ',ass.title), ass.deadline start, date_format(ass.deadline,'%Y-%m-%d') startdate, '00:00' duration, date_format(ass.deadline, '%Y-%m-%d %H:%i') `end`, concat(ass.description,'\n','(deadline: ',deadline,')') content, 'deadline' event_group, 'event-important' class, 'assignment' event_type, c.code course "
                        . "FROM assignment ass JOIN course_user cu ON ass.course_id=cu.course_id  JOIN course c ON cu.course_id=c.id LEFT JOIN assignment_to_specific ass_sp ON ass.id=ass_sp.assignment_id "
                        . "WHERE cu.user_id =?d AND (assign_to_specific = '0' OR  ass_sp.user_id = ?d OR cu.status = 1) AND ass.active = 1"
                        . $dc;
                $q_args = array_merge($q_args, array($user_id));
                $q_args = array_merge($q_args, $q_args_templ);

                // exercises
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'ex.end_date', $datecond);
                $q .= "SELECT ex.id, CONCAT(c.title,': ',ex.title), ex.end_date start, date_format(ex.end_date,'%Y-%m-%d') startdate, '00:00' duration, date_format(ex.end_date + time('00:00'), '%Y-%m-%d %H:%i') `end`, concat(ex.description,'\n','(deadline: ',end_date,')') content, 'deadline' event_group, 'event-important' class, 'exercise' event_type, c.code course "
                        . "FROM exercise ex JOIN course_user cu ON ex.course_id=cu.course_id  JOIN course c ON cu.course_id=c.id "
                        . "WHERE cu.user_id =?d AND (ex.public = 1 OR cu.status = 1) AND ex.active = 1"
                        . $dc;
                $q_args = array_merge($q_args, $q_args_templ);
            }
        }
        if (empty($q)) {
            return null;
        }
        $q .= " ORDER BY start, event_type";

        return Database::get()->queryArray($q, $q_args);

        /*if ($eventtypes == "all") {
            if (!is_null($startdate) && !is_null($enddate)) {
                return Database::get()->queryArray($q, $user_id, $startdate, $enddate, $user_id, $startdate, $enddate);
            }
            elseif (!is_null($startdate)) {
                return Database::get()->queryArray($q, $user_id, $startdate, $user_id, $startdate);
            }
            else{
                return Database::get()->queryArray($q, $user_id, $user_id);
            }
        }
        else{
            if (!is_null($startdate) && !is_null($enddate)) {
                return Database::get()->queryArray($q, $user_id, $startdate, $enddate);
            }
            elseif (!is_null($startdate)) {
                return Database::get()->queryArray($q, $user_id, $startdate);
            }
            else{
                return Database::get()->queryArray($q, $user_id);
            }
        }*/
    }



    /**
     * Get events count for a given user
     * @param int $user_id if empty the session user is assumed
     * @return int
     */
    public static function count_user_events($user_id = NULL) {
        global $uid;
        if (is_null($user_id)) {
            $user_id = $uid;
        }
        return Database::get()->querySingle("SELECT COUNT(*) AS count FROM personal_calendar WHERE user_id = ?d", $user_id)->count;
    }


    /**
     * Inserts new event and logs the action
     * @param string $title event title
     * @param text $content event description
     * @param string $start event start date time as "yyyy-mm-dd hh:mm:ss"
     * @param string $duration as "hhh:mm:ss"
     * @param array $recursion event recursion period as array('unit'=>'D|W|M', 'repeat'=>number to multiply time unit, 'end'=>'YYYY-mm-dd')
     * @param string $reference_obj_id refernced object by note containing object type (from $ref_object_types) and object id (is in the corresponding db table), e.g., video_link:5
     * @return int $eventid which is the id of the new event
     */
    public static function add_event($title, $content, $start, $duration, $recursion = NULL, $reference_obj_id = NULL, $admin_event_visibility = null) {
        global $uid, $langNotValidInput, $is_admin;
        $refobjinfo = References::get_ref_obj_field_values($reference_obj_id);
        // insert
        $period = "";
        $enddate = null;
        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $title = trim($title);
        if (empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start))) {
            return array('success' => false, 'message' => $langNotValidInput);
        }
        $start = $d1->format('Y-m-d H:i');
        if (!empty($recursion)) {
            $period = "P".$recursion['repeat'].$recursion['unit'];
            $enddate = $recursion['end'];
            $d1 = DateTime::createFromFormat('d-m-Y', $enddate);
            if (!($d1 && $d1->format('d-m-Y') == $enddate)) {
               return array('success' => false, 'message' => $langNotValidInput);
            } else {
                $enddate = $d1->format('Y-m-d H:i');
            }
        }

        // Make sure $duration has both hours and minutes part
        $parts = explode(':', $duration);
        if (empty($parts[0])) {
            $parts[0] = '0';
        }
        if (!isset($parts[1])) {
            $duration = '0:' . $parts[0];
        } else {
            if (empty($parts[1])) {
                $parts[1] = '00';
            }
            $duration = $parts[0] . ':' . $parts[1];
        }

        if (is_null($admin_event_visibility)) {
            $eventid = Database::get()->query("INSERT INTO personal_calendar "
                . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, duration = ?t, "
                . "recursion_period = ?s, recursion_end = ?t, "
                . "reference_obj_module = ?d, reference_obj_type = ?s, reference_obj_id = ?d, reference_obj_course = ?d",
                purify($content), $title, $uid, $start, $duration, $period, $enddate, $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'])->lastInsertID;
            if (isset($eventid) && !is_null($eventid)) {
               Database::get()->query("UPDATE personal_calendar SET source_event_id = id WHERE id = ?d",$eventid);
            }
        }
        elseif ($is_admin) {
            $eventid = Database::get()->query("INSERT INTO admin_calendar "
                . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, duration = ?t, "
                . "recursion_period = ?s, recursion_end = ?t, "
                . "visibility_level = ?d",
                purify($content), $title, $uid, $start, $duration, $period, $enddate, $admin_event_visibility)->lastInsertID;
            if (isset($eventid) && !is_null($eventid)) {
               Database::get()->query("UPDATE admin_calendar SET source_event_id = id WHERE id = ?d",$eventid);
            }
        }


        /* Additional events generated by recursion */
        if (isset($eventid) && !is_null($eventid) && !empty($recursion)) {
            $sourceevent = $eventid;
            $interval = new DateInterval($period);
            $startdatetime = new DateTime($start);
            $enddatetime = new DateTime($recursion['end']." 23:59:59");
            $newdate = date_add($startdatetime, $interval);
            while($newdate <= $enddatetime) {
                if (is_null($admin_event_visibility)) {
                    $neweventid = Database::get()->query("INSERT INTO personal_calendar "
                        . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, duration = ?t, "
                        . "recursion_period = ?s, recursion_end = ?t, "
                        . "source_event_id = ?d, reference_obj_module = ?d, reference_obj_type = ?s, "
                        . "reference_obj_id = ?d, reference_obj_course = ?d",
                        purify($content), $title, $uid, $newdate->format('Y-m-d H:i'), $duration, $period, $enddate, $sourceevent, $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'])->lastInsertID;
                } else {
                    $neweventid = Database::get()->query("INSERT INTO admin_calendar "
                        . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, duration = ?t, "
                        . "recursion_period = ?s, recursion_end = ?t, "
                        . "source_event_id = ?d, visibility_level = ?d",
                        purify($content), $title, $uid, $newdate->format('Y-m-d H:i'), $duration, $period, $enddate, $sourceevent, $admin_event_visibility)->lastInsertID;
                }
                $newdate = date_add($startdatetime, $interval);
            }
        }
        if (is_null($admin_event_visibility)) {
            Log::record(0, MODULE_ID_PERSONALCALENDAR, LOG_INSERT, array('user_id' => $uid, 'id' => $eventid,
            'title' => $title,
            'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
        } else {
            Log::record(0, MODULE_ID_ADMINCALENDAR, LOG_INSERT, array('user_id' => $uid, 'id' => $eventid,
            'title' => $title,
            'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
        }
        return array('success' => true, 'message' => '', 'event' => $eventid);
    }

    /**
     * Update existing event and logs the action
     * @param int $eventid id in table personal_calendar
     * @param string $title event title
     * @param string $start event datetime
     * @param text $content event details
     * @param boolean $recursivelly specifies if the update should be applied to all events of the group of recursive events or to the specific one
     * @param string $reference_obj_id refernced object by note. It contains the object type (from $ref_object_types) and object id (id in the corresponding db table), e.g., video_link:5
     */
    public static function update_event($eventid, $title, $start, $duration, $content, $recursivelly = false, $recursion = NULL, $reference_obj_id = NULL) {
        global $uid, $langNotValidInput;

        if($recursivelly && !is_null($recursion)){
            $oldrec = Calendar_Events::get_event_recursion($eventid, 'personal');
            $p = "P".$recursion['repeat'].$recursion['unit'];
            $e = DateTime::createFromFormat('d-m-Y', $recursion['end'])->format('Y-m-d');
            if($oldrec->recursion_period != $p || $oldrec->recursion_end != $e){
                Calendar_Events::delete_recursive_event($eventid, 'personal');
                return Calendar_Events::add_event($title, $content, $start, $duration, $recursion, $reference_obj_id);
            }
        }
        if(!is_null($recursion) && !Calendar_Events::is_recursive($eventid, 'personal')){
            Calendar_Events::delete_event($eventid, 'personal');
            return Calendar_Events::add_event($title, $content, $start, $duration, $recursion, $reference_obj_id);
        }

        $refobjinfo = References::get_ref_obj_field_values($reference_obj_id);

        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $title = trim($title);
        if (empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start))) {
            return array('success' => false, 'message' => $langNotValidInput);
        }

        $where_clause = ($recursivelly)? "WHERE source_event_id = ?d":"WHERE id = ?d";
        $startdatetimeformatted = ($recursivelly)? $d1->format('H:i'):$d1->format('Y-m-d H:i');
        $start_date_update_clause = ($recursivelly)? "start = CONCAT(date_format(start, '%Y-%m-%d '),?t), ":"start = ?t, ";
        Database::get()->query("UPDATE personal_calendar SET "
                . "title = ?s, "
                . $start_date_update_clause
                . "duration = ?t, "
                . "content = ?s, "
                . "reference_obj_module = ?d, "
                . "reference_obj_type = ?s, "
                . "reference_obj_id = ?d, "
                . "reference_obj_course = ?d "
                . $where_clause,
                $title, $startdatetimeformatted, $duration, purify($content), $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'], $eventid);

        Log::record(0, MODULE_ID_PERSONALCALENDAR, LOG_MODIFY, array('user_id' => $uid, 'id' => $eventid,
        'title' => $title,
        'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
        return array('success' => true, 'message' => '', 'event' => $eventid);
    }

    /**
     * Update existing group of recursive events and logs the action
     * @param int $eventid id in table personal_calendar
     * @param string $title event title
     * @param string $start event datetime
     * @param text $content event details
     * @param string $reference_obj_id refernced object by note. It contains the object type (from $ref_object_types) and object id (id in the corresponding db table), e.g., video_link:5
     */

    public static function update_recursive_event($eventid, $title, $start, $duration, $content, $recursion = NULL, $reference_obj_id = NULL) {
        global $langNotValidInput;
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM personal_calendar WHERE id=?d',$eventid);
        if ($rec_eventid) {
            return Calendar_Events::update_event($rec_eventid->source_event_id, $title, $start, $duration, $content, true, $recursion, $reference_obj_id);
        } else {
            return array('success' => false, 'message' => $langNotValidInput);
        }
    }

    /**
     * Update existing admin event and logs the action
     * @param int $eventid id in table note
     * @param string $title note title
     * @param text $content note body
     * @param int $visibility_level min user level to show this event to
     */
    public static function update_admin_event($eventid, $title, $start, $duration, $content, $visibility_level, $recursion = NULL, $recursivelly = false) {
        global $uid, $is_admin, $langNotValidInput, $langNotAllowed;
        if (!$is_admin) {
            return array('success' => false, 'message' => $langNotAllowed);
        }

        if($recursivelly && !is_null($recursion)){
            $oldrec = Calendar_Events::get_event_recursion($eventid, 'admin');
            $p = "P".$recursion['repeat'].$recursion['unit'];
            $e = DateTime::createFromFormat('d-m-Y', $recursion['end'])->format('Y-m-d');
            if($oldrec->recursion_period != $p || $oldrec->recursion_end != $e){
                Calendar_Events::delete_recursive_event($eventid, 'admin');
                return Calendar_Events::add_event($title, $content, $start, $duration, $recursion, null, $visibility_level);
            }
        }

        if(!is_null($recursion) && !Calendar_Events::is_recursive($eventid, 'admin'))
        {
            Calendar_Events::delete_event($eventid, 'admin');
            return Calendar_Events::add_event($title, $content, $start, $duration, $recursion, null, $visibility_level);
        }

        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $title = trim($title);
        if (empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start))) {
            return array('success' => false, 'message' => $langNotValidInput);
        }

        $where_clause = ($recursivelly)? "WHERE source_event_id = ?d":"WHERE id = ?d";
        $startdatetimeformatted = ($recursivelly)? $d1->format('H:i'):$d1->format('Y-m-d H:i');
        $start_date_update_clause = ($recursivelly)? "start = CONCAT(date_format(start, '%Y-%m-%d '),?t), ":"start = ?t, ";
        Database::get()->query("UPDATE admin_calendar SET "
                . "title = ?s, "
                . $start_date_update_clause
                . "duration = ?t, "
                . "content = ?s, "
                . "visibility_level = ?d "
                . $where_clause,
                $title, $startdatetimeformatted, $duration, purify($content), $visibility_level, $eventid);

        Log::record(0, MODULE_ID_ADMINCALENDAR, LOG_MODIFY, array('user_id' => $uid, 'id' => $eventid,
        'title' => $title,
        'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
        return array('success' => true, 'message' => '', 'event' => $eventid);
    }

    /**
     * Updates existing group of administrative recursive events and logs the action
     * @param int $eventid id in table admin_calendar
     * @param string $title event title
     * @param string $start event datetime
     * @param text $content event details
     * @param string $reference_obj_id refernced object by note. It contains the object type (from $ref_object_types) and object id (id in the corresponding db table), e.g., video_link:5
     */

    public static function update_recursive_admin_event($eventid, $title, $start, $duration, $content, $visibility_level, $recursion = NULL){
        global $langNotValidInput;
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM admin_calendar WHERE id=?d',$eventid);
        if ($rec_eventid) {
            return Calendar_Events::update_admin_event($rec_eventid->source_event_id, $title, $start, $duration, $content, $visibility_level, $recursion, true);
        } else {
            return array('success' => false, 'message' => $langNotValidInput);
        }
    }
    /**
     * Deletes an existing event and logs the action
     * @param int $eventid id in table personal_calendar
     * @param string $eventtype type of the event: personal|admin|course|deadline
     * @param boolean $recursivelly specifies if the update should be applied to all events of the group of recursive events or to the specific one
     */
    public static function delete_event($eventid, $eventtype, $recursivelly = false) {
        global $uid, $is_admin, $langNotAllowed;

        if ($eventtype == 'personal') {
            $event = Calendar_Events::get_event($eventid);
        } else {
            $event = Calendar_Events::get_admin_event($eventid);
        }

        if ($eventtype != 'personal' && $eventtype != 'admin') {
            return array('success' => false, 'message' => $langNotAllowed);
        }
        if ($eventtype == 'personal' && !$event) {
            return array('success' => false, 'message' => $langNotAllowed);
        }
        if ($eventtype == 'admin' && (!$is_admin)) {
            return array('success' => false, 'message' => $langNotAllowed);
        }
        $t = ($eventtype == 'personal')? 'personal_calendar':'admin_calendar';

        $where_clause = ($recursivelly)? "WHERE source_event_id = ?d":"WHERE id = ?d";
        $resp = Database::get()->query("DELETE FROM $t ".$where_clause, $eventid);

        $m = ($eventtype == 'personal')? MODULE_ID_PERSONALCALENDAR:MODULE_ID_ADMINCALENDAR;
        $content = ellipsize_html(canonicalize_whitespace(strip_tags($event->content)), 50, '+');
        Log::record(0, $m, LOG_DELETE, array('user_id' => $uid, 'id' => $eventid,
                                             'title' => $event->title,
                                             'content' => $content));
        return array('success' => true, 'message' => '', 'event' => $eventid);
    }

    /**
     * @brief delete a recursive event
     * @global type $langNotValidInput
     * @param type $eventid
     * @param type $eventtype
     * @return type
     */
    public static function delete_recursive_event($eventid, $eventtype) {
        global $langNotValidInput;
        $t = ($eventtype == 'personal')? 'personal_calendar':'admin_calendar';
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM '.$t.' WHERE id=?d', $eventid)->source_event_id;

        return Calendar_Events::delete_event($rec_eventid, $eventtype, true);

    }
    /**
     * Delete all events of a given user and logs the action
     * @param int $user_id if empty the session user is assumed
     */
    public static function delete_all_events($user_id = NULL) {
        global $uid;
        $resp = Database::get()->query("DELETE FROM personal_calendar WHERE user_id = ?", $uid);
        if ($resp) {
            Log::record(0, MODULE_ID_PERSONALCALENDAR, LOG_DELETE, array('user_id' => $uid, 'id' => 'all'));
            return array('success' => true, 'message' => '');
        } else {
            return array('success' => false, 'message' => 'Database error');
        }
    }

    /**
     * Get calendar events for a given user including personal and course events
     * @param string $scope month|week|day the calendar selected view
     * @param string $startdate mysql friendly formatted string representing the start of the time frame for which events are seeked
     * @param string $enddate mysql friendly formatted string representing the end of the time frame for which events are seeked
     * @param int $user_id if empty the session user is assumed
     * @return array of user events with details
     */
    public static function get_current_course_events($scope = "month", $startdate = null, $enddate = null) {

        global $course_id;
        //form date range condition
        $dateconditions = array("month" => "date_format(?t".',"%Y-%m") = date_format(start,"%Y-%m")',
                                "week" => "YEARWEEK(?t,1) = YEARWEEK(start,1)",
                                 "day" => "date_format(?t".',"%Y-%m-%d") = date_format(start,"%Y-%m-%d")');
        if (!is_null($startdate) && !is_null($enddate)) {
            $datecond = " AND start>=?t AND start<=?t";
        }
        elseif (!is_null($startdate)) {
            $datecond = " AND ";
            $datecond .= (array_key_exists($scope, $dateconditions))? $dateconditions[$scope]:$dateconditions["month"];
        }
        else{
            $datecond = "";
        }
        //retrieve events from various tables according to user preferences on what type of events to show
        $q = "";
        $q_args = array();
        $q_args_templ = array();
        $q_args_templ[] = $course_id;
        if (!is_null($startdate)) {
           $q_args_templ[] = $startdate;
        }
        if (!is_null($enddate)) {
           $q_args_templ[] = $enddate;
        }

        //agenda
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'ag.start', $datecond);
        $q .= "SELECT ag.id, ag.title, ag.start, date_format(ag.start, '%Y-%m-%d') startdate, ag.duration, date_format(ag.start + time(ag.duration), '%Y-%m-%d %H:%i') `end`, content, 'course' event_group, 'event-info' class, 'agenda' event_type,  c.code course "
                . "FROM agenda ag JOIN course c ON ag.course_id=c.id "
                . "WHERE ag.course_id =?d AND ag.visible = 1 "
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        //big blue button
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'bbb.start_date', $datecond);
        $q .= "SELECT bbb.id, bbb.title, bbb.start_date start, date_format(bbb.start_date, '%Y-%m-%d') startdate, '00:00' duration, "
                . "date_format(bbb.start_date + time('00:00:01'), '%Y-%m-%d %H:%i') `end`, bbb.description content, 'course' event_group, 'event-info' class, 'teleconference' event_type,  c.code course "
                . "FROM tc_session bbb JOIN course c ON bbb.course_id=c.id "
                . "WHERE bbb.course_id =?d AND bbb.active = '1' "
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);


        //assignements
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'ass.deadline', $datecond);
        $q .= "SELECT ass.id, ass.title, ass.deadline start, date_format(ass.deadline, '%Y-%m-%d') startdate, '00:00' duration, date_format(ass.deadline + time('00:00:01'), '%Y-%m-%d %H:%i') `end`, concat(ass.description, '\n', '(deadline: ', deadline, ')') content, 'deadline' event_group, 'event-important' class, 'assignment' event_type, c.code course "
                . "FROM assignment ass JOIN course c ON ass.course_id=c.id "
                . "WHERE ass.course_id =?d AND ass.active = 1 "
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        //exercises
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'ex.end_date', $datecond);
        $q .= "SELECT ex.id, ex.title, ex.end_date start, date_format(ex.end_date, '%Y-%m-%d') startdate, '00:00' duration, date_format(ex.end_date + time('00:00:01'), '%Y-%m-%d %H:%i') `end`, concat(ex.description, '\n', '(deadline: ', end_date, ')') content, 'deadline' event_group, 'event-important' class, 'exercise' event_type, c.code course "
                . "FROM exercise ex JOIN course c ON ex.course_id=c.id "
                . "WHERE ex.course_id =?d AND ex.active = 1 "
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        if (empty($q))
        {
            return null;
        }
        $q .= " ORDER BY start, event_type";
        return Database::get()->queryArray($q, $q_args);


    }
    /**************************************************************************/
    /*
     * Set of functions to be called from modules other than calendar
     * in order to associate events with module specific items
     */


    /**
     * Get personal events generally associated with a course. If no course is defined the current course is assumed.
     * @param int $cid the course id
     * @return array of events
     */
    public static function get_general_course_events($cid = NULL) {
       global $uid, $course_id;
       if (is_null($cid)) {
           $cid = $course_id;
       }
       return Database::get()->queryArray("SELECT id, title, content FROM personal_calendar WHERE user_id = ? AND reference_obj_type = 'course' AND reference_obj_id = ?", $uid, $cid);
    }

    /** Get personal events associated with a course generally or with specific items of the course
     * @param int $cid the course id
     * @return array array of events
     */
    public static function get_all_course_events($cid = NULL) {
       global $uid, $course_id;
       if (is_null($cid)) {
           $cid = $course_id;
       }
       return Database::get()->queryArray("SELECT id, title, content FROM personal_calendar WHERE user_id = ? AND reference_obj_course = ? ", $uid, $cid);
    }

    /**
     * Get events associated with items of a specific module of a course. If course is not specified the current one is assumed. If module is not specified the whole course is assumed.
     * @param int $module_id the id of the module
     * @param int $cid the course id
     * @return array of events
     */
    public static function get_module_events($cid = NULL, $module_id = NULL) {
       global $uid, $course_id;
       if (is_null($cid)) {
           $cid = $course_id;
       }
       if (is_null($module_id)) {
           return self::get_all_course_events($cid);
       }
       return Database::get()->queryArray("SELECT id, title, content FROM personal_calendar WHERE user_id = ? AND reference_obj_course = ? ", $uid, $cid);
    }

    /**
     * Get events associated with a specific item of a module of a course
     * If module or course are not specified the current ones are assumed.
     * Item type should be defined in case of a module being associated with more than one
     * object types (e.g., video module that contains videos and links to videos)
     * @param integer $item_id the item id in the database
     * @param integer $module_id the module id
     * @param integer $course_id the course id
     * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
     * @return array array of evnets associated with the item
     */
    public static function get_item_events($item_id, $module_id, $course_id, $item_type) {
       global $uid;
       return Database::get()->queryArray("SELECT id, title, content FROM note WHERE user_id = ? AND reference_obj_course = ? AND reference_obj_module = ? AND reference_obj_type = ? AND reference_obj_id = ?", $uid, $course_id, $module_id, $item_type, $item_id);
    }

     /**
      * A boolean function to check if some item listed by a module's page is
      * associated with any personal events for the current user.
      * @param integer $item_id the item id in the database
      * @param integer $module_id the module id
      * @param integer $course_id the course id
      * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
      * @return boolean true if events exist for the specified item or false otherwise
     */
    public static function item_has_events($item_id, $module_id, $course_id, $item_type) {
       return count_item_events($item_id, $module_id, $course_id, $item_type) > 0;
    }

    /**
      * A function to count the personal events associated with some item listed by a module's page, for the current user.
      * @param integer $item_id the item id in the database
      * @param integer $module_id the module id
      * @param integer $course_id the course id
      * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
      * @return object with `count` attribute containing the number of associated evnets with the item
     */
    public static function count_item_events($item_id, $module_id, $course_id, $item_type) {
        global $uid;
        return Database::get()->querySingle("SELECT count(*) `count` FROM note WHERE user_id = ? AND reference_obj_course = ?  AND reference_obj_course = ? AND reference_obj_module = ? AND reference_obj_type = ? AND reference_obj_id = ?", $uid, $course_id, $module_id, $item_type, $item_id);
    }


    public static function calendar_view($day = null, $month = null, $year = null, $calendar_type = null)
    {
        if (!is_null($calendar_type) && ($calendar_type == 'day' || $calendar_type == 'week' || $calendar_type == 'month')) {
            Calendar_Events::set_calendar_view_preference($calendar_type);
            $view_func = $calendar_type."_calendar";
        }
        else {
            $view_func = Calendar_Events::$calsettings->view_type."_calendar";
        }
        if (is_null($month) || is_null($year) || $month<0 || $month>12 || $year<1990 || $year>2099) {
            $today = getdate();
            $day = $today['mday'];
            $month = $today['mon'];
            $year = $today['year'];
        }
        if ($calendar_type == 'small') {
            return Calendar_Events::small_month_calendar($day, $month, $year);
        } else {
            return Calendar_Events::$view_func($day, $month, $year);
        }
    }

     /**
      * A function to generate month view of a set of events
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames
      * @return object with `count` attribute containing the number of associated events with the item
     */
   public static function month_calendar($day, $month, $year) {
       global $langDay_of_weekNames, $langMonthNames, $langToday, $langDay, $langWeek, $langMonth, $langViewShow;

       $calendar_content = "";
        //Handle leap year
        $numberofdays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if (($year % 400 == 0) or ($year % 4 == 0 and $year % 100 <> 0)) {
            $numberofdays[2] = 29;
        }

        $eventlist = Calendar_Events::get_calendar_events("month", "$year-$month-$day");

        $events = array();
        if ($eventlist) {
            foreach ($eventlist as $event) {
                $eventday = new DateTime($event->startdate);
                $eventday = $eventday->format('j');
                if (!array_key_exists($eventday,$events)) {
                        $events[$eventday] = array();
                }
                array_push($events[$eventday], $event);
            }
        }

        //Get the first day of the month
        $dayone = getdate(mktime(0, 0, 0, $month, 1, $year));
        //Start the week on monday
        $startdayofweek = $dayone['wday'] <> 0 ? ($dayone['wday'] - 1) : 6;

        $backward = array('month'=>$month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year - 1 : $year);
        $foreward = array('month'=>$month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year);

        $calendar_content .= '<div class="right" style="width:100%">'.$langViewShow.':&nbsp;'.
                '<a href="#" onclick="show_day(selectedday, selectedmonth, selectedyear);return false;">'.$langDay.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_week(selectedday, selectedmonth, selectedyear);return false;">'.$langWeek.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_month(selectedday, selectedmonth, selectedyear);return false;">'.$langMonth.'</a></div>';

        $calendar_content .= '<table width=100% class="title1">';
        $calendar_content .= "<tr>";
        $calendar_content .= '<td width="250"><a href="#" onclick="show_month(1,'.$backward['month'].','.$backward['year'].'); return false;">&laquo;</a></td>';
        $calendar_content .= "<td class='center'><b>{$langMonthNames['long'][$month-1]} $year</b></td>";
        $calendar_content .= '<td width="250" class="right"><a href="#" onclick="show_month(1,'.$foreward['month'].','.$foreward['year'].'); return false;">&raquo;</a></td>';
        $calendar_content .= "</tr>";
        $calendar_content .= "</table><br />";
        $calendar_content .= "<table class='table-default'><tr>";
        for ($ii = 1; $ii < 8; $ii++) {
            $calendar_content .= "<th class='center'>" . $langDay_of_weekNames['long'][$ii % 7] . "</th>";
        }
        $calendar_content .= "</tr>";
        $curday = -1;
        $today = getdate();

        while ($curday <= $numberofdays[$month]) {
            $calendar_content .= "<tr>";

            for ($ii = 0; $ii < 7; $ii++) {
                if (($curday == -1) && ($ii == $startdayofweek)) {
                    $curday = 1;
                }
                if (($curday > 0) && ($curday <= $numberofdays[$month])) {
                    $bgcolor = $ii < 5 ? "class='alert alert-danger'" : "class='odd'";
                    $dayheader = "$curday";
                    $class_style = "class=odd";
                    if (($curday == $today['mday']) && ($year == $today['year']) && ($month == $today['mon'])) {
                        $dayheader = "<b>$curday</b> <small>($langToday)</small>";
                        $class_style = "class='today'";
                    }
                    $calendar_content .= "<td height=50 width=14% valign=top $class_style><b>$dayheader</b>";
                    $thisDayItems = "";
                    if (array_key_exists($curday, $events)) {
                        foreach ($events[$curday] as $ev) {
                            $thisDayItems .= Calendar_Events::month_calendar_item($ev, Calendar_Events::$calsettings->{$ev->event_group."_color"});
                        }
                        $calendar_content .= "$thisDayItems</td>";
                    }
                    $curday++;
                } else {
                    $calendar_content .= "<td width=14%>&nbsp;</td>";
                }
            }
            $calendar_content .= "</tr>";
        }
        $calendar_content .= "</table>";

        /* Legend */
        $calendar_content .= Calendar_Events::calendar_legend();


        /***************************************  Bootstrap calendar  ******************************************************/

        $calendar_content .=  '<div id="bootstrapcalendar"></div>';

        return $calendar_content;
    }

    /**
      * A function to generate month view of a set of events small enough for the portfolio page
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames
      * @return object with `count` attribute containing the number of associated events with the item
      */
    public static function small_month_calendar($day, $month, $year) {
       return Calendar_Events::small_month_bootstrap_calendar();
    }

    /**
      * A function to generate week view of a set of events
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames
      * @return object with `count` attribute containing the number of associated events with the item
      */
    public static function week_calendar($day, $month, $year) {
        global $dateFormatLong, $langNoEvents, $langDay, $langWeek, $langMonth, $langViewShow;

        $calendar_content = "";
        if (is_null($day)) {
            $day = 1;
        }
        $nextweekdate = new DateTime("$year-$month-$day");
        $nextweekdate->add(new DateInterval('P1W'));
        $previousweekdate = new DateTime("$year-$month-$day");
        $previousweekdate->sub(new DateInterval('P1W'));

        $thisweekday = new DateTime("$year-$month-$day");
        $difffromMonday = ($thisweekday->format('w') == 0)? 6:$thisweekday->format('w')-1;
        $monday = $thisweekday->sub(new DateInterval('P'.$difffromMonday.'D')); //Sunday->1, ..., Saturday->7
        $weekdescription = ucfirst(claro_format_locale_date($dateFormatLong, $monday->getTimestamp()));
        $sunday = $thisweekday->add(new DateInterval('P6D'));
        $weekdescription .= ' - '.ucfirst(claro_format_locale_date($dateFormatLong, $sunday->getTimestamp()));
        $cursorday = $thisweekday->sub(new DateInterval('P6D'));

        $backward = array('day'=>$previousweekdate->format('d'), 'month'=>$previousweekdate->format('m'), 'year' => $previousweekdate->format('Y'));
        $foreward = array('day'=>$nextweekdate->format('d'), 'month'=>$nextweekdate->format('m'), 'year' => $nextweekdate->format('Y'));

        $calendar_content .= '<div class="right" style="width:100%">'.$langViewShow.':&nbsp;'.
                '<a href="#" onclick="show_day(selectedday, selectedmonth, selectedyear);return false;">'.$langDay.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_week(selectedday, selectedmonth, selectedyear);return false;">'.$langWeek.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_month(selectedday, selectedmonth, selectedyear);return false;">'.$langMonth.'</a></div>';

        $calendar_content .= "<table class='table-default'>";
        $calendar_content .= "<tr>";
        $calendar_content .= '<td width="25"><a href="#" onclick="show_week('.$backward['day'].','.$backward['month'].','.$backward['year'].'); return false;">&laquo;</a></td>';
        $calendar_content .= "<td class='center'><b>$weekdescription</b></td>";
        $calendar_content .= '<td width="25" class="right"><a href="#" onclick="show_week('.$foreward['day'].','.$foreward['month'].','.$foreward['year'].'); return false;">&raquo;</a></td>';
        $calendar_content .= "</tr>";
        $calendar_content .= "</table>";
        $eventlist = Calendar_Events::get_calendar_events("week", "$year-$month-$day");
        $numLine = 0;

        $calendar_content .= "<table class='table-default'>";

        $curday = 0;
        $now = getdate();
        $today = new DateTime($now['year'].'-'.$now['mon'].'-'.$now['mday']);
        $curstartddate = "";
        foreach ($eventlist as $thisevent) {
            if ($curstartddate != $thisevent->startdate) { //event date changed
                $thiseventdatetime = new DateTime($thisevent->startdate);
                while($cursorday < $thiseventdatetime) {
                    if ($cursorday == $today)
                        $class = 'today';
                    else
                        $class = 'monthLabel';
                    $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst(claro_format_locale_date($dateFormatLong, $cursorday->getTimestamp())) . "</b></td></tr>";
                    $calendar_content .= "<tr><td colspan='3'>$langNoEvents</td></tr>";
                    $cursorday->add(new DateInterval('P1D'));
                    $curday++;
                }
                if ($thiseventdatetime == $today)
                    $class = 'today';
                else
                    $class = 'monthLabel';
                $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst(claro_format_locale_date($dateFormatLong, strtotime($thisevent->startdate))) . "</b></td></tr>";
                if ($cursorday <= $thiseventdatetime) {
                    $cursorday->add(new DateInterval('P1D'));
                    $curday++;
                }
            }
            $calendar_content .= Calendar_Events::week_calendar_item($thisevent, 'even');
            $curstartddate = $thisevent->startdate;
            //$numLine++;
        }
        /* Fill with empty days*/
        for($i=$curday;$i<7;$i++) {
            if ($cursorday == $today)
                    $class = 'today';
                else
                    $class = 'monthLabel';
                $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst(claro_format_locale_date($dateFormatLong, $cursorday->getTimestamp())) . "</b></td></tr>";
                $calendar_content .= "<tr><td colspan='3'>$langNoEvents</td></tr>";
                $cursorday->add(new DateInterval('P1D'));
        }
        $calendar_content .= "</table>";
        /* Legend */
        $calendar_content .= Calendar_Events::calendar_legend();

        return $calendar_content;
    }

   /**
      * A function to generate day view of a set of events
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames
      * @return object with `count` attribute containing the number of associated events with the item
     */
   public static function day_calendar($day, $month, $year) {
       global $dateFormatLong, $langNoEvents, $langDay, $langWeek, $langMonth, $langViewShow;

        $calendar_content = "";
        if (is_null($day)) {
            $day = 1;
        }
        $nextdaydate = new DateTime("$year-$month-$day");
        $nextdaydate->add(new DateInterval('P1D'));
        $previousdaydate = new DateTime("$year-$month-$day");
        $previousdaydate->sub(new DateInterval('P1D'));

        $thisday = new DateTime("$year-$month-$day");
        $daydescription = ucfirst(claro_format_locale_date($dateFormatLong, $thisday->getTimestamp()));

        $backward = array('day'=>$previousdaydate->format('d'), 'month'=>$previousdaydate->format('m'), 'year' => $previousdaydate->format('Y'));
        $foreward = array('day'=>$nextdaydate->format('d'), 'month'=>$nextdaydate->format('m'), 'year' => $nextdaydate->format('Y'));

        $calendar_content .= '<div class="right" style="width:100%">'.$langViewShow.':&nbsp;'.
                '<a href="#" onclick="show_day(selectedday, selectedmonth, selectedyear);return false;">'.$langDay.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_week(selectedday, selectedmonth, selectedyear);return false;">'.$langWeek.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_month(selectedday, selectedmonth, selectedyear);return false;">'.$langMonth.'</a></div>';

        $calendar_content .= "<table class='table-default'>";
        $calendar_content .= "<tr>";
        $calendar_content .= '<td width="25"><a href="#" onclick="show_day('.$backward['day'].','.$backward['month'].','.$backward['year'].'); return false;">&laquo;</a></td>';
        $calendar_content .= "<td class='center'><b>$daydescription</b></td>";
        $calendar_content .= '<td width="25" class="right"><a href="#" onclick="show_day('.$foreward['day'].','.$foreward['month'].','.$foreward['year'].'); return false;">&raquo;</a></td>';
        $calendar_content .= "</tr>";
        $calendar_content .= "</table>";

        $eventlist = Calendar_Events::get_calendar_events("day", "$year-$month-$day");
        $calendar_content .= "<table class='table-default'>";

        $curhour = 0;
        $now = getdate();
        $today = new DateTime($now['year'].'-'.$now['mon'].'-'.$now['mday'].' '.$now['hours'].':'.$now['minutes']);
        if ($now['year'].'-'.$now['mon'].'-'.$now['mday'] == "$year-$month-$day") {
            $thisdayistoday = true;
        }
        else{
           $thisdayistoday = false;
        }
        $thishour = new DateTime($today->format('Y-m-d H:00'));
        $cursorhour = new DateTime("$year-$month-$day 00:00");
        $curstarthour = "";

        foreach ($eventlist as $thisevent) {
            $thiseventstart = new DateTime($thisevent->start);
            $thiseventhour = new DateTime($thiseventstart->format('Y-m-d H:00'));
            if ($curstarthour != $thiseventhour) { //event date changed
                while($cursorhour < $thiseventhour) {
                    if ($thisdayistoday && $thishour>=$cursorhour && intval($cursorhour->diff($thishour,true)->format('%h'))<6)
                        $class = 'today';
                    else
                        $class = 'monthLabel';
                    $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst($cursorhour->format('H:i')) . "</b></td></tr>";
                    if (intval($cursorhour->diff($thiseventhour,true)->format('%h'))>6) {
                        $calendar_content .= "<tr><td colspan='3'>$langNoEvents</td></tr>";
                    }
                    $cursorhour->add(new DateInterval('PT6H'));
                    $curhour += 6;
                }

                if ($thisdayistoday && $thishour>=$cursorhour && intval($cursorhour->diff($thishour,true)->format('%h'))<6)
                    $class = 'today';
                else
                    $class = 'monthLabel';
                //No hour tr for the event
                //$calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst($thiseventhour->format('H:i')) . "</b></td></tr>";
                if ($cursorhour <= $thiseventhour) {
                    $cursorhour->add(new DateInterval('PT6H'));
                    $curhour += 6;
                }
            }
            $calendar_content .= Calendar_Events::day_calendar_item($thisevent, 'even');
            $curstarthour = $thiseventhour;
            //$numLine++;
        }
        /* Fill with empty days*/
        for($i=$curhour;$i<24;$i+=6) {
            if ($thisdayistoday && $thishour>=$cursorhour && intval($cursorhour->diff($thishour,true)->format('%h'))<6)
                    $class = 'today';
                else
                    $class = 'monthLabel';
                $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst($cursorhour->format('H:i')) . "</b></td></tr>";
                $calendar_content .= "<tr><td colspan='3'>$langNoEvents</td></tr>";
                $cursorhour->add(new DateInterval('PT6H'));
        }
        $calendar_content .= "</table>";
        /* Legend */
        $calendar_content .= Calendar_Events::calendar_legend();

        return $calendar_content;
   }

   /**
      * A function to generate event block in month calendar
      * @param object $event event to format
      * @param string $color event color
      * @return html formatted item
     */
   public static function month_calendar_item($event, $color) {
       global $urlServer, $is_admin;

       $link = str_replace('thisid', $event->id, $urlServer.Calendar_Events::$event_type_url[$event->event_type]);
       if ($event->event_type != 'personal' && $event->event_type != 'admin') {
           $link = str_replace('thiscourse', $event->course, $link);
       }
       $formatted_calendar_item = "<a href=\"".$link."\"><div class=\"{$event->event_group}\" style=\"padding:2px;background-color:$color;\">".$event->title."</div></a>";
       if (!$is_admin && $event->event_group == 'admin') {
           $formatted_calendar_item = "<div class=\"{$event->event_group}\" style=\"padding:2px;background-color:$color;\">".$event->title."</div>";
       }
       return $formatted_calendar_item;
   }

   /**
      * A function to generate event block in week calendar
      * @param object $event event to format
      * @param string $color event color
      * @return html formatted item
     */
    public static function week_calendar_item($event, $class) {
        global $urlServer,$is_admin, $langDuration, $langAgendaNoTitle, $langModify, $langDelete, $langHour, $langConfirmDelete, $langReferencedObject;

        $formatted_calendar_item = "";
        $formatted_calendar_item .= "<tr $class>";
        $formatted_calendar_item .= "<td valign='top'><div class=\"legend_color\" style=\"float:left;margin:3px;height:16px;width:16px;background-color:".Calendar_Events::$calsettings->{$event->event_group."_color"}."\"></div></td>";
        $formatted_calendar_item .= "<td valign='top'>";
        $eventdate = strtotime($event->start);
        $formatted_calendar_item .= $langHour.": " . ucfirst(date('H:i', $eventdate));
        if ($event->duration != '') {
            $msg = "($langDuration: " . q($event->duration) . ")";
        } else {
            $msg = '';
        }
        $formatted_calendar_item .= "<br><b><div class='event'>";
        $link = str_replace('thisid', $event->id, $urlServer.Calendar_Events::$event_type_url[$event->event_type]);
        if ($event->event_type != 'personal' && $event->event_type != 'admin') {
            $link = str_replace('thiscourse', $event->course, $link);
        }
        if ($event->title == '') {
            $formatted_calendar_item .= $langAgendaNoTitle;
        } else {
            if (!$is_admin && $event->event_type == 'admin') {
                $formatted_calendar_item .= q($event->title);
            } else {
                $formatted_calendar_item .= "<a href=\"".$link."\">".q($event->title)."</a>";
            }
        }
        if ($event->event_type == "personal") {
            $fullevent = Calendar_Events::get_event($event->id);
            if ($reflink = References::item_link($fullevent->reference_obj_module, $fullevent->reference_obj_type, $fullevent->reference_obj_id, $fullevent->reference_obj_course)) {
                $formatted_calendar_item .= "</b> $msg ".standard_text_escape($event->content)
                    . "$langReferencedObject: "
                    .$reflink
                    . "</div></td>";
            }
        }
        else{
            $formatted_calendar_item .= "</b> $msg ".standard_text_escape($event->content). "</div></td>";
        }
        $formatted_calendar_item .= "<td class='right' width='70'>";
        if ($event->event_type == "personal" || ($event->event_type == "admin" && $is_admin)) {
            $formatted_calendar_item .= icon('fa-edit', $langModify, str_replace('thisid',$event->id, $urlServer.Calendar_Events::$event_type_url[$event->event_type])). "&nbsp;
                        ".icon('fa-times', $langDelete, "?delete=$event->id&et=$event->event_type", "onClick=\"return confirmation('$langConfirmDelete');\""). "&nbsp;";
        }
        $formatted_calendar_item .= "</td>";
        $formatted_calendar_item .= "</tr>";

       return $formatted_calendar_item;
   }

   /**
      * A function to generate event block in day calendar
      * @param object $event event to format
      * @param string $color event color
      * @return html formatted item
     */
    public static function day_calendar_item($event, $class) {
        global $urlServer, $is_admin, $langDuration, $langAgendaNoTitle, $langModify, $langDelete, $langHour, $langConfirmDelete, $langReferencedObject;

        $formatted_calendar_item = "";
        $formatted_calendar_item .= "<tr $class>";
        $formatted_calendar_item .= "<td valign='top'><div class=\"legend_color\" style=\"float:left;margin:3px;height:16px;width:16px;background-color:".Calendar_Events::$calsettings->{$event->event_group."_color"}."\"></div></td>";
        $formatted_calendar_item .= "<td valign='top'>";
        $eventdate = strtotime($event->start);
        $formatted_calendar_item .= $langHour.": " . ucfirst(date('H:i', $eventdate));
        if ($event->duration != '') {
            $msg = "($langDuration: " . q($event->duration) . ")";
        } else {
            $msg = '';
        }
        $formatted_calendar_item .= "<br><b><div class='event'>";
        $link = str_replace('thisid', $event->id, $urlServer.Calendar_Events::$event_type_url[$event->event_type]);
        if ($event->event_type != 'personal' && $event->event_type != 'admin') {
            $link = str_replace('thiscourse', $event->course, $link);
        }
        if ($event->title == '') {
            $formatted_calendar_item .= $langAgendaNoTitle;
        } else {
            if (!$is_admin && $event->event_type == 'admin') {
                $formatted_calendar_item .= q($event->title);
            } else {
                $formatted_calendar_item .= "<a href=\"".$link."\">".q($event->title)."</a>";
            }
        }
        if ($event->event_type == "personal") {
            $fullevent = Calendar_Events::get_event($event->id);
            if ($reflink = References::item_link($fullevent->reference_obj_module, $fullevent->reference_obj_type, $fullevent->reference_obj_id, $fullevent->reference_obj_course)) {
                $formatted_calendar_item .= "</b> $msg ".standard_text_escape($event->content)
                    . "$langReferencedObject: "
                    .$reflink
                    . "</div></td>";
            }
        }
        else{
            $formatted_calendar_item .= "</b> $msg ".standard_text_escape($event->content). "</div></td>";
        }
        $formatted_calendar_item .= "<td class='right' width='70'>";
        if ($event->event_type == "personal" || ($event->event_type == "admin" && $is_admin)) {
            $formatted_calendar_item .= icon('fa-edit', $langModify, str_replace('thisid',$event->id,Calendar_Events::$event_type_url[$event->event_type])). "&nbsp;
                        ".icon('fa-times', $langDelete, "?delete=$event->id&et=$event->event_type", "onClick=\"return confirmation('$langConfirmDelete');\""). "&nbsp;";
        }
        $formatted_calendar_item .= "</td>";
        $formatted_calendar_item .= "</tr>";

       return $formatted_calendar_item;
   }

   public static function calendar_legend() {
       $legend = "";

        /* Legend */
        $legend .= "<br/>"
                . "<table width=100% class='calendar_legend'>";
        $legend .= "<tr>";
        $legend .= "<td>";
        foreach (array_values(Calendar_Events::$event_groups) as $evtype)
        {
            global ${"langEvent".$evtype};
            $evtype_legendtext = ${"langEvent".$evtype};
            $legend .= "<div class=\"legend_item\" style=\"padding:3px;margin-left:10px;float:left;\"><div class=\"legend_color\" style=\"float:left;margin-right:3px;height:16px;width:16px;background-color:".Calendar_Events::$calsettings->{$evtype."_color"}."\"></div>".$evtype_legendtext."</div>";
        }
        $legend .= "<div style=\"clear:both;\"></both></td>";
        $legend .= "</tr>";
        $legend .= "</table>";
       return $legend;
   }

   /**
      * A function to generate event block in month calendar
      * @param object $event event to format
      * @param string $color event color
      * @return icalendar list of user events
     */
   public static function icalendar() {
       $ical = "BEGIN:VCALENDAR".PHP_EOL;
       $ical .= "VERSION:2.0".PHP_EOL;

       $show_personal_bak = Calendar_Events::$calsettings->show_personal;
       $show_course_bak = Calendar_Events::$calsettings->show_course;
       $show_deadline_bak = Calendar_Events::$calsettings->show_deadline;
       $show_admin_bak = Calendar_Events::$calsettings->show_admin;
       Calendar_Events::set_calendar_settings(1,1,1,1);
       Calendar_Events::get_calendar_settings();
       $eventlist = Calendar_Events::get_calendar_events();
       Calendar_Events::set_calendar_settings($show_personal_bak,$show_course_bak,$show_deadline_bak,$show_admin_bak);
       Calendar_Events::get_calendar_settings();

       $events = array();
       foreach ($eventlist as $event) {
           $ical .= "BEGIN:VEVENT".PHP_EOL;
           $startdatetime = new DateTime($event->start);
           $ical .= "DTSTART:".$startdatetime->format("Ymd\THis").PHP_EOL;
           $duration = new DateTime($event->duration);
           $ical .= "DURATION:".$duration->format("\P\TH\Hi\Ms\S").PHP_EOL;
           $ical .= "SUMMARY:[".strtoupper($event->event_group)."] ".$event->title.PHP_EOL;
           $ical .= "DESCRIPTION:".canonicalize_whitespace(strip_tags($event->content)).PHP_EOL;
           if ($event->event_group == 'deadline')
           {
               $ical .= "BEGIN:VALARM".PHP_EOL;
               $ical .= "TRIGGER:-PT24H".PHP_EOL;
               $ical .= "DURATION:PT10H".PHP_EOL;
               $ical .= "ACTION:DISPLAY".PHP_EOL;
               $ical .= "DESCRIPTION:DEADLINE REMINDER for ".canonicalize_whitespace(strip_tags($event->title)).PHP_EOL;
               $ical .= "END:VALARM".PHP_EOL;
           }
           $ical .= "END:VEVENT".PHP_EOL;
       }
       $ical .= "END:VCALENDAR".PHP_EOL;
       return $ical;
   }

   public static function bootstrap_events($from, $to) {
       global $urlServer, $uid, $course_id, $status;

       $fromdatetime = date("Y-m-d H:i:s",$from/1000);
       $todatetime = date("Y-m-d H:i:s",$to/1000);
       /* The type of calendar here defines how detailed the events are going to be. Default:month  */
       if (isset($course_id)) {
            $eventlist = Calendar_events::get_current_course_events("month", $fromdatetime, $todatetime);
       } else {
            $eventlist = Calendar_Events::get_calendar_events("month", $fromdatetime, $todatetime);
       }

       $events = array();
       if (count($eventlist > 0)) {
            foreach ($eventlist as $event) {
                $event->title = q($event->title);
                $event->content = q($event->content);
                $event->course = q($event->course);
                $startdatetime = new DateTime($event->start);
                $event->start = $startdatetime->getTimestamp()*1000;
                $event->start_hour = $startdatetime->format("H:i");
                $enddatetime = new DateTime($event->end);
                $event->end = $enddatetime->getTimestamp()*1000;
                $event->end_hour = $enddatetime->format("H:i");
                $event->url = str_replace('thisid', $event->id, $urlServer.Calendar_Events::$event_type_url[$event->event_type]);
                if ($event->event_type != 'personal' && $event->event_type != 'admin') {
                    $event->url = str_replace('thiscourse', $event->course, $event->url);
                }
                if ($event->event_type == 'teleconference') {
                    $participants = Database::get()->querySingle("SELECT participants FROM tc_session WHERE id = ?d", $event->id)->participants;
                    if (($status == USER_TEACHER) or ($participants == 0) or in_array($uid, explode(',', $participants))) {
                        array_push($events, $event);
                    }
                } else {
                    array_push($events, $event);
                }
            }
       }
       return json_encode(array('success'=>1, 'result'=>$events, 'cid'=>$course_id));
   }

   public static function small_month_bootstrap_calendar() {
       global $langNextMonth, $langPreviousMonth;

       $calendar = "<div id='cal-header' class='btn-group btn-group-justified btn-group-sm'>
                            <div class='btn-group btn-group-sm' style='width:20%;'><button type='button' class='btn btn-default' data-calendar-nav='prev'><i class='fa fa-caret-left'></i><span class='sr-only'>$langPreviousMonth</span></button></div>
                            <div class='btn-group btn-group-sm' style='width:60%;'><button id='current-month' type='button' class='btn btn-default' disabled='disabled'>&nbsp;</button></div>
                            <div class='btn-group btn-group-sm' style='width:20%;'><button type='button' class='btn btn-default' data-calendar-nav='next'><i class='fa fa-caret-right'></i><span class='sr-only'>$langNextMonth</span></button></div>
                    </div>";

       $calendar .= '<div id="bootstrapcalendar"></div><div class="clearfix"></div>';

        return $calendar;
   }

   public static function is_recursive($event_id, $eventtype){
        $t = ($eventtype == 'personal')? 'personal_calendar':'admin_calendar';
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM '.$t.' WHERE id=?d', $event_id);
        if($rec_eventid && $rec_eventid->source_event_id>0){
            $event_count = Database::get()->querySingle('SELECT COUNT(*) c FROM '.$t.' WHERE source_event_id=?d', $rec_eventid->source_event_id);
            if($event_count){
                return $event_count->c > 1;
            }
        }
        return false;
    }


    public static function get_event_recursion($eventid, $eventtype)
    {
        $t = ($eventtype == 'personal')? 'personal_calendar':'admin_calendar';
        return Database::get()->querySingle('SELECT recursion_period, recursion_end FROM '.$t.' WHERE id=?d',$eventid);
    }

}

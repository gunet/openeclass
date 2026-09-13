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
require_once 'include/lib/references.class.php';
require_once 'modules/request/functions.php';

class Calendar_Events {

    /** @staticvar array of event groups to group and style events in calendar
    */
    private static $event_groups = array('deadline', 'course', 'personal', 'admin');

    /** @staticvar array of urls to form links from events in calendar
    */
    private static $event_type_url = array(
            'personal' => 'main/personal_calendar/index.php?id=thisid',
            'admin' => 'main/personal_calendar/index.php?admin=1&id=thisid',
            'assignment' => 'modules/work/index.php?id=thisid&course=thiscourse',
            'exercise' => 'modules/exercise/exercise_submit.php?course=thiscourse&exerciseId=thisid',
            'agenda' => 'modules/agenda/index.php?id=thisid&course=thiscourse',
            'teleconference' => 'modules/tc/index.php?course=thiscourse',
            'request' => 'modules/request/index.php?course=thiscourse&id=thisid',
            'session' => 'modules/session/session_space.php?course=thiscourse&session=thisid');

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
     * Get calendar events for a given user including personal and course events
     * @param string $scope month|week|day the calendar selected view
     * @param string $startdate mysql friendly formatted string representing the start of the time frame for which events are seeked
     * @param string $enddate mysql friendly formatted string representing the end of the time frame for which events are seeked
     * @param int $user_id if empty the session user is assumed
     * @return array of user events with details
     */
    public static function get_calendar_events($scope = "month", $startdate = null, $enddate = null, $user_id = NULL) {
        global $uid;

        if (is_null($user_id)) {
            $user_id = $uid;
        }

        //form date range condition
        $dateconditions = array("month" => "date_format(?t".',"%Y-%m") = date_format(start,"%Y-%m")',
                                "week" => "YEARWEEK(?t,1) = YEARWEEK(start,1)",
                                 "day" => "date_format(?t".',"%Y-%m-%d") = date_format(start,"%Y-%m-%d")');
        if (!is_null($startdate) && !is_null($enddate)) {
            $datecond = " AND start >= ?t AND start <= ?t";
        } elseif (!is_null($startdate)) {
            $datecond = " AND ";
            $datecond .= (array_key_exists($scope, $dateconditions))? $dateconditions[$scope]:$dateconditions["month"];
        } else {
            $datecond = "";
        }
        $student_groups = array_map(function ($item) {
            return $item->group_id;
        }, Database::get()->queryArray('SELECT group_id
            FROM group_members, `group`
            WHERE group_id = `group`.id AND user_id = ?d', $uid));
        if (count($student_groups)) {
            $group_sql_template = 'OR group_id IN (' . implode(', ', array_fill(0, count($student_groups), '?d')) . ')';
            $group_sql_template2 = 'AND group_id IN (' . implode(', ', array_fill(0, count($student_groups), '?d')) . ')';
        } else {
            $group_sql_template = '';
            $group_sql_template2 = '';
        }

        $group_sql_template3 = implode(' OR ',
            array_map(function ($group) {
                return "participants REGEXP '\\\\b_{$group}\\\\b'";
            }, $student_groups));
        $group_sql_template3 .= ($group_sql_template3? ' OR ': '') .  " participants REGEXP '\\\\b{$uid}\\\\b'";

        // retrieve events from various tables according to user preferences on what type of events to show
        $q = '';
        $q_args = array();
        $q_args_templ = array($user_id);
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
                $q .= "SELECT id, title, start, date_format(start,'%Y-%m-%d') startdate, duration, date_format(addtime(start, time(duration)), '%Y-%m-%d %H:%i') `end`, content, 'personal' event_group, 'event-special' class, 'personal' event_type, null as course FROM personal_calendar pc "
                        . "WHERE user_id = ?d " . $dc;
                $q_args = array_merge($q_args, $q_args_templ);
            }
            if (Calendar_Events::$calsettings->show_admin == 1) {
                //admin
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'adm.start', $datecond);
                $q .= "SELECT id, title, start, date_format(start, '%Y-%m-%d') startdate, duration, date_format(addtime(start, time(duration)), '%Y-%m-%d %H:%i') `end`, content, 'admin' event_group, 'event-success' class, 'admin' event_type, null as course FROM admin_calendar adm "
                        . "WHERE visibility_level >= ?d " . $dc;
                $q_admin_events_args = $q_args_templ;
                $q_admin_events_args[0] = Calendar_Events::get_user_visibility_level();
                $q_args = array_merge($q_args, $q_admin_events_args);
            }
            if (Calendar_Events::$calsettings->show_course == 1) {
                // agenda
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'ag.start', $datecond);
                $q .= "SELECT ag.id, CONCAT(c.title,': ',ag.title), ag.start, date_format(ag.start,'%Y-%m-%d') startdate, ag.duration, date_format(addtime(ag.start, time(ag.duration)), '%Y-%m-%d %H:%i') `end`, content, 'course' event_group, 'event-info' class, 'agenda' event_type,  c.code course "
                        . "FROM agenda ag JOIN course_user cu ON ag.course_id=cu.course_id "
                        . "JOIN course c ON cu.course_id=c.id "
                        . "JOIN course_module cm ON c.id=cm.course_id "
                        . "WHERE cu.user_id = ?d "
                        . "AND ag.visible = 1 "
                        . "AND cm.module_id = " . MODULE_ID_AGENDA . " "
                        . "AND cm.visible = 1 "
                        . "AND c.visible != " . COURSE_INACTIVE . " "
                        . "AND (c.start_date IS NULL OR c.start_date < " . DBHelper::timeAfter() . ") "
                        . "AND (c.end_date IS NULL OR c.end_date > " . DBHelper::timeAfter() . ") "
                        . $dc;
                $q_args = array_merge($q_args, $q_args_templ);

                // BigBlueButton
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'tc.start_date', $datecond);
                $q .= "SELECT tc.id, CONCAT(c.title,': ',tc.title), tc.start_date start, date_format(tc.start_date,'%Y-%m-%d') startdate, '00:00' duration, date_format(tc.start_date, '%Y-%m-%d %H:%i') `end`, tc.description content, 'course' event_group, 'event-info' class, 'teleconference' event_type,  c.code course "
                        . "FROM tc_session tc JOIN course_user cu ON tc.course_id = cu.course_id "
                        . "JOIN course c ON cu.course_id=c.id "
                        . "WHERE cu.user_id = ?d "
                        . "AND tc.active = '1' "
                        . "AND c.visible != " . COURSE_INACTIVE . " "
                        . "AND (c.start_date IS NULL OR c.start_date < " . DBHelper::timeAfter() . ") "
                        . "AND (c.end_date IS NULL OR c.end_date > " . DBHelper::timeAfter() . ") "
                        . "AND (cu.status = 1 OR
                                cu.editor = 1 OR
                                cu.reviewer = 1 OR
                                tc.participants = 0 OR
                                $group_sql_template3) "
                        . $dc;
                $q_args = array_merge($q_args, $q_args_templ);

                // session
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'ms.start', $datecond);
                $q .= "SELECT ms.id, CONCAT(c.title,': ',ms.title), ms.start start, date_format(ms.start,'%Y-%m-%d') startdate, '00:00' duration, date_format(ms.start, '%Y-%m-%d %H:%i') `end`, ms.comments content, 'course' event_group, 'event-info' class, 'session' event_type,  c.code course "
                        . "FROM mod_session ms JOIN course_user cu ON ms.course_id = cu.course_id "
                        . "JOIN course c ON cu.course_id=c.id "
                        . "WHERE cu.user_id = ?d AND ms.visible = '1' "
                        . "AND c.visible != " . COURSE_INACTIVE . " "
                        . "AND (c.start_date IS NULL OR c.start_date < " . DBHelper::timeAfter() . ") "
                        . "AND (c.end_date IS NULL OR c.end_date > " . DBHelper::timeAfter() . ") "
                        . "AND ((ms.creator = $uid) OR (ms.id IN (SELECT session_id FROM mod_session_users WHERE participants = $uid AND is_accepted = 1)))"
                        . $dc;
                $q_args = array_merge($q_args, $q_args_templ);

                // requests
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'rfd.data', $datecond);
                $q .= "SELECT req.id, concat(c.title, ?s, req.title), concat(rfd.data, ' 08:00:00') start, rfd.data startdate, '00:00' duration, concat(rfd.data, ' 08:00:01') `end`, concat(req.description, '\n', '(deadline: ', rfd.data, ')') content, 'course' event_group, 'event-info' class, 'request' event_type, c.code course "
                        . "FROM request req JOIN course c ON req.course_id = c.id
                                JOIN request_field_data rfd ON rfd.request_id = req.id
                                JOIN request_field rf ON rf.id = rfd.field_id
                                LEFT JOIN request_watcher rw ON req.id = rw.request_id
                           WHERE req.state NOT IN (?d, ?d)
                                AND (req.creator_id = ?d OR rw.user_id = ?d) "
                        . $dc;
                $q_args = array_merge($q_args, [': ' . trans('langSingleRequest') . ': ',
                    REQUEST_STATE_LOCKED, REQUEST_STATE_CLOSED, $uid], $q_args_templ);
            }
            if (Calendar_Events::$calsettings->show_deadline == 1) {
                // assignments
                if (!empty($q)) {
                    $q .= " UNION ";
                }

                $dc = str_replace('start', 'ass.deadline', $datecond);
                $q .= "SELECT ass.id, CONCAT(c.title,': ',ass.title), ass.deadline start, date_format(ass.deadline,'%Y-%m-%d') startdate, '00:00' duration, date_format(ass.deadline, '%Y-%m-%d %H:%i') `end`, concat(ass.description,'\n','(deadline: ',deadline,')') content, 'deadline' event_group, 'event-important' class, 'assignment' event_type, c.code course "
                        . "FROM assignment ass JOIN course_user cu ON ass.course_id=cu.course_id "
                        . "JOIN course c ON cu.course_id=c.id LEFT JOIN assignment_to_specific ass_sp ON ass.id=ass_sp.assignment_id "
                        . "WHERE cu.user_id = ?d " . $dc
                        . "AND (assign_to_specific = 0 OR
                            ass.id IN
                            (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                UNION
                            SELECT assignment_id FROM assignment_to_specific
                               WHERE group_id != 0 $group_sql_template2)
                                OR cu.status = 1) "
                        . "AND ass.active = 1 "
                        . "AND c.visible != " . COURSE_INACTIVE . " "
                        . "AND (c.start_date IS NULL OR c.start_date < " . DBHelper::timeAfter() . ") "
                        . "AND (c.end_date IS NULL OR c.end_date > " . DBHelper::timeAfter() . ") ";
                $q_args = array_merge($q_args, $q_args_templ, array($user_id), $student_groups);

                // exercises
                if (!empty($q)) {
                    $q .= " UNION ";
                }
                $dc = str_replace('start', 'ex.end_date', $datecond);
                $q .= "SELECT ex.id, CONCAT(c.title,': ',ex.title), ex.end_date start, date_format(ex.end_date,'%Y-%m-%d') startdate, '00:00' duration, date_format(addtime(ex.end_date, time('00:00')), '%Y-%m-%d %H:%i') `end`, concat(ex.description,'\n','(deadline: ',ex.end_date,')') content, 'deadline' event_group, 'event-important' class, 'exercise' event_type, c.code course "
                        . "FROM exercise ex JOIN course_user cu ON ex.course_id=cu.course_id "
                        . "JOIN course c ON cu.course_id=c.id LEFT JOIN exercise_to_specific ex_sp ON ex.id = ex_sp.exercise_id "
                        . "WHERE cu.user_id = ?d " . $dc
                        . "AND ex.public = 1 AND ex.active = 1 AND (assign_to_specific = 0 OR ex_sp.user_id = ?d $group_sql_template) "
                        . "AND c.visible != " . COURSE_INACTIVE . " "
                        . "AND (c.start_date IS NULL OR c.start_date < " . DBHelper::timeAfter() . ") "
                        . "AND (c.end_date IS NULL OR c.end_date > " . DBHelper::timeAfter() . ")  ";
                $q_args = array_merge($q_args, $q_args_templ, array($user_id), $student_groups);
            }
        }
        if (empty($q)) {
            return null;
        }
        $q .= " ORDER BY start, event_type";
        return Database::get()->queryArray($q, $q_args);
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
    public static function add_event($title, $content, $start, $end, $duration, $recursion = NULL, $reference_obj_id = NULL, $admin_event_visibility = null) {
        global $uid, $langNotValidInput, $is_admin;

        $refobjinfo = References::get_ref_obj_field_values($reference_obj_id);
        // insert
        $period = "";
        $enddate = null;
        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $d3 = DateTime::createFromFormat('d-m-Y H:i', $end);
        $d4 = DateTime::createFromFormat('d-m-Y H:i:s', $end);
        $title = trim($title);
        if (empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start))) {
            return array('success' => false, 'message' => $langNotValidInput);
        }
        $start = $d1->format('Y-m-d H:i');
        $end = $d3->format('Y-m-d H:i');
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
                . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, end =?t, duration = ?t, "
                . "recursion_period = ?s, recursion_end = ?t, "
                . "reference_obj_module = ?d, reference_obj_type = ?s, reference_obj_id = ?d, reference_obj_course = ?d",
                purify($content), $title, $uid, $start, $end, $duration, $period, $enddate, $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'])->lastInsertID;
            if (isset($eventid) && !is_null($eventid)) {
               Database::get()->query("UPDATE personal_calendar SET source_event_id = id WHERE id = ?d",$eventid);
            }
        }
        elseif ($is_admin) {
            $eventid = Database::get()->query("INSERT INTO admin_calendar "
                . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, end = ?t, duration = ?t, "
                . "recursion_period = ?s, recursion_end = ?t, "
                . "visibility_level = ?d",
                purify($content), $title, $uid, $start, $end, $duration, $period, $enddate, $admin_event_visibility)->lastInsertID;
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

                $tmp_date = $newdate->format('Y-m-d');
                $tmp_time = date('H:i:s',strtotime($end));
                $end = date('Y-m-d H:i:s', strtotime("$tmp_date $tmp_time"));

                if (is_null($admin_event_visibility)) {
                    $neweventid = Database::get()->query("INSERT INTO personal_calendar "
                        . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, end = ?t, duration = ?t, "
                        . "recursion_period = ?s, recursion_end = ?t, "
                        . "source_event_id = ?d, reference_obj_module = ?d, reference_obj_type = ?s, "
                        . "reference_obj_id = ?d, reference_obj_course = ?d",
                        purify($content), $title, $uid, $newdate->format('Y-m-d H:i'), $end, $duration, $period, $enddate, $sourceevent, $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'])->lastInsertID;
                } else {
                    $neweventid = Database::get()->query("INSERT INTO admin_calendar "
                        . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, end = ?t, duration = ?t, "
                        . "recursion_period = ?s, recursion_end = ?t, "
                        . "source_event_id = ?d, visibility_level = ?d",
                        purify($content), $title, $uid, $newdate->format('Y-m-d H:i'), $end, $duration, $period, $enddate, $sourceevent, $admin_event_visibility)->lastInsertID;
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
    public static function update_event($eventid, $title, $start, $end, $duration, $content, $recursivelly = false, $recursion = NULL, $reference_obj_id = NULL) {
        global $uid, $langNotValidInput;

        if($recursivelly && !is_null($recursion)){
            $oldrec = Calendar_Events::get_event_recursion($eventid, 'personal');
            $p = "P".$recursion['repeat'].$recursion['unit'];
            $e = DateTime::createFromFormat('d-m-Y', $recursion['end'])->format('Y-m-d');
            if($oldrec->recursion_period != $p || $oldrec->recursion_end != $e){
                Calendar_Events::delete_recursive_event($eventid, 'personal');
                return Calendar_Events::add_event($title, $content, $start, $end, $duration, $recursion, $reference_obj_id);
            }
        }
        if(!is_null($recursion) && !Calendar_Events::is_recursive($eventid, 'personal')){
            Calendar_Events::delete_event($eventid, 'personal');
            return Calendar_Events::add_event($title, $content, $start, $end, $duration, $recursion, $reference_obj_id);
        }

        $refobjinfo = References::get_ref_obj_field_values($reference_obj_id);

        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $d3 = DateTime::createFromFormat('d-m-Y H:i', $end);
        $d4 = DateTime::createFromFormat('d-m-Y H:i:s', $end);
        $title = trim($title);
        if (empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start))) {
            return array('success' => false, 'message' => $langNotValidInput);
        }

        $where_clause = ($recursivelly)? "WHERE source_event_id = ?d":"WHERE id = ?d";
        $startdatetimeformatted = ($recursivelly)? $d1->format('H:i'):$d1->format('Y-m-d H:i');
        $start_date_update_clause = ($recursivelly)? "start = CONCAT(date_format(start, '%Y-%m-%d '),?t), ":"start = ?t, ";
        $end = ($recursivelly)? $d3->format('H:i'):$d3->format('Y-m-d H:i');
        $end_date_update_clause = ($recursivelly)? "end = CONCAT(date_format(end, '%Y-%m-%d '),?t), ":"end = ?t, ";
        Database::get()->query("UPDATE personal_calendar SET "
                . "title = ?s, "
                . $start_date_update_clause
                . $end_date_update_clause
                . "duration = ?t, "
                . "content = ?s, "
                . "reference_obj_module = ?d, "
                . "reference_obj_type = ?s, "
                . "reference_obj_id = ?d, "
                . "reference_obj_course = ?d "
                . $where_clause,
                $title, $startdatetimeformatted, $end, $duration, purify($content), $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'], $eventid);

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

    public static function update_recursive_event($eventid, $title, $start, $end, $duration, $content, $recursion = NULL, $reference_obj_id = NULL) {
        global $langNotValidInput;
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM personal_calendar WHERE id=?d',$eventid);
        if ($rec_eventid) {
            return Calendar_Events::update_event($rec_eventid->source_event_id, $title, $start, $end, $duration, $content, true, $recursion, $reference_obj_id);
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
    public static function update_admin_event($eventid, $title, $start, $end, $duration, $content, $visibility_level, $recursion = NULL, $recursivelly = false) {
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
                return Calendar_Events::add_event($title, $content, $start, $end, $duration, $recursion, null, $visibility_level);
            }
        }

        if(!is_null($recursion) && !Calendar_Events::is_recursive($eventid, 'admin'))
        {
            Calendar_Events::delete_event($eventid, 'admin');
            return Calendar_Events::add_event($title, $content, $start, $end, $duration, $recursion, null, $visibility_level);
        }

        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $d3 = DateTime::createFromFormat('d-m-Y H:i', $end);
        $d4 = DateTime::createFromFormat('d-m-Y H:i:s', $end);
        $title = trim($title);
        if (empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start))) {
            return array('success' => false, 'message' => $langNotValidInput);
        }

        $where_clause = ($recursivelly)? "WHERE source_event_id = ?d":"WHERE id = ?d";
        $startdatetimeformatted = ($recursivelly)? $d1->format('H:i'):$d1->format('Y-m-d H:i');
        $start_date_update_clause = ($recursivelly)? "start = CONCAT(date_format(start, '%Y-%m-%d '),?t), ":"start = ?t, ";
        $enddatetimeformatted = ($recursivelly)? $d3->format('H:i'):$d3->format('Y-m-d H:i');
        $end_date_update_clause = ($recursivelly)? "end = CONCAT(date_format(end, '%Y-%m-%d '),?t), ":"end = ?t, ";
        Database::get()->query("UPDATE admin_calendar SET "
                . "title = ?s, "
                . $start_date_update_clause
                . $end_date_update_clause
                . "duration = ?t, "
                . "content = ?s, "
                . "visibility_level = ?d "
                . $where_clause,
                $title, $startdatetimeformatted, $enddatetimeformatted, $duration, purify($content), $visibility_level, $eventid);

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

    public static function update_recursive_admin_event($eventid, $title, $start, $end, $duration, $content, $visibility_level, $recursion = NULL){
        global $langNotValidInput;
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM admin_calendar WHERE id=?d',$eventid);
        if ($rec_eventid) {
            return Calendar_Events::update_admin_event($rec_eventid->source_event_id, $title, $start, $end, $duration, $content, $visibility_level, $recursion, true);
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

        $t = ($eventtype == 'personal')? 'personal_calendar':'admin_calendar';
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM '.$t.' WHERE id=?d', $eventid)->source_event_id;

        return Calendar_Events::delete_event($rec_eventid, $eventtype, true);

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

        global $course_id, $uid;
        // form date range condition
        $dateconditions = array("month" => "date_format(?t".',"%Y-%m") = date_format(start,"%Y-%m")',
                                "week" => "YEARWEEK(?t,1) = YEARWEEK(start,1)",
                                "day" => "date_format(?t".',"%Y-%m-%d") = date_format(start,"%Y-%m-%d")');
        if (!is_null($startdate) && !is_null($enddate)) {
            $datecond = " AND start >= ?t AND start <= ?t";
        } elseif (!is_null($startdate)) {
            $datecond = " AND ";
            $datecond .= (array_key_exists($scope, $dateconditions))? $dateconditions[$scope]: $dateconditions['month'];
        } else {
            $datecond = "";
        }
        $student_groups = array_map(function ($item) {
            return $item->group_id;
        }, Database::get()->queryArray('SELECT group_id
            FROM group_members, `group`
            WHERE group_id = `group`.id AND course_id = ?d AND user_id = ?d', $course_id, $uid));
        if (count($student_groups)) {
            $group_sql_template = 'OR group_id IN (' . implode(', ', array_fill(0, count($student_groups), '?d')) . ')';
            $group_sql_template2 = 'AND group_id IN (' . implode(', ', array_fill(0, count($student_groups), '?d')) . ')';
        } else {
            $group_sql_template = '';
            $group_sql_template2 = '';
        }
        $group_sql_template3 = implode(' OR ',
            array_map(function ($group) {
                return "participants REGEXP '\\\\b_{$group}\\\\b'";
            }, $student_groups));
        $group_sql_template3 .= ($group_sql_template3? ' OR ': '') .  " participants REGEXP '\\\\b{$uid}\\\\b'";

        // retrieve events from various tables according to user preferences on what type of events to show
        $q = '';
        $q_args = array();
        $q_args_templ = array($course_id);
        if (!is_null($startdate)) {
           $q_args_templ[] = $startdate;
        }
        if (!is_null($enddate)) {
           $q_args_templ[] = $enddate;
        }

        // agenda
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'ag.start', $datecond);
        $q .= "SELECT ag.id, ag.title, ag.start, date_format(ag.start, '%Y-%m-%d') startdate, ag.duration, date_format(addtime(ag.start, time(ag.duration)), '%Y-%m-%d %H:%i') `end`, content, 'course' event_group, 'event-info' class, 'agenda' event_type,  c.code course "
                . "FROM agenda ag JOIN course c ON ag.course_id=c.id "
                . "WHERE ag.course_id =?d AND ag.visible = 1 "
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        // big blue button
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'tc.start_date', $datecond);
        $q .= "SELECT tc.id, tc.title, tc.start_date start, date_format(tc.start_date, '%Y-%m-%d') startdate, '00:00' duration, "
                . "date_format(addtime(tc.start_date, time('00:00:01')), '%Y-%m-%d %H:%i') `end`, tc.description content, 'course' event_group, 'event-info' class, 'teleconference' event_type,  c.code course "
                . "FROM tc_session tc JOIN course c ON tc.course_id=c.id JOIN course_user cu ON cu.course_id = c.id AND cu.user_id = $uid "
                . "WHERE tc.course_id =?d AND tc.active = '1' AND (cu.status = 1 OR cu.editor = 1 OR cu.reviewer = 1 OR $group_sql_template3) "
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);


        // assignments
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'ass.deadline', $datecond);
        $q .= "SELECT ass.id, ass.title, ass.deadline start, date_format(ass.deadline, '%Y-%m-%d') startdate, '00:00' duration, date_format(addtime(ass.deadline, time('00:00:01')), '%Y-%m-%d %H:%i') `end`, concat(ass.description, '\n', '(deadline: ', deadline, ')') content, 'deadline' event_group, 'event-important' class, 'assignment' event_type, c.code course "
                . "FROM assignment ass JOIN course c ON ass.course_id=c.id LEFT JOIN assignment_to_specific ass_sp ON ass.id=ass_sp.assignment_id "
                . "WHERE ass.course_id =?d AND ass.active = 1 "
                . $dc .
                "AND (assign_to_specific = 0 OR
                    ass.id IN
                        (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                            UNION
                        SELECT assignment_id FROM assignment_to_specific
                           WHERE group_id != 0 $group_sql_template2)
                    )";
        $q_args = array_merge($q_args, $q_args_templ, array($uid), $student_groups);

        // exercises
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'ex.end_date', $datecond);
        $q .= "SELECT ex.id, ex.title, ex.end_date start, date_format(ex.end_date, '%Y-%m-%d') startdate, '00:00' duration, date_format(addtime(ex.end_date, time('00:00:01')), '%Y-%m-%d %H:%i') `end`, concat(ex.description, '\n', '(deadline: ', ex.end_date, ')') content, 'deadline' event_group, 'event-important' class, 'exercise' event_type, c.code course "
                . "FROM exercise ex JOIN course c ON ex.course_id=c.id LEFT JOIN  exercise_to_specific ex_sp ON ex.id = ex_sp.exercise_id "
                . "WHERE ex.course_id = ?d AND ex.active = 1 "
                . $dc
                . "AND (assign_to_specific = 0 OR ex_sp.user_id = ?d $group_sql_template) ";
        $q_args = array_merge($q_args,  $q_args_templ, array($uid), $student_groups);

        // session
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'ms.start', $datecond);
        $q .= "SELECT ms.id, CONCAT(c.title,': ',ms.title), ms.start start, date_format(ms.start,'%Y-%m-%d') startdate, '00:00' duration, date_format(ms.start, '%Y-%m-%d %H:%i') `end`, ms.comments content, 'course' event_group, 'event-info' class, 'session' event_type,  c.code course "
                . "FROM mod_session ms JOIN course c ON ms.course_id=c.id "
                . "WHERE ms.course_id =?d AND ms.visible = 1 "
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        // requests
        if (!empty($q)) {
            $q .= " UNION ";
        }
        $dc = str_replace('start', 'rfd.data', $datecond);
        $q .= "SELECT req.id, concat(?s, req.title), concat(rfd.data, ' 08:00:00') start, rfd.data startdate, '00:00' duration, concat(rfd.data, ' 08:00:01') `end`, concat(req.description, '\n', '(deadline: ', rfd.data, ')') content, 'course' event_group, 'event-info' class, 'request' event_type, c.code course "
                . "FROM request req JOIN course c ON req.course_id = c.id
                        JOIN request_field_data rfd ON rfd.request_id = req.id
                        JOIN request_field rf ON rf.id = rfd.field_id
                        LEFT JOIN request_watcher rw ON req.id = rw.request_id
                   WHERE req.course_id = ?d AND req.state NOT IN (?d, ?d)
                        AND (req.creator_id = ?d OR rw.user_id = ?d) "
                . $dc;
        $q_args = array_merge($q_args, [trans('langSingleRequest') . ': ', $course_id,
            REQUEST_STATE_LOCKED, REQUEST_STATE_CLOSED, $uid], $q_args_templ);

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

    public static function calendar_view($day = null, $month = null, $year = null, $calendar_type = null)
    {
        if ($calendar_type == 'day' || $calendar_type == 'week' || $calendar_type == 'month') {
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
      * A function to generate month view of a set of events small enough for the portfolio page
      * @return object with `count` attribute containing the number of associated events with the item
      */
    public static function small_month_calendar() {
       return Calendar_Events::small_month_bootstrap_calendar();
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
           $ical .= "DESCRIPTION:".canonicalize_whitespace(strip_tags($event->content ?? '')).PHP_EOL;
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
       global $urlServer, $course_id;

       $fromdatetime = date("Y-m-d H:i:s",$from/1000);
       $todatetime = date("Y-m-d H:i:s",$to/1000);
       /* The type of calendar here defines how detailed the events are going to be. Default:month  */
       if (isset($course_id)) {
            $eventlist = Calendar_events::get_current_course_events("month", $fromdatetime, $todatetime);
       } else {
            $eventlist = Calendar_Events::get_calendar_events("month", $fromdatetime, $todatetime);
       }

       $events = array();
       if (isset($eventlist)) {
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
                $events[] = $event;
            }
       }
       return json_encode(array('success'=>1, 'result'=>$events, 'cid'=>$course_id));
   }

   public static function small_month_bootstrap_calendar() {
       global $langNextMonth, $langPreviousMonth, $course_code;

       if($course_code) {
            $calendar = "<div class='panel-heading p-0 d-flex justify-content-center align-items-center' style='min-height:39px;'>
                            <div id='cal-header' class='cal-header-course d-flex justify-content-center align-items-center w-100'>
                                <div class='btn-group w-100' role='group'>
                                    <button type='button' aria-label='$langPreviousMonth' class='btn btn-transparent d-flex justify-content-center align-items-center month-prev-btn' data-calendar-nav='prev'>
                                        <div class='btn-calendar-prev d-flex justify-content-start align-items-center'>
                                            <i class='fa-solid fa-chevron-left fa-lg'></i>
                                        </div>
                                        <span class='visually-hidden'>$langPreviousMonth</span>
                                    </button>
                                    <button id='current-month' type='button' class='btn btn-transparent pe-none'></button>
                                    <button type='button' aria-label='$langNextMonth' class='btn btn-transparent d-flex justify-content-center align-items-center month-next-btn' data-calendar-nav='next'>
                                        <div class='btn-calendar-next d-flex justify-content-end align-items-center'>
                                            <i class='fa-solid fa-chevron-right fa-lg'></i>
                                        </div>
                                        <span class='visually-hidden'>$langNextMonth</span>
                                    </button>
                                </div>
                            </div>
                        </div>";
       } else {
            $calendar = "<div class='panel-heading p-0 d-flex justify-content-center align-items-center' style='min-height:39px;'>
                            <div id='cal-header' class='cal-header-Portfolio d-flex justify-content-center align-items-center w-100'>
                                <div class='btn-group w-100' role='group'>
                                    <button type='button' aria-label='$langPreviousMonth' class='btn btn-transparent d-flex justify-content-center align-items-center month-prev-btn' data-calendar-nav='prev'>
                                        <div class='btn-calendar-prev d-flex justify-content-start align-items-center'>
                                            <i class='fa-solid fa-chevron-left fa-lg'></i>
                                        </div>
                                        <span class='visually-hidden'>$langPreviousMonth</span>
                                    </button>
                                    <button id='current-month' type='button' class='btn btn-transparent pe-none'></button>
                                    <button type='button' aria-label='$langNextMonth' class='btn btn-transparent d-flex justify-content-center align-items-center month-next-btn' data-calendar-nav='next'>
                                        <div class='btn-calendar-next d-flex justify-content-end align-items-center'>
                                            <i class='fa-solid fa-chevron-right fa-lg'></i>
                                        </div>
                                        <span class='visually-hidden'>$langNextMonth</span>
                                    </button>
                                </div>
                            </div>
                        </div>";
       }


       $calendar .= '<div class="panel-body-calendar panel-border-calendar pt-2 pb-3"><div id="bootstrapcalendar"></div></div>';

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

    public static function get_user_visibility_level() {
        global $uid, $session, $is_admin, $is_power_user, $is_usermanage_user, $is_departmentmanage_user;

        if (!$uid) {
            return 10;
        } elseif ($is_admin or $is_power_user or $is_usermanage_user or $is_departmentmanage_user) {
            return 0;
        } else {
            return $session->status;
        }
    }
}

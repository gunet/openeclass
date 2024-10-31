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
 * Eclass course calendar manipulation library
 *
 * @version 1.0
 * @absract
 * This class mainly contains static methods, so it could be defined simply
 * as a namespace.
 * However, it is created as a class for a possible need of instantiation of
 * event objects in the future. Another scenario could be the creation
 * of a set of abstract methods to be implemented seperatelly per module.
 *
 */

require_once 'include/log.class.php';
require_once 'include/lib/references.class.php';


    /********  Basic set of functions to be called from inside **************
     *********** personal events module that manipulates event items ********************/

    /**
     * Get event details given the event id
     * @param int $event_id id in table agenda
     * @return array event tuple
     */
    function get_event($event_id, $cid = null){
        global $course_id;
        if(is_null($cid)){
            $cid = $course_id;
        }
        return Database::get()->querySingle("SELECT * FROM agenda WHERE course_id = ?d AND $event_id = ?d", $course_id, $event_id);
    }

    /**
     * Get event details given the event id
     * @param int $eventid id in table agenda
     * @return the number of events
     */
    function count_course_events($cid = null){
        global $course_id;
        if(is_null($cid)){
            $cid = $course_id;
        }
        return Database::get()->querySingle("SELECT count(*) events_number FROM agenda WHERE course_id = ?d", $cid);
    }

    /**
     * Inserts new event and logs the action
     * @param string $title event title
     * @param text $content event description
     * @param string $start event start date time as "yyyy-mm-dd hh:mm:ss"
     * @param string $duration as "hhh:mm:ss"
     * @param array $recursion event recursion period as array('unit'=>'D|W|M', 'repeat'=>number to multiply time unit, 'end'=>'YYYY-mm-dd')
     * @return int $eventid which is the id of the new event
     */
    function add_event($title, $content, $start, $enddateEvent, $duration, $recursion = NULL){
        global $course_id, $langNotValidInput, $is_admin, $is_editor, $langNotAllowed;
        $eventids = array();
        // insert
        $period = "";
        $enddate = null;
        $multiple_events = false;
        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $d3 = DateTime::createFromFormat('d-m-Y H:i', $enddateEvent);
        $d4 = DateTime::createFromFormat('d-m-Y H:i:s', $enddateEvent);
        $title = trim($title);
        if(empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start)))
        {
            return array('success'=>false, 'message'=>$langNotValidInput);
        } else {
            $startdate = $d1->format('Y-m-d H:i');
            $enddateEventIn = $d3->format('Y-m-d H:i');
        }
        if(!empty($recursion))
        {
            $period = "P".$recursion['repeat'].$recursion['unit'];
            $enddate = $recursion['end'];
            $d1 = DateTime::createFromFormat('d-m-Y', $enddate);
            if(!($d1 && $d1->format('d-m-Y') == $enddate)){
               return array('success'=>false, 'message'=>$langNotValidInput);
            } else {
                $enddate = $d1->format('Y-m-d H:i');
            }
        }
        if (!preg_match('/[0-9]+(:[0-9]+){0,2}/', $duration)) {
            $duration = '0:00';
        }
        if($is_editor || $is_admin){
            $eventid = Database::get()->query("INSERT INTO agenda "
                . "SET content = ?s, title = ?s, course_id = ?d, start = ?t, end = ?t, duration = ?t, "
                . "recursion_period = ?s, recursion_end = ?t, visible = 1",
                purify($content), $title, $course_id, $startdate, $enddateEventIn, $duration, $period, $enddate)->lastInsertID;

            if(isset($eventid) && !is_null($eventid)){
                Database::get()->query("UPDATE agenda SET source_event_id = id WHERE id = ?d",$eventid);
                $eventids[] = $eventid;
            }
            $txt_content = ellipsize(canonicalize_whitespace(strip_tags($content)), 50, '+');

            /* Additional events generated by recursion */
            if(isset($eventid) && !is_null($eventid) && !empty($recursion)){
                $sourceevent = $eventid;
                $interval = new DateInterval($period);
                $startdatetime = new DateTime($startdate);
                $enddatetime = new DateTime($recursion['end']." 23:59:59");
                $newdate = date_add($startdatetime, $interval);
                while($newdate <= $enddatetime)
                {

                    $tmp_date = $newdate->format('Y-m-d');
                    $tmp_time = date('H:i:s',strtotime($enddateEvent));
                    $enddateEventIn = date('Y-m-d H:i:s', strtotime("$tmp_date $tmp_time"));

                    $multiple_events = true;
                    $neweventid = Database::get()->query("INSERT INTO agenda "
                        . "SET content = ?s, title = ?s, course_id = ?d, start = ?t, end = ?t, duration = ?t, "
                        . "recursion_period = ?s, recursion_end = ?t, "
                        . "source_event_id = ?d, visible = 1",
                        purify($content), $title, $course_id, $newdate->format('Y-m-d H:i'), $enddateEventIn, $duration, $period, $enddate, $sourceevent)->lastInsertID;
                    $newdate = date_add($startdatetime, $interval);
                    $eventids[] = $neweventid;
                }
                if(!$multiple_events){
                    Database::get()->query("UPDATE agenda SET recursion_period = NULL, recursion_end = NULL WHERE id = ?d",$eventid);
                }
                Log::record($course_id, MODULE_ID_AGENDA, LOG_INSERT, array('id' => $eventid,
                                     'date' => $start,
                                     'duration' => $duration,
                                     'title' => $title,
                                     'content' => $txt_content));
            }
            return array('success'=>true, 'message'=>'', 'event'=>$eventids);
        } else {
            return array('success'=>false, 'message'=>$langNotAllowed);
        }
    }

    function is_recursive($event_id){
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM agenda WHERE id=?d',$event_id);
        if($rec_eventid && $rec_eventid->source_event_id>0){
            $event_count = Database::get()->querySingle('SELECT count(*) c FROM agenda WHERE source_event_id=?d',$rec_eventid->source_event_id);
            if($event_count){
                return $event_count->c > 1;
            }
        }
        return false;
    }
    /**
     * Update existing event and logs the action
     * @param int $eventid id in table personal_calendar
     * @param string $title event title
     * @param string $start event datetime
     * @param text $content event details
     * @param boolean $recursivelly specifies if the update should be applied to all events of the group of recursive events or to the specific one
     */
    function update_event($eventid, $title, $start, $enddateEvent, $duration, $content, $recursion, $recursivelly = false){
        global $uid, $langNotValidInput, $course_id;

        if (!preg_match('/[0-9]+(:[0-9]+){0,2}/', $duration)) {
            $duration = '0:00';
        }

        if($recursivelly && !is_null($recursion)){
            delete_recursive_event($eventid);
            return add_event($title, $content, $start, $enddateEvent, $duration, $recursion);
        }

        if(!is_null($recursion) && !is_recursive($eventid))
        {
            delete_event($eventid);
            return add_event($title, $content, $start, $enddateEvent, $duration, $recursion);
        }

        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $d3 = DateTime::createFromFormat('d-m-Y H:i', $enddateEvent);
        $d4 = DateTime::createFromFormat('d-m-Y H:i:s', $enddateEvent);
        $title = trim($title);
        if(empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start)))
        {
            return array('success'=>false, 'message'=>$langNotValidInput);
        } else {
            $start = $d1->format('Y-m-d H:i');
            $enddateEventIn = $d3->format('Y-m-d H:i');
        }

        $where_clause = ($recursivelly)? "WHERE source_event_id = ?d AND course_id = ?d":"WHERE id = ?d AND course_id = ?d";
        $startdatetimeformatted = ($recursivelly)? $d1->format('H:i'):$d1->format('Y-m-d H:i');
        $start_date_update_clause = ($recursivelly)? "start = CONCAT(date_format(start, '%Y-%m-%d '),?t), ":"start = ?t, ";
        $enddatetimeformatted = ($recursivelly)? $d3->format('H:i'):$d3->format('Y-m-d H:i');
        $end_date_update_clause = ($recursivelly)? "end = CONCAT(date_format(end, '%Y-%m-%d '),?t), ":"end = ?t, ";
        Database::get()->query("UPDATE agenda SET "
            . "title = ?s, "
            . $start_date_update_clause
            . $end_date_update_clause
            . "duration = ?t, "
            . "content = ?s "
            . $where_clause,
            $title, $startdatetimeformatted, $enddatetimeformatted, $duration, purify($content), $eventid, $course_id);

        Log::record($course_id, MODULE_ID_AGENDA, LOG_MODIFY, array('user_id' => $uid, 'id' => $eventid,
        'title' => $title,
        'recursivelly' => $recursivelly,
        'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
        return array('success'=>true, 'message'=>'', 'event'=>$eventid);
    }

    /**
     * Update existing event and logs the action
     * @param int $eventid id in table personal_calendar
     * @param string $title event title
     * @param string $start event datetime
     * @param text $content event details
     */
    function update_recursive_event($eventid, $title, $start, $enddateEvent, $duration, $content, $recursion){
        global $langNotValidInput;

        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM agenda WHERE id=?d',$eventid);
        if($rec_eventid){
            return update_event($rec_eventid->source_event_id, $title, $start, $enddateEvent, $duration, $content, $recursion, true);
        } else {
            return array('success'=>false, 'message'=>$langNotValidInput);
        }
    }

    /**
     * Deletes an existing event and logs the action
     * @param int $noteid id in table note
     */
    function delete_event($eventid, $recursivelly = false){
        global $course_id, $is_admin, $is_editor, $langNotAllowed, $uid;
        $rec_event = Database::get()->querySingle('SELECT * FROM agenda WHERE id=?d AND course_id=?d',$eventid,$course_id);
        if($rec_event && ($is_admin || $is_editor)){
            $content = ellipsize_html(canonicalize_whitespace(strip_tags($rec_event->content)), 50, '+');
            $where_clause = ($recursivelly)? " WHERE source_event_id = ?d":" WHERE id = ?d";
            $d = Database::get()->query("DELETE FROM agenda ".$where_clause, $eventid);
            if($d){
                Log::record($course_id, MODULE_ID_AGENDA, LOG_DELETE, array('user_id' => $uid,
                    'id' => $eventid,
                    'title' => $rec_event->title,
                    'content' => $content,
                    'recursive'=>$recursivelly));
            }
            return array('success'=>true,'message'=>'', 'event'=>$eventid);
        } else {
            return array('success'=>true,'message'=>$langNotAllowed);
        }
    }

    function delete_recursive_event($eventid){
        global $langNotValidInput;

        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM agenda WHERE id=?d',$eventid);
        if($rec_eventid){
            return delete_event($rec_eventid->source_event_id, true);
        } else {
            return array('success'=>false, 'message'=>$langNotValidInput);
        }
    }
    /**
     * Delete all events of a given user and logs the action
     * @param int $user_id if empty the session user is assumed
     */
    function delete_all_events(){
        global $course_id, $uid, $is_editor, $is_admin;
        if($is_admin || $is_editor){
            $resp = Database::get()->query("DELETE FROM agenda WHERE course_id = ?", $course_id);
            if($resp){
                Log::record($course_id, MODULE_ID_AGENDA, LOG_DELETE, array('user_id' => $uid, 'course_id' => $course_id, 'id' => 'all'));
                return array('success'=>true,'message'=>'');
            } else {
               return array('success'=>false,'message'=>'Database error');
            }
        } else {
            return array('success'=>true,'message'=>$langNotAllowed);
        }
    }


    /**************************************************************************/
    /*
     * Set of functions to be called from modules other than calendar
     * in order to associate events with module specific items
     */

    function calendar_view($day = null, $month = null, $year = null, $calendar_type = null)
    {
        global $uid;
        if(!is_null($calendar_type) && ($calendar_type == 'day' || $calendar_type == 'week' || $calendar_type == 'month')){
            set_calendar_view_preference($calendar_type);
            $view_func = $calendar_type."_calendar";
        }
        else{
            $view_func = $calsettings->view_type."_calendar";
        }
        if(is_null($month) || is_null($year) || $month<0 || $month>12 || $year<1990 || $year>2099){
            $today = getdate();
            $day = $today['mday'];
            $month = $today['mon'];
            $year = $today['year'];
        }
        if($calendar_type == 'small'){
            return small_month_calendar($day, $month, $year);
        }
        else{
            return $view_func($day, $month, $year);
        }
    }


   /**
      * A function to generate event block in month calendar
      * @param object $event event to format
      * @param string $color event color
      * @return icalendar list of user events
     */
   function icalendar($cid){
       global $course_id;
       if(!isset($course_id)){
           $course_id=$cid;
       }
       $ical = "BEGIN:VCALENDAR".PHP_EOL;
       $ical .= "VERSION:2.0".PHP_EOL;

       $eventlist = get_course_events();
       foreach($eventlist as $event){
           $ical .= "BEGIN:VEVENT".PHP_EOL;
           $startdatetime = new DateTime($event->start);
           $ical .= "DTSTART:".$startdatetime->format("Ymd\THis").PHP_EOL;
           $duration = new DateTime($event->duration);
           $ical .= "DURATION:".$duration->format("\P\TH\Hi\Ms\S").PHP_EOL;
           $ical .= "SUMMARY:[".strtoupper($event->event_group)."] ".$event->title.PHP_EOL;
           $ical .= "DESCRIPTION:".canonicalize_whitespace(strip_tags($event->content)).PHP_EOL;
           if($event->event_group == 'deadline')
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

   function small_month_bootstrap_calendar()
   {
       global $langNext, $langPrevious;

       $calendar = '<div id="cal-header" class="btn-group btn-group-justified btn-group-sm">
                            <div class="btn-group btn-group-sm"><button type="button" class="btn btn-default" data-calendar-nav="prev">&larr; '.$langPrevious.'</button></div>
                            <div class="btn-group btn-group-sm"><button id="current-month" type="button" class="btn btn-default" disabled="disabled" aria-label="Disabled">&nbsp;</button></div>
                            <div class="btn-group btn-group-sm"><button type="button" class="btn btn-default" data-calendar-nav="next">'.$langNext.' &rarr;</button></div>
                    </div>';

       $calendar .= '<div id="bootstrapcalendar"></div><div class="clearfix"></div>';

        return $calendar;
   }


   function get_list_course_events($display = 'all', $sens = 'ASC') {
       global $is_editor, $course_id;

       $param = [ $course_id ];
       if ($display != 'all') {
           $extra_sql = "AND id = ?d";
           $param[] = $display;
       } else {
           $extra_sql = '';
        }
       $result = array();
       if ($is_editor) {
            $result = Database::get()->queryArray("SELECT id, title, content, start, duration, visible, recursion_period, recursion_end
                                    FROM agenda WHERE course_id = ?d $extra_sql
                                ORDER BY start " . $sens, $param);
        } else {
            $result = Database::get()->queryArray("SELECT id, title, content, start, duration, visible
                                    FROM agenda WHERE course_id = ?d $extra_sql
                                AND visible = 1 ORDER BY start " . $sens, $param);
        }
        return $result;

   }

   /***
    * @brief call event_list for displaying events if exist
    * @global type $langNoEvents
    * @param type $display
    * @param type $sens
    * @return type
    */
    function event_list_view($display = 'all', $sens = 'ASC'){
        global $langNoEvents;

        $events = get_list_course_events($display, 'DESC');

        if (is_array($events)) {
            return event_list($events, 'DESC');
        } else {
            return "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoEvents</span></div>";
        }
    }

    /**
     * @brief display event list
     * @param type $events
     * @param type $sens
     * @return string
     */
    function event_list($events, $sens, $type = '') {
        global $course_code, $is_editor, $langDateNow, $langMonths,
               $langHour, $langHoursSmall, $langDuration, $langAgendaNoTitle, $langDelete,
               $langConfirmDeleteEvent, $langConfirmDeleteRecursive, $langConfirmDeleteRecursiveEvents,
               $langEditChange, $langViewHide, $langViewShow, $id, $is_admin;

        $barMonth = '';
        $eventlist = "<div class='table-responsive mt-0'><table class='table-default'>";
        foreach ($events as $myrow) {
            $content = standard_text_escape($myrow->content);
            $d = strtotime($myrow->start);
            // month year label
            if ($barMonth != date("m", $d)) {
                $barMonth = date("m", $d);
                $barYear = date("Y", $d);
                $eventlist .= "<thead><tr>";
                $eventlist .= "<td colspan='2' class='monthLabel'>";
                $eventlist .= "<div><strong>" . $langMonths[$barMonth] . "&nbsp;" . $barYear . "</strong></div>";
                $eventlist .= "</td>";
                $eventlist .= "</tr></thead>";
            }
            $classvis = '';
            if ($is_editor) {
                if ($myrow->visible == 0) {
                    $classvis = 'class = "not_visible"';
                }
            }
            $eventlist .= "<tr $classvis>";
            if ($is_editor or $type == 'personal' or ($is_admin and $type == 'admin')) {
                $eventlist .= "<td>";
            } else {
                $eventlist .= "<td colspan='2'>";
            }
            if (($myrow->duration != '00:00:00') and ($myrow->duration != '')) {
                if ($myrow->duration == 1) {
                    $message = $langHour;
                } else {
                    $message = $langHoursSmall;
                }
                $msg = "($langDuration: " . q($myrow->duration) . " $message)";
            } else {
                $msg = '';
            }
            if ($myrow->title == '') {
                $eventlist .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$myrow->id'>$langAgendaNoTitle</a>";
            } else {
                $eventlist .= "<strong><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$myrow->id'>".q($myrow->title)."</a></strong> &nbsp;&nbsp;$msg";
            }

            $eventlist .= "<div><span class='day'>" . format_locale_date($d) . "</span></div>";
            if (isset($id)) {
                $eventlist .= "<br>";
                $eventlist .= "<div class='text-muted'>$content</div>";
            }
            $eventlist .= "</td>";
            if ($type == 'admin' and $is_admin == true) {
               $eventlist .= "<td class='option-btn-cell text-end'>";
               $eventlist .= action_button([
                   [ 'title' => $langEditChange,
                     'url' => "?admin=1&amp;modify=$myrow->id",
                     'icon' => 'fa-edit' ],
                   [ 'title' => $langConfirmDeleteRecursive,
                     'url' => "?delete=$myrow->id&amp;et=admin&amp;rep=yes",
                     'icon' => 'fa-xmark',
                     'class' => 'delete',
                     'confirm' => $langConfirmDeleteRecursiveEvents,
                     'show' => !(is_null($myrow->recursion_period) || is_null($myrow->recursion_end)) ],
                   [ 'title' => $langDelete,
                     'url' => "?delete=$myrow->id&amp;et=admin",
                     'icon' => 'fa-xmark',
                     'class' => 'delete',
                     'confirm' => $langConfirmDeleteEvent ],
               ]);
              $eventlist .= "</td>";
            } else {
               if ($is_editor) {
                    $eventlist .= "<td class='option-btn-cell text-end'>";
                    $eventlist .= action_button([
                        [ 'title' => $langEditChange,
                          'url' => "?course=$course_code&amp;id=$myrow->id&amp;edit=true",
                          'icon' => 'fa-edit' ],
                        [ 'title' => $myrow->visible ?   $langViewHide : $langViewShow,
                          'url' => "?course=$course_code&amp;id=$myrow->id&amp;" . ($myrow->visible? "mkInvisibl=true" : "mkVisibl=true"),
                          'icon' => $myrow->visible ? 'fa-eye-slash' : 'fa-eye' ],
                        [ 'title' => $langDelete,
                          'url' => "?course=$course_code&amp;id=$myrow->id&amp;delete=yes",
                          'icon' => 'fa-xmark',
                          'class' => 'delete',
                          'confirm' => $langConfirmDeleteEvent ],
                        [ 'title' => $langConfirmDeleteRecursive,
                          'url' => "?course=$course_code&amp;id=$myrow->id&amp;delete=yes&amp;rep=yes",
                          'icon' => 'fa-times-circle-o',
                          'class' => 'delete',
                          'confirm' => $langConfirmDeleteRecursiveEvents,
                          'show' => !(is_null($myrow->recursion_period) || is_null($myrow->recursion_end)) ],
                    ]);
                    $eventlist .= "</td>";
                } elseif ($type == 'personal') { // personal or admin event
                    $eventlist .= "<td class='option-btn-cell text-end'>";
                    $eventlist .= action_button([
                        [ 'title' => $langEditChange,
                          'url' => "?modify=$myrow->id",
                          'icon' => 'fa-edit' ],
                        [ 'title' => $langDelete,
                          'url' => "?delete=$myrow->id&et=$type",
                          'icon' => 'fa-xmark',
                          'class' => 'delete',
                          'confirm' => $langConfirmDeleteEvent ],
                        [ 'title' => $langConfirmDeleteRecursive,
                          'url' => "?delete=$myrow->id&et=$type&amp;rep=yes",
                          'icon' => 'fa-times-circle-o',
                          'class' => 'delete',
                          'confirm' => $langConfirmDeleteRecursiveEvents,
                          'show' => !(is_null($myrow->recursion_period) || is_null($myrow->recursion_end)) ],
                    ]);
                   $eventlist .= "</td>";
                }
           }
           $eventlist .= "</tr>";
       }
       $eventlist .= "</table></div>";
       return $eventlist;
   }


   /**
     * Get calendar events for a given course
     * @param string $scope month|week|day the calendar selected view
     * @param string $startdate mysql friendly formatted string representing the start of the time frame for which events are seeked
     * @param string $enddate mysql friendly formatted string representing the end of the time frame for which events are seeked
     * @return array of user events with details
     */
    function get_course_events($scope = "month", $startdate = null, $enddate = null){


        global $course_id, $is_editor;
        //form date range condition
        $dateconditions = array("month" => "date_format(?t".',"%Y-%m") = date_format(start,"%Y-%m")',
                                "week" => "YEARWEEK(?t,1) = YEARWEEK(start,1)",
                                 "day" => "date_format(?t".',"%Y-%m-%d") = date_format(start,"%Y-%m-%d")');
        if(!is_null($startdate) && !is_null($enddate)){
            $datecond = " AND start>=?t AND start<=?t";
        }
        elseif(!is_null($startdate)){
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
        if(!is_null($startdate)){
           $q_args_templ[] = $startdate;
        }
        if(!is_null($enddate)){
           $q_args_templ[] = $enddate;
        }

        //agenda
        if(!empty($q)){
            $q .= " UNION ";
        }
        if ($is_editor) {
            $q_extra = '';
        } else {
            $q_extra = "AND ag.visible = 1";
        }
        $dc = str_replace('start','ag.start',$datecond);
        $q .= "SELECT ag.id, ag.title, ag.start, date_format(ag.start,'%Y-%m-%d') startdate, ag.duration, date_format(addtime(ag.start, if(ag.duration = '', '0:00', ag.duration)), '%Y-%m-%d %H:%i') `end`, content, 'course' event_group, 'event-info' class, 'agenda' event_type,  c.code course "
                . "FROM agenda ag JOIN course c ON ag.course_id=c.id "
                . "WHERE ag.course_id =?d $q_extra"
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        //big blue button
        if(!empty($q)){
            $q .= " UNION ";
        }
        if ($is_editor) {
            $q_extra = '';
        } else {
            $q_extra = "AND tc.active = '1'";
        }
        $dc = str_replace('start','tc.start_date', $datecond);
        $q .= "SELECT tc.id, tc.title, tc.start_date start, date_format(tc.start_date,'%Y-%m-%d') startdate, '00:00' duration, date_format(tc.start_date + time('01:00:00'), '%Y-%m-%d %H:%i') `end`, tc.description content, 'course' event_group, 'event-special' class, 'teleconference' event_type,  c.code course "
                . "FROM tc_session tc JOIN course c ON tc.course_id = c.id "
                . "WHERE tc.course_id = ?d $q_extra"
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);


        //assignments
        if(!empty($q)){
            $q .= " UNION ";
        }
        if ($is_editor) {
            $q_extra = '';
        } else {
            $q_extra = "AND ass.active = 1";
        }
        $dc = str_replace('start','ass.deadline', $datecond);
        $q .= "SELECT ass.id, ass.title, ass.deadline start, date_format(ass.deadline,'%Y-%m-%d') startdate, '00:00' duration, date_format(ass.deadline + time('00:00'), '%Y-%m-%d %H:%i') `end`, concat(ass.description,'\n','(deadline: ',deadline,')') content, 'deadline' event_group, 'event-important' class, 'assignment' event_type, c.code course "
                . "FROM assignment ass JOIN course c ON ass.course_id=c.id "
                . "WHERE ass.course_id =?d $q_extra"
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        //exercises
        if(!empty($q)){
            $q .= " UNION ";
        }
        if ($is_editor) {
            $q_extra = '';
        } else {
            $q_extra = "AND ex.active = 1";
        }
        $dc = str_replace('start','ex.end_date',$datecond);
        $q .= "SELECT ex.id, ex.title, ex.end_date start, date_format(ex.end_date,'%Y-%m-%d') startdate, '00:00' duration, date_format(ex.end_date + time('00:00'), '%Y-%m-%d %H:%i') `end`, concat(ex.description,'\n','(deadline: ',ex.end_date,')') content, 'deadline' event_group, 'event-important' class, 'exercise' event_type, c.code course "
                . "FROM exercise ex JOIN course c ON ex.course_id=c.id "
                . "WHERE ex.course_id =?d $q_extra"
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        if(empty($q))
        {
            return null;
        }
        $q .= " ORDER BY start, event_type";
        return Database::get()->queryArray($q, $q_args);
    }

    /**
     * @param type $eventid
     * @param type $cid
     * @return type
     */

    function get_event_recursion($eventid, $cid)
    {
        return Database::get()->querySingle('SELECT recursion_period, recursion_end FROM agenda WHERE id=?d and course_id=?d',$eventid, $cid);
    }

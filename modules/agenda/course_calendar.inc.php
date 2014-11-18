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

require_once 'include/log.php';
require_once 'include/lib/references.class.php';


    /********  Basic set of functions to be called from inside **************
     *********** personal events module that manipulates event items ********************/

    /**
     * Get event details given the event id
     * @param int $eventid id in table agenda
     * @return array event tuple
     */
    function get_event($eventid, $cid = null){
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
     * Get calendar events for a given userincluding personal and course events
     * @param string $scope month|week|day the calendar selected view
     * @param string $agenda_events_only true|false show also events from other course modules or only agenda items
     * @param string $startdate mysql friendly formatted string representing the start of the time frame for which events are seeked
     * @param string $enddate mysql friendly formatted string representing the end of the time frame for which events are seeked
     * @param int $cid the course id, if empty the session course is assumed
     * @return array of user events with details
     */
    function get_calendar_events($scope = "month", $agenda_events_only = true, $startdate = null, $enddate = null, $cid = NULL){
        global $uid, $is_admin;
        global $course_id;
        if(is_null($cid)){
            $cid = $course_id;
        }
        
        if(is_null($user_id)){
            $user_id = $uid;
        }
        
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
        get_calendar_settings();       
        
        //agenda
        if(!empty($q)){
            $q .= " UNION ";
        }
        $dc = str_replace('start','ag.start',$datecond);
        $q .= "SELECT ag.id, ag.title, ag.start, date_format(ag.start,'%Y-%m-%d') startdate, ag.duration, date_format(ag.start + ag.duration, '%Y-%m-%d %H:%s') `end`, content, 'course' event_group, 'event-info' class, 'agenda' event_type,  c.code course "
                . "FROM agenda ag "
                . "WHERE cg.course_id =?d "
                . $dc;
        $q_args = array_merge($q_args, $q_args_templ);

        if(!$agenda_events_only){
            //big blue button
            if(!empty($q)){
                $q .= " UNION ";
            }
            $dc = str_replace('start','bbb.start_date',$datecond);
            $q .= "SELECT bbb.id, bbb.title, bbb.start_date start, date_format(bbb.start_date,'%Y-%m-%d') startdate, '00:00' duration, date_format(bbb.start_date + '00:00', '%Y-%m-%d %H:%s') `end`, bbb.description content, 'course' event_group, 'event-info' class, 'teleconference' event_type,  c.code course "
                    . "FROM bbb_session bbb  "
                    . "WHERE bbb.course_id =?d "
                    . $dc;
            $q_args = array_merge($q_args, $q_args_templ);
            //assignements
            if(!empty($q)){
                $q .= " UNION ";
            }
            $dc = str_replace('start','ass.deadline',$datecond);
            $q .= "SELECT ass.id, ass.title, ass.deadline start, date_format(ass.deadline,'%Y-%m-%d') startdate, '00:00' duration, date_format(ass.deadline + '00:00', '%Y-%m-%d %H:%s') `end`, concat(ass.description,'\n','(deadline: ',deadline,')') content, 'deadline' event_group, 'event-important' class, 'assignment' event_type, c.code course "
                    . "FROM assignment ass  "
                    . "WHERE ass.course_id =?d "
                    . $dc;
            $q_args = array_merge($q_args, $q_args_templ);

            //exercises
            if(!empty($q)){
                $q .= " UNION ";
            }
            $dc = str_replace('start','ex.end_date',$datecond);
            $q .= "SELECT ex.id, ex.title, ex.end_date start, date_format(ex.end_date,'%Y-%m-%d') startdate, '00:00' duration, date_format(ex.end_date + '00:00', '%Y-%m-%d %H:%s') `end`, concat(ex.description,'\n','(deadline: ',end_date,')') content, 'deadline' event_group, 'event-important' class, 'exercise' event_type, c.code course "
                    . "FROM exercise ex "
                    . "WHERE ex.course_id =?d "
                    . $dc;
            $q_args = array_merge($q_args, $q_args_templ);
        }
        if(empty($q))
        {
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
     * @return int $eventid which is the id of the new event
     */
    function add_event($title, $content, $start, $duration, $recursion = NULL){
        global $course_id, $langNotValidInput, $is_admin, $is_editor, $langNotAllowed;
        $eventids = array();
        // insert
        $period = "";
        $enddate = null;
        $multiple_events = false;
        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $title = trim($title);
        if(empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start)))
        {
            return array('success'=>false, 'message'=>$langNotValidInput);
        } else {
            $startdate = $d1->format('Y-m-d H:i');
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
        if($is_editor || $is_admin){
            $eventid = Database::get()->query("INSERT INTO agenda "
                . "SET content = ?s, title = ?s, course_id = ?d, start = ?t, duration = ?t, "
                . "recursion_period = ?s, recursion_end = ?t, visible = 1",
                purify($content), $title, $course_id, $startdate, $duration, $period, $enddate)->lastInsertID;

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
                    $multiple_events = true;
                    $neweventid = Database::get()->query("INSERT INTO agenda "
                        . "SET content = ?s, title = ?s, course_id = ?d, start = ?t, duration = ?t, "
                        . "recursion_period = ?s, recursion_end = ?t, "
                        . "source_event_id = ?d, visible = 1",
                        purify($content), $title, $course_id, $newdate->format('Y-m-d H:i'), $duration, $period, $enddate, $sourceevent)->lastInsertID;
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
    function update_event($eventid, $title, $start, $duration, $content, $recursivelly = false){
        global $uid, $langNotValidInput, $course_id;
        
        $d1 = DateTime::createFromFormat('d-m-Y H:i', $start);
        $d2 = DateTime::createFromFormat('d-m-Y H:i:s', $start);
        $title = trim($title);
        if(empty($title) || !(($d1 && $d1->format('d-m-Y H:i') == $start) || ($d2 && $d2->format('d-m-Y H:i:s') == $start)))
        {
            return array('success'=>false, 'message'=>$langNotValidInput);
        } else {
            $start = $d1->format('Y-m-d H:i');
        }
        
        $where_clause = ($recursivelly)? " ":" ";
        if($recursivelly){
            Database::get()->query("UPDATE agenda SET "
                . "title = ?s, "
                . "duration = ?t, "
                . "content = ?s "
                . "WHERE source_event_id = ?d AND course_id = ?d",
                $title, $duration, purify($content), $eventid, $course_id);    
        } else {
            Database::get()->query("UPDATE agenda SET "
                . "title = ?s, "
                . "start = ?t, "
                . "duration = ?t, "
                . "content = ?s "
                . "WHERE id = ?d AND course_id = ?d",
                $title, $start, $duration, purify($content), $eventid, $course_id);
        }
        

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
    function update_recursive_event($eventid, $title, $start, $duration, $content){
        global $langNotValidInput;
        $rec_eventid = Database::get()->querySingle('SELECT source_event_id FROM agenda WHERE id=?d',$eventid);
        if($rec_eventid){
            return update_event($rec_eventid, $title, $start, $duration, $content, true);
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
            return delete_event($rec_eventid, true);
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
      * A function to generate month view of a set of events
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames
      * @return object with `count` attribute containing the number of associated events with the item
     */
   function month_calendar($day, $month, $year) {
       global $uid, $langDay_of_weekNames, $langMonthNames, $langToday, $langDay, $langWeek, $langMonth, $langView;
       $calendar_content = "";
        //Handle leap year
        $numberofdays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if (($year % 400 == 0) or ($year % 4 == 0 and $year % 100 <> 0)) {
            $numberofdays[2] = 29;
        }

        $eventlist = get_calendar_events("month", "$year-$month-$day");

        $events = array();
        if ($eventlist) {
            foreach($eventlist as $event){
                $eventday = new DateTime($event->startdate);
                $eventday = $eventday->format('j');
                if(!array_key_exists($eventday,$events)){
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

        $calendar_content .= '<div class="right" style="width:100%">'.$langView.':&nbsp;'.
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
        $calendar_content .= "<table width=100% class='tbl_1'><tr>";
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
                    if(array_key_exists($curday, $events)){
                        foreach($events[$curday] as $ev){
                            $thisDayItems .= month_calendar_item($ev, $calsettings->{$ev->event_group."_color"});
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
        $calendar_content .= calendar_legend();
        
        
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
   function small_month_calendar($day, $month, $year) {
       global $uid, $langDay_of_weekNames, $langMonthNames, $langToday;
       if($_SESSION['theme'] == 'bootstrap'){
           return small_month_bootstrap_calendar();
       }
       $calendar_content = "";
        //Handle leap year
        $numberofdays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if (($year % 400 == 0) or ($year % 4 == 0 and $year % 100 <> 0)) {
            $numberofdays[2] = 29;
        }

        $eventlist = get_calendar_events("month", "$year-$month-$day");

        $events = array();
        if ($eventlist) {
            foreach($eventlist as $event){
                $eventday = new DateTime($event->startdate);
                $eventday = $eventday->format('d');
                if(!array_key_exists($eventday,$events)){
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

        $calendar_content .= "<table class='title1' style='with:450px;'>";
        $calendar_content .= "<tr>";
        $calendar_content .= '<td style="width:25px;"><a href="#" onclick="show_month(1,'.$backward['month'].','.$backward['year'].'); return false;">&laquo;</a></td>';
        $calendar_content .= "<td class='center' style='width:400px;font-size:11px;'><b>{$langMonthNames['long'][$month-1]} $year</b></td>";
        $calendar_content .= '<td style="width:25px;"><a href="#" onclick="show_month(1,'.$foreward['month'].','.$foreward['year'].'); return false;">&raquo;</a></td>';
        $calendar_content .= "</tr>";
        $calendar_content .= "</table>";
        $calendar_content .= "<table style='min-width:450px;font-size:10px;' class='tbl_1'><tr>";
        for ($ii = 1; $ii < 8; $ii++) {
            $calendar_content .= "<th class='center'>" . $langDay_of_weekNames['short'][$ii % 7] . "</th>";
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
                    if(array_key_exists($curday, $events)){
                        foreach($events[$curday] as $ev){
                            $thisDayItems .= month_calendar_item($ev, $calsettings->{$ev->event_group."_color"});
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
        $calendar_content .= calendar_legend();
        return $calendar_content;
    }

   /**
      * A function to generate week view of a set of events
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames
      * @return object with `count` attribute containing the number of associated events with the item
     */
    function week_calendar($day, $month, $year){
        global $langEvents, $langActions, $langCalendar, $langDateNow, $is_editor, $dateFormatLong, $langNoEvents, $langDay, $langWeek, $langMonth, $langView;
        $calendar_content = "";
        if(is_null($day)){
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

        $calendar_content .= '<div class="right" style="width:100%">'.$langView.':&nbsp;'.
                '<a href="#" onclick="show_day(selectedday, selectedmonth, selectedyear);return false;">'.$langDay.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_week(selectedday, selectedmonth, selectedyear);return false;">'.$langWeek.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_month(selectedday, selectedmonth, selectedyear);return false;">'.$langMonth.'</a></div>';

        $calendar_content .= "<table width='100%' class='title1'>";
        $calendar_content .= "<tr>";
        $calendar_content .= '<td width="25"><a href="#" onclick="show_week('.$backward['day'].','.$backward['month'].','.$backward['year'].'); return false;">&laquo;</a></td>';
        $calendar_content .= "<td class='center'><b>$weekdescription</b></td>";
        $calendar_content .= '<td width="25" class="right"><a href="#" onclick="show_week('.$foreward['day'].','.$foreward['month'].','.$foreward['year'].'); return false;">&raquo;</a></td>';
        $calendar_content .= "</tr>";
        $calendar_content .= "</table>";        
        $eventlist = get_calendar_events("week", "$year-$month-$day");
        //$dateNow = date("j-n-Y", time());
        $numLine = 0;

        $calendar_content .= "<table width='100%' class='tbl_alt'>";
        //                <tr><th colspan='2' class='left'>$langEvents</th>";
        //$calendar_content .= "<th width='50'><b>$langActions</b></th>";
        //$calendar_content .= "</tr>";

        $curday = 0;
        $now = getdate();
        $today = new DateTime($now['year'].'-'.$now['mon'].'-'.$now['mday']);
        $curstartddate = "";        
        foreach ($eventlist as $thisevent) {        
            if($curstartddate != $thisevent->startdate){ //event date changed
                $thiseventdatetime = new DateTime($thisevent->startdate);
                while($cursorday < $thiseventdatetime){
                    if($cursorday == $today)
                        $class = 'today';
                    else
                        $class = 'monthLabel';
                    $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst(claro_format_locale_date($dateFormatLong, $cursorday->getTimestamp())) . "</b></td></tr>";
                    $calendar_content .= "<tr><td colspan='3'>$langNoEvents</td></tr>";
                    $cursorday->add(new DateInterval('P1D'));
                    $curday++;
                }

                /*if ($numLine % 2 == 0) {
                    $classvis = "class='even'";
                } else {
                    $classvis = "class='odd'";
                }*/

                if($thiseventdatetime == $today)
                    $class = 'today';
                else
                    $class = 'monthLabel';
                $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst(claro_format_locale_date($dateFormatLong, strtotime($thisevent->startdate))) . "</b></td></tr>";
                if($cursorday <= $thiseventdatetime){
                    $cursorday->add(new DateInterval('P1D'));
                    $curday++;
                }
            }
            $calendar_content .= week_calendar_item($thisevent, 'even');
            $curstartddate = $thisevent->startdate;
            //$numLine++;
        }
        /* Fill with empty days*/
        for($i=$curday;$i<7;$i++){
            if($cursorday == $today)
                    $class = 'today';
                else
                    $class = 'monthLabel';
                $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst(claro_format_locale_date($dateFormatLong, $cursorday->getTimestamp())) . "</b></td></tr>";
                $calendar_content .= "<tr><td colspan='3'>$langNoEvents</td></tr>";
                $cursorday->add(new DateInterval('P1D'));
        }
        $calendar_content .= "</table>";
        /* Legend */
        $calendar_content .= calendar_legend();

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
   function day_calendar($day, $month, $year){
       global $langEvents, $langActions, $langCalendar, $langDateNow, $is_editor, $dateFormatLong, $langNoEvents, $langDay, $langWeek, $langMonth, $langView;
        $calendar_content = "";
        if(is_null($day)){
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

        $calendar_content .= '<div class="right" style="width:100%">'.$langView.':&nbsp;'.
                '<a href="#" onclick="show_day(selectedday, selectedmonth, selectedyear);return false;">'.$langDay.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_week(selectedday, selectedmonth, selectedyear);return false;">'.$langWeek.'</a>&nbsp;|&nbsp;'.
                '<a href="#" onclick="show_month(selectedday, selectedmonth, selectedyear);return false;">'.$langMonth.'</a></div>';

        $calendar_content .= "<table width='100%' class='title1'>";
        $calendar_content .= "<tr>";
        $calendar_content .= '<td width="25"><a href="#" onclick="show_day('.$backward['day'].','.$backward['month'].','.$backward['year'].'); return false;">&laquo;</a></td>';
        $calendar_content .= "<td class='center'><b>$daydescription</b></td>";
        $calendar_content .= '<td width="25" class="right"><a href="#" onclick="show_day('.$foreward['day'].','.$foreward['month'].','.$foreward['year'].'); return false;">&raquo;</a></td>';
        $calendar_content .= "</tr>";
        $calendar_content .= "</table>";

        $eventlist = get_calendar_events("day", "$year-$month-$day");        
        $calendar_content .= "<table width='100%' class='tbl_alt'>";

        $curhour = 0;
        $now = getdate();
        $today = new DateTime($now['year'].'-'.$now['mon'].'-'.$now['mday'].' '.$now['hours'].':'.$now['minutes']);
        if($now['year'].'-'.$now['mon'].'-'.$now['mday'] == "$year-$month-$day"){
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
            if($curstarthour != $thiseventhour){ //event date changed
                while($cursorhour < $thiseventhour){
                    if($thisdayistoday && $thishour>=$cursorhour && intval($cursorhour->diff($thishour,true)->format('%h'))<6)
                        $class = 'today';
                    else
                        $class = 'monthLabel';
                    $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst($cursorhour->format('H:i')) . "</b></td></tr>";
                    if(intval($cursorhour->diff($thiseventhour,true)->format('%h'))>6){
                        $calendar_content .= "<tr><td colspan='3'>$langNoEvents</td></tr>";
                    }
                    $cursorhour->add(new DateInterval('PT6H'));
                    $curhour += 6;
                }

                if($thisdayistoday && $thishour>=$cursorhour && intval($cursorhour->diff($thishour,true)->format('%h'))<6)
                    $class = 'today';
                else
                    $class = 'monthLabel';
                //No hour tr for the event
                //$calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst($thiseventhour->format('H:i')) . "</b></td></tr>";
                if($cursorhour <= $thiseventhour){
                    $cursorhour->add(new DateInterval('PT6H'));
                    $curhour += 6;
                }
            }
            $calendar_content .= day_calendar_item($thisevent, 'even');
            $curstarthour = $thiseventhour;
            //$numLine++;
        }
        /* Fill with empty days*/
        for($i=$curhour;$i<24;$i+=6){
            if($thisdayistoday && $thishour>=$cursorhour && intval($cursorhour->diff($thishour,true)->format('%h'))<6)
                    $class = 'today';
                else
                    $class = 'monthLabel';
                $calendar_content .= "<tr><td colspan='3' class='$class'>" . "&nbsp;<b>" . ucfirst($cursorhour->format('H:i')) . "</b></td></tr>";
                $calendar_content .= "<tr><td colspan='3'>$langNoEvents</td></tr>";
                $cursorhour->add(new DateInterval('PT6H'));
        }
        $calendar_content .= "</table>";
        /* Legend */
        $calendar_content .= calendar_legend();

        return $calendar_content;
   }

   /**
      * A function to generate event block in month calendar
      * @param object $event event to format
      * @param string $color event color
      * @return html formatted item
     */
   function month_calendar_item($event, $color){
       global $urlServer, $is_admin;
       $link = str_replace('thisid', $event->id, $urlServer.$event_type_url[$event->event_type]);
       if($event->event_type != 'personal' && $event->event_type != 'admin'){
           $link = str_replace('thiscourse', $event->course, $link);
       }
       $formatted_calendar_item = "<a href=\"".$link."\"><div class=\"{$event->event_group}\" style=\"padding:2px;background-color:$color;\">".$event->title."</div></a>";
       if(!$is_admin && $event->event_group == 'admin'){
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
    function week_calendar_item($event, $class){
        global $urlServer,$is_admin,$langVisible, $dateFormatLong, $langDuration, $langAgendaNoTitle, $langModify, $langDelete, $langHour, $langConfirmDelete, $langReferencedObject;
        $formatted_calendar_item = "";
        $formatted_calendar_item .= "<tr $class>";
        $formatted_calendar_item .= "<td valign='top'><div class=\"legend_color\" style=\"float:left;margin:3px;height:16px;width:16px;background-color:".$calsettings->{$event->event_group."_color"}."\"></div></td>";
        $formatted_calendar_item .= "<td valign='top'>";
        $eventdate = strtotime($event->start);
        $formatted_calendar_item .= $langHour.": " . ucfirst(date('H:i', $eventdate));
        if ($event->duration != '') {
            $msg = "($langDuration: " . q($event->duration) . ")";
        } else {
            $msg = '';
        }
        $formatted_calendar_item .= "<br><b><div class='event'>";
        $link = str_replace('thisid', $event->id, $urlServer.$event_type_url[$event->event_type]);
        if($event->event_type != 'personal' && $event->event_type != 'admin'){
            $link = str_replace('thiscourse', $event->course, $link);
        }
        if ($event->title == '') {
            $formatted_calendar_item .= $langAgendaNoTitle;
        } else {
            if(!$is_admin && $event->event_type == 'admin'){
                $formatted_calendar_item .= q($event->title);
            } else {
                $formatted_calendar_item .= "<a href=\"".$link."\">".q($event->title)."</a>";
            }
        }
        if($event->event_type == "personal"){
            $fullevent = get_event($event->id);
            if($reflink = References::item_link($fullevent->reference_obj_module, $fullevent->reference_obj_type, $fullevent->reference_obj_id, $fullevent->reference_obj_course)){
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
        if($event->event_type == "personal" || ($event->event_type == "admin" && $is_admin)){
            $formatted_calendar_item .= icon('fa-edit', $langModify, str_replace('thisid',$event->id, $urlServer.$event_type_url[$event->event_type])). "&nbsp;
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
    function day_calendar_item($event, $class){
        global $urlServer, $is_admin, $langVisible, $dateFormatLong, $langDuration, $langAgendaNoTitle, $langModify, $langDelete, $langHour, $langConfirmDelete, $langReferencedObject;
        $formatted_calendar_item = "";
        $formatted_calendar_item .= "<tr $class>";
        $formatted_calendar_item .= "<td valign='top'><div class=\"legend_color\" style=\"float:left;margin:3px;height:16px;width:16px;background-color:".$calsettings->{$event->event_group."_color"}."\"></div></td>";
        $formatted_calendar_item .= "<td valign='top'>";
        $eventdate = strtotime($event->start);
        $formatted_calendar_item .= $langHour.": " . ucfirst(date('H:i', $eventdate));
        if ($event->duration != '') {
            $msg = "($langDuration: " . q($event->duration) . ")";
        } else {
            $msg = '';
        }
        $formatted_calendar_item .= "<br><b><div class='event'>";
        $link = str_replace('thisid', $event->id, $urlServer.$event_type_url[$event->event_type]);
        if($event->event_type != 'personal' && $event->event_type != 'admin'){
            $link = str_replace('thiscourse', $event->course, $link);
        }
        if ($event->title == '') {
            $formatted_calendar_item .= $langAgendaNoTitle;
        } else {
            if(!$is_admin && $event->event_type == 'admin'){
                $formatted_calendar_item .= q($event->title);
            } else {
                $formatted_calendar_item .= "<a href=\"".$link."\">".q($event->title)."</a>";
            }
        }
        if($event->event_type == "personal"){
            $fullevent = get_event($event->id);
            if($reflink = References::item_link($fullevent->reference_obj_module, $fullevent->reference_obj_type, $fullevent->reference_obj_id, $fullevent->reference_obj_course)){
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
        if($event->event_type == "personal" || ($event->event_type == "admin" && $is_admin)){
            $formatted_calendar_item .= icon('fa-edit', $langModify, str_replace('thisid',$event->id,$event_type_url[$event->event_type])). "&nbsp;
                        ".icon('fa-times', $langDelete, "?delete=$event->id&et=$event->event_type", "onClick=\"return confirmation('$langConfirmDelete');\""). "&nbsp;";
        }
        $formatted_calendar_item .= "</td>";
        $formatted_calendar_item .= "</tr>";

       return $formatted_calendar_item;
   }

   function calendar_legend(){
       $legend = "";

        /* Legend */
        $legend .= "<br/>"
                . "<table width=100% class='calendar_legend'>";
        $legend .= "<tr>";
        $legend .= "<td>";
        foreach(array_values($event_groups) as $evtype)
        {
            global ${"langEvent".$evtype};
            $evtype_legendtext = ${"langEvent".$evtype};
            $legend .= "<div class=\"legend_item\" style=\"padding:3px;margin-left:10px;float:left;\"><div class=\"legend_color\" style=\"float:left;margin-right:3px;height:16px;width:16px;background-color:".$calsettings->{$evtype."_color"}."\"></div>".$evtype_legendtext."</div>";
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
   function icalendar(){
       $ical = "BEGIN:VCALENDAR".PHP_EOL;
       $ical .= "VERSION:2.0".PHP_EOL;

       $show_personal_bak = $calsettings->show_personal;
       $show_course_bak = $calsettings->show_course;
       $show_deadline_bak = $calsettings->show_deadline;
       $show_admin_bak = $calsettings->show_admin;
       set_calendar_settings(1,1,1,1);
       get_calendar_settings();
       $eventlist = get_calendar_events();
       set_calendar_settings($show_personal_bak,$show_course_bak,$show_deadline_bak,$show_admin_bak);
       get_calendar_settings();

       $events = array();
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
   
   function bootstrap_events($from, $to){
       global $urlServer, $uid, $langDay_of_weekNames, $langMonthNames, $langToday, $course_id;
       $fromdatetime = date("Y-m-d H:i:s",$from/1000);
       $todatetime = date("Y-m-d H:i:s",$to/1000);
       /* The type of calendar here defines how detailed the events are going to be. Default:month  */
       if(!isset($course_id) || empty($course_id) || is_null($course_id)){
           $eventlist = get_calendar_events("month", $fromdatetime, $todatetime);
       } else {
           $eventlist = Calendar_events::get_current_course_events("month", $fromdatetime, $todatetime);
       }
       $events = array();
       foreach($eventlist as $event){
           $startdatetime = new DateTime($event->start);
           $event->start = $startdatetime->getTimestamp()*1000;
           $enddatetime = new DateTime($event->end);
           $event->end = $enddatetime->getTimestamp()*1000;
           $event->url = str_replace('thisid', $event->id, $urlServer.$event_type_url[$event->event_type]);
           if($event->event_type != 'personal' && $event->event_type != 'admin'){
               $event->url = str_replace('thiscourse', $event->course, $event->url);
           }
           array_push($events, $event);
       }
       return json_encode(array('success'=>1, 'result'=>$events, 'cid'=>$course_id));
   }
   
   function small_month_bootstrap_calendar()
   {
       global $langNext, $langPrevious;
       
       $calendar = '<div id="cal-header" class="btn-group btn-group-justified btn-group-sm">
                            <div class="btn-group btn-group-sm"><button type="button" class="btn btn-default" data-calendar-nav="prev">&larr; '.$langPrevious.'</button></div>
                            <div class="btn-group btn-group-sm"><button id="current-month" type="button" class="btn btn-default" disabled="disabled">&nbsp;</button></div>
                            <div class="btn-group btn-group-sm"><button type="button" class="btn btn-default" data-calendar-nav="next">'.$langNext.' &rarr;</button></div>
                    </div>';
       
       $calendar .= '<div id="bootstrapcalendar"></div><div class="clearfix"></div>';

        return $calendar;
   }

 ?>

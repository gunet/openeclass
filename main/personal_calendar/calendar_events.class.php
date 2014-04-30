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
 * Eclass personal and course events manipulation library 
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

class Calendar_Events {
    
    /** @staticvar array of urls to form links from events in calendar
    */ 
    private static $event_type_url = array(
            'deadline' => '../../modules/work/index.php?id=',
            'course' => '../../modules/agenda/?id=',
            'personal' => '?modify=');
    
    /********  Basic set of functions to be called from inside **************
     *********** personal events module that manipulate note items ********************/
    
    /**
     * Get note details given the note id
     * @param int $eventid id in table personal_calendar
     * @return array event tuple 
     */
    public static function get_event($eventid){
        global $uid;
        return Database::get()->querySingle("SELECT * FROM personal_calendar WHERE id = ?d AND user_id = ?d", $eventid, $uid);
    }
    
    /**
     * Get personal events with details for a given user
     * @param string $startdate mysql friendly formatted string representing the start of the time frame for which events are seeked
     * @param string $enddate mysql friendly formatted string representing the end of the time frame for which events are seeked
     * @param int $user_id if empty the session user is assumed
     * @return array of user events with details 
     */
    public static function get_user_events($startdate = null, $enddate = null, $user_id = NULL){
        global $uid;
        if(is_null($user_id)){
            $user_id = $uid;
        }
        if(is_null($startdate) || is_null($enddate)){
            return Database::get()->queryArray("SELECT id, title, start, date_format(start,'%d') startday, duration, content"
                . "'personal' event_type FROM personal_calendar WHERE user_id = ?d  AND start>=?t AND start<=?t"
                . " ORDER BY `start`", $user_id, $startdate, $enddate);
        }
        else{
            return Database::get()->queryArray("SELECT id, title, start, date_format(start,'%d') startday, duration, content"
                . "'personal' event_type FROM personal_calendar WHERE user_id = ?d"
                . " ORDER BY `start`", $user_id);
        }
    }
    
    /**
     * Get course events for a given user and her courses
     * @param string $eventtypes all|course|deadline
     * @param string $startdate mysql friendly formatted string representing the start of the time frame for which events are seeked
     * @param string $enddate mysql friendly formatted string representing the end of the time frame for which events are seeked
     * @param int $user_id if empty the session user is assumed
     * @return array of user events with details 
     */
    public static function get_user_course_events($eventtypes, $startdate = null, $enddate = null, $user_id = NULL){
        global $uid;
        if(is_null($user_id)){
            $user_id = $uid;
        }
        if(is_null($startdate) || is_null($enddate)){
            $startdate = '1990-01-01';
            $enddate = '2100-12-31';
        }
        $q = "";
        if($eventtypes == "all" || $eventtypes == "course"){
            $q .= "SELECT ag.id, ag.title, ag.start, date_format(ag.start,'%d') startday, ag.duration, content, 'course' event_type FROM agenda ag JOIN course_user cu ON ag.course_id=cu.course_id WHERE cu.user_id =?d "
                . " AND ag.start>=?t AND ag.start<=?t";
        }
        if($eventtypes == "all"){
            $q .= " UNION ";
        }
        if($eventtypes == "all" || $eventtypes == "deadline"){
            $q .= "SELECT ass.id, ass.title, ass.deadline start, date_format(ass.deadline,'%d') startday, '00:00' duration, concat(description,'\n','(deadline: ',deadline,')') content,'deadline' event_type FROM assignment ass JOIN course_user cu ON ass.course_id=cu.course_id WHERE cu.user_id =?d "
                    . " AND ass.deadline>=?s AND ass.deadline<=?s";
        }
        $q .= " ORDER BY start, event_type";
        
        if($eventtypes == "all"){
            return Database::get()->queryArray($q, $user_id, $startdate, $enddate, $user_id, $startdate, $enddate);
        }
        else{
            return Database::get()->queryArray($q, $user_id, $startdate, $enddate);
        }
    }
    
    /**
     * Get events count for a given user
     * @param int $user_id if empty the session user is assumed
     * @return int 
     */
    public static function count_user_events($user_id = NULL){
        global $uid;
        if(is_null($user_id)){
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
    public static function add_event($title, $content, $start, $duration, $recursion = NULL, $reference_obj_id = NULL){
        global $uid;
        $refobjinfo = References::get_ref_obj_field_values($reference_obj_id);
        // insert
        $period = "";
        $enddate = "";
        if(!empty($recursion))
        {
            $period = "P".$recursion['repeat'].$recursion['unit'];
            $enddate = $recursion['end'];
        }
        $eventid = Database::get()->query("INSERT INTO personal_calendar "
                . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, duration = ?t, "
                . "recursion_period = ?s, recursion_end = ?t, "
                . "reference_obj_module = ?d, reference_obj_type = ?s, reference_obj_id = ?d, reference_obj_course = ?d", 
                purify($content), $title, $uid, $start, $duration, $period, $enddate, $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'])->lastInsertID;
        if(isset($eventid) && !is_null($eventid)){
            Database::get()->query("UPDATE personal_calendar SET source_event_id = id WHERE id = ?",$eventid);
        }
        
        /* Additional events generated by recursion */
        if(isset($eventid) && !is_null($eventid) && !empty($recursion)){
            $sourceevent = $eventid;
            $interval = new DateInterval($period);
            $startdatetime = new DateTime($start);
            $enddatetime = new DateTime($recursion['end']." 23:59:59");
            var_dump($startdatetime);
            
            $newdate = date_add($startdatetime, $interval);
            while($newdate <= $enddatetime)
            {
                $neweventid = Database::get()->query("INSERT INTO personal_calendar "
                        . "SET content = ?s, title = ?s, user_id = ?d, start = ?t, duration = ?t, "
                        . "recursion_period = ?s, recursion_end = ?t, "
                        . "source_event_id = ?d, reference_obj_module = ?d, reference_obj_type = ?s, "
                        . "reference_obj_id = ?d, reference_obj_course = ?d", 
                purify($content), $title, $uid, $newdate->format('Y-m-d'), $duration, $period, $enddate, $sourceevent, $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'])->lastInsertID;
                $newdate = date_add($startdatetime, $interval);
            }
        }
        Log::record(0, MODULE_ID_PERSONALCALENDAR, LOG_INSERT, array('user_id' => $uid, 'id' => $eventid,
        'title' => $title,
        'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
        return $eventid;
    }
    
    /**
     * Update existing event and logs the action
     * @param int $eventid id in table note
     * @param string $title note title
     * @param text $content note body
     * @param string $reference_obj_id refernced object by note. It contains the object type (from $ref_object_types) and object id (id in the corresponding db table), e.g., video_link:5  
     */
    public static function update_event($eventid, $title, $start, $duration, $content, $reference_obj_id = NULL){
        global $uid;
        $refobjinfo = References::get_ref_obj_field_values($reference_obj_id);
        $intvduration = new DateInterval($duration);
        Database::get()->query("UPDATE personal_calendar SET "
                . "title = ?s, "
                . "start = ?t, "
                . "duration = ?t, "
                . "content = ?s, "
                . "reference_obj_module = ?d, "
                . "reference_obj_type = ?s, "
                . "reference_obj_id = ?d, "
                . "reference_obj_course = ?d "
                . "WHERE id = ?d", 
                $title, $start, $intvduration->format('%h:%I:%S'), purify($content), $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'], $noteid);
        
        Log::record(0, MODULE_ID_PERSONALCALENDAR, LOG_MODIFY, array('user_id' => $uid, 'id' => $eventid,
        'title' => $title,
        'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
    }
    
    
    
    /**
     * Deletes an existing event and logs the action 
     * @param int $noteid id in table note
     */
    public static function delete_event($eventid){
        global $uid;
        $note = Database::get()->querySingle("SELECT title, content FROM note WHERE id = ? ", $eventid);
        $content = ellipsize_html(canonicalize_whitespace(strip_tags($note->content)), 50, '+');
        Database::get()->query("DELETE FROM personal_calendar WHERE id = ?", $eventid);
        
        $noteidx = new NoteIndexer();
        $noteidx->remove($noteid);
        
        Log::record(0, MODULE_ID_PERSONALCALENDAR, LOG_DELETE, array('user_id' => $uid, 'id' => $noteid,
            'title' => $note->title,
            'content' => $content));
    }
    
    /**
     * Delete all events of a given user and logs the action
     * @param int $user_id if empty the session user is assumed
     */
    public static function delete_all_events($user_id = NULL){
        global $uid;
        Database::get()->query("DELETE FROM personal_calendar WHERE user_id = ?", $uid);
        
        Log::record(0, MODULE_ID_PERSONALCALENDAR, LOG_DELETE, array('user_id' => $uid, 'id' => 'all'));
    }
    
    /**************************************************************************/
    /*
     * Set of functions to be called from modules other than notes
     * in order to associate notes with module specific items
     */
    
    
    /** 
     * Get personal events generally associated with a course. If no course is defined the current course is assumed.
     * @param int $cid the course id
     * @return array of notes 
     */
    public static function get_general_course_events($cid = NULL){
       global $uid, $course_id;
       if(is_null($cid)){
           $cid = $course_id;
       }
       return Database::get()->queryArray("SELECT id, title, content FROM personal_calendar WHERE user_id = ? AND reference_obj_type = 'course' AND reference_obj_id = ?", $uid, $cid);
    }
    
    /** Get personal events associated with a course generally or with specific items of the course
     * @param int $cid the course id
     * @return array array of notes 
     */
    public static function get_all_course_events($cid = NULL){
       global $uid, $course_id;
       if(is_null($cid)){
           $cid = $course_id;
       }
       return Database::get()->queryArray("SELECT id, title, content FROM personal_calendar WHERE user_id = ? AND reference_obj_course = ? ", $uid, $cid);
    }
    
    /** 
     * Get notes associated with items of a specific module of a course. If course is not specified the current one is assumed. If module is not specified the whole course is assumed. 
     * @param int $module_id the id of the module
     * @param int $cid the course id
     * @return array of notes
     */
    public static function get_module_events($cid = NULL, $module_id = NULL){
       global $uid, $course_id;
       if(is_null($cid)){
           $cid = $course_id;
       }
       if(is_null($module_id)){
           return self::get_all_course_events($cid);
       }
       return Database::get()->queryArray("SELECT id, title, content FROM personal_calendar WHERE user_id = ? AND reference_obj_course = ? ", $uid, $cid);
    }
    
    /** 
     * Get notes associated with a specific item of a module of a course 
     * If module or course are not specified the current ones are assumed.
     * Item type should be defined in case of a module being associated with more than one 
     * object types (e.g., video module that contains videos and links to videos)
     * @param integer $item_id the item id in the database
     * @param integer $module_id the module id
     * @param integer $course_id the course id
     * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
     * @return array array of notes associated with the item
     */
    public static function get_item_events($item_id, $module_id, $course_id, $item_type){
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
      * @return boolean true if notes exist for the specified item or false otherwise 
     */
    public static function item_has_events($item_id, $module_id, $course_id, $item_type){
       return count_item_notes($item_id, $module_id, $course_id, $item_type) > 0;
    }
    
    /** 
      * A function to count the personal events associated with some item listed by a module's page, for the current user.
      * @param integer $item_id the item id in the database
      * @param integer $module_id the module id
      * @param integer $course_id the course id
      * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
      * @return object with `count` attribute containing the number of associated notes with the item 
     */
    public static function count_item_events($item_id, $module_id, $course_id, $item_type){
        global $uid;
        return Database::get()->querySingle("SELECT count(*) `count` FROM note WHERE user_id = ? AND reference_obj_course = ?  AND reference_obj_course = ? AND reference_obj_module = ? AND reference_obj_type = ? AND reference_obj_id = ?", $uid, $course_id, $module_id, $item_type, $item_id);
    }
    
    
    public static function calendar_view($day = null, $month = null, $year = null, $calendar_type = null)
    {
        global $uid;
        $calsettings = Database::get()->querySingle("SELECT view_type FROM personal_calendar_settings WHERE user_id = ?d",$uid);
        $view_func = $calsettings->view_type."_calendar";
        if(is_null($month) || is_null($year) || $month<0 || $month>12 || $year<2014 || $year>2020){
            $today = getdate();
            $day = $today['mday'];
            $month = $today['mon'];
            $year = $today['year'];
        }
        if($calendar_type == 'small'){
            return Calendar_Events::small_month_calendar($day, $month, $year);
        }
        else{
            return Calendar_Events::$view_func($day, $month, $year);
        }
    }
    
     /** 
      * A function to generate month view of a set of events
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames 
      * @return object with `count` attribute containing the number of associated notes with the item 
     */
   public static function month_calendar($day, $month, $year) {
       global $uid, $langDay_of_weekNames, $langMonthNames, $langToday; 
       $calendar_content = "";
        //Handle leap year
        $numberofdays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if (($year % 400 == 0) or ($year % 4 == 0 and $year % 100 <> 0)) {
            $numberofdays[2] = 29;
        }

        $eventlist = array();
        $calsettings = Database::get()->querySingle("SELECT show_personal, show_course, show_deadline, personal_color, course_color, deadline_color FROM personal_calendar_settings WHERE user_id = ?d", $uid);
        if($calsettings->show_personal == 1){
            $e = Calendar_Events::get_user_events("$year-$month-1", "$year-$month-{$numberofdays[$month]}");
            if(count($e)>0){
               $eventlist = array_merge($eventlist, $e);
            }
        }
        if($calsettings->show_course == 1 && $calsettings->show_deadline == 1){
            $e = Calendar_Events::get_user_course_events('all', "$year-$month-1", "$year-$month-{$numberofdays[$month]}");
            if(count($e)>0){
                $eventlist = array_merge($eventlist, $e);
            }
        }
        elseif($calsettings->show_course == 1){
            $e = Calendar_Events::get_user_course_events('course', "$year-$month-1", "$year-$month-{$numberofdays[$month]}");
            if(count($e)>0){
                $eventlist = array_merge($eventlist, $e);
            }
        }
        elseif($calsettings->show_deadline == 1){
            $e = Calendar_Events::get_user_course_events('deadline', "$year-$month-1", "$year-$month-{$numberofdays[$month]}");
            if(count($e)>0){
                $eventlist = rray_merge($eventlist, $e);
            }
        }
        $events = array();
        foreach($eventlist as $event){
            if(!array_key_exists(intval($event->startday),$events)){
                    $events[intval($event->startday)] = array();
            }
            array_push($events[intval($event->startday)], $event);
        }
        
        //Get the first day of the month
        $dayone = getdate(mktime(0, 0, 0, $month, 1, $year));
        //Start the week on monday
        $startdayofweek = $dayone['wday'] <> 0 ? ($dayone['wday'] - 1) : 6;

        $backwardsURL = "$_SERVER[SCRIPT_NAME]?month=" . ($month == 1 ? 12 : $month - 1) . "&amp;year=" . ($month == 1 ? $year - 1 : $year);
        $forewardsURL = "$_SERVER[SCRIPT_NAME]?month=" . ($month == 12 ? 1 : $month + 1) . "&amp;year=" . ($month == 12 ? $year + 1 : $year);

        $calendar_content .= "<table width=100% class='title1'>";
        $calendar_content .= "<tr>";
        $calendar_content .= "<td width='250'><a href=$backwardsURL>&laquo;</a></td>";
        $calendar_content .= "<td class='center'><b>{$langMonthNames['long'][$month-1]} $year</b></td>";
        $calendar_content .= "<td width='250' class='right'><a href=$forewardsURL>&raquo;</a></td>";
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
                    $bgcolor = $ii < 5 ? "class='cautionk'" : "class='odd'";
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
                            $thisDayItems .= Calendar_Events::month_calendar_item($ev, $calsettings->{$ev->event_type."_color"});
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
        $calendar_content .= "<br/>"
                . "<table width=100% class='calendar_legend'>";
        $calendar_content .= "<tr>";
        $calendar_content .= "<td>";
        foreach(array_keys(Calendar_Events::$event_type_url) as $evtype)
        {
            global ${"langEvent".$evtype};
            $evtype_legendtext = ${"langEvent".$evtype};
            $calendar_content .= "<div class=\"legend_item\" style=\"padding:3px;margin-left:10px;float:left;\"><div class=\"legend_color\" style=\"float:left;margin-right:3px;height:16px;width:16px;background-color:".$calsettings->{$evtype."_color"}."\"></div>".$evtype_legendtext."</div>";
        }
        $calendar_content .= "<div style=\"clear:both;\"></both></td>";
        $calendar_content .= "</tr>";
        $calendar_content .= "</table>";
        
        
        return $calendar_content;
    }
    
     /** 
      * A function to generate month view of a set of events small enough for the portfolio page
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames 
      * @return object with `count` attribute containing the number of associated notes with the item 
     */
   public static function small_month_calendar($day, $month, $year) {
       global $uid, $langDay_of_weekNames, $langMonthNames, $langToday; 
       $calendar_content = "";
        //Handle leap year
        $numberofdays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if (($year % 400 == 0) or ($year % 4 == 0 and $year % 100 <> 0)) {
            $numberofdays[2] = 29;
        }

        $eventlist = array();
        $calsettings = Database::get()->querySingle("SELECT show_personal, show_course, show_deadline, personal_color, course_color, deadline_color FROM personal_calendar_settings WHERE user_id = ?d", $uid);
        if($calsettings->show_personal == 1){
            $e = Calendar_Events::get_user_events("$year-$month-1", "$year-$month-{$numberofdays[$month]}");
            if(count($e)>0){
               $eventlist = array_merge($eventlist, $e);
            }
        }
        if($calsettings->show_course == 1 && $calsettings->show_deadline == 1){
            $e = Calendar_Events::get_user_course_events('all', "$year-$month-1", "$year-$month-{$numberofdays[$month]}");
            if(count($e)>0){
                $eventlist = array_merge($eventlist, $e);
            }
        }
        elseif($calsettings->show_course == 1){
            $e = Calendar_Events::get_user_course_events('course', "$year-$month-1", "$year-$month-{$numberofdays[$month]}");
            if(count($e)>0){
                $eventlist = array_merge($eventlist, $e);
            }
        }
        elseif($calsettings->show_deadline == 1){
            $e = Calendar_Events::get_user_course_events('deadline', "$year-$month-1", "$year-$month-{$numberofdays[$month]}");
            if(count($e)>0){
                $eventlist = rray_merge($eventlist, $e);
            }
        }
        $events = array();
        foreach($eventlist as $event){
            if(!array_key_exists(intval($event->startday),$events)){
                    $events[intval($event->startday)] = array();
            }
            array_push($events[intval($event->startday)], $event);
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
                    $bgcolor = $ii < 5 ? "class='cautionk'" : "class='odd'";
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
                            $thisDayItems .= Calendar_Events::month_calendar_item($ev, $calsettings->{$ev->event_type."_color"});
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
        $calendar_content .= "<br/>"
                . "<table class='calendar_legend' style=\"width:450px;font-size:10px;\">";
        $calendar_content .= "<tr>";
        $calendar_content .= "<td>";
        foreach(array_keys(Calendar_Events::$event_type_url) as $evtype)
        {
            global ${"langEvent".$evtype};
            $evtype_legendtext = ${"langEvent".$evtype};
            $calendar_content .= "<div class=\"legend_item\" style=\"padding:3px;margin-left:10px;float:left;\"><div class=\"legend_color\" style=\"float:left;margin-right:3px;height:16px;width:16px;background-color:".$calsettings->{$evtype."_color"}."\"></div>".$evtype_legendtext."</div>";
        }
        $calendar_content .= "<div style=\"clear:both;\"></both></td>";
        $calendar_content .= "</tr>";
        $calendar_content .= "</table>";
        
        
        return $calendar_content;
    }

   /** 
      * A function to generate week view of a set of events
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames 
      * @return object with `count` attribute containing the number of associated notes with the item 
     */
   public static function week_calendar($day, $month, $year){
       return Calendar_Events::month_calendar($month, $year);
   }
   
   /** 
      * A function to generate day view of a set of events
      * @param array $day day to show
      * @param integer $month month to show
      * @param integer $year year to show
      * @param array $weekdaynames 
      * @return object with `count` attribute containing the number of associated notes with the item 
     */
   public static function day_calendar($day, $month, $year){
       return Calendar_Events::month_calendar($month, $year);
   }
   
   /** 
      * A function to generate event block in month calendar
      * @param object $event event to format
      * @param string $color event color 
      * @return html formatted item
     */
   public static function month_calendar_item($event, $color){
       $formatted_calendar_item = "<a href=\"".Calendar_Events::$event_type_url[$event->event_type].$event->id."\"><div class=\"{$event->event_type}\" style=\"padding:2px;border:1px solid $color; background-color:$color;\">".$event->title."</div></a>";
       $formatted_calendar_item = "<a href=\"".Calendar_Events::$event_type_url[$event->event_type].$event->id."\"><div class=\"{$event->event_type}\" style=\"padding:2px;background-color:$color;\">".$event->title."</div></a>";
       return $formatted_calendar_item;
   }
   
   /** 
      * A function to generate event block in week calendar
      * @param object $event event to format
      * @param string $color event color  
      * @return html formatted item
     */
   public static function week_calendar_item($event, $color){
       return Calendar_Evnets::month_calendar_item($event, $color);
   }
   
   /** 
      * A function to generate event block in day calendar
      * @param object $event event to format
      * @param string $color event color  
      * @return html formatted item
     */
   public static function day_calendar_item($event, $color){
       return Calendar_Evnets::month_calendar_item($event, $color);
   }   
   
   /** 
      * A function to generate event block in month calendar
      * @param object $event event to format
      * @param string $color event color  
      * @return icalendar list of user events
     */
   public static function icalendar(){
       $ical = "BEGIN:VCALENDAR".PHP_EOL;
       $ical .= "VERSION:2.0".PHP_EOL;
       $eventlist = array(); 
       $e = Calendar_Events::get_user_events();
       if(count($e)>0){
           $eventlist = array_merge($eventlist, $e);
       }
       $e = Calendar_Events::get_user_course_events('all');
       if(count($e)>0){
           $eventlist = array_merge($eventlist, $e);
       }
       $events = array();
       foreach($eventlist as $event){
           $ical .= "BEGIN:VEVENT".PHP_EOL;
           $startdatetime = new DateTime($event->start);
           $ical .= "DTSTART:".$startdatetime->format("Ymd\THis").PHP_EOL;
           $duration = new DateTime($event->duration);
           $ical .= "DURATION:".$duration->format("\P\TH\Hi\Ms\S").PHP_EOL;
           $ical .= "SUMMARY:[".strtoupper($event->event_type)."] ".$event->title.PHP_EOL;
           $ical .= "DESCRIPTION:".canonicalize_whitespace(strip_tags($event->content)).PHP_EOL;
           $ical .= "END:VEVENT".PHP_EOL;
       } 
       $ical .= "END:VCALENDAR".PHP_EOL;
       return $ical; 
   }
}

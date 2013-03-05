<?php

define('DEFAULT_MAX_DURATION', 900);

class action {

    function record($module_id, $action_name = 'access') {
        global $uid, $course_id;

        $action_type = new action_type();
        $action_type_id = $action_type->get_action_type_id($action_name);
        $exit = $action_type->get_action_type_id('exit');

        ////// ophelia -28-08-2006 : add duration to previous
        $sql = "SELECT id, TIME_TO_SEC(TIMEDIFF(NOW(), last_update)) AS diff, module_id
                FROM actions_daily
                WHERE user_id = $uid
                AND course_id = $course_id
                AND day = DATE(NOW())
                ORDER BY last_update DESC LIMIT 1";
        
        $last_id = $diff = $last_module = 0;
        $result = db_query($sql);
        
        if ($result and mysql_num_rows($result) > 0) {
            list($last_id, $diff, $last_module) = mysql_fetch_row($result);
            mysql_free_result($result);

            // Update previous action with corect duration
            if ($last_id && $diff < DEFAULT_MAX_DURATION) {
                $this->appendStats($uid, $last_module, $course_id, 0, $diff - DEFAULT_MAX_DURATION, $diff, false);
            }
        }
        
        if ($action_type_id != $exit) {
            $this->appendStats($uid, $module_id, $course_id, 1, DEFAULT_MAX_DURATION, DEFAULT_MAX_DURATION, true);
        }
    }
    
    
    private function appendStats($uid, $module_id, $course_id, $hits, $diffduration, $induration, $update_lastdt = false) {
        
        $today = date('Y-m-d');
        $sql = "SELECT id
                    FROM actions_daily
                    WHERE user_id = $uid
                    AND module_id = $module_id
                    AND course_id = $course_id
                    AND day = '". $today ."'";
        
        $stid = 0;
        $result = db_query($sql);
        $sql = null;
        $lu = '';
        if ($update_lastdt) {
            $lu = ' , last_update = NOW() ';
        }
        
        if ($result && mysql_num_rows($result) > 0) {
        
        	list($stid) = mysql_fetch_row($result);
        	$sql = "UPDATE actions_daily SET
                    	hits = hits + $hits,
                    	duration = duration + $diffduration
                    	$lu
                    	WHERE id = $stid";
        } else {
        
        	$sql = "INSERT INTO actions_daily SET
                    	user_id = $uid,
                    	module_id = $module_id,
                    	course_id = $course_id,
                    	hits = 1,
                    	duration = $induration,
                    	day = '". $today ."'
                    	$lu";
        }
        
        db_query($sql);
    }


// ophelia 2006-08-02: per month and per course
    function summarize($course_id = null) {
        if ($course_id == null)
            $course_id = $GLOBALS['course_id'];
        
        // set start/stop dates
        $now = time();
        $stop_stmp = mktime(0, 0, 0, date("m", $now)- (get_config('actions_expire_interval')-1), 1, date("Y", $now)); // minus proper amount of months
        $stop_month = date('Y-m-01 00:00:00', $stop_stmp);

        $start_date = $this->calcSumStartDate($course_id);
	$stmp = strtotime($start_date);
        $end_stmp = mktime(0, 0, 0, date("m", $stmp)+1, 1, date("Y", $stmp)); // min time + 1 month
        $end_date = date('Y-m-01 00:00:00', $end_stmp);
        
        while ($end_date < $stop_month)
            list($start_date, $end_date, $end_stmp) = $this->doSummarize($course_id, $start_date, $end_date, $end_stmp);
    }
    
    /**
     * Summarize All statitics of current course: from its start up to (and including) the current month.
     */
    function summarizeAll($course_id = null) {
        if ($course_id == null)
            $course_id = $GLOBALS['course_id'];
        
        // set start/stop dates
        $now = time();
        $stop_stmp = mktime(0, 0, 0, date("m", $now)+1, 1, date("Y", $now)); // + 1 month offset
        $stop_month = date('Y-m-01 00:00:00', $stop_stmp);

        $start_date = $this->calcSumStartDate($course_id);
	$stmp = strtotime($start_date);
        $end_stmp = mktime(0, 0, 0, date("m", $stmp)+1, 1, date("Y", $stmp)); // min time + 1 month
        $end_date = date('Y-m-01 00:00:00', $end_stmp);
        
        while ($end_date <= $stop_month)
            list($start_date, $end_date, $end_stmp) = $this->doSummarize($course_id, $start_date, $end_date, $end_stmp);
    }
    
    private function calcSumStartDate($course_id) {
        $sql_0 = "SELECT min(day) as min_date
                    FROM actions_daily
                   WHERE course_id = " . intval($course_id);
        
        $result = db_query($sql_0);
        while ($row = mysql_fetch_assoc($result))
            $start_date = $row['min_date'];
	if (empty($start_date))
            $start_date = '2003-01-01 00:00:00';
        mysql_free_result($result);
        
        return $start_date;
    }
    
    private function doSummarize($course_id, $start_date, $end_date, $end_stmp) {
        $sql_1 = "SELECT DISTINCT module_id
                    FROM actions_daily
                   WHERE course_id = $course_id";
        
        $result = db_query($sql_1);
        while ($row = mysql_fetch_assoc($result)) {
            // edw kanoume douleia gia ka8e module
            $module_id = $row['module_id'];

            $sql_2 = "SELECT SUM(hits) AS visits, sum(duration) AS total_dur 
                        FROM actions_daily
                       WHERE module_id = $module_id 
                         AND course_id = $course_id 
                         AND day >= '$start_date' 
                         AND day < '$end_date' ";

            $result_2  = db_query($sql_2);
            $row2      = mysql_fetch_assoc($result_2);
            $visits    = $row2['visits'];
            $total_dur = intval($row2['total_dur']);
            mysql_free_result($result_2);

            $sql_3 = "INSERT INTO actions_summary SET 
                        module_id  = $module_id, 
                        course_id  = $course_id, 
                        visits     = $visits, 
                        start_date = '$start_date', 
                        end_date   = '$end_date', 
                        duration   = $total_dur ";
            $result_3 = db_query($sql_3);
            @mysql_free_result($result_3);

            $sql_4 = "DELETE FROM actions_daily 
                            WHERE module_id = $module_id 
                              AND course_id = $course_id
                              AND day      >= '$start_date' 
                              AND day       < '$end_date' ";
            $result_4 = db_query($sql_4);
            @mysql_free_result($result_4);
        }
        mysql_free_result($result);

        // next month
        $stmp       = $end_stmp;
        $end_stmp = mktime(0, 0, 0, date("m", $stmp)+1, 1, date("Y", $stmp)); // end time + 1 month
        $end_date   = date('Y-m-01 00:00:00', $end_stmp);
        $start_date = date('Y-m-01 00:00:00', $stmp);
        
        return array($start_date, $end_date, $end_stmp);
    }
}

class action_type {
    function get_action_type_id($action_name) {
        switch ($action_name) {
            case 'access':
                    return 1;
            case 'exit':
                    return 2;
            default:
                    return false;
        }
    }
}

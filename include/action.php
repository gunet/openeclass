<?php

define('DEFAULT_MAX_DURATION', 900);

class action {

    function record($module_id, $action_name = 'access') {
        global $uid, $course_id;

        if (!isset($course_id)) {
            return;
        }

        $action_type = new action_type();
        $action_type_id = $action_type->get_action_type_id($action_name);
        $exit = $action_type->get_action_type_id('exit');

        ////// ophelia -28-08-2006 : add duration to previous
        $last_id = $diff = $last_module = 0;
        $result = Database::get()->querySingle("SELECT id, TIME_TO_SEC(TIMEDIFF(NOW(), last_update)) AS diff, module_id
                                                FROM actions_daily
                                                WHERE user_id = ?d
                                                AND course_id = ?d
                                                AND day = DATE(NOW())
                                                ORDER BY last_update DESC LIMIT 1", $uid, $course_id);
        if ($result) {
            $last_id = $result->id;
            $diff = $result->diff;
            $last_module = $result->module_id;            
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
        $stid = 0;
        $result = Database::get()->querySingle("SELECT id
                                    FROM actions_daily
                                    WHERE user_id = ?d
                                    AND module_id = ?d
                                    AND course_id = ?d
                                    AND day = '" . $today . "'", $uid, $module_id, $course_id);        
        $lu = '';
        if ($update_lastdt) {
            $lu = ' , last_update = NOW() ';
        }

        if ($result) {
            $stid = $result->id;
            Database::get()->query("UPDATE actions_daily SET
                    	hits = hits + $hits,
                    	duration = duration + $diffduration
                    	$lu
                    	WHERE id = $stid");
        } else {
            Database::get()->query("INSERT INTO actions_daily SET
                    	user_id = ?d,
                    	module_id = ?d,
                    	course_id = ?d,
                    	hits = 1,
                    	duration = $induration,
                    	day = '" . $today . "'
                    	$lu", $uid, $module_id, $course_id);            
        }        
    }

// ophelia 2006-08-02: per month and per course
    function summarize($course_id = null) {
        if ($course_id == null) {
            $course_id = $GLOBALS['course_id'];
        }
        // set start/stop dates
        $now = time();
        $stop_stmp = mktime(0, 0, 0, date("m", $now) - (get_config('actions_expire_interval') - 1), 1, date("Y", $now)); // minus proper amount of months
        $stop_month = date('Y-m-01 00:00:00', $stop_stmp);

        $start_date = $this->calcSumStartDate($course_id);
        $stmp = strtotime($start_date);
        $end_stmp = mktime(0, 0, 0, date("m", $stmp) + 1, 1, date("Y", $stmp)); // min time + 1 month
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
        $stop_stmp = mktime(0, 0, 0, date("m", $now) + 1, 1, date("Y", $now)); // + 1 month offset
        $stop_month = date('Y-m-01 00:00:00', $stop_stmp);

        $start_date = $this->calcSumStartDate($course_id);
        $stmp = strtotime($start_date);
        $end_stmp = mktime(0, 0, 0, date("m", $stmp) + 1, 1, date("Y", $stmp)); // min time + 1 month
        $end_date = date('Y-m-01 00:00:00', $end_stmp);

        while ($end_date <= $stop_month)
            list($start_date, $end_date, $end_stmp) = $this->doSummarize($course_id, $start_date, $end_date, $end_stmp);
    }

    
    private function calcSumStartDate($course_id) {
       
        $row = Database::get()->querySingle("SELECT MIN(day) as min_date
                        FROM actions_daily
                       WHERE course_id = ?d", $course_id);
         
        if ($row) {
            $start_date = $row->min_date;
        } else {
            $start_date = '2003-01-01 00:00:00';
        }
        return $start_date;
    }

    private function doSummarize($course_id, $start_date, $end_date, $end_stmp) {
        
        $result = Database::get()->queryArray("SELECT DISTINCT module_id
                                    FROM actions_daily
                                   WHERE course_id = ?d", $course_id);
        
        foreach ($result as $row) {
            // edw kanoume douleia gia ka8e module
            $module_id = $row->module_id;
            
            $row2 = Database::get()->querySingle("SELECT IFNULL(SUM(hits),0) AS visits, IFNULL(SUM(duration),0) AS total_dur 
                        FROM actions_daily
                       WHERE module_id = ?d
                         AND course_id = ?d
                         AND day >= '$start_date' 
                         AND day < '$end_date'", $module_id, $course_id);            
            $visits = $row2->visits;
            $total_dur = $row2->total_dur;            
            
	    $result_3 = Database::get()->query("INSERT INTO actions_summary SET 
                                module_id  = ?d, 
                                course_id  = ?d, 
                                visits = ?d, 
                                start_date = '$start_date', 
                                end_date = '$end_date', 
                                duration = ?d", $module_id, $course_id, $visits, $total_dur);
            
           $result_4 = Database::get()->query("DELETE FROM actions_daily 
                                    WHERE module_id = ?d 
                                      AND course_id = ?d
                                      AND day >= '$start_date' 
                                      AND day < '$end_date'", $module_id, $course_id);
        }
        // next month
        $stmp = $end_stmp;
        $end_stmp = mktime(0, 0, 0, date("m", $stmp) + 1, 1, date("Y", $stmp)); // end time + 1 month
        $end_date = date('Y-m-01 00:00:00', $end_stmp);
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

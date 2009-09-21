<?php

define('DEFAULT_MAX_DURATION', 900);

class action {

    function record($module_name, $action_name = "access") {
        global $uid, $currentCourseID;
        $action_type = new action_type();
        $action_type_id = $action_type->get_action_type_id($action_name);
        $exit = $action_type->get_action_type_id('exit');
        $module_id = $this->get_module_id($module_name);

        ###ophelia -28-08-2006 : add duration to previous
        $sql = "SELECT id, TIME_TO_SEC(TIMEDIFF(NOW(), date_time)) AS diff, action_type_id
                FROM actions
                WHERE user_id = $uid
                ORDER BY id DESC LIMIT 1";
        $last_id = $diff = $last_action = 0;
        $result = db_query($sql, $currentCourseID);
        if ($result and mysql_num_rows($result) > 0) {
                list($last_id, $diff, $last_action) = mysql_fetch_row($result);
                mysql_free_result($result);
                # Update previous action with corect duration
                if ($last_id and $last_action != $exit and $diff < DEFAULT_MAX_DURATION) {
                        $sql = "UPDATE actions
                                SET duration = $diff
                                WHERE id = $last_id";
                        db_query($sql, $currentCourseID);
                }
        }
        if ($action_type_id == $exit) {
                $duration = 0;
        } else {
                $duration = DEFAULT_MAX_DURATION;
        }
        $sql = "INSERT INTO actions SET
                    module_id = $module_id,
                    user_id = $uid,
                    action_type_id = $action_type_id,
                    date_time = NOW(),
                    duration = " . $duration;
        db_query($sql, $currentCourseID);
    }


#ophelia 2006-08-02: per month and per course
    function summarize() {
        global $currentCourseID;
        
        ## edw ftia3e tis hmeromhnies
        $now = date('Y-m-d H:i:s');
        $current_month = date('Y-m-01 00:00:00');
        
        $sql_0 = "SELECT min(date_time) as min_date FROM actions";   //gia na doume
        $sql_1 = "SELECT DISTINCT module_id FROM actions ";  //arkei gia twra.

 
        $result = db_query($sql_0, $currentCourseID);
        while ($row = mysql_fetch_assoc($result)) {
            $start_date = $row['min_date'];
        }
	if (empty($start_date)) {
		$start_date = '2003-01-01 00:00:00';
	}
        mysql_free_result($result);

	$stmp = strtotime($start_date);
        $end_stmp = $stmp + 31*24*60*60;  //min time + 1 month
        $end_date = date('Y-m-01 00:00:00', $end_stmp);
        while ($end_date < $current_month){
            $result = db_query($sql_1, $currentCourseID);
            while ($row = mysql_fetch_assoc($result)) {
                #edw kanoume douleia gia ka8e module
                $module_id = $row['module_id'];

                $sql_2 = "SELECT count(id) as visits, sum(duration) as total_dur FROM actions ".
                    " WHERE module_id = $module_id AND ".
                    " date_time >= '$start_date' AND ".
                    " date_time < '$end_date' ";
                    
                $result_2 = db_query($sql_2, $currentCourseID);
                while ($row2 = mysql_fetch_assoc($result_2)) {
                    $visits = $row2['visits'];
                    $total_dur = $row2['total_dur'];
                }
                mysql_free_result($result_2);
                $sql_3 = "INSERT INTO actions_summary SET ".
                    " module_id = $module_id, ".
                    " visits = $visits, ".
                    " start_date = '$start_date', ".
                    " end_date = '$end_date', ".
                    " duration = $total_dur";
                $result_3 = db_query($sql_3, $currentCourseID);
                @mysql_free_result($result_3);
            
                $sql_4 = "DELETE FROM actions ".
                    " WHERE module_id = $module_id ".
                    " AND date_time >= '$start_date' AND ".
                    " date_time < '$end_date'";
                $result_4 = db_query($sql_4, $currentCourseID);
                @mysql_free_result($result_4);
            
            }
            mysql_free_result($result);
            
            #next month
            $start_date = $end_date;
	    $stmp = $end_stmp;	
            $end_stmp += 31*24*60*60;  //end time + 1 month
            $end_date = date('Y-m-01 00:00:00', $end_stmp);
	    $start_date = date('Y-m-01 00:00:00', $stmp);
        }
    }


    function get_module_id($module_name) {
        global $currentCourseID;
        $sql = "SELECT id FROM accueil WHERE define_var = '$module_name'";
        $result = db_query($sql, $currentCourseID);
        if ($result and mysql_num_rows($result) > 0) {
                list($id) = mysql_fetch_row($result);
                mysql_free_result($result);
                return $id;
        } else {
                return 0;
        }
    }

}

class action_type {
    function get_action_type_id($action_name) {
        global $currentCourseID;
        $sql = "SELECT id FROM action_types WHERE name = '$action_name'";
        $result = db_query($sql, $currentCourseID);
        if ($result and mysql_num_rows($result) > 0) {
                list($id) = mysql_fetch_row($result);
                mysql_free_result($result);
                return $id;
        } else {
                return false;
        }
    }
}

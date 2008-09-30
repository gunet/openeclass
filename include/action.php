<?php

class action {

    function record($module_name, $action_name = "access") {
        global $uid, $currentCourseID;
        $action_type = new action_type();
        $action_type_id = $action_type->get_action_type_id($action_name);
        $module_id = $this->get_module_id($module_name);

        ###ophelia -28-08-2006 : add duration to previous
        
        $sql = "SELECT id, NOW()-date_time as diff FROM actions ".
               "WHERE user_id = '$uid' ".
               " ORDER BY date_time DESC LIMIT 1 ";

        $result = db_query($sql, $currentCourseID);
        $last_id = 0;
        $diff = 0;
        while ($row = mysql_fetch_assoc($result)) {
            $last_id = $row['id'];
            $diff = $row['diff'];
        }
        mysql_free_result($result);

        //if ($last_time) {
            $duration = $diff;
            
            if ($duration < 900) {
                $sql = "UPDATE actions SET duration = '$duration' WHERE id = '$last_id' ";
                $result = db_query($sql, $currentCourseID);
                @mysql_free_result($result);
            }
        //}
        ##########################
        
        $sql = "INSERT INTO actions SET ".
                " module_id = '$module_id', ".
                " user_id = '$uid', ".
                " action_type_id = '$action_type_id', ".
                " date_time = NOW(), ".
                " duration = 900 ";    //ophelia 28-8-06
        $result = db_query($sql, $currentCourseID);
        @mysql_free_result($result);
        
        
        
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
            $min_date = $row['min_date'];
        }
        mysql_free_result($result);

        $end_stmp = strtotime($min_date)+ 31*24*60*60;  //min time + 1 month
        $start_date = $min_date;
        $end_date = date('Y-m-01 00:00:00', $end_stmp);

        while ($end_date < $current_month){
            $result = db_query($sql_1, $currentCourseID);
            while ($row = mysql_fetch_assoc($result)) {
                #edw kanoume douleia gia ka8e module
                $module_id = $row['module_id'];

                $sql_2 = "SELECT count(id) as visits, sum(duration) as total_dur FROM actions ".
                    " WHERE module_id='$module_id' AND ".
                    " date_time >= '$start_date' AND ".
                    " date_time < '$end_date' ";
                    
                $result_2 = db_query($sql_2, $currentCourseID);
                while ($row2 = mysql_fetch_assoc($result_2)) {
                    $visits = $row2['visits'];
                    $total_dur = $row2['total_dur'];
                }
                mysql_free_result($result_2);
                print "$total_dur";
                $sql_3 = "INSERT INTO actions_summary SET ".
                    " module_id = '$module_id', ".
                    " visits = '$visits', ".
                    " start_date = '$start_date', ".
                    " end_date = '$end_date', ".
                    " duration = '$total_dur' ";
                $result_3 = db_query($sql_3, $currentCourseID);
                @mysql_free_result($result_3);
            
                $sql_4 = "DELETE FROM actions ".
                    " WHERE module_id = '$module_id' ".
                    " AND date_time >= '$start_date' AND ".
                    " date_time < '$end_date' ";
                $result_4 = db_query($sql_4, $currentCourseID);
                @mysql_free_result($result_4);
            
            }
            mysql_free_result($result);
            
            #next month
            $start_date = $end_date;
            $end_stmp =strtotime($end_date)+ 31*24*60*60;  //end time + 1 month
            $end_date = date('Y-m-01 00:00:00', $end_stmp);
        }
    }


    function get_module_id($module_name) {
        global $currentCourseID;
        $sql = "SELECT id FROM accueil WHERE define_var = '$module_name'";
        $result = db_query($sql, $currentCourseID);
        while ($row = mysql_fetch_assoc($result)) {
            $id = $row['id'];
        }
        mysql_free_result($result);

        return $id;

    }

}

class action_type {
    function get_action_type_id($action_name) {
        global $currentCourseID;
        $sql = "SELECT id FROM action_types WHERE name = '$action_name'";
        $result = db_query($sql, $currentCourseID);
        while ($row = mysql_fetch_assoc($result)) {
            $id = $row['id'];
        }
        mysql_free_result($result);
        return $id;
    }
}

?>

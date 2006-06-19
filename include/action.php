<?php

class action {

    function record($module_name, $action_name = "access") {
        global $uid, $currentCourseID;
        $action_type = new action_type();
        $action_type_id = $action_type->get_action_type_id($action_name);
        $module_id = $this->get_module_id($module_name);
        $sql = "INSERT INTO actions SET ".
                " module_id = '$module_id', ".
                " user_id = '$uid', ".
                " action_type_id = '$action_type_id', ".
                " date_time = NOW() ";
        $result = db_query($sql, $currentCourseID);
        @mysql_free_result($result);
    }

    function summarize() {

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

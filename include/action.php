<?php

class action {
    static $module_ids = array();
    function record($module_name, $action_name = "access") {
        global $uid, $dbname;
        $action_type_id = action_type::get_action_type_id($action_name);
        $module_id = action::get_module_id($module_name);
        $sql = "INSERT INTO actions SET ".
                " module_id = '$module_id', ".
                " user_id = '$uid', ".
                " action_type_id = '$action_type_id', ".
                " date_time = NOW() ";
        $result = db_query($sql, $dbname);
    }

    function summarize() {

    }

    function get_module_id($module_name) {
        global $dbname;
        if (empty(action::$module_ids)) {
            $sql = "SELECT id, define_var AS name FROM accueil ";
            $result = db_query($sql, $dbname);
            while ($row = mysql_fetch_assoc($result)) {
                action::$module_ids[$row['name']] = $row['id'];
            }
        }

        return action::$module_ids[$module_name];

    }

}

class action_type {
    static $ids = array();
    function get_action_type_id($action_name) {
        global $dbname;
        if (empty(action_type::$ids)) {
            $sql = "SELECT * FROM action_types";
            $result = db_query($sql, $dbname);
            while ($row = mysql_fetch_assoc($result)) {
                action_type::$ids[$row['name']] = $row['id'];
            }
            mysql_free_result($result);
        }
        return action_type::$ids[$action_name];
    }
}

?>

<?php

define ("ACCUEIL_ID_USAGE", 23);

class action {
    function record($accueil_id, $action_name = "access") {
        global $uid, $dbname;
        mysql_select_db($dbname);
        $action_type_id = action_type::get_action_type_id($action_name);
        $sql = "INSERT INTO actions SET ".
                " accueil_id = '$accueil_id', ".
                " user_id = '$uid', ".
                " action_type_id = '$action_type_id', ".
                " date_time = NOW() ";
        $result = mysql_query($sql);
    }
}

class action_type {
    static $ids = array();
    function get_action_type_id($action_name) {
        global $dbname;
        mysql_select_db($dbname);
        if (empty(action_type::$ids)) {
            $sql = "SELECT * FROM action_types";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                action_type::$ids[$row['name']] = $row['id'];
            }
            mysql_free_result($result);
        }
        return action_type::$ids[$action_name];
    }
}

?>

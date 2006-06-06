<?php


class usage_module {
    var $course_db_name;


    var $queries = array(
        'add_module' =>
            "INSERT IGNORE INTO accueil VALUES (
                '___MODULE_ID___',
                '___MODULE_NAME___',
                '../../modules/usage/usage.php',
                '../../../images/usage.gif',
                '___IS_VISIBLE___',
                '1',
                '../../../images/pastillegris.png',
                'MODULE_ID_USAGE'
            )",


        'create_tables' => array (
            'action_types' => "CREATE TABLE action_types (
                        id int(11) NOT NULL auto_increment,
                        name varchar(200),
                        PRIMARY KEY (id))",
            'action_types_fill' => "INSERT INTO action_types VALUES ('1', 'access')",
            'actions' => "CREATE TABLE actions (
                        id int(11) NOT NULL auto_increment,
                        user_id int(11) NOT NULL,
                        module_id int(11) NOT NULL,
                        action_type_id int(11) NOT NULL,
                        date_time DATETIME NOT NULL default '0000-00-00 00:00:00',
                        PRIMARY KEY (id))",

            'actions_summary' => "CREATE TABLE actions_summary (
                        id int(11) NOT NULL auto_increment,
                        module_id int(11) NOT NULL,
                        start_date DATETIME NOT NULL default '0000-00-00 00:00:00',
                        end_date DATETIME NOT NULL default '0000-00-00 00:00:00',
                        PRIMARY KEY (id))",
        ),
    );


    function create_course($module_id, $module_name = 'Στατιστικά Χρήσης', $is_visible = false) {
        $this->insert_module($module_id, $module_name, $is_visible);
        foreach ($this->queries['create_tables'] as $key => $query) {
            db_query($query);
        }

    }

    function insert_module($module_id, $module_name = 'Στατιστικά Χρήσης', $is_visible = false) {
        $search = array (
            '___MODULE_ID___',
            '___MODULE_NAME___',
            '___IS_VISIBLE___',
        );
        $replace = array (
            $module_id,
            $module_name,
            $is_visible,
        );
        $query = $this->queries['add_module'];
        $query = str_replace($search, $replace, $query);

        if ( $this->course_db_name) {
            db_query($query, $this->course_db_name);
        } else {
            db_query($query);
        }
    }

    function upgrade($module_id, $course_db_name) {
        $this->course_db_name = $course_db_name;
        $this->insert_module($module_id);

        if (!mysql_table_exists($course_db_name, 'action_types')) {
            $query = $this->queries['action_types'];
            db_query($query, $course_db_name);
            $query = $this->queries['action_types_fill'];
            db_query($query, $course_db_name);
        }

        if (!mysql_table_exists($course_db_name, 'actions')) {
            $query = $this->queries['actions'];
            db_query($query, $course_db_name);
        }

        if (!mysql_table_exists($course_db_name, 'actions_summary')) {
            $query = $this->queries['actions_summary'];
            db_query($query, $course_db_name);
        }
    }

}

?>

<?php

// Do the queries to calculate usage between timestamps $start and $end
// Returns a MySQL resource, where fetching rows results in:
// duration, nom, prenom, user_id, am
function user_duration_query($currentCourseID, $start = false, $end = false, $group = false)
{ 
        global $mysqlMainDb;

        mysql_select_db($mysqlMainDb);
        
        $sql_where = array();
        if ($group !== false) {
                $sql_where[] = "c.user_id IN (SELECT user FROM `$currentCourseID`.user_group WHERE TEAM = $group)";
        }
        if ($start !== false) {
                $sql_where[] = 'c.date_time > ' . quote($start . ' 00:00:00');
        }
        if ($end !== false) {
                $sql_where[] = 'c.date_time < ' . quote($end . ' 23:59:59');
        }
        if (count($sql_where)) {
                $where = 'WHERE ' . join(' AND ', $sql_where);
        } else {
                $where = '';
        }

        db_query("CREATE TEMPORARY TABLE duration AS
                  SELECT SUM(c.duration) AS duration, c.user_id AS user_id
                  FROM `$currentCourseID`.actions AS c " .
                  $where .  " GROUP BY c.user_id");

        return db_query("SELECT duration.duration AS duration,
                                   user.nom AS nom,
                                   user.prenom AS prenom,
                                   user.user_id AS user_id,
                                   user.am AS am
                            FROM user LEFT JOIN cours_user ON user.user_id = cours_user.user_id
                                      LEFT JOIN duration ON user.user_id = duration.user_id
                            WHERE cours_user.code_cours = '$currentCourseID'
                            GROUP BY duration.user_id
                            ORDER BY nom, prenom");
}


// This should be called after processing all tables
function user_duration_query_end()
{
        global $mysqlMainDb;

        mysql_select_db($mysqlMainDb);
        
        db_query('DROP TEMPORARY TABLE duration', $mysqlMainDb);
}

<?php

// Do the queries to calculate usage between timestamps $start and $end
// Returns a MySQL resource, where fetching rows results in:
// duration, nom, prenom, user_id, am
function user_duration_query($course_code, $course_id, $start = false, $end = false, $group = false)
{ 
        global $mysqlMainDb, $course_id;

        mysql_select_db($mysqlMainDb);
        
        if ($start !== false AND $end !== false) {
                $date_where = 'WHERE c.date_time BETWEEN ' .
                              quote($start . ' 00:00:00') . ' AND ' .
                              quote($end . ' 23:59:59') . '
                              AND course_id = ' . $course_id;
        } elseif ($start !== false) {
                $date_where = 'WHERE c.date_time > ' . quote($start . ' 00:00:00') . 'AND course_id = ' . $course_id;
        } elseif ($end !== false) {
                $date_where = 'WHERE c.date_time < ' . quote($end . ' 23:59:59') . 'AND course_id = ' . $course_id;
        } else {
                $date_where = 'WHERE course_id = ' . $course_id;
        }

        db_query("CREATE TEMPORARY TABLE duration AS
                  SELECT SUM(c.duration) AS duration, c.user_id AS user_id
                  FROM actions AS c " .
                  $date_where .  " GROUP BY c.user_id");

        if ($group !== false) {
                $from = "`group_members` AS groups
                                LEFT JOIN user ON groups.user_id = user.user_id";
                $and = "AND groups.group_id = $group";
        } else {
                $from = "user";
                $and = '';
        }
        return db_query("SELECT duration.duration AS duration,
                                   user.nom AS nom,
                                   user.prenom AS prenom,
                                   user.user_id AS user_id,
                                   user.am AS am
                            FROM $from
                                      LEFT JOIN course_user ON user.user_id = course_user.user_id
                                      LEFT JOIN duration ON user.user_id = duration.user_id
                            WHERE course_user.course_id = $course_id
                                  $and
                            ORDER BY nom, prenom");
}


// This should be called after processing all tables
function user_duration_query_end()
{
        global $mysqlMainDb;

        mysql_select_db($mysqlMainDb);
        
        db_query('DROP TEMPORARY TABLE duration', $mysqlMainDb);
}

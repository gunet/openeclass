<?php

/* ========================================================================
 * Open eClass 3.0
* E-learning and Course Management System
* ========================================================================
* Copyright 2003-2012  Greek Universities Network - GUnet
* A full copyright notice can be read in "/info/copyright.txt".
* For a full list of contributors, see "credits.txt".
*
* Open eClass is an open platform distributed in the hope that it will
* be useful (without any warranty), under the terms of the GNU (General
		* Public License) as published by the Free Software Foundation.
* The full license can be read in "/info/license/license_gpl.txt".
*
* Contact address: GUnet Asynchronous eLearning Group,
*                  Network Operations Center, University of Athens,
*                  Panepistimiopolis Ilissia, 15784, Athens, Greece
*                  e-mail: info@openeclass.org
* ======================================================================== */

// Do the queries to calculate usage between timestamps $start and $end
// Returns a MySQL resource, where fetching rows results in:
// duration, nom, prenom, user_id, am
function user_duration_query($course_code, $course_id, $start = false, $end = false, $group = false)
{

        if ($start !== false AND $end !== false) {
                $date_where = 'WHERE c.day BETWEEN ' .
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

        if ($group !== false) {
                $from = "`group_members` AS groups
                                LEFT JOIN user ON groups.user_id = user.user_id";
                $and = "AND groups.group_id = $group";
                $or = '';
        } else {
                $from = " (select * from user union (SELECT 0 as user_id,
                            '' as nom,
                            'Anonymous' as prenom,
                            null as username,
                            null as password,
                            null as email,
                            null as statut,
                            null as phone,
                            null as am,
                            null as registered_at,
                            null as expires_at,
                            null as perso,
                            null as lang,
                            null as announce_flag,
                            null as doc_flag,
                            null as forum_flag,
                            null as description,
                            null as has_icon,
                            null as verified_mail,
                            null as receive_mail,
                            null as email_public,
                            null as phone_public,
                            null as am_public,
                            null as whitelist,
                            null as last_passreminder)) as user ";
                $and = '';
                $or = ' OR user.user_id = 0 ';
        }
        
        
        return db_query("SELECT SUM(actions_daily.duration) AS duration,
                                   user.nom AS nom,
                                   user.prenom AS prenom,
                                   user.user_id AS user_id,
                                   user.am AS am
                            FROM $from
                                      LEFT JOIN course_user ON user.user_id = course_user.user_id
                                      LEFT JOIN actions_daily ON user.user_id = actions_daily.user_id
                            WHERE (course_user.course_id = $course_id  $or )
                                  $and
                            GROUP BY user_id
                            ORDER BY nom, prenom");
}


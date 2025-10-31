<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

class PointsGame {

    public static function levelUpdate($uid, $gid, $points, $curlevel) {
        $level  = Database::get()->querySingle("select id from points_game_levels 
                                                where points_game = ?d and required_points <= ?d 
                                                order by required_points desc limit 1", $gid, $points);
        if($level) {
            if($level->id != $curlevel) {
                Database::get()->query("update user_points_game_points set current_level = ?d 
                                        where user = ?d and points_game = ?d", $level->id, $uid, $gid);
            }
        }
    }

}

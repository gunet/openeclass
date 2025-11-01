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
        $level  = Database::get()->querySingle("SELECT id FROM points_game_levels 
                                                WHERE points_game = ?d AND required_points <= ?d 
                                                ORDER BY required_points DESC LIMIT 1", $gid, $points);
        if($level) {
            if($level->id != $curlevel) {
                Database::get()->query("UPDATE user_points_game_points SET current_level = ?d 
                                        WHERE user = ?d AND points_game = ?d", $level->id, $uid, $gid);
            }
        }
    }

    public static function getNextLevelInfo($uid, $gid) {
        $points_q = Database::get()->querySingle("SELECT total_points AS p, current_level AS l FROM user_points_game_points WHERE user=?d AND points_game=?d", $uid, $gid);
        if($points_q) {
            if($points_q->l) {
                $q = Database::get()->querySingle("SELECT required_points AS p FROM points_game_levels WHERE id=?d",$points_q->l);
            }

            $performance = [
                'points' => $points_q->p,
                'current_level' => $points_q->l,
                'current_level_min_points' => ($points_q->l) ? $q->p : null
            ];
        } else {
            $performance = [
                'points' => 0,
                'current_level' => null,
                'current_level_min_points' => null
            ];
        }

        $next_q = Database::get()->querySingle("SELECT id, required_points FROM points_game_levels 
                                                WHERE points_game = ?d AND required_points > ?d
                                                ORDER BY required_points ASC LIMIT 1", $gid, $performance['points']);

        //user hasn't reach first level yet
        if(!$performance['current_level'] && $next_q) {
            $max = (int) $next_q->required_points;
            $progress = max(0, $performance['points']);
            $percent = ($max > 0) ? ($progress / $max) * 100 : 100;
            $percent = round(min(100, max(0, $percent)), 2);

            return [
                'current_level_id' => null,
                'next_level_id' => $next_q->id,
                'points_needed_for_next' => $max - $performance['points'],
                'progress_percentage' => $percent
            ];
        }

        //user is in max level
        if($performance['current_level'] && !$next_q) {
            return [
                'current_level_id' => $performance['current_level'],
                'next_level_id' => null,
                'points_needed_for_next' => null,
                'progress_percentage' => 100
            ];
        }

        //typical case where user has reached one level and is progressing to the next one
        $min = (int) $performance['current_level_min_points'];
        $max = (int) $next_q->required_points;
        $span = $max - $min;
        $progress = $performance['points'] - $min;
        $percent = ($span > 0) ? ($progress / $span) * 100 : 100;
        $percent = round(min(100, max(0, $percent)), 2);

        return [
            'current_level_id' => $performance['current_level'],
            'next_level_id' => $next_q->id,
            'points_needed_for_next' => $max - $performance['points'],
            'progress_percentage' => $percent
        ];
    }

}

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

abstract class CriterionAbstract {

    protected $id;
    protected $type;
    protected $activityType;
    protected $module;
    protected $resource;
    protected $threshold;
    protected $operator;
    //only for points games
    protected $points_game;
    protected $points;
    protected $criterion_type;
    protected $max_points_from_criterion;
    protected $max_points_from_criterion_time_period;
    protected $time_period_in_days;

    protected $table;
    protected $field;

    protected $rule;
    protected $ruler;

    /*public static function initWithId($id, $type) {
        $instance = new static();
        $instance->loadById($id, $type);
        return $instance;
    }

    protected function loadById($id, $type) {
        echo "running loadById(): select * from \$type_criterion WHERE id = \$id \n";
        // TODO: $crit = FROM DB
        $this->loadByProperties($crit);
    }*/

    public static function initWithProperties($properties) {
        $instance = new static();
        $instance->loadByProperties($properties);
        return $instance;
    }

    protected function loadByProperties($properties) {
        $this->id = $properties->id;
        $this->type = $properties->type;
        $this->activityType = $properties->activity_type;
        $this->module = $properties->module;
        $this->resource = $properties->resource;
        $this->threshold = $properties->threshold;
        $this->operator = $properties->operator;

        if ($properties->type == 'points_game') {
            $this->points_game = $properties->points_game;
            $this->points = $properties->points;
            $this->criterion_type = $properties->criterion_type;
            $this->max_points_from_criterion = $properties->max_points_from_criterion;
            $this->max_points_from_criterion_time_period = $properties->max_points_from_criterion_time_period;
            $this->time_period_in_days = $properties->time_period_in_days;
        }

        $this->table = 'user_' . $properties->type . '_criterion';
        $this->field = $properties->type . '_criterion';

        $this->buildRule();
    }

    abstract protected function buildRule();

    abstract public function evaluate($context);

    protected function assertedAction($context, $points = null) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        if ($uid) {
            $exists = Database::get()->querySingle("select count(id) as cnt from $this->table where user = ?d and $this->field = ?d", $uid, $this->id)->cnt;
            if (!$exists) {
                if(is_null($points)) { //badge or certificate asserted action
                    Database::get()->query("insert into $this->table (user, $this->field, created) values (?d, ?d, ?t)", $uid, $this->id, gmdate('Y-m-d H:i:s'));
                } else { //points game one-time asserted action
                    Database::get()->query("insert into $this->table (user, $this->field, points_awarded, created) values (?d, ?d, ?d, ?t)", $uid, $this->id, $points, gmdate('Y-m-d H:i:s'));
                    //add awarded points to user
                    $points_q = Database::get()->querySingle("select id, total_points, current_level from user_points_game_points where user = ?d and points_game = ?d", $uid, $this->points_game);
                    if($points_q) {
                        $total_points = $points_q->total_points + $points;
                        $current_level = $points_q->current_level;
                        Database::get()->query("update user_points_game_points set total_points = ?d where id = ?d", $total_points, $points_q->id);
                    } else {
                        $total_points = $points;
                        $current_level = NULL;
                        Database::get()->query("insert into user_points_game_points (user, points_game, total_points) values (?d, ?d, ?d)", $uid, $this->points_game, $total_points);
                    }
                    PointsGame::levelUpdate($uid, $this->points_game, $total_points, $current_level);
                }
            }
        }
    }

    protected function notAssertedAction($context) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        if ($uid) {
            Database::get()->query("delete from $this->table where user = ?d and $this->field = ?d", $uid, $this->id);
        }
    }

    protected function assignPointsRecurringAction($uid) {
        Database::get()->query("insert into user_points_game_criterion (user, points_game_criterion, points_awarded, created) values (?d, ?d, ?d, ?t)", $uid, $this->id, $this->points, gmdate('Y-m-d H:i:s'));
        //add awarded points to user
        $points_q = Database::get()->querySingle("select id, total_points, current_level from user_points_game_points where user = ?d and points_game = ?d", $uid, $this->points_game);
        if($points_q) {
            $total_points = $points_q->total_points + $this->points;
            $current_level = $points_q->current_level;
            Database::get()->query("update user_points_game_points set total_points = ?d where id = ?d", $total_points, $points_q->id);
        } else {
            $total_points = $this->points;
            $current_level = NULL;
            Database::get()->query("insert into user_points_game_points (user, points_game, total_points) values (?d, ?d, ?d)", $uid, $this->points_game, $total_points);
        }
        PointsGame::levelUpdate($uid, $this->points_game, $total_points, $current_level);
    }
}


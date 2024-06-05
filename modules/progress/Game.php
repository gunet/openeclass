<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * ========================================================================
 */

require_once 'GameAbstract.php';

class Game extends GameAbstract {

    public function __construct() {
        $this->ruler = new Hoa\Ruler\Ruler();

        // fix for hiqdev protocol
        if (!array_key_exists('argv', $_SERVER)) {
            $_SERVER['argv'] = array();
            $_SERVER['argv'][0] = '';
            $_SERVER['argc'] = 0;
        }
    }

    protected function buildRule() {
        $satisfiesAllCriteria = function($userCriterionIds) {
            $ret = true;

            foreach($this->criterionIds as $crit) {
                if (!in_array($crit, $userCriterionIds)) {
                    $ret = false;
                    break;
                }
            }

            return $ret;
        };

        $asserter = new Hoa\Ruler\Visitor\Asserter();
        $asserter->setOperator('satisfiesallcriteria', $satisfiesAllCriteria);
        $this->ruler->setAsserter($asserter);

        $this->rule = 'satisfiesallcriteria(userCriterionIds)';
    }

    private function evaluateProper($context, $terminal = false) {
        if ($terminal) {
            $this->prepareForContextTerminal($context);
        } else {
            $this->prepareForContext($context);
        }

        if (!$this->autoassign) {
            return false;
        }

        if ($this->ruler->assert($this->rule, $context)) {
            $this->assertedAction($context);
            return true;
        } else {
            $this->notAssertedAction($context);
            return false;
        }
    }

    public function evaluate($context) {
        return $this->evaluateProper($context, false);
    }

    public function evaluateTerminal($context) {
        return $this->evaluateProper($context, true);
    }

    private static function checkCompletenessProper($uid, $course_id, $unit_id, $session_id, $terminal = false) {
        $context = new Hoa\Ruler\Context();
        $context['uid'] = $uid;
        $context['courseId'] = $course_id;
        $context['unit_id'] = $unit_id;
        $context['session_id'] = $session_id;
        $context['userCriterionIds'] = array();

        $iter = array('badge', 'certificate');

        foreach ($iter as $key) {
            if ($unit_id or $session_id) {
                if ($key == 'certificate') {
                    continue;
                }
                $gameQ = "select g.*, '$key' as type from $key g where course_id = ?d and active = 1 and (expires is null or expires > ?t) and unit_id = ".$unit_id." and session_id =".$session_id;
            } else {
                if ($key == 'badge') {
                    $gameQ = "select g.*, '$key' as type from $key g where course_id = ?d and active = 1 and (expires is null or expires > ?t) and unit_id = ".$unit_id." and session_id =".$session_id;
                } else {
                    $gameQ = "select g.*, '$key' as type from $key g where course_id = ?d and active = 1 and (expires is null or expires > ?t)";
                }
            }
            Database::get()->queryFunc($gameQ, function($game) use ($key, $uid, &$context, $terminal) {
                // get game child-criterion ids
                $criterionIds = array();
                Database::get()->queryFunc("select c.id from {$key}_criterion c where $key = ?d ", function($crit) use (&$criterionIds) {
                    $criterionIds[] = $crit->id;
                }, $game->id);
                $game->criterionIds = $criterionIds;
                // get user satisfied criterion ids
                $userCriterionIds = array();
                $critQ = "select uc.{$key}_criterion as criterion from user_{$key}_criterion uc where user = ?d";
                Database::get()->queryFunc($critQ, function($uc) use (&$userCriterionIds, $criterionIds) {
                    if (in_array($uc->criterion, $criterionIds)) {
                        $userCriterionIds[] = $uc->criterion;
                    }
                }, $uid);
                $context['userCriterionIds'] = $userCriterionIds;
                $gameObj = Game::initWithProperties($game);
                if ($terminal) {
                    $gameObj->evaluateTerminal($context);
                } else {
                    $gameObj->evaluate($context);
                }
            }, $course_id, gmdate('Y-m-d H:i:s'));
        }
    }

    public static function checkCompleteness($uid, $course_id, $unit_id = 0, $session_id = 0) {
        self::checkCompletenessProper($uid, $course_id, $unit_id, $session_id, false);
    }

    public static function checkCompletenessTerminal($uid, $course_id, $unit_id = 0, $session_id = 0) {
        self::checkCompletenessProper($uid, $course_id, $unit_id, $session_id, true);
    }
}

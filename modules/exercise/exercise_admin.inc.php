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
global $count;

/**
 * @file exercise_admin.inc.php
 * @brief Create new exercise or modify an existing one
 */
require_once 'modules/search/classes/ConstantsUtil.php';
require_once 'modules/search/classes/SearchEngineFactory.php';
require_once 'modules/tags/moduleElement.class.php';

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['assign_type'])) {
        if ($_POST['assign_type'] == 2) {
            $data = Database::get()->queryArray("SELECT name,id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
        } elseif ($_POST['assign_type'] == 1) {
            $data = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                    FROM user, course_user
                                    WHERE user.id = course_user.user_id
                                    AND course_user.course_id = ?d AND course_user.status = " . USER_STUDENT . "
                                    AND user.id ORDER BY surname", $course_id);
        }
        echo json_encode($data);
        exit;
    }
}

load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('select2');

// the exercise form has been submitted
if (isset($_POST['submitExercise'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $v = new Valitron\Validator($_POST);
    $v->addRule('ipORcidr', function($field, $value, array $params) {
        //matches IPv4/6 and IPv4/6 CIDR ranges
        foreach ($value as $ip){
            $valid = isIPv4($ip) || isIPv4cidr($ip) || isIPv6($ip) || isIPv6cidr($ip);
            if (!$valid) {
                return false;
            }
        }
        return true;
    }, $langIPInvalid);
    $v->rule('required', array('exerciseTitle'));
    $v->rule('numeric', array('exerciseTimeConstraint', 'exerciseAttemptsAllowed'));
    $v->rule('date', array('exerciseEndDate', 'exerciseStartDate'));
    $v->rule('ipORcidr', array('exerciseIPLock'));
    $v->labels(array(
        'exerciseTitle' => "$langTheField $langExerciseName",
        'exerciseTimeConstraint' => "$langTheField $langExerciseConstrain",
        'exerciseAttemptsAllowed' => "$langTheField $langExerciseAttemptsAllowed",
        'exerciseEndDate' => "$langTheField $langFinish",
        'exerciseStartDate' => "$langTheField $langStart",
        'exerciseIPLock' => "$langTheField IPs"
    ));
    if($v->validate()) {
        $exerciseTitle = trim($_POST['exerciseTitle']);
        $objExercise->updateTitle($exerciseTitle);
        $objExercise->updateDescription($_POST['exerciseDescription']);
        $objExercise->setEndMessage($_POST['exerciseEndMessage']);
        if (isset($_POST['feedback_text'])) {
            $objExercise->setFeedback($_POST['feedback_text'], $_POST['feedback_grade']);
        } else {
            $objExercise->setFeedback([], null);
        }
        $objExercise->updateType($_POST['exerciseType']);
        $objExercise->updateRange($_POST['exerciseRange']);
        $objExercise->setCalcGradeMethod($_POST['exerciseCalcGradeMethod']);
        if (isset($_POST['exerciseIPLock'])) {
            $objExercise->updateIPLock(implode(',', $_POST['exerciseIPLock']));
        } else {
            $objExercise->updateIPLock('');
        }

        if (!is_null($_POST['exerciseGradePass']) and floatval($_POST['exerciseGradePass']) > 0) {
            $objExercise->setPassingGrade($_POST['exerciseGradePass']);
        }
        $objExercise->updatePasswordLock($_POST['exercisePasswordLock']);
        $startDateTime_obj = !empty($_POST['exerciseStartDate']) ?
            DateTime::createFromFormat('d-m-Y H:i', $_POST['exerciseStartDate'])->format('Y-m-d H:i:s') : NULL;
        $objExercise->updateStartDate($startDateTime_obj);
        $endDateTime_obj = !empty($_POST['exerciseEndDate']) ?
            DateTime::createFromFormat('d-m-Y H:i', $_POST['exerciseEndDate'])->format('Y-m-d H:i:s') : NULL;
        $objExercise->updateEndDate($endDateTime_obj);
        $objExercise->updateTempSave($_POST['exerciseTempSave']);
        $objExercise->updateTimeConstraint($_POST['exerciseTimeConstraint']);
        $objExercise->updateAttemptsAllowed($_POST['exerciseAttemptsAllowed']);
        $objExercise->updateResults($_POST['dispresults']);
        $objExercise->updateScore($_POST['dispscore']);
        $objExercise->updateAssignToSpecific($_POST['assign_to_specific']);
        if (!isset($_POST['continueAttempt']) or !isset($_POST['continueTimeLimit'])) {
            $objExercise->updateContinueTimeLimit(0);
        } else {
            $objExercise->updateContinueTimeLimit($_POST['continueTimeLimit']);
        }
        if (!isset($_POST['jsPreventCopy'])) {
            $objExercise->setOption('jsPreventCopy', false);
        } else {
            $objExercise->setOption('jsPreventCopy', true);
        }
        if (!isset($_POST['isExam'])) {
            $objExercise->setisExam(0);
        } else {
            $objExercise->setisExam(1);
        }
        if (!isset($_POST['shuffle_answers'])) {
            $objExercise->setOption('ShuffleAnswers', false);
        } else {
            $objExercise->setOption('ShuffleAnswers', true);
        }
        if (!isset($_POST['stricterExamRestriction'])) {
            $objExercise->setOption('stricterExamRestriction', false);
        } else {
            $objExercise->setOption('stricterExamRestriction', true);
        }

        $objExercise->save();
        // reads the exercise ID (only useful for a new exercise)
        $exerciseId = $objExercise->selectId();

        $objExercise->assignTo(filter_input(INPUT_POST, 'ingroup', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY));
        $searchEngine = SearchEngineFactory::create();
        $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_EXERCISE, $exerciseId);

        // tags
        $moduleTag = new ModuleElement($exerciseId);
        if (isset($_POST['tags'])) {
            $moduleTag->syncTags($_POST['tags']);
        } else {
            $moduleTag->syncTags(array());
        }
        redirect_to_home_page('modules/exercise/admin.php?course=' . $course_code . '&exerciseId=' . $exerciseId);
    } else {
        $new_or_modify = isset($_GET['NewExercise']) ? "&NewExercise=Yes" : "&exerciseId=$_GET[exerciseId]&modifyExercise=yes";
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page('modules/exercise/admin.php?course='.$course_code.$new_or_modify);
    }
} else {
    $exerciseId = $objExercise->selectId();
    $exerciseTitle = Session::has('exerciseTitle') ? Session::get('exerciseTitle') : $objExercise->selectTitle();
    $exerciseDescription = Session::has('exerciseDescription') ? Session::get('exerciseDescription') : $objExercise->selectDescription();
    $exerciseEndMessage = Session::has('exerciseEndMessage') ? Session::get('exerciseEndMessage') : $objExercise->getEndMessage();
    $exerciseFeedback = $objExercise->getFeedback();
    $exerciseType = Session::has('exerciseType') ? Session::get('exerciseType') : $objExercise->selectType();
    $exerciseRange = Session::has('exerciseRange') ? Session::get('exerciseRange') : $objExercise->selectRange();
    $exerciseCalcGradeMethod = $objExercise->getCalcGradeMethod();
    $exerciseStartDate = $objExercise->selectStartDate();
    if (is_null($exerciseStartDate) && !Session::has('exerciseStartDate')) {
        $exerciseStartDate = '';
    } else {
        $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $exerciseStartDate);
        $exerciseStartDate = Session::has('exerciseStartDate') ? Session::get('exerciseStartDate') : $startDateTime->format('d-m-Y H:i');
    }
    $exerciseEndDate = $objExercise->selectEndDate();
    if (is_null($exerciseEndDate) && !Session::has('exerciseEndDate')) {
        $exerciseEndDate = '';
    } else {
        $endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $exerciseEndDate);
        $exerciseEndDate = Session::has('exerciseEndDate') ? Session::get('exerciseEndDate') : $endDateTime->format('d-m-Y H:i');
    }
    $enableStartDate = Session::has('enableStartDate') ? Session::get('enableStartDate') : ($exerciseStartDate ? 1 : 0);;
    $enableEndDate = Session::has('enableEndDate') ? Session::get('enableEndDate') : ($exerciseEndDate ? 1 : 0);
    $exerciseTempSave = Session::has('exerciseTempSave') ? Session::get('exerciseTempSave') : $objExercise->selectTempSave();
    $exerciseTimeConstraint = Session::has('exerciseTimeConstraint') ? Session::get('exerciseTimeConstraint') : $objExercise->selectTimeConstraint();
    $exerciseAttemptsAllowed = Session::has('exerciseAttemptsAllowed') ? Session::get('exerciseAttemptsAllowed') : $objExercise->selectAttemptsAllowed();
    $displayResults = Session::has('dispresults') ? Session::get('dispresults') : $objExercise->selectResults();
    $displayScore = Session::has('dispscore') ? Session::get('dispscore') : $objExercise->selectScore();
    $continueTimeLimit = Session::has('continueTimeLimit') ? Session::get('continueTimeLimit') : $objExercise->continueTimeLimit();
    $isExam = Session::has('isExam') ? Session::get('isExam') : $objExercise->isExam();
    $exerciseGradePass = $objExercise->getPassingGrade();
    $hasShuffleAnswers = Session::has('shuffle_answers') ? Session::get('shuffle_answers') : $objExercise->getOption(('ShuffleAnswers'));
    $continueTimeField = str_replace('[]',
        "<input type='text' class='form-control' name='continueTimeLimit' value='$continueTimeLimit' aria-label='$langminutes'>",
        $langContinueAttemptTime);
    if (!is_null($objExercise->selectIPLock())) {
        $exerciseIPLock = Session::has('exerciseIPLock') ? Session::get('exerciseIPLock') : explode(',', $objExercise->selectIPLock());
        $exerciseIPLockOptions = implode('', array_map(
            function ($item) {
                return $item ? ('<option selected>' . q(trim($item)) . '</option>') : '';
            }, $exerciseIPLock));
    } else {
        $exerciseIPLockOptions = '';
    }
    $exercisePreventCopy = Session::has('jsPreventCopy') ? Session::get('jsPreventCopy') : $objExercise->getOption('jsPreventCopy');
    $exerciseStricterExamRestriction = Session::has('stricterExamRestriction') ? Session::get('stricterExamRestriction') : $objExercise->getOption('stricterExamRestriction');
    $exercisePasswordLock = Session::has('exercisePasswordLock') ? Session::get('exercisePasswordLock') : $objExercise->selectPasswordLock();
    $exerciseAssignToSpecific = Session::has('assign_to_specific') ? Session::get('assign_to_specific') : $objExercise->selectAssignToSpecific();
    $assignee_options = $unassigned_options = '';
    if ($objExercise->selectAssignToSpecific()) {
        //preparing options in select boxes for assigning to specific users/groups
        if ($objExercise->selectAssignToSpecific() == 2) {
            $assignees = Database::get()->queryArray("SELECT `group`.id AS id, `group`.name
                FROM exercise_to_specific, `group`
                WHERE `group`.id = exercise_to_specific.group_id
                    AND exercise_to_specific.exercise_id = ?d", $exerciseId);
            $all_groups = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d AND visible = 1", $course_id);
            foreach ($assignees as $assignee_row) {
                $assignee_options .= "<option value='" . $assignee_row->id . "'>" . $assignee_row->name . "</option>";
            }
            $unassigned = array_udiff($all_groups, $assignees,
                function ($obj_a, $obj_b) {
                    return $obj_a->id - $obj_b->id;
                }
            );
            foreach ($unassigned as $unassigned_row) {
                $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->name</option>";
            }
        } else {
            $assignees = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                FROM exercise_to_specific, user
                WHERE user.id = exercise_to_specific.user_id AND exercise_to_specific.exercise_id = ?d", $exerciseId);
            $all_users = Database::get()->queryArray("SELECT user.id AS id, user.givenname, user.surname
                FROM user, course_user
                WHERE user.id = course_user.user_id
                AND course_user.course_id = ?d
                AND course_user.status = " . USER_STUDENT . "
                AND user.id", $course_id);
            foreach ($assignees as $assignee_row) {
                $assignee_options .= "<option value='$assignee_row->id'>$assignee_row->surname $assignee_row->givenname</option>";
            }
            $unassigned = array_udiff($all_users, $assignees,
                function ($obj_a, $obj_b) {
                    return $obj_a->id - $obj_b->id;
                }
            );
            foreach ($unassigned as $unassigned_row) {
                $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->surname $unassigned_row->givenname</option>";
            }
        }
    }

    $data['exerciseTitle'] = $exerciseTitle;
    $data['exerciseDescription'] = $exerciseDescription;
    $data['exerciseEndMessage'] = $exerciseEndMessage;
    $data['exerciseFeedback'] = $exerciseFeedback;
    $data['exerciseType'] = $exerciseType;
    $data['exerciseRange'] = $exerciseRange;
    $data['hasShuffleAnswers'] = $hasShuffleAnswers;
    $data['exerciseGradePass'] = $exerciseGradePass;
    $data['exerciseCalcGradeMethod'] = $exerciseCalcGradeMethod;
    $data['exerciseStartDate'] = $exerciseStartDate;
    $data['enableStartDate'] = $enableStartDate;
    $data['exerciseEndDate'] = $exerciseEndDate;
    $data['enableEndDate'] = $enableEndDate;
    $data['exerciseTimeConstraint'] = $exerciseTimeConstraint;
    $data['exerciseAttemptsAllowed'] = $exerciseAttemptsAllowed;
    $data['exerciseTempSave'] = $exerciseTempSave;
    $data['displayResults'] = $displayResults;
    $data['displayScore'] = $displayScore;
    $data['exerciseAssignToSpecific'] = $exerciseAssignToSpecific;
    $data['unassigned_options'] = $unassigned_options;
    $data['assignee_options'] = $assignee_options;
    $data['exerciseStricterExamRestriction'] = $exerciseStricterExamRestriction;
    $data['isExam'] = $isExam;
    $data['continueTimeLimit'] = $continueTimeLimit;
    $data['continueTimeField'] = $continueTimeField;
    $data['exercisePreventCopy'] = $exercisePreventCopy;
    $data['exerciseIPLockOptions'] = $exerciseIPLockOptions;
    $data['exercisePasswordLock'] = $exercisePasswordLock;
    $data['tags_list'] = eClassTag::tagInput($exerciseId);

    if (isset($_GET['modifyExercise'])) {
        $form_string = "&exerciseId=$exerciseId&modifyExercise=yes";
    } else {
        $form_string = "&NewExercise=Yes";
    }
    $data['form_string'] = $form_string;
    $data['form_buttons'] = form_buttons([
        ['text' => $langSave,
            'class' => 'submitAdminBtn',
            'name' => 'submitExercise',
            'value' => $langSubmit,
            'javascript' => "selectAll('assignee_box',true)"
        ],
        ['href' => $exerciseId ?
            "admin.php?course=$course_code&exerciseId=$exerciseId" :
            "index.php?course=$course_code",
            'class' => 'cancelAdminBtn ms-1',
        ]
    ]);

    rich_text_editor(null, null, null, null);

    view('modules.exercise.exercise_admin', $data);
    exit;
}

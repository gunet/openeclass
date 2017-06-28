<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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


require_once 'exercise.class.php';
require_once 'userRecord.class.php';

$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();


if (isset($_GET['exerciseId'])) {
    $exerciseIdIndirect = $_GET['exerciseId'];   
    $exerciseId = getDirectReference($exerciseIdIndirect);
    $objExercise = new Exercise();
    if (!$objExercise->read($exerciseId) && !$is_editor) {
        Session::Messages($langExerciseNotFound);
        redirect_to_home_page("modules/exercise/index.php?course=$course_code");
    }
    if (!$objExercise->selectScore() && !$is_editor) {
        redirect_to_home_page("modules/exercise/index.php?course=$course_code");
    }    
}
if ($is_editor && isset($_GET['purgeAttempID'])) {
    $eurid = $_GET['purgeAttempID'];
    $objExercise->purgeAttempt($exerciseIdIndirect, $eurid);
    Session::Messages($langPurgeExerciseResultsSuccess);
    redirect_to_home_page("modules/exercise/results.php?course=$course_code&exerciseId=" . getIndirectReference($_GET['exerciseId']));
}

$pageName = $langResults;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langExercices);

$head_content .= "
    <script type='text/javascript'>
            $(function(){
              // bind change event to select
              $('#status_filtering').bind('change', function () {
                  var url = $(this).val(); // get selected value
                  if (url) { // require a URL
                      window.location = url; // redirect
                  }
                  return false;
              });
            });
    </script>";

$data['status'] = isset($_GET['status']) ? intval($_GET['status']) : '';
$extra_sql = $data['status'] !== ''  ? ' AND attempt_status = '.$data['status'] : '';
$user_attempts = [];
if ($is_editor) {
    $data['students'] = Database::get()->queryArray("SELECT DISTINCT record.uid id, user.surname surname, user.givenname givenname, user.am am "
            . "FROM `exercise_user_record` record, `user` user "
            . "WHERE user.id = record.uid AND eid in "
            . "(SELECT id FROM exercise WHERE course_id = ?d)", $course_id);
} else {
    $data['students'] = Database::get()->queryArray("SELECT id, surname, givenname, am FROM user WHERE id = ?d", $uid);
}
foreach ($data['students'] as $student) {
     $data['user_attempts'][$student->id] = [];
    $user_user_records = Database::get()->queryArray("SELECT eurid
                FROM `exercise_user_record` 
                WHERE uid = ?d AND eid = ?d$extra_sql ORDER BY record_start_date DESC", 
            $student->id, $exerciseId);
    foreach ($user_user_records as $user_user_record) {
        $user_record = new UserRecord();
        $user_record->find($user_user_record->eurid);
        $data['user_attempts'][$student->id][] = $user_record;
    }    
}

$data['exercise'] = $objExercise;

$data['cur_date_time'] = new DateTime('NOW');

$displayScore = $objExercise->selectScore();
$data['showScore'] = $displayScore == 1
            || $is_editor
            || $displayScore == 3 && $exerciseAttemptsAllowed == $userAttempts
            || $displayScore == 4 && $end_date < $cur_date;

view('modules.exercise.results', $data);
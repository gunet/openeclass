<?php
require_once 'modules/analytics/ExerciseAnalyticsEvent.php';

function triggerExerciseAnalytics($courseId, $uid, $exerciseId) {
    $data = new stdClass();
    $data->course_id = $courseId;
    $data->uid = $uid;
    $data->element_type = 30;
    $data->resource = $exerciseId;

    ExerciseAnalyticsEvent::trigger(ExerciseAnalyticsEvent::EXERCISEGRADE, $data, true);
}
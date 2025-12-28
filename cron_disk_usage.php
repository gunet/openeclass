<?php

require_once 'include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';

$courses = Database::get()->queryArray("SELECT id, code FROM course");
$upsert_commands = [];

foreach ($courses as $course) {
    $course_code = $course->code;
    $courses_path = $webDir . "/courses/$course_code";
    $videos_path = $webDir . "/video/$course_code";
    $total_course_size = dir_total_space($courses_path) + dir_total_space($videos_path);
    $upsert_commands[] = "($course->id, $total_course_size)";
}

$query = "INSERT INTO course_resource_usage (course_id, disk_size) 
    VALUES " . implode(', ', $upsert_commands) . "
    ON DUPLICATE KEY UPDATE 
        disk_size = VALUES(disk_size);";

Database::get()->query($query);
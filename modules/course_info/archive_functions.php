<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

/**
 * Archive serialized course tables
 *
 * @param int $course_id
 * @param string $course_code
 * @param string $archivedir  Target directory
 */
function archiveTables($course_id, $course_code, $archivedir) {
    // backup subsystems from main db
    $sql_course = "course_id = $course_id";
    $archive_conditions = array(
        'course' => "id = $course_id",
        'user' => "id IN (SELECT user_id FROM course_user
                                          WHERE course_id = $course_id)",
        'course_user' => "course_id = $course_id",
        'course_settings' => "course_id = $course_id",
        'course_department' => "course = $course_id",
        'course_module' => $sql_course,
        'hierarchy' => "id IN (SELECT department FROM course_department
                                          WHERE course = $course_id)",
        'announcement' => $sql_course,
        'group_properties' => $sql_course,
        'group_category' => $sql_course,
        'group' => $sql_course,
        'group_members' => "group_id IN (SELECT id FROM `group`
                                                    WHERE course_id = $course_id)",
        'document' => $sql_course,
        'link_category' => $sql_course,
        'link' => $sql_course,
        'ebook' => $sql_course,
        'ebook_section' => "ebook_id IN (SELECT id FROM ebook
                                                    WHERE course_id = $course_id)",
        'ebook_subsection' => "section_id IN (SELECT ebook_section.id
                                                         FROM ebook, ebook_section
                                                         WHERE ebook.id = ebook_id AND
                                                               course_id = $course_id)",
        'course_units' => $sql_course,
        'unit_resources' => "unit_id IN (SELECT id FROM course_units
                                                    WHERE course_id = $course_id)",
        'forum' => $sql_course,
        'forum_category' => $sql_course,
        'forum_topic' => "forum_id IN (SELECT id FROM forum
                                                  WHERE course_id = $course_id)",
        'forum_post' => "topic_id IN (SELECT forum_topic.id
                                                 FROM forum, forum_topic
                                                 WHERE forum.id = forum_id AND
                                                       course_id = $course_id)",
        'forum_notify' => $sql_course,
        'forum_user_stats' => $sql_course,
        'course_description' => $sql_course,
        'glossary' => $sql_course,
        'glossary_category' => $sql_course,
        'video_category' => $sql_course,
        'video' => $sql_course,
        'videolink' => $sql_course,
        'dropbox_msg' => $sql_course,
        'dropbox_attachment' => "msg_id IN (SELECT id from dropbox_msg WHERE course_id = $course_id)",
        'dropbox_index' => "msg_id IN (SELECT id from dropbox_msg WHERE course_id = $course_id)",
        'lp_learnPath' => $sql_course,
        'lp_module' => $sql_course,
        'lp_asset' => "module_id IN (SELECT module_id FROM lp_module WHERE course_id = $course_id)",
        'lp_rel_learnPath_module' => "learnPath_id IN (SELECT learnPath_id FROM lp_learnPath WHERE course_id = $course_id)",
        'lp_user_module_progress' => "learnPath_id IN (SELECT learnPath_id FROM lp_learnPath WHERE course_id = $course_id)",
        'wiki_properties' => $sql_course,
        'wiki_acls' => "wiki_id IN (SELECT id FROM wiki_properties WHERE course_id = $course_id)",
        'wiki_pages' => "wiki_id IN (SELECT id FROM wiki_properties WHERE course_id = $course_id)",
        'wiki_pages_content' => "pid IN (SELECT id FROM wiki_pages
                                                    WHERE wiki_id IN (SELECT id FROM wiki_properties
                                                                             WHERE course_id = $course_id))",
        'poll' => $sql_course,
        'poll_question' => "pid IN (SELECT pid FROM poll WHERE course_id = $course_id)",
        'poll_to_specific' => "poll_id IN (SELECT pid FROM poll WHERE course_id = $course_id)",
        'poll_answer_record' => "poll_user_record_id IN (SELECT id FROM poll_user_record WHERE pid IN (SELECT pid FROM poll WHERE course_id = $course_id))",
        'poll_user_record' => "pid IN (SELECT pid FROM poll WHERE course_id = $course_id)",
        'poll_question_answer' => "pqid IN (SELECT pqid FROM poll_question
                                                       WHERE pid IN (SELECT pid FROM poll
                                                                            WHERE course_id = $course_id))",
        'assignment' => $sql_course,
        'assignment_to_specific' => "assignment_id IN (SELECT id FROM assignment WHERE course_id = $course_id)",
        'assignment_submit' => "assignment_id IN (SELECT id FROM assignment
                                                             WHERE course_id = $course_id)",
        'rubric' => $sql_course,
        'grading_scale' => $sql_course,
        'gradebook' => $sql_course,
        'gradebook_activities' => "gradebook_id IN (SELECT id FROM gradebook
                                                             WHERE course_id = $course_id)",
        'gradebook_book' => "gradebook_activity_id IN (SELECT gradebook_activities.id FROM gradebook_activities, gradebook
                                                             WHERE gradebook.course_id = $course_id AND gradebook_activities.gradebook_id = gradebook.id)",
        'gradebook_users' => "gradebook_id IN (SELECT id FROM gradebook WHERE course_id = $course_id)",
        'attendance' => $sql_course,
        'attendance_activities' => "attendance_id IN (SELECT id FROM attendance
                                                             WHERE course_id = $course_id)",
        'attendance_book' => "attendance_activity_id IN (SELECT attendance_activities.id FROM attendance_activities, attendance
                                                             WHERE attendance.course_id = $course_id AND attendance_activities.attendance_id = attendance.id)",
        'attendance_users' => "attendance_id IN (SELECT id FROM attendance WHERE course_id = $course_id)",
        'agenda' => $sql_course,
        'exercise' => $sql_course,
        'exercise_to_specific' => "exercise_id IN (SELECT id FROM exercise WHERE course_id = $course_id)",
        'exercise_question' => $sql_course,
        'exercise_answer' => "question_id IN (SELECT id FROM exercise_question
                                                         WHERE course_id = $course_id)",
        'exercise_user_record' => "eid IN (SELECT id FROM exercise WHERE course_id = $course_id)",
        'exercise_with_questions' => "question_id IN (SELECT id FROM exercise_question
                                                                 WHERE course_id = $course_id) OR
                                          exercise_id IN (SELECT id FROM exercise
                                                                 WHERE course_id = $course_id)",
        'exercise_question_cats' => $sql_course,
        'exercise_answer_record' => "question_id IN (SELECT id FROM exercise_question
                                                                WHERE course_id = $course_id)",
        'tc_session' => "course_id IN (SELECT id FROM tc_session WHERE course_id = $course_id)",
        'blog_post' => $sql_course,
        'comments' => "(rtype = 'blogpost' AND rid IN (SELECT id FROM blog_post WHERE course_id = $course_id)) OR (rtype = 'course' AND rid = $course_id) OR
            (rtype = 'wallpost' AND rid IN (SELECT id FROM wall_post WHERE course_id = $course_id))",
        'rating' => "(rtype = 'blogpost' AND rid IN (SELECT id FROM blog_post WHERE course_id = $course_id)) OR (rtype = 'course' AND rid = $course_id) OR
            (rtype = 'forum_post' AND rid IN (SELECT forum_post.id FROM forum_post INNER JOIN forum_topic on forum_post.topic_id = forum_topic.id INNER JOIN forum on forum_topic.forum_id = forum.id
            WHERE forum.course_id = $course_id)) OR (rtype = 'link' AND rid IN (SELECT id FROM link WHERE course_id = $course_id)) OR (rtype = 'wallpost' AND rid IN (SELECT id FROM wall_post WHERE course_id = $course_id))",
        'rating_cache' => "(rtype = 'blogpost' AND rid IN (SELECT id FROM blog_post WHERE course_id = $course_id)) OR (rtype = 'course' AND rid = $course_id) OR
            (rtype = 'forum_post' AND rid IN (SELECT forum_post.id FROM forum_post INNER JOIN forum_topic on forum_post.topic_id = forum_topic.id INNER JOIN forum on forum_topic.forum_id = forum.id
            WHERE forum.course_id = $course_id)) OR (rtype = 'link' AND rid IN (SELECT id FROM link WHERE course_id = $course_id)) OR (rtype = 'wallpost' AND rid IN (SELECT id FROM wall_post WHERE course_id = $course_id))",
        'abuse_report' => $sql_course,
        'wall_post' => $sql_course,
        'wall_post_resources' => "post_id IN (SELECT id FROM wall_post WHERE course_id = $course_id)",
        'note' => "(reference_obj_course IS NOT NULL AND reference_obj_course = $course_id)",
        'activity_heading' => 'true',
        'activity_content' => $sql_course,
        'certificate' => $sql_course,
        'certificate_criterion' => "certificate IN (SELECT id FROM certificate WHERE course_id = $course_id)",
        'certificate_template' => 'true',
        'badge' => $sql_course,
        'badge_criterion' => "badge IN (SELECT id FROM badge WHERE course_id = $course_id)",
        'badge_icon' => 'true',
        'category' => 'true',
        'category_value' => 'true',
        'course_category' => $sql_course,
        'h5p_content' => $sql_course,
        'h5p_content_dependency' => "content_id IN (SELECT id FROM h5p_content WHERE course_id = $course_id)"
        );

    foreach ($archive_conditions as $table => $condition) {
        backup_table($archivedir, $table, $condition);
    }

    file_put_contents("$archivedir/config_vars",
        serialize(array(
            'urlServer' => $GLOBALS['urlServer'],
            'urlAppend' => $GLOBALS['urlAppend'],
            'siteName' => $GLOBALS['siteName'],
            'version' => get_config('version'))));
}


/**
 * Do the main task of archiving a course.
 *
 * @param int $course_id
 * @param string $course_code
 */
function doArchive($course_id, $course_code) {
    global $webDir, $tool_content, $langGeneralError;

    // Remove previous back-ups older than 10 minutes
    touch("$webDir/courses/archive/index.html");
    cleanup("$webDir/courses/archive", 600);

    $basedir = "$webDir/courses/archive/$_SESSION[csrf_token]/$course_code";
    file_exists($basedir) or make_dir($basedir);

    $backup_date = date('Ymd-His');
    $backup_date_short = date('Ymd');

    $archivedir = $basedir . '/' . $backup_date;
    file_exists($archivedir) or make_dir($archivedir);

    archiveTables($course_id, $course_code, $archivedir);

    $zipfile = "$webDir/courses/archive/$_SESSION[csrf_token]/$course_code-$backup_date_short.zip";
    if (file_exists($zipfile)) {
        unlink($zipfile);
    }

    // create zip file
    $zip = new ZipArchive;
    if ($zip->open($zipfile, ZipArchive::CREATE) !== true) {
        Session::Messages($langGeneralError, 'alert-danger');
        redirect_to_home_page('modules/course_info/?course=' . $course_code);
    }
    $result = $zip->addGlob($archivedir . '/*', GLOB_NOSORT, [
            'remove_path' => "$webDir/courses/archive/$_SESSION[csrf_token]" ]) &&
        addDir($zip, "$webDir/courses/$course_code", "$course_code/$backup_date/html") &&
        addDir($zip, "$webDir/video/$course_code", "$course_code/$backup_date/video_files");
    $zip->close();
    removeDir($basedir);

    if (!$result) {
        Session::Messages($langGeneralError, 'alert-danger');
        redirect_to_home_page('modules/course_info/index.php?course=' . $course_code);
    }

    return $zipfile;
}

function addDir($zip, $path, $newPath) {
    $result = true;
    $dir = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $name => $item) {
        $basename = str_replace($path, $newPath, $name);
        if ($item->isFile()) {
            $result = $result && $zip->addFile($name, $basename);
        } else {
            $result = $result && $zip->addEmptyDir($basename);
        }
    }
    return $result;
}

/**
 * @brief back up a table
 * @param string $basedir
 * @param string $table
 * @param string $condition
 */
function backup_table($basedir, $table, $condition) {
    $q = Database::get()->queryArray("SELECT * FROM `$table` WHERE $condition");
    $backup = array();
    foreach ($q as $data) {
        $backup[] = (array) $data;
    }
    file_put_contents("$basedir/$table", serialize($backup));
}

/**
 * Delete everything in $basedir older than $age seconds.
 *
 * @param string $basedir
 * @param string $age
 */
function cleanup($basedir, $age) {
    if (($handle = opendir($basedir))) {
        while (($file = readdir($handle)) !== false) {
            $entry = "$basedir/$file";
            if ($file != '.' && $file != '..' && (time() - filemtime($entry) > $age)) {
                if (is_dir($entry)) {
                    removeDir($entry);
                } else {
                    unlink($entry);
                }
            }
        }
    }
}

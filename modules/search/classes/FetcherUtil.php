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

class FetcherUtil {

    public static function fetchCourse(int $courseId): ?object {
        $course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $courseId);
        if (!$course) {
            return null;
        }

        $course->units = $course->description;
        if ($course->view_type == 'activity') {
            $res = Database::get()->queryArray("SELECT content
                                                FROM activity_content
                                               WHERE course_id = ?d", $courseId);
            foreach ($res as $row) {
                $course->units .= $row->content . ' ';
            }
        } elseif (in_array($course->view_type, ['units', 'weekly'])) {
            if ($course->view_type == 'units') {
                $dbtable = 'course_units';
                $resdbtable = 'unit_resources';
                $keyfield = 'unit_id';
            } else {
                $dbtable = 'course_weekly_view';
                $resdbtable = 'course_weekly_view_activities';
                $keyfield = 'course_weekly_view_id';
            }
            // visible units
            $res = Database::get()->queryArray("SELECT id, title, comments
                                                FROM $dbtable
                                               WHERE visible > 0
                                                 AND course_id = ?d", $courseId);
            $unitIds = array();
            foreach ($res as $row) {
                $course->units .= $row->title . ' ' . $row->comments . ' ';
                $unitIds[] = $row->id;
            }

            // visible unit resources
            foreach ($unitIds as $unitId) {
                $res = Database::get()->queryArray("SELECT title, comments
                                                    FROM $resdbtable
                                                   WHERE visible > 0
                                                     AND $keyfield = ?d", $unitId);
                foreach ($res as $row) {
                    $course->units .= $row->title . ' ' . $row->comments . ' ';
                }
            }
        }

        // invisible but useful units and resources
        $res = Database::get()->queryArray("SELECT id
                                            FROM course_units
                                           WHERE visible = 0
                                             AND `order` = -1
                                             AND course_id  = ?d", $courseId);
        $unitIds = array();
        foreach ($res as $row) {
            $unitIds[] = $row->id;
        }
        foreach ($unitIds as $unitId) {
            $res = Database::get()->queryArray("SELECT comments
                                                FROM unit_resources
                                               WHERE visible >= 0
                                                 AND unit_id = ?d", $unitId);
            foreach ($res as $row) {
                $course->units .= $row->comments . ' ';
            }
        }
        return $course;
    }

    public static function fetchAnnouncements(int $courseId): array {
        return Database::get()->queryArray("SELECT * FROM announcement WHERE course_id = ?d", $courseId);
    }

    public static function fetchAnnouncement(int $announceId): ?object {
        $announce = Database::get()->querySingle("SELECT * FROM announcement WHERE id = ?d", $announceId);
        if (!$announce) {
            return null;
        }
        return $announce;
    }

    public static function fetchAgendas(int $courseId): array {
        return Database::get()->queryArray("SELECT * FROM agenda WHERE course_id = ?d", $courseId);
    }

    public static function fetchLinks(int $courseId): array {
        return Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d", $courseId);
    }

    public static function fetchLink(int $linkId): ?object {
        $link = Database::get()->querySingle("SELECT * FROM link WHERE id = ?d", $linkId);
        if (!$link) {
            return null;
        }
        return $link;
    }

    public static function fetchVideos(int $courseId): array {
        return Database::get()->queryArray("SELECT * FROM video WHERE course_id = ?d", $courseId);
    }

    public static function fetchVideo(int $videoId): ?object {
        $video = Database::get()->querySingle("SELECT * FROM video WHERE id = ?d", $videoId);
        if (!$video) {
            return null;
        }
        return $video;
    }

    public static function fetchVideoLinks(int $courseId): array {
        return Database::get()->queryArray("SELECT * FROM videolink WHERE course_id = ?d", $courseId);
    }

    public static function fetchVideoLink(int $vlinkId): ?object {
        $vlink = Database::get()->querySingle("SELECT * FROM videolink WHERE id = ?d", $vlinkId);
        if (!$vlink) {
            return null;
        }
        return $vlink;
    }

    public static function fetchExercises(int $courseId): array {
        return Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d", $courseId);
    }

    public static function fetchExercise(int $exerciseId): ?object {
        $exercise = Database::get()->querySingle("SELECT * FROM exercise WHERE id = ?d", $exerciseId);
        if (!$exercise) {
            return null;
        }
        return $exercise;
    }

    public static function fetchForums(int $courseId): array {
        return Database::get()->queryArray("SELECT f.* 
            FROM forum f 
            JOIN forum_category fc ON f.cat_id = fc.id 
            WHERE fc.cat_order >= 0
            AND f.course_id = ?d", $courseId);
    }

    public static function fetchForumTopics(int $courseId): array {
        return Database::get()->queryArray("SELECT ft.*, f.course_id 
            FROM forum_topic ft 
            JOIN forum f ON ft.forum_id = f.id 
            JOIN forum_category fc ON fc.id = f.cat_id 
            WHERE fc.cat_order >= 0 AND f.course_id = ?d", $courseId);
    }

    public static function fetchForumPosts(int $courseId): array {
        return Database::get()->queryArray("SELECT fp.*, f.course_id, ft.forum_id, ft.title 
            FROM forum_post fp 
            JOIN forum_topic ft ON fp.topic_id = ft.id 
            JOIN forum f ON ft.forum_id = f.id 
            JOIN forum_category fc ON fc.id = f.cat_id 
            WHERE fc.cat_order >= 0 AND f.course_id = ?d", $courseId);
    }

    public static function fetchDocuments(int $courseId): array {
        return Database::get()->queryArray("SELECT * 
            FROM document 
            WHERE course_id >= 1 
            AND subsystem = 0 
            AND format <> \".meta\" 
            AND course_id = ?d", $courseId);
    }

    public static function fetchDocument(int $docId): ?object {
        // exclude non-main subsystems and metadata
        $doc = Database::get()->querySingle("SELECT * FROM document WHERE id = ?d AND course_id >= 1 AND subsystem = 0 AND format <> \".meta\"", $docId);
        if (!$doc) {
            return null;
        }
        return $doc;
    }

    public static function fetchUnits(int $courseId): array {
        return Database::get()->queryArray("SELECT * FROM course_units WHERE course_id = ?d", $courseId);
    }

    public static function fetchUnitResources(int $courseId): array {
        return Database::get()->queryArray("SELECT ur.*, cu.course_id 
            FROM unit_resources ur 
            JOIN course_units cu ON cu.id = ur.unit_id AND cu.course_id = ?d", $courseId);
    }

    public static function fetchNotes(int $courseId): array {
        return Database::get()->queryArray("SELECT * FROM note WHERE reference_obj_course = ?d", $courseId);
    }

    public static function fetchNote(int $noteId): ?object {
        $note = Database::get()->querySingle("SELECT * FROM note WHERE id = ?d", $noteId);
        if (!$note) {
            return null;
        }
        if (!is_null($note->reference_obj_course)) {
            $note->course_id = intval($note->reference_obj_course);
        }
        return $note;
    }
}

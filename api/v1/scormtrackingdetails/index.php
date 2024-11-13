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

function get_scorm_details($path) {
    $path .= "/imsmanifest.xml";
    if (!($xml = simplexml_load_file($path))) {
        return null;
    }
    return $xml->organizations->organization->item;
}

function api_method($access) {
    global $course_id;

    $course = null;
    $scorms = [];
    $users = [];
    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }
    if (isset($_GET['user_id'])) {
        $users = [$_GET['user_id']];
    } elseif (isset($_GET['group_id'])) {
        $group = Database::get()->querySingle('SELECT * FROM `group` WHERE id = ?d', $_GET['group_id']);
        if (!$group) {
            Access::error(3, "Group with id '$_GET[group_id]' not found");
        }
        $course = Database::get()->querySingle('SELECT id, code, visible FROM course
            WHERE id = ?d', $group->course_id);
        $group_members = Database::get()->queryArray('SELECT user_id FROM group_members
            WHERE group_id = ?d AND is_tutor = 0', $_GET['group_id']);
        if (!$group_members) {
            Access::error(3, "No members found for group with id '$_GET[group_id]'");
        }
        $users = array_map(function ($member) {
            return $member->user_id;
        }, $group_members);
    }
    if (isset($_GET['course_id'])) {
        $course_id = $_GET['course_id'];
        $course = Database::get()->querySingle('SELECT id, code, visible FROM course
            WHERE code = ?s', $course_id);
        if (!$course) {
            Access::error(3, "Course with id '$course_id' not found");
        }
    }
    if (isset($_GET['scorm_id'])) {
        $lp = Database::get()->querySingle('SELECT learnPath_id, name, comment, course_id
            FROM lp_learnPath WHERE learnPath_id = ?d', $_GET['scorm_id']);
        if (!$lp) {
            Access::error(3, "SCORM with id '$_GET[scorm_id]' not found");
        }
        $course_code = course_id_to_code($lp->course_id);
        $path = "courses/$course_code/scormPackages/path_{$lp->learnPath_id}";
        $scorm_details = get_scorm_details($path);
        if (!$scorm_details) {
            Access::error(3, "Unable to read SCORM with id '$_GET[scorm_id]' in course '$course_code'");
        }
        $scorms = [[$lp->learnPath_id, $scorm_details['identifier']]];
    } elseif ($course) {
        $lps = Database::get()->queryArray("SELECT lp_learnPath.learnPath_id
            FROM lp_learnPath
                JOIN lp_rel_learnPath_module ON lp_learnPath.learnPath_id = lp_rel_learnPath_module.learnPath_id
                JOIN lp_module ON lp_module.module_id = lp_rel_learnPath_module.learnPath_module_id
            WHERE lp_learnPath.course_id = ?d AND lp_module.contentType = 'SCORM'",
            $course->id);
        $course_code = $course->code;
        foreach ($lps as $lp) {
            $path = "courses/$course_code/scormPackages/path_{$lp->learnPath_id}";
            $scorm_details = get_scorm_details($path);
            $scorms[] = [$lp->learnPath_id, $scorm_details['identifier']];
        }
    }
    if (!$course) {
       if (isset($course_code)) {
           $course = Database::get()->querySingle('SELECT id FROM course WHERE code = ?s', $course_code);
       } else {
           Access::error(2, 'Required parameter missing - group_id, course_id or scorm_id is required');
       }
    }
    if (!$users) {
        $course_users = Database::get()->queryArray('SELECT user_id FROM course_user
            WHERE course_id = ?d AND status = ?d AND editor = 0', $course->id, USER_STUDENT);
        $users = array_map(function ($user) {
            return $user->user_id;
        }, $course_users);
    }
    $course_id = $course->id;
    if (!isset($_GET['group_id'])) {
        $group_data = [];
        $group_members = Database::get()->queryArray('SELECT user_id, group_id
            FROM group_members, `group`
            WHERE group_id = `group`.id AND course_id = ?d', $course_id);
        foreach ($group_members as $item) {
            if (isset($group_data[$item->user_id])) {
                if (is_array($group_data[$item->user_id])) {
                    $group_data[$item->user_id][] = $item->group_id;
                } else {
                    $group_data[$item->user_id] = [$group_data[$item->user_id], $item->group_id];
                }
            } else {
                $group_data[$item->user_id] = $item->group_id;
            }
        }
    }
    $tracking_data = [];

    $from_date = null;
    if (isset($_GET['from_date'])) {
        $from_date = $_GET['from_date'] . ' 00:00:00';
    }

    foreach ($scorms as $scorm) {
        $path_id = $scorm[0];
        $sco_id = (string)$scorm[1];
        foreach ($users as $user_id) {
            $attempts = get_learnPath_progress_details($path_id, $user_id, false);
            foreach ($attempts as $attempt) {
                list($progress, $time, $started, $accessed, $status, $attemptNb) = $attempt;
                if ($from_date && $started < $from_date) {
                    continue;
                }
                $data = [
                    'userid' => $user_id,
                    'scormid' => $path_id,
                    'scoid' => $sco_id,
                    'starttime' => $started,
                    'endtime' => $accessed,
                    'duration' => $time,
                    'attempt' => $attemptNb,
                ];
                if (isset($_GET['group_id'])) {
                    $data['groupid'] = $_GET['group_id'];
                } elseif (isset($group_data[$user_id])) {
                    $data['groupid'] = $group_data[$user_id];
                }
                $tracking_data[] = $data;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($tracking_data, JSON_UNESCAPED_UNICODE);
    exit();
}

require_once '../../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'api/v1/apiCall.php';

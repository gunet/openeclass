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

function get_scorm_sco_id($course_code, $lp_id) {
    $path = "courses/$course_code/scormPackages/path_$lp_id";
    if (is_dir($path)) {
        $path .= "/imsmanifest.xml";
        if (!($xml = simplexml_load_file($path))) {
            return null;
        }
        return (string)$xml->organizations->organization->item['identifier'];
    } else {
        return null;
    }
}

function api_method($access) {
    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }
    if (isset($_GET['scorm_id'])) {
        $lp = Database::get()->querySingle('SELECT learnPath_id, name, comment, rank, course_id
            FROM lp_learnPath WHERE learnPath_id = ?d', $_GET['scorm_id']);
        if (!$lp) {
            Access::error(3, "SCORM with id '$_GET[scorm_id]' not found");
        }
        $lp_data = [
                'id' => $lp->learnPath_id,
                'name' => $lp->name,
                'summary' => $lp->comment,
                'order' => $lp->rank,
        ];
        $course_code = course_id_to_code($lp->course_id);
        $sco_id = get_scorm_sco_id($course_code, $lp->learnPath_id);
        if ($sco_id) {
            $lp_data['sco_id'] = $sco_id;
        }
        header('Content-Type: application/json');
        echo json_encode($lp_data, JSON_UNESCAPED_UNICODE);
        exit();
    }
    if (isset($_GET['course_id'])) {
        $course_id = $_GET['course_id'];
        $course = Database::get()->querySingle('SELECT id, code, visible FROM course
            WHERE code = ?s AND visible <> ?d',
            $course_id, COURSE_INACTIVE);
        if (!$course) {
            Access::error(3, "Course with id '$course_id' not found");
        }
    } else {
        $course = null;
    }
    $section = null;
    if (isset($_GET['section_id'])) {
        if ($course) {
            $section = Database::get()->querySingle('SELECT id FROM course_units
              WHERE course_id = ?d AND id = ?d',
              $course->id, $_GET['section_id']);
            if (!$section) {
                Access::error(3, "Section with id '$_GET[section_id]' not found for course '{$course->id}'");
            }
            $course_code = $course_code;
        } else {
            $section = Database::get()->querySingle('SELECT id, course_id FROM course_units WHERE id = ?d',
                $_GET['section_id']);
            if (!$section) {
                Access::error(3, "Section with id '$_GET[section_id]' not found");
            }
            $course_id = $section->course_id;
            $course_code = course_id_to_code($course_id);
        }
        $lps = Database::get()->queryArray("SELECT title, comments, res_id, `order`
            FROM unit_resources WHERE unit_id = ?d AND type = 'lp' ORDER BY `order`",
            $section->id);
        $lp_data = array_filter(array_map(function ($lp) use ($course_code) {
            $sco_id = get_scorm_sco_id($course_code, $lp->res_id);
            if ($sco_id) {
                return [
                    'id' => $lp->res_id,
                    'name' => $lp->title,
                    'summary' => $lp->comments,
                    'order' => $lp->order,
                    'sco_id' => $sco_id,
                ];
            } else {
                return null;
            }
        }, $lps));
    } else {
        if (!$course) {
            Access::error(2, 'Required parameter missing - scorm_id, course_id or section_id is required');
        }
        $lps = Database::get()->queryArray('SELECT learnPath_id, name, comment, rank
            FROM lp_learnPath WHERE course_id = ?d ORDER BY rank', $course->id);
        $course_code = $course->code;
        $lp_data = array_filter(array_map(function ($lp) use ($course_code) {
            $sco_id = get_scorm_sco_id($course_code, $lp->learnPath_id);
            if ($sco_id) {
                return [
                    'id' => $lp->learnPath_id,
                    'name' => $lp->name,
                    'summary' => $lp->comment,
                    'order' => $lp->rank,
                    'sco_id' => $sco_id,
                ];
            } else {
                return null;
            }
        }, $lps));
    }
    header('Content-Type: application/json');
    echo json_encode($lp_data, JSON_UNESCAPED_UNICODE);
    exit();
}

chdir('..');
require_once 'apiCall.php';

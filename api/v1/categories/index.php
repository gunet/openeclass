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



function api_method($access)
{

    if (isset($_GET['all'])) {

        // Build department filter if needed
        $departmentFilter = '';
        $queryParams = [];

        if ($access && $access->allowedDepartments !== null) {
            $placeholders = implode(',', array_fill(0, count($access->allowedDepartments), '?d'));
            $departmentFilter = "WHERE hierarchy.id IN ($placeholders)";
            $queryParams = $access->allowedDepartments;
        }

        $categories = Database::get()->queryArray("SELECT hierarchy.id, hierarchy.code, hierarchy.name, hierarchy.description,
                MIN(course.created) AS timemodified, 0 AS sortorder
            FROM hierarchy
                LEFT JOIN course_department ON hierarchy.id = course_department.department
                LEFT JOIN course ON course_department.course = course.id
            $departmentFilter
            GROUP BY hierarchy.id, hierarchy.code, hierarchy.name, hierarchy.description
            ORDER BY hierarchy.id", ...$queryParams);
        $categories = array_map(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'description' => getSerializedMessage($item->description),
                'name' => getSerializedMessage($item->name),
                'timemodified' => $item->timemodified,
                'sortorder' => $item->sortorder,
            ];
        }, $categories);


        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($categories, JSON_UNESCAPED_UNICODE);
        exit;
    }


    if (isset($_GET['id'])) {

        // Build department filter if needed
        $departmentFilter = '';
        $queryParams = [$_GET['id']];

        if ($access && $access->allowedDepartments !== null) {
            $placeholders = implode(',', array_fill(0, count($access->allowedDepartments), '?d'));
            $departmentFilter = "AND hierarchy.id IN ($placeholders)";
            $queryParams = array_merge($queryParams, $access->allowedDepartments);
        }

        $category = Database::get()->querySingle("SELECT hierarchy.id, hierarchy.name, hierarchy.description,
                MIN(course.created) AS timemodified, 0 AS sortorder
            FROM hierarchy
                LEFT JOIN course_department ON hierarchy.id = course_department.department
                LEFT JOIN course ON course_department.course = course.id
            WHERE hierarchy.id = ?d $departmentFilter
            GROUP BY hierarchy.id, hierarchy.name, hierarchy.description", ...$queryParams);
        if (!$category) {
            Access::error(3, "Category with id '$_GET[id]' not found");
        } else {
            $categories = [
                'id' => $category->id,
                'name' => getSerializedMessage($category->name),
                'description' => getSerializedMessage($category->description),
                'timemodified' => $category->timemodified,
                'sortorder' => $category->sortorder,
            ];
        }
    } else {
        if ($access and !$access->allCourses and $access->courseIDs) {
            // Filter by course IDs and optionally by allowed departments
            $whereClause = '';
            $queryParams = [];

            $coursePlaceholders = implode(',', array_fill(0, count($access->courseIDs), '?d'));
            $whereClause = "WHERE course.id IN ($coursePlaceholders)";
            $queryParams = $access->courseIDs;

            if ($access->allowedDepartments !== null) {
                $deptPlaceholders = implode(',', array_fill(0, count($access->allowedDepartments), '?d'));
                $whereClause .= " AND hierarchy.id IN ($deptPlaceholders)";
                $queryParams = array_merge($queryParams, $access->allowedDepartments);
            }

            $categories = Database::get()->queryArray("SELECT hierarchy.id, hierarchy.name, hierarchy.description,
                       MIN(course.created) AS timemodified, 0 AS sortorder
                    FROM hierarchy
                       JOIN course_department ON hierarchy.id = course_department.department
                       JOIN course ON course_department.course = course.id
                    $whereClause
                    GROUP BY hierarchy.id, hierarchy.name, hierarchy.description
                    ORDER BY name", ...$queryParams);
        } else {
            // All courses - optionally filter by allowed departments
            $whereClause = 'WHERE allow_course = 1';
            $queryParams = [];

            if ($access && $access->allowedDepartments !== null) {
                $placeholders = implode(',', array_fill(0, count($access->allowedDepartments), '?d'));
                $whereClause .= " AND hierarchy.id IN ($placeholders)";
                $queryParams = $access->allowedDepartments;
            }

            $categories = Database::get()->queryArray("SELECT hierarchy.id, hierarchy.name, hierarchy.description,
                    MIN(course.created) AS timemodified, 0 AS sortorder
                FROM hierarchy
                    LEFT JOIN course_department ON hierarchy.id = course_department.department
                    LEFT JOIN course ON course_department.course = course.id
                $whereClause
                GROUP BY hierarchy.id, hierarchy.name, hierarchy.description
                ORDER BY name", ...$queryParams);
        }
        $categories = array_map(function ($item) {
            return [
                'id' => $item->id,
                'name' => getSerializedMessage($item->name),
                'description' => getSerializedMessage($item->description),
                'timemodified' => $item->timemodified,
                'sortorder' => $item->sortorder,
            ];
        }, $categories);
    }
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($categories, JSON_UNESCAPED_UNICODE);
    exit();
}


chdir('..');
require_once 'apiCall.php';

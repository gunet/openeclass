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

function api_method($access) {

    if (isset($_GET['all'])) {

        $hierarchyQuery = Database::get()->queryArray("SELECT id, code, description, name FROM `hierarchy`");

//        $name = $hierarchyQuery[6]->name;
//        $unsName = unserialize($name);

        header('Content-Type: application/json');
        echo json_encode($hierarchyQuery, JSON_UNESCAPED_UNICODE);
        exit();


    }

    if (isset($_GET['id'])) {
        $category = Database::get()->querySingle('SELECT hierarchy.id, hierarchy.name, hierarchy.description,
                MIN(course.created) AS timemodified, 0 AS sortorder
            FROM hierarchy
                JOIN course_department ON hierarchy.id = course_department.department
                JOIN course ON course_department.course = course.id
            WHERE hierarchy.id = ?d', $_GET['id']);
        if (!$category) {
            Access::error(3, "Category with id '$_GET[id]' not found");
        } else {
            $categories = [
                'id' => $category->id,
                'name' => getSerializedMessage($category->name, 'el'),
                'description' => getSerializedMessage($category->description, 'el'),
                'timemodified' => $category->timemodified,
                'sortorder' => $category->sortorder,
            ];
        }
    } else {
        $categories = Database::get()->queryArray('SELECT hierarchy.id, hierarchy.name, hierarchy.description,
                MIN(course.created) AS timemodified, 0 AS sortorder
            FROM hierarchy
                JOIN course_department ON hierarchy.id = course_department.department
                JOIN course ON course_department.course = course.id
            WHERE allow_course = 1
            ORDER BY name');
        $categories = array_map(function ($item) {
            return [
                'id' => $item->id,
                'name' => getSerializedMessage($item->name, 'el'),
                'description' => getSerializedMessage($item->description, 'el'),
                'timemodified' => $item->timemodified,
                'sortorder' => $item->sortorder,
            ];
        }, $categories);
    }
    header('Content-Type: application/json');
    echo json_encode($categories, JSON_UNESCAPED_UNICODE);
    exit();
}

chdir('..');
require_once 'apiCall.php';

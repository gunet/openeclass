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

/**
 * @file listcours.php
 * @brief display list of courses
 */
$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $tree = new Hierarchy();
    $course = new Course();
    $user = new User();
    // A search has been submitted
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $searchurl = "&search=yes";
    $searchtitle = isset($_GET['formsearchtitle']) ? $_GET['formsearchtitle'] : '';
    $searchcode = isset($_GET['formsearchcode']) ? $_GET['formsearchcode'] : '';
    $searchtype = isset($_GET['formsearchtype']) ? intval($_GET['formsearchtype']) : '-1';
    $searchfaculte = isset($_GET['formsearchfaculte']) ? intval($_GET['formsearchfaculte']) : '';
    $searchprof = isset($_GET['formsearchprof']) ? $_GET['formsearchprof'] : '';
    // pagination
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    // Search for courses
    $query = '';
    $terms = array();
    if (!empty($searchtitle)) {
        $query .= ' AND title LIKE ?s';
        $terms[] = '%' . $searchtitle . '%';
    }
    if (!empty($searchcode)) {
        $query .= ' AND (course.code LIKE ?s OR public_code LIKE ?s)';
        $terms[] = '%' . $searchcode . '%';
        $terms[] = '%' . $searchcode . '%';
    }

    if ($searchtype != "-1") {
        if ($searchtype == '4') {
            $query .= ' AND course.visible < ?d';
            $terms[] = 3;
        } else {
            $query .= ' AND course.visible = ?d';
            $terms[] = $searchtype;
        }

    }
    if ($searchprof !== '') {
        $query .= ' AND course.prof_names LIKE ?s';
        $terms[] = '%' . $searchprof . '%';
    }
    if ($searchfaculte) {
        $subs = $tree->buildSubtrees(array($searchfaculte));
        $ids = 0;
        foreach ($subs as $key => $id) {
            $terms[] = $id;
            $ids++;
        }
        $query .= ' AND hierarchy.id IN (' . implode(', ', array_fill(0, $ids, '?d')) . ')';
    }
    if (isset($_GET['reg_flag']) and ! empty($_GET['date'])) {
        $query .= ' AND created ' . (($_GET['reg_flag'] == 1) ? '>=' : '<=') . ' ?s';
        $date_created_at = DateTime::createFromFormat("d-m-Y H:i", $_GET['date']);
        $terms[] = $date_created_at->format("Y-m-d H:i:s");
    }

    // Datatables internal search
    $filter_terms = array();
    if (!empty($_GET['sSearch'])) {
        $filter_query = ' AND (title LIKE ?s OR prof_names LIKE ?s)';
        $filter_terms[] = '%' . $_GET['sSearch'] . '%';
        $filter_terms[] = '%' . $_GET['sSearch'] . '%';
    } else {
        $filter_query = '';
    }

    // Limit department admin search only to subtrees of own departments
    if (isDepartmentAdmin()) {
        $begin = true;
        foreach ($user->getAdminDepartmentIds($uid) as $department) {
            if ($begin) {
                $query .= ' AND (';
                $begin = false;
            } else {
                $query .= ' OR ';
            }
            $nodeLftRgt = $tree->getNodeLftRgt($department);
            $query .= 'hierarchy.lft BETWEEN ' . $nodeLftRgt->lft . ' AND ' . $nodeLftRgt->rgt;
        }
        $query .= ')';
    }

    // sorting
    $extra_query = "ORDER BY course.title " .
            ($_GET['sSortDir_0'] == 'desc' ? 'DESC' : '');
    // pagination
    if ($limit > 0) {
        $extra_query .= " LIMIT ?d, ?d";
        $extra_terms = array($offset, $limit);
    } else {
        $extra_terms = array();
    }

    $query_collaboration = '';
    if(get_config('show_collaboration') && get_config('show_always_collaboration')){
        $query_collaboration = ' AND course.is_collaborative = 1';
    }


    $sql = Database::get()->queryArray("SELECT DISTINCT course.code, course.title, course.prof_names, course.visible, course.id, course.created, course.popular_course
                               FROM course, course_department, hierarchy
                              WHERE course.id = course_department.course
                                AND hierarchy.id = course_department.department
                                    $query $filter_query $query_collaboration $extra_query", $terms, $filter_terms, $extra_terms);

    $all_results = Database::get()->querySingle("SELECT COUNT(*) as total FROM course, course_department, hierarchy
                                                WHERE course.id = course_department.course
                                                AND hierarchy.id = course_department.department
                                                $query $query_collaboration", $terms)->total;
    $filtered_results = Database::get()->querySingle("SELECT COUNT(*) as total FROM course, course_department, hierarchy
                                                WHERE course.id = course_department.course
                                                AND hierarchy.id = course_department.department
                                                $query $filter_query $query_collaboration", $terms, $filter_terms)->total;

    $data['iTotalRecords'] = $all_results;
    $data['iTotalDisplayRecords'] = $filtered_results;

    $data['aaData'] = array();

    foreach ($sql as $logs) {
        $popular_icon = '';
        $popular_course_message = "$langPopular $langsCourse";
        $popular_course_action = "pop=1";
        if ($logs->popular_course) {
            $popular_icon = icon('fa-star');
            $popular_course_message = $langRemovePopular;
            $popular_course_action = "pop=0";
        }
        $course_title = "<a href='{$urlServer}courses/" . $logs->code . "/'>" . q($logs->title) . "
                        </a> (" . q($logs->code) . ") " . $popular_icon . "<br><i>" . q($logs->prof_names) .
                        "<br><span class='help-block'>$langCreatedIn: " . format_locale_date(strtotime($logs->created), null, false). "</span>";

        $departments = $course->getDepartmentIds($logs->id);
        $i = 1;
        $dep = '';
        foreach ($departments as $department) {
            $br = ($i < count($departments)) ? '<br/>' : '';
            $dep .= $tree->getFullPath($department) . $br;
            $i++;
        }
        // Add links to course users, delete course and course edit
        $icon_content = action_button(array(
            array(
                'title' => $langEditChange,
                'icon' => 'fa-edit',
                'url' => "editcours.php?c=$logs->code"
            ),
            array(
                'title' => $langUsers,
                'icon' => 'fa-user',
                'url' => "listusers.php?c=$logs->id"
            ),
            array(
                'title' => $langUsersLog,
                'icon' => 'fa-list',
                'url' => "../usage/displaylog.php?c=$logs->id&amp;from_admin=TRUE",
                'show' => !isDepartmentAdmin()
            ),
            array(
                'title' => $langDelete,
                'icon' => 'fa-xmark',
                'url' => "delcours.php?c=$logs->id"
            ),
            array(
                'title' => $popular_course_message,
                'icon' => 'fa-star',
                'url' => "$_SERVER[SCRIPT_NAME]?c=$logs->id&$popular_course_action"
            ),

        ));
        $data['aaData'][] = array(
            '0' => $course_title,
            '1' => course_access_icon($logs->visible),
            '2' => $dep,
            '3' => $icon_content
        );
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// change course popularity
if (isset($_GET['pop'])) {
    Database::get()->querySingle("UPDATE course SET popular_course = ?d WHERE id = ?d", $_GET['pop'], $_GET['c']);
    Session::flash('message', $langFaqEditSuccess);
    Session::flash('alert-class', 'alert-success');
}

load_js('tools.js');
load_js('datatables');

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourses);
$toolName = $langAdmin;
$pageName = $langListCours;

// Display Actions Toolbar
$data['action_bar'] = action_bar(array(
            array('title' => $langAllCourses,
                'url' => "$_SERVER[SCRIPT_NAME]?formsearchtitle=&amp;formsearchcode=&amp;formsearchtype=-1&amp;reg_flag=1&amp;date=&amp;formsearchfaculte=0&amp;search_submit=$langSearch",
                'icon' => 'fa-search',
                'level' => 'primary-label')
            ));

view('admin.courses.listcours', $data);

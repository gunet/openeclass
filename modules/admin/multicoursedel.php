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

$require_departmentmanage_user = true;
$require_help = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/admin/hierarchy_validations.php';
require_once 'modules/create_course/functions.php';

$tenant_courses = getTenantCourses();

function parseCourseCodes(string $input): array
{
    return explode(' ', canonicalize_whitespace(str_replace(["\n", "\r"], ' ', $input)));
}

function resolveCourse($code, $tenant, $tenantCourses = [])
{
    if (!$tenant) {
        return Database::get()->querySingle(
            'SELECT c.id, c.code, c.title
             FROM course c
             WHERE c.code = ?s',
            $code
        );
    }

    $matches = array_filter($tenantCourses, fn($course) => $course->code === $code);
    return reset($matches) ?: null;
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    checkSecondFactorChallenge();

    $tenant = getCurrentTenant();
    $courseCodes = parseCourseCodes($_POST['courses']);
    $tenantCourses = $tenant ? getTenantCourses() : [];

    $courseIds = [];
    $courseNames = [];
    $errorMessages = [];
    $successMessages = [];

    foreach ($courseCodes as $code) {
        $course = resolveCourse($code, $tenant, $tenantCourses);

        if (!$course) {
            $errorMessages[] = q($code) . ": " . $langCourseNotExist;
            continue;
        }

        $coursesToDelete[$course->id] = [
            'code' => $course->code,
            'title' => $course->title
        ];
    }

    if (!empty($errorMessages)) {
        Session::Messages(implode('<br>', $errorMessages), 'alert-danger');
        redirect_to_home_page('modules/admin/multicoursedel.php');
    }

    foreach ($coursesToDelete as $courseId => $details) {
        delete_course($courseId);
        $successMessages[] = $details['code'] . ": " . $langMultiCourseDeleted;
        Log::record(0, 0, LOG_DELETE_COURSE, [
            'id' => $courseId,
            'code' => $details['code'],
            'title' => $details['title'],
            'tenant' => $tenant ? $tenant->id : 0
        ]);
    }

    Session::Messages(implode('<br>', $successMessages), 'alert-success');
    redirect_to_home_page('modules/admin/multicoursedel.php');
}

$toolName = $langAdmin;
$pageName = $langMultiCourseDelete;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$data = [];

view('admin.courses.multicoursedel', $data);

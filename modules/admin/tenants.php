<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2025, Greek Universities Network - GUnet
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

$require_admin = true;
$require_help = true;
$helpTopic = 'tenants';

require_once '../../include/baseTheme.php';

load_js('datatables');

$toolName = $langAdmin;
$pageName = $langTenants;

$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];

$data['tenants'] = Database::get()->queryArray(
    "SELECT t.*, 
           h.lft, h.rgt,
           COUNT(DISTINCT ud.user) AS total_users,
           COUNT(DISTINCT cd.course) AS total_courses,
           COALESCE(SUM(cru.disk_size), 0) AS disk_usage
    FROM tenant t
    LEFT JOIN hierarchy h ON h.id = t.department_id
    LEFT JOIN hierarchy h2 ON h2.lft BETWEEN h.lft AND h.rgt
    LEFT JOIN user_department ud ON ud.department = h2.id
    LEFT JOIN course_department cd ON cd.department = h2.id
    LEFT JOIN course_resource_usage cru ON cru.course_id = cd.course
    GROUP BY t.id"
);

view('admin.other.tenants.index', $data);

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

$data['tenants'] = Database::get()->queryArray('SELECT * FROM tenant');

view('admin.other.tenants.index', $data);

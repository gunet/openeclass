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

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';
require_once 'include/lib/hierarchy.class.php';

$toolName = $langAdmin;
$pageName = $langMonthlyReport;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array("url" => "../usage/index.php?t=a", "name" => $langUsage);

$tree = new Hierarchy();

list($roots, $rootSubtrees) = $tree->buildRootsWithSubTreesArray();

$fc = ($_GET['fc'] ?? $roots[0]->id);
$fc_name = $tree->getNodeName($fc);

if (count($tree->buildRootsArray()) > 1) { // multiple root tree
    $data['buildRoots'] = $tree->buildRootsSelectForm(intval($roots[0]->id));
} else {
    $data['buildRoots'] = '';
}

if (isset($_GET['d'])) {  // detailed statistics per faculty
    $monthly_data = get_monthly_archives($fc, true, $_GET['m']);
} else {
    $monthly_data = get_monthly_archives($fc);
}

$data['monthly_data'] = $monthly_data;
$data['fc'] = $fc;
$data['fc_name'] = $fc_name;

view('admin.other.stats.monthlyReport', $data);


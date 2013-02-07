<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

require_once '../../include/baseTheme.php';

$TBL_HIERARCHY = 'hierarchy';
require_once 'include/lib/hierarchy.class.php';
$tree = new hierarchy();

$nameTools = $langSelectFac;

$roots = $tree->buildRootsArray();

if (count($roots) <= 0)
    die("ERROR: no root nodes");
else if (count($roots) == 1) {
    header("Location:" . $urlServer . "modules/auth/opencourses.php?fc=". intval($roots[0]));
    exit();
} else {
    $tool_content = $tree->buildNodesNavigationHtml($tree->buildRootsArray(), 'opencourses');
}

draw($tool_content, (isset($uid) and $uid)? 1: 0);

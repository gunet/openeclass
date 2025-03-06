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

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';

if (get_config('dont_display_courses_menu')) {
    redirect_to_home_page();
}

$redirectUrl = "modules/auth/opencourses.php?fc=";
$countCallback = null;
$showEmpty = true;

if (defined('LISTING_MODE') && LISTING_MODE === 'COURSE_METADATA') {
    require_once 'modules/course_metadata/CourseXML.php';
    $redirectUrl = "modules/course_metadata/opencourses.php?fc=";
    $countCallback = CourseXMLElement::getCountCallback();
    $showEmpty = false;
    // exit if feature disabled
    if (!get_config('opencourses_enable')) {
        header("Location: {$urlServer}");
        exit();
    }
}

$tree = new Hierarchy();
list($roots, $subtrees) = $tree->buildRootsWithSubTreesArray();

if (count($roots) <= 0) {
    Session::flash('message', $langNoRootNodes);
    Session::flash('alert-class', 'alert-danger');
    header("Location: {$urlServer}");
    exit();
} else if (count($roots) == 1) {
    header("Location:" . $urlServer . $redirectUrl . intval($roots[0]->id));
    exit();
} else {
    $data['tree'] = $tree->buildNodesNavigationHtml($roots, 'opencourses', $countCallback, array('showEmpty' => $showEmpty, 'respectVisibility' => true), $subtrees);
    view('modules.auth.listfaculties', $data);
}

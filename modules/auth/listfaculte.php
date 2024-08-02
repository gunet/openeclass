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
$toolName = $langSelectFac;
list($roots, $subtrees) = $tree->buildRootsWithSubTreesArray();

if (count($roots) <= 0) {
    die("ERROR: no root nodes");
} else if (count($roots) == 1) {
    header("Location:" . $urlServer . $redirectUrl . intval($roots[0]->id));
    exit();
} else {

    $margin = '';
    if(isset($_SESSION['uid'])){
        $margin = 'mt-4';
    }

    $tool_content .= "
        <div class='col-12 $margin'>
            <h1>$langCourses</h1>
        </div>
        <div class='col-12 mt-4'>
            <div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>
                <div class='col-lg-6 col-12'>
                    <ul class='list-group list-group-flush'>";
                $tool_content .= $tree->buildNodesNavigationHtml($roots, 'opencourses', $countCallback, array('showEmpty' => $showEmpty, 'respectVisibility' => true), $subtrees);
                $tool_content .= "                       
                    </ul>
                </div>
                <div class='col-lg-6 col-12 d-none d-lg-block text-end'>
                    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                </div>
            </div>
        </div>";
}

draw($tool_content, (isset($uid) and $uid) ? 1 : 0);

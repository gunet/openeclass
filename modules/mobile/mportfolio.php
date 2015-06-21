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


$require_mlogin = true;
$require_noerrors = true;
require_once('minit.php');

define('M_NOTERMINATE', 1);
define('M_ROOT', 'portfolio');
require_once('mcourses.php');

$tools = populateTools();

echo appendToolsDom($coursesDom, $coursesDomRoot, $tools);
exit();

//////////////////////////////////////////////////////////////////////////////////////

function appendToolsDom($dom, $domRoot, $toolsArr) {

    if (isset($toolsArr) && count($toolsArr) > 0) {

        $root = $domRoot->appendChild($dom->createElement('tools'));

        foreach ($toolsArr as $tool) {

            $t = $root->appendChild($dom->createElement('tool'));

            $t->appendChild(new DOMAttr('name', $tool->name));
            $t->appendChild(new DOMAttr('link', $tool->link));
            $t->appendChild(new DOMAttr('redirect', $tool->redirect));
            $t->appendChild(new DOMAttr('type', $tool->type));
            $t->appendChild(new DOMAttr('active', $tool->active));
        }
    }

    $dom->formatOutput = true;
    $ret = $dom->saveXML();
    return $ret;
}

function populateTools() {
    global $urlServer, $langMyAnnouncements, $langMyPersoDeadlines, $langMyProfile, $langRegCourses, $langMyAgenda;

    $profile = (isset($_SESSION['profile'])) ? '?profile=' . $_SESSION['profile'] . '&' : '?';
    $baseurl = $urlServer . 'modules/mobile/mlogin.php' . $profile . 'redirect=';

    $toolsArr = array();

    $tool = new stdClass();
    $tool->name = $langMyAnnouncements;
    $tool->redirect = $urlServer . 'modules/announcements/myannouncements.php';
    $tool->link = $baseurl . urlencode($tool->redirect);
    $tool->type = 'myannouncements';
    $tool->active = "true";
    $toolsArr[] = $tool;

    $tool = new stdClass();
    $tool->name = $langMyPersoDeadlines;
    $tool->redirect = $urlServer . 'modules/work/mydeadlines.php';
    $tool->link = $baseurl . urlencode($tool->redirect);
    $tool->type = 'mydeadlines';
    $tool->active = "true";
    $toolsArr[] = $tool;

    $tool = new stdClass();
    $tool->name = $langMyAgenda;
    $tool->redirect = $urlServer . 'main/personal_calendar/index.php';
    $tool->link = $baseurl . urlencode($tool->redirect);
    $tool->type = 'myagenda';
    $tool->active = "true";
    $toolsArr[] = $tool;

    $tool = new stdClass();
    $tool->name = $langMyProfile;
    $tool->redirect = $urlServer . 'main/profile/display_profile.php';
    $tool->link = $baseurl . urlencode($tool->redirect);
    $tool->type = 'myprofile';
    $tool->active = "true";
    $toolsArr[] = $tool;

    $tool = new stdClass();
    $tool->name = $langRegCourses;
    $tool->redirect = $urlServer . 'modules/auth/courses.php';
    $tool->link = $baseurl . urlencode($tool->redirect);
    $tool->type = 'coursesubscribe';
    $tool->active = "true";
    $toolsArr[] = $tool;

    return $toolsArr;
}

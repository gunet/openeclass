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

$require_mlogin = true;
$require_noerrors = true;
require_once('minit.php');

define('M_NOTERMINATE', 1);
define('M_ROOT', 'portfolio');
require_once('mcourses.php');

$profile = (isset($_SESSION['profile'])) ? '?profile=' . $_SESSION['profile'] . '&' : '?';
$baseurl = $urlServer . 'modules/mobile/mlogin.php' . $profile . 'redirect=';

$tools = populateTools($baseurl);
$profileTools = populateProfileTools($baseurl);

echo appendToolsDom($coursesDom, $coursesDomRoot, $tools, $profileTools);
exit();

//////////////////////////////////////////////////////////////////////////////////////

function appendToolsDom($dom, $domRoot, $toolsArr, $profileToolsArr) {
    appendToolArray($toolsArr, 'tools', $dom, $domRoot);
    appendToolArray($profileToolsArr, 'profiletools', $dom, $domRoot);

    appendUserDetails($domRoot, $dom);
    $dom->formatOutput = true;
    $ret = $dom->saveXML();
    return $ret;
}

function appendToolArray($toolsArr, $elementName, $dom, $domRoot) {
    if (isset($toolsArr) && count($toolsArr) > 0) {
        $root = $domRoot->appendChild($dom->createElement($elementName));
        foreach ($toolsArr as $tool) {
            appendToolElement($root, $dom, $tool);
        }
    }
}

function appendToolElement($root, $dom, $tool) {
    $t = $root->appendChild($dom->createElement('tool'));

    $t->appendChild(new DOMAttr('name', $tool->name));
    $t->appendChild(new DOMAttr('link', $tool->link));
    $t->appendChild(new DOMAttr('redirect', $tool->redirect));
    $t->appendChild(new DOMAttr('type', $tool->type));
    $t->appendChild(new DOMAttr('active', $tool->active));
}

function appendUserDetails ($root, $dom) {
    global $uid;

    $t = $root->appendChild($dom->createElement('user'));
    $t->appendChild(new DOMAttr('givenname', q(uid_to_name($uid, 'givenname'))));
    $t->appendChild(new DOMAttr('surname', q(uid_to_name($uid, 'surname'))));

}

function populateTools($baseurl) {
    global $langRegCourses, $langCourseCreate, $session;

    $toolsArr = array();
    $toolsArr[] = createNewTool($baseurl, $langRegCourses, 'modules/auth/courses.php?view=mobile', 'coursesubscribe');
    if ($session->status == USER_TEACHER) {
        $toolsArr[] = createNewTool($baseurl, $langCourseCreate, 'modules/create_course/create_course.php', 'createcourse');
    }
    return $toolsArr;
}

function populateProfileTools($baseurl) {
    global $langMyCourses, $langMyDropBox, $langMyAnnouncements, $langMyAgenda, $langNotes, $langMyProfile, $langMyStats;

    $toolsArr = array();
    $toolsArr[] = createNewTool($baseurl, $langMyCourses, 'main/my_courses.php?view=mobile', 'mycourses');
    $toolsArr[] = createNewTool($baseurl, $langMyDropBox, 'modules/message/index.php?view=mobile', 'mymessages');
    $toolsArr[] = createNewTool($baseurl, $langMyAnnouncements, 'modules/announcements/myannouncements.php?view=mobile', 'myannouncements');
    $toolsArr[] = createNewTool($baseurl, $langMyAgenda, 'main/personal_calendar/index.php?view=mobile', 'myagenda');
    $toolsArr[] = createNewTool($baseurl, $langNotes, 'main/notes/index.php?view=mobile', 'mynotes');
    $toolsArr[] = createNewTool($baseurl, $langMyProfile, 'main/profile/display_profile.php?view=mobile', 'myprofile');
    $toolsArr[] = createNewTool($baseurl, $langMyStats, 'modules/usage/index.php?t=u&view=mobile', 'mystats');

    return $toolsArr;
}

function createNewTool($baseurl, $name, $redirect, $type) {
    global $urlServer;

    $tool = new stdClass();
    $tool->name = $name;
    $tool->redirect = $urlServer . $redirect;
    $tool->link = $baseurl . urlencode($tool->redirect);
    $tool->type = $type;
    $tool->active = "true";

    return $tool;
}

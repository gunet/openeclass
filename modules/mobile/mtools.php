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
$require_mcourse = true;
$require_noerrors = true;
require_once('minit.php');
require_once('include/tools.php');

$groupsArr = array();
$toolsArr = array();

$toolArr = lessonToolsMenu(false);
$group = new stdClass();
$group->id = 0;
$group->name = $langIdentity;
$groupsArr[] = $group;

$tool = new stdClass();

// course description
$tool->id = 0;
$tool->name = $langCourseProgram;
$tool->link = $urlAppend . 'courses/' . $course_code;
$tool->img = 'coursedescription';
$tool->type = 'coursedescription';
$tool->active = true;
$toolsArr[0][] = $tool;

// course tools
$offset = 1;
for ($i = 0; $i < count($toolArr); $i++) {
    $id = $i + $offset;

    if ($toolArr[$i][0]['type'] == 'text') {
        $group = new stdClass();
        $group->id = $id;
        $group->name = $toolArr[$i][0]['text'];
        $groupsArr[] = $group;

        $numOfTools = count($toolArr[$i][1]);
        for ($j = 0; $j < $numOfTools; $j++) {
            $tool = new stdClass();
            $tool->id = (isset($toolArr[$i][4][$j])) ? $toolArr[$i][4][$j] : null;
            $tool->name = $toolArr[$i][1][$j];
            $tool->link = $toolArr[$i][2][$j];
            $tool->img = $toolArr[$i][3][$j];
            $tool->type = $toolArr[$i][3][$j];
            $tool->active = visible_module($toolArr[$i][4][$j], $course_id);
            $toolsArr[$id][] = $tool;
        }
    }
}

echo createDom($groupsArr, $toolsArr);
exit();

/**
 * Generates an XML representation of tool groups and their associated tools.
 *
 * @param array $groupsArr An array of group objects, where each object contains information about a tool group.
 * @param array $toolsArr An array where keys are group IDs and values are arrays of tool objects associated with the respective group.
 * @return string The generated XML as a string.
 */
function createDom($groupsArr, $toolsArr) {
    global $status;

    $dom = new DomDocument('1.0', 'utf-8');

    $root = $dom->appendChild($dom->createElement('tools'));

    foreach ($groupsArr as $group) {

        if (isset($toolsArr[$group->id])) {

            $g = $root->appendChild($dom->createElement('toolgroup'));
            $gname = $g->appendChild(new DOMAttr('name', $group->name));

            foreach ($toolsArr[$group->id] as $tool) {
                $t = $g->appendChild($dom->createElement('tool'));

                $name = $t->appendChild(new DOMAttr('name', $tool->name));
                $link = $t->appendChild(new DOMAttr('link', correctLink($tool->link)));
                $redirect = $t->appendChild(new DOMAttr('redirect', correctRedirect($tool->link)));
                $type = $t->appendChild(new DOMAttr('type', $tool->type));
                $acti = $t->appendChild(new DOMAttr('active', $tool->active));
            }
        }
    }

    if ($status == USER_TEACHER || $status == USER_STUDENT) {
        $stname = ($status == USER_TEACHER) ? 'teacher' : 'student';
        $st = $root->appendChild($dom->createElement('status'));
        $st->appendChild(new DOMAttr('name', q($stname)));
    }

    $dom->formatOutput = true;
    $ret = $dom->saveXML();
    return $ret;
}

function correctLink($value) {
    global $urlServer, $urlAppend;
    $link = $urlServer . substr($value, strlen($urlAppend));
    $profile = (isset($_SESSION['profile'])) ? '?profile=' . $_SESSION['profile'] . '&' : '?';
    $redirect = 'redirect=' . urlencode($link);
    return $urlServer . 'modules/mobile/mlogin.php' . $profile . $redirect;
}

function correctRedirect($value) {
    global $urlServer, $urlAppend;
    return $urlServer . substr($value, strlen($urlAppend));
}
<?php

$require_mlogin = true;
$require_mcourse = true;
$require_noerrors = true;

require_once 'minit.php';

$q = Database::get()->querySingle('SELECT view_type FROM course WHERE id = ?d', $course_id)->view_type;

if ($q != 'units') { // course is not in units view
    echo RESPONSE_NO_COURSE_UNITS;
    exit();
}

if ($_SESSION['status'] == USER_TEACHER) {
    $course_units = Database::get()->queryArray("SELECT id, title, comments FROM course_units WHERE course_id = ?d ORDER BY `order` ASC", $course_id);
} else {
    $units = Database::get()->queryArray("SELECT id, title, comments FROM course_units WHERE course_id = ?d AND (visible = 1 OR visible = 2) ORDER BY `order` ASC", $course_id);
    $course_units = findUserVisibleUnits($uid, $units);
}

if (isset($course_units) && count($course_units) > 0) {
    list($courseUnitsDom, $courseUnitsDomRoot) = createCourseUnitsDom($course_units);
} else { // no course units
    echo RESPONSE_NO_COURSE_UNITS;
    exit();
}

if (!defined('M_NOTERMINATE')) {
    echo $courseUnitsDom->saveXML();
    exit();
}

/**
 * @brief create xml file
 * @param $course_units
 * @return array
 * @throws DOMException
 */
function createCourseUnitsDom($course_units) {

    $dom = new DomDocument('1.0', 'utf-8');

    if (defined('M_ROOT')) {
        $root0 = $dom->appendChild($dom->createElement(M_ROOT));
        $root = $root0->appendChild($dom->createElement('units'));
        $retroot = $root0;
    } else {
        $root = $dom->appendChild($dom->createElement('units'));
        $retroot = $root;
    }
    foreach ($course_units as $course_unit) {
        $u = $root->appendChild($dom->createElement('unit'));
        $course_unit_description = ellipsize(html2text($course_unit->comments), 80);
        $u->appendChild(new DOMAttr('id', $course_unit->id));
        $u->appendChild(new DOMAttr('name', $course_unit->title));
        $u->appendChild(new DOMAttr('description', $course_unit_description));
    }

    $dom->formatOutput = true;
    return array($dom, $retroot);
}
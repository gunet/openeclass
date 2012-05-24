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


$courses = array();

$sql = "SELECT course.code,
               course.lang, 
               course.title,
               course.description,
               course.keywords,
               course.visible,
               course.prof_names,
               course.public_code,
               course_user.statut as statut
          FROM course JOIN course_user ON course.id = course_user.course_id
         WHERE course_user.user_id = $uid        
      ORDER BY statut, course.title, course.prof_names";
$sql2 = "SELECT course.code,
                course.lang, 
                course.title,
                course.description,
                course.keywords,
                course.visible,
                course.prof_names,
                course.public_code, 
                course_user.statut as statut
           FROM course JOIN course_user ON course.id = course_user.course_id
          WHERE course_user.user_id = $uid
            AND course.visible != ".COURSE_INACTIVE."
       ORDER BY statut, course.title, course.prof_names";

if ($_SESSION['statut'] == 1) {
        $result = db_query($sql);
}
if ($_SESSION['statut'] == 5) {
        $result = db_query($sql2);
}

if ($result and mysql_num_rows($result) > 0)
    while ($course = mysql_fetch_object($result))
        $courses[] = $course;


list($coursesDom, $coursesDomRoot) = createCoursesDom($courses);

if (!defined('M_NOTERMINATE')) {
    echo $coursesDom->saveXML();
    exit();
}

//////////////////////////////////////////////////////////////////////////////////////

function createCoursesDom($coursesArr) {
    global $langMyCoursesProf, $langMyCoursesUser;
    
    $dom = new DomDocument('1.0', 'utf-8');
    
    if (defined('M_ROOT')) {
        $root0 = $dom->appendChild($dom->createElement(M_ROOT));
        $root = $root0->appendChild($dom->createElement('courses'));
        $retroot = $root0;
    } else {
        $root = $dom->appendChild($dom->createElement('courses'));
        $retroot = $root;
    }

    if (isset($coursesArr) && count($coursesArr) > 0) {

        $k = 0;
        $this_statut = 0;

        foreach($coursesArr as $course) {

            $old_statut = $this_statut;
            $this_statut = $course->statut;

            if ($k == 0 || ($old_statut != $this_statut)) {
                $cg = $root->appendChild($dom->createElement('coursegroup'));
                $gname = ($this_statut == 1) ? $langMyCoursesProf : $langMyCoursesUser;
                $cg->appendChild(new DOMAttr('name', $gname));
            }

            $c = $cg->appendChild($dom->createElement('course'));

            $titleStr = ($course->code === $course->public_code) ? $course->title : $course->title .' - '. $course->public_code;

            $c->appendChild(new DOMAttr('code', $course->code));
            $c->appendChild(new DOMAttr('title', $titleStr));
            $c->appendChild(new DOMAttr('description', $course->description));
            
            //$c->appendChild(new DOMAttr('teacher', $course->titulaires));
            //$c->appendChild(new DOMAttr('visible', $course->visible));
            //$c->appendChild(new DOMAttr('visibleName', getVisibleName($course->visible)));

            $k++;
        }
    }

    $dom->formatOutput = true;
    return array($dom, $retroot);
}

function getVisibleName($value) {
    global $m;
    
    $visibles = array(3 => $m['linactive'],
                      2 => $m['legopen'],
                      1 => $m['legrestricted'],
                      0 => $m['legclosed']);
    
    return $visibles[$value];
}

function getTypeNames($value) {
    $ret = array($value, $value);
    
    $containslang = (substr($value, 0, strlen("lang")) === "lang") ? true : false;
    if ($containslang)
        $ret = array($GLOBALS[$value], $GLOBALS[$value."s"]);
    
    return $ret;
}

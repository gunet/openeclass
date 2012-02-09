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
require_once('../../include/minit.php');


$courses = array();

$sql = "SELECT cours.code,
               cours.languageCourse, 
               cours.intitule,
               cours.description,
               cours.course_keywords,
               cours.visible,
               cours.titulaires,
               cours.fake_code, 
               cours_user.statut as statut
          FROM cours JOIN cours_user ON cours.cours_id = cours_user.cours_id
         WHERE cours_user.user_id = $uid        
      ORDER BY statut, cours.intitule, cours.titulaires";
$sql2 = "SELECT cours.code,
                cours.languageCourse, 
                cours.intitule,
                cours.description,
                cours.course_keywords,
                cours.visible,
                cours.titulaires,
                cours.fake_code, 
                cours_user.statut as statut
           FROM cours JOIN cours_user ON cours.cours_id = cours_user.cours_id
          WHERE cours_user.user_id = $uid
            AND cours.visible != ".COURSE_INACTIVE."
       ORDER BY statut, cours.intitule, cours.titulaires";

if ($_SESSION['statut'] == 1) {
        $result = db_query($sql);
}
if ($_SESSION['statut'] == 5) {
        $result = db_query($sql2);
}

if ($result and mysql_num_rows($result) > 0)
    while ($course = mysql_fetch_object($result))
        $courses[] = $course;


echo createDom($courses);
exit();

//////////////////////////////////////////////////////////////////////////////////////

function createDom($coursesArr) {
    global $langMyCoursesProf, $langMyCoursesUser;
    
    $dom = new DomDocument('1.0', 'utf-8');

    $root = $dom->appendChild($dom->createElement('courses'));

    if (isset($coursesArr) && count($coursesArr) > 0) {

        $k = 0;
        $this_statut = 0;

        foreach($coursesArr as $course) {

            $old_statut = $this_statut;
            $this_statut = $course->statut;

            if ($k == 0 || ($old_statut != $this_statut)) {
                $cg = $root->appendChild($dom->createElement('coursegroup'));
                $gname = ($this_statut == 1) ? $langMyCoursesProf : $langMyCoursesUser;
                $cgn = $cg->appendChild(new DOMAttr('name', $gname));
            }

            $c = $cg->appendChild($dom->createElement('course'));

            $titleStr = ($course->code === $course->fake_code) ? $course->intitule : $course->intitule .' - '. $course->fake_code ;

            $code = $c->appendChild(new DOMAttr('code', $course->code));
            $title = $c->appendChild(new DOMAttr('title', $titleStr));
            $descr = $c->appendChild(new DOMAttr('description', $course->description));
            
            //$teach = $c->appendChild(new DOMAttr('teacher', $course->titulaires));
            //$visib = $c->appendChild(new DOMAttr('visible', $course->visible));
            //$visin = $c->appendChild(new DOMAttr('visibleName', getVisibleName($course->visible)));

            $k++;
        }
    }

    $dom->formatOutput = true;
    $ret = $dom->saveXML();
    return $ret;
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

<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014 Greek Universities Network - GUnet
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

include '../../include/baseTheme.php';

$toolName = $langPreview;
$navigation[] = array('url' => 'listfaculte.php', 'name' => $langSelectFac);

$data['courseId'] = $courseId = course_code_to_id($_GET['c']);

$is_collab_course = 0;
$data['infoCourse'] = $infoCourse = Database::get()->queryArray("SELECT * FROM course WHERE id = ?d",$courseId);
foreach($infoCourse as $c){
      if($c->is_collaborative){
            $is_collab_course = 1;
      }
}
$data['is_collab_course'] = $is_collab_course;

$data['course_descriptions'] = $res = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
                                    LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
                                    WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $courseId);

$data['action_bar'] = action_bar(array(
                                array('title' => $langBack,
                                      'url' => 'listfaculte.php',
                                      'icon' => 'fa-reply',
                                      'level' => 'primary',
                                      'button-class' => 'btn-default')
                            ),false);


$data['menuTypeID'] = isset($uid) && $uid ? 1 : 0;

view('modules.auth.info_course', $data);

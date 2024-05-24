<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
 * ========================================================================
 */

 
function is_tutor_course($cid,$userId){
    $result = Database::get()->querySingle("SELECT tutor FROM course_user WHERE course_id = ?d AND user_id = ?d",$cid,$userId);
    return $result->tutor;
}

function is_consultant($cid,$userId){
    $result = Database::get()->querySingle("SELECT editor FROM course_user WHERE course_id = ?d AND user_id = ?d",$cid,$userId);
    return $result->editor;
}
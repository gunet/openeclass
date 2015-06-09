<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

/**
 * @file about.php
 * @brief Displays general platform information.
 * @author original developed by Ophelia Neofytou.
 */

require_once '../include/baseTheme.php';
$pageName = $langInfo;
$tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
$tool_content .= "<div class='row'><div class='col-sm-12'><div class='panel'><div class='panel-body'>
<div><label>$langInstituteShortName:&nbsp;</label><a href='$InstitutionUrl' target='_blank' class='mainpage'>$Institution</a></div>
<div><label>$langCampusName:&nbsp;</label>$siteName&nbsp;</div>
<div><label>$langVersion:&nbsp;</label><a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank'>Open eClass " . ECLASS_VERSION . "&raquo;</a></div>
<div><label>$langSupportUser&nbsp;</label>" . q(get_config('admin_name')) . "</div>
</div></div></div>";
$a = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d", COURSE_INACTIVE)->count;
$a1 = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_OPEN)->count;
$a2 = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_REGISTRATION)->count;
$a3 = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_CLOSED)->count;
$tool_content .= "</div><div class='row'><div class='col-sm-6'>";
$tool_content .= "<ul class='list-group'>
    <li class='list-group-item'><label>$langCoursesHeader</label><span class='badge'>$a</span></li>
    <li class='list-group-item'>$langOpenCoursesShort<span class='badge'>$a1</span></li>
    <li class='list-group-item'>$langOpenCourseWithRegistration<span class='badge'>$a2</span></li>
    <li class='list-group-item'>$langClosedCourses<span class='badge'>$a3</span></li>
  </ul>";
$tool_content .= "</div>";

$e = Database::get()->querySingle("SELECT COUNT(*) as count FROM user")->count;
$b = Database::get()->querySingle('SELECT COUNT(*) as count FROM user WHERE status = ?d', USER_TEACHER)->count;
$c = Database::get()->querySingle('SELECT COUNT(*) as count FROM user WHERE status = ?d', USER_STUDENT)->count;
$d = Database::get()->querySingle('SELECT COUNT(*) as count FROM user WHERE status = ?d', USER_GUEST)->count;

$tool_content .= "<div class='col-sm-6'>";
$tool_content .= "<ul class='list-group'>
          <li class='list-group-item'><label>$langUsers</label><span class='badge'>$e</span></li>
          <li class='list-group-item'>$langTeachers<span class='badge'>$b</span></li>
          <li class='list-group-item'>$langStudents<span class='badge'>$c</span></li>
          <li class='list-group-item'>$langGuest<span class='badge'>$d</span> </li>
      </ul>";
$tool_content .= "</div></div>";

if (isset($uid) and $uid) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}

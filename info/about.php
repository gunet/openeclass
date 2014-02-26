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
$nameTools = $langInfo;
$tool_content .= "<table class='tbl_1' width='100%'>
<tr'>
<th width='160' class='left'><strong>$langCampusName:</strong></th>
<td><b>$siteName&nbsp;</b>(<a href='$InstitutionUrl' target='_blank' class='mainpage'>$Institution</a>)</td>
</tr>
<tr>
<th class='left'><strong>$langVersion:</strong></th>
<td><b><a href='http://www.openeclass.org/' title='Open eClass Portal' target='_blank'>Open eClass " . ECLASS_VERSION . "&raquo;</a></b></td>
<tr>
<th class='left'><strong>$langCoursesHeader:</strong></th>
<td>";

$a = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d", COURSE_INACTIVE)->count;
$a1 = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_OPEN)->count;
$a2 = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_REGISTRATION)->count;
$a3 = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_CLOSED)->count;

$tool_content .= "$langAboutCourses <b>$a</b> $langCourses<br />
  <ul>
    <li><b>$a1</b> $langOpen,</li>
    <li><b>$a2</b> $langSemiopen,</li>
    <li><b>$a3</b> $langClosed </li>
  </ul>
</td>
</tr>";

$e = Database::get()->querySingle("SELECT COUNT(*) as count FROM user")->count;
$b = Database::get()->querySingle('SELECT COUNT(*) as count FROM user WHERE status = ?d', USER_TEACHER)->count;
$c = Database::get()->querySingle('SELECT COUNT(*) as count FROM user WHERE status = ?d', USER_STUDENT)->count;
$d = Database::get()->querySingle('SELECT COUNT(*) as count FROM user WHERE status = ?d', USER_GUEST)->count;

$tool_content .= "
    <tr>
      <th class='left'><strong>$langUsers:</strong></th>
      <td>$langAboutUsers <b>$e</b> $langUsers
        <ul>
          <li><b>$b</b> $langTeachers, </li>
          <li><b>$c</b> $langStudents $langAnd </li>
          <li><b>$d</b> $langGuest </li>
      </ul></td>
    </tr>
      <tr>
        <th class='left'><strong>$langSupportUser</strong></th>
        <td>" . q(get_config('admin_name')) . "</td>
  </tr>
   </table>";

if (isset($uid) and $uid) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}

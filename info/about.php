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

$a = db_query_get_single_value("SELECT COUNT(*) FROM course WHERE visible != " . COURSE_INACTIVE);
$a1 = db_query_get_single_value("SELECT COUNT(*) FROM course WHERE visible = " . COURSE_OPEN);
$a2 = db_query_get_single_value("SELECT COUNT(*) FROM course WHERE visible = " . COURSE_REGISTRATION);
$a3 = db_query_get_single_value("SELECT COUNT(*) FROM course WHERE visible = " . COURSE_CLOSED);

$tool_content .= "$langAboutCourses <b>$a[0]</b> $langCourses<br />
  <ul>
    <li><b>$a1[0]</b> $langOpen,</li>
    <li><b>$a2[0]</b> $langSemiopen,</li>
    <li><b>$a3[0]</b> $langClosed </li>
  </ul>
</td>
</tr>";

$e = db_query_get_single_value('SELECT COUNT(*) FROM user');
$b = db_query_get_single_value('SELECT COUNT(*) FROM user WHERE status = '.USER_TEACHER);
$c = db_query_get_single_value('SELECT COUNT(*) FROM user WHERE status = '.USER_STUDENT);
$d = db_query_get_single_value('SELECT COUNT(*) FROM user WHERE status = '.USER_GUEST);

$tool_content .= "
    <tr>
      <th class='left'><strong>$langUsers:</strong></th>
      <td>$langAboutUsers <b>$e[0]</b> $langUsers
        <ul>
          <li><b>$b[0]</b> $langTeachers, </li>
          <li><b>$c[0]</b> $langStudents $langAnd </li>
          <li><b>$d[0]</b> $langGuest </li>
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

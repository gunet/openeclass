<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


$path2add = 2;
include '../include/baseTheme.php';
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

  /*
  * Make table with general platform information
  * ophelia neofytou - 2006/09/26
  */

  mysql_select_db($mysqlMainDb);

  $a = mysql_fetch_row(db_query("SELECT COUNT(*) FROM cours WHERE visible != ".COURSE_INACTIVE));
  $a1 = mysql_fetch_row(db_query("SELECT COUNT(*) FROM cours WHERE visible = ".COURSE_OPEN));
  $a2 = mysql_fetch_row(db_query("SELECT COUNT(*) FROM cours WHERE visible = ".COURSE_REGISTRATION));
  $a3 = mysql_fetch_row(db_query("SELECT COUNT(*) FROM cours WHERE visible = ".COURSE_CLOSED));

  $tool_content .= "$langAboutCourses <b>$a[0]</b> $langCourses<br />
  <ul>
    <li><b>$a1[0]</b> $langOpen,</li>
    <li><b>$a2[0]</b> $langSemiopen,</li>
    <li><b>$a3[0]</b> $langClosed </li>
  </ul>
</td>
</tr>";

$e = mysql_fetch_row(db_query('SELECT COUNT(*) FROM user'));
$b = mysql_fetch_row(db_query('SELECT COUNT(*) FROM user WHERE statut=1'));
$c = mysql_fetch_row(db_query('SELECT COUNT(*) FROM user WHERE statut=5'));
$d = mysql_fetch_row(db_query('SELECT COUNT(*) FROM user WHERE statut=10'));

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
        <td>$administratorName $administratorSurname</td>
  </tr>
   </table>";

if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}

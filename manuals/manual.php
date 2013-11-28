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

$mail_ver_excluded = true;
require_once '../include/baseTheme.php';
$nameTools = $langManuals;

$addon = '';

if (!in_array($language, array('el', 'en'))) {
    $language = 'en';
    $addon = "($langOnlyInEnglish)";
}

$tool_content .= "<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langFinalDesc, 'detail_descr', $language) . "</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td> " . manlink($langShortDesc, 'short_descr', $language) . "</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langManT, 'mant', $language) . "</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td> " . manlink($langManS, 'mans', $language) . "</td>
  </tr>
</table>";

$tool_content .= "<br /><p class='tool_title'>$langTutorials $langOfTeacher $addon";

$tool_content .= "</p><table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langCreateAccount, 'create_account', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langCourseCreate, 'create_course', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langPersonalisedBriefcase, 'portfolio_management', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langAdministratorCourse, 'course_management', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langAdministratorForum, 'forum_management', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langAdministratorGroup, 'group_management', $language) . "</a></td>
  </tr>
</table>";

$tool_content .= "<br /><p class='tool_title'>$langTutorials $langOfStudent $addon</p>

<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langRegCourses, 'register_course', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
   <td>" . manlink($langPersonalisedBriefcase, 'personal_portfolio', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langIntroToCourse, 'ecourse', $language) . "</a>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>" . manlink($langForumParticipation, 'forum', $language) . "</a>
  </tr>
</table>";


if (isset($uid) and $uid) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}

// create link
function manlink($desc, $link, $language) {
    global $addon;

    $url = 'http://wiki.openeclass.org/3.0/doku.php';
    return "<a href='$url?id=$language:$link' target='_blank' class='mainpage'>$desc</a>";
}

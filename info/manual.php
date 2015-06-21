<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
$pageName = $langManuals;

$addon = '';

if (!in_array($language, array('el', 'en'))) {
    $language = 'en';
    $addon = " ($langOnlyInEnglish)";
}
$tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
$tool_content .= "<table class='table table-striped table-bordered table-hover'>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langFinalDesc, 'detail_descr', $language) . "</td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td> " . manlink($langShortDesc, 'short_descr', $language) . "</td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langManT, 'mant', $language) . "</td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td> " . manlink($langManS, 'mans', $language) . "</td>
  </tr>
</table>";

$tool_content .= "<br><p class='tool_title'>$langTeacherTutorials$addon";

$tool_content .= "</p><table class='table table-striped table-bordered table-hover'>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langCreateAccount, 'create_account', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langCourseCreate, 'create_course', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langUserPortfolio, 'portfolio_management', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langAdministratorCourse, 'course_management', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langAdministratorForum, 'forum_management', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langAdministratorGroup, 'group_management', $language) . "</a></td>
  </tr>
</table>";

$tool_content .= "<br /><p class='tool_title'>$langStudentTutorials$addon</p>
<table class='table table-striped table-bordered table-hover'>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langRegCourses, 'register_course', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
   <td>" . manlink($langUserPortfolio, 'personal_portfolio', $language) . "</a></td>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langIntroToCourse, 'ecourse', $language) . "</a>
  </tr>
  <tr>
    <th width='16'>".icon('fa-globe')."</th>
    <td>" . manlink($langForumParticipation, 'forum', $language) . "</a>
  </tr>
</table>";


if (isset($uid) and $uid) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}

/**
 * @brief create link to manuals
 * @param type $desc
 * @param type $link
 * @param type $language
 * @return type
 */
function manlink($desc, $link, $language) {
    $url = 'http://docs.openeclass.org/3.1';
    return "<a href='$url/$language:$link' target='_blank' class='mainpage'>$desc</a>";
}

<?php
/* ========================================================================
 * Open eClass 2.6
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

$path2add=2;
$mail_ver_excluded = true;
include '../include/baseTheme.php';
$nameTools = $langManuals;

$lang = langname_to_code($language);
$addon = '';

if (!in_array($lang, array('el', 'en'))) {
        $lang = 'en';
        $addon = "($langOnlyInEnglish)";        
}

$tool_content .= "<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langFinalDesc, 'detail_descr', $lang) ."</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td> ". manlink($langShortDesc, 'short_descr', $lang) ."</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langManT, 'mant',  $lang) ."</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td> ". manlink($langManS, 'mans', $lang) ."</td>
  </tr>
</table>";

$tool_content .= "<br /><p class='tool_title'>$langTutorials $langOfTeacher $addon";

$tool_content .= "</p><table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langCreateAccount, 'create_account', $lang)  ."</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langCourseCreate, 'create_course', $lang) ."</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langPersonalisedBriefcase, 'portfolio_management', $lang) ."</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langAdministratorCourse, 'course_management', $lang). "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langAdministratorForum, 'forum_management', $lang). "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langAdministratorGroup, 'group_management', $lang). "</a></td>
  </tr>
</table>";

$tool_content .= "<br /><p class='tool_title'>$langTutorials $langOfStudent $addon</p>

<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langRegCourses, 'register_course', $lang) ."</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
   <td>". manlink($langPersonalisedBriefcase, 'personal_portfolio', $lang). "</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langIntroToCourse, 'ecourse', $lang) ."</a>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/wiki.png' alt='icon'></th>
    <td>". manlink($langForumParticipation, 'forum', $lang) ."</a>
  </tr>
</table>";


if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}

// create link
function manlink($desc, $link, $lang)        
{                
        global $addon;
        
        $url = 'http://wiki.openeclass.org/'.ECLASS_VERSION.'/doku.php';
        return "<a href='$url?id=$lang:$link' target='_blank' class='mainpage'>$desc</a> $addon";
}
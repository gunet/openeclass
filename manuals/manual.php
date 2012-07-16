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


/*
 * eClass manuals Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component offers  the platform's manuals.
 *
 */

$mail_ver_excluded = true;
require_once '../include/baseTheme.php';
$nameTools = $langManuals;

$urlServerTemp = strrev(substr(strrev($urlServer),1));

$ext = langname_to_code($language);


function manlink($basename, $langext, $desc)
{
        global $urlServerTemp, $langFormatPDF;

        if (file_exists($basename . '_' . $langext . '.pdf')) {
                $url = $urlServerTemp . '/manuals/' . $basename . '_' . $langext . '.pdf';
        } else {
                $url = $urlServerTemp . '/manuals/' . $basename . '_en.pdf';
        }
        return "<a href='$url' target='_blank' class='mainpage'>$desc</a>";
}

if (isset($language) and $language == 'greek') {
	$rowspan = 5;
} else {
	$rowspan = 1;
}




$tool_content .= "<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td>". manlink('OpeneClass25', $ext, $langFinalDesc) ."</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td> ". manlink('OpeneClass25_short', $ext, $langShortDesc) ."</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td>". manlink('manT/OpeneClass25_ManT', $ext, $langManT) ."</td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td> ". manlink('manS/OpeneClass25_ManS', $ext, $langManS) ."</td>
  </tr>
</table>
";

if (isset($language) and $language == 'greek') {

$tool_content .= "

<p class='tool_title'>$langTutorials $langOfTeacher</p>

<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/create_teacher_account.pdf' target='_blank'>$langCreateAccount</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td> <a href='http://www.openeclass.org/guides/pdf/create_course.pdf' target='_blank'>$langCourseCreate</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/teacher_portfolio.pdf' target='_blank'>$langPersonalisedBriefcase</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td> <a href='http://www.openeclass.org/guides/pdf/manage_course.pdf' target='_blank'>$langAdministratorCourse</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td> <a href='http://www.openeclass.org/guides/pdf/forum_teacher_view.pdf' target='_blank'>$langAdministratorForum</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td> <a href='http://www.openeclass.org/guides/pdf/manage_groups.pdf' target='_blank'>$langAdministratorGroup</a></td>
  </tr>

</table>





<p class='tool_title'>$langTutorials $langOfStudent</p>


<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/course_registration.pdf' target='_blank'>$langRegCourses</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
   <td><a href='http://www.openeclass.org/guides/pdf/student_portfolio.pdf' target='_blank'>$langPersonalisedBriefcase</a></td>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/view_course.pdf' target='_blank'>$langIntroToCourse</a>
  </tr>
  <tr>
    <th width='16'><img src='$themeimg/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/forum_student_view.pdf' target='_blank'>$langForumParticipation</a>
  </tr>

</table>
";


}

$tool_content .= '<br><p class="smaller right">' .
                 sprintf($langAcrobat,
                         '<a href="http://get.adobe.com/reader/" target="_blank">',
                         '</a>',
                         '<a href="http://pdfreaders.org/" target="_blank">',
                         '</a>') . '</p>';

if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}


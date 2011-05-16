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

$path2add=2;
include '../include/baseTheme.php';
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
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td>". manlink('OpeneClass23', $ext, $langFinalDesc) ."</td>
  </tr>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td> ". manlink('OpeneClass23_short', $ext, $langShortDesc) ."</td>
  </tr>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td>". manlink('manT/OpeneClass23_ManT', $ext, $langManT) ."</td>
  </tr>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td> ". manlink('manS/OpeneClass23_ManS', $ext, $langManS) ."</td>
  </tr>
</table>
";

if (isset($language) and $language == 'greek') {

$tool_content .= "

<p class='tool_title'>$langTutorials $langOfTeacher</p>

<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/create_teacher_account.pdf' target='_blank'>$langCreateAccount</a></td>
  </tr>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td> <a href='http://www.openeclass.org/guides/pdf/create_course.pdf' target='_blank'>$langCourseCreate</a></td>
  </tr>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/teacher_portfolio.pdf' target='_blank'>$langPersonalisedBriefcase</a></td>
  </tr>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td> <a href='http://www.openeclass.org/guides/pdf/manage_course.pdf' target='_blank'>$langAdministratorCourse</a></td>
  </tr>
</table>





<p class='tool_title'>$langTutorials $langOfStudent</p>


<table width='100%' class='tbl_alt'>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/course_registration.pdf' target='_blank'>$langRegCourses</a></td>
  </tr>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
   <td><a href='http://www.openeclass.org/guides/pdf/student_portfolio.pdf' target='_blank'>$langPersonalisedBriefcase</a></td>
  </tr>
  <tr>
    <th width='16px'><img src='../template/classic/img/pdf.png' width='16' height='16' alt='icon'></th>
    <td><a href='http://www.openeclass.org/guides/pdf/view_course.pdf' target='_blank'>$langIntroToCourse</a>
  </tr>
</table>
";


}




$tool_content .= "<br><p class='smaller right'>$langAcrobat $langWhere <a href='http://www.adobe.com/products/acrobat/readstep2.html' target='_blank'>
		$langHere</a>.</p>";

if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}


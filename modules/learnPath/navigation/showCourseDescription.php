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


/* ===========================================================================
  showCourseDescription.php
  @last update: 30-06-2006 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
  ==============================================================================
  @Description: This script displays the Course Description when
  the user is navigating in a learning path.

  @Comments:

  @todo:
  ==============================================================================
 */

$require_current_course = true;
require_once '../../../include/init.php';
require_once 'include/lib/textLib.inc.php';

$pageName = $langCourseProgram;

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>">
        <link href="../../../template/<?php echo $theme ?>/theme.css" rel="stylesheet" type="text/css" />
        <title><?php echo $langCourseProgram ?></title>
    </head>
    <body style="margin: 0px; padding-left: 0px; height: 100%!important; height: auto; background-color: #ffffff;">
        <div id="content" style="width:800px; margin: 0 auto;">

            <?php
            $q = Database::get()->queryArray("SELECT id, title, comments FROM course_description WHERE course_id = ?d ORDER BY `order`", $course_id);

            if ($q && count($q) > 0) {
                foreach ($q as $row) {
                    echo "
			<table width='100%' class='tbl_border'>
			<tr class='odd'>
			<td class='bold'>" . q($row->title) . "</td>\n
			</tr>
			<tr>";

                    if ($is_editor) {
                        echo "\n<td colspan='6'>" . standard_text_escape($row->comments) . "</td>";
                    } else {
                        echo "\n<td>" . standard_text_escape($row->comments) . "</td>";
                    }
                    echo "</tr></table><br />\n";
                }
            } else {
                echo "   <div class='alert alert-warning'>$langThisCourseDescriptionIsEmpty</div>";
            }
            ?></div></body></html>


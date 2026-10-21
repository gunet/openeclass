<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */


/* ===========================================================================
  showCourseDescription.php
  @last update: 30-06-2023 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
  ==============================================================================
  @Description: This script displays the Course Description when
  the user is navigating in a learning path.

  ==============================================================================
 */

$require_current_course = true;
require_once '../../../include/baseTheme.php';

$pageName = $langCourseProgram;

$theme_id = $_SESSION['theme_options_id'] ?? get_config('theme_options_id');
$theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);
$cssFile = '';
if ($theme_id > 0) {
    $cssFile = "<link rel='stylesheet' type='text/css' href='{$urlAppend}courses/theme_data/$theme_id/style_str.css?".time()."'/>";
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
    . "\n<html>\n"
    . '<head>' . "\n"
    . '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">' . "\n"
    . "<script type='text/javascript' src='{$urlAppend}js/jquery-3.6.0.min.js'></script>
        <link href='{$urlAppend}template/modern/css/bootstrap.min.css' rel='stylesheet'>
        <link href='{$urlAppend}template/modern/css/fonts_all/typography.css?" . time() . "' rel='stylesheet'>
        <link href='{$urlAppend}template/modern/css/lp.css?" . time() . "' rel='stylesheet'>\n
       <link href='{$urlAppend}template/modern/css/default.css?" . time() . "' rel='stylesheet'>\n
       $cssFile\n';       
    <title>$langCourseProgram</title>
    </head>
    <body class='body-learningPath' style='margin: 0px; height: 100%!important; height: auto;'>
        <div id='content' style='width:800px; margin: 0 auto;'>";

        $q = Database::get()->queryArray("SELECT id, title, comments FROM course_description WHERE course_id = ?d ORDER BY `order`", $course_id);

        if ($q && count($q) > 0) {
            foreach ($q as $row) {
                echo "
                <table class='table-default'>
                <tr>
                <td><strong>" . q($row->title) . "</strong></td>\n
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
            echo "   <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langThisCourseDescriptionIsEmpty</span></div>";
        }

        echo "</div>
    </body>
</html>";
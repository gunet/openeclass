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
require_once '../../../include/init.php';

$pageName = $langCourseProgram;

$theme_id = isset($_SESSION['theme_options_id']) ? $_SESSION['theme_options_id'] : get_config('theme_options_id');
$theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>">
        <link href="../../../template/modern/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="../../../template/modern/css/fonts_all/typography.css?<?php echo time(); ?>" type="text/css">
        <link href="../../../template/modern/css/lp.css?<?php echo time(); ?>" rel="stylesheet" type="text/css">
        <link href="../../../template/modern/css/default.css?<?php echo time(); ?>" rel="stylesheet" type="text/css">

        <?php if($theme_id > 0){ ?>
            <link href="../../../courses/theme_data/<?php echo $theme_id; ?>/style_str.css?<?php echo time(); ?>" rel="stylesheet" type="text/css" />
        <?php } ?>

        <title><?php echo $langCourseProgram ?></title>
    </head>
    <body class='body-learningPath' style="margin: 0px; height: 100%!important; height: auto;">
        <div id="content" style="width:800px; margin: 0 auto;">
        <?php
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
        ?>
        </div>
    </body>
</html>

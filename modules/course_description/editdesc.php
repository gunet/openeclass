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
 * Edit, Course Description
 *
 */

$require_current_course = TRUE;
$require_login = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'modules/units/functions.php';

$tool_content = $head_content = "";
$nameTools = $langEditCourseProgram;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langCourseProgram);

if (isset($_POST['submit'])) {
    $unit_id = description_unit_id($course_id);
    add_unit_resource($unit_id, 'description', -1, $langDescription, trim($_POST['description']));
    // update index
    require_once 'modules/search/courseindexer.class.php';
    $idx = new CourseIndexer();
    $idx->store($course_id);
}

$description = '';
$unit_id = description_unit_id($course_id);
$q = Database::get()->queryArray("SELECT id, res_id, comments FROM unit_resources WHERE unit_id = ?d
                      AND (res_id < 0 OR `order` < 0)", $unit_id);
if ($q && count($q) > 0) {
    foreach ($q as $row) {
        if ($row->res_id == -1) {
            $description = $row->comments;
        } else {
            $new_order = add_unit_resource_max_order($unit_id);
            $new_res_id = new_description_res_id($unit_id);
            Database::get()->query("UPDATE unit_resources SET
                        res_id = ?d, visibility = 'v', `order` = ?d
                        WHERE id = ?d", $new_res_id, $new_order, $row->id);
        }
    }
}

$tool_content = "
 <form method='post' action='index.php?course=$course_code'>
 <input type='hidden' name='edIdBloc' value='-1' />
 <input type='hidden' name='edTitleBloc' value='$langDescription' />
   <fieldset>
   <legend>$langDescription</legend>
         " . rich_text_editor('edContentBloc', 4, 20, $description) . "
   <br />
   <div class='right'><input class='btn btn-primary' type='submit' name='submit' value='$langSubmit' /></div>
   </fieldset>
 </form>\n";

draw($tool_content, 2, null, $head_content);

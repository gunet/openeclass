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

function display_text_form() {
    global $tool_content, $id, $langContent, $langAdd, $course_code;

    $tool_content .= "<div class='form-wrapper'><form class='form-horizontal' role='form' action='insert.php?course=$course_code' method='post'>
                      <input type='hidden' name='id' value='$id'>";
    $tool_content .= "<fieldset>
        " . rich_text_editor('comments', 4, 20, '') . "
	<br />
        <input class='btn btn-primary' type='submit' name='submit_text' value='$langAdd'>
	</fieldset>
	</form></div>";
}

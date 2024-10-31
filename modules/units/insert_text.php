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

function display_text_form() {
    global $tool_content, $id, $langContent, $langAdd, $course_code, $langForm;

    $tool_content .= "<div class='col-12'><div class='form-wrapper form-edit rounded'><form class='form-horizontal' role='form' action='insert.php?course=$course_code' method='post'>
                      <input type='hidden' name='id' value='$id'>";
    $tool_content .= "<fieldset><legend class='mb-0' aria-label='$langForm'></legend>
        " . rich_text_editor('comments', 4, 20, '') . "
	<br />
        <input class='btn submitAdminBtn' type='submit' name='submit_text' value='$langAdd'>
	</fieldset>
	</form></div></div>";
}

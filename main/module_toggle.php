<?php
/* ========================================================================
 * Open eClass 3.1
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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

$require_current_course = TRUE;
require_once '../include/baseTheme.php';

if ($is_editor and isset($_GET['module_id']) and isset($_POST['hide'])) {
    $eclass_module_id = intval($_GET['module_id']);
    $visible = $_POST['hide'] == '0'? 0: 1;
    Database::get()->query("UPDATE course_module SET visible = ?d
        WHERE module_id = ?d AND course_id = ?d",
        $visible, $eclass_module_id, $course_id);
}

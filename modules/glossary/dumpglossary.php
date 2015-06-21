<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
$require_editor = true;

require_once '../../include/init.php';

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
} else {
    $charset = 'UTF-8';
}
$crlf = "\r\n";

if (!$is_editor) {
    Session::Messages($langForbidden);
    redirect_to_home_page('modules/glossary/index.php?course=' . $course_code);
}

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=glossary.csv");

echo join(';', array_map("csv_escape", array($langGlossaryTerm, $langGlossaryDefinition, $langGlossaryUrl))),
$crlf;
$sql = Database::get()->queryArray("SELECT term, definition, url FROM glossary
                            WHERE course_id = ?d
                            ORDER BY `order`", $course_id);

foreach ($sql as $a) {
    echo join(';', array_map("csv_escape", array($a->term, $a->definition, $a->url)));
    echo "$crlf";   
}    


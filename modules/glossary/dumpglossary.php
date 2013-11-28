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

$require_current_course = TRUE;

require_once '../../include/init.php';

if ($is_editor) {

    if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
        $charset = 'Windows-1253';
    } else {
        $charset = 'UTF-8';
    }
    $crlf = "\r\n";

    header("Content-Type: text/csv; charset=$charset");
    header("Content-Disposition: attachment; filename=glossary.csv");

    echo join(';', array_map("csv_escape", array($langGlossaryTerm, $langGlossaryDefinition, $langGlossaryUrl))),
    $crlf;
    $sql = db_query("SELECT term, definition, url FROM glossary
				WHERE course_id = $course_id
                                ORDER BY `order`", $mysqlMainDb);
    $r = 0;
    while ($r < mysql_num_rows($sql)) {
        $a = mysql_fetch_array($sql);
        echo "$crlf";
        $f = 0;
        while ($f < mysql_num_fields($sql)) {
            echo csv_escape($a[$f]);
            echo ';';
            $f++;
        }
        $r++;
    }
    echo "$crlf";
}

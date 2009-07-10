<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*
Units module: insert new resource
*/

$require_current_course = true;
include '../../include/baseTheme.php';

$tool_content = $head_content = "";
$id = intval($_GET['id']);

$q = db_query("SELECT * FROM course_units
               WHERE id=$id AND course_id=$cours_id");
if (!$q or mysql_num_rows($q) == 0) {
        $nameTools = $langUnitUnknown;
        draw('', 2, 'units');
        exit;
}

$info = mysql_fetch_array($q);
$navigation[] = array("url"=>"index.php?id=$id", "name"=> htmlspecialchars($info['title']));

switch ($_GET['type']) {
        case 'doc': $nameTools = $langInsertMyDocToolName;
                include 'insert_doc.php';
                display_docs();
                break;
        case 'exercise': $nameTools = $langInsertMyExerciseToolName;
                include 'insert_exercise.php';
                display_exercises();
                break;
        case 'text': $nameTools = $langInsertText;
                include 'insert_text.php';
                display_text();
                break;
        default: break;
}

draw($tool_content, 2, 'units', $head_content);

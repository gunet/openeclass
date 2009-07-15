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
include "../../include/lib/fileDisplayLib.inc.php";
$tool_content = $head_content = "";
if ($language == 'greek')
        $lang_editor = 'el';
else
        $lang_editor = 'en';

$head_content .= "<script type='text/javascript'>
_editor_url  = '$urlAppend/include/xinha/';
_editor_lang = '$lang_editor';
</script>
<script type='text/javascript' src='$urlAppend/include/xinha/XinhaCore.js'></script>
<script type='text/javascript' src='$urlAppend/include/xinha/my_config.js'></script>";

$id = intval($_REQUEST['id']);

// Check that the current unit id belongs to the current course
$q = db_query("SELECT * FROM course_units
               WHERE id=$id AND course_id=$cours_id");
if (!$q or mysql_num_rows($q) == 0) {
        $nameTools = $langUnitUnknown;
        draw('', 2, 'units', $head_content);
        exit;
}

if (isset($_POST['submit_doc'])) {
	insert_docs($id);
}
if (isset($_POST['submit_text'])) {
	$title = $_POST['title'];
	$comments = $_POST['comments'];
	insert_text($id);
}
if (isset($_POST['submit_lp'])) {
	//$title = $_POST['title'];
	//$comments = $_POST['comments'];
	insert_lp($id);
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
	case 'lp': $nameTools = $langLearningPaths;
                include 'insert_lp.php';
                display_lp();
                break;
        default: break;
}

draw($tool_content, 2, 'units', $head_content);

// insert docs in database
function insert_docs($id)
{
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	
	foreach ($_POST['document'] as $file_id) {
		$order++;
		$file = mysql_fetch_array(db_query("SELECT * FROM document
			WHERE id =" . intval($file_id), $GLOBALS['currentCourseID']), MYSQL_ASSOC);
		$title = (empty($file['title']))? $file['filename']: $file['title'];
		db_query("INSERT INTO unit_resources SET unit_id=$id, type='doc', title=" .
			 autoquote($title) . ", comments=" . autoquote($file['comment']) .
			 ", visibility='$file[visibility]', `order`=$order, `date`=NOW(), res_id=$file[id]",
			 $GLOBALS['mysqlMainDb']); 
	}
	header('Location: index.php?id=' . $id);
	exit;
}

// insert text in database
function insert_text($id)
{
	global $title, $comments;
	
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	$order++;
	db_query("INSERT INTO unit_resources SET unit_id=$id, type='text', title=" .
			autoquote($title) . ", comments=" . autoquote($comments) .
			", visibility='v', `order`=$order, `date`=NOW(), res_id=0",
			$GLOBALS['mysqlMainDb']);
			
	header('Location: index.php?id=' . $id);
	exit;
}


// insert lp in database
function insert_lp($id)
{
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	
	foreach ($_POST['lp'] as $lp_id) {
		$order++;
		$lp = mysql_fetch_array(db_query("SELECT * FROM lp_learnPath
			WHERE learnPath_id =" . intval($lp_id), $GLOBALS['currentCourseID']), MYSQL_ASSOC);
		if ($lp['visibility'] == 'HIDE') {
			 $visibility = 'i';
		} else {
			$visibility = 'v';
		}
			db_query("INSERT INTO unit_resources SET unit_id=$id, type='lp', title=" .
			autoquote($lp['name']) . ", comments=" . autoquote($lp['comment']) .
			", visibility='$visibility', `order`=$order, `date`=NOW(), res_id=$lp[learnPath_id]",
			$GLOBALS['mysqlMainDb']);
	}
	header('Location: index.php?id=' . $id);
	exit;
}
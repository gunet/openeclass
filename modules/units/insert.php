<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
Units module: insert new resource
*/

$require_current_course = true;
include '../../include/baseTheme.php';
include "../../include/lib/fileDisplayLib.inc.php";
require_once '../video/video_functions.php';
load_modal_box(true);

$lang_editor = langname_to_code($language);

$id = intval($_REQUEST['id']);

// Check that the current unit id belongs to the current course
$q = db_query("SELECT * FROM course_units
               WHERE id=$id AND course_id=$cours_id");
if (!$q or mysql_num_rows($q) == 0) {
        $nameTools = $langUnitUnknown;
        draw('', 2, null, $head_content);
        exit;
}

if (isset($_POST['submit_doc'])) {
	insert_docs($id);
} elseif (isset($_POST['submit_text'])) {
	$comments = $_POST['comments'];
	insert_text($id);
} elseif (isset($_POST['submit_lp'])) {
	insert_lp($id);
} elseif (isset($_POST['submit_video'])) {
        insert_video($id);
} elseif (isset($_POST['submit_exercise'])) {
        insert_exercise($id);
} elseif (isset($_POST['submit_work'])) {
        insert_work($id);
} elseif (isset($_POST['submit_forum'])) {
        insert_forum($id);
} elseif (isset($_POST['submit_wiki'])) {
        insert_wiki($id);
} elseif (isset($_POST['submit_link'])) {
	insert_link($id);
}  elseif (isset($_POST['submit_ebook'])) {
	insert_ebook($id);
}


$info = mysql_fetch_array($q);
$navigation[] = array("url"=>"index.php?course=$code_cours&amp;id=$id", "name"=> htmlspecialchars($info['title']));

switch ($_GET['type']) {
        case 'work': $nameTools = "$langAdd $langInsertWork";
                include 'insert_work.php';
                list_assignments();
                break;
        case 'doc': $nameTools = "$langAdd $langInsertDoc";
                include 'insert_doc.php';
                list_docs();
                break;
        case 'exercise': $nameTools = "$langAdd $langInsertExercise";
                include 'insert_exercise.php';
                list_exercises();
                break;
        case 'text': $nameTools = "$langAdd $langInsertText";
                include 'insert_text.php';
                display_text_form();
                break;
	case 'link': $nameTools = "$langAdd $langInsertLink";
		include 'insert_link.php';
		list_links();
		break;
	case 'lp': $nameTools = "$langAdd $langLearningPath1";
                include 'insert_lp.php';
                list_lps();
                break;
	case 'video': $nameTools = "$langAddV";
                include 'insert_video.php';
                list_videos();
                break;
        case 'ebook': $nameTools = "$langAdd $langInsertEBook";
                include 'insert_ebook.php';
                list_ebooks();
                break;
	case 'forum': $nameTools = "$langAdd $langInsertForum";
                include 'insert_forum.php';
                list_forums();
                break;
        case 'wiki': $nameTools = "$langAdd $langInsertWiki";
                include 'insert_wiki.php';
                list_wikis();
                break;
        default: break;
}

draw($tool_content, 2, null, $head_content);

// insert docs in database
function insert_docs($id)
{
	global $cours_id, $code_cours;

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id = $id"));
	
	foreach ($_POST['document'] as $file_id) {
		$order++;
		$file = mysql_fetch_array(db_query("SELECT * FROM document
			WHERE course_id = $cours_id AND id =" . intval($file_id)), MYSQL_ASSOC);
		$title = (empty($file['title']))? $file['filename']: $file['title'];
		db_query("INSERT INTO unit_resources SET unit_id=$id, type='doc', title=" .
			 quote($title) . ", comments=" . quote($file['comment']) .
			 ", visibility='$file[visibility]', `order`=$order, `date`=NOW(), res_id=$file[id]",
			 $GLOBALS['mysqlMainDb']); 
	}
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}

// insert text in database
function insert_text($id)
{
	global $title, $comments, $code_cours;
	
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	$order++;
	db_query("INSERT INTO unit_resources SET unit_id=$id, type='text', title='', 
		comments=" . autoquote(purify($comments)) . ", visibility='v', `order`=$order, `date`=NOW(), res_id=0",
		$GLOBALS['mysqlMainDb']);
			
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}


// insert lp in database
function insert_lp($id)
{
	global $code_cours;
	
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
			quote($lp['name']) . ", comments=" . quote($lp['comment']) .
			", visibility='$visibility', `order`=$order, `date`=NOW(), res_id=$lp[learnPath_id]",
			$GLOBALS['mysqlMainDb']);
	}
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}

// insert video in database
function insert_video($id)
{
	global $code_cours;
	
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['video'] as $video_id) {
		$order++;
                list($table, $res_id) = explode(':', $video_id);
                $res_id = intval($res_id);
                $table = ($table == 'video')? 'video': 'videolinks';
		$row = mysql_fetch_array(db_query("SELECT * FROM $table
			WHERE id = $res_id", $GLOBALS['currentCourseID']), MYSQL_ASSOC);
                db_query("INSERT INTO unit_resources SET unit_id=$id, type='$table', title=" . quote($row['titre']) . ", comments=" . quote($row['description']) . ", visibility='v', `order`=$order, `date`=NOW(), res_id=$res_id", $GLOBALS['mysqlMainDb']);
	}
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}

// insert work (assignment) in database
function insert_work($id)
{
	global $code_cours;
	
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['work'] as $work_id) {
		$order++;
		$work = mysql_fetch_array(db_query("SELECT * FROM assignments
			WHERE id =" . intval($work_id), $GLOBALS['currentCourseID']), MYSQL_ASSOC);
		if ($work['active'] == '0') {
			 $visibility = 'i';
		} else {
			$visibility = 'v';
		}
		db_query("INSERT INTO unit_resources SET
                                unit_id = $id,
                                type = 'work',
                                title = " . quote($work['title']) . ",
                                comments = " . quote($work['description']) . ",
                                visibility = '$visibility',
                                `order` = $order,
                                `date` = NOW(),
                                res_id = $work[id]",
			 $GLOBALS['mysqlMainDb']); 
	}
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}


// insert exercise in database
function insert_exercise($id)
{
	global $code_cours;
	
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['exercise'] as $exercise_id) {
		$order++;
		$exercise = mysql_fetch_array(db_query("SELECT * FROM exercices
			WHERE id =" . intval($exercise_id), $GLOBALS['currentCourseID']), MYSQL_ASSOC);
		if ($exercise['active'] == '0') {
			 $visibility = 'i';
		} else {
			$visibility = 'v';
		}
		db_query("INSERT INTO unit_resources SET unit_id=$id, type='exercise', title=" .
			quote($exercise['titre']) . ", comments=" . quote($exercise['description']) .
			", visibility='$visibility', `order`=$order, `date`=NOW(), res_id=$exercise[id]",
			$GLOBALS['mysqlMainDb']); 
	}
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}

// insert forum in database
function insert_forum($id)
{
	global $code_cours;
	
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['forum'] as $for_id) {
		$order++;
		$ids = explode(':', $for_id);
		if (count($ids) == 2) {
                        list($forum_id, $topic_id) = $ids;
			$topic = mysql_fetch_array(db_query("SELECT * FROM topics
				WHERE topic_id =" . intval($topic_id) ." AND forum_id =" . intval($forum_id), $GLOBALS['currentCourseID']), MYSQL_ASSOC);
			db_query("INSERT INTO unit_resources SET unit_id=$id, type='topic', title=" .
				quote($topic['topic_title']) .", visibility='v', `order`=$order, `date`=NOW(), res_id=$topic[topic_id]",
			$GLOBALS['mysqlMainDb']);		
		} else {
                        $forum_id = $ids[0];
			$forum = mysql_fetch_array(db_query("SELECT * FROM forums
				WHERE forum_id =" . intval($forum_id), $GLOBALS['currentCourseID']), MYSQL_ASSOC);
			db_query("INSERT INTO unit_resources SET unit_id=$id, type='forum', title=" .
				quote($forum['forum_name']) . ", comments=" . quote($forum['forum_desc']) .
				", visibility='v', `order`=$order, `date`=NOW(), res_id=$forum[forum_id]",
				$GLOBALS['mysqlMainDb']);
		} 
	}
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}


// insert wiki in database
function insert_wiki($id)
{
	global $code_cours;
	
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['wiki'] as $wiki_id) {
		$order++;
		$wiki = mysql_fetch_array(db_query("SELECT * FROM wiki_properties
			WHERE id =" . intval($wiki_id), $GLOBALS['currentCourseID']), MYSQL_ASSOC);
		db_query("INSERT INTO unit_resources SET unit_id=$id, type='wiki', title=" .
			quote($wiki['title']) . ", comments=" . quote($wiki['description']) .
			", visibility='v', `order`=$order, `date`=NOW(), res_id=$wiki[id]",
			$GLOBALS['mysqlMainDb']); 
	}
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}

// insert link in database
function insert_link($id)
{
        global $cours_id, $code_cours;
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	// insert link categories 
        if (isset($_POST['catlink']) and count($_POST['catlink'] > 0)) {
                foreach ($_POST['catlink'] as $catlink_id) {
                        $order++;
                        $sql = db_query("SELECT * FROM link_category WHERE course_id = $cours_id AND id =" . intval($catlink_id));
                        $linkcat = mysql_fetch_array($sql);
                        db_query("INSERT INTO unit_resources SET unit_id = $id, type='linkcategory', title = " .
                                quote($linkcat['name']) . ", comments = " . autoquote($linkcat['description']) .
                                ", visibility='v', `order` = $order, `date` = NOW(), res_id = $linkcat[id]");
                }
        }
	
        if (isset($_POST['link']) and count($_POST['link'] > 0)) {
                foreach ($_POST['link'] as $link_id) {
                        $order++;
                        $link = mysql_fetch_array(db_query("SELECT * FROM link
                                WHERE course_id = $cours_id AND id =" . intval($link_id)), MYSQL_ASSOC);
                        db_query("INSERT INTO unit_resources SET unit_id = $id, type = 'link', title = " .
                                quote($link['title']) . ", comments = " . autoquote($link['description']) .
                                ", visibility='v', `order` = $order, `date` = NOW(), res_id = $link[id]");
                }
	}
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}

// insert ebook in database
function insert_ebook($id)
{
        global $cours_id, $mysqlMainDb, $code_cours;
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id = $id"));
        foreach (array('ebook', 'section', 'subsection') as $type) {
            if (isset($_POST[$type]) and count($_POST[$type]) > 0) {
                    foreach ($_POST[$type] as $ebook_id) {
                            $order++;
                            db_query("INSERT INTO unit_resources SET unit_id = $id, type = '$type',
                                        title = " . autoquote($_POST[$type.'_title'][$ebook_id]) . ", comments = '',
                                        visibility='v', `order` = $order, `date` = NOW(),
                                        res_id = " . intval($ebook_id));
                    }
            }
        }
	header('Location: index.php?course='.$code_cours.'&id=' . $id);
	exit;
}

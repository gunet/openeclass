<?php
/* ========================================================================
 * Open eClass 3.0
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
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/search/courseindexer.class.php';
require_once 'modules/search/documentindexer.class.php';
require_once 'modules/search/unitresourceindexer.class.php';
require_once 'modules/course_metadata/CourseXML.php';
require_once 'include/log.php';

ModalBoxHelper::loadModalBox(true);
$idx = new Indexer();
$cidx = new CourseIndexer($idx);
$didx = new DocumentIndexer($idx);
$urdx = new UnitResourceIndexer($idx);

$id = intval($_REQUEST['id']);
if ($id != -1) {
        // Check that the current unit id belongs to the current course
        $q = db_query("SELECT * FROM course_units
                       WHERE id=$id AND course_id=$course_id");
        if (!$q or mysql_num_rows($q) == 0) {
                $nameTools = $langUnitUnknown;
                draw('', 2, null, $head_content);
                exit;
        }
        $info = mysql_fetch_array($q);
        $navigation[] = array("url"=>"index.php?course=$course_code&amp;id=$id", "name"=> htmlspecialchars($info['title']));
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
        global $webDir, $course_id, $course_code, $cidx, $urdx,
               $group_sql, $subsystem, $subsystem_id, $basedir;

        if ($id == -1) { // Insert common documents into main documents
                $target_dir = '';
                if (isset($_POST['dir']) and !empty($_POST['dir'])) {
                        // Make sure specified target dir exists in course
                        $target_dir = db_query_get_single_value("SELECT path FROM document
                                        WHERE course_id = $course_id AND
                                              subsystem = ".MAIN." AND
                                              path = " . quote($_POST['dir']));
                }

                foreach ($_POST['document'] as $file_id) {
                        $file = mysql_fetch_assoc(db_query("SELECT * FROM document
                                        WHERE course_id = -1
                                        AND subsystem = ".COMMON."
                                        AND id = " . intval($file_id)));
                        if ($file) {
                                $subsystem = MAIN;
                                $subsystem_id = 'NULL';
                                $group_sql = "course_id = $course_id AND subsystem = " . MAIN;
                                $basedir = $webDir . '/courses/' . $course_code . '/document';
                                insert_common_docs($file, $target_dir);
                        }
                }
                header('Location: ../document/index.php?course=' . $course_code .
                        '&openDir=' . $target_dir);
                exit;
        }

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id = $id"));

	foreach ($_POST['document'] as $file_id) {
		$order++;
		$file = mysql_fetch_array(db_query("SELECT * FROM document
			WHERE course_id = $course_id AND id =" . intval($file_id)), MYSQL_ASSOC);
		$title = (empty($file['title']))? $file['filename']: $file['title'];
		db_query("INSERT INTO unit_resources SET unit_id=$id, type='doc', title=" .
			 quote($title) . ", comments=" . quote($file['comment']) .
			 ", visible='$file[visible]', `order`=$order, `date`=NOW(), res_id=$file[id]");
                $uresId = mysql_insert_id();
                $urdx->store($uresId, false);
	}
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}

// insert text in database
function insert_text($id)
{
	global $title, $comments, $course_code, $course_id, $cidx, $urdx;

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	$order++;
	db_query("INSERT INTO unit_resources SET unit_id=$id, type='text', title='',
		comments=" . autoquote(purify($comments)) . ", visible=1, `order`=$order, `date`=NOW(), res_id=0");
        $uresId = mysql_insert_id();
        $urdx->store($uresId, false);
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}


// insert lp in database
function insert_lp($id)
{
	global $course_code, $course_id, $cidx, $urdx;

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['lp'] as $lp_id) {
		$order++;
		$lp = mysql_fetch_array(db_query("SELECT * FROM lp_learnPath
			WHERE course_id = $course_id AND learnPath_id =" . intval($lp_id)), MYSQL_ASSOC);
		if ($lp['visibility'] == 'HIDE') {
			 $visibility = 'i';
		} else {
			$visibility = 'v';
		}
                db_query("INSERT INTO unit_resources SET unit_id=$id, type='lp', title=" .
                    quote($lp['name']) . ", comments=" . quote($lp['comment']) .
                    ", visible='$visibility', `order`=$order, `date`=NOW(), res_id=$lp[learnPath_id]");
                $uresId = mysql_insert_id();
                $urdx->store($uresId, false);
	}
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}

// insert video in database
function insert_video($id)
{
	global $course_code, $course_id, $cidx, $urdx;

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['video'] as $video_id) {
		$order++;
                list($table, $res_id) = explode(':', $video_id);
                $res_id = intval($res_id);
                $table = ($table == 'video')? 'video': 'videolinks';
		$row = mysql_fetch_array(db_query("SELECT * FROM $table
			WHERE course_id = $course_id AND id = $res_id"), MYSQL_ASSOC);
                db_query("INSERT INTO unit_resources SET unit_id=$id, type='$table', title=" . quote($row['title']) . ", comments=" . quote($row['description']) . ", visible=1, `order`=$order, `date`=NOW(), res_id=$res_id");
                $uresId = mysql_insert_id();
                $urdx->store($uresId, false);
	}
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}

// insert work (assignment) in database
function insert_work($id)
{
	global $course_code, $course_id, $cidx, $urdx;

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['work'] as $work_id) {
		$order++;
		$work = mysql_fetch_array(db_query("SELECT * FROM assignment
			WHERE course_id = $course_id AND id =" . intval($work_id)), MYSQL_ASSOC);
		if ($work['active'] == '0') {
			 $visibility = 0;
		} else {
			$visibility = 1;
		}
		db_query("INSERT INTO unit_resources SET
                                unit_id = $id,
                                type = 'work',
                                title = " . quote($work['title']) . ",
                                comments = " . quote($work['description']) . ",
                                visible = '$visibility',
                                `order` = $order,
                                `date` = NOW(),
                                res_id = $work[id]");
                $uresId = mysql_insert_id();
                $urdx->store($uresId, false);
	}
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}


// insert exercise in database
function insert_exercise($id)
{
	global $course_code, $course_id, $cidx, $urdx;

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['exercise'] as $exercise_id) {
		$order++;
		$exercise = mysql_fetch_array(db_query("SELECT * FROM exercise
			WHERE course_id = $course_id AND id = ". intval($exercise_id)), MYSQL_ASSOC);
		if ($exercise['active'] == '0') {
			 $visibility = 0;
		} else {
			$visibility = 1;
		}
		db_query("INSERT INTO unit_resources SET unit_id=$id, type='exercise', title=" .
			quote($exercise['title']) . ", comments=" . quote($exercise['description']) .
			", visible='$visibility', `order`=$order, `date`=NOW(), res_id=$exercise[id]");
                $uresId = mysql_insert_id();
                $urdx->store($uresId, false);
	}
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}

// insert forum in database
function insert_forum($id)
{
	global $course_code, $course_id, $cidx, $urdx;

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['forum'] as $for_id) {
		$order++;
		$ids = explode(':', $for_id);
		if (count($ids) == 2) {
                        list($forum_id, $topic_id) = $ids;
			$topic = mysql_fetch_array(db_query("SELECT * FROM forum_topic
                                        WHERE id =" . intval($topic_id) ."
                                        AND forum_id =" . intval($forum_id)), MYSQL_ASSOC);
			db_query("INSERT INTO unit_resources SET unit_id=$id, type='topic', title=" .
				quote($topic['title']) .", visible=1, `order`=$order, `date`=NOW(), res_id=$topic[id]");
		} else {
                        $forum_id = $ids[0];
			$forum = mysql_fetch_array(db_query("SELECT * FROM forum
                                        WHERE id =" . intval($forum_id) ."
                                        AND course_id = $course_id"), MYSQL_ASSOC);
			db_query("INSERT INTO unit_resources SET unit_id=$id, type='forum', title=" .
				quote($forum['name']) . ", comments=" . quote($forum['desc']) .
				", visible=1, `order`=$order, `date`=NOW(), res_id=$forum[id]");
		}
                $uresId = mysql_insert_id();
                $urdx->store($uresId, false);
	}
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}


// insert wiki in database
function insert_wiki($id)
{
	global $course_code, $course_id, $cidx, $urdx;

	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	foreach ($_POST['wiki'] as $wiki_id) {
		$order++;
		$wiki = mysql_fetch_array(db_query("SELECT * FROM wiki_properties
			WHERE course_id = $course_id AND id =" . intval($wiki_id)), MYSQL_ASSOC);
		db_query("INSERT INTO unit_resources SET unit_id=$id, type='wiki', title=" .
			quote($wiki['title']) . ", comments=" . quote($wiki['description']) .
			", visible=1, `order`=$order, `date`=NOW(), res_id=$wiki[id]");
                $uresId = mysql_insert_id();
                $urdx->store($uresId, false);
	}
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}

// insert link in database
function insert_link($id)
{
        global $course_id, $course_code, $cidx, $urdx;
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$id"));
	// insert link categories
        if (isset($_POST['catlink']) and count($_POST['catlink'] > 0)) {
                foreach ($_POST['catlink'] as $catlink_id) {
                        $order++;
                        $sql = db_query("SELECT * FROM link_category WHERE course_id = $course_id AND id =" . intval($catlink_id));
                        $linkcat = mysql_fetch_array($sql);
                        db_query("INSERT INTO unit_resources SET unit_id = $id, type='linkcategory', title = " .
                                quote($linkcat['name']) . ", comments = " . autoquote($linkcat['description']) .
                                ", visible = 1, `order` = $order, `date` = NOW(), res_id = $linkcat[id]");
                        $uresId = mysql_insert_id();
                        $urdx->store($uresId, false);
                }
        }

        if (isset($_POST['link']) and count($_POST['link'] > 0)) {
                foreach ($_POST['link'] as $link_id) {
                        $order++;
                        $link = mysql_fetch_array(db_query("SELECT * FROM link
                                WHERE course_id = $course_id AND id =" . intval($link_id)), MYSQL_ASSOC);
                        db_query("INSERT INTO unit_resources SET unit_id = $id, type = 'link', title = " .
                                quote($link['title']) . ", comments = " . autoquote($link['description']) .
                                ", visible=1, `order` = $order, `date` = NOW(), res_id = $link[id]");
                        $uresId = mysql_insert_id();
                        $urdx->store($uresId, false);
                }
	}
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}

// insert ebook in database
function insert_ebook($id)
{
        global $course_id, $course_code, $cidx, $urdx;
	list($order) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id = $id"));
        foreach (array('ebook', 'section', 'subsection') as $type) {
            if (isset($_POST[$type]) and count($_POST[$type]) > 0) {
                    foreach ($_POST[$type] as $ebook_id) {
                            $order++;
                            db_query("INSERT INTO unit_resources SET unit_id = $id, type = '$type',
                                        title = " . autoquote($_POST[$type.'_title'][$ebook_id]) . ", comments = '',
                                        visible=1, `order` = $order, `date` = NOW(),
                                        res_id = " . intval($ebook_id));
                            $uresId = mysql_insert_id();
                            $urdx->store($uresId, false);
                    }
            }
        }
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
	header('Location: index.php?course='.$course_code.'&id=' . $id);
	exit;
}

function insert_common_docs($file, $target_dir)
{
        global $course_id, $course_code, $group_sql, $didx;
                
        $common_docs_dir_map = array();

        if ($file['format'] == '.dir') {
                $target_dir = make_path($target_dir, array($file['filename']));
                $r = mysql_fetch_assoc(db_query("SELECT id FROM document WHERE $group_sql AND path = ". autoquote($target_dir)));
                $didx->store($r['id']);
                $common_docs_dir_map[$file['path']] = $target_dir;
                $q = db_query("SELECT * FROM document
                                      WHERE course_id = -1 AND
                                            subsystem = ".COMMON." AND
                                            path LIKE " . quote($file['path'] . '/%') . "
                                      ORDER BY path");
                while ($file = mysql_fetch_assoc($q)) {
                        $new_target_dir = $common_docs_dir_map[dirname($file['path'])];
                        if ($file['format'] == '.dir') {
                                $new_dir = make_path($new_target_dir,
                                                     array($file['filename']));
                                $r2 = mysql_fetch_assoc(db_query("SELECT id FROM document WHERE $group_sql AND path = ". autoquote($new_dir)));
                                $didx->store($r2['id']);
                                $common_docs_dir_map[$file['path']] = $new_dir;
                        } else {
                                insert_common_docs($file, $new_target_dir);
                        }
                }
        } else {
                $path = preg_replace('|^.*/|', $target_dir . '/', $file['path']);
                if ($file['extra_path']) {
                        $extra_path = $file['extra_path'];
                } else {
                        $extra_path = "common:$file[path]";
                }
                db_query("INSERT INTO document SET
                                course_id = $course_id,
                                subsystem = ".MAIN.",
                                path = " . quote($path) . ",
                                extra_path = " . quote($extra_path) . ",
                                filename = " . quote($file['filename']) . ",
                                visible = 1,
                                comment = " . quote($file['comment']) . ",
                                title =	" . quote($file['title']) . ",
                                date = NOW(),
                                date_modified =	NOW(),
                                format = ".quote($file['format'])."");
                $id = mysql_insert_id();
                $didx->store($id);
        }
}

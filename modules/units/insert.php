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

/**
 * @brief Units module: insert new resource
 * @file insert.php
 */



$require_current_course = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/course_metadata/CourseXML.php';
require_once 'include/log.php';

ModalBoxHelper::loadModalBox(true);

$id = intval($_REQUEST['id']);
if ($id != -1) {
    // Check that the current unit id belongs to the current course
    $q = Database::get()->querySingle("SELECT * FROM course_units
                                    WHERE id = ?d AND course_id = ?d", $id, $course_id);
    if (!$q) {
        $pageName = $langUnitUnknown;
        draw('', 2, null, $head_content);
        exit;
    }    
    $navigation[] = array("url" => "index.php?course=$course_code&amp;id=$id", "name" => htmlspecialchars($q->title));
}

$tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "index.php?course=$course_code&amp;id=$id",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));

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
} elseif (isset($_POST['submit_poll'])) {
    insert_poll($id);
} elseif (isset($_POST['submit_wiki'])) {
    insert_wiki($id);
} elseif (isset($_POST['submit_link'])) {
    insert_link($id);
} elseif (isset($_POST['submit_ebook'])) {
    insert_ebook($id);
}


switch ($_GET['type']) {
    case 'work': $pageName = "$langAdd $langInsertWork";
        include 'insert_work.php';
        list_assignments();
        break;
    case 'doc': $pageName = "$langAdd $langInsertDoc";
        include 'insert_doc.php';
        list_docs();
        break;
    case 'exercise': $pageName = "$langAdd $langInsertExercise";
        include 'insert_exercise.php';
        list_exercises();
        break;
    case 'text': $pageName = "$langAdd $langInsertText";
        include 'insert_text.php';
        display_text_form();
        break;
    case 'link': $pageName = "$langAdd $langInsertLink";
        include 'insert_link.php';
        list_links();
        break;
    case 'lp': $pageName = "$langAdd $langLearningPath1";
        include 'insert_lp.php';
        list_lps();
        break;
    case 'video': $pageName = "$langAddV";
        include 'insert_video.php';
        list_videos();
        break;
    case 'ebook': $pageName = "$langAdd $langInsertEBook";
        include 'insert_ebook.php';
        list_ebooks();
        break;
    case 'forum': $pageName = "$langAdd $langInsertForum";
        include 'insert_forum.php';
        list_forums();
        break;
    case 'poll': $pageName = "$langAdd $langInsertPoll";
        include 'insert_poll.php';
        list_polls();
        break;
    case 'wiki': $pageName = "$langAdd $langInsertWiki";
        include 'insert_wiki.php';
        list_wikis();
        break;
    default: break;
}

draw($tool_content, 2, null, $head_content);


/**
 * @brief insert docs in database
 * @global type $webDir
 * @global type $course_id
 * @global type $course_code
 * @global string $group_sql
 * @global string $subsystem
 * @global string $subsystem_id
 * @global string $basedir
 * @param type $id
 */
function insert_docs($id) {
    global $webDir, $course_id, $course_code, $group_sql, $subsystem, $subsystem_id, $basedir;

    if(isset($_POST['document'])){
        if ($id == -1) { // Insert common documents into main documents
            $target_dir = '';
            if (isset($_POST['dir']) and !empty($_POST['dir'])) {
                // Make sure specified target dir exists in course
                $target_dir = Database::get()->querySingle("SELECT path FROM document
                                            WHERE course_id = ?d AND
                                                  subsystem = " . MAIN . " AND
                                                  path = ?s", $course_id, $_POST['dir']->path);
            }

            foreach ($_POST['document'] as $file_id) {
                $file = Database::get()->querySingle("SELECT * FROM document
                                            WHERE course_id = -1
                                            AND subsystem = " . COMMON . "
                                            AND id = ?d", $file_id);
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

        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['document'] as $file_id) {
            $order++;
            $file = Database::get()->querySingle("SELECT * FROM document
                                        WHERE course_id = ?d AND id = ?d", $course_id, $file_id);
            $title = (empty($file->title)) ? $file->filename : $file->title;
            $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='doc', 
                                            title = ?s, comments = ?s, 
                                            visible = ?d, `order` = ?d, 
                                            `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                        $id, $title, $file->comment, $file->visible, $order, $file->id);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}


/**
 * @brief insert text in database
 * @global type $comments
 * @global type $course_code
 * @global type $course_id
 * @param type $id
 */
function insert_text($id) {
    global $comments, $course_code, $course_id;
    if(!empty($comments)){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        $order++;
        $comments = purify($comments);
        $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='text', title='',
                            comments = ?s, visible=1, `order` = ?d, `date`= " . DBHelper::timeAfter() . ", res_id = 0", $id, $comments, $order);
        $uresId = $q->lastInsertID;
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}

/**
 * @brief insert lp in database
 * @global type $course_code
 * @global type $course_id
 * @param type $id
 */
function insert_lp($id) {
    global $course_code, $course_id;
    if(isset($_POST['lp'])) {
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['lp'] as $lp_id) {
            $order++;
            $lp = Database::get()->querySingle("SELECT * FROM lp_learnPath
                            WHERE course_id = ?d AND learnPath_id = ?d", $course_id, $lp_id);
            $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='lp', title = ?s, comments = ?s,
                                            visible = ?d, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d", 
                                    $id, $lp->name, $lp->comment, $lp->visible, $order, $lp->learnPath_id);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}


/**
 * @brief insert video in database
 * @global type $course_code
 * @global type $course_id
 * @param type $id
 */
function insert_video($id) {
    global $course_code, $course_id;

    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
    if (isset($_POST['videocatlink']) and count($_POST['videocatlink'] > 0)) {
        foreach ($_POST['videocatlink'] as $videocatlink_id) {
            $order++;
            $videolinkcat = Database::get()->querySingle("SELECT * FROM video_category WHERE id = ?d AND course_id = ?d", $videocatlink_id, $course_id);
            Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='videolinkcategory', title = ?s,
                        comments = ?s, visible = 1, `order` = $order, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                    $id, $videolinkcat->name, $videolinkcat->description, $videolinkcat->id);
        }
    }
    if (isset($_POST['video']) and count($_POST['video'] > 0)) {    
        foreach ($_POST['video'] as $video_id) {
            $order++;
            list($table, $res_id) = explode(':', $video_id);        
            $table = ($table == 'video') ? 'video' : 'videolink';
            $row = Database::get()->querySingle("SELECT * FROM $table
                            WHERE course_id = ?d AND id = ?d", $course_id, $res_id);
            $q = Database::get()->query("INSERT INTO unit_resources 
                                    SET unit_id = ?d, type = '$table', title = ?s, comments = ?s, visible = 1, `order` = $order, `date` = " . DBHelper::timeAfter() . ", res_id = ?d", 
                                $id, $row->title, $row->description, $res_id);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}


/**
 * @brief insert work (assignment) in database
 * @global type $course_code
 * @global type $course_id
 * @param type $id
 */
function insert_work($id) {
    global $course_code, $course_id;
    if(isset($_POST['work'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['work'] as $work_id) {
            $order++;
            $work = Database::get()->querySingle("SELECT * FROM assignment
                            WHERE course_id = ?d AND id = ?d", $course_id, $work_id);
            if ($work->active == '0') {
                $visibility = 0;
            } else {
                $visibility = 1;
            }
            $q = Database::get()->query("INSERT INTO unit_resources SET
                                    unit_id = ?d,
                                    type = 'work',
                                    title = ?s,
                                    comments = ?s,
                                    visible = ?d,
                                    `order` = ?d,
                                    `date` = " . DBHelper::timeAfter() . ",
                                    res_id = ?d", $id, $work->title, $work->description, $visibility, $order, $work->id);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}


/**
 * @brief insert exercise in database
 * @global type $course_code
 * @global type $course_id
 * @param type $id
 */
function insert_exercise($id) {
    global $course_code, $course_id;
    if(isset($_POST['exercise'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['exercise'] as $exercise_id) {
            $order++;
            $exercise = Database::get()->querySingle("SELECT * FROM exercise
                            WHERE course_id = ?d AND id = ?d", $course_id, $exercise_id);
            if ($exercise->active == '0') {
                $visibility = 0;
            } else {
                $visibility = 1;
            }
            $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='exercise', title = ?s,
                                comments = ?s, visible = ?d, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d", 
                        $id, $exercise->title, $exercise->description, $visibility, $order, $exercise->id);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}


/**
 * @brief insert forum in database
 * @global type $course_code
 * @global type $course_id
 * @param type $id
 */
function insert_forum($id) {
    global $course_code, $course_id;
    if(isset($_POST['forum'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['forum'] as $for_id) {
            $order++;
            $ids = explode(':', $for_id);
            if (count($ids) == 2) {
                list($forum_id, $topic_id) = $ids;
                $topic = Database::get()->querySingle("SELECT * FROM forum_topic
                                            WHERE id = ?d
                                            AND forum_id = ?d", $topic_id, $forum_id);
                $q = Database::get()->query("INSERT INTO unit_resources
                                                SET unit_id = ?d, type = 'topic', title = ?s, visible = 1, `order`= ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d", 
                                            $id, $topic->title, $order, $topic->id);
            } else {
                $forum_id = $ids[0];
                $forum = Database::get()->querySingle("SELECT * FROM forum
                                            WHERE id = ?d
                                            AND course_id = ?d", $forum_id, $course_id);
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'forum', title = ?s,
                                                comments = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d", 
                                        $id, $forum->name, $forum->desc, $order, $forum->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}


/**
 * @brief insert poll in database
 * @global type $course_id
 * @global type $course_code
 * @param type $id
 */
function insert_poll($id) {
    global $course_id, $course_code;
    if(isset($_POST['poll'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['poll'] as $poll_id) {
            $order++;
            $poll = Database::get()->querySingle("SELECT * from poll where course_id = ?d", $course_id);
            $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'poll', 
                                            title = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                        $id, $poll->name, $order, $poll->pid);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;       
}

/**
 * @brief insert wiki in database
 * @global type $course_code
 * @global type $course_id
 * @param type $id
 */
function insert_wiki($id) {
    global $course_code, $course_id;
    if(isset($_POST['wiki'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['wiki'] as $wiki_id) {
            $order++;
            $wiki = Database::get()->querySingle("SELECT * FROM wiki_properties
                            WHERE course_id = ?d AND id = ?d", $course_id, $wiki_id);
            $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='wiki', title = ?s, comments = ?s,
                                            visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d", 
                                        $id, $wiki->title, $wiki->description, $order, $wiki->id);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}


/**
 * @brief insert link in database
 * @global type $course_id
 * @global type $course_code
 * @param type $id
 */
function insert_link($id) {
    global $course_id, $course_code;
    
    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
    // insert link categories
    if (isset($_POST['catlink']) and count($_POST['catlink'] > 0)) {
        foreach ($_POST['catlink'] as $catlink_id) {
            $order++;
            $linkcat = Database::get()->querySingle("SELECT * FROM link_category WHERE course_id = ?d AND id = ?d", $course_id, $catlink_id);
            $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='linkcategory', title = ?s,
                                        comments = ?s, visible = 1, `order` = ?d, `date` = ". DBHelper::timeAfter() . ", res_id = ?d", 
                                $id, $linkcat->name, $linkcat->description, $order, $linkcat->id);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
    }

    if (isset($_POST['link']) and count($_POST['link'] > 0)) {
        foreach ($_POST['link'] as $link_id) {
            $order++;
            $link = Database::get()->querySingle("SELECT * FROM link WHERE course_id = ?d AND id = ?d", $course_id, $link_id);
            $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'link', title = ?s,
                                            comments = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d", 
                                $id, $link->title, $link->description, $order, $link->id);
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
    }
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    CourseXMLElement::refreshCourse($course_id, $course_code);
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}

/**
 * @brief insert ebook in database
 * @global type $course_id
 * @global type $course_code
 * @param type $id
 */
function insert_ebook($id) {
    global $course_id, $course_code;
    
    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
    foreach (array('ebook', 'section', 'subsection') as $type) {
        if (isset($_POST[$type]) and count($_POST[$type]) > 0) {
            foreach ($_POST[$type] as $ebook_id) {
                $order++;
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = '$type',
                                                title = ?s, comments = '', visible=1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ",res_id = ?d", 
                                            $id, $_POST[$type . '_title'][$ebook_id], $order, $ebook_id);
                $uresId = $q->lastInsertID;
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
            }
        }
    }
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    CourseXMLElement::refreshCourse($course_id, $course_code);
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    exit;
}

/**
 * @brief insert common docs
 * @global type $course_id
 * @global type $course_code
 * @global string $group_sql
 * @param type $file
 * @param type $target_dir
 */
function insert_common_docs($file, $target_dir) {
    global $course_id, $course_code, $group_sql;

    $common_docs_dir_map = array();

    if ($file->format == '.dir') {
        $target_dir = make_path($target_dir, array($file->filename));
        $r = Database::get()->querySingle("SELECT id FROM document WHERE $group_sql AND path = ?s", $target_dir);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
        $common_docs_dir_map[$file->path] = $target_dir;
        $q = Database::get()->queryArray("SELECT * FROM document
                                      WHERE course_id = -1 AND
                                            subsystem = " . COMMON . " AND
                                            path LIKE ?s
                                      ORDER BY path", $file->path . '/%');
        foreach ($q as $file) {
            $new_target_dir = $common_docs_dir_map[dirname($file->path)];
            if ($file->format == '.dir') {
                $new_dir = make_path($new_target_dir, array($file->filename));
                $r2 = Database::get()->querySingle("SELECT id FROM document WHERE $group_sql AND path = ?s", $new_dir);
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r2->id);
                $common_docs_dir_map[$file->path] = $new_dir;
            } else {
                insert_common_docs($file, $new_target_dir);
            }
        }
    } else {
        $path = preg_replace('|^.*/|', $target_dir . '/', $file->path);
        if ($file->extra_path) {
            $extra_path = $file->extra_path;
        } else {
            $extra_path = "common:$file->path";
        }
        $q = Database::get()->query("INSERT INTO document SET
                                course_id = ?d,
                                subsystem = " . MAIN . ",
                                path = ?s,
                                extra_path = ?s,
                                filename = ?s,
                                visible = 1,
                                comment = ?s,
                                title =	?s,
                                date = " . DBHelper::timeAfter() . ",
                                date_modified =	" . DBHelper::timeAfter() . ",
                                format = ?s", $course_id, $path, $extra_path, $file->filename, $file->comment, $file->title, $file->format);
        $id = $q->lastInsertID;
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $id);
    }
}

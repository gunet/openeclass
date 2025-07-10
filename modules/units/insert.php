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
require_once 'modules/search/lucene/indexer.class.php';
require_once 'modules/course_metadata/CourseXML.php';
require_once 'include/log.class.php';

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
    $navigation[] = array('url' => "index.php?course=$course_code&id=$id", "name" => $q->title);
    $backURL = "index.php?course=$course_code&amp;id=$id";
} else {
    // id=-1 means we came from common documents insert button
    $backURL = $urlAppend . "modules/document/index.php?course=$course_code&amp;openDir=" . $_GET['dir'];
}

$fc_type = $act_name ="";


if(isset($_GET['fc_type'])){

    $fc_type = $_GET['fc_type'];
    $_SESSION['fc_type'] =$fc_type ;

}
if(isset($_GET['act_name'])){
    $act_name = $_GET['act_name'];
    $_SESSION['act_name'] =$act_name;

}
if(isset($_GET['act_id'])){
    $act_id = $_GET['act_id'];
    $_SESSION['act_id'] =$act_id;

}


if (isset($_POST['submit_doc'])) {
    insert_docs($id);
} elseif (isset($_POST['submit_text'])) {
    $comments = $_POST['comments'];
    insert_text($id);
} elseif (isset($_POST['submit_lp'])) {
    insert_lp($id);
} elseif (isset($_POST['submit_h5p'])) {
    insert_h5p($id);
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
} elseif (isset($_POST['submit_chat'])) {
    insert_chat($id);
} elseif (isset($_POST['submit_blog'])) {
    insert_blog($id);
} elseif (isset($_POST['submit_tc'])) {
    insert_tc($id);
}
switch ($_GET['type']) {
    case 'divider';
        insert_divider($id);
        break;
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
    case 'chat': $pageName = "$langAdd $langInsertChat";
        include "insert_chat.php";
        list_chats();
        break;
    case 'blog': $pageName = "$langAdd $langInsertBlog";
        include "insert_blog.php";
        list_blogs();
        break;
    case 'tc': $pageName = "$langAdd $langInsertTcMeeting";
        include "insert_tc.php";
        list_tcs();
        break;
    case 'h5p': $pageName = "$langAdd $langOfH5p";
        include "insert_h5p.php";
        list_h5p();
        break;
    default: break;
}

draw($tool_content, 2, null, $head_content);


/**
 * @brief insert docs in database
 * @param integer $id
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
            unset($_SESSION['fc_type']);
            unset($_SESSION['act_name']);
            unset($_SESSION['act_id']);
            exit;
        }

        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['document'] as $file_id) {
            $order++;
            $file = Database::get()->querySingle("SELECT * FROM document
                                        WHERE course_id = ?d AND id = ?d", $course_id, $file_id);
            $title = (empty($file->title)) ? $file->filename : $file->title;
            if (empty($file->comment)) {
                $comment = '';
            } else {
                $comment = $file->comment;
            }
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='doc',
                                                title = ?s, comments = ?s,
                                                visible = 1, `order` = ?d,
                                                `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                            $id, $title, $comment, $order, $file->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='doc',
                                                title = ?s, comments = ?s,
                                                visible = 1, `order` = ?d,
                                                `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                            $id, $title, $comment, $order, $file->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}


/**
 * @brief insert text in database
 * @param integer $id
 */
function insert_text($id) {
    global $comments, $course_code, $course_id;
    if(!empty($comments)){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        $order++;
        $comments = purify($comments);
        if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
            $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='text', title='',
                                comments = ?s, visible=1, `order` = ?d, `date`= " . DBHelper::timeAfter() . ", res_id = 0,fc_type=?d,activity_title=?s,activity_id=?s", $id, $comments, $order,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
        }else{
            $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='text', title='',
                                comments = ?s, visible=1, `order` = ?d, `date`= " . DBHelper::timeAfter() . ", res_id = 0", $id, $comments, $order);
        }
        $uresId = $q->lastInsertID;
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}

/**
 * @brief insert divider in database
 * @param integer $id
 */
function insert_divider($id) {
    global $comments, $course_code, $course_id;

    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
    $order++;
//    $divider = purify($comments);
    $divider = '<div class="unit-divider"></div>';
    if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
        $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='text', title='',
                            comments = ?s, visible=1, `order` = ?d, `date`= " . DBHelper::timeAfter() . ", res_id = 0,fc_type=?d,activity_title=?s,activity_id=?s", $id, $divider, $order,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
    }else{
        $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='text', title='',
                            comments = ?s, visible=1, `order` = ?d, `date`= " . DBHelper::timeAfter() . ", res_id = 0", $id, $divider, $order);
    }
    $uresId = $q->lastInsertID;
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    CourseXMLElement::refreshCourse($course_id, $course_code);

    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}

/**
 * @brief insert lp in database
 * @param integer $id
 */
function insert_lp($id) {
    global $course_code, $course_id;
    if(isset($_POST['lp'])) {
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['lp'] as $lp_id) {
            $order++;
            $lp = Database::get()->querySingle("SELECT * FROM lp_learnPath
                            WHERE course_id = ?d AND learnPath_id = ?d", $course_id, $lp_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='lp', title = ?s, comments = ?s,
                                                visible = ?d, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                        $id, $lp->name, $lp->comment, $lp->visible, $order, $lp->learnPath_id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='lp', title = ?s, comments = ?s,
                visible = ?d, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                    $id, $lp->name, $lp->comment, $lp->visible, $order, $lp->learnPath_id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}

/**
 * @brief insert h5p in database
 * @param $id
 */
function insert_h5p($id) {
    global $course_code, $course_id;

    if(isset($_POST['h5p'])) {
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['h5p'] as $h5p_id) {
            $order++;
            $h5p = Database::get()->querySingle("SELECT * FROM h5p_content WHERE course_id = ?d AND id = ?d", $course_id, $h5p_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='h5p', title = ?s, comments = '',
                                            visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                    $id, $h5p->title, $order, $h5p->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='h5p', title = ?s, comments = '',
                                            visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                    $id, $h5p->title, $order, $h5p->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}

/**
 * @brief insert video in database
 * @param integer $id
 */
function insert_video($id) {
    global $course_code, $course_id;

    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
    if (isset($_POST['videocatlink']) and count($_POST['videocatlink']) > 0) {
        foreach ($_POST['videocatlink'] as $videocatlink_id) {
            $order++;
            $videolinkcat = Database::get()->querySingle("SELECT * FROM video_category WHERE id = ?d AND course_id = ?d", $videocatlink_id, $course_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='videolinkcategory', title = ?s,
                            comments = ?s, visible = 1, `order` = $order, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                        $id, $videolinkcat->name, $videolinkcat->description, $videolinkcat->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='videolinkcategory', title = ?s,
                            comments = ?s, visible = 1, `order` = $order, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                        $id, $videolinkcat->name, $videolinkcat->description, $videolinkcat->id);
            }
        }
    }
    if (isset($_POST['video']) and count($_POST['video']) > 0) {
        foreach ($_POST['video'] as $video_id) {
            $order++;
            list($table, $res_id) = explode(':', $video_id);
            $table = ($table == 'video') ? 'video' : 'videolink';
            $row = Database::get()->querySingle("SELECT * FROM $table
                            WHERE course_id = ?d AND id = ?d", $course_id, $res_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources
                                        SET unit_id = ?d, type = '$table', title = ?s, comments = ?s, visible = 1, `order` = $order, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                    $id, $row->title, $row->description, $res_id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources
                                        SET unit_id = ?d, type = '$table', title = ?s, comments = ?s, visible = 1, `order` = $order, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                    $id, $row->title, $row->description, $res_id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}


/**
 * @brief insert work (assignment) in database
 * @param integer $id
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
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources SET
                                        unit_id = ?d,
                                        type = 'work',
                                        title = ?s,
                                        comments = ?s,
                                        visible = ?d,
                                        `order` = ?d,
                                        `date` = " . DBHelper::timeAfter() . ",
                                        res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s", $id, $work->title, $work->description, $visibility, $order, $work->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources SET
                                        unit_id = ?d,
                                        type = 'work',
                                        title = ?s,
                                        comments = ?s,
                                        visible = ?d,
                                        `order` = ?d,
                                        `date` = " . DBHelper::timeAfter() . ",
                                        res_id = ?d", $id, $work->title, $work->description, $visibility, $order, $work->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}


/**
 * @brief insert exercise in database
 * @param integer $id
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
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='exercise', title = ?s,
                                    comments = ?s, visible = ?d, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                            $id, $exercise->title, $exercise->description, $visibility, $order, $exercise->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='exercise', title = ?s,
                                    comments = ?s, visible = ?d, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                            $id, $exercise->title, $exercise->description, $visibility, $order, $exercise->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}


/**
 * @brief insert forum in database
 * @param integer $id
 */
function insert_forum($id) {
    global $course_code, $course_id;

    if (isset($_POST['forum'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['forum'] as $for_id) {
            $order++;
            $ids = explode(':', $for_id);
            if (count($ids) == 2) {
                list($forum_id, $topic_id) = $ids;
                $topic = Database::get()->querySingle("SELECT * FROM forum_topic
                                            WHERE id = ?d
                                            AND forum_id = ?d", $topic_id, $forum_id);
                if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                    $q = Database::get()->query("INSERT INTO unit_resources
                                                SET unit_id = ?d, type = 'topic', title = ?s, visible = 1, `order`= ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s, comments = ''",
                                            $id, $topic->title, $order, $topic->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
                }else{
                    $q = Database::get()->query("INSERT INTO unit_resources
                                                SET unit_id = ?d, type = 'topic', title = ?s, visible = 1, `order`= ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d, comments = ''",
                                            $id, $topic->title, $order, $topic->id);
                }
            } else {
                $forum_id = $ids[0];
                $forum = Database::get()->querySingle("SELECT * FROM forum
                                            WHERE id = ?d
                                            AND course_id = ?d", $forum_id, $course_id);
                if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                    $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'forum', title = ?s,
                                                    comments = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                            $id, $forum->name, q($forum->desc), $order, $forum->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
                }else{
                    $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'forum', title = ?s,
                                                    comments = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                            $id, $forum->name, q($forum->desc), $order, $forum->id);
                }
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}


/**
 * @brief insert poll in database
 * @param integer $id
 */
function insert_poll($id) {
    global $course_id, $course_code;
    if(isset($_POST['poll'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['poll'] as $poll_id) {
            $order++;
            $poll = Database::get()->querySingle("SELECT * FROM poll where course_id = ?d AND pid = ?d", $course_id, $poll_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'poll', comments = '',
                                            title = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                        $id, $poll->name, $order, $poll->pid,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'poll', comments = '',
                title = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
            $id, $poll->name, $order, $poll->pid);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}

/**
 * @brief insert wiki in database
 * @param integer $id
 */
function insert_wiki($id) {
    global $course_code, $course_id;
    if(isset($_POST['wiki'])){

        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['wiki'] as $wiki_id) {
            $order++;
            $wiki = Database::get()->querySingle("SELECT * FROM wiki_properties
                            WHERE course_id = ?d AND id = ?d", $course_id, $wiki_id);
            if (isset($_SESSION['fc_type'])&&isset($_SESSION['act_name'])){
                $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='wiki', title = ?s, comments = ?s,
                                                visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                            $id, $wiki->title, $wiki->description, $order, $wiki->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='wiki', title = ?s, comments = ?s,
                                                visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                            $id, $wiki->title, $wiki->description, $order, $wiki->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}


/**
 * @brief insert link in database
 * @param integer $id
 */
function insert_link($id) {
    global $course_id, $course_code;

    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
    // insert link categories
    if (isset($_POST['catlink']) and count($_POST['catlink']) > 0) {
        foreach ($_POST['catlink'] as $catlink_id) {
            $order++;
            $linkcat = Database::get()->querySingle("SELECT * FROM link_category WHERE course_id = ?d AND id = ?d", $course_id, $catlink_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='linkcategory', title = ?s,
                                        comments = ?s, visible = 1, `order` = ?d, `date` = ". DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                $id, $linkcat->name, $linkcat->description, $order, $linkcat->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='linkcategory', title = ?s,
                                        comments = ?s, visible = 1, `order` = ?d, `date` = ". DBHelper::timeAfter() . ", res_id = ?d",
                                $id, $linkcat->name, $linkcat->description, $order, $linkcat->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
    }

    if (isset($_POST['link']) and count($_POST['link']) > 0) {
        foreach ($_POST['link'] as $link_id) {
            $order++;
            $link = Database::get()->querySingle("SELECT * FROM link WHERE course_id = ?d AND id = ?d", $course_id, $link_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'link', title = ?s,
                                            comments = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                $id, $link->title, $link->description, $order, $link->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = 'link', title = ?s,
                                            comments = ?s, visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                $id, $link->title, $link->description, $order, $link->id);
            }

            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
    }
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    CourseXMLElement::refreshCourse($course_id, $course_code);
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}

/**
 * @brief insert ebook in database
 * @param integer $id
 */
function insert_ebook($id) {
    global $course_id, $course_code;

    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
    foreach (array('ebook', 'section', 'subsection') as $type) {
        if (isset($_POST[$type]) and count($_POST[$type]) > 0) {
            foreach ($_POST[$type] as $ebook_id) {
                $order++;
                if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                    $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = '$type',
                                                    title = ?s, comments = '', visible=1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ",res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                                                $id, $_POST[$type . '_title'][$ebook_id], $order, $ebook_id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
                }else{
                    $q = Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type = '$type',
                    title = ?s, comments = '', visible=1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ",res_id = ?d",
                    $id, $_POST[$type . '_title'][$ebook_id], $order, $ebook_id);
                }
                $uresId = $q->lastInsertID;
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
            }
        }
    }
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    CourseXMLElement::refreshCourse($course_id, $course_code);
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}


/**
 * @brief insert chat resource in course units resources
 * @param integer $id
 */
function insert_chat($id) {

    global $course_code, $course_id;
    if(isset($_POST['chat'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['chat'] as $chat_id) {
            $order++;
            $chat = Database::get()->querySingle("SELECT * FROM conference
                            WHERE course_id = ?d AND conf_id = ?d", $course_id, $chat_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='chat', title = ?s, comments = ?s,
                                                visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                    $id, $chat->conf_title, $chat->conf_description, $order, $chat->conf_id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='chat', title = ?s, comments = ?s,
                                                visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                    $id, $chat->conf_title, $chat->conf_description, $order, $chat->conf_id);
            }

            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}


/**
 * @brief insert blog resource in course units resources
 * @param integer $id
 */
function insert_blog($id) {

    global $course_code, $course_id;
    if(isset($_POST['blog'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['blog'] as $blog_id) {
            $order++;
            $blog = Database::get()->querySingle("SELECT * FROM blog_post WHERE course_id = ?d AND id = ?d", $course_id, $blog_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='blog', title = ?s, comments = ?s,
                                            visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                    $id, $blog->title, $blog->content, $order, $blog->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='blog', title = ?s, comments = ?s,
                                            visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                    $id, $blog->title, $blog->content, $order, $blog->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;

}

/**
 * @brief insert tc resource in course units resources
 * @param integer $id
 */
function insert_tc($id) {
    global $course_code, $course_id;

    if(isset($_POST['tc'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM unit_resources WHERE unit_id = ?d", $id)->maxorder;
        foreach ($_POST['tc'] as $tc_id) {
            $order++;
            $tc = Database::get()->querySingle("SELECT * FROM tc_session
                            WHERE course_id = ?d AND id = ?d", $course_id, $tc_id);
            if(isset($_SESSION['fc_type']) && isset($_SESSION['act_name'])){
                $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='tc', title = ?s, comments = ?s,
                                                visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d,fc_type=?d,activity_title=?s,activity_id=?s",
                    $id, $tc->title, $tc->description, $order, $tc->id,$_SESSION['fc_type'],$_SESSION['act_name'],$_SESSION['act_id']);
            }else{
                $q =  Database::get()->query("INSERT INTO unit_resources SET unit_id = ?d, type='tc', title = ?s, comments = ?s,
                visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                    $id, $tc->title, $tc->description, $order, $tc->id);
            }
            $uresId = $q->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNITRESOURCE, $uresId);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    }
    header('Location: index.php?course=' . $course_code . '&id=' . $id);
    unset($_SESSION['fc_type']);
    unset($_SESSION['act_name']);
    unset($_SESSION['act_id']);
    exit;
}




/**
 * @brief insert common docs
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

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

$require_departmentmanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'modules/create_course/functions.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/pclzip/pclzip.lib.php';
require_once 'include/phpass/PasswordHash.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'restore_functions.php';
require_once 'restorehelper.class.php';

$treeObj = new Hierarchy();
$courseObj = new Course();

load_js('jquery');
load_js('jquery-ui');
load_js('jstree');

list($js, $html) = $treeObj->buildCourseNodePicker();
$head_content .= $js;

$nameTools = $langRestoreCourse;
$navigation[] = array('url' => '../admin/index.php', 'name' => $langAdmin);

// Default backup version
if (isset($_FILES['archiveZipped']) and $_FILES['archiveZipped']['size'] > 0) {

    validateUploadedFile($_FILES['archiveZipped']['name'], 3);

    $tool_content .= "<fieldset>
        <legend>" . $langFileSent . "</legend>
        <table class='tbl' width='100%'>
                   <tr><th width='150'>$langFileSentName</td><td>" . $_FILES['archiveZipped']['name'] . "</th></tr>
                   <tr><th>$langFileSentSize</td><td>" . $_FILES['archiveZipped']['size'] . "</th></tr>
                   <tr><th>$langFileSentType</td><td>" . $_FILES['archiveZipped']['type'] . "</th></tr>
                   <tr><th>$langFileSentTName</td><td>" . $_FILES['archiveZipped']['tmp_name'] . "</th></tr>
                </table></fieldset>
                        <fieldset>
        <legend>" . $langFileUnzipping . "</legend>
        <table class='tbl' width='100%'>
                    <tr><td>" . unpack_zip_show_files($_FILES['archiveZipped']['tmp_name']) . "</td></tr>
                </table></fieldset>";
} elseif (isset($_POST['send_path']) and isset($_POST['pathToArchive'])) {
    $pathToArchive = $_POST['pathToArchive'];
    if (file_exists($pathToArchive)) {
        $tool_content .= "<fieldset>
        <legend>" . $langFileUnzipping . "</legend>
        <table class='tbl' width='100%'>";
        $tool_content .= "<tr><td>" . unpack_zip_show_files($pathToArchive) . "</td></tr>";
        $tool_content .= "</table></fieldset>";
    } else {
        $tool_content .= "<p class='caution'>$langFileNotFound</p>";
    }
} elseif (isset($_POST['create_restored_course'])) {
    register_posted_variables(array('restoreThis' => true,
        'course_code' => true,
        'course_lang' => true,
        'course_title' => true,
        'course_desc' => true,
        'course_vis' => true,
        'course_prof' => true), 'all', 'autounquote');

    $departments = array();
    if (isset($_POST['department'])) {
        foreach ($_POST['department'] as $did) {
            $departments[] = intval($did);
        }
    } else {
        $minDep = Database::get()->querySingle("SELECT MIN(id) AS min FROM hierarchy");
        if ($minDep) {
            $departments[0] = $minDep->min;
        }
    }

    $r = $restoreThis . '/html';
    list($new_course_code, $course_id) = create_course($course_code, $course_lang, $course_title, $departments, $course_vis, $course_prof);
    if (!$new_course_code) {
        $tool_content = "<p class='alert1'>$langError</p>";
        draw($tool_content, 3);
        exit;
    }

    $config_data = unserialize(file_get_contents($restoreThis . '/config_vars'));
    // If old $urlAppend didn't end in /, add it
    if (substr($config_data['urlAppend'], -1) !== '/') {
        $config_data['urlAppend'] .= '/';
    }
    $eclass_version = (isset($config_data['version'])) ? $config_data['version'] : null;
    if (file_exists($restoreThis . '/backup.php')) {
        $backupData = parse_backup_php($restoreThis . '/backup.php');
        $eclass_version = $backupData['eclass_version'];
    }
    $restoreHelper = new RestoreHelper($eclass_version);

    $course_file = $restoreThis . '/' . $restoreHelper->getFile('course');
    if (file_exists($course_file)) {
        $course_data = unserialize(file_get_contents($course_file));
        $course_data = $course_data[0];
        Database::get()->query("UPDATE course SET keywords = ?s, doc_quota = ?f, video_quota = ?f, "
                . " group_quota = ?f, dropbox_quota = ?f, glossary_expand = ?d WHERE id = ?d", 
                $course_data[$restoreHelper->getField('course', 'keywords')], 
                floatval($course_data['doc_quota']), 
                floatval($course_data['video_quota']), 
                floatval($course_data['group_quota']), 
                floatval($course_data['dropbox_quota']), 
                intval($course_data[$restoreHelper->getField('course', 'glossary_expand')]), 
                intval($course_id));
    }

    $userid_map = array();
    $user_file = $restoreThis . '/user';
    if (file_exists($user_file)) {
        $cours_user = unserialize(file_get_contents($restoreThis . '/' . $restoreHelper->getFile('course_user')));
        $userid_map = restore_users(unserialize(file_get_contents($user_file)), $cours_user, $departments);
        register_users($course_id, $userid_map, $cours_user);
    }
    $userid_map[0] = 0;
    $userid_map[-1] = -1;

    $coursedir = "${webDir}/courses/$new_course_code";
    $videodir = "${webDir}/video/$new_course_code";
    move_dir($r, $coursedir);
    if (is_dir($restoreThis . '/video_files')) {
        move_dir($restoreThis . '/video_files', $videodir);
    }
    course_index($new_course_code);
    $tool_content .= "<p>$langCopyFiles $coursedir</p>";

    require_once 'upgrade/functions.php';
    load_global_messages();

    $url_prefix_map = array(
        $config_data['urlServer'] . 'modules/ebook/show.php/' . $course_data['code'] =>
        $urlServer . 'modules/ebook/show.php/' . $new_course_code,
        $config_data['urlAppend'] . 'modules/ebook/show.php/' . $course_data['code'] =>
        $urlAppend . 'modules/ebook/show.php/' . $new_course_code,
        $config_data['urlServer'] . 'modules/document/file.php/' . $course_data['code'] =>
        $urlServer . 'modules/document/file.php/' . $new_course_code,
        $config_data['urlAppend'] . 'modules/document/file.php/' . $course_data['code'] =>
        $urlAppend . 'modules/document/file.php/' . $new_course_code,
        $config_data['urlServer'] . 'courses/' . $course_data['code'] =>
        $urlServer . 'courses/' . $new_course_code,
        $config_data['urlAppend'] . 'courses/' . $course_data['code'] =>
        $urlAppend . 'courses/' . $new_course_code,
        $course_data['code'] =>
        $new_course_code);

    if ($restoreHelper->getBackupVersion() === RestoreHelper::STYLE_3X) {
        restore_table($restoreThis, 'course_module', array('set' => array('course_id' => $course_id), 'delete' => array('id')));
    } else if ($restoreHelper->getBackupVersion() === RestoreHelper::STYLE_2X) {
        create_modules($course_id);
    }
    restore_table($restoreThis, 'announcement', array('set' => array('course_id' => $course_id), 'delete' => array('id', 'preview')));
    restore_table($restoreThis, 'group_properties', array('set' => array('course_id' => $course_id)));
    $group_map = restore_table($restoreThis, 'group', array('set' => array('course_id' => $course_id), 'return_mapping' => 'id'));
    restore_table($restoreThis, 'group_members', array('map' => array('group_id' => $group_map, 'user_id' => $userid_map)));

    // Forums Restore
    $forum_category_map = restore_table($restoreThis, 'forum_category', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'id'));
    $forum_category_map[0] = 0;
    $forum_map = restore_table($restoreThis, 'forum', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'id', 'map' => array('cat_id' => $forum_category_map)));
    $forum_map[0] = 0;
    $forum_topic_map = restore_table($restoreThis, 'forum_topic', array('return_mapping' => 'id',
        'map' => array('forum_id' => $forum_map, 'poster_id' => $userid_map)));
    $forum_topic_map[0] = 0;
    $forum_post_map = restore_table($restoreThis, 'forum_post', array('return_mapping' => 'id',
        'map' => array('topic_id' => $forum_topic_map, 'poster_id' => $userid_map)));
    $forum_post_map[0] = 0;
    restore_table($restoreThis, 'forum_notify', array('set' => array('course_id' => $course_id),
        'map' => array('user_id' => $userid_map, 'cat_id' => $forum_category_map, 'forum_id' => $forum_map, 'topic_id' => $forum_topic_map),
        'delete' => array('id')));
    restore_table($restoreThis, 'forum_user_stats', array('set' => array('course_id' => $course_id),
    'map' => array('user_id' => $userid_map)));
    if ($restoreHelper->getBackupVersion() === RestoreHelper::STYLE_2X 
            && isset($backupData) && is_array($backupData) 
            && isset($backupData['query']) && is_array($backupData['query'])) {
        $postsText = get_tabledata_from_parsed('posts_text');
        foreach ($postsText as $ptData) {
            if (array_key_exists($ptData['post_id'], $forum_post_map)) {
                Database::get()->query("UPDATE forum_post SET post_text = ?s WHERE id = ?d", $ptData['post_text'], intval($forum_post_map[$ptData['post_id']]));
            }
        }
    }

    $forumLastPosts = Database::get()->queryArray("SELECT DISTINCT last_post_id FROM forum WHERE course_id = ?d ", intval($course_id));
    if (is_array($forumLastPosts) && count($forumLastPosts) > 0) {
        foreach ($forumLastPosts as $lastPost) {
            if (isset($forum_post_map[$lastPost->last_post_id])) {
                Database::get()->query("UPDATE forum SET last_post_id = ?d WHERE course_id = ?d AND last_post_id = ?d", intval($forum_post_map[$lastPost->last_post_id]), intval($course_id), intval($lastPost->last_post_id));
            }
        }
    }

    $topicLastPosts = Database::get()->queryArray("SELECT DISTINCT last_post_id FROM forum_topic WHERE forum_id IN (SELECT id FROM forum WHERE course_id = ?d)", intval($course_id));
    if (is_array($topicLastPosts) && count($topicLastPosts) > 0) {
        foreach ($topicLastPosts as $lastPost) {
            if (isset($forum_post_map[$lastPost->last_post_id])) {
                Database::get()->query("UPDATE forum_topic SET last_post_id = ?d WHERE last_post_id = ?d", intval($forum_post_map[$lastPost->last_post_id]), intval($lastPost->last_post_id));
            }
        }
    }

    $parentPosts = Database::get()->queryArray("SELECT DISTINCT parent_post_id FROM forum_post WHERE topic_id IN (SELECT id FROM forum_topic WHERE forum_id IN (SELECT id FROM forum WHERE course_id = ?d))", intval($course_id));
    if (is_array($parentPosts) && count($parentPosts) > 0) {
        foreach ($parentPosts as $parentPost) {
            if (isset($forum_post_map[$parentPost->parent_post_id])) {
                Database::get()->query("UPDATE forum_post SET parent_post_id = ?d WHERE parent_post_id = ?d", intval($forum_post_map[$parentPost->parent_post_id]), intval($parentPost->parent_post_id));
            }
        }
    }
    // Forums Restore End

    // Glossary Restore
    $glossary_category_map = restore_table($restoreThis, 'glossary_category', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'id'));
    $glossary_category_map[0] = 0;
    restore_table($restoreThis, 'glossary', array('set' => array('course_id' => $course_id),
        'delete' => array('id'), 'map' => array('category_id' => $glossary_category_map)));
    // Glossary Restore End

    $link_category_map = restore_table($restoreThis, 'link_category', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'id'));
    $link_category_map[0] = 0;
    $link_map = restore_table($restoreThis, 'link', array('set' => array('course_id' => $course_id),
        'map' => array('category' => $link_category_map), 'return_mapping' => 'id'));
    $ebook_map = restore_table($restoreThis, 'ebook', array('set' => array('course_id' => $course_id), 'return_mapping' => 'id'));
    foreach ($ebook_map as $old_id => $new_id) {
        // new and old id might overlap as the map contains multiple values!
        rename("$coursedir/ebook/$old_id", "$coursedir/ebook/__during_restore__$new_id");
    }
    foreach ($ebook_map as $old_id => $new_id) {
        // better to use an intermediary rename step
        rename("$coursedir/ebook/__during_restore__$new_id", "$coursedir/ebook/$new_id");
    }
    $document_map = restore_table($restoreThis, 'document', array('set' => array('course_id' => $course_id),
        'map_function' => 'document_map_function',
        'map_function_data' => array(1 => $group_map, 2 => $ebook_map),
        'return_mapping' => 'id'));
    $ebook_section_map = restore_table($restoreThis, 'ebook_section', array('map' => array('ebook_id' => $ebook_map),
        'return_mapping' => 'id'));
    $ebook_subsection_map = restore_table($restoreThis, 'ebook_subsection', array('map' => array('section_id' => $ebook_section_map,
        'file_id' => $document_map), 'delete' => array('file'), 'return_mapping' => 'id'));
    
    // Video
    $video_map = restore_table($restoreThis, 'video', array('set' => array('course_id' => $course_id), 'return_mapping' => 'id'));
    $videolink_map = restore_table($restoreThis, 'videolink', array('set' => array('course_id' => $course_id), 'return_mapping' => 'id'));
    
    // Dropbox
    $dropbox_map = restore_table($restoreThis, 'dropbox_msg', array('set' => array('course_id' => $course_id),
            'map' => array('author_id' => $userid_map), 'return_mapping' => 'id'));
    restore_table($restoreThis, 'dropbox_attachment', array('map' => array('msg_id' => $dropbox_map), 'return_mapping' => 'id'));
    restore_table($restoreThis, 'dropbox_index', array('map' => array('msg_id' => $dropbox_map, 'recipient_id' => $userid_map)));
    
    // Learning Path
    $lp_learnPath_map = restore_table($restoreThis, 'lp_learnPath', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'learnPath_id'));
    $lp_module_map = restore_table($restoreThis, 'lp_module', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'module_id'));
    $lp_asset_map = restore_table($restoreThis, 'lp_asset', array('map' => array('module_id' => $lp_module_map),
        'return_mapping' => 'asset_id'));
    // update lp_module startAsset_id with new asset_id from map
    foreach ($lp_asset_map as $key => $value) {
        Database::get()->query("UPDATE lp_module SET `startAsset_id` = ?d "
                . "WHERE `course_id` = ?d "
                . "AND `startAsset_id` = ?d", intval($value), intval($course_id), intval($key));
    }
    $lp_rel_learnPath_module_map = restore_table($restoreThis, 'lp_rel_learnPath_module', array('map' => array('learnPath_id' => $lp_learnPath_map,
        'module_id' => $lp_module_map), 'return_mapping' => 'learnPath_module_id'));
    // update parent
    foreach ($lp_rel_learnPath_module_map as $key => $value) {
        Database::get()->query("UPDATE lp_rel_learnPath_module SET `parent` = ?d "
                . "WHERE `learnPath_id` IN (SELECT learnPath_id FROM lp_learnPath WHERE course_id = ?d) "
                . "AND `parent` = ?d", intval($value), intval($course_id), intval($key));
    }
    restore_table($restoreThis, 'lp_user_module_progress', array('delete' => array('user_module_progress_id'),
        'map' => array('user_id' => $userid_map,
        'learnPath_module_id' => $lp_rel_learnPath_module_map,
        'learnPath_id' => $lp_learnPath_map)));
    
    // Wiki
    $wiki_map = restore_table($restoreThis, 'wiki_properties', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'id'));
    restore_table($restoreThis, 'wiki_acls', array('map' => array('wiki_id' => $wiki_map)));
    $wiki_pages_map = restore_table($restoreThis, 'wiki_pages', array('map' => array('wiki_id' => $wiki_map,
        'owner_id' => $userid_map), 'return_mapping' => 'id'));
    restore_table($restoreThis, 'wiki_pages_content', array('delete' => array('id'),
        'map' => array('pid' => $wiki_pages_map, 'editor_id' => $userid_map)));
    
    //Blog
    if (file_exists("$restoreThis/blog_post")) {
        $blog_map = restore_table($restoreThis, 'blog_post', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'id'));
    }
    
    //Comments
    if (file_exists("$restoreThis/comments")) {
        restore_table($restoreThis, 'rating', array('delete' => array('id'),
        'map' => array('user_id' => $userid_map),
        'map_function' => 'comments_map_function',
        'map_function_data' => array($blog_map,
        $course_id)));
    }
    
    //Rating
    if (file_exists("$restoreThis/rating")) {
        restore_table($restoreThis, 'rating', array('delete' => array('rate_id'),
        'map' => array('user_id' => $userid_map),
        'map_function' => 'ratings_map_function',
        'map_function_data' => array($blog_map,
        $course_id)));
    }
    if (file_exists("$restoreThis/rating_cache")) {
        restore_table($restoreThis, 'rating_cache', array('delete' => array('rate_cache_id'),
        'map_function' => 'ratings_map_function',
        'map_function_data' => array($blog_map,
        $course_id)));
    }
    
    // Polls
    $poll_map = restore_table($restoreThis, 'poll', array('set' => array('course_id' => $course_id),
        'map' => array('creator_id' => $userid_map), 'return_mapping' => 'pid'));
    $poll_question_map = restore_table($restoreThis, 'poll_question', array('map' => array('pid' => $poll_map),
        'return_mapping' => 'pqid'));
    $poll_answer_map = restore_table($restoreThis, 'poll_question_answer', array('map' => array('pqid' => $poll_question_map),
        'return_mapping' => 'pqaid'));
    restore_table($restoreThis, 'poll_answer_record', array('delete' => array('arid'),
        'map' => array('pid' => $poll_map,
        'qid' => $poll_question_map,
        'aid' => $poll_answer_map,
        'user_id' => $userid_map)));

    // Assignments
    if (!isset($group_map[0])) {
        $group_map[0] = 0;
    }
    $assignments_map = restore_table($restoreThis, 'assignment', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'id'));
    restore_table($restoreThis, 'assignment_submit', array('delete' => array('id'),
        'map' => array('uid' => $userid_map, 'assignment_id' => $assignments_map, 'group_id' => $group_map)));

    // Agenda
    restore_table($restoreThis, 'agenda', array('delete' => array('id'), 'set' => array('course_id' => $course_id)));
    
    // Exercises
    $exercise_map = restore_table($restoreThis, 'exercise', array('set' => array('course_id' => $course_id), 'return_mapping' => 'id'));
    restore_table($restoreThis, 'exercise_user_record', array('delete' => array('eurid'),
        'map' => array('eid' => $exercise_map, 'uid' => $userid_map)));
    $question_map = restore_table($restoreThis, 'exercise_question', array('set' => array('course_id' => $course_id),
        'return_mapping' => 'id'));
    restore_table($restoreThis, 'exercise_answer', array(
        'delete' => array('id'),
        'map' => array('question_id' => $question_map)
    ));
    restore_table($restoreThis, 'exercise_with_questions', array('map' => array('question_id' => $question_map,
            'exercise_id' => $exercise_map)));

    $sql = "SELECT asset.asset_id, asset.path FROM `lp_module` AS module, `lp_asset` AS asset
                    WHERE module.startAsset_id = asset.asset_id
                    AND course_id = ?d AND contentType = 'EXERCISE'";
    $rows = Database::get()->queryArray($sql, intval($course_id));

    if (is_array($rows) && count($rows) > 0) {
        foreach ($rows as $row) {
            Database::get()->query("UPDATE `lp_asset` SET path = ?s WHERE asset_id = ?d", $exercise_map[$row->path], intval($row->asset_id));
        }
    }
    
    // Units
    $unit_map = restore_table($restoreThis, 'course_units', array('set' => array('course_id' => $course_id), 'return_mapping' => 'id'));
    restore_table($restoreThis, 'unit_resources', array('delete' => array('id'),
        'map' => array('unit_id' => $unit_map),
        'map_function' => 'unit_map_function',
        'map_function_data' => array($document_map,
            $link_category_map,
            $link_map,
            $ebook_map,
            $ebook_section_map,
            $ebook_subsection_map,
            $video_map,
            $videolink_map,
            $lp_learnPath_map,
            $wiki_map,
            $assignments_map,
            $exercise_map)));
    
    restore_table($restoreThis, 'course_description', array('set' => array('course_id' => $course_id),
        'delete' => array('id')));

    removeDir($restoreThis);
    
    // index course after restoring
    require_once 'modules/search/indexer.class.php';
    $idx = new Indexer();
    $idx->removeAllByCourse($course_id);
    $idx->storeAllByCourse($course_id);
    
    $tool_content .= "</p><br /><center><p><a href='../admin/index.php'>$langBack</a></p></center>";
} elseif (isset($_POST['do_restore'])) {
    $base = $_POST['restoreThis'];
    if (!file_exists($base . '/config_vars')) {
        $tool_content .= "<p class='alert1'>$langInvalidArchive</p>";
        draw($tool_content, 3);
        exit;
    }
    if ($data = get_serialized_file('course')) {
        // 3.0-style backup
        $data = $data[0];
        if (isset($data['fake_code'])) {
            $data['public_code'] = $data['fake_code'];
        }
        $hierarchy = get_serialized_file('hierarchy');
        $course_units = get_serialized_file('course_units');
        $unit_resources = get_serialized_file('unit_resources');
        $description = '';
        if ($unit_data = search_table_dump($course_units, 'order', -1)) {
            if ($resource_data = search_table_dump($unit_resources, 'order', -1)) {
                $description = purify($resource_data['comments']);
            }
        }
        $tool_content = course_details_form($data['public_code'], $data['title'], $data['prof_names'], $data['lang'], null, $data['visible'], $description, $hierarchy);
    } elseif ($data = get_serialized_file('cours')) {
        // 2.x-style backup
        $data = $data[0];
        if (isset($data['fake_code'])) {
            $data['public_code'] = $data['fake_code'];
        }
        $faculte = get_serialized_file('faculte');
        $course_units = get_serialized_file('course_units');
        $unit_resources = get_serialized_file('unit_resources');
        $description = '';
        if ($unit_data = search_table_dump($course_units, 'order', -1)) {
            if ($resource_data = search_table_dump($unit_resources, 'order', -1)) {
                $description = purify($resource_data['comments']);
            }
        }
        $tool_content = course_details_form($data['public_code'], $data['intitule'], $data['titulaires'], $data['languageCourse'], $data['type'], $data['visible'], $description, $faculte);
    } else {
        // Old-style backup
        $data = parse_backup_php($base . '/backup.php');
        $tool_content = course_details_form($data['code'], $data['title'], $data['prof_names'], $data['lang'], $data['type'], $data['visible'], $data['description'], $data['faculty']);
    }
} else {

// -------------------------------------
// Display restore info form
// -------------------------------------
    $tool_content .= "<br />
       <fieldset>
      <legend>$langFirstMethod</legend>
        <table width='100%' class='tbl'><tr>
          <td>$langRequest1
          <br /><br />
          <form action='" . $_SERVER['SCRIPT_NAME'] . "' method='post' enctype='multipart/form-data'>
            <input type='file' name='archiveZipped' />
            <input type='submit' name='send_archive' value='" . $langSend . "' />
            </form>
            <div class='right smaller'>$langMaxFileSize " .
            ini_get('upload_max_filesize') . "</div>
            </td>
        </tr></table>
        </fieldset>
<br />

 <fieldset>
    <legend>$langSecondMethod</legend>
    <table width='100%' class='tbl'>
        <tr>
          <td>$langRequest2
          <br /><br />
          <form action='" . $_SERVER['SCRIPT_NAME'] . "' method='post'>
            <input type='text' name='pathToArchive' />
            <input type='submit' name='send_path' value='" . $langSend . "' />
          </form>
          </td>
        </tr>
        </table></fieldset>
        <br />";
}
draw($tool_content, 3, null, $head_content);

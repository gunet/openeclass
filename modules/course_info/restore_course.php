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
require_once 'upgrade/upgrade_functions.php';
require_once 'modules/create_course/functions.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/pclzip/pclzip.lib.php';
require_once 'include/phpass/PasswordHash.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/hierarchy.class.php';

$treeObj = new Hierarchy();
$courseObj = new Course();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');

list($js, $html) = $treeObj->buildCourseNodePicker();
$head_content .= $js;

$nameTools = $langRestoreCourse;
$navigation[] = array('url' => '../admin/index.php', 'name' => $langAdmin);

// Default backup version
$version = 1;
$encoding = 'ISO-8859-7';
if (isset($_FILES['archiveZipped']) and $_FILES['archiveZipped']['size'] > 0) {

    validateUploadedFile($_FILES['archiveZipped']['name'], 3);

        $tool_content .= "<fieldset>
        <legend>".$langFileSent."</legend>
        <table class='tbl' width='100%'>
                   <tr><th width='150'>$langFileSentName</td><td>".$_FILES['archiveZipped']['name']."</th></tr>
                   <tr><th>$langFileSentSize</td><td>".$_FILES['archiveZipped']['size']."</th></tr>
                   <tr><th>$langFileSentType</td><td>".$_FILES['archiveZipped']['type']."</th></tr>
                   <tr><th>$langFileSentTName</td><td>".$_FILES['archiveZipped']['tmp_name']."</th></tr>
                </table></fieldset>
                        <fieldset>
        <legend>".$langFileUnzipping."</legend>
        <table class='tbl' width='100%'>
                    <tr><td>".unpack_zip_show_files($_FILES['archiveZipped']['tmp_name'])."</td></tr>
                </table></fieldset>";
} elseif (isset($_POST['send_path']) and isset($_POST['pathToArchive'])) {
        $pathToArchive = $_POST['pathToArchive'];
        if (file_exists($pathToArchive)) {
                $tool_content .= "<fieldset>
        <legend>".$langFileUnzipping."</legend>
        <table class='tbl' width='100%'>";
                $tool_content .= "<tr><td>".unpack_zip_show_files($pathToArchive)."</td></tr>";
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

        if (isset($_POST['department'])) {
                foreach ($_POST['department'] as $did) {
                        $departments[] = intval($did);
                }
        } else {
                $departments[0] = db_query_get_single_value("SELECT MIN(id) FROM hierarchy");
        }

        $r = $restoreThis . '/html';
        list($new_course_code, $course_id) = create_course($course_code, $course_lang, $course_title,
                $departments, $course_vis, $course_prof);
        if (!$new_course_code) {
                $tool_content = "<p class='alert1'>$langError</p>";
                draw($tool_content, 3);
                exit;
        }
        $cours_file = $_POST['restoreThis'] . '/course';
        if (file_exists($cours_file)) {
                $data = unserialize(file_get_contents($cours_file));
                $data = $data[0];
                db_query("UPDATE course
                                 SET course_keywords = ".quote($data['course_keywords']).",
                                     doc_quota = ".floatval($data['doc_quota']).",
                                     video_quota = ".floatval($data['video_quota']).",
                                     group_quota = ".floatval($data['group_quota']).",
                                     dropbox_quota = ".floatval($data['dropbox_quota']).",
                                     expand_glossary = ".intval($data['expand_glossary'])."
                                 WHERE cours_id = $course_id");
        }

        $userid_map = array();
        $user_file = $_POST['restoreThis'] . '/user';
        if (file_exists($user_file)) {
                $cours_user = unserialize(file_get_contents($_POST['restoreThis'] . '/cours_user'));
                $userid_map = restore_users($course_id, unserialize(file_get_contents($user_file)),
                                            $cours_user);
                register_users($course_id, $userid_map, $cours_user);
        }

        $coursedir = "${webDir}/courses/$new_course_code";
        $videodir = "${webDir}/video/$new_course_code";
        move_dir($r, $coursedir);
        if (is_dir($restoreThis . '/video_files')) {
                move_dir($restoreThis . '/video_files', $videodir);
        }
        course_index($coursedir, $new_course_code);
        $tool_content .= "<p>$langCopyFiles $coursedir</p>";
        $data = parse_backup_php($_POST['restoreThis'] . '/backup.php');

        load_global_messages();

        //        map_db_field('dropbox_file', 'uploaderId', $userid_map);
        //        map_db_field('dropbox_person', 'personId', $userid_map);
        //        map_db_field('dropbox_post', 'recipientId', $userid_map);

        $config_data = unserialize(file_get_contents("$restoreThis/config_vars"));
        $course_data = unserialize(file_get_contents("$restoreThis/cours"));
        $url_prefix_map = array(
                $config_data['urlServer'] . 'modules/ebook/show.php/' . $course_data[0]['code'] =>
                        $urlServer . 'modules/ebook/show.php/' . $new_course_code,
                $config_data['urlAppend'] . '/modules/ebook/show.php/' . $course_data[0]['code'] =>
                        $urlAppend . '/modules/ebook/show.php/' . $new_course_code,
                $config_data['urlServer'] . 'modules/document/file.php/' . $course_data[0]['code'] =>
                        $urlServer . 'modules/document/file.php/' . $new_course_code,
                $config_data['urlAppend'] . '/modules/document/file.php/' . $course_data[0]['code'] =>
                        $urlAppend . '/modules/document/file.php/' . $new_course_code,
                $config_data['urlServer'] . 'courses/' . $course_data[0]['code'] =>
                        $urlServer . 'courses/' . $new_course_code,
                $config_data['urlAppend'] . '/courses/' . $course_data[0]['code'] =>
                        $urlAppend . '/courses/' . $new_course_code,
                $course_data[0]['code'] =>
                        $new_course_code);

        function document_map_function(&$data, $maps) {
                // $maps[1]: group map, $maps[2]: ebook map
                $stype = $data['subsystem'];
                $sid = $data['subsystem_id'];
                if ($stype > 0) {
                        if (isset($maps[$stype][$sid])) {
                                $data['subsystem_id'] = $maps[$stype][$sid];
                        } else {
                                return false;
                        }
                }
                return true;
        }

        function unit_map_function(&$data, $maps) {
                list($document_map, $link_category_map, $link_map, $ebook_map, $section_map, $subsection_map, $video_map, $videolinks_map, $lp_learnPath_map, $wiki_map, $assignments_map, $exercise_map) = $maps;
                $type = $data['type'];
                if ($type == 'doc') {
                        $data['res_id'] = $document_map[$data['res_id']];
                } elseif ($type == 'linkcategory') {
                        $data['res_id'] = $link_category_map[$data['res_id']];
                } elseif ($type == 'link') {
                        $data['res_id'] = $link_map[$data['res_id']];
                } elseif ($type == 'ebook') {
                        $data['res_id'] = $ebook_map[$data['res_id']];
                } elseif ($type == 'section') {
                        $data['res_id'] = $section_map[$data['res_id']];
                } elseif ($type == 'subsection') {
                        $data['res_id'] = $subsection_map[$data['res_id']];
                } elseif ($type == 'description') {
                        $data['res_id'] = intval($data['res_id']);
                } elseif ($type == 'video') {
                        $data['res_id'] = $video_map[$data['res_id']];
                } elseif ($type == 'videolinks') {
                        $data['res_id'] = $videolinks_map[$data['res_id']];
                } elseif ($type == 'lp') {
                        $data['res_id'] = $lp_learnPath_map[$data['res_id']];
                } elseif ($type == 'wiki') {
                        $data['res_id'] = $wiki_map[$data['res_id']];
                } elseif ($type == 'work') {
                        $data['res_id'] = $assignments_map[$data['res_id']];
                } elseif ($type == 'exercise') {
                        $data['res_id'] = $exercise_map[$data['res_id']];
                }
                return true;
        }

        function offset_map_function(&$data, $maps) {
            list($key, $offset) = $maps;
            if (isset($data[$key])) {
                $data[$key] += $offset;
            }
            return true;
        }

        restore_table($restoreThis, 'announcements',
                array('set' => array('course_id' => $course_id),
                      'delete' => array('id')));
        restore_table($restoreThis, 'group_properties',
                array('set' => array('course_id' => $course_id)));
        $group_map = restore_table($restoreThis, 'group',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        restore_table($restoreThis, 'group_members',
                array('map' => array('group_id' => $group_map,
                                     'user_id' => $userid_map)));
        restore_table($restoreThis, 'forum_notify',
                array('set' => array('course_id' => $course_id),
                      'map' => array('user_id' => $userid_map),
                      'delete' => array('id')));
        $link_category_map = restore_table($restoreThis, 'link_category',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        $link_category_map[0] = 0;
        $link_map = restore_table($restoreThis, 'link',
                array('set' => array('course_id' => $course_id),
                      'map' => array('category' => $link_category_map),
                      'return_mapping' => 'id'));
        $ebook_map = restore_table($restoreThis, 'ebook',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        foreach ($ebook_map as $old_id => $new_id) {
                rename("$coursedir/ebook/$old_id", "$coursedir/ebook/$new_id");
        }
        $document_map = restore_table($restoreThis, 'document',
                array('set' => array('course_id' => $course_id),
                      'map_function' => 'document_map_function',
                      'map_function_data' => array(1 => $group_map, 2 => $ebook_map),
                      'return_mapping' => 'id'));
        $ebook_section_map = restore_table($restoreThis, 'ebook_section',
                array('map' => array('ebook_id' => $ebook_map),
                      'return_mapping' => 'id'));
        $ebook_subsection_map = restore_table($restoreThis, 'ebook_subsection',
                array('map' => array('section_id' => $ebook_section_map,
                                     'file_id' => $document_map),
                      'delete' => array('file'),
                      'return_mapping' => 'id'));
        if (file_exists("$restoreThis/video"))
            $video_map = restore_table($restoreThis, 'video',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        if (file_exists("$restoreThis/videolinks"))
            $videolinks_map  = restore_table($restoreThis, 'videolinks',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        if (file_exists("$restoreThis/dropbox_file") &&
            file_exists("$restoreThis/dropbox_person") &&
            file_exists("$restoreThis/dropbox_post"))
        {
            $dropbox_map = restore_table($restoreThis, 'dropbox_file',
                array('set' => array('course_id' => $course_id),
                      'map' => array('uploaderId' => $userid_map),
                      'return_mapping' => 'id'));
            restore_table($restoreThis, 'dropbox_person',
                array('map' => array('fileId' => $dropbox_map,
                                     'personId' => $userid_map)));
            restore_table($restoreThis, 'dropbox_post',
                array('map' => array('fileId' => $dropbox_map,
                                     'recipientId' => $userid_map)));
        }
        if (file_exists("$restoreThis/lp_learnPath") &&
            file_exists("$restoreThis/lp_module") &&
            file_exists("$restoreThis/lp_asset") &&
            file_exists("$restoreThis/lp_rel_learnPath_module") &&
            file_exists("$restoreThis/lp_user_module_progress")) {
            $lp_learnPath_map = restore_table($restoreThis, 'lp_learnPath',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'learnPath_id'));
            $lp_module_map = restore_table($restoreThis, 'lp_module',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'module_id'));
            $lp_asset_map = restore_table($restoreThis, 'lp_asset',
                array('map' => array('module_id' => $lp_module_map),
                      'return_mapping' => 'asset_id'));
            // update lp_module startAsset_id with new asset_id from map
            foreach ($lp_asset_map as $key => $value) {
                $result = db_query("UPDATE lp_module SET `startAsset_id` = $value
                                                WHERE `course_id` = $course_id AND `startAsset_id` = $key");
            }
            $lp_rel_learnPath_module_map = restore_table($restoreThis, 'lp_rel_learnPath_module',
                array('map' => array('learnPath_id' => $lp_learnPath_map,
                                     'module_id' => $lp_module_map),
                      'return_mapping' => 'learnPath_module_id'));
            // update parent
            foreach ($lp_rel_learnPath_module_map as $key => $value) {
                    $result = db_query("UPDATE lp_rel_learnPath_module
                                               SET `parent` = $value
                                               WHERE `learnPath_id` IN
                                                         (SELECT learnPath_id FROM lp_learnPath
                                                                 WHERE course_id = $course_id) AND
                                                     `parent` = $key");
            }
            restore_table($restoreThis, 'lp_user_module_progress',
                array('delete' => array('user_module_progress_id'),
                      'map' => array('user_id' => $userid_map,
                                     'learnPath_module_id' => $lp_rel_learnPath_module_map,
                                     'learnPath_id' => $lp_learnPath_map)));
        }
        if (file_exists("$restoreThis/wiki_properties") &&
            file_exists("$restoreThis/wiki_acls") &&
            file_exists("$restoreThis/wiki_pages") &&
            file_exists("$restoreThis/wiki_pages_content")) {
                $wiki_map = restore_table($restoreThis, 'wiki_properties',
                    array('set' => array('course_id' => $course_id),
                          'return_mapping' => 'id'));
                restore_table($restoreThis, 'wiki_acls',
                    array('map' => array('wiki_id' => $wiki_map)));
                $wiki_pages_map = restore_table($restoreThis, 'wiki_pages',
                    array('map' => array('wiki_id' => $wiki_map,
                                         'owner_id' => $userid_map),
                          'return_mapping' => 'id'));
                restore_table($restoreThis, 'wiki_pages_content',
                    array('delete' => array('id'),
                          'map' => array('pid' => $wiki_pages_map,
                                         'editor_id' => $userid_map)));
        }
        if (file_exists("$restoreThis/poll") &&
            file_exists("$restoreThis/poll_question") &&
            file_exists("$restoreThis/poll_question_answer") &&
            file_exists("$restoreThis/poll_answer_record"))
        {
            $poll_map = restore_table($restoreThis, 'poll',
                array('set' => array('course_id' => $course_id),
                      'map' => array('creator_id' => $userid_map),
                      'return_mapping' => 'pid'));
            $poll_question_map = restore_table($restoreThis, 'poll_question',
                array('map' => array('pid' => $poll_map),
                      'return_mapping' => 'pqid'));
            $poll_answer_map = restore_table($restoreThis, 'poll_question_answer',
                array('map' => array('pqid' => $poll_question_map),
                      'return_mapping' => 'pqaid'));
            restore_table($restoreThis, 'poll_answer_record',
                array('delete' => array('arid'),
                      'map' => array('pid' => $poll_map,
                                     'qid' => $poll_question_map,
                                     'aid' => $poll_answer_map,
                                     'user_id' => $userid_map)));
        }
        if (file_exists("$restoreThis/assignments") &&
            file_exists("$restoreThis/assignment_submit"))
        {
            if (!isset($group_map[0]))
            {
                $group_map[0] = 0;
            }
            $assignments_map = restore_table($restoreThis, 'assignments',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
            restore_table($restoreThis, 'assignment_submit',
                array('delete' => array('id'),
                      'map' => array('uid' => $userid_map,
                                     'assignment_id' => $assignments_map,
                                     'group_id' => $group_map)));
        }
        if (file_exists("$restoreThis/agenda"))
        {
            restore_table($restoreThis, 'agenda',
                array('delete' => array('id'),
                      'set' => array('course_id' => $course_id)));
        }
        if (file_exists("$restoreThis/exercise") &&
            file_exists("$restoreThis/exercise_user_record") &&
            file_exists("$restoreThis/question") &&
            file_exists("$restoreThis/answer") &&
            file_exists("$restoreThis/exercise_question"))
        {
            $exercise_map = restore_table($restoreThis, 'exercise',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
            restore_table($restoreThis, 'exercise_user_record',
                array('delete' => array('eurid'),
                      'map' => array('eid' => $exercise_map,
                                     'uid' => $userid_map)));
            $question_map = restore_table($restoreThis, 'question',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));

            list($answer_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM answer"));
            if (!$answer_offset)
                $answer_offset = 0;

            restore_table($restoreThis, 'answer',
                array('map_function' => 'offset_map_function',
                      'map_function_data' => array('id', $answer_offset),
                      'map' => array('question_id' => $question_map)));
            restore_table($restoreThis, 'exercise_question',
                array('map' => array('question_id' => $question_map,
                                     'exercise_id' => $exercise_map)));

            $sql = "SELECT asset.asset_id, asset.path FROM `lp_module` AS module, `lp_asset` AS asset
                        WHERE module.startAsset_id = asset.asset_id
                        AND course_id = $course_id AND contentType = 'EXERCISE'";
            $result = db_query($sql);

            while($row = mysql_fetch_array($result)) {
                    db_query("UPDATE `lp_asset`
                                     SET path = ". $exercise_map[$row['path']] ."
                                     WHERE asset_id = ". $row['asset_id']);
            }
        }
        $unit_map = restore_table($restoreThis, 'course_units',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        restore_table($restoreThis, 'unit_resources',
                array('delete' => array('id'),
                      'map' => array('unit_id' => $unit_map),
                      'map_function' => 'unit_map_function',
                      'map_function_data' => array($document_map,
                                                   $link_category_map,
                                                   $link_map,
                                                   $ebook_map,
                                                   $ebook_section_map,
                                                   $ebook_subsection_map,
                                                   $video_map,
                                                   $videolinks_map,
                                                   $lp_learnPath_map,
                                                   $wiki_map,
                                                   $assignments_map,
                                                   $exercise_map)));

        removeDir($restoreThis);
        $tool_content .= "</p><br />
                          <center><p><a href='../admin/index.php'>$langBack</a></p></center>";
}

elseif (isset($_POST['do_restore'])) {
        $base = $_POST['restoreThis'];
        if (!file_exists($base . '/backup.php') and
            !file_exists($base . '/config_vars')) {
                $tool_content .= "<p class='alert1'>$langInvalidArchive</p>";
                draw($tool_content, 3);
                exit;
        }
        if ($data = get_serialized_file('course')) {
                // 3.0-style backup
                $data = $data[0];
                $hierarchy = get_serialized_file('hierarchy');
                if (isset($data['fake_code'])) {
                        $data['public_code'] = $data['fake_code'];
                }
                $tool_content = course_details_form($data['public_code'], $data['title'],
                        $data['prof_names'], $data['lang'], null, $data['visible'],
                        "data[description]", $hierarchy);
        } elseif ($data = get_serialized_file('cours')) {
                // 2.x-style backup
                die('FIXME!');
        } else {
                // Old-style backup
                $data = parse_backup_php($_POST['restoreThis'] . '/backup.php');
                $tool_content = course_details_form($data['code'], $data['title'],
                        $data['prof_names'], $data['lang'], $data['type'],
                        $data['visible'], $data['description'], $data['faculty']);

        }
} else {

// -------------------------------------
// Display restore info form
// -------------------------------------
        $tool_content .= "
        <br />
   <fieldset>
  <legend>$langFirstMethod</legend>
        <table width='100%' class='tbl'><tr>
          <td>$langRequest1
          <br /><br />
          <form action='".$_SERVER['SCRIPT_NAME']."' method='post' enctype='multipart/form-data'>
            <input type='file' name='archiveZipped' />
            <input type='submit' name='send_archive' value='".$langSend."' />
            </form>
            <div class='right smaller'>$langMaxFileSize ".
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
          <form action='".$_SERVER['SCRIPT_NAME']."' method='post'>
            <input type='text' name='pathToArchive' />
            <input type='submit' name='send_path' value='".$langSend."' />
          </form>
          </td>
        </tr>
        </table></fieldset>
        <br />";
}
draw($tool_content, 3, null, $head_content);


// insert users into main database
function user($userid, $name, $surname, $login, $password, $email, $statut, $phone, $department,
              $registered_at = NULL, $expires_at = NULL, $inst_id = NULL)
{
        global $action, $new_course_code, $course_id, $userid_map,
               $version, $langUserWith, $langAlready,
               $langWithUsername, $langUserisAdmin, $langUsernameSame,
               $langRestoreUserExists, $langRestoreUserNew,
               $langUsername, $langPrevId, $langNewId, $langUserName;

        $name = inner_unquote($name);
        $surname = inner_unquote($surname);
        $login = inner_unquote($login);

        if (!$action or $_POST['add_users'] == 'none' or
            ($_POST['add_users'] == 'prof' and $statut != 1)) {
                return;
        }
        if (isset($userid_map[$userid])) {
                echo "<br />$langUserWith $userid_map[$userid] $langAlready\n";
                return;
        }
        if (!$registered_at)  {
                $registered_at = time();
        }
        if (!$expires_at) {
                $expires_at = time() + get_config('account_duration');
        }

        $u = db_query("SELECT * FROM user WHERE BINARY username=".quote($login));
        if (mysql_num_rows($u) > 0) {
                $res = mysql_fetch_array($u);
                $userid_map[$userid] = $res['user_id'];
                echo sprintf($langRestoreUserExists,
                             '<b>' . q($login) . '</b>',
                             '<i>' . q("$res[prenom] $res[nom]") . '</i>',
                             '<i>' . q("$name $surname") . '</i>'), '<br>';
        } elseif (isset($_POST['create_users'])) {
                if ($version == 1) { // if we come from a archive < 2.x encrypt user password
                        $hasher = new PasswordHash(8, false);
                        $password = $hasher->HashPassword($password);
                }
                db_query("INSERT INTO user
                        (nom, prenom, username, password, email, statut, phone, department, registered_at, expires_at, description)
                        VALUES (".
                        join(", ", array(
                                quote($name),
                                quote($surname),
                                quote($login),
                                quote($password),
                                quote($email),
                                quote($statut),
                                quote($phone),
                                quote($department),
                                quote($registered_at),
                                quote($expires_at)
                                )).
                                ", '')");
                $userid_map[$userid] = mysql_insert_id();
                echo sprintf($langRestoreUserNew,
                             '<b>' . q($login), '</b>',
                             '<i>' . q("$name $surname") . '</i>'), '<br>';
        } else {
                return;
        }

        db_query("INSERT INTO course_user
                (course_id, user_id, statut, reg_date)
                VALUES ($course_id, {$userid_map[$userid]}, $statut, NOW())");
        echo q("$langUsername=$login, $langPrevId=$userid, $langNewId=" . $userid_map[$userid]),
             "<br>\n";
}


// form select about visibility
function visibility_select($current)
{
        global $m;

        $ret = "<select name='course_vis'>\n";
        foreach (array($m['legopen'] => COURSE_OPEN,
                       $m['legrestricted'] => COURSE_REGISTRATION,
                       $m['legclosed'] => COURSE_CLOSED,
                       $m['linactive'] => COURSE_INACTIVE) as $text => $type) {
                $selected = ($type == $current)? ' selected': '';
                $ret .= "<option value='$type'$selected>" . q($text) . "</option>\n";
        }
        $ret .= "</select>\n";
        return $ret;
}

// Unzip backup file
function unpack_zip_show_files($zipfile)
{
        global $webDir, $uid, $langEndFileUnzip, $langLesFound, $langRestore, $langLesFiles;

        $retString = '';
        $zip = new pclZip($zipfile);
        validateUploadedZipFile($zip->listContent(), 3);

        $destdir = $webDir . '/courses/tmpUnzipping/' . $uid;
        mkpath($destdir);
        chdir($destdir);
        $state = $zip->extract();
        $retString .= "<br />$langEndFileUnzip<br /><br />$langLesFound
                       <form action='$_SERVER[SCRIPT_NAME]' method='post'>
                         <ol>";
        $checked = ' checked';
        foreach (find_backup_folders($destdir) as $folder) {
                $path = q($folder['path'] . '/' . $folder['dir']);
                $file = q($folder['dir']);
                $course = q(preg_replace('|^.*/|', '', $folder['path']));
                $retString .= "<li>$langLesFiles <input type='radio' name='restoreThis' value='$path'$checked>
                        <b>$course</b> ($file)</li>\n";
                $checked = '';
        }
        $retString .= "</ol><br /><input type='submit' name='do_restore' value='$langRestore' /></form>";
        chdir($webDir);
        return $retString;
}


// Find folders under $basedir containing a "backup.php" or a "config_vars" file
function find_backup_folders($basedir)
{
        $dirlist = array();
        if (is_dir($basedir) and $handle = opendir($basedir)) {
                while (($file = readdir($handle)) !== false) {
                        $entry = "$basedir/$file";
                        if (is_dir($entry) and $file != '.' and $file != '..') {
                                if (file_exists("$entry/backup.php") or
                                    file_exists("$entry/config_vars")) {
                                        $dirlist[] = array('path' => $basedir,
                                                           'dir' => $file);
                                } else {
                                        $dirlist = array_merge($dirlist,
                                                               find_backup_folders($entry));
                                }
                        }
                }
                closedir($handle);
        }
        return $dirlist;
}

function restore_table($basedir, $table, $options)
{
        global $url_prefix_map;

        $set = get_option($options, 'set');
        $backup = unserialize(file_get_contents("$basedir/$table"));
        $i = 0;
        $mapping = array();
        if (isset($options['return_mapping'])) {
                $return_mapping = true;
                $id_var = $options['return_mapping'];
        } else {
                $return_mapping = false;
        }

        foreach ($backup as $data) {
                if ($return_mapping) {
                        $old_id = $data[$id_var];
                        unset($data[$id_var]);
                }
                if (isset($options['delete'])) {
                        foreach ($options['delete'] as $field) {
                                unset($data[$field]);
                        }
                }
                if (!isset($sql_intro)) {
                        $sql_intro = "INSERT INTO `$table` " .
                                     field_names($data) . ' VALUES ';
                }
                if (isset($options['map'])) {
                        foreach ($options['map'] as $field => &$map) {
                                if (isset($map[$data[$field]])) {
                                        $data[$field] = $map[$data[$field]];
                                } else {
                                        continue 2;
                                }
                        }
                }
                $do_insert = true;
                if (isset($options['map_function'])) {
                        if (isset($options['map_function_data'])) {
                                $do_insert = $options['map_function']($data, $options['map_function_data']);
                        } else {
                                $do_insert = $options['map_function']($data);
                        }
                }
                if ($do_insert) {
                        if (isset($url_prefix_map)) {
                                db_query(strtr($sql_intro . field_values($data, $set),
                                               $url_prefix_map));
                        } else {
                                db_query($sql_intro . field_values($data, $set));
                        }
                }
                if ($return_mapping) {
                        $mapping[$old_id] = mysql_insert_id();
                }
        }
        if ($return_mapping) {
                return $mapping;
        }
}

function field_names($data)
{
        foreach ($data as $name => $value) {
                $keys[] = '`' . $name . '`';
        }
        return '(' . implode(', ', $keys) . ')';
}

function field_values($data, $set)
{
        foreach ($data as $name => $value) {
                if (isset($set[$name])) {
                        $value = $set[$name];
                }
                if (is_int($value)) {
                        $values[] = $value;
                } else {
                        $values[] = quote($value);
                }
        }
        return '(' . implode(', ', $values) . ')';
}

function get_option($options, $name)
{
        if (isset($options[$name])) {
                return $options[$name];
        } else {
                return array();
        }
}

function course_details_form($code, $title, $prof, $lang, $type=null, $vis, $desc, $faculty=null)
{
        global $langInfo1, $langInfo2, $langCourseCode, $langLanguage, $langTitle,
               $langCourseDescription, $langFaculty, $langCourseVis,
               $langTeacher, $langUsersWillAdd,
               $langOk, $langAll, $langsTeachers, $langMultiRegType,
               $langNone, $langOldValue, $treeObj;

        // find available languages
        $languages = array();
        foreach ($GLOBALS['active_ui_languages'] as $langcode) {
                $entry = langcode_to_name($langcode);
                if (isset($langNameOfLang[$entry])) {
                        $languages[$entry] = $langNameOfLang[$entry];
                } else {
                        $languages[$entry] = $entry;
                }
        }

        list($tree_js, $tree_html) = $treeObj->buildCourseNodePicker();
        if ($type) {
                if (isset($GLOBALS['lang' . $type])) {
                        $type_label = ' (' . $GLOBALS['lang' . $type] . ')';
                } else {
                        $type_label = ' (' . $type . ')';
                }
        } else {
                $type_label = '';
        }
        if (is_array($faculty)) {
                foreach ($faculty as $entry) {
                        $old_faculty_names[] = q($entry['name']);
                }
                $old_faculty = implode('<br>', $old_faculty_names);
        } else {
                $old_faculty = q($faculty . $type_label);
        }
        return "<p>$langInfo1</p>
                <p>$langInfo2</p>
                <form action='$_SERVER[SCRIPT_NAME]' method='post' onsubmit='return validateNodePickerForm();' >
                <table width='99%' class='tbl'><tbody>
                   <tr><td>&nbsp;</td></tr>
                   <tr><th>$langCourseCode:</th>
                       <td><input type='text' name='course_code' value='".q($code)."' /></td></tr>
                   <tr><th>$langLanguage:</th>
                       <td>".selection($languages, 'course_lang', $lang)."</td></tr>
                   <tr><th>$langTitle:</th>
                       <td><input type='text' name='course_title' value='".q($title)."' size='50' /></td></tr>
                   <tr><th>$langCourseDescription:</th>
                       <td>".rich_text_editor('desc', 10, 40, purify($desc))."</td></tr>
                       <tr><th>$langFaculty:</th>
                       <td>" . $tree_html ."<br>$langOldValue: <i>$old_faculty</i></td></tr>
                   <tr><th>$langCourseVis:</th><td>".visibility_select($vis)."</td></tr>
                   <tr><th>$langTeacher:</th>
                       <td><input type='text' name='course_prof' value='".q($prof)."' size='50' /></td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><th>$langUsersWillAdd:</th>
                       <td><input type='radio' name='add_users' value='all' id='add_users_all'>
                           <label for='add_users_all'>$langAll</label><br>
                           <input type='radio' name='add_users' value='prof' id='add_users_prof' checked>
                           <label for='add_users_prof'>$langsTeachers</label><br>
                           <input type='radio' name='add_users' value='none' id='add_users_none'>
                           <label for='add_users_none'>$langNone</label></td></tr>
                   <tr><th><label for='create_users'>$langMultiRegType:</label></th>
                       <td><input type='checkbox' name='create_users' value='1' id='create_users'></td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><td colspan='2'>
                      <input type='submit' name='create_restored_course' value='$langOk' />
                      <input type='hidden' name='restoreThis' value='$_POST[restoreThis]' /></td></tr>
                </tbody></table>
                </form>";
}

function restore_users($course_id, $users, $cours_user)
{
        global $tool_content, $version,
               $langUserWith, $langWithUsername, $langUserisAdmin,
               $langUsernameSame, $langRestoreUserExists,
               $langRestoreUserNew;

        $userid_map = array();
        if ($_POST['add_users'] == 'none') {
                return $userid_map;
        }

        if ($_POST['add_users'] == 'prof') {
                $add_only_profs = true;
                foreach ($cours_user as $cu_info) {
                        $is_prof[$cu_info['user_id']] = ($cu_info['statut'] == 1);
                }
        } else {
                $add_only_profs = false;
        }

        foreach ($users as $data) {

                if ($add_only_profs and !$is_prof[$data['user_id']]) {
                        continue;
                }
                $u = db_query("SELECT * FROM user WHERE BINARY username=".quote($data['username']));
                if (mysql_num_rows($u) > 0) {
                        $res = mysql_fetch_array($u);
                        $userid_map[$data['user_id']] = $res['user_id'];
                        $tool_content .= "<p>" .
                                         sprintf($langRestoreUserExists,
                                                 '<b>' . q($data['username']) . '</b>',
                                                 '<i>' . q("$res[prenom] $res[nom]") . '</i>',
                                                 '<i>' . q("$data[prenom] $data[nom]") . '</i>') .
                                         "</p>\n";
                } elseif (isset($_POST['create_users'])) {
                        db_query("INSERT INTO user
                                         SET nom = ".quote($data['nom']).",
                                             prenom = ".quote($data['prenom']).",
                                             username = ".quote($data['username']).",
                                             password = ".quote($data['password']).",
                                             email = ".quote($data['email']).",
                                             statut = ".quote($data['statut']).",
                                             phone = ".quote($data['phone']).",
                                             department = ".quote($data['department']).",
                                             registered_at = ".quote($data['registered_at']).",
                                             expires_at = ". quote($data['registered_at'] + get_config('account_duration')));
                        $userid_map[$data['user_id']] = mysql_insert_id();
                        $tool_content .= "<p>" .
                                         sprintf($langRestoreUserNew,
                                                 '<b>' . q($data['username']) . '</b>',
                                                 '<i>' . q("$data[prenom] $data[nom]") . '</i>') .
                                         "</p>\n";
                }

        }
        return $userid_map;
}

function register_users($course_id, $userid_map, $cours_user)
{
        global $langPrevId, $langNewId, $tool_content;

        foreach ($cours_user as $cudata) {
                $old_id = $cudata['user_id'];
                if (isset($userid_map[$old_id])) {
                        $statut[$old_id] = $cudata['statut'];
                        $tutor[$old_id] = $cudata['tutor'];
                        $reg_date[$old_id] = $cudata['reg_date'];
                        $receive_mail[$old_id] = $cudata['receive_mail'];
                }
        }

        foreach ($userid_map as $old_id => $new_id) {
                db_query("INSERT INTO course_user
                                 SET course_id = $course_id,
                                     user_id = $new_id,
                                     statut = {$statut[$old_id]},
                                     reg_date = ".quote($reg_date[$old_id]).",
                                     receive_mail = {$receive_mail[$old_id]}");
                $tool_content .=  "<p>$langPrevId=$old_id, $langNewId=$new_id</p>\n";
        }
}

function map_table_field($table, $id, $field, $map)
{
        $q = db_query("SELECT `$id`, `$field` FROM `$table`");
        while ($r = mysql_fetch_row($q)) {
                db_query("UPDATE `$table` SET `$field` = " . $map[$r[1]] . " WHERE `$id` = " . $r[0]);
        }
}

function inner_unquote($s)
{
        global $encoding;

        return str_replace(array('\"', "\\\0"),
                           array('"', "\0"),
                           $s);

}

function map_db_field($table, $field, $mapping) {
        foreach ($mapping as $old => $new) {
               db_query("UPDATE `$table` SET `$field` = " . quote($new) . "
                             WHERE `$field` = " . quote($old));
        }
}


function parse_backup_php($file)
{
        global $durationAccount;

        $source = preg_replace('/^<\?\n/m', "<?php\n", file_get_contents($file));
        if (!preg_match('/encoding = .UTF-8./', $source)) {
                $source = iconv('ISO-8859-7', 'UTF-8//IGNORE', $source);
        }
        $tokens = token_get_all($source);
        $info = array();
        for ($i = 0; $i < count($tokens); $i++) {
                $token = $tokens[$i];
                if (!is_string($token)) {
                        list($id, $text) = $token;
                        if ($id == T_VARIABLE) {
                                $varname = substr($text, 1);
                                do {
                                        $i++;
                                } while ($tokens[$i] == '=' or
                                        $tokens[$i][0] == T_WHITESPACE);
                                list($id, $text) = $tokens[$i];
                                if ($id == T_CONSTANT_ENCAPSED_STRING or
                                    $id == T_LNUMBER) {
                                        $value = eval("return($text);");
                                        $info[$varname] = $value;
                                }
                        } elseif ($id == T_STRING) {
                                list($i, $args) = get_args($tokens, ++$i);
                                if ($text == 'query') {
                                        $sql = $args[0];
                                        if (preg_match('/^INSERT INTO `(\w+)` \(([^)]+)\) VALUES\s+(.*)$/si',
                                                       $sql, $matches)) {
                                                $table = $matches[1];
                                                // Skip tables not used any longer
                                                if ($table != 'stat_accueil' and $table != 'users') {
                                                        $fields = parse_fields($matches[2]);
                                                        $values = parse_values($matches[3]);
                                                        $info['query'][] = array(
                                                                'table' => $table,
                                                                'fields' => parse_fields($matches[2]),
                                                                'values' => parse_values($matches[3]));
                                                }
                                        }
                                } elseif ($text == 'course_details') {
                                        $info['code'] = $args[0];
                                        $info['lang'] = $args[1];
                                        $info['title'] = $args[2];
                                        $info['description'] = $args[3];
                                        $info['faculty'] = $args[4];
                                        $info['visible'] = $args[5];
                                        $info['prof_names'] = $args[6];
                                        $info['type'] = $args[7];
                                } elseif ($text == 'announcement') {
                                        $info['announcement'][] = make_assoc($args,
                                                array('contenu', 'temps', 'ordre', 'title'));
                                } elseif ($text == 'user') {
                                        if (!isset($args[9])) {
                                                $args[9] = time();
                                                $args[10] = time() + $durationAccount;
                                        }
                                        $info['user'][] = make_assoc($args,
                                                array('id', 'name', 'surname', 'username', 'password',
                                                      'email', 'statut', 'phone', 'department',
                                                      'registered_at', 'expires_at'));
                                } elseif ($text == 'assignment_submit') {
                                        $info['assignment_submit'][] = make_assoc($args,
                                                array('uid', 'assignment_id', 'submission_date',
                                                      'submission_ip', 'file_path', 'file_name',
                                                      'comments', 'grade', 'grade_comments',
                                                      'grade_submission_date', 'grade_submission_ip'));
                                } elseif ($text == 'dropbox_file') {
                                        $info['dropbox_file'][] = make_assoc($args,
                                                array('uploaderId', 'filename', 'filesize', 'title',
                                                      'description', 'author', 'uploadDate', 'lastUploadDate'));
                                } elseif ($text == 'dropbox_person') {
                                        $info['dropbox_person'][] = array(
                                                'fileId' => $args[0],
                                                'personId' => $args[1]);
                                } elseif ($text == 'dropbox_post') {
                                        $info['dropbox_post'][] = array(
                                                'fileId' => $args[0],
                                                'recipientId' => $args[1]);
                                } elseif ($text == 'group') {
                                        $info['group'][] = make_assoc($args,
                                                array('user', 'team', 'status', 'role'));
                                } elseif ($text == 'course_units') {
                                        $info['course_units'][] = make_assoc($args,
                                                array('title', 'comments', 'visibility', 'order',
                                                      'resource_units'));
                                } else {
                                        $info[$text] = $args;
                                }
                        } /* else {
                                if ($id != T_WHITESPACE) {
                                        echo token_name($id), ": ", q($text), '<br>';
                                }
                        } */
                }
        }
        return $info;
}

function make_assoc($args, $names)
{
        foreach ($args as $i => $value) {
                $assoc[$names[$i]] = $value;
        }
        return $assoc;
}

function get_args($tokens, $i)
{
        $args = array();
        do {
                if (!is_string($tokens[$i])) {
                        if ($tokens[$i][0] == T_CONSTANT_ENCAPSED_STRING or
                            $tokens[$i][0] == T_LNUMBER) {
                                $args[] = eval("return({$tokens[$i][1]});");
                        } elseif ($tokens[$i][0] == T_ARRAY) {
                                list($i, $args1) = get_args($tokens, ++$i);
                                $args[] = $args1;
                        }
                }
                $i++;
        } while ($tokens[$i] != ')');
        return array($i, $args);
}

function parse_fields($s)
{
        return preg_split('/[`, ]/', $s, null,  PREG_SPLIT_NO_EMPTY);
}

function parse_values($s)
{
        $values = array();
        $tokens = token_get_all('<?php ' . $s . ';');
        foreach ($tokens as $token) {
                if ($token == '(') {
                        $vtmp = array();
                } elseif ($token == ')') {
                        $values[] = $vtmp;
                } elseif (isset($token[0]) and
                          ($token[0] == T_CONSTANT_ENCAPSED_STRING or
                           $token[0] == T_LNUMBER)) {
                        $vtmp[] = eval("return({$token[1]});");
                }
        }
        return $values;
}

function get_serialized_file($file)
{
        global $base;

        $file = $base . '/' . $file;
        if (file_exists($file)) {
                return unserialize(file_get_contents($file));
        } else {
                return false;
        }
}

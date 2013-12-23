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

// ----------------------------------------------------------------
// Functions used for upgrade
// ----------------------------------------------------------------
//function to update a field in a table
function update_field($table, $field, $field_name, $id_col, $id) {
    $id = quote($id);
    $sql = "UPDATE `$table` SET `$field` = '$field_name' WHERE `$id_col` = $id;";
    db_query($sql);
}

// Adds field $field to table $table of current database, if it doesn't already exist
function add_field($table, $field, $type) {
    global $langToTable, $langAddField, $BAD;

    $retString = "";
    $fields = db_query("SHOW COLUMNS FROM $table LIKE '$field'");
    if (mysql_num_rows($fields) == 0) {
        if (!db_query("ALTER TABLE `$table` ADD `$field` $type")) {
            $retString .= "$langAddField <b>$field</b> $langToTable <b>$table</b>: ";
            $retString .= " $BAD<br>";
        }
    }
    return $retString;
}

function add_field_after_field($table, $field, $after_field, $type) {
    global $langToTable, $langAddField, $langAfterField, $BAD;

    $retString = "";

    $fields = db_query("SHOW COLUMNS FROM $table LIKE '$field'");
    if (mysql_num_rows($fields) == 0) {
        if (!db_query("ALTER TABLE `$table` ADD COLUMN `$field` $type AFTER `$after_field`")) {
            $retString .= "$langAddField <b>$field</b> $langAfterField <b>$after_field</b> $langToTable <b>$table</b>: ";
            $retString .= " $BAD<br>";
        }
    }
    return $retString;
}

function rename_field($table, $field, $new_field, $type) {
    global $langToA, $langRenameField, $langToTable, $BAD;

    $retString = "";

    $fields = db_query("SHOW COLUMNS FROM $table LIKE '$new_field'");
    if (mysql_num_rows($fields) == 0) {
        if (!db_query("ALTER TABLE `$table` CHANGE  `$field` `$new_field` $type")) {
            $retString .= "$langRenameField <b>$field</b> $langToA <b>$new_field</b> $langToTable <b>$table</b>: ";
            $retString .= " $BAD<br>";
        }
    }
    return $retString;
}

function delete_field($table, $field) {
    global $langOfTable, $langDeleteField, $BAD;

    $retString = "";
    if (!db_query("ALTER TABLE `$table` DROP `$field`")) {
        $retString .= "$langDeleteField <b>$field</b> $langOfTable <b>$table</b>";
        $retString .= " $BAD<br>";
    }
    return $retString;
}

function delete_table($table) {
    global $langDeleteTable, $BAD;
    $retString = "";

    if (!db_query("DROP TABLE IF EXISTS $table")) {
        $retString .= "$langDeleteTable <b>$table</b>: ";
        $retString .= " $BAD<br>";
    }
    return $retString;
}

function merge_tables($table_destination, $table_source, $fields_destination, $fields_source) {
    global $langMergeTables, $BAD;

    $retString = "";

    $query = "INSERT INTO $table_destination (";
    foreach ($fields_destination as $val) {
        $query.=$val . ",";
    }
    $query = substr($query, 0, -1) . ") SELECT ";
    foreach ($fields_source as $val) {
        $query.=$val . ",";
    }
    $query = substr($query, 0, -1) . " FROM " . $table_source;
    if (!db_query($query)) {
        $retString .= " $langMergeTables <b>$table_destination</b>,<b>$table_source</b>";
        $retString .= " $BAD<br>";
    }

    return $retString;
}

// check if a mysql table exists
function mysql_table_exists($db, $table) {
    $exists = db_query('SHOW TABLES FROM `' . $db . '` LIKE \'' . $table . '\'');
    return mysql_num_rows($exists) == 1;
}

// check if a mysql table field exists
function mysql_field_exists($db, $table, $field) {
    $fields = db_query("SHOW COLUMNS from $table LIKE '$field'", $db);
    if (mysql_num_rows($fields) > 0)
        return TRUE;
}

// check if a mysql index exists
function mysql_index_exists($table, $index_name) {
    $q = db_query("SHOW INDEX FROM `$table` WHERE Key_name = " . quote($index_name));
    if ($q and mysql_num_rows($q) > 0) {
        return true;
    } else {
        return false;
    }
}

// add index/indexes in specific table columns
function add_index($index, $table, $column) {
    global $langIndexAdded, $langIndexExists, $langToTable;

    $num_of_args = func_num_args();
    if ($num_of_args <= 3) {
        $ind_sql = db_query("SHOW INDEX FROM $table");
        while ($i = mysql_fetch_array($ind_sql)) {
            if ($i['Key_name'] == $index) {
                $retString = "<p>$langIndexExists $table</p>";
                return $retString;
            }
        }
        db_query("ALTER TABLE $table ADD INDEX `$index` ($column)");
    } else {
        $arguments = func_get_args();
        // cut the first and second argument
        array_shift($arguments);
        array_shift($arguments);
        $st = '';
        for ($j = 0; $j < count($arguments); $j++) {
            $st .= $arguments[$j] . ',';
        }
        $ind_sql = db_query("SHOW INDEXES FROM `$table`");
        while ($i = mysql_fetch_array($ind_sql)) {
            if ($i['Key_name'] == $index) {
                $retString = "<p>$langIndexExists $table</p>";
                return $retString;
            }
        }
        $sql = "ALTER TABLE $table ADD INDEX `$index` ($st)";
        $sql = str_replace(',)', ')', $sql);
        db_query($sql);
    }
    $retString = "<p>$langIndexAdded $langToTable $table</p>";
    return $retString;
}

// Removes initial part of path from assignment_submit.file_path
function update_assignment_submit() {
    global $langTable;

    $updated = FALSE;
    $q = db_query('SELECT id, file_path FROM assignment_submit');
    if ($q) {
        while ($i = mysql_fetch_array($q)) {
            $new = preg_replace('+^.*/work/+', '', $i['file_path']);
            if ($new != $i['file_path']) {
                db_query("UPDATE assignment_submit SET file_path = " .
                        quote($new) . " WHERE id = $i[id]");
                $updated = TRUE;
            }
        }
    }
    if ($updated) {
        echo "$langTable assignment_submit: $GLOBALS[OK]<br>\n";
    }
}

// checks if admin user
function is_admin($username, $password) {
    global $mysqlMainDb;

    if (mysql_field_exists($mysqlMainDb, 'user', 'user_id')) {
        $r = db_query("SELECT * FROM user, admin
				WHERE admin.idUser = user.user_id AND
				BINARY user.username = " . quote($username));
    } else {
        $r = db_query("SELECT * FROM user, admin
				WHERE admin.user_id = user.id AND
				BINARY user.username = " . quote($username));
    }

    if (!$r or mysql_num_rows($r) == 0) {
        return false;
    } else {

        $row = mysql_fetch_assoc($r);
        if (isset($row['privilege']) and $row['privilege'] !== '0')
            return false;

        $hasher = new PasswordHash(8, false);
        if (!$hasher->CheckPassword($password, $row['password']))
            return false;

        $_SESSION['uid'] = $row['user_id'];
        $_SESSION['nom'] = $row['nom'];
        $_SESSION['prenom'] = $row['prenom'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['uname'] = $username;
        $_SESSION['statut'] = $row['statut'];
        $_SESSION['is_admin'] = true;

        return true;
    }
}

// Check whether an entry with the specified $define_var exists in the accueil table
function accueil_tool_missing($define_var) {
    $r = mysql_query("SELECT id FROM accueil WHERE define_var = '$define_var'");
    if ($r and mysql_num_rows($r) > 0) {
        return false;
    } else {
        return true;
    }
}

// convert database and all tables to UTF-8
function convert_db_utf8($database) {
    global $langNotTablesList;

    db_query("ALTER DATABASE `$database` DEFAULT CHARACTER SET=utf8");
    $result = db_query("SHOW TABLES FROM `$database`");
    if (!$result) {
        echo "$langNotTablesList $database. ",
        'MySQL Error: ', mysql_error();
    }
    while ($row = mysql_fetch_row($result)) {
        db_query("ALTER TABLE `$database`.`$row[0]` CONVERT TO CHARACTER SET utf8");
    }
    mysql_free_result($result);
}

// -------------------------------------
// function for upgrading dropbox files
// -------------------------------------

function encode_dropbox_documents($code, $id, $filename, $title) {

    global $webDir, $langEncDropboxError;

    $format = get_file_extension($title);
    $new_filename = safe_filename($format);
    $path_to_dropbox = $webDir . '/courses/' . $code . '/dropbox/';

    if (!file_exists($path_to_dropbox . $filename)) {
        $filename = iconv('UTF-8', 'ISO-8859-7', $filename);
    }

    if (rename($path_to_dropbox . $filename, $path_to_dropbox . $new_filename)) {
        db_query("UPDATE dropbox_file SET filename = '$new_filename'
	        	WHERE id = '$id'", $code);
    } else {
        echo "$langEncDropboxError<br>";
    }
}

//---------------------------------------------
// Upgrade course database
//---------------------------------------------
// run all upgrade functions (used mainly to restore course)
function upgrade_course($code, $lang) {
    upgrade_course_old($code, $lang);
    upgrade_course_2_1_3($code);
    upgrade_course_2_2($code, $lang);
    upgrade_course_2_3($code);
    upgrade_course_2_4($code, $lang);
    upgrade_course_2_5($code, $lang);
    upgrade_course_2_8($code, $lang);
    upgrade_course_3_0($code);
}

// ----------------------------------
// Upgrade queries for 3.0
// ----------------------------------
function upgrade_course_3_0($code, $extramessage = '', $return_mapping = false) {
    global $langUpgCourse, $mysqlMainDb, $webDir, $langUpgradeCourseDone;

    echo "<hr><p>$langUpgCourse <b>$code</b> (3.0) $extramessage<br>";

    $course_id = course_code_to_id($code);
    mysql_select_db($code);
    flush();

    // move forum tables to central db
    if (mysql_table_exists($code, 'forums')) {
        list($forumcatid_offset) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.forum_category"));
        $forumcatid_offset = intval($forumcatid_offset);
        db_query("UPDATE catagories SET cat_order = 0 WHERE cat_order IS NULL OR cat_order = ''");
        $ok = db_query("INSERT INTO `$mysqlMainDb`.`forum_category`
                        (`id`, `cat_title`, `cat_order`, `course_id`)
                        SELECT `cat_id` + $forumcatid_offset, `cat_title`,
                               `cat_order`, $course_id FROM catagories");
        list($forumid_offset) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.forum"));
        $forumid_offset = intval($forumid_offset);
        $ok = db_query("INSERT INTO `$mysqlMainDb`.`forum`
                        (`id`, `name`, `desc`, `num_topics`, `num_posts`, `last_post_id`, `cat_id`, `course_id`)
                        SELECT forum_id + $forumid_offset, forum_name, forum_desc, forum_topics,
                               forum_posts, forum_last_post_id, cat_id + $forumcatid_offset, $course_id
                        FROM forums ORDER by forum_id") && $ok;
        $ok = db_query("UPDATE `$mysqlMainDb`.group SET forum_id = forum_id + $forumid_offset
                                          WHERE course_id = $course_id") && $ok;
        list($forumtopicid_offset) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.forum_topic"));
        $forumtopicid_offset = intval($forumtopicid_offset);
        $ok = db_query("INSERT INTO `$mysqlMainDb`.`forum_topic`
                        (`id`, `title`, `poster_id`, `topic_time`, `num_views`, `num_replies`, `last_post_id`, `forum_id`)
                        SELECT topic_id + $forumtopicid_offset, topic_title, topic_poster, topic_time, topic_views,
                               topic_replies, topic_last_post_id, forum_id + $forumid_offset
                        FROM topics") && $ok;
        list($forumpostid_offset) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.forum_post"));
        $forumpostid_offset = intval($forumpostid_offset);
        $ok = db_query("INSERT INTO `$mysqlMainDb`.`forum_post`
                        (`id`, `topic_id`, `post_text`, `poster_id`, `post_time`, `poster_ip`)
                        SELECT p.post_id + $forumpostid_offset, p.topic_id + $forumtopicid_offset,
                               pt.post_text, p.poster_id, p.post_time, p.poster_ip
                        FROM posts p, posts_text pt
                        WHERE p.post_id = pt.post_id") && $ok;
        $ok = db_query("UPDATE `$mysqlMainDb`.`forum_topic` ft, `$mysqlMainDb`.`forum` f
                                       SET f.last_post_id = f.last_post_id + $forumpostid_offset,
                                           ft.last_post_id = ft.last_post_id + $forumpostid_offset
                                       WHERE ft.forum_id = f.id AND
                                             course_id = $course_id") && $ok;
        $ok = db_query("UPDATE `$mysqlMainDb`.forum_notify
                                       SET cat_id = cat_id + $forumcatid_offset,
                                           forum_id = forum_id + $forumid_offset,
                                           topic_id = topic_id + $forumtopicid_offset
                                       WHERE course_id = $course_id") && $ok;
        if ($ok) {
            foreach (array('posts', 'topics', 'forums', 'catagories') as $table) {
                db_query('DROP TABLE ' . $table);
            }
        }
    }

    $video_map = array();
    // move video to central db and drop table
    if (mysql_table_exists($code, 'video')) {

        list($videoid_offset) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.video"));
        $videoid_offset = (!$videoid_offset) ? 0 : intval($videoid_offset);

        if ($return_mapping) {
            $result = db_query("SELECT * FROM video ORDER by id");
            while ($row = mysql_fetch_array($result)) {
                $oldid = intval($row['id']);
                $newid = $oldid + $videoid_offset;
                $video_map[$oldid] = $newid;
            }
        }

        $ok = db_query("INSERT INTO `$mysqlMainDb`.video
                        (`id`, `course_id`, `path`, `url`, `title`, `description`, `creator`, `publisher`, `date`, `visible`, `public`)
                        SELECT `id` + $videoid_offset, $course_id, `path`, `url`, `titre`, `description`,
                               `creator`, `publisher`, `date`, `visible`, `public` FROM video ORDER by id");

        if (false !== $ok)
            db_query("DROP TABLE video");

        db_query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + $videoid_offset
                            WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'video'");
    }

    $videolink_map = array();
    // move videolinks to central db and drop table
    if (mysql_table_exists($code, 'videolinks')) {

        list($linkid_offset) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.videolink"));
        $linkid_offset = (!$linkid_offset) ? 0 : intval($linkid_offset);

        if ($return_mapping) {
            $result = db_query("SELECT * FROM videolinks ORDER by id");
            while ($row = mysql_fetch_array($result)) {
                $oldid = intval($row['id']);
                $newid = $oldid + $linkid_offset;
                $videolink_map[$oldid] = $newid;
            }
        }

        $ok = db_query("INSERT INTO `$mysqlMainDb`.videolink
                        (`id`, `course_id`, `url`, `title`, `description`, `creator`, `publisher`, `date`, `visible`, `public`)
                        SELECT `id` + $linkid_offset, $course_id, `url`, `titre`, `description`, `creator`,
                               `publisher`, `date`, `visible`, `public` FROM videolinks ORDER by id");

        if (false !== $ok)
            db_query("DROP TABLE videolinks");

        db_query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + $linkid_offset
                            WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'videolink'");
    }

    // move dropbox to central db and drop tables
    if (mysql_table_exists($code, 'dropbox_file') && mysql_table_exists($code, 'dropbox_person') &&
            mysql_table_exists($code, 'dropbox_post')) {

        list($fileid_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM `$mysqlMainDb`.dropbox_file"));
        $fileid_offset = (!$fileid_offset) ? 0 : intval($fileid_offset);

        db_query("CREATE TEMPORARY TABLE dropbox_map AS
                   SELECT old.id AS old_id, old.id + $fileid_offset AS new_id
                     FROM dropbox_file AS old ORDER by id");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.dropbox_file
                        (`id`, `course_id`, `uploaderId`, `filename`, `filesize`, `title`,
                         `description`, `uploadDate`, `lastUploadDate`)
                        SELECT `id` + $fileid_offset, $course_id, `uploaderId`, `filename`,
                               `filesize`, `title`, `description`, `uploadDate`,
                               `lastUploadDate` FROM dropbox_file ORDER BY id") && $ok;

        $ok = db_query("INSERT INTO `$mysqlMainDb`.dropbox_person
                         (`fileId`, `personId`)
                         SELECT DISTINCT dropbox_map.new_id, dropbox_person.personId
                           FROM dropbox_person, dropbox_map
                          WHERE dropbox_person.fileId = dropbox_map.old_id
                          ORDER BY dropbox_person.fileId") && $ok;

        $ok = db_query("INSERT INTO `$mysqlMainDb`.dropbox_post
                         (`fileId`, `recipientId`)
                         SELECT DISTINCT dropbox_map.new_id, dropbox_post.recipientId
                           FROM dropbox_post, dropbox_map
                          WHERE dropbox_post.fileId = dropbox_map.old_id
                          ORDER BY dropbox_post.fileId") && $ok;

        db_query("DROP TEMPORARY TABLE dropbox_map");

        if (false !== $ok) {
            db_query("DROP TABLE dropbox_file");
            db_query("DROP TABLE dropbox_person");
            db_query("DROP TABLE dropbox_post");
        }
    }

    $lp_map = array();
    // move learn path to central db and drop tables
    if (mysql_table_exists($code, 'lp_learnPath') && mysql_table_exists($code, 'lp_module') &&
            mysql_table_exists($code, 'lp_asset') && mysql_table_exists($code, 'lp_rel_learnPath_module') &&
            mysql_table_exists($code, 'lp_user_module_progress')) {

        // first change `visibility` field name and type to lp_learnPath table
        db_query("ALTER TABLE lp_learnPath CHANGE `visibility` `visibility` VARCHAR(5)");
        db_query("UPDATE lp_learnPath SET visibility = '1' WHERE visibility = 'SHOW'");
        db_query("UPDATE lp_learnPath SET visibility = '0' WHERE visibility = 'HIDE'");
        db_query("ALTER TABLE lp_learnPath CHANGE `visibility` `visible` TINYINT(4)");

        // first change `visibility` field name and type to lp_rel_learnPath_module table
        db_query("ALTER TABLE lp_rel_learnPath_module CHANGE `visibility` `visibility` VARCHAR(5)");
        db_query("UPDATE lp_rel_learnPath_module SET visibility = '1' WHERE visibility = 'SHOW'");
        db_query("UPDATE lp_rel_learnPath_module SET visibility = '0' WHERE visibility = 'HIDE'");
        db_query("ALTER TABLE lp_rel_learnPath_module CHANGE `visibility` `visible` TINYINT(4)");

        $asset_map = array();
        $rel_map = array();
        $rel_map[0] = 0;

        // ----- lp_learnPath DB Table ----- //
        list($lpid_offset) = mysql_fetch_row(db_query("SELECT MAX(learnPath_id) FROM `$mysqlMainDb`.lp_learnPath"));
        $lpid_offset = (!$lpid_offset) ? 0 : intval($lpid_offset);

        if ($return_mapping) {
            $result = db_query("SELECT * FROM lp_learnPath ORDER by learnPath_id");
            while ($row = mysql_fetch_array($result)) {
                $oldid = intval($row['learnPath_id']);
                $newid = $oldid + $lpid_offset;
                $lp_map[$oldid] = $newid;
            }
        }

        db_query("CREATE TEMPORARY TABLE lp_map AS
                   SELECT old.learnPath_id AS old_id, old.learnPath_id + $lpid_offset AS new_id
                     FROM lp_learnPath AS old ORDER by learnPath_id");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.lp_learnPath
                         (`learnPath_id`, `course_id`, `name`, `comment`, `lock`, `visible`, `rank`)
                         SELECT `learnPath_id` + $lpid_offset, $course_id, `name`, `comment`, `lock`,
                         `visible`, `rank` FROM lp_learnPath ORDER BY learnPath_id");

        // ----- lp_module DB Table ----- //
        list($moduleid_offset) = mysql_fetch_row(db_query("SELECT max(module_id) FROM `$mysqlMainDb`.lp_module"));
        $moduleid_offset = (!$moduleid_offset) ? 0 : intval($moduleid_offset);

        db_query("CREATE TEMPORARY TABLE module_map AS
                   SELECT old.module_id AS old_id, old.module_id + $moduleid_offset AS new_id
                     FROM lp_module AS old ORDER by module_id");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.lp_module
                         (`module_id`, `course_id`, `name`, `comment`, `accessibility`, `startAsset_id`,
                          `contentType`, `launch_data`)
                         SELECT `module_id` + $moduleid_offset, $course_id, `name`, `comment`,
                                `accessibility`, `startAsset_id`, `contentType`, `launch_data`
                           FROM lp_module ORDER by module_id") && $ok;

        // ----- lp_asset DB Table ----- //
        list($assetid_offset) = mysql_fetch_row(db_query("SELECT max(asset_id) FROM `$mysqlMainDb`.lp_asset"));
        $assetid_offset = (!$assetid_offset) ? 0 : intval($assetid_offset);

        $result = db_query("SELECT * FROM lp_asset ORDER by asset_id");

        while ($row = mysql_fetch_array($result)) {
            $oldid = intval($row['asset_id']);
            $newid = $oldid + $assetid_offset;
            $asset_map[$oldid] = $newid;
        }

        $ok = db_query("INSERT INTO `$mysqlMainDb`.lp_asset
                         (`asset_id`, `module_id`, `path`, `comment`)
                         SELECT DISTINCT lp_asset.asset_id + $assetid_offset, module_map.new_id,
                                lp_asset.path, lp_asset.comment
                           FROM lp_asset, module_map
                          WHERE lp_asset.module_id = module_map.old_id
                           ORDER BY lp_asset.asset_id") && $ok;

        foreach ($asset_map as $key => $value) {
            $ok = db_query("UPDATE `$mysqlMainDb`.lp_module SET `startAsset_id` = $value
                             WHERE `course_id` = $course_id AND `startAsset_id` = $key") && $ok;
        }

        // ----- lp_rel_learnPath_module DB Table ----- //
        list($relid_offset) = mysql_fetch_row(db_query("SELECT max(learnPath_module_id) FROM `$mysqlMainDb`.lp_rel_learnPath_module"));
        $relid_offset = (!$relid_offset) ? 0 : intval($relid_offset);

        $result = db_query("SELECT * FROM lp_rel_learnPath_module ORDER by learnPath_module_id");

        while ($row = mysql_fetch_array($result)) {
            $oldid = intval($row['learnPath_module_id']);
            $newid = $oldid + $relid_offset;
            $rel_map[$oldid] = $newid;
        }

        db_query("CREATE TEMPORARY TABLE rel_map AS
                   SELECT old.learnPath_module_id AS old_id, old.learnPath_module_id + $relid_offset AS new_id
                     FROM lp_rel_learnPath_module AS old ORDER by learnPath_module_id");
        db_query("INSERT INTO rel_map (old_id, new_id) VALUES (0, 0)");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.lp_rel_learnPath_module
                         (`learnPath_module_id`, `learnPath_id`, `module_id`, `lock`, `visible`, `specificComment`,
                          `rank`, `parent`, `raw_to_pass`)
                         SELECT DISTINCT lp_rel_learnPath_module.learnPath_module_id + $relid_offset,
                                lp_map.new_id, module_map.new_id, lp_rel_learnPath_module.lock,
                                lp_rel_learnPath_module.visible, lp_rel_learnPath_module.specificComment,
                                lp_rel_learnPath_module.rank, lp_rel_learnPath_module.parent,
                                lp_rel_learnPath_module.raw_to_pass
                           FROM lp_rel_learnPath_module, lp_map, module_map
                          WHERE lp_rel_learnPath_module.learnPath_id = lp_map.old_id
                            AND lp_rel_learnPath_module.module_id = module_map.old_id
                          ORDER BY lp_rel_learnPath_module.learnPath_module_id") && $ok;

        foreach ($rel_map as $key => $value) {
            $ok = db_query("UPDATE `$mysqlMainDb`.lp_rel_learnPath_module SET `parent` = $value
                             WHERE `learnPath_id` IN (SELECT learnPath_id FROM `$mysqlMainDb`.lp_learnPath WHERE course_id = $course_id)
                               AND `parent` = $key") && $ok;
        }

        // ----- lp_user_module_progress DB Table ----- //
        list($lumid_offset) = mysql_fetch_row(db_query("SELECT max(user_module_progress_id) FROM `$mysqlMainDb`.lp_user_module_progress"));
        $lumid_offset = (!$lumid_offset) ? 0 : intval($lumid_offset);

        $ok = db_query("INSERT INTO `$mysqlMainDb`.lp_user_module_progress
                         (`user_module_progress_id`, `user_id`, `learnPath_module_id`, `learnPath_id`,
                          `lesson_location`, `lesson_status`, `entry`, `raw`, `scoreMin`, `scoreMax`,
                          `total_time`, `session_time`, `suspend_data`, `credit`)
                         SELECT DISTINCT lp_user_module_progress.user_module_progress_id + $lumid_offset,
                                lp_user_module_progress.user_id, rel_map.new_id, lp_map.new_id,
                                lp_user_module_progress.lesson_location,
                                lp_user_module_progress.lesson_status,
                                lp_user_module_progress.entry,
                                lp_user_module_progress.raw,
                                lp_user_module_progress.scoreMin,
                                lp_user_module_progress.scoreMax,
                                lp_user_module_progress.total_time,
                                lp_user_module_progress.session_time,
                                lp_user_module_progress.suspend_data,
                                lp_user_module_progress.credit
                           FROM lp_user_module_progress, rel_map, lp_map
                          WHERE lp_user_module_progress.learnPath_module_id = rel_map.old_id
                            AND lp_user_module_progress.learnPath_id = lp_map.old_id
                          ORDER BY lp_user_module_progress.user_module_progress_id") && $ok;

        db_query("DROP TEMPORARY TABLE lp_map");
        db_query("DROP TEMPORARY TABLE module_map");
        db_query("DROP TEMPORARY TABLE rel_map");

        if (false !== $ok) {
            $scormPkgDir = $webDir . '/courses/' . $code . '/scormPackages';
            $pathTkn = 'path_';
            $pathids = array();

            if (file_exists($scormPkgDir) && is_dir($scormPkgDir)) {
                if (($handle = opendir($scormPkgDir))) {
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry != "." && $entry != ".." && substr($entry, 0, strlen($pathTkn)) === $pathTkn && is_dir($scormPkgDir . '/' . $entry))
                            $pathids[] = substr($entry, strlen($pathTkn), strlen($entry));
                    }
                    rsort($pathids);
                    foreach ($pathids as $pathid)
                        rename($scormPkgDir . '/' . $pathTkn . $pathid, $scormPkgDir . '/' . $pathTkn . ($pathid + $lpid_offset));
                    closedir($handle);
                }
            }

            db_query("DROP TABLE lp_learnPath");
            db_query("DROP TABLE lp_module");
            db_query("DROP TABLE lp_asset");
            db_query("DROP TABLE lp_rel_learnPath_module");
            db_query("DROP TABLE lp_user_module_progress");
        }

        db_query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + $lpid_offset
                            WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'lp'");
    }

    $wiki_map = array();
    // move wiki to central db and drop tables
    if (mysql_table_exists($code, 'wiki_properties') && mysql_table_exists($code, 'wiki_acls') &&
            mysql_table_exists($code, 'wiki_pages') && mysql_table_exists($code, 'wiki_pages_content')) {

        // ----- wiki_properties and wiki_acls DB Tables ----- //
        list($wikiid_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM `$mysqlMainDb`.wiki_properties"));
        $wikiid_offset = (!$wikiid_offset) ? 0 : intval($wikiid_offset);

        if ($return_mapping) {
            $result = db_query("SELECT * FROM wiki_properties ORDER by id");
            while ($row = mysql_fetch_array($result)) {
                $oldid = intval($row['id']);
                $newid = $oldid + $wikiid_offset;
                $wiki_map[$oldid] = $newid;
            }
        }

        db_query("CREATE TEMPORARY TABLE wiki_map AS
                   SELECT old.id AS old_id, old.id + $wikiid_offset AS new_id
                     FROM wiki_properties AS old ORDER by id");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.wiki_properties
                         (`id`, `course_id`, `title`, `description`, `group_id`)
                         SELECT `id` + $wikiid_offset, $course_id, `title`, `description`, `group_id`
                           FROM wiki_properties ORDER BY id");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.wiki_acls
                         (`wiki_id`, `flag`, `value`)
                         SELECT DISTINCT wiki_map.new_id, wiki_acls.flag, wiki_acls.value
                           FROM wiki_acls, wiki_map
                          WHERE wiki_acls.wiki_id = wiki_map.old_id
                          ORDER BY wiki_acls.wiki_id") && $ok;

        // ----- wiki_pages DB Table ----- //
        list($wikipageid_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM `$mysqlMainDb`.wiki_pages"));
        $wikipageid_offset = (!$wikipageid_offset) ? 0 : intval($wikipageid_offset);

        $result = db_query("SELECT * FROM wiki_pages ORDER by id");
        $wiki_page_map = array();
        while ($row = mysql_fetch_array($result)) {
            $oldid = intval($row['id']);
            $newid = $oldid + $wikipageid_offset;
            $wiki_page_map[$oldid] = $newid;
        }

        db_query("CREATE TEMPORARY TABLE wikipage_map AS
                   SELECT old.id AS old_id, old.id + $wikipageid_offset AS new_id
                     FROM wiki_pages AS old ORDER by id");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.wiki_pages
                         (`id`, `wiki_id`, `owner_id`, `title`, `ctime`, `last_version`, `last_mtime`)
                         SELECT DISTINCT wiki_pages.id + $wikipageid_offset, wiki_map.new_id,
                                wiki_pages.owner_id, wiki_pages.title, wiki_pages.ctime,
                                wiki_pages.last_version, wiki_pages.last_mtime
                           FROM wiki_pages, wiki_map
                          WHERE wiki_pages.wiki_id = wiki_map.old_id
                          ORDER BY wiki_pages.id") && $ok;

        // ----- wiki_pages_content DB Table ----- //
        list($wikipagecontentid_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM `$mysqlMainDb`.wiki_pages_content"));
        $wikipagecontentid_offset = (!$wikipagecontentid_offset) ? 0 : intval($wikipagecontentid_offset);

        $ok = db_query("INSERT INTO `$mysqlMainDb`.wiki_pages_content
                         (`id`, `pid`, `editor_id`, `mtime`, `content`)
                         SELECT DISTINCT wiki_pages_content.id + $wikipagecontentid_offset,
                                wikipage_map.new_id, wiki_pages_content.editor_id,
                                wiki_pages_content.mtime, wiki_pages_content.content
                           FROM wiki_pages_content, wikipage_map
                          WHERE wiki_pages_content.pid = wikipage_map.old_id
                          ORDER BY wiki_pages_content.id") && $ok;

        db_query("DROP TEMPORARY TABLE wiki_map");
        db_query("DROP TEMPORARY TABLE wikipage_map");

        if (false !== $ok) {
            db_query("DROP TABLE wiki_properties");
            db_query("DROP TABLE wiki_acls");
            db_query("DROP TABLE wiki_pages");
            db_query("DROP TABLE wiki_pages_content");
        }

        db_query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + $wikiid_offset
                            WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'wiki'");
    }


    // move polls to central db and drop tables
    if (mysql_table_exists($code, 'poll') && mysql_table_exists($code, 'poll_answer_record') &&
            mysql_table_exists($code, 'poll_question') && mysql_table_exists($code, 'poll_question_answer')) {

        // ----- poll DB Table ----- //
        list($pollid_offset) = mysql_fetch_row(db_query("SELECT max(pid) FROM `$mysqlMainDb`.poll"));
        $pollid_offset = (!$pollid_offset) ? 0 : intval($pollid_offset);

        db_query("CREATE TEMPORARY TABLE poll_map AS
                   SELECT old.pid AS old_id, old.pid + $pollid_offset AS new_id
                     FROM poll AS old ORDER by pid");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.poll
                         (`pid`, `course_id`, `creator_id`, `name`, `creation_date`, `start_date`, `end_date`, `active`)
                         SELECT `pid` + $pollid_offset, $course_id, `creator_id`, `name`, `creation_date`, `start_date`,
                                `end_date`, `active`
                           FROM poll ORDER BY pid");

        // ----- poll_question DB Table ----- //
        list($pollquestionid_offset) = mysql_fetch_row(db_query("SELECT max(pqid) FROM `$mysqlMainDb`.poll_question"));
        $pollquestionid_offset = (!$pollquestionid_offset) ? 0 : intval($pollquestionid_offset);

        db_query("CREATE TEMPORARY TABLE pollquestion_map AS
                   SELECT old.pqid AS old_id, old.pqid + $pollquestionid_offset AS new_id
                     FROM poll_question AS old ORDER by pqid");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.poll_question
                         (`pqid`, `pid`, `question_text`, `qtype`)
                         SELECT DISTINCT poll_question.pqid + $pollquestionid_offset, poll_map.new_id,
                                poll_question.question_text, poll_question.qtype
                           FROM poll_question, poll_map
                          WHERE poll_question.pid = poll_map.old_id
                          ORDER BY poll_question.pqid") && $ok;

        // ----- poll_question_answer DB Table ----- //
        list($pollanswerid_offset) = mysql_fetch_row(db_query("SELECT max(pqaid) FROM `$mysqlMainDb`.poll_question_answer"));
        $pollanswerid_offset = (!$pollanswerid_offset) ? 0 : intval($pollanswerid_offset);

        db_query("CREATE TEMPORARY TABLE pollanswer_map AS
                   SELECT old.pqaid AS old_id, old.pqaid + $pollanswerid_offset AS new_id
                     FROM poll_question_answer AS old ORDER by pqaid");
        db_query("INSERT INTO pollanswer_map (`old_id`, `new_id`) VALUES (0, 0)");
        db_query("INSERT INTO pollanswer_map (`old_id`, `new_id`) VALUES (-1, -1)");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.poll_question_answer
                         (`pqaid`, `pqid`, `answer_text`)
                         SELECT DISTINCT poll_question_answer.pqaid + $pollanswerid_offset,
                                pollquestion_map.new_id, poll_question_answer.answer_text
                           FROM poll_question_answer, pollquestion_map
                          WHERE poll_question_answer.pqid = pollquestion_map.old_id
                          ORDER BY poll_question_answer.pqaid") && $ok;

        // ----- poll_answer_record DB Table ----- //
        list($pollrecordid_offset) = mysql_fetch_row(db_query("SELECT max(arid) FROM `$mysqlMainDb`.poll_answer_record"));
        $pollrecordid_offset = (!$pollrecordid_offset) ? 0 : intval($pollrecordid_offset);

        $ok = db_query("INSERT INTO `$mysqlMainDb`.poll_answer_record
                         (`arid`, `pid`, `qid`, `aid`, `answer_text`, `user_id`, `submit_date`)
                         SELECT DISTINCT poll_answer_record.arid + $pollrecordid_offset,
                                poll_map.new_id, pollquestion_map.new_id, pollanswer_map.new_id,
                                poll_answer_record.answer_text, poll_answer_record.user_id,
                                poll_answer_record.submit_date
                           FROM poll_answer_record, poll_map, pollquestion_map, pollanswer_map
                          WHERE poll_answer_record.pid = poll_map.old_id
                            AND poll_answer_record.qid = pollquestion_map.old_id
                            AND poll_answer_record.aid = pollanswer_map.old_id
                          ORDER BY poll_answer_record.arid") && $ok;

        db_query("DROP TEMPORARY TABLE poll_map");
        db_query("DROP TEMPORARY TABLE pollquestion_map");
        db_query("DROP TEMPORARY TABLE pollanswer_map");

        if (false !== $ok) {
            db_query("DROP TABLE poll");
            db_query("DROP TABLE poll_answer_record");
            db_query("DROP TABLE poll_question");
            db_query("DROP TABLE poll_question_answer");
        }
    }

    $assignments_map = array();
    // move assignments to central db and drop tables
    if (mysql_table_exists($code, 'assignments') && mysql_table_exists($code, 'assignment_submit')) {

        // ----- assigments DB Table ----- //
        list($assignmentid_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM `$mysqlMainDb`.assignment"));
        $assignmentid_offset = (!$assignmentid_offset) ? 0 : intval($assignmentid_offset);

        if ($return_mapping) {
            $result = db_query("SELECT * FROM assignments ORDER by id");
            while ($row = mysql_fetch_array($result)) {
                $oldid = intval($row['id']);
                $newid = $oldid + $assignmentid_offset;
                $assignments_map[$oldid] = $newid;
            }
        }

        db_query("CREATE TEMPORARY TABLE assignments_map AS
                   SELECT old.id AS old_id, old.id + $assignmentid_offset AS new_id
                     FROM assignments AS old ORDER by id");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.assignment
                         (`id`, `course_id`, `title`, `description`, `comments`, `deadline`, `submission_date`,
                          `active`, `secret_directory`, `group_submissions`)
                         SELECT `id` + $assignmentid_offset, $course_id, `title`, `description`, `comments`,
                                `deadline`, `submission_date`, `active`, `secret_directory`, `group_submissions`
                           FROM assignments ORDER BY id");

        // ----- assigments DB Table ----- //
        list($assignmentsubmitid_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM `$mysqlMainDb`.assignment_submit"));
        $assignmentsubmitid_offset = (!$assignmentsubmitid_offset) ? 0 : intval($assignmentsubmitid_offset);

        db_query("UPDATE assignment_submit SET group_id = 0 WHERE group_id IS NULL");
        $ok = db_query("INSERT INTO `$mysqlMainDb`.assignment_submit
                         (`id`, `uid`, `assignment_id`, `submission_date`, `submission_ip`, `file_path`, `file_name`,
                          `comments`, `grade`, `grade_comments`, `grade_submission_date`, `grade_submission_ip`,
                          `group_id`)
                         SELECT DISTINCT assignment_submit.id + $assignmentsubmitid_offset,
                                assignment_submit.uid, assignments_map.new_id,
                                assignment_submit.submission_date, assignment_submit.submission_ip,
                                assignment_submit.file_path, assignment_submit.file_name,
                                assignment_submit.comments, assignment_submit.grade, assignment_submit.grade_comments,
                                assignment_submit.grade_submission_date, assignment_submit.grade_submission_ip,
                                assignment_submit.group_id
                           FROM assignment_submit, assignments_map
                          WHERE assignment_submit.assignment_id = assignments_map.old_id
                          ORDER BY assignment_submit.id") && $ok;

        db_query("DROP TEMPORARY TABLE assignments_map");

        if (false !== $ok) {
            db_query("DROP TABLE assignments");
            db_query("DROP TABLE assignment_submit");
        }

        db_query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + $assignmentid_offset
                            WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'work'");
    }

    // move agenda to central db and drop table
    if (mysql_table_exists($code, 'agenda')) {

        // ----- agenda DB Table ----- //
        db_query("UPDATE `$code`.agenda SET visibility = '1' WHERE visibility = 'v'");
        db_query("UPDATE `$code`.agenda SET visibility = '0' WHERE visibility = 'i'");

        list($agendaid_offset) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.agenda"));
        $agendaid_offset = (!$agendaid_offset) ? 0 : intval($agendaid_offset);

        $ok = db_query("INSERT INTO `$mysqlMainDb`.agenda
                         (`id`, `course_id`, `title`, `content`, `start`, `duration`, `visible`)
                         SELECT `id` + $agendaid_offset, $course_id, `titre`, `contenu`, CONCAT(day,' ',hour), `lasting`,
                                `visibility` FROM agenda ORDER BY id");

        if (false !== $ok) {
            db_query("DROP TABLE agenda");
        }
    }

    $exercise_map = array();
    $exercise_map[0] = 0;
    // move exercises to central db and drop tables
    if (mysql_table_exists($code, 'exercices') &&
            mysql_table_exists($code, 'exercise_user_record') &&
            mysql_table_exists($code, 'questions') &&
            mysql_table_exists($code, 'reponses') &&
            mysql_table_exists($code, 'exercice_question')) {

        // ----- exercices DB Table ----- //
        list($exerciseid_offset) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.exercise"));
        $exerciseid_offset = (!$exerciseid_offset) ? 0 : intval($exerciseid_offset);

        if ($return_mapping) {
            $result = db_query("SELECT * FROM exercices ORDER by id");
            while ($row = mysql_fetch_array($result)) {
                $oldid = intval($row['id']);
                $newid = $oldid + $exerciseid_offset;
                $exercise_map[$oldid] = $newid;
            }
        }

        db_query("CREATE TEMPORARY TABLE exercise_map AS
                   SELECT old.id AS old_id, old.id + $exerciseid_offset AS new_id
                     FROM exercices AS old ORDER by id");
        db_query("INSERT INTO exercise_map (`old_id`, `new_id`) VALUES (0, 0)");

        db_query("UPDATE exercices SET active = 0 WHERE active IS NULL");
        $ok = db_query("INSERT INTO `$mysqlMainDb`.exercise
                         (`id`, `course_id`, `title`, `description`, `type`, `start_date`, `end_date`,
                          `time_constraint`, `attempts_allowed`, `random`, `active`, `public`, `results`, `score`)
                         SELECT `id` + $exerciseid_offset, $course_id, `titre`, `description`, `type`,
                                `StartDate`, `EndDate`, `TimeConstrain`, `AttemptsAllowed`, `random`,
                                `active`, `public`, `results`, `score`
                           FROM exercices ORDER BY id") && $ok;

        // ----- exercise_user_record DB Table ----- //
        list($eurid_offset) = mysql_fetch_row(db_query("SELECT max(eurid) FROM `$mysqlMainDb`.exercise_user_record"));
        $eurid_offset = (!$eurid_offset) ? 0 : intval($eurid_offset);

        $ok = db_query("INSERT INTO `$mysqlMainDb`.exercise_user_record
                         (`eurid`, `eid`, `uid`, `record_start_date`, `record_end_date`, `total_score`,
                          `total_weighting`, `attempt`)
                         SELECT DISTINCT exercise_user_record.eurid + $eurid_offset, exercise_map.new_id,
                                exercise_user_record.uid, exercise_user_record.RecordStartDate,
                                exercise_user_record.RecordEndDate, exercise_user_record.TotalScore,
                                exercise_user_record.TotalWeighting, exercise_user_record.attempt
                           FROM exercise_user_record, exercise_map
                          WHERE exercise_user_record.eid = exercise_map.old_id
                          ORDER BY exercise_user_record.eurid") && $ok;

        // ----- questions DB Table ----- //
        list($questionid_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM `$mysqlMainDb`.exercise_question"));
        $questionid_offset = (!$questionid_offset) ? 0 : intval($questionid_offset);

        db_query("CREATE TEMPORARY TABLE question_map AS
                   SELECT old.id AS old_id, old.id + $questionid_offset AS new_id
                     FROM questions AS old ORDER by id");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.exercise_question
                         (`id`, `course_id`, `question`, `description`, `weight`, `q_position`, `type`)
                         SELECT `id` + $questionid_offset, $course_id, `question`, `description`, `ponderation`,
                                `q_position`, `type`
                           FROM questions ORDER BY id") && $ok;

        // ----- reponses DB Table ----- //
        list($answerid_offset) = mysql_fetch_row(db_query("SELECT max(id) FROM `$mysqlMainDb`.exercise_answer"));
        $answerid_offset = (!$answerid_offset) ? 0 : intval($answerid_offset);

        $ok = db_query("INSERT INTO `$mysqlMainDb`.exercise_answer
                         (`id`, `question_id`, `answer`, `correct`, `comment`, `weight`, `r_position`)
                         SELECT DISTINCT reponses.id + $answerid_offset, question_map.new_id,
                                reponses.reponse, reponses.correct, reponses.comment, reponses.ponderation,
                                reponses.r_position
                           FROM reponses, question_map
                          WHERE reponses.question_id = question_map.old_id
                          ORDER BY reponses.id") && $ok;

        $ok = db_query("INSERT INTO `$mysqlMainDb`.exercise_with_questions
                         (`question_id`, `exercise_id`)
                         SELECT DISTINCT question_map.new_id, exercise_map.new_id
                           FROM exercice_question, exercise_map, question_map
                          WHERE exercice_question.exercice_id = exercise_map.old_id
                            AND exercice_question.question_id = question_map.old_id") && $ok;

        db_query("DROP TEMPORARY TABLE exercise_map");
        db_query("DROP TEMPORARY TABLE question_map");

        if (false !== $ok) {
            db_query("DROP TABLE exercices");
            db_query("DROP TABLE exercise_user_record");
            db_query("DROP TABLE questions");
            db_query("DROP TABLE reponses");
            db_query("DROP TABLE exercice_question");
        }

        db_query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                     SET res_id = res_id + $exerciseid_offset
                   WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'exercise'");
        db_query("UPDATE `$mysqlMainDb`.lp_module AS module, `$mysqlMainDb`.lp_asset AS asset
                     SET path = path + $exerciseid_offset
                   WHERE module.startAsset_id = asset.asset_id AND course_id = $course_id AND contentType = 'EXERCISE'");
    }

    // move table `actions`, `actions_summary`, `login` in main DB
    $result = db_query("SELECT `user_id`, `module_id`, $course_id AS `course_id`,
                               COUNT(`id`) AS `hits`, SUM(`duration`) AS `duration`,
                               DATE(`date_time`) AS `day`
                            FROM `actions`
                            GROUP BY DATE(`date_time`), `user_id`, `module_id`");
    while ($row = mysql_fetch_assoc($result)) {
        db_query("INSERT INTO `$mysqlMainDb`.`actions_daily`
                        (`id`, `user_id`, `module_id`, `course_id`, `hits`, `duration`, `day`, `last_update`) 
                        VALUES 
                        (NULL, $row[user_id], $row[module_id], $row[course_id], $row[hits], $row[duration], '$row[day]', NOW())");
    }

    db_query("INSERT INTO `$mysqlMainDb`.actions_summary
                (module_id, visits, start_date, end_date, duration, course_id)
                SELECT module_id, visits, start_date, end_date, duration, $course_id
                FROM actions_summary", $code);

    db_query("INSERT INTO `$mysqlMainDb`.logins
                (user_id, ip, date_time, course_id)
                SELECT user_id, ip, date_time, $course_id
                FROM logins", $code);


    // -------------------------------------------------------
    // Move table `accueil` to table `course_module` in main DB
    // -------------------------------------------------------
    // external links are moved to table `links` with category = -1
    $q1 = db_query("INSERT IGNORE INTO `$mysqlMainDb`.link
                               (course_id, url, title, category, description)
                               SELECT $course_id, lien, rubrique, -1, '' FROM accueil
                                      WHERE define_var = 'HTML_PAGE' OR
                                            image = 'external_link'", $code);

    $q2 = db_query("INSERT IGNORE INTO `$mysqlMainDb`.course_module
                        (module_id, visible, course_id)
                SELECT id, visible, $course_id FROM accueil
                WHERE define_var NOT IN ('MODULE_ID_TOOLADMIN',
                                         'MODULE_ID_COURSEINFO',
                                         'MODULE_ID_USERS',
                                         'MODULE_ID_USAGE',
                                         'HTML_PAGE') AND
                      image <> 'external_link'", $code);


    if ($q1 and $q2) { // if everything ok drop course db
        echo $langUpgradeCourseDone;
        // Do not drop database yet so we can run upgrade many times
        //db_query("DROP DATABASE $code");
    }

    // index all courses
    mysql_select_db($mysqlMainDb);
    global $webDir;
    require_once 'modules/search/indexer.class.php';
    $idx = new Indexer();
    $idx->storeAllByCourse($course_id);

    // NOTE: no code must occur after this statement or else course upgrade will be broken
    if ($return_mapping) {
        return array($video_map, $videolinks_map, $lp_map, $wiki_map, $assignments_map, $exercise_map);
    }
}

function upgrade_course_2_8($code, $lang, $extramessage = '') {

    global $langUpgCourse, $global_messages;

    mysql_select_db($code);
    echo "<hr><p>$langUpgCourse <b>$code</b> (2.8) $extramessage<br>";
    flush();

    mysql_field_exists(null, 'exercices', 'public') or
            db_query("ALTER TABLE `exercices` ADD `public` TINYINT(4) NOT NULL DEFAULT 1 AFTER `active`");
    mysql_field_exists(null, 'video', 'visible') or
            db_query("ALTER TABLE `video` ADD `visible` TINYINT(4) NOT NULL DEFAULT 1 AFTER `date`");
    mysql_field_exists(null, 'video', 'public') or
            db_query("ALTER TABLE `video` ADD `public` TINYINT(4) NOT NULL DEFAULT 1");
    mysql_field_exists(null, 'videolinks', 'visible') or
            db_query("ALTER TABLE `videolinks` ADD `visible` TINYINT(4) NOT NULL DEFAULT 1 AFTER `date`");
    mysql_field_exists(null, 'videolinks', 'public') or
            db_query("ALTER TABLE `videolinks` ADD `public` TINYINT(4) NOT NULL DEFAULT 1");
    if (mysql_index_exists('dropbox_file', 'UN_filename')) {
        db_query("ALTER TABLE dropbox_file DROP index UN_filename");
    }
    db_query("ALTER TABLE dropbox_file CHANGE description description VARCHAR(500)");
    db_query("UPDATE accueil SET rubrique = " . quote($global_messages['langDropBox'][$lang]) . "
                                    WHERE id = 16 AND define_var = 'MODULE_ID_DROPBOX'");
}

function upgrade_course_2_5($code, $lang, $extramessage = '') {

    global $langUpgCourse, $global_messages;

    mysql_select_db($code);
    echo "<hr><p>$langUpgCourse <b>$code</b> (2.5) $extramessage<br>";
    flush();

    db_query("UPDATE `accueil` SET `rubrique` = " .
            quote($global_messages['langVideo'][$lang]) . "
                        WHERE `define_var` = 'MODULE_ID_VIDEO'");

    db_query("ALTER TABLE `assignments`
                        CHANGE `deadline` `deadline` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    db_query("ALTER TABLE `assignments`
                        CHANGE `submission_date` `submission_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    db_query("ALTER TABLE `assignment_submit`
                        CHANGE `submission_date` `submission_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    db_query("ALTER TABLE `poll`
                        CHANGE `start_date` `start_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    db_query("ALTER TABLE `poll`
                        CHANGE `end_date` `end_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    db_query("ALTER TABLE `poll`
                        CHANGE `creation_date` `creation_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    db_query("ALTER TABLE `poll_answer_record`
                        CHANGE `submit_date` `submit_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    db_query("ALTER TABLE `exercices`
                        CHANGE `StartDate` `StartDate` DATETIME
                        DEFAULT NULL");

    db_query("ALTER TABLE `exercices`
                        CHANGE `EndDate` `EndDate` DATETIME
                        DEFAULT NULL");

    db_query("ALTER TABLE `lp_module`
                        CHANGE `contentType`
                        `contentType` ENUM('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK','MEDIA','MEDIALINK')");
}

function upgrade_course_2_4($code, $lang, $extramessage = '') {
    global $langUpgCourse, $mysqlMainDb, $global_messages, $webDir;

    $course_id = course_code_to_id($code);
    mysql_select_db($code);
    echo "<hr><p>$langUpgCourse <b>$code</b> (2.4) $extramessage<br>";
    flush();

    // not needed anymore
    delete_table('stat_accueil');
    delete_table('users');

    db_query("UPDATE accueil SET lien = '../../modules/course_info/infocours.php'
                                 WHERE id = 14 AND define_var = 'MODULE_ID_COURSEINFO'");
    db_query("UPDATE accueil SET rubrique = " .
            quote($global_messages['langCourseDescription'][$lang]) . "
                                        WHERE id = 20 AND define_var = 'MODULE_ID_DESCRIPTION'");
    db_query("UPDATE accueil SET lien = REPLACE(lien, ' \"target=_blank', '')
                                 WHERE lien LIKE '% \"target=_blank'");
    db_query("ALTER TABLE `poll_answer_record` CHANGE `answer_text` `answer_text` TEXT", $code);

    // move main documents to central table and if successful drop table
    if (mysql_table_exists($code, 'document')) {
        list($doc_id) = mysql_fetch_row(db_query("SELECT MAX(id) FROM `$mysqlMainDb`.document"));
        if (!$doc_id) {
            $doc_id = 1;
        }
        db_query("INSERT INTO `$mysqlMainDb`.document
                                (`id`, `course_id`, `subsystem`, `subsystem_id`, `path`, `filename`, `visibility`, `comment`,
                                 `category`, `title`, `creator`, `date`, `date_modified`, `subject`,
                                 `description`, `author`, `format`, `language`, `copyrighted`)
                                SELECT $doc_id + id, $course_id, 0, NULL, `path`, `filename`, `visibility`, `comment`,
                                       0 + `category`, `title`, `creator`, `date`, `date_modified`, `subject`,
                                       `description`, `author`, `format`, `language`, 0 + `copyrighted` FROM document") and
                db_query("DROP TABLE document");
        db_query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                                 SET res_id = res_id + $doc_id
                                 WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'doc'");
    }

    // move user group information to central tables and if successful drop original tables
    if (mysql_table_exists($code, 'group_properties')) {
        $ok = db_query("INSERT INTO `$mysqlMainDb`.`group_properties`
                                (`course_id`, `self_registration`, `private_forum`, `forum`, `documents`,
                                 `wiki`, `agenda`)
                                SELECT $course_id, `self_registration`, `private`, `forum`, `document`,
                                        `wiki`, `agenda` FROM group_properties");
        if ($ok) {
            db_query("DROP TABLE group_properties");
        }

        $ok = db_query("INSERT INTO `$mysqlMainDb`.`group`
                                (`course_id`, `name`, `description`, `forum_id`, `max_members`,
                                 `secret_directory`)
                                SELECT $course_id, `name`, `description`, `forumId`, `maxStudent`,
                                        `secretDirectory` FROM student_group");

        db_query("CREATE TEMPORARY TABLE group_map AS
                                SELECT old.id AS old_id, new.id AS new_id
                                        FROM student_group AS old, `$mysqlMainDb`.`group` AS new
                                        WHERE new.course_id = $course_id AND
                                              old.secretDirectory = new.secret_directory");

        $ok = db_query("INSERT INTO `$mysqlMainDb`.group_members
                                        (group_id, user_id, is_tutor, description)
                                        SELECT DISTINCT new_id, tutor, 1, ''
                                                FROM student_group, group_map
                                                WHERE student_group.id = group_map.old_id AND
                                                      tutor IS NOT NULL") && $ok;

        $ok = db_query("INSERT INTO `$mysqlMainDb`.group_members
                                        (group_id, user_id, is_tutor, description)
                                        SELECT DISTINCT new_id, user, 0, ''
                                                FROM user_group, group_map
                                                WHERE user_group.team = group_map.old_id") && $ok;

        db_query("DROP TEMPORARY TABLE group_map");

        $ok = move_group_documents_to_main_db($code, $course_id) && $ok;

        if ($ok) {
            db_query("DROP TABLE student_group");
            db_query("DROP TABLE user_group");
            db_query("DROP TABLE group_documents");
        }
    }

    // move links to central tables and if successful drop original tables
    if (mysql_table_exists($code, 'liens')) {
        db_query("INSERT INTO `$mysqlMainDb`.link
                                (`id`, `course_id`, `url`, `title`, `description`, `category`, `order`)
                                SELECT `id`, $course_id, `url`, `titre`, `description`, `category`, `ordre` FROM liens") and
                db_query("DROP TABLE liens");
        db_query("INSERT INTO `$mysqlMainDb`.link_category
                                (`id`, `course_id`, `name`, `description`, `order`)
                                SELECT `id`, $course_id, `categoryname`, `description`, `ordre` FROM link_categories") and
                db_query("DROP TABLE link_categories");
    }

    // upgrade acceuil for glossary
    if (accueil_tool_missing('MODULE_ID_GLOSSARY')) {
        db_query("INSERT IGNORE INTO accueil VALUES (
                                '17',
                                '{$global_messages['langGlossary'][$lang]}',
                                '../../modules/glossary/glossary.php',
                                'glossary',
                                '0',
                                '0',
                                '',
                                'MODULE_ID_GLOSSARY')", $code);
    }

    // upgrade acceuil for glossary
    if (accueil_tool_missing('MODULE_ID_EBOOK')) {
        db_query("INSERT IGNORE INTO accueil VALUES (
                                '18',
                                '{$global_messages['langEBook'][$lang]}',
                                '../../modules/ebook/index.php',
                                'ebook',
                                '0',
                                '0',
                                '',
                                'MODULE_ID_EBOOK')", $code);
    }
    // upgrade poll_question
    db_query("ALTER TABLE `poll_question` CHANGE `pqid` `pqid` BIGINT(12) NOT NULL AUTO_INCREMENT", $code);

    // move old chat files
    $courses_dir = "$webDir/courses/";
    foreach (array('chat.txt', 'tmpChatArchive.txt') as $f) {
        $old_chat = $courses_dir . $code . '.' . $f;
        if (file_exists($old_chat)) {
            rename($old_chat, $courses_dir . $code . '/' . $f);
        }
    }
}

function upgrade_course_2_3($code, $extramessage = '') {
    global $langUpgCourse;

    mysql_select_db($code);
    echo "<hr><p>$langUpgCourse <b>$code</b> (2.3) $extramessage<br>";
    flush();
    // upgrade exercises
    if (!mysql_field_exists("$code", 'exercices', 'score'))
        echo add_field('exercices', 'score', "TINYINT(1) NOT NULL DEFAULT '1'");
}

function upgrade_course_2_2($code, $lang, $extramessage = '') {
    global $langUpgCourse, $global_messages;

    mysql_select_db($code);
    echo "<hr><p>$langUpgCourse <b>$code</b> (2.2) $extramessage<br>";
    flush();

    // upgrade exercises
    db_query("ALTER TABLE `exercise_user_record`
		CHANGE `RecordStartDate` `RecordStartDate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'", $code);
    db_query("ALTER TABLE `exercise_user_record`
		CHANGE `RecordEndDate` `RecordEndDate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'", $code);
    if (!mysql_field_exists("$code", 'exercices', 'results'))
        echo add_field('exercices', 'results', "TINYINT(1) NOT NULL DEFAULT '1'");
    db_query("ALTER TABLE `questions` CHANGE `ponderation` `ponderation` FLOAT(11,2) NULL DEFAULT NULL");
    db_query("ALTER TABLE `reponses` CHANGE `ponderation` `ponderation` FLOAT(5,2) NULL DEFAULT NULL");
    // not needed anymore
    echo delete_table('mc_scoring');

    if (accueil_tool_missing('MODULE_ID_UNITS')) {
        mysql_query("INSERT INTO accueil VALUES (
                                '27',
                                '" . $global_messages['langCourseUnits'][$lang] . "',
                                '../../modules/units/index.php',
                                'description',
                                '2',
                                '0',
                                '',
                                'MODULE_ID_UNITS')");
    }
    // upgrade lp_module me to kainourio content type
    db_query("ALTER TABLE `lp_module`
		CHANGE `contentType` `contentType` ENUM('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK')", $code);
    db_query("ALTER TABLE `liens` CHANGE `url` `url` VARCHAR(255) DEFAULT NULL", $code);
    db_query("ALTER TABLE `liens` CHANGE `titre` `titre` VARCHAR(255) DEFAULT NULL", $code);
    // indexes creation
    add_index('optimize', 'lp_user_module_progress', 'user_id', 'learnPath_module_id');
    add_index('actionsindex', 'actions', 'module_id', 'date_time');
}

function upgrade_course_2_1_3($code, $extramessage = '') {
    global $mysqlMainDb, $langEncodeDropBoxDocuments, $langUpgCourse;

    mysql_select_db($code);

    echo "<hr><p>$langUpgCourse <b>$code</b> $extramessage<br>";
    flush();

    // added field visibility in agenda
    if (!mysql_field_exists("$code", 'agenda', 'visibility'))
        echo add_field('agenda', 'visibility', "CHAR(1) NOT NULL DEFAULT 'v'");

    // upgrade dropbox
    echo "$langEncodeDropBoxDocuments<br>";
    $d = db_query("SELECT id, filename, title FROM dropbox_file", $code);
    while ($dbox = mysql_fetch_array($d)) {
        encode_dropbox_documents($code, $dbox['id'], $dbox['filename'], $dbox['title']);
    }

    // upgrade lp_module me to kainourio content type
    db_query("ALTER TABLE `lp_module`
		CHANGE `contentType` `contentType` ENUM('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK')", $code);
}

// --------------------------------------------
// For older version  ( <= 2.1.3 )
// Upgrade course database and documents
// --------------------------------------------
function upgrade_course_old($code, $lang, $extramessage = '') {
    global $webDir, $mysqlMainDb, $tool_content, $langTable,
    $langNotMovedDir, $langToDir, $OK, $BAD, $langMoveIntroText,
    $langCorrectTableEntries, $langEncodeDocuments,
    $langEncodeGroupDocuments, $langEncodeDropBoxDocuments, $langUpgIndex, $langUpgNotIndex,
    $langCheckPerm, $langUpgFileNotRead, $langUpgFileNotModify,
    $langUpgNotChDir, $langUpgCourse;

    include 'messages_accueil.php';

    mysql_select_db($code);

    // ****************************
    // modify course_code/index.php
    // ****************************

    echo "<hr><p>$langUpgIndex <b>$code</b> $extramessage<br>";
    flush();

    // added field visibility in agenda
    if (!mysql_field_exists("$code", 'agenda', 'visibility'))
        echo add_field('agenda', 'visibility', "CHAR(1) NOT NULL DEFAULT 'v'");

    // upgrade dropbox
    echo "$langEncodeDropBoxDocuments<br>";
    $d = db_query("SELECT id, filename, title FROM dropbox_file", $code);
    while ($dbox = mysql_fetch_array($d)) {
        encode_dropbox_documents($code, $dbox['id'], $dbox['filename'], $dbox['title']);
    }

    $course_base_dir = "$webDir/courses/$code";
    if (!is_writable($course_base_dir)) {
        die("$langUpgNotIndex \"$course_base_dir\"! $langCheckPerm.");
    }

    if (!file_exists("$course_base_dir/temp")) {
        mkdir("$course_base_dir/temp", 0777);
    }

    echo "$langUpgCourse <b>$code</b><br>";
    flush();

    // *********************************
    // old upgrade queries
    // *********************************
    // upgrade queries from 1.2 --> 1.4
    if (!mysql_field_exists('$code', 'exercices', 'type'))
        echo add_field('exercices', 'type', "TINYINT( 4 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `description`");
    if (!mysql_field_exists('$code', 'exercices', 'random'))
        echo add_field('exercices', 'random', "SMALLINT( 6 ) DEFAULT '0' NOT NULL AFTER `type`");
    if (!mysql_field_exists('$code', 'reponses', 'ponderation'))
        echo add_field('reponses', 'ponderation', "SMALLINT( 5 ) NOT NULL AFTER `comment`");
    $s = db_query("SELECT type FROM questions", $code);
    while ($f = mysql_fetch_row($s)) {
        if (empty($f[0])) {
            if (db_query("UPDATE `questions` SET type=1", $code)) {
                echo "$langTable questions: $OK<br>";
            } else {
                echo "$langTable questions: $BAD<br>";
            }
        }
    } // while

    if (!mysql_table_exists($code, 'assignments')) {
        db_query("CREATE TABLE `assignments` (
                        `id` int(11) NOT NULL auto_increment,
                        `title` varchar(200) NOT NULL default '',
                        `description` text NOT NULL,
                        `comments` text NOT NULL,
                        `deadline` date NOT NULL default '1000-10-10',
                        `submission_date` date NOT NULL default '1000-10-10',
                        `active` char(1) NOT NULL default '1',
                        `secret_directory` varchar(30) NOT NULL,
                        `group_submissions` CHAR(1) DEFAULT '0' NOT NULL,
                        UNIQUE KEY `id` (`id`))", $code);
    }

    if (!mysql_table_exists($code, 'assignment_submit')) {
        db_query("CREATE TABLE `assignment_submit` (
                        `id` int(11) NOT NULL auto_increment,
                        `uid` int(11) NOT NULL default '0',
                        `assignment_id` int(11) NOT NULL default '0',
                        `submission_date` date NOT NULL default '1000-10-10',
                        `submission_ip` varchar(16) NOT NULL default '',
                        `file_path` varchar(200) NOT NULL default '',
                        `file_name` varchar(200) NOT NULL default '',
                        `comments` text NOT NULL,
                        `grade` varchar(50) NOT NULL default '',
                        `grade_comments` text NOT NULL,
                        `grade_submission_date` date NOT NULL default '1000-10-10',
                        `grade_submission_ip` varchar(16) NOT NULL default '',
                        `group_id` INT( 11 ) DEFAULT NULL,
                        UNIQUE KEY `id` (`id`))", $code);
    }
    update_assignment_submit();

    // upgrade queries for eClass 1.5
    if (!mysql_table_exists($code, 'videolink')) {
        db_query("CREATE TABLE videolinks (
                        id int(11) NOT NULL auto_increment,
                           url varchar(200),
                           titre varchar(200),
                           description text,
                           visibility CHAR(1) DEFAULT '1' NOT NULL,
                           PRIMARY KEY (id))", $code);
    }

    // upgrade queries for eClass 1.6
    echo add_field('liens', 'category', "INT(4) DEFAULT '0' NOT NULL");
    echo add_field('liens', 'ordre', "MEDIUMINT(8) DEFAULT '0' NOT NULL");
    if (!mysql_table_exists($code, 'link_categories')) {
        db_query("CREATE TABLE `link_categories` (
                        `id` int(6) NOT NULL auto_increment,
                        `categoryname` varchar(255) default NULL,
                        `description` text,
                        `ordre` mediumint(8) NOT NULL default '0',
                        PRIMARY KEY  (`id`))", $code);
    }

    // correct link entries to correctly appear in a blank window
    $sql = db_query("SELECT url FROM `liens` WHERE url REGEXP '\"target=_blank$'");
    while ($u = mysql_fetch_row($sql)) {
        $temp = $u[0];
        $newurl = preg_replace('#\s*"target=_blank#', '', $temp);
        db_query("UPDATE liens SET url='$newurl' WHERE url='$temp'");
    }

    // for dropbox
    if (!mysql_table_exists($code, 'dropbox_file')) {
        db_query("INSERT INTO accueil VALUES (
                        16,
                        '{$langDropbox[$lang]}',
                        '../../modules/dropbox/index.php',
                        'dropbox',
                        '0',
                        '0',
                        '',
                        )", $code);

        db_query("CREATE TABLE dropbox_file (
                        id int(11) unsigned NOT NULL auto_increment,
                           uploaderId int(11) unsigned NOT NULL default '0',
                           filename varchar(250) NOT NULL default '',
                           filesize int(11) unsigned NOT NULL default '0',
                           title varchar(250) default '',
                           description varchar(250) default '',
                           author varchar(250) default '',
                           uploadDate datetime NOT NULL default '0000-00-00 00:00:00',
                           lastUploadDate datetime NOT NULL default '0000-00-00 00:00:00',
                           PRIMARY KEY (id),
                           UNIQUE KEY UN_filename (filename))", $code);
    }
    if (!mysql_table_exists($code, 'dropbox_person')) {
        db_query("CREATE TABLE dropbox_person (
                        fileId int(11) unsigned NOT NULL default '0',
                               personId int(11) unsigned NOT NULL default '0',
                               PRIMARY KEY  (fileId,personId))", $code);
    }
    if (!mysql_table_exists($code, 'dropbox_post')) {
        db_query("CREATE TABLE dropbox_post (
                        fileId int(11) unsigned NOT NULL default '0',
                               recipientId int(11) unsigned NOT NULL default '0',
                               PRIMARY KEY  (fileId,recipientId))", $code);
    }

    // ********************************************
    // new upgrade queries for eClass 2.0
    // ********************************************
    if (mysql_table_exists($code, 'work'))
        db_query("DROP TABLE `work`");
    if (mysql_table_exists($code, 'work_student'))
        db_query("DROP TABLE `work_student`");

    $sql = 'SELECT id, titre, contenu, day, hour, lasting
                FROM  agenda WHERE CONCAT(titre,contenu) != \'\'
                AND DATE_FORMAT(day,\'%Y %m %d\') >= \'' . date("Y m d") . '\'';

    //  Get all agenda events from each table & parse them to arrays
    $mysql_query_result = db_query($sql, $code);
    $event_counter = 0;
    while ($myAgenda = mysql_fetch_array($mysql_query_result)) {
        $lesson_agenda[$event_counter]['id'] = $myAgenda[0];
        $lesson_agenda[$event_counter]['title'] = $myAgenda[1];
        $lesson_agenda[$event_counter]['content'] = $myAgenda[2];
        $lesson_agenda[$event_counter]['date'] = $myAgenda[3];
        $lesson_agenda[$event_counter]['time'] = $myAgenda[4];
        $lesson_agenda[$event_counter]['duree'] = $myAgenda[5];
        $lesson_agenda[$event_counter]['lesson_code'] = $code;
        $event_counter++;
    }

    for ($j = 0; $j < $event_counter; $j++) {
        db_query("INSERT INTO agenda (lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
                                VALUES (" . quote($lesson_agenda[$j]['id']) . ",
                                        " . quote($lesson_agenda[$j]['title']) . ",
                                        " . quote($lesson_agenda[$j]['content']) . ",
                                        " . quote($lesson_agenda[$j]['date']) . ",
                                        " . quote($lesson_agenda[$j]['time']) . ",
                                        " . quote($lesson_agenda[$j]['duree']) . ",
                                        " . quote($lesson_agenda[$j]['lesson_code']) . ")", $mysqlMainDb);
    }
    // end of agenda
    // Group table
    if (!mysql_table_exists($code, 'group_documents')) {
        db_query("CREATE TABLE `group_documents` (
                        `id` INT(4) NOT NULL AUTO_INCREMENT,
                        `path` VARCHAR(255) default NULL ,
                        `filename` VARCHAR(255) default NULL,
                        PRIMARY KEY(id))", $code);
    }

    // Learning Path tables
    if (!mysql_table_exists($code, 'lp_module')) {
        db_query("CREATE TABLE `lp_module` (
                        `module_id` int(11) NOT NULL auto_increment,
                        `name` varchar(255) NOT NULL default '',
                        `comment` text NOT NULL,
                        `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
                        `startAsset_id` int(11) NOT NULL default '0',
                        `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK') NOT NULL,
                        `launch_data` text NOT NULL,
                        PRIMARY KEY  (`module_id`)
                                ) ", $code); //TYPE=MyISAM COMMENT='List of available modules used in learning paths';
    }
    if (!mysql_table_exists($code, 'lp_learnPath')) {
        db_query("CREATE TABLE `lp_learnPath` (
                        `learnPath_id` int(11) NOT NULL auto_increment,
                        `name` varchar(255) NOT NULL default '',
                        `comment` text NOT NULL,
                        `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
                        `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
                        `rank` int(11) NOT NULL default '0',
                        PRIMARY KEY  (`learnPath_id`),
                        UNIQUE KEY rank (`rank`)
                                ) ", $code); //TYPE=MyISAM COMMENT='List of learning Paths';
    }

    if (!mysql_table_exists($code, 'lp_rel_learnPath_module')) {
        db_query("CREATE TABLE `lp_rel_learnPath_module` (
                        `learnPath_module_id` int(11) NOT NULL auto_increment,
                        `learnPath_id` int(11) NOT NULL default '0',
                        `module_id` int(11) NOT NULL default '0',
                        `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
                        `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
                        `specificComment` text NOT NULL,
                        `rank` int(11) NOT NULL default '0',
                        `parent` int(11) NOT NULL default '0',
                        `raw_to_pass` tinyint(4) NOT NULL default '50',
                        PRIMARY KEY  (`learnPath_module_id`)
                                ) ", $code); //TYPE=MyISAM COMMENT='This table links module to the learning path using them';
    }

    if (!mysql_table_exists($code, 'lp_asset')) {
        db_query("CREATE TABLE `lp_asset` (
                        `asset_id` int(11) NOT NULL auto_increment,
                        `module_id` int(11) NOT NULL default '0',
                        `path` varchar(255) NOT NULL default '',
                        `comment` varchar(255) default NULL,
                        PRIMARY KEY  (`asset_id`)
                                ) ", $code); //TYPE=MyISAM COMMENT='List of resources of module of learning paths';
    }

    if (!mysql_table_exists($code, 'lp_user_module_progress')) {
        db_query("CREATE TABLE `lp_user_module_progress` (
                        `user_module_progress_id` int(22) NOT NULL auto_increment,
                        `user_id` mediumint(9) NOT NULL default '0',
                        `learnPath_module_id` int(11) NOT NULL default '0',
                        `learnPath_id` int(11) NOT NULL default '0',
                        `lesson_location` varchar(255) NOT NULL default '',
                        `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
                        `entry` enum('AB-INITIO','RESUME','') NOT NULL default 'AB-INITIO',
                        `raw` tinyint(4) NOT NULL default '-1',
                        `scoreMin` tinyint(4) NOT NULL default '-1',
                        `scoreMax` tinyint(4) NOT NULL default '-1',
                        `total_time` varchar(13) NOT NULL default '0000:00:00.00',
                        `session_time` varchar(13) NOT NULL default '0000:00:00.00',
                        `suspend_data` text NOT NULL,
                        `credit` enum('CREDIT','NO-CREDIT') NOT NULL default 'NO-CREDIT',
                        PRIMARY KEY  (`user_module_progress_id`)
                                ) ", $code); //TYPE=MyISAM COMMENT='Record the last known status of the user in the course';
    }

    // Wiki tables
    if (!mysql_table_exists($code, 'wiki_properties')) {
        db_query("CREATE TABLE `wiki_properties` (
                        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `title` VARCHAR(255) NOT NULL DEFAULT '',
                        `description` TEXT NULL,
                        `group_id` INT(11) NOT NULL DEFAULT 0,
                        PRIMARY KEY(`id`)
                                ) ", $code);
    }

    if (!mysql_table_exists($code, 'wiki_acls')) {
        db_query("CREATE TABLE `wiki_acls` (
                        `wiki_id` INT(11) UNSIGNED NOT NULL,
                        `flag` VARCHAR(255) NOT NULL,
                        `value` ENUM('false','true') NOT NULL DEFAULT 'false'
                                ) ", $code);
    }

    if (!mysql_table_exists($code, 'wiki_pages')) {
        db_query("CREATE TABLE `wiki_pages` (
                        `id` int(11) unsigned NOT NULL auto_increment,
                        `wiki_id` int(11) unsigned NOT NULL default '0',
                        `owner_id` int(11) unsigned NOT NULL default '0',
                        `title` varchar(255) NOT NULL default '',
                        `ctime` datetime NOT NULL default '0000-00-00 00:00:00',
                        `last_version` int(11) unsigned NOT NULL default '0',
                        `last_mtime` datetime NOT NULL default '0000-00-00 00:00:00',
                        PRIMARY KEY  (`id`)
                                ) ", $code);
    }

    if (!mysql_table_exists($code, 'wiki_pages_content')) {
        db_query("CREATE TABLE `wiki_pages_content` (
                        `id` int(11) unsigned NOT NULL auto_increment,
                        `pid` int(11) unsigned NOT NULL default '0',
                        `editor_id` int(11) NOT NULL default '0',
                        `mtime` datetime NOT NULL default '0000-00-00 00:00:00',
                        `content` text NOT NULL,
                        PRIMARY KEY  (`id`)
                                ) ", $code);
    }

    // questionnaire tables
    if (!mysql_table_exists($code, 'survey')) {
        db_query("CREATE TABLE `survey` (
                        `sid` bigint(14) NOT NULL auto_increment,
                        `creator_id` mediumint(8) unsigned NOT NULL default '0',
                        `course_id` varchar(20) NOT NULL default '0',
                        `name` varchar(255) NOT NULL default '',
                        `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
                        `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
                        `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
                        `type` int(11) NOT NULL default '0',
                        `active` int(11) NOT NULL default '0',
                        PRIMARY KEY  (`sid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the questionnaire module';
    }
    if (!mysql_table_exists($code, 'survey_answer')) {
        db_query("CREATE TABLE `survey_answer` (
                        `aid` bigint(12) NOT NULL default '0',
                        `creator_id` mediumint(8) unsigned NOT NULL default '0',
                        `sid` bigint(12) NOT NULL default '0',
                        `date` datetime NOT NULL default '0000-00-00 00:00:00',
                        PRIMARY KEY  (`aid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the questionnaire module';
    }
    if (!mysql_table_exists($code, 'survey_answer_record')) {
        db_query("CREATE TABLE `survey_answer_record` (
                        `arid` int(11) NOT NULL auto_increment,
                        `aid` bigint(12) NOT NULL default '0',
                        `question_text` varchar(250) NOT NULL default '',
                        `question_answer` varchar(250) NOT NULL default '',
                        PRIMARY KEY  (`arid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the questionnaire module';
    }
    if (!mysql_table_exists($code, 'survey_question')) {
        db_query("CREATE TABLE `survey_question` (
                        `sqid` bigint(12) NOT NULL default '0',
                        `sid` bigint(12) NOT NULL default '0',
                        `question_text` varchar(250) NOT NULL default '',
                        PRIMARY KEY  (`sqid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the questionnaire module';
    }
    if (!mysql_table_exists($code, 'survey_question_answer')) {
        db_query("CREATE TABLE `survey_question_answer` (
                        `sqaid` int(11) NOT NULL auto_increment,
                        `sqid` bigint(12) NOT NULL default '0',
                        `answer_text` varchar(250) default NULL,
                        PRIMARY KEY  (`sqaid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the questionnaire module';
    }

    // poll tables
    if (!mysql_table_exists($code, 'poll')) {
        db_query("CREATE TABLE `poll` (
                        `pid` bigint(14) NOT NULL auto_increment,
                        `creator_id` mediumint(8) unsigned NOT NULL default '0',
                        `course_id` varchar(20) NOT NULL default '0',
                        `name` varchar(255) NOT NULL default '',
                        `creation_date` date NOT NULL default '1000-10-10',
                        `start_date` date NOT NULL default '1000-10-10',
                        `end_date` date NOT NULL default '1000-10-10',
                        `type` int(11) NOT NULL default '0',
                        `active` int(11) NOT NULL default '0',
                        PRIMARY KEY  (`pid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the poll module';
    } else {
        db_query("ALTER TABLE `poll` CHANGE `creation_date` `creation_date` DATE NOT NULL DEFAULT '1000-10-10'", $code);
        db_query("ALTER TABLE `poll` CHANGE `start_date` `start_date` DATE NOT NULL DEFAULT '1000-10-10'", $code);
        db_query("ALTER TABLE `poll` CHANGE `end_date` `end_date` DATE NOT NULL DEFAULT '1000-10-10'", $code);
    }

    if (!mysql_table_exists($code, 'poll_answer_record')) {
        db_query("CREATE TABLE `poll_answer_record` (
                        `arid` int(11) NOT NULL auto_increment,
                        `aid` bigint(12) NOT NULL default '0',
                        `question_text` varchar(250) NOT NULL default '',
                        `question_answer` varchar(250) NOT NULL default '',
                        PRIMARY KEY  (`arid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the poll module';
    }

    if (!mysql_field_exists("$code", 'poll_answer_record', 'qtype'))
        echo add_field_after_field('poll_answer_record', 'pid', 'arid', "INT(11) NOT NULL");
    if (!mysql_field_exists("$code", 'poll_answer_record', 'pid'))
        echo add_field_after_field('poll_answer_record', 'pid', 'arid', "INT(11) NOT NULL DEFAULT '0'");
    if (!mysql_field_exists("$code", 'poll_answer_record', 'qid'))
        echo add_field_after_field('poll_answer_record', 'qid', 'pid', "INT(11) NOT NULL DEFAULT '0'");
    if (mysql_field_exists("$code", 'poll_answer_record', 'question_text'))
        echo delete_field('poll_answer_record', 'question_text');
    if (mysql_field_exists("$code", 'poll_answer_record', 'question_answer'))
        echo delete_field('poll_answer_record', 'question_answer');
    if (!mysql_field_exists("$code", 'poll_answer_record', 'answer_text'))
        echo add_field('poll_answer_record', 'answer_text', "VARCHAR(255) NOT NULL");
    if (!mysql_field_exists("$code", 'poll_answer_record', 'user_id'))
        echo add_field('poll_answer_record', 'user_id', "INT(11) NOT NULL DEFAULT '0'");
    if (!mysql_field_exists("$code", 'poll_answer_record', 'submit_date'))
        echo add_field('poll_answer_record', 'submit_date', "DATE NOT NULL DEFAULT '1000-10-10'");

    if (!mysql_table_exists($code, 'poll_question')) {
        db_query("CREATE TABLE `poll_question` (
                        `pqid` bigint(12) NOT NULL default '0',
                        `pid` bigint(12) NOT NULL default '0',
                        `question_text` varchar(250) NOT NULL default '',
                        PRIMARY KEY  (`pqid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the poll module';
    }

    if (!mysql_field_exists("$code", 'poll_question', 'qtype'))
        echo add_field('poll_question', 'qtype', "ENUM('multiple', 'fill') NOT NULL");

    if (!mysql_table_exists($code, 'poll_question_answer')) {
        db_query("CREATE TABLE `poll_question_answer` (
                        `pqaid` int(11) NOT NULL auto_increment,
                        `pqid` bigint(12) NOT NULL default '0',
                        `answer_text` varchar(250) default NULL,
                        PRIMARY KEY  (`pqaid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the poll module';
    }


    //  usage tables
    if (!mysql_table_exists($code, 'actions')) {
        db_query("CREATE TABLE actions (
                        id int(11) NOT NULL auto_increment,
                           user_id int(11) NOT NULL,
                           module_id int(11) NOT NULL,
                           action_type_id int(11) NOT NULL,
                           date_time DATETIME NOT NULL default '0000-00-00 00:00:00',
                           duration int(11) NOT NULL,
                           PRIMARY KEY (id))", $code);
    }

    if (!mysql_table_exists($code, 'logins')) {
        db_query("CREATE TABLE logins (
                        id int(11) NOT NULL auto_increment,
                           user_id int(11) NOT NULL,
                           ip char(16) NOT NULL default '0.0.0.0',
                           date_time DATETIME NOT NULL default '0000-00-00 00:00:00',
                           PRIMARY KEY (id))", $code);
    }

    if (!mysql_table_exists($code, 'actions_summary')) {
        db_query("CREATE TABLE actions_summary (
                        id int(11) NOT NULL auto_increment,
                           module_id int(11) NOT NULL,
                           visits int(11) NOT NULL,
                           start_date DATETIME NOT NULL default '0000-00-00 00:00:00',
                           end_date DATETIME NOT NULL default '0000-00-00 00:00:00',
                           duration int(11) NOT NULL,
                           PRIMARY KEY (id))", $code);
    }

    // exercise tables
    if (!mysql_table_exists($code, 'exercise_user_record')) {
        db_query("CREATE TABLE `exercise_user_record` (
                        `eurid` int(11) NOT NULL auto_increment,
                        `eid` tinyint(4) NOT NULL default '0',
                        `uid` mediumint(8) NOT NULL default '0',
                        `RecordStartDate` date NOT NULL default '1000-10-10',
                        `RecordEndDate` date NOT NULL default '1000-10-10',
                        `TotalScore` int(11) NOT NULL default '0',
                        `TotalWeighting` int(11) default '0',
                        `attempt` int(11) NOT NULL default '0',
                        PRIMARY KEY  (`eurid`)
                                ) ", $code); //TYPE=MyISAM COMMENT='For the exercise module';
    }

    // Upgrading EXERCICES table for new func of EXERCISE module
    if (!mysql_field_exists("$code", 'exercices', 'StartDate'))
        echo add_field_after_field('exercices', 'StartDate', 'type', "DATE NOT NULL default '1000-10-10'");
    else
        db_query("ALTER TABLE `exercices` CHANGE `StartDate` `StartDate` DATE NULL DEFAULT NULL", $code);
    if (!mysql_field_exists("$code", 'exercices', 'EndDate'))
        echo add_field_after_field('exercices', 'EndDate', 'StartDate', "DATE NOT NULL default '1000-10-10'");
    else
        db_query("ALTER TABLE `exercices` CHANGE `EndDate` `EndDate` DATE NULL DEFAULT NULL", $code);
    if (!mysql_field_exists("$code", 'exercices', 'TimeConstrain'))
        echo add_field_after_field('exercices', 'TimeConstrain', 'EndDate', "INT(11)");
    if (!mysql_field_exists("$code", 'exercices', 'AttemptsAllowed'))
        echo add_field_after_field('exercices', 'AttemptsAllowed', 'TimeConstrain', "INT(11)");
    db_query('UPDATE exercices
                  SET StartDate = NOW(),
                      EndDate = DATE_ADD(NOW(), INTERVAL 20 YEAR)
                  WHERE StartDate IS NULL OR EndDate IS NULL');


    // add new document fields
    if (!mysql_field_exists("$code", 'document', 'filename'))
        echo add_field('document', 'filename', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'category'))
        echo add_field('document', 'category', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'title'))
        echo add_field('document', 'title', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'creator'))
        echo add_field('document', 'creator', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'date'))
        echo add_field('document', 'date', "DATETIME");
    if (!mysql_field_exists("$code", 'document', 'date_modified'))
        echo add_field('document', 'date_modified', "DATETIME");
    if (!mysql_field_exists("$code", 'document', 'subject'))
        echo add_field('document', 'subject', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'description'))
        echo add_field('document', 'description', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'author'))
        echo add_field('document', 'author', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'format'))
        echo add_field('document', 'format', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'language'))
        echo add_field('document', 'language', "TEXT");
    if (!mysql_field_exists("$code", 'document', 'copyrighted'))
        echo add_field('document', 'copyrighted', "TEXT");


    // -------------- document upgrade ---------------------
    echo "$langEncodeDocuments<br>";
    flush();
    encode_documents($code);
    fix_multiple_document_entries('document');
    fix_document_date_and_format($code);

    // -------------- group document upgrade ---------------
    echo "$langEncodeGroupDocuments<br>";
    flush();
    // find group secret directory
    $s = db_query("SELECT id, secretDirectory FROM student_group");
    while ($group = mysql_fetch_array($s)) {
        encode_group_documents($code, $group['id'], $group['secretDirectory']);
    }
    fix_multiple_document_entries('group_documents');

    // ----------------- dropbox document upgrade ----------
    echo "$langEncodeDropBoxDocuments<br>";
    flush();
    $d = db_query("SELECT id, filename, title FROM dropbox_file");
    while ($dbox = mysql_fetch_array($d)) {
        encode_dropbox_documents($code, $dbox['id'], $dbox['filename'], $dbox['title']);
    }

    // -------------- move video files to new directory ----
    $course_video = "$webDir/courses/$code/video";
    if (is_dir($course_video)) {
        if (!rename($course_video, "$webDir/video/$code")) {
            echo "$langNotMovedDir $course_video $langToDir video<br>";
        }
    }
    // upgrade video
    if (!mysql_field_exists("$code", 'video', 'path')) {
        echo add_field_after_field('video', 'path', 'id', "VARCHAR(255)");
        $r = db_query("SELECT * FROM video", $code);
        while ($row = mysql_fetch_array($r)) {
            upgrade_video($row['url'], $row['id'], $code);
        }
    }
    if (!mysql_field_exists($code, 'video', 'creator'))
        echo add_field_after_field('video', 'creator', 'description', "VARCHAR(255)");
    if (!mysql_field_exists($code, 'video', 'publisher'))
        echo add_field_after_field('video', 'publisher', 'creator', "VARCHAR(255)");
    if (!mysql_field_exists($code, 'video', 'date'))
        echo add_field_after_field('video', 'date', 'publisher', "DATETIME");

    // upgrade videolinks
    if (!mysql_field_exists($code, 'videolinks', 'creator'))
        echo add_field_after_field('videolinks', 'creator', 'description', "VARCHAR(255)");
    if (!mysql_field_exists($code, 'videolinks', 'publisher'))
        echo add_field_after_field('videolinks', 'publisher', 'creator', "VARCHAR(255)");
    if (!mysql_field_exists($code, 'videolinks', 'date'))
        echo add_field_after_field('videolinks', 'date', 'publisher', "DATETIME");

    // upgrading accueil table
    //  create new column (define_var)
    echo add_field('accueil', 'define_var', 'VARCHAR(50) NOT NULL');

    // Move all external links to id > 1000
    db_query("UPDATE `accueil`
                        SET `id` = `id` + 1000
                        WHERE `id`>20 AND `id`<1000
                        AND `define_var` <> 'MODULE_ID_QUESTIONNAIRE' AND `define_var` <> 'MODULE_ID_LP'
                        AND `define_var` <> 'MODULE_ID_USAGE' AND `define_var` <> 'MODULE_ID_TOOLADMIN'
                        AND `define_var` <> 'MODULE_ID_WIKI'", $code);

    // id νέων υποσυστημάτων
    if (accueil_tool_missing('MODULE_ID_QUESTIONNAIRE')) {
        db_query("INSERT IGNORE INTO accueil VALUES (
                        '21',
                        '{$langQuestionnaire[$lang]}',
                        '../../modules/questionnaire/questionnaire.php',
                        'questionnaire',
                        '0',
                        '0',
                        '',
                        'MODULE_ID_QUESTIONNAIRE'
                                )", $code);
    }

    if (accueil_tool_missing('MODULE_ID_LP')) {
        db_query("INSERT IGNORE INTO accueil VALUES (
                        '23',
                        '{$langLearnPath[$lang]}',
                        '../../modules/learnPath/learningPathList.php',
                        'lp',
                        '0',
                        '0',
                        '',
                        'MODULE_ID_LP'
                                )", $code);
    }

    if (accueil_tool_missing('MODULE_ID_USAGE')) {
        db_query("INSERT IGNORE INTO accueil VALUES (
                        '24',
                        '{$langCourseStat[$lang]}',
                        '../../modules/usage/usage.php',
                        'usage',
                        '0',
                        '1',
                        '',
                        'MODULE_ID_USAGE')", $code);
    }

    if (accueil_tool_missing('MODULE_ID_TOOLADMIN')) {
        db_query("INSERT IGNORE INTO accueil VALUES (
                        '25',
                        '{$langToolManagement[$lang]}',
                        '../../modules/course_tools/course_tools.php',
                        'tooladmin',
                        '0',
                        '1',
                        '',
                        'MODULE_ID_TOOLADMIN'
                                )", $code);
    }

    if (accueil_tool_missing('MODULE_ID_WIKI')) {
        db_query("INSERT IGNORE INTO accueil VALUES (
                        '26',
                        '{$langWiki[$lang]}',
                        '../../modules/wiki/wiki.php',
                        'wiki',
                        '0',
                        '0',
                        '',
                        'MODULE_ID_WIKI'
                                )", $code);
    }

    // table accueil
    echo "$langCorrectTableEntries accueil.<br>";

    /* compatibility update
      a) remove entries modules import, external, videolinks, old statistics
      b) correct agenda and video link
     */

    db_query("DELETE FROM accueil WHERE (id = 12 OR id = 13 OR id = 11 OR id=6)", $code);
    update_field("accueil", "lien", "../../modules/agenda/agenda.php", "id", 1);
    db_query("UPDATE accueil SET visible = '0', admin = '1' WHERE id = 8 LIMIT 1", $code);
    update_field("accueil", "lien", "../../modules/video/video.php", "id", 4);
    update_field("accueil", "lien", "../../modules/conference/conference.php", "id", 19);

    //set define string vars
    update_field("accueil", "define_var", "MODULE_ID_AGENDA", "id", 1);
    update_field("accueil", "define_var", "MODULE_ID_LINKS", "id", 2);
    update_field("accueil", "define_var", "MODULE_ID_DOCS", "id", 3);
    update_field("accueil", "define_var", "MODULE_ID_VIDEO", "id", 4);
    update_field("accueil", "define_var", "MODULE_ID_ASSIGN", "id", 5);
    update_field("accueil", "define_var", "MODULE_ID_ANNOUNCE", "id", 7);
    update_field("accueil", "define_var", "MODULE_ID_USERS", "id", 8);
    update_field("accueil", "define_var", "MODULE_ID_FORUM", "id", 9);
    update_field("accueil", "define_var", "MODULE_ID_EXERCISE", "id", 10);
    update_field("accueil", "define_var", "MODULE_ID_COURSEINFO", "id", 14);
    update_field("accueil", "define_var", "MODULE_ID_GROUPS", "id", 15);
    update_field("accueil", "define_var", "MODULE_ID_DROPBOX", "id", 16);
    update_field("accueil", "define_var", "MODULE_ID_CHAT", "id", 19);
    update_field("accueil", "define_var", "MODULE_ID_DESCRIPTION", "id", 20);

    $sql = db_query("SELECT id,lien,image,address FROM accueil");
    while ($u = mysql_fetch_row($sql)) {
        $oldlink_lien = $u[1];
        $newlink_lien = preg_replace(
                array('#../claroline/#',
            '#../../modules/import/import_page.php\?link=(\S+) "target=_blank#'), array('../../modules/',
            '../../courses/' . $code . '/page/$1'), $oldlink_lien);
        $oldlink_image = $u[2];
        $newlink_image = str_replace(
                '../claroline/image/', '../../images/', $oldlink_image);
        $oldlink_address = $u[3];
        $newlink_address = str_replace(
                '../claroline/image/', '../../images/', $oldlink_address);
        if ($oldlink_lien != $newlink_lien or
                $oldlink_image != $newlink_image or
                $oldlink_address != $newlink_address) {
            db_query("UPDATE accueil SET
                                         lien = " . quote($newlink_lien) . ",
                                         image = " . quote($newlink_image) . ",
                                         address = " . quote($newlink_address) . "
                                  WHERE id='$u[0]'");
        }
    }

    //set the new images for the icons of lesson modules
    update_field("accueil", "image", "calendar", "id", 1);
    update_field("accueil", "image", "links", "id", 2);
    update_field("accueil", "image", "docs", "id", 3);
    update_field("accueil", "image", "videos", "id", 4);
    update_field("accueil", "image", "assignments", "id", 5);
    update_field("accueil", "image", "announcements", "id", 7);
    update_field("accueil", "image", "users", "id", 8);
    update_field("accueil", "image", "forum", "id", 9);
    update_field("accueil", "image", "exercise", "id", 10);
    update_field("accueil", "image", "course_info", "id", 14);
    update_field("accueil", "image", "groups", "id", 15);
    update_field("accueil", "image", "dropbox", "id", 16);
    update_field("accueil", "image", "conference", "id", 19);
    update_field("accueil", "image", "description", "id", 20);

    $sql = db_query("SELECT image FROM accueil");
    while ($u = mysql_fetch_row($sql)) {
        if ($u[0] == '../../../images/npage.gif') {
            update_field("accueil", "image", "external_link", "image", "../../../images/npage.gif");
        }
        if ($u[0] == '../../../images/page.gif') {
            update_field("accueil", "image", "external_link", "image", "../../../images/page.gif");
        }
        if ($u[0] == 'travaux.png') {
            update_field("accueil", "image", "external_link", "image", "travaux.png");
        }
    }

    // update menu entries with new messages
    update_field('accueil', 'rubrique', $langWork[$lang], 'id', '5');
    update_field('accueil', 'rubrique', $langForums[$lang], 'id', '9');
    update_field('accueil', 'rubrique', $langUsers[$lang], 'id', '8');
    update_field('accueil', 'visible', '0', 'id', '8');
    update_field('accueil', 'admin', '1', 'id', '8');
    update_field('accueil', 'rubrique', $langCourseAdmin[$lang], 'id', '14');
    update_field('accueil', 'rubrique', $langDropBox[$lang], 'id', '16');

    // remove table 'introduction' entries and insert them in table 'cours' (field 'description') in eclass maindb
    // after that drop table introduction
    if (mysql_table_exists($code, 'introduction')) {
        $sql = db_query("SELECT texte_intro FROM introduction", $code);
        while ($text = mysql_fetch_array($sql)) {
            $description = quote($text[0]);
            if (db_query("UPDATE cours SET description=$description WHERE code='$code'", $mysqlMainDb)) {
                db_query("DROP TABLE IF EXISTS introduction", $code);
            } else {
                echo "$langMoveIntroText <b>cours</b>: $BAD<br>";
            }
        }
    } // end of table introduction

    $tool_content .= "<br><br></td></tr>";

    // add full text indexes for search operation (ginetai xrhsh @mysql_query(...) giati ean
    // yparxei hdh, to FULL INDEX den mporei na ksanadhmiourgithei. epipleon, den yparxei tropos
    // elegxou gia to an yparxei index, opote o monadikos tropos diekperaiwshs ths ergasias einai
    // dokimh-sfalma.
    @mysql_query("ALTER TABLE `agenda` ADD FULLTEXT `agenda` (`titre` ,`contenu`)");
    @mysql_query("ALTER TABLE `course_description` ADD FULLTEXT `course_description` (`title` ,`content`)");
    @mysql_query("ALTER TABLE `document` ADD FULLTEXT `document` (`filename` ,`comment` ,`title`,`creator`,`subject`,`description`,`author`,`language`)");
    @mysql_query("ALTER TABLE `exercices` ADD FULLTEXT `exercices` (`titre`,`description`)");
    @mysql_query("ALTER TABLE `posts_text` ADD FULLTEXT `posts_text` (`post_text`)");
    @mysql_query("ALTER TABLE `forums` ADD FULLTEXT `forums` (`forum_name`,`forum_desc`)");
    @mysql_query("ALTER TABLE `liens` ADD FULLTEXT `liens` (`url` ,`titre` ,`description`)");
    @mysql_query("ALTER TABLE `video` ADD FULLTEXT `video` (`url` ,`titre` ,`description`)");

    // bogart: Update code for forum functionality START
    // Remove tables banlist, disallow, headermetafooter, priv_msgs, ranks, sessions, themes, whosonline, words
    db_query("DROP TABLE IF EXISTS access");
    db_query("DROP TABLE IF EXISTS banlist");
    db_query("DROP TABLE IF EXISTS config");
    db_query("DROP TABLE IF EXISTS disallow");
    db_query("DROP TABLE IF EXISTS forum_access");
    db_query("DROP TABLE IF EXISTS forum_mods");
    db_query("DROP TABLE IF EXISTS headermetafooter");
    db_query("DROP TABLE IF EXISTS priv_msgs");
    db_query("DROP TABLE IF EXISTS ranks");
    db_query("DROP TABLE IF EXISTS sessions");
    db_query("DROP TABLE IF EXISTS themes");
    db_query("DROP TABLE IF EXISTS whosonline");
    db_query("DROP TABLE IF EXISTS words");
    // bogart: Update code for forum functionality END
    // remove tables liste_domains. Used for old statistics module
    db_query("DROP TABLE IF EXISTS liste_domaines");

    // convert to UTF-8
    convert_db_utf8($code);
}

// -----------------------------------
// functions for document ------------
// -----------------------------------
// Rename a file and insert its information in the database, if needed
// Returns the new file path or false if file wasn't renamed
function document_upgrade_file($path, $data) {
    if ($data == 'document') {
        $table = 'document';
    } else {
        $table = 'group_documents';
    }

    // Filenames in older versions of eClass were in ISO-8859-7
    // No need to conver them, we're assuming "SET NAMES greek"
    $db_path = trim_path($path);
    $old_filename = preg_replace('|^.*/|', '', $db_path);
    $new_filename = safe_filename(get_file_extension($old_filename));
    $new_path = preg_replace('|/[^/]*$|', "/$new_filename", $db_path);
    $file_date = quote(date('Y-m-d H:i:s', filemtime($path)));
    $r = db_query("SELECT * FROM $table WHERE path = " . quote($db_path));
    if (mysql_num_rows($r) > 0) {
        $current_filename = mysql_fetch_array($r);
        if (empty($current_filename['filename']) or
                preg_match('/[^\040-\177]/', $current_filename['path'])) {
            if (!empty($current_filename['filename'])) {
                $old_filename = $current_filename['filename'];
            }
            // File exists in database, hasn't been upgraded
            $format = quote(get_file_extension($old_filename));
            db_query("UPDATE $table
                                        SET filename = " . quote($old_filename) . ",
                                        path = " . quote($new_path) . ",
                                        date = $file_date, date_modified = $file_date,
                                        format = $format
                                        WHERE path= " . quote($db_path));
            rename($path, $data . $new_path);
        } else {
            // File wasn't renamed
            $new_path = false;
        }
    } else {
        // File doesn't exist in database
        if ($table == 'document') {
            $format = quote(get_file_extension($old_filename));
            db_query("INSERT INTO document
                                  SET path = " . quote($new_path) . ",
                                      filename = " . quote($old_filename) . ",
                                      visibility = 'v',
                                      comment = '', category = '',
                                      title = '', creator = '',
                                      date = $file_date, date_modified = $file_date,
                                      subject = '', description = '',
                                      author = '', format = $format,
                                      language = '', copyrighted = ''");
        } else {
            db_query("INSERT INTO group_documents
                                  SET path = " . quote($new_path) . ",
                                  filename = " . quote($old_filename));
        }
        rename($path, $data . $new_path);
    }
    return $new_path;
}

// Upgrade a directory, and if it was renamed, fix its contents'
// database records to point to the new path
function document_upgrade_dir($path, $data) {
    if ($data == 'document') {
        $table = 'document';
    } else {
        $table = 'group_documents';
    }

    $db_path = trim_path($path);
    $new_path = document_upgrade_file($path, $data);
    if ($new_path) {
        // Directory was renamed - need to update contents' entries
        db_query("UPDATE $table
                          SET path = CONCAT(" . quote($new_path) . ',
                                SUBSTRING(path FROM ' . (1 + strlen($db_path)) . '))' .
                'WHERE path LIKE ' . quote("$db_path%"));
        if ($data == 'document') {
            db_query("UPDATE $table SET format = '.dir'
			          WHERE path = " . quote($new_path));
        }
    }
}

// Remove the first component from beginning of $path, return the rest starting with '/'
function trim_path($path) {
    return preg_replace('|^[^/]*/|', '/', $path);
}

// Upgrades 'group_documents' table and encodes all filenames to be pure ASCII
// Database selected should be the current course database
function encode_group_documents($course_code, $group_id, $secret_directory) {
    $cwd = getcwd();
    chdir($GLOBALS['webDir'] . '/courses/' . $course_code . '/group');
    if (is_dir($secret_directory)) {
        traverseDirTree($secret_directory, 'document_upgrade_file', 'document_upgrade_dir', $secret_directory);
    } else {
        mkdir($secret_directory, '0775');
    }
    chdir($cwd);
}

// Update database entries for files missing the correct date
// Delete entries for non-existent files
function fix_document_date_and_format($code) {
    global $webDir;
    $base = "$webDir/courses/$code/document";

    $q = db_query("SELECT * FROM document WHERE date = '0000-00-00 00:00:00' OR date IS NULL");
    while ($file = mysql_fetch_array($q)) {
        $path = $base . $file['path'];
        if (!file_exists($path)) {
            db_query("DELETE FROM document WHERE id = $file[id]");
        } else {
            $file_date = quote(date('Y-m-d H:i:s', filemtime($path)));
            db_query("UPDATE document
                                  SET date = $file_date, date_modified = $file_date
                                  WHERE id = $file[id]");
        }
    }
    $q = db_query("SELECT * FROM document
                       WHERE format='' OR format IS NULL");
    while ($file = mysql_fetch_array($q)) {
        $path = $base . $file['path'];
        if (is_dir($path)) {
            $format = '.dir';
        } else {
            $format = get_file_extension($file['filename']);
        }
        db_query("UPDATE document SET format = '$format' WHERE id = $file[id]");
    }
}

// Delete multiple entries for the same file in the document or
// group_documents tables
function fix_multiple_document_entries($table) {
    $q = db_query("SELECT path, count(path) as c FROM $table
                        GROUP BY path HAVING c > 1");
    while ($file = mysql_fetch_array($q)) {
        db_query("DELETE FROM document WHERE path = " .
                quote($file['path']) .
                " LIMIT " . ($file['c'] - 1));
    }
}

// Upgrades 'document' table and encodes all filenames to be pure ASCII
// Database selected should be the current course database
function encode_documents($course_code) {
    $cwd = getcwd();
    chdir($GLOBALS['webDir'] . '/courses/' . $course_code);
    traverseDirTree('document', 'document_upgrade_file', 'document_upgrade_dir', 'document');
    chdir($cwd);
}

// -----------------------------------------------------------
// generic function to traverse the directory tree depth first
// -----------------------------------------------------------
function traverseDirTree($base, $fileFunc, $dirFunc, $data) {
    $subdirectories = opendir($base);
    // First process all directories
    while (($subdirectory = readdir($subdirectories)) !== false) {
        $path = $base . '/' . $subdirectory;
        if ($subdirectory != '.' and $subdirectory != '..' and is_dir($path)) {
            traverseDirTree($path, $fileFunc, $dirFunc, $data);
            $dirFunc($path, $data);
        }
    }
    // Then process all files
    rewinddir($subdirectories);
    while (($filename = readdir($subdirectories)) !== false) {
        $path = $base . '/' . $filename;
        if (is_file($path)) {
            $fileFunc($path, $data);
        }
    }
    closedir($subdirectories);
}

// -------------------------------------
// function for upgrading video files
// -------------------------------------
function upgrade_video($file, $id, $code) {
    global $webDir, $langGroupNone, $langWarnVideoFile;

    $fileName = trim($file);
    $fileName = replace_dangerous_char($fileName);
    $fileName = add_ext_on_mime($fileName);
    $fileName = php2phps($fileName);
    $safe_filename = date("YmdGis") . "_" . randomkeys('3') . "." . get_file_extension($fileName);
    $path_to_video = $webDir . '/video/' . $code . '/';
    if (rename($path_to_video . $file, $path_to_video . $safe_filename)) {
        db_query("UPDATE video SET path = '$safe_filename'
	        	WHERE id = '$id'", $code);
    } else {
        echo "$langWarnVideoFile $path_to_video.$file $langGroupNone!<br>";
        db_query("DELETE FROM video WHERE id = '$id'", $code);
    }
}

// Convert course description to special course unit with order = -1
function convert_description_to_units($code, $course_id) {
    global $mysqlMainDb, $langCourseDescription;

    mysql_select_db($mysqlMainDb);

    $desc = $addon = '';
    $qdesc = @mysql_query("SELECT description, course_addon FROM cours WHERE course_id = $course_id");
    if ($qdesc) {
        list($desc, $addon) = mysql_fetch_row($qdesc);
        $desc = trim($desc);
        $addon = trim($addon);
    }

    $q = @mysql_query("SELECT * FROM `$code`.course_description");

    // If old-style course description data don't exist and course description is
    // empty, don't do anything
    if ((!$q or mysql_num_rows($q) == 0) and empty($desc) and empty($addon)) {
        return;
    }

    $id = description_unit_id($course_id);

    $error = false;
    if (!empty($desc)) {
        $error = add_unit_resource($id, 'description', -1, $GLOBALS['langCourseDescription'], html_cleanup($desc)) && $error;
    }
    if (!empty($addon)) {
        $error = add_unit_resource($id, 'description', false, $GLOBALS['langCourseAddon'], $addon, 'v') && $error;
    }
    if (!$error) {
        db_query("UPDATE cours SET description = '', course_addon = '' WHERE course_id = $course_id");
    }

    if ($q and mysql_num_rows($q) > 0) {
        $error = false;
        while ($row = mysql_fetch_array($q, MYSQL_ASSOC)) {
            $error = add_unit_resource($id, 'description', $row['id'], $row['title'], html_cleanup($row['content']), 'i', $row['upDate']) && $error;
        }
        if (!$error) {
            db_query("DROP TABLE `$code`.course_description");
        }
    }
}

function upgrade_course_index_php($code) {
    global $langUpgNotIndex, $langCheckPerm;
    $course_base_dir = "$GLOBALS[webDir]/courses/$code";
    if (!is_writable($course_base_dir)) {
        echo "$langUpgNotIndex \"$course_base_dir\"! $langCheckPerm.<br>";
        return;
    }
    if (!($f = fopen("$course_base_dir/index.php", 'w'))) {
        echo "$langUpgNotIndex \"$course_base_dir/index.php\"! $langCheckPerm.<br>";
        return;
    }
    fwrite($f, "<?php\nsession_start();\n\$_SESSION['dbname']='$code';\n" .
            "include '../../modules/course_home/course_home.php';\n");
    fclose($f);
}

function move_group_documents_to_main_db($code, $course_id) {
    global $mysqlMainDb, $webDir, $group_document_upgrade_ok, $group_document_dir;

    $group_document_upgrade_ok = true;
    $q = db_query("SELECT id, secretDirectory FROM student_group");
    if (!$q) {
        // Group table doesn't exist in course database
        return false;
    }
    while ($r = mysql_fetch_array($q)) {
        $group_document_dir = $webDir . '/courses/' . $code . '/group/' . $r['secretDirectory'];
        list($new_group_id) = mysql_fetch_row(db_query("SELECT id FROM `$mysqlMainDb`.`group`
                                                                WHERE course_id = $course_id AND
                                                                      secret_directory = '$r[secretDirectory]'"));
        if (!is_dir($group_document_dir)) {
            if (file_exists($group_document_dir)) {
                unlink($group_document_dir);
            }
            mkdir($group_document_dir, 0775);
        } else {
            traverseDirTree($group_document_dir, 'group_documents_main_db_file', 'group_documents_main_db_dir', array($course_id, $new_group_id));
        }
    }
    return $group_document_upgrade_ok;
}

function group_documents_main_db_file($path, $data) {
    group_documents_main_db($path, $data[0], $data[1], get_file_extension($path));
}

function group_documents_main_db_dir($path, $data) {
    group_documents_main_db($path, $data[0], $data[1], '.dir');
}

function group_documents_main_db($path, $course_id, $group_id, $type) {
    global $group_document_upgrade_ok, $mysqlMainDb, $group_document_dir;

    $file_date = quote(date('Y-m-d H:i:s', filemtime($path)));
    $internal_path = quote(str_replace($group_document_dir, '', $path));
    list($filename) = mysql_fetch_row(db_query("SELECT `filename` FROM group_documents WHERE `path` = " . $internal_path));
    if (!db_query("INSERT INTO `$mysqlMainDb`.document SET
                              course_id = $course_id, subsystem = 1, subsystem_id = $group_id,
                              path = $internal_path, filename = " . quote($filename) . ",
                              format = '$type', visibility = 'v',
                              date = $file_date, date_modified = $file_date")) {
        $group_document_upgrade_ok = false;
    }
}

function mkdir_or_error($dirname) {
    global $langErrorCreatingDirectory;
    if (!is_dir($dirname)) {
        if (!mkdir($dirname, 0775)) {
            echo "<p class='caution'>$langErrorCreatingDirectory $dirname</p>";
        }
    }
}

function touch_or_error($filename) {
    global $langErrorCreatingDirectory;
    if (@!touch($filename)) {
        echo "<p class='caution'>$langErrorCreatingDirectory $filename</p>";
    }
}

// We need some messages from all languages to upgrade course accueil table
function load_global_messages() {
    global $global_messages, $native_language_names, $language_codes,
    $webDir, $siteName, $InstitutionUrl, $Institution;

    foreach ($native_language_names as $code => $name) {
        // include_messages
        include "$webDir/lang/$code/common.inc.php";
        $extra_messages = "config/{$language_codes[$code]}.inc.php";
        if (file_exists($extra_messages)) {
            include $extra_messages;
        } else {
            $extra_messages = false;
        }
        include "$webDir/lang/$code/messages.inc.php";
        if ($extra_messages) {
            include $extra_messages;
        }
        $global_messages['langCourseDescription'][$code] = $langCourseDescription;
        $global_messages['langCourseUnits'][$code] = $langCourseUnits;
        $global_messages['langGlossary'][$code] = $langGlossary;
        $global_messages['langEBook'][$code] = $langEBook;
        $global_messages['langVideo'][$code] = $langVideo;
    }
}

function html_cleanup($s) {
    // Fixes overescaping introduced by bug in older versions
    return str_replace(array('&quot;', '\\'), '', $s);
}

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

// ----------------------------------------------------------------
// Functions used for upgrade
// ----------------------------------------------------------------
//function to update a field in a table
function update_field($table, $field, $field_name, $id_col, $id) {
    $id = quote($id);
    $sql = "UPDATE `$table` SET `$field` = '$field_name' WHERE `$id_col` = $id;";
    Database::get()->query($sql);
}

// Adds field $field to table $table of current database, if it doesn't already exist
function add_field($table, $field, $type) {
    global $langToTable, $langAddField, $BAD;

    $fields = Database::get()->queryArray("SHOW COLUMNS FROM $table LIKE '$field'");
    if (count($fields) == 0) {
        if (!Database::get()->query("ALTER TABLE `$table` ADD `$field` $type")) {
            $retString = "$langAddField <b>$field</b> $langToTable <b>$table</b>: ";
            $retString .= " $BAD<br>";
            Debug::message($retString, Debug::ERROR);
        }
    }
}

function add_field_after_field($table, $field, $after_field, $type) {
    global $langToTable, $langAddField, $langAfterField, $BAD;

    $fields = Database::get()->queryArray("SHOW COLUMNS FROM $table LIKE '$field'");
    if (count($fields) == 0) {
        if (!Database::get()->query("ALTER TABLE `$table` ADD COLUMN `$field` $type AFTER `$after_field`")) {
            $retString = "$langAddField <b>$field</b> $langAfterField <b>$after_field</b> $langToTable <b>$table</b>: ";
            $retString .= " $BAD<br>";
            Debug::message($retString, Debug::ERROR);
        }
    }
}

function rename_field($table, $field, $new_field, $type) {
    global $langToA, $langRenameField, $langToTable, $BAD;

    $fields = Database::get()->queryArray("SHOW COLUMNS FROM $table LIKE '$new_field'");
    if (count($fields) == 0) {
        if (!Database::get()->query("ALTER TABLE `$table` CHANGE  `$field` `$new_field` $type")) {
            $retString = "$langRenameField <b>$field</b> $langToA <b>$new_field</b> $langToTable <b>$table</b>: ";
            $retString .= " $BAD<br>";
            Debug::message($retString, Debug::ERROR);
        }
    }
}

function delete_field($table, $field) {
    global $langOfTable, $langDeleteField, $BAD;

    if (!Database::get()->query("ALTER TABLE `$table` DROP `$field`")) {
        $retString = "$langDeleteField <b>$field</b> $langOfTable <b>$table</b>";
        $retString .= " $BAD<br>";
        Debug::message($retString, Debug::ERROR);
    }
}

function delete_table($table) {
    global $langDeleteTable, $BAD;
    $retString = "";

    if (!Database::get()->query("DROP TABLE IF EXISTS $table")) {
        $retString = "$langDeleteTable <b>$table</b>: ";
        $retString .= " $BAD<br>";
        Debug::message($retString, Debug::ERROR);
    }
}

function merge_tables($table_destination, $table_source, $fields_destination, $fields_source) {
    global $langMergeTables, $BAD;

    $query = "INSERT INTO $table_destination (";
    foreach ($fields_destination as $val) {
        $query.=$val . ",";
    }
    $query = substr($query, 0, -1) . ") SELECT ";
    foreach ($fields_source as $val) {
        $query.=$val . ",";
    }
    $query = substr($query, 0, -1) . " FROM " . $table_source;
    if (!Database::get()->query($query)) {
        $retString = " $langMergeTables <b>$table_destination</b>,<b>$table_source</b>";
        $retString .= " $BAD<br>";
        Debug::message($retString, Debug::ERROR);
    }
}

// add index/indexes in specific table columns
function add_index($index, $table, $column) {
    global $langIndexAdded, $langIndexExists, $langToTable;

    $num_of_args = func_num_args();
    if ($num_of_args <= 3) {
        $ind_sql = Database::get()->queryArray("SHOW INDEX FROM $table");
        foreach ($ind_sql as $i) {
            if ($i->Key_name == $index) {
                $retString = "<p>$langIndexExists $table</p>";
                return $retString;
            }
        }
        Database::get()->query("ALTER TABLE $table ADD INDEX `$index` ($column)");
    } else {
        $arguments = func_get_args();
        // cut the first and second argument
        array_shift($arguments);
        array_shift($arguments);
        $st = '';
        for ($j = 0; $j < count($arguments); $j++) {
            $st .= $arguments[$j] . ',';
        }
        $ind_sql = Database::get()->queryArray("SHOW INDEXES FROM `$table`");
        foreach ($ind_sql as $i) {
            if ($i->Key_name == $index) {
                $retString = "<p>$langIndexExists $table</p>";
                return $retString;
            }
        }
        $sql = "ALTER TABLE $table ADD INDEX `$index` ($st)";
        $sql = str_replace(',)', ')', $sql);
        Database::get()->query($sql);
    }
    $retString = "<p>$langIndexAdded $langToTable $table</p>";
    return $retString;
}

// Check MySQL for InnoDB storage engine support
function check_engine() {
    foreach (Database::get()->queryArray('SHOW ENGINES') as $item) {
        if ($item->Engine == 'InnoDB') {
            return $item->Support == 'YES' or $item->Support == 'DEFAULT';
        }
    }
    return false;
}

// Removes initial part of path from assignment_submit.file_path
function update_assignment_submit() {
    global $langTable;

    $updated = FALSE;
    Database::get()->queryFunc('SELECT id, file_path FROM assignment_submit', function ($i) use (&$updated) {
        $new = preg_replace('+^.*/work/+', '', $i->file_path);
        if ($new != $i->file_path) {
            Database::get()->query("UPDATE assignment_submit SET file_path = " .
                    quote($new) . " WHERE id = $i->id");
            $updated = TRUE;
        }
    });
    if ($updated) {
        Debug::message("$langTable assignment_submit: $GLOBALS[OK]<br>\n", Debug::WARNING);
    }
}

// checks if admin user
function is_admin($username, $password) {
    global $mysqlMainDb, $session;

    if (DBHelper::fieldExists('user', 'user_id')) {
        $user = Database::get()->querySingle("SELECT * FROM user, admin
				WHERE admin.idUser = user.user_id AND
				BINARY user.username = ?s", $username);
        $db_schema = 0;
    } else {
        $user = Database::get()->querySingle("SELECT * FROM user, admin
				WHERE admin.user_id = user.id AND
				BINARY user.username = ?s", $username);
        $db_schema = 1;
    }

    if (!$user) {
        return false;
    } else {
        if (isset($user->privilege) and $user->privilege !== '0')
            return false;

        $hasher = new PasswordHash(8, false);
        if (!$hasher->CheckPassword($password, $user->password)) {
            if (strlen($user->password) < 60 and md5($password) == $user->password) {
                return true;
            }
            return false;
        }

        if ($db_schema == 0) {
            $_SESSION['uid'] = $user->user_id;
            $_SESSION['givenname'] = $user->prenom;
            $_SESSION['surname'] = $user->nom;
            $_SESSION['status'] = $user->statut;
        } else {
            $_SESSION['uid'] = $user->id;
            $_SESSION['givenname'] = $user->givenname;
            $_SESSION['surname'] = $user->surname;
            $_SESSION['status'] = $user->status;
        }
        $_SESSION['email'] = $user->email;
        $_SESSION['uname'] = $username;
        $_SESSION['is_admin'] = true;
        $session->setLoginTimestamp();

        return true;
    }
}

/**
 * @brief Check whether an entry with the specified $define_var exists in the accueil table
 * @param type $define_var
 * @return boolean
 */
function accueil_tool_missing($db, $define_var) {

    $r = Database::get($db)->querySingle("SELECT id FROM accueil WHERE define_var = '$define_var'");
    if ($r) {
        return false;
    } else {
        return true;
    }
}

/**
 * @brief convert database and all tables to UTF-8
 * @global type $langNotTablesList
 * @param type $database
 */
function convert_db_utf8($database) {
    global $langNotTablesList;

    Database::get()->query("ALTER DATABASE `$database` DEFAULT CHARACTER SET=utf8");
    $result = Database::get()->queryArray("SHOW TABLES FROM `openeclass30`");
    if (!$result) {
        die("$langNotTablesList $database");
    }
    foreach ($result as $row) {
        $value = "Tables_in_$database";
        Database::get($database)->query("ALTER TABLE " . $row->$value . " CONVERT TO CHARACTER SET utf8");
    }
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
        Database::get()->query("UPDATE dropbox_file SET filename = '$new_filename'
	        	WHERE id = '$id'", $code);
    } else {
        Debug::message($langEncDropboxError, Debug::ERROR);
    }
}

/**
 * @brief Upgrade course database
 * @param type $code
 * @param type $lang
 */
function upgrade_course($code, $lang) {
            
    upgrade_course_2_1_3($code);
    upgrade_course_2_2($code, $lang);
    upgrade_course_2_3($code);
    upgrade_course_2_4($code, $lang);
    upgrade_course_2_5($code, $lang);
    upgrade_course_2_8($code, $lang);
    upgrade_course_2_9($code, $lang);
    upgrade_course_2_10($code);
    upgrade_course_2_11($code);
    upgrade_course_3_0($code);
    Database::forget();
}

/**
 * @brief upgrade to 3.0
 * @global type $langUpgCourse
 * @global type $mysqlMainDb
 * @global type $webDir
 * @param type $code
 * @param type $extramessage
 * @return type
 */
function upgrade_course_3_0($code, $course_id) {
    global $langUpgCourse, $mysqlMainDb, $webDir;

    Database::get()->query("USE `$code`");
    
    // move forum tables to central db
    if (DBHelper::tableExists('forums', $code)) {
        $forumcatid_offset = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.forum_category")->max;
        if (is_null($forumcatid_offset)) {
            $forumcatid_offset = 0;
        }
        Database::get()->query("UPDATE catagories SET cat_order = 0 WHERE cat_order IS NULL OR cat_order = ''");
        $ok = Database::get()->query("INSERT INTO `$mysqlMainDb`.`forum_category`
                        (`id`, `cat_title`, `cat_order`, `course_id`)
                        SELECT `cat_id` + $forumcatid_offset, `cat_title`,
                               `cat_order`, $course_id FROM catagories") != null;
        $forumid_offset = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.forum")->max;
        if (is_null($forumid_offset)) {
            $forumid_offset = 0;
        }
        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.`forum`
                        (`id`, `name`, `desc`, `num_topics`, `num_posts`, `last_post_id`, `cat_id`, `course_id`)
                        SELECT forum_id + $forumid_offset, forum_name, forum_desc, forum_topics,
                               forum_posts, forum_last_post_id, cat_id + $forumcatid_offset, $course_id
                        FROM forums ORDER by forum_id") != null) && $ok;
        $ok = (Database::get()->query("UPDATE `$mysqlMainDb`.group SET forum_id = forum_id + $forumid_offset
                                          WHERE course_id = $course_id") != null) && $ok;
        $forumtopicid_offset = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.forum_topic")->max;
        if (is_null($forumtopicid_offset)) {
            $forumtopicid_offset = 0;
        }
        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.`forum_topic`
                        (`id`, `title`, `poster_id`, `topic_time`, `num_views`, `num_replies`, `last_post_id`, `forum_id`)
                        SELECT topic_id + $forumtopicid_offset, topic_title, topic_poster, topic_time, topic_views,
                               topic_replies, topic_last_post_id, forum_id + $forumid_offset
                        FROM topics") != null) && $ok;
        $forumpostid_offset = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.forum_post")->max;
        if (is_null($forumpostid_offset)) {
            $forumpostid_offset = 0;
        }
        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.`forum_post`
                        (`id`, `topic_id`, `post_text`, `poster_id`, `post_time`, `poster_ip`)
                        SELECT p.post_id + $forumpostid_offset, p.topic_id + $forumtopicid_offset,
                               pt.post_text, p.poster_id, p.post_time, p.poster_ip
                        FROM posts p, posts_text pt
                        WHERE p.post_id = pt.post_id") != null) && $ok;
        $ok = (Database::get()->query("UPDATE `$mysqlMainDb`.`forum_topic` ft, `$mysqlMainDb`.`forum` f
                                       SET f.last_post_id = f.last_post_id + $forumpostid_offset,
                                           ft.last_post_id = ft.last_post_id + $forumpostid_offset
                                       WHERE ft.forum_id = f.id AND
                                             course_id = $course_id") != null) && $ok;
        $ok = (Database::get()->query("UPDATE `$mysqlMainDb`.forum_notify
                                       SET cat_id = cat_id + $forumcatid_offset,
                                           forum_id = forum_id + $forumid_offset,
                                           topic_id = topic_id + $forumtopicid_offset
                                       WHERE course_id = $course_id") != null) && $ok;
        if ($ok) {
            foreach (array('posts', 'topics', 'forums', 'catagories') as $table) {
                Database::get()->query('DROP TABLE ' . $table);
            }
        }
    }

    // move video/multimedia tables to central db and drop them
    if (DBHelper::tableExists('video_category', $code) and DBHelper::tableExists('video', $code) and DBHelper::tableExists('videolinks', $code)) {        
        // move video_category data
        $video_category_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.video_category")->max;
        if (is_null($video_category_offset)) {
            $video_category_offset = 0;
        }

        $ok = Database::get()->query("INSERT INTO `$mysqlMainDb`.video_category
                        (`id`, `course_id`, `name`, `description`)
                        SELECT `id` + ?d, ?d, `name`, `description` FROM video_category ORDER by id",
                    $video_category_offset, $course_id) && $ok;

        $ok = Database::get()->query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + ?d
                            WHERE units.id = res.unit_id AND course_id = ?d AND type = 'videolinkcategory'",
                    $video_category_offset, $course_id) && $ok;
           
        // move video data
        $video_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.video")->max;
        if (is_null($video_offset)) {
            $video_offset = 0;
        }

        if (!DBHelper::fieldExists('video', 'visible', $code)) {
            Database::get()->query("ALTER TABLE video ADD visible TINYINT(4) NOT NULL DEFAULT 1 AFTER date");
        }
        if (!DBHelper::fieldExists('video', 'public', $code)) {
            Database::get()->query("ALTER TABLE video ADD public TINYINT(4) NOT NULL DEFAULT 1 AFTER visible");
        }
        $ok = Database::get()->query("INSERT INTO `$mysqlMainDb`.video
                        (`id`, `course_id`, `path`, `url`, `title`, `description`, `category`, `creator`, `publisher`, `date`, `visible`, `public`)
                        SELECT `id` + ?d, ?d, `path`, `url`, `titre`, `description`, 
                               NULLIF(`category`, 0) + ?d,
                               COALESCE(`creator`, ''), COALESCE(`publisher`, ''),
                               `date`, `visible`, `public` FROM video ORDER by id",
                    $video_offset, $course_id, $video_category_offset) && $ok;
        
                
        $ok = Database::get()->query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + ?d
                            WHERE units.id = res.unit_id AND course_id = ?d AND type = 'video'",
                    $video_offset, $course_id) && $ok;
   
    
        // move videolink data
        $videolink_offset = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.videolink")->max;
        if (is_null($videolink_offset)) {
            $videolink_offset = 0;
        }

        $ok = Database::get()->query("INSERT INTO `$mysqlMainDb`.videolink
                        (`id`, `course_id`, `url`, `title`, `description`, `category`, `creator`, `publisher`, `date`, `visible`, `public`)
                        SELECT `id` + ?d, ?d, `url`, `titre`, `description`, 
                               NULLIF(`category`, 0) + ?d,
                               COALESCE(`creator`, ''), COALESCE(`publisher`, ''),
                               `date`, `visible`, `public` FROM videolinks ORDER by id",
                    $videolink_offset, $course_id, $video_category_offset) && $ok;
        

        $ok = Database::get()->query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + ?d 
                            WHERE units.id = res.unit_id AND course_id = ?d AND type IN ('videolink', 'videolinks')",
                    $videolink_offset, $course_id) && $ok;
        
        if ($ok) { // drop old tables
            Database::get()->query("DROP TABLE videolinks");
            Database::get()->query("DROP TABLE video_category");
            Database::get()->query("DROP TABLE video");
        }
    }

    // move dropbox to central db and drop tables
    if (DBHelper::tableExists('dropbox_file', $code) && DBHelper::tableExists('dropbox_person', $code) &&
            DBHelper::tableExists('dropbox_post', $code)) {

        $fileid_offset = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.dropbox_msg")->max;
        if (is_null($fileid_offset)) {
            $fileid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE dropbox_map AS
                   SELECT old.id AS old_id, old.id + $fileid_offset AS new_id
                     FROM dropbox_file AS old ORDER by id");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.dropbox_msg
                        (`id`, `course_id`, `author_id`, `subject`,
                         `body`, `timestamp`)
                        SELECT `id` + $fileid_offset, $course_id, `uploaderId`, `title`, `description`, UNIX_TIMESTAMP(`uploadDate`)
                               FROM dropbox_file ORDER BY id") != null) && $ok;

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.dropbox_attachment
                        (`msg_id`, `filename`, `real_filename`, `filesize`)
                        SELECT `id` + $fileid_offset, `filename`, `real_filename`, `filesize`
                               FROM dropbox_file WHERE `filename` != '' AND `filesize` != 0 ORDER BY id") != null) && $ok;

        Database::get()->query("CREATE TEMPORARY TABLE dropbox_temp_index (
                                    msg_id INT NOT NULL,
                                    recipient_id INT NOT NULL,
                                    is_read TINYINT NOT NULL DEFAULT 1,
                                    deleted TINYINT NOT NULL DEFAULT 1
                                    )");
        
        //we use dropbox_post to fill temp table with recipients and dropbox_file with senders
        Database::get()->query("INSERT INTO dropbox_temp_index
                        (`msg_id`, `recipient_id`)

                        SELECT id, uploaderId FROM dropbox_file");
        
        Database::get()->query("INSERT INTO dropbox_temp_index

                        (`msg_id`, `recipient_id`)

                        SELECT fileID, recipientId FROM dropbox_post");
        
        //Users present at dropbox_person but not in temp haven't deleted their messages

        Database::get()->query("UPDATE dropbox_temp_index t
                                       INNER JOIN dropbox_person p
                                         ON t.msg_id = p.fileID AND t.recipient_id = p.personId
                                     SET deleted = ?d", 0);
        
        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.dropbox_index
                         (`msg_id`, `recipient_id`, `is_read`, `deleted`)
                         SELECT DISTINCT dropbox_map.new_id, dropbox_temp_index.recipient_id, dropbox_temp_index.is_read, dropbox_temp_index.deleted
                           FROM dropbox_temp_index, dropbox_map
                          WHERE dropbox_temp_index.msg_id = dropbox_map.old_id
                          ORDER BY dropbox_temp_index.msg_id") != null) && $ok;

        Database::get()->query("DROP TEMPORARY TABLE dropbox_map");
        Database::get()->query("DROP TEMPORARY TABLE dropbox_temp_index");

        if (false !== $ok) {
            Database::get()->query("DROP TABLE dropbox_file");
            Database::get()->query("DROP TABLE dropbox_person");
            Database::get()->query("DROP TABLE dropbox_post");
        }
    }

    $lp_map = array();
    // move learn path to central db and drop tables
    if (DBHelper::tableExists('lp_learnPath', $code) && DBHelper::tableExists('lp_module', $code) &&
            DBHelper::tableExists('lp_asset', $code) && DBHelper::tableExists('lp_rel_learnPath_module', $code) &&
            DBHelper::tableExists('lp_user_module_progress', $code)) {

        // first change `visibility` field name and type to lp_learnPath table
        Database::get()->query("ALTER TABLE lp_learnPath CHANGE `visibility` `visibility` VARCHAR(5)");
        Database::get()->query("UPDATE lp_learnPath SET visibility = '1' WHERE visibility = 'SHOW'");
        Database::get()->query("UPDATE lp_learnPath SET visibility = '0' WHERE visibility = 'HIDE'");
        Database::get()->query("ALTER TABLE lp_learnPath CHANGE `visibility` `visible` TINYINT(4)");

        // first change `visibility` field name and type to lp_rel_learnPath_module table
        Database::get()->query("ALTER TABLE lp_rel_learnPath_module CHANGE `visibility` `visibility` VARCHAR(5)");
        Database::get()->query("UPDATE lp_rel_learnPath_module SET visibility = '1' WHERE visibility = 'SHOW'");
        Database::get()->query("UPDATE lp_rel_learnPath_module SET visibility = '0' WHERE visibility = 'HIDE'");
        Database::get()->query("ALTER TABLE lp_rel_learnPath_module CHANGE `visibility` `visible` TINYINT(4)");

        $asset_map = array();
        $rel_map = array();
        $rel_map[0] = 0;

        // ----- lp_learnPath DB Table ----- //
        $lpid_offset = Database::get()->querySingle("SELECT MAX(learnPath_id) as max FROM `$mysqlMainDb`.lp_learnPath")->max;
        if (is_null($lpid_offset)) {
            $lpid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE lp_map AS
                   SELECT old.learnPath_id AS old_id, old.learnPath_id + $lpid_offset AS new_id
                     FROM lp_learnPath AS old ORDER by learnPath_id");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.lp_learnPath
                         (`learnPath_id`, `course_id`, `name`, `comment`, `lock`, `visible`, `rank`)
                         SELECT `learnPath_id` + $lpid_offset, $course_id, `name`, `comment`, `lock`,
                         `visible`, `rank` FROM lp_learnPath ORDER BY learnPath_id") != null);

        // ----- lp_module DB Table ----- //
        $moduleid_offset = Database::get()->querySingle("SELECT MAX(module_id) as max FROM `$mysqlMainDb`.lp_module")->max;
        if (is_null($moduleid_offset)) {
            $moduleid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE module_map AS
                   SELECT old.module_id AS old_id, old.module_id + $moduleid_offset AS new_id
                     FROM lp_module AS old ORDER by module_id");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.lp_module
                         (`module_id`, `course_id`, `name`, `comment`, `accessibility`, `startAsset_id`,
                          `contentType`, `launch_data`)
                         SELECT `module_id` + $moduleid_offset, $course_id, `name`, `comment`,
                                `accessibility`, `startAsset_id`, `contentType`, `launch_data`
                           FROM lp_module ORDER by module_id") != null) && $ok;

        // ----- lp_asset DB Table ----- //
        $assetid_offset = Database::get()->querySingle("SELECT MAX(asset_id) as max FROM `$mysqlMainDb`.lp_asset")->max;
        if (is_null($assetid_offset)) {
            $assetid_offset = 0;
        }

        Database::get()->queryFunc("SELECT asset_id FROM lp_asset ORDER by asset_id", function ($row) use($assetid_offset, &$asset_map) {
            $oldid = intval($row->asset_id);
            $newid = $oldid + $assetid_offset;
            $asset_map[$oldid] = $newid;
        });

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.lp_asset
                         (`asset_id`, `module_id`, `path`, `comment`)
                         SELECT DISTINCT lp_asset.asset_id + $assetid_offset, module_map.new_id,
                                lp_asset.path, lp_asset.comment
                           FROM lp_asset, module_map
                          WHERE lp_asset.module_id = module_map.old_id
                           ORDER BY lp_asset.asset_id") != null) && $ok;

        foreach ($asset_map as $key => $value) {
            $ok = (Database::get()->query("UPDATE `$mysqlMainDb`.lp_module SET `startAsset_id` = $value
                             WHERE `course_id` = $course_id AND `startAsset_id` = $key") != null) && $ok;
        }

        // ----- lp_rel_learnPath_module DB Table ----- //
        $relid_offset = Database::get()->querySingle("SELECT MAX(learnPath_module_id) as max FROM `$mysqlMainDb`.lp_rel_learnPath_module")->max;
        if (is_null($relid_offset)) {
            $relid_offset = 0;
        }

        Database::get()->queryFunc("SELECT learnPath_module_id FROM lp_rel_learnPath_module ORDER by learnPath_module_id", function ($row) use($relid_offset, &$rel_map) {
            $oldid = intval($row->learnPath_module_id);
            $newid = $oldid + $relid_offset;
            $rel_map[$oldid] = $newid;
        });

        Database::get()->query("CREATE TEMPORARY TABLE rel_map AS
                   SELECT old.learnPath_module_id AS old_id, old.learnPath_module_id + $relid_offset AS new_id
                     FROM lp_rel_learnPath_module AS old ORDER by learnPath_module_id");
        Database::get()->query("INSERT INTO rel_map (old_id, new_id) VALUES (0, 0)");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.lp_rel_learnPath_module
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
                          ORDER BY lp_rel_learnPath_module.learnPath_module_id") != null) && $ok;

        foreach ($rel_map as $key => $value) {
            $ok = (Database::get()->query("UPDATE `$mysqlMainDb`.lp_rel_learnPath_module SET `parent` = $value
                             WHERE `learnPath_id` IN (SELECT learnPath_id FROM `$mysqlMainDb`.lp_learnPath WHERE course_id = $course_id)
                               AND `parent` = $key") != null) && $ok;
        }

        // ----- lp_user_module_progress DB Table ----- //
        $lumid_offset = Database::get()->querySingle("SELECT MAX(user_module_progress_id) as max FROM `$mysqlMainDb`.lp_user_module_progress")->max;
        if (is_null($lumid_offset)) {
            $lumid_offset = 0;
        }

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.lp_user_module_progress
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
                          ORDER BY lp_user_module_progress.user_module_progress_id") != null) && $ok;

        Database::get()->query("DROP TEMPORARY TABLE lp_map");
        Database::get()->query("DROP TEMPORARY TABLE module_map");
        Database::get()->query("DROP TEMPORARY TABLE rel_map");

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

            Database::get()->query("DROP TABLE lp_learnPath");
            Database::get()->query("DROP TABLE lp_module");
            Database::get()->query("DROP TABLE lp_asset");
            Database::get()->query("DROP TABLE lp_rel_learnPath_module");
            Database::get()->query("DROP TABLE lp_user_module_progress");
        }

        Database::get()->query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + $lpid_offset
                            WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'lp'");
    }

    $wiki_map = array();
    // move wiki to central db and drop tables
    if (DBHelper::tableExists('wiki_properties', $code) && DBHelper::tableExists('wiki_acls', $code) &&
            DBHelper::tableExists('wiki_pages', $code) && DBHelper::tableExists('wiki_pages_content', $code)) {

        // ----- wiki_properties and wiki_acls DB Tables ----- //
        $wikiid_offset = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.wiki_properties")->max;
        if (is_null($wikiid_offset)) {
            $wikiid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE wiki_map AS
                   SELECT old.id AS old_id, old.id + $wikiid_offset AS new_id
                     FROM wiki_properties AS old ORDER by id");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.wiki_properties
                         (`id`, `course_id`, `title`, `description`, `group_id`)
                         SELECT `id` + $wikiid_offset, $course_id, `title`, `description`, `group_id`
                           FROM wiki_properties ORDER BY id") != null);

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.wiki_acls
                         (`wiki_id`, `flag`, `value`)
                         SELECT DISTINCT wiki_map.new_id, wiki_acls.flag, wiki_acls.value
                           FROM wiki_acls, wiki_map
                          WHERE wiki_acls.wiki_id = wiki_map.old_id
                          ORDER BY wiki_acls.wiki_id") != null) && $ok;

        // ----- wiki_pages DB Table ----- //
        $wikipageid_offset = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.wiki_pages")->max;
        if (is_null($wikipageid_offset)) {
            $wikipageid_offset = 0;
        }

        $wiki_page_map = array();
        Database::get()->queryFunc("SELECT id FROM wiki_pages ORDER by id", function ($row) use($wikipageid_offset, &$wiki_page_map) {
            $oldid = intval($row->id);
            $newid = $oldid + $wikipageid_offset;
            $wiki_page_map[$oldid] = $newid;
        });

        Database::get()->query("CREATE TEMPORARY TABLE wikipage_map AS
                   SELECT old.id AS old_id, old.id + $wikipageid_offset AS new_id
                     FROM wiki_pages AS old ORDER by id");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.wiki_pages
                         (`id`, `wiki_id`, `owner_id`, `title`, `ctime`, `last_version`, `last_mtime`)
                         SELECT DISTINCT wiki_pages.id + $wikipageid_offset, wiki_map.new_id,
                                wiki_pages.owner_id, wiki_pages.title, wiki_pages.ctime,
                                wiki_pages.last_version, wiki_pages.last_mtime
                           FROM wiki_pages, wiki_map
                          WHERE wiki_pages.wiki_id = wiki_map.old_id
                          ORDER BY wiki_pages.id") != null) && $ok;

        // ----- wiki_pages_content DB Table ----- //
        $wikipagecontentid_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.wiki_pages_content")->max;
        if (is_null($wikipagecontentid_offset)) {
            $wikipagecontentid_offset = 0;
        }

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.wiki_pages_content
                         (`id`, `pid`, `editor_id`, `mtime`, `content`)
                         SELECT DISTINCT wiki_pages_content.id + $wikipagecontentid_offset,
                                wikipage_map.new_id, wiki_pages_content.editor_id,
                                wiki_pages_content.mtime, wiki_pages_content.content
                           FROM wiki_pages_content, wikipage_map
                          WHERE wiki_pages_content.pid = wikipage_map.old_id
                          ORDER BY wiki_pages_content.id") != null) && $ok;

        Database::get()->query("DROP TEMPORARY TABLE wiki_map");
        Database::get()->query("DROP TEMPORARY TABLE wikipage_map");

        if (false !== $ok) {
            Database::get()->query("DROP TABLE wiki_properties");
            Database::get()->query("DROP TABLE wiki_acls");
            Database::get()->query("DROP TABLE wiki_pages");
            Database::get()->query("DROP TABLE wiki_pages_content");
        }

        Database::get()->query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + $wikiid_offset
                            WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'wiki'");
    }


    // move polls to central db and drop tables
    if (DBHelper::tableExists('poll', $code) && DBHelper::tableExists('poll_answer_record', $code) &&
            DBHelper::tableExists('poll_question', $code) && DBHelper::tableExists('poll_question_answer', $code)) {

        // ----- poll DB Table ----- //
        $pollid_offset = Database::get()->querySingle("SELECT MAX(pid) AS max FROM `$mysqlMainDb`.poll")->max;
        if (is_null($pollid_offset)) {
            $pollid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE poll_map AS
                   SELECT old.pid AS old_id, old.pid + $pollid_offset AS new_id
                     FROM poll AS old ORDER by pid");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.poll
                         (`pid`, `course_id`, `creator_id`, `name`, `creation_date`, `start_date`, `end_date`, `active`)
                         SELECT `pid` + $pollid_offset, $course_id, `creator_id`, `name`, `creation_date`, `start_date`,
                                `end_date`, `active`
                           FROM poll ORDER BY pid") != null);

        // ----- poll_question DB Table ----- //
        $pollquestionid_offset = Database::get()->querySingle("SELECT MAX(pqid) AS max FROM `$mysqlMainDb`.poll_question")->max;
        if (is_null($pollquestionid_offset)) {
            $pollquestionid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE pollquestion_map AS
                   SELECT old.pqid AS old_id, old.pqid + $pollquestionid_offset AS new_id
                     FROM poll_question AS old ORDER by pqid");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.poll_question
                         (`pqid`, `pid`, `question_text`, `qtype`)
                         SELECT DISTINCT poll_question.pqid + $pollquestionid_offset, poll_map.new_id,
                                poll_question.question_text, poll_question.qtype
                           FROM poll_question, poll_map
                          WHERE poll_question.pid = poll_map.old_id
                          ORDER BY poll_question.pqid") != null) && $ok;

        // ----- poll_question_answer DB Table ----- //
        $pollanswerid_offset = Database::get()->querySingle("SELECT MAX(pqaid) AS max FROM `$mysqlMainDb`.poll_question_answer")->max;
        if (is_null($pollanswerid_offset)) {
            $pollanswerid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE pollanswer_map AS
                   SELECT old.pqaid AS old_id, old.pqaid + $pollanswerid_offset AS new_id
                     FROM poll_question_answer AS old ORDER by pqaid");
        Database::get()->query("INSERT INTO pollanswer_map (`old_id`, `new_id`) VALUES (0, 0)");
        Database::get()->query("INSERT INTO pollanswer_map (`old_id`, `new_id`) VALUES (-1, -1)");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.poll_question_answer
                         (`pqaid`, `pqid`, `answer_text`)
                         SELECT DISTINCT poll_question_answer.pqaid + $pollanswerid_offset,
                                pollquestion_map.new_id, poll_question_answer.answer_text
                           FROM poll_question_answer, pollquestion_map
                          WHERE poll_question_answer.pqid = pollquestion_map.old_id
                          ORDER BY poll_question_answer.pqaid") != null) && $ok;

        // ----- poll_answer_record DB Table ----- //
        $pollrecordid_offset = Database::get()->querySingle("SELECT MAX(arid) AS max FROM `$mysqlMainDb`.poll_answer_record")->max;
        if (is_null($pollrecordid_offset)) {
            $pollrecordid_offset = 0;
        }

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.poll_answer_record
                         (`arid`, `pid`, `qid`, `aid`, `answer_text`, `user_id`, `submit_date`)
                         SELECT DISTINCT poll_answer_record.arid + $pollrecordid_offset,
                                poll_map.new_id, pollquestion_map.new_id, pollanswer_map.new_id,
                                poll_answer_record.answer_text, poll_answer_record.user_id,
                                poll_answer_record.submit_date
                           FROM poll_answer_record, poll_map, pollquestion_map, pollanswer_map
                          WHERE poll_answer_record.pid = poll_map.old_id
                            AND poll_answer_record.qid = pollquestion_map.old_id
                            AND poll_answer_record.aid = pollanswer_map.old_id
                          ORDER BY poll_answer_record.arid") != null) && $ok;

        Database::get()->query("DROP TEMPORARY TABLE poll_map");
        Database::get()->query("DROP TEMPORARY TABLE pollquestion_map");
        Database::get()->query("DROP TEMPORARY TABLE pollanswer_map");

        if (false !== $ok) {
            Database::get()->query("DROP TABLE poll");
            Database::get()->query("DROP TABLE poll_answer_record");
            Database::get()->query("DROP TABLE poll_question");
            Database::get()->query("DROP TABLE poll_question_answer");
        }
    }

    $assignments_map = array();
    // move assignments to central db and drop tables
    if (DBHelper::tableExists('assignments', $code) && DBHelper::tableExists('assignment_submit', $code)) {

        // ----- assigments DB Table ----- //
        $assignmentid_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.assignment")->max;
        if (is_null($assignmentid_offset)) {
            $assignmentid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE assignments_map AS
                   SELECT old.id AS old_id, old.id + $assignmentid_offset AS new_id
                     FROM assignments AS old ORDER by id");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.assignment
                         (`id`, `course_id`, `title`, `description`, `comments`, `deadline`, `submission_date`,
                          `active`, `secret_directory`, `group_submissions`, `assign_to_specific`)
                         SELECT `id` + $assignmentid_offset, $course_id, `title`, `description`, `comments`,
                                `deadline`, `submission_date`, `active`, `secret_directory`, `group_submissions`, '0' 
                                FROM assignments ORDER BY id") != null);

        // ----- assigments DB Table ----- //
        $assignmentsubmitid_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.assignment_submit")->max;
        if (is_null($assignmentsubmitid_offset)) {
            $assignmentsubmitid_offset = 0;
        }

        Database::get()->query("UPDATE assignment_submit SET group_id = 0 WHERE group_id IS NULL");
        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.assignment_submit
                         (`id`, `uid`, `assignment_id`, `submission_date`, `submission_ip`, `file_path`, `file_name`,
                          `comments`, `grade`, `grade_comments`, `grade_submission_date`, `grade_submission_ip`,
                          `group_id`)
                         SELECT DISTINCT assignment_submit.id + $assignmentsubmitid_offset,
                                assignment_submit.uid, assignments_map.new_id,
                                assignment_submit.submission_date, assignment_submit.submission_ip,
                                assignment_submit.file_path, assignment_submit.file_name,
                                assignment_submit.comments,
                                CAST(REPLACE(NULLIF(assignment_submit.grade, ''), ',', '.') AS DECIMAL(10,2)), 
                                assignment_submit.grade_comments,
                                assignment_submit.grade_submission_date, assignment_submit.grade_submission_ip,
                                assignment_submit.group_id
                           FROM assignment_submit, assignments_map
                          WHERE assignment_submit.assignment_id = assignments_map.old_id
                          ORDER BY assignment_submit.id") != null) && $ok;

        Database::get()->query("DROP TEMPORARY TABLE assignments_map");

        if (false !== $ok) {
            Database::get()->query("DROP TABLE assignments");
            Database::get()->query("DROP TABLE assignment_submit");
        }

        Database::get()->query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                            SET res_id = res_id + $assignmentid_offset
                            WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'work'");
    }

    // move agenda to central db and drop table
    if (DBHelper::tableExists('agenda', $code)) {

        if (!DBHelper::fieldExists('agenda', 'visibility', $code)) {
            Database::get()->query("ALTER TABLE `$code`.agenda ADD `visibility` char(1) NOT NULL DEFAULT 'v'");
        }

        // ----- agenda DB Table ----- //
        Database::get()->query("UPDATE `$code`.agenda SET visibility = '1' WHERE visibility = 'v'");
        Database::get()->query("UPDATE `$code`.agenda SET visibility = '0' WHERE visibility = 'i'");

        $agendaid_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.agenda")->max;
        if (is_null($agendaid_offset)) {
            $agendaid_offset = 0;
        }

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.agenda
                         (`id`, `course_id`, `title`, `content`, `start`, `duration`, `visible`)
                         SELECT `id` + $agendaid_offset, $course_id, `titre`, `contenu`, CONCAT(day,' ',hour), `lasting`,
                                `visibility` FROM agenda ORDER BY id") != null);

        if (false !== $ok) {
            Database::get()->query("DROP TABLE agenda");
        }
    }

    $exercise_map = array();
    $exercise_map[0] = 0;
    // move exercises to central db and drop tables
    if (DBHelper::tableExists('exercices', $code) &&
            DBHelper::tableExists('exercise_user_record', $code) &&
            DBHelper::tableExists('questions', $code) &&
            DBHelper::tableExists('reponses', $code) &&
            DBHelper::tableExists('exercice_question', $code)) {

        // ----- exercices DB Table ----- //
        $exerciseid_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.exercise")->max;
        if (is_null($exerciseid_offset)) {
            $exerciseid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE exercise_map AS
                   SELECT old.id AS old_id, old.id + $exerciseid_offset AS new_id
                     FROM exercices AS old ORDER by id");
        Database::get()->query("INSERT INTO exercise_map (`old_id`, `new_id`) VALUES (0, 0)");

        Database::get()->query("UPDATE exercices SET active = 0 WHERE active IS NULL");
        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.exercise
                         (`id`, `course_id`, `title`, `description`, `type`, `start_date`, `end_date`,
                          `time_constraint`, `attempts_allowed`, `random`, `active`, `public`, `results`, `score`)
                         SELECT `id` + $exerciseid_offset, $course_id, `titre`, `description`, `type`,
                                `StartDate`, `EndDate`, `TimeConstrain`, `AttemptsAllowed`, `random`,
                                `active`, `public`, `results`, `score`
                           FROM exercices ORDER BY id") != null) && $ok;

        // ----- exercise_user_record DB Table ----- //
        $eurid_offset = Database::get()->querySingle("SELECT MAX(eurid) AS max FROM `$mysqlMainDb`.exercise_user_record")->max;
        if (is_null($eurid_offset)) {
            $eurid_offset = 0;
        }

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.exercise_user_record
                         (`eurid`, `eid`, `uid`, `record_start_date`, `record_end_date`, `total_score`,
                          `total_weighting`, `attempt`)
                         SELECT DISTINCT exercise_user_record.eurid + $eurid_offset, exercise_map.new_id,
                                exercise_user_record.uid, exercise_user_record.RecordStartDate,
                                exercise_user_record.RecordEndDate, exercise_user_record.TotalScore,
                                exercise_user_record.TotalWeighting, exercise_user_record.attempt
                           FROM exercise_user_record, exercise_map
                          WHERE exercise_user_record.eid = exercise_map.old_id
                          ORDER BY exercise_user_record.eurid") != null) && $ok;

        // ----- questions DB Table ----- //
        $questionid_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.exercise_question")->max;
        if (is_null($questionid_offset)) {
            $questionid_offset = 0;
        }

        Database::get()->query("CREATE TEMPORARY TABLE question_map AS
                   SELECT old.id AS old_id, old.id + $questionid_offset AS new_id
                     FROM questions AS old ORDER by id");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.exercise_question
                         (`id`, `course_id`, `question`, `description`, `weight`, `q_position`, `type`)
                         SELECT `id` + $questionid_offset, $course_id, `question`, `description`, `ponderation`,
                                `q_position`, `type`
                           FROM questions ORDER BY id") != null) && $ok;

        // ----- reponses DB Table ----- //
        $answerid_offset = Database::get()->querySingle("SELECT MAX(id) AS max FROM `$mysqlMainDb`.exercise_answer")->max;
        if (is_null($answerid_offset)) {
            $answerid_offset = 0;
        }

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.exercise_answer
                         (`id`, `question_id`, `answer`, `correct`, `comment`, `weight`, `r_position`)
                         SELECT DISTINCT reponses.id + $answerid_offset, question_map.new_id,
                                reponses.reponse, reponses.correct, reponses.comment, reponses.ponderation,
                                reponses.r_position
                           FROM reponses, question_map
                          WHERE reponses.question_id = question_map.old_id
                          ORDER BY reponses.id") != null) && $ok;

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.exercise_with_questions
                         (`question_id`, `exercise_id`)
                         SELECT DISTINCT question_map.new_id, exercise_map.new_id
                           FROM exercice_question, exercise_map, question_map
                          WHERE exercice_question.exercice_id = exercise_map.old_id
                            AND exercice_question.question_id = question_map.old_id") != null) && $ok;

        Database::get()->query("DROP TEMPORARY TABLE exercise_map");
        Database::get()->query("DROP TEMPORARY TABLE question_map");

        if (false !== $ok) {
            Database::get()->query("DROP TABLE exercices");
            Database::get()->query("DROP TABLE exercise_user_record");
            Database::get()->query("DROP TABLE questions");
            Database::get()->query("DROP TABLE reponses");
            Database::get()->query("DROP TABLE exercice_question");
        }

        Database::get()->query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                     SET res_id = res_id + $exerciseid_offset
                   WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'exercise'");
        Database::get()->query("UPDATE `$mysqlMainDb`.lp_module AS module, `$mysqlMainDb`.lp_asset AS asset
                     SET path = path + $exerciseid_offset
                   WHERE module.startAsset_id = asset.asset_id AND course_id = $course_id AND contentType = 'EXERCISE'");
    }

    // move table `actions`, `actions_summary`, `login` in main DB
    Database::get()->queryFunc("SELECT `user_id`, `module_id`, $course_id AS `course_id`,
                               COUNT(`id`) AS `hits`, SUM(`duration`) AS `duration`,
                               DATE(`date_time`) AS `day`
                            FROM `actions`
                            GROUP BY DATE(`date_time`), `user_id`, `module_id`", function ($row) use ($mysqlMainDb) {
        Database::get()->query("INSERT INTO `$mysqlMainDb`.`actions_daily`
                        (`id`, `user_id`, `module_id`, `course_id`, `hits`, `duration`, `day`, `last_update`) 
                        VALUES 
                        (NULL, $row->user_id, $row->module_id, $row->course_id, $row->hits, $row->duration, '$row->day', NOW())");
    });

    Database::get()->query("INSERT INTO `$mysqlMainDb`.actions_summary
                (module_id, visits, start_date, end_date, duration, course_id)
                SELECT module_id, visits, start_date, end_date, duration, $course_id
                FROM actions_summary");

    Database::get()->query("INSERT INTO `$mysqlMainDb`.logins
                (user_id, ip, date_time, course_id)
                SELECT user_id, ip, date_time, $course_id
                FROM logins");


    // -------------------------------------------------------
    // Move table `accueil` to table `course_module` in main DB
    // -------------------------------------------------------
    // external links are moved to table `links` with category = -1
    $q1 = Database::get()->query("INSERT IGNORE INTO `$mysqlMainDb`.link
                               (course_id, url, title, category, description)
                               SELECT $course_id, lien, rubrique, -1, '' FROM accueil
                                      WHERE define_var = 'HTML_PAGE' OR
                                            image = 'external_link'");

    $q2 = Database::get()->query("INSERT IGNORE INTO `$mysqlMainDb`.course_module
                        (module_id, visible, course_id)
                SELECT id, visible, $course_id FROM accueil
                WHERE define_var NOT IN ('MODULE_ID_TOOLADMIN',
                                         'MODULE_ID_COURSEINFO',
                                         'MODULE_ID_USERS',
                                         'MODULE_ID_USAGE',
                                         'HTML_PAGE') AND
                      image <> 'external_link'");

    Database::get()->query("INSERT INTO `$mysqlMainDb`.course_module (module_id, visible, course_id)
                                    VALUES (".MODULE_ID_GRADEBOOK.", 0, $course_id)");
    Database::get()->query("INSERT INTO `$mysqlMainDb`.course_module (module_id, visible, course_id)
                                    VALUES (".MODULE_ID_ATTENDANCE.", 0, $course_id)");
    Database::get()->query("INSERT INTO `$mysqlMainDb`.course_module (module_id, visible, course_id)
                                    VALUES (".MODULE_ID_BLOG.", 0, $course_id)");
    Database::get()->query("INSERT INTO `$mysqlMainDb`.course_module (module_id, visible, course_id)
                                    VALUES (".MODULE_ID_BBB.", 0, $course_id)");
    
    
    if ($q1 and $q2) { // if everything ok drop course db
        // finally drop database
        Database::get()->query("DROP DATABASE `$code`");
    }
        
    // refresh XML metadata
    Database::get()->query("USE `$mysqlMainDb`");
    require_once "modules/course_metadata/CourseXML.php";
    if (file_exists(CourseXMLConfig::getCourseXMLPath($code))) {
        CourseXMLElement::refreshCourse($course_id, $code, true);
    }
}

/**
 * @brief upgrade to 3.0rc2
 * @param string $code
 * @param int    $course_id
 */
function upgrade_course_3_0_rc2($code, $course_id) {
    
    global $mysqlMainDb;
    
    Database::get()->query("USE `$mysqlMainDb`");
    // refresh XML metadata
    require_once "modules/course_metadata/CourseXML.php";
    if (file_exists(CourseXMLConfig::getCourseXMLPath($code))) {
        CourseXMLElement::refreshCourse($course_id, $code, true);
    }
}

/**
 * @brief upgrade to 2.11
 * @global type $langUpgCourse
 * @param type $code
 * @param type $lang
 * @param type $extramessage
 */
function upgrade_course_2_11($code) {
    
    global $langUpgCourse;
       
    Database::get()->query("USE `$code`");
    
    if (!DBHelper::fieldExists('video', 'category', $code)) {
        Database::get()->query("ALTER TABLE video ADD category INT(6) DEFAULT NULL AFTER description");
    }
    
    if (!DBHelper::fieldExists('videolinks', 'category', $code)) {
        Database::get()->query("ALTER TABLE videolinks ADD category INT(6) DEFAULT NULL AFTER description");
    }
    
    if (!DBHelper::tableExists('video_category', $code)) {
        Database::get()->query("CREATE TABLE video_category (
            id int(6) NOT NULL auto_increment, 
            name varchar(255) NOT NULL, 
            description text DEFAULT NULL, 
            `order` int(6) NOT NULL,                
            PRIMARY KEY (id)) DEFAULT CHARACTER SET=utf8");
    }
}

/**
 * @brief upgrade to 2.10
 * @global type $langUpgCourse
 * @param type $code
 * @param type $course_id
 * @param type $extramessage
 */
function upgrade_course_2_10($code, $course_id) {
    global $langUpgCourse;

    Database::get()->query("USE `$code`");
    
    Database::get()->query("ALTER TABLE `dropbox_file` CHANGE `description` `description` TEXT");

    // refresh XML metadata
    require_once "modules/course_metadata/CourseXML.php";
    if (file_exists(CourseXMLConfig::getCourseXMLPath($code))) {
        CourseXMLElement::refreshCourse($course_id, $code, true);
    }
    if (!DBHelper::fieldExists('poll', 'description', $code)) {
        Database::get()->query('ALTER TABLE poll ADD description MEDIUMTEXT NOT NULL,
                                   ADD end_message MEDIUMTEXT NOT NULL,
                                   ADD anonymized INT(1) NOT NULL DEFAULT 0');
        Database::get()->query('ALTER TABLE poll_question
                    CHANGE qtype qtype tinyint(3) UNSIGNED NOT NULL');
    }
}

/**
 * @brief upgrade to 2.9
 * @global type $langUpgCourse* 
 * @param type $code
 * @param type $lang
 * @param type $extramessage
 */
function upgrade_course_2_9($code, $lang) {

    global $langUpgCourse;

    Database::get()->query("USE `$code`");
    
    if (!DBHelper::fieldExists('dropbox_file', 'real_filename', $code)) {
        Database::get()->query("ALTER TABLE `dropbox_file` ADD `real_filename` VARCHAR(255) NOT NULL DEFAULT '' AFTER `filename`");
        Database::get()->query("UPDATE dropbox_file SET real_filename = filename");
    }
}

/**
 * @brief upgrade to 2.8
 * @global type $langUpgCourse
 * @global type $global_messages
 * @param type $code
 * @param type $lang
 * @param type $extramessage
 * 
 */
function upgrade_course_2_8($code, $lang) {

    global $langUpgCourse, $global_messages;

    Database::get()->query("USE `$code`");
    
    DBHelper::fieldExists('exercices', 'public', $code) or
            Database::get()->query("ALTER TABLE `exercices` ADD `public` TINYINT(4) NOT NULL DEFAULT 1 AFTER `active`");
    DBHelper::fieldExists('video', 'visible', $code) or
            Database::get()->query("ALTER TABLE `video` ADD `visible` TINYINT(4) NOT NULL DEFAULT 1 AFTER `date`");
    DBHelper::fieldExists('video', 'public', $code) or
            Database::get()->query("ALTER TABLE `video` ADD `public` TINYINT(4) NOT NULL DEFAULT 1");
    DBHelper::fieldExists('videolinks', 'visible', $code) or
            Database::get()->query("ALTER TABLE `videolinks` ADD `visible` TINYINT(4) NOT NULL DEFAULT 1 AFTER `date`");
    DBHelper::fieldExists('videolinks', 'public', $code) or
            Database::get()->query("ALTER TABLE `videolinks` ADD `public` TINYINT(4) NOT NULL DEFAULT 1");
    if (DBHelper::indexExists('dropbox_file', 'UN_filename', $code)) {
        Database::get()->query("ALTER TABLE dropbox_file DROP index UN_filename");
    }
    Database::get()->query("ALTER TABLE dropbox_file CHANGE description description VARCHAR(500)");
    Database::get()->query("UPDATE accueil SET rubrique = " . quote($global_messages['langDropBox'][$lang]) . "
                                    WHERE id = 16 AND define_var = 'MODULE_ID_DROPBOX'");
}

/**
 * @brief upgrade to 2.5
 * @global type $langUpgCourse
 * @global type $global_messages
 * @param type $code
 * @param type $lang
 * @param type $extramessage
 */
function upgrade_course_2_5($code, $lang) {

    global $langUpgCourse, $global_messages;

    Database::get()->query("USE `$code`");
    
    Database::get()->query("UPDATE `accueil` SET `rubrique` = " .
            quote($global_messages['langVideo'][$lang]) . "
                        WHERE `define_var` = 'MODULE_ID_VIDEO'");

    Database::get()->query("ALTER TABLE `assignments`
                        CHANGE `deadline` `deadline` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    Database::get()->query("ALTER TABLE `assignments`
                        CHANGE `submission_date` `submission_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    Database::get()->query("ALTER TABLE `assignment_submit`
                        CHANGE `submission_date` `submission_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    Database::get()->query("ALTER TABLE `poll`
                        CHANGE `start_date` `start_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    Database::get()->query("ALTER TABLE `poll`
                        CHANGE `end_date` `end_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    Database::get()->query("ALTER TABLE `poll`
                        CHANGE `creation_date` `creation_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    Database::get()->query("ALTER TABLE `poll_answer_record`
                        CHANGE `submit_date` `submit_date` DATETIME
                        NOT NULL DEFAULT '0000-00-00 00:00:00'");

    Database::get()->query("ALTER TABLE `exercices`
                        CHANGE `StartDate` `StartDate` DATETIME
                        DEFAULT NULL");

    Database::get()->query("ALTER TABLE `exercices`
                        CHANGE `EndDate` `EndDate` DATETIME
                        DEFAULT NULL");

    Database::get()->query("ALTER TABLE `lp_module`
                        CHANGE `contentType`
                        `contentType` ENUM('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK','MEDIA','MEDIALINK')");
}

/**
 * @brief upgrade to 2.4
 * @global type $langUpgCourse
 * @global type $mysqlMainDb
 * @global type $global_messages
 * @global type $webDir
 * @param type $code
 * @param type $lang
 * @param type $extramessage
 */
function upgrade_course_2_4($code, $course_id, $lang) {
    global $langUpgCourse, $mysqlMainDb, $global_messages, $webDir;
    
    Database::get()->query("USE `$code`");
    
    // not needed anymore
    delete_table('stat_accueil');
    delete_table('users');

    Database::get()->query("UPDATE accueil SET lien = '../../modules/course_info/infocours.php'
                                 WHERE id = 14 AND define_var = 'MODULE_ID_COURSEINFO'");
    Database::get()->query("UPDATE accueil SET rubrique = " .
            quote($global_messages['langCourseDescription'][$lang]) . "
                                        WHERE id = 20 AND define_var = 'MODULE_ID_DESCRIPTION'");
    Database::get()->query("UPDATE accueil SET lien = REPLACE(lien, ' \"target=_blank', '')
                                 WHERE lien LIKE '% \"target=_blank'");
    Database::get()->query("ALTER TABLE `poll_answer_record` CHANGE `answer_text` `answer_text` TEXT");

    // move main documents to central table and if successful drop table
    if (DBHelper::tableExists('document', $code)) {
        $doc_id = Database::get()->querySingle("SELECT MAX(id) as max FROM `$mysqlMainDb`.document")->max;
        if (!$doc_id) {
            $doc_id = 1;
        }
        Database::get()->query("UPDATE `document` SET visibility = '1' WHERE visibility = 'v'");
        Database::get()->query("UPDATE `document` SET visibility = '0' WHERE visibility = 'i'");
        Database::get()->query("ALTER TABLE `document`
                                CHANGE `visibility` `visible` TINYINT(4) NOT NULL DEFAULT 1");
        Database::get()->query("INSERT INTO `$mysqlMainDb`.document
                                (`id`, `course_id`, `subsystem`, `subsystem_id`, `path`, `filename`, `visible`, `comment`,
                                 `category`, `title`, `creator`, `date`, `date_modified`, `subject`,
                                 `description`, `author`, `format`, `language`, `copyrighted`)
                                SELECT $doc_id + id, $course_id, 0, NULL, `path`, `filename`, `visible`, `comment`,
                                       0 + `category`, `title`, `creator`, `date`, `date_modified`, `subject`,
                                       `description`, `author`, `format`, `language`, 0 + `copyrighted` FROM document") and
        Database::get()->query("DROP TABLE document");
        Database::get()->query("UPDATE `$mysqlMainDb`.course_units AS units, `$mysqlMainDb`.unit_resources AS res
                                 SET res_id = res_id + $doc_id
                                 WHERE units.id = res.unit_id AND course_id = $course_id AND type = 'doc'");
    }

    // move user group information to central tables and if successful drop original tables
    if (DBHelper::tableExists('group_properties', $code)) {
        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.`group_properties`
                                (`course_id`, `self_registration`, `private_forum`, `forum`, `documents`,
                                 `wiki`, `agenda`)
                                SELECT $course_id, `self_registration`, `private`, `forum`, `document`,
                                        `wiki`, `agenda` FROM group_properties") != null);
        if ($ok) {
            Database::get()->query("DROP TABLE group_properties");
        }

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.`group`
                                (`course_id`, `name`, `description`, `forum_id`, `max_members`,
                                 `secret_directory`)
                                SELECT $course_id, `name`, `description`, `forumId`, `maxStudent`,
                                        `secretDirectory` FROM student_group") != null);

        Database::get()->query("CREATE TEMPORARY TABLE group_map AS
                                SELECT old.id AS old_id, new.id AS new_id
                                        FROM student_group AS old, `$mysqlMainDb`.`group` AS new
                                        WHERE new.course_id = $course_id AND
                                              old.secretDirectory = new.secret_directory");

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.group_members
                                        (group_id, user_id, is_tutor, description)
                                        SELECT DISTINCT new_id, tutor, 1, ''
                                                FROM student_group, group_map
                                                WHERE student_group.id = group_map.old_id AND
                                                      tutor IS NOT NULL") != null) && $ok;

        $ok = (Database::get()->query("INSERT INTO `$mysqlMainDb`.group_members
                                        (group_id, user_id, is_tutor, description)
                                        SELECT DISTINCT new_id, user, 0, ''
                                                FROM user_group, group_map
                                                WHERE user_group.team = group_map.old_id") != null) && $ok;

        Database::get()->query("DROP TEMPORARY TABLE group_map");

        $ok = move_group_documents_to_main_db($code, $course_id) && $ok;

        if ($ok) {
            Database::get()->query("DROP TABLE student_group");
            Database::get()->query("DROP TABLE user_group");
            Database::get()->query("DROP TABLE group_documents");
        }
    }

    // move links to central tables and if successful drop original tables
    if (DBHelper::tableExists('liens', $code)) {
        Database::get()->query("INSERT INTO `$mysqlMainDb`.link
                                (`id`, `course_id`, `url`, `title`, `description`, `category`, `order`)
                                SELECT `id`, $course_id, `url`, `titre`, `description`, `category`, `ordre` FROM liens") and
                Database::get()->query("DROP TABLE liens");
        Database::get()->query("INSERT INTO `$mysqlMainDb`.link_category
                                (`id`, `course_id`, `name`, `description`, `order`)
                                SELECT `id`, $course_id, `categoryname`, `description`, `ordre` FROM link_categories") and
                Database::get()->query("DROP TABLE link_categories");
    }

    // upgrade acceuil for glossary
    if (accueil_tool_missing($code, 'MODULE_ID_GLOSSARY')) {
        Database::get()->query("INSERT IGNORE INTO accueil VALUES (
                                '17',
                                '{$global_messages['langGlossary'][$lang]}',
                                '../../modules/glossary/glossary.php',
                                'glossary',
                                '0',
                                '0',
                                '',
                                'MODULE_ID_GLOSSARY')");
    }

    // upgrade acceuil for glossary
    if (accueil_tool_missing($code, 'MODULE_ID_EBOOK')) {
        Database::get()->query("INSERT IGNORE INTO accueil VALUES (
                                '18',
                                '{$global_messages['langEBook'][$lang]}',
                                '../../modules/ebook/index.php',
                                'ebook',
                                '0',
                                '0',
                                '',
                                'MODULE_ID_EBOOK')");
    }
    // upgrade poll_question
    Database::get()->query("ALTER TABLE `poll_question` CHANGE `pqid` `pqid` BIGINT(12) NOT NULL AUTO_INCREMENT");

    // move old chat files
    $courses_dir = "$webDir/courses/";
    foreach (array('chat.txt', 'tmpChatArchive.txt') as $f) {
        $old_chat = $courses_dir . $code . '.' . $f;
        if (file_exists($old_chat)) {
            rename($old_chat, $courses_dir . $code . '/' . $f);
        }
    }
}

/**
 * @brief upgrade to 2.3
 * @global type $langUpgCourse
 * @param type $code
 * @param type $extramessage
 */
function upgrade_course_2_3($code) {
    global $langUpgCourse;

    Database::get()->query("USE `$code`");
    
    // upgrade exercises
    if (!DBHelper::fieldExists('exercices', 'score', $code))
        echo add_field('exercices', 'score', "TINYINT(1) NOT NULL DEFAULT '1'");
}

/**
 * @brief upgrade to 2.2
 * @global type $langUpgCourse
 * @global type $global_messages
 * @param type $code
 * @param type $lang
 * @param type $extramessage
 */
function upgrade_course_2_2($code, $lang) {
    global $langUpgCourse, $global_messages;

    Database::get()->query("USE `$code`");

    // upgrade exercises
    Database::get()->query("ALTER TABLE `exercise_user_record`
		CHANGE `RecordStartDate` `RecordStartDate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    Database::get()->query("ALTER TABLE `exercise_user_record`
		CHANGE `RecordEndDate` `RecordEndDate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    if (!DBHelper::fieldExists('exercices', 'results', $code))
        echo add_field('exercices', 'results', "TINYINT(1) NOT NULL DEFAULT '1'");
    Database::get()->query("ALTER TABLE `questions` CHANGE `ponderation` `ponderation` FLOAT(11,2) NULL DEFAULT NULL");
    Database::get()->query("ALTER TABLE `reponses` CHANGE `ponderation` `ponderation` FLOAT(5,2) NULL DEFAULT NULL");
    // not needed anymore
    echo delete_table('mc_scoring');

    if (accueil_tool_missing($code, 'MODULE_ID_UNITS')) {
        Database::get()->query("INSERT INTO accueil VALUES (
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
    Database::get()->query("ALTER TABLE `lp_module`
		CHANGE `contentType` `contentType` ENUM('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK')");
    Database::get()->query("ALTER TABLE `liens` CHANGE `url` `url` VARCHAR(255) DEFAULT NULL");
    Database::get()->query("ALTER TABLE `liens` CHANGE `titre` `titre` VARCHAR(255) DEFAULT NULL");
    // indexes creation
    add_index('optimize', 'lp_user_module_progress', 'user_id', 'learnPath_module_id');
    add_index('actionsindex', 'actions', 'module_id', 'date_time');
}

/**
 * upgrade to 2.1.3
 * @global type $mysqlMainDb
 * @global type $langEncodeDropBoxDocuments
 * @global type $langUpgCourse
 * @param type $code
 * @param type $extramessage
 */
function upgrade_course_2_1_3($code) {
    global $langEncodeDropBoxDocuments, $langUpgCourse;

    Database::get()->query("USE `$code`");

    // added field visibility in agenda
    if (!DBHelper::fieldExists('agenda', 'visibility', $code))
        echo add_field('agenda', 'visibility', "CHAR(1) NOT NULL DEFAULT 'v'");

    // upgrade dropbox
    echo "$langEncodeDropBoxDocuments<br>";
    Database::get()->queryFunc("SELECT id, filename, title FROM dropbox_file", function ($dbox) use ($code) {
        encode_dropbox_documents($code, $dbox->id, $dbox->filename, $dbox->title);
    });

    // upgrade lp_module me to kainourio content type
    Database::get()->query("ALTER TABLE `lp_module`
		CHANGE `contentType` `contentType` ENUM('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK')");
}

// Remove the first component from beginning of $path, return the rest starting with '/'
function trim_path($path) {
    return preg_replace('|^[^/]*/|', '/', $path);
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
    $q = Database::get()->queryArray("SELECT id, secretDirectory FROM student_group");
    if (!$q) {
        // Group table doesn't exist in course database
        return false;
    }
    foreach ($q as $r) {
        $group_document_dir = $webDir . '/courses/' . $code . '/group/' . $r->secretDirectory;
        $new_group_id = Database::get()->querySingle("SELECT id FROM `$mysqlMainDb`.`group`
                                                                WHERE course_id = ?d AND
                                                                      secret_directory = '$r->secretDirectory'", $course_id)->id;
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
    $filename = Database::get()->querySingle("SELECT `filename` FROM group_documents WHERE `path` = ?s", $internal_path)->filename;
    if(!empty($filename)) {
        if (!Database::get()->query("INSERT INTO `$mysqlMainDb`.document SET
                                  course_id = ?d, subsystem = 1, subsystem_id = ?d,
                                  path = ?s, filename = ?s,
                                  format = ?s, visible = 1,
                                  date = ?d, date_modified = ?d", $course_id, $group_id, $internal_path, $filename, $type, $file_date, $file_date)) {
            $group_document_upgrade_ok = false;
        }
    }
}

function mkdir_or_error($dirname) {
    global $langErrorCreatingDirectory;
    if (!is_dir($dirname)) {
        if (!mkdir($dirname, 0775)) {
            echo "<div class='alert alert-danger'>$langErrorCreatingDirectory $dirname</div>";
        }
    }
}

function touch_or_error($filename) {
    global $langErrorCreatingDirectory;
    if (@!touch($filename)) {
        echo "<div class='alert alert-danger'>$langErrorCreatingDirectory $filename</div>";
    }
}

// We need some messages from all languages to upgrade course accueil table
function load_global_messages() {
    global $global_messages, $session, $webDir, $language_codes;
    // these may seem unused, but they are needed when including messages.inc.php
    global $siteName, $InstitutionUrl, $Institution;

    foreach ($session->native_language_names as $code => $name) {
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
        $global_messages['langDropBox'][$code] = $langDropBox;
    }
}

function html_cleanup($s) {
    // Fixes overescaping introduced by bug in older versions
    return str_replace(array('&quot;', '\\'), '', $s);
}

// Quote string for output in config.php file
function quote($s) {
    return "'" . addslashes(canonicalize_whitespace($s)) . "'";
}


/**
 * @brief fix multiple usernames if exist
 * @global type $langUpgradeMulUsernames
 * @global type $langUpgradeChangeUsername
 */
function fix_multiple_usernames()  {

    global $langUpgradeMulUsernames, $langUpgradeChangeUsername, $tool_content;

    $q1 = Database::get()->queryArray("SELECT username, COUNT(*) AS nb
                                       FROM user GROUP BY BINARY username HAVING nb > 1 ORDER BY nb DESC");
    if ($q1) {
        $tool_content .= "<div class='alert alert-warning'>";
        $tool_content .= $langUpgradeMulUsernames;
        $tool_content .= "<p>&nbsp;</p>";

        foreach ($q1 as $u) {
            $q2 = Database::get()->queryArray("SELECT user_id, username FROM user
                WHERE BINARY username = ?s ORDER BY user_id DESC", $u->username);
            $i = 0;
            foreach ($q2 as $uid) {
                while (++$i) {
                    $new_username = $uid->username . $i;
                    // check if new username exists 
                    if (!Database::get()->querySingle("SELECT user_id FROM user WHERE BINARY username = ?s", $new_username)) {
                        Database::get()->query("UPDATE user SET username = ?s WHERE user_id = ?d", $new_username, $uid->user_id);
                        $tool_content .= sprintf($langUpgradeChangeUsername, $uid->username, $new_username) . "<br>";
                        break;
                    }
                }
            }
        }
        $tool_content .= "</div>";
    }
}

function importThemes($themes = null) {
    global $webDir;
    if (!isset($themes) || isset($themes) && !empty($themes)) {
        require_once "$webDir/include/pclzip/pclzip.lib.php";
        $themesDir = "$webDir/template/$_SESSION[theme]/themes";
        if(!is_dir("$webDir/courses/theme_data")) mkdir("$webDir/courses/theme_data", 0755, true);
        if (is_dir($themesDir) && $handle = opendir($themesDir)) {
            if (!isset($themes)) {
                while (false !== ($file_name = readdir($handle))) {
                    if ($file_name != "." && $file_name != "..") {
                        installTheme($themesDir, $file_name);
                    }                 
                }                
            } else {
                while (false !== ($file_name = readdir($handle))) {
                    if ($file_name != "." && $file_name != ".." && in_array($file_name, $themes)) {
                        installTheme($themesDir, $file_name);
                    }                 
                }
            }
            closedir($handle);
        }
    }
}
function installTheme($themesDir, $file_name) {
    global $webDir;
    if (copy("$themesDir/$file_name", "$webDir/courses/theme_data/$file_name")) {                   
        $archive = new PclZip("$webDir/courses/theme_data/$file_name");
        if (!$archive->extract(PCLZIP_OPT_PATH, "$webDir/courses/theme_data/temp")) {
            die("Error : ".$archive->errorInfo(true));
        } else {
            unlink("$webDir/courses/theme_data/$file_name");
            $base64_str = file_get_contents("$webDir/courses/theme_data/temp/theme_options.txt");
            unlink("$webDir/courses/theme_data/temp/theme_options.txt");
            $theme_options = unserialize(base64_decode($base64_str));                
            $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles) VALUES(?s, ?s)", $theme_options->name, $theme_options->styles)->lastInsertID;
            @rename("$webDir/courses/theme_data/temp/$theme_options->id", "$webDir/courses/theme_data/$new_theme_id");
            recurse_copy("$webDir/courses/theme_data/temp","$webDir/courses/theme_data");
            removeDir("$webDir/courses/theme_data/temp");
        }
    }    
}
function setGlobalContactInfo() {
    global $Institution, $postaddress, $telephone, $fax;

    if (!isset($Institution)) {
        $Institution = get_config('institution');
    }
    if (!isset($postaddress)) {
        $postaddress = get_config('postaddress');
    }
    if (!isset($telephone)) {
        $telephone = get_config('phone');
    }
    if (!isset($fax)) {
        $fax = get_config('fax');
    }
}

function refreshHierarchyProcedures() {
    Database::get()->query("DROP VIEW IF EXISTS `hierarchy_depth`");

    Database::get()->query("DROP PROCEDURE IF EXISTS `add_node`");
    Database::get()->query("CREATE PROCEDURE `add_node` (IN name TEXT, IN parentlft INT(11),
                                IN p_code VARCHAR(20), IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN,
                                IN p_order_priority INT(11))
                            LANGUAGE SQL
                            BEGIN
                                DECLARE lft, rgt INT(11);

                                SET lft = parentlft + 1;
                                SET rgt = parentlft + 2;

                                CALL shift_right(parentlft, 2, 0);

                                INSERT INTO `hierarchy` (name, lft, rgt, code, allow_course, allow_user, order_priority) VALUES (name, lft, rgt, p_code, p_allow_course, p_allow_user, p_order_priority);
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `add_node_ext`");
    Database::get()->query("CREATE PROCEDURE `add_node_ext` (IN name TEXT, IN parentlft INT(11),
                                IN p_code VARCHAR(20), IN p_number INT(11), IN p_generator INT(11),
                                IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN, IN p_order_priority INT(11))
                            LANGUAGE SQL
                            BEGIN
                                DECLARE lft, rgt INT(11);

                                SET lft = parentlft + 1;
                                SET rgt = parentlft + 2;

                                CALL shift_right(parentlft, 2, 0);

                                INSERT INTO `hierarchy` (name, lft, rgt, code, number, generator, allow_course, allow_user, order_priority) VALUES (name, lft, rgt, p_code, p_number, p_generator, p_allow_course, p_allow_user, p_order_priority);
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `update_node`");
    Database::get()->query("CREATE PROCEDURE `update_node` (IN p_id INT(11), IN p_name TEXT,
                                IN nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11), IN parentlft INT(11),
                                IN p_code VARCHAR(20), IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN, IN p_order_priority INT(11))
                            LANGUAGE SQL
                            BEGIN
                                UPDATE `hierarchy` SET name = p_name, lft = p_lft, rgt = p_rgt,
                                    code = p_code, allow_course = p_allow_course, allow_user = p_allow_user,
                                    order_priority = p_order_priority WHERE id = p_id;

                                IF nodelft <> parentlft THEN
                                    CALL move_nodes(nodelft, p_lft, p_rgt);
                                END IF;
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `delete_node`");
    Database::get()->query("CREATE PROCEDURE `delete_node` (IN p_id INT(11))
                            LANGUAGE SQL
                            BEGIN
                                DECLARE p_lft, p_rgt INT(11);

                                SELECT lft, rgt INTO p_lft, p_rgt FROM `hierarchy` WHERE id = p_id;
                                DELETE FROM `hierarchy` WHERE id = p_id;

                                CALL delete_nodes(p_lft, p_rgt);
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `shift_right`");
    Database::get()->query("CREATE PROCEDURE `shift_right` (IN node INT(11), IN shift INT(11), IN maxrgt INT(11))
                            LANGUAGE SQL
                            BEGIN
                                IF maxrgt > 0 THEN
                                    UPDATE `hierarchy` SET rgt = rgt + shift WHERE rgt > node AND rgt <= maxrgt;
                                ELSE
                                    UPDATE `hierarchy` SET rgt = rgt + shift WHERE rgt > node;
                                END IF;

                                IF maxrgt > 0 THEN
                                    UPDATE `hierarchy` SET lft = lft + shift WHERE lft > node AND lft <= maxrgt;
                                ELSE
                                    UPDATE `hierarchy` SET lft = lft + shift WHERE lft > node;
                                END IF;
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `shift_left`");
    Database::get()->query("CREATE PROCEDURE `shift_left` (IN node INT(11), IN shift INT(11), IN maxrgt INT(11))
                            LANGUAGE SQL
                            BEGIN
                                IF maxrgt > 0 THEN
                                    UPDATE `hierarchy` SET rgt = rgt - shift WHERE rgt > node AND rgt <= maxrgt;
                                ELSE
                                    UPDATE `hierarchy` SET rgt = rgt - shift WHERE rgt > node;
                                END IF;

                                IF maxrgt > 0 THEN
                                    UPDATE `hierarchy` SET lft = lft - shift WHERE lft > node AND lft <= maxrgt;
                                ELSE
                                    UPDATE `hierarchy` SET lft = lft - shift WHERE lft > node;
                                END IF;
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `shift_end`");
    Database::get()->query("CREATE PROCEDURE `shift_end` (IN p_lft INT(11), IN p_rgt INT(11), IN maxrgt INT(11))
                            LANGUAGE SQL
                            BEGIN
                                UPDATE `hierarchy`
                                SET lft = (lft - (p_lft - 1)) + maxrgt,
                                    rgt = (rgt - (p_lft - 1)) + maxrgt WHERE lft BETWEEN p_lft AND p_rgt;
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `get_maxrgt`");
    Database::get()->query("CREATE PROCEDURE `get_maxrgt` (OUT maxrgt INT(11))
                            LANGUAGE SQL
                            BEGIN
                                SELECT rgt INTO maxrgt FROM `hierarchy` ORDER BY rgt DESC LIMIT 1;
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `get_parent`");
    Database::get()->query("CREATE PROCEDURE `get_parent` (IN p_lft INT(11), IN p_rgt INT(11))
                            LANGUAGE SQL
                            BEGIN
                                SELECT * FROM `hierarchy` WHERE lft < p_lft AND rgt > p_rgt ORDER BY lft DESC LIMIT 1;
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `delete_nodes`");
    Database::get()->query("CREATE PROCEDURE `delete_nodes` (IN p_lft INT(11), IN p_rgt INT(11))
                            LANGUAGE SQL
                            BEGIN
                                DECLARE node_width INT(11);
                                SET node_width = p_rgt - p_lft + 1;

                                DELETE FROM `hierarchy` WHERE lft BETWEEN p_lft AND p_rgt;
                                UPDATE `hierarchy` SET rgt = rgt - node_width WHERE rgt > p_rgt;
                                UPDATE `hierarchy` SET lft = lft - node_width WHERE lft > p_lft;
                            END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `move_nodes`");
    Database::get()->query("CREATE PROCEDURE `move_nodes` (INOUT nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11))
                            LANGUAGE SQL
                            BEGIN
                                DECLARE node_width, maxrgt INT(11);

                                SET node_width = p_rgt - p_lft + 1;
                                CALL get_maxrgt(maxrgt);

                                CALL shift_end(p_lft, p_rgt, maxrgt);

                                IF nodelft = 0 THEN
                                    CALL shift_left(p_rgt, node_width, 0);
                                ELSE
                                    CALL shift_left(p_rgt, node_width, maxrgt);

                                    IF p_lft < nodelft THEN
                                        SET nodelft = nodelft - node_width;
                                    END IF;

                                    CALL shift_right(nodelft, node_width, maxrgt);

                                    UPDATE `hierarchy` SET rgt = (rgt - maxrgt) + nodelft WHERE rgt > maxrgt;
                                    UPDATE `hierarchy` SET lft = (lft - maxrgt) + nodelft WHERE lft > maxrgt;
                                END IF;
                            END");
}

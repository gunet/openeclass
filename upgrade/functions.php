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

/*
 * @brief helper functions for upgrade
 */


/**
 * @brief function to update a field in a table
 * @param $table
 * @param $field
 * @param $field_name
 * @param $id_col
 * @param $id
 */
function update_field($table, $field, $field_name, $id_col, $id) {
    $id = quote($id);
    $sql = "UPDATE `$table` SET `$field` = '$field_name' WHERE `$id_col` = $id;";
    Database::get()->query($sql);
}

/**
 * @brief add field $field to table $table of current database, if it doesn't already exist
 * @param $table
 * @param $field
 * @param $type
 */
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


/**
 * @brief Check MySQL for InnoDB storage engine support
 * @return bool
 */
function check_engine() {
    foreach (Database::get()->queryArray('SHOW ENGINES') as $item) {
        if ($item->Engine == 'InnoDB') {
            return $item->Support == 'YES' or $item->Support == 'DEFAULT';
        }
    }
    return false;
}

/**
 * @brief check if user is admin
 * @param $username
 * @param $password
 * @return bool
 */
function is_admin($username, $password) {

    global $session;

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
        if (isset($user->privilege) and $user->privilege !== '0') {
            return false;
        }

        if (!password_verify($password, $user->password)) {
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



function mkdir_or_error($dirname) {
    global $langErrorCreatingDirectory, $command_line;

    if (!(is_dir($dirname) or make_dir($dirname))) {
        if ($command_line) {
            echo "$langErrorCreatingDirectory $dirname\n";
        } else {
            echo "<div class='alert alert-danger'>$langErrorCreatingDirectory $dirname</div>";
        }
    }
}

function touch_or_error($filename) {
    global $langErrorCreatingDirectory, $command_line;

    if (!(file_exists($filename) or @touch($filename))) {
        if ($command_line) {
            echo "$langErrorCreatingDirectory $filename\n";
        } else {
            echo "<div class='alert alert-danger'>$langErrorCreatingDirectory $filename</div>";
        }
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

// Quote string for output in config.php file
function quote($s) {
    return "'" . addslashes(canonicalize_whitespace($s)) . "'";
}


/**
 * @brief import themes
 */
function importThemes($themes = null) {
    global $webDir;

    $themesDir = "$webDir/template/$_SESSION[theme]/themes";
    if (!is_dir("$webDir/courses/theme_data")) make_dir("$webDir/courses/theme_data");
    if (is_dir($themesDir) && $handle = opendir($themesDir)) {
        while (false !== ($file_name = readdir($handle))) {
            if ($file_name != '.' && $file_name != '..') {
                installTheme($themesDir, $file_name);
            }
        }
        closedir($handle);
    }
}

/**
 * @brief install new theme (only if a theme with this name doesn't already exist)
 * @param $themesDir
 * @param $file_name
 */
function installTheme($themesDir, $file_name) {
    global $webDir, $logfile_path;

    $tempdir = "$webDir/courses/theme_data/temp";
    $archive = new ZipArchive;
    if (!$archive->open("$themesDir/$file_name") or !$archive->extractTo($tempdir)) {
        Debug::message("Error extracting theme $file_name: " . $archive->getStatusString(), Debug::CRITICAL);
    } else {
        $base64_str = file_get_contents("$tempdir/theme_options.txt");
        unlink("$tempdir/theme_options.txt");
        $theme_options = unserialize(base64_decode($base64_str));
        $exists = Database::get()->querySingle('SELECT id FROM theme_options
            WHERE name = ?s', $theme_options->name);
        if (!$exists) {
            $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles) VALUES (?s, ?s)",
                $theme_options->name, $theme_options->styles)->lastInsertID;
            rename($tempdir . '/' . $theme_options->id, "$webDir/courses/theme_data/$new_theme_id");
        }
    }
    removeDir($tempdir);
}


/**
 * @brief install ready to use certificate templates
 */
function installCertTemplates($root_dir) {
    $cert_default_dir = $root_dir . "/template/default/img/game";
    foreach (glob("$cert_default_dir/*.zip") as $zipfile) {
        $archive = new ZipArchive;
        if (!$archive->open($zipfile) or !$archive->extractTo($root_dir . CERT_TEMPLATE_PATH)) {
          die('Error: ' . $archive->getStatusString());
        }
    }
    Database::get()->query("INSERT INTO certificate_template
        (name, description, filename, orientation) VALUES
        ('Πρότυπο 1', '', 'certificate1.html', 'L'),
        ('Πρότυπο 2', '', 'certificate2.html', 'L'),
        ('Πρότυπο 3', '', 'certificate3.html', 'P'),
        ('Πρότυπο 4', '', 'certificate4.html', 'L'),
        ('Πρότυπο 5', '', 'certificate5.html', 'L')");
}

/**
 * install ready to use badge icons
 */
function installBadgeIcons($root_dir) {
    $cert_default_dir = $root_dir . "/template/default/img/game";
    foreach (glob("$cert_default_dir/*.png") as $icon) {
        $iconname = preg_replace('|.*/(.*)\.png|', '$1', $icon);
        $filename = $iconname . '.png';
        if (!copy($icon, $root_dir . BADGE_TEMPLATE_PATH . $filename)) {
            die("Error copying badge icon!");
        }
        Database::get()->query("INSERT INTO badge_icon
            (name, description, filename) VALUES (?s, '', ?s)",
            $iconname, $filename);
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


function updateAnnouncementAdminSticky( $table ) {
    $arr_date = Database::get()->queryArray("SELECT id FROM $table ORDER BY `date` ASC");
    $arr_order_objects = Database::get()->queryArray("SELECT id FROM $table ORDER BY `order` ASC");
    $arr_order = [];
    foreach ($arr_order_objects as $key=>$value) {
        $arr_order[$key] = $value->id;
    }

    $length = count($arr_order);

    $offset = 0;
    for ($i = 0; $i < $length; $i++) {
        if ($arr_date[$i]->id != $arr_order[$i-$offset]) {
            $offset++;
        }
    }

    $zero = $length - $offset;
    $arr_sticky = array_slice($arr_order, -$offset);
    $arr_default = array_slice($arr_order, 0, $zero);

    $default_placeholders = implode(',', array_fill(0, count($arr_default), '?d'));
    if (!empty($default_placeholders)) {
        Database::get()->query("UPDATE $table SET `order` = 0 WHERE `id` IN ($default_placeholders)", $arr_default);
    }

    $ordering = 0;
    foreach ($arr_sticky as $announcement_id) {
        $ordering++;
        Database::get()->query("UPDATE $table SET `order` = ?d where `id`= ?d", $ordering, $announcement_id);
    }
}

function updateAnnouncementSticky( $table ) {
    $courses = Database::get()->queryArray("SELECT course_id FROM $table GROUP BY course_id");
    foreach ($courses as $course) {
        $arr_date = Database::get()->queryArray("SELECT id FROM $table WHERE course_id = $course->course_id ORDER BY `date` ASC");
        $arr_order_objects = Database::get()->queryArray("SELECT id FROM $table WHERE course_id = $course->course_id ORDER BY `order` ASC");
        $arr_order = [];
        foreach ($arr_order_objects as $key => $value) {
            $arr_order[$key] = $value->id;
        }

        $length = count($arr_order);

        $offset = 0;
        for ($i = 0; $i < $length; $i++) {
            if ($arr_date[$i]->id != $arr_order[$i - $offset]) {
                $offset++;
            }
        }

        $zero = $length - $offset;
        $arr_sticky = array_slice($arr_order, -$offset);
        $arr_default = array_slice($arr_order, 0, $zero);

        $default_placeholders = implode(',', array_fill(0, count($arr_default), '?d'));
        if (!empty($default_placeholders)) {
            Database::get()->query("UPDATE $table SET `order` = 0 WHERE `id` IN ($default_placeholders)", $arr_default);
        }

        $ordering = 0;
        foreach ($arr_sticky as $announcement_id) {
            $ordering++;
            Database::get()->query("UPDATE $table SET `order` = ?d WHERE `id`= ?d", $ordering, $announcement_id);
        }
    }
}

/**
 * @brief Create Indexes
 */

function create_indexes() {

    DBHelper::indexExists('actions_daily', 'actions_daily_index') or
        Database::get()->query("CREATE INDEX `actions_daily_index` ON actions_daily(user_id, module_id, course_id)");
    DBHelper::indexExists('actions_summary', 'actions_summary_index') or
        Database::get()->query("CREATE INDEX `actions_summary_index` ON actions_summary(module_id, course_id)");
    DBHelper::indexExists('admin', 'admin_index') or
        Database::get()->query("CREATE INDEX `admin_index` ON admin(user_id)");
    DBHelper::indexExists('agenda', 'agenda_index') or
        Database::get()->query("CREATE INDEX `agenda_index` ON agenda(course_id)");
    DBHelper::indexExists('announcement', 'ann_index') or
        Database::get()->query("CREATE INDEX `ann_index` ON announcement(course_id)");
    DBHelper::indexExists('assignment', 'assignment_index') or
        Database::get()->query("CREATE INDEX `assignment_index` ON assignment(course_id)");
    DBHelper::indexExists('assignment_submit', 'assign_submit_index') or
        Database::get()->query("CREATE INDEX `assign_submit_index` ON assignment_submit(uid, assignment_id)");
    DBHelper::indexExists('assignment_to_specific', 'assign_spec_index') or
        Database::get()->query("CREATE INDEX `assign_spec_index` ON assignment_to_specific(user_id)");
    DBHelper::indexExists('attendance', 'att_index') or
        Database::get()->query("CREATE INDEX `att_index` ON attendance(course_id)");
    DBHelper::indexExists('attendance_activities', 'att_act_index') or
        Database::get()->query("CREATE INDEX `att_act_index` ON attendance_activities(attendance_id)");
    DBHelper::indexExists('attendance_book', 'att_book_index') or
        Database::get()->query("CREATE INDEX `att_book_index` ON attendance_book(attendance_activity_id)");
    DBHelper::indexExists('course', 'course_index') or
        Database::get()->query("CREATE INDEX `course_index` ON course(code)");
    DBHelper::indexExists('course_description', 'cd_type_index') or
        Database::get()->query('CREATE INDEX `cd_type_index` ON course_description(`type`)');
    DBHelper::indexExists('course_description', 'cd_cid_type_index') or
        Database::get()->query('CREATE INDEX `cd_cid_type_index` ON course_description (course_id, `type`)');
    DBHelper::indexExists('course_description', 'cid') or
        Database::get()->query('CREATE INDEX `cid` ON course_description (course_id)');
    DBHelper::indexExists('course_module', 'visible_cid') or
        Database::get()->query('CREATE INDEX `visible_cid` ON course_module (visible, course_id)');
    DBHelper::indexExists('course_review', 'crev_index') or
        Database::get()->query("CREATE INDEX `crev_index` ON course_review(course_id)");
    DBHelper::indexExists('course_units', 'course_units_index') or
        Database::get()->query('CREATE INDEX `course_units_index` ON course_units (course_id, `order`)');
    DBHelper::indexExists('course_user', 'cu_index') or
        Database::get()->query("CREATE INDEX `cu_index` ON course_user (course_id, user_id, status)");
    DBHelper::indexExists('document', 'doc_path_index') or
        Database::get()->query('CREATE INDEX `doc_path_index` ON document (course_id, subsystem,path)');
    DBHelper::indexExists('dropbox_attachment', 'drop_att_index') or
        Database::get()->query("CREATE INDEX `drop_att_index` ON dropbox_attachment(msg_id)");
    DBHelper::indexExists('dropbox_index', 'drop_index') or
        Database::get()->query("CREATE INDEX `drop_index` ON dropbox_index(recipient_id, is_read)");
    DBHelper::indexExists('dropbox_msg', 'drop_msg_index') or
        Database::get()->query("CREATE INDEX `drop_msg_index` ON dropbox_msg(course_id, author_id)");
    DBHelper::indexExists('ebook', 'ebook_index') or
        Database::get()->query("CREATE INDEX `ebook_index` ON ebook(course_id)");
    DBHelper::indexExists('ebook_section', 'ebook_sec_index') or
        Database::get()->query("CREATE INDEX `ebook_sec_index` ON ebook_section(ebook_id)");
    DBHelper::indexExists('ebook_subsection', 'ebook_sub_sec_index') or
        Database::get()->query("CREATE INDEX `ebook_sub_sec_index` ON ebook_subsection(section_id)");
    DBHelper::indexExists('exercise', 'exer_index') or
        Database::get()->query('CREATE INDEX `exer_index` ON exercise (course_id)');
    DBHelper::indexExists('exercise_user_record', 'eur_index1') or
        Database::get()->query('CREATE INDEX `eur_index1` ON exercise_user_record (eid)');
    DBHelper::indexExists('exercise_user_record', 'eur_index2') or
        Database::get()->query('CREATE INDEX `eur_index2` ON exercise_user_record (uid)');
    DBHelper::indexExists('exercise_answer_record', 'ear_index1') or
        Database::get()->query('CREATE INDEX `ear_index1` ON exercise_answer_record (eurid)');
    DBHelper::indexExists('exercise_answer_record', 'ear_index2') or
        Database::get()->query('CREATE INDEX `ear_index2` ON exercise_answer_record (question_id)');
    DBHelper::indexExists('exercise_question', 'eq_index') or
        Database::get()->query('CREATE INDEX `eq_index` ON exercise_question (course_id)');
    DBHelper::indexExists('exercise_answer', 'ea_index') or
        Database::get()->query('CREATE INDEX `ea_index` ON exercise_answer (question_id)');
    DBHelper::indexExists('forum', 'for_index') or
        Database::get()->query("CREATE INDEX `for_index` ON forum(course_id)");
    DBHelper::indexExists('forum_category', 'for_cat_index') or
        Database::get()->query("CREATE INDEX `for_cat_index` ON forum_category(course_id)");
    DBHelper::indexExists('forum_notify', 'for_not_index') or
        Database::get()->query("CREATE INDEX `for_not_index` ON forum_notify(course_id)");
    DBHelper::indexExists('forum_post', 'for_post_index') or
        Database::get()->query("CREATE INDEX `for_post_index` ON forum_post(topic_id)");
    DBHelper::indexExists('forum_topic', 'for_topic_index') or
        Database::get()->query("CREATE INDEX `for_topic_index` ON forum_topic(forum_id)");
    DBHelper::indexExists('glossary', 'glos_index') or
        Database::get()->query("CREATE INDEX `glos_index` ON glossary(course_id)");
    DBHelper::indexExists('glossary_category', 'glos_cat_index') or
        Database::get()->query("CREATE INDEX `glos_cat_index` ON glossary_category(course_id)");
    DBHelper::indexExists('gradebook', 'grade_index') or
        Database::get()->query("CREATE INDEX `grade_index` ON gradebook(course_id)");
    DBHelper::indexExists('gradebook_activities', 'grade_act_index') or
        Database::get()->query("CREATE INDEX `grade_act_index` ON gradebook_activities(gradebook_id)");
    DBHelper::indexExists('gradebook_book', 'grade_book_index') or
        Database::get()->query("CREATE INDEX `grade_book_index` ON gradebook_book(gradebook_activity_id)");
    DBHelper::indexExists('group', 'group_index') or
        Database::get()->query("CREATE INDEX `group_index` ON `group`(course_id)");
    DBHelper::indexExists('group_properties', 'gr_prop_index') or
        Database::get()->query("CREATE INDEX `gr_prop_index` ON group_properties(course_id)");
    DBHelper::indexExists('hierarchy', 'hier_index') or
        Database::get()->query("CREATE INDEX `hier_index` ON hierarchy(code,name(20))");
    DBHelper::indexExists('link', 'link_index') or
        Database::get()->query("CREATE INDEX `link_index` ON link(course_id)");
    DBHelper::indexExists('link_category', 'link_cat_index') or
        Database::get()->query("CREATE INDEX `link_cat_index` ON link_category(course_id)");
    DBHelper::indexExists('log', 'cmid') or
        Database::get()->query('CREATE INDEX `cmid` ON log (course_id, module_id)');
    DBHelper::indexExists('logins', 'logins_id') or
        Database::get()->query("CREATE INDEX `logins_id` ON logins(user_id, course_id)");
    DBHelper::indexExists('loginout', 'loginout_id') or
        Database::get()->query("CREATE INDEX `loginout_id` ON loginout(id_user)");
    DBHelper::indexExists('lp_asset', 'lp_as_id') or
        Database::get()->query("CREATE INDEX `lp_as_id` ON lp_asset(module_id)");
    DBHelper::indexExists('lp_learnPath', 'lp_id') or
        Database::get()->query("CREATE INDEX `lp_id` ON lp_learnPath(course_id)");
    DBHelper::indexExists('lp_module', 'lp_mod_id') or
        Database::get()->query("CREATE INDEX `lp_mod_id` ON lp_module(course_id)");
    DBHelper::indexExists('lp_rel_learnPath_module', 'lp_rel_lp_id') or
        Database::get()->query("CREATE INDEX `lp_rel_lp_id` ON lp_rel_learnPath_module(learnPath_id, module_id)");
    DBHelper::indexExists('lp_user_module_progress', 'optimize') or
        Database::get()->query("CREATE INDEX `optimize` ON lp_user_module_progress (user_id, learnPath_module_id)");
    DBHelper::indexExists('poll', 'poll_index') or
        Database::get()->query("CREATE INDEX `poll_index` ON poll(course_id)");
    DBHelper::indexExists('poll_question', 'poll_q_id') or
        Database::get()->query("CREATE INDEX `poll_q_id` ON poll_question(pid)");
    DBHelper::indexExists('poll_question_answer', 'poll_qa_id') or
        Database::get()->query("CREATE INDEX `poll_qa_id` ON poll_question_answer(pqid)");
    DBHelper::indexExists('unit_resources', 'unit_res_index') or
        Database::get()->query('CREATE INDEX `unit_res_index` ON unit_resources (unit_id, visibility,res_id)');
    DBHelper::indexExists('user', 'u_id') or
        Database::get()->query("CREATE INDEX `u_id` ON user(username)");
    DBHelper::indexExists('video', 'cid') or
        Database::get()->query('CREATE INDEX `cid` ON video (course_id)');
    DBHelper::indexExists('videolink', 'cid') or
        Database::get()->query('CREATE INDEX `cid` ON videolink (course_id)');
    DBHelper::indexExists('wiki_locks', 'wiki_id') or
        Database::get()->query("CREATE INDEX `wiki_id` ON wiki_locks(wiki_id)");
    DBHelper::indexExists('wiki_pages', 'wiki_pages_id') or
        Database::get()->query("CREATE INDEX `wiki_pages_id` ON wiki_pages(wiki_id)");
    DBHelper::indexExists('wiki_pages_content', 'wiki_pcon_id') or
        Database::get()->query("CREATE INDEX `wiki_pcon_id` ON wiki_pages_content(pid)");
    DBHelper::indexExists('wiki_properties', 'wik_prop_id') or
        Database::get()->query("CREATE INDEX `wik_prop_id` ON  wiki_properties(course_id)");
    DBHelper::indexExists('idx_queue', 'idx_queue_cid') or
        Database::get()->query("CREATE INDEX `idx_queue_cid` ON `idx_queue` (course_id)");
    DBHelper::indexExists('idx_queue_async', 'idx_queue_async_uid') or
        Database::get()->query("CREATE INDEX `idx_queue_async_uid` ON idx_queue_async(user_id)");
    DBHelper::indexExists('attendance_users', 'attendance_users_aid') or
        Database::get()->query('CREATE INDEX `attendance_users_aid` ON `attendance_users` (attendance_id)');
    DBHelper::indexExists('gradebook_users', 'gradebook_users_gid') or
        Database::get()->query('CREATE INDEX `gradebook_users_gid` ON `gradebook_users` (gradebook_id)');
    DBHelper::indexExists('actions_daily', 'actions_daily_mcd') or
        Database::get()->query('CREATE INDEX `actions_daily_mcd` ON `actions_daily` (module_id, course_id, day)');
    DBHelper::indexExists('actions_daily', 'actions_daily_hdi') or
        Database::get()->query('CREATE INDEX `actions_daily_hdi` ON `actions_daily` (hits, duration, id)');
    DBHelper::indexExists('actions_summary', 'actions_summary_module_id') or
        Database::get()->query("CREATE INDEX `actions_summary_module_id` ON actions_summary(module_id)");
    DBHelper::indexExists('actions_summary', 'actions_summary_course_id') or
        Database::get()->query("CREATE INDEX `actions_summary_course_id` ON actions_summary(course_id)");
    DBHelper::indexExists('loginout', 'loginout_ia') or
        Database::get()->query('CREATE INDEX `loginout_ia` ON `loginout` (id_user, action)');
    DBHelper::indexExists('announcement', 'announcement_cvo') or
        Database::get()->query('CREATE INDEX `announcement_cvo` ON `announcement` (course_id, visible, `order`)');
    DBHelper::indexExists('document', 'doc_course_id') or
        Database::get()->query('CREATE INDEX `doc_course_id` ON document (course_id)');
    DBHelper::indexExists('document', 'doc_subsystem') or
        Database::get()->query('CREATE INDEX `doc_subsystem` ON document (subsystem)');
    DBHelper::indexExists('document', 'doc_path') or
        Database::get()->query('CREATE INDEX `doc_path` ON document (path)');
    DBHelper::indexExists('dropbox_index', 'drop_index_recipient_id') or
        Database::get()->query("CREATE INDEX `drop_index_recipient_id` ON dropbox_index(recipient_id)");
    DBHelper::indexExists('dropbox_index', 'drop_index_recipient_id') or
        Database::get()->query("CREATE INDEX `drop_index_is_read` ON dropbox_index(is_read)");
    DBHelper::indexExists('dropbox_msg', 'drop_msg_index_course_id') or
        Database::get()->query("CREATE INDEX `drop_msg_index_course_id` ON dropbox_msg(course_id)");
    DBHelper::indexExists('dropbox_msg', 'drop_msg_index_author_id') or
        Database::get()->query("CREATE INDEX `drop_msg_index_author_id` ON dropbox_msg(author_id)");
    DBHelper::indexExists('exercise_with_questions', 'ewq_index_question_id') or
        Database::get()->query('CREATE INDEX `ewq_index_question_id` ON exercise_with_questions (question_id)');
    DBHelper::indexExists('exercise_with_questions', 'ewq_index_exercise_id') or
        Database::get()->query('CREATE INDEX `ewq_index_exercise_id` ON exercise_with_questions (exercise_id)');
    DBHelper::indexExists('exercise_to_specific', 'exercise_id') or
        Database::get()->query('CREATE INDEX exercise_id on exercise_to_specific(exercise_id)');
    DBHelper::indexExists('group_members', 'gr_mem_user_id') or
        Database::get()->query("CREATE INDEX `gr_mem_user_id` ON group_members(user_id)");
    DBHelper::indexExists('group_members', 'gr_mem_group_id') or
        Database::get()->query("CREATE INDEX `gr_mem_group_id` ON group_members(group_id)");
    DBHelper::indexExists('log', 'log_course_id') or
        Database::get()->query("CREATE INDEX `log_course_id` ON log (course_id)");
    DBHelper::indexExists('log', 'log_module_id') or
        Database::get()->query("CREATE INDEX `log_module_id` ON log (module_id)");
    DBHelper::indexExists('logins', 'logins_id_user_id') or
        Database::get()->query("CREATE INDEX `logins_id_user_id` ON logins(user_id)");
    DBHelper::indexExists('logins', 'logins_id_course_id') or
        Database::get()->query("CREATE INDEX `logins_id_course_id` ON logins(course_id)");
    DBHelper::indexExists('lp_rel_learnPath_module', 'lp_rel_learnPath_id') or
        Database::get()->query("CREATE INDEX `lp_rel_learnPath_id` ON lp_rel_learnPath_module(learnPath_id)");
    DBHelper::indexExists('lp_rel_learnPath_module', 'lp_rel_learnPath_id') or
        Database::get()->query("CREATE INDEX `lp_rel_module_id` ON lp_rel_learnPath_module(module_id)");
    DBHelper::indexExists('lp_user_module_progress', 'lp_learnPath_module_id') or
        Database::get()->query("CREATE INDEX `lp_learnPath_module_id` ON lp_user_module_progress (learnPath_module_id)");
    DBHelper::indexExists('lp_user_module_progress', 'lp_user_id') or
        Database::get()->query("CREATE INDEX `lp_user_id` ON lp_user_module_progress (user_id)");
    DBHelper::indexExists('unit_resources', 'unit_res_unit_id') or
        Database::get()->query("CREATE INDEX `unit_res_unit_id` ON unit_resources (unit_id)");
    DBHelper::indexExists('unit_resources', 'unit_res_visible') or
        Database::get()->query("CREATE INDEX `unit_res_visible` ON unit_resources (visible)");
    DBHelper::indexExists('unit_resources', 'unit_res_res_id') or
        Database::get()->query("CREATE INDEX `unit_res_res_id` ON unit_resources (res_id)");
    DBHelper::indexExists('personal_calendar', 'pcal_start') or
        Database::get()->query('CREATE INDEX `pcal_start` ON personal_calendar (start)');
    DBHelper::indexExists('agenda', 'agenda_start') or
        Database::get()->query('CREATE INDEX `agenda_start` ON agenda (start)');
    DBHelper::indexExists('agenda', 'source_event_id') or
        Database::get()->query('CREATE INDEX `source_event_id` ON agenda (source_event_id)');
    DBHelper::indexExists('assignment', 'assignment_deadline') or
        Database::get()->query('CREATE INDEX `assignment_deadline` ON assignment (deadline)');
    DBHelper::indexExists('course_settings', 'course_id') or
        Database::get()->query('CREATE INDEX course_id on course_settings (course_id)');
    DBHelper::indexExists('assignment_submit', 'assignment_id') or
        Database::get()->query('CREATE INDEX assignment_id on assignment_submit(assignment_id)');
    DBHelper::indexExists('attendance_users', 'uid_attendance_id') or
        Database::get()->query('CREATE INDEX uid_attendance_id on attendance_users (uid,attendance_id)');
    DBHelper::indexExists('gradebook_users', 'uid_gradebook_id') or
        Database::get()->query('CREATE INDEX uid_gradebook_id on gradebook_users (uid,gradebook_id)');
    DBHelper::indexExists('video_category', 'course_id') or
        Database::get()->query('CREATE INDEX course_id ON video_category(course_id)');
    DBHelper::indexExists('comments', 'rid_rtype') or
        Database::get()->query('CREATE INDEX rid_rtype ON comments (rid,rtype)');
    DBHelper::indexExists('exercise_question_cats', 'course_id') or
        Database::get()->query('CREATE INDEX course_id on exercise_question_cats(course_id)');
    DBHelper::indexExists('blog_post', 'course_id') or
        Database::get()->query('CREATE INDEX course_id on blog_post(course_id)');
    DBHelper::indexExists('note', 'reference_obj_course') or
        Database::get()->query('CREATE INDEX reference_obj_course on note(reference_obj_course)');
    DBHelper::indexExists('assignment_submit', 'assignment_id') or
        Database::get()->query('CREATE INDEX assignment_id on assignment_submit(qid)');
    DBHelper::indexExists('forum_user_stats', 'course_id') or
        Database::get()->query('CREATE INDEX course_id on forum_user_stats(course_id)');
    DBHelper::indexExists('note', 'user_id') or
        Database::get()->query('CREATE INDEX user_id on note(user_id)');
    DBHelper::indexExists('conference', 'course_id') or
        Database::get()->query('CREATE INDEX course_id on conference(course_id)');
    DBHelper::indexExists('lti_apps', 'course_id_enabled') or
        Database::get()->query('CREATE INDEX course_id_enabled on lti_apps(course_id,enabled)');
    DBHelper::indexExists('user', 'am') or
        Database::get()->query('CREATE INDEX am on `user`(am)');
    DBHelper::indexExists('group', 'forum_id') or
        Database::get()->query('CREATE INDEX forum_id on `group`(forum_id)');
    DBHelper::indexExists('assignment_to_specific', 'group_id') or
        Database::get()->query('CREATE INDEX group_id on assignment_to_specific (group_id)');
    DBHelper::indexExists('user', 'email') or
        Database::get()->query('CREATE INDEX email on `user`(email)');
    DBHelper::indexExists('dropbox_index', 'drop_index2') or
        Database::get()->query('CREATE INDEX drop_index2 on dropbox_index(recipient_id,deleted,msg_id)');
    DBHelper::indexExists('personal_calendar', 'user_id_start') or
        Database::get()->query('CREATE INDEX user_id_start on personal_calendar(user_id,start)');
    DBHelper::indexExists('lp_user_module_progress', 'learnPath_id') or
        Database::get()->query('CREATE INDEX learnPath_id on lp_user_module_progress(learnPath_id)');
    DBHelper::indexExists('assignment_to_specific', 'assignment_id') or
        Database::get()->query('CREATE INDEX assignment_id on assignment_to_specific(assignment_id)');
    DBHelper::indexExists('hierarchy', 'lft_rgt') or
        Database::get()->query('CREATE INDEX lft_rgt on hierarchy(lft,rgt)');
    DBHelper::indexExists('video', 'category_visible') or
        Database::get()->query('CREATE INDEX category_visible ON video(category,visible)');
    DBHelper::indexExists('analytics', 'courseID') or
        Database::get()->query('CREATE INDEX courseID on analytics(courseID)');
    DBHelper::indexExists('loginout', 'useractionwhen') or
        Database::get()->query('CREATE INDEX useractionwhen on loginout (id_user,action,`when` desc)');
    DBHelper::indexExists('user', 'index_surname') or
        Database::get()->query('CREATE INDEX index_surname on user(surname)');
    DBHelper::indexExists('wiki_properties', 'group_id') or
        Database::get()->query('CREATE INDEX group_id on wiki_properties(group_id)');
}

/**
 * Create Stored Procedures
 */
function refreshHierarchyProcedures() {
    Database::get()->query("DROP VIEW IF EXISTS `hierarchy_depth`");

    Database::get()->query("DROP PROCEDURE IF EXISTS `add_node`");
    Database::get()->query("CREATE PROCEDURE `add_node` (IN name TEXT CHARSET utf8, IN description TEXT CHARSET utf8, IN parentlft INT(11),
                    IN p_code VARCHAR(20) CHARSET utf8, IN p_allow_course BOOLEAN,
                    IN p_allow_user BOOLEAN, IN p_order_priority INT(11), IN p_visible TINYINT(4))
                LANGUAGE SQL
                BEGIN
                    DECLARE lft, rgt INT(11);

                    SET lft = parentlft + 1;
                    SET rgt = parentlft + 2;

                    CALL shift_right(parentlft, 2, 0);

                    INSERT INTO `hierarchy` (name, description, lft, rgt, code, allow_course, allow_user, order_priority, visible) VALUES (name, description, lft, rgt, p_code, p_allow_course, p_allow_user, p_order_priority, p_visible);
                END");


    Database::get()->query("DROP PROCEDURE IF EXISTS `update_node`");
    Database::get()->query("CREATE PROCEDURE `update_node` (IN p_id INT(11), IN p_name TEXT CHARSET utf8, IN p_description TEXT CHARSET utf8,
                    IN nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11), IN parentlft INT(11),
                    IN p_code VARCHAR(20) CHARSET utf8, IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN,
                    IN p_order_priority INT(11), IN p_visible TINYINT(4))
                LANGUAGE SQL
                BEGIN
                    UPDATE `hierarchy` SET name = p_name, description = p_description, lft = p_lft, rgt = p_rgt,
                        code = p_code, allow_course = p_allow_course, allow_user = p_allow_user,
                        order_priority = p_order_priority, visible = p_visible WHERE id = p_id;

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


/**
 * @brief Create directory indexes to hinder directory traversal in misconfigured servers
 */
function addDirectoryIndexFiles() {
    $dirs = ['courses/archive', 'courses/document', 'courses/garbage', 'courses/mathimg', 'courses/mydocs', 'courses/theme_data', 'courses/tmpUnzipping'];

    foreach ($dirs as $dir) {
        addDirectoryIndexFilesHelper($dir);
    }
    Database::get()->queryFunc('SELECT code FROM course ORDER BY id',
        function ($course) {
            $code = $course->code;
            addDirectoryIndexFilesHelper("courses/$code/document");
            addDirectoryIndexFilesHelper("courses/$code/dropbox");
            addDirectoryIndexFilesHelper("courses/$code/group");
            addDirectoryIndexFilesHelper("courses/$code/image");
            addDirectoryIndexFilesHelper("courses/$code/page");
            addDirectoryIndexFilesHelper("courses/$code/scormPackages");
            addDirectoryIndexFilesHelper("courses/$code/temp");
            addDirectoryIndexFilesHelper("courses/$code/work");
            addDirectoryIndexFilesHelper("courses/$code/work/admin_files");
            addDirectoryIndexFilesHelper("video/$code");
        });
}

function addDirectoryIndexFilesHelper($dir) {
    if (is_dir($dir) and !(file_exists("$dir/index.php") or file_exists("$dir/index.html"))) {
        touch("$dir/index.html");
    }
}

/**
 * @brief change db encoding to utf8mb4
 */
function convert_db_encoding_to_utf8mb4() {

    global $mysqlMainDb;
    // convert database
    Database::core()->query("ALTER DATABASE `$mysqlMainDb` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin;");

    // convert tables
    $r = Database::core()->queryArray("SHOW TABLES FROM `$mysqlMainDb`");
    foreach ($r as $tables) {
        foreach ($tables as $table_name) {
            Database::get()->query("ALTER TABLE `$table_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
        }
    }

    // convert table columns
    Database::get()->query("ALTER TABLE `abuse_report` CHANGE rtype rtype varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `abuse_report` CHANGE reason reason varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `abuse_report` CHANGE message message text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `activity_content` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `activity_heading` CHANGE heading heading text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `admin_announcement` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `admin_announcement` CHANGE body body text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `admin_calendar` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `admin_calendar` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `admin_calendar` CHANGE recursion_period recursion_period varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `agenda` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `agenda` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `agenda` CHANGE duration duration varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `agenda` CHANGE recursion_period recursion_period varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `analytics` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `analytics` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `announcement` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `announcement` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE group_submissions group_submissions char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE assign_to_specific assign_to_specific char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE file_path file_path varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE file_name file_name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE auto_judge_scenarios auto_judge_scenarios text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE ip_lock ip_lock text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE password_lock password_lock varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment` CHANGE tii_exclude_type tii_exclude_type varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_grading_review` CHANGE file_name file_name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_grading_review` CHANGE submission_text submission_text mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_grading_review` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_grading_review` CHANGE rubric_scales rubric_scales text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE submission_ip submission_ip varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE file_name file_name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE submission_text submission_text mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE grade_rubric grade_rubric text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE grade_comments grade_comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE grade_comments_filename grade_comments_filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE grade_submission_ip grade_submission_ip varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `assignment_submit` CHANGE auto_judge_scenarios_output auto_judge_scenarios_output text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `attendance` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `attendance_activities` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `attendance_activities` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `attendance_book` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `auth` CHANGE auth_name auth_name varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `auth` CHANGE auth_settings auth_settings text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `auth` CHANGE auth_instructions auth_instructions text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `auth` CHANGE auth_title auth_title text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge` CHANGE issuer issuer varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge` CHANGE message message text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge_criterion` CHANGE activity_type activity_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge_criterion` CHANGE operator operator varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge_icon` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge_icon` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `badge_icon` CHANGE filename filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `blog_post` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `blog_post` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `category` CHANGE name name text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `category_value` CHANGE name name text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate` CHANGE issuer issuer varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate` CHANGE message message text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate_criterion` CHANGE activity_type activity_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate_criterion` CHANGE operator operator varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate_template` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate_template` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate_template` CHANGE filename filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certificate_template` CHANGE orientation orientation varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certified_users` CHANGE course_title course_title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certified_users` CHANGE cert_title cert_title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certified_users` CHANGE cert_message cert_message text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certified_users` CHANGE cert_issuer cert_issuer varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certified_users` CHANGE user_fullname user_fullname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `certified_users` CHANGE identifier identifier varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `colmooc_user_session` CHANGE session_id session_id text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `colmooc_user_session` CHANGE session_token session_token text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `comments` CHANGE rtype rtype varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `comments` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `conference` CHANGE conf_title conf_title text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `conference` CHANGE conf_description conf_description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `conference` CHANGE user_id user_id varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `conference` CHANGE group_id group_id varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `conference` CHANGE `status` `status` enum('active','inactive') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT 'active'");
    Database::get()->query("ALTER TABLE `config` CHANGE `key` `key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `config` CHANGE `value` `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE code code varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE title title varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE keywords keywords text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE prof_names prof_names varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE public_code public_code varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE password password varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE view_type view_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course` CHANGE course_image course_image varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course_description` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course_description` CHANGE comments comments mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course_description_type` CHANGE title title mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course_description_type` CHANGE icon icon varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course_units` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course_units` CHANGE comments comments mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `course_user_request` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `cron_params` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `custom_profile_fields` CHANGE shortname shortname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `custom_profile_fields` CHANGE name name mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `custom_profile_fields` CHANGE description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `custom_profile_fields` CHANGE datatype datatype varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `custom_profile_fields` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `custom_profile_fields_category` CHANGE name name mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `custom_profile_fields_data` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `custom_profile_fields_data_pending` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `document` CHANGE filename filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `document` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `document` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `document` CHANGE creator creator text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `document` CHANGE subject subject text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `document` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `document` CHANGE author author varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `document` CHANGE format format varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `dropbox_attachment` CHANGE filename filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `dropbox_attachment` CHANGE real_filename real_filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `dropbox_msg` CHANGE subject subject varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `dropbox_msg` CHANGE body body longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `ebook` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `ebook_section` CHANGE public_id public_id varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `ebook_section` CHANGE file file varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `ebook_section` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `ebook_subsection` CHANGE public_id public_id varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `ebook_subsection` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_fields` CHANGE shortname shortname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_fields` CHANGE name name mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_fields` CHANGE description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_fields` CHANGE datatype datatype varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_fields` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_fields_category` CHANGE name name mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_fields_data` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_resource` CHANGE resource_type resource_type varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_resource` CHANGE course_title course_title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `eportfolio_resource` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise` CHANGE ip_lock ip_lock text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise` CHANGE password_lock password_lock varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise_answer` CHANGE answer answer text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise_answer` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise_answer_record` CHANGE answer answer text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise_question` CHANGE question question text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise_question` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise_question_cats` CHANGE question_cat_name question_cat_name varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `exercise_with_questions` CHANGE random_criteria random_criteria text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `faq` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `faq` CHANGE body body text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `forum` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `forum` CHANGE `desc` `desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `forum_category` CHANGE cat_title cat_title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `forum_post` CHANGE post_text post_text mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `forum_post` CHANGE poster_ip poster_ip varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `forum_post` CHANGE topic_filename topic_filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `forum_topic` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `glossary` CHANGE term term varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `glossary` CHANGE definition definition text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `glossary` CHANGE url url text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `glossary` CHANGE notes notes text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `glossary_category` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `glossary_category` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `gradebook` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `gradebook_activities` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `gradebook_activities` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `gradebook_book` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `grading_scale` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `grading_scale` CHANGE scales scales text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `group` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `group` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `group` CHANGE secret_directory secret_directory varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `group_category` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `group_category` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `group_members` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_content` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_content` CHANGE params params longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_content_dependency` CHANGE dependency_type dependency_type varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE machine_name machine_name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE embed_types embed_types varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE preloaded_js preloaded_js longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE preloaded_css preloaded_css longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE droplibrary_css droplibrary_css longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE semantics semantics longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE add_to add_to longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE metadata_settings metadata_settings longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE tutorial tutorial longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library` CHANGE example example longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library_dependency` CHANGE dependency_type dependency_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `h5p_library_translation` CHANGE language_json language_json text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `hierarchy` CHANGE code code varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `hierarchy` CHANGE name name text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `hierarchy` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `idx_queue_async` CHANGE request_type request_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `idx_queue_async` CHANGE resource_type resource_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `link` CHANGE url url text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `link` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `link` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `link_category` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `link_category` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `log` CHANGE details details text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `log_archive` CHANGE details details text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `loginout` CHANGE action action enum('LOGIN','LOGOUT') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL default 'LOGIN'");
    Database::get()->query("ALTER TABLE `lp_asset` CHANGE path path varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_asset` CHANGE comment comment varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_learnPath` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_learnPath` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_learnPath` CHANGE `lock` `lock` enum('OPEN','CLOSE') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'OPEN'");
    Database::get()->query("ALTER TABLE `lp_module` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_module` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_module` CHANGE launch_data launch_data text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_module` CHANGE `accessibility` `accessibility` enum('PRIVATE','PUBLIC') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'PRIVATE'");
    Database::get()->query("ALTER TABLE `lp_module` CHANGE `contentType` `contentType` enum('CLARODOC', 'DOCUMENT', 'EXERCISE', 'HANDMADE',
                                                                                                    'SCORM', 'SCORM_ASSET', 'LABEL', 'COURSE_DESCRIPTION', 'LINK',
                                                                                                    'MEDIA','MEDIALINK') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL");
    Database::get()->query("ALTER TABLE `lp_rel_learnPath_module` CHANGE specificComment specificComment text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_rel_learnPath_module` CHANGE `lock` `lock` enum('OPEN','CLOSE') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'OPEN'");
    Database::get()->query("ALTER TABLE `lp_user_module_progress` CHANGE lesson_location lesson_location varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_user_module_progress` CHANGE total_time total_time varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_user_module_progress` CHANGE session_time session_time varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_user_module_progress` CHANGE suspend_data suspend_data text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lp_user_module_progress` CHANGE `lesson_status` `lesson_status` enum('NOT ATTEMPTED', 'PASSED', 'FAILED', 'COMPLETED',
                                                                                                                    'BROWSED', 'INCOMPLETE', 'UNKNOWN') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'NOT ATTEMPTED'");
    Database::get()->query("ALTER TABLE `lp_user_module_progress` CHANGE `entry` `entry` enum('AB-INITIO', 'RESUME', '') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'AB-INITIO'");
    Database::get()->query("ALTER TABLE `lp_user_module_progress` CHANGE `credit` `credit` enum('CREDIT','NO-CREDIT') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'NO-CREDIT'");
    Database::get()->query("ALTER TABLE `lti_apps` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `lti_apps` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `monthly_summary` CHANGE month month varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `monthly_summary` CHANGE details details mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `note` CHANGE title title varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `note` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `note` CHANGE `reference_obj_type` `reference_obj_type` enum('course','personalevent','user',
                                                                'course_ebook','course_event','course_assignment',
                                                                'course_document','course_link','course_exercise',
                                                                'course_learningpath','course_video','course_videolink') CHARACTER SET ascii COLLATE ascii_general_ci default NULL");
    Database::get()->query("ALTER TABLE `oai_metadata` CHANGE value value text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `personal_calendar` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `personal_calendar` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `personal_calendar` CHANGE recursion_period recursion_period varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `personal_calendar` CHANGE reference_obj_type reference_obj_type ENUM('course', 'personalevent', 'user',
                                                            'course_ebook', 'course_event', 'course_assignment', 'course_document',
                                                            'course_link', 'course_exercise', 'course_learningpath', 'course_video',
                                                            'course_videolink') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL");
    Database::get()->query("ALTER TABLE `personal_calendar_settings` CHANGE personal_color personal_color varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `personal_calendar_settings` CHANGE course_color course_color varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `personal_calendar_settings` CHANGE deadline_color deadline_color varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `personal_calendar_settings` CHANGE admin_color admin_color varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `personal_calendar_settings` CHANGE `view_type` `view_type` enum('day','month','week') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT 'month'");
    Database::get()->query("ALTER TABLE `poll` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `poll` CHANGE description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `poll` CHANGE end_message end_message mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `poll_answer_record` CHANGE answer_text answer_text text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `poll_question` CHANGE question_text question_text text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `poll_question_answer` CHANGE answer_text answer_text text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `poll_user_record` CHANGE email email varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `poll_user_record` CHANGE verification_code verification_code varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `rating` CHANGE rtype rtype varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `rating` CHANGE widget widget varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `rating` CHANGE rating_source rating_source varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `rating_cache` CHANGE rtype rtype varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `rating_cache` CHANGE tag tag varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `recyclebin` CHANGE tablename tablename varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `recyclebin` CHANGE entrydata entrydata varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `rubric` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `rubric` CHANGE scales scales text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `rubric` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tag` CHANGE name name varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_attendance` CHANGE meetingid meetingid varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_attendance` CHANGE bbbuserid bbbuserid varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_log` CHANGE meetingid meetingid varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_log` CHANGE bbbuserid bbbuserid varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_log` CHANGE fullName fullName varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_log` CHANGE type type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_servers` CHANGE webapp webapp varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_servers` CHANGE `enabled` `enabled` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL");
    Database::get()->query("ALTER TABLE `tc_servers` CHANGE `enable_recordings` `enable_recordings` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL");
    Database::get()->query("ALTER TABLE `tc_session` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_session` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_session` CHANGE external_users external_users text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_session` CHANGE participants participants varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_session` CHANGE options options text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `tc_session` CHANGE `record` `record` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT 'false'");
    Database::get()->query("ALTER TABLE `theme_options` CHANGE name name varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `theme_options` CHANGE styles styles longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `unit_resources` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `unit_resources` CHANGE comments comments mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE surname surname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE givenname givenname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE username username varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE email email varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE parent_email parent_email varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE phone phone varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE am am varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user` CHANGE whitelist whitelist text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_ext_uid` CHANGE uid uid varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE givenname givenname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE surname surname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE username username varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE password password varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE email email varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE phone phone varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE am am varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request` CHANGE request_ip request_ip varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `user_request_ext_uid` CHANGE uid uid varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `video` CHANGE url url varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `video` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `video` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `video` CHANGE creator creator varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `video` CHANGE publisher publisher varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `video_category` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `video_category` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `videolink` CHANGE url url varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `videolink` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `videolink` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `videolink` CHANGE creator creator varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `videolink` CHANGE publisher publisher varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wiki_acls` CHANGE `value` `value` ENUM('false','true') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'false'");
    Database::get()->query("ALTER TABLE `wall_post` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wall_post` CHANGE extvideo extvideo varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wall_post_resources` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wiki_locks` CHANGE ptitle ptitle varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wiki_pages` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wiki_pages_content` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wiki_pages_content` CHANGE changelog changelog varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wiki_properties` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    Database::get()->query("ALTER TABLE `wiki_properties` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");

}

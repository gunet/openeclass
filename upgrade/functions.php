<?php

/* ========================================================================
 * Open eClass 3.15
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

use PhpOffice\PhpSpreadsheet\IOFactory;

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
        if (isset($user->privilege) and intval($user->privilege) !== ADMIN_USER) {
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
            echo "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langErrorCreatingDirectory $dirname</span></div>";
        }
    }
}

function touch_or_error($filename) {
    global $langErrorCreatingDirectory, $command_line;

    if (!(file_exists($filename) or @touch($filename))) {
        if ($command_line) {
            echo "$langErrorCreatingDirectory $filename\n";
        } else {
            echo "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langErrorCreatingDirectory $filename</span></div>";
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
        if (file_exists('config/config.php')) {
            if(get_config('show_always_collaboration') and get_config('show_collaboration')){
              include "$webDir/lang/$code/messages_collaboration.inc.php";
            }
        }
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
function importThemes() {
    global $webDir;

    $themesDir = "$webDir/template/modern/themes";
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
    global $webDir;

    $tempdir = "$webDir/courses/theme_data/temp";
    if (file_exists($tempdir)) {
        removeDir($tempdir);
    }
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
            $new_theme_id = Database::get()->query("INSERT INTO theme_options (name, styles, version) VALUES (?s, ?s, 4)",
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
    $cert_default_dir = $root_dir . "/template/modern/img/game";
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
    $cert_default_dir = $root_dir . "/template/modern/img/game";
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

/**
 * @brief upgrade queries to 3.1
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_1($tbl_options): void
{

    if (!DBHelper::fieldExists('course_user', 'document_timestamp')) {
        Database::get()->query("ALTER TABLE `course_user` ADD document_timestamp DATETIME NOT NULL,
                CHANGE `reg_date` `reg_date` DATETIME NOT NULL");
        Database::get()->query("UPDATE `course_user` SET document_timestamp = NOW()");
    }

    if (get_config('course_guest') == '') {
        set_config('course_guest', 'link');
    }

    // fix agenda entries without duration
    Database::get()->query("UPDATE agenda SET duration = '0:00' WHERE duration = ''");
    // Fix wiki last_version id's
    Database::get()->query("UPDATE wiki_pages SET last_version = (SELECT MAX(id) FROM wiki_pages_content WHERE pid = wiki_pages.id)");

    Database::get()->query("CREATE TABLE IF NOT EXISTS module_disable (module_id int(11) NOT NULL PRIMARY KEY)");
    DBHelper::fieldExists('assignment', 'submission_type') or
    Database::get()->query("ALTER TABLE `assignment` ADD `submission_type` TINYINT NOT NULL DEFAULT '0' AFTER `comments`");
    DBHelper::fieldExists('assignment_submit', 'submission_text') or
    Database::get()->query("ALTER TABLE `assignment_submit` ADD `submission_text` MEDIUMTEXT NULL DEFAULT NULL AFTER `file_name`");
    Database::get()->query("UPDATE `assignment` SET `max_grade` = 10 WHERE `max_grade` IS NULL");
    Database::get()->query("ALTER TABLE `assignment` CHANGE `max_grade` `max_grade` FLOAT NOT NULL DEFAULT '10'");
    // default assignment end date value should be null instead of 0000-00-00 00:00:00
    Database::get()->query("ALTER TABLE `assignment` CHANGE `deadline` `deadline` DATETIME NULL DEFAULT NULL");
    Database::get()->query("UPDATE `assignment` SET `deadline` = NULL WHERE `deadline` = '0000-00-00 00:00:00'");
    // improve primary key for table exercise_answer
    Database::get()->query("CREATE TABLE IF NOT EXISTS `tag_element_module` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `course_id` int(11) NOT NULL,
                    `module_id` int(11) NOT NULL,
                    `element_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `date` DATETIME DEFAULT NULL,
                    `tag_id` int(11) NOT NULL)");
    DBHelper::indexExists('tag_element_module', 'tag_element_index') or
    Database::get()->query("CREATE INDEX `tag_element_index` ON `tag_element_module` (course_id, module_id, element_id)");
    // Tag tables upgrade
    if (DBHelper::fieldExists('tags', 'tag')) {
        $tags = Database::get()->queryArray("SELECT * FROM tags");
        $module_ids = array(
            'work'          =>  MODULE_ID_ASSIGN,
            'announcement'  =>  MODULE_ID_ANNOUNCE,
            'exe'           =>  MODULE_ID_EXERCISE
        );
        foreach ($tags as $tag) {
            $first_tag_id = Database::get()->querySingle("SELECT `id` FROM `tags` WHERE `tag` = ?s ORDER BY `id` ASC", $tag->tag)->id;
            Database::get()->query("INSERT INTO `tag_element_module` (`module_id`,`element_id`, `tag_id`)
                                        VALUES (?d, ?d, ?d)", $module_ids[$tag->element_type], $tag->element_id, $first_tag_id);
        }
        // keep one instance of each tag (the one with the lowest id)
        Database::get()->query("DELETE t1 FROM tags t1, tags t2 WHERE t1.id > t2.id AND t1.tag = t2.tag");
        Database::get()->query("ALTER TABLE tags DROP COLUMN `element_type`, "
            . "DROP COLUMN `element_id`, DROP COLUMN `user_id`, DROP COLUMN `date`, DROP COLUMN `course_id`");
        Database::get()->query("ALTER TABLE tags CHANGE `tag` `name` varchar (255)");
        Database::get()->query("ALTER TABLE tags ADD UNIQUE KEY (name)");
        Database::get()->query("RENAME TABLE `tags` TO `tag`");
    }
    Database::get()->query("CREATE TABLE IF NOT EXISTS tag (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            UNIQUE KEY (name)) $tbl_options");

    if (!DBHelper::fieldExists('blog_post', 'commenting')) {
        Database::get()->query("ALTER TABLE `blog_post` ADD `commenting` TINYINT NOT NULL DEFAULT '1' AFTER `views`");
    }
    Database::get()->query("UPDATE unit_resources SET type = 'videolink' WHERE type = 'videolinks'");
}


/**
 * @brief upgrade queries for version 3.2
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_2($tbl_options): void
{

set_config('ext_bigbluebutton_enabled',
    Database::get()->querySingle("SELECT COUNT(*) AS count FROM bbb_servers WHERE enabled='true'")->count > 0? '1': '0');

        Database::get()->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `shortname` VARCHAR(255) NOT NULL,
                                `name` MEDIUMTEXT NOT NULL,
                                `description` MEDIUMTEXT NULL DEFAULT NULL,
                                `datatype` VARCHAR(255) NOT NULL,
                                `categoryid` INT(11) NOT NULL DEFAULT 0,
                                `sortorder`  INT(11) NOT NULL DEFAULT 0,
                                `required` TINYINT NOT NULL DEFAULT 0,
                                `visibility` TINYINT NOT NULL DEFAULT 0,
                                `user_type` TINYINT NOT NULL,
                                `registration` TINYINT NOT NULL DEFAULT 0,
                                `data` TEXT NULL DEFAULT NULL) $tbl_options");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data` (
                                `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                                `field_id` INT(11) NOT NULL,
                                `data` TEXT NOT NULL,
                                PRIMARY KEY (`user_id`, `field_id`)) $tbl_options");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data_pending` (
                                `user_request_id` INT(11) NOT NULL DEFAULT 0,
                                `field_id` INT(11) NOT NULL,
                                `data` TEXT NOT NULL,
                                PRIMARY KEY (`user_request_id`, `field_id`)) $tbl_options");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_category` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `name` MEDIUMTEXT NOT NULL,
                                `sortorder`  INT(11) NOT NULL DEFAULT 0) $tbl_options");


        // Autojudge fields
        if (!DBHelper::fieldExists('assignment', 'auto_judge')) {
            Database::get()->query("ALTER TABLE `assignment`
                ADD `auto_judge` TINYINT(1) NOT NULL DEFAULT 0,
                ADD `auto_judge_scenarios` TEXT,
                ADD `lang` VARCHAR(10) NOT NULL DEFAULT ''");
            Database::get()->query("ALTER TABLE `assignment_submit`
                ADD `auto_judge_scenarios_output` TEXT");
        }

        if (!DBHelper::fieldExists('link', 'user_id')) {
            Database::get()->query("ALTER TABLE `link` ADD `user_id` INT(11) DEFAULT 0 NOT NULL");
        }
        if (!DBHelper::fieldExists('exercise', 'ip_lock')) {
            Database::get()->query("ALTER TABLE `exercise` ADD `ip_lock` TEXT NULL DEFAULT NULL");
        }
        if (!DBHelper::fieldExists('exercise', 'password_lock')) {
            Database::get()->query("ALTER TABLE `exercise` ADD `password_lock` VARCHAR(255) NULL DEFAULT NULL");
        }
        // Recycle object table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `recyclebin` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `tablename` varchar(100) NOT NULL,
            `entryid` int(11) NOT NULL,
            `entrydata` varchar(4000) NOT NULL,
            KEY `entryid` (`entryid`), KEY `tablename` (`tablename`)) $tbl_options");

        // Auto-enroll rules tables
        Database::get()->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `status` TINYINT(4) NOT NULL DEFAULT 0)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule_department` (
            `rule` INT(11) NOT NULL,
            `department` INT(11) NOT NULL,
            PRIMARY KEY (rule, department),
            FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
            FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `autoenroll_course` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `rule` INT(11) NOT NULL DEFAULT 0,
            `course_id` INT(11) NOT NULL,
            FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `autoenroll_department` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `rule` INT(11) NOT NULL DEFAULT 0,
            `department_id` INT(11) NOT NULL,
            FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
            FOREIGN KEY (department_id) REFERENCES hierarchy(id) ON DELETE CASCADE)");

        // Abuse report table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `abuse_report` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `rid` INT(11) NOT NULL,
            `rtype` VARCHAR(50) NOT NULL,
            `course_id` INT(11) NOT NULL,
            `reason` VARCHAR(50) NOT NULL DEFAULT '',
            `message` TEXT NOT NULL,
            `timestamp` INT(11) NOT NULL DEFAULT 0,
            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            INDEX `abuse_report_index_1` (`rid`, `rtype`, `user_id`, `status`),
            INDEX `abuse_report_index_2` (`course_id`, `status`)) $tbl_options");

        // Delete old key 'language' (it has been replaced by 'default_language')
        Database::get()->query("DELETE FROM config WHERE `key` = 'language'");

        // Add grading scales table
        Database::get()->query("CREATE TABLE IF NOT EXISTS `grading_scale` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` varchar(255) NOT NULL,
            `scales` text NOT NULL,
            `course_id` int(11) NOT NULL,
            KEY `course_id` (`course_id`)) $tbl_options");

        // Add grading_scale_id field to assignments
        if (!DBHelper::fieldExists('assignment', 'grading_scale_id')) {
            Database::get()->query("ALTER TABLE `assignment` ADD `grading_scale_id` INT(11) NOT NULL DEFAULT '0' AFTER `max_grade`");
        }

        // Add show results to participants field
        if (!DBHelper::fieldExists('poll', 'show_results')) {
            Database::get()->query("ALTER TABLE `poll` ADD `show_results` TINYINT NOT NULL DEFAULT '0'");
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_to_specific` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` int(11) NULL,
            `group_id` int(11) NULL,
            `poll_id` int(11) NOT NULL ) $tbl_options");

        if (!DBHelper::fieldExists('poll', 'assign_to_specific')) {
            Database::get()->query("ALTER TABLE `poll` ADD `assign_to_specific` TINYINT NOT NULL DEFAULT '0'");
        }
        Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_to_specific` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `user_id` int(11) NULL,
                    `group_id` int(11) NULL,
                    `exercise_id` int(11) NOT NULL ) $tbl_options");
        if (!DBHelper::fieldExists('exercise', 'assign_to_specific')) {
            Database::get()->query("ALTER TABLE `exercise` ADD `assign_to_specific` TINYINT NOT NULL DEFAULT '0'");
        }
        // This is needed for ALTER IGNORE TABLE
        Database::get()->query('SET SESSION old_alter_table = 1');

        // Unique and foreign keys for user_department table
        if (DBHelper::indexExists('user_department', 'udep_id')) {
            Database::get()->query('DROP INDEX `udep_id` ON user_department');
        }

        if (!DBHelper::indexExists('user_department', 'udep_unique')) {
            Database::get()->queryFunc('SELECT user_department.id FROM user
                        RIGHT JOIN user_department ON user.id = user_department.user
                    WHERE user.id IS NULL', function ($item) {
                Recycle::deleteObject('user_department', $item->id, 'id');
            });
            Database::get()->queryFunc('SELECT user_department.id FROM hierarchy
                        RIGHT JOIN user_department ON hierarchy.id = user_department.department
                    WHERE hierarchy.id IS NULL', function ($item) {
                Recycle::deleteObject('user_department', $item->id, 'id');
            });
            Database::get()->query('ALTER TABLE user_department CHANGE `user` `user` INT(11) NOT NULL');
            Database::get()->query('ALTER IGNORE TABLE `user_department`
                ADD UNIQUE KEY `udep_unique` (`user`,`department`),
                ADD FOREIGN KEY (user) REFERENCES user(id) ON DELETE CASCADE,
                ADD FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE');
        }

        // Unique and foreign keys for course_department table
        if (DBHelper::indexExists('course_department', 'cdep_index')) {
            Database::get()->query('DROP INDEX `cdep_index` ON course_department');
        }
        if (!DBHelper::indexExists('course_department', 'cdep_unique')) {
            Database::get()->queryFunc('SELECT course_department.id FROM course
                        RIGHT JOIN course_department ON course.id = course_department.course
                    WHERE course.id IS NULL', function ($item) {
                Recycle::deleteObject('course_department', $item->id, 'id');
            });
            Database::get()->queryFunc('SELECT course_department.id FROM hierarchy
                        RIGHT JOIN course_department ON hierarchy.id = course_department.department
                    WHERE hierarchy.id IS NULL', function ($item) {
                Recycle::deleteObject('course_department', $item->id, 'id');
            });
            Database::get()->query('ALTER IGNORE TABLE `course_department`
                ADD UNIQUE KEY `cdep_unique` (`course`,`department`),
                ADD FOREIGN KEY (course) REFERENCES course(id) ON DELETE CASCADE,
                ADD FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE');
        }

        // External authentication via Hybridauth
        Database::get()->query("INSERT IGNORE INTO `auth`
            (auth_id, auth_name, auth_title, auth_settings, auth_instructions, auth_default)
            VALUES
            (8, 'facebook', '', '', '', 0),
            (9, 'twitter', '', '', '', 0),
            (10, 'google', '', '', '', 0),
            (11, 'live', 'Microsoft Live Account', '', 'does not work locally', 0),
            (12, 'yahoo', '', '', 'does not work locally', 0),
            (13, 'linkedin', '', '', '', 0)");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `user_ext_uid` (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            auth_id INT(2) NOT NULL,
            uid VARCHAR(64) NOT NULL,
            UNIQUE KEY (user_id, auth_id),
            KEY (uid),
            FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE)
            $tbl_options");
        Database::get()->query("CREATE TABLE IF NOT EXISTS `user_request_ext_uid` (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_request_id INT(11) NOT NULL,
            auth_id INT(2) NOT NULL,
            uid VARCHAR(64) NOT NULL,
            UNIQUE KEY (user_request_id, auth_id),
            FOREIGN KEY (`user_request_id`) REFERENCES `user_request` (`id`) ON DELETE CASCADE)
            $tbl_options");

        if (!DBHelper::fieldExists('gradebook', 'start_date')) {
            Database::get()->query("ALTER TABLE `gradebook` ADD `start_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `gradebook` SET `start_date` = " . DBHelper::timeAfter(-6*30*24*60*60) . ""); // 6 months before
            $q = Database::get()->queryArray("SELECT gradebook_book.id, grade,`range` FROM gradebook, gradebook_activities, gradebook_book
                                                    WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id
                                                    AND gradebook_activities.gradebook_id = gradebook.id");
            foreach ($q as $data) {
                Database::get()->query("UPDATE gradebook_book SET grade = $data->grade/$data->range WHERE id = $data->id");
            }
        }
        if (!DBHelper::fieldExists('gradebook', 'end_date')) {
            Database::get()->query("ALTER TABLE `gradebook` ADD `end_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `gradebook` SET `end_date` = " . DBHelper::timeAfter(6*30*24*60*60) . ""); // 6 months after
        }

        if (!DBHelper::fieldExists('attendance', 'start_date')) {
            Database::get()->query("ALTER TABLE `attendance` ADD `start_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `attendance` SET `start_date` = " . DBHelper::timeAfter(-6*30*24*60*60) . ""); // 6 months before
        }
        if (!DBHelper::fieldExists('attendance', 'end_date')) {
            Database::get()->query("ALTER TABLE `attendance` ADD `end_date` DATETIME NOT NULL");
            Database::get()->query("UPDATE `attendance` SET `end_date` = " . DBHelper::timeAfter(6*30*24*60*60) . ""); // 6 months after
        }
        // Cancelled exercises total weighting fix
        $exercises = Database::get()->queryArray("SELECT exercise.id AS id, exercise.course_id AS course_id, exercise_user_record.eurid AS eurid "
            . "FROM exercise_user_record, exercise "
            . "WHERE exercise_user_record.eid = exercise.id "
            . "AND exercise_user_record.total_weighting = 0 "
            . "AND exercise_user_record.attempt_status = 4");
        foreach ($exercises as $exercise) {
            $totalweight = Database::get()->querySingle("SELECT SUM(exercise_question.weight) AS totalweight
                                            FROM exercise_question, exercise_with_questions
                                            WHERE exercise_question.course_id = ?d
                                            AND exercise_question.id = exercise_with_questions.question_id
                                            AND exercise_with_questions.exercise_id = ?d", $exercise->course_id, $exercise->id)->totalweight;
            Database::get()->query("UPDATE exercise_user_record SET total_weighting = ?f WHERE eurid = ?d", $totalweight, $exercise->eurid);
        }

        if (DBHelper::fieldExists('link', 'hits')) { // not needed
            delete_field('link', 'hits');
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `group_category` (
                                `id` INT(6) NOT NULL AUTO_INCREMENT,
                                `course_id` INT(11) NOT NULL,
                                `name` VARCHAR(255) NOT NULL,
                                `description` TEXT,
                                PRIMARY KEY (`id`, `course_id`)) $tbl_options");

        if (!DBHelper::fieldExists('group', 'category_id')) {
            Database::get()->query("ALTER TABLE `group` ADD `category_id` INT(11) NULL");
        }
        //Group Mapping due to group_id addition in group_properties table
        if (!DBHelper::fieldExists('group_properties', 'group_id')) {
            Database::get()->query("ALTER TABLE `group_properties` ADD `group_id` INT(11) NOT NULL DEFAULT 0");
            Database::get()->query("ALTER TABLE `group_properties` DROP PRIMARY KEY");

            $group_info = Database::get()->queryArray("SELECT * FROM group_properties");
            foreach ($group_info as $group) {
                $cid = $group->course_id;
                $self_reg = $group->self_registration;
                $multi_reg = $group->multiple_registration;
                $unreg = $group->allow_unregister;
                $forum = $group->forum;
                $priv_forum = $group->private_forum;
                $documents = $group->documents;
                $wiki = $group->wiki;
                $agenda = $group->agenda;

                Database::get()->query("DELETE FROM group_properties WHERE course_id = ?d", $cid);

                $num = Database::get()->queryArray("SELECT id FROM `group` WHERE course_id = ?d", $cid);

                foreach ($num as $group_num) {
                    $group_id = $group_num->id;
                    Database::get()->query("INSERT INTO `group_properties` (course_id, group_id, self_registration, allow_unregister, forum, private_forum, documents, wiki, agenda)
                                                    VALUES  (?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d)", $cid, $group_id, $self_reg, $unreg, $forum, $priv_forum, $documents, $wiki, $agenda);
                }
                setting_set(SETTING_GROUP_MULTIPLE_REGISTRATION, $multi_reg, $cid);
            }
            Database::get()->query("ALTER TABLE `group_properties` ADD PRIMARY KEY (group_id)");
            delete_field('group_properties', 'multiple_registration');
        }

        Database::get()->query("CREATE TABLE IF NOT EXISTS `course_user_request` (
                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `uid` int(11) NOT NULL,
                        `course_id` int(11) NOT NULL,
                        `comments` text,
                        `status` int(11) NOT NULL,
                        `ts` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
                        PRIMARY KEY (`id`)) $tbl_options");

        Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_user_record` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
            `email` VARCHAR(255) DEFAULT NULL,
            `email_verification` TINYINT(1) DEFAULT NULL,
            `verification_code` VARCHAR(255) DEFAULT NULL) $tbl_options");
        if (!DBHelper::fieldExists('poll_answer_record', 'poll_user_record_id')) {
            Database::get()->query("ALTER TABLE `poll_answer_record` "
                . "ADD `poll_user_record_id` INT(11) NOT NULL AFTER `arid`");

            if ($user_records = Database::get()->queryArray("SELECT DISTINCT `pid`, `user_id` FROM poll_answer_record")) {
                foreach ($user_records as $user_record) {
                    $poll_user_record_id = Database::get()->query("INSERT INTO poll_user_record (pid, uid) VALUES (?d, ?d)", $user_record->pid, $user_record->user_id)->lastInsertID;
                    Database::get()->query("UPDATE poll_answer_record SET poll_user_record_id = ?d WHERE pid = ?d AND user_id = ?d", $poll_user_record_id, $user_record->pid, $user_record->user_id);
                }
            }
            Database::get()->query("ALTER TABLE `poll_answer_record` ADD FOREIGN KEY (`poll_user_record_id`) REFERENCES `poll_user_record` (`id`) ON DELETE CASCADE");
            delete_field('poll_answer_record', 'pid');
            delete_field('poll_answer_record', 'user_id');
        }
        DBHelper::indexExists('poll_user_record', 'poll_user_rec_id') or
        Database::get()->query("CREATE INDEX `poll_user_rec_id` ON poll_user_record(pid, uid)");
        //Removing Course Home Layout 2
        Database::get()->query("UPDATE course SET home_layout = 1 WHERE home_layout = 2");
}


/**
 * @brief upgrade queries to 3.3
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_3($tbl_options): void
{

// Remove '0000-00-00' default dates and fix exercise weight fields
    Database::get()->query('ALTER TABLE `announcement`
            MODIFY `date` DATETIME NOT NULL,
            MODIFY `start_display` DATETIME DEFAULT NULL,
            MODIFY `stop_display` DATETIME DEFAULT NULL');
    Database::get()->query("UPDATE IGNORE announcement SET start_display=null
            WHERE start_display='0000-00-00 00:00:00'");
    Database::get()->query("UPDATE IGNORE announcement SET stop_display=null
            WHERE stop_display='0000-00-00 00:00:00'");
    Database::get()->query('ALTER TABLE `agenda`
            CHANGE `start` `start` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `course`
            MODIFY `created` DATETIME DEFAULT NULL,
            MODIFY `start_date` DATE DEFAULT NULL,
            MODIFY `finish_date` DATE DEFAULT NULL');
    Database::get()->query("UPDATE IGNORE course SET start_date=null
                            WHERE start_date='0000-00-00 00:00:00'");
    Database::get()->query("UPDATE IGNORE course SET finish_date=null
                            WHERE finish_date='0000-00-00 00:00:00'");
    Database::get()->query('ALTER TABLE `course_weekly_view`
            MODIFY `start_week` DATE DEFAULT NULL,
            MODIFY `finish_week` DATE DEFAULT NULL');
    Database::get()->query('ALTER TABLE `course_weekly_view_activities`
            CHANGE `date` `date` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `course_user_request`
            CHANGE `ts` `ts` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `user`
            CHANGE `registered_at` `registered_at` DATETIME NOT NULL,
            CHANGE `expires_at` `expires_at` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `loginout`
            CHANGE `when` `when` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `personal_calendar`
            CHANGE `start` `start` datetime NOT NULL');
    Database::get()->query('ALTER TABLE `admin_calendar`
            CHANGE `start` `start` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `loginout_summary`
            CHANGE `start_date` `start_date` DATETIME NOT NULL,
            CHANGE `end_date` `end_date` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `document`
            CHANGE `date` `date` DATETIME NOT NULL,
            CHANGE `date_modified` `date_modified` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `wiki_pages`
            CHANGE `ctime` `ctime` DATETIME NOT NULL,
            CHANGE `last_mtime` `last_mtime` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `wiki_pages_content`
            CHANGE `mtime` `mtime` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `wiki_locks`
            MODIFY `ltime_created` DATETIME DEFAULT NULL,
            MODIFY `ltime_alive` DATETIME DEFAULT NULL;');
    Database::get()->query('ALTER TABLE `poll`
            CHANGE `creation_date` `creation_date` DATETIME NOT NULL,
            CHANGE `start_date` `start_date` DATETIME DEFAULT NULL,
            CHANGE `end_date` `end_date` DATETIME DEFAULT NULL');
    Database::get()->query('ALTER TABLE `poll_answer_record`
            CHANGE `submit_date` `submit_date` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `assignment`
            CHANGE `submission_date` `submission_date` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `assignment_submit`
            CHANGE `submission_date` `submission_date` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `exercise_user_record`
            CHANGE `record_start_date` `record_start_date` DATETIME NOT NULL,
            CHANGE `total_score` `total_score` FLOAT(11,2) NOT NULL DEFAULT 0,
            CHANGE `total_weighting` `total_weighting` FLOAT(11,2) DEFAULT 0');
    Database::get()->query('ALTER TABLE `exercise_answer_record`
            CHANGE `weight` `weight` FLOAT(11,2) DEFAULT NULL');
    Database::get()->query('ALTER TABLE `unit_resources`
            CHANGE `date` `date` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `actions_summary`
            CHANGE `start_date` `start_date` DATETIME NOT NULL,
            CHANGE `end_date` `end_date` DATETIME NOT NULL');
    Database::get()->query('ALTER TABLE `logins`
            CHANGE `date_time` `date_time` DATETIME NOT NULL');

    // Fix incorrectly-graded fill-in-blanks questions
    Database::get()->queryFunc('SELECT question_id, answer, type
            FROM exercise_question, exercise_answer
            WHERE question_id = exercise_question.id AND
                  type in (?d, ?d) AND answer LIKE ?s',
        function ($item) {
            if (preg_match('#\[/?m[^]]#', $item->answer)) {
                Database::get()->queryFunc('SELECT answer_record_id, answer, answer_id, weight
                        FROM exercise_user_record, exercise_answer_record
                        WHERE exercise_user_record.eurid = exercise_answer_record.eurid AND
                              exercise_answer_record.question_id = ?d AND
                              attempt_status IN (?d, ?d)',
                    function ($a) use ($item) {
                        static $answers, $weights;
                        $qid = $item->question_id;
                        if (!isset($answers[$qid])) {
                            // code from modules/exercise/exercise.class.php lines 865-878
                            list($answer, $answerWeighting) = explode('::', $item->answer);
                            $weights[$qid] = explode(',', $answerWeighting);
                            preg_match_all('#(?<=\[)(?!/?m])[^\]]+#', $answer, $match);
                            if ($item->type == FILL_IN_BLANKS_TOLERANT) {
                                $expected = array_map(function ($m) {
                                    return strtr(mb_strtoupper($m, 'UTF-8'), 'ΆΈΉΊΌΎΏ', 'ΑΕΗΙΟΥΩ');
                                }, $match[0]);
                            } else {
                                $expected = $match[0];
                            }
                            $answers[$qid] = array_map(function ($str) {
                                return preg_split('/\s*\|\s*/', $str);
                            }, $expected);
                        }
                        if ($item->type == FILL_IN_BLANKS_TOLERANT) {
                            $choice = strtr(mb_strtoupper($a->answer, 'UTF-8'), 'ΆΈΉΊΌΎΏ', 'ΑΕΗΙΟΥΩ');
                        } else {
                            $choice = $a->answer;
                        }
                        $aid = $a->answer_id - 1;
                        $weight = in_array($choice, $answers[$qid][$aid]) ? $weights[$qid][$aid] : 0;
                        if ($weight != $a->weight) {
                            Database::get()->query('UPDATE exercise_answer_record
                                    SET weight = ?f WHERE answer_record_id = ?d',
                                $weight, $a->answer_record_id);
                        }
                    }, $item->question_id, ATTEMPT_COMPLETED, ATTEMPT_PAUSED);
            }
        }, FILL_IN_BLANKS, FILL_IN_BLANKS_TOLERANT, '%[m%');

    // Fix duplicate exercise answer records
    Database::get()->queryFunc('SELECT COUNT(*) AS cnt,
                    MIN(answer_record_id) AS min_answer_record_id
                FROM exercise_answer_record
                GROUP BY eurid, question_id, answer, answer_id
                HAVING cnt > 1',
        function ($item) {
            $details = Database::get()->querySingle('SELECT * FROM exercise_answer_record
                    WHERE answer_record_id = ?d', $item->min_answer_record_id);
            if (is_null($details->answer)) {
                Database::get()->query('DELETE FROM exercise_answer_record
                        WHERE eurid = ?d AND question_id = ?d AND answer IS NULL AND
                              answer_id = ?d AND answer_record_id > ?d',
                    $details->eurid, $details->question_id, $details->answer_id,
                    $item->min_answer_record_id);
            } else {
                Database::get()->query('DELETE FROM exercise_answer_record
                        WHERE eurid = ?d AND question_id = ?d AND answer = ?s AND
                              answer_id = ?d AND answer_record_id > ?d',
                    $details->eurid, $details->question_id, $details->answer,
                    $details->answer_id, $item->min_answer_record_id);
            }
        });

    // Fix incorrect exercise answer grade totals
    Database::get()->query('CREATE TEMPORARY TABLE exercise_answer_record_total AS
            SELECT SUM(weight) AS TOTAL, exercise_answer_record.eurid AS eurid
                FROM exercise_user_record, exercise_answer_record
                WHERE exercise_user_record.eurid = exercise_answer_record.eurid AND
                      attempt_status = ?d
                GROUP BY eurid',
        ATTEMPT_COMPLETED);
    Database::get()->query('UPDATE exercise_user_record, exercise_answer_record_total
            SET exercise_user_record.total_score = exercise_answer_record_total.total
            WHERE exercise_user_record.eurid = exercise_answer_record_total.eurid AND
                  exercise_user_record.total_score <> exercise_answer_record_total.total');
    Database::get()->query('DROP TEMPORARY TABLE exercise_answer_record_total');

    // Fix duplicate link orders
    Database::get()->queryFunc('SELECT DISTINCT course_id, category FROM link
            GROUP BY course_id, category, `order` HAVING COUNT(*) > 1',
        function ($item) {
            $order = 0;
            foreach (Database::get()->queryArray('SELECT id FROM link
                    WHERE course_id = ?d AND category = ?d
                    ORDER BY `order`',
                $item->course_id, $item->category) as $link) {
                Database::get()->query('UPDATE link SET `order` = ?d
                            WHERE id = ?d', $order++, $link->id);
            }
        });

    Database::get()->query("UPDATE link SET `url` = '' WHERE `url` IS NULL");
    Database::get()->query("UPDATE link SET `title` = '' WHERE `title` IS NULL");
    Database::get()->query('ALTER TABLE link
            CHANGE `url` `url` TEXT NOT NULL,
            CHANGE `title` `title` TEXT NOT NULL');

    // Fix duplicate poll_question orders
    Database::get()->queryFunc('SELECT `pid`
                FROM `poll_question`
                GROUP BY `pid`, `q_position` HAVING COUNT(`pqid`) > 1',
        function ($item) {
            $poll_questions = Database::get()->queryArray("SELECT * FROM `poll_question` WHERE pid = ?d", $item->pid);
            $order = 1;
            foreach ($poll_questions as $poll_question) {
                Database::get()->query('UPDATE `poll_question` SET `q_position` = ?d
                                                    WHERE pqid = ?d', $order++, $poll_question->pqid);
            }
        });
    if (!DBHelper::fieldExists('poll', 'public')) {
        Database::get()->query("ALTER TABLE `poll` ADD `public` TINYINT(1) NOT NULL DEFAULT 1 AFTER `active`");
        Database::get()->query("UPDATE `poll` SET `public` = 0");
    }

    // If Shibboleth auth is enabled, try reading current settings and
    // regenerate secure index if successful
    if (Database::get()->querySingle('SELECT auth_default FROM auth
                WHERE auth_name = ?s', 'shibboleth')->auth_default) {
        $secureIndexPath = $webDir . '/secure/index.php';
        $shib_vars = get_shibboleth_vars($secureIndexPath);
        if (count($shib_vars) and isset($shib_vars['uname'])) {
            $shib_config = array();
            foreach ($shib_vars as $shib_var => $shib_value) {
                $shib_config['shib_' . $shib_var] = $shib_value;
            }
            update_shibboleth_endpoint($shib_config);
        }
    }
}

/**
 * @brief upgrade queries to 3.4
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_4($tbl_options): void
{
    global $webDir;

    // Conference table
    Database::get()->query("CREATE TABLE IF NOT EXISTS `conference` (
                        `conf_id` int(11) NOT NULL AUTO_INCREMENT,
                        `course_id` int(11) NOT NULL,
                        `conf_title` text NOT NULL,
                        `conf_description` text DEFAULT NULL,
                        `status` enum('active','inactive') DEFAULT 'active',
                        `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `user_id` varchar(255) default '0',
                        `group_id` varchar(255) default '0',
                        PRIMARY KEY (`conf_id`,`course_id`)) $tbl_options");

    // create db entries about old chats
    $query = Database::get()->queryArray("SELECT id, code FROM course");
    foreach ($query as $codes) {
        $c = $codes->code;
        $chatfile = "$webDir/courses/$c/chat.txt";
        if (file_exists($chatfile) and filesize($chatfile) > 0) {
            $q_ins = Database::get()->query("INSERT INTO conference SET
                                                course_id = $codes->id,
                                                conf_title = '$langUntitledChat',
                                                status = 'active'");
            $last_conf_id = $q_ins->lastInsertID;
            $newchatfile = "$webDir/courses/$c/" . $last_conf_id . "_chat.txt";
            rename($chatfile, $newchatfile);
        }
    }

    // upgrade poll table (COLLES and ATTLS support)
    if (!DBHelper::fieldExists('poll', 'type')) {
        Database::get()->query("ALTER TABLE `poll` ADD `type` TINYINT(1) NOT NULL DEFAULT 0");
    }

    // upgrade bbb_session table
    if (DBHelper::tableExists('bbb_session')) {
        if (!DBHelper::fieldExists('bbb_session', 'end_date')) {
            Database::get()->query("ALTER TABLE `bbb_session` ADD `end_date` datetime DEFAULT NULL AFTER `start_date`");
        }
        Database::get()->query("RENAME TABLE bbb_session TO tc_session");
    }

    // upgrade bbb_servers table
    if (DBHelper::tableExists('bbb_servers')) {
        if (!DBHelper::fieldExists('bbb_servers', 'all_courses')) {
            Database::get()->query("ALTER TABLE bbb_servers ADD `type` varchar(255) NOT NULL DEFAULT 'bbb' AFTER id");
            Database::get()->query("ALTER TABLE bbb_servers ADD port varchar(255) DEFAULT NULL AFTER ip");
            Database::get()->query("ALTER TABLE bbb_servers ADD username varchar(255) DEFAULT NULL AFTER server_key");
            Database::get()->query("ALTER TABLE bbb_servers ADD password varchar(255) DEFAULT NULL AFTER username");
            Database::get()->query("ALTER TABLE bbb_servers ADD webapp varchar(255) DEFAULT NULL AFTER api_url");
            Database::get()->query("ALTER TABLE bbb_servers ADD screenshare varchar(255) DEFAULT NULL AFTER weight");
            Database::get()->query("ALTER TABLE bbb_servers ADD all_courses TINYINT(1) NOT NULL DEFAULT 1");
        }
        // rename `bbb_servers` to `tc_servers`
        if (DBHelper::tableExists('bbb_servers')) {
            Database::get()->query("RENAME TABLE bbb_servers TO tc_servers");
        }
    }

    // course external server table
    Database::get()->query("CREATE TABLE IF NOT EXISTS `course_external_server` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `course_id` int(11) NOT NULL,
            `external_server` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY (`external_server`, `course_id`)) $tbl_options");

    // drop trigger
    Database::get()->query("DROP TRIGGER IF EXISTS personal_calendar_settings_init");
    // update announcements
    Database::get()->query("UPDATE announcement SET `order` = 0");
    updateAnnouncementAdminSticky("admin_announcement");

    //Create FAQ table
    Database::get()->query("CREATE TABLE IF NOT EXISTS `faq` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `title` text NOT NULL,
                            `body` text NOT NULL,
                            `order` int(11) NOT NULL,
                            PRIMARY KEY (`id`)) $tbl_options");

    //wall tables
    Database::get()->query("CREATE TABLE IF NOT EXISTS `wall_post` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `course_id` INT(11) NOT NULL,
                            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                            `content` TEXT DEFAULT NULL,
                            `extvideo` VARCHAR(250) DEFAULT '',
                            `timestamp` INT(11) NOT NULL DEFAULT 0,
                            `pinned` TINYINT(1) NOT NULL DEFAULT 0,
                            INDEX `wall_post_index` (`course_id`)) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `wall_post_resources` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `post_id` INT(11) NOT NULL,
                            `title` VARCHAR(255) NOT NULL DEFAULT '',
                            `res_id` INT(11) NOT NULL,
                            `type` VARCHAR(255) NOT NULL DEFAULT '',
                            INDEX `wall_post_resources_index` (`post_id`)) $tbl_options");

}

/**
 * @brief upgrade queries to 3.5
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_5($tbl_options): void
{

    // Fix multiple equal orders for the same unit if needed
    Database::get()->queryFunc('SELECT course_id FROM course_units
            GROUP BY course_id, `order` HAVING COUNT(`order`) > 1',
        function ($course) {
            $i = 0;
            Database::get()->queryFunc('SELECT id
                    FROM course_units WHERE course_id = ?d
                    ORDER BY `order`',
                function ($unit) use (&$i) {
                    $i++;
                    Database::get()->query('UPDATE course_units SET `order` = ?d
                            WHERE id = ?d', $i, $unit->id);
                }, $course->course_id);
        });
    if (!DBHelper::indexExists('course_units', 'course_units_order')) {
        Database::get()->query('ALTER TABLE course_units
                ADD UNIQUE KEY `course_units_order` (`course_id`,`order`)');
    }
    Database::get()->queryFunc('SELECT unit_id FROM unit_resources
            GROUP BY unit_id, `order` HAVING COUNT(`order`) > 1',
        function ($unit) {
            $i = 0;
            Database::get()->queryFunc('SELECT id
                    FROM unit_resources WHERE unit_id = ?d
                    ORDER BY `order`',
                function ($resource) use (&$i) {
                    $i++;
                    Database::get()->query('UPDATE unit_resources SET `order` = ?d
                            WHERE id = ?d', $i, $resource->id);
                }, $unit->unit_id);
        });
    if (!DBHelper::indexExists('unit_resources', 'unit_resources_order')) {
        Database::get()->query('ALTER TABLE unit_resources
                ADD UNIQUE KEY `unit_resources_order` (`unit_id`,`order`)');
    }

    // fix wrong entries in statistics
    Database::get()->query("UPDATE actions_daily SET module_id = " .MODULE_ID_VIDEO . " WHERE module_id = 0");

    // hierarchy extra fields
    if (!DBHelper::fieldExists('hierarchy', 'description')) {
        Database::get()->query("ALTER TABLE hierarchy ADD `description` TEXT AFTER name");
        Database::get()->query("ALTER TABLE hierarchy ADD `visible` tinyint(4) not null default 2 AFTER order_priority");
    }

    // fix invalid agenda durations
    Database::get()->queryFunc("SELECT DISTINCT duration FROM agenda WHERE duration NOT LIKE '%:%'",
        function ($item) {
            $d = $item->duration;
            if (preg_match('/(\d*)[.,:](\d+)/', $d, $matches)) {
                $fixed = sprintf('%02d:%02d', intval($matches[0]), intval($matches[1]));
            } else {
                $val = intval($d);
                if ($val <= 10) {
                    $fixed = sprintf('%02d:00', $val);
                } else {
                    $h = floor($val / 60);
                    $m = $val % 60;
                    $fixed = sprintf('%02d:%02d', $h, $m);
                }
            }
            Database::get()->query('UPDATE agenda
                    SET duration = ?s WHERE duration = ?s', $fixed, $d);
        });

    // FAQ, E-book and learning path unique indexes
    if (!DBHelper::indexExists('faq', 'faq_order')) {
        Database::get()->query('ALTER TABLE faq
                ADD UNIQUE KEY `faq_order` (`order`)');
    }
    if (!DBHelper::indexExists('ebook', 'ebook_order')) {
        Database::get()->query('ALTER TABLE ebook
                ADD UNIQUE KEY `ebook_order` (`course_id`, `order`)');
    }
    if (!DBHelper::indexExists('lp_learnPath', 'learnPath_order')) {
        Database::get()->query('ALTER TABLE lp_learnPath
                ADD UNIQUE KEY `learnPath_order` (`course_id`, `rank`)');
    }
}

/**
 * @brief upgrade queries to 3.6
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_6($tbl_options): void
{

    Database::get()->query("CREATE TABLE IF NOT EXISTS `activity_heading` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `order` INT(11) NOT NULL DEFAULT 0,
            `heading` TEXT NOT NULL,
            `required` BOOL NOT NULL DEFAULT 0) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `activity_content` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `course_id` INT(11) NOT NULL,
            `heading_id` INT(11) NOT NULL DEFAULT 0,
            `content` TEXT NOT NULL,
            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
            FOREIGN KEY (heading_id) REFERENCES activity_heading(id) ON DELETE CASCADE,
            UNIQUE KEY `heading_course` (`course_id`,`heading_id`)) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields_data` (
            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
            `field_id` INT(11) NOT NULL,
            `data` TEXT NOT NULL,
            PRIMARY KEY (`user_id`, `field_id`)) $tbl_options");

    if (!DBHelper::tableExists('eportfolio_fields_category')) {
        Database::get()->query("CREATE TABLE `eportfolio_fields_category` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` MEDIUMTEXT NOT NULL,
            `sortorder`  INT(11) NOT NULL DEFAULT 0) $tbl_options");

        Database::get()->query("INSERT INTO `eportfolio_fields_category` (`id`, `name`, `sortorder`) VALUES
                (1, '$langPersInfo', 0),
                (2, '$langEduEmpl', -1),
                (3, '$langAchievements', -2),
                (4, '$langGoalsSkills', -3),
                (5, '$langContactInfo', -4)");
    }

    if (!DBHelper::tableExists('eportfolio_fields')) {
        Database::get()->query("CREATE TABLE `eportfolio_fields` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `shortname` VARCHAR(255) NOT NULL,
                `name` MEDIUMTEXT NOT NULL,
                `description` MEDIUMTEXT NULL DEFAULT NULL,
                `datatype` VARCHAR(255) NOT NULL,
                `categoryid` INT(11) NOT NULL DEFAULT 0,
                `sortorder`  INT(11) NOT NULL DEFAULT 0,
                `required` TINYINT NOT NULL DEFAULT 0,
                `data` TEXT NULL DEFAULT NULL) $tbl_options");

        Database::get()->query("INSERT INTO `eportfolio_fields` (`id`, `shortname`, `name`, `description`, `datatype`, `categoryid`, `sortorder`, `required`, `data`) VALUES
                (1, 'birth_date', '$langBirthDate', '', '3', 1, 0, 0, ''),
                (2, 'birth_place', '$langBirthPlace', '', '1', 1, -1, 0, ''),
                (3, 'gender', '$langGender', '', '4', 1, -2, 0, 'a:2:{i:0;s:".strlen($langMale).":\"$langMale\";i:1;s:".strlen($langFemale).":\"$langFemale\";}'),
                (4, 'about_me', '$langAboutMe', '$langAboutMeDescr', '2', 1, -3, 0, ''),
                (5, 'personal_website', '$langPersWebsite', '', '5', 1, -4, 0, ''),
                (6, 'education', '$langEducation', '$langEducationDescr', '2', 2, 0, 0, ''),
                (7, 'employment', '$langEmployment', '', '2', 2, -1, 0, ''),
                (8, 'certificates_awards', '$langCertAwards', '', '2', 3, 0, 0, ''),
                (9, 'publications', '$langPublications', '', '2', 3, -1, 0, ''),
                (10, 'personal_goals', '$langPersGoals', '', '2', 4, 0, 0, ''),
                (11, 'academic_goals', '$langAcademicGoals', '', '2', 4, -1, 0, ''),
                (12, 'career_goals', '$langCareerGoals', '', '2', 4, -2, 0, ''),
                (13, 'personal_skills', '$langPersSkills', '', '2', 4, -3, 0, ''),
                (14, 'academic_skills', '$langAcademicSkills', '', '2', 4, -4, 0, ''),
                (15, 'career_skills', '$langCareerSkills', '', '2', 4, -5, 0, ''),
                (16, 'email', '$langEmail', '', '1', 5, 0, 0, ''),
                (17, 'phone_number', '$langPhone', '', '1', 5, -1, 0, ''),
                (18, 'Address', '$langAddress', '', '1', 5, -2, 0, ''),
                (19, 'fb', '$langFBProfile', '', '5', 5, -3, 0, ''),
                (20, 'twitter', '$langTwitterAccount', '', '5', 5, -4, 0, ''),
                (21, 'linkedin', '$langLinkedInProfile', '', '5', 5, -5, 0, '')");
    }

    Database::get()->query("CREATE TABLE IF NOT EXISTS `eportfolio_resource` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
            `resource_id` INT(11) NOT NULL,
            `resource_type` VARCHAR(50) NOT NULL,
            `course_id` INT(11) NOT NULL,
            `course_title` VARCHAR(250) NOT NULL DEFAULT '',
            `time_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data` TEXT NOT NULL,
            INDEX `eportfolio_res_index` (`user_id`,`resource_type`)) $tbl_options");

    Database::get()->query("INSERT IGNORE INTO `config` (`key`, `value`) VALUES ('bio_quota', '4')");

    if (!DBHelper::fieldExists('user', 'eportfolio_enable')) {
        Database::get()->query("ALTER TABLE `user` ADD eportfolio_enable TINYINT(1) NOT NULL DEFAULT 0");
    }
    // upgrade table `assignment_submit`
    if (!DBHelper::fieldExists('assignment_submit', 'grade_comments_filepath')) {
        Database::get()->query("ALTER TABLE assignment_submit ADD grade_comments_filepath VARCHAR(200) NOT NULL DEFAULT ''
                                AFTER grade_comments");
    }
    if (!DBHelper::fieldExists('assignment_submit', 'grade_comments_filename')) {
        Database::get()->query("ALTER TABLE assignment_submit ADD grade_comments_filename VARCHAR(200) NOT NULL DEFAULT ''
                                AFTER grade_comments");
    }
    if (!DBHelper::fieldExists('assignment_submit', 'grade_rubric')) {
        Database::get()->query("ALTER TABLE assignment_submit ADD `grade_rubric` TEXT AFTER grade");
    }
    // upgrade table `assignment`
    if (!DBHelper::fieldExists('assignment', 'notification')) {
        Database::get()->query("ALTER TABLE assignment ADD notification tinyint(4) DEFAULT 0");
    }
    if (!DBHelper::fieldExists('assignment', 'grading_type')) {
        Database::get()->query("ALTER TABLE assignment ADD `grading_type` TINYINT NOT NULL DEFAULT '0' AFTER group_submissions");
    }
    if (!DBHelper::fieldExists('assignment', 'password_lock')) {
        Database::get()->query("ALTER TABLE `assignment` ADD `password_lock` VARCHAR(255) NOT NULL DEFAULT ''");
    }
    if (!DBHelper::fieldExists('assignment', 'ip_lock')) {
        Database::get()->query("ALTER TABLE `assignment` ADD `ip_lock` TEXT");
    }

    // plagiarism tool table
    if (!DBHelper::tableExists('ext_plag_connection')) {
        Database::get()->query("CREATE TABLE `ext_plag_connection` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `type` int(1) unsigned NOT NULL DEFAULT '1',
              `file_id` int(11) NOT NULL,
              `remote_file_id` int(11) DEFAULT NULL,
              `submission_id` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)) $tbl_options");
    }

    // Course Category tables
    Database::get()->query("CREATE TABLE IF NOT EXISTS `category` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` TEXT NOT NULL,
            `ordering` INT(11),
            `multiple` BOOLEAN NOT NULL DEFAULT TRUE,
            `searchable` BOOLEAN NOT NULL DEFAULT TRUE,
            `active` BOOLEAN NOT NULL DEFAULT TRUE
            ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `category_value` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT(11) NOT NULL REFERENCES category(id),
            `name` TEXT NOT NULL,
            `ordering` INT(11),
            `active` BOOLEAN NOT NULL DEFAULT TRUE
            ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `course_category` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `course_id` INT(11) NOT NULL REFERENCES course(id),
            `category_value_id` INT(11) NOT NULL REFERENCES category_value(id)
            ) $tbl_options");

    // Rubric tables
    Database::get()->query("CREATE TABLE IF NOT EXISTS `rubric` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(200) NOT NULL,
            `scales` text NOT NULL,
            `description` text,
            `preview_rubric` tinyint(1) NOT NULL DEFAULT '0',
            `points_to_graded` tinyint(1) NOT NULL DEFAULT '0',
            `course_id` int(11) NOT NULL,
            KEY `course_id` (`course_id`)
            ) $tbl_options");

    // Gamification Tables (aka certificate + badge)
    Database::get()->query("CREATE TABLE IF NOT EXISTS `certificate_template` (
            `id` mediumint(8) not null auto_increment,
            `name` varchar(255) not null,
            `description` text,
            `filename` varchar(255),
            `orientation` varchar(10),
            PRIMARY KEY(`id`)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `badge_icon` (
                `id` mediumint(8) not null auto_increment primary key,
                `name` varchar(255) not null,
                `description` text,
                `filename` varchar(255)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `certificate` (
            `id` int(11) not null auto_increment primary key,
            `course_id` int(11) not null,
            `issuer` varchar(255) not null default '',
            `template` mediumint(8),
            `title` varchar(255) not null,
            `description` text,
            `message` text,
            `autoassign` tinyint(1) not null default 1,
            `active` tinyint(1) not null default 1,
            `created` datetime,
            `expires` datetime,
            `bundle` int(11) not null default 0,
            index `certificate_course` (`course_id`),
            foreign key (`course_id`) references `course` (`id`),
            foreign key (`template`) references `certificate_template`(`id`)
          ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `badge` (
            `id` int(11) not null auto_increment primary key,
            `course_id` int(11) not null,
            `issuer` varchar(255) not null default '',
            `icon` mediumint(8),
            `title` varchar(255) not null,
            `description` text,
            `message` text,
            `autoassign` tinyint(1) not null default 1,
            `active` tinyint(1) not null default 1,
            `created` datetime,
            `expires` datetime,
            `bundle` int(11) not null default 0,
            index `badge_course` (`course_id`),
            foreign key (`course_id`) references `course` (`id`)
          ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `user_certificate` (
          `id` int(11) not null auto_increment primary key,
          `user` int(11) not null,
          `certificate` int(11) not null,
          `completed` boolean default false,
          `completed_criteria` int(11),
          `total_criteria` int(11),
          `updated` datetime,
          `assigned` datetime,
          unique key `user_certificate` (`user`, `certificate`),
          foreign key (`user`) references `user`(`id`),
          foreign key (`certificate`) references `certificate` (`id`)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `user_badge` (
          `id` int(11) not null auto_increment primary key,
          `user` int(11) not null,
          `badge` int(11) not null,
          `completed` boolean default false,
          `completed_criteria` int(11),
          `total_criteria` int(11),
          `updated` datetime,
          `assigned` datetime,
          unique key `user_badge` (`user`, `badge`),
          foreign key (`user`) references `user`(`id`),
          foreign key (`badge`) references `badge` (`id`)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `certificate_criterion` (
          `id` int(11) not null auto_increment primary key,
          `certificate` int(11) not null,
          `activity_type` varchar(255),
          `module` int(11),
          `resource` int(11),
          `threshold` decimal(7,2),
          `operator` varchar(20),
          foreign key (`certificate`) references `certificate`(`id`)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `badge_criterion` (
          `id` int(11) not null auto_increment primary key,
          `badge` int(11) not null,
          `activity_type` varchar(255),
          `module` int(11),
          `resource` int(11),
          `threshold` decimal(7,2),
          `operator` varchar(20),
          foreign key (`badge`) references `badge`(`id`)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `user_certificate_criterion` (
          `id` int(11) not null auto_increment primary key,
          `user` int(11) not null,
          `certificate_criterion` int(11) not null,
          `created` datetime,
          unique key `user_certificate_criterion` (`user`, `certificate_criterion`),
          foreign key (`user`) references `user`(`id`),
          foreign key (`certificate_criterion`) references `certificate_criterion`(`id`)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `user_badge_criterion` (
          `id` int(11) not null auto_increment primary key,
          `user` int(11) not null,
          `badge_criterion` int(11) not null,
          `created` datetime,
          unique key `user_badge_criterion` (`user`, `badge_criterion`),
          foreign key (`user`) references `user`(`id`),
          foreign key (`badge_criterion`) references `badge_criterion`(`id`)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `certified_users` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `course_title` varchar(255) NOT NULL DEFAULT '',
            `cert_title` varchar(255) NOT NULL DEFAULT '',
            `cert_message` TEXT,
            `cert_id` int(11) NOT NULL,
            `cert_issuer` varchar(256) DEFAULT NULL,
            `user_fullname` varchar(255) NOT NULL DEFAULT '',
            `assigned` datetime NOT NULL,
            `identifier` varchar(255) NOT NULL DEFAULT '',
            `expires` datetime DEFAULT NULL,
            `template_id` INT(11),
            PRIMARY KEY (`id`)
        ) $tbl_options");

    // install predefined cert templates
    installCertTemplates($webDir);
    // install badge icons
    installBadgeIcons($webDir);

    // tc attendance tables
    Database::get()->query("CREATE TABLE IF NOT EXISTS `tc_attendance` (
            `id` int(11) NOT NULL DEFAULT '0',
            `meetingid` varchar(42) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `bbbuserid` varchar(20) DEFAULT NULL,
            `totaltime` int(11) NOT NULL DEFAULT '0',
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`,`meetingid`),
            KEY `id` (`id`),
            KEY `meetingid` (`meetingid`)
        ) $tbl_options");

    Database::get()->query("CREATE TABLE IF NOT EXISTS `tc_log` (
                `id` int(11) NOT NULL,
                `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `meetingid` varchar(42) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                `bbbuserid` varchar(20) DEFAULT NULL,
                `fullName` varchar(200) DEFAULT NULL,
                `type` varchar(255) default 'bbb',
                PRIMARY KEY (`id`),
                KEY `userid` (`bbbuserid`),
                KEY `fullName` (`fullName`)
            ) $tbl_options");

    Database::get()->query('ALTER TABLE poll_question
                CHANGE question_text question_text TEXT NOT NULL');
    Database::get()->query('ALTER TABLE document
                CHANGE filename filename VARCHAR(255) NOT NULL COLLATE utf8_bin');

    // restore admin user white list
    Database::get()->query("UPDATE user SET whitelist=NULL where username='admin'");
}

/**
 * @brief upgrade queries to 3.6.3
 * @return void
 */
function upgrade_to_3_6_3() {

    Database::get()->query('ALTER TABLE tc_session
            CHANGE external_users external_users TEXT DEFAULT NULL');
}


/**
 * @brief upgrade queries to 3.7
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_7($tbl_options): void
{

    if (!DBHelper::fieldExists('wiki_properties', 'visible')) {
        Database::get()->query("ALTER TABLE `wiki_properties`
                ADD `visible` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1'");
    }

    // course units upgrade
    if (!DBHelper::fieldExists('course_units', 'finish_week')) {
        Database::get()->query("ALTER TABLE course_units ADD finish_week DATE after comments");
    }
    if (!DBHelper::fieldExists('course_units', 'start_week')) {
        Database::get()->query("ALTER TABLE course_units ADD start_week DATE after comments");
    }

    // -------------------------------------------------------------------------------
    // Upgrade course units. Merge course weekly view type with course unit view type
    // -------------------------------------------------------------------------------

    // For all courses with view type = 'weekly'
    $q = Database::get()->queryArray("SELECT id FROM course WHERE view_type = 'weekly'");
    foreach ($q as $courseid) {
        // Clean up: Check if course has any (simple) course units.
        // If yes then delete them since they are not appeared anywhere and we don't want to have stale db records.
        $s = Database::get()->queryArray("SELECT id FROM course_units WHERE course_id = ?d", $courseid);
        foreach ($s as $oldcu) {
            Database::get()->query("DELETE FROM unit_resources WHERE unit_id = ?d", $oldcu);
        }
        Database::get()->query("DELETE FROM course_units WHERE course_id = ?d", $courseid);

        // Now we can continue
        // Move weekly_course_units to course_units
        $result = Database::get()->query("INSERT INTO course_units
                        (title, comments, start_week, finish_week, visible, public, `order`, course_id)
                            SELECT CASE WHEN (title = '' OR title IS NULL)
                                THEN
                                  TRIM(CONCAT_WS(' ','$langWeek', DATE_FORMAT(start_week, '%d-%m-%Y')))
                                ELSE
                                  title
                                END
                              AS title,
                              comments, start_week, finish_week, visible, public, `order`, ?d
                                FROM course_weekly_view
                                WHERE course_id = ?d ORDER BY id", $courseid, $courseid);
        $unit_map = [];
        $current_id = Database::get()->querySingle("SELECT MAX(id) AS max_id FROM course_units")->max_id;
        Database::get()->queryFunc("SELECT id FROM course_weekly_view
                                WHERE course_id = ?d ORDER BY id DESC LIMIT ?d",
            function ($item) use (&$unit_map, &$current_id) {
                $unit_map[$current_id] = $item->id;
                $current_id--;
            },
            $courseid, $result->affectedRows);

        // move weekly_course_unit_resources to course_unit_resources
        foreach ($unit_map as $unit_id => $weekly_id) {
            Database::get()->query("INSERT INTO unit_resources
                                (unit_id, title, comments, res_id, `type`, visible, `order`, `date`)
                            SELECT ?d, title, comments, res_id, `type`, visible, `order`, `date`
                                FROM course_weekly_view_activities
                                WHERE course_weekly_view_id = ?d", $unit_id, $weekly_id);
        }
        // update course with new view type (=units)
        Database::get()->query("UPDATE course SET view_type = 'units' WHERE id = ?d", $courseid);
    }
    // drop tables
    if (DBHelper::tableExists('course_weekly_view')) {
        Database::get()->query("DROP TABLE course_weekly_view");
    }
    if (DBHelper::tableExists('course_weekly_view_activities')) {
        Database::get()->query("DROP TABLE course_weekly_view_activities");
    }
    // end of upgrading course units

    // course prerequisites
    Database::get()->query("CREATE TABLE IF NOT EXISTS `course_prerequisite` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` int(11) not null,
                `prerequisite_course` int(11) not null,
                PRIMARY KEY (`id`)
            ) $tbl_options");

    // lti apps
    Database::get()->query("CREATE TABLE IF NOT EXISTS lti_apps (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) DEFAULT NULL,
                `title` VARCHAR(255) DEFAULT NULL,
                `description` TEXT,
                `lti_provider_url` VARCHAR(255) DEFAULT NULL,
                `lti_provider_key` VARCHAR(255) DEFAULT NULL,
                `lti_provider_secret` VARCHAR(255) DEFAULT NULL,
                `launchcontainer` TINYINT(4) NOT NULL DEFAULT 1,
                `is_template` TINYINT(4) NOT NULL DEFAULT 0,
                `enabled` TINYINT(4) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) $tbl_options");

    if (!DBHelper::fieldExists('assignment', 'assignment_type')) {
        Database::get()->query("ALTER TABLE assignment ADD assignment_type TINYINT NOT NULL DEFAULT '0' AFTER password_lock");
    }
    if (!DBHelper::fieldExists('assignment', 'lti_template')) {
        Database::get()->query("ALTER TABLE assignment ADD lti_template INT(11) DEFAULT NULL AFTER assignment_type");
    }
    if (!DBHelper::fieldExists('assignment', 'launchcontainer')) {
        Database::get()->query("ALTER TABLE assignment ADD launchcontainer TINYINT DEFAULT NULL AFTER lti_template");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_feedbackreleasedate')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_feedbackreleasedate DATETIME NULL DEFAULT NULL AFTER launchcontainer");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_internetcheck')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_internetcheck TINYINT NOT NULL DEFAULT '1' AFTER tii_feedbackreleasedate");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_institutioncheck')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_institutioncheck TINYINT NOT NULL DEFAULT '1' AFTER tii_internetcheck");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_journalcheck')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_journalcheck TINYINT NOT NULL DEFAULT '1' AFTER tii_institutioncheck");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_report_gen_speed')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_report_gen_speed TINYINT NOT NULL DEFAULT '0' AFTER tii_journalcheck");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_s_view_reports')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_s_view_reports TINYINT NOT NULL DEFAULT '0' AFTER tii_report_gen_speed");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_studentpapercheck')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_studentpapercheck TINYINT NOT NULL DEFAULT '1' AFTER tii_s_view_reports");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_submit_papers_to')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_submit_papers_to TINYINT NOT NULL DEFAULT '1' AFTER tii_studentpapercheck");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_use_biblio_exclusion')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_use_biblio_exclusion TINYINT NOT NULL DEFAULT '0' AFTER tii_submit_papers_to");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_use_quoted_exclusion')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_use_quoted_exclusion TINYINT NOT NULL DEFAULT '0' AFTER tii_use_biblio_exclusion");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_exclude_type')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_exclude_type VARCHAR(20) NOT NULL DEFAULT 'none' AFTER tii_use_quoted_exclusion");
    }
    if (!DBHelper::fieldExists('assignment', 'tii_exclude_value')) {
        Database::get()->query("ALTER TABLE assignment ADD tii_exclude_value INT(11) NOT NULL DEFAULT '0' AFTER tii_exclude_type");
    }

    // move question position to exercise and exercise_answer
    // and make sure position is unique in each exercise / attempt
    if (DBHelper::fieldExists('exercise_question', 'q_position')) {
        Database::get()->query('ALTER TABLE exercise_with_questions ADD q_position INT(11) NOT NULL DEFAULT 1');
        Database::get()->query('ALTER TABLE exercise_answer_record ADD q_position INT(11) NOT NULL DEFAULT 1');
        Database::get()->query('UPDATE exercise_with_questions
                JOIN exercise_question ON exercise_question.id = question_id
                SET exercise_with_questions.q_position = exercise_question.q_position');
        Database::get()->query('UPDATE exercise_answer_record
                JOIN exercise_question ON exercise_question.id = question_id
                SET exercise_answer_record.q_position = exercise_question.q_position');
        $exercises = Database::get()->queryArray('SELECT exercise_id AS id
                FROM exercise_with_questions GROUP by exercise_id, q_position HAVING COUNT(*) > 1');
        foreach ($exercises as $exercise) {
            $questions = Database::get()->queryArray('SELECT question_id AS id FROM exercise_with_questions
                    WHERE exercise_id = ?d ORDER BY q_position', $exercise->id);
            $i = 1;
            foreach ($questions as $question) {
                Database::get()->query('UPDATE exercise_with_questions
                        SET q_position = ?d WHERE exercise_id = ?d AND question_id = ?d',
                    $i, $exercise->id, $question->id);
                Database::get()->query('UPDATE exercise_answer_record
                        JOIN exercise_user_record USING (eurid)
                        SET q_position = ?d WHERE eid = ?d AND question_id = ?d',
                    $i, $exercise->id, $question->id);
                $i++;
            }
        }
        Database::get()->query('ALTER TABLE exercise_question DROP q_position');
    }

    if (!DBHelper::fieldExists('exercise_user_record', 'assigned_to')) {
        Database::get()->query("ALTER TABLE `exercise_user_record`
                    ADD `assigned_to` INT(11) DEFAULT NULL");
    }

    // user consent
    Database::get()->query("CREATE TABLE IF NOT EXISTS `user_consent` (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT(11) NOT NULL,
            has_accepted BOOL NOT NULL DEFAULT 0,
            ts DATETIME,
            PRIMARY KEY (id),
            UNIQUE KEY (user_id),
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
          ) $tbl_options");
}


/**
 * @brief upgrade queries to 3.8
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_8($tbl_options): void
{

    if (DBHelper::fieldExists('announcement', 'preview')) {
        Database::get()->query("ALTER TABLE announcement DROP COLUMN preview");
    }
    // conference chat activity and agent
    if (!DBHelper::fieldExists('conference', 'chat_activity')) {
        Database::get()->query('ALTER TABLE conference ADD chat_activity boolean not null default false');
    }
    if (!DBHelper::fieldExists('conference', 'agent_created')) {
        Database::get()->query('ALTER TABLE conference ADD agent_created boolean not null default false');
    }

    // user settings table
    if (!DBHelper::tableExists('user_settings')) {
        Database::get()->query("CREATE TABLE `user_settings` (
                `setting_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `course_id` int(11) DEFAULT NULL,
                `value` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`setting_id`,`user_id`),
                KEY `user_id` (`user_id`),
                KEY `course_id` (`course_id`),
                  CONSTRAINT `user_settings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                  CONSTRAINT `user_settings_ibfk_4` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) $tbl_options");
    }

    // forum attachments
    if (!DBHelper::fieldExists('forum_post', 'topic_filepath')) {
        Database::get()->query("ALTER TABLE forum_post ADD `topic_filepath` varchar(200) DEFAULT NULL");
    }

    if (!DBHelper::fieldExists('forum_post', 'topic_filename')) {
        Database::get()->query("ALTER TABLE forum_post ADD `topic_filename` varchar(200) DEFAULT NULL");
    }
    // chat agent
    if (!DBHelper::fieldExists('conference', 'chat_activity_id')) {
        Database::get()->query('ALTER TABLE conference ADD chat_activity_id int(11)');
    }

    if (!DBHelper::fieldExists('conference', 'agent_id')) {
        Database::get()->query('ALTER TABLE conference ADD agent_id int(11)');
    }

    if (!DBHelper::tableExists('colmooc_user')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `colmooc_user` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `colmooc_id` INT(11) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY (user_id),
                FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('colmooc_user_session')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `colmooc_user_session` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `activity_id` INT(11) NOT NULL,
                `session_id` TEXT NOT NULL,
                `session_token` TEXT NOT NULL,
                `session_status` TINYINT(4) NOT NULL DEFAULT 0,
                `session_status_updated` datetime DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY `user_activity` (`user_id`, `activity_id`),
                FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('colmooc_pair_log')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `colmooc_pair_log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `activity_id` INT(11) NOT NULL,
                `moderator_id` INT(11) NOT NULL,
                `partner_id` INT(11) NOT NULL,
                `session_status` TINYINT(4) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (moderator_id) REFERENCES user(id) ON DELETE CASCADE,
                FOREIGN KEY (partner_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");
    }

    //learning analytics
    if (!DBHelper::tableExists('analytics')) {
        Database::get()->query("CREATE TABLE `analytics` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `courseID` int(11) NOT NULL,
              `title` varchar(255) NOT NULL,
              `description` text,
              `active` tinyint(1) NOT NULL DEFAULT '0',
              `start_date` date DEFAULT NULL,
              `end_date` date DEFAULT NULL,
              `created` datetime DEFAULT NULL,
              `periodType` int(11) NOT NULL,
              PRIMARY KEY (id)) $tbl_options");
    }

    if (!DBHelper::tableExists('analytics_element')) {
        Database::get()->query("CREATE TABLE `analytics_element` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `analytics_id` int(11) NOT NULL,
              `module_id` int(11) NOT NULL,
              `resource` int(11) DEFAULT NULL,
              `upper_threshold` float DEFAULT NULL,
              `lower_threshold` float DEFAULT NULL,
              `weight` int(11) NOT NULL DEFAULT '1',
              `min_value` float NOT NULL,
              `max_value` float NOT NULL,
              PRIMARY KEY (`id`)) $tbl_options");
    }

    if (!DBHelper::tableExists('user_analytics')) {
        Database::get()->query("CREATE TABLE `user_analytics` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `analytics_element_id` int(11) NOT NULL,
              `value` float NOT NULL DEFAULT '0',
              `updated` datetime NOT NULL,
              PRIMARY KEY (`id`)) $tbl_options");
    }

    // lti apps
    if (!DBHelper::fieldExists('lti_apps', 'all_courses')) {
        Database::get()->query("ALTER TABLE lti_apps ADD all_courses TINYINT(1) NOT NULL DEFAULT 1");
    }
    if (!DBHelper::fieldExists('lti_apps', 'type')) {
        Database::get()->query("ALTER TABLE lti_apps ADD `type` VARCHAR(255) NOT NULL DEFAULT 'turnitin'");
    }

    if (!DBHelper::tableExists('course_lti_app')) {
        Database::get()->query("CREATE TABLE `course_lti_app` (
              `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `course_id` int(11) NOT NULL,
              `lti_app` int(11) NOT NULL,
              FOREIGN KEY (`course_id`) REFERENCES `course` (`id`),
              FOREIGN KEY (`lti_app`) REFERENCES `lti_apps` (`id`)) $tbl_options");
    }

    // fix wrong entries in exercises answers regarding negative weight (if any)
    Database::get()->query("UPDATE exercise_answer SET weight=-ABS(weight) WHERE correct=0 AND weight>0");

    // in gradebook change `weight` type from integer to decimal
    Database::get()->query("ALTER TABLE `gradebook_activities` CHANGE `weight` `weight` DECIMAL(5,2) NOT NULL DEFAULT '0'");

    // peer review
    if (!DBHelper::fieldExists('assignment', 'reviews_per_assignment')) {
        Database::get()->query("ALTER TABLE assignment ADD `reviews_per_assignment` INT(4) DEFAULT NULL");
    }
    if (!DBHelper::fieldExists('assignment', 'start_date_review')) {
        Database::get()->query("ALTER TABLE assignment ADD `start_date_review` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
    }
    if (!DBHelper::fieldExists('assignment', 'due_date_review')) {
        Database::get()->query("ALTER TABLE assignment ADD `due_date_review` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
    }

    if (!DBHelper::tableExists('assignment_grading_review')) {
        Database::get()->query("CREATE TABLE `assignment_grading_review` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `assignment_id` INT(11) NOT NULL,
            `user_submit_id` INT(11) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `file_path` VARCHAR(200) NOT NULL,
            `file_name` VARCHAR(200) NOT NULL,
            `submission_text` MEDIUMTEXT,
            `submission_date` DATETIME NOT NULL,
            `gid` INT(11) NOT NULL,
            `users_id` INT(11) NOT NULL,
            `grade` FLOAT DEFAULT NULL,
            `comments` TEXT,
            `date_submit` DATETIME DEFAULT NULL,
            `rubric_scales` TEXT) $tbl_options");
    }
    Database::get()->query("ALTER TABLE `ebook_subsection` CHANGE `section_id` `section_id` int(11) NOT NULL");
}

/**
 * @brief upgrade queries to 3.9
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_9($tbl_options): void
{

    if (!DBHelper::fieldExists('exercise', 'continue_time_limit')) {
        Database::get()->query("ALTER TABLE `exercise` ADD `continue_time_limit` INT(11) NOT NULL DEFAULT 0");
    }
    if (!DBHelper::fieldExists('assignment', 'max_submissions')) {
        Database::get()->query("ALTER TABLE `assignment` ADD `max_submissions` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1");
    }
    if (!DBHelper::fieldExists('group', 'visible')) {
        Database::get()->query("ALTER TABLE `group` ADD `visible` TINYINT(4) NOT NULL DEFAULT 1");
    }
}

/**
 * @brief upgrade queries to 3.10
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_10($tbl_options): void
{
    if (!DBHelper::fieldExists('exercise_with_questions', 'random_criteria')) {
        Database::get()->query("ALTER TABLE exercise_with_questions ADD `random_criteria` TEXT");
        Database::get()->query("ALTER TABLE exercise_with_questions DROP PRIMARY KEY");
        Database::get()->query("ALTER TABLE exercise_with_questions ADD id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST");
        Database::get()->query("ALTER TABLE exercise_with_questions CHANGE question_id question_id INT NULL DEFAULT 0");
    }

    if (!DBHelper::fieldExists('exercise', 'shuffle')) {
        Database::get()->query("ALTER TABLE `exercise` ADD `shuffle` SMALLINT NOT NULL DEFAULT '0' AFTER `random`");
        // update old records
        Database::get()->query("UPDATE exercise SET shuffle=1, random=0 WHERE random=32767");
        Database::get()->query("UPDATE exercise SET shuffle=1 WHERE random>0");
    }
    if (!DBHelper::fieldExists('exercise', 'range')) {
        Database::get()->query("ALTER TABLE `exercise` ADD `range` TINYINT DEFAULT 0 AFTER `type`");
    }
    if (!DBHelper::fieldExists('tc_session', 'options')) {
        Database::get()->query("ALTER TABLE `tc_session` ADD `options` TEXT DEFAULT NULL");
    }
}

/**
 * @brief upgrade queries to 3.11
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_11($tbl_options) {
    if (!DBHelper::fieldExists('poll', 'lti_template')) {
        Database::get()->query("ALTER TABLE poll ADD lti_template INT(11) DEFAULT NULL AFTER assign_to_specific");
    }
    if (!DBHelper::fieldExists('poll', 'launchcontainer')) {
        Database::get()->query("ALTER TABLE poll ADD launchcontainer TINYINT DEFAULT NULL AFTER lti_template");
    }
    if (!DBHelper::fieldExists('poll', 'multiple_submissions')) {
        Database::get()->query("ALTER TABLE poll ADD multiple_submissions TINYINT NOT NULL DEFAULT '0'");
    }
    if (!DBHelper::fieldExists('poll', 'default_answer')) {
        Database::get()->query("ALTER TABLE poll ADD default_answer TINYINT NOT NULL DEFAULT '0'");
        Database::get()->query("UPDATE poll SET default_answer = 1"); // set value for previous polls
    }
    if (!DBHelper::fieldExists('exercise', 'calc_grade_method')) {
        Database::get()->query("ALTER TABLE exercise ADD calc_grade_method TINYINT DEFAULT '1'");
        Database::get()->query("UPDATE exercise SET calc_grade_method = 0");
    }
    if (!DBHelper::fieldExists('certified_users', 'user_id')) {
        Database::get()->query("ALTER TABLE certified_users
                ADD user_id INT DEFAULT NULL,
                ADD FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL");
        Database::get()->query("UPDATE certified_users JOIN user
                ON certified_users.user_fullname = CONCAT(user.surname, ' ', user.givenname)
                SET certified_users.user_id = user.id");
    }

}

/**
 * @brief upgrade queries to 3.12
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_12($tbl_options) {

    Database::get()->query("ALTER TABLE user MODIFY `password` VARCHAR(255) NOT NULL DEFAULT 'empty'");
    // fix email status of guest user
    Database::get()->query("UPDATE `user` SET verified_mail = " . EMAIL_UNVERIFIED . ", receive_mail = " . EMAIL_NOTIFICATIONS_DISABLED . " WHERE status = " . USER_GUEST . "");
    Database::get()->query("UPDATE `course_user` set receive_mail = " . EMAIL_NOTIFICATIONS_DISABLED . " WHERE status = " . USER_GUEST . "");

    // tc attendance
    Database::get()->query("ALTER TABLE `tc_attendance` CHANGE `bbbuserid` `bbbuserid` varchar(100)");
    Database::get()->query("ALTER TABLE `tc_log` CHANGE `bbbuserid` `bbbuserid` varchar(100)");

    if (!DBHelper::fieldExists('admin', 'department_id')) {
        Database::get()->query('DELETE admin
                FROM admin LEFT JOIN user ON user_id = user.id
                WHERE user.id IS NULL');
        if (DBHelper::indexExists('admin', 'idUser')) {
            Database::get()->query('ALTER TABLE admin DROP index idUser');
        }
        Database::get()->query('ALTER TABLE admin
                DROP PRIMARY KEY,
                ADD COLUMN id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
                ADD department_id INT(11) DEFAULT NULL,
                ADD FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
                ADD FOREIGN KEY (department_id) REFERENCES hierarchy (id) ON DELETE CASCADE,
                ADD UNIQUE KEY (user_id, department_id)');
        Database::get()->query('INSERT INTO admin (user_id, privilege, department_id)
                SELECT user_id, ?d, department FROM admin, user_department
                    WHERE user_id = user AND privilege = ?d',
            DEPARTMENTMANAGE_USER, DEPARTMENTMANAGE_USER);
        Database::get()->query('DELETE FROM admin
                WHERE department_id IS NULL AND privilege = ?d',
            DEPARTMENTMANAGE_USER);

    }

    // h5p
    if (!DBHelper::tableExists('h5p_library')) {
        Database::get()->query("CREATE TABLE h5p_library (
                id INT(10) NOT NULL AUTO_INCREMENT,
                machine_name VARCHAR(255) NOT NULL,
                title VARCHAR(255) NOT NULL,
                major_version INT(4) NOT NULL,
                minor_version INT(4) NOT NULL,
                patch_version INT(4) NOT NULL,
                runnable INT(1) NOT NULL DEFAULT '0',
                fullscreen INT(1) NOT NULL DEFAULT '0',
                embed_types VARCHAR(255),
                preloaded_js LONGTEXT,
                preloaded_css LONGTEXT,
                droplibrary_css LONGTEXT,
                semantics LONGTEXT,
                add_to LONGTEXT,
                core_major INT(4),
                core_minor INT(4),
                metadata_settings LONGTEXT,
                tutorial LONGTEXT,
                example LONGTEXT,
              PRIMARY KEY(id)) $tbl_options");
    } elseif (!DBHelper::fieldExists('h5p_library', 'example')) {
        Database::get()->query("ALTER TABLE h5p_library
                MODIFY preloaded_js LONGTEXT,
                MODIFY preloaded_css LONGTEXT,
                ADD droplibrary_css LONGTEXT,
                ADD semantics LONGTEXT,
                ADD add_to LONGTEXT,
                ADD core_major INT(4),
                ADD core_minor INT(4),
                ADD metadata_settings LONGTEXT,
                ADD tutorial LONGTEXT,
                ADD example LONGTEXT");
    }

    if (!DBHelper::tableExists('h5p_library_dependency')) {
        Database::get()->query("CREATE TABLE h5p_library_dependency (
                id INT(10) NOT NULL AUTO_INCREMENT,
                library_id INT(10) NOT NULL,
                required_library_id INT(10) NOT NULL,
                dependency_type VARCHAR(255) NOT NULL,
              PRIMARY KEY(id)) $tbl_options");
    }

    if (!DBHelper::tableExists('h5p_library_translation')) {
        Database::get()->query("CREATE TABLE h5p_library_translation (
                id INT(10) NOT NULL,
                library_id INT(10) NOT NULL,
                language_code VARCHAR(255) NOT NULL,
                language_json TEXT NOT NULL,
              PRIMARY KEY(id)) $tbl_options");
    }

    if (!DBHelper::tableExists('h5p_content')) {
        Database::get()->query("CREATE TABLE h5p_content (
                id INT(10) NOT NULL AUTO_INCREMENT,
                title varchar(255),
                main_library_id INT(10) NOT NULL,
                params LONGTEXT,
                course_id INT(11) NOT NULL,
              PRIMARY KEY(id)) $tbl_options");
    } else {
        Database::get()->query("ALTER TABLE h5p_content MODIFY params LONGTEXT");
    }

    if (!DBHelper::tableExists('h5p_content_dependency')) {
        Database::get()->query("CREATE TABLE h5p_content_dependency (
                id INT(10) NOT NULL AUTO_INCREMENT,
                content_id INT(10) NOT NULL,
                library_id INT(10) NOT NULL,
                dependency_type VARCHAR(10) NOT NULL,
          PRIMARY KEY(id)) $tbl_options");
    }
}

/**
 * @brief upgrade queries to 3.13
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_13($tbl_options): void
{

    // h5p
    if (!DBHelper::fieldExists('h5p_content', 'title')) {
        Database::get()->query("ALTER TABLE h5p_content ADD title VARCHAR(255) AFTER id");
    }
    if (!DBHelper::fieldExists('h5p_content', 'enabled')) {
        Database::get()->query("ALTER TABLE h5p_content ADD enabled TINYINT(4) NOT NULL DEFAULT 1 AFTER course_id");
    }
    if (!DBHelper::fieldExists('h5p_content', 'reuse_enabled')) {
        Database::get()->query("ALTER TABLE h5p_content ADD reuse_enabled TINYINT(4) NOT NULL DEFAULT 1 AFTER enabled");
    }
    // course units prerequisites
    if (!DBHelper::fieldExists('certificate', 'unit_id')) {
        Database::get()->query("ALTER TABLE certificate ADD unit_id INT(11) NOT NULL DEFAULT 0");
    }
    if (!DBHelper::fieldExists('badge', 'unit_id')) {
        Database::get()->query("ALTER TABLE badge ADD unit_id INT(11) NOT NULL DEFAULT 0");
    }
    if (!DBHelper::tableExists('unit_prerequisite')) {
        Database::get()->query("CREATE TABLE `unit_prerequisite` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `course_id` int(11) not null,
              `unit_id` int(11) not null,
              `prerequisite_unit` int(11) not null,
              PRIMARY KEY (`id`)) $tbl_options");
    }
    // course favorites
    if (!DBHelper::fieldExists('course_user','favorite')) {
        Database::get()->query("ALTER TABLE course_user ADD favorite datetime DEFAULT NULL");
    }
    // blog post visibility
    if (!DBHelper::fieldExists('blog_post', 'visible')) {
        Database::get()->query("ALTER TABLE blog_post ADD `visible` TINYINT UNSIGNED NOT NULL DEFAULT '1'");
    }

    // lti publish provider
    if (!DBHelper::tableExists('course_lti_publish')) {
        Database::get()->query("CREATE TABLE `course_lti_publish` (
              `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `course_id` int(11) NOT NULL,
              `title` VARCHAR(255) NOT NULL,
              `description` TEXT,
              `lti_provider_key` VARCHAR(255) NOT NULL,
              `lti_provider_secret` VARCHAR(255) NOT NULL,
              `enabled` TINYINT(4) NOT NULL DEFAULT 1,
              FOREIGN KEY (`course_id`) REFERENCES `course` (`id`)) $tbl_options");
    }

    if (!DBHelper::tableExists('course_lti_publish_user_enrolments')) {
        Database::get()->query("CREATE TABLE `course_lti_publish_user_enrolments` (
              `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `publish_id` int(11) NOT NULL,
              `user_id` int(11) NOT NULL,
              `created` int(11) NOT NULL,
              `updated` int(11) NOT NULL,
              FOREIGN KEY (`publish_id`) REFERENCES `course_lti_publish` (`id`),
              FOREIGN KEY (`user_id`) REFERENCES  `user` (`id`)) $tbl_options");
    }

    if (!DBHelper::tableExists('course_lti_enrol_users')) {
        Database::get()->query("CREATE TABLE `course_lti_enrol_users` (
              `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `publish_id` int(11) NOT NULL,
              `user_id` int(11) NOT NULL,
              `service_url` TEXT,
              `source_id` TEXT,
              `consumer_key` TEXT,
              `consumer_secret` TEXT,
              `memberships_url` TEXT,
              `memberships_id` TEXT,
              `last_grade` FLOAT,
              `last_access` int(11),
              `time_created` int(11),
              FOREIGN KEY (`publish_id`) REFERENCES `course_lti_publish` (`id`),
              FOREIGN KEY (`user_id`) REFERENCES  `user` (`id`)) $tbl_options");
    }

    // lti provider tables
    if (!DBHelper::tableExists('lti_publish_lti2_consumer')) {
        Database::get()->query("CREATE TABLE `lti_publish_lti2_consumer` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(50) NOT NULL,
                `consumerkey256` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
                `consumerkey` TEXT,
                `secret` VARCHAR(1024) NOT NULL,
                `ltiversion` VARCHAR(10),
                `consumername` VARCHAR(255),
                `consumerversion` VARCHAR(255),
                `consumerguid` VARCHAR(1024),
                `profile` TEXT,
                `toolproxy` TEXT,
                `settings` TEXT,
                `protected` smallint(6) NOT NULL,
                `enabled` smallint(6) NOT NULL,
                `enablefrom` int(11),
                `enableuntil` int(11),
                `lastaccess` int(11),
                `created` int(11) NOT NULL,
                `updated` int(11) NOT NULL) $tbl_options");
    }

    if (!DBHelper::tableExists('lti_publish_lti2_context')) {
        Database::get()->query("CREATE TABLE `lti_publish_lti2_context` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `consumerid` int(11) NOT NULL,
                `lticontextkey` VARCHAR(255) NOT NULL,
                `type` VARCHAR(100),
                `settings` TEXT,
                `created` int(11) NOT NULL,
                `updated` int(11) NOT NULL) $tbl_options");
    }

    if (!DBHelper::tableExists('lti_publish_lti2_nonce')) {
        Database::get()->query("CREATE TABLE `lti_publish_lti2_nonce` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `consumerid` int(11) NOT NULL,
                `value` VARCHAR(64) NOT NULL,
                `expires` int(11) NOT NULL) $tbl_options");
    }

    if (!DBHelper::tableExists('lti_publish_lti2_resource_link')) {
        Database::get()->query("CREATE TABLE `lti_publish_lti2_resource_link` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `contextid` int(11),
                `consumerid` int(11),
                `ltiresourcelinkkey` VARCHAR(255) NOT NULL,
                `settings` TEXT,
                `primaryresourcelinkid` int(11),
                `shareapproved` smallint(6),
                `created` int(11) NOT NULL,
                `updated` int(11) NOT NULL) $tbl_options");
    }

    if (!DBHelper::tableExists('lti_publish_lti2_share_key')) {
        Database::get()->query("CREATE TABLE `lti_publish_lti2_share_key` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `sharekey` VARCHAR(32) NOT NULL UNIQUE,
                `resourcelinkid` int(11) NOT NULL UNIQUE,
                `autoapprove` smallint(6) NOT NULL,
                `expires` int(11) NOT NULL) $tbl_options");
    }

    if (!DBHelper::tableExists('lti_publish_lti2_tool_proxy')) {
        Database::get()->query("CREATE TABLE `lti_publish_lti2_tool_proxy` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `toolproxykey` VARCHAR(32) NOT NULL UNIQUE,
                `consumerid` int(11) NOT NULL,
                `toolproxy` TEXT NOT NULL,
                `created` int(11) NOT NULL,
                `updated` int(11) NOT NULL) $tbl_options");
    }

    if (!DBHelper::tableExists('lti_publish_lti2_user_result')) {
        Database::get()->query("CREATE TABLE `lti_publish_lti2_user_result` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `resourcelinkid` int(11) NOT NULL,
                `ltiuserkey` VARCHAR(255) NOT NULL,
                `ltiresultsourcedid` VARCHAR(1024) NOT NULL,
                `created` int(11) NOT NULL,
                `updated` int(11) NOT NULL) $tbl_options");
    }

    // flipped classroom course type
    if (!DBHelper::tableExists('course_activities')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `course_activities` (
                `id` int(11) NOT NULL auto_increment,
                `activity_id` varchar(4) NOT NULL,
                `activity_type` tinyint(4) NOT NULL,
                `visible` int(11) NOT NULL,
                `unit_id` int(11) NOT NULL,
                `module_id` int(11) NOT NULL,
                PRIMARY KEY  (`id`)) $tbl_options");

        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC1',0,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC2',0,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC3',0,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC5',0,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC6',0,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC7',1,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC8',1,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC9',1,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC10',1,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC11',1,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC12',1,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC13',1,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC14',1,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC15',2,0,0,0)");
        Database::get()->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC16',2,0,0,0)");
    }
    if (!DBHelper::tableExists('course_units_activities')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS`course_units_activities` (
                `id` INT NOT NULL AUTO_INCREMENT ,
                `course_code` VARCHAR(20) NOT NULL ,
                `activity_id` VARCHAR(5) NOT NULL ,
                `unit_id` INT NOT NULL,
                `tool_ids` TEXT NOT NULL ,
                `activity_type` INT NOT NULL,
                `visible` INT NOT NULL,
                PRIMARY KEY (`id`)) $tbl_options");
    }
    if (!DBHelper::tableExists('course_class_info')) {
            Database::get()->query("CREATE TABLE IF NOT EXISTS `course_class_info` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `student_number` VARCHAR(50) NOT NULL ,
            `lessons_number` INT NOT NULL,
            `lesson_hours` INT NOT NULL,
            `home_hours` INT NOT NULL,
            `total_hours` INT NOT NULL,
            `course_code` VARCHAR(20) NOT NULL,
            PRIMARY KEY (`id`)) $tbl_options");
    }
    if (!DBHelper::tableExists('course_learning_objectives')) {
            Database::get()->query("CREATE TABLE `course_learning_objectives` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `course_code` VARCHAR(20) NOT NULL,
            `title` TEXT NOT NULL,
            PRIMARY KEY (`id`)) $tbl_options");
    }

    if (!DBHelper::fieldExists('course','flipped_flag')) {
        Database::get()->query("ALTER table course ADD `flipped_flag` INT(11) NOT NULL DEFAULT 0");
    }
    if (!DBHelper::fieldExists('course','lectures_model')) {
        Database::get()->query("ALTER table course ADD `lectures_model` INT(11) NOT NULL DEFAULT 0");
    }
    if (!DBHelper::fieldExists('unit_resources', 'fc_type')) {
        Database::get()->query("ALTER table unit_resources ADD `fc_type` INT(11) NOT NULL DEFAULT 3");
    }
    if (!DBHelper::fieldExists('unit_resources', 'activity_title')) {
        Database::get()->query("ALTER table unit_resources ADD `activity_title` VARCHAR(50) NOT NULL DEFAULT ''");
    }
    if (!DBHelper::fieldExists('unit_resources', 'activity_id')) {
        Database::get()->query("ALTER table unit_resources ADD `activity_id` VARCHAR(5) NOT NULL DEFAULT 'FC000'");
    }

    if (!DBHelper::fieldExists('exercise_question', 'copy_of_qid')) {
        Database::get()->query("ALTER table exercise_question ADD copy_of_qid INT(11) DEFAULT NULL,
            ADD CONSTRAINT FOREIGN KEY (copy_of_qid) REFERENCES exercise_question(id) ON DELETE SET NULL");
    }

}


/**
 * @brief upgrade queries to 3.14
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_14($tbl_options) : void {

    if (!DBHelper::indexExists('tc_servers', 'hostname')) {
        Database::get()->query("ALTER TABLE `tc_servers` ADD UNIQUE `hostname` (`hostname`)");
        Database::get()->query("ALTER TABLE `tc_servers` CHANGE `hostname` `hostname` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL AFTER `type`");
        Database::get()->query("ALTER TABLE `tc_servers` CHANGE `type` `type` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL AFTER `id`");
    }
    if (DBHelper::fieldExists('tc_session', 'meeting_id')) {
        Database::get()->query("ALTER TABLE `tc_session` CHANGE `meeting_id` `meeting_id` varchar(255) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL");
    }

    // question feedback
    if (!DBHelper::fieldExists('exercise_question', 'feedback')) {
        Database::get()->query("ALTER TABLE exercise_question ADD `feedback` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci AFTER description");
    }
    // exercise end message (aka feedback)
    if (!DBHelper::fieldExists('exercise', 'general_feedback')) {
        Database::get()->query("ALTER TABLE `exercise` ADD `general_feedback` TEXT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_520_ci' NULL");
    }

    // clean up gradebook -- delete multiple tuples (gradebook_activity_id, uid) in `gradebook_book` table (if any)
    $q = Database::get()->queryArray("SELECT MIN(id) AS id, uid, gradebook_activity_id, COUNT(*) AS cnt
            FROM gradebook_book GROUP BY gradebook_activity_id, uid HAVING cnt>=2 ORDER BY cnt");
    foreach ($q as $data) {
        Database::get()->query("DELETE FROM gradebook_book
            WHERE uid = ?d
            AND gradebook_activity_id = ?d
            AND id != ?d",
            $data->uid, $data->gradebook_activity_id, $data->id);
    }
    if (!DBHelper::indexExists('gradebook_book', 'activity_uid')) {
        Database::get()->query("ALTER TABLE gradebook_book ADD UNIQUE activity_uid (gradebook_activity_id, uid)");
    }

    // learnPath user progress
    if (!DBHelper::fieldExists('lp_user_module_progress', 'attempt')) {
        Database::get()->query("ALTER TABLE lp_user_module_progress ADD `attempt` int(11) NOT NULL DEFAULT 1 AFTER credit");
    }

    if (!DBHelper::fieldExists('lp_user_module_progress', 'started')) {
        Database::get()->query("ALTER TABLE lp_user_module_progress ADD `started` datetime DEFAULT NULL AFTER attempt");
    }

    if (!DBHelper::fieldExists('lp_user_module_progress', 'accessed')) {
        Database::get()->query("ALTER TABLE lp_user_module_progress ADD `accessed` datetime DEFAULT NULL AFTER started");
    }

    if (!DBHelper::tableExists('page')) {
        Database::get()->query("CREATE TABLE `page` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `course_id` int(11) DEFAULT NULL,
            `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
            `path` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `visible` tinyint(4) DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `course_id_index` (`course_id`)) $tbl_options");
    }

    if (!DBHelper::fieldExists('user','pic_public')) {
        Database::get()->query("ALTER TABLE user ADD pic_public TINYINT(1) NOT NULL DEFAULT 0 AFTER am_public");
    }

    if (DBHelper::fieldExists('monthly_summary', 'logins')) {
        // convert `month` field from `varchar` to `date`
        Database::get()->query("UPDATE monthly_summary SET month = STR_TO_DATE(CONCAT('01 ', month),'%d %m %Y')");
        Database::get()->query("ALTER TABLE `monthly_summary` CHANGE `month` `month` DATE NOT NULL AFTER `id`");
        // remove `login` field (`login` field is in table `loginout_summary`)
        delete_field('monthly_summary', 'logins');
    }

    if (DBHelper::fieldExists('course', 'finish_date')) {
        Database::get()->query("UPDATE course SET finish_date=NULL");
        Database::get()->query("ALTER TABLE course CHANGE finish_date end_date DATE DEFAULT NULL");
    }

    if (!DBHelper::fieldExists('course', 'updated')) {
        Database::get()->querySingle("ALTER TABLE `course`ADD `updated` datetime NULL AFTER `created`");
    }

    if (!DBHelper::fieldExists('group_properties', 'public_users_list')) {
        Database::get()->query("ALTER TABLE `group_properties`ADD `public_users_list` tinyint NOT NULL DEFAULT '1'");
    }
    // question feedback
    if (!DBHelper::fieldExists('exercise_question', 'feedback')) {
        Database::get()->query("ALTER TABLE exercise_question ADD `feedback` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci AFTER description");
    }
    // exercise end message (aka feedback)
    if (!DBHelper::fieldExists('exercise', 'general_feedback')) {
        Database::get()->query("ALTER TABLE `exercise` ADD `general_feedback` TEXT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_520_ci' NULL");
    }

    // api token
    if (!DBHelper::tableExists('api_token')) {
        Database::get()->querySingle("CREATE TABLE `api_token` (
            `id` smallint NOT NULL AUTO_INCREMENT,
            `token` text CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `ip` varchar(45) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `enabled` tinyint NOT NULL,
            `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `expired` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)) $tbl_options");
    }

    // api token specific fields
    if (!DBHelper::fieldExists('api_token', 'created')) {
        Database::get()->query("ALTER TABLE `api_token` ADD `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
    if (!DBHelper::fieldExists('api_token', 'updated')) {
        Database::get()->query("ALTER TABLE `api_token` ADD `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
    if (!DBHelper::fieldExists('api_token', 'expired')) {
        Database::get()->query("ALTER TABLE `api_token` ADD `expired` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

}

/** @brief upgrade queries to 3.15
* @param $tbl_options
* @return void
*/
function upgrade_to_3_15($tbl_options) : void
{
    // course reviewer
    if (!DBHelper::fieldExists('course_user','course_reviewer')) {
        Database::get()->query("ALTER TABLE course_user ADD `course_reviewer` TINYINT NOT NULL DEFAULT '0' AFTER editor");
    }
    //quick poll
    if (!DBHelper::fieldExists('poll', 'display_position')) {
        Database::get()->query("ALTER TABLE poll ADD `display_position` INT(1) NOT NULL DEFAULT 0 AFTER show_results");
    }
    if (!DBHelper::tableExists('lti_publish_lti2_consumer')) {
        Database::get()->query("CREATE TABLE `lti_publish_lti2_consumer` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(50) NOT NULL,
                `consumerkey256` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
                `consumerkey` TEXT,
                `secret` VARCHAR(1024) NOT NULL,
                `ltiversion` VARCHAR(10),
                `consumername` VARCHAR(255),
                `consumerversion` VARCHAR(255),
                `consumerguid` VARCHAR(1024),
                `profile` TEXT,
                `toolproxy` TEXT,
                `settings` TEXT,
                `protected` smallint(6) NOT NULL,
                `enabled` smallint(6) NOT NULL,
                `enablefrom` int(11),
                `enableuntil` int(11),
                `lastaccess` int(11),
                `created` int(11) NOT NULL,
                `updated` int(11) NOT NULL) $tbl_options");
    } else {
        Database::get()->query('ALTER TABLE lti_publish_lti2_consumer
            MODIFY `consumerkey256` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL');
    }

    if (!DBHelper::fieldExists('user', 'disable_course_registration')) {
        Database::get()->query("ALTER TABLE `user`ADD `disable_course_registration` tinyint NULL DEFAULT 0");
    }

    Database::get()->query("ALTER TABLE course CHANGE code code VARCHAR(40) NOT NULL");

    if (!DBHelper::tableExists('course_invitation')) {
        Database::get()->query("CREATE TABLE `course_invitation` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `surname` varchar(255) NOT NULL DEFAULT '',
            `givenname` varchar(255) NOT NULL DEFAULT '',
            `email` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
            `identifier` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL,
            `expires_at` datetime DEFAULT NULL,
            `registered_at` datetime DEFAULT NULL,
            `course_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `identifier` (`identifier`),
            UNIQUE KEY `course_email` (`course_id`,`email`),
            CONSTRAINT `invitation_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('minedu_departments')) {
        Database::get()->query("CREATE TABLE `minedu_departments` (
              `MineduID` TEXT NOT NULL,
              `Institution` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
              `School` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
              `Department` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
              `Comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
            ) $tbl_options");
    }
    // insert departments info
    update_minedu_deps();
    // options
    set_config('allow_rec_video', 1);
    set_config('allow_rec_audio', 1);

}

/**
 * @brief upgrade queries to 3.16
 * @param $tbl_options
 * @return void
 */
function upgrade_to_3_16($tbl_options) : void
{
    if (!dbhelper::tableexists('login_lock')) {
        Database::get()->query('CREATE TABLE `login_lock` (
           `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
           `user_id` INT(11) NOT NULL,
           `session_id` VARCHAR(48) NOT NULL COLLATE ascii_bin,
           `ts` DATETIME NOT NULL,
           FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
           UNIQUE KEY (session_id)) CHARACTER SET ascii ENGINE=InnoDB');
    }
    if (!DBHelper::fieldExists('exercise', 'options')) {
        Database::get()->query("ALTER TABLE `exercise` ADD `options` text");
    }

    if (!DBHelper::tableExists('zoom_user')) {
        Database::get()->querySingle("CREATE TABLE `zoom_user` (
          `user_id` INT(10) NOT NULL,
          `id` varchar(45) NOT NULL,
          `first_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
          `last_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
          `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
          `type` TINYINT(1) NOT NULL DEFAULT 1,
          `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id)) $tbl_options");
    }

    // Fix incorrect dates in course_user table
    Database::get()->query('UPDATE course_user
        SET document_timestamp = reg_date
        WHERE document_timestamp < reg_date');

    if (!dbhelper::tableexists('minedu_department_association')) {
        Database::get()->query("CREATE TABLE `minedu_department_association` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `minedu_id` int(11) NOT NULL DEFAULT 0,
              `department_id` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`department_id`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $tbl_options");
    }

    if (!dbhelper::tableexists('course_certificate_template')) {
        Database::get()->query("CREATE TABLE `course_certificate_template` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `course_id` int(11) NOT NULL,
              `certificate_template_id` mediumint(8) NOT NULL,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
              FOREIGN KEY (`certificate_template_id`) REFERENCES `certificate_template` (`id`) ON DELETE CASCADE) $tbl_options");
    }
    if (!DBHelper::fieldExists('certificate_template', 'all_courses')) {
        Database::get()->query("ALTER TABLE `certificate_template` ADD `all_courses` tinyint(1) NOT NULL DEFAULT 1");
    }
}

/**
 * @brief upgrade queries to 4.0
 * @param $tbl_options
 * @return void
 */
function upgrade_to_4_0($tbl_options): void {

    // widgets
    if (!DBHelper::tableExists('widget')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `widget` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `class` varchar(400) NOT NULL) $tbl_options");
    }
    if (!DBHelper::tableExists('widget_widget_area')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `widget_widget_area` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `widget_id` int(11) unsigned NOT NULL,
            `widget_area_id` int(11) NOT NULL,
            `options` text NOT NULL,
            `position` int(3) NOT NULL,
            `user_id` int(11) NULL,
            `course_id` int(11) NULL,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
            FOREIGN KEY (widget_id) REFERENCES widget(id) ON DELETE CASCADE) $tbl_options");
    }

    // `request` tables (aka `ticketing`)
    if (!DBHelper::tableExists('request_type')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS request_type (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` MEDIUMTEXT NOT NULL,
            `description` MEDIUMTEXT NULL DEFAULT NULL) $tbl_options");
    }

    if (!DBHelper::tableExists('request')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS request (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `course_id` INT(11) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `creator_id` INT(11) NOT NULL,
            `state` TINYINT(4) NOT NULL,
            `type` INT(11) UNSIGNED NOT NULL,
            `open_date` DATETIME NOT NULL,
            `change_date` DATETIME NOT NULL,
            `close_date` DATETIME,
            PRIMARY KEY(id),
            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
            FOREIGN KEY (`type`) REFERENCES request_type(id) ON DELETE CASCADE,
            FOREIGN KEY (creator_id) REFERENCES user(id)) $tbl_options");
    }

    if (!DBHelper::tableExists('request_field')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `request_field` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `type_id` INT(11) UNSIGNED NOT NULL,
            `name` MEDIUMTEXT NOT NULL,
            `description` MEDIUMTEXT NULL DEFAULT NULL,
            `datatype` INT(11) NOT NULL,
            `sortorder` INT(11) NOT NULL DEFAULT 0,
            `values` MEDIUMTEXT DEFAULT NULL,
            FOREIGN KEY (type_id) REFERENCES request_type(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('request_field_data')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `request_field_data` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `request_id` INT(11) UNSIGNED NOT NULL,
            `field_id` INT(11) UNSIGNED NOT NULL,
            `data` TEXT NOT NULL,
            FOREIGN KEY (field_id) REFERENCES request_field(id) ON DELETE CASCADE,
            UNIQUE KEY (`request_id`, `field_id`)) $tbl_options");
    }

    if (!DBHelper::tableExists('request_watcher')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS request_watcher (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `request_id` INT(11) UNSIGNED NOT NULL,
            `user_id` INT(11) NOT NULL,
            `type` TINYINT(4) NOT NULL,
            `notification` TINYINT(4) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY (request_id, user_id),
            FOREIGN KEY (request_id) REFERENCES request(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('request_action')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS request_action (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `request_id` INT(11) UNSIGNED NOT NULL,
            `user_id` INT(11) NOT NULL,
            `ts` DATETIME NOT NULL,
            `old_state` TINYINT(4) NOT NULL,
            `new_state` TINYINT(4) NOT NULL,
            `filename` VARCHAR(256),
            `real_filename` VARCHAR(255),
            `comment` TEXT,
            PRIMARY KEY(id),
            FOREIGN KEY (request_id) REFERENCES request(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");
    }

    //texts in homepage
    if (!DBHelper::tableExists('homepageTexts')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `homepageTexts` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
            `title` text NULL,
            `body` text NULL,
            `order` int(11) NOT NULL) $tbl_options");
    }

    if (!DBHelper::fieldExists('course','view_units')) {
        Database::get()->query("ALTER table course ADD `view_units` INT(11) NOT NULL DEFAULT 0");
    }

    if (!DBHelper::fieldExists('course','popular_course')) {
        Database::get()->query("ALTER table course ADD `popular_course` INT(11) NOT NULL DEFAULT 0");
    }

    $checkKeyTestimonials = get_config('dont_display_testimonials');
    if (is_null($checkKeyTestimonials)) {
        set_config('dont_display_testimonials', 0);
    }

    // themes
    $current_theme = get_config('theme');
    if (!$current_theme or $current_theme == 'default') {
        set_config('theme', 'modern');
    }
    if (!DBHelper::fieldExists('theme_options', 'version')) {
        Database::get()->query("ALTER TABLE theme_options ADD version TINYINT");
    }

    if (!DBHelper::fieldExists('group_properties', 'public_users_list')) {
        Database::get()->query("ALTER TABLE `group_properties`ADD `public_users_list` tinyint NOT NULL DEFAULT '1'");
    }

    if (!DBHelper::fieldExists('user','pic_public')) {
        Database::get()->query("ALTER TABLE user ADD pic_public TINYINT(1) NOT NULL DEFAULT 0 AFTER am_public");
    }

    if (!DBHelper::fieldExists('exercise', 'general_feedback')) {
        Database::get()->query("ALTER TABLE `exercise` ADD `general_feedback` TEXT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_520_ci' NULL");
    }

    if (!DBHelper::fieldExists('personal_calendar', 'end')) {
        Database::get()->query("ALTER TABLE `personal_calendar` ADD `end` DATETIME NOT NULL");
    }

    if (!DBHelper::fieldExists('admin_calendar', 'end')) {
        Database::get()->query("ALTER TABLE `admin_calendar` ADD `end` DATETIME NOT NULL");
    }

    if (!DBHelper::fieldExists('agenda', 'end')) {
        Database::get()->query("ALTER TABLE `agenda` ADD `end` DATETIME NOT NULL");
    }

    // learningPath
    if (!DBHelper::fieldExists('lp_user_module_progress', 'attempt')) {
        Database::get()->query("ALTER TABLE lp_user_module_progress ADD `attempt` int(11) NOT NULL DEFAULT 1");
    }
    if (!DBHelper::fieldExists('lp_user_module_progress', 'started')) {
        Database::get()->query("ALTER TABLE lp_user_module_progress ADD `started` datetime DEFAULT NULL");
    }
    if (!DBHelper::fieldExists('lp_user_module_progress', 'accessed')) {
        Database::get()->query("ALTER TABLE lp_user_module_progress ADD `accessed` datetime DEFAULT NULL");
    }


    // api token
    if (!DBHelper::tableExists('api_token')) {
        Database::get()->querySingle("CREATE TABLE `api_token` (
            `id` smallint NOT NULL AUTO_INCREMENT,
            `token` text CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `ip` varchar(45) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `enabled` tinyint NOT NULL,
            `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `expired` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)) $tbl_options");
    }

    // api token specific fields
    if (!DBHelper::fieldExists('api_token', 'created')) {
        Database::get()->query("ALTER TABLE `api_token` ADD `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
    if (!DBHelper::fieldExists('api_token', 'updated')) {
        Database::get()->query("ALTER TABLE `api_token` ADD `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
    if (!DBHelper::fieldExists('api_token', 'expired')) {
        Database::get()->query("ALTER TABLE `api_token` ADD `expired` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    if (!DBHelper::fieldExists('course_user', 'course_reviewer')) {
        Database::get()->query("ALTER TABLE course_user ADD `course_reviewer` TINYINT NOT NULL DEFAULT '0' AFTER editor");
    }

    if (!DBHelper::fieldExists('homepageTexts','type')) {
        Database::get()->query("ALTER table homepageTexts ADD `type` INT(11) NOT NULL DEFAULT 1");
    }

    $total_courses = get_config('total_courses');
    if (is_null($total_courses)) {
        set_config('total_courses', 0);
    }

    $visits_per_week = get_config('visits_per_week');
    if (is_null($visits_per_week)) {
        set_config('visits_per_week', 0);
    }

    $show_only_loginScreen = get_config('show_only_loginScreen');
    if (is_null($show_only_loginScreen)) {
        set_config('show_only_loginScreen', 0);
    }


    //priorities homepage
    if (!DBHelper::tableExists('homepagePriorities')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `homepagePriorities` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `title` text NULL,
                                        `order` int(11) NOT NULL,
                                        PRIMARY KEY (`id`)) $tbl_options");

        Database::get()->query("INSERT INTO `homepagePriorities` (`title`, `order`) VALUES 
                                            ('announcements', 0),
                                            ('popular_courses', 1),
                                            ('texts', 2),
                                            ('testimonials', 3),
                                            ('statistics', 4),
                                            ('open_courses', 5)");

    }



    //quick poll
    if (!DBHelper::fieldExists('poll', 'display_position')) {
        Database::get()->query("ALTER TABLE poll ADD `display_position` INT(1) NOT NULL DEFAULT 0 AFTER show_results");
    }

    if (!DBHelper::fieldExists('user', 'disable_course_registration')) {
        Database::get()->query("ALTER TABLE `user`ADD `disable_course_registration` tinyint NULL DEFAULT 0");
    }


    $show_modal_openCourses = get_config('show_modal_openCourses');
    if (is_null($show_modal_openCourses)) {
        set_config('show_modal_openCourses', 0);
    }

    if (!DBHelper::tableExists('course_invitation')) {
        Database::get()->query("CREATE TABLE `course_invitation` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `surname` varchar(255) NOT NULL DEFAULT '',
            `givenname` varchar(255) NOT NULL DEFAULT '',
            `email` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
            `identifier` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL,
            `expires_at` datetime DEFAULT NULL,
            `registered_at` datetime DEFAULT NULL,
            `course_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `identifier` (`identifier`),
            UNIQUE KEY `course_email` (`course_id`,`email`),
            CONSTRAINT `invitation_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE) $tbl_options");
    }

    $course_invitationn = get_config('course_invitation');
    if (is_null($course_invitationn)) {
        set_config('course_invitation', 0);
    }

    if (!DBHelper::fieldExists('group_properties', 'booking')) {
        Database::get()->query("ALTER TABLE `group_properties`ADD `booking` tinyint NOT NULL DEFAULT '0'");
    }

    if (!DBHelper::tableExists('tutor_availability_group')) {
        Database::get()->query("CREATE TABLE `tutor_availability_group` (
                                `id` int(11) NOT NULL auto_increment,
                                `user_id` int(11) NOT NULL default 0,
                                `group_id` int(11) NOT NULL default 0,
                                `start` DATETIME DEFAULT NULL,
                                `end` DATETIME DEFAULT NULL,
                                `lesson_id` int(11) NOT NULL DEFAULT 0,
                                PRIMARY KEY  (`id`)) $tbl_options");
    }

    if (!DBHelper::tableExists('booking')) {
        Database::get()->query("CREATE TABLE `booking` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `lesson_id` INT(11) NOT NULL,
                                `group_id` INT(11) NOT NULL DEFAULT 0,
                                `tutor_id` INT(11) NOT NULL,
                                `title` VARCHAR(255) NOT NULL,
                                `start` DATETIME NOT NULL,
                                `end` DATETIME NOT NULL,
                                `accepted` INT(11) NOT NULL DEFAULT 0,
                                PRIMARY KEY(id),
                                FOREIGN KEY (lesson_id) REFERENCES course(id) ON DELETE CASCADE,
                                FOREIGN KEY (tutor_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('booking_user')) {
        Database::get()->query("CREATE TABLE `booking_user` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `booking_id` INT(11) NOT NULL,
                                `simple_user_id` INT(11) NOT NULL,
                                PRIMARY KEY(id),
                                FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('date_availability_user')) {
        Database::get()->query("CREATE TABLE `date_availability_user` (
                                `id` int(11) NOT NULL auto_increment,
                                `user_id` int(11) NOT NULL default 0,
                                `start` DATETIME DEFAULT NULL,
                                `end` DATETIME DEFAULT NULL,
                                PRIMARY KEY  (`id`)) $tbl_options");
    }

    if (!DBHelper::tableExists('date_booking')) {
        Database::get()->query("CREATE TABLE `date_booking` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `teacher_id` INT(11) NOT NULL,
                                `title` VARCHAR(255) NOT NULL,
                                `start` DATETIME NOT NULL,
                                `end` DATETIME NOT NULL,
                                `accepted` INT(11) NOT NULL DEFAULT 0,
                                PRIMARY KEY(id),
                                FOREIGN KEY (teacher_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('date_booking_user')) {
        Database::get()->query("CREATE TABLE `date_booking_user` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `booking_id` INT(11) NOT NULL,
                                `student_id` INT(11) NOT NULL,
                                PRIMARY KEY(id),
                                FOREIGN KEY (booking_id) REFERENCES date_booking(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::fieldExists('homepagePriorities', 'visible')) {
        Database::get()->query("ALTER TABLE `homepagePriorities` ADD `visible` INT(11) NOT NULL DEFAULT 1");
    }

    if (!DBHelper::fieldExists('admin_announcement', 'important')) {
        Database::get()->query("ALTER TABLE `admin_announcement` ADD `important` INT(11) NOT NULL DEFAULT 0");
    }

    $show_collaboration = get_config('show_collaboration');
    if (is_null($show_collaboration)) {
        set_config('show_collaboration', 0);
    }

    $show_always_collaboration = get_config('show_always_collaboration');
    if (is_null($show_always_collaboration)) {
        set_config('show_always_collaboration', 0);
    }

    if (!DBHelper::fieldExists('course', 'is_collaborative')) {
        Database::get()->query("ALTER TABLE course ADD `is_collaborative` int(11) NOT NULL DEFAULT 0");
    }

    if (!DBHelper::tableExists('module_disable_collaboration')) {
        Database::get()->query("CREATE TABLE IF NOT EXISTS `module_disable_collaboration` (
                                        module_id int(11) NOT NULL PRIMARY KEY) $tbl_options");
    }

    if (!DBHelper::tableExists('mod_session')) {
        Database::get()->query("CREATE TABLE `mod_session` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `creator` INT(11) NOT NULL DEFAULT 0,
            `title` VARCHAR(255) NOT NULL DEFAULT '',
            `comments` MEDIUMTEXT,
            `type` VARCHAR(255) NOT NULL DEFAULT '',
            `type_remote` int(11) NOT NULL DEFAULT 0,
            `start` DATETIME DEFAULT NULL,
            `finish` DATETIME DEFAULT NULL,
            `visible` TINYINT(4),
            `public` TINYINT(4) NOT NULL DEFAULT 1,
            `order` INT(11) NOT NULL DEFAULT 0,
            `course_id` INT(11) NOT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('mod_session_users')) {
        Database::get()->query("CREATE TABLE `mod_session_users` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `session_id` INT(11) NOT NULL DEFAULT 0,
                                `participants` INT(11) NOT NULL DEFAULT 0,
                                PRIMARY KEY(id),
                                FOREIGN KEY (session_id) REFERENCES mod_session(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::tableExists('session_resources')) {
        Database::get()->query("CREATE TABLE `session_resources` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `session_id` INT(11) NOT NULL DEFAULT 0,
                                `title` VARCHAR(255) NOT NULL DEFAULT '',
                                `comments` MEDIUMTEXT,
                                `res_id` INT(11) NOT NULL DEFAULT 0,
                                `type` VARCHAR(255) NOT NULL DEFAULT '',
                                `visible` TINYINT(4),
                                `order` INT(11) NOT NULL DEFAULT 0,
                                `date` DATETIME NOT NULL,
                                PRIMARY KEY(id),
                                FOREIGN KEY (session_id) REFERENCES mod_session(id) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::fieldExists('mod_session', 'type_remote')) {
        Database::get()->query("ALTER TABLE mod_session ADD `type_remote` int(11) NOT NULL DEFAULT 0");
    }

    if (!DBHelper::fieldExists('badge', 'session_id')) {
        Database::get()->query("ALTER TABLE badge ADD `session_id` int(11) NOT NULL DEFAULT 0");
    }

    if (!DBHelper::tableExists('session_prerequisite')) {
        Database::get()->query("CREATE TABLE `session_prerequisite` (
                                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                `course_id` int(11) not null,
                                `session_id` int(11) not null,
                                `prerequisite_session` int(11) not null,
                                PRIMARY KEY (`id`)) $tbl_options");
    }

    if (!DBHelper::tableexists('course_certificate_template')) {
        Database::get()->query("CREATE TABLE `course_certificate_template` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `course_id` int(11) NOT NULL,
              `certificate_template_id` mediumint(8) NOT NULL,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
              FOREIGN KEY (`certificate_template_id`) REFERENCES `certificate_template` (`id`) ON DELETE CASCADE) $tbl_options");
    }
    if (!DBHelper::fieldExists('certificate_template', 'all_courses')) {
        Database::get()->query("ALTER TABLE `certificate_template` ADD `all_courses` tinyint(1) NOT NULL DEFAULT 1");
    }

    if (!DBHelper::tableexists('mod_session_completion')) {
        Database::get()->query("CREATE TABLE `mod_session_completion` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `course_id` int(11) NOT NULL,
              `session_id` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
              FOREIGN KEY (`session_id`) REFERENCES `mod_session` (`id`) ON DELETE CASCADE) $tbl_options");
    }

    if (!DBHelper::fieldExists('session_resources', 'doc_id')) {
        Database::get()->query("ALTER TABLE session_resources ADD `doc_id` int(11) NOT NULL DEFAULT 0");
    }

    if (!DBHelper::fieldExists('session_resources', 'from_user')) {
        Database::get()->query("ALTER TABLE session_resources ADD `from_user` int(11) NOT NULL DEFAULT 0");
    }

    if (!DBHelper::fieldExists('session_resources', 'is_completed')) {
        Database::get()->query("ALTER TABLE session_resources ADD `is_completed` int(11) NOT NULL DEFAULT 0");
    }

    if (!DBHelper::fieldExists('tc_session', 'id_session')) {
        Database::get()->query("ALTER TABLE tc_session ADD `id_session` int(11) NOT NULL DEFAULT 0");
    }

    if (!DBHelper::fieldExists('session_resources', 'deliverable_comments')) {
        Database::get()->query("ALTER TABLE session_resources ADD `deliverable_comments` TEXT DEFAULT NULL");
    }

    if (!DBHelper::fieldExists('session_resources', 'passage')) {
        Database::get()->query("ALTER TABLE session_resources ADD `passage` TEXT DEFAULT NULL");
    }

    if (!DBHelper::fieldExists('mod_session', 'consent')) {
        Database::get()->query("ALTER TABLE mod_session ADD `consent` int(11) NOT NULL DEFAULT 1");
    }
    
    if (!DBHelper::fieldExists('mod_session_users', 'is_accepted')) {
        Database::get()->query("ALTER TABLE mod_session_users ADD `is_accepted` int(11) NOT NULL DEFAULT 1");
    }
}


/**
 * @brief Create Indexes
 */

function create_indexes(): void
{

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
    DBHelper::indexExists('course_lti_publish', 'course_id_enabled') or
    Database::get()->query('CREATE INDEX course_id_enabled on course_lti_publish(course_id, enabled)');
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
    DBHelper::indexExists('lti_publish_lti2_context', 'consumerid') or
        Database::get()->query('CREATE INDEX consumerid on lti_publish_lti2_context(consumerid)');
    DBHelper::indexExists('lti_publish_lti2_nonce', 'consumerid') or
        Database::get()->query('CREATE INDEX consumerid on lti_publish_lti2_nonce(consumerid)');
    DBHelper::indexExists('lti_publish_lti2_resource_link', 'consumerid') or
        Database::get()->query('CREATE INDEX consumerid on lti_publish_lti2_resource_link(consumerid)');
    DBHelper::indexExists('lti_publish_lti2_resource_link', 'contextid') or
        Database::get()->query('CREATE INDEX contextid on lti_publish_lti2_resource_link(contextid)');
    DBHelper::indexExists('lti_publish_lti2_resource_link', 'primaryresourcelinkid') or
        Database::get()->query('CREATE INDEX primaryresourcelinkid on lti_publish_lti2_resource_link(primaryresourcelinkid)');
    DBHelper::indexExists('lti_publish_lti2_tool_proxy', 'consumerid') or
        Database::get()->query('CREATE INDEX consumerid on lti_publish_lti2_tool_proxy(consumerid)');
    DBHelper::indexExists('lti_publish_lti2_user_result', 'resourcelinkid') or
        Database::get()->query('CREATE INDEX resourcelinkid on lti_publish_lti2_user_result(resourcelinkid)');
    DBHelper::indexExists('course_lti_publish_user_enrolments', 'publish_id') or
        Database::get()->query('CREATE INDEX publish_id on course_lti_publish_user_enrolments(publish_id)');
    DBHelper::indexExists('course_lti_publish_user_enrolments', 'user_id') or
        Database::get()->query('CREATE INDEX user_id on course_lti_publish_user_enrolments(user_id)');
    DBHelper::indexExists('course_lti_enrol_users', 'publish_id') or
        Database::get()->query('CREATE INDEX publish_id on course_lti_enrol_users(publish_id)');
    DBHelper::indexExists('course_lti_enrol_users', 'user_id') or
        Database::get()->query('CREATE INDEX user_id on course_lti_enrol_users(user_id)');
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


function finalize_upgrade(): void
{
    // add new modules to courses by reinserting all modules
    Database::get()->queryFunc("SELECT id, code FROM course", function ($course) {
        global $modules;
        $modules_count = count($modules);
        $placeholders = implode(', ', array_fill(0, $modules_count, '(?d, ?d, ?d)'));
        $values = array();
        foreach($modules as $mid => $minfo) {
            $values[] = array($mid, 0, $course->id);
        }
        Database::get()->query("INSERT IGNORE INTO course_module (module_id, visible, course_id) VALUES " .
            $placeholders, $values);
    });
    // delete deprecated course modules
    Database::get()->query("DELETE FROM course_module WHERE module_id = " . MODULE_ID_DESCRIPTION);
    Database::get()->query("DELETE FROM course_module WHERE module_id = " . MODULE_ID_LTI_CONSUMER);

    // Ensure that all stored procedures about hierarchy are up and running!
    refreshHierarchyProcedures();

    // create appropriate indices
    create_indexes();

    // Import new themes
    importThemes();
    if (!get_config('theme_options_id')) {
        // set_config('theme_options_id', Database::get()->querySingle('SELECT id FROM theme_options WHERE name = ?s', 'Open eClass 2020 - Default')->id);
        set_config('theme_options_id', 0);
    }

    set_config('version', ECLASS_VERSION);
    set_config('upgrade_begin', '');
}


/**
  * @brief Rename user profile image files to unpredictable names
  */
function encode_user_profile_pics(): void
{
    Database::get()->queryFunc('SELECT id FROM user WHERE has_icon = 1',
        function ($user) {
            $base = "courses/userimg/{$user->id}_";
            if (file_exists($base . IMAGESIZE_LARGE . '.jpg')) {
                $hash = profile_image_hash($user->id);
                rename($base . IMAGESIZE_LARGE . '.jpg', $base . $hash . '_' . IMAGESIZE_LARGE . '.jpg');
                rename($base . IMAGESIZE_SMALL . '.jpg', $base . $hash . '_' . IMAGESIZE_SMALL . '.jpg');
            }
        });
}


/**
 * @brief change db encoding to utf8mb4
 */
function convert_db_encoding_to_utf8mb4(): void
{
    global $mysqlMainDb, $step;
    if ($step == 3) {
        // convert database
        Database::core()->query("ALTER DATABASE `$mysqlMainDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");

        // convert tables
        $r = Database::core()->queryArray("SHOW TABLES FROM `$mysqlMainDb`");
        foreach ($r as $tables) {
            foreach ($tables as $table_name) {
                Database::get()->query("ALTER TABLE `$table_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
            }
        }
        break_on_step();
    }

    // convert table columns
    $queries = [
        "ALTER TABLE `abuse_report` CHANGE rtype rtype varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `abuse_report` CHANGE reason reason varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `abuse_report` CHANGE message message text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `activity_content` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `activity_heading` CHANGE heading heading text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `admin_announcement` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `admin_announcement` CHANGE body body text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `admin_calendar` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `admin_calendar` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `admin_calendar` CHANGE recursion_period recursion_period varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `agenda` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `agenda` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `agenda` CHANGE duration duration varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `agenda` CHANGE recursion_period recursion_period varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `analytics` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `analytics` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `announcement` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `announcement` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment` CHANGE active active TINYINT NOT NULL DEFAULT 1",
        "ALTER TABLE `assignment` CHANGE group_submissions group_submissions TINYINT NOT NULL DEFAULT 0",
        "ALTER TABLE `assignment` CHANGE assign_to_specific assign_to_specific TINYINT NOT NULL DEFAULT 0",
        "ALTER TABLE `assignment` CHANGE file_path file_path varchar(255) CHARACTER SET utf8mb4",
        "ALTER TABLE `assignment` CHANGE file_name file_name varchar(255) CHARACTER SET utf8mb4",
        "ALTER TABLE `assignment` CHANGE auto_judge_scenarios auto_judge_scenarios text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment` CHANGE ip_lock ip_lock text CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `assignment` CHANGE password_lock password_lock varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin",
        "ALTER TABLE `assignment` CHANGE tii_exclude_type tii_exclude_type varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_grading_review` CHANGE file_name file_name varchar(255) CHARACTER SET utf8mb4",
        "ALTER TABLE `assignment_grading_review` CHANGE submission_text submission_text mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_grading_review` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_grading_review` CHANGE rubric_scales rubric_scales text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_submit` CHANGE submission_ip submission_ip varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_submit` CHANGE file_name file_name varchar(255) CHARACTER SET utf8mb4",
        "ALTER TABLE `assignment_submit` CHANGE submission_text submission_text mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_submit` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_submit` CHANGE grade_rubric grade_rubric text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_submit` CHANGE grade_comments grade_comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `assignment_submit` CHANGE grade_comments_filename grade_comments_filename varchar(255) CHARACTER SET utf8mb4",
        "ALTER TABLE `assignment_submit` CHANGE grade_submission_ip grade_submission_ip varchar(45) CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `assignment_submit` CHANGE auto_judge_scenarios_output auto_judge_scenarios_output text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `attendance` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `attendance_activities` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `attendance_activities` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `attendance_book` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `auth` CHANGE auth_name auth_name varchar(50) CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `auth` CHANGE auth_settings auth_settings text CHARACTER SET utf8mb4",
        "ALTER TABLE `auth` CHANGE auth_instructions auth_instructions text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `auth` CHANGE auth_title auth_title text CHARACTER SET utf8mb4",
        "ALTER TABLE `badge` CHANGE issuer issuer varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `badge` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `badge` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `badge` CHANGE message message text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `badge_criterion` CHANGE activity_type activity_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `badge_criterion` CHANGE operator operator varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `badge_icon` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `badge_icon` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `badge_icon` CHANGE filename filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `blog_post` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `blog_post` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `category` CHANGE name name text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `category_value` CHANGE name name text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate` CHANGE issuer issuer varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate` CHANGE message message text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate_criterion` CHANGE activity_type activity_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate_criterion` CHANGE operator operator varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate_template` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate_template` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate_template` CHANGE filename filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certificate_template` CHANGE orientation orientation varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certified_users` CHANGE course_title course_title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certified_users` CHANGE cert_title cert_title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certified_users` CHANGE cert_message cert_message text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certified_users` CHANGE cert_issuer cert_issuer varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certified_users` CHANGE user_fullname user_fullname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `certified_users` CHANGE identifier identifier varchar(255) CHARACTER SET utf8mb4",
        "ALTER TABLE `colmooc_user_session` CHANGE session_id session_id text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `colmooc_user_session` CHANGE session_token session_token text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `comments` CHANGE rtype rtype varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `comments` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `conference` CHANGE conf_title conf_title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `conference` CHANGE conf_description conf_description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `conference` CHANGE user_id user_id varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `conference` CHANGE group_id group_id varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `conference` CHANGE `status` `status` enum('active','inactive') CHARACTER SET ascii DEFAULT 'active'",
        "ALTER TABLE `config` CHANGE `key` `key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `config` CHANGE `value` `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course` CHANGE code code varchar(20) CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `course` CHANGE title title varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course` CHANGE keywords keywords text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course` CHANGE prof_names prof_names varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course` CHANGE public_code public_code varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course` CHANGE password password varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin",
        "ALTER TABLE `course` CHANGE view_type view_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course` CHANGE description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course` CHANGE course_image course_image varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course_description` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course_description` CHANGE comments comments mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course_description_type` CHANGE title title mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course_description_type` CHANGE icon icon varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course_units` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course_units` CHANGE comments comments mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course_user_request` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `course_learning_objectives` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `cron_params` CHANGE name name varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `custom_profile_fields` CHANGE shortname shortname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `custom_profile_fields` CHANGE name name mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `custom_profile_fields` CHANGE description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `custom_profile_fields` CHANGE datatype datatype varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `custom_profile_fields` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `custom_profile_fields_category` CHANGE name name mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `custom_profile_fields_data` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `custom_profile_fields_data_pending` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `document` CHANGE filename filename varchar(255) CHARACTER SET utf8mb4",
        "ALTER TABLE `document` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `document` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `document` CHANGE creator creator text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `document` CHANGE subject subject text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `document` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `document` CHANGE author author varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `document` CHANGE format format varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `dropbox_attachment` CHANGE filename filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `dropbox_attachment` CHANGE real_filename real_filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `dropbox_msg` CHANGE subject subject text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `dropbox_msg` CHANGE body body longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `ebook` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `ebook_section` CHANGE public_id public_id varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `ebook_section` CHANGE file file varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `ebook_section` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `ebook_subsection` CHANGE public_id public_id varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `ebook_subsection` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_fields` CHANGE shortname shortname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_fields` CHANGE name name mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_fields` CHANGE description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_fields` CHANGE datatype datatype varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_fields` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_fields_category` CHANGE name name mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_fields_data` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_resource` CHANGE resource_type resource_type varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_resource` CHANGE course_title course_title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `eportfolio_resource` CHANGE data data text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise` CHANGE ip_lock ip_lock text CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `exercise` CHANGE password_lock password_lock varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin",
        "ALTER TABLE `exercise_answer` CHANGE answer answer text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise_answer` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise_answer_record` CHANGE answer answer text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise_question` CHANGE question question text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise_question` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise_question_cats` CHANGE question_cat_name question_cat_name varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `exercise_with_questions` CHANGE random_criteria random_criteria text CHARACTER SET utf8mb4",
        "ALTER TABLE `faq` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `faq` CHANGE body body text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `forum` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `forum` CHANGE `desc` `desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `forum_category` CHANGE cat_title cat_title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `forum_post` CHANGE post_text post_text mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `forum_post` CHANGE poster_ip poster_ip varchar(45) CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `forum_post` CHANGE topic_filename topic_filename varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `forum_topic` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `glossary` CHANGE term term varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `glossary` CHANGE definition definition text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `glossary` CHANGE url url text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `glossary` CHANGE notes notes text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `glossary_category` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `glossary_category` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `gradebook` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `gradebook_activities` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `gradebook_activities` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `gradebook_book` CHANGE comments comments text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `grading_scale` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `grading_scale` CHANGE scales scales text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `group` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `group` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `group` CHANGE secret_directory secret_directory varchar(30) CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `group_category` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `group_category` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `group_members` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_content` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_content` CHANGE params params longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_content_dependency` CHANGE dependency_type dependency_type varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE machine_name machine_name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE embed_types embed_types varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE preloaded_js preloaded_js longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE preloaded_css preloaded_css longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE droplibrary_css droplibrary_css longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE semantics semantics longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE add_to add_to longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE metadata_settings metadata_settings longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE tutorial tutorial longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library` CHANGE example example longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library_dependency` CHANGE dependency_type dependency_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `h5p_library_translation` CHANGE language_json language_json text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `hierarchy` CHANGE code code varchar(20) CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `hierarchy` CHANGE name name text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `hierarchy` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `idx_queue_async` CHANGE request_type request_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `idx_queue_async` CHANGE resource_type resource_type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `link` CHANGE url url text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `link` CHANGE title title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `link` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `link_category` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `link_category` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `log` CHANGE details details text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `log_archive` CHANGE details details text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `loginout` CHANGE action action enum('LOGIN','LOGOUT') CHARACTER SET ascii COLLATE ascii_bin NOT NULL default 'LOGIN'",
        "ALTER TABLE `lp_asset` CHANGE path path varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_asset` CHANGE comment comment varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_learnPath` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_learnPath` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_learnPath` CHANGE `lock` `lock` enum('OPEN','CLOSE') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'OPEN'",
        "ALTER TABLE `lp_module` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_module` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_module` CHANGE launch_data launch_data text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_module` CHANGE `accessibility` `accessibility` enum('PRIVATE','PUBLIC') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PRIVATE'",
        "ALTER TABLE `lp_module` CHANGE `contentType` `contentType` enum('CLARODOC', 'DOCUMENT', 'EXERCISE', 'HANDMADE',
                                                                        'SCORM', 'SCORM_ASSET', 'LABEL', 'COURSE_DESCRIPTION', 'LINK',
                                                                        'MEDIA','MEDIALINK') CHARACTER SET ascii COLLATE ascii_bin NOT NULL",
        "ALTER TABLE `lp_rel_learnPath_module` CHANGE specificComment specificComment text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_rel_learnPath_module` CHANGE `lock` `lock` enum('OPEN','CLOSE') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'OPEN'",
        "ALTER TABLE `lp_user_module_progress` CHANGE lesson_location lesson_location varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_user_module_progress` CHANGE total_time total_time varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_user_module_progress` CHANGE session_time session_time varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_user_module_progress` CHANGE suspend_data suspend_data text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lp_user_module_progress` CHANGE `lesson_status` `lesson_status` enum('NOT ATTEMPTED', 'PASSED', 'FAILED', 'COMPLETED',
                                                                        'BROWSED', 'INCOMPLETE', 'UNKNOWN') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'NOT ATTEMPTED'",
        "ALTER TABLE `lp_user_module_progress` CHANGE `entry` `entry` enum('AB-INITIO', 'RESUME', '') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'AB-INITIO'",
        "ALTER TABLE `lp_user_module_progress` CHANGE `credit` `credit` enum('CREDIT','NO-CREDIT') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'NO-CREDIT'",
        "ALTER TABLE `lti_apps` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `lti_apps` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `monthly_summary` CHANGE month month varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `monthly_summary` CHANGE details details mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `note` CHANGE title title varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `note` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `note` CHANGE `reference_obj_type` `reference_obj_type` enum('course','personalevent','user',
                                                                'course_ebook','course_event','course_assignment',
                                                                'course_document','course_link','course_exercise',
                                                                'course_learningpath','course_video','course_videolink') CHARACTER SET ascii COLLATE ascii_bin default NULL",
        "ALTER TABLE `oai_metadata` CHANGE value value text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `personal_calendar` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `personal_calendar` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `personal_calendar` CHANGE recursion_period recursion_period varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `personal_calendar` CHANGE reference_obj_type reference_obj_type ENUM('course', 'personalevent', 'user',
                                                            'course_ebook', 'course_event', 'course_assignment', 'course_document',
                                                            'course_link', 'course_exercise', 'course_learningpath', 'course_video',
                                                            'course_videolink') CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL",
        "ALTER TABLE `personal_calendar_settings` CHANGE personal_color personal_color varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `personal_calendar_settings` CHANGE course_color course_color varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `personal_calendar_settings` CHANGE deadline_color deadline_color varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `personal_calendar_settings` CHANGE admin_color admin_color varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `personal_calendar_settings` CHANGE `view_type` `view_type` enum('day','month','week') CHARACTER SET ascii COLLATE ascii_bin DEFAULT 'month'",
        "ALTER TABLE `poll` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `poll` CHANGE description description mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `poll` CHANGE end_message end_message mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `poll_answer_record` CHANGE answer_text answer_text text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `poll_question` CHANGE question_text question_text text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `poll_question_answer` CHANGE answer_text answer_text text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `poll_user_record` CHANGE email email varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `poll_user_record` CHANGE verification_code verification_code varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `rating` CHANGE rtype rtype varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `rating` CHANGE widget widget varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `rating` CHANGE rating_source rating_source varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `rating_cache` CHANGE rtype rtype varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `rating_cache` CHANGE tag tag varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `recyclebin` CHANGE tablename tablename varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `recyclebin` CHANGE entrydata entrydata varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `rubric` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `rubric` CHANGE scales scales text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `rubric` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tag` CHANGE name name varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_attendance` CHANGE meetingid meetingid varchar(42) CHARACTER SET ascii COLLATE ascii_bin NOT NULL",
        "ALTER TABLE `tc_attendance` CHANGE bbbuserid bbbuserid varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_log` CHANGE meetingid meetingid varchar(42) CHARACTER SET ascii COLLATE ascii_bin NOT NULL",
        "ALTER TABLE `tc_log` CHANGE bbbuserid bbbuserid varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_log` CHANGE fullName fullName varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_log` CHANGE type type varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_servers` CHANGE webapp webapp varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_servers` CHANGE `enabled` `enabled` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL",
        "ALTER TABLE `tc_servers` CHANGE `enable_recordings` `enable_recordings` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL",
        "ALTER TABLE `tc_session` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_session` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_session` CHANGE external_users external_users text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_session` CHANGE participants participants varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_session` CHANGE options options text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `tc_session` CHANGE meeting_id meeting_id varchar(42) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL",
        "ALTER TABLE `tc_session` CHANGE `record` `record` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT 'false'",
        "ALTER TABLE `theme_options` CHANGE name name varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `theme_options` CHANGE styles styles longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `unit_resources` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `unit_resources` CHANGE comments comments mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user` CHANGE surname surname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user` CHANGE givenname givenname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user` CHANGE username username varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin",
        "ALTER TABLE `user` CHANGE email email varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user` CHANGE parent_email parent_email varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user` CHANGE phone phone varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user` CHANGE am am varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user` CHANGE whitelist whitelist text CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `user_ext_uid` CHANGE uid uid varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request` CHANGE givenname givenname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request` CHANGE surname surname varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request` CHANGE username username varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin",
        "ALTER TABLE `user_request` CHANGE password password varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request` CHANGE email email varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request` CHANGE phone phone varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request` CHANGE am am varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request` CHANGE comment comment text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request` CHANGE request_ip request_ip varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `user_request_ext_uid` CHANGE uid uid varchar(64) CHARACTER SET ascii COLLATE ascii_bin",
        "ALTER TABLE `video` CHANGE url url varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `video` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `video` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `video` CHANGE creator creator varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `video` CHANGE publisher publisher varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `video_category` CHANGE name name varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `video_category` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `videolink` CHANGE url url varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `videolink` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `videolink` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `videolink` CHANGE creator creator varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `videolink` CHANGE publisher publisher varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wiki_acls` CHANGE `value` `value` ENUM('false','true') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'false'",
        "ALTER TABLE `wall_post` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wall_post` CHANGE extvideo extvideo varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wall_post_resources` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wiki_locks` CHANGE ptitle ptitle varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wiki_pages` CHANGE title title varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wiki_pages_content` CHANGE content content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wiki_pages_content` CHANGE changelog changelog varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wiki_properties` CHANGE title title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
        "ALTER TABLE `wiki_properties` CHANGE description description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci",
    ];
    foreach ($queries as $index => $query) {
        if ($index + 4 == $step) {
            $processes = array_filter(Database::get()->queryArray('SHOW FULL PROCESSLIST'),
                function ($item) {
                    return $item->Info && strpos($item->Info, 'ALTER TABLE') !== false;
                });
            if ($processes) {
                message($processes[0]->Info . ' - ' . $processes[0]->Time, 'wait');
            }
            Database::get()->query($query);
            break_on_step();
        }
    }
}

function update_upload_whitelists() {
    $default_student_upload_whitelist = ['pdf', 'ps', 'eps', 'tex', 'latex',
        'dvi', 'texinfo', 'texi', 'zip', 'rar', 'tar', 'bz2', 'gz', '7z', 'xz',
        'lha', 'lzh', 'z', 'doc', 'docx', 'odt', 'ott', 'sxw', 'stw',
        'fodt', 'txt', 'rtf', 'dot', 'mcw', 'wps', 'xls', 'xlsx', 'xlt', 'ods',
        'ots', 'sxc', 'stc', 'fods', 'uos', 'csv', 'ppt', 'pps',
        'pot', 'pptx', 'ppsx', 'odp', 'otp', 'sxi', 'sti', 'fodp', 'uop',
        'potm', 'odg', 'otg', 'sxd', 'std', 'fodg', 'odb', 'mdb', 'ttf', 'otf',
        'jpg', 'jpeg', 'jxl', 'png', 'gif', 'bmp', 'tif', 'tiff', 'psd', 'dia',
        'ppm', 'xbm', 'xpm', 'ico', 'avi', 'asf', 'asx', 'wm', 'wmv', 'wma',
        'dv', 'mov', 'moov', 'movie', 'mp4', 'mpg', 'mpeg', '3gp', '3g2',
        'm2v', 'aac', 'm4a', 'flv', 'f4v', 'm4v', 'mp3', 'swf', 'webm', 'ogv',
        'ogg', 'mid', 'midi', 'aif', 'rm', 'rpm', 'ram', 'wav', 'mp2', 'm3u',
        'qt', 'vsd', 'vss', 'vst', 'cg3', 'ggb', 'psc', 'dir', 'dcr', 'sb',
        'sb2', 'sb3', 'sbx', 'kodu', 'html', 'htm', 'wlmp', 'mswmm',
        'apk', 'py', 'ev3', 'psg', 'glo', 'gsp', 'xml', 'a3p', 'ypr',
        'mw2', 'dtd', 'aia', 'hex', 'mscz', 'pages', 'heic', 'piv', 'stk',
        'pptm', 'gfar', 'lab', 'lmsp', 'qrs', 'cpp', 'c', 'h', 'java', 'm', 'opus', 'mka'];
    $default_teacher_upload_whitelist = ['html', 'htm', 'svg', 'js',
        'css', 'xml', 'xsl', 'tcl', 'sgml', 'sgm', 'ini', 'ds_store', 'dir',
        'mom', 'gsp', 'kid', 'apk', 'woff', 'xsd', 'cur', 'lxf', 'a3p',
        'ypr', 'mw2', 'h5p', 'dtd', 'xsd', 'woff2', 'ppsm', 'jqz', 'jm',
        'data', 'jar', 'mkv'];

    if (get_config('student_upload_whitelist')) { // upgrade
        // add default whitelists to current whitelists, remove duplicates
        $student_upload_whitelist = array_unique(array_merge($default_student_upload_whitelist,
            explode(',', preg_replace('/\s+/', '', get_config('student_upload_whitelist')))));
        $teacher_upload_whitelist = array_unique(array_merge($default_teacher_upload_whitelist,
            explode(',', preg_replace('/\s+/', '', get_config('student_upload_whitelist')))));

        // restrict web files to teachers, remove from student whitelist
        $student_upload_whitelist = array_diff($student_upload_whitelist, ['svg', 'html', 'htm', 'js', 'css']);

        // remove student whitelist extensions from teacher whitelist
        $teacher_upload_whitelist = array_diff($teacher_upload_whitelist, $student_upload_whitelist);
        set_config('student_upload_whitelist', implode(', ', $student_upload_whitelist));
        set_config('teacher_upload_whitelist', implode(', ', $teacher_upload_whitelist));
    } else { // install
        set_config('student_upload_whitelist', implode(', ', $default_student_upload_whitelist));
        set_config('teacher_upload_whitelist', implode(', ', $default_teacher_upload_whitelist));
    }

}

/**
 * @return void
 */
function update_minedu_deps()
{

    $value_string = '';
    $i = 0;

    Database::get()->query("DELETE FROM minedu_departments");
    $file = IOFactory::load('upgrade/minedu_departments.xlsx');
    $sheet = $file->getActiveSheet();

    foreach ($sheet->getRowIterator() as $row) {
        $i++;
        if ($i == 1) { // first row contains field names
            continue;
        } else {
            $cellIterator = $row->getCellIterator();
            foreach ($cellIterator as $cell) {
                $cell_value = $cell->getValue();
                $value_string .= "'" . $cell_value . "'" . ",";
            }
            $db_string = "INSERT INTO minedu_departments(MineduID, Institution, School, Department, Comment) VALUES (" . rtrim($value_string, ',') .")";
            Database::get()->query($db_string);
            $value_string = '';
        }
    }
}

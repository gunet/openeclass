<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
use Hautelook\Phpass\PasswordHash;


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
 * @brief import new theme
 */
function importThemes($themes = null) {
    global $webDir;
    if (!isset($themes) || isset($themes) && !empty($themes)) {
        $themesDir = "$webDir/template/$_SESSION[theme]/themes";
        if(!is_dir("$webDir/courses/theme_data")) make_dir("$webDir/courses/theme_data");
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

/**
 * @brier install new theme
 * @param $themesDir
 * @param $file_name
 */
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


/**
 * @brief install ready to use certificate templates 
 */
function installCertTemplates($root_dir) {
        
    
    $cert_default_dir = $root_dir . "/template/default/img/game";
    chdir($cert_default_dir);
    foreach (glob("*.zip") as $zipfile) {
        if (copy("$zipfile", "$root_dir" . CERT_TEMPLATE_PATH . "$zipfile")) {
            $archive = new PclZip("$root_dir" . CERT_TEMPLATE_PATH . "$zipfile");
            if ($archive->extract(PCLZIP_OPT_PATH , "$root_dir" . CERT_TEMPLATE_PATH)) {
                unlink("$root_dir" . CERT_TEMPLATE_PATH . "$zipfile");                
            } else {
                die("Error : ".$archive->errorInfo(true));
            }
        }
    }
    Database::get()->query("INSERT INTO certificate_template(name, description, filename, orientation) VALUES ('Πρότυπο 1', '', 'certificate1.html', 'L')");
    Database::get()->query("INSERT INTO certificate_template(name, description, filename, orientation) VALUES ('Πρότυπο 2', '', 'certificate2.html', 'L')");
    Database::get()->query("INSERT INTO certificate_template(name, description, filename, orientation) VALUES ('Πρότυπο 3', '', 'certificate3.html', 'P')");
    Database::get()->query("INSERT INTO certificate_template(name, description, filename, orientation) VALUES ('Πρότυπο 4', '', 'certificate4.html', 'L')");    
    Database::get()->query("INSERT INTO certificate_template(name, description, filename, orientation) VALUES ('Πρότυπο 5', '', 'certificate5.html', 'L')");    
}

/**
 * install ready to use badge icons 
 */
function installBadgeIcons($root_dir) {
        
    
    $cert_default_dir = $root_dir . "/template/default/img/game";
    chdir($cert_default_dir);
    foreach (glob("*.png") as $icon) {
        if (!copy("$icon", "$root_dir" . BADGE_TEMPLATE_PATH . "$icon")) {
            die("Error copying badge icon!");
        }
        $iconname = substr($icon, 0, -4);
        Database::get()->query("INSERT INTO badge_icon(name, description, filename) VALUES (?s, '', ?s)", $iconname, $icon);
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

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
 * @file install_db.php
 * @brief installation data base queries
 */

require_once 'modules/db/foreignkeys.php';

if (!defined('ECLASS_VERSION')) {
    exit;
}

set_time_limit(0);

// create eclass database
Database::core()->query("CREATE DATABASE IF NOT EXISTS `$mysqlMainDb` CHARACTER SET utf8mb4");

$db = Database::get();

// drop old tables if they exist
$db->query("DROP TABLE IF EXISTS admin");
$db->query("DROP TABLE IF EXISTS admin_announcements");
$db->query("DROP TABLE IF EXISTS agenda");
$db->query("DROP TABLE IF EXISTS announcements");
$db->query("DROP TABLE IF EXISTS auth");
$db->query("DROP TABLE IF EXISTS course");
$db->query("DROP TABLE IF EXISTS course_user");
$db->query("DROP TABLE IF EXISTS course_description");
$db->query("DROP TABLE IF EXISTS course_review");
$db->query("DROP TABLE IF EXISTS faculte");
$db->query("DROP TABLE IF EXISTS institution");
$db->query("DROP TABLE IF EXISTS loginout");
$db->query("DROP TABLE IF EXISTS loginout_summary");
$db->query("DROP TABLE IF EXISTS monthly_summary");
$db->query("DROP TABLE IF EXISTS user_request");
$db->query("DROP TABLE IF EXISTS prof_request");
$db->query("DROP TABLE IF EXISTS user");
$db->query("DROP TABLE IF EXISTS oai_record");
$db->query("DROP TABLE IF EXISTS oai_metadata");
$db->query("DROP TABLE IF EXISTS tc_servers");
$db->query("DROP TABLE IF EXISTS tc_session");

$tbl_options = 'DEFAULT CHARACTER SET=utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
// ********************************************
// create tables
// ********************************************

// flipped classroom course type
$db->query("CREATE TABLE IF NOT EXISTS `course_activities` (
    `id` int(11) NOT NULL auto_increment,
    `activity_id` varchar(4) NOT NULL,
    `activity_type` tinyint(4) NOT NULL,
    `visible` int(11) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `module_id` int(11) NOT NULL,
    PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS`course_units_activities` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_code` VARCHAR(20) NOT NULL,
    `activity_id` VARCHAR(5) NOT NULL,
    `unit_id` INT NOT NULL,
    `tool_ids` TEXT NOT NULL,
    `activity_type` INT NOT NULL,
    `visible` INT NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `course_class_info` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `student_number` VARCHAR(50) NOT NULL,
    `lessons_number` INT NOT NULL,
    `lesson_hours` INT NOT NULL,
    `home_hours` INT NOT NULL,
    `total_hours` INT NOT NULL,
    `course_code` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `course_learning_objectives` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_code` VARCHAR(20) NOT NULL,
    `title` TEXT NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");
// end of flipped classroom

$db->query("CREATE TABLE IF NOT EXISTS `course_module` (
  `id` int(11) NOT NULL auto_increment,
  `module_id` int(11) NOT NULL,
  `visible` tinyint(4) NOT NULL,
  `course_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `module_course` (`module_id`,`course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS module_disable (
    module_id int(11) NOT NULL PRIMARY KEY) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS module_disable_collaboration (
    module_id int(11) NOT NULL PRIMARY KEY) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default 0,
  `course_id` int(11) NOT NULL default 0,
  `module_id` int(11) NOT NULL default 0,
  `details` text NOT NULL,
  `action_type` int(11) NOT NULL default 0,
  `ts` datetime NOT NULL,
  `ip` varchar(45) NOT NULL default 0,
  PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `log_archive` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default 0,
  `course_id` int(11) NOT NULL default 0,
  `module_id` int(11) NOT NULL default 0,
  `details` text NOT NULL,
  `action_type` int(11) NOT NULL default 0,
  `ts` datetime NOT NULL,
  `ip` varchar(45) NOT NULL default 0,
  PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE `announcement` (
    `id` INT(11) NOT NULL auto_increment,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `content` MEDIUMTEXT,
    `date` DATETIME NOT NULL,
    `course_id` INT(11) NOT NULL DEFAULT 0,
    `order` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `visible` TINYINT(4) NOT NULL DEFAULT 0,
    `start_display` DATETIME DEFAULT NULL,
    `stop_display` DATETIME DEFAULT NULL,
    PRIMARY KEY (id)) $tbl_options");

$db->query("CREATE TABLE `admin_announcement` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `body` TEXT,
    `date` DATETIME NOT NULL,
    `begin` DATETIME DEFAULT NULL,
    `end` DATETIME DEFAULT NULL,
    `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
    `order` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `important` INT(11) NOT NULL DEFAULT 0,
    `visible` TINYINT(4)) $tbl_options");

$db->query("CREATE TABLE `agenda` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `start` DATETIME NOT NULL,
    `end` DATETIME DEFAULT NULL,
    `duration` VARCHAR(20) NOT NULL,
    `visible` TINYINT(4),
    `recursion_period` varchar(30) DEFAULT NULL,
    `recursion_end` date DEFAULT NULL,
    `source_event_id` int(11) DEFAULT NULL)
    $tbl_options");

$db->query("CREATE TABLE `course` (
  `id` INT(11) NOT NULL auto_increment,
  `uuid` VARCHAR(40) NOT NULL DEFAULT 0,
  `code` VARCHAR(20) NOT NULL,
  `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `keywords` TEXT NOT NULL,
  `course_license` TINYINT(4) NOT NULL DEFAULT 0,
  `visible` TINYINT(4) NOT NULL,
  `prof_names` VARCHAR(255) NOT NULL DEFAULT '',
  `public_code` VARCHAR(100) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL,
  `updated` DATETIME NULL,
  `doc_quota` FLOAT NOT NULL default '104857600',
  `video_quota` FLOAT NOT NULL default '104857600',
  `group_quota` FLOAT NOT NULL default '104857600',
  `dropbox_quota` FLOAT NOT NULL default '104857600',
  `password` VARCHAR(255) NOT NULL DEFAULT '',
  `glossary_expand` BOOL NOT NULL DEFAULT 0,
  `glossary_index` BOOL NOT NULL DEFAULT 1,
  `view_type` VARCHAR(255) NOT NULL DEFAULT 'units',
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `description` MEDIUMTEXT DEFAULT NULL,
  `home_layout` TINYINT(1) NOT NULL DEFAULT 1,
  `course_image` VARCHAR(400) NULL,
  `flipped_flag` int(11) NOT NULL DEFAULT 0,
  `lectures_model` int(11) NOT NULL DEFAULT 0,
  `view_units` INT(11) NOT NULL DEFAULT 0,
  `popular_course` INT(11) NOT NULL DEFAULT 0,
  `is_collaborative` INT(11) NOT NULL DEFAULT 0,
  `daily_access_limit` INT NULL,
  PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE `course_user` (
      `course_id` INT(11) NOT NULL DEFAULT 0,
      `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
      `status` TINYINT(4) NOT NULL DEFAULT 0,
      `tutor` INT(11) NOT NULL DEFAULT 0,
      `editor` INT(11) NOT NULL DEFAULT 0,
      `course_reviewer` TINYINT(4) NOT NULL DEFAULT 0,
      `reviewer` INT(11) NOT NULL DEFAULT 0,
      `reg_date` DATETIME NOT NULL,
      `receive_mail` BOOL NOT NULL DEFAULT 1,
      `document_timestamp` datetime NOT NULL,
      `favorite` datetime DEFAULT NULL,
      `can_view_course` TINYINT(1) NOT NULL DEFAULT 1,
      PRIMARY KEY (course_id, user_id)) $tbl_options");

$db->query("CREATE TABLE `course_user_request` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL,
    `course_id` int(11) NOT NULL,
    `comments` text,
    `status` int(11) NOT NULL,
    `ts` datetime NOT NULL,
    PRIMARY KEY (`id`))  $tbl_options");

$db->query("CREATE TABLE `course_description_type` (
    `id` smallint(6) NOT NULL AUTO_INCREMENT,
    `title` mediumtext,
    `syllabus` tinyint(1) DEFAULT 0,
    `objectives` tinyint(1) DEFAULT 0,
    `bibliography` tinyint(1) DEFAULT 0,
    `teaching_method` tinyint(1) DEFAULT 0,
    `assessment_method` tinyint(1) DEFAULT 0,
    `prerequisites` tinyint(1) DEFAULT 0,
    `featured_books` tinyint(1) DEFAULT 0,
    `instructors` tinyint(1) DEFAULT 0,
    `target_group` tinyint(1) DEFAULT 0,
    `active` tinyint(1) DEFAULT 1,
    `order` int(11) NOT NULL,
    `icon` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("INSERT INTO `course_description_type` (`id`, `title`, `syllabus`, `order`, `icon`) VALUES (1, 'a:2:{s:2:\"el\";s:41:\"Περιεχόμενο μαθήματος\";s:2:\"en\";s:15:\"Course Syllabus\";}', 1, 1, '0.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `objectives`, `order`, `icon`) VALUES (2, 'a:2:{s:2:\"el\";s:33:\"Μαθησιακοί στόχοι\";s:2:\"en\";s:23:\"Course Objectives/Goals\";}', 1, 2, '1.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `bibliography`, `order`, `icon`) VALUES (3, 'a:2:{s:2:\"el\";s:24:\"Βιβλιογραφία\";s:2:\"en\";s:12:\"Bibliography\";}', 1, 3, '2.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `teaching_method`, `order`, `icon`) VALUES (4, 'a:2:{s:2:\"el\";s:37:\"Μέθοδοι διδασκαλίας\";s:2:\"en\";s:21:\"Instructional Methods\";}', 1, 4, '3.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `assessment_method`, `order`, `icon`) VALUES (5, 'a:2:{s:2:\"el\";s:37:\"Μέθοδοι αξιολόγησης\";s:2:\"en\";s:18:\"Assessment Methods\";}', 1, 5, '4.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `prerequisites`, `order`, `icon`) VALUES (6, 'a:2:{s:2:\"el\";s:28:\"Προαπαιτούμενα\";s:2:\"en\";s:29:\"Prerequisites/Prior Knowledge\";}', 1, 6, '5.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `instructors`, `order`, `icon`) VALUES (7, 'a:2:{s:2:\"el\";s:22:\"Διδάσκοντες\";s:2:\"en\";s:11:\"Instructors\";}', 1, 7, '6.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `target_group`, `order`, `icon`) VALUES (8, 'a:2:{s:2:\"el\";s:23:\"Ομάδα στόχος\";s:2:\"en\";s:12:\"Target Group\";}', 1, 8, '7.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `featured_books`, `order`, `icon`) VALUES (9, 'a:2:{s:2:\"el\";s:47:\"Προτεινόμενα συγγράμματα\";s:2:\"en\";s:9:\"Textbooks\";}', 1, 9, '8.png')");
$db->query("INSERT INTO `course_description_type` (`id`, `title`, `order`, `icon`) VALUES (10, 'a:2:{s:2:\"el\";s:22:\"Περισσότερα\";s:2:\"en\";s:15:\"Additional info\";}', 11, 'default.png')");

$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC1',0,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC2',0,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC3',0,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC5',0,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC6',0,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC7',1,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC8',1,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC9',1,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC10',1,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC11',1,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC12',1,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC13',1,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC14',1,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC15',2,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC16',2,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC17',0,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC18',1,0,0,0)");

$db->query("CREATE TABLE IF NOT EXISTS `course_description` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `comments` mediumtext,
    `type` smallint(6),
    `visible` tinyint(4) DEFAULT 0,
    `order` int(11) NOT NULL,
    `update_dt` datetime NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `course_review` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `is_certified` BOOL NOT NULL DEFAULT 0,
    `level` TINYINT(4) NOT NULL DEFAULT 0,
    `last_review` DATETIME NOT NULL,
    `last_reviewer` INT(11) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY cid (course_id)) $tbl_options");

$db->query("CREATE TABLE `user` (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(40) NOT NULL DEFAULT 0,
    surname VARCHAR(255) NOT NULL DEFAULT '',
    givenname VARCHAR(255) NOT NULL DEFAULT '',
    username VARCHAR(190) NOT NULL UNIQUE KEY COLLATE utf8mb4_bin,
    password VARCHAR(255) NOT NULL DEFAULT 'empty',
    email VARCHAR(255) NOT NULL DEFAULT '',
    parent_email VARCHAR(255) NOT NULL DEFAULT '',
    status TINYINT(4) NOT NULL DEFAULT " . USER_STUDENT . ",
    phone VARCHAR(20) DEFAULT '',
    am VARCHAR(20) DEFAULT '',
    registered_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    lang VARCHAR(16) NOT NULL DEFAULT 'el',
    description TEXT,
    has_icon TINYINT(1) NOT NULL DEFAULT 0,
    verified_mail TINYINT(1) NOT NULL DEFAULT " . EMAIL_UNVERIFIED . ",
    receive_mail TINYINT(1) NOT NULL DEFAULT 1,
    email_public TINYINT(1) NOT NULL DEFAULT 0,
    phone_public TINYINT(1) NOT NULL DEFAULT 0,
    am_public TINYINT(1) NOT NULL DEFAULT 0,
    pic_public TINYINT(1) NOT NULL DEFAULT 0,
    whitelist TEXT,
    eportfolio_enable TINYINT(1) NOT NULL DEFAULT 0,
    last_passreminder DATETIME DEFAULT NULL,
    disable_course_registration TINYINT NULL DEFAULT 0,
    options TEXT DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE `login_failure` (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip varchar(45) NOT NULL,
    count tinyint(4) unsigned NOT NULL default 0,
    last_fail datetime NOT NULL,
    UNIQUE KEY ip (ip)) $tbl_options");

$db->query("CREATE TABLE `loginout` (
    idLog int(11) NOT NULL auto_increment,
    id_user int(11) NOT NULL default 0,
    ip char(45) NOT NULL default '0.0.0.0',
    `when` datetime NOT NULL,
    action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
    PRIMARY KEY (idLog), KEY `id_user` (`id_user`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `personal_calendar` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `start` datetime NOT NULL,
    `end` datetime DEFAULT NULL,
    `duration` time NOT NULL,
    `recursion_period` varchar(30) DEFAULT NULL,
    `recursion_end` date DEFAULT NULL,
    `source_event_id` int(11) DEFAULT NULL,
    `reference_obj_module` mediumint(11) DEFAULT NULL,
    `reference_obj_type` ENUM('course', 'personalevent', 'user',
        'course_ebook', 'course_event', 'course_assignment', 'course_document',
        'course_link', 'course_exercise', 'course_learningpath', 'course_video',
        'course_videolink') DEFAULT NULL,
    `reference_obj_id` int(11) DEFAULT NULL,
    `reference_obj_course` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `personal_calendar_settings` (
    `user_id` int(11) NOT NULL,
    `view_type` enum('day','month','week') DEFAULT 'month',
    `personal_color` varchar(30) DEFAULT '#5882fa',
    `course_color` varchar(30) DEFAULT '#acfa58',
    `deadline_color` varchar(30) DEFAULT '#fa5882',
    `admin_color` varchar(30) DEFAULT '#eeeeee',
    `show_personal` bit(1) DEFAULT b'1',
    `show_course` bit(1) DEFAULT b'1',
    `show_deadline` bit(1) DEFAULT b'1',
    `show_admin` bit(1) DEFAULT b'1',
    PRIMARY KEY (`user_id`)) $tbl_options");

$db->query("CREATE TABLE `admin_calendar` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `start` datetime NOT NULL,
    `end` datetime DEFAULT NULL,
    `duration` time NOT NULL,
    `recursion_period` varchar(30) DEFAULT NULL,
    `recursion_end` date DEFAULT NULL,
    `source_event_id` int(11) DEFAULT NULL,
    `visibility_level` int(11) DEFAULT '1',
    `email_notification` time DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_events` (`user_id`),
    KEY `admin_events_dates` (`start`)) $tbl_options");

//  login out roll ups
$db->query("CREATE TABLE `loginout_summary` (
    id int(11) NOT NULL auto_increment,
    login_sum int(11) unsigned  NOT NULL default 0,
    start_date datetime NOT NULL,
    end_date datetime NOT NULL,
    PRIMARY KEY (id)) $tbl_options");

// monthly reports
$db->query("CREATE TABLE monthly_summary (
    id int(11) NOT NULL auto_increment,
    `month` date NOT NULL,
    `teachers` int NOT NULL DEFAULT '0',
    `students` int NOT NULL DEFAULT '0',
    `guests` int NOT NULL DEFAULT '0',
    `courses` int NOT NULL DEFAULT '0',
    `dep_id` int DEFAULT '0',
    `assignments` int DEFAULT '0',
    `exercises` int DEFAULT '0',
    `documents` int DEFAULT '0',
    `messages` int DEFAULT '0',
    `announcements` int DEFAULT '0',
    `forum_posts` int DEFAULT '0',
    `inactive_courses` int DEFAULT '0',            
    PRIMARY KEY (id)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `document` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL DEFAULT 0,
    `subsystem` TINYINT(4) NOT NULL,
    `subsystem_id` INT(11) DEFAULT NULL,
    `path` VARCHAR(255) NOT NULL,
    `extra_path` VARCHAR(255) NOT NULL DEFAULT '',
    `filename` VARCHAR(255) NOT NULL COLLATE utf8mb4_bin,
    `visible` TINYINT(4) NOT NULL DEFAULT 1,
    `public` TINYINT(4) NOT NULL DEFAULT 1,
    `comment` TEXT,
    `category` TINYINT(4) NOT NULL DEFAULT 0,
    `title` TEXT,
    `creator` TEXT,
    `date` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    `subject` TEXT,
    `description` TEXT,
    `author` VARCHAR(255) NOT NULL DEFAULT '',
    `format` VARCHAR(32) NOT NULL DEFAULT '',
    `language` VARCHAR(16) NOT NULL DEFAULT 'el',
    `copyrighted` TINYINT(4) NOT NULL DEFAULT 0,
    `editable` TINYINT(4) NOT NULL DEFAULT 0,
    `lock_user_id` INT(11) NOT NULL DEFAULT 0,
    `prevent_download` INT(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `group_properties` (
    `course_id` INT(11) NOT NULL,
    `group_id` INT(11) NOT NULL PRIMARY KEY,
    `self_registration` TINYINT(4) NOT NULL DEFAULT 1,
    `allow_unregister` TINYINT(4) NOT NULL DEFAULT 0,
    `forum` TINYINT(4) NOT NULL DEFAULT 1,
    `private_forum` TINYINT(4) NOT NULL DEFAULT 0,
    `documents` TINYINT(4) NOT NULL DEFAULT 1,
    `wiki` TINYINT(4) NOT NULL DEFAULT 0,
    `public_users_list` TINYINT(4) NOT NULL DEFAULT '1',
    `booking` TINYINT(4) NOT NULL DEFAULT 0,
    `agenda` TINYINT(4) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `group` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL DEFAULT 0,
    `name` varchar(255) NOT NULL DEFAULT '',
    `description` TEXT,
    `forum_id` int(11) NULL,
    `category_id` int(11) NULL,
    `max_members` int(11) NOT NULL DEFAULT 0,
    `secret_directory` varchar(100) NOT NULL DEFAULT 0,
    `visible` TINYINT(4) NOT NULL DEFAULT 1) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `group_members` (
    `group_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `is_tutor` int(11) NOT NULL DEFAULT 0,
    `description` TEXT,
    PRIMARY KEY (`group_id`, `user_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `tutor_availability_group` (
    `id` int(11) NOT NULL auto_increment,
    `user_id` int(11) NOT NULL default 0,
    `group_id` int(11) NOT NULL default 0,
    `start` DATETIME DEFAULT NULL,
    `end` DATETIME DEFAULT NULL,
    `lesson_id` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `booking` (
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

$db->query("CREATE TABLE IF NOT EXISTS `booking_user` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `booking_id` INT(11) NOT NULL,
    `simple_user_id` INT(11) NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `date_availability_user` (
        `id` int(11) NOT NULL auto_increment,
        `user_id` int(11) NOT NULL default 0,
        `start` DATETIME DEFAULT NULL,
        `end` DATETIME DEFAULT NULL,
        PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `date_booking` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `teacher_id` INT(11) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `start` DATETIME NOT NULL,
            `end` DATETIME NOT NULL,
            `accepted` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY(id),
            FOREIGN KEY (teacher_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `date_booking_user` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `booking_id` INT(11) NOT NULL,
            `student_id` INT(11) NOT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY (booking_id) REFERENCES date_booking(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `group_category` (
    `id` INT(6) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    PRIMARY KEY (`id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `glossary` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `term` VARCHAR(255) NOT NULL,
    `definition` text NOT NULL,
    `url` text,
    `order` INT(11) NOT NULL DEFAULT 0,
    `datestamp` DATETIME NOT NULL,
    `course_id` INT(11) NOT NULL,
    `category_id` INT(11) DEFAULT NULL,
    `notes` TEXT NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `glossary_category` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `order` INT(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `limit` TINYINT(4) NOT NULL DEFAULT 0,
    `students_semester` TINYINT(4) NOT NULL DEFAULT 1,
    `active` TINYINT(1) NOT NULL DEFAULT 0,
    `title` VARCHAR(255) DEFAULT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `attendance_activities` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_id` MEDIUMINT(11) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `date` DATETIME DEFAULT NULL,
    `description` TEXT NOT NULL,
    `module_auto_id` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `module_auto_type` TINYINT(4) NOT NULL DEFAULT 0,
    `auto` TINYINT(4) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `attendance_book` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_activity_id` MEDIUMINT(11) NOT NULL,
    `uid` int(11) NOT NULL DEFAULT 0,
    `attend` TINYINT(4) NOT NULL DEFAULT 0,
    `comments` TEXT NOT NULL,
     UNIQUE KEY attendance_activity_uid (attendance_activity_id, uid)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `attendance_users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_id` INT(11) NOT NULL,
    `uid` int(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `link` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `url` TEXT NOT NULL,
    `title` TEXT NOT NULL,
    `description` TEXT NOT NULL,
    `category` INT(6) DEFAULT 0 NOT NULL,
    `order` INT(6) DEFAULT 0 NOT NULL,
    `user_id` INT(11) DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `link_category` (
    `id` INT(6) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `order` INT(6) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `ebook` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `order` INT(11) NOT NULL,
    `title` TEXT,
    `visible` BOOL NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `ebook_section` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ebook_id` INT(11) NOT NULL,
    `public_id` VARCHAR(11) NOT NULL,
    `file` VARCHAR(128),
    `title` TEXT) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `ebook_subsection` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `section_id` INT(11) NOT NULL,
    `public_id` VARCHAR(11) NOT NULL,
    `file_id` INT(11) NOT NULL,
    `title` TEXT) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum` (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) DEFAULT '' NOT NULL,
    `desc` MEDIUMTEXT NOT NULL,
    `num_topics` INT(10) DEFAULT 0 NOT NULL,
    `num_posts` INT(10) DEFAULT 0 NOT NULL,
    `last_post_id` INT(10) DEFAULT 0 NOT NULL,
    `cat_id` INT(10) DEFAULT 0 NOT NULL,
    `course_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_category` (
    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cat_title` VARCHAR(255) DEFAULT '' NOT NULL,
    `cat_order` INT(11) DEFAULT 0 NOT NULL,
    `course_id` INT(11) NOT NULL,
    KEY `forum_category_index` (`id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_notify` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) DEFAULT 0 NOT NULL,
    `cat_id` INT(11) DEFAULT 0 NOT NULL,
    `forum_id` INT(11) DEFAULT 0 NOT NULL,
    `topic_id` INT(11) DEFAULT 0 NOT NULL,
    `notify_sent` BOOL DEFAULT 0 NOT NULL,
    `course_id` INT(11) DEFAULT 0 NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_post` (
    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `topic_id` INT(10) NOT NULL DEFAULT 0,
    `post_text` MEDIUMTEXT NOT NULL,
    `poster_id` INT(10) NOT NULL DEFAULT 0,
    `post_time` DATETIME,
    `poster_ip` VARCHAR(45) DEFAULT '' NOT NULL,
    `parent_post_id` INT(10) NOT NULL DEFAULT 0,
    `topic_filepath` varchar(200) DEFAULT NULL,
    `topic_filename` varchar(200) DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_topic` (
    `id` INT(10) NOT NULL auto_increment,
    `title` VARCHAR(255) DEFAULT NULL,
    `poster_id` INT(10) DEFAULT NULL,
    `topic_time` DATETIME,
    `num_views` INT(10) NOT NULL DEFAULT 0,
    `num_replies` INT(10) NOT NULL DEFAULT 0,
    `last_post_id` INT(10) NOT NULL DEFAULT 0,
    `forum_id` INT(10) NOT NULL DEFAULT 0,
    `locked` TINYINT DEFAULT 0 NOT NULL,
    `pin_time` DATETIME DEFAULT NULL,
    PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_user_stats` (
    `user_id` INT(11) NOT NULL,
    `num_posts` INT(11) NOT NULL,
    `course_id` INT(11) NOT NULL,
    PRIMARY KEY (`user_id`,`course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `video` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `url` VARCHAR(200) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `category` INT(6) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `creator` VARCHAR(255) NOT NULL,
    `publisher` VARCHAR(255) NOT NULL,
    `date` DATETIME NOT NULL,
    `visible` TINYINT(4) NOT NULL DEFAULT 1,
    `public` TINYINT(4) NOT NULL DEFAULT 1) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `videolink` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `url` VARCHAR(255) NOT NULL DEFAULT '',
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NOT NULL,
    `category` INT(6) DEFAULT NULL,
    `creator` VARCHAR(255) NOT NULL DEFAULT '',
    `publisher` VARCHAR(255) NOT NULL DEFAULT '',
    `date` DATETIME NOT NULL,
    `visible` TINYINT(4) NOT NULL DEFAULT 1,
    `public` TINYINT(4) NOT NULL DEFAULT 1) $tbl_options");

$db->query("CREATE TABLE `video_category` (
    `id` INT(11) NOT NULL auto_increment,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    PRIMARY KEY (id)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_msg (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `author_id` INT(11) UNSIGNED NOT NULL,
    `subject` TEXT NOT NULL,
    `body` LONGTEXT NOT NULL,
    `timestamp` INT(11) NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_attachment (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `msg_id` INT(11) UNSIGNED NOT NULL,
    `filename` VARCHAR(250) NOT NULL,
    `real_filename` varchar(255) NOT NULL,
    `filesize` INT(11) UNSIGNED NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_index (
    `msg_id` INT(11) UNSIGNED NOT NULL,
    `recipient_id` INT(11) UNSIGNED NOT NULL,
    `is_read` BOOLEAN NOT NULL DEFAULT 0,
    `deleted` BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY (`msg_id`, `recipient_id`)) $tbl_options");

// COMMENT='List of available modules used in learning paths';
$db->query("CREATE TABLE IF NOT EXISTS `lp_module` (
    `module_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `comment` TEXT NOT NULL,
    `accessibility` enum('PRIVATE','PUBLIC') NOT NULL DEFAULT 'PRIVATE',
    `startAsset_id` INT(11) NOT NULL DEFAULT 0,
    `contentType` enum('CLARODOC', 'DOCUMENT', 'EXERCISE', 'HANDMADE',
        'SCORM', 'SCORM_ASSET', 'LABEL', 'COURSE_DESCRIPTION', 'LINK',
        'MEDIA','MEDIALINK') NOT NULL,
    `launch_data` TEXT NOT NULL) $tbl_options");

// COMMENT='List of learning Paths';
$db->query("CREATE TABLE IF NOT EXISTS `lp_learnPath` (
    `learnPath_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `comment` TEXT NOT NULL,
    `lock` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
    `visible` TINYINT(4) NOT NULL DEFAULT 0,
    `rank` INT(11) NOT NULL DEFAULT 0) $tbl_options");

// COMMENT='This table links module to the learning path using them';
$db->query("CREATE TABLE IF NOT EXISTS `lp_rel_learnPath_module` (
    `learnPath_module_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `learnPath_id` INT(11) NOT NULL DEFAULT 0,
    `module_id` INT(11) NOT NULL DEFAULT 0,
    `lock` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
    `visible` TINYINT(4) NOT NULL DEFAULT 0,
    `specificComment` TEXT NOT NULL,
    `rank` INT(11) NOT NULL DEFAULT 0,
    `parent` INT(11) NOT NULL DEFAULT 0,
    `raw_to_pass` TINYINT(4) NOT NULL DEFAULT 50) $tbl_options");

// COMMENT='List of resources of module of learning paths';
$db->query("CREATE TABLE IF NOT EXISTS `lp_asset` (
    `asset_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `module_id` INT(11) NOT NULL DEFAULT 0,
    `path` VARCHAR(255) NOT NULL DEFAULT '',
    `comment` VARCHAR(255) default NULL) $tbl_options");

// COMMENT='Record the last known status of the user in the course';
$db->query("CREATE TABLE IF NOT EXISTS `lp_user_module_progress` (
    `user_module_progress_id` INT(22) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `learnPath_module_id` INT(11) NOT NULL DEFAULT 0,
    `learnPath_id` INT(11) NOT NULL DEFAULT 0,
    `lesson_location` VARCHAR(255) NOT NULL DEFAULT '',
    `lesson_status` enum('NOT ATTEMPTED', 'PASSED', 'FAILED', 'COMPLETED',
        'BROWSED', 'INCOMPLETE', 'UNKNOWN') NOT NULL DEFAULT 'NOT ATTEMPTED',
    `entry` enum('AB-INITIO', 'RESUME', '') NOT NULL DEFAULT 'AB-INITIO',
    `raw` TINYINT(4) NOT NULL DEFAULT '-1',
    `scoreMin` TINYINT(4) NOT NULL DEFAULT '-1',
    `scoreMax` TINYINT(4) NOT NULL DEFAULT '-1',
    `total_time` VARCHAR(13) NOT NULL DEFAULT '0000:00:00.00',
    `session_time` VARCHAR(13) NOT NULL DEFAULT '0000:00:00.00',
    `suspend_data` TEXT NOT NULL,
    `credit` enum('CREDIT','NO-CREDIT') NOT NULL DEFAULT 'NO-CREDIT',
    `attempt` int(11) NOT NULL DEFAULT 1,
    `started` datetime DEFAULT NULL,
    `accessed` datetime DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_properties` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NULL,
    `group_id` INT(11) NOT NULL DEFAULT 0,
    visible TINYINT(4) UNSIGNED NOT NULL DEFAULT '1') $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_acls` (
    `wiki_id` INT(11) UNSIGNED NOT NULL,
    `flag` VARCHAR(255) NOT NULL,
    `value` ENUM('false','true') NOT NULL DEFAULT 'false',
    PRIMARY KEY (wiki_id, flag)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_pages` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `wiki_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `owner_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `title` VARCHAR(190) NOT NULL DEFAULT '',
    `ctime` DATETIME NOT NULL,
    `last_version` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `last_mtime` DATETIME NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_pages_content` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `editor_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `mtime` DATETIME NOT NULL,
    `content` TEXT NOT NULL,
    `changelog` VARCHAR(255) )  $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_locks` (
    `ptitle` VARCHAR(190) NOT NULL DEFAULT '',
    `wiki_id` INT(11) UNSIGNED NOT NULL,
    `uid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `ltime_created` DATETIME DEFAULT NULL,
    `ltime_alive` DATETIME DEFAULT NULL,
    PRIMARY KEY (ptitle, wiki_id) ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `blog_post` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `content` TEXT NOT NULL,
    `time` DATETIME NOT NULL,
    `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `commenting` TINYINT NOT NULL DEFAULT '1',
    `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `course_id` INT(11) NOT NULL,
    `visible` TINYINT UNSIGNED NOT NULL DEFAULT '1'
    ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT(11) NOT NULL,
    `rtype` VARCHAR(50) NOT NULL,
    `content` TEXT NOT NULL,
    `time` DATETIME NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `rating` (
    `rate_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT(11) NOT NULL,
    `rtype` VARCHAR(50) NOT NULL,
    `value` TINYINT NOT NULL,
    `widget` VARCHAR(30) NOT NULL,
    `time` DATETIME NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `rating_source` VARCHAR(50) NOT NULL,
    INDEX `rating_index_1` (`rid`, `rtype`, `widget`),
    INDEX `rating_index_2` (`rid`, `rtype`, `widget`, `user_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `rating_cache` (
    `rate_cache_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT(11) NOT NULL,
    `rtype` VARCHAR(50) NOT NULL,
    `value` FLOAT NOT NULL DEFAULT 0,
    `count` INT(11) NOT NULL DEFAULT 0,
    `tag` VARCHAR(50),
    INDEX `rating_cache_index_1` (`rid`, `rtype`, `tag`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `abuse_report` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT(11) NOT NULL,
    `rtype` VARCHAR(50) NOT NULL,
    `course_id` INT(11) NOT NULL,
    `reason` VARCHAR(50) NOT NULL DEFAULT '',
    `message` TEXT NOT NULL,
    `timestamp` INT(11) NOT NULL DEFAULT 0,
    `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    INDEX `abuse_report_index_1` (`rid`, `rtype`, `user_id`, `status`),
    INDEX `abuse_report_index_2` (`course_id`, `status`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields` (
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

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data` (
                `user_id` INT(8) UNSIGNED NOT NULL DEFAULT 0,
                `field_id` INT(11) NOT NULL,
                `data` TEXT NOT NULL,
                PRIMARY KEY (`user_id`, `field_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data_pending` (
                `user_request_id` INT(11) NOT NULL DEFAULT 0,
                `field_id` INT(11) NOT NULL,
                `data` TEXT NOT NULL,
                PRIMARY KEY (`user_request_id`, `field_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_category` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` MEDIUMTEXT NOT NULL,
                `sortorder`  INT(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `faq` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `title` text NOT NULL,
                            `body` text NOT NULL,
                            `order` int(11) NOT NULL,
                            PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `homepageTexts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
    `title` text NULL,
    `body` text NULL,
    `order` int(11) NOT NULL,
    `type` INT(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `homepagePriorities` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` text NULL,
    `order` int(11) NOT NULL,
    `visible` int(11) NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("INSERT INTO `homepagePriorities` (`title`, `order`, `visible`) VALUES
                                            ('announcements', 0, 1),
                                            ('popular_courses', 1, 1),
                                            ('texts', 2, 1),
                                            ('testimonials', 3, 1),
                                            ('statistics', 4, 1),
                                            ('open_courses', 5, 1)");


$db->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `shortname` VARCHAR(255) NOT NULL,
        `name` MEDIUMTEXT NOT NULL,
        `description` MEDIUMTEXT NULL DEFAULT NULL,
        `datatype` VARCHAR(255) NOT NULL,
        `categoryid` INT(11) NOT NULL DEFAULT 0,
        `sortorder`  INT(11) NOT NULL DEFAULT 0,
        `required` TINYINT NOT NULL DEFAULT 0,
        `data` TEXT NULL DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields_data` (
        `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
        `field_id` INT(11) NOT NULL,
        `data` TEXT NOT NULL,
        PRIMARY KEY (`user_id`, `field_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields_category` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` MEDIUMTEXT NOT NULL,
        `sortorder`  INT(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("INSERT INTO `eportfolio_fields_category` (`id`, `name`, `sortorder`) VALUES
        (1, '$langPersInfo', 0),
        (2, '$langEduEmpl', -1),
        (3, '$langAchievements', -2),
        (4, '$langGoalsSkills', -3),
        (5, '$langContactInfo', -4)");

$db->query("INSERT INTO `eportfolio_fields` (`id`, `shortname`, `name`, `description`, `datatype`, `categoryid`, `sortorder`, `required`, `data`) VALUES
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

$db->query("CREATE TABLE IF NOT EXISTS `eportfolio_resource` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
        `resource_id` INT(11) NOT NULL,
        `resource_type` VARCHAR(50) NOT NULL,
        `course_id` INT(11) NOT NULL,
        `course_title` VARCHAR(255) NOT NULL DEFAULT '',
        `time_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `data` TEXT NOT NULL,
        INDEX `eportfolio_res_index` (`user_id`,`resource_type`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wall_post` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT(11) NOT NULL,
        `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
        `content` TEXT DEFAULT NULL,
        `extvideo` VARCHAR(255) DEFAULT '',
        `timestamp` INT(11) NOT NULL DEFAULT 0,
        `pinned` TINYINT(1) NOT NULL DEFAULT 0,
        INDEX `wall_post_index` (`course_id`)) $tbl_options");

$db->query("CREATE TABLE `wall_post_resources` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `post_id` INT(11) NOT NULL,
        `title` VARCHAR(255) NOT NULL DEFAULT '',
        `res_id` INT(11) NOT NULL,
        `type` VARCHAR(255) NOT NULL DEFAULT '',
        INDEX `wall_post_resources_index` (`post_id`)) $tbl_options");


$db->query("CREATE TABLE IF NOT EXISTS `poll` (
    `pid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `creator_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `creation_date` DATETIME NOT NULL,
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `active` INT(11) NOT NULL DEFAULT 0,
    `public` TINYINT(1) NOT NULL DEFAULT 1,
    `description` MEDIUMTEXT NULL DEFAULT NULL,
    `end_message` MEDIUMTEXT NULL DEFAULT NULL,
    `anonymized` INT(1) NOT NULL DEFAULT 0,
    `show_results` INT(1) NOT NULL DEFAULT 0,
    `display_position` INT(1) NOT NULL DEFAULT 0,
    `multiple_submissions` TINYINT NOT NULL DEFAULT '0',
    `default_answer` TINYINT NOT NULL DEFAULT '0',
    `type` TINYINT NOT NULL DEFAULT 0,
    `assign_to_specific` TINYINT NOT NULL DEFAULT '0',
    `lti_template` INT(11) DEFAULT NULL,
    `launchcontainer` TINYINT DEFAULT NULL,
    `pagination` INT(11) NOT NULL DEFAULT 0,
    `require_answer` INT(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_to_specific` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11) NULL,
    `group_id` int(11) NULL,
    `poll_id` int(11) NOT NULL ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_user_record` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `uid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `email` VARCHAR(255) DEFAULT NULL,
    `email_verification` TINYINT(1) DEFAULT NULL,
    `verification_code` VARCHAR(255) DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_answer_record` (
    `arid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `poll_user_record_id` INT(11) NOT NULL,
    `qid` INT(11) NOT NULL DEFAULT 0,
    `aid` INT(11) NOT NULL DEFAULT 0,
    `answer_text` TEXT NOT NULL,
    `submit_date` DATETIME NOT NULL,
    `sub_qid` INT(11) NOT NULL DEFAULT 0,
    `sub_qid_row` int(11) NOT NULL DEFAULT 0,
    FOREIGN KEY (`poll_user_record_id`)
    REFERENCES `poll_user_record` (`id`)
    ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_question` (
    `pqid` BIGINT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT(11) NOT NULL DEFAULT 0,
    `question_text` TEXT NOT NULL,
    `qtype` tinyint(3) UNSIGNED NOT NULL,
    `q_position` INT(11) DEFAULT 1,
    `q_scale` INT(11) NULL DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `answer_scale` TEXT DEFAULT NULL,
    `q_row` INT(11) NOT NULL DEFAULT 0,
    `q_column` INT(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_question_answer` (
    `pqaid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pqid` INT(11) NOT NULL DEFAULT 0,
    `answer_text` TEXT NOT NULL,
    `sub_question` INT(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `assignment` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NOT NULL,
    `comments` TEXT NOT NULL,
    `submission_type` TINYINT NOT NULL DEFAULT '0',
    `deadline` DATETIME NULL DEFAULT NULL,
    `late_submission` TINYINT NOT NULL DEFAULT '0',
    `submission_date` DATETIME NOT NULL,
    `active` TINYINT NOT NULL DEFAULT 1,
    `secret_directory` VARCHAR(30) NOT NULL,
    `group_submissions` TINYINT  NOT NULL DEFAULT 0,
    `grading_type` TINYINT NOT NULL DEFAULT '0',
    `max_grade` FLOAT DEFAULT '10' NOT NULL,
    `grading_scale_id` INT(11) NOT NULL DEFAULT '0',
    `assign_to_specific` TINYINT NOT NULL DEFAULT 0,
    `file_path` VARCHAR(200) DEFAULT '' NOT NULL,
    `file_name` VARCHAR(200) DEFAULT '' NOT NULL,
    `auto_judge` TINYINT(1) NOT NULL DEFAULT 0,
    `auto_judge_scenarios` TEXT,
    `lang` VARCHAR(10) NOT NULL DEFAULT '',
    `notification` TINYINT(4) DEFAULT 0,
    `ip_lock` TEXT,
    `password_lock` VARCHAR(255) NOT NULL DEFAULT '',
    `assignment_type` TINYINT NOT NULL DEFAULT '0',
    `lti_template` INT(11) DEFAULT NULL,
    `launchcontainer` TINYINT DEFAULT NULL,
    `tii_feedbackreleasedate` DATETIME NULL DEFAULT NULL,
    `tii_internetcheck` TINYINT NOT NULL DEFAULT '1',
    `tii_institutioncheck` TINYINT NOT NULL DEFAULT '1',
    `tii_journalcheck` TINYINT NOT NULL DEFAULT '1',
    `tii_report_gen_speed` TINYINT NOT NULL DEFAULT '0',
    `tii_s_view_reports` TINYINT NOT NULL DEFAULT '0',
    `tii_studentpapercheck` TINYINT NOT NULL DEFAULT '1',
    `tii_submit_papers_to` TINYINT NOT NULL DEFAULT '1',
    `tii_use_biblio_exclusion` TINYINT NOT NULL DEFAULT '0',
    `tii_use_quoted_exclusion` TINYINT NOT NULL DEFAULT '0',
    `tii_exclude_type` VARCHAR(20) NOT NULL DEFAULT 'none',
    `tii_exclude_value` INT(11) NOT NULL DEFAULT '0',
    `tii_instructorcustomparameters` TEXT,
    `reviews_per_assignment` int(4) DEFAULT NULL,
    `start_date_review` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `due_date_review` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `max_submissions` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
    `passing_grade` FLOAT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `assignment_submit` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `assignment_id` INT(11) NOT NULL DEFAULT 0,
    `submission_date` DATETIME NOT NULL,
    `submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
    `file_path` VARCHAR(200) NOT NULL DEFAULT '',
    `file_name` VARCHAR(200) NOT NULL DEFAULT '',
    `submission_text` MEDIUMTEXT NULL DEFAULT NULL,
    `comments` TEXT NOT NULL,
    `grade` FLOAT DEFAULT NULL,
    `grade_rubric` TEXT,
    `grade_comments` TEXT NOT NULL,
    `grade_comments_filepath` VARCHAR(200) NOT NULL DEFAULT '',
    `grade_comments_filename` VARCHAR(200) NOT NULL DEFAULT '',
    `grade_submission_date` DATE NOT NULL DEFAULT '1000-10-10',
    `grade_submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
    `group_id` INT( 11 ) DEFAULT NULL,
    `auto_judge_scenarios_output` TEXT) $tbl_options");

// assignment peer review
$db->query("CREATE TABLE `assignment_grading_review` (
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

// grading scales table
$db->query("CREATE TABLE IF NOT EXISTS `grading_scale` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` varchar(255) NOT NULL,
    `scales` text NOT NULL,
    `course_id` int(11) NOT NULL,
    KEY `course_id` (`course_id`)) $tbl_options");

// rubric table based on grading scales

$db->query("CREATE TABLE IF NOT EXISTS `rubric` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `scales` text NOT NULL,
    `description` text,
    `preview_rubric` tinyint(1) NOT NULL DEFAULT '0',
    `points_to_graded` tinyint(1) NOT NULL DEFAULT '0',
    `course_id` int(11) NOT NULL,
    KEY `course_id` (`course_id`)) $tbl_options");


$db->query("CREATE TABLE IF NOT EXISTS `assignment_to_specific` (
    `user_id` int(11) NOT NULL,
    `group_id` int(11) NOT NULL,
    `assignment_id` int(11) NOT NULL,
    PRIMARY KEY (user_id, group_id, assignment_id)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `description` TEXT,
    `type` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
    `range` TINYINT UNSIGNED DEFAULT 0,
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `temp_save` TINYINT(1) NOT NULL DEFAULT 0,
    `time_constraint` INT(11) DEFAULT 0,
    `attempts_allowed` INT(11) DEFAULT 0,
    `random` SMALLINT(6) NOT NULL DEFAULT 0,
    `shuffle` SMALLINT(6) NOT NULL DEFAULT 0,
    `active` TINYINT(4) DEFAULT NULL,
    `public` TINYINT(4) NOT NULL DEFAULT 1,
    `results` TINYINT(1) NOT NULL DEFAULT 1,
    `score` TINYINT(1) NOT NULL DEFAULT 1,
    `assign_to_specific` TINYINT NOT NULL DEFAULT 0,
    `ip_lock` TEXT NULL DEFAULT NULL,
    `password_lock` VARCHAR(255) NULL DEFAULT NULL,
    `continue_time_limit` INT(11) NOT NULL DEFAULT 0,
    `calc_grade_method` TINYINT DEFAULT 1,
    `general_feedback` TEXT DEFAULT NULL,
    `options` TEXT DEFAULT NULL,
    `is_exam` INT DEFAULT 0 NULL,
     passing_grade FLOAT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_to_specific` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11) NULL,
    `group_id` int(11) NULL,
    `exercise_id` int(11) NOT NULL ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_user_record` (
    `eurid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eid` INT(11) NOT NULL DEFAULT 0,
    `uid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `record_start_date` DATETIME NOT NULL,
    `record_end_date` DATETIME DEFAULT NULL,
    `total_score` FLOAT(11,2) NOT NULL DEFAULT 0,
    `total_weighting` FLOAT(11,2) DEFAULT 0,
    `attempt` INT(11) NOT NULL DEFAULT 0,
    `attempt_status` tinyint(4) NOT NULL DEFAULT 1,
    `secs_remaining` INT(11) NOT NULL DEFAULT '0',
    `assigned_to` INT(11) DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_answer_record` (
    `answer_record_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eurid` int(11) NOT NULL,
    `question_id` int(11) NOT NULL,
    `answer` text,
    `answer_id` int(11) NOT NULL,
    `weight` float(11,2) DEFAULT NULL,
    `is_answered` TINYINT NOT NULL DEFAULT 1,
    `q_position` INT(11) NOT NULL DEFAULT 1) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_question` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `question` TEXT,
    `description` TEXT,
    `feedback` TEXT,
    `weight` FLOAT(11,2) DEFAULT NULL,
    `type` INT(11) DEFAULT 1,
    `difficulty` INT(1) DEFAULT 0,
    `category` INT(11) DEFAULT 0,
    `copy_of_qid` INT(11) DEFAULT NULL,
     CONSTRAINT FOREIGN KEY (copy_of_qid)
     REFERENCES exercise_question(id) ON DELETE SET NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_question_cats` (
    `question_cat_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `question_cat_name` VARCHAR(300) NOT NULL,
    `course_id` INT(11) NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_answer` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT(11) NOT NULL DEFAULT 0,
    `answer` TEXT,
    `correct` INT(11) DEFAULT NULL,
    `comment` TEXT,
    `weight` FLOAT(5,2),
    `r_position` INT(11) DEFAULT NULL ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_with_questions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT(11) DEFAULT 0,
    `exercise_id` INT(11) NOT NULL DEFAULT 0,
    `q_position` INT(11) NOT NULL DEFAULT 1,
    `random_criteria` TEXT) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS lti_apps (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) DEFAULT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `description` TEXT,
    `lti_version` VARCHAR(255) NOT NULL DEFAULT '1.1',
    `lti_provider_url` VARCHAR(255) DEFAULT NULL,
    `lti_provider_key` VARCHAR(255) DEFAULT NULL,
    `lti_provider_secret` VARCHAR(255) DEFAULT NULL,
    `lti_provider_public_keyset_url` VARCHAR(255) DEFAULT NULL,
    `lti_provider_initiate_login_url` VARCHAR(255) DEFAULT NULL,
    `lti_provider_redirection_uri` VARCHAR(255) DEFAULT NULL,
    `client_id` VARCHAR(255) DEFAULT NULL,
    `launchcontainer` TINYINT(4) NOT NULL DEFAULT 1,
    `is_template` TINYINT(4) NOT NULL DEFAULT 0,
    `enabled` TINYINT(4) NOT NULL DEFAULT 1,
    `all_courses` TINYINT(1) NOT NULL DEFAULT 1,
    `type` VARCHAR(255) NOT NULL DEFAULT 'turnitin',
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `lti_access_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `lti_app` int(11) NOT NULL,
    `scope` TEXT,
    `token` VARCHAR(128) NOT NULL,
    `valid_until` int(11) NOT NULL,
    `time_created` int(11) NOT NULL,
    `last_access` int(11),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`lti_app`) REFERENCES `lti_apps` (`id`)) $tbl_options");

$db->query("CREATE TABLE `course_lti_app` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `course_id` int(11) NOT NULL,
      `lti_app` int(11) NOT NULL,
      FOREIGN KEY (`course_id`) REFERENCES `course` (`id`),
      FOREIGN KEY (`lti_app`) REFERENCES `lti_apps` (`id`))
   $tbl_options");

$db->query("CREATE TABLE `course_lti_publish` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `course_id` int(11) NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `description` TEXT,
      `lti_provider_key` VARCHAR(255) NOT NULL,
      `lti_provider_secret` VARCHAR(255) NOT NULL,
      `enabled` TINYINT(4) NOT NULL DEFAULT 1,
      FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `course_lti_publish_user_enrolments` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `publish_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `created` int(11) NOT NULL,
      `updated` int(11) NOT NULL,
      FOREIGN KEY (`publish_id`) REFERENCES `course_lti_publish` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `course_lti_enrol_users` (
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
      FOREIGN KEY (`publish_id`) REFERENCES `course_lti_publish` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE) $tbl_options");

// lti provider tables
$db->query("CREATE TABLE `lti_publish_lti2_consumer` (
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

$db->query("CREATE TABLE `lti_publish_lti2_context` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `consumerid` int(11) NOT NULL,
    `lticontextkey` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100),
    `settings` TEXT,
    `created` int(11) NOT NULL,
    `updated` int(11) NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_nonce` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `consumerid` int(11) NOT NULL,
    `value` VARCHAR(64) NOT NULL,
    `expires` int(11) NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_resource_link` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `contextid` int(11),
    `consumerid` int(11),
    `ltiresourcelinkkey` VARCHAR(255) NOT NULL,
    `settings` TEXT,
    `primaryresourcelinkid` int(11),
    `shareapproved` smallint(6),
    `created` int(11) NOT NULL,
    `updated` int(11) NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_share_key` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `sharekey` VARCHAR(32) NOT NULL UNIQUE,
    `resourcelinkid` int(11) NOT NULL UNIQUE,
    `autoapprove` smallint(6) NOT NULL,
    `expires` int(11) NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_tool_proxy` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `toolproxykey` VARCHAR(32) NOT NULL UNIQUE,
    `consumerid` int(11) NOT NULL,
    `toolproxy` TEXT NOT NULL,
    `created` int(11) NOT NULL,
    `updated` int(11) NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_user_result` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `resourcelinkid` int(11) NOT NULL,
    `ltiuserkey` VARCHAR(255) NOT NULL,
    `ltiresultsourcedid` VARCHAR(1024) NOT NULL,
    `created` int(11) NOT NULL,
    `updated` int(11) NOT NULL) $tbl_options");

// hierarchy tables
$db->query("CREATE TABLE IF NOT EXISTS `hierarchy` (
    `id` int(11) NOT NULL auto_increment PRIMARY KEY,
    `code` varchar(20),
    `name` text NOT NULL,
    `description` text NOT NULL,
    `number` int(11) NOT NULL default 1000,
    `generator` int(11) NOT NULL default 100,
    `lft` int(11) NOT NULL,
    `rgt` int(11) NOT NULL,
    `allow_course` boolean not null default false,
    `allow_user` boolean NOT NULL default false,
    `order_priority` int(11) default null,
    `visible` TINYINT(4) NOT NULL default 2,
    `faculty_image` varchar(400) NULL,
    KEY `lftindex` (`lft`),
    KEY `rgtindex` (`rgt`) ) $tbl_options");

$db->query("INSERT INTO `hierarchy` (code, name, description, lft, rgt)
    VALUES ('', ?s, '', 1, 68)", $institutionForm);

$db->query("INSERT INTO `hierarchy` (code, name, description, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA', ?s, '', '10', '100', 2, 23, true, true)", $langHierarchyTestDepartment);
$db->query("INSERT INTO `hierarchy` (code, name, description, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMAPRE', ?s, '', '10', '100', 3, 20, true, true)", $langHierarchyTestCategory . ' 1');
$db->query("INSERT INTO `hierarchy` (code, name, description, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA1', ?s, '', '10', '100', 4, 5, true, true)", $langHierarchyTestSubCategory . ' 1');
$db->query("INSERT INTO `hierarchy` (code, name, description, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA2', ?s, '', '10', '100', 6, 7, true, true)", $langHierarchyTestSubCategory . ' 2');
$db->query("INSERT INTO `hierarchy` (code, name, description, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMAPOST', ?s, '', '10', '100', '21', '22', true, true)", $langHierarchyTestCategory . ' 2');

$db->query("CREATE TABLE `course_department` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course` INT(11) NOT NULL,
    `department` INT(11) NOT NULL,
    UNIQUE KEY `cdep_unique` (`course`,`department`),
    FOREIGN KEY (`course`) REFERENCES `course` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `user_department` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` INT(11) NOT NULL,
    `department` INT(11) NOT NULL,
    UNIQUE KEY `udep_unique` (`user`,`department`),
    FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $tbl_options");


// create stored procedures
refreshHierarchyProcedures();

$db->query("CREATE TABLE `admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `privilege` int(11) NOT NULL DEFAULT 0,
    `department_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id` (`user_id`,`department_id`),
    KEY `admin_index` (`user_id`),
    KEY `department_id` (`department_id`),
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $tbl_options");

// encrypt the admin password into DB
$password_encrypted = password_hash($passForm, PASSWORD_DEFAULT);
$admin_uid = $db->query("INSERT INTO `user`
    (`givenname`, `surname`, `username`, `password`, `email`, `status`, `lang`,
     `registered_at`,`expires_at`, `verified_mail`, `whitelist`, `description`)
    VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, " . DBHelper::timeAfter() . ", " .
            DBHelper::timeAfter(5 * 365 * 24 * 60 * 60) . ", ?d, ?s, ?s)",
    $nameForm, '', $loginForm, $password_encrypted, $emailForm, 1, $lang, 1,
    NULL, 'Administrator')->lastInsertID;
if (isset($_SERVER['REMOTE_ADDR'])) {
    $db->query("INSERT INTO loginout (`id_user`, `ip`, `when`, `action`)
        VALUES (?d, ?s, " . DBHelper::timeAfter() . ", ?s)",
        $admin_uid, $_SERVER['REMOTE_ADDR'], 'LOGIN');
}

$db->query("INSERT INTO admin (user_id, privilege) VALUES (?d, ?d)", $admin_uid, 0);

$db->query("CREATE TABLE `user_request` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    givenname VARCHAR(255) NOT NULL DEFAULT '',
    surname VARCHAR(255) NOT NULL DEFAULT '',
    username VARCHAR(255) NOT NULL DEFAULT '',
    password VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '',
    verified_mail TINYINT(1) NOT NULL DEFAULT " . EMAIL_UNVERIFIED . ",
    faculty_id INT(11) NOT NULL DEFAULT 0,
    phone VARCHAR(20) NOT NULL DEFAULT '',
    am VARCHAR(20) NOT NULL DEFAULT '',
    state INT(11) NOT NULL DEFAULT 0,
    date_open DATETIME DEFAULT NULL,
    date_closed DATETIME DEFAULT NULL,
    comment TEXT NOT NULL,
    lang VARCHAR(16) NOT NULL DEFAULT 'el',
    status TINYINT(4) NOT NULL DEFAULT 1,
    request_ip VARCHAR(45) NOT NULL DEFAULT '',
    PRIMARY KEY (id)) $tbl_options");

$db->query("CREATE TABLE `auth` (
    `auth_id` int(2) NOT NULL auto_increment,
    `auth_name` varchar(20) NOT NULL default '',
    `auth_settings` text,
    `auth_instructions` text,
    `auth_title` text,
    `auth_default` tinyint(1) NOT NULL default 0,
    PRIMARY KEY (`auth_id`))
    $tbl_options");

$db->query("INSERT INTO `auth` VALUES
    (1, 'eclass', '', '', '', 1),
    (2, 'pop3', '', '', '', 0),
    (3, 'imap', '', '', '', 0),
    (4, 'ldap', '', '', '', 0),
    (5, 'db', '', '', '', 0),
    (6, 'shibboleth', '', '', '', 0),
    (7, 'cas', '', '', '', 0),
    (8, 'facebook', '', '', '', 0),
    (9, 'twitter', '', '', '', 0),
    (10, 'google', '', '', '', 0),
    (11, 'live', '', '', '', 0),
    (12, 'yahoo', '', '', '', 0),
    (13, 'linkedin', '', '', '', 0)");

$db->query("CREATE TABLE `user_ext_uid` (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    auth_id INT(2) NOT NULL,
    uid VARCHAR(64) NOT NULL,
    UNIQUE KEY (user_id, auth_id),
    KEY (uid),
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE)
    $tbl_options");

$db->query("CREATE TABLE `user_request_ext_uid` (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_request_id INT(11) NOT NULL,
    auth_id INT(2) NOT NULL,
    uid VARCHAR(64) NOT NULL,
    UNIQUE KEY (user_request_id, auth_id),
    FOREIGN KEY (`user_request_id`) REFERENCES `user_request` (`id`) ON DELETE CASCADE)
    $tbl_options");

$eclass_stud_reg = intval($eclass_stud_reg);

$db->query("CREATE TABLE `config` (
    `key` VARCHAR(32) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`key`)) $tbl_options");

$default_config = array(
    'base_url', $urlForm,
    'default_language', $lang,
    'dont_display_login_form', 0,
    'dont_display_testimonials', 1,
    'dont_display_announcements', 0,
    'dont_display_popular_courses', 1,
    'dont_display_texts', 1,
    'dont_display_statistics', 1,
    'dont_display_open_courses', 1,
    'course_invitation', 0,
    'total_courses', 0,
    'visits_per_week', 0,
    'users_registered', 0,
    'show_modal_openCourses', 0,
    'individual_group_bookings', 0,
    'enable_quick_note', 0,
    'email_required', 0,
    'email_from', 1,
    'email_verification_required', 0,
    'dont_mail_unverified_mails', 0,
    'am_required', 0,
    'dropbox_allow_student_to_student', 0,
    'block_username_change', 0,
    'enable_mobileapi', 1,
    'code_key', generate_secret_key(32),
    'display_captcha', 0,
    'insert_xml_metadata', 0,
    'doc_quota', 500,
    'video_quota', 500,
    'group_quota', 500,
    'dropbox_quota', 500,
    'user_registration', 1,
    'alt_auth_stud_reg', 2,
    'alt_auth_prof_reg', 2,
    'eclass_stud_reg', $eclass_stud_reg,
    'eclass_prof_reg', 1,
    'course_multidep', 0,
    'user_multidep', 0,
    'restrict_owndep', 0,
    'restrict_teacher_owndep', 0,
    'allow_teacher_clone_course', 0,
    'contact_form_activation', 1,
    'max_glossary_terms', '250',
    'phpSysInfoURL', $phpSysInfoURL,
    'email_sender', $emailForm,
    'admin_name', $nameForm,
    'email_helpdesk', $helpdeskmail,
    'site_name', $campusForm,
    'phone', $helpdeskForm,
    'postaddress', $postaddressForm,
    'institution', $institutionForm,
    'institution_url', $institutionUrlForm,
    'account_duration', '126144000',
    'language', $lang,
    'active_ui_languages', $active_ui_languages,
    // 'student_upload_whitelist', $student_upload_whitelist,
    // 'teacher_upload_whitelist', $teacher_upload_whitelist,
    'theme', 'modern',
    'login_fail_check', 1,
    'login_fail_threshold', 15,
    'login_fail_deny_interval', 5,
    'login_fail_forgive_interval', 24,
    'actions_expire_interval', 24,
    'log_expire_interval', 5,
    'log_purge_interval', 12,
    'course_metadata', 0,
    'opencourses_enable', 0,
    'enable_indexing', 1,
    'enable_search', 1,
    'enable_social_sharing_links', 1,
    'eportfolio_enable', 1,
    'enable_prevent_download_url', 0,
    'personal_blog', 1,
    'personal_blog_commenting', 1,
    'personal_blog_rating', 1,
    'personal_blog_sharing', 1,
    'course_guest', 'link',
    'allow_rec_audio', 1,
    'allow_rec_video', 1,
    'show_always_collaboration', 0,
    'show_collaboration', 0,
    'version', ECLASS_VERSION);

$db->query("INSERT INTO `config` (`key`, `value`) VALUES " .
        implode(', ', array_fill(0, count($default_config) / 2, '(?s, ?s)')), $default_config);

store_mail_config();
update_upload_whitelists();

// table for cron parameters
$db->query("CREATE TABLE `cron_params` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `last_run` DATETIME NOT NULL) $tbl_options");

// tables for units module
$db->query("CREATE TABLE `course_units` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `comments` MEDIUMTEXT,
    `start_week` DATE,
    `finish_week` DATE,
    `visible` TINYINT(4),
    `public` TINYINT(4) NOT NULL DEFAULT 1,
    `order` INT(11) NOT NULL DEFAULT 0,
    `assign_to_specific` tinyint NOT NULL DEFAULT '0',
    `course_id` INT(11) NOT NULL,
    UNIQUE KEY `course_units_order` (`course_id`,`order`)) $tbl_options");

$db->query("CREATE TABLE course_units_to_specific (
        id INT auto_increment NOT NULL,
        unit_id INT NOT NULL,
        user_id INT NULL,
        group_id INT NULL,
      PRIMARY KEY (`id`),
      KEY `unit_id` (`unit_id`)) $tbl_options");

$db->query("CREATE TABLE `unit_resources` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `unit_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `comments` MEDIUMTEXT,
    `res_id` INT(11) NOT NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT '',
    `visible` TINYINT(4),
    `order` INT(11) NOT NULL DEFAULT 0,
    `date` DATETIME NOT NULL,
    `fc_type` INT(11) NOT NULL DEFAULT 3,
    `activity_title`  VARCHAR(50) NOT NULL DEFAULT '',
    `activity_id` VARCHAR(5) NOT NULL DEFAULT 'FC000',
    UNIQUE KEY `unit_resources_order` (`unit_id`,`order`)) $tbl_options");

$db->query("CREATE TABLE `actions_daily` (
    `id` int(11) NOT NULL auto_increment,
    `user_id` int(11) NOT NULL,
    `module_id` int(11) NOT NULL,
    `course_id` int(11) NOT NULL,
    `hits` int(11) NOT NULL,
    `duration` int(11) NOT NULL,
    `day` date NOT NULL,
    `last_update` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `actionsdailyindex` (`module_id`, `day`),
    KEY `actionsdailyuserindex` (`user_id`),
    KEY `actionsdailydayindex` (`day`),
    KEY `actionsdailymoduleindex` (`module_id`),
    KEY `actionsdailycourseindex` (`course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `actions_summary` (
    `id` int(11) NOT NULL auto_increment,
    `module_id` int(11) NOT NULL,
    `visits` int(11) NOT NULL,
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `duration` int(11) NOT NULL,
    `course_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `logins` (
    `id` int(11) NOT NULL auto_increment,
    `user_id` int(11) NOT NULL,
    `ip` char(45) NOT NULL default '0.0.0.0',
    `date_time` datetime NOT NULL,
    `course_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

// tc_sessions tables
$db->query("CREATE TABLE IF NOT EXISTS `tc_session` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) DEFAULT NULL,
    `title` varchar(255) DEFAULT NULL,
    `description` text,
    `start_date` datetime DEFAULT NULL,
    `end_date` datetime DEFAULT NULL,
    `public` enum('0','1') DEFAULT NULL,
    `active` enum('0','1') DEFAULT NULL,
    `running_at` int(11) DEFAULT NULL,
    `meeting_id` varchar(255) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
    `mod_pw` varchar(255) DEFAULT NULL,
    `att_pw` varchar(255) DEFAULT NULL,
    `unlock_interval` int(11) DEFAULT NULL,
    `external_users` text DEFAULT NULL,
    `participants` varchar(1000) DEFAULT NULL,
    `record` enum('true','false') DEFAULT 'false',
    `sessionUsers` int(11) DEFAULT 0,
    `options` text DEFAULT NULL,
    `id_session` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)) $tbl_options");

// tc_servers table
$db->query("CREATE TABLE IF NOT EXISTS `tc_servers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` varchar(255) NOT NULL,
    `hostname` varchar(255) NOT NULL,
    `ip` varchar(255) DEFAULT NULL,
    `port` varchar(255) DEFAULT NULL,
    `enabled` enum('true','false') DEFAULT NULL,
    `server_key` varchar(255) DEFAULT NULL,
    `username` varchar(255) DEFAULT NULL,
    `password` varchar(255) DEFAULT NULL,
    `api_url` varchar(255) DEFAULT NULL,
    `webapp` varchar(255) DEFAULT NULL,
    `max_rooms` int(11) DEFAULT NULL,
    `max_users` int(11) DEFAULT NULL,
    `enable_recordings` enum('true','false') DEFAULT NULL,
    `weight` int(11) DEFAULT NULL,
    `screenshare` varchar(255) DEFAULT NULL,
    `all_courses` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `hostname` (`hostname`),
    KEY `idx_tc_servers` (`hostname`)) $tbl_options");


$db->query("CREATE TABLE `tc_attendance` (
    `id` int(11) NOT NULL DEFAULT '0',
    `meetingid` varchar(42) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `bbbuserid` varchar(20) DEFAULT NULL,
    `totaltime` int(11) NOT NULL DEFAULT '0',
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`,`meetingid`),
    KEY `id` (`id`),
    KEY `meetingid` (`meetingid`)) $tbl_options");

$db->query("CREATE TABLE `tc_log` (
    `id` int(11) NOT NULL,
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `meetingid` varchar(42) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `bbbuserid` varchar(255) DEFAULT NULL,
    `fullName` varchar(255) DEFAULT NULL,
    `type` varchar(255) default 'bbb',
    PRIMARY KEY (`id`),
    KEY `userid` (`bbbuserid`),
    KEY `fullName` (`fullName`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `course_external_server` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `external_server` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`external_server`, `course_id`)) $tbl_options");

// plagiarism tool table
$db->query("CREATE TABLE `ext_plag_connection` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `type` int(1) unsigned NOT NULL DEFAULT '1',
        `file_id` int(11) NOT NULL,
        `remote_file_id` int(11) DEFAULT NULL,
        `submission_id` int(11) DEFAULT NULL,
        PRIMARY KEY (`id`)) $tbl_options");


$db->query("CREATE TABLE IF NOT EXISTS `course_settings` (
    `setting_id` INT(11) NOT NULL,
    `course_id` INT(11) NOT NULL,
    `value` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`setting_id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `students_semester` TINYINT(4) NOT NULL DEFAULT 1,
    `range` TINYINT(4) NOT NULL DEFAULT 10,
    `active` TINYINT(1) NOT NULL DEFAULT 0,
    `title` VARCHAR(255) DEFAULT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL,
    `passing_grade` FLOAT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_activities` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_id` MEDIUMINT(11) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `activity_type` INT(11) DEFAULT NULL,
    `date` DATETIME DEFAULT NULL,
    `description` TEXT NOT NULL,
    `weight` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `module_auto_id` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `module_auto_type` TINYINT(4) NOT NULL DEFAULT 0,
    `auto` TINYINT(4) NOT NULL DEFAULT 0,
    `visible` TINYINT(4) NOT NULL DEFAULT 0,
    `extra_credit` BOOL DEFAULT 0 NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_book` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_activity_id` MEDIUMINT(11) NOT NULL,
    `uid` int(11) NOT NULL DEFAULT 0,
    `grade` FLOAT NOT NULL DEFAULT -1,
    `comments` TEXT NOT NULL,
    UNIQUE KEY activity_uid (gradebook_activity_id, uid)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_users` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_id` MEDIUMINT(11) NOT NULL,
    `uid` int(11) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `oai_record` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL UNIQUE,
    `oai_identifier` varchar(255) DEFAULT NULL,
    `oai_metadataprefix` varchar(255) DEFAULT 'oai_dc',
    `oai_set` varchar(255) DEFAULT 'class:course',
    `datestamp` datetime DEFAULT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `oai_identifier` (`oai_identifier`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `oai_metadata` (
    `id` int(11) NOT NULL auto_increment PRIMARY KEY,
    `oai_record` int(11) NOT NULL references oai_record(id),
    `field` varchar(255) NOT NULL,
    `value` text,
    INDEX `field_index` (`field`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `note` (
    `id` int(11) NOT NULL auto_increment,
    `user_id` int(11) NOT NULL,
    `title` varchar(300),
    `content` text NOT NULL,
    `date_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    `order` mediumint(11) NOT NULL default 0,
    `reference_obj_module` mediumint(11) default NULL,
    `reference_obj_type` enum('course','personalevent','user','course_ebook','course_event','course_assignment','course_document','course_link','course_exercise','course_learningpath','course_video','course_videolink') default NULL,
    `reference_obj_id` int(11) default NULL,
    `reference_obj_course` int(11) default NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `idx_queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL UNIQUE,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `idx_queue_async` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `request_type` VARCHAR(255) NOT NULL,
    `resource_type` VARCHAR(255) NOT NULL,
    `resource_id` int(11) NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `theme_options` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(300) NOT NULL,
    `styles` LONGTEXT NOT NULL,
    `version` TINYINT,
    PRIMARY KEY (`id`)) $tbl_options");

// Tags tables
$db->query("CREATE TABLE IF NOT EXISTS `tag_element_module` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` int(11) NOT NULL,
    `module_id` int(11) NULL,
    `element_id` int(11) NULL,
    `user_id` int(11) NOT NULL,
    `date` DATETIME DEFAULT NULL,
    `tag_id` int(11) NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS tag (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    UNIQUE KEY (name)) $tbl_options");

// Recycle object table
$db->query("CREATE TABLE IF NOT EXISTS `recyclebin` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tablename` varchar(100) NOT NULL,
    `entryid` int(11) NOT NULL,
    `entrydata` varchar(4000) NOT NULL,
    KEY `entryid` (`entryid`), KEY `tablename` (`tablename`)) $tbl_options");

// Auto-enroll rules tables
$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `status` TINYINT(4) NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule_department` (
    `rule` INT(11) NOT NULL,
    `department` INT(11) NOT NULL,
    PRIMARY KEY (rule, department),
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_course` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rule` INT(11) NOT NULL DEFAULT 0,
    `course_id` INT(11) NOT NULL,
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_department` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rule` INT(11) NOT NULL DEFAULT 0,
    `department_id` INT(11) NOT NULL,
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES hierarchy(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `activity_heading` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order` INT(11) NOT NULL DEFAULT 0,
    `heading` TEXT NOT NULL,
    `required` BOOL NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `activity_content` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `heading_id` INT(11) NOT NULL DEFAULT 0,
    `content` TEXT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
    FOREIGN KEY (heading_id) REFERENCES activity_heading(id) ON DELETE CASCADE,
    UNIQUE KEY `heading_course` (`course_id`,`heading_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `widget` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `class` varchar(400) NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `widget_widget_area` (
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

// Conference table
$db->query("CREATE TABLE IF NOT EXISTS `conference` (
    `conf_id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `conf_title` text NOT NULL,
    `conf_description` text DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` varchar(255) default '0',
    `group_id` varchar(255) default '0',
    `chat_activity` boolean not null default false,
    `agent_created` boolean not null default false,
    `chat_activity_id` int(11),
    `agent_id` int(11),
    PRIMARY KEY (`conf_id`,`course_id`)) $tbl_options");

// Colmooc user table
$db->query("CREATE TABLE IF NOT EXISTS `colmooc_user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `colmooc_id` INT(11) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

// Colmooc user session table
$db->query("CREATE TABLE IF NOT EXISTS `colmooc_user_session` (
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

// Colmooc pair log table
$db->query("CREATE TABLE IF NOT EXISTS `colmooc_pair_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `activity_id` INT(11) NOT NULL,
    `moderator_id` INT(11) NOT NULL,
    `partner_id` INT(11) NOT NULL,
    `session_status` TINYINT(4) NOT NULL DEFAULT 0,
    `created` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (moderator_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

// Course Category tables
$db->query("CREATE TABLE IF NOT EXISTS `category` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` TEXT NOT NULL,
    `ordering` INT(11),
    `multiple` BOOLEAN NOT NULL DEFAULT TRUE,
    `searchable` BOOLEAN NOT NULL DEFAULT TRUE,
    `active` BOOLEAN NOT NULL DEFAULT TRUE
    ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `category_value` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT(11) NOT NULL REFERENCES category(id),
    `name` TEXT NOT NULL,
    `ordering` INT(11),
    `active` BOOLEAN NOT NULL DEFAULT TRUE
    ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `course_category` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL REFERENCES course(id),
    `category_value_id` INT(11) NOT NULL REFERENCES category_value(id)
    ) $tbl_options");

// Gamification Tables
$db->query("CREATE TABLE `certificate_template` (
    `id` mediumint(8) not null auto_increment primary key,
    `name` varchar(255) not null,
    `description` text,
    `filename` varchar(255),
    `orientation` varchar(10),
    `all_courses` tinyint(1) not null default 1
) $tbl_options");

$db->query("CREATE TABLE `badge_icon` (
    `id` mediumint(8) not null auto_increment primary key,
    `name` varchar(255) not null,
    `description` text,
    `filename` varchar(255)
) $tbl_options");

$db->query("CREATE TABLE `certificate` (
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
  `unit_id` INT(11) NOT NULL DEFAULT 0,
  `session_id` INT(11) NOT NULL DEFAULT 0,
  index `certificate_course` (`course_id`),
  foreign key (`course_id`) references `course` (`id`),
  foreign key (`template`) references `certificate_template`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `badge` (
  `id` int(11) not null auto_increment primary key,
  `course_id` int(11) not null,
  `unit_id` int(11) not null default 0,
  `session_id` int(11) not null default 0,
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

$db->query("CREATE TABLE `user_certificate` (
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

$db->query("CREATE TABLE `user_badge` (
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

$db->query("CREATE TABLE `certificate_criterion` (
  `id` int(11) not null auto_increment primary key,
  `certificate` int(11) not null,
  `activity_type` varchar(255),
  `module` int(11),
  `resource` int(11),
  `threshold` decimal(7,2),
  `operator` varchar(20),
  foreign key (`certificate`) references `certificate`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `badge_criterion` (
  `id` int(11) not null auto_increment primary key,
  `badge` int(11) not null,
  `activity_type` varchar(255),
  `module` int(11),
  `resource` int(11),
  `threshold` decimal(7,2),
  `operator` varchar(20),
  foreign key (`badge`) references `badge`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `user_certificate_criterion` (
  `id` int(11) not null auto_increment primary key,
  `user` int(11) not null,
  `certificate_criterion` int(11) not null,
  `created` datetime,
  unique key `user_certificate_criterion` (`user`, `certificate_criterion`),
  foreign key (`user`) references `user`(`id`),
  foreign key (`certificate_criterion`) references `certificate_criterion`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `user_badge_criterion` (
  `id` int(11) not null auto_increment primary key,
  `user` int(11) not null,
  `badge_criterion` int(11) not null,
  `created` datetime,
  unique key `user_badge_criterion` (`user`, `badge_criterion`),
  foreign key (`user`) references `user`(`id`),
  foreign key (`badge_criterion`) references `badge_criterion`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `certified_users` (
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
  `user_id` INT DEFAULT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL
  ) $tbl_options");

$db->query("CREATE TABLE `course_certificate_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `certificate_template_id` mediumint(8) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`certificate_template_id`) REFERENCES `certificate_template` (`id`) ON DELETE CASCADE
) $tbl_options");

$db->query("CREATE TABLE `course_prerequisite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) not null,
  `prerequisite_course` int(11) not null,
  PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `unit_prerequisite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) not null,
  `unit_id` int(11) not null,
  `prerequisite_unit` int(11) not null,
  PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `session_prerequisite` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `course_id` int(11) not null,
    `session_id` int(11) not null,
    `prerequisite_session` int(11) not null,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `user_consent` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  has_accepted BOOL NOT NULL DEFAULT 0,
  ts DATETIME,
  PRIMARY KEY (id),
  UNIQUE KEY (user_id),
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) $tbl_options");


// `request` tables (aka `ticketing`)
$db->query("CREATE TABLE IF NOT EXISTS request_type (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` MEDIUMTEXT NOT NULL,
    `description` MEDIUMTEXT NULL DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS request (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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

$db->query("CREATE TABLE IF NOT EXISTS `request_field` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type_id` INT(11) UNSIGNED NOT NULL,
    `name` MEDIUMTEXT NOT NULL,
    `description` MEDIUMTEXT NULL DEFAULT NULL,
    `datatype` INT(11) NOT NULL,
    `sortorder` INT(11) NOT NULL DEFAULT 0,
    `values` MEDIUMTEXT DEFAULT NULL,
    FOREIGN KEY (type_id) REFERENCES request_type(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `request_field_data` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT(11) UNSIGNED NOT NULL,
    `field_id` INT(11) UNSIGNED NOT NULL,
    `data` TEXT NOT NULL,
    FOREIGN KEY (field_id) REFERENCES request_field(id) ON DELETE CASCADE,
    UNIQUE KEY (`request_id`, `field_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS request_watcher (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) NOT NULL,
    `type` TINYINT(4) NOT NULL,
    `notification` TINYINT(4) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (request_id, user_id),
    FOREIGN KEY (request_id) REFERENCES request(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS request_action (
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

$db->query("CREATE TABLE `user_settings` (
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
    ON DELETE CASCADE ON UPDATE CASCADE ) $tbl_options");

// learning analytics

$db->query("CREATE TABLE `analytics` (
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

$db->query("CREATE TABLE `analytics_element` (
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

$db->query("CREATE TABLE `user_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `analytics_element_id` int(11) NOT NULL,
  `value` float NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)) $tbl_options");

// Course pages

$db->query("CREATE TABLE `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `path` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `visible` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `course_id_index` (`course_id`)) $tbl_options");

// H5P

$db->query("CREATE TABLE h5p_library (
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

$db->query("CREATE TABLE h5p_library_dependency (
    id INT(10) NOT NULL AUTO_INCREMENT,
    library_id INT(10) NOT NULL,
    required_library_id INT(10) NOT NULL,
    dependency_type VARCHAR(255) NOT NULL,
  PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE h5p_library_translation (
    id INT(10) NOT NULL,
    library_id INT(10) NOT NULL,
    language_code VARCHAR(255) NOT NULL,
    language_json TEXT NOT NULL,
  PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE h5p_content (
    id INT(10) NOT NULL AUTO_INCREMENT,
    title varchar(255),
    main_library_id INT(10) NOT NULL,
    params LONGTEXT,
    course_id INT(11) NOT NULL,
    enabled TINYINT(4) NOT NULL DEFAULT 1,
    reuse_enabled TINYINT(4) NOT NULL DEFAULT 1,
    PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE h5p_content_dependency (
    id INT(10) NOT NULL AUTO_INCREMENT,
    content_id INT(10) NOT NULL,
    library_id INT(10) NOT NULL,
    dependency_type VARCHAR(10) NOT NULL,
  PRIMARY KEY(id)) $tbl_options");

// tables for CCE API
$db->query("CREATE TABLE `api_grade_analytics` (
    id INT(10) NOT NULL AUTO_INCREMENT,
    user_uuid VARCHAR(40) NOT NULL,
    course_uuid VARCHAR(40) NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE `api_course_completion` (
    id INT(10) NOT NULL AUTO_INCREMENT,
    user_id INT(10) NOT NULL,
    course_id INT(10) NOT NULL,
    is_locked BOOL NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE api_token (
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

$db->query("CREATE TABLE `course_invitation` (
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

$db->query("CREATE TABLE `minedu_departments` (
    `MineduID` TEXT NOT NULL,
    `Institution` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `School` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `Department` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `Comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL)
    $tbl_options");

$db->query("CREATE TABLE `minedu_department_association` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `minedu_id` int(11) NOT NULL DEFAULT 0,
    `department_id` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`department_id`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $tbl_options");

$db->query('CREATE TABLE `login_lock` (
   `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `user_id` INT(11) NOT NULL,
   `session_id` VARCHAR(48) NOT NULL COLLATE ascii_bin,
   `ts` DATETIME NOT NULL,
   FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
   UNIQUE KEY (session_id)) CHARACTER SET ascii ENGINE=InnoDB');

$db->query("CREATE TABLE `zoom_user` (
      `user_id` INT(10) NOT NULL,
      `id` varchar(45) NOT NULL,
      `first_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
      `last_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
      `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
      `type` TINYINT(1) NOT NULL DEFAULT 1,
      `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (user_id)) $tbl_options");

$db->query("CREATE TABLE `mod_session` (
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
        `consent` int(11) NOT NULL DEFAULT 1,
        PRIMARY KEY(id),
        FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `mod_session_users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `session_id` INT(11) NOT NULL DEFAULT 0,
            `participants` INT(11) NOT NULL DEFAULT 0,
            `is_accepted` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY(id),
            FOREIGN KEY (session_id) REFERENCES mod_session(id) ON DELETE CASCADE) $tbl_options");


$db->query("CREATE TABLE `session_resources` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `session_id` INT(11) NOT NULL DEFAULT 0,
                            `title` VARCHAR(255) NOT NULL DEFAULT '',
                            `comments` MEDIUMTEXT,
                            `res_id` INT(11) NOT NULL DEFAULT 0,
                            `type` VARCHAR(255) NOT NULL DEFAULT '',
                            `visible` TINYINT(4),
                            `order` INT(11) NOT NULL DEFAULT 0,
                            `date` DATETIME NOT NULL,
                            `doc_id` INT(11) NOT NULL DEFAULT 0,
                            `is_completed` INT(11) NOT NULL DEFAULT 0,
                            `from_user` INT(11) NOT NULL DEFAULT 0,
                            `deliverable_comments` TEXT DEFAULT NULL,
                            `passage` TEXT DEFAULT NULL,
                            PRIMARY KEY(id),
                            FOREIGN KEY (session_id) REFERENCES mod_session(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `mod_session_completion` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `course_id` int(11) NOT NULL,
                            `session_id` int(11) NOT NULL,
                            PRIMARY KEY (`id`),
                            FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
                            FOREIGN KEY (`session_id`) REFERENCES `mod_session` (`id`) ON DELETE CASCADE) $tbl_options");


$db->query("CREATE TABLE `session_user_material` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `course_id` int(11) NOT NULL,
          `session_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `content` MEDIUMTEXT,
          PRIMARY KEY (`id`),
          FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
          FOREIGN KEY (`session_id`) REFERENCES `mod_session` (`id`) ON DELETE CASCADE) $tbl_options");



$_SESSION['theme'] = 'modern';

importThemes();

// create indices
$db->query("CREATE INDEX `actions_daily_index` ON actions_daily(user_id, module_id, course_id)");
$db->query("CREATE INDEX `actions_summary_index` ON actions_summary(module_id, course_id)");
$db->query("CREATE INDEX `agenda_index` ON agenda(course_id)");
$db->query("CREATE INDEX `ann_index` ON announcement(course_id)");
$db->query("CREATE INDEX `assignment_index` ON assignment(course_id)");
$db->query("CREATE INDEX `assign_submit_index` ON assignment_submit(uid, assignment_id)");
$db->query('CREATE INDEX assignment_id on assignment_submit(assignment_id)');
$db->query("CREATE INDEX `assign_spec_index` ON assignment_to_specific(user_id)");
$db->query('CREATE INDEX group_id on assignment_to_specific (group_id)');
$db->query('CREATE INDEX assignment_id on assignment_to_specific(assignment_id)');
$db->query("CREATE INDEX `att_index` ON attendance(course_id)");
$db->query("CREATE INDEX `att_act_index` ON attendance_activities(attendance_id)");
$db->query("CREATE INDEX `att_book_index` ON attendance_book(attendance_activity_id)");
$db->query("CREATE INDEX `tc_index` ON tc_session(course_id)");
$db->query("CREATE INDEX `course_index` ON course(code)");
$db->query('CREATE INDEX `cd_type_index` ON course_description (`type`)');
$db->query('CREATE INDEX `cd_cid_type_index` ON course_description (course_id, `type`)');
$db->query('CREATE INDEX `cid` ON course_description (course_id)');
$db->query('CREATE INDEX `visible_cid` ON course_module (visible, course_id)');
$db->query("CREATE INDEX `crev_index` ON course_review(course_id)");
$db->query("CREATE INDEX `course_units_index` ON course_units (course_id, `order`)");
$db->query("CREATE INDEX `cu_index` ON course_user (user_id, status)");
$db->query('CREATE INDEX `doc_path_index` ON document (course_id, subsystem, path)');
$db->query("CREATE INDEX `drop_att_index` ON dropbox_attachment(msg_id)");
$db->query("CREATE INDEX `drop_index` ON dropbox_index(recipient_id, is_read)");
$db->query("CREATE INDEX `drop_msg_index` ON dropbox_msg(course_id, author_id)");
$db->query("CREATE INDEX `ebook_index` ON ebook(course_id)");
$db->query("CREATE INDEX `ebook_sec_index` ON ebook_section(ebook_id)");
$db->query("CREATE INDEX `ebook_sub_sec_index` ON ebook_subsection(section_id)");
$db->query('CREATE INDEX `exer_index` ON exercise (course_id)');
$db->query('CREATE INDEX `eur_index1` ON exercise_user_record (eid)');
$db->query('CREATE INDEX `eur_index2` ON exercise_user_record (uid)');
$db->query('CREATE INDEX `ear_index1` ON exercise_answer_record (eurid)');
$db->query('CREATE INDEX `ear_index2` ON exercise_answer_record (question_id)');
$db->query('CREATE INDEX `eq_index` ON exercise_question (course_id)');
$db->query('CREATE INDEX `ea_index` ON exercise_answer (question_id)');
$db->query("CREATE INDEX `for_index` ON forum(course_id)");
$db->query("CREATE INDEX `for_cat_index` ON forum_category(course_id)");
$db->query("CREATE INDEX `for_not_index` ON forum_notify(course_id)");
$db->query("CREATE INDEX `for_post_index` ON forum_post(topic_id)");
$db->query("CREATE INDEX `for_topic_index` ON forum_topic(forum_id)");
$db->query("CREATE INDEX `glos_index` ON glossary(course_id)");
$db->query("CREATE INDEX `glos_cat_index` ON glossary_category(course_id)");
$db->query("CREATE INDEX `grade_index` ON gradebook(course_id)");
$db->query("CREATE INDEX `grade_act_index` ON gradebook_activities(gradebook_id)");
$db->query("CREATE INDEX `grade_book_index` ON gradebook_book(gradebook_activity_id)");
$db->query("CREATE INDEX `group_index` ON `group`(course_id)");
$db->query("CREATE INDEX `gr_prop_index` ON group_properties(course_id)");
$db->query("CREATE INDEX `hier_index` ON hierarchy(code,name(20))");
$db->query("CREATE INDEX lft_rgt on hierarchy(lft,rgt)");
$db->query("CREATE INDEX `link_index` ON link(course_id)");
$db->query("CREATE INDEX `link_cat_index` ON link_category(course_id)");
$db->query("CREATE INDEX `cmid` ON log (course_id, module_id)");
$db->query("CREATE INDEX `logins_id` ON logins(user_id, course_id)");
$db->query("CREATE INDEX `loginout_id` ON loginout(id_user)");
$db->query("CREATE INDEX `lp_as_id` ON lp_asset(module_id)");
$db->query("CREATE INDEX `lp_id` ON lp_learnPath(course_id)");
$db->query("CREATE INDEX `lp_mod_id` ON lp_module(course_id)");
$db->query("CREATE INDEX `lp_rel_lp_id` ON lp_rel_learnPath_module(learnPath_id, module_id)");
$db->query("CREATE INDEX `optimize` ON lp_user_module_progress (user_id, learnPath_module_id)");
$db->query("CREATE INDEX `poll_index` ON poll(course_id)");
$db->query("CREATE INDEX `poll_ans_id` ON poll_user_record(pid, uid)");
$db->query("CREATE INDEX `poll_q_id` ON poll_question(pid)");
$db->query("CREATE INDEX `poll_qa_id` ON poll_question_answer(pqid)");
$db->query("CREATE INDEX `unit_res_index` ON unit_resources (unit_id, visible, res_id)");
$db->query("CREATE INDEX `u_id` ON user(username)");
$db->query('CREATE INDEX `surname` on user(surname)');
$db->query("CREATE INDEX `cid` ON video (course_id)");
$db->query("CREATE INDEX `cid` ON videolink (course_id)");
$db->query("CREATE INDEX `wiki_id` ON wiki_locks(wiki_id)");
$db->query("CREATE INDEX `wiki_pages_id` ON wiki_pages(wiki_id)");
$db->query("CREATE INDEX `wiki_pcon_id` ON wiki_pages_content(pid)");
$db->query("CREATE INDEX `wik_prop_id` ON  wiki_properties(course_id)");
$db->query("CREATE INDEX `user_notes` ON note (user_id)");
$db->query('CREATE INDEX `user_events` ON personal_calendar (user_id)');
$db->query('CREATE INDEX `user_events_dates` ON personal_calendar (user_id,start)');
$db->query('CREATE INDEX `agenda_item_dates` ON agenda (course_id,start)');
$db->query('CREATE INDEX `deadline_dates` ON assignment (course_id, deadline)');
$db->query('CREATE INDEX `idx_queue_cid` ON `idx_queue` (course_id)');
$db->query('CREATE INDEX `idx_queue_async_uid` ON `idx_queue_async` (user_id)');
$db->query('CREATE INDEX `attendance_users_aid` ON `attendance_users` (attendance_id)');
$db->query("CREATE INDEX `uid_attendance_id` on attendance_users (uid,attendance_id)");
$db->query('CREATE INDEX `gradebook_users_gid` ON `gradebook_users` (gradebook_id)');
$db->query("CREATE INDEX `uid_gradebook_id` on gradebook_users (uid,gradebook_id)");
$db->query("CREATE INDEX `tag_element_index` ON `tag_element_module` (course_id, module_id, element_id)");
$db->query('CREATE INDEX `actions_daily_mcd` ON `actions_daily` (module_id, course_id, day)');
$db->query('CREATE INDEX `actions_daily_hdi` ON `actions_daily` (hits, duration, id)');
$db->query('CREATE INDEX `loginout_ia` ON `loginout` (id_user, action)');
$db->query('CREATE INDEX `announcement_cvo` ON `announcement` (course_id, visible, `order`)');
$db->query("CREATE INDEX `actions_summary_module_id` ON actions_summary(module_id)");
$db->query("CREATE INDEX `actions_summary_course_id` ON actions_summary(course_id)");
$db->query('CREATE INDEX `doc_course_id` ON document (course_id)');
$db->query('CREATE INDEX `doc_subsystem` ON document (subsystem)');
$db->query('CREATE INDEX `doc_path` ON document (path)');
$db->query("CREATE INDEX `drop_index_recipient_id` ON dropbox_index(recipient_id)");
$db->query("CREATE INDEX `drop_index_is_read` ON dropbox_index(is_read)");
$db->query('CREATE INDEX `drop_index2` on dropbox_index(recipient_id,deleted,msg_id)');
$db->query("CREATE INDEX `drop_msg_index_course_id` ON dropbox_msg(course_id)");
$db->query("CREATE INDEX `drop_msg_index_author_id` ON dropbox_msg(author_id)");
$db->query('CREATE INDEX `ewq_index_question_id` ON exercise_with_questions (question_id)');
$db->query('CREATE INDEX `ewq_index_exercise_id` ON exercise_with_questions (exercise_id)');
$db->query("CREATE INDEX `gr_mem_user_id` ON group_members(user_id)");
$db->query("CREATE INDEX `gr_mem_group_id` ON group_members(group_id)");
$db->query("CREATE INDEX `log_course_id` ON log (course_id)");
$db->query("CREATE INDEX `log_module_id` ON log (module_id)");
$db->query("CREATE INDEX `logins_id_user_id` ON logins(user_id)");
$db->query("CREATE INDEX `logins_id_course_id` ON logins(course_id)");
$db->query("CREATE INDEX `lp_rel_learnPath_id` ON lp_rel_learnPath_module(learnPath_id)");
$db->query("CREATE INDEX `lp_rel_module_id` ON lp_rel_learnPath_module(module_id)");
$db->query("CREATE INDEX `lp_learnPath_module_id` ON lp_user_module_progress (learnPath_module_id)");
$db->query("CREATE INDEX `lp_user_id` ON lp_user_module_progress (user_id)");
$db->query('CREATE INDEX `learnPath_id` on lp_user_module_progress(learnPath_id)');
$db->query("CREATE INDEX `unit_res_unit_id` ON unit_resources (unit_id)");
$db->query("CREATE INDEX `unit_res_visible` ON unit_resources (visible)");
$db->query("CREATE INDEX `unit_res_res_id` ON unit_resources (res_id)");
$db->query('CREATE INDEX `pcal_start` ON personal_calendar (start)');
$db->query('CREATE INDEX `user_id_start` on personal_calendar(user_id,start)');
$db->query('CREATE INDEX `agenda_start` ON agenda (start)');
$db->query('CREATE INDEX `source_event_id` on agenda (source_event_id)');
$db->query('CREATE INDEX `assignment_deadline` ON assignment (deadline)');
$db->query('CREATE INDEX course_id on course_settings (course_id)');
$db->query('CREATE INDEX course_id on video_category(course_id)');
$db->query('CREATE INDEX rid_rtype on comments (rid,rtype)');
$db->query('CREATE INDEX course_id on exercise_question_cats(course_id)');
$db->query('CREATE INDEX course_id on blog_post(course_id)');
$db->query('CREATE INDEX reference_obj_course on note(reference_obj_course)');
$db->query('CREATE INDEX qid on poll_answer_record(qid)');
$db->query('CREATE INDEX course_id on forum_user_stats(course_id)');
$db->query('CREATE INDEX user_id on note(user_id)');
$db->query('CREATE INDEX course_id on conference(course_id)');
$db->query('CREATE INDEX course_id_enabled on lti_apps(course_id,enabled)');
$db->query('CREATE INDEX course_id_enabled on course_lti_publish(course_id, enabled)');
$db->query('CREATE INDEX am on `user`(am)');
$db->query('CREATE INDEX forum_id on `group`(forum_id)');
$db->query('CREATE INDEX email on `user`(email)');
$db->query('CREATE INDEX exercise_id on exercise_to_specific(exercise_id)');
$db->query('CREATE INDEX category_visible ON video(category,visible)');
$db->query('CREATE INDEX courseID on analytics(courseID)');
$db->query('CREATE INDEX useractionwhen on loginout (id_user,action,`when` desc)');
$db->query('CREATE INDEX group_id on wiki_properties(group_id)');
$db->query('CREATE INDEX consumerid on lti_publish_lti2_context(consumerid)');
$db->query('CREATE INDEX consumerid on lti_publish_lti2_nonce(consumerid)');
$db->query('CREATE INDEX consumerid on lti_publish_lti2_resource_link(consumerid)');
$db->query('CREATE INDEX contextid on lti_publish_lti2_resource_link(contextid)');
$db->query('CREATE INDEX primaryresourcelinkid on lti_publish_lti2_resource_link(primaryresourcelinkid)');
$db->query('CREATE INDEX consumerid on lti_publish_lti2_tool_proxy(consumerid)');
$db->query('CREATE INDEX resourcelinkid on lti_publish_lti2_user_result(resourcelinkid)');
$db->query('CREATE INDEX publish_id on course_lti_publish_user_enrolments(publish_id)');
$db->query('CREATE INDEX user_id on course_lti_publish_user_enrolments(user_id)');
$db->query('CREATE INDEX publish_id on course_lti_enrol_users(publish_id)');
$db->query('CREATE INDEX user_id on course_lti_enrol_users(user_id)');

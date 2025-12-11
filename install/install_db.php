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

$tbl_options = 'DEFAULT CHARACTER SET=utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';

// ********************************************
// create tables
// ********************************************
// flipped classroom course type
$db->query("CREATE TABLE IF NOT EXISTS `course_activities` (
    `id` INT NOT NULL auto_increment,
    `activity_id` varchar(4) NOT NULL,
    `activity_type` TINYINT NOT NULL,
    `visible` INT NOT NULL,
    `unit_id` INT NOT NULL,
    `module_id` INT NOT NULL,
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
    `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");
// end of flipped classroom

$db->query("CREATE TABLE IF NOT EXISTS `course_module` (
  `id` INT NOT NULL auto_increment,
  `module_id` INT NOT NULL,
  `visible` TINYINT NOT NULL,
  `course_id` INT NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `module_course` (`module_id`,`course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS module_disable (
    module_id INT NOT NULL PRIMARY KEY) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS module_disable_collaboration (
    module_id INT NOT NULL PRIMARY KEY) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `log` (
  `id` INT NOT NULL auto_increment,
  `user_id` INT NOT NULL default 0,
  `course_id` INT NOT NULL default 0,
  `module_id` INT NOT NULL default 0,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `action_type` INT NOT NULL default 0,
  `ts` datetime NOT NULL,
  `ip` varchar(45) NOT NULL default 0,
  PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `log_archive` (
  `id` INT NOT NULL auto_increment,
  `user_id` INT NOT NULL default 0,
  `course_id` INT NOT NULL default 0,
  `module_id` INT NOT NULL default 0,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `action_type` INT NOT NULL default 0,
  `ts` datetime NOT NULL,
  `ip` varchar(45) NOT NULL default 0,
  PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE `announcement` (
    `id` INT NOT NULL auto_increment,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `content` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `date` DATETIME NOT NULL,
    `course_id` INT NOT NULL DEFAULT 0,
    `order` MEDIUMINT NOT NULL DEFAULT 0,
    `visible` TINYINT NOT NULL DEFAULT 0,
    `start_display` DATETIME DEFAULT NULL,
    `stop_display` DATETIME DEFAULT NULL,
    PRIMARY KEY (id)) $tbl_options");

$db->query("CREATE TABLE `admin_announcement` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `body` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `date` DATETIME NOT NULL,
    `begin` DATETIME DEFAULT NULL,
    `end` DATETIME DEFAULT NULL,
    `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
    `order` MEDIUMINT NOT NULL DEFAULT 0,
    `important` INT NOT NULL DEFAULT 0,
    `visible` TINYINT) $tbl_options");

$db->query("CREATE TABLE `agenda` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `start` DATETIME NOT NULL,
    `end` DATETIME DEFAULT NULL,
    `duration` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `visible` TINYINT,
    `recursion_period` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `recursion_end` date DEFAULT NULL,
    `source_event_id` INT DEFAULT NULL)
    $tbl_options");

$db->query("CREATE TABLE `course` (
  `id` INT NOT NULL auto_increment,
  `uuid` VARCHAR(40) NOT NULL DEFAULT 0,
  `code` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
  `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `keywords` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `course_license` TINYINT NOT NULL DEFAULT 0,
  `visible` TINYINT NOT NULL,
  `prof_names` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `public_code` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL,
  `updated` DATETIME NULL,
  `doc_quota` FLOAT NOT NULL default '104857600',
  `video_quota` FLOAT NOT NULL default '104857600',
  `group_quota` FLOAT NOT NULL default '104857600',
  `dropbox_quota` FLOAT NOT NULL default '104857600',
  `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `glossary_expand` BOOL NOT NULL DEFAULT 0,
  `glossary_index` BOOL NOT NULL DEFAULT 1,
  `view_type` VARCHAR(255) NOT NULL DEFAULT 'units',
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `description` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `home_layout` TINYINT NOT NULL DEFAULT 1,
  `course_image` VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL,
  `flipped_flag` INT NOT NULL DEFAULT 0,
  `lectures_model` INT NOT NULL DEFAULT 0,
  `view_units` INT NOT NULL DEFAULT 1,
  `popular_course` INT NOT NULL DEFAULT 0,
  `is_collaborative` INT NOT NULL DEFAULT 0,
  `daily_access_limit` INT NULL,
  PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE `course_user` (
      `course_id` INT NOT NULL DEFAULT 0,
      `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
      `status` TINYINT NOT NULL DEFAULT 0,
      `tutor` INT NOT NULL DEFAULT 0,
      `editor` INT NOT NULL DEFAULT 0,
      `course_reviewer` TINYINT NOT NULL DEFAULT 0,
      `reviewer` INT NOT NULL DEFAULT 0,
      `reg_date` DATETIME NOT NULL,
      `receive_mail` BOOL NOT NULL DEFAULT 1,
      `document_timestamp` datetime NOT NULL,
      `favorite` datetime DEFAULT NULL,
      `can_view_course` TINYINT NOT NULL DEFAULT 1,
      PRIMARY KEY (course_id, user_id)) $tbl_options");

$db->query("CREATE TABLE `course_user_request` (
    `id` INT unsigned NOT NULL AUTO_INCREMENT,
    `uid` INT NOT NULL,
    `course_id` INT NOT NULL,
    `comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `comment_rejected` text,
    `status` INT NOT NULL,
    `ts` datetime NOT NULL,
    `ts_update` datetime DEFAULT NULL,
    PRIMARY KEY (`id`))  $tbl_options");

$db->query("CREATE TABLE `course_description_type` (
    `id` smallint NOT NULL AUTO_INCREMENT,
    `title` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `syllabus` TINYINT DEFAULT 0,
    `objectives` TINYINT DEFAULT 0,
    `bibliography` TINYINT DEFAULT 0,
    `teaching_method` TINYINT DEFAULT 0,
    `assessment_method` TINYINT DEFAULT 0,
    `prerequisites` TINYINT DEFAULT 0,
    `featured_books` TINYINT DEFAULT 0,
    `instructors` TINYINT DEFAULT 0,
    `target_group` TINYINT DEFAULT 0,
    `active` TINYINT DEFAULT 1,
    `order` INT NOT NULL,
    `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
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
//$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC17',0,0,0,0)");
$db->query("INSERT INTO `course_activities` (`activity_id`, `activity_type`, `visible`,`unit_id`,`module_id`) VALUES ('FC18',1,0,0,0)");

$db->query("CREATE TABLE IF NOT EXISTS `course_description` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `comments` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `type` SMALLINT,
    `visible` TINYINT DEFAULT 0,
    `order` INT NOT NULL,
    `update_dt` datetime NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `course_review` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `is_certified` BOOL NOT NULL DEFAULT 0,
    `level` TINYINT NOT NULL DEFAULT 0,
    `last_review` DATETIME NOT NULL,
    `last_reviewer` INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY cid (course_id)) $tbl_options");

$db->query("CREATE TABLE `user` (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(40) NOT NULL DEFAULT 0,
    surname VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    givenname VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    username VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL UNIQUE KEY ,
    password VARCHAR(255) NOT NULL DEFAULT 'empty',
    email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    parent_email VARCHAR(255) NOT NULL DEFAULT '',
    status TINYINT NOT NULL DEFAULT " . USER_STUDENT . ",
    phone VARCHAR(20) DEFAULT '',
    am VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT '',
    registered_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    lang VARCHAR(16) NOT NULL DEFAULT 'el',
    description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    has_icon TINYINT NOT NULL DEFAULT 0,
    verified_mail TINYINT NOT NULL DEFAULT " . EMAIL_UNVERIFIED . ",
    receive_mail TINYINT NOT NULL DEFAULT 1,
    email_public TINYINT NOT NULL DEFAULT 0,
    phone_public TINYINT NOT NULL DEFAULT 0,
    am_public TINYINT NOT NULL DEFAULT 0,
    pic_public TINYINT NOT NULL DEFAULT 0,
    whitelist TEXT CHARACTER SET ascii COLLATE ascii_bin,
    eportfolio_enable TINYINT NOT NULL DEFAULT 0,
    last_passreminder DATETIME DEFAULT NULL,
    disable_course_registration TINYINT NULL DEFAULT 0,
    options TEXT CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE `login_failure` (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip varchar(45) NOT NULL,
    count TINYINT unsigned NOT NULL default 0,
    last_fail datetime NOT NULL,
    UNIQUE KEY ip (ip)) $tbl_options");

$db->query("CREATE TABLE `loginout` (
    idLog INT NOT NULL auto_increment,
    id_user INT NOT NULL default 0,
    ip char(45) NOT NULL default '0.0.0.0',
    `when` datetime NOT NULL,
    action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
    PRIMARY KEY (idLog), KEY `id_user` (`id_user`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `personal_calendar` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `start` datetime NOT NULL,
    `end` datetime DEFAULT NULL,
    `duration` time NOT NULL,
    `recursion_period` varchar(30) DEFAULT NULL,
    `recursion_end` date DEFAULT NULL,
    `source_event_id` INT DEFAULT NULL,
    `reference_obj_module` mediumINT DEFAULT NULL,
    `reference_obj_type` ENUM('course', 'personalevent', 'user',
        'course_ebook', 'course_event', 'course_assignment', 'course_document',
        'course_link', 'course_exercise', 'course_learningpath', 'course_video',
        'course_videolink') CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
    `reference_obj_id` INT DEFAULT NULL,
    `reference_obj_course` INT DEFAULT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `personal_calendar_settings` (
    `user_id` INT NOT NULL,
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
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `start` datetime NOT NULL,
    `end` datetime DEFAULT NULL,
    `duration` time NOT NULL,
    `recursion_period` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `recursion_end` date DEFAULT NULL,
    `source_event_id` INT DEFAULT NULL,
    `visibility_level` INT DEFAULT '1',
    `email_notification` time DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_events` (`user_id`),
    KEY `admin_events_dates` (`start`)) $tbl_options");

//  login out roll ups
$db->query("CREATE TABLE `loginout_summary` (
    id INT NOT NULL auto_increment,
    login_sum INT unsigned  NOT NULL default 0,
    start_date datetime NOT NULL,
    end_date datetime NOT NULL,
    PRIMARY KEY (id)) $tbl_options");

// monthly reports
$db->query("CREATE TABLE monthly_summary (
    id INT NOT NULL auto_increment,
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
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL DEFAULT 0,
    `subsystem` TINYINT NOT NULL,
    `subsystem_id` INT DEFAULT NULL,
    `path` VARCHAR(255) NOT NULL,
    `extra_path` VARCHAR(255) NOT NULL DEFAULT '',
    `filename` VARCHAR(255) NOT NULL COLLATE utf8mb4_bin,
    `visible` TINYINT NOT NULL DEFAULT 1,
    `public` TINYINT NOT NULL DEFAULT 1,
    `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `category` TINYINT NOT NULL DEFAULT 0,
    `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `creator` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `date` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    `subject` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `author` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `format` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `language` VARCHAR(16) NOT NULL DEFAULT 'el',
    `copyrighted` TINYINT NOT NULL DEFAULT 0,
    `editable` TINYINT NOT NULL DEFAULT 0,
    `lock_user_id` INT NOT NULL DEFAULT 0,
    `prevent_download` INT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `group_properties` (
    `course_id` INT NOT NULL,
    `group_id` INT NOT NULL PRIMARY KEY,
    `self_registration` TINYINT NOT NULL DEFAULT 1,
    `allow_unregister` TINYINT NOT NULL DEFAULT 0,
    `forum` TINYINT NOT NULL DEFAULT 1,
    `private_forum` TINYINT NOT NULL DEFAULT 0,
    `documents` TINYINT NOT NULL DEFAULT 1,
    `wiki` TINYINT NOT NULL DEFAULT 0,
    `public_users_list` TINYINT NOT NULL DEFAULT '1',
    `booking` TINYINT NOT NULL DEFAULT 0,
    `agenda` TINYINT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `group` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL DEFAULT 0,
    `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `description`  TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `forum_id` INT NULL,
    `category_id` INT NULL,
    `max_members` INT NOT NULL DEFAULT 0,
    `secret_directory` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 0,
    `visible` TINYINT NOT NULL DEFAULT 1) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `group_members` (
    `group_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `is_tutor` INT NOT NULL DEFAULT 0,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    PRIMARY KEY (`group_id`, `user_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `tutor_availability_group` (
    `id` INT NOT NULL auto_increment,
    `user_id` INT NOT NULL default 0,
    `group_id` INT NOT NULL default 0,
    `start` DATETIME DEFAULT NULL,
    `end` DATETIME DEFAULT NULL,
    `lesson_id` INT NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `booking` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `lesson_id` INT NOT NULL,
    `group_id` INT NOT NULL DEFAULT 0,
    `tutor_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `start` DATETIME NOT NULL,
    `end` DATETIME NOT NULL,
    `accepted` INT NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    FOREIGN KEY (lesson_id) REFERENCES course(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `booking_user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `booking_id` INT NOT NULL,
    `simple_user_id` INT NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `date_availability_user` (
    `id` INT NOT NULL auto_increment,
    `user_id` INT NOT NULL default 0,
    `start` DATETIME DEFAULT NULL,
    `end` DATETIME DEFAULT NULL,
    PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `date_booking` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `teacher_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `start` DATETIME NOT NULL,
    `end` DATETIME NOT NULL,
    `accepted` INT NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    FOREIGN KEY (teacher_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `date_booking_user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `booking_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY (booking_id) REFERENCES date_booking(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `group_category` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    PRIMARY KEY (`id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `glossary` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `term` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `definition` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `url` text,
    `order` INT NOT NULL DEFAULT 0,
    `datestamp` DATETIME NOT NULL,
    `course_id` INT NOT NULL,
    `category_id` INT DEFAULT NULL,
    `notes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `glossary_category` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `order` INT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `limit` TINYINT NOT NULL DEFAULT 0,
    `students_semester` TINYINT NOT NULL DEFAULT 1,
    `active` TINYINT NOT NULL DEFAULT 0,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL) $tbl_options");

DBHelper::createForeignKey('attendance', 'course_id', 'course', 'id', DBHelper::FKRefOption_CASCADE);

$db->query("CREATE TABLE IF NOT EXISTS `attendance_activities` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_id` INT NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `date` DATETIME DEFAULT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `module_auto_id` INT NOT NULL DEFAULT 0,
    `module_auto_type` TINYINT NOT NULL DEFAULT 0,
    `auto` TINYINT NOT NULL DEFAULT 0) $tbl_options");

DBHelper::createForeignKey('attendance_activities', 'attendance_id', 'attendance', 'id', DBHelper::FKRefOption_CASCADE);

$db->query("CREATE TABLE IF NOT EXISTS `attendance_book` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_activity_id` INT NOT NULL,
    `uid` INT NOT NULL DEFAULT 0,
    `attend` TINYINT NOT NULL DEFAULT 0,
    `comments` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
     UNIQUE KEY attendance_activity_uid (attendance_activity_id, uid)) $tbl_options");

DBHelper::createForeignKey('attendance_book', 'attendance_activity_id', 'attendance_activities', 'id', DBHelper::FKRefOption_CASCADE);
DBHelper::createForeignKey('attendance_book', 'uid', 'user', 'id', DBHelper::FKRefOption_CASCADE);

$db->query("CREATE TABLE IF NOT EXISTS `attendance_users` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_id` INT NOT NULL,
    `uid` INT NOT NULL DEFAULT 0) $tbl_options");

DBHelper::createForeignKey('attendance_users', 'attendance_id', 'attendance', 'id', DBHelper::FKRefOption_CASCADE);
DBHelper::createForeignKey('attendance_users', 'uid', 'user', 'id', DBHelper::FKRefOption_CASCADE);

$db->query("CREATE TABLE IF NOT EXISTS `link` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `url` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `category` INT DEFAULT 0 NOT NULL,
    `order` INT DEFAULT 0 NOT NULL,
    `user_id` INT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `link_category` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `order` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `ebook` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `order` INT NOT NULL,
    `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `visible` BOOL NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `ebook_section` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ebook_id` INT NOT NULL,
    `public_id` VARCHAR(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `file` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `ebook_subsection` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `section_id` INT NOT NULL,
    `public_id` VARCHAR(11) NOT NULL,
    `file_id` INT NOT NULL,
    `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT '' NOT NULL,
    `desc` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `num_topics` INT DEFAULT 0 NOT NULL,
    `num_posts` INT DEFAULT 0 NOT NULL,
    `last_post_id` INT DEFAULT 0 NOT NULL,
    `cat_id` INT DEFAULT 0 NOT NULL,
    `visible` TINYINT DEFAULT 1 NOT NULL,
    `course_id` INT NOT NULL,    
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_category` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cat_title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT '' NOT NULL,
    `cat_order` INT DEFAULT 0 NOT NULL,
    `course_id` INT NOT NULL,
    KEY `forum_category_index` (`id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_notify` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT 0 NOT NULL,
    `cat_id` INT DEFAULT 0 NOT NULL,
    `forum_id` INT DEFAULT 0 NOT NULL,
    `topic_id` INT DEFAULT 0 NOT NULL,
    `notify_sent` BOOL DEFAULT 0 NOT NULL,
    `course_id` INT DEFAULT 0 NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_post` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `topic_id` INT NOT NULL DEFAULT 0,
    `post_text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `poster_id` INT NOT NULL DEFAULT 0,
    `post_time` DATETIME,
    `poster_ip` VARCHAR(45) DEFAULT '' NOT NULL,
    `parent_post_id` INT NOT NULL DEFAULT 0,
    `topic_filepath` varchar(200) DEFAULT NULL,
    `topic_filename` varchar(200) DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_topic` (
    `id` INT NOT NULL auto_increment,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `poster_id` INT DEFAULT NULL,
    `topic_time` DATETIME,
    `num_views` INT NOT NULL DEFAULT 0,
    `num_replies` INT NOT NULL DEFAULT 0,
    `last_post_id` INT NOT NULL DEFAULT 0,
    `forum_id` INT NOT NULL DEFAULT 0,
    `locked` TINYINT DEFAULT 0 NOT NULL,
    `visible` TINYINT DEFAULT 1 NOT NULL,
    `pin_time` DATETIME DEFAULT NULL,
    PRIMARY KEY  (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `forum_user_stats` (
    `user_id` INT NOT NULL,
    `num_posts` INT NOT NULL,
    `course_id` INT NOT NULL,
    PRIMARY KEY (`user_id`,`course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `video` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `url` VARCHAR(200) NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `category` INT DEFAULT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `creator` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `publisher` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `date` DATETIME NOT NULL,
    `visible` TINYINT NOT NULL DEFAULT 1,
    `public` TINYINT NOT NULL DEFAULT 1) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `videolink` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `url` VARCHAR(255) NOT NULL DEFAULT '',
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `category` INT DEFAULT NULL,
    `creator` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `publisher` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `date` DATETIME NOT NULL,
    `visible` TINYINT NOT NULL DEFAULT 1,
    `public` TINYINT NOT NULL DEFAULT 1) $tbl_options");

$db->query("CREATE TABLE `video_category` (
    `id` INT NOT NULL auto_increment,
    `course_id` INT NOT NULL,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    PRIMARY KEY (id)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_msg (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `subject` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `body` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `timestamp` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_attachment (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `msg_id` INT UNSIGNED NOT NULL,
    `filename` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `real_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `filesize` INT UNSIGNED NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_index (
    `msg_id` INT UNSIGNED NOT NULL,
    `recipient_id` INT UNSIGNED NOT NULL,
    `is_read` BOOLEAN NOT NULL DEFAULT 0,
    `deleted` BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY (`msg_id`, `recipient_id`)) $tbl_options");

// COMMENT='List of available modules used in learning paths';
$db->query("CREATE TABLE IF NOT EXISTS `lp_module` (
    `module_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `accessibility` enum('PRIVATE','PUBLIC') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PRIVATE',
    `startAsset_id` INT NOT NULL DEFAULT 0,
    `contentType` enum('CLARODOC', 'DOCUMENT', 'EXERCISE', 'HANDMADE',
        'SCORM', 'SCORM_ASSET', 'LABEL', 'COURSE_DESCRIPTION', 'LINK',
        'MEDIA','MEDIALINK') CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `launch_data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL) $tbl_options");

// COMMENT='List of learning Paths';
$db->query("CREATE TABLE IF NOT EXISTS `lp_learnPath` (
    `learnPath_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `lock` enum('OPEN','CLOSE') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'OPEN',
    `visible` TINYINT NOT NULL DEFAULT 0,
    `rank` INT NOT NULL DEFAULT 0) $tbl_options");

// COMMENT='This table links module to the learning path using them';
$db->query("CREATE TABLE IF NOT EXISTS `lp_rel_learnPath_module` (
    `learnPath_module_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `learnPath_id` INT NOT NULL DEFAULT 0,
    `module_id` INT NOT NULL DEFAULT 0,
    `lock` enum('OPEN','CLOSE') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'OPEN',
    `visible` TINYINT NOT NULL DEFAULT 0,
    `specificComment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `rank` INT NOT NULL DEFAULT 0,
    `parent` INT NOT NULL DEFAULT 0,
    `raw_to_pass` TINYINT NOT NULL DEFAULT 50) $tbl_options");

// COMMENT='List of resources of module of learning paths';
$db->query("CREATE TABLE IF NOT EXISTS `lp_asset` (
    `asset_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `module_id` INT NOT NULL DEFAULT 0,
    `path` VARCHAR(255) NOT NULL DEFAULT '',
    `comment` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL) $tbl_options");

// COMMENT='Record the last known status of the user in the course';
$db->query("CREATE TABLE IF NOT EXISTS `lp_user_module_progress` (
    `user_module_progress_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `learnPath_module_id` INT NOT NULL DEFAULT 0,
    `learnPath_id` INT NOT NULL DEFAULT 0,
    `lesson_location` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `lesson_status` enum('NOT ATTEMPTED', 'PASSED', 'FAILED', 'COMPLETED',
        'BROWSED', 'INCOMPLETE', 'UNKNOWN') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'NOT ATTEMPTED',
    `entry` enum('AB-INITIO', 'RESUME', '') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'AB-INITIO',
    `raw` TINYINT NOT NULL DEFAULT '-1',
    `scoreMin` TINYINT NOT NULL DEFAULT '-1',
    `scoreMax` TINYINT NOT NULL DEFAULT '-1',
    `total_time` VARCHAR(13) NOT NULL DEFAULT '0000:00:00.00',
    `session_time` VARCHAR(13) NOT NULL DEFAULT '0000:00:00.00',
    `progress_measure` FLOAT DEFAULT NULL,
    `suspend_data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `credit` enum('CREDIT','NO-CREDIT') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'NO-CREDIT',
    `attempt` INT NOT NULL DEFAULT 1,
    `started` datetime DEFAULT NULL,
    `accessed` datetime DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_properties` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL,
    `group_id` INT NOT NULL DEFAULT 0,
    visible TINYINT UNSIGNED NOT NULL DEFAULT '1') $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_acls` (
    `wiki_id` INT UNSIGNED NOT NULL,
    `flag` VARCHAR(255) NOT NULL,
    `value` ENUM('false','true') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'false',
    PRIMARY KEY (wiki_id, flag)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_pages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `wiki_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `owner_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `title` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `ctime` DATETIME NOT NULL,
    `last_version` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_mtime` DATETIME NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_pages_content` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT UNSIGNED NOT NULL DEFAULT 0,
    `editor_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `mtime` DATETIME NOT NULL,
    `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `changelog` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci)  $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_locks` (
    `ptitle` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `wiki_id` INT UNSIGNED NOT NULL,
    `uid` INT UNSIGNED NOT NULL DEFAULT 0,
    `ltime_created` DATETIME DEFAULT NULL,
    `ltime_alive` DATETIME DEFAULT NULL,
    PRIMARY KEY (ptitle, wiki_id) ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `blog_post` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `time` DATETIME NOT NULL,
    `views` INT UNSIGNED NOT NULL DEFAULT '0',
    `commenting` TINYINT NOT NULL DEFAULT '1',
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `course_id` INT NOT NULL,
    `visible` TINYINT UNSIGNED NOT NULL DEFAULT '1'
    ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT NOT NULL,
    `rtype` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `time` DATETIME NOT NULL,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `rating` (
    `rate_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT NOT NULL,
    `rtype` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `value` TINYINT NOT NULL,
    `widget` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `time` DATETIME NOT NULL,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `rating_source` VARCHAR(50) NOT NULL,
    INDEX `rating_index_1` (`rid`, `rtype`, `widget`),
    INDEX `rating_index_2` (`rid`, `rtype`, `widget`, `user_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `rating_cache` (
    `rate_cache_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT NOT NULL,
    `rtype` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `value` FLOAT NOT NULL DEFAULT 0,
    `count` INT NOT NULL DEFAULT 0,
    `tag` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    INDEX `rating_cache_index_1` (`rid`, `rtype`, `tag`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `abuse_report` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT NOT NULL,
    `rtype` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `course_id` INT NOT NULL,
    `reason` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `message` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `timestamp` INT NOT NULL DEFAULT 0,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT NOT NULL DEFAULT 1,
    INDEX `abuse_report_index_1` (`rid`, `rtype`, `user_id`, `status`),
    INDEX `abuse_report_index_2` (`course_id`, `status`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `shortname` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
                `name` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
                `description` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                `datatype` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
                `categoryid` INT NOT NULL DEFAULT 0,
                `sortorder`  INT NOT NULL DEFAULT 0,
                `required` TINYINT NOT NULL DEFAULT 0,
                `visibility` TINYINT NOT NULL DEFAULT 0,
                `user_type` TINYINT NOT NULL,
                `registration` TINYINT NOT NULL DEFAULT 0,
                `data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data` (
                `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
                `field_id` INT NOT NULL,
                `data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
                PRIMARY KEY (`user_id`, `field_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data_pending` (
                `user_request_id` INT NOT NULL DEFAULT 0,
                `field_id` INT NOT NULL,
                `data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
                PRIMARY KEY (`user_request_id`, `field_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_category` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
                `sortorder`  INT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `faq` (
                            `id` INT NOT NULL AUTO_INCREMENT,
                            `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
                            `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
                            `order` INT NOT NULL,
                            PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `homepageTexts` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
    `title` text NULL,
    `body` text NULL,
    `order` INT NOT NULL,
    `type` INT NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `homepagePriorities` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `title` text NULL,
    `order` INT NOT NULL,
    `visible` INT NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("INSERT INTO `homepagePriorities` (`title`, `order`, `visible`) VALUES
                                            ('announcements', 0, 1),
                                            ('popular_courses', 1, 1),
                                            ('texts', 2, 1),
                                            ('testimonials', 3, 1),
                                            ('statistics', 4, 1),
                                            ('open_courses', 5, 1)");


$db->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `shortname` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        `name` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        `description` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        `datatype` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        `categoryid` INT NOT NULL DEFAULT 0,
        `sortorder`  INT NOT NULL DEFAULT 0,
        `required` TINYINT NOT NULL DEFAULT 0,
        `data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields_data` (
        `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
        `field_id` INT NOT NULL,
        `data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        PRIMARY KEY (`user_id`, `field_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `eportfolio_fields_category` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        `sortorder`  INT NOT NULL DEFAULT 0) $tbl_options");

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
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
        `resource_id` INT NOT NULL,
        `resource_type` VARCHAR(50) NOT NULL,
        `course_id` INT NOT NULL,
        `course_title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
        `time_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
        INDEX `eportfolio_res_index` (`user_id`,`resource_type`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `wall_post` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NOT NULL,
        `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
        `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        `extvideo` VARCHAR(255) DEFAULT '',
        `timestamp` INT NOT NULL DEFAULT 0,
        `pinned` TINYINT NOT NULL DEFAULT 0,
        INDEX `wall_post_index` (`course_id`)) $tbl_options");

$db->query("CREATE TABLE `wall_post_resources` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `post_id` INT NOT NULL,
        `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
        `res_id` INT NOT NULL,
        `type` VARCHAR(255) NOT NULL DEFAULT '',
        INDEX `wall_post_resources_index` (`post_id`)) $tbl_options");


$db->query("CREATE TABLE IF NOT EXISTS `poll` (
    `pid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `creator_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `creation_date` DATETIME NOT NULL,
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `active` INT NOT NULL DEFAULT 0,
    `public` TINYINT NOT NULL DEFAULT 1,
    `description` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `end_message` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `anonymized` INT NOT NULL DEFAULT 0,
    `show_results` INT NOT NULL DEFAULT 0,
    `display_position` INT NOT NULL DEFAULT 0,
    `multiple_submissions` TINYINT NOT NULL DEFAULT '0',
    `default_answer` TINYINT NOT NULL DEFAULT '0',
    `type` TINYINT NOT NULL DEFAULT 0,
    `assign_to_specific` TINYINT NOT NULL DEFAULT '0',
    `lti_template` INT DEFAULT NULL,
    `launchcontainer` TINYINT DEFAULT NULL,
    `pagination` INT NOT NULL DEFAULT 0,
    `require_answer` INT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_to_specific` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `group_id` INT NULL,
    `poll_id` INT NOT NULL ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_user_record` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT UNSIGNED NOT NULL DEFAULT 0,
    `uid` INT UNSIGNED NOT NULL DEFAULT 0,
    `email` VARCHAR(255) DEFAULT NULL,
    `email_verification` TINYINT DEFAULT NULL,
    `verification_code` VARCHAR(255) DEFAULT NULL,
    `session_id` INT NOT NULL DEFAULT 0)
    $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_answer_record` (
    `arid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `poll_user_record_id` INT NOT NULL,
    `qid` INT NOT NULL DEFAULT 0,
    `aid` INT NOT NULL DEFAULT 0,
    `answer_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `submit_date` DATETIME NOT NULL,
    `sub_qid` INT NOT NULL DEFAULT 0,
    `sub_qid_row` INT NOT NULL DEFAULT 0,
    FOREIGN KEY (`poll_user_record_id`)
    REFERENCES `poll_user_record` (`id`)
    ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_question` (
    `pqid` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT NOT NULL DEFAULT 0,
    `question_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `qtype` tinyint UNSIGNED NOT NULL,
    `q_position` INT DEFAULT 1,
    `q_scale` INT NULL DEFAULT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `answer_scale` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `q_row` INT NOT NULL DEFAULT 0,
    `q_column` INT NOT NULL DEFAULT 0,
    `page` INT NOT NULL DEFAULT 0,
    `require_response` INT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `poll_question_answer` (
    `pqaid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pqid` INT NOT NULL DEFAULT 0,
    `answer_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `sub_question` INT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `assignment` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `comments` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `submission_type` TINYINT NOT NULL DEFAULT '0',
    `deadline` DATETIME NULL DEFAULT NULL,
    `late_submission` TINYINT NOT NULL DEFAULT '0',
    `submission_date` DATETIME NOT NULL,
    `results_date` DATETIME DEFAULT NULL,
    `active` TINYINT NOT NULL DEFAULT 1,
    `secret_directory` VARCHAR(30) NOT NULL,
    `group_submissions` TINYINT  NOT NULL DEFAULT 0,
    `grading_type` TINYINT NOT NULL DEFAULT '0',
    `max_grade` FLOAT DEFAULT '10' NOT NULL,
    `grading_scale_id` INT NOT NULL DEFAULT '0',
    `assign_to_specific` TINYINT NOT NULL DEFAULT 0,
    `file_path` VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL,
    `file_name` VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL,
    `auto_judge` TINYINT NOT NULL DEFAULT 0,
    `auto_judge_scenarios` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `lang` VARCHAR(10) NOT NULL DEFAULT '',
    `notification` TINYINT DEFAULT 0,
    `ip_lock` TEXT CHARACTER SET ascii COLLATE ascii_bin,
    `password_lock` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `assignment_type` TINYINT NOT NULL DEFAULT '0',
    `lti_template` INT DEFAULT NULL,
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
    `tii_exclude_type` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'none',
    `tii_exclude_value` INT NOT NULL DEFAULT '0',
    `tii_instructorcustomparameters` TEXT,
    `reviews_per_assignment` INT DEFAULT NULL,
    `start_date_review` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `due_date_review` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `max_submissions` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `passing_grade` FLOAT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `assignment_submit` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uid` INT UNSIGNED NOT NULL DEFAULT 0,
    `assignment_id` INT NOT NULL DEFAULT 0,
    `submission_date` DATETIME NOT NULL,
    `submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
    `file_path` VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
    `file_name` VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
    `submission_text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `comments` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `grade` FLOAT DEFAULT NULL,
    `grade_rubric` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `grade_comments` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `grade_comments_filepath` VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
    `grade_comments_filename` VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
    `grade_submission_date` DATE NOT NULL DEFAULT '1000-10-10',
    `grade_submission_ip` VARCHAR(45) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
    `group_id` INT DEFAULT NULL,
    `auto_judge_scenarios_output` TEXT) $tbl_options");

// assignment peer review
$db->query("CREATE TABLE `assignment_grading_review` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `user_submit_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `file_path` VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL,
    `file_name` VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL,
    `submission_text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `submission_date` DATETIME NOT NULL,
    `gid` INT NOT NULL,
    `users_id` INT NOT NULL,
    `grade` FLOAT DEFAULT NULL,
    `comments` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `date_submit` DATETIME DEFAULT NULL,
    `rubric_scales` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci) $tbl_options");

// grading scales table
$db->query("CREATE TABLE IF NOT EXISTS `grading_scale` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `scales` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `course_id` INT NOT NULL,
    KEY `course_id` (`course_id`)) $tbl_options");

// rubric table based on grading scales

$db->query("CREATE TABLE IF NOT EXISTS `rubric` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `scales` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `preview_rubric` TINYINT NOT NULL DEFAULT '0',
    `points_to_graded` TINYINT NOT NULL DEFAULT '0',
    `course_id` INT NOT NULL,
    KEY `course_id` (`course_id`)) $tbl_options");


$db->query("CREATE TABLE IF NOT EXISTS `assignment_to_specific` (
    `user_id` INT NOT NULL,
    `group_id` INT NOT NULL,
    `assignment_id` INT NOT NULL,
    PRIMARY KEY (user_id, group_id, assignment_id)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `type` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `range` TINYINT UNSIGNED DEFAULT 0,
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `temp_save` TINYINT NOT NULL DEFAULT 0,
    `time_constraint` INT DEFAULT 0,
    `attempts_allowed` INT DEFAULT 0,
    `random` SMALLINT NOT NULL DEFAULT 0,
    `shuffle` SMALLINT NOT NULL DEFAULT 0,
    `active` TINYINT DEFAULT NULL,
    `public` TINYINT NOT NULL DEFAULT 1,
    `results` TINYINT NOT NULL DEFAULT 1,
    `results_date` DATETIME DEFAULT NULL,
    `score` TINYINT NOT NULL DEFAULT 1,
    `assign_to_specific` TINYINT NOT NULL DEFAULT 0,
    `ip_lock` TEXT CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
    `password_lock` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    `continue_time_limit` INT NOT NULL DEFAULT 0,
    `calc_grade_method` TINYINT DEFAULT 1,
    `general_feedback` TEXT DEFAULT NULL,
    `options` TEXT DEFAULT NULL,
    `is_exam` INT DEFAULT 0 NULL,
     passing_grade FLOAT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_to_specific` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `group_id` INT NULL,
    `exercise_id` INT NOT NULL ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_user_record` (
    `eurid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eid` INT NOT NULL DEFAULT 0,
    `uid` INT UNSIGNED NOT NULL DEFAULT 0,
    `record_start_date` DATETIME NOT NULL,
    `record_end_date` DATETIME DEFAULT NULL,
    `total_score` FLOAT(11,2) NOT NULL DEFAULT 0,
    `total_weighting` FLOAT(11,2) DEFAULT 0,
    `attempt` INT NOT NULL DEFAULT 0,
    `attempt_status` TINYINT NOT NULL DEFAULT 1,
    `secs_remaining` INT NOT NULL DEFAULT '0',
    `assigned_to` INT DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_answer_record` (
    `answer_record_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eurid` INT NOT NULL,
    `question_id` INT NOT NULL,
    `answer` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `answer_id` INT NOT NULL,
    `weight` float(11,2) DEFAULT NULL,
    `is_answered` TINYINT NOT NULL DEFAULT 1,
    `q_position` INT NOT NULL DEFAULT 1) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_question` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `question` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `feedback` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `weight` FLOAT(11,2) DEFAULT NULL,
    `type` INT DEFAULT 1,
    `difficulty` INT DEFAULT 0,
    `category` INT DEFAULT 0,
    `copy_of_qid` INT DEFAULT NULL,
    `options` TEXT DEFAULT NULL,
     CONSTRAINT FOREIGN KEY (copy_of_qid)
     REFERENCES exercise_question(id) ON DELETE SET NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_question_cats` (
    `question_cat_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `question_cat_name` VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `course_id` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_answer` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT NOT NULL DEFAULT 0,
    `answer` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `correct` INT DEFAULT NULL,
    `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `weight` FLOAT(5,2),
    `r_position` INT DEFAULT NULL ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_with_questions` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT DEFAULT 0,
    `exercise_id` INT NOT NULL DEFAULT 0,
    `q_position` INT NOT NULL DEFAULT 1,
    `random_criteria` TEXT) $tbl_options");

$db->query("CREATE TABLE exercise_ai_config (
        `id` INT NOT NULL AUTO_INCREMENT,
        `question_id` INT NOT NULL,
        `course_id` INT NOT NULL,
        `enabled` TINYINT DEFAULT 1,
        `evaluation_prompt` text NOT NULL,
        `sample_responses` text NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_question` (`question_id`),
        KEY `idx_course` (`course_id`),
        KEY `idx_enabled` (`enabled`),
        FOREIGN KEY (`question_id`) REFERENCES `exercise_question`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`course_id`) REFERENCES `course`(`id`) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE exercise_ai_evaluation (
        `id` INT NOT NULL AUTO_INCREMENT,
        `answer_record_id` INT NOT NULL COMMENT 'Reference to exercise_answer_record.answer_record_id',
        `question_id` INT NOT NULL COMMENT 'Reference to exercise_question.id',
        `exercise_id` INT NOT NULL COMMENT 'Reference to exercise.id for easier querying',
        `student_record_id` INT NOT NULL COMMENT 'Reference to exercise_user_record.eurid',
        `ai_suggested_score` decimal(5,2) NOT NULL COMMENT 'AI suggested score',
        `ai_max_score` decimal(5,2) NOT NULL COMMENT 'Maximum possible score for this question',
        `ai_reasoning` text NOT NULL COMMENT 'AI explanation of the score',
        `ai_confidence` decimal(3,2) NOT NULL COMMENT 'AI confidence level (0.0-1.0)',
        `ai_provider` varchar(50) NOT NULL COMMENT 'AI provider used (openai, anthropic, etc.)',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When AI evaluation was performed',
        PRIMARY KEY (`id`),
        KEY `idx_answer_record` (`answer_record_id`),
        KEY `idx_question` (`question_id`),
        KEY `idx_exercise` (`exercise_id`),
        KEY `idx_student_record` (`student_record_id`),
        KEY `idx_confidence` (`ai_confidence`),
        FOREIGN KEY (`answer_record_id`) REFERENCES `exercise_answer_record`(`answer_record_id`) ON DELETE CASCADE,
        FOREIGN KEY (`question_id`) REFERENCES `exercise_question`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`exercise_id`) REFERENCES `exercise`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`student_record_id`) REFERENCES `exercise_user_record`(`eurid`) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS lti_apps (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT DEFAULT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `lti_version` VARCHAR(255) NOT NULL DEFAULT '1.1',
    `lti_provider_url` VARCHAR(255) DEFAULT NULL,
    `lti_provider_key` VARCHAR(255) DEFAULT NULL,
    `lti_provider_secret` VARCHAR(255) DEFAULT NULL,
    `lti_provider_public_keyset_url` VARCHAR(255) DEFAULT NULL,
    `lti_provider_initiate_login_url` VARCHAR(255) DEFAULT NULL,
    `lti_provider_redirection_uri` VARCHAR(255) DEFAULT NULL,
    `client_id` VARCHAR(255) DEFAULT NULL,
    `launchcontainer` TINYINT NOT NULL DEFAULT 1,
    `is_template` TINYINT NOT NULL DEFAULT 0,
    `enabled` TINYINT NOT NULL DEFAULT 1,
    `all_courses` TINYINT NOT NULL DEFAULT 1,
    `type` VARCHAR(255) NOT NULL DEFAULT 'turnitin',
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `lti_access_tokens` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `lti_app` INT NOT NULL,
    `scope` TEXT,
    `token` VARCHAR(128) NOT NULL,
    `valid_until` INT NOT NULL,
    `time_created` INT NOT NULL,
    `last_access` INT,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`lti_app`) REFERENCES `lti_apps` (`id`)) $tbl_options");

$db->query("CREATE TABLE `course_lti_app` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `course_id` INT NOT NULL,
      `lti_app` INT NOT NULL,
      `visible` TINYINT NOT NULL DEFAULT 1,
      FOREIGN KEY (`course_id`) REFERENCES `course` (`id`),
      FOREIGN KEY (`lti_app`) REFERENCES `lti_apps` (`id`))
   $tbl_options");

$db->query("CREATE TABLE `course_lti_publish` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `course_id` INT NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `description` TEXT,
      `lti_provider_key` VARCHAR(255) NOT NULL,
      `lti_provider_secret` VARCHAR(255) NOT NULL,
      `enabled` TINYINT NOT NULL DEFAULT 1,
      FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `course_lti_publish_user_enrolments` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `publish_id` INT NOT NULL,
      `user_id` INT NOT NULL,
      `created` INT NOT NULL,
      `updated` INT NOT NULL,
      FOREIGN KEY (`publish_id`) REFERENCES `course_lti_publish` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `course_lti_enrol_users` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `publish_id` INT NOT NULL,
      `user_id` INT NOT NULL,
      `service_url` TEXT,
      `source_id` TEXT,
      `consumer_key` TEXT,
      `consumer_secret` TEXT,
      `memberships_url` TEXT,
      `memberships_id` TEXT,
      `last_grade` FLOAT,
      `last_access` INT,
      `time_created` INT,
      FOREIGN KEY (`publish_id`) REFERENCES `course_lti_publish` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE) $tbl_options");

// lti provider tables
$db->query("CREATE TABLE `lti_publish_lti2_consumer` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
    `protected` smallINT NOT NULL,
    `enabled` smallINT NOT NULL,
    `enablefrom` INT,
    `enableuntil` INT,
    `lastaccess` INT,
    `created` INT NOT NULL,
    `updated` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_context` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `consumerid` INT NOT NULL,
    `lticontextkey` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100),
    `settings` TEXT,
    `created` INT NOT NULL,
    `updated` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_nonce` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `consumerid` INT NOT NULL,
    `value` VARCHAR(64) NOT NULL,
    `expires` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_resource_link` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `contextid` INT,
    `consumerid` INT,
    `ltiresourcelinkkey` VARCHAR(255) NOT NULL,
    `settings` TEXT,
    `primaryresourcelinkid` INT,
    `shareapproved` smallINT,
    `created` INT NOT NULL,
    `updated` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_share_key` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `sharekey` VARCHAR(32) NOT NULL UNIQUE,
    `resourcelinkid` INT NOT NULL UNIQUE,
    `autoapprove` smallINT NOT NULL,
    `expires` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_tool_proxy` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `toolproxykey` VARCHAR(32) NOT NULL UNIQUE,
    `consumerid` INT NOT NULL,
    `toolproxy` TEXT NOT NULL,
    `created` INT NOT NULL,
    `updated` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE `lti_publish_lti2_user_result` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `resourcelinkid` INT NOT NULL,
    `ltiuserkey` VARCHAR(255) NOT NULL,
    `ltiresultsourcedid` VARCHAR(1024) NOT NULL,
    `created` INT NOT NULL,
    `updated` INT NOT NULL) $tbl_options");

// hierarchy tables
$db->query("CREATE TABLE IF NOT EXISTS `hierarchy` (
    `id` INT NOT NULL auto_increment PRIMARY KEY,
    `code` varchar(20),
    `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `number` INT NOT NULL default 1000,
    `generator` INT NOT NULL default 100,
    `lft` INT NOT NULL,
    `rgt` INT NOT NULL,
    `allow_course` boolean not null default false,
    `allow_user` boolean NOT NULL default false,
    `order_priority` INT default null,
    `visible` TINYINT NOT NULL default 2,
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
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course` INT NOT NULL,
    `department` INT NOT NULL,
    UNIQUE KEY `cdep_unique` (`course`,`department`),
    FOREIGN KEY (`course`) REFERENCES `course` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `user_department` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` INT NOT NULL,
    `department` INT NOT NULL,
    UNIQUE KEY `udep_unique` (`user`,`department`),
    FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $tbl_options");


// create stored procedures
refreshHierarchyProcedures();

$db->query("CREATE TABLE `admin` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `privilege` INT NOT NULL DEFAULT 0,
    `department_id` INT DEFAULT NULL,
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
    id INT NOT NULL AUTO_INCREMENT,
    givenname VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    surname VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    username VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    password VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    verified_mail TINYINT NOT NULL DEFAULT " . EMAIL_UNVERIFIED . ",
    faculty_id INT NOT NULL DEFAULT 0,
    phone VARCHAR(20) NOT NULL DEFAULT '',
    am VARCHAR(20) NOT NULL DEFAULT '',
    state INT NOT NULL DEFAULT 0,
    date_open DATETIME DEFAULT NULL,
    date_closed DATETIME DEFAULT NULL,
    comment TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    lang VARCHAR(16) NOT NULL DEFAULT 'el',
    status TINYINT NOT NULL DEFAULT 1,
    request_ip VARCHAR(45) NOT NULL DEFAULT '',
    PRIMARY KEY (id)) $tbl_options");

$db->query("CREATE TABLE `auth` (
    `auth_id` INT NOT NULL auto_increment,
    `auth_name` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL default '',
    `auth_settings` text CHARACTER SET utf8mb4,
    `auth_instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `auth_title` text CHARACTER SET utf8mb4,
    `auth_default` TINYINT NOT NULL default 0,
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
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    auth_id INT NOT NULL,
    uid VARCHAR(64) NOT NULL,
    UNIQUE KEY (user_id, auth_id),
    KEY (uid),
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE)
    $tbl_options");

$db->query("CREATE TABLE `user_request_ext_uid` (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_request_id INT NOT NULL,
    auth_id INT NOT NULL,
    uid VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    UNIQUE KEY (user_request_id, auth_id),
    FOREIGN KEY (`user_request_id`) REFERENCES `user_request` (`id`) ON DELETE CASCADE)
    $tbl_options");

$db->query("CREATE TABLE `permissions` (
    `id` tinyint NOT NULL AUTO_INCREMENT,
    `permission` VARCHAR(255),
     PRIMARY KEY (`id`)) $tbl_options");

$db->query("INSERT INTO permissions(permission) VALUE('admin_course_users'), 
             ('admin_course_modules'),
             ('backup_course'),
             ('clone_course'),
             ('can_upload_document'),
             ('can_upload_multimedia')");

$db->query("CREATE TABLE user_permissions (
    `course_id` int NOT NULL DEFAULT '0',  
    `user_id` int unsigned NOT NULL DEFAULT '0',
    `permission_id` tinyint NOT NULL,
    PRIMARY KEY (`course_id`,`user_id`,`permission_id`)
  ) $tbl_options");

$eclass_stud_reg = intval($eclass_stud_reg);

$db->query("CREATE TABLE `config` (
    `key` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
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
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `last_run` DATETIME NOT NULL) $tbl_options");

// tables for units module
$db->query("CREATE TABLE `course_units` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comments` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `start_week` DATE,
    `finish_week` DATE,
    `visible` TINYINT,
    `public` TINYINT NOT NULL DEFAULT 1,
    `order` INT NOT NULL DEFAULT 0,
    `assign_to_specific` tinyint NOT NULL DEFAULT '0',
    `course_id` INT NOT NULL,
    UNIQUE KEY `course_units_order` (`course_id`,`order`)) $tbl_options");

$db->query("CREATE TABLE course_units_to_specific (
        id INT auto_increment NOT NULL,
        unit_id INT NOT NULL,
        user_id INT NULL,
        group_id INT NULL,
      PRIMARY KEY (`id`),
      KEY `unit_id` (`unit_id`)) $tbl_options");

$db->query("CREATE TABLE `unit_resources` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `unit_id` INT NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `comments` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `res_id` INT NOT NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT '',
    `visible` TINYINT,
    `order` INT NOT NULL DEFAULT 0,
    `date` DATETIME NOT NULL,
    `fc_type` INT NOT NULL DEFAULT 3,
    `activity_title`  VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `activity_id` VARCHAR(5) NOT NULL DEFAULT 'FC000',
    UNIQUE KEY `unit_resources_order` (`unit_id`,`order`)) $tbl_options");

$db->query("CREATE TABLE `actions_daily` (
    `id` INT NOT NULL auto_increment,
    `user_id` INT NOT NULL,
    `module_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `hits` INT NOT NULL,
    `duration` INT NOT NULL,
    `day` date NOT NULL,
    `last_update` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `actionsdailyindex` (`module_id`, `day`),
    KEY `actionsdailyuserindex` (`user_id`),
    KEY `actionsdailydayindex` (`day`),
    KEY `actionsdailymoduleindex` (`module_id`),
    KEY `actionsdailycourseindex` (`course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `actions_summary` (
    `id` INT NOT NULL auto_increment,
    `module_id` INT NOT NULL,
    `visits` INT NOT NULL,
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `duration` INT NOT NULL,
    `course_id` INT NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `logins` (
    `id` INT NOT NULL auto_increment,
    `user_id` INT NOT NULL,
    `ip` char(45) NOT NULL default '0.0.0.0',
    `date_time` datetime NOT NULL,
    `course_id` INT NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

// tc_sessions tables
$db->query("CREATE TABLE IF NOT EXISTS `tc_session` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT DEFAULT NULL,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `start_date` datetime DEFAULT NULL,
    `end_date` datetime DEFAULT NULL,
    `public` enum('0','1') DEFAULT NULL,
    `active` enum('0','1') DEFAULT NULL,
    `running_at` INT DEFAULT NULL,
    `meeting_id` varchar(255) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
    `mod_pw` varchar(255) DEFAULT NULL,
    `att_pw` varchar(255) DEFAULT NULL,
    `unlock_interval` INT DEFAULT NULL,
    `external_users` text DEFAULT NULL,
    `participants` varchar(1000) DEFAULT NULL,
    `record` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT 'false',
    `sessionUsers` INT DEFAULT 0,
    `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `id_session` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)) $tbl_options");

// tc_servers table
$db->query("CREATE TABLE IF NOT EXISTS `tc_servers` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `type` varchar(255) NOT NULL,
    `hostname` varchar(255) NOT NULL,
    `ip` varchar(255) DEFAULT NULL,
    `port` varchar(255) DEFAULT NULL,
    `enabled` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
    `server_key` varchar(255) DEFAULT NULL,
    `username` varchar(255) DEFAULT NULL,
    `password` varchar(255) DEFAULT NULL,
    `api_url` varchar(255) DEFAULT NULL,
    `webapp` varchar(255) DEFAULT NULL,
    `max_rooms` INT DEFAULT NULL,
    `max_users` INT DEFAULT NULL,
    `enable_recordings` enum('true','false') CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
    `weight` INT DEFAULT NULL,
    `screenshare` varchar(255) DEFAULT NULL,
    `all_courses` TINYINT NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `hostname` (`hostname`),
    KEY `idx_tc_servers` (`hostname`)) $tbl_options");


$db->query("CREATE TABLE `tc_attendance` (
    `id` INT NOT NULL DEFAULT '0',
    `meetingid` varchar(42) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `bbbuserid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `totaltime` INT NOT NULL DEFAULT '0',
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`,`meetingid`),
    KEY `id` (`id`),
    KEY `meetingid` (`meetingid`)) $tbl_options");

$db->query("CREATE TABLE `tc_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `meetingid` varchar(42) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `bbbuserid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `fullName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci default 'bbb',
    PRIMARY KEY (`id`),
    KEY `userid` (`bbbuserid`),
    KEY `fullName` (`fullName`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `course_external_server` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `external_server` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`external_server`, `course_id`)) $tbl_options");

// plagiarism tool table
$db->query("CREATE TABLE `ext_plag_connection` (
        `id` INT unsigned NOT NULL AUTO_INCREMENT,
        `type` INT unsigned NOT NULL DEFAULT '1',
        `file_id` INT NOT NULL,
        `remote_file_id` INT DEFAULT NULL,
        `submission_id` INT DEFAULT NULL,
        PRIMARY KEY (`id`)) $tbl_options");


$db->query("CREATE TABLE IF NOT EXISTS `course_settings` (
    `setting_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `value` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`setting_id`, `course_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `students_semester` TINYINT NOT NULL DEFAULT 1,
    `range` TINYINT NOT NULL DEFAULT 10,
    `active` TINYINT NOT NULL DEFAULT 0,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL,
    `passing_grade` FLOAT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_activities` (
    `id` MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_id` MEDIUMINT NOT NULL,
    `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `activity_type` INT DEFAULT NULL,
    `date` DATETIME DEFAULT NULL,
    `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `weight` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `module_auto_id` MEDIUMINT NOT NULL DEFAULT 0,
    `module_auto_type` TINYINT NOT NULL DEFAULT 0,
    `auto` TINYINT NOT NULL DEFAULT 0,
    `visible` TINYINT NOT NULL DEFAULT 0,
    `extra_credit` BOOL DEFAULT 0 NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_book` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_activity_id` MEDIUMINT NOT NULL,
    `uid` INT NOT NULL DEFAULT 0,
    `grade` FLOAT NOT NULL DEFAULT -1,
    `comments` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    UNIQUE KEY activity_uid (gradebook_activity_id, uid)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_users` (
    `id` MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_id` MEDIUMINT NOT NULL,
    `uid` INT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `oai_record` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL UNIQUE,
    `oai_identifier` varchar(255) DEFAULT NULL,
    `oai_metadataprefix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT 'oai_dc',
    `oai_set` varchar(255) DEFAULT 'class:course',
    `datestamp` datetime DEFAULT NULL,
    `deleted` TINYINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `oai_identifier` (`oai_identifier`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `oai_metadata` (
    `id` INT NOT NULL auto_increment PRIMARY KEY,
    `oai_record` INT NOT NULL references oai_record(id),
    `field` varchar(255) NOT NULL,
    `value` text,
    INDEX `field_index` (`field`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `note` (
    `id` INT NOT NULL auto_increment,
    `user_id` INT NOT NULL,
    `title` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `date_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    `order` mediumINT NOT NULL default 0,
    `reference_obj_module` mediumINT default NULL,
    `reference_obj_type` enum('course','personalevent','user','course_ebook','course_event','course_assignment','course_document','course_link','course_exercise','course_learningpath','course_video','course_videolink') CHARACTER SET ascii COLLATE ascii_bin default NULL,
    `reference_obj_id` INT default NULL,
    `reference_obj_course` INT default NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `idx_queue` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL UNIQUE,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `idx_queue_async` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `request_type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `resource_type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `resource_id` INT NOT NULL,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `theme_options` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `styles` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `version` TINYINT,
    PRIMARY KEY (`id`)) $tbl_options");

// Tags tables
$db->query("CREATE TABLE IF NOT EXISTS `tag_element_module` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `module_id` INT NULL,
    `element_id` INT NULL,
    `user_id` INT NOT NULL,
    `date` DATETIME DEFAULT NULL,
    `tag_id` INT NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS tag (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    UNIQUE KEY (name)) $tbl_options");

// Recycle object table
$db->query("CREATE TABLE IF NOT EXISTS `recyclebin` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tablename` varchar(100) NOT NULL,
    `entryid` INT NOT NULL,
    `entrydata` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    KEY `entryid` (`entryid`), KEY `tablename` (`tablename`)) $tbl_options");

// Auto-enroll rules tables
$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `status` TINYINT NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule_department` (
    `rule` INT NOT NULL,
    `department` INT NOT NULL,
    PRIMARY KEY (rule, department),
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_course` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rule` INT NOT NULL DEFAULT 0,
    `course_id` INT NOT NULL,
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_department` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rule` INT NOT NULL DEFAULT 0,
    `department_id` INT NOT NULL,
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES hierarchy(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `activity_heading` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order` INT NOT NULL DEFAULT 0,
    `heading` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `required` BOOL NOT NULL DEFAULT 0) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `activity_content` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `heading_id` INT NOT NULL DEFAULT 0,
    `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
    FOREIGN KEY (heading_id) REFERENCES activity_heading(id) ON DELETE CASCADE,
    UNIQUE KEY `heading_course` (`course_id`,`heading_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `widget` (
                `id` INT unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `class` varchar(400) NOT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `widget_widget_area` (
                `id` INT unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `widget_id` INT unsigned NOT NULL,
                `widget_area_id` INT NOT NULL,
                `options` text NOT NULL,
                `position` INT NOT NULL,
                `user_id` INT NULL,
                `course_id` INT NULL,
                 FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                 FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
                 FOREIGN KEY (widget_id) REFERENCES widget(id) ON DELETE CASCADE) $tbl_options");

// Conference table
$db->query("CREATE TABLE IF NOT EXISTS `conference` (
    `conf_id` INT NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `conf_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `conf_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci default '0',
    `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci default '0',
    `chat_activity` boolean not null default false,
    `agent_created` boolean not null default false,
    `chat_activity_id` INT,
    `agent_id` INT,
    PRIMARY KEY (`conf_id`,`course_id`)) $tbl_options");

// Colmooc user table
$db->query("CREATE TABLE IF NOT EXISTS `colmooc_user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `colmooc_id` INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

// Colmooc user session table
$db->query("CREATE TABLE IF NOT EXISTS `colmooc_user_session` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `activity_id` INT NOT NULL,
    `session_id` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `session_token` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `session_status` TINYINT NOT NULL DEFAULT 0,
    `session_status_updated` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY `user_activity` (`user_id`, `activity_id`),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

// Colmooc pair log table
$db->query("CREATE TABLE IF NOT EXISTS `colmooc_pair_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `activity_id` INT NOT NULL,
    `moderator_id` INT NOT NULL,
    `partner_id` INT NOT NULL,
    `session_status` TINYINT NOT NULL DEFAULT 0,
    `created` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (moderator_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

// Course Category tables
$db->query("CREATE TABLE IF NOT EXISTS `category` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `ordering` INT,
    `multiple` BOOLEAN NOT NULL DEFAULT TRUE,
    `searchable` BOOLEAN NOT NULL DEFAULT TRUE,
    `active` BOOLEAN NOT NULL DEFAULT TRUE
    ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `category_value` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL REFERENCES category(id),
    `name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `ordering` INT,
    `active` BOOLEAN NOT NULL DEFAULT TRUE
    ) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `course_category` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL REFERENCES course(id),
    `category_value_id` INT NOT NULL REFERENCES category_value(id)
    ) $tbl_options");

// Gamification Tables
$db->query("CREATE TABLE `certificate_template` (
    `id` MEDIUMINT not null auto_increment primary key,
    `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci not null,
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `filename` varchar(255),
    `orientation` varchar(10),
    `all_courses` TINYINT not null default 1
) $tbl_options");

$db->query("CREATE TABLE `badge_icon` (
    `id` MEDIUMINT not null auto_increment primary key,
    `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci not null,
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    `filename` varchar(255)
) $tbl_options");

$db->query("CREATE TABLE `certificate` (
  `id` INT not null auto_increment primary key,
  `course_id` INT not null,
  `issuer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci not null default '',
  `template` MEDIUMINT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci not null,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `autoassign` TINYINT not null default 1,
  `active` TINYINT not null default 1,
  `created` datetime,
  `expires` datetime,
  `bundle` INT not null default 0,
  `unit_id` INT NOT NULL DEFAULT 0,
  `session_id` INT NOT NULL DEFAULT 0,
  index `certificate_course` (`course_id`),
  foreign key (`course_id`) references `course` (`id`),
  foreign key (`template`) references `certificate_template`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `badge` (
  `id` INT not null auto_increment primary key,
  `course_id` INT not null,
  `unit_id` INT not null default 0,
  `session_id` INT not null default 0,
  `issuer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci not null default '',
  `icon` MEDIUMINT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci not null,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `autoassign` TINYINT not null default 1,
  `active` TINYINT not null default 1,
  `created` datetime,
  `expires` datetime,
  `bundle` INT not null default 0,
  index `badge_course` (`course_id`),
  foreign key (`course_id`) references `course` (`id`)
) $tbl_options");

$db->query("CREATE TABLE `user_certificate` (
  `id` INT not null auto_increment primary key,
  `user` INT not null,
  `certificate` INT not null,
  `completed` boolean default false,
  `completed_criteria` INT,
  `total_criteria` INT,
  `updated` datetime,
  `assigned` datetime,
  unique key `user_certificate` (`user`, `certificate`),
  foreign key (`user`) references `user`(`id`),
  foreign key (`certificate`) references `certificate` (`id`)
) $tbl_options");

$db->query("CREATE TABLE `user_badge` (
  `id` INT not null auto_increment primary key,
  `user` INT not null,
  `badge` INT not null,
  `completed` boolean default false,
  `completed_criteria` INT,
  `total_criteria` INT,
  `updated` datetime,
  `assigned` datetime,
  unique key `user_badge` (`user`, `badge`),
  foreign key (`user`) references `user`(`id`),
  foreign key (`badge`) references `badge` (`id`)
) $tbl_options");

$db->query("CREATE TABLE `certificate_criterion` (
  `id` INT not null auto_increment primary key,
  `certificate` INT not null,
  `activity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `module` INT,
  `resource` INT,
  `threshold` decimal(7,2),
  `operator` varchar(20),
  foreign key (`certificate`) references `certificate`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `badge_criterion` (
  `id` INT not null auto_increment primary key,
  `badge` INT not null,
  `activity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `module` INT,
  `resource` INT,
  `threshold` decimal(7,2),
  `operator` varchar(20),
  foreign key (`badge`) references `badge`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `user_certificate_criterion` (
  `id` INT not null auto_increment primary key,
  `user` INT not null,
  `certificate_criterion` INT not null,
  `created` datetime,
  unique key `user_certificate_criterion` (`user`, `certificate_criterion`),
  foreign key (`user`) references `user`(`id`),
  foreign key (`certificate_criterion`) references `certificate_criterion`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `user_badge_criterion` (
  `id` INT not null auto_increment primary key,
  `user` INT not null,
  `badge_criterion` INT not null,
  `created` datetime,
  unique key `user_badge_criterion` (`user`, `badge_criterion`),
  foreign key (`user`) references `user`(`id`),
  foreign key (`badge_criterion`) references `badge_criterion`(`id`)
) $tbl_options");

$db->query("CREATE TABLE `certified_users` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT,
  `course_title` varchar(255) NOT NULL DEFAULT '',
  `cert_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `cert_message`  TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `cert_id` INT NOT NULL,
  `cert_issuer` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_fullname` varchar(255) NOT NULL DEFAULT '',
  `assigned` datetime NOT NULL,
  `identifier` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `expires` datetime DEFAULT NULL,
  `template_id` INT,
  `user_id` INT DEFAULT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL
  ) $tbl_options");

$db->query("CREATE TABLE `course_certificate_template` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `course_id` INT NOT NULL,
  `certificate_template_id` MEDIUMINT NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`certificate_template_id`) REFERENCES `certificate_template` (`id`) ON DELETE CASCADE
) $tbl_options");

$db->query("CREATE TABLE `course_prerequisite` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT,
  `course_id` INT not null,
  `prerequisite_course` INT not null,
  PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `unit_prerequisite` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT,
  `course_id` INT not null,
  `unit_id` INT not null,
  `prerequisite_unit` INT not null,
  PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `session_prerequisite` (
    `id` INT unsigned NOT NULL AUTO_INCREMENT,
    `course_id` INT not null,
    `session_id` INT not null,
    `prerequisite_session` INT not null,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `user_consent` (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  has_accepted BOOL NOT NULL DEFAULT 0,
  ts DATETIME,
  PRIMARY KEY (id),
  UNIQUE KEY (user_id),
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) $tbl_options");


// `request` tables (aka `ticketing`)
$db->query("CREATE TABLE IF NOT EXISTS request_type (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` MEDIUMTEXT NOT NULL,
    `description` MEDIUMTEXT NULL DEFAULT NULL) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS request (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `creator_id` INT NOT NULL,
    `state` TINYINT NOT NULL,
    `type` INT UNSIGNED NOT NULL,
    `open_date` DATETIME NOT NULL,
    `change_date` DATETIME NOT NULL,
    `close_date` DATETIME,
    PRIMARY KEY(id),
    FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
    FOREIGN KEY (`type`) REFERENCES request_type(id) ON DELETE CASCADE,
    FOREIGN KEY (creator_id) REFERENCES user(id)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `request_field` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type_id` INT UNSIGNED NOT NULL,
    `name` MEDIUMTEXT NOT NULL,
    `description` MEDIUMTEXT NULL DEFAULT NULL,
    `datatype` INT NOT NULL,
    `sortorder` INT NOT NULL DEFAULT 0,
    `values` MEDIUMTEXT DEFAULT NULL,
    FOREIGN KEY (type_id) REFERENCES request_type(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS `request_field_data` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT UNSIGNED NOT NULL,
    `field_id` INT UNSIGNED NOT NULL,
    `data` TEXT NOT NULL,
    FOREIGN KEY (field_id) REFERENCES request_field(id) ON DELETE CASCADE,
    UNIQUE KEY (`request_id`, `field_id`)) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS request_watcher (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `type` TINYINT NOT NULL,
    `notification` TINYINT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (request_id, user_id),
    FOREIGN KEY (request_id) REFERENCES request(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE IF NOT EXISTS request_action (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_id` INT UNSIGNED NOT NULL,
    `user_id` INT NOT NULL,
    `ts` DATETIME NOT NULL,
    `old_state` TINYINT NOT NULL,
    `new_state` TINYINT NOT NULL,
    `filename` VARCHAR(256),
    `real_filename` VARCHAR(255),
    `comment` TEXT,
    PRIMARY KEY(id),
    FOREIGN KEY (request_id) REFERENCES request(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `user_settings` (
  `setting_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `course_id` INT DEFAULT NULL,
  `value` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`setting_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `user_settings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_settings_ibfk_4` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE ) $tbl_options");

// learning analytics

$db->query("CREATE TABLE `analytics` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courseID` INT NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `active` TINYINT NOT NULL DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `periodType` INT NOT NULL,
  PRIMARY KEY (id)) $tbl_options");

$db->query("CREATE TABLE `analytics_element` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `analytics_id` INT NOT NULL,
  `module_id` INT NOT NULL,
  `resource` INT DEFAULT NULL,
  `upper_threshold` float DEFAULT NULL,
  `lower_threshold` float DEFAULT NULL,
  `weight` INT NOT NULL DEFAULT '1',
  `min_value` float NOT NULL,
  `max_value` float NOT NULL,
  PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE `user_analytics` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `analytics_element_id` INT NOT NULL,
  `value` float NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)) $tbl_options");

// Course pages

$db->query("CREATE TABLE `page` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `course_id` INT DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `path` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `visible` TINYINT DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `course_id_index` (`course_id`)) $tbl_options");

// H5P

$db->query("CREATE TABLE h5p_library (
    id INT NOT NULL AUTO_INCREMENT,
    machine_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
    major_version INT NOT NULL,
    minor_version INT NOT NULL,
    patch_version INT NOT NULL,
    runnable INT NOT NULL DEFAULT '0',
    fullscreen INT NOT NULL DEFAULT '0',
    embed_types VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    preloaded_js LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    preloaded_css LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    droplibrary_css LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    semantics LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    add_to LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    core_major INT,
    core_minor INT,
    metadata_settings LONGTEXT,
    tutorial LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    example LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE h5p_library_dependency (
    id INT NOT NULL AUTO_INCREMENT,
    library_id INT NOT NULL,
    required_library_id INT NOT NULL,
    dependency_type VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE h5p_library_translation (
    id INT NOT NULL,
    library_id INT NOT NULL,
    language_code VARCHAR(255) NOT NULL,
    language_json TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE h5p_content (
    id INT NOT NULL AUTO_INCREMENT,
    title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    main_library_id INT NOT NULL,
    params LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
    course_id INT NOT NULL,
    enabled TINYINT NOT NULL DEFAULT 1,
    reuse_enabled TINYINT NOT NULL DEFAULT 1,
    creator_id INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE h5p_content_dependency (
    id INT NOT NULL AUTO_INCREMENT,
    content_id INT NOT NULL,
    library_id INT NOT NULL,
    dependency_type VARCHAR(10) NOT NULL,
  PRIMARY KEY(id)) $tbl_options");

// tables for CCE API
$db->query("CREATE TABLE `api_grade_analytics` (
    id INT NOT NULL AUTO_INCREMENT,
    user_uuid VARCHAR(40) NOT NULL,
    course_uuid VARCHAR(40) NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY(id)) $tbl_options");

$db->query("CREATE TABLE `api_course_completion` (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
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

$db->query("CREATE TABLE ai_providers (    
    `id` smallint NOT NULL AUTO_INCREMENT,
    `name` text CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `api_key` text CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `model_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    `provider_type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
    `endpoint_url` varchar(255) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
    `enabled` tinyint NOT NULL,
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expired` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)) $tbl_options");

$db->query("CREATE TABLE ai_modules (
    `id` SMALLINT NOT NULL AUTO_INCREMENT, 
    `ai_module_id` SMALLINT NOT NULL DEFAULT 0, 
    `ai_provider_id` SMALLINT DEFAULT 0,
    `all_courses` TINYINT NOT NULL DEFAULT 1, 
    PRIMARY KEY(ID)) $tbl_options");

$db->query("CREATE TABLE `ai_courses` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `course_id` int NOT NULL,
    `ai_module` int NOT NULL,
    PRIMARY KEY (`id`), KEY (`ai_module`, `course_id`))  $tbl_options");

$db->query("CREATE TABLE `course_invitation` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `surname` varchar(255) NOT NULL DEFAULT '',
    `givenname` varchar(255) NOT NULL DEFAULT '',
    `email` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
    `identifier` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
    `created_at` datetime NOT NULL,
    `expires_at` datetime DEFAULT NULL,
    `registered_at` datetime DEFAULT NULL,
    `course_id` INT DEFAULT NULL,
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
    `id` INT NOT NULL AUTO_INCREMENT,
    `minedu_id` INT NOT NULL DEFAULT 0,
    `department_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`department_id`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $tbl_options");

$db->query('CREATE TABLE `login_lock` (
   `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `user_id` INT NOT NULL,
   `session_id` VARCHAR(48) NOT NULL COLLATE ascii_bin,
   `ts` DATETIME NOT NULL,
   FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
   UNIQUE KEY (session_id)) CHARACTER SET ascii ENGINE=InnoDB');

$db->query("CREATE TABLE `zoom_user` (
      `user_id` INT NOT NULL,
      `id` varchar(45) NOT NULL,
      `first_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
      `last_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
      `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
      `type` TINYINT NOT NULL DEFAULT 1,
      `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (user_id)) $tbl_options");

$db->query("CREATE TABLE `mod_session` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `creator` INT NOT NULL DEFAULT 0,
        `title` VARCHAR(255) NOT NULL DEFAULT '',
        `comments` MEDIUMTEXT,
        `type` VARCHAR(255) NOT NULL DEFAULT '',
        `type_remote` INT NOT NULL DEFAULT 0,
        `start` DATETIME DEFAULT NULL,
        `finish` DATETIME DEFAULT NULL,
        `visible` TINYINT,
        `public` TINYINT NOT NULL DEFAULT 1,
        `order` INT NOT NULL DEFAULT 0,
        `course_id` INT NOT NULL,
        `consent` INT NOT NULL DEFAULT 1,
        PRIMARY KEY(id),
        FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `mod_session_users` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `session_id` INT NOT NULL DEFAULT 0,
            `participants` INT NOT NULL DEFAULT 0,
            `is_accepted` INT NOT NULL DEFAULT 0,
            PRIMARY KEY(id),
            FOREIGN KEY (session_id) REFERENCES mod_session(id) ON DELETE CASCADE) $tbl_options");


$db->query("CREATE TABLE `session_resources` (
                            `id` INT NOT NULL AUTO_INCREMENT,
                            `session_id` INT NOT NULL DEFAULT 0,
                            `title` VARCHAR(255) NOT NULL DEFAULT '',
                            `comments` MEDIUMTEXT,
                            `res_id` INT NOT NULL DEFAULT 0,
                            `type` VARCHAR(255) NOT NULL DEFAULT '',
                            `visible` TINYINT,
                            `order` INT NOT NULL DEFAULT 0,
                            `date` DATETIME NOT NULL,
                            `doc_id` INT NOT NULL DEFAULT 0,
                            `is_completed` INT NOT NULL DEFAULT 0,
                            `from_user` INT NOT NULL DEFAULT 0,
                            `deliverable_comments` TEXT DEFAULT NULL,
                            `passage` TEXT DEFAULT NULL,
                            PRIMARY KEY(id),
                            FOREIGN KEY (session_id) REFERENCES mod_session(id) ON DELETE CASCADE) $tbl_options");

$db->query("CREATE TABLE `mod_session_completion` (
                            `id` INT NOT NULL AUTO_INCREMENT,
                            `course_id` INT NOT NULL,
                            `session_id` INT NOT NULL,
                            PRIMARY KEY (`id`),
                            FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
                            FOREIGN KEY (`session_id`) REFERENCES `mod_session` (`id`) ON DELETE CASCADE) $tbl_options");


$db->query("CREATE TABLE `session_user_material` (
          `id` INT NOT NULL AUTO_INCREMENT,
          `course_id` INT NOT NULL,
          `session_id` INT NOT NULL,
          `user_id` INT NOT NULL,
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

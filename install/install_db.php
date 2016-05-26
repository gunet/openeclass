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

/**
 * @file install_db.php
 * @brief installation data base queries
 */
use Hautelook\Phpass\PasswordHash;
require_once '../modules/db/foreignkeys.php';

if (!defined('ECLASS_VERSION')) {
    exit;
}

set_time_limit(0);

// set default storage engine
Database::core()->query("SET storage_engine = InnoDB");
// create eclass database
Database::core()->query("CREATE DATABASE IF NOT EXISTS `$mysqlMainDb` CHARACTER SET utf8");

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
$db->query("DROP TABLE IF EXISTS om_servers");
$db->query("DROP TABLE IF EXISTS bbb_servers");
$db->query("DROP TABLE IF EXISTS bbb_session");

$charset_spec = 'DEFAULT CHARACTER SET=utf8';

// create tables

$db->query("CREATE TABLE IF NOT EXISTS `course_module` (
  `id` int(11) NOT NULL auto_increment,
  `module_id` int(11) NOT NULL,
  `visible` tinyint(4) NOT NULL,
  `course_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `module_course` (`module_id`,`course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS module_disable (
    module_id int(11) NOT NULL PRIMARY KEY) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default 0,
  `course_id` int(11) NOT NULL default 0,
  `module_id` int(11) NOT NULL default 0,
  `details` text NOT NULL,
  `action_type` int(11) NOT NULL default 0,
  `ts` datetime NOT NULL,
  `ip` varchar(45) NOT NULL default 0,
  PRIMARY KEY  (`id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `log_archive` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default 0,
  `course_id` int(11) NOT NULL default 0,
  `module_id` int(11) NOT NULL default 0,
  `details` text NOT NULL,
  `action_type` int(11) NOT NULL default 0,
  `ts` datetime NOT NULL,
  `ip` varchar(45) NOT NULL default 0,
  PRIMARY KEY  (`id`)) $charset_spec");

$db->query("CREATE TABLE `announcement` (
    `id` MEDIUMINT(11) NOT NULL auto_increment,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `content` TEXT,
    `date` DATETIME NOT NULL,
    `course_id` INT(11) NOT NULL DEFAULT 0,
    `order` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `visible` TINYINT(4) NOT NULL DEFAULT 0,
    `start_display` DATETIME DEFAULT NULL,
    `stop_display` DATETIME DEFAULT NULL,
    PRIMARY KEY (id)) $charset_spec");

$db->query("CREATE TABLE `admin_announcement` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `body` TEXT,
    `date` DATETIME NOT NULL,
    `begin` DATETIME DEFAULT NULL,
    `end` DATETIME DEFAULT NULL,
    `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
    `order` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `visible` TINYINT(4)) $charset_spec");

$db->query("CREATE TABLE `agenda` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `start` DATETIME NOT NULL,
    `duration` VARCHAR(20) NOT NULL,
    `visible` TINYINT(4),
    `recursion_period` varchar(30) DEFAULT NULL,
    `recursion_end` date DEFAULT NULL,
    `source_event_id` int(11) DEFAULT NULL)
    $charset_spec");

$db->query("CREATE TABLE `course` (
  `id` INT(11) NOT NULL auto_increment,
  `code` VARCHAR(20) NOT NULL,
  `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
  `title` VARCHAR(250) NOT NULL DEFAULT '',
  `keywords` TEXT NOT NULL,
  `course_license` TINYINT(4) NOT NULL DEFAULT 0,
  `visible` TINYINT(4) NOT NULL,
  `prof_names` VARCHAR(200) NOT NULL DEFAULT '',
  `public_code` VARCHAR(20) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL,
  `doc_quota` FLOAT NOT NULL default '104857600',
  `video_quota` FLOAT NOT NULL default '104857600',
  `group_quota` FLOAT NOT NULL default '104857600',
  `dropbox_quota` FLOAT NOT NULL default '104857600',
  `password` VARCHAR(50) NOT NULL DEFAULT '',
  `glossary_expand` BOOL NOT NULL DEFAULT 0,
  `glossary_index` BOOL NOT NULL DEFAULT 1,
  `view_type` VARCHAR(255) NOT NULL DEFAULT 'units',
  `start_date` DATE DEFAULT NULL,
  `finish_date` DATE DEFAULT NULL,
  `description` MEDIUMTEXT DEFAULT NULL,
  `home_layout` TINYINT(1) NOT NULL DEFAULT 1,
  `course_image` VARCHAR(400) NULL,
  PRIMARY KEY  (`id`)) $charset_spec");

$db->query("CREATE TABLE `course_weekly_view` (
  `id` INT(11) NOT NULL auto_increment,
  `course_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `comments` MEDIUMTEXT,
  `start_week` DATE NOT NULL,
  `finish_week` DATE NOT NULL,
  `visible` TINYINT(4) NOT NULL DEFAULT 1,
  `public` TINYINT(4) NOT NULL DEFAULT 1,
  `order` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)) $charset_spec");

$db->query("CREATE TABLE `course_weekly_view_activities` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `course_weekly_view_id` INT(11) NOT NULL ,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `comments` MEDIUMTEXT,
    `res_id` INT(11) NOT NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT '',
    `visible` TINYINT(4),
    `order` INT(11) NOT NULL DEFAULT 0,
    `date` DATETIME NOT NULL) $charset_spec");

$db->query("CREATE TABLE `course_user` (
      `course_id` INT(11) NOT NULL DEFAULT 0,
      `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
      `status` TINYINT(4) NOT NULL DEFAULT 0,
      `tutor` INT(11) NOT NULL DEFAULT 0,
      `editor` INT(11) NOT NULL DEFAULT 0,
      `reviewer` INT(11) NOT NULL DEFAULT 0,
      `reg_date` DATETIME NOT NULL,
      `receive_mail` BOOL NOT NULL DEFAULT 1,
      `document_timestamp` datetime NOT NULL,
      PRIMARY KEY (course_id, user_id)) $charset_spec");

$db->query("CREATE TABLE `course_user_request` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL,
    `course_id` int(11) NOT NULL,
    `comments` text,
    `status` int(11) NOT NULL,
    `ts` datetime NOT NULL,
    PRIMARY KEY (`id`))  $charset_spec");

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
    PRIMARY KEY (`id`)) $charset_spec");

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

$db->query("CREATE TABLE IF NOT EXISTS `course_description` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `comments` mediumtext,
    `type` smallint(6),
    `visible` tinyint(4) DEFAULT 0,
    `order` int(11) NOT NULL,
    `update_dt` datetime NOT NULL,
    PRIMARY KEY (`id`)) $charset_spec");

$db->query("CREATE TABLE `course_review` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `is_certified` BOOL NOT NULL DEFAULT 0,
    `level` TINYINT(4) NOT NULL DEFAULT 0,
    `last_review` DATETIME NOT NULL,
    `last_reviewer` INT(11) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY cid (course_id)) $charset_spec");

$db->query("CREATE TABLE `user` (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    surname VARCHAR(100) NOT NULL DEFAULT '',
    givenname VARCHAR(100) NOT NULL DEFAULT '',
    username VARCHAR(100) NOT NULL UNIQUE KEY COLLATE utf8_bin,
    password VARCHAR(60) NOT NULL DEFAULT 'empty',
    email VARCHAR(100) NOT NULL DEFAULT '',
    parent_email VARCHAR(100) NOT NULL DEFAULT '',
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
    whitelist TEXT,
    last_passreminder DATETIME DEFAULT NULL) $charset_spec");

$db->query("CREATE TABLE `admin` (
    user_id INT(11) NOT NULL PRIMARY KEY,
    privilege INT(11) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE `login_failure` (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip varchar(45) NOT NULL,
    count tinyint(4) unsigned NOT NULL default 0,
    last_fail datetime NOT NULL,
    UNIQUE KEY ip (ip)) $charset_spec");

$db->query("CREATE TABLE `loginout` (
    idLog mediumint(9) unsigned NOT NULL auto_increment,
    id_user mediumint(9) unsigned NOT NULL default 0,
    ip char(45) NOT NULL default '0.0.0.0',
    loginout.when datetime NOT NULL,
    loginout.action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
    PRIMARY KEY (idLog), KEY `id_user` (`id_user`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `personal_calendar` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `title` varchar(200) NOT NULL,
    `content` text NOT NULL,
    `start` datetime NOT NULL,
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
    PRIMARY KEY (`id`)) $charset_spec");

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
    PRIMARY KEY (`user_id`)) $charset_spec");

$db->query("CREATE TABLE `admin_calendar` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `title` varchar(200) NOT NULL,
    `content` text NOT NULL,
    `start` datetime NOT NULL,
    `duration` time NOT NULL,
    `recursion_period` varchar(30) DEFAULT NULL,
    `recursion_end` date DEFAULT NULL,
    `source_event_id` int(11) DEFAULT NULL,
    `visibility_level` int(11) DEFAULT '1',
    `email_notification` time DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_events` (`user_id`),
    KEY `admin_events_dates` (`start`)) $charset_spec");

// table for loginout rollups
// only contains LOGIN events summed up by a period (typically weekly)
$db->query("CREATE TABLE `loginout_summary` (
    id mediumint unsigned NOT NULL auto_increment,
    login_sum int(11) unsigned  NOT NULL default 0,
    start_date datetime NOT NULL,
    end_date datetime NOT NULL,
    PRIMARY KEY (id)) $charset_spec");

// table keeping data for monthly reports
$db->query("CREATE TABLE monthly_summary (
    id mediumint unsigned NOT NULL auto_increment,
    `month` varchar(20)  NOT NULL default 0,
    profesNum int(11) NOT NULL default 0,
    studNum int(11) NOT NULL default 0,
    visitorsNum int(11) NOT NULL default 0,
    coursNum int(11) NOT NULL default 0,
    logins int(11) NOT NULL default 0,
    details MEDIUMTEXT,
    PRIMARY KEY (id)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `document` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL DEFAULT 0,
    `subsystem` TINYINT(4) NOT NULL,
    `subsystem_id` INT(11) DEFAULT NULL,
    `path` VARCHAR(255) NOT NULL,
    `extra_path` VARCHAR(255) NOT NULL DEFAULT '',
    `filename` VARCHAR(255) NOT NULL,
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
    `lock_user_id` INT(11) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `group_properties` (
    `course_id` INT(11) NOT NULL,
    `group_id` INT(11) NOT NULL PRIMARY KEY,	
    `self_registration` TINYINT(4) NOT NULL DEFAULT 1,
    `multiple_registration` TINYINT(4) NOT NULL DEFAULT 0,
    `allow_unregister` TINYINT(4) NOT NULL DEFAULT 0,
    `forum` TINYINT(4) NOT NULL DEFAULT 1,
    `private_forum` TINYINT(4) NOT NULL DEFAULT 0,
    `documents` TINYINT(4) NOT NULL DEFAULT 1,
    `wiki` TINYINT(4) NOT NULL DEFAULT 0,
    `agenda` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `group` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL DEFAULT 0,
    `name` varchar(100) NOT NULL DEFAULT '',
    `description` TEXT,
    `forum_id` int(11) NULL,
    `category_id` int(11) NULL,
    `max_members` int(11) NOT NULL DEFAULT 0,
    `secret_directory` varchar(30) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `group_members` (
    `group_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `is_tutor` int(11) NOT NULL DEFAULT 0,
    `description` TEXT,
    PRIMARY KEY (`group_id`, `user_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `group_category` (
    `id` INT(6) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    PRIMARY KEY (`id`, `course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `glossary` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `term` VARCHAR(255) NOT NULL,
    `definition` text NOT NULL,
    `url` text,
    `order` INT(11) NOT NULL DEFAULT 0,
    `datestamp` DATETIME NOT NULL,
    `course_id` INT(11) NOT NULL,
    `category_id` INT(11) DEFAULT NULL,
    `notes` TEXT NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `glossary_category` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `order` INT(11) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `attendance` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `limit` TINYINT(4) NOT NULL DEFAULT 0,
    `students_semester` TINYINT(4) NOT NULL DEFAULT 1,
    `active` TINYINT(1) NOT NULL DEFAULT 0,
    `title` VARCHAR(250) DEFAULT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `attendance_activities` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_id` MEDIUMINT(11) NOT NULL,
    `title` VARCHAR(250) DEFAULT NULL,
    `date` DATETIME DEFAULT NULL,
    `description` TEXT NOT NULL,
    `module_auto_id` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `module_auto_type` TINYINT(4) NOT NULL DEFAULT 0,
    `auto` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `attendance_book` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_activity_id` MEDIUMINT(11) NOT NULL,
    `uid` int(11) NOT NULL DEFAULT 0,
    `attend` TINYINT(4) NOT NULL DEFAULT 0,
    `comments` TEXT NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `attendance_users` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `attendance_id` MEDIUMINT(11) NOT NULL,
    `uid` int(11) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `link` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `url` TEXT NOT NULL,
    `title` TEXT NOT NULL,
    `description` TEXT NOT NULL,
    `category` INT(6) DEFAULT 0 NOT NULL,
    `order` INT(6) DEFAULT 0 NOT NULL,    
    `user_id` INT(11) DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`, `course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `link_category` (
    `id` INT(6) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `order` INT(6) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`, `course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `ebook` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `order` INT(11) NOT NULL,
    `title` TEXT,
    `visible` BOOL NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `ebook_section` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ebook_id` INT(11) NOT NULL,
    `public_id` VARCHAR(11) NOT NULL,
    `file` VARCHAR(128),
    `title` TEXT) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `ebook_subsection` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `section_id` VARCHAR(11) NOT NULL,
    `public_id` VARCHAR(11) NOT NULL,
    `file_id` INT(11) NOT NULL,
    `title` TEXT) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `forum` (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) DEFAULT '' NOT NULL,
    `desc` MEDIUMTEXT NOT NULL,
    `num_topics` INT(10) DEFAULT 0 NOT NULL,
    `num_posts` INT(10) DEFAULT 0 NOT NULL,
    `last_post_id` INT(10) DEFAULT 0 NOT NULL,
    `cat_id` INT(10) DEFAULT 0 NOT NULL,
    `course_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `forum_category` (
    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cat_title` VARCHAR(100) DEFAULT '' NOT NULL,
    `cat_order` INT(11) DEFAULT 0 NOT NULL,
    `course_id` INT(11) NOT NULL,
    KEY `forum_category_index` (`id`, `course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `forum_notify` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) DEFAULT 0 NOT NULL,
    `cat_id` INT(11) DEFAULT 0 NOT NULL ,
    `forum_id` INT(11) DEFAULT 0 NOT NULL,
    `topic_id` INT(11) DEFAULT 0 NOT NULL ,
    `notify_sent` BOOL DEFAULT 0 NOT NULL ,
    `course_id` INT(11) DEFAULT 0 NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `forum_post` (
    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `topic_id` INT(10) NOT NULL DEFAULT 0,
    `post_text` MEDIUMTEXT NOT NULL,
    `poster_id` INT(10) NOT NULL DEFAULT 0,
    `post_time` DATETIME,
    `poster_ip` VARCHAR(45) DEFAULT '' NOT NULL,
    `parent_post_id` INT(10) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `forum_topic` (
    `id` INT(10) NOT NULL auto_increment,
    `title` VARCHAR(100) DEFAULT NULL,
    `poster_id` INT(10) DEFAULT NULL,
    `topic_time` DATETIME,
    `num_views` INT(10) NOT NULL DEFAULT 0,
    `num_replies` INT(10) NOT NULL DEFAULT 0,
    `last_post_id` INT(10) NOT NULL DEFAULT 0,
    `forum_id` INT(10) NOT NULL DEFAULT 0,
    `locked` TINYINT DEFAULT 0 NOT NULL,
    PRIMARY KEY  (`id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `forum_user_stats` (
    `user_id` INT(11) NOT NULL,
    `num_posts` INT(11) NOT NULL,
    `course_id` INT(11) NOT NULL,
    PRIMARY KEY (`user_id`,`course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `video` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `url` VARCHAR(200) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `category` INT(6) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `creator` VARCHAR(200) NOT NULL,
    `publisher` VARCHAR(200) NOT NULL,
    `date` DATETIME NOT NULL,
    `visible` TINYINT(4) NOT NULL DEFAULT 1,
    `public` TINYINT(4) NOT NULL DEFAULT 1) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `videolink` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `url` VARCHAR(200) NOT NULL DEFAULT '',
    `title` VARCHAR(200) NOT NULL DEFAULT '',
    `description` TEXT NOT NULL,
    `category` INT(6) DEFAULT NULL,
    `creator` VARCHAR(200) NOT NULL DEFAULT '',
    `publisher` VARCHAR(200) NOT NULL DEFAULT '',
    `date` DATETIME NOT NULL,
    `visible` TINYINT(4) NOT NULL DEFAULT 1,
    `public` TINYINT(4) NOT NULL DEFAULT 1) $charset_spec");

$db->query("CREATE TABLE `video_category` (
    `id` INT(11) NOT NULL auto_increment,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL, 
    `description` TEXT DEFAULT NULL,
    PRIMARY KEY (id)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_msg (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `author_id` INT(11) UNSIGNED NOT NULL,
    `subject` VARCHAR(250) NOT NULL,
    `body` LONGTEXT NOT NULL,
    `timestamp` INT(11) NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_attachment (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `msg_id` INT(11) UNSIGNED NOT NULL,
    `filename` VARCHAR(250) NOT NULL,
    `real_filename` varchar(255) NOT NULL,
    `filesize` INT(11) UNSIGNED NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS dropbox_index (
    `msg_id` INT(11) UNSIGNED NOT NULL,
    `recipient_id` INT(11) UNSIGNED NOT NULL,
    `is_read` BOOLEAN NOT NULL DEFAULT 0,
    `deleted` BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY (`msg_id`, `recipient_id`)) $charset_spec");

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
    `launch_data` TEXT NOT NULL) $charset_spec");

// COMMENT='List of learning Paths';
$db->query("CREATE TABLE IF NOT EXISTS `lp_learnPath` (
    `learnPath_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `comment` TEXT NOT NULL,
    `lock` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
    `visible` TINYINT(4) NOT NULL DEFAULT 0,
    `rank` INT(11) NOT NULL DEFAULT 0) $charset_spec");

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
    `raw_to_pass` TINYINT(4) NOT NULL DEFAULT 50) $charset_spec");

// COMMENT='List of resources of module of learning paths';
$db->query("CREATE TABLE IF NOT EXISTS `lp_asset` (
    `asset_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `module_id` INT(11) NOT NULL DEFAULT 0,
    `path` VARCHAR(255) NOT NULL DEFAULT '',
    `comment` VARCHAR(255) default NULL) $charset_spec");

// COMMENT='Record the last known status of the user in the course';
$db->query("CREATE TABLE IF NOT EXISTS `lp_user_module_progress` (
    `user_module_progress_id` INT(22) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
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
    `credit` enum('CREDIT','NO-CREDIT') NOT NULL DEFAULT 'NO-CREDIT') $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_properties` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NULL,
    `group_id` INT(11) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_acls` (
    `wiki_id` INT(11) UNSIGNED NOT NULL,
    `flag` VARCHAR(255) NOT NULL,
    `value` ENUM('false','true') NOT NULL DEFAULT 'false',
    PRIMARY KEY (wiki_id, flag)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_pages` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `wiki_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `owner_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `ctime` DATETIME NOT NULL,
    `last_version` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `last_mtime` DATETIME NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_pages_content` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `editor_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `mtime` DATETIME NOT NULL,
    `content` TEXT NOT NULL,
    `changelog` VARCHAR(200) )  $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `wiki_locks` (
    `ptitle` VARCHAR(255) NOT NULL DEFAULT '',
    `wiki_id` INT(11) UNSIGNED NOT NULL,
    `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `ltime_created` DATETIME DEFAULT NULL,
    `ltime_alive` DATETIME DEFAULT NULL,
    PRIMARY KEY (ptitle, wiki_id) ) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `blog_post` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `content` TEXT NOT NULL,
    `time` DATETIME NOT NULL,
    `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `commenting` TINYINT NOT NULL DEFAULT '1',
    `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `course_id` INT(11) NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT(11) NOT NULL,
    `rtype` VARCHAR(50) NOT NULL,
    `content` TEXT NOT NULL,
    `time` DATETIME NOT NULL,
    `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `rating` (
    `rate_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT(11) NOT NULL,
    `rtype` VARCHAR(50) NOT NULL,
    `value` TINYINT NOT NULL,
    `widget` VARCHAR(30) NOT NULL,
    `time` DATETIME NOT NULL,
    `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `rating_source` VARCHAR(50) NOT NULL,
    INDEX `rating_index_1` (`rid`, `rtype`, `widget`),
    INDEX `rating_index_2` (`rid`, `rtype`, `widget`, `user_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `rating_cache` (
    `rate_cache_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rid` INT(11) NOT NULL,
    `rtype` VARCHAR(50) NOT NULL,
    `value` FLOAT NOT NULL DEFAULT 0,
    `count` INT(11) NOT NULL DEFAULT 0,
    `tag` VARCHAR(50),
    INDEX `rating_cache_index_1` (`rid`, `rtype`, `tag`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `abuse_report` (
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
    INDEX `abuse_report_index_2` (`course_id`, `status`)) $charset_spec");

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
                `data` TEXT NULL DEFAULT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data` (
                `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `field_id` INT(11) NOT NULL,
                `data` TEXT NOT NULL,
                PRIMARY KEY (`user_id`, `field_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_data_pending` (
                `user_request_id` INT(11) NOT NULL DEFAULT 0,
                `field_id` INT(11) NOT NULL,
                `data` TEXT NOT NULL,
                PRIMARY KEY (`user_request_id`, `field_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `custom_profile_fields_category` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` MEDIUMTEXT NOT NULL,
                `sortorder`  INT(11) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `poll` (
    `pid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `creator_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
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
    `type` TINYINT(1) NOT NULL DEFAULT 0,
    `assign_to_specific` TINYINT NOT NULL DEFAULT '0' ) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `poll_to_specific` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11) NULL,
    `group_id` int(11) NULL,
    `poll_id` int(11) NOT NULL ) $charset_spec"); 

$db->query("CREATE TABLE IF NOT EXISTS `poll_user_record` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `email` VARCHAR(255) DEFAULT NULL,
    `email_verification` TINYINT(1) DEFAULT NULL,
    `verification_code` VARCHAR(255) DEFAULT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `poll_answer_record` (
    `arid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `poll_user_record_id` INT(11) NOT NULL,
    `qid` INT(11) NOT NULL DEFAULT 0,
    `aid` INT(11) NOT NULL DEFAULT 0,
    `answer_text` TEXT NOT NULL,
    `submit_date` DATETIME NOT NULL,
    FOREIGN KEY (`poll_user_record_id`) 
    REFERENCES `poll_user_record` (`id`) 
    ON DELETE CASCADE) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `poll_question` (
    `pqid` BIGINT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pid` INT(11) NOT NULL DEFAULT 0,
    `question_text` VARCHAR(250) NOT NULL DEFAULT '',
    `qtype` tinyint(3) UNSIGNED NOT NULL,
    `q_position` INT(11) DEFAULT 1, 
    `q_scale` INT(11) NULL DEFAULT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `poll_question_answer` (
    `pqaid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pqid` INT(11) NOT NULL DEFAULT 0,
    `answer_text` TEXT NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `assignment` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(200) NOT NULL DEFAULT '',
    `description` TEXT NOT NULL,
    `comments` TEXT NOT NULL,
    `submission_type` TINYINT NOT NULL DEFAULT '0',
    `deadline` DATETIME NULL DEFAULT NULL,
    `late_submission` TINYINT NOT NULL DEFAULT '0', 
    `submission_date` DATETIME NOT NULL,
    `active` CHAR(1) NOT NULL DEFAULT '1',
    `secret_directory` VARCHAR(30) NOT NULL,
    `group_submissions` CHAR(1) DEFAULT '0' NOT NULL,
    `max_grade` FLOAT DEFAULT '10' NOT NULL,
    `grading_scale_id` INT(11) NOT NULL DEFAULT '0',
    `assign_to_specific` CHAR(1) DEFAULT '0' NOT NULL,
    `file_path` VARCHAR(200) DEFAULT '' NOT NULL,
    `file_name` VARCHAR(200) DEFAULT '' NOT NULL,
    `auto_judge` TINYINT(1) NOT NULL DEFAULT 0,
    `auto_judge_scenarios` TEXT,
    `lang` VARCHAR(10) NOT NULL DEFAULT '') $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `assignment_submit` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `assignment_id` INT(11) NOT NULL DEFAULT 0,
    `submission_date` DATETIME NOT NULL,
    `submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
    `file_path` VARCHAR(200) NOT NULL DEFAULT '',
    `file_name` VARCHAR(200) NOT NULL DEFAULT '',
    `submission_text` MEDIUMTEXT NULL DEFAULT NULL,
    `comments` TEXT NOT NULL,
    `grade` FLOAT DEFAULT NULL,
    `grade_comments` TEXT NOT NULL,
    `grade_submission_date` DATE NOT NULL DEFAULT '1000-10-10',
    `grade_submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
    `group_id` INT( 11 ) DEFAULT NULL,
    `auto_judge_scenarios_output` TEXT) $charset_spec");

// grading scales table
$db->query("CREATE TABLE IF NOT EXISTS `grading_scale` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` varchar(255) NOT NULL,
    `scales` text NOT NULL,
    `course_id` int(11) NOT NULL,
    KEY `course_id` (`course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `assignment_to_specific` (
    `user_id` int(11) NOT NULL,
    `group_id` int(11) NOT NULL,
    `assignment_id` int(11) NOT NULL,
    PRIMARY KEY (user_id, group_id, assignment_id)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `exercise` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(250) DEFAULT NULL,
    `description` TEXT,
    `type` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `temp_save` TINYINT(1) NOT NULL DEFAULT 0,
    `time_constraint` INT(11) DEFAULT 0,
    `attempts_allowed` INT(11) DEFAULT 0,
    `random` SMALLINT(6) NOT NULL DEFAULT 0,
    `active` TINYINT(4) DEFAULT NULL,
    `public` TINYINT(4) NOT NULL DEFAULT 1,
    `results` TINYINT(1) NOT NULL DEFAULT 1,
    `score` TINYINT(1) NOT NULL DEFAULT 1,
    `assign_to_specific` TINYINT NOT NULL DEFAULT '0',
    `ip_lock` TEXT NULL DEFAULT NULL,
    `password_lock` VARCHAR(255) NULL DEFAULT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_to_specific` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11) NULL,
    `group_id` int(11) NULL,
    `exercise_id` int(11) NOT NULL ) $charset_spec"); 

$db->query("CREATE TABLE IF NOT EXISTS `exercise_user_record` (
    `eurid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eid` INT(11) NOT NULL DEFAULT 0,
    `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `record_start_date` DATETIME NOT NULL,
    `record_end_date` DATETIME DEFAULT NULL,
    `total_score` FLOAT(11,2) NOT NULL DEFAULT 0,
    `total_weighting` FLOAT(11,2) DEFAULT 0,
    `attempt` INT(11) NOT NULL DEFAULT 0,
    `attempt_status` tinyint(4) NOT NULL DEFAULT 1,
    `secs_remaining` INT(11) NOT NULL DEFAULT '0') $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_answer_record` (
    `answer_record_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eurid` int(11) NOT NULL,
    `question_id` int(11) NOT NULL,
    `answer` text,
    `answer_id` int(11) NOT NULL,
    `weight` float(11,2) DEFAULT NULL,
    `is_answered` TINYINT NOT NULL DEFAULT '1') $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_question` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `question` TEXT,
    `description` TEXT,
    `weight` FLOAT(11,2) DEFAULT NULL,
    `q_position` INT(11) DEFAULT 1,
    `type` INT(11) DEFAULT 1,
    `difficulty` INT(1) DEFAULT 0,
    `category` INT(11) DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_question_cats` (
    `question_cat_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `question_cat_name` VARCHAR(300) NOT NULL,
    `course_id` INT(11) NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_answer` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT(11) NOT NULL DEFAULT 0,
    `answer` TEXT,
    `correct` INT(11) DEFAULT NULL,
    `comment` TEXT,
    `weight` FLOAT(5,2),
`r_position` INT(11) DEFAULT NULL ) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `exercise_with_questions` (
    `question_id` INT(11) NOT NULL DEFAULT 0,
    `exercise_id` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (question_id, exercise_id) ) $charset_spec");

// hierarchy tables
$db->query("CREATE TABLE IF NOT EXISTS `hierarchy` (
    `id` int(11) NOT NULL auto_increment PRIMARY KEY,
    `code` varchar(20),
    `name` text NOT NULL,
    `number` int(11) NOT NULL default 1000,
    `generator` int(11) NOT NULL default 100,
    `lft` int(11) NOT NULL,
    `rgt` int(11) NOT NULL,
    `allow_course` boolean not null default false,
    `allow_user` boolean NOT NULL default false,
    `order_priority` int(11) default null,
    KEY `lftindex` (`lft`),
    KEY `rgtindex` (`rgt`) ) $charset_spec");

$db->query("INSERT INTO `hierarchy` (code, name, lft, rgt)
    VALUES ('', ?s, 1, 68)", $institutionForm);

$db->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA', ?s, '10', '100', 2, 23, true, true)", $langHierarchyTestDepartment);
$db->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMAPRE', ?s, '10', '100', 3, 20, true, true)", $langHierarchyTestCategory . ' 1');
$db->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA1', ?s, '10', '100', 4, 5, true, true)", $langHierarchyTestSubCategory . ' 1');
$db->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA2', ?s, '10', '100', 6, 7, true, true)", $langHierarchyTestSubCategory . ' 2');
$db->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMAPOST', ?s, '10', '100', '21', '22', true, true)", $langHierarchyTestCategory . ' 2');

$db->query("CREATE TABLE `course_department` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course` INT(11) NOT NULL,
    `department` INT(11) NOT NULL,
    UNIQUE KEY `cdep_unique` (`course`,`department`),
    FOREIGN KEY (`course`) REFERENCES `course` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $charset_spec");

$db->query("CREATE TABLE `user_department` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` INT(11) NOT NULL,
    `department` INT(11) NOT NULL,
    UNIQUE KEY `udep_unique` (`user`,`department`),
    FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department`) REFERENCES `hierarchy` (`id`) ON DELETE CASCADE) $charset_spec"); 

// hierarchy stored procedures
$db->query("DROP PROCEDURE IF EXISTS `add_node`");
$db->query("CREATE PROCEDURE `add_node` (IN name TEXT CHARSET utf8, IN parentlft INT(11),
            IN p_code VARCHAR(20) CHARSET utf8, IN p_allow_course BOOLEAN,
            IN p_allow_user BOOLEAN, IN p_order_priority INT(11))
        LANGUAGE SQL
        BEGIN
            DECLARE lft, rgt INT(11);

            SET lft = parentlft + 1;
            SET rgt = parentlft + 2;

            CALL shift_right(parentlft, 2, 0);

            INSERT INTO `hierarchy` (name, lft, rgt, code, allow_course, allow_user, order_priority) VALUES (name, lft, rgt, p_code, p_allow_course, p_allow_user, p_order_priority);
        END");

$db->query("DROP PROCEDURE IF EXISTS `add_node_ext`");
$db->query("CREATE PROCEDURE `add_node_ext` (IN name TEXT CHARSET utf8, IN parentlft INT(11),
            IN p_code VARCHAR(20) CHARSET utf8, IN p_number INT(11), IN p_generator INT(11),
            IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN, IN p_order_priority INT(11))
        LANGUAGE SQL
        BEGIN
            DECLARE lft, rgt INT(11);

            SET lft = parentlft + 1;
            SET rgt = parentlft + 2;

            CALL shift_right(parentlft, 2, 0);

            INSERT INTO `hierarchy` (name, lft, rgt, code, number, generator, allow_course, allow_user, order_priority) VALUES (name, lft, rgt, p_code, p_number, p_generator, p_allow_course, p_allow_user, p_order_priority);
        END");

$db->query("DROP PROCEDURE IF EXISTS `update_node`");
$db->query("CREATE PROCEDURE `update_node` (IN p_id INT(11), IN p_name TEXT CHARSET utf8,
            IN nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11), IN parentlft INT(11),
            IN p_code VARCHAR(20) CHARSET utf8, IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN, IN p_order_priority INT(11))
        LANGUAGE SQL
        BEGIN
            UPDATE `hierarchy` SET name = p_name, lft = p_lft, rgt = p_rgt,
                code = p_code, allow_course = p_allow_course, allow_user = p_allow_user,
                order_priority = p_order_priority WHERE id = p_id;

            IF nodelft <> parentlft THEN
                CALL move_nodes(nodelft, p_lft, p_rgt);
            END IF;
        END");

$db->query("DROP PROCEDURE IF EXISTS `delete_node`");
$db->query("CREATE PROCEDURE `delete_node` (IN p_id INT(11))
        LANGUAGE SQL
        BEGIN
            DECLARE p_lft, p_rgt INT(11);

            SELECT lft, rgt INTO p_lft, p_rgt FROM `hierarchy` WHERE id = p_id;
            DELETE FROM `hierarchy` WHERE id = p_id;

            CALL delete_nodes(p_lft, p_rgt);
        END");

$db->query("DROP PROCEDURE IF EXISTS `shift_right`");
$db->query("CREATE PROCEDURE `shift_right` (IN node INT(11), IN shift INT(11), IN maxrgt INT(11))
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

$db->query("DROP PROCEDURE IF EXISTS `shift_left`");
$db->query("CREATE PROCEDURE `shift_left` (IN node INT(11), IN shift INT(11), IN maxrgt INT(11))
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

$db->query("DROP PROCEDURE IF EXISTS `shift_end`");
$db->query("CREATE PROCEDURE `shift_end` (IN p_lft INT(11), IN p_rgt INT(11), IN maxrgt INT(11))
        LANGUAGE SQL
        BEGIN
            UPDATE `hierarchy`
            SET lft = (lft - (p_lft - 1)) + maxrgt,
                rgt = (rgt - (p_lft - 1)) + maxrgt WHERE lft BETWEEN p_lft AND p_rgt;
        END");

$db->query("DROP PROCEDURE IF EXISTS `get_maxrgt`");
$db->query("CREATE PROCEDURE `get_maxrgt` (OUT maxrgt INT(11))
        LANGUAGE SQL
        BEGIN
            SELECT rgt INTO maxrgt FROM `hierarchy` ORDER BY rgt DESC LIMIT 1;
        END");

$db->query("DROP PROCEDURE IF EXISTS `get_parent`");
$db->query("CREATE PROCEDURE `get_parent` (IN p_lft INT(11), IN p_rgt INT(11))
        LANGUAGE SQL
        BEGIN
            SELECT * FROM `hierarchy` WHERE lft < p_lft AND rgt > p_rgt ORDER BY lft DESC LIMIT 1;
        END");

$db->query("DROP PROCEDURE IF EXISTS `delete_nodes`");
$db->query("CREATE PROCEDURE `delete_nodes` (IN p_lft INT(11), IN p_rgt INT(11))
        LANGUAGE SQL
        BEGIN
            DECLARE node_width INT(11);
            SET node_width = p_rgt - p_lft + 1;

            DELETE FROM `hierarchy` WHERE lft BETWEEN p_lft AND p_rgt;
            UPDATE `hierarchy` SET rgt = rgt - node_width WHERE rgt > p_rgt;
            UPDATE `hierarchy` SET lft = lft - node_width WHERE lft > p_lft;
        END");

$db->query("DROP PROCEDURE IF EXISTS `move_nodes`");
$db->query("CREATE PROCEDURE `move_nodes` (INOUT nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11))
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

// encrypt the admin password into DB
$hasher = new PasswordHash(8, false);
$password_encrypted = $hasher->HashPassword($passForm);
$admin_uid = $db->query("INSERT INTO `user`
    (`givenname`, `surname`, `username`, `password`, `email`, `status`, `lang`,
     `registered_at`,`expires_at`, `verified_mail`, `whitelist`, `description`)
    VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, " . DBHelper::timeAfter() . ", " .
            DBHelper::timeAfter(5 * 365 * 24 * 60 * 60) . ", ?d, ?s, ?s)",
    $nameForm, '', $loginForm, $password_encrypted, $emailForm, 1, $lang, 1,
    '*,,', 'Administrator')->lastInsertID;
$db->query("INSERT INTO loginout (`id_user`, `ip`, `when`, `action`)
    VALUES (?d, ?s, " . DBHelper::timeAfter() . ", ?s)",
    $admin_uid, $_SERVER['REMOTE_ADDR'], 'LOGIN');

$db->query("INSERT INTO admin (user_id, privilege) VALUES (?d, ?d)", $admin_uid, 0);

$db->query("CREATE TABLE `user_request` (
    id INT(11) NOT NULL AUTO_INCREMENT,
    givenname VARCHAR(60) NOT NULL DEFAULT '',
    surname VARCHAR(60) NOT NULL DEFAULT '',
    username VARCHAR(50) NOT NULL DEFAULT '',
    password VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(100) NOT NULL DEFAULT '',
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
    PRIMARY KEY (id)) $charset_spec");

$db->query("CREATE TABLE `auth` (
    `auth_id` int(2) NOT NULL auto_increment,
    `auth_name` varchar(20) NOT NULL default '',
    `auth_settings` text ,
    `auth_instructions` text ,
    `auth_title` text,
    `auth_default` tinyint(1) NOT NULL default 0,
    PRIMARY KEY (`auth_id`))
    $charset_spec");

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
    $charset_spec"); 

$db->query("CREATE TABLE `user_request_ext_uid` (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_request_id INT(11) NOT NULL,
    auth_id INT(2) NOT NULL,
    uid VARCHAR(64) NOT NULL,
    UNIQUE KEY (user_request_id, auth_id),
    FOREIGN KEY (`user_request_id`) REFERENCES `user_request` (`id`) ON DELETE CASCADE)
    $charset_spec"); 

$eclass_stud_reg = intval($eclass_stud_reg);
$eclass_prof_reg = intval($eclass_prof_reg);

$student_upload_whitelist = 'pdf, ps, eps, tex, latex, dvi, texinfo, texi, zip, rar, tar, bz2, gz, 7z, xz, lha, lzh, z, Z, doc, docx, odt, ott, sxw, stw, fodt, txt, rtf, dot, mcw, wps, xls, xlsx, xlt, ods, ots, sxc, stc, fods, uos, csv, ppt, pps, pot, pptx, ppsx, odp, otp, sxi, sti, fodp, uop, potm, odg, otg, sxd, std, fodg, odb, mdb, ttf, otf, jpg, jpeg, png, gif, bmp, tif, tiff, psd, dia, svg, ppm, xbm, xpm, ico, avi, asf, asx, wm, wmv, wma, dv, mov, moov, movie, mp4, mpg, mpeg, 3gp, 3g2, m2v, aac, m4a, flv, f4v, m4v, mp3, swf, webm, ogv, ogg, mid, midi, aif, rm, rpm, ram, wav, mp2, m3u, qt, vsd, vss, vst';
$teacher_upload_whitelist = 'htm, html, js, css, xml, xsl, cpp, c, java, m, h, tcl, py, sgml, sgm, ini, ds_store';

$db->query("CREATE TABLE `config` (
    `key` VARCHAR(32) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`key`)) $charset_spec");

$default_config = array(
    'base_url', $urlForm,
    'default_language', $lang,
    'dont_display_login_form', 0,
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
    'eclass_prof_reg', $eclass_prof_reg,
    'course_multidep', 0,
    'user_multidep', 0,
    'restrict_owndep', 0,
    'restrict_teacher_owndep', 0,
    'allow_teacher_clone_course', 0,
    'max_glossary_terms', '250',
    'phpSysInfoURL', $phpSysInfoURL,
    'email_sender', $emailForm,
    'admin_name', $nameForm,
    'email_helpdesk', $helpdeskmail,
    'site_name', $campusForm,
    'phone', $helpdeskForm,
    'fax', $faxForm,
    'postaddress', $postaddressForm,
    'institution', $institutionForm,
    'institution_url', $institutionUrlForm,
    'account_duration', '126144000',
    'language', $lang,
    'active_ui_languages', $active_ui_languages,
    'student_upload_whitelist', $student_upload_whitelist,
    'teacher_upload_whitelist', $teacher_upload_whitelist,
    'theme', 'default',
    'theme_options_id', 0,
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
    'course_guest', 'link',
    'version', ECLASS_VERSION);

$db->query("INSERT INTO `config` (`key`, `value`) VALUES " .
        implode(', ', array_fill(0, count($default_config) / 2, '(?s, ?s)')), $default_config);

store_mail_config();

// table for cron parameters
$db->query("CREATE TABLE `cron_params` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `last_run` DATETIME NOT NULL) $charset_spec");

// tables for units module
$db->query("CREATE TABLE `course_units` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `comments` MEDIUMTEXT,
    `visible` TINYINT(4),
    `public` TINYINT(4) NOT NULL DEFAULT 1,
    `order` INT(11) NOT NULL DEFAULT 0,
    `course_id` INT(11) NOT NULL) $charset_spec");

$db->query("CREATE TABLE `unit_resources` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `unit_id` INT(11) NOT NULL ,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `comments` MEDIUMTEXT,
    `res_id` INT(11) NOT NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT '',
    `visible` TINYINT(4),
    `order` INT(11) NOT NULL DEFAULT 0,
    `date` DATETIME NOT NULL) $charset_spec");

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
    KEY `actionsdailycourseindex` (`course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `actions_summary` (
    `id` int(11) NOT NULL auto_increment,
    `module_id` int(11) NOT NULL,
    `visits` int(11) NOT NULL,
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `duration` int(11) NOT NULL,
    `course_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `logins` (
    `id` int(11) NOT NULL auto_increment,
    `user_id` int(11) NOT NULL,
    `ip` char(45) NOT NULL default '0.0.0.0',
    `date_time` datetime NOT NULL,
    `course_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`)) $charset_spec");

// bbb_servers table
$db->query("CREATE TABLE IF NOT EXISTS `bbb_servers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `hostname` varchar(255) DEFAULT NULL,
    `ip` varchar(255) NOT NULL,
    `enabled` enum('true','false') DEFAULT NULL,
    `server_key` varchar(255) DEFAULT NULL,
    `api_url` varchar(255) DEFAULT NULL,
    `max_rooms` int(11) DEFAULT NULL,
    `max_users` int(11) DEFAULT NULL,
    `enable_recordings` enum('true','false') DEFAULT NULL,
    `weight` int(11) DEFAULT NULL,
    `all_courses` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_bbb_servers` (`hostname`)) $charset_spec");

// bbb_sessions tables
$db->query("CREATE TABLE IF NOT EXISTS `bbb_session` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) DEFAULT NULL,
    `title` varchar(255) DEFAULT NULL,
    `description` text,
    `start_date` datetime DEFAULT NULL,
    `end_date` datetime DEFAULT NULL,
    `public` enum('0','1') DEFAULT NULL,
    `active` enum('0','1') DEFAULT NULL,
    `running_at` int(11) DEFAULT NULL,
    `meeting_id` varchar(255) DEFAULT NULL,
    `mod_pw` varchar(255) DEFAULT NULL,
    `att_pw` varchar(255) DEFAULT NULL,
    `unlock_interval` int(11) DEFAULT NULL,
    `external_users` varchar(255) DEFAULT NULL,
    `participants` varchar(1000) DEFAULT NULL,
    `record` enum('true','false') DEFAULT 'false',
    `sessionUsers` int(11) DEFAULT 0,
    PRIMARY KEY (`id`)) $charset_spec");

// om_servers table
$db->query("CREATE TABLE IF NOT EXISTS `om_servers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `hostname` varchar(255) DEFAULT NULL,
    `port` varchar(255) DEFAULT NULL,
    `enabled` enum('true','false') DEFAULT NULL,
    `username` varchar(255) DEFAULT NULL,
    `password` varchar(255) DEFAULT NULL,
    `module_key` int(11) DEFAULT NULL,
    `webapp` varchar(255) DEFAULT NULL,
    `max_rooms` int(11) DEFAULT NULL,
    `max_users` int(11) DEFAULT NULL,
    `enable_recordings` enum('true','false') DEFAULT NULL,
    `all_courses` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_om_servers` (`hostname`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `course_external_server` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `external_server` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`external_server`, `course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `course_settings` (
    `setting_id` INT(11) NOT NULL,
    `course_id` INT(11) NOT NULL,
    `value` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`setting_id`, `course_id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) NOT NULL,
    `students_semester` TINYINT(4) NOT NULL DEFAULT 1,
    `range` TINYINT(4) NOT NULL DEFAULT 10,
    `active` TINYINT(1) NOT NULL DEFAULT 0,
    `title` VARCHAR(250) DEFAULT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_activities` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_id` MEDIUMINT(11) NOT NULL,
    `title` VARCHAR(250) DEFAULT NULL,
    `activity_type` INT(11) DEFAULT NULL,
    `date` DATETIME DEFAULT NULL,
    `description` TEXT NOT NULL,
    `weight` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `module_auto_id` MEDIUMINT(11) NOT NULL DEFAULT 0,
    `module_auto_type` TINYINT(4) NOT NULL DEFAULT 0,
    `auto` TINYINT(4) NOT NULL DEFAULT 0,
    `visible` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_book` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_activity_id` MEDIUMINT(11) NOT NULL,
    `uid` int(11) NOT NULL DEFAULT 0,
    `grade` FLOAT NOT NULL DEFAULT -1,
    `comments` TEXT NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `gradebook_users` (
    `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `gradebook_id` MEDIUMINT(11) NOT NULL,
    `uid` int(11) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `oai_record` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL UNIQUE,
    `oai_identifier` varchar(255) DEFAULT NULL,
    `oai_metadataprefix` varchar(255) DEFAULT 'oai_dc',
    `oai_set` varchar(255) DEFAULT 'class:course',
    `datestamp` datetime DEFAULT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `oai_identifier` (`oai_identifier`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `oai_metadata` (
    `id` int(11) NOT NULL auto_increment PRIMARY KEY,
    `oai_record` int(11) NOT NULL references oai_record(id),
    `field` varchar(255) NOT NULL,
    `value` text,
    INDEX `field_index` (`field`)) $charset_spec");

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
    PRIMARY KEY (`id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `idx_queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL UNIQUE,
    PRIMARY KEY (`id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `idx_queue_async` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `request_type` VARCHAR(255) NOT NULL,
    `resource_type` VARCHAR(255) NOT NULL,
    `resource_id` int(11) NOT NULL,
    PRIMARY KEY (`id`)) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `theme_options` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(300) NOT NULL,
    `styles` LONGTEXT NOT NULL,
    PRIMARY KEY (`id`)) $charset_spec");

// Tags tables
$db->query("CREATE TABLE IF NOT EXISTS `tag_element_module` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_id` int(11) NOT NULL,
    `module_id` int(11) NOT NULL,
    `element_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `date` DATETIME DEFAULT NULL,
    `tag_id` int(11) NOT NULL) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS tag (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    UNIQUE KEY (name)) $charset_spec");

// Recycle object table
$db->query("CREATE TABLE IF NOT EXISTS `recyclebin` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tablename` varchar(100) NOT NULL,
    `entryid` int(11) NOT NULL,
    `entrydata` varchar(4000) NOT NULL,
    KEY `entryid` (`entryid`), KEY `tablename` (`tablename`)) $charset_spec");

// Auto-enroll rules tables
$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `status` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_rule_department` (
    `rule` INT(11) NOT NULL,
    `department` INT(11) NOT NULL,
    PRIMARY KEY (rule, department),
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (department) REFERENCES hierarchy(id) ON DELETE CASCADE) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_course` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rule` INT(11) NOT NULL DEFAULT 0,
    `course_id` INT(11) NOT NULL,
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE) $charset_spec");

$db->query("CREATE TABLE IF NOT EXISTS `autoenroll_department` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rule` INT(11) NOT NULL DEFAULT 0,
    `department_id` INT(11) NOT NULL,
    FOREIGN KEY (rule) REFERENCES autoenroll_rule(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES hierarchy(id) ON DELETE CASCADE) $charset_spec");

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
    PRIMARY KEY (`conf_id`,`course_id`)) $charset_spec");

$_SESSION['theme'] = 'default';
$webDir = '..';
importThemes();

// create indices
$db->query("CREATE INDEX `actions_daily_index` ON actions_daily(user_id, module_id, course_id)");
$db->query("CREATE INDEX `actions_summary_index` ON actions_summary(module_id, course_id)");
$db->query("CREATE INDEX `admin_index` ON admin(user_id)");
$db->query("CREATE INDEX `agenda_index` ON agenda(course_id)");
$db->query("CREATE INDEX `ann_index` ON announcement(course_id)");
$db->query("CREATE INDEX `assignment_index` ON assignment(course_id)");
$db->query("CREATE INDEX `assign_submit_index` ON assignment_submit(uid, assignment_id)");
$db->query("CREATE INDEX `assign_spec_index` ON assignment_to_specific(user_id)");
$db->query("CREATE INDEX `att_index` ON attendance(course_id)");
$db->query("CREATE INDEX `att_act_index` ON attendance_activities(attendance_id)");
$db->query("CREATE INDEX `att_book_index` ON attendance_book(attendance_activity_id)");
$db->query("CREATE INDEX `bbb_index` ON bbb_session(course_id)");
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
$db->query('CREATE INDEX `gradebook_users_gid` ON `gradebook_users` (gradebook_id)');

$db->query("CREATE INDEX `tag_element_index` ON `tag_element_module` (course_id, module_id, element_id)");

// The following tuples have been confirmed
$db->query('CREATE INDEX `actions_daily_mcd` ON `actions_daily` (module_id, course_id, day)');
$db->query('CREATE INDEX `actions_daily_hdi` ON `actions_daily` (hits, duration, id)');
$db->query('CREATE INDEX `loginout_ia` ON `loginout` (id_user, action)');
$db->query('CREATE INDEX `announcement_cvo` ON `announcement` (course_id, visible, `order`)');

// Single indices from multiple tuples
$db->query("CREATE INDEX `actions_summary_module_id` ON actions_summary(module_id)");
$db->query("CREATE INDEX `actions_summary_course_id` ON actions_summary(course_id)");

$db->query('CREATE INDEX `doc_course_id` ON document (course_id)');
$db->query('CREATE INDEX `doc_subsystem` ON document (subsystem)');
$db->query('CREATE INDEX `doc_path` ON document (path)');

$db->query("CREATE INDEX `drop_index_recipient_id` ON dropbox_index(recipient_id)");
$db->query("CREATE INDEX `drop_index_is_read` ON dropbox_index(is_read)");

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

$db->query("CREATE INDEX `unit_res_unit_id` ON unit_resources (unit_id)");
$db->query("CREATE INDEX `unit_res_visible` ON unit_resources (visible)");
$db->query("CREATE INDEX `unit_res_res_id` ON unit_resources (res_id)");

$db->query('CREATE INDEX `pcal_start` ON personal_calendar (start)');

$db->query('CREATE INDEX `agenda_start` ON agenda (start)');

$db->query('CREATE INDEX `assignment_deadline` ON assignment (deadline)');



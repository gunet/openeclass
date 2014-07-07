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

require_once '../include/phpass/PasswordHash.php';
require_once '../modules/db/database.php';
require_once '../modules/admin/debug.php';

if (!defined('ECLASS_VERSION')) {
        exit;
}

Database::core()->query("DROP DATABASE IF EXISTS `$mysqlMainDb`");

// set default storage engine
Database::core()->query("SET storage_engine = InnoDB");
// create eclass database
Database::core()->query("CREATE DATABASE `$mysqlMainDb` CHARACTER SET utf8");

// drop old tables if they exist
Database::get()->query("DROP TABLE IF EXISTS admin");
Database::get()->query("DROP TABLE IF EXISTS admin_announcements");
Database::get()->query("DROP TABLE IF EXISTS agenda");
Database::get()->query("DROP TABLE IF EXISTS announcements");
Database::get()->query("DROP TABLE IF EXISTS auth");
Database::get()->query("DROP TABLE IF EXISTS course");
Database::get()->query("DROP TABLE IF EXISTS course_user");
Database::get()->query("DROP TABLE IF EXISTS course_description");
Database::get()->query("DROP TABLE IF EXISTS course_review");
Database::get()->query("DROP TABLE IF EXISTS faculte");
Database::get()->query("DROP TABLE IF EXISTS institution");
Database::get()->query("DROP TABLE IF EXISTS loginout");
Database::get()->query("DROP TABLE IF EXISTS loginout_summary");
Database::get()->query("DROP TABLE IF EXISTS monthly_summary");
Database::get()->query("DROP TABLE IF EXISTS user_request");
Database::get()->query("DROP TABLE IF EXISTS prof_request");
Database::get()->query("DROP TABLE IF EXISTS user");
Database::get()->query("DROP TABLE IF EXISTS oai_record");
Database::get()->query("DROP TABLE IF EXISTS bbb_servers");
Database::get()->query("DROP TABLE IF EXISTS bbb_session");

$charset_spec = 'DEFAULT CHARACTER SET=utf8';

// create tables

#
# table `course_module`
#
Database::get()->query("CREATE TABLE IF NOT EXISTS `course_module` (
  `id` int(11) NOT NULL auto_increment,
  `module_id` int(11) NOT NULL,
  `visible` tinyint(4) NOT NULL,
  `course_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `module_course` (`module_id`,`course_id`)) $charset_spec");

#
# table `log`
#
Database::get()->query("CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default 0,
  `course_id` int(11) NOT NULL default 0,
  `module_id` int(11) NOT NULL default 0,
  `details` text NOT NULL,
  `action_type` int(11) NOT NULL default 0,
  `ts` datetime NOT NULL,
  `ip` varchar(45) NOT NULL default 0,
  PRIMARY KEY  (`id`)) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `log_archive` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default 0,
  `course_id` int(11) NOT NULL default 0,
  `module_id` int(11) NOT NULL default 0,
  `details` text NOT NULL,
  `action_type` int(11) NOT NULL default 0,
  `ts` datetime NOT NULL,
  `ip` varchar(45) NOT NULL default 0,
  PRIMARY KEY  (`id`)) $charset_spec");

#
# table `announcement`
#
Database::get()->query("CREATE TABLE announcement (
	`id` MEDIUMINT(11) NOT NULL auto_increment,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`content` TEXT,
	`date` DATE DEFAULT NULL,
	`course_id` INT(11) NOT NULL DEFAULT 0,
	`order` MEDIUMINT(11) NOT NULL DEFAULT 0,
	`visible` TINYINT(4) NOT NULL DEFAULT 0,
	PRIMARY KEY (id)) $charset_spec");

#
# table admin_announcements
#
Database::get()->query("CREATE TABLE admin_announcement (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`title` VARCHAR(255) NOT NULL,
	`body` TEXT,
	`date` DATETIME NOT NULL,
	`begin` DATETIME DEFAULT NULL,
	`end` DATETIME DEFAULT NULL,
	`lang` VARCHAR(16) NOT NULL DEFAULT 'el',
	`order` MEDIUMINT(11) NOT NULL DEFAULT 0,
	`visible` TINYINT(4)) $charset_spec");

#
# table `agenda`
#

Database::get()->query("CREATE TABLE `agenda` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`course_id` INT(11) NOT NULL,
	`title` VARCHAR(200) NOT NULL,
	`content` TEXT NOT NULL,
	`start` DATETIME NOT NULL DEFAULT '0000-00-00',
	`duration` VARCHAR(20) NOT NULL,
	`visible` TINYINT(4)) $charset_spec");

#
# table `course`
#

Database::get()->query("CREATE TABLE `course` (
  `id` INT(11) NOT NULL auto_increment,
  `code` VARCHAR(20) NOT NULL,
  `lang` VARCHAR(16) NOT NULL DEFAULT 'el',
  `title` VARCHAR(250) NOT NULL DEFAULT '',
  `keywords` TEXT NOT NULL,
  `course_license` TINYINT(4) NOT NULL DEFAULT 0,
  `visible` TINYINT(4) NOT NULL,
  `prof_names` VARCHAR(200) NOT NULL DEFAULT '',
  `public_code` VARCHAR(20) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL default '0000-00-00 00:00:00',
  `doc_quota` FLOAT NOT NULL default '104857600',
  `video_quota` FLOAT NOT NULL default '104857600',
  `group_quota` FLOAT NOT NULL default '104857600',
  `dropbox_quota` FLOAT NOT NULL default '104857600',
  `password` VARCHAR(50) NOT NULL DEFAULT '',
  `glossary_expand` BOOL NOT NULL DEFAULT 0,
  `glossary_index` BOOL NOT NULL DEFAULT 1,
  PRIMARY KEY  (`id`)) $charset_spec");


#
# Table `course_user`
#

Database::get()->query("CREATE TABLE course_user (
      `course_id` INT(11) NOT NULL DEFAULT 0,
      `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
      `status` TINYINT(4) NOT NULL DEFAULT 0,
      `tutor` INT(11) NOT NULL DEFAULT 0,
      `editor` INT(11) NOT NULL DEFAULT 0,
      `reviewer` INT(11) NOT NULL DEFAULT 0,
      `reg_date` DATE NOT NULL,
      `receive_mail` BOOL NOT NULL DEFAULT 1,
      PRIMARY KEY (course_id, user_id)) $charset_spec");

//
// table `course_description_type`
//

Database::get()->query("CREATE TABLE `course_description_type` (
    `id` smallint(6) NOT NULL AUTO_INCREMENT,
    `title` mediumtext,
    `syllabus` tinyint(1) DEFAULT 0,
    `objectives` tinyint(1) DEFAULT 0,
    `literature` tinyint(1) DEFAULT 0,
    `teaching_method` tinyint(1) DEFAULT 0,
    `assessment_method` tinyint(1) DEFAULT 0,
    `prerequisites` tinyint(1) DEFAULT 0,
    `active` tinyint(1) DEFAULT 1,
    `order` int(11) NOT NULL,
    PRIMARY KEY (`id`)) $charset_spec");

Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `syllabus`, `order`) VALUES (1, 'a:2:{s:2:\"el\";s:52:\"Περιεχόμενο μαθήματος (Syllabus)\";s:2:\"en\";s:25:\"Course Content (Syllabus)\";}', 1, 1)");
Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `objectives`, `order`) VALUES (2, 'a:2:{s:2:\"el\";s:41:\"Αντικειμενικοί Στόχοι\";s:2:\"en\";s:25:\"Objectives / Overall Aims\";}', 1, 2)");
Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `literature`, `order`) VALUES (3, 'a:2:{s:2:\"el\";s:47:\"Συνιστώμενη Βιβλιογραφία\";s:2:\"en\";s:30:\"Study Materials / Reading List\";}', 1, 3)");
Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `teaching_method`, `order`) VALUES (4, 'a:2:{s:2:\"el\";s:63:\"Διδακτικές και μαθησιακές μέθοδοι\";s:2:\"en\";s:30:\"Education and Teaching Methods\";}', 1, 4)");
Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `assessment_method`, `order`) VALUES (5, 'a:2:{s:2:\"el\";s:62:\"Μέθοδοι αξιολόγησης/βαθμολόγησης\";s:2:\"en\";s:26:\"Assessment Methods / Exams\";}', 1, 5)");
Database::get()->query("INSERT INTO `course_description_type` (`id`, `title`, `prerequisites`, `order`) VALUES (6, 'a:2:{s:2:\"el\";s:26:\"Προαπαιτήσεις\";s:2:\"en\";s:25:\"Recommended Prerequisites\";}', 1, 6)");

//
// table `course_description`
//

Database::get()->query("CREATE TABLE IF NOT EXISTS `course_description` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `comments` mediumtext,
    `type` smallint(6),
    `visible` tinyint(4) DEFAULT 0,
    `order` int(11) NOT NULL,
    `update_dt` datetime NOT NULL,
    PRIMARY KEY (`id`)) $charset_spec");


//
// Table `course_review`
//

Database::get()->query("CREATE TABLE course_review (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `is_certified` BOOL NOT NULL DEFAULT 0,
    `level` TINYINT(4) NOT NULL DEFAULT 0,
    `last_review` DATETIME NOT NULL,
    `last_reviewer` INT(11) NOT NULL,
    PRIMARY KEY (id)) $charset_spec");


#
# Table `user`
#

Database::get()->query("CREATE TABLE user (
      id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      surname VARCHAR(60) NOT NULL DEFAULT '',
      givenname VARCHAR(60) NOT NULL DEFAULT '',
      username VARCHAR(50) NOT NULL UNIQUE KEY COLLATE utf8_bin,
      password VARCHAR(60) NOT NULL DEFAULT 'empty',
      email VARCHAR(100) NOT NULL DEFAULT '',
      status TINYINT(4) NOT NULL DEFAULT ".USER_STUDENT.",
      phone VARCHAR(20) NOT NULL DEFAULT '',
      am VARCHAR(20) NOT NULL DEFAULT '',
      registered_at DATETIME NOT NULL DEFAULT '0000-00-00',
      expires_at DATETIME NOT NULL DEFAULT '0000-00-00',
      lang VARCHAR(16) NOT NULL DEFAULT 'el',      
      description TEXT NOT NULL,
      has_icon TINYINT(1) NOT NULL DEFAULT 0,
      verified_mail TINYINT(1) NOT NULL DEFAULT ".EMAIL_UNVERIFIED.",
      receive_mail TINYINT(1) NOT NULL DEFAULT 1,
      email_public TINYINT(1) NOT NULL DEFAULT 0,
      phone_public TINYINT(1) NOT NULL DEFAULT 0,
      am_public TINYINT(1) NOT NULL DEFAULT 0,
      whitelist TEXT NOT NULL,
      last_passreminder DATETIME DEFAULT NULL) $charset_spec");

Database::get()->query("CREATE TABLE admin (
      user_id INT(11) NOT NULL PRIMARY KEY,
      privilege INT(11) NOT NULL DEFAULT 0) $charset_spec");

Database::get()->query("CREATE TABLE login_failure (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip varchar(45) NOT NULL,
    count tinyint(4) unsigned NOT NULL default 0,
    last_fail datetime NOT NULL,
    UNIQUE KEY ip (ip)) $charset_spec");

Database::get()->query("CREATE TABLE loginout (
      idLog mediumint(9) unsigned NOT NULL auto_increment,
      id_user mediumint(9) unsigned NOT NULL default 0,
      ip char(45) NOT NULL default '0.0.0.0',
      loginout.when datetime NOT NULL default '0000-00-00 00:00:00',
      loginout.action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
      PRIMARY KEY (idLog), KEY `id_user` (`id_user`)) $charset_spec");

// haniotak:
// table for loginout rollups
// only contains LOGIN events summed up by a period (typically weekly)
Database::get()->query("CREATE TABLE loginout_summary (
        id mediumint unsigned NOT NULL auto_increment,
        login_sum int(11) unsigned  NOT NULL default 0,
        start_date datetime NOT NULL default '0000-00-00 00:00:00',
        end_date datetime NOT NULL default '0000-00-00 00:00:00',
        PRIMARY KEY (id)) $charset_spec");

//table keeping data for monthly reports
Database::get()->query("CREATE TABLE monthly_summary (
        id mediumint unsigned NOT NULL auto_increment,
        `month` varchar(20)  NOT NULL default 0,
        profesNum int(11) NOT NULL default 0,
        studNum int(11) NOT NULL default 0,
        visitorsNum int(11) NOT NULL default 0,
        coursNum int(11) NOT NULL default 0,
        logins int(11) NOT NULL default 0,
        details text,
        PRIMARY KEY (id)) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `document` (
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
                `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `date_modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `subject` TEXT,
                `description` TEXT,
                `author` VARCHAR(255) NOT NULL DEFAULT '',
                `format` VARCHAR(32) NOT NULL DEFAULT '',
                `language` VARCHAR(16) NOT NULL DEFAULT 'el',
                `copyrighted` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `group_properties` (
                `course_id` INT(11) NOT NULL PRIMARY KEY ,
                `self_registration` TINYINT(4) NOT NULL DEFAULT 1,
                `multiple_registration` TINYINT(4) NOT NULL DEFAULT 0,
                `allow_unregister` TINYINT(4) NOT NULL DEFAULT 0,
                `forum` TINYINT(4) NOT NULL DEFAULT 1,
                `private_forum` TINYINT(4) NOT NULL DEFAULT 0,
                `documents` TINYINT(4) NOT NULL DEFAULT 1,
                `wiki` TINYINT(4) NOT NULL DEFAULT 0,
                `agenda` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `group` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL DEFAULT 0,
                `name` varchar(100) NOT NULL DEFAULT '',
                `description` TEXT,
                `forum_id` int(11) NULL,
                `max_members` int(11) NOT NULL DEFAULT 0,
                `secret_directory` varchar(30) NOT NULL DEFAULT 0) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `group_members` (
                `group_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `is_tutor` int(11) NOT NULL DEFAULT 0,
                `description` TEXT,
                PRIMARY KEY (`group_id`, `user_id`)) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `glossary` (
               `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
               `term` VARCHAR(255) NOT NULL,
               `definition` text NOT NULL,
	       `url` text,
               `order` INT(11) NOT NULL DEFAULT 0,
               `datestamp` DATETIME NOT NULL,
               `course_id` INT(11) NOT NULL,
               `category_id` INT(11) DEFAULT NULL,
               `notes` TEXT NOT NULL) $charset_spec");

 Database::get()->query("CREATE TABLE IF NOT EXISTS `glossary_category` (
               `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
               `course_id` INT(11) NOT NULL,
               `name` VARCHAR(255) NOT NULL,
               `description` TEXT NOT NULL,
               `order` INT(11) NOT NULL DEFAULT 0) $charset_spec");

 Database::get()->query("CREATE TABLE IF NOT EXISTS `attendance` (
               `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
               `course_id` INT(11) NOT NULL,
               `limit` TINYINT(4) NOT NULL DEFAULT 0,
               `students_semester` TINYINT(4) NOT NULL DEFAULT 1) $charset_spec");
 Database::get()->query("CREATE TABLE IF NOT EXISTS `attendance_activities` (
               `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
               `attendance_id` MEDIUMINT(11) NOT NULL,
               `title` VARCHAR(250) DEFAULT NULL,
               `date` DATETIME DEFAULT NULL,
               `description` TEXT NOT NULL,
               `module_auto_id` MEDIUMINT(11) NOT NULL DEFAULT 0,
               `module_auto_type` TINYINT(4) NOT NULL DEFAULT 0,
               `auto` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");
 Database::get()->query("CREATE TABLE IF NOT EXISTS `attendance_book` (
               `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
               `attendance_activity_id` MEDIUMINT(11) NOT NULL,
               `uid` int(11) NOT NULL DEFAULT 0,
               `attend` TINYINT(4) NOT NULL DEFAULT 0,
               `comments` TEXT NOT NULL) $charset_spec");
  
Database::get()->query("CREATE TABLE IF NOT EXISTS `link` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) NOT NULL,
                `url` VARCHAR(255),
                `title` VARCHAR(255),
                `description` TEXT NOT NULL,
                `category` INT(6) DEFAULT 0 NOT NULL,
                `order` INT(6) DEFAULT 0 NOT NULL,
                `hits` INT(6) DEFAULT 0 NOT NULL,
                PRIMARY KEY (`id`, `course_id`)) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `link_category` (
                `id` INT(6) NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT,
                `order` INT(6) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`, `course_id`)) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS ebook (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `order` INT(11) NOT NULL,
                `title` TEXT,
                `visible` BOOL NOT NULL DEFAULT 0) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS ebook_section (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `ebook_id` INT(11) NOT NULL,
                `public_id` VARCHAR(11) NOT NULL,
		`file` VARCHAR(128),
                `title` TEXT) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS ebook_subsection (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `section_id` VARCHAR(11) NOT NULL,
                `public_id` VARCHAR(11) NOT NULL,
                `file_id` INT(11) NOT NULL,
                `title` TEXT) $charset_spec");


Database::get()->query("CREATE TABLE IF NOT EXISTS `forum` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) DEFAULT '' NOT NULL,
  `desc` MEDIUMTEXT NOT NULL,
  `num_topics` INT(10) DEFAULT 0 NOT NULL,
  `num_posts` INT(10) DEFAULT 0 NOT NULL,
  `last_post_id` INT(10) DEFAULT 0 NOT NULL,
  `cat_id` INT(10) DEFAULT 0 NOT NULL,
  `course_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_category` (
  `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cat_title` VARCHAR(100) DEFAULT '' NOT NULL,
  `cat_order` INT(11) DEFAULT 0 NOT NULL,
  `course_id` INT(11) NOT NULL,
  KEY `forum_category_index` (`id`, `course_id`)) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_notify` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(11) DEFAULT 0 NOT NULL,
  `cat_id` INT(11) DEFAULT 0 NOT NULL ,
  `forum_id` INT(11) DEFAULT 0 NOT NULL,
  `topic_id` INT(11) DEFAULT 0 NOT NULL ,
  `notify_sent` BOOL DEFAULT 0 NOT NULL ,
  `course_id` INT(11) DEFAULT 0 NOT NULL) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_post` (
  `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `topic_id` INT(10) NOT NULL DEFAULT 0,
  `post_text` MEDIUMTEXT NOT NULL,
  `poster_id` INT(10) NOT NULL DEFAULT 0,
  `post_time` DATETIME,
  `poster_ip` VARCHAR(45) DEFAULT '' NOT NULL,
  `parent_post_id` INT(10) NOT NULL DEFAULT 0) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `forum_topic` (
  `id` INT(10) NOT NULL auto_increment,
  `title` VARCHAR(100) DEFAULT NULL,
  `poster_id` INT(10) DEFAULT NULL,
  `topic_time` DATETIME,
  `num_views` INT(10) NOT NULL DEFAULT 0,
  `num_replies` INT(10) NOT NULL DEFAULT 0,
  `last_post_id` INT(10) NOT NULL DEFAULT 0,
  `forum_id` INT(10) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)) $charset_spec");


Database::get()->query("CREATE TABLE IF NOT EXISTS video (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `path` VARCHAR(255) NOT NULL,
                `url` VARCHAR(200) NOT NULL,
                `title` VARCHAR(200) NOT NULL,
                `description` TEXT NOT NULL,
                `creator` VARCHAR(200) NOT NULL,
                `publisher` VARCHAR(200) NOT NULL,
                `date` DATETIME NOT NULL,
                `visible` TINYINT(4) NOT NULL DEFAULT 1,
                `public` TINYINT(4) NOT NULL DEFAULT 1) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS videolink (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `url` VARCHAR(200) NOT NULL DEFAULT '',
                `title` VARCHAR(200) NOT NULL DEFAULT '',
                `description` TEXT NOT NULL,
                `creator` VARCHAR(200) NOT NULL DEFAULT '',
                `publisher` VARCHAR(200) NOT NULL DEFAULT '',
                `date` DATETIME NOT NULL,
                `visible` TINYINT(4) NOT NULL DEFAULT 1, 
                `public` TINYINT(4) NOT NULL DEFAULT 1) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS dropbox_msg (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `author_id` INT(11) UNSIGNED NOT NULL,
                `subject` VARCHAR(250) NOT NULL,
                `body` LONGTEXT NOT NULL,                
                `timestamp` INT(11) NOT NULL) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS dropbox_attachment (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `msg_id` INT(11) UNSIGNED NOT NULL,
                `filename` VARCHAR(250) NOT NULL,
                 real_filename varchar(255) NOT NULL,
                `filesize` INT(11) UNSIGNED NOT NULL,
                KEY `msg` (`msg_id`)) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS dropbox_index (
                `msg_id` INT(11) UNSIGNED NOT NULL,
                `recipient_id` INT(11) UNSIGNED NOT NULL,
                `thread_id` INT(11) UNSIGNED NOT NULL,
                `is_read` BOOLEAN NOT NULL DEFAULT 0,
                `deleted` BOOLEAN NOT NULL DEFAULT 0,
                PRIMARY KEY (`msg_id`, `recipient_id`),
                KEY `list` (`recipient_id`,`is_read`),
                KEY `participants` (`thread_id`,`recipient_id`)) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_module` (
                `module_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                `comment` TEXT NOT NULL,
                `accessibility` enum('PRIVATE','PUBLIC') NOT NULL DEFAULT 'PRIVATE',
                `startAsset_id` INT(11) NOT NULL DEFAULT 0,
                `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK','MEDIA','MEDIALINK') NOT NULL,
                `launch_data` TEXT NOT NULL)  $charset_spec");
                //COMMENT='List of available modules used in learning paths';
Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_learnPath` (
                `learnPath_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                `comment` TEXT NOT NULL,
                `lock` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
                `visible` TINYINT(4) NOT NULL DEFAULT 0,
                `rank` INT(11) NOT NULL DEFAULT 0)  $charset_spec");
                //COMMENT='List of learning Paths';
Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_rel_learnPath_module` (
                `learnPath_module_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `learnPath_id` INT(11) NOT NULL DEFAULT 0,
                `module_id` INT(11) NOT NULL DEFAULT 0,
                `lock` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
                `visible` TINYINT(4) NOT NULL DEFAULT 0,
                `specificComment` TEXT NOT NULL,
                `rank` INT(11) NOT NULL DEFAULT 0,
                `parent` INT(11) NOT NULL DEFAULT 0,
                `raw_to_pass` TINYINT(4) NOT NULL DEFAULT 50)  $charset_spec");
                //COMMENT='This table links module to the learning path using them';
Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_asset` (
                `asset_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `module_id` INT(11) NOT NULL DEFAULT 0,
                `path` VARCHAR(255) NOT NULL DEFAULT '',
                `comment` VARCHAR(255) default NULL)  $charset_spec");
                //COMMENT='List of resources of module of learning paths';
Database::get()->query("CREATE TABLE IF NOT EXISTS `lp_user_module_progress` (
                `user_module_progress_id` INT(22) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `learnPath_module_id` INT(11) NOT NULL DEFAULT 0,
                `learnPath_id` INT(11) NOT NULL DEFAULT 0,
                `lesson_location` VARCHAR(255) NOT NULL DEFAULT '',
                `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
                `entry` enum('AB-INITIO','RESUME','') NOT NULL DEFAULT 'AB-INITIO',
                `raw` TINYINT(4) NOT NULL DEFAULT '-1',
                `scoreMin` TINYINT(4) NOT NULL DEFAULT '-1',
                `scoreMax` TINYINT(4) NOT NULL DEFAULT '-1',
                `total_time` VARCHAR(13) NOT NULL DEFAULT '0000:00:00.00',
                `session_time` VARCHAR(13) NOT NULL DEFAULT '0000:00:00.00',
                `suspend_data` TEXT NOT NULL,
                `credit` enum('CREDIT','NO-CREDIT') NOT NULL DEFAULT 'NO-CREDIT')  $charset_spec");
                //COMMENT='Record the last known status of the user in the course';

Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_properties` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `title` VARCHAR(255) NOT NULL DEFAULT '',
                `description` TEXT NULL,
                `group_id` INT(11) NOT NULL DEFAULT 0 )  $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_acls` (
                `wiki_id` INT(11) UNSIGNED NOT NULL,
                `flag` VARCHAR(255) NOT NULL,
                `value` ENUM('false','true') NOT NULL DEFAULT 'false',
                PRIMARY KEY (wiki_id, flag) )
                $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_pages` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `wiki_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `owner_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `title` VARCHAR(255) NOT NULL DEFAULT '',
                `ctime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `last_version` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `last_mtime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' )  $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_pages_content` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `editor_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `mtime` DATETIME NOT NULL default '0000-00-00 00:00:00',
                `content` TEXT NOT NULL,
                `changelog` VARCHAR(200) )  $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `wiki_locks` (
                `ptitle` VARCHAR(255) NOT NULL DEFAULT '',
                `wiki_id` INT(11) UNSIGNED NOT NULL,
                `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `ltime_created` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
                `ltime_alive` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (ptitle, wiki_id) ) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `poll` (
                `pid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `creator_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                `creation_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `start_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `end_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `active` INT(11) NOT NULL DEFAULT 0,
                `anonymized` INT(1) NOT NULL DEFAULT 0) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_answer_record` (
                `arid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `pid` INT(11) NOT NULL DEFAULT 0,
                `qid` INT(11) NOT NULL DEFAULT 0,
                `aid` INT(11) NOT NULL DEFAULT 0,
                `answer_text` TEXT NOT NULL,
                `user_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `submit_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_question` (
                `pqid` BIGINT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `pid` INT(11) NOT NULL DEFAULT 0,
                `question_text` VARCHAR(250) NOT NULL DEFAULT '',
                `qtype` ENUM('multiple', 'fill') NOT NULL ) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `poll_question_answer` (
                `pqaid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `pqid` INT(11) NOT NULL DEFAULT 0,
                `answer_text` TEXT NOT NULL ) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `assignment` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `title` VARCHAR(200) NOT NULL DEFAULT '',
                `description` TEXT NOT NULL,
                `comments` TEXT NOT NULL,
                `deadline` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `submission_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `active` CHAR(1) NOT NULL DEFAULT 1,
                `secret_directory` VARCHAR(30) NOT NULL,
                `group_submissions` CHAR(1) DEFAULT 0 NOT NULL,
                `max_grade` FLOAT DEFAULT NULL,                
                `assign_to_specific` CHAR(1) NOT NULL,
                `file_path` VARCHAR(200) DEFAULT '' NOT NULL,
                `file_name` VARCHAR(200) DEFAULT '' NOT NULL) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `assignment_submit` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `assignment_id` INT(11) NOT NULL DEFAULT 0,
                `submission_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
                `file_path` VARCHAR(200) NOT NULL DEFAULT '',
                `file_name` VARCHAR(200) NOT NULL DEFAULT '',
                `comments` TEXT NOT NULL,
                `grade` FLOAT DEFAULT NULL,
                `grade_comments` TEXT NOT NULL,
                `grade_submission_date` DATE NOT NULL DEFAULT '1000-10-10',
                `grade_submission_ip` VARCHAR(45) NOT NULL DEFAULT '',
                `group_id` INT( 11 ) DEFAULT NULL ) $charset_spec");


Database::get()->query("CREATE TABLE IF NOT EXISTS `assignment_to_specific` (
                `user_id` int(11) NOT NULL,
                `group_id` int(11) NOT NULL,
                `assignment_id` int(11) NOT NULL,
                PRIMARY KEY (user_id, group_id, assignment_id)
              ) $charset_spec");        
        
Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise` (
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
                `score` TINYINT(1) NOT NULL DEFAULT 1) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_user_record` (
                `eurid` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `eid` INT(11) NOT NULL DEFAULT 0,
                `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                `record_start_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `record_end_date` DATETIME DEFAULT NULL,
                `total_score` INT(11) NOT NULL DEFAULT 0,
                `total_weighting` INT(11) DEFAULT 0,
                `attempt` INT(11) NOT NULL DEFAULT 0,
                `attempt_status` tinyint(4) NOT NULL DEFAULT 1,
                `secs_remaining` INT(11) NOT NULL DEFAULT '0') $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_answer_record` (
 				`answer_record_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`eurid` int(11) NOT NULL,
				`question_id` int(11) NOT NULL,
				`answer` text,
  				`answer_id` int(11) NOT NULL,
  				`weight` float(5,2) DEFAULT NULL,
                                `is_answered` TINYINT NOT NULL DEFAULT '1') $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_question` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `question` TEXT,
                `description` TEXT,
                `weight` FLOAT(11,2) DEFAULT NULL,
                `q_position` INT(11) DEFAULT 1,
                `type` INT(11) DEFAULT 1) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_answer` (
                `id` INT(11) NOT NULL DEFAULT 0,
                `question_id` INT(11) NOT NULL DEFAULT 0,
                `answer` TEXT,
                `correct` INT(11) DEFAULT NULL,
                `comment` TEXT,
                `weight` FLOAT(5,2),
                `r_position` INT(11) DEFAULT NULL,
                PRIMARY KEY (id, question_id) ) $charset_spec");
Database::get()->query("CREATE TABLE IF NOT EXISTS `exercise_with_questions` (
                `question_id` INT(11) NOT NULL DEFAULT 0,
                `exercise_id` INT(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (question_id, exercise_id) ) $charset_spec");

// hierarchy tables
Database::get()->query("CREATE TABLE IF NOT EXISTS `hierarchy` (
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
                KEY `rgtindex` (`rgt`) )");

Database::get()->query("INSERT INTO `hierarchy` (code, name, lft, rgt)
    VALUES ('', ".quote($institutionForm).", 1, 68)");

Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA', 'Τμήμα 1', '10', '100', '2', '23', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMAPRE', 'Προπτυχιακό Πρόγραμμα Σπουδών', '10', '100', '3', '20', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA1', '1ο εξάμηνο', '10', '100', '4', '5', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA2', '2ο εξάμηνο', '10', '100', '6', '7', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA3', '3ο εξάμηνο', '10', '100', '8', '9', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA4', '4ο εξάμηνο', '10', '100', '10', '11', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA5', '5ο εξάμηνο', '10', '100', '12', '13', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA6', '6ο εξάμηνο', '10', '100', '14', '15', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA7', '7ο εξάμηνο', '10', '100', '16', '17', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMA8', '8ο εξάμηνο', '10', '100', '18', '19', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMAPOST', 'Μεταπτυχιακό Πρόγραμμα Σπουδών', '10', '100', '21', '22', true, true)");


Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB', 'Τμήμα 2', '20', '100', '24', '45', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMBPRE', 'Προπτυχιακό Πρόγραμμα Σπουδών', '20', '100', '25', '42', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB1', '1ο εξάμηνο', '20', '100', '26', '27', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB2', '2ο εξάμηνο', '20', '100', '28', '29', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB3', '3ο εξάμηνο', '20', '100', '30', '31', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB4', '4ο εξάμηνο', '20', '100', '32', '33', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB5', '5ο εξάμηνο', '20', '100', '34', '35', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB6', '6ο εξάμηνο', '20', '100', '36', '37', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB7', '7ο εξάμηνο', '20', '100', '38', '39', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMB8', '8ο εξάμηνο', '20', '100', '40', '41', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMBPOST', 'Μεταπτυχιακό Πρόγραμμα Σπουδών', '20', '100', '43', '44', true, true)");

Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC', 'Τμήμα 3', '30', '100', '46', '67', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMCPRE', 'Προπτυχιακό Πρόγραμμα Σπουδών', '30', '100', '47', '64', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC1', '1ο εξάμηνο', '30', '100', '48', '49', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC2', '2ο εξάμηνο', '30', '100', '50', '51', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC3', '3ο εξάμηνο', '30', '100', '52', '53', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC4', '4ο εξάμηνο', '30', '100', '54', '55', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC5', '5ο εξάμηνο', '30', '100', '56', '57', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC6', '6ο εξάμηνο', '30', '100', '58', '59', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC7', '7ο εξάμηνο', '30', '100', '60', '61', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMC8', '8ο εξάμηνο', '30', '100', '62', '63', true, true)");
Database::get()->query("INSERT INTO `hierarchy` (code, name, number, generator, lft, rgt, allow_course, allow_user)
    VALUES ('TMCPOST', 'Μεταπτυχιακό Πρόγραμμα Σπουδών', '30', '100', '65', '66', true, true)");

Database::get()->query("CREATE TABLE IF NOT EXISTS `course_department` (
                `id` int(11) NOT NULL auto_increment PRIMARY KEY,
                `course` int(11) NOT NULL references course(id),
                `department` int(11) NOT NULL references hierarchy(id) )");

Database::get()->query("CREATE TABLE IF NOT EXISTS `user_department` (
                `id` int(11) NOT NULL auto_increment PRIMARY KEY,
                `user` mediumint(8) unsigned NOT NULL references user(user_id),
                `department` int(11) NOT NULL references hierarchy(id) )");

// hierarchy stored procedures
    Database::get()->query("DROP VIEW IF EXISTS `hierarchy_depth`");
    Database::get()->query("CREATE VIEW `hierarchy_depth` AS
                    SELECT node.id, node.code, node.name, node.number, node.generator,
                           node.lft, node.rgt, node.allow_course, node.allow_user, node.order_priority,
                           COUNT(parent.id) - 1 AS depth
                    FROM hierarchy AS node,
                         hierarchy AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                    GROUP BY node.id
                    ORDER BY node.lft");

    Database::get()->query("DROP PROCEDURE IF EXISTS `add_node`");
    Database::get()->query("CREATE PROCEDURE `add_node` (IN name VARCHAR(255), IN parentlft INT(11),
                        IN p_code VARCHAR(10), IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN, IN p_order_priority INT(11))
                    LANGUAGE SQL
                    BEGIN
                        DECLARE lft, rgt INT(11);

                        SET lft = parentlft + 1;
                        SET rgt = parentlft + 2;

                        CALL shift_right(parentlft, 2, 0);

                        INSERT INTO `hierarchy` (name, lft, rgt, code, allow_course, allow_user, order_priority) VALUES (name, lft, rgt, p_code, p_allow_course, p_allow_user, p_order_priority);
                    END");

    Database::get()->query("DROP PROCEDURE IF EXISTS `add_node_ext`");
    Database::get()->query("CREATE PROCEDURE `add_node_ext` (IN name VARCHAR(255), IN parentlft INT(11),
                        IN p_code VARCHAR(10), IN p_number INT(11), IN p_generator INT(11),
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
    Database::get()->query("CREATE PROCEDURE `update_node` (IN p_id INT(11), IN p_name VARCHAR(255),
                        IN nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11), IN parentlft INT(11),
                        IN p_code VARCHAR(10), IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN, IN p_order_priority INT(11))
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

// encrypt the admin password into DB
$hasher = new PasswordHash(8, false);
$password_encrypted = $hasher->HashPassword($passForm);
Database::get()->query("INSERT INTO `user` (`givenname`, `surname`, `username`, `password`, `email`, `status`, `registered_at`,`expires_at`, `verified_mail`, `whitelist`, `description`)
                 VALUES (" . quote($nameForm) . ", '', " .
                             quote($loginForm) . ", '$password_encrypted', " .
                             quote($emailForm) . ", 1, " . DBHelper::timeAfter() .", ". DBHelper::timeAfter(5*365*24*60*60).", 1, '*,,', 'Administrator')");
$admin_uid = mysql_insert_id();
Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                 VALUES ($admin_uid, '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");
Database::get()->query("INSERT INTO admin VALUES ($admin_uid, 0)");

#
# Table structure for table `user_request`
#

Database::get()->query("CREATE TABLE user_request (
                id INT(11) NOT NULL AUTO_INCREMENT,
                givenname VARCHAR(60) NOT NULL DEFAULT '',
                surname VARCHAR(60) NOT NULL DEFAULT '',
                username VARCHAR(50) NOT NULL DEFAULT '',
                password VARCHAR(255) NOT NULL DEFAULT '',
                email VARCHAR(100) NOT NULL DEFAULT '',
                verified_mail TINYINT(1) NOT NULL DEFAULT ".EMAIL_UNVERIFIED.",
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


// New table auth for authentication methods
// added by kstratos
Database::get()->query("CREATE TABLE `auth` (
                  `auth_id` int(2) NOT NULL auto_increment,
                  `auth_name` varchar(20) NOT NULL default '',
                  `auth_settings` text ,
                  `auth_instructions` text ,
                  `auth_default` tinyint(1) NOT NULL default 0,
                  PRIMARY KEY (`auth_id`))
                  $charset_spec");

Database::get()->query("INSERT INTO `auth` VALUES
                (1, 'eclass', '', '', 1),
                (2, 'pop3', '', '', 0),
                (3, 'imap', '', '', 0),
                (4, 'ldap', '', '', 0),
                (5, 'db', '', '', 0),
                (6, 'shibboleth', '', '', 0),
                (7, 'cas', '', '', 0)");

$dont_display_login_form = intval($dont_display_login_form);
$email_required = intval($email_required);
$email_verification_required = intval($email_verification_required);
$dont_mail_unverified_mails = intval($dont_mail_unverified_mails);
$email_from = intval($email_from);
$am_required = intval($am_required);
$dropbox_allow_student_to_student = intval($dropbox_allow_student_to_student);
$block_username_change = intval($block_username_change);
$display_captcha = intval($display_captcha);
$insert_xml_metadata = intval($insert_xml_metadata);
$betacms = intval($betacms);
$enable_mobileapi = intval($enable_mobileapi);
$eclass_stud_reg = intval($eclass_stud_reg);
$eclass_prof_reg = intval($eclass_prof_reg);
$course_multidep = intval($course_multidep);
$user_multidep = intval($user_multidep);
$restrict_owndep = intval($restrict_owndep);
$restrict_teacher_owndep = intval($restrict_teacher_owndep);
$student_upload_whitelist = quote($student_upload_whitelist);
$teacher_upload_whitelist = quote($teacher_upload_whitelist);

// restrict_owndep and restrict_teacher_owndep are interdependent
if ($restrict_owndep == 0) {
	$restrict_teacher_owndep = 0;
}

Database::get()->query("CREATE TABLE `config`
                (`key` VARCHAR(32) NOT NULL,
                 `value` TEXT NOT NULL,
                 PRIMARY KEY (`key`))");
db_query("INSERT INTO `config` (`key`, `value`) VALUES
                ('base_url', ".quote($urlForm)."),
                ('default_language', '$lang'),
                ('dont_display_login_form', $dont_display_login_form),
                ('email_required', $email_required),
                ('email_from', $email_from),
                ('email_verification_required', $email_verification_required),
                ('dont_mail_unverified_mails', $dont_mail_unverified_mails),
                ('am_required', $am_required),
                ('dropbox_allow_student_to_student', $dropbox_allow_student_to_student),
                ('block_username_change', $block_username_change),
                ('betacms', $betacms),
                ('enable_mobileapi', $enable_mobileapi),
                ('code_key', '" . generate_secret_key(32) . "'),
                ('display_captcha', $display_captcha),
                ('insert_xml_metadata', $insert_xml_metadata),
                ('doc_quota', $doc_quota),
                ('video_quota', $video_quota),
                ('group_quota', $group_quota),
                ('dropbox_quota', $dropbox_quota),
                ('user_registration', 1),
                ('alt_auth_stud_reg', 2),
                ('alt_auth_prof_reg', 2),
                ('eclass_stud_reg', $eclass_stud_reg),
                ('eclass_prof_reg', $eclass_prof_reg),
                ('course_multidep', $course_multidep),
                ('user_multidep', $user_multidep),
                ('restrict_owndep', $restrict_owndep),
                ('restrict_teacher_owndep', $restrict_teacher_owndep),
                ('max_glossary_terms', '250'),                
                ('phpSysInfoURL', ".quote($phpSysInfoURL)."),
                ('email_sender', ".quote($emailForm)."),
                ('admin_name', ".quote($nameForm)."),
                ('email_helpdesk', ".quote($helpdeskmail)."),
                ('site_name', ".quote($campusForm)."),
                ('phone', ".quote($helpdeskForm)."),
                ('fax', ".quote($faxForm)."),
                ('postaddress', ".quote($postaddressForm)."),
                ('institution', ".quote($institutionForm)."),
                ('institution_url', ".quote($institutionUrlForm)."),
                ('account_duration', '126144000'),
                ('language', ".quote($lang)."),
                ('active_ui_languages', ".quote($active_ui_languages)."),
                ('student_upload_whitelist', $student_upload_whitelist),
                ('teacher_upload_whitelist', $teacher_upload_whitelist),
                ('login_fail_check', 1),
                ('login_fail_threshold', 15),
                ('login_fail_deny_interval', 5),
                ('login_fail_forgive_interval', 24),
                ('actions_expire_interval', 12),
                ('log_expire_interval', 5),
                ('log_purge_interval', 12),
                ('course_metadata', 0),
                ('opencourses_enable', 0),
                ('version', '" . ECLASS_VERSION ."')");

// table for cron parameters
Database::get()->query("CREATE TABLE `cron_params` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL UNIQUE,
        `last_run` DATETIME NOT NULL) $charset_spec");

// tables for units module
Database::get()->query("CREATE TABLE `course_units` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`comments` MEDIUMTEXT,
	`visible` TINYINT(4),
        `public` TINYINT(4) NOT NULL DEFAULT 1,
	`order` INT(11) NOT NULL DEFAULT 0,
	`course_id` INT(11) NOT NULL) $charset_spec");

 Database::get()->query("CREATE TABLE `unit_resources` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`unit_id` INT(11) NOT NULL ,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`comments` MEDIUMTEXT,
	`res_id` INT(11) NOT NULL,
	`type` VARCHAR(255) NOT NULL DEFAULT '',
	`visible` TINYINT(4),
	`order` INT(11) NOT NULL DEFAULT 0,
	`date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00') $charset_spec");

Database::get()->query("CREATE TABLE `actions_daily` (
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
        KEY `actionsdailycourseindex` (`course_id`) )");

Database::get()->query("CREATE TABLE IF NOT EXISTS `actions_summary` (
        `id` int(11) NOT NULL auto_increment,
        `module_id` int(11) NOT NULL,
        `visits` int(11) NOT NULL,
        `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `duration` int(11) NOT NULL,
        `course_id` INT(11) NOT NULL,
        PRIMARY KEY (`id`))");

Database::get()->query("CREATE TABLE IF NOT EXISTS `logins` (
        `id` int(11) NOT NULL auto_increment,
        `user_id` int(11) NOT NULL,
        `ip` char(45) NOT NULL default '0.0.0.0',
        `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
        `course_id` INT(11) NOT NULL,
        PRIMARY KEY (`id`))");

// bbb_servers table
Database::get()->query('CREATE TABLE IF NOT EXISTS `bbb_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) DEFAULT NULL,
  `ip` varchar(255) NOT NULL,
  `enabled` enum("true","false") DEFAULT NULL,
  `server_key` varchar(255) DEFAULT NULL,
  `api_url` varchar(255) DEFAULT NULL,
  `max_rooms` int(11) DEFAULT NULL,
  `max_users` int(11) DEFAULT NULL,
  `enable_recordings` enum("yes","no") DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_bbb_servers` (`hostname`))');
    
// bbb_sessions tables
Database::get()->query('CREATE TABLE IF NOT EXISTS `bbb_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `start_date` datetime DEFAULT NULL,
  `public` enum("0","1") DEFAULT NULL,
  `active` enum("0","1") DEFAULT NULL,
  `running_at` int(11) DEFAULT NULL,
  `meeting_id` varchar(255) DEFAULT NULL,
  `mod_pw` varchar(255) DEFAULT NULL,
  `att_pw` varchar(255) DEFAULT NULL,
  `unlock_interval` int(11) DEFAULT NULL,
  `external_users` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
)');

Database::get()->query("CREATE TABLE IF NOT EXISTS `course_settings` (
        `setting_id` INT(11) NOT NULL,
        `course_id` INT(11) NOT NULL,
        `value` INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`setting_id`, `course_id`))");


Database::get()->query("CREATE TABLE IF NOT EXISTS `gradebook` (
        `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT(11) NOT NULL,
        `students_semester` TINYINT(4) NOT NULL DEFAULT 1,
        `range` TINYINT(4) NOT NULL DEFAULT 10) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `gradebook_activities` (
        `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `gradebook_id` MEDIUMINT(11) NOT NULL,
        `title` VARCHAR(250) DEFAULT NULL,
        `activity_type` INT(11) DEFAULT NULL,
        `date` DATETIME DEFAULT NULL,
        `description` TEXT NOT NULL,
        `weight` MEDIUMINT(11) NOT NULL DEFAULT 0,
        `module_auto_id` MEDIUMINT(11) NOT NULL DEFAULT 0,
        `module_auto_type` TINYINT(4) NOT NULL DEFAULT 0,
        `auto` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");

Database::get()->query("CREATE TABLE IF NOT EXISTS `gradebook_book` (
        `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `gradebook_activity_id` MEDIUMINT(11) NOT NULL,
        `uid` int(11) NOT NULL DEFAULT 0,
        `grade` FLOAT NOT NULL DEFAULT -1,
        `comments` TEXT NOT NULL) $charset_spec");

Database::get()->query("CREATE TABLE `oai_record` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL UNIQUE,
    `oai_identifier` varchar(255) DEFAULT NULL,
    `oai_metadataprefix` varchar(255) DEFAULT 'oai_dc',
    `oai_set` varchar(255) DEFAULT 'class:course',
    `datestamp` datetime DEFAULT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT 0,
    `dc_title` text DEFAULT NULL,
    `dc_description` text DEFAULT NULL,
    `dc_syllabus` text DEFAULT NULL,
    `dc_subject` text DEFAULT NULL,
    `dc_objectives` text DEFAULT NULL,
    `dc_level` text DEFAULT NULL,
    `dc_prerequisites` text DEFAULT NULL,
    `dc_instructor` text DEFAULT NULL,
    `dc_department` text DEFAULT NULL,
    `dc_institution` text DEFAULT NULL,
    `dc_coursephoto` text DEFAULT NULL,
    `dc_instructorphoto` text DEFAULT NULL,
    `dc_url` text DEFAULT NULL,
    `dc_identifier` text DEFAULT NULL,
    `dc_language` text DEFAULT NULL,
    `dc_date` datetime DEFAULT NULL,
    `dc_format` text DEFAULT NULL,
    `dc_rights` text DEFAULT NULL,
    `dc_videolectures` text DEFAULT NULL,
    PRIMARY KEY (`id`)) $charset_spec");

// create indexes
Database::get()->query('CREATE INDEX `doc_path_index` ON document (course_id, subsystem, path)');
Database::get()->query('CREATE INDEX `course_units_index` ON course_units (course_id, `order`)');
Database::get()->query('CREATE INDEX `unit_res_index` ON unit_resources (unit_id, visible, res_id)');
Database::get()->query('CREATE INDEX `cid` ON course_description (course_id)');
Database::get()->query('CREATE INDEX `cd_type_index` ON course_description (type)');
Database::get()->query('CREATE INDEX `cd_cid_type_index` ON course_description (course_id, type)');
Database::get()->query('CREATE INDEX `cid` ON oai_record (course_id)');
Database::get()->query("CREATE INDEX `optimize` ON lp_user_module_progress (user_id, learnPath_module_id)");
Database::get()->query('CREATE INDEX `visible_cid` ON course_module (visible, course_id)');        
Database::get()->query('CREATE INDEX `cid` ON video (course_id)');
Database::get()->query('CREATE INDEX `cid` ON videolink (course_id)');
Database::get()->query('CREATE INDEX `cmid` ON log (course_id, module_id)');

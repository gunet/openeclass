<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

require_once '../include/phpass/PasswordHash.php';

if (!defined('ECLASS_VERSION')) {
        exit;
}

db_query("DROP DATABASE IF EXISTS `$mysqlMainDb`");
if (mysql_version()) db_query("SET NAMES utf8");

// set default storage engine
mysql_query("SET storage_engine=MYISAM");

if (mysql_version()) {
        $cdb=db_query("CREATE DATABASE `$mysqlMainDb` CHARACTER SET utf8");

} else {
        $cdb=db_query("CREATE DATABASE `$mysqlMainDb`");
}
mysql_select_db ($mysqlMainDb);

// drop old tables if they exist
db_query("DROP TABLE IF EXISTS admin");
db_query("DROP TABLE IF EXISTS admin_announcements");
db_query("DROP TABLE IF EXISTS agenda");
db_query("DROP TABLE IF EXISTS annonces");
db_query("DROP TABLE IF EXISTS auth");
db_query("DROP TABLE IF EXISTS cours");
db_query("DROP TABLE IF EXISTS cours_user");
db_query("DROP TABLE IF EXISTS faculte");
db_query("DROP TABLE IF EXISTS institution");
db_query("DROP TABLE IF EXISTS loginout");
db_query("DROP TABLE IF EXISTS loginout_summary");
db_query("DROP TABLE IF EXISTS monthly_summary");
db_query("DROP TABLE IF EXISTS user_request");
db_query("DROP TABLE IF EXISTS prof_request");
db_query("DROP TABLE IF EXISTS user");

$charset_spec = 'DEFAULT CHARACTER SET=utf8';

// create tables

#
# table `annonces`
#

db_query("CREATE TABLE annonces (
	`id` MEDIUMINT(11) NOT NULL auto_increment,
	`title` VARCHAR(255) DEFAULT NULL,
	`contenu` TEXT,
	`preview` TEXT NULL DEFAULT NULL,
	`temps` DATE DEFAULT NULL,
	`cours_id` INT(11) NOT NULL default '0',
	`ordre` MEDIUMINT(11) NOT NULL,
	`visibility` CHAR(1) NOT NULL DEFAULT 'v',
	PRIMARY KEY (id)) $charset_spec");

#
# table admin_announcements
#
db_query("CREATE TABLE admin_announcements (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`title` VARCHAR(255) NOT NULL,
	`body` TEXT,
	`date` DATETIME NOT NULL,
	`begin` DATETIME DEFAULT NULL,
	`end` DATETIME DEFAULT NULL,
	`lang` VARCHAR(16) NOT NULL DEFAULT 'el',
	`ordre` MEDIUMINT(11) NOT NULL DEFAULT 0,
	`visible` ENUM('V', 'I') NOT NULL) $charset_spec");

#
# table `agenda`
#

db_query("CREATE TABLE `agenda` (
	`id` int(11) NOT NULL auto_increment,
	`lesson_event_id` int(11) NOT NULL default '0',
	`titre` varchar(200) NOT NULL default '',
	`contenu` text NOT NULL,
	`day` date NOT NULL default '0000-00-00',
	`hour` time NOT NULL default '00:00:00',
	`lasting` varchar(20) NOT NULL default '',
	`lesson_code` varchar(50) NOT NULL default '',
	PRIMARY KEY  (`id`)) $charset_spec");

#
# table `forum_notify`
#
db_query("CREATE TABLE `forum_notify` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`user_id` INT NOT NULL DEFAULT '0',
	`cat_id` INT NULL ,
	`forum_id` INT NULL ,
	`topic_id` INT NULL ,
	`notify_sent` BOOL NOT NULL DEFAULT '0',
	`course_id` INT NOT NULL DEFAULT '0'
	) $charset_spec");

#
# table `cours`
#

db_query("CREATE TABLE `cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(20) NOT NULL,
  `languageCourse` VARCHAR(16) NOT NULL DEFAULT 'el',
  `intitule` varchar(250) NOT NULL DEFAULT '',
  `description` TEXT,
  `course_keywords` TEXT,
  `course_addon` TEXT,
  `course_license` TINYINT(4),
  `visible` tinyint(4) NOT NULL,
  `titulaires` varchar(200) NOT NULL DEFAULT '',
  `fake_code` varchar(20) NOT NULL DEFAULT '',
  `departmentUrlName` varchar(30) default NULL,
  `departmentUrl` varchar(180) default NULL,
  `lastVisit` date NOT NULL default '0000-00-00',
  `lastEdit` datetime NOT NULL default '0000-00-00 00:00:00',
  `expirationDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `first_create` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` ENUM( 'pre', 'post', 'other' ) DEFAULT 'pre' NOT NULL,
  `doc_quota` float NOT NULL default '104857600',
  `video_quota` float NOT NULL default '104857600',
  `group_quota` float NOT NULL default '104857600',
  `dropbox_quota` float NOT NULL default '104857600',
  `password` varchar(50) DEFAULT NULL,
  `faculteid` int(11) NOT NULL DEFAULT 0,
  `expand_glossary` BOOL NOT NULL DEFAULT 0,
  `glossary_index` BOOL NOT NULL DEFAULT 1,
  PRIMARY KEY  (`cours_id`)) $charset_spec");


#
# Table `cours_user`
#

db_query("CREATE TABLE cours_user (
      `cours_id` INT(11) NOT NULL DEFAULT 0,
      `user_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
      `statut` TINYINT(4) NOT NULL DEFAULT 0,
      `team` INT(11) NOT NULL DEFAULT 0,
      `tutor` INT(11) NOT NULL DEFAULT 0,
      `editor` INT(11) NOT NULL DEFAULT 0,
      `reviewer` INT(11) NOT NULL DEFAULT 0,
      `reg_date` DATE NOT NULL,
      `receive_mail` BOOL NOT NULL DEFAULT 1,
      PRIMARY KEY (cours_id, user_id)) $charset_spec");

#
# Table `faculte`
#

db_query("CREATE TABLE faculte (
      id int(11) NOT NULL auto_increment,
      code varchar(10) NOT NULL,
      name varchar(100) NOT NULL,
      number int(11) NOT NULL default 0,
      generator int(11) NOT NULL default 0,
      PRIMARY KEY (id)) $charset_spec");


db_query("INSERT INTO faculte VALUES (1, 'TMA', 'Τμήμα 1', 10, 100)");
db_query("INSERT INTO faculte VALUES (2, 'TMB', 'Τμήμα 2', 20, 100)");
db_query("INSERT INTO faculte VALUES (3, 'TMC', 'Τμήμα 3', 30, 100)");
db_query("INSERT INTO faculte VALUES (4, 'TMD', 'Τμήμα 4', 40, 100)");
db_query("INSERT INTO faculte VALUES (5, 'TME', 'Τμήμα 5', 50, 100)");

#
# Table `user`
#

db_query("CREATE TABLE user (
      user_id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
      nom VARCHAR(60) DEFAULT NULL,
      prenom VARCHAR(60) DEFAULT NULL,
      username VARCHAR(50) DEFAULT 'empty',
      password VARCHAR(60) DEFAULT 'empty',
      email VARCHAR(100) DEFAULT NULL,
      parent_email VARCHAR(100) DEFAULT NULL,
      statut TINYINT(4) DEFAULT NULL,
      phone VARCHAR(20) DEFAULT NULL,
      department INT(10) DEFAULT NULL,
      am VARCHAR(20) DEFAULT NULL,
      registered_at INT(10) NOT NULL default '0',
      expires_at INT(10) NOT NULL default '0',
      perso ENUM('yes','no') NOT NULL default 'yes',
      lang VARCHAR(16) NOT NULL DEFAULT 'el',
      announce_flag date NOT NULL DEFAULT '0000-00-00',
      doc_flag DATE NOT NULL DEFAULT '0000-00-00',
      forum_flag DATE NOT NULL DEFAULT '0000-00-00',
      description TEXT,
      has_icon BOOL NOT NULL DEFAULT 0,
      verified_mail BOOL NOT NULL DEFAULT ".EMAIL_UNVERIFIED.",
      receive_mail BOOL NOT NULL DEFAULT 1,
      email_public TINYINT(1) NOT NULL DEFAULT 0,
      phone_public TINYINT(1) NOT NULL DEFAULT 0,
      am_public TINYINT(1) NOT NULL DEFAULT 0,
      whitelist TEXT,
      last_passreminder DATETIME DEFAULT NULL,
      PRIMARY KEY (user_id),
      KEY `user_username` (`username`)) $charset_spec");

db_query("CREATE TABLE admin (
      idUser mediumint unsigned  NOT NULL default '0',
      `privilege` int(11) NOT NULL default '0',
      UNIQUE KEY idUser (idUser)) $charset_spec");

db_query("CREATE TABLE login_failure (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip varchar(15) NOT NULL,
    count tinyint(4) unsigned NOT NULL default '0',
    last_fail datetime NOT NULL,
    UNIQUE KEY ip (ip)) $charset_spec");

db_query("CREATE TABLE loginout (
      idLog mediumint(9) unsigned NOT NULL auto_increment,
      id_user mediumint(9) unsigned NOT NULL default '0',
      ip char(39) NOT NULL default '0.0.0.0',
      loginout.when datetime NOT NULL default '0000-00-00 00:00:00',
      loginout.action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
      PRIMARY KEY (idLog), KEY `id_user` (`id_user`)) $charset_spec");

// haniotak:
// table for loginout rollups
// only contains LOGIN events summed up by a period (typically weekly)
db_query("CREATE TABLE loginout_summary (
        id mediumint unsigned NOT NULL auto_increment,
        login_sum int(11) unsigned  NOT NULL default '0',
        start_date datetime NOT NULL default '0000-00-00 00:00:00',
        end_date datetime NOT NULL default '0000-00-00 00:00:00',
        PRIMARY KEY (id)) $charset_spec");

//table keeping data for monthly reports
db_query("CREATE TABLE monthly_summary (
        id mediumint unsigned NOT NULL auto_increment,
        `month` varchar(20)  NOT NULL default '0',
        profesNum int(11) NOT NULL default '0',
        studNum int(11) NOT NULL default '0',
        visitorsNum int(11) NOT NULL default '0',
        coursNum int(11) NOT NULL default '0',
        logins int(11) NOT NULL default '0',
        details text,
        PRIMARY KEY (id)) $charset_spec");

db_query("CREATE TABLE `parents_announcements` (
        `id` mediumint(9) NOT NULL auto_increment,
        `title` varchar(255) default NULL,
        `content` text,
        `date` datetime default NULL,
        `sender_id` int(11) NOT NULL,
        `recipient_id` int(11) NOT NULL,
        `course_id` int(11) NOT NULL,
         PRIMARY KEY  (`id`)) $charset_spec");

db_query("CREATE TABLE IF NOT EXISTS `document` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL DEFAULT 0,
		`subsystem` TINYINT(4) NOT NULL,	
                `subsystem_id` INT(11) DEFAULT NULL,
                `path` VARCHAR(255) NOT NULL,
                `extra_path` VARCHAR(255) NOT NULL DEFAULT '',
                `filename` VARCHAR(255) NOT NULL,
                `visibility` CHAR(1) NOT NULL DEFAULT 'v',
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
                `copyrighted` TINYINT(4) NOT NULL DEFAULT 0,
                FULLTEXT KEY `document`
                        (`filename`, `comment`, `title`, `creator`,
                         `subject`, `description`, `author`, `language`)) $charset_spec");

db_query("CREATE TABLE IF NOT EXISTS `group_properties` (
                `course_id` INT(11) NOT NULL PRIMARY KEY ,
                `self_registration` TINYINT(4) NOT NULL DEFAULT 1,
                `multiple_registration` TINYINT(4) NOT NULL DEFAULT 0,
                `allow_unregister` TINYINT(4) NOT NULL DEFAULT 0,
                `forum` TINYINT(4) NOT NULL DEFAULT 1,
                `private_forum` TINYINT(4) NOT NULL DEFAULT 0,
                `documents` TINYINT(4) NOT NULL DEFAULT 1,
                `wiki` TINYINT(4) NOT NULL DEFAULT 0,
                `agenda` TINYINT(4) NOT NULL DEFAULT 0) $charset_spec");
db_query("CREATE TABLE IF NOT EXISTS `group` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL DEFAULT 0,
                `name` varchar(100) NOT NULL DEFAULT '',
                `description` TEXT,
                `forum_id` int(11) NULL,
                `max_members` int(11) NOT NULL DEFAULT 0,
                `secret_directory` varchar(30) NOT NULL DEFAULT '0') $charset_spec");
db_query("CREATE TABLE IF NOT EXISTS `group_members` (
                `group_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `is_tutor` int(11) NOT NULL DEFAULT 0,
                `description` TEXT,
                PRIMARY KEY (`group_id`, `user_id`)) $charset_spec");

db_query("CREATE TABLE IF NOT EXISTS `glossary` (
               `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
               `term` VARCHAR(255) NOT NULL,
               `definition` text NOT NULL,
	       `url` text,
               `order` INT(11) NOT NULL DEFAULT 0,
               `datestamp` DATETIME NOT NULL,
               `course_id` INT(11) NOT NULL,
               `category_id` INT(11) DEFAULT NULL,
               `notes` TEXT NOT NULL) $charset_spec");

 db_query("CREATE TABLE IF NOT EXISTS `glossary_category` (
               `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
               `course_id` INT(11) NOT NULL,
               `name` VARCHAR(255) NOT NULL,
               `description` TEXT NOT NULL,
               `order` INT(11) NOT NULL DEFAULT 0) $charset_spec");

db_query("CREATE TABLE IF NOT EXISTS `link` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) NOT NULL,
                `url` VARCHAR(255),
                `title` VARCHAR(255),
                `description` TEXT,
                `category` INT(6) DEFAULT NULL,
                `order` INT(6) DEFAULT 0 NOT NULL,
                `hits` INT(6) DEFAULT 0 NOT NULL,
                PRIMARY KEY (`id`, `course_id`)) $charset_spec");
db_query("CREATE TABLE IF NOT EXISTS `link_category` (
                `id` INT(6) NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT,
                `order` INT(6) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`, `course_id`)) $charset_spec");
db_query("CREATE TABLE IF NOT EXISTS ebook (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) NOT NULL,
                `order` INT(11) NOT NULL,
                `title` TEXT,
                `visible` BOOL NOT NULL DEFAULT 0) $charset_spec");
db_query("CREATE TABLE IF NOT EXISTS ebook_section (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `ebook_id` INT(11) NOT NULL,
                `public_id` VARCHAR(11) NOT NULL,
		`file` VARCHAR(128),
                `title` TEXT) $charset_spec");
db_query("CREATE TABLE IF NOT EXISTS ebook_subsection (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `section_id` VARCHAR(11) NOT NULL,
                `public_id` VARCHAR(11) NOT NULL,
                `file_id` INT(11) NOT NULL,
                `title` TEXT) $charset_spec");

// encrypt the admin password into DB
$hasher = new PasswordHash(8, false);
$password_encrypted = $hasher->HashPassword($passForm);
$exp_time = time() + 140000000;
db_query("INSERT INTO `user` (`prenom`, `nom`, `username`, `password`, `email`, `statut`,`registered_at`,`expires_at`, `verified_mail`, `whitelist`)
	VALUES (".quote($nameForm).", ".quote($surnameForm).", ".quote($loginForm).",".quote($password_encrypted).",".quote($emailForm).",'1',".time().", $exp_time, 1, '*,,')");
$idOfAdmin = mysql_insert_id();
db_query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
	 VALUES ($idOfAdmin, '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");


#add admin in list of admin
db_query("INSERT INTO admin VALUES ('".$idOfAdmin."', 0)");

#
# Table structure for table `user_request`
#

db_query("CREATE TABLE user_request (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL DEFAULT '',
                surname varchar(255) NOT NULL DEFAULT '',
                uname varchar(255) NOT NULL DEFAULT '',
                password varchar(255) NOT NULL DEFAULT '',
                email varchar(255) NOT NULL DEFAULT '',
                verified_mail tinyint(1) NOT NULL DEFAULT ".EMAIL_UNVERIFIED.",
                faculty_id INT(11) NOT NULL DEFAULT 0,
                phone varchar(20) NOT NULL DEFAULT '',
		am varchar(20) NOT NULL DEFAULT '',
                status int(11) default NULL,
                date_open datetime default NULL,
                date_closed datetime default NULL,
                comment text,
                lang varchar(16) NOT NULL DEFAULT 'el',
                statut tinyint(4) NOT NULL DEFAULT 1,
                ip_address INT(11) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (id)) $charset_spec");


###############PHPMyAdminTables##################

db_query("CREATE TABLE `pma_bookmark` (
                id int(11) NOT NULL auto_increment,
                dbase varchar(255) NOT NULL,
                user varchar(255) NOT NULL,
                label varchar(255) NOT NULL,
                query text NOT NULL,
                PRIMARY KEY (id)) $charset_spec");

db_query("CREATE TABLE `pma_relation` (
               `master_db` varchar(64) NOT NULL default '',
               `master_table` varchar(64) NOT NULL default '',
               `master_field` varchar(64) NOT NULL default '',
               `foreign_db` varchar(64) NOT NULL default '',
               `foreign_table` varchar(64) NOT NULL default '',
               `foreign_field` varchar(64) NOT NULL default '',
               PRIMARY KEY (`master_db`, `master_table`, `master_field`),
               KEY foreign_field (foreign_db, foreign_table))
               $charset_spec");


db_query("CREATE TABLE `pma_table_info` (
               `db_name` varchar(64) NOT NULL default '',
               `table_name` varchar(64) NOT NULL default '',
               `display_field` varchar(64) NOT NULL default '',
               PRIMARY KEY (`db_name`, `table_name`)) $charset_spec");

db_query("CREATE TABLE `pma_table_coords` (
               `db_name` varchar(64) NOT NULL default '',
               `table_name` varchar(64) NOT NULL default '',
               `pdf_page_number` int NOT NULL default '0',
               `x` float unsigned NOT NULL default '0',
               `y` float unsigned NOT NULL default '0',
               PRIMARY KEY (`db_name`, `table_name`, `pdf_page_number`))
               $charset_spec");

db_query("CREATE TABLE `pma_pdf_pages` (
               `db_name` varchar(64) NOT NULL default '',
               `page_nr` int(10) unsigned NOT NULL auto_increment,
               `page_descr` varchar(50) NOT NULL default '',
               PRIMARY KEY (page_nr),
               KEY (db_name))
               $charset_spec");

db_query("CREATE TABLE `pma_column_comments` (
               id int(5) unsigned NOT NULL auto_increment,
               db_name varchar(64) NOT NULL default '',
               table_name varchar(64) NOT NULL default '',
               column_name varchar(64) NOT NULL default '',
               comment varchar(255) NOT NULL default '',
               PRIMARY KEY (id),
               UNIQUE KEY db_name (db_name, table_name, column_name))
               $charset_spec");

// New table auth for authentication methods
// added by kstratos
db_query("CREATE TABLE `auth` (
                  `auth_id` int(2) NOT NULL auto_increment,
                  `auth_name` varchar(20) NOT NULL default '',
                  `auth_settings` text ,
                  `auth_instructions` text ,
                  `auth_default` tinyint(1) NOT NULL default '0',
                  PRIMARY KEY (`auth_id`))
                  $charset_spec");

db_query("INSERT INTO `auth` VALUES
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
$student_upload_whitelist = quote($student_upload_whitelist);
$teacher_upload_whitelist = quote($teacher_upload_whitelist);

db_query("CREATE TABLE `config`
                (`key` VARCHAR(32) NOT NULL,
                 `value` TEXT NOT NULL,
                 PRIMARY KEY (`key`))");
db_query("INSERT INTO `config` (`key`, `value`) VALUES
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
                ('max_glossary_terms', '250'),
                ('student_upload_whitelist', $student_upload_whitelist),
                ('teacher_upload_whitelist', $teacher_upload_whitelist),
                ('login_fail_check', 1),
                ('login_fail_threshold', 15),
                ('login_fail_deny_interval', 5),
                ('login_fail_forgive_interval', 24),
                ('course_metadata', 0),
                ('opencourses_enable', 0),
                ('version', '" . ECLASS_VERSION ."')");

// tables for units module
db_query("CREATE TABLE `course_units` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`comments` MEDIUMTEXT,
	`visibility` CHAR(1) NOT NULL DEFAULT 'v',
	`order` INT(11) NOT NULL DEFAULT 0,
	`course_id` INT(11) NOT NULL) $charset_spec");

 db_query("CREATE TABLE `unit_resources` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`unit_id` INT(11) NOT NULL ,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`comments` MEDIUMTEXT,
	`res_id` INT(11) NOT NULL,
	`type` VARCHAR(255) NOT NULL DEFAULT '',
	`visibility` CHAR(1) NOT NULL DEFAULT 'v',
	`order` INT(11) NOT NULL DEFAULT 0,
	`date` DATETIME NOT NULL DEFAULT '0000-00-00') $charset_spec");
 
// Create full text indexes
db_query("ALTER TABLE `annonces` ADD FULLTEXT `annonces` (`contenu`, `title`)");
db_query("ALTER TABLE `cours` ADD FULLTEXT `cours` (`code` ,`description` ,`intitule` ,`course_keywords`, `course_addon`)");

// create indexes
db_query('CREATE INDEX `doc_path_index` ON document (course_id,subsystem,path)');			
db_query('CREATE INDEX `course_units_index` ON course_units (course_id,`order`)');	
db_query('CREATE INDEX `unit_res_index` ON unit_resources (unit_id,visibility,res_id)');			

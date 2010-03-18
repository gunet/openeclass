<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

if (!defined('ECLASS_VERSION')) {
        exit;
}

db_query("DROP DATABASE IF EXISTS ".$mysqlMainDb);
if (mysql_version()) db_query("SET NAMES utf8");
if (mysql_version()) {
        $cdb=db_query("CREATE DATABASE $mysqlMainDb CHARACTER SET utf8");

} else {
        $cdb=db_query("CREATE DATABASE $mysqlMainDb");
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
db_query("DROP TABLE IF EXISTS prof_request");
db_query("DROP TABLE IF EXISTS user");

$charset_spec = 'DEFAULT CHARACTER SET=utf8';

// create tables

#
# table `annonces`
#


db_query("CREATE TABLE annonces (
      `id` mediumint(11) NOT NULL auto_increment,
      `title` varchar(255) default NULL,
      `contenu` text,
      `temps` date default NULL,
      `cours_id` int(11) NOT NULL default '0',
      `ordre` mediumint(11) NOT NULL,
      PRIMARY KEY (id))
      $charset_spec");


#
# table admin_announcements
#
db_query("CREATE TABLE admin_announcements (
	id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	gr_title VARCHAR(255) NULL,
	 gr_body TEXT NULL,
	 gr_comment VARCHAR(255) NULL,
	 en_title VARCHAR(255) NULL,
	 en_body TEXT NULL,
	en_comment VARCHAR(255) NULL,
	date DATE NOT NULL,
	visible ENUM('V', 'I') NOT NULL) $charset_spec");

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
  `code` varchar(20) default NULL,
  `languageCourse` varchar(15) default NULL,
  `intitule` varchar(250) default NULL,
  `description` text default NULL,
  `course_keywords` text default NULL,
  `course_addon` text default NULL,
  `faculte` varchar(100) default NULL,
  `visible` tinyint(4) default NULL,
  `titulaires` varchar(200) default NULL,
  `fake_code` varchar(20) default NULL,
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
  `password` varchar(50) default NULL,
  `faculteid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cours_id`)) $charset_spec");


# #
 # Table `cours_faculte`	 
 #
 db_query("CREATE TABLE cours_faculte ( 	 
       id int(11) NOT NULL auto_increment, 	 
       faculte varchar(100) NOT NULL, 	 
       code varchar(20) NOT NULL, 	 
       facid int(11) NOT NULL default '0', 	 
       PRIMARY KEY (id)) $charset_spec");

#
# Table `cours_user`
#

db_query("CREATE TABLE cours_user (
      `cours_id` int(11) NOT NULL default '0',
      `user_id` int(11) unsigned NOT NULL default '0',
      `statut` tinyint(4) NOT NULL default '0',
      `team` int(11) NOT NULL default '0',
      `tutor` int(11) NOT NULL default '0',
      `reg_date` date NOT NULL,
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
      user_id mediumint unsigned NOT NULL auto_increment,
      nom varchar(60) default NULL,
      prenom varchar(60) default NULL,
      username varchar(20) default 'empty',
      password varchar(50) default 'empty',
      email varchar(100) default NULL,
      statut tinyint(4) default NULL,
      phone varchar(20) default NULL,
      department int(10) default NULL,
      am varchar(20) default NULL,
      registered_at int(10) NOT NULL default '0',
      expires_at int(10) NOT NULL default '0',
     `perso` enum('yes','no') NOT NULL default 'yes',
	 `lang` enum('el','en','es') DEFAULT 'el' NOT NULL,
 	`announce_flag` date NOT NULL default '0000-00-00',
 	 `doc_flag` date NOT NULL default '0000-00-00',
    `forum_flag` date NOT NULL default '0000-00-00',
     PRIMARY KEY (user_id)) $charset_spec");

db_query("CREATE TABLE admin (
      idUser mediumint unsigned  NOT NULL default '0',
      UNIQUE KEY idUser (idUser)) $charset_spec");

db_query("CREATE TABLE loginout (
      idLog mediumint(9) unsigned NOT NULL auto_increment,
      id_user mediumint(9) unsigned NOT NULL default '0',
      ip char(16) NOT NULL default '0.0.0.0',
      loginout.when datetime NOT NULL default '0000-00-00 00:00:00',
      loginout.action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
      PRIMARY KEY (idLog)) $charset_spec");

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


// encrypt the admin password into DB
$password_encrypted = md5($passForm);
$exp_time = time() + 140000000;
db_query("INSERT INTO `user` (`prenom`, `nom`, `username`, `password`, `email`, `statut`,`registered_at`,`expires_at`)
    VALUES ('$nameForm', '$surnameForm', '$loginForm','$password_encrypted','$emailForm','1',".time().",".$exp_time.")");
$idOfAdmin=mysql_insert_id();
db_query("INSERT INTO loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action) VALUES ('', '".$idOfAdmin."', '".$REMOTE_ADDR."', NOW(), 'LOGIN')");


#add admin in list of admin
db_query("INSERT INTO admin VALUES ('".$idOfAdmin."')");

#
# Table structure for table `prof_request`
#

db_query("CREATE TABLE `prof_request` (
                `rid` int(11) NOT NULL auto_increment,
                `profname` varchar(255) NOT NULL default '',
                `profsurname` varchar(255) NOT NULL default '',
                `profuname` varchar(255) NOT NULL default '',
                `profpassword` varchar(255) NOT NULL default '',
                `profemail` varchar(255) NOT NULL default '',
                `proftmima` varchar(255) default NULL,
                `profcomm` varchar(20) default NULL,
                `status` int(11) default NULL,
                `date_open` datetime default NULL,
                `date_closed` datetime default NULL,
                `comment` text default NULL,
                `lang` ENUM('el', 'en', 'es') NOT NULL DEFAULT 'el',
                `statut` tinyint(4) NOT NULL default 1,
                PRIMARY KEY (`rid`)) $charset_spec");


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

db_query("INSERT INTO `auth` VALUES (1, 'eclass', '', '', 1)");
db_query("INSERT INTO `auth` VALUES (2, 'pop3', '', '', 0)");
db_query("INSERT INTO `auth` VALUES (3, 'imap', '', '', 0)");
db_query("INSERT INTO `auth` VALUES (4, 'ldap', '', '', 0)");
db_query("INSERT INTO `auth` VALUES (5, 'db', '', '', 0)");
db_query("INSERT INTO `auth` VALUES (6, 'shibboleth', '', '', 0)");


db_query("CREATE TABLE `config` (
               `id` MEDIUMINT NOT NULL AUTO_INCREMENT,
               `key` VARCHAR( 255 ) NOT NULL,
               `value` VARCHAR( 255 ) NOT NULL,
               PRIMARY KEY (`id`)) $charset_spec");

db_query("INSERT INTO `config` (`key`, `value`)
               VALUES ('version', '" . ECLASS_VERSION ."')");


#
# Table passwd_reset (used by the password reset module)
#

db_query("CREATE TABLE `passwd_reset` (
                `user_id` INT(11) NOT NULL,
                `hash` VARCHAR(40) NOT NULL,
                `password` VARCHAR(8) NOT NULL,
                `datetime` DATETIME NOT NULL) $charset_spec");

// tables for units module
db_query("CREATE TABLE `course_units` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`comments` MEDIUMTEXT NOT NULL DEFAULT '',
	`visibility` CHAR(1) NOT NULL DEFAULT 'v',
	`order` INT(11) NOT NULL DEFAULT 0,
	`course_id` INT(11) NOT NULL) $charset_spec");

 db_query("CREATE TABLE `unit_resources` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`unit_id` INT(11) NOT NULL ,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`comments` MEDIUMTEXT NOT NULL DEFAULT '',
	`res_id` INT(11) NOT NULL,
	`type` VARCHAR(255) NOT NULL DEFAULT '',
	`visibility` CHAR(1) NOT NULL DEFAULT 'v',
	`order` INT(11) NOT NULL DEFAULT 0,
	`date` DATETIME NOT NULL DEFAULT '0000-00-00') $charset_spec");
 
//dhmiourgia full text indexes
db_query("ALTER TABLE `annonces` ADD FULLTEXT `annonces` (`contenu`)");
db_query("ALTER TABLE `cours` ADD FULLTEXT `cours` (`code` ,`description` ,`intitule` ,`course_keywords`, `course_addon`)");

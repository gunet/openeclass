<?php
/* ========================================================================
 * Open eClass 2.10
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

session_start();

//Flag for fixing relative path
//See init.php to undestand its logic
$path2add = 2;

include '../include/baseTheme.php';
include '../include/lib/fileUploadLib.inc.php';
include '../include/lib/forcedownload.php';
require_once '../include/phpass/PasswordHash.php';

// set default storage engine
db_query("SET storage_engine=MYISAM");

include 'upgrade_functions.php';

@set_time_limit(0);

load_global_messages();

// include_messages
include("${webDir}modules/lang/$language/common.inc.php");
$extra_messages = "${webDir}/config/$language.inc.php";
if (file_exists($extra_messages)) {
        include $extra_messages;
} else {
        $extra_messages = false;
}
include("${webDir}modules/lang/$language/messages.inc.php");
if ($extra_messages) {
        include $extra_messages;
}

$nameTools = $langUpgrade;
$tool_content = "";

$auth_methods = array("imap","pop3","ldap","db");
$OK = "[<font color='green'> $langSuccessOk </font>]";
$BAD = "[<font color='red'> $langSuccessBad </font>]";

$charset_spec = 'DEFAULT CHARACTER SET=utf8';

// default quota values  (if needed)
$diskQuotaDocument = 40000000;
$diskQuotaGroup = 40000000;
$diskQuotaVideo = 20000000;
$diskQuotaDropbox = 40000000;

$fromadmin = true;

if (isset($_POST['submit_upgrade'])) {
	$fromadmin = false;
}

if (!defined('UTF8')) {
        $Institution = iconv('ISO-8859-7', 'UTF-8', $Institution);
        $postaddress = iconv('ISO-8859-7', 'UTF-8', $postaddress);
}

if (!isset($_POST['submit2'])) {
        if (!is_admin(@q($_POST['login']), @q($_POST['password']), $mysqlMainDb)) {
                $tool_content .= "<p class='alert1'>$langUpgAdminError</p>
                        <center><a href='index.php'>$langBack</a></center>";
                draw($tool_content, 0);
                exit;
        }
}
$_SESSION['user_perso_active'] = false;
// Make sure 'video' subdirectory exists and is writable
if (!file_exists('../video')) {
        if (!mkdir('../video')) {
                die("$langUpgNoVideoDir");
        }
} elseif (!is_dir('../video')) {
        die("$langUpgNoVideoDir2");
} elseif (!is_writable('../video')) {
        die("$langUpgNoVideoDir3");
}

mkdir_or_error('../courses/temp');
touch_or_error('../courses/temp/index.htm');
mkdir_or_error('../courses/userimg');
touch_or_error('../courses/userimg/index.htm');
touch_or_error('../video/index.htm');

// ********************************************
// upgrade config.php
// *******************************************
if (!@chdir("../config/")) {
     die ("$langConfigError4");
}
if (!isset($_POST['submit2']) and isset($_SESSION['is_admin']) and $_SESSION['is_admin'] == true) {
        if (ini_get('register_globals')) { // check if register globals is Off
                $tool_content .= "<div class='caution'>$langWarningInstall1</div>";
        }
        if (ini_get('short_open_tag')) { // check if short_open_tag is Off
                $tool_content .= "<div class='caution'>$langWarningInstall2</div>";
        }
        // get old contact values
        $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>" .
                "<div class='info'>" .
                "<p>$langConfigFound" .
                "<br>$langConfigMod</p></div>" .
                "<fieldset><legend>$langUpgContact</legend>" .
                "<table width='100%' class='tbl'><tr><th width='220'>$langInstituteShortName:</th>" .
                "<td><input class=auth_input_admin type='text' size='40' name='Institution' value='".q(@$Institution)."'></td></tr>" .
                "<tr><th>$langUpgAddress</th>" .
                "<td><textarea rows='3' cols='40' class=auth_input_admin name='postaddress'>".q(@$postaddress)."</textarea></td></tr>" .
                "<tr><th>$langUpgTel</th>" .
                "<td><input class=auth_input_admin type='text' name='telephone' value='".q(@$telephone)."'></td></tr>" .
                "<tr><th>Fax:</th>" .
                "<td><input class=auth_input_admin type='text' name='fax' value='".q(@$fax)."'></td></tr></table></fieldset>
                <fieldset><legend>$langUploadWhitelist</legend>
                <table class='tbl' width='100%'>
                <tr>
                <th class='left'>$langStudentUploadWhitelist</th>
                <td><textarea rows='6' cols='60' name='student_upload_whitelist'>pdf, ps, eps, tex, latex, dvi, texinfo, texi, zip, rar, tar, bz2, gz, 7z, xz, lha, lzh, z, Z, doc, docx, odt, ott, sxw, stw, fodt, txt, rtf, dot, mcw, wps, xls, xlsx, xlt, ods, ots, sxc, stc, fods, uos, csv, ppt, pps, pot, pptx, ppsx, odp, otp, sxi, sti, fodp, uop, potm, odg, otg, sxd, std, fodg, odb, mdb, ttf, otf, jpg, jpeg, png, gif, bmp, tif, tiff, psd, dia, svg, ppm, xbm, xpm, ico, avi, asf, asx, wm, wmv, wma, dv, mov, moov, movie, mp4, mpg, mpeg, 3gp, 3g2, m2v, aac, m4a, flv, f4v, m4v, mp3, swf, webm, ogv, ogg, mid, midi, aif, rm, rpm, ram, wav, mp2, m3u, qt, vsd, vss, vst</textarea></td>
                </tr>
                <tr>
                <th class='left'>$langTeacherUploadWhitelist</th>
                <td><textarea rows='6' cols='60' name='teacher_upload_whitelist'>html, js, css, xml, xsl, cpp, c, java, m, h, tcl, py, sgml, sgm, ini, ds_store</textarea></td>
                </tr>
                </table>
                </fieldset>
                <div class='right'><input name='submit2' value='".q($langCont)." &raquo;' type='submit'></div>
                </form>";
} else {
        // Main part of upgrade starts here
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $langUpgrade; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="../template/<?php echo $theme ?>/theme.css" rel="stylesheet" type="text/css" />
</head>
<body class='upgrade-main'>
  <div id="container" style="padding: 30px;">
  <div id="header">

<a href="<?php echo $urlAppend ?>" title="<?php echo q($siteName) ?>" class="logo"></a></div>
<?php

        echo "<p class='title1'>$langUpgradeStart</p>",
             "<p class='sub_title1'>$langUpgradeConfig</p>";
	flush();
        // backup of config file
        if (!copy("config.php","config_backup.php"))
                die ("$langConfigError1");

        $conf = file_get_contents("config.php");
        if (!$conf)
                die ("$langConfigError2");

        $lines_to_add = "";

        // Convert to UTF-8 if needed
        if (!defined('UTF8')) {
                $lines_to_add .= "define('UTF8', true);\n";
                $conf = iconv('ISO-8859-7', 'UTF-8', $conf);
        }

        // for upgrading 1.5 --> 1.7
        if (!strstr($conf, '$postaddress')) {
                $lines_to_add .= "\$postaddress = ".quote($_POST['postaddress']).";\n";
        }
        if (!strstr($conf, '$fax')) {
                $lines_to_add .= "\$fax = ".quote($_POST['fax']).";\n";
        }

        if (!strstr($conf, '$durationAccount')) {
                $lines_to_add .= "\$durationAccount = \"126144000\";\n";
        }
        if (!strstr($conf, '$persoIsActive')) {
                $lines_to_add .= "\$persoIsActive = true;\n";
        }
        if (!strstr($conf, '$encryptedPasswd')) {
                $lines_to_add .= "\$encryptedPasswd = true;\n";
        }
        $new_copyright = file_get_contents('../info/license/header.txt');

        $new_conf = preg_replace(
                        array(
				'#^.*(mainInterfaceWidth|bannerPath|userMailCanBeEmpty|colorLight|colorMedium|colorDark|table_border|color1|color2).*$#m',
                                '#\$postaddress\b[^;]*;#sm',
                                '#\$fax\b[^;]*;#',
                                '#(\?>)?\s*$#',
                                '#\$Institution\b[^;]*;#',
                                '#\$telephone\b[^;]*;#',
                                '#^/\*$.*^\*/$#sm',
                                '#\/\/ .*^\/\/ HTTP_COOKIE[^\n]+$#sm'),
                        array(
				'',
                                "\$postaddress = ".quote($_POST['postaddress']).";",
                                "\$fax = ".quote($_POST['fax']).";",
                            	'',
                                "\$Institution = ".quote($_POST['Institution']).";",
                                "\$telephone = ".quote($_POST['telephone']).";",
                                $new_copyright,
                                ''),
                        $conf) . "\n" . $lines_to_add;
        $fp = @fopen("config.php","w");
        if (!$fp)
                die ("$langConfigError3");
        fwrite($fp, $new_conf);
        fclose($fp);


        // ****************************************************
        // 		upgrade eclass main database
        // ****************************************************

	echo "<p class='sub_title1'>$langUpgradeBase <b>$mysqlMainDb</b></p>";
	flush();
        mysql_select_db($mysqlMainDb);
	// Create or upgrade config table
	if (!mysql_table_exists($mysqlMainDb, 'config')) {
                db_query("CREATE TABLE `config`
                                (`key` VARCHAR(32) NOT NULL,
                                 `value` VARCHAR(255) NOT NULL,
                                 PRIMARY KEY (`key`)) $charset_spec");
                db_query("INSERT INTO `config` (`key`, `value`)
                                 VALUES ('version', '2.1.2')");
                $oldversion = '2.1.2';
	        db_query('SET NAMES greek');
        	// old queries
        	require "upgrade_main_db_old.php";
	} else {
                if (mysql_field_exists($mysqlMainDb, 'config', 'id')) {
                        db_query("RENAME TABLE config TO old_config");
                        db_query("CREATE TABLE `config`
                                        (`key` VARCHAR(32) NOT NULL,
                                         `value` VARCHAR(255) NOT NULL,
                                         PRIMARY KEY (`key`)) $charset_spec");
                        db_query("INSERT INTO config
                                         SELECT `key`, `value` FROM old_config
                                         GROUP BY `key`");
                        db_query("DROP TABLE old_config");
                }
                $oldversion = get_config('version');
        }
        db_query("INSERT IGNORE INTO `config` (`key`, `value`) VALUES
  			('dont_display_login_form', '0'),
			('email_required', '0'),
                        ('email_from', '1'),
			('am_required', '0'),
			('dropbox_allow_student_to_student', '0'),
			('block_username_change', '0'),
			('betacms', '0'),
			('enable_mobileapi', '0'),
			('display_captcha', '0'),
			('insert_xml_metadata', '0'),
			('doc_quota', '200'),
			('dropbox_quota', '100'),
			('video_quota', '100'),
			('group_quota', '100')");

        if ($oldversion < '2.1.3') {
        	// delete useless field
        	if (mysql_field_exists($mysqlMainDb, 'cours', 'scoreShow')) {
	        	echo delete_field('cours', 'scoreShow');
                }
        	// delete old example test from table announcements
                $langAnnounceExample = 'Παράδειγμα ανακοίνωσης. Μόνο ο καθηγητής και τυχόν άλλοι διαχειριστές του μαθήματος μπορεί να ανεβάσουν ανακοινώσεις.';
                db_query('SET NAMES utf8');
	        db_query("DELETE from annonces WHERE contenu='$langAnnounceExample'");
        }

        if ($oldversion < '2.2') {
                // course units
		db_query("CREATE TABLE IF NOT EXISTS `course_units` (
			`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`title` VARCHAR(255) NOT NULL DEFAULT '',
			`comments` MEDIUMTEXT,
			`visibility` CHAR(1) NOT NULL DEFAULT 'v',
			`order` INT(11) NOT NULL DEFAULT 0,
			`course_id` INT(11) NOT NULL) $charset_spec");
                db_query("CREATE TABLE IF NOT EXISTS `unit_resources` (
			`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`unit_id` INT(11) NOT NULL ,
			`title` VARCHAR(255) NOT NULL DEFAULT '',
			`comments` MEDIUMTEXT,
			`res_id` INT(11) NOT NULL,
			`type` VARCHAR(255) NOT NULL DEFAULT '',
			`visibility` CHAR(1) NOT NULL DEFAULT 'v',
			`order` INT(11) NOT NULL DEFAULT 0,
			`date` DATETIME NOT NULL DEFAULT '0000-00-00') $charset_spec");
	}

        if ($oldversion < '2.2.1') {
                db_query("ALTER TABLE `cours` CHANGE `doc_quota` `doc_quota` FLOAT NOT NULL DEFAULT '104857600'");
                db_query("ALTER TABLE `cours` CHANGE `video_quota` `video_quota` FLOAT NOT NULL DEFAULT '104857600'");
                db_query("ALTER TABLE `cours` CHANGE `group_quota` `group_quota` FLOAT NOT NULL DEFAULT '104857600'");
                db_query("ALTER TABLE `cours` CHANGE `dropbox_quota` `dropbox_quota` FLOAT NOT NULL DEFAULT '104857600'");
                db_query("CREATE TABLE IF NOT EXISTS `forum_notify` (
                        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        `user_id` INT NOT NULL DEFAULT '0',
                        `cat_id` INT NULL ,
                        `forum_id` INT NULL ,
                        `topic_id` INT NULL ,
                        `notify_sent` BOOL NOT NULL DEFAULT '0',
                        `course_id` INT NOT NULL DEFAULT '0') $charset_spec");

        	if (!mysql_field_exists($mysqlMainDb, 'cours_user', 'cours_id')) {
	        	db_query('ALTER TABLE cours_user ADD cours_id int(11) DEFAULT 0 NOT NULL FIRST');
                        db_query('UPDATE cours_user SET cours_id =
                                        (SELECT cours_id FROM cours WHERE code = cours_user.code_cours)
                                  WHERE cours_id = 0');
	        	db_query('ALTER TABLE cours_user DROP PRIMARY KEY, ADD PRIMARY KEY (cours_id, user_id)');
                        db_query('CREATE INDEX cours_user_id ON cours_user (user_id, cours_id)');
                        db_query('ALTER TABLE cours_user DROP code_cours');
                }

        	if (!mysql_field_exists($mysqlMainDb, 'annonces', 'cours_id')) {
	        	db_query('ALTER TABLE annonces ADD cours_id int(11) DEFAULT 0 NOT NULL AFTER code_cours');
                        db_query('UPDATE annonces SET cours_id =
                                        (SELECT cours_id FROM cours WHERE code = annonces.code_cours)
                                  WHERE cours_id = 0');
                        db_query('ALTER TABLE annonces DROP code_cours');
                }
        }
	if ($oldversion < '2.3.1') {
		if (!mysql_field_exists($mysqlMainDb, 'prof_request', 'am')) {
			db_query('ALTER TABLE `prof_request` ADD `am` VARCHAR(20) NULL AFTER profcomm');
		}
        }
	db_query("INSERT IGNORE INTO `auth` VALUES (7, 'cas', '', '', 0)");
        mysql_field_exists($mysqlMainDb, 'user', 'email_public') or
                db_query("ALTER TABLE `user`
                                ADD `email_public` TINYINT(1) NOT NULL DEFAULT 0,
                                ADD `phone_public` TINYINT(1) NOT NULL DEFAULT 0,
                                ADD `am_public` TINYINT(1) NOT NULL DEFAULT 0");
        if ($oldversion < '2.4') {
        	if (mysql_field_exists($mysqlMainDb, 'cours', 'faculte')) {
	        	echo delete_field('cours', 'faculte');
                }

		db_query("ALTER TABLE user CHANGE lang lang VARCHAR(16) NOT NULL DEFAULT 'el'");
                mysql_index_exists('user', 'user_username') or
                        db_query('CREATE INDEX user_username ON user (username)');
                mysql_index_exists('course_units', 'course_units_title') or
                        db_query('CREATE FULLTEXT INDEX course_units_title ON course_units (title)');
                mysql_index_exists('course_units', 'course_units_comments') or
                        db_query('CREATE FULLTEXT INDEX course_units_comments ON course_units (comments)');
                mysql_index_exists('unit_resources', 'unit_resources_title') or
                        db_query('CREATE FULLTEXT INDEX unit_resources_title ON unit_resources (title)');
                mysql_index_exists('unit_resources', 'unit_resources_title') or
                        db_query('CREATE FULLTEXT INDEX unit_resources_comments ON unit_resources (comments)');
                mysql_field_exists($mysqlMainDb, 'annonces', 'visibility') or
                        db_query("ALTER TABLE `annonces` ADD `visibility` CHAR(1) NOT NULL DEFAULT 'v'");
                mysql_field_exists($mysqlMainDb, 'user', 'description') or
                        db_query("ALTER TABLE `user` ADD description TEXT,
                                                     ADD has_icon BOOL NOT NULL DEFAULT 0");
                mysql_field_exists($mysqlMainDb, 'user', 'verified_mail') or
                        db_query("ALTER TABLE `user` ADD verified_mail BOOL NOT NULL DEFAULT ".EMAIL_UNVERIFIED.",
                                                     ADD receive_mail BOOL NOT NULL DEFAULT 1");
                mysql_field_exists($mysqlMainDb, 'cours_user', 'receive_mail') or
                        db_query("ALTER TABLE `cours_user` ADD receive_mail BOOL NOT NULL DEFAULT 1");
		db_query("ALTER TABLE `loginout` CHANGE `ip` `ip` CHAR(39) NOT NULL DEFAULT '0.0.0.0'");
                db_query("CREATE TABLE IF NOT EXISTS `document` (
                                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL,
				`subsystem` TINYINT(4) NOT NULL,
				`subsystem_id` INT(11) DEFAULT NULL,
                                `path` VARCHAR(255) NOT NULL,
                                `filename` VARCHAR(255) NOT NULL,
                                `visibility` CHAR(1) NOT NULL DEFAULT 'v',
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
                                `language` VARCHAR(16) NOT NULL DEFAULT '',
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
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL DEFAULT 0,
                                `name` varchar(100) NOT NULL DEFAULT '',
                                `description` TEXT,
                                `forum_id` INT(11) NULL,
                                `max_members` INT(11) NOT NULL DEFAULT 0,
                                `secret_directory` varchar(30) NOT NULL DEFAULT '0') $charset_spec");
                db_query("CREATE TABLE IF NOT EXISTS `group_members` (
                                `group_id` INT(11) NOT NULL,
                                `user_id` INT(11) NOT NULL,
                                `is_tutor` INT(11) NOT NULL DEFAULT 0,
                                `description` TEXT,
                                PRIMARY KEY (`group_id`, `user_id`)) $charset_spec");
                db_query("CREATE TABLE IF NOT EXISTS `glossary` (
			       `id` MEDIUMINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			       `term` VARCHAR(255) NOT NULL,
			       `definition` text NOT NULL,
			       `url` text,
                               `order` INT(11) NOT NULL DEFAULT 0,
                               `datestamp` DATETIME NOT NULL,
                               `course_id` INT(11) NOT NULL) $charset_spec");
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
                db_query('CREATE TABLE IF NOT EXISTS ebook (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL,
                                `order` INT(11) NOT NULL,
                                `title` TEXT) ' . $charset_spec);
                db_query('CREATE TABLE IF NOT EXISTS ebook_section (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `ebook_id` INT(11) NOT NULL,
                                `public_id` VARCHAR(11) NOT NULL,
				`file` VARCHAR(128),
                                `title` TEXT) ' . $charset_spec);
                db_query('CREATE TABLE IF NOT EXISTS ebook_subsection (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `section_id` VARCHAR(11) NOT NULL,
                                `public_id` VARCHAR(11) NOT NULL,
                                `file_id` INT(11) NOT NULL,
                                `title` TEXT) ' . $charset_spec);

                if (mysql_table_exists($mysqlMainDb, 'prof_request')) {
                        db_query("RENAME TABLE prof_request TO user_request");
                        db_query("ALTER TABLE user_request
                                        CHANGE rid id INT(11) NOT NULL auto_increment,
                                        CHANGE profname name VARCHAR(255) NOT NULL DEFAULT '',
                                        CHANGE profsurname surname VARCHAR(255) NOT NULL DEFAULT '',
                                        CHANGE profuname uname VARCHAR(255) NOT NULL DEFAULT '',
                                        CHANGE profpassword password VARCHAR(255) NOT NULL DEFAULT '',
                                        CHANGE profemail email varchar(255) NOT NULL DEFAULT '',
                                        CHANGE proftmima faculty_id INT(11) NOT NULL DEFAULT 0,
                                        CHANGE profcomm phone VARCHAR(20) NOT NULL DEFAULT '',
                                        CHANGE lang lang VARCHAR(16) NOT NULL DEFAULT 'el',
                                        ADD ip_address INT(11) UNSIGNED NOT NULL DEFAULT 0");
                }

                // Upgrade table admin_announcements if needed
                if (mysql_field_exists($mysqlMainDb, 'admin_announcements', 'gr_body')) {
                        db_query("RENAME TABLE `admin_announcements` TO `admin_announcements_old`");
                        db_query("CREATE TABLE IF NOT EXISTS `admin_announcements` (
                                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                                        `title` VARCHAR(255) DEFAULT NULL,
                                        `body` TEXT,
                                        `date` DATETIME NOT NULL,
                                        `begin` DATETIME DEFAULT NULL,
                                        `end` DATETIME DEFAULT NULL,
                                        `visible` ENUM('V','I') NOT NULL,
                                        `lang` VARCHAR(10) NOT NULL DEFAULT 'el',
                                        `ordre` MEDIUMINT(11) NOT NULL DEFAULT 0,
                                        PRIMARY KEY (`id`)) $charset_spec");

                        $aq = db_query("INSERT INTO admin_announcements (title, body, `date`, visible, lang)
                                        SELECT gr_title AS title, CONCAT_WS('  ', gr_body, gr_comment) AS body, `date`, visible, 'el'
                                        FROM admin_announcements_old WHERE gr_title <> '' OR gr_body <> ''");
                        $adm = db_query("INSERT INTO admin_announcements (title, body, `date`, visible, lang)
                                         SELECT en_title AS title, CONCAT_WS('  ', en_body, en_comment) AS body, `date`, visible, 'en'
                                         FROM admin_announcements_old WHERE en_title <> '' OR en_body <> ''");
                        db_query("DROP TABLE admin_announcements_old");
                }
                mysql_field_exists($mysqlMainDb, 'admin_announcements', 'ordre') or
                        db_query("ALTER TABLE `admin_announcements` ADD `ordre` MEDIUMINT(11) NOT NULL DEFAULT 0 AFTER `lang`");
		// not needed anymore
		if (mysql_table_exists($mysqlMainDb, 'cours_faculte')) {
			db_query("DROP TABLE cours_faculte");
		}
        }

        if ($oldversion < '2.5') {
                db_query("INSERT IGNORE INTO `config` (`key`, `value`) VALUES
                        ('disable_eclass_stud_reg', '0'),
                        ('disable_eclass_prof_reg', '0'),
                        ('email_verification_required', '1'),
                        ('dont_mail_unverified_mails', '1'),
                        ('close_user_registration', '0'),
                        ('max_glossary_terms', '250'),
                        ('code_key', '" . generate_secret_key(32) . "')");

                // old users have their email verified
                if (mysql_field_exists($mysqlMainDb, 'user', 'verified_mail')) {
                        db_query('ALTER TABLE `user` MODIFY `verified_mail` TINYINT(1) NOT NULL DEFAULT '.EMAIL_UNVERIFIED);
                        db_query('UPDATE `user` SET `verified_mail`= ' . EMAIL_VERIFIED);
                }
                mysql_field_exists($mysqlMainDb, 'user_request', 'verified_mail') or
                        db_query("ALTER TABLE `user_request` ADD `verified_mail` TINYINT(1) NOT NULL DEFAULT ".EMAIL_UNVERIFIED." AFTER `email`");

                db_query("UPDATE `user` SET `email`=LOWER(TRIM(`email`))");
                db_query("UPDATE `user` SET `username`=TRIM(`username`)");
        }
        
        if ($oldversion < '2.5.2') {
        	db_query("ALTER TABLE `user` MODIFY `password` VARCHAR(60) DEFAULT 'empty'");
                db_query("DROP TABLE IF EXISTS passwd_reset");
        }
        
        if ($oldversion < '2.6') {
            db_query("ALTER TABLE `config` CHANGE `value` `value` TEXT NOT NULL");
            $old_close_user_registration = intval(db_query_get_single_value("SELECT `value` FROM config WHERE `key` = 'close_user_registration'"));
            if ($old_close_user_registration == 0) {
                    $eclass_stud_reg = 2;
            } else  {
                    $eclass_stud_reg = 1;
            }
            db_query("UPDATE `config` SET `key` = 'eclass_stud_reg', 
                                          `value`= $eclass_stud_reg
                                      WHERE `key` = 'close_user_registration'");
            
            $old_disable_eclass_prof_reg = intval(!(db_query_get_single_value("SELECT `value` FROM config WHERE `key` = 'disable_eclass_prof_reg'")));
            db_query("UPDATE `config` SET `key` = 'eclass_prof_reg',
                                           `value` = $old_disable_eclass_prof_reg
                                      WHERE `key` = 'disable_eclass_prof_reg'");
            db_query("DELETE FROM `config` WHERE `key` = 'disable_eclass_stud_reg'");
            db_query("DELETE FROM `config` WHERE `key` = 'alt_auth_student_req'");
            $old_alt_auth_stud_req = intval(db_query_get_single_value("SELECT `value` FROM config WHERE `key` = 'alt_auth_student_req'"));
            if ($old_alt_auth_stud_req == 1) {                    
                    $alt_auth_stud_req = 1;
            } else {
                    $alt_auth_stud_req = 2;
            }
            db_query("INSERT IGNORE INTO `config`(`key`, `value`) VALUES
                                        ('user_registration', 1),
                                        ('alt_auth_prof_reg', 1),
                                        ('alt_auth_stud_reg', $alt_auth_stud_req)");
            
            db_query("DELETE FROM `config` WHERE `key` = 'alt_auth_student_req'");
            
            if (!mysql_field_exists($mysqlMainDb, 'user', 'whitelist')) {
                    db_query("ALTER TABLE `user` ADD `whitelist` TEXT AFTER `am_public`");
                    db_query("UPDATE `user` SET `whitelist` = '*,,' WHERE user_id = 1");
            }
            db_query("INSERT IGNORE INTO `config` (`key`, `value`) VALUES
                            ('student_upload_whitelist', ". quote($_POST['student_upload_whitelist']) ."),
                            ('teacher_upload_whitelist', ". quote($_POST['teacher_upload_whitelist']) .")");
            if (!mysql_field_exists($mysqlMainDb, 'user', 'last_passreminder')) {
                    db_query("ALTER TABLE `user` ADD `last_passreminder` DATETIME DEFAULT NULL AFTER `whitelist`");
            }
            db_query("CREATE TABLE IF NOT EXISTS login_failure (
                id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ip varchar(15) NOT NULL,
                count tinyint(4) unsigned NOT NULL default '0',
                last_fail datetime NOT NULL,
                UNIQUE KEY ip (ip)) $charset_spec");
        }
        
        if ($oldversion < '2.6.1') {
            db_query("INSERT IGNORE INTO `config`(`key`, `value`) VALUES
                                        ('login_fail_check', 1),
                                        ('login_fail_threshold', 15),
                                        ('login_fail_deny_interval', 5),
                                        ('login_fail_forgive_interval', 24)");
        }

        if ($oldversion < '2.7') {
                mysql_field_exists($mysqlMainDb, 'document', 'extra_path') or
                        db_query("ALTER TABLE `document` ADD `extra_path` VARCHAR(255) NOT NULL DEFAULT '' AFTER `path`");
		mysql_field_exists($mysqlMainDb, 'user', 'parent_email') or
                        db_query("ALTER TABLE `user` ADD `parent_email` VARCHAR(100) NOT NULL DEFAULT '' AFTER `email`");
                db_query("CREATE TABLE IF NOT EXISTS `parents_announcements` (
                        `id` mediumint(9) NOT NULL auto_increment,
                        `title` varchar(255) default NULL,
                        `content` text,
                        `date` datetime default NULL,
                        `sender_id` int(11) NOT NULL,
                        `recipient_id` int(11) NOT NULL,
                        `course_id` int(11) NOT NULL,
                         PRIMARY KEY (`id`)) $charset_spec");
        }
        if ($oldversion < '2.8') {
                db_query("INSERT IGNORE INTO `config`(`key`, `value`) VALUES
                                        ('course_metadata', 0),
                                        ('opencourses_enable', 0)");
                
                mysql_field_exists($mysqlMainDb, 'document', 'public') or
                        db_query("ALTER TABLE `document` ADD `public` TINYINT(4) NOT NULL DEFAULT 1 AFTER `visibility`");                
                mysql_field_exists($mysqlMainDb, 'cours_user', 'reviewer') or
                        db_query("ALTER TABLE `cours_user` ADD `reviewer` INT(11) NOT NULL DEFAULT 0 AFTER `editor`");
                mysql_field_exists($mysqlMainDb, 'cours', 'course_license') or
                        db_query("ALTER TABLE `cours` ADD COLUMN `course_license` TINYINT(4) NOT NULL DEFAULT 0 AFTER `course_addon`");
                
                // prevent dir list under video storage
                if ($handle = opendir('../video/')) {
                    while (false !== ($entry = readdir($handle))) {
                        if (is_dir('../video/' . $entry) && $entry != "." && $entry != "..") {
                            touch_or_error('../video/' . $entry . '/index.htm');
                        }
                    }
                    closedir($handle);
                }
        }
        
        if ($oldversion < '2.8.3') {
            db_query("CREATE TABLE IF NOT EXISTS course_review (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) NOT NULL,
                `is_certified` BOOL NOT NULL DEFAULT 0,
                `level` TINYINT(4) NOT NULL DEFAULT 0,
                `last_review` DATETIME NOT NULL,
                `last_reviewer` INT(11) NOT NULL,
                PRIMARY KEY (id)) $charset_spec");
            
            require_once '../modules/course_metadata/CourseXML.php';
            $res = db_query("SELECT cours_id, code FROM cours", $mysqlMainDb);
            while ($course = mysql_fetch_assoc($res)) {
                $xml = CourseXMLElement::initFromFile($course['code']);
                if ($xml !== false) {
                    $xmlData = $xml->asFlatArray();
                    
                    $is_certified = 0;
                    if ( (isset($xmlData['course_confirmAMinusLevel']) && $xmlData['course_confirmAMinusLevel'] == 'true') || 
                         (isset($xmlData['course_confirmALevel']) && $xmlData['course_confirmALevel'] == 'true') || 
                         (isset($xmlData['course_confirmAPlusLevel']) && $xmlData['course_confirmAPlusLevel'] == 'true') ) {
                        $is_certified = 1;
                    }
                    
                    $level = CourseXMLElement::NO_LEVEL;
                    if (isset($xmlData['course_confirmAMinusLevel']) && $xmlData['course_confirmAMinusLevel'] == 'true')
                        $level = CourseXMLElement::A_MINUS_LEVEL;
                    if (isset($xmlData['course_confirmALevel']) && $xmlData['course_confirmALevel'] == 'true')
                        $level = CourseXMLElement::A_LEVEL;
                    if (isset($xmlData['course_confirmAPlusLevel']) && $xmlData['course_confirmAPlusLevel'] == 'true')
                        $level = CourseXMLElement::A_PLUS_LEVEL;
                    
                    $last_review = date('Y-m-d H:i:s');
                    if (isset($xmlData['course_lastLevelConfirmation']) && 
                            strlen($xmlData['course_lastLevelConfirmation']) > 0 && 
                            ($ts = strtotime($xmlData['course_lastLevelConfirmation'])) > 0 ) {
                        $last_review = date('Y-m-d H:i:s', $ts);
                    }
                    
                    db_query("INSERT INTO course_review (course_id, is_certified, level, last_review, last_reviewer) 
                                VALUES (". $course['cours_id'] . ", $is_certified, $level, '$last_review', $uid)");
                }
            }
        }
        
        if ($oldversion < '2.9.1') {
            mysql_field_exists($mysqlMainDb, 'course_units', 'public') or
                db_query("ALTER TABLE `course_units` ADD `public` TINYINT(4) NOT NULL DEFAULT '1' AFTER `visibility`");
        }
        
        if (version_compare($oldversion, '2.10', '<')) {
            if (!mysql_table_exists($mysqlMainDb, 'course_description_type')) {
                db_query("CREATE TABLE `course_description_type` (
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

                db_query("INSERT INTO `course_description_type` (`id`, `title`, `syllabus`, `order`, `icon`) VALUES (1, 'a:2:{s:2:\"el\";s:41:\"Περιεχόμενο μαθήματος\";s:2:\"en\";s:15:\"Course Syllabus\";}', 1, 1, '0.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `objectives`, `order`, `icon`) VALUES (2, 'a:2:{s:2:\"el\";s:33:\"Μαθησιακοί στόχοι\";s:2:\"en\";s:23:\"Course Objectives/Goals\";}', 1, 2, '1.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `bibliography`, `order`, `icon`) VALUES (3, 'a:2:{s:2:\"el\";s:24:\"Βιβλιογραφία\";s:2:\"en\";s:12:\"Bibliography\";}', 1, 3, '2.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `teaching_method`, `order`, `icon`) VALUES (4, 'a:2:{s:2:\"el\";s:37:\"Μέθοδοι διδασκαλίας\";s:2:\"en\";s:21:\"Instructional Methods\";}', 1, 4, '3.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `assessment_method`, `order`, `icon`) VALUES (5, 'a:2:{s:2:\"el\";s:37:\"Μέθοδοι αξιολόγησης\";s:2:\"en\";s:18:\"Assessment Methods\";}', 1, 5, '4.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `prerequisites`, `order`, `icon`) VALUES (6, 'a:2:{s:2:\"el\";s:28:\"Προαπαιτούμενα\";s:2:\"en\";s:29:\"Prerequisites/Prior Knowledge\";}', 1, 6, '5.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `instructors`, `order`, `icon`) VALUES (7, 'a:2:{s:2:\"el\";s:22:\"Διδάσκοντες\";s:2:\"en\";s:11:\"Instructors\";}', 1, 7, '6.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `target_group`, `order`, `icon`) VALUES (8, 'a:2:{s:2:\"el\";s:23:\"Ομάδα στόχος\";s:2:\"en\";s:12:\"Target Group\";}', 1, 8, '7.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `featured_books`, `order`, `icon`) VALUES (9, 'a:2:{s:2:\"el\";s:47:\"Προτεινόμενα συγγράμματα\";s:2:\"en\";s:9:\"Textbooks\";}', 1, 9, '8.png')");
                db_query("INSERT INTO `course_description_type` (`id`, `title`, `order`, `icon`) VALUES (10, 'a:2:{s:2:\"el\";s:22:\"Περισσότερα\";s:2:\"en\";s:15:\"Additional info\";}', 11, 'default.png')");
            }
            
            if (!mysql_table_exists($mysqlMainDb, 'course_description')) {
                db_query("CREATE TABLE IF NOT EXISTS `course_description` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `course_id` int(11) NOT NULL,
                    `title` varchar(255) NOT NULL,
                    `comments` mediumtext,
                    `type` smallint(6),
                    `visible` tinyint(4) DEFAULT 0,
                    `order` int(11) NOT NULL,
                    `update_dt` datetime NOT NULL,
                    PRIMARY KEY (`id`)) $charset_spec");
                
                db_query('CREATE INDEX `cid` ON course_description (course_id)');
                db_query('CREATE INDEX `cd_type_index` ON course_description (type)');
                db_query('CREATE INDEX `cd_cid_type_index` ON course_description (course_id, type)');
                
                $res = db_query("SELECT ur.res_id, ur.title, ur.comments, ur.order, ur.visibility, ur.date, cu.course_id
                        FROM unit_resources ur LEFT JOIN course_units cu ON (cu.id = ur.unit_id) WHERE cu.order = -1 AND ur.res_id <> -1");
                while ($ures = mysql_fetch_array($res)) {
                    $newvis = ($ures['visibility'] == 'i') ? 0 : 1;
                    db_query("INSERT INTO course_description SET
                        course_id = " . intval($ures['course_id']) . ",
                        title = " . quote($ures['title']) . ",
                        comments = " . quote(purify($ures['comments'])) . ",
                        visible = " . intval($newvis) . ",
                        `order` = " . intval($ures['order']) . ",
                        update_dt = " . quote($ures['date']));
                }
            }
            
            if (!mysql_table_exists($mysqlMainDb, 'oai_record')) {
                db_query("CREATE TABLE `oai_record` (
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
                    `dc_subsubject` text DEFAULT NULL,
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
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `oai_identifier` (`oai_identifier`)) $charset_spec");
                
                db_query('CREATE INDEX `cid` ON oai_record (course_id)');
                db_query('CREATE INDEX `oaiid` ON oai_record (oai_identifier)');
            }
            
            // unique course_id for course_review
            $crevres = db_query("SELECT DISTINCT course_id FROM course_review");
            while ($crev = mysql_fetch_array($crevres)) {
                $crevres2 = db_query("SELECT * FROM course_review WHERE course_id = " . intval($crev['course_id']) . " ORDER BY last_review DESC");
                $crevcnt = 0;
                while ($crev2 = mysql_fetch_array($crevres2)) {
                    if ($crevcnt > 0) {
                        db_query("DELETE FROM course_review WHERE id = " . intval($crev2['id']));
                    }
                    $crevcnt++;
                }
            }
            db_query("ALTER TABLE course_review ADD UNIQUE cid (course_id)");
        }
        
        mysql_field_exists($mysqlMainDb, 'annonces', 'preview') or
                db_query("ALTER TABLE `annonces` ADD `preview` TEXT NOT NULL DEFAULT ''");
        mysql_field_exists($mysqlMainDb, 'cours', 'expand_glossary') or
                db_query("ALTER TABLE `cours` ADD `expand_glossary` BOOL NOT NULL DEFAULT 0");
        mysql_field_exists($mysqlMainDb, 'cours', 'glossary_index') or
                db_query("ALTER TABLE `cours` ADD `glossary_index` BOOL NOT NULL DEFAULT 1");
        mysql_field_exists($mysqlMainDb, 'ebook', 'visible') or
                db_query("ALTER TABLE `ebook` ADD `visible` BOOL NOT NULL DEFAULT 1");
        mysql_field_exists($mysqlMainDb, 'admin', 'privilege') or
                db_query("ALTER TABLE `admin` ADD `privilege` INT NOT NULL DEFAULT '0'");
        mysql_field_exists($mysqlMainDb, 'cours_user', 'editor') or
                db_query("ALTER TABLE `cours_user` ADD `editor` INT NOT NULL DEFAULT '0' AFTER `tutor`");
        if (!mysql_field_exists($mysqlMainDb, 'glossary', 'category_id')) {
                db_query("ALTER TABLE glossary ADD category_id INT(11) DEFAULT NULL,
                                               ADD notes TEXT NOT NULL");
                db_query("CREATE TABLE IF NOT EXISTS `glossary_category` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL,
                                `name` VARCHAR(255) NOT NULL,
                                `description` TEXT NOT NULL,
                                `order` INT(11) NOT NULL DEFAULT 0) $charset_spec");
        }
        if (!mysql_field_exists($mysqlMainDb, 'document', 'editable')) {
            db_query("ALTER TABLE `document` ADD editable TINYINT(1) NOT NULL DEFAULT 0,
                                             ADD lock_user_id INT(11) NOT NULL DEFAULT 0");
        }


	mysql_index_exists('document', 'doc_path_index') or
                db_query('CREATE INDEX `doc_path_index` ON document (course_id,subsystem,path)');
	mysql_index_exists('course_units', 'course_units_index') or
                db_query('CREATE INDEX `course_units_index` ON course_units (course_id,`order`)');
	mysql_index_exists('unit_resources', 'unit_res_index') or
		db_query('CREATE INDEX `unit_res_index` ON unit_resources (unit_id,visibility,res_id)');

        // **********************************************
        // upgrade courses databases
        // **********************************************
        $res = db_query("SELECT code, languageCourse, cours_id
                         FROM cours ORDER BY code");
        $total = mysql_num_rows($res);
        $i = 1;
        while ($code = mysql_fetch_row($res)) {
                // get course language
                $lang = $code[1];
                if ($oldversion < '2.1.3') {
                        db_query('SET NAMES greek');
        		upgrade_course_old($code[0], $lang, "($i / $total)");
                        db_query('SET NAMES utf8');
               	        upgrade_course_2_1_3($code[0], "($i / $total)");
                }
                if ($oldversion <= '2.2') {
               	        upgrade_course_2_2($code[0], $lang, "($i / $total)");
		}
                if ($oldversion < '2.3') {
			upgrade_course_2_3($code[0], "($i / $total)");
		}
                if ($oldversion < '2.4') {
                        convert_description_to_units($code[0], $code[2]);
                        upgrade_course_index_php($code[0]);
			upgrade_course_2_4($code[0], $lang, "($i / $total)");
                }
                if ($oldversion < '2.5') {
			upgrade_course_2_5($code[0], $lang, "($i / $total)");
                }
                if ($oldversion < '2.8.3') {
			upgrade_course_2_8($code[0], $lang, "($i / $total)");
                }
                if ($oldversion < '2.9') {
			upgrade_course_2_9($code[0], $lang, "($i / $total)");
                }
                if (version_compare($oldversion, '2.10', '<')) {
                    upgrade_course_2_10($code[0], $lang, "($i / $total)");
                }
                echo "</p>\n";
                $i++;
        }
	echo "<hr>";

        if ($oldversion < '2.1.3') {
	        echo "<p>$langChangeDBCharset <b>$mysqlMainDb</b> $langToUTF</p><br>";
                convert_db_utf8($mysqlMainDb);
        }

        db_query("UPDATE config SET `value` = '" . ECLASS_VERSION ."' WHERE `key`='version'", $mysqlMainDb);

        echo "<hr><p class='success'>$langUpgradeSuccess
                <br><b>$langUpgReady</b></p>
                <p class='info'>$langUpgSucNotice</p>
		<p class='right'><a href='$urlServer?logout=yes'>$langBack</a></p>";

        echo '</div></body></html>';
        exit;
} // end of if not submit

draw($tool_content, 0);

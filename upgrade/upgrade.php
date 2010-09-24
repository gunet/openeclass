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
session_start();

//Flag for fixing relative path
//See init.php to undestand its logic
$path2add=2;

include '../include/baseTheme.php';
include '../include/lib/fileUploadLib.inc.php';
include '../include/lib/forcedownload.php';

// set default storage engine
mysql_query("SET storage_engine=MYISAM");

include 'upgrade_functions.php';

set_time_limit(0);

// We need some messages from all languages to upgrade course accueil table
foreach ($native_language_names as $code => $name) {
        $templang = langcode_to_name($code);
        // include_messages
        include("${webDir}modules/lang/$templang/common.inc.php");
        $extra_messages = "${webDir}/config/$templang.inc.php";
        if (file_exists($extra_messages)) {
                include $extra_messages;
        } else {
                $extra_messages = false;
        }
        include("${webDir}modules/lang/$templang/messages.inc.php");
        if ($extra_messages) {
                include $extra_messages;
        }
        $global_messages['langCourseUnits'][$templang] = $langCourseUnits;
        $global_messages['langGlossary'][$templang] = $langGlossary;
        $global_messages['langEBook'][$templang] = $langEBook;
}
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
        if(isset($encryptedPasswd) and $encryptedPasswd) {
                $newpass = md5(@$_POST['password']);
        } else {
                // plain text password since the passwords are not hashed
                $newpass = @$_POST['password'];
        }

        if (!is_admin(@$_POST['login'], $newpass, $mysqlMainDb)) {
                $tool_content .= "<p>$langUpgAdminError</p>
                        <center><a href=\"index.php\">$langBack</a></center>";
                draw($tool_content, 0);
                exit;
        }
}

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

// ********************************************
// upgrade config.php
// *******************************************
if (!@chdir("../config/")) {
     die ("$langConfigError4");
}

if (!isset($_POST['submit2'])) {
        $closeregdefault = $close_user_registration? ' checked="checked"': '';
        // get old contact values
        $tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>" .
                "<div class='kk'>" .
                "<p>$langConfigFound" .
                "<br />$langConfigMod</p>" .
                "<fieldset><legend>$langUpgContact</legend>" .
                "<table><tr><td style='border: 1px solid #FFFFFF;'>$langInstituteShortName:</td>" .
                "<td style='border: 1px solid #FFFFFF;'>&nbsp;<input class=auth_input_admin type='text' size='40' name='Institution' value='".@$Institution."'></td></tr>" .
                "<tr><td style='border: 1px solid #FFFFFF;'>$langUpgAddress</td>" .
                "<td style='border: 1px solid #FFFFFF;'>&nbsp;<textarea rows='3' cols='40' class=auth_input_admin name='postaddress'>".@$postaddress."</textarea></td></tr>" .
                "<tr><td style='border: 1px solid #FFFFFF;'>$langUpgTel</td>" .
                "<td style='border: 1px solid #FFFFFF;'>&nbsp;<input class=auth_input_admin type='text' name='telephone' value='".@$telephone."'></td></tr>" .
                "<tr><td style='border: 1px solid #FFFFFF;'>Fax:</td>" .
                "<td style='border: 1px solid #FFFFFF;'>&nbsp;<input class=auth_input_admin type='text' name='fax' value='".@$fax."'></td></tr></table></fieldset>
                <fieldset><legend>$langUpgReg</legend>
                <table cellpadding='1' cellspacing='2' border='0' width='99%'>
                <tr><td style='border: 1px solid #FFFFFF;''>
                <span class='explanationtext'>$langViaReq</span></td>
                <td style='border: 1px solid #FFFFFF;'><input type='checkbox' name='reguser' $closeregdefault></td>
                </tr>
                </table></fieldset>
                <p><input name='submit2' value='$langCont' type='submit'></p>
                </div></form>";
} else {
        // Main part of upgrade starts here
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $langUpgrade; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="../template/classic/theme.css" rel="stylesheet" type="text/css" />
<link href="../template/classic/tool_content.css" rel="stylesheet" type="text/css" />
</head>
<body class='upgrade-main'>
<?php

        echo "<h1>$langUpgradeStart</h1>",
             "<p>$langUpgradeConfig</p>";
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
                $lines_to_add .= "\$postaddress = '$_POST[postaddress]';\n";
        }
        if (!strstr($conf, '$fax')) {
                $lines_to_add .= "\$fax = '$_POST[fax]';\n";
        }

        if (@(!$_POST['reguser'])) {
                $user_reg = 'FALSE';
        } else {
                $user_reg = 'TRUE';
        }

        if (!strstr($conf, '$close_user_registration')) {
                $lines_to_add .= "\$close_user_registration = $user_reg;\n";
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
                                '#\$close_user_registration\b[^;]*;#',
                                '#(\?>)?\s*$#',
                                '#\$Institution\b[^;]*;#',
                                '#\$telephone\b[^;]*;#',
                                '#^/\*$.*^\*/$#sm',
                                '#\/\/ .*^\/\/ HTTP_COOKIE[^\n]+$#sm'),
                        array(
				'',
                                "\$postaddress = '$_POST[postaddress]';",
                                "\$fax = '$_POST[fax]';",
                                "\$close_user_registration = $user_reg;",
                            	'',
                                "\$Institution = '$_POST[Institution]';",
                                "\$telephone = '$_POST[telephone]';",
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

	echo "<p>$langUpgradeBase <b>$mysqlMainDb</b></p>";
	flush();
	
	// creation of config table 
	if (!mysql_table_exists($mysqlMainDb, 'config')) {
		db_query("CREATE TABLE `config` (
			`id` MEDIUMINT NOT NULL AUTO_INCREMENT,
			`key` VARCHAR( 255 ) NOT NULL,
			`value` VARCHAR( 255 ) NOT NULL,
			PRIMARY KEY (`id`))", $mysqlMainDb);
		db_query("INSERT INTO `config` (`key`, `value`)
			VALUES ('version', '2.1.2')", $mysqlMainDb);
                $oldversion = '2.1.2';
	        db_query('SET NAMES greek');
        	// old queries
        	require "upgrade_main_db_old.php";
	} else {
                $r = mysql_fetch_row(db_query("SELECT `value` FROM config WHERE `key`='version'"));
                $oldversion = $r[0];
        }

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
		db_query("ALTER TABLE `user` CHANGE `lang` `lang` VARCHAR(10) NOT NULL DEFAULT 'el'");
		db_query("ALTER TABLE `prof_request` CHANGE `lang` `lang`  VARCHAR(10) NOT NULL DEFAULT 'el'");
                // course units
		db_query("CREATE TABLE IF NOT EXISTS `course_units` (
			`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`title` VARCHAR(255) NOT NULL DEFAULT '',
			`comments` MEDIUMTEXT NOT NULL DEFAULT '',
			`visibility` CHAR(1) NOT NULL DEFAULT 'v',
			`order` INT(11) NOT NULL DEFAULT 0,
			`course_id` INT(11) NOT NULL)");
                db_query("CREATE TABLE IF NOT EXISTS `unit_resources` (
			`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`unit_id` INT(11) NOT NULL ,
			`title` VARCHAR(255) NOT NULL DEFAULT '',
			`comments` MEDIUMTEXT NOT NULL DEFAULT '',
			`res_id` INT(11) NOT NULL,
			`type` VARCHAR(255) NOT NULL DEFAULT '',
			`visibility` CHAR(1) NOT NULL DEFAULT 'v',
			`order` INT(11) NOT NULL DEFAULT 0,
			`date` DATETIME NOT NULL DEFAULT '0000-00-00')");
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
                        `course_id` INT NOT NULL DEFAULT '0')");
                
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
        if ($oldversion < '2.4') {
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
		db_query("ALTER TABLE `loginout` CHANGE `ip` `ip` CHAR(39) NOT NULL DEFAULT '0.0.0.0'");
                db_query("CREATE TABLE IF NOT EXISTS `document` (
                                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL,
                                `group_id` INT(11) DEFAULT NULL,
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
                                         `subject`, `description`, `author`, `language`))");
                db_query("CREATE TABLE IF NOT EXISTS `group_properties` (
                                `course_id` INT(11) NOT NULL PRIMARY KEY ,
                                `self_registration` TINYINT(4) NOT NULL DEFAULT 1,
                                `multiple_registration` TINYINT(4) NOT NULL DEFAULT 0,
                                `forum` TINYINT(4) NOT NULL DEFAULT 1,
                                `private_forum` TINYINT(4) NOT NULL DEFAULT 0,
                                `documents` TINYINT(4) NOT NULL DEFAULT 1,
                                `wiki` TINYINT(4) NOT NULL DEFAULT 0,
                                `agenda` TINYINT(4) NOT NULL DEFAULT 0)");
                db_query("CREATE TABLE IF NOT EXISTS `group` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL DEFAULT 0,
                                `name` varchar(100) NOT NULL DEFAULT '',
                                `description` TEXT,
                                `forum_id` INT(11) NULL,
                                `max_members` INT(11) NOT NULL DEFAULT 0,
                                `secret_directory` varchar(30) NOT NULL DEFAULT '0')");
                db_query("CREATE TABLE IF NOT EXISTS `group_members` (
                                `group_id` INT(11) NOT NULL,
                                `user_id` INT(11) NOT NULL,
                                `is_tutor` INT(11) NOT NULL DEFAULT 0,
                                PRIMARY KEY (`group_id`, `user_id`))");
                db_query("CREATE TABLE IF NOT EXISTS `glossary` (
			       `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			       `term` VARCHAR(255) NOT NULL,
			       `definition` TEXT,
                               `order` INT(11) NOT NULL DEFAULT 0,
                               `datestamp` DATE NOT NULL,
                               `course_id` INT(11) NOT NULL)");
                db_query("CREATE TABLE IF NOT EXISTS `link` (
                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                `course_id` INT(11) NOT NULL,
                                `url` VARCHAR(255),
                                `title` VARCHAR(255),
                                `description` TEXT,
                                `category` INT(6) DEFAULT NULL,
                                `order` INT(6) DEFAULT 0 NOT NULL,
                                `hits` INT(6) DEFAULT 0 NOT NULL,
                                PRIMARY KEY (`id`, `course_id`))");
                db_query("CREATE TABLE IF NOT EXISTS `link_category` (
                                `id` INT(6) NOT NULL AUTO_INCREMENT,
                                `course_id` INT(11) NOT NULL,
                                `name` VARCHAR(255) NOT NULL,
                                `description` TEXT,
                                `order` INT(6) NOT NULL DEFAULT 0,
                                PRIMARY KEY (`id`, `course_id`))");
                db_query('CREATE TABLE IF NOT EXISTS ebook (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `course_id` INT(11) NOT NULL,
                                `order` INT(11) NOT NULL,
                                `title` TEXT)');
                db_query('CREATE TABLE IF NOT EXISTS ebook_section (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `ebook_id` INT(11) NOT NULL,
                                `public_id` VARCHAR(11) NOT NULL,
                                `title` TEXT)');
                db_query('CREATE TABLE IF NOT EXISTS ebook_subsection (
                                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `section_id` VARCHAR(11) NOT NULL,
                                `public_id` VARCHAR(11) NOT NULL,
                                `file` VARCHAR(128),
                                `title` TEXT)');

                // Upgrade table admin_announcements if needed
                if (mysql_field_exists($mysqlMainDb, 'admin_announcements', 'gr_body')) {
                        db_query("RENAME TABLE `admin_announcements` TO `admin_announcements_old`");
                        db_query("CREATE TABLE IF NOT EXISTS `admin_announcements` (
                                        `id` int(11) NOT NULL auto_increment,
                                        `title` varchar(255) default NULL,
                                        `body` mediumtext,
                                        `date` date NOT NULL,
                                        `visible` enum('V','I') NOT NULL,
                                        `lang` varchar(10) NOT NULL default 'el',
                                        PRIMARY KEY (`id`))");
                        
                        $aq = db_query("INSERT INTO admin_announcements (title, body, `date`, visible, lang)
                                        SELECT gr_title AS title, CONCAT_WS('  ', gr_body, gr_comment) AS body, `date`, visible, 'el'
                                        FROM admin_announcements_old WHERE gr_title <> '' OR gr_body <> ''");     
                        $adm = db_query("INSERT INTO admin_announcements (title, body, `date`, visible, lang)
                                         SELECT en_title AS title, CONCAT_WS('  ', en_body, en_comment) AS body, `date`, visible, 'en'
                                         FROM admin_announcements_old WHERE en_title <> '' OR en_body <> ''");     
                        db_query("DROP TABLE admin_announcements_old");
                }
        }

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
                echo "</p>\n";
                $i++;
        }
	echo "<hr />";
	
        if ($oldversion < '2.1.3') {
	        echo "<p>$langChangeDBCharset <b>$mysqlMainDb</b> $langToUTF</p><br />";
                convert_db_utf8($mysqlMainDb);
        }

        db_query("UPDATE config SET `value` = '" . ECLASS_VERSION ."' WHERE `key`='version'", $mysqlMainDb);

        echo "<hr /><p><em class='success_small' style='font-weight:bold;'>$langUpgradeSuccess</em></p>
                <p><em style='font-weight:bold;'>$langUpgReady</em></p>
                <p><em>$langUpgSucNotice</em></p>
		<center><p><a href='$urlServer?logout=yes'>$langBack</a></p></center>";

        echo '</body></html>';
        exit;
} // end of if not submit

draw($tool_content, 0);

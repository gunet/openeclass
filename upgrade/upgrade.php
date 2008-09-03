<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

session_start();

//Flag for fixing relative path
//See init.php to undestand its logic
$path2add=2;

include '../include/baseTheme.php';
include 'upgrade_functions.php';

$nameTools = "Αναβάθμιση των βάσεων δεδομένων του eClass";
$auth_methods = array("imap","pop3","ldap","db");
$OK = "[<font color='green'> Επιτυχία </font>]";
$BAD = "[<font color='red'> Σφάλμα ή δεν χρειάζεται τροποποίηση</font>]";

$tool_content = "";
$tool_content .= "<table width=\"99%\"><tbody>";

// default quota values  (if needed)
$diskQuotaDocument = 40000000;
$diskQuotaGroup = 40000000;
$diskQuotaVideo = 20000000;
$diskQuotaDropbox = 40000000;

// Initialise $tool_content
$tool_content = "";
$fromadmin = true;

if (isset($_POST['submit_upgrade'])) {
	$fromadmin = false;
}

if (!defined('UTF8')) {
        $Institution = iconv('ISO-8859-7', 'UTF-8', $Institution);
        $postaddress = iconv('ISO-8859-7', 'UTF-8', $postaddress);
}

if (!isset($submit2)) {
        if((isset($encryptedPasswd)) || (!empty($encryptedPasswd))) {
                $newpass = md5(@$_REQUEST['password']);
        } else {
                // plain text password since the passwords are not hashed
                $newpass = @$_REQUEST['password'];
        }

        if (!is_admin(@$_REQUEST['login'], $newpass, $mysqlMainDb)) {
                $tool_content .= "<p>Τα στοιχεία που δώσατε δεν αντιστοιχούν στο διαχειριστή του
                        συστήματος! Παρακαλούμε επιστρέψτε στην προηγούμενη σελίδα και ξαναδοκιμάστε.</p>
                        <center><a href=\"index.php\">Επιστροφή</a></center>";
                draw($tool_content, 0);
                exit;
        }
}

// Make sure 'video' subdirectory exists and is writable
if (!file_exists('../video')) {
        if (!mkdir('../video')) {
                die('Ο υποκατάλογος "video" δεν υπάρχει και δεν μπόρεσε να δημιουργηθεί. Ελέγξτε τα δικαιώματα πρόσβασης.');
        }
} elseif (!is_dir('../video')) {
        die('Υπάρχει ένα αρχείο με όνομα "video" που εμποδίζει! Θα πρέπει να το διαγράψετε.');
} elseif (!is_writable('../video')) {
        die('Δεν υπάρχει δικαίωμα εγγραφής στον υποκατάλογο "video"!');
}

// ********************************************
// upgrade config.php
// *******************************************
if (!@chdir("../config/")) {
     die ("Δεν ήταν δυνατή η πρόσβαση στον κατάλογο του αρχείο ρυθμίσεων config.php! Ελέγξτε τα δικαιώματα πρόσβασης.");
}

if (!isset($submit2)) {
        $closeregdefault = $close_user_registration? ' checked="checked"': '';
        // get old contact values
        $tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>" .
                "<div class='kk'>" .
                "<p>Στο αρχείο ρυθμίσεων <tt>config.php</tt> βρέθηκαν τα παρακάτω στοιχεία επικοινωνίας." .
                "<br>Μπορείτε να τα αλλάξετε / συμπληρώσετε.</p>" .
                "<fieldset><legend>Στοιχεία Επικοινωνίας</legend>" .
                "<table><tr><td style='border: 1px solid #FFFFFF;'>Όνομα Ιδρύματος:</td>" .
                "<td style='border: 1px solid #FFFFFF;'>&nbsp;<input class=auth_input_admin type='text' size='40' name='Institution' value='".@$Institution."'></td></tr>" .
                "<tr><td style='border: 1px solid #FFFFFF;'>Διεύθυνση Ιδρύματος:</td>" .
                "<td style='border: 1px solid #FFFFFF;'>&nbsp;<textarea rows='3' cols='40' class=auth_input_admin name='postaddress'>".@$postaddress."</textarea></td></tr>" .
                "<tr><td style='border: 1px solid #FFFFFF;'>Τηλ. Επικοινωνίας:</td>" .
                "<td style='border: 1px solid #FFFFFF;'>&nbsp;<input class=auth_input_admin type='text' name='telephone' value='".@$telephone."'></td></tr>" .
                "<tr><td style='border: 1px solid #FFFFFF;'>Fax:</td>" .
                "<td style='border: 1px solid #FFFFFF;'>&nbsp;<input class=auth_input_admin type='text' name='fax' value='".@$fax."'></td></tr></table></fieldset>
                <fieldset><legend>Εγγραφή Χρηστών</legend>
                <table cellpadding='1' cellspacing='2' border='0' width='99%'>
                <tr><td style='border: 1px solid #FFFFFF;''>
                <span class='explanationtext'>Εγγραφή χρηστών μέσω αίτησης</span></td>
                <td style='border: 1px solid #FFFFFF;'><input type='checkbox' name='reguser' $closeregdefault></td>
                </tr>
                </table></fieldset>
                <p><input name='submit2' value='Συνέχεια' type='submit'></p>
                </div></form>";
} else {
        // Main part of upgrade starts here
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>η-Τάξη ΕΚΠΑ | Αναβάθμιση των βάσεων δεδομένων του eClass</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="../template/classic/theme.css" rel="stylesheet" type="text/css" />
<link href="../template/classic/tool_content.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php

        // backup of config file 
        if (!copy("config.php","config_backup.php"))
                die ("Δεν ήταν δυνατή η λειτουργία αντιγράφου ασφαλείας του config.php! Ελέγξτε τα δικαιώματα πρόσβασης.");

        $conf = file_get_contents("config.php");
        if (!$conf)
                die ("Το αρχείο ρυθμίσεων config.php δεν μπόρεσε να διαβαστεί! Ελέγξτε τα δικαιώματα πρόσβασης.");

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
				'#^.*(mainInterfaceWidth|bannerPath|userMailCanBeEmpty).*$#m',
                                '#\$postaddress\b[^;]*;#sm',
                                '#\$fax\b[^;]*;#',
                                '#\$close_user_registration\b[^;]*;#',
                                '#\?\>#',
                                '#\$Institution\b[^;]*;#',
                                '#\$telephone\b[^;]*;#',
                                '#^/\*$.*^\*/$#sm',
                                '#\/\/ .*^\/\/ HTTP_COOKIE[^\n]+$#sm'),
                        array(
				'',
                                "\$postaddress = '$_POST[postaddress]';",
                                "\$fax = '$_POST[fax]';",
                                "\$close_user_registration = $user_reg;",
                                $lines_to_add."\n\n?>",
                                "\$Institution = '$_POST[Institution]';",
                                "\$telephone = '$_POST[telephone]';",
                                $new_copyright,
                                ''),
                        $conf);
        $fp = @fopen("config.php","w");
        if (!$fp)
                die ("Δεν πραγματοποιήθηκε η εγγραφή των αλλαγών στο αρχείο ρυθμίσεων config.php! Ελέγξτε τα δικαιώματα πρόσβασης.");
        fwrite($fp, $new_conf);
        fclose($fp);
        // ****************************************************
        // 		upgrade eclass main database
        // ****************************************************

	db_query('SET NAMES greek');

        // **************************************
        // old queries
        //  *************************************
        //upgrade queries from 1.2 --> 1.4
        if (!mysql_field_exists("$mysqlMainDb", 'user', 'am'))
                echo add_field('user', 'am', "VARCHAR( 20 ) NOT NULL");
        if (mysql_table_exists($mysqlMainDb, 'todo'))
                db_query("DROP TABLE `todo`");
        // upgrade queries to 1.4
        if (!mysql_field_exists("$mysqlMainDb",'cours','type'))
                echo add_field('cours', 'type', "ENUM('pre', 'post', 'other') DEFAULT 'pre' NOT NULL");
        if (!mysql_field_exists("$mysqlMainDb",'cours','doc_quota'))
                echo add_field('cours', 'doc_quota', "FLOAT DEFAULT '$diskQuotaDocument' NOT NULL");
        if (!mysql_field_exists("$mysqlMainDb",'cours','video_quota'))
                echo add_field('cours', 'video_quota', "FLOAT DEFAULT '$diskQuotaVideo' NOT NULL");
        if (!mysql_field_exists("$mysqlMainDb",'cours','group_quota'))
                echo add_field('cours', 'group_quota', "FLOAT DEFAULT '$diskQuotaGroup' NOT NULL");

        // upgrade query to 1.6
        if (!mysql_field_exists("$mysqlMainDb",'cours','dropbox_quota'))
                echo add_field('cours', 'dropbox_quota', "FLOAT DEFAULT '$diskQuotaDropbox' NOT NULL");

        // upgrade query to 1.7
        if (!mysql_field_exists("$mysqlMainDb", 'annonces','title'))
                echo add_field_after_field('annonces', 'title', 'id', "varchar(255) NULL");
        if (!mysql_field_exists("$mysqlMainDb", 'prof_request','statut'))
                echo add_field('prof_request', 'statut', "tinyint(4) NOT NULL default 1");

        // ***********************************************
        // new queries - upgrade queries to 2.0
        // ***********************************************
	
	// delete deprecated tables
	if (mysql_table_exists($mysqlMainDb, 'institution'))
                db_query("DROP TABLE `institution`");
	
        if (!mysql_field_exists("$mysqlMainDb",'cours','course_objectives'))
                echo add_field('cours', 'course_objectives', "TEXT");
        if (!mysql_field_exists("$mysqlMainDb",'cours','course_prerequisites'))
                echo add_field('cours', 'course_prerequisites', "TEXT");
        if (!mysql_field_exists("$mysqlMainDb",'cours','course_references'))
                echo add_field('cours', 'course_references', "TEXT");
        if (!mysql_field_exists("$mysqlMainDb",'cours','course_keywords'))
                echo add_field('cours', 'course_keywords', "TEXT");
        if (!mysql_field_exists("$mysqlMainDb",'cours','course_addon'))
                echo add_field('cours', 'course_addon', "TEXT");
        if (!mysql_field_exists("$mysqlMainDb",'cours','first_create'))
                echo add_field('cours', 'first_create', "datetime not null default '0000-00-00 00:00:00'");

        // delete useless fields
        if (mysql_field_exists("$mysqlMainDb",'cours','cahier_charges'))
                echo delete_field('cours', 'cahier_charges');
        if (mysql_field_exists("$mysqlMainDb",'cours','versionDb'))
                echo delete_field('cours', 'versionDb');
        if (mysql_field_exists("$mysqlMainDb",'cours','versionClaro'))
                echo delete_field('cours', 'versionClaro');
        if (mysql_field_exists("$mysqlMainDb",'user','inst_id'))
                echo delete_field('user', 'inst_id');
	if (mysql_field_exists("$mysqlMainDb",'cours_user','role'))
                echo delete_field('cours_user', 'role');
	
	// add field to cours_user to keep track course user registration date
	if (!mysql_field_exists($mysqlMainDb,'cours_user','reg_date'))
                echo add_field('cours_user','reg_date',"DATE NOT NULL");
	
        // kstratos - UOM
        // Add 1 new field into table 'prof_request', after the field 'profuname'
        $reg = time();
        $exp = 126144000 + $reg;
        if (!mysql_field_exists($mysqlMainDb,'prof_request','profpassword'))
                echo add_field('prof_request','profpassword',"VARCHAR(255)");
        // Add 2 new fields into table 'user': registered_at,expires_at
        if (!mysql_field_exists($mysqlMainDb,'user','registered_at'))
                echo add_field('user', 'registered_at', "INT(10) DEFAULT $reg NOT NULL");
        if (!mysql_field_exists($mysqlMainDb,'user','expires_at'))
                echo add_field('user', 'expires_at', "INT(10) DEFAULT $exp NOT NULL");

        // Add 2 new fields into table 'cours': password,faculteid
        if (!mysql_field_exists($mysqlMainDb,'cours','password'))
                echo add_field('cours', 'password', "VARCHAR(50)");

        // vagpits: update cours.faculteid with id from faculte
        if (!mysql_field_exists($mysqlMainDb,'cours','faculteid')) {
                echo add_field('cours', 'faculteid', "INT(11)");
                mysql_query("UPDATE cours,faculte SET cours.faculteid = faculte.id
                                WHERE cours.faculte = faculte.name");
        }

        // Add 1 new field into table 'cours_faculte': facid
        // vagpits: update cours.faculteid with id from faculte
        if (!mysql_field_exists($mysqlMainDb,'cours_faculte','facid')) {
                echo add_field('cours_faculte', 'facid', "INT(11)");
                mysql_query("UPDATE cours_faculte,faculte SET cours_faculte.facid = faculte.id
                                WHERE cours_faculte.faculte = faculte.name");
        }

        // *****************************
        // new tables added
        // *****************************
        // haniotak:: new table for loginout summary
        if (!mysql_table_exists($mysqlMainDb, 'loginout_summary'))  {
                mysql_query("CREATE TABLE loginout_summary (
                        id mediumint unsigned NOT NULL auto_increment,
                           login_sum int(11) unsigned  NOT NULL default '0',
                           start_date datetime NOT NULL default '0000-00-00 00:00:00',
                           end_date datetime NOT NULL default '0000-00-00 00:00:00',
                           PRIMARY KEY  (id))
                                TYPE=MyISAM DEFAULT CHARACTER SET=utf8");
        }
        // new table for monthly summary
        if (!mysql_table_exists($mysqlMainDb, 'monthly_summary'))  {
                mysql_query("CREATE TABLE monthly_summary (
                        id mediumint unsigned NOT NULL auto_increment,
                           `month` varchar(20)  NOT NULL default '0',
                           profesNum int(11) NOT NULL default '0',
                           studNum int(11) NOT NULL default '0',
                           visitorsNum int(11) NOT NULL default '0',
                           coursNum int(11) NOT NULL default '0',
                           logins int(11) NOT NULL default '0',
                           details text NOT NULL default '',
                           PRIMARY KEY  (id))
                                TYPE=MyISAM DEFAULT CHARACTER SET=utf8");
        }
        // new table 'auth' with auth methods
        if(!mysql_table_exists($mysqlMainDb, 'auth')) {
                db_query("CREATE TABLE `auth` (
                        `auth_id` int( 2 ) NOT NULL AUTO_INCREMENT ,
                        `auth_name` varchar( 20 ) NOT NULL default '',
                        `auth_settings` text NOT NULL default '',
                        `auth_instructions` text NOT NULL default '',
                        `auth_default` tinyint( 1 ) NOT NULL default '0',
                        PRIMARY KEY ( `auth_id` )) ",$mysqlMainDb); //TYPE = MYISAM  COMMENT='New table with auth methods in Eclass 2.0'
                  // Insert the default values into the new table 'auth'
                   	db_query("INSERT INTO `auth` VALUES (1, 'eclass', '', '', 1)");
                	db_query("INSERT INTO `auth` VALUES (2, 'pop3', '', '', 0)");
                	db_query("INSERT INTO `auth` VALUES (3, 'imap', '', '', 0)");
                	db_query("INSERT INTO `auth` VALUES (4, 'ldap', '', '', 0)");
                	db_query("INSERT INTO `auth` VALUES (5, 'db', '', '', 0)");
        }

        // Table agenda might be missing some fields in case database
        // was upgraded from 1.7 to an old version of 2.0. In this case,
        // just drop the table and it will be recreated
        if (!mysql_field_exists($mysqlMainDb, 'agenda', 'lesson_code') or
            !mysql_field_exists($mysqlMainDb, 'agenda', 'lesson_event_id')) {
                db_query("DROP TABLE `agenda`");
        }

        //Table agenda (stores events from all lessons)
        if (!mysql_table_exists($mysqlMainDb, 'agenda'))  {
                db_query("CREATE TABLE `agenda` (
                        `id` int(11) NOT NULL auto_increment,
                        `lesson_event_id` int(11) NOT NULL default '0',
                        `titre` varchar(200) NOT NULL default '',
                        `contenu` text NOT NULL,
                        `day` date NOT NULL default '0000-00-00',
                        `hour` time NOT NULL default '00:00:00',
                        `lasting` varchar(20) NOT NULL default '',
                        `lesson_code` varchar(50) NOT NULL default '',
                        PRIMARY KEY  (`id`)) TYPE=MyISAM ", $mysqlMainDb);
        }

        // table admin_announcemets (stores administrator  announcements)
        if (!mysql_table_exists($mysqlMainDb, 'admin_announcements'))  {
                db_query("CREATE TABLE `admin_announcements` (
                        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        `gr_title` VARCHAR( 255 ) NULL ,
                        `gr_body` VARCHAR( 255 ) NULL ,
                        `gr_comment` VARCHAR( 255 ) NULL ,
                        `en_title` VARCHAR( 255 ) NULL ,
                        `en_body` VARCHAR( 255 ) NULL ,
                        `en_comment` VARCHAR( 255 ) NULL ,
                        `date` DATE NOT NULL ,
                        `visible` ENUM( 'V', 'I' ) NOT NULL
                                ) TYPE = MYISAM ", $mysqlMainDb);
        }

        // Table passwd_reset (used by the password reset module)
        if (!mysql_table_exists($mysqlMainDb, 'passwd_reset'))  {
                db_query("CREATE TABLE `passwd_reset` (
                              `user_id` int(11) NOT NULL,
                              `hash` varchar(40) NOT NULL,
                              `password` varchar(8) NOT NULL,
                              `datetime` datetime NOT NULL
                              ) TYPE=MyISAM", $mysqlMainDb);
        	}

        // add 5 new fields to table users
        if (!mysql_field_exists("$mysqlMainDb",'user','perso'))
                echo add_field('user', 'perso', "enum('yes','no') NOT NULL default 'no'");
        if (!mysql_field_exists("$mysqlMainDb",'user','announce_flag'))
                echo add_field('user', 'announce_flag', "date NOT NULL default '0000-00-00'");
        if (!mysql_field_exists("$mysqlMainDb",'user','doc_flag'))
                echo add_field('user', 'doc_flag', "date NOT NULL default '0000-00-00'");
        if (!mysql_field_exists("$mysqlMainDb",'user','forum_flag'))
                echo add_field('user', 'forum_flag', "date NOT NULL default '0000-00-00'");
        if (!mysql_field_exists("$mysqlMainDb",'user','lang'))
                echo add_field('user', 'lang', "ENUM('el', 'en') DEFAULT 'el' NOT NULL");

        // add full text indexes for search operation
        @mysql_query("ALTER TABLE `annonces` ADD FULLTEXT `annonces` (`contenu` ,`code_cours`)");
        @mysql_query("ALTER TABLE `cours` ADD FULLTEXT `cours` (`code` ,`description` ,`intitule` ,`course_objectives`,`course_prerequisites` ,`course_keywords` ,`course_references`)");

        // encrypt passwords in users table
        if (!isset($encryptedPasswd)) {
                if ($res = db_query("SELECT user_id, password FROM user")) {
                        while ($row = mysql_fetch_array($res)) {
                                $pass = $row["password"];
                                if (!in_array($pass,$auth_methods)) {
                                        $newpass = md5($pass);
                                        // do the update
                                        db_query("UPDATE user SET password = '$newpass'
                                                        WHERE user_id = $row[user_id]"); 
                                }
                        }
                } else {
                        die("ΠΡΟΣΟΧΗ! Η διαδικασία αναβάθμισης δέν μπόρεσε να " .
                                        "κρυπτογραφήσει τα password και η πλατφόρμα δεν μπορεί " .
                                        "να λειτουργήσει. Αφαιρέστε τη γραμμή " .
                                        "«\$encryptedPasswd = true;»");
                }
        }

        // update users with no registration date
        $res = db_query("SELECT user_id,registered_at,expires_at FROM user
                        WHERE registered_at='0'
                        OR registered_at='NULL' OR registered_at=NULL
                        OR registered_at='null' OR registered_at=null
                        OR registered_at='\N' OR registered_at=\N
                        OR registered_at=''");  

                while ($row = mysql_fetch_array($res)) {
                        $registered_at = $row["registered_at"];
                        $regtime = 126144000+time();
                        db_query("UPDATE user SET registered_at=".time().",expires_at=".$regtime);
                }


        //Empty table 'agenda' in the main database so that we do not have multiple entries
        //in case we run the upgrade script twice. This has to be done at this point and NOT
        //in the while loop. Otherwise it will be emptying the table for each iteration
        $sql = 'TRUNCATE TABLE `agenda`';
        db_query($sql);

        // add indexes
        add_index('i_cours', 'code', 'cours');
        add_index('i_loginout', 'id_user', 'loginout');
        add_index('i_action', 'action', 'loginout');
        add_index('i_codecours', 'code_cours', 'annonces');
        add_index('i_temps', 'temps', 'annonces');

        // **********************************************
        // upgrade courses databases
        // **********************************************
        $res = db_query("SELECT code, languageCourse FROM cours ORDER BY code");
        while ($code = mysql_fetch_row($res)) {
                // get course language
                $lang = $code[1];

                // modify course_code/index.php
                echo "<hr><p>Τροποποίηση αρχείου index.php του μαθήματος <b>$code[0]</b><br />";
                flush();
                if (!@chdir("$webDir/courses/$code[0]")) {
                        die ("Δεν πραγματοποιήθηκε η αλλαγή στον κατάλογο του μαθήματος \"$code[0]\"! Ελέγξτε τα δικαιώματα πρόσβασης.");
                }

                if (!file_exists("temp")) {
                        mkdir("temp", 0777);
                }

                $filecontents = file_get_contents("index.php");
                if (!$filecontents)
                        die ("To αρχείο δεν μπόρεσε να διαβαστεί. Ελέγξτε τα δικαιώματα πρόσβασης.");
                $newfilecontents = preg_replace('#../claroline/#','../../modules/',$filecontents);
                $fp = @fopen("index.php","w");
                if (!$fp)
                        die ("To αρχείο δεν μπόρεσε να διαβαστεί. Ελέγξτε τα δικαιώματα πρόσβασης.");
                if (!@fwrite($fp, $newfilecontents))
                        die ("Το αρχείο δεν μπόρεσε να τροποποιηθεί. Ελέγξτε τα δικαιώματα πρόσβασης.");
                fclose($fp);
                // Fixed By vagpits
                if (!@chdir("$webDir/upgrade")) {
                        die("Δεν πραγματοποιήθηκε η αλλαγή στον κατάλογο αναβάθμισης! Ελέγξτε τα δικαιώματα πρόσβασης.");
                }

                echo "Αναβάθμιση μαθήματος <b>$code[0]</b><br>";
                flush();
                upgrade_course($code[0], $lang);
                echo "</p>\n";
        }

        convert_db_utf8($mysqlMainDb);

        echo "<p>Η αναβάθμιση των βάσεων δεδομένων του eClass πραγματοποιήθηκε!</p>
                <p>Είστε πλέον έτοιμοι να χρησιμοποιήσετε την καινούρια έκδοση του eClass!</p>
                <p>Αν παρουσιάστηκε κάποιο σφάλμα, πιθανόν κάποιο μάθημα να μην δουλεύει εντελώς σωστά.
                Σε αυτή την περίπτωση επικοινωνήστε μαζί μας στο <a href='mailto:eclass@gunet.gr'>eclass@gunet.gr</a>
                περιγράφοντας το πρόβλημα που παρουσιάστηκε και στέλνοντας (αν είναι δυνατόν) όλα τα μηνύματα που
                εμφανίστηκαν στην οθόνη σας</p>
		<center><p><a href='$urlServer?logout=yes'>Επιστροφή</a></p></center>
                </td></tr></tbody></table>";

        echo '</body></html>';
        exit;
} // end of if not submit

draw($tool_content, 0);

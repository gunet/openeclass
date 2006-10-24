<?php
//Flag for fixing relative path
//See init.php to undestand it's logic
$path2add=2;
include '../include/baseTheme.php';
include 'document_upgrade.php';

// encryption
include '../modules/auth/auth.inc.php';
$crypt = new Encryption;
if((!isset($encryptkey)) || (empty($encryptkey)))
{
	$key1 = "eclass";
}
else
{
	$key1 = $encryptkey;
}
$pswdlen = "20";
$auth_methods = array("imap","pop3","ldap","db");


$nameTools = "Αναβάθμιση των βάσεων δεδομένων του e-Class";
// Initialise $tool_content
$tool_content = "";

$fromadmin = true;

if (isset($_POST['submit_upgrade'])) {
	$fromadmin = false;
}

if((isset($_POST['password'])) && (!in_array($_POST['password'],$auth_methods))) {
	if((isset($encryptkey)) || (!empty($encryptkey)))
	$newpass = $crypt->encrypt($key1, $_POST['password'], $pswdlen);
	else
	$newpass = $_POST['password'];
} else {
	if((isset($uid)) && ($uid==1)) 	// means he is the main admin, doing the upgrade from the admin panel
	{
		// now get his password from DB
		if($res = db_query("SELECT password FROM user WHERE user_id='1'"));
		{
			$row = mysql_fetch_array($res);
			$passdb = $row[0]; // it is the encrypted password, do the decrypt process
			$passdb_decrypted = $crypt->decrypt($key1, $row[0]);
			$newpass = $passdb_decrypted;
		}
	}
	else	// doing the upgrade from the url (not admin)
	{
		$newpass = $_POST['password'];
	}
}

// error message
if (isset($_POST['login']) and isset($_POST['password']) and !is_admin($_POST['login'], $newpass)) {
	$tool_content .= "<p>Τα στοιχεία που δώσατε δεν αντιστοιχούν στο διαχειριστή του
        συστήματος! Παρακαλούμε επιστρέψτε στην προηγούμενη σελίδα και ξαναδοκιμάστε.</p>
        <center><a href=\"index.php\">Επιστροφή</a></center>";
	draw($tool_content,0);
	die();
}

if ($fromadmin)
	include "../modules/admin/check_admin.inc";

//====================
// Main body
//====================

$tool_content .= "<table width=\"99%\"><caption>Εξέλιξη Αναβάθμισης</caption><tbody>";
$OK = "[<font color='green'> Επιτυχία </font>]";
$BAD = "[<font color='red'> Σφάλμα ή δεν χρειάζεται τροποποίηση</font>]";

$errors = 0;

// default values for quota
if (!isset($diskQuotaDocument)) {
	$diskQuotaDocument = 40000000;
}
if (!isset($diskQuotaGroup)) {
	$diskQuotaGroup = 40000000;
}
if (!isset($diskQuotaVideo)) {
	$diskQuotaVideo = 20000000;
}
if (!isset($diskQuotaDropbox)) {
	$diskQuotaDropbox = 40000000;
}


// **************************************
// 		upgrade eclass main database
// **************************************

// ***********************
// old queries
//  **********************
//upgrade queries from 1.2 --> 1.4
if (!mysql_field_exists("$mysqlMainDb", 'user', 'am'))
	$tool_content .= add_field('user', 'am', "VARCHAR( 20 ) NOT NULL");
if (mysql_table_exists($mysqlMainDb, 'todo'))
		db_query("DROP TABLE `todo`");
// upgrade queries to 1.4
if (!mysql_field_exists("$mysqlMainDb",'cours','type'))
	$tool_content .= add_field('cours', 'type', "ENUM('pre', 'post', 'other') DEFAULT 'pre' NOT NULL");
if (!mysql_field_exists("$mysqlMainDb",'cours','doc_quota'))
	$tool_content .= add_field('cours', 'doc_quota', "FLOAT DEFAULT '$diskQuotaDocument' NOT NULL");
if (!mysql_field_exists("$mysqlMainDb",'cours','video_quota'))
	$tool_content .= add_field('cours', 'video_quota', "FLOAT DEFAULT '$diskQuotaVideo' NOT NULL");
if (!mysql_field_exists("$mysqlMainDb",'cours','group_quota'))
	$tool_content .= add_field('cours', 'group_quota', "FLOAT DEFAULT '$diskQuotaGroup' NOT NULL");
// upgrade query to 1.6
if (!mysql_field_exists("$mysqlMainDb",'cours','dropbox_quota'))
	$tool_content .= add_field('cours', 'dropbox_quota', "FLOAT DEFAULT '$diskQuotaDropbox' NOT NULL");

// ************************************
// new queries - upgrade queries to 2.0
// ************************************
if (!mysql_field_exists("$mysqlMainDb",'cours','course_objectives'))
	$tool_content .= add_field('cours', 'course_objectives', "TEXT");
if (!mysql_field_exists("$mysqlMainDb",'cours','course_prerequisites'))
	$tool_content .= add_field('cours', 'course_prerequisites', "TEXT");
if (!mysql_field_exists("$mysqlMainDb",'cours','course_references'))
	$tool_content .= add_field('cours', 'course_references', "TEXT");
if (!mysql_field_exists("$mysqlMainDb",'cours','course_keywords'))
	$tool_content .= add_field('cours', 'course_keywords', "TEXT");
if (!mysql_field_exists("$mysqlMainDb",'cours','course_addon'))
	$tool_content .= add_field('cours', 'course_addon', "TEXT");

// kstratos - UOM
// Add 1 new field into table 'prof_request', after the field 'profuname'
if (!mysql_field_exists($mysqlMainDb,'prof_request','profpassword'))
	$tool_content .= add_field('prof_request','profpassword',"VARCHAR(255)");
// Add 2 new fields into table 'user': registered_at,expires_at
if (!mysql_field_exists($mysqlMainDb,'user','registered_at'))
	$tool_content .= add_field('user', 'registered_at', "INT(10)");
if (!mysql_field_exists($mysqlMainDb,'user','expires_at'))
	$tool_content .= add_field('user', 'expires_at', "INT(10)");

// Add 2 new fields into table 'cours': password,faculteid
if (!mysql_field_exists($mysqlMainDb,'cours','password'))
	$tool_content .= add_field('cours', 'password', "VARCHAR(50)");

// vagpits: update cours.faculteid with id from faculte
if (!mysql_field_exists($mysqlMainDb,'cours','faculteid')) {
	$tool_content .= add_field('cours', 'faculteid', "INT(11)");
	mysql_query("UPDATE cours,faculte SET cours.faculteid = faculte.id
		WHERE cours.faculte = faculte.name");
}

// Add 1 new field into table 'cours_faculte': facid
// vagpits: update cours.faculteid with id from faculte
if (!mysql_field_exists($mysqlMainDb,'cours_faculte','facid')) {
	$tool_content .= add_field('cours_faculte', 'facid', "INT(11)");
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
        TYPE=MyISAM DEFAULT CHARACTER SET=greek");
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
        TYPE=MyISAM DEFAULT CHARACTER SET=greek");
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

// add 5 new fields to table users
if (!mysql_field_exists("$mysqlMainDb",'user','perso'))
	$tool_content .= add_field('user', 'perso', "enum('yes','no') NOT NULL default 'no'");
if (!mysql_field_exists("$mysqlMainDb",'user','announce_flag'))
	$tool_content .= add_field('user', 'announce_flag', "date NOT NULL default '0000-00-00'");
if (!mysql_field_exists("$mysqlMainDb",'user','doc_flag'))
	$tool_content .= add_field('user', 'doc_flag', "date NOT NULL default '0000-00-00'");
if (!mysql_field_exists("$mysqlMainDb",'user','forum_flag'))
	$tool_content .= add_field('user', 'forum_flag', "date NOT NULL default '0000-00-00'");
if (!mysql_field_exists("$mysqlMainDb",'user','lang'))
	$tool_content .= add_field('user', 'lang', "ENUM('el', 'en') DEFAULT 'el' NOT NULL");

// add full text indexes for search operation
db_query("ALTER TABLE `annonces` ADD FULLTEXT `annonces` (`contenu` ,`code_cours`)");
db_query("ALTER TABLE `cours` ADD FULLTEXT `cours` (`code` ,`description` ,`intitule` ,`course_objectives`,`course_prerequisites` ,`course_keywords` ,`course_references`)");


// encryption of passwords
// Orismos $durationAccount sto config.php
if((!isset($durationAccount)) || (empty($durationAccount))) {
	@chmod( "../config",777 );
	@chmod( "../config", 0777 );
	$remove_this = "?>";
	$text1 = "\$durationAccount = \"126144000\";\n";		//load file into $fc array
	$fc=@file("../config/config.php");	//open same file and use "w" to clear file
	$f=fopen("../config/config.php","w");
	foreach($fc as $line)			//loop through array using foreach
	{
		if(!strstr($line,$remove_this)) //look for $key in each line
		fputs($f,$line); //place $line back in file
		else
		{
			fwrite($f, $text1);
			fwrite($f, $remove_this);
		}
	}
	fclose($f);
	$tool_content .= "Ενημερώθηκε το config.php για το durationAccount<br />";

	// update users with no registration date
	if($res = db_query("SELECT user_id,registered_at,expires_at FROM user
		WHERE registered_at='0' 
		OR registered_at='NULL' OR registered_at=NULL  
		OR registered_at='null' OR registered_at=null 
		OR registered_at='\N' OR registered_at=\N 
		OR registered_at=''"))  {

		while($row = mysql_fetch_array($res)) {
			$registered_at = $row["registered_at"];
			// do the update
			$regtime = 126144000+time();
			if(db_query("UPDATE user SET registered_at=".time().",expires_at=".$regtime)) {
				$tool_content .= "Ενημέρωση του χρόνου λήξης λογαριασμού για τον χρήστη με id=".$row["user_id"].".Το κρυπτογραφημένο συνθηματικό είναι:".$newpass."<br />";
			} else {
				$tool_content .= "Δεν έγινε ενημέρωση για τον χρήστη με id=".$row["user_id"]."<br />";
			}
		}
	}
	else
	{
		die("Δεν έγινε ενημέρωση σε κανένα λογαριασμό χρήστη σχετικά με το χρόνο λήξης λογαριασμού. Η ενημέρωση θα συνεχιστεί κανονικά.");
	}
}  //end of duration account


// Orismos $encryptkey sto config.php
if((!isset($encryptkey)) || (empty($encryptkey))) {
	@chmod( "../config",777 );
	@chmod( "../config", 0777 );
	$has_encryption = 0;
	$remove_this = "?>";
	$text2 = "\$encryptkey = \"eclass\";\n";
	$fc=@file("../config/config.php");		//load file into $fc array
	$f=fopen("../config/config.php","w");		//open same file and use "w" to clear file
	foreach($fc as $line)		//loop through array using foreach
	{
		if(!strstr($line,$remove_this)) //look for $key in each line
		fputs($f,$line); //place $line back in file
		else
		{
			fwrite($f, $text2);
			fwrite($f, $remove_this);
			$has_encryption = 1;
		}
	}
	fclose($f);
	$tool_content .= "Ενημερώθηκε το config.php για το encryptkey<br />";

	// update all the records in user table
	if($res = db_query("SELECT user_id,password FROM user")) {
		while($row = mysql_fetch_array($res)) {
			$pass = $row["password"];
			
			// do not allow the user to have the characters: ',\" or \\ in password
			$pw = array(); 	
			$nr = 0;
			while (isset($pass{$nr})) // convert the string $password into an array $pw
			{
  			$pw[$nr] = $pass{$nr};
    		$nr++;
			}
  		if((in_array("'",$pw)) || (in_array("\"",$pw)) || (in_array("\\",$pw))) {
				$tool_content .= "Δεν έγινε κρυπτογράφηση του συνθηματικού του χρήστη με id=".$row["user_id"]." (άκυροι χαρακτήρες στο συνθηματικό)<br />";
				continue; // get the next one
			}
			
			$newpass = $crypt->encrypt($key1, $pass, $pswdlen);
			if(!in_array($pass,$auth_methods)) {
				// do the update
				if(db_query("UPDATE user SET password = '".$newpass."' WHERE user_id=".$row["user_id"])) {
					$tool_content .= "SUCCESS for user with id=".$row["user_id"].".Encrypted pass is:".$newpass."<br />";
				} else {
					$tool_content .= "NO UPDATE for user with id=".$row["user_id"]."<br />";
				}
			}
			else
			{
				continue; // move to the next one
			}
		}
	}
	else
	{
		die("ΠΡΟΣΟΧΗ! Τα passwords στη ΒΔ δεν είναι κρυπτογραφημένα και η πλατφόρμα δεν μπορεί να λειτουργήσει");
	}

}
else
{
	// check if the passwords are already encrypted
}

//Empty table 'agenda' in the main database so that we do not have multiple entries
//in case we run the upgrade script twice. This has to be done at this point and NOT
//in the while loop. Otherwise it will be emptying the table for each iteration
$sql = 'TRUNCATE TABLE `agenda`';//empty main agenda table so that we do not have multiple entries
db_query($sql, $mysqlMainDb);

// **********************************************
// upgrade courses databases
// **********************************************

$res = db_query("SELECT code FROM cours");
while ($code = mysql_fetch_row($res)) {

	// modify course_code/index.php
	$tool_content .= "<tr><td><b>Τροποποίηση αρχείου index.php του μαθήματος $code[0]</td></tr>";
	if (!@chdir("$webDir/courses/$code[0]")) {
		die ("Δεν πραγματοποιήθηκε η αλλαγή στον κατάλογο των μαθημάτων! Ελέγξτε τα δικαιώματα πρόσβασης.");
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

	$tool_content .= "<tr><td><b>Αναβάθμιση μαθήματος $code[0]</b><br>";
	mysql_select_db($code[0]);

	// upgrade queries from 1.2 --> 1.4

	if (!mysql_field_exists('$code[0]','exercices','type'))
	$tool_content .= add_field('exercices','type',"TINYINT( 4 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `description`");
	if (!mysql_field_exists('$code[0]','exercices','random'))
	$tool_content .= add_field('exercices','random',"SMALLINT( 6 ) DEFAULT '0' NOT NULL AFTER `type`");
	if (!mysql_field_exists('$code[0]','reponses','ponderation'))
	$tool_content .= add_field('reponses','ponderation',"SMALLINT( 5 ) NOT NULL AFTER `comment`");

	$s = db_query("SELECT type FROM questions",$code[0]);
	while ($f = mysql_fetch_row($s)) {
		if (empty($f[0]))  {
			if (db_query("UPDATE `questions` SET type=1",$code[0]))  {
				$tool_content .= "Πίνακας questions: $OK<br>";
			} else {
				$tool_content .= "Πίνακας questions: $BAD<br>";
				$errors++;
			}
		}
	} // while

	if (!mysql_table_exists($code[0], 'assignments'))  {
		db_query("CREATE TABLE `assignments` (
                 `id` int(11) NOT NULL auto_increment,
                  `title` varchar(200) NOT NULL default '',
                  `description` text NOT NULL,
                  `comments` text NOT NULL,
                `deadline` date NOT NULL default '0000-00-00',
                `submission_date` date NOT NULL default '0000-00-00',
                `active` char(1) NOT NULL default '1',
                `secret_directory` varchar(30) NOT NULL,
                `group_submissions` CHAR(1) DEFAULT '0' NOT NULL,
                UNIQUE KEY `id` (`id`))", $code[0]);
	}

	if (!mysql_table_exists($code[0], 'assignment_submit')) {
		db_query("CREATE TABLE `assignment_submit` (
        `id` int(11) NOT NULL auto_increment,
        `uid` int(11) NOT NULL default '0',
        `assignment_id` int(11) NOT NULL default '0',
        `submission_date` date NOT NULL default '0000-00-00',
        `submission_ip` varchar(16) NOT NULL default '',
        `file_path` varchar(200) NOT NULL default '',
        `file_name` varchar(200) NOT NULL default '',
        `comments` text NOT NULL,
        `grade` varchar(50) NOT NULL default '',
        `grade_comments` text NOT NULL,
        `grade_submission_date` date NOT NULL default '0000-00-00',
        `grade_submission_ip` varchar(16) NOT NULL default '',
                                `group_id` INT( 11 ) DEFAULT NULL,
         UNIQUE KEY `id` (`id`))",$code[0]);
	}

	update_assignment_submit();


	// upgrade queries to 1.4
	if (db_query("UPDATE accueil SET lien='../../modules/stat/index2.php?table=stat_accueil".
	"&reset=0&period=jour' WHERE id=11", $code[0])) {
		$tool_content .= "Πίνακας accueil: $OK<br>";
	} else {
		$tool_content .= "Πίνακας accueil: $BAD<br>";
		$errors++;
	}

	// upgrade queries for e-Class 1.5

	$langVideoLinks = "Βιντεοσκοπημένα Μαθήματα";
	if (!mysql_table_exists($code[0], 'videolinks'))  {
		db_query("CREATE TABLE videolinks (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               visibility CHAR(1) DEFAULT '1' NOT NULL,
               PRIMARY KEY (id))", $code[0]);

		db_query("UPDATE accueil SET
               rubrique='$langVideoLinks',
               lien='.././modules/video/videolinks.php',
               image='../../../images/videos.png',
               visible='0',
               admin='0',
               address='../../../images/pastillegris.png'
               WHERE id='6'", $code[0]);

	}

	// upgrade queries for e-Class 1.6
	$tool_content .= add_field('liens','category',"INT(4) DEFAULT '0' NOT NULL");
	$tool_content .= add_field('liens','ordre',"MEDIUMINT(8) DEFAULT '0' NOT NULL");

	if (!mysql_table_exists($code[0], 'link_categories'))  {
		db_query("CREATE TABLE `link_categories` (
          `id` int(6) NOT NULL auto_increment,
           `categoryname` varchar(255) default NULL,
          `description` text,
          `ordre` mediumint(8) NOT NULL default '0',
          PRIMARY KEY  (`id`))",$code[0]);
	}

	// correct link entries to correctly appear in a blank window
	$tool_content .= "<b>Διόρθωση συνδέσμων</b><br>";

	$sql = db_query("SELECT url FROM `liens` WHERE url REGEXP '\"target=_blank$'");
	while ($u = mysql_fetch_row($sql))  {
		$temp = $u[0];
		$newurl = preg_replace('#\s*"target=_blank#','',$temp);
		$tool_content .= "<b>Παλιός σύνδεσμος: </b>";
		$tool_content .=  $temp;
		$tool_content .= "<br><b>Καινούριος σύνδεσμος: </b>";
		$tool_content .= $newurl;
		$tool_content .= "<br>";
		db_query("UPDATE liens SET url='$newurl' WHERE url='$temp'");
	}

	// for dropbox
	$langDropbox = "Χώρος Ανταλλαγής Αρχείων";
	if (!mysql_table_exists($code[0], 'dropbox_file'))  {
		db_query("CREATE TABLE dropbox_file (
          id int(11) unsigned NOT NULL auto_increment,
        uploaderId int(11) unsigned NOT NULL default '0',
        filename varchar(250) NOT NULL default '',
        filesize int(11) unsigned NOT NULL default '0',
          title varchar(250) default '',
          description varchar(250) default '',
          author varchar(250) default '',
          uploadDate datetime NOT NULL default '0000-00-00 00:00:00',
          lastUploadDate datetime NOT NULL default '0000-00-00 00:00:00',
          PRIMARY KEY  (id),
          UNIQUE KEY UN_filename (filename))", $code[0]);
	}

	if (!mysql_table_exists($code[0], 'dropbox_person'))  {
		db_query("CREATE TABLE dropbox_person (
          fileId int(11) unsigned NOT NULL default '0',
          personId int(11) unsigned NOT NULL default '0',
          PRIMARY KEY  (fileId,personId))", $code[0]);
	}

	if (!mysql_table_exists($code[0], 'dropbox_post'))  {
		db_query("CREATE TABLE dropbox_post (
          fileId int(11) unsigned NOT NULL default '0',
          recipientId int(11) unsigned NOT NULL default '0',
          PRIMARY KEY  (fileId,recipientId))", $code[0]);
	}

	db_query("INSERT IGNORE INTO accueil VALUES (
                16,
                '$langDropbox',
                '../../modules/dropbox/index.php',
                '../../../images/dropbox.png',
                '0',
                '0',
                '../../../images/pastillegris.png',
                                'MODULE_ID_DROPBOX'
                )", $code[0]);


	// -----------------------------------------------------
	// upgrade queries for e-Class 2.0
	// -----------------------------------------------------


	//Move all non-expired events from lessons' agendas to the main agenda table
	//	$sql = 'TRUNCATE TABLE `agenda`';//empty main agenda table so that we do not have multiple entries
	//	db_query($sql, $mysqlMainDb);
	$sql = 'SELECT id, titre, contenu, day, hour, lasting
                FROM  agenda
                WHERE CONCAT(titre,contenu) != \'\'
                AND DATE_FORMAT(day,\'%Y %m %d\') >= \''.date("Y m d").'\'';

	//.  Get all agenda events from each table & parse them to arrays
	$mysql_query_result = db_query($sql, $code[0]);
	//$total_agenda_events_counter = 0;
	$event_counter=0;
	while ($myAgenda = mysql_fetch_array($mysql_query_result)) {
		$lesson_agenda[$event_counter]['id']                  = $myAgenda[0];
		$lesson_agenda[$event_counter]['title']               = $myAgenda[1];
		$lesson_agenda[$event_counter]['content']             = $myAgenda[2];
		$lesson_agenda[$event_counter]['date']                = $myAgenda[3];
		$lesson_agenda[$event_counter]['time']                = $myAgenda[4];
		$lesson_agenda[$event_counter]['duree']               = $myAgenda[5];
		$lesson_agenda[$event_counter]['lesson_code']         = $code[0];
		//		$lesson_agenda[$i][$event_counter]['lesson_title']        = $lesson_details[1];
		//		$lesson_agenda[$i][$event_counter]['lesson_prof']         = $lesson_details[2];
		$event_counter++;
	}

	for ($j=0; $j <$event_counter; $j++) {
		db_query("INSERT INTO agenda (lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
          VALUES ('".$lesson_agenda[$j]['id']."', 
                  '".$lesson_agenda[$j]['title']."', 
                  '".$lesson_agenda[$j]['content']."', 
                  '".$lesson_agenda[$j]['date']."', 
                  '".$lesson_agenda[$j]['time']."', 
                  '".$lesson_agenda[$j]['duree']."',
                  '".$lesson_agenda[$j]['lesson_code']."'
                  )", $mysqlMainDb);

	}
	// end of agenda

	$langLearnPath = "Γραμμή Μάθησης";
	if (!mysql_table_exists($code[0], 'lp_module'))  {
		db_query("CREATE TABLE `lp_module` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default '0',
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL','COURSE_DESCRIPTION','LINK') NOT NULL,
              `launch_data` text NOT NULL,
              PRIMARY KEY  (`module_id`)
             ) ", $code[0]); //TYPE=MyISAM COMMENT='List of available modules used in learning paths';
	}

	if (!mysql_table_exists($code[0], 'lp_learnPath'))  {
		db_query("CREATE TABLE `lp_learnPath` (
              `learnPath_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
              `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
              `rank` int(11) NOT NULL default '0',
              PRIMARY KEY  (`learnPath_id`),
              UNIQUE KEY rank (`rank`)
            ) ", $code[0]); //TYPE=MyISAM COMMENT='List of learning Paths';
	}

	if (!mysql_table_exists($code[0], 'lp_rel_learnPath_module'))  {
		db_query("CREATE TABLE `lp_rel_learnPath_module` (
                `learnPath_module_id` int(11) NOT NULL auto_increment,
                `learnPath_id` int(11) NOT NULL default '0',
                `module_id` int(11) NOT NULL default '0',
                `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
                `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
                `specificComment` text NOT NULL,
                `rank` int(11) NOT NULL default '0',
                `parent` int(11) NOT NULL default '0',
                `raw_to_pass` tinyint(4) NOT NULL default '50',
                PRIMARY KEY  (`learnPath_module_id`)
              ) ", $code[0]);//TYPE=MyISAM COMMENT='This table links module to the learning path using them';
	}

	if (!mysql_table_exists($code[0], 'lp_asset'))  {
		db_query("CREATE TABLE `lp_asset` (
              `asset_id` int(11) NOT NULL auto_increment,
              `module_id` int(11) NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `comment` varchar(255) default NULL,
              PRIMARY KEY  (`asset_id`)
            ) ", $code[0]); //TYPE=MyISAM COMMENT='List of resources of module of learning paths';
	}

	if (!mysql_table_exists($code[0], 'lp_user_module_progress'))  {
		db_query("CREATE TABLE `lp_user_module_progress` (
              `user_module_progress_id` int(22) NOT NULL auto_increment,
              `user_id` mediumint(9) NOT NULL default '0',
              `learnPath_module_id` int(11) NOT NULL default '0',
              `learnPath_id` int(11) NOT NULL default '0',
              `lesson_location` varchar(255) NOT NULL default '',
              `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
              `entry` enum('AB-INITIO','RESUME','') NOT NULL default 'AB-INITIO',
              `raw` tinyint(4) NOT NULL default '-1',
              `scoreMin` tinyint(4) NOT NULL default '-1',
              `scoreMax` tinyint(4) NOT NULL default '-1',
              `total_time` varchar(13) NOT NULL default '0000:00:00.00',
              `session_time` varchar(13) NOT NULL default '0000:00:00.00',
              `suspend_data` text NOT NULL,
              `credit` enum('CREDIT','NO-CREDIT') NOT NULL default 'NO-CREDIT',
              PRIMARY KEY  (`user_module_progress_id`)
            ) ", $code[0]); //TYPE=MyISAM COMMENT='Record the last known status of the user in the course';
	}

	//---------------------------------------------------------------------
	// Begin table definitions for QUESTIONNAIRE module
	//---------------------------------------------------------------------
	if (!mysql_table_exists($code[0], 'survey'))  {
		db_query("CREATE TABLE `survey` (
		`sid` bigint(14) NOT NULL auto_increment,
	  `creator_id` mediumint(8) unsigned NOT NULL default '0',
	  `course_id` varchar(20) NOT NULL default '0',
	  `name` varchar(255) NOT NULL default '',
	  `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `type` int(11) NOT NULL default '0',
	  `active` int(11) NOT NULL default '0',
	  PRIMARY KEY  (`sid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the survey module';
	}
	if (!mysql_table_exists($code[0], 'survey_answer'))  {
		db_query("CREATE TABLE `survey_answer` (
		`aid` bigint(12) NOT NULL default '0',
	  `creator_id` mediumint(8) unsigned NOT NULL default '0',
	  `sid` bigint(12) NOT NULL default '0',
	  `date` datetime NOT NULL default '0000-00-00 00:00:00',
	  PRIMARY KEY  (`aid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the survey module';
	}
	if (!mysql_table_exists($code[0], 'survey_answer_record'))  {
		db_query("CREATE TABLE `survey_answer_record` (
		`arid` int(11) NOT NULL auto_increment,
	  `aid` bigint(12) NOT NULL default '0',
	  `question_text` varchar(250) NOT NULL default '',
	  `question_answer` varchar(250) NOT NULL default '',
	  PRIMARY KEY  (`arid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the survey module';
	}
	if (!mysql_table_exists($code[0], 'survey_question'))  {
		db_query("CREATE TABLE `survey_question` (
		`sqid` bigint(12) NOT NULL default '0',
	  `sid` bigint(12) NOT NULL default '0',
	  `question_text` varchar(250) NOT NULL default '',
	  PRIMARY KEY  (`sqid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the survey module';
	}
	if (!mysql_table_exists($code[0], 'survey_question_answer'))  {
		db_query("CREATE TABLE `survey_question_answer` (
		`sqaid` int(11) NOT NULL auto_increment,
	  `sqid` bigint(12) NOT NULL default '0',
	  `answer_text` varchar(250) default NULL,
	  PRIMARY KEY  (`sqaid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the survey module';
	}



	//---------------------------------------------------------------------
	// More table definitions for QUESTIONNAIRE module
	//---------------------------------------------------------------------
	if (!mysql_table_exists($code[0], 'poll'))  {
		db_query("CREATE TABLE `poll` (
		`pid` bigint(14) NOT NULL auto_increment,
	  `creator_id` mediumint(8) unsigned NOT NULL default '0',
	  `course_id` varchar(20) NOT NULL default '0',
	  `name` varchar(255) NOT NULL default '',
	  `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `type` int(11) NOT NULL default '0',
	  `active` int(11) NOT NULL default '0',
	  PRIMARY KEY  (`pid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the poll module';
	}
	if (!mysql_table_exists($code[0], 'poll_answer'))  {
		db_query("CREATE TABLE `poll_answer` (
		`aid` bigint(12) NOT NULL default '0',
	  `creator_id` mediumint(8) unsigned NOT NULL default '0',
	  `pid` bigint(12) NOT NULL default '0',
	  `date` datetime NOT NULL default '0000-00-00 00:00:00',
	  PRIMARY KEY  (`aid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the poll module';
	}
	if (!mysql_table_exists($code[0], 'pollpoll_answer_record'))  {
		db_query("CREATE TABLE `pollpoll_answer_record` (
		`arid` int(11) NOT NULL auto_increment,
	  `aid` bigint(12) NOT NULL default '0',
	  `question_text` varchar(250) NOT NULL default '',
	  `question_answer` varchar(250) NOT NULL default '',
	  PRIMARY KEY  (`arid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the poll module';
	}
	if (!mysql_table_exists($code[0], 'poll_question'))  {
		db_query("CREATE TABLE `poll_question` (
		`pqid` bigint(12) NOT NULL default '0',
	  `pid` bigint(12) NOT NULL default '0',
	  `question_text` varchar(250) NOT NULL default '',
	  PRIMARY KEY  (`pqid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the poll module';
	}
	if (!mysql_table_exists($code[0], 'poll_question_answer'))  {
		db_query("CREATE TABLE `poll_question_answer` (
		`pqaid` int(11) NOT NULL auto_increment,
	  `pqid` bigint(12) NOT NULL default '0',
	  `answer_text` varchar(250) default NULL,
	  PRIMARY KEY  (`pqaid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the poll module';
	}
	// End of table definitions for QUESTIONNAIRE module

	// table definitions for usage modules

	if (!mysql_table_exists($code[0], 'actions')) {
		db_query("CREATE TABLE actions (
	        id int(11) NOT NULL auto_increment,
					user_id int(11) NOT NULL,
					module_id int(11) NOT NULL, 
					action_type_id int(11) NOT NULL,
                    date_time DATETIME NOT NULL default '0000-00-00 00:00:00',
                    duration int(11) NOT NULL,
                   	PRIMARY KEY (id))", $code[0]);
	}

	if (!mysql_table_exists($code[0], 'action_types')) {
		db_query("CREATE TABLE action_types (
						id int(11) NOT NULL auto_increment,
  	        name varchar(200),
						PRIMARY KEY (id))", $code[0]);
		db_query("INSERT INTO action_types VALUES ('1', 'access')", $code[0]);
	}

	if (!mysql_table_exists($code[0], 'actions_summary')) {
		db_query("CREATE TABLE actions_summary (
             id int(11) NOT NULL auto_increment,
             module_id int(11) NOT NULL,
             visits int(11) NOT NULL,
             start_date DATETIME NOT NULL default '0000-00-00 00:00:00',
             end_date DATETIME NOT NULL default '0000-00-00 00:00:00',
             duration int(11) NOT NULL,
             PRIMARY KEY (id))", $code[0]);
	}


	//---------------------------------------------------------------------
	// Add table EXERCISE_USER_RECORD for new func of EXERCISE module
	//---------------------------------------------------------------------
	if (!mysql_table_exists($code[0], 'exercise_user_record'))  {
		db_query("CREATE TABLE `exercise_user_record` (
		`eurid` int(11) NOT NULL auto_increment,
	  `eid` tinyint(4) NOT NULL default '0',
	  `uid` mediumint(8) NOT NULL default '0',
	  `RecordStartDate` datetime NOT NULL default '0000-00-00 00:00:00',
	  `RecordEndDate` datetime NOT NULL default '0000-00-00 00:00:00',
	  `TotalScore` int(11) NOT NULL default '0',
	  `TotalWeighting` int(11) default '0',
	  `attempt` int(11) NOT NULL default '0',
	  PRIMARY KEY  (`eurid`)
	) ", $code[0]); //TYPE=MyISAM COMMENT='For the poll module';
	}
	// End of table definitions



	//===============================================================================================
	//BEGIN: Move all external links to id > 100, add column define_var
	//===============================================================================================

	$result = db_query( $sql = 'SELECT `define_var` FROM `accueil`'
	. ' WHERE `id` = 25', $code[0]);

	$item = mysql_fetch_array($result);

	if ($item[0] != "MODULE_ID_TOOLADMIN") {

		if (db_query("UPDATE IGNORE `accueil`
                    SET `id` = `id` + 80
                    WHERE `id`>20", $code[0])) {
		$tool_content .= "All external (id >= 20) links moved to id >=101<br>";
                    } else {
                    	$tool_content .= "Error: Could not move external links<br>";
                    	$GLOBALS['errors']++;
                    	die();
                    }
	}
	//===============================================================================================
	//END: Move all external links to id > 100
	//===============================================================================================

	db_query("INSERT IGNORE INTO accueil VALUES (
                23,
                '$langLearnPath',
                '../../modules/learnPath/learningPathList.php',
                '../../../images/learnpath.gif',
                '1',
                '0',
                '../../../images/pastillegris.png',
                'MODULE_ID_LP'
         )", $code[0]);

	//	// For SURVEY module
	//	$langSurvey = "Έρευνα";
	//	db_query("INSERT IGNORE INTO accueil VALUES (
	//                21,
	//                '$langSurvey',
	//                '../../modules/survey/survey.php',
	//                '../../../images/survey.gif',
	//                '1',
	//                '0',
	//                '../../../images/pastillegris.png',
	//                'MODULE_ID_SURVEY'
	//         )", $code[0]);
	//
	//	// For POLL module
	//	$langPoll = "Ψηφοφορίες";
	//	db_query("INSERT IGNORE INTO accueil VALUES (
	//                22,
	//                '$langPoll',
	//                '../../modules/poll/poll.php',
	//                '../../../images/poll.gif',
	//                '1',
	//                '0',
	//                '../../../images/pastillegris.png',
	//                'MODULE_ID_POLL'
	//         )", $code[0]);

	// For QUESTIONNAIRE module (maybe remove old 21)
	$langQuestionnaire = "Ερωτηματολόγιο";
	db_query("INSERT IGNORE INTO accueil VALUES (
                21,																
                '$langQuestionnaire',
                '../../modules/questionnaire/questionnaire.php',
                '../../../images/questionnaire.gif',
                '1',
                '0',
                '../../../images/pastillegris.png',
                'MODULE_ID_QUESTIONNAIRE'
         )", $code[0]);

	// for usage module
	$langUsageModule = "Στατιστικά Χρήσης";
	db_query("INSERT IGNORE INTO accueil VALUES (
                '24',
                '$langUsageModule',
                '../../modules/usage/usage.php',
                '../../../images/usage.gif',
                '0',
                '1',
                '../../../images/pastillegris.png',
                'MODULE_ID_USAGE')", $code[0]);


	//for tool management
	$langToolManagement = "Διαχείριση εργαλείων";
	db_query("INSERT IGNORE INTO accueil VALUES (
                25,
                '$langToolManagement',
                '../../modules/course_tools/course_tools.php',
                '../../../images/course_tools.gif',
                '0',
                '1',
                '../../../images/pastillegris.png',
                'MODULE_ID_TOOLADMIN'
                )", $code[0]);




	//sakis - prosthiki epipleon pediwn ston pinaka documents gia ta metadedomena (xrhsimopoiountai apo ton neo file manager)
	if (!mysql_field_exists("$code[0]",'document','filename'))
	$tool_content .= add_field('document', 'filename', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','category'))
	$tool_content .= add_field('document', 'category', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','title'))
	$tool_content .= add_field('document', 'title', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','creator'))
	$tool_content .= add_field('document', 'creator', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','date'))
	$tool_content .= add_field('document', 'date', "DATETIME");

	if (!mysql_field_exists("$code[0]",'document','date_modified'))
	$tool_content .= add_field('document', 'date_modified', "DATETIME");

	if (!mysql_field_exists("$code[0]",'document','subject'))
	$tool_content .= add_field('document', 'subject', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','description'))
	$tool_content .= add_field('document', 'description', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','author'))
	$tool_content .= add_field('document', 'author', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','format'))
	$tool_content .= add_field('document', 'format', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','language'))
	$tool_content .= add_field('document', 'language', "TEXT");

	if (!mysql_field_exists("$code[0]",'document','copyrighted'))
	$tool_content .= add_field('document', 'copyrighted', "TEXT");


	// upgrade course documents directory
	$baseFolder = "$webDir/courses/".$code[0]."/document";
	$tmpfldr = getcwd();	

	if (!@chdir("$webDir/courses/".$code[0]."/document")) {
		die("Can't enter document course folder!");
	}

	//function gia anavathmisi twn arxeiwn se kathe course
	//*** proypothetei oti exei proepilegei h DB tou mathimatos kai pws to CWD einai o fakelos document tou mathimatos (p.x. .../eclass/courses/TMA100/document/) ***
	RecurseDir(getcwd(), $baseFolder);

	//epistrofh sto prohgoumeno getcwd()
	chdir($tmpfldr);


	//---------------------------------------------------------------------
	// Upgrading EXERCICES table for new func of EXERCISE module
	//---------------------------------------------------------------------
	if (!mysql_field_exists("$code[0]",'exercices','StartDate'))
	$tool_content .= add_field_after_field('exercices', 'StartDate', 'type', "DATETIME");
	if (!mysql_field_exists("$code[0]",'exercices','EndDate'))
	$tool_content .= add_field_after_field('exercices', 'EndDate', 'StartDate', "DATETIME");
	if (!mysql_field_exists("$code[0]",'exercices','TimeConstrain'))
	$tool_content .= add_field_after_field('exercices', 'TimeConstrain', 'EndDate', "INT(11)");
	if (!mysql_field_exists("$code[0]",'exercices','AttemptsAllowed'))
	$tool_content .= add_field_after_field('exercices', 'AttemptsAllowed', 'TimeConstrain', "INT(11)");
	// End of upgrading EXERCICES table for new func of EXERCISE module

	//---------------------------------------------------------------------
	// Upgrading VIDEO table for new func of VIDEO module
	//---------------------------------------------------------------------
	if (!mysql_field_exists("$code[0]",'video','creator'))
	$tool_content .= add_field_after_field('video', 'creator', 'description', "VARCHAR(255)");
	if (!mysql_field_exists("$code[0]",'video','publisher'))
	$tool_content .= add_field_after_field('video', 'publisher', 'creator',"VARCHAR(255)");
	if (!mysql_field_exists("$code[0]",'video','date'))
	$tool_content .= add_field_after_field('video', 'date', 'publisher',"DATETIME");
	if (!mysql_field_exists("$code[0]",'videolinks','creator'))
	$tool_content .= add_field_after_field('videolinks', 'creator', 'description', "VARCHAR(255)");
	if (!mysql_field_exists("$code[0]",'videolinks','publisher'))
	$tool_content .= add_field_after_field('videolinks', 'publisher', 'creator',"VARCHAR(255)");
	if (!mysql_field_exists("$code[0]",'videolinks','date'))
	$tool_content .= add_field_after_field('videolinks', 'date', 'publisher',"DATETIME");

	if (is_dir("$webDir/$code[0]/video"))

	{

		rename ("$webDir/$code[0]/video", "$webDir/video/$code[0]") or die ("Could not rename 
		directory"); 
	}
	// End of upgrading VIDEO table for new func of VIDEO module


	//table accueil -  create new column (define_var)
	$tool_content .= add_field("accueil","define_var", "VARCHAR(50) NOT NULL");
	$tool_content .= "Added field <i>define_var</i> to table <i>".$code[0]."accueil</i><br>";

	//set define string vars
	update_field("accueil", "define_var","MODULE_ID_AGENDA", "id", 		1);
	update_field("accueil", "define_var","MODULE_ID_LINKS", "id",	 	2);
	update_field("accueil", "define_var","MODULE_ID_DOCS", "id",	 	3);
	//id 4 is Video (this tool is to be removed)
	update_field("accueil", "define_var","MODULE_ID_ASSIGN", "id",		5);
	update_field("accueil", "define_var","MODULE_ID_VIDEO", "id",	 	6);//vinteoskophmena ma8hmata
	update_field("accueil", "define_var","MODULE_ID_ANNOUNCE", "id",	7);
	update_field("accueil", "define_var","MODULE_ID_USERS", "id", 		8);
	update_field("accueil", "define_var","MODULE_ID_FORUM", "id", 		9);
	update_field("accueil", "define_var","MODULE_ID_EXERCISE", "id", 	10);
	update_field("accueil", "define_var","MODULE_ID_STAT", "id", 		11);
	update_field("accueil", "define_var","MODULE_ID_IMPORT", "id", 		12);
	update_field("accueil", "define_var","MODULE_ID_EXTERNAL", "id",	13);
	update_field("accueil", "define_var","MODULE_ID_COURSEINFO", "id",	14);
	update_field("accueil", "define_var","MODULE_ID_GROUPS", "id", 		15);
	update_field("accueil", "define_var","MODULE_ID_DROPBOX", "id", 	16);

	update_field("accueil", "define_var","MODULE_ID_CHAT", "id", 		19);
	update_field("accueil", "define_var","MODULE_ID_DESCRIPTION", "id", 20);
	//	update_field("accueil", "define_var","MODULE_ID_SURVEY", "id", 21);
	//	update_field("accueil", "define_var","MODULE_ID_POLL", "id", 22);
	update_field("accueil", "define_var","MODULE_ID_QUESTIONNAIRE", "id", 21);
	update_field("accueil", "define_var","MODULE_ID_LP", "id", 			23);
	update_field("accueil", "define_var","MODULE_ID_USAGE", "id", 		24);
	update_field("accueil", "define_var","MODULE_ID_TOOLADMIN", "id", 	25);
	// table accueil
	$tool_content .= "Διόρθωση εγγραφών του πίνακα accueil<br>";
	if (db_query("UPDATE accueil SET lien='../../modules/agenda/agenda.php' WHERE id=1", $code[0])) {
		$tool_content .= "Εγγραφή με id 1 του πίνακα <b>accueil</b> $OK<br>";
	} else {
		$tool_content .= "Εγγραφή με id 1 του πίνακα <b>accueil</b>: $BAD<br>";
		$errors++;
	}

	$sql = db_query("SELECT id,lien,image,address FROM accueil");
	while ($u = mysql_fetch_row($sql))  {
		$oldlink_lien = $u[1];
		$newlink_lien = preg_replace('#../claroline/#','../../modules/',$oldlink_lien);
		$oldlink_image = $u[2];
		$newlink_image = preg_replace('#../claroline/image/#','../../images/',$oldlink_image);
		$oldlink_address = $u[3];
		$newlink_address = preg_replace('#../claroline/image/#','../../images/',$oldlink_address);
		if	(db_query("UPDATE accueil
                            SET lien='$newlink_lien', image='$newlink_image', address='$newlink_address'
                    WHERE id='$u[0]'")) {
		$tool_content .= "Εγγραφή με id $u[0] του πίνακα <b>accueil</b>: $OK<br>";
                    } else {
                    	$tool_content .= "Εγγραφή με id $u[0] του πίνακα <b>accueil</b>: $BAD<br>";
                    	$errors++;
                    }
	}

	//Move Users module from public to course-admin tools
	$sql = 'UPDATE `accueil` SET `visible` = \'0\', `admin` = \'1\' WHERE `id` = 8 LIMIT 1';
	db_query($sql, $code[0]);

	// update menu entries with new messages
	$langModifyInfoNew = "Διαχείριση Μαθήματος";
	$langAddPageHomeNew= "Ανέβασμα Ιστοσελίδας";
	update_field("accueil", "rubrique", "$langModifyInfoNew", "id", 14);
	update_field("accueil", "rubrique", "$langAddPageHomeNew", "id", 12);

	//set the new images for the icons of lesson modules
	update_field("accueil", "image","calendar", "id", 		1);
	update_field("accueil", "image","links", "id",	 		2);
	update_field("accueil", "image","docs", "id",	 		3);
	//id 4 is Video (this tool is to be removed)
	update_field("accueil", "image","assignments", "id",	5);
	update_field("accueil", "image","video", "id",	 		6);//vinteoskophmena ma8hmata
	update_field("accueil", "image","announcements", "id",	7);
	update_field("accueil", "image","users", "id", 			8);
	update_field("accueil", "image","forum", "id", 			9);
	update_field("accueil", "image","exercise", "id", 		10);
	update_field("accueil", "image","stat", "id", 			11);
	//update_field("accueil", "image","import", "id", 		12);
	//update_field("accueil", "image","external", "id",		13);
	update_field("accueil", "image","course_info", "id",	14);
	update_field("accueil", "image","groups", "id", 		15);
	update_field("accueil", "image","dropbox", "id", 		16);

	update_field("accueil", "image","chat", "id", 			19);
	update_field("accueil", "image","description", "id",	20);
	//	update_field("accueil", "image","survey", "id", 		21);
	//	update_field("accueil", "image","poll", "id", 			22);
	update_field("accueil", "image","questionnaire", "id", 			21);
	update_field("accueil", "image","lp", "id", 			23);
	update_field("accueil", "image","usage", "id", 			24);
	update_field("accueil", "image","tooladmin", "id", 		25);

	//remove modules import & external (now a part of tool admin)
	$sql = 'DELETE FROM `accueil` WHERE (`id` = 12 OR `id` = 13)';
	db_query($sql, $code[0]);

	// table stat_accueil
	$sql = db_query("SELECT id,request FROM stat_accueil");
	while ($u = mysql_fetch_row($sql))  {
		$old_request = $u[1];
		$new_request = preg_replace('#'.$code[0].'/#','courses/'.$code[0].'/', $old_request);
		if (db_query("UPDATE stat_accueil SET request='$new_request' WHERE id = '$u[0]'")) {
			$tool_content .= "Εγγραφή με id $u[0] του πίνακα <b>stat_accueil</b>: $OK<br>";
		} else {
			$tool_content .= "Εγγραφή με id $u[0] του πίνακα <b>stat_accueil</b>: $BAD<br>";
			$errors++;
		}
	}

	// remove table 'introduction' entries and insert them in table 'cours' (field 'description') in eclass main database
	// after that drop table introduction
	if (mysql_table_exists($code[0], 'introduction')) {
		$sql = db_query("SELECT texte_intro FROM introduction", $code[0]);
		while ($text = mysql_fetch_array($sql)) {
			if (db_query("UPDATE cours SET description='$text[0]' WHERE code='$code[0]'", $mysqlMainDb)) {
				$tool_content .= "Μεταφορά του εισαγωγικού κειμένου <b>$text[0]</b> στον πίνακα <b>cours</b>: $OK<br>";
				db_query("DROP TABLE introduction", $code[0]);
			} else {
				$tool_content .= "Μεταφορά του εισαγωγικού κειμένου <b>$text[0]</b> στον πίνακα <b>cours</b>: $BAD<br>";
				$errors++;
			}
		}
	} // end of table introduction
	
// remove table 'cours_description' entries and insert them in table 'cours' 
// after that drop table cours_description
/*
if (mysql_table_exists($code[0], 'course_description')) {
	// description
	$sql = db_query("SELECT content FROM course_description WHERE id='0'", $code[0]);
	while ($cdesc = mysql_fetch_array($sql))  
		db_query("UPDATE cours SET description='$cdesc[0]' WHERE code='$code[0]'", $mysqlMainDb)); 
	$sql = db_query("SELECT content FROM course_description WHERE id='1'", $code[0]);
	while ($cdesc = mysql_fetch_array($sql))  
		db_query("UPDATE cours SET cours_objectives='$cdesc[0]' WHERE code='$code[0]'", $mysqlMainDb); 
	$sql = db_query("SELECT content FROM course_description WHERE id='2'", $code[0]);
	while ($cdesc = mysql_fetch_array($sql))  
		db_query("UPDATE cours SET cours_prerequisites='$cdesc[0]' WHERE code='$code[0]'", $mysqlMainDb); 
	$sql = db_query("SELECT content FROM course_description WHERE id='3'", $code[0]);
	while ($cdesc = mysql_fetch_array($sql))  
		db_query("UPDATE cours SET course_keywords='$cdesc[0]' WHERE code='$code[0]'", $mysqlMainDb); 
	$sql = db_query("SELECT content FROM course_description WHERE id='4'", $code[0]);
	while ($cdesc = mysql_fetch_array($sql)) 
		db_query("UPDATE cours SET course_references='$cdesc[0]' WHERE code='$code[0]'", $mysqlMainDb); 

	db_query("DROP TABLE course_description", $code[0]);	

} // end of table 'cours_description'
	
	*/

$tool_content .= "<br><br></td></tr>";

// add full text indexes for search operation
db_query("ALTER TABLE `agenda` ADD FULLTEXT `agenda` (`titre` ,`contenu`)");
db_query("ALTER TABLE `course_description` ADD FULLTEXT `course_description` (`title` ,`content`)");
db_query("ALTER TABLE `document` ADD FULLTEXT `document` (`filename` ,`comment` ,`title`,`creator`,
`subject`,`description`,`author`,`language`)");
db_query("ALTER TABLE `exercices` ADD FULLTEXT `exercices` (`titre`,`description`)");
db_query("ALTER TABLE `posts_text` ADD FULLTEXT `posts_text` (`post_text`)");
db_query("ALTER TABLE `liens` ADD FULLTEXT `liens` (`url` ,`titre` ,`description`)");
db_query("ALTER TABLE `video` ADD FULLTEXT `video` (`url` ,`titre` ,`description`)");
//db_query("ALTER TABLE `videolinks` ADD FULLTEXT `videolinks` (`url` ,`titre` ,`description`)");

// bogart: Update code for phpbb functionality START
// Remove tables banlist, disallow, headermetafooter, priv_msgs, ranks, sessions, themes, whosonline, words
db_query("DROP TABLE IF EXISTS access");
db_query("DROP TABLE IF EXISTS banlist");
db_query("DROP TABLE IF EXISTS config");
db_query("DROP TABLE IF EXISTS disallow");
db_query("DROP TABLE IF EXISTS forum_access");
db_query("DROP TABLE IF EXISTS forum_mods");
db_query("DROP TABLE IF EXISTS headermetafooter");
db_query("DROP TABLE IF EXISTS priv_msgs");
db_query("DROP TABLE IF EXISTS ranks");
db_query("DROP TABLE IF EXISTS sessions");
db_query("DROP TABLE IF EXISTS themes");
db_query("DROP TABLE IF EXISTS whosonline");
db_query("DROP TABLE IF EXISTS words");
// bogart: Update code for phpbb functionality END

} // End of 'while' courses

// Fixed by vagpits
mysql_select_db($mysqlMainDb);

$tool_content .= "<tr><td align=\"center\"><b><i>Σφάλματα: $errors.</i></b></td></tr>";
$tool_content .= "</tbody></table><br>";
$tool_content .= "<table width=\"99%\"><caption>Αποτελέσματα Αναβάθμισης</caption><tbody><tr><td>
";

// display info message
if ($errors==0) {
	$tool_content .= upgrade_success();
} else {
	$tool_content .= upgrade_failure();
}
$tool_content .= "</td></tr></tbody></table>";
if ($fromadmin)
$tool_content .= "<br><center><p><a href=\"../modules/admin/index.php\">Επιστροφή</a></p></center>";
else
$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

if ($fromadmin)
draw($tool_content,3, 'admin');
else
draw($tool_content,0);


//-----------------------------
// end of main script
// ---------------------------


// ----------------------------------------------------------------
// Function list
// ----------------------------------------------------------------


//function to update a field in a table
function update_field($table, $field, $field_name, $id_col, $id) {
	$sql = "UPDATE `$table` SET `$field` = '$field_name' WHERE `$id_col` = $id;";
	db_query($sql);
}

// Removes initial part of path from assignment_submit.file_path
function update_assignment_submit()
{
	$updated = FALSE;
	$q = db_query('SELECT id, file_path FROM assignment_submit');
	if ($q) {
		while ($i = mysql_fetch_array($q)) {
			$new = preg_replace('+^.*/work/+', '', $i['file_path']);
			if ($new != $i['file_path']) {
				db_query("UPDATE assignment_submit SET file_path = " .
				quote($new) . " WHERE id = $i[id]");
				$updated = TRUE;
			}
		}
	}
	if ($updated) {
		echo "Πίνακας assignment_submit: $GLOBALS[OK]<br>\n";
	}
}


// Adds field $field to table $table of current database, if it doesn't already exist
function add_field($table, $field, $type)
{
	global $OK, $BAD;

	$retString = "";
	$retString .= "Προσθήκη πεδίου <b>$field</b> στον πίνακα <b>$table</b>: ";
	$fields = db_query("SHOW COLUMNS FROM $table LIKE '$field'");
	if (mysql_num_rows($fields) == 0) {
		if (db_query("ALTER TABLE `$table` ADD `$field` $type")) {
			$retString .= " $OK<br>";
		} else {
			$retString .= " $BAD<br>";
			$GLOBALS['errors']++;
		}
	} else {
		$retString .= "Υπάρχει ήδη. $OK<br>";
	}

	return $retString;
}

function add_field_after_field($table, $field, $after_field, $type)
{
	global $OK, $BAD;

	$retString = "";
	$retString .= "Προσθήκη πεδίου <b>$field</b> μετά το πεδίο <b>$after_field</b> στον πίνακα <b>$table</b>: ";
	$fields = db_query("SHOW COLUMNS FROM $table LIKE '$field'");
	if (mysql_num_rows($fields) == 0) {
		if (db_query("ALTER TABLE `$table` ADD COLUMN `$field` $type AFTER `$after_field`")) {
			$retString .= " $OK<br>";
		} else {
			$retString .= " $BAD<br>";
			$GLOBALS['errors']++;
		}
	} else {
		$retString .= "Υπάρχει ήδη. $OK<br>";
	}

	return $retString;
}
function rename_field($table, $field, $new_field, $type)
{
	global $OK, $BAD;
	$retString = "";
	$retString .= "Μετονομασία πεδίου <b>$field</b> σε <b>$new_field</b> στον πίνακα <b>$table</b>: ";
	$fields = db_query("SHOW COLUMNS FROM $table LIKE '$new_field'");
	if (mysql_num_rows($fields) == 0) {
		if (db_query("ALTER TABLE `$table` CHANGE  `$field` `$new_field` $type")) {
			$retString .= " $OK<br>";
		} else {
			$retString .= " $BAD<br>";
			$GLOBALS['errors']++;
		}
	} else {
		$retString .= "Υπάρχει ήδη. $OK<br>";
	}
	return $retString;
	

}

function delete_table($table)
{
	global $OK, $BAD;
	$retString = "";
	$retString .= "Διαγραφή πίνακα <b>$table</b>: ";
	if (db_query("DROP TABLE $table")) {
		$retString .= " $OK<br>";
	} else {
		$retString .= " $BAD<br>";
		$GLOBALS['errors']++;
	}
	return $retString;
}

function merge_tables($table_destination,$table_source,$fields_destination,$fields_source)
{
	global $OK, $BAD;
	$retString = "";
	$retString .= " Ενοποίηση των πινάκων <b>$table_destination</b>,<b>$table_source</b>";
	$query = "INSERT INTO $table_destination (";
	foreach($fields_destination as $val)
	{
			$query.=$val.",";
		}
	$query=substr($query,0,-1).") SELECT ";
	foreach($fields_source as $val)
	{
			$query.=$val.",";
		}
	$query=substr($query,0,-1)." FROM ".$table_source;
		if (db_query($query)) {
			$retString .= " $OK<br>";
		} else {
			$retString .= " $BAD<br>";
			$GLOBALS['errors']++;
		}
	
	return $retString;

}

// checks if a mysql table exists
function mysql_table_exists($db, $table)
{
	$exists = mysql_query('SHOW TABLES FROM `'.$db.'` LIKE \''.$table.'\'');
	return mysql_num_rows($exists) == 1;
}

// checks if a mysql table field exists

function mysql_field_exists($db,$table,$field)
{
	$fields = db_query("SHOW COLUMNS from $table LIKE '$field'",'$db');
	if (mysql_num_rows($fields) > 0)
	return TRUE;

}

// checks if admin user

function is_admin($username, $password)
{
	$r = db_query("SELECT * FROM user, admin WHERE admin.idUser = user.user_id
            AND user.username = '$username' AND user.password = '$password'");
	if (mysql_num_rows($r) == 0) {
		return FALSE;
	} else {
		return TRUE;
	}
}


// error
function upgrade_failure () {
	$retString = "";
	$retString .= "<b>Παρουσιάστηκαν κάποια σφάλματα κατά την αναβάθμιση των βάσεων δεδομένων του e-Class!</b><br><br>";
	$retString .= "Πιθανόν κάποιο μάθημα να μην δουλεύει τελείως σωστά. Μπορείτε να επικοινωνήστε μαζί
    μας στο <a href='mailto:elearn@noc.uoa.gr'>elearn@noc.uoa.gr</a>
    περιγράφοντας το πρόβλημα που παρουσιάστηκε και στέλνοντας (αν είναι δυνατόν)
    όλα τα μυνήματα που εμφανίστηκαν στην οθόνη σας.";

	return $retString;
}

// success
function upgrade_success () {
	$retString = "";
	$retString .= "<b>Η αναβάθμιση των βάσεων δεδομένων του e-Class πραγματοποιήθηκε με επιτυχία!</b><br><br>";
	$retString .= "Είστε πλέον έτοιμοι να χρησιμοποιήσετε την καινούρια έκδοση του e-Class.";

	return $retString;
}

?>

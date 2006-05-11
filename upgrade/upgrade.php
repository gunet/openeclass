<?

include '../include/init.php';

$nameTools = "Αναβάθμιση των βάσεων δεδομένων του e-Class";

$OK = "[<font color='green'> Επιτυχία </font>]";
$BAD = "[<font color='red'> Σφάλμα ή δεν χρειάζεται τροποποίηση</font>]";

begin_page();

if (isset($_POST['login']) and isset($_POST['password']) and !is_admin($_POST['login'], $_POST['password'])) {
	echo "<p>Τα στοιχεία που δώσατε δεν αντιστοιχούν στο διαχειριστή του
		συστήματος! Παρακαλούμε επιστρέψτε στην προηγούμενη σελίδα και ξαναδοκιμάστε.</p>
		<center><a href=\"index.php\">Επιστροφή</a></center>";
	end_page();
	exit;
}

$errors = 0;

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

//upgrade queries from 1.2 --> 1.4

if (!mysql_field_exists("$mysqlMainDb", 'user', 'am')) 
	add_field('user', 'am', "VARCHAR( 20 ) NOT NULL");
if (mysql_table_exists($mysqlMainDb, 'todo'))
	db_query("DROP TABLE `todo`");


// upgrade queries to 1.4

if (!mysql_field_exists("$mysqlMainDb",'cours','type'))
	add_field('cours', 'type', "ENUM('pre', 'post', 'other') DEFAULT 'pre' NOT NULL");
if (!mysql_field_exists("$mysqlMainDb",'cours','doc_quota'))
	add_field('cours', 'doc_quota', "FLOAT DEFAULT '$diskQuotaDocument' NOT NULL");
if (!mysql_field_exists("$mysqlMainDb",'cours','video_quota'))
	add_field('cours', 'video_quota', "FLOAT DEFAULT '$diskQuotaVideo' NOT NULL");
if (!mysql_field_exists("$mysqlMainDb",'cours','group_quota'))
	add_field('cours', 'group_quota', "FLOAT DEFAULT '$diskQuotaGroup' NOT NULL");

// upgrade query to 1.6
if (!mysql_field_exists("$mysqlMainDb",'cours','dropbox_quota'))
	add_field('cours', 'dropbox_quota', "FLOAT DEFAULT '$diskQuotaDropbox' NOT NULL");


// *************************************
// 		upgrade courses databases 
// ************************************


$res = db_query("SELECT code FROM cours");
while ($code = mysql_fetch_row($res)) {

// modify course_code/index.php

echo "<p><h4>Τροποποίηση αρχείου index.php του μαθήματος $code[0]</h4></p>";
if (!chdir("$webDir/courses/$code[0]")) {
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

echo "<p><h4>Αναβάθμιση μαθήματος $code[0]</h4></p>";
mysql_select_db($code[0]);

	// upgrade queries from 1.2 --> 1.4

	if (!mysql_field_exists('$code[0]','exercices','type')) 
		add_field('exercices','type',"TINYINT( 4 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `description`");	
	if (!mysql_field_exists('$code[0]','exercices','random')) 
		add_field('exercices','random',"SMALLINT( 6 ) DEFAULT '0' NOT NULL AFTER `type`");	
	if (!mysql_field_exists('$code[0]','reponses','ponderation')) 
		add_field('reponses','ponderation',"SMALLINT( 5 ) NOT NULL AFTER `comment`");	

	$s = db_query("SELECT type FROM questions",$code[0]);
	while ($f = mysql_fetch_row($s)) {
		if (empty($f[0]))  {	
			if (db_query("UPDATE `questions` SET type=1",$code[0]))  {
				echo "Πίνακας questions: $OK<br>";
			} else {
				echo "Πίνακας questions: $BAD<br>";
				$errors++;
			}	
		}
	} // while

	echo "</p>\n";

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
			echo "Πίνακας accueil: $OK<br>";
	} else {
			echo "Πίνακας accueil: $BAD<br>";
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
add_field('liens','category',"INT(4) DEFAULT '0' NOT NULL");
add_field('liens','ordre',"MEDIUMINT(8) DEFAULT '0' NOT NULL");

if (!mysql_table_exists($code[0], 'link_categories'))  {
	db_query("CREATE TABLE `link_categories` (
  		`id` int(6) NOT NULL auto_increment,
   		`categoryname` varchar(255) default NULL,
  		`description` text,
		  `ordre` mediumint(8) NOT NULL default '0',
		  PRIMARY KEY  (`id`))",$code[0]);	
	}

// correct link entries to correctly appear in a blank window
$legend = "<p>Διόρθωση συνδέσμων</p>";
echo $legend;
echo "<br>";

$sql = db_query("SELECT url FROM `liens` WHERE url REGEXP '\"target=_blank$'");
while ($u = mysql_fetch_row($sql))  {
        $temp = $u[0];
        $newurl = preg_replace('#\s*"target=_blank#','',$temp);
        echo "<b>Παλιός σύνδεσμος: </b>";
        echo  $temp;
        echo "<br>";
        echo "<b>Καινούριος σύνδεσμος: </b>";
        echo $newurl;
        echo "<br>";
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
                '16',
                '$langDropbox',
                '../../modules/dropbox/index.php',
                '../../../images/dropbox.png',
                '0',
                '0',
                '../../../images/pastillegris.png'
                )", $code[0]);


// upgrade queries for eclass 2.0
// for learning path

$langLearnPath = "Γραμμή Μάθησης";
if (!mysql_table_exists($code[0], 'lp_module'))  {
	db_query("CREATE TABLE `lp_module` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default '0',
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL','COURSE_DESCRIPTION') NOT NULL,
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

db_query("INSERT IGNORE INTO accueil VALUES (
				'21',
				'$langLearnPath',
				'../../modules/learnPath/learningPathList.php',
				'../../../images/learnpath.gif',
				'1',
				'0',
				'../../../images/pastillegris.png'
                )", $code[0]);


//for tool management
$langToolManagement = "Διαχείριση εργαλείων";
db_query("INSERT IGNORE INTO accueil VALUES (
				'22',
				'$langToolManagement',
				'../../modules/course_tools/course_tools.php',
				'../../../images/course_tools.gif',
				'0',
				'1',
				'../../../images/pastillegris.png'
                )", $code[0]);


// table accueil
echo "Διόρθωση εγγραφών του πίνακα accueil<br>";
	if (db_query("UPDATE accueil SET lien='../../modules/agenda/agenda.php' WHERE id=1", $code[0])) {
			echo "Εγγραφή με id 1 του πίνακα <b>accueil</b> $OK<br>";
	} else {
			echo "Εγγραφή με id 1 του πίνακα <b>accueil</b>: $BAD<br>";
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
					echo "Εγγραφή με id $u[0] του πίνακα <b>accueil</b>: $OK<br>";
				} else {
					echo "Εγγραφή με id $u[0] του πίνακα <b>accueil</b>: $BAD<br>";
					$errors++;
				}
}
																																				
// table stat_accueil
$sql = db_query("SELECT id,request FROM stat_accueil");
  while ($u = mysql_fetch_row($sql))  {
	    $old_request = $u[1];
			$new_request = preg_replace('#'.$code[0].'/#','courses/'.$code[0].'/', $old_request);
		  if (db_query("UPDATE stat_accueil SET request='$new_request' WHERE id = '$u[0]'")) {
						echo "Εγγραφή με id $u[0] του πίνακα <b>stat_accueil</b>: $OK<br>";
					} else {
						echo "Εγγραφή με id $u[0] του πίνακα <b>stat_accueil</b>: $BAD<br>";
						$errors++;
			}
echo "<br>";
}
																																		
echo "</p>\n";
} // end of do ... while for each course)

echo "<p>Σφάλματα: $errors.";
echo "<br><center><a href='../index.php'>Επιστροφή</a></center></p>";

end_page();

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
	echo "<p>Προσθήκη πεδίου <b>$field</b> στον πίνακα <b>$table</b>: ";
	$fields = db_query("SHOW COLUMNS FROM $table LIKE '$field'");
	if (mysql_num_rows($fields) == 0) {
		if (db_query("ALTER TABLE `$table` ADD `$field` $type")) {
			echo " $OK</p>";
		} else {
			echo " $BAD</p>";
			$GLOBALS['errors']++;
		}
	} else {
		echo "Υπάρχει ήδη. $OK</p>";
	}
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
	echo "<p><center><h4>Παρουσιάστηκαν κάποια σφάλματα κατά την αναβάθμιση των βάσεων δεδομένων του e-Class!</h4></center></p>";
	echo "<p><h4>Πιθανόν κάποιο μάθημα να μην δουλεύει τελείως σωστά. Μπορείτε να επικοινωνήστε μαζί 
	μας στο <a href='mailto:elearn@noc.uoa.gr'>elearn@noc.uoa.gr</a> 
	περιγράφοντας το πρόβλημα που παρουσιάστηκε και στέλνοντας (αν είναι δυνατόν) 
	όλα τα μυνήματα που εμφανίστηκαν στην οθόνη σας.</h4>";
}

// success
function upgrade_success () {
	echo "<p><center><h4>Η αναβάθμιση των βάσεων δεδομένων του e-Class πραγματοποιήθηκε με επιτυχία!</h4></center></p>";
	echo "<p><center><h4>Είστε πλέον έτοιμοι να χρησιμοποιήσετε την καινούρια έκδοση του e-Class.</h4></center></p>";
}

?>

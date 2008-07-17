<?php
if (isset($c) && ($c!="")) {
	$require_admin = TRUE;
	$dbname = $c;
	session_register("dbname");
}
$require_current_course = TRUE;
$require_prof;
include '../../include/baseTheme.php';

$nameTools = $langArchiveCourse;
$navigation[] = array("url" => "infocours.php", "name" => $langModifInfo);
$tool_content = ""; 
$archiveDir = "/courses/archive"; 
 
if (extension_loaded("zlib")) {
	include("../../include/pclzip/pclzip.lib.php");
}
		
// check if you are admin
if($is_adminOfCourse) {
	$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\"><li>";
	if (isset($c) && ($c!="")) {
		if (isset($search) && ($search=="yes")) $searchurl = "&search=yes";
		else $searchurl = "";
		$tool_content .= "<a href=\"../admin/editcours.php?c=".$c."".$searchurl."\">$langBack</a></li>";
	} else {
		$tool_content .= "<a href=\"infocours.php\">$langBack</a></li>";
	}
	
	$tool_content .= "</ul></div>";	
	$dateBackuping  = date("Y-m-d-H-i-(B)-s");
	$shortDateBackuping  = date("YzBs"); // YEAR - Day in Year - Swatch - second 
	$archiveDir .= "/".$currentCourseID."/".$dateBackuping;
	$zipfile = $webDir."courses/archive/$currentCourseID/archive.$currentCourseID.$shortDateBackuping.zip";
	$tool_content .= "<table class='Deps' align='center'><tbody><tr><th align=\"left\"><ol>\n";

	// if dir is missing, first we create it. mkpath is a recursive function
        $dirCourBase = realpath("../..").$archiveDir."/courseBase";
	if (!is_dir($dirCourBase)) {
		$tool_content .= "<li>".$langCreateDirCourseBase."</li>\n";
		mkpath($dirCourBase);
	}
	
	create_backup_file("$webDir/$archiveDir/backup.php");

    	$dirMainBase = realpath("../..").$archiveDir."/mainBase";
	if (!is_dir($dirMainBase)) {
		$tool_content .= "<li>".$langCreateDirMainBase."</li>\n";
		mkpath($dirMainBase);
	}
    	$dirhtml = realpath("../..").$archiveDir."/html";

// ********************************************************************
// Copy of  from DB main
// fields about this course
// ********************************************************************
//  info  about cours
// ********************************************************************
	$tool_content .= "<li>".$langBUCourseDataOfMainBase."  ".$currentCourseID."</li>\n";
	$sqlInsertCourse = "INSERT INTO cours SET ";
	$sqlSelectInfoCourse ="SELECT * FROM `$mysqlMainDb`.cours WHERE code = '".$currentCourseID."' ";
	$resInfoCourse = mysql_query($sqlSelectInfoCourse) ;
	$infoCourse = mysql_fetch_array($resInfoCourse);
	for($noField=0; $noField<mysql_num_fields($resInfoCourse); $noField++) {
		if ($noField>0)
			$sqlInsertCourse .= ", ";
		$nameField = mysql_field_name($resInfoCourse,$noField);
		$sqlInsertCourse .= "$nameField = '".$infoCourse["$nameField"]."'";
	}
	$sqlInsertCourse .= ";";

	$fcoursql = fopen(realpath("../..").$archiveDir."/mainBase/cours.sql", "w");
	fwrite($fcoursql, $sqlInsertCourse); 
	fclose($fcoursql);
	
// ********************************************************************
//  info  about users
// ********************************************************************
	$tool_content .= "<li>".$langBUUsersInMainBase." ".$currentCourseID."</li>\n";
	
	$sqlUserOfTheCourse ="SELECT user.* FROM `$mysqlMainDb`.user, `$mysqlMainDb`.cours_user
		WHERE user.user_id=cours_user.user_id
		AND cours_user.code_cours='$currentCourseID'";

		$resUsers = mysql_query($sqlUserOfTheCourse,$db);
		$nbFields = mysql_num_fields($resUsers);
		$sqlInsertUsers = "";

// creation of body
		while($users = mysql_fetch_array($resUsers))
		{
			$sqlInsertUsers .= "INSERT IGNORE INTO user SET ";
			for($noField=0; $noField < $nbFields; $noField++)
			{
				if ($noField>0)
					$sqlInsertUsers .= ", ";
				$nameField = mysql_field_name($resUsers,$noField);
				$sqlInsertUsers .= "$nameField = '".$users["$nameField"]."' ";
			}
			$sqlInsertUsers .= ";";
		}
	$fuserssql = fopen(realpath("../..").$archiveDir."/mainBase/users.sql", "w");
	fwrite($fuserssql, $sqlInsertUsers); 
	fclose($fuserssql);
	

/*  End  of  backup user */

// ********************************************************************
//  info  about announcment
// ********************************************************************
	$tool_content .= "<li>".$langBUAnnounceInMainBase." ".$currentCourseID."</li>\n";
	$sqlAnnounceOfTheCourse ="SELECT a.* FROM  `$mysqlMainDb`.annonces a WHERE a.code_cours='$currentCourseID'";
		$resAnn = mysql_query($sqlAnnounceOfTheCourse,$db);
		$nbFields = mysql_num_fields($resAnn);
		$sqlInsertAnn = "";

// creation of body
		while($announce = mysql_fetch_array($resAnn)) {
			$sqlInsertAnn .= "INSERT INTO users SET ";
			for($noField=0; $noField < $nbFields; $noField++) {
				if ($noField>0)
					$sqlInsertAnn .= ", ";
				$nameField = mysql_field_name($resAnn,$noField);
				$sqlInsertAnn .= "$nameField = '".addslashes($announce["$nameField"])."' ";	
			}
			$sqlInsertAnn .= ";";
		}

	$fannsql = fopen(realpath("../..").$archiveDir."/mainBase/annonces.sql", "w");
	fwrite($fannsql, $sqlInsertAnn); 
	fclose($fannsql);
	

/*  End  of  backup Annonces */

	// we can copy file of course
	$tool_content .= "<li>".$langCopyDirectoryCourse."<br>(";
	$nbFiles = copydir(realpath("../../courses/".$currentCourseID."/"), $dirhtml);
	$tool_content .= "<strong>".$nbFiles."</strong> ".$langFileCopied.")</li>\n";

// ********************************************************************
// Copy of  DB course
// with mysqldump
// ********************************************************************
	$tool_content .= "<li>".$langBackupOfDataBase." ".$currentCourseID."  (SQL)<br>(";
	$tool_content .= backupDatabase($db , $currentCourseID , true, true , 'SQL' , realpath("../..".$archiveDir."/courseBase/"),true);

// ********************************************************************
// Copy of DB course
// with mysqldump
// ********************************************************************

	$tool_content .= ")</li></ol></th><td>&nbsp;</td></tr></tbody></table>";

//-----------------------------------------
// create zip file
// ----------------------------------------

	$zipCourse = new PclZip($zipfile);
	if ($zipCourse->create($webDir.$archiveDir, PCLZIP_OPT_REMOVE_PATH, "$webDir") == 0) {
		$tool_content .= "Error: ".$zipCourse->errorInfo(true);
		draw($tool_content, 2, 'course_info');
		exit;
	} else { 		
		$tool_content .= "<br /><table width='99%'><tbody>
   		<tr><td class='success' width='1'></td>
     		<td class='left'><b>$langBackupSuccesfull</b></td><td><div align='right'>
    		<a href='$urlServer/courses/archive/$currentCourseID/archive.$currentCourseID.$shortDateBackuping.zip'>$langDownloadIt</a>
		</div></td>
        	<td width='1'><img src='../../template/classic/img/download.gif' title='$langDownloadIt' width='30' height='29'></td></tr>
    		</tbody></table><br>";
	}
	draw($tool_content, 2, 'course_info');	
}	// end of isadminOfCourse
else 
{
	$tool_content .= "<center><p>$langNotAllowed</p></center>";
	draw($tool_content, 2, 'course_info');
	exit;
}

// ---------------------------------------------
// useful functions
// ---------------------------------------------

function DirSize($path , $recursive=TRUE) { 
	$result = 0; 
	if(!is_dir($path) || !is_readable($path)) 
   		return 0; 
	$fd = dir($path); 
	while($file = $fd->read())
	{ 
	   	if(($file != ".") && ($file != "..")) { 
    	if (@is_dir("$path$file/")) 
 		$result += $recursive?DirSize("$path$file/"):0; 
    	else  
		$result += filesize("$path$file"); 
		} 
	}
	$fd->close(); 
	return $result; 
} 

/* 
 * Backup a db to a file 
 */ 
function backupDatabase($link , $db_name , $structure , $donnees , $format="SQL" , $whereSave=".", $insertComplet="")
{ 
	global $langBackupEnd;
	if (!is_resource($link)) 
		return false; 
	mysql_select_db($db_name); 
	$format = strtolower($format); 
	$filename = $whereSave."/courseDbContent.".$format; 
	$format = strtoupper($format); 
	$fp = fopen($filename, "w"); 
	if (!is_resource($fp)) 
		return false; 
	$res = mysql_list_tables($db_name, $link); 
	$num_rows = mysql_num_rows($res); 
	$i = 0; 
	while ($i < $num_rows) { 
		$tablename = mysql_tablename($res, $i); 
		if ($structure === true) 
		{ 
			fwrite($fp, "DROP TABLE IF EXISTS `$tablename`;\n"); 
			$query = "SHOW CREATE TABLE $tablename"; 
			$resCreate = mysql_query($query); 
			$row = mysql_fetch_array($resCreate); 
			$schema = $row[1].";"; 
			fwrite($fp, "$schema\n\n"); 
		} 
		if ($donnees === true) 
		{ 
			$query = "SELECT * FROM $tablename"; 
			$resData = mysql_query($query); 
			if (mysql_num_rows($resData) > 0) 
			{ 
				$sFieldnames = ""; 
				if ($insertComplet === true) 
				{ 
					$num_fields = mysql_num_fields($resData); 
					for($j=0; $j < $num_fields; $j++) 
					{ 
						$sFieldnames .= "`".mysql_field_name($resData, $j)."`, "; 
					} 
					$sFieldnames = "(".substr($sFieldnames, 0, -2).")"; 
				} 
				$sInsert = "INSERT INTO `$tablename` $sFieldnames values "; 
				while($rowdata = mysql_fetch_assoc($resData)) 
				{ 
					$lesDonnees = "<guillemet>".implode("<guillemet>,<guillemet>", $rowdata)."<guillemet>"; 
					$lesDonnees = str_replace("<guillemet>", "'",addslashes($lesDonnees)); 
					if ($format == "SQL") 
					{ 
						$lesDonnees = $sInsert." ( ".$lesDonnees." );"; 
					} 
					fwrite($fp, "$lesDonnees\n"); 
				} 
			} 
		} 
		$i++; 
	} 
	return "$langBackupEnd $format";
	fclose($fp); 
}

function copydir($origine, $destination) {
	
	$dossier=opendir($origine);
	if (file_exists($destination))
	{ 
		return 0;
	}
	mkdir($destination, 0755);
	$total = 0;

	while ($fichier = readdir($dossier)) 
	{
		$l = array('.', '..');
		if (!in_array( $fichier, $l))
		{
			if (is_dir($origine."/".$fichier))
			{
				$total += copydir("$origine/$fichier", "$destination/$fichier");
			} 
			else 
			{
				copy("$origine/$fichier", "$destination/$fichier");
				$total++;
			}
		}
	}
	return $total;
}



// adia function
function create_backup_file($file) {
	global $currentCourseID;

	$f = fopen($file,"w");
	if (!$f) {
		die("Error! Unable to open output file: '$f'\n");
	}
	fputs($f, "<?\n");
	backup_course_details($f, $currentCourseID);
	backup_annonces($f, $currentCourseID);
	backup_users($f, $currentCourseID);
	backup_course_db($f, $currentCourseID);
	fputs($f, "?>\n");
	fclose($f);
}

function backup_annonces($f, $course) {
	global $mysqlMainDb;
	
	$res = mysql_query("SELECT * FROM `$mysqlMainDb`.annonces
				    WHERE code_cours = '$course'");
	while($q = mysql_fetch_array($res)) {
		fputs($f, "announcement(".
			quote($q['contenu']).",\n".
			quote($q['temps']).", ".
			quote($q['ordre']).");\n");
	}
}

function backup_groups($f) {
	$res = mysql_query("SELECT * FROM user_group");
		while($row = mysql_fetch_assoc($res)) {
		fputs($f, "group(".
			$row['user'].", ".
			$row['team'].", ".
			$row['status'].", ".
			quote($row['role']).");\n");
	}
}

function backup_assignment_submit($f) {
	$res = mysql_query("SELECT * FROM assignment_submit");
		while($row = mysql_fetch_assoc($res)) {
		$values = array();
		foreach (array('assignment_id', 'submission_date',
			'submission_ip', 'file_path', 'file_name', 'comments',
			'grade', 'grade_comments', 'grade_submission_date',
			'grade_submission_ip') as $field) {
			$values[] = quote($row[$field]);
		}
		fputs($f, "assignment_submit($row[uid], ".
			join(", ", $values).
			");\n");
	}
}


function backup_dropbox_file($f) {
	$res = mysql_query("SELECT * FROM dropbox_file");
	while ($row = mysql_fetch_array($res)) {
		fputs ($f, "dropbox_file(".
			quote($row['uploaderId']).", ".
			quote($row['filename']).", ".
			quote($row['filesize']).", ".
			quote($row['title']).", ".
			quote($row['description']).", ".
			quote($row['author']).", ".
			quote($row['uploadDate']).", ".
			quote($row['lastUploadDate']).");\n");
		}
}

function backup_dropbox_person($f) {
	$res = mysql_query("SELECT * FROM dropbox_person");
	while ($row = mysql_fetch_array($res)) {
		fputs ($f, "dropbox_person(".
			quote($row['fileId']).", ".
			quote($row['personId']).");\n");
		}
}

function backup_dropbox_post($f) {
	$res = mysql_query("SELECT * FROM dropbox_post");
	while ($row = mysql_fetch_array($res)) {
		fputs ($f, "dropbox_post(".
			quote($row['fileId']).", ".
			quote($row['recipientId']).");\n");
	}
}


function backup_users($f, $course) {
	global $mysqlMainDb;
	
	$res = mysql_query("SELECT user.*, cours_user.statut as cours_statut
		FROM `$mysqlMainDb`.user, `$mysqlMainDb`.cours_user
		WHERE user.user_id=cours_user.user_id
		AND cours_user.code_cours='$course'");
	while($q = mysql_fetch_array($res)) {
		fputs($f, "user(".
			quote($q['user_id']).", ".
			quote($q['nom']).", ".
			quote($q['prenom']).", ".
			quote($q['username']).", ".
			quote($q['password']).", ".
			quote($q['email']).", ".
			quote($q['cours_statut']).", ".
			quote($q['phone']).", ".
			quote($q['department']).");\n");
	}
}

function backup_course_db($f, $course) { 
	mysql_select_db($course); 
	$res_tables = mysql_list_tables($course); 
	while ($r = mysql_fetch_row($res_tables)) 
	{ 
		$tablename = $r[0]; 
		fwrite($f, "query(\"DROP TABLE IF EXISTS `$tablename`\");\n"); 
		$res_create = mysql_fetch_array(mysql_query("SHOW CREATE TABLE $tablename"));
		$schema = $res_create[1]; 
		fwrite($f, "query(\"$schema\");\n");
		if ($tablename == 'user_group') {
			backup_groups($f);
		} elseif ($tablename == 'assignment_submit') {
			backup_assignment_submit($f);
		} elseif ($tablename == 'dropbox_file') {
			backup_dropbox_file($f);
		} elseif ($tablename == 'dropbox_person') {
			backup_dropbox_person($f);
		} elseif ($tablename == 'dropbox_post') {
			backup_dropbox_post($f);
		} else {
			$res = mysql_query("SELECT * FROM $tablename"); 
			if (mysql_num_rows($res) > 0) { 
				$fieldnames = "";
				$num_fields = mysql_num_fields($res); 
				for($j = 0; $j < $num_fields; $j++) { 
					$fieldnames .= "`".mysql_field_name($res, $j)."`"; 
					if ($j < ($num_fields - 1)) {
						$fieldnames .= ', ';
					}
				}
				$insert = "query(\"INSERT INTO `$tablename` ($fieldnames) VALUES\n";
				$counter = 1;
				while($rowdata = mysql_fetch_row($res)) {
					if (($counter % 30) == 1) {
						if ($counter > 1) {
							fputs($f, "\n\");\n");
						}
						fputs($f, $insert."\t(");
					} else {
						fputs($f, ",\n\t(");
					}
					$counter++;
					for ($j = 0; $j < $num_fields; $j++) { 
						fputs($f, quote($rowdata[$j]));
						if ($j < ($num_fields - 1)) {
							fputs($f, ', ');
						}
					}
					fputs($f, ')');
				}
				fputs($f, "\n\");\n");
			} 
		} 
	} 
}


function backup_course_details($f, $course) {
	global $mysqlMainDb;
	
	$res = mysql_query("SELECT * FROM `$mysqlMainDb`.cours
				    WHERE code = '$course'");
	$q = mysql_fetch_array($res);
	fputs($f, "course_details('$course',\t// Course code\n\t".
		quote($q['languageCourse']).",\t// Language\n\t".
		quote($q['intitule']).",\t// Title\n\t".
		quote($q['description']).",\t// Description\n\t".
		quote($q['faculte']).",\t// Faculty\n\t".
		quote($q['visible']).",\t// Visible?\n\t".
		quote($q['titulaires']).",\t// Professor\n\t".
		quote($q['type']).");\t// Type\n");
}
?>

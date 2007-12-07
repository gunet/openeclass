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
$tool_content = "";

$verboseBackup = FALSE; 
$archiveDir = "/courses/archive";  	// <- must moved to config
$ext ="txt";				// <- must moved to config
 
if (extension_loaded("zlib")) {
	include("../../include/pclzip/pclzip.lib.php");
}
		
// check if you are admin
if($is_adminOfCourse) {
  $tool_content .= "
  <div id=\"operations_container\">
  <ul id=\"opslist\">
    <li>";
if (isset($c) && ($c!="")) {
	if (isset($search) && ($search=="yes")) $searchurl = "&search=yes";
	else $searchurl = "";
	$tool_content .= "<a href=\"../admin/editcours.php?c=".$c."".$searchurl."\">$langBack</a></li>";
} else {
	$tool_content .= "<a href=\"infocours.php\">$langBack</a></li>";
}
	

  $tool_content .= "
  </ul>
  </div>";
	
	$dateBackuping  = date("Y-m-d-H-i-(B)-s");
	$shortDateBackuping  = date("YzBs"); // YEAR - Day in Year - Swatch - second 
	$archiveDir .= "/".$currentCourseID."/".$dateBackuping;
	$systemFileNameOfArchive = realpath("../..").$archiveDir."/claroBak-".$currentCourseID."-".$dateBackuping.".".$ext;
	
// ********************************************************************
// build config file
// ********************************************************************
	$stringConfig="<?php
/*
      +----------------------------------------------------------------------+
      +----------------------------------------------------------------------+
      This file was generate by script $PHP_SELF
      ".date("r")."                  |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
*/

// Source was  in ".realpath("../../".$currentCourseID."/")."
// find in ".$archiveDir."/courseBase/courseBase.sql sql to rebuild the cours base
// find in ".$archiveDir."/".$currentCourseID." to content of directory of course

";


if (extension_loaded("zlib")) {
	$zipCourse = new PclZip("../..".$archiveDir."/../archive.".$currentCourseID.".".$shortDateBackuping.".zip");
	$zipCourse->create("../..".$archiveDir."/");
    

} else {
		$tool_content .= $langBackupSuccesfull;

	}
	
	$tool_content .= "<br />
    <table width='99%'>
    <tbody>
    <tr>
      <td class=\"success\" width='1'></td>
      <td class=\"left\"><b>$langBackupSuccesfull</b></td>
      <td><div align='right'>
          <a href=\"".$urlServer."/".$archiveDir."/../archive.".$currentCourseID.".".$shortDateBackuping.".zip\">".$langDownloadIt."</a></div>
      </td>
      <td width='1'><img src=\"../../template/classic/img/download.gif\" alt=\"".$langDownloadIt."\" title=\"".$langDownloadIt."\" width=\"30\" height=\"29\"></td>
    </tr>
    </tbody>
    </table>

    <br />

    <table class='Deps' align='center'>
    <tbody>
    <tr>
      <th align=\"left\">
      <ol>\n";

// if dir is missing, first we create it. mkpath is a recursive function
    $dirCourBase = realpath("../..").$archiveDir."/courseBase";
	if (!is_dir($dirCourBase)) {
		$tool_content .= "       <li>".$langCreateDirCourseBase."</li>\n";
		mkpath($dirCourBase,$verboseBackup);
	}

	create_backup_file("$webDir/$archiveDir/backup.php");
    
    $dirMainBase = realpath("../..").$archiveDir."/mainBase";
	if (!is_dir($dirMainBase)) {
		$tool_content .= "       <li>".$langCreateDirMainBase."</li>\n";
		mkpath($dirMainBase,$verboseBackup);
	}
    $dirhtml = realpath("../..").$archiveDir."/html";

// ********************************************************************
// Copy of  from DB main
// fields about this course
// ********************************************************************
//  info  about cours
// ********************************************************************

	$tool_content .= "       <li>".$langBUCourseDataOfMainBase."  ".$currentCourseID."</li>\n";
	$sqlInsertCourse = "
INSERT INTO cours SET ";
	$csvInsertCourse ="\n";
	$sqlSelectInfoCourse ="Select * from `$mysqlMainDb`.cours where code = '".$currentCourseID."' ";
	$resInfoCourse = mySqlQueryShowError($sqlSelectInfoCourse) ;
	$infoCourse = mysql_fetch_array($resInfoCourse);
	for($noField=0; $noField<mysql_num_fields($resInfoCourse); $noField++) {
		if ($noField>0)
			$sqlInsertCourse .= ", ";
		$nameField = mysql_field_name($resInfoCourse,$noField);
		$sqlInsertCourse .= "$nameField = '".$infoCourse["$nameField"]."'";
		$csvInsertCourse .= "'".addslashes($infoCourse["$nameField"])."';";
	}
	$sqlInsertCourse .= ";";
	$stringConfig .= "
# Insert Course
#------------------------------------------
#	".$sqlInsertCourse."
#------------------------------------------
	";

	$fcoursql = fopen(realpath("../..").$archiveDir."/mainBase/cours.sql", "w");
	fwrite($fcoursql, $sqlInsertCourse); 
	fclose($fcoursql);
	
	$fcourcsv = fopen(realpath("../..").$archiveDir."/mainBase/cours.csv", "w");
	fwrite($fcourcsv, $csvInsertCourse); 
	fclose($fcourcsv);
	
// ********************************************************************
//  info  about users
// ********************************************************************
	$tool_content .= "       <li>".$langBUUsersInMainBase." ".$currentCourseID."</li>\n";
	
	$sqlUserOfTheCourse ="SELECT user.* FROM `$mysqlMainDb`.user, `$mysqlMainDb`.cours_user
		WHERE user.user_id=cours_user.user_id
		AND cours_user.code_cours='$currentCourseID'";

		$resUsers = mySqlQueryShowError($sqlUserOfTheCourse,$db);
		$nbFields = mysql_num_fields($resUsers);
		$sqlInsertUsers = "";
		$csvInsertUsers = "";
		$htmlInsertUsers = "<table>\t<TR>\n";
// creation of headers 
		for($noField=0; $noField < $nbFields; $noField++)
		{
			$nameField = mysql_field_name($resUsers,$noField);
			$csvInsertUsers .= "'".addslashes($nameField)."';";
			$htmlInsertUsers .= "\t\t<TH>".$nameField."</TH>\n";
		}
			$htmlInsertUsers .= "\t</TR>\n";

// creation of body
		while($users = mysql_fetch_array($resUsers))
		{
			$htmlInsertUsers .= "\t<TR>\n";
			$sqlInsertUsers .= "INSERT IGNORE INTO user SET ";
			$csvInsertUsers .= "\n";
			for($noField=0; $noField < $nbFields; $noField++)
			{
				if ($noField>0)
					$sqlInsertUsers .= ", ";
				$nameField = mysql_field_name($resUsers,$noField);
				$sqlInsertUsers .= "$nameField = '".$users["$nameField"]."' ";
				$csvInsertUsers .= "'".addslashes($users["$nameField"])."';";
				$htmlInsertUsers .= "\t\t<td>".$users["$nameField"]."</td>\n";				
			}
			$sqlInsertUsers .= ";";
			$htmlInsertUsers .= "\t</tr>\n";
		}
	$htmlInsertUsers .= "</table>\n";

	$stringConfig .= "
# INSERT Users
#------------------------------------------
#	".$sqlInsertUsers."
#------------------------------------------
	";

	$fuserssql = fopen(realpath("../..").$archiveDir."/mainBase/users.sql", "w");
	fwrite($fuserssql, $sqlInsertUsers); 
	fclose($fuserssql);
	
	$fuserscsv = fopen(realpath("../..").$archiveDir."/mainBase/users.csv", "w");
	fwrite($fuserscsv, $csvInsertUsers); 
	fclose($fuserscsv);

	$fusershtml = fopen(realpath("../..").$archiveDir."/mainBase/users.html", "w");
	fwrite($fusershtml, $htmlInsertUsers); 
	fclose($fusershtml);

/*  End  of  backup user */

// ********************************************************************
//  info  about announcment
// ********************************************************************
	$tool_content .= "       <li>".$langBUAnnounceInMainBase." ".$currentCourseID."</li>\n";
	
	$sqlAnnounceOfTheCourse ="SELECT a.* FROM  `$mysqlMainDb`.annonces a WHERE a.code_cours='$currentCourseID'";

		$resAnn = mySqlQueryShowError($sqlAnnounceOfTheCourse,$db);
		$nbFields = mysql_num_fields($resAnn);
		$sqlInsertAnn = "";
		$csvInsertAnn = "";
		$htmlInsertAnn = "<table>\t<TR>\n";

// creation of headers 
		for($noField=0; $noField < $nbFields; $noField++) {
			$nameField = mysql_field_name($resUsers,$noField);
			$csvInsertAnn .= "'".addslashes($nameField)."';";
			$htmlInsertAnn .= "\t\t<TH>".$nameField."</TH>\n";
		}
			$htmlInsertAnn .= "\t</TR>\n";

// creation of body
		while($announce = mysql_fetch_array($resAnn)) {
			$htmlInsertAnn .= "\t<TR>\n";
			$sqlInsertAnn .= "INSERT INTO users SET ";
			$csvInsertAnn .= "\n";
			for($noField=0; $noField < $nbFields; $noField++) {
				if ($noField>0)
					$sqlInsertAnn .= ", ";
				$nameField = mysql_field_name($resAnn,$noField);
				$sqlInsertAnn .= "$nameField = '".addslashes($announce["$nameField"])."' ";
				$csvInsertAnn .= "'".addslashes($announce["$nameField"])."';";
				$htmlInsertAnn .= "\t\t<td>".$announce["$nameField"]."</td>\n";				
			}
			$sqlInsertAnn .= ";";
			$htmlInsertAnn .= "\t</tr>\n";
		}
	$htmlInsertAnn .= "</table>\n";

	
	$stringConfig .= "
#INSERT ANNOUNCE
#------------------------------------------
#	".$sqlInsertAnn."
#------------------------------------------
	";

	$fannsql = fopen(realpath("../..").$archiveDir."/mainBase/annonces.sql", "w");
	fwrite($fannsql, $sqlInsertAnn); 
	fclose($fannsql);
	
	$fanncsv = fopen(realpath("../..").$archiveDir."/mainBase/annnonces.csv", "w");
	fwrite($fanncsv, $csvInsertAnn); 
	fclose($fanncsv);

	$fannhtml = fopen(realpath("../..").$archiveDir."/mainBase/annonces.html", "w");
	fwrite($fannhtml, $htmlInsertAnn); 
	fclose($fannhtml);

/*  End  of  backup Annonces */

	// we can copy file of course
	$tool_content .= "       <li>".$langCopyDirectoryCourse."<br>(";
	$nbFiles = copydir(realpath("../../courses/".$currentCourseID."/"), $dirhtml,$verboseBackup);
	$tool_content .= "<strong>".$nbFiles."</strong> ".$langFileCopied.")</li>\n";
	$stringConfig .= "// ".$nbFiles." was in ".realpath("../../courses/".$currentCourseID."/");

// ********************************************************************
// Copy of  DB course
// with mysqldump
// ********************************************************************
	$tool_content .= "       <li>".$langBackupOfDataBase." ".$currentCourseID."  (SQL)<br>(";
	$tool_content .= backupDatabase($db , $currentCourseID , true, true , 'SQL' , realpath("../..".$archiveDir."/courseBase/"),true,$verboseBackup);
	$tool_content .= ")</li>\n       <li>".$langBackupOfDataBase." ".$currentCourseID."  (PHP)<br>(";
	$tool_content .= backupDatabase($db , $currentCourseID , true, true , 'PHP' , realpath("../..".$archiveDir."/courseBase/"),true,$verboseBackup);
	$tool_content .= ")</li>\n       <li>".$langBackupOfDataBase." ".$currentCourseID."  (CSV)<br>(";
	$tool_content .= backupDatabase($db , $currentCourseID , true, true , 'CSV' , realpath("../..".$archiveDir."/courseBase/"),true,$verboseBackup);

// ********************************************************************
// Copy of DB course
// with mysqldump
// ********************************************************************

	$fdesc = fopen($systemFileNameOfArchive, "w");
	fwrite($fdesc,$stringConfig);
	fclose($fdesc);
	$tool_content .=  ")</li>\n     </ol>\n     </th>\n     <td>&nbsp;</td>\n     </tr>\n   </tbody>\n   </table>\n";
	

}	// end of isadminOfCourse
else 
{
	$tool_content .= "<center><p>$langNotAllowed</p></center>";
}



draw($tool_content, 2, 'course_info');

// -----------------
// useful functions
// -----------------

function DirSize($path , $recursive=TRUE) { 
	$result = 0; 
	if(!is_dir($path) || !is_readable($path)) 
   		return 0; 
	$fd = dir($path); 
	while($file = $fd->read())
	{ 
	   	if(($file != ".") && ($file != ".."))
   		{ 
    	if (@is_dir("$path$file/")) 
 			$result += $recursive?DirSize("$path$file/"):0; 
    	else  
			$result += filesize("$path$file"); 
		} 
	}
	$fd->close(); 
	return $result; 
} 

/** 
 * Backup a db to a file 
 */ 
function backupDatabase($link , $db_name , $structure , $donnees , $format="SQL" , $whereSave=".", $insertComplet="",$verbose=false)
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
		if ($format=="PHP")
			fwrite($fp, "mysql_query(\"");
		$tablename = mysql_tablename($res, $i); 
		if ($verbose)
			echo "[".$tablename."] ";
		if ($structure === true) 
		{ 
			fwrite($fp, "DROP TABLE IF EXISTS `$tablename`;\n"); 
			if ($format=="PHP")
				fwrite($fp, "\");");
			if ($format=="PHP")
				fwrite($fp, "mysql_query(\"");
			$query = "SHOW CREATE TABLE $tablename"; 
			$resCreate = mysql_query($query); 
			$row = mysql_fetch_array($resCreate); 
			$schema = $row[1].";"; 
			fwrite($fp, "$schema\n\n"); 
			if ($format=="PHP")
				fwrite($fp, "\");");
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
					if ($format=="PHP")
						fwrite($fp, "mysql_query(\"");
					fwrite($fp, "$lesDonnees\n"); 
					if ($format=="PHP")
						fwrite($fp, "\");");
				} 
			} 
		} 
		$i++; 
	} 
	return "$langBackupEnd $format";
	fclose($fp); 
}

function copydir($origine,$destination,$verbose=false) {
	$dossier=opendir($origine);
	if (file_exists($destination))
	{ 
		return 0;
	}
	mkdir($destination, 0770);
	if ($verbose)
		echo "
		<strong>
			[".basename($destination)."]
		</strong>
		<ol>";
	$total = 0;

	while ($fichier = readdir($dossier)) 
	{
		$l = array('.', '..');
		if (!in_array( $fichier, $l))
		{
			if (is_dir($origine."/".$fichier))
			{
				if ($verbose)
					echo "
			<li>";
				$total += copydir("$origine/$fichier", "$destination/$fichier",$verbose);
			} 
			else 
			{
				copy("$origine/$fichier", "$destination/$fichier");
				if ($verbose)
					echo "<li>$fichier";
				$total++;
			}
			if ($verbose)
				echo "</li>";
		}
	}
	if ($verbose)
		echo "</ol>";
	return $total;
}
function getextension($fichier) { 
	$bouts = explode(".", $fichier); 
	return array(array_pop($bouts), implode(".", $bouts)); 
}

/**
 * to create missing directory in a gived path
 *
 * @returns a resource identifier or FALSE if the query was not executed correctly. 
 * @author KilerCris@Mail.com original function from  php manual 
 * @author Christophe Gesche gesche@ipm.ucl.ac.be Claroline Team 
 * @since  28-Aug-2001 09:12 
 * @param sting		$path 		wanted path 
 * @param boolean	$verbose	fix if comments must be printed
 * @param string	$mode		fix if chmod is same of parent or default
 */
function mkpath($path, $verbose = false, $mode = "herit")  {
	Global $langCreatedIn;
	$path = str_replace("/","\\",$path);
	$dirs = explode("\\",$path);
	$path = $dirs[0];
	if ($verbose)
		echo "<ul>";
	for($i = 1;$i < count($dirs);$i++) 
	{
		$path .= "/".$dirs[$i];
		if(!is_dir($path))
		{
			$ret=mkdir($path, 0770);
			if ($ret)
			{
				if ($verbose)
					echo "
				<li>
					<strong>
						".basename($path)."
					</strong>
					<br>
				 	".$langCreatedIn." 
					<br>
				 	<strong>
						".realpath($path."/..")."
					</strong>";
			}
			else
			{
				if ($verbose)
					echo "
				</ul>
				error : ".$path." not created";
			}
		}
	}
	if ($verbose)
		echo "</ul>";
	return $ret;
}

// 
/**
 * to detect errors in Mysql Queries
 *
 * if  there is no error, the string is  write  in source html
 * if not,  error is printed with sql request
 *
 * @returns a resource identifier or FALSE if the query was not executed correctly. 
 * @author Christophe Gesche gesche@ipm.ucl.ac.be Claroline Team 
 */
function mySqlQueryShowError($sql,$db="###")
{
    if ($db=="###")
	{
		$val =  @mysql_query($sql);
	}
	else
	{
		$val =  @mysql_query($sql,$db);
	}
	if (mysql_errno())
	{
		echo "<HR>".mysql_errno().": ".mysql_error()."<br><PRE>$sql</PRE><HR>";
	}
    else
	{
		echo "<!-- \n$sql\n-->";
	}
	return $val;
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
	
	$res = mySqlQueryShowError("SELECT * FROM `$mysqlMainDb`.annonces
				    WHERE code_cours = '$course'");
	while($q = mysql_fetch_array($res)) {
		fputs($f, "announcement(".
			quote($q['contenu']).",\n".
			quote($q['temps']).", ".
			quote($q['ordre']).");\n");
	}
}

function backup_groups($f) {
	$res = mySqlQueryShowError("SELECT * FROM user_group");
		while($row = mysql_fetch_assoc($res)) {
		fputs($f, "group(".
			$row['user'].", ".
			$row['team'].", ".
			$row['status'].", ".
			quote($row['role']).");\n");
	}
}

function backup_assignment_submit($f) {
	$res = mySqlQueryShowError("SELECT * FROM assignment_submit");
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
	$res = mySqlQueryShowError("SELECT * FROM dropbox_file");
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
	$res = mySqlQueryShowError("SELECT * FROM dropbox_person");
	while ($row = mysql_fetch_array($res)) {
		fputs ($f, "dropbox_person(".
			quote($row['fileId']).", ".
			quote($row['personId']).");\n");
		}
}

function backup_dropbox_post($f) {
	$res = mySqlQueryShowError("SELECT * FROM dropbox_post");
	while ($row = mysql_fetch_array($res)) {
		fputs ($f, "dropbox_post(".
			quote($row['fileId']).", ".
			quote($row['recipientId']).");\n");
	}
}


function backup_users($f, $course) {
	global $mysqlMainDb;
	
	$res = mySqlQueryShowError("SELECT user.*, cours_user.statut as cours_statut
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
			quote($q['department']).", ".
			quote($q['inst_id']).");\n");
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
	
	$res = mySqlQueryShowError("SELECT * FROM `$mysqlMainDb`.cours
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

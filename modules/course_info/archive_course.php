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

if (isset($c) && ($c!="")) {
	session_start();
	$require_admin = TRUE;
	$_SESSION['dbname'] = $c;
}

$require_current_course = TRUE;
$require_prof = TRUE;
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
	$dateBackuping  = date("Y-m-d-H-i-(B)-s");
	$shortDateBackuping  = date("YzBs"); // YEAR - Day in Year - Swatch - second
	$archiveDir .= "/".$currentCourseID."/".$dateBackuping;
	$zipfile = $webDir."courses/archive/$currentCourseID/archive.$currentCourseID.$shortDateBackuping.zip";
	$tool_content .= "<table class='Deps' align='center'><tbody><tr><th align=\"left\"><ol>\n";

	$dirArchive = realpath("../..").$archiveDir;
	mkpath($dirArchive);

	// creation of the sql queries will all the data dumped
	create_backup_file("$webDir/$archiveDir/backup.php");

    	$dirhtml = realpath("../..").$archiveDir."/html";

	$tool_content .= "<li>".$langBUCourseDataOfMainBase."  ".$currentCourseID."</li>\n";

	// we can copy file of course
	$tool_content .= "<li>".$langCopyDirectoryCourse."<br>(";
	$nbFiles = copydir(realpath("../../courses/".$currentCourseID."/"), $dirhtml);
	$tool_content .= "<strong>".$nbFiles."</strong> ".$langFileCopied.")</li>\n";
	$tool_content .= "<li>".$langBackupOfDataBase." ".$currentCourseID;
	$tool_content .= "</li></ol></th><td>&nbsp;</td></tr></tbody></table>";

//-----------------------------------------
// create zip file
// ----------------------------------------

	$zipCourse = new PclZip($zipfile);
	if ($zipCourse->create($webDir.$archiveDir, PCLZIP_OPT_REMOVE_PATH, "$webDir") == 0) {
		$tool_content .= "Error: ".$zipCourse->errorInfo(true);
		draw($tool_content, 2, 'course_info');
		exit;
	} else {
		$tool_content .= "<br /><p class='success_small'>$langBackupSuccesfull</p><div align=\"left\"><a href='$urlServer/courses/archive/$currentCourseID/archive.$currentCourseID.$shortDateBackuping.zip'>$langDownloadIt</a><img src='../../template/classic/img/download.gif' title='$langDownloadIt' width='30' height='29'></div>";
	}

	$tool_content .= "<p align=\"right\">";
	if (isset($c) && ($c!="")) {
		if (isset($search) && ($search=="yes")) $searchurl = "&search=yes";
		else $searchurl = "";
		$tool_content .= "<a href=\"../admin/editcours.php?c=".$c."".$searchurl."\">$langBack</a>";
	} else {
		$tool_content .= "<a href=\"infocours.php\">$langBack</a>";
	}
	$tool_content .= "</p>";

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
                                touch("$destination/$fichier", filemtime("$origine/$fichier"));
				$total++;
			}
		}
	}
	return $total;
}



function create_backup_file($file) {
	global $currentCourseID, $cours_id, $mysqlMainDb;

	$f = fopen($file,"w");
	if (!$f) {
		die("Error! Unable to open output file: '$f'\n");
	}
	list($ver) = mysql_fetch_array(db_query("SELECT `value` FROM `$mysqlMainDb`.config WHERE `key`='version'"));
	fputs($f, "<?\n\$eclass_version = '$ver';\n\$version = 2;\n\$encoding = 'UTF-8';\n");
	backup_course_details($f, $currentCourseID);
	backup_annonces($f, $cours_id);
	backup_course_units($f);
	backup_users($f, $cours_id);
	backup_course_db($f, $currentCourseID);
	fputs($f, "?>\n");
	fclose($f);
}

function backup_annonces($f, $cours_id) {
	global $mysqlMainDb;

	$res = mysql_query("SELECT * FROM `$mysqlMainDb`.annonces
				    WHERE cours_id = $cours_id");
	while($q = mysql_fetch_array($res)) {
		fputs($f, "announcement(".
			quote($q['contenu']).",\n".
			quote($q['temps']).", ".
			quote($q['ordre']).", ".
			quote($q['title']).");\n");
	}
}

function backup_course_units($f) {
	global $mysqlMainDb, $cours_id;
	
	$res = mysql_query("SELECT * FROM `$mysqlMainDb`.course_units
				    WHERE course_id = $cours_id");
	while($q = mysql_fetch_array($res)) {
		fputs($f, "course_units(".
			quote($q['title']).", ".
			quote($q['comments']).", ".
			quote($q['visibility']).", ".
			quote($q['order']).", array(");
		$res2 = db_query("SELECT * FROM unit_resources WHERE unit_id = $q[id]", $mysqlMainDb);
		$begin = true;
		while($q2 = mysql_fetch_array($res2)) {
			if ($begin) {
				$begin = !$begin;
				fputs($f, "\n");
			} else {
				fputs($f, ",\n");
			}
			fputs($f, "array(".
			quote($q2['title']).", ".
			quote($q2['comments']).", ".
			quote($q2['res_id']).", ".
			quote($q2['type']).", ".
			quote($q2['visibility']).", ".
			quote($q2['order']).", ".
			quote($q2['date']).")");
		}
		fputs($f,"));\n");
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


function backup_users($f, $cours_id) {
	global $mysqlMainDb;

	$res = mysql_query("SELECT user.*, cours_user.statut as cours_statut
		FROM `$mysqlMainDb`.user, `$mysqlMainDb`.cours_user
		WHERE user.user_id=cours_user.user_id
		AND cours_user.cours_id = $cours_id");
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
			quote($q['registered_at']).", ".
			quote($q['expires_at']).");\n");
	}
}

function backup_course_db($f, $course) {
	mysql_select_db($course);

	$res_tables = db_query("SHOW TABLES FROM $course");
	while ($r = mysql_fetch_row($res_tables)) {
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

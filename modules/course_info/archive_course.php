<?php
/* ========================================================================
 * Open eClass 2.4
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

$require_current_course = TRUE;
$require_course_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/lib/fileManageLib.inc.php';

$nameTools = $langArchiveCourse;
$navigation[] = array('url' => "infocours.php?course=$code_cours", 'name' => $langModifInfo);

if (extension_loaded('zlib')) {
	include '../../include/pclzip/pclzip.lib.php';
}


        // Remove previous back-ups older than 10 minutes
        cleanup("${webDir}courses/archive", 600);

        $basedir = "${webDir}courses/archive/$currentCourseID";
	mkpath($basedir);

	$backup_date = date('Y-m-d-H-i-(B)-s');
	$backup_date_short = date('YzBs'); // YEAR - Day in Year - Swatch - second

	$archivedir = $basedir . '/' . $backup_date;
	mkpath($archivedir);

	$zipfile = $basedir . "/archive.$currentCourseID.$backup_date_short.zip";
	$tool_content .= "<table class='tbl' align='center'><tbody><tr><th align='left'><ol>\n";

	// creation of the sql queries will all the data dumped
	create_backup_file($archivedir . '/backup.php');

        // backup subsystems from main db
        mysql_select_db($mysqlMainDb);
        $sql_course = "course_id = $cours_id";
        foreach (array('cours' => "cours_id = $cours_id",
                       'faculte' => "id = (SELECT faculteid FROM cours
                                                            WHERE cours_id = $cours_id)",
                       'user' => "user_id IN (SELECT user_id FROM cours_user
                                                             WHERE cours_id = $cours_id)",
                       'cours_user' => "cours_id = $cours_id",
                       'annonces' => "cours_id = $cours_id",
                       'group_properties' => $sql_course,
                       'group' => $sql_course,
                       'group_members' => "group_id IN (SELECT id FROM `group`
                                                               WHERE course_id = $cours_id)",
                       'document' => $sql_course,
                       'link_category' => $sql_course,
                       'link' => $sql_course,
                       'ebook' => $sql_course,
                       'ebook_section' => "ebook_id IN (SELECT id FROM ebook
                                                               WHERE course_id = $cours_id)",
                       'ebook_subsection' => "section_id IN (SELECT ebook_section.id
                                                                    FROM ebook, ebook_section
                                                                    WHERE ebook.id = ebook_id AND
                                                                          course_id = $cours_id)",
                       'course_units' => $sql_course,
                       'unit_resources' => "unit_id IN (SELECT id FROM course_units
                                                               WHERE course_id = $cours_id)",
                       'forum_notify' => $sql_course,
                       'video' => $sql_course,
                       'videolinks' => $sql_course)
             as $table => $condition) {
                backup_table($archivedir, $table, $condition);
        }
        file_put_contents("$archivedir/config_vars",
                serialize(array('urlServer' => $urlServer,
                                'urlAppend' => $urlAppend,
                                'siteName' => $siteName)));

    	$htmldir = $archivedir . '/html';
	$tool_content .= "<li>$langBUCourseDataOfMainBase  $currentCourseID</li>\n";
	       
        // create zip file
	$zipCourse = new PclZip($zipfile);
        $result = $zipCourse->create($archivedir, 
                                PCLZIP_OPT_REMOVE_PATH, "${webDir}courses/archive");
        $result = $zipCourse->add("$webDir/courses/$currentCourseID", 
                                PCLZIP_OPT_REMOVE_PATH, "${webDir}courses/$currentCourseID", 
                                PCLZIP_OPT_ADD_PATH, "$currentCourseID/$backup_date/html");
        $result = $zipCourse->add("${webDir}video/$currentCourseID", 
                                PCLZIP_OPT_REMOVE_PATH, "${webDir}video/$currentCourseID", 
                                PCLZIP_OPT_ADD_PATH, "$currentCourseID/$backup_date/video_files");        
        
        removeDir($archivedir);
        
        $tool_content .= "<li>$langBackupOfDataBase $currentCourseID</li></ol></th>
                          <td>&nbsp;</td></tr></tbody></table>";
	if (!$result) {
		$tool_content .= "Error: ".$zipCourse->errorInfo(true);
		draw($tool_content, 2);
		exit;
	} else {
		$tool_content .= "<br /><p class='success_small'>$langBackupSuccesfull</p><div align=\"left\"><a href='$urlAppend/courses/archive/$currentCourseID/archive.$currentCourseID.$backup_date_short.zip'>$langDownloadIt <img src='$themeimg/download.png' title='$langDownloadIt' alt=''></a></div>";
	}

        $tool_content .= "<p align='right'>
               <a href='infocours.php?course=$code_cours'>$langBack</a></p>";

	draw($tool_content, 2);

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
	fputs($f, "<?php\n\$eclass_version = '$ver';\n\$version = 2;\n\$encoding = 'UTF-8';\n");
	backup_course_db($f, $currentCourseID);
	fputs($f, "?>\n");
	fclose($f);
}

function backup_table($basedir, $table, $condition) {
        $q = db_query("SELECT * FROM `$table` WHERE $condition");
        $backup = array();
        $num_fields = mysql_num_fields($q);
        while ($data = mysql_fetch_assoc($q)) {
                for ($i=0; $i < $num_fields; $i++) {
                        $type = mysql_field_type($q, $i);
                        if ($type == 'int') {
                                $name = mysql_field_name($q, $i);
                                $data[$name] = intval($data[$name]);
                        }
                }
                $backup[] = $data;
        }
        file_put_contents("$basedir/$table", serialize($backup));
}


function backup_assignment_submit($f) {
	$res = db_query("SELECT * FROM assignment_submit");
		while($row = mysql_fetch_assoc($res)) {
		$values = array();
		foreach (array('assignment_id', 'submission_date',
			'submission_ip', 'file_path', 'file_name', 'comments',
			'grade', 'grade_comments', 'grade_submission_date',
			'grade_submission_ip') as $field) {
			$values[] = inner_quote($row[$field]);
		}
		fputs($f, "assignment_submit($row[uid], ".
			join(", ", $values).
			");\n");
	}
}


function backup_course_db($f, $course) {
	mysql_select_db($course);

	$res_tables = db_query("SHOW TABLES FROM `$course`");
	while ($r = mysql_fetch_row($res_tables)) {
		$tablename = $r[0];
		fwrite($f, "query(\"DROP TABLE IF EXISTS `$tablename`\");\n");
		$res_create = mysql_fetch_array(db_query("SHOW CREATE TABLE $tablename"));
		$schema = $res_create[1];
		fwrite($f, "query(\"$schema\");\n");
		if ($tablename == 'assignment_submit') {
			backup_assignment_submit($f);
		} else {
			$res = db_query("SELECT * FROM $tablename");
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
						fputs($f, inner_quote($rowdata[$j]));
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

function inner_quote($s)
{
        return "'" . str_replace(array('\\', '\'', '"', "\0"),
                array('\\\\', '\\\'', '\\"', "\\\0"),
                $s) . "'";
}

// Delete everything in $basedir older than $age seconds
function cleanup($basedir, $age)
{
        if ($handle = opendir($basedir)) {
                while (($file = readdir($handle)) !== false) {
                        $entry = "$basedir/$file";
                        if ($file != '.' and $file != '..' and
                            (time() - filemtime($entry) > $age)) {
                                if (is_dir($entry)) {
                                        removeDir($entry);
                                } else {
                                        unlink($entry);
                                }
                        }
                }
        }
}
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

$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../upgrade/upgrade_functions.php';
include '../../include/lib/fileUploadLib.inc.php';
include '../../include/lib/fileManageLib.inc.php';
include '../../include/lib/forcedownload.php';
include '../../include/pclzip/pclzip.lib.php';

$nameTools = $langRestoreCourse;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);

// Initialise $tool_content
$tool_content = "";
// Main body

// Default backup version
$version = 1;
$encoding = 'ISO-8859-7';
if (isset($_FILES['archiveZipped']) and $_FILES['archiveZipped']['size'] > 0) {
        $tool_content .= "<fieldset>
	<legend>".$langFileSent."</legend>
	<table class='tbl' width='100%'>
                   <tr><th width='150'>$langFileSentName</td><td>".$_FILES['archiveZipped']['name']."</th></tr>
                   <tr><th>$langFileSentSize</td><td>".$_FILES['archiveZipped']['size']."</th></tr>
                   <tr><th>$langFileSentType</td><td>".$_FILES['archiveZipped']['type']."</th></tr>
                   <tr><th>$langFileSentTName</td><td>".$_FILES['archiveZipped']['tmp_name']."</th></tr>
	        </table></fieldset>
			<fieldset>
	<legend>".$langFileUnzipping."</legend>
	<table class='tbl' width='100%'>
                    <tr><td>".unpack_zip_show_files($_FILES['archiveZipped']['tmp_name'])."</td></tr>
                </table></fieldset>";
} elseif (isset($_POST['send_path']) and isset($_POST['pathToArchive'])) {
        $pathToArchive = $_POST['pathToArchive'];
	if (file_exists($pathToArchive)) {
		$tool_content .= "<fieldset>
	<legend>".$langFileUnzipping."</legend>
	<table class='tbl' width='100%'>";
		$tool_content .= "<tr><td>".unpack_zip_show_files($pathToArchive)."</td></tr>";
		$tool_content .= "</table></fieldset>";
	} else {
		$tool_content .= "<p class='caution'>$langFileNotFound</p>";
	}
} elseif (isset($_POST['create_restored_course'])) {
        register_posted_variables(array('restoreThis' => true,
                                        'course_code' => true,
                                        'course_lang' => true,
                                        'course_title' => true,
                                        'course_desc' => true,
                                        'course_fac' => true,
                                        'course_vis' => true,
                                        'course_prof' => true,
                                        'course_type' => true));
        $r = $restoreThis . '/html';
	list($new_course_code, $course_id) = create_course($course_code, $course_lang, $course_title,
                $course_desc, intval($course_fac), $course_vis, $course_prof, $course_type);

        $cours_file = $_POST['restoreThis'] . '/cours';
        if (file_exists($cours_file)) {
                $data = unserialize(file_get_contents($cours_file));
                $data = $data[0];
                db_query("UPDATE `$mysqlMainDb`.cours
                                 SET course_keywords = ".quote($data['course_keywords']).",
                                     doc_quota = ".floatval($data['doc_quota']).",
                                     video_quota = ".floatval($data['video_quota']).",
                                     group_quota = ".floatval($data['group_quota']).",
                                     dropbox_quota = ".floatval($data['dropbox_quota']).",
                                     expand_glossary = ".intval($data['expand_glossary'])."
                                 WHERE cours_id = $course_id");
        }

	$userid_map = array();
        $user_file = $_POST['restoreThis'] . '/user';
        if (file_exists($user_file)) {
                $userid_map = restore_users(unserialize(file_get_contents($user_file)));
                $cours_user = unserialize(file_get_contents($_POST['restoreThis'] . '/cours_user'));
                register_users($course_id, $userid_map, $cours_user);
        }

        $coursedir = "${webDir}courses/$new_course_code";
        $videodir = "${webDir}video/$new_course_code";
	move_dir($r, $coursedir);
        if (is_dir($restoreThis . '/video_files')) {
                move_dir($restoreThis . '/video_files', $videodir);
        } else {
                mkdir($videodir, 0775);
        }
	course_index($coursedir, $new_course_code);
	$tool_content .= "<p>$langCopyFiles $coursedir</p><br /><p>";
	$action = 1;
	// now we include the file for restoring
	ob_start();
	include("$restoreThis/backup.php");
	if ($encoding != 'UTF-8') {
		db_query('SET NAMES greek');
        }

        function document_map_function(&$data, $maps) {
                // !!!! FIXME !!!! when restoring ebook and group documents, subsystem id's have changed
        }

        function group_map_function(&$data, $arg) {
                // !!!! FIXME !!!! when restoring group members, group id's have changed
        }

        mysql_select_db($mysqlMainDb);
        restore_table($restoreThis, 'group_properties',
                array('set' => array('course_id' => $course_id)));
        $group_map = restore_table($restoreThis, 'group',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        restore_table($restoreThis, 'group_members',
                array('map' => array('group_id' => $group_map,
                                     'user_id' => $userid_map),
                      'map_function' => 'group_map_function'));
        $link_category_map = restore_table($restoreThis, 'link_category',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        $link_map = restore_table($restoreThis, 'link',
                array('set' => array('course_id' => $course_id),
                      'map' => array('category' => $link_category_map),
                      'return_mapping' => 'id'));
        $ebook_map = restore_table($restoreThis, 'ebook',
                array('set' => array('course_id' => $course_id),
                      'return_mapping' => 'id'));
        $document_map = restore_table($restoreThis, 'document',
                array('set' => array('course_id' => $course_id),
                      'map_function' => 'document_map_function',
                      'map_function_data' => array($group_map, $ebook_map),
                      'return_mapping' => 'id'));


        /*************  backups: *************************
                !!!! FIXME !!!! - need to write restore logic for the following:
foreach (array('document' => $sql_course,
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
                       'forum_notify' => $sql_course)
                       as $table => $condition) { }
         **************************************************************************************/

	if (!isset($eclass_version) or $eclass_version < ECLASS_VERSION) { // if we come from older versions 
		if ($version < '2.2') { // if we come from 2.1.x
			upgrade_course_2_2($new_course_code, $course_lang);
		} else {
			upgrade_course($new_course_code, $course_lang);
		}
	}
	$tool_content .= ob_get_contents();
	ob_end_clean();

	$tool_content .= "</p>";
	if (!file_exists($webDir."courses/garbage"))
		mkdir($webDir."courses/garbage");
	if (!file_exists($webDir."courses/garbage/tmpUnzipping"))
		mkdir($webDir."courses/garbage/tmpUnzipping");
	rename($webDir."courses/tmpUnzipping", $webDir."courses/garbage/tmpUnzipping/".time()."");
	$tool_content .= "<br /><center><p><a href='../admin/index.php'>$langBack</p></center>";
}

elseif (isset($_POST['do_restore'])) {
        if (!file_exists($_POST['restoreThis'] . '/backup.php')) {
                $tool_content .= "<p class='alert1'>$dropbox_lang[generalError]</p>";
                draw($tool_content, 3);
                exit;
        }
        $cours_file = $_POST['restoreThis'] . '/cours';
        if (file_exists($cours_file)) {
                // New-style backup
                $data = unserialize(file_get_contents($cours_file));
                $data = $data[0];
                $tool_content = course_details_form($data['fake_code'], $data['intitule'],
                                                    $data['faculteid'], $data['titulaires'],
                                                    $data['type'], $data['languageCourse'],
                                                    $data['visible'], $data['description']);
        } else {
                // Old-style backup
                // If $action == 0, the course isn't restored - the user just
                // gets a form with the archived course details.
                $action = 0;
                ob_start();
                include($_POST['restoreThis'] . '/backup.php');
                $tool_content .= ob_get_contents();
                ob_end_clean();
        }
} else {

// -------------------------------------
// Displaying Form
// -------------------------------------
	$tool_content .= "
        <br />   
   <fieldset>
  <legend>$langFirstMethod</legend>   
        <table width='100%' class='tbl'>

	<tr>
          <td>$langRequest1
	  <br /><br />
	  <form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	    <input type='file' name='archiveZipped' />
	    <input type='submit' name='send_archive' value='".$langSend."' />
	  </form>	  </td>
          </tr>
	</table>
</fieldset>
<br />


 <fieldset>
  <legend>$langSecondMethod</legend> 	
        <table width='100%' class='tbl'>

	<tr>
	  <td>$langRequest2
	  <br /><br />
	  <form action='".$_SERVER['PHP_SELF']."' method='post'>
	    <input type='text' name='pathToArchive' />
	    <input type='submit' name='send_path' value='".$langSend."' />
	  </form>
	  </td>
        </tr>
	</table></fieldset>
        <br />";
}
mysql_select_db($mysqlMainDb);
draw($tool_content, 3);

// Functions restoring
function course_details($code, $lang, $title, $desc, $fac, $vis, $prof, $type) {

	global $action, $langNameOfLang, $encoding, $version;
	global $siteName, $InstitutionUrl, $Institution;

        include("../lang/greek/common.inc.php");
        $extra_messages = "../../config/greek.inc.php";
        if (file_exists($extra_messages)) {
                include $extra_messages;
        } else {
                $extra_messages = false;
        }
        include("../lang/greek/messages.inc.php");
        if ($extra_messages) {
                include $extra_messages;
        }

	if ($encoding != 'UTF-8') {
		$code = iconv($encoding, 'UTF-8', $code);
		$title = iconv($encoding, 'UTF-8', $title);
		$desc = iconv($encoding, 'UTF-8', $desc);
		$prof = iconv($encoding, 'UTF-8', $prof);
		$fac = iconv($encoding, 'UTF-8', $fac);
	}

        // display the restoring form
	if (!$action) {
                echo course_details_form($code, $title, $fac, $prof, $type, $lang, $vis, $desc);
	}
}

// inserting announcements into the main database
function announcement($text, $date, $order, $title = '') {
	global $action, $course_id, $mysqlMainDb;
	if (!$action) return;
	db_query("INSERT INTO `$mysqlMainDb`.annonces
		(title, contenu, temps, cours_id, ordre)
		VALUES (".
		join(", ", array(
			quote($title),
			quote($text),
			quote($date),
			$course_id,
			quote($order))).
			")");
}

// insert course units into the main database
function course_units($title, $comments, $visibility, $order, $resource_units) {
	global $action, $course_id, $mysqlMainDb;
	
	if (!$action) return;
	
	db_query("INSERT INTO `$mysqlMainDb`.course_units
		(title, comments, visibility, `order`, course_id)
		VALUES (".
		join(", ", array(
			quote($title),
			quote($comments),
			quote($visibility),
			quote($order),
			$course_id)).
			")");
	$unit_id = mysql_insert_id();
	foreach ($resource_units as $key => $units_array) {
		db_query("INSERT INTO `$mysqlMainDb`.unit_resources (unit_id, title, comments, res_id, type, visibility, `order`, date)
			VALUES (".$unit_id.",".join(", ", array_map('quote',$units_array)).");\n");
	}
}


// inserting users into the main database
function user($userid, $name, $surname, $login, $password, $email, $statut, $phone, $department, $registered_at = NULL, $expires_at = NULL, $inst_id = NULL) {
	global $action, $new_course_code, $course_id, $userid_map, $mysqlMainDb, $course_prefix, $course_addusers, $durationAccount, $version, $encoding;
	global $langUserWith, $langAlready, $langWithUsername, $langUserisAdmin, $langUsernameSame, $langUserAlready, $langUName, $langPrevId, $langNewId, $langUserName;

	if ($encoding != 'UTF-8') {
		$name = iconv($encoding, 'UTF-8', $name);
		$surname = iconv($encoding, 'UTF-8', $surname);
		$login = iconv($encoding, 'UTF-8', $login);
	}

	if (!$action) return;
	if (!$course_addusers and $statut != 1)  return;
	if (isset($userid_map[$userid])) {
		echo "<br />$langUserWith $userid_map[$userid] $langAlready\n";
		return;
	}
	if (!$registered_at)  {
		$registered_at = time();
	}
	if (!$expires_at) {
		$expires_at = time() + $durationAccount;
	}

	// add prefix only to usernames that dont use LDAP login
	if ($course_prefix) {
		if ($statut == 1) {
			echo "<br />$langWithUsername $login $langUserisAdmin".
				" - $langUsernameSame";
		} else {
			$login = $new_course_code.'_'.$login;
		}
	}

	$u = mysql_query("SELECT * FROM `$mysqlMainDb`.user WHERE BINARY username=".quote($login));
	if (mysql_num_rows($u) > 0) 	{
		$res = mysql_fetch_array($u);
		$userid_map[$userid] = $res['user_id'];
		echo "<br />";
                echo "<b>" . q($login) . "</b>: $langUserAlready. <i>" .
                     q("$res[1] $res[2]") . "</i>!\n";
	} else {
		if ($version == 1) { // if we come from a archive < 2.x encrypt user password
			$password = md5($password);
		}
		db_query("INSERT INTO `$mysqlMainDb`.user
			(nom, prenom, username, password, email, statut, phone, department, registered_at, expires_at)
			VALUES (".
			join(", ", array(
				quote($name),
				quote($surname),
				quote($login),
				quote($password),
				quote($email),
				quote($statut),
				quote($phone),
				quote($department),
				quote($registered_at),
				quote($expires_at)
				)).
				")");
		$userid_map[$userid] = mysql_insert_id();
	}

	db_query("INSERT INTO `$mysqlMainDb`.cours_user
		(cours_id, user_id, statut, reg_date)
		VALUES ($course_id, $userid_map[$userid], $statut, NOW())");
	echo "<br /> $langUserName=$login, $langPrevId=$userid, $langNewId=$userid_map[$userid]\n";
}

function query($sql) {
	global $action, $new_course_code, $encoding;
        if (!$action) return;
        // Skip tables not used any longer
        if (preg_match('/^(CREATE TABLE|INSERT INTO) `(stat_accueil|users)`/', $sql)) {
                return;
        }
	mysql_select_db($new_course_code);
	if ($encoding != 'UTF-8') {
		if (!iconv($encoding, 'UTF-8', $sql)) {
			die($sql);
		};
	}
	db_query($sql);
}

// function for inserting info about user group
function group($userid, $team, $status, $role) {
	global $action, $userid_map, $new_course_code, $course_addusers;
	if (!$action or !$course_addusers or !isset($userid_map[$userid])) {
		return;
	}
	mysql_select_db($new_course_code);
        // !!!!FIXME!!!! add group directly to new table
	db_query("INSERT into user_group
		(user,team,status,role)
		VALUES (".
		join(", ", array(
			quote($userid_map[$userid]),
			quote($team),
			quote($status),
			quote($role))).
			")");

}

// functions for inserting info about dropbox
function dropbox_file($userid, $filename, $filesize, $title, $description, $author, $uploadDate, $lastUploadDate) {
	global $action,$userid_map, $new_course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($new_course_code);
	db_query("INSERT into dropbox_file
		(uploaderId,filename,filesize,title,description,author,uploadDate,lastUploadDate)
		VALUES (".
		join(", ", array(
			quote($userid_map[$userid]),
			quote($filename),
			quote($filesize),
			quote($title),
			quote($description),
			quote($author),
			quote($uploadDate),
			quote($lastUploadDate))).
			")");
}

function dropbox_person($fileId, $personId) {
	global $action, $userid_map, $new_course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($course_code);
	db_query("INSERT into dropbox_person(fileId, personId)
		VALUES (".
		join(", ", array(
			quote($fileId),
			quote($userid_map[$personId]))).")");
}

function dropbox_post($fileId, $recipientId) {
	global $action, $userid_map, $new_course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($new_course_code);
	db_query("INSERT into dropbox_post (fileId, recipientId)
		VALUES (".
		join(", ", array(
			quote($fileId),
			quote($userid_map[$recipientId]))).")");
}


// insert an assignment submission, translating user id's
function assignment_submit($userid, $assignment_id, $submission_date,
	$submission_ip, $file_path, $file_name, $comments,
	$grade, $grade_comments, $grade_submission_date,
	$grade_submission_ip)
{
	global $action, $userid_map, $new_course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($new_course_code);
	$values = array();
	foreach (array($assignment_id, $submission_date,
		$submission_ip, $file_path, $file_name, $comments,
		$grade, $grade_comments, $grade_submission_date,
		$grade_submission_ip) as $v) {
		$values[] = quote($v);
	}
	db_query("INSERT into assignment_submit
		(uid, assignment_id, submission_date,
		 submission_ip, file_path, file_name,
		 comments, grade, grade_comments, grade_submission_date,
		 grade_submission_ip) VALUES (".
		 quote($userid_map[$userid]). ", ".
		 join(", ", $values). ")");
}

// creating course and inserting entries into the main database
function create_course($code, $lang, $title, $desc, $fac, $vis, $prof, $type) {
	global $mysqlMainDb;

	$repertoire = new_code($fac);

	if (mysql_select_db($repertoire)) {
		echo $langCourseExists;
		exit;
        }
        $fac_name = find_faculty_by_id($fac);
	db_query("INSERT into `$mysqlMainDb`.cours
		(code, languageCourse, intitule, description, faculte, faculteid, visible, titulaires, fake_code, type)
		VALUES (".
		join(", ", array(
			quote($repertoire),
			quote($lang),
			quote($title),
			quote($desc),
			quote($fac_name), $fac,
			quote($vis),
			quote($prof),
			quote($code),
			quote($type))).
		")");
        $cid = mysql_insert_id();

	if (!db_query("CREATE DATABASE `$repertoire`")) {
		echo "Database $repertoire creation failure ";
		exit;
	}
	return array($repertoire, $cid);
}

// creating course index.php file
function course_index($dir, $code) {
	$f = fopen("$dir/index.php", "w");
	fputs($f, "<?php
session_start();
\$dbname=\"$code\";
\$_SESSION['dbname']=\"$code\";
include(\"../../modules/course_home/course_home.php\");
");
	fclose($f);
}


// form select about visibility
function visibility_select($current)
{
	global $langTypeOpen, $langTypeRegistration, $langTypeClosed;
	$ret = "";

	$ret .= "<select name='course_vis'>\n";
	foreach (array($langTypeOpen => '2', $langTypeRegistration => '1', $langTypeClosed => '0')
			as $text => $type) {
		if($type == $current) {
			$ret .= "<option value='$type' selected='1'>$text</option>\n";
		} else {
			$ret .= "<option value='$type'>$text</option>\n";
		}
	}
	$ret .= "</select>\n";
return $ret;
}

// form select about type
function type_select($current)
{
	global $langpre, $langpost, $langother;

	$ret = "";
	$ret .= "<select name='course_type'>\n";
	foreach (array($langpre => 'pre', $langpost => 'post', $langother => 'other') as $text => $type) {
		if($type == $current) {
			$ret .= "<option value='$type' selected>$text</option>\n";
		} else {
			$ret .= "<option value='$type'>$text</option>\n";
		}
	}
	$ret .= "</select>\n";
return $ret;
}

// form select about faculty
function faculty_select($current)
{
	global $mysqlMainDb;
	$ret = "";

	$ret .= "<select name='course_fac'>\n";
	$res = mysql_query("SELECT id, name FROM `$mysqlMainDb`.faculte ORDER BY number");
	while ($fac = mysql_fetch_array($res)) {
		if($fac['name'] == $current) {
			$ret .= "<option selected value='$fac[id]'>$fac[name]</option>\n";
		} else {
			$ret .= "<option value='$fac[id]'>$fac[name]</option>\n";
		}
	}
	$ret .= "</select>\n";
	return $ret;
}

// Unzip backup file
function unpack_zip_show_files($zipfile)
{
	global $webDir, $uid, $langEndFileUnzip, $langLesFound, $langRestore, $langLesFiles;

	$retString = '';

	$destdir = $webDir.'courses/tmpUnzipping/'.$uid;
	mkpath($destdir);
	$zip = new pclZip($zipfile);
	chdir($destdir);	
	$state = $zip->extract();
        $retString .= "<br />$langEndFileUnzip<br /><br />$langLesFound
                       <form action='$_SERVER[PHP_SELF]' method='post'>
                         <ol>";
        foreach (find_backup_folders($destdir) as $folder) {
                $path = q($folder['path'] . '/' . $folder['dir']);
                $file = q($folder['dir']);
                $course = q(preg_replace('|^.*/|', '', $folder['path']));
                $retString .= "<li>$langLesFiles <input type='radio' name='restoreThis' value='$path' />
                                   <b>$course</b> ($file)</li>\n";
	}
        $retString .= "</ol><br /><input type='submit' name='do_restore' value='$langRestore' /></form>";
	chdir($webDir . "modules/course_info");
	return $retString;
}


// Find folders under $basedir containing a "backup.php" file
function find_backup_folders($basedir)
{
        $dirlist = array();
        if (is_dir($basedir) and $handle = opendir($basedir)) {
                while (($file = readdir($handle)) !== false) {
                        $entry = "$basedir/$file";
                        if (is_dir($entry) and $file != '.' and $file != '..') {
                                if (file_exists("$entry/backup.php")) {
                                        $dirlist[] = array('path' => $basedir,
                                                           'dir' => $file);
                                } else {
                                        $dirlist = array_merge($dirlist,
                                                               find_backup_folders($entry));
                                }
                        }
                }
                closedir($handle);
        }
        return $dirlist;
}

function restore_table($basedir, $table, $options)
{
        $set = get_option($options, 'set');
        $backup = unserialize(file_get_contents("$basedir/$table"));
        $i = 0;
        $mapping = array();
        if (isset($options['return_mapping'])) {
                $return_mapping = true;
                $id_var = $options['return_mapping'];
        } else {
                $return_mapping = false;
        }

        foreach ($backup as $data) {
                if ($return_mapping) {
                        $old_id = $data[$id_var];
                        unset($data[$id_var]);
                }
                if (!isset($sql_intro)) {
                        $sql_intro = "INSERT INTO `$table` " .
                                     field_names($data) . ' VALUES ';
                }
                if (isset($options['map'])) {
                        foreach ($options['map'] as $field => &$map) {
                                if (isset($map[$data[$field]])) {
                                        $data[$field] = $map[$data[$field]];
                                } else {
                                        break 2;
                                }
                        }
                }
                db_query($sql_intro . field_values($data, $set));
                if ($return_mapping) {
                        $mapping[$old_id] = mysql_insert_id();
                }
        }
        if ($return_mapping) {
                return $mapping;
        }
}

function field_names($data)
{
        foreach ($data as $name => $value) {
                $keys[] = '`' . $name . '`';
        }
        return '(' . implode(', ', $keys) . ')';
}

function field_values($data, $set)
{
        foreach ($data as $name => $value) {
                if (isset($set[$name])) {
                        $value = $set[$name];
                }
                if (is_int($value)) {
                        $values[] = $value;
                } else {
                        $values[] = quote($value);
                }
        }
        return '(' . implode(', ', $values) . ')';
}

function get_option($options, $name)
{
        if (isset($options[$name])) {
                return $options[$name];
        } else {
                return array();
        }
}

function course_details_form($code, $title, $fac, $prof, $type, $lang, $vis, $desc)
{
        global $langInfo1, $langInfo2, $langCourseCode, $langLanguage, $langTitle,
               $langCourseDescription, $langFaculty, $langCourseOldFac, $langCourseVis,
               $langTeacher, $langCourseType, $langUsersWillAdd, $langUserPrefix,
               $langOk;

	// find available languages
	$languages = array();
        foreach ($GLOBALS['active_ui_languages'] as $langcode) {
                $entry = langcode_to_name($langcode);
                if (isset($langNameOfLang[$entry])) {
                        $languages[$entry] = $langNameOfLang[$entry];
                } else {
                        $languages[$entry] = $entry;
                }
	}

        return "<form action='$_SERVER[PHP_SELF]' method='post'>
                <table width='99%' class='tbl'><tbody>
                   <tr><td align='justify' colspan='2'>$langInfo1</td></tr>
                   <tr><td align='justify' colspan='2'>$langInfo2</td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><td>$langCourseCode:</td>
                       <td><input type='text' name='course_code' value='".q($code)."' /></td></tr>
                   <tr><td>$langLanguage:</td>
                       <td>".selection($languages, 'course_lang', $lang)."</td></tr>
                   <tr><td>$langTitle:</td>
                       <td><input type='text' name='course_title' value='".q($title)."' size='50' /></td></tr>
                   <tr><td>$langCourseDescription:</td>
                       <td><input type='text' name='course_desc' value='".q($desc)."' size='50' /></td></tr>
                   <tr><td>$langFaculty:</td><td>".faculty_select($fac)."</td></tr>
                   <tr><td>$langCourseOldFac:</td><td>$fac</td></tr>
                   <tr><td>$langCourseVis:</td><td>".visibility_select($vis)."</td></tr>
                   <tr><td>$langTeacher:</td>
                       <td><input type='text' name='course_prof' value='$prof' size='50' /></td></tr>
                   <tr><td>$langCourseType:</td><td>".type_select($type)."</td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><td colspan='2'><input type='checkbox' name='course_addusers' />$langUsersWillAdd </td></tr>
                   <tr><td colspan='2'><input type='checkbox' name='course_prefix' />$langUserPrefix</td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><td>
                      <input type='submit' name='create_restored_course' value='$langOk' />
                      <input type='hidden' name='restoreThis' value='$_POST[restoreThis]' /></td></tr>
                </tbody></table>
                </form>";
}

function restore_users($course_id, $users) {
	global $mysqlMainDb, $course_prefix, $course_addusers, $durationAccount, $version,
	       $langUserWith, $langAlready, $langWithUsername, $langUserisAdmin, $langUsernameSame,
               $langUserAlready, $langUName, $tool_content;

        foreach ($users as $data) {
                $u = mysql_query("SELECT * FROM `$mysqlMainDb`.user WHERE BINARY username=".quote($data['username']));
                if (mysql_num_rows($u) > 0) {
                        $res = mysql_fetch_array($u);
                        $userid_map[$data['user_id']] = $res['user_id'];
                        $tool_content .= "<p><b>" . q($login) . "</b>: $langUserAlready. <i>" .
                                         q("$res[nom] $res[prenom]") . "</i>!</p>\n";
                } else {
                        if ($course_addusers) {
                                db_query("INSERT INTO `$mysqlMainDb`.user
                                                 SET nom = ".quote($data['nom']).",
                                                     prenom = ".quote($data['prenom']).",
                                                     username = ".quote($data['username']).",
                                                     password = ".quote($data['password']).",
                                                     email = ".quote($data['email']).",
                                                     statut = ".quote($data['statut']).",
                                                     phone = ".quote($data['phone']).",
                                                     department = ".quote($data['department']).",
                                                     registered_at = ".quote($data['registered_at']));
                                $userid_map[$userid] = mysql_insert_id();
                        }
                }

        }
}

function register_users($course_id, $userid_map, $cours_user)
{
        global $mysqlMainDb, $langPrevId, $langNewId;

        foreach ($cours_user as $cudata) {
                $old_id = $cudata['user_id'];
                if (isset($userid_map[$old_id])) {
                        $statut[$old_id] = $cudata['statut'];
                        $tutor[$old_id] = $cudata['tutor'];
                        $reg_date[$old_id] = $cudata['reg_date'];
                        $receive_mail[$old_id] = $cudata['receive_mail'];
                }
        }

        foreach ($userid_map as $old_id => $new_id) {
                db_query("INSERT INTO `$mysqlMainDb`.cours_user
                                 SET cours_id = $course_id,
                                     user_id = $new_id,
                                     statut = $statut[$new_id],
                                     reg_date = ".q($reg_date[$new_id]).",
                                     receive_mail = $receive_mail[$new_id]");
                $tool_content .=  "<p>$langPrevId=$old_id, $langNewId=$new_id</p>\n";
        }
}


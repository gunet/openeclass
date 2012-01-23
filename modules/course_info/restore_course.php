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

$require_power_user = true;
include '../../include/baseTheme.php';
include '../../upgrade/upgrade_functions.php';
include '../../include/lib/fileUploadLib.inc.php';
include '../../include/lib/fileManageLib.inc.php';
include '../../include/lib/forcedownload.php';
include '../../include/pclzip/pclzip.lib.php';

$nameTools = $langRestoreCourse;
$navigation[] = array('url' => '../admin/index.php', 'name' => $langAdmin);

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
                                        'course_type' => true), 'all', 'autounquote');
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
                $cours_user = unserialize(file_get_contents($_POST['restoreThis'] . '/cours_user'));
                $userid_map = restore_users($course_id, unserialize(file_get_contents($user_file)),
                                            $cours_user);
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
	$tool_content .= "<p>$langCopyFiles $coursedir</p>";
	$action = 1;
	// now we include the file for restoring
	ob_start();
	include("$restoreThis/backup.php");

        load_global_messages();

        if (mysql_table_exists($new_course_code, 'dropbox_file')) {
                mysql_select_db($new_course_code);
                map_db_field('dropbox_file', 'uploaderId', $userid_map);
                map_db_field('dropbox_person', 'personId', $userid_map);
                map_db_field('dropbox_post', 'recipientId', $userid_map);
        }
        
        if (!isset($eclass_version)) {
                // if we come from older versions, do all upgrades
                upgrade_course($new_course_code, $course_lang);
        } else {
                if (mysql_table_exists($new_course_code, 'student_group')) {
                        if (file_exists("$restoreThis/group_members")) {
                                // new-style backup - student_group shouldn't exist
                                db_query('DROP TABLE student_group');
                        } else {
                                map_table_field('student_group', 'id', 'tutor', $userid_map);
                        }
                }
		if ($eclass_version < '2.2') { // if we come from 2.1.x
                        upgrade_course_2_2($new_course_code, $course_lang);
                }
                if ($eclass_version < '2.3') {
                        upgrade_course_2_3($new_course_code);
                }
                if ($eclass_version < '2.4') {
                        upgrade_course_2_4($new_course_code, $course_lang);
                }
                if ($eclass_version < '2.5') {
                        upgrade_course_2_5($new_course_code, $course_lang);
                }
                if ($eclass_version < '3.0') {
                    list($video_map, $videolinks_map, $lp_learnPath_map) = upgrade_course_3_0($new_course_code, $course_lang, null, true);
                }
	}
        convert_description_to_units($new_course_code, $course_id);
	$tool_content .= ob_get_contents();
	ob_end_clean();
        
        if (file_exists("$restoreThis/cours")) {
                // New-style backup - restore intividual tables

                if (file_exists("$restoreThis/config_vars")) {
                        $config_data = unserialize(file_get_contents("$restoreThis/config_vars"));
                        $course_data = unserialize(file_get_contents("$restoreThis/cours"));
                        $url_prefix_map = array(
                                $config_data['urlServer'] . 'modules/ebook/show.php/' . $course_data[0]['code'] =>
                                        $urlServer . 'modules/ebook/show.php/' . $new_course_code,
                                $config_data['urlAppend'] . '/modules/ebook/show.php/' . $course_data[0]['code'] =>
                                        $urlAppend . '/modules/ebook/show.php/' . $new_course_code,
                                $config_data['urlServer'] . 'modules/document/file.php/' . $course_data[0]['code'] =>
                                        $urlServer . 'modules/document/file.php/' . $new_course_code,
                                $config_data['urlAppend'] . '/modules/document/file.php/' . $course_data[0]['code'] =>
                                        $urlAppend . '/modules/document/file.php/' . $new_course_code,
                                $config_data['urlServer'] . 'courses/' . $course_data[0]['code'] =>
                                        $urlServer . 'courses/' . $new_course_code,
                                $config_data['urlAppend'] . '/courses/' . $course_data[0]['code'] =>
                                        $urlAppend . '/courses/' . $new_course_code,
                                $course_data[0]['code'] =>
                                        $new_course_code);
                }

                function document_map_function(&$data, $maps) {
                        // $maps[1]: group map, $maps[2]: ebook map
                        $stype = $data['subsystem'];
                        $sid = $data['subsystem_id'];
                        if ($stype > 0) {
                                if (isset($maps[$stype][$sid])) {
                                        $data['subsystem_id'] = $maps[$stype][$sid];
                                } else {
                                        return false;
                                }
                        }
                        return true;
                }

                function unit_map_function(&$data, $maps) {
                        list($document_map, $link_category_map, $link_map, $ebook_map, $section_map, $subsection_map, $video_map, $videolinks_map, $lp_learnPath_map) = $maps;
                        $type = $data['type'];
                        if ($type == 'doc') {
                                $data['res_id'] = $document_map[$data['res_id']];
                        } elseif ($type == 'linkcategory') {
                                $data['res_id'] = $link_category_map[$data['res_id']];
                        } elseif ($type == 'link') {
                                $data['res_id'] = $link_map[$data['res_id']];
                        } elseif ($type == 'ebook') {
                                $data['res_id'] = $ebook_map[$data['res_id']];
                        } elseif ($type == 'section') {
                                $data['res_id'] = $section_map[$data['res_id']];
                        } elseif ($type == 'subsection') {
                                $data['res_id'] = $subsection_map[$data['res_id']];
                        } elseif ($type == 'description') {
                                $data['res_id'] = intval($data['res_id']);
                        } elseif ($type == 'video') {
                                $data['res_id'] = $video_map[$data['res_id']];
                        } elseif ($type == 'videolinks') {
                                $data['res_id'] = $videolinks_map[$data['res_id']];
                        } elseif ($type == 'lp') {
                                $data['res_id'] = $lp_learnPath_map[$data['res_id']];
                        }
                        return true;
                }

                mysql_select_db($mysqlMainDb);
                restore_table($restoreThis, 'annonces',
                        array('set' => array('cours_id' => $course_id),
                              'delete' => array('id')));
                restore_table($restoreThis, 'group_properties',
                        array('set' => array('course_id' => $course_id)));
                $group_map = restore_table($restoreThis, 'group',
                        array('set' => array('course_id' => $course_id),
                              'return_mapping' => 'id'));
                restore_table($restoreThis, 'group_members',
                        array('map' => array('group_id' => $group_map,
                                             'user_id' => $userid_map)));
                restore_table($restoreThis, 'forum_notify',
                        array('set' => array('course_id' => $course_id),
                              'map' => array('user_id' => $userid_map),
                              'delete' => array('id')));
                $link_category_map = restore_table($restoreThis, 'link_category',
                        array('set' => array('course_id' => $course_id),
                              'return_mapping' => 'id'));
                $link_category_map[0] = 0;
                $link_map = restore_table($restoreThis, 'link',
                        array('set' => array('course_id' => $course_id),
                              'map' => array('category' => $link_category_map),
                              'return_mapping' => 'id'));
                $ebook_map = restore_table($restoreThis, 'ebook',
                        array('set' => array('course_id' => $course_id),
                              'return_mapping' => 'id'));
                foreach ($ebook_map as $old_id => $new_id) {
                        rename("$coursedir/ebook/$old_id", "$coursedir/ebook/$new_id");
                }
                $document_map = restore_table($restoreThis, 'document',
                        array('set' => array('course_id' => $course_id),
                              'map_function' => 'document_map_function',
                              'map_function_data' => array(1 => $group_map, 2 => $ebook_map),
                              'return_mapping' => 'id'));
                $ebook_section_map = restore_table($restoreThis, 'ebook_section',
                        array('map' => array('ebook_id' => $ebook_map),
                              'return_mapping' => 'id'));
                $ebook_subsection_map = restore_table($restoreThis, 'ebook_subsection',
                        array('map' => array('section_id' => $ebook_section_map,
                                             'file_id' => $document_map),
                              'delete' => array('file'),
                              'return_mapping' => 'id'));
                if (file_exists("$restoreThis/video"))
                    $video_map = restore_table($restoreThis, 'video',
                        array('set' => array('course_id' => $course_id),
                              'return_mapping' => 'id'));
                if (file_exists("$restoreThis/videolinks"))
                    $videolinks_map  = restore_table($restoreThis, 'videolinks',
                        array('set' => array('course_id' => $course_id),
                              'return_mapping' => 'id'));
                if (file_exists("$restoreThis/dropbox_file") && 
                    file_exists("$restoreThis/dropbox_person") && 
                    file_exists("$restoreThis/dropbox_post"))
                {
                    $dropbox_map = restore_table($restoreThis, 'dropbox_file',
                        array('set' => array('course_id' => $course_id),
                              'map' => array('uploaderId' => $userid_map),
                              'return_mapping' => 'id'));
                    restore_table($restoreThis, 'dropbox_person',
                        array('map' => array('fileId' => $dropbox_map,
                                             'personId' => $userid_map)));
                    restore_table($restoreThis, 'dropbox_post',
                        array('map' => array('fileId' => $dropbox_map,
                                             'recipientId' => $userid_map)));
                }
                if (file_exists("$restoreThis/lp_learnPath") &&
                    file_exists("$restoreThis/lp_module") &&
                    file_exists("$restoreThis/lp_asset") &&
                    file_exists("$restoreThis/lp_rel_learnPath_module") &&
                    file_exists("$restoreThis/lp_user_module_progress"))
                {
                    $lp_learnPath_map = restore_table($restoreThis, 'lp_learnPath',
                        array('set' => array('course_id' => $course_id),
                              'return_mapping' => 'learnPath_id'));
                    $lp_module_map = restore_table($restoreThis, 'lp_module',
                        array('set' => array('course_id' => $course_id),
                              'return_mapping' => 'module_id'));
                    $lp_asset_map = restore_table($restoreThis, 'lp_asset',
                        array('map' => array('module_id' => $lp_module_map),
                              'return_mapping' => 'asset_id'));
                    // update lp_module startAsset_id with new asset_id from map
                    foreach ($lp_asset_map as $key => $value) {
                        $result = db_query("UPDATE `$mysqlMainDb`.lp_module SET `startAsset_id` = $value
                                					WHERE `course_id` = $course_id AND `startAsset_id` = $key");
                    }
                    $lp_rel_learnPath_module_map = restore_table($restoreThis, 'lp_rel_learnPath_module',
                        array('map' => array('learnPath_id' => $lp_learnPath_map,
                                             'module_id' => $lp_module_map),
                              'return_mapping' => 'learnPath_module_id'));
                    // update parent
                    foreach ($lp_rel_learnPath_module_map as $key => $value) {
                        $result = db_query("UPDATE `$mysqlMainDb`.lp_rel_learnPath_module SET `parent` = $value
                                					WHERE `learnPath_id` IN (SELECT learnPath_id FROM `$mysqlMainDb`.lp_learnPath WHERE course_id = $course_id) 
                                					AND `parent` = $key");
                    }
                    restore_table($restoreThis, 'lp_user_module_progress',
                        array('delete' => array('user_module_progress_id'),
                              'map' => array('user_id' => $userid_map,
                                             'learnPath_module_id' => $lp_rel_learnPath_module_map,
                                             'learnPath_id' => $lp_learnPath_map)));
                }
                $unit_map = restore_table($restoreThis, 'course_units',
                        array('set' => array('course_id' => $course_id),
                              'return_mapping' => 'id'));
                restore_table($restoreThis, 'unit_resources',
                        array('delete' => array('id'),
			      'map' => array('unit_id' => $unit_map),
                              'map_function' => 'unit_map_function',
                              'map_function_data' => array($document_map,
                                                           $link_category_map,
                                                           $link_map,
                                                           $ebook_map,
                                                           $ebook_section_map,
                                                           $ebook_subsection_map,
                                                           $video_map,
                                                           $videolinks_map,
                                                           $lp_learnPath_map)));
        }
        
	removeDir($restoreThis);
        $tool_content .= "</p><br />
                          <center><p><a href='../admin/index.php'>$langBack</a></p></center>";
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
                $faculte_data = unserialize(file_get_contents($_POST['restoreThis'] . '/faculte'));
                $old_faculte = $faculte_data[0]['name'];
                $tool_content = course_details_form($data['fake_code'], $data['intitule'],
                                                    $old_faculte, $data['titulaires'],
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
        <table width='100%' class='tbl'><tr>
          <td>$langRequest1
	  <br /><br />
	  <form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	    <input type='file' name='archiveZipped' />
	    <input type='submit' name='send_archive' value='".$langSend."' />
            </form>
            <div class='right smaller'>$langMaxFileSize ".
                       ini_get('upload_max_filesize') . "</div>
        </td>
        </tr></table>
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

	global $action, $langNameOfLang, $version;
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

        $code = inner_unquote($code);
        $title = inner_unquote($title);
        $desc = inner_unquote($desc);
        $prof = inner_unquote($prof);
        $fac = inner_unquote($fac);

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
			quote(inner_unquote($title)),
			quote(inner_unquote($text)),
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
			quote(inner_unquote($title)),
			quote(inner_unquote($comments)),
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
function user($userid, $name, $surname, $login, $password, $email, $statut, $phone, $department,
              $registered_at = NULL, $expires_at = NULL, $inst_id = NULL)
{
        global $action, $new_course_code, $course_id, $userid_map,
               $mysqlMainDb, $durationAccount, $version, $langUserWith, $langAlready,
               $langWithUsername, $langUserisAdmin, $langUsernameSame,
               $langRestoreUserExists, $langRestoreUserNew,
               $langUsername, $langPrevId, $langNewId, $langUserName;

        $name = inner_unquote($name);
        $surname = inner_unquote($surname);
        $login = inner_unquote($login);

	if (!$action or $_POST['add_users'] == 'none' or
            ($_POST['add_users'] == 'prof' and $statut != 1)) {
                return;
        }
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

	$u = db_query("SELECT * FROM `$mysqlMainDb`.user WHERE BINARY username=".quote($login));
	if (mysql_num_rows($u) > 0) {
		$res = mysql_fetch_array($u);
		$userid_map[$userid] = $res['user_id'];
		echo sprintf($langRestoreUserExists,
                             '<b>' . q($login) . '</b>',
                             '<i>' . q("$res[prenom] $res[nom]") . '</i>',
                             '<i>' . q("$name $surname") . '</i>'), '<br>';
	} elseif (isset($_POST['create_users'])) {
		if ($version == 1) { // if we come from a archive < 2.x encrypt user password
			$password = md5($password);
		}
		db_query("INSERT INTO `$mysqlMainDb`.user
			(nom, prenom, username, password, email, statut, phone, department, registered_at, expires_at, description)
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
				", '')");
		$userid_map[$userid] = mysql_insert_id();
		echo sprintf($langRestoreUserNew,
                             '<b>' . q($login), '</b>',
                             '<i>' . q("$name $surname") . '</i>'), '<br>';
	} else {
                return;
        }

	db_query("INSERT INTO `$mysqlMainDb`.cours_user
		(cours_id, user_id, statut, reg_date)
		VALUES ($course_id, $userid_map[$userid], $statut, NOW())");
	echo q("$langUsername=$login, $langPrevId=$userid, $langNewId=$userid_map[$userid]"),
             "<br>\n";
}

function query($sql)
{
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
	global $action, $userid_map, $new_course_code;

	if (!$action or $_POST['add_users'] == 'none' or
            !isset($userid_map[$userid])) {
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
function dropbox_file($userid, $filename, $filesize, $title, $description, $author, $uploadDate, $lastUploadDate)
{
	global $action, $userid_map, $new_course_code, $langDropBoxIncompatible;
        static $warned = false;

        if (!$warned) {
                echo "<br>$langDropBoxIncompatible";
        }
	if (!$action or $_POST['add_users'] == 'none' or
            !isset($userid_map[$userid])) {
		return;
	}
	mysql_select_db($new_course_code);
	db_query("INSERT into dropbox_file (uploaderId, filename, filesize, title, description,
                                            author, uploadDate, lastUploadDate)
                         VALUES (".
                         join(', ', array($userid_map[$userid],
                                          quote($filename),
                                          quote($filesize),
                                          quote($title),
                                          quote($description),
                                          quote($author),
                                          quote($uploadDate),
                                          quote($lastUploadDate))) . ')');
}

function dropbox_person($file_id, $person_id)
{
	global $action, $userid_map, $new_course_code;

	if (!$action or $_POST['add_users'] == 'none' or
            !isset($userid_map[$person_id])) {
		return;
	}
	mysql_select_db($new_course_code);
        $file_id = intval($file_id);
        $new_uid = $userid_map[$person_id];
	db_query("INSERT INTO dropbox_person (fileId, personId)
                         VALUES ($file_id, $new_uid)");
}

function dropbox_post($file_id, $recipient_id)
{
	global $action, $userid_map, $new_course_code, $course_addusers;

	if (!$action or $_POST['add_users'] == 'none' or
            !isset($userid_map[$recipient_id])) {
		return;
	}
	mysql_select_db($new_course_code);
        $file_id = intval($file_id);
        $new_uid = $userid_map[$recipient_id];
	db_query("INSERT INTO dropbox_post (fileId, recipientId)
                         VALUES ($file_id, $new_uid)");
}


// insert an assignment submission, translating user id's
function assignment_submit($userid, $assignment_id, $submission_date,
	$submission_ip, $file_path, $file_name, $comments,
	$grade, $grade_comments, $grade_submission_date,
	$grade_submission_ip)
{
	global $action, $userid_map, $new_course_code, $course_addusers;

	if (!$action or $_POST['add_users'] == 'none' or
            !isset($userid_map[$userid])) {
		return;
	}

	mysql_select_db($new_course_code);
	$values = array();
	foreach (array($assignment_id, $submission_date,
                       $submission_ip, $file_path, $file_name, $comments,
                       $grade, $grade_comments, $grade_submission_date,
                       $grade_submission_ip) as $v) {
		$values[] = quote($v);
	}
	db_query("INSERT INTO assignment_submit (uid, assignment_id, submission_date,
                                                 submission_ip, file_path, file_name,
                                                 comments, grade, grade_comments,
                                                 grade_submission_date, grade_submission_ip)
                         VALUES (" . quote($userid_map[$userid]). ", ".
                                     join(', ', $values) . ')');
}

// creating course and inserting entries into the main database
function create_course($code, $lang, $title, $desc, $fac, $vis, $prof, $type) {
	global $mysqlMainDb;

        $fac = intval($fac);
	$repertoire = new_code($fac);

	if (mysql_select_db($repertoire)) {
		echo $langCourseExists;
		exit;
        }
	db_query("INSERT into `$mysqlMainDb`.cours
		(code, languageCourse, intitule, description, faculteid, visible, titulaires, fake_code, type)
		VALUES (" .
		join(', ', array(
			quote($repertoire),
			quote($lang),
			quote($title),
			quote($desc),
			$fac,
			quote($vis),
			quote($prof),
			quote($code),
			quote($type))).
                ')');
        $cid = mysql_insert_id();

	if (!db_query("CREATE DATABASE `$repertoire`")) {
		echo "Database $repertoire creation failure ";
		exit;
	}
	return array($repertoire, $cid);
}

// Create course index.php file
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

	$ret = "<select name='course_type'>\n";
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

	$ret = "<select name='course_fac'>\n";
	$res = db_query("SELECT id, name FROM `$mysqlMainDb`.faculte ORDER BY number");
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
        $checked = ' checked';
        foreach (find_backup_folders($destdir) as $folder) {
                $path = q($folder['path'] . '/' . $folder['dir']);
                $file = q($folder['dir']);
                $course = q(preg_replace('|^.*/|', '', $folder['path']));
                $retString .= "<li>$langLesFiles <input type='radio' name='restoreThis' value='$path'$checked>
                        <b>$course</b> ($file)</li>\n";
                $checked = '';
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
                                        fix_short_open_tag("$entry/backup.php");
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
        global $url_prefix_map;

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
		if (isset($options['delete'])) {
                        foreach ($options['delete'] as $field) {
                                unset($data[$field]);
                        }
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
                                        continue 2; 	 
                                }
                        }
                }
                $do_insert = true;
                if (isset($options['map_function'])) {
                        if (isset($options['map_function_data'])) {
                                $do_insert = $options['map_function']($data, $options['map_function_data']);
                        } else {
                                $do_insert = $options['map_function']($data);
                        }
                }
                if ($do_insert) {
                        if (isset($url_prefix_map)) {
                                db_query(strtr($sql_intro . field_values($data, $set),
                                               $url_prefix_map));
                        } else {
                                db_query($sql_intro . field_values($data, $set));
                        }
                }
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
               $langTeacher, $langCourseType, $langUsersWillAdd,
               $langOk, $langAll, $langsTeachers, $langMultiRegType, $langActivate,
               $langNone;

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

        return "<p>$langInfo1</p>
                <p>$langInfo2</p>
                <form action='$_SERVER[PHP_SELF]' method='post'>
                <table width='99%' class='tbl'><tbody>
                   <tr><td>&nbsp;</td></tr>
                   <tr><th>$langCourseCode:</th>
                       <td><input type='text' name='course_code' value='".q($code)."' /></td></tr>
                   <tr><th>$langLanguage:</th>
                       <td>".selection($languages, 'course_lang', $lang)."</td></tr>
                   <tr><th>$langTitle:</th>
                       <td><input type='text' name='course_title' value='".q($title)."' size='50' /></td></tr>
                   <tr><th>$langCourseDescription:</th>
                       <td><input type='text' name='course_desc' value='".q($desc)."' size='50' /></td></tr>
                   <tr><th>$langFaculty:</th><td>".faculty_select($fac)."</td></tr>
                   <tr><th>$langCourseOldFac:</th><td>$fac</td></tr>
                   <tr><th>$langCourseVis:</th><td>".visibility_select($vis)."</td></tr>
                   <tr><th>$langTeacher:</th>
                       <td><input type='text' name='course_prof' value='".q($prof)."' size='50' /></td></tr>
                   <tr><th>$langCourseType:</th><td>".type_select($type)."</td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><th>$langUsersWillAdd:</th>
                       <td><input type='radio' name='add_users' value='all' id='add_users_all'>
                           <label for='add_users_all'>$langAll</label><br>
                           <input type='radio' name='add_users' value='prof' id='add_users_prof' checked>
                           <label for='add_users_prof'>$langsTeachers</label><br>
                           <input type='radio' name='add_users' value='none' id='add_users_none'>
                           <label for='add_users_none'>$langNone</label></td></tr>
                   <tr><th>$langMultiRegType:</th>
                       <td><input type='checkbox' name='create_users' value='1' id='create_users'>
                           <label for='create_users'>$langActivate</label></td></tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr><td colspan='2'>
                      <input type='submit' name='create_restored_course' value='$langOk' />
                      <input type='hidden' name='restoreThis' value='$_POST[restoreThis]' /></td></tr>
                </tbody></table>
                </form>";
}

function restore_users($course_id, $users, $cours_user)
{
	global $tool_content, $mysqlMainDb, $durationAccount, $version,
               $langUserWith, $langWithUsername, $langUserisAdmin,
               $langUsernameSame, $langRestoreUserExists,
               $langRestoreUserNew;

        $userid_map = array();
        if ($_POST['add_users'] == 'none') {
                return $userid_map;
        }

        if ($_POST['add_users'] == 'prof') {
                $add_only_profs = true;
                foreach ($cours_user as $cu_info) {
                        $is_prof[$cu_info['user_id']] = ($cu_info['statut'] == 1);
                }
        } else {
                $add_only_profs = false;
        }

        foreach ($users as $data) {

                if ($add_only_profs and !$is_prof[$data['user_id']]) {
                        continue;
                }
                $u = db_query("SELECT * FROM `$mysqlMainDb`.user WHERE BINARY username=".quote($data['username']));
                if (mysql_num_rows($u) > 0) {
                        $res = mysql_fetch_array($u);
                        $userid_map[$data['user_id']] = $res['user_id'];
                        $tool_content .= "<p>" .
                                         sprintf($langRestoreUserExists,
                                                 '<b>' . q($data['username']) . '</b>',
                                                 '<i>' . q("$res[prenom] $res[nom]") . '</i>',
                                                 '<i>' . q("$data[prenom] $data[nom]") . '</i>') .
                                         "</p>\n";
                } elseif (isset($_POST['create_users'])) {                        
                        db_query("INSERT INTO `$mysqlMainDb`.user
                                         SET nom = ".quote($data['nom']).",
                                             prenom = ".quote($data['prenom']).",
                                             username = ".quote($data['username']).",
                                             password = ".quote($data['password']).",
                                             email = ".quote($data['email']).",
                                             statut = ".quote($data['statut']).",
                                             phone = ".quote($data['phone']).",
                                             department = ".quote($data['department']).",
                                             registered_at = ".quote($data['registered_at']).",
                                             expires_at = ". quote($data['registered_at']+ $durationAccount));
                        $userid_map[$data['userid']] = mysql_insert_id();
                        $tool_content .= "<p>" .
                                         sprintf($langRestoreUserNew,
                                                 '<b>' . q($data['username']) . '</b>',
                                                 '<i>' . q("$data[prenom] $data[nom]") . '</i>') .
                                         "</p>\n";
                }

        }
        return $userid_map;
}

function register_users($course_id, $userid_map, $cours_user)
{
        global $mysqlMainDb, $langPrevId, $langNewId, $tool_content;

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
                                     statut = $statut[$old_id],
                                     reg_date = ".quote($reg_date[$old_id]).",
                                     receive_mail = $receive_mail[$old_id]");
                $tool_content .=  "<p>$langPrevId=$old_id, $langNewId=$new_id</p>\n";
        }
}

function fix_short_open_tag($file)
{
        $f = fopen($file, 'r');
        $line = fgets($f);
        if (!(strpos($line, '<?php') === 0)) {
                $line = "<?php\n";
                $out = fopen($file . '.bak', 'w');
                fputs($out, $line);
                while (!feof($f)) {
                        fwrite($out, fread($f, 8192));
                }
                fclose($f);
                fclose($out);
                rename($file . '.bak', $file);
        }
}

function map_table_field($table, $id, $field, $map)
{
        $q = db_query("SELECT `$id`, `$field` FROM `$table`");
        while ($r = mysql_fetch_row($q)) {
                db_query("UPDATE `$table` SET `$field` = " . $map[$r[1]] . " WHERE `$id` = " . $r[0]);
        }
}

function inner_unquote($s)
{
        global $encoding;

        if ($encoding != 'UTF-8') {
		$s = iconv($encoding, 'UTF-8', $s);
        }

        return str_replace(array('\"', "\\\0"),
                           array('"', "\0"),
                           $s);

}

function map_db_field($table, $field, $mapping) {
        foreach ($mapping as $old => $new) {
               db_query("UPDATE `$table` SET `$field` = " . quote($new) . "
                             WHERE `$field` = " . quote($old));
        }
}

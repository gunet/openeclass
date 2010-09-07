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


/*===========================================================================
document.php
 * @version $Id$
@last update: 20-12-2006 by Evelthon Prodromou
@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
*/

$require_current_course = TRUE;
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/forcedownload.php';
include "../../include/lib/fileDisplayLib.inc.php";
include "../../include/lib/fileManageLib.inc.php";
include "../../include/lib/fileUploadLib.inc.php";

if (!defined('GROUP_DOCUMENTS')) {
        define('GROUP_DOCUMENTS', false);
}

/**** The following is added for statistics purposes ***/
include '../../include/action.php';
$action = new action();

if (GROUP_DOCUMENTS) {
        include '../group/group_functions.php';
        $action->record('MODULE_ID_GROUPS');
        mysql_select_db($mysqlMainDb);

        initialize_group_id('gid');
        initialize_group_info($group_id);
        $navigation[] = array ('url' => 'group.php', 'name' => $langGroups);
        $navigation[] = array ('url' => 'group_space.php?userGroupId=' . $group_id, 'name' => q($name));
        $groupset = "gid=$group_id&amp;";
        $base_url = $_SERVER['PHP_SELF'] . '?' . $groupset;
        $group_sql = "group_id = $group_id";
        $group_hidden_input = "<input type='hidden' name='gid' value='$group_id' />";
        $basedir = $webDir . 'courses/' . $currentCourseID . '/group/' . $secret_directory;
} else {
        $action->record('MODULE_ID_DOCS');
        mysql_select_db($mysqlMainDb);

        $base_url = $_SERVER['PHP_SELF'] . '?';
        $group_id = 'NULL';
        $groupset = '';
        $group_sql = "group_id IS NULL";
        $group_hidden_input = '';
        $basedir = $webDir . 'courses/' . $currentCourseID . '/document';
}

$tool_content = "";
$nameTools = $langDoc;

$require_help = TRUE;
$helpTopic = 'Doc';

// check for quotas
$diskUsed = dir_total_space($basedir);
$type = GROUP_DOCUMENTS? 'group_quota': 'doc_quota';
$d = mysql_fetch_row(mysql_query("SELECT $type FROM cours WHERE cours_id = $cours_id"));
$diskQuotaDocument = $d[0];

if (isset($_GET['showQuota'])) {
        $nameTools = $langQuotaBar;
        if (GROUP_DOCUMENTS) {
        	$navigation[] = array ('url' => 'document.php?gid=' . $group_id, 'name' => $langDoc);
        } else {
        	$navigation[] = array ('url' => 'document.php', 'name' => $langDoc);
        }
	$tool_content .= showquota($diskQuotaDocument, $diskUsed);
	draw($tool_content, 2);
	exit;
}

// -------------------------
// download action2
// --------------------------
if (@$action2=="download")
{
	$real_file = $basedir . $id;
	if (strpos($real_file, '/../') === FALSE) {
		//fortwma tou pragmatikou onomatos tou arxeiou pou vrisketai apothikevmeno sth vash
                $result = db_query ("FIXME SELECT filename FROM document
                                                        WHERE course_id = $cours_id AND
                                                              path = '$id'");
		$row = mysql_fetch_array($result);
		if (!empty($row['filename']))
		{
			$id = $row['filename'];
		}
		send_file_to_client($real_file, my_basename($id));
		exit;
	} else {
		header("Refresh: ${urlServer}modules/document/document.php");
	}
}


if($is_adminOfCourse)  {
	if (@$uncompress == 1)
		include("../../include/pclzip/pclzip.lib.php");
}

// file manager basic variables definition
$local_head = '
<script type="text/javascript">
function confirmation (name)
{
    if (confirm("'.$langConfirmDelete.'" + name))
        {return true;}
    else
        {return false;}
}
</script>
';


// Actions to do before extracting file from zip archive
// Create database entries and set extracted file path to
// a new safe filename
function process_extracted_file($p_event, &$p_header) {

        global $file_comment, $file_category, $file_creator, $file_date, $file_subject,
               $file_title, $file_description, $file_author, $file_language,
               $file_copyrighted, $uploadPath, $realFileSize, $basedir, $cours_id,
               $group_id;

        $realFileSize += $p_header['size'];
        $stored_filename = $p_header['stored_filename'];
        if (invalid_utf8($stored_filename)) {
                $stored_filename = cp737_to_utf8($stored_filename);
        }
        $path_components = explode('/', $stored_filename);
        $filename = array_pop($path_components);
        $file_date = date("Y\-m\-d G\:i\:s", $p_header['mtime']);
        $path = make_path($uploadPath, $path_components);
        if ($p_header['folder']) {
                // Directory has been created by make_path(),
                // no need to do anything else
                return 0;
        } else {
                $format = get_file_extension($filename);
                $path .= '/' . safe_filename($format);
                db_query("INSERT INTO document SET
                                 course_id = $cours_id,
                                 group_id = $group_id,
                                 path = '$path',
                                 filename = " . quote($filename) .",
                                 visibility = 'v',
                                 comment = " . quote($file_comment) . ",
                                 category = " . quote($file_category) . ",
                                 title = " . quote($file_title) . ",
                                 creator = " . quote($file_creator) . ",
                                 date = " . quote($file_date) . ",
                                 date_modified = " . quote($file_date) . ",
                                 subject = " . quote($file_subject) . ",
                                 description = " . quote($file_description) . ",
                                 author = " . quote($file_author) . ",
                                 format = '$format',
                                 language = " . quote($file_language) . ",
                                 copyrighted = " . quote($file_copyrighted));
                // File will be extracted with new encoded filename
                $p_header['filename'] = $basedir . $path;
                return 1;
        }
}


// Create a path with directory names given in array $path_components
// under base path $path, inserting the appropriate entries in 
// document table.
// Returns the full encoded path created.
function make_path($path, $path_components)
{
        global $basedir, $nom, $prenom, $path_already_exists, $cours_id, $group_id, $group_sql;

        $path_already_exists = true;
        $depth = 1 + substr_count($path, '/');
        foreach ($path_components as $component) {
                $q = db_query("SELECT path, visibility, format,
                                      (LENGTH(path) - LENGTH(REPLACE(path, '/', ''))) AS depth
                                      FROM document
                                      WHERE course_id = $cours_id AND $group_sql AND
                                            filename = " . quote($component) . " AND
                                            path LIKE '$path%' HAVING depth = $depth");
                if (mysql_num_rows($q) > 0) {
                        // Path component already exists in database
                        $r = mysql_fetch_array($q);
                        $path = $r['path'];
                        $depth++;
                } else {
                        // Path component must be created
                        $path .= '/' . safe_filename();
                        mkdir($basedir . $path, 0775);
                        db_query("INSERT INTO document SET
                                          course_id = $cours_id,
                                          group_id = $group_id,
                                          path='$path',
                                          filename=" . quote($component) . ",
                                          visibility='v',
                                          creator=" . quote($prenom." ".$nom) . ",
                                          date=NOW(),
                                          date_modified=NOW(),
                                          format='.dir'");
                        $path_already_exists = false;
                }
        }
        return $path;
}


// Used in documents path navigation bar
function make_clickable_path($path)
{
	global $langRoot, $userGroupId, $base_url, $group_sql;

	$cur = $out = '';
	foreach (explode('/', $path) as $component) {
		if (empty($component)) {
			$out = "<a href='{$base_url}openDir=/'>$langRoot</a>";
		} else {
			$cur .= rawurlencode("/$component");
			$row = mysql_fetch_array(db_query ("SELECT filename FROM document
					WHERE path LIKE '%/$component' AND $group_sql"));
			$dirname = q($row['filename']);
			$out .= " &raquo; <a href='{$base_url}openDir=$cur'>$dirname</a>";
		}
	}
	return $out;
}


/*** clean information submited by the user from antislash ***/
// stripSubmitValue($_POST);
// stripSubmitValue($_GET);
/*****************************************************************************/

if($is_adminOfCourse) {
	/*********************************************************************
	UPLOAD FILE

        Ousiastika dhmiourgei ena safe_fileName xrhsimopoiwntas ta DATETIME
        wste na mhn dhmiourgeitai provlhma sto filesystem apo to onoma tou
        arxeiou. Parola afta to palio filename pernaei apo 'filtrarisma' wste
        na apofefxthoun 'epikyndynoi' xarakthres.
	***********************************************************************/

	$dialogBox = '';
	if (isset($_FILES['userFile']) and is_uploaded_file($_FILES['userFile']['tmp_name'])) {
                $userFile = $_FILES['userFile']['tmp_name'];
		// check for disk quotas
		$diskUsed = dir_total_space($basedir);
		if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaDocument) {
			$dialogBox .= "<p class='caution'>$langNoSpace</p>";
		} else {
                        if (unwanted_file($_FILES['userFile']['name'])) {
                                $dialogBox .= "$langUnwantedFiletype: {$_FILES['userFile']['name']}";
                        }
                        /*** Unzipping stage ***/
                        elseif (isset($_POST['uncompress']) and $_POST['uncompress'] == 1
                                and preg_match('/\.zip$/i', $_FILES['userFile']['name'])) {
                                $zipFile = new pclZip($userFile);
                                $realFileSize = 0;
                                $zipFile->extract(PCLZIP_CB_PRE_EXTRACT, 'process_extracted_file');
                                if ($diskUsed + $realFileSize > $diskQuotaDocument) {
                                        $dialogBox .= $langNoSpace;
                                } else {
                                        $dialogBox .= "<p class='success'>$langDownloadAndZipEnd</p><br />";
                                }
                        } else {
                                $error = false;
                                $fileName = canonicalize_whitespace($_FILES['userFile']['name']);
                                $uploadPath = $_POST['uploadPath'];
                                // Check if upload path exists
                                if (!empty($uploadPath)) {
                                        $result = mysql_fetch_row(db_query("SELECT count(*) FROM document
                                                        WHERE course_id = $cours_id AND $group_sql AND
                                                              path = " . autoquote($uploadPath)));
                                        if (!$result[0]) {
                                                $error = $langImpossible;
                                        }
                                }
                                if (!$error) {
                                        // Check if file already exists
                                        $result = db_query("SELECT filename FROM document WHERE
                                                                   course_id = $cours_id AND
                                                                   $group_sql AND
                                                                   path REGEXP '" . escapeSimple($uploadPath) . "/.*$' AND
                                                                   filename = " . autoquote($fileName));
                                        if (mysql_num_rows($result) > 0) {
                                                $error = $langFileExists;
                                        }
                                }
                                if (!$error) {
                                        //to arxeio den vrethike sth vash ara mporoume na proxwrhsoume me to upload
                                        /*** Try to add an extension to files witout extension ***/
                                        $fileName = add_ext_on_mime($fileName);
                                        /*** Handle PHP files ***/
                                        $fileName = php2phps($fileName);
                                        // to onoma afto tha xrhsimopoiei sto filesystem kai sto pedio path
                                        $safe_fileName = safe_filename(get_file_extension($fileName));
                                        //prosthiki eggrafhs kai metadedomenwn gia to eggrafo sth vash
                                        if ($uploadPath == ".") {
                                                $uploadPath2 = "/".$safe_fileName;
                                        } else {
                                                $uploadPath2 = $uploadPath."/".$safe_fileName;
                                        }
                                        // san file format vres to extension tou arxeiou
                                        $file_format = get_file_extension($fileName);
                                        // san date you arxeiou xrhsimopoihse thn shmerinh hm/nia
                                        $file_date = date("Y\-m\-d G\:i\:s");
                                        db_query("INSERT INTO document SET
                                                        course_id = $cours_id,
                                                        group_id = $group_id,
                                                        path = " . quote($uploadPath2) . ",
                                                        filename = " . autoquote($fileName) . ",
                                                        visibility = 'v',
                                                        comment = " . autoquote($_POST['file_comment']) . ",
                                                        category = " . intval($_POST['file_category']) . ",
                                                        title =	" . autoquote($_POST['file_title']) . ",
                                                        creator	= " . autoquote($_POST['file_creator']) . ",
                                                        date = '$file_date',
                                                        date_modified =	'$file_date',
                                                        subject	= " . autoquote($_POST['file_subject']) . ",
                                                        description = " . autoquote($_POST['file_description']) . ",
                                                        author = " . autoquote($_POST['file_author']) . ",
                                                        format = " . autoquote($file_format) . ",
                                                        language = " . autoquote($_POST['file_language']) . ",
                                                        copyrighted = " . intval($_POST['file_copyrighted']));

                                        /*** Copy the file to the desired destination ***/
                                        copy ($userFile, $basedir.$uploadPath.'/'.$safe_fileName);
                                        $dialogBox .= "<p class='success'>$langDownloadEnd</p><br />";
                                } else {
                                        $dialogBox .= "<p class='caution'>$error</p><br />";
                                }
                        }
                }
	} // end if is_uploaded_file

	/**************************************
	MOVE FILE OR DIRECTORY
	**************************************/
	/*-------------------------------------
	MOVE FILE OR DIRECTORY : STEP 2
	--------------------------------------*/
        if (isset($_POST['moveTo'])) {
                $moveTo = $_POST['moveTo'];
                $source = $_POST['source'];
		//elegxos ean source kai destintation einai to idio
		if($basedir . $source != $basedir . $moveTo or $basedir . $source != $basedir . $moveTo) {
			if (move($basedir . $source, $basedir . $moveTo)) {
				update_db_info('document', 'update', $source, $moveTo.'/'.my_basename($source));
				$dialogBox = "<p class='success'>$langDirMv</p><br />";
			} else {
				$dialogBox = "<p class='caution'>$langImpossible</p><br />";
				/*** return to step 1 ***/
				$move = $source;
				unset ($moveTo);
			}
		}
	}

	/*-------------------------------------
	MOVE FILE OR DIRECTORY : STEP 1
	--------------------------------------*/
        if (isset($_GET['move'])) {
                $move = $_GET['move'];
		// h $move periexei to onoma tou arxeiou. anazhthsh onomatos arxeiou sth vash
                $result = mysql_query("SELECT * FROM document WHERE course_id = $cours_id AND
                                                                    $group_sql AND
                                                                    path=" . autoquote($move));
		$res = mysql_fetch_array($result);
		$moveFileNameAlias = $res['filename'];
		@$dialogBox .= form_dir_list_exclude('document', 'source', $move, "moveTo", $basedir, $move);
	}

	/**************************************
	DELETE FILE OR DIRECTORY
	**************************************/
        if (isset($_POST['delete'])) {
                $delete = str_replace('..', '', $_POST['filePath']);
		// Check if file actually exists
                $result = db_query("SELECT path, format FROM document WHERE course_id = $cours_id AND
                                                                            $group_sql AND
                                                                            path=" . autoquote($delete));
                if (mysql_num_rows($result) > 0) {
                        if (my_delete($basedir . $delete) or !file_exists($basedir . $delete)) {
                                update_db_info('document', 'delete', $delete);
                                $dialogBox = "<p class='success'>$langDocDeleted</p><br />";
                        }
                }
	}

	/*****************************************
	RENAME
	******************************************/
	// Step 2: Rename file by updating record in database
	if (isset($_POST['renameTo'])) {
		db_query("UPDATE document SET filename=" .
                         autoquote(canonicalize_whitespace($_POST['renameTo'])) .
                         " WHERE course_id = $cours_id AND $group_sql AND path=" . autoquote($_POST['sourceFile']));
		$dialogBox = "<p class='success'>$langElRen</p><br />";
	}

	// Step 1: Show rename dialog box
        if (isset($_GET['rename'])) {
                $result = mysql_query("SELECT * FROM document WHERE course_id = $cours_id AND
                                                                    $group_sql AND
                                                                    path = " . autoquote($_GET['rename']));
		$res = mysql_fetch_array($result);
		$fileName = $res['filename'];
		@$dialogBox .= "
            <form method='post' action='document.php'>\n";
		$dialogBox .= "
            <input type='hidden' name='sourceFile' value='$_GET[rename]' />
            <fieldset>
				<table class='tbl'>
                <tr>
					<td>$langRename: <b>".q($fileName)."</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $langIn:</td>
					<td><input type='text' name='renameTo' value='$fileName' size='50' /></td>
					<td width='1'><input type='submit' value='$langRename' /></td>
				</tr>
				</table>
            </fieldset>
            </form>
            <br />\n";
	}

	// create directory
	// step 2: create the new directory
	if (isset($_POST['newDirPath'])) {
                $newDirName = canonicalize_whitespace($_POST['newDirName']);
                if (!empty($newDirName)) {
                        make_path($_POST['newDirPath'], array($newDirName));
                        // $path_already_exists: global variable set by make_path()
                        if ($path_already_exists) {
                                $dialogBox = "<p class='caution'>$langFileExists</p>";
                        } else {
                                $dialogBox = "<p class='success'>$langDirCr</p>";
                        }
                }
	}

	// step 1: display a field to enter the new dir name
        if (isset($_GET['createDir'])) {
                $createDir = q($_GET['createDir']);
                $dialogBox .= "
			<form actsion='document.php' method='post'>
            $group_hidden_input
			<fieldset>
				<input type='hidden' name='newDirPath' value='$createDir' />
				<table class='tbl'>
				<tr>
					<th>$langNameDir</th>
					<td width='1'><input type='text' name='newDirName' /></td>
					<td><input type='submit' value='$langCreateDir' /></td>
				</tr>
				</table>
           </fieldset>
           </form>
           <br />\n";
	}

	// add/update/remove comment
	// h $commentPath periexei to path tou arxeiou gia to opoio tha epikyrothoun ta metadata
	if (isset($_POST['commentPath'])) {
                $commentPath = $_POST['commentPath'];
		//elegxos ean yparxei eggrafh sth vash gia to arxeio
		$result = db_query("SELECT * FROM document WHERE path=" . autoquote($commentPath));
		$res = mysql_fetch_array($result);
		if(!empty($res)) {
                        if (!isset($language_codes[$_POST['file_language']])) {
                                $file_language = langname_to_code($language);
                        } else {
                                $file_language = $_POST['file_language'];
                        }
			db_query("UPDATE document SET
                                                comment = " . autoquote($_POST['file_comment']) . ",
                                                category = " . intval($_POST['file_category']) . ",
                                                title = " . autoquote($_POST['file_title']) . ",
                                                date_modified = NOW(),
                                                subject = " . autoquote($_POST['file_subject']) . ",
                                                description = " . autoquote($_POST['file_description']) . ",
                                                author = " . autoquote($_POST['file_author']) . ",
                                                language = '$file_language',
                                                copyrighted = " . intval($_POST['file_copyrighted']) . "
                                        WHERE path = '$commentPath'");
                }
	}

        if (isset($_POST['replacePath']) and
            isset($_FILES['newFile']) and
            is_uploaded_file($_FILES['newFile']['tmp_name'])) {
                $replacePath = $_POST['replacePath'];
		// Check if file actually exists
                $result = db_query("SELECT path, format FROM document WHERE format <> '.dir' AND
                                        path=" . autoquote($replacePath));
                if (mysql_num_rows($result) > 0) {
        		list($oldpath, $oldformat) = mysql_fetch_row($result);
                        // check for disk quota
                        $diskUsed = dir_total_space($basedir);
                        if ($diskUsed - filesize($basedir . $oldpath) + $_FILES['newFile']['size'] > $diskQuotaDocument) {
                                $dialogBox = "<p class='caution'>$langNoSpace</p>";
                        } elseif (unwanted_file($_FILES['newFile']['name'])) {
                                $dialogBox = "<p class='caution'>$langUnwantedFiletype: " .
                                                        q($_FILES['newFile']['name']) . "</p>";
                        } else {
                                $newformat = get_file_extension($_FILES['newFile']['name']);
                                $newpath = preg_replace("/\\.$oldformat$/", '', $oldpath) .
                                           (empty($newformat)? '': '.' . $newformat);
                                my_delete($basedir . $oldpath);
                                if (!copy($_FILES['newFile']['tmp_name'], $basedir . $newpath) or
                                    !db_query("UPDATE document SET path = " . quote($newpath) . ",
                                                                   format = " . quote($newformat) . ",
                                                                   filename = " . autoquote($_FILES['newFile']['name']) . "
                                                              WHERE path = " . quote($oldpath))) {
                                        $dialogBox = "<p class='caution'>$dropbox_lang[generalError]</p>";
                                } else {
                                        $dialogBox = "<p class='success'>$langReplaceOK</p>";
                                }
                        }
                }
	}

	// Display form to replace/overwrite an existing file
	if (isset($_GET['replace'])) {
                $result = db_query("SELECT filename FROM document WHERE format <> '.dir' AND
                                        path = " . autoquote($_GET['replace']));
                if (mysql_num_rows($result) > 0) {
                        list($filename) = mysql_fetch_row($result);
                        $filename = q($filename);
                        $replacemessage = sprintf($langReplaceFile, '<b>' . $filename . '</b>');
                        $dialogBox = "
				<form method='post' action='document.php' enctype='multipart/form-data'>
				<fieldset>
				<input type='hidden' name='replacePath' value='" . q($_GET['replace']) . "' />
					<table class='tbl'>
					<tr>
						<td>$replacemessage</td>
						<td><input type='file' name='newFile' size='35' /></td>
						<td><input type='submit' value='$langReplace' /></td>
					</tr>
					</table>
				</fieldset>
				</form>
				<br />\n";
                }
        }

	// Emfanish ths formas gia tropopoihsh comment
	if (isset($_GET['comment'])) {
                $comment = $_GET['comment'];
		$oldComment='';
		/*** Retrieve the old comment and metadata ***/
		$result = db_query("SELECT * FROM document WHERE path = " . autoquote($comment));
                if (mysql_num_rows($result) > 0) {
                        $row = mysql_fetch_array($result);
                        $oldFilename = q($row['filename']);
                        $oldComment = q($row['comment']);
                        $oldCategory = $row['category'];
                        $oldTitle = q($row['title']);
                        $oldCreator = q($row['creator']);
                        $oldDate = q($row['date']);
                        $oldSubject = q($row['subject']);
                        $oldDescription = q($row['description']);
                        $oldAuthor = q($row['author']);
                        $oldLanguage = q($row['language']);
                        $oldCopyrighted = $row['copyrighted'];

                        // filsystem compability: ean gia to arxeio den yparxoun dedomena sto pedio filename
                        // (ara to arxeio den exei safe_filename (=alfarithmitiko onoma)) xrhsimopoihse to
                        // $fileName gia thn provolh tou onomatos arxeiou
                        $fileName = my_basename($comment);
                        if (empty($oldFilename)) $oldFilename = $fileName;
                        $dialogBox .= "
				<form method='post' action='document.php'>
				<fieldset>
				<input type='hidden' name='commentPath' value='" . q($comment) . "' />
				<input type='hidden' size='80' name='file_filename' value='$oldFilename' />
					<legend>aaaa</legend>
					<table  class='tbl' width='99%'>
					<tr>
						<th>&nbsp;</th>
						<td><b>$langAddComment:</b> $oldFilename</td>
					</tr>
					<tr>
						<th class='left'>$langComment:</th>
						<td><input type='text' size='60' name='file_comment' value='$oldComment' class='FormData_InputText' /></td>
					</tr>
					<tr>
						<th class='left'>$langTitle:</th>
						<td><input type='text' size='60' name='file_title' value='$oldTitle' class='FormData_InputText' /></td>
					</tr>
					<tr>
						<th class='left'>$langCategory:</th>
						<td>" .
                        selection(array('0' => $langCategoryOther,
                                        '1' => $langCategoryExcercise,
                                        '2' => $langCategoryLecture,
                                        '3' => $langCategoryEssay,
                                        '4' => $langCategoryDescription,
                                        '5' => $langCategoryExample,
                                        '6' => $langCategoryTheory),
                                  'file_category', $oldCategory) . "</td>
					</tr>
					<tr>
						<th class='left'>$langSubject : </th>
						<td><input type='text' size='60' name='file_subject' value='$oldSubject' class='FormData_InputText' /></td>
					</tr>
					<tr>
						<th class='left'>$langDescription : </th>
						<td><input type='text' size='60' name='file_description' value='$oldDescription' class='FormData_InputText' /></td>
					</tr>
					<tr>
						<th class='left'>$langAuthor : </th>
						<td><input type='text' size='60' name='file_author' value='$oldAuthor' class='FormData_InputText' /></td>
					</tr>";

                        $dialogBox .= "
					<tr>
						<th class='left'>$langCopyrighted : </th>
						<td><input name='file_copyrighted' type='radio' value='0' ";
                        if ($oldCopyrighted=="0" || empty($oldCopyrighted)) $dialogBox .= " checked='checked' "; $dialogBox .= " /> $langCopyrightedUnknown <input name='file_copyrighted' type='radio' value='2' "; if ($oldCopyrighted=="2") $dialogBox .= " checked='checked' "; $dialogBox .= " /> $langCopyrightedFree <input name='file_copyrighted' type='radio' value='1' ";

                        if ($oldCopyrighted=="1") { 
                                $dialogBox .= " checked='checked' ";
                        }
                        $dialogBox .= "/>$langCopyrightedNotFree</td>
					</tr>";

                        //ektypwsh tou combox gia epilogh glwssas
                        $dialogBox .= "<tr><th class='left'>$langLanguage :</th><td>" .
                                selection(array('en' => $langEnglish,
                                                'fr' => $langFrench,
                                                'de' => $langGerman,
                                                'el' => $langGreek,
                                                'it' => $langItalian,
                                                'es' => $langSpanish), 'file_language', $oldLanguage) .
                                "</td>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<td><input type='submit' value='$langOkComment' />&nbsp;&nbsp;&nbsp;$langNotRequired</td>
					</tr>
					</table>
                <input type='hidden' size='80' name='file_creator' value='$oldCreator' />
                <input type='hidden' size='80' name='file_date' value='$oldDate' />
                <input type='hidden' size='80' name='file_oldLanguage' value='$oldLanguage' />
                </form>
				<br />\n";
                } else {
                        $dialogBox = "\n       <p class='caution'>$langFileNotFound</p>\n       <br />\n";
                }
        }

	// Visibility commands
	if (isset($_GET['mkVisibl']) || isset($_GET['mkInvisibl'])) {
		if (isset($_GET['mkVisibl'])) {
                        $newVisibilityStatus = "v";
                        $visibilityPath = $_GET['mkVisibl'];
                } else {
                        $newVisibilityStatus = "i";
                        $visibilityPath = $_GET['mkInvisibl'];
                }
		db_query("UPDATE document SET visibility='$newVisibilityStatus' WHERE path = " . autoquote($visibilityPath));
		$dialogBox = "<p class='success_small'>$langViMod</p><br />";
	}
} // teacher only

// Common for teachers and students
// define current directory

// Check if $var is set and return it - if $is_file, then return only dirname part
function pathvar(&$var, $is_file = false)
{
        static $found = false;
        if ($found) {
                return '';
        }
        if (isset($var)) {
                $found = true;
                $var = str_replace('..', '', $var);
                if ($is_file) {
                        return dirname($var);
                } else {
                        return $var;
                }
        }
        return '';
}

$curDirPath = 
        pathvar($_GET['openDir'], false) .
        pathvar($_GET['createDir'], false) .
        pathvar($_POST['moveTo'], false) .
        pathvar($_POST['newDirPath'], false) .
        pathvar($_POST['uploadPath'], false) .
        pathvar($_POST['filePath'], true) .
        pathvar($_GET['move'], true) .
        pathvar($_GET['rename'], true) .
        pathvar($_GET['replace'], true) .
        pathvar($_GET['comment'], true) .
        pathvar($_GET['mkInvisibl'], true) .
        pathvar($_GET['mkVisibl'], true) .
        pathvar($_POST['sourceFile'], true) .
        pathvar($_POST['replacePath'], true) .
        pathvar($_POST['commentPath'], true);

if ($curDirPath == '/' or $curDirPath == '\\') {
        $curDirPath = '';
}
$curDirName = my_basename($curDirPath);
$parentDir = dirname($curDirPath);
if ($parentDir == '\\') {
        $parentDir = '/';
}

if (strpos($curDirName, '/../') !== false or
    !is_dir(realpath($basedir . $curDirPath))) {
	$tool_content .=  $langInvalidDir;
        draw($tool_content, 2);
        exit;
}

$order = 'ORDER BY filename';
$sort = 'name';
$reverse = false;
if (isset($_GET['sort'])) {
        if ($_GET['sort'] == 'type') {
                $order = 'ORDER BY format';
                $sort = 'type';
        } elseif ($_GET['sort'] == 'date') {
                $order = 'ORDER BY date_modified';
                $sort = 'date';
        }
}
if (isset($_GET['rev'])) {
        $order .= ' DESC';
        $reverse = true;
}

/*** Retrieve file info for current directory from database and disk ***/
$result = db_query("SELECT * FROM document
    	WHERE course_id = $cours_id AND $group_sql AND path LIKE '$curDirPath/%'
        AND path NOT LIKE '$curDirPath/%/%' $order");

$fileinfo = array();
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $fileinfo[] = array(
                'is_dir' => is_dir($basedir . $row['path']),
                'size' => filesize($basedir . $row['path']),
                'title' => $row['title'],
                'filename' => $row['filename'],
                'format' => $row['format'],
                'path' => $row['path'],
                'visible' => ($row['visibility'] == 'v'),
                'comment' => $row['comment'],
                'copyrighted' => $row['copyrighted'],
                'date' => strtotime($row['date_modified']));
}

// end of common to teachers and students

// ----------------------------------------------
// Display
// ----------------------------------------------

$dspCurDirName = htmlspecialchars($curDirName);
$cmdCurDirPath = rawurlencode($curDirPath);
$cmdParentDir  = rawurlencode($parentDir);

if($is_adminOfCourse) {
	/*----------------------------------------------------------------
	UPLOAD SECTION (ektypwnei th forma me ta stoixeia gia upload eggrafou + ola ta pedia
	gia ta metadata symfwna me Dublin Core)
	------------------------------------------------------------------*/
	$tool_content .= "\n  <div id='operations_container'>\n    <ul id='opslist'>";
	$tool_content .= "\n      <li><a href='upload.php?{$groupset}uploadPath=$curDirPath'>$langDownloadFile</a></li>";
	/*----------------------------------------
	Create new folder
	--------------------------------------*/
	$tool_content .= "\n      <li><a href='{$base_url}createDir=$cmdCurDirPath'>$langCreateDir</a></li>";
	$diskQuotaDocument = $diskQuotaDocument * 1024 / 1024;
	$tool_content .= "\n      <li><a href='{$base_url}showQuota=true'>$langQuotaBar</a></li>";
	$tool_content .= "\n    </ul>\n  </div>\n";

	// Dialog Box
	if (!empty($dialogBox))
	{
		$tool_content .=  $dialogBox . "\n";
	}
}

// check if there are documents
if ($is_adminOfCourse) {
	$sql = db_query("SELECT * FROM document");
} else {
	$sql = db_query("SELECT * FROM document WHERE visibility = 'v'");
}
if (mysql_num_rows($sql) == 0) {
	$tool_content .= "\n    <p class='alert1'>$langNoDocuments</p>";
} else {

	// Current Directory Line
	$tool_content .= "
		<br />
			<table width='99%'>\n";

        if ($is_adminOfCourse) {
                $cols = 4;
        } else {
                $cols = 3;
        }

	$tool_content .= "
			<tr>
				<th height='18' colspan='$cols'><div align=\"left\">$langDirectory: " . make_clickable_path($curDirPath) . "</div></th>
				<th><div align='right'>";

        // Link for sortable table headings
        function headlink($label, $this_sort)
        {
                global $sort, $reverse, $curDirPath, $base_url;

                if (empty($curDirPath)) {
                        $path = '/';
                } else {
                        $path = $curDirPath;
                }
                if ($sort == $this_sort) {
                        $this_reverse = !$reverse;
                        $indicator = ' <img src="../../template/classic/img/arrow_' . 
                                ($reverse? 'up': 'down') . '.gif" />';
                } else {
                        $this_reverse = $reverse;
                        $indicator = '';
                }
                return '<a href="' . $base_url . 'openDir=' . $path .
                       '&amp;sort=' . $this_sort . ($this_reverse? '&amp;rev=1': '') .
                       '">' . $label . $indicator . '</a>';
        }

	/*** go to parent directory ***/
        if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
        {
                $parentlink = $base_url . 'openDir=' . $cmdParentDir;
                $tool_content .=  "<a href='$parentlink'>$langUp</a> <a href='$parentlink'><img src='../../template/classic/img/parent.gif' height='20' width='20' /></a>";
        }
        $tool_content .= "</div></th>
			</tr>
			<tr>";
        $tool_content .= "\n				<td width='10%' class='center'><b>" . headlink($langType, 'type') . '</b></td>';
        $tool_content .= "\n				<td><b>" . headlink($langName, 'name') . '</b></td>';
        $tool_content .= "\n				<td width='15%' class='center'><b>$langSize</b></td>";
        $tool_content .= "\n				<td width='15%' class='center'><b>" . headlink($langDate, 'date') . '</b></td>';
	if($is_adminOfCourse) {
		$tool_content .= "\n				<td width='20%' class='center'><b>$langCommands</b></td>";
	}
	$tool_content .= "\n  </tr>";

        // -------------------------------------
        // Display directories first, then files
        // -------------------------------------
        foreach (array(true, false) as $is_dir) {
                foreach ($fileinfo as $entry) {
                        if (($entry['is_dir'] != $is_dir) or
                                        (!$is_adminOfCourse and !$entry['visible'])) {
                                continue;
                        }
                        $cmdDirName = $entry['path'];
                        if ($entry['visible']) {
                                $style = '';
                        } else {
                                $style = ' class="invisible"';
                        }
                        $copyright_icon = '';
                        if ($is_dir) {
                                $image = '../../template/classic/img/folder.gif';
                                $file_url = $base_url . "openDir=$cmdDirName";
                                $link_extra = '';

                                $link_text = $entry['filename'];
                        } else {
                                $image = $urlAppend . '/modules/document/img/' . choose_image('.' . $entry['format']);
                                $file_url = file_url($cmdDirName, $entry['filename']);
                                $link_extra = " title='$langSave' target='_blank'";
                                if (empty($entry['title'])) {
                                        $link_text = $entry['filename'];
                                } else {
                                        $link_text = q($entry['title']);
                                }
                                if ($entry['copyrighted']) {
                                        $link_text .= " <img src='$urlAppend/modules/document/img/copyrighted.jpg' />";
                                }
                        }
                        $tool_content .= "\n			<tr$style>";
                        $tool_content .= "\n				<td width='1%' valign='top'><a href='$file_url'$style$link_extra><img src='$image' /></a></td>";
                        $tool_content .= "\n				<td><a href='$file_url'$style$link_extra>$link_text</a>";

                        /*** comments ***/
                        if (!empty($entry['comment'])) {
                                $tool_content .= "<br /><span class='comment'>" .
                                        nl2br(htmlspecialchars($entry['comment'])) .
                                        "</span>\n";
                        }
                        $tool_content .= "</td>\n";
                        if ($is_dir) {
                                // skip display of date and time for directories
                                $tool_content .= "				<td>&nbsp;</td>\n				<td>&nbsp;</td>";
                        } else {
                                $size = format_file_size($entry['size']);
                                $date = format_date($entry['date']);
                                $tool_content .= "\n				<td>$size</td>\n				<td>$date</td>";
                        }
                        if ($is_adminOfCourse) {
                                $tool_content .= "\n				<td><form action='document.php' method='post'>" . $group_hidden_input .
                                                 "<input type='hidden' name='filePath' value='$cmdDirName' />";
                                /*** delete command ***/
                                $tool_content .= "<input type='image' src='../../template/classic/img/delete.gif' alt='$langDelete' title='$langDelete' name='delete' value='1' onClick=\"return confirmation('".addslashes($entry['filename'])."');\" />&nbsp;";
                                /*** copy command ***/
                                $tool_content .= "<a href='{$base_url}move=$cmdDirName'>";
                                $tool_content .= "<img src='../../template/classic/img/move_doc.gif' title='$langMove' /></a>&nbsp;";
                                /*** rename command ***/
                                $tool_content .=  "<a href='{$base_url}rename=$cmdDirName'>";
                                $tool_content .=  "<img src='../../template/classic/img/edit.gif' title='$langRename' /></a>&nbsp;";
                                /*** comment command ***/
                                $tool_content .= "<a href='{$base_url}comment=$cmdDirName'>";
                                $tool_content .= "<img src='../../template/classic/img/information.gif' title='$langComment' /></a>&nbsp;";
                                /*** visibility command ***/
                                if ($entry['visible']) {
                                        $tool_content .= "<a href='{$base_url}mkInvisibl=$cmdDirName'>";
                                        $tool_content .= "<img src='../../template/classic/img/visible.gif' title='$langVisible' /></a>";
                                } else {
                                        $tool_content .= "<a href='{$base_url}mkVisibl=$cmdDirName'>";
                                        $tool_content .= "<img src='../../template/classic/img/invisible.gif' title='$langVisible' /></a>";
                                }
                                if (!$is_dir) {
                                        /*** replace/overwrite command, only applies to files ***/
                                        $tool_content .= "&nbsp;<a href='{$base_url}replace=$cmdDirName'>" .
                                                         "<img src='../../template/classic/img/add.gif' title='$langReplace' /></a>";
                                }
                                $tool_content .= "</form></td>";
                                $tool_content .= "\n			</tr>";
                        }
                }
        }
        $tool_content .=  "\n			</table>\n";
	if ($is_adminOfCourse) {
		$tool_content .= "			<p align='right'><small>$langMaxFileSize " . ini_get('upload_max_filesize') . "</small></p>\n";
	}
        $tool_content .=  "\n			<br />";
}
add_units_navigation(TRUE);
draw($tool_content, 2, '', $local_head);

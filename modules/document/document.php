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


$require_current_course = TRUE;
$guest_allowed = true;

include '../../include/baseTheme.php';
/**** The following is added for statistics purposes ***/
include '../../include/action.php';
$action = new action();
include 'doc_init.php';
include '../../include/lib/forcedownload.php';
include "../../include/lib/fileDisplayLib.inc.php";
include "../../include/lib/fileManageLib.inc.php";
include "../../include/lib/fileUploadLib.inc.php";

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

$require_help = TRUE;
$helpTopic = 'Doc';

// check for quotas
$diskUsed = dir_total_space($basedir);
$type = ($subsystem == GROUP)? 'group_quota': 'doc_quota';
$d = mysql_fetch_row(mysql_query("SELECT $type FROM cours WHERE cours_id = $cours_id"));
$diskQuotaDocument = $d[0];

if (isset($_GET['showQuota'])) {
        $nameTools = $langQuotaBar;
        if ($subsystem == GROUP) {
        	$navigation[] = array ('url' => 'document.php?group_id=' . $group_id, 'name' => $langDoc);
        } elseif ($subsystem == EBOOK) {
		$navigation[] = array ('url' => 'document.php?ebook_id=' . $ebook_id, 'name' => $langDoc);
	} else {
        	$navigation[] = array ('url' => 'document.php', 'name' => $langDoc);
        }
	$tool_content .= showquota($diskQuotaDocument, $diskUsed);
	draw($tool_content, 2);
	exit;
}

if ($subsystem == EBOOK) {
	$nameTools = $langFileAdmin;
	$navigation[] = array('url' => 'index.php', 'name' => $langEBook);
        $navigation[] = array('url' => 'edit.php?id=' . $ebook_id, 'name' => $langEBookEdit);
}

// ---------------------------
// download directory action
// ---------------------------
if (isset($_GET['downloadDir'])) {
        include("../../include/pclzip/pclzip.lib.php");

        // Make sure $downloadDir doesn't contain /..
	$downloadDir = str_replace('.', '', $_GET['downloadDir']);

	list($real_filename) = mysql_fetch_row(db_query("SELECT filename FROM document
                                                                WHERE $group_sql AND
                                                                      path = " . autoquote($downloadDir)));
	$real_filename = $real_filename.'.zip';
	$zip_filename = $webDir . 'courses/temp/'.safe_filename('zip');

        zip_documents_directory($zip_filename, $downloadDir);

	// download file
	send_file_to_client($zip_filename, $real_filename, null, true, true);
	exit;
}

if ($can_upload)  {
        if (isset($_POST['uncompress'])) {
                include("../../include/pclzip/pclzip.lib.php");
        }
}


// Used in documents path navigation bar
function make_clickable_path($path)
{
	global $langRoot, $base_url, $group_sql;

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

if($can_upload) {
	/*********************************************************************
	UPLOAD FILE

        Ousiastika dhmiourgei ena safe_fileName xrhsimopoiwntas ta DATETIME
        wste na mhn dhmiourgeitai provlhma sto filesystem apo to onoma tou
        arxeiou. Parola afta to palio filename pernaei apo 'filtrarisma' wste
        na apofefxthoun 'epikyndynoi' xarakthres.
	***********************************************************************/

	$action_message = $dialogBox = '';
	if (isset($_FILES['userFile']) and is_uploaded_file($_FILES['userFile']['tmp_name'])) {
                $userFile = $_FILES['userFile']['tmp_name'];
		// check for disk quotas
		$diskUsed = dir_total_space($basedir);
		if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaDocument) {
			$action_message .= "<p class='caution'>$langNoSpace</p>";
		} else {
                        $uploadPath = str_replace('\'', '', $_POST['uploadPath']);

                        if (unwanted_file($_FILES['userFile']['name'])) {
                                $action_message .= "<p class='caution'>$langUnwantedFiletype: " .
                                                   q($_FILES['userFile']['name']) . "</p>";
                        } elseif (isset($_POST['uncompress']) and $_POST['uncompress'] == 1
                                  and preg_match('/\.zip$/i', $_FILES['userFile']['name'])) {
                                /*** Unzipping stage ***/
                                $zipFile = new pclZip($userFile);
                                $realFileSize = 0;
                                $zipFile->extract(PCLZIP_CB_PRE_EXTRACT, 'process_extracted_file');
                                if ($diskUsed + $realFileSize > $diskQuotaDocument) {
                                        $action_message .= "<p class='caution'>$langNoSpace</p>";
                                } else {
                                        $action_message .= "<p class='success'>$langDownloadAndZipEnd</p><br />";
                                }
                        } else {
                                $error = false;
                                $fileName = canonicalize_whitespace($_FILES['userFile']['name']);
                                // Check if upload path exists
                                if (!empty($uploadPath)) {
                                        $result = mysql_fetch_row(db_query("SELECT count(*) FROM document
                                                        WHERE $group_sql AND
                                                              path = " . autoquote($uploadPath)));
                                        if (!$result[0]) {
                                                $error = $langImpossible;
                                        }
                                }
                                if (!$error) {
                                        // Check if file already exists
					$result = db_query("SELECT filename FROM document WHERE
                                                                   $group_sql AND
                                                                   path REGEXP '^" . escapeSimple($uploadPath) . "/[^/]+$' AND
                                                                   filename = " . autoquote($fileName) ." LIMIT 1");
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
							subsystem = $subsystem,
                                                        subsystem_id = $subsystem_id,
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
                                        $action_message .= "<p class='success'>$langDownloadEnd</p><br />";
                                } else {
                                        $action_message .= "<p class='caution'>$error</p><br />";
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
				$action_message = "<p class='success'>$langDirMv</p><br />";
			} else {
				$action_message = "<p class='caution'>$langImpossible</p><br />";
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
                $result = db_query("SELECT filename FROM document
						WHERE $group_sql AND path=" . autoquote($move));
		$res = mysql_fetch_array($result);
		$moveFileNameAlias = $res['filename'];
		$dialogBox .= directory_selection($move, 'moveTo', dirname($move));
	}

	/**************************************
	DELETE FILE OR DIRECTORY
	**************************************/
        if (isset($_POST['delete']) or isset($_POST['delete_x'])) {
                $delete = str_replace('..', '', $_POST['filePath']);
		// Check if file actually exists
                $result = db_query("SELECT path, format FROM document
					WHERE $group_sql AND path=" . autoquote($delete));
                if (mysql_num_rows($result) > 0) {
                        if (my_delete($basedir . $delete) or !file_exists($basedir . $delete)) {
                                update_db_info('document', 'delete', $delete);
                                $action_message = "<p class='success'>$langDocDeleted</p><br />";
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
                         " WHERE $group_sql AND path=" . autoquote($_POST['sourceFile']));
		$action_message = "<p class='success'>$langElRen</p><br />";
	}

	// Step 1: Show rename dialog box
        if (isset($_GET['rename'])) {
                $result = db_query("SELECT * FROM document
						WHERE $group_sql AND
						      path = " . autoquote($_GET['rename']));
		$res = mysql_fetch_array($result);
		$fileName = $res['filename'];
		$dialogBox .= "
            <form method='post' action='document.php'>
            <input type='hidden' name='sourceFile' value='$_GET[rename]' />
	    $group_hidden_input
            <fieldset>
		<table class='tbl' width='100%'>
                <tr>
		  <td>$langRename: &nbsp;&nbsp;&nbsp;<b>".q($fileName)."</b>&nbsp;&nbsp;&nbsp; $langIn:
		  <input type='text' name='renameTo' value='$fileName' size='50' /></td>
		  <td class='right'><input type='submit' value='$langRename' /></td>
		</tr>
		</table>
            </fieldset>
            </form>\n";
	}

	// create directory
	// step 2: create the new directory
	if (isset($_POST['newDirPath'])) {
                $newDirName = canonicalize_whitespace($_POST['newDirName']);
                if (!empty($newDirName)) {
                        make_path($_POST['newDirPath'], array($newDirName));
                        // $path_already_exists: global variable set by make_path()
                        if ($path_already_exists) {
                                $action_message = "<p class='caution'>$langFileExists</p>";
                        } else {
                                $action_message = "<p class='success'>$langDirCr</p>";
                        }
                }
	}

	// step 1: display a field to enter the new dir name
        if (isset($_GET['createDir'])) {
                $createDir = q($_GET['createDir']);
                $dialogBox .= "
			<form action='document.php' method='post'>
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
		$result = db_query("SELECT * FROM document
					     WHERE $group_sql AND
					           path=" . autoquote($commentPath));
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
                                        WHERE $group_sql AND
					      path = '$commentPath'");
			$action_message = "<p class='success'>$langComMod</p>";
                }
	}

        if (isset($_POST['replacePath']) and
            isset($_FILES['newFile']) and
            is_uploaded_file($_FILES['newFile']['tmp_name'])) {
                $replacePath = $_POST['replacePath'];
		// Check if file actually exists
                $result = db_query("SELECT path, format FROM document WHERE
					$group_sql AND
					format <> '.dir' AND
                                        path=" . autoquote($replacePath));
                if (mysql_num_rows($result) > 0) {
        		list($oldpath, $oldformat) = mysql_fetch_row($result);
                        // check for disk quota
                        $diskUsed = dir_total_space($basedir);
                        if ($diskUsed - filesize($basedir . $oldpath) + $_FILES['newFile']['size'] > $diskQuotaDocument) {
                                $action_message = "<p class='caution'>$langNoSpace</p>";
                        } elseif (unwanted_file($_FILES['newFile']['name'])) {
                                $action_message = "<p class='caution'>$langUnwantedFiletype: " .
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
                                                              WHERE $group_sql AND
							       path = " . quote($oldpath))) {
                                        $action_message = "<p class='caution'>$dropbox_lang[generalError]</p>";
                                } else {
                                        $action_message = "<p class='success'>$langReplaceOK</p>";
                                }
                        }
                }
	}

	// Display form to replace/overwrite an existing file
	if (isset($_GET['replace'])) {
                $result = db_query("SELECT filename FROM document
					WHERE $group_sql AND
						format <> '.dir' AND
						path = " . autoquote($_GET['replace']));
                if (mysql_num_rows($result) > 0) {
                        list($filename) = mysql_fetch_row($result);
                        $filename = q($filename);
                        $replacemessage = sprintf($langReplaceFile, '<b>' . $filename . '</b>');
                        $dialogBox = "
				<form method='post' action='document.php' enctype='multipart/form-data'>
				<fieldset>
				<input type='hidden' name='replacePath' value='" . q($_GET['replace']) . "' />
				$group_hidden_input
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
		$result = db_query("SELECT * FROM document WHERE $group_sql AND path = " . autoquote($comment));
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
			  $group_hidden_input
			  <legend>$langAddComment</legend>
			  <table class='tbl' width='100%'>
			  <tr>
			    <th>$langWorkFile:</th>
			    <td>$oldFilename</td>
			  </tr>
			  <tr>
			    <th>$langTitle:</th>
			    <td><input type='text' size='60' name='file_title' value='$oldTitle' /></td>
			  </tr>
			  <tr>
			    <th>$langComment:</th>
			    <td><input type='text' size='60' name='file_comment' value='$oldComment' /></td>
			  </tr>
			  <tr>
			    <th>$langCategory:</th>
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
			    <th>$langSubject : </th>
			    <td><input type='text' size='60' name='file_subject' value='$oldSubject' /></td>
			  </tr>
			  <tr>
			    <th>$langDescription : </th>
			    <td><input type='text' size='60' name='file_description' value='$oldDescription' /></td>
			  </tr>
			  <tr>
			    <th>$langAuthor : </th>
			    <td><input type='text' size='60' name='file_author' value='$oldAuthor' /></td>
			  </tr>";
		  
                        $dialogBox .= "
          <tr>
            <th>$langCopyrighted : </th>
            <td><input name='file_copyrighted' type='radio' value='0' ";
                        if ($oldCopyrighted=="0" || empty($oldCopyrighted)) $dialogBox .= " checked='checked' "; $dialogBox .= " /> $langCopyrightedUnknown <input name='file_copyrighted' type='radio' value='2' "; if ($oldCopyrighted=="2") $dialogBox .= " checked='checked' "; $dialogBox .= " /> $langCopyrightedFree <input name='file_copyrighted' type='radio' value='1' ";

                        if ($oldCopyrighted=="1") { 
                                $dialogBox .= " checked='checked' ";
                        }
                        $dialogBox .= "/>$langCopyrightedNotFree</td>
          </tr>";

                        //ektypwsh tou combox gia epilogh glwssas
                        $dialogBox .= "
          <tr>
            <th>$langLanguage :</th>
            <td>" .
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
            <td><input type='submit' value='$langOkComment' /></td>
          </tr>
          <tr>
            <th>&nbsp;</th>
            <td class='right'>$langNotRequired</td>
          </tr>
          </table>
        <input type='hidden' size='80' name='file_creator' value='$oldCreator' />
        <input type='hidden' size='80' name='file_date' value='$oldDate' />
        <input type='hidden' size='80' name='file_oldLanguage' value='$oldLanguage' />
        </fieldset>
        </form>
        \n\n";
                } else {
                        $action_message = "<p class='caution'>$langFileNotFound</p>";
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
		db_query("UPDATE document SET visibility='$newVisibilityStatus'
					  WHERE $group_sql AND
					        path = " . autoquote($visibilityPath));
		$action_message = "<p class='success'>$langViMod</p>";
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
			WHERE $group_sql AND
				path LIKE '$curDirPath/%' AND
				path NOT LIKE '$curDirPath/%/%' $order");

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

if($can_upload) {
	// Action result message
	if (!empty($action_message))
	{
		$tool_content .= "\n" . $action_message . "\n";
	}

	/*----------------------------------------------------------------
	UPLOAD SECTION (ektypwnei th forma me ta stoixeia gia upload eggrafou + ola ta pedia
	gia ta metadata symfwna me Dublin Core)
	------------------------------------------------------------------*/
	$tool_content .= "\n  <div id='operations_container'>\n    <ul id='opslist'>";
	$tool_content .= "\n  <li><a href='upload.php?{$groupset}uploadPath=$curDirPath'>$langDownloadFile</a></li>";
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
		$tool_content .= "\n" . $dialogBox . "\n";
	}
}

// check if there are documents
list($doc_count) = mysql_fetch_row(db_query("SELECT COUNT(*) FROM document WHERE $group_sql" .
				            ($is_adminOfCourse? '': " AND visibility='v'")));
if ($doc_count == 0) {
	$tool_content .= "\n    <p class='alert1'>$langNoDocuments</p>";
} else {
	// Current Directory Line
	$tool_content .= "
    <table width='100%' class='tbl'>\n";

        if ($can_upload) {
                $cols = 4;
        } else {
                $cols = 3;
        }

	$tool_content .= "
    <tr>
      <td colspan='$cols'><div align='left'>$langDirectory: " . make_clickable_path($curDirPath) . "</div></d>
      <td><div align='right'>";

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
                                ($reverse? 'up': 'down') . '.png" />';
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
                $tool_content .=  "<a href='$parentlink'>$langUp</a> <a href='$parentlink'><img src='../../template/classic/img/folder_up.png' height='16' width='16' alt='icon'/></a>";
        }
        $tool_content .= "</div></td>
    </tr>
    </table>
    <table width='100%' class='tbl_alt'>
    <tr>";
        $tool_content .= "\n      <th width='50' class='center'><b>" . headlink($langType, 'type') . '</b></th>';
        $tool_content .= "\n      <th><div align='left'>" . headlink($langName, 'name') . '</div></th>';
        $tool_content .= "\n      <th width='60' class='center'><b>$langSize</b></th>";
        $tool_content .= "\n      <th width='80' class='center'><b>" . headlink($langDate, 'date') . '</b></th>';
	if($can_upload) {
		$tool_content .= "\n<th width='150' class='center'><b>$langCommands</b></th>";
	} else {
		$tool_content .= "\n<th width='50' class='center'><b>$langCommands</b></th>";
	}
	$tool_content .= "\n</tr>";

        // -------------------------------------
        // Display directories first, then files
        // -------------------------------------
        $counter = 0;
        foreach (array(true, false) as $is_dir) {
                foreach ($fileinfo as $entry) {
                        if (($entry['is_dir'] != $is_dir) or
                                        (!$is_adminOfCourse and !$entry['visible'])) {
                                continue;
                        }
                        $cmdDirName = $entry['path'];
                        if ($entry['visible']) {
                                if ($counter%2 == 0) {
                                  $style = 'class="even"';
                                } else {
                                  $style = 'class="odd"';
                                }
                        } else {
                                $style = ' class="invisible"';
                        }
                        $copyright_icon = '';
                        if ($is_dir) {
                                $image = '../../template/classic/img/folder.png';
                                $file_url = $base_url . "openDir=$cmdDirName";
                                $link_extra = '';
				$link_text = $entry['filename'];
				$img_download = "<img src='../../template/classic/img/download.png' width='16' height='16' align='middle' alt='$langDownloadDir' title='$langDownloadDir'>";
				$download_url = $base_url . "downloadDir=$cmdDirName";
                        } else {
                                $image = $urlAppend . '/modules/document/img/' . choose_image('.' . $entry['format']);
                                $file_url = file_url($cmdDirName, $entry['filename']);
                                $link_extra = " title='$langSave' target='_blank'";
				$img_download = '';
                                if (empty($entry['title'])) {
                                        $link_text = $entry['filename'];
                                } else {
                                        $link_text = q($entry['title']);
                                }
                                if ($entry['copyrighted']) {
                                        $link_text .= " <img src='$urlAppend/modules/document/img/copyrighted.png' />";
                                }
                        }
                        $tool_content .= "\n<tr $style>";
                        $tool_content .= "\n<td class='center' valign='top'><a href='$file_url'$style$link_extra><img src='$image' /></a></td>";
                        $tool_content .= "\n<td><a href='$file_url'$link_extra>$link_text</a>";
			
                        /*** comments ***/
                        if (!empty($entry['comment'])) {
                                $tool_content .= "<br /><span class='comment'>" .
                                        nl2br(htmlspecialchars($entry['comment'])) .
                                        "</span>";
                        }
                        $tool_content .= "</td>";
                        if ($is_dir) {
                                // skip display of date and time for directories
                                $tool_content .= "\n<td>&nbsp;</td>\n<td>&nbsp;</td>";
                        } else {
                                $size = format_file_size($entry['size']);
                                $date = format_date($entry['date']);
                                $tool_content .= "\n<td class='center'>$size</td>\n<td class='center'>$date</td>";
				
                        }
                        if ($can_upload) {
                                $tool_content .= "\n<td class='right' valign='top'><form action='document.php' method='post'>" . $group_hidden_input .
                                                 "<input type='hidden' name='filePath' value='$cmdDirName' />";
				if ($is_dir) {
					$tool_content .= "<a href='$download_url'>$img_download</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				} 
                                if (!$is_dir) {
                                        /*** replace/overwrite command, only applies to files ***/
                                        $tool_content .= "<a href='{$base_url}replace=$cmdDirName'>" .
                                                         "<img src='../../template/classic/img/replace.png' " .
							 "title='$langReplace' alt='$langReplace' /></a>&nbsp;";
                                }
                                /*** delete command ***/
                                $tool_content .= "<input type='image' src='../../template/classic/img/delete.png' alt='$langDelete' title='$langDelete' name='delete' value='1' onClick=\"return confirmation('".addslashes($entry['filename'])."');\" />&nbsp;";
                                /*** copy command ***/
                                $tool_content .= "<a href='{$base_url}move=$cmdDirName'>" .
                                                 "<img src='../../template/classic/img/move.png' " .
						 "title='$langMove' alt='$langMove' /></a>&nbsp;";
                                /*** rename command ***/
                                $tool_content .=  "<a href='{$base_url}rename=$cmdDirName'>";
                                $tool_content .=  "<img src='../../template/classic/img/rename.png' " .
					          "title='$langRename' alt='$langRename' /></a>&nbsp;";
                                /*** comment command ***/
                                $tool_content .= "<a href='{$base_url}comment=$cmdDirName'>";
                                $tool_content .= "<img src='../../template/classic/img/comment_edit.png' " .
					         "title='$langComment' alt='$langComment' /></a>&nbsp;";
                                /*** visibility command ***/
                                if ($is_adminOfCourse) {
					if ($entry['visible']) {
						$tool_content .= "<a href='{$base_url}mkInvisibl=$cmdDirName'>" .
								 "<img src='../../template/classic/img/visible.png' " .
								 "title='$langVisible' alt='$langVisible' /></a>";
	                                } else {
	                                        $tool_content .= "<a href='{$base_url}mkVisibl=$cmdDirName'>" .
								 "<img src='../../template/classic/img/invisible.png' " .
								 "title='$langVisible' alt='$langVisible' /></a>";
	                                }
				}
				if ($subsystem == GROUP and isset($is_member) and ($is_member)) {
	                                $tool_content .= "<a href='$urlAppend/modules/work/group_work.php?" .
							 "group_id=$group_id&amp;submit=$cmdDirName'>" .
							 "<img src='../../template/classic/img/book.png' " .
							 "title='$langPublish' alt='$langPublish' /></a>";			
				}
                                $tool_content .= "</form></td>";
                                $tool_content .= "\n    </tr>";
                        } else { // only for students
				if ($is_dir) { 
					$tool_content .= "<td class='center'><a href='$download_url'>$img_download</a></td></tr>";
				} else {
					$tool_content .= "<td class='center'>&nbsp;</td></tr>";
				}
			}
                        $counter++;
                }
        }
        $tool_content .=  "\n    </table>\n";
	if ($can_upload) {
		$tool_content .= "\n    <p align='right'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</p>\n";
	}
        $tool_content .= "\n    <br />";
}
add_units_navigation(TRUE);
draw($tool_content, 2, '', $local_head);

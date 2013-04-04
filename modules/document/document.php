<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$is_in_tinymce = (isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce') ? true : false;
if (!defined('COMMON_DOCUMENTS')) {
        $require_current_course = TRUE;
        $menuTypeID = ($is_in_tinymce) ? 5: 2;
} else {        
        if ($is_in_tinymce) {
                $menuTypeID = 5;
        } else {
                $require_admin = TRUE;
                $menuTypeID = 3;
        }
}

$guest_allowed = true;

include '../../include/baseTheme.php';
/**** The following is added for statistics purposes ***/
include '../../include/action.php';
$action = new action();
include 'doc_init.php';
include 'doc_metadata.php';
include '../../include/lib/forcedownload.php';
include '../../include/lib/fileDisplayLib.inc.php';
include '../../include/lib/fileManageLib.inc.php';
include '../../include/lib/fileUploadLib.inc.php';
include '../../include/pclzip/pclzip.lib.php' ;
require_once '../video/video_functions.php';

load_js('tools.js');
load_modal_box(true);

$require_help = TRUE;
$helpTopic = 'Doc';

if ($is_in_tinymce) {
    
    $_SESSION['embedonce'] = true; // necessary for baseTheme
    $docsfilter = (isset($_REQUEST['docsfilter'])) ? 'docsfilter='. $_REQUEST['docsfilter'] .'&amp;' : '';
    $base_url .= 'embedtype=tinymce&amp;'. $docsfilter;
    
    load_js('jquery');
    load_js('tinymce/jscripts/tiny_mce/tiny_mce_popup.js');
    
    $head_content .= <<<EOF
<script type='text/javascript'>
$(document).ready(function() {

    $("a.fileURL").click(function() { 
        var URL = $(this).attr('href');
        var win = tinyMCEPopup.getWindowArg("window");

        // insert information now
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

        // are we an image browser
        if (typeof(win.ImageDialog) != "undefined") {
            // we are, so update image dimensions...
            if (win.ImageDialog.getImageData)
                win.ImageDialog.getImageData();

            // ... and preview if necessary
            if (win.ImageDialog.showPreviewImage)
                win.ImageDialog.showPreviewImage(URL);
        }

        // close popup window
        tinyMCEPopup.close();
        return false;
    });
});
</script>
EOF;
}

// check for quotas
$diskUsed = dir_total_space($basedir);
if (defined('COMMON_DOCUMENTS')) {
        $diskQuotaDocument = $diskUsed + ini_get('upload_max_filesize') * 1024 * 1024;
} else {
        $type = ($subsystem == GROUP)? 'group_quota': 'doc_quota';
        $d = mysql_fetch_row(db_query("SELECT $type FROM cours WHERE cours_id = $cours_id"));
        $diskQuotaDocument = $d[0];
}


if (isset($_GET['showQuota'])) {
        $nameTools = $langQuotaBar;
        if ($subsystem == GROUP) {
        	$navigation[] = array ('url' => 'document.php?course='.$code_cours.'&amp;group_id=' . $group_id, 'name' => $langDoc);
        } elseif ($subsystem == EBOOK) {
                $navigation[] = array ('url' => 'index.php?course='.$code_cours.'&amp;ebook_id=' . $ebook_id, 'name' => $langDoc);
        } elseif ($subsystem == COMMON) {
                $navigation[] = array ('url' => 'commondocs.php', 'name' => $langCommonDocs);
	} else {
        	$navigation[] = array ('url' => 'document.php?course='.$code_cours, 'name' => $langDoc);
        }
	$tool_content .= showquota($diskQuotaDocument, $diskUsed);
	draw($tool_content, $menuTypeID);
	exit;
}

if ($subsystem == EBOOK) {
	$nameTools = $langFileAdmin;
	$navigation[] = array('url' => 'index.php?course='.$code_cours, 'name' => $langEBook);
        $navigation[] = array('url' => 'edit.php?course='.$code_cours.'&amp;id=' . $ebook_id, 'name' => $langEBookEdit);
}

// ---------------------------
// download directory or file
// ---------------------------
if (isset($_GET['download'])) {
        $downloadDir = $_GET['download'];
        
        if ($downloadDir == '/') {
                $format = '.dir';
                $real_filename = remove_filename_unsafe_chars($langDoc . ' ' . $fake_code);
        } else {
                $q = db_query("SELECT filename, format, visibility, extra_path FROM document
                                      WHERE $group_sql AND
                                            path = " . autoquote($downloadDir));
                if (!$q or mysql_num_rows($q) != 1) {
                        not_found($downloadDir);
                }
                list($real_filename, $format, $visibility, $extra_path) = mysql_fetch_row($q);                
                if (($visibility != 'v') and (isset($statut) and $statut != 1)) {
                        not_found($downloadDir);
                }
        }
        // Allow unlimited time for creating the archive
        @set_time_limit(0);
        
        if ($format == '.dir') {
                $real_filename = $real_filename.'.zip';
                $dload_filename = $webDir . 'courses/temp/'.safe_filename('zip');
                zip_documents_directory($dload_filename, $downloadDir, $is_editor);
                $delete = true;
        } elseif ($extra_path) {
                if ($real_path = common_doc_path($extra_path, true)) {
                        // Common document
                        if (!$common_doc_visible) {
                                forbidden($downloadDir);
                        }
                        $dload_filename = $real_path;
                        $delete = false;
        } else {
                        // External document - redirect to URL
                        header('Location: ' . $extra_path);
                        exit;
                }
        } else {
                $dload_filename = $basedir . $downloadDir;
                $delete = false;
        }

	send_file_to_client($dload_filename, $real_filename, null, true, $delete);
	exit;
}



/**
 * Used in documents path navigation bar
 * @global type $langRoot
 * @global type $base_url
 * @global type $group_sql
 * @param type $path
 * @return type
 */
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

if ($can_upload) {
	/*********************************************************************
	UPLOAD FILE

        Ousiastika dhmiourgei ena safe_fileName xrhsimopoiwntas ta DATETIME
        wste na mhn dhmiourgeitai provlhma sto filesystem apo to onoma tou
        arxeiou. Parola afta to palio filename pernaei apo 'filtrarisma' wste
        na apofefxthoun 'epikyndynoi' xarakthres.
	***********************************************************************/

	$action_message = $dialogBox = '';
	if (isset($_FILES['userFile']) and is_uploaded_file($_FILES['userFile']['tmp_name'])) {
	    validateUploadedFile($_FILES['userFile']['name'], $menuTypeID);
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
                                validateUploadedZipFile($zipFile->listContent(), $menuTypeID);
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
					$result = db_query("SELECT path, visibility FROM document WHERE
                                                                   $group_sql AND
                                                                   path REGEXP '^" . escapeSimple($uploadPath) . "/[^/]+$' AND
                                                                   filename = " . autoquote($fileName) ." LIMIT 1");
                                        if (mysql_num_rows($result)) {
                                                if (isset($_POST['replace'])) {
                                                        // Delete old file record when replacing file
                                                        list($file_path, $vis) = mysql_fetch_row($result);
                                                        db_query("DELETE FROM document WHERE
                                                                         $group_sql AND
                                                                         path = " . quote($file_path));
                                                } else {
                                                        $error = $langFileExists;
                                                }
                                        } else {
                                                // Try to add an extension to files witout extension,
                                                // change extension of PHP files
                                                $fileName = php2phps(add_ext_on_mime($fileName));
                                                // File name used in file system and path field
                                                $safe_fileName = safe_filename(get_file_extension($fileName));
                                                if ($uploadPath == '.') {
                                                        $file_path = '/' . $safe_fileName;
                                                } else {
                                                        $file_path = $uploadPath . '/' . $safe_fileName;
                                                }
                                                $vis = 'v';
                                        }
                                }
                                if (!$error) {
                                        // No errors, so proceed with upload
                                        $file_format = get_file_extension($fileName);
                                        // File date is current date
                                        $file_date = date("Y\-m\-d G\:i\:s");
                                        db_query("INSERT INTO document SET
                                                        course_id = $cours_id,
							subsystem = $subsystem,
                                                        subsystem_id = $subsystem_id,
                                                        path = " . quote($file_path) . ",
                                                        filename = " . autoquote($fileName) . ",
                                                        visibility = '$vis',
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
                                        copy ($userFile, $basedir . $file_path);
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
                $sourceXml = $source . '.xml';
                //check if source and destination are the same
		if($basedir . $source != $basedir . $moveTo or $basedir . $source != $basedir . $moveTo) {
                        $r = mysql_fetch_array(db_query("SELECT filename, extra_path FROM document WHERE $group_sql AND path='$source'"));
                        $filename = $r['filename'];
                        $extra_path = $r['extra_path'];
                        if (empty($extra_path)) {
			if (move($basedir . $source, $basedir . $moveTo)) {
                                        if (hasMetaData($source, $basedir, $group_sql)) {
                                                move($basedir . $sourceXml, $basedir . $moveTo);
                                        }
                                        update_db_info('document', 'update', $source, $moveTo.'/'.my_basename($source));
                                }
                        } else {
                                update_db_info('document', 'update', $source, $moveTo.'/'.my_basename($source));
                        }
				$action_message = "<p class='success'>$langDirMv</p><br />";
			} else {
				$action_message = "<p class='caution'>$langImpossible</p><br />";
				/*** return to step 1 ***/
				$move = $source;
				unset ($moveTo);
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
                $result = db_query("SELECT path, extra_path, format, filename FROM document
					WHERE $group_sql AND path=" . autoquote($delete));
                $r = mysql_fetch_array($result);                
                $filename = $r['filename'];
                if (mysql_num_rows($result) > 0) {
                        if (empty($r['extra_path'])) {
                        if (my_delete($basedir . $delete) or !file_exists($basedir . $delete)) {
                                        if (hasMetaData($delete, $basedir, $group_sql)) {
                                	my_delete($basedir . $delete . ".xml");
                                        }
                                        update_db_info('document', 'delete', $delete);
                                }
                        } else {
                                update_db_info('document', 'delete', $delete);
                        }
                                $action_message = "<p class='success'>$langDocDeleted</p><br />";
                        }
                }

	/*****************************************
	RENAME
	******************************************/
	// Step 2: Rename file by updating record in database
	if (isset($_POST['renameTo'])) {
	    $result = db_query("SELECT format FROM document WHERE $group_sql AND path =". quote($_POST['sourceFile']) );
	    if (mysql_num_rows($result)) {
	        $row = mysql_fetch_assoc($result);
	        if ($row['format'] != '.dir')
	            validateRenamedFile($_POST['renameTo'], $menuTypeID);
	    }
		db_query("UPDATE document SET filename=" .
                         autoquote($_POST['renameTo']) .
                         ", date_modified=NOW()
                          WHERE $group_sql AND path=" . autoquote($_POST['sourceFile']));
		if (hasMetaData($_POST['sourceFile'], $basedir, $group_sql)) {
			$q = db_query("UPDATE document SET filename=" .
                                      autoquote($_POST['renameTo'] . '.xml') .
                                      " WHERE $group_sql AND
                                              path = " . autoquote($_POST['sourceFile'] . '.xml'));
                        if ($q and mysql_affected_rows()) {
                                metaRenameDomDocument($basedir . $_POST['sourceFile'] . '.xml',
                                                      $_POST['renameTo']);
                        }
		}
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
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours'>
            <input type='hidden' name='sourceFile' value='" .
                q($_GET['rename']) . "' />
	    $group_hidden_input
            <fieldset>
		<table class='tbl' width='100%'>
                <tr>
		  <td>$langRename: &nbsp;&nbsp;&nbsp;<b>".q($fileName)."</b>&nbsp;&nbsp;&nbsp; $langIn:
		  <input type='text' name='renameTo' value='".q($fileName)."' size='50' /></td>
		  <td class='right'><input type='submit' value='".q($langRename)."' /></td>
		</tr>
		</table>
            </fieldset>
            </form>\n";
	}

	// create directory
	// step 2: create the new directory
	if (isset($_POST['newDirPath'])) {
                $newDirName = canonicalize_whitespace(q($_POST['newDirName']));
                if (!empty($newDirName)) {
                        make_path(q($_POST['newDirPath']), array($newDirName));
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
			<form action='$_SERVER[SCRIPT_NAME]?course=$code_cours' method='post'>
            $group_hidden_input
			<fieldset>
				<input type='hidden' name='newDirPath' value='$createDir' />
				<table class='tbl' width='100%'>
				<tr>
					<th>$langNameDir</th>
					<td width='1'><input type='text' name='newDirName' /></td>
					<td><input type='submit' value='".q($langCreateDir)."' /></td>
				</tr>
				</table>
           </fieldset>
           </form>
           <br />\n";
	}

	// add/update/remove comment
	if (isset($_POST['commentPath'])) {
                $commentPath = $_POST['commentPath'];
                // check if file exists
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
	
	// add/update/remove metadata
	// h $metadataPath periexei to path tou arxeiou gia to opoio tha epikyrwthoun ta metadata
	if (isset($_POST['metadataPath'])) {
		
		$metadataPath = $_POST['metadataPath'] . ".xml";
		$oldFilename = $_POST['meta_filename'] . ".xml";
		$xml_filename = $basedir . str_replace('/..', '', $metadataPath);
		$xml_date = date("Y\-m\-d G\:i\:s");
		$file_format = ".meta";
		
		metaCreateDomDocument($xml_filename);
		
		$result = db_query("SELECT * FROM document WHERE $group_sql AND path = " . autoquote($metadataPath));
		if (mysql_num_rows($result) > 0) {
			db_query("UPDATE document SET
				creator	= " . autoquote($_SESSION['prenom'] ." ". $_SESSION['nom']) . ",
				date_modified = NOW(),
				format = " . autoquote($file_format) . ",
				language = ". autoquote($_POST['meta_language']) ." 
				WHERE $group_sql AND path = ". autoquote($metadataPath) );
		} else {
			db_query("INSERT INTO document SET
				course_id = $cours_id,
				subsystem = $subsystem,
				subsystem_id = $subsystem_id,
				path = " . quote($metadataPath) . ",
				filename = " . autoquote($oldFilename) . ",
				visibility = 'i',
				creator	= " . autoquote($_SESSION['prenom'] ." ". $_SESSION['nom']) . ",
				date = '$xml_date',
				date_modified =	'$xml_date',
				format = " . autoquote($file_format) . ",
				language = " . autoquote($_POST['meta_language']));
		}
		
		$action_message = "<p class='success'>$langMetadataMod</p>";
	}

        if (isset($_POST['replacePath']) and
            isset($_FILES['newFile']) and
            is_uploaded_file($_FILES['newFile']['tmp_name'])) {
            validateUploadedFile($_FILES['newFile']['name'], $menuTypeID);
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
                                                                   filename = " . autoquote($_FILES['newFile']['name']) . ",
                                                                   date_modified = NOW()
                                                              WHERE $group_sql AND
							       path = " . quote($oldpath))) {
                                        $action_message = "<p class='caution'>$dropbox_lang[generalError]</p>";
                                } else {
                                	if (hasMetaData($oldpath, $basedir, $group_sql)) {
                                		rename($basedir . $oldpath . ".xml", $basedir . $newpath . ".xml");
                                		db_query("UPDATE document SET path = " . quote($newpath . ".xml") . ",
                                		      filename=" . autoquote($_FILES['newFile']['name'] . ".xml") .
                                		    " WHERE $group_sql AND path =" . quote($oldpath . ".xml"));
                                	}
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
				<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours' enctype='multipart/form-data'>
				<fieldset>
				<input type='hidden' name='replacePath' value='" . q($_GET['replace']) . "' />
				$group_hidden_input
					<table class='tbl' width='100%'>
					<tr>
						<td>$replacemessage</td>
						<td><input type='file' name='newFile' size='35' /></td>
						<td><input type='submit' value='".q($langReplace)."' /></td>
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
			<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours'>
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
            <td class='right'><input type='submit' value='".q($langOkComment)."' /></td>
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

	// Emfanish ths formas gia tropopoihsh metadata
	if (isset($_GET['metadata'])) {
		
		$metadata = $_GET['metadata'];
		$result = db_query("SELECT * FROM document WHERE $group_sql AND path = " . autoquote($metadata));
		
		if (mysql_num_rows($result) > 0) {
			
			$row = mysql_fetch_array($result);
			$oldFilename = q($row['filename']);
			
			// filesystem compability: ean gia to arxeio den yparxoun dedomena sto pedio filename
			// (ara to arxeio den exei safe_filename (=alfarithmitiko onoma)) xrhsimopoihse to
			// $fileName gia thn provolh tou onomatos arxeiou
			$fileName = my_basename($metadata);
			if (empty($oldFilename)) $oldFilename = $fileName;
			$real_filename = $basedir . str_replace('/..', '', q($metadata));
			
			$dialogBox .= metaCreateForm($metadata, $oldFilename, $real_filename);
			
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
        pathvar($_GET['metadata'], true) .
        pathvar($_GET['mkInvisibl'], true) .
        pathvar($_GET['mkVisibl'], true) .
        pathvar($_POST['sourceFile'], true) .
        pathvar($_POST['replacePath'], true) .
        pathvar($_POST['commentPath'], true) .
        pathvar($_POST['metadataPath'], true);

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
        draw($tool_content, $menuTypeID);
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

$filter = '';
$eclplugin = true;
if (isset($_REQUEST['docsfilter'])) {
    
    switch ($_REQUEST['docsfilter']) {
        case 'image':
            $ors = '';
            foreach (get_supported_images() as $imgfmt)
                $ors .= " OR format LIKE '$imgfmt'";
            $filter = "AND (format LIKE '.dir' $ors)";
            break;
        case 'eclmedia':
        	$ors = '';
        	foreach (get_supported_media() as $mediafmt)
        		$ors .= " OR format LIKE '$mediafmt'";
        	$filter = "AND (format LIKE '.dir' $ors)";
        	break;
        case 'media':
            $eclplugin = false;
            $ors = '';
            foreach (get_supported_media() as $mediafmt)
                $ors .= " OR format LIKE '$mediafmt'";
            $filter = "AND (format LIKE '.dir' $ors)";
            break;
        case 'zip':
            $filter = "AND (format LIKE '.dir' OR FORMAT LIKE 'zip')";
            break;
        case 'file':
        default:
            break;
    }
}

/*** Retrieve file info for current directory from database and disk ***/
$result = db_query("SELECT * FROM document
			WHERE $group_sql AND
				path LIKE '$curDirPath/%' AND
				path NOT LIKE '$curDirPath/%/%' $filter $order");

$fileinfo = array();
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        if ($real_path = common_doc_path($row['extra_path'], true)) {
                // common docs
                $path = $real_path;
        } else {
                $path = $basedir . $row['path'];
        }
        if (!$real_path and $row['extra_path']) {
                // external file
                $size = 0;
        } else {
                $size = filesize($path);
        }
        $fileinfo[] = array(
                'is_dir' => ($row['format'] == '.dir'),
                'size' => $size,
                'title' => $row['title'],
                'filename' => $row['filename'],
                'format' => $row['format'],
                'path' => $row['path'],
                'extra_path' => $row['extra_path'],
                'visible' => ($row['visibility'] == 'v'),                
                'comment' => $row['comment'],
                'copyrighted' => $row['copyrighted'],
                'date' => $row['date_modified']);
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
                $tool_content .= $action_message;
	}
        // available actions
        if (!$is_in_tinymce) {
            $tool_content .= "<div id='operations_container'><ul id='opslist'>";
            $tool_content .= "<li><a href='upload.php?course=$code_cours&amp;{$groupset}uploadPath=$curDirPath'>$langDownloadFile</a></li>";
            $tool_content .= "<li><a href='{$base_url}createDir=$cmdCurDirPath'>$langCreateDir</a></li>";
            if (!defined('COMMON_DOCUMENTS')) {
                    $tool_content .= "<li><a href='../units/insert.php?course=$code_cours&amp;dir=$curDirPath&amp;type=doc&amp;id=-1'>$langCommonDocs</a>";
            }
            $diskQuotaDocument = $diskQuotaDocument * 1024 / 1024;
            $tool_content .= "<li><a href='{$base_url}showQuota=true'>$langQuotaBar</a></li>";
            $tool_content .= "</ul></div>\n";
        }

	// Dialog Box
	if (!empty($dialogBox))
	{
		$tool_content .= "\n" . $dialogBox . "\n";
	}
}

// check if there are documents
list($doc_count) = mysql_fetch_row(db_query("SELECT COUNT(*) FROM document WHERE $group_sql $filter" .
				            ($is_editor? '': " AND visibility='v'")));
if ($doc_count == 0) {
        $tool_content .= "<p class='alert1'>$langNoDocuments</p>";
} else {
	// Current Directory Line
        $tool_content .= "<table width='100%' class='tbl'>";

        if ($can_upload) {
                $cols = 4;
        } else {
                $cols = 3;
        }

        $download_path = empty($curDirPath)? '/': $curDirPath;
        $download_dir = ($is_in_tinymce) ? '' : "<a href='{$base_url}download=$download_path'><img src='$themeimg/save_s.png' width='16' height='16' align='middle' alt='$langDownloadDir' title='$langDownloadDir'></a>";
        $tool_content .= "<tr>
      <td colspan='$cols'><div class='sub_title1'><b>$langDirectory:</b> " . make_clickable_path($curDirPath) .
      "&nbsp;$download_dir<br></div></td>
      <td><div align='right'>";

        // Link for sortable table headings
        function headlink($label, $this_sort)
        {
                global $sort, $reverse, $curDirPath, $base_url, $themeimg;

                if (empty($curDirPath)) {
                        $path = '/';
                } else {
                        $path = $curDirPath;
                }
                if ($sort == $this_sort) {
                        $this_reverse = !$reverse;
                        $indicator = " <img src='$themeimg/arrow_" . 
                                ($reverse? 'up': 'down') . ".png' />";
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
                $tool_content .=  "<a href='$parentlink'>$langUp</a> <a href='$parentlink'><img src='$themeimg/folder_up.png' height='16' width='16' alt='$langUp'/></a>";
        }
        $tool_content .= "</div></td>
    </tr>
    </table>
    <table width='100%' class='tbl_alt'>
    <tr>";
        $tool_content .= "<th width='50' class='center'><b>" . headlink($langType, 'type') . '</b></th>' .
                         "<th><div align='left'>" . headlink($langName, 'name') . '</div></th>' .
                         "<th width='60' class='center'><b>$langSize</b></th>" .
                         "<th width='80' class='center'><b>" . headlink($langDate, 'date') . '</b></th>';
        if (!$is_in_tinymce) {
            if($can_upload) {
		$width = (get_config("insert_xml_metadata")) ? 175 : 135;
		$tool_content .= "\n      <th width='$width' class='center'><b>$langCommands</b></th>";
            } else {
		$tool_content .= "\n      <th width='50' class='center'><b>$langCommands</b></th>";
            }
        }
	$tool_content .= "\n    </tr>";

        // -------------------------------------
        // Display directories first, then files
        // -------------------------------------
        $counter = 0;
        foreach (array(true, false) as $is_dir) {
                foreach ($fileinfo as $entry) {
                        $link_title_extra = '';
                        if (($entry['is_dir'] != $is_dir) or
                                        (!$is_editor and !$entry['visible'])) {
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
                        if ($is_dir) {
                                $image = $themeimg.'/folder.png';
                                $file_url = $base_url . "openDir=$cmdDirName";
                                $link_title = q($entry['filename']);
                                $dload_msg = $langDownloadDir;
                                $link_href = "<a href='$file_url'>$link_title</a>";
                        } else {
                                $image = $urlAppend . '/modules/document/img/' . choose_image('.' . $entry['format']);
                                $file_url = file_url($cmdDirName, $entry['filename']);
                                if ($entry['extra_path']) {
                                        $cdpath = common_doc_path($entry['extra_path']);
                                        if ($cdpath) {
                                                if ($is_editor) {
                                                        $link_title_extra .= '&nbsp;' .
                                                                $common_doc_visible? 'common': 'common_invisible';
                                                }
                                        } else {
                                                // External file URL
                                                $file_url = $entry['extra_path'];
                                                if ($is_editor) {
                                                        $link_title_extra .= '&nbsp;external';
                                                }
                                        }
                                }
                                $play_url = file_playurl($cmdDirName, $entry['filename']);
                                $link_extra = " class='fileURL' title='$langSave' target='_blank'";
                                $link_title = (empty($entry['title'])) ? $entry['filename'] : q($entry['title']);
                                $link_title_extra = ($entry['copyrighted']) ? "&nbsp;<img src='$urlAppend/modules/document/img/copyrighted.png' />" : '';
                                $dload_msg = $langSave;
                                if ($is_in_tinymce) {
                                        $furl = (is_supported_media($entry['path'], true) && $eclplugin) ? $play_url : $file_url;
                                        $link_href = "<a href='$furl'$link_extra>".$link_title."</a>$link_title_extra";
                                } else {                                    
                                       $link_href = choose_media_ahref($file_url, $file_url, $play_url, $link_title, $entry['path'], $link_title, $link_extra);
                                }
                        }
                        $img_href = "<img src='$image' />";                       
                        if (!$entry['extra_path'] or common_doc_path($entry['extra_path'])) {
                                // Normal or common document
                                $download_url = $base_url . "download=$cmdDirName";
                        } else {
                                // External document
                                $download_url = $entry['extra_path'];
                        }
                        $download_icon = "<a href='$download_url'><img src='$themeimg/save_s.png' width='16' height='16' align='middle' alt='$dload_msg' title='$dload_msg'></a>";
                        $tool_content .= "\n<tr $style>";
                        $tool_content .= "\n<td class='center' valign='top'>".$img_href."</td>";
                        $tool_content .= "\n<td>". $link_href ." ".$link_title_extra;
			
                        /*** comments ***/
                        if (!empty($entry['comment'])) {
                                $tool_content .= "<br /><span class='comment'>" .
                                        nl2br(htmlspecialchars($entry['comment'])) .
                                        "</span>";
                        }
                        $tool_content .= "</td>";
                        $padding = '&nbsp;';
                        $padding2 = '';
                        $date = nice_format($entry['date'], true, true);
                        $date_with_time = nice_format($entry['date'], true);
                        if ($is_dir) {                         
                                $tool_content .= "\n<td>&nbsp;</td>\n<td class='center' title='$date_with_time'>$date</td>";
                                $padding = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        } else if ($entry['format'] == ".meta") {
                                $size = format_file_size($entry['size']);                            
                                $tool_content .= "\n<td class='center'>$size</td>\n<td class='center'>$date</td>";
                                $padding = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                $padding2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        } else {                                
                                $size = format_file_size($entry['size']);                               
                                $tool_content .= "\n<td class='center'>$size</td>\n<td class='center' title='$date_with_time'>$date</td>";
                        }
                        if (!$is_in_tinymce) {
                            if ($can_upload) {
                                $tool_content .= "\n<td class='right' valign='top'><form action='$_SERVER[SCRIPT_NAME]?course=$code_cours' method='post'>" . $group_hidden_input .
                                                 "<input type='hidden' name='filePath' value='$cmdDirName' />" .
                                                 $download_icon . $padding;
                                if (!$is_dir && $entry['format'] != ".meta") {
                                	/*** replace/overwrite command, only applies to files ***/
                                	$tool_content .= "<a href='{$base_url}replace=$cmdDirName'>" .
                                	                 "<img src='$themeimg/replace.png' " .
                                	                 "title='".q($langReplace)."' alt='".q($langReplace)."' /></a>&nbsp;";
                                }
                                /*** delete command ***/
                                $tool_content .= "<input type='image' src='$themeimg/delete.png' alt='".q($langDelete)."' title='".q($langDelete)."' name='delete' value='1' onClick=\"return confirmation('".js_escape($langConfirmDelete.' '.$entry['filename'])."');\" />&nbsp;" . $padding2;
                                if ($entry['format'] != '.meta') {
                                	/*** copy command ***/
                                	$tool_content .= "<a href='{$base_url}move=$cmdDirName'>" .
                                	                 "<img src='$themeimg/move.png' " .
                                	                 "title='".q($langMove)."' alt='".q($langMove)."' /></a>&nbsp;";
                                	/*** rename command ***/
                                	$tool_content .= "<a href='{$base_url}rename=$cmdDirName'>" .
                                                         "<img src='$themeimg/rename.png' " .
                                                         "title='".q($langRename)."' alt='".q($langRename)."' /></a>&nbsp;";
                                	/*** comment command ***/
                                	$tool_content .= "<a href='{$base_url}comment=$cmdDirName'>" .
                                                         "<img src='$themeimg/comment_edit.png' " .
                                                         "title='".q($langComment)."' alt='".q($langComment)."' /></a>&nbsp;";
                                }
                                /*** metadata command ***/
                                if (get_config("insert_xml_metadata")) {
                                	$xmlCmdDirName = ($entry['format'] == ".meta" && get_file_extension($cmdDirName) == "xml") ? substr($cmdDirName, 0, -4) : $cmdDirName;
	                                $tool_content .= "<a href='{$base_url}metadata=$xmlCmdDirName'>";
	                                $tool_content .= "<img src='$themeimg/lom.png' " .
	                                                 "title='".q($langMetadata)."' alt='".q($langMetadata)."' /></a>&nbsp;";
                                }
                                /*** visibility command ***/
                                if ($is_editor) {
					if ($entry['visible']) {
						$tool_content .= "<a href='{$base_url}mkInvisibl=$cmdDirName'>" .
								 "<img src='$themeimg/visible.png' " .
								 "title='".q($langVisible)."' alt='".q($langVisible)."' /></a>&nbsp;";
	                                } else {
	                                        $tool_content .= "<a href='{$base_url}mkVisibl=$cmdDirName'>" .
								 "<img src='$themeimg/invisible.png' " .
								 "title='".q($langVisible)."' alt='".q($langVisible)."' /></a>&nbsp;";
	                                }
				}
				if ($subsystem == GROUP and isset($is_member) and ($is_member)) {
	                                $tool_content .= "<a href='$urlAppend/modules/work/group_work.php?course=$code_cours" .
							 "&amp;group_id=$group_id&amp;submit=$cmdDirName'>" .
							 "<img src='$themeimg/book.png' " .
                                                         "title='$langGroupSubmit' alt='$langGroupSubmit' /></a>";
				}
                                $tool_content .= "</form></td>";
                                $tool_content .= "</tr>\n";
                            } else { // only for students
                                $tool_content .= "<td>$download_icon</td>";
                            }
                        }
                        $counter++;
                }
        }
        $tool_content .=  "\n    </table>\n";
	if ($can_upload && !$is_in_tinymce) {
		$tool_content .= "\n    <br><div class='right smaller'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>\n";
	}
        $tool_content .= "\n    <br />";
}
if (defined('SAVED_COURSE_CODE')) {
        $code_cours = SAVED_COURSE_CODE;
        $cours_id = SAVED_COURSE_ID;
}
add_units_navigation(TRUE);
draw($tool_content, $menuTypeID, null, $head_content);

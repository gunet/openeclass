<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_DOCS');
/**************************************/

$tool_content = "";
$nameTools = $langDoc;
$dbTable = "document";

$require_help = TRUE;
$helpTopic = 'Doc';

// check for quotas
mysql_select_db($mysqlMainDb);
$d = mysql_fetch_array(mysql_query("SELECT doc_quota FROM cours WHERE code='$currentCourseID'"));
$diskQuotaDocument = $d['doc_quota'];
mysql_select_db($currentCourseID);

// -------------------------
// download action2
// --------------------------
if (@$action2=="download")
{
	$real_file = $webDir."/courses/".$currentCourseID."/document/".$id;
	if (strpos($real_file, '/../') === FALSE) {
		//fortwma tou pragmatikou onomatos tou arxeiou pou vrisketai apothikevmeno sth vash
		$result = mysql_query ("SELECT filename FROM document WHERE path LIKE '%$id%'");
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
$baseServDir = $webDir;
$baseServUrl = $urlAppend."/";
$courseDir = "courses/$currentCourseID/document";
$baseWorkDir = $baseServDir.$courseDir;
$diskUsed = dir_total_space($baseWorkDir);

$local_head = '
<script>
function confirmation (name)
{
    if (confirm("'.$langConfirmDelete.'" + name))
        {return true;}
    else
        {return false;}
}
</script>
';


// actions to do before extracting file from zip archive
function renameziparchivefile($p_event, &$p_header) {

	global $dbTable, $file_comment, $file_category, $file_creator, $file_date, $file_subject,
		$file_title, $file_description, $file_author, $file_format, $file_language, $file_copyrighted,
		$currentCourseID, $uploadPath, $realFileSize;

	$realFileSize += $p_header['size'];
	$fileName = $p_header['stored_filename'];
	/**** Check for no desired characters ***/
	$fileName = replace_dangerous_char($fileName);
	/*** Try to add an extension to files witout extension ***/
	$fileName = add_ext_on_mime($fileName);
	/*** Handle PHP files ***/
	$fileName = php2phps($fileName);
	//ypologismos onomatos arxeiou me date + time.
	//to onoma afto tha xrhsimopoiei sto filesystem & tha apothikevetai ston pinaka documents
	$safe_fileName = date("YmdGis").randomkeys("8").".".get_file_extention($fileName);
	//prosthiki eggrafhs kai metadedomenwn gia to eggrafo sth vash
	if ($uploadPath == ".")
		$uploadPath2 = "/".$safe_fileName;
	else
		$uploadPath2 = $uploadPath."/".$safe_fileName;
	//san file format vres to extension tou arxeiou
	$file_format = get_file_extention($fileName);
	//san date you arxeiou xrhsimopoihse thn shmerinh hm/nia
	$file_date = date("Y\-m\-d G\:i\:s");
	$query = "INSERT INTO ".$dbTable." SET
		path = '".mysql_real_escape_string($uploadPath2)."',
		filename = '".mysql_real_escape_string($fileName)."',
		visibility = 'v',
		comment = '".mysql_real_escape_string($file_comment)."',
		category = '".mysql_real_escape_string($file_category)."',
		title =	'".mysql_real_escape_string($file_title)."',
		creator	= '".mysql_real_escape_string($file_creator)."',
		date = '".mysql_real_escape_string($file_date)."',
		date_modified = '".mysql_real_escape_string($file_date)."',
		subject = '".mysql_real_escape_string($file_subject)."',
		description = '".mysql_real_escape_string($file_description)."',
		author = '".mysql_real_escape_string($file_author)."',
		format = '".mysql_real_escape_string($file_format)."',
		language = '".mysql_real_escape_string($file_language)."',
		copyrighted = '".mysql_real_escape_string($file_copyrighted)."'";
		db_query($query, $currentCourseID);
	// file will be extracted with new encoded filename
		$p_header['filename'] = $safe_fileName;
return 1;
}

/*** clean information submited by the user from antislash ***/
stripSubmitValue($_POST);
stripSubmitValue($_GET);
/*****************************************************************************/

if($is_adminOfCourse)
{       // teacher only

	/*********************************************************************
	UPLOAD FILE
	//ousiastika dhmiourgei ena safe_fileName xrhsimopoiwntas ta DATETIME wste na mhn dhmiourgeitai
	//provlhma sto filesystem apo to onoma tou arxeiou. Parola afta to palio filename pernaei apo
	//'filtrarisma' wste na apofefxthoun 'epikyndynoi' xarakthres.
	//gia pardeigma me $fileName = "test.jpg" sto filesystem grafetai arxeio
	$safe_fileName = "20060301121510sdjklhsd.jpg"
	***********************************************************************/

	$dialogBox = '';
	if (is_uploaded_file(@$userFile))
	{
		// check for disk quotas
		$diskUsed = dir_total_space($baseWorkDir);
		if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaDocument) {
			$dialogBox .= $langNoSpace;
		}
		// check for dangerous extensions and file types
		if (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' .
		'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' .
		'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $_FILES['userFile']['name'])) {
			$dialogBox .= "$langUnwantedFiletype: {$_FILES['userFile']['name']}";
		}
		/*** Unzipping stage ***/
		elseif (isset($uncompress) and $uncompress == 1
			and preg_match("/.zip$/", $_FILES['userFile']['name']) )
		{
			$zipFile = new pclZip($userFile);
			$realFileSize = 0;
			$zipFile->extract(PCLZIP_CB_PRE_EXTRACT, 'renameziparchivefile');
			if ($diskUsed + $realFileSize > $diskQuotaDocument) {
				$dialogBox .= $langNoSpace;
			} else {
				$dialogBox .= "<p class=\"success_small\">$langDownloadAndZipEnd</p><br />";
			}
		}
		else
		{
		$fileName = trim ($_FILES['userFile']['name']);
		//elegxos ean to "path" tou arxeiou pros upload vrisketai hdh se eggrafh ston pinaka documents
		//(aftos einai ousiastika o elegxos if_exists dedomenou tou oti to onoma tou arxeiou sto filesystem einai monadiko)
		$result = mysql_query ("SELECT filename FROM document WHERE filename LIKE '%$uploadPath/$fileName%'");
		$row = mysql_fetch_array($result);
			if (!empty($row['filename']))
			{
				//to arxeio yparxei hdh se eggrafh ston pinaka document ths vashs
				$dialogBox .= "<b>$langFileExists !</b>";
			} else //to arxeio den vrethike sth vash ara mporoume na proxwrhsoume me to upload
			{
				/**** Check for no desired characters ***/
				$fileName = replace_dangerous_char($fileName);
				/*** Try to add an extension to files witout extension ***/
				$fileName = add_ext_on_mime($fileName);
				/*** Handle PHP files ***/
				$fileName = php2phps($fileName);
				//ypologismos onomatos arxeiou me date + time.
				//to onoma afto tha xrhsimopoiei sto filesystem & tha apothikevetai ston pinaka documents
				$safe_fileName = date("YmdGis").randomkeys("8").".".get_file_extention($fileName);
				//prosthiki eggrafhs kai metadedomenwn gia to eggrafo sth vash
				if ($uploadPath == ".")
					$uploadPath2 = "/".$safe_fileName;
				else
					$uploadPath2 = $uploadPath."/".$safe_fileName;
				//san file format vres to extension tou arxeiou
				$file_format = get_file_extention($fileName);
				//san date you arxeiou xrhsimopoihse thn shmerinh hm/nia
				$file_date = date("Y\-m\-d G\:i\:s");
				$query = "INSERT INTO ".$dbTable." SET
		            	path	=	'".mysql_real_escape_string($uploadPath2)."',
		            	filename =	'".mysql_real_escape_string($fileName)."',
		            	visibility =	'v',
		            	comment	=	'".mysql_real_escape_string($file_comment)."',
		            	category =	'".mysql_real_escape_string($file_category)."',
		            	title =	'".mysql_real_escape_string($file_title)."',
		            	creator	=	'".mysql_real_escape_string($file_creator)."',
		            	date	= '".mysql_real_escape_string($file_date)."',
		            	date_modified	=	'".mysql_real_escape_string($file_date)."',
		            	subject	=	'".mysql_real_escape_string($file_subject)."',
		            	description =	'".mysql_real_escape_string($file_description)."',
		            	author	=	'".mysql_real_escape_string($file_author)."',
		            	format	=	'".mysql_real_escape_string($file_format)."',
		            	language =	'".mysql_real_escape_string($file_language)."',
		            	copyrighted	=	'".mysql_real_escape_string($file_copyrighted)."'";

				db_query($query, $currentCourseID);

				/*** Copy the file to the desired destination ***/
				copy ($userFile, $baseWorkDir.$uploadPath."/".$safe_fileName);
				@$dialogBox .= "<p class=\"success_small\">$langDownloadEnd</p><br />";
			} // end else tou if(!empty($row['filename']))
		} // end else
	} // end if is_uploaded_file

	/**************************************
	MOVE FILE OR DIRECTORY
	**************************************/
	/*-------------------------------------
	MOVE FILE OR DIRECTORY : STEP 2
	--------------------------------------*/
	if (isset($moveTo))
	{
		//elegxos ean source kai destintation einai to idio
		if($baseWorkDir."/".$source != $baseWorkDir.$moveTo || $baseWorkDir.$source != $baseWorkDir.$moveTo)
		{
			if (move($baseWorkDir.$source,$baseWorkDir.$moveTo)) {
				update_db_info("document", "update", $source, $moveTo."/".my_basename($source));
				$dialogBox = "<p class=\"success_small\">$langDirMv</p><br />";
			}
			else
			{
				$dialogBox = "<p class=\"caution_small\">$langImpossible</p><br />";
				/*** return to step 1 ***/
				$move = $source;
				unset ($moveTo);
			}
		}
	}

	/*-------------------------------------
	MOVE FILE OR DIRECTORY : STEP 1
	--------------------------------------*/
	if (isset($move))
	{
		//h $move periexei to onoma tou arxeiou. anazhthsh onomatos arxeiou sth vash
		$result = mysql_query ("SELECT * FROM $dbTable WHERE path=\"".$move."\"");
		$res = mysql_fetch_array($result);
		$moveFileNameAlias = $res['filename'];
		@$dialogBox .= form_dir_list_exclude($dbTable, "source", $move, "moveTo", $baseWorkDir, $move);
	}

	/**************************************
	DELETE FILE OR DIRECTORY
	**************************************/
	if (isset($delete)) {
		if (my_delete($baseWorkDir.$delete)) {
			update_db_info("document", "delete", $delete);
			$dialogBox = "<b>$langDocDeleted</b>";
		}
	}

	/*****************************************
	RENAME
	******************************************/
	// step 2
	//nea methodos metonomasias arxeiwn kanontas update sthn eggrafh pou yparxei sth vash
	if (isset($renameTo2)) {
		$query =  "UPDATE ".$dbTable." SET filename=\"".$renameTo2."\" WHERE path=\"".$sourceFile."\"";
		db_query($query);
		$dialogBox = "<p class=\"caution_small\">$langElRen</p><br />";
	}

	//	rename
	if (isset($rename))
	{
		//elegxos gia to ean yparxei hdh eggrafh sth vash
		$result = mysql_query ("SELECT * FROM $dbTable WHERE path=\"".$rename."\"");
		$res = mysql_fetch_array($result);
		//yparxei eggrafh sth vash gia to arxeio opote xrhsimopoihse thn nea methodo metonomasias (ginetai sto STEP 2)
		$fileName = $res["filename"];
		@$dialogBox .= "<form>\n";
		$dialogBox .= "<input type=\"hidden\" name=\"sourceFile\" value=\"$rename\">
        	<table class='FormData' width=\"99%\"><tbody><tr>
          	<th class='left' width='200'>$langRename:</th>
          	<td class='left'>$langRename ".htmlspecialchars($fileName)." $langIn: <input type=\"text\" name=\"renameTo2\" value=\"$fileName\" class='FormData_InputText' size='50'></td>
          	<td class='left' width='1'><input type=\"submit\" value=\"$langRename\"></td>
        	</tr></tbody></table></form><br />";
	}

	// create directory
	//step 2
	if (isset($newDirPath) and !empty($newDirName))
	{
		$newDirName = trim($newDirName);
        	$r = db_query('SELECT * FROM document WHERE filename = ' . quote($newDirName));
        	$exists = false;
        	$parent = preg_replace('|/[^/]*$|', '', $newDirPath);
        	while ($rs = mysql_fetch_array($r)) {
                	if (preg_replace('|/[^/]*$|', '', $rs['path']) == $parent) {
                        	$exists = true;
                	}
        	}
        	if ($exists) {
                	$dialogBox .= "<p class=\"caution_small\">$langFileExists</p><br />";
        	} else {
			$safe_dirName = date("YmdGis").randomkeys("8");
			mkdir($baseWorkDir.$newDirPath."/".$safe_dirName, 0775);
			$query =  "INSERT INTO ".$dbTable." SET
    				path=\"".$newDirPath."/".$safe_dirName."\",
    				filename=\"".$newDirName."\",
    				visibility=\"v\",
				comment=\"\",
				category=\"\",
				title=\"\",
				creator=\"".$prenom." ".$nom."\",
				date=\"".date("Y\-m\-d G\:i\:s")."\",
				date_modified=\"".date("Y\-m\-d G\:i\:s")."\",
				subject=\"\",
				description=\"\",
				author=\"\",
				format=\"\",
				language=\"\",
				copyrighted=\"\"";
			mysql_query($query);
			$dialogBox = "<p class=\"caution_small\">$langDirCr</p><br />";
		}
	}

	// step 1
	if (isset($createDir))
	{
		$dialogBox .= "<form>\n";
		$dialogBox .= "<input type=\"hidden\" name=\"newDirPath\" value=\"$createDir\">\n";
		$dialogBox .= "<table class='FormData' width=\"99%\">
        	<tbody><tr><th class='left' width='200'>$langNameDir:</th>
          	<td class='left' width='1'><input type=\"text\" name=\"newDirName\" class='FormData_InputText'></td>
          	<td class='left'><input type=\"submit\" value=\"$langCreateDir\"></td>
  		</tr></tbody></table></form><br />";
	}

	//	add/update/remove comment
	//	h $commentPath periexei to path tou arxeiou gia to opoio tha epikyrothoun ta metadata
	if (isset($edit_metadata))
	{
		//elegxos ean yparxei eggrafh sth vash gia to arxeio
		$result = mysql_query ("SELECT * FROM $dbTable WHERE path=\"".$commentPath."\"");
		$res = mysql_fetch_array($result);
		if(!empty($res))
		{
			//elegxos ean o xrhsths epelekse diaforetikh glwssa h' tipota (option -> "")
			if (empty($file_language)) $file_language = $file_oldLanguage;
			$query =  "UPDATE ".$dbTable." SET
    				comment=\"".mysql_real_escape_string($file_comment)."\",
				category=\"".mysql_real_escape_string($file_category)."\",
  	 			title=\"".mysql_real_escape_string($file_title)."\",
				date_modified=\"".date("Y\-m\-d G\:i\:s")."\",
    				subject=\"".mysql_real_escape_string($file_subject)."\",
    				description=\"".mysql_real_escape_string($file_description)."\",
    				author=\"".mysql_real_escape_string($file_author)."\",
    				language=\"".mysql_real_escape_string($file_language)."\",
    				copyrighted=\"".mysql_real_escape_string($file_copyrighted)."\"
    				  WHERE path=\"".$commentPath."\"";
		} else
		//den yparxei eggrafh sth vash gia to sygkekrimeno arxeio opote dhmiourghse thn eggrafh
		{
			if (empty($file_language)) $file_language = $file_oldLanguage;
			if (empty($file_filename)) $file_filename = htmlspecialchars($fileName);
			$file_format = get_file_extention($file_filename);
			$query =  "INSERT INTO ".$dbTable." SET
    			path=\"".$commentPath."\",
    			filename=\"".$file_filename."\",
    			visibility=\"v\",
				comment=\"".mysql_real_escape_string($file_comment)."\",
				category=\"".mysql_real_escape_string($file_category)."\",
				title=\"".mysql_real_escape_string($file_title)."\",
				creator=\"".$prenom." ".$nom."\",
				date=\"".date("Y\-m\-d G\:i\:s")."\",
				date_modified=\"".date("Y\-m\-d G\:i\:s")."\",
				subject=\"".mysql_real_escape_string($file_subject)."\",
				description=\"".mysql_real_escape_string($file_description)."\",
				author=\"".mysql_real_escape_string($file_author)."\",
				format=\"".mysql_real_escape_string($file_format)."\",
				language=\"".mysql_real_escape_string($file_language)."\",
				copyrighted=\"".mysql_real_escape_string($file_copyrighted)."\"";
		}
		mysql_query($query);
	}

	//emfanish ths formas gia tropopoihsh comment
	//edw tha valoume kai ta epipleon pedia gia ta metadedomena
	if (isset($comment))
	{
		$oldComment='';
		/*** Retrieve the old comment and metadata ***/
		$query = "SELECT * FROM $dbTable WHERE path LIKE '%".$comment."%'";
		$result = mysql_query ($query);
		$row = mysql_fetch_array($result);
		$oldFilename = $row['filename'];
		$oldComment = $row['comment'];
		$oldCategory = $row['category'];
		$oldTitle = $row['title'];
		$oldCreator = $row['creator'];
		$oldDate = $row['date'];
		$oldSubject = $row['subject'];
		$oldDescription = $row['description'];
		$oldAuthor = $row['author'];
		$oldLanguage = $row['language'];
		$oldCopyrighted = $row['copyrighted'];

		//filsystem compability: ean gia to arxeio den yparxoun dedomena sto pedio filename
		//(ara to arxeio den exei safe_filename (=alfarithmitiko onoma)) xrhsimopoihse to
		//$fileName gia thn provolh tou onomatos arxeiou
		$fileName = my_basename($comment);
		if (empty($oldFilename)) $oldFilename = $fileName;
		$dialogBox .="	<form method=\"post\" action=\"$_SERVER[PHP_SELF]?edit_metadata\">
        		<input type=\"hidden\" name=\"commentPath\" value=\"$comment\">
        		<input type=\"hidden\" size=\"80\" name=\"file_filename\" value=\"$oldFilename\">
        		<table  class='FormData' width=\"99%\">
        		<tbody><tr><th>&nbsp;</th>
        		<td><b>$langAddComment: </b>".htmlspecialchars($oldFilename)."</td>
        		</tr><tr>
        		<th class='left'>$langComment:</th>
        		<td><input type=\"text\" size=\"60\" name=\"file_comment\" value=\"$oldComment\" class='FormData_InputText'></td>
        		</tr><tr>
        		<th class='left'>$langTitle:</th>
        		<td><input type=\"text\" size=\"60\" name=\"file_title\" value=\"$oldTitle\" class='FormData_InputText'></td>
        		</tr>
        		<tr><th class='left'>$langCategory:</th><td>";
		//ektypwsh tou combobox gia thn epilogh kathgorias tou eggrafou
		$dialogBox .= "<select name=\"file_category\" class='auth_input'>
			<option"; if($oldCategory=="0") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"0\">$langCategoryOther<br>";
		$dialogBox .= "	<option";
		if($oldCategory=="1") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"1\">$langCategoryExcercise<br>
		<option"; if($oldCategory=="1") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"2\">$langCategoryLecture<br>
		<option"; if($oldCategory=="2") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"3\">$langCategoryEssay<br>
		<option"; if($oldCategory=="3") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"4\">$langCategoryDescription<br>
		<option"; if($oldCategory=="4") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"5\">$langCategoryExample<br>
		<option"; if($oldCategory=="5") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"6\">$langCategoryTheory<br>
		</select></td></tr>";
		$dialogBox .= "<input type=\"hidden\" size=\"80\" name=\"file_creator\" value=\"$oldCreator\">
    			<input type=\"hidden\" size=\"80\" name=\"file_date\" value=\"$oldDate\">
    			<tr><th class='left'>$langSubject : </th><td>
			<input type=\"text\" size=\"60\" name=\"file_subject\" value=\"$oldSubject\" class='FormData_InputText'>
			</td></tr><tr><th class='left'>$langDescription : </th><td>
    			<input type=\"text\" size=\"60\" name=\"file_description\" value=\"$oldDescription\" class='FormData_InputText'></td></tr>
    			<tr><th class='left'>$langAuthor : </th><td>
    			<input type=\"text\" size=\"60\" name=\"file_author\" value=\"$oldAuthor\" class='FormData_InputText'>
    			</td></tr>";

		$dialogBox .= "<tr><th class='left'>$langCopyrighted : </th>
			<td><input name=\"file_copyrighted\" type=\"radio\" value=\"0\" ";
		if ($oldCopyrighted=="0" || empty($oldCopyrighted)) $dialogBox .= " checked=\"checked\" "; $dialogBox .= " /> $langCopyrightedUnknown <input name=\"file_copyrighted\" type=\"radio\" value=\"2\" "; if ($oldCopyrighted=="2") $dialogBox .= " checked=\"checked\" "; $dialogBox .= " /> $langCopyrightedFree <input name=\"file_copyrighted\" type=\"radio\" value=\"1\" ";

		if ($oldCopyrighted=="1") $dialogBox .= " checked=\"checked\" "; $dialogBox .= "/> $langCopyrightedNotFree
    		</td></tr>
    		<input type=\"hidden\" size=\"80\" name=\"file_oldLanguage\" value=\"$oldLanguage\">";
		//ektypwsh tou combox gia epilogh glwssas
		$dialogBox .= "	<tr><th class='left'>$langLanguage :</th>
    			<td><select name=\"file_language\" class='auth_input'>
			</option><option value=\"en\">$langEnglish
			</option><option value=\"fr\">$langFrench
			</option><option value=\"de\">$langGerman
			</option><option value=\"el\" selected>$langGreek
			</option><option value=\"it\">$langItalian
			</option><option value=\"es\">$langSpanish
			</option>
			</select></td></tr>
			<tr><th>&nbsp;</th>
			<td><input type=\"submit\" value=\"$langOkComment\">&nbsp;&nbsp;&nbsp;$langNotRequired</td>
			</tr></tbody></table></form><br>";
	}

	// Visibility commands
	if (isset($mkVisibl) || isset($mkInvisibl))
	{
		$visibilityPath = @$mkVisibl.@$mkInvisibl; // At least one of these variables are empty

		// analoga me poia metavlhth exei timh ($mkVisibl h' $mkInvisibl) vale antistoixh
		//timh sthn $newVisibilityStatus gia na graftei sth vash
		if (isset($mkVisibl))
			$newVisibilityStatus = "v";
		else
			$newVisibilityStatus = "i";
		// enallagh ths timhs sto pedio visibility tou pinaka document
		mysql_query ("UPDATE $dbTable SET visibility='".$newVisibilityStatus."' WHERE path LIKE '%".$visibilityPath."%'");
		$dialogBox = "<p class=\"success_small\">$langViMod</p><br />";
	}
} // teacher only

// Common for teachers and students
// define current directory
if (isset($openDir)  || isset($moveTo) || isset($createDir) || isset($newDirPath) || isset($uploadPath) ) // $newDirPath is from createDir command (step 2) and $uploadPath from upload command
{
	$curDirPath = @$openDir . @$createDir . @$moveTo . @$newDirPath . @$uploadPath;
}
elseif (isset($delete) || isset($move) || isset($rename) || isset($sourceFile) || isset($comment) || isset($commentPath) || isset($mkVisibl) || isset($mkInvisibl)) //$sourceFile is from rename command (step 2)
{
	$curDirPath = dirname(@$delete . @$move . @$rename . @$sourceFile . @$comment . @$commentPath . @$mkVisibl . @$mkInvisibl);
}
else
{
	$curDirPath="";
}

// The strpos($curDirPath, "..") prevent malicious users to go to the root directory
if ($curDirPath == "/" || $curDirPath == "\\" || strpos($curDirPath, ".."))
{
	$curDirPath =""; // manage the root directory problem
}

$curDirName = my_basename($curDirPath);
$parentDir = dirname($curDirPath);

if ($parentDir == "/" || $parentDir == "\\")
{
	$parentDir =""; // manage the root directory problem
}

// Read current Directory content
// Searching files and directories info in database

/*** Search infos in the DB about the current directory the user is in ***/
$result = db_query("SELECT * FROM $dbTable
    	WHERE path LIKE '$curDirPath/%'
        AND path NOT LIKE '$curDirPath/%/%'");

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
	$attribute['path'][] = $row['path'];
	$attribute['visibility'][] = $row['visibility'];
	$attribute['comment'][] = $row['comment'];
}

// load Files and directories into arrays
if (@chdir(realpath($baseWorkDir.$curDirPath))) {
	$handle = opendir(".");
	while ($file = readdir($handle))
	{
		if ($file == "." || $file == "..")
		{
			continue;                       // Skip current and parent directories
		}
		if(is_dir($file)) {
			$dirNameList[] = $file;
			/*** Make the correspondance between info given by the file system and info given by the DB ***/
			$keyDir = sizeof($dirNameList)-1;
			if (isset($attribute))
			{
				$keyAttribute = array_search($curDirPath."/".$file, $attribute['path']);
				if ($keyAttribute !== false)
				{
					$dirCommentList[$keyDir] = $attribute['comment'][$keyAttribute];
					$dirVisibilityList[$keyDir] = $attribute['visibility'][$keyAttribute];
				}
			}
		}
		if(is_file($file)) {
			$fileNameList[] = $file;
			$fileSizeList[] = filesize($file);
			$fileDateList[] = filemtime($file);
			/*** Make the correspondance between info given by the file system and info given by the DB ***/
			$keyFile = sizeof($fileNameList)-1;
			if (isset($attribute))
			{
				$keyAttribute = array_search($curDirPath."/".$file, $attribute['path']);
				if ($keyAttribute !== false)
				{
					$fileCommentList[$keyFile] = $attribute['comment'][$keyAttribute];
					$fileVisibilityList[$keyFile] = $attribute['visibility'][$keyAttribute];
				}
			}
		}
	} // end while ($file = readdir($handle))
	closedir($handle);
	unset($attribute);
	/*** Sort alphabetically ***/
	if (isset($dirNameList)) {
		asort($dirNameList);
	}
	if (isset($fileNameList)) {
		asort($fileNameList);
	}

} else {
	$tool_content .=  $langInvalidDir;
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
	$tool_content .= "\n  <div id=\"operations_container\">\n    <ul id=\"opslist\">";
	$tool_content .= "\n      <li><a href=\"upload.php?uploadPath=$curDirPath\">$langDownloadFile</a></li>";
	/*----------------------------------------
	Create new folder
	--------------------------------------*/
	$tool_content .= "\n      <li><a href=\"$_SERVER[PHP_SELF]?createDir=".$cmdCurDirPath."\">$langCreateDir</small></a></li>";
	$diskQuotaDocument = $diskQuotaDocument * 1024 / 1024;
	$tool_content .= "\n      <li><a href=\"showquota.php?diskQuotaDocument=$diskQuotaDocument&diskUsed=$diskUsed\">$langQuotaBar</a></li>";
    $tool_content .= "\n    </ul>\n  </div>\n<br />";

	// Dialog Box
	if (!empty($dialogBox))
	{
		$tool_content .=  $dialogBox . " ";
	}
}

// check if there are documents
if($is_adminOfCourse) {
	$sql = db_query("SELECT * FROM document");
} else {
	$sql = db_query("SELECT * FROM document WHERE visibility = 'v'");
}
if (mysql_num_rows($sql) == 0) {
	$tool_content .= "\n<p class='alert1'>$langNoDocuments</p>";
} else {

	// Current Directory Line
	$tool_content .= "\n<div class=\"fileman\">";
	$tool_content .= "\n  <table width=\"99%\" align='left' class=\"Documents\">";
    $tool_content .= "\n  <tbody>";

	   if($is_adminOfCourse) {
		  $cols = 4;
	   } else {
		  $cols = 3;
	   }

	$tool_content .= "\n  <tr>";
    $tool_content .= "\n    <th height='18' colspan='$cols'><div align=\"left\">$langDirectory: ".make_clickable_path($dbTable, $curDirPath). "</div></th>";
	$tool_content .= "\n    <th><div align='right'>";

	/*** go to parent directory ***/
	if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
	{
   	    $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdParentDir."\">$langUp</a>\n";
   	    $tool_content .=  "<img src=\"../../template/classic/img/parent.gif\" border=0 align=\"absmiddle\" height='12' width='12'>";
	}
	$tool_content .= "</div></th>";
    $tool_content .= "\n  </tr>";
	$tool_content .= "\n  <tr>";
    $tool_content .= "\n    <td width='1' class=\"DocHead\"><b>".$m['type']."</b></td>";
	$tool_content .= "\n    <td class=\"DocHead\"><b><div align=\"left\">$langName</div></b></td>";
	$tool_content .= "\n    <td width='100' class=\"DocHead\"><b>$langSize</b></td>";
	$tool_content .= "\n    <td width='100' class=\"DocHead\"><b>$langDate</b></td>";
	if($is_adminOfCourse) {
		$tool_content .= "\n    <td width='100' class=\"DocHead\"><b>$langCommands</b></td>";
	}
	$tool_content .= "\n  </tr>";
	//$tool_content .= "\n  </thead>";
	//$tool_content .= "\n  </tbody>";

// ---------------------------------
// Display Directories
// ---------------------------------
	if (isset($dirNameList))
	{
		while (list($dirKey, $dirName) = each($dirNameList)) {
			$result = db_query("SELECT filename FROM document WHERE path LIKE '%$dirName'");
			$row = mysql_fetch_array($result);
			$dspDirName = $row['filename'];
			$cmdDirName = rawurlencode($curDirPath."/".$dirName);
			if (@$dirVisibilityList[$dirKey] == "i") {
				$style=" class=\"invisible\"";
				$style2 = " class=\"invisible_doc\"";
			} else {
				$style="";
				$style2="";
			}
		// do not display invisible directories to students
			if ((@$dirVisibilityList[$dirKey] == "i")  and (!$is_adminOfCourse)) {
				continue;
			}
			$tool_content .=  "\n  <tr $style2>";
			$tool_content .=  "\n    <td width='1%' valign=\"top\" style=\"padding-top: 7px;\"><a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdDirName."\"".$style."><img src=\"../../template/classic/img/folder.gif\" border=0></a></td>";
			$tool_content .=  "\n    <td><div align=\"left\">";
			$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdDirName."\"".$style.">";
			$tool_content .=  $dspDirName."";
			$tool_content .=  "</a>";
			/*** comments ***/
			if (@$dirCommentList[$dirKey] != "")
			{
				$dirCommentList[$dirKey] = htmlspecialchars($dirCommentList[$dirKey]);
				$dirCommentList[$dirKey] = nl2br($dirCommentList[$dirKey]);
				$tool_content .=  "<br /><span class=\"comment\">";
				$tool_content .=  " (".$dirCommentList[$dirKey].")";
				$tool_content .=  "</span>\n";
			}
			/*** skip display date and time ***/
			$tool_content .=  "</div></td>";
			$tool_content .=  "\n    <td>&nbsp;</td>";
			$tool_content .=  "\n    <td>&nbsp;</td>";
			if($is_adminOfCourse) {
				/*** delete command ***/
				@$tool_content .=  "\n    <td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdDirName."\" onClick=\"return confirmation('".addslashes($dspDirName)."');\">";
                $tool_content .=  "<img src=\"../../template/classic/img/delete.gif\" border=0 title=\"$langDelete\"></a>&nbsp;";
				/*** copy command ***/
				$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?move=".$cmdDirName."\">";
                $tool_content .=  "<img src=\"../../template/classic/img/move_doc.gif\" border=0 title=\"$langMove\"></a>&nbsp;";
				/*** rename command ***/
				$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?rename=".$cmdDirName."\">";
                $tool_content .=  "<img src=\"../../template/classic/img/edit.gif\" border=0 title=\"$langRename\"></a>&nbsp;";
				/*** comment command ***/
				$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?comment=".$cmdDirName."\">";
                $tool_content .=  "<img src=\"../../template/classic/img/information.gif\" border=0 title=\"$langComment\"></a>&nbsp;";
				/*** visibility command ***/
				if (@$dirVisibilityList[$dirKey] == "i") {
					$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?mkVisibl=".$cmdDirName."\">";
                    $tool_content .=  "<img src=\"../../template/classic/img/invisible.gif\" border=0 title=\"$langVisible\"></a>";
				}
				else {
					$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?mkInvisibl=".$cmdDirName."\">";
                    $tool_content .=  "<img src=\"../../template/classic/img/visible.gif\" border=0 title=\"$langVisible\"></a>";
				}
				$tool_content .=  "</td>";
			    $tool_content .=  "\n  </tr>";
			}
		}
	}

	// ------------------------------
	//       Display Files
	// ------------------------------
	if (isset($fileNameList)) {
		while (list($fileKey, $fileName) = each ($fileNameList)) {
			$image = choose_image($fileName);
			$size = format_file_size($fileSizeList[$fileKey]);
			$date = format_date($fileDateList[$fileKey]);
			$urlFileName = format_url($baseServUrl.$courseDir.$curDirPath."/".$fileName);
			$cmdFileName = rawurlencode($curDirPath."/".$fileName);
			$dspFileName = htmlspecialchars($fileName);
			if (@$fileVisibilityList[$fileKey] == "i") {
				$style2=" class=\"invisible_doc\"";
			} else {
				$style2="";
			}
			// do not display invisible files to students
			if ((@$fileVisibilityList[$fileKey] == "i")  and (!$is_adminOfCourse)) {
				continue;
			}
			$tool_content .=  "\n  <tr ".$style2.">";
			$tool_content .=  "\n    <td valign=\"top\" valign=\"top\" style=\"padding-top: 7px;\">";
            $tool_content .=  "<img src=\"./img/".$image."\" align='absmiddle' border=0>";
			//h $dspFileName periexei to onoma tou arxeiou sto filesystem
			$query = "SELECT filename, copyrighted FROM document WHERE path LIKE '%".$curDirPath."/".$fileName."%'";
			$result = mysql_query ($query);
			$row = mysql_fetch_array($result);
			$tool_content .=  "</td>";
            $tool_content .=  "\n    <td>";
			$tool_content .=  "<div align=\"left\"><a href='$_SERVER[PHP_SELF]?action2=download&id=".$cmdFileName."' 	title=\"$langSave\">".$row["filename"];
			if ($row["copyrighted"] == "1")
				$tool_content .= " <img src=\"./img/copyrighted.jpg\" align='absmiddle' border=\"0\">";
			$tool_content .= "</a>";
			/*** comments ***/
			if (@$fileCommentList[$fileKey] != "")
			{
				$fileCommentList[$fileKey] = htmlspecialchars($fileCommentList[$fileKey]);
				$fileCommentList[$fileKey] = nl2br($fileCommentList[$fileKey]);
				$tool_content .=  "&nbsp;<br /><span class=\"comment\">";
				$tool_content .=  " (".$fileCommentList[$fileKey].")";
				$tool_content .=  "</span>";
			}
			$tool_content .=  "</div></td>";
			/*** size ***/
			$tool_content .=  "\n    <td>".$size."</td>";
			/*** date ***/
			$tool_content .=  "\n    <td>".$date."</td>";
			if($is_adminOfCourse) {
				/*** delete command ***/
				$tool_content .=  "\n    <td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdFileName."\" onClick=\"return confirmation('".addslashes($row["filename"])."');\">";
                $tool_content .=  "<img src=\"../../template/classic/img/delete.gif\" border=0  title=\"$langDelete\"></a>&nbsp;";
				/*** copy command ***/
				$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?move=".$cmdFileName."\">";
                $tool_content .=  "<img src=\"../../template/classic/img/move_doc.gif\" border=0  title=\"$langMove\"></a>&nbsp;";
				/*** rename command ***/
				$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?rename=".$cmdFileName."\">";
                $tool_content .=  "<img src=\"../../template/classic/img/edit.gif\" border=0  title=\"$langRename\"></a>&nbsp;";
				/*** comment command ***/
				$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?comment=".$cmdFileName."\">";
                $tool_content .=  "<img src=\"../../template/classic/img/information.gif\" border=0  title=\"$langComment\"></a>&nbsp;";
				/*** visibility command ***/
				if (@$fileVisibilityList[$fileKey] == "i")
				{
					$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?mkVisibl=".$cmdFileName."\">";
                    $tool_content .=  "<img src=\"../../template/classic/img/invisible.gif\" border=0  title=\"$langVisible\"></a>";
				} else {
					$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?mkInvisibl=".$cmdFileName."\">";
                    $tool_content .=  "<img src=\"../../template/classic/img/visible.gif\" border=0  title=\"$langVisible\"></a>";
				}
				$tool_content .=  "</td>";
                $tool_content .=  "\n  </tr>";
			}
		}
	}
    $tool_content .=  "\n  </tbody>";
    $tool_content .=  "\n  </table>";
    $tool_content .=  "\n</div>";
}
$tmp_cwd = getcwd();
chdir($baseServDir."/modules/document/");
draw($tool_content, 2, "document", $local_head);
chdir($tmp_cwd);
?>

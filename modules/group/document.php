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
* @version $Id$
@last update: 21-12-2006 by Evelthon Prodromou
@author  Dionysios G. Synodinos <synodinos@gmail.com>
@author Evelthon Prodromou <eprodromou@upnet.gr>
==============================================================================
@Description: For uploading documents

This is a tool plugin that allows course administrators - or others with the
same rights

The user can : - navigate through files and directories.
- upload a file
- delete, copy a file or a directory
- edit properties & content (name
html content)
*/

$require_current_course = TRUE;
$require_help = TRUE;
$require_login = true;
$helpTopic = 'Doc';

include '../../include/baseTheme.php';
include '../../include/lib/forcedownload.php';
include "../../include/lib/fileDisplayLib.inc.php";
include "../../include/lib/fileManageLib.inc.php";
include "../../include/lib/fileUploadLib.inc.php";

$nameTools = $langDoc;
$navigation[] = array ("url" => "group_space.php", "name" => $langGroupSpace);
$baseServDir = $webDir;
$tool_content = $dialogBox = "";

$local_head = '
<script>
function confirmation ()
{
    if (confirm("'.$langConfirmDelete.'"))
        {return true;}
    else
        {return false;}
}
</script>
';

$sql_result = db_query("SELECT group_quota FROM cours WHERE code='$currentCourseID'", $mysqlMainDb);
$d = mysql_fetch_array($sql_result);
$diskQuotaGroup = $d['group_quota'];

/**************************************
/FILEMANAGER BASIC VARIABLES DEFINITION
**************************************/
if ($is_adminOfCourse) {
	$secretDirectory = group_secret($userGroupId);
} else {
	$secretDirectory = group_secret(user_group($uid));
}
if (empty($secretDirectory)) {
	$tool_content .= $langInvalidGroupDir;
	draw($tool_content, 2, 'group', $local_head);
	exit;
}

$baseServDir = $webDir;
$baseServUrl = $urlAppend."/";
$courseDir = "courses/".$dbname."/group/".$secretDirectory;
$baseWorkDir = $baseServDir.$courseDir;

// -------------------------
// download
// -------------------------
if (isset($action2) and $action2 == "download")  {
	$real_file = $webDir."/courses/".$currentCourseID."/group/".$secretDirectory."/".$id;
	if (strpos($real_file, '/../') === FALSE) {
		$result = db_query ("SELECT filename FROM group_documents WHERE path =" . quote($id), $currentCourseID);
		$row = mysql_fetch_array($result);
		if (!empty($row['filename']))
		{
			$id = $row['filename'];
		}
		send_file_to_client($real_file, my_basename($id), true);
		exit;
	} else {
		header("Refresh: ${urlServer}modules/group/document.php");
	}
}

/*** clean information submited by the user from antislash ***/
stripSubmitValue($_POST);
stripSubmitValue($_GET);

/**************************************
UPLOAD FILE
**************************************/
if (is_uploaded_file(@$userFile) )
{
	/* Check the file size doesn't exceed
	* the maximum file size authorized in the directory
	*/
	$diskUsed = dir_total_space($baseWorkDir);
	if ($diskUsed + $_FILES['userFile']['size'] > $diskQuotaGroup)
	{
		$dialogBox .= $langNoSpace;
	}
	elseif (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' .
		'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' .
		'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $_FILES['userFile']['name'])) {
			$dialogBox .= "$langUnwantedFiletype: {$_FILES['userFile']['name']}";
	} else {
		$fileName = trim($_FILES['userFile']['name']);
		/**** Check for no desired characters ***/
		$fileName = replace_dangerous_char($fileName);
		/*** Try to add an extension to files witout extension ***/
		$fileName = add_ext_on_mime($fileName);
		/*** Handle PHP files ***/
		$fileName = php2phps($fileName);
		$safe_fileName = date("YmdGis").randomkeys("8").".".get_file_extention($fileName);
		$path = $uploadPath."/".$safe_fileName;
		/*** Copy the file to the desired destination ***/
		copy ($userFile, $baseWorkDir.$uploadPath."/".$safe_fileName);
		@$dialogBox .= "<table width=\"99%\"><tbody>
			<tr><td class=\"success\"><p><b>$langDownloadEnd</b></p></td></tr>
			</tbody></table>";
		db_query('INSERT INTO group_documents SET
			        path='.quote($path).',
                                filename='.quote($fileName));

	} // end else
} // end if is_uploaded_file

/**************************************
MOVE FILE OR DIRECTORY
**************************************/
if (isset($moveTo))
{
	//elegxos ean source kai destintation einai to idio
	if($baseWorkDir."/".$source != $baseWorkDir.$moveTo || $baseWorkDir.$source != $baseWorkDir.$moveTo) {
		if (move($baseWorkDir.$source,$baseWorkDir.$moveTo) ) {
			update_db_info("group_documents", "update", $source, $moveTo."/".my_basename($source));
			$dialogBox =  "<p class=\"success_small\">$langMoveOK</p><br />";
		} else {
			$dialogBox = "<p class=\"caution_small\">$langMoveNotOK</p><br />";
			/*** return to step 1 ***/
			$move = $source;
			unset ($moveTo);
		}
	}
}

if (isset($move)) {
	//h $move periexei to onoma tou arxeiou. anazhthsh onomatos arxeiou sth vash
	$result = mysql_query ("SELECT * FROM group_documents WHERE path=\"".$move."\"");
	$res = mysql_fetch_array($result);
	$moveFileNameAlias = $res['filename'];
	@$dialogBox .= form_dir_list_exclude("group_documents", "source", $move, "moveTo", $baseWorkDir, $move);
	}

/**************************************
DELETE FILE OR DIRECTORY
**************************************/
if (isset($delete))
{
	if (my_delete($baseWorkDir.$delete)) {
		update_db_info("group_documents", "delete", $delete);
		$dialogBox = "<p class=\"success_small\">$langDocDeleted</p><br />";
	}
}

/*****************************************
	RENAME
******************************************/
if (isset($renameTo)) {
	$query =  "UPDATE group_documents SET filename=\"".$renameTo."\" WHERE path=\"".$sourceFile."\"";
	db_query($query);
	$dialogBox = "<p class=\"success_small\">$langElRen</p><br />";
}

// rename
if (isset($rename)) {
	$result = mysql_query ("SELECT * FROM group_documents WHERE path=\"".$rename."\"");
	$res = mysql_fetch_array($result);
	$fileName = $res["filename"];
	@$dialogBox .= "<form>\n";
	$dialogBox .= "<input type=\"hidden\" name=\"sourceFile\" value=\"$rename\">
        <table class='FormData' width=\"99%\"><tbody><tr>
        <th class='left' width='200'>$langRename:</th>
        <td class='left'>$langRename ".htmlspecialchars($fileName)." $langIn: <input type=\"text\" name=\"renameTo\" value=\"$fileName\" class='FormData_InputText' size='50'></td>
        <td class='left' width='1'><input type=\"submit\" value=\"$langRename\"></td>
        </tr></tbody></table></form><br />";
}

/*****************************************
CREATE DIRECTORY
*****************************************/
if (isset($newDirPath) && isset($newDirName)) {
        $newDirName = trim($newDirName);
        $r = db_query('SELECT * FROM group_documents WHERE filename = ' . quote($newDirName));
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
                mkdir("$baseWorkDir$newDirPath/$safe_dirName", 0775);
                db_query('INSERT INTO group_documents SET
                                path='.quote($newDirPath.'/'.$safe_dirName).',
                                filename='.quote($newDirName));
                $dialogBox = "<p class=\"success_small\">$langDirCr</p><br />";
        }
}

/*-------------------------------------
STEP 1
--------------------------------------*/
if (isset($createDir))
{
	//$dialogBox ="";
	$dialogBox .= "<form>\n";
	$dialogBox .= "<input type=\"hidden\" name=\"newDirPath\" value=\"$createDir\">\n";
	$dialogBox .= "<table class='FormData' width=\"99%\"><tbody><tr><th class='left' width=\"220\">$langNewDir:</th>";
	$dialogBox .= "<td class='left' width=\"1\"><input type=\"text\" name=\"newDirName\" class='FormData_InputText'></td>";
	$dialogBox .= "<td class='left'><input type=\"submit\" value=\"$langCreate\"></td> \n";
	$dialogBox .= "</tr></tbody></table></form><br />\n";
}

/**************************************
DEFINE CURRENT DIRECTORY
**************************************/
if (isset($openDir)  || isset($moveTo) || isset($createDir) || isset($newDirPath) || isset($uploadPath)) // $newDirPath is from createDir command (step 2) and $uploadPath from upload command
{
	@$curDirPath = $openDir . $createDir . $moveTo . $newDirPath . $uploadPath;
}
elseif (isset($delete) || isset($move) || isset($rename) || isset($sourceFile)) //$sourceFile is from rename command (step 2)
{
	@$curDirPath = dirname($delete . $move . $rename . $sourceFile);
}
else
{
	$curDirPath="";
}

if ($curDirPath == "/" || $curDirPath == "\\")
{
	$curDirPath =""; // manage the root directory problem
}

$curDirName = my_basename($curDirPath);
$parentDir = dirname($curDirPath);

if ($parentDir == "/" || $parentDir == "\\")
{
	$parentDir =""; // manage the root directory problem
}

/**************************************
READ CURRENT DIRECTORY CONTENT
**************************************/
chdir ($baseWorkDir.$curDirPath);
$handle = opendir(".");

while ($file = readdir($handle))
{
	if ($file == "." || $file == "..")
	{
		continue; // Skip current and parent directories
	}
	if(is_dir($file))
	{
		$dirNameList[] = $file;
	}
	if(is_file($file))
	{
		$fileNameList[] = $file;
		$fileSizeList[] = filesize($file);
		$fileDateList[] = filectime($file);
	}
}
closedir($handle);

/*** Sort alphabetically ***/

if (isset($dirNameList))
{
	asort($dirNameList);
}

if (isset($fileNameList))
{
	asort($fileNameList);
}

/**************************************
DISPLAY
**************************************/
$dspCurDirName = htmlspecialchars($curDirName);
$resultGroup=db_query("SELECT forumId FROM student_group WHERE id='$userGroupId'", $dbname);

while ($myGroup = mysql_fetch_array($resultGroup)) {
	$forumId=$myGroup['forumId'];
}
$tool_content .= "
    <div id=\"operations_container\">
      <ul id=\"opslist\"><li><a href='group_space.php'>$langGroupSpaceLink</a></li>
        <li><a href='../phpbb/viewforum.php?forum=$forumId'>$langGroupForumLink</a></li>
        <li><a href='$_SERVER[PHP_SELF]?createDir=".$curDirPath."'>$langCreateDir</a></li>
        <li><a href='$_SERVER[PHP_SELF]?uploadPath=".$curDirPath."'>$langDownloadFile</a></li>
      </ul>
    </div>";

/*----------------------------------------
DIALOG BOX SECTION
--------------------------------------*/
if (isset($dialogBox))
{
	$tool_content .= <<<cData
	${dialogBox} <br/>
cData;

}
else
{
	$tool_content .=  "<td>&nbsp</td>";
}

/*----------------------------------------
UPLOAD SECTION
--------------------------------------*/

if(isset($uploadPath)) {
	$tool_content .= <<<cData
	<form action='$_SERVER[PHP_SELF]' method='post' enctype='multipart/form-data'>
	<input type='hidden' name='uploadPath' value='$curDirPath'>
	<table class='FormData' width=\"99%\">
    <tbody>
    <tr>
      <th class='left' width=\"220\">$langDownloadFile :</th>
      <td class='left' width=\"1\"><input type='file' name='userFile'></td>
      <td class='left'><input type='submit' value='$langUpload'></td>
    </tr>
    </thead>
    </table>
	</form>
    <br/>
cData;
}

/*------------------------------------
CURRENT DIRECTORY LINE
--------------------------------------*/
$tool_content .= "
    <table width=\"99%\" align='left' class=\"Documents\">
    <tbody>
    <tr>
        <th height='18' colspan='7'><div align='left'>$langDirectory: ".make_clickable_path("group_documents", $curDirPath). "</div></th>
        <th><div align='right'>";
  /*** go to parent directory ***/
if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
{
	$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?openDir=".$parentDir."\">$langUp</a>\n";
	$tool_content .=  "<img src=\"../../template/classic/img/parent.gif\" border=0 align=\"absmiddle\">";
}

$tool_content .= "</th>\n    </tr>";
$tool_content .= "\n    <tr>";
$tool_content .= "
        <td class=\"DocHead\" colspan=\"2\"><b>$langName</b></td>
        <td class=\"DocHead\"><b>$langSize</b></td>
        <td class=\"DocHead\"><b>$langDate</b></td>
        <td class=\"DocHead\"><b>$langPublish</b></td>
        <td class=\"DocHead\" colspan=\"3\"><b>$langCommands</b></td>";
$tool_content .= "\n    </tr>";

/*----------------------------------------
DISPLAY DIRECTORIES
------------------------------------------*/
if (isset($dirNameList))
{
	while (list($dirKey, $dirName) = each($dirNameList))
	{
		$result = db_query ("SELECT filename FROM group_documents WHERE path LIKE '%$dirName'");
		$row = mysql_fetch_array($result);
		$dspDirName = $row['filename'];
		$cmdDirName = $curDirPath."/".$dirName;
		$tool_content .= "\n    <tr>";
		$tool_content .= "\n        <td width=\"1\">";
		$tool_content .= "<a href='$_SERVER[PHP_SELF]?openDir=$cmdDirName'".@$style.">\n";
		$tool_content .= "<img src=\"../../template/classic/img/folder.gif\" border=0 hspace=5>";
		$tool_content .= "</a></td>";
		$tool_content .= "\n        <td align='left'><div align='left'>";
		$tool_content .= "<a href='$_SERVER[PHP_SELF]?openDir=$cmdDirName'".@$style.">\n";
		$tool_content .= $dspDirName;
		$tool_content .= "</a></div></td>";
		/*** skip display date and time ***/
		$tool_content .= "\n        <td>-</td>";
		$tool_content .= "\n        <td>-</td>";
		$tool_content .= "\n        <td>&nbsp;</td>";
		/*** copy command ***/
		$tool_content .= "\n        <td width=\"1\"><a href=\"$_SERVER[PHP_SELF]?move=".$cmdDirName."\">
		<img src=\"../../template/classic/img/move_doc.gif\" border=0 title=\"$langMove\"></a></td>\n";
		/*** rename command ***/
		$tool_content .= "\n        <td width=\"1\"><a href=\"$_SERVER[PHP_SELF]?rename=".$cmdDirName."\">
		<img src=\"../../template/classic/img/edit.gif\" border=0 title=\"$langRename\"></a></td>\n";
		/*** delete command ***/
		$tool_content .= "\n        <td width=\"1\"><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdDirName."\" onClick=\"return confirmation();\"><img src=\"../../template/classic/img/delete.gif\" title=\"$langDelete\" border=0></a></td>\n";
		$tool_content .=  "\n    </tr>\n";
	}
}

/*----------------------------------------
DISPLAY FILES
--------------------------------------*/
if (isset($fileNameList))
{
	while (list($fileKey, $fileName) = each ($fileNameList))
	{
		$image = choose_image($fileName);
		$size = format_file_size($fileSizeList[$fileKey]);
		$date = format_date($fileDateList[$fileKey]);
		$urlFileName = format_url("../../".$courseDir.$curDirPath."/".$fileName);
		$urlShortFileName = format_url("$curDirPath/$fileName");
		$cmdFileName = rawurlencode($curDirPath."/".$fileName);
		$dspFileName = htmlspecialchars($fileName);
		$tool_content .= "\n    <tr align=\"center\"".@$style.">\n";
		$tool_content .= "\n        <td width=\"1\">";
		$tool_content .= "<img src='../document/img/$image' border=0 hspace=5 align=absmiddle>";
		$tool_content .= "</td>";
		$tool_content .= "\n        <td align=\"left\"><div align='left'>\n";
		$result = db_query("SELECT path, filename FROM group_documents
			WHERE path LIKE '%/$fileName%'", $currentCourseID);
		$r = mysql_fetch_array($result);

		if(empty($r["filename"])) { // compatibility
			$tool_content .=  "<a href='$_SERVER[PHP_SELF]?action2=download&id=".$cmdFileName."' title=\"$langSave\">".$dspFileName."</a>";
		} else {
			$tool_content .= "<a href='$_SERVER[PHP_SELF]?action2=download&id=$r[path]'>$r[filename]</a>";
		}
		$tool_content .= "</div></td>";
		/*** size ***/
		$tool_content .= "\n        <td>".$size."</td>\n";
		/*** date ***/
		$tool_content .= "\n        <td>".$date."</td>\n";
		$tool_content .= "\n        <td>
		<a href=\"../work/group_work.php?submit=$urlShortFileName\">$langPublish</a></td>\n";
		/*** copy command ***/
		$tool_content .= "\n        <td><a href=\"$_SERVER[PHP_SELF]?move=".$cmdFileName."\">
		<img src=\"../../template/classic/img/move_doc.gif\" border=0></a></td>\n";
		/*** rename command ***/
		$tool_content .= "\n        <td><a href=\"$_SERVER[PHP_SELF]?rename=".$cmdFileName."\">
		<img src=\"../../template/classic/img/edit.gif\" border=0></a></td>\n";
		/*** delete command ***/
		@$tool_content .= "\n        <td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdFileName."\" onClick=\"return confirmation();\">
		<img src=\"../../template/classic/img/delete.gif\" border=0></a></td>\n";
		/*** submit command ***/
		$tool_content .= "\n    </tr>\n";
	}
}
$tool_content .= "\n    </tbody>\n    </table>\n";
$tool_content .= "</div>\n";
chdir($baseServDir."/modules/group/");

draw($tool_content, 2, 'group', $local_head);
?>

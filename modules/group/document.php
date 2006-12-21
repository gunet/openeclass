<?php
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
- edit properties & content (name, comments,
html content)

@Comments: The script is organised in four sections.

1) Execute the command called by the user
Note (March 2004) some editing functions (renaming, commenting)
are moved to a separate page, edit_document.php. This is also
where xml and other stuff should be added.
2) Define the directory to display
3) Read files and directories from the directory defined in part 2
4) Display all of that on an HTML page
*/

$langFiles = 'document';
$require_current_course = TRUE;
$require_help = TRUE;
$require_login = true;
$helpTopic = 'Doc';
include '../../include/baseTheme.php';
$nameTools = $langDoc;
$navigation[] = array ("url" => "group_space.php", "name" => $langGroupSpace);
$baseServDir = $webDir;
$tool_content = "";

include ("../../include/lib/fileDisplayLib.inc.php");
include ("../../include/lib/fileManageLib.inc.php");
include ("../../include/lib/fileUploadLib.inc.php");

$local_head = '
<script>
function confirmation (name)
{
    if (confirm("'.$langAreYouSureToDelete.'"+ name + " ?"))
        {return true;}
    else
        {return false;}
}
</script>
';

if (isset($uncompress) && $uncompress == 1)
include("../../include/pclzip/pclzip.lib.php");

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
	die("Error: can't find group document directory");
}

$baseServDir = $webDir;
$baseServUrl = $urlAppend."/";
$courseDir = "courses/".$dbname."/group/".$secretDirectory;
$baseWorkDir = $baseServDir.$courseDir;

//$tool_content .=  "</td></tr></table></center>";

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
	else
	{
		$fileName = trim ($_FILES['userFile']['name']);

		/**** Check for no desired characters ***/
		$fileName = replace_dangerous_char($fileName);

		/*** Try to add an extension to files witout extension ***/
		$fileName = add_ext_on_mime($fileName);

		/*** Handle PHP files ***/
		$fileName = php2phps($fileName);

		/*** Copy the file to the desired destination ***/
		copy ($userFile, $baseWorkDir.$uploadPath."/".$fileName);

		@$dialogBox .= " <table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langDownloadEnd</b></p>
							
						</td>
					</tr>
				</tbody>
			</table>";

	} // end else

} // end if is_uploaded_file


/**************************************
MOVE FILE OR DIRECTORY
**************************************/

/*
* The code begin with STEP 2
* so it allows to return to STEP 1 if STEP 2 unsucceeds
*/

/*-------------------------------------
MOVE FILE OR DIRECTORY : STEP 2
--------------------------------------*/

if (isset($moveTo))
{
	if ( move($baseWorkDir.$source,$baseWorkDir.$moveTo) )
	{
		$dialogBox =  "<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langMoveOK</b></p>
							
						</td>
					</tr>
				</tbody>
			</table>";
	}
	else
	{
		$dialogBox = "<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p><b>$langMoveNotOK</b></p>
							
						</td>
					</tr>
				</tbody>
			</table>";

		/*** return to step 1 ***/
		$move = $source;
		unset ($moveTo);
	}
}

/*-------------------------------------
MOVE FILE OR DIRECTORY : STEP 1
--------------------------------------*/
if (isset($move)) {
	@$dialogBox .= form_dir_list("source", $move, "moveTo", $baseWorkDir);
}


/**************************************
DELETE FILE OR DIRECTORY
**************************************/


if ( isset($delete) )
{
	if ( my_delete($baseWorkDir.$delete))
	{
		$dialogBox = "<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langDocDeleted</b></p>
							
						</td>
					</tr>
				</tbody>
			</table>";
	}
}

/*****************************************
RENAME
******************************************/

if (isset($renameTo))
{
	if ( my_rename($baseWorkDir.$sourceFile, $renameTo) )
	{
		$dialogBox = " <table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langElRen</b></p>
							
						</td>
					</tr>
				</tbody>
			</table>";
	}
	else
	{
		$dialogBox = "<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p><b>$langFileExists</b></p>
							
						</td>
					</tr>
				</tbody>
			</table><br/>";

		/*** return to step 1 ***/
		$rename = $sourceFile;
		unset($sourceFile);
	}
}


/*-------------------------------------
RENAME : STEP 1
--------------------------------------*/

if (isset($rename))
{
	$fileName = basename($rename);
	@$dialogBox .= "<!-- rename -->\n";
	$dialogBox .= "<form>\n";
	$dialogBox .= "<input type=\"hidden\" name=\"sourceFile\" value=\"$rename\">\n";
	$dialogBox .= "<table><thead><tr><th>$langRename ".htmlspecialchars($fileName)." $langIn :</th>";
	$dialogBox .= "<td><input type=\"text\" name=\"renameTo\" value=\"$fileName\"></td></thead></table>";
	$dialogBox .= "<br/><input type=\"submit\" value=\"OK\">\n";
	$dialogBox .= "</form>\n";
}


/*****************************************
CREATE DIRECTORY
*****************************************/

if (isset($newDirPath) && isset($newDirName))
{
	$newDirName = replace_dangerous_char($newDirName);
	if ( check_name_exist($baseWorkDir.$newDirPath."/".$newDirName) )
	{
		@$dialogBox .= "<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p><b>$langFileExists</b></p>
							
						</td>
					</tr>
				</tbody>
			</table>";
		$createDir = $newDirPath; unset($newDirPath);// return to step 1
	}
	else
	{
		mkdir($baseWorkDir.$newDirPath."/".$newDirName, 0700);
	}

	$dialogBox = "<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langDirCr</b></p>
							
						</td>
					</tr>
				</tbody>
			</table>";
}


/*-------------------------------------
STEP 1
--------------------------------------*/

if (isset($createDir))
{
	@$dialogBox .= "<!-- create dir -->\n";
	$dialogBox .= "<form>\n";
	$dialogBox .= "<input type=\"hidden\" name=\"newDirPath\" value=\"$createDir\">\n";
	$dialogBox .= "<table><thead><tr><th>$langNewDir:</th>";
	$dialogBox .= "<td><input type=\"text\" name=\"newDirName\"></td></thead></table><br/>";
	$dialogBox .= "<input type=\"submit\" value=\"Ok\">\n";
	$dialogBox .= "</form>\n";
}


/*****************************************


/**************************************
DEFINE CURRENT DIRECTORY
**************************************/

if (isset($openDir)  || isset($moveTo) || isset($createDir) || isset($newDirPath) || isset($uploadPath) ) // $newDirPath is from createDir command (step 2) and $uploadPath from upload command
{
	@$curDirPath = $openDir . $createDir . $moveTo . $newDirPath . $uploadPath;
	/*
	* NOTE: Actually, only one of these variables is set.
	* By concatenating them, we eschew a long list of "if" statements
	*/
}
elseif ( isset($delete) || isset($move) || isset($rename) || isset($sourceFile) || isset($comment) || isset($commentPath) || isset($mkVisibl) || isset($mkInvisibl)) //$sourceFile is from rename command (step 2)
{
	@$curDirPath = dirname($delete . $move . $rename . $sourceFile . $comment . $commentPath . $mkVisibl . $mkInvisibl);
	/*
	* NOTE: Actually, only one of these variables is set.
	* By concatenating them, we eschew a long list of "if" statements
	*/
}
else
{
	$curDirPath="";
}

if ($curDirPath == "/" || $curDirPath == "\\")
{
	$curDirPath =""; // manage the root directory problem
}

$curDirName = basename($curDirPath);
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
} // end while ($file = readdir($handle))

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
$cmdCurDirPath = rawurlencode($curDirPath);
$cmdParentDir  = rawurlencode($parentDir);
//mysql_select_db($dbname);
$resultGroup=db_query("SELECT forumId
				FROM student_group 
					WHERE id='$userGroupId'", $dbname);

while ($myGroup = mysql_fetch_array($resultGroup)) {
	$forumId=$myGroup['forumId'];
}

$tool_content .= "

	<div id=\"operations_container\">
		<ul id=\"opslist\">
			<li><a href='group_space.php'>${langGroupSpaceLink}</a></li>
			<li><a href='../phpbb/viewforum.php?forum=$forumId'>$langGroupForumLink</a></li>
			<li><a href='$_SERVER[PHP_SELF]?createDir=".$cmdCurDirPath."\'>$langCreateDir</a></li>
			 <li><a href='$_SERVER[PHP_SELF]?uploadFile=1'>$langDownloadFile</a></li>
		</ul>
	</div>
	
	<div>
";

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
//	$tool_content .=  "<td>\n<!-- dialog box -->\n&nbsp;\n</td>\n";
}

/*----------------------------------------
UPLOAD SECTION
--------------------------------------*/
if(isset($uploadFile) && $uploadFile == 1) {
$tool_content .= <<<cData

	<!-- upload  -->

	<form action='${_SERVER['PHP_SELF']}' method='post' enctype='multipart/form-data'>
	<input type='hidden' name='uploadPath' value='$curDirPath'>
	<table><caption>$langDownload</caption>
	<thead>
	<tr>
	<th>
${langDownloadFile} :
</th><td>
	<input type='file' name='userFile'></td></thead></table><br/>
	<input type='submit' value='$langDownload'>
	</form><br/>
cData;
}

$tool_content .="<table width=\"99%\"><thead>";

/*----------------------------------------
CURRENT DIRECTORY LINE
--------------------------------------*/

//$tool_content .=  "<tr>\n";
//$tool_content .=  "<td colspan=8>\n";

/*** go to parent directory ***/
if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
{
//	$tool_content .=  "<!-- parent dir -->\n";
	$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdParentDir."\">\n";
	$tool_content .=  "<IMG src=\"img/parent.gif\" border=0 align=\"absbottom\" hspace=5>\n";
	$tool_content .=  "<small>$langUp</small>\n";
	$tool_content .=  "</a>\n";
}


/*** create directory ***/
//$tool_content .=  "<!-- create dir -->\n";
//
//
//$tool_content .=  "</tr>\n";
//$tool_content .=  "</td>\n";
//
//
if ($curDirName) // if the $curDirName is empty, we're in the root point and there is'nt a dir name to display
{
	/*** current directory ***/
	$tool_content .=  "<!-- current dir name -->\n";
	$tool_content .=  "<tr>\n";
	$tool_content .=  "<td colspan=\"7\" align=\"left\" bgcolor=\"#000066\">\n";
	$tool_content .=  "<img src=\"img/opendir.gif\" align=\"absbottom\" vspace=2 hspace=5>\n";
	$tool_content .=  "<font color=\"#CCCCCC\">".$dspCurDirName."</font>\n";
	$tool_content .=  "</td>\n";
	$tool_content .=  "</tr>\n";
}



//$tool_content .= "<!-- command list -->";

$tool_content .= "<tr>";

$tool_content .=  "<th>$langName</th>
<th>$langSize</th>
<th>$langDate</th>
<th>$langDelete</th>
<th>$langMove</th>
<th>$langRename</th>
<th>$langPublish</th>";
$tool_content .= "</tr></thead><tbody>";



/*----------------------------------------
DISPLAY DIRECTORIES
------------------------------------------*/

if (isset($dirNameList))
{
	while (list($dirKey, $dirName) = each($dirNameList))
	{
		$dspDirName = htmlspecialchars($dirName);
		$cmdDirName = rawurlencode($curDirPath."/".$dirName);

		$tool_content .= "<tr align=\"center\">\n";
		$tool_content .= "<td align=\"left\">\n";
		$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdDirName."\"".@$style.">\n";
		$tool_content .= "<img src=\"../../template/classic/img/folder.gif\" border=0 hspace=5>\n";
		$tool_content .= $dspDirName."\n";
		$tool_content .= "</a>\n";

		/*** skip display date and time ***/
		$tool_content .= "<td>-</td>\n";
		$tool_content .= "<td>-</td>\n";

		/*** delete command ***/
		$tool_content .= "<td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdDirName."\" onClick=\"return confirmation('".addslashes($dspDirName)."');\"><img src=\"../../template/classic/img/delete.gif\" border=0></a></td>\n";
		/*** copy command ***/
		$tool_content .= "<td><a href=\"$_SERVER[PHP_SELF]?move=".$cmdDirName."\"><img src=\"../../template/classic/img/move_doc.gif\" border=0></a></td>\n";
		/*** rename command ***/
		$tool_content .= "<td><a href=\"$_SERVER[PHP_SELF]?rename=".$cmdDirName."\"><img src=\"../../template/classic/img/edit.gif\" border=0></a></td>\n";
		/*** comment command ***/
		$tool_content .= "<td>-</td>\n";

		$tool_content .=  "</tr>\n";
	}
}


/*----------------------------------------
DISPLAY FILES
--------------------------------------*/

if (isset($fileNameList))
{
	while (list($fileKey, $fileName) = each ($fileNameList))
	{
		$image       = choose_image($fileName);
		$size        = format_file_size($fileSizeList[$fileKey]);
		$date        = format_date($fileDateList[$fileKey]);
		$urlFileName = format_url("../../".$courseDir.$curDirPath."/".$fileName);
		$urlShortFileName = format_url("$curDirPath/$fileName");

		$cmdFileName = rawurlencode($curDirPath."/".$fileName);
		$dspFileName = htmlspecialchars($fileName);

		$tool_content .= "<tr align=\"center\"".@$style.">\n";
		$tool_content .= "<td align=\"left\">\n";
		$tool_content .= "<a href=\"".$urlFileName."\"".@$style.">\n";
		$tool_content .= "<img src=\"./img/".$image."\" border=0 hspace=5>\n";
		$tool_content .= $dspFileName."\n";
		$tool_content .= "</a>\n";

		/*** size ***/
		$tool_content .= "<td>".$size."</td>\n";
		/*** date ***/
		$tool_content .= "<td>".$date."</td>\n";

		/*** delete command ***/
		$tool_content .= "<td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdFileName."\" onClick=\"return confirmation('".addslashes($dspDirName)."');\"><img src=\"../../template/classic/img/delete.gif\" border=0></a></td>\n";
		/*** copy command ***/
		$tool_content .= "<td><a href=\"$_SERVER[PHP_SELF]?move=".$cmdFileName."\"><img src=\"../../template/classic/img/move_doc.gif\" border=0></a></td>\n";
		/*** rename command ***/
		$tool_content .= "<td><a href=\"$_SERVER[PHP_SELF]?rename=".$cmdFileName."\"><img src=\"../../template/classic/img/edit.gif\" border=0></a></td>\n";
		/*** submit command ***/
		$tool_content .= "<td><a href=\"../work/group_work.php?submit=$urlShortFileName\">$langPublish</a></td>\n";

		$tool_content .= "</tr>\n";
	}
}
$tool_content .= "</tbody></table>\n";
$tool_content .= "</div>\n";

chdir($baseServDir."/modules/group/");

draw($tool_content, 2, '', $local_head);
?>

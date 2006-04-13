<?php
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.3.0 $Revision$                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 2000, 2001 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
  |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
  |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
  +----------------------------------------------------------------------+

  DESCRIPTION:
  ****
  This PHP script allow user to manage files and directories on a remote http server.
  The user can : - navigate trough files and directories.
                 - upload a file
				 - rename, delete, copy a file or a directory

  The script is organised in four sections.

  * 1st section execute the command called by the user
                Note: somme commands of this section is organised in two step.
			    The script lines always begin by the second step,
			    so it allows to return more easily to the first step.

  * 2nd section define the directory to display

  * 3rd section read files and directories from the directory defined in part 3

  * 4th section display all of that on a HTML page
*/

$langFiles = 'document';
$require_current_course = TRUE;
include('../../include/init.php');

include("../../include/lib/fileDisplayLib.inc.php");
include ("../../include/lib/fileManageLib.inc.php");
include ("../../include/lib/fileUploadLib.inc.php");

if (isset($uncompress) && $uncompress == 1)
		include("../../include/pclzip/pclzip.lib.php");

// added by jexi
$d = mysql_fetch_array(mysql_query("SELECT group_quota FROM cours WHERE code='$currentCourseID'"));
$diskQuotaGroup = $d['group_quota'];

$nameTools = $langDoc;
$navigation[] = array ("url" => "group_space.php", "name" => $langGroupSpace);
begin_page();

/**************************************
FILEMANAGER BASIC VARIABLES DEFINITION
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

echo "</td></tr></table></center>";


/*** clean information submited by the user from antislash ***/

stripSubmitValue($_POST);
stripSubmitValue($_GET);



/*****************************************************************************/

/*>>>>>>>>>>>> MAIN SECTION  <<<<<<<<<<<<*/


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

		@$dialogBox .= "<b>$langDownloadEnd</b>";

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
			$dialogBox = $langMoveOK;
		}
		else
		{
			$dialogBox = $langMoveNotOK;

			/*** return to step 1 ***/
			$move = $source;
			unset ($moveTo);
		}
		
	}


	/*-------------------------------------
		MOVE FILE OR DIRECTORY : STEP 1
	--------------------------------------*/

	if (isset($move))
	{
		@$dialogBox .= form_dir_list("source", $move, "moveTo", $baseWorkDir);
	}




	/**************************************
			DELETE FILE OR DIRECTORY
	**************************************/


	if ( isset($delete) )
	{
		if ( my_delete($baseWorkDir.$delete))
		{
			$dialogBox = "<b>$langDocDeleted</b>";
		}
	}




	/*****************************************
					 RENAME
	******************************************/

	/*
	 * The code begin with STEP 2
	 * so it allows to return to STEP 1
	 * if STEP 2 unsucceds
	 */


	/*-------------------------------------
			  RENAME : STEP 2
	--------------------------------------*/

	if (isset($renameTo))
	{
		if ( my_rename($baseWorkDir.$sourceFile, $renameTo) )
		{
			$dialogBox = "<b>$langElRen.</b>";
		}
		else
		{
			$dialogBox = "<b>$langFileExists</b>";

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
		$dialogBox .= "$langRename ".htmlspecialchars($fileName)." $langIn :\n";
		$dialogBox .= "<input type=\"text\" name=\"renameTo\" value=\"$fileName\">\n";
		$dialogBox .= "<input type=\"submit\" value=\"OK\">\n";
		$dialogBox .= "</form>\n";
	}




	/*****************************************
	           CREATE DIRECTORY
	*****************************************/

	/*
	 * The code begin with STEP 2
	 * so it allows to return to STEP 1
	 * if STEP 2 unsucceds
	 */

	/*-------------------------------------
		STEP 2
	--------------------------------------*/
	if (isset($newDirPath) && isset($newDirName))
	{
		$newDirName = replace_dangerous_char($newDirName);

		if ( check_name_exist($baseWorkDir.$newDirPath."/".$newDirName) )
		{
			@$dialogBox .= "<b>$langFileExists!</b>";
			$createDir = $newDirPath; unset($newDirPath);// return to step 1
		}
		else
		{
			mkdir($baseWorkDir.$newDirPath."/".$newDirName, 0700);
		}

		$dialogBox = "<b>$langDirCr.</b>";
	}


	/*-------------------------------------
			STEP 1
	--------------------------------------*/

	if (isset($createDir))
	{
		@$dialogBox .= "<!-- create dir -->\n";
		$dialogBox .= "<form>\n";
		$dialogBox .= "<input type=\"hidden\" name=\"newDirPath\" value=\"$createDir\">\n";
		$dialogBox .= "$langNewDir:\n";
		$dialogBox .= "<input type=\"text\" name=\"newDirName\">\n";
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

/*----------------------------------------
LOAD FILES AND DIRECTORIES INTO ARRAYS
--------------------------------------*/

//echo $baseWorkDir." --- ".$curDirPath;
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

?>
<div class="fileman" align="center">
<table width="100%" border="0" cellspacing="2" cellpadding="4">
<tr>
<td><h4><? echo $langDoc; ?></h4></td>

<td align="right">

<a href="../help/help.php?topic=Doc" 
onClick="window.open('../help/help.php?topic=Doc','Help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
return false;"> <font size=2 face="arial, helvetica"><?= $langHelp ?></font>
</a>


	</td>
</tr>

<tr>

<?php

echo "<td colspan=2>
		<a href=\"../group/group_space.php\">$langGroupSpaceLink</a>&nbsp;&nbsp;
		<a href=\"../phpbb/viewforum.php?forum=$forumId\">$langGroupForumLink</a>
	</td>
	</tr>
	<tr>";


/*----------------------------------------
	DIALOG BOX SECTION
--------------------------------------*/
if (isset($dialogBox))
{
	echo "<td bgcolor=\"#9999FF\">";
	echo "<!-- dialog box -->";
	echo $dialogBox;
	echo "</td>";
}
else
{
	echo "<td>\n<!-- dialog box -->\n&nbsp;\n</td>\n";
}


/*----------------------------------------
               UPLOAD SECTION
--------------------------------------*/
echo "<!-- upload  -->
<td align=\"right\">
<form action=\"$PHP_SELF\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"uploadPath\" value=\"$curDirPath\">
$langDownloadFile&nbsp;:
<input type=\"file\" name=\"userFile\">
<input type=\"submit\" value=\"$langDownload\">
</form>
</td>\n";

?>

</tr>
</table>

<table width="100%" border="0" cellspacing="2">

<?php


/*----------------------------------------
	CURRENT DIRECTORY LINE
--------------------------------------*/

echo "<tr>\n";
echo "<td colspan=8>\n";

/*** go to parent directory ***/
if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
{
	echo "<!-- parent dir -->\n";
	echo "<a href=\"$PHP_SELF?openDir=".$cmdParentDir."\">\n";
	echo "<IMG src=\"img/parent.gif\" border=0 align=\"absbottom\" hspace=5>\n";
	echo "<small>$langUp</small>\n";
	echo "</a>\n";
}


/*** create directory ***/
echo "<!-- create dir -->\n";
echo "<a href=\"$PHP_SELF?createDir=".$cmdCurDirPath."\">";
echo "<IMG src=\"img/dossier.gif\" border=0 align=\"absbottom\" hspace=5>";
echo "<small> $langCreateDir</small>";
echo "</a>";

echo "</tr>\n";
echo "</td>\n";


if ($curDirName) // if the $curDirName is empty, we're in the root point and there is'nt a dir name to display
{
	/*** current directory ***/
	echo "<!-- current dir name -->\n";
	echo "<tr>\n";
	echo "<td colspan=\"7\" align=\"left\" bgcolor=\"#000066\">\n";
	echo "<img src=\"img/opendir.gif\" align=\"absbottom\" vspace=2 hspace=5>\n";
	echo "<font color=\"#CCCCCC\">".$dspCurDirName."</font>\n";
	echo "</td>\n";
	echo "</tr>\n";
}

?>


<!-- command list -->

<tr bgcolor="<?php echo "$color2" ?>"  align="center" valign="top">
<?
echo "<td>$langName</td>
<td>$langSize</td>
<td>$langDate</td>
<td>$langDelete</td>
<td>$langMove</td>
<td>$langRename</td>
<td>$langPublish</td>
</tr>";



/*----------------------------------------
			DISPLAY DIRECTORIES
------------------------------------------*/

if (isset($dirNameList))
{
	while (list($dirKey, $dirName) = each($dirNameList))
	{
		$dspDirName = htmlspecialchars($dirName);
		$cmdDirName = rawurlencode($curDirPath."/".$dirName);

		echo "<tr align=\"center\">\n";
		echo "<td align=\"left\">\n";
		echo "<a href=\"$PHP_SELF?openDir=".$cmdDirName."\"".@$style.">\n";
		echo "<img src=\"img/dossier.gif\" border=0 hspace=5>\n";
		echo $dspDirName."\n";
		echo "</a>\n";

		/*** skip display date and time ***/
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";

		/*** delete command ***/
		echo "<td><a href=\"$PHP_SELF?delete=".$cmdDirName."\"><img src=\"./img/supprimer.gif\" border=0></a></td>\n";
		/*** copy command ***/
		echo "<td><a href=\"$PHP_SELF?move=".$cmdDirName."\"><img src=\"img/deplacer.gif\" border=0></a></td>\n";
		/*** rename command ***/
		echo "<td><a href=\"$PHP_SELF?rename=".$cmdDirName."\"><img src=\"img/renommer.gif\" border=0></a></td>\n";
		/*** comment command ***/
		echo "<td></td>\n";

		echo "</tr>\n";
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

		echo "<tr align=\"center\"".@$style.">\n";
		echo "<td align=\"left\">\n";
		echo "<a href=\"".$urlFileName."\"".@$style.">\n";
		echo "<img src=\"./img/".$image."\" border=0 hspace=5>\n";
		echo $dspFileName."\n";
		echo "</a>\n";

		/*** size ***/
		echo "<td><small>".$size."</small></td>\n";
		/*** date ***/
		echo "<td><small>".$date."</small></td>\n";

		/*** delete command ***/
		echo "<td><a href=\"$PHP_SELF?delete=".$cmdFileName."\"><img src=\"img/supprimer.gif\" border=0></a></td>\n";
		/*** copy command ***/
		echo "<td><a href=\"$PHP_SELF?move=".$cmdFileName."\"><img src=\"img/deplacer.gif\" border=0></a></td>\n";
		/*** rename command ***/
		echo "<td><a href=\"$PHP_SELF?rename=".$cmdFileName."\"><img src=\"img/renommer.gif\" border=0></a></td>\n";
		/*** submit command ***/
		echo "<td><a href=\"../work/group_work.php?submit=$urlShortFileName\"><small>$langPublish</small></a></td>\n";

		echo "</tr>\n";
	}
}
echo "</table>\n";
echo "</div>\n";


?>

</body>
</html>

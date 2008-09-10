<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address:	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

$require_current_course = TRUE;
include '../../include/baseTheme.php';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="refresh" content="30; url=<?= $_SERVER['PHP_SELF'] ?>" />
<link rel="stylesheet" href="<?= $urlServer ?>/template/chat.css" type="text/css" />
<title>Chat messages</title>
</head>
<body>
<?
include '../../include/lib/textLib.inc.php';

$coursePath=$webDir."courses";
$fileChatName   = $coursePath.'/'.$currentCourseID.'.chat.txt';
$tmpArchiveFile = $coursePath.'/'.$currentCourseID.'.tmpChatArchive.txt';
$pathToSaveChat = $coursePath.'/'.$currentCourseID.'/document/';

$nick = uid_to_name($uid);

// How many lines to show on screen
define('MESSAGE_LINE_NB',  40);
// How many lines to keep in temporary archive
// (the rest are in the current chat file)
define('MAX_LINE_IN_FILE', 80);

if ($GLOBALS['language'] == 'greek')
	$timeNow = date("d-m-Y / H:i",time());
else
	$timeNow = date("Y-m-d / H:i",time());

if (!file_exists($fileChatName)) {
	$fp = fopen($fileChatName, 'w')
		or die ('<center>$langChatError</center>');
	fclose($fp);
}

// chat commands

// reset command
if (isset($_GET['reset']) && $is_adminOfCourse) {
	$fchat = fopen($fileChatName,'w');
	fwrite($fchat, $timeNow." ---- ".$langWashFrom." ---- ".$nick." --------\n");
	fclose($fchat);
	@unlink($tmpArchiveFile);
}

// store
if (isset($_GET['store']) && $is_adminOfCourse) {
	$saveIn = "chat.".date("Y-m-j-B").".txt";
	$chat_filename = date("YmdGis").randomkeys("8").".txt";

	buffer(implode('', file($fileChatName)), $tmpArchiveFile);
	if (copy($tmpArchiveFile, $pathToSaveChat.$chat_filename)) {
                $alert_div=$langSaveMessage;
        } else {
                $alert_div= $langSaveErrorMessage;
        }
	echo $alert_div;
	db_query("INSERT INTO document SET path='/$chat_filename', filename='$saveIn',
		date=NOW(), date_modified=NOW()", $currentCourseID);
	exit;
}

// add new line
if (isset($chatLine) and trim($chatLine) != '') {
	$fchat = fopen($fileChatName,'a');
	fwrite($fchat,$timeNow.' - '.$nick.' : '.stripslashes($chatLine)."\n");
	fclose($fchat);
}

// display message list
$fileContent  = file($fileChatName);
$FileNbLine   = count($fileContent);
$lineToRemove = $FileNbLine - MESSAGE_LINE_NB;
if ($lineToRemove < 0) $lineToRemove = 0;
$tmp = array_splice($fileContent, 0 , $lineToRemove);

$fileReverse = array_reverse($fileContent);
foreach ($fileReverse as $thisLine) {
    echo '<small><span style="color:#727266;">', preg_replace('/ : /', '</span> : ', $thisLine), "</small><br />\n";
}

echo "</body></html>\n";


/*
 * For performance reason, buffer the content
 * in a temporary archive file
 * once the chat file is too large
 */

if ($FileNbLine > MAX_LINE_IN_FILE) {
	buffer(implode("",$tmp), $tmpArchiveFile);
	// clean the original file
	$fp = fopen($fileChatName, "w");
	fwrite($fp, implode("", $fileContent));
}

function buffer($content, $tmpFile) {
	$fp = fopen($tmpFile, "a");
	fwrite($fp, $content);
}

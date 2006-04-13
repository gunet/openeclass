<?

/*
 * This script  chat simply works with a flat file where lines are appended.
 * Simple user can  just  write lines. Admininistraor can reset and store the 
 * chat if $chatForGroup is true,  the file  is reserved because always formed 
 * with the group id of the current user in the current course.
 */

$require_login = TRUE;
$require_current_course = TRUE;
$langFiles = 'chat';
include '../../include/init.php';

include '../../include/lib/textLib.inc.php';

// CHAT MESSAGE LIST OWN'S HEADER

echo	'<html>'
	.'<head>'
	.'<meta http-equiv="refresh" content="400; url="'.$_SERVER['PHP_SELF'].'">'
	.'</head>'
	.'<body>';
 

$coursePath  =  $webDir.'courses/'.$currentCourseID;
$nick = uid_to_name($uid);

/*==========================
          CHAT INIT
  ==========================*/

$fileChatName   = $coursePath.'/'.$currentCourseID.'.chat.txt';
$tmpArchiveFile = $coursePath.'/'.$currentCourseID.'.tmpChatArchive.txt';
$pathToSaveChat = $coursePath.'/document/';

define('MESSAGE_LINE_NB',  40);
define('MAX_LINE_IN_FILE', 80);

$dateNow = claro_format_locale_date($dateTimeFormatLong);
$timeNow = date("d-m-Y H:i:s",time());

if (!file_exists($fileChatName)) {
	$fp = fopen($fileChatName, 'w')
		or die ('<center>$langChatError</center>');
	fclose($fp);
}

/*==========================
          COMMANDS
  ==========================*/

echo "<table border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" width=\" $mainInterfaceWidth\">";
echo "<tr><td>";

/*---------------------------
          RESET COMMAND
  ---------------------------*/

if (isset($_GET['reset']) && $is_adminOfCourse) {
	$fchat = fopen($fileChatName,'w');
	fwrite($fchat, $timeNow." ---- ".$langWashFrom." ---- ".$nick." --------\n");
	fclose($fchat);
	@unlink($tmpArchiveFile);
}

/*--------------------------
         STORE COMMAND
  --------------------------*/

if (isset($_GET['store']) && $is_adminOfCourse) {
	$saveIn = "chat.".date("Y-m-j-B").".txt";

	// COMPLETE ARCHIVE FILE WITH THE LAST LINES BEFORE STORING

	buffer(implode('', file($fileChatName)), $tmpArchiveFile);

	if (copy($tmpArchiveFile, $pathToSaveChat.$saveIn) ) {	
		echo "<blockquote>".$langIsNowInYourDocDir.
			"<br><a href=\"../document/document.php\" target=\"top\">",
			"<strong>".$saveIn."</strong>",
			"</a> ".$langIsChatDocVisible.
			"</blockquote>";
	} else {
		echo '<blockquote>'.$langCopyFailed.'</blockquote>';
	}
}

/*-----------------------------
      'ADD NEW LINE' COMMAND
  -----------------------------*/

if (isset($chatLine)) {
	$fchat = fopen($fileChatName,'a');
	fwrite($fchat,$timeNow.' - '.$nick.' : '.stripslashes($chatLine)."\n");
	fclose($fchat);
}

/*==========================
    DISPLAY MESSAGE LIST
  ==========================*/

/*
 * We don't show the complete message list.
 * We tail the last lines
 */

$fileContent  = file($fileChatName);
$FileNbLine   = count($fileContent);
$lineToRemove = $FileNbLine - MESSAGE_LINE_NB;
if ($lineToRemove < 0) $lineToRemove = 0;
$tmp = array_splice($fileContent, 0 , $lineToRemove);

$fileReverse = array_reverse($fileContent);
foreach ($fileReverse as $thisLine) {
    echo $thisLine.'<br />';
}

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

end_page();

?>

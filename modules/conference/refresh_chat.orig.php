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
/**
 * refresh_chat
 * 
 * @author Dimitris Tsachalis <ditsa@ccf.auth.gr>
 * @version $Id$
 * 
 * @abstract 
 *
 */
	
header("Content-type: text/html; charset=UTF-8"); 
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';
$tool_content = "";
include '../../include/baseTheme.php';
if(!isset($prenom))
        $prenom="";
if(!isset($nom))
        $nom="";
$nick=$prenom." ".$nom;

$coursePath=$webDir."courses";

/*==========================
          CHAT INIT
  ==========================*/

$fileChatName   = $coursePath.'/'.$currentCourseID.'/.chat.txt';
$tmpArchiveFile = $coursePath.'/'.$currentCourseID.'/.tmpChatArchive.txt';
$pathToSaveChat = $coursePath.'/'.$currentCourseID.'/document/';

define('MESSAGE_LINE_NB',  40);
define('MAX_LINE_IN_FILE', 80);

$timeNow = date("d-m-Y H:i:s",time());

if (!file_exists($fileChatName)) {
        $fp = fopen($fileChatName, 'w')
                or die ('<center>$langChatError</center>');
        fclose($fp);
}

/*==========================
          COMMANDS
  ==========================*/
/*---------------------------
          RESET COMMAND
  ---------------------------*/

if (isset($_POST['reset']) && $is_adminOfCourse) {
        $fchat = fopen($fileChatName,'w');
        fwrite($fchat, $timeNow." ---- ".$langWashFrom." ---- ".$nick." --------\n");
        fclose($fchat);
        @unlink($tmpArchiveFile);
}

/*--------------------------
         STORE COMMAND
  --------------------------*/
if (isset($_POST['store']) && $is_adminOfCourse) {
        $saveIn = "chat.".date("Y-m-j-B").".txt";

        // COMPLETE ARCHIVE FILE WITH THE LAST LINES BEFORE STORING

        buffer(implode('', file($fileChatName)), $tmpArchiveFile);
        if (copy($tmpArchiveFile, $pathToSaveChat.$saveIn) ) {
                $alert_div=$langSaveMessage; 
        } else {
                $alert_div= $langSaveErrorMessage;
        }
		echo $alert_div;
		exit;
}

/*-----------------------------
      'ADD NEW LINE' COMMAND
  -----------------------------*/
if (isset($chatLine)) {
	$chatLine=uft8html2utf8(utf8RawUrlDecode($chatLine));
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
    $tool_content .= $thisLine.'<br />';
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

echo "<p>".$tool_content."</p>";

function utf8RawUrlDecode ($source) {
   $decodedStr = "";
   $pos = 0;
   $len = strlen ($source);
   while ($pos < $len) {
       $charAt = substr ($source, $pos, 1);
       if ($charAt == '%') {
           $pos++;
           $charAt = substr ($source, $pos, 1);
           if ($charAt == 'u') {
               // we got a unicode character
               $pos++;
               $unicodeHexVal = substr ($source, $pos, 4);
               $unicode = hexdec ($unicodeHexVal);
               $entity = "&#". $unicode . ';';
               $decodedStr .= utf8_encode ($entity);
               $pos += 4;
           }
           else {
               // we have an escaped ascii character
               $hexVal = substr ($source, $pos, 2);
               $decodedStr .= chr (hexdec ($hexVal));
               $pos += 2;
           }
       } else {
           $decodedStr .= $charAt;
           $pos++;
       }
   }
   return $decodedStr;
}

function uft8html2utf8( $s ) {
       if ( !function_exists('uft8html2utf8_callback') ) {
             function uft8html2utf8_callback($t) {
                     $dec = $t[1];
           if ($dec < 128) {
             $utf = chr($dec);
           } else if ($dec < 2048) {
             $utf = chr(192 + (($dec - ($dec % 64)) / 64));
             $utf .= chr(128 + ($dec % 64));
           } else {
             $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
             $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
             $utf .= chr(128 + ($dec % 64));
           }
           // Not needed since encoding is UTF-8
           // return mb_convert_encoding($utf,"ISO-8859-7","UTF-8");
           return $utf;
           }
       }
       return preg_replace_callback('|&#([0-9]{1,});|', 'uft8html2utf8_callback', $s); 
}
?>

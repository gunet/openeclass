<?php  
//Μετατροπή του εργαλείου για να χρησιμοποιεί το baseTheme
$require_current_course = TRUE;
$langFiles = 'conference';
$require_help = TRUE;
$helpTopic = 'User';
include '../../include/baseTheme.php';


$nameTools = "conference";
$head_content='

<meta http-equiv="refresh" content="400; url=\''.$_SERVER['PHP_SELF'].'\'">
<script>
function prepare_message()
{
        document.chatForm.chatLine.value=document.chatForm.msg.value;
        document.chatForm.msg.value = "";
        document.chatForm.msg.focus();
        return true;
}
</script>
';
$body_action='';
$tool_content = "";//initialise $tool_content


//$nick=htmlentities(mb_convert_encoding($prenom." ".$nom,"UTF-8","ISO-8859-7") ,ENT_QUOTES,"UTF-8");
$nick=$prenom." ".$nom;

$coursePath=$webDir."courses";





/*==========================
          CHAT INIT
  ==========================*/

$fileChatName   = $coursePath.'/'.$currentCourseID.'/.chat.txt';
$tmpArchiveFile = $coursePath.'/'.$currentCourseID.'.tmpChatArchive.txt';
$pathToSaveChat = $coursePath.'/document/';

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

//$tool_content .= "<table border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" width=\" $mainInterfaceWidth\">";
//$tool_content .= "<tr><td>";

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
                $tool_content .= "<blockquote>".$langIsNowInYourDocDir.
                        "<br><a href=\"../document/document.php\" target=\"top\">".
                        "<strong>".$saveIn."</strong>".
                        "</a> ".$langIsChatDocVisible.
                        "</blockquote>";
        } else {
                $tool_content .= '<blockquote>'.$langCopyFailed.'</blockquote>';
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







echo utf8RawUrlDecode($tool_content);

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





?>

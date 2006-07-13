<?
//ÌåôáôñïðÞ ôïõ åñãáëåßïõ ãéá íá ÷ñçóéìïðïéåß ôï baseTheme
$require_current_course = TRUE;
$langFiles = 'conference';
$require_help = TRUE;
$helpTopic = 'User';
include '../../include/baseTheme.php';


$nameTools = "conference";

$coursePath=$webDir."courses";

$fileParameter   = $coursePath.'/'.$currentCourseID.'/.parameters.txt';


if (is_file($fileParameter))
{
     	$fp = fopen($fileParameter, 'r')
                or die ('<center>$langChatError</center>');
 	$URL = unserialize(fread($fp, filesize($fileParameter)));
	
	fclose($fp);
}
if ($is_adminOfCourse) {

		if(isset($_POST["video_div"]))
		{
		 	$URL["video"]=stripslashes($_POST["video_div"]);
		}
		if(isset($_POST["presantation_URL"]))
		{
			$URL["presantation"]=stripslashes($_POST["presantation_URL"]);
		}
		if(isset($_POST["netmeeting_number"]))
		{
			$URL["netmeeting_number"]=$_POST["netmeeting_number"];
		}
		if(isset($_POST["video_type"]))
		{
			$URL["video_type"]=$_POST["video_type"];
		}



     		$fp = fopen($fileParameter, 'w+');
		fwrite($fp,serialize($URL));
		fclose($fp);


}
else
{

               if($_POST["variable"]=="presantation")
                {
			if(isset($URL["presantation"]))
                       		echo $URL["presantation"];
                }
                if($_POST["variable"]=="video")
                {
			if(isset($URL["video"]))
                       		echo $URL["video"];
                }
                if($_POST["variable"]=="netmeeting_number")
                {
			if(isset($URL["netmeeting_number"]))
                       		echo $URL["netmeeting_number"];
                }
                if($_POST["variable"]=="video_type")
                {
			if(isset($URL["video_type"]))
                       		echo $URL["video_type"];
                }

}


?>

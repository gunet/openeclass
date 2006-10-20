<?
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*       Copyright(c) 2003-2006  Greek Universities Network - GUnet
*       Á full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:     Dimitris Tsachalis <ditsa@ccf.auth.gr>
*
*       For a full list of contributors, see "credits.txt".
*
*       This program is a free software under the terms of the GNU
*       (General Public License) as published by the Free Software
*       Foundation. See the GNU License for more details.
*       The full license can be read in "license.txt".
*
*       Contact address:        GUnet Asynchronous Teleteaching Group,
*                                               Network Operations Center, University of Athens,
*                                               Panepistimiopolis Ilissia, 15784, Athens, Greece
*                                               eMail: eclassadmin@gunet.gr
============================================================================*/
/**
 * refresh_chat
 * 
 * @author Dimitris Tsachalis <ditsa@ccf.auth.gr>
 * @version $Id$
 * 
 * @abstract 
 *
 */
header("Content-type: text/html; charset=ISO-8859-7"); 
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
/* Admin set parameters for conference module*/
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
		if(isset($_POST["action"]))
		{
			if($_POST["action"]=="clean")
				{
				$URL["video"]="";
				$URL["presantation"]="$langPresantation_content";
				$URL["netmeeting_number"]="";
				$URL["video_type"]="none";
				}
		}



     		$fp = fopen($fileParameter, 'w+');
		fwrite($fp,serialize($URL));
		fclose($fp);


}
/*student get parameters for conference module*/
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

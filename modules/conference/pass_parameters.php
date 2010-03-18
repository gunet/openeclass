<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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

		if(isset($_POST["video_URL"]))
		{
		 	$URL["video_URL"]=stripslashes($_POST["video_URL"]);
		}
		if(isset($_POST["presantation_URL"]))
		{
			$URL["presantation_URL"]=stripslashes($_POST["presantation_URL"]);
		}
		if(isset($_POST["netmeeting_show"]))
		{
			$URL["netmeeting_show"]=$_POST["netmeeting_show"];
		}

		if(isset($_POST["action"]))
		{
			if($_POST["action"]=="clean_video")
				{
					unset($URL["video_URL"]);
				}
			if($_POST["action"]=="clean_presantation")
				{
					unset($URL["presantation_URL"]);
				}
		}
     		$fp = fopen($fileParameter, 'w+');
		fwrite($fp,serialize($URL));
		fclose($fp);
}

/*student get parameters for conference module*/
               if($_POST["variable"]=="netmeeting_show")
                {
			if(isset($URL["netmeeting_show"]))
                       		echo $URL["netmeeting_show"];
                }
                if($_POST["variable"]=="video_URL")
                {
			if(isset($URL["video_URL"]))
                       		echo $URL["video_URL"];
                }
                if($_POST["variable"]=="presantation_URL")
                {
			if(isset($URL["presantation_URL"]))
                       		echo $URL["presantation_URL"];
                }
?>

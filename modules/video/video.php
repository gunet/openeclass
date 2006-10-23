<?PHP 
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*       Copyright(c) 2003-2006  Greek Universities Network - GUnet
*       Α full copyright notice can be read in "/info/copyright.txt".
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
 * video
 * 
 * @author Dimitris Tsachalis <ditsa@ccf.auth.gr>
 * @version $Id$
 * 
 * @abstract 
 *
 */


 /*******************************************************************
  *			   VIDEO UPLOADER AND DOWNLOADER
  ********************************************************************

GOALS
*****
Allow professor to send quickly video immediately
visible on his site.

The script makes 5 things:

	 1. Upload video

	 2. Give them a name

	 3. Modify data about video

	 4. Delete link to video and simultaneously remove them

	 5. Show video list to students and visitors

On the long run, the idea is to allow sending realvideo . Which means only
establish a correspondence between RealServer Content Path and the user's
documents path.

All documents are sent to the address /$webDir/$currentCourseID/document/
where $currentCourseID is the web directory for the course and $webDir usually /var/www/html
*/

//Μετατροπή του εργαλείου για να χρησιμοποιεί το baseTheme
$require_current_course = TRUE;
$langFiles = 'video';
$require_help = TRUE;
$helpTopic = 'Video';
include '../../include/baseTheme.php';


$nameTools = $langVideo;
$nick=$prenom." ".$nom;
$tool_content="";
$tool_content .= "<div id=\"tool_operations\">";


$d = mysql_fetch_array(db_query("SELECT video_quota FROM cours WHERE code='$currentCourseID'",$mysqlMainDb));
$diskQuotaVideo = $d['video_quota'];

$tool_content.="<tr><td>";
if($is_adminOfCourse) {
	if (isset($submit)) {
		if (isset($tout)) {
			$sql="DELETE FROM video";
		}	
		elseif($id) {
			$sql = "UPDATE $table SET titre='$titre', description='$description',creator='$creator',publisher='$publisher', date='$date' WHERE id=$id";
		}	 
		else {
			if(isset($URL))
			{
			if ($titre == "") $titre = $URL;
			$url=$URL;
			$sql = "INSERT INTO videolinks (url,titre,description,creator,publisher,date) VALUES ('$url','$titre','$description','$creator','$publisher','$date')";
			}else{
				$updir = "$webDir/video/$currentCourseID/"; //path to upload directory
				if (($file_name != "") && ($file_size <= $diskQuotaVideo )) {
			
					// convert php file in phps to protect the platform against malicious codes
					$file_name = ereg_replace(".php$", ".phps", $file_name);

					$file_name = str_replace(" ", "%20", $file_name);
					$file_name = str_replace("%20", "", $file_name); 
					$file_name = str_replace(" ", "", $file_name);
					$file_name = str_replace("\'", "", $file_name);

					if ($titre == "") $titre = $file_name;
					$iscopy=@copy("$file", "$updir/$file_name");
					if(!$iscopy)
					 {$tool_content="<table><tbody><tr><td colspan=2 class=\"caution\">$langFileNot</td></tr></tbody></table><a href=\"$_SERVER[PHP_SELF]\">$langBack</a>";
					draw($tool_content, 2, 'user', $head_content);}
				} else {
					$tool_content="<table><tbody><tr><td colspan=2 class=\"caution\">$langTooBig</td></tr></tbody></table><a href=\"$_SERVER[PHP_SELF]\">$langBack</a>";
draw($tool_content, 2, 'user', $head_content);
exit;
				}


			$url="$file_name";
			$sql = "INSERT INTO video (url,titre,description,creator,publisher,date) VALUES ('$url','$titre','$description','$creator','$publisher','$date')";
			}
		}	// else
		$result = db_query($sql,$currentCourseID);
		if (isset($tout)) {
			$tool_content.="
				<table>
				<tbody>
				<tr>
				<td class=\"success\">
				$langVideoDeleted
				</td>
				</tr>
				</tbody>
				</table>
			<br>";
				$id="";
		} elseif($id) {
			$tool_content.="
				<table>
				<tbody>
				<tr>
				<td class=\"success\">
				$langTitleMod
				</td>
				</tr>
				</tbody>
				</table>
				<br>
				<br>";
		$id="";
		} else {
			$tool_content.="
				<table>
				<tbody>
				<tr>
				<td class=\"success\">
				$langFAdd
				</td>
				</tr>
				</tbody>
				</table>
				<br>
			<br>";
		}	 // else
	}	// if submit

	elseif (isset($delete)) {
		$sql_select="SELECT url FROM $table WHERE id=$id";
		$result = db_query($sql_select,$currentCourseID);
		$myrow = mysql_fetch_array($result);
		$nom_document=$myrow[0];
		if($myrow[1]==0 && $table=="video")
			unlink("$webDir/video/$currentCourseID/".$myrow[0]);
		$sql = "DELETE FROM $table WHERE id=$id";
		$result = db_query($sql,$currentCourseID);
		$tool_content.="
			<table>
			<tbody>
			<tr>
			<td class=\"success\">
			$langDelF
			</td>
			</tr>
			</tbody>
			</table>
			";
			$id="";
	}	
########################### IF NO SUBMIT #############################
			$tool_content.="
			<br>
			<br>

			<a href=\"javascript:change_form_input('file');\">$langAddV</a> | <a href=\"javascript:change_form_input('URL');\">$langAddVideoLink</a>";
$head_content="
<script>
function change_form_input(type)
{
if(type==\"file\")
        
        { if(document.getElementById(\"edit_form\")!=null)
	        {document.getElementById(\"edit_form\").innerHTML='';
	        }

		
                document.getElementById(\"insert_form\").innerHTML='<table width=\"800\"><thead><tr>'+
                        '<th>$langsendV'+
                        '<input type=\"hidden\" name=\"id\" value=\"\">'+
                        '</th>'+
                                '<td><input type=\"file\" name=\"file\" size=\"45\"></td>'+
                        '<tr>'+
                                        '<th>$langVideoTitle:</th>'+

                        '<td><input type=\"text\" name=\"titre\" value=\"\" size=\"55\"></td>'+
                        '</tr>'+
                        '<tr>'+
                                        '<th>$langDescr&nbsp;:</th>'+
                        '<td>'+
                        '<textarea wrap=\"physical\" rows=\"3\" name=\"description\" cols=\"50\"></textarea>'+
                        '</td>'+
                        '</tr>'+
                        '<tr>'+
                                        '<th>$langcreator&nbsp;:</th>'+
                        '<td><input type=\"text\" name=\"creator\" value=\"$nick\" size=\"55\"></td>'+
                        '</tr>'+
                        '<tr>'+
                                        '<th>$langpublisher &nbsp;:</th>'+
                        '<td><input type=\"text\" name=\"publisher\" value=\"$nick\" size=\"55\"></td>'+
                        '</tr>'+
                        '<tr>'+
                                        '<th>$langdate &nbsp;:</th>'+
                        '<td><input type=\"text\" name=\"date\" value=\"".date("Y-m-d G:i:s")."\" size=\"55\"></td>'+
                        '</tr>'+
                        '<tr><td><input type=\"Submit\" name=\"submit\" value=\"$langAdd\"></td></tr></thead>'+
                        '</table>';

        }
if(type==\"URL\")
        {	if(document.getElementById(\"edit_form\")!=null)
			{document.getElementById(\"edit_form\").innerHTML='';
			}
                document.getElementById(\"insert_form\").innerHTML='<table width=\"800\"><thead><tr>'+
                        '<th>$langURL'+
                        '<input type=\"hidden\" name=\"id\" value=\"\">'+
                        '</th>'+
                                '<td><input type=\"URL\" name=\"URL\" size=\"45\"></td>'+
                        '<tr>'+
                                        '<th>$langVideoTitle:</th>'+

                        '<td><input type=\"text\" name=\"titre\" value=\"\" size=\"55\"></td>'+
                        '</tr>'+
                        '<tr>'+
                                        '<th>$langDescr&nbsp;:</th>'+
                        '<td>'+
                        '<textarea wrap=\"physical\" rows=\"3\" name=\"description\" cols=\"50\"></textarea>'+
                        '</td>'+
                        '</tr>'+
                        '<tr>'+
                                        '<th>$langcreator&nbsp;:</th>'+
                        '<td><input type=\"text\" name=\"creator\" value=\"$nick\" size=\"55\"></td>'+
                        '</tr>'+
                        '<tr>'+
                                        '<th>$langpublisher &nbsp;:</th>'+
                        '<td><input type=\"text\" name=\"publisher\" value=\"$nick\" size=\"55\"></td>'+
                        '</tr>'+
                        '<tr>'+
                                        '<th>$langdate &nbsp;:</th>'+
                        '<td><input type=\"text\" name=\"date\" value=\"".date("Y-m-d G:i:s")."\" size=\"55\"></td>'+
                        '</tr>'+
                        '<tr><td><input type=\"Submit\" name=\"submit\" value=\"$langAdd\"></td></tr></thead>'+
                        '</table>';
        }

}


</script>

";
		$tool_content.="<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\" id=\"insert_form\"></form>";
			// print the list if there is no editing
			$results['video'] = db_query("SELECT *  FROM video ORDER BY titre",$currentCourseID);
			$results['videolinks'] = db_query("SELECT * FROM videolinks ORDER BY titre",$currentCourseID);
			$i=0;

			$tool_content.="<table>";
			foreach($results as $table => $result)
			while ($myrow = mysql_fetch_array($result)) {
				if($myrow[7])
				{$videoURL=$myrow[1];
				}else{
					
					switch($table){
						case "video":
							if(isset($vodServer))
								{
								$videoURL=$vodServer."$currentCourseID/".$myrow[1];
								}
							else
								{
								$videoURL="../../video/$currentCourseID/".$myrow[1];
								}
						break;
						case "videolinks":
							$videoURL=$myrow[1];
						break;
						default:
							exit;
					}
				}
				


				if($i%2)
				{
				$tool_content.=sprintf("
				<tr>
					<td bgcolor=\"#F5F5F5\" width=\"30\" valign=\"top\">
						<a href=\"%s\" target=\"_blank\"><img src=\"images/video.gif\" border=\"0\"  alt=\"video\"></a>
					</td>
					<td width=\"570\" valign=\"top\" bgcolor=\"#F5F5F5\">
						<font size=\"2\" face=\"arial, helvetica\">
							$langVideoTitle: <a href=\"%s\" target=\"_blank\">%s</a>
							<br>
							$langDescr: %s <br>
							$langcreator: %s <br>
							$langpublisher: %s <br>
							$langdate: %s

 						</font>", $videoURL, $videoURL, $myrow[2], $myrow[3],$myrow[4], $myrow[5],$myrow[6]);
					$tool_content.=sprintf("<br>
							 <a href=\"%s?id=%s&table_edit=%s&action=edit\"><img src=\"../../images/edit.gif\" border=\"0\" alt=\"$langModify\"></a>", $_SERVER['PHP_SELF'], $myrow[0],$table);
					$tool_content.=sprintf("<a href=\"%s?id=%s&delete=yes&table=%s\"><img src=\"../../images/delete.gif\" border=\"0\" alt=\"$langDelete\"></a>
					</td>
				</tr>
			", $_SERVER['PHP_SELF'], $myrow[0],$table);
				} else {
				$tool_content.=sprintf("
				<tr>
					<td bgcolor=\"#E6E6E6\" width=\"30\" valign=\"top\">
						<a href=\"%s\" target=\"_blank\"><img  alt=\"video\" src=\"images/video.gif\" border=\"0\"></a>
					</td>
					<td width=\"570\" valign=\"top\" bgcolor=\"#E6E6E6\">
						<font size=\"2\" face=\"arial, helvetica\">
							$langVideoTitle: <a href=\"%s\" target=\"_blank\">%s</a>
							<br>
							$langDescr: %s <br>
							$langcreator: %s <br>
							$langpublisher: %s <br>
							$langdate: %s
							", $videoURL, $videoURL, $myrow[2], $myrow[3],$myrow[4], $myrow[5],$myrow[6]);
					$tool_content.=sprintf("<br>
							<a href=\"%s?id=%s&table_edit=%s&action=edit\"><img src=\"../../images/edit.gif\" border=\"0\" alt=\"$langModify\"></a>", $_SERVER['PHP_SELF'], $myrow[0],$table);
					$tool_content.=sprintf("<a href=\"%s?id=%s&delete=yes&table=%s\"><img src=\"../../images/delete.gif\" border=\"0\" alt=\"$langDelete\"></a>
						</font>
					</td>
				</tr>
			", $_SERVER['PHP_SELF'], $myrow[0],$table);
				}// while
				$i++;
			}	
			$tool_content.="</table>";


		######################### FORM #######################################


		if (isset($id)) {
			if($id!="")
			{
			$sql = "SELECT * FROM $table_edit WHERE id=$id ORDER BY titre";
			$result = db_query($sql,$currentCourseID);
			$myrow = mysql_fetch_array($result);
			$id = $myrow[0];
			$titre = $myrow[2];
			$description = $myrow[3];
			$creator = $myrow[4];
			$publisher = $myrow[5];
			$date = $myrow[6];
			$rdf="<thead><tr><th>
					$langVideoTitle:
			</th>
			<td><input type=\"text\" name=\"titre\" value=\"".@$titre."\" size=\"55\"></td>
			</tr>
			<tr>
			<th>
					$langDescr&nbsp;:
			</th>
			<td>
			<textarea wrap=\"physical\" rows=\"3\" name=\"description\" cols=\"50\">".@$description."</textarea>
			</td>
			</tr>

			<tr>
			<th>
					$langcreator&nbsp;:
			</th>
			<td><input type=\"text\" name=\"creator\" value=\"".@$creator."\" size=\"55\"></td>
			</tr>
			<tr><th>
					$langpublisher &nbsp;:
			</th>
			<td><input type=\"text\" name=\"publisher\" value=\"".@$publisher."\" size=\"55\"></td>
			</tr>
			<tr><th>
					$langdate &nbsp;:
			</th>
			<td><input type=\"text\" name=\"date\" value=\"".@$date."\" size=\"55\"></td>
			</tr>

			<tr><td colspan=\"2\"><input type=\"Submit\" name=\"edit_sumbit\" value=\"$langAdd\"></td></tr>
			";

		$tool_content.="<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\" id=\"edit_form\">
			<input type=\"hidden\" name=\"id\" value=\"".@$id."\">
			<table width=\"800\">
			".$rdf."
			</table>
			<input type=\"hidden\" name=\"table\" value=\"$table_edit\">
		</form>";
			}
		}	// if id




}   // if uid=prof_id
############################# STUDENT VIEW #############################
else {
	$results['video'] = db_query("SELECT *  FROM video ORDER BY titre",$currentCourseID);
	$results['videolinks'] = db_query("SELECT * FROM videolinks ORDER BY titre",$currentCourseID);

	$i=0;
	foreach($results as $table => $result)
	{
	while ($myrow = mysql_fetch_array($result)) {
				if($myrow[7])
				{$videoURL=$myrow[1];
				}else{
					switch($table){
						case "video":
							if(isset($vodServer))
								{
								$videoURL=$vodServer."$currentCourseID/".$myrow[1];
								}
							else
								{
								$videoURL="../../video/$currentCourseID/".$myrow[1];
								}
						break;
						case "videolinks":
							$videoURL=$myrow[1];
						break;
						default:
							exit;
					}
				}
		if($i%2==0) {
			$tool_content.=sprintf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td bgcolor=\"#E6E6E6\" width=\"30\" valign=\"top\">
			<a href=\"%s\" target=\"_blank\"><img alt=\"video\" src=\"images/video.gif\" border=\"0\"></a>
			</td>
			<td width=\"570\" valign=\"top\" bgcolor=\"#E6E6E6\"><font size=\"2\" face=\"arial, helvetica\">
			<a href=\"%s\" target=\"_blank\">%s</a>
			<br>%s
			</td>
			</tr>
			</table>", $videoURL, $videoURL, $myrow[2], $myrow[3]);
		} elseif($i%2==1) {
			$tool_content.=sprintf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td bgcolor=\"#F5F5F5\" width=\"30\" valign=\"top\">
			<a href=\"%s\" target=\"_blank\"><img alt=\"video\" src=\"images/video.gif\" border=\"0\"></a>
			</td>
			<td width=\"570\" valign=\"top\" bgcolor=\"#F5F5F5\"><font size=\"2\" face=\"arial, helvetica\">
			<a href=\"%s\" target=\"_blank\">%s</a>
			<br>%s
			</td></tr>
			</table>", $videoURL, $videoURL, $myrow[2], $myrow[3]);
		}	   
		$i++;
	}	 
	}
}	

$tool_content .= "</div>";
draw($tool_content, 2, 'user', $head_content);

?>

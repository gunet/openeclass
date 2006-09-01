<?PHP 
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.3.0     $Revision$                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  |   $Id$              |
  +----------------------------------------------------------------------+
  |   This program is free software; you can redistribute it and/or      |
  |   modify it under the terms of the GNU General Public License        |
  |   as published by the Free Software Foundation; either version 2     |
  |   of the License, or (at your option) any later version.             |
  |                                                                      |
  |   This program is distributed in the hope that it will be useful,    |
  |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
  |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
  |   GNU General Public License for more details.                       |
  |                                                                      |
  |   You should have received a copy of the GNU General Public License  |
  |   along with this program; if not, write to the Free Software        |
  |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
  |   02111-1307, USA. The GNU GPL license is also available through     |
  |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
  +----------------------------------------------------------------------+
  | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
  |          Hugues Peeters	<peeters@ipm.ucl.ac.be>                      |
  |          Christophe Geschι <gesche@ipm.ucl.ac.be>                    |
  +----------------------------------------------------------------------+
 */
 /*******************************************************************
  *			   VIDEO UPLOADER AND DOWNLOADER
  ********************************************************************

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
$helpTopic = 'User';
include '../../include/baseTheme.php';


$nameTools = $langVideo;
$nick=$prenom." ".$nom;
$tool_content="";


$d = mysql_fetch_array(db_query("SELECT video_quota FROM cours WHERE code='$currentCourseID'",$mysqlMainDb));
$diskQuotaVideo = $d['video_quota'];

$tool_content.="<tr><td>";
if($is_adminOfCourse) {
	if (isset($submit)) {
		if (isset($tout)) {
			$sql="DELETE FROM video";
		}	
		elseif($id) {
			$sql = "UPDATE video SET title='$title', description='$description',creator='$creator',publisher='$publisher', date='$date' WHERE id=$id";
		}	 
		else {
			if(isset($URL))
			{
			if ($title == "") $title = $URL;
			$url=$URL;
			$sql = "INSERT INTO video (url,title,description,creator,publisher,date,external_URL) VALUES ('$url','$title','$description','$creator','$publisher','$date','1')";
			}else{
				$updir = "$webDir/video/$currentCourseID/"; //path to upload directory
				if (($file_name != "") && ($file_size <= $diskQuotaVideo )) {
			
					// convert php file in phps to protect the platform against malicious codes
					$file_name = ereg_replace(".php$", ".phps", $file_name);

					$file_name = str_replace(" ", "%20", $file_name);
					$file_name = str_replace("%20", "", $file_name); 
					$file_name = str_replace(" ", "", $file_name);
					$file_name = str_replace("\'", "", $file_name);

					if ($title == "") $title = $file_name;
					$iscopy=@copy("$file", "$updir/$file_name");
					if(!$iscopy)
					 {$tool_content="<table><tbody><tr><td colspan=2 class=\"caution\">$langFileNot</td></tr></tbody></table><a href=\"$_SERVER[PHP_SELF]\">$langBack</a>";
					draw($tool_content, 2, 'user', $head_content);}
				} else {
					$tool_content="<table><tbody><tr><td colspan=2 class=\"caution\">$langTooBig</td></tr></tbody></table><a href=\"$_SERVER[PHP_SELF]\">$langBack</a>";
if (isset($head_content)) 					
	draw($tool_content, 2, 'user', $head_content);
else 	
	draw($tool_content, 2, 'user');
exit;
				}


			$url="$file_name";
			$sql = "INSERT INTO video (url,title,description,creator,publisher,date,external_URL) VALUES ('$url','$title','$description','$creator','$publisher','$date','0')";
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
				<br><br>";
			$tool_content.="<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
			<br>";
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
				<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
				<br>";
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
				<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
			<br>";
		}	 // else
	}	// if submit

	elseif (isset($delete)) {
		$sql_select="SELECT url,external_URL FROM video WHERE id=$id";
		$result = db_query($sql_select,$currentCourseID);
		$myrow = mysql_fetch_array($result);
		$nom_document=$myrow[0];
		if($myrow[1]==0)
			unlink("$webDir/video/$currentCourseID/".$myrow[0]);
		$sql = "DELETE FROM video WHERE id=$id";
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
			<p><a href=\"$_SERVER[PHP_SELF]\">$langBack</a>";
	}	
	else {
// if no submit
		if (!isset($id)) {
			$tool_content.="
			<br>
			<br>
			<label>$langAddV <input type=\"radio\"  onclick=\"javascript:change_form_input('file');\" name=\"choose\" checked=\"checked\" value=\"\" /></label> <label>$langAddVideoLink <input type=\"radio\"  onclick=\"javascript:change_form_input('URL');\" name=\"choose\" value=\"\" /></label>";

$head_content="
<script>

function change_form_input(type)
{
if(type==\"file\")
	{
		document.getElementById(\"title_file_url\").innerHTML='$langsendV';
		document.getElementById(\"file_url_input_type\").innerHTML='<input type=\"file\" name=\"file\" size=\"45\">';
	}
if(type==\"URL\")
	{
		document.getElementById(\"title_file_url\").innerHTML='$langURL';
		document.getElementById(\"file_url_input_type\").innerHTML='<input type=\"text\" name=\"URL\" size=\"45\">';
	}
		
}


</script>
";
		$tool_content.="<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\">
			<input type=\"hidden\" name=\"id\" value=\"".@$id."\">
			<table width=\"800\"><tr><td><font size=\"2\" face=\"arial, helvetica\">
				<div id=\"title_file_url\">$langsendV&nbsp;</div>
				</font></td>
				<td><div id=\"file_url_input_type\"><input type=\"file\" name=\"file\" size=\"45\"></div></td>
			</tr>
			<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langVideoTitle&nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"title\" value=\"\" size=\"55\"></td>
			</tr>
			<tr>
			<td valign=\"top\">
				<font size=\"2\" face=\"arial, helvetica\">
					$langDescr&nbsp;:
				</font>
			</td>
			<td>
			<textarea wrap=\"physical\" rows=\"3\" name=\"description\" cols=\"50\"></textarea>
			</td>
			</tr>

			<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langcreator&nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"creator\" value=\"$nick\" size=\"55\"></td>
			</tr>
			<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langpublisher &nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"publisher\" value=\"$nick\" size=\"55\"></td>
			</tr>
			<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langdate &nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"date\" value=\"".date("Y-m-d G:i:s")."\" size=\"55\"></td>
			</tr>

			<tr>
			<td colspan=\"2\">
				<font size=\"1\" face='arial, helvetica'>$langDelList&nbsp;:</font size>
				<input type=\"checkbox\" name=\"tout\" value=\"tout\">
			</td>
			</tr>
			<tr><td colspan=\"2\"><input type=\"Submit\" name=\"submit\" value=\"$langAdd\"></td></tr>

			</table>
		</form>";
			// print the list if there is no editing
			$result = db_query("SELECT * FROM video ORDER BY title",$currentCourseID);
			$i=0;
			while ($myrow = mysql_fetch_array($result)) {
				if($myrow[7])
				{$videoURL=$myrow[1];
				}else{
					if(isset($vodServer))
						{
						$videoURL=$vodServer."$currentCourseID/".$myrow[1];
						}
					else
						{
						$videoURL="../../video/$currentCourseID/".$myrow[1];
						}
				}
				


				if($i%2)
				{
				$tool_content.=sprintf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
				<tr>
					<td bgcolor=\"#F5F5F5\" width=\"30\" valign=\"top\">
						<a href=\"%s\" ><img src=\"images/video.gif\" border=\"0\"  alt=\"video\"></a>
					</td>
					<td width=\"570\" valign=\"top\" bgcolor=\"#F5F5F5\">
						<font size=\"2\" face=\"arial, helvetica\">
							$langVideoTitle: <a href=\"%s\">%s</a>
							<br>
							$langDescr: %s <br>
							$langcreator: %s <br>
							$langpublisher: %s <br>
							$langdate: %s

 						</font>", $videoURL, $videoURL, $myrow[2], $myrow[3],$myrow[4], $myrow[5],$myrow[6]);
					$tool_content.=sprintf("<br>
						<font size=\"1\" face=\"arial, helvetica\">
							<a href=\"%s?id=%s\">$langModify</a>
							 | 
							 ", $_SERVER['PHP_SELF'], $myrow[0]);
					$tool_content.=sprintf("<a href=\"%s?id=%s&delete=yes\">$langDelete</a>
						</font>
					</td>
				</tr>
			</table>", $_SERVER['PHP_SELF'], $myrow[0]);
				} else {
				$tool_content.=sprintf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
				<tr>
					<td bgcolor=\"#E6E6E6\" width=\"30\" valign=\"top\">
						<a href=\"%s\"><img  alt=\"video\" src=\"images/video.gif\" border=\"0\"></a>
					</td>
					<td width=\"570\" valign=\"top\" bgcolor=\"#E6E6E6\">
						<font size=\"2\" face=\"arial, helvetica\">
							<a href=\"%s\">%s</a>
							<br>
							$langDescr: %s <br>
							$langcreator: %s <br>
							$langpublisher: %s <br>
							$langdate: %s
							", $videoURL, $videoURL, $myrow[2], $myrow[3],$myrow[4], $myrow[5],$myrow[6]);
					$tool_content.=sprintf("<br>
							<font size=\"1\" face=\"arial, helvetica\">
								|
								&nbsp;
								<a href=\"%s?id=%s\">$langModify</a>
								| ", $_SERVER['PHP_SELF'], $myrow[0]);
					$tool_content.=sprintf("<a href=\"%s?id=%s&delete=yes\">$langDelete</a>
							</font>
						</font>
					</td>
				</tr>
			</table>", $_SERVER['PHP_SELF'], $myrow[0]);
				}
				$i++;
			}	// while

			// --------------- form --------------------------------

		}	// if ! id
		if (isset($id)) {
			$sql = "SELECT * FROM video WHERE id=$id ORDER BY title";
			$result = db_query($sql,$currentCourseID);
			$myrow = mysql_fetch_array($result);
			$id = $myrow[0];
			$title = $myrow[2];
			$description = $myrow[3];
			$creator = $myrow[4];
			$publisher = $myrow[5];
			$date = $myrow[6];
			$rdf="<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langVideoTitle&nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"title\" value=\"".@$title."\" size=\"55\"></td>
			</tr>
			<tr>
			<td valign=\"top\">
				<font size=\"2\" face=\"arial, helvetica\">
					$langDescr&nbsp;:
				</font>
			</td>
			<td>
			<textarea wrap=\"physical\" rows=\"3\" name=\"description\" cols=\"50\">".@$description."</textarea>
			</td>
			</tr>

			<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langcreator&nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"creator\" value=\"".@$creator."\" size=\"55\"></td>
			</tr>
			<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langpublisher &nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"publisher\" value=\"".@$publisher."\" size=\"55\"></td>
			</tr>
			<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langdate &nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"date\" value=\"".@$date."\" size=\"55\"></td>
			</tr>

			<tr>
			<td colspan=\"2\">
				<font size=\"1\" face='arial, helvetica'>$langDelList&nbsp;:</font size>
				<input type=\"checkbox\" name=\"tout\" value=\"tout\">
			</td>
			</tr>
			<tr><td colspan=\"2\"><input type=\"Submit\" name=\"submit\" value=\"$langAdd\"></td></tr>
			";

		$tool_content.="<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\">
			<input type=\"hidden\" name=\"id\" value=\"".@$id."\">
			<table width=\"800\">
			".$rdf."
			</table>
		</form>";
		}	// if id


	}	
}   // if uid=prof_id
// student view
else {
	$result = db_query("SELECT * FROM video ORDER BY title",$currentCourseID);
	$i=0;
	while ($myrow = mysql_fetch_array($result)) {
				if($myrow[7])
				{$videoURL=$myrow[1]; echo "aaaa";
				}else{
					if(isset($vodServer))
						{
						$videoURL=$vodServer."$currentCourseID/".$myrow[1];
						}
					else
						{
						$videoURL="../../video/$currentCourseID/".$myrow[1];
						}
				}
		if($i%2==0) {
			$tool_content.=sprintf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td bgcolor=\"#E6E6E6\" width=\"30\" valign=\"top\">
			<a href=\"%s\"><img alt=\"video\" src=\"images/video.gif\" border=\"0\"></a>
			</td>
			<td width=\"570\" valign=\"top\" bgcolor=\"#E6E6E6\"><font size=\"2\" face=\"arial, helvetica\">
			<a href=\"%s\">%s</a>
			<br>%s
			</td>
			</tr>
			</table>", $videoURL, $videoURL, $myrow[2], $myrow[3]);
		} elseif($i%2==1) {
			$tool_content.=sprintf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td bgcolor=\"#F5F5F5\" width=\"30\" valign=\"top\">
			<a href=\"%s\"><img alt=\"video\" src=\"images/video.gif\" border=\"0\"></a>
			</td>
			<td width=\"570\" valign=\"top\" bgcolor=\"#F5F5F5\"><font size=\"2\" face=\"arial, helvetica\">
			<a href=\"%s\">%s</a>
			<br>%s
			</td></tr>
			</table>", $videoURL, $videoURL, $myrow[2], $myrow[3]);
		}	   
		$i++;
	}	 
}	

if (isset($head_content))
	draw($tool_content, 2, 'user', $head_content);
else
	draw($tool_content, 2, 'user');
?>

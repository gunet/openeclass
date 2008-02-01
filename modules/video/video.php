<?PHP 
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*       Copyright(c) 2003-2006  Greek Universities Network - GUnet
*       A full copyright notice can be read in "/info/copyright.txt".
*
* 			Authors:     Dimitris Tsachalis <ditsa@ccf.auth.gr>
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
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract 
 *
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
$require_help = TRUE;
$helpTopic = 'Video';
$guest_allowed = true;

include '../../include/baseTheme.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_VIDEO');
/**************************************/

$nameTools = $langVideo;
$tool_content="";
$d = mysql_fetch_array(db_query("SELECT video_quota FROM cours WHERE code='$currentCourseID'",$mysqlMainDb));
$diskQuotaVideo = $d['video_quota'];

if($is_adminOfCourse) {
	$head_content = '
<script>
function confirmation (name)
{
    if (confirm("'.$langAreYouSureToDelete.' "+ name + " ?"))
        {return true;}
    else
        {return false;}
}
</script>
';

	$nick=$prenom." ".$nom;
	
	$tool_content .= "<div id=\"operations_container\">
		<ul id=\"opslist\">
		<li><a href=\"$_SERVER[PHP_SELF]?form_input=file\">$langAddV</a></li>
		<li><a href=\"$_SERVER[PHP_SELF]?form_input=url\">$langAddVideoLink</a></li>
		</ul></div>";

	if (isset($_POST['submit']) or isset($_POST['edit_submit'])) {
		if($id) {
			$sql = "UPDATE $table SET url='".mysql_real_escape_string($url)."', titre='".mysql_real_escape_string($titre)."', description='".mysql_real_escape_string($description)."',creator='".mysql_real_escape_string($creator)."',publisher='".mysql_real_escape_string($publisher)."', date='".mysql_real_escape_string($date)."' WHERE id='".mysql_real_escape_string($id)."'";
		}
		else {
			if(isset($URL))
			{
				if ($titre == "") $titre = $URL;
				$url=$URL;
				$sql = "INSERT INTO videolinks (url,titre,description,creator,publisher,date) VALUES ('$url','".mysql_real_escape_string($titre)."','".mysql_real_escape_string($description)."','".mysql_real_escape_string($creator)."','".mysql_real_escape_string($publisher)."','".mysql_real_escape_string($date)."')";
			} else {
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
					if(!$iscopy) {
					$tool_content="<table width=\"99%\"><tbody><tr>
												<td class=\"caution\"><p><b>$langFileNot</b></p>
												<p><a href=\"$_SERVER[PHP_SELF]\">$langBack</a></p></td></tr></tbody></table>";
						draw($tool_content, 2, 'user', $head_content);
						exit;
				}
		} else {
					$tool_content="<table width=\"99%\"><tbody><tr><td class=\"caution\"><p><b>$langTooBig</b></p><p><a href=\"$_SERVER[PHP_SELF]\">$langBack</a></p></td></tr></tbody></table>";
					draw($tool_content, 2, 'user', $head_content);
					exit;
				}

				$url="$file_name";
				$sql = "INSERT INTO video (url,titre,description,creator,publisher,date) VALUES ('$url','".mysql_real_escape_string($titre)."','".mysql_real_escape_string($description)."','".mysql_real_escape_string($creator)."','".mysql_real_escape_string($publisher)."','".mysql_real_escape_string($date)."')";
			}
		}	// else
		$result = db_query($sql,$currentCourseID);
			if($id) {
				$tool_content.="<table width=\"99%\">
				<tbody><tr><td class=\"success\"><p><b>$langTitleMod</b></p></td></tr></tbody>
				</table><br>";
			$id="";
		} else {
			$tool_content.="
				<table width=\"99%\">
				<tbody><tr><td class=\"success\"><p><b>$langFAdd</b></p></td></tr></tbody>
				</table><br>";
		}	 // else
	}	// if submit

	elseif (isset($delete)) {
		$sql_select="SELECT url FROM $table WHERE id='".mysql_real_escape_string($id)."'";
		$result = db_query($sql_select,$currentCourseID);
		$myrow = mysql_fetch_array($result);
		$nom_document=$myrow[0];
		if($table=="video")
		unlink("$webDir/video/$currentCourseID/".$myrow[0]);
		$sql = "DELETE FROM $table WHERE id='".mysql_real_escape_string($id)."'";
		$result = db_query($sql,$currentCourseID);
		$tool_content .= "<table width=\"99%\">
			<tbody><tr><td class=\"success\"><p><b>$langDelF</b></p></td></tr></tbody>
			</table><br/>";
		$id="";
	} elseif (isset($form_input) && $form_input == "file") {
		$tool_content .= "<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\" id=\"insert_form\">
		<table><thead>
		<th>$langsendV
		<input type=\"hidden\" name=\"id\" value=\"\">
		</th>
		<td><input type=\"file\" name=\"file\" size=\"45\"></td>
		<tr>
		<th>$langVideoTitle:</th>
		<td><input type=\"text\" name=\"titre\" value=\"\" size=\"55\"></td>
		</tr>
		<tr>
		<th>$langDescr&nbsp;:</th>
		<td>
		<textarea wrap=\"physical\" rows=\"3\" name=\"description\" cols=\"50\"></textarea>
		</td>
		</tr>
		<tr>
		<th>$langcreator&nbsp;:</th>
		<td><input type=\"text\" name=\"creator\" value=\"$nick\" size=\"55\"></td>
		</tr>
		<tr>
		<th>$langpublisher &nbsp;:</th>
		<td><input type=\"text\" name=\"publisher\" value=\"$nick\" size=\"55\"></td>
		</tr>
		<tr>
		<th>$langdate &nbsp;:</th>
		<td><input type=\"text\" name=\"date\" value=\"".date("Y-m-d G:i:s")."\" size=\"55\"></td>
		</tr></thead>
		</table><br/>
		<input type=\"submit\" name=\"submit\" value=\"$langAdd\"></form><br/>";
	} elseif (isset($form_input) && $form_input == "url") {
		$tool_content .= "<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\" id=\"insert_form\">
		<table><thead><tr><th>$langURL
		<input type=\"hidden\" name=\"id\" value=\"\">
		</th>
		<td><input type=\"text\" name=\"URL\" size=\"45\"></td>
		<tr>
		<th>$langVideoTitle:</th>
		<td><input type=\"text\" name=\"titre\" value=\"\" size=\"55\"></td>
		</tr>
		<tr>
		<th>$langDescr :</th>
		<td>
		<textarea wrap=\"physical\" rows=\"3\" name=\"description\" cols=\"50\"></textarea>
		</td>
		</tr>
		<tr>
		<th>$langcreator :</th>
		<td><input type=\"text\" name=\"creator\" value=\"$nick\" size=\"55\"></td>
		</tr>
		<tr>
		<th>$langpublisher :</th>
		<td><input type=\"text\" name=\"publisher\" value=\"$nick\" size=\"55\"></td>
		</tr>
		<tr>
		<th>$langdate :</th>
		<td><input type=\"text\" name=\"date\" value=\"".date("Y-m-d G:i")."\" size=\"55\"></td>
		</tr></thead>
		</table><br/>
		<input type=\"submit\" name=\"submit\" value=\"$langAdd\"><br/><br/>
		</form>";
	}

// ------------------- if no submit -----------------------
if (isset($id)) {
		if($id!="") {
		$sql = "SELECT * FROM $table_edit WHERE id='".mysql_real_escape_string($id)."' ORDER BY titre";
      $result = db_query($sql,$currentCourseID);
      $myrow = mysql_fetch_array($result);
      $id = $myrow[0];
			$url= $myrow[1];
      $titre = $myrow[2];
      $description = $myrow[3];
      $creator = $myrow[4];
      $publisher = $myrow[5];
      $date = $myrow[6];

			$tool_content .= "<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\" id=\"edit_form\"><table><thead>";
			if ($table_edit == 'videolinks') {
					$tool_content .= "<tr><th>$langURL:</th>";
	    		$tool_content .= "<input type='hidden' name='id' value=' '>";
					$tool_content .= "<td><input type='text' name='url' value='$url' size='45'></td></tr>";		
				} 
				elseif ($table_edit == 'video') {
		    		$tool_content .= "<input type='hidden' name='url' value='$url'>";
			}
			$tool_content .= "<tr><th>$langVideoTitle:</th>";
			$tool_content .= "<td><input type=\"text\" name=\"titre\" value=\"".@$titre."\" size=\"55\"></td></tr>";
			$tool_content .= "<tr>
			<th>$langDescr&nbsp;:</th>
			<td>
			<textarea wrap=\"physical\" rows=\"3\" name=\"description\" cols=\"50\">".@$description."</textarea>
			</td>
			</tr>
			<tr>
			<th>$langcreator&nbsp;:</th>
			<td><input type=\"text\" name=\"creator\" value=\"".@$creator."\" size=\"55\"></td>
			</tr>
			<tr><th>$langpublisher &nbsp;:</th>
			<td><input type=\"text\" name=\"publisher\" value=\"".@$publisher."\" size=\"55\"></td>
			</tr>
			<tr><th>$langdate &nbsp;:</th>
			<td><input type=\"text\" name=\"date\" value=\"".@$date."\" size=\"55\"></td>
			</tr></thead></table><br/>
			<input type=\"submit\" name=\"edit_submit\" value=\"$langModify\"><br/><br/>
			<input type=\"hidden\" name=\"id\" value=\"".@$id."\">
			<input type=\"hidden\" name=\"table\" value=\"".$table_edit."\"></form>
			";		
		}
}	// if id
	
	$tool_content.="<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\" id=\"insert_form\"></form>";
	// print the list if there is no editing
	$results['video'] = db_query("SELECT *  FROM video ORDER BY titre",$currentCourseID);
	$results['videolinks'] = db_query("SELECT * FROM videolinks ORDER BY titre",$currentCourseID);
	$i=0;
	$tool_content.="<table width=\"99%\"><thead>
	<tr>
		<th>$langVideoTitle</th>
		<th>$langDescr</th>
		<th>$langcreator</th>
		<th>$langpublisher</th>
		<th>$langdate</th>
		<th>$langActions</th>
		</tr></thead>
		<tbody>";
	foreach($results as $table => $result)
	while ($myrow = mysql_fetch_array($result)) {
		switch($table){
			case "video":
				if(isset($vodServer))				
					$videoURL=$vodServer."$currentCourseID/".$myrow[1];
				else
					$videoURL="../../video/$currentCourseID/".$myrow[1];
				break;
			case "videolinks":
				$videoURL=$myrow[1];
				break;
			default:
				exit;
		}
		if($i%2)
		{
			$rowClass = ($i%2) ? "class=\"odd\"" : "";
			$tool_content.=sprintf("
				<tr $rowClass>
				<td><a href=\"%s\" target=\"_blank\">%s</a></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
					", $videoURL, $myrow[2], $myrow[3],$myrow[4], $myrow[5],$myrow[6]);
			$tool_content.=sprintf("<td align='center'>
			 <a href=\"%s?id=%s&table_edit=%s&action=edit\">
					<img src=\"../../template/classic/img/edit.gif\" border=\"0\" title=\"$langModify\"></a> ", $_SERVER['PHP_SELF'], $myrow[0],$table);
			$tool_content.=sprintf("<a href=\"%s?id=%s&delete=yes&table=%s\" onClick=\"return confirmation('".addslashes($myrow[2])."');\"><img src=\"../../template/classic/img/delete.gif\" border=\"0\" title=\"$langDelete\"></a>
					</td>
				</tr>
			", $_SERVER['PHP_SELF'], $myrow[0],$table);
		} else {
			$tool_content.=sprintf("
				<tr>
				<td><a href=\"%s\" target=\"_blank\">%s</a></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
							", $videoURL, $myrow[2], $myrow[3],$myrow[4], $myrow[5],$myrow[6]);
				$tool_content.=sprintf("<td align='center'>
					<a href=\"%s?id=%s&table_edit=%s&action=edit\">
					<img src=\"../../template/classic/img/edit.gif\" border=\"0\" title=\"$langModify\"></a> ", $_SERVER['PHP_SELF'], $myrow[0],$table);
				$tool_content.=sprintf("<a href=\"%s?id=%s&delete=yes&table=%s\" onClick=\"return confirmation('".addslashes($myrow[2])."');\">
					<img src=\"../../template/classic/img/delete.gif\" border=\"0\" title=\"$langDelete\"></a>						
					</td>
				</tr>
			", $_SERVER['PHP_SELF'], $myrow[0],$table);
		}// while
		$i++;
	}
	$tool_content.="</tbody></table>";
}   // if uid=prof_id

// student view
else {
	$results['video'] = db_query("SELECT *  FROM video ORDER BY titre",$currentCourseID);
	$results['videolinks'] = db_query("SELECT * FROM videolinks ORDER BY titre",$currentCourseID);
	$tool_content .= "<table width=\"99%\"><thead>
	<tr><th>$langVideoTitle</th><th>$langDescr</th></tr></thead><tbody>";
	$i=0;
	foreach($results as $table => $result)
	{
		while ($myrow = mysql_fetch_array($result)) {
			switch($table){
				case "video":
					if(isset($vodServer))
							$videoURL=$vodServer."$currentCourseID/".$myrow[1];
					else
							$videoURL="../../video/$currentCourseID/".$myrow[1];
				break;
				case "videolinks":
					$videoURL=$myrow[1];
					break;
				default:
					exit;
			}
			$rowClass = ($i%2)==0 ? "class=\"odd\"" : "";
			if($i%2==0) {				
				$tool_content.=sprintf("				
				<tr $rowClass>			
				<td>
				<a href=\"%s\" target=\"_blank\">%s</a></td>
				<td>%s
				</td>
				</tr>
				", $videoURL, $myrow[2], $myrow[3]);
			} elseif($i%2==1) {
				$tool_content.=sprintf("				
				<tr>			
				<td>
				<a href=\"%s\" target=\"_blank\">%s</a></td>
				<td>%s
				</td></tr>
				", $videoURL, $myrow[2], $myrow[3]);
			}
			$i++;
		}
	}
	$tool_content .= "</tbody></table>";
}
if (isset($head_content)) draw($tool_content, 2, 'user', $head_content);
else draw($tool_content, 2, 'user');
?>

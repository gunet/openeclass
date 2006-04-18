<?php 
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
  |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
  +----------------------------------------------------------------------+
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

$require_current_course = TRUE;
$langFiles = 'video';
$require_help = TRUE;
$helpTopic = 'Video';
include ('../../include/init.php');

$nameTools = $langVideo;

begin_page();

$d = mysql_fetch_array(db_query("SELECT video_quota FROM cours WHERE code='$currentCourseID'",$mysqlMainDb));
$diskQuotaVideo = $d['video_quota'];

$nameTools = $langVideo;

echo "<tr><td>";
if($is_adminOfCourse) {
	if (isset($submit)) {
		if (isset($tout)) {
			$sql="DELETE FROM video";
		}	
		elseif($id) {
			$sql = "UPDATE video SET titre='$titre', description='$description' WHERE id=$id";
		}	 
		else {
			$updir = "$webDir/courses/$currentCourseID/video/"; //path to upload directory
			if (($file_name != "") && ($file_size <= $diskQuotaVideo )) {
				// convert php file in phps to protect the platform against malicious codes
				$file_name = ereg_replace(".php$", ".phps", $file_name);

				$file_name = str_replace(" ", "%20", $file_name);
				$file_name = str_replace("%20", "", $file_name); 
				$file_name = str_replace(" ", "", $file_name);
				$file_name = str_replace("\'", "", $file_name);

				if ($titre == "") $titre = $file_name;
				
				@copy("$file", "$updir/$file_name")
					or die("<tr><td colspan=2><font size=2 face='arial, helvetica'>$langFileNot</td></tr>");
			} else {
				die("<tr><td colspan=2>
					<font size=2 face='arial, helvetica'>$langTooBig</font>
					</td>
				</tr>");
			}
			$url="$file_name";
			$sql = "INSERT INTO video (url,titre,description) VALUES ('$url','$titre','$description')";
		}	// else
		$result = db_query($sql,$currentCourseID);
		if (isset($tout)) {
			echo "<font size=2 face='arial, helvetica'>
				$langVideoDeleted
				<br><br>";
			echo "<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
			<br>";
		} elseif($id) {
			echo "<font size=\"2\" face=\"arial, helvetica\">
				$langTitleMod
				<br><br>
				<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
				<br>";
		} else {
			echo "<font size=\"2\" face=\"arial, helvetica\">
				$langFAdd
				<br><br>
				<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
			<br>";
		}	 // else
	}	// if submit

	elseif (isset($delete)) {
		$sql_select="SELECT url FROM video WHERE id=$id";
		$result = db_query($sql_select,$currentCourseID);
		$myrow = mysql_fetch_array($result);
		$nom_document=$myrow[0];
		unlink("$webDir/courses/$currentCourseID/video/$nom_document");
		$sql = "DELETE FROM video WHERE id=$id";
		$result = db_query($sql,$currentCourseID);
		echo "<font size=\"2\" face=\"arial, helvetica\">$langDelF
			<p><a href=\"$_SERVER[PHP_SELF]\">$langBack</a>";
	}	
	else {
########################### IF NO SUBMIT #############################
		if (!isset($id)) {
			// print the list if there is no editing
			$result = db_query("SELECT * FROM video ORDER BY titre",$currentCourseID);
			$i=0;
			while ($myrow = mysql_fetch_array($result)) {
				if($i%2)
				{
				printf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
				<tr>
					<td bgcolor=\"#F5F5F5\" width=\"30\" valign=\"top\">
						<a href=\"../../courses/$currentCourseID/video/%s\" ><img src=\"../../images/video.gif\" border=\"0\"  alt=\"video\"></a>
					</td>
					<td width=\"570\" valign=\"top\" bgcolor=\"#F5F5F5\">
						<font size=\"2\" face=\"arial, helvetica\">
							<a href=\"../../courses/$currentCourseID/video/%s\">%s</a>
							<br>
							%s
 						</font>", $myrow[1], $myrow[1], $myrow[2], $myrow[3]);
					printf("<br>
						<font size=\"1\" face=\"arial, helvetica\">
							<a href=\"%s?id=%s\">$langModify</a>
							 | 
							 ", $_SERVER['PHP_SELF'], $myrow[0]);
					printf("<a href=\"%s?id=%s&delete=yes\">$langDelete</a>
						</font>
					</td>
				</tr>
			</table>", $_SERVER['PHP_SELF'], $myrow[0]);
				} else {
				printf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
				<tr>
					<td bgcolor=\"#E6E6E6\" width=\"30\" valign=\"top\">
						<a href=\"../../courses/$currentCourseID/video/%s\"><img  alt=\"video\" src=\"../../images/video.gif\" border=\"0\"></a>
					</td>
					<td width=\"570\" valign=\"top\" bgcolor=\"#E6E6E6\">
						<font size=\"2\" face=\"arial, helvetica\">
							<a href=\"../../courses/$currentCourseID/video/%s\">%s</a>
							<br>
							%s", $myrow[1], $myrow[1], $myrow[2], $myrow[3]);
					printf("<br>
							<font size=\"1\" face=\"arial, helvetica\">
								|
								&nbsp;
								<a href=\"%s?id=%s\">$langModify</a>
								| ", $_SERVER['PHP_SELF'], $myrow[0]);
					printf("<a href=\"%s?id=%s&delete=yes\">$langDelete</a>
							</font>
						</font>
					</td>
				</tr>
			</table>", $_SERVER['PHP_SELF'], $myrow[0]);
				}
				$i++;
			}	// while
		######################### FORM #######################################
			echo "
			<br>
			<img  alt=\"\" src=\"../../images/ligne.png\">
			<br>
			$langAddV";
		}	// if ! id
		if (isset($id)) {
			$sql = "SELECT * FROM video WHERE id=$id ORDER BY titre";
			$result = db_query($sql,$currentCourseID);
			$myrow = mysql_fetch_array($result);
			$id = $myrow[0];
			$titre = $myrow[2];
			$description = $myrow[3];
		}	// if id
		echo "<form method=\"POST\" action=\"$_SERVER[PHP_SELF]?submit=yes\" enctype=\"multipart/form-data\">
			<input type=\"hidden\" name=\"id\" value=\"".@$id."\">
			<table>";
		if(!isset($id)) {
			echo "<tr><td><font size=\"2\" face=\"arial, helvetica\">
					$langsendV&nbsp;:
				</font></td>
			<td><input type=\"file\" name=\"file\" size=\"45\"></td>
			</tr>";
		}
		echo "<tr><td><font size=\"2\" face=\"arial, helvetica\">
				$langVideoTitle&nbsp;:
			</font></td>
			<td><input type=\"text\" name=\"titre\" value=\"".@$titre."\" size=\"55\"></td>
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
			<tr>
			<td colspan=\"2\">
				<font size=\"1\" face='arial, helvetica'>$langDelList&nbsp;:</font size>
				<input type=\"checkbox\" name=\"tout\" value=\"tout\">
			</td>
			</tr>
			<tr><td colspan=\"2\"><input type=\"Submit\" name=\"submit\" value=\"$langAdd\"></td></tr>
			</table>
		</form>";
	}	
}   // if uid=prof_id
############################# STUDENT VIEW #############################
else {
	$result = db_query("SELECT * FROM video ORDER BY titre",$currentCourseID);
	$i=0;
	while ($myrow = mysql_fetch_array($result)) {
		if($i%2==0) {
			printf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td bgcolor=\"#E6E6E6\" width=\"30\" valign=\"top\">
			<a href=\"../../courses/$currentCourseID/video/%s\"><img alt=\"video\" src=\"../../images/video.gif\" border=\"0\"></a>
			</td>
			<td width=\"570\" valign=\"top\" bgcolor=\"#E6E6E6\"><font size=\"2\" face=\"arial, helvetica\">
			<a href=\"../../courses/$currentCourseID/video/%s\">%s</a>
			<br>%s
			</td>
			</tr>
			</table>", $myrow[1], $myrow[1], $myrow[2], $myrow[3]);
		} elseif($i%2==1) {
			printf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td bgcolor=\"#F5F5F5\" width=\"30\" valign=\"top\">
			<a href=\"../../courses/$currentCourseID/video/%s\"><img alt=\"video\" src=\"../../images/video.gif\" border=\"0\"></a>
			</td>
			<td width=\"570\" valign=\"top\" bgcolor=\"#F5F5F5\"><font size=\"2\" face=\"arial, helvetica\">
			<a href=\"../../courses/$currentCourseID/video/%s\">%s</a>
			<br>%s
			</td></tr>
			</table>", $myrow[1], $myrow[1], $myrow[2], $myrow[3]);
		}	   
		$i++;
	}	 
}	

?>
</td></tr>

<tr><td colspan="2"><hr noshade size="1"></td></tr>
</table>
</html>
</body>

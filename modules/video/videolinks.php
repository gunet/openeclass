<? 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$               |
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
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$require_current_course = TRUE;
$langFiles = 'video';
$require_help = TRUE;
$helpTopic = 'VideoLinks';

include ('../../include/init.php');

$nameTools = $langVideoLinks;

begin_page();
echo "<tr><td><br>";

if ($is_adminOfCourse)   {
	if (isset($submit) && $submit) {
		if(isset($tout)) {
			$sql="DELETE FROM videolinks";
		} 
		elseif($id) {
		$sql = "UPDATE videolinks 
			SET url='".q(trim($url))."', titre='".q(trim($titre))."', description='".q(trim($description))."' 
			WHERE id=$id";
  		} else {
			$sql = "INSERT INTO videolinks (url,titre,description,visibility) 
				VALUES ('".q(trim($url))."','".q(trim($titre))."','".q(trim($description))."','1')";
		}
		$result = db_query($sql,$currentCourseID);
		if(isset($tout) && $tout) {
			echo "
			$langVideoDeleted
			<br><br>
			<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
			<br>";
  		} 
		elseif(isset($id) && $id) 
		{
			echo "$langVideoMod
			<br><br>
		  	<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
			<br>";
		} else {
			echo "
			$langVideoAdd
			<br><br>
			<a href=\"$_SERVER[PHP_SELF]\">$langBack</a>
			<br>";
		}
	} 
	elseif (isset($delete)) {
		// EFFACER
	    $sql = "DELETE FROM videolinks WHERE id=$id";
	    $result = db_query($sql,$currentCourseID);
		echo "$langVideoDel.
		<p>
			<a href=\"$_SERVER[PHP_SELF]\">$langBackList</a>";
	} 
	else {
		if (isset($id) and isset($visibility)) {
		$v = ($visibility == 1)? 1: 0;
	    	db_query("UPDATE videolinks SET visibility=$v WHERE id=$id",$currentCourseID);
		unset($id);
		} 

// if not submit 
		if (!isset($id)) {
			// print the list if there is no editing
			$result = db_query("SELECT * FROM videolinks ORDER BY titre",$currentCourseID);
			$i=0;
			while ($myrow = mysql_fetch_array($result)) {
			if($i%2==0) 
				$tdbgcolor="#E6E6E6";
			else if($i%2==1) 
				$tdbgcolor="#F5F5F5";

			// choose visibility link and icon
			if ($myrow[4]) {
				$iconvis="../../images/visible.gif";
				$linkvis=0;
				$visclass = '';
			} else { 
				$iconvis="../../images/invisible.gif"; 
				$linkvis=1;
				$visclass = 'class="invisible"';
			}
			
			printf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
			<tr>
 			<td bgcolor=".$tdbgcolor." width=\"20\" valign=\"top\">
			<a href=\"%s\">
			<img src=\"../../images/video.gif\" border=\"0\" alt=\"".$langVideo."\">
			</td>
			<td width=\"580\" valign=\"top\" bgcolor=".$tdbgcolor.">
				<font size=\"2\" face=\"arial, helvetica\">
				<a $visclass href=\"%s\">%s</a>
				<br>
				%s", $myrow[1], $myrow[1], $myrow[2], $myrow[3]);
				printf("
				<br>
				<font size=\"1\" face=\"arial, helvetica\">
					<a href=\"%s?id=%s&modify=yes\">
						<font color=\"#808080\" face=\"arial, helvetica\">
							$langModify
						</font >
					</a>
					&nbsp;
					<font color=\"#808080\" face=\"arial, helvetica\">|&nbsp;", $_SERVER['PHP_SELF'], $myrow[0]);
								
					printf("<a href=\"%s?id=%s&delete=yes\">
						<font color=\"#808080\" face=\"arial, helvetica\">
							$langDelete
						</font >
						</a>
					</font size>
				</td>
				<td bgcolor=".$tdbgcolor.">
				<a href='%s?id=%s&visibility=".$linkvis."'><img src=".$iconvis." border='0'></a>
				</td>
			 </tr>
			</table>", $_SERVER['PHP_SELF'], $myrow[0], $_SERVER['PHP_SELF'], $myrow[0]);
		$i++;
		} // end of while

			if ($i>0)
			echo "
				<br>
				<hr noshade size=\"1\">
				<font size=\"2\" face=\"arial, helvetica\">
					<a href=\"".$_SERVER['PHP_SELF']."?submit=submit&tout=tout\">
						$langDelList
					</a>
				</font>";
			echo "
				<br>
				<hr noshade size=\"1\">
				<br>
				$langAddVideoLink
				<br>";
		}


		if (isset($id) and isset($modify)) {

	    	// modify 
		$sql = "SELECT * FROM videolinks WHERE id=$id ORDER BY titre";
	    	$result = db_query($sql,$currentCourseID);
		    $myrow = mysql_fetch_array($result);
		    $id = $myrow[0];
		    $url = $myrow[1];
		    $titre = $myrow[2];
		    $description = $myrow[3];

		}		

echo "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
		<input type=\"hidden\" name=\"id\" value='".@$id."'>
		<table>
			<tr>
				<td>
					<font size=\"2\" face=\"arial, helvetica\">
						URL:
					</font>
				</td>
				<td>
					<input type=\"Text\" name=\"url\" value='".@$url."' size=\"55\">
				</td>
			</tr>
			<tr>
				<td>
					<font size=\"2\" face=\"arial, helvetica\">
						$langVideoTitle:
					</font>	
				</td>
				<td>
					<input type=\"Text\" name=\"titre\" value='".@$titre."' size=\"55\">
				</td>
			</tr>
			<tr>
				<td>
					<font size=\"2\" face=\"arial, helvetica\">
						$langDescr:
					</font>
				</td>
				<td>
					<textarea wrap=\"physical\" rows=\"3\" cols=\"50\"
					name=\"description\">".@$description."</textarea>
				</td>
			</tr>
			<tr>
				<td colspan=\"2\">
					<input type=\"Submit\" name=\"submit\" value=\"$langAdd\">
				</td>
			</tr>
		</table>
	</form>";
	}


} // end of is_admin_of_course

// student view

else {
	$result = db_query("SELECT * FROM videolinks WHERE visibility='1' ORDER BY titre",$currentCourseID);
	$i=0;
	while ($myrow = mysql_fetch_array($result)) {
	
		if($i%2==0)
                         $tdbgcolor="#E6E6E6";
                else if($i%2==1)
                         $tdbgcolor="#F5F5F5";

		printf("<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td bgcolor=".$tdbgcolor." width=\"20\" valign=\"top\">
				<a href=\"%s\">
				<img src=\"../../images/video.gif\" border=\"0\" alt=\"".$langVideo."\">
				</a>
			</td>
			<td width=\"580\" valign=\"top\" bgcolor=".$tdbgcolor.">
			<font size=\"2\" face=\"arial, helvetica\">
                        <a href=\"%s\">%s</a>
			<br>%s</font>
			</td>
			</tr>
			</table>", $myrow[1], $myrow[1], $myrow[2], $myrow[3]);
		$i++;
	} // end of while
}
?>
		</td>
	</tr>
<tr><td colspan="2"><br><hr noshade size="1"></td></tr>
</table>
</body>
</html>

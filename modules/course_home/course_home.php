<?
/*
      +----------------------------------------------------------------------+
      | e-class version 1.0                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$		     |
      +----------------------------------------------------------------------+
      |   $Id$
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
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
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
      |                                                                      |
      | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      +----------------------------------------------------------------------+
 */

$require_current_course = TRUE;
$langFiles = 'course_home';
include ('../../include/init.php');
include ('../../include/lib/textLib.inc.php'); 

begin_page();

$moduleId = 1;
echo "<tr><td colspan='4' bgcolor='$color1'style='padding-left: 15px; padding-right:15px; padding-bottom: 5px;'>";
include "introductionSection.inc.php"; 
?>

</td>
</tr>
<tr><td>&nbsp;</td></tr>
<?
if ($is_adminOfCourse) {
		
	if(isset($remove) && $remove) {
		echo "<tr><td colspan=\"4\" bgcolor=\"".$color2."\" >
		<font face=\"arial, helvetica\" color=\"#ff0000\">
		<strong>".$langDelLk."</strong>
		<br>
		<a href=\"$_SERVER[PHP_SELF]\"><font face=\"arial, helvetica\">$langNo</font></a>
		&nbsp;|&nbsp;
		<a href=\"$_SERVER[PHP_SELF]?destroy=yes&id=$id\"><font face=\"arial, helvetica\">$langYes</font></a>
		<br>
		<br>
		</font>
		</td>
		</tr>";
	} 
	elseif (isset($destroy) && $destroy) {
		$sql = "UPDATE accueil SET visible='2' WHERE id=$id";
		db_query($sql);
	}
	

########################## Hide #######################
	elseif (isset ($hide) && $hide) {
		$sql = "UPDATE accueil SET visible=0 WHERE id=$id";
		db_query($sql);
	}   

################### reactivate #########################
	elseif (isset($restore) && $restore) {
		$sql = "UPDATE accueil SET visible=1 WHERE id=$id";
		db_query($sql);
	}   

################## update ##########################
// added by haniotak@ucnet.uoc.gr 17 May 04
    elseif (isset($submit) && $submit) {
        $sql = "UPDATE accueil SET rubrique = '$rubrique' WHERE id = '$id'";
      	db_query($sql);
    }
// end add
	elseif (isset ($update) && $update) {
		$sql = "SELECT * FROM accueil WHERE id=$id";
		$result = db_query($sql);
		$toolsRow = mysql_fetch_array($result);
		$rubrique = $toolsRow[1];
		echo "<tr><td colspan=\"4\">
		<table><tr>
		<td><form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
		<input type=\"hidden\" name=\"id\" value=\"$id\">
		</td>";
		echo "</tr><tr>
			<td>
			<font face=\"arial, helvetica\">$langNameOfTheLink:</font>
			</td>
			<td>
			<input type=\"Text\" name=\"rubrique\" value=\"".$rubrique."\">
			</td>
			</tr>
			<tr>
			<td colspan=\"2\">
			<input type=\"Submit\" name=\"submit\" value=\"".$langUpdate."\">
			</td>
			</tr>
			</form>
			</table>
		</td>
		</tr>";
	}
}


// work with data post by admin of  course
if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"]) {
	if(isset($askDelete) && $askDelete) {
		echo "<tr>
		<td colspan=\"4\" bgcolor=\"".$color2."\" >
		<font face=\"arial, helvetica\" color=\"#ff0000\">
		<strong>".$langDelLk."</strong>
		<br>
		<a href=\"$_SERVER[PHP_SELF]\"><font face=\"arial, helvetica\">$langNo</font></a>
		&nbsp;|&nbsp;
		<a href=\"$_SERVER[PHP_SELF]?delete=yes&id=$id\">
		<font face=\"arial, helvetica\">$langYes</font></a>
		<br>
		<br>
		</font>
		</td>
		</tr>";
	} elseif (isset($delete) && $delete) {
		$sql = "DELETE FROM accueil WHERE id=$id";
		db_query($sql);
	}
 }

showtools('Public');

// professor view
if ($is_adminOfCourse) {
	echo "<tr>
	<td colspan=\"4\"><hr noshade size=\"1\"></td>
	</tr>
	<tr>
	<td colspan=\"4\">
	<font color=\"#F66105\" size=\"2\" face=\"arial, helvetica\">$langAdminOnly</font>
	</td>
	</tr>";
	
	showtools('courseAdmin');
	
// inactive links
	echo "<tr>
		<td colspan=\"4\">
		<hr noshade size=\"1\">
		</td>
		</tr>
		<tr>
		<td colspan=\"4\">
		<font size=\"2\" color=\"#808080\" face=\"arial, helvetica\">$langInLnk</font>
		</td>
	</tr>";
	showTools('PublicButHide');
}

// tools for admin only
if (isset($_SESSION["is_admin"]) and $_SESSION['is_admin']) {
	echo "<tr>
		<td colspan=\"4\">
		<hr noshade size=\"1\">
		</td>
		</tr>
		<tr>
		<td colspan=\"4\">
		<font color=\"#F66105\" size=\"2\" face=\"arial, helvetica\">
		$langAdminOnly
		</font>
		</td>
	</tr>";
	showtools('claroAdmin');
}
echo "<tr><td colspan=\"4\"><hr noshade size=\"1\"></td></tr></table>";
echo "</body></html>";

$table= "stat_accueil"; 

// statistics  - Count only if first visit during the session
if (!isset($alreadyHome) || (isset($alreadyHome) && !$alreadyHome)) {
	include ("../../modules/stat/write_logs.php"); 
}

$alreadyHome = 1;
session_register("alreadyHome");

// function for displaying tools
function showtools($cat) {

	global $is_adminOfCourse, $langDeactivate, $langActivate, $langRemove, $langUpdate, $langDelete;

	switch ($cat) { 
		case 'Public':
			# Show "users" link only if user is logged in
			if (isset($_SESSION['uid']) and $_SESSION['uid']) {
				$result = db_query("
					select * from accueil 
					where visible=1 
					ORDER BY id");
			} else {
				$result = db_query("
					select * from accueil 
					where visible=1 AND lien NOT LIKE '%/user.php'
					ORDER BY id");
			}
			break;
			case 'PublicButHide':
				$result = db_query("
					select * 
					from accueil 
					where visible=0 
					and admin=0 
					ORDER BY id");
				break;
			case 'courseAdmin':
				$result = db_query("
					select * 
					from accueil 
					where admin=1 
					ORDER BY id");
				break;
			case 'claroAdmin':
				$result = db_query("
					select * 
					from accueil 
					where visible = 2
					ORDER BY id");
	}

	$i=0;
	echo "<tr><td><table>\n";
	while ($toolsRow = mysql_fetch_array($result)) {
		if (!($i%2))
			echo "<tr>\n";
		echo "<!-- tools $i -->
		<td width=\"20\" valign=\"top\">
			<a href=\"".$toolsRow["lien"]."\">
			<img alt=\"\" src=\"image/".$toolsRow["image"]."\" border=\"0\"></a>
		</td>
		<td width=\"280\" valign=\"top\">
			<font size=\"2\"  face=\"arial, helvetica\">
				<a href=\"".$toolsRow["lien"]."\">".$toolsRow["rubrique"]."</a>
			</font>";
		$changeToolsLinks ="";
		if ($is_adminOfCourse) {
			if ($toolsRow["visible"]) {
				$changeToolsLinks .= "<a href='$_SERVER[PHP_SELF]?id=$toolsRow[id]&hide=yes'>".
					"<font size='1' color='#808080' face='arial, helvetica'>".
					"$langDeactivate</font></a>";
			}
			if ($cat=='PublicButHide') {
				$changeToolsLinks .= "<a href='$_SERVER[PHP_SELF]?id=$toolsRow[id]&restore=yes'>".
					"<font size='1' color='#808080' face='arial, helvetica'>".
					"$langActivate</font></a>";
				if($toolsRow["id"] > 20) {
					$changeToolsLinks .= " <a href='$_SERVER[PHP_SELF]?id=$toolsRow[id]&remove=yes'>".
						"<font size='1' color='#808080' face='arial, helvetica'>".
						"$langRemove</font></a>";
				}
			}
		}
		if (isset($_SESSION["is_admin"]) and $_SESSION['is_admin']) {
			if ($toolsRow["visible"]==2)
			{
				$changeToolsLinks .="
			<a href=\"".$_SERVER['PHP_SELF']."?id=".$toolsRow["id"]."&restore=yes\">
			<font size=\"1\" color=\"#808080\" face=\"arial, helvetica\">".$langActivate."</font></a>";
				if($toolsRow["id"]>20)
				{
				$changeToolsLinks .="
			<a href=\"".$_SERVER['PHP_SELF']."?id=".$toolsRow["id"]."&askDelete=yes\">
			<font size=\"1\" color=\"#808080\" face=\"arial, helvetica\">".$langDelete."</font></a>";
				}
			}
			$changeToolsLinks .="
			<a href=\"".$_SERVER['PHP_SELF']."?id=".$toolsRow["id"]."&update=yes\">
			<font size=\"1\" color=\"#808080\" face=\"arial, helvetica\">".$langUpdate."</font></a>";
		}
		if (!empty($changeToolsLinks)) {
			echo "<br>".$changeToolsLinks;
		}
		echo "</td>";
		if($i%2)
			echo "</tr>";
		$i++;
	}

	if($i%2) {
		echo "
		<td>&nbsp;</td>
		<td width=\"20\">
		&nbsp;
		</td>
	</tr>";
	}
	echo "</table></td></tr>\n";
}
?>

<?php

/*
      +----------------------------------------------------------------------+
      | e-Class version 1.2                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$                   |
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
      | e-Class Authors:    Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      | upatras.gr patch    atkyritsis@upnet.gr, daskalou@upnet.gr           |
      |                                                                      |
      | Claroline Authors:  Thomas Depraetere <depraetere@ipm.ucl.ac.be>     |
      |                     Hugues Peeters    <peeters@ipm.ucl.ac.be>        |
      |                     Christophe Gesché <gesche@ipm.ucl.ac.be>         |
      |                                                                      |
      +----------------------------------------------------------------------+
*/

$local_style = 'em, h3 { color: #f0741e; }
h2 { font-size: 12pt; font-style: bold; }
.courses { font-size: 10pt; }
.small { font-size: 9pt; }
.normal { font-size: 12pt; }
.largeorange { color: #f0741e; font-size: 12pt; font-weight: bold;}';

$require_login = TRUE;
$langFiles = array('registration', 'opencours');
include("../../include/init.php");

$nameTools = $langCoursesLabel;

check_guest();

$icons = array(
	2 => "<img src=\"../../images/gunet/OpenCourse.gif\" alt=\"\">",
	1 => "<img src=\"../../images/gunet/Registration.gif\" alt=\"\">",
	0 => "<img src=\"../../images/gunet/ClosedCourse.gif\" alt=\"\">" 
);

begin_page();

if (isset($_POST["submit"])) {
	if (isset($changeCourse) && is_array($changeCourse)) {
		// check if user tries to unregister from restricted course
		foreach ($changeCourse as $key => $value) {
			if (!isset($selectCourse[$key]) and is_restricted($value)) {
				echo "(restricted unsub $value) ";
			}
		}
		foreach ($changeCourse as $value) {
			db_query("DELETE FROM cours_user WHERE statut <> 1 
				AND statut <> 10 AND user_id = '$uid' AND code_cours = '$value'");
		}
	}
  if (isset($selectCourse) and is_array($selectCourse)) {
		while (list($key,$contenu) = each ($selectCourse)) { 
			$sqlInsertCourse = 
				"INSERT INTO `cours_user` 
					(`code_cours`, `user_id`, `statut`, `role`)
					VALUES ('".$contenu."', '".$uid."', '5', ' ')"; 
			mysql_query($sqlInsertCourse) ;
		    if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()."<br>";
		}
	}
	echo "
	<H3>
		".$langIsReg."
	</H3>
	<br>
	
	<a href=\"../../index.php\">$langHome</a>
	<hr>";
}
else
{
	echo "</td></tr>";
	echo "<tr><td bgcolor= $color2 >";


// check if user requested a specific faculte
if (isset( $_GET['fc'] ) ) { 
	// get faculte name from db
	$fac = getfacfromfc( $_GET['fc'] );
} else {
	// get faculte name from user's department column
	$fac = getfacfromuid($uid);
	echo $fac;
}

if (!$fac) {
 	echo "</td></tr>";
	echo "<tr><td bgcolor= $color2>$langAddHereSomeCourses</td></tr>";
        echo "<tr><td bgcolor= $color2 >";
	collapsed_facultes_vert(0);
	}
else {
	// department exists
	
	echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">";
	$formend = "<tr>
			<TD colspan=\"6\" bgcolor= $color2 >
				<input type=\"submit\" name=\"submit\" value=\"$langSubscribe\">
			</TD>
		</TR>";
		
	$numofcourses = getdepnumcourses($fac);
		
	// display all the facultes collapsed
	collapsed_facultes_horiz($fac);
	if ( $numofcourses > 0 ) {
		expanded_faculte($fac, $uid);
	}
	

} // end of else (department exists)


	echo "
		</td></tr>
		";
	if (isset($formend))
		echo $formend;
}
?>
			</table>
		</form>
		</td>
	</tr>
</table>
</body>
</html>

<?php

function getfacfromfc( $dep_id) {
	$dep_id = intval( $dep_id);
	
	$fac = mysql_fetch_row(mysql_query(
	"SELECT name FROM faculte WHERE id = '$dep_id'"));
	if ( isset($fac[0] ) )
		return $fac[0];
	else
		return 0;
}

function getfacfromuid($uid) {
	$res = mysql_fetch_row(mysql_query(
	"SELECT name
	FROM faculte,user
	WHERE user.user_id = '$uid'
		AND faculte.id = user.department"));
	if ( isset($res[0]) )
		return $res[0];
	else
		return 0;
}

function getdepnumcourses($fac) {
	$res = mysql_fetch_row(mysql_query(
	"SELECT count(code) 
	FROM cours_faculte
	WHERE faculte='$fac'" ));
	return $res[0];
}



function expanded_faculte($fac, $uid) {
	global $m, $icons, $langTitular, $langBegin, $mysqlMainDb;
	
	// build a list of  course follow  by  user.
	$sqlListOfCoursesOfUser = "
	SELECT 
		code_cours cc,
		statut ss
	FROM `$mysqlMainDb`.cours_user
	WHERE user_id = ".$uid;
	
	$listOfCoursesOfUser = mysql_query($sqlListOfCoursesOfUser);
	
	// build array of user's courses
	while ($rowMyCourses = mysql_fetch_array($listOfCoursesOfUser)) {
	 	$myCourses[$rowMyCourses["cc"]]["subscribed"]= TRUE; 
	 	$myCourses[$rowMyCourses["cc"]]["statut"]= $rowMyCourses["ss"]; 
	}
	
	echo "<h2><a name=\"top\">$m[department]:</a> <em>$fac</em></h2>";
	
	// get the different course types available for this faculte
		$typesresult = mysql_query(
		"SELECT DISTINCT cours.type types 
		FROM cours 
		WHERE cours.faculte = '$fac' 
			AND cours.visible <> 0 
		ORDER BY cours.type");
		
		// count the number of different types
		$numoftypes = mysql_num_rows($typesresult);
		// output the nav bar only if we have more than 1 types of courses
		if ( $numoftypes > 1) {
			echo "<font class=\"courses\">";
			$counter = 1;
			while ($typesArray = mysql_fetch_array($typesresult)) {
				$t = $typesArray['types'];
				// make the plural version of type (eg pres, posts, etc)
				// this is for fetching the proper translations
				// just concatenate the s char in the end of the string
				$ts = $t."s";
				//type the seperator in front of the types except the 1st
				if ($counter != 1) echo " | ";
				echo "<a href=\"#".$t."\">".$m["$ts"]."</a>";
				$counter++;
			}
			echo "</font>";
		}
		
		// now output the legend
		echo "<hr><font class=\"courses\">"
		."<b>".$m['legend'].":</b> ".$icons[2]
		." ".$m['legopen']." | ".$icons[1]
		." ".$m['legrestricted']
		//." | "
		//.$icons[0]
		//." ".$m['legclosed']
		."</font>";
		
		echo "<div class='courses'>";
		
		// changed this foreach statement a bit
				// this way we sort by the course types
				// then we just select visible
				// and finally we do the secondary sort by course title and but teacher's name
				foreach (array("pre" => $m['pres'],
				               "post" => $m['posts'],
				               "other" => $m['others']) as $type => $message) {
					$result=mysql_query("SELECT
						cours.code k,
						cours.fake_code c,
						cours.intitule i,
						cours.visible visible,
						cours.titulaires t
			        FROM cours_faculte, cours
			        WHERE cours.code = cours_faculte.code
							      AND cours.type = '$type'
                		AND cours_faculte.faculte='$fac'
						AND cours.visible <> '0'
		                ORDER BY cours.intitule, cours.titulaires");
					
					if (mysql_num_rows($result) == 0) {
						continue;
					}
					
					// We changed the style a bit here and we output types as the title
					echo "<hr><a name=\"$type\" class=\"largeorange\">$message</a><p>\n";
					
					while ($mycours = mysql_fetch_array($result)) {
					// changed the variable because of the previous change in the select argument
						if ($mycours['visible'] == 2) {
							$codelink = "<a href='../../$mycours[k]/' target=\"blank\">$mycours[c]</a>";
						} else {
							$codelink = $mycours['c'];
						}
						
						// output each course as a table for beautifying reasons
						echo "<table border=\"0\" class=\"courses\" cellspacing=\"0\" cellpadding=\"0\">
						<tr><td rowspan=\"2\" valign=\"top\">";
						
						// show the necessary access icon
						foreach ( $icons as $visible => $image) {
							if ( $visible == $mycours['visible'] ) {
								echo $image;
							}
						}
						
						echo "</td><td width=\"100%\" valign=\"top\">"
							.$codelink.": <b>$mycours[i]</b> </td>"
							."<td align=\"right\">";
						
						if (isset ($myCourses[$mycours["k"]]["subscribed"])) { 
							if ($myCourses[$mycours["k"]]["statut"]!=1) {
								echo "<input type='checkbox' name='selectCourse[]' value='$mycours[k]' checked >";
							} else {
								echo "[$langTitular]";
							}
						}
						else { 
							echo "<input type='checkbox' name='selectCourse[]' value='$mycours[k]'>";
						}
						echo "<input type='hidden' name='changeCourse[]' value='$mycours[k]'>\n";
						
							echo "</td></tr>
							<tr>
							<td colspan=\"2\">$mycours[t]</td>
							</tr></table></p>";
					}
					// output a top href link if necessary
               if ( $numoftypes > 1)
	               echo "<div class=\"courses\" align=\"right\"><a href=\"#top\">".$langBegin."</a></div>";
					
					// that's it!
					// upatras.gr patch end
				}
				
			echo "<hr size=\"1\"></div>";
}

function collapsed_facultes_vert($fac) {
	
	global $avlesson, $avlessons;
	
	$result = mysql_query(
		"SELECT DISTINCT cours.faculte f, faculte.id id
		FROM cours, faculte 
		WHERE (cours.visible = '1' OR cours.visible = '2') 
			AND faculte.name = cours.faculte
			AND faculte.name <> '$fac'
		ORDER BY cours.faculte");
	
	while ($fac = mysql_fetch_array($result)) {
		echo "<blockquote>";
		echo "<a href=\"?fc=$fac[id]\" class=\"normal\">$fac[f]</a>";
		
		$n = mysql_query("SELECT COUNT(*) FROM cours
			WHERE cours.faculte='$fac[f]' AND cours.visible <> '0'");
                $r = mysql_fetch_array($n);
                echo " <span style='font-size: 10pt'>($r[0] "
                        . ($r[0] == 1? $avlesson: $avlessons) . ")</span><br>\n";
		echo "</blockquote>";
	}
		echo "<br>";
}

function collapsed_facultes_horiz($fac) {
	
	$result = mysql_query(
		"SELECT DISTINCT cours.faculte f, faculte.id id
		FROM cours, faculte 
		WHERE (cours.visible = '1' OR cours.visible = '2') 
			AND faculte.name = cours.faculte
		ORDER BY cours.faculte");
	$counter = 1;
	while ($facs = mysql_fetch_array($result)) {
		if ($counter != 1) echo "<font class=\"small\"> | </font>";
		if ($facs['f'] != $fac)
			$codelink = "<a href=\"?fc=$facs[id]\" class=\"small\">$facs[f]</a>"; 
		else
			$codelink = "<font class=\"small\">$facs[f]</font>";

		echo $codelink;
		$counter++;
	}
	
	echo "<hr size=\"1\">";
}

function is_restricted($course)
{
	$res = mysql_fetch_row(db_query("SELECT visible FROM cours
		WHERE code = ".quote($course)));
	if ($res[0] == 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}

?>

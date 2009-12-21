<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/

$require_login = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langChoiceLesson;
$navigation[] = array ("url"=>"courses.php", "name"=> $langChoiceDepartment);
$tool_content = "";

$icons = array(
2 => "<img src=\"../../template/classic/img/OpenCourse.gif\" title=\"\">",
1 => "<img src=\"../../template/classic/img/Registration.gif\" title=\"\">",
0 => "<img src=\"../../template/classic/img/ClosedCourse.gif\" title=\"\">"
);

if (isset($_REQUEST['fc'])) {
        $fc = intval($_REQUEST['fc']);
} elseif (isset($_SESSION['fc_memo'])) {
        $fc = $_SESSION['fc_memo'];
} else {
        $fc = getfcfromuid($uid);
}
$_SESSION['fc_memo'] = $fc;

$restrictedCourses=null; //DUKE
$i=0; //DUKE

if (isset($_POST["submit"])) {
        if (isset($changeCourse) && is_array($changeCourse)) {
                // check if user tries to unregister from restricted course
                foreach ($changeCourse as $key => $value) {
                        if (!isset($selectCourse[$key]) and is_restricted($value)) {
                                $tool_content .= "(restricted unsub $value) ";
                        }
                }
                foreach ($changeCourse as $value) {
                        db_query("DELETE FROM cours_user WHERE statut <> 1
                                        AND statut <> 10 AND user_id = '$uid' AND code_cours = '$value'");
                }
        }

	$errorExists = false;
        if (isset($selectCourse) and is_array($selectCourse)) {
                while (list($key,$contenu) = each ($selectCourse)) {
				 $sqlcheckpassword = mysql_query("SELECT password FROM cours WHERE code='".$contenu."'");
                        $myrow = mysql_fetch_array($sqlcheckpassword);
                        if ($myrow['password'] != "" and $myrow['password'] != autounquote($$contenu)) {
                                $errorExists = true;
                        } else {
                        	if(!is_restricted($contenu)) { //do not allow registration to restricted course
                                $sqlInsertCourse =
                                "INSERT INTO `cours_user` (`code_cours`, `user_id`, `statut`, `reg_date`)
                                         VALUES ('".$contenu."', '".$uid."', '5', CURDATE())";
                                mysql_query($sqlInsertCourse) ;
                                if (mysql_errno() > 0) {
              						echo mysql_errno().": ".mysql_error()."<br>";
              					}
                        	} else { //DUKE
                                        $restrictedCourses[$i]=$contenu;
                        	}
                        }
                }
        }

	if (!$errorExists)
	     $tool_content .= "
    <p class=\"success_small\">$langRegDone</p><br/>";
		else
		  $tool_content .= "
    <p class=\"caution_small\">$langWrongPassCourse $contenu</p><br/>";
        $tool_content .= "
    <div align=right><a href=\"../../index.php\">$langHome</a></div>";

} else {
        $fac = getfacfromfc($fc);
	if (!$fac) {
		$tool_content .= "
		<p align='justify'>$langAddHereSomeCourses</p>";
		$result=db_query("SELECT id, name, code FROM faculte ORDER BY name");
		$numrows = mysql_num_rows($result);
		if (isset($result))  {
			$tool_content .= "
			<table width=\"99%\" class=\"framed\">
            <tr>
              <td>
			<script type=\"text/javascript\" src=\"sorttable.js\"></script>
			<table width='100%' class=\"sortable\" id=\"t1\">
			<thead>
			  <tr><th class='left'><b>$langFaculty</b></th></tr>
			</thead>
            <tbody>";
			$k = 0;
			while ($fac = mysql_fetch_array($result)) {
				if ($k%2==0) {
					$tool_content .= "\n        <tr>";
				} else {
					$tool_content .= "\n        <tr class=\"odd\">";
				}
				$tool_content .= "\n<td>&nbsp;<img src='../../images/arrow_blue.gif'>&nbsp;
					<a href='$_SERVER[PHP_SELF]?fc=$fac[id]'>" . htmlspecialchars($fac['name']) . "</a> <small><font color='#a33033'>($fac[code])</font></small>";
				$n=db_query("SELECT COUNT(*) FROM cours_faculte WHERE facid='$fac[id]'");
				$r=mysql_fetch_array($n);
				$tool_content .= "&nbsp;<small><font color=#a5a5a5>($r[0]  ". ($r[0] == 1? $langAvCours: $langAvCourses) . ")</font><small></td></tr>";
				$k++;
			}
			$tool_content .= "\n</tbody>\n</table>";
			$tool_content .= "\n\n</td>\n</tr>\n</table>\n";
		}
		$tool_content .= "<br>\n";
		$tool_content .= "<br>\n";
	} else {
		// department exists
		$numofcourses = getdepnumcourses($fc);
		// display all the facultes collapsed
		$tool_content .= collapsed_facultes_horiz($fc);
		$tool_content .= "\n    <form action=\"$_SERVER[PHP_SELF]\" method=\"post\">";
		//$tool_content .= "\n    <br/>";
		if ($numofcourses > 0) {
			$tool_content .= expanded_faculte($fac, $fc, $uid);
			$tool_content .= "
    <br>
    <table width=\"99%\" class=\"framed\" align=\"left\">
    <tbody>
    <tr>
      <td><input class=\"Login\" type=\"submit\" name=\"submit\" value=\"$langRegistration\"></td>
    </tr>
    </tbody>
    </table>
    </form>";
		} else {
			if ($fac) {
				$tool_content .= "<table width=99% align=\"left\">
				<tr>
				<td><a name='top'>&nbsp;</a>$langFaculty:&nbsp;<b>$fac</b></td>
				<td>&nbsp;</td>
				</tr></table>";
				$tool_content .= "<br/><br/>
				<div class=alert1>$langNoCoursesAvailable</div>\n";
			}
		}
	} // end of else (department exists)
}

draw($tool_content, 1);

// functions
function getfacfromfc( $dep_id) {
	$dep_id = intval( $dep_id);

	$fac = mysql_fetch_row(db_query("SELECT name FROM faculte WHERE id = '$dep_id'"));
	if (isset($fac[0]))
		return $fac[0];
	else
		return 0;
}

function getfcfromuid($uid) {
	$res = mysql_fetch_row(db_query("SELECT department FROM user WHERE user_id = '$uid'"));
	if (isset($res[0]))
		return $res[0];
	else
		return 0;
}

function getdepnumcourses($fac) {
	$res = mysql_fetch_row(db_query(
	"SELECT count(code)
	FROM cours_faculte
	WHERE facid='$fac'" ));
	return $res[0];
}

function expanded_faculte($fac_name, $facid, $uid) {
	global $m, $icons, $langTutor, $langBegin, $langRegistration, $mysqlMainDb,
		$langRegistration, $langCourseCode, $langTeacher, $langType, $langFaculty,
		$langpres, $langposts, $langothers;

	$retString = "";

	// build a list of  course follow  by  user.
	$sqlListOfCoursesOfUser = "SELECT code_cours cc, statut ss
		FROM `$mysqlMainDb`.cours_user
		WHERE user_id = ".$uid;

	$listOfCoursesOfUser = db_query($sqlListOfCoursesOfUser);

	// build array of user's courses
	while ($rowMyCourses = mysql_fetch_array($listOfCoursesOfUser)) {
	 	$myCourses[$rowMyCourses["cc"]]["subscribed"]= TRUE;
	 	$myCourses[$rowMyCourses["cc"]]["statut"]= $rowMyCourses["ss"];
	}

	$retString .= "<table width=\"99%\" align=\"left\"><tbody><tr>
	<td><a name=\"top\"> </a>$langFaculty: <b>$fac_name</b>&nbsp;&nbsp;</td>";
	// get the different course types available for this faculte
	$typesresult = db_query("SELECT DISTINCT cours.type types
		FROM cours WHERE cours.faculteid = '$facid'
		AND cours.visible <> 0
		ORDER BY cours.type");

	// count the number of different types
	$numoftypes = mysql_num_rows($typesresult);
	// output the nav bar only if we have more than 1 types of courses
	if ($numoftypes > 1) {
		$retString .= "<td><div align=\"right\">";
		$counter = 1;
		while ($typesArray = mysql_fetch_array($typesresult)) {
			$t = $typesArray['types'];
			// make the plural version of type (eg pres, posts, etc)
			// this is for fetching the proper translations
			// just concatenate the s char in the end of the string
			$ts = $t."s";
			//type the seperator in front of the types except the 1st
			if ($counter != 1) $retString .= " | ";
				$retString .= "<a href=\"#".$t."\">".${'lang'.$ts}."</a>";
				$counter++;
			}
		$retString .= "</div></td></tr></tbody></table><br>\n\n";
	} else {
		$retString .= "\n      <td>&nbsp;</td>\n</tr>\n
		</thead>\n</table>\n<p>&nbsp;</p>\n<p>&nbsp;</p>";
	}

	  // changed this foreach statement a bit
	  // this way we sort by the course types
	  // then we just select visible
	  // and finally we do the secondary sort by course title and but teacher's name
	foreach (array("pre" => $langpres,
			"post" => $langposts,
			"other" => $langothers) as $type => $message) {

			$result=db_query("SELECT
				cours.code k,
				cours.fake_code c,
				cours.intitule i,
				cours.visible visible,
				cours.titulaires t,
				cours.password p
				FROM cours_faculte, cours
				WHERE cours.code = cours_faculte.code
						AND cours.type = '$type'
				AND cours_faculte.facid='$facid'
						AND cours.visible <> '0'
				ORDER BY cours.intitule, cours.titulaires");

		if (mysql_num_rows($result) == 0) {
				continue;
		}

		if ($numoftypes > 1) {
			$retString .= "\n    <br>";
			$retString .= "\n    <table width=\"99%\">";
			$retString .= "\n    <tbody>";
			$retString .= "\n    <tr>";
			$retString .= "\n      <td><a name=\"$type\"></a><b>$message</b></td>";
			$retString .= "\n      <td align=\"right\"><a href=\"#top\">".$langBegin."</a>&nbsp;</td>";
			$retString .= "\n    </tr>";
			$retString .= "\n    </tbody>";
			$retString .= "\n    </table>\n";
		} else {
			$retString .= "\n    <br>";
			$retString .= "\n    <table width=\"99%\">";
			$retString .= "\n    <thead>";
			$retString .= "\n    <tr>";
			$retString .= "\n      <td><a name=\"$type\"></a><b>$message</b></td>";
			$retString .= "\n      <td>&nbsp;</td>";
			$retString .= "\n    </tr>";
			$retString .= "\n    </thead>";
			$retString .= "\n    </table>\n\n";
		}

		// legend
		$retString .= "\n  <table width=\"99%\" class=\"framed\">";
		$retString .= "\n  <tr>";
		$retString .= "\n    <td>\n";
		$retString .= "\n    <script type=\"text/javascript\" src=\"sorttable.js\"></script>";
		$retString .= "\n    <table class=\"sortable\" id=\"t1\" width=\"100%\">";
		$retString .= "\n    <thead>";
		$retString .= "\n    <tr>";
		$retString .= "\n      <th width='10%'>$langRegistration</th>";
		$retString .= "\n      <th class='left'>$langCourseCode</th>";
		$retString .= "\n      <th class='left' width='23%'>$langTeacher</th>";
		$retString .= "\n      <th width='7%'><b>$langType</b></th>";
		$retString .= "\n    </tr>";
		$retString .= "\n    </thead>";
		$retString .= "\n    <tbody>";
		$k=0;
		while ($mycours = mysql_fetch_array($result)) {
			if ($mycours['visible'] == 2) {
				$codelink = "<a href='../../courses/$mycours[k]/' target=_blank>$mycours[i]</a>";
			} else {
				$codelink = $mycours['i'];
			}
			if ($k%2==0) {
				$retString .= "\n    <tr>";
			} else {
				$retString .= "\n    <tr class=\"odd\">";
			}
			$retString .= "\n      <td width='10%' align='center'>";
			$requirepassword = "";
			if (isset ($myCourses[$mycours["k"]]["subscribed"])) {
				if ($myCourses[$mycours["k"]]["statut"]!=1) {
					// password needed
					if ($mycours['p']!="" && $mycours['visible'] == 1) {
						$requirepassword = $m['code'].": <input type=\"password\" name=\"".$mycours['k']."\" value=\"".$mycours['p']."\">";
					} else {
						$requirepassword = "";
					}
					$retString .= "<input type='checkbox' name='selectCourse[]' value='$mycours[k]' checked >";
				} else {
					$retString .= "<img src=../../template/classic/img/teacher.gif title=$langTutor>";
				}
			} else {
				if ($mycours['p']!="" && $mycours['visible'] == 1) {
					$requirepassword = "<br>".$m['code'].": <input type=\"password\" name=\"".$mycours['k']."\">";
				} else {
					$requirepassword = "";
				}
				if ($mycours["visible"]>0  || isset ($myCourses[$mycours["k"]]["subscribed"])) {
					$retString .= "<input type='checkbox' name='selectCourse[]' value='$mycours[k]'>";
				}
			}
			$retString .= "<input type='hidden' name='changeCourse[]' value='$mycours[k]'>";
			$retString .= "</td>";
			$retString .= "\n      <td width=60%><b>$codelink</b> <small>(".$mycours['k'].")</small>$requirepassword </td>";
			$retString .= "\n      <td width=23%>$mycours[t]</td>";
			$retString .= "\n      <td align='center' width='7%'>";
			// show the necessary access icon
			foreach ($icons as $visible => $image) {
				if ($visible == $mycours['visible']) {
					$retString .= $image;
				}
			}
			$retString .= "</td>";
			$retString .= "\n    </tr>";
			$k++;
		} // END of while
		$retString .= "\n       </tbody>";
		$retString .= "\n       </table>";
		$retString .= "\n       </td>";
		$retString .= "\n    </tr>";
		$retString .= "\n    </table>\n";
	} // end of foreach

return $retString;
}

function collapsed_facultes_vert($fc) {

	global $langAvCourse, $langAvCourses;
	$retString = "";
/*
$result = mysql_query(
        "SELECT DISTINCT cours.faculte f, faculte.id id
                FROM cours, faculte
                WHERE faculte.id = cours.faculteid
                        AND faculte.id <> '$facid'
                ORDER BY cours.faculte");
*/

	$result = db_query(
		"SELECT DISTINCT cours.faculte f, faculte.id id
		FROM cours, faculte
		WHERE (cours.visible = '1' OR cours.visible = '2')
			AND faculte.name = cours.faculte
			AND faculte.id <> '$fc'
		ORDER BY cours.faculte");

	while ($fac = mysql_fetch_array($result)) {
		$retString .= "<a href=\"?fc=$fac[id]\" class=\"normal\">$fac[f]</a>";

		$n = db_query("SELECT COUNT(*) FROM cours
			WHERE cours.faculte='$fac[f]' AND cours.visible <> '0'");
                $r = mysql_fetch_array($n);
                $retString .= " <span style='font-size: 10pt'>($r[0] "
                        . ($r[0] == 1? $langAvLesson: $langAvCourses) . ")</span><br>\n";
	}
		$retString .= "<br>";

	return $retString;
}


function collapsed_facultes_horiz($fc) {

	global $langListFac, $langSelectFac;

	$retString = "\n   <form name='depform' action='$_SERVER[PHP_SELF]' method='get'>\n";

	$retString .= "\n  <div id=\"operations_container\">\n    <ul id=\"opslist\">";
	$retString .=  "\n    <li>$langSelectFac:&nbsp;";
	$retString .= dep_selection($fc);
	$retString .=  "\n    </li>";
	$retString .= "\n    </ul>\n  </div>\n";


  	$retString .= "\n    </form>";

return $retString;
}

// selection of department
function dep_selection($fc) {

	$string = "";
	$faculte_names = array();

	// get all the departments
	$result = db_query("SELECT id, name FROM faculte ORDER BY name");
	while ($facs = mysql_fetch_array($result)) {
		$faculte_names[$facs['id']] = $facs['name'];
	}

	$string .= selection($faculte_names, 'fc', $fc, 'onChange="document.depform.submit();"');

return $string;
}


// check if a course is restricted
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

<?php
/*
      +----------------------------------------------------------------------+
      | GUnet eClass 1.7                                                     |
      | Asychronous Teleteaching Platform                                    |
      +----------------------------------------------------------------------+
      | Copyright (c) 2003-2007  GUnet                                       |
      +----------------------------------------------------------------------+
      |                                                                      |
      | GUnet eClass 1.7 is an open platform distributed in the hope that    |
      | it will be useful (without any warranty), under the terms of the     |
      | GNU License (General Public License) as published by the Free        |
      | Software Foundation. The full license can be read in "license.txt".  |
      |                                                                      |
      | Main Developers Group: Costas Tsibanis <k.tsibanis@noc.uoa.gr>       |
      |                        Yannis Exidaridis <jexi@noc.uoa.gr>           |
      |                        Alexandros Diamantidis <adia@noc.uoa.gr>      |
      |                        Tilemachos Raptis <traptis@noc.uoa.gr>        |
      |                                                                      |
      | For a full list of contributors, see "credits.txt".                  |
      |                                                                      |
      +----------------------------------------------------------------------+
      | Contact address: Asynchronous Teleteaching Group (eclass@gunet.gr),  |
      |                  Network Operations Center, University of Athens,    |
      |                  Panepistimiopolis Ilissia, 15784, Athens, Greece    |
      +----------------------------------------------------------------------+
*/

$require_login = TRUE;
$langFiles = array('registration', 'opencours');
include '../../include/baseTheme.php';

$nameTools = $langOtherCourses;

$tool_content = "";

$icons = array(
2 => "<img src=\"../../template/classic/img/OpenCourse.gif\" alt=\"\">",
1 => "<img src=\"../../template/classic/img/Registration.gif\" alt=\"\">",
0 => "<img src=\"../../template/classic/img/ClosedCourse.gif\" alt=\"\">"
);

if (isset($_REQUEST['fc'])) {
        $_SESSION['fc_memo'] = $_REQUEST['fc'];
}

if (!isset($_REQUEST['fc']) && isset($_SESSION['fc_memo'])) {
        $fc = $_SESSION['fc_memo'];
}

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
                        if ($myrow['password']!="" && $myrow['password']!=$$contenu) {
                                $errorExists = true;
                        } else {
                                $sqlInsertCourse =
                                "INSERT INTO `cours_user`
                                                (`code_cours`, `user_id`, `statut`, `role`)
                                                VALUES ('".$contenu."', '".$uid."', '5', ' ')";
                                mysql_query($sqlInsertCourse) ;
                                if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()."<br>";
                        }

									/*
                        if(!is_restricted($contenu)) { //DUKE
                                $sqlInsertCourse = 
                                        "INSERT INTO `cours_user` 
                                        (`code_cours`, `user_id`, `statut`, `role`)
                                        VALUES ('".$contenu."', '".$uid."', '5', ' ')"; 
                                        db_query($sqlInsertCourse) ;
                                if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()."<br>";
                        } else { //DUKE
                                $restrictedCourses[$i]=$contenu;
                        } //DUKE */
                } 
        }
        $tool_content .= "<table width=96% height=363 border=0><tr><td valign=top>";
        $tool_content .= "<div class=alert1>$langIsReg</div><br><br><br><br>";
        if($restrictedCourses!=null) { //DUKE
                $tool_content .= "<div class=alert1>(Μη επιτρεπτή ενέργεια)</div><br><br><br><br>";
        } //DUKE
        $tool_content .= "<div align=right><a href=\"../../index.php\" class=mainpage>$langHome</a></div>";
        $tool_content .= "</td></tr></table>";
}
else
{
        $tool_content .= "</td></tr>";
        $tool_content .= "<tr><td valign=top height=355>";

        // check if user requested a specific faculte
        if (isset( $_GET['fc'] ) ) { 
                // get faculte name from db
                $fac = getfacfromfc( $_GET['fc'] );
        } else {
                // get faculte name from user's department column
                $fac = getfacfromuid($uid);
                //	echo $fac;
        }

        if (!$fac) {

                $tool_content .= "$langAddHereSomeCourses";

                $result=db_query("SELECT id, name, code FROM faculte ORDER BY name");
                $numrows = mysql_num_rows($result);
                if (isset($result))  {
                      $tool_content .= "
                         <script type=\"text/javascript\" src=\"sorttable.js\"></script>
                         <table width='90%' align=center class=\"sortable\" id=\"t1\" cellspacing='0' cellpadding='10' border='0' style=\"border: 1px solid $table_border\">
                       <tr>
                       <td class=td_small_HeaderRow height=25><b>$langFaculte</b></td>
                       </tr>";
                         
                       while ($fac = mysql_fetch_array($result)) {
                           $tool_content .= "<tr onMouseOver=\"this.style.backgroundColor='#F1F1F1'\" onMouseOut=\"this.style.backgroundColor='transparent'\">
                             <td class='kk' height=25>&nbsp;<img src='../../images/arrow_blue.gif'>&nbsp;<a href='courses.php?fc=$fac[id]' class='mainpage'>$fac[name]</a> &nbsp;<small>
															<font color=#4175B9>($fac[code])</font></small>";
                               $n=db_query("SELECT COUNT(*) FROM cours_faculte WHERE faculte='$fac[name]'");
                                $r=mysql_fetch_array($n);
                              $tool_content .= "<small><font color=#AAAAAA>($r[0]  "
                                                 . ($r[0] == 1? $avlesson: $avlessons) . ")</font><small>
                                  </td>
                                  </tr>\n";
                                }
                      $tool_content .= "</table>";

                }
                $tool_content .= "<br>\n";
                $tool_content .= "<br>\n";
        }
        else {
                // department exists

                $tool_content .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">";
                $formend = "<tr>
                        <td colspan=\"6\" ><br>&nbsp;&nbsp;
                <input type=\"submit\" name=\"submit\" value=\"$langSubscribe\"><br><br>
                        </td>
                        </tr>\n";

                $numofcourses = getdepnumcourses($fac);

                // display all the facultes collapsed
                $tool_content .= collapsed_facultes_horiz($fac);
                if ($numofcourses > 0) {
                        $tool_content .= expanded_faculte($fac, $uid);
                }

        } // end of else (department exists)

        if (isset($formend)) {
                $numofcourses = getdepnumcourses($fac);
                if ($numofcourses > 0) {
                        $tool_content .= $formend;
                } else {
                        if ($fac) {
                                $tool_content .= "<div class=alert1>$langNoCourses</div></td></tr></table>\n";
                        }
                }
        }
}

draw($tool_content,1,'admin');

// functions
function getfacfromfc( $dep_id) {
	$dep_id = intval( $dep_id);
	
	$fac = mysql_fetch_row(db_query("SELECT name FROM faculte WHERE id = '$dep_id'"));
	if (isset($fac[0]))
		return $fac[0];
	else
		return 0;
}

function getfacfromuid($uid) {
	$res = mysql_fetch_row(db_query("SELECT name FROM faculte,user
					WHERE user.user_id = '$uid' AND faculte.id = user.department"));
	if (isset($res[0]))
		return $res[0];
	else
		return 0;
}

function getdepnumcourses($fac) {
	$res = mysql_fetch_row(db_query(
	"SELECT count(code) 
	FROM cours_faculte
	WHERE faculte='$fac'" ));
	return $res[0];
}

function expanded_faculte($fac, $uid) {
	global $m, $icons, $langTitular, $langBegin, $mysqlMainDb, $table_border;

	$retString = "";

	// build a list of  course follow  by  user.
	$sqlListOfCoursesOfUser = "
	SELECT code_cours cc, statut ss
		FROM `$mysqlMainDb`.cours_user
		WHERE user_id = ".$uid;
	
	$listOfCoursesOfUser = db_query($sqlListOfCoursesOfUser);
	
	// build array of user's courses
	while ($rowMyCourses = mysql_fetch_array($listOfCoursesOfUser)) {
	 	$myCourses[$rowMyCourses["cc"]]["subscribed"]= TRUE; 
	 	$myCourses[$rowMyCourses["cc"]]["statut"]= $rowMyCourses["ss"]; 
	}
	
	$retString .= "</td></tr><tr><td valign=top height=1 class=kk style=\"border: 1px dotted $table_border\">
					<a name=\"top\">$m[department]:</a> <b><em>$fac</em></b>&nbsp;&nbsp;\n";
	
	// get the different course types available for this faculte
		$typesresult = db_query(
		"SELECT DISTINCT cours.type types 
				FROM cours WHERE cours.faculte = '$fac' 
				AND cours.visible <> 0 
			ORDER BY cours.type");
		
		// count the number of different types
		$numoftypes = mysql_num_rows($typesresult);

		// output the nav bar only if we have more than 1 types of courses
		if ($numoftypes > 1) {
         $retString .= "<span style=\"float: right\">";
			$counter = 1;
			while ($typesArray = mysql_fetch_array($typesresult)) {
				$t = $typesArray['types'];
				// make the plural version of type (eg pres, posts, etc)
				// this is for fetching the proper translations
				// just concatenate the s char in the end of the string
				$ts = $t."s";
				//type the seperator in front of the types except the 1st
				if ($counter != 1) $retString .= " | ";
				$retString .= "<a href=\"#".$t."\" class=\"sortheader\">".$m["$ts"]."</a>";
				$counter++;
			}
			$retString .= "</span></td><tr><tr><td><br>";
		}
                else
                {
                        $retString .= "<div class='courses' align=right>";
                        $retString .= "&nbsp;";
                        $retString .= "</div></td></tr><tr><td height=1>&nbsp;</td></tr>";
                }
		
		// changed this foreach statement a bit
				// this way we sort by the course types
				// then we just select visible
				// and finally we do the secondary sort by course title and but teacher's name
				foreach (array("pre" => $m['pres'],
				               "post" => $m['posts'],
				               "other" => $m['others']) as $type => $message) {
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
                		AND cours_faculte.faculte='$fac'
						AND cours.visible <> '0'
		                ORDER BY cours.intitule, cours.titulaires");
					
					if (mysql_num_rows($result) == 0) {
						continue;
					}
					
					// We changed the style a bit here and we output types as the title
					$retString .= "<tr><td class=kk height=20 valign=top style=\"border: 1px solid #DCDCDC;\">
                            <table width=100% cellpading=0 cellspacing=1>
                            <tr><td colspan=4 class=td_small_HeaderRow style=\"border: 1px solid #DCDCDC;\">
                            <table width=100% border=0 cellpading=0 cellspacing=0>
                            <tr><td class=td_small_HeaderRow><a name=\"$type\" class='alert1'>$message</a></td>";
                if ($numoftypes > 1) {
                          $retString .= "<td align=right class=td_small_HeaderRow>
													<a href=\"#top\" class=sortheader>".$langBegin."</a></td>";
                            }
                         $retString .= "</tr></table>";
                         $retString .= "</td><tr><tr><td>";

			// legend
              $retString .= "<tr>\n";
              $retString .= "<td class='color1' align='center' width='10%' style=\"border: 1px solid #DCDCDC;\"><b>Εγγραφή</b></td>";
              $retString .= "<td class='color1' align='left' width='60%' style=\"border: 1px solid #DCDCDC;\"><b>Μάθημα (κωδικός)</b></td>";
              $retString .= "<td class='color1' align='left' width='23%' style=\"border: 1px solid #DCDCDC;\"><b>Καθηγητής</b></td>";
              $retString .= "<td class='color1' align='center' width='7%' style=\"border: 1px solid #DCDCDC;\"><b>Τύπος</b></td>";
              $retString .= "</tr>\n";
              $retString .= "</table>\n";
					while ($mycours = mysql_fetch_array($result)) {
					// changed the variable because of the previous change in the select argument
						if ($mycours['visible'] == 2) {
							$codelink = "<a href='../../courses/$mycours[k]/' target=_blank class='CourseLink'>$mycours[i]</a>";
						} else {
							$codelink = $mycours['i'];
						}
	
						// output each course as a table for beautifying reasons
						$retString .= "\n\n";
						$retString .= "<table border='0 'width=100% align=center cellspacing='1' cellpadding='0'>\n";
            $retString .= "<tr onMouseOver=\"this.style.backgroundColor='#F1F1F1'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
					  $retString .= "<td class='kkk' align='center' width='10%'>";
						
//----- needed ?????????????
/*
					 if ($mycours["visible"]==0 && !isset ($myCourses[$mycours["k"]]["subscribed"])) {
							        $contactprof = $m['mailprof']."<a href=\"contactprof.php?fc=".$facid."&cc=".$mycours['k']."\">".$m['here']."</a>";
						        $retString .= $codelink;
			      } else {
						        $retString .= $codelink;
					      }

				      if ($mycours["visible"]>0 && 
											(isset ($myCourses[$mycours["k"]]["subscribed"]) 
											|| !isset ($myCourses[$mycours["k"]]["subscribed"]))) {
									        $retString .= "<input type='hidden' name='changeCourse[]' value='$mycours[k]'>\n";
									        @$retString .= "<td>".$mycours['t']."</td>";
					      } elseif ($mycours["visible"]== 0 && isset ($myCourses[$mycours["k"]]["subscribed"])) {
								        $retString .= "<td>".$mycours['t']."</td>";
					      } else {
					        $retString .= "<td>$mycours[t]</td><td>".$contactprof."</td>";
								}
*/
// ---------------------------------------

						if (isset ($myCourses[$mycours["k"]]["subscribed"])) { 
							if ($myCourses[$mycours["k"]]["statut"]!=1) {
										// password needed
										if ($mycours['p']!="" && $mycours['visible'] == 1) {
							            $requirepassword = $m['code'].": 
													<input type=\"password\" name=\"".$mycours['k']."\" value=\"".$mycours['p']."\">";
										} else {
					            $requirepassword = "";
          					}
				
								$retString .= "<input type='checkbox' name='selectCourse[]' value='$mycours[k]' checked >";
								} else {
                	$retString .= "<img src=../../images/teacher.gif title=$langTitular>";
								}
						} else {

									if ($mycours['p']!="" && $mycours['visible'] == 1) {
						          $requirepassword = $m['code'].": <input type=\"password\" name=\"".$mycours['k']."\">";
					        } else {
						          $requirepassword = "";
					        }
    			    if ($mycours["visible"]>0  || isset ($myCourses[$mycours["k"]]["subscribed"])) {
		      		    $retString .= "<td>
									<input type='checkbox' name='selectCourse[]' value='$mycours[k]'> $requirepassword</td>";
       				 }
 
//							$retString .= "<input type='checkbox' name='selectCourse[]' value='$mycours[k]'>";
						}

						$retString .= "<input type='hidden' name='changeCourse[]' value='$mycours[k]'>";
						$retString .= "</td>\n";
						$retString .= "<td class='kkk'  valign='top' width=60%><b>$codelink</b> <font color=#4175B9>(".$mycours['k'].")</font> </td>\n";
					  $retString .= "<td class=kkk width=23%><span class='explanationtext'>$mycours[t]</span></td>\n";	
						$retString .= "<td class='kkk' valign='top' align='center' width='7%'>";
            // show the necessary access icon
            foreach ($icons as $visible => $image) {
              if ($visible == $mycours['visible']) {
                $retString .= $image;
              }
            }
            $retString .= "</td>\n\n";
		
					$retString .= "</tr>\n";
					$retString .= "</table>\n";

					}
          $retString .= "</td><tr><tr><td><br>";
					
					// that's it!
					// upatras.gr patch end
				}
				
      $retString .= "</td>\n";
      $retString .= "</tr>\n";
      $retString .= "</table>\n";
			$retString .= "</div>";

return $retString;
}

function collapsed_facultes_vert($fac) {
	
	global $avlesson, $avlessons;
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
			AND faculte.name <> '$fac'
		ORDER BY cours.faculte");
	
	while ($fac = mysql_fetch_array($result)) {
		$retString .= "<blockquote>";
		$retString .= "<a href=\"?fc=$fac[id]\" class=\"normal\">$fac[f]</a>";
		
		$n = db_query("SELECT COUNT(*) FROM cours
			WHERE cours.faculte='$fac[f]' AND cours.visible <> '0'");
                $r = mysql_fetch_array($n);
                $retString .= " <span style='font-size: 10pt'>($r[0] "
                        . ($r[0] == 1? $avlesson: $avlessons) . ")</span><br>\n";
		$retString .= "</blockquote>";
	}
		$retString .= "<br>";

	return $retString;
}

function collapsed_facultes_horiz($fac) {

global $listfac;
$retString = "";

//start_toolbar();

	$retString .= "</td><td class=tool_bar align=left width=20%>
						<span class=\"small\"><b>$listfac:</b></span></td><td class=tool_bar align=right width=80%>\n";

$result = db_query("SELECT DISTINCT faculte.id id, faculte.name f
                FROM faculte
                ORDER BY name");

/*	$result = db_query(
		"SELECT DISTINCT cours.faculte f, faculte.id id
		FROM cours, faculte 
		WHERE (cours.visible = '1' OR cours.visible = '2') 
			AND faculte.name = cours.faculte
		ORDER BY cours.faculte"); */
	$counter = 1;
	while ($facs = mysql_fetch_array($result)) {
		if ($counter != 1) $retString .= "<font class=\"small\"> | </font>";
		if ($facs['f'] != $fac)
			$codelink = "<a href=\"?fc=$facs[id]\" class=\"small\">$facs[f]</a>"; 
		else
			$codelink = "<font class=\"small\">$facs[f]</font>";

		$retString .= $codelink;
		$counter++;
	}
  //      end_toolbar();
               // o pinakas autos stoixizei tin kartela

    $retString .= "<table border='0' height=283 width=96% align=center cellspacing='1' cellpadding='0'>\n";
    $retString .= "<tr>\n";
    $retString .= "<td valign=top height=1 class=kk>\n";

return $retString;
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

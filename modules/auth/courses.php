<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$require_login = TRUE;
include '../../include/baseTheme.php';
require_once 'hierarchy.inc.php';

$TBL_HIERARCHY = 'hierarchy';

require_once('../../include/lib/hierarchy.class.php');

$tree = new hierarchy();

$nameTools = $langChoiceLesson;
$navigation[] = array ("url"=>"courses.php", "name"=> $langChoiceDepartment);

$icons = array(
        2 => "<img src='$themeimg/lock_open.png' alt='" . $m['legopen'] . "' title='" . $m['legopen'] . "' />",
        1 => "<img src='$themeimg/lock_registration.png' alt='" . $m['legrestricted'] . "' title='" . $m['legrestricted'] . "' />",
        0 => "<img src='$themeimg/lock_closed.png' alt='" . $m['legclosed'] . "' title='" . $m['legclosed'] . "' />"
);

if (isset($_REQUEST['fc'])) {
        $fc = intval($_REQUEST['fc']);
} elseif (isset($_SESSION['fc_memo'])) {
        $fc = $_SESSION['fc_memo'];
} else {
        $fc = getfcfromuid($uid);
}
$_SESSION['fc_memo'] = $fc;

$restrictedCourses = array();
if (isset($_POST['changeCourse']) and is_array($_POST['changeCourse'])) {
        $changeCourse = $_POST['changeCourse'];
} else {
        $changeCourse = array();
}
if (isset($_POST['selectCourse']) and is_array($_POST['selectCourse'])) {
        $selectCourse = $_POST['selectCourse'];
} else {
        $selectCourse = array();
}

if (isset($_POST["submit"])) {
        foreach ($changeCourse as $key => $value) {
                $cid = intval($value);
                if (!in_array($cid, $selectCourse)) {
			db_query("DELETE FROM cours_user
					WHERE statut <> 1 AND statut <> 10
					AND user_id = $uid AND cours_id = $cid");
                }
        }

	$errorExists = false;
        foreach ($selectCourse as $key => $value) {
                $cid = intval($value);
                $course_info = db_query("SELECT fake_code, password, visible FROM cours WHERE cours_id = $cid");
                if ($course_info) {
                        $row = mysql_fetch_array($course_info);
                        if ($row['visible'] == 1 and !empty($row['password']) and
                            $row['password'] != autounquote($_POST['pass' . $cid])) {
                                $errorExists = true;
                                $restrictedCourses[] = $row['fake_code'];
                                continue;
                        }
                        if (is_restricted($cid) and !in_array($cid, $selectCourse)) { // do not allow registration to restricted course
                                $errorExists = true;
                                $restrictedCourses[] = $row['fake_code'];
                        } else {
                                db_query("INSERT IGNORE INTO `cours_user` (`cours_id`, `user_id`, `statut`, `reg_date`)
                                                 VALUES ($cid, $uid, 5, CURDATE())");
                        }
                }
        }

	if ($errorExists) {
                $tool_content .= "<p class='caution'>$langWrongPassCourse " .
                                 q(join(', ', $restrictedCourses)) . "</p><br />";
        } else {
                $tool_content .= "<p class='success'>$langRegDone</p>";
        }
        $tool_content .= "<div><a href='../../index.php'>$langHome</a></div>";

} else {
        $fac = getfacfromfc($fc);
	if (!$fac) { // if user does not belong to department
		$tool_content .= "<p align='justify'>$langAddHereSomeCourses</p>";
		
                $query = "SELECT node.id, node.lft AS lft, node.code as code, node.name,
                                 COUNT(parent.id) - 1 AS depth
                            FROM ". $TBL_HIERARCHY ." AS node, ". $TBL_HIERARCHY ." AS parent 
                           WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                             AND node.allow_course = true
                        GROUP BY node.id 
                        ORDER BY node.lft";
                $result = db_query($query);
                
                $query = "SELECT max(depth) FROM (SELECT COUNT(parent.id) - 1 AS depth
                            FROM $TBL_HIERARCHY AS node, $TBL_HIERARCHY AS parent 
                           WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                        GROUP BY node.id 
                        ORDER BY node.lft) AS hierarchydepth";
                $maxdepth = mysql_fetch_array(db_query($query));

		if (isset($result))  {
			$tool_content .= "<table width='100%' class='tbl_alt' id='t1'>";
			$k = 0;
			while ($fac = mysql_fetch_array($result)) {
				if ($k%2==0) {
					$tool_content .= "<tr class='even'>";
				} else {
					$tool_content .= "<tr class='odd'>";
				}
                                
                                $tool_content .= "<th width='16'><img src='$themeimg/arrow.png' /></th>";
                                
                                for ($i = 1; $i <= $fac['depth']-1; $i++) // extra -1 because we do not display root
                                    $tool_content .= "<td width='5'>&nbsp;</td>";
                                $colspan = $maxdepth[0] - $fac['depth'] + 1;
                                
				$tool_content .= "<td colspan='$colspan'>
				<a href='$_SERVER[PHP_SELF]?fc=$fac[id]'>" . htmlspecialchars(hierarchy::unserializeLangField($fac['name'])) . "</a>&nbsp;
				<span class='smaller'>($fac[code])</span>";
				$n = db_query("SELECT COUNT(*) FROM cours, course_department
					WHERE cours.cours_id = course_department.course
                                          AND course_department.department = $fac[id] 
                                          AND (cours.visible = '1' OR cours.visible = '2')");
				$r = mysql_fetch_array($n);
				$tool_content .= " 
                                <span class='smaller'>&nbsp;($r[0]  ". ($r[0] == 1? $langAvCours: $langAvCourses) . ") </span>
                                </td>
                                </tr>";
                                $k++;
			}
			$tool_content .= "</table>";
		}
		$tool_content .= "<br /><br />\n";
	} else {
		// department exists
		$numofcourses = getdepnumcourses($fc);
		// display all the facultes collapsed
		$tool_content .= collapsed_facultes_horiz($fc);
		$tool_content .= "\n    <form action='$_SERVER[PHP_SELF]' method='post'>";
		if ($numofcourses > 0) {
			$tool_content .= expanded_faculte($fac, $fc, $uid);
			$tool_content .= "<br />
				<div align='right'><input class='Login' type='submit' name='submit' value='$langRegistration' />&nbsp;&nbsp;</div>
				</form>";
		} else {
			if ($fac) {
				$tool_content .= "<table width='100%' class='tbl_border'>
				<tr>
				<th><a name='top'></a><b>$langFaculty:</b> ". $tree->getFullPath($fc) ."</th>
				</tr></table><br />";
                                
                                $tool_content .= departmentChildren($fc, 'courses');
                                
				$tool_content .= "<br />
				<div class=alert1>$langNoCoursesAvailable</div>\n";
			}
		}
	} // end of else (department exists)
}

draw($tool_content, 1);


function getfacfromfc($dep_id) {
	$dep_id = intval( $dep_id);

	$fac = mysql_fetch_row(db_query("SELECT name FROM hierarchy WHERE allow_course = true AND id = '$dep_id'"));
	if (isset($fac[0]))
		return $fac[0];
	else
		return 0;
}

function getfcfromuid($uid) {
	$res = mysql_fetch_row(db_query("SELECT department FROM user_department WHERE user = '$uid'"));
	if (isset($res[0])) {
		return $res[0];
	}
	else {
		return 0;
	}
}

function getdepnumcourses($fac) {
	$res = mysql_fetch_row(db_query("SELECT count(code) FROM cours, course_department 
                WHERE cours.cours_id = course_department.course AND course_department.department = $fac"));
	return $res[0];
}

function expanded_faculte($fac_name, $facid, $uid) {
    global $m, $icons, $langTutor, $langBegin, $langRegistration, $mysqlMainDb,
           $langRegistration, $langCourseCode, $langTeacher, $langType, $langFaculty,
           $langpres, $langposts, $langothers, $themeimg, $tree;

    $retString = "";

    // build a list of course followed by user.
    $usercourses = db_query("SELECT cours.code code_cours, cours.fake_code fake_code,
                                    cours.cours_id cours_id, statut
                                FROM cours_user, cours
                                WHERE cours_user.cours_id = cours.cours_id                                  
                                AND user_id = ".$uid);

    while ($row = mysql_fetch_array($usercourses)) {
        $myCourses[$row['cours_id']] = $row;
    }

    $retString .= "<table width='100%' class='tbl_border'>
                   <tr>
                   <th><a name='top'> </a>$langFaculty: <b>". $tree->getFullPath($facid) ."</b></th></tr></table><br/>";
    
    $retString .= departmentChildren($facid, 'courses');


    $result = db_query("SELECT
                            cours.cours_id cid,
                            cours.code k,
                            cours.fake_code fake_code,
                            cours.intitule i,
                            cours.visible visible,
                            cours.titulaires t,
                            cours.password password
                       FROM cours, course_department
                      WHERE cours.cours_id = course_department.course
                        AND course_department.department = $facid 
                        AND cours.visible != ".COURSE_INACTIVE."
                   ORDER BY cours.intitule, cours.titulaires");

    $retString .= "\n    <table class='tbl_alt' width='100%'>";
    $retString .= "\n    <tr>";
    $retString .= "\n      <th width='50' align='center'>$langRegistration</th>";
    $retString .= "\n      <th>$langCourseCode</th>";
    $retString .= "\n      <th width='220'>$langTeacher</th>";
    $retString .= "\n      <th width='30' align='center'>$langType</th>";
    $retString .= "\n    </tr>";
                
    $k=0;
    while ($mycours = mysql_fetch_array($result)) {
        $cid = $mycours['cid'];
        $course_title = q($mycours['i']);
        $password = q($mycours['password']);
        
        // link creation
        if ($mycours['visible'] == COURSE_OPEN or $uid == COURSE_REGISTRATION) { //open course
            $codelink = "<a href='../../courses/$mycours[k]/'>$course_title</a>";
        } elseif ($mycours['visible'] == COURSE_CLOSED) { //closed course
            $codelink = "<a href='../contact/index.php?from_reg=true&cours_id=$cid'>$course_title</a>";
        } else {
            $codelink = $course_title;
        }
        
        
        if ($k%2 == 0) {
            $retString .= "\n    <tr class='even'>";
        } else {
            $retString .= "\n    <tr class='odd'>";
        }
        
        $retString .= "<td align='center'>";
        $requirepassword = '';
        
        if (isset($myCourses[$cid])) {
            if ($myCourses[$cid]['statut'] != 1) { // display registered courses
                // password needed
                if (!empty($password) and $mycours['visible'] == 1) {
                    $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' value='". q($password) ."' />";
                } else {
                    $requirepassword = '';
                }
                $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' checked='checked' />";
                if ($mycours['visible'] == 0) {
                    $codelink = "<a href='../../courses/$mycours[k]/'>$course_title</a>";
                }
            } else {
                $retString .= "<img src='$themeimg/teacher.png' alt='$langTutor' title='$langTutor' />";
            }
        } else { // display unregistered courses
            if (!empty($password) and $mycours['visible'] == 1) {
                $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' />";
            } else {
                $requirepassword = '';
            }
            
            if ($mycours['visible'] == 0) {
                $retString .= "<input type='checkbox' disabled />";
            }
            
            if (($mycours['visible'] == 1) or ($mycours['visible'] == 2)) {
                $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' />";
            }
        }
        
        $retString .= "<input type='hidden' name='changeCourse[]' value='$cid' />";
        $retString .= "</td>";
        $retString .= "\n      <td>$codelink (" . q($mycours['fake_code']) .")$requirepassword</td>";
        $retString .= "\n      <td>". q($mycours['t']) ."</td>";
        $retString .= "\n      <td align='center'>";
        
        // show the necessary access icon
        foreach ($icons as $visible => $image) {
            if ($visible == $mycours['visible']) {
                $retString .= $image;
            }
        }
        
        $retString .= "</td></tr>";
        $k++;
    } // END of while
    $retString .= "</table>";

    return $retString;
}

function expanded_faculte_old($fac_name, $facid, $uid) {
	global $m, $icons, $langTutor, $langBegin, $langRegistration, $mysqlMainDb,
               $langRegistration, $langCourseCode, $langTeacher, $langType, $langFaculty,
               $langpres, $langposts, $langothers, $themeimg, $tree;

	$retString = "";

	// build a list of course followed by user.
	$usercourses = db_query("SELECT cours.code code_cours, cours.fake_code fake_code,
                                        cours.cours_id cours_id, statut
                                 FROM cours_user, cours
                                 WHERE cours_user.cours_id = cours.cours_id                                  
                                 AND user_id = ".$uid);
	while ($row = mysql_fetch_array($usercourses)) {
	 	$myCourses[$row['cours_id']] = $row;
	}

	$retString .= "
           <table width='100%' class='tbl_border'>
           <tr>
             <th><a name='top'> </a>$langFaculty: <b>". $tree->getFullPath($facid) ."</b></th>";

	// get the different course types available for this faculte
        $typessql = "SELECT DISTINCT course_type.name as type 
                        FROM cours, course_department, course_is_type, course_type
                        WHERE cours.cours_id = course_department.course
                          AND cours.cours_id = course_is_type.course
                          AND course_type.id = course_is_type.course_type
                          AND course_department.department = $facid 
                        ORDER BY course_type.id";
	$typesresult = db_query($typessql);

	// count the number of different types
	$numoftypes = mysql_num_rows($typesresult);
	// output the nav bar only if we have more than 1 type of course
	if ($numoftypes > 1) {
		$retString .= "
             <th><div align='right'>";
		$counter = 1;
		while ($typesArray = mysql_fetch_array($typesresult)) {
			$t = $typesArray['type'];
                        $containslang = (substr($t, 0, strlen("lang")) === "lang") ? true : false;
			// make the plural version of type (eg pres, posts, etc)
			// this is for fetching the proper translations
			// just concatenate the s char in the end of the string
                        if ($containslang) {
                            $ts = $t . "s";
                            $t = substr($t, strlen("lang"), strlen($t));
                        }
			//type the seperator in front of the types except the 1st
			if ($counter != 1) $retString .= " | ";
                        if ($containslang)
                            $retString .= "<a href=\"#".$t."\">". ${$ts} ."</a>";
                        else
                            $retString .= "<a href=\"#".$t."\">". $t ."</a>";
                        $counter++;
                }
		$retString .= "</div></th></tr></table>\n\n";
	} else {
		$retString .= "\n</table>\n";
	}

	  // changed this foreach statement a bit
	  // this way we sort by the course types
	  // then we just select visible
	  // and finally we do the secondary sort by course title and but teacher's name
        $typesresult = db_query($typessql);
        while ($typesArray = mysql_fetch_array($typesresult)) {
                $t = $typesArray['type'];
                $containslang = (substr($t, 0, strlen("lang")) === "lang") ? true : false;
                if ($containslang) {
                    $ts = $t . "s";
                    $t = substr($t, strlen("lang"), strlen($t));
                }
                $result = db_query("SELECT
                                        cours.cours_id cid,
                                        cours.code k,
                                        cours.fake_code fake_code,
                                        cours.intitule i,
                                        cours.visible visible,
                                        cours.titulaires t,
                                        cours.password password
                                  FROM cours, course_department, course_is_type, course_type
                                  WHERE cours.cours_id = course_department.course
                                    AND cours.cours_id = course_is_type.course
                                    AND course_type.id = course_is_type.course_type
                                    AND course_department.department = $facid 
                                    AND course_type.name = '".$typesArray['type']."'
                                    AND cours.visible != ".COURSE_INACTIVE."
                                  ORDER BY cours.intitule, cours.titulaires");

                if ($numoftypes > 1) {
                        $retString .= "\n    <table width='100%' class='tbl_course_type'>";
                        $retString .= "\n    <tr>";
                        if ($containslang)
                        $retString .= "\n      <td><a name='$t'></a><b>${$ts}</b></td>";
                        else
                        $retString .= "\n      <td><a name='$t'></a><b>$t</b></td>";
                        $retString .= "\n      <td align='right'><a href='#top'>$langBegin</a>&nbsp;</td>";
                        $retString .= "\n    </tr>";
                        $retString .= "\n    </table>\n";
                } else {
                        $retString .= "\n    <br />";
                        $retString .= "\n    <table width='100%' class='tbl_course_type'>";
                        $retString .= "\n    <tr>";
                        if ($containslang)
                        $retString .= "\n      <td><a name='$t'></a><b>${$ts}</b></td>";
                        else
                        $retString .= "\n      <td><a name='$t'></a><b>$t</b></td>";
                        $retString .= "\n      <td>&nbsp;</td>";
                        $retString .= "\n    </tr>";
                        $retString .= "\n    </table>\n\n";
                }

                // legend
                $retString .= "\n    <script type='text/javascript' src='sorttable.js'></script>";
                $retString .= "\n    <table class='sortable' id='t1$t' width='100%'>";
                $retString .= "\n    <tr>";
                $retString .= "\n      <th width='50' align='center'>$langRegistration</th>";
                $retString .= "\n      <th>$langCourseCode</th>";
                $retString .= "\n      <th width='220'>$langTeacher</th>";
                $retString .= "\n      <th width='30' align='center'>$langType</th>";
                $retString .= "\n    </tr>";
                $k=0;
                while ($mycours = mysql_fetch_array($result)) {
                        $cid = $mycours['cid'];
                        $course_title = q($mycours['i']);
                        $password = q($mycours['password']);
			// link creation
                        if ($mycours['visible'] == COURSE_OPEN or $uid == COURSE_REGISTRATION) { //open course
                                $codelink = "<a href='../../courses/$mycours[k]/'>$course_title</a>";
                        } elseif ($mycours['visible'] == COURSE_CLOSED) { //closed course
                                $codelink = "<a href='../contact/index.php?from_reg=true&cours_id=$cid'>$course_title</a>";
                        } else {
                                $codelink = $course_title;
                        }
			// end of link creation
                        if ($k%2 == 0) {
                                $retString .= "\n    <tr class='even'>";
                        } else {
                                $retString .= "\n    <tr class='odd'>";
                        }
                        $retString .= "<td align='center'>";
                        $requirepassword = '';
                        if (isset($myCourses[$cid])) {
                                if ($myCourses[$cid]['statut'] != 1) { // display registered courses
                                        // password needed
                                        if (!empty($password) and $mycours['visible'] == 1) {
                                                $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' value='".
                                                        q($password)."' />";
                                        } else {
                                                $requirepassword = '';
                                        }
                                        $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' checked='checked' />";
					if ($mycours['visible'] == 0) {
						$codelink = "<a href='../../courses/$mycours[k]/'>$course_title</a>";
					}
                                } else {
                                        $retString .= "<img src='$themeimg/teacher.png' alt='$langTutor' title='$langTutor' />";
                                }
                        } else { // display unregistered courses
                                if (!empty($password) and $mycours['visible'] == 1) {
                                        $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' />";
                                } else {
                                        $requirepassword = '';
                                }
				if ($mycours['visible'] == 0) {
					$retString .= "<input type='checkbox' disabled />";
				}
                                if (($mycours['visible'] == 1) or ($mycours['visible'] == 2)) {
                                        $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' />";
                                }
                        }
                        $retString .= "<input type='hidden' name='changeCourse[]' value='$cid' />";
                        $retString .= "</td>";
                        $retString .= "\n      <td>$codelink (" . q($mycours['fake_code']) .
                                ")$requirepassword</td>";
                        $retString .= "\n      <td>" . q($mycours['t']) . "</td>";
                        $retString .= "\n      <td align='center'>";
                        // show the necessary access icon
                        foreach ($icons as $visible => $image) {
                                if ($visible == $mycours['visible']) {
                                        $retString .= $image;
                                }
                        }
                        $retString .= "</td></tr>";
                        $k++;
                } // END of while
                $retString .= "</table>";
        } // end of foreach
        return $retString;
}


function collapsed_facultes_horiz($fc) {

	global $langSelectFac, $tree;
        
	$retString = "\n   <form name='depform' action='$_SERVER[PHP_SELF]' method='get'>\n";
	$retString .= "\n  <div id='operations_container'>\n    <ul id='opslist'>";
	$retString .=  "\n    <li>$langSelectFac:&nbsp;";
        $retString .= $tree->buildHtmlSelect('name="fc" onChange="document.depform.submit();"', $fc, null, array(), "id", "AND node.allow_course = true");
	$retString .=  "\n    </li>";
	$retString .= "\n    </ul>\n  </div>\n";
  	$retString .= "\n    </form>";

        return $retString;
}

// check if a course is restricted
function is_restricted($cours_id)
{
	$res = mysql_fetch_row(db_query("SELECT visible FROM cours WHERE cours_id = $cours_id"));
	if ($res[0] == 0) {
		return true;
	} else {
		return false;
	}
}

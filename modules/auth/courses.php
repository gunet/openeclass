<?php
/* ========================================================================
 * Open eClass 2.9
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

load_js('jquery');
load_js('jquery-ui');
load_js('tools.js');

$nameTools = $langChoiceLesson;
$navigation[] = array ('url' => 'courses.php', 'name' => $langChoiceDepartment);

$icons = array(
        COURSE_OPEN => "<img src='$themeimg/lock_open.png' alt='" . $m['legopen'] . "' title='" . $m['legopen'] . "' />",
        COURSE_REGISTRATION => "<img src='$themeimg/lock_registration.png' alt='" . $m['legrestricted'] . "' title='" . $m['legrestricted'] . "' />",
        COURSE_CLOSED => "<img src='$themeimg/lock_closed.png' alt='" . $m['legclosed'] . "' title='" . $m['legclosed'] . "' />"
);

if (isset($_REQUEST['fc'])) {
        $fc = intval($_REQUEST['fc']);
} elseif (isset($_SESSION['fc_memo'])) {
        $fc = $_SESSION['fc_memo'];
} else {
        $fc = getfcfromuid($uid);
}
$_SESSION['fc_memo'] = $fc;

$courses_list = array();
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

if (isset($_POST['submit'])) {
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
                        if (($row['visible'] == 1 or $row['visible'] == 2) 
                                and !empty($row['password']) and $row['password'] !== autounquote($_POST['pass' . $cid])) {
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
		$result=db_query("SELECT id, name, code FROM faculte ORDER BY name");
		$numrows = mysql_num_rows($result);
		if (isset($result))  {
			$tool_content .= "
			<script type='text/javascript' src='sorttable.js'></script>
			<table width='100%' class='sortable' id='t1'>
			  <tr>
                            <th class='left'>$langFaculty</th>
                          </tr>";
			$k = 0;
			while ($fac = mysql_fetch_array($result)) {
                            if ($k%2==0) {
                                    $tool_content .= "<tr class='even'>";
                            } else {
                                    $tool_content .= "<tr class='odd'>";
                            }
                            $tool_content .= "<td>&nbsp;<img src='$themeimg/arrow.png' />&nbsp;
                            <a href='$_SERVER[SCRIPT_NAME]?fc=$fac[id]'>" . q($fac['name']) . "</a>&nbsp;
                            <span class='smaller'>($fac[code])</span>";
                            $n = db_query("SELECT COUNT(*) FROM cours
                                    WHERE faculteid = $fac[id] AND visible != ".COURSE_INACTIVE."");
                            $r = mysql_fetch_array($n);
                            $tool_content .= "<span class='smaller'>&nbsp;($r[0]  ". ($r[0] == 1? $langAvCours: $langAvCourses) . ") </span>
                            </td>
                            </tr>";
                            $k++;
			}
			$tool_content .= "</table>";
		}
		$tool_content .= "<br /><br />";
	} else {            
		// department exists
		$numofcourses = getdepnumcourses($fc);
		// display all the facultes collapsed
		$tool_content .= collapsed_facultes_horiz($fc);
		$tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>";
		if ($numofcourses > 0) {
			$tool_content .= expanded_faculte($fac, $fc, $uid);
			$tool_content .= "<br />
				<div align='right'>
                                <input class='login' type='submit' name='submit' value='".q($langRegistration)."' />&nbsp;&nbsp;</div>";
		} else {                    
			if ($fac) {
				$tool_content .= "<table width='100%' class='tbl_border'>
				<tr>
				<th><a name='top'></a><b>$langFaculty:</b> ".q($fac)."</th>
				</tr></table>";
				$tool_content .= "<br /><br />
				<div class=alert1>$langNoCoursesAvailable</div>\n";
			}
		}
		$tool_content .= "</form>";
	} // end of else (department exists)
}
$tool_content .= "<script type='text/javascript'>$(course_list_init);
var themeimg = '".js_escape($themeimg)."';
var urlAppend = '".js_escape($urlAppend)."';
var lang = {
        unCourse: '".js_escape($langUnCourse)."',
        cancel: '".js_escape($langCancel)."',
        close: '".js_escape($langClose)."',
        unregCourse: '".js_escape($langUnregCourse)."',
        reregisterImpossible: '".js_escape($langReregisterImpossible)."',
        invalidCode: '".js_escape($langInvalidCode)."',
};
var courses = ".(json_encode($courses_list)).";</script>";

$head_content .= "<link href='../../js/jquery-ui.css' rel='stylesheet' type='text/css'>";

draw($tool_content, 1, null, $head_content);


function getfacfromfc($dep_id) {
	$dep_id = intval($dep_id);

	$fac = mysql_fetch_row(db_query("SELECT name FROM faculte WHERE id = $dep_id"));
	if (isset($fac[0]))
		return $fac[0];
	else
		return 0;
}

function getfcfromuid($uid) {
	$res = mysql_fetch_row(db_query("SELECT department FROM user WHERE user_id = $uid"));
	if (isset($res[0])) {
		return $res[0];
	}
	else {
		return 0;
	}
}

function getdepnumcourses($fac) {        
        $fac = intval($fac);
        
	$res = mysql_fetch_row(db_query("SELECT COUNT(code) FROM cours WHERE faculteid = $fac"));
	return $res[0];
}

function expanded_faculte($fac_name, $facid, $uid) {
	global $m, $icons, $langTutor, $langBegin, $langRegistration, $mysqlMainDb,
               $langRegistration, $langCourseCode, $langTeacher, $langType, $langFaculty,
               $langpres, $langposts, $langothers, $themeimg, $courses_list, $urlAppend,
               $is_power_user;

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
                    <th><a name='top'> </a>$langFaculty: <b>".q($fac_name)."</b></th>";

	// get the different course types available for this faculte
	$typesresult = db_query("SELECT DISTINCT type FROM cours
                                 WHERE cours.faculteid = $facid ORDER BY cours.type");

	// count the number of different types
	$numoftypes = mysql_num_rows($typesresult);
	// output the nav bar only if we have more than 1 type of course
	if ($numoftypes > 1) {
		$retString .= "
             <th><div align='right'>";
		$counter = 1;
		while ($typesArray = mysql_fetch_array($typesresult)) {
			$t = $typesArray['type'];
			// make the plural version of type (eg pres, posts, etc)
			// this is for fetching the proper translations
			// just concatenate the s char in the end of the string
			$ts = $t."s";
			//type the seperator in front of the types except the 1st
			if ($counter != 1) $retString .= " | ";
				$retString .= "<a href=\"#".$t."\">".${'lang'.$ts}."</a>";
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
        foreach (array("pre" => $langpres,
                       "post" => $langposts,
                       "other" => $langothers) as $type => $message) {

                $result = db_query("SELECT
                                        cours.cours_id cid,
                                        cours.code k,
                                        cours.fake_code fake_code,
                                        cours.intitule i,
                                        cours.visible visible,
                                        cours.titulaires t,
                                        cours.password password
                                  FROM cours
                                  WHERE cours.faculteid = $facid
                                  AND cours.visible != ".COURSE_INACTIVE."
				  AND cours.type = '$type'
                                  ORDER BY cours.intitule, cours.titulaires");

                if (mysql_num_rows($result) == 0) {
                        continue;
                }

                if ($numoftypes > 1) {
                        $retString .= "\n    <table width='100%' class='tbl_course_type'>";
                        $retString .= "\n    <tr>";
                        $retString .= "\n      <td><a name='$type'></a><b>$message</b></td>";
                        $retString .= "\n      <td align='right'><a href='#top'>$langBegin</a>&nbsp;</td>";
                        $retString .= "\n    </tr>";
                        $retString .= "\n    </table>\n";
                } else {
                        $retString .= "\n    <br />";
                        $retString .= "\n    <table width='100%' class='tbl_course_type'>";
                        $retString .= "\n    <tr>";
                        $retString .= "\n      <td><a name='$type'></a><b>$message</b></td>";
                        $retString .= "\n      <td>&nbsp;</td>";
                        $retString .= "\n    </tr>";
                        $retString .= "\n    </table>\n\n";
                }

                // legend
                $retString .= "\n    <script type='text/javascript' src='sorttable.js'></script>";
                $retString .= "\n    <table class='sortable' id='t1$type' width='100%'>";
                $retString .= "\n    <tr>";
                $retString .= "\n      <th width='50' align='center'>$langRegistration</th>";
                $retString .= "\n      <th>$langCourseCode</th>";
                $retString .= "\n      <th width='220'>$langTeacher</th>";
                $retString .= "\n      <th width='30' align='center'>$langType</th>";
                $retString .= "\n    </tr>";
                $k = 0;                
                while ($mycours = mysql_fetch_array($result)) {
                        $cid = $mycours['cid'];
                        $course_title = q($mycours['i']);
                        $courses_list[$cid] = array($mycours['k'], $mycours['visible']);
                        $password = $mycours['password'];
			// link creation
                        if ($mycours['visible'] == COURSE_OPEN or $is_power_user) { //open course
                                $codelink = "<a href='../../courses/$mycours[k]/'>$course_title</a>";
                        } elseif ($mycours['visible'] == COURSE_CLOSED) { //closed course
                                if (@$myCourses[$cid]['statut'] == 5) {
                                    $codelink = "<a href='../../courses/$mycours[k]/'>$course_title</a>";
                                } else { // if we are not registered
                                    $codelink = "<a href='../contact/index.php?from_reg=true&amp;cours_id=$cid'>$course_title</a>";
                                }
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
                        $vis_class = ($mycours['visible'] == 0)? 'class="reg_closed"': '';

                        if (isset($myCourses[$cid])) {
                                $codelink = "<a href='$urlAppend/courses/$mycours[k]/'>$course_title</a>";
                                if ($myCourses[$cid]['statut'] != 1) { // display registered courses
                                        // password needed
                                        if (!empty($password)) {
                                                $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' value='".
                                                        q($password)."' autocomplete='off' />";
                                        } else {
                                                $requirepassword = '';
                                        }
                                        $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' checked='checked' $vis_class />";
                                } else {
                                        $retString .= "<img src='$themeimg/teacher.png' alt='".q($langTutor)."' title='".q($langTutor)."' />";
                                }
                        } else { // display unregistered courses
                                if (!empty($password) and ($mycours['visible'] == COURSE_REGISTRATION or $mycours['visible'] == COURSE_OPEN)) {
                                        $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' autocomplete='off' />";
                                } else {
                                        $requirepassword = '';
                                }
                                $disabled = ($mycours['visible'] == 0)? 'disabled': '';
                                $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' $disabled $vis_class />";
                        }

                        $retString .= "<input type='hidden' name='changeCourse[]' value='$cid' />
                                   <td><span id='cid$cid'>$codelink</span> (" . q($mycours['fake_code']) .")$requirepassword</td>
                                   <td>". q($mycours['t']) ."</td>
                                   <td align='center'>";
                        // show the necessary access icon
                        foreach ($icons as $visible => $image) {
                                if ($visible == $mycours['visible']) {
                                        $retString .= $image;
                                }
                        }
                        $retString .= "</td>\n    </tr>";
                        $k++;
                } // END of while
                $retString .= "\n    </table>\n";
        } // end of foreach
        return $retString;
}


function collapsed_facultes_horiz($fc) {

	global $langSelectFac;

	$retString = "\n   <form name='depform' action='$_SERVER[SCRIPT_NAME]' method='get'>\n";
	$retString .= "\n  <div id='operations_container'>\n    <ul id='opslist'>";
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
function is_restricted($cours_id)
{
	$res = mysql_fetch_row(db_query("SELECT visible FROM cours WHERE cours_id = $cours_id"));
	if ($res[0] == 0) {
		return true;
	} else {
		return false;
	}
}

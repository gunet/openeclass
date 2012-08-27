<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
require_once '../../include/baseTheme.php';
require_once 'hierarchy.inc.php';

$TBL_HIERARCHY = 'hierarchy';

require_once 'include/lib/hierarchy.class.php';

$tree = new hierarchy();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');
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
			db_query("DELETE FROM course_user
					WHERE statut <> 1 AND statut <> 10
					AND user_id = $uid AND course_id = $cid");
                }
        }

	$errorExists = false;
        foreach ($selectCourse as $key => $value) {
                $cid = intval($value);
                $course_info = db_query("SELECT public_code, password, visible FROM course WHERE id = $cid");
                if ($course_info) {
                        $row = mysql_fetch_array($course_info);
                        if ($row['visible'] == 1 and !empty($row['password']) and
                            $row['password'] != autounquote($_POST['pass' . $cid])) {
                                $errorExists = true;
                                $restrictedCourses[] = $row['public_code'];
                                continue;
                        }
                        if (is_restricted($cid) and !in_array($cid, $selectCourse)) { // do not allow registration to restricted course
                                $errorExists = true;
                                $restrictedCourses[] = $row['public_code'];
                        } else {
                                db_query("INSERT IGNORE INTO `course_user` (`course_id`, `user_id`, `statut`, `reg_date`)
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

                $tool_content .= "<table width='100%' class='tbl_border' id='t1'>";

                $initopen = $tree->buildJSTreeInitOpen();

                $head_content .= <<<hContent
<script type="text/javascript">

$(function() {

    $( "#js-tree" ).jstree({
        "plugins" : ["html_data", "themes", "ui", "cookies", "types", "sort"],
        "core" : {
            "animation": 300,
            "initially_open" : [$initopen]
        },
        "themes" : {
            "theme" : "eclass",
            "dots" : true,
            "icons" : false
        },
        "ui" : {
            "select_limit" : 1
        },
        "cookies" : {
            "save_selected": false
        },
        "types" : {
            "types" : {
                "nosel" : {
                    "hover_node" : false,
                    "select_node" : false
                }
            }
        },
        "sort" : function (a, b) {
            priorityA = this._get_node(a).attr("tabindex");
            priorityB = this._get_node(b).attr("tabindex");

            if (priorityA == priorityB)
                return this.get_text(a) > this.get_text(b) ? 1 : -1;
            else
                return priorityA < priorityB ? 1 : -1;
        }
    })
    .bind("select_node.jstree", function (event, data) { document.location.href='?fc=' + data.rslt.obj.attr("id").substring(2); });

});

</script>
hContent;

                $tool_content .= "<tr><td><div id='js-tree'>". $tree->buildHtmlUl(array('where' => 'AND node.allow_course = true', 'codesuffix' => true)) ."</div></td></tr>";

                $tool_content .= "</table>";
		$tool_content .= "<br /><br />\n";
	} else {
		// department exists
		$numofcourses = getdepnumcourses($fc);
		// display all the facultes collapsed
		$tool_content .= collapsed_facultes_horiz($fc);
		$tool_content .= "\n    <form action='$_SERVER[SCRIPT_NAME]' method='post'>";
		if ($numofcourses > 0) {
			$tool_content .= expanded_faculte($fac, $fc, $uid);
			$tool_content .= "<br />
				<div align='right'><input class='Login' type='submit' name='submit' value='$langRegistration' />&nbsp;&nbsp;</div>";
		} else {
			if ($fac) {
				$tool_content .= "<table width='100%' class='tbl_border'>
				<tr>
				<th><a name='top'></a><b>$langFaculty:</b> ". $tree->getFullPath($fc, true, $_SERVER['SCRIPT_NAME'].'?fc=') ."</th>
				</tr></table><br />";

                                $tool_content .= departmentChildren($fc, 'courses');

				$tool_content .= "<br />
				<div class=alert1>$langNoCoursesAvailable</div>\n";
			}
		}
		$tool_content .= "\n    </form>";
	} // end of else (department exists)
}
$tool_content .= "<script type='text/javascript'>$(course_list_init);
var themeimg = '".js_escape($themeimg)."';
var lang = {
        unCourse: '".js_escape($langUnCourse)."',
        cancel: '".js_escape($langCancel)."',
        close: '".js_escape($langClose)."',
        unregCourse: '".js_escape($langUnregCourse)."',
        reregisterImpossible: '".js_escape("$langConfirmUnregCours $m[unsub]")."',
        invalidCode: '".js_escape($langInvalidCode)."',
};</script>";

draw($tool_content, 1, null, $head_content);


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
	$res = mysql_fetch_row(db_query("SELECT COUNT(code) FROM course, course_department
                WHERE course.id = course_department.course AND course_department.department = $fac"));
	return $res[0];
}

function expanded_faculte($fac_name, $facid, $uid) {
    global $m, $icons, $langTutor, $langBegin, $langRegistration, $mysqlMainDb,
           $langRegistration, $langCourseCode, $langTeacher, $langType, $langFaculty,
           $langpres, $langposts, $langothers, $themeimg, $tree;

    $retString = "";

    // build a list of course followed by user.
    $usercourses = db_query("SELECT course.code course_code, course.public_code public_code,
                                    course.id course_id, statut
                                FROM course_user, course
                                WHERE course_user.course_id = course.id
                                AND user_id = ".$uid);

    while ($row = mysql_fetch_array($usercourses)) {
        $myCourses[$row['course_id']] = $row;
    }

    $retString .= "<table width='100%' class='tbl_border'>
                   <tr>
                   <th><a name='top'> </a>$langFaculty: <b>". $tree->getFullPath($facid, true, $_SERVER['SCRIPT_NAME'].'?fc=') ."</b></th></tr></table><br/>";

    $retString .= departmentChildren($facid, 'courses');


    $result = db_query("SELECT
                            course.id cid,
                            course.code k,
                            course.public_code public_code,
                            course.title i,
                            course.visible visible,
                            course.prof_names t,
                            course.password password
                       FROM course, course_department
                      WHERE course.id = course_department.course
                        AND course_department.department = $facid
                        AND course.visible != ".COURSE_INACTIVE."
                   ORDER BY course.title, course.prof_names");

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
            $codelink = "<a href='../contact/index.php?from_reg=true&course_id=$cid'>$course_title</a>";
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
        $vis_class = ($mycours['visible'] == 0)? 'class="reg_closed"': '';

        if (isset($myCourses[$cid])) {
            if ($myCourses[$cid]['statut'] != 1) { // display registered courses
                // password needed
                if (!empty($password) and $mycours['visible'] == 1) {
                    $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' value='". q($password) ."' />";
                } else {
                    $requirepassword = '';
                }
                $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' checked='checked' $vis_class />";
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

            $disabled = ($mycours['visible'] == 0)? 'disabled': '';
            $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' $disabled $vis_class />";
        }
        $retString .= "<input type='hidden' name='changeCourse[]' value='$cid' />
                   <td>$codelink (" . q($mycours['public_code']) .")$requirepassword</td>
                   <td>". q($mycours['t']) ."</td>
                   <td align='center'>";

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

function collapsed_facultes_horiz($fc) {

	global $langSelectFac, $tree, $head_content;

	$retString = "\n   <form name='depform' action='$_SERVER[SCRIPT_NAME]' method='get'>\n";
	$retString .= "\n  <div id='operations_container'>\n    <ul id='opslist'>";
	$retString .=  "\n    <li>$langSelectFac:&nbsp;";
        list($js, $html) = $tree->buildNodePicker(array('params' => 'name="fc" onChange="document.depform.submit();"', 'defaults' => $fc, 'tree' => null, 'useKey' => 'id', 'where' => 'AND node.allow_course = true', 'multiple' => false));
        $head_content .= $js;
        $retString .= $html;
	$retString .=  "\n    </li>";
	$retString .= "\n    </ul>\n  </div>\n";
  	$retString .= "\n    </form>";

        return $retString;
}

// check if a course is restricted
function is_restricted($course_id)
{
	$res = mysql_fetch_row(db_query("SELECT visible FROM course WHERE id = $course_id"));
	if ($res[0] == 0) {
		return true;
	} else {
		return false;
	}
}

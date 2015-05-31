<?php

/* ========================================================================
 * Open eClass 3.0
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
require_once '../../include/baseTheme.php';
require_once 'include/log.php';
require_once 'include/lib/hierarchy.class.php';
$tree = new Hierarchy();

$pageName = $langChoiceLesson;

$icons = array(
    COURSE_OPEN => "<img src='$themeimg/lock_open.png' alt='" . $langOpenCourse . "' title='" . $langOpenCourse . "' />",
    COURSE_REGISTRATION => "<img src='$themeimg/lock_registration.png' alt='" . $langRegCourse . "' title='" . $langRegCourse . "' />",
    COURSE_CLOSED => "<img src='$themeimg/lock_closed.png' alt='" . $langClosedCourse . "' title='" . $langClosedCourse . "' />"
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
            Database::get()->query("DELETE FROM course_user "
                    . " WHERE status <> ?d AND status <> ?d AND user_id = ?d "
                    . " AND course_id = ?d", USER_TEACHER, USER_GUEST, $uid, $cid);
            // logging
            Log::record($cid, MODULE_ID_USERS, LOG_DELETE, array('uid' => $uid, 'right' => 0));
        }
    }

    $errorExists = false;
    foreach ($selectCourse as $key => $value) {
        $cid = intval($value);
        $course_info = Database::get()->querySingle("SELECT public_code, password, visible FROM course WHERE id = ?d", $cid);
        if ($course_info) {
            if (($course_info->visible == COURSE_REGISTRATION or
                    $course_info->visible == COURSE_OPEN) and !empty($course_info->password) and
                    $course_info->password !== $_POST['pass' . $cid]) {
                $errorExists = true;
                $restrictedCourses[] = $course_info->public_code;
                continue;
            }
            if (is_restricted($cid) and !in_array($cid, $selectCourse)) { // do not allow registration to restricted course
                $errorExists = true;
                $restrictedCourses[] = $course_info->public_code;
            } else {
                Database::get()->query("INSERT IGNORE INTO `course_user` (`course_id`, `user_id`, `status`, `reg_date`)
                                        VALUES (?d, ?d, ?d, NOW())", $cid, intval($uid), USER_STUDENT);
            }
        }
    }

    if ($errorExists) {
        $tool_content .= "<div class='alert alert-danger'>$langWrongPassCourse " .
                q(join(', ', $restrictedCourses)) . "</div><br />";
    } else {
        $tool_content .= "<div class='alert alert-success'>$langRegDone</div>";
    }
    $tool_content .= "<div><a href='../../index.php'>$langHome</a></div>";
} else {
    $fac = getfacfromfc($fc);
    if (!$fac) { // if user does not belong to department
        $tool_content .= $langAddHereSomeCourses;

        $roots = $tree->buildRootsArray();

        if (count($roots) <= 0)
            die("ERROR: no root nodes");
        else if (count($roots) == 1) {
            header("Location:" . $urlServer . "modules/auth/courses.php?fc=" . intval($roots[0]));
            exit();
        } else {
            $tool_content .= '<ul>' .
                $tree->buildNodesNavigationHtml($tree->buildRootsArray(), 'opencourses') .
                '</ul>';
        }
    } else {
        // department exists
        $numofcourses = getdepnumcourses($fc);
        $tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
        if (count($tree->buildRootsArray()) > 1) {
            $tool_content .= $tree->buildRootsSelectForm($fc);
        }
        $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<ul class='list-group'>
                                  <li class='list-group-item list-header'><a name='top'></a>$langFaculty: " .
                $tree->getFullPath($fc, false, $_SERVER['SCRIPT_NAME'] . '?fc=') . "
                                  </li>";
        $tool_content .= $tree->buildDepartmentChildrenNavigationHtml($fc, 'courses');
        $subTrees = $tree->buildSubtrees(array($fc));
        $tool_content .= "</ul></form>";

        if ($numofcourses > 0) {
            $tool_content .= expanded_faculte($fc, $uid);
            $tool_content .= "<br /><div align='right'><input class='btn btn-primary' type='submit' name='submit' value='$langRegistration'>&nbsp;&nbsp;</div>";
        } else {
            $tool_content .= "<div class='alert alert-warning text-center'>- $langNoCourses -</div>\n";

        }
    } // end of else (department exists)
}
$tool_content .= "<script type='text/javascript'>$(course_list_init);
var themeimg = '" . js_escape($themeimg) . "';
var urlAppend = '".js_escape($urlAppend)."';
var lang = {
        unCourse: '" . js_escape($langUnCourse) . "',
        cancel: '" . js_escape($langCancel) . "',
        close: '" . js_escape($langClose) . "',
        unregCourse: '" . js_escape($langUnregCourse) . "',
        reregisterImpossible: '" . js_escape("$langConfirmUnregCours $m[unsub]") . "',
        invalidCode: '" . js_escape($langInvalidCode) . "',
};
var courses = ".(json_encode($courses_list)).";
</script>";

load_js('tools.js');

draw($tool_content, 1, null, $head_content);

function getfacfromfc($dep_id) {
    $fac = Database::get()->querySingle("SELECT name FROM hierarchy WHERE id = ?d", intval($dep_id));
    if ($fac) {
        return $fac->name;
    } else {
        return 0;
    }
}

function getfcfromuid($uid) {
    $res = Database::get()->querySingle("SELECT department FROM user_department WHERE user = ?d LIMIT 1", intval($uid));
    if ($res) {
        return $res->department;
    } else {
        return 0;
    }
}

function getdepnumcourses($fac) {
    return Database::get()->querySingle("SELECT COUNT(code) as count FROM course, course_department
                WHERE course.id = course_department.course AND course_department.department = ?d", intval($fac))->count;
}

/**
 * @brief display courses list
 * @global type $m
 * @global array $icons
 * @global type $langTutor
 * @global type $langBegin
 * @global type $langRegistration
 * @global type $langRegistration
 * @global type $langCourseCode
 * @global type $langTeacher
 * @global type $langType
 * @global type $langFaculty
 * @global type $langpres
 * @global type $langposts
 * @global type $langothers
 * @global type $themeimg
 * @global Hierarchy $tree
 * @param type $facid
 * @param type $uid
 * @return string
 */
function expanded_faculte($facid, $uid) {
    global $m, $icons, $langTutor, $langBegin, $langRegistration,
    $langRegistration, $langCourseCode, $langTeacher, $langType, $langFaculty,
    $langpres, $langposts, $langothers, $themeimg, $tree;

    $retString = '';

    // build a list of course followed by user.
    $myCourses = array();
    Database::get()->queryFunc("SELECT course.code course_code, course.public_code public_code,
                                       course.id course_id, status
                                  FROM course_user, course
                                 WHERE course_user.course_id = course.id
                                   AND user_id = ?d", function ($course) use (&$myCourses) {
        $myCourses[$course->course_id] = $course;
    }, intval($uid));



    $retString .= "\n    <div class='table-responsive'><table class='table-default'>";
    $retString .= "\n    <tr class='list-header'>";
    $retString .= "\n      <th width='50' align='center'>$langRegistration</th>";
    $retString .= "\n      <th>$langCourseCode</th>";
    $retString .= "\n      <th width='220'>$langTeacher</th>";
    $retString .= "\n      <th width='30' align='center'>$langType</th>";
    $retString .= "\n    </tr>";
    $k = 0;

    Database::get()->queryFunc("SELECT
                            course.id cid,
                            course.code k,
                            course.public_code public_code,
                            course.title i,
                            course.visible visible,
                            course.prof_names t,
                            course.password password
                       FROM course, course_department
                      WHERE course.id = course_department.course
                        AND course_department.department = ?d
                        AND course.visible != ?d
                   ORDER BY course.title, course.prof_names", function ($mycours) use (&$retString, $k, $uid, $myCourses, $themeimg, $langTutor, $m, $icons) {
        global $urlAppend, $courses_list;
        $cid = $mycours->cid;
        $course_title = q($mycours->i);
        $password = q($mycours->password);
        $courses_list[$cid] = array($mycours->k, $mycours->visible);
        // link creation
        if ($mycours->visible == COURSE_OPEN or $GLOBALS['is_power_user'] or isset($myCourses[$cid])) {
            // open course, registered to course, or power user who can see all
            $codelink = "<a href='{$urlAppend}courses/" . $mycours->k . "/'>$course_title</a>";
        } elseif ($mycours->visible == COURSE_CLOSED) {
            // closed course
            $codelink = "<a href='{$urlAppend}modules/contact/index.php?course_id=$cid'>$course_title</a>";
        } else {
            $codelink = $course_title;
        }

        if ($k % 2 == 0) {
            $retString .= "<tr class='even'>";
        } else {
            $retString .= "<tr class='odd'>";
        }

        $retString .= "<td align='center'>";
        $requirepassword = '';
        $vis_class = ($mycours->visible == 0) ? 'class="reg_closed"' : '';
        if (isset($myCourses[$cid])) {
            if ($myCourses[$cid]->status != 1) { // display registered courses
                // password needed
                if (!empty($password)) {
                    $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' value='" . q($password) . "' autocomplete='off' />";
                } else {
                    $requirepassword = '';
                }
                $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' checked='checked' $vis_class />";
            } else {
                $retString .= "<i class='fa fa-user'></i>";
            }
        } else { // display unregistered courses
            if (!empty($password) and ($mycours->visible == COURSE_REGISTRATION or $mycours->visible == COURSE_OPEN)) {
                $requirepassword = "<br />$m[code]: <input type='password' name='pass$cid' autocomplete='off' />";
            } else {
                $requirepassword = '';
            }

            $disabled = ($mycours->visible == 0) ? 'disabled' : '';
            $retString .= "<input type='checkbox' name='selectCourse[]' value='$cid' $disabled $vis_class />";
        }
        $retString .= "<input type='hidden' name='changeCourse[]' value='$cid' />
                   <td><span id='cid$cid'>$codelink</span> (" . q($mycours->public_code) . ")$requirepassword</td>
                   <td>" . q($mycours->t) . "</td>
                   <td align='center'>";

        // show the necessary access icon
        foreach ($icons as $visible => $image) {
            if ($visible == $mycours->visible) {
                $retString .= $image;
            }
        }

        $retString .= "</td></tr>";
        $k++;
    }, intval($facid), COURSE_INACTIVE);
    $retString .= "</table></div>";

    return $retString;
}

/**
 * @brief check if a course is restricted
 * @param type $course_id
 * @return boolean
 */
function is_restricted($course_id) {
    $res = Database::get()->querySingle("SELECT visible FROM course WHERE id = ?d", intval($course_id));
    if ($res && $res->visible == 0) {
        return true;
    } else {
        return false;
    }
}

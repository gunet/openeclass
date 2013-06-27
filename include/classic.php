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

/*
 * Logged In Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates the content of the start page when the
 * user is logged in
 *
 */

if (!defined('INDEX_START')) {
	die("Action not allowed!");
}

require_once 'include/lib/textLib.inc.php';

function course_table_header($statut)
{
        global $langCourseCode, $langMyCoursesProf, $langMyCoursesUser, $langCourseCode,
               $langTeacher, $langAdm, $langUnregCourse, $langUnCourse, $tool_content;

        if ($statut == 1) {
                $legend = $langMyCoursesProf;
                $manage = $langAdm;
        } elseif ($statut == 5) {
                $legend = $langMyCoursesUser;
                $manage = $langUnCourse;
        } else {
                $legend = "(? $statut ?)";
                $manage = '';
        }

	$tool_content .= "
        <script type='text/javascript' src='modules/auth/sorttable.js'></script>
        <table width='99%' class='sortable' id='t1'>
        <tr>
          <th colspan='2'>$legend</th>
          <th width='190'>$langTeacher</th>
          <th width='50' class='center'>$manage</th>
        </tr>\n";
}

function course_table_end()
{
        $GLOBALS['tool_content'] .= "\n</table><br />\n";
}

$status = array();
$sql = "SELECT course.id cid, course.code code, course.public_code,
                        course.title title, course.prof_names profs, course_user.statut statut
                FROM course JOIN course_user ON course.id = course_user.course_id
                WHERE course_user.user_id = $uid
                ORDER BY statut, course.title, course.prof_names";
$sql2 = "SELECT course.id cid, course.code code, course.public_code,
                        course.title title, course.prof_names profs, course_user.statut statut
                FROM course JOIN course_user ON course.id = course_user.course_id
                WHERE course_user.user_id = $uid
                AND course.visible != ".COURSE_INACTIVE."
                ORDER BY statut, course.title, course.prof_names";

if ($_SESSION['statut'] == 1) {
        $result2 = db_query($sql);
}
if ($_SESSION['statut'] == 5) {
        $result2 = db_query($sql2);
}
if ($result2 and mysql_num_rows($result2) > 0) {
	$k = 0;
        $this_statut = 0;
	// display courses
	while ($mycours = mysql_fetch_array($result2)) {
                $old_statut = $this_statut;
                $this_statut = $mycours['statut'];
                if ($k == 0 or $old_statut <> $this_statut) {
                        if ($k > 0) {
                                course_table_end();
                        }
                        course_table_header($this_statut);
                }
		$code = $mycours['code'];
                $title = $mycours['title'];
		$status[$code] = $this_statut;
		$course_id_map[$code] = $mycours['cid'];
                $profs[$code] = $mycours['profs'];
                $titles[$code] = $mycours['title'];
		if ($k%2==0) {
			$tool_content .= "<tr class='even'>\n";
		} else {
			$tool_content .= "<tr class='odd'>\n";
		}
                if ($this_statut == 1) {
                        $manage_link = "${urlServer}modules/course_info/?from_home=true&amp;cid=$code";
                        $manage_icon = $themeimg . '/tools.png';
                        $manage_title = $langAdm;
                } else {
                        $manage_link = "${urlServer}modules/unreguser/unregcours.php?cid=$code&amp;u=$uid";
                        $manage_icon = $themeimg . '/cunregister.png';
                        $manage_title = $langUnregCourse;
                }
		$tool_content .= "<td width='5'><img src='$themeimg/arrow.png' alt='' /></td>";
		$tool_content .= "<td><a href='${urlServer}courses/$code'>".q($title)."</a> <span class='smaller'>(".q($mycours['public_code']).")</span></td>";
		$tool_content .= "<td class='smaller'>".q($mycours['profs'])."</td>";
		$tool_content .= "<td align='center'><a href='$manage_link'><img src='$manage_icon' title='$manage_title' alt='$manage_title' /></a></td>";
		$tool_content .= "</tr>";
		$k++;
	}
        course_table_end();
}  elseif ($_SESSION['statut'] == '5') {
        // if are loging in for the first time as student...
	$tool_content .= "<p class='success'>$langWelcomeStud</p>\n";
}  elseif ($_SESSION['statut'] == '1') {
        // ...or as professor
        $tool_content .= "<p class='success'>$langWelcomeProf</p>\n";
}

if (count($status) > 0) {
        $announce_table_header = "
        <table width='100%' class='sortable' id='t3'>
        <tr>
           <th colspan='2'>$langMyPersoAnnouncements</th>
        </tr>\n";

        $logindate = last_login($uid);
        $table_begin = true;
        $result = db_query("SELECT announcement.id, announcement.content, announcement.`date`, announcement.title, announcement.course_id
                        FROM announcement, course_module, course_user
                        WHERE course_user.course_id = announcement.course_id AND
                              course_module.course_id = announcement.course_id AND
                              course_user.user_id = $uid AND
                              announcement.visible = 1 AND
                              course_module.visible = 1 AND
                              course_module.module_id = ".MODULE_ID_ANNOUNCE." AND
                              announcement.`date` > DATE_SUB('$logindate', INTERVAL 10 DAY)
                        ORDER BY announcement.`date` DESC");
        

        if ($result and mysql_num_rows($result) > 0) {
                if ($table_begin) {
                        $table_begin = false;
                        $tool_content .= $announce_table_header;
                }
                $la = 0;
                while ($ann = mysql_fetch_array($result)) {                        
                        $course_title = course_id_to_title($ann['course_id']);
                        $code = course_id_to_code($ann['course_id']);
                        $content = standard_text_escape($ann['content']);
                        if ($la % 2 == 0) {
                                $tool_content .= "<tr class='even'>\n";
                        } else {
                                $tool_content .= "<tr class='odd'>\n";
                        }
                        $tool_content .= "
                        <td width='16'>
                            <img src='$themeimg/arrow.png' alt='' /></td><td>
                                <b><a href='modules/announcements/index.php?course=$code&amp;an_id=$ann[id]'>".q($ann['title'])."</a></b>
                                <br>" . "<span class='smaller'>" .
                            claro_format_locale_date($dateFormatLong, strtotime($ann['date'])) .
                            "&nbsp;($langCourse: <b>" . q($code) . "</b>, $langTutor: <b>" .
                            q($profs[$code]) . "</b></span>)<br />".
                            standard_text_escape(ellipsize_html($content, 250, "<strong>&nbsp;...<a href='modules/announcements/index.php?course=$code&amp;an_id=$ann[id]'>
                                <span class='smaller'>[$langMore]</span></a></strong>"))."</td></tr>\n";
                        $la++;
                }
        }
        if (!$table_begin) {
                $tool_content .= "</table>";
        }
}
if (isset($status)) {
	$_SESSION['status'] = $status;
}

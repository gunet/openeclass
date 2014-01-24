<?php
/* ========================================================================
 * Open eClass 2.8
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

include "../include/lib/textLib.inc.php";

function cours_table_header($statut)
{
        global $langCourseCode, $langMyCoursesProf, $langMyCoursesUser, $langCourseCode,
               $langTeacher, $langAdm, $langUnCourse, $tool_content;

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
        <script type='text/javascript' src='../modules/auth/sorttable.js'></script>
        <table width='99%' class='sortable' id='t1'>
        <tr>
          <th colspan='2'>$legend</th>
          <th width='190'>$langTeacher</th>
          <th width='50' class='center'>$manage</th>
        </tr>";
}

function cours_table_end()
{
        $GLOBALS['tool_content'] .= "</table><br />";
}

$status = array();
$sql2 = "SELECT cours.cours_id cours_id, cours.code code, cours.fake_code fake_code,
                        cours.intitule title, cours.titulaires profs, cours_user.statut statut
                FROM cours JOIN cours_user ON cours.cours_id = cours_user.cours_id
                WHERE cours_user.user_id = $uid
                AND (cours.visible != ".COURSE_INACTIVE." OR cours_user.statut = 1)
                ORDER BY statut, cours.intitule, cours.titulaires";
$result2 = db_query($sql2);
$active_courses = array();
if ($result2 and mysql_num_rows($result2) > 0) {
	$k = 0;
        $this_statut = 0;
	// display courses
	while ($mycours = mysql_fetch_array($result2)) {
                $old_statut = $this_statut;
                $this_statut = $mycours['statut'];
                if ($k == 0 or $old_statut <> $this_statut) {
                        if ($k > 0) {
                                cours_table_end();
                        }
                        cours_table_header($this_statut);
                }
		$code = $mycours['code'];
                $title = $mycours['title'];
                $active_courses[$mycours['cours_id']] = $code;
		$status[$code] = $this_statut;
		$cours_id_map[$code] = $mycours['cours_id'];
                $profs[$code] = $mycours['profs'];
                $titles[$code] = $mycours['title'];
		if ($k%2==0) {
			$tool_content .= "<tr class='even'>";
		} else {
			$tool_content .= "<tr class='odd'>";
		}
                if ($this_statut == 1) {
                        $manage_link = "${urlServer}modules/course_info/infocours.php?from_home=TRUE&amp;cid=$code";
                        $manage_icon = $themeimg . '/tools.png';
                        $manage_title = $langAdm;
                } else {
                        $manage_link = "${urlServer}main/unregcours.php?cid=$code&amp;u=$uid";
                        $manage_icon = $themeimg . '/cunregister.png';
                        $manage_title = $langUnregCourse;
                }
		$tool_content .= "<td width='5'><img src='$themeimg/arrow.png' alt='' /></td>";
		$tool_content .= "<td><a href='${urlServer}courses/$code'>".q($title)."</a> <span class='smaller'>(".q($mycours['fake_code']).")</span></td>";
		$tool_content .= "<td class='smaller'>".q($mycours['profs'])."</td>";
		$tool_content .= "<td align='center'><a href='$manage_link'><img src='$manage_icon' title='$manage_title' alt='$manage_title' /></a></td>";
		$tool_content .= "</tr>";
		$k++;
	}
        cours_table_end();
}  elseif ($_SESSION['statut'] == '5') {
        // if are loging in for the first time as student...
	$tool_content .= "<p class='success'>$langWelcomeStud</p>";
}  elseif ($_SESSION['statut'] == '1') {
        // ...or as professor
        $tool_content .= "<p class='success'>$langWelcomeProf</p>";
}

$announcements = array();
if (count($active_courses) > 0) {
        $logindate = last_login($uid);
        foreach ($active_courses as $cid => $code) {
                $q = db_query("SELECT annonces.id, preview, temps, title, cours_id
                        FROM `$mysqlMainDb`.annonces, `$code`.accueil
                        WHERE cours_id = $cid AND
                              `$mysqlMainDb`.annonces.visibility = 'v' AND
                               temps > DATE_SUB('$logindate', INTERVAL 10 DAY) AND
                               `$code`.accueil.visible = 1 AND
                               `$code`.accueil.id = 7
                        ORDER BY temps DESC");
                if ($q and mysql_num_rows($q) > 0) {
                        while ($info = mysql_fetch_assoc($q)) {
                                $info['code'] = $code;
                                $info['cours_id'] = $cid;
                                $announcements[] = $info;
                        }
                }
        }
}
if (count($announcements)) {
        $tool_content .= "
        <table width='100%' class='sortable' id='t3'>
                <tr>
                   <th colspan='2'>$langMyPersoAnnouncements</th>
                </tr>\n";
        $la = 0;
        $lastcode = '';
        foreach ($announcements as $ann) {
                $code = $ann['code'];
                if ($code != $lastcode) {
                        $course_title = "<a href='$urlAppend/courses/$code/'>" .
                                q(course_id_to_title($ann['cours_id'])) . "</a>";
                        $tool_content .= "<tr class=''><td colspan='2'>" . q($langCourse) .
                                ": <b>$course_title</b></td></tr>\n";
                }
                $lastcode = $code;
                $class = ($la % 2)? 'odd': 'even';
                $tool_content .= "
                        <tr class='$class'>
                            <td width='16'>" . icon('arrow', '') . "</td>
                            <td>
                            <b><a href=". $urlServer . "modules/announcements/announcements.php?course=$ann[code]&amp;an_id=$ann[id]>".q($ann['title']) .
                            "</a></b><br>" .
                            claro_format_locale_date($dateFormatLong, strtotime($ann['temps'])) .
                            "<br>$ann[preview]</td></tr>\n";
                $la++;
        }
        $tool_content .= "</table>";
}

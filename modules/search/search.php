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

/*===========================================================================
search.php
@version $Id$
@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================

*/

require_once '../../include/baseTheme.php';
$require_current_course = FALSE;
$nameTools = $langSearch;

if (!get_config('enable_search')) {
        $tool_content .= "<div class='info'>$langSearchDisabled</div>";
        draw($tool_content, 0);
        exit;
}

if (!register_posted_variables(array('search_terms' => false,
                                    'search_terms_title' => false,
                                    'search_terms_keywords' => false,
                                    'search_terms_instructor' => false,
                                    'search_terms_coursecode' => false,
                                    'search_terms_description' => false), 'any')) {
/**********************************************************************************************
	emfanish formas anahzthshs ean oi oroi anazhthshs einai kenoi
***********************************************************************************************/
    $tool_content .= "
    <form method='post' action='$_SERVER[SCRIPT_NAME]'>
    <fieldset>
     <legend>$langSearchCriteria:</legend>
        <table class='tbl'>
            <tr>
                <th width='120'>$langTitle:</th>
                <td><input name='search_terms_title' type='text' size='50' /></td>
                <td class='smaller'>$langTitle_Descr</td>
            </tr>
            <tr>
                <th>$langDescription:</th>
                <td><input name='search_terms_description' type='text' size='50' /></td>
                <td class='smaller'>$langDescription_Descr</small>
            </tr>
            <tr>
                <th>$langKeywords:</th>
                <td><input name='search_terms_keywords' type='text' size='50' /></td>
                <td class='smaller'>$langKeywords_Descr</td>
            </tr>
            <tr>
                <th>$langTeacher:</th>
                <td><input name='search_terms_instructor' type='text' size='50' /></td>
                <td class='smaller'>$langInstructor_Descr</td>
            </tr>
            <tr>
                <th>$langCourseCode:</th>
                <td><input name='search_terms_coursecode' type='text' size='50' /></td>
                <td class='smaller'>$langCourseCode_Descr</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td colspan=2 class='right'><input type='submit' name='submit' value='$langDoSearch' />&nbsp;&nbsp;<input type='reset' name='reset' value='$langNewSearch' /></td>
            </tr>
        </table>
    </fieldset>
    </form>";

} else {
/**********************************************************************************************
		ektelesh anazhthshs afou yparxoun oroi anazhthshs
		 emfanish arikown mhnymatwn anazhthshs
***********************************************************************************************/
        $join_op = ' AND ';
        if(!empty($search_terms)) {
                $search_terms_title = $search_terms_keywords =
                        $search_terms_instructor = $search_terms_coursecode =
                        $search_terms_description = $_POST['search_terms'];
                $join_op = ' OR ';
        }

        $terms = array();

        $search_keys = array(
                       'search_terms_title' => 'course.title',
                       'search_terms_instructor' => 'course.prof_names',
                       'search_terms_keywords' => 'course.keywords',
                       'search_terms_coursecode' => array('course.code', 'course.public_code'));

        foreach ($search_keys as $key => $subject) {
                if (isset($GLOBALS[$key]) and !empty($GLOBALS[$key])) {
                        if (is_array($subject)) {
                                $subterms = array();
                                foreach ($subject as $field) {
                                        $subterms[] = $field . ' LIKE ' . quote('%' . $GLOBALS[$key] . '%');
                                }
                                $terms[] = '(' . implode(' OR ', $subterms) . ')';
                        } else {
                                $terms[] = $subject . ' LIKE ' . quote('%' . $GLOBALS[$key] . '%');
                        }
                }
        }

        if (!empty($search_terms_description)) {
                db_query('CREATE TEMPORARY TABLE desc_search_tmp AS
                                SELECT unit_id FROM unit_resources WHERE
                                        (visible = 1 OR unit_resources.`order` < 0) AND
                                        MATCH (title, comments)
                                        AGAINST (' . quote($search_terms_description) . ' IN BOOLEAN MODE)');
                db_query('INSERT INTO desc_search_tmp
                                SELECT id FROM course_units WHERE
                                        MATCH (title, comments) AGAINST (' . quote($search_terms_description) . ' IN BOOLEAN MODE)');
                $terms[] = "course_units.id IN (SELECT DISTINCT unit_id FROM desc_search_tmp)";
        }

        $course_restriction = 'course.visible IN (2, 1)';
        $course_user_join = '';
        if (isset($uid) and $uid) {
                $course_restriction = "($course_restriction OR course_user.statut IS NOT NULL)";
                $course_user_join = 'LEFT JOIN course_user ON course.id = course_user.course_id AND course_user.user_id = ' . $uid;
        }


	// visible = 2 or 1 for Public and Open courses
        $search = 'SELECT course.code, course.public_code, course.title, course.prof_names, course.keywords
                          FROM course ' . $course_user_join . '
                                     LEFT JOIN course_units ON course.id = course_units.course_id
                          WHERE ' . $course_restriction . ' AND
                                (course_units.visible = 1 OR
                                 course_units.visible IS NULL OR
                                 course_units.`order` < 0) AND (' .
                  implode($join_op, $terms) . ') GROUP BY code ORDER BY code';

        $result = db_query($search);
        if (!$result or mysql_num_rows($result) == 0) {
                $tool_content .= "<p class='alert1'>$langNoResult</p>";
        } else {
               $tool_content .= "
              <div id='operations_container'>
                <ul id='opslist'>
                   <li><a href='search.php'>$langNewSearch</a></li>
                </ul>
              </div>";

                $tool_content .= "
                  <p>$langDoSearch:&nbsp;<b>$langResults</b></p>
                  <script type='text/javascript' src='../auth/sorttable.js'></script>
                  <table width='100%' class='sortable' id='t1' align='left'>
                  <tr>
                    <th width='1'>&nbsp;</th>
                    <th><div align='left'>".$langCourse." ($langCode)</div></th>
                    <th width='200'><div align='left'>$langTeacher</div></th>
                    <th width='150'><div align='left'>$langKeywords</div></th>
                  </tr>";
                $k = 0;
                while ($mycours = mysql_fetch_array($result)) {
                        if ($k % 2) {
                                $tool_content .= "
                  <tr class='odd'>";
                        } else {
                                $tool_content .= "
                  <tr class='even'>";
                        }
			$course_id = course_code_to_id($mycours['code']);
                        $tool_content .= "<td><img src='$themeimg/arrow.png' alt=''  /></td><td>";
			// search inside course. If at least one result is found then display link for searching inside the course
			if (search_in_course($search_terms, $course_id, $mycours['code'])) {
				$tool_content .= "<a href='../../courses/$mycours[code]/?from_search=".urlencode($search_terms)."'>" . q($mycours['title']) .
                                "</a> (" . q($mycours['public_code']) . ")";
			} else {
				$tool_content .= "<a href='../../courses/$mycours[code]/'>" . q($mycours['title']) .
                                "</a> (" . q($mycours['public_code']) . ")";
			}
			$tool_content .= "</td>" .
                                "<td>" . q($mycours['prof_names']) . "</td>" .
                                "<td>" . q($mycours['keywords']) . "</td>
			</tr>";
                        $k++;
                }
                $tool_content .= "
                </table>";
        }
}

if (!empty($search_terms_description)) {
        db_query('DROP TEMPORARY TABLE desc_search_tmp');
}

// -----------------------------
// search inside course
// -----------------------------
function search_in_course($search_terms, $course_id, $course_code) {

	global $mysqlMainDb;

	$search_terms = mysql_real_escape_string($_REQUEST['search_terms']);
	$query = " AGAINST ('".$search_terms."";
	$query .= "' IN BOOLEAN MODE)";

	$sql = db_query("SELECT title, content, `date` FROM announcement
				WHERE course_id = $course_id
				AND visible = 1
				AND MATCH (title, content)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT title, content, day, hour, lasting FROM agenda
				WHERE course_id = $course_id
				AND visible = 1
				AND MATCH (title, content)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM document
				WHERE course_id = $course_id
				AND subsystem = 0
				AND visible = 1
				AND MATCH (filename, comment, title, creator, subject, description, author, language)".$query, $mysqlMainDb);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM exercise
				WHERE course_id = $course_id
				AND active = '1'
				AND MATCH (title, description)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM forum WHERE course_id = $course_id AND MATCH (name, `desc`)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT id, title FROM forum_topic WHERE course_id = $course_id AND MATCH (title)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	while($res = mysql_fetch_array($sql)) {
		$sql = db_query("SELECT topic_id AS topicid, post_text AS posttext
					FROM forum_post
					WHERE forum_id = $res[forum_id]
						AND MATCH (post_text)".$query);
		if (mysql_num_rows($sql) > 0) {
			return TRUE;
		}
	}
	$sql = db_query("SELECT * FROM link
				WHERE course_id = $course_id
				AND MATCH (url, title, description)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM video
				WHERE course_id = $course_id
				AND MATCH (url, title, description)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM videolinks
				WHERE course_id = $course_id
				AND MATCH (url, title, description)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT id, title, comments FROM course_units
				WHERE course_id = $course_id
				AND visible = 1
				AND MATCH (title, comments)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT unit_resources.unit_id AS id,
				unit_resources.title AS title,
				unit_resources.comments AS comments
			FROM unit_resources, course_units
				WHERE unit_resources.unit_id = course_units.id
				AND course_units.course_id = $course_id
				AND course_units.visible = 1
			AND MATCH(unit_resources.title, unit_resources.comments)".$query);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	return FALSE;
}

draw($tool_content, 0);

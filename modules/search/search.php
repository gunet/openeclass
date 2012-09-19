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

/*===========================================================================
search.php
@version $Id$
@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================

*/

include '../../include/baseTheme.php';
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
                       'search_terms_title' => 'intitule',
                       'search_terms_instructor' => 'titulaires',
                       'search_terms_keywords' => 'course_keywords',
                       'search_terms_coursecode' => array('code', 'fake_code'));

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
                                        (visibility = "v" OR unit_resources.`order` < 0) AND 
                                        MATCH (title, comments)
                                        AGAINST (' . quote($search_terms_description) . ' IN BOOLEAN MODE)');
                db_query('INSERT INTO desc_search_tmp
                                SELECT id FROM course_units WHERE
                                        MATCH (title, comments) AGAINST (' . quote($search_terms_description) . ' IN BOOLEAN MODE)');
                $terms[] = "course_units.id IN (SELECT DISTINCT unit_id FROM desc_search_tmp)";
        }

        $course_restriction = 'cours.visible IN (2, 1)';
        $course_user_join = '';
        if (isset($uid) and $uid) {
                $course_restriction = "($course_restriction OR cours_user.statut IS NOT NULL)";
                $course_user_join = 'LEFT JOIN cours_user ON cours.cours_id = cours_user.cours_id AND cours_user.user_id = ' . $uid;
        }

        
	// visible = 2 or 1 for Public and Open courses
        $search = 'SELECT code, fake_code, intitule, titulaires, course_keywords
                          FROM cours ' . $course_user_join . '
                                     LEFT JOIN course_units ON cours.cours_id = course_units.course_id
                          WHERE ' . $course_restriction . ' AND
                                (course_units.visibility = "v" OR
                                 course_units.visibility IS NULL OR
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
			$cours_id = course_code_to_id($mycours['code']);
                        $tool_content .= "<td><img src='$themeimg/arrow.png' alt=''  /></td><td>";
			// search inside course. If at least one result is found then display link for searching inside the course
			if (search_in_course($search_terms, $cours_id, $mycours['code'])) {
				$tool_content .= "<a href='../../courses/$mycours[code]/?from_search=".urlencode($search_terms)."'>" . q($mycours['intitule']) .
                                "</a> (" . q($mycours['fake_code']) . ")";
			} else {
				$tool_content .= "<a href='../../courses/$mycours[code]/'>" . q($mycours['intitule']) .
                                "</a> (" . q($mycours['fake_code']) . ")";
			}
			$tool_content .= "</td>" .
                                "<td>" . q($mycours['titulaires']) . "</td>" .
                                "<td>" . q($mycours['course_keywords']) . "</td>
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
function search_in_course($search_terms, $cours_id, $code_cours) {
	
	global $mysqlMainDb;
	
	$search_terms = mysql_real_escape_string($_REQUEST['search_terms']);
	$query = " AGAINST ('".$search_terms."";
	$query .= "' IN BOOLEAN MODE)";
	
	$sql = db_query("SELECT title, contenu, temps FROM annonces
				WHERE cours_id = $cours_id
				AND visibility = 'v'
				AND MATCH (title, contenu)".$query, $mysqlMainDb);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT titre, contenu, day, hour, lasting FROM agenda
				WHERE visibility = 'v'
				AND MATCH (titre, contenu)".$query, $code_cours);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM document
				WHERE course_id = $cours_id
				AND subsystem = 0
				AND visibility = 'v'
				AND MATCH (filename, comment, title, creator, subject, description, author, language)".$query, $mysqlMainDb);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM exercices
				WHERE active = '1'
				AND MATCH (titre, description)".$query, $code_cours);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM forums WHERE MATCH (forum_name, forum_desc)".$query, $code_cours);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT forum_id, topic_title FROM topics WHERE MATCH (topic_title)".$query, $code_cours);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	while($res = mysql_fetch_array($sql)) {
		$sql = db_query("SELECT posts.topic_id AS topicid, posts_text.post_text AS posttext
					FROM posts, posts_text
					WHERE posts.forum_id = $res[forum_id]
						AND posts.post_id = posts_text.post_id 
						AND MATCH (posts_text.post_text)".$query, $code_cours);
		if (mysql_num_rows($sql) > 0) {
			return TRUE;
		}
	}
	$sql = db_query("SELECT * FROM link
				WHERE course_id = $cours_id
				AND MATCH (url, title, description)".$query, $mysqlMainDb);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM video WHERE MATCH (url, titre, description)".$query, $code_cours);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT * FROM videolinks WHERE MATCH (url, titre, description)".$query, $code_cours);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT id, title, comments FROM course_units
				WHERE course_id = $cours_id
				AND visibility = 'v' 
				AND MATCH (title, comments)".$query, $mysqlMainDb);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	$sql = db_query("SELECT unit_resources.unit_id AS id,
				unit_resources.title AS title,
				unit_resources.comments AS comments
			FROM unit_resources, course_units
				WHERE unit_resources.unit_id = course_units.id
				AND course_units.course_id = $cours_id
				AND course_units.visibility = 'v'
			AND MATCH(unit_resources.title, unit_resources.comments)".$query, $mysqlMainDb);
	if (mysql_num_rows($sql) > 0) {
		return TRUE;
	}
	return FALSE;
}

draw($tool_content, 0);

<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/*===========================================================================
search.php
@version $Id$
@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================


//  elegxos gia to pou vrisketai o xrhsths sto systhma kai redirect sto antistoixo script anazhthshs
//  oi diathesimes katastaseis einai oi ekseis:
//
//  1. sthn kentrikh selida tou systhmatos (den exei ginei log-in)
//  2. sthn kentrikh selida twn mathimatwn (amesws meta to log-in)
//*/

include '../../include/baseTheme.php';
$require_current_course = FALSE;

$nameTools = $langSearch;
$tool_content = "";

//elegxos ean *yparxoun* oroi anazhthshs
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
    <form method='post' action='$_SERVER[PHP_SELF]'>
        <table width='99%'>
            <tr><th width='120' class='left'>&nbsp;</th>
                <td><b>$langSearchCriteria</b></td></tr>
            <tr><th class='left'>$langTitle</th>
                <td width='250'><input class='FormData_InputText' name='search_terms_title' type='text' size='50' /></td>
                <td><small>$langTitle_Descr</small></td></tr>
            <tr><th class='left'>$langDescription</th>
                <td width='250'><input class='FormData_InputText' name='search_terms_description' type='text' size='50' /></td>
                <td><small>$langDescription_Descr</small></td></tr>
            <tr><th class='left'>$langKeywords</th>
                <td><input class='FormData_InputText' name='search_terms_keywords' type='text' size='50' /></td>
                <td><small>$langKeywords_Descr</small></td></tr>
            <tr><th class='left'>$langTeacher</th>
                <td><input class='FormData_InputText' name='search_terms_instructor' type='text' size='50' /></td>
                <td><small>$langInstructor_Descr</small></td></tr>
            <tr><th class='left'>$langCourseCode</th>
                <td><input class='FormData_InputText' name='search_terms_coursecode' type='text' size='50' /></td>
                <td><small>$langCourseCode_Descr</small></td></tr>
            <tr><th>&nbsp;</th>
                <td colspan=2><input type='submit' name='submit' value='$langDoSearch' />&nbsp;&nbsp;<input type='reset' name='reset' value='$langNewSearch' /></td></tr>
        </table>
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
                        <table width='99%' class='Search' align='left'>
                        <tr class='odd'>
                                <td colspan='4'>$langDoSearch:&nbsp;<b>$langResults</b></td>
                        </tr>
                        <tr>
                                <th width='1%'>&nbsp;</th>
                                <th width='40%'><div align='left'>".$langCourse." ($langCode)</div></th>
                                <th width='30%'><div align='left'>$langTeacher</div></th>
                                <th width='30%'><div align='left'>$langKeywords</div></th>
                        </tr>";
                $k = 0;
                while ($mycours = mysql_fetch_array($result)) {
                        if ($k % 2) {
                                $tool_content .= "<tr class='odd'>";
                        } else {
                                $tool_content .= "<tr>";
                        }
                        $tool_content .= "<td><img src='../../template/classic/img/arrow_grey.gif' alt=''  /></td>" .
                                "<td><a href='../../courses/$mycours[code]/'>" . q($mycours['intitule']) .
                                "</a> (" . q($mycours['fake_code']) . ")</td>" .
                                "<td>" . q($mycours['titulaires']) . "</td>" .
                                "<td>" . q($mycours['course_keywords']) . "</td></tr>";
                        $k++;
                }
                $tool_content .= "</table>
                        <div id='operations_container'><ul id='opslist'>
                             <li><a href='search.php'>$langNewSearch</a></li>
                        </ul></div>";
        }
}

if (!empty($search_terms_description)) {
        db_query('DROP TEMPORARY TABLE desc_search_tmp');
}

draw($tool_content, 0, 'search');

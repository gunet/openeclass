<?
/*========================================================================
 *   Open eClass 2.1
 *   E-learning and Course Management System
 * ========================================================================
 *  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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


function display_exercises()
{
        global $id, $currentCourseID, $tool_content, $urlServer,
               $langComments, $langAddModulesButton, $langChoice, $langNoExercises, $langExercices;


        $result = db_query("SELECT * FROM exercices", $currentCourseID);
        $quizinfo = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $quizinfo[] = array(
			'id' => $row['id'],
		        'name' => $row['titre'],
                        'comment' => $row['description'],
                        'visibility' => $row['active']);
        }
        if (count($quizinfo) == 0) {
                $tool_content .= "\n<p class='alert1'>$langNoExercises</p>";
        } else {
                $tool_content .= "<form action='insert.php' method='post'><input type='hidden' name='id' value='$id'" .
                                 "<div class='fileman'><table class='Documents'><tbody>" .
                                 "<tr><th>$langExercices</th><th>$langComments</th>" .
                                 "<th>$langChoice</th></tr>\n";

		foreach ($quizinfo as $entry) {
			if ($entry['visibility'] == '0') { 
				$vis = 'invisible';
			} else {
				$vis = '';
			}
			$tool_content .= "<tr class='$vis'>";
			$tool_content .= "<td width='30%' valign='top' style='padding-top: 7px;' align='left'>
			<a href='${urlServer}modules/exercice/exercice_submit.php?exerciseId=$entry[id]'>$entry[name]</a></td>";
			$tool_content .= "<td width='70%'><div align='left'>$entry[comment]</div></td>";
			$tool_content .= "<td align='center'><input type='checkbox' name='exercise[]' value='$entry[id]'></td>";
			$tool_content .= "</tr>";
		}
		$tool_content .= "<tr><td colspan='3' class='right'>";
		$tool_content .= "<input type='submit' name='submit_exercise' value='$langAddModulesButton'></td>";
                $tool_content .= "</tr></tbody></table></div></form>\n";
        }
}

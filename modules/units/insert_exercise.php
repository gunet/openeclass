<?php
/* ========================================================================
 * Open eClass 2.6
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



function list_exercises()
{
        global $id, $currentCourseID, $tool_content, $urlServer,
               $langComments, $langAddModulesButton, $langChoice, $langNoExercises, $langExercices, $code_cours;


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
                $tool_content .= "\n  <p class='alert1'>$langNoExercises</p>";
        } else {
                $tool_content .= "\n  <form action='insert.php?course=$code_cours' method='post'><input type='hidden' name='id' value='$id'>" .
                                 "\n  <table width='99%' class='tbl_alt'>" .
                                 "\n  <tr>" .
                                 "\n    <th><div align='left'>&nbsp;$langExercices</div></th>" .
                                 "\n    <th><div align='left'>$langComments</div></th>" .
                                 "\n    <th width='100'>$langChoice</th>" .
                                 "\n  </tr>";
		$i = 0;
		foreach ($quizinfo as $entry) {
			if ($entry['visibility'] == '0') { 
				$vis = 'invisible';
			} else {
				if ($i%2 == 0) {
              $vis = 'even';
          } else {
              $vis = 'odd';
          }
			}
			$tool_content .= "\n  <tr class='$vis'>";
			$tool_content .= "\n    <td>&laquo; <a href='${urlServer}modules/exercice/exercice_submit.php?course=$code_cours&amp;exerciseId=$entry[id]'>$entry[name]</a></td>";
			$tool_content .= "\n    <td><div align='left'>$entry[comment]</div></td>";
			$tool_content .= "\n    <td class='center'><input type='checkbox' name='exercise[]' value='$entry[id]'></td>";
			$tool_content .= "\n  </tr>";
			$i++;
		}
		$tool_content .= "\n  <tr>\n    <th colspan='3'><div align='right'>";
		$tool_content .= "<input type='submit' name='submit_exercise' value='$langAddModulesButton'></div></th>";
                $tool_content .= "\n  </tr>\n  </table>\n  </form>\n";
        }
}

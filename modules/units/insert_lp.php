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



function list_lps()
{
        global $id, $cours_id, $mysqlMainDb, $tool_content, $urlServer, $langComments,
               $langAddModulesButton, $langChoice, $langNoLearningPath,
               $langLearningPaths, $code_cours, $themeimg;


        $result = db_query("SELECT * FROM lp_learnPath WHERE course_id = $cours_id ORDER BY name", $mysqlMainDb);
        $lpinfo = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $lpinfo[] = array(
			'id' => $row['learnPath_id'],
		        'name' => $row['name'],
                        'comment' => $row['comment'],
                        'visibility' => $row['visibility'],
                        'rank' => $row['rank']);
        }
        if (count($lpinfo) == 0) {
                $tool_content .= "\n  <p class='alert1'>$langNoLearningPath</p>";
        } else {
                $tool_content .= "\n  <form action='insert.php?course=$code_cours' method='post'>" .
                                 "\n  <input type='hidden' name='id' value='$id'>" .
                                 "\n  <table width='99%' class='tbl_alt'>" .
                                 "\n  <tr>" .
                                 "\n    <th><div align='left'>&nbsp;$langLearningPaths</div></th>" .
                                 "\n    <th><div align='left'>$langComments</div></th>" .
                                 "\n    <th width='80'>$langChoice</th>" .
                                 "\n  </tr>";
			$i = 0;
			foreach ($lpinfo as $entry) {
				if ($entry['visibility'] == 'HIDE') { 
					$vis = 'invisible';
				} else {
						if ($i%2 == 0) {
							$vis = 'even';
					} else {
							$vis = 'odd';
					}
				}
				$tool_content .= "\n  <tr class='$vis'>";
				$tool_content .= "\n    <td>&nbsp;<img src='$themeimg/lp_on.png' />&nbsp;&nbsp;<a href='${urlServer}/modules/learnPath/learningPath.php?course=$code_cours&amp;path_id=$entry[id]'>$entry[name]</a></td>";
				$tool_content .= "\n    <td>$entry[comment]</td>";
				$tool_content .= "\n    <td align='center'><input type='checkbox' name='lp[]' value='$entry[id]'></td>";
				$tool_content .= "\n  </tr>";
				$i++;
			}
		$tool_content .= "\n  <tr>" .
                                 "\n    <th colspan='3'><div align='right'>";
		$tool_content .= "<input type='submit' name='submit_lp' value='$langAddModulesButton'></div></th>";
                $tool_content .= "\n  </tr>\n  </table>\n  </form>\n";
        }
}

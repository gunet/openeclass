<?php
/* ========================================================================
 * Open eClass 2.6
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



function list_links()
{
        global $id, $cours_id, $currentCourseID, $tool_content, $urlServer, $mysqlMainDb,
               $langNoCategory, $langCategorisedLinks, $langComments, $langAddModulesButton,
               $langChoice, $langNoLinksExist, $langLinks, $code_cours, $themeimg;

        mysql_select_db($mysqlMainDb);
        $result = db_query("SELECT * FROM link WHERE course_id = $cours_id");
        if (mysql_num_rows($result) == 0) {
                $tool_content .= "\n<p class='alert1'>$langNoLinksExist</p>";
        } else {
                $tool_content .= "\n  <form action='insert.php?course=$code_cours' method='post'>
				<input type='hidden' name='id' value='$id' />" .
                                 "\n  <table class='tbl_alt' width='99%'>" .
                                 "\n  <tr>" .
                                 "\n    <th align='left'>&nbsp;$langLinks</th>" .
                                 "\n    <th align='left'>$langComments</th>" .
                                 "\n    <th width='80'>$langChoice</th>" .
                                 "\n  </tr>";
		$sql = db_query("SELECT * FROM link_category WHERE course_id = $cours_id");
		if (mysql_num_rows($sql) > 0) {
			$tool_content .= "\n  <tr class='odd'>" .
                                         "\n    <td colspan='3' class='bold'>&nbsp;$langCategorisedLinks</td>".
                                         "\n  </tr>";
			while ($catrow = mysql_fetch_array($sql, MYSQL_ASSOC)) {
				$tool_content .= "\n  <tr class='even'>";
                                $tool_content .= "\n    <td><img src='$themeimg/folder_open.png' />&nbsp;&nbsp;" .
                                                 q($catrow['name']) . "</td>";
				$tool_content .= "\n    <td>" . standard_text_escape($catrow['description']) . "</td>";
				$tool_content .= "\n    <td align='center'><input type='checkbox' name='catlink[]' value='$catrow[id]' /></td>";
				$tool_content .= "\n  </tr>";
				$sql2 = db_query("SELECT * FROM link WHERE course_id = $cours_id AND category = $catrow[id]");
				while($linkcatrow = mysql_fetch_array($sql2, MYSQL_ASSOC)) {
					$tool_content .= "\n  <tr class='even'>";
					$tool_content .= "\n    <td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='$themeimg/links_on.png' />&nbsp;&nbsp;<a href='" . q($linkcatrow['url']) . "' target='_blank'>" .
                                                q(($linkcatrow['title'] == '')? $linkcatrow['url']: $linkcatrow['title']) . "</a></td>";
					$tool_content .= "\n    <td>" . standard_text_escape($linkcatrow['description']) . "</td>";
					$tool_content .= "\n    <td align='center'><input type='checkbox' name='link[]' value='$linkcatrow[id]' /></td>";
					$tool_content .= "\n  </tr>";	
				}
			}
		}
		$result = db_query("SELECT * FROM link WHERE course_id = $cours_id AND category = 0");
		$linkinfo = array();
	        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $linkinfo[] = array(
			'id' => $row['id'],
		        'url' => $row['url'],
			'title' => ($row['title'] == '')? $row['url']: $row['title'],
                        'comment' => $row['description'],
			'category' => $row['category']);
		}                
		if (count($linkinfo) > 0) {
			$tool_content .= "\n  <tr class='odd'>" .
                                         "\n    <td colspan='3' class='bold'>$langNoCategory</td>" .
                                         "\n  </tr>";
			foreach ($linkinfo as $entry) { 
				$tool_content .= "\n  <tr class='even'>" .
                                                 "\n    <td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='$themeimg/links_on.png' />&nbsp;&nbsp;<a href='" . q($entry['url']) . "' target=_blank>" . q($entry['title']) . "</a></td>" .
				                 "\n  <td>" . standard_text_escape($entry['comment']) . "</td>" .
                                                 "\n  <td align='center'><input type='checkbox' name='link[]' value='$entry[id]' /></td>";
                                                 "\n  </tr>";
			}
		}
		$tool_content .= "\n  <tr>" .
                                 "\n    <th colspan='3'><div align='right'>" .
                                 "<input type='submit' name='submit_link' value='".q($langAddModulesButton)."' /></div></th>" .
                                 "\n  </tr>\n  </table>\n  </form>\n";
        }
}

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



function list_wikis()
{
        global $id, $cours_id, $mysqlMainDb, $tool_content, $urlServer,
               $langWikis, $langAddModulesButton, $langChoice, $langWikiNoWiki,
               $langWikiDescriptionForm, $code_cours, $themeimg;


        $result = db_query("SELECT * FROM wiki_properties WHERE course_id = $cours_id", $mysqlMainDb);
        $wikiinfo = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $wikiinfo[] = array(
			'id' => $row['id'],
		        'title' => $row['title'],
                        'description' => $row['description']);
        }
        if (count($wikiinfo) == 0) {
                $tool_content .= "\n<p class='alert1'>$langWikiNoWiki</p>";
        } else {
                $tool_content .= "\n  <form action='insert.php?course=$code_cours' method='post'>".
                                 "\n  <input type='hidden' name='id' value='$id'>" .
                                 "\n  <table class='tbl_alt' width='99%'>" .
                                 "\n  <tr>".
                                 "\n    <th><div align='left'>&nbsp;$langWikis</div></th>".
                                 "\n    <th>$langWikiDescriptionForm</th>" .
                                 "\n    <th>$langChoice</th>".
                                 "\n  </tr>\n";
		$i = 0;
		foreach ($wikiinfo as $entry) {
                        if ($i%2) {
                                $rowClass = "class='odd'";
                        } else {
                                $rowClass = "class='even'";
                        }
			$tool_content .= "\n  <tr $rowClass>";
			$tool_content .= "\n    <td>&nbsp;<img src='$themeimg/wiki_on.png' />&nbsp;&nbsp;<a href='${urlServer}modules/wiki/page.php?course=$code_cours&amp;wikiId=$entry[id]&amp;action=show'>$entry[title]</a></td>";
			$tool_content .= "\n    <td>$entry[description]</td>";
			$tool_content .= "\n    <td align='center'><input type='checkbox' name='wiki[]' value='$entry[id]'></td>";
			$tool_content .= "\n  </tr>";
			$i++;
		}
		$tool_content .= "\n  <tr><th colspan='3'><div align='right'>";
		$tool_content .= "<input type='submit' name='submit_wiki' value='$langAddModulesButton'></div></th>";
                $tool_content .= "\n  </tr>\n  </table>\n  </form>\n";
        }
}

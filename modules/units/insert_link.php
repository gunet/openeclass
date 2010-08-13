<?php
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


function display_links()
{
        global $id, $currentCourseID, $tool_content, $urlServer, $langNoCategory, $langCategorisedLinks,
        $langComments, $langAddModulesButton, $langChoice, $langNoLinksExist, $langLinks;


        $result = db_query("SELECT * FROM liens", $currentCourseID);
        if (mysql_num_rows($result) == 0) {
                $tool_content .= "\n<p class='alert1'>$langNoLinksExist</p>";
        } else {
                $tool_content .= "\n  <form action='insert.php' method='post'><input type='hidden' name='id' value='$id'" .
                                 "\n  <table class='tbl_alt' width='99%'>" .
                                 "\n  <tr>" .
                                 "\n    <th><div align='left'>&nbsp;$langLinks</div></th>" .
                                 "\n    <th><div align='left'>$langComments</div></th>" .
                                 "\n    <th width='80'>$langChoice</th>" .
                                 "\n  </tr>";
		$sql = db_query("SELECT * FROM link_categories", $currentCourseID);
		if (mysql_num_rows($sql) > 0) {
			$tool_content .= "\n  <tr class='odd'>" .
                                         "\n    <td colspan='3' class='bold'>&nbsp;$langCategorisedLinks</td>".
                                         "\n  </tr>";
			while($catrow = mysql_fetch_array($sql, MYSQL_ASSOC)) {
				$tool_content .= "\n  <tr class='even'>";
				$tool_content .= "\n    <td><img src='../../template/classic/img/opendir.gif' />&nbsp;&nbsp;$catrow[categoryname]</div></td>";
				$tool_content .= "\n    <td>$catrow[description]</td>";
				$tool_content .= "\n    <td align='center'><input type='checkbox' name='catlink[]' value='$catrow[id]'></td>";
				$tool_content .= "\n  </tr>";
				$sql2 = db_query("SELECT * FROM liens WHERE category = $catrow[id]");
				while($linkcatrow = mysql_fetch_array($sql2, MYSQL_ASSOC)) {
					$tool_content .= "\n  <tr class='even'>";
					$tool_content .= "\n    <td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='../../template/classic/img/links_on.gif' /></a>&nbsp;&nbsp;<a href='${urlServer}modules/link/link_goto.php?link_id=$linkcatrow[id]&link_url=$linkcatrow[url]' target=_blank>$linkcatrow[titre]</a></td>";
					$tool_content .= "\n    <td>$linkcatrow[description]</td>";
					$tool_content .= "\n    <td align='center'><input type='checkbox' name='link[]' value='$linkcatrow[id]'></td>";
					$tool_content .= "\n  </tr>";	
				}
			}
		}
		$result = db_query("SELECT * FROM liens WHERE category = 0", $currentCourseID);
		$linkinfo = array();
	        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $linkinfo[] = array(
			'id' => $row['id'],
		        'url' => $row['url'],
			'title' => $row['titre'],
                        'comment' => $row['description'],
			'category' => $row['category']);
		}
		if (count($linkinfo) > 0) {
			$tool_content .= "\n  <tr class='odd'>" .
                                         "\n    <td colspan='3' class='bold'>$langNoCategory</td>" .
                                         "\n  </tr>";
			foreach ($linkinfo as $entry) { 
				$tool_content .= "\n  <tr class='even'>";
				$tool_content .= "\n    <td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='../../template/classic/img/links_on.gif' /></a>&nbsp;&nbsp;<a href='${urlServer}modules/link/link_goto.php?link_id=$entry[id]&link_url=$entry[url]' target=_blank>$entry[title]</a></td>";
				$tool_content .= "\n  <td>$entry[comment]</td>";
				$tool_content .= "\n  <td align='center'><input type='checkbox' name='link[]' value='$entry[id]'></td>";
				$tool_content .= "\n  </tr>";
			}
		}
		$tool_content .= "\n  <tr>" .
                                 "\n    <th colspan='3'><div align='right'>";
		$tool_content .= "<input type='submit' name='submit_link' value='$langAddModulesButton'></th>";
                $tool_content .= "\n  </tr>\n  </table>\n  </form>\n";
        }
}

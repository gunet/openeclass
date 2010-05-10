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


function display_links()
{
        global $id, $currentCourseID, $tool_content, $urlServer, $langNoCategory, $langCategorisedLinks,
        $langComments, $langAddModulesButton, $langChoice, $langNoLinksExist, $langLinks;


        $result = db_query("SELECT * FROM liens", $currentCourseID);
        if (mysql_num_rows($result) == 0) {
                $tool_content .= "\n<p class='alert1'>$langNoLinksExist</p>";
        } else {
                $tool_content .= "<form action='insert.php' method='post'><input type='hidden' name='id' value='$id'" .
                                 "<div class='fileman'><table class='Documents'><tbody>" .
                                 "<tr><th width='60%'>$langLinks</th><th width='40%'>$langComments</th>" .
                                 "<th>$langChoice</th></tr>\n";
		$sql = db_query("SELECT * FROM link_categories", $currentCourseID);
		if (mysql_num_rows($sql) > 0) {
			$tool_content .= "<tr><th valign='top' style='padding-top: 7px;' align='left' colspan='3'>$langCategorisedLinks</th></tr>";
			while($catrow = mysql_fetch_array($sql, MYSQL_ASSOC)) {
				$tool_content .= "<tr>";
				$tool_content .= "<td valign='top' style='padding-top: 7px;'><div align='left'><img src='../../template/classic/img/opendir.gif' />&nbsp;&nbsp;$catrow[categoryname]</div></th>";
				$tool_content .= "<td><div align='left'>$catrow[description]</div></td>";
				$tool_content .= "<td align='center'><input type='checkbox' name='catlink[]' value='$catrow[id]'></td>";
				$tool_content .= "</tr>";
				$sql2 = db_query("SELECT * FROM liens WHERE category = $catrow[id]");
				while($linkcatrow = mysql_fetch_array($sql2, MYSQL_ASSOC)) {
					$tool_content .= "<tr>";
					$tool_content .= "<td valign='top' style='padding-top: 7px; padding-left: 20px;'><div align='left'>
					<a href='${urlServer}modules/link/link_goto.php?link_id=$linkcatrow[id]&link_url=$linkcatrow[url]' target=_blank>$linkcatrow[titre]</a></div></td>";
					$tool_content .= "<td><div align='left'>$linkcatrow[description]</div></td>";
					$tool_content .= "<td align='center'><input type='checkbox' name='link[]' value='$linkcatrow[id]'></td>";
					$tool_content .= "</tr>";	
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
			$tool_content .= "<tr><th valign='top' style='padding-top: 7px;' align='left ' colspan='3'>$langNoCategory</th></tr>";
			foreach ($linkinfo as $entry) { 
				$tool_content .= "<tr>";
				$tool_content .= "<td valign='top' style='padding-top: 7px;'><div align='left'>
				<a href='${urlServer}modules/link/link_goto.php?link_id=$entry[id]&link_url=$entry[url]' target=_blank>$entry[title]</a></div></td>";
				$tool_content .= "<td><div align='left'>$entry[comment]</div></td>";
				$tool_content .= "<td align='center'><input type='checkbox' name='link[]' value='$entry[id]'></td>";
				$tool_content .= "</tr>";
			}
		}
		$tool_content .= "<tr><td colspan='3' class='right'>";
		$tool_content .= "<input type='submit' name='submit_link' value='$langAddModulesButton'></td>";
                $tool_content .= "</tr></tbody></table></div></form>\n";
        }
}

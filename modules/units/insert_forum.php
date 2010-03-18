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


function display_forum()
{
        global $id, $currentCourseID, $tool_content, $urlServer,
               $langComments, $langAddModulesButton, $langChoice, $langNoForums, $langForums;

        $result = db_query("SELECT * FROM forums WHERE cat_id <> 1", $currentCourseID);
        $foruminfo = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $foruminfo[] = array(
			'id' => $row['forum_id'],
		        'name' => $row['forum_name'],
                        'comment' => $row['forum_desc'],
                        'topics' => $row['forum_topics']);
        }
        if (count($foruminfo) == 0) {
                $tool_content .= "\n<p class='alert1'>$langNoForums</p>";
        } else {
                $tool_content .= "<form action='insert.php' method='post'><input type='hidden' name='id' value='$id'" .
                                 "<div class='fileman'><table class='Documents'><tbody>" .
                                 "<tr><th>$langForums</th><th>$langComments</th>" .
                                 "<th>$langChoice</th></tr>\n";

		foreach ($foruminfo as $entry) {
			$tool_content .= "<tr class='forum'>";
			$tool_content .= "<td width='40%'>
			<a href='${urlServer}modules/phpbb/viewforum.php?forum=$entry[id]'>$entry[name]</a></td>";
			$tool_content .= "<td width='60%'><div align='left'>$entry[comment]</div></td>";
			$tool_content .= "<td align='center'><input type='checkbox' name='forum[]'  value='$entry[id]'></td>";
			$tool_content .= "</tr>";
			$r = db_query("SELECT * FROM topics WHERE forum_id = '$entry[id]'", $currentCourseID);
			if (mysql_num_rows($r) > 0) { // if forum topics found 
				$topicinfo = array();
				while($topicrow = mysql_fetch_array($r, MYSQL_ASSOC)) {
				$topicinfo[] = array(
					'topic_id' => $topicrow['topic_id'],
					'topic_title' => $topicrow['topic_title'],
					'topic_time' => $topicrow['topic_time']);
				}
				foreach ($topicinfo as $topicentry) {
					$tool_content .= "<tr>";
					$tool_content .= "<td class='topic' width='40%'><img src='../../modules/phpbb/images/topic_read.gif' /><a href='${urlServer}/modules/phpbb/viewtopic.php?topic=$topicentry[topic_id]&amp;forum=$entry[id]'>$topicentry[topic_title]</a></td>";
					$tool_content .= "<td>&nbsp;</td>";
					$tool_content .= "<td align='center'><input type='checkbox' name='forum[]'  value='$entry[id]:$topicentry[topic_id]'></td>";
					$tool_content .= "</tr>";
				}
			}
		}
		$tool_content .= "<tr><td colspan='3' class='right'>";
		$tool_content .= "<input type='submit' name='submit_forum' value='$langAddModulesButton'></td>";
                $tool_content .= "</tr></tbody></table></div></form>\n";
        }
}
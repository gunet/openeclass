<?php
/* ========================================================================
 * Open eClass 3.0
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



function list_forums()
{
        global $id, $tool_content, $urlServer, $course_id,
               $langComments, $langAddModulesButton, $langChoice, $langNoForums, $langForums, $course_code;

        $result = db_query("SELECT * FROM forum WHERE course_id = $course_id");
        $foruminfo = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $foruminfo[] = array(
			'id' => $row['id'],
		        'name' => $row['name'],
                        'comment' => $row['desc'],
                        'topics' => $row['num_topics']);
        }
        if (count($foruminfo) == 0) {
                $tool_content .= "\n  <p class='alert1'>$langNoForums</p>";
        } else {
                $tool_content .= "\n  <form action='insert.php?course=$course_code' method='post'>" .
                                 "\n  <input type='hidden' name='id' value='$id' />" .
                                 "\n  <table class='tbl_alt' width='99%'>" .
                                 "\n  <tr>".
                                 "\n    <th>$langForums</th>" .
                                 "\n    <th>$langComments</th>" .
                                 "\n    <th width='80'>$langChoice</th>".
                                 "\n  </tr>";

		foreach ($foruminfo as $entry) {
			$tool_content .= "<tr class='odd'>";
			$tool_content .= "<td>
			<a href='${urlServer}modules/forum/viewforum.php?course=$course_code&amp;forum=$entry[id]'>$entry[name]</a></td>";
			$tool_content .= "<td>$entry[comment]</td>";
			$tool_content .= "<td class='center'><input type='checkbox' name='forum[]' value='$entry[id]' /></td>";
			$tool_content .= "</tr>";
			$r = db_query("SELECT * FROM forum_topics WHERE forum_id = $entry[id]");
			if (mysql_num_rows($r) > 0) { // if forum topics found 
				$topicinfo = array();
				while($topicrow = mysql_fetch_array($r, MYSQL_ASSOC)) {
				$topicinfo[] = array(
					'topic_id' => $topicrow['id'],
					'topic_title' => $topicrow['title'],
					'topic_time' => $topicrow['topic_time']);
				}
				foreach ($topicinfo as $topicentry) {
					$tool_content .= "\n  <tr class='even'>";
					$tool_content .= "\n    <td>&nbsp;<img src='../../modules/forum/images/topic_read.gif' />&nbsp;&nbsp;<a href='${urlServer}/modules/forum/viewtopic.php?course=$course_code&amp;topic=$topicentry[topic_id]&amp;forum=$entry[id]'>$topicentry[topic_title]</a></td>";
					$tool_content .= "\n    <td>&nbsp;</td>";
					$tool_content .= "\n    <td class='center'><input type='checkbox' name='forum[]'  value='$entry[id]:$topicentry[topic_id]' /></td>";
					$tool_content .= "\n  </tr>";
				}
			}
		}
		$tool_content .= "\n  <tr>" .
                                 "\n    <th colspan='3'><div align='right'><input type='submit' name='submit_forum' value='$langAddModulesButton' /></div></th>";
                $tool_content .= "\n  </tr>" .
                                 "\n  </table>".
                                 "\n  </form>\n";
        }
}

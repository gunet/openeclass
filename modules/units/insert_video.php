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


function display_video()
{
        global $id, $currentCourseID, $tool_content, $urlServer,
               $langVideoTitle, $langDescr, $langDate, $langChoice,
               $langAddModulesButton, $langNoVideo; 

        $table_started = false;
        $count = 0;
        foreach (array('video', 'videolinks') as $table) {
                $result = db_query("SELECT * FROM $table", $currentCourseID);
                $count += mysql_num_rows($result);
                while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        if (!$table_started) {
                                $tool_content .= "<form action='insert.php' method='post'><input type='hidden' name='id' value='$id' />";
                                $tool_content .= "<table class='Documents' width='100%'>";
                        	$tool_content .= "<tr><th>$langVideoTitle</th><th>$langDescr</th><th>$langDate</th><th>$langChoice</th></tr>";
                                $table_started = true;
                        }
                        $videolink = "<a href='" .
                                video_url($table, $row['url'], @$row['path']) .
                                "'>" . htmlspecialchars($row['titre']) . '</a>';
                        $tool_content .= '<tr><td>' . $videolink . '</td><td>' . htmlspecialchars($row['description']) . '</td><td>' . format_date(strtotime($row['date'])) . "</td><td class='center'><input type='checkbox' name='video[]' value='$table:$row[id]' /></td></tr>\n";
                }
        }
        if ($count > 0) {
                $tool_content .= "<tr><td colspan='4' class='right'><input type='submit' name='submit_video' value='$langAddModulesButton' /></td></tr></table></form>";
        } else {
                $tool_content .= "<p class='alert1'>$langNoVideo</p>";
        }
}

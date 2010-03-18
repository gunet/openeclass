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


function display_assignments()
{
        global $id, $tool_content, $currentCourseID, $langTitle, $langChoice, $m,
               $langAddModulesButton, $langNoAssign, $langActive, $langInactive,
               $langVisible;


        $result = db_query("SELECT * FROM assignments ORDER BY active, title", $currentCourseID);
        if (mysql_num_rows($result) == 0) {
                $tool_content .= "\n<p class='alert1'>$langNoAssign</p>";
        } else {
                $tool_content .= "<form action='insert.php' method='post'><input type='hidden' name='id' value='$id' />\n" .
                                 "<table width='100%'>\n" .
                                 "<tr><th width='70%'>$langTitle</th><th>$langVisible<th>$m[deadline]</th>" .
                                 "<th>$langChoice</th></tr>\n";
                while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        $visible = $row['active']?
                                "<img title='$langActive' src='../../template/classic/img/visible.gif' />":
                                "<img title='$langInactive' src='../../template/classic/img/invisible.gif' />";
                        $description = empty($row['description'])? '':
                                "<br /><i>$row[description]</i>";
                        $tool_content .= "<tr><td>$row[title]$description</td>" .
                                "<td class='center'>$visible</td>" .
                                "<td class='center'>$row[submission_date]</td>" .
                                "<td class='center'><input name='work[]' value='$row[id]' type='checkbox' /></td></tr>\n";

		}
		$tool_content .= "<tr><td colspan='4' class='right'>" .
                        "<input type='submit' name='submit_work' value='$langAddModulesButton' />" .
                        "</td></tr></table></form>\n";
        }
}

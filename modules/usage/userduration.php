<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

/*
===========================================================================
    usage/userlogins.php
 * @version $Id$
    @last update: 2006-12-27 by Evelthon Prodromou <eprodromou@upnet.gr>
    @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr,
                    Ophelia Neofytou ophelia@ucnet.uoc.gr
==============================================================================
    @Description: Shows logins made by a user or all users of a course, during a specific period.
    Takes data from table 'logins' (and also from table 'stat_accueil' if still exists).

==============================================================================
*/

$require_current_course = TRUE;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;
$require_prof = true;
$totalDuration = 0; 
include '../../include/baseTheme.php';
include('../../include/action.php');
$tool_content = '';
$tool_content .= "
  <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li><a href='usage.php'>".$langUsageVisits."</a></li>
      <li><a href='favourite.php?first='>".$langFavourite."</a></li>
      <li><a href='userduration.php'>".$langUserDuration."</a></li>
    </ul>
  </div>";

$nameTools = $langUsage;
$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';


$tool_content .= "<table class='FormData' width='99%' align='left'><tbody>
	<tr>
	<th width='40%' class='left'>$langSurname $langName</th>
	<th width='30%'>$langAm</th>
	<th>$langGroup</th>
	<th width='10%'>$langDuration</th>
	</tr>
	</thead>
	<tbody>";


$sql= "SELECT a.user_id as user_id FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
	WHERE b.code_cours='$currentCourseID'";
$result= db_query($sql, $mysqlMainDb);

while ($row = mysql_fetch_assoc($result)) {
	$user_id = $row['user_id'];
	$sql2 = db_query("SELECT SUM(duration) FROM actions WHERE user_id = '$user_id'", $currentCourseID);
	list($duration[$currentCourseID]) = mysql_fetch_row($sql2);
	$totalDuration += $duration[$currentCourseID];
	$totalDuration = format_time_duration(0 + $totalDuration);
	$i = 0;
	foreach ($duration as $code => $time) {
		if ($i%2 == 0) {
			$tool_content .= "\n    <tr>";
		} else {
			$tool_content .= "\n    <tr class=\"odd\">";
		}
		$i++;
		$tool_content .= "<td width='70%'><img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif'>
		" .uid_to_name($user_id) . "</td>
		<td>" . uid_to_am($user_id) . "</td>
		<td align='center'>" . user_group($user_id) . "</td>
		<td>" . format_time_duration(0 + $time) . "</td></tr>";
	}
}
$tool_content .= "</tbody></table>";

draw($tool_content, 2);
?>

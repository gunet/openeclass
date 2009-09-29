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
include '../../include/baseTheme.php';

$tool_content = '';
if (isset($_GET['format']) and $_GET['format'] == 'csv') {
        $format = 'csv';

        if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
                $charset = 'Windows-1253';
        } else {
                $charset = 'UTF-8';
        }
        $crlf="\r\n";

        header("Content-Type: text/csv; charset=$charset");
        header("Content-Disposition: attachment; filename=usersduration.csv");
        
        echo join(';', array_map("csv_escape",
                                 array($langSurnameName, $langAm, $langGroup, $langDuration))),
             $crlf, $crlf;

} else {
        $format = 'html';

        $tool_content .= "
          <div id='operations_container'>
            <ul id='opslist'>
              <li><a href='usage.php'>".$langUsageVisits."</a></li>
              <li><a href='favourite.php?first='>".$langFavourite."</a></li>
              <li><a href='userduration.php'>".$langUserDuration."</a></li>
              <li>$langDumpUserDurationToFile&nbsp;(<a href='userduration.php?format=csv'>$langCodeUTF</a>&nbsp;<a href='userduration.php?format=csv&amp;enc=1253'>$langCodeWin</a>)</li>
            </ul>
          </div>";

        $nameTools = $langUsage;
        $local_style = '
            .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
             padding-left: 15px; padding-right : 15px; }
            .content {position: relative; left: 25px; }';

        $tool_content .= "<table class='FormData' width='99%' align='left'><tbody>
                <tr>
                <th class='left'>$langSurname $langName</th>
                <th>$langAm</th>
                <th>$langGroup</th>
                <th>$langDuration</th>
                </tr>
                </thead>
                <tbody>";
}

mysql_select_db($mysqlMainDb);
db_query("CREATE TEMPORARY TABLE duration AS
          SELECT SUM(c.duration) AS duration, user_id
          FROM `$currentCourseID`.actions AS c GROUP BY c.user_id;");

$result = db_query("SELECT duration.duration AS duration,
                           user.nom AS nom,
                           user.prenom AS prenom,
                           user.user_id AS user_id,
                           user.am AS am
                    FROM user LEFT JOIN cours_user ON user.user_id = cours_user.user_id
                              LEFT JOIN duration ON user.user_id = duration.user_id
                    WHERE cours_user.code_cours = '$currentCourseID'
                    GROUP BY duration.user_id
                    ORDER BY nom, prenom");

if ($result) {
        $i = 0;
        while ($row = mysql_fetch_assoc($result)) {
                $i++;
                if ($format == 'html') {
                        if ($i%2 == 0) {
                                $tool_content .= "\n    <tr>";
                        } else {
                                $tool_content .= "\n    <tr class='odd'>";
                        }
                        $tool_content .= "<td width='30%'><img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif'> $row[nom] $row[prenom]</td><td width='30%'>$row[am]</td><td align='center'>" . gid_to_name(user_group($row['user_id'])) . "</td <td>" . format_time_duration(0 + $row['duration']) . "</td></tr>";
                } else {
                        echo csv_escape($row['nom'] . ' ' . $row['prenom']), ';',
                             csv_escape($row['am']), ';',
                             csv_escape(gid_to_name(user_group($row['user_id']))), ';',
                             csv_escape(format_time_duration(0 + $row['duration'])), $crlf;
                }
        }
        if ($format == 'html') {
                $tool_content .= "</tbody></table>";
        }
}

db_query('DROP TEMPORARY TABLE duration', $mysqlMainDb);

if ($format == 'html') {
        draw($tool_content, 2);
}

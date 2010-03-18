<?php
/*========================================================================
*   Open eClass 2.3
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
 * My Announcements Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component shows a list of all the announcements in all the lessons
 * the user is enrolled in.
 *
 */
$require_login = TRUE;
$ignore_module_ini = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
include('../../include/phpmathpublisher/mathpublisher.php') ;
$nameTools = $langMyAnnouncements;
$tool_content = "";
$result = db_query("SELECT annonces.id, annonces.title, annonces.contenu,
                        DATE_FORMAT(temps, '%e-%c-%Y') AS temps,
                         cours.fake_code,
                         annonces.ordre
                        FROM annonces, cours_user, cours
                        WHERE annonces.cours_id = cours_user.cours_id AND
                              cours_user.cours_id = cours.cours_id AND
                              cours_user.user_id = $uid
                        ORDER BY annonces.temps DESC", $mysqlMainDb);

	$tool_content .= "
      <table width=\"99%\" class='FormData'>
      <thead>
      <tr>
        <th class=\"left\" width=\"220\">$langTitle</th>
        <td>&nbsp;</td>
      </tr>
      </thead>
      </table>
    ";
	$tool_content .= "
      <table width=\"99%\" align='left' class=\"announcements\">
      <tbody>";
        if (mysql_num_rows($result) > 0)  {    // found announcements ?
        while ($myrow = mysql_fetch_array($result)) {
                $content = $myrow['contenu'];
                $content = make_clickable($content);
                $content = nl2br($content);
		$content = mathfilter($content, 12, "../../include/phpmathpublisher/img/");
                $row = mysql_fetch_array(db_query("SELECT intitule,titulaires FROM cours
			WHERE code='$myrow[fake_code]'"));
                $tool_content .= "
      <tr>
        <td width='3'><img class=\"displayed\" src=../../template/classic/img/announcements_on.gif border=0 title=\"" . $myrow["title"] . "\"></td>
        <td>$m[name]: <b>$row[intitule]</b><br />$content</td>
        <td align='right' width='300'><small><i>($langAnn: ".$myrow['temps'].")</i></small><br /><br />$langTutor: <b>$row[titulaires]</b></td>
      </tr>";
      }  // while loop
	$tool_content .= "
      <tbody>
      </table>";

} else {  // no announcements
        $tool_content .= "<tr><td class=alert1>".$langNoAnnounce."</td></tr>\n";
}

$tool_content .= "</tbody></table>";
draw($tool_content, 1, 'announcements');
?>

<?php 
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**
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
$langFiles = 'announcements';
$ignore_module_ini = true;

include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');
$nameTools = $langMyAnnouncements;
$tool_content = "";
$result = db_query("SELECT annonces.id, annonces.title, annonces.contenu,
                        DATE_FORMAT(temps, '%e-%c-%Y') AS temps,
                         cours_user.code_cours,
                         annonces.ordre
                        FROM annonces,cours_user
                        WHERE annonces.code_cours=cours_user.code_cours
                        AND cours_user.user_id='$uid'
                        ORDER BY annonces.temps DESC", $mysqlMainDb)
                                OR die("Πρόβλημα στην βάση δεδομένων!");

$tool_content .= "<table width=\"100%\" cellpadding=\"0\" align=center cellspacing=\"0\" border=\"0\">\n";

        if (mysql_num_rows($result) > 0)  {    // found announcements ?
        while ($myrow = mysql_fetch_array($result)) {
                $content = $myrow['contenu'];
                $content = make_clickable($content);
                $content = nl2br($content);
                $row = mysql_fetch_array(db_query("SELECT intitule,titulaires FROM cours 
											WHERE code='$myrow[code_cours]'"));
                $tool_content .= "<tr><td>";
                $tool_content .= "<table width=96% align=center style=\"border: 1px solid silver;\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
                $tool_content .= "<tr><td class=color2><b>$row[intitule]</b></td>\n";
                $tool_content .= "<td class=color2 align=right valign=middle>$row[titulaires]</td>";
                $tool_content .= "<td class=color2 align=right width=1% valign=middle><img src='../../images/teacher.gif' title=$langTitulaire></td>\n";
                $tool_content .= "</tr>";
                $tool_content .= "<tr>";
                $tool_content .= "<td colspan=3 class=\"color1\"><i>($langAnn : ".$myrow['temps'].")</i><br><br>\n";
                $tool_content .= "$content</td>";
                $tool_content .= "</tr>";
                $tool_content .= "</table>";

								$tool_content .= "<br>";
        }       // while loop

} else {  // no announcements
        $tool_content .= "<tr><td>&nbsp;</td></tr>\n";
        $tool_content .= "<tr><td class=alert1><em>".$langNoAnnouncements."</em></td></tr>\n";
}

$tool_content .= "</table>";

draw($tool_content, 1, 'announcements');
?>

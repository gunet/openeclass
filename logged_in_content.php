<?PHP
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	Á full copyright notice can be read in "/info/copyright.txt".
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
 * Logged In Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component creates the content of the start page when the 
 * user is logged in
 * 
 */
$tool_content .= "<table width=\"99%\"><thead>";
$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
		FROM cours, cours_user WHERE cours.code=cours_user.code_cours AND cours_user.user_id='".$uid."'
		AND (cours_user.statut='5' OR cours_user.statut='10')");
if (mysql_num_rows($result2) > 0) {
	$tool_content .=  '<tr><th>'.$langMyCoursesUser.'</th></tr>';

	$tool_content .= "</thead><tbody>";
	$i=0;
	// SHOW COURSES
	while ($mycours = mysql_fetch_array($result2)) {
		$dbname = $mycours["k"];
		$status[$dbname] = $mycours["s"];
		if ($i%2==0) $tool_content .=  '<tr>';
		elseif($i%2==1) $tool_content .= '<tr class="odd">';
		$tool_content .= '<td>
			<a href="courses/'.$mycours['k'].'/">'.$mycours['i'].'</a>
			<br>'.$mycours['t'].'<br>'.$mycours['c'].'
			</td>
			</tr>';
		$i++;
	}	// while
} // end of if

$tool_content .= "</tbody></table><br>";

$tool_content .= "<table width=\"99%\"><thead>";
// second check: Get all the course that are administered by the current user (professor)
$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
        	FROM cours, cours_user WHERE cours.code=cours_user.code_cours 
		AND cours_user.user_id='".$uid."' AND cours_user.statut='1'");
if (mysql_num_rows($result2) > 0) {
	$tool_content .= '<tr><th>'.$langMyCoursesProf.'</th></tr>';
	$tool_content .= "</thead><tbody>";
	$i=0;
	while ($mycours = mysql_fetch_array($result2)) {
		$dbname = $mycours["k"];
		$status[$dbname] = $mycours["s"];
		if ($i%2==0) $tool_content .= '<tr>';
		elseif($i%2==1) $tool_content .= '<tr class=\"odd\">';
		$tool_content .= '<td>
                        <a href="'.$urlServer."courses/".$mycours['k'].'/">'.$mycours['i'].'</a>
                        <br>'.$mycours['t'].'<br>'.$mycours['c'].'
                        </td>
                        </tr>';
		$i++;
	}       // while
} // if
$tool_content .= '</tbody></table>';
session_register('status');

?>
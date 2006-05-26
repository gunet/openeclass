<?php
$langFiles = 'admin';
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Configuration File του e-Class";

// Initialise $tool_content
$tool_content = "";
// Main body


if (isset($submit))  {
	
	@chmod( "../../config",777 );
	@chmod( "../../config", 0777 );
	if ($backupfile=="on") {
		if (file_exists("../../config/config_backup.php"))
			unlink("../../config/config_backup.php");
		copy("../../config/config.php","../../config/config_backup.php");
	}
	
	$fd=@fopen("../../config/config.php", "w");
	if (!$fd) {
		
	} else {
	
		$stringConfig='<?php
/*
      +----------------------------------------------------------------------+
      | e-Class version 1.6                                                  |
      | based on CLAROLINE version 1.3.0                                     |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003, 2004 GUNet                                             |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | e-Class Authors:    Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      |                                                                      |
      | Claroline Authors:  Thomas Depraetere <depraetere@ipm.ucl.ac.be>     |
      |                     Hugues Peeters    <peeters@ipm.ucl.ac.be>        |
      |                     Christophe Geschι <gesche@ipm.ucl.ac.be>         |
      |                                                                      |
      +----------------------------------------------------------------------+
*/
/***************************************************************
*           CONFIG OF VIRTUAL CAMPUS
****************************************************************
GOAL
****
List of variables to be modified by the campus site administrator.
File has been CHMODDED 0444 by install.php. 
CHMOD 0666 (Win: remove read-only file property) to edit manually
*****************************************************************/

// This file was generate by script /install/index.php
// on '.date("r").'
// REMOTE_ADDR : 		'.$_SERVER['REMOTE_ADDR'].' = '.gethostbyaddr($_SERVER['REMOTE_ADDR']).'
// REMOTE_PORT : 		'.$_SERVER['REMOTE_PORT'].'
// HTTP_USER_AGENT : 	'.$_SERVER['HTTP_USER_AGENT'].'
// SERVER_NAME :		'.$_SERVER['SERVER_NAME'].'
// HTTP_COOKIE :		'.$_SERVER['HTTP_COOKIE'].'


$urlServer	=	"'.$_POST['formurlServer'].'";
$urlAppend	=	"'.$_POST['formurlAppend'].'";
$webDir		=	"'.str_replace("\\","/",realpath($_POST['formwebDir'])."/").'" ;

$mysqlServer="'.$_POST['formmysqlServer'].'";
$mysqlUser="'.$_POST['formmysqlUser'].'";
$mysqlPassword="'.$_POST['formmysqlPassword'].'";
$mysqlMainDb="'.$_POST['formmysqlMainDb'].'";
$phpMyAdminURL="'.$_POST['formphpMyAdminURL'].'";
$phpSysInfoURL="'.$_POST['formphpSysInfoURL'].'";
$emailAdministrator="'.$_POST['formemailAdministrator'].'";
$administratorName="'.$_POST['formadministratorName'].'";
$administratorSurname="'.$_POST['formadministratorSurname'].'";
$siteName="'.$_POST['formsiteName'].'";

$telephone="'.$_POST['formtelephone'].'";
$emailhelpdesk="'.$_POST['formemailhelpdesk'].'";
$Institution="'.$_POST['formInstitution'].'";
$InstitutionUrl="'.$_POST['formInstitutionUrl'].'";
$color1="'.$_POST['formcolor1'].'"; // light grey
$color2="'.$_POST['formcolor2'].'"; // less light grey for bicolored tables

// available: greek and english
$language = "'.$_POST['formlanguage'].'";

$userMailCanBeEmpty = "'.$_POST['formuserMailCanBeEmpty'].'";
$mainInterfaceWidth ="'.$_POST['formmainInterfaceWidth'].'";

$bannerPath = "'.$_POST['formbannerPath'].'";
$colorLight = "'.$_POST['formcolorLight'].'";
$colorMedium = "'.$_POST['formcolorMedium'].'";
$colorDark = "'.$_POST['formcolorDark'].'";

$have_latex = "'.$_POST['formhave_latex'].'";

?>';

	fwrite($fd, $stringConfig);

	$tool_content .= "<p>Το αρχείο ρυθμίσεων τροποποιήθηκε με επιτυχία!</p>";
	
}
	
	$tool_content .= "<center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

} else {
	
	if (isset($restore) && $restore=="yes") {
		$titleextra = " (Restored Values)";
		@include("../../config/config_backup.php");
	}
	
	$tool_content .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"post\"";
	$tool_content .= "<table width=\"99%\"><caption>Επεξεργασία Αρχείου".$titleextra."</caption><tbody>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$urlServer:</b></td>
    <td><input type=\"text\" name=\"formurlServer\" size=\"40\" value=\"".$urlServer."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$urlAppend:</b></td>
    <td><input type=\"text\" name=\"formurlAppend\" size=\"40\" value=\"".$urlAppend."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$webDir:</b></td>
    <td><input type=\"text\" name=\"formwebDir\" size=\"40\" value=\"".$webDir."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$mysqlServer:</b></td>
    <td><input type=\"text\" name=\"formmysqlServer\" size=\"40\" value=\"".$mysqlServer."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$mysqlUser:</b></td>
    <td><input type=\"text\" name=\"formmysqlUser\" size=\"40\" value=\"".$mysqlUser."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$mysqlPassword:</b></td>
    <td><input type=\"text\" name=\"formmysqlPassword\" size=\"40\" value=\"".$mysqlPassword."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$mysqlMainDb:</b></td>
    <td><input type=\"text\" name=\"formmysqlMainDb\" size=\"40\" value=\"".$mysqlMainDb."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$phpMyAdminURL:</b></td>
    <td><input type=\"text\" name=\"formphpMyAdminURL\" size=\"40\" value=\"".$phpMyAdminURL."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$phpSysInfoURL:</b></td>
    <td><input type=\"text\" name=\"formphpSysInfoURL\" size=\"40\" value=\"".$phpSysInfoURL."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$emailAdministrator:</b></td>
    <td><input type=\"text\" name=\"formemailAdministrator\" size=\"40\" value=\"".$emailAdministrator."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$administratorName:</b></td>
    <td><input type=\"text\" name=\"formadministratorName\" size=\"40\" value=\"".$administratorName."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$administratorSurname:</b></td>
    <td><input type=\"text\" name=\"formadministratorSurname\" size=\"40\" value=\"".$administratorSurname."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$siteName:</b></td>
    <td><input type=\"text\" name=\"formsiteName\" size=\"40\" value=\"".$siteName."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$telephone:</b></td>
    <td><input type=\"text\" name=\"formtelephone\" size=\"40\" value=\"".$telephone."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$emailhelpdesk:</b></td>
    <td><input type=\"text\" name=\"formemailhelpdesk\" size=\"40\" value=\"".$emailhelpdesk."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$Institution:</b></td>
    <td><input type=\"text\" name=\"formInstitution\" size=\"40\" value=\"".$Institution."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$InstitutionUrl:</b></td>
    <td><input type=\"text\" name=\"formInstitutionUrl\" size=\"40\" value=\"".$InstitutionUrl."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$color1:</b></td>
    <td><input type=\"text\" name=\"formcolor1\" size=\"40\" value=\"".$color1."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$color2:</b></td>
    <td><input type=\"text\" name=\"formcolor2\" size=\"40\" value=\"".$color2."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
	if ($language=="greek") {
		$grSel = "selected";
		$enSel = "";
	} else {
		$grSel = "";
		$enSel = "selected";
	}
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$language:</b></td>
    <td><select name=\"formlanguage\">
      <option value=\"greek\" ".$grSel.">greek</option>
      <option value=\"english\" ".$enSel.">english</option>
    </select></td>
</tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
	if ($userMailCanBeEmpty=="true") {
		$userMailSelTrue = "selected";
		$userMailSelFalse = "";
	} else {
		$userMailSelTrue = "";
		$userMailSelFalse = "selected";
	}
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$userMailCanBeEmpty:</b></td>
    <td><select name=\"formuserMailCanBeEmpty\">
      <option value=\"true\" ".$userMailSelTrue.">true</option>
      <option value=\"false\" ".$userMailSelFalse.">false</option>
    </select></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$mainInterfaceWidth:</b></td>
    <td><input type=\"text\" name=\"formmainInterfaceWidth\" size=\"40\" value=\"".$mainInterfaceWidth."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
  	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$bannerPath:</b></td>
    <td><input type=\"text\" name=\"formbannerPath\" size=\"40\" value=\"".$bannerPath."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$colorLight:</b></td>
    <td><input type=\"text\" name=\"formcolorLight\" size=\"40\" value=\"".$colorLight."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$colorMedium:</b></td>
    <td><input type=\"text\" name=\"formcolorMedium\" size=\"40\" value=\"".$colorMedium."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$colorDark:</b></td>
    <td><input type=\"text\" name=\"formcolorDark\" size=\"40\" value=\"".$colorDark."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
	if ($have_latex=="true") {
		$have_latexSelTrue = "selected";
		$have_latexSelFalse = "";
	} else {
		$have_latexSelTrue = "";
		$have_latexSelFalse = "selected";
	}
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$have_latex:</b></td>
    <td><select name=\"formhave_latex\">
      <option value=\"true\" ".$have_latexSelTrue.">true</option>
      <option value=\"false\" ".$have_latexSelFalse.">false</option>
    </select></td>
</tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><input type=\"checkbox\" name=\"backupfile\" checked> Αντικατάσταση του config_backup.php.</td>
  </tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><br><input type='submit' name='submit' value='$langModify'></td>
  </tr>";
	$tool_content .= "</tbody></table></form>\n";
  if (file_exists("../../config/config_backup.php")) {
  		$tool_content .= "<table width=\"99%\"><caption>Αλλες Ενέργειες</caption><tbody>";
		$tool_content .= "  <tr>
    <td colspan=\"2\"><a href=\"clarconf.php?restore=yes\">Restore values from backup</a></td>
  </tr>";
		$tool_content .= "</tbody></table>";
	}

	$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";
	
	if (isset($restore) && $restore=="yes") {
		@include("../../config/cconfig.php");
	}

}

draw($tool_content,3,'admin');
?>
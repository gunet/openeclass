<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	eclassconf.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
        @Description: Change configuration file settings

 	This script allows the administrator to change all values in the config.php,
 	to make a backup of the orginal and restore values from backup config.php

 	The user can : - Change settings in config.php
 	               - Create a backup file of the original config.php
 	               - Restore values from backup config.php
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Display values from config.php
  2) Restore values from backup config.php
  3) Save new config.php
  4) Create a backup file of config.php
  5) Display all on an HTML page
  
==============================================================================*/

/*****************************************************************************
		DEAL WITH  BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
$nameTools = $langEclassConf;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Save new config.php
if (isset($submit))  {
	// Make config directory writable
	@chmod( "../../config",777 );
	@chmod( "../../config", 0777 );
	// Create backup file
	if ($backupfile=="on") {
		// If a backup already exists delete it
		if (file_exists("../../config/config_backup.php"))
			unlink("../../config/config_backup.php");
		// Create the backup
		copy("../../config/config.php","../../config/config_backup.php");
	}
	// Open config.php empty
	$fd=@fopen("../../config/config.php", "w");
	if (!$fd) {
		$tool_content .= $langFileError;
	} else {

if ($_POST['formcloseuserregistration'] == 'false') {
                     $user_reg = 'FALSE';
             } else {
                     $user_reg = 'TRUE';
    }


if ($_POST['formhavelatex'] == 'false') {
                     $latex = 'FALSE';
             } else {
                     $latex = 'TRUE';
		    }


		// Prepare config.php content
		$stringConfig='<?php

/*
	=============================================================================
	GUnet e-Class 2.0
	E-learning and Course Management Program
	================================================================================
	Copyright(c) 2003-2007  - GUnet

	GUnet eClass 2.0 is an open platform distributed in the hope that    
  it will be useful (without any warranty), under the terms of the     
  GNU License (General Public License) as published by the Free        
  Software Foundation. The full license can be read in "license.txt".  
                                                                            
       Main Developers Group: Costas Tsibanis <k.tsibanis@noc.uoa.gr>       
                                  Yannis Exidaridis <jexi@noc.uoa.gr>       
                             Alexandros Diamantidis <adia@noc.uoa.gr>       
                               Tilemachos Raptis <traptis@noc.uoa.gr>       
                                                                            
       For a full list of contributors, see "CREDITS.txt".

	Contact address: Asynchronous Teleteaching Group (eclass@gunet.gr),  
                     Network Operations Center, University of Athens,    
                    Panepistimiopolis Ilissia, 15784, Athens, Greece

*/

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

$colorLight = "'.$_POST['formcolorLight'].'";
$colorMedium = "'.$_POST['formcolorMedium'].'";
$colorDark = "'.$_POST['formcolorDark'].'";

$postaddress = "'.$_POST['formpostaddress'].'";
$fax = "'.$_POST['formfax'].'";
$have_latex = '.$latex.';

$table_border = "'.$_POST['formtableborder'].'";
$close_user_registration = '.$user_reg.';
$encryptedPasswd = "true";
$persoIsActive = "'.$_POST['formpersoIsActive'].'";

$durationAccount = "'.$_POST['formdurationAccount'].'";

?>';
	// Save new config.php
	fwrite($fd, $stringConfig);
	// Update user with perso = no if persoisactive==false
	if ($_POST['formpersoIsActive']=="false") {
		$sql = 'UPDATE `user` SET `perso` = \'no\'';
		mysql_query($sql);
	}
	// Display result message
	$tool_content .= "<p>".$langFileUpdatedSuccess."</p>";
	
}
	// Display link to go back to index.php
	$tool_content .= "<center><p><a href=\"index.php\">".$langBack."</a></p></center>";

}
// Display config.php edit form
else {
	$titleextra = "config.php";
	// Check if restore has been selected
	if (isset($restore) && $restore=="yes") {
		// Substitute variables with those from backup file
		$titleextra = " ($langRestoredValues)";
		@include("../../config/config_backup.php");
	}
	// Constract the form
	$tool_content .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
	$tool_content .= "<table width=\"99%\"><caption>".$langFileEdit." ".$titleextra."</caption><tbody>";
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
	$tool_content .= " <tr>
    <td width=\"3%\" nowrap><b>\$emailAdministrator:</b></td>
    <td><input type=\"text\" name=\"formemailAdministrator\" size=\"40\" value=\"".$emailAdministrator."\"></td>
</tr>";
	$tool_content .= " <tr>
    <td width=\"3%\" nowrap><b>\$administratorName:</b></td>
    <td><input type=\"text\" name=\"formadministratorName\" size=\"40\" value=\"".$administratorName."\"></td>
</tr>";
	$tool_content .= " <tr>
    <td width=\"3%\" nowrap><b>\$administratorSurname:</b></td>
    <td><input type=\"text\" name=\"formadministratorSurname\" size=\"40\" value=\"".$administratorSurname."\"></td>
</tr>";
	$tool_content .= " <tr>
    <td width=\"3%\" nowrap><b>\$siteName:</b></td>
    <td><input type=\"text\" name=\"formsiteName\" size=\"40\" value=\"".$siteName."\"></td>
</tr>";
	$tool_content .= " <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
$tool_content .= " <tr>
    <td width=\"3%\" nowrap><b>\$postaddress:</b></td>
		<td><textarea rows='3' cols='40' name='formpostaddress'>$postaddress</textarea></td>
</tr>";
$tool_content .= " <tr>
    <td width=\"3%\" nowrap><b>\$telephone:</b></td>
    <td><input type=\"text\" name=\"formtelephone\" size=\"40\" value=\"".$telephone."\"></td>
</tr>";
 $tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$fax:</b></td>
    <td><input type=\"text\" name=\"formfax\" size=\"40\" value=\"".$fax."\"></td>
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
    <td width=\"3%\" nowrap><b>\$table_border:</b></td>
    <td><input type=\"text\" name=\"formtableborder\" size=\"40\" value=\"".$table_border."\"></td>
</tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";

if ($close_user_registration=="true") {
    $close_user_registrationSelTrue = "selected";
    $close_user_registrationSelFalse = "";
  } else {
    $close_user_registrationSelTrue = "";
    $close_user_registrationSelFalse = "selected";
  }

$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$close_user_registration:</b></td>
    <td><select name=\"formcloseuserregistration\">
      <option value=\"true\" ".$close_user_registrationSelTrue.">true</option>
      <option value=\"false\" ".$close_user_registrationSelFalse.">false</option>
    </select></td>
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
    <td><select name=\"formhavelatex\">
      <option value=\"true\" ".$have_latexSelTrue.">true</option>
      <option value=\"false\" ".$have_latexSelFalse.">false</option>
    </select></td>
</tr>";
	// Add select for $persoIsActive
	if ($persoIsActive == "true") {
		$persoIsActiveSelTrue = "selected";
		$persoIsActiveSelFalse = "";
	} else {
		$persoIsActiveSelTrue = "";
		$persoIsActiveSelFalse = "selected";
	}
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$persoIsActive:</b></td>
    <td><select name=\"formpersoIsActive\">
      <option value=\"true\" ".$persoIsActiveSelTrue.">true</option>
      <option value=\"false\" ".$persoIsActiveSelFalse.">false</option>
    </select></td>
</tr>";

$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$durationAccount:</b></td>
    <td><input type=\"text\" name=\"formdurationAccount\" size=\"40\" value=\"".$durationAccount."\"></td>
</tr>";

$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>\$encryptedPasswd:</b></td>
    <td><input type=\"checkbox\" checked disabled> ".$langencryptedPasswd."</td>
</tr>";

	$tool_content .= " <tr>
    <td colspan=\"2\"><hr></td>
  </tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><input type=\"checkbox\" name=\"backupfile\" checked> ".$langReplaceBackupFile."</td>
  </tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><br><input type='submit' name='submit' value='$langModify'></td>
  </tr>";
	$tool_content .= "</tbody></table></form>\n";
	// Check if a backup file exists
  if (file_exists("../../config/config_backup.php")) {
  	// Give option to restore values from backup file
  	$tool_content .= "<table width=\"99%\"><caption>".$langOtherActions."</caption><tbody>";
		$tool_content .= "  <tr>
    <td colspan=\"2\"><a href=\"eclassconf.php?restore=yes\">$langRestoredValues</a></td>
  </tr>";
		$tool_content .= "</tbody></table>";
	}
	// Display link to index.php
	$tool_content .= "<br><center><p><a href=\"index.php\">".$langBack."</a></p></center>";
	// After restored values have been inserted into form then bring back
	// values from original config.php, so the rest of the page can be played correctly
	if (isset($restore) && $restore=="yes") {
		@include("../../config/config.php");
	}

}

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>

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
 * Installation wizard
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This is the installation wizard of eclass. 
 *
 */
session_start();

$tool_content = "";
@include ("../modules/lang/greek/install.inc.php");
include('install_functions.php');

if (file_exists("../config/config.php")) {
	$tool_content .= "
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
  <head>
    <title>Καλωσορίσατε στον οδηγό εγκατάστασης του e-Class</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-7\" />
    <link href=\"../template/classic/tool_content.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"./install.css\" rel=\"stylesheet\" type=\"text/css\" />
      
  </head>
  <body>
	<table width = \"99%\">
				<tbody>
					<tr>
						<td class=\"extraMessage\">
						Προσοχή !! Το αρχείο <b>config.php</b> υπάρχει ήδη στο σύστημά σας!! Το πρόγραμμα εγκατάστασης δεν πραγματοποιεί αναβάθμιση. Αν θέλετε να ξανατρέξετε την εγκατάσταση της πλατφόρμας,
            παρακαλούμε διαγράψτε το αρχείο config.php!
						
					</td>
					</tr>
				</tbody>
			</table>
			
	</body>
	</html>
   ";
	exit($tool_content);
}


//  step 0 initialise variables
if(isset($welcomeScreen) )
{
	$dbHostForm="localhost";
	$dbUsernameForm="root";
	$dbNameForm="eclass";
	$dbMyAdmin="../admin/mysql/";
	$phpSysInfoURL="../admin/sysinfo/";

	// extract the path to append to the url if it is not installed on the web root directory
	$urlAppendPath = ereg_replace ("/install/index.php", "", $_SERVER['PHP_SELF']);
	$urlForm = "http://".$_SERVER['SERVER_NAME'].$urlAppendPath."/";
	$pathForm = realpath("../")."/";
	$emailForm = $_SERVER['SERVER_ADMIN'];
	$nameForm = "Διαχειριστής";
	$surnameForm = "Πλατφόρμας";
	$loginForm = "admin";
	$passForm = generePass(8);
	$campusForm = "GUNet eClass";
	$helpdeskForm = "+30 2xx xxxx xxx";
	$faxForm = "";
	$postaddressForm = "";
	$institutionForm = "Ακαδημαϊκό Διαδίκτυο GUNet ";
	$institutionUrlForm = "http://www.gunet.gr/";

	$languageCourse = "greek";

	$encryptkey = "eclass";
}

if (isset($alreadyVisited)) {


	$tool_content .= "<form action=".$PHP_SELF."?alreadyVisited=1 method=\"post\">";
	$tool_content .= "
            <input type=\"hidden\" name=\"languageCourse\" value=\"$languageCourse\">
            <input type=\"hidden\" name=\"urlAppendPath\" value=\"$urlAppendPath\">
            <input type=\"hidden\" name=\"pathForm\" value=\"".str_replace("\\","/",realpath($pathForm)."/")."\" >

            <input type=\"hidden\" name=\"dbHostForm\" value=\"$dbHostForm\">
            <input type=\"hidden\" name=\"dbUsernameForm\" value=\"$dbUsernameForm\">
            <input type=\"hidden\" name=\"dbNameForm\" value=\"$dbNameForm\">
            <input type=\"hidden\" name=\"dbMyAdmin\" value=\"$dbMyAdmin\">
            <input type=\"hidden\" name=\"dbPassForm\" value=\"".@$dbPassForm."\">

            <input type=\"hidden\" name=\"urlForm\" value=\"$urlForm\">
            <input type=\"hidden\" name=\"emailForm\" value=\"$emailForm\">
            <input type=\"hidden\" name=\"nameForm\" value=\"$nameForm\">
            <input type=\"hidden\" name=\"surnameForm\" value=\"$surnameForm\">

            <input type=\"hidden\" name=\"loginForm\" value=\"$loginForm\">
            <input type=\"hidden\" name=\"passForm\" value=\"$passForm\">

            <input type=\"hidden\" name=\"languageForm\" value=\"".@$languageForm."\">

            <input type=\"hidden\" name=\"phpSysInfoURL\" value=\"$phpSysInfoURL\">

            <input type=\"hidden\" name=\"campusForm\" value=\"$campusForm\">
            <input type=\"hidden\" name=\"helpdeskForm\" value=\"$helpdeskForm\">
            <input type=\"hidden\" name=\"helpdeskmail\" value=\"".@$helpdeskmail."\">
            <input type=\"hidden\" name=\"institutionForm\" value=\"$institutionForm\">
            <input type=\"hidden\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\">
            <input type=\"hidden\" name=\"faxForm\" value=\"".@$faxForm."\">
            <input type=\"hidden\" name=\"postaddressForm\" value=\"".@$postaddressForm."\">
            <input type=\"hidden\" name=\"ldapserver\" value=\"".@$ldapserver."\">
            <input type=\"hidden\" name=\"dnldapserver\" value=\"".@$dnldapserver."\">
            <input type=\"hidden\" name=\"vodServer\" value=\"".@$vodServerForm."\">
            <input type=\"hidden\" name=\"MCU\" value=\"".@$MCUForm."\">
            <input type=\"hidden\" name=\"persoIsActive\" value=\"".@$persoIsActive."\">
	    
	    <input type=\"hidden\" name=\"encryptkey\" value=\"$encryptkey\">
";
}


// step 2 license

if(isset($_REQUEST['install2']) OR isset($_REQUEST['back2']))
{
	$langStepTitle = $langLicence;
	$langStep = $langStep2;
	$_SESSION['step']=2;
	$tool_content .= "
     <p>Tο e-Class είναι ελεύθερη εφαρμογή και διανέμεται σύμφωνα με την άδεια GNU General Public Licence (GPL).
     Παρακαλούμε διαβάστε την άδεια και κάνετε κλίκ στην 'Αποδοχή'.
     <a href=\"../info/license/gpl_print.txt\">(".$langPrintVers.")</a></p>
     
     <textarea wrap=\"virtual\" cols=\"65\" rows=\"15\">";
	$tool_content .= file_get_contents('../info/license/gpl.txt');
	$tool_content .= "</textarea><br/><br/>
                    <input type=\"submit\" name=\"back1\" value=\"< Πίσω\">
                    <input type=\"submit\" name=\"install3\" value=\"Αποδοχή>\" ></form>
                  ";
	draw($tool_content);
}

elseif(isset($_REQUEST['install3']) OR isset($_REQUEST['back3'])) {

	// step 3 mysql database settings
	$langStepTitle = $langDBSetting;
	$langStep = $langStep3;
	$_SESSION['step']=3;
	$tool_content .= "
	
		<p>".$langDBSettingIntro.".  ".$langAllFieldsRequired."</p>
           
	<table width=\"99%\">
		<thead>
			<tr>
				<th>".$langDBHost."</th>
				<td>
					<input type=\"text\" size=\"25\" name=\"dbHostForm\" value=\"".$dbHostForm."\">".$langEG." localhost
				</td>
			</tr>
			<tr>
				<th>".$langDBLogin."</th>
				<td>
					<input type=\"text\"  size=\"25\" name=\"dbUsernameForm\" value=\"".$dbUsernameForm."\">".$langEG." root
				</td>
			</tr>
			<tr>
				<th>".$langDBPassword."</th>
				<td>
					<input type=\"text\"  size=\"25\" name=\"dbPassForm\" value=\"$dbPassForm\">".$langEG." ".generePass(8)."
				</td>
			</tr>
			<tr>
				<th>".$langMainDB."</th>
				<td>
					<input type=\"text\"  size=\"25\" name=\"dbNameForm\" value=\"$dbNameForm\">(αν υπάρχει ήδη κάποια βάση δεδομένων με το όνομα eclass αλλάξτε το)
				</td>
			</tr>
			<tr>
				<th>URL του phpMyAdmin</th>
				<td>
					<input type=\"text\" size=\"25\" name=\"dbMyAdmin\" value=\"".$dbMyAdmin."\">Δεν χρειάζεται να το αλλάξετε
				</td>
			</tr>
			<tr>
				<th>URL του System info</th>
				<td>
					<input type=\"text\" size=\"25\" name=\"phpSysInfoURL\" value=\"".$phpSysInfoURL."\">Δεν χρειάζεται να το αλλάξετε
				</td>
			</tr>
		</thead>
	</table>
    <br/><br/>              
	<input type=\"submit\" name=\"back2\" value=\"< Πίσω\">
	<input type=\"submit\" name=\"install5\" value=\"Επόμενο >\">
</form>
                   ";
	draw($tool_content);
}	 // install3

// step 4 config settings

elseif(isset($_REQUEST['install5']) OR isset($_REQUEST['back4']))
{
	// Added by vagpits
	// Global variable persoIsActive
	if ($persoIsActive == "true") {
		$persoIsActiveSelTrue = "selected";
		$persoIsActiveSelFalse = "";
	} else {
		$persoIsActiveSelTrue = "";
		$persoIsActiveSelFalse = "selected";
	}
	$langStepTitle = $langCfgSetting;
	$langStep = $langStep4;
	$_SESSION['step']=4;
	$tool_content .=  "
        
        <p>Τα παρακάτω θα γραφτούν στο αρχείο <b>config.php</b>.</p>
        
		<table width=\"99%\">
			<thead>
				<tr>
					<th>".$langMainLang."</th>
					<td>
						 <select name=\"languageForm\">	";

	$dirname = "../modules/lang/";
	if($dirname[strlen($dirname)-1]!='/')
	$dirname.='/';
	$handle=opendir($dirname);
	while ($entries = readdir($handle))
	{
		if ($entries=='.'||$entries=='..'||$entries=='CVS')
		continue;
		if (is_dir($dirname.$entries))
		{
			$tool_content .= "<option value=\"$entries\"";
			if ($entries == $languageCourse)
			$tool_content .= " selected ";
			$tool_content .= ">$entries</option>";
		}
	}
	closedir($handle);
	$tool_content .= "
						</select>
					 </td>
				</tr>
				<tr>
					<th>URL του e-Class<font color=\"red\">*</font></th>
					<td>
						<input type=\"text\" size=\"40\" name=\"urlForm\" value=\"$urlForm\">
					</td>
				</tr>
				<tr>
					<th>".$langLocalPath."<font color=red>*</font></th>
					<td>
						<input type=text size=40 name=\"pathForm\" value=\"".realpath($pathForm)."/\">
					</td>
				</tr>
				<tr>
					<th>".$langAdminName."</th>
					<td>
						<input type=\"text\" size=\"40\" name=\"nameForm\" value=\"$nameForm\">
					</td>
				</tr>
				<tr>
					<th>".$langAdminSurname."</th>
					<td>
						<input type=\"text\" size=\"40\" name=\"surnameForm\" value=\"$surnameForm\">
					</td>
				</tr>

                        <tr>
            <th>
            
               ".$langAdminEmail."
           
            </th>
            <td>
            <input type=text size=40 name=\"emailForm\" value=\"$emailForm\">
            </td>
            </tr>

                        <tr>
                            <th>
                                
                                    ".$langAdminLogin."
                                
                            </th>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"loginForm\" value=\"$loginForm\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                
                                    ".$langAdminPass."
                               
                            </th>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"passForm\" value=\"$passForm\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                
                                    ".$langCampusName."
                               
                            </th>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"campusForm\" value=\"$campusForm\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                
                                    ".$langHelpDeskPhone."
                                
                            </th>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"helpdeskForm\" value=\"$helpdeskForm\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                
                                    ".$langHelpDeskFax."
                                
                            </th>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"faxForm\" value=\"$faxForm\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                
                                    ".$langHelpDeskEmail."
                                    <font color=\"red\">
                                        **
                                    </font color>
                               
                            </th>
                            <td>
                                <input type=text size=40 name=\"helpdeskmail\" value=\"$helpdeskmail\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                               
                                    ".$langInstituteShortName."
                                
                            </th>
                            <td>
                                <input type=text size=40 name=\"institutionForm\" value=\"$institutionForm\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                               
                                    ".$langInstituteName."
                                
                            </th>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                               
                                    ".$langInstitutePostAddress."
                                
                            </th>
                            <td>
                                <input type=text size=40 name=\"postaddressForm\" value=\"$postaddressForm\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                 
                                     LDAP εξυπηρέτης του Ιδρύματος
                               
                            </th>
                            <td>
                                 <input type=\"text\" size=\"40\" name=\"ldapserver\" value=\"$ldapserver\">
                             </td>
                        </tr>
                        <tr>
                            <th>
                                
                                    Base dn του LDAP εξυπηρέτη
                                </font>
                            </th>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"dnldapserver\" value=\"$dnldapserver\">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                
                                   Εξυπηρετητής video streaming 
                               
                            </th>
                            <td>
<script>
function set_video_input()
	{
		if(document.getElementById(\"video_check\").checked==true)
		{
			document.getElementById(\"video_input_div_text\").innerHTML='Πρόθεμα του τελικού URL με το οποίο θα σερβίρονται τα αποθηκευμένα στον video streaming εξυπηρετητή αρχεία<font color=\"red\">*';
			document.getElementById(\"video_input_div_input\").innerHTML='<input type=\"text\" size=\"20\" name=\"vodServerForm\" value=\"$vodServer\"><br>Πχ. mms://windows_media.server.gr/, rtsp://real.server.gr';
		}
		else{ document.getElementById(\"video_input_div_text\").innerHTML='';
		      document.getElementById(\"video_input_div_input\").innerHTML='';
		}
		

	}
</script>
		<input type=\"checkbox\" id=\"video_check\" onclick=\"set_video_input();\"/><br>
     </td>
     </tr>
			<tr>
				<th>
				 <div id=\"video_input_div_text\">
				 </div>
				</th>
				<td>
				 <div id=\"video_input_div_input\">
				</td>
			</tr>
                        <tr>
                            <th>
                                
                                   MCU (μονάδα ελέγχου για τηλεδιάσκεψη)
                                
                            </th>
                            <td>
<script>
function set_MCU()
	{
		if(document.getElementById(\"MCU_check\").checked==true)
		{
			document.getElementById(\"MCU_div_text\").innerHTML='<font size=\"2\" face=\"arial, helvetica\">Διεύθυνση MCU</font><font color=\"red\">*</font>';
			document.getElementById(\"MCU_div_input\").innerHTML='<input type=\"text\" size=\"20\" name=\"MCUForm\" value=\"$MCU\"><br>Πχ. rts.grnet.gr';
		}
		else{ document.getElementById(\"MCU_div_text\").innerHTML='';
		      document.getElementById(\"MCU_div_input\").innerHTML='';
		}
		

	}
</script>
		<input type=\"checkbox\" id=\"MCU_check\" onclick=\"set_MCU();\"/><br>
     </td>
     </tr>
			<tr>
				<th>
				 <div id=\"MCU_div_text\">
				 </div>
				</th>
				<td>
				 <div id=\"MCU_div_input\">
				</td>
			</tr>
                        <tr>
                            <th>
                                
                                    Personalization
                                
                            </th>
                            <td>
                            		<select name=\"persoIsActive\">
                            			<option value=\"true\" ".$persoIsActiveSelTrue.">true</option>
                            			<option value=\"false\" ".$persoIsActiveSelFalse.">false</option>
                            		</select>
                            </td>
                        </tr>
			
			
			
                         </thead>
                    </table>
                        <p>
                                <font color=\"red\">*
                                </font>
                                  = υποχρεωτικό</p>
                                
                        <p>
                            <font color=\"red\">**</font>
                                
                                     ".$langWarnHelpDesk."
                                </p><br/>
                                <input type=\"submit\" name=\"back3\" value=\"< Πίσω \">
                            
                                <input type=\"submit\" name=\"install6\" value='Επόμενο >'>
                           
                       ";

	draw($tool_content);

}

// step 5 last check before install

elseif(isset($_REQUEST['install6']))
{
	$pathForm = str_replace("\\\\", "/", $pathForm);
	//	chmod( "../config/config.php", 666 );
	//	chmod( "../config/config.php", 0666 );
	$langStepTitle = $langLastCheck;
	$langStep = $langStep5;
	$_SESSION['step']=5;
	$tool_content .=  "
        
        <p>
        Τα στοιχεία που δηλώσατε είναι τα παρακάτω:
        (Εκτυπώστε τα αν θέλετε να θυμάστε το συνθηματικό του διαχειριστή και τις άλλες ρυθμίσεις)</p>
        <ul id=\"installBullet\">
        <li>Γλώσσα : $languageForm</li>
        <li>Όνομα υπολογιστή : $dbHostForm</li>
        <li>Όνομα Χρήστη για τη Βάση Δεδομένων : $dbUsernameForm</li>
        <li>Συνθηματικό για τη Βάση Δεδομένων: $dbPassForm</li>
        <li>Κύρια Βάση Δεδομένων : $dbNameForm</li>
        <li>URL του phpMyAdmin : $dbMyAdmin</li>

        <li>URL του e-Class : $urlForm</li>
        <li>Toπικό path : $pathForm</li>
        <li>Email Διαχειριστή : $emailForm</li>
        <li>Όνομα Διαχειριστή : $nameForm</li>

        <li>Επώνυμο Διαχειριστή : $surnameForm</li>
        <li><b>Όνομα Χρήστη του Διαχειριστή : </b>$loginForm</li>
        <li><b>Συνθηματικό του Διαχειριστή : </b>$passForm</li>
        <li>Όνομα Πανεπιστημιακού Ιδρύματος : $campusForm</li>

        <li>Τηλέφωνο Helpdesk : $helpdeskForm</li>
        <li>Αριθμός Fax Helpdesk : $faxForm</li>
        <li>E-mail Helpdesk : $helpdeskmail</li>
        <li>Σύντομο όνομα του Ιδρύματος : $institutionForm</li>
        <li>URL του Ιδρύματος : $institutionUrlForm</li>
        <li>Ταχ. διεύθυνση του Ιδρύματος : $postaddressForm</li>
        <li>Εξυπηρετητής LDAP του Ιδρύματος : $ldapserver</li>
        <li>Base DN του εξυπηρετητή LDAP : $dnldapserver </li>
	<li>MCU: ".@$MCUForm." </li>
	<li>Vod Server: ".@$vodServerForm." </li>
	
        </ul>
        
                    <input type=\"submit\" name=\"back4\" value=\"< Πίσω\">
               
                    <input type=\"submit\" name=\"install7\" value=\"Eγκατάσταση του e-Class >\">
               
        </form>";

	draw($tool_content);
}
// step 6 installation successful

elseif(isset($_REQUEST['install7']))
{

	// database creation
	$langStepTitle = $langInstallEnd;
	$langStep = $langStep6;
	$_SESSION['step']=6;
	$db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
	if (mysql_errno()>0) // problem with server
	{
		$no = mysql_errno();     $msg = mysql_error();
		$tool_content .= "
		<table width = \"99%\">
				<tbody>
					<tr>
						<td class=\"extraMessage\">
						<u><b>[".$no."] - ".$msg."</b></u><br/>
						<p>Η MySQL  δεν λειτουργεί ή το όνομα χρήστη/συνθηματικό δεν είναι σωστό.<br/>
        Παρακαλούμε ελέγξετε τα στοιχεία σας: </p>
        <ul id=\"installBullet\">
        <li>Όνομα Υπολογιστή : ".$dbHostForm."</li>
        <li>Όνομα Χρήστη : ".$dbUsernameForm."</li>
        <li>Συνθηματικό  : ".$dbPassForm."</li>
        </ul>
        <p>Eπιστρέψτε στο βήμα 3 για να τα διορθώσετε.</p>
						
					</td>
					</tr>
				</tbody>
			</table><br/>
			<input type=\"submit\" name=\"install3\" value=\"< Επιστροφή στο βήμα 3\">
			</form>
        
        ";
		draw($tool_content);exit();
	}
	$mysqlMainDb = $dbNameForm;
	mysql_query("DROP DATABASE IF EXISTS ".$mysqlMainDb);
	if (mysql_version()) mysql_query("SET NAMES greek");
	if (mysql_version())
	$cdb=mysql_query("CREATE DATABASE $mysqlMainDb CHARACTER SET greek");
	else
	$cdb=mysql_query("CREATE DATABASE $mysqlMainDb");
	mysql_select_db ($mysqlMainDb);

	// drop old tables (if existed)
	mysql_query("DROP TABLE IF EXISTS admin");
	mysql_query("DROP TABLE IF EXISTS admin_announcements");
	mysql_query("DROP TABLE IF EXISTS agenda");
	mysql_query("DROP TABLE IF EXISTS annonces");
	mysql_query("DROP TABLE IF EXISTS auth");
	mysql_query("DROP TABLE IF EXISTS cours");
	mysql_query("DROP TABLE IF EXISTS cours_faculte");
	mysql_query("DROP TABLE IF EXISTS cours_user");
	mysql_query("DROP TABLE IF EXISTS faculte");
	mysql_query("DROP TABLE IF EXISTS institution");
	mysql_query("DROP TABLE IF EXISTS loginout");
	mysql_query("DROP TABLE IF EXISTS loginout_summary");
	mysql_query("DROP TABLE IF EXISTS monthly_summary");
	mysql_query("DROP TABLE IF EXISTS prof_request");
	mysql_query("DROP TABLE IF EXISTS user");

	#
	# table `annonces`
	#

	// if mysql > 4.1 then create tables with charset

	if (mysql_version())  {


		mysql_query("CREATE TABLE annonces (
      id mediumint(11) NOT NULL auto_increment,
      contenu text,
      temps date default NULL,
      code_cours varchar(20) default NULL,
      ordre mediumint(11) NOT NULL,
      PRIMARY KEY  (id))
      TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		#---------------------------------------------

		#
		# table admin_announcements
		#
		mysql_query("CREATE TABLE admin_announcements (
    		id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		gr_title VARCHAR(255) NULL,
		 gr_body VARCHAR(255) NULL,
		 gr_comment VARCHAR(255) NULL,
		 en_title VARCHAR(255) NULL,
		  en_body VARCHAR(255) NULL,
		en_comment VARCHAR(255) NULL,
		date DATE NOT NULL,
		visible ENUM('V', 'I') NOT NULL
		) TYPE = MyISAM DEFAULT CHARACTER SET=greek");

		# --------------------------------------------------------

		#
		# table `agenda`
		#

		mysql_query("CREATE TABLE `agenda` (
  `id` int(11) NOT NULL auto_increment,
  `lesson_event_id` int(11) NOT NULL default '0',
  `titre` varchar(200) NOT NULL default '',
  `contenu` text NOT NULL,
  `day` date NOT NULL default '0000-00-00',
  `hour` time NOT NULL default '00:00:00',
  `lasting` varchar(20) NOT NULL default '',
  `lesson_code` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		# --------------------------------------------------------

		#
		# table `cours`
		#

		mysql_query("CREATE TABLE `cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(20) default NULL,
  `languageCourse` varchar(15) default NULL,
  `intitule` varchar(250) default NULL,
  `description` text default NULL,
  `course_keywords` text default NULL,
  `course_addon` text default NULL,
  `faculte` varchar(100) default NULL,
  `visible` tinyint(4) default NULL,
  `cahier_charges` varchar(250) default NULL,
  `scoreShow` int(11) NOT NULL default '1',
  `titulaires` varchar(200) default NULL,
  `fake_code` varchar(20) default NULL,
  `departmentUrlName` varchar(30) default NULL,
  `departmentUrl` varchar(180) default NULL,
  `versionDb` varchar(10) NOT NULL default 'NEVER SET',
  `versionClaro` varchar(10) NOT NULL default 'NEVER SET',
  `lastVisit` date NOT NULL default '0000-00-00',
  `lastEdit` datetime NOT NULL default '0000-00-00 00:00:00',
  `expirationDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` ENUM( 'pre', 'post', 'other' ) DEFAULT 'pre' NOT NULL,
  `doc_quota` float NOT NULL default '40000000',
  `video_quota` float NOT NULL default '20000000',
  `group_quota` float NOT NULL default '40000000',
  `dropbox_quota` float NOT NULL default '40000000',
  `password` varchar(50) default NULL,
  `faculteid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cours_id`))
  TYPE=MyISAM DEFAULT CHARACTER SET='greek'");

		# --------------------------------------------------------

		#
		# Table `cours_faculte`
		#

		mysql_query("CREATE TABLE cours_faculte (
      id int(11) NOT NULL auto_increment,
      faculte varchar(100) NOT NULL,
      code varchar(20) NOT NULL,
      facid int(11) NOT NULL default '0',
      PRIMARY KEY  (id))
      TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		# --------------------------------------------------------

		#
		# Table `cours_user`
		#


		mysql_query("CREATE TABLE cours_user (
      code_cours varchar(30) NOT NULL default '0',
      user_id int(11) unsigned NOT NULL default '0',
      statut tinyint(4) NOT NULL default '0',
      role varchar(60) default NULL,
      team int(11) NOT NULL default '0',
      tutor int(11) NOT NULL default '0',
      PRIMARY KEY  (code_cours,user_id))
      TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		#
		# Table `faculte`
		#

		mysql_query("CREATE TABLE faculte (
      id int(11) NOT NULL auto_increment,
      code varchar(10) NOT NULL,
      name varchar(100) NOT NULL,
      number int(11) NOT NULL default 0,
      generator int(11) NOT NULL default 0,
      PRIMARY KEY  (id))
      TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		# --------------------------------------------------------

		mysql_query("INSERT INTO faculte VALUES (1, 'TMA', 'Τμήμα 1', 10, 100)");
		mysql_query("INSERT INTO faculte VALUES (2, 'TMB', 'Τμήμα 2', 20, 100)");
		mysql_query("INSERT INTO faculte VALUES (3, 'TMC', 'Τμήμα 3', 30, 100)");
		mysql_query("INSERT INTO faculte VALUES (4, 'TMD', 'Τμήμα 4', 40, 100)");
		mysql_query("INSERT INTO faculte VALUES (5, 'TME', 'Τμήμα 5', 50, 100)");


		#
		# Table `user`
		#


		mysql_query("CREATE TABLE user (
      user_id mediumint unsigned NOT NULL auto_increment,
      nom varchar(60) default NULL,
      prenom varchar(60) default NULL,
      username varchar(20) default 'empty',
      password varchar(50) default 'empty',
      email varchar(100) default NULL,
      statut tinyint(4) default NULL,
      phone varchar(20) default NULL,
          department int(10) default NULL,
      inst_id int(11) default NULL,
      am varchar(20) default NULL,
      registered_at int(10) NOT NULL default '0',
      expires_at int(10) NOT NULL default '0',
       `perso` enum('yes','no') NOT NULL default 'no',
	`lang` enum('el','en') DEFAULT 'el' NOT NULL,	
  	`announce_flag` date NOT NULL default '0000-00-00',
 	 `doc_flag` date NOT NULL default '0000-00-00',
  	`forum_flag` date NOT NULL default '0000-00-00',
     PRIMARY KEY  (user_id))
      TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		mysql_query("CREATE TABLE admin (
      idUser mediumint unsigned  NOT NULL default '0',
      UNIQUE KEY idUser (idUser))
      TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		mysql_query("CREATE TABLE loginout (
      idLog mediumint(9) unsigned NOT NULL auto_increment,
      id_user mediumint(9) unsigned NOT NULL default '0',
      ip char(16) NOT NULL default '0.0.0.0',
      loginout.when datetime NOT NULL default '0000-00-00 00:00:00',
      loginout.action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
      PRIMARY KEY  (idLog))
      TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		// haniotak:
		// table for loginout rollups
		// only contains LOGIN events summed up by a period (typically weekly)
		mysql_query("CREATE TABLE loginout_summary (
        id mediumint unsigned NOT NULL auto_increment,
        login_sum int(11) unsigned  NOT NULL default '0',
        start_date datetime NOT NULL default '0000-00-00 00:00:00',
        end_date datetime NOT NULL default '0000-00-00 00:00:00',
        PRIMARY KEY  (id))
        TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		//table keeping data for monthly reports
		mysql_query("CREATE TABLE monthly_summary (
        id mediumint unsigned NOT NULL auto_increment,
        `month` varchar(20)  NOT NULL default '0',
        profesNum int(11) NOT NULL default '0',
        studNum int(11) NOT NULL default '0',
        visitorsNum int(11) NOT NULL default '0',
        coursNum int(11) NOT NULL default '0',
        logins int(11) NOT NULL default '0',
        details text NOT NULL default '',
        PRIMARY KEY  (id))
        TYPE=MyISAM DEFAULT CHARACTER SET=greek");



		// encrypt the admin password into DB
		$password_encrypted = md5($passForm);
		$exp_time = time() + 140000000;
		mysql_query("INSERT INTO `user` (`prenom`, `nom`, `username`, `password`, `email`, `statut`,`registered_at`,`expires_at`)
    VALUES ('$nameForm', '$surnameForm', '$loginForm','$password_encrypted','$emailForm','1',".time().",".$exp_time.")");
		$idOfAdmin=mysql_insert_id();
		mysql_query("INSERT INTO loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action) VALUES ('', '".$idOfAdmin."', '".$REMOTE_ADDR."', NOW(), 'LOGIN')");

		#add admin in list of admin
		mysql_query("INSERT INTO admin VALUES ('".$idOfAdmin."')");


		#
		# Table structure for table `institution`
		#

		mysql_query("CREATE TABLE institution (
            inst_id int(11) NOT NULL auto_increment,
             nom varchar(100) NOT NULL default '',
             ldapserver varchar(30) NOT NULL default '',
             basedn varchar(40) NOT NULL default '',
         PRIMARY KEY  (inst_id))
          TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		#
		# Dumping data for table `institution`
		#

		mysql_query("INSERT INTO institution (inst_id, nom, ldapserver, basedn) VALUES ('1', '$institutionForm', '$ldapserver', '$dnldapserver')");

		#
		# Table structure for table `prof_request`
		#

		mysql_query("CREATE TABLE `prof_request` (
          `rid` int(11) NOT NULL auto_increment,
            `profname` varchar(255) NOT NULL default '',
              `profsurname` varchar(255) NOT NULL default '',
            `profuname` varchar(255) NOT NULL default '',
            `profpassword` varchar(255) NOT NULL default '',
          `profemail` varchar(255) NOT NULL default '',
            `proftmima` varchar(255) default NULL,
              `profcomm` varchar(20) default NULL,
            `status` int(11) default NULL,
        `date_open` datetime default NULL,
        `date_closed` datetime default NULL,
        `comment` text default NULL,
        PRIMARY KEY  (`rid`))
        TYPE=MyISAM DEFAULT CHARACTER SET=greek");


		###############PHPMyAdminTables##################

		mysql_query("
    CREATE TABLE `pma_bookmark` (
       id int(11) NOT NULL auto_increment,
       dbase varchar(255) NOT NULL,
       user varchar(255) NOT NULL,
       label varchar(255) NOT NULL,
       query text NOT NULL,
       PRIMARY KEY (id))
       TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		mysql_query("
CREATE TABLE `pma_relation` (
       `master_db` varchar(64) NOT NULL default '',
       `master_table` varchar(64) NOT NULL default '',
       `master_field` varchar(64) NOT NULL default '',
       `foreign_db` varchar(64) NOT NULL default '',
       `foreign_table` varchar(64) NOT NULL default '',
       `foreign_field` varchar(64) NOT NULL default '',
       PRIMARY KEY (`master_db`, `master_table`, `master_field`),
       KEY foreign_field (foreign_db, foreign_table))
       TYPE=MyISAM DEFAULT CHARACTER SET=greek");


		mysql_query("
    CREATE TABLE `pma_table_info` (
       `db_name` varchar(64) NOT NULL default '',
       `table_name` varchar(64) NOT NULL default '',
       `display_field` varchar(64) NOT NULL default '',
       PRIMARY KEY (`db_name`, `table_name`))
       TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		mysql_query("
     CREATE TABLE `pma_table_coords` (
       `db_name` varchar(64) NOT NULL default '',
       `table_name` varchar(64) NOT NULL default '',
       `pdf_page_number` int NOT NULL default '0',
       `x` float unsigned NOT NULL default '0',
       `y` float unsigned NOT NULL default '0',
       PRIMARY KEY (`db_name`, `table_name`, `pdf_page_number`))
       TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		mysql_query("
     CREATE TABLE `pma_pdf_pages` (
       `db_name` varchar(64) NOT NULL default '',
       `page_nr` int(10) unsigned NOT NULL auto_increment,
       `page_descr` varchar(50) NOT NULL default '',
       PRIMARY KEY (page_nr),
       KEY (db_name))
       TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		mysql_query("
CREATE TABLE `pma_column_comments` (
       id int(5) unsigned NOT NULL auto_increment,
       db_name varchar(64) NOT NULL default '',
       table_name varchar(64) NOT NULL default '',
       column_name varchar(64) NOT NULL default '',
       comment varchar(255) NOT NULL default '',
       PRIMARY KEY (id),
       UNIQUE KEY db_name (db_name, table_name, column_name))
       TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		// New table auth for authentication methods
		// added by kstratos
		mysql_query("
CREATE TABLE `auth` (
  `auth_id` int(2) NOT NULL auto_increment,
  `auth_name` varchar(20) NOT NULL default '',
  `auth_settings` text NOT NULL default '',
  `auth_instructions` text NOT NULL default '',
  `auth_default` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`auth_id`))
  TYPE=MyISAM DEFAULT CHARACTER SET=greek");

		mysql_query("INSERT INTO `auth` VALUES (1, 'eclass', '', '', 1)");
		mysql_query("INSERT INTO `auth` VALUES (2, 'pop3', '', '', 0)");
		mysql_query("INSERT INTO `auth` VALUES (3, 'imap', '', '', 0)");
		mysql_query("INSERT INTO `auth` VALUES (4, 'ldap', '', '', 0)");
		mysql_query("INSERT INTO `auth` VALUES (5, 'db', '', '', 0)");


		#
		# Table passwd_reset (used by the password reset module)
		#

		mysql_query("
			CREATE TABLE `passwd_reset` (
  			`user_id` int(11) NOT NULL,
  			`hash` varchar(40) NOT NULL,
  			`password` varchar(8) NOT NULL,
  			`datetime` datetime NOT NULL
			) TYPE=MyISAM DEFAULT CHARSET=greek");

	} else {
		mysql_query("CREATE TABLE annonces (
      id mediumint(11) NOT NULL auto_increment,
      contenu text,
      temps date default NULL,
      code_cours varchar(20) default NULL,
      ordre mediumint(11) NOT NULL,
      PRIMARY KEY  (id))
      TYPE=MyISAM");

		# -------------------------------
		#
		# table admin_announcements
		#
		mysql_query("CREATE TABLE admin_announcements (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		gr_title VARCHAR(255) NULL ,
		 gr_body VARCHAR(255) NULL ,
		 gr_comment VARCHAR(255) NULL ,
		en_title VARCHAR(255) NULL ,
		  en_body VARCHAR(255) NULL ,
			en_comment VARCHAR(255) NULL ,
			date  DATE NOT NULL ,
			visible ENUM('V', 'I') NOT NULL
			) TYPE = MYISAM");


		# --------------------------------------------------------

		#
		# table `agenda`
		#

		mysql_query("CREATE TABLE `agenda` (
  	`id` int(11) NOT NULL auto_increment,
  	`lesson_event_id` int(11) NOT NULL default '0',
  	`titre` varchar(200) NOT NULL default '',
  	`contenu` text NOT NULL,
  	`day` date NOT NULL default '0000-00-00',
  	`hour` time NOT NULL default '00:00:00',
  	`lasting` varchar(20) NOT NULL default '',
  	`lesson_code` varchar(50) NOT NULL default '',
  	PRIMARY KEY  (`id`)
	) TYPE=MyISAM ");

		# --------------------------------------------------------

		#
		# table `cours`
		#

		mysql_query("CREATE TABLE `cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(20) default NULL,
  `languageCourse` varchar(15) default NULL,
  `intitule` varchar(250) default NULL,
  `description` text default NULL,
  `course_objectives` text default NULL,
  `course_prerequisites` text default NULL,
  `course_keywords` text default NULL,
	`course_addon` text default NULL,
  `course_references` text default NULL,
  `faculte` varchar(100) default NULL,
  `visible` tinyint(4) default NULL,
  `cahier_charges` varchar(250) default NULL,
  `scoreShow` int(11) NOT NULL default '1',
  `titulaires` varchar(200) default NULL,
  `fake_code` varchar(20) default NULL,
  `departmentUrlName` varchar(30) default NULL,
  `departmentUrl` varchar(180) default NULL,
  `versionDb` varchar(10) NOT NULL default 'NEVER SET',
  `versionClaro` varchar(10) NOT NULL default 'NEVER SET',
  `lastVisit` date NOT NULL default '0000-00-00',
  `lastEdit` datetime NOT NULL default '0000-00-00 00:00:00',
  `expirationDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` ENUM( 'pre', 'post', 'other' ) DEFAULT 'pre' NOT NULL,
  `doc_quota` float NOT NULL default '40000000',
  `video_quota` float NOT NULL default '20000000',
  `group_quota` float NOT NULL default '40000000',
  `dropbox_quota` float NOT NULL default '40000000',
  `password` varchar(50) default NULL,
  `faculteid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cours_id`))
  TYPE=MyISAM");

		# --------------------------------------------------------

		#
		# Table `cours_faculte`
		#

		mysql_query("CREATE TABLE cours_faculte (
      id int(11) NOT NULL auto_increment,
      faculte varchar(100) NOT NULL,
      code varchar(20) NOT NULL,
      facid int(11) NOT NULL default '0',
      PRIMARY KEY  (id))
      TYPE=MyISAM");

		# --------------------------------------------------------

		#
		# Table `cours_user`
		#


		mysql_query("CREATE TABLE cours_user (
      code_cours varchar(30) NOT NULL default '0',
      user_id int(11) unsigned NOT NULL default '0',
      statut tinyint(4) NOT NULL default '0',
      role varchar(60) default NULL,
      team int(11) NOT NULL default '0',
      tutor int(11) NOT NULL default '0',
      PRIMARY KEY  (code_cours,user_id))
      TYPE=MyISAM");

		#
		# Table `faculte`
		#

		mysql_query("CREATE TABLE faculte (
      id int(11) NOT NULL auto_increment,
      code varchar(10) NOT NULL,
      name varchar(100) NOT NULL,
      number int(11) NOT NULL default 0,
      generator int(11) NOT NULL default 0,
      PRIMARY KEY  (id))
      TYPE=MyISAM");

		# --------------------------------------------------------

		mysql_query("INSERT INTO faculte VALUES (1, 'TMA', 'Τμήμα 1', 10, 100)");
		mysql_query("INSERT INTO faculte VALUES (2, 'TMB', 'Τμήμα 2', 20, 100)");
		mysql_query("INSERT INTO faculte VALUES (3, 'TMC', 'Τμήμα 3', 30, 100)");
		mysql_query("INSERT INTO faculte VALUES (4, 'TMD', 'Τμήμα 4', 40, 100)");
		mysql_query("INSERT INTO faculte VALUES (5, 'TME', 'Τμήμα 5', 50, 100)");


		#
		# Table `user`
		#


		mysql_query("CREATE TABLE user (
      user_id mediumint unsigned NOT NULL auto_increment,
      nom varchar(60) default NULL,
      prenom varchar(60) default NULL,
      username varchar(20) default 'empty',
      password varchar(50) default 'empty',
      email varchar(100) default NULL,
      statut tinyint(4) default NULL,
      phone varchar(20) default NULL,
          department int(10) default NULL,
      inst_id int(11) default NULL,
      am varchar(20) default NULL,
      registered_at int(10) NOT NULL default '0',
      expires_at int(10) NOT NULL default '0',
       `perso` enum('yes','no') NOT NULL default 'no',
	`lang` enum('el','en') DEFAULT 'el' NOT NULL,	
  	`announce_flag` date NOT NULL default '0000-00-00',
 	 `doc_flag` date NOT NULL default '0000-00-00',
  	`forum_flag` date NOT NULL default '0000-00-00',
     PRIMARY KEY  (user_id))
      TYPE=MyISAM");

		mysql_query("CREATE TABLE admin (
      idUser mediumint unsigned  NOT NULL default '0',
      UNIQUE KEY idUser (idUser))
      TYPE=MyISAM");

		mysql_query("CREATE TABLE loginout (
      idLog mediumint(9) unsigned NOT NULL auto_increment,
      id_user mediumint(9) unsigned NOT NULL default '0',
      ip char(16) NOT NULL default '0.0.0.0',
      loginout.when datetime NOT NULL default '0000-00-00 00:00:00',
      loginout.action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
      PRIMARY KEY  (idLog))
      TYPE=MyISAM");

		// haniotak:
		// table for loginout rollups
		// only contains LOGIN events summed up by a period (typically weekly)
		mysql_query("CREATE TABLE loginout_summary (
        id mediumint unsigned NOT NULL auto_increment,
        login_sum int(11) unsigned  NOT NULL default '0',
        start_date datetime NOT NULL default '0000-00-00 00:00:00',
        end_date datetime NOT NULL default '0000-00-00 00:00:00',
        PRIMARY KEY  (id))
        TYPE=MyISAM");

		//table keeping data for monthly reports
		mysql_query("CREATE TABLE monthly_summary (
        id mediumint unsigned NOT NULL auto_increment,
        `month` varchar(20)  NOT NULL default '0',
        profesNum int(11) NOT NULL default '0',
        studNum int(11) NOT NULL default '0',
        visitorsNum int(11) NOT NULL default '0',
        coursNum int(11) NOT NULL default '0',
        logins int(11) NOT NULL default '0',
        details text NOT NULL default '',
        PRIMARY KEY  (id))
        TYPE=MyISAM");


		// encrypt the admin password before storing it, into DB
		/*
		include '../modules/auth/auth.inc.php';
		$crypt = new Encryption;
		$key = $encryptkey;
		$pswdlen = "20";
		$password_encrypted = $crypt->encrypt($key, $passForm, $pswdlen);
		*/
		$password_encrypted = md5($passForm);
		$exp_time = time() + 140000000;

		mysql_query("INSERT INTO `user` (`prenom`, `nom`, `username`, `password`, `email`, `statut`, `registered_at`,`expires_at`)
    VALUES ('$nameForm', '$surnameForm', '$loginForm','$password_encrypted','$emailForm','1',".time().",".$exp_time.")");
		$idOfAdmin=mysql_insert_id();
		mysql_query("INSERT INTO loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action) VALUES ('', '".$idOfAdmin."', '".$REMOTE_ADDR."', NOW(), 'LOGIN')");

		#add admin in list of admin
		mysql_query("INSERT INTO admin VALUES ('".$idOfAdmin."')");


		#
		# Table structure for table `institution`
		#

		mysql_query("CREATE TABLE institution (
            inst_id int(11) NOT NULL auto_increment,
             nom varchar(100) NOT NULL default '',
             ldapserver varchar(30) NOT NULL default '',
             basedn varchar(40) NOT NULL default '',
         PRIMARY KEY  (inst_id))
          TYPE=MyISAM");

		#
		# Dumping data for table `institution`
		#

		mysql_query("INSERT INTO institution (inst_id, nom, ldapserver, basedn) VALUES ('1', '$institutionForm', '$ldapserver', '$dnldapserver')");

		#
		# Table structure for table `prof_request`
		#

		mysql_query("CREATE TABLE `prof_request` (
          `rid` int(11) NOT NULL auto_increment,
            `profname` varchar(255) NOT NULL default '',
              `profsurname` varchar(255) NOT NULL default '',
            `profuname` varchar(255) NOT NULL default '',
            `profpassword` varchar(255) NOT NULL default '',
          `profemail` varchar(255) NOT NULL default '',
            `proftmima` varchar(255) default NULL,
              `profcomm` varchar(20) default NULL,
            `status` int(11) default NULL,
        `date_open` datetime default NULL,
        `date_closed` datetime default NULL,
        `comment` text default NULL,
        PRIMARY KEY  (`rid`))
        TYPE=MyISAM");


		###############PHPMyAdminTables##################

		mysql_query("
    CREATE TABLE `pma_bookmark` (
       id int(11) DEFAULT '0' NOT NULL auto_increment,
       dbase varchar(255) NOT NULL,
       user varchar(255) NOT NULL,
       label varchar(255) NOT NULL,
       query text NOT NULL,
       PRIMARY KEY (id))
       TYPE=MyISAM");

		mysql_query("
CREATE TABLE `pma_relation` (
       `master_db` varchar(64) NOT NULL default '',
       `master_table` varchar(64) NOT NULL default '',
       `master_field` varchar(64) NOT NULL default '',
       `foreign_db` varchar(64) NOT NULL default '',
       `foreign_table` varchar(64) NOT NULL default '',
       `foreign_field` varchar(64) NOT NULL default '',
       PRIMARY KEY (`master_db`, `master_table`, `master_field`),
       KEY foreign_field (foreign_db, foreign_table))
       TYPE=MyISAM ");


		mysql_query("
    CREATE TABLE `pma_table_info` (
       `db_name` varchar(64) NOT NULL default '',
       `table_name` varchar(64) NOT NULL default '',
       `display_field` varchar(64) NOT NULL default '',
       PRIMARY KEY (`db_name`, `table_name`))
       TYPE=MyISAM");

		mysql_query("
     CREATE TABLE `pma_table_coords` (
       `db_name` varchar(64) NOT NULL default '',
       `table_name` varchar(64) NOT NULL default '',
       `pdf_page_number` int NOT NULL default '0',
       `x` float unsigned NOT NULL default '0',
       `y` float unsigned NOT NULL default '0',
       PRIMARY KEY (`db_name`, `table_name`, `pdf_page_number`))
       TYPE=MyISAM");

		mysql_query("
     CREATE TABLE `pma_pdf_pages` (
       `db_name` varchar(64) NOT NULL default '',
       `page_nr` int(10) unsigned NOT NULL auto_increment,
       `page_descr` varchar(50) NOT NULL default '',
       PRIMARY KEY (page_nr),
       KEY (db_name))
       TYPE=MyISAM");

		mysql_query("
CREATE TABLE `pma_column_comments` (
       id int(5) unsigned NOT NULL auto_increment,
       db_name varchar(64) NOT NULL default '',
       table_name varchar(64) NOT NULL default '',
       column_name varchar(64) NOT NULL default '',
       comment varchar(255) NOT NULL default '',
       PRIMARY KEY (id),
       UNIQUE KEY db_name (db_name, table_name, column_name))
       TYPE=MyISAM");

		// New table auth for authentication methods
		// added by kstratos
		mysql_query("
CREATE TABLE `auth` (
  `auth_id` int(2) NOT NULL auto_increment,
  `auth_name` varchar(20) NOT NULL default '',
  `auth_settings` text NOT NULL default '',
  `auth_instructions` text NOT NULL default '',
  `auth_default` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`auth_id`))
  TYPE=MyISAM");

		mysql_query("INSERT INTO `auth` VALUES (1, 'eclass', '', '', 1)");
		mysql_query("INSERT INTO `auth` VALUES (2, 'pop3', '', '', 0)");
		mysql_query("INSERT INTO `auth` VALUES (3, 'imap', '', '', 0)");
		mysql_query("INSERT INTO `auth` VALUES (4, 'ldap', '', '', 0)");
		mysql_query("INSERT INTO `auth` VALUES (5, 'db', '', '', 0)");

		#
		# Table passwd_reset (used by the password reset module)
		#

		mysql_query("
			CREATE TABLE `passwd_reset` (
  			`user_id` int(11) NOT NULL,
  			`hash` varchar(40) NOT NULL,
  			`password` varchar(8) NOT NULL,
  			`datetime` datetime NOT NULL
			) TYPE=MyISAM");

		//dhmiourgia full text indexes
		mysql_query("ALTER TABLE `annonces` ADD FULLTEXT `annonces` (`contenu` ,`code_cours`)");
		mysql_query("ALTER TABLE `cours` ADD FULLTEXT `cours` (`code` ,`description` ,`intitule` ,`course_objectives` ,`course_prerequisites` ,`course_keywords` ,`course_references`)");


	}

	// create config, courses and video catalogs
	mkdir ("../config", 0777);
	mkdir("../courses", 0777);
	mkdir("../video", 0777);

	// creation of config.php
	$fd=@fopen("../config/config.php", "w");
	$langStepTitle = $langInstallEnd;
	$langStep = $langStep6;
	if (!$fd) {

		$tool_content .= "
                <br>
                <b>Παρουσιάστηκε σφάλμα!</b>
                <br><br>
        Δεν είναι δυνατή η δημιουργία του αρχείου config.php.<br><br>
        Παρακαλούμε ελέγξτε τα δικαιώματα πρόσβασης στους υποκαταλόγους του eclass
        και δοκιμάστε ξανά την εγκατάσταση.\n";
	} else {


		$stringConfig='<?php
/*
=============================================================================
           GUnet e-Class 2.0
        E-learning and Course Management Program
================================================================================
        Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".

           Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
==============================================================================
*/


/***************************************************************
*           config file of e-Class
****************************************************************
File has been chmoded 0444 by install.php.
chmod 0666 (Win: remove read-only file property) to edit manually
*****************************************************************/

// This file was generate by script /install/index.php
// on '.date("r").'
// REMOTE_ADDR : 		'.$_SERVER['REMOTE_ADDR'].' = '.gethostbyaddr($_SERVER['REMOTE_ADDR']).'
// REMOTE_PORT : 		'.$_SERVER['REMOTE_PORT'].'
// HTTP_USER_AGENT : 	'.$_SERVER['HTTP_USER_AGENT'].'
// SERVER_NAME :		'.$_SERVER['SERVER_NAME'].'
// HTTP_COOKIE :		'.$_SERVER['HTTP_COOKIE'].'


$urlServer	=	"'.$urlForm.'";
$urlAppend	=	"'.$urlAppendPath.'";
$webDir		=	"'.str_replace("\\","/",realpath($pathForm)."/").'" ;

$mysqlServer="'.$dbHostForm.'";
$mysqlUser="'.$dbUsernameForm.'";
$mysqlPassword="'.$dbPassForm.'";
$mysqlMainDb="'.$mysqlMainDb.'";
$phpMyAdminURL="'.$dbMyAdmin.'";
$phpSysInfoURL="'.$phpSysInfoURL.'";
$emailAdministrator="'.$emailForm.'";
$administratorName="'.$nameForm.'";
$administratorSurname="'.$surnameForm.'";
$siteName="'.$campusForm.'";

$telephone="'.$helpdeskForm.'";
$fax="'.$faxForm.'";

$emailhelpdesk="'.$helpdeskmail.'";
$Institution="'.$institutionForm.'";
$InstitutionUrl="'.$institutionUrlForm.'";
$postaddress="'.$postaddressForm.'";
$color1="#F5F5F5"; // light grey
$color2="#E6E6E6"; // less light grey for bicolored tables

// available: greek and english
$language = "'.$languageForm.'";

$userMailCanBeEmpty = true;
$mainInterfaceWidth ="600";

$bannerPath = "images/gunet/banner.jpg";
$colorLight = "#F5F5F5";
$colorMedium = "#004571";
$colorDark = "#000066";

$have_latex = FALSE;

$persoIsActive = '.$persoIsActive.';
$durationAccount = "126144000";

'.($vodServer==''?'//':'').'$vodServer="'.$vodServer.'";
'.($MCU==''?'//':'').'$MCU="'.$MCU.'";
$encryptedPasswd = true;
?>';
		// write to file
		fwrite($fd, $stringConfig);
		// message

		$tool_content .= "
               <table width = \"99%\">
				<tbody>
					<tr>
						<td class=\"extraMessageOK\">
						 
						<p>Η εγκατάσταση ολοκληρώθηκε με επιτυχία!
                Κάντε κλίκ παρακάτω για να μπείτε στο e-class.</p>
                <br>
                <p><b>
                Συμβουλή: Για να προστατέψετε το e-class, αλλάξτε τα δικαιώματα πρόσβασης των αρχείων
                <tt>/config/config.php</tt> και <tt>/install/index.php</tt> και
                επιτρέψτε μόνο ανάγνωση (CHMOD 444).</b></p>
						
					</td>
					</tr>
				</tbody>
			</table>
                <br>
               
    </form>
    <form action=\"../\">
    <input type=\"submit\" value=\"Είσοδος στο e-Class\">
	</form>";
		draw($tool_content);
	}       // τέλος ελέγχου για δικαιώματα
}	// end of step 6

// step 1 requirements

elseif (isset($_REQUEST['install1']) || isset($_REQUEST['back1']))
{
	$langStepTitle = $langRequirements;
	$langStep = $langStep1;
	$_SESSION['step']=1;
	$configErrorExists = false;

	if (empty($SERVER_SOFTWARE)) {

		$errorContent[]= "
		<table width = \"99%\">
				<tbody>
					<tr>
						<td class=\"extraMessage\">
						
        <p><b>Προσοχή!</b> Φαίνεται πως η επιλογή register_globals
        στο αρχείο php.ini δεν είναι ενεργοποιημένη. Χωρίς αυτήν το
        e-class δεν μπορεί να λειτουργήσει. Παρακαλούμε διορθώστε το
        αρχείο php.ini ώστε να περιέχει τη γραμμή:</p>
        <p><b>register_globals = On</b></p>
       <p>Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε
        τις οδηγίες εγκατάστασης στο αρχείο
        <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.</p>
						
					</td>
					</tr>
				</tbody>
			</table>
        ";
		$configErrorExists = true;
	}

	if (!ini_get('short_open_tag')) {
		$errorContent[]= "
		<table width = \"99%\">
				<tbody>
					<tr>
						<td class=\"extraMessage\">
						
        <p><b>Προσοχή!</b> Φαίνεται πως η επιλογή short_open_tag
        στο αρχείο php.ini δεν είναι ενεργοποιημένη. Χωρίς αυτήν το
        e-class δεν μπορεί να λειτουργήσει. Παρακαλούμε διορθώστε το
        αρχείο php.ini ώστε να περιέχει τη γραμμή:</p>
        <p><b>short_open_tag = On</b></p>
        <p>Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε
        τις οδηγίες εγκατάστασης στο αρχείο
        <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.</p>
						
					</td>
					</tr>
				</tbody>
			</table>
		";
		$configErrorExists = true;
	}

	$mkd=@mkdir("../config", 0777);
	if(!$mkd){
		$errorContent[]= "
		<table width = \"99%\">
				<tbody>
					<tr>
						<td class=\"extraMessage\">
						
        <p><b>Προσοχή!</b> Φαίνεται πως ο οδηγός εγκατάστασης δεν έχει 
        δικαιώματα δημιουργίας του κατάλογου <b>/config</b>.<br/>
        Χωρίς δικαιώματα δημιουργίας, ο οδηγός εγκατάστασης δεν μπορεί να συνεχίσει. 
        Παρακαλούμε διορθώστε τα δικαιώματα.
        <br/>
        Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε
        τις οδηγίες εγκατάστασης στο αρχείο
        <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.</p>
						
					</td>
					</tr>
				</tbody>
			</table>
		";
		$configErrorExists = true;

	} else rmdir("../config");

	// courses directory
	$mkd = @mkdir("../courses", 0777);
	if(!$mkd){
		$errorContent[]= "
		<table width = \"99%\">
				<tbody>
					<tr>
						<td class=\"extraMessage\">
						
        <p><b>Προσοχή!</b> Φαίνεται πως ο οδηγός εγκατάστασης δεν έχει 
        δικαιώματα δημιουργίας του κατάλογου <b>/courses</b>.<br/>
        Χωρίς δικαιώματα δημιουργίας, ο οδηγός εγκατάστασης δεν μπορεί να συνεχίσει. 
        Παρακαλούμε διορθώστε τα δικαιώματα.
        <br/>
        Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε
        τις οδηγίες εγκατάστασης στο αρχείο
        <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.</p>
						
					</td>
					</tr>
				</tbody>
			</table>
		";
		$configErrorExists = true;
	} else rmdir("../courses");


	if($configErrorExists) {
		$tool_content .= implode("<br/>", $errorContent);
		$tool_content .= "</form>";
		draw($tool_content);
		exit();
	}

	$tool_content .= "
     
    <u>Έλεγχος προαπαιτούμενων προγραμμάτων για τη λειτουργία του e-Class</u>
    <p>
        Webserver (<em>βρέθηκε <b>".$_SERVER['SERVER_SOFTWARE']."</b></em>) 
        με υποστήριξη PHP (<em>βρέθηκε <b>PHP ".phpversion()."</b></em>).</p>
    ";

	$tool_content .= "<u>Απαιτούμενα PHP modules</u>";
	$tool_content .= "<ul id=\"installBullet\">";
	warnIfExtNotLoaded("standard");
	warnIfExtNotLoaded("session");
	warnIfExtNotLoaded("mysql");
	warnIfExtNotLoaded("gd");
	warnIfExtNotLoaded("mbstring");
	warnIfExtNotLoaded("zlib");
	warnIfExtNotLoaded("pcre");
	$tool_content .= "</ul><u>Προαιρετικά PHP modules</u>";
	$tool_content .= "<ul id=\"installBullet\">";
	warnIfExtNotLoaded("ldap");
	$tool_content .= "</ul>";
	$tool_content .= "
    
    <u>Άλλες απαιτήσεις συστήματος</u>
    <ul id=\"installBullet\">
    <li>
    Μια βάση δεδομένων MySQL, στην οποία έχετε λογαριασμό με δικαιώματα να δημιουργείτε και να διαγράφετε βάσεις δεδομένων.
    </li>
    <li>
        Δικαιώματα εγγραφής στον κατάλογο <tt>include/</tt>.
    </li>
    <li>
        Δικαιώματα εγγραφής στον κατάλογο όπου το e-class έχει αποσυμπιεστεί.
    </li>
    </ul>
    
    <u>Επιπρόσθετη λειτουργικότητα:</u>
     <ul id=\"installBullet\">
     <li>Εάν επιθυμείτε να υποστηρίζετε streaming για τα αρχεία video που θα αποτελούν μέρος του υλικού των αποθηκευμένων μαθημάτων θα πρέπει να υπάρχει εγκατεστημένος streaming server σύμφωνα με τις οδηγίες που θα βρείτε στο εγχειρίδιο τάδε. 
    </li>
    <li>
Το e-Class θα εγκαταστήσει το δικό του διαχειριστικό εργαλείο μέσω web των βάσεων δεδομένων MySQL (<a
href=\"http://www.phpmyadmin.net\" target=_blank>phpMyAdmin</a>) αλλά
μπορείτε να χρησιμοποιήσετε και το δικό σας.
</li></ul>
<p>
Πριν προχωρήσετε στην εγκατάσταση τυπώστε και διαβάστε προσεκτικά τις
<a href=\"install.html\" target=_blank>Οδηγίες Εγκατάστασης</a>.
</p>
<p>
Επίσης, γενικές οδηγίες για την πλατφόρμα μπορείτε να διαβάσετε <a href=\"../README.txt\" target=_blank>εδώ</a>.
</p>
<br>

<input type=\"submit\" name=\"install2\" value=\"Επόμενο >\">
</form>
";
	draw($tool_content);

} else {

	$tool_content .= "
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
  <head>
    <title>Καλωσορίσατε στον οδηγό εγκατάστασης του e-Class</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-7\" />
    <link href=\"./install.css\" rel=\"stylesheet\" type=\"text/css\" />

      
  </head>
  <body>
	
	
	<div class=\"outer\">
     <form action=".$PHP_SELF."?alreadyVisited=1 method=\"post\">
	 <input type=\"hidden\" name=\"welcomeScreen\" value=\"welcomeScreen\">
    <div class=\"welcomeImg\"></div>
   
   Καλωσορίσατε στον οδηγό εγκατάστασης του e-Class. Ο οδηγός αυτός :
    <ul id=\"installBullet\">
    	<li>Θα σας βοηθήσει να όρίσετε τις ρυθμίσεις για τη βάση δεδομένων</li>
    	<li>Θα σας βοηθήσει να όρίσετε τις ρυθμίσεις της πλατφόρμας</li>
    	<li>Θα δημιουργήσει το αρχείο config.php</li>
    </ul>
 
  <input type=\"submit\" name=\"install1\" value=\"Επόμενο >\">
 </div>
  </form>
  
  </body>
</html>";

	echo $tool_content;
}


// useful functions

/**
 * check extention and  write  if exist  in a  <LI></LI>
 *
 * @params string	$extentionName 		name  of  php extention to be checked
 * @params boolean	$echoWhenOk			true => show ok when  extention exist
 * @author Christophe Gesche
 * @desc check extention and  write  if exist  in a  <LI></LI>
 *
 */
function warnIfExtNotLoaded($extentionName) {

	global $tool_content;
	if (extension_loaded ($extentionName)) {
		$tool_content .= "<li> $extentionName - <b>ok!</b> </li> ";
	} else {
		$tool_content .= "
                <li>$extentionName
                <font color=\"#FF0000\"> - <b>Δεν είναι εγκατεστημένο!</b></font>
                (Διαβάστε περισσότερα
                <a href=\"http://www.php.net/$extentionName\" target=_blank>εδώ)</a>
                </li>";
	}
}


// -----------------------------------------------------------------------------------
// checking the mysql version
// note version_compare() is used for checking the php version but works for mysql too
// ------------------------------------------------------------------------------------

function mysql_version() {

	$ver = mysql_get_server_info();
	if (version_compare("4.1", $ver) <= 0)
	return true;
	else
	return false;
}

/**
 * return a string without logic
 *
 * @author Christophe Gesche <gesche@ipm.ucl.ac.be>
 * @param  integer	$nbcar 			default 5   	define here  length of password
 * @param  boolean	$lettresseules	default false	fix  if pass can content digit
 * @return string password
 * @desc return a string to be use as password
 */

function generePass($nbcar=5,$lettresseules = false) {
	$chaine = "abBDEFcdefghijkmnPQRSTUVWXYpqrst23456789"; //possible characters
	if ($lettresseules)
	$chaine = "abcdefghijklmnopqrstuvwxyzAZERTYUIOPMLKJHGFDSQWXCVBN"; //possible characters
	for($i=0; $i<$nbcar; $i++)
	{
		@$pass .= $chaine[rand()%strlen($chaine)];
	}
	return $pass;
}

?>

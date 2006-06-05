<?php
/*
      +----------------------------------------------------------------------+
      | e-Class version 1.5                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$                   |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
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

header("Content-type: text/html; charset=iso-8859-7");


@include ("../modules/lang/greek/install.inc.php");

if (file_exists("../config/config.php")) {
    echo "
<html>
<head>
    <title>Εγκατάσταση του e-Class</title>
</head>
<body bgcolor='white'>
<center>
<table cellpadding='6' cellspacing='0' border='0' width='650' bgcolor='#E6E6E6'>
<tr bgcolor='navy'><td valign='top'>
<font color='white' face='arial, helvetica'>Εγκατάσταση του e-Class</font>
</td>
</tr>
<tr bgcolor='#E6E6E6'>
        <td>
            Προσοχή !! Το αρχείο <tt>config.php</tt> υπάρχει ήδη στο σύστημά σας!!
            Το πρόγραμμα εγκατάστασης δεν πραγματοποιεί αναβάθμιση.
            Αν θέλετε να ξανατρέξετε την εγκατάσταση της πλατφόρμας,
            παρακαλούμε διαγράψτε το <tt>config.php<tt>.
        </td>
    </tr>
</table>";
exit;
}


##### STEP 0 INITIALISE FORM VARIABLES IF FIRST VISIT ##################
if(!isset($alreadyVisited))
{
    $dbHostForm="localhost";
    $dbUsernameForm="root";
    $dbNameForm="eclass";
    $dbMyAdmin="../admin/mysql/";
    $phpSysInfoURL="../admin/sysinfo/";

    // extract the path to append to the url if it is not installed on the web root directory
    $urlAppendPath = ereg_replace ("/install/index.php", "", $_SERVER['PHP_SELF']);
       $urlForm = "http://".$_SERVER['SERVER_NAME'].$urlAppendPath."/";
    $pathForm=realpath("../")."/";
    $emailForm=$_SERVER['SERVER_ADMIN'];
    $nameForm="Νίκος";
    $surnameForm="Παπαδόπουλος";
    $loginForm="admin";
    $passForm  		= generePass(8);
    $campusForm="GUNet e-Class";
    $helpdeskForm="+30 2xx xxxx xxx";
    $institutionForm="Ακαδημαϊκό Διαδίκτυο GUNet ";
    $institutionUrlForm="http://www.gunet.gr/";

    $languageCourse = "greek";
}

?>
<html>
<head>
    <title>Εγκατάσταση του e-Class</title>
</head>
<body bgcolor="white">
<center>
<form action="<?php echo $PHP_SELF?>?alreadyVisited=1" method="post">
<table cellpadding="6" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
    <tr bgcolor="navy">
        <td valign="top">
            <font color="white" face="arial, helvetica">
                 Εγκατάσταση του e-Class
            </font>
        </td>
    </tr>
    <tr bgcolor="#E6E6E6">
        <td>
        <font face="arial, helvetica">
<?
echo "
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
            <input type=\"hidden\" name=\"ldapserver\" value=\"".@$ldapserver."\">
            <input type=\"hidden\" name=\"dnldapserver\" value=\"".@$dnldapserver."\">
";

switch (PHP_OS)
{
    case "WIN32" :
    case "WINNT" :
        $wizardImage = "windowsWizard.gif";
        break;
    case "Linux" :
        $wizardImage = "linuxWizard.gif";
        break;
/*	case "SunOS" :
        $wizardImage = "sunWizard.gif"; <- can be created sun have a limitative copyright
        break;
    case "Darwin" : // (MacOS)
        $wizardImage = "macWizard.gif";
        break;
    case "AIX":
        $wizardImage = "aixWizard.gif";
        break;
*/
    default :
        $wizardImage = "defaultWizard.gif";
}

echo "<img src=\"$wizardImage\" align=\"right\" hspace=\"10\" vspace=\"10\">";

############### STEP 2 LICENSE  ###################################

if(isset($install2) OR isset($back2))
{
    echo "
                <h2>
                    ".$langStep2." ".$langLicence."
                </h2>
                Tο e-Class είναι ελεύθερη εφαρμογή και διανέμεται σύμφωνα με την άδεια GNU General Public Licence (GPL).
                Παρακαλούμε διαβάστε την άδεια και κάνετε κλίκ στην 'Αποδοχή'.
                <a href=\"../info/license/gpl_print.txt\">(".$langPrintVers.")</a>
                <br>
                <br>
                <br>
                <textarea wrap=\"virtual\" cols=\"65\" rows=\"15\">";
    include ('../info/license/gpl.txt');
    echo "</textarea>
        </td>
    </tr>
    <tr>
        <td>
            <table width=\"100%\">
                <tr>
                    <td>
                    </td>
                    <td align=\"right\">
                    <input type=\"submit\" name=\"back\" value=\"< Πίσω\">
                    <input type=\"submit\" name=\"install3\" value=\"Αποδοχή>\">
                    </td>
                </tr>
            </table>";
}

######## CHMOD PROBLEM #################################################

elseif(isset($install3) OR isset($back3)) {

// The two following CHMOD are necessary, 666 for Windows, 0666 for Linux
@mkdir ("../config", 0777);
@chmod( "../config/config.php", 666 );
@chmod( "../config/config.php", 0666 );
// courses directory
mkdir("../courses", 0777);

###### STEP 3 MYSQL DATABASE SETTINGS ##############################################
    echo "
                <h2>
                    ".$langStep3." ".$langDBSetting."
                </h2>
                    ".$langDBSettingIntro."
                <br>
                <br>
            </td>
        </tr>
        <tr>
            <td>
                <table width=\"100%\">
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langDBHost."
                            </font>
                        </td>
                        <td>
                            <input type=\"text\" size=\"25\" name=\"dbHostForm\" value=\"".$dbHostForm."\">
                        </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langEG." localhost
                            </font>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langDBLogin."
                            </font>
                        </td>
                        <td>
                            <input type=\"text\"  size=\"25\" name=\"dbUsernameForm\" value=\"".$dbUsernameForm."\">
                        </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langEG." root
                            </font>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langDBPassword."
                            </font>
                        </td>
                        <td>
                            <input type=\"text\"  size=\"25\" name=\"dbPassForm\" value=\"$dbPassForm\">
                        </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langEG." ".generePass(8)."
                            </font>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langMainDB."
                            </font>
                        </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                <input type=\"text\"  size=\"25\" name=\"dbNameForm\" value=\"$dbNameForm\">
                            </font>
                            </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
              (αν υπάρχει ήδη κάποια βάση δεδομένων με το όνομα eclass αλλάξτε το)
             </font>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                URL του phpMyAdmin
                            </font>
                        </td>
                        <td>
                            <input type=\"text\" size=\"25\" name=\"dbMyAdmin\" value=\"".$dbMyAdmin."\">
                        </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                Δεν χρειάζεται να το αλλάξετε
                            </font>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                URL του System info
                            </font>
                        </td>
                        <td>
                            <input type=\"text\" size=\"25\" name=\"phpSysInfoURL\" value=\"".$phpSysInfoURL."\">
                        </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                Δεν χρειάζεται να το αλλάξετε
                            </font>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &nbsp;
                        </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langAllFieldsRequired."
                            </font>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type=\"submit\" name=\"back2\" value=\"< Πίσω\">
                        </td>
                        <td>
                            &nbsp;
                        </td>
                        <td align=\"right\">
                            <input type=\"submit\" name=\"install5\" value=\"Επόμενο >\">
                        </td>
                    </tr>
                </table>";
}	 // install3


###### STEP 4 CONFIG SETTINGS ##############################################

elseif(isset($install5) OR isset($back4))
{

    echo "
        <h2>
            ".$langStep4." ".$langCfgSetting."
        </h2>
            Τα παρακάτω θα γραφτούν στο αρχείο <tt>config.php</tt>.
        </td>
        </tr>
        <tr>
            <td>
                <table width=\"100%\">
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langMainLang."
                            </font>
                        </td>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">

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
            echo "<option value=\"$entries\"";
            if ($entries == $languageCourse)
                echo " selected ";
            echo ">$entries</option>";
        }
    }
    closedir($handle);
echo "
                                </select>
                            </font>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                URL του e-Class
                                <font color=\"red\">
                                    *
                                </font color>
                            </font>
                        </td>
                        <td>
                            <input type=\"text\" size=\"40\" name=\"urlForm\" value=\"$urlForm\">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size=\"2\" face=\"arial, helvetica\">
                                ".$langLocalPath."
                                <font color=red>
                                    *
                                </font>
                            </font>
                        </td>
                        <td>
                            <input type=text size=40 name=\"pathForm\" value=\"".realpath($pathForm)."/\">
                        </td>
                    </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langAdminName."
                                </font>
                            </td>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"nameForm\" value=\"$nameForm\">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langAdminSurname."
                                </font>
                            </td>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"surnameForm\" value=\"$surnameForm\">
                            </td>
                        </tr>

                        <tr>
            <td>
            <font size=\"2\" face=\"arial, helvetica\">
               ".$langAdminEmail."
            </font>
            </td>
            <td>
            <input type=text size=40 name=\"emailForm\" value=\"$emailForm\">
            </td>
            </tr>

                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langAdminLogin."
                                </font>
                            </td>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"loginForm\" value=\"$loginForm\">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langAdminPass."
                                </font>
                            </td>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"passForm\" value=\"$passForm\">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langCampusName."
                                </font>
                            </td>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"campusForm\" value=\"$campusForm\">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langHelpDeskPhone."
                                </font>
                            </td>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"helpdeskForm\" value=\"$helpdeskForm\">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langHelpDeskEmail."
                                    <font color=\"red\">
                                        **
                                    </font color>
                                </font>
                            </td>
                            <td>
                                <input type=text size=40 name=\"helpdeskmail\" value=\"$helpdeskmail\">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langInstituteShortName."
                                </font>
                            </td>
                            <td>
                                <input type=text size=40 name=\"institutionForm\" value=\"$institutionForm\">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    ".$langInstituteName."
                                </font>
                            </td>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                 <font size=\"2\" face=\"arial, helvetica\">
                                     LDAP εξυπηρέτης του Ιδρύματος
                                </font>
                            </td>
                            <td>
                                 <input type=\"text\" size=\"40\" name=\"ldapserver\" value=\"$ldapserver\">
                             </td>
                        </tr>
                        <tr>
                            <td>
                                <font size=\"2\" face=\"arial, helvetica\">
                                    Base dn του LDAP εξυπηρέτη
                                </font>
                            </td>
                            <td>
                                <input type=\"text\" size=\"40\" name=\"dnldapserver\" value=\"$dnldapserver\">
                            </td>
                        </tr>

                        <tr><td colspan=\"2\">&nbsp;</td></tr>
                        <tr>
                            <td colspan=\"2\">
                                <font size=\"2\" face=\"arial, helvetica\">
                                    <font color=\"red\">
                                        *
                                    </font>
                                     = υποχρεωτικό
                                </font>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=\"2\">
                                    <font color=\"red\">
                                                                               **
                                                                        </font>
                                <font size=\"2\" face=\"arial, helvetica\">
                                     ".$langWarnHelpDesk."
                                </font>
                            </td>
                        </tr>
                        <tr><td colspan=\"2\">&nbsp;</td></tr>
                        <tr>
                            <td>
                                <input type=\"submit\" name=\"back3\" value=\"< Πίσω \">
                            </td>
                            <td align=\"right\">
                                <input type=\"submit\" name=\"install6\" value='Επόμενο >'>
                            </td>
                        </tr>
                    </table>";

}

###### STEP 5 LAST CHECK BEFORE INSTALL ##############################################

elseif(isset($install6))
{

    $pathForm = str_replace("\\\\", "/", $pathForm);

    @chmod( "../config/config.php", 666 );
    @chmod( "../config/config.php", 0666 );

    echo "
        <h2>
            ".$langStep5." ".$langLastCheck."
        </h2>
        Τα στοιχεία που δηλώσατε είναι τα παρακάτω:<br>
        <font color=\"red\">
            Εκτυπώστε τα αν θέλετε να θυμάστε το συνθηματικό του διαχειριστή και τις άλλες ρυθμίσεις
        </font>
        <font size=\"2\" face=\"arial, helvetica\">

        <blockquote>Γλώσσα : $languageForm<br>
        Όνομα υπολογιστή : $dbHostForm<br>
        Όνομα Χρήστη για τη Βάση Δεδομένων : $dbUsernameForm<br>
        Συνθηματικό για τη Βάση Δεδομένων: $dbPassForm<br>
        Κύρια Βάση Δεδομένων : $dbNameForm<br>
        URL του phpMyAdmin : $dbMyAdmin<br>

        URL του e-Class : $urlForm<br>
        Toπικό path : $pathForm<br>
        Email Διαχειριστή : $emailForm<br>
        Όνομα Διαχειριστή : $nameForm<br>

        Επώνυμο Διαχειριστή : $surnameForm<br>
        <table border=0>
            <tr>
                <td>
                    <font size=\"2\" color=\"red\" face=\"arial, helvetica\">
                    Όνομα Χρήστη του Διαχειριστή : $loginForm<br>
                    Συνθηματικό του Διαχειριστή : $passForm
                    </font>
                </td>
            <tr>
        </table>
        Όνομα Πανεπιστημιακού Ιδρύματος : $campusForm<br>

        Τηλέφωνο Helpdesk : $helpdeskForm<br>
        E-mail Helpdesk : $helpdeskmail<br>
        Σύντομο όνομα του Ιδρύματος : $institutionForm<br>
        URL του Ιδρύματος : $institutionUrlForm<br>
         LDAP εξυπηρέτης του Ιδρύματος : $ldapserver<br>
        Base dn του LDAP Εξυπηρέτη : $dnldapserver <br>
        </blockquote>
        <table width=100%>
            <tr>
                <td>
                    <input type=\"submit\" name=\"back4\" value=\"< Πίσω\">
                </td>
                <td align=\"right\">
                    <input type=\"submit\" name=\"install7\" value=\"Eγκατάσταση του e-Class >\">
                </td>
            </tr>
        </table>";
}
###### STEP 6 INSTALLATION SUCCESSFULL !##############################################

elseif(isset($install7))
{

############# DB CREATE + STRUCTURE + CONTENT #############################

    $db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
    if (mysql_errno()>0) // problem with server
    {
        $no = mysql_errno();     $msg = mysql_error();
        echo "<HR>[".$no."] - ".$msg."<HR>
        Η Mysql  δεν λειτουργεί ή το όνομα χρήστη/συνθηματικό δεν είναι σωστό.<br>
        Παρακαλούμε ελέγξετε τα στοιχεία σας. <br>
        Όνομα Υπολογιστή : ".$dbHostForm."<br>
        Όνομα Χρήστη : ".$dbUsernameForm."<br>
        Συνθηματικό  : ".$dbPassForm."<br>
        και επιστρέψτε στο βήμα 2
        ";
        exit();
    }
    $mysqlMainDb = $dbNameForm;
    mysql_query("DROP DATABASE IF EXISTS ".$mysqlMainDb);
    if (mysql_version()) mysql_query("SET NAMES greek");
    if (mysql_version())
        $cdb=mysql_query("CREATE DATABASE $mysqlMainDb CHARACTER SET greek");
    else
        $cdb=mysql_query("CREATE DATABASE $mysqlMainDb");
    mysql_select_db ($mysqlMainDb);


    mysql_query("DROP TABLE IF EXISTS admin");
    mysql_query("DROP TABLE IF EXISTS annonces");
    mysql_query("DROP TABLE IF EXISTS cours");
    mysql_query("DROP TABLE IF EXISTS cours_faculte");
    mysql_query("DROP TABLE IF EXISTS cours_user");
    mysql_query("DROP TABLE IF EXISTS faculte");
    mysql_query("DROP TABLE IF EXISTS user");
    mysql_query("DROP TABLE IF EXISTS loginout");
    mysql_query("DROP TABLE IF EXISTS institution");
    mysql_query("DROP TABLE IF EXISTS prof_request");
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

    mysql_query("INSERT INTO `user` (`prenom`, `nom`, `username`, `password`, `email`, `statut`)
    VALUES ('$nameForm', '$surnameForm', '$loginForm','$passForm','$emailForm','1')");
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
       id int(11) DEFAULT '0' NOT NULL auto_increment,
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


 } else {
     mysql_query("CREATE TABLE annonces (
      id mediumint(11) NOT NULL auto_increment,
      contenu text,
      temps date default NULL,
      code_cours varchar(20) default NULL,
      ordre mediumint(11) NOT NULL,
      PRIMARY KEY  (id))
      TYPE=MyISAM");


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


    mysql_query("INSERT INTO `user` (`prenom`, `nom`, `username`, `password`, `email`, `statut`)
    VALUES ('$nameForm', '$surnameForm', '$loginForm','$passForm','$emailForm','1')");
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

 }

    ########################## WRITE config.php ##################################

    $fd=@fopen("../config/config.php", "w");
    if (!$fd) {
        echo "		<h2>
                    ".$langStep6." ".$langCfgSetting."
                </h2>
                <br>
                <b>Παρουσιάστηκε σφάλμα!</b>
                <br><br>
        Δεν είναι δυνατή η δημιουργία του αρχείου config.php.<br><br>
        Παρακαλούμε ελέγξτε τα δικαιώματα πρόσβασης στους υποκαταλόγους του eclass
        και δοκιμάστε ξανά την εγκατάσταση.\n";
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
$emailhelpdesk="'.$helpdeskmail.'";
$Institution="'.$institutionForm.'";
$InstitutionUrl="'.$institutionUrlForm.'";
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

?>';

######### DEALING WITH FILES #########################################

    fwrite($fd, $stringConfig);

############ PROTECTING FILES AGAINST WEB WRITING ###################

    echo "
                <h2>
                    ".$langStep6." ".$langCfgSetting."
                </h2>
                <br>
                <br>
                Η εγκατάσταση ολοκληρώθηκε με επιτυχία!
                Κάντε κλίκ παρακάτω για να μπείτε στο e-class.
                <br>
                <br><b>
                Συμβουλή: Για να προστατέψετε το e-class, αλλάξτε τα δικαιώματα πρόσβασης των αρχείων
                <tt>/config/config.php</tt> και <tt>/install/index.php</tt> και
                επιτρέψτε μόνο ανάγνωση (CHMOD 444).</b>
                <br>
                <br>
                <br>
                <br>
                <br>
    </form>
    <form action=\"../\">
    <p align=\"right\"><input type=\"submit\" value=\"Είσοδος στο e-Class\"></p>";

    }       // τέλος ελεγχου για δικαιώματα
}	// STEP 6 of 6 END



###### STEP 1 REQUIREMENTS ##############################################
else
{
    echo "
<h2>
    ".$langStep1." ".$langRequirements."
</h2>
Το πρόγραμμα εγκατάστασης της πλατφόρμας θα κάνει όλες τις απαιτούμενες ρυθμίσεις στο σύστημα,
    στην κύρια βάση δεδομένων και θα δημιουργήσει το αρχείο <tt>config.php</tt>
            ";
    echo "
    <p></p>
    <b>Έλεγχος προαπαιτούμενων προγραμμάτων για τη λειτουργία του e-Class :</b>
    <ul>
    <li>
        Webserver (<font size=\"-1\"><em>βρέθηκε <b>".$_SERVER['SERVER_SOFTWARE']."</b></em></font>) <br>
        με υποστήριξη PHP (<font size=\"-1\"><em>βρέθηκε <b>PHP ".phpversion()."</b></em></font>).<br>
    ";
    if (empty($SERVER_SOFTWARE)) {
        echo "
        <li><strong>Προσοχή!</strong> Φαίνεται πως η επιλογή register_globals
        στο αρχείο php.ini δεν είναι ενεργοποιημένη. Χωρίς αυτήν το
        e-class δεν μπορεί να λειτουργήσει. Παρακαλούμε διορθώστε το
        αρχείο php.ini ώστε να περιέχει τη γραμμή:<br>
        <pre>register_globals = On</pre><br>
        Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε
        τις οδηγίες εγκατάστασης στο αρχείο
        <a href='../../INSTALL.txt'>INSTALL.txt</a>.</li>";
    }
    if (!ini_get('short_open_tag')) {
        echo "
        <li><strong>Προσοχή!</strong> Φαίνεται πως η επιλογή short_open_tag
        στο αρχείο php.ini δεν είναι ενεργοποιημένη. Χωρίς αυτήν το
        e-class δεν μπορεί να λειτουργήσει. Παρακαλούμε διορθώστε το
        αρχείο php.ini ώστε να περιέχει τη γραμμή:<br>
        <pre>short_open_tag = On</pre><br>
        Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε
        τις οδηγίες εγκατάστασης στο αρχείο
        <a href='../../INSTALL.txt'>INSTALL.txt</a>.</li>";
    }
    echo "</ul>";
    echo "<ul>Απαιτούμενα PHP modules";
    warnIfExtNotLoaded("standard");
    warnIfExtNotLoaded("session");
    warnIfExtNotLoaded("mysql");
    warnIfExtNotLoaded("zlib");
    warnIfExtNotLoaded("pcre");
    echo "</ul>";
    echo "<UL>Προαιρετικά PHP modules";
    warnIfExtNotLoaded("ldap");
    warnIfExtNotLoaded("gd");
    warnIfExtNotLoaded("mbstring");
    echo "</ul>";
    echo "
    <br>
    <b>Άλλες απαιτήσεις συστήματος:</b>
    <ul>
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
    <br>
Το e-Class θα εγκαταστήσει το δικό του διαχειριστικό εργαλείο μέσω web των βάσεων δεδομένων MySQL (<a
href=\"http://www.phpmyadmin.net\" target=_blank>phpMyAdmin</a>) αλλά
μπορείτε να χρησιμοποιήσετε και το δικό σας.
<br>
<br>
Πριν προχωρήσετε στην εγκατάσταση τυπώστε και διαβάστε προσεκτικά τις
<a href=\"../INSTALL.txt\" target=_blank>Οδηγίες Εγκατάστασης</a>.
<br>
<br>
Επίσης, γενικές οδηγίες για την πλατφόρμα μπορείτε να διαβάσετε <a href=\"../README.txt\" target=_blank>εδώ</a>.
<br>
<br>
<p align=\"right\">
<input type=\"submit\" name=\"install2\" value=\"Επόμενο >\">";

}

?>
        </td>
    </tr>
</table>
</form>
</center>
</body>
</html>
<?

// useful functions


/**
 * check extention and  write  if exist  in a  <LI></LI>
 *
 * @params string	$extentionName 		name  of  php extention to be checked
 * @params boolean	$echoWhenOk			true => show ok when  extention exist
 * @author Christophe Geschι
 * @desc check extention and  write  if exist  in a  <LI></LI>
 *
 */

function warnIfExtNotLoaded($extentionName) {

        if (extension_loaded ($extentionName)) {
                echo "<li> $extentionName - <b>ok!</b> </li> ";
        } else {
                echo "
                <li>$extentionName
                <font color=\"#FF0000\"> - <b>!!λείπει!!</b></font>
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

<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
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
	auth_process.php
	@last update: 31-05-2006 by Stratos Karatzidis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
        @Description: Platform Authentication Methods and their settings

 	This script tries to get the values of an authentication method, establish 
 	a connectiond and with a test account successfully connect to the server.
 	Possible scenarios:
 	- The settings of the method are fine and the mechanism authenticates the 
 	test account
 	- The settings of the method are fine, but the method does not work 
 	with the test account
 	- The settings are wrong.
 	
 	The admin can: - choose a method and define its settings


==============================================================================
*/

// LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
$langFiles = array('admin','about');
include '../../include/baseTheme.php';
include_once '../auth/auth.inc.php';
@include "check_admin.inc";			// check if user is administrator
$nameTools = "Πιστοποίηση Χρηστών";

$tool_content = "";			// Initialise $tool_content

// get the values
$auth = isset($_POST['auth'])?$_POST['auth']:'';
$auth_submit = isset($_POST['auth_submit'])?$_POST['auth_submit']:'';

if((!empty($auth_submit)) && ($auth_submit==1))
{
	$submit = isset($_POST['submit'])?$_POST['submit']:'';

  if((array_key_exists('submit', $_POST)) && (!empty($submit))) // if form is submitted
	{
		$test_username = isset($_POST['test_username'])?$_POST['test_username']:'';
		$test_password = isset($_POST['test_password'])?$_POST['test_password']:'';
		$tool_content .= "<br />Γίνεται δοκιμή του τρόπου πιστοποίησης...";
		if((!empty($test_username)) && (!empty($test_password)))
		{
	    $is_valid = auth_user_login($auth,$test_username,$test_password);
	    if($is_valid)
	    {
				$auth_allow = 1;	
				$tool_content .= "<span style=\"color:green;font-weight:bold;\">ΕΠΙΤΥΧΗΣ ΣΥΝΔΕΣΗ</span><br /><br />";
	    }
	    else
	    {
				$tool_content .= "<span style=\"color:red;font-weight:bold;\">Η ΣΥΝΔΕΣΗ ΔΕΝ ΔΟΥΛΕΥΕΙ</span><br /><br />";
				$auth_allow = 0;
	    }	
	}
	else
	{
	    $tool_content .= "<br />You did not provide a valid pair of username/password<br />";
	    $auth_allow = 0;
	}
	
	// store the values - do the updates
	if((!empty($auth_allow))&&($auth_allow==1))
	{
	    $currentauth = get_auth_id();
	    $qry = "UPDATE auth set auth_default=0";		// set inactive the previous auth method
	    $sql = mysql_query($qry,$db);
	    if($sql)
	    {
	    	switch($auth)
				{
					case '1':	$auth_default = 1;
						$auth_settings = "";
						$auth_instructions = "";
						break;
					case '2':	$pop3host = isset($_POST['pop3host'])?$_POST['pop3host']:'';
						$auth_default = 2;
						$auth_settings = "pop3host=".$pop3host;
						$auth_instructions = isset($_POST['pop3instructions'])?$_POST['pop3instructions']:'';
						break;
				  case '3':	$imaphost = isset($_POST['imaphost'])?$_POST['imaphost']:'';
						$auth_default = 3;
						$auth_settings = "imaphost=".$imaphost;
						$auth_instructions = isset($_POST['imapinstructions'])?$_POST['imapinstructions']:'';
						break;
				  case '4':	$ldaphost = isset($_POST['ldaphost'])?$_POST['ldaphost']:'';
						$ldapbase_dn = isset($_POST['ldapbase_dn'])?$_POST['ldapbase_dn']:'';
						$ldapbind_user = isset($_POST['ldapbind_user'])?$_POST['ldapbind_user']:'';
						$ldapbind_pw = isset($_POST['ldapbind_pw'])?$_POST['ldapbind_pw']:'';
						$auth_default = 4;
						$auth_settings = "ldaphost=".$ldaphost."|ldapbind_dn=".$ldapbind_dn."|ldapbind_user=".$ldapbind_user."|ldapbind_pw=".$ldapbind_pw;
						$auth_instructions = isset($_POST['ldapinstructions'])?$_POST['ldapinstructions']:'';
						break;
				  case '5':	$dbhost = isset($_POST['dbhost'])?$_POST['dbhost']:'';
						$dbtype = isset($_POST['dbtype'])?$_POST['dbtype']:'';
						$dbname = isset($_POST['dbname'])?$_POST['dbname']:'';
						$dbuser = isset($_POST['dbuser'])?$_POST['dbuser']:'';
						$dbpass = isset($_POST['dbpass'])?$_POST['dbpass']:'';
						$dbtable = isset($_POST['dbtable'])?$_POST['dbtable']:'';
						$dbfielduser = isset($_POST['dbfielduser'])?$_POST['dbfielduser']:'';
						$dbfieldpass = isset($_POST['dbfieldpass'])?$_POST['dbfieldpass']:'';
						$auth_default = 5;
						$auth_settings = "dbhost=".$dbhost."|dbname=".$dbname."|dbuser=".$dbuser."|dbpass=".$dbpass."|dbtable=".$dbtable."|dbfielduser=".$dbfielduser."|dbfieldpass=".$dbfieldpass;
						$auth_instructions = isset($_POST['dbinstructions'])?$_POST['dbinstructions']:'';;
						break;
						default:
						break;
				}
				 
				$qry = "UPDATE auth SET auth_settings='".$auth_settings."',auth_instructions='".$auth_instructions."',auth_default=1 WHERE auth_id=".$auth;
				$sql2 = mysql_query($qry,$db);		// do the update as the default method
				if(($sql2) && (mysql_affected_rows($db)==1))
				{
					$tool_content .= "<br />O τρόπος πιστοποίησης που επιλέξατε έχει οριστεί ως ο προκαθορισμένος της πλατφόρμας<br />";
				}
				else	// rollback the previous operation
				{
				    $tool_content .= "ΣΦΑΛΜΑ. Ο τρόπος πιστοποίησης δεν μπορεί να οριστεί ως προκαθορισμένος<br />";
				    $qry = "UPDATE auth set auth_default=1 WHERE auth_id=".$currentauth;
				    $sql3 = db_query($qry);
				}
	    }
	    else
	    {
				$tool_content .= "ΣΦΑΛΜΑ. Ο τρόπος πιστοποίησης δεν μπορεί να οριστεί ως προκαθορισμένος<br />";
	    }
		}
		
	}

}
else
{
	// display the form
	if(!empty($auth))
	{
		$auth_data = get_auth_settings($auth);
	}
	
	$tool_content .= "<table width=\"99%\">
	<tr><td>";
	$tool_content .= "<form name=\"authmenu\" method=\"post\" action=\"auth_process.php\">
	<input type=\"hidden\" name=\"auth_submit\" value=\"1\" />
	<input type=\"hidden\" name=\"auth\" value=\"".$auth."\" />
	AUTH METHOD:<br /><br />";
	switch($auth)
	{
		case 1: $tool_content .= "<br />ECLASS<br />";
			include_once '../auth/methods/eclassform.php';
			break;
		case 2: $tool_content .= "<br />POP3<br />";
			include_once '../auth/methods/pop3form.php';
			break;
		case 3: $tool_content .= "<br />IMAP<br />";
			include_once '../auth/methods/imapform.php';
			break;			
		case 4: $tool_content .= "<br />LDAP<br />";
			include_once '../auth/methods/ldapform.php';
			break;
		case 5: $tool_content .= "<br />EXTERNAL DATABASE<br />";
			include_once '../auth/methods/db/dbform.php';
			break;
		default:
			break;		
	}	
	
	$tool_content .= "<br />
	Username:<input type=\"text\" name=\"test_username\" value=\"".$test_username."\"><br />
	Password:<input type=\"password\" name=\"test_password\" value=\"".$test_password."\"><br />
	<input type=\"submit\" name=\"submit\" value=\"ΕΝΗΜΕΡΩΣΗ\"><br />";
	
	$tool_content .= "</form>";
	$tool_content .="<br /></td></tr></table>";
}

draw($tool_content,3);

?>
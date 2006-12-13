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
	lostpass.php
* @version $Id$
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
  @Description: Send a user's password via e-mail

 	
==============================================================================
*/

//LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
$langFiles = array('lostpass');
include '../../include/baseTheme.php';
include 'auth.inc.php';
include('../../include/sendMail.inc.php');

// Initialise $tool_content
$tool_content = ""; 


$nameTools = $lang_remind_pass;

function valid_email($e) {
	$elements = explode('@', $e);
	if (sizeof($elements) != 2) {
		return FALSE;
	}
	return TRUE;
}

function check_password_editable($password)
{
	$authmethods = array("pop3","imap","ldap","db");
	if(in_array($password,$authmethods))
	{
		return 0; // it is not editable, because it belongs in external auth method
	}
	else
	{
		return 1; // is editable
	}
}

if (!isset($femail)) {
	/***** Email address entry form *****/

$tool_content .= "<p>$lang_pass_intro</p>";

$tool_content .= "<form method=\"post\" action=\"".$REQUEST_URI."\">
	<table>
	<thead>
	<tr>
	<th>
	$lang_email: 
	</th>
	<td>
	<input type=\"text\" name=\"femail\" size=\"40\">
	</td>
	</thead>
	</table>
	<br/>
	<input type=\"submit\" name=\"doit\" value=\"".$lang_pass_submit."\">
	</form>";

} 
else 
{
	if (!valid_email($femail)) 
	{
		$tool_content .=  $lang_pass_invalid_mail1
		."<code> ".htmlspecialchars($femail)." </code>"
		.$lang_pass_invalid_mail2
		." <a href='mailto: $emailhelpdesk'>".$emailhelpdesk."</a>, "
		.$lang_pass_invalid_mail3;

		$tool_content .= "<form method=\"post\" action=\"".$REQUEST_URI."\">
		<input type=\"text\" name=\"femail\" size=\"50\"><br>
		<input type=\"submit\" name=\"doit\" value=\"".$lang_pass_submit."\">
		</form>";

	} 
	else 
	{
		/***** If valid e-mail address was entered, find user and send email *****/
		$res = mysql_query("SELECT nom, prenom, username, password, statut, inst_id FROM user
			WHERE email = '" . mysql_escape_string($femail) . "'");
		if (mysql_num_rows($res) > 0) 
		{
			$text = $lang_pass_email_intro. $emailhelpdesk;
			
			
			
			if (mysql_num_rows($res) == 1) 
			{
				$text .= "\n$lang_pass_email_account\n";
			} 
			else 
			{
				$text .= "\n$lang_pass_email_many_accounts\n";
			}
			while ($s = mysql_fetch_array($res, MYSQL_ASSOC)) 
			{
				$is_editable = check_password_editable($s['password']);
				if(!empty($is_editable))
				{
					// decrypt now, the password from db
					$crypt = new Encryption;
					$key = $encryptkey;
					$password_decrypted = $crypt->decrypt($key, $s['password']);
					
					$text .= "
					$lang_pass_email_name " . htmlspecialchars($s['prenom']." ".$s['nom']) . "
					$lang_pass_email_status " . (($s['statut'] == 1)? "$lang_prof": (
						($s['statut'] == 5)? "$lang_student": "$lang_other")) . "
						$lang_pass_email_username " . htmlspecialchars($s['username']);
						//($s['inst_id']? $lang_pass_email_ldap:$lang_pass_email_password . htmlspecialchars($password_decrypted) . "\n");
						$text .= $lang_pass_email_password . htmlspecialchars($password_decrypted) . "\n";
				}
				else
				{
					switch($s['password'])
					{
						case 'pop3':$auth=2;break; case 'imap':$auth=3;break; case 'ldap':$auth=4;break; case 'db':$auth=5;break; default:$auth=1;break;
					}
					$text .= "Το συνθηματικό αυτού του λογαριασμού δεν μπορεί να αλλαχθεί,
					διότι ανήκει σε εξωτερική μέθοδο πιστοποίησης\n
					Παρακαλούμε, επικοινωνήστε με το διαχειριστή για την ".get_auth_info($auth);
				}
			}
			/***** Account details found, now send e-mail *****/
			$emailheaders = "From: $siteName <$emailAdministrator>\n".
					"MIME-Version: 1.0\n".
					"Content-Type: text/plain; charset=$charset\n".
					"Content-Transfer-Encoding: 8bit";
			$emailsubject = "Account information";
			if (!send_mail($siteName, $emailAdministrator, '', $femail,
				   $emailsubject, $text, $charset)) 
			{
				$tool_content .= $lang_pass_email_error1
					."<code> ".$femail." </code>"
					.$lang_pass_email_error2
					."<a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>.";
			} 
			else 
			{
				$tool_content .= $lang_pass_email_ok
				."<code> ".$femail." </code>.";
			}
			
			
			
			
		} 
		else 
		{
			$tool_content .= $lang_pass_not_found1
			."<code> ".$femail." </code>."
			.$lang_pass_not_found2
			."<a href='mailto: $emailhelpdesk'>$emailhelpdesk</a>"
			.$lang_pass_not_found3;
		}
	}
}

$tool_content .= "<br />";

draw($tool_content,0);

?>

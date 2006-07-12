<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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
	@last update: 12-07-2006 by Karatzidis Stratos
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

if (!isset($femail)) {
	/***** Email address entry form *****/

$tool_content .= $lang_pass_intro;

$tool_content .= "<form method=\"post\" action=\"".$REQUEST_URI."\">
	<em style=\"padding-left:5px; font-size:10pt;\">".$lang_email.": </em>
	<input type=\"text\" name=\"femail\" size=\"40\"><br><br>
	<input type=\"submit\" name=\"doit\" value=\"".$lang_pass_submit."\">
	</form>";

} 
else 
{
	if (!valid_email($femail)) 
	{
		$tool_content .=  $lang_pass_invalid_mail1
		."<code> ".$femail." </code>"
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
				$text .= "
					$lang_pass_email_name " . htmlspecialchars($s['prenom']." ".$s['nom']) . "
					$lang_pass_email_status " . (($s['statut'] == 1)? "$lang_prof": (
						($s['statut'] == 5)? "$lang_student": "$lang_other")) . "
						$lang_pass_email_username " . htmlspecialchars($s['username']) .
						($s['inst_id']? " $lang_pass_email_ldap": "
						$lang_pass_email_password " . htmlspecialchars($s['password']) . "\n");
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
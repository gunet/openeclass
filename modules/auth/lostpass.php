<?php
/**===========================================================================
*              GUnet eClass 2.0
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
 * Password reset component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component resets the user's password after verifying 
 * his/hers  information through a challenge/response system.
 *
 */

// Initialise $tool_content
$tool_content = "";
include '../../include/baseTheme.php';
include 'auth.inc.php';
include('../../include/sendMail.inc.php');
$nameTools = $lang_remind_pass;

function check_password_editable($password)
{
	$authmethods = array("pop3","imap","ldap","db");
	if(in_array($password,$authmethods))
	{
		return false; // it is not editable, because it belongs in external auth method
	}
	else
	{
		return true; // is editable
	}
}

function createPassword ($length = 8) {

	// initialise password var
	$password = "";
	// define possible characters
	$possible = "abcdefghjklmnopqrstvwxyz0123456789";

	$i = 0;
	// add random characters to $password until $length is reached
	while ($i < $length) {

	// pick a random character from the $possible pool
	$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

	// do not allow dublicate characters in the password
		if (!strstr($password, $char)) {
			$password .= $char;
			$i++;
		}
	}
	return $password;
}

//TODO place some sort of clear-out function to delete reset-pass entries older than 2 hours (link validity window)
if (isset($_REQUEST['do']) && $_REQUEST['do'] == "go") {
	$userUID = (int)$_REQUEST['u'];
	$hash = $_REQUEST['h'];

	$res = db_query("SELECT `user_id`, `hash`, `password`, `datetime` FROM passwd_reset
							WHERE `user_id` = '" . mysql_escape_string($userUID) . "'
							AND `hash` = '" . mysql_escape_string($hash) . "'
							AND TIMESTAMPDIFF(MINUTE, `datetime`,NOW()) < 60
							", $mysqlMainDb);

	if (mysql_num_rows($res) == 1) {
		$myrow = mysql_fetch_array($res);
		//copy pass hash (md5) from reset_pass to user table
		$sql = "UPDATE `user` SET `password` = '".$myrow['hash']."' WHERE `user_id` = ".$myrow['user_id']."";
		
		if(db_query($sql, $mysqlMainDb)) {

			//send email to the user of his new pass (not hashed)
			$res = db_query("SELECT `email` FROM user WHERE `user_id` = ".$myrow['user_id']."", $mysqlMainDb);
			$myrow2 = mysql_fetch_array($res);

			$text = "$langPassEmail1 <em>$myrow[password]</em><br>$langPassEmail2";

			$tool_content .= "
				<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
						<p>$langAccountResetSuccess1</p>
						<p>$text</p>
    					<p><a href=\"../../index.php\">$langHome</a></p>
					</td>
					</tr>
				</tbody>
			</table>";

			$sql = "DELETE FROM `passwd_reset` WHERE `user_id` = ".$myrow['user_id']."";
			db_query($sql, $mysqlMainDb);
		}
		//advice him to change his pass once logged in
	} else {
$tool_content = "<table width=\"99%\">
                   <tbody>
                   <tr>
                   <td class=\"caution\">
                   <p><strong>$langAccountResetInvalidLink</strong></p>
                   <p><a href=\"../../index.php\">$langHome</a></p>
                      </td>
                     </tr>
                     </tbody>
                </table>";
	}
} elseif ((!isset($email) || !email_seems_valid($email)
     || !isset($userName) || empty($userName)) && !isset($_REQUEST['do'])) {

		$lang_pass_invalid_mail= "$lang_pass_invalid_mail1 $lang_pass_invalid_mail2 $lang_pass_invalid_mail3";

	/***** Email address entry form *****/
        if (isset($email) and !email_seems_valid($email)) {
                $tool_content .= '<table width="99%"><tbody><tr><td class="caution">' .
                                '<p><strong>' . $lang_pass_invalid_mail . '<br />&nbsp;<br />' .
                                '&nbsp;<br />&nbsp;<br /></strong></p>' .
				'</td></tr></tbody></table>';
        }

	$tool_content .= $lang_pass_intro;

	$tool_content .= "<form method=\"post\" action=\"".$REQUEST_URI."\">
		<table>
		<thead>
		<tr><th>$lang_username: </th>
		<td>
		<input type=\"text\" name=\"userName\" size=\"40\" />
		</td>
		<tr>
		<th>$lang_email: </th>
		<td>
		<input type=\"text\" name=\"email\" size=\"40\" />
		</td>
		</thead>
		</table>
		<br/>
		<input type=\"submit\" name=\"doit\" value=\"".$lang_pass_submit."\" />
	</form>";

} elseif (!isset($_REQUEST['do'])) {
	/***** If valid e-mail address was entered, find user and send email *****/
	$res = db_query("SELECT user_id, nom, prenom, username, password, statut FROM user
				WHERE email = '" . mysql_escape_string($email) . "'
				AND username = '" . mysql_escape_string($userName) . "'", $mysqlMainDb);

	if (mysql_num_rows($res) == 1) {
		$text = $langPassResetIntro. $emailhelpdesk;
		$text .= "$langHowToResetTitle";

		while ($s = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$is_editable = check_password_editable($s['password']);
			if($is_editable) {

				//insert an md5 key to the db
				$new_pass = createPassword();
				//TODO: add a query to check if the newly generated password already exists in the
				//reset-pass table. If yes, attempt to generate another one.
				$sql = "INSERT INTO `passwd_reset` (`user_id`, `hash`, `password`, `datetime`) VALUES ('".$s['user_id']."',  '".md5($new_pass)."', '$new_pass', NOW())";
				db_query($sql, $mysqlMainDb);
				//prepare instruction for password reset
				$text .= $langPassResetGoHere;
				$text .= $urlServer . "modules/auth/lostpass.php?do=go&u=".$s['user_id']."&h=" .md5($new_pass);

			} else { //other type of auth...
				switch($s['password'])  {
					case 'pop3':{
						$auth=2;
						break;
					}
					case 'imap':{
						$auth=3;
						break;
					}
					case 'ldap':{
						$auth=4;
						break;
					}
					case 'db':{
						$auth=5;
						break;
					}
					default:{
						$auth=1;
						break;
					}
				}

				$tool_content = "
				<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
						<p><strong>$langPassCannotChange1</strong></p>
						<p>$langPassCannotChange2 <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a> $langPassCannotChange3</p>
						<p><a href=\"../../index.php\">$langHome</a></p>
					</td>
					</tr>
				</tbody>
			</table>";
		}
	}
	/***** Account details found, now send e-mail *****/
	$emailheaders = "From: $siteName <$emailAdministrator>\n".
        	"MIME-Version: 1.0\n".
        	"Content-Type: text/plain; charset=$charset\n".
        	"Content-Transfer-Encoding: 8bit";
	$emailsubject = "eClass account information";
	if (!send_mail($siteName, $emailAdministrator, '', $email, $emailsubject, $text, $charset)) {
		$tool_content = "
			<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"caution\">
					<p><strong>$langAccountEmailError1</strong></p>
					<p>$langAccountEmailError2 $email.</p>
					<p>$langAccountEmailError3 <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>.</p>
					<p><a href=\"../../index.php\">$langHome</a></p>
				</td>
				</tr>
			</tbody>
		</table>";

	} elseif (!isset($auth)) {
            $tool_content .= "<table width=\"99%\">
                   <tbody><tr><td class=\"success\">
                       $lang_pass_email_ok <strong>$email</strong><br/><br/>
                        <a href=\"../../index.php\">$langHome</a>
                        </td></tr></tbody></table><br/>";
                }

       } else {
                $tool_content .= "<table width=\"99%\">
                <tbody>
                  <tr>
                  <td class=\"caution\">
                  <p><strong>$langAccountNotFound1 ($email)</strong></p>
                  <p>$langAccountNotFound2 <a href='mailto: $emailhelpdesk'>$emailhelpdesk</a>, $langAccountNotFound3</p>
                  <p><a href=\"../../index.php\">$langHome</a></p>
                   </td>
                  </tr>
                 </tbody>
                </table>";
        }
} else {
               $tool_content = "<table width=\"99%\">
                   <tbody>
		               <tr>
                   <td class=\"caution\">
                   <p><strong>$langAccountEmailError1</strong></p>
                   <p>$langAccountEmailError2 $email.</p>
                   <p>$langAccountEmailError3 <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>.</p>
                   <p><a href=\"../../index.php\">$langHome</a></p>
                      </td>
                     </tr>
                        </tbody>
                </table>";
}
draw($tool_content,0);
?>

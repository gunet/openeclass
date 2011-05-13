<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
require_once 'auth.inc.php';
$nameTools = $langReqRegProf;
$navigation[] = array("url"=>"registration.php", "name"=> $langNewUser);

// Initialise $tool_content
$tool_content = "";

// security check
if (isset($_POST['localize'])) {
	$language = preg_replace('/[^a-z]/', '', $_POST['localize']);
}

$auth = get_auth_id();

// display form
if (!isset($_POST['submit'])) {

@$tool_content .= "
<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">

 <fieldset>
  <legend>$langUserData</legend>
  <table class='tbl' width='100%'> 
  <tr>
    <th>$langName:</th>
    <td><input size='35' type='text' name='prenom_form' value='$prenom_form'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
   <th>$langSurname:</th>
   <td><input size='35' type='text' name='nom_form' value='$nom_form'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th>$langPhone:</th>
    <td><input size='35' type='text' name='userphone' value='$userphone'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th>$langUsername:</th>
    <td><input size='35' type='text' name='uname' value='$uname'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th>$langEmail:</th>
    <td><input size='35' type='text' name='email_form' value='$email_form'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th>$langComments:</th>
    <td><textarea name='usercomment' COLS='32' ROWS='4' WRAP='SOFT'>$usercomment</textarea>&nbsp;&nbsp;<small>(*) $profreason</small></td>
  </tr>
  <tr>
    <th>$langFaculty:</th>
    <td><select name='department'>";
        $deps=mysql_query("SELECT id, name FROM faculte order by id");
        while ($dep = mysql_fetch_array($deps))
        {
        	$tool_content .= "<option value='$dep[id]'>$dep[name]</option>\n";
        }
        $tool_content .= "</select>
    </td>
  </tr>
<tr>
      <th>$langLanguage:</th>
      <td>";
	$tool_content .= lang_select_options('proflang');
	$tool_content .= "</td>
    </tr>
  <tr>
    <th>&nbsp;</th>
    <td class='right'>
      <input type='submit' name='submit' value='$langSubmitNew' />
      <input type='hidden' name='auth' value='1' />
    </td>
  </tr>
  </table>
 </fieldset>
</form>
<div class='right smaller'>$langRequiredFields</div>";

} else {

// registration
$registration_errors = array();

    // check if there are empty fields
    if (empty($_POST['nom_form']) or empty($_POST['prenom_form']) or empty($_POST['userphone'])
	 or empty($_POST['usercomment']) or empty($_POST['uname']) or (empty($_POST['email_form']))) {
		$registration_errors[]=$langEmptyFields;
	}

    if (count($registration_errors) == 0) {    // registration is ok
            // ------------------- Update table user_request ------------------------------
            $auth = $_POST['auth'];
            if($auth != 1) {
                    switch($auth) {
                            case '2': $password = "pop3";
                                      break;
                            case '3': $password = "imap";
                                      break;
                            case '4': $password = "ldap";
                                      break;
                            case '5': $password = "db";
                                      break;
                            case '7': $password = "cas";
                                      break;
                            default:  $password = "";
                                      break;
                    }
            }

            db_query('INSERT INTO user_request SET
                                name = ' . autoquote($_POST['prenom_form']). ',
                                surname = ' . autoquote($_POST['nom_form']). ',
                                uname = ' . autoquote($_POST['uname']). ',
                                email = ' . autoquote($_POST['email_form']). ',
                                faculty_id = ' . autoquote($_POST['department']). ',
                                phone = ' . autoquote($_POST['userphone']). ',
                                status = 1,
                                statut = 1,
                                date_open = NOW(),
                                comment = ' . autoquote($_POST['usercomment']). ',
                                lang = ' . autoquote($_POST['proflang']). ",
                                ip_address = inet_aton('$_SERVER[REMOTE_ADDR]')",
                     $mysqlMainDb);

            //----------------------------- Email Message --------------------------
            $MailMessage = $mailbody1 . $mailbody2 . "$_POST[prenom_form] $_POST[nom_form]\n\n" . $mailbody3 .
                    $mailbody4 . $mailbody5 . "$mailbody6\n\n" . "$langFaculty: " .
                    find_faculty_by_id($_POST['department']) . "\n$langComments: $_POST[usercomment]\n" .
                    "$langProfUname: $_POST[uname]\n$langProfEmail: $_POST[email_form]\n" .
                    "$contactphone: $_POST[userphone]\n\n\n$logo\n\n";

            if (!send_mail('', $emailhelpdesk, $gunet, $emailhelpdesk, $mailsubject, $MailMessage, $charset))
            {
                    $tool_content .= "
                            <p class='alert1'>$langMailErrorMessage &nbsp; <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a></p>";
                    draw($tool_content,0);
                    exit();
            }

            //------------------------------------User Message ----------------------------------------
            $tool_content .= "
                    <p class='success'>$langDearProf<br />$success<br />$infoprof<br /></p>
                    <p>&laquo; <a href='$urlServer'>$langBack</a></p>";
    }

	else	{  // errors exist - registration failed
            $tool_content .= "<div class='caution'>";
                foreach ($registration_errors as $error) {
                        $tool_content .= "$error</div>";
                }
	       $tool_content .= "<p><a href='$_SERVER[PHP_SELF]?prenom_form=$_POST[prenom_form]&amp;nom_form=$_POST[nom_form]&amp;userphone=$_POST[userphone]&amp;uname=$_POST[uname]&amp;email_form=$_POST[email_form]&amp;usercomment=$_POST[usercomment]'>$langAgain</a>" .
                "</p>";
	}

} // end of submit

draw($tool_content,0);

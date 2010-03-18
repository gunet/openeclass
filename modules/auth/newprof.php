<?
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
if (!isset($submit)) {

@$tool_content .= "
<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">
<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
<thead>
<tr>
  <td>
  <table width=\"99%\" class='FormData' align='left'>
  <thead>
  <tr>
   <th class='left' width='220'>$langSurname</th>
   <td><input size='35' type='text' name='nom_form' value='$nom_form' class='FormData_InputText'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langName</th>
    <td><input size='35' type='text' name='prenom_form' value='$prenom_form' class='FormData_InputText'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
	<tr>
    <th class='left'>$langPhone</th>
    <td><input size='35' type='text' name='userphone' value='$userphone' class='FormData_InputText'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langUsername</th>
    <td><input size='35' type='text' name='uname' value='$uname' class='FormData_InputText'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langEmail</th>
    <td><input size='35' type='text' name='email_form' value='$email_form' class='FormData_InputText'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langComments</td>
    <td><textarea name='usercomment' COLS='32' ROWS='4' WRAP='SOFT' class='FormData_InputText'>$usercomment</textarea>&nbsp;&nbsp;<small>(*) $profreason</small></td>
  </tr>
  <tr>
    <th class='left'>$langFaculty</th>
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
      <th class='left'>$langLanguage</th>
      <td>";
	$tool_content .= lang_select_options('proflang');
	$tool_content .= "</td>
    </tr>
  <tr>
    <th>&nbsp;</th>
    <td>
      <input type='submit' name='submit' value='$langSubmitNew' />
      <input type='hidden' name='auth' value='1' />
    </td>
  </tr>
  </thead>
  </table>
    <div align='right'><small>$langRequiredFields</small></div>
  </td>
</tr>
</thead>
</table>

</form>

<br>";

} else {

// registration
$registration_errors = array();

    // check if there are empty fields
    if (empty($nom_form) or empty($prenom_form) or empty($userphone)
	 or empty($usercomment) or empty($uname) or (empty($email_form))) {
      $registration_errors[]=$langEmptyFields;
	   }

    if (count($registration_errors) == 0) {    // registration is ok
            // ------------------- Update table prof_request ------------------------------
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
                            default:  $password = "";
                                      break;
                    }
            }

            db_query('INSERT INTO prof_request SET
                                profname = ' . autoquote($prenom_form). ',
                                profsurname = ' . autoquote($nom_form). ',
                                profuname = ' . autoquote($uname). ',
                                profemail = ' . autoquote($email_form). ',
                                proftmima = ' . autoquote($department). ',
                                profcomm = ' . autoquote($userphone). ',
                                status = 1,
                                statut = 1,
                                date_open = NOW(),
                                comment = ' . autoquote($usercomment). ',
                                lang = ' . autoquote($proflang),
                     $mysqlMainDb);

            //----------------------------- Email Message --------------------------
            $MailMessage = $mailbody1 . $mailbody2 . "$prenom_form $nom_form\n\n" . $mailbody3 .
                    $mailbody4 . $mailbody5 . "$mailbody6\n\n" . "$langFaculty: " .
                    find_faculty_by_id($department) . "\n$langComments: $usercomment\n" .
                    "$langProfUname: $uname\n$langProfEmail: $email_form\n" .
                    "$contactphone: $userphone\n\n\n$logo\n\n";

            if (!send_mail('', $emailhelpdesk, $gunet, $emailhelpdesk, $mailsubject, $MailMessage, $charset))
            {
                    $tool_content .= "<table width='99%'>
                            <tbody><tr>
                            <td class='caution' height='60'>
                            <p>$langMailErrorMessage &nbsp; <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a></p>
                            </td>
                            </tr></tbody></table>";
                    draw($tool_content,0);
                    exit();
            }

            //------------------------------------User Message ----------------------------------------
            $tool_content .= "<table width='99%'><tbody>
                    <tr>
                    <td class='well-done' height='60'>
                    <p>$langDearProf</p><p>$success</p><p>$infoprof</p>
                    <p><a href='$urlServer'>$langBack</a></p>
                    </td>
                    </tr></tbody></table>";
    }

	else	{  // errors exist - registration failed
            $tool_content .= "<table width='99%'><tbody><tr>" .
                              "<td class='caution' height='60'>";
                foreach ($registration_errors as $error) {
                        $tool_content .= "<p>$error</p>";
                }
	       $tool_content .= "<p><a href='$_SERVER[PHP_SELF]?prenom_form=$_POST[prenom_form]&amp;nom_form=$_POST[nom_form]&amp;userphone=$_POST[userphone]&amp;uname=$_POST[uname]&amp;email_form=$_POST[email_form]&amp;usercomment=$_POST[usercomment]'>$langAgain</a></p>" .
                "</td></tr></tbody></table><br /><br />";
	}

} // end of submit

draw($tool_content,0);

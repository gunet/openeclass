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


$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$nameTools = $langNewUser;
$navigation[]= array ("url"=>"../admin/", "name"=> $langAdmin);

// Initialise $tool_content
$tool_content = "";
$submit = isset($_POST['submit'])?$_POST['submit']:'';
// ----------------------------
// register user
// ----------------------------

if($submit) {
   // register user
  $nom_form = isset($_POST['nom_form'])?$_POST['nom_form']:'';
  $prenom_form = isset($_POST['prenom_form'])?$_POST['prenom_form']:'';
  $uname = isset($_POST['uname'])?$_POST['uname']:'';
  $password = isset($_POST['password'])?$_POST['password']:'';
  $email_form = isset($_POST['email_form'])?$_POST['email_form']:'';
  $department = isset($_POST['department'])?$_POST['department']:'';
  $localize = isset($_POST['localize'])?$_POST['localize']:'';
  $lang = langname_to_code($localize);	

      // check if user name exists
  $username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='$uname'");
  while ($myusername = mysql_fetch_array($username_check)) {
    $user_exist=$myusername[0];
  }

// check if there are empty fields
  if (empty($nom_form) or empty($prenom_form) or empty($password)
        or empty($uname) or empty($email_form)) {
      $tool_content .= error_screen($langEmptyFields);
      $tool_content .= end_tables();
  }
  elseif(isset($user_exist) and $uname==$user_exist) {
      $tool_content .= error_screen($langUserFree);
      $tool_content .= end_tables();
 }

// check if email syntax is valid
 elseif(!email_seems_valid($email_form)) {
        $tool_content .= error_screen($langEmailWrong);
        $tool_content .= end_tables();
 }


// registration accepted

  else {
    $emailsubject = "$langYourReg $siteName"; // $langAsUser

      $emailbody = "
$langDestination $prenom_form $nom_form

$langYouAreReg$siteName, $langSettings $uname
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

$administratorName $administratorSurname
$langManager $siteName
$langTel $telephone
$langEmail : $emailhelpdesk
";

send_mail('', '', '', $email_form, $emailsubject, $emailbody, $charset);


// register user
    $registered_at = time();
    $expires_at = time() + $durationAccount;

    $password_encrypted = md5($password);
    $s = mysql_query("SELECT id FROM faculte WHERE name='$department'");
    $dep = mysql_fetch_array($s);
    $inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
      (user_id, nom, prenom, username, password, email, statut, department, registered_at, expires_at, lang)
      VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email_form', '5', '$dep[id]', '$registered_at', '$expires_at', '$lang')");

    // close request
        $rid = intval($_POST['rid']);
        db_query("UPDATE prof_request set status = '2',
         date_closed = NOW() WHERE rid = '$rid'");

    $tool_content .= "<tr><td valign='top' align='center' class='alert1'>$usersuccess
    <br><br><a href='../admin/listreq.php?type=user' class='mainpage'>$langBack</a>";
  }

} else {

//---------------------------
// 	display form
// ---------------------------

if (isset($_GET['lang'])) {
	$lang = $_GET['lang'];
	$lang = langname_to_code($language);
}

$tool_content .= "<table width=\"99%\"><tbody>
   <tr>
    <td>
    <form action='$_SERVER[PHP_SELF]' method='post'>
    <table border=0 cellpadding='1' cellspacing='2' border='0' width='100%' align=center>
	<thead>
    <tr>
    <th class='left' width=20%>$langSurname</th>
	 <td><input type='text' class=auth_input_admin name='nom_form' value='".@$ps."' >
	<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class='left'>$langName</th>
	  <td><input type='text' class=auth_input_admin name='prenom_form' value='".@$pn."' >
	<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class='left'>$langUsername</th>
	  <td><input type='text' class=auth_input_admin name='uname' value='".@$pu."'>
		<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class='left'>$langPass&nbsp;:</th>
	  <td><input type='text' class=auth_input_admin name='password' value=".create_pass()."></td>
	  </tr>
	  <tr>
    	<th class='left'>$langEmail</th>
	  <td><input type='text' class=auth_input_admin name='email_form' value='".@$pe."'>
		<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class='left'>$langFaculty &nbsp;
		</span></th><td>";

	$dep = array();
        $deps=db_query("SELECT name FROM faculte order by id");
			while ($n = mysql_fetch_array($deps))
				$dep[$n[0]] = $n['name'];  

		if (isset($pt))
			$tool_content .= selection ($dep, 'department', $pt);
		else 
			$tool_content .= selection ($dep, 'department');
 
	$tool_content .= "<tr><th class='left'>$langLanguage</th><td>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td></tr>";

	$tool_content .= "</td></tr><tr><td colspan=\"2\">".$langRequiredFields."</td></tr>
		<tr><td>&nbsp;</td>
		<td><input type=\"submit\" name=\"submit\" value=\"".$langSubmit."\" ></td>
		</tr></thead></table>
		<input type='hidden' name='rid' value='".@$id."'>
		</tbody></table></form>";
    $tool_content .= "<center><p><a href=\"../admin/index.php\">$langBack</p></center>";

} // end of if 

draw($tool_content,3, 'auth');

// -----------------
// functions
// -----------------
function error_screen($message) {

	global $langTryAgain;

	return "<tr height='80'><td colspan='3' valign='top' align='center' class=alert1>$message</td></tr><br><br>
      <tr height='30' valign='top' align='center'><td align=center>
      <a href='../admin/listreq.php?type=user' class=mainpage>$langTryAgain</a><br><br></td></tr>";
}

function end_tables() {
	global $langBack;
	
	$retstring = "</td></tr><tr><td align=right valign=bottom height='180'>";
	$retstring .= "<a href='../admin/index.php' class=mainpage>$langBack&nbsp;</a>";
	$retstring .= "</td></tr></table>";
	
	return $retstring;
}

?>

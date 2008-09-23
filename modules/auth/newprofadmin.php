<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

$require_admin = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langProfReg;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);

// Initialise $tool_content
$tool_content = "";

$submit = isset($_POST['submit'])?$_POST['submit']:'';
if($submit) {
		
	// register user
	$nom_form = isset($_POST['nom_form'])?$_POST['nom_form']:'';
	$prenom_form = isset($_POST['prenom_form'])?$_POST['prenom_form']:'';
	$uname = isset($_POST['uname'])?$_POST['uname']:'';
	$password = isset($_POST['password'])?$_POST['password']:'';
	$email_form = isset($_POST['email_form'])?$_POST['email_form']:'';
	$department = isset($_POST['department'])?$_POST['department']:'';
	$localize = isset($_POST['localize'])?$_POST['localize']:'';
	if ($localize == 'greek')
		$lang = 'el';
	elseif ($localize == 'english')
		$lang = 'en';

		// check if user name exists
		$username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($uname)."'");
		while ($myusername = mysql_fetch_array($username_check))
		{
			$user_exist=$myusername[0];
		}

		// check if there are empty fields
		if (empty($nom_form) or empty($prenom_form) or empty($password) or empty($department) or empty($uname) or (empty($email_form)))
		{
			$tool_content .= "<p class=\"caution_small\">$langEmptyFields</p>
			<br><br><p align=\"right\"><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
		}
		elseif(isset($user_exist) and $uname==$user_exist)
		{
			$tool_content .= "<p class=\"caution_small\">$langUserFree</p>
			<br><br><p align=\"right\"><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
	  	}
		elseif(!email_seems_valid($email_form)) // check if email syntax is valid
		{
      			$tool_content .= "<p class=\"caution_small\">$langEmailWrong.</p>
			<br><br><p align=\"right\"><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
		}
		else
		{
			$s = mysql_query("SELECT id FROM faculte WHERE name='$department'");
			$dep = mysql_fetch_array($s);
			$registered_at = time();
	 		$expires_at = time() + $durationAccount;
			$password_encrypted = md5($password);
			$uname = escapeSimple($uname);
			$inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
				(user_id, nom, prenom, username, password, email, statut, department, registered_at, expires_at,lang)
				VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email_form','$statut','$dep[id]', '$registered_at', '$expires_at', '$lang')");
			$last_id=mysql_insert_id();

		// close request
	  	$rid = intval($_POST['rid']);
  	  	db_query("UPDATE prof_request set status = '2',date_closed = NOW() WHERE rid = '$rid'");
	       	$tool_content .= "<p class=\"success_small\">$profsuccess</p><br><br><p align=\"right\"><a href='../admin/listreq.php'>$langBackReq</a></p>";
		}
} else {

// if not submit then display the form
if (isset($_GET['lang'])) {
	$lang = $_GET['lang'];
	if ($lang == 'el')
		$language = 'greek';
	elseif ($lang == 'en')
		$language = 'english';
}

$tool_content .= "
    <form action=\"$_SERVER[PHP_SELF]\" method=\"post\">
    <table width=\"99%\" align=\"left\" class=\"FormData\">
    <tbody>
    <tr>
      <th width=\"220\">&nbsp;</th>
      <td><b>$langNewProf</b></td>
    </tr>
    <tr>
      <th class='left'><b>".$langSurname."</b></th>
      <td><input class='FormData_InputText' type=\"text\" name=\"nom_form\" value=\"".@$ps."\" >&nbsp;(*)</td>
    </tr>
    <tr>
      <th class='left'><b>".$langName."</b></th>
      <td><input class='FormData_InputText' type=\"text\" name=\"prenom_form\" value=\"".@$pn."\">&nbsp;(*)</td>
    </tr>
    <tr>
      <th class='left'><b>".$langUsername."</b></th>
      <td><input class='FormData_InputText' type=\"text\" name=\"uname\" value=\"".@$pu."\">&nbsp;(*)</td>
    </tr>
    <tr>
      <th class='left'><b>".$langPass."&nbsp;:</b></th>
      <td><input class='FormData_InputText' type=\"text\" name=\"password\" value=\"".create_pass(5)."\"></td>
    </tr>
    <tr>
      <th class='left'><b>".$langEmail."</b></th>
      <td><input class='FormData_InputText' type=\"text\" name=\"email_form\" value=\"".@$pe."\">&nbsp;(*)</b></td>
    </tr>
    <tr>
      <th class='left'>".$langDepartment.":</th>
      <td><select name=\"department\" class=\"auth_input\">";
        $deps=mysql_query("SELECT name FROM faculte order by id");
        while ($dep = mysql_fetch_array($deps))
        {
        	$tool_content .= "<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
        $tool_content .= "</select>
      </td>
    </tr>
	<tr>
      <th class='left'>$langLanguage</th>
      <td>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type=\"submit\" name=\"submit\" value=\"".$langSubmit."\" >
          <input type=\"hidden\" name=\"auth\" value=\"1\" >&nbsp;
          <small>".$langRequiredFields."</small></td>
    </tr>
    <input type='hidden' name='rid' value='".@$id."'>
    </tbody>
    </table>
    </form>";

$tool_content .= "
    <br />
    <p align=\"right\"><a href=\"../admin/index.php\">$langBack</p>";
}
draw($tool_content, 3, 'admin');
?>

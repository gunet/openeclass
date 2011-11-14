<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


/*===========================================================================
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
  @Description: This script/file tries to authenticate the user, using
  his user/pass pair and the authentication method defined by the admin

==============================================================================
*/

$require_usermanage_user = TRUE;

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
require_once 'auth.inc.php';

$auth = isset($_REQUEST['auth'])?$_REQUEST['auth']:'';

$msg = "$langProfReg (".(get_auth_info($auth)).")";

$nameTools = $msg;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../admin/listreq.php", "name" => $langOpenProfessorRequests);
$tool_content = "";

$submit = isset($_POST['submit'])?$_POST['submit']:'';
// professor registration
if ($submit)  {
        $pn = $_POST['pn'];
        $ps = $_POST['ps'];
        $pu = $_POST['pu'];
        $pe = $_POST['pe'];
        $department = $_POST['department'];
        $comment = isset($_POST['comment'])?$_POST['comment']:'';
        $lang = $_POST['language'];
        if (!isset($native_language_names[$lang])) {
		$lang = langname_to_code($language);
	}

	// check if user name exists
    	$username_check = db_query("SELECT username FROM `$mysqlMainDb`.user 
			WHERE username=".autoquote($pu));
	if (mysql_num_rows($username_check) > 0) {
		$tool_content .= "<p class='caution'>$langUserFree</p><br><br><p align='right'>
		<a href='../admin/listreq.php'>$langBackRequests</a></p>";
		draw($tool_content, 3);
		exit();
	}

        switch($auth)
        {
          case '2': $password = "pop3";
            break;
          case '3': $password = "imap";
            break;
          case '4': $password = "ldap";
            break;
          case '5': $password = "db";
            break;
					case '6': $password = "shibboleth";
						break;
          case '7': $password = "cas";
            break;
          default:  $password = "";
            break;
        }

	$registered_at = time();
        $expires_at = time() + $durationAccount;

	$verified_mail = isset($_REQUEST['verified_mail'])?intval($_REQUEST['verified_mail']):2;

	$sql = db_query("INSERT INTO `$mysqlMainDb`.user
			(nom, prenom, username, password, email, statut, department,
			am, registered_at, expires_at, lang, verified_mail)
			VALUES (" .
			autoquote($ps) . ', ' .
			autoquote($pn) . ', ' .
			autoquote($pu) . ", '$password', " .
			autoquote($pe) .
			", 1, $department, " . autoquote($comment) . ", $registered_at, $expires_at, '$lang', $verified_mail)");

	// Close user request 
	$rid = intval($_POST['rid']);
	db_query("UPDATE user_request set status = 2, date_closed = NOW(), verified_mail=$verified_mail WHERE id = $rid");
		$emailbody = "$langDestination $pn $ps\n" .
                                "$langYouAreReg $siteName $langSettings $pu\n" .
                                "$langPass: $langPassSameAuth\n$langAddress $siteName: " .
                                "$urlServer\n$langProblem\n$langFormula" .
                                "$administratorName $administratorSurname\n" .
                                "$langManager $siteName \n$langTel $telephone \n" .
                                "$langEmail: $emailhelpdesk";

	if (!send_mail('', '', '', $pe, $mailsubject, $emailbody, $charset))  {
		$tool_content .= "<table width='99%'><tbody><tr>
		<td class='caution' height='60'>
		<p>$langMailErrorMessage &nbsp; <a href=\"mailto:$emailhelpdesk\">$emailhelpdesk</a></p>
		</td></tr></tbody></table>";
		draw($tool_content, 3);
        	exit();
	}

	// user message
	$tool_content .= "
	<p class='success'>$profsuccess<br><br>
	<a href='../admin/listreq.php'>$langBackRequests</a></p>";

} else { 
	// if not submit then display the form
	if (isset($_GET['id'])) { // if we come from prof request
		$id = $_GET['id'];
		// display actions toolbar
		$tool_content .= "<div id='operations_container'>
		<ul id='opslist'>
		<li><a href='../admin/listreq.php?id=$id&amp;close=1' onclick='return confirmation();'>$langClose</a></li>
		<li><a href='../admin/listreq.php?id=$id&amp;close=2'>$langRejectRequest</a></li>";
        if (isset($_GET['id'])) {
                $tool_content .= "
                <li><a href='../admin/listreq.php'>$langBackRequests</a>";
        }
                $tool_content .= "
		</ul></div>";
		$res = mysql_fetch_array(db_query("SELECT name, surname, uname, email, 
			faculty_id, comment, lang, date_open, phone, am, verified_mail FROM user_request WHERE id = $id"));
		$ps = $res['surname'];
		$pn = $res['name'];
		$pu = $res['uname'];
		$pe = $res['email'];
		$pt = intval($res['faculty_id']);
		$pcom = $res['comment'];
		$pam = $res['am'];
		$pphone = $res['phone'];
		$lang = $res['lang'];
		$pvm = intval($res['verified_mail']);
		$pdate = nice_format(date('Y-m-d', strtotime($res['date_open'])));
	}
	
	@$tool_content .= "
      <form action='$_SERVER[PHP_SELF]' method='post'>
      <fieldset>
      <legend>$langNewProf</legend>
	<table width='100%' class='tbl'>
	<tr>
	<th class='left' width='180'><b>".$langSurname."</b></th>
	<td>$ps<input type='hidden' name='ps' value='$ps'></td>
	</tr>
	<tr>
	<th class='left'><b>$langName</b></th>
	<td>$pn<input type='hidden' name='pn' value='$pn'></td>
	</tr>
	<tr>
	<th class='left'><b>$langUsername</b></th>
	<td>$pu<input type='hidden' name='pu' value='$pu'></td>
	</tr>
	<tr>
	<th class='left'><b>$langEmail</b></th>
	<td>$pe
	<input type='hidden' name='pe' value='$pe' ></td>
	</tr>
	<tr>
	<th class='left'><b>$langEmailVerified</b></th>
	<td>";

	$verified_mail_data = array();
	$verified_mail_data[0] = $m['pending'];
	$verified_mail_data[1] = $m['yes'];
	$verified_mail_data[2] = $m['no'];

	$tool_content .= selection($verified_mail_data,"verified_mail",$pvm);

	$tool_content .= "</td>
	</tr>
	<tr>
	<th class='left'>$langFaculty</th>
	<td>";
        $result = db_query("SELECT id, name FROM faculte ORDER BY id");
        while ($facs = mysql_fetch_array($result)) {
                $faculte_names[$facs['id']] = $facs['name'];
        }
        $tool_content .= selection($faculte_names, 'department', $pt) .
                         "</td></tr>";
	$tool_content .= "<tr>
	<th class='left'>$langLanguage</th>
	<td>";
	$tool_content .= lang_select_options('language', '', $lang);
	$tool_content .= "</td></tr>";
	$tool_content .= "<tr><th class='left'><b>$langPhone</b></th>
	<td>".@q($pphone)."&nbsp;</td></tr>
	<tr>
	<th class='left'><b>$langComments</b></th>
	<td>".@q($pcom)."&nbsp;</td>
	</tr>
	<tr>
	<th class='left'><b>$langDate</b></th>
	<td>".@q($pdate)."&nbsp;</td>
	</tr>
	<tr><th>&nbsp;</th>
	<td><input type='submit' name='submit' value='".$langSubmit."' >
	<input type='hidden' name='auth' value='$auth' >
	</td></tr>
	</table>
	<input type='hidden' name='rid' value='".@$id."'>
      </fieldset>
      </form>";
	$tool_content .= "<p align='right'><a href='../admin/index.php'>$langBack</a></p>";
 }
draw($tool_content, 3);

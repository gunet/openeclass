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

/*===========================================================================
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
  @Description: This script/file tries to authenticate the user, using
  his user/pass pair and the authentication method defined by the admin

==============================================================================
*/

$require_admin = TRUE;

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
require_once 'auth.inc.php';

$msg = "$langProfReg (".(get_auth_info($auth)).")";
$nameTools = $msg;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../admin/listreq.php", "name" => $langOpenProfessorRequests);
$tool_content = "";

$submit = isset($_POST['submit'])?$_POST['submit']:'';
// professor registration
if ($submit)  {
        $auth = $_POST['auth'];
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
		$tool_content .= "<p class='caution_small'>$langUserFree</p><br><br><p align='right'>
		<a href='../admin/listreq.php'>$langBackRequests</a></p>";
		draw($tool_content, 3, 'auth');
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
          default:  $password = "";
            break;
        }

	$registered_at = time();
        $expires_at = time() + $durationAccount;

	$sql = db_query("INSERT INTO `$mysqlMainDb`.user
			(nom, prenom, username, password, email, statut, department,
			am, registered_at, expires_at,lang)
			VALUES (" .
			autoquote($ps) . ', ' .
			autoquote($pn) . ', ' .
			autoquote($pu) . ", '$password', " .
			autoquote($pe) .
			", 1, $department, " . autoquote($comment) . ", $registered_at, $expires_at, '$lang')");

	//  Update table prof_request 
	$rid = intval($_POST['rid']);
	db_query("UPDATE prof_request set status = '2',date_closed = NOW() WHERE rid = '$rid'");
		$emailbody = "$langDestination $pu $ps\n" .
                                "$langYouAreReg $siteName $langSettings $pu\n" .
                                "$langPass: $password\n$langAddress $siteName: " .
                                "$urlServer\n$langProblem\n$langFormula" .
                                "$administratorName $administratorSurname" .
                                "$langManager $siteName \n$langTel $telephone \n" .
                                "$langEmail: $emailhelpdesk";

	if (!send_mail('', '', '', $pe, $mailsubject, $emailbody, $charset))  {
		$tool_content .= "<table width='99%'><tbody><tr>
		<td class='caution' height='60'>
		<p>$langMailErrorMessage &nbsp; <a href=\"mailto:$emailhelpdesk\">$emailhelpdesk</a></p>
		</td></tr></tbody></table>";
		draw($tool_content, 3, 'auth');
        	exit();
	}

	// user message
	$tool_content .= "<table width='99%'><tbody><tr>
	<td class='well-done' height='60'>
	<p>$profsuccess</p><br><br>
	<center><p><a href='../admin/listreq.php'>$langBackRequests</a></p></center>
	</td>
	</tr></tbody></table>";

} else { 
	// if not submit then display the form
	if (isset($id)) { // if we come from prof request
		$res = mysql_fetch_array(db_query("SELECT profname,profsurname, profuname, profemail, 
			proftmima, comment, lang FROM prof_request WHERE rid='$id'"));
		$ps = $res['profsurname'];
		$pn = $res['profname'];
		$pu = $res['profuname'];
		$pe = $res['profemail'];
		$pt = $res['proftmima'];
		$pcom = $res['comment'];
		$lang = $res['lang'];
	}

	$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
	<table width='99%' class='FormData'>
	<tbody>
	<tr>
	<th width='220'>&nbsp;</th>
	<td><b>$langNewProf</b></td>
	</tr>
	<tr>
	<th class='left'><b>".$langSurname."</b></th>
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
	<td>$pe</b></td>
	<input type='hidden' name='pe' value='$pe' >
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
	<th class='left'><b>$langComments</b></th>
	<td><input class='FormData_InputText' type='text' name='comment' value='".@q($pcom)."'>&nbsp;</b></td>
	</tr>
	<tr>
	<th class='left'>$langLanguage</th>
	<td>";
	$tool_content .= lang_select_options('language', '', $lang);
	$tool_content .= "</td></tr>
	<tr><th>&nbsp;</th>
	<td><input type='submit' name='submit' value='".$langSubmit."' >
	<input type='hidden' name='auth' value='$auth' >
	</td></tr>
	<input type='hidden' name='rid' value='".@$id."'>
	</tbody>
	</table>
	</form>";
 }
draw($tool_content, 3, 'auth');

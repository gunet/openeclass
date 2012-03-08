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

$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
include '../auth/auth.inc.php';

$head_content = '
<script type="text/javascript">
function confirmation() {
   if (confirm("'.$langCloseConf.'")) {
                return true;
   } else {
          return false;
  }
}
</script>';

$basetoolurl = $_SERVER['PHP_SELF'];
if (isset($_GET['type']) and $_GET['type'] == 'user') {
        $list_statut = 5;
        $nameTools = $langUserOpenRequests;
        $reqtype = '&amp;type=user';
        $basetoolurl .= '?type=user';
	$linkreg = $langUserDetails;
	$linkget = '?type=user';
} else {
        $list_statut = 1;
        $nameTools = $langOpenProfessorRequests;
        $reqtype = '';
	$linkreg = $langProfReg;
	$linkget = '';
}
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

// Main body
$close = isset($_GET['close'])?$_GET['close']:(isset($_POST['close'])?$_POST['close']:'');
$id = isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:'');
$show = isset($_GET['show'])?$_GET['show']:(isset($_POST['show'])?$_POST['show']:'');

// Deal with navigation
switch ($show) {
	case "closed":
		$navigation[] = array("url" => $basetoolurl, "name" => $nameTools);
		$nameTools = $langReqHaveClosed;
		break;
	case "rejected":
		$navigation[] = array("url" => $basetoolurl, "name" => $nameTools);
		$nameTools = $langReqHaveBlocked;
		break;
}

 // Display Actions Toolbar
  $tool_content .= "
      <div id='operations_container'>
        <ul id='opslist'>
	  <li><a href='newuseradmin.php$linkget'>$linkreg</a></li>
          <li><a href='$_SERVER[PHP_SELF]?show=closed$reqtype'>$langReqHaveClosed</a></li>
          <li><a href='$_SERVER[PHP_SELF]?show=rejected$reqtype'>$langReqHaveBlocked</a></li>
        </ul>
      </div>";

// -----------------------------------
// display closed requests 
// ----------------------------------
if (!empty($show) and $show == 'closed') {
	if (!empty($id) and $id > 0) {
		// restore request
		$sql = db_query("UPDATE user_request set status = 1, date_closed = NULL WHERE id = $id");
		$tool_content = "<p class='success'>$langReintroductionApplication</p>";
	} else {
		$tool_content .= "<table class='tbl_alt' width='100%'>";
		$tool_content .= table_header(1, $langDateClosed_small);
 		$sql = db_query("SELECT id, name, surname, uname, email, faculty_id,
                                        phone, am, date_open, date_closed, comment
                                        FROM user_request
                                        WHERE (status = 2 AND statut = $list_statut) ORDER BY date_open DESC");
        	$k = 0;
		while ($req = mysql_fetch_array($sql)) {
			if ($k%2 == 0) {
	              		$tool_content .= "\n  <tr class='even'>";
	            	} else {
	              		$tool_content .= "\n  <tr class='odd'>";
	            	}
	        	$tool_content .= "<td width='1'>
			<img style='border:0px;' src='$themeimg/arrow.png' title='bullet'></td>";
			$tool_content .= '<td>'.q($req['name'])."&nbsp;".q($req['surname'])."";
			$tool_content .= '<td>'.q($req['uname']).'</td>';
			$tool_content .= '<td>'.q(find_faculty_by_id($req['faculty_id'])).'</td>';
			$tool_content .= "<td align='center'>
				<small>".nice_format(date('Y-m-d', strtotime($req['date_open'])))."</small></td>";
            		$tool_content .= "<td align='center'>
				<small>".nice_format(date('Y-m-d', strtotime($req['date_closed'])))."</small></td>";
			$tool_content .= "<td align='center'>
			<a href='$_SERVER[PHP_SELF]?id=$req[id]&amp;show=closed$reqtype'>$langRestore</a></td>\n  </tr>";
			$k++;
		}
	}
	$tool_content .= "\n  </table>\n";

// -----------------------------------
// display rejected requests 
// ----------------------------------
} elseif (!empty($show) && ($show == 'rejected')) {
	if (!empty($id) && ($id > 0)) {
	// restore request
		$sql = db_query("UPDATE user_request set status = 1, date_closed = NULL WHERE id = $id");
		$tool_content = "
		<p class=\"success\">$langReintroductionApplication</p>";
	} else {
		$tool_content .= "<table class=\"tbl_alt\" width=\"100%\" align=\"left\">";
		$tool_content .= table_header(1, $langDateReject_small);
 		$sql = db_query("SELECT id, name, surname, uname, email,
                                        faculty_id, phone, am, date_open, date_closed, comment
                                        FROM user_request
                                        WHERE (status = 3 AND statut = $list_statut) ORDER BY date_open DESC");
        	$k = 0;
		while ($req = mysql_fetch_array($sql)) {
			if ($k%2==0) {
	              		$tool_content .= "\n  <tr class='even'>";
	            	} else {
	              		$tool_content .= "\n  <tr class=\"odd\">";
	            	}
	    		$tool_content .= "<td width='1'>
			<img src='$themeimg/arrow.png' title='bullet'></td>";
			$tool_content .= "<td>".q($req['name'])."&nbsp;".q($req['surname'])."</td>";
                        $tool_content .= "<td>".q($req['uname'])."&nbsp;</td>";
			$tool_content .= "<td>".q(find_faculty_by_id($req['faculty_id']))."</td>";
			$tool_content .= "<td align='center'>
				<small>".nice_format(date('Y-m-d', strtotime($req['date_open'])))."</small></td>";
                	$tool_content .= "<td align='center'>
				<small>".nice_format(date('Y-m-d', strtotime($req['date_closed'])))."</small></td>";
			$tool_content .= "<td align=center>
			<a href='$_SERVER[PHP_SELF]?id=$req[id]&amp;show=closed$reqtype'>$langRestore</a>
			</td></tr>";
			$k++;
		}
	}
	$tool_content .= "</table>";

// ------------------------------
// close request
// ------------------------------
} elseif(!empty($close)) {
	switch($close) {
	case '1':
		$sql = db_query("UPDATE user_request SET status = 2, date_closed = NOW() WHERE id = $id");
                if ($list_statut == 1) {
        		$tool_content .= "<div class='info'>$langProfessorRequestClosed</div>";
                } else {
        		$tool_content .= "<div class='info'$langRequestStudent</div>";
                }
		break;
	case '2':
		$submit = isset($_POST['submit'])? $_POST['submit']: '';
		if(!empty($submit)) {
			// post the comment and do the delete action
			if (!empty($_POST['comment'])) {
                                $sql = "UPDATE user_request
                                               SET status = 3, date_closed = NOW(),
                                                   comment = " . autoquote($_POST['comment']) . "
                                               WHERE id = $id";
				if (db_query($sql)) {
					if (isset($_POST['sendmail']) and ($_POST['sendmail'] == 1)) {
						$emailsubject = $langemailsubjectBlocked;
						$emailbody = "$langemailbodyBlocked
$langComments:> $_POST[comment]
$langManager $siteName
$administratorName $administratorSurname
$langPhone: $telephone
$langEmail: $emailhelpdesk";
						send_mail('', '', "$_POST[prof_name] $_POST[prof_surname]", $_POST['prof_email'], $emailsubject, $emailbody, $charset);
					}
					$tool_content .= "<p class='success'>" .  ($list_statut == 1)? $langTeacherRequestHasRejected: $langRequestReject;
					$tool_content .= " $langRequestMessageHasSent <b>$_POST[prof_email]</b></p>";
					$tool_content .= "<br><p><b>$langComments:</b><br />$_POST[comment]</p>\n";
				}
			}
		} else {
			// display the form
			$r = db_query("SELECT comment, name, surname, email, statut
                                              FROM user_request WHERE id = $id");
			$d = mysql_fetch_assoc($r);
                        $warning = ($d['statut'] == 5)? $langWarnReject: $langGoingRejectRequest;
			$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
			<div class='alert1'>$warning</div>
			<table width='100%' class='tbl_border'>
			<tr><th class='left'>$langName</th>
			<td>".q($d['name'])."</td></tr>
			<tr><th class='left'>$langSurname</th>
			<td>".q($d['surname'])."</td></tr>
			<tr><th class='left'>$langEmail</th>
			<td>".q($d['email'])."</td></tr>
			<tr><th class='left'>$langComments</th>
			<td>
			<input type='hidden' name='id' value='".$id."'>
			<input type='hidden' name='close' value='2'>
			<input type='hidden' name='prof_name' value='".q($d['name'])."'>
			<input type='hidden' name='prof_surname' value='".q($d['surname'])."'>
			<textarea class='auth_input' name='comment' rows='5' cols='60'>".q($d['comment'])."</textarea>
			</td></tr>
			<tr><th class='left'>$langRequestSendMessage</th>
			<td>&nbsp;<input type='text' class='auth_input' name='prof_email' value='".q($d['email'])."'>
			<input type='checkbox' name='sendmail' value='1' checked='yes'> <small>($langGroupValidate)</small>
			</td></tr>
			<tr><th class='left'>&nbsp;</th>
			<td><input type='submit' name='submit' value='$langRejectRequest'>&nbsp;&nbsp;<small>($langRequestDisplayMessage)</small></td>
			</tr></table>
			</form>";
			}
		break;
	default:
		break;
	} // end of switch
}

// -----------------------------------
// display all the requests
// -----------------------------------
else
{
	
	// show username as well (useful)
 	$sql = db_query("SELECT id, name, surname, uname, faculty_id, date_open, comment, password FROM user_request
                                WHERE (status = 1 AND statut = $list_statut)");
        if (mysql_num_rows($sql) > 0) {
                $tool_content .= "<table class='tbl_alt' width='100%'>";
                $tool_content .= table_header();
                $k = 0;
                while ($req = mysql_fetch_array($sql)) {
                        if ($k%2 == 0) {
                              $tool_content .= "\n<tr class='even'>";
                        } else {
                              $tool_content .= "\n<tr class='odd'>";
                        }
                        $tool_content .= "<td align='right' width='1'>
                        <img src='$themeimg/arrow.png' title='bullet'></td>";
                        $tool_content .= "<td>".q($req['name'])."&nbsp;".q($req['surname'])."</td>";
                        $tool_content .= "<td>".q($req['uname'])."</td>";
                        $tool_content .= "<td>".q(find_faculty_by_id($req['faculty_id']))."</td>";
                        $tool_content .= "<td align='center'>
                                <small>".nice_format(date('Y-m-d', strtotime($req['date_open'])))."</small></td>";
                        $tool_content .= "<td align='center' class='smaller'>";
                        switch($req['password']) {
                            case 'pop3':
                                $tool_content .= "<a href='../auth/ldapnewprofadmin.php?id=$req[id]&amp;auth=2'>
                                                  $langElaboration<br>($langViaPop)</a>";
                                break;
                            case 'imap':
                                $tool_content .= "<a href='../auth/ldapnewprofadmin.php?id=$req[id]&amp;auth=3'>
                                                  $langElaboration<br>($langViaImap)</a>";
                                break;
                            case 'ldap':
                                 $tool_content .= "<a href='../auth/ldapnewprofadmin.php?id=$req[id]&amp;auth=4'>
                                                   $langElaboration<br />($langViaLdap)</a>";
                                 break;
                            case 'db':
                                 $tool_content .= "<a href='../auth/ldapnewprofadmin.php?id=$req[id]&amp;auth=5'>
                                                   $langElaboration<br>($langViaDB)</a>";
                                 break;
                            case 'shibboleth':
                                 $tool_content .= "<a href='../auth/ldapnewprofadmin.php?id=$req[id]&amp;auth=6'>
                                                   $langElaboration<br>($langViaShibboleth)</a>";
                                 break;
                            case 'cas':
                                 $tool_content .= "<a href='../auth/ldapnewprofadmin.php?id=$req[id]&amp;auth=7'>
                                                   $langElaboration<br>($langViaCAS)</a>";
                                 break;
                            default:
                                 $tool_content .= "<a href='newuseradmin.php?id=$req[id]'>
                                                   $langElaboration</a>";
                                 break;
                        }
                        $tool_content .= "</td></tr>\n";
                        $k++;
                }
                $tool_content .= "\n  </table>\n";
        } else {
                $tool_content .= "<p class='alert1'>$langUserNoRequests</p>";
        }       
	
}

// If show is set then we return to listreq, else return to admin index.php
//if (isset($close) or isset($closed)) {
if (!empty($show) or !empty($close)) {
	$tool_content .= "<p align='right'><a href='$_SERVER[PHP_SELF]$linkget'>$langBackRequests</a></p><br>";
}
$tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";

draw($tool_content, 3, null, $head_content);

// --------------------------------------
// function to display table header
// --------------------------------------
function table_header($addon = FALSE, $message = FALSE) {
	
	global $langName, $langSurname, $langFaculty, $langDate, $langActions, $langComments, $langUsername;
	global $langDateRequest_small, $list_statut;

	$string = "";
	if ($addon) { 
		$rowspan=2;
		$datestring = "<th colspan='2'>$langDate</th>
		<th scope='col' rowspan='$rowspan'><div align='center'>$langActions</div></th>
		</tr><tr>
		<th>$langDateRequest_small</th>
		<th>$message</th>";
	} else {
		$rowspan=1;
		$datestring = "<th scope='col'><div align='center'>$langDate<br />$langDateRequest_small</div></th>
		<th scope='col'><div align='center'>$langActions</div></th>";
	}

	$string .= "<tr>
	<th scope='col' colspan='2' rowspan='$rowspan'><div align='left'>&nbsp;&nbsp;$langName $langSurname</div></th>
	<th scope='col' rowspan='$rowspan'><div align='left'>$langUsername</div></th>
	<th scope='col' rowspan='$rowspan'><div align='center'>$langFaculty</div></th>";
	$string .= $datestring; 
	$string .= "</tr>";

return $string;
}

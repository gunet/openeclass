<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

$require_admin = TRUE;
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

// Initialise $tool_content
$tool_content = "";

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
	case "accepted":
		$navigation[] = array("url" => $basetoolurl, "name" => $nameTools);
		$nameTools = $langReqHaveFinished;
		break;
}

 // Display Actions Toolbar
  $tool_content .= "
      <div id='operations_container'>
        <ul id='opslist'>
	  <li><a href='newuseradmin.php$linkget'>$linkreg</a></li>
          <li><a href='$_SERVER[PHP_SELF]?show=closed$reqtype'>$langReqHaveClosed</a></li>
          <li><a href='$_SERVER[PHP_SELF]?show=rejected$reqtype'>$langReqHaveBlocked</a></li>
          <li><a href='$_SERVER[PHP_SELF]?show=accepted$reqtype'>$langReqHaveFinished</a></li>
        </ul>
      </div>";

// -----------------------------------
// display closed requests 
// ----------------------------------
if (!empty($show) && ($show=="closed")) {
	if (!empty($id) && ($id>0)) {
		// restore request
		$sql = db_query("UPDATE prof_request set status='1', date_closed=NULL WHERE rid='$id'");
		$tool_content = "<p class=\"success_small\">$langReintroductionApplication</p>";
	} else {
		$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\">";
		$tool_content .= table_header(1, $langDateClosed_small);
		$tool_content .= "<tbody>";
 		$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,
				profcomm,date_open,date_closed,comment
				FROM prof_request
                                WHERE (status = 2 AND statut = $list_statut)");
        	$k = 0;
		while ($req = mysql_fetch_array($sql)) {
			if ($k%2 == 0) {
	              		$tool_content .= "\n  <tr>";
	            	} else {
	              		$tool_content .= "\n  <tr class=\"odd\">";
	            	}
	        	$tool_content .= "<td width=\"1\">
			<img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
			$tool_content .= "<td>".htmlspecialchars($req['profname'])."&nbsp;".htmlspecialchars($req['profsurname'])."";
			$tool_content .= "<td>".htmlspecialchars($req['profuname'])."</td>";
			if ($req['profemail'] != "") {
				$tool_content .= "<td>
				<a href=\"mailto:".htmlspecialchars($req['profemail'])."\">".
				htmlspecialchars($req['profemail'])."</a></td>";
			} else {
				htmlspecialchars($req['profemail'])."</td>";
			}
			$tool_content .= "<td>".htmlspecialchars($req['proftmima'])."</td>";
			$tool_content .= "<td>".htmlspecialchars($req['profcomm'])."</td>";
			$tool_content .= "<td align=\"center\">
				<small>".nice_format(date("Y-m-d", strtotime($req['date_open'])))."</small></td>";
            		$tool_content .= "<td align=\"center\">
				<small>".nice_format(date("Y-m-d", strtotime($req['date_closed'])))."</small></td>";
            		$tool_content .= "<td>".$req['comment']."</td>";
			$tool_content .= "<td align=center>
			<a href='$_SERVER[PHP_SELF]?id=$req[rid]&show=closed$reqtype'>$langRestore</a></td>\n  </tr>";
		$k++;
		}
	}
	$tool_content .= "\n  </tbody>\n  </table>\n";

// -----------------------------------
// display rejected requests 
// ----------------------------------
} elseif (!empty($show) && ($show=="rejected")) {
	if (!empty($id) && ($id>0)) {
	// restore request
		$sql = db_query("UPDATE prof_request set status='1', date_closed=NULL WHERE rid='$id'");
		$tool_content = "<table><tbody><tr>
		<td class=\"success\">$langReintroductionApplication</td></tr></tbody></table>";
	} else {
		$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\">";
		$tool_content .= table_header(1, $langDateReject_small);
		$tool_content .= "<tbody>";

 		$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,
				proftmima,profcomm,date_open,date_closed,comment
				FROM prof_request
                                WHERE (status = 3 AND statut = $list_statut)");

        	$k = 0;
		while ($req = mysql_fetch_array($sql)) {
			if ($k%2==0) {
	              		$tool_content .= "\n  <tr>";
	            	} else {
	              		$tool_content .= "\n  <tr class=\"odd\">";
	            	}
	    		$tool_content .= "<td width='1'>
			<img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
			$tool_content .= "<td>".htmlspecialchars($req['profname'])."&nbsp;".htmlspecialchars($req['profsurname'])."";
			$tool_content .= "<td>".htmlspecialchars($req['profuname'])."</td>";
			if ($req['profemail'] != "") {
				$tool_content .= "<td><a href=\"mailto:".
				htmlspecialchars($req['profemail'])."\">".
				htmlspecialchars($req['profemail'])."</a></td>";
			} else {
				$tool_content .= "<td>".htmlspecialchars($req['profemail'])."</td>";
			}
			$tool_content .= "<td>".htmlspecialchars($req['proftmima'])."</td>";
			$tool_content .= "<td>".htmlspecialchars($req['profcomm'])."</td>";
			$tool_content .= "<td align=\"center\">
				<small>".nice_format(date("Y-m-d", strtotime($req['date_open'])))."</small></td>";
                	$tool_content .= "<td align=\"center\">
				<small>".nice_format(date("Y-m-d", strtotime($req['date_closed'])))."</small></td>";
                	$tool_content .= "<td>".$req['comment']."</td>";
			$tool_content .= "<td align=center>
			<a href='$_SERVER[PHP_SELF]?id=$req[rid]&show=closed$reqtype'>$langRestore</a>
			</td></tr>";
			$k++;
		}
	}
	$tool_content .= "</tbody></table>";

// ---------------------------------
// Display accepted requests
// ---------------------------------
} elseif (!empty($show) && ($show=="accepted")) {
	$tool_content .= "<table class='FormData' width='99%' align='left'>";
	$tool_content .= table_header(1, $langDateCompleted_small);
	$tool_content .= "<tbody>";

 	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,
			profcomm,date_open,date_closed,comment
			FROM prof_request
                        WHERE (status = 0 AND statut = $list_statut)");

    	$k = 0;
	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$req = mysql_fetch_array($sql);
			if ($k%2==0) {
	              $tool_content .= "\n  <tr>";
	            } else {
	              $tool_content .= "\n  <tr class=\"odd\">";
	            }
	    $tool_content .= "\n    <td width=\"1\"><img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
		$tool_content .= "\n    <td>".htmlspecialchars($req[1])."&nbsp;".htmlspecialchars($req[2])."</td>";
		for ($i = 3; $i < mysql_num_fields($sql) - 3; $i++) {
			if ($i == 4 and $req[$i] != "") {
				$tool_content .= "\n    <td><a href=\"mailto:".
				htmlspecialchars($req[$i])."\">".
				htmlspecialchars($req[$i])."</a></td>";
			} else {
				$tool_content .= "\n    <td>".
				htmlspecialchars($req[$i])."</td>";
			}
		}
		$tool_content .= "\n    <td align=\"center\"><small>".nice_format(date("Y-m-d", strtotime($req[7])))."</small></td>";
        $tool_content .= "\n    <td align=\"center\"><small>".nice_format(date("Y-m-d", strtotime($req[8])))."</small></td>";
        $tool_content .= "\n    <td>".$req[9]."</td>";
		$tool_content .= "</tr>";
		$k++;
	}
	$tool_content .= "\n  </tbody>\n  </table>\n";
// ------------------------------
// close request
// ------------------------------
} elseif(!empty($close)) {
	switch($close) {
	case '1':
		$sql = db_query("UPDATE prof_request set status='2', date_closed=NOW() WHERE rid='$id'");
                if ($list_statut == 1) {
        		$tool_content .= "<p><center>$langProfessorRequestClosed</p>";
                } else {
        		$tool_content .= "<p><center>$langRequestStudent</p>";
                }
		break;
	case '2':
		$submit = isset($_POST['submit'])?$_POST['submit']:'';
		if(!empty($submit)) {
			// post the comment and do the delete action
			if (!empty($comment)) {
				$sql = "UPDATE prof_request set status = '3',
						date_closed = NOW(),
						comment = '".mysql_escape_string($comment)."'
						WHERE rid = '$id'";
				if (db_query($sql)) {
					if (isset($sendmail) and ($sendmail == 1)) {
						$emailsubject = $langemailsubjectBlocked;
						$emailbody = "$langemailbodyBlocked
$langComments:> $comment
$langManager $siteName
$administratorName $administratorSurname
$langPhone: $telephone
$langEmail: $emailhelpdesk";
						send_mail('', '', "$prof_name $prof_surname", $prof_email, $emailsubject, $emailbody, $charset);
					}
					$tool_content .= "<p class='success_small'>" .  ($list_statut == 1)? $langTeacherRequestHasRejected: $langRequestReject;
					$tool_content .= " $langRequestMessageHasSent <b>$prof_email</b></p>";
					$tool_content .= "<br><p><b>$langComments:</b><br />$comment</p>\n";
				}
			}
		} else {
			// display the form
			$r = db_query("SELECT comment, profname, profsurname, profemail, statut
				FROM prof_request WHERE rid = '$id'");
			$d = mysql_fetch_assoc($r);
                        $warning = ($d['statut'] == 5)? $langWarnReject: $langGoingRejectRequest;
			$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
			<table width='99%' class='FormData'>
			<tbody><tr>
			<th width='220'>&nbsp;</th>
			<td><b>$warning</b></td></tr>
			<tr><th class='left'>$langName</th>
			<td>".$d['profname']."</td></tr>
			<tr><th class='left'>$langSurname</th>
			<td>".$d['profsurname']."</td></tr>
			<tr><th class='left'>$langEmail</th>
			<td>".$d['profemail']."</td></tr>
			<tr><th class='left'>$langComments</th>
			<td>
			<input type='hidden' name='id' value='".$id."'>
			<input type='hidden' name='close' value='2'>
			<input type='hidden' name='prof_name' value='".$d['profname']."'>
			<input type='hidden' name='prof_surname' value='".$d['profsurname']."'>
			<textarea class='auth_input' name='comment' rows='5' cols='60'>".$d['comment']."</textarea>
			</td></tr>
			<tr><th class='left'>$langRequestSendMessage</th>
			<td>&nbsp;<input type='text' class='auth_input' name='prof_email' value='".$d['profemail']."'>
			<input type='checkbox' name='sendmail' value='1' checked='yes'> <small>($langGroupValidate)</small>
			</td></tr>
			<tr><th class='left'>&nbsp;</th>
			<td><input type='submit' name='submit' value='$langRejectRequest'>&nbsp;&nbsp;<small>($langRequestDisplayMessage)</small></td>
			</tr></tbody></table>
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
	$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\">";
	$tool_content .= table_header();
	$tool_content .= "<tbody>";
 	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima, 
			profcomm, date_open, comment, profpassword, lang
			FROM prof_request
                        WHERE (status = 1 AND statut = $list_statut)");
    	$k = 0;
	while ($req = mysql_fetch_array($sql)) {
		if ($k%2 == 0) {
	              $tool_content .= "\n<tr>";
	            } else {
	              $tool_content .= "\n<tr class='odd'>";
	            }
	    	$tool_content .= "<td align='right' width='1'>
		<img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
	     	$tool_content .= "<td>".htmlspecialchars($req['profname'])."&nbsp;".htmlspecialchars($req['profsurname'])."</td>";
		$tool_content .= "<td>".htmlspecialchars($req['profuname'])."</td>";
		if ($req['profemail'] != "") {
				$tool_content .= "\n<td><a href=\"mailto:".
				htmlspecialchars($req['profemail'])."\">".
				htmlspecialchars($req['profemail'])."</a></td>";
		} else {
			$tool_content .= "\n<td>".htmlspecialchars($req['profemail'])."</td>";
		}
		$tool_content .= "<td>".htmlspecialchars(find_faculty_by_id($req['proftmima']))."</td>";
		$tool_content .= "<td>".htmlspecialchars($req['profcomm'])."</td>";
		$tool_content .= "<td align='center'>
			<small>".nice_format(date("Y-m-d", strtotime($req['date_open'])))."</small></td>";
		$tool_content .= "<td align='center'>$req[comment]</td>";
		$tool_content .= "<td align='center'>
		<a href='$_SERVER[PHP_SELF]?id=$req[rid]&amp;close=1$reqtype' onclick='return confirmation();'>$langClose</a><br />
		<a href='$_SERVER[PHP_SELF]?id=$req[rid]&amp;close=2$reqtype'>$langRejectRequest</a>";
		switch($req['profpassword']) {
			case 'ldap': $tool_content .= "<br />
					<a href='../auth/ldapnewprofadmin.php?id=".urlencode($req['rid'])."&amp;auth=4'>
					$langRegistration<br />($langViaLdap)</td>\n  </tr>";
				break;
			case 'pop3': $tool_content .= "<br>
					<a href='../auth/ldapnewprofadmin.php?id=".urlencode($req['rid'])."&amp;auth=2'>
					$langRegistration<br>($langViaPop)</td>\n  </tr>";
				break;
			case 'imap': $tool_content .= "<br>
					<a href='../auth/ldapnewprofadmin.php?id=".urlencode($req['rid'])."&amp;auth=3'>
					$langRegistration<br>($langViaImap)</td>\n  </tr>";
				break;
			default:  $tool_content .= "<br>
					<a href='newuseradmin.php?id=".urlencode($req['rid'])."'>
					$langRegistration
					</a></td>\n  </tr>";
				break;
		}
		$k++;
	}
	$tool_content .= "\n  </tbody>\n  </table>\n";

}

// If show is set then we return to listreq, else return to admin index.php
//if (isset($close) or isset($closed)) {
if (!empty($show)) {
	$tool_content .= "<p align=\"right\"><a href=\"$_SERVER[PHP_SELF]\">$langBackRequests</a></p><br>";
}
	$tool_content .= "<p align=\"right\"><a href=\"index.php\">$langBack</a></p>";
draw($tool_content, 3, null, $head_content);

// --------------------------------------
// function to display table header
// --------------------------------------

function table_header($addon = FALSE, $message = FALSE) {
	
	global $langName, $langSurname, $langUsername, $langEmail, $langFaculty, $langTel;
	global $langDate, $langComments, $langActions;
	global $langDateRequest_small;

	$string = "";
	if ($addon) { 
		$rowspan=2;
		$datestring = "<th align='center' colspan='2'>$langDate</th>
		<th scope='col' rowspan='$rowspan' align='center'>$langComments</th>
		<th scope='col' rowspan='$rowspan' align='center'>$langActions</th>
		</tr><tr>
		<th>$langDateRequest_small</th>
		<th>$message</th>";
	} else {
		$rowspan=1;
		$datestring = "<th scope='col' align='center'>$langDate<br />$langDateRequest_small</th>
		<th scope='col' align='center'>$langComments</th>
		<th scope='col' align='center'>$langActions</th>";
	}

	$string .= "<thead><tr>
	<th scope='col' colspan='2' rowspan='$rowspan' class='left'>&nbsp;&nbsp;$langName $langSurname</th>
	<th scope='col' width='20' rowspan='$rowspan' class='left'>$langUsername</th>
	<th scope='col' rowspan='$rowspan' class='left'>$langEmail</th>
	<th scope='col' rowspan='$rowspan' class='left'>$langFaculty</th>
	<th scope='col' rowspan='$rowspan' align='center'>$langTel</th>";
	$string .= $datestring; 
	$string .= "</tr></thead>";

return $string;
}

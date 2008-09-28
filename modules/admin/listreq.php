<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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
$nameTools= $langOpenProfessorRequests;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

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

// Initialise $tool_content
$tool_content = "";

// Main body
$close = isset($_GET['close'])?$_GET['close']:(isset($_POST['close'])?$_POST['close']:'');
$id = isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:'');
$show = isset($_GET['show'])?$_GET['show']:(isset($_POST['show'])?$_POST['show']:'');

// Deal with navigation
switch ($show) {
	case "closed":
		$navigation[] = array("url" => "listreq.php", "name" => $langOpenProfessorRequests);
		$nameTools = $langReqHaveClosed;
		break;
	case "rejected":
		$navigation[] = array("url" => "listreq.php", "name" => $langOpenProfessorRequests);
		$nameTools = $langReqHaveBlocked;
		break;
	case "accepted":
		$navigation[] = array("url" => "listreq.php", "name" => $langOpenProfessorRequests);
		$nameTools = $langReqHaveFinished;
		break;
}

if (!empty($show) && ($show=="closed")) {
	if (!empty($id) && ($id>0)) {
		// Epanafora aitisis
		$sql = db_query("UPDATE prof_request set status='1', date_closed=NULL WHERE rid='$id'");
		$tool_content = "<p class=\"success_small\">$langReintroductionApplication</p>";

	} else {
		// Show only closed forms
		$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <thead>
  <tr>
    <th class=\"left\" colspan=\"2\" rowspan=\"2\">$langName $langSurname</th>
    <th class=\"left\" rowspan=\"2\">$langUsername</th>
    <th class=\"left\" rowspan=\"2\">$langEmail</th>
    <th class=\"left\" rowspan=\"2\">$langDepartment</th>
    <th align=\"center\" rowspan=\"2\">$langTel</th>
    <th align=\"center\" colspan=\"2\">$langDate</th>
    <th align=\"center\" rowspan=\"2\">$langComments</th>
    <th align=\"center\" rowspan=\"2\">$langActions</th>
  </tr>
  <tr>
    <th align=\"center\">$langDateRequest_small</th>
    <th align=\"center\">$langDateClosed_small</th>
  </tr>
  </thead>
  <tbody>";

 		$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,date_closed,comment
			FROM prof_request WHERE (status='2' AND statut<>'5')");

        $k = 0;
		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
			$req = mysql_fetch_array($sql);
				if ($k%2==0) {
	              $tool_content .= "\n  <tr>";
	            } else {
	              $tool_content .= "\n  <tr class=\"odd\">";
	            }
	        $tool_content .= "\n    <td width=\"1\"><img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
			$tool_content .= "\n    <td>".htmlspecialchars($req[1])."&nbsp;".htmlspecialchars($req[2])."";
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
			$tool_content .= "\n    <td align=center><a href=\"listreq.php?id=$req[rid]&"."show=closed\">$langRestore</a></td>\n  </tr>";
			$k++;
		}
	}
	$tool_content .= "\n  </tbody>\n  </table>\n";
} elseif (!empty($show) && ($show=="rejected")) {
	// Show only rejected forms
	if (!empty($id) && ($id>0)) {
		// Epanafora aitisis
		$sql = db_query("UPDATE prof_request set status='1', date_closed=NULL WHERE rid='$id'");
		$tool_content = "<table><tbody><tr><td class=\"success\">$langReintroductionApplication</td></tr></tbody></table>";

	} else {
		// Show only closed forms
		$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <thead>
  <tr>
    <th colspan=\"2\" rowspan=\"2\" class=\"left\">$langName $langSurname</th>
    <th class=\"left\" rowspan=\"2\">$langUsername</th>
    <th class=\"left\" rowspan=\"2\">$langEmail</th>
    <th class=\"left\" rowspan=\"2\">$langDepartment</th>
    <th align=\"center\" rowspan=\"2\">$langTel</th>
    <th align=\"center\" colspan=\"2\">$langDate</th>
    <th align=\"center\" rowspan=\"2\">$langComments</th>
    <th align=\"center\" rowspan=\"2\">$langActions</th>
  </tr>
  <tr>
    <th align=\"center\">$langDateRequest_small</th>
    <th align=\"center\">$langDateReject_small</th>
  </tr>
  </thead>
  <tbody>";

 		$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,date_closed,comment
		FROM prof_request WHERE (status='3' AND statut<>'5')");

        $k = 0;
		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
				if ($k%2==0) {
	              $tool_content .= "\n  <tr>";
	            } else {
	              $tool_content .= "\n  <tr class=\"odd\">";
	            }
	    $tool_content .= "\n    <td width=\"1\"><img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
			$tool_content .= "\n    <td>".htmlspecialchars($req[1])."&nbsp;".htmlspecialchars($req[2])."";
			$req = mysql_fetch_array($sql);
			for ($i = 3; $i < mysql_num_fields($sql)-3; $i++) {
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
			$tool_content .= "\n    <td align=center>
			<a href=\"listreq.php?id=$req[rid]&"."show=closed\">$langRestore</a>
			</td></tr>";
			$k++;
		}
	}
	$tool_content .= "\n  </tbody>\n  </table>\n";
} elseif (!empty($show) && ($show=="accepted")) {
	// Show only accepted forms
	$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <thead>
  <tr>
    <th rowspan=\"2\"colspan=\"2\" class=\"left\">$langName $langSurname</th>
    <th rowspan=\"2\"class=\"left\">$langUsername</th>
    <th rowspan=\"2\"class=\"left\">$langEmail</th>
    <th rowspan=\"2\"class=\"left\">$langDepartment</th>
    <th rowspan=\"2\"align=\"center\">$langTel</th>
    <th colspan=\"2\" align=\"center\">$langDate</th>
    <th rowspan=\"2\"scope=\"col\">$langComments</th>
  </tr>
  <tr>
    <th scope=\"col\" align=\"center\">$langDateRequest_small</th>
    <th scope=\"col\" align=\"center\">$langDateCompleted_small</th>
  </tr>
  </thead>
  <tbody>";

 	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,date_closed,comment
		FROM prof_request WHERE (status='0' AND statut<>'5')");

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
} elseif(!empty($close)) {
switch($close)
{
    case '1':
	    $sql = db_query("UPDATE prof_request set status='2', date_closed=NOW() WHERE rid='$id'");
	    $tool_content .= "<p><center>$langProfessorRequestClosed</p>";
	    break;
    case '2':
	    $submit = isset($_POST['submit'])?$_POST['submit']:'';
	    if(!empty($submit))
	    {
		// post the comment and do the delete action
		if (!empty($comment)) {
		    	$sql = "UPDATE prof_request set status = '3',
					    date_closed = NOW(),
					    comment = '".mysql_escape_string($comment)."'
					    WHERE rid = '$id'";
		    	if (db_query($sql))
		    	{
				if (isset($sendmail) and ($sendmail == 1)) {
    			    		$emailsubject = $langemailsubjectBlocked;
			    		$emailbody = "$langemailbodyBlocked
			    		$langComments:> $comment
			    		$langManager $siteName
			    		$administratorName $administratorSurname
			    		$langphone : $telephone
			    		$langEmail : $emailAdministrator";
			    		send_mail($siteName, $emailAdministrator, "$prof_name $prof_surname",	$prof_email, $emailsubject, $emailbody, $charset);
				}
				$tool_content .= "<p class=\"success_small\">$langTeacherRequestHasRejected";
				$tool_content .= " $langRequestMessageHasSent <b>$prof_email</b></p>";
				$tool_content .= "<br><p><b>$langComments:</b><br />$comment</p>\n";
		    	}
		}
	    } else {
			// display the form
			$r = db_query("SELECT comment, profname, profsurname, profemail
				     FROM prof_request WHERE rid = '$id'");
			$d = mysql_fetch_assoc($r);
			$tool_content .= "
<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">
  <table width=\"99%\" class=\"FormData\">
  <tbody>
  <tr>
    <th width=\"220\">&nbsp;</th>
    <td><b>$langGoingRejectRequest</b></td>
  </tr>
  <tr>
    <th class=\"left\">$langName </th>
    <td>".$d['profname']."</td>
  </tr>
  <tr>
    <th class=\"left\">$langSurname</th>
    <td>".$d['profsurname']."</td>
  </tr>
  <tr>
    <th class=\"left\">$langEmail</th>
    <td>".$d['profemail']."</td>
  </tr>
  <tr>
    <th class=\"left\">$langComments</th>
    <td>
	  <input type=\"hidden\" name=\"id\" value=\"".$id."\">
	  <input type=\"hidden\" name=\"close\" value=\"2\">
	  <input type=\"hidden\" name=\"prof_name\" value=\"".$d['profname']."\">
	  <input type=\"hidden\" name=\"prof_surname\" value=\"".$d['profsurname']."\">
	  <textarea class=\"auth_input\" name=\"comment\" rows=\"5\" cols=\"60\">".$d['comment']."</textarea>
    </td>
  </tr>
  <tr>
    <th class=\"left\">$langRequestSendMessage</th>
    <td>&nbsp;<input type=\"text\" class=\"auth_input\" name=\"prof_email\" value=\"".$d['profemail']."\"><br />
        <input type=\"checkbox\" name=\"sendmail\" value=\"1\" checked=\"yes\"> <small>($langGroupValidate)</small>

    </td>
  </tr>
  <tr>
    <th class=\"left\">&nbsp;</th>
    <td><input type=\"submit\" name=\"submit\" value=\"$langRejectRequest\">&nbsp;&nbsp;<small>($langRequestDisplayMessage)</small></td>
  </tr>
  </tbody>
  </table>
</form>";
	    }
	    break;
    default:
	    break;
    }
}
else
{

  // Display other actions
  $tool_content .= "
      <div id=\"operations_container\">
        <ul id=\"opslist\">
          <li><a href=\"listreq.php?show=closed\">$langReqHaveClosed</a></li>
          <li><a href=\"listreq.php?show=rejected\">$langReqHaveBlocked</a></li>
          <li><a href=\"listreq.php?show=accepted\">$langReqHaveFinished</a></li>
        </ul>
      </div>";

// -----------------------------------
// display all the requests
// -----------------------------------
	$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <thead>
  <tr>
    <th scope=\"col\" colspan=\"2\" class=\"left\">&nbsp;&nbsp;$langName $langSurname</th>
    <th scope=\"col\" width='20' class=\"left\">$langUsername</th>
    <th scope=\"col\" class=\"left\">$langEmail</th>
    <th scope=\"col\" class=\"left\">$langDepartment</th>
    <th scope=\"col\" align=\"center\">$langTel</th>
    <th scope=\"col\" align=\"center\">$langDate<br />$langDateRequest_small</th>
    <th scope=\"col\" align=\"center\">$langComments</th>
    <th scope=\"col\" align=\"center\">$langActions</th>
  </tr>
  </thead>
  <tbody>";

 	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,comment,profpassword, lang
		FROM prof_request WHERE (status='1' AND statut<>'5')");

    $k = 0;
	for ($j = 0; $j < mysql_num_rows($sql) ; $j++) {
		$req = mysql_fetch_array($sql);
				if ($k%2==0) {
	              $tool_content .= "\n  <tr>";
	            } else {
	              $tool_content .= "\n  <tr class=\"odd\">";
	            }
	    $tool_content .= "\n    <td align=\"right\" width=\"1\"><img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
		$tool_content .= "\n    <td>".htmlspecialchars($req[1])."&nbsp;".htmlspecialchars($req[2])."</td>";
		for ($i = 3; $i < mysql_num_fields($sql)-4; $i++) {
			if ($i == 4 and $req[$i] != "") {
				$tool_content .= "\n    <td><a href=\"mailto:".
				htmlspecialchars($req[$i])."\">".
				htmlspecialchars($req[$i])."</a></td>";
			} else {
				$tool_content .= "\n    <td>".htmlspecialchars($req[$i])."</td>";
			}
		}
		$tool_content .= "\n    <td align=\"center\"><small>".nice_format(date("Y-m-d", strtotime($req[7])))."</small></td>";
		$tool_content .= "\n    <td align=\"center\">$req[8]</td>";
		$tool_content .= "\n    <td align=center><a href='listreq.php?id=$req[rid]&close=1' onclick='return confirmation();'>$langClose</a><br /><a href='listreq.php?id=$req[rid]&close=2'>$langRejectRequest</a>";
		switch($req['profpassword']) {
			case 'ldap': $tool_content .= "<br /><a href='../auth/ldapnewprofadmin.php?id=".urlencode($req['rid']).
				"&pn=".urlencode($req['profname']).
		        	   "&ps=".urlencode($req['profsurname']).
				"&pu=".urlencode($req['profuname']).
				"&pe=".urlencode($req['profemail']).
				"&pt=".urlencode($req['proftmima']).
				"&lang=".$req['lang'].
				"&auth=4'>$langRegistration<br />($langViaLdap)</td>\n  </tr>";
        break;
      case 'pop3': $tool_content .= "<br><a href='../auth/ldapnewprofadmin.php?id=".urlencode($req['rid']).
                                      "&pn=".urlencode($req['profname']).
                                      "&ps=".urlencode($req['profsurname']).
                                      "&pu=".urlencode($req['profuname']).
                                      "&pe=".urlencode($req['profemail']).
                                      "&pt=".urlencode($req['proftmima']).
				      "&lang=".$req['lang'].
				"&auth=2'>$langRegistration<br>($langViaPop)</td>\n  </tr>";
        break;
      case 'imap': $tool_content .= "<br><a href='../auth/ldapnewprofadmin.php?id=".urlencode($req['rid']).
                                      "&pn=".urlencode($req['profname']).
                                      "&ps=".urlencode($req['profsurname']).
                                      "&pu=".urlencode($req['profuname']).
                                      "&pe=".urlencode($req['profemail']).
                                      "&pt=".urlencode($req['proftmima']).
				      "&lang=".$req['lang'].
				"&auth=3'>$langRegistration<br>($langViaImap)</td>\n  </tr>";
        break;
      default:  $tool_content .= "<br><a href='../auth/newprofadmin.php?id=".urlencode($req['rid']).
                      "&pn=".urlencode($req['profname']).
                      "&ps=".urlencode($req['profsurname']).
                      "&pu=".urlencode($req['profuname']).
                      "&pe=".urlencode($req['profemail']).
                      "&pt=".urlencode($req['proftmima']).
                      "&lang=".$req['lang']."'>$langRegistration</a></td>\n  </tr>";
        break;
		}
		$k++;
	}
	$tool_content .= "\n  </tbody>\n  </table>\n";

}

// If show is set then we return to listereq, else return to admin index.php
if (!empty($show)) {
	$tool_content .= "<p>&nbsp;</p><p align=\"right\"><a href=\"listreq.php\">$langBack</a></p>";
} else {
	$tool_content .= "<p>&nbsp;</p><p align=\"right\"><a href=\"index.php\">$langBack</a></p>";
}
draw($tool_content, 3, ' ', $head_content);
?>

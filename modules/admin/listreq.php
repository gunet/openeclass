<?php
$require_admin = TRUE;
include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');
include '../auth/auth.inc.php';
$nameTools= $langOpenProfessorRequests;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
global $langRejectRequest,$langRegistration,$langReintroductionApplication;
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
		$tool_content = "<table><tbody><tr><td class=\"success\">$langReintroductionApplication  </td></tr></tbody></table>";
		
	} else {
		// Show only closed forms
		$tool_content .= "<table width=\"99%\"><caption>$langOpenProfessorRequests</caption><thead><tr>
		<th scope=\"col\">$langName</th>
		<th scope=\"col\">$langSurname</th>
		<th scope=\"col\">$langUsername</th>
		<th scope=\"col\">$langEmail</th>
		<th scope=\"col\">$langDepartment</th>
		<th scope=\"col\">$langphone</th>
		<th scope=\"col\">$langDateRequest</th>
		<th scope=\"col\">$langDateClosed</th>
		<th scope=\"col\">$langComments</th>
		<th scope=\"col\">$langActions</th>
		</tr></thead><tbody>";

 		$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,date_closed,comment 
											FROM prof_request WHERE (status='2' AND statut<>'5')");

		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
			$req = mysql_fetch_array($sql);
			$tool_content .= "<tr>";
			for ($i = 1; $i < mysql_num_fields($sql); $i++) {
				if ($i == 4 and $req[$i] != "") {
					$tool_content .= "<td><a href=\"mailto:".
					htmlspecialchars($req[$i])."\">".
					htmlspecialchars($req[$i])."</a></td>";
				} else {
					$tool_content .= "<td>".
					htmlspecialchars($req[$i])."</td>";
				}
			}
			$tool_content .= "<td align=center>
			<a href=\"listreq.php?id=$req[rid]&"."show=closed\">$langRestore</a>
			</td></tr>";
		}
	}
	$tool_content .= "</tbody></table>";
} elseif (!empty($show) && ($show=="rejected")) {
	// Show only rejected forms
	if (!empty($id) && ($id>0)) {
		// Epanafora aitisis
		$sql = db_query("UPDATE prof_request set status='1', date_closed=NULL WHERE rid='$id'");
		$tool_content = "<table><tbody><tr><td class=\"success\">$langReintroductionApplication</td></tr></tbody></table>";
		
	} else {
		// Show only closed forms
		$tool_content .= "<table width=\"99%\">
		<caption>$langOpenProfessorRequests</caption>
		<thead><tr>
		<th scope=\"col\">$langName</th>
		<th scope=\"col\">$langSurname</th>
		<th scope=\"col\">$langUsername</th>
		<th scope=\"col\">$langEmail</th>
		<th scope=\"col\">$langDepartment</th>
		<th scope=\"col\">$langphone</th>
		<th scope=\"col\">$langDateRequest</th>
		<th scope=\"col\">$langDateReject</th>
		<th scope=\"col\">$langComments</th>
		<th scope=\"col\">$langActions</th>
		</tr></thead><tbody>";

 		$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,date_closed,comment 
		FROM prof_request WHERE (status='3' AND statut<>'5')");

		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
			$req = mysql_fetch_array($sql);
			$tool_content .= "<tr>";
			for ($i = 1; $i < mysql_num_fields($sql); $i++) {
				if ($i == 4 and $req[$i] != "") {
					$tool_content .= "<td><a href=\"mailto:".
					htmlspecialchars($req[$i])."\">".
					htmlspecialchars($req[$i])."</a></td>";
				} else {
					$tool_content .= "<td>".
					htmlspecialchars($req[$i])."</td>";
				}
			}
			$tool_content .= "<td align=center>
			<a href=\"listreq.php?id=$req[rid]&"."show=closed\">$langRestore</a>
			</td></tr>";
		}
	}
	$tool_content .= "</tbody></table>";
} elseif (!empty($show) && ($show=="accepted")) {
	// Show only accepted forms
	$tool_content .= "<table width=\"99%\"><caption>$langOpenProfessorRequests</caption><thead><tr>
		<th scope=\"col\">$langName</th>
		<th scope=\"col\">$langSurname</th>
		<th scope=\"col\">$langUsername</th>
		<th scope=\"col\">$langEmail</th>
		<th scope=\"col\">$langDepartment</th>
		<th scope=\"col\">$langphone</th>
		<th scope=\"col\">$langDateRequest</th>
		<th scope=\"col\">$langDateCompleted</th>
		<th scope=\"col\">$langComments</th>
		</tr></thead><tbody>";

 	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,date_closed,comment 
		FROM prof_request WHERE (status='0' AND statut<>'5')");

	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$req = mysql_fetch_array($sql);
		$tool_content .= "<tr>";
		for ($i = 1; $i < mysql_num_fields($sql); $i++) {
			if ($i == 4 and $req[$i] != "") {
				$tool_content .= "<td><a href=\"mailto:".
				htmlspecialchars($req[$i])."\">".
				htmlspecialchars($req[$i])."</a></td>";
			} else {
				$tool_content .= "<td>".
				htmlspecialchars($req[$i])."</td>";
			}
		}
		$tool_content .= "</tr>";
	}
	$tool_content .= "</tbody></table>";
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
				if (!empty($comment)) 
				{
		    	$sql = "UPDATE prof_request set status = '3',
					    date_closed = NOW(),
					    comment = '".mysql_escape_string($comment)."'
					    WHERE rid = '$id'";
		    	if (db_query($sql)) 
		    	{
						if (isset($sendmail) and ($sendmail == 1)) 
						{
    			    $emailsubject = $langemailsubjectBlocked;
			    		$emailbody = "$langemailbodyBlocked
			    		$langComments:> $comment
			    		$langManager $siteName
			    		$administratorName $administratorSurname
			    		$langphone : $telephone
			    		$langEmail : $emailAdministrator";
			    		send_mail($siteName, $emailAdministrator, "$prof_name $prof_surname",	$prof_email, $emailsubject, $emailbody, $charset);
						}
						$tool_content .= "<p>$langTeacherRequestHasRejected";
						$tool_content .= " $langRequestMessageHasSent $prof_email";
						$tool_content .= ". <br><br>$langComments:<br><pre>$comment</pre></p>\n";
		    	}
				}
	    }
	    else
	    {
				// display the form
				$r = db_query("SELECT comment, profname, profsurname, profemail
					     FROM prof_request WHERE rid = '$id'");
				$d = mysql_fetch_assoc($r);
				$tool_content .= "
					<br><br>
					<center><p>$langGoingRejectRequest:<br><br>".$d['profname']." ".$d['profsurname']." &lt;".$d['profemail']."&gt;
					<br><br>$langComments:	<form action=\"listreq.php\" method=\"post\">
					<input type=\"hidden\" name=\"id\" value=\"".$id."\">
					<input type=\"hidden\" name=\"close\" value=\"2\">
					<input type=\"hidden\" name=\"prof_name\" value=\"".$d['profname']."\">
					<input type=\"hidden\" name=\"prof_surname\" value=\"".$d['profsurname']."\">
					<textarea name=\"comment\" rows=\"5\" cols=\"40\">".$d['comment']."</textarea>
					<br><input type=\"checkbox\" name=\"sendmail\" value=\"1\"
					checked=\"yes\">&nbsp;$langRequestSendMessage:
					<input type=\"text\" name=\"prof_email\" value=\"".$d['profemail']."\">
					<br><br>($langRequestDisplayMessage)
					<br><br><input type=\"submit\" name=\"submit\" value=\"$langRejectRequest\"></form></p></center>";
	    }	
	    break;
    default:
	    break;
    }
}
else
{

	$tool_content .= "<table width=\"99%\"><caption>$langOpenProfessorRequests</caption><thead><tr>
		<th scope=\"col\">$langName</th>
		<th scope=\"col\">$langSurname</th>
		<th scope=\"col\">$langUsername</th>
		<th scope=\"col\">$langEmail</th>
		<th scope=\"col\">$langDepartment</th>
		<th scope=\"col\">$langphone</th>
		<th scope=\"col\">$langDateRequest</th>
		<th scope=\"col\">$langComments</th>
		<th scope=\"col\">$langActions</th>
		</tr></thead><tbody>";

 	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,comment 
									FROM prof_request WHERE (status='1' AND statut<>'5')");

	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$req = mysql_fetch_array($sql);
		$tool_content .= "<tr>";
		for ($i = 1; $i < mysql_num_fields($sql); $i++) {
			if ($i == 4 and $req[$i] != "") {
				$tool_content .= "<td><a href=\"mailto:".
				htmlspecialchars($req[$i])."\">".
				htmlspecialchars($req[$i])."</a></td>";
			} else {
				$tool_content .= "<td>".
				htmlspecialchars($req[$i])."</td>";
			}
		}
		$tool_content .= "<td align=center><font size='2'><a href='listreq.php?id=$req[rid]&close=1'>$langClose</a>
			<br><a href='listreq.php?id=$req[rid]&close=2'>$langRejectRequest</a>
			<br><a href='../auth/newprofadmin.php?id=".urlencode($req['rid']).
											"&pn=".urlencode($req['profname']).
											"&ps=".urlencode($req['profsurname']).
											"&pu=".urlencode($req['profuname']).
											"&pe=".urlencode($req['profemail']).
											"&pt=".urlencode($req['proftmima']).
											"'>$langRegistration</a></td></tr>";
	}
	$tool_content .= "</tbody></table>";
	// Display other actions
	$tool_content .= "<br><table width=\"99%\"><caption>$langOtherActions</caption><tbody>
		<tr><td><a href=\"listreq.php?show=closed\">$langReqHaveClosed</a><br>
		<a href=\"listreq.php?show=rejected\">$langReqHaveBlocked</a><br>
		<a href=\"listreq.php?show=accepted\">$langReqHaveFinished</a></td></tr>
	</tbody></table>";
}

// If show is set then we return to listereq, else return to admin index.php
if (!empty($show)) {
	$tool_content .= "<br><center><p><a href=\"listreq.php\">$langBack</a></p></center>";
} else {
	$tool_content .= "<br><center><p><a href=\"index.php\">$langBack</a></p></center>";
}
draw($tool_content,3);
?>

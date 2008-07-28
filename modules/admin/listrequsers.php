
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
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$nameTools= $langUserOpenRequests;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
$sendmail = 0;

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

if (isset($close) && $close == 1) {
	$sql = db_query("UPDATE prof_request set status='2', date_closed=NOW() WHERE rid='$id'");
	$tool_content .= "<br><br><center>$langRequestStudent</center>";
} elseif (isset($close) && $close == 2) {
	if (!empty($comment)) {
		if (db_query("UPDATE prof_request set status = '2',
					    date_closed = NOW(),
					    comment = '".mysql_escape_string($comment)."'
					    WHERE rid = '$id'")) {
			if ($sendmail == 1) {
        $emailsubject = $langemailsubjectBlocked;
				$emailbody = "$langemailbodyBlocked

$langComments:

> $comment

$langManager $siteName
$administratorName $administratorSurname
$langphone $telephone
$langEmail : $emailAdministrator

";
				send_mail($siteName, $emailAdministrator, "$prof_name $prof_surname",
					$prof_email, $emailsubject, $emailbody, $charset);
			}
                  $tool_content .= "<div class=alert1>$langRequestReject</div><br>";
                  if ($sendmail == 1) $tool_content .= "<div class=kk align=center>$langInformativeEmail <b>$prof_email</b></div>.";
                  $tool_content .= "<br><center><h4>$langComments:</h4><pre>$comment</pre></center>\n";
		}
	} else {
// -----------------
 // reject request
// ----------------
		$r = db_query("SELECT comment, profname, profsurname, profemail, proftmima, date_open, profcomm
					     FROM prof_request WHERE rid = '$id'");
		$d = mysql_fetch_assoc($r);

$tool_content .= "<br><br>
          <center><p>$langWarnReject:<br><br>".$d['profname']." ".$d['profsurname']." &lt;".$d['profemail']."&gt;
          <br><br>$langComments:  <form action=\"$_SERVER[PHP_SELF]\" method=\"post\"
							 <input type='hidden' name='id' value='$id'>
               <input type='hidden' name='close' value='2'>
               <input type='hidden' name='prof_name' value='$d[profname]'>
               <input type='hidden' name='prof_surname' value='$d[profsurname]'>
					<textarea name=\"comment\" rows=\"5\" cols=\"40\">".$d['comment']."</textarea>
          <br>
					<input type=\"checkbox\" name=\"sendmail\" value=\"1\" checked=\"yes\">&nbsp;$langRequestSendMessage
          <input type=\"text\" name=\"prof_email\" value=\"".$d['profemail']."\">
          <br><br>($langRequestDisplayMessage)
          <br><br><input type=\"submit\" name=\"submit\" value=\"$langRejectRequest\"></form></p></center>";
	}

} else {

		$tool_content .= "<table width=\"99%\"><thead><tr>
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
								FROM prof_request WHERE status='1' and statut='5'");

	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$req = mysql_fetch_array($sql);
$tool_content .= "<tr onMouseOver=\"this.style.backgroundColor='#F1F1F1'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
     $tool_content .= "<td class=kk title=".htmlspecialchars($req[3])."><small>".htmlspecialchars($req[1])."<br>";
     $tool_content .= htmlspecialchars($req[2])."</small></td>";
		for ($i = 2; $i < mysql_num_fields($sql); $i++) {
			if ($i == 4 and $req[$i] != "") {
				$tool_content .= "<td class=kk><small><a href=\"mailto:".htmlspecialchars($req[$i])."\" class=small_tools>".htmlspecialchars($req[$i])."</a></small></td>";
			} else {
				$tool_content .= "<td class=kk><small>".htmlspecialchars($req[$i])."</small></td>";
			}
		}
			$tool_content .= "<td align=center class=kk><small><a href='$_SERVER[PHP_SELF]?id=$req[rid]&close=1' class=small_tools onclick='return confirmation();'>$langClose</a><br><a href='$_SERVER[PHP_SELF]?id=$req[rid]&close=2' class=small_tools>$langRejectRequest</a>";
			$tool_content .= "<br><a href=\"../auth/newuserreq.php?".
			"id=".urlencode($req['rid']).
			"&pn=".urlencode($req['profname']).
			"&ps=".urlencode($req['profsurname']).
			"&pu=".urlencode($req['profuname']).
			"&pe=".urlencode($req['profemail']).
			"&pt=".urlencode($req['proftmima']).
			"\" class=small_tools>$langRegistration</a>";	
			$tool_content .= "</small></td></tr>";
	}

		  // no requests
        if (mysql_num_rows($sql) == 0) {
             $tool_content .= "<tr><td colspan=9 class=kk align=center><br>$langUserNoRequests<br><br></td></tr>";
        }
        $tool_content .= "</thead></tbody></table>";
}
$tool_content .= "<br><center><p><a href=\"index.php\">$langBack</a></p></center>";
draw($tool_content, 3 ,' ', $head_content);
?>

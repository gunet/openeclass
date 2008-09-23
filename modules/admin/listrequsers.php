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
*					Network Operations Center, University of Athens,
*					Panepistimiopolis Ilissia, 15784, Athens, Greece
*					eMail: eclassadmin@gunet.gr
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

		$tool_content .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <thead>
  <tr>
    <th colspan=\"2\" class=\"left\">$langSurname<br />$langName</th>
    <th class=\"left\">$langUsername</th>
    <th class=\"left\">$langEmail</th>
    <th class=\"left\">$langDepartment</th>
    <th class=\"left\">$langphone</th>
    <th>$langDate<br />$langDateRequest_small</th>
    <th>$langComments</th>
    <th>$langActions</th>
  </tr>
  </thead>
  <tbody>";

	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,comment,lang
			FROM prof_request WHERE status='1' and statut='5'");

    $k = 0;
	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$req = mysql_fetch_array($sql);
				if ($k%2==0) {
	              $tool_content .= "\n  <tr>";
	            } else {
	              $tool_content .= "\n  <tr class=\"odd\">";
	            }
	    $tool_content .= "\n    <td width=\"1\"><img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
     $tool_content .= "\n    <td title=".htmlspecialchars($req[3])."><small>".htmlspecialchars($req[2])."&nbsp;".htmlspecialchars($req[1])."</small></td>";
		for ($i = 3; $i < mysql_num_fields($sql) - 3; $i++) {
			if ($i == 4 and $req[$i] != "") {
				$tool_content .= "\n    <td><small><a href=\"mailto:".htmlspecialchars($req[$i])."\" class=small_tools>".htmlspecialchars($req[$i])."</a></small></td>";
			} else {
				$tool_content .= "\n    <td><small>".htmlspecialchars($req[$i])."</small></td>";
			}
		}
            $tool_content .= "\n    <td align=\"center\"><small>".nice_format(date("Y-m-d", strtotime($req[7])))."</small></td>";
            $tool_content .= "\n    <td>".$req[8]."</td>";
			$tool_content .= "\n    <td align=center><small><a href='$_SERVER[PHP_SELF]?id=$req[rid]&close=1' class=small_tools onclick='return confirmation();'>$langClose</a><br><a href='$_SERVER[PHP_SELF]?id=$req[rid]&close=2' class=small_tools>$langRejectRequest</a>";
			$tool_content .= "<br><a href=\"../auth/newuserreq.php?".
			"id=".urlencode($req['rid']).
			"&pn=".urlencode($req['profname']).
			"&ps=".urlencode($req['profsurname']).
			"&pu=".urlencode($req['profuname']).
			"&pe=".urlencode($req['profemail']).
			"&pt=".urlencode($req['proftmima']).
			"&lang=".$req['lang'].
			"\" class=small_tools>$langRegistration</a>";
			$tool_content .= "</small></td>\n  </tr>";
			$k++;
	}

		  // no requests
        if (mysql_num_rows($sql) == 0) {
             $tool_content .= "\n  <tr><td colspan=9 class=kk align=center><br>$langUserNoRequests<br><br></td></tr>";
        }
        $tool_content .= "\n  </tbody>\n  </table>\n";
}
$tool_content .= "<p>&nbsp;</p><center><p align=\"right\"><a href=\"index.php\">$langBack</a></p></center>";
draw($tool_content, 3 ,' ', $head_content);

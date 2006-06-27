<?php
$langFiles = array('gunet','registration','admin');
include '../../include/baseTheme.php';
@include "check_admin.inc";
include('../../include/sendMail.inc.php');
$nameTools= $langOpenProfessorRequests;

// Initialise $tool_content
$tool_content = "";
// Main body

$close = isset($_GET['close'])?$_GET['close']:(isset($_POST['close'])?$_POST['close']:'');
$id = isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:'');
if(!empty($close))
{
switch($close)
{
    case '1':
	    //$tool_content .= "TOTAL DELETE OF REQUEST";
	    $sql = db_query("UPDATE prof_request set status='2', date_closed=NOW() WHERE rid='$id'");
	    $tool_content .= "<p><center>$langProfessorRequestClosed</p>";
	    break;
    case '2':
	    //$tool_content .= "DELETE OF REQUEST AND SENDING A MESSAGE";
	    $submit = isset($_POST['submit'])?$_POST['submit']:'';
	    if(!empty($submit))
	    {
				// post the comment and do the delete action	    
				if (!empty($comment)) 
				{
		    	$sql = "UPDATE prof_request set status = '2',
					    date_closed = NOW(),
					    comment = '".mysql_escape_string($comment)."'
					    WHERE rid = '$id'";
		    	if (db_query($sql)) 
		    	{
						if ($sendmail == 1) 
						{
    			    $emailsubject = "Απόρριψη αίτησης εγγραφής στην Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης";
			    		$emailbody = "Η αίτησή σας για εγγραφή στην πλατφόρμα e-Class απορρίφθηκε.
			    		Σχόλια:> $comment
			    		$langManager $siteName
			    		$administratorName $administratorSurname
			    		Τηλ. $telephone
			    		$langEmail : $emailAdministrator";
			    		send_mail($siteName, $emailAdministrator, "$prof_name $prof_surname",	$prof_email, $emailsubject, $emailbody, $charset);
						}
						$tool_content .= "<p>Η αίτηση του καθηγητή απορρίφθηκε";
						if ($sendmail == 1) $tool_content .= " και στάλθηκε ενημερωτικό μήνυμα στη"." διεύθυνση $prof_email";
						$tool_content .= ". <br><br>Σχόλια:<br><pre>$comment</pre></p>\n";
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
					<center><p>Πρόκειται να απορρίψετε την αίτηση καθηγητή με στοιχεία:<br><br>".$d[profname]." ".$d[profsurname]." &lt;".$d[profemail]."&gt;
					<br><br>Σχόλια:	<form action=\"listreq.php\" method=\"post\">
					<input type=\"hidden\" name=\"id\" value=\"".$id."\">
					<input type=\"hidden\" name=\"close\" value=\"2\">
					<input type=\"hidden\" name=\"prof_name\" value=\"".$d['profname']."\">
					<input type=\"hidden\" name=\"prof_surname\" value=\"".$d['profsurname']."\">
					<textarea name=\"comment\" rows=\"5\" cols=\"40\">".$d['comment']."</textarea>
					<br><input type=\"checkbox\" name=\"sendmail\" value=\"1\"
					checked=\"yes\">&nbsp;Αποστολή μηνύματος στο χρήστη, στη διεύθυνση:
					<input type=\"text\" name=\"prof_email\" value=\"".$d['profemail']."\">
					<br><br>(στο μήνυμα θα αναφέρεται και το παραπάνω σχόλιο)
					<br><br><input type=\"submit\" name=\"submit\" value=\"Απόρριψη\"></form></p></center>";
	    }	
	    break;
    case '3':
	    //$tool_content .= "REGISTER HIM/HER AS A TEACHER";
	    $r = "SELECT profname,profsurname,profuname,profpassword,profemail,proftmima,profcomm,comment
	    FROM prof_request WHERE rid='$id'";
	    //$tool_content .= $r."<br>";
	    $d = db_query($r);
		  if($d)
		  {
		  	$m = mysql_fetch_assoc($d);
		  	// check if user name exists
				$username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='".$m['profuname']."'");
				while ($myusername = mysql_fetch_array($username_check)) 
				{
					$user_exist=$myusername[0];
				}
				if((!empty($user_exist)) and ($uname==$user_exist))
				{
					$tool_content .= "<p>$langUserFree</p>
					<br><br><center><p><a href=\"./listreq.php\">$langAgain</a></p></center>";
			  }
			  else
			  {
			  	// do the insert
			  	$s = mysql_query("SELECT id FROM faculte WHERE name='".$m['proftmima']."'");
					$dep = mysql_fetch_array($s);
					$registered_at = time();
			 		$expires_at = time() + 31536000;
			 						
					$inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
						(user_id, nom, prenom, username, password, email, statut, 
						department, inst_id, registered_at, expires_at)
						VALUES ('NULL', '".$m['profsurname']."', '".$m['profname']."', '".$m['profuname']."', '".$m['profpassword']."', '".$m['profemail']."','1',
						'".$dep[0]."', '0', '".$registered_at."', '".$expires_at."')");
					$last_id = mysql_insert_id();
					if($inscr_user)
					{
						$sql = "UPDATE prof_request set status = '2',date_closed = NOW() WHERE rid = '$id'";
			    	if (db_query($sql)) 
			    	{
					  	$tool_content .= "<p>$profsuccess</p><br /><br />
										<center><p><a href='../admin/listreq.php'>$langBackReq</a></p></center>";
						}
						else
						{
							$tool_content .= "The user is now a professor, but the request is still active<br>";
						}
					}
					else
					{
						$tool_content .= "Error.Cannot proceed<br />";
					}
			  }
		  }
		  else
		  {
		  	$tool_content .= "<br>No such prof request with ID:".$id."<br>Cannot Proceed<br>";
		  }
	    
	    break;
    default:
	    break;
    }
}
else
{

	$tool_content .= "<table width=\"99%\"><caption>Λίστα Αιτήσεων</caption><thead><tr>
		<th scope=\"col\">Όνομα</th>
		<th scope=\"col\">Επώνυμο</th>
		<th scope=\"col\">Username</th>
		<th scope=\"col\">E-mail</th>
		<th scope=\"col\">Τμήμα</th>
		<th scope=\"col\">Τηλ.</th>
		<th scope=\"col\">Ημερ. Αιτ.</th>
		<th scope=\"col\">Σχόλια</th>
		<th scope=\"col\">Ενέργειες</th>
		</tr></thead><tbody>";

 	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,comment 
		FROM prof_request WHERE status='1'");

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
		$tool_content .= "<td align=center><font size=\"2\"><a href=\"listreq.php?id=$req[rid]&"."close=1\">Κλείσιμο</a>
			<br><a href=\"listreq.php?id=$req[rid]&"."close=2\">Απόρριψη</a>
			<br><a href=\"listreq.php?id=$req[rid]&close=3"."\">Εγγραφή</a>
			</td></tr>";
	}
	$tool_content .= "</tbody></table>";
}

$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

draw($tool_content,3,'admin');

?>
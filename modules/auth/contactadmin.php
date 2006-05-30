<?php
//$require_login = TRUE;
$langFiles = array('registration', 'opencours');

include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');
$nameTools = "Αποστολή ενημερωτικού email στον ADMIN";
$tool_content = "";

$userid = isset($_GET['userid'])?$_GET['userid']:(isset($_POST['id'])?$_POST['id']:'');
$submit = isset($_POST['submit'])?$_POST['submit']:'';
if(!empty($userid))
{
	$sql=mysql_query("SELECT * FROM user WHERE user_id='".$userid."'");
	while ($m = mysql_fetch_array($sql)) 
	{
		$sirname = $m["nom"];
		$firstname = $m["prenom"];
		$email = $m["email"];
	}
	
	if(!empty($_POST["submit"])) 
	{
		$body = isset($_POST['body'])?$_POST['body']:'';
		$tool_content .= "<table width=\"99%\"><tbody><tr><td>";
		$to = $email;
		$emailsubject = "Ενεργοποίηση λογαριασμού χρήστη";
		$emailbody = "Ο φοιτητής με τα παρακάτω στοιχεία επιθυμεί την 
		επανενεργοποίηση του λογαριασμού του:
		$sirname $firstname
		Email: $email
		Σχόλια: $body";
		if (!send_mail($siteName, $emailAdministrator, '', $to,	$emailsubject, $emailbody, $charset)) 
		{
				$tool_content .= "<h4>Σφάλμα κατά την αποστολή e-mail στη διεύθυνση '$to'!</h4>";
		}
		else
		{
		$tool_content .= "<h4>$emailsuccess</h4>";
		}
		$tool_content .= "</td></tr><tbody></table><br />";
	}
	else
	{
		//$sql = "SELECT * FROM user WHERE user_id='".$uid."'";
		//$res = mysql_query($sql);
		//$row = mysql_fetch_array($res);
		$tool_content .= "<form action=\"./contactadmin.php?userid=".$userid."\" method=\"post\">
	<table width=\"99%\"><caption>Συμπλήρωση Φόρμας</caption><tbody>";
		$tool_content .= "<tr><td width=\"3%\" nowrap valign=\"top\"><b>Όνομα:</b></td><td>".$firstname."</td></tr>";	
		$tool_content .= "<tr><td width=\"3%\" nowrap valign=\"top\"><b>Επίθετο:</b></td><td>".$sirname."</td></tr>";	
		$tool_content .= "<tr><td width=\"3%\" nowrap valign=\"top\"><b>Email:</b></td><td>".$email."</td></tr>";
		$tool_content .= "<tr><td width=\"3%\" nowrap valign=\"top\"><b>Σχόλια:</b></td><td><textarea rows=\"6\" cols=\"40\" name=\"body\">
		Παρακαλώ να ενεργοποιήσετε το λογαριασμό μου
		</textarea></td></tr>";
		$tool_content .= "<tr><td width=\"3%\" nowrap valign=\"top\">&nbsp;</td><td><input type=\"submit\" name=\"submit\" value=\"Αποστολή\"><input type=\"hidden\" name=\"userid\" value=\"".$userid."\"</td></tr>";
		$tool_content .= "</tbody></table></form>";
	}

	
}


$tool_content .= "<center><p><a href=\"../../index.php\">Επιστροφή</a></p></center>";

draw($tool_content,0);


?>
<?
$langFiles = array('gunet','registration','admin');
include '../../include/init.php';
@include('check_admin.inc');
include('../../include/sendMail.inc.php');

$nameTools=$sendinfomail;
$navigation[]= array ("url"=>"index.php", "name"=> "$langAdmin");
begin_page();

echo "<tr><td>";
$conn = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
if (!mysql_select_db("$mysqlMainDb", $conn)) {
        die("Cannot select database $mysqlMainDb.\n");
        }


if (isset($sendmail) && $sendmail == 1) {
	
	$sql=mysql_query("SELECT DISTINCT email FROM user where statut='1'");
	while ($m = mysql_fetch_array($sql)) {
		$to = $m[0];
		$emailsubject = $infoabouteclass;
		$emailbody = "$body_mail 

$langManager $siteName
$administratorName $administratorSurname
Τηλ. $telephone
$langEmail : $emailAdministrator
";
		if (!send_mail($siteName, $emailAdministrator, '', $to,
			$emailsubject, $emailbody, $charset)) {
				echo "<h4>Σφάλμα κατά την αποστολή e-mail στη διεύθυνση '$to'!</h4>";
		}
	}
	echo "<h4>$emailsuccess</h4>";
	echo "<p><center><a href=\"index.php\">Επιστροφή</a></p></center>";
	$sendmail=0;

} else {

?>

<h5><?echo $typeyourmessage;?></h5>

<form action="mailtoprof.php" method="post">
<textarea name="body_mail" rows="10" cols="60"></textarea>
<br><br>
<input type="checkbox" name="sendmail" value="1" checked="yes">
&nbsp;Αποστολή μηνύματος<br><br><input type="submit" name="submit" value="Αποστολή"></input>
</form>
<p><center><a href="index.php">Επιστροφή</a></p></center>
</body>
</html>
<?
	}
?>

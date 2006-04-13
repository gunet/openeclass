<?

$langFiles = array('gunet','registration','admin');
include "../../include/init.php";
@include("check_admin.inc");
include('../../include/sendMail.inc.php');

$nameTools= "Ανοικτές Αιτήσεις Καθηγητών";
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

echo "<tr><td><br>";

$conn = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
if (!mysql_select_db("$mysqlMainDb", $conn)) {
        die("Cannot select database $mysqlMainDb.\n");
	}
if (isset($close) && $close == 1) {
	$sql = db_query("UPDATE prof_request set status='2', date_closed=NOW() WHERE rid='$id'");

	echo "<br><br><center>Η αίτηση του καθηγητή έκλεισε !</center>";
} elseif (isset($close) && $close == 2) {
	if (!empty($comment)) {
		if (db_query("UPDATE prof_request set status = '2',
					    date_closed = NOW(),
					    comment = '".mysql_escape_string($comment)."'
					    WHERE rid = '$id'")) {
			if ($sendmail == 1) {
        $emailsubject = "Απόρριψη αίτησης εγγραφής στην Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης";
				$emailbody = "
Η αίτησή σας για εγγραφή στην πλατφόρμα e-Class απορρίφθηκε.
Σχόλια:

> $comment

$langManager $siteName
$administratorName $administratorSurname
Τηλ. $telephone
$langEmail : $emailAdministrator

";
				send_mail($siteName, $emailAdministrator, "$prof_name $prof_surname",
					$prof_email, $emailsubject, $emailbody, $charset);
			}
			echo "<br><br><center>Η αίτηση του καθηγητή απορρίφθηκε";
			if ($sendmail == 1) echo " και στάλθηκε ενημερωτικό μήνυμα στη".
				" διεύθυνση $prof_email";
			echo ". <br><br>Σχόλια:<br><pre>$comment</pre></center>\n";
		}
	} else {
		$r = db_query("SELECT comment, profname, profsurname, profemail
					     FROM prof_request WHERE rid = '$id'");
		$d = mysql_fetch_assoc($r);
?>
		<br><br>
		<center>Πρόκειται να απορρίψετε την αίτηση καθηγητή με
		στοιχεία:<br><br><? echo "$d[profname] $d[profsurname] &lt;$d[profemail]&gt;" ?>
		<br><br>Σχόλια:
		<form action="listreq.php" method="post">
			<input type="hidden" name="id" value="<? echo $id ?>">
			<input type="hidden" name="close" value="2">
			<input type="hidden" name="prof_name" value="<? echo $d['profname'] ?>">
			<input type="hidden" name="prof_surname" value="<? echo $d['profsurname'] ?>">
			<textarea name="comment" rows="5" cols="40"><?
				echo $d['comment'] ?></textarea>
			<br><input type="checkbox" name="sendmail" value="1"
				checked="yes">&nbsp;Αποστολή μηνύματος στο χρήστη, στη
				διεύθυνση:
			<input type="text" name="prof_email" value="<? echo $d['profemail'] ?>">
			<br><br>(στο μήνυμα θα αναφέρεται και το παραπάνω σχόλιο)
			<br><br><input type="submit" name="submit" value="Απόρριψη">
		</form>
<?

	}
}

else {

 echo "<table border=\"1\">\n<tr><th>Όνομα</th><th>Επώνυμο</th>".
             "<th>Username</th>
		<th>E-mail</th>
		<th>Τμήμα</th>
		<th>Τηλ.</th>
		<th>Ημερ. Αιτ.</th>
		<th>Σχόλια</th>
		<th>Ενέργειες</th>
		</tr>";

	$sql = db_query("SELECT rid,profname,profsurname,profuname,profemail,proftmima,profcomm,date_open,comment 
		FROM prof_request WHERE status='1'");

	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$req = mysql_fetch_array($sql);
		echo("<tr>");
		for ($i = 1; $i < mysql_num_fields($sql); $i++) {
			if ($i == 4 and $req[$i] != "") {
				echo("<td><a href=\"mailto:".
				htmlspecialchars($req[$i])."\">".
				htmlspecialchars($req[$i])."</a></td>");
			} else {
				echo("<td>".
				htmlspecialchars($req[$i])."</td>");
			}
		}
		echo("<td align=center><font size=\"2\"><a href=\"listreq.php?id=$req[rid]&"."close=1\">Κλείσιμο</a>
			<br><a href=\"listreq.php?id=$req[rid]&"."close=2\">Απόρριψη</a>
			<br><a href=\"../auth/newprof.php?".
			"id=".urlencode($req['rid']).
			"&pn=".urlencode($req['profname']).
			"&ps=".urlencode($req['profsurname']).
			"&pu=".urlencode($req['profuname']).
			"&pe=".urlencode($req['profemail']).
			"&pt=".urlencode($req['proftmima']).
			"\">Εγγραφή</a>
			<br><a href=\"../auth/ldapnewprof.php?"."id=$req[rid]&m=$req[profemail]\">Εγγραφή (μέσω LDAP)</a>
			</td>");	
		echo ("</tr>");
	}
	echo "</table>";
}
?>
<center><p><a href="index.php">Επιστροφή</a></p></center>
</body></html>

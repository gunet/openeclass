<?
$langFiles = array('gunet','admin');
include '../../include/init.php';
@include("check_admin.inc");

$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

if (!isset($doit) or $doit != "yes") {

	echo "
		<h3>Επιβεβαίωση διαγραφής</h3>
		<p>Θέλετε σίγουρα να διαγράψετε τον χρήστη <em>$un</em>";
	if (isset($c) and $c != "") echo " από το μάθημα με κωδικό <em>$c</em>";
	echo ";</p>
		<ul><li>Ναι: 
			<a href=\"unreguser.php?u=$u&c=$c&doit=yes\">Διαγραφή!</a>
			<br>&nbsp;</li>
		<li>Όχι: <a href=\"index.php\">
			Επιστροφή στη σελίδα διαχείρισης</a></li></ul>
	";	


} else {

	$conn = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
        if (!mysql_select_db($mysqlMainDb, $conn))
                die("Cannot select database \"claroline\".\n");

	if ($c==""  and isset($u)) {
		if ($u == 1) {
			echo "Σφάλμα! Προσπαθήσατε να διαγράψετε τον χρήστη με user id = 1!";
			exit;
		}
		$sql = mysql_query("DELETE from user WHERE user_id = '$u'");

		if (mysql_affected_rows($conn) > 0) {
        		echo "<p>Ο χρήστης με id $u διαγράφτηκε.</p>\n";
		} else {
        		echo "Σφάλμα κατά τη διαγραφή του χρήστη";
		}
		mysql_query("DELETE from admin WHERE idUser = '$u'");
		if (mysql_affected_rows($conn) > 0) {
        		echo "<p>Ο χρήστης με id $u ήταν διαχειριστής.</p>\n";
		}
	} elseif (isset($c) and isset($u)) 
		{
		$sql = mysql_query("DELETE from cours_user WHERE user_id = '$u' and code_cours='$c'");
		if (mysql_affected_rows($conn) > 0)  
			echo "<p>Ο χρήστης με id $u διαγράφτηκε από το Μάθημα $c.</p>\n";
		else
			echo "Σφάλμα κατά τη διαγραφή του χρήστη";
		}
	echo "<br>&nbsp;<br><a href=\"index.php\">Επιστροφή στη σελίδα διαχείρισης</a>\n";
}	
?>
</body></html>

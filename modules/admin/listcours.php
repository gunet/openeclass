<?
$langFiles = array('admin','gunet');
include '../../include/init.php';
@include "check_admin.inc";

$nameTools = "Λίστα Μαθημάτων / Ενέργειες";
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

if (isset($ord)) {
	switch ($ord) {
		case "s":
			$order = "b.statut"; break;
		case "n":
			$order = "a.nom"; break;
		case "p":
			$order = "a.prenom"; break;
		case "u":
			$order = "a.username"; break;
		default:
			$order = "b.statut"; break;
	}
} else {
	$order = "b.statut";
}

if (isset($c)) {
	$sql = mysql_query("
		SELECT a.nom, a.prenom, a.username, a.password, b.statut, a.user_id
		FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
		WHERE b.code_cours='$c' ORDER BY $order");
	if (!$sql) {
		die("Unable to query database!");
	}
}

// Αν δεν είναι ορισμένη η παράμετρος c (c=<Κωδικός μαθήματος>),
// ή δεν βρίσκονται χρήστες που να έχουν μάθημα με τον κωδικό αυτόν,
// εμφανίζεται η σελίδα με τα μαθήματα.

if (!isset($c) or mysql_num_rows($sql) == 0) {

	$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours"));
	echo "<p><i>Υπάρχουν $a[0] μαθήματα</i></p>";
	echo "<table border=\"1\">\n<tr><th>Τμήμα</th><th>Κωδικός</th>".
	     "<th>Τίτλος (Διδάσκων)</th><th>Κατάσταση Μαθήματος</th><th>Χρήστες</th><th>Όριο Αποθηκ. Χώρου<th>Διαγραφή 
Μαθήματος</th></tr>";

	$sql = mysql_query(
		"SELECT faculte, code, intitule,titulaires,visible FROM cours ORDER BY faculte");
	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$logs = mysql_fetch_array($sql);
		echo("<tr>");
		 for ($i = 0; $i < 2; $i++) {
			echo("<td width='500'>".htmlspecialchars($logs[$i])."</td>");
		}
		echo "<td width='500'>".htmlspecialchars($logs[2])." ($logs[3])</td>";
		switch ($logs[4]) {
		case 2:
			echo "<td>Ανοιχτό</td>"; break;
		case 1:
			echo "<td>Απαιτείται Εγγραφή</td>"; break;
		case 0:
			echo "<td>Κλειστό</td>"; break;
		}	
		echo "<td><a href=\"listcours.php?c=$logs[1]\">Χρήστες</a></td>";
		echo "<td><a href=\"quotacours.php?c=$logs[1]\">Αλλαγή</a></td>";
		echo "<td><a href=\"delcours.php?c=$logs[1]\">Διαγραφή</a></td>\n";
  }
	echo "</table>\n";

echo "<center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

} else {

// Αν έχει ζητηθεί κάποιο μάθημα με τον κωδικό c, και βρέθηκαν χρήστες,
// εμφανίζεται η σελίδα με τους χρήστες:


	echo "<table border=\"1\">\n<tr><th>".
	     "<a href=\"listcours.php?c=$c&ord=n\">Επώνυμο</a></th><th>".
			 "<a href=\"listcours.php?c=$c&ord=p\">Όνομα</a></th><th>".
			 "<a href=\"listcours.php?c=$c&ord=u\">Username</a></th><th>".
			 "Password</th><th>".
			 "<a href=\"listcours.php?c=$c&ord=s\">Ιδιότητα</a></th>".
			 "<th>Λειτουργίες</th></tr>";

	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$logs = mysql_fetch_array($sql);
		echo("<tr>");
		for ($i = 0; $i < 4; $i++) {	
			echo("<td>".htmlspecialchars($logs[$i])."</td>");
		}
		switch ($logs[4]) {
			case 1:
				echo "<td>Καθηγητής</td>"; break;
			case 5:
				echo "<td>Φοιτητής</td>"; break;
			default:
				echo "<td>¶λλο ($logs[4])</td>"; break;
		}
		echo "<td><a href=\"edituser.php?u=$logs[5]\">Επεξεργασία</a></td></tr>\n";
  }
	echo "</table>
		<p><a href=\"listcours.php\">Επιστροφή στη λίστα μαθημάτων</a></p>\n";

}

?>
</body></html>

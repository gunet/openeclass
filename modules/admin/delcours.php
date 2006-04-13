<?
$langFiles = array('gunet','admin');
include '../../include/init.php';
@include "check_admin.inc";

$nameTools = "Διαγραφή Μαθήματος";
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

if (!isset($doit) or $doit != "yes") {
        echo "<h4>Επιβεβαίωση διαγραφής Μαθήματος</h4>
              <p>Θέλετε σίγουρα να διαγράψετε το μάθημα με κωδικό <em>$c</em>;</p>
              <ul><li>Ναι:<a href=\"delcours.php?c=$c&doit=yes\">Διαγραφή!</a><br>&nbsp;</li>
              <li>Όχι: <a href=\"index.php\">Επιστροφή στη σελίδα διαχείρισης</a></li></ul>";
} else {
	mysql_query("DROP DATABASE '$c'");
	mysql_query("DELETE FROM cours WHERE code='$c'");
	mysql_query("DELETE FROM cours_user WHERE code_cours='$c'");
	mysql_query("DELETE FROM cours_faculte WHERE code='$c'");
	mysql_query("DELETE FROM annonces WHERE code_cours='$c'");
	@mkdir("../../courses/garbage");			
	rename("../../courses/$c","../../courses/garbage/$c");
	echo "<br>";
	echo "Το μάθημα $c διαγράφτηκε με επιτυχία !";
	echo "<br>";
	echo "<br>";	
	echo "<a href=\"index.php\">Επιστροφή στη σελίδα διαχείρισης</a>";
}
end_page();
?>

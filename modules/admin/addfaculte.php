<?
$langFiles = array('admin','gunet','faculte');
include '../../include/init.php';
@include "check_admin.inc";

$nameTools=$langListFaculte;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

echo "<tr><td>";

// Λίστα με όλες τις σχολές
if (!isset($a)) {
	$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM faculte"));
        echo "<p><i>Υπάρχουν $a[0] Σχολές / Τμήματα</i></p>";
	echo "<p><a href=\"addfaculte.php?a=1\">Προσθήκη</a></p>";
	echo "<table border=\"1\">\n<tr><th>$langCodeF</th><th>Σχολή / Τμήμα</th><th>Ενέργειες</th></tr>";
	$sql=mysql_query("SELECT code,name FROM faculte");
	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
                	$logs = mysql_fetch_array($sql);
                	echo("<tr>");
                 	for ($i = 0; $i < 2; $i++) {
                        	echo("<td width='500'>".htmlspecialchars($logs[$i])."</td>");
                	}
         	echo "<td><a href=\"addfaculte.php?a=2&c=$logs[1]\">Διαγραφή</a></td></tr>\n";
	}
	echo "</table>";
	echo "<p><center><a href=\"index.php\">$langBackToIndex</a></p></center>";
}

// Προσθήκη σχολής / τμήματος

elseif ($a == 1)  {
	if (isset($add)) {
		// Πεδία κενά
		if (empty($codefaculte) or empty($faculte)) {
			echo "<center><p>$langEmptyFaculte</p></center>";
			echo "<p><center><a href=\"index.php\">$langBackToIndex</a></p></center>";
			}
		// Οχι Ελληνικά
		elseif (!preg_match("/^[A-Z0-9a-z_-]+$/", $codefaculte)) {
			echo "<center><p>$langGreekCode</p></center>";
			echo "<p><center><a href=\"index.php\">$langBackToIndex</a></p></center>";
			}
		// Mήπως υπάρχει ήδη η σχολή / κωδικός 
		elseif (mysql_num_rows(mysql_query("SELECT * from faculte WHERE code='$codefaculte'")) > 0) {
			echo "<center><p>$langFCodeExists</p></center>";
			echo "<p><center><a href=\"index.php\">$langBackToIndex</a></p></center>";
			} 
			elseif (mysql_num_rows(mysql_query("SELECT * from faculte WHERE name='$faculte'")) > 0) {
			echo "<center><p>$langFaculteExists</p></center>";
			echo "<p><center><a href=\"index.php\">$langBackToIndex</a></p></center>";
			} else {
		// Οκ δημιούργησέ τον 
			mysql_query("INSERT into faculte(code,name,generator,number) VALUES('$codefaculte','$faculte','100','1000')") 
				or die ($langNoSuccess);
			echo $langAddSuccess;
			echo "<p><center><a href=\"index.php\">$langBackToIndex</a></p></center>";
			}
	}  else {
?>
		<font size="1" face="arial, helvetica">
		<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?a=1">
		<table>
		<tr><td><?= $langCodeFaculte1 ?>:&nbsp;</td><td><input type="text" name="codefaculte" value="<?= @$codefaculte ?>"></td></tr>
		<tr><td><font size="-1"><? echo $langCodeFaculte2 ?>:&nbsp;</font></td></tr>
		<tr><td><?= $langFaculte1 ?>:&nbsp;</td><td><input type="text" name="faculte" value="<?= @$faculte ?>"></td></tr>
		<tr><td><font size="-1"><? echo $langFaculte2 ?>:&nbsp;</font></td></tr>
		<tr><td><br></td></tr>
		<tr><td><input type="submit" name="add" value="<?= $langAddYes ?>"></td</tr>
		</table>
		</form>
		<br>
		</font>
		</td></tr>
		<tr>
<?
		}
	}

// Διαγραφή
 
elseif ($a == 2) {
	$s=mysql_query("SELECT * from cours WHERE faculte='$c'"); 
	if (mysql_num_rows($s) > 0)  {
		echo "<br><br><p><center>$langProErase</p>";
		echo "<p>$langNoErase</p></center>";
		}
	else {
		mysql_query("DELETE from faculte WHERE name='$c'");
		echo "<br><br><p><center>$langErase</center></p>";
	     }	
	echo "<p><center><a href=\"index.php\">$langBackToIndex</a></p></center>";
}
?>
</font>
</body>
</html>

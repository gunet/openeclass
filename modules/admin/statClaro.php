<?

$langFiles = 'admin';
include '../../init.php';
include "check_admin.inc";

$langStat4Claroline = "Στατιστικά πλατφόρμας";

$nameTools = $langStat4Claroline;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

?>
<tr><td>
<ul><li><?= $langNbLogin ?>
<ul><li>
Από <? echo list_1Result("select loginout.when from loginout order by loginout.when limit 1 "); ?>
: <b><? echo list_1Result("select count(*) from loginout where loginout.action ='LOGIN' "); ?></b>
<li>
<?= $langLast30Days ?>
: <b><? echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > 
DATE_SUB(CURDATE(),INTERVAL 30 DAY))"); ?></b>
<li>
<?= $langLast7Days ?>
: <b><? echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > 
DATE_SUB(CURDATE(),INTERVAL 7 DAY))"); ?></b>
<li>
<?= $langToday ?>:
<b><? echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > curdate())"); 
?></b>
</ul>
<li><?= $langNbProf ?> : <b><?= list_1Result("select count(*) from user where statut = 1;");?></b></li>
<li><?= $langNbStudents ?> : <b><?= list_1Result("select count(*) from user where statut = 5;");?></b></li>
<li>Αριθμός επισκέπτων: <b><?= list_1Result("select count(*) from user where statut = 10;");?></b></li>
<br>
<li>Αριθμός μαθημάτων : <b><?= list_1Result("select count(*) from cours;");?></b></li>
<li>Αριθμός μαθημάτων ανά τμήμα  : <? tablize(list_ManyResult("select DISTINCT faculte, count(*) from cours Group by faculte ")); ?></li>
<li>Αριθμός μαθημάτων ανά γλώσσα : <? tablize(list_ManyResult("select DISTINCT languageCourse, count(*) from cours Group by languageCourse ")); ?></li>
<li>Αριθμός μαθημάτων ανά κατάσταση ορατότητας: <? tablize(list_ManyResult("select DISTINCT visible, count(*) 
from cours Group by visible ")); ?></li>
<li>Αριθμός μαθημάτων ανά τύπο μαθημάτων: <? tablize(list_ManyResult("select DISTINCT type, 
count(*) from cours Group by type ")); ?></li>
<li>Αριθμός εγγραφών ανά μάθημα : <? tablize(list_ManyResult("select CONCAT(code_cours,\" Statut :\",statut), count(user_id) 
from cours_user Group by code_cours, statut order by code_cours")); ?></li>
<li><? echo $langNbAnnoucement."  : ".list_1Result("select count(*) from annonces;");?></li>
</li>
</ul>
<font size="+2" color="#FF0000">Σφάλματα:</font>
<ul>
<li><strong>Πολλαπλές εγγραφές χρηστών:</strong><br>
<?  
$sqlLoginDouble = "select DISTINCT username , count(*) as nb from user group by username HAVING nb > 1  order by nb desc ";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (count($loginDouble) > 0) { 	
	echo "<br>";
	error_message();
 	echo "<br>";
	tablize($loginDouble);
} else { 
	ok_message();
}
?>
</li>
<li><strong>Πολλαπλές εμφανίσεις διευθύνσεων e-mail</strong> : <br>
<?
$sqlLoginDouble = "select DISTINCT email, count(*) as nb from user group by email HAVING nb > 1  order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (count($loginDouble) > 0) { 	
	echo "<BR>";
	error_message();
 	echo "<BR>";
	tablize($loginDouble);
} 
else
{ 
	ok_message();
}
?>

</li>
<li><strong>Πολλαπλά ζεύγη LOGIN - PASS</strong>: <br>

<?  
$sqlLoginDouble = "select DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb from user group by paire HAVING nb > 1   order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (count($loginDouble) > 0) { 
	echo "<br>";
	error_message();
	echo "<br>";
	tablize($loginDouble);
} else { 
	echo "<br>";
	ok_message();
	echo "<br>";
}
?>
</li></ul></td></tr>
</table>
</body>
</html>

<?

/**
 * output an <Table> with an array
 *
 * @return void
 * @param  array $tableau arrey to output
 * @desc output an <Table> with an array
 */
 
function tablize($tableau) { 
	if (is_array($tableau)) { 
		echo "<table ";
		echo "align=\"center\"  ";
    	echo "bgcolor=\"#ffcccc\"  border=\"1\" ";
    	echo "cellpadding=\"1\" cellspacing=\"0\" > ";
    	while ( list( $key, $laValeur ) = each($tableau)) { 
			echo "<tr>"; 
			switch ($key) {
				case '0': $key = 'Κλειστά'; break;
				case '1'; $key = 'Ανοικτά με εγγραφή'; break;
				case '2': $key = 'Ανοικτά'; break;
				case '5': $key = 'Φοιτητές'; break;
				case '10': $key = 'Επισκέπτες'; break;
				case 'pre': $key = 'Προπτυχιακά'; break;
				case 'post': $key = 'Μεταπτυχιακά'; break;
				case 'other': $key = '¶λλο'; break;
				case 'english': $key = 'Αγγλικά'; break;
				case 'greek': $key = 'Ελληνικά'; break;
			}
			if (strpos($key, 'Statut :10')) $key = substr_replace($key, 'Επισκέπτες', strlen($key)-10);
			if (strpos($key, 'Statut :1')) $key = substr_replace($key, 'Καθηγητές', strlen($key)-9);
			if (strpos($key, 'Statut :5')) $key = substr_replace($key, 'Φοιτητές', strlen($key)-9);
			echo "<td bgcolor=\"#e6e6e6\">".$key."</td>";
			echo "<td bgcolor=\"#f5f5f5\"><strong>".$laValeur."</strong></td>";
			echo"</tr>";
		}
	echo "</table>";
	}
}

function ok_message() {  
    	echo " <font color=\"#00FF000\">Εντάξει!</font>";
}

function error_message() {
	echo "<font size=\"+1\" color=\"#FF0000\"><B>Προσοχή!</B></font>";
} 


function list_1Result($sql) {
	global $db;
	$res = mysql_query($sql ,$db);
	$res = mysql_fetch_array($res);
	return $res[0];
}

function list_ManyResult($sql) { 
	global $db;
	$resu=array();

	$res = mysql_query($sql ,$db);
	while ($resA = mysql_fetch_array($res))
	{ 
		$resu[$resA[0]]=$resA[1];
	}
	return $resu;
}

?>

<?

$langFiles = 'admin';
include '../../include/init.php';
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
: <? echo list_1Result("select count(*) from loginout where loginout.action ='LOGIN' "); ?>
<li>
<?= $langLast30Days ?>
: <? echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_SUB(CURDATE(),INTERVAL 30 DAY))"); ?>
<li>
<?= $langLast7Days ?>
: <? echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_SUB(CURDATE(),INTERVAL 7 DAY))"); ?>
<li>
<?= $langToday ?>:
<? echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > curdate())"); ?>
</ul>
<li><?= $langNbProf ?> (1): <?= list_1Result("select count(*) from user where statut = 1;");?></li>
<li><? echo $langNbStudents ?> (5): <?= list_1Result("select count(*) from user where statut = 5;");?></li>
<li>Αριθμός εγγραφών ανά κατηγορία : <? tablize(list_ManyResult("select DISTINCT statut, count(*) from user Group by statut ")); ?>
<li>Αριθμός μαθημάτων : <?= list_1Result("select count(*) from cours;");?></li>
<li>Αριθμός μαθημάτων ανά τμήμα  : <? tablize(list_ManyResult("select DISTINCT faculte, count(*) from cours Group by faculte ")); ?></li>
<li>Αριθμός μαθημάτων ανά γλώσσα : <? tablize(list_ManyResult("select DISTINCT languageCourse, count(*) from cours Group by languageCourse ")); ?></li>
<li>Αριθμός μαθημάτων ανά κατάσταση ορατότητας: <? tablize(list_ManyResult("select DISTINCT visible, count(*) from cours Group by visible ")); ?></li>
<li>Αριθμός εγγραφών ανά μάθημα : <? tablize(list_ManyResult("select CONCAT(code_cours,\" Statut :\",statut), count(user_id) from cours_user Group by code_cours, statut order by code_cours")); ?></li>
<li><? echo $langNbAnnoucement."  :  ".list_1Result("select count(*) from annonces;");?></li>
</li>
</ul>
<font size="+2" color="#FF0000">Σφάλματα:</font>
<ul>
<li><strong>Πολλαπλές εγγραφές χρηστών:</strong><br>
<?  
$sqlLoginDouble = "select DISTINCT username , count(*) as nb from user group by username HAVING nb > 1  order by nb desc ";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (is_array($loginDouble)) { 	
	echo "<BR>";
	echoDefcon(6);
 	echo "<BR>";
	tablize($loginDouble);
} 
else
{ 
	echoDefcon();
}
?>
</li>
<li><strong>Πολλαπλές εμφανίσεις διευθύνσεων e-mail</strong> : <br>
<?
$sqlLoginDouble = "select DISTINCT email , count(*) as nb from user group by email HAVING nb > 1  order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (is_array($loginDouble)) { 	
	echo "<BR>";
	echoDefcon(7);
 	echo "<BR>";
	tablize($loginDouble);
} 
else
{ 
	echoDefcon();
}
?>

</li>
<li><strong>Πολλαπλά ζεύγη LOGIN - PASS</strong>: <br>

<?  
$sqlLoginDouble = "select DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb from user group by paire HAVING nb > 1   order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (is_array($loginDouble)) { 
	echo "<br>";
	echoDefcon(4);
	echo "<br>";
	tablize($loginDouble);
} 
else
{ 
	echoDefcon();
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
			echo "<td bgcolor=\"#99CCFF\">".$key."</td>";
			echo "<td bgcolor=\"#eeeeee\">".$laValeur."</td>";
			echo"</tr>";
		}
	echo "</table>";
	}
}

function echoDefcon($levelOfDefcon="7") {  
	if ($levelOfDefcon==7)
    		echo "<font color=\"#00FF000\">Εντάξει</font>";
    	else 
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

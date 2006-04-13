<?
$langFiles = array('gunet','admin','registration');
include '../../include/init.php';
@include "check_admin.inc";

$nameTools=$searchuser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();
?>
<tr><td><b><?= $langAskSearch;?></b><br><br><br></td></tr>
<tr>
<td>

<font size="1" face="arial, helvetica">
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">

<table>
<tr><td><? echo $langSurname ?>:&nbsp;</td><td><input type="text" name="search_nom" value="<?= @$search_nom 
?>"></td></tr>
<tr><td><? echo $langName ?>:&nbsp;</td><td><input type="text" name="search_prenom" value="<?= @$search_prenom 
?>"></td></tr>
<tr><td><? echo $langUsername ?>:&nbsp;</td><td><input type="text" name="search_uname" value="<?= @$search_uname ?>"></td></tr>
<tr><td><input type="submit" value="<? echo $langSearch ?>"></td</tr>
</table>

</form>
<br><br><br>
</font>

</td></tr>
<tr>

<?
$conn = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
if (!mysql_select_db("$mysqlMainDb", $conn)) {
        die("Cannot select database $mysqlMainDb.\n");
}

$search=array();
if(!empty($search_nom)) {
	$search[] = "nom LIKE '".mysql_escape_string($search_nom)."%'";
}
if(!empty($search_prenom)) {
	$search[] = "prenom LIKE '".mysql_escape_string($search_prenom)."%'";
}
if(!empty($search_uname)) {
	$search[] = "username LIKE '".mysql_escape_string($search_uname)."%'";
}

$query=join(' AND ',$search);
if (!empty($query)) {
	$sql=mysql_query("SELECT nom,prenom,username,password,email,statut,user_id FROM user WHERE $query");

	if (mysql_num_rows($sql) > 0) {
?>
<td> 
<table width=100% cellpadding=2 cellspacing=1 border=1>
                <tr bgcolor=silver>
                        <th><?= $langSurname ?></th>
			<th><?= $langName ?></th>
                        <th><?= $langUsername ?></th>
			<th><?= $langPass ?></th>
			<th><?= $langEmail ?></th>
			<th>Ιδιότητα</th>
			<th><?= $langphone ?></th>
			<th><?= $langDepartment ?></th>
			<th><?= $langActions ?></th>
                </tr>
<?

 	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$logs = mysql_fetch_array($sql);
		echo "<tr>";
                for ($i = 0; $i < 5 ; $i++) {
                        echo("<td width='500' align=justify>&nbsp;".htmlspecialchars($logs[$i])."</td>");
                }
		 switch ($logs[5]) {
                        case 1:
                                echo "<td>Καθηγητής</td>"; 
				$s2=array();
                                if(!empty($search_nom)) {
                                	$s2[] = "profsurname LIKE '".mysql_escape_string($search_nom)."%'";
                                }
                                if(!empty($search_prenom)) {
                                	$s2[] = "profname LIKE '".mysql_escape_string($search_prenom)."%'";
                                }
                                if(!empty($search_uname)) {
                                	$s2[] = "profuname LIKE '".mysql_escape_string($search_uname)."%'";
                                }
                                $q2=join(' AND ',$s2);
                                $a=mysql_query("SELECT profcomm,proftmima FROM prof_request WHERE $q2");
                                $p=mysql_fetch_array($a);
                                echo "<td>".htmlspecialchars($p[0])."</td><td>".htmlspecialchars($p[1])."</td>";
				break;
                        case 5:
                                echo "<td>Φοιτητής</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
			case 10:
				echo "<td>Επισκέπτης</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
                        default:
                               echo "<td>¶λλο ($logs[5])</td><td>&nbsp;</td><td>&nbsp;</td>"; break;

                }
		echo "<td><a href=\"edituser.php?u=$logs[user_id]\">Επεξεργασία</a></td>\n";
		echo "</tr>";
	}
} else {
	echo "<tr><td>Δεν βρέθηκε κανένας χρήστης με τα στοιχεία που δώσατε.</td></tr>"; 
	}
}
?>
</td>
</table>
<p><center><a href="index.php">Επιστροφή</a></p></center>
</body>
</html>

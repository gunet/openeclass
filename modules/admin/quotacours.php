<?
$langFiles = 'admin';
include '../../include/init.php';
@include("check_admin.inc");

$nameTools = $langQuotaAdmin;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

echo "<br>";
if (isset($submit))  {
	$dq = $dq * 1000000;
        $vq = $vq * 1000000;
        $gq = $gq * 1000000;
        $drq = $drq * 1000000;
	$sql = mysql_query("UPDATE cours SET doc_quota='$dq',video_quota='$vq',group_quota='$gq',dropbox_quota='$drq' 
			WHERE code='$c'");
	if (mysql_affected_rows() > 0) {
		echo $langQuotaSuccess;
	} else {
		echo $langQuotaFail;
	}

} else {
	$q = mysql_fetch_array(mysql_query("SELECT code,intitule,doc_quota,video_quota,group_quota,dropbox_quota 
			FROM cours WHERE code='$c'"));
	echo "$langTheCourse <b>$q[intitule]</b> $langMaxQuota";
	$dq = $q['doc_quota'] / 1000000;
	$vq = $q['video_quota'] / 1000000;
	$gq = $q['group_quota'] / 1000000;
	$drq = $q['dropbox_quota'] / 1000000;
	echo "<br><br>";
	echo "<form action='$_SERVER[PHP_SELF]'>";
	echo "<table>";
	echo "<tr><td>$langLegend <b>$langDocument</b>:</td>";
	echo "<td><input type='text' name='dq' value='$dq' size='4' maxlength='4'> Mb.</td></tr>";
	echo "<tr><td>$langLegend <b>$langVideo</b>:</td>";
	echo "<td><input type='text' name='vq' value='$vq' size='4' maxlength='4'> Mb.</td></tr>";
	echo "<tr><td>$langLegend <b>$langGroup</b>:</td>";
	echo "<td><input type='text' name='gq' value='$gq' size='4' maxlength='4'> Mb.</td></tr>";
	echo "<tr><td>$langLegend <b>$langDropbox</b>:</td>";
	echo "<td><input type='text' name='drq' value='$drq' size='4' maxlength='4'> Mb.</td></tr>";
	echo "<input type='hidden' name='c' value='$c'>";
	echo "<tr><td><input type='submit' name='submit' value='$langModify'></td></tr>";
	echo "</table>";
	echo "</form>";
}

echo "<br>";
echo "<br>";	
echo "<a href='index.php'>$langBackAdmin</a>";
end_page();

?>

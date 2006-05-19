<?
$langFiles = 'admin';
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Επεξεργασία Μαθήματος";

// Initialise $tool_content
$tool_content = "";
// Main body

if (isset($search) && ($search=="yes")) {
	$searchurl = "&search=yes";
}

if (isset($submit))  {
	$dq = $dq * 1000000;
        $vq = $vq * 1000000;
        $gq = $gq * 1000000;
        $drq = $drq * 1000000;
	$sql = mysql_query("UPDATE cours SET doc_quota='$dq',video_quota='$vq',group_quota='$gq',dropbox_quota='$drq' 
			WHERE code='$c'");
	if (mysql_affected_rows() > 0) {
		$tool_content .= "<p>".$langQuotaSuccess."</p>";
	} else {
		$tool_content .= "<p>".$langQuotaFail."</p>";
	}

} else {
	$q = mysql_fetch_array(mysql_query("SELECT code,intitule,doc_quota,video_quota,group_quota,dropbox_quota 
			FROM cours WHERE code='$c'"));
	$quota_info .= "<i>".$langTheCourse." <b>".$q[intitule]."</b> ".$langMaxQuota;
	$dq = $q['doc_quota'] / 1000000;
	$vq = $q['video_quota'] / 1000000;
	$gq = $q['group_quota'] / 1000000;
	$drq = $q['dropbox_quota'] / 1000000;

	$tool_content .= "<form action=".$_SERVER[PHP_SELF]."?c=".$c."".$searchurl." method=\"post\">
<table width=\"99%\"><caption>".$langQuotaAdmin."</caption><tbody>
  <tr>
    <td colspan=\"2\">".$quota_info."</td>
  </tr>
  <tr>
    <td width=\"3%\" nowrap>$langLegend <b>$langDocument</b>:</td>
    <td><input type='text' name='dq' value='$dq' size='4' maxlength='4'> Mb.</td>
  </tr>
  <tr>
    <td width=\"3%\" nowrap>$langLegend <b>$langVideo</b>:</td>
    <td><input type='text' name='vq' value='$vq' size='4' maxlength='4'> Mb.</td>
  </tr>
  <tr>
    <td width=\"3%\" nowrap>$langLegend <b>$langGroup</b>:</td>
    <td><input type='text' name='gq' value='$gq' size='4' maxlength='4'> Mb.</td>
  </tr>
  <tr>
    <td width=\"3%\" nowrap>$langLegend <b>$langDropbox</b>:</td>
    <td><input type='text' name='drq' value='$drq' size='4' maxlength='4'> Mb.</td>
  </tr>
  <input type='hidden' name='c' value='$c'>
  <tr>
    <td colspan=\"2\"><input type='submit' name='submit' value='$langModify'></td>
  </tr>
</tbody></table>
</form>\n";
}

if (isset($c)) {
	$tool_content .= "<center><p><a href=\"editcours.php?c=".$c."".$searchurl."\">Επιστροφή</a></p></center>";
} else {
	$tool_content .= "<center><p><a href=\"index.php\">".$langBackAdmin."</a></p></center>";
}

draw($tool_content,3,'admin');

?>

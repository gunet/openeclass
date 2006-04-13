<?
$langFiles = array('admin','about');
include '../../include/init.php';
include "check_admin.inc";
$nameTools = $langVersion;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();
?>
<tr><td><table cellpadding="3" cellspacing="0" border="0" width="100%">
<tr valign="top" bgcolor="<?= $color2 ?>">
<td><font size="2" face="arial, helvetica">
<p align=center><?= $langAboutText ?></p>
<p align=center><b><?= $langEclassVersion?></b></p>
<p align=center><?= $langHostName?><b><?= $SERVER_NAME?></b></p>	
<p align=center><?= $langWebVersion?><b><?= $SERVER_SOFTWARE?></b></p>
<? 
if (extension_loaded('mysql')) 
	echo "<p align=center>$langMySqlVersion<b>".mysql_get_server_info()."</b></p>";
else 
	echo "<p align=center font color=\"red\">$langNoMysql</p>";
?>	
</font>
<br><br><br>
<?				
end_page();
?>

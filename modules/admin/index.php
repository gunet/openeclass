<?
$langFiles = array('gunet','admin');
include '../../include/init.php';
@include "check_admin.inc";

$nameTools = $langAdmin;
begin_page();
	
/* Check for LDAP server entries */
$ldap_entries = mysql_fetch_array(mysql_query("SELECT ldapserver FROM institution"));
if ($ldap_entries['ldapserver'] <> NULL)
	$newuser = "newprof_info.php";
else
	$newuser = "newprof.php";
?>

<ul>
<b><u><?= $langAdminProf ?></u></b>
<li><a href="../auth/<?= $newuser ?>"><?= $langProfReg ?></a></li>
<li><a href="listreq.php"><?= $langProfOpen ?></a></li>
<li><a href="mailtoprof.php"><?= $langInfoMail ?></a></li>
</ul>

<ul>
<b><u><?= $langAdminUsers ?></u></b>
<li><a href="listusers.php"><?= $langListUsers ?></a></li>
<li><a href="search_user.php"><?= $langSearchUser ?></a></li>
<li><a href="addadmin.php"><?= $langAddAdminInApache ?></a></li>
</ul>

<ul>
<b><u><?= $langAdminCours ?></u></b>
<li><a href="listcours.php"><?= $langListCours ?></a></li>
<li><a href="../course_info/restore_course.php"><?= $langRestoreCourse ?></a></li>
<li><a href="speedSubscribe.php"><?= $langSpeeSubscribe ?></a></li>
<li><a href="addfaculte.php"><?= $langListFaculte ?></a></li>
</ul>

<b><?= $langState ?></b>

<ul>
<? 
if (isset($phpSysInfoURL)&&PHP_OS!="WIN32"&&PHP_OS!="WINNT")
	echo "<li><a href='".$phpSysInfoURL."'>".$langSysInfo."</a></li>";
?>
<li><a href="phpInfo.php?to=phpinfo"><?= $langPHPInfo ?></a></li>
</ul>

<b><?= $langDevAdmin ?></b>
<ul>
<?
if (isset($phpMyAdminURL))
	echo "<li><a href='".$phpMyAdminURL."' target=_blank>".$langDBaseAdmin."</a></li>";
?>
</uL>

<b><?= $langGenAdmin ?></b>

<ul>
<li><a href="about.php"><?= $langVersion ?></a></li>
<li><a href="phpInfo.php?to=clarconf"><?= $langConfigFile ?></a></li>
<li><a href="statClaro.php"><? echo $langStatOf." ".$siteName ?></a></li>

<?
if (isset($phpMyAdminURL))
	echo "<li><a href='".$phpMyAdminURL."sql.php?db=".$mysqlMainDb."&table=loginout&goto=db_details.php&sql_query=SELECT+%2A+FROM+%60loginout%60&pos=0'>".$langLogIdentLogout."</a></li>";
?>

<li><a href="<?= $urlServer?>/manuals/manual.php"><?= $langManuals ?></a></li>
<li><a href="<?= $urlServer?>/manuals/manA/admin.txt"><?= $langAdminManual ?></a></li>
</ul>

<?
end_page();
?>

<?
$langFiles = 'admin';
include '../../include/init.php';
@include "check_admin.inc";

$nameTools = "Πληροφορίες για την PHP";
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();
echo "</tr></td>";
end_page();

if (!isset($to)) $to = '';

if ($to=="phpinfo") {
	echo '<div style="background-color: white;">';
	phpinfo();
	echo '</div>';
}
elseif ($to=="clarconf") {
	echo '<div style="background-color: #dfdfff;"><HR>config file<HR>';
	highlight_file("../../include/config.php");
	echo '<hr></div>';
}

?>
</body>
</html>

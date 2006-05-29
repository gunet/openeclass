<?
$langFiles = array('gunet','admin');
include '../../include/baseTheme.php';
@include "check_admin.inc";

$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);

$u = isset($_GET['u'])?$_GET['u']:'';
$doit = isset($_GET['doit'])?$_GET['doit']:'';
$c = isset($_GET['c'])?$_GET['c']:'';

if((!empty($doit)) && ($doit != "yes")) 
{
	$tool_content .= "<h4>Επιβεβαίωση διαγραφής</h4>
		<p>Θέλετε σίγουρα να διαγράψετε τον χρήστη <em>$un</em>";
	if(!empty($c)) 
	{
		$tool_content .= " από το μάθημα με κωδικό <em>".$c."</em>";
	}
	$tool_content .= ";</p>
		<ul><li>Ναι: 
			<a href=\"unreguser.php?u=$u&c=$c&doit=yes\">Διαγραφή!</a>
			<br>&nbsp;</li>
		<li>Όχι: <a href=\"index.php\">Επιστροφή στη σελίδα διαχείρισης</a></li></ul>";	
} 
else 
{

	$conn = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
        if (!mysql_select_db($mysqlMainDb, $conn))
                die("Cannot select database \"claroline\".\n");

	if(empty($c)) 
	{
		if ($u == 1) 
		{
			$tool_content .= "Σφάλμα! Προσπαθήσατε να διαγράψετε τον χρήστη με user id = 1!";
		}
		$sql = mysql_query("DELETE from user WHERE user_id = '$u'");
		if (mysql_affected_rows($conn) > 0) 
		{
			$tool_content .= "<p>Ο χρήστης με id $u διαγράφτηκε.</p>\n";
		} 
		else 
		{
			$tool_content .= "Σφάλμα κατά τη διαγραφή του χρήστη";
		}
		mysql_query("DELETE from admin WHERE idUser = '$u'");
		if (mysql_affected_rows($conn) > 0) 
		{
			$tool_content .= "<p>Ο χρήστης με id $u ήταν διαχειριστής.</p>\n";
		}
	} 
	elseif((!empty($c)) && (!empty($u)))
	{
		$sql = mysql_query("DELETE from cours_user WHERE user_id = '$u' and code_cours='$c'");
		if (mysql_affected_rows($conn) > 0)  
		{
			$tool_content .= "<p>Ο χρήστης με id $u διαγράφτηκε από το Μάθημα $c.</p>\n";
		}
	}
	else
	{
			$tool_content .= "Σφάλμα κατά τη διαγραφή του χρήστη";
	}
	$tool_content .= "<br>&nbsp;<br><a href=\"index.php\">Επιστροφή στη σελίδα διαχείρισης</a>\n";
}	

$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

draw($tool_content,3,'admin');

?>
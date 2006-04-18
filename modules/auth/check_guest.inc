<?
// added by jexi Tue Mar 18 12:41:23 EET 2003

include('../../config/config.php');

$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
mysql_select_db($mysqlMainDb,$db);

$res = mysql_query("SELECT statut FROM user WHERE user_id = '$uid'");
$g = mysql_fetch_row($res);

if ($g[0] == 10) {
                echo "<center><br><br><b>Χώρος Ελεγχόμενης Πρόσβασης</b><br><br><br>
		<font face=\"arial, helvetica\" size=2>Ο λογαριασμός Επισκέπτη που έχετε δεν σας δίνει αυτό το δικαίωμα.<br>
		Επικοινωνήστε με τον διδάσκοντα του μαθήματος για την απόκτηση λογαριασμού κανονικού χρήστη.<br>		
		Επιστροφή στην <a href=../index.php> αρχική σελίδα</a><br>
                </center>";
                exit();
        }


?>

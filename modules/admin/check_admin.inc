<?

include '../../include/config.php';
$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
mysql_select_db("$mysqlMainDb",$db);


// just make sure that the $uid variable isn't faked
if (isset($_SESSION['uid']))
        $uid = $_SESSION['uid'];
else
        unset($uid);

if (isset($uid)) {
	$res = mysql_query("SELECT * FROM admin WHERE idUser='$uid'");
}

if (!isset($uid) or mysql_num_rows($res) == 0) {

                echo "<center><br><br><b>Χώρος Ελεγχόμενης Πρόσβασης</b><br><br><br>
								<font face=\"arial, helvetica\" size=2>Η σελίδα που προσπαθείτε να μπείτε απαιτεί δικαιώματα  
				διαχειριστή. <br>
				Παρακαλούμε πηγαίνετε στην <a href=../../index.php> αρχική σελίδα</a> και δώστε τα στοιχεία σας.<br>
                      </center>";
                exit();
        }


?>

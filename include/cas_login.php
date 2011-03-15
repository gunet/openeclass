<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Author:				Giannis Kapetanakis <bilias@edu.physics.uoc.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

if (!defined('INDEX_START')) {
	die ("Action not allowed!");
}
// user is authenticated, now let's see if he is registered also in db
$cas_uname = $_SESSION['cas_uname'];
$cas_nom = $_SESSION['cas_nom'];
$cas_prenom = $_SESSION['cas_prenom'];
$cas_email = $_SESSION['cas_email'];

$sqlLogin= "SELECT user_id, nom, username, password, prenom, statut, email, iduser is_admin, perso, lang
	FROM user LEFT JOIN admin
	ON user.user_id = admin.iduser
	WHERE username='".$cas_uname."'";

$r = db_query($sqlLogin); 
if (mysql_num_rows($r) > 0) { // if cas user found 
	$myrow = mysql_fetch_array($r);
		// update user information. set also password to cas
		$update_query = "UPDATE user SET nom='$cas_nom', prenom='$cas_prenom', password='cas' ";
		if (!empty($cas_email)) {
			$update_query .= ",email = '$cas_email' ";
		}
		$update_query .= " WHERE username = '$cas_uname'";
		db_query($update_query);
		$r2 = db_query($sqlLogin);
		$myrow2 = mysql_fetch_array($r2);
		$uid = $myrow2["user_id"];
		$is_admin = $myrow2["is_admin"];
		$userPerso = $myrow2["perso"];
		$nom = $myrow2["nom"];
		$prenom = $myrow2["prenom"];
		if (isset($_SESSION['langswitch'])) {
			$language = $_SESSION['langswitch'];
		} else {
			$language = langcode_to_name($myrow["lang"]);
		}
} else { // CAS auth ok but user not registered. Let's do the normal procedure
	foreach(array_keys($_SESSION) as $key)
		unset($_SESSION[$key]);
	session_destroy();
	unset($uid);
	header('Location: ../modules/auth/registration.php');
	exit;
}
$_SESSION['uid'] = $uid;
$_SESSION['nom'] = $nom;
$_SESSION['prenom'] = $prenom;
$_SESSION['email'] = $cas_email;
$_SESSION['statut'] = 5;
$_SESSION['is_admin'] = $is_admin;
$_SESSION['cas_user'] = 1; // now we are cas user
$log='yes';
mysql_query("INSERT INTO loginout 
	(loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action) 
	VALUES ('', '$uid', '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");

?>

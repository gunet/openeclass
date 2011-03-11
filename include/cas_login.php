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
	while ($myrow = mysql_fetch_array($r)) {
		if ($myrow['password'] == 'cas') {
			// update user information
			$update_query = "UPDATE user SET nom = '$cas_nom', prenom = '$cas_prenom' ";
			if (!empty($cas_email)) {
				$update_query .= ",email = '$cas_email' ";
			}
			$update_query .= " WHERE username = '$cas_uname'";
			db_query($update_query);
			$r2 = db_query($sqlLogin);
			while ($myrow2 = mysql_fetch_array($r2)) {
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
			}
		} else {
			$tool_content .= "<table width='99%'><tbody><tr><td class='caution' height='60'>";
			$tool_content .= "<p>$langUserFree</p></td></tr></table>";
		}
	}	
} else { // else create him
	db_query("INSERT INTO user SET nom='$cas_nom', prenom='$cas_prenom',
		password='cas', username='$cas_uname', email='$cas_email', statut=5, lang='el'");
	$uid = mysql_insert_id();
	$userPerso = 'yes';
	$nom = $cas_nom;
	$prenom = $cas_prenom;
	$language = $_SESSION['langswitch'] = langcode_to_name('el');
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

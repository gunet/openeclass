<?
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
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
// authenticate user via shibboleth
$shib_uname = $_SESSION['shib_uname'];
$shib_email = $_SESSION['shib_email'];
$shib_nom = $_SESSION['shib_nom'];
if (strpos($shib_nom,';')) {
	$temp = explode(';',$shib_nom);
	$shib_nom_en = $temp[0];
	$shib_nom = $temp[1];
}
$sqlLogin= "SELECT user_id, nom, username, password, prenom, statut, email, iduser is_admin, perso, lang
	FROM user LEFT JOIN admin
	ON user.user_id = admin.iduser
	WHERE username='".$shib_uname."'";
$r = db_query($sqlLogin); 
if (mysql_num_rows($r) > 0) { // if shibboleth user found 
	while ($myrow = mysql_fetch_array($r)) {
		// update user information
		db_query("UPDATE user SET nom = '$shib_nom', prenom = '$shib_nom', email = '$shib_email' 
			WHERE username = '$shib_uname'");

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
	}	
} else { // else create him
	db_query("INSERT INTO user SET nom='$shib_nom', prenom='$shib_nom', password='shibboleth', 
		username='$shib_uname',email='$shib_email', statut=5, lang='el'");
	$uid = mysql_insert_id();
	$userPerso = 'yes';
	$prenom = $nom = $shib_nom;
	$language = $_SESSION['langswitch'] = langcode_to_name('el');
}
$_SESSION['uid'] = $uid;
$_SESSION['nom'] = $nom;
$_SESSION['prenom'] = $prenom;
$_SESSION['email'] = $shib_email;
$_SESSION['statut'] = 5;
$_SESSION['is_admin'] = $is_admin;
$_SESSION['shib_user'] = 1; // now we are shibboleth user
$log='yes';
?>
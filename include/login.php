<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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
	die('Action not allowed!');
}

// authenticate user via eclass
if ($uname == escapeSimpleSelect($myrow["username"]))
{
	if (md5($pass) == escapeSimpleSelect($myrow["password"])) {
		// check if account is active
		$is_active = check_activity($myrow["user_id"]);
		if ($myrow["user_id"] == 1) {
			$is_active = 1;
			$auth_allow = 1;
			$is_admin = 1;
		}
		if($is_active == 1) {
			$uid = $myrow["user_id"];
			$nom = $myrow["nom"];
			$prenom = $myrow["prenom"];
			$statut = $myrow["statut"];
			$email = $myrow["email"];
			$userPerso = $myrow["perso"];
			$language = $_SESSION['langswitch'] = langcode_to_name($myrow["lang"]);
			$auth_allow = 1;
		} else {
			$auth_allow = 3;
			$user = $myrow["user_id"];
		}
	}
	else {
		$auth_allow = 4; // means wrong password
	}
} else {
	$auth_allow = 4; // means wrong username or password
}

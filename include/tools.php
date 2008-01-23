<?PHP
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**
 * Tool Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component creates an array of the tools that are displayed on the left
 * side column .
 * 
 */

/**
 * Function getSideMenu
 *
 * Offers an upper-layer logic. Decides what function should be called to
 * create the needed tools array
 *
 * @param int $menuTypeID Type of menu to generate
 * 
 */
function getSideMenu($menuTypeID){

	switch ($menuTypeID){
		case 0: { //logged out
			$menu = loggedOutMenu();
			break;
		}

		case 1: {//logged in
			$menu = loggedInMenu();
			break;
		}

		case 2: { //course home (lesson tools)
			$menu = lessonToolsMenu();
			break;
		}

		case 3: { // admin tools
			$menu = adminMenu();
			break;
		}
	}
	return $menu;
}


/**
 * Function getToolsArray
 *
 * Queries the database for tool information in accordance
 * to the parameter passed.
 *
 * @param string $cat Type of lesson tools
 * @return mysql_resource
 * @see function lessonToolsMenu
 */
function getToolsArray($cat) {
	global $currentCourse, $currentCourseID;
	$currentCourse = $currentCourseID;

	switch ($cat) {
		case 'Public':
			if (!check_guest()) {
				if (isset($_SESSION['uid']) and $_SESSION['uid']) {
					$result = db_query("
                    select * from accueil
                    where visible=1
                    ORDER BY rubrique", $currentCourse);
				} else {
					$result = db_query("
                    select * from accueil
                    where visible=1 AND lien NOT LIKE '%/user.php'
                    ORDER BY rubrique", $currentCourse);
				}
			} else {
				$result = db_query("
				SELECT * FROM `accueil` 
				WHERE `visible` = 1
				AND (
				`id` = 1 or 
				`id` = 2 or 
				`id` = 3 or 
				`id` = 4 or 
				`id` = 7 or 
				`id` = 10 or 
				`id` = 20) 
				ORDER BY rubrique
				", $currentCourse);
			}
			break;
		case 'PublicButHide':

			$result = db_query("
                    select *
                    from accueil
                    where visible=0
                    and admin=0
                    ORDER BY rubrique", $currentCourse);
			break;
		case 'courseAdmin':
			$result = db_query("
                    select *
                    from accueil
                    where admin=1
                    ORDER BY rubrique", $currentCourse);
			break;
		case 'claroAdmin':
			$result = db_query("
                    select *
                    from accueil
                    where visible = 2
                    ORDER BY id", $currentCourse);
			break;
	}

	return $result;

}
//

/**
 * Function loggedInMenu
 *
 * Creates a multi-dimensional array of the user's tools
 * when the user is signed in, and not at a lesson specific tool,
 * in regard to the user's user level.
 *
 * (student | professor | platform administrator)
 *
 * @return array
 */
function loggedInMenu(){
	global $webDir, $language, $uid, $is_admin, $urlServer, $mysqlMainDb;

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	$arrMenuType = array();
	$arrMenuType['type'] = 'none';
	$arrMenuType['text'] = 'none';
	array_push($sideMenuSubGroup, $arrMenuType);
	// User is not currently in a course - set statut from main database

	if (isset($is_admin) and $is_admin) {
		array_push($sideMenuText, "<b>$GLOBALS[langAdminTool]</b>");
		array_push($sideMenuLink, $urlServer . "modules/admin/");
		array_push($sideMenuImg, "admin-tools.gif");
	}

	$res2 = db_query("SELECT statut FROM user WHERE user_id = '$uid'",$mysqlMainDb);

	if ($row = mysql_fetch_row($res2)) $statut = $row[0];

	if ($statut==1) {
		array_push($sideMenuText, $GLOBALS['langCourseCreate']);
		array_push($sideMenuLink, $urlServer . "modules/create_course/create_course.php");
		array_push($sideMenuImg, "create_lesson.gif");
	}

	if ($statut != 10) {

		array_push($sideMenuText, $GLOBALS['langRegCourses']);
		array_push($sideMenuLink, $urlServer . "modules/auth/courses.php");
		array_push($sideMenuImg, "enroll.gif");

		array_push($sideMenuText, $GLOBALS['langMyAgenda']);
		array_push($sideMenuLink, $urlServer . "modules/agenda/myagenda.php");
		array_push($sideMenuImg, "calendar.gif");


		array_push($sideMenuText, $GLOBALS['langMyAnnouncements']);
		array_push($sideMenuLink, $urlServer . "modules/announcements/myannouncements.php");
		array_push($sideMenuImg, "announcements.gif");

		array_push($sideMenuText, $GLOBALS['langModifyProfile']);
		array_push($sideMenuLink, $urlServer . "modules/profile/profile.php");
		array_push($sideMenuImg, "profile.gif");

		array_push($sideMenuText, $GLOBALS['langMyStats']);
		array_push($sideMenuLink, $urlServer . "modules/profile/personal_stats.php");
		array_push($sideMenuImg, "platform_stats.gif");

	}

	if ($statut == 10) {

		array_push($sideMenuText, $GLOBALS['langManuals']);
		array_push($sideMenuLink, $urlServer."manuals/manual.php");
		array_push($sideMenuImg, "manual.gif");

		array_push($sideMenuText, $GLOBALS['langContact']);
		array_push($sideMenuLink, $urlServer."info/contact.php");
		array_push($sideMenuImg, "contact.gif");
	}

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	return $sideMenuGroup;
}

/**
 * Function loggedOutMenu
 *
 * Creates a multi-dimensional array of the user's tools/links
 * for the menu presented when the user is not logged in.
 * *
 * @return array
 */
function loggedOutMenu(){

	global $webDir, $language, $urlServer, $is_eclass_unique, $mysqlMainDb;

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	$arrMenuType = array();
	$arrMenuType['type'] = 'none';
	$arrMenuType['text'] = 'none';
	array_push($sideMenuSubGroup, $arrMenuType);

	array_push($sideMenuText, $GLOBALS['langListCourses']);
	array_push($sideMenuLink, $urlServer."modules/auth/listfaculte.php");
	array_push($sideMenuImg, "black-arrow1.gif");

	$newuser = ($is_eclass_unique==1)?'newuser.php':'newuser_info.php';
	$newprof = ($is_eclass_unique==1)?'newprof.php':'newprof_info.php';

	array_push($sideMenuText, $GLOBALS['langNewUser']);
	array_push($sideMenuLink, $urlServer."modules/auth/registration.php");
	array_push($sideMenuImg, "black-arrow1.gif");
	array_push($sideMenuText, $GLOBALS['langManuals']);
	array_push($sideMenuLink, $urlServer."manuals/manual.php");
	array_push($sideMenuImg, "black-arrow1.gif");
	array_push($sideMenuText, $GLOBALS['langInfoPlat']);
	array_push($sideMenuLink, $urlServer."info/about.php");
	array_push($sideMenuImg, "black-arrow1.gif");
	array_push($sideMenuText, $GLOBALS['langContact']);
	array_push($sideMenuLink, $urlServer."info/contact.php");
	array_push($sideMenuImg, "black-arrow1.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);

	array_push($sideMenuGroup, $sideMenuSubGroup);
	return $sideMenuGroup;
}

function adminMenu(){

	global $webDir, $urlAppend, $language, $phpSysInfoURL, $phpMyAdminURL;
	global $siteName, $is_admin, $urlServer, $mysqlMainDb, $close_user_registration;

	/* Check for LDAP server entries */
	$ldap_entries = mysql_fetch_array(db_query("SELECT ldapserver FROM institution",$mysqlMainDb));
	if ($ldap_entries['ldapserver'] <> NULL)
	$newuser = "newprof_info.php";
	else
	$newuser = "newprof.php";

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg	= array();

	//user administration
	$arrMenuType = array();
	$arrMenuType['type'] = 'text';
	$arrMenuType['text'] = $GLOBALS['langAdminUsers'];
	array_push($sideMenuSubGroup, $arrMenuType);

	array_push($sideMenuText, $GLOBALS['langProfReg']);
	array_push($sideMenuLink, "../auth/newprofadmin.php");
	array_push($sideMenuImg, "register_prof.gif");
	array_push($sideMenuText, $GLOBALS['langProfOpen']);
	array_push($sideMenuLink, "../admin/listreq.php");
	array_push($sideMenuImg, "open_prof.gif");
	array_push($sideMenuText, $GLOBALS['langInfoMail']);
	array_push($sideMenuLink, "../admin/mailtoprof.php");
	array_push($sideMenuImg, "email_prof.gif");

	// check for close user registration
	if (isset($close_user_registration) and $close_user_registration == TRUE) {
		array_push($sideMenuText, $GLOBALS['langUserOpen']);
		array_push($sideMenuLink, "../admin/listrequsers.php");
		array_push($sideMenuImg, "register_prof.gif");
	}
	array_push($sideMenuText, $GLOBALS['langListUsersActions']);
	array_push($sideMenuLink, "../admin/listusers.php");
	array_push($sideMenuImg, "user_list.gif");
	array_push($sideMenuText, $GLOBALS['langSearchUser']);
	array_push($sideMenuLink, "../admin/search_user.php");
	array_push($sideMenuImg, "user_search.gif");
	array_push($sideMenuText, $GLOBALS['langAddAdminInApache']);
	array_push($sideMenuLink, "../admin/addadmin.php");
	array_push($sideMenuImg, "user_add_admin.gif");
	array_push($sideMenuText, $GLOBALS['langUserAuthentication']);
	array_push($sideMenuLink, "../admin/auth.php");
	array_push($sideMenuImg, "user_auth.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	//lesson administration
	//reset sub-arrays so that we do not have duplicate entries
	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg	= array();

	$arrMenuType = array();
	$arrMenuType['type'] = 'text';
	$arrMenuType['text'] = $GLOBALS['langAdminCours'];
	array_push($sideMenuSubGroup, $arrMenuType);

	array_push($sideMenuText, $GLOBALS['langListCours']);
	array_push($sideMenuLink, "../admin/listcours.php");
	array_push($sideMenuImg, "lessons_list.gif");
	array_push($sideMenuText, $GLOBALS['langSearchCourses']);
	array_push($sideMenuLink, "../admin/searchcours.php");
	array_push($sideMenuImg, "lessons_search.gif");
	array_push($sideMenuText, $GLOBALS['langRestoreCourse']);
	array_push($sideMenuLink, "../course_info/restore_course.php");
	array_push($sideMenuImg, "lesson_recovery.gif");
	array_push($sideMenuText, $GLOBALS['langSpeeSubscribe']);
	array_push($sideMenuLink, "../admin/speedSubscribe.php");
	array_push($sideMenuImg, "quick_reg.gif");
	array_push($sideMenuText, $GLOBALS['langListFaculte']);
	array_push($sideMenuLink, "../admin/addfaculte.php");
	array_push($sideMenuImg, "schools_list.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	//server administration
	//reset sub-arrays so that we do not have duplicate entries
	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg	= array();

	$arrMenuType = array();
	$arrMenuType['type'] = 'text';
	$arrMenuType['text'] = $GLOBALS['langState'];
	array_push($sideMenuSubGroup, $arrMenuType);
	array_push($sideMenuText, $GLOBALS['langCleanUp']);
  array_push($sideMenuLink, "../admin/cleanup.php");
  array_push($sideMenuImg, "clean.gif");

	if (isset($phpSysInfoURL) && PHP_OS!="WIN32" && PHP_OS!="WINNT") {
		array_push($sideMenuText, $GLOBALS['langSysInfo']);
		array_push($sideMenuLink, $phpSysInfoURL);
		array_push($sideMenuImg, "system_info.gif");
	}
	array_push($sideMenuText, $GLOBALS['langPHPInfo']);
	array_push($sideMenuLink, "../admin/phpInfo.php?to=phpinfo");
	array_push($sideMenuImg, "php_info.gif");

	if (isset($phpMyAdminURL)){
		array_push($sideMenuText, $GLOBALS['langDBaseAdmin']);
		array_push($sideMenuLink, $phpMyAdminURL);
		array_push($sideMenuImg, "db_admin.gif");
	}
	array_push($sideMenuText, $GLOBALS['langUpgradeBase']);
	array_push($sideMenuLink, "../admin/upgrade.php");
	array_push($sideMenuImg, "db_upgrade.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	//other tools
	//reset sub-arrays so that we do not have duplicate entries
	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg	= array();

	$arrMenuType = array();
	$arrMenuType['type'] = 'text';
	$arrMenuType['text'] = $GLOBALS['langGenAdmin'];
	array_push($sideMenuSubGroup, $arrMenuType);

	array_push($sideMenuText, $GLOBALS['langAdminAn']);
	array_push($sideMenuLink, "../admin/adminannouncements.php");
	array_push($sideMenuImg, "announcements.gif");
	array_push($sideMenuText, $GLOBALS['langVersion']);
	array_push($sideMenuLink, "../admin/about.php");
	array_push($sideMenuImg, "eclass_version.gif");
	array_push($sideMenuText, $GLOBALS['langConfigFile']);
	array_push($sideMenuLink, "../admin/eclassconf.php");
	array_push($sideMenuImg, "config_file.gif");
	array_push($sideMenuText, $GLOBALS['langPlatformStats']);
	array_push($sideMenuLink, "../admin/stateclass.php");
	array_push($sideMenuImg, "platform_stats.gif");
	array_push($sideMenuText, $GLOBALS['langAdminManual']);
	array_push($sideMenuLink, $urlServer . "manuals/manA/ManA.pdf");
	array_push($sideMenuImg, "administrator_manual.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	return $sideMenuGroup;
}


/**
 * Function lessonToolsMenu
 *
 * Creates a multi-dimensional array of the user's tools
 * in regard to the user's user level
 * (student | professor | platform administrator)
 *
 * @return array
 */
function lessonToolsMenu(){
	global $is_admin, $is_adminOfCourse, $uid, $mysqlMainDb;
	global $webDir, $language;

	$sideMenuGroup = array();

	//	------------------------------------------------------------------
	//	Get public tools
	//	------------------------------------------------------------------
	$result = getToolsArray('Public');

	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg = array();
	$sideMenuID = array();


	$arrMenuType = array();
	if ($is_adminOfCourse) {
		$arrMenuType['type'] = 'text';
		$arrMenuType['text'] = $GLOBALS['langActiveTools'];
	} else  {
		$arrMenuType['type'] = 'none';
		$arrMenuType['text'] = 'none';
	}
	array_push($sideMenuSubGroup, $arrMenuType);

	while ($toolsRow = mysql_fetch_array($result)) {
		if(!defined($toolsRow["define_var"])) define($toolsRow["define_var"], $toolsRow["id"]);

		array_push($sideMenuText, $toolsRow["rubrique"]);
		array_push($sideMenuLink, $toolsRow["lien"]);
		array_push($sideMenuImg, $toolsRow["image"]."_on.gif");
		array_push($sideMenuID, $toolsRow["id"]);
	}

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuSubGroup, $sideMenuID);
	array_push($sideMenuGroup, $sideMenuSubGroup);
	//	------------------------------------------------------------------
	//	END of Get public tools
	//	------------------------------------------------------------------

	//	------------------------------------------------------------------
	//	Get professor's tools
	//	------------------------------------------------------------------

	$res2 = db_query("SELECT statut FROM user WHERE user_id = '$uid'",$mysqlMainDb);

	if ($row = mysql_fetch_row($res2)) $statut = $row[0];

	if ($is_adminOfCourse) {
		//get inactive tools
		$result= getToolsArray('PublicButHide');

		$sideMenuSubGroup = array();
		$sideMenuText = array();
		$sideMenuLink = array();
		$sideMenuImg = array();
		$sideMenuID = array();

		$arrMenuType = array();
		$arrMenuType['type'] = 'text';
		$arrMenuType['text'] = $GLOBALS['langInactiveTools'];
		array_push($sideMenuSubGroup, $arrMenuType);

		while ($toolsRow = mysql_fetch_array($result)) {
			if(!defined($toolsRow["define_var"])) {
				define($toolsRow["define_var"], $toolsRow["id"]);
			}

			array_push($sideMenuText, $toolsRow["rubrique"]);
			array_push($sideMenuLink, $toolsRow["lien"]);
			array_push($sideMenuImg, $toolsRow["image"]."_off.gif");
			array_push($sideMenuID, $toolsRow["id"]);
		}

		array_push($sideMenuSubGroup, $sideMenuText);
		array_push($sideMenuSubGroup, $sideMenuLink);
		array_push($sideMenuSubGroup, $sideMenuImg);
		array_push($sideMenuSubGroup, $sideMenuID);
		array_push($sideMenuGroup, $sideMenuSubGroup);

		//get course administration tools
		$result= getToolsArray('courseAdmin');

		$sideMenuSubGroup = array();
		$sideMenuText = array();
		$sideMenuLink = array();
		$sideMenuImg = array();
		$sideMenuID = array();

		$arrMenuType = array();
		$arrMenuType['type'] = 'text';
		$arrMenuType['text'] = $GLOBALS['langAdministrationTools'];
		array_push($sideMenuSubGroup, $arrMenuType);
		while ($toolsRow = mysql_fetch_array($result)) {

			if(!defined($toolsRow["define_var"])) define($toolsRow["define_var"], $toolsRow["id"]);

			array_push($sideMenuText, $toolsRow["rubrique"]);
			array_push($sideMenuLink, $toolsRow["lien"]);
			array_push($sideMenuImg, $toolsRow["image"]."_on.gif");
			array_push($sideMenuID, $toolsRow["id"]);
		}

		array_push($sideMenuSubGroup, $sideMenuText);
		array_push($sideMenuSubGroup, $sideMenuLink);
		array_push($sideMenuSubGroup, $sideMenuImg);
		array_push($sideMenuSubGroup, $sideMenuID);
		array_push($sideMenuGroup, $sideMenuSubGroup);

	}
	//	------------------------------------------------------------------
	//	END of Get professor's tools
	//	------------------------------------------------------------------

	//	------------------------------------------------------------------
	//	Get platform administrator tools
	//	------------------------------------------------------------------
	/*if (isset($is_admin) and $is_admin) {

	$result= getToolsArray('claroAdmin');

	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuID = array();

	array_push($sideMenuSubGroup, $langAdministratorTools);
	while ($toolsRow = mysql_fetch_array($result)) {

	define($toolsRow["define_var"], $toolsRow["id"]);

	array_push($sideMenuText, $toolsRow["rubrique"]);
	array_push($sideMenuLink, $toolsRow["lien"]);
	array_push($sideMenuID, $toolsRow["id"]);
	}

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuID);
	array_push($sideMenuGroup, $sideMenuSubGroup);
	}*/
	//	------------------------------------------------------------------
	//	End of Get professor's tools
	//	------------------------------------------------------------------

	return $sideMenuGroup;
}

?>

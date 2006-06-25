<?PHP

/**
 * tools functions
 *
 * Responsible to generate the tools of each tool
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @package eclass 2.0
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
 * function getToolsArray
 *
 * Queries the database for tool information in accordance
 * to the parameter passed.
 *
 * @param string $cat
 * @return mysql_resource
 * @see function lessonToolsMenu
 */
function getToolsArray($cat) {
	global $currentCourse, $currentCourseID;
	$currentCourse = $currentCourseID;
	
	switch ($cat) {
		case 'Public':

			if (isset($_SESSION['uid']) and $_SESSION['uid']) {
				$result = db_query("
                    select * from accueil
                    where visible=1
                    ORDER BY id", $currentCourse);
			} else {
				$result = db_query("
                    select * from accueil
                    where visible=1 AND lien NOT LIKE '%/user.php'
                    ORDER BY id", $currentCourse);
			}

			break;
		case 'PublicButHide':

			$result = db_query("
                    select *
                    from accueil
                    where visible=0
                    and admin=0
                    ORDER BY id", $currentCourse);
			break;
		case 'courseAdmin':
			$result = db_query("
                    select *
                    from accueil
                    where admin=1
                    ORDER BY id", $currentCourse);
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
 * function loggedInMenu
 *
 * Creates a multi-dimensional array of the user's tools
 * when the user is signed in, and not at a lesson specific tool,
 * in regard to the user's user level.
 *
 * (student | professor | platform administrator)
 *
 * @return unknown
 */
function loggedInMenu(){
	global $webDir, $language, $uid, $is_admin, $urlServer, $mysqlMainDb;

	include("$webDir/modules/lang/$language/index.inc");

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();
	//the logic of this function was taken from the main index.php file of eclass
	array_push($sideMenuSubGroup, $langMenu);

	// User is not currently in a course - set statut from main database

	// $res2 = db_query("SELECT statut FROM user WHERE user_id = '$uid'",$mysqlMainDb);

	//	$res2 = db_query("SELECT statut FROM user WHERE user_id = '$uid'", $mysqlMainDb);


	$res2 = db_query("SELECT statut FROM user WHERE user_id = '$uid'",$mysqlMainDb);
	//	$res2 = db_query("SELECT statut FROM user WHERE user_id = '$uid'");

	if ($row = mysql_fetch_row($res2)) $statut = $row[0];

	if ($statut==1) {
		array_push($sideMenuText, $langCourseCreate);
		array_push($sideMenuLink, $urlServer . "modules/create_course/create_course.php");
		array_push($sideMenuImg, "create_lesson.gif");
	}

	if (isset($is_admin) and $is_admin) {
		array_push($sideMenuText, $langAdminTool);
		array_push($sideMenuLink, $urlServer . "modules/admin/");
		array_push($sideMenuImg, "admin-tools.gif");
	}

	if ($statut != 10) {
		//		echo $urlServer . "modules/auth/courses.php<br>";
		//		echo $urlServer;
		array_push($sideMenuText, $langOtherCourses);
		array_push($sideMenuLink, $urlServer . "modules/auth/courses.php");
		array_push($sideMenuImg, "enroll.gif");

		array_push($sideMenuText, $langMyAgenda);
		array_push($sideMenuLink, $urlServer . "modules/agenda/myagenda.php");
		array_push($sideMenuImg, "calendar.gif");

		array_push($sideMenuText, $langMyAnnouncements);
		array_push($sideMenuLink, $urlServer . "modules/announcements/myannouncements.php");
		array_push($sideMenuImg, "announcements.gif");

		array_push($sideMenuText, $langModifyProfile);
		array_push($sideMenuLink, $urlServer . "modules/profile/profile.php");
		array_push($sideMenuImg, "profile.gif");

		array_push($sideMenuText, $langSearch);
		array_push($sideMenuLink, $urlServer."modules/search/search.php");
		array_push($sideMenuImg, "search.gif");

	}

	//	array_push($sideMenuText, $langHelp);
	//	array_push($sideMenuLink, $urlServer . "\modules/help/help.php?topic=Clar2\"
	//		onClick=\"window.open('modules/help/help.php?topic=Clar2','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10');
	//		return false;");

	//	array_push($sideMenuText, $langLogout);
	//	array_push($sideMenuLink, $_SERVER['PHP_SELF'] . "?logout=yes");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	return $sideMenuGroup;
}

/**
 * function loggedOutMenu
 *
 * Creates a multi-dimensional array of the user's tools/links
 * for the menu presented when the user is not logged in.
 * *
 * @return array
 */
function loggedOutMenu(){

	global $webDir, $language, $urlServer, $auth;

	include("$webDir/modules/lang/$language/index.inc");

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	array_push($sideMenuSubGroup, $langMenu);

	array_push($sideMenuText, $langListFaculte);
	array_push($sideMenuLink, $urlServer."modules/auth/listfaculte.php");
	array_push($sideMenuImg, "faculte.gif");

	/* Check for LDAP server entries */
	$ldap_entries = mysql_fetch_array(mysql_query("SELECT ldapserver FROM institution"));
	if ($ldap_entries['ldapserver'] <> NULL) $newuser = "newuser_info.php";
	else $newuser = "newuser.php";
	// end of checking

	$newuser = ($auth==1)?'newuser.php':'newuser_info.php';
	$newprof = ($auth==1)?'newprof.php':'newprof_info.php';

	array_push($sideMenuText, $langNewUser);
	array_push($sideMenuLink, $urlServer."modules/auth/$newuser");
	array_push($sideMenuImg, "user_reg.gif");
	array_push($sideMenuText, $langProfReq);
	array_push($sideMenuLink, $urlServer."modules/auth/$newprof");
	array_push($sideMenuImg, "prof_reg.gif");
	array_push($sideMenuText, $langManuals);
	array_push($sideMenuLink, $urlServer."manuals/manual.php");
	array_push($sideMenuImg, "manual.gif");
	array_push($sideMenuText, $langInfoPlat);
	array_push($sideMenuLink, $urlServer."info/about.php");
	array_push($sideMenuImg, "plat_id.gif");
	array_push($sideMenuText, $langSupportForum);
	array_push($sideMenuLink, "http://eclass.gunet.gr/teledu/index.htm");
	array_push($sideMenuImg, "support.gif");
	array_push($sideMenuText, $langContact);
	array_push($sideMenuLink, $urlServer."info/contact.php");
	array_push($sideMenuImg, "contact.gif");
	array_push($sideMenuText, $langSearch);
	array_push($sideMenuLink, $urlServer."modules/search/search.php");
	array_push($sideMenuImg, "search.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);

	array_push($sideMenuGroup, $sideMenuSubGroup);
	return $sideMenuGroup;
}

function adminMenu(){

	global $webDir, $urlAppend, $language, $phpSysInfoURL, $phpMyAdminURL;
	global $siteName, $is_admin, $urlServer, $mysqlMainDb;

	include($webDir."modules/lang/$language/admin.inc.php");
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

	//professor administration
	array_push($sideMenuSubGroup, $langAdminProf);

	array_push($sideMenuText, $langProfReg);
	//array_push($sideMenuLink, "../auth/" . $newuser);
	array_push($sideMenuLink, "../auth/newprofadmin.php");
	array_push($sideMenuImg, "register_prof.gif");
	array_push($sideMenuText, $langProfOpen);
	array_push($sideMenuLink, "../admin/listreq.php");
	array_push($sideMenuImg, "open_prof.gif");
	array_push($sideMenuText, $langInfoMail);
	array_push($sideMenuLink, "../admin/mailtoprof.php");
	array_push($sideMenuImg, "email_prof.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);


	//user administration
	//reset sub-arrays so that we do not have duplicate entries
	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg	= array();

	array_push($sideMenuSubGroup, $langAdminUsers);

	array_push($sideMenuText, $langListUsers);
	array_push($sideMenuLink, "../admin/listusers.php");
	array_push($sideMenuImg, "user_list.gif");
	array_push($sideMenuText, $langSearchUser);
	array_push($sideMenuLink, "../admin/search_user.php");
	array_push($sideMenuImg, "user_search.gif");
	array_push($sideMenuText, $langAddAdminInApache);
	array_push($sideMenuLink, "../admin/addadmin.php");
	array_push($sideMenuImg, "user_add_admin.gif");
	array_push($sideMenuText, "Πιστοποίηση Χρηστών");
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

	array_push($sideMenuSubGroup, $langAdminCours);

	array_push($sideMenuText, $langListCours);
	array_push($sideMenuLink, "../admin/listcours.php");
	array_push($sideMenuImg, "lessons_list.gif");
	// Added by vagpits
	array_push($sideMenuText, "Αναζήτηση Μαθημάτων");
	array_push($sideMenuLink, "../admin/searchcours.php");
	array_push($sideMenuImg, "lessons_search.gif");
	// End
	array_push($sideMenuText, $langRestoreCourse);
	array_push($sideMenuLink, "../course_info/restore_course.php");
	array_push($sideMenuImg, "lesson_recovery.gif");
	array_push($sideMenuText, $langSpeeSubscribe);
	array_push($sideMenuLink, "../admin/speedSubscribe.php");
	array_push($sideMenuImg, "quick_reg.gif");
	array_push($sideMenuText, $langListFaculte);
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

	array_push($sideMenuSubGroup, $langState);

	if (isset($phpSysInfoURL)&&PHP_OS!="WIN32"&&PHP_OS!="WINNT") {
		array_push($sideMenuText, $langSysInfo);
		array_push($sideMenuLink, $phpSysInfoURL);
		array_push($sideMenuImg, "system_info.gif");
	}
	array_push($sideMenuText, $langPHPInfo);
	array_push($sideMenuLink, "../admin/phpInfo.php?to=phpinfo");
	array_push($sideMenuImg, "php_info.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	//mysql administration
	//reset sub-arrays so that we do not have duplicate entries
	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg	= array();

	array_push($sideMenuSubGroup, $langDevAdmin);

	if (isset($phpMyAdminURL)){
		array_push($sideMenuText, $langDBaseAdmin);
		array_push($sideMenuLink, $phpMyAdminURL);
		array_push($sideMenuImg, "db_admin.gif");
	}
	// Added by vagpits
	array_push($sideMenuText, "Αναβάθμιση Βάσης Δεδομένων");
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

	array_push($sideMenuSubGroup, $langGenAdmin);

	array_push($sideMenuText, $langVersion);
	array_push($sideMenuLink, "../admin/about.php");
	array_push($sideMenuImg, "eclass_version.gif");
	array_push($sideMenuText, $langConfigFile);
	// Changed by vagpits
	//array_push($sideMenuLink, "phpInfo.php?to=clarconf");
	array_push($sideMenuLink, "../admin/clarconf.php");
	array_push($sideMenuImg, "config_file.gif");
	// End
	array_push($sideMenuText, $siteName);
	array_push($sideMenuLink, "../admin/statClaro.php");
	array_push($sideMenuImg, "stat_claro.gif"); // image file does not exist! what does this tool do ?
	if (isset($phpMyAdminURL)){
		array_push($sideMenuText, $langLogIdentLogout);
		array_push($sideMenuLink, $phpMyAdminURL."sql.php?db=".$mysqlMainDb."&table=loginout&goto=db_details.php&sql_query=SELECT+%2A+FROM+%60loginout%60&pos=0");
		array_push($sideMenuImg, "logs.gif");
	}

	array_push($sideMenuText, $langPlatformStats);
	array_push($sideMenuLink, "../admin/platformStats.php");
	array_push($sideMenuImg, "platform_stats.gif");

	array_push($sideMenuText, $langManuals);
	array_push($sideMenuLink, $urlServer . "manuals/manual.php");
	array_push($sideMenuImg, "available_manuals.gif");
	array_push($sideMenuText, $langAdminManual);
	array_push($sideMenuLink, $urlServer . "manuals/manA/admin.txt");
	array_push($sideMenuImg, "administrator_manual.gif");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	return $sideMenuGroup;
}


/**
 * function lessonToolsMenu
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
	include($webDir."modules/lang/$language/lessonTools.inc.php");
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
	if($is_adminOfCourse) array_push($sideMenuSubGroup, $langActiveTools);
	else array_push($sideMenuSubGroup, $langTools);
	//	define('MODULE_ID_AGENDA', 4);
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

	//	if (@$statut==1 || $is_adminOfCourse) {
	if ($is_adminOfCourse) {
		//		$courseAdminTools = getToolsArray('courseAdmin');
		//		$hiddenTools = getToolsArray('PublicButHide');

		//get course administration tools
		$result= getToolsArray('courseAdmin');

		$sideMenuSubGroup = array();
		$sideMenuText = array();
		$sideMenuLink = array();
		$sideMenuImg = array();
		$sideMenuID = array();

		array_push($sideMenuSubGroup, $langAdministrationTools);//TODO: add lang
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
		//get inactive tools
		$result= getToolsArray('PublicButHide');

		$sideMenuSubGroup = array();
		$sideMenuText = array();
		$sideMenuLink = array();
		$sideMenuImg = array();
		$sideMenuID = array();


		array_push($sideMenuSubGroup, $langInactiveTools);//TODO: add lang

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
	}
	//	------------------------------------------------------------------
	//	END of Get professor's tools
	//	------------------------------------------------------------------

	//	------------------------------------------------------------------
	//	Get platform administrator tools
	//	------------------------------------------------------------------
	if (isset($is_admin) and $is_admin) {
		//		$adminTools = getToolsArray('claroAdmin');
		$result= getToolsArray('claroAdmin');

		$sideMenuSubGroup = array();
		$sideMenuText = array();
		$sideMenuLink = array();
		$sideMenuID = array();

		array_push($sideMenuSubGroup, $langAdministratorTools);//TODO: add lang
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
	}
	//	------------------------------------------------------------------
	//	End of Get professor's tools
	//	------------------------------------------------------------------

	//	array_push($sideMenuGroup, $sideMenuSubGroup);
	//echo "<br><br>count is " . count($sideMenuGroup[0][1], COUNT_RECURSIVE);
	return $sideMenuGroup;
}




?>

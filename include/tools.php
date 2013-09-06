<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/*
 * Tool Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates an array of the tools that are displayed on the left
 * side column .
 *
 */

/*
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

		case 1: { //logged in
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

		case 4: { // custom tools
			$menu = customMenu();
			break;
		}
                
		case 5: { // tools when embedded in tinymce
			$menu = pickerMenu();
			break;
		}
                    
	}
	return $menu;
}


/*
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
					$result = db_query("SELECT * FROM accueil
                                                        WHERE visible=1
                                                        ORDER BY rubrique", $currentCourse);
				} else {
					$result = db_query("SELECT * FROM accueil
                                        WHERE visible=1 AND lien NOT LIKE '%/user.php'
                                        AND lien NOT LIKE '%/conference/conference.php'
                                        AND lien NOT LIKE '%/work/work.php'
                                            AND lien NOT LIKE '%/dropbox/index.php'
                                            AND lien NOT LIKE '%/questionnaire/questionnaire.php'
                                            AND lien NOT LIKE '%/phpbb/index.php'
                                            AND lien NOT LIKE '%/learnPath/learningPathList.php'
                                            AND lien NOT LIKE '%/group/group.php'
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
	}

	return $result;

}


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
	global $webDir, $language, $uid, $is_admin, $is_power_user, $is_usermanage_user, 
                $urlServer, $mysqlMainDb;

	$sideMenuGroup = array();

	if ((isset($is_admin) and $is_admin) or (isset($is_power_user) and $is_power_user)
                or (isset($is_usermanage_user) and ($is_usermanage_user))) {
		$sideMenuSubGroup = array();
		$sideMenuText = array();
		$sideMenuLink = array();
		$sideMenuImg	= array();
	
		$arrMenuType = array();
		$arrMenuType['type'] = 'text';
		$arrMenuType['text'] = $GLOBALS['langAdminOptions'];
		array_push($sideMenuSubGroup, $arrMenuType);
	
		array_push($sideMenuText, "<b style=\"color:#a33033;\">$GLOBALS[langAdminTool]</b>");
		array_push($sideMenuLink, $urlServer . "modules/admin/");
		array_push($sideMenuImg, "arrow.png");
		
		array_push($sideMenuSubGroup, $sideMenuText);
		array_push($sideMenuSubGroup, $sideMenuLink);
		array_push($sideMenuSubGroup, $sideMenuImg);
		array_push($sideMenuGroup, $sideMenuSubGroup);
	}

	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg = array();

	$arrMenuType = array();
	$arrMenuType['type'] = 'text';
	$arrMenuType['text'] = $GLOBALS['langBasicOptions'];
	array_push($sideMenuSubGroup, $arrMenuType);

	array_push($sideMenuText, $GLOBALS['langListCourses']);
	array_push($sideMenuLink, $urlServer."modules/auth/courses.php");
	array_push($sideMenuImg, "arrow.png");

	array_push($sideMenuText, $GLOBALS['langManuals']);
	array_push($sideMenuLink, $urlServer."manuals/manual.php");
	array_push($sideMenuImg, "arrow.png");
	
	array_push($sideMenuText, $GLOBALS['langPlatformIdentity']);
	array_push($sideMenuLink, $urlServer."info/about.php");
	array_push($sideMenuImg, "arrow.png");

	array_push($sideMenuText, $GLOBALS['langContact']);
	array_push($sideMenuLink, $urlServer."info/contact.php");
	array_push($sideMenuImg, "arrow.png");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);
	
	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	$arrMenuType = array();
	$arrMenuType['type'] = 'text';
	$arrMenuType['text'] = $GLOBALS['langUserOptions'];

	array_push($sideMenuSubGroup, $arrMenuType);

	$res2 = db_query("SELECT statut FROM user WHERE user_id = '$uid'",$mysqlMainDb);

	if ($row = mysql_fetch_row($res2)) $statut = $row[0];

	if (isset($statut) and ($statut == 1)) {
		array_push($sideMenuText, $GLOBALS['langCourseCreate']);
		array_push($sideMenuLink, $urlServer . "modules/create_course/create_course.php");
		array_push($sideMenuImg, "arrow.png");
	}

	array_push($sideMenuText, $GLOBALS['langMyAgenda']);
	array_push($sideMenuLink, $urlServer . "modules/agenda/myagenda.php");
	array_push($sideMenuImg, "arrow.png");

	array_push($sideMenuText, $GLOBALS['langModifyProfile']);
	array_push($sideMenuLink, $urlServer . "modules/profile/profile.php");
	array_push($sideMenuImg, "arrow.png");

	array_push($sideMenuText, $GLOBALS['langMyStats']);
	array_push($sideMenuLink, $urlServer . "modules/profile/personal_stats.php");
	array_push($sideMenuImg, "arrow.png");
	
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
	$arrMenuType['type'] = 'text';
	$arrMenuType['text'] = $GLOBALS['langBasicOptions'];
	array_push($sideMenuSubGroup, $arrMenuType);
	
	array_push($sideMenuText, $GLOBALS['langListCourses']);
	array_push($sideMenuLink, $urlServer."modules/auth/listfaculte.php");
	array_push($sideMenuImg, "arrow.png");
        if (get_config('course_metadata')) {
            $res = db_query("SELECT cours_id, code FROM cours", $mysqlMainDb);
            while ($course = mysql_fetch_assoc($res)) {
                if (CourseXMLElement::isCertified($course['cours_id'], $course['code'])) {
                    array_push($sideMenuText, $GLOBALS['langListOpenCourses']);
                    array_push($sideMenuLink, $urlServer."modules/course_metadata/openfaculties.php");
                    array_push($sideMenuImg, "arrow.png");
                    break;
                }
            }
        }
        if (get_config('user_registration')) {
                array_push($sideMenuText, $GLOBALS['langNewUser']);
                array_push($sideMenuLink, $urlServer."modules/auth/registration.php");
                array_push($sideMenuImg, "arrow.png");
        }	
	array_push($sideMenuText, $GLOBALS['langManuals']);
	array_push($sideMenuLink, $urlServer."manuals/manual.php");
	array_push($sideMenuImg, "arrow.png");
	array_push($sideMenuText, $GLOBALS['langPlatformIdentity']);
	array_push($sideMenuLink, $urlServer."info/about.php");
	array_push($sideMenuImg, "arrow.png");
	array_push($sideMenuText, $GLOBALS['langContact']);
	array_push($sideMenuLink, $urlServer."info/contact.php");
	array_push($sideMenuImg, "arrow.png");

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);

	array_push($sideMenuGroup, $sideMenuSubGroup);
	return $sideMenuGroup;
}

function adminMenu(){

	global $webDir, $urlAppend, $language, $phpSysInfoURL, $phpMyAdminURL;
	global $siteName, $urlServer, $mysqlMainDb;
        global $is_admin, $is_power_user;

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
	array_push($sideMenuText, $GLOBALS['langSearchUser']);
	array_push($sideMenuLink, "../admin/search_user.php");
	array_push($sideMenuImg, "arrow.png");

	array_push($sideMenuText, $GLOBALS['langProfOpen']);
	array_push($sideMenuLink, "../admin/listreq.php");
	array_push($sideMenuImg, "arrow.png");
                	
	if ((get_config('eclass_stud_reg') == 1) or get_config('alt_auth_stud_reg') == 0) {
		array_push($sideMenuText, $GLOBALS['langUserOpen']);
		array_push($sideMenuLink, "../admin/listreq.php?type=user");
		array_push($sideMenuImg, "arrow.png");
	} else {
                array_push($sideMenuText, $GLOBALS['langUserDetails']);
                array_push($sideMenuLink, "../admin/newuseradmin.php?type=user");
                array_push($sideMenuImg, "arrow.png");
        }
	
        if (isset($is_admin) and $is_admin) {
                array_push($sideMenuText, $GLOBALS['langUserAuthentication']);
                array_push($sideMenuLink, "../admin/auth.php");
                array_push($sideMenuImg, "arrow.png");
                array_push($sideMenuText, $GLOBALS['langMailVerification']);
                array_push($sideMenuLink, "../admin/mail_ver_settings.php");
                array_push($sideMenuImg, "arrow.png");
                array_push($sideMenuText, $GLOBALS['langChangeUser']);
                array_push($sideMenuLink, "../admin/change_user.php");
                array_push($sideMenuImg, "arrow.png");    
        }	
	array_push($sideMenuText, $GLOBALS['langMultiRegUser']);
	array_push($sideMenuLink, "../admin/multireguser.php");
	array_push($sideMenuImg, "arrow.png");
        array_push($sideMenuText, $GLOBALS['langMultiCourseUser']);
	array_push($sideMenuLink, "../admin/multicourseuser.php");
	array_push($sideMenuImg, "arrow.png");
	array_push($sideMenuText, $GLOBALS['langMultiDelUser']);
	array_push($sideMenuLink, "../admin/multideluser.php");
	array_push($sideMenuImg, "arrow.png");
	array_push($sideMenuText, $GLOBALS['langInfoMail']);
	array_push($sideMenuLink, "../admin/mailtoprof.php");
	array_push($sideMenuImg, "arrow.png");
        if (isset($is_admin) and $is_admin) {
            array_push($sideMenuText, $GLOBALS['langAdmins']);
            array_push($sideMenuLink, "../admin/addadmin.php");
            array_push($sideMenuImg, "arrow.png");    
        }        
	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);
        
        if (isset($is_power_user) and $is_power_user) {
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
                array_push($sideMenuImg, "arrow.png");
                array_push($sideMenuText, $GLOBALS['langRestoreCourse']);
                array_push($sideMenuLink, "../course_info/restore_course.php");
                array_push($sideMenuImg, "arrow.png");
                array_push($sideMenuText, $GLOBALS['langListFaculte']);
                array_push($sideMenuLink, "../admin/addfaculte.php");
                array_push($sideMenuImg, "arrow.png");
                array_push($sideMenuText, $GLOBALS['langMultiCourse']);
                array_push($sideMenuLink, "../admin/multicourse.php");
                // check if we have betacms enabled
                if (get_config('betacms') == TRUE) {
                        array_push($sideMenuImg, "arrow.png");
                        array_push($sideMenuText, $GLOBALS['langBrowseBCMSRepo']);
                        array_push($sideMenuLink, "../betacms_bridge/browserepo.php");
                }
                array_push($sideMenuImg, "arrow.png");
                array_push($sideMenuSubGroup, $sideMenuText);
                array_push($sideMenuSubGroup, $sideMenuLink);
                array_push($sideMenuSubGroup, $sideMenuImg);
                array_push($sideMenuGroup, $sideMenuSubGroup);
        }

	//server administration
	//reset sub-arrays so that we do not have duplicate entries
	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg	= array();

        if (isset($is_admin) and $is_admin) {
                $arrMenuType = array();
                $arrMenuType['type'] = 'text';
                $arrMenuType['text'] = $GLOBALS['langState'];
                array_push($sideMenuSubGroup, $arrMenuType);
                array_push($sideMenuText, $GLOBALS['langCleanUp']);
                array_push($sideMenuLink, "../admin/cleanup.php");
                array_push($sideMenuImg, "arrow.png");

                if (isset($phpSysInfoURL) && PHP_OS == "Linux") {
                    array_push($sideMenuText, $GLOBALS['langSysInfo']);
                    array_push($sideMenuLink, $phpSysInfoURL);
                    array_push($sideMenuImg, "arrow.png");
                }
                array_push($sideMenuText, $GLOBALS['langPHPInfo']);
                array_push($sideMenuLink, "../admin/phpInfo.php");
                array_push($sideMenuImg, "arrow.png");

                if (isset($phpMyAdminURL) and $phpMyAdminURL){
                    array_push($sideMenuText, $GLOBALS['langDBaseAdmin']);
                    array_push($sideMenuLink, $phpMyAdminURL);
                    array_push($sideMenuImg, "arrow.png");
                }
                array_push($sideMenuText, $GLOBALS['langUpgradeBase']);
                array_push($sideMenuLink, $urlServer."upgrade/");
                array_push($sideMenuImg, "arrow.png");

                array_push($sideMenuSubGroup, $sideMenuText);
                array_push($sideMenuSubGroup, $sideMenuLink);
                array_push($sideMenuSubGroup, $sideMenuImg);
                array_push($sideMenuGroup, $sideMenuSubGroup);
        }        

	//other tools
	//reset sub-arrays so that we do not have duplicate entries
	$sideMenuSubGroup = array();
	$sideMenuText = array();
	$sideMenuLink = array();
	$sideMenuImg	= array();
        if (isset($is_admin) and $is_admin) {
            $arrMenuType = array();
            $arrMenuType['type'] = 'text';
            $arrMenuType['text'] = $GLOBALS['langGenAdmin'];
            array_push($sideMenuSubGroup, $arrMenuType);
            
            array_push($sideMenuText, $GLOBALS['langConfigFile']);
            array_push($sideMenuLink, "../admin/eclassconf.php");
            array_push($sideMenuImg, "arrow.png");
            array_push($sideMenuText, $GLOBALS['langPlatformStats']);
            array_push($sideMenuLink, "../admin/stateclass.php");            
            array_push($sideMenuImg, "arrow.png");
            if (get_config('enable_common_docs')) {
                array_push($sideMenuText, $GLOBALS['langCommonDocs']);
                array_push($sideMenuLink, "../admin/commondocs.php");
                array_push($sideMenuImg, "arrow.png");
            }
            array_push($sideMenuText, $GLOBALS['langAdminAn']);
            array_push($sideMenuLink, "../admin/adminannouncements.php");
            array_push($sideMenuImg, "arrow.png");
            array_push($sideMenuText, $GLOBALS['langAdminManual']);
            if ($language == 'greek') {
                    array_push($sideMenuLink, "http://wiki.openeclass.org/doku.php?id=el:admin_doc");
            } else {
                    array_push($sideMenuLink, "http://wiki.openeclass.org/doku.php?id=en:admin_doc");
            }
            array_push($sideMenuImg, "arrow.png");

            array_push($sideMenuSubGroup, $sideMenuText);
            array_push($sideMenuSubGroup, $sideMenuLink);
            array_push($sideMenuSubGroup, $sideMenuImg);
            array_push($sideMenuGroup, $sideMenuSubGroup);
        }

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
	global $is_editor, $uid, $mysqlMainDb, $is_course_admin;
	global $webDir, $language;
	global $currentCourseID;

	$sideMenuGroup = array();
	$sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();
	$sideMenuID = array();

        $arrMenuType = array();
        $arrMenuType['type'] = 'none';

        if ($is_editor) {
                $tools_sections = array(array('type' => 'Public',
                                              'title' => $GLOBALS['langActiveTools'],
                                              'iconext' => '_on.png'),
                                        array('type' => 'PublicButHide',
                                              'title' => $GLOBALS['langInactiveTools'],
                                              'iconext' => '_off.png'));
                if ($is_course_admin) {            
                        array_push($tools_sections, 
                                   array('type' => 'courseAdmin',
                                         'title' => $GLOBALS['langAdministrationTools'],
                                         'iconext' => '_on.png'));
                }
        } else {
                $tools_sections = array(array('type' => 'Public',
                                              'title' => $GLOBALS['langCourseOptions'],
                                              'iconext' => '_on.png'));
        }

        foreach ($tools_sections as $section) {
                $result = getToolsArray($section['type']);

                $sideMenuSubGroup = array();
                $sideMenuText = array();
                $sideMenuLink = array();
                $sideMenuImg = array();
                $sideMenuID = array();

                $arrMenuType = array('type' => 'text',
                                     'text' => $section['title']);
                array_push($sideMenuSubGroup, $arrMenuType);

                while ($toolsRow = mysql_fetch_array($result)) {
                        if(!defined($toolsRow['define_var'])) {
                                define($toolsRow['define_var'], $toolsRow['id']);
                        }

                        // Add course code only to internal links
                        if (!empty($toolsRow['define_var'])) {
                                $toolsRow['lien'] .= "?course=".$currentCourseID;
                        }

                        array_push($sideMenuText, q($toolsRow['rubrique']));
                        array_push($sideMenuLink, q($toolsRow['lien']));
                        array_push($sideMenuImg, $toolsRow['image'].$section['iconext']);
                        array_push($sideMenuID, $toolsRow['id']);
                }

                array_push($sideMenuSubGroup, $sideMenuText);
                array_push($sideMenuSubGroup, $sideMenuLink);
                array_push($sideMenuSubGroup, $sideMenuImg);
                array_push($sideMenuSubGroup, $sideMenuID);
                array_push($sideMenuGroup, $sideMenuSubGroup);
        }

	return $sideMenuGroup;
}


/**
 * Function pickerMenu
 *
 * Creates a multi-dimensional array of the user's tools/links
 * for the menu presented for the embedded theme.
 * *
 * @return array
 */
function pickerMenu() {

	global $code_cours, $cours_id, $is_editor, $urlServer, $urlAppend;
        
        $docsfilter = (isset($_REQUEST['docsfilter'])) ? '&docsfilter='. q($_REQUEST['docsfilter']) : '';
        $params = "?course=$code_cours&embedtype=tinymce". $docsfilter;
        
	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	$arrMenuType = array();
	$arrMenuType['type'] = 'text';
	$arrMenuType['text'] = $GLOBALS['langBasicOptions'];
	array_push($sideMenuSubGroup, $arrMenuType);
        
        if (isset($cours_id) and $cours_id >= 1) {
                $visible = ($is_editor) ? '' : 'AND visible = 1';
                $sql = "SELECT * FROM accueil
                         WHERE (lien LIKE '%/document.php' OR lien LIKE '%/video.php' OR lien LIKE '%/link.php')
                         $visible  ORDER BY rubrique";
                $result = db_query($sql, $code_cours);

                while($module = mysql_fetch_assoc($result))
                {
                    array_push($sideMenuText, $module['rubrique']);
                    $module['lien'] = str_replace('../../', $urlAppend.'/', $module['lien']);
                    array_push($sideMenuLink, $module['lien']. $params);
                    array_push($sideMenuImg , $module['image']."_on.png");
                }
        }
        /* link for common documents */
        if (get_config('enable_common_docs')) {
                array_push($sideMenuText, q($GLOBALS['langCommonDocs']));
                array_push($sideMenuLink, q($urlServer . 'modules/admin/commondocs.php/' .  $params));
                array_push($sideMenuImg, 'docs.png');
        }
        
        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);

        array_push($sideMenuGroup, $sideMenuSubGroup);
        //print_a($sideMenuGroup);
	return $sideMenuGroup;
}

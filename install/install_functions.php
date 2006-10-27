<?PHP
//functions for the new installer
include("../template/template.inc");
function draw($toolContent, $menuTypeID=null, $tool_css = null, $head_content = null, $body_action = null){
	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp, $langAnonUser;
	global $language, $helpTopic, $require_help, $langEclass, $langCopyrightFooter;
	global $relPath, $urlServer, $toolContent_ErrorExists;
	global $page_name, $page_navi,$currentCourseID, $siteName, $navigation;
	global $homePage, $courseHome, $uid, $webDir, $extraMessage;
	global $langChangeLang, $langUserBriefcase, $langPersonalisedBriefcase, $langAdmin, $switchLangURL;

	$messageBox = "";

	//if an error exists (ex., sessions is lost...)
	//show the error message above the normal tool content

	if (strlen($extraMessage) > 0) {
		$messageBox =  "
					<table>
				<tbody>
					<tr>
						<td class=\"extraMessage\">
						$extraMessage
					</td>
					</tr>
				</tbody>
			</table><br/>";
	}

	//get the left side menu from tools.php
		$toolArr = installerMenu();
		$numOfToolGroups = count($toolArr);

	$t = new Template();

	$t->set_file('fh', "theme.html");

	$t->set_block('fh', 'mainBlock', 'main');

	//	BEGIN constructing of left navigation
	//	----------------------------------------------------------------------
	$t->set_block('mainBlock', 'leftNavCategoryBlock', 'leftNavCategory');
	$t->set_block('leftNavCategoryBlock', 'leftNavLinkBlock', 'leftNavLink');

	if (is_array($toolArr)) {

		for($i=0; $i< $numOfToolGroups; $i++){

			$numOfTools = count($toolArr[$i][1]);

			for($j=0; $j< $numOfTools; $j++){

				$t->set_var('TOOL_LINK', $toolArr[$i][2][$j]);
				$t->set_var('TOOL_TEXT', $toolArr[$i][1][$j]);

				$t->set_var('IMG_FILE', $toolArr[$i][3][$j]);
				$t->parse('leftNavLink', 'leftNavLinkBlock', true);

			}

			$t->set_var('ACTIVE_TOOLS', $toolArr[$i][0]);
			$t->set_var('NAV_CSS_CAT_CLASS', 'category');
			$t->parse('leftNavCategory', 'leftNavCategoryBlock',true);

			$t->clear_var('leftNavLink'); //clear inner block
		}

		$t->set_var('URL_PATH',  $urlServer);

		//If there is a message to display, show it (ex. Session timeout)
		if (strlen($messageBox) > 1) {
			$t->set_var('EXTRA_MSG', $messageBox);
		}

		$t->set_var('TOOL_CONTENT', $toolContent);
		
		//show user's name and surname on the user bar
		if (session_is_registered('uid') && strlen($nom) > 0) {
			$t->set_var('LANG_USER', $langUser);
			$t->set_var('USER_NAME', $prenom);
			$t->set_var('USER_SURNAME', $nom);
		}

			$langLogout = "Bhma 1/x";
			$t->set_var('LANG_LOGOUT', $langLogout);
			$t->set_var('LOGOUT_CLASS_ICON', 'logout_icon');


		//set the text and icon on the third bar (header)
		if ($menuTypeID == 2) {
			$t->set_var('THIRD_BAR_TEXT', $intitule);
			$t->set_var('THIRDBAR_LEFT_ICON', 'lesson_icon');
		} elseif (isset($langUserBriefcase) && $menuTypeID > 0 && !session_is_registered('user_perso_active')) {
			$t->set_var('THIRD_BAR_TEXT', $langUserBriefcase);
			$t->set_var('THIRDBAR_LEFT_ICON', 'briefcase_icon');
		} elseif (isset($langPersonalisedBriefcase) && $menuTypeID > 0 && session_is_registered('user_perso_active')) {
			$t->set_var('THIRD_BAR_TEXT', $langPersonalisedBriefcase);
			$t->set_var('THIRDBAR_LEFT_ICON', 'briefcase_icon');
		} elseif ($menuTypeID == 3)  {
			$t->set_var('THIRD_BAR_TEXT', $langAdmin);
			$t->set_var('THIRDBAR_LEFT_ICON', 'admin_bar_icon');
		} else {
			$t->set_var('THIRD_BAR_TEXT', $langEclass);
		}

		$t->set_var('TOOL_NAME',  $nameTools);

		$t->set_var('LOGOUT_LINK',  $relPath);
/*
		if ($menuTypeID != 2) {
			if (session_is_registered('langswitch')) {
				$t->set_var('LANG_LOCALIZE',  $langChangeLang);
				$t->set_var('LOCALIZE_LINK',  $switchLangURL);
			} else {
				$t->set_var('LANG_LOCALIZE',  'English');
				$t->set_var('LOCALIZE_LINK',  '?localize=en');
			}
		} else {
			$t->set_var('LANG_LOCALIZE',  '');
		}*/

		//START breadcrumb AND page title

		if (!$page_navi) $page_navi = $navigation;
		if (!$page_name) $page_name = $nameTools;

		$t->set_block('mainBlock', 'breadCrumbHomeBlock', 'breadCrumbHome');

		if(!session_is_registered('uid')) $t->set_var('BREAD_TEXT',  $siteName);
		elseif(session_is_registered('uid') && session_is_registered('user_perso_active')) {
			$t->set_var('BREAD_TEXT',  $langPersonalisedBriefcase);
		} elseif(session_is_registered('uid') && !session_is_registered('user_perso_active')) {
			$t->set_var('BREAD_TEXT',  $langUserBriefcase);
		}

		$pageTitle = $siteName;
		if (!$homePage) {
			$t->set_var('BREAD_HREF_FRONT',  '<a href="{BREAD_START_LINK}">');
			$t->set_var('BREAD_START_LINK',  $urlServer);
			$t->set_var('BREAD_HREF_END',  '</a>');
		}

		$t->parse('breadCrumbHome', 'breadCrumbHomeBlock',false);

		$breadIterator=1;
		$t->set_block('mainBlock', 'breadCrumbStartBlock', 'breadCrumbStart');

		if (isset($currentCourseID) && !$courseHome){
			$t->set_var('BREAD_HREF_FRONT',  '<a href="{BREAD_LINK}">');
			$t->set_var('BREAD_LINK',  $urlServer.'courses/'.$currentCourseID.'/index.php');
			$t->set_var('BREAD_TEXT',  $intitule);
			$t->set_var('BREAD_ARROW', '&#187;');
			$t->set_var('BREAD_HREF_END',  '</a>');
			$t->parse('breadCrumbStart', 'breadCrumbStartBlock',true);
			$breadIterator++;
			$pageTitle .= " | " .$intitule;

		} elseif (isset($currentCourseID) && $courseHome) {
			$t->set_var('BREAD_HREF_FRONT',  '');
			$t->set_var('BREAD_LINK',  '');
			$t->set_var('BREAD_TEXT',  $intitule);
			$t->set_var('BREAD_ARROW', '&#187;');
			$t->set_var('BREAD_HREF_END',  '');
			$t->parse('breadCrumbStart', 'breadCrumbStartBlock',true);
			$breadIterator++;
			$pageTitle .= " | " .$intitule;

		}

		if (isset($page_navi) && is_array($page_navi) && !$homePage){
			foreach ($page_navi as $step){

				$t->set_var('BREAD_HREF_FRONT',  '<a href="{BREAD_LINK}">');
				$t->set_var('BREAD_LINK',  $step["url"]);
				$t->set_var('BREAD_TEXT',  $step["name"]);
				$t->set_var('BREAD_ARROW', '&#187;');
				$t->set_var('BREAD_HREF_END',  '</a>');
				$t->parse('breadCrumbStart', 'breadCrumbStartBlock',true);

				$breadIterator++;

				$pageTitle .= " | " .$step["name"];
			}
		}

		if (isset($page_name) && !$homePage) {

			$t->set_var('BREAD_HREF_FRONT',  '');
			$t->set_var('BREAD_TEXT',  $page_name);
			$t->set_var('BREAD_ARROW', '&#187;');
			$t->set_var('BREAD_HREF_END',  '');

			$t->parse('breadCrumbStart', 'breadCrumbStartBlock',true);
			$breadIterator++;
			$pageTitle .= " | " .$page_name;

		}

		$t->set_block('mainBlock', 'breadCrumbEndBlock', 'breadCrumbEnd');
		for($breadIterator2=0; $breadIterator2 <= $breadIterator; $breadIterator2++){

			$t->parse('breadCrumbEnd', 'breadCrumbEndBlock',true);
		}

		//END breadcrumb --------------------------------

		$t->set_var('PAGE_TITLE',  $pageTitle);

		//Add the optional tool-specific css of the tool, if it's set
		/*if (isset($tool_css)){
			$t->set_var('TOOL_CSS', "<link href=\"{TOOL_PATH}modules/$tool_css/tool.css\" rel=\"stylesheet\" type=\"text/css\" />");
		}*/

		$t->set_var('TOOL_PATH',  $relPath);

		if (isset($head_content)){
			$t->set_var('HEAD_EXTRAS', $head_content);
		}

		if (isset($body_action)){
			$t->set_var('BODY_ACTION', $body_action);
		}

		//if $require_help is true (set by each tool) display the help link
		if ($require_help == true){
			$help_link = "
		 <a href=\"".$relPath."modules/help/help.php?topic=$helpTopic&language=$language>\" 
        onClick=\"window.open('".$relPath."modules/help/help.php?topic=$helpTopic&language=$language','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
        return false;\">$langHelp</a>
";

			$help_link_icon = " <a id=\"help_icon\" href=\"".$relPath."modules/help/help.php?topic=$helpTopic&language=$language>\"
        onClick=\"window.open('".$relPath."modules/help/help.php?topic=$helpTopic&language=$language','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
        return false;\"></a>";


			$t->set_var('HELP_LINK', $help_link);
			$t->set_var('HELP_LINK_ICON', $help_link_icon);
			$t->set_var('LANG_HELP', $langHelp);
		} else {
			$t->set_var('{HELP_LINK}', '');
			$t->set_var('LANG_HELP', '');
		}

		$t->set_var('LANG_COPYRIGHT_NOTICE', $langCopyrightFooter);

		//		At this point all variables are set and we are ready to send the final output
		//		back to the browser
		//		-----------------------------------------------------------------------------
		$t->parse('main', 'mainBlock', false);

		$t->pparse('Output', 'fh');

	}
	//	session_unregister('errMessage');
}

function installerMenu(){
	global $webDir, $language, $uid, $is_admin, $urlServer, $mysqlMainDb;

	//	include("$webDir/modules/lang/$language/index.inc");

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	array_push($sideMenuSubGroup, "Bhmata egkatastashs");

	// User is not currently in a course - set statut from main database
$urlServer = "lala";

	array_push($sideMenuText, "<b>menu1</b>");
	array_push($sideMenuLink, "modules/admin/");
	array_push($sideMenuImg, "admin-tools.gif");

	array_push($sideMenuText, "menu2");
	array_push($sideMenuLink, "modules/create_course/create_course.php");
	array_push($sideMenuImg, "create_lesson.gif");

	array_push($sideMenuText, "menu3");
	array_push($sideMenuLink, "modules/auth/courses.php");
	array_push($sideMenuImg, "enroll.gif");

	array_push($sideMenuText, "menu4");
	array_push($sideMenuLink, $urlServer . "modules/agenda/myagenda.php");
	array_push($sideMenuImg, "calendar.gif");

	array_push($sideMenuText, "menu5");
	array_push($sideMenuLink, $urlServer . "modules/announcements/myannouncements.php");
	array_push($sideMenuImg, "announcements.gif");

	array_push($sideMenuText, "menu6");
	array_push($sideMenuLink, $urlServer . "modules/profile/profile.php");
	array_push($sideMenuImg, "profile.gif");

	array_push($sideMenuText, "menu7");
	array_push($sideMenuLink, $urlServer . "modules/profile/personal_stats.php");
	array_push($sideMenuImg, "platform_stats.gif");


	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	return $sideMenuGroup;
}

?>
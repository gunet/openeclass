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
	global $langStep, $langStepTitle;

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
				if ($toolArr[$i][2][$j] == true) $currentStep = "currentStep";
				else $currentStep = "";
				$t->set_var('CURRENT_STEP', $currentStep);
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
		/*if ($menuTypeID == 2) {
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
		*/
		$t->set_var('THIRD_BAR_TEXT',$langStepTitle);
		
		
//		$t->set_var('CURRENT_STEP',"currentStep");

		//		$t->set_var('TOOL_NAME',  $nameTools);

		//		$t->set_var('LOGOUT_LINK',  $relPath);
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


		$t->set_var('BREAD_TEXT',  $langStep);


		$pageTitle = "Οδηγός Εγκατάστασης e-Class - " . $langStepTitle . "(" . $langStep . ")";
		$t->set_var('PAGE_TITLE',  $pageTitle);


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

		//		$t->set_var('LANG_COPYRIGHT_NOTICE', $langCopyrightFooter);

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
	global $langRequirements, $langLicence, $langDBSetting;
	global $langCfgSetting, $langLastCheck, $langInstallEnd;

	//	include("$webDir/modules/lang/$language/index.inc");

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	array_push($sideMenuSubGroup, "Πορεία Εγκατάστασης");

	// User is not currently in a course - set statut from main database

	for($i=0; $i<6; $i++) {
		if($i < $_SESSION['step']-1) {
			$currentStep[$i] = false;
			$stepImg[$i] = "tick.gif";
		} else {
			if ($i == $_SESSION['step']-1) $currentStep[$i] = true;
			else $currentStep[$i] = false;
			$stepImg[$i] = "bullet_bw.gif";
		}
	}

	array_push($sideMenuText, $langRequirements);
	array_push($sideMenuLink, $currentStep[0]);
	array_push($sideMenuImg, $stepImg[0]);

	array_push($sideMenuText, $langLicence);
	array_push($sideMenuLink, $currentStep[1]);
	array_push($sideMenuImg, $stepImg[1]);

	array_push($sideMenuText, $langDBSetting);
	array_push($sideMenuLink, $currentStep[2]);
	array_push($sideMenuImg, $stepImg[2]);

	array_push($sideMenuText, $langCfgSetting);
	array_push($sideMenuLink, $currentStep[3]);
	array_push($sideMenuImg, $stepImg[3]);

	array_push($sideMenuText, $langLastCheck);
	array_push($sideMenuLink, $currentStep[4]);
	array_push($sideMenuImg, $stepImg[4]);

	array_push($sideMenuText, $langInstallEnd);
	array_push($sideMenuLink, $currentStep[5]);
	array_push($sideMenuImg, $stepImg[5]);

	array_push($sideMenuSubGroup, $sideMenuText);
	array_push($sideMenuSubGroup, $sideMenuLink);
	array_push($sideMenuSubGroup, $sideMenuImg);
	array_push($sideMenuGroup, $sideMenuSubGroup);

	return $sideMenuGroup;
}

?>
<?php
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
//functions for the new installer
include("../template/template.inc.php");
function draw($toolContent){
	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp, $langAnonUser;
	global $language, $helpTopic, $require_help, $langEclass, $langCopyrightFooter;
	global $relPath, $urlServer;
	global $langChangeLang, $switchLangURL;
	global $langStep, $langStepTitle, $langTitleInstall, $langInstallProgress;

	//display the left column (installation steps)
	$toolArr = installerMenu();
	$numOfToolGroups = count($toolArr);

	$t = new Template();
	$t->set_file('fh', "theme.html");
	$t->set_block('fh', 'mainBlock', 'main');

	//	BEGIN constructing the installation wizard steps
	//	----------------------------------------------------------------------
	$t->set_block('mainBlock', 'leftNavCategoryBlock', 'leftNavCategory');
	$t->set_block('leftNavCategoryBlock', 'leftNavLinkBlock', 'leftNavLink');

	if (is_array($toolArr)) {
		for($i=0; $i< $numOfToolGroups; $i++){
			$numOfTools = count($toolArr[$i][1]);
			for($j=0; $j< $numOfTools; $j++){
				if ($toolArr[$i][1][$j] == true) $currentStep = "currentStep";
				else $currentStep = "";
				$t->set_var('CURRENT_STEP', $currentStep);
				$t->set_var('TOOL_TEXT', $toolArr[$i][0][$j]);

				$t->set_var('IMG_FILE', $toolArr[$i][2][$j]);
				$t->parse('leftNavLink', 'leftNavLinkBlock', true);

				//memorise current step to show it as title
				if ($currentStep != '') {
					$i_var = $i;
					$j_var = $j;
				}
			}

			$t->parse('leftNavCategory', 'leftNavCategoryBlock',true);
			$t->clear_var('leftNavLink'); //clear inner block
		}

		$t->set_var('CURRENT_STEP_TITLE', $toolArr[$i_var][0][$j_var]);
		$t->set_var('URL_PATH',  $urlServer);
		$t->set_var('TOOL_CONTENT', $toolContent);
		$t->set_var('THIRD_BAR_TEXT', $langInstallProgress);
		$t->set_var('BREAD_TEXT',  $langStep);
		$t->set_var('FOUR_BAR_TEXT', $langTitleInstall);

		$pageTitle = "$langTitleInstall - " . $langStepTitle . " (" . $langStep . ")";
		$t->set_var('PAGE_TITLE',  $pageTitle);

		if (isset($head_content)){
			$t->set_var('HEAD_EXTRAS', $head_content);
		}

		if (isset($body_action)){
			$t->set_var('BODY_ACTION', $body_action);
		}

		//		At this point all variables are set and we are ready to send the final output
		//		back to the browser
		//		-----------------------------------------------------------------------------
		$t->parse('main', 'mainBlock', false);
		$t->pparse('Output', 'fh');

	}
}

function installerMenu(){
	global $webDir, $language, $uid, $is_admin, $urlServer, $mysqlMainDb;
	global $langRequirements, $langLicence, $langDBSetting;
	global $langCfgSetting, $langLastCheck, $langInstallEnd;

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	for($i=0; $i<6; $i++) {
		if($i < $_SESSION['step']-1) {
			$currentStep[$i] = false;
			$stepImg[$i] = "tick.gif";
		} else {
			if ($i == $_SESSION['step']-1) $currentStep[$i] = true;
			else $currentStep[$i] = false;
			$stepImg[$i] = "black-arrow1.gif";
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

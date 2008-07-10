<?PHP
//functions for the new installer
include("../template/template.inc");
function draw($toolContent){
	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp, $langAnonUser;
	global $language, $helpTopic, $require_help, $langEclass, $langCopyrightFooter;
	global $relPath, $urlServer;
	global $langChangeLang, $switchLangURL;
	global $langStep, $langStepTitle;

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

//			$t->set_var('ACTIVE_TOOLS', $toolArr[$i][0]);
//			$t->set_var('NAV_CSS_CAT_CLASS', 'category');
			$t->parse('leftNavCategory', 'leftNavCategoryBlock',true);

			$t->clear_var('leftNavLink'); //clear inner block
		}

		$t->set_var('CURRENT_STEP_TITLE', $toolArr[$i_var][0][$j_var]);
		
		$t->set_var('URL_PATH',  $urlServer);

		$t->set_var('TOOL_CONTENT', $toolContent);

		$t->set_var('THIRD_BAR_TEXT',$langStepTitle);

		$t->set_var('BREAD_TEXT',  $langStep);

		$pageTitle = "Οδηγός Εγκατάστασης eClass - " . $langStepTitle . " (" . $langStep . ")";
		$t->set_var('PAGE_TITLE',  $pageTitle);


		//		$t->set_var('TOOL_PATH',  $relPath);

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

//	array_push($sideMenuSubGroup, "Πορεία Εγκατάστασης");

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


// Other useful functions

/*
 * check extension and  write  if exist  in a  <LI></LI>
 * @params string	$extentionName 	name  of  php extention to be checked
 * @params boolean	$echoWhenOk	true => show ok when  extention exist
 * @author Christophe Gesche
 * @desc check extention and  write  if exist  in a  <LI></LI>
 */
function warnIfExtNotLoaded($extentionName) {

	global $tool_content;
	if (extension_loaded ($extentionName)) {
		$tool_content .= "<li> $extentionName - <b>ok!</b> </li> ";
	} else {
		$tool_content .= "
                <li>$extentionName
                <font color=\"#FF0000\"> - <b>Δεν είναι εγκατεστημένο!</b></font>
                (Διαβάστε περισσότερα
                <a href=\"http://www.php.net/$extentionName\" target=_blank>εδώ)</a>
                </li>";
	}
}

// -----------------------------------------------------------------------------------
// checking the mysql version
// note version_compare() is used for checking the php version but works for mysql too
// ------------------------------------------------------------------------------------

function mysql_version() {

	$ver = mysql_get_server_info();
	if (version_compare("4.1", $ver) <= 0)
		return true;
	else
		return false;
}

/*
 * return a string without logic
 *
 * @author Christophe Gesche <gesche@ipm.ucl.ac.be>
 * @param  integer	$nbcar 			default 5   	define here  length of password
 * @param  boolean	$lettresseules	default false	fix  if pass can content digit
 * @return string password
 * @desc return a string to be use as password
 */

function generePass($nbcar=5,$lettresseules = false) {
	$chaine = "abBDEFcdefghijkmnPQRSTUVWXYpqrst23456789"; //possible characters
	if ($lettresseules)
	$chaine = "abcdefghijklmnopqrstuvwxyzAZERTYUIOPMLKJHGFDSQWXCVBN"; //possible characters
	for($i=0; $i<$nbcar; $i++)
	{
		@$pass .= $chaine[rand()%strlen($chaine)];
	}
	return $pass;
}



?>

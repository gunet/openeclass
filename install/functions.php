<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

/**
 * @file install_functions.php
 * @brief Functions for the installation wizard
 */

require_once '../template/template.inc.php';

/**
 * draws installation screens
 * @global type $urlServer
 * @global type $langStep
 * @global type $langStepTitle
 * @global type $langTitleInstall
 * @global type $langInstallProgress
 * @param type $toolContent
 */
function draw($toolContent, $options=null, $head_content ='') {
	global $urlServer, $langStep, $langStepTitle, $langTitleInstall, $langInstallProgress;

    if (!$options) {
        $options = array();
    }

	$t = new Template();
	$t->set_file('fh', '../template/default/theme.html');
	$t->set_block('fh', 'mainBlock', 'main');

    $t->set_var('SITE_NAME', 'Open eClass');
    $t->set_block('mainBlock', 'sideBarBlock', 'delete');
    $t->set_block('mainBlock', 'LoggedInBlock', 'delete');
    $t->set_block('mainBlock', 'LoggedOutBlock', 'delete');
    $t->set_block('mainBlock', 'toolTitleBlock', 'delete');
    $t->set_block('mainBlock', 'statusSwitchBlock', 'delete');
    $t->set_var('logo_img', '../template/default/img/logo_eclass.png');
    $t->set_var('logo_img_small', '../template/default/img/logo_eclass_small.png');
    $t->set_var('template_base', '../template/default');
    $t->set_var('HEAD_EXTRAS', $head_content);
    
    if (isset($options['no-menu'])) {
        $t->set_block('mainBlock', 'leftNavBlock', 'delete');
        $t->set_block('mainBlock', 'breadCrumbs', 'delete');
        $t->set_block('mainBlock', 'normalViewOpenDiv', 'delete');
    } else {
        //display the left column (installation steps)
        $toolArr = installerMenu();
        $numOfToolGroups = count($toolArr);

        $t->set_block('mainBlock', 'leftNavCategoryBlock', 'leftNavCategory');
        $t->set_block('leftNavCategoryBlock', 'leftNavLinkBlock', 'leftNavLink');
        $t->set_block('mainBlock', 'mobileViewOpenDiv', 'delete');
        $t->set_block('mainBlock', 'searchBlock', 'delete');

        if (is_array($toolArr)) {
            for ($i = 0; $i < $numOfToolGroups; $i++) {
                $t->set_var('ACTIVE_TOOLS', $langInstallProgress);
                $t->set_var('TOOL_GROUP_ID', $i + 1);
                $t->set_var('GROUP_CLASS', 'in');
                $numOfTools = count($toolArr[$i][0]);
                for ($j = 0; $j < $numOfTools; $j++) {
                    $t->set_var('TOOL_TEXT', $toolArr[$i][0][$j]);
                    $t->set_var('TOOL_CLASS', $toolArr[$i][1][$j]? 'active': '');
                    $t->set_var('IMG_CLASS', $toolArr[$i][2][$j]);
                    $t->set_var('TOOL_LINK', '#');
                    $t->parse('leftNavLink', 'leftNavLinkBlock', true);

                    // remember current step to use as title
                }

                $t->parse('leftNavCategory', 'leftNavCategoryBlock',true);
                $t->clear_var('leftNavLink'); //clear inner block
            }

            $t->set_var('THIRD_BAR_TEXT', $langInstallProgress);
            $t->set_var('BREAD_TEXT',  $langStep);
            $t->set_var('FOUR_BAR_TEXT', $langTitleInstall);

            $pageTitle = "$langTitleInstall - " . $langStepTitle . " (" . $langStep . ")";
            $t->set_var('PAGE_TITLE',  $pageTitle);
        }
    }
    $t->set_var('URL_PATH',  empty($urlServer)? '../': $urlServer);
    $t->set_var('TOOL_CONTENT', $toolContent);
    $t->parse('main', 'mainBlock', false);
    $t->pparse('Output', 'fh');
    exit;
}

/**
 * @brief installation right menu 
 * @global type $langRequirements
 * @global type $langLicense
 * @global type $langDBSetting
 * @global type $langBasicCfgSetting 
 * @global type $langLastCheck
 * @global type $langInstallEnd
 * @return array
 */
function installerMenu(){
	global $langRequirements, $langLicense, $langDBSetting;
	global $langBasicCfgSetting, $langLastCheck, $langInstallEnd;

	$sideMenuGroup = array();

	$sideMenuSubGroup = array();
	$sideMenuText 	= array();
	$sideMenuLink 	= array();
	$sideMenuImg	= array();

	for($i = 0; $i < 7; $i++) {
		if ($i < $_SESSION['step'] - 1) {
			$currentStep[$i] = false;
			$stepImg[$i] = "fa-check";
		} else {
			if ($i == $_SESSION['step'] - 1) {
				$currentStep[$i] = true;
			} else {
				$currentStep[$i] = false;
			}
            $stepImg[$i] = "fa-angle-double-right";
		}
	}

	array_push($sideMenuText, $langRequirements);
	array_push($sideMenuLink, $currentStep[0]);
	array_push($sideMenuImg, $stepImg[0]);

	array_push($sideMenuText, $langLicense);
	array_push($sideMenuLink, $currentStep[1]);
	array_push($sideMenuImg, $stepImg[1]);

	array_push($sideMenuText, $langDBSetting);
	array_push($sideMenuLink, $currentStep[2]);
	array_push($sideMenuImg, $stepImg[2]);

	array_push($sideMenuText, $langBasicCfgSetting);
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

/**
 * @brief make directories
 * @global type $errorContent
 * @global boolean $configErrorExists
 * @global type $langWarningInstall3
 * @param type $dirname
 */
function mkdir_try($dirname) {
    global $errorContent, $configErrorExists, $langWarningInstall3;
    
    if (!is_dir('../' . $dirname)) {
        if (!@mkdir('../' . $dirname, 0775)) {
            $errorContent[] = sprintf("<p>$langWarningInstall3</p>", $dirname);
            $configErrorExists = true;
        }
    }
}

/**
 * @brief create files
 * @global type $errorContent
 * @global boolean $configErrorExists
 * @global type $langWarningInstall3 
 * @param type $filename
 */
function touch_try($filename) {
    global $errorContent, $configErrorExists, $langWarningInstall3;
    
    if (!@touch('../' . $filename)) {
        $errorContent[] = sprintf("<p>$langWarningInstall3</p>", $filename);
        $configErrorExists = true;
    }
}

function form_entry($name, $input, $label) {
    return "
    <div class='form-group'>
      <label for='$name' class='col-sm-2 control-label'>" . q($label) . "</label>
      <div class='col-sm-10'>$input</div>
    </div>";
}

function display_entry($input, $label) {
    return "
    <div class='form-group'>
      <label class='col-sm-4 control-label'>" . q($label) . "</label>
      <div class='col-sm-8'><p class='form-control-static'>$input</p></div>
    </div>";
}


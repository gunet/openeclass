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

require_once 'template/template.inc.php';

/**
 * draws installation screens
 * @param type $toolContent
 */
function draw($toolContent, $options=null, $head_content ='') {
    global $urlServer, $langStep, $langStepTitle, $langTitleInstall, $langInstallProgress;

    if (!$options) {
        $options = array();
    }

	$t = new Template();
	$t->set_file('fh', 'template/modern/theme.html');
	$t->set_block('fh', 'mainBlock', 'main');

    $t->set_var('SITE_NAME', 'Open eClass');
    $t->set_block('mainBlock', 'sideBarBlock', 'delete');
    $t->set_block('mainBlock', 'LoggedInBlock', 'delete');
    $t->set_block('mainBlock', 'LoggedOutBlock', 'delete');
    $t->set_block('mainBlock', 'toolTitleBlock', 'delete');
    $t->set_block('mainBlock', 'statusSwitchBlock', 'delete');
    $t->set_var('logo_img_small', '../template/modern/img/eclass-new-logo.svg');
    $t->set_var('template_base', '../template/modern');
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

            $t->set_block('mainBlock', 'breadCrumbLinkBlock', 'breadCrumbLink');
            $t->set_block('mainBlock', 'breadCrumbEntryBlock', 'breadCrumbEntry');
            $t->set_var('BREAD_HREF',  '#');
            $t->set_var('BREAD_TEXT',  $langStep);
            $t->parse('breadCrumbLink', 'breadCrumbLinkBlock', true);
            $t->set_var('BREAD_TEXT',  $langStepTitle);
            $t->parse('breadCrumbEntry', 'breadCrumbEntryBlock', true);

            $t->set_var('THIRD_BAR_TEXT', $langInstallProgress);
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
 * @return array
 */
function installerMenu() {
    global $langRequirements, $langLicense, $langDBSetting,
        $langBasicCfgSetting, $langLastCheck, $langInstallEnd,
        $langEmailSettings, $langThemeSettings;

    $sideMenuGroup = array();

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    $stepTitles = array($langRequirements, $langLicense, $langDBSetting,
        $langBasicCfgSetting, $langThemeSettings, $langEmailSettings, $langLastCheck,
        $langInstallEnd);

	for($i = 0; $i < 8; $i++) {
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
        $sideMenuText[] = $stepTitles[$i];
        $sideMenuLink[] = $currentStep[$i];
        $sideMenuImg[] = $stepImg[$i];
	}

	$sideMenuSubGroup[] = $sideMenuText;
	$sideMenuSubGroup[] = $sideMenuLink;
	$sideMenuSubGroup[] = $sideMenuImg;
	$sideMenuGroup[] = $sideMenuSubGroup;

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
    global $errorContent, $configErrorExists, $langWarningInstall3, $autoinstall;

    if (!is_dir($dirname)) {
        if (!make_dir($dirname)) {
            if ($autoinstall) {
                echo sprintf($langWarningInstall3, $dirname), "\n";
            } else {
                $errorContent[] = sprintf("<p>$langWarningInstall3</p>", $dirname);
            }
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
    global $errorContent, $configErrorExists, $langWarningInstall3, $autoinstall;

    if (!@touch($filename)) {
        if ($autoinstall) {
            echo sprintf($langWarningInstall3, $dirname), "\n";
        } else {
            $errorContent[] = sprintf("<p>$langWarningInstall3</p>", $filename);
        }
        $configErrorExists = true;
    }
}

function form_entry($name, $input, $label) {
    return "
    <div class='form-group mt-3'>
      <label for='$name' class='col-sm-12 control-label-notes'>" . q($label) . "</label>
      <div class='col-sm-12'>$input</div>
    </div>";
}

function display_entry($input, $label) {

    $content = '';

    if ($input) {
        $content = "
            <div class='form-group mt-3'>
              <label class='col-sm-12 control-label-notes'>" . q($label) . "</label>
              <div class='col-sm-12'>
                <p class='form-control-static'>
                    $input
                </p>
              </div>
            </div>";
    }
    return $content;
}

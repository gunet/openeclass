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
 * Base Theme Component, e-Class Core
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component is the core of eclass. Each and every file that 
 * requires output to the user's browser must include this file and use
 * the draw method to output the UI to the user's browser.
 * 
 * An exception of this scenario is when the user uses the personalised 
 * interface. In that case function drawPerso needs to be called.
 *
 */
include('init.php');

//template path for logged out + logged in (ex., when session expires)
$extraMessage = "";//initialise var for security
if(isset($errorMessagePath)) {
	$relPath = $errorMessagePath;
}

if(isset($toolContent_ErrorExists)) {
	$toolContent = $toolContent_ErrorExists;

	$_SESSION['errMessage'] = $toolContent_ErrorExists;
	session_write_close();
	header("location:".$urlServer."index.php?logout=yes");
	exit();
}

if (session_is_registered('errMessage') && strlen($_SESSION['errMessage'])>0) {

	$extraMessage = $_SESSION['errMessage'];
	session_unregister('errMessage');
}

include($relPath."template/template.inc");
include('tools.php');

/**
 * Function draw
 *
 * This method processes all data to render the display. It is executed by
 * each tool. Is in charge of generating the interface and parse it to the user's browser.
 * 
 * @param mixed $toolContent html code
 * @param int $menuTypeID 
 * @param string $tool_css (optional) catalog name where a "tool.css" file exists
 * @param string $head_content (optional) code to be added to the HEAD of the UI
 * @param string $body_action (optional) code to be added to the BODY tag
 */
function draw($toolContent, $menuTypeID, $tool_css = null, $head_content = null, $body_action = null, $hideLeftNav = null){
	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp, $langAnonUser;
	global $language, $helpTopic, $require_help, $langEclass, $langCopyrightFooter;
	global $relPath, $urlServer, $toolContent_ErrorExists, $statut;
	global $page_name, $page_navi,$currentCourseID, $siteName, $navigation;
	global $homePage, $courseHome, $uid, $webDir, $extraMessage;
	global $langChangeLang, $langUserBriefcase, $langPersonalisedBriefcase, $langAdmin, $switchLangURL;
	global $langSearch, $langAdvancedSearch;

	$messageBox = "";

	//if an error exists (ex., sessions is lost...)
	//show the error message above the normal tool content

	if (strlen($extraMessage) > 0) {
		$messageBox =  "
					<table width=\"99%\">
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
	$toolArr = getSideMenu($menuTypeID);
	$numOfToolGroups = count($toolArr);

	$t = new Template($relPath ."template/classic");

	$t->set_file('fh', "theme.html");

	$t->set_block('fh', 'mainBlock', 'main');

	//	BEGIN constructing of left navigation
	//	----------------------------------------------------------------------
	$t->set_block('mainBlock', 'leftNavBlock', 'leftNav');
	$t->set_block('leftNavBlock', 'leftNavCategoryBlock', 'leftNavCategory');
	$t->set_block('leftNavCategoryBlock', 'leftNavCategoryTitleBlock', 'leftNavCategoryTitle');

	$t->set_block('leftNavCategoryBlock', 'leftNavLinkBlock', 'leftNavLink');

	if (is_array($toolArr)) {

		for($i=0; $i< $numOfToolGroups; $i++){

			if($toolArr[$i][0]['type'] == 'none') {
				$t->set_var('ACTIVE_TOOLS', '');
				$t->set_var('NAV_CSS_CAT_CLASS', 'spacer');
				$t->parse('leftNavCategoryTitle', 'leftNavCategoryTitleBlock',false);
//				$t->clear_var('leftNavCategoryTitle'); //clear inner block
			} elseif ($toolArr[$i][0]['type'] == 'split') {
				$t->set_var('ACTIVE_TOOLS', '');
				$t->set_var('NAV_CSS_CAT_CLASS', 'split');
				$t->parse('leftNavCategoryTitle', 'leftNavCategoryTitleBlock',false);
				
			} elseif ($toolArr[$i][0]['type'] == 'text') {
				$t->set_var('ACTIVE_TOOLS', $toolArr[$i][0]['text']);
				$t->set_var('NAV_CSS_CAT_CLASS', 'category');
				$t->parse('leftNavCategoryTitle', 'leftNavCategoryTitleBlock',false);
			}

			$numOfTools = count($toolArr[$i][1]);
			for($j=0; $j< $numOfTools; $j++){

				$t->set_var('TOOL_LINK', $toolArr[$i][2][$j]);
				$t->set_var('TOOL_TEXT', $toolArr[$i][1][$j]);

				$t->set_var('IMG_FILE', $toolArr[$i][3][$j]);
				$t->parse('leftNavLink', 'leftNavLinkBlock', true);

			}



			$t->parse('leftNavCategory', 'leftNavCategoryBlock',true);

			$t->clear_var('leftNavLink'); //clear inner block
		}
		$t->parse('leftNav', 'leftNavBlock',true);

		if (isset($hideLeftNav)) {
			$t->clear_var('leftNav');
			$t->set_var('CONTENT_MAIN_CSS', 'content_main_no_nav');
		} else {
			$t->set_var('CONTENT_MAIN_CSS', 'content_main');
		}

		$t->set_var('URL_PATH',  $urlServer);

		//If there is a message to display, show it (ex. Session timeout)
		if (strlen($messageBox) > 1) {
			$t->set_var('EXTRA_MSG', $messageBox);
		}

		$t->set_var('TOOL_CONTENT', $toolContent);

		//if we are on the login page we can include two optional html files
		//by including eclass_home_extras_left.html (if exists) and
		//eclass_home_extras_right.html (if exists) for extra content on the
		//left and right bar.

		if ($homePage && file_exists('./eclass_home_extras_left.html') && !session_is_registered('uid')) {
			$s = file_get_contents($webDir . 'eclass_home_extras_left.html');
			$t->set_var('ECLASS_HOME_EXTRAS_LEFT', $s);
		}

		if ($homePage && file_exists('./eclass_home_extras_right.html') && !session_is_registered('uid')) {
			$s = file_get_contents($webDir . 'eclass_home_extras_right.html');
			$t->set_var('ECLASS_HOME_EXTRAS_RIGHT', $s);
		}

		//show user's name and surname on the user bar
		if (session_is_registered('uid') && strlen($nom) > 0) {
			$t->set_var('LANG_USER', $langUser);
			$t->set_var('USER_NAME', $prenom);
			$t->set_var('USER_SURNAME', $nom.", ");
		}

		//if user is logged in display the logout option
		if (session_is_registered('uid')) {
			$t->set_var('LANG_LOGOUT', $langLogout);
//			$t->set_var('LOGOUT_CLASS_ICON', 'logout_icon');
		}

		//set the text and icon on the third bar (header)
		if ($menuTypeID == 2) {
			$t->set_var('THIRD_BAR_TEXT', $intitule);
			$t->set_var('THIRDBAR_LEFT_ICON', 'lesson_icon');
		} elseif (isset($langUserBriefcase) && $menuTypeID > 0 && $menuTypeID <3 && !session_is_registered('user_perso_active')) {
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
			$t->set_var('THIRDBAR_LEFT_ICON', 'logo_icon');
		}

		//set the appropriate search action for the searchBox form
		if ($menuTypeID==2) {
			$searchAction = "search_incourse.php";
			$searchAdvancedURL = $searchAction;
		} elseif ($menuTypeID == 1 || $menuTypeID == 3) {
			$searchAction = "search.php";
			$searchAdvancedURL = $searchAction;
		} else {//$menuType == 0
			$searchAction = "search.php";
			$searchAdvancedURL = $searchAction;
		}

		$t->set_var('SEARCH_ACTION', $searchAction);
		$t->set_var('SEARCH_ADVANCED_URL', $searchAdvancedURL);
		$t->set_var('SEARCH_TITLE', $langSearch);
		$t->set_var('SEARCH_ADVANCED', $langAdvancedSearch);

		$t->set_var('TOOL_NAME',  $nameTools);

		$t->set_var('LOGOUT_LINK',  $relPath);

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
			$t->set_var('LOCALIZE_LINK',  '');
		}

		//START breadcrumb AND page title

		if (!$page_navi) $page_navi = $navigation;
		if (!$page_name) $page_name = $nameTools;

		$t->set_block('mainBlock', 'breadCrumbHomeBlock', 'breadCrumbHome');

		if ($statut != 10) {
			if(!session_is_registered('uid')) $t->set_var('BREAD_TEXT',  $siteName);
			elseif(session_is_registered('uid') && session_is_registered('user_perso_active')) {
				$t->set_var('BREAD_TEXT',  $langPersonalisedBriefcase);
			} elseif(session_is_registered('uid') && !session_is_registered('user_perso_active')) {
				$t->set_var('BREAD_TEXT',  $langUserBriefcase);
			}


			if (!$homePage) {
				$t->set_var('BREAD_HREF_FRONT',  '<a href="{BREAD_START_LINK}">');
				$t->set_var('BREAD_START_LINK',  $urlServer);
				$t->set_var('BREAD_HREF_END',  '</a>');
			}

			$t->parse('breadCrumbHome', 'breadCrumbHomeBlock',false);
		}

		$pageTitle = $siteName;

		$breadIterator=1;
		$t->set_block('mainBlock', 'breadCrumbStartBlock', 'breadCrumbStart');

		if (isset($currentCourseID) && !$courseHome){
			$t->set_var('BREAD_HREF_FRONT',  '<a href="{BREAD_LINK}">');
			$t->set_var('BREAD_LINK',  $urlServer.'courses/'.$currentCourseID.'/index.php');
			$t->set_var('BREAD_TEXT',  $intitule);
			if ($statut == 10) $t->set_var('BREAD_ARROW', '');
			$t->set_var('BREAD_HREF_END',  '</a>');
			$t->parse('breadCrumbStart', 'breadCrumbStartBlock',true);
			$breadIterator++;
			if(isset($pageTitle)) {
				$pageTitle .= " | " .$intitule;
			} else {
				$pageTitle = $intitule;
			}

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


		for($breadIterator2=0; $breadIterator2 < $breadIterator; $breadIterator2++){

			$t->parse('breadCrumbEnd', 'breadCrumbEndBlock',true);
		}


		//END breadcrumb --------------------------------

		$t->set_var('PAGE_TITLE',  $pageTitle);

		//Add the optional tool-specific css of the tool, if it's set
		if (isset($tool_css)){
			$t->set_var('TOOL_CSS', "<link href=\"{TOOL_PATH}modules/$tool_css/tool.css\" rel=\"stylesheet\" type=\"text/css\" />");
		}

		$t->set_var('TOOL_PATH',  $relPath);

		if (isset($head_content)){
			$t->set_var('HEAD_EXTRAS', $head_content);
		}

		if (isset($body_action)){
			$t->set_var('BODY_ACTION', $body_action);
		}

		//if $require_help is true (set by each tool) display the help link
		if ($require_help == true){

			$help_link_icon = " <a  href=\"".$relPath."modules/help/help.php?topic=$helpTopic&amp;language=$language\"
        onClick=\"window.open('".$relPath."modules/help/help.php?topic=$helpTopic&amp;language=$language','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
        return false;\"> <img src=\"".$relPath."template/classic/img/help_icon.gif\" width=\"14\" height=\"14\" border=\"0\" alt=\"$langHelp\"/> </a>";

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

/**
 * Function drawPerso
 * 
 * This method processes all data to render the display. It is executed by
 * eclass personalised. Is in charge of generating the interface and parse it to the user's browser.
 *
 * @param mixed $toolContent html code
 * 
 */
function drawPerso($toolContent){

	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp, $langPersonalisedBriefcase;
	global $language, $helpTopic, $require_help, $langCopyrightFooter;
	global $relPath, $urlServer, $is_admin;
	global $page_name, $page_navi,$currentCourseID, $siteName, $navigation;
	global $homePage, $courseHome;
	global $langPersonalisedBriefcase, $langMyPersoLessons, $langMyPersoDeadlines;
	global $langMyPersoAnnouncements, $langMyPersoDocs, $langMyPersoAgenda, $langMyPersoForum;
	global $langModifyProfile, $langSearch, $langAdminTool;
	global $langChangeLang, $switchLangURL;
	global $langSearch, $langAdvancedSearch;

	//get blocks content from $toolContent array
	$lesson_content 	= $toolContent['lessons_content'];
	$assigns_content 	= $toolContent['assigns_content'];
	$announce_content 	= $toolContent['announce_content'];
	$docs_content		= $toolContent['docs_content'];
	$agenda_content 	= $toolContent['agenda_content'];
	$forum_content 		= $toolContent['forum_content'];

	$t = new Template($relPath ."template/classic");

	$t->set_file('fh', "perso.html");

	$t->set_block('fh', 'mainBlock', 'main');

	$t->set_var('LANG_USER', $langUser);
	$t->set_var('USER_NAME', $prenom);
	$t->set_var('USER_SURNAME', $nom.", ");
	$t->set_var('LANG_LOGOUT', $langLogout);
	$t->set_var('LOGOUT_LINK',  $relPath);

	if (session_is_registered('langswitch')) {
		$lang_localize = $langChangeLang;
		$localize_link = $switchLangURL;
	} else {
		$lang_localize = 'English';
		$localize_link =  '?localize=en';
	}

	$otherLinks = "";
	if ($is_admin) {
		$otherLinks = "<a class=\"admin_icon\" href=".$urlServer."modules/admin/>$langAdminTool</a> | ";
	}

	$otherLinks .= "<a class=\"create_course_icon\" href=".$urlServer."modules/profile/profile.php>$langModifyProfile</a> | ";
	$otherLinks .= "<a href=".$localize_link.">$lang_localize</a>";
	$t->set_var('OTHER_LINKS',  $otherLinks);

	$t->set_var('THIRD_BAR_TEXT', $langPersonalisedBriefcase);
	$t->set_var('THIRDBAR_LEFT_ICON', 'briefcase_icon');



	$t->set_var('SEARCH_TITLE', $langSearch);
	$t->set_var('SEARCH_ADVANCED', $langAdvancedSearch);

	$t->set_var('LANG_MY_PERSO_LESSONS', $langMyPersoLessons);
	$t->set_var('LANG_MY_PERSO_DEADLINES', $langMyPersoDeadlines);
	$t->set_var('LANG_MY_PERSO_ANNOUNCEMENTS', $langMyPersoAnnouncements);
	$t->set_var('LANG_MY_PERSO_DOCS', $langMyPersoDocs);
	$t->set_var('LANG_MY_PERSO_AGENDA', $langMyPersoAgenda);
	$t->set_var('LANG_PERSO_FORUM',  $langMyPersoForum);

	$t->set_var('LESSON_CONTENT', $lesson_content);
	$t->set_var('ASSIGN_CONTENT', $assigns_content);
	$t->set_var('ANNOUNCE_CONTENT', $announce_content);
	$t->set_var('DOCS_CONTENT', $docs_content);
	$t->set_var('AGENDA_CONTENT', $agenda_content);
	$t->set_var('FORUM_CONTENT', $forum_content);
	$t->set_var('URL_PATH',  $urlServer);
	$t->set_var('TOOL_PATH',  $relPath);

	//START breadcrumb

	if (!$page_navi) $page_navi = $navigation;
	if (!$page_name) $page_name = $nameTools;

	$t->set_block('mainBlock', 'breadCrumbHomeBlock', 'breadCrumbHome');

	$t->set_var('BREAD_TEXT',  $langPersonalisedBriefcase);
	$t->set_var('PAGE_TITLE',  $siteName);
	// end breadcrumb

	$t->set_var('LANG_COPYRIGHT_NOTICE', $langCopyrightFooter);

	$t->parse('main', 'mainBlock', false);

	$t->pparse('Output', 'fh');
}

/**
 * Function dumpArray
 *
 * Used for debugging purposes. Dumps array to browser
 * window.
 * 
 * @param array $arr
 */
function dumpArray($arr){
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

/**
 * Function print_a
 *
 * Used for debugging purposes. Dumps array to browser
 * window. Better organisation of arrays than dumpArray
 * 
 * @param array $arr
 */
function print_a($TheArray) {
	echo "<table border=1>n";

	$Keys = array_keys( $TheArray );
	foreach( $Keys as $OneKey )
	{
		echo "<tr>n";

		echo "<td bgcolor='yellow'>";
		echo "<B>" . $OneKey . "</B>";
		echo "</td>n";

		echo "<td bgcolor='#C4C2A6'>";
		if ( is_array($TheArray[$OneKey]) )
		print_a($TheArray[$OneKey]);
		else
		echo $TheArray[$OneKey];
		echo "</td>n";

		echo "</tr>n";
	}
	echo "</table>n";
}

?>

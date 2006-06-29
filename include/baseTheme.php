<?PHP

/**
 * baseTheme
 * 
 *Includes some basic functions to render the UI.
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version 1.0
 * @package eclass 2.0
 * 
 +----------------------------------------------------------------------+
 |   $Id$       |
 +----------------------------------------------------------------------+
 */



include('init.php');

//template path for modules
//include('../../template/template.inc');

//template path for logged out + logged in
include($relPath."template/template.inc");
include('tools.php');

function getTools($menuTypeID){

	return getSideMenu($menuTypeID);
}


/**
	 * function draw
	 * 
	 * This method processes all data to render the display. It is executed by
	 * each tool. Is in charge of generating the 
	 * interface and parse it to the user's browser.
	 * 
	 */
function draw($toolContent, $menuTypeID, $tool_css = null, $head_content = null, $body_action = null){
	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp, $langAnonUser;
	global $language, $helpTopic, $require_help, $langEclass, $langCopyrightFooter;
	global $relPath, $urlServer;
	global $page_name, $page_navi,$currentCourseID, $siteName, $navigation;
	global $homePage, $courseHome, $uid, $webDir;
	global $langChangeLang, $langUserBriefcase, $langPersonalisedBriefcase, $langAdmin, $switchLangURL;

	$toolArr = getTools($menuTypeID);
	//	dumpArray($toolArr);

	$numOfToolGroups = count($toolArr);

	//	$t = new Template("../../template/classic");
	//	echo $relPath;
	$t = new Template($relPath ."template/classic");

	$t->set_file('fh', "theme.html");

	$t->set_block('fh', 'mainBlock', 'main');

	//	if(isset($this->head_extras)) $t->set_var('HEAD_EXTRAS', $this->head_extras);
	//	if(isset($this->body_action)) $t->set_var('BODY_ACTION', $this->body_action);

	//			BEGIN constructing of left navigation
	//			---------------------------------------------------------------------------------------------------------------------------------------------
	$t->set_block('mainBlock', 'leftNavCategoryBlock', 'leftNavCategory');
	//
	//	$t->set_var('LESSON_TITLE', $currentCourseName);
	//	$t->set_var('URL_APPEND', $urlAppend);

	$t->set_block('leftNavCategoryBlock', 'leftNavLinkBlock', 'leftNavLink');

	if (is_array($toolArr)) {

		for($i=0; $i< $numOfToolGroups; $i++){

			$numOfTools = count($toolArr[$i][1]);

			for($j=0; $j< $numOfTools; $j++){

				$t->set_var('TOOL_LINK', $toolArr[$i][2][$j]);
				$t->set_var('TOOL_TEXT', $toolArr[$i][1][$j]);
				//				$t->set_var('TOOL_ICON', 'agenda');
				$t->set_var('IMG_FILE', $toolArr[$i][3][$j]);
				$t->parse('leftNavLink', 'leftNavLinkBlock', true);

			}

			$t->set_var('ACTIVE_TOOLS', $toolArr[$i][0]);
			$t->set_var('NAV_CSS_CAT_CLASS', 'category');
			$t->parse('leftNavCategory', 'leftNavCategoryBlock',true); //auto prepei na einai true otan mpei o ypoloipos kwdikas


			$t->clear_var('leftNavLink'); //clear inner block
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



		if (session_is_registered('uid') && strlen($nom) > 0) {
			$t->set_var('LANG_USER', $langUser);
			$t->set_var('USER_NAME', $prenom);
			$t->set_var('USER_SURNAME', $nom);
		}

		if (session_is_registered('uid')) {
			$t->set_var('LANG_LOGOUT', $langLogout);
			$t->set_var('LOGOUT_CLASS_ICON', 'logout_icon');
		}

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
		}

		if ($menuTypeID !=2) {
			$t->set_var('INV1',  '<!--');
			$t->set_var('INV2',  '-->');
		}

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

		//END breadcrumb

		$t->set_var('PAGE_TITLE',  $pageTitle);

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
}

function drawPerso($toolContent, $menuTypeID=null, $tool_css = null, $head_content = null, $body_action = null){

	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp, $langPersonalisedBriefcase;
	global $language, $helpTopic, $require_help, $langCopyrightFooter;
	global $relPath, $urlServer, $is_admin;
	global $page_name, $page_navi,$currentCourseID, $siteName, $navigation;
	global $homePage, $courseHome;
	global $langPersonalisedBriefcase, $langMyPersoLessons, $langMyPersoDeadlines;
	global $langMyPersoAnnouncements, $langMyPersoDocs, $langMyPersoAgenda, $langMyPersoForum;
	global $langModifyProfile, $langSearch, $langAdminTool;
	global $langChangeLang, $switchLangURL;

	//	global $lesson_content;

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
	$t->set_var('USER_SURNAME', $nom);
	$t->set_var('LANG_LOGOUT', $langLogout);
	$t->set_var('LOGOUT_LINK',  $relPath);

	//	$t->set_var('TOOL_NAME',  $nameTools);//feugei ?

	//	$t->set_var('LESSON_TITLE', $intitule);//auto allazei

	$otherLinks = "| <a class=\"create_course_icon\" href=".$urlServer."modules/profile/profile.php>$langModifyProfile</a> ";
	if ($is_admin) {
		$otherLinks .= "| <a class=\"admin_icon\" href=".$urlServer."modules/admin/>$langAdminTool</a> ";
	}
	$otherLinks .= "| <a class=\"search_icon\" href=".$urlServer."modules/search/search.php>$langSearch</a>";
	$t->set_var('OTHER_LINKS',  $otherLinks);

	$t->set_var('THIRD_BAR_TEXT', $langPersonalisedBriefcase);
	$t->set_var('THIRDBAR_LEFT_ICON', 'briefcase_icon');

	if (session_is_registered('langswitch')) {
		$t->set_var('LANG_LOCALIZE',  $langChangeLang);
		$t->set_var('LOCALIZE_LINK',  $switchLangURL);
	} else {
		$t->set_var('LANG_LOCALIZE',  'English');
		$t->set_var('LOCALIZE_LINK',  '?localize=en');
	}
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
	$t->set_var('TOOL_PATH',  $relPath);

	//START breadcrumb

	if (!$page_navi) $page_navi = $navigation;
	if (!$page_name) $page_name = $nameTools;

	$t->set_block('mainBlock', 'breadCrumbHomeBlock', 'breadCrumbHome');
	//	$t->set_var('BREAD_TEXT',  $siteName);
	$t->set_var('BREAD_TEXT',  $langPersonalisedBriefcase);
	$t->set_var('PAGE_TITLE',  $siteName);
	// end breadcrumb

	$t->set_var('LANG_COPYRIGHT_NOTICE', $langCopyrightFooter);
	
	$t->parse('main', 'mainBlock', false);

	$t->pparse('Output', 'fh');
}

/**
 * function dumpArray
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

function print_a( $TheArray )
{ // Note: the function is recursive
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
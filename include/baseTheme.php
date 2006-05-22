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
	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp;
	global $language, $helpTopic, $require_help;
	global $relPath, $urlServer;
	global $page_name, $page_navi,$currentCourseID, $siteName, $navigation;
	global $homePage, $courseHome;

	$toolArr = getTools($menuTypeID);
	//	dumpArray($toolArr);
	//echo MODULE_ID_GROUPS;
	$numOfToolGroups = count($toolArr);



	//	$t = new Template("../../template/classic");
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

	if (is_array($toolArr)){

		for($i=0; $i< $numOfToolGroups; $i++){

			$numOfTools = count($toolArr[$i][1]);
			//echo "draw";

			//			$t->set_var('TOOL_LINK', $url_intro);
			//			$t->set_var('TOOL_TEXT', 'Eisagwgh Ma8hmatos');
			//			$t->parse('leftNavLink', 'leftNavLinkBlock', true);

			for($j=0; $j< $numOfTools; $j++){

				$t->set_var('TOOL_LINK', $toolArr[$i][2][$j]);
				$t->set_var('TOOL_TEXT', $toolArr[$i][1][$j]);
				$t->set_var('TOOL_ICON', 'agenda');
				$t->parse('leftNavLink', 'leftNavLinkBlock', true);

			}

			$t->set_var('ACTIVE_TOOLS', $toolArr[$i][0]);
			$t->set_var('NAV_CSS_CAT_CLASS', 'category');
			$t->parse('leftNavCategory', 'leftNavCategoryBlock',true); //auto prepei na einai true otan mpei o ypoloipos kwdikas


			$t->clear_var('leftNavLink'); //clear inner block
		}

		$t->set_var('TOOL_CONTENT', $toolContent);

		$t->set_var('LANG_USER', $langUser);
		$t->set_var('USER_NAME', $prenom);
		$t->set_var('USER_SURNAME', $nom);
		$t->set_var('LANG_LOGOUT', $langLogout);
		//TODO: set a var for logout link url!
		$t->set_var('LESSON_TITLE', $intitule);
		$t->set_var('TOOL_NAME',  $nameTools);

		$t->set_var('LOGOUT_LINK',  $relPath);

		//START breadcrumb
		//		$t->set_var('BREAD_TEXT',  "Η αρχική μου σελίδα");
		//		$t->set_var('BREAD_START_LINK',  $urlServer);

		if (!$page_navi) $page_navi = $navigation;
		if (!$page_name) $page_name = $nameTools;

		$t->set_block('mainBlock', 'breadCrumbHomeBlock', 'breadCrumbHome');
		$t->set_var('BREAD_TEXT',  $siteName);
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
		} elseif (isset($currentCourseID) && $courseHome) {
			$t->set_var('BREAD_HREF_FRONT',  '');
			$t->set_var('BREAD_LINK',  '');
			$t->set_var('BREAD_TEXT',  $intitule);
			$t->set_var('BREAD_ARROW', '&#187;');
			$t->set_var('BREAD_HREF_END',  '');
			$t->parse('breadCrumbStart', 'breadCrumbStartBlock',true);
			$breadIterator++;
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
			}
		}

		if (isset($page_name) && !$homePage) {
			
			$t->set_var('BREAD_HREF_FRONT',  '');
			$t->set_var('BREAD_TEXT',  $page_name);
			$t->set_var('BREAD_ARROW', '&#187;');
			$t->set_var('BREAD_HREF_END',  '');

			$t->parse('breadCrumbStart', 'breadCrumbStartBlock',true);
			$breadIterator++;
		}
		
		$t->set_block('mainBlock', 'breadCrumbEndBlock', 'breadCrumbEnd');
		for($breadIterator2=0; $breadIterator2 <= $breadIterator; $breadIterator2++){

			$t->parse('breadCrumbEnd', 'breadCrumbEndBlock',true);
		}

		//END breadcrumb

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



		//		At this point all variables are set and we are ready to send the final output
		//		back to the browser
		//		-----------------------------------------------------------------------------
		$t->parse('main', 'mainBlock', false);

		$t->pparse('Output', 'fh');

	}
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

?>
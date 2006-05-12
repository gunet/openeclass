<?PHP

/**
 * baseTheme
 * 
 *Includes some basic functions to render the UI.
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version 1.0
 * @package eclass 2.0
 */


include('init.php');
//echo $langAdmin;
include('../../template/template.inc');
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
	global $langUser, $prenom, $nom, $langLogout, $intitule,  $nameTools, $langHelp, $language, $helpTopic, $require_help;
	$toolArr = getTools($menuTypeID);
//	dumpArray($toolArr);

//	echo "<br>" . count($toolArr[0][1]);
	$numOfToolGroups = count($toolArr);

	//	$url_intro = $this->eclBase->varVal("get", "url_server");
	//	$url_intro .= $this->eclBase->varVal("get", "current_course_id");

	$t = new Template("../../template/classic");

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
		
		$t->set_var('LESSON_TITLE', $intitule);
		$t->set_var('TOOL_NAME',  $nameTools);
		
		
		if (isset($tool_css)){
			
			$t->set_var('TOOL_CSS', "<link href=\"../../modules/$tool_css/tool.css\" rel=\"stylesheet\" type=\"text/css\" />");
		}
		
		if (isset($head_content)){
			$t->set_var('HEAD_EXTRAS', $head_content);
		}
		
		if (isset($body_action)){
			$t->set_var('BODY_ACTION', $body_action);
		}
		
		if ($require_help == true){
			$help_link = <<<hLink
		 <a href="../help/help.php?topic=$helpTopic&language=$language>" 
        onClick="window.open('../help/help.php?topic=$helpTopic&language=$language','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
        return false;">$langHelp</a>
hLink;

			$help_link_icon = " <a id=\"help_icon\" href=\"../help/help.php?topic=$helpTopic&language=$language>\" 
        onClick=\"window.open('../help/help.php?topic=$helpTopic&language=$language','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
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
	 * function makeToolLink
	 *
	 * The tool links are stored in a database in the format of
	 * "../claroline/
	 * This method removes the beginning dots to be able to be used
	 * with the new interface.
	 * 
	 * There is a problem to be resolved regarding external module links
	 * 
	 * @param string $link
	 * @return string
	 */
/*function makeToolLink($link){
	$path = $this->eclBase->varVal("get", "url_server");
	if (substr($link, 3) != "htt") {
		$path .= substr($link, 3);
	} else {
		$path = substr($link, 3);
	}
	//		$path .= substr($link, 3);
	return $path;
}*/

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
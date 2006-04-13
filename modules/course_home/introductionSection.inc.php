<?php

/*
 * The INTRODUCTION MICRO MODULE is used to insert and edit
 * an introduction section on a Claroline Module.
 * It can be inserted on any Claroline Module, provided a connection 
 * to a course Database is already active.
 *
 * The introduction content are stored on a table called "introduction" 
 * in the course Database. Each module introduction has an Id stored on 
 * the table. It is this id that can make correspondance to a specific module.
 *
 * 'introduction' table description
 *   id : int
 *   texte_intro :text
 *
 *
 * usage :
 *
 * $moduleId = XX // specifying the module Id
 * include(moduleIntro.inc.php);
 */
/*

*/

if ($language == 'greek') 
	$lang_editor='gr';
else 
	$lang_editor='en';

?>
<script type="text/javascript">
  _editor_url = '<?= $urlAppend ?>/include/htmlarea/';
  _css_url='<?= $urlAppend ?>/css/';
  _image_url='<?= $urlAppend ?>/include/htmlarea/images/';
  _editor_lang = '<?= $lang_editor ?>';
</script>
<script type="text/javascript" src='<?= $urlAppend ?>/include/htmlarea/htmlarea.js'></script>

<script type="text/javascript">
var editor = null;

function initEditor() {

  var config = new HTMLArea.Config();
  config.height = '180px';
  config.hideSomeButtons("showhelp undo redo popupeditor ");
  editor = new HTMLArea("ta",config);

  // comment the following two lines to see how customization works
  editor.generate();
  return false;
}

</script>

<body onload="initEditor()">  
<?
include_once('../../include/lib/textLib.inc.php');

if ($is_adminOfCourse) {
	$intro_editAllowed = true; // "view & edit" Mode
} else {
	$intro_editAllowed = false; // "view only" Mode
}


/*********************************************************
INTRODUCTION MICRO MODULE - COMMANDS SECTION (IF ALLOWED)
**********************************************************/

if ($intro_editAllowed)
{
	/*** Replace command ***/

	if (isset($intro_cmdUpdate))
	{
		$intro_content = trim($intro_content);

		if (!empty($intro_content) )
		{
			mysql_query("REPLACE introduction 
				SET id=\"".$moduleId."\", 
				texte_intro=\"".$intro_content."\"");
		} else {
			$intro_cmdDel = true;	// got to the delete command
		}
	}

	/*** Delete Command ***/

	if (isset($intro_cmdDel)) {
		mysql_query("DELETE FROM introduction WHERE id=\"".$moduleId."\"");
	}
}


/*******************************************
INTRODUCTION MICRO MODULE - DISPLAY SECTION
********************************************/

/*** Retrieves the module introduction text, if exist ***/

$intro_dbQuery = mysql_query("SELECT texte_intro FROM introduction WHERE id=\"".$moduleId."\"");
$intro_dbResult = mysql_fetch_array($intro_dbQuery);
$intro_content = $intro_dbResult['texte_intro'];

/*** Determines the correct display ***/

if (isset($intro_cmdEdit) || isset($intro_cmdAdd)) {
	$intro_dispDefault = false;
	$intro_dispForm = true;
	$intro_dispCommand = false;
} else {
	$intro_dispDefault = true;
	$intro_dispForm = false;

	if ($intro_editAllowed) {
		$intro_dispCommand = true;
	} else {
		$intro_dispCommand = false;
	}
}


/*** Executes the display ***/

if ($intro_dispForm) {

// HTML area editor

echo "<form action='$_SERVER[PHP_SELF]' method='post' id='edit' name='edit'>\n";
echo "<textarea id='ta' name='intro_content' value='$intro_content' style='width:100%' rows='20' cols='80'>$intro_content</textarea>";
echo "<input type='submit' name='intro_cmdUpdate' value='$langModify'>";
echo "<br></form>";

}

if ($intro_dispDefault) {
	$intro_content = nl2br(($intro_content));
	$intro_content = make_clickable($intro_content); // make url in text clickable
	include "$webDir".'/modules/latexrender/latex.php';
	$intro_content=latex_content($intro_content);
	echo "<p>\n",$intro_content,"\n","</p>\n";
}

if ($intro_dispCommand) {
	if(empty($intro_content)) // displays "Add intro" Commands
	{  
		echo	"<p>\n",
				"<small>\n",
				"<a href=\"$_SERVER[PHP_SELF]?intro_cmdAdd=1\">\n",$langAddIntro,"</a>\n",
				"</small>\n",
				"</p>\n";
	}
	else // displays "edit intro && delete intro" Commands
	{  
		echo	"<p>\n",
				"<small>\n",
				"<a href=\"$_SERVER[PHP_SELF]?intro_cmdEdit=1\">
					<font color=\"#808080\">",$langModify,"</font></a>\n",
				" | \n",
				"<a href=\"$_SERVER[PHP_SELF]?intro_cmdDel=1\">
					<font color=\"#808080\">",$langDelete,"</font></a>\n",
				"</small>\n",
				"</p>\n";
	}
}

?>

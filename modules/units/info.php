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

/*
Units module	
*/

$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'AddCourseUnits';
include '../../include/baseTheme.php';

$nameTools = $langEditUnit;
$tool_content = $head_content = "";
$lang_editor = langname_to_code($language);
$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyUnitTitle");
		return false;
	} else {
		return true;
	}
}

</script>
<script type="text/javascript" src="$urlAppend/include/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
	// General options
		language : "$lang_editor",
		mode : "textareas",
		theme : "advanced",
		plugins : "pagebreak,style,save,advimage,advlink,inlinepopups,media,print,contextmenu,paste,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,emotions,preview",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontsizeselect,forecolor,backcolor,removeformat,hr",
		theme_advanced_buttons2 : "pasteword,|,bullist,numlist,|indent,blockquote,|,sub,sup,|,undo,redo,|,link,unlink,|,charmap,media,emotions,image,|,preview,cleanup,code",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "$urlAppend/template/classic/img/tool.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],

		// Replace values for the template plugin
		template_replace_values : {
			username : "Open eClass",
			staffid : "991234"
		}
});
</script>
hContent;

if (!$is_adminOfCourse) { // check teacher status
        $tool_content .= $langNotAllowed;
        draw($tool_content, 2, '', $head_content);
        exit;
}

if (isset($_GET['edit'])) { // display form for editing course unit
        $id = intval($_GET['edit']); 
        $sql = db_query("SELECT id, title, comments FROM course_units WHERE id='$id'");
        $cu = mysql_fetch_array($sql);
        $unittitle = " value='" . htmlspecialchars($cu['title'], ENT_QUOTES) . "'";
        $unitdescr = $cu['comments'];
        $unit_id = $cu['id'];
        $button = $langEdit;
} else {
        $nameTools = $langAddUnit;
        $button = $langAdd;
        $unitdescr = $unittitle = '';
}

if (isset($_GET['next'])) {
        $action = "index.php?id=$unit_id";
} else {
        $action = "${urlServer}courses/$currentCourseID/";
}

$tool_content .= "<form method='post' action='$action'
        onsubmit=\"return checkrequired(this, 'unittitle');\">";
if (isset($unit_id)) {
        $tool_content .= "<input type='hidden' name='unit_id' value='$unit_id'>";
}
$tool_content .= "<table width='99%' class='FormData' align='center'><tbody>
        <tr><th width='220'>&nbsp;</th>
            <td><b>$nameTools</b></td></tr>
        <tr><th width='150' class='left'>$langUnitTitle:</th>
            <td><input type='text' name='unittitle' size='50' maxlength='255' $unittitle class='FormData_InputText'></td></tr>
        <tr><th class='left'>$langUnitDescr:</th><td>
        <table class='xinha_editor'><tr><td>".
        rich_text_editor('unitdescr', 4, 20, $unitdescr)
        ."</td></tr>
        </table></td></tr>
        <tr><th>&nbsp;</th>
            <td><input type='submit' name='edit_submit' value='$button'></td></tr>
</tbody></table>
</form>";
draw($tool_content, 2, 'units', $head_content);


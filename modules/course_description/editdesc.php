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
 * Edit, Course Description
 *
 */

$require_current_course = TRUE;
$require_login = true;
$require_prof = true;

include '../../include/baseTheme.php';
include '../units/functions.php';

$tool_content = $head_content = "";
$nameTools = $langEditCourseProgram ;
$navigation[] = array ('url' => 'index.php', 'name' => $langCourseProgram);

$lang_editor = langname_to_code($language);

$head_content = <<<hContent
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

add_html_editor();

if (!$is_adminOfCourse) {
        header('Location: ' . $urlServer);
        exit;
}

mysql_select_db($mysqlMainDb);

if (isset($_POST['submit'])) {
        $unit_id = description_unit_id($cours_id);
        add_unit_resource($unit_id, 'description', -1, $langDescription, trim(autounquote($_POST['description'])));
}

$description = '';
$unit_id = description_unit_id($cours_id);
$q = db_query("SELECT id, res_id, comments FROM unit_resources WHERE unit_id = $unit_id
                      AND (res_id < 0 OR `order` < 0)");
if ($q and mysql_num_rows($q) > 0) {
        while ($row = mysql_fetch_array($q)) {
                if ($row['res_id'] == -1) {
                        $description = $row['comments'];
                } else {
                        $new_order = add_unit_resource_max_order($unit_id);
                        $new_res_id = new_description_res_id($unit_id);
                        db_query("UPDATE unit_resources SET
                                         res_id = $new_res_id, visibility = 'v', `order` = $new_order
                                  WHERE id = $row[id]");
                }
        }
}

$tool_content = "

 <form method='post' action='index.php'>
 <input type='hidden' name='edIdBloc' value='-1' />
 <input type='hidden' name='edTitleBloc' value='$langDescription' />
   <fieldset>
   <legend>$langDescription</legend>
         " . rich_text_editor('edContentBloc', 4, 20, $description) . "
   <br />
   <input type='submit' name='submit' value='$langSubmit' />
   </fieldset>
 </form>\n";

draw($tool_content, 2, '', $head_content);


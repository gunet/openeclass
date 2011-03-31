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
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract Actions for add/edit/delete portions of a course's descriptions
 *
 * Based on course units code
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Coursedescription';
$require_login = true;
$require_prof = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';

$tool_content = $head_content = '';
$nameTools = $langEditCourseProgram ;
$navigation[] = array ('url' => 'index.php?course='.$code_cours, 'name' => $langCourseProgram);

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

if (!$is_adminOfCourse) {
        header('Location: ' . $urlServer);
        exit;
}

mysql_select_db($mysqlMainDb);

if (isset($_POST['edIdBloc'])) {
        // Save results from block edit (save action)
        $res_id = intval($_POST['edIdBloc']);
        $unit_id = description_unit_id($cours_id);
        add_unit_resource($unit_id, 'description', $res_id,
                          autounquote($_POST['edTitleBloc']),
                          autounquote($_POST['edContentBloc']));
        display_add_block_form();
} elseif (isset($_REQUEST['numBloc'])) {
        // Display block edit form (edit action)
        add_html_editor();
        $numBloc = intval($_REQUEST['numBloc']);
        if (isset($titreBloc[$numBloc])) {
                $title = q($titreBloc[$numBloc]);
        }
        if (isset($title) and @!$titreBlocNotEditable[$numBloc]) {
               $edit_title = " value='$title'"; 
        } else {
               $edit_title = false; 
        }
        if (isset($_POST['add']) and @!$titreBlocNotEditable[$numBloc]) {
                $numBloc = new_description_res_id($cours_id);
                $contentBloc = '';
        } else {
                $q = db_query("SELECT title, comments FROM unit_resources WHERE unit_id =
                                        (SELECT id FROM course_units WHERE course_id = $cours_id AND `order` = -1)
                                        AND res_id = $numBloc");
                if ($q and mysql_num_rows($q)) {
                        list($title, $contentBloc) = mysql_fetch_row($q);
                        if ($edit_title) {
                               $edit_title = " value='$title'"; 
                        }
                } else {
                        $contentBloc = '';
                }
        }
        $tool_content .= "
      <form method='post' action='index.php?course=$code_cours'>
      <input type='hidden' name='edIdBloc' value='$numBloc' />
        <fieldset>
        
        <table class='tbl'>
        <tr>
           <th width='100'>$langTitle:</th>";
        if ($edit_title) {
                $tool_content .= "
           <td><input type='text' name='edTitleBloc' $edit_title /></td>
        </tr>";
        } else {
                $tool_content .= "
           <td><b>$title</b><input type='hidden' name='edTitleBloc' value='$title' /></td>
        </tr>";
        }

        $tool_content .= "
        <tr>
           <th valign='top'>$langContent:</th>
           <td>".
                    @rich_text_editor('edContentBloc', 4, 20, $contentBloc)
                    ."</td>
        </tr>
        <tr>
           <td>&nbsp;</td>
           <td class='right'><input class='Login' type='submit' name='save' value='$langAdd' />&nbsp;&nbsp;
              <input class='Login' type='submit' name='ignore' value='$langBackAndForget' />
           </td>
        </tr>
        </table>
      </fieldset>
      </form>\n";
} else {
        display_add_block_form();
}

draw($tool_content, 2, '', $head_content);


// Display form to to add a new block
function display_add_block_form()
{
        global $cours_id, $code_cours, $tool_content, $titreBloc, $langAddCat, $langAdd, $langSelection, $titreBlocNotEditable;
        $q = db_query("SELECT res_id FROM unit_resources WHERE unit_id =
                                (SELECT id FROM course_units WHERE course_id = $cours_id AND `order` = -1)
                       ORDER BY `order`");
        while ($row = mysql_fetch_row($q)) {
                if (@$titreBlocNotEditable[$row[0]]) {
                        $blocState[$row[0]] = true;
                }
        }

        $tool_content .= "
        <form method='post' action='$_SERVER[PHP_SELF]?course=$code_cours'>
        <input type='hidden' name='add' value='1' />
        <fieldset>
          <legend>$langAddCat</legend>
          <table class='tbl'>
          <tr>
            <th>$langSelection:</th>
            <td><select name='numBloc' size='1'>";
        while (list($numBloc,) = each($titreBloc)) {
                if (!isset($blocState[$numBloc])) {
                        $tool_content .= "\n                <option value='$numBloc'>$titreBloc[$numBloc]</option>\n";
                }
        }
        $tool_content .= "\n                </select>
            </td>
          </tr>
          <tr>
            <th>&nbsp;</th>
            <td><input type='submit' name='add' value='$langAdd' /></td>
          </tr>
          </table>
        </fieldset>  
        </form>\n";
}

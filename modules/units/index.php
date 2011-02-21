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
Units display module	
*/
define('HIDE_TOOL_TITLE', 1);
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'AddCourseUnitscontent';
include '../../include/baseTheme.php';
include '../../include/lib/fileDisplayLib.inc.php';
include '../../include/action.php';
include 'functions.php';

$action = new action();
$action->record('MODULE_ID_UNITS');

mysql_select_db($mysqlMainDb);

if (isset($_REQUEST['id'])) {
	$id = intval($_REQUEST['id']);
}
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
<script type="text/javascript">
function confirmation () {
        if (confirm("'.$langConfirmDelete.'"))
                {return true;}
        else
                {return false;}
}
</script>
hContent;

if (isset($_REQUEST['edit_submit'])) {
        units_set_maxorder();
        $tool_content .= handle_unit_info_edit();
}

$form = process_actions();

if ($is_adminOfCourse) {
        $tool_content .= "\n  <div id='operations_container'>\n    <ul id='opslist'>" .
                        "\n      <li>$langAdd: <a href='insert.php?type=doc&amp;id=$id'>$langInsertDoc</a></li>" .
                        "\n      <li><a href='insert.php?type=exercise&amp;id=$id'>$langInsertExercise</a></li>" .
                        "\n      <li><a href='insert.php?type=text&amp;id=$id'>$langInsertText</a></li>" .
			"\n      <li><a href='insert.php?type=link&amp;id=$id'>$langInsertLink</a></li>" .
			"\n      <li><a href='insert.php?type=lp&amp;id=$id'>$langLearningPath1</a></li>" .
			"\n      <li><a href='insert.php?type=video&amp;id=$id'>$langInsertVideo</a></li>" .
			"\n      <li><a href='insert.php?type=forum&amp;id=$id'>$langInsertForum</a></li>" .
			"\n      <li><a href='insert.php?type=work&amp;id=$id'>$langInsertWork</a></li>" .
			"\n      <li><a href='insert.php?type=wiki&amp;id=$id'>$langInsertWiki</a></li>" .
                        "\n    </ul>\n  </div>\n" .
		        $form;
}

if ($is_adminOfCourse) {
        $visibility_check = '';
} else {
        $visibility_check = "AND visibility='v'";
}
$q = db_query("SELECT * FROM course_units
               WHERE id = $id AND course_id=$cours_id " . $visibility_check);
if (!$q or mysql_num_rows($q) == 0) {
        $nameTools = $langUnitUnknown;
        draw('', 2, '', $head_content);
        exit;
}
$info = mysql_fetch_array($q);
$nameTools = htmlspecialchars($info['title']);
$comments = trim($info['comments']);

// Links for next/previous unit
foreach (array('previous', 'next') as $i) {
        if ($i == 'previous') {
                $op = '<=';
                $dir = 'DESC';
                $arrow1 = '« ';
                $arrow2 = '';
        } else {
                $op = '>=';
                $dir = '';
                $arrow1 = '';
                $arrow2 = ' »';
        }
        $q = db_query("SELECT id, title FROM course_units
                       WHERE course_id = $cours_id
                             AND id <> $id
                             AND `order` $op $info[order]
                             AND `order` >= 0
                             $visibility_check
                       ORDER BY `order` $dir
                       LIMIT 1");
        if ($q and mysql_num_rows($q) > 0) {
                list($q_id, $q_title) = mysql_fetch_row($q);
                $q_title = htmlspecialchars($q_title);
                $link[$i] = "<a href='$_SERVER[PHP_SELF]?id=$q_id'>$arrow1$q_title$arrow2</a>";
        } else {
                $link[$i] = '&nbsp;';
        }
}

if ($is_adminOfCourse) {
        $comment_edit_link = "<td valign='top' width='20'><a href='info.php?edit=$id&amp;next=1'><img src='../../template/classic/img/edit.png' title='' alt='' /></a></td>";
        $units_class = 'tbl';
} else {
        $units_class = 'tbl';
        $comment_edit_link = '';
}

$tool_content .= "
    <table class='$units_class' width='99%'>
    <tr class='odd'>
      <td class='left'>" .  $link['previous'] . '</td>
      <td class="right">' .  $link['next'] . "</td>
    </tr>
    <tr>
      <td colspan='2' class='unit_title'>$nameTools</td>
    </tr>
    </table>\n";


if (!empty($comments)) {
        $tool_content .= "
    <table class='tbl' width='99%'>
    <tr class='even'>
      <td>$comments</td>
      $comment_edit_link
    </tr>
    </table>";
}

show_resources($id);

$tool_content .= '
  <form name="unitselect" action="' .  $urlServer . 'modules/units/" method="get">';
$tool_content .="
    <table width='99%' class='tbl'>
     <tr class='odd'>
       <td class='right'>".$langCourseUnits.":&nbsp;</td>
       <td width='50' class='right'>".
                 "<select name='id' onChange='document.unitselect.submit();'>";
$q = db_query("SELECT id, title FROM course_units
               WHERE course_id = $cours_id AND `order` > 0
                     $visibility_check
               ORDER BY `order`", $mysqlMainDb);
while ($info = mysql_fetch_array($q)) {
        $selected = ($info['id'] == $id)? ' selected="1" ': '';
        $tool_content .= "<option value='$info[id]'$selected>" .
                         htmlspecialchars(ellipsize($info['title'], 40)) .
                         '</option>';
}
$tool_content .= "</select>
       </td>
     </tr>
    </table>
 </form>";

draw($tool_content, 2, '', $head_content);


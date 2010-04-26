<?
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
include '../../include/phpmathpublisher/mathpublisher.php';
include '../units/functions.php';

$tool_content = $head_content = "";
$nameTools = $langEditCourseProgram ;
$navigation[] = array ('url' => 'index.php', 'name' => $langCourseProgram);

$lang_editor = langname_to_code($language);

$head_content .= <<<hCont
<script type="text/javascript">
function confirmation ()
{
    if (confirm('$langConfirmDelete'))
        {return true;}
    else
        {return false;}
}
</script>
hCont;

$body_action = 'onload="initEditor()"';

if (!$is_adminOfCourse) {
        header('Location: ' . $urlServer);
        exit;
}

mysql_select_db($mysqlMainDb);

process_actions();

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
        if (!isset($titreBlocNotEditable[$numBloc]) or !$titreBlocNotEditable[$numBloc]) {
               $edit_title = " value='$title'"; 
        } else {
               $edit_title = false; 
        }
        if (isset($_POST['add'])) {
                $q = db_query("SELECT MAX(res_id) FROM unit_resources WHERE unit_id =
                        (SELECT id FROM course_units WHERE course_id = $cours_id AND `order` = -1)");
                list($max_res_id) = mysql_fetch_row($q);
                $numBloc = 1 + max(count($titreBloc), $max_res_id);
                $contentBloc = '';
        } else {
                $q = db_query("SELECT title, comments FROM unit_resources WHERE unit_id =
                                        (SELECT id FROM course_units WHERE course_id = $cours_id AND `order` = -1)
                                        AND res_id = $numBloc");
                if ($q and mysql_num_rows($q)) {
                        list($old_title, $contentBloc) = mysql_fetch_row($q);
                        if ($edit_title) {
                               $edit_title = " value='$old_title'"; 
                        }
                } else {
                        $contentBloc = '';
                }
        }
        $tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]'>
                <input type='hidden' name='edIdBloc' value='$numBloc' />
                <table width='99%' class='FormData' align='left'><tbody>
                   <tr><th class='left' width='220'>$langTitle:</th>
                       <td><b>$title</b>";
        if ($edit_title) {
                $tool_content .= "</td></tr><tr><th class='left'>&nbsp;</th>
                    <td><input type='text' name='edTitleBloc' $edit_title />
                        </td></tr>";
        } else {
                $tool_content .= "<input type='hidden' name='edTitleBloc' value='$title' /></td></tr>";
        }

        $tool_content .= "
                <tr><th class='left'>&nbsp;</th>
                    <td><table class='xinha_editor'>
                    <tr><td>".
                    @rich_text_editor('edContentBloc', 4, 20, $contentBloc)
                    ."</td></tr></table></td></tr>
                <tr><th class='left'>&nbsp;</th>
                    <td><input type='submit' name='save' value='$langAdd' />&nbsp;&nbsp;
                        <input type='submit' name='ignore' value='$langBackAndForget' /></td></tr>
            </tbody></table></form>\n";
} else {
        display_add_block_form();
}

show_resources(description_unit_id($cours_id));

draw($tool_content, 2, 'course_description', $head_content, $body_action);


// Display form to to add a new block
function display_add_block_form()
{
        global $cours_id, $tool_content, $titreBloc, $langAddCat, $langAdd, $langSelection;
        $q = db_query("SELECT res_id FROM unit_resources WHERE unit_id =
                                (SELECT id FROM course_units WHERE course_id = $cours_id AND `order` = -1)
                                AND res_id > 0 ORDER BY `order`");
        while ($row = mysql_fetch_row($q)) {
                if (@$titreBlocNotEditable[$row[0]]) {
                        $blocState[$row[0]] = true;
                }
        }

        $tool_content .= "
        <form method='post' action='$_SERVER[PHP_SELF]'>
        <input type='hidden' name='add' value='1' />
        <table width='99%' align='left' class='FormData'>
        <tbody>
        <tr>
        <th class='left' width='220'>&nbsp;</th>
        <td><b>$langAddCat</b></td>
        </tr>
        <tr>
        <th class='left'>$langSelection :</th>
        <td><select name='numBloc' size='1' class='auth_input'>";
        while (list($numBloc,) = each($titreBloc)) {
                if (!isset($blocState[$numBloc])) {
                        $tool_content .= "<option value='$numBloc'>$titreBloc[$numBloc]</option>\n";
                }
        }
        $tool_content .= "</select></td></tr><tr><th>&nbsp;</th>
                <td><input type='submit' name='add' value='$langAdd' /></td>
                </tr></tbody></table>
                </form>\n";
}

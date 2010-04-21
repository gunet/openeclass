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
 * Based on previous code of eclass 1.6
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Coursedescription';
$require_login = true;
$require_prof = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
// support for math symbols
include '../../include/phpmathpublisher/mathpublisher.php';

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

$head_content .= <<<hCont
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>
hCont;

$body_action = 'onload="initEditor()"';

mysql_select_db($_SESSION['dbname']);

if ($is_adminOfCourse) {
        if (isset($_POST['save'])) {
                if ($_POST['edIdBloc'] == 'add') {
                        $res = db_query("SELECT MAX(id) FROM course_description");
                        list($max_id) = mysql_fetch_row($res);
                        $new_id = max(sizeof($titreBloc), $max_id) + 1;
                } else {
                        $new_id = intval($_POST['edIdBloc']);
                }
                if (empty($edTitleBloc)) {
                        $edTitleBloc = $titreBloc[$edIdBloc];
                }
                db_query("INSERT IGNORE INTO course_description SET id = $new_id");
                db_query("UPDATE course_description
                                SET title = " . autoquote(trim($edTitleBloc)) . ",
                                    content = " . autoquote(trim($edContentBloc)) . ",
                                    `upDate` = NOW()
                                WHERE id = $new_id");
                header('Location: ' . $urlServer . 'modules/course_description/edit.php');
                exit;
        } elseif (isset($_GET['delete'])) {
                $del_id = intval($_GET['numBloc']);
		$res = db_query("DELETE FROM course_description WHERE id = $del_id");
		$tool_content .= "<p class='success'>$langBlockDeleted<br /><br /><a href='$_SERVER[PHP_SELF]'>$langBack</a></p>";

        } elseif (isset($_REQUEST['numBloc'])) {
                // Edit action
                $edit_id = intval($_REQUEST['numBloc']);
                $numBlock = $edit_id;
                $res = db_query("SELECT * FROM course_description WHERE id = $edit_id");
                $title = '';
                if ($res and mysql_num_rows($res) > 0) {
                        $blocs = mysql_fetch_array($res);
                        $title = q($blocs['title']);
                        $contentBloc = $blocs["content"];
                } else {
                        if (isset($titreBloc[$edit_id])) {
                                $title = q($titreBloc[$edit_id]);
                        }
                        if (!isset($titreBlocNotEditable[$edit_id]) or !$titreBlocNotEditable[$numBloc]) {
                                $numBloc = 'add';
                        }
                }

                $tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]'>
                        <input type='hidden' name='edIdBloc' value='$numBloc' />
                        <table width='99%' class='FormData' align='left'><tbody>
                           <tr><th class='left' width='220'>$langTitle:</th>
                               <td><b>$title</b>";
                if (!isset($titreBlocNotEditable[$edit_id]) or !$titreBlocNotEditable[$numBloc]) {
                        $tool_content .= "</td></tr><tr><th class='left'>&nbsp;</th>
                            <td><input type='text' name='edTitleBloc' value='$title' />
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
                $sql = "SELECT * FROM `course_description` order by id";
                $res = db_query($sql,$db);
                while($bloc = mysql_fetch_array($res)) {
                        $blocState[$bloc["id"]] = "used";
                        $titreBloc[$bloc["id"]]	= $bloc["title"];
                        $contentBloc[$bloc["id"]] = $bloc["content"];
                }
                $tool_content .= "
    <form method='post' action='$_SERVER[PHP_SELF]'>

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
			if (!isset($blocState[$numBloc])||$blocState[$numBloc] != "used")
				$tool_content .= "\n            <option value='".$numBloc."'>".$titreBloc[$numBloc]."</option>";
		}
		$tool_content .= "\n</select></td></tr><tr><th>&nbsp;</th>
      		<td><input type='submit' name='add' value='$langAdd' /></td>
    		</tr></tbody></table>
    		<p>&nbsp;</p>
    </form>\n";

	reset($titreBloc);
		while (list($numBloc,) = each($titreBloc)) {
			if (isset($blocState[$numBloc]) && $blocState[$numBloc]=="used") {
				$tool_content .= "<table width='99%' class='CourseDescr'>
    					<thead><tr><td>
        				<table width='100%' class='FormData'>
        				<thead><tr>
          				<th class='left' style='border: 1px solid #CAC3B5;'>".$titreBloc[$numBloc].":</th>
          				<td width='50' class='right'>
					<a href='".$_SERVER['PHP_SELF']."?numBloc=".$numBloc."' >
					<img src='../../template/classic/img/edit.gif' border='0' title='$langModify' /></a>&nbsp;&nbsp;";
					$tool_content .= "<a href='$_SERVER[PHP_SELF]?delete=yes&amp;numBloc=$numBloc' onClick='return confirmation();'><img src='../../images/delete.gif' border='0' title='$langDelete' /></a>&nbsp;</td></tr></thead></table>
      					</td></tr><tr>
      				<td>".mathfilter(make_clickable(nl2br($contentBloc[$numBloc])), 12, "../../courses/mathimg/")."</td>
    				</tr></thead></table>";
				$tool_content .= "<br />";
			}
		}
	}
} else {
	exit();
}

if(isset($numBloc)) {
	draw($tool_content, 2, 'course_description', $head_content, $body_action);
} else {
	draw($tool_content, 2, 'course_description', $head_content);
}

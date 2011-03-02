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

$require_current_course = true;

include '../../include/baseTheme.php';

/*
 * *** The following is added for statistics purposes **
 */
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_GLOSSARY');

mysql_select_db($mysqlMainDb);

if ($is_adminOfCourse) {
    $head_content .= '
    <script type="text/javascript">
    function confirmation ()
    {
            if (confirm("'. $langConfirmDelete .'"))
                {return true;}
            else
                {return false;}	
    }
    
    </script>
    ';
}

$nameTools = $langGlossary;

/********************************************
 *Actions*
********************************************/

if ($is_adminOfCourse) {
	if (isset($_POST['url'])) {
		$url = trim($_POST['url']);
		if (!empty($url)) {
			$url = canonicalize_url($url);
		}
	} else {
		$url = '';
	}
    
    if (isset($_POST['submit'])) {
        db_query("INSERT INTO glossary SET term = " .
                    autoquote(trim($_POST['term'])) . ", definition = " .
                    autoquote(trim($_POST['definition'])) . ", url = " .
                    autoquote($url) . ", `order` = " .
                    findorder($cours_id ) .", datestamp = NOW(), course_id = $cours_id");
        invalidate_glossary_cache();
        $tool_content .= "<div class='success'>$langGlossaryAdded</div><br />";
    }
    if (isset($_POST['edit_submit'])) {
        $id = intval($_POST['id']);
        $sql = db_query("UPDATE glossary SET term = " .
                            autoquote(trim($_POST['term'])) . ", definition = " .
                            autoquote(trim($_POST['definition'])) . ", url = " .
                            autoquote($url) . ",
                            datestamp = NOW()
                        WHERE id = $id AND course_id = $cours_id");
        invalidate_glossary_cache();
        if (mysql_affected_rows() > 0) {
            $tool_content .= "<div class='success'>$langGlossaryUpdated</div><br />";    
        }
    }
    if (isset($_GET['delete'])) {
        $sql = db_query("DELETE FROM glossary WHERE id = '$_GET[delete]' AND course_id = $cours_id");
        invalidate_glossary_cache();
        if (mysql_affected_rows() > 0) {
            $tool_content .= "<div class='success'>$langGlossaryDeleted</div><br />";    
        }
    }
    $tool_content .= "
       <div id='operations_container'>
         <ul id='opslist'>
           <li><a href='" . $_SERVER['PHP_SELF'] . "?add=1'>" . $langAddGlossaryTerm . "</a></li>
         </ul>
       </div>";
    
    // display form for adding a glossary term
    if (isset($_GET['add']))  {
        $term = $definition = $url = '';
        $navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langGlossary);
        $nameTools = $langAddGlossaryTerm;
        
        $tool_content .= "
              <form action='$_SERVER[PHP_SELF]' method='post'>
               <fieldset>
                 <legend>$langAddGlossaryTerm</legend>
                 <table class='tbl'>
                 <tr>
                   <th>$langGlossaryTerm</th>
                   <td>
                     <input type='text' name='term' value='$term' size='60'>
                   </td>
                 </tr>
                 <tr>
                   <th valign='top'>$langGlossaryDefinition</th>
                   <td>\n";
        $tool_content .= text_area('definition', 4, 60, $definition);
        $tool_content .= "\n
                   </td>
                 </tr>
                 <tr>
                   <th>$langGlossaryUrl</th>
                 <td>
                 <input type='text' name='url' value='$url' size='50'>
                 </td>
                 </tr>
                 <tr>
                   <th>&nbsp;</th>
                   <td><input type='submit' name='submit' value='$langSubmit'></td>
                 </tr>
                 </table>
               </fieldset>
              </form>
              <br />\n";    
    }
    
    // display form for editiong a glossary term
    if (isset($_GET['edit']))  {
        $navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langGlossary);
        $nameTools = $langEditGlossaryTerm;
        
        $sql = db_query("SELECT term, definition, url FROM glossary WHERE id='$_GET[edit]'");
        $data = mysql_fetch_array($sql);
        
        $tool_content .= "
               <form action='$_SERVER[PHP_SELF]' method='post'>
               <fieldset>
                 <legend>$langModify</legend>
                 <table class='tbl'>
                 <tr>
                   <th>$langGlossaryTerm</th>
                   <td><input type='text' name='term' value='$data[term]' size='60'></td>
                 </tr>
                 <tr>
                   <th valign='top'>$langGlossaryDefinition</th>
                   <td valign='top'>\n";
        $tool_content .= text_area('definition', 4, 60, $data['definition']);
        $tool_content .= "\n
                   </td>
                 </tr>
                 <tr><th>$langGlossaryUrl</th>
                 <td>
                 <input type='text' name='url' value='$data[url]' size='50'>
                 </td>
                 </tr>
                 <tr>
                   <th>&nbsp;</th>
                   <td>
                    <input type='submit' name='edit_submit' value='$langModify'>
                    <input type = 'hidden' name='id' value='$_GET[edit]'>
                   </td>
                 </tr>
                 </table>
               </fieldset>
               <br />\n";    
    }
}

/*************************************************
// display glossary
*************************************************/

$tool_content .= "
                 <table class='tbl_alt_bordless' width='99%'>";
/*
$tool_content .= "
                 <tr>
                   <th><div align='left'>$langGlossaryTerm - $langGlossaryDefinition</div></th>";
if ($is_adminOfCourse) {
    $tool_content .= "
                   <th width='20'>$langActions</th>";
}
$tool_content .= "</tr>";
*/
$sql = db_query("SELECT id, term, definition, url FROM glossary WHERE course_id = '$cours_id'");
$i=0;
while ($g = mysql_fetch_array($sql)) {
                if ($i%2) {
                    $rowClass = "class='odd'";
                } else {
                    $rowClass = "class='even'";
                }
        if (!empty($g['url'])) {
            $urllink = "(<a href='" . q($g['url']) .
                       "' target='_blank'>" . q($g['url']) . "</a>)";
        } else {
            $urllink = '';
        }
    $tool_content .= "
                 <tr $rowClass>
                   <td><strong>" . q($g['term']) . "</strong> 
                    <br /><em>" . q($g['definition']) . "</em><br /><span align='left' class='smaller'> $urllink</span>
                   </td>";
    if ($is_adminOfCourse) {
        $tool_content .= "
                   <td align='center' valign='top' width='50'><a href='$_SERVER[PHP_SELF]?edit=$g[id]'>
                   <img src='../../template/classic/img/edit.png' /></a>
                   <a href='$_SERVER[PHP_SELF]?delete=$g[id]' onClick=\"return confirmation();\">
                   <img src='../../template/classic/img/delete.png' /></a></td>";
    }
    $tool_content .= "
                 </tr>";
    $i++;
}
$tool_content .= "
                 </table>
                 <br />\n";

draw($tool_content, 2, '', $head_content);


/*******************************************/
function findorder($course_id)
{
    $sql = db_query("SELECT MAX(`ORDER`) FROM glossary WHERE course_id = $course_id");
    list($maxorder) = mysql_fetch_row($sql);
    if ($maxorder > 0) {
        $maxorder++;
        return $maxorder;
    } else {
        $maxorder = 1;
        return $maxorder;
    }                         
}

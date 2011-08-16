<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$require_current_course = true;
$require_help = true;
$helpTopic = 'Glossary';

include '../../include/baseTheme.php';

/*
 * *** The following is added for statistics purposes **
 */
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_GLOSSARY');

mysql_select_db($mysqlMainDb);

if ($is_adminOfCourse) {
        load_js('tools.js');
}

$nameTools = $langGlossary;

$categories = array();
$q = db_query("SELECT id, name, description, `order`
                      FROM glossary_category WHERE course_id = $cours_id
                      ORDER BY `order`");
while ($cat = mysql_fetch_assoc($q)) {
        $categories[intval($cat['id'])] = $cat['name'];
}
if (isset($_GET['cat'])) {
        $cat_id = intval($_GET['cat']);
} else {
        $cat_id = false;
}

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

        if (isset($_POST['submit_config'])) {
                db_query("UPDATE cours SET expand_glossary = " . (isset($_POST['expand'])? 1: 0));
                invalidate_glossary_cache();
                $tool_content .= "<div class='success'>$langQuotaSuccess</div>";
        }

        if (isset($_POST['submit_category'])) {
                if (isset($_POST['category_id'])) {
                        $category_id = intval($_POST['category_id']);
                        $q = db_query("UPDATE glossary_category
                                              SET name = " . autoquote($_POST['name']) . ",
                                                  description = " . autoquote($_POST['description']) . "
                                              WHERE id = $category_id AND course_id = $cours_id");
                        $success_message = $langCategoryModded;
                } else {
                        db_query("SELECT @new_order := (1 + IFNULL(MAX(`order`),0))
                                         FROM glossary_category WHERE course_id = $cours_id");
                        $q = db_query("INSERT INTO glossary_category
                                              SET name = " . autoquote($_POST['name']) . ",
                                                  description = " . autoquote($_POST['description']) . ",
                                                  course_id = $cours_id,
                                                  `order` = @new_order");
                        $category_id = mysql_insert_id();
                        $success_message = $langCategoryAdded;
                } 
                if ($q and mysql_affected_rows()) {
                        $categories[$category_id] = autounquote($_POST['name']);
                        $tool_content .= "<div class='success'>$success_message</div><br />";
                }
        }

        if (isset($_POST['submit'])) {
                if ($_POST['category_id'] == 'none') {
                        $category_id = 'NULL';
                } else {
                        $category_id = intval($_POST['category_id']);
                }
                if (isset($_POST['id'])) {
                        $id = intval($_POST['id']);
                        $q = db_query("UPDATE glossary
                                              SET term = " . autoquote($_POST['term']) . ",
                                                  definition = " . autoquote($_POST['definition']) . ",
                                                  url = " . autoquote($url) . ",
                                                  notes = " . autoquote($_POST['notes']) . ",
                                                  category_id = $category_id,
                                                  datestamp = NOW()
                                              WHERE id = $id AND course_id = $cours_id");
                        $success_message = $langGlossaryUpdated;
                } else {
                        $q = db_query("INSERT INTO glossary
                                              SET term = " . autoquote($_POST['term']) . ",
                                                  definition = " . autoquote($_POST['definition']) . ",
                                                  url = " . autoquote($url) . ",
                                                  notes = " . autoquote($_POST['notes']) . ",
                                                  category_id = $category_id,
                                                  datestamp = NOW(),
                                                  course_id = $cours_id,
                                                  `order` = " . findorder($cours_id));
                        $success_message = $langGlossaryAdded;
                } 
                if ($q and mysql_affected_rows()) {
                        invalidate_glossary_cache();
                        $tool_content .= "<div class='success'>$success_message</div><br />";
                }
        }

        if (isset($_GET['delete'])) {
                $q = db_query("DELETE FROM glossary WHERE id = '$_GET[delete]' AND course_id = $cours_id");
                invalidate_glossary_cache();
                if ($q and mysql_affected_rows()) {
                        $tool_content .= "<div class='success'>$langGlossaryDeleted</div><br />";    
                }
        }

        $tool_content .= "
       <div id='operations_container'>
         <ul id='opslist'>
           <li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;add=1'>$langAddGlossaryTerm</a></li>
           <li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;add_category=1'>$langCategoryAdd</a></li>
           <li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;config=1'>$langConfig</a></li>
           <li>$langGlossaryToCsv (<a href='dumpglossary.php?course=$code_cours'>UTF8</a>&nbsp;-&nbsp;<a href='dumpglossary.php?course=$code_cours&amp;enc=1253'>Windows 1253</a>)</li>  
         </ul>
       </div>";

        // display configuration form
        if (isset($_GET['config']))  {
                $navigation[] = array("url" => "$_SERVER[PHP_SELF]?course=$code_cours", "name" => $langGlossary);
                $nameTools = $langConfig;
                list($expand) = mysql_fetch_row(db_query("SELECT expand_glossary FROM `$mysqlMainDb`.cours
                        WHERE cours_id = $cours_id"));
                $checked = $expand? ' checked="1"': '';
                $tool_content .= "
              <form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>
               <fieldset>
                 <legend>$langConfig</legend>
                 <table class='tbl' width='100%'>
                 <tr>
                   <th>$langGlossaryExpand:</th>
                   <td>
                     <input type='checkbox' name='expand' value='yes'$checked>
                   </td>
                 </tr>
                 <tr>
                   <th>&nbsp;</th>
                   <td class='right'><input type='submit' name='submit_config' value='$langSubmit'></td>
                 </tr>
                 </table>
               </fieldset>
              </form>\n";    
        }

        // display form for adding or editing a category
        if (isset($_GET['add_category']) or isset($_GET['edit_category'])) {
                $navigation[] = array('url' => "$_SERVER[PHP_SELF]?course=$code_cours", 'name' => $langGlossary);
                $html_id = $html_name = $description = '';
                if (isset($_GET['add_category'])) {
                        $nameTools = $langCategoryAdd;
                        $submit_value = $langSubmit;
                } else {
                        $nameTools = $langCategoryMod;
                        $cat_id = intval($_GET['edit_category']);
                        $q = db_query("SELECT name, description
                                              FROM glossary_category WHERE id = $cat_id");
                        if (mysql_num_rows($q)) {
                                $data = mysql_fetch_assoc($q);
                                $html_name = " value='" . q($data['name']) . "'";
                                $html_id = "<input type = 'hidden' name='category_id' value='$cat_id'>";
                                $description = q($data['description']);
                        }
                        $submit_value = $langModify;
                }
                $tool_content .= "
             <form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>
               $html_id
               <fieldset>
                 <legend>$nameTools</legend>
                 <table class='tbl' width='100%'>
                 <tr>
                   <th>$langCategoryName:</th>
                   <td>
                     <input name='name' size='60'$html_name>
                   </td>
                 </tr>
                 <tr>
                   <th valign='top'>$langDescription:</th>
                   <td valign='top'>" . rich_text_editor('description', 4, 60, $description) . "
                   </td>
                 </tr>
                 <tr>
                   <th>&nbsp;</th>
                   <td class='right'><input type='submit' name='submit_category' value='$submit_value'></td>
                 </tr>
                 </table>
               </fieldset>
             </form>\n";    
        }

        // display form for adding or editing a glossary term
        if (isset($_GET['add']) or isset($_GET['edit'])) {
                $navigation[] = array('url' => "$_SERVER[PHP_SELF]?course=$code_cours",
                                      'name' => $langGlossary);
                $html_id = $html_term = $html_url = $definition = $notes = '';
                $category_id = null;
                if (isset($_GET['add'])) {
                        $nameTools = $langAddGlossaryTerm;
                        $submit_value = $langSubmit;
                } else {
                        $nameTools = $langEditGlossaryTerm;
                        $id = intval($_GET['edit']);
                        $q = db_query("SELECT term, definition, url, notes, category_id
                                              FROM glossary WHERE id = $id");
                        if (mysql_num_rows($q)) {
                                $data = mysql_fetch_assoc($q);
                                $html_id = "<input type = 'hidden' name='id' value='$id'>";
                                $html_term = " value='" . q($data['term']) . "'";
                                $html_url = " value='" . q($data['url']) . "'";
                                $definition = q($data['definition']);
                                $notes = q($data['definition']);
                        }
                        $submit_value = $langModify;
                }
                if ($categories) {
                        $categories['none'] = '-';
                        $category_selection = "
                         <tr>
                           <th valign='top'>$langCategory:</th>
                           <td valign='top'>" . selection($categories, 'category_id', $category_id) . "
                           </td>
                         </tr>";
                        unset($categories['none']);
                } else {
                        $category_selection = '';
                }

                $tool_content .= "
             <form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>
               $html_id
               <fieldset>
                 <legend>$nameTools</legend>
                 <table class='tbl' width='100%'>
                 <tr>
                   <th width='90'>$langGlossaryTerm:</th>
                   <td>
                     <input type='text' name='term' size='60'$html_term>
                   </td>
                 </tr>
                 <tr>
                   <th valign='top'>$langGlossaryDefinition:</th>
                   <td valign='top'>" . text_area('definition', 4, 60, $definition) . "
                   </td>
                 </tr>
                 <tr>
                   <th>$langGlossaryUrl:</th>
                   <td><input type='text' name='url' size='50'$html_url></td>
                 </tr>
                 <tr>
                   <th valign='top'>$langCategoryNotes:</th>
                   <td valign='top'>" . rich_text_editor('notes', 4, 60, $notes) . "
                   </td>
                 </tr>
                 $category_selection
                 <tr>
                   <th>&nbsp;</th>
                   <td class='right'><input type='submit' name='submit' value='$submit_value'></td>
                 </tr>
                 </table>
               </fieldset>
             </form>\n";    
        }
}

/*************************************************
// display glossary
*************************************************/

if ($categories) {
        $tool_content .= "<div class='forum_category'><b>$langCategories:</b> ";
        $cat_first = true;
        $edit_icon = '';
        foreach ($categories as $category_id => $category_name) {
                $class = ($category_id == $cat_id)? 'class="today"': '';
                if ($is_adminOfCourse) {
                        $edit_icon = "&nbsp;<a href='$_SERVER[PHP_SELF]?course=$code_cours" .
                                     "&amp;edit_category=$category_id' alt='$langCategoryMod' " .
                                     "title='$langCategoryMod'>" .
                                     "<img src='$themeimg/edit.png'></a>";
                }
                $tool_content .= ($cat_first? '': ', ') .
                                 "<a $class href='$_SERVER[PHP_SELF]?course=$code_cours" .
                                 "&amp;cat=$category_id'>" . q($category_name) . "</a>" .
                                 $edit_icon;
                $cat_first = false;
        }
        $tool_content .= "</div>\n";
}

$where = '';
if (isset($_GET['edit'])) {
        $where = "AND id = $id";
}
if ($cat_id) {
        if (!isset($_GET['edit_category'])) {
                $navigation[] = array('url' => "$_SERVER[PHP_SELF]?course=$code_cours",
                                      'name' => $langGlossary);
                $nameTools = q($categories[$cat_id]);
        }
        $where = "AND category_id = $cat_id";
}
$sql = db_query("SELECT id, term, definition, url, notes
                        FROM glossary WHERE course_id = $cours_id $where
                        ORDER BY category_id, `order`, term");
if (mysql_num_rows($sql) > 0) { 
        $tool_content .= "
	       <script type='text/javascript' src='../auth/sorttable.js'></script>
  <table class='sortable' id='t2' width='100%'>";
	$tool_content .= "
	       <tr>
		 <th><div align='left'>$langGlossaryTerm</div></th>
		 <th><div align='left'>$langGlossaryDefinition</div></th>";
	    if ($is_adminOfCourse) {
		 $tool_content .= "
		 <th width='20'>$langActions</th>";
	    }
	$tool_content .= "
	       </tr>";
	$i=0;
	while ($g = mysql_fetch_array($sql)) {
		if ($i%2) {
		   $rowClass = "class='odd'";
		} else {
		   $rowClass = "class='even'";
		}
		if (!empty($g['url'])) {
		    $urllink = "<br /><span class='smaller'>(<a href='" . q($g['url']) .
			       "' target='_blank'>" . q($g['url']) . "</a>)</span>";
		} else {
		    $urllink = '';
		}

                if (!empty($g['notes'])) {
                        $urllink .= "<br />" . standard_text_escape($g['notes']);
                }

		if (!empty($g['definition'])) {
		    $definition_data = q($g['definition']);
		} else {
		    $definition_data = '-';
		}

	    $tool_content .= "
	       <tr $rowClass>
		 <th width='150'>" . q($g['term']) . "</th> 
                 <td><em>$definition_data</em>$urllink</td>";
	    if ($is_adminOfCourse) {
		$tool_content .= "
		 <td align='center' valign='top' width='50'><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;edit=$g[id]'>
		    <img src='$themeimg/edit.png' /></a>
                    <a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;delete=$g[id]' onClick=\"return confirmation('" .
                        js_escape($langConfirmDelete) . "');\">
		    <img src='$themeimg/delete.png' /></a>
		 </td>";
	    }
	    $tool_content .= "
	       </tr>";
	    $i++;
	}
	$tool_content .= "
	       </table>
	     
	       <br />\n";

} else {
	$tool_content .= "<p class='alert1'>$langNoResult</p>";
}

draw($tool_content, 2, null, $head_content);


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

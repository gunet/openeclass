<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

require_once '../../include/baseTheme.php';
require_once 'modules/video/video_functions.php';

load_modal_box();

$base_url = 'glossary.php?course=' . $course_code;
$cat_url = 'categories.php?course=' . $course_code;

/*
 * *** The following is added for statistics purposes **
 */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_GLOSSARY);

mysql_select_db($mysqlMainDb);

if ($is_editor) {
        load_js('tools.js');
        $max_glossary_terms = get_config('max_glossary_terms');
}

$nameTools = $langGlossary;

$categories = array();
$q = db_query("SELECT id, name, description, `order`
                      FROM glossary_category WHERE course_id = $course_id
                      ORDER BY name");
while ($cat = mysql_fetch_assoc($q)) {
        $categories[intval($cat['id'])] = $cat['name'];
}
if (isset($_GET['cat'])) {
        $cat_id = intval($_GET['cat']);
} else {
        $cat_id = false;
}

list($expand_glossary, $glossary_index) =
        mysql_fetch_row(db_query("SELECT glossary_expand, glossary_index
                                         FROM course WHERE id = $course_id"));
if ($glossary_index) {
        $prefixes = array();
        $q = db_query("SELECT DISTINCT UPPER(LEFT(term, 1)) AS prefix
                              FROM glossary WHERE course_id = $course_id
                              ORDER BY prefix");
        while ($prefix = mysql_fetch_row($q)) {
                $prefix = remove_accents($prefix[0]);
                if (array_search($prefix, $prefixes) === false) {
                        $prefixes[] = $prefix;
                }
        }
}


/********************************************
 *Actions*
********************************************/

if ($is_editor) {
        if (isset($_POST['url'])) {
                $url = trim($_POST['url']);
                if (!empty($url)) {
                        $url = canonicalize_url($url);
                }
        } else {
                $url = '';
        }

        if (isset($_POST['submit_config'])) {
                $expand_glossary = isset($_POST['expand'])? 1: 0;
                db_query("UPDATE course SET glossary_expand = $expand_glossary,
                                           glossary_index = " . (isset($_POST['index'])? 1: 0));
                invalidate_glossary_cache();
                $tool_content .= "<div class='success'>$langQuotaSuccess</div>";
        }

        if (isset($_POST['submit'])) {
                if (!isset($_POST['category_id']) or $_POST['category_id'] == 'none') {
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
                                                  notes = " . autoquote(purify($_POST['notes'])) . ",
                                                  category_id = $category_id,
                                                  datestamp = NOW()
                                              WHERE id = $id AND course_id = $course_id");
                        $success_message = $langGlossaryUpdated;
                } else {
                        $q = db_query("INSERT INTO glossary
                                              SET term = " . autoquote($_POST['term']) . ",
                                                  definition = " . autoquote($_POST['definition']) . ",
                                                  url = " . autoquote($url) . ",
                                                  notes = " . autoquote(purify($_POST['notes'])) . ",
                                                  category_id = $category_id,
                                                  datestamp = NOW(),
                                                  course_id = $course_id,
                                                  `order` = " . findorder($course_id));
                        $success_message = $langGlossaryAdded;
                } 
                if ($q and mysql_affected_rows()) {
                        invalidate_glossary_cache();
                        $tool_content .= "<div class='success'>$success_message</div><br />";
                }
        }

        if (isset($_GET['delete'])) {
                $q = db_query("DELETE FROM glossary WHERE id = '$_GET[delete]' AND course_id = $course_id");
                invalidate_glossary_cache();
                if ($q and mysql_affected_rows()) {
                        $tool_content .= "<div class='success'>$langGlossaryDeleted</div><br />";    
                }
        }

        $tool_content .= "
       <div id='operations_container'>
         <ul id='opslist'>" .
           ($categories? "<li><a href='categories.php?course=$course_code'>$langCategories</a></li>": '') . "
           <li><a href='$base_url&amp;add=1'>$langAddGlossaryTerm</a></li>
           <li><a href='$cat_url&amp;add=1'>$langCategoryAdd</a></li>
           <li><a href='$base_url&amp;config=1'>$langConfig</a></li>
           <li>$langGlossaryToCsv (<a href='dumpglossary.php?course=$course_code'>UTF8</a>&nbsp;-&nbsp;<a href='dumpglossary.php?course=$course_code&amp;enc=1253'>Windows 1253</a>)</li>  
         </ul>
       </div>";

        // display configuration form
        if (isset($_GET['config']))  {
                $navigation[] = array('url' => $base_url, 'name' => $langGlossary);
                $nameTools = $langConfig;
                $checked_expand = $expand_glossary? ' checked="1"': '';
                $checked_index = $glossary_index? ' checked="1"': '';
                $tool_content .= "
              <form action='$base_url' method='post'>
               <fieldset>
                 <legend>$langConfig</legend>
                 <table class='tbl' width='100%'>
                 <tr>
                   <th>$langGlossaryIndex:
                     <input type='checkbox' name='index' value='yes'$checked_index>
                   </th>
                   <td class='right' width='10'>&nbsp;</td>
                 </tr>
                 <tr>
                   <th>$langGlossaryExpand:
                     <input type='checkbox' name='expand' value='yes'$checked_expand>
                   </th>
                   <td class='right' width='10'><input type='submit' name='submit_config' value='$langSubmit'></td>
                 </tr>
                 </table>
               </fieldset>
              </form>\n";    
        }

        // display form for adding or editing a glossary term
        if (isset($_GET['add']) or isset($_GET['edit'])) {
                $navigation[] = array('url' => $base_url,
                                      'name' => $langGlossary);
                $html_id = $html_term = $html_url = $definition = $notes = '';
                $category_id = 'none';
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
                                $notes = q($data['notes']);
                                $category_id = is_null($data['category_id'])? 'none': $data['category_id'];
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
             <form action='$base_url' method='post'>
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
        list($total_glossary_terms) =
                mysql_fetch_row(db_query("SELECT COUNT(*) FROM glossary
                                                          WHERE course_id = $course_id"));
        if ($expand_glossary and $total_glossary_terms > $max_glossary_terms) {
                $tool_content .= sprintf("<p class='alert1'>$langGlossaryOverLimit</p>",
                                         "<b>$max_glossary_terms</b>");
        }
} else {
        // Show categories link for students if needed
        if ($categories) {
                $tool_content .= "
       <div id='operations_container'>
         <ul id='opslist'>
           <li><a href='categories.php?course=$course_code'>$langCategories</a></li>
         </ul>
       </div>";
        }
}

if ($glossary_index and count($prefixes) > 1) {
        $tool_content .= "<div class='alphabetic_index'>";
        $begin = true;
        foreach ($prefixes as $letter) {
                $active = (!isset($_GET['prefix']) && !$cat_id && $begin) ||
                          (isset($_GET['prefix']) and autounquote($_GET['prefix']) == $letter);
                $tool_content .= ($begin? '': ' | ') .
                                 ($active? '<b>': "<a href='$base_url&amp;prefix=$letter'>") .
                                 q($letter) . ($active? '</b>': '</a>');
                $begin = false;
        }
        $tool_content .= "</div>";
}

/*************************************************
// display glossary
*************************************************/

$where = '';
if (isset($_GET['edit'])) {
        $where = "AND id = $id";
} elseif (isset($_GET['id'])) {
        $navigation[] = array('url' => $base_url,
                'name' => $langGlossary);
        $where = "AND id = " . intval($_GET['id']);
} elseif (isset($_GET['prefix'])) {
        $where = " AND term LIKE " . autoquote($_GET['prefix'] . '%');
} elseif ($glossary_index and !$cat_id and count($prefixes) > 1) {
        $where = " AND term LIKE " . quote($prefixes[0] . '%');
}
if ($cat_id) {
        $navigation[] = array('url' => $base_url,
                'name' => $langGlossary);
        $nameTools = q($categories[$cat_id]);
        $where .= " AND category_id = $cat_id";
}
$sql = db_query("SELECT id, term, definition, url, notes, category_id
                        FROM glossary WHERE course_id = $course_id $where
                        GROUP BY term
                        ORDER BY term");
if (mysql_num_rows($sql) > 0) { 
        $tool_content .= "
	       <script type='text/javascript' src='../auth/sorttable.js'></script>
               <table class='sortable' id='t2' width='100%'>";
	$tool_content .= "
	       <tr>
		 <th><div align='left'>$langGlossaryTerm</div></th>
		 <th><div align='left'>$langGlossaryDefinition</div></th>";
	    if ($is_editor) {
		 $tool_content .= "
		 <th width='20'>$langActions</th>";
	    }
	$tool_content .= "
	       </tr>";
	$i=0;
	while ($g = mysql_fetch_assoc($sql)) {
                if ($i == 0 and isset($_GET['id'])) {
                        $nameTools = q($g['term']);
                }
		if ($i%2) {
		   $rowClass = "class='odd'";
		} else {
		   $rowClass = "class='even'";
		}
		if (!empty($g['url'])) {
		    $urllink = "<div><span class='smaller'>(<a href='" . q($g['url']) .
			       "' target='_blank'>" . q($g['url']) . "</a>)</span></div>";
		} else {
		    $urllink = '';
		}

                if (!empty($g['category_id'])) {
                    $cat_descr = "<span class='smaller'>$langCategory: <a href='$base_url&amp;cat=$g[category_id]'>". q($categories[$g['category_id']]) ."</a></span>";
                } else {
                    $cat_descr = '';
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
		 <th width='150'><a href='$base_url&amp;id=$g[id]'>" . q($g['term']) . "</a> <div class='invisible'>$cat_descr</div></th> 
                 <td><em>$definition_data</em>$urllink</td>";
	    if ($is_editor) {
		$tool_content .= "
		 <td align='center' valign='top' width='50'><a href='$base_url&amp;edit=$g[id]'>
		    <img src='$themeimg/edit.png' /></a>
                    <a href='$base_url&amp;delete=$g[id]' onClick=\"return confirmation('" .
                        js_escape($langConfirmDelete) . "');\">
		    <img src='$themeimg/delete.png'></a>
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

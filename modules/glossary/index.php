<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.php';

ModalBoxHelper::loadModalBox();

$edit_url = $base_url = 'index.php?course=' . $course_code;
$cat_url = 'categories.php?course=' . $course_code;

/*
 * *** The following is added for statistics purposes **
 */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_GLOSSARY);

if ($is_editor) {
    load_js('tools.js');
    $max_glossary_terms = get_config('max_glossary_terms');
}

$nameTools = $langGlossary;

$categories = array();
Database::get()->queryFunc("SELECT id, name, description, `order`
                      FROM glossary_category WHERE course_id = ?d
                      ORDER BY name", function ($cat) use (&$categories) {
    $categories[intval($cat->id)] = $cat->name;
}, $course_id);
if (isset($_GET['cat'])) {
    $cat_id = intval($_GET['cat']);
    $edit_url .= "&amp;cat=$cat_id";
} else {
    $cat_id = false;
}
if (isset($_GET['prefix'])) {
    $edit_url .= '&amp;prefix=' . urlencode($_GET['prefix']);
}

$glossary_data = Database::get()->querySingle("SELECT glossary_expand, glossary_index
                                         FROM course WHERE id = ?d", $course_id);
if ($glossary_data) {
    $expand_glossary = $glossary_data->glossary_expand;
    $glossary_index = $glossary_data->glossary_index;
    if ($glossary_index) {
        $prefixes = array();
        Database::get()->queryFunc("SELECT DISTINCT UPPER(LEFT(term, 1)) AS prefix
                              FROM glossary WHERE course_id = ?d
                              ORDER BY prefix", function ($prefix) use (&$prefixes) {
            $prefix = remove_accents($prefix->prefix);
            if (array_search($prefix, $prefixes) === false) {
                $prefixes[] = $prefix;
            }
        }, $course_id);
    }
}


/* * ******************************************
 * Actions*
 * ****************************************** */

if ($is_editor) {
    
    if (isset($_GET['add']) or isset($_GET['config']) or isset($_GET['edit'])) {
       $tool_content .= "<div id='operations_container'>
         <ul id='opslist'>
            <li><a href='$base_url'>$langBack</a></li>
            </ul>
       </div>";
    } else {    
         $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>" .
             ($categories ? "<li><a href='categories.php?course=$course_code'>$langCategories</a></li>" : '') . "
            <li><a href='$base_url&amp;add=1'>$langAddGlossaryTerm</a></li>
            <li><a href='$cat_url&amp;add=1'>$langCategoryAdd</a></li>
            <li><a href='$base_url&amp;config=1'>$langConfig</a></li>
            <li>$langGlossaryToCsv (<a href='dumpglossary.php?course=$course_code'>UTF8</a>&nbsp;-&nbsp;<a href='dumpglossary.php?course=$course_code&amp;enc=1253'>Windows 1253</a>)</li>
          </ul>
        </div>";
    }
    
    if (isset($_POST['url'])) {
        $url = trim($_POST['url']);
        if (!empty($url)) {
            $url = canonicalize_url($url);
        }
    } else {
        $url = '';
    }

    if (isset($_POST['submit_config'])) {
        $expand_glossary = isset($_POST['expand']) ? 1 : 0;
        Database::get()->query("UPDATE course SET glossary_expand = ?d,
                                           glossary_index = ?d WHERE id = ?d"
                , $expand_glossary, (isset($_POST['index']) ? 1 : 0), $course_id);
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
            $q = Database::get()->query("UPDATE glossary
                                              SET term = ?s,
                                                  definition = ?s,
                                                  url = ?s,
                                                  notes = ?s,
                                                  category_id = ?d ,
                                                  datestamp = NOW()
                                              WHERE id = ?d AND course_id = ?d"
                    , $_POST['term'], $_POST['definition'], $url, purify($_POST['notes']), $category_id, $id, $course_id);
            $log_action = LOG_MODIFY;
            $success_message = $langGlossaryUpdated;
        } else {
            $q = Database::get()->query("INSERT INTO glossary
                                              SET term = ?s,
                                                  definition = ?s,
                                                  url = ?s,
                                                  notes = ?s,
                                                  category_id = ?d,
                                                  datestamp = NOW(),
                                                  course_id = ?d,
                                                  `order` = ?d"
                    , $_POST['term'], $_POST['definition'], $url, purify($_POST['notes']), $category_id, $course_id, findorder($course_id));
            $log_action = LOG_INSERT;
            $success_message = $langGlossaryAdded;
        }
        $id = $q->lastInsertID;
        Log::record($course_id, MODULE_ID_GLOSSARY, $log_action, array('id' => $id,
            'term' => $_POST['term'],
            'definition' => $_POST['definition'],
            'url' => $url,
            'notes' => purify($_POST['notes'])));

        if ($q and $q->affectedRows) {
            invalidate_glossary_cache();
            $tool_content .= "<div class='success'>$success_message</div><br />";
        }
    }

    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $term = Database::get()->querySingle("SELECT term FROM glossary WHERE ID = ?d", $id)->term;
        $q = Database::get()->query("DELETE FROM glossary WHERE id = ?d AND course_id = ?d", $id, $course_id);
        invalidate_glossary_cache();
        Log::record($course_id, MODULE_ID_GLOSSARY, LOG_DELETE, array('id' => $id,
                                                                      'term' => $term));
        if ($q and $q->affectedRows) {
            $tool_content .= "<div class='success'>$langGlossaryDeleted</div><br />";
        }
        draw($tool_content, 2, null, $head_content);
        exit;
    }       

    // display configuration form
    if (isset($_GET['config'])) {
        $navigation[] = array('url' => $base_url, 'name' => $langGlossary);
        $nameTools = $langConfig;
        $checked_expand = $expand_glossary ? ' checked="1"' : '';
        $checked_index = $glossary_index ? ' checked="1"' : '';
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
            $data = Database::get()->querySingle("SELECT term, definition, url, notes, category_id
                                              FROM glossary WHERE id = ?d", $id);
            if ($data) {
                $html_id = "<input type = 'hidden' name='id' value='$id'>";
                $html_term = " value='" . q($data->term) . "'";
                $html_url = " value='" . q($data->url) . "'";
                $category_id = is_null($data->category_id) ? 'none' : $data->category_id;
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
             <form action='$edit_url' method='post'>
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
                   <td valign='top'>" . @text_area('definition', 4, 60, $data->definition) . "
                   </td>
                 </tr>
                 <tr>
                   <th>$langGlossaryUrl:</th>
                   <td><input type='text' name='url' size='50'$html_url></td>
                 </tr>
                 <tr>
                   <th valign='top'>$langCategoryNotes:</th>
                   <td valign='top'>" . @rich_text_editor('notes', 4, 60, $data->notes) . "
                   </td>
                 </tr>
                 $category_selection
                 <tr>
                   <th>&nbsp;</th>
                   <td class='right'><input type='submit' name='submit' value='$submit_value'></td>
                 </tr>
                 </table>
               </fieldset>
             </form>";
    }
    $total_glossary_terms = Database::get()->querySingle("SELECT COUNT(*) AS count FROM glossary
                                                          WHERE course_id = ?d", $course_id)->count;
    if ($expand_glossary and $total_glossary_terms > $max_glossary_terms) {
        $tool_content .= sprintf("<p class='alert1'>$langGlossaryOverLimit</p>", "<b>$max_glossary_terms</b>");
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
                (isset($_GET['prefix']) and $_GET['prefix'] == $letter);
        $tool_content .= ($begin ? '' : ' | ') .
                ($active ? '<b>' : "<a href='$base_url&amp;prefix=" . urlencode($letter) . "'>") .
                q($letter) . ($active ? '</b>' : '</a>');
        $begin = false;
    }
    $tool_content .= "</div>";
}

/* * ***********************************************
  // display glossary
 * *********************************************** */

$where = '';
$terms = array();
if (isset($_GET['edit'])) {
    $where = "AND id = ?d";
    $terms[] = intval($id);
} elseif (isset($_GET['id'])) {
    $navigation[] = array('url' => $base_url,
        'name' => $langGlossary);
    $where = "AND id = ?d";
    $terms[] = intval($_GET['id']);
} elseif (isset($_GET['prefix'])) {
    $where = "AND term LIKE ?s";
    $terms[] = $_GET['prefix'] . '%';
} elseif ($glossary_index and ! $cat_id and count($prefixes) > 1) {
    $where = "AND term LIKE ?s";
    $terms[] = $prefixes[0] . '%';
}
if ($cat_id) {
    $navigation[] = array('url' => $base_url,
        'name' => $langGlossary);
    $nameTools = q($categories[$cat_id]);
    $where .= " AND category_id = $cat_id";
}
$sql = Database::get()->queryArray("SELECT id, term, definition, url, notes, category_id
                        FROM glossary WHERE course_id = ?d $where
                        GROUP BY term
                        ORDER BY term", $course_id, $terms);
if (count($sql) > 0) {
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
    $tool_content .= "</tr>";
    $i = 0;
    foreach ($sql as $g) {
        if ($i == 0 and isset($_GET['id'])) {
            $nameTools = q($g->term);
        }
        if ($i % 2) {
            $rowClass = "class='odd'";
        } else {
            $rowClass = "class='even'";
        }
        if (!empty($g->url)) {
            $urllink = "<div><span class='smaller'>(<a href='" . q($g->url) .
                    "' target='_blank'>" . q($g->url) . "</a>)</span></div>";
        } else {
            $urllink = '';
        }

        if (!empty($g->category_id)) {
            $cat_descr = "<span class='smaller'>$langCategory: <a href='$base_url&amp;cat=$g->category_id'>" . q($categories[$g->category_id]) . "</a></span>";
        } else {
            $cat_descr = '';
        }

        if (!empty($g->notes)) {
            $urllink .= "<br />" . standard_text_escape($g->notes);
        }

        if (!empty($g->definition)) {
            $definition_data = q($g->definition);
        } else {
            $definition_data = '-';
        }

        $tool_content .= "
	       <tr $rowClass>
		 <th width='150'><a href='$base_url&amp;id=$g->id'>" . q($g->term) . "</a> <div class='invisible'>$cat_descr</div></th>
                 <td><em>$definition_data</em>$urllink</td>";
        if ($is_editor) {
            $tool_content .= "
		 <td align='center' valign='top' width='50'><a href='$edit_url&amp;edit=$g->id'>
		    <img src='$themeimg/edit.png' alt='$langEdit' title='$langEdit'></a>
                    <a href='$edit_url&amp;delete=$g->id' onClick=\"return confirmation('" .
                    js_escape($langConfirmDelete) . "');\">
		    <img src='$themeimg/delete.png' alt='$langDelete' title='$langDelete'></a>
		 </td>";
        }
        $tool_content .= "</tr>";
        $i++;
    }
    $tool_content .= "</table><br />";
} else {
    $tool_content .= "<p class='alert1'>$langNoResult</p>";
}

draw($tool_content, 2, null, $head_content);


/* * **************************************** */

function findorder($course_id) {
    $maxorder = Database::get()->querySingle("SELECT MAX(`ORDER`) as maxorder FROM glossary WHERE course_id = ?d", $course_id)->maxorder;
    if ($maxorder > 0) {
        $maxorder++;
        return $maxorder;
    } else {
        $maxorder = 1;
        return $maxorder;
    }
}

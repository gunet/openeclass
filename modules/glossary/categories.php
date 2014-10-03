<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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
ModalBoxHelper::loadModalBox();


$base_url = 'index.php?course=' . $course_code;
$cat_url = 'categories.php?course=' . $course_code;

$navigation[] = array('url' => $base_url, 'name' => $langGlossary);
$nameTools = $langCategories;

$categories = array();
$q = Database::get()->queryArray("SELECT id, name, description, `order`
                      FROM glossary_category WHERE course_id = ?d
                      ORDER BY name", $course_id);
foreach ($q as $cat) {
    $categories[intval($cat->id)] = $cat->name;
}

if ($is_editor) {
    load_js('tools.js');

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

    if (isset($_POST['submit_category'])) {
        if (isset($_POST['category_id'])) {
            $category_id = intval($_POST['category_id']);
            $q = Database::get()->query("UPDATE glossary_category
                                              SET name = ?s,
                                                  description = ?s
                                              WHERE id = ?d AND course_id = ?d"
                    , $_POST['name'], $_POST['description'], $category_id, $course_id);
            $success_message = $langCategoryModded;
        } else {
            Database::get()->query("SELECT @new_order := (1 + IFNULL(MAX(`order`),0))
                                         FROM glossary_category WHERE course_id = ?d", $course_id);
            $q = Database::get()->query("INSERT INTO glossary_category
                                              SET name = ?s,
                                                  description = ?s,
                                                  course_id = ?d,
                                                  `order` = @new_order"
                    , $_POST['name'], $_POST['description'], $course_id);
            $category_id = $q->lastInsertID;
            $success_message = $langCategoryAdded;
        }
        if ($q and $q->affectedRows) {
            $categories[$category_id] = $_POST['name'];
            $tool_content .= "<div class='success'>$success_message</div><br />";
        }
    }

    // Delete category, turn terms in it to uncategorized
    if (isset($_GET['delete'])) {
        $cat_id = $_GET['delete'];
        $q = Database::get()->query("DELETE FROM glossary_category
                                      WHERE id = ?d AND course_id = ?d", $cat_id, $course_id);
        if ($q and $q->affectedRows) {
            Database::get()->query("UPDATE glossary SET category_id = NULL
                                                  WHERE course_id = ?d AND
                                                        category_id = ?d", $course_id, $cat_id);
            $tool_content .= "<div class='success'>$langCategoryDeletedGlossary</div><br />";
        }        
    }


    // display form for adding or editing a category
    if (isset($_GET['add']) or isset($_GET['edit'])) {
        $html_id = $html_name = $description = '';
        if (isset($_GET['add'])) {
            $nameTools = $langCategoryAdd;
            $submit_value = $langSubmit;
        } else {
            $nameTools = $langCategoryMod;
            $cat_id = intval($_GET['edit']);
            $data = Database::get()->querySingle("SELECT name, description
                                              FROM glossary_category WHERE id = ?d", $cat_id);
            if ($data) {
                $html_name = " value='" . q($data->name) . "'";
                $html_id = "<input type = 'hidden' name='category_id' value='$cat_id'>";
                $description = $data->description;
            }
            $submit_value = $langModify;
        }
        $tool_content .= "<form action='$cat_url' method='post'>
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
          </form>";                       
    }
}

$q = Database::get()->queryArray("SELECT id, name, description
                      FROM glossary_category WHERE course_id = ?d
                      ORDER BY name", $course_id);

if ($q and count($q)) {
    $tool_content .= "
               <script type='text/javascript' src='../auth/sorttable.js'></script>
               <table class='sortable' id='t2' width='100%'>
               <tr>
                 <th width='1'>&nbsp;</th>
                 <th class='left'>$langName</th>" .
            ($is_editor ? "<th width='20' class='center'>$langActions</th>" : '') . "
               </tr>";
    $i = 0;
    foreach ($q as $cat) {
        $class = ($i % 2) ? 'odd' : 'even';
        if ($cat->description) {
            $desc = "<br>" . standard_text_escape($cat->description);
        } else {
            $desc = '';
        }
        if ($is_editor) {
            $actions = "<td class='center'>
                     <a href='$cat_url&amp;edit=$cat->id title='$langCategoryMod'>
                        <img src='$themeimg/edit.png' alt='$langCategoryMod'></a>&nbsp;
                     <a href='$cat_url&amp;delete=$cat->id' onClick=\"return confirmation('" . js_escape($langConfirmDelete) ."');\">
                    <img src='$themeimg/delete.png' alt='$langCategoryDel'
                        title='$langCategoryDel'></a>
                 </td>";
        } else {
            $actions = '';
        }
        $tool_content .= "
               <tr class='$class'>
                 <td width='1' valign='top'>
                   <img style='padding-top:3px;' src='$themeimg/arrow.png' alt=''>
                 </td>
                 <td><a href='$base_url&amp;cat=$cat->id'>" . q($cat->name) . "</a>$desc</td>$actions
               </tr>";
        $i++;
    }
    $tool_content .= "</table>";
} else {
    $tool_content .= "<p class='alert1'>$langNoResult</p>";
}

draw($tool_content, 2, null, $head_content);


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
$helpTopic = 'glossary';

require_once '../../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

$base_url = $data['base_url'] = 'index.php?course=' . $course_code;
$cat_url = $data['cat_url'] = 'categories.php?course=' . $course_code;

$navigation[] = array('url' => $base_url, 'name' => $langGlossary);
$toolName = $langCategories;

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
        if (isset($_GET['add'])) {
            $pageName = $langCategoryAdd;
        }
        if (isset($_GET['config'])) {
            $pageName = $langConfig;
        }
        if (isset($_GET['edit'])) {
            $pageName = $langCategoryMod;
        }
        
        $data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                      'url' => "$cat_url",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));        
    } else {
        $data['action_bar'] = action_bar(array(
                array('title' => $langAddGlossaryTerm,
                      'url' => "$base_url&amp;add=1",
                      'icon' => 'fa-plus-circle',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success'),
                array('title' => $langCategoryAdd,
                      'url' => "$cat_url&amp;add=1",
                      'icon' => 'fa-plus-circle',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success'),
                array('title' => $langConfig,
                      'url' => "$base_url&amp;config=1",                      
                      'icon' => 'fa-gear'),
                array('title' => "$langGlossaryToCsv",
                      'url' => "dumpglossary.php?course=$course_code",
                      'icon' => 'fa-file-excel-o'),
                array('title' => "$langGlossaryToCsv (UTF-8)",
                      'url' => "dumpglossary.php?course=$course_code&amp;enc=UTF-8",
                      'icon' => 'fa-file-excel-o'),
                array('title' => $langGlossaryTerms,
                      'url' => "index.php?course=$course_code",
                      'icon' => 'fa-tasks',
                      'level' => 'primary-label')
            ));        
    }

    if (isset($_POST['submit_category'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('name'));
        $v->labels(array(
            'name' => "$langTheField $langCategoryName"
        ));        
        if($v->validate()) {
            if (isset($_POST['category_id'])) {
                $category_id = intval(getDirectReference($_POST['category_id']));
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
                Session::Messages($success_message, 'alert-success');
            }
            redirect_to_home_page("modules/glossary/categories.php?course=$course_code");
        } else {
            $new_or_modify = isset($_POST['category_id']) ? "&edit=" . q($_POST[category_id]) : "&add=1";
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/glossary/categories.php?course=$course_code$new_or_modify");
        }
    }

    // Delete category, turn terms in it to uncategorized
    if (isset($_GET['delete'])) {
        $cat_id = getDirectReference($_GET['delete']);
        $q = Database::get()->query("DELETE FROM glossary_category
                                      WHERE id = ?d AND course_id = ?d", $cat_id, $course_id);
        if ($q and $q->affectedRows) {
            Database::get()->query("UPDATE glossary SET category_id = NULL
                                                  WHERE course_id = ?d AND
                                                        category_id = ?d", $course_id, $cat_id);
            Session::Messages($langCategoryDeletedGlossary, 'alert-success');
            redirect_to_home_page("modules/glossary/categories.php?course=$course_code");
        }        
    }


    // display form for adding or editing a category
    if (isset($_GET['add']) or isset($_GET['edit'])) {
        if (isset($_GET['add'])) {
            $pageName = $langCategoryAdd;
            $submit_value = $langSubmit;
        } else {
            $pageName = $langCategoryMod;
            $cat_id = getDirectReference($_GET['edit']);
            $submit_value = $langModify;
            $data['glossary_cat'] = Database::get()->querySingle("SELECT id, name, description
                                              FROM glossary_category WHERE id = ?d", $cat_id);
        }
        $data['name'] = Session::has('name') ? Session::get('name') : ( isset($_GET['add']) ? "" : $data['glossary_cat']->name );
        $description = Session::has('description') ? Session::get('description') : ( isset($_GET['add']) ? "" : $data['glossary_cat']->description);
        $data['description_rich'] = rich_text_editor('description', 4, 60, $description);
        $data['form_buttons'] = form_buttons(array(
                                    array(
                                        'text' => $langSave,
                                        'value'=> $submit_value,
                                        'name' => 'submit_category'
                                    ),
                                    array(
                                        'href' => $cat_url
                                    )
                                ));
        view('modules.glossary.createCategory', $data);                
    }
}

if (!isset($_GET['edit']) && !isset($_GET['add'])) {
    $data['categories'] = Database::get()->queryArray("SELECT id, name, description
                          FROM glossary_category WHERE course_id = ?d
                          ORDER BY name", $course_id);
    view('modules.glossary.indexCategory', $data);
}

//draw($tool_content, 2, null, $head_content);


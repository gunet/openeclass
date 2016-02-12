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

$data['base_url'] = $base_url = 'index.php?course=' . $course_code;
$cat_url = 'categories.php?course=' . $course_code;

/*
 * *** The following is added for statistics purposes **
 */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_GLOSSARY);

if ($is_editor) {
    load_js('tools.js');
    $data['max_glossary_terms'] = get_config('max_glossary_terms');
}

$toolName = $langGlossary;

$categories = array();
Database::get()->queryFunc("SELECT id, name, description, `order`
                      FROM glossary_category WHERE course_id = ?d
                      ORDER BY name", function ($cat) use (&$categories) {
    $categories[intval($cat->id)] = $cat->name;
}, $course_id);

$indirectcategories = array();
foreach ($categories as $k => $v) {
    $indirectcategories[getIndirectReference($k)] = $v;
}
if (isset($_GET['cat'])) {
    $data['cat_id'] = $cat_id = intval(getDirectReference($_GET['cat']));
    $data['edit_url'] = $base_url."&amp;cat=$cat_id";
} else {
    $data['cat_id'] = $cat_id = false;
    $data['edit_url'] = $base_url;
}
if (isset($_GET['prefix'])) {
    $data['edit_url'] = $base_url.'&amp;prefix=' . urlencode($_GET['prefix']);
}

$glossary_data = Database::get()->querySingle("SELECT glossary_expand, glossary_index
                                         FROM course WHERE id = ?d", $course_id);
if ($glossary_data) {
    $data['expand_glossary'] = $expand_glossary = $glossary_data->glossary_expand;
    $data['glossary_index'] = $glossary_index = $glossary_data->glossary_index;
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
    $data['prefixes'] = $prefixes;
}


/* * ******************************************
 * Actions*
 * ****************************************** */

if ($is_editor) {
    if (isset($_GET['add']) or isset($_GET['config']) or isset($_GET['edit'])) {
        if (isset($_GET['add'])) {
            $pageName = $langAddGlossaryTerm;
        }
        if (isset($_GET['config'])) {
            $pageName = $langConfig;
        }
        if (isset($_GET['edit'])) {
            $pageName = $langEdit;
        }
        $data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                      'url' => "$base_url",
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
                array('title' => "$langGlossaryToCsv (UTF8)",
                      'url' => "dumpglossary.php?course=$course_code",
                      'icon' => 'fa-download'),
                array('title' => "$langGlossaryToCsv (Windows 1253)",
                      'url' => "dumpglossary.php?course=$course_code&amp;enc=1253",
                      'icon' => 'fa-download'),
                array('title' => $langCategories,
                      'url' => "categories.php?course=$course_code",
                      'icon' => 'fa-tasks',
                      'level' => 'primary-label',
                      'show' => $categories)
            ));
    }
    
    if (isset($_POST['submit_config'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $expand_glossary = isset($_POST['expand']) ? 1 : 0;
        Database::get()->query("UPDATE course SET glossary_expand = ?d,
                                           glossary_index = ?d WHERE id = ?d"
                , $expand_glossary, (isset($_POST['index']) ? 1 : 0), $course_id);
        invalidate_glossary_cache();
        Session::Messages($langQuotaSuccess, 'alert-success');
        redirect_to_home_page("modules/glossary/index.php?course=$course_code");
    }

    if (isset($_POST['submit'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        if (isset($_POST['url']) and !empty($_POST['url']) and !preg_match('/^\w+:/', $_POST['url'])) {
            $_POST['url'] = 'http://' . $_POST['url'];
        }
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('term', 'definition'));
        $v->rule('url', array('url'));
        $v->rule('urlActive', array('url'));
        $v->labels(array(
            'term' => "$langTheField $langGlossaryTerm",
            'definition' => "$langTheField $langGlossaryDefinition",
            'url' => "$langTheField $langGlossaryUrl"
        ));
        if($v->validate()) {
            if (!isset($_POST['category_id']) || getDirectReference($_POST['category_id']) == 0) {
                $category_id = NULL;
            } else {
                $category_id = intval(getDirectReference($_POST['category_id']));
            }

            if (isset($_POST['url'])) {
                $url = trim($_POST['url']);
                if (!empty($url)) {
                    $url = canonicalize_url($url);
                }
            } else {
                $url = '';
            }

            if (isset($_POST['id'])) {
                $id = intval(getDirectReference($_POST['id']));
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
                Session::Messages($success_message, 'alert-success');
            }
            redirect_to_home_page("modules/glossary/index.php?course=$course_code");
        } else {
            $new_or_modify = isset($_POST['id']) ? "&edit=$_POST[id]" : "&add=1";
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/glossary/index.php?course=$course_code$new_or_modify");
        }
    }

    if (isset($_GET['delete'])) {
        $id = getDirectReference($_GET['delete']);
        $term = Database::get()->querySingle("SELECT term FROM glossary WHERE ID = ?d", $id)->term;
        $q = Database::get()->query("DELETE FROM glossary WHERE id = ?d AND course_id = ?d", $id, $course_id);
        invalidate_glossary_cache();
        Log::record($course_id, MODULE_ID_GLOSSARY, LOG_DELETE, array('id' => $id,
                                                                      'term' => $term));
        if ($q and $q->affectedRows) {
            Session::Messages($langGlossaryDeleted, 'alert-success');
        }
        redirect_to_home_page("modules/glossary/index.php?course=$course_code");
    }       

    // display configuration form
    if (isset($_GET['config'])) {
        $navigation[] = array('url' => $base_url, 'name' => $langGlossary);
        $pageName = $langConfig;
        
        $data['base_url'] = $base_url;
        $data['checked_expand'] = $expand_glossary ? ' checked="1"' : '';
        $data['checked_index'] = $glossary_index ? ' checked="1"' : '';
        $data['form_buttons'] = form_buttons(array(
                            array(
                                'text' => $langSave,
                                'name' => 'submit_config',
                                'value'=> $langSubmit
                            ),
                            array(
                                'href' => $base_url
                            )
                        ));
        view('modules.glossary.config', $data);
    }

    // display form for adding or editing a glossary term
    if (isset($_GET['add']) or isset($_GET['edit'])) {
        $navigation[] = array(
                'url' => $base_url,
                'name' => $langGlossary
            );
        $data['action_bar'] =    
            action_bar(array(
                array('title' => $langBack,
                      'url' => "$base_url",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')
                )
            );          
        $category_id = 'none';
        if (isset($_GET['add'])) {
            $pageName = $langAddGlossaryTerm;
            $submit_value = $langSubmit;
        } else {
            $pageName = $langEditGlossaryTerm;
            $id = intval(getDirectReference($_GET['edit']));
            $data['glossary_item'] = $glossary_item = Database::get()->querySingle("SELECT id, term, definition, url, notes, category_id
                                              FROM glossary WHERE id = ?d", $id);
            if ($glossary_item) {
                $category_id = is_null($glossary_item->category_id) ? 'none' : $glossary_item->category_id;
            }
            $submit_value = $langModify;
        }
        $data['term'] = Session::has('term') ? Session::get('term') : ( isset($_GET['add']) ? "" : q($glossary_item->term) );
        $data['url'] = Session::has('url') ? Session::get('url') : ( isset($_GET['add']) ? "" : q($glossary_item->url) );
        $data['definition'] = Session::has('definition') ? Session::get('definition') : (isset($_GET['add']) ? "" : $glossary_item->definition );
        $notes = Session::has('notes') ? Session::get('notes') : (isset($_GET['add']) ? "" : $glossary_item->notes );
        $data['category_selection'] = '';
        if ($categories) {
            $categories[0] = '-';
            array_unshift($indirectcategories, "-");
            $data['category_selection'] = "
                        <div class='form-group'>
                             <label for='category_id' class='col-sm-2 control-label'>$langCategory: </label>
                             <div class='col-sm-10'>
                                 " . selection($indirectcategories, 'category_id', $category_id, 'class="form-control"') . "
                             </div>
                        </div>";
            unset($categories['none']);
            unset($indirectcategories['none']);
        }
        $data['notes_rich'] = rich_text_editor('notes', 4, 60, $notes);
        $data['form_buttons'] =
            form_buttons(array(
                    array(
                        'text' => $langSave,
                        'value'=> $submit_value,
                        'name' => 'submit'
                    ),
                    array(
                        'href' => $base_url,
                    )
                ));
        view('modules.glossary.create', $data);
    }
    $data['total_glossary_terms'] = Database::get()->querySingle("SELECT COUNT(*) AS count FROM glossary
                                                          WHERE course_id = ?d", $course_id)->count;       
} else {
    // Show categories link for students if needed
    if ($categories) {
        $tool_content .= action_bar(array(
                      array('title' => $langCategories,
                            'url' => "categories.php?course=$course_code",
                            'icon' => 'fa-tasks',
                            'level' => 'primary-label')));        
    }
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
    $terms[] = intval(getDirectReference($_GET['id']));
} elseif (isset($_GET['prefix'])) {
    $where = "AND term LIKE ?s";
    $terms[] = $_GET['prefix'] . '%';
} elseif ($glossary_index and ! $cat_id and count($prefixes) > 1) {
    $where = "AND term LIKE ?s";
    $terms[] = $prefixes[0] . '%';
}

if(!isset($_GET['add']) && !isset($_GET['edit']) && !isset($_GET['config'])) {
    if ($cat_id) {
        $navigation[] = array('url' => $base_url, 'name' => $langGlossary);
        $pageName = q($categories[$cat_id]);
        $where .= " AND category_id = $cat_id";
    }
    $data['glossary_terms'] = $sql = Database::get()->queryArray("SELECT id, term, definition, url, notes, category_id
                            FROM glossary WHERE course_id = ?d $where
                            GROUP BY term
                            ORDER BY term", $course_id, $terms);    
    view('modules.glossary.index', $data);   
}

/**
 * @brief find glossary term order
 * @param type $course_id
 * @return int
 */
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

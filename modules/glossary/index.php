<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_help = true;
$helpTopic = 'glossary';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.class.php';

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
$data['categories'] = $categories;

if (isset($_GET['cat'])) {
    $data['cat_id'] = $cat_id = intval($_GET['cat']);
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
        $data['prefixes'] = $prefixes;
    }
}


/* * ******************************************
 * Actions*
 * ****************************************** */

if ($is_editor) {

    if (isset($_GET['dump'])) { // dump to excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($langGlossary);
        $sheet->getDefaultColumnDimension()->setWidth(30);
        $filename = $course_code . '_glossary.xlsx';
        $course_title = course_id_to_title($course_id);

        $cnt[] = [ $course_title ];
        $cnt[] = [];
        $cnt[] = [ $langGlossaryTerm, $langGlossaryDefinition, $langGlossaryUrl ];

        $sql = Database::get()->queryFunc("SELECT term, definition, url FROM glossary
                            WHERE course_id = ?d
                            ORDER BY `order`",
            function ($item) use (&$cnt) {
                $cnt[] = [ $item->term, $item->definition, $item->url ];
            }, $course_id);

        $sheet->mergeCells("A1:C1");
        $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
        for ($i = 1; $i <= 3; $i++) {
            $cells = [$i, 3];
            $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
        }
        // create spreadsheet
        $sheet->fromArray($cnt, NULL);

        // file output
        $writer = new Xlsx($spreadsheet);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        set_content_disposition('attachment', $filename);
        $writer->save("php://output");
        exit;
    }

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
        $data['action_bar'] = '';
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
                      'button-class' => 'btn-success'),
                array('title' => $langConfig,
                      'url' => "$base_url&amp;config=1",
                      'icon' => 'fa-gear'),
                array('title' => $langDumpExcel,
                      'url' => "$base_url&amp;dump=1",
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
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
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
            if (!isset($_POST['category_id']) || ($_POST['category_id'] == 0)) {
                $category_id = NULL;
            }

            if (isset($_POST['url'])) {
                $url = trim($_POST['url']);
                if (!empty($url)) {
                    $url = canonicalize_url($url);
                }
            } else {
                $url = '';
            }
            $term = trim($_POST['term']);
            if (isset($_POST['id'])) {
                $id = intval(getDirectReference($_POST['id']));
                $q = Database::get()->query("UPDATE glossary
                                                  SET term = ?s,
                                                      definition = ?s,
                                                      url = ?s,
                                                      notes = ?s,
                                                      category_id = ?d,
                                                      datestamp = " . DBHelper::timeAfter() . "
                                                  WHERE id = ?d AND course_id = ?d"
                        , $term, $_POST['definition'], $url, purify($_POST['notes']), $_POST['category_id'], $id, $course_id);
                $log_action = LOG_MODIFY;
                $success_message = $langGlossaryUpdated;
            } else {
                $q = Database::get()->query("INSERT INTO glossary
                                                  SET term = ?s,
                                                      definition = ?s,
                                                      url = ?s,
                                                      notes = ?s,
                                                      category_id = ?d,
                                                      datestamp = " . DBHelper::timeAfter() . ",
                                                      course_id = ?d,
                                                      `order` = ?d"
                        , $term, $_POST['definition'], $url, purify($_POST['notes']), $_POST['category_id'], $course_id, findorder($course_id));
                $log_action = LOG_INSERT;
                $success_message = $langGlossaryAdded;
            }
            $id = $q->lastInsertID;
            Log::record($course_id, MODULE_ID_GLOSSARY, $log_action, array('id' => $id,
                'term' => $term,
                'definition' => $_POST['definition'],
                'url' => $url,
                'notes' => purify($_POST['notes'])));

            if ($q and $q->affectedRows) {
                invalidate_glossary_cache();
                Session::flash('message',$success_message);
                Session::flash('alert-class', 'alert-success');
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
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
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
                                'class' => 'submitAdminBtn',
                                'text' => $langSubmit,
                                'name' => 'submit_config',
                                'value'=> $langSubmit
                            ),
                            array(
                                'class' => 'cancelAdminBtn ms-1',
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
        $data['action_bar'] = '';

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
        $data['term'] = Session::has('term') ? Session::get('term') : ( isset($_GET['add']) ? "" : $glossary_item->term);
        $data['url'] = Session::has('url') ? Session::get('url') : ( isset($_GET['add']) ? "" : q($glossary_item->url) );
        $data['definition'] = Session::has('definition') ? Session::get('definition') : (isset($_GET['add']) ? "" : $glossary_item->definition );
        $notes = Session::has('notes') ? Session::get('notes') : (isset($_GET['add']) ? "" : $glossary_item->notes );
        $data['category_selection'] = '';
        $category_id = Session::has('category_id') ? Session::get('category_id') : $category_id;

        if ($categories) {
            $categories[0] = '-';
            $data['category_selection'] = "
                        <div class='form-group'>
                             <label for='cat_ID' class='col-sm-6 control-label-notes'>$langCategory: </label>
                             <div class='col-sm-12'>
                                 " . selection($categories, 'category_id', $category_id, 'class="form-control" id="cat_ID"') . "
                             </div>
                        </div>";
        }
        $data['notes_rich'] = rich_text_editor('notes', 4, 60, $notes);
        $data['form_buttons'] =

            form_buttons(array(
                    array(
                        'class' => 'submitAdminBtn',
                        'text' => $langSubmit,
                        'value'=> $submit_value,
                        'name' => 'submit'
                    )
                ));
        view('modules.glossary.create', $data);
    }
    $data['total_glossary_terms'] = Database::get()->querySingle("SELECT COUNT(*) AS count FROM glossary
                                                          WHERE course_id = ?d", $course_id)->count;
} else {
    // Show categories link for students if needed
    if ($categories) {
        $data['action_bar'] = action_bar(array(
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
    $terms[] = intval($_GET['id']);
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
                            GROUP BY term, definition, url, notes, category_id, id 
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

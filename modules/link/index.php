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


/**
 * @brief links module
 * partially based on code by Patrick Cool
 *
 */

$require_current_course = TRUE;
$require_help = true;
$helpTopic = 'Link';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.php';
require_once 'linkfunctions.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/course_settings.php';

require_once 'include/action.php';
$action_stats = new action();
$action_stats->record(MODULE_ID_LINKS);

//check if social bookmarking is enabled for this course
$social_bookmarks_enabled = $data['social_bookmarks_enabled'] = setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id);

$toolName = $langLinks;
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'addlink':
            $pageName = $langLinkAdd;
            break;
        case 'editlink':
            $pageName = $langLinkModify;
            break;
        case 'addcategory':
            $pageName = $langCategoryAdd;
            break;
        case 'editcategory':
            $pageName = $langCategoryMod;
            break;
        case 'settings':
            $pageName = $langLinkSettings;
            break;
    }
}

$is_in_tinymce = $data['is_in_tinymce'] = (isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce') ? true : false;
$menuTypeID = ($is_in_tinymce) ? 5 : 2;
$tinymce_params = $data['tinymce_params'] = '';

if ($is_in_tinymce) {
    $_SESSION['embedonce'] = true; // necessary for baseTheme
    $docsfilter = (isset($_REQUEST['docsfilter'])) ? '&amp;docsfilter=' . $_REQUEST['docsfilter'] : '';
    $tinymce_params = $data['tinymce_params'] = '&amp;embedtype=tinymce' . $docsfilter;
    load_js('jquery-' . JQUERY_VERSION . '.min');
    load_js('tinymce.popup.urlgrabber.min.js');
}

ModalBoxHelper::loadModalBox();

if (isset($_GET['category'])) {
    $category = $data['category'] = getDirectReference($_GET['category']);
} else {
    unset($category);
    unset($data['category']);
}

if (isset($_GET['id'])) {
    $id = $data['id'] = intval(getDirectReference($_GET['id']));
} else {
    unset($id);
}

if (isset($_GET['urlview'])) {
    $urlview = $data['urlview'] = urlencode($_GET['urlview']);
} else {
    $urlview = $data['urlview'] = '';
}

if (isset($_GET['socialview'])) {
    $socialview = $data['socialview'] = true;
    $socialview_param = $data['socialview_param'] = '&amp;socialview';
} else {
    $socialview = $data['socialview'] = false;
    $socialview_param = $data['socialview_param'] = '';
}

$action = $data['action'] = isset($_GET['action']) ? $_GET['action'] : '';

if ($is_editor) {
    //Link Submission
    if (isset($_POST['submitLink'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        submit_link();
        $message = isset($_POST['id']) ? $langLinkMod : $langLinkAdded;
        Session::Messages($message, 'alert-success');
        redirect_to_home_page("modules/link/index.php");
    }
    //Category Submission
    if (isset($_POST['submitCategory'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        submit_category();
        $messsage = isset($_POST['id']) ? $langCategoryModded : $langCategoryAdded;
        Session::Messages($messsage, 'alert-success');
        redirect_to_home_page("modules/link/index.php");
    }
    // Settings Submission
    if (isset($_POST['submitSettings'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        if (isset($_POST['settings_radio'])) {
            setting_set(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, intval($_POST['settings_radio']));
            Session::Messages($langLinkSettingsSucc, 'alert-success');
        }
        redirect_to_home_page("modules/link/index.php?course=$course_code");
    }
    // Link and Category Ordering
    if (isset($_GET['down'])) {
        move_order('link', 'id', intval(getDirectReference($_GET['down'])), 'order', 'down', "course_id = $course_id");
    } elseif (isset($_GET['up'])) {
        move_order('link', 'id', intval(getDirectReference($_GET['up'])), 'order', 'up', "course_id = $course_id");
    } elseif (isset($_GET['cdown'])) {
        move_order('link_category', 'id', intval(getDirectReference($_GET['cdown'])), 'order', 'down', "course_id = $course_id");
    } elseif (isset($_GET['cup'])) {
        move_order('link_category', 'id', intval(getDirectReference($_GET['cup'])), 'order', 'up', "course_id = $course_id");
    }
    
    switch ($action) {
        case 'deletelink':
            delete_link($id);
            Session::Messages($langLinkDeleted, 'alert-success');
            redirect_to_home_page("modules/link/index.php");
            break;
        case 'deletecategory':
            delete_category($id);
            Session::Messages($langCategoryDeleted, 'alert-success');
            redirect_to_home_page("modules/link/index.php");
            break;
    }


    if (!$is_in_tinymce) {
        if (isset($_GET['action'])) {
            $tool_content .= $data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label',
                      'show' => $is_editor)));

        } else {
            $ext = (isset($category)? "&amp;category=$category": '') .
                   (isset($urlview)? "&amp;urlview=$urlview": '');
            $tool_content .= $data['action_bar'] = action_bar(array(
                array('title' => $langLinkAdd,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=addlink$ext",
                      'icon' => 'fa-plus-circle',
                      'button-class' => 'btn-success',
                      'level' => 'primary-label'),
                array('title' => $langCategoryAdd,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=addcategory$ext",
                      'icon' => 'fa-plus-circle',
                      'button-class' => 'btn-success',
                      'level' => 'primary-label'),
                array('title' => $langConfig,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=settings",
                      'icon' => 'fa-gear',
                      'level' => 'primary')));
        }
    }

    // Add or Edit Link
    if (in_array($action, ['addlink', 'editlink'])) {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langLinks);
        if ($action == 'editlink') {
            $data['link'] = Database::get()->querySingle("SELECT * FROM `link` WHERE course_id = ?d AND id = ?d", $course_id, $id);
            $form_description = $data['link'] ? purify($data['link']->description) : "" ;
            $data['submit_label'] = $langLinkModify;
        } else {
            $form_description = '';
            $data['submit_label'] = $langAdd;
        }
        $data['urlLinkError'] = Session::getError('urllink') ? " has-error" : "";
        $data['description_textarea'] = rich_text_editor('description', 3, 30, $form_description);

        $data['categories'] = Database::get()->queryArray("SELECT * FROM link_category WHERE course_id = ?d ORDER BY `order`", $course_id);

        echo view('modules.link.create', $data);
        exit();
    // Add or Edit Category
    } elseif (in_array($action, array('addcategory', 'editcategory'))) {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langLinks);
        $data['categoryNameError'] = Session::getError('categoryname') ? " has-error" : "";
        if ($action == 'editcategory') {
            $data['category'] = Database::get()->querySingle("SELECT name, description  FROM link_category WHERE course_id = ?d AND id = ?d", $course_id, $id);
            $data['form_legend'] = trans('langCategoryMod');
        } else {
            $data['form_legend'] = trans('langCategoryAdd');
        }

        echo view('modules.link.createCategory', $data);
        exit();
    // Edit Settings
    } elseif ($action == 'settings') {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langLinks);
        
        $data['social_enabled'] = setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id);
        
        echo view('modules.link.settings', $data);
        exit();        
    }
// If the user !$is_editor and social bookmarks are enabled    
} elseif ($social_bookmarks_enabled) {
    if (isset($_SESSION['uid'])) {
        //check if user is course member
        $result = Database::get()->querySingle("SELECT COUNT(*) as c FROM course_user WHERE course_id = ?d AND user_id = ?d", $course_id, $uid);
        if ($result->c > 0) {
            if (isset($_POST['submitLink'])) {
                if (isset($_POST['id']) && !is_link_creator(getDirectReference($_POST['id']))) {
                    Session::Messages($langLinkNotOwner, 'alert-error');
                } else {
                    $_POST['selectcategory'] = getIndirectReference(-2); //ensure that simple users cannot change category
                    submit_link();
                    $message = isset($_POST['id']) ? $langLinkMod : $langLinkAdded;
                    Session::Messages($message, 'alert-success');
                }
                redirect_to_home_page("modules/link/index.php");
            }
            switch ($action) {
                case 'deletelink':
                    if (is_link_creator($id)) {
                        delete_link($id);
                        Session::Messages($langLinkDeleted, 'alert-success');
                    } else {
                        Session::Messages($langLinkNotOwner, 'alert-danger');
                    }
                    redirect_to_home_page("modules/link/index.php");
                    break;
            }
            
            if (isset($_GET['action'])) {
                $data['action_bar'] = action_bar(array(
                        array('title' => $langBack,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                              'icon' => 'fa-reply',
                              'level' => 'primary-label')));
            
            } else {
                $ext = (isset($urlview)? "&amp;urlview=$urlview": '');
                $data['action_bar'] = action_bar(array(
                        array('title' => $langLinkAdd,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=addlink$ext",
                              'icon' => 'fa-plus-circle',
                              'button-class' => 'btn-success',
                              'level' => 'primary-label')));
            }
            
            if (in_array($action, array('addlink', 'editlink'))) {
                if ((isset($id) && is_link_creator($id)) || !isset($id)) {
                    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langLinks);

                    if ($action == 'editlink') {
                        $data['link'] = Database::get()->querySingle("SELECT * FROM `link` WHERE course_id = ?d AND id = ?d", $course_id, $id);
                        if ($data['link']) {
                            $description = purify(trim($data['link']->description));
                        } else {
                            $description = '';
                        }                        
                        $data['submit_label'] = $langLinkModify;
                    } else {
                        $description = '';
                        $data['submit_label'] = $langAdd;
                    }
                    $data['urlLinkError'] = Session::getError('urllink') ? " has-error" : "";
                    $data['description_textarea'] = rich_text_editor('description', 3, 30, $description);
                    
                    echo view('modules.link.create', $data);
                    exit();
                }
            }
        }
    }
}



$data['display_tools'] = $display_tools = $is_editor && !$is_in_tinymce;

if (!in_array($action, array('addlink', 'editlink', 'addcategory', 'editcategory', 'settings'))) {
    
    if ($social_bookmarks_enabled == 1) {
        $data['countlinks'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM `link` WHERE course_id = ?d AND category <> ?d", $course_id, -1)->cnt;
    } else {
        $data['countlinks'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM `link` WHERE course_id = ?d AND category <> ?d AND category <> ?d", $course_id, -1, -2)->cnt;
    }
    
    add_units_navigation(true);
    
    $head_content .= abuse_report_add_js();

    //Uncategorized Links
    $general_links = Database::get()->queryArray("SELECT * FROM `link` WHERE course_id = ?d AND category = ?d", $course_id, 0);    
    $data['general_category'] = (object) ['id' => 0, 'links' => $general_links];

    //Social Links
    $social_links = Database::get()->queryArray("SELECT * FROM `link` WHERE course_id = ?d AND category = ?d", $course_id, -2);      
    $data['social_category'] = (object) ['id' => -2, 'links' => $social_links];

    //Other Categories                
    $data['categories'] = FALSE;
    DataBase::get()->queryFunc("SELECT * FROM `link_category` WHERE course_id = ?d ORDER BY `order`", function($category) use (&$data) {
        $links = Database::get()->queryArray("SELECT * FROM `link`
                               WHERE course_id = ?d AND category = ?d
                               ORDER BY `order`", $category->course_id, $category->id);
        $category->links = $links;
        $data['categories'][] = $category;

    }, $course_id);        

    // making the show none / show all links. Show none means urlview=0000 (number of zeros depending on the
    // number of categories). Show all means urlview=1111 (number of 1 depending on teh number of categories).         
    if ($urlview === '') {
        $urlview = $data['urlview'] = str_repeat('0', count($data['categories']));
    }

    echo view('modules.link.index', $data);
    exit();
}

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

require_once 'modules/search/indexer.class.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/abuse_report/abuse_report.php';

function makedefaultviewcode($locatie) {
    global $aantalcategories;

    $view = str_repeat('0', $aantalcategories);
    $view[$locatie] = '1';

    return $view;
}

/**
 * @brief Enter the modified info submitted from the link form into the database
 * @global type $course_id
 * @global type $langLinkMod
 * @global type $langLinkAdded
 * @global type $urllink
 * @global type $title
 * @global type $description
 * @global type $selectcategory
 * @global type $langLinkNotPermitted
 * @global string $state
 * @return type
 */
function submit_link() {
    global $course_id, $langLinkMod, $langLinkAdded, $course_code, $uid, $langSocialCategory,
    $urllink, $title, $description, $selectcategory, $langLinkNotPermitted, $state,
	$langFormErrors, $langTheFieldIsRequired, $langTheField;

    register_posted_variables(array('urllink' => true,
        'title' => true,
        'description' => true), 'all', 'trim');
    $urllink = canonicalize_url($urllink);
    
    $v = new Valitron\Validator($_POST);
    $v->rule('required', ['urllink']);
    $v->rule('url', ['urllink']);
    $v->rule('urlActive', ['url']);
    $v->labels(['urllink' => "$langTheField URL"]);
    if($v->validate()) {
        $set_sql = "SET url = ?s, title = ?s, description = ?s, category = ?d";
        $terms = array($urllink, $title, purify($description), intval(getDirectReference($_POST['selectcategory'])));

        if (isset($_POST['id'])) {
                $id = intval(getDirectReference($_POST['id']));
                Database::get()->query("UPDATE `link` $set_sql WHERE course_id = ?d AND id = ?d", $terms, $course_id, $id);

                $log_type = LOG_MODIFY;
        } else {
                $order = Database::get()->querySingle("SELECT MAX(`order`) as maxorder FROM `link`
                                                                        WHERE course_id = ?d AND category = ?d", $course_id, getDirectReference($_POST['selectcategory']))->maxorder;
                $order++;
                $id = Database::get()->query("INSERT INTO `link` $set_sql, course_id = ?d, `order` = ?d, user_id = ?d", $terms, $course_id, $order, $uid)->lastInsertID;
                $log_type = LOG_INSERT;
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_LINK, $id);
        // find category name
        if ($selectcategory == -2) {
                $category = $langSocialCategory;
        } else {
                $category_object = Database::get()->querySingle("SELECT link_category.name as name FROM link, link_category
                                                                                                                WHERE link.category = link_category.id
                                                                                                                AND link.course_id = ?s
                                                                                                                AND link.id = ?d", $course_id, $id);
                $category = $category_object ? $category_object->name : 0;
        }
        $txt_description = ellipsize_html(canonicalize_whitespace(strip_tags($description)), 50, '+');
        Log::record($course_id, MODULE_ID_LINKS, $log_type, @array('id' => $id,
                'url' => $urllink,
                'title' => $title,
                'description' => $txt_description,
                'category' => $category));

    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/link/index.php?course=$course_code&action=addlink&urlview=");
    }
}

/**
 * @brief Enter the modified info submitted from the category form into the database
 * @global type $course_id
 * @global type $langCategoryAdded
 * @global type $langCategoryModded
 * @global type $categoryname
 * @global type $description
 */
function submit_category() {
    global $course_id, $langCategoryAdded, $langCategoryModded,
    $categoryname, $description, $course_code, $langTheFieldIsRequired, $langFormErrors;

    register_posted_variables(array('categoryname' => true,
                                    'description' => true), 'all', 'trim');
    $set_sql = "SET name = ?s, description = ?s";
    $terms = array($categoryname, purify($description));
	$v = new Valitron\Validator($_POST);
    $v->rule('required', array('categoryname'))->message($langTheFieldIsRequired)->label('');
    if($v->validate()) {
		if (isset($_POST['id'])) {
			$id = getDirectReference($_POST['id']);
			Database::get()->query("UPDATE `link_category` $set_sql WHERE course_id = ?d AND id = ?d", $terms, $course_id, $id);
			$log_type = LOG_MODIFY;
		} else {
			$order = Database::get()->querySingle("SELECT MAX(`order`) as maxorder FROM `link_category`
										WHERE course_id = ?d", $course_id)->maxorder;
			$order++;
			$id = Database::get()->query("INSERT INTO `link_category` $set_sql, course_id = ?d, `order` = ?d", $terms, $course_id, $order)->lastInsertID;
			$log_type = LOG_INSERT;
		}
		$txt_description = ellipsize(canonicalize_whitespace(strip_tags($description)), 50, '+');
		Log::record($course_id, MODULE_ID_LINKS, $log_type, array('id' => $id,
			'category' => $categoryname,
			'description' => $txt_description));
	} else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/link/index.php?course=$course_code&action=addcategory&urlview=");
    }
}

/**
 * @brief delete link
 * @global type $course_id
 * @global type $langLinkDeleted
 * @param type $id
 */
function delete_link($id) {
    global $course_id, $langLinkDeleted;

    $tuple = Database::get()->querySingle("SELECT url, title, category FROM link WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $url = $tuple->url;
    $title = $tuple->title;
    $category = $tuple->category;
    if ($category == -2) { //delete abuse reports and ratings for social bookmark
        Database::get()->query("DELETE abuse_report FROM abuse_report INNER JOIN `link` ON `link`.id = abuse_report.rid
                               WHERE abuse_report.rtype = ?s AND abuse_report.rid = ?d", 'link', $id);
        Database::get()->query("DELETE rating FROM rating INNER JOIN `link` ON `link`.id = rating.rid
                                WHERE rating.rtype = ?s AND rating.rid = ?d", 'link', $id);
        Database::get()->query("DELETE rating_cache FROM rating_cache INNER JOIN `link` ON `link`.id = rating_cache.rid
                                WHERE rating_cache.rtype = ?s AND rating_cache.rid = ?d", 'link', $id);
        
    }
    Database::get()->query("DELETE FROM `link` WHERE course_id = ?d AND id = ?d", $course_id, $id);
    Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_LINK, $id);
    Log::record($course_id, MODULE_ID_LINKS, LOG_DELETE, array('id' => $id,
                                                               'url' => $url,
                                                               'title' => $title));
}

/**
 * @brief delete category
 * @global type $course_id
 * @global type $langCategoryDeleted
 * @global type $catlinkstatus
 * @param type $id
 */
function delete_category($id) {
    global $course_id, $langCategoryDeleted, $catlinkstatus;

    Database::get()->query("DELETE FROM `link` WHERE course_id = ?d AND category = ?d", $course_id, $id);
    $category = Database::get()->querySingle("SELECT name FROM link_category WHERE course_id = ?d AND id = ?d", $course_id, $id)->name;
    Database::get()->query("DELETE FROM `link_category` WHERE course_id = ?d AND id = ?d", $course_id, $id);
    Log::record($course_id, MODULE_ID_LINKS, LOG_DELETE, array('cat_id' => $id,
                                                               'category' => $category));
}

/**
 * @brief check if user is creator of link, mainly used for social bookmarks
 * @global type $uid
 * @param type $id
 */
function is_link_creator($id) {
    global $uid;
    
    $result = Database::get()->querySingle("SELECT COUNT(*) as c FROM `link` WHERE id = ?d AND user_id = ?d", $id, $uid);
    if ($result->c > 0) {
        return true;
    } else {
        return false;
    }
}

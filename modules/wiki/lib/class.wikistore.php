<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
  @file class.wikistore.php
  @author: Frederic Minne <zefredz@gmail.com>
           Open eClass Team <eclass@gunet.gr>
 */

require_once dirname(__FILE__) . "/class.wiki.php";
require_once 'include/log.class.php';

!defined("WIKI_NOT_FOUND_ERROR") && define("WIKI_NOT_FOUND_ERROR", "Wiki not found");

/**
 * Class representing the WikiStore
 * (ie the place where the wiki are stored)
 */
class WikiStore {

    var $error = '';

    /**
     * Constructor
     */
    public function __construct() {

    }

    // load and save
    /**
     * Load a Wiki
     * @param int wikiId ID of the Wiki
     * @return Wiki the loaded Wiki
     */
    function loadWiki($wikiId) {
        $wiki = new Wiki();

        if ($wiki->hasError()) {
        	$this->setError($wiki->error);
        }

        $wiki->load($wikiId);

        return $wiki;
    }

    /**
     * Check if a page exists in a given wiki
     * @param int wikiId ID of the Wiki
     * @param string title page title
     * @return boolean
     */
    function pageExists($wikiId, $title) {

        $sql = "SELECT COUNT(`id`) as `c` "
        		. "FROM `wiki_pages` "
        		. "WHERE BINARY `title` = ?s "
        		. "AND `wiki_id` = ?d";

        $result = Database::get()->querySingle($sql, $title, $wikiId);

        if ($result->c > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if a wiki exists usind its ID
     * @param int id wiki ID
     * @return boolean
     */
    function wikiIdExists($wikiId) {
        global $course_id;

        $sql = "SELECT COUNT(`id`) as `c` "
                . "FROM `wiki_properties` "
                . "WHERE `id` = ?d "
                . "AND `course_id` = ?d";

        $result = Database::get()->querySingle($sql, $wikiId, $course_id);

        if ($result->c > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the list of the wiki's for a given group
     * @param int groupId ID of the group, Zero for a course
     * @return array list of the wiki's for the given group
     */
    function getWikiListByGroup($groupId) {
        global $course_id, $is_editor;

        if ($is_editor) {
            $extra = "";
        } else {
            $extra = "AND visible = 1";
        }
        $sql = "SELECT id, title, description, visible
                FROM wiki_properties
                WHERE group_id = ?d
                AND course_id = ?d $extra
                ORDER BY `id` DESC";

        return Database::get()->queryArray($sql, $groupId, $course_id);
    }

    /**
     * Get the list of the wiki's in a course
     * @return array list of the wiki's in the course
     * @see WikiStore::getWikiListByGroup( $groupId )
     */
    function getCourseWikiList() {
        return $this->getWikiListByGroup(0);
    }

    /**
     * Get the list of the wiki's in all groups (exept course wiki's)
     * @return array list of all the group wiki's
     */
    function getGroupWikiList() {
        global $course_id;

        $sql = "SELECT `id`, `title`, `description` "
                . "FROM `wiki_properties` "
                . "WHERE `group_id` != ?d "
                . "AND `course_id` = ?d "
                . "ORDER BY `group_id` ASC";

        return Database::get()->queryArray($sql, 0, $course_id);
    }

    function getNumberOfPagesInWiki($wikiId) {

        if ($this->wikiIdExists($wikiId)) {
            $sql = "SELECT count( `id` ) as `pages` "
                    . "FROM `wiki_pages` "
                    . "WHERE `wiki_id` = ?d";

            $result = Database::get()->querySingle($sql, $wikiId);

            return $result->pages;
        } else {
            return false;
        }
    }

    /**
     * Delete a Wiki from the store
     * @param int wikiId ID of the wiki
     * @return boolean true on success, false on failure
     */
    function deleteWiki($wikiId) {
        global $course_id;

        if ($this->wikiIdExists($wikiId)) {

            $sql = "SELECT title FROM wiki_properties WHERE course_id = ?d AND id = ?d";
            $that = $this;
            $result = Database::get()->querySingle($sql, function ($errormsg) use ($that) {
                    $that->setError($errormsg);
                }, $course_id, $wikiId);
            $wiki_title = $result->title;

            // delete properties
            $sql = "DELETE FROM `wiki_properties` "
                    . "WHERE `id` = ?d"
                    . " AND `course_id` = ?d"
            ;

            $result = Database::get()->query($sql, function ($errormsg) use ($that) {
                    $that->setError($errormsg);
                }, $wikiId, $course_id);

            if ($result->affectedRows < 1) {
                return false;
            }

            // delete wiki acl
            $sql = "DELETE FROM `wiki_acls` "
                    . "WHERE `wiki_id` = ?d"
            ;

            $result = Database::get()->query($sql, function ($errormsg) use ($that) {
            	$that->setError($errormsg);
            }, $wikiId);

            if ($result->affectedRows < 1) {
                return false;
            }

            $sql = "SELECT `id` "
                    . "FROM `wiki_pages` "
                    . "WHERE `wiki_id` = ?d";

            $pageIds = Database::get()->queryArray($sql, function ($errormsg) use ($that) {
            	    $that->setError($errormsg);
                }, $wikiId);

            if ($this->hasError()) {
                return false;
            }

            foreach ($pageIds as $pageId) {
                $sql = "DELETE "
                        . "FROM `wiki_pages_content` "
                        . "WHERE `pid` = ?d"
                ;

                Database::get()->query($sql, function ($errormsg) use ($that) {
                	$that->setError($errormsg);
                }, $pageId->id);

                if ($this->hasError()) {
                    return false;
                }
            }

            $sql = "DELETE FROM `wiki_pages` "
                    . "WHERE `wiki_id` = ?d";

            Database::get()->query($sql, function ($errormsg) use ($that) {
                	$that->setError($errormsg);
                }, $wikiId);

            if ($this->hasError()) {
                return false;
            }
            // record action
            Log::record($course_id, MODULE_ID_WIKI, LOG_DELETE, array('wiki_id' => $wikiId,
                'title' => $wiki_title));

            return true;
        } else {
            $this->setError(WIKI_NOT_FOUND_ERROR);
            return false;
        }
    }

    // error handling
    function setError($errmsg = '') {
        $this->error = ($errmsg != '') ? $errmsg : "Unknown error";
    }

    function getError() {
        if ($this->error != '') {
            $error = $this->error;
            $this->error = '';
            return $error;
        } else {
            return false;
        }
    }

    function hasError() {
        return ($this->error != '');
    }

}

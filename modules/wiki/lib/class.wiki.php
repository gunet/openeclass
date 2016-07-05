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

/* * ===========================================================================
  class.wiki.php
  @last update: 15-05-2007 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7.9 licensed under GPL
  copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)

  original file: class.wiki Revision: 1.12.2.5

  Claroline authors: Frederic Minne <zefredz@gmail.com>
  ==============================================================================
 */

require_once dirname(__FILE__) . "/class.wikipage.php";
require_once 'include/log.class.php';

!defined("WIKI_NOT_FOUND_ERROR") && define("WIKI_NOT_FOUND_ERROR", "Wiki not found");

/**
 * This class represents a Wiki
 */
class Wiki {

    var $wikiId;
    var $title;
    var $desc;
    var $accessControlList;
    var $groupId;
    // error handling
    var $error = '';

    /**
     * Constructor
     */
    function Wiki() {
        $this->wikiId = 0;
    }

    // accessors

    /**
     * Set Wiki title
     * @param string wikiTitle
     */
    function setTitle($wikiTitle) {
        $this->title = $wikiTitle;
    }

    /**
     * Get the Wiki title
     * @return string title of the wiki
     */
    function getTitle() {
        return $this->title;
    }

    /**
     * Set the description of the Wiki
     * @param string wikiDesc description of the wiki
     */
    function setDescription($wikiDesc = '') {
        $this->desc = $wikiDesc;
    }

    /**
     * Get the description of the Wiki
     * @param string description of the wiki
     */
    function getDescription() {
        return $this->desc;
    }

    /**
     * Set the access control list of the Wiki
     * @param array accessControlList wiki access control list
     */
    function setACL($accessControlList) {
        $this->accessControlList = $accessControlList;
    }

    /**
     * Get the access control list of the Wiki
     * @return array wiki access control list
     */
    function getACL() {
        return $this->accessControlList;
    }

    /**
     * Set the group ID of the Wiki
     * @param int groupId group ID
     */
    function setGroupId($groupId) {
        $this->groupId = $groupId;
    }

    /**
     * Get the group ID of the Wiki
     * @return int group ID
     */
    function getGroupId() {
        return $this->groupId;
    }

    /**
     * Set the ID of the Wiki
     * @param int wikiId ID of the Wiki
     */
    function setWikiId($wikiId) {
        $this->wikiId = $wikiId;
    }

    /**
     * Set the ID of the Wiki
     * @return int ID of the Wiki
     */
    function getWikiId() {
        return $this->wikiId;
    }

    // load and save

    /**
     * Load a Wiki
     * @param int wikiId ID of the Wiki
     */
    function load($wikiId) {
        if ($this->wikiIdExists($wikiId)) {
            $this->loadProperties($wikiId);
            $this->loadACL($wikiId);
        } else {
            $this->setError(WIKI_NOT_FOUND_ERROR);
        }
    }

    /**
     * Load the properties of the Wiki
     * @param int wikiId ID of the Wiki
     */
    function loadProperties($wikiId) {
        global $course_id;

        $sql = "SELECT `id`, `title`, `description`, `group_id` "
                . "FROM `wiki_properties` "
                . "WHERE `id` = ?d "
                . "AND `course_id` = ?d"
        ;
        
        $result = Database::get()->querySingle($sql, $wikiId, $course_id);

        $this->setWikiId($result->id);
        $this->setTitle($result->title);
        $this->setDescription($result->description);
        $this->setGroupId($result->group_id);
    }

    /**
     * Load the access control list of the Wiki
     * @param int wikiId ID of the Wiki
     */
    function loadACL($wikiId) {

        $sql = "SELECT `flag`, `value` "
                . "FROM `wiki_acls` "
                . "WHERE `wiki_id` = ?d"
        ;
        
        $result = Database::get()->queryArray($sql, $wikiId);

        $acl = array();

        if (is_array($result)) {
            foreach ($result as $row) {
                $value = ( $row->value == 'true' ) ? true : false;
                $acl[$row->flag] = $value;
            }
        }

        $this->setACL($acl);
    }

    /**
     * Save the Wiki
     */
    function save() {
        $this->saveProperties();

        $this->saveACL();

        if ($this->hasError()) {
            return 0;
        } else {
            return $this->wikiId;
        }
    }

    /**
     * Save the access control list of the Wiki
     */
    function saveACL() {

        $sql = "SELECT COUNT(`wiki_id`) as `c` FROM `wiki_acls` "
                . "WHERE `wiki_id` = ?d"
        ;

        $that = $this;
        $result = Database::get()->querySingle($sql, function ($errormsg) use ($that) {
            	    $that->setError($errormsg);
                }, $this->getWikiId());
        
        // wiki already exists
        if ($result->c > 0) {
            $acl = $this->getACL();

            foreach ($acl as $flag => $value) {
                $value = ( $value == false ) ? 'false' : 'true';

                $sql = "UPDATE `wiki_acls` "
                        . "SET `value`= ?s "
                        . "WHERE `wiki_id` = ?d "
                        . "AND `flag` = ?s"
                ;

                Database::get()->query($sql, function ($errormsg) use ($that) {
            	        $that->setError($errormsg);
                    }, $value, $this->getWikiId(), $flag);
            }
        }
        // new wiki
        else {
            $acl = $this->getACL();

            foreach ($acl as $flag => $value) {
                $value = ( $value == false ) ? 'false' : 'true';

                $sql = "INSERT INTO "
                        . "`wiki_acls`"
                        . "("
                        . "`wiki_id`, `flag`, `value`"
                        . ") "
                        . "VALUES(?d,?s,?s)"
                ;

                Database::get()->query($sql, function ($errormsg) use ($that) {
            	        $that->setError($errormsg);
                    }, $this->getWikiId(), $flag, $value);
            }
        }
    }

    /**
     * Save the properties of the Wiki
     */
    function saveProperties() {
        global $course_id;

        // new wiki
        if ($this->getWikiId() === 0) {
            // INSERT PROPERTIES
            $sql = "INSERT INTO `"
                    . "wiki_properties"
                    . "`("
                    . "`course_id`, `title`,`description`,`group_id`"
                    . ") "
                    . "VALUES(?d,?s,?s,?d)"
            ;

            // GET WIKIID
            $that = $this;
            $result = Database::get()->query($sql, function ($errormsg) use ($that) {
            	    $that->setError($errormsg);
                }, $course_id, $this->getTitle(), $this->getDescription(), $this->getGroupId());

            if (!$this->hasError()) {
                $wikiId = $result->lastInsertID;
                $this->setWikiId($wikiId);
            }
            $log_action = LOG_INSERT;
        }
        // Wiki already exists
        else {
            // UPDATE PROPERTIES
            $sql = "UPDATE `wiki_properties` "
                    . "SET "
                    . "`title` = ?s, "
                    . "`description` = ?s, "
                    . "`group_id` = ?d "
                    . "WHERE `id` = ?d"
                    . " AND `course_id` = ?d"
            ;

            $that = $this;
            Database::get()->query($sql, function ($errormsg) use ($that) {
            	        $that->setError($errormsg);
                    }, $this->getTitle(), $this->getDescription(), $this->getGroupId(), $this->getWikiId(), $course_id);
            $log_action = LOG_MODIFY;
        }
        //record action                
        Log::record($course_id, MODULE_ID_WIKI, $log_action, array('wiki_id' => $this->getWikiId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription()));
    }

    // utility methods

    /**
     * Check if a page exists in the wiki
     * @param string title page title
     * @return boolean
     */
    function pageExists($title) {

        $sql = "SELECT COUNT(`id`) as `c` "
                . "FROM `wiki_pages` "
                . "WHERE BINARY `title` = ?s "
                . "AND `wiki_id` = ?d"
        ;

        $result = Database::get()->querySingle($sql, $title, $this->wikiId);
        
        if($result->c > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if a wiki exists using its title
     * @param string title wiki title
     * @return boolean
     */
    function wikiExists($title) {
        global $course_id;

        $sql = "SELECT COUNT(`id`) as `c` "
                . "FROM `wiki_properties` "
                . "WHERE `title` = ?s "
                . "AND `course_id` = ?d"
        ;

        $result = Database::get()->querySingle($sql, $title, $course_id);
        
        if($result->c > 0) {
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
    function wikiIdExists($id) {
        global $course_id;

        $sql = "SELECT COUNT(`id`) as `c` "
                . "FROM `wiki_properties` "
                . "WHERE `id` = ?d "
                . "AND `course_id` = ?d"
        ;

        $result = Database::get()->querySingle($sql, $id, $course_id);
        
        if($result->c > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all the pages of this wiki (at this time the method returns
     * only the titles of the pages...)
     * @return array containing thes pages
     */
    function allPages() {

        $sql = "SELECT `title` "
                . "FROM `wiki_pages` "
                . "WHERE `wiki_id` = ?d "
                . "ORDER BY `title` ASC"
        ;
        
        return Database::get()->queryArray($sql, $this->getWikiId());
    }
	
	/**
     * Get all the pages of this wiki (at this time the method returns
     * only the titles of the pages...) ordered by creation date
     * @return array containing thes pages
     */
    public function allPagesByCreationDate() {
        $sql = "SELECT `title` "
                . "FROM `wiki_pages` "
                . "WHERE `wiki_id` = ?d "
                . "ORDER BY `ctime` ASC"
        ;

        return Database::get()->queryArray($sql, $this->getWikiId());
    }

    /**
     * Get recently modified wiki pages
     * @param int offset start at given offset
     * @param int count number of record to return starting at offset
     * @return array recently modified pages (title, last_mtime, editor_id)
     */
    function recentChanges($offset = 0, $count = 50) {

        $limit = ($count == 0 ) ? "" : "LIMIT " . $offset . ", " . $count;

        $sql = "SELECT `page`.`title`, `page`.`last_mtime`, `content`.`editor_id` "
                . "FROM `wiki_pages` `page`, "
                . "`wiki_pages_content` `content` "
                . "WHERE `page`.`wiki_id` = ?d "
                . "AND `page`.`last_version` = `content`.`id` "
                . "ORDER BY `page`.`last_mtime` DESC "
                . $limit
        ;

        return Database::get()->queryArray($sql, $this->getWikiId());
    }
	
	public function getNumberOfPages() {
        $sql = "
            SELECT count( `id` ) as `pages` 
            FROM `wiki_pages` 
            WHERE `wiki_id` = ?d";

        $result = Database::get()->querySingle($sql, $this->getWikiId());

        return $result->pages;
    }

    // error handling

    function setError($errmsg = '') {
        $this->error = ($errmsg != '') ? $errmsg : 'Unknown error';
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

?>
